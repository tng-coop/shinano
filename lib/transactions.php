<?php

declare(strict_types=1);

#----------------------------------------------------------------

# lib to provide transaction sequence
namespace Tx {

use \PDO;

class Exception extends \RuntimeException {
}

function with_connection(string $data_source_name, string $sql_user, string $sql_password) {
    return function($tx) use($data_source_name, $sql_user, $sql_password) {
        $conn = new PDO($data_source_name, $sql_user, $sql_password,
                        array(
                            /* connection pooling
                               mysqlドライバでは prepared-statement の leak を防ぐ仕組みは担保されているか? */
                            PDO::ATTR_PERSISTENT => true,
                            PDO::ATTR_TIMEOUT => 600,
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            /* default is buffering all result of query*/
                            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false)
                        );
        try {
            return $tx($conn);
        } catch(Throwable $e) {
            $conn->rollBack();
            throw $e;
        }
    };
}

function block(PDO $conn, string $tag) {
    return function($body) use($conn, $tag) {
        try {
            if (!($conn->beginTransaction())) {
                throw new \Tx\Exception('Tx.block: beginTrascaction failed: ' . $tag);
            }
            $result = $body();
            if (!($conn->commit())) {
                throw new \Tx\Exception('Tx.block: commit failed: ' . $tag);
            }
            return $result;
        } catch(Throwable $e) {
            $conn->rollBack();
            throw $e;
        }
    };
}

} ## end of namesapce Tx

#----------------------------------------------------------------

# transaction definitions for shinano
namespace TxSnn {

include_once(__DIR__ . '/finite_field.php');

use \PDO;

function add_user(PDO $conn, string $name, string $email, string $passwd_hash, string $note) {
    \Tx\block($conn, "add_user")(
        function() use($conn, $name, $email, $passwd_hash, $note) {
            $pstate = $conn->prepare('SELECT last_uid FROM public_uid_state LIMIT 1 FOR UPDATE');
            $pstate->execute();
            // カーソル位置でテーブルのレコードをロック
            $state_ref = $pstate->fetch(PDO::FETCH_NUM);
            if (!$state_ref) {
                new \Tx\Exception('TxSnn.add_user: internal error. wrong system initialization');
            }
            $last_public_uid = $state_ref[0];
            $public_uid = \FF\galois_next24($last_public_uid);
            $stmt = $conn->prepare('UPDATE public_uid_state SET last_uid = ?');
            $pstate->fetchAll(); ## UGLY HACK for MySQL!! breaks critical section for Cursor Solid isolation RDBMs
            $stmt->execute(array($public_uid));

            $stmt = $conn->prepare(<<<SQL
INSERT INTO user(email, passwd_hash, public_uid, name, note, created_at, updated_at)
    VALUES (:email, :passwd_hash, :public_uid, :name, :note, current_timestamp, current_timestamp)
SQL
            );
            $stmt->execute(array(':email' => $email, ':passwd_hash' => $passwd_hash, 'public_uid' => $public_uid,
                                 ':name' => $name, ':note' => $note));
        });
}

function add_job_listing(PDO $conn, string $email, string $title, string $description) {
    return add_job_things('L')($conn, $email, $title, $description);
}

function add_job_seeking(PDO $conn, string $email, string $title, string $description) {
    return add_job_things('S')($conn, $email, $title, $description);
}

function add_job_things(string $attribute) {
    return function(PDO $conn, string $email, string $title, string $description) use($attribute) {
        \Tx\block($conn, "add_job_things:" . $attribute)(
            function() use($attribute, $conn, $email, $title, $description) {
                $user_id = user_id_lock_by_email($conn, $email);
                $stmt = $conn->prepare(<<<SQL
INSERT INTO job_entry(attribute, user, title, description, created_at, updated_at)
    VALUES (:attribute, :user_id, :title, :desc, current_timestamp, current_timestamp)
SQL
                );
                $stmt->execute(array(':attribute' => $attribute, ':user_id' => $user_id, ':title' => $title, ':desc' => $description));
            }
        );
    };
}

function open_job_listing(PDO $conn, string $email, int $job_entry_id) {
    return open_job_things('L')($conn, $email, $job_entry_id);
}

function open_job_seeking(PDO $conn, string $email, int $job_entry_id) {
    return open_job_things('S')($conn, $email, $job_entry_id);
}

function open_job_things(string $attribute) {
    return function(PDO $conn, string $email, int $job_entry_id) use ($attribute) {
        \Tx\block($conn, "open_job_things:" . $attribute)(
            function() use($attribute, $conn, $email, $job_entry_id) {
                $user_id = user_id_lock_by_email($conn, $email);
                if (!$user_id) {
                    return;
                }
                $stmt = $conn->prepare(<<<SQL
UPDATE job_entry AS J SET opened_at = current_timestamp
       WHERE attribute = :attribute AND id = :job_entry_id AND user = :user_id
SQL
                );
                $stmt->execute(array(':attribute' => $attribute, ':job_entry_id' => $job_entry_id, ':user_id' => $user_id));
            }
        );
    };
}

function close_job_listing(PDO $conn, string $email, int $job_entry_id) {
    return close_job_things('L')($conn, $email, $job_entry_id);
}

function close_job_seeking(PDO $conn, string $email, int $job_entry_id) {
    return close_job_things('S')($conn, $email, $job_entry_id);
}

function close_job_things(string $attribute) {
    return function(PDO $conn, string $email, int $job_entry_id) use ($attribute) {
        \Tx\block($conn, "close_job_things:" . $attribute)(
            function() use($attribute, $conn, $email, $job_entry_id) {
                $user_id = user_id_lock_by_email($conn, $email);
                if (!$user_id) {
                    return;
                }
                $stmt = $conn->prepare(<<<SQL
UPDATE job_entry AS J SET closed_at = current_timestamp
       WHERE attribute = :attribute AND id = :job_entry_id AND user = :user_id
SQL
                );
                $stmt->execute(array(':attribute' => $attribute, ':job_entry_id' => $job_entry_id, ':user_id' => $user_id));
            }
        );
    };
}

function user_id_lock_by_email(PDO $conn, string $email) {
    $stmt = $conn->prepare('SELECT id FROM user WHERE email = ? FOR UPDATE');
    $stmt->execute(array($email));
    // カーソル位置で user テーブルのレコードをロック
    $aref = $stmt->fetch(PDO::FETCH_NUM);
    if ($aref) {
        return $aref[0];
    }
    return false;
}

function view_job_things(PDO $conn, string $email) {
    $stmt = $conn->prepare(<<<SQL
SELECT U.name, J.attribute, J.title, J.description, J.created_at, J.updated_at, J.opened_at, J.closed_at
       FROM user as U INNER JOIN job_entry AS J
       ON U.id = J.user
       WHERE U.email = ?
       ORDER BY J.attribute, J.opened_at IS NULL ASC, J.created_at ASC;
SQL
            );
    $stmt->execute(array($email));
    return $stmt;
}

function search_job_things(PDO $conn, string $search_pat) {
    $stmt = $conn->prepare(<<<SQL
SELECT U.name, J.attribute, J.title, J.description, J.created_at, J.updated_at, J.opened_at, J.closed_at
       FROM user as U INNER JOIN job_entry AS J
       ON U.id = J.user
       WHERE J.title LIKE CONCAT('%', ?, '%')
       ORDER BY J.attribute, J.user, J.opened_at IS NULL ASC, J.created_at ASC;
SQL
    );
    $stmt->execute(array($search_pat));
    return $stmt;
}

} ## end of namesapce TxSnn

?>

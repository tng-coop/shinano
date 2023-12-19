<?php

declare(strict_types=1);

#----------------------------------------------------------------

# lib to provide transaction sequence
namespace Tx {

use \PDO;

class Exception extends \RuntimeException {
}

## read_only : with_connection 内で1つ以上の SELECT 等を行なう.
## read_write: with_connection 内で1つ以上のトランザクション ( \Tx\block ) を行なう.
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

function unsafe_update_public_uid(PDO $conn, string $tag, $update_callback) {
    return \Tx\block($conn, "unsafe_update_public_uid: " . $tag)(
        ## public_uid を発行するトランザクション
        ## Galois LFSR 以外の $update_callback を渡すと空間を書き潰す危険がある
        function() use($conn, $update_callback) {
            $pstate = $conn->prepare('SELECT last_uid FROM public_uid_state LIMIT 1 FOR UPDATE');
            $pstate->execute();
            ## カーソル位置でテーブルのレコードをロック
            $state_ref = $pstate->fetch(PDO::FETCH_NUM);
            if (!$state_ref) {
                throw new \Tx\Exception('TxSnn.unsafe_update_public_uid: internal error. wrong system initialization');
            }
            $last_public_uid = $state_ref[0];
            [ $public_uid, $result ] = $update_callback($last_public_uid);
            $stmt = $conn->prepare('UPDATE public_uid_state SET last_uid = ?');
            $pstate->fetchAll(); ## UGLY HACK for MySQL!! breaks critical section for Cursor Solid isolation RDBMs
            $stmt->execute(array($public_uid));
            return $result;
        });
}

function gen_public_uid_list(PDO $conn, int $n) {
    return unsafe_update_public_uid($conn, "gen_public_uid_list",
                                    fn ($last_public_uid) => \FF\galois_next24_list($last_public_uid, $n) );
}

function add_user(PDO $conn, string $name, string $email, string $passwd_hash, string $note) {
    $public_uid = unsafe_update_public_uid($conn, "gen_public_uid",
                                           function ($last_public_uid) {
                                               $next = \FF\galois_next24($last_public_uid);
                                               return [ $next, $next ];
                                           });

    \Tx\block($conn, "add_user")(
        function() use($conn, $email, $passwd_hash, $public_uid, $name, $note) {
            $stmt = $conn->prepare(<<<SQL
INSERT INTO user(email, passwd_hash, public_uid, last_thing, name, note, created_at, updated_at)
    VALUES (:email, :passwd_hash, :public_uid, 0, :name, :note, current_timestamp, current_timestamp)
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
        return \Tx\block($conn, "add_job_things:" . $attribute)(
            function() use($attribute, $conn, $email, $title, $description) {
                // UPDATE user's last entry
                $user = user_lock_by_email_or_raise('TxSnn.add_job_things: ', $conn, $email);
                $user_id = $user['id'];
                $id_on_user = $user['last_thing'] + 1;
                $upd_last_thing = $conn->prepare('UPDATE user SET last_thing = :id_on_user WHERE id = :user_id');
                $upd_last_thing->execute(array(':user_id' => $user_id, 'id_on_user' => $id_on_user));

                // INSERT to job_entry
                $stmt = $conn->prepare(<<<SQL
INSERT INTO job_entry(user, id_on_user, attribute, title, description, created_at, updated_at)
       VALUES (:user_id, :id_on_user, :attribute, :title, :description, current_timestamp, current_timestamp);
SQL
                );
                $stmt->execute(array(':user_id' => $user_id, ':id_on_user' => $id_on_user, ':attribute' => $attribute,
                                     ':title' => $title, ':description' => $description));

                return array($user_id, $id_on_user);
            }
        );
    };
}

function open_job_thing(PDO $conn, string $email, int $id_on_user){
    return open_job_things(null)($conn, $email, $id_on_user);
}

function close_job_thing(PDO $conn, string $email, int $id_on_user){
    return close_job_things(null)($conn, $email, $id_on_user);
}

function update_job_things(PDO $conn, int $id_on_user,
                           string $email, string $attribute, string $title, string $description){
    // attribute:: 'S'eeking or 'L'isting or etc...
    // email: email of logging in account.
    \Tx\block($conn, "update_job_things email: {$email}, id_on_user:{$id_on_user} ")(
        function() use($conn, $id_on_user, $email, $attribute, $title, $description) {
            // raise if invalid user
            $user_id = user_id_lock_by_email_or_raise('TxSnn.update_job_things: ', $conn, $email);
            // rewrite DB
            $sql1 = "UPDATE job_entry AS J"
                  . "  SET J.attribute = :attribute , J.title = :title , J.description = :description , J.updated_at = current_timestamp "
                  . "  WHERE J.id_on_user = :id_on_user AND J.user = :user_id;";
            $stmt = $conn->prepare($sql1);
            $stmt->execute(array(':id_on_user'=>$id_on_user, ':user_id'=>$user_id, ':attribute'=>$attribute, ':title'=>$title, ':description'=>$description));
            return true;
        });
}

function open_job_listing(PDO $conn, string $email, int $id_on_user) {
    return open_job_things('L')($conn, $email, $id_on_user);
}

function open_job_seeking(PDO $conn, string $email, int $id_on_user) {
    return open_job_things('S')($conn, $email, $id_on_user);
}

function open_job_things($attribute) {
    return function(PDO $conn, string $email, int $id_on_user) use ($attribute) {
        \Tx\block($conn, "open_job_things:" . $attribute)(
            function() use($attribute, $conn, $email, $id_on_user) {
                $user_id = user_id_lock_by_email_or_raise('TxSnn.open_job_things: ', $conn, $email);
                $stmt = $conn->prepare(<<<SQL
UPDATE job_entry AS J SET opened_at = current_timestamp
       WHERE user = :user_id AND id_on_user = :id_on_user AND (:n_attribute IS NULL OR attribute = :attribute)
SQL
                );
                $stmt->execute(array(':user_id' => $user_id, ':id_on_user' => $id_on_user,
                                     ':n_attribute' => $attribute, ':attribute' => $attribute));
            }
        );
    };
}

function close_job_listing(PDO $conn, string $email, int $id_on_user) {
    return close_job_things('L')($conn, $email, $id_on_user);
}

function close_job_seeking(PDO $conn, string $email, int $id_on_user) {
    return close_job_things('S')($conn, $email, $id_on_user);
}

function close_job_things($attribute) {
    return function(PDO $conn, string $email, int $id_on_user) use ($attribute) {
        \Tx\block($conn, "close_job_things:" . $attribute)(
            function() use($attribute, $conn, $email, $id_on_user) {
                $user_id = user_id_lock_by_email_or_raise('TxSnn.close_job_things: ', $conn, $email);
                $stmt = $conn->prepare(<<<SQL
UPDATE job_entry AS J SET closed_at = current_timestamp
       WHERE user = :user_id AND id_on_user = :id_on_user AND (:n_attribute IS NULL OR attribute = :attribute)
SQL
                );
                $stmt->execute(array(':user_id' => $user_id, ':id_on_user' => $id_on_user,
                                     ':n_attribute' => $attribute, ':attribute' => $attribute));
            }
        );
    };
}

function user_id_lock_by_email_or_raise(string $prefix, PDO $conn, string $email) {
    $user_id = user_id_lock_by_email($conn, $email);
    if (!$user_id) {
        throw new \Tx\Exception($prefix . 'wrong input email: ' . $email);
    }
    return $user_id;
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

function user_lock_by_email_or_raise(string $prefix, PDO $conn, string $email) {
    $user = user_lock_by_email($conn, $email);
    if (!$user) {
        throw new \Tx\Exception($prefix . 'wrong input email: ' . $email);
    }
    return $user;
}

function user_lock_by_email(PDO $conn, string $email) {
    $stmt = $conn->prepare('SELECT id, last_thing FROM user WHERE email = ? FOR UPDATE');
    $stmt->execute(array($email));
    // カーソル位置で user テーブルのレコードをロック
    $aref = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($aref) {
        return $aref;
    }
    return false;
}

function user_public_uid_get_by_email(PDO $conn, string $email){
    $stmt = $conn->prepare('SELECT public_uid FROM user WHERE email = ?');
    $stmt->execute(array($email));
    $aref = $stmt->fetch(PDO::FETCH_NUM);
    if ($aref) {
        return $aref[0];
    }
    return false;
}

function view_job_things_by_public_uid(PDO $conn, int $public_uid) {
    $stmt = $conn->prepare(<<<SQL
SELECT U.public_uid, U.name, J.attribute, J.title, J.description, J.created_at, J.updated_at, J.opened_at, J.closed_at, J.id_on_user AS eid
       FROM user as U INNER JOIN job_entry AS J
       ON U.id = J.user
       WHERE U.public_uid = ?
       ORDER BY J.attribute, J.opened_at IS NULL ASC, J.created_at ASC;
SQL
            );
    $stmt->execute(array($public_uid));
    return $stmt;
}

function view_job_thing_by_public_uid_and_id_on_user(PDO $conn, int $public_uid, int $id_on_user) {
    $stmt = $conn->prepare(<<<SQL
SELECT U.public_uid, U.name, J.attribute, J.title, J.description, J.created_at, J.updated_at, J.opened_at, J.closed_at, J.id_on_user AS eid
       FROM user as U INNER JOIN job_entry AS J
       ON U.id = J.user
       WHERE U.public_uid = ?
         AND J.id_on_user = ?
       ORDER BY J.attribute, J.opened_at IS NULL ASC, J.created_at ASC;
SQL
            );
    $stmt->execute(array($public_uid, $id_on_user));
    return $stmt;
}

function view_job_things_by_email(PDO $conn, string $email) {
    $stmt = $conn->prepare(<<<SQL
SELECT U.public_uid, U.name, J.attribute, J.title, J.description, J.created_at, J.updated_at, J.opened_at, J.closed_at, J.id_on_user AS eid
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
SELECT U.public_uid, U.name, J.attribute, J.title, J.description, J.created_at, J.updated_at, J.opened_at, J.closed_at
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

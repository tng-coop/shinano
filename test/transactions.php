<?php

declare(strict_types=1);

include_once(__DIR__ . '/../lib/transactions.php');

$dsn = 'mysql:host=localhost;dbname=shinano_dev';

function check_record1(PDO $conn, string $sql, $pred) {
    $stmt = $conn->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($pred($row)) {
        echo "OK\n";
    } else {
        $show_row = print_r($row, true);
        echo "failed: {$sql} \n  row : {$show_row} is not satisfied predicate !\n";
    }
}

function show_records($stmt) {
    while(true) {
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!$r) {
            break;
        }
        print_r($r);
    }
    $stmt->closeCursor();
}

\Tx\with_connection($dsn, 'sdev_rw', 'Kis0Shinan0DevRW')(
    function($conn_rw) {
        \TxSnn\add_user($conn_rw, 'Taro Yamada', 'yamada@example.com', 'abcd', 'taro\'s note');
        check_record1($conn_rw, 'SELECT * FROM user',
                      fn($row) => $row['email'] == 'yamada@example.com');
        \TxSnn\add_job_listing($conn_rw, 'yamada@example.com', 'taro job listing', 'taro job description ...');
        check_record1($conn_rw, "SELECT * FROM job_entry WHERE attribute = 'L'",
                      fn($row) => preg_match('/^taro job l/', $row['title']) && is_null($row['opened_at']));
        \TxSnn\add_job_seeking($conn_rw, 'yamada@example.com', 'taro job seeking', 'taro seeking job about ...');
        check_record1($conn_rw, "SELECT * FROM job_entry WHERE attribute = 'S'",
                      fn($row) => preg_match('/^taro job s/', $row['title']) && is_null($row['opened_at']));
        \TxSnn\open_job_listing($conn_rw, 'yamada@example.com', 1);
        check_record1($conn_rw, "SELECT * FROM job_entry WHERE attribute = 'L'",
                      fn($row) => !is_null($row['opened_at']) && is_null($row['closed_at']));
        \TxSnn\open_job_seeking($conn_rw, 'yamada@example.com', 2);
        check_record1($conn_rw, "SELECT * FROM job_entry WHERE attribute = 'S'",
                      fn($row) => !is_null($row['opened_at']) && is_null($row['closed_at']));
        \TxSnn\close_job_listing($conn_rw, 'yamada@example.com', 1);
        check_record1($conn_rw, "SELECT * FROM job_entry WHERE attribute = 'L'",
                      fn($row) => !is_null($row['closed_at']));
        \TxSnn\close_job_seeking($conn_rw, 'yamada@example.com', 2);
        check_record1($conn_rw, "SELECT * FROM job_entry WHERE attribute = 'S'",
                      fn($row) => !is_null($row['closed_at']));

        show_records(\TxSnn\view_job_things($conn_rw, 'yamada@example.com'));
        show_records(\TxSnn\search_job_things($conn_rw, 'taro'));
    }
);

 ?>
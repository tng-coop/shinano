<?php

// Insert mock datas to Shinano DB from .csv files

declare(strict_types=1);

include_once(__DIR__ . '../../../lib/transactions.php');
include_once(__DIR__ . '../../../lib/utilities.php');
include_once(__DIR__ . '../../../lib/form_check.php');

// parse .csv file into table

function csvfile_table_array($csv_file_path){
    $csv_table = [];

    $file = fopen($csv_file_path , "r");
    while(! feof($file)){
        $row = (fgetcsv($file)); // one line of csv
        if(isset($row)){
            array_push($csv_table, $row);
        }
    }
    fclose($file);

    return $csv_table;
}


function insert_users(){

    // csv into 2D_array
    $csv_filename=__DIR__ . "/./user.csv";
    $csv_table = csvfile_table_array($csv_filename);

    // key index, index key.
    //print_r($csv_table[0]);
    $ki = array(); // keys index
    foreach($csv_table[0] as $index => $col_key){
        $ki[trim($col_key)] = $index;
    }
    //print_r($ki);

    global $data_source_name, $sql_rw_user, $sql_rw_pass;
    \Tx\with_connection($data_source_name, $sql_rw_user, $sql_rw_pass)(
        function($conn_rw) use($csv_table, $ki) {
            $puid_list = \TxSnn\gen_public_uid_list($conn_rw, count($csv_table) - 1);

            $conn_rw->beginTransaction();
            // for for insert data
            // 1 is table_head line. $i+1 means index count from 1.
            for($i=0+1; isset($csv_table[$i+1]); $i++){

                // safe check and set value for row's data
                $row = $csv_table[$i];

                $r_name = $row[$ki['name']];
                $r_email = $row[$ki['email']];
                $r_password = $row[$ki['password']];

                $r_passwd_hash = password_hash($r_password, PASSWORD_DEFAULT);
                $r_note = $row[$ki['note']];

                $public_uid = $puid_list[$i];

                $stmt = $conn_rw->prepare(<<<SQL
                                          INSERT IGNORE INTO user(email, passwd_hash, public_uid, name, note, created_at, updated_at)
                                          VALUES (:email, :passwd_hash, :public_uid, :name, :note, current_timestamp, current_timestamp)
                                          SQL
                    );
                $stmt->execute(array(':email' => $r_email, ':passwd_hash' => $r_passwd_hash, 'public_uid' => $public_uid,
                                     ':name' => $r_name, ':note' => $r_note));
            }
            $conn_rw->commit();
        });

    return null;
}

function user_email_lock_by_id(PDO $conn, $user_id) {
    $stmt = $conn->prepare('SELECT email FROM user WHERE id = ? FOR UPDATE');
    $stmt->execute(array($user_id));
    // カーソル位置で user テーブルのレコードをロック
    $aref = $stmt->fetch(PDO::FETCH_NUM);
    if ($aref) {
        return $aref[0];
    }
    return false;
}


function insert_job_entries(){

    // csv into 2D_array
    $csv_filename=__DIR__ . "/./job_entry.csv";
    $csv_table = csvfile_table_array($csv_filename);

    // key index, index key.
    //print_r($csv_table[0]);
    $ki = array(); // keys index
    foreach($csv_table[0] as $index => $col_key){
        $ki[trim($col_key)] = $index;
    }

    // for for insert data
    // 1 is table_head line. $i+1 means index count from 1.
    for($i=0+1; isset($csv_table[$i+1]); $i++){
        global $data_source_name, $sql_rw_user, $sql_rw_pass;   
        \Tx\with_connection($data_source_name, $sql_rw_user, $sql_rw_pass)(
            function($conn_rw) use($csv_table, $ki, $i) {
                
                // value of row's data
                $row = $csv_table[$i];

                $r_attribute = $row[$ki['attribute']];
                $r_user_id = $row[$ki['user']];
                $r_title = $row[$ki['title']];
                $r_description = $row[$ki['description']];

                $email = user_email_lock_by_id($conn_rw, $r_user_id);


                \TxSnn\add_job_things($r_attribute)
                    ($conn_rw, $email, $r_title, $r_description);
                //$conn_rw->commit();
            });
    }

    return null;
}


// main method
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    # start logging time
    $start_time = microtime(true);
    {
        echo("insert user.\n");
        insert_users();
        
        echo("insert job_entry.\n");
        insert_job_entries();
    }
    # end logging time
    $end_time = microtime(true);
    $execution_time = ($end_time - $start_time);
    echo("execution time: {$execution_time} sec.\n");
}


?>

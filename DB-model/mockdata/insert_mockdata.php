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

            // for for insert data
            // 1 is table_head line. $i+1 means index count from 1.
            for($i=0+1; isset($csv_table[$i+1]); $i++){

                // safe check and set value for row's data
                $row = $csv_table[$i];

                $r_name = $row[$ki['name']];
                $r_email = $row[$ki['email']];
                $r_password = $row[$ki['password']];

                [[$pn, $bottom],
                 [$pe, $bottom],
                 [$pp, $bottom]]
                = [\FormCheck\check_user_name_safe($r_name),
                   \FormCheck\check_user_email_safe($r_email),
                   \FormCheck\check_user_password_safe($r_password, $r_password)];

                if($pn && $pe && $pp){

                    $r_passwd_hash = password_hash($pp, PASSWORD_DEFAULT);
                    $r_note = $row[$ki['note']];

                    // insert to DB
                    $emails_user_id=\TxSnn\user_id_lock_by_email($conn_rw, $r_email);
                    if(!$emails_user_id){
                        \TxSnn\add_user($conn_rw, $r_name, $r_email, $r_passwd_hash, $r_note);
                    }
                }
            }
        });

    return null;
}


// main method
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    echo("insert user.\n");
    # start logging time
    $start_time = microtime(true);
    insert_users();
    # end logging time
    $end_time = microtime(true);
    $execution_time = ($end_time - $start_time);
    echo("execution time: {$execution_time} sec.\n");
}


?>

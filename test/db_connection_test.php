<?php

// $data_source_name = "{$sqlclient}:host={$dbhost};dbname={$dbname};charset=UTF8";

$dbconn_ro = new PDO("mariadb:dbname=shinano_dev;host=localhost", "sdev_ro", "Kis0Shinan0DevR0"); // todo: charset
echo $dbconn_ro;

?>

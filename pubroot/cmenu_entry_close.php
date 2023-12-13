<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");
include_once(__DIR__ . "/../lib/form_check.php");
include_once(__DIR__ . '/../lib/transactions.php');

//

print_r("close (job_)entry\n");

echo '<br /> POST: ';
print_r($_POST);

echo '<br /> USER: ';
print_r($login->user());

?>

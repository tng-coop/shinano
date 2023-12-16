<?php

declare(strict_types=1);

include_once(__DIR__ . "/utilities_iso.php");
include_once(__DIR__ . "/prop.php");

prop('public_uid_iso: 1', public_uid_iso(1));
prop('public_uid_iso: pow(2, 24) - 1', public_uid_iso(pow(2, 24) - 1));
prop('uid_string_iso: 0000-0001', uid_string_iso('0000-0001'));
prop('uid_string_iso: 1677-7215', uid_string_iso('1677-7215'));

prop_results();

 ?>

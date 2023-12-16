<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/utilities.php");

function public_uid_iso(int $public_uid) : bool {
    return $public_uid === to_public_uid(from_public_uid($public_uid));
}

function uid_string_iso(string $ustr) : bool {
    return $ustr === from_public_uid(to_public_uid($ustr));
}

 ?>

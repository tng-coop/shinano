<?php

declare(strict_types=1);

$failed = array();

function prop(string $name, bool $b) : void {
    global $failed;
    if (!$b) {
        array_push($failed, '  ' . $name . "\n");
    }
}

function prop_results() {
    global $failed;
    if(empty($failed)) {
        echo "test uid: All succeeded.\n";
    } else {
        array_unshift($failed, "failed, list below:\n");
        $err = implode($failed);
        throw new RuntimeException($err);
    }
}

 ?>

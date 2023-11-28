<?php

// # configs

//echo "$URL hogehoge";


$document_root = realpath(__DIR__ . "/../");
$URL = get_url();

global $document_root;
global $URL;



// # functions

function get_url(){
    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
        $url = "https://";   
    else  
        $url = "http://";   
    // Append the host(domain name, ip) to the URL.   
    $url.= $_SERVER['HTTP_HOST'];   
    
    // Append the requested resource location to the URL   
    $url.= $_SERVER['REQUEST_URI'];    
    
    echo $url;
}

function eval_template($template_file)
{
    include($document_root . "./ingredients/template/{$template_file}");
}


?>

<?php

namespace NAMES;




// # functions

function get_url(){
    // check if secured
    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
        $url = "https://";   
    else  
        $url = "http://";   
    // Append the host(domain name, ip) to the URL.   
    $url.= $_SERVER['HTTP_HOST'];   
    
    // Append the requested resource location to the URL   
    $url.= $_SERVER['REQUEST_URI'];    
    
    return $url;
}


class TemplateAndConfigs{

    // config and config values

    function __construct(){
        $this->_document_root = realpath(__DIR__ . "/../"); 
        $this->_URL = get_url();
    }

    // template
    function eval_template($template_file){
        $v = $this;
        include($this->_document_root . "/./ingredients/template/{$template_file}");
    }
}



?>

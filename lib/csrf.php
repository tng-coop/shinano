<?php

// referenced:
// 6th chapter of "Postgre SQL 徹底入門"
// https://www.shoeisha.co.jp/book/detail/9784798164090
//
// CSRF attack is 
//
//
//
//

declare(strict_types=1);

namespace CSRF;

// session start if not started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// CSRF tokens
//
// getToken() generates token as session and token as string for hidden HTML.
//
// once POSTed by render HTML of client, inserted token (maybe hidden) is also POSTed to server.
// compare POSTed token with Session having token,
//
// checkToken() returns current_token of server's session.

class CSRF{
    const TOKEN_NAME = 'csrf_token';
    const BASE_SID_NAME = 'csrf_base_sid';

    public function getToken(){
        if(empty($_SESSION[self::BASE_SID_NAME]) || $_SESSION[self::BASE_SID_NAME] !== session_id()){
        
            if(function_exists('random_bytes')) {
                $bytes = random_bytes(32);
            } elseif(function_exists('openssl_random_pseudo_bytes')) {
                $bytes = openssl_random_pseudo_bytes(32);
            } else {
                return null;
            }
            $_SESSION[self::TOKEN_NAME] = bin2hex($bytes);
            $_SESSION[self::BASE_SID_NAME] = session_id();
        }

        return $_SESSION[self::TOKEN_NAME];
    }

    public function checkToken(){
        if(!filter_has_var(INPUT_POST, self::TOKEN_NAME)){
            return false;
        }
        $token = filter_input(INPUT_POST, self::TOKEN_NAME, FILTER_DEFAULT, FILTER_REQUIRE_SCALAR);
        if($token === false || $token === ''){
            $match = false;
        } else {
            $match = hash_equals(self::getToken(), $token);
        }
        if(!$match){
            @time_sleep_until(microtime(true) + 1);
            return false;
        }
        return true;
    }

    public function hiddenInputHTML(){
        $h_token_name = h(self::TOKEN_NAME);
        $h_token = h(self::getToken());
        return "<input type='hidden' name='${h_token_name}' value='${h_token}' />";
    }
}

?>

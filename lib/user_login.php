<?php

// referenced:
// 6th chapter of "Postgre SQL 徹底入門"
// https://www.shoeisha.co.jp/book/detail/9784798164090

declare(strict_types=1);

namespace USER_LOGIN;

include_once(__DIR__ . '/./utilities.php');

// session start if not started.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// login user

class LOGIN{
    private const LOGIN_PUID = 'login_user_public_uid'; // PUID is Public_User_ID.

    private $login_user;

    public function __construct(){
        $this->setLoginUser();
    }

    private function setLoginUser(){
        if(!isset($_SESSION[self::LOGIN_PUID])){
            $this->login_user=null;
        } else {
            $dbh = db_connect_ro();
            $sth = $dbh->prepare("SELECT id, public_uid, name, email" .
                                       "  FROM user" .
                                       "  WHERE public_uid=:public_uid");
            $sth->execute([':public_uid' => $_SESSION[self::LOGIN_PUID]]);
            $user=$sth->fetch();
            if($user){
                $this->login_user = $user;
            } else {
                $_SESSION[self::LOGIN_PUID] = null;
                $this->login_user = null;
            }
        }
    }
                
    public function user(string $col = null){
        if(is_null($col)){
            return $this->login_user;
        } elseif(isset($this->login_user[$col])) {
            return $this->login_user[$col];
        }
        return null;
    }

    public function login(int $public_uid){
        $_SESSION[self::LOGIN_PUID] = null;
        $_SESSION[self::LOGIN_PUID] = $public_uid;
        $this->setLoginUser();
        session_regenerate_id(true);
    }

    public function logout(){
        $_SESSION[self::LOGIN_PUID] = null;
        $this->setLoginUser();
        session_regenerate_id(true);
    }
}

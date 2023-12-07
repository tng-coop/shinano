<?php
session_start();
?>
<!-- referenced: https://www.javadrive.jp/php/session/ -->
<html>
    <head>

        <title>PHP TEST</title>
    </head>

    <body>
        <h3> セッション、クッキーのサンプル </h3>
        <p> referenced: https://www.javadrive.jp/php/session/ </p>
        
        <?php
        // reset session and cookie
        // query into key-array
        $qa = [];
        parse_str($_SERVER['QUERY_STRING'] , $qa);

        if($qa['reset'] == 'true'){
            // show current session and cookie
            print('<h4>reset! session. </h4>');
            print('セッション変数の一覧を表示します。<br />');
            print_r($_SESSION);
            print('<br>');
            print('セッションIDを表示します。<br />');
            print($_COOKIE["PHPSESSID"].'<br />');
            print('クッキー変数の一覧を表示します。<br />');
            print_r($_COOKIE);
            print('<br>');
            
            // logout 
            print('<p>ログアウトします</p>');

            // session to nil
            $_SESSION = array();

            // cookie PHPSESSID to nil
            if (isset($_COOKIE["PHPSESSID"])) {
                setcookie("PHPSESSID", '', time() - 3600, '/');
            }

            // cookie to nil
            foreach ( $_COOKIE as $key => $value )
            {
                setcookie($key, '', time() - 3600, '/' );
            }

            // destroy session
            session_destroy();
            
        } else {
            print('<h4>session and cookie update. </h4>');

            // session message
            if (!isset($_COOKIE["PHPSESSID"])){
                print('初回の訪問です。セッションを開始します。');
            }else{
                print('セッションは開始しています。<br />');
                //print('セッションIDは '.$_COOKIE["PHPSESSID"].' です。');
                print("クッキーのPHPSESSIDは、 " . $_COOKIE["PHPSESSID"] . ".<br />");
                
                print('クッキーのsession_name()は、 ' . $_COOKIE[session_name()] . ' です。<br />');
                print('session_name()は、 ' . session_name() . ' です。<br />');
                print('session_id()は ' . session_id() . ' です。<br />');
            }
            print('<hr />');
            
            // variable message
            if (!isset($_SESSION["n_visited"])) {
                print('初回の訪問です。<br />');
                $_SESSION["n_visited"] = 0 + 1;
                $_SESSION["first_date"] = date('c');
            } else {
                //
                $n_visited = $_SESSION["n_visited"] + 1;
                print('訪問回数n_visitedは、' . $n_visited . "回です。<br />");
                $_SESSION["n_visited"] = $n_visited;
                //
                if (isset($_SESSION["first_date"])){
                    print('初回の訪問日時は、' . $_SESSION["first_date"] . "でした。<br />");
                }
            }
            print('<hr />');
            
            // something message of cookie and session
            {
                print('セッションの中身は、' .
                      '<pre>' . print_r($_SESSION, TRUE) . '</pre>' .
                      'クッキーの中身は、' .
                      '<pre>' . print_r($_COOKIE, TRUE) . '</pre>' .
                      'です。 <br />');
                setcookie("fs", (string)$_COOKIE["fs"] . (string)"f");
            }        
            print('<hr />');
        }
        ?>

        <a href="session_sample.php">update</a> ,
        <a href="session_sample.php?reset=true">reset_session</a>
    </body>
</html>

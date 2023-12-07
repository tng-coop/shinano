
# 例外

* Exception
    * 実行中に対処可能なエラー
* Error
    * 実行中に対処不可能なエラー

* Exception ハンドルよる exit を行なうと、呼出側でエラー後の後始末を行なうコードが実行できないのはマズそう.
* Error ハンドル処理でも、対処しないならば、トップレベルまで上がって終了するので exit は必要ない.



# `= null` vs `unset`

https://stackoverflow.com/questions/13667137/the-difference-between-unset-and-null

リソース破棄を期待するときに、 `= null` するのが良いか `unset` するのが良いか.
リソース破棄が期待できるのはどちらも同じ.

* `= null` 変数に `null` を代入
    * 若干高速(6%くらい ?)
    * メモリの解放量が少ない
* `unset` 変数が定義ごと削除される
    * 若干低速
    * メモリの解放量が多い


# Web Page 作成の定石

`*.php` による web page 作成の定石(例)

```php

<?php

// include functions
declare(strict_types=1);

include_once(__DIR__ . "/../lib/form_check.php");
include_once(__DIR__ . '/../lib/transactions.php');

// add csrf cookie
// prepare CSRF

// fill variables by POSTed or GETed values

// get user by session and cookie.

// check data from client, communicate with DataBase, make data.
// by request of POST, GET.

if($request_method == "POST"){
    // check CSRF

    // check POSTed form's values

    if(no_problem_post)
    // parepare and execute DB and SQL.

}

if($request_method == "GET"){
    // parepare and execute DB and SQL.
}

// prepare template

$tpl = new TemplateAndConfigs();
$tpl->page_title = "Account Create - Shinano -";

// make contents

$tpl->content_actual = "content html"

// apply and echos template

$tpl->eval_template("template.html");

?>

```

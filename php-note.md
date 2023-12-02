
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

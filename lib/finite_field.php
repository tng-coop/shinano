<?php

/*

有限体および Galois LFSR については詳しくは finite-field.md を参照

----------------------------------------------------------

`F(2²⁴)` の最長周期の生成
-------------------------

`F(2²⁴)` の原始元 `α` を考える.
すると `α·` による巡回は最長周期となり `2²⁴-1`.
その素因数を考えると、

```
2²⁴ - 1 = 3·3·5·7·13·17·241
```

よって、これらの素因数と、
互いに素である素因数を含む累乗数の元を構成することで、
最長の巡回周期を得られる.

たとえば2乗や4乗なら良い

24次の原始多項式は
```
x²⁴ + x²³ + x²² + x¹⁷ + 1
```

 */

## From https://docs.xilinx.com/v/u/en-US/xapp052
## 24次原始多項式 x^24 + x^23 + x^22 + x^17 + 1
$irreducible = 0x1c20001;

## 原始多項式 $irreducible の原始元の逆元
$prim_inv = $irreducible >> 1;

## Galois LFSR を使用
function galois_next24(int $lfsr) : int {
    global $prim_inv;
    $lfsr1 = ($lfsr >> 1) ^ ( (- ($lfsr & 1)) & $prim_inv ); ## 24bit原始元の逆元を参照
    return $lfsr1;
}

?>

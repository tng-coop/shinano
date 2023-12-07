
module GaloisLFSR24 where

import Data.Bits
import Numeric


pprHex :: (Show a, Integral a) => a -> String
pprHex = ("0x" ++) . fillzero . (`showHex` "")
  where fillzero s = replicate (7 - length s) '0' ++ s

pprBin :: (Show a, Integral a) => a -> String
pprBin = ("0b" ++) . fillzero . (`showBin` "")
  where fillzero s = replicate (25 - length s) '0' ++ s

{- | 原始多項式
     From https://docs.xilinx.com/v/u/en-US/xapp052
>>> pprBin irreducible
"0b1110000100000000000000001"
>>> pprHex irreducible
"0x1c20001"
 -}
irreducible :: Word
irreducible = foldl setBit zeroBits [24, 23, 22, 17, 0]

{- | 原始元の逆元
>>> pprBin primInv
"0b0111000010000000000000000"
>>> pprHex primInv
"0x0e10000"
 -}
primInv :: Word
primInv = irreducible `shiftR` 1

next :: Word -> Word
next lfsr =
  (lfsr `shiftR` 1)
  `xor`
  negate (lfsr .&. 1) {- 0: when LSB is 0 , all 1: when LSB is 1 -}
  .&. primInv
{-# INLINEABLE next #-}

list :: Word -> [Word]
list iv = xs  where xs = iv : map next xs

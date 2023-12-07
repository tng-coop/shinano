
import Control.Monad
import Test.QuickCheck (Arbitrary (..), Property, choose, ioProperty, (===), quickCheck)
import System.Process

import qualified GaloisLFSR24


newtype Input = Input Word deriving (Eq, Show, Num)

instance Arbitrary Input where
  arbitrary = Input <$> choose (1, 2^(24 :: Int) - 1)

readNext24PHP' :: Bool -> Word -> IO Word
readNext24PHP' debug w = do
  let php = "include_once(\"finite_field.php\"); echo galois_next24(" ++ show w ++ ");"
  when debug $ putStrLn php
  readIO =<< readProcess "php" ["-r", php] ""

readNext24PHP :: Word -> IO Word
readNext24PHP = readNext24PHP' False

prop_next24PHP :: Input -> Property
prop_next24PHP (Input w) = ioProperty $ do
  php  <- readNext24PHP w
  let hask = GaloisLFSR24.next w
  pure $ hask === php

main :: IO ()
main = quickCheck prop_next24PHP

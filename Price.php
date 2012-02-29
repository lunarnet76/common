<?php
namespace Common {
    class Price  {

        public static function format($price) {
            return number_format($price, 2, '.', '\'');
        }

        public static function formatTotal($price) {
            $price = ceil($price * 10) / 10;
            return number_format($price, 2, '.', '\'');
        }
    }
}
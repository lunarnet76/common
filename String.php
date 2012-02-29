<?php
namespace Common {
    class String {
        public static function normalize($string) {
            $a = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
            $b = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr';
            $string = utf8_decode($string);
            $string = strtr($string, utf8_decode($a), $b);
            $string = strtolower($string);
            return utf8_encode($string);
        }
    }
}

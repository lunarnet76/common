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

                public static function extractContentBetween($content, $from, $to, &$finalPosition = null) {
                        if (false !== $pos = strpos($content, $from)) {

                                if (false !== $pos2 = strpos($content, $to, $pos + strlen($from))) {
                                        $finalPosition = $pos2;
                                        return substr($content, $pos + strlen($from), $pos2 - $pos - strlen($from));
                                }
                        }
                        return false;
                }

                public static function extractContentBetweenWithRepetition($content, $from, $to) {
                        $matches = array();
                        do {
                                $found = self::extractContentBetween($content, $from, $to, $finalPosition);
                                if ($found) {
                                        $matches[] = $found;
                                        $content = substr($content, $finalPosition);
                                }
                        } while ($found !== false);
                        return $matches;
                }

                public static function shorten($text, $size = 32) {

                        $sl = strlen($text);
                        if ($sl < $size)
                                return $text;
                        $text = substr($text, 0, $size);
                        $pos = strrpos($text, ' ');
                        if ($pos !== false)
                                return substr($text, 0, $pos) . '...';
                        return $text;
                }
        }
}

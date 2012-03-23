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
	
	public static function extractContentBetween($what,$from,$to){
		if(false !== $pos = strpos($what,$from)){
			
			if(false !== $pos2 = strpos($what,$to,$pos+strlen($from))){
				return substr($what,$pos+strlen($from),$pos2 - $pos -strlen($from) );
			}
			
		}
		return false;
		
	}
	
	public static function extractContentBetweenWithRepetition(&$what,$from,$to){
		if(false !== $pos = strpos($what,$from)){
			
			if(false !== $pos2 = strpos($what,$to,$pos+strlen($from))){
				$ret = substr($what,$pos+strlen($from),$pos2 - $pos -strlen($from) );;
				$what = substr($what,$pos2+strlen($to));
				return $ret;
			}
			
		}
		return false;
		
	}
    }
}

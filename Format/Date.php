<?php 
namespace Common\Format{
	class Date{
		public static function fromDDMMYYYYtotime($date){
			preg_match('|([0-9]{2}).([0-9]{2}).([0-9]{4})|',$date,$match);
                        $time =  strtotime($match[2].'/'.$match[1].'/'.$match[3]);
                        return $time;
		}
	}
}
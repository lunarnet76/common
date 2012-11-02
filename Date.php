<?php
namespace Common {
    class Date {
        public static function mysqlToDate($date) {// Y-m-d => d/m/Y
		$ex = explode('-',$date);
		return $ex[2].'/'.$ex[1].'/'.$ex[0];
            
        }
    }
}

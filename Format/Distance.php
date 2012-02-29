<?php 
namespace Common\Format{
	class Distance{
		public static function toKm($distanceInMeter){
			$distanceInMeter *= 1000;
			return number_format($distanceInMeter, 0, '', '');
		
		}
		
		public static function fromMeters($distanceInMeter){
			return $distanceInMeter > 1000 ? number_format($distanceInMeter / 1000, 1, '.', '') . 'km' : $distanceInMeter . 'm';
		}
	}
}
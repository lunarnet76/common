<?php
namespace Common\Google {
	class Map {
		const URL = 'http://maps.google.com/maps/';
		const GEOCODE_URL = 'http://maps.googleapis.com/maps/api/geocode/';
		protected $_apiKey;

		public function __construct($apiKey = false)
		{
			$this->_apiKey = $apiKey;
		}

		public function fromAddress($address,$uk=true)
		{	
			$url =self::GEOCODE_URL . 'json?address=' . urlencode($address).'&sensor=false'.($uk?'&region=gb&language=en-GB':'');
			
			$json = $this->_urlToHtml($url);
			return $json;
		}

		public function getLatLng($address)
		{
			$info = json_decode($this->fromAddress($address),true);
			if(empty($info['results'][0]['geometry']['location']))
				return false;
			$info = $info['results'][0]['geometry']['location'];
			return array('lng' => ($info['lng']), 'lat' => ($info['lat']));
		}

		protected function _urlToHtml($url)
		{
			$c = curl_init();

			curl_setopt($c, CURLOPT_URL,$url);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

			$buffer = curl_exec($c);
			curl_close($c);

			return $buffer;
		}
		
		public function latLng2City($lat,$lng){
			
			$json = $this->_urlToHtml(self::GEOCODE_URL.'json?latlng='.$lat.','.$lng.'&sensor=false');
			if(!$json)return false;
			$results = json_decode($json,true);
			
			foreach($results['results'] as $result){
				foreach($result['address_components'] as $addressComponent){
					foreach($addressComponent['types'] as $type){
						if($type == 'locality')
							return $addressComponent['short_name'];
					}
				}
			}
			return false;
		}
	}
}
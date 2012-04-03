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

		public function fromAddress($address)
		{
			$xml = $this->_urlToHtml( self::URL . 'geo?output=xml&key=' . $this->_apiKey . '&q=' . urlencode($address));

			$o = new SimpleXMLElement($xml);

			return $o->Response;
		}

		public function getLatLng($address)
		{
			$info = $this->fromAddress($address);

			$o = $this->fromAddress('lothian road Edinburgh');
			$latLng = explode(',', $o->Placemark[0]->Point->coordinates);

			return array('lng' => deg2rad($latLng[0]), 'lat' => deg2rad($latLng[1]));
		}

		protected function _urlToHtml($url)
		{
			echo $url;
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
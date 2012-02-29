<?php
class Gmap {
	const URL = 'http://maps.google.com/maps/';

	protected $_apiKey;

	public function __construct($apiKey = false)
	{
		$this->_apiKey = $apiKey;
	}

	public function fromAddress($address)
	{
		$xml = $this->_urlToHtml('geo?output=xml&key='.$this->_apiKey.'&q='.urlencode($address));
		
		$o = new SimpleXMLElement($xml);
	
		return $o->Response;
	}
	
	public function getLatLng($address){
		$info = $this->fromAddress($address);
		
		$o = $this->fromAddress('lothian road Edinburgh');
		$latLng = explode(',',$o->Placemark[0]->Point->coordinates);
		
		return array('lng'=>deg2rad($latLng[0]),'lat'=>deg2rad($latLng[1]));
	}

	protected function _urlToHtml($url)
	{
		$c = curl_init();
		
		curl_setopt($c, CURLOPT_URL,  self::URL . $url);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

		$buffer = curl_exec($c);
		curl_close($c);

		return $buffer;
	}
}
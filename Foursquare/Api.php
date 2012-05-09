<?php
namespace Common\Foursquare {
	class Api {
		protected $_id;

		public function __construct($config)
		{
			$this->_id = $config['id'];
			$this->_secret = $config['secret'];
		}

		public function query($query)
		{
			$ch = curl_init();
			
			$separator = strpos($query,'?')!==false?'&':'?';
			$url = 'https://api.foursquare.com/v2/'.$query.$separator.'client_id='.$this->_id.'&client_secret='.$this->_secret.'&v='.date('Ymd');
			
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			$body = curl_exec($ch);
			
			curl_close($ch);
			$json = json_decode($body,true);
			
			if(isset($json['meta']['code']) && $json['meta']['code']!=200)
				return false;
			
			return $json['response'];
		}
	}
}
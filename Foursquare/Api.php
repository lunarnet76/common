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
                        if(isset($_REQUEST['die']))
                        die('foursquare');
                        $start = microtime(true);
			$ch = curl_init();
			
			$separator = strpos($query,'?')!==false?'&':'?';
			$url = 'https://api.foursquare.com/v2/'.$query.$separator.'client_id='.$this->_id.'&client_secret='.$this->_secret.'&v='.date('Ymd');
			
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			/*curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 1000);
			curl_setopt($ch, CURLOPT_TIMEOUT, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1000);*/

			$body = curl_exec($ch);
			
			curl_close($ch);
                        
			$json = json_decode($body,true);
			
			if(isset($json['meta']['code']) && $json['meta']['code']!=200)
				return false;
			$end = round(microtime(true) - $start, 4);
                        if(function_exists('lg'))
                            lg($end,'logs/foursquare_api');
			return $json['response'];
		}
	}
}
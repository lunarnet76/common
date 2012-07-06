<?php
namespace Common\Paypal {
	class Ipn {
		protected $_data;
		protected $_mock = false;

		public function __construct()
		{
			$this->_data = $_POST;
		}

		public function getData()
		{
			return $this->_data;
		}

		public function check()
		{
			if ($this->_mock)
				return true;
			// read the post from PayPal system and add 'cmd'
			$req = 'cmd=_notify-validate';

			foreach ($_POST as $key => $value) {
				$value = urlencode(stripslashes($value));
				$req .= "&$key=$value";
			}

			// post back to PayPal system to validate
			$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
			$fp = fsockopen('ssl://www.paypal.com', 443, $errno, $errstr, 30);

			if (!$fp) {
				return false;
			} else {

				fputs($fp, $header . $req);
				while (!feof($fp)) {
					$res = fgets($fp, 1024);
					if (strcmp($res, "VERIFIED") == 0) {

						return true;
					} else if (strcmp($res, "INVALID") == 0) {
						return false;
					}
				}
				fclose($fp);
			}
		}

		public function mock($filename)
		{
			$this->_mock = true;
			$str = file_get_contents($filename);
			$ex = explode(PHP_EOL, $str);

			foreach ($ex as $line) {
				if (preg_match('|^\s*\[([^\]]*)\]\s*\=\>\s*(.*)|', $line, $match)) {
					$this->_data[$match[1]] = $match[2];
				}
			}
		}
	}
}
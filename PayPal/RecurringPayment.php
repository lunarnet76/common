<?php
namespace Common\Paypal\RecurringPayment {

	function pre($v, $title = false)
	{
		if ($title)
			echo '<h3>' . $title . '</h3>';
		echo '<pre>';
		print_r($v);
		echo '</pre>';
	}
	class RecurringPayment {
		protected $_key;
		protected $_password;
		protected $_signature;
		protected $_version;
		protected $_environment;
		protected $_endPointUrl;
		protected $_cancelUrl;
		protected $_returnUrl;
		protected $_args = array();
		protected $_lastCall = false;

		public function __construct($key, $password, $signature, $returnUrl, $cancelUrl, $environment = 'sandbox', $version = '51.0')
		{
			$this->_key = $key;
			$this->_password = $password;
			$this->_signature = $signature;
			$this->_environment = $environment;
			$this->_version = $version;
			$this->_returnUrl = ($returnUrl);
			$this->_cancelUrl = ($cancelUrl);
			$this->_endPointUrl = "sandbox" === $environment || "beta-sandbox" === $environment ? 'https://api-3t.' . $environment . '.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
			if (!isset($_SESSION['paypalRecurringPayment']))
				$_SESSION['paypalRecurringPayment'] = array();
			$this->_session = &$_SESSION['paypalRecurringPayment'];
		}

		public function __call($method, $args)
		{
			return $this->curl($method, $args[0]);
		}

		/**
		 * redirect to paypal
		 * @param type $paymentAmount
		 * @param type $currencyID // or other currency code ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
		 * @param type $paymentType // Authorization or 'Sale' or 'Order'
		 * @param type $returnURL
		 * @param type $cancelURL
		 * @throws FatalException 
		 */
		public function setCheckoutExpress($paymentAmount, $currencyID = 'GBP', $desc = 'description', $extraArgs = null, $paymentType = 'Authorization')
		{
			$args = array(
			    'AMT' => $paymentAmount,
			    'RETURNURL' => $this->_returnUrl,
			    'CANCELURL' => $this->_cancelUrl,
			    'PAYMENTACTION' => $paymentType,
			    'CURRENCYCODE' => $currencyID,
			    'L_BILLINGTYPE0' => 'RecurringPayments',
			    'L_BILLINGAGREEMENTDESCRIPTION0' => $desc,
			    'L_PAYMENTTYPE0' => 'Any',
			    'L_CUSTOM0' => ''
			);
			if (!empty($extraArgs))
				$args = $args + $extraArgs;
			$response = $this->SetExpressCheckout($args);
			if (empty($response['TOKEN']))
				throw new FatalException('could not get token');
			$url = 'https://www.' . ($this->_environment == 'sandbox' ? 'sandbox.' : '') . 'paypal.com/webscr&cmd=_express-checkout&token=' . ($response['TOKEN']);
			header('Location: ' . $url);
			$this->_session['token'] = $response['TOKEN'];
			$this->_session['description'] = $desc;
			$this->_session['AMT'] = $paymentAmount;
			$this->_session['CURRENCYCODE'] = $currencyID;
			return $response['TOKEN'];
		}

		/**
		 * @param $token result of setCheckoutExpress()
		 * @param $currencyID  ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
		 * @param $startDate date('Y-m-d')
		 * @param $billingPeriod "Month" or "Day", "Week", "SemiMonth", "Year"
		 * @param $billingFreq // combination of this and billingPeriod must be at most a year
		 * @throws Exception when server cannot be contacted
		 * @return boolean succeeded or not
		 */
		public function createSubscription($startDate, $billingPeriod, $billingFreq)
		{
			// api call
			$args = (array(
			    'TOKEN' => ($this->_session['token']),
			    'AMT' => urlencode($this->_session['AMT']),
			    'CURRENCYCODE' => urlencode($this->_session['CURRENCYCODE']),
			    'PROFILESTARTDATE' => $startDate . 'T0:0:0',
			    'BILLINGPERIOD' => urlencode($billingPeriod),
			    'BILLINGFREQUENCY' => urlencode($billingFreq),
			    'DESC' => $this->_session['description']
				));

			$httpParsedResponseArray = $this->CreateRecurringPaymentsProfile($args);

			if ("SUCCESS" == strtoupper($httpParsedResponseArray["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseArray["ACK"])) {
				return true;
			} else {
				return false;
			}
		}

		public function doExpressCheckoutPayment($payerId)
		{
			pre($this->_session);
			return $this->curl('DoExpressCheckoutPayment', array(
				    'TOKEN' => ($this->_session['token']),
				    'PAYERID' => $payerId,
				    'PAYMENTACTION' => 'Authorization',
				    'AMT' => $this->_session['AMT'],
				    'CURRENCYCODE' => $this->_session['CURRENCYCODE']
				));
		}

		public function getLastCall()
		{
			return $this->_lastCall;
		}

		public function curl($methodName, array $args)
		{
			$nvpreq = 'METHOD=' . $methodName . '&VERSION=' . urlencode($this->_version) . '&PWD=' . $this->_password . '&USER=' . $this->_key . '&SIGNATURE=' . $this->_signature;

			foreach ($args as $k => $v)
				$nvpreq.= '&' . $k . '=' . ($v);

			// setting the curl parameters.
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->_endPointUrl);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);

			// turning off the server and peer verification(TrustManager Concept).
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);

			// NVPRequest for submitting to server
			// setting the nvpreq as POST FIELD to curl
			curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

			// getting response from server
			$httpResponse = curl_exec($ch);

			if (!$httpResponse) {
				throw new FatalException(curl_error($ch) . '(' . curl_errno($ch) . ')');
			}

			// Extract the RefundTransaction response details
			$httpResponseAr = explode("&", $httpResponse);

			$httpParsedResponseArray = array();
			foreach ($httpResponseAr as $i => $value) {
				$tmpAr = explode("=", $value);
				if (sizeof($tmpAr) > 1) {
					$httpParsedResponseArray[$tmpAr[0]] = $tmpAr[1];
				}
			}

			if ((0 == sizeof($httpParsedResponseArray)) || !array_key_exists('ACK', $httpParsedResponseArray)) {
				throw new FatalException("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
			}

			$this->_lastCall = $httpParsedResponseArray;
			$this->_lastCall['request'] = $methodName . ':' . $nvpreq;
			return $this->_lastCall;
		}

		public static function test()
		{
			try {
				if (!isset($_SESSION))
					session_start();
				$rp = new RecurringPayment('buyer_1325602144_biz_api1.msn.com', '1325602169', 'Awfo7lgWXyizmP8cuorghcywo7RfAGIt.C5kSn3igUlJz5630rUcKLey', 'http://dev/test/paypal.php?action=return', 'http://dev/test/paypal.php?action=cancel');

				$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : 'set_express_checkout';
				switch ($action) {
					case 'set_express_checkout':
						$rp->setCheckoutExpress(50, 'GBP', 'subscription test');
						break;
					case 'return':
						$rp->doExpressCheckoutPayment($_REQUEST['PayerID']);

						if ($rp->createSubscription(date('Y-n-j'), 'Month', '12')) {
							echo 'subscription ok';
						}
						break;
				}
			} catch (FatalException $e) {
				pre($e);
				pre($rp->getLastCall(), 'setCheckoutExpress');
			}
		}
	}
	class FatalException extends \Exception {
		
	}
}
RecurringPayment::test();

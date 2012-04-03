<?php
namespace Common {
	class Email {
		protected $_to;
		protected $_from = false;
		protected $_subject;
		protected $_template = false;
		protected $_false = false;
		protected $_message = false;
		protected $_viewArgs = array();
		
		public function __construct($to,$subject,$message = false){
			$this->_to = $to;
			$this->_subject = $subject;
			$this->_message = $message;
			$subject = 'Website Change Reqest';
		}
		
		public function setTemplate($path){
			$this->_template = $path;
		}
		
		public function setView($path,array $args = array()){
			$this->_view = $path;
			$this->_viewArgs = $args;
		}
		
		public function render(){
			ob_start();
			if($this->_template)
				require($this->_template.'.php');
			else
				echo renderView();
			return ob_get_clean();
		}
		
		public function renderView(){
			foreach($this->_viewArgs as $k=>$v)
				$$k = $v;
			require($this->_view.'.php');
		}
		
		public function setSender($from){
			$this->_from = $from;
		}
		
		public function send($live = false){
			$headers = '';
			if($this->_from)
				$headers = "From: " . $this->_from . "\r\n";
			//$headers .= "Reply-To: ". strip_tags($_POST['req-email']) . "\r\n";
			//$headers .= "CC: susan@example.com\r\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			
			if($this->_template){
				$message = $this->render();
			}else
				$message = $this->_message;
			
			if($live)
				return mail($this->_to, $this->_subject, $message, $headers);
			else{
				var_dump(array($this->_to, $this->_subject));
				echo $message;
				return true;
			}
		}
	}
}
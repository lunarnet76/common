<?php
namespace Common {
	class Upload {

		public static function error($errorcode, $emptyiserror = false)
		{
			switch ($errorcode) {
				case UPLOAD_ERR_OK:
					$error = false;
					break;
				case 1 :
					$error = 'upload_too_large';
				case 2 :
					$error = 'upload_too_large';
				case 3 :
					$error = 'upload_partial';
				case 4:
					if ($emptyiserror)
						$error = 'upload_none';
					else
						$error = false;
					break;
				case 6 :
					$error = 'upload_notmpdir';
				case 7 :
					$error = 'upload_cantwrite';
				case 8 :
					$error = 'upload_extension';
				case -1:
					break;
				default:
					$error = 'upload_unknown';
			}
			return $error;
		}
		private static $isNormalised = false;

		public static function normalise()
		{
			if (self::$isNormalised)
				return;
			self::$isNormalised = true;

			$ret = array();
			foreach ($_FILES as $index => $infos) {
				foreach ($infos as $k => $v) {
					$_FILES[$k][$index] = $v;
				}
				unset($_FILES[$index]);
			}
			if (isset($_FILES['name'])) {
				foreach ($_FILES['name'] as $index => $infos) {
					self::_normaliseRecursive($_FILES['name'][$index], $k, $_FILES['type'][$index], $_FILES['tmp_name'][$index], $_FILES['error'][$index], $_FILES['size'][$index], $_REQUEST[$index]);
				}
				$_FILES = $_FILES['name'];
			}
		}

		protected static function _normaliseRecursive(&$array, $part, &$arrayType, &$arrayTmpName, &$arrayError, &$arraySize, &$request)
		{
			if (is_array($array)) {
				foreach ($array as $k => $v) {
					if (is_array($v))
						self::_normaliseRecursive($array[$k], $part, $arrayType[$k], $arrayTmpName[$k], $arrayError[$k], $arraySize[$k], $request[$k]);
					else {
						$array[$k] = array(
						    'name' => $v,
						    'type' => $arrayType[$k],
						    'tmp_name' => $arrayTmpName[$k],
						    'error' => $arrayError[$k],
						    'size' => $arraySize[$k],
						);
						$request[$k] = $array[$k];
					}
				}
			} else {
				/* pre($array);
				  $array[$k] = array(
				  'name' => $v,
				  'type' => $arrayType[$k],
				  'tmp_name' => $arrayTmpName[$k],
				  'error' => $arrayError[$k],
				  'size' => $arraySize[$k],
				  ); */
			}
		}
	}
}

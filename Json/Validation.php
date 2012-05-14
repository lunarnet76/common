<?php
namespace Common\Json {
	class Validation {

		public static function againstSchema($json, array $schema, $folder = false,$name = 'root')
		{
			if(!is_string($json))
				throw new ValidationException('json must be a string');
			
			$json = json_decode($json, true);
			if (json_last_error())
				throw new ValidationException('parse_error:' . json_last_error());
			
			self::againstSchemaRecursive($json, $schema, $name, $folder);
		}

		/**
		 * @todo add stringarray and intarray and floatarray
		 * @param type $value
		 * @param array $props
		 * @param type $name
		 * @param type $folder
		 * @throws ValidationException 
		 */
		private static function againstSchemaRecursive($value, array $props, $name = 'root', $folder = false)
		{
			if(!isset($props['type']))
				throw new ValidationException('schema is missign type for element ' . $name);
			foreach ($props as $k => $v) {
				$$k = $v;
				switch ($k) {
					case 'type':
						
						if (($value === null || $value ==='') && isset($props['required']) && ($props['required']))
							throw new ValidationException('element ' . $name . ' is empty but required');
						
						switch ($type) {
							case 'object':
								foreach($value as $k2=>$v2){
									if (!isset($props['properties'][$k2]))
										throw new ValidationException('property ' . $name . '.' . $k2 . ' has not been specified in the schema');
								}			
							case 'array':

								break;
							case 'timestamp':
								if ((!is_string($value) && !is_int($value)) || (!filter_var($value, FILTER_VALIDATE_INT)))
									throw new ValidationException('element ' . $name . ' must be a valid ' . $type . ' , found "' . $value . '"');
								break;
							case 'number':
								if ((!is_string($value) && !is_int($value) && !is_float($value)) || !preg_match('|^[0-9]*(\.[0-9]*)?$|', $value))
									throw new ValidationException('element ' . $name . ' must be a valid ' . $type . ' , found "' . $value . '"');
								break;
							case 'string':
								if(!is_string($value))
									throw new ValidationException('element ' . $name . ' must be a valid ' . $type . ' , found "' . $value . '"');
								break;
						}
						break;
					case 'properties':
						if (is_string($properties) && $properties[0]=='@') {
							ob_start();
							require($folder . substr($properties,1));

							$properties = json_decode(substr(ob_get_clean(), strlen('var $validation=')), true);
							if ($properties == null)
								throw new ValidationException(json_last_error());
						}
						foreach ($properties as $propertyName => $propertyInfo) {
							if (!isset($value[$propertyName])) {
								if (isset($propertyInfo['required']) && $propertyInfo['required']){
									throw new ValidationException('missing element ' . $name . '.' . $propertyName);
								}
								continue;
							}
							switch ($propertyInfo['type']) {
								case 'array':
									if (!is_array($value[$propertyName]))
										throw new ValidationException('element ' . $name . ' must be an array');
									if (isset($propertyInfo['minimum']) && count($value[$propertyName]) < $propertyInfo['minimum'])
										throw new ValidationException('property ' . $propertyName . ' must have at least ' . $propertyInfo['minimum'] . ' elements');
									
									if (isset($propertyInfo['maximum']) && count($value[$propertyName]) > $propertyInfo['maximum'])
										throw new ValidationException('property ' . $propertyName . ' must have a maximum of ' . $propertyInfo['maximum'] . ' elements');

									foreach ($value[$propertyName] as $objI => $obj) {
										if (!is_array($obj))
											throw new ValidationException('element ' . $name . '.' . $propertyName . '.' . $objI . ' must be an object');
										self::againstSchemaRecursive($obj, $propertyInfo, $name . '.' . $propertyName . '[' . ($objI + 1) . 'th]', $folder);
									}
									break;
								case 'object':
									//if (!is_array($value[$propertyName]))
									//	throw new ValidationException('element ' . $name . '.' . $propertyName . ' must be an object');
									//if (isset($propertyInfo['required']) && $propertyInfo['required'] && !count($value[$propertyName]))
									//	throw new ValidationException('element ' . $name . '.' . $propertyName . ' is required was empty');
									break;
								default:
									self::againstSchemaRecursive($value[$propertyName], $propertyInfo, $name . '.' . $propertyName, $folder);
							}
						}
						foreach ($value as $k => $v) {
							if (!isset($properties[$k]))
								throw new ValidationException('property ' . $name . '.' . $k . ' has not been specified in the schema');
						}
						break;
				}
			}
		}
	}
	class ValidationException extends \Exception{}
}

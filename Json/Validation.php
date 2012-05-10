<?php
namespace Common\Json {
	class Validation {

		public static function againstSchema($json, array $schema, $folder = false,$name = 'root')
		{
			$json = @json_decode($json, true);
			if (json_last_error())
				throw new \Exception('parse_error:' . json_last_error());

			self::againstSchemaRecursive($json, $schema, $name, $folder);
		}

		private static function againstSchemaRecursive($value, array $props, $name = 'root', $folder = false)
		{
			foreach ($props as $k => $v) {
				$$k = $v;
				switch ($k) {
					case 'type':
						if ($value === null && isset($props['required']) && ($props['required']))
							throw new \Exception('element ' . $name . ' is required but was empty');
						switch ($type) {
							case 'object':
							case 'array':

								break;
							case 'timestamp':
								if ($value != 0 && (!filter_var($value, FILTER_VALIDATE_INT)))
									throw new \Exception('element ' . $name . ' must be a valid ' . $type . ' , found "' . $value . '"');
								break;
							case 'number':
								if ($value != 0 && !preg_match('|^[0-9]*(\.[0-9]*)?$|', $value))
									throw new \Exception('element ' . $name . ' must be a valid ' . $type . ' , found "' . $value . '"');
								break;
						}
						break;
					case 'properties':
						if (is_string($properties) && $properties[0]=='@') {
							ob_start();
							require($folder . substr($properties,1));

							$properties = json_decode(substr(ob_get_clean(), strlen('var $validation=')), true);
							if ($properties == null)
								throw new \Exception(json_last_error());
						}
						foreach ($properties as $propertyName => $propertyInfo) {
							if (!isset($value[$propertyName])) {
								if (isset($propertyInfo['required']) && $propertyInfo['required'])
									throw new \Exception('missing element ' . $name . '.' . $propertyName);
								continue;
							}
							switch ($propertyInfo['type']) {
								case 'array':
									if (!is_array($value[$propertyName]))
										throw new \Exception('element ' . $name . ' must be an array');
									if (isset($propertyInfo['minimum']) && count($value[$propertyName]) < $propertyInfo['minimum'])
										throw new \Exception('property ' . $propertyName . ' must have at least ' . $propertyInfo['minimum'] . ' elements');

									foreach ($value[$propertyName] as $objI => $obj) {
										if (!is_array($obj))
											throw new \Exception('element ' . $name . '.' . $propertyName . '.' . $objI . ' must be an object');
										self::againstSchemaRecursive($obj, $propertyInfo, $name . '.' . $propertyName . '[' . ($objI + 1) . 'th]', $folder);
									}
									break;
								case 'object':
									if (!is_array($value[$propertyName]))
										throw new \Exception('element ' . $name . '.' . $propertyName . ' must be an object');
									if (isset($propertyInfo['required']) && $propertyInfo['required'] && !count($value[$propertyName]))
										throw new \Exception('element ' . $name . '.' . $propertyName . ' is required was empty');
									break;
								default:
									self::againstSchemaRecursive($value[$propertyName], $propertyInfo, $name . '.' . $propertyName, $folder);
							}
						}
						foreach ($value as $k => $v) {
							if (!isset($properties[$k]))
								throw new \Exception('property ' . $name . '.' . $k . ' has not been specified in the schema');
						}
						break;
				}
			}
		}
	}
}

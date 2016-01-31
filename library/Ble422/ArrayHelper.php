<?php
/**
 * array helper collection with static functions
 * @author BLE 422, ip
 *
 */
class Ble422_ArrayHelper
{
	public static function array_pick($hash, $num) {
		/*
		 * directly returns array instead array keys
		 * note: unsets elements until target size is reached, therefore this array keys/elements will be missing in array key sequence
		 * handles num==1
		 * handles if num>array
		 *
		 * credits:
		 * MadeGlobal
		 30-May-2008 10:16
		 http://php.net/manual/en/function.array-rand.php
		 */
		$count = count($hash);
		if ($num <= 0) return array();
		if ($num >= $count) return $hash;
		$required = $count - $num;
		if ($required == 1) {   //array rand returns the KEY if there is only one item requested so...
			$keys = array(array_rand($hash, $required));
		} else {
			$keys = array_rand($hash, $required);
		}
		foreach ($keys as $k) unset($hash[$k]);
		return $hash;
	}

	/**
	 * gets duplicate values of array and returns key1, key2 and value,
	 * but only returns one element per pair, not the reverse pair!
	 *
	 * original developed to get duplicate filenames with row numbers out of CSV
	 * @param $assocArray assocative array
	 * @param $caseSensitive
	 * @return unknown_type
	 */
	public static function getDuplicates($assocArray, $caseSensitive = TRUE) {
			
		//create cloned arrays for identifing and unsetting duplicate keys
		foreach ($assocArray as $key => $val) {
			$clone_assocArray1[$key] = $val;
		}
		foreach ($assocArray as $key => $val) {
			$clone_assocArray2[$key] = $val;
		}

		foreach($clone_assocArray1 as $key1 => &$value1) {
			foreach($clone_assocArray2 as $key2 => &$value2) {
				//ignore identical index of clones
				if ($key1 != $key2) {
					if ($caseSensitive) {
						//case sensitive
						if ($value1 == $value2) {
							$duplicates[] = array( 'key1' => $key1,
                                               'key2' => $key2,
                                               'value' => $value1);
							//unset duplicate elements to delete reverse pairs and make function faster
							unset($clone_assocArray2[$key2]);
							unset($clone_assocArray1[$key2]);
						}
					} else {
						//case non-sensitive
						if (strtolower($value1) == strtolower($value2)) {
							$duplicates[] = array( 'key1' => $key1,
                                               'key2' => $key2,
                                               'value' => $value1);
							//unset duplicate elements to delete reverse pairs and make function faster
							unset($clone_assocArray2[$key2]);
							unset($clone_assocArray1[$key2]);
						}
					}
				}
			}
		}
		return $duplicates;
	}

	public static function convertToString($array)
	{
		$output = print_r($array, TRUE);
		return $output;
	}

	/**
	 * XXX !!!!!!!!!!!!!!!!!funktioniert nicht!!!!!!!!!!!!!!!!!!!!!!!!!
	 * @author BLE 621, ip
	 * altered php array to XML function just to get valid XML keys/values for REST service
	 *
	 * original function needs SimpleXML, this part is outcommented
	 * credits:
	 * http://snipplr.com/view.php?codeview&id=3491
	 * Posted By
	 djdykes on 08/08/07
	 extended by:
	 * Posted By: visual77 on September 24, 2008
	 * Posted By: hradek on October 1, 2008
	 * Posted By: Jpsy on June 4, 2009
	 *
	 *
	 * @param array $data
	 * @param string $rootNodeName - what you want the root node to be - defaultsto data.
	 * @param string $xml - should only be used recursively, this is the key to inner array
	 * @param string $breakAssociativeArrays - BLE custom to get only static keys
	 * @return string XML
	 */
	public static function getArrayForXml($data, $rootNodeName = 'data', &$xml=null, $breakAssociativeArrays = FALSE)
	{
		// turn off compatibility mode as simple xml throws a wobbly if you don't.
		//		if (ini_get('zend.ze1_compatibility_mode') == 1)
		//		{
		//			ini_set ('zend.ze1_compatibility_mode', 0);
		//		}

		if (is_null($xml))
		{
			//            $xml = simplexml_load_string("");
		}

		// loop through the data passed in.
		foreach($data as $key => $value)
		{
			// no numeric keys in our xml please!
			if (is_numeric($key))
			{
				// make string key...
				$key = "unknownNode_". (string) $key;
			}

			// delete any char not allowed in XML element names
			$key = preg_replace('/[^a-z0-9\-\_\.\:]/i', '', $key);

			// if there is another array found recrusively call this function
			if (is_array($value))
			{
				//                $node = $xml->addChild($key);
				// recrusive call.
				//				ArrayToXML::toXml($value, $rootNodeName, $node);
				if ($breakAssociativeArrays) {
					$arrayForXml[$key] = self::getArrayForXml($value, $rootNodeName, $key, TRUE);
				} else {
					$arrayForXml[$key] = self::getArrayForXml($value, $rootNodeName, $key);
				}
			}
			else
			{
				// add single node.
				$value = htmlentities($value);
				//                $xml->addChild($key,$value);
				if ($breakAssociativeArrays) {
					$arrayForXml['key'] = $key;
					$arrayForXml['value'] = $value;
				} else {
					$arrayForXml[$key] = $value;
				}
			}

		}
		// pass back as string. or simple xml object if you want!
		//        return $xml->asXML();
		return $arrayForXml;
	}
	
	//aus php klappt auch nicht!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	public static function object_to_array($mixed) {

		if(is_array($mixed)) {
			$new = array();
			foreach($mixed as $key => $val) {
				$key = preg_replace('/[^a-z0-9\-\_\.\:]/i', '', $key);
				$new[$key] = self::object_to_array($val);
			}
		}
		else $new = htmlentities($new);
		return $new;
	}
}
<?php

class Ble422_Guid {
	
	private $guid;
	
	public function __construct() {
		$rawid = strtoupper(md5(uniqid(rand(), true)));
		
		$this->guid = substr($rawid, 0, 8).'-'
			    .substr($rawid, 8, 4).'-'
			    .substr($rawid,12, 4).'-'
			    .substr($rawid,16, 4).'-'
			    .substr($rawid,20,12);
	}
	public static function getGuid() {
		$rawid = strtoupper(md5(uniqid(rand(), true)));
		
		return (substr($rawid, 0, 8).'-'
			    .substr($rawid, 8, 4).'-'
			    .substr($rawid,12, 4).'-'
			    .substr($rawid,16, 4).'-'
			    .substr($rawid,20,12));
	}
	public function __toString()
	{
		return $this->guid;
	}
	public static function stringToGuid($string)
	{
		if (preg_match('^(\{{0,1}([0-9a-fA-F]){32})$^',$string)) {
				$sA = str_split($string);
				$newGuid =	$sA[0].$sA[1].$sA[2].$sA[3].$sA[4].$sA[5].$sA[6].$sA[7]."-"
							.$sA[8].$sA[9].$sA[10].$sA[11]."-"
							.$sA[12].$sA[13].$sA[14].$sA[15]."-"
							.$sA[16].$sA[17].$sA[18].$sA[19]."-"
							.$sA[20].$sA[21].$sA[22].$sA[23].$sA[24].$sA[25].$sA[26].$sA[27].$sA[28].$sA[29].$sA[30].$sA[31];
		}
		return $newGuid;
	}
	
}


?>
<?php
/**
 * logs arrays with current time
 * creates or appends the log entries in given file
 * @author BLE 422, IP
 * 
 *
 */
class Ble422_ArrayLogger
{
	private $log;

	function __construct($filename)
	{
//		$timestamp = time();
//		$date = date("Ymd",$timestamp);
//		$time = date("His",$timestamp);
//		$this->log = fopen ($date.'_'.$time.'_'.'import_log.txt', 'w');
		$this->log = fopen($filename, 'a');
	}

	function __destruct()
	{
		fclose ($this->log);
	}
	
	/**
	 * @param $array the array to log
	 * @param $info optional, informal line
	 * @return unknown_type
	 */
	public function log($array, $info = NULL)
	{
		$timestamp = time();
		$date = date("Y.m.d",$timestamp);
		$time = date("H:i:s",$timestamp);
		fwrite ($this->log, $date.'_'.$time."\r\n");
		if (isset($info)) {
			fwrite ($this->log, $info."\r\n");
		}
		$output = print_r($array, TRUE);
		fwrite ($this->log, $output."\r\n");
	}	
}
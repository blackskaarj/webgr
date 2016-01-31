<?php
/**
 *
 * @author BLE 422, IP, NR
 * depending PHP 4.3.0 features for fgetcsv()
 * to do: replace length with a "get longest line" function
 * TODO check duplicate header
 *
 * credits:
 * class workprint by
 * myrddin at myrddin dot myrddin
 * 18-Jul-2006 04:14
 * http://us2.php.net/manual/en/function.fgetcsv.php
 *
 */

class Ble422_CsvParser
{
	//TODO return the duplicateHeaders as array
	private $fp;
	private $parse_header;
	private $header;
	private $delimiter;
	private $length;
	private $trim;

	/**
	 *
	 * @param $file_name
	 * @param $parse_header
	 * @param $delimiter
	 * @param $length
	 * @param $trim trims the cells and headings, BLE mod
	 * @return unknown_type
	 */
	function __construct($file_name, $parse_header=false, $delimiter="\t", $length=8000, $trim = TRUE)
	{
		$this->fp = fopen($file_name, "r");
		if ($this->fp == FALSE) {
			throw new Exception ("Error: could not open file $file_name");
		}
		$this->parse_header = $parse_header;
		$this->delimiter = $delimiter;
		$this->length = $length;
		$this->trim = $trim;
		//$this->lines = $lines;

		if ($this->parse_header)
		{
			$this->header = fgetcsv($this->fp, $this->length, $this->delimiter);
			if ($this->header == FALSE) {
				throw new Exception ('Error: could not read headers');
			}
			if ($this->trim) {
				//changes reference
				foreach ($this->header as &$head) {
					$head = trim($head);
				}
			}
		}

	}

	function __destruct()
	{
		if ($this->fp)
		{
			fclose($this->fp);
		}
	}

	/**
	 *
	 * @param $max_lines maximal no. of lines to read
	 * @return array array[index][string columnname]
	 */

	function get($max_lines=0)
	{
		//if $max_lines is set to 0, then get all the data

		$data = array();

		if ($max_lines > 0)
		$line_count = 0;
		else
		$line_count = -1; // so loop limit is ignored

		//set enclosure to double quotation mark
		while ($line_count < $max_lines && ($row = fgetcsv($this->fp, $this->length, $this->delimiter, "\"")) !== FALSE)
		{
			if ($this->parse_header)
			{
				foreach ($this->header as $i => $heading_i)
				{
					if ($this->trim) {
						$row[$i] = trim($row[$i]);
					}
					$row_new[$heading_i] = $row[$i];
				}
				$data[] = $row_new;
			}
			else
			{
				if ($this->trim) {
					$row = trim($row);
				}
				$data[] = $row;
			}

			if ($max_lines > 0)
			$line_count++;
		}
		return $data;
	}

	function getHeadings()
	{
		return $this->header;
	}
}
<?php
class Ble422_Tool {
	public function createGuidWithCom()
	{
		$guid = com_create_guid();

		//entfernt geschweifte Klammern der GUID in Microsoft-Schreibweise
		//returns GUID with minus-signs
		if (substr($guid, 0, 1) == "{" && substr($guid, 37, 1) == "}")
		{
			$guid = substr($guid, 1, -1);
		}
		if (strlen($guid) == 36)
		{
			return $guid;
		}
		return -1;
	}

	//returns GUID without minus-signs
	public function createGuidWithPhp()
	{
		$guid = md5 (uniqid (rand()));
		if (strlen($guid) == 32)
		{
			return $guid;
		}
		return -1;
	}

	/**
	 *
	 * @param $row row number starting at 0
	 * @param $col column number starting at 0
	 * @return unknown_type
	 * credits:
	 * http://stackoverflow.com/questions/688869/convert-a-decimal-into-an-alphabetic-column-id
	 * Here's the solution in PHP, taken pretty much directly from this answer by Graham
	 edited Mar 30 at 1:41
	 answered Mar 27 at 15:00
	 nickf
	 * referes to http://stackoverflow.com/questions/181596/how-to-convert-a-column-number-eg-127-into-an-excel-column-eg-aa/182924#182924
	 *
	 * Edit:
	 If you're using the PHP PEAR module Spreadsheet Excel Writer, then it has this function built-in:
	 Spreadsheet_Excel_Writer::rowcolToCell
	 I probably shoulda just RTFM, hey...
	 */
	public function rowCol2Cell($row, $col) {
		$dividend = $col + 1;
		$columnName = '';

		while ($dividend > 0) {
			$modulo = ($dividend - 1) % 26;
			$columnName = chr(65 + $modulo) . $columnName;
			$dividend = (int)(($dividend - $modulo) / 26);
		}

		return $columnName . ($row + 1);

		// rowCol2Cell(0, 0) = "A1"
		// rowCol2Cell(0, 1) = "B1"
		// rowCol2Cell(0,26) = "AA1"
	}

	/**
	 *
	 * @param $col column number starting at 0
	 * @return unknown_type
	 * modified to only column, based on rowCol2Cell($row, $col)
	 * credits:
	 * http://stackoverflow.com/questions/688869/convert-a-decimal-into-an-alphabetic-column-id
	 * Here's the solution in PHP, taken pretty much directly from this answer by Graham
	 edited Mar 30 at 1:41
	 answered Mar 27 at 15:00
	 nickf
	 * referes to http://stackoverflow.com/questions/181596/how-to-convert-a-column-number-eg-127-into-an-excel-column-eg-aa/182924#182924
	 *
	 * Edit:
	 If you're using the PHP PEAR module Spreadsheet Excel Writer, then it has this function built-in:
	 Spreadsheet_Excel_Writer::rowcolToCell
	 I probably shoulda just RTFM, hey...
	 */
	public function col2SpreadsheetCol($col) {
		$dividend = $col + 1;
		$columnName = '';

		while ($dividend > 0) {
			$modulo = ($dividend - 1) % 26;
			$columnName = chr(65 + $modulo) . $columnName;
			$dividend = (int)(($dividend - $modulo) / 26);
		}

		return $columnName;

		// col2SpreadsheetCol(0) = "A"
		// col2SpreadsheetCol(1) = "B"
		// col2SpreadsheetCol(26) = "AA"
	}
}
?>
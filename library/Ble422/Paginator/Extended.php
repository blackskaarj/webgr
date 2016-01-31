<?php
class Ble422_Paginator_Extended extends Zend_Paginator  {

	private $translator;
	private $header;
	private $url;

	/**
	 *
	 * @var Zend_Db_Select
	 */
	private $select;

	/**
	 * Konstruktor
	 *
	 * @param Zend_Db_Select $select
	 * @param Zend_Controller_Request_Abstract $request
	 * @param Zend_Translate $translator
	 */
	public function __construct(Zend_Db_Select $select , Zend_Controller_Request_Abstract $request , Zend_Translate $translator = null)
	{
		$paginatorAdapter = new Zend_Paginator_Adapter_DbSelect($select);
		$this->select = $select;
		$this->translator = $translator;
		$this->url = new Ble422_Urls($request);
		parent::__construct($paginatorAdapter);
	}//ENDE: function ...(...)

	/**
	 * gibt die Default-URL zurück
	 *
	 * @return Ble422_Urls $this->url
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * sortiert nach Spaltenname der durch den Paginator dargestellten
	 * Tabelle
	 *
	 * @param string $columnName
	 */
	public function orderBy($columnName){
		if ($this->isRawcolumn($columnName) ) {
			$this->select->order($columnName);
		}//ENDE: if()
	}

	/**
	 * Setzt die Spaltenüberschriften in der Paginatortabelle
	 *
	 * @param array $headerArray
	 */
	public function setHeader($headerArray) {
		if (is_array($headerArray)) {
			$this->header = $headerArray;
		} else {
			throw new Exception('setHeaders benötigt ein asso. Array als Parameter.');
		}//ENDE: else ==> if()
	}//ENDE: function ...

	/**
	 * Überprüft, ob der übergebene String eine Spaltenüberschrift/Fieldname
	 * in der Datenbanktabelle ist
	 *
	 * @param string $rawColumName
	 * @return boolean
	 */
	private function isRawcolumn($rawColumName){
		$isColumn = false;
		foreach ($this->header as $column) {
			if (strtoupper($column['raw'])==strtoupper($rawColumName)) {
				$isColumn = true;
			}//ENDE: if()
			;
		}//ENDE: foreach ($array as $wert)
		return $isColumn;
	}

	/**
	 * Gibt ein assoziatives Array zurück, das die Spaltenüberschriften der Tabelle
	 * in Rohform (Key 'raw') und in der anzuzeigenden Form (Key 'name') enthält
	 *
	 * @return array
	 */
	public function getHeader()
	{
		if (isset($this->translator)) {
			$lang = $this->translator->getAdapter()->getLocale();
			$headerArray = array();
	
			foreach ($this->header as $column) {
				$raw = $column['raw'];
				$name = $column['lang_'.strtolower($lang)];
				array_push($headerArray,array(  'raw'=>$raw,
	                                            'name'=>$name));
			}//ENDE: foreach ($array as $wert);
		} else {
			$headerArray = array();
			foreach ($this->header as $column) {
				$raw = $column['raw'];
				$name = $column['name'];
				array_push($headerArray,array(  'raw'=>$raw,
	                                            'name'=>$name));
			}//ENDE: foreach ($array as $wert);
		}
		return $headerArray;
	}//ENDE: function ...(...)

	/**
	 * Setzt die Headerdaten einsprachig entsprechend des 
	 * internen Select-Objekts
	 *
	 */
	public function setDefaultHeaderdata()
	{
		$selectpart = $this->select->getPart(Zend_DB_Select::COLUMNS);

		for($i = 0; $i < count($selectpart); $i++)
		{
			If(($columnname=$selectpart[$i][1]) != '*')
			{
				$defaultHeaderData[] = array(	'raw'=>$columnname,
												'lang_xx'=>$columnname,		
												'lang_de'=>$columnname, 
												'lang_en'=>$columnname);
			}
			Else
			{
				throw new Exception('Das Select-Objekt enthält keine Spaltennamen. Es kann kein Default-Header gesetzt werden.');
			}
		}
		$this->header = $defaultHeaderData;
	}
}//ENDE: class ...
?>
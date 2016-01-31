<?php

class Default_Form_Element_KeyTableSelect extends Zend_Form_Element_Select {
	
	public function __construct($spec = '',$options = '') {
		parent::__construct($spec,$options);
		
		$table = new KeyTable();
		$select = $table->select();
		$select->order(KeyTable::COL_FILENAME);
		$rowset = $table->fetchAll($select);
		$array = $rowset->toArray();
		$optArray = array(null=>'Please select');
		
		foreach ($array as $value) {
			$optArray = $optArray + array($value[KeyTable::COL_ID]=>$value[KeyTable::COL_FILENAME].' ('.$value[KeyTable::COL_NAME].')');
		}
		$this->setMultiOptions($optArray);
	}
}

?>
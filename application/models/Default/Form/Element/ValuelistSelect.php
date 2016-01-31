<?php

class Default_Form_Element_ValuelistSelect extends Zend_Form_Element_Select
{	
	public function __construct($attributId, $spec = '',$options = '') {
		parent::__construct($spec,$options);
		
		$table = new ValueList();
		$select = $table->select();
		$select->order(ValueList::COL_NAME);
		$select->where(ValueList::COL_ATTRIBUTE_DESCRIPTOR_ID . "= ?",$attributId);
		$rowset = $table->fetchAll($select);
		$array = $rowset->toArray();
		$optArray = array(null=>'Please select');
		
		foreach ($array as $value) {
			$optArray = $optArray + array($value[ValueList::COL_ID]=>$value[ValueList::COL_NAME]);
		}
		$this->setMultiOptions($optArray);
	}
}
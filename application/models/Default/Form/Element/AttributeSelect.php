<?php
//TODO show only attributes not already used in entity (CE)
//		use a parameter array: values to substract from list 
class Default_Form_Element_AttributeSelect extends Zend_Form_Element_Select {
	public function __construct($spec = '',$options = '', $group = '') {
		parent::__construct($spec,$options);
		
		$table = new AttributeDescriptor();
		$select = $table->select();
		$select->order(AttributeDescriptor::COL_NAME);
		if ($group != '') {
			$select->where(AttributeDescriptor::COL_GROUP . "= ?",$group);
		}
		$rowset = $table->fetchAll($select);
		$array = $rowset->toArray();
		$optArray = array(null=>'Please select');
		
		foreach ($array as $value) {
			$optArray = $optArray + array($value[AttributeDescriptor::COL_ID]=>$value[AttributeDescriptor::COL_NAME]);
		}
		$this->setMultiOptions($optArray);
	}
}
<?php
/*
 * element that shows two text fields for "from xyz to xyz" searches
 * name = input name as array 
 */
class Default_Form_Element_Between extends Zend_Form_Element {
	public function __construct($spec = '',$options = '') {
		parent::__construct($spec,$options);
		
//		$table = new AttributeDescriptor();
//		$select = $table->select();
//		$select->order(AttributeDescriptor::COL_NAME);
//		$select->where(AttributeDescriptor::COL_SHOW_IN_LIST . "= ?", 1);
//		if ($group != '') {
//			$select->where(AttributeDescriptor::COL_GROUP . "= ?",$group);
//		}
//		$rowset = $table->fetchAll($select);
//		$array = $rowset->toArray();
//		$optArray = array(null=>'Please select');
//		
//		foreach ($array as $value) {
//			$optArray = $optArray + array($value[AttributeDescriptor::COL_ID]=>$value[AttributeDescriptor::COL_NAME]);
//		}
//		$this->setMultiOptions($optArray);
		
		$from = new Zend_Form_Element_Text($spec);
		$this->setIsArray(TRUE);
		$to = new Zend_Form_Element_Text($spec);
		$this->setIsArray(TRUE);
		$this->add($from);
		$this->add($to); 
		
		//$this->
	}
}
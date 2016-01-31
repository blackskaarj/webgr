<?php

class Default_Form_Element_ExpertiseMulticheckbox extends Zend_Form_Element_MultiCheckbox
{
	public function __construct($spec = '',$options = '') {
		parent::__construct($spec,$options);
		
		$table = new Expertise();
		$select = $table->select();
		$select->order(Expertise::COL_SPECIES);
		$rowset = $table->fetchAll($select);
		$array = $rowset->toArray();
		$optArray = array();
		
		foreach ($array as $value) {
			$merged = $value[Expertise::COL_ID] . ',' . $value[Expertise::COL_AREA] . ',' . $value[Expertise::COL_SPECIES] . ',' . $value[Expertise::COL_SUBJECT];
			$optArray = $optArray + array($value[Expertise::COL_ID]=>$merged);
		}
		$this->setMultiOptions($optArray);
	}
}
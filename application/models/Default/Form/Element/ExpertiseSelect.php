<?php

class Default_Form_Element_ExpertiseSelect extends Zend_Form_Element_Select {
	
	public function __construct($spec = '',$options = '') {
		parent::__construct($spec,$options);
		
//		$table = new Expertise();
//		$select = $table->select();
//		$select->order(Expertise::COL_SPECIES);
//		//$select->where(ValueList::COL_ATTRIBUTE_DESCRIPTOR_ID . "= ?",$attributId);
//		$rowset = $table->fetchAll($select);
		
		$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
        $select = $dbAdapter->select();
        $select->from(Expertise::TABLE_NAME);
        $select->joinLeft(array('valSpec' => ValueList::TABLE_NAME),
                          Expertise::COL_SPECIES . ' = ' . 'valSpec.' . ValueList::COL_ID,
                          array('valSpec' => ValueList::COL_VALUE));
        $select->joinLeft(array('valSubj' => ValueList::TABLE_NAME),
                          Expertise::COL_SUBJECT . ' = ' . 'valSubj.' . ValueList::COL_ID,
                          array('valSubj' => ValueList::COL_VALUE));                          
        $array = $dbAdapter->fetchAll($select);
        //$array = $rowset->toArray();
		
		$optArray = array(null=>'Please select');
		foreach ($array as $value) {
			$merged = $value[Expertise::COL_ID] . ',' . $value[Expertise::COL_AREA] . ',' . $value['valSpec'] . ',' . $value['valSubj'];
			$optArray = $optArray + array($value[Expertise::COL_ID]=>$merged);
		}
		$this->setMultiOptions($optArray);
	}
}

?>
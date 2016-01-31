<?php
class Admin_Form_Valuelist extends Zend_Form {
	public function __construct($atDeId) {
		parent::__construct();
		
		$table = new ValueList();
		$select = $table->select();
		$select->where(ValueList::COL_ATTRIBUTE_DESCRIPTOR_ID. "= ?", $atDeId, 'int');
		$rowset = $table->fetchAll($select);
		$array = $rowset->toArray();
		
		foreach ($array as $valiDataset) {
			$this->addElement(	'text',
								'VALI_'.$valiDataset[ValueList::COL_ID],
								array(  'label' => 'Current value: '.$valiDataset[ValueList::COL_NAME],
										'value' => $valiDataset[ValueList::COL_VALUE],
                           				'required' => true,
        								'validators' => array(new Zend_Validate_Alnum(true))));								
		}
		
		$this->setAction('/admin/valuelist/update/'.AttributeDescriptor::COL_ID."/".$atDeId);
		$this->addElement('submit', 'submit', array(  'label' => 'Update'));
	}
}
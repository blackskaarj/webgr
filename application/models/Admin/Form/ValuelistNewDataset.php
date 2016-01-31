<?php
class Admin_Form_ValuelistNewDataset extends Zend_Form {
	public function __construct($atDeId) {
		parent::__construct();
		$this->addElement(	'text',
							ValueList::COL_VALUE,
							array(  'label' => 'New entry:',
                      				'required' => true,
        							'validators' => array(new Zend_Validate_Alnum(true))));
		$this->setAction('/admin/valuelist/insert/'.AttributeDescriptor::COL_ID."/".$atDeId);
		$this->addElement('submit', 'submit', array(  'label' => 'Add'));
	}
}
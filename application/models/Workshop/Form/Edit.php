<?php

class Workshop_Form_Edit extends Zend_Form 
{
	public function __construct() 
	{
		parent::__construct();
		
		$this->addElement('hidden', Workshop::COL_ID, array(	'required'=>true,
														 	 	'validators'=>array('Int')));
		
		$this->addElement('text', Workshop::COL_NAME, array( 'label'=>'Name:',
															 'required'=>true,
														 	 'validators'=>array(new Zend_Validate_Alnum(true))));
		
		$this->addElement(new Default_Form_Element_ValuelistSelect(601,Workshop::COL_LOCATION,
																		array(	'label'=>'Location:',
																				'required'=>true,
																				'validators'=>array(new Zend_Validate_Alnum(true)))));
																		
		$this->addElement('text', Workshop::COL_START_DATE, array(	'label'=>'Startdate (YYYY-MM-DD):',
																	'required'=>true,
																 	'validators'=>array('Date')));

		$this->addElement('text', Workshop::COL_END_DATE, array(	'label'=>'Enddate (YYYY-MM-DD):',
																	'required'=>true,
															 	 	'validators'=>array('Date')));
		
		$this->addElement(new Default_Form_Element_ValuelistSelect(603,Workshop::COL_HOST_ORGANISATION,
																		array(	'label'=>'Institution:',
																				'required'=>true,
																				'validators'=>array(new Zend_Validate_Alnum(true)))));
																											
		$this->addElement('text', User::COL_USERNAME, array(	'label'=>'Manager:','readonly'=>'readonly'));																							
		
		$this->addElement('hidden', Workshop::COL_USER_ID, array(	'required'=>true,
																 	'validators'=>array('Int')));
		
//		$this->getElement(Workshop::COL_USER_ID)->setDecorators(array(
//            'ViewHelper',
//            array('Description',array('escape'=>false,'tag'=>'span')), //escape false because I want html output
//        ));
//		$this->getElement(Workshop::COL_USER_ID)->setDescription('<a href="/user/search/index/followAction/setManager">change</a>');
		
        $this->addElement('submit', 'setManager', array('label'=>'change ws manager'));
		$this->addElement('submit', 'save', array('label'=>'Save'));
		
		$this->addElement('hidden','Token');
		//#####################new###################################
        $this->setDecorators(array(
                'FormElements',
        array('HtmlTag', array('tag' => 'table', 'class' => 'login_form')),
        array('Description', array('placement' => 'prepend')),
                'Form'
                ));
                $this->setElementDecorators(array(
            'ViewHelper',
            'Errors',
                array(  'decorator' => array('td' => 'HtmlTag'),
                        'options' => array('tag' => 'td')),
                array(  'Label', array('tag' => 'td')),
                array(  'decorator' => array('tr' => 'HtmlTag'),
                        'options' => array('tag' => 'tr')),
                ));
        //###########################################################
	}
	
	public function setValues(array $elementNamesValues)
	{
		foreach ($elementNamesValues as $elementName => $elementValue) {
			$elementName = str_replace('-','',$elementName);
			$element = $this->getElement($elementName);
			if ($element !== null) {
				$element->setValue($elementValue);
			}
		}
	}
}


?>
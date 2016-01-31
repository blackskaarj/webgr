<?php

class User_Form_Edit extends Zend_Form {
	
	const PASSWORD_CLONE = 'passwordClone';
	
	public function __construct($forSearch=false) {
		
		parent::__construct();
		
		if ($forSearch) {
			$radioKind = new Zend_Form_Element_Radio('kind');
			$radioKind->addMultiOptions(array('and'=>'and',
										'or'=>'or'));
			$radioKind->setValue('and');
			$radioKind->setLabel('Search field combination:');
			$radioKind->setRequired(true);
			
			$this->addElement($radioKind);
		}
		
		$valiAlphaWhiteSpace = new Zend_Validate_Alpha(true);
		
		$this->addElement('hidden', User::COL_ID, array('required'=>true));
		
		$this->addElement('text', User::COL_USERNAME, array('label'=>'Username = e-mail adress:',
															'required'=>true,
															'validators'=>array(
                												'EmailAddress',
                												array('StringLength', false, array(6, 40))
            													)));
		
		$this->addElement('text', User::COL_FIRSTNAME, array('label'=>'First name:',
															 'required'=>true,
														 	 'validators'=>array($valiAlphaWhiteSpace)));
		
		$this->addElement('text', User::COL_LASTNAME, array('label'=>'Last name:',
															 'required'=>true,
														 	 'validators'=>array($valiAlphaWhiteSpace)));
		
		$this->addElement('password', User::COL_PASSWORD, array('label'=>'Password:',
															    'required'=>true,
																'validators'=>array(
																	array('StringLength', false, array(6, 20))
																	)));
		
		$this->addElement('password', self::PASSWORD_CLONE, array('label'=>'Repeat Password:',
															      'required'=>true));

		//due to the rule username=e-mail adress, this field is not viewed
/*		$this->addElement('text', User::COL_EMAIL, array(	'label'=>'E-mail adress:',
													     	'required'=>true,
														 	'validators'=>array('EmailAddress')));*/
	
		$institutionSelect = new Default_Form_Element_ValuelistSelect(603,User::COL_INSTITUTION);
		$institutionSelect->setRequired(true);
		$institutionSelect->setLabel('Institution:');
		$this->addElement($institutionSelect);
		
		$this->addElement('text', User::COL_STREET, array(	'label'=>'Street:',
															'validators'=>array(new Zend_Validate_Alnum(true))));

		$this->addElement('text', User::COL_CITY, array('label'=>'City:',
														'validators'=>array($valiAlphaWhiteSpace)));

		$this->addElement('text', User::COL_PHONE, array('label'=>'Phone number:'
														));
		
		$this->addElement('text', User::COL_FAX, array('label'=>'Faxsimile number:'
													     ));

		$countrySelect = new Default_Form_Element_ValuelistSelect(602,User::COL_COUNTRY);
		$countrySelect->setRequired(true);
		$countrySelect->setLabel('Country:');
		$this->addElement($countrySelect);
															     
		$this->addElement('checkbox', User::COL_ACTIVE, array('label'=>'Active:'
													     ));

		$this->addElement('hidden', User::COL_GUID);													     
													     
		$this->addElement('submit', 'submit', array('label'=>'Submit'
													     ));
													     
		$this->addElement('hidden','Token');
		
		$this->setElementFilters(array('StringTrim'));
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
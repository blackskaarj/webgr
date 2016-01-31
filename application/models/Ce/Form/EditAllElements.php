<?php
class Ce_Form_EditAllElements extends Zend_Form {
	
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
		
		$this->addElement('hidden', CalibrationExercise::COL_ID, array('required'=>true));
		$this->addElement('hidden', CalibrationExercise::COL_WORKSHOP_ID, array('required'=>true));
		
		$valiAlphaWhiteSpace = new Zend_Validate_Alpha(true);
		$valiAlnumWhiteSpace = new Zend_Validate_Alnum(true);
		
		$this->addElement('text', CalibrationExercise::COL_NAME, array('label'=>'Calibration exercise name:',
																'required'=>true,
																'validators'=>array($valiAlnumWhiteSpace)));

		$this->addElement('text', CalibrationExercise::COL_DESCRIPTION, array('label'=>'Description:',
																'required'=>true,
																'validators'=>array($valiAlnumWhiteSpace)));
		
		$this->addElement(new Default_Form_Element_KeyTableSelect(CalibrationExercise::COL_KEY_TABLE_ID,
																		array(	'label'=>'Protocol:',
																				'required'=>true,
																				'validators'=>array($valiAlnumWhiteSpace))));
																		
		$this->addElement(new Default_Form_Element_ExpertiseSelect(CalibrationExercise::COL_EXPERTISE_ID,
																		array(	'label'=>'Expertise:',
																				'required'=>true,
																				'validators'=>array($valiAlnumWhiteSpace))));		
		
		$this->addElement('checkbox', CalibrationExercise::COL_COMPAREABLE, array('label'=>'Show comparable other user/group annotations/references:',
																'required'=>true));

		$this->addElement('checkbox', CalibrationExercise::COL_RANDOMIZED, array('label'=>'Allow adding images to image set at random:',
																'required'=>true));
		
		$this->addElement('submit', 'save', array('label'=>'Save'
													     ));
													     
		$this->addElement('hidden','Token');
		$this->addElement('hidden',CalibrationExercise::COL_IS_STOPPED);
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
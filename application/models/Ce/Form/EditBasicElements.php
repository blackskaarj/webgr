<?php
class Ce_Form_EditBasicElements extends Zend_Form {
	
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
		
		$valiAlphaWhiteSpace = new Zend_Validate_Alpha(true);
		$valiAlnumWhiteSpace = new Zend_Validate_Alnum(true);
		
		$this->addElement('text', CalibrationExercise::COL_NAME, array('label'=>'Calibration exercise name:',
																'required'=>true,
																'validators'=>array($valiAlnumWhiteSpace)));

		$this->addElement('text', CalibrationExercise::COL_DESCRIPTION, array('label'=>'Description:',
																'required'=>true,
																'validators'=>array($valiAlnumWhiteSpace)));
		
		$this->addElement('submit', 'save', array('label'=>'Save'
													     ));
													     
		$this->addElement('hidden','Token');
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
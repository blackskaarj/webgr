<?php
class Ce_Form_EditExpertise extends Zend_Form {

	public function __construct()
	{
		parent::__construct();

		$valiAlphaWhiteSpace = new Zend_Validate_Alpha(true);
		$valiAlnumWhiteSpace = new Zend_Validate_Alnum(true);

//		$this->addElement('text', Expertise::COL_SPECIES, array('label'=>'Species:',
//																'required'=>true,
//																'validators'=>array($valiAlphaWhiteSpace)));
		
		
		$speciesSelect = new Default_Form_Element_ValuelistSelect(605, Expertise::COL_SPECIES, array('label' => 'Species:',
                                                                                                    'required' => true));
		
		$this->addElement($speciesSelect);

		$this->addElement('text', Expertise::COL_AREA, array('label'=>'Area:',
																'required'=>true,
																'validators'=>array($valiAlnumWhiteSpace)));

//		$subjectSelect = new Zend_Form_Element_Select(Expertise::COL_SUBJECT);
//		$subjectSelect->setLabel('Subject:');
//		$subjectSelect->setRequired(true);
//		$subjectSelect->setMultiOptions(array(null=>'Please select',
//		                                      'otolith'=>'otolith',
//		                                      'gonade'=>'gonade'));
//		$this->addElement($subjectSelect);
		
		$subjectSelect = new Default_Form_Element_ValuelistSelect(606, Expertise::COL_SUBJECT, array('label' => 'Type of structure:',
                                                                                                    'required' => true));
        
        $this->addElement($subjectSelect);
		
		$this->addElement('submit', 'save', array('label'=>'Save'
		));

		$this->addElement('submit', 'cancel', array('label'=>'Cancel'
		));
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
}
?>
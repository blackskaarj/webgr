<?php
class Ce_Form_EditKeyTable extends Zend_Form {

	public function __construct()
	{
		parent::__construct();
		$this->setAttrib('enctype', 'multipart/form-data');

		$valiAlphaWhiteSpace = new Zend_Validate_Alpha(true);
		$valiAlnumWhiteSpace = new Zend_Validate_Alnum(true);

		$this->addElement('text', KeyTable::COL_NAME, array('label'=>'Protocol name:',
																'required'=>true,
																'validators'=>array($valiAlnumWhiteSpace)));		

		//		$this->addElement('text', KeyTable::COL_AREA, array('label'=>'Area:',
		//																'required'=>true,
		//																'validators'=>array($valiAlnumWhiteSpace)));
		//
		//		$this->addElement('text', KeyTable::COL_SPECIES, array('label'=>'Species:',
		//																'required'=>true,
		//																'validators'=>array($valiAlphaWhiteSpace)));
		//
		//		$this->addElement('select', KeyTable::COL_SUBJECT, array(	'label'=>'Subject:',
		//																	'required'=>true,
		//																	'multiOptions'=>array(	NULL => 'Please select',
		//																							'otolith'=>'otolith',
		//		                                      												'gonade'=>'gonade')));
		//														'validators'=>array('Int',
		//validator value 0 causes warnings in validation process, if validation from other elements fire or value=0 (Fehler nicht genau eingeschränkt)
		//http://framework.zend.com/issues/browse/ZF-5920
		/*array('GreaterThan', false, 0),
		 array('LessThan', false, 100)*/

		//		$this->addElement('checkbox', KeyTable::COL_MATURITY, array('label'=>'Maturity:',
		//																'required'=>true,
		//																'validators'=>array($valiAlnumWhiteSpace)));

		//upload element
		$this->addElement('text', KeyTable::COL_FILENAME, array(  'label'=>'Current file:','readonly'=>'readonly'));
		
		$element = new Zend_Form_Element_File('uploadElement');
		
        $path = __FILE__;
        $path = dirname($path);
        $path = dirname($path);
        $path = dirname($path);
        $path = dirname($path);
        $path = dirname($path);
		$element->setLabel('upload file:')
		->setDestination($path . '/public/protocols');
		$this->addElement($element);

		$this->addElement('submit', 'save', array('label'=>'Save'
		));

		$this->addElement('submit', 'cancel', array('label'=>'Cancel'
		));

		//#####################new###################################
		$this->setDecorators(array(   'FormElements',
		array('HtmlTag', array('tag' => 'table', 'class' => 'login_form')),
		array('Description', array('placement' => 'prepend')),
                                      'Form'));
		$this->setElementDecorators(array(
            'ViewHelper',
            'Errors',
		array(  'decorator' => array('td' => 'HtmlTag'),
                        'options' => array('tag' => 'td')),
		array(  'Label', array('tag' => 'td')),
		array(  'decorator' => array('tr' => 'HtmlTag'),
                        'options' => array('tag' => 'tr')),
		));
		$element->setDecorators(array(
                'File',
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
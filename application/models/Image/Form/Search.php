<?php

class Image_Form_Search extends Ble422_Form_Dynamic {

	public function __construct() {

		parent::__construct();
		
		$radioKind = new Zend_Form_Element_Radio('kind');
		$radioKind->addMultiOptions(array('and'=>'and',
									'or'=>'or'));
		$radioKind->setValue('and');
		$radioKind->setLabel('Search field combination:');
		$radioKind->setRequired(true);

		$this->addElement($radioKind);
		$this->addElement('text',Image::COL_ORIGINAL_FILENAME,array(  'label' => 'Image filename:'));

		$fishSampleCode = new Zend_Form_Element_Text(Fish::COL_SAMPLE_CODE);
		$fishSampleCode->setLabel('Fish Sample Code:');

		//---------------------------------------------------------
		//read the available image attributes from attribute descriptor+group image
		$metadata = new Default_MetaData();
		$imageRowSetArray = $metadata->getAttributesComplete('image');
		//----------------------------------------------------------
		$this->addDynamicElements($imageRowSetArray,true);


		//---------------------------------------------------------
		//read the available image attributes from attribute descriptor+group image
		$metadata = new Default_MetaData();
		$fishRowSetArray = $metadata->getAttributesComplete('fish');
		//----------------------------------------------------------
		$this->addDynamicElements($fishRowSetArray,true);

		$this->addElement($fishSampleCode);
		$this->addElement('submit', 'submit', array('label'=>'search'
		));
		$this->addElement('hidden','Token');
		$this->setElementFilters(array('StringTrim'));
		//#####################new###################################         
                $this->setDecorators(array(
                'FormElements',
        array('HtmlTag', array('tag' => 'table', 'class' => 'dynamic_form')),
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
                $this->setSubFormDecorators(array(
            'FormElements',
                array('HtmlTag', array('tag' => 'tr')),));

                foreach($this->getSubForms() as $index => $subform){
                    $subform->setElementDecorators(array(
                    'ViewHelper',
                    'Errors',
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                    array('Label', array('tag' => 'td'),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
                    )));
                }
                
                $this->submit->setDecorators(array(
                array(
            'decorator' => 'ViewHelper',
            'options' => array('helper' => 'formSubmit')),
                array(
            'decorator' => array('td' => 'HtmlTag'),
            'options' => array('tag' => 'td', 'colspan' => 2)),
                array(
            'decorator' => array('tr' => 'HtmlTag'),
            'options' => array('tag' => 'tr')),
                ));
          //###########################################################
	}
}
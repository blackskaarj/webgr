<?php
class Fish_Form_Edit extends Ble422_Form_Dynamic {

    public function __construct() {

        parent::__construct();
        
        $this->addElement('hidden', Fish::COL_ID, array('required'=>true));

        $fishSampleCode = new Zend_Form_Element_Text(Fish::COL_SAMPLE_CODE);
        $fishSampleCode->setLabel('Fish Sample Code:');

        //---------------------------------------------------------
        //read the available image attributes from attribute descriptor+group image
        $metadata = new Default_MetaData();
        $fishRowSetArray = $metadata->getAttributesComplete('fish');
        //----------------------------------------------------------
        $this->addDynamicElements($fishRowSetArray);

        $this->addElement($fishSampleCode);
        $this->addElement('submit', 'save', array('label'=>'Save'
        ));
        $this->addElement('hidden','Token');
        $this->setElementFilters(array('StringTrim'));
    }
}
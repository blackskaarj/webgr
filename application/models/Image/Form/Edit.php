<?php
class Image_Form_Edit extends Ble422_Form_Dynamic {

    public function __construct() {

        parent::__construct();
        
        $this->addElement('hidden', Image::COL_ID, array('required'=>true));
        $this->addElement('text', Image::COL_ORIGINAL_FILENAME,(array('label'=>'original filename:',
                                                                        'required'=>true)));
        $this->addElement('text', Image::COL_RATIO_EXTERNAL,(array('label'=>'ratio physical structure length / pixel [micrometer]:',
                                                                        'required'=>true)));
        
        //---------------------------------------------------------
        //read the available image attributes from attribute descriptor+group image
        $metadata = new Default_MetaData();
        $imageRowSetArray = $metadata->getAttributesComplete('image');
        //----------------------------------------------------------
        $this->addDynamicElements($imageRowSetArray);

        $this->addElement('submit', 'save', array('label'=>'Save'));
        $this->addElement('hidden','Token');
        $this->setElementFilters(array('StringTrim'));
    }
}
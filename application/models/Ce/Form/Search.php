<?php
class Ce_Form_Search extends Zend_Form {

    public function __construct() {
    	parent::__construct();
        
    	$this->addElement(new Default_Form_Element_ExpertiseSelect(CalibrationExercise::COL_EXPERTISE_ID, array('label'=>'Expertise:')));
        		
    	$this->addElement('hidden','Token');
        $this->addElement('submit','submit',array('label'=>'search'));
        
        
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
    }//ENDE: public function ...
  
//ENDE: class ...  
}
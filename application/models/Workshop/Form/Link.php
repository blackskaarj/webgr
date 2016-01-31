<?php
class Workshop_Form_Link extends Zend_Form {


    public function __construct() {
        parent::__construct();
        $this->addElement('text',WorkshopInfo::COL_TEXT,array(  'label'=>'Name / Info:',
                                                                'required'=>true,
                                                                'validators'=>array(new Zend_Validate_Alnum(true))));
        $this->addElement('text',WorkshopInfo::COL_LINK,array(  'label'=>'url:',
                                                                'required'=>true));
        
        //render as html table and add the submit button
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table', 'class' => 'login_form','border'=>'solid')),
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
                            
        $this->addElement('submit', 'submit', array(
            'required' => false,
            'ignore'   => true,
            'label'    => 'save',
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
        
        //hidden elements
        $this->addElement('hidden',WorkshopInfo::COL_WORKSHOP_ID);
        $this->addElement('hidden',WorkshopInfo::COL_ID);
    }//ENDE: public function ...
  
//ENDE: class ...  
}
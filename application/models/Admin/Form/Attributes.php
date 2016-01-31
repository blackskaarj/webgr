<?php
class Admin_Form_Attributes extends Zend_Form {

    public function __construct() {
        parent::__construct();
        
        $this->addElement('text','username', array(    'label' => 'Owner:',
                                                       'disabled'=>'disabled'));
        $this->addElement('text',AttributeDescriptor::COL_NAME,array(    'label'=>'name:',
                                                                         'required'=>true,
                                                                         'validators' => array(new Zend_Validate_Alnum(true))));
        $this->addElement(new Default_Form_Element_ValuelistSelect(604,AttributeDescriptor::COL_UNIT, array('label'=>'unit:',
                                                                                                            'validator'=>'int')));
        $this->addElement('textarea',AttributeDescriptor::COL_DESCRIPTION,array('label'=>'description:',
                                    'rows' => '4',
                                    'cols' => '40'));
        $this->addElement('text',AttributeDescriptor::COL_DEFAULT,array('label'=>'default value:'));
        $this->addElement('checkbox',AttributeDescriptor::COL_REQUIRED,array('label'=>'is required:'));
        $this->addElement('checkbox',AttributeDescriptor::COL_IS_STANDARD,array('label'=>'is standard:'));
        $this->addElement('checkbox',AttributeDescriptor::COL_ACTIVE,array('label'=>'active:'));
        
        $datatypes = new Zend_Form_Element_Select(AttributeDescriptor::COL_DATA_TYPE);
        $datatypes->setLabel('data type:');
        $datatypes->addMultiOptions(array(  'Please select'=>null,
                                            'string'=>'string',
                                            'decimal'=>'decimal',
                                            'integer'=>'integer',
                                            'boolaen'=>'boolean',
                                            'date'=>'date',
                                            'time'=>'time',
                                            'datetime'=>'datetime'));
        $datatypes->setRequired(true);
        $this->addElement($datatypes);
        
        $formtypes = new Zend_Form_Element_Select(AttributeDescriptor::COL_FORM_TYPE);
        $formtypes->setLabel('form type:');
        $formtypes->addMultiOptions(array (    'Please select'=>null,
                                               'checkbox'       =>'checkbox',
                                               'textarea'       =>'textarea',
                                               'multicheckbox'   =>'multicheckbox',
                                               'multiselect'   =>'multiSelect',
                                               'radio'           =>'radiobuttons',
                                               'select'         =>'select',
                                               'text'           =>'textbox'));
        $formtypes->setRequired(true);
        $this->addElement($formtypes);
        
        $this->addElement('checkbox',AttributeDescriptor::COL_VALUE_LIST,array('label'=>'has valuelist:'));
        
        $sequence = new Zend_Form_Element_Text(AttributeDescriptor::COL_SEQUENCE);
        $lastSequenceFish = $this->getLastSequence('fish');
        $lastSequenceImage = $this->getLastSequence('image');
        $sequence->setLabel('sequence (last sequence fish:'.$lastSequenceFish.' last sequence image:'.$lastSequenceImage.'):');
        $sequence->setRequired(true);
        $sequence->setValidators(array(new Zend_Validate_Int(), 
            new Zend_Validate_GreaterThan(0)));
        $this->addElement($sequence);
        
        $this->addElement('checkbox',AttributeDescriptor::COL_MULTIPLE,array('label'=>'is multiple:'));
        $this->addElement('checkbox',AttributeDescriptor::COL_SHOW_IN_LIST,array('label'=>'show in list:'));
        
        $group = new Zend_Form_Element_Select(AttributeDescriptor::COL_GROUP);
        $group->setLabel('attribute group:');
        $group->addMultiOptions(array ( 'Please select'=>null,
                                        'image'=>'image',
                                        'fish'=>'fish',
                                        'system'=>'system'));
        $group->setRequired(true);
        $this->addElement($group);
        
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
        
        // hidden elements
        $this->addElement('hidden',AttributeDescriptor::COL_ID);
        $this->addElement('hidden',AttributeDescriptor::COL_USER_ID);
    }//ENDE: public function ...
    private function getLastSequence($ATDE_GROUP) {
    	$select = Zend_Db_Table_Abstract::getDefaultAdapter()->select();
    	$select->from(AttributeDescriptor::TABLE_NAME, 'Max('.AttributeDescriptor::COL_SEQUENCE.')')
    	->where(AttributeDescriptor::COL_GROUP.' = ?', $ATDE_GROUP);
    	$lastSequence = Zend_Db_Table_Abstract::getDefaultAdapter()->fetchOne($select);   
    	return $lastSequence;	
    }
//ENDE: class ...  
}
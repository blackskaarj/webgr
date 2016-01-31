<?php

class Default_Form_Login extends Zend_Form 
{
	function __construct() {
		
		$this->addElement('text', User::COL_EMAIL, array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                'EmailAddress',
                array('StringLength', false, array(6, 40)),
            ),
            'required'   => true,
            'label'      => 'e-mail:',
        ));

        $this->addElement('password', User::COL_PASSWORD, array(
            'filters'    => array('StringTrim'),
//            'validators' => array(
//                'alnum',
//                array('StringLength', false, array(6, 20)),
//            ),
            'required'   => true,
            'label'      => 'password:',
        ));

        $this->addElement('submit', 'login', array(
            'required' => false,
            'ignore'   => true,
            'label'    => 'login',
        ));

        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'login_form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
	}
}


?>
<?php
class Ble422_Zend_Form_Helper {


	public function markRequiredElements($form)
	/*
	 * adds asterisks to the element label from required elements
	 * useful for add/update forms
	 * don't use for search forms
	 */
	{
		
		$elements = $form->getElements();
		foreach ($elements as $elem) {
			if ($elem->isRequired()) {
				$elem->setLabel($elem->getLabel().' *');
			}
		}
	}
	public function addFormFooter()
	//add custom XHTML to the form
	//credit: by dinoboff Mar 09, 2008; 04:45pm
	//http://www.nabble.com/Adding-custom-HTML-to-Form--td15920982.html
	{
		$e = new Zend_Form_Element_Xhtml();
	}
	
}
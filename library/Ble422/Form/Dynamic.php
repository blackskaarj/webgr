<?php
class Ble422_Form_Dynamic extends Zend_Form {

	private $dynamicElements = array();
	private $forSearch = false;
    
	//adds elements to array
	//uses form element types from attribute descriptor
	//uses option list from value list
	//uses special form element types for search corresponding to attribute descriptor
	//elements get ordered by sequence later XXXyes? where? check 
	public function addDynamicElements ($attrRowSetArray, $forSearch = false, $forImagesetAttribute = false) {
		 
		$this->forSearch = $forSearch;
		//dynamic form elements
		foreach ($attrRowSetArray as $attr) {
			$fromValue = FALSE;
			//column type is nullable
			if (is_null($attr['UNIT'])) {
				$strUnit = '';
			} else {
				$strUnit = '['.$attr['UNIT'].']';
			}
			$attrName = 'ATDE_'.$attr[AttributeDescriptor::COL_ID];
			$attrLabel = $attr[AttributeDescriptor::COL_NAME].$strUnit.':';
			
			if ($attr[AttributeDescriptor::COL_REQUIRED] == 1) {
				$required = TRUE;
			} else {
				$required = FALSE;
			}

			if ($attr[AttributeDescriptor::COL_FORM_TYPE] == 'select') {
				if ($attr[AttributeDescriptor::COL_VALUE_LIST] == 1) {
					//show select
					if ($forSearch) {
						$valiSel = new Default_Form_Element_ValuelistMulticheckbox($attr[AttributeDescriptor::COL_ID],
						$attrName,
						array('label' => $attrLabel));
					} else {
						$valiSel = new Default_Form_Element_ValuelistSelect($attr[AttributeDescriptor::COL_ID],
	                    $attrName,
	                    array('label' => $attrLabel,
	                           'required' => $required));
					}
					//XXX valids/filters beta state
					if (isset($attr[AttributeDescriptor::COL_VALIDATORS])) {
						$valiSel->addValidators($attr[AttributeDescriptor::COL_VALIDATORS]);
					}
					if (isset($attr[AttributeDescriptor::COL_FILTERS])) {
						$valiSel->addFilters($attr[AttributeDescriptor::COL_FILTERS]);
					}
					$this->addElement($valiSel);
				}
			}elseif ($attr[AttributeDescriptor::COL_FORM_TYPE] == 'radio') {
				if ($forSearch) {
					$valiRad = new Default_Form_Element_ValuelistMulticheckbox($attr[AttributeDescriptor::COL_ID],
	                $attrName,
	                array('label' => $attrLabel));
				}else {
				    $valiRad = new Default_Form_Element_ValuelistRadio($attr[AttributeDescriptor::COL_ID],
	                $attrName,
	                array('label' => $attrLabel,
	                      'required' => $required));
				}
				//XXX valids/filters beta state
				if (isset($attr[AttributeDescriptor::COL_VALIDATORS])) {
					$valiRad->addValidators($attr[AttributeDescriptor::COL_VALIDATORS]);
				}
				if (isset($attr[AttributeDescriptor::COL_FILTERS])) {
					$valiRad->addFilters($attr[AttributeDescriptor::COL_FILTERS]);
				}
				$this->addElement($valiRad);
			}elseif ($attr[AttributeDescriptor::COL_FORM_TYPE] == 'text') {
				if ($attr[AttributeDescriptor::COL_DATA_TYPE] == 'integer' ||
				$attr[AttributeDescriptor::COL_DATA_TYPE] == 'decimal' ||
				$attr[AttributeDescriptor::COL_DATA_TYPE] == 'date' ||
				$attr[AttributeDescriptor::COL_DATA_TYPE] == 'time' ||
				$attr[AttributeDescriptor::COL_DATA_TYPE] == 'datetime') {
					if ($forSearch) {
						//show from/to textfields
						$sform = new Zend_Form_SubForm(array('elementsBelongto' => $attrName));
						$sform->addElement('text', 'fromValue', array('label' => $attrLabel.' FROM'));
						$sform->addElement('text', 'toValue', array('label' => $attrLabel.' TO'));
						$this->addSubForm($sform, $attrName);
						$fromValue = TRUE;
					}else{
						$this->addElement(    $attr[AttributeDescriptor::COL_FORM_TYPE],
						$attrName,
						array('label' => $attrLabel,
						      'required' => $required));
					}
				}elseif ($attr[AttributeDescriptor::COL_DATA_TYPE] == 'string') {
					$this->addElement(  $attr[AttributeDescriptor::COL_FORM_TYPE],
					$attrName,
					array('label' => $attrLabel,
					       'required' => $required));
				}
			}elseif( $attr[AttributeDescriptor::COL_FORM_TYPE] == 'textarea' && !$forSearch) {
				$this->addElement(  $attr[AttributeDescriptor::COL_FORM_TYPE],
                $attrName,
                array(  'label' => $attrLabel,
                        'rows'=>'4',
                        'cols'=>'20',
                        'required' => $required));
			}elseif($attr[AttributeDescriptor::COL_FORM_TYPE] == 'checkbox') {
				//standard returns 0 for unchecked, 1 for checked, if not set
				$this->addElement(  $attr[AttributeDescriptor::COL_FORM_TYPE],
				$attrName,
				array(  'label' => $attrLabel,
				        'required' => $required));
			}elseif( $attr[AttributeDescriptor::COL_FORM_TYPE] == 'multiselect') {
				$valiMultisel = new Default_Form_Element_ValuelistMultiselect(  $attr[AttributeDescriptor::COL_ID],
				$attrName,
				array(  'label' => $attrLabel,
				        'required' => $required));
				$this->addElement($valiMultisel);
			}elseif( $attr[AttributeDescriptor::COL_FORM_TYPE] == 'multicheckbox') {
				$valiMulticheckbox = new Default_Form_Element_ValuelistMulticheckbox($attr[AttributeDescriptor::COL_ID],
				$attrName,
				array(  'label' => $attrLabel,
				        'required' => $required));
				$this->addElement($valiMulticheckbox);
			}

			//special link to remove search element for customized search filter
			if ($forImagesetAttribute) {
				if (!$fromValue) {
					$formElem = $this->getElement($attrName);
					$formElem->setDescription("<a href='/ce/edit/removeimagesetattribute/".AttributeDescriptor::COL_ID.'/'.$attr[AttributeDescriptor::COL_ID]."'>Remove attribute</a>")
					->setDecorators(array(
       									'ViewHelper',
					array('Description', array('escape' => false, 'tag' => false)),
					array('HtmlTag', array('tag' => 'dd')),
					array('Label', array('tag' => 'dt')),
    								    'Errors',
					));
				}else {
					$subForm = $this->getSubForm($attrName);
					$formElem = $subForm->getElement('fromValue');
					$formElem->setDescription("<a href='/ce/edit/removeimagesetattribute/".AttributeDescriptor::COL_ID.'/'.$attr[AttributeDescriptor::COL_ID]."'>Remove attribute</a>")
					->setDecorators(array(
       									'ViewHelper',
					array('Description', array('escape' => false, 'tag' => false)),
					array('HtmlTag', array('tag' => 'dd')),
					array('Label', array('tag' => 'dt')),
    								    'Errors',
					));
				}
			}
			array_push($this->dynamicElements,$attrName);
			//            $this->addElement(	'submit',
			//									'Remove_attribute_ATDE_'.$attr[AttributeDescriptor::COL_ID],
			//									array(	'label' => 'Remove attribute',
			//            								'action' => '/ce/edit/removeimagesetattribute/'.AttributeDescriptor::COL_ID.'/'.$attr[AttributeDescriptor::COL_ID]));
			 
		}
	}
	
	//getter method
	public function getDynamicElements()
	{
		return $this->dynamicElements;
	}
	
	//populate element with value(s) from database
	public function dynPopulate($resultAttr , $valueColumn , $additionalParams = array())
	{
		$attribFormValues = array();
		foreach ($resultAttr as $attribValue){
			if(  $attribValue[AttributeDescriptor::COL_FORM_TYPE] == 'text' ||
			     $attribValue[AttributeDescriptor::COL_FORM_TYPE] == 'textarea' ||
			     ($attribValue[AttributeDescriptor::COL_FORM_TYPE] == 'radio' && !$this->forSearch) ||
			     ($attribValue[AttributeDescriptor::COL_FORM_TYPE] == 'select' && !$this->forSearch) ||
			     $attribValue[AttributeDescriptor::COL_FORM_TYPE] == 'checkbox'){
				if(array_key_exists(ImagesetAttributes::COL_VALUE_LIST_ID,$attribValue) && ($attribValue[AttributeDescriptor::COL_VALUE_LIST] == 1 || $attribValue[AttributeDescriptor::COL_VALUE_LIST] == '1')) {
					$attribFormValues += array('ATDE_' . $attribValue[AttributeDescriptor::COL_ID] => $attribValue[ImagesetAttributes::COL_VALUE]);
				}elseif ($attribValue[AttributeDescriptor::COL_FORM_TYPE] == 'text'){
					if ($attribValue[AttributeDescriptor::COL_DATA_TYPE] == 'integer' ||
					$attribValue[AttributeDescriptor::COL_DATA_TYPE] == 'decimal' ||
					$attribValue[AttributeDescriptor::COL_DATA_TYPE] == 'date' ||
					$attribValue[AttributeDescriptor::COL_DATA_TYPE] == 'time' ||
					$attribValue[AttributeDescriptor::COL_DATA_TYPE] == 'datetime') {
						if(array_key_exists(ImagesetAttributes::COL_FROM,$attribValue) && ($attribValue[ImagesetAttributes::COL_TO] == null || $attribValue[ImagesetAttributes::COL_TO] == '')){
							if(array_key_exists('ATDE_' . $attribValue[AttributeDescriptor::COL_ID],$attribFormValues) && is_array($attribFormValues['ATDE_' . $attribValue[AttributeDescriptor::COL_ID]])){
								$attribFormValues['ATDE_' . $attribValue[AttributeDescriptor::COL_ID]] += array('fromValue' => $attribValue[ImagesetAttributes::COL_FROM]);
							}else{
								$attribFormValues['ATDE_' . $attribValue[AttributeDescriptor::COL_ID]] = array('fromValue' => $attribValue[ImagesetAttributes::COL_FROM]);
							}
						}else if (array_key_exists(ImagesetAttributes::COL_TO,$attribValue)&& ($attribValue[ImagesetAttributes::COL_FROM] == null || $attribValue[ImagesetAttributes::COL_FROM] == '')){
							if(array_key_exists('ATDE_' . $attribValue[AttributeDescriptor::COL_ID],$attribFormValues) && is_array($attribFormValues['ATDE_' . $attribValue[AttributeDescriptor::COL_ID]])){
								$attribFormValues['ATDE_' . $attribValue[AttributeDescriptor::COL_ID]] += array('toValue' => $attribValue[ImagesetAttributes::COL_TO]);
							}else{
								$attribFormValues['ATDE_' . $attribValue[AttributeDescriptor::COL_ID]] = array('toValue' => $attribValue[ImagesetAttributes::COL_TO]);
							}
						}else{
							$attribFormValues += array('ATDE_' . $attribValue[AttributeDescriptor::COL_ID] => $attribValue[$valueColumn]);
						}
					}else{
						$attribFormValues += array('ATDE_' . $attribValue[AttributeDescriptor::COL_ID] => $attribValue[$valueColumn]);
					}
				}else{
					$attribFormValues += array('ATDE_' . $attribValue[AttributeDescriptor::COL_ID] => $attribValue[$valueColumn]);
				}
			}else{
				if(array_key_exists('ATDE_' . $attribValue[AttributeDescriptor::COL_ID],$attribFormValues) && is_array($attribFormValues['ATDE_' . $attribValue[AttributeDescriptor::COL_ID]])){
					array_push($attribFormValues['ATDE_' . $attribValue[AttributeDescriptor::COL_ID]],$attribValue[$valueColumn]);
				}else{
					$attribFormValues['ATDE_' . $attribValue[AttributeDescriptor::COL_ID]] = array($attribValue[$valueColumn]);
				}
			}
		}
		$attribFormValues += $additionalParams;
		$this->populate($attribFormValues);
	}

}
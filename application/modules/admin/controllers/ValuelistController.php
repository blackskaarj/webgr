<?php
class Admin_ValuelistController extends Zend_Controller_Action {

	private $formUpdate;
	private $formAdd;
	private $atDeId;
	private $namespace;

	public function init() {
		$this->namespace = new Zend_Session_Namespace('valuelist');

		$this->atDeId = $this->getRequest()->getParam(AttributeDescriptor::COL_ID);
		if (Default_ReferenceQuery::hasValueListData($this->atDeId)) {
			$this->formUpdate = new Admin_Form_Valuelist($this->atDeId);
		}
		$this->formAdd = new Admin_Form_ValuelistNewDataset($this->atDeId);
	}

	public function editAction() {
		$this->view->atDeId = $this->atDeId;
		$this->view->atDeName = Default_SimpleQuery::getAttributeName($this->atDeId);
		if (Default_ReferenceQuery::hasValueListData($this->atDeId)) {
			$this->view->formUpdate = $this->formUpdate;
		}
		$this->view->formAdd = $this->formAdd;
			
	}

	public function insertAction()
	{
		$request = $this->getRequest();


		if ($request->isPost() && $this->formAdd->isValid($request->getParams())) {
			$table = new ValueList();
			$data = array(	ValueList::COL_ATTRIBUTE_DESCRIPTOR_ID => $this->atDeId,
			ValueList::COL_NAME => $this->formAdd->getValue(ValueList::COL_VALUE),
			ValueList::COL_VALUE => $this->formAdd->getValue(ValueList::COL_VALUE));
			$table->insert($data);
			$this->redirectTo();
		}else{
			$this->view->atDeName = Default_SimpleQuery::getAttributeName($this->atDeId);
			$this->view->formAdd = $this->formAdd;
			$this->view->formUpdate = $this->formUpdate;
			$this->render('edit');
		}
	}

	public function updateAction()
	{
		$request = $this->getRequest();

		if ($request->isPost() && $this->formUpdate->isValid($request->getParams())) {
			$table = new ValueList();
			$formValues = $this->formUpdate->getValues();
			foreach ($formValues as $key => $value) {
				if ($this->formKeyHasValue($key, $value)) {
					//search for data sets with NULL values - e.g. old data sets before introduction of new attributes - isn't possible at the moment
					//process possible meta data attributes
					if (substr_compare($key, 'VALI_', 0, 4, TRUE) == 0) {
						$keyVaLiId = substr($key, 5);
							
						$data = array(	ValueList::COL_NAME => $value,
						ValueList::COL_VALUE => $value);
						$table->update($data,ValueList::COL_ID . '=' . intval($keyVaLiId));
					}
				}
			}
			$this->redirectTo();
		}else{
			$this->view->formAdd = $this->formAdd;
			$this->view->formUpdate = $this->formUpdate;
			$this->render('edit');
		}
	}

	public function showAction()
	{
		$this->view->atDeId = $this->atDeId;
		$this->view->atDeName = Default_SimpleQuery::getAttributeName($this->atDeId);
		if (Default_ReferenceQuery::hasValueListData($this->atDeId)) {
				
			$table = new ValueList();
			$select = $table->select();
			$select->where(ValueList::COL_ATTRIBUTE_DESCRIPTOR_ID. "= ?", $this->atDeId, 'int');
			$rowset = $table->fetchAll($select);
			$array = $rowset->toArray();
				
			$this->view->list = $array;
			$this->render('list');
		}
	}
	public function redirectTo()
	{
		$Redirect = new Zend_Controller_Action_Helper_Redirector();
		$Redirect->setGotoSimple('edit','valuelist','admin', array(AttributeDescriptor::COL_ID => $this->atDeId));
	}

	private function formKeyHasValue($key, $value) {
		//first if condition asks for possible simple form key value pairs
		//'kind' radio button excluded
		//'submit' submit button excluded
		//'Token' string excluded
		if ($key != null && $value != null && $key != 'kind' && $key != 'submit' && $key != 'Token') {
			//second if condition asks if possible value array has any value filled
			if (is_array($value)) {
				foreach ($value as $val) {
					//is_null doesn't return TRUE for *empty* array elements from form element, use != NULL
					if ($val != NULL) {
						return TRUE;
					}
				}
				return FALSE;
			} else {
				return TRUE;
			}
		} else {
			return FALSE;
		}
	}
}
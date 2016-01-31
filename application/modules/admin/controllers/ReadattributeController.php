<?php
class admin_ReadattributeController extends Zend_Controller_Action {

	private $form;

	public function init() {
		$this->form = new Admin_Form_Attributes();
		$this->form->removeElement('submit');
	}//ENDE: public function ...

	public function listAction()
	{
		$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
		$select = $dbAdapter->select();
		$select->from(AttributeDescriptor::TABLE_NAME);
        $select->joinLeft(array('val1' => ValueList::TABLE_NAME),
        AttributeDescriptor::TABLE_NAME.'.'.AttributeDescriptor::COL_UNIT.'='.'val1.'.ValueList::COL_ID,
        array(AttributeDescriptor::COL_UNIT => ValueList::COL_NAME));
		/**
		 * Paginator control
		 */
		$paginator = new Ble422_Paginator_Extended($select,$this->getRequest());
		$paginator->setHeader(array(array('raw'=>AttributeDescriptor::COL_NAME,'name'=>'attribute desc.'),
		array('raw'=>AttributeDescriptor::COL_GROUP,'name'=>'group'),
		array('raw'=>AttributeDescriptor::COL_UNIT,'name'=>'unit'),
		array('raw'=>AttributeDescriptor::COL_DESCRIPTION,'name'=>'description')));
		$paginator  ->setCurrentPageNumber($this->getRequest()->getParam('page'))
		->setItemCountPerPage(50)//$this->_getParam('itemCountPerPage'))
		->setPageRange(5)
		->orderBy($this->getRequest()->getParam('orderBy'));//$this->_getParam('pageRange'));
		$this->view->paginator = $paginator;
	}
	
	public function detailAction()
	{
		$request = $this->getRequest();
		$table = new AttributeDescriptor();

		if ($request->isPost() && $this->form->isValid($request->getParams())) {
			 
		}else{
			$attribArray = $table->find($request->getParam(AttributeDescriptor::COL_ID))->current()->toArray();
			$userTable = new User();
			$userArray = $userTable->find($attribArray[User::COL_ID])->current()->toArray();
			$attribArray += array('username' => $userArray[User::COL_USERNAME]);
			$this->form->isValid($attribArray);
			$this->view->form = $this->form;
			$this->render('form');
		}

		//ENDE: class ...
	}
}
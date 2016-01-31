<?php
class admin_AttributeController extends Zend_Controller_Action {

	private $form;

	public function init() {
		$this->form = new Admin_Form_Attributes();
	}//ENDE: public function ...

	public function listAction()
	{
		$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
		$select = $dbAdapter->select();
		$select->from(AttributeDescriptor::TABLE_NAME);
		 
		/**
		 * Paginator control
		 */
		$paginator = new Ble422_Paginator_Extended($select,$this->getRequest());
		$paginator->setHeader(array(array('raw'=>AttributeDescriptor::COL_NAME,'name'=>'attribute desc.'),
		array('raw'=>AttributeDescriptor::COL_GROUP,'name'=>'group'),
		array('raw'=>AttributeDescriptor::COL_FORM_TYPE,'name'=>'formtype'),
        array('raw'=>AttributeDescriptor::COL_UNIT,'name'=>'unit'),
        array('raw'=>AttributeDescriptor::COL_DESCRIPTION,'name'=>'description'),
        array('raw'=>AttributeDescriptor::COL_SEQUENCE,'name'=>'sequence')));
		$paginator  ->setCurrentPageNumber($this->getRequest()->getParam('page'))
		->setItemCountPerPage(50)//$this->_getParam('itemCountPerPage'))
		->setPageRange(5)
		->orderBy($this->getRequest()->getParam('orderBy'));//$this->_getParam('pageRange'));
		$this->view->paginator = $paginator;
	}

	public function insertAction()
	{
		$request = $this->getRequest();
		if ($request->isPost() && $this->form->isValid($request->getParams())) {
			$table = new AttributeDescriptor();
			$data = array(    AttributeDescriptor::COL_NAME => $this->form->getValue(AttributeDescriptor::COL_NAME),
			AttributeDescriptor::COL_UNIT => $this->form->getValue(AttributeDescriptor::COL_UNIT),
			AttributeDescriptor::COL_DESCRIPTION => $this->form->getValue(AttributeDescriptor::COL_DESCRIPTION),
			AttributeDescriptor::COL_DEFAULT => $this->form->getValue(AttributeDescriptor::COL_DEFAULT),
			AttributeDescriptor::COL_REQUIRED => $this->form->getValue(AttributeDescriptor::COL_REQUIRED),
			AttributeDescriptor::COL_IS_STANDARD => $this->form->getValue(AttributeDescriptor::COL_IS_STANDARD),
			AttributeDescriptor::COL_ACTIVE => $this->form->getValue(AttributeDescriptor::COL_ACTIVE),
			AttributeDescriptor::COL_DATA_TYPE => $this->form->getValue(AttributeDescriptor::COL_DATA_TYPE),
			AttributeDescriptor::COL_FORM_TYPE => $this->form->getValue(AttributeDescriptor::COL_FORM_TYPE),
			AttributeDescriptor::COL_VALUE_LIST => $this->form->getValue(AttributeDescriptor::COL_VALUE_LIST),
			AttributeDescriptor::COL_SEQUENCE => $this->form->getValue(AttributeDescriptor::COL_SEQUENCE),
			AttributeDescriptor::COL_MULTIPLE => $this->form->getValue(AttributeDescriptor::COL_MULTIPLE),
			AttributeDescriptor::COL_SHOW_IN_LIST => $this->form->getValue(AttributeDescriptor::COL_SHOW_IN_LIST),
			AttributeDescriptor::COL_GROUP => $this->form->getValue(AttributeDescriptor::COL_GROUP),
			AttributeDescriptor::COL_USER_ID => $this->form->getValue(AttributeDescriptor::COL_USER_ID));
			$table->insert($data);
			$this->redirectTo();
		}else{
			// Get user_id and part_role
			$auth = Zend_Auth::getInstance();
			$storage = $auth->getStorage();
			$constUserId = User::COL_ID;
			$userId = $storage->read()->$constUserId;
			$constUsername = User::COL_USERNAME;
			$username = $storage->read()->$constUsername;
			$this->form->isValidPartial(array( AttributeDescriptor::COL_USER_ID => $userId,
                                   'username' => $username));
			$this->form->removeElement(AttributeDescriptor::COL_ID);
			$this->view->form = $this->form;
			$this->render('form');
		}
	}

	public function updateAction()
	{
		$request = $this->getRequest();
		$table = new AttributeDescriptor();

		if ($request->isPost()
		&& $this->form->isValid($request->getParams())) {
//		&& $this->sequenceUnique($this->form->getValue(AttributeDescriptor::COL_GROUP),$this->form->getValue(AttributeDescriptor::COL_SEQUENCE), intval($this->form->getValue(AttributeDescriptor::COL_ID)))) {
			$data = array(    AttributeDescriptor::COL_NAME => $this->form->getValue(AttributeDescriptor::COL_NAME),
			AttributeDescriptor::COL_UNIT => $this->form->getValue(AttributeDescriptor::COL_UNIT),
			AttributeDescriptor::COL_DESCRIPTION => $this->form->getValue(AttributeDescriptor::COL_DESCRIPTION),
			AttributeDescriptor::COL_DEFAULT => $this->form->getValue(AttributeDescriptor::COL_DEFAULT),
			AttributeDescriptor::COL_REQUIRED => $this->form->getValue(AttributeDescriptor::COL_REQUIRED),
			AttributeDescriptor::COL_IS_STANDARD => $this->form->getValue(AttributeDescriptor::COL_IS_STANDARD),
			AttributeDescriptor::COL_ACTIVE => $this->form->getValue(AttributeDescriptor::COL_ACTIVE),
			AttributeDescriptor::COL_DATA_TYPE => $this->form->getValue(AttributeDescriptor::COL_DATA_TYPE),
			AttributeDescriptor::COL_FORM_TYPE => $this->form->getValue(AttributeDescriptor::COL_FORM_TYPE),
			AttributeDescriptor::COL_VALUE_LIST => $this->form->getValue(AttributeDescriptor::COL_VALUE_LIST),
			AttributeDescriptor::COL_SEQUENCE => $this->form->getValue(AttributeDescriptor::COL_SEQUENCE),
			AttributeDescriptor::COL_MULTIPLE => $this->form->getValue(AttributeDescriptor::COL_MULTIPLE),
			AttributeDescriptor::COL_SHOW_IN_LIST => $this->form->getValue(AttributeDescriptor::COL_SHOW_IN_LIST),
			AttributeDescriptor::COL_GROUP => $this->form->getValue(AttributeDescriptor::COL_GROUP));
			$table->update($data,AttributeDescriptor::COL_ID . '=' . intval($this->form->getValue(AttributeDescriptor::COL_ID)));
			$this->redirectTo();
		}else{
			$attribArray = $table->find($request->getParam(AttributeDescriptor::COL_ID))->current()->toArray();
			$userTable = new User();
			$userArray = $userTable->find($attribArray[User::COL_ID])->current()->toArray();
			$attribArray += array('username' => $userArray[User::COL_USERNAME]);
			$this->form->populate($attribArray);
			$this->view->form = $this->form;
			$this->render('form');
		}
	}

	public function redirectTo()
	{
		$Redirect = new Zend_Controller_Action_Helper_Redirector();
		$Redirect->setGotoSimple('list','attribute','admin');
	}

	public function createattributecsvAction()
	{
		$csvString = '';
		// prepare the header
		$fishBaseAttr = array(	  Fish::COL_SAMPLE_CODE);
		$imageBaseAttr = array(   Image::COL_ORIGINAL_FILENAME,
		                          Image::COL_RATIO_EXTERNAL);
		$meta = new Default_MetaData();
		$attribRowset = array_merge($fishBaseAttr,
		$meta->getAttributesBasic('FISH'),
		$imageBaseAttr,
		$meta->getAttributesBasic('IMAGE'));
		 
		/*handle last item differently
		 * credit:grobemo
		 * 24-Apr-2009 08:13
		 * http://de3.php.net/manual/en/control-structures.foreach.php
		 */
		$last_item = end($attribRowset);
		foreach ($attribRowset as $attr) {
			if ($attr == $last_item) {
				if (is_array($attr) && array_key_exists(AttributeDescriptor::COL_NAME, $attr)) {
					$csvString .= $attr[AttributeDescriptor::COL_NAME];
				} else {
					$csvString .= $attr;
				}
			} else {
				if (is_array($attr) && array_key_exists(AttributeDescriptor::COL_NAME, $attr)) {
					$csvString .= $attr[AttributeDescriptor::COL_NAME]. ',' ;
				} else {
					$csvString .= $attr. ',' ;
				}
			}
		}
		$csvString .= "\n";

		$this->view->csvString = $csvString;
		// generate the download file
		Zend_Layout::resetMvcInstance();
		$this->render('csvstring');
	}
//	/**
//   * @TODO realize later as Validator_Class
//	 * validates Column ATDE_SEQUENCE in Table attribute_desc wheather it is unique
//	 * for one type of ATDE_GROUP
//	 *
//	 */
//	private function sequenceUnique($ATDE_GROUP, $ATDE_SEQUENCE, $ID) {
//		if($ATDE_GROUP && $ATDE_SEQUENCE) {
//			$select = Zend_Db_Table_Abstract::getDefaultAdapter()->select();
//			$select->from(AttributeDescriptor::TABLE_NAME, AttributeDescriptor::COL_ID)
//			->where(AttributeDescriptor::COL_GROUP.' = ?', $ATDE_GROUP)
//			->where(AttributeDescriptor::COL_SEQUENCE.' = ?', $ATDE_SEQUENCE);
//			$result = Zend_Db_Table_Abstract::getDefaultAdapter()->fetchOne($select);
//			if($result) {
//				if($result == $ID) {//SEQUENCE is the same as before
//					return true;
//				} else {
//					return false;
//				}
//			} else {
//				return true;
//			}
//		}
//		return true; // no ATDE_GROUP and no Sequence chosen
//	}
	//ENDE: class ...
}
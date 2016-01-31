<?php
class Ce_EditkeytableController extends Zend_Controller_Action
{
	//TODO Token Prüfung reintegrieren

	private $form;
	private $defaultNamespace;
	private $ktId;

	public function init()
	{
		$this->defaultNamespace = new Zend_Session_Namespace('default');
		if (!isset($this->defaultNamespace->callingAction)) {
			$this->defaultNamespace->callingAction = '';
		}
		if (!isset($this->defaultNamespace->callingActionId)) {
			$this->defaultNamespace->callingActionId = '';
		}
			
		$this->defaultNamespace->returningAction = NULL;
		$this->defaultNamespace->returningActionId = NULL;
			
		$this->form = new Ce_Form_EditKeyTable();
		//$this->view->form = $this->form;

		//get workshop ID from calling workshop
		//		$this->callingWorkshopId = $this->_getParam('WORK_ID');
		//		if ($this->callingWorkshopId == NULL)
		//		{
		//			$this->_redirect(Zend_Controller_Front::getInstance()->getBaseUrl()."/index/index");
		//		}
	}

	public function indexAction()
	{
		if ($this->getRequest()->isPost()) {
			$paramCancel = $this->getRequest()->getParam('cancel');
			$cancelButtonPressed = isset($paramCancel);
			if ($cancelButtonPressed) {
				$this->defaultNamespace->returningAction = 'ce/editkeytable/index';
				switch ($this->defaultNamespace->callingAction) {
					case 'ce/edit/index':
						$this->_forward("index","edit","ce",array("CAEX_ID" => $this->defaultNamespace->callingActionId));
						break;
					case 'ce/editkeytable/list':
						$this->_forward("list","editkeytable","ce");
						break;
					default:
						throw new Zend_Exception;
						break;
				}
			}

			if ($this->form->isValid($this->getRequest()->getParams())) {
				$upload = $this->form->uploadElement->getTransferAdapter();
				$filename = $this->form->uploadElement->getFilename(null,false);
				$upload->receive();
				//insert/update
				$ktTable = new KeyTable();
				$data = array(
				KeyTable::COL_NAME => $this->form->getValue(KeyTable::COL_NAME),
				KeyTable::COL_FILENAME => $filename
				//				KeyTable::COL_AREA => $this->form->getValue(KeyTable::COL_AREA),
				//				KeyTable::COL_SPECIES=>$this->form->getValue(KeyTable::COL_SPECIES),
				////				KeyTable::COL_AGE => $this->form->getValue(KeyTable::COL_AGE),
				////				KeyTable::COL_MATURITY => $this->form->getValue(KeyTable::COL_MATURITY),
				//				KeyTable::COL_SUBJECT => $this->form->getValue(KeyTable::COL_SUBJECT)
				);
				$this->ktId = $ktTable->insert($data);
				//$this->redirectTo('inserted');
				//$this->inserted();
				if($this->defaultNamespace->callingActionId != null){
					$this->defaultNamespace->returningAction = 'ce/editkeytable/index';
					$this->defaultNamespace->returningActionId = $this->ktId;
					$this->_forward("index","edit","ce",array("CAEX_ID" => $this->defaultNamespace->callingActionId));
				}else{
					$this->redirectTo('list');
				}

			} else {
				//not valid
			}
		} else {
			//not post

			$this->form->setAction(Zend_Controller_Front::getInstance()->getBaseUrl().'/ce/editkeytable/index');
		}
		$this->view->form = $this->form;
	}
	public function updateAction()
	{
		$ktTable = new KeyTable();
		$this->form->addElement('hidden', KeyTable::COL_ID, array('required'=>true));
		if ($this->getRequest()->isPost()) {
			if ($this->form->isValid($this->getRequest()->getParams())) {
				$upload = $this->form->uploadElement->getTransferAdapter();
				$filename = $this->form->uploadElement->getFilename(null,false);
				$upload->receive();
				if ($upload->isReceived($filename)) {
					$data = array(
					//KeyTable::COL_AGE => $this->form->getValue(KeyTable::COL_AGE),
//					KeyTable::COL_AREA => $this->form->getValue(KeyTable::COL_AREA),
					//KeyTable::COL_MATURITY => $this->form->getValue(KeyTable::COL_MATURITY),
					KeyTable::COL_NAME => $this->form->getValue(KeyTable::COL_NAME),
//					KeyTable::COL_SPECIES => $this->form->getValue(KeyTable::COL_SPECIES),
//					KeyTable::COL_SUBJECT => $this->form->getValue(KeyTable::COL_SUBJECT),
					KeyTable::COL_FILENAME => $filename
					);
					$ktTable->update($data, $ktTable->getAdapter()->quoteInto(KeyTable::COL_ID.'=?',$this->form->getValue(KeyTable::COL_ID)));
				}
				$this->redirectTo('list');
			}
		} else {
			//not post
			$keyArray = $ktTable->find($this->getRequest()->getParam(KeyTable::COL_ID))->toArray();
			$this->form->populate($keyArray[0]);
		}
		$this->view->form = $this->form;
		$this->render('index');
	}

	public function listAction()
	{
		$this->defaultNamespace->callingAction = 'ce/editkeytable/list';
		$this->defaultNamespace->callingActionId = null;
		$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
		$select = $dbAdapter->select();
		$select->from(KeyTable::TABLE_NAME);
		$this->view->keyArray = $dbAdapter->fetchAll($select);
	}

	public function inserted()
	{
		//$this->defaultNamespace->returnId = $this->ktId;
	}

	public function redirectTo($action)
	{
		$Redirect = new Zend_Controller_Action_Helper_Redirector();
		$Redirect->setGoto($action,'editkeytable','ce');
	}
}
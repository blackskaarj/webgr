<?php
class Ce_EditexpertiseController extends Zend_Controller_Action
{
	private $form;
	private $defaultNamespace;
	private $expId;

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

		$this->form = new Ce_Form_EditExpertise();
	}

	public function indexAction()
	{
		if ($this->getRequest()->isPost()) {
			$paramCancel = $this->getRequest()->getParam('cancel');
			$cancelButtonPressed = isset($paramCancel);
			if ($cancelButtonPressed) {
				$this->defaultNamespace->returningAction = 'ce/editexpertise/index';
				switch ($this->defaultNamespace->callingAction) {
					case 'ce/edit/index':
						$this->_forward("index","edit","ce",array("CAEX_ID" => $this->defaultNamespace->callingActionId));
						break;
					case 'ce/editexpertise/list':
						$this->_forward("list","editexpertise","ce");
						break;
					default:
						throw new Zend_Exception;
						break;
				}
			}

			if ($this->form->isValid($this->getRequest()->getParams())) {
				//insert/update
				$expTable = new Expertise();
				$data = array(Expertise::COL_SPECIES => $this->form->getValue(Expertise::COL_SPECIES),
				Expertise::COL_AREA => $this->form->getValue(Expertise::COL_AREA),
				Expertise::COL_SUBJECT =>$this->form->getValue(Expertise::COL_SUBJECT));
				$this->expId = $expTable->insert($data);
				//$this->redirectTo('inserted');
				//$this->inserted();
				if($this->defaultNamespace->callingActionId != null){
					$this->defaultNamespace->returningAction = 'ce/editexpertise/index';
					$this->defaultNamespace->returningActionId = $this->expId;
                                        $this->_forward("index","edit","ce",array("CAEX_ID" => $this->defaultNamespace->callingActionId));
				}else{
					$this->redirectTo('list');
				}
			} else {
				//not valid
			}
		} else {
			//not post
			$this->form->setAction(Zend_Controller_Front::getInstance()->getBaseUrl().'/ce/editexpertise/index');
		}
		$this->view->form = $this->form;
	}
	public function updateAction()
	{
		$this->form->addElement('hidden', Expertise::COL_ID, array('required'=>true));
		$expertiseTable = new Expertise();
		if ($this->getRequest()->isPost()) {
			if ($this->form->isValid($this->getRequest()->getParams())) {
				$data = array(  Expertise::COL_AREA => $this->form->getValue(Expertise::COL_AREA),
				Expertise::COL_SPECIES => $this->form->getValue(Expertise::COL_SPECIES),
				Expertise::COL_SUBJECT => $this->form->getValue(Expertise::COL_SUBJECT));
				$expertiseTable->update($data,$expertiseTable->getAdapter()->quoteInto(Expertise::COL_ID.'=?',$this->form->getValue(Expertise::COL_ID)));
				$this->redirectTo('list');
			}
		}else{
			$expArray = $expertiseTable->find($this->getRequest()->getParam(Expertise::COL_ID))->toArray();
			$this->form->populate($expArray[0]);
		}
		$this->view->form = $this->form;
		$this->render('index');
	}
	public function listAction()
	{
		$this->defaultNamespace->callingAction = 'ce/editexpertise/list';
		$this->defaultNamespace->callingActionId = null;
		$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
		$select = $dbAdapter->select();
		$select->from(Expertise::TABLE_NAME);
		$select->joinLeft(array('valSpec' => ValueList::TABLE_NAME),
		                  Expertise::COL_SPECIES . ' = ' . 'valSpec.' . ValueList::COL_ID,
		                  array('valSpec' => ValueList::COL_VALUE));
        $select->joinLeft(array('valSubj' => ValueList::TABLE_NAME),
                          Expertise::COL_SUBJECT . ' = ' . 'valSubj.' . ValueList::COL_ID,
                          array('valSubj' => ValueList::COL_VALUE));		                  
		$this->view->expArray = $dbAdapter->fetchAll($select);
	}
	public function redirectTo($action)
	{
		$Redirect = new Zend_Controller_Action_Helper_Redirector();
		$Redirect->setGoto($action,'editexpertise','ce');
	}
}
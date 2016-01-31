<?php
class Ce_EditparticipantsController extends Zend_Controller_Action
{
	private $defaultNamespace;
	private $myNamespace;

	public function indexAction()
	{
		$this->defaultNamespace = new Zend_Session_Namespace('default');
		$this->myNamespace = new Zend_Session_Namespace('editParticipants');
		$ceId = $this->defaultNamespace->callingActionId;
		$this->view->callingActionId = $ceId;

		if ($this->getRequest()->isPost()) {
			//save form params to namespace
			$this->myNamespace->params = $this->getRequest()->getParams();
			//check which submit button has been clicked
			if ($this->getRequest()->getParam('Check_all')!=NULL) {
				$this->myNamespace->checkAll = TRUE;
				$this->redirectTo ('index');
			}
			if ($this->getRequest()->getParam('Uncheck_all')!=NULL) {
				$this->myNamespace->checkAll = FALSE;
				$this->redirectTo ('index');
			}
			if ($this->getRequest()->getParam('Remove_from_participants')!=NULL) {
				$success = $this->deleteParticipants();
				if (!$success) {
					//XXX show error
				}
				$this->redirectTo ('index');
			}

			if ($this->getRequest()->getParam('Apply_to_selected')!=NULL) {
				$success = $this->applySettingsToParticipants();
				if (!$success) {
					///XXX show error
				}
				$this->redirectTo ('index');
			}
		} else {
			//not post
			//TODO getCaliExName and display in header

			$userTable = new User();
			$participantsTable = new Participant();
			$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
			$select = $dbAdapter->select();
			$select->from(array('user'=>$userTable->getTableName()), array (User::COL_LASTNAME, User::COL_FIRSTNAME, User::COL_USERNAME));
			$select->join(array('part'=>$participantsTable->getTableName()),
			$dbAdapter->quoteIdentifier('user.' . User::COL_ID). '=' . $dbAdapter->quoteIdentifier('part.' .Participant::COL_USER_ID),
			array(Participant::COL_ID, Participant::COL_NUMBER, Participant::COL_EXPERTISE_LEVEL, Participant::COL_STOCK_ASSESSMENT, Participant::COL_ROLE));
			$select->where($dbAdapter->quoteInto('part.'.Participant::COL_CE_ID.' = ?', $ceId));
			//echo $select;

			$paginator = new Ble422_Paginator_Extended($select,$this->getRequest());

			$paginator->setHeader(array(array('raw'=>User::COL_LASTNAME,'name'=>'Last name'),
			array('raw'=>User::COL_FIRSTNAME,'name'=>'First name'),
			array('raw'=>User::COL_USERNAME,'name'=>'User name'),
			array('raw'=>Participant::COL_NUMBER,'name'=>'Reader no.'),
			array('raw'=>Participant::COL_EXPERTISE_LEVEL,'name'=>'Expertise level'),
			array('raw'=>Participant::COL_STOCK_ASSESSMENT,'name'=>'Stock assess.'),
			array('raw'=>Participant::COL_ROLE,'name'=>'Role')
			));

			$paginator	->setCurrentPageNumber($this->getRequest()->getParam('page'))
			->setItemCountPerPage(50)//$this->_getParam('itemCountPerPage'))
			->setPageRange(10)
			->orderBy($this->getRequest()->getParam('orderBy'));//$this->_getParam('pageRange'));
			 
			Zend_View_Helper_PaginationControl::setDefaultViewPartial(
                          'partials/list_pagination_control.phtml'); 
			$this->view->paginator = $paginator;

			$this->defaultNamespace->callingAction = "ce/editparticipants/index";
			//callingActionId stays the same
			$this->view->callingAction = $this->defaultNamespace->callingAction;
			$this->view->callingActionId = $ceId;
			$this->view->ceName = Default_SimpleQuery::getCeName($ceId);

			$this->view->checkAll = $this->myNamespace->checkAll;
			unset($this->myNamespace->checkAll);
			//TODO partTable.phtml wird schon auf this->checkAll geprüft, vom Controller übergeben, ebenso reload bei uncheck all
		}
	}

	//TODO form schönmachen (ids raus, wenn möglich, unterstriche aus label entf.)
	//is called from user/search/search, look search.phtml
	//TODO no user selected
	public function addparticipantsAction()
	{
		//inserts one or many selected participants
		if ($this->getRequest()->isPost()) {
			$this->defaultNamespace = new Zend_Session_Namespace('default');
			$ceId = $this->defaultNamespace->callingActionId;
				
			$userIds = $this->getRequest()->getParam(User::COL_ID);
			$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
			$part = new Participant();
			foreach ($userIds as $userId) {
				//count participants in CE
				/*				$where = $part->getAdapter()->quoteInto(Participant::COL_CE_ID.' = ?', $ceId);
				 $rowSet = $part->fetchAll($where);
				 $numberOfPart = count($rowSet);*/

				//SELECT MAX(column_name) FROM table_name where partceid=ceid
				$select = $dbAdapter->select();
				$select->from(Participant::TABLE_NAME, array('max' => 'MAX('.Participant::COL_NUMBER.')'));
				$select->where(Participant::COL_CE_ID.' = ?', $ceId);
				$resultArray = $dbAdapter->fetchAll($select);
				$select->reset();

				//reader number for new CE part. is highest reader no. of old participents within CE + 1
				$readerNo = $resultArray[0]['max'] + 1;
				$data = array (	Participant::COL_CE_ID => $ceId,
				Participant::COL_USER_ID => $userId,
				Participant::COL_NUMBER => $readerNo);
				$part->insert($data);
			}
			$this->redirectTo('index');
		}
	}

	private function deleteParticipants()
	//no Zend_Action because form post data would be lost
	{
		//TODO check if participant has any anno in ce
		$params = $this->myNamespace->params;
		$ceId = $this->defaultNamespace->callingActionId;

		if (!isset($params[Participant::COL_ID])) {
			return FALSE;
		}

		$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
		$select = $dbAdapter->select();
		$select->from(array('anno'=>Annotations::TABLE_NAME)
		//,array(Annotations::COL_ID, Annotations::COL_PART_ID)
		);
		$select->joinLeft(array('cehim'=>CeHasImage::TABLE_NAME),
									'anno.'.Annotations::COL_CE_HAS_IMAGE_ID.'='.'cehim.'.CeHasImage::COL_ID
		//,array()
		);
		//continue select in loop
		$partIds = $params[Participant::COL_ID];
		foreach ($partIds as $partId) {
			$select->where('anno.'.Annotations::COL_PART_ID.' = ?', $partId);
			$resultArray = $dbAdapter->fetchAll($select);
				
			if (empty($resultArray)) {
				//if result array is empty = Array[0], the participant has no annotations in this calibration exercise
				//delete participant
				$part = new Participant();
				$where = $part->getAdapter()->quoteInto(Participant::COL_ID.'= ?', $partId);
				$part->delete($where);
			}
		}
		return TRUE;
	}

	private function applySettingsToParticipants()
	//no Zend_Action because form post data would be lost
	{
		//only checked attributes are processed
		//returns FALSE if no attribute and/or no participant is checked
		//works only for single-Select-Inputs!
		//checked value = COL_NAME
		//form control name = COL_NAME
		//form control value = CELL_VALUE

		$partAttrs = $this->getRequest()->getParam('participantAttributeChecked');
		if ($partAttrs == NULL) {
			//no attribute checked
			return FALSE;
		}
		$data = array();
		foreach ($partAttrs as $attr=>$attrName) {
			$attrValue = $this->getRequest()->getParam($attrName);
			//boolean: write 1 instead of "on" to database
			if ($attrName == Participant::COL_STOCK_ASSESSMENT && $attrValue == "on") {
				$attrValue = 1;
			}
			$data = $data + array($attrName => $attrValue);
		}
		//echo $data;

		$partIds = $this->getRequest()->getParam(Participant::COL_ID);
		if ($partIds == NULL) {
			//no participant checked
			return FALSE;
		}
		$part = new Participant();
		foreach ($partIds as $partId) {
			$where = $part->getAdapter()->quoteInto(Participant::COL_ID.' = ?', $partId);
			$part->update($data, $where);
		}
		$this->redirectTo('index');

		return TRUE;
	}

	public function redirectTo($action)
	{
		$Redirect = new Zend_Controller_Action_Helper_Redirector();
		$Redirect->setGotoSimple($action,'editparticipants','ce');
	}
}
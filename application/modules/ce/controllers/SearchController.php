<?php

class Ce_SearchController extends Zend_Controller_Action
{
	private $form;
	private $defaultNamespace;

	public function init()
	{
		$this->defaultNamespace = new Zend_Session_Namespace('default');
	}

	public function formAction()
	{
		$this->form = new Ce_Form_Search();

		$elem  = $this->form->getElement(CalibrationExercise::COL_EXPERTISE_ID);
		$elem->addMultioptions (array(NULL => 'All'));

		//$this->form->setAction('/ce/search/list');
		$this->form->setAction('/ce/search/form');
		$request = $this->getRequest();
		$params = $request->getParams();
		//$this->view->form = $this->form;
		if ($request->isPost() && $this->form->isValid($params))
		{
			$this->defaultNamespace->callingActionId = $this->form->getValue(CalibrationExercise::COL_EXPERTISE_ID);
			$this->redirectTo('list');
		}
		else
		{
			$this->view->form = $this->form;
		}
	}

	//delete defNamespace and redirect to form
	public function resetAction() {
		$this->defaultNamespace->callingAction = '';
		$this->defaultNamespace->callingActionId = '';
		$this->redirectTo('form');
	}

	public function listAction()
	{
		$this->defaultNamespace->callingAction = 'ce/search/list';
		$this->form = new Ce_Form_Search();
		if( $this->form->isValid($this->getRequest()->getParams())){
			//showXYZ shows links/actions
			$this->view->showStart = FALSE;
			if ($this->defaultNamespace->callingActionId != '') {
				$this->process(array(CalibrationExercise::COL_EXPERTISE_ID => $this->defaultNamespace->callingActionId));
			} else {
				$this->process(array());
				//throw new Zend_Exception('Error: no expertise set');
			}
		}
	}
	public function myceAction()
	{
		//showXYZ shows links/actions
		$this->defaultNamespace->callingAction = 'ce/search/myce';
		$this->view->showStart = TRUE;
		$this->view->deleteTrainingCe = TRUE;
		$this->process(array(User::COL_ID => AuthQuery::getUserId()));
	}

	private function process(array $params)
	{
		//params-array filters (where) and/or shows more info (joins)
		$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
		$select = $dbAdapter->select();
		$select->from(array('celist'=>View_CeList::NAME),
		array(  View_CeList::COL_WORK_NAME,
		View_CeList::COL_CAEX_NAME,
		View_CeList::COL_CAEX_DESC,
		View_CeList::COL_CAEX_ID,
		View_CeList::COL_WORK_ID,
		View_CeList::COL_CAEX_TRAIN,
                              'images' => new Zend_Db_Expr('count(cehim.'.CeHasImage::COL_ID.')'),
		));
		$select->joinLeft(array('exp'=>Expertise::TABLE_NAME),
		$dbAdapter->quoteIdentifier('celist.' . View_CeList::COL_EXPE_ID). '=' . $dbAdapter->quoteIdentifier('exp.' .Expertise::COL_ID));
		$select->joinLeft(array('key'=>KeyTable::TABLE_NAME),
		$dbAdapter->quoteIdentifier('celist.' . View_CeList::COL_KETA_ID). '=' . $dbAdapter->quoteIdentifier('key.' .KeyTable::COL_ID));
		$select->joinLeft(array('cehim'=>CeHasImage::TABLE_NAME),
		$dbAdapter->quoteIdentifier('celist.' . View_CeList::COL_CAEX_ID). '=' . $dbAdapter->quoteIdentifier('cehim.' .CeHasImage::COL_CALIBRATION_EXERCISE_ID),
		array());
		$select->joinLeft(array('vali1'=>ValueList::TABLE_NAME),
		$dbAdapter->quoteIdentifier('vali1.' . ValueList::COL_ID). '=' . $dbAdapter->quoteIdentifier('exp.' .Expertise::COL_SPECIES),
		array(Expertise::COL_SPECIES => 'vali1.' . ValueList::COL_VALUE));
		$select->joinLeft(array('vali2'=>ValueList::TABLE_NAME),
		$dbAdapter->quoteIdentifier('vali2.' . ValueList::COL_ID). '=' . $dbAdapter->quoteIdentifier('exp.' .Expertise::COL_SUBJECT),
		array(Expertise::COL_SUBJECT => 'vali2.' . ValueList::COL_VALUE));

		$select->group('celist.' . View_CeList::COL_CAEX_ID);

		$headerArray = array(
		array('raw'=>View_CeList::COL_CAEX_NAME,'name'=>'CE name'),
		array('raw'=>View_CeList::COL_WORK_NAME,'name'=>'Workshop name'),
		array('raw'=>Expertise::COL_AREA,'name'=>'Exp area'),
		array('raw'=>Expertise::COL_SPECIES,'name'=>'Exp species'),
		array('raw'=>Expertise::COL_SUBJECT,'name'=>'Exp subject'),
		array('raw'=>KeyTable::COL_FILENAME,'name'=>'Protocol'));
		if (array_key_exists(User::COL_ID,$params)) {
			//filter...
			$select->joinLeft(array('part'=>Participant::TABLE_NAME),
			$dbAdapter->quoteIdentifier('celist.' . View_CeList::COL_CAEX_ID). '=' . $dbAdapter->quoteIdentifier('part.' .Participant::COL_CE_ID),
			array('part.' . Participant::COL_ID));
			//where i am participant
			$select->where('part.'.Participant::COL_USER_ID.'=?',$params[User::COL_ID]);
				

			$userId = $params[User::COL_ID];
			$select->joinLeft(array('work'=>Workshop::TABLE_NAME),
                             'work.'.Workshop::COL_ID.'='.'celist.'.View_CeList::COL_WORK_ID,
			array(Workshop::COL_USER_ID));
			//or where i am workshop-manager
			$select->orWhere('work.'.Workshop::COL_USER_ID.'= ?', $userId);

			array_push($headerArray,array('raw'=>View_CeList::COL_IMAGES,'name'=>'Images'));
		}
		if (array_key_exists(CalibrationExercise::COL_EXPERTISE_ID,$params)) {
			$select->where('celist.' . CalibrationExercise::COL_EXPERTISE_ID.'=?',$params[CalibrationExercise::COL_EXPERTISE_ID]);
			//			$select->joinLeft(array('cehim'=>CeHasImage::TABLE_NAME),
			//			$dbAdapter->quoteIdentifier('celist.' . View_CeList::COL_CAEX_ID). '=' . $dbAdapter->quoteIdentifier('cehim.' .CeHasImage::COL_CALIBRATION_EXERCISE_ID),
			//			array());
		}
		//Hinzugefügt am 14.10.2009 Tabellenzeilen mit WS name = training exercise
		//sollten nicht angezeigt werden (im Menüpunkt search calibration exercises)
		//kompletter if-Zweig wurde hinzugefügt
		if($this->getRequest()->getModuleName() == 'ce'
		&& $this->getRequest()->getControllerName() == 'search'
		&& $this->getRequest()->getActionName() == 'list') {
			$select->where(CalibrationExercise::COL_WORKSHOP_ID.' is not null');
		}
		//echo $select;

		/**
		 * Paginator control
		 */
		$paginator = new Ble422_Paginator_Extended($select,$this->getRequest());
		$paginator->setHeader($headerArray);
		$paginator  ->setCurrentPageNumber($this->getRequest()->getParam('page'))
		->setItemCountPerPage(10)//$this->_getParam('itemCountPerPage'))
		->setPageRange(5)
		->orderBy($this->getRequest()->getParam('orderBy'));//$this->_getParam('pageRange'));

		Zend_View_Helper_PaginationControl::setDefaultViewPartial(
                          'partials/list_pagination_control.phtml');

		$detailId = $this->getRequest()->getParam('detailId');
		if($detailId != null){
			$detailId = intval($detailId);
			/**
			 * show wether CE is stopped
			 */
			try{
				if(Default_SimpleQuery::isCeStopped($detailId)) {
					$this->view->ceInfo = 'CE is <b>STOPPED</b>';
				} else {
					$this->view->ceInfo = 'CE is <b>RUNNING</b>';
				}
			} catch (exception $e) {
				$this->view->ceInfo = $e->getMessage();
			}
			/**
			 * show Coordinator
			 */
			if($coordinators = Participant::getCoordinators($detailId)) {
				$this->view->ceCoordinators = $coordinators;
			}
				
			/**
			 * imageset and shown attributes
			 */
			$imagesetSelect = $dbAdapter->select();
			$imagesetSelect->from('v_imageset_info');
			$imagesetSelect->where(CalibrationExercise::COL_ID. '=?',$detailId);
			$imagesetSelect->group(AttributeDescriptor::COL_NAME);
			$imagesetArray = $dbAdapter->fetchAll($imagesetSelect);
			$this->view->imagesetArray = $imagesetArray;

			$shownAttrSelect = $dbAdapter->select();
			$shownAttrSelect->from(array('cehat'=>CeHasAttributeDescriptor::TABLE_NAME));
			$shownAttrSelect->join(array('attr'=>AttributeDescriptor::TABLE_NAME),
	        'cehat.'.CeHasAttributeDescriptor::COL_ATDE_ID . '= attr.'.AttributeDescriptor::COL_ID);
			$shownAttrSelect->where(CalibrationExercise::COL_ID. '=?',$detailId);
			$shownAttrArray = $dbAdapter->fetchAll($shownAttrSelect);
			$this->view->shownAttrArray = $shownAttrArray;
			$this->view->detailId = $detailId;

		}
		$this->view->userRole = AuthQuery::getUserRole();
		$this->view->expeId = $this->getRequest()->getParam(CalibrationExercise::COL_EXPERTISE_ID);
		$this->view->paginator = $paginator;

		$this->render('list');
	}

	public function redirectTo($action , array $params = array())
	{
		$redirect = new Zend_Controller_Action_Helper_Redirector();
		$redirect->setGoto($action,'search','ce', $params);
	}
}
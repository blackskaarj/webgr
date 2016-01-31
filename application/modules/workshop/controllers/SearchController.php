<?php
class Workshop_SearchController extends Zend_Controller_Action {

	private $myws = false;
	
	/**
     * @deprecated
	 */
	public function formAction()
	{
		
	}
	
	public function listAction(){
        $dbAdapter = Zend_Registry::get('DB_CONNECTION1');
        
        $select = $dbAdapter->select();
        $select->from(array('wt'=>Workshop::TABLE_NAME),array(Workshop::COL_NAME,
                                                                        Workshop::COL_START_DATE,
                                                                        'wt.'.Workshop::COL_ID,
                                                                        Workshop::COL_END_DATE,
                                                                        Workshop::COL_USER_ID));
        $select->join(array('vl'=>ValueList::TABLE_NAME),
                        $dbAdapter->quoteIdentifier('wt.' . Workshop::COL_LOCATION) . '=' . $dbAdapter->quoteIdentifier('vl.' . ValueList::COL_ID),array(ValueList::COL_NAME));
        $select->join(array('us'=>User::TABLE_NAME),
                        $dbAdapter->quoteIdentifier('wt.' . Workshop::COL_USER_ID). '=' . $dbAdapter->quoteIdentifier('us.' . User::COL_ID), array(User::COL_USERNAME));
        if($this->myws){
        	// get user id from current user
            $userId = AuthQuery::getUserId();
            
            //pushed the join 'part' where to extra where method
        	$select->distinct();
        	//left join to show ws without CEs, too
        	$select->joinLeft(  array('caex'=>CalibrationExercise::TABLE_NAME),
                            $dbAdapter->quoteIdentifier('wt.' . Workshop::COL_ID). '=' . $dbAdapter->quoteIdentifier('caex.' . CalibrationExercise::COL_WORKSHOP_ID), 
                            array());
            //left join to show ws without CEs, too
            $select->joinLeft(  array('part'=>Participant::TABLE_NAME),
                            $dbAdapter->quoteIdentifier('caex.' . CalibrationExercise::COL_ID). '=' . $dbAdapter->quoteIdentifier('part.' . Participant::COL_CE_ID),         
                            array());
            //show workshops where you are participant in any CE
            $select->where('part.' . Participant::COL_USER_ID . '= ?', $userId, 'int');
            //show workshops where you are ws-manager
            $select->orWhere('wt.' . Workshop::COL_USER_ID . '= ?', $userId, 'int');
            
            
        }              
        $paginator = new Ble422_Paginator_Extended($select,$this->getRequest());
        $paginator->setHeader(array(array('raw'=>ValueList::COL_NAME,'name'=>'Location'),
                                    array('raw'=>Workshop::COL_NAME,'name'=>'Workshopname'),
                                    array('raw'=>Workshop::COL_START_DATE,'name'=>'Start date'),
                                    array('raw'=>Workshop::COL_END_DATE,'name'=>'End Date'),
                                    array('raw'=>User::COL_USERNAME,'name'=>'Manager')));
        $paginator  ->setCurrentPageNumber($this->getRequest()->getParam('page'))
                    ->setItemCountPerPage(1000)//$this->_getParam('itemCountPerPage'))
                    ->setPageRange(10)
                    ->orderBy($this->getRequest()->getParam('orderBy'));//$this->_getParam('pageRange'));
                    
        $this->view->paginator = $paginator;
        $this->view->userRole = AuthQuery::getUserRole();
        // for the redirect from mywsAction!
        $this->render('list');
	}
	
	public function mywsAction()
	{
		$this->myws = true;
		$this->listAction();
	}
	
	public function detailsAction() {
		$wsId = $this->getRequest()->getParam(Workshop::COL_ID);
		$dbAdapter = Zend_Registry::get('DB_CONNECTION1'); 
		$wsSelect = $dbAdapter->select();
		$wsSelect->from(  array('work' => Workshop::TABLE_NAME));
		$wsSelect->join(  array('inst' => ValueList::TABLE_NAME),
		                  'inst.' . ValueList::COL_ID . "=" . 'work.' . Workshop::COL_HOST_ORGANISATION,
		                  array('instname' => ValueList::COL_NAME));
        $wsSelect->join(  array('local' => ValueList::TABLE_NAME),
                          'local.' . ValueList::COL_ID . "=" . 'work.' . Workshop::COL_LOCATION,
                          array('location' => ValueList::COL_NAME));			                  
        $wsSelect->join(  array('user' => User::TABLE_NAME),
                          'user.' . User::COL_ID . "=" . 'work.' . Workshop::COL_USER_ID,
                          array('manager' => User::COL_USERNAME));
        $wsSelect->where(Workshop::COL_ID . '=?' , $wsId);
		$workshopArray = $dbAdapter->fetchAll($wsSelect);

		/**
		 * ce list
		 */
		$ceSelect = $dbAdapter->select();
		$ceSelect->from(CalibrationExercise::TABLE_NAME);
		$ceSelect->where(CalibrationExercise::COL_WORKSHOP_ID . '=?',$wsId);
		$this->view->ceArray = $dbAdapter->fetchAll($ceSelect);
		
		/**
         * links
		 */
		$infoTable = new WorkshopInfo();
		$linkSelect = $infoTable->select();
		$linkSelect->where(WorkshopInfo::COL_WORKSHOP_ID . "=?", $wsId);
		$linkSelect->where(WorkshopInfo::COL_FILE . " IS NULL");
		
		$linkResult = $infoTable->fetchAll($linkSelect);
		if($linkResult != null){
			$linkArray = $linkResult->toArray();
		}else{
            $linkArray = array();			
		}
		
		/**
         * files
		 */
		$fileSelect = $infoTable->select();
        $fileSelect->where(WorkshopInfo::COL_WORKSHOP_ID . "=?", $wsId);
        $fileSelect->where(WorkshopInfo::COL_LINK . " IS NULL");
        
        $fileResult = $infoTable->fetchAll($fileSelect);
        if($fileResult != null){
        	$fileArray = $fileResult->toArray();
        }else{
            $fileArray = array();	
        }
        
        $this->view->userRole = AuthQuery::getUserRole();
        $this->view->workshopArray = $workshopArray[0];
        $this->view->linkArray = $linkArray;
        $this->view->fileArray = $fileArray;
        $this->view->wsId = $wsId;
	}

}
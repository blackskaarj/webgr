<?php
class Fish_EditController extends Zend_Controller_Action {
		
	private $form;
	private $fishTable;
	
	public function init() 
	{
	   $this->form = new Fish_Form_Edit();
	   $this->fishTable = new Fish();	
	}
		
	public function insertAction()
	{
		
	}
	
	public function updateAction()
	{
		$request = $this->getRequest();
		
		$fishId = intval($request->getParam(Fish::COL_ID));
		$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
		
		if ($request->isPost() AND $this->form->isValid($request->getParams($fishId))) {
			$data = array(	Fish::COL_SAMPLE_CODE => $this->form->getValue(Fish::COL_SAMPLE_CODE),
							Fish::COL_USER_ID => AuthQuery::getUserId());

			$this->fishTable->updateFishAndMetadata($this->form, $fishId, $data);
            
			$namespace = new Zend_Session_Namespace('default');
			$redirect = new Zend_Controller_Action_Helper_Redirector();
			if($namespace->next != null){
	            $nextArray = $namespace->next;
	            $namespace->next = null;
				$redirect->setGotoSimple($nextArray['nextAction'],$nextArray['nextController'],$nextArray['nextModul']);
			}else{
				$redirect->setGotoSimple('search','search','fish');
			}
		}else{
			$fishResult = $this->fishTable->find($fishId)->current();
			if($fishResult != null){
				$fishArray = $fishResult->toArray();
			}else{
				$fishArray = array();
			}
			
			// get meta data
			$select = $dbAdapter->select();
			$select->from(MetaDataFish::TABLE_NAME);
			$select->join(   AttributeDescriptor::TABLE_NAME,
            MetaDataFish::TABLE_NAME. '.' . MetaDataFish::COL_ATTRIBUTE_DESCRIPTOR_ID . '='.AttributeDescriptor::TABLE_NAME.'.ATDE_ID');
			$select->where(MetaDataFish::COL_FISH_ID . '=?', $fishId);
			$metaArray = $dbAdapter->fetchAll($select);
			
			$this->form->dynPopulate($metaArray,MetaDataFish::COL_VALUE,$fishArray);
			$this->view->form = $this->form;
            $this->render('form');
		}
	}

	public function deleteAction() {
		//delete fish
		//delete metadata fish done by DB
		$request = $this->getRequest();
		$fishId = intval($this->getRequest()->getParam(Fish::COL_ID));
		$fish = new Fish();
		$rowset = $fish->find($fishId);
		if (count($rowset) == 1) {
			//note: delete of metadata is executed from db
			$fish->delete($fish->getAdapter()->quoteInto(Fish::COL_ID .' = ?', $fishId));
		}
		$redirect = new Zend_Controller_Action_Helper_Redirector();
		$redirect->setGoto('search','search','fish');
	}
}
<?php
class Workshop_InfoController extends Zend_Controller_Action {

	private $infoTable;

	public function init()
	{
		$this->infoTable = new WorkshopInfo();
	}

	public function addlinkAction() {
		$this->view->headline = 'Add link';
		$form = new Workshop_Form_Link();
		$request = $this->getRequest();

		if ($request->isPost() AND $form->isValid($request->getParams())) {
			$data = array( WorkshopInfo::COL_WORKSHOP_ID => $form->getValue(WorkshopInfo::COL_WORKSHOP_ID),
			WorkshopInfo::COL_LINK => $form->getValue(WorkshopInfo::COL_LINK),
			WorkshopInfo::COL_TEXT => $form->getValue(WorkshopInfo::COL_TEXT));
			$this->infoTable->insert($data);
			$this->redirectTo();
		}else{
			$this->view->form = $form;
			$this->render('form');
		}
	}
	public function deletelinkAction()
	{
		$this->infoTable->delete(WorkshopInfo::COL_ID . '=' . intval($this->getRequest()->getParam(WorkshopInfo::COL_ID)));
		$this->redirectTo();
	}
	public function addfileAction()
	{
		$this->view->headline = 'Add file';
		$form = new Workshop_Form_File();
		$request = $this->getRequest();

		if ($request->isPost() AND $form->isValid($request->getParams())) {
			 
			$upload = $form->uploadElement->getTransferAdapter();
			$filename = $form->uploadElement->getFilename(null,false);
			//	        $upload = new Zend_File_Transfer_Adapter_Http();
			$upload->receive();
			if ($upload->isReceived($file)) {
				$data = array(    WorkshopInfo::COL_FILE => $filename,
				WorkshopInfo::COL_TEXT => $form->getValue(WorkshopInfo::COL_TEXT),
				WorkshopInfo::COL_WORKSHOP_ID => $form->getValue(WorkshopInfo::COL_WORKSHOP_ID));
				$this->infoTable->insert($data);
				$this->redirectTo();
			}
		}
		$this->view->form = $form;
		$this->render('form');
	}
	public function deletefileAction()
	{
		$infoResult = $this->infoTable->find(intval($this->getRequest()->getParam(WorkshopInfo::COL_ID)))->current();
		if($infoResult != null){
			$infoArray = $infoResult->toArray();
        
            $path = __FILE__;
	        $path = dirname($path);
	        $path = dirname($path);
	        $path = dirname($path);
	        $path = dirname($path);
	        $path = dirname($path);
			$path = $path . '/public/infoFiles';

			if(unlink($path . '/' . $infoArray[WorkshopInfo::COL_FILE])){
				$this->infoTable->delete(WorkshopInfo::COL_ID . '=' . intval($this->getRequest()->getParam(WorkshopInfo::COL_ID)));
			}
			$this->redirectTo();
		}
		//$this->redirectTo();
		$this->render('form');
	}
	public function redirectTo($params = array())
	{
		$Redirect = new Zend_Controller_Action_Helper_Redirector();
		$Redirect->setGotoSimple('details','search','workshop',array(WorkshopInfo::COL_WORKSHOP_ID => $this->getRequest()->getParam(WorkshopInfo::COL_WORKSHOP_ID)));
	}
	//ENDE: class ...
}
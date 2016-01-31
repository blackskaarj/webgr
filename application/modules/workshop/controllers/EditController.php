<?php

class Workshop_EditController extends Zend_Controller_Action {

	private $form;

	public function Init()
	{
		$this->form = new Workshop_Form_Edit();
		$this->view->form = $this->form;
	}
	public function newAction()
	{
		$this->form->removeElement(Workshop::COL_ID);

		$table = new Workshop();
		$request = $this->getRequest();
		$insertValues = $request->getParams();
		$wsNamespace = new Zend_Session_Namespace('workshop');

		if ($request->isPost()){
			if($this->getRequest()->getParam('save') != null){
				//save Button clicked
				if($this->form->isValid($insertValues)){
					if ($this->form->getValue('Token') == $wsNamespace->Token) {
						$data = array (	Workshop::COL_NAME => $this->form->getValue(Workshop::COL_NAME),
						Workshop::COL_LOCATION => $this->form->getValue(Workshop::COL_LOCATION),
						Workshop::COL_START_DATE => $this->form->getValue(Workshop::COL_START_DATE),
						Workshop::COL_END_DATE => $this->form->getValue(Workshop::COL_END_DATE),
						Workshop::COL_HOST_ORGANISATION => $this->form->getValue(Workshop::COL_HOST_ORGANISATION),
						Workshop::COL_USER_ID => $this->form->getValue(Workshop::COL_USER_ID));
						$table->insert($data);
						$wsNamespace->unsetAll();
						$this->redirectTo();
					}else{
						$this->form->reset();
						$this->render('outOfDate');
					}
				}
			}else if ($this->getRequest()->getParam('setManager') != null) {
				// new ws manager button clicked
				$wsNamespace->formValues = $this->getRequest()->getParams();
				$defaultNamespace = new Zend_Session_Namespace('default');
				$defaultNamespace->callingAction = 'workshop/edit/new';
				$this->redirectTo('index','search','user');
			}else{
				// new ws manager has choosen and is loading
				$userTable = new User();
				$userResult = $userTable->find($this->getRequest()->getParam(Workshop::COL_USER_ID))->current();
				if($userResult != null){
					$userArray = $userResult->toArray();
				}else{
					$userArray = array(Workshop::COL_USER_ID => null,
					User::COL_USERNAME => 'not valid');
				}
				$valueArray = $wsNamespace->formValues;
				$valueArray[Workshop::COL_USER_ID] = $userArray[Workshop::COL_USER_ID];
				$valueArray[User::COL_USERNAME] = $userArray[User::COL_USERNAME];
				$this->form->isValid($valueArray);

				if ($this->form->getValue('Token') == null) {
					$guid = new Ble422_guid();
					$wsNamespace->Token = $guid->__toString();
					$this->form->getElement('Token')->setValue($guid->__toString());
				}
			}
		}else{
			// first call load form with default values
			// Get part_id and part_role
			$auth = Zend_Auth::getInstance();
			$storage = $auth->getStorage();
			$constUserId = User::COL_ID;
			$userId = $storage->read()->$constUserId;
			$constUsername = User::COL_USERNAME;
			$username = $storage->read()->$constUsername;
			$this->form->isValidPartial(array( Workshop::COL_USER_ID => $userId,
			User::COL_USERNAME => $username));
			$guid = new Ble422_Guid();
			$wsNamespace->Token = $guid->__toString();
			$this->form->getElement('Token')->setValue($guid->__toString());
		}
	}
	public function updateAction()
	{
		$table = new Workshop();
		$request = $this->getRequest();
		$updateValues = $request->getParams();
		$wsNamespace = new Zend_Session_Namespace('workshop');

		//$this->view->WORK_ID = $updateValues[Workshop::COL_ID];
		$workId = intval($this->getRequest()->getParam(Workshop::COL_ID));
		$this->view->WORK_ID = $workId;
		
		//if user is not actual workshop manager, redirect
		if (! AuthQuery::getUserId() == Default_SimpleQuery::getWsManagerUserId($workId)) {
			$this->redirectTo('list', 'search', 'workshop');
			return;
		}
			
		if ($request->isPost()){
			if($this->getRequest()->getParam('save') != null){
				//save Button clicked
				if($this->form->isValid($updateValues)){
					if ($this->form->getValue('Token') == $wsNamespace->Token) {
						$data = array ( Workshop::COL_NAME => $this->form->getValue(Workshop::COL_NAME),
						Workshop::COL_LOCATION => $this->form->getValue(Workshop::COL_LOCATION),
						Workshop::COL_START_DATE => $this->form->getValue(Workshop::COL_START_DATE),
						Workshop::COL_END_DATE => $this->form->getValue(Workshop::COL_END_DATE),
						Workshop::COL_HOST_ORGANISATION => $this->form->getValue(Workshop::COL_HOST_ORGANISATION),
						Workshop::COL_USER_ID => $this->form->getValue(Workshop::COL_USER_ID));
						$table->update($data,Workshop::COL_ID . " = '" . $this->form->getValue(Workshop::COL_ID) . "'");
						$wsNamespace->unsetAll();
						$this->redirectTo();
					}else{
						$this->form->reset();
						$this->render('outOfDate');
					}
				}
			}else if ($this->getRequest()->getParam('setManager') != null) {
				// new ws manager button clicked
				$wsNamespace->formValues = $this->getRequest()->getParams();
				$defaultNamespace = new Zend_Session_Namespace('default');
				$defaultNamespace->callingAction = 'workshop/edit/update';
				$defaultNamespace->callingActionId = $request->getParam(Workshop::COL_ID);
				$this->redirectTo('index','search','user');
			}else{
				// new ws manager has choosen and is loading
				$userTable = new User();
				$userResult = $userTable->find($this->getRequest()->getParam(Workshop::COL_USER_ID))->current();
				if($userResult != null){
					$userArray = $userResult->toArray();
				}else{
					$userArray = array(Workshop::COL_USER_ID => null,
					User::COL_USERNAME => 'not valid');
				}
				$valueArray = $wsNamespace->formValues;
				$valueArray[Workshop::COL_USER_ID] = $userArray[Workshop::COL_USER_ID];
				$valueArray[User::COL_USERNAME] = $userArray[User::COL_USERNAME];
				$this->form->isValid($valueArray);

				if ($this->form->getValue('Token') == null) {
					$guid = new Ble422_guid();
					$wsNamespace->Token = $guid->__toString();
					$this->form->getElement('Token')->setValue($guid->__toString());
				}
			}
		}else{
			// first call load form with default values
			$rowset = $table->find($request->getParam(Workshop::COL_ID))->current();
			if($rowset != null){
				$this->form->setValues($rowset->toArray());
			}
			$this->form->setAction(Zend_Controller_Front::getInstance()->getBaseUrl()."/workshop/edit/update");
			// Get part_id and part_role
			$userTable = new User();
			$userResult = $userTable->find($this->form->getValue(Workshop::COL_USER_ID))->current();
			if($userResult != null){
				$userArray = $userResult->toArray();
			}else{
				$userArray = array(Workshop::COL_USER_ID => null,
				User::COL_USERNAME => 'not valid');
			}
			$this->form->isValidPartial(array( Workshop::COL_USER_ID => $userArray[User::COL_ID],
			User::COL_USERNAME => $userArray[User::COL_USERNAME]));
			$guid = new Ble422_Guid();
			$wsNamespace->Token = $guid->__toString();
			$this->form->getElement('Token')->setValue($guid->__toString());
		}
	}

	public function deleteAction()
	{
		//check if workshop has exercises if not then
		//delete workshop files
		//delete workshop
		//delete ws info
		
		//delete ce
		//delete imageset attributes -> DB on delete cascade
		//delete ce has image -> DB on delete cascade
		//delete ce has attribute desc. -> DB on delete cascade
		//delete participants -> DB on delete cascade
		//delete annotations -> DB on delete cascade
		//delete dots -> DB on delete cascade				
		
		$request = $this->getRequest();
		$workId = intval($this->getRequest()->getParam(Workshop::COL_ID));
		if (AuthQuery::getUserRole() == 'admin') {
			if (Default_SimpleQuery::isValueInTableColumn($workId, new CalibrationExercise(), CalibrationExercise::COL_WORKSHOP_ID)) {
				$request = $this->getRequest();

				$workshop = new Workshop();
				$rowset = $workshop->find($workId);
				if (count($rowset) == 1) {
					$table = new WorkshopInfo();
					//$tableAdapter = $table->getAdapter();
					$select = $table->select();
					//$select->from(WorkshopInfo::TABLE_NAME);
					$select->where(WorkshopInfo::COL_WORKSHOP_ID.' = ?', $workId, 'int');
					echo $select;
					$rowset = $table->fetchAll($select);

					if (count($rowset) >= 1) {
						$rowsetArray = $rowset->toArray();
						$RELATIVE_WORKSHOP_FILES_PATH = 'infoFiles'; //without pre- and post-slash!

						foreach ($rowsetArray as $row) {
							try {
								$filename = $row[WorkshopInfo::COL_FILE];
								if ($filename != NULL) {
									$myFile = $RELATIVE_WORKSHOP_FILES_PATH.'/'.$filename;
									$fh = fopen($myFile, 'w');
									fclose($fh);
									unlink($myFile);

								}
							}
							catch (Exception $e) {
								throw new Zend_Exception('Error: can not open file');
							}
						}
					}
					//note: delete of workshop_info is executed from db
					$workshop->delete($workshop->getAdapter()->quoteInto(Workshop::COL_ID .' = ?', $workId));
				}
			}
		}

		$redirect = new Zend_Controller_Action_Helper_Redirector();
		$redirect->setGoto('myws', 'search', 'workshop');
	}

	public function deleterecursiveAction()
	{
		//delete workshop files
		//delete workshop -> triggers delete ce
		//delete ws info -> DB on delete cascade
		
		//delete ce
		//delete imageset attributes -> DB on delete cascade
		//delete ce has image -> DB on delete cascade
		//delete ce has attribute desc. -> DB on delete cascade
		//delete participants -> DB on delete cascade
		//delete annotations -> DB on delete cascade
		//delete dots -> DB on delete cascade
		
		$request = $this->getRequest();
		$workId = intval($this->getRequest()->getParam(Workshop::COL_ID));
		if (AuthQuery::getUserRole() == 'admin') {
			$request = $this->getRequest();
			$workId = intval($this->getRequest()->getParam(Workshop::COL_ID));

			$workshop = new Workshop();
			$rowset = $workshop->find($workId);
			if (count($rowset) == 1) {
				$table = new WorkshopInfo();
				//$tableAdapter = $table->getAdapter();
				$select = $table->select();
				//$select->from(WorkshopInfo::TABLE_NAME);
				$select->where(WorkshopInfo::COL_WORKSHOP_ID.' = ?', $workId, 'int');
				echo $select;
				$rowset = $table->fetchAll($select);

				if (count($rowset) >= 1) {
					$rowsetArray = $rowset->toArray();
					$RELATIVE_WORKSHOP_FILES_PATH = 'infoFiles'; //without pre- and post-slash!

					foreach ($rowsetArray as $row) {
						try {
							$filename = $row[WorkshopInfo::COL_FILE];
							if ($filename != NULL) {
								$myFile = $RELATIVE_WORKSHOP_FILES_PATH.'/'.$filename;
								$fh = fopen($myFile, 'w');
								fclose($fh);
								unlink($myFile);

							}
						}
						catch (Exception $e) {
							throw new Zend_Exception('Error: can not open file');
						}
					}
				}
				//note: delete of workshop_info is executed from db
				$workshop->delete($workshop->getAdapter()->quoteInto(Workshop::COL_ID .' = ?', $workId));
			}
		}

		$redirect = new Zend_Controller_Action_Helper_Redirector();
		$redirect->setGoto('myws', 'search', 'workshop');
	}
		
	public function redirectTo($action = 'list',$controller = 'search',$modul = 'workshop')
	{
		$Redirect = new Zend_Controller_Action_Helper_Redirector();
		$Redirect->setGoto($action,$controller,$modul);
	}

}
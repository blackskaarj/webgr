<?php

class Ce_EditController extends Zend_Controller_Action {

	private $form;
	private $formAttrSelCon;
	private $formAttrSelConFish;
	private $formAttrSelConImage;
	private $formImageSearch;
	private $callingCeId;
	private $defaultNamespace;
	private $imageSetForm;
	private $readOnly = false;

	public function init()
	{
		$ceId = intval($this->getRequest()->getParam(CalibrationExercise::COL_ID));
		$workId = Default_SimpleQuery::getWorkshopId($ceId);
		//		if (! Default_SimpleQuery::getWsManagerUserId($workId) == AuthQuery::getUserId()) {
		//			$Redirect = new Zend_Controller_Action_Helper_Redirector();
		//			$Redirect->setGotoSimple('myce', 'search', 'ce');
		//			return;
		//		}

		$this->defaultNamespace = new Zend_Session_Namespace('default');
		if (isset($this->defaultNamespace->message)) {
			$this->view->message = $this->defaultNamespace->message;
		}
		if ($this->getRequest()->getParam(CalibrationExercise::COL_ID) != null){
			$this->callingCeId = $this->getRequest()->getParam(CalibrationExercise::COL_ID);
		}else{
			$this->callingCeId = $this->defaultNamespace->callingActionId;
		}
		$this->imageSetForm = new Ble422_Form_Dynamic();
		$this->imageSetForm->addElement('hidden',ImagesetAttributes::COL_CE_ID,array('required'=>true));
		$this->imageSetForm->setAction('/ce/edit/imagesetform');

		//-----------------------------------------------
		//check if user is authorized for editing CE
		//		$auth = Zend_Auth::getInstance();
		//		$storage = $auth->getStorage()->read();
		//		$const = User::COL_ID;
		//		$userId = $storage->$const;
		//		$ceTable = new CalibrationExercise();
		//		$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
		//		$select = $dbAdapter->select();
		//		$select->from(	array('ce'=>$ceTable->getTableName()),
		//						array(	'ce.'.CalibrationExercise::COL_ID,
		//								'ce.'.CalibrationExercise::COL_WORKSHOP_ID)
		//						);
		//		$partTable = new Participant();
		//		$select->join(array('part'=>$partTable->getTableName()),
		//							'ce.'.CalibrationExercise::COL_ID.' = '.'part.'.Participant::COL_CE_ID,
		//							Participant::COL_USER_ID
		//							);
		//		$userTable = new User();
		//		$select->join(array('user'=>$userTable->getTableName()),
		//							'part.'.Participant::COL_USER_ID.' = '.'user.'.User::COL_ID,
		//							'user.'.User::COL_ROLE
		//							);
		//		$select->where(	$dbAdapter->quoteInto('ce.'.CalibrationExercise::COL_ID.' = ?', $this->callingCeId));
		//		$select->where(	'user.'.User::COL_ID.' = ?', $userId);
		//		$select->where(	$dbAdapter->quoteInto('user.'.User::COL_ROLE.' = ? OR ', 'workshop manager').
		//						$dbAdapter->quoteInto('user.'.User::COL_ROLE.' = ?', 'admin'));
		//		$stmt = $select->query();
		//		//echo $select;
		//		$resultArray = $stmt->fetchAll();
		//		if (count($resultArray) != 1) {
		//			$redirect = new Zend_Controller_Action_Helper_Redirector();
		//			$redirect->setGotoSimple('myce', 'search', 'ce');
		//		}
		//------------------------------------------------

		$this->view->ceName = Default_SimpleQuery::getCeName($this->callingCeId);
		$this->view->callingActionId = $this->callingCeId;
		$this->form = new Ce_Form_EditAllElements();

		$formElem = $this->form->getElement(CalibrationExercise::COL_KEY_TABLE_ID);
		if (! Default_SimpleQuery::isCeStopped($this->callingCeId)) {
			$this->form->removeElement('save');
		}
		
		//credits: http://stackoverflow.com/questions/643736/zend-form-add-a-link-to-the-right-of-a-text-field
		//answered Mar 14 at 13:05 monzee
		$formElem->setDescription('<a href="/ce/editkeytable/index">Add protocol...</a>')
		->setDecorators(array(
        'ViewHelper',
		array('Description', array('escape' => false, 'tag' => false)),
		array('HtmlTag', array('tag' => 'dd')),
		array('Label', array('tag' => 'dt')),
        'Errors',
		));
		$formElem = $this->form->getElement(CalibrationExercise::COL_EXPERTISE_ID);
		$formElem->setDescription('<a href="/ce/editexpertise/index">Add expertise...</a>')
		->setDecorators(array(
        'ViewHelper',
		array('Description', array('escape' => false, 'tag' => false)),
		array('HtmlTag', array('tag' => 'dd')),
		array('Label', array('tag' => 'dt')),
        'Errors',
		));

		$ceHasAttrTable = new CeHasAttributeDescriptor();
		$tableRow = CeHasAttributeDescriptor::COL_CAEX_ID;
		$this->formAttrSelCon = new Default_Form_AttributeSelectContainer($ceHasAttrTable, $tableRow, $this->callingCeId);
		$this->formAttrSelConImage = new Default_Form_AttributeSelectContainer(NULL, NULL, NULL, 'IMAGE');
		$this->formAttrSelConFish = new Default_Form_AttributeSelectContainer(NULL, NULL, NULL, 'FISH');
		//$this->view->formAttrSelConFish = $this->formAttrSelConFish;
	}

	public function indexAction()
	{
		if ($this->callingCeId == NULL){
                        $this->_forward("index","index");
		}
		$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
		$namespace = new Zend_Session_Namespace('ce');
		if ($this->getRequest()->isPost()){
			if ($this->form->isValid($this->getRequest()->getParams())){
				if ($this->form->getValue('Token') == $namespace->Token){
					//insert/update
					$ceTable = new CalibrationExercise();
					$data = array(CalibrationExercise::COL_NAME=>$this->form->getValue(CalibrationExercise::COL_NAME),
					CalibrationExercise::COL_DESCRIPTION=>$this->form->getValue(CalibrationExercise::COL_DESCRIPTION),
					CalibrationExercise::COL_COMPAREABLE=>$this->form->getValue(CalibrationExercise::COL_COMPAREABLE),
					CalibrationExercise::COL_RANDOMIZED=>$this->form->getValue(CalibrationExercise::COL_RANDOMIZED),
					CalibrationExercise::COL_WORKSHOP_ID=>$this->form->getValue(CalibrationExercise::COL_WORKSHOP_ID),
					CalibrationExercise::COL_KEY_TABLE_ID=>$this->form->getValue(CalibrationExercise::COL_KEY_TABLE_ID),
					CalibrationExercise::COL_EXPERTISE_ID=>$this->form->getValue(CalibrationExercise::COL_EXPERTISE_ID));
					$where = $ceTable->getAdapter()->quoteInto(CalibrationExercise::COL_ID.' = ?', $this->form->getValue(CalibrationExercise::COL_ID));
					$ceTable->update($data, $where);
					$newToken = Ble422_Guid::getGuid();
					$this->form->getElement('Token')->setValue($newToken);
					$namespace->Token = $newToken;
				}else{
					//form token is not equal session token
					$this->form->reset();
					$this->redirectTo('outofdate');
				}
			}else{
				//not valid

			}
		}else{
			//not post
			$this->form->setAction(Zend_Controller_Front::getInstance()->getBaseUrl().'/ce/edit/index');
			if ($this->form->getValue('Token') == null)	{
				$guid = new Ble422_Guid();
				$namespace->Token = $guid->__toString();
				$this->form->getElement('Token')->setValue($guid->__toString());
			}
			/**
			 * get all set values for calibration exercise
			 */
			//use Left Join to get the CE without assigned KeyTable/Expertise, too
			//use left join for workshop to get training ce's too
			$select = $dbAdapter->select();
			$select->from(array('ce'=>CalibrationExercise::TABLE_NAME));
			$select->joinLeft(array('ws'=>Workshop::TABLE_NAME),
			$dbAdapter->quoteIdentifier('ce.' . CalibrationExercise::COL_WORKSHOP_ID) . '=' . $dbAdapter->quoteIdentifier('ws.' . Workshop::COL_ID));
			$select->joinLeft(array('kt'=>KeyTable::TABLE_NAME),
			$dbAdapter->quoteIdentifier('ce.' . CalibrationExercise::COL_KEY_TABLE_ID) . '=' . $dbAdapter->quoteIdentifier('kt.' . KeyTable::COL_ID));
			$select->joinLeft(array('exp'=>Expertise::TABLE_NAME),
			$dbAdapter->quoteIdentifier('ce.' . CalibrationExercise::COL_EXPERTISE_ID) . '=' . $dbAdapter->quoteIdentifier('exp.' . Expertise::COL_ID));
			$select->where($dbAdapter->quoteIdentifier('ce.' . CalibrationExercise::COL_ID).' = ?', $this->callingCeId);

			$resultArray = $dbAdapter->fetchAll($select);
			//fill form with values
			$this->form->setValues($resultArray[0]);
		}
		$this->view->isStopped = $this->form->getValue(CalibrationExercise::COL_IS_STOPPED);

		if (isset($this->defaultNamespace->returningAction) && isset($this->defaultNamespace->returningActionId))
		{
			switch ($this->defaultNamespace->returningAction)
			{
				case 'ce/editkeytable/index':
					$this->form->getElement(CalibrationExercise::COL_KEY_TABLE_ID)->setValue($this->defaultNamespace->returningActionId);
					break;
				case 'ce/editexpertise/index':
					$this->form->getElement(CalibrationExercise::COL_EXPERTISE_ID)->setValue($this->defaultNamespace->returningActionId);
					break;
				default:
					$this->defaultNamespace->returningAction = NULL;
					$this->defaultNamespace->returningActionId = NULL;
					throw new Zend_Exception;
					break;
			}
		}
		$this->defaultNamespace->returningAction = NULL;
		$this->defaultNamespace->returningActionId = NULL;

		/**
		 * get Shown Attributes List
		 */
		$selectShownAttr = $dbAdapter->select();
		$selectShownAttr->from(CeHasAttributeDescriptor::TABLE_NAME);
		$selectShownAttr->join(AttributeDescriptor::TABLE_NAME, CeHasAttributeDescriptor::TABLE_NAME.'.ATDE_ID = '.AttributeDescriptor::TABLE_NAME.'.ATDE_ID', array(AttributeDescriptor::COL_NAME));
		$selectShownAttr->where(CeHasAttributeDescriptor::COL_CAEX_ID." = ?", $this->callingCeId);

		$resultShownAttr = $dbAdapter->fetchAll($selectShownAttr);
		$this->view->resultShownAttr = $resultShownAttr;

		//remove already used attributes from selectbox
		$elem = $this->formAttrSelCon->getElement('attr');
		foreach ($resultShownAttr as $row) {
			$elem->removeMultiOption($row[AttributeDescriptor::COL_ID]);
		}
		unset($elem);

		//info/actions about participants
		$this->view->numOfParti = $this->countParticipants();

		$this->defaultNamespace->callingAction = 'ce/edit/index';
		$this->defaultNamespace->callingActionId = $this->callingCeId;
		$this->view->form = $this->form;
		$this->formAttrSelCon->setAction(Zend_Controller_Front::getInstance()->getBaseUrl().'/ce/edit/addattribute');
		$this->view->formAttrSelCon = $this->formAttrSelCon;

		/**
		 * imageset attributes fish and image
		 */
		//---get Attributes List
		$selectAttr = $dbAdapter->select();
		$selectAttr->from(ImagesetAttributes::TABLE_NAME);
		$selectAttr->join(	AttributeDescriptor::TABLE_NAME,
		ImagesetAttributes::TABLE_NAME.'.ATDE_ID = '.AttributeDescriptor::TABLE_NAME.'.ATDE_ID');
		$selectAttr->joinLeft(  ValueList::TABLE_NAME,
		AttributeDescriptor::TABLE_NAME . '.' . AttributeDescriptor::COL_UNIT . '='. ValueList::TABLE_NAME . '.' . ValueList::COL_ID,
		array('UNIT'=>ValueList::COL_VALUE));
		$selectAttr->where(ImagesetAttributes::COL_CE_ID." = ?", $this->callingCeId);
		//show only FISH-group & IMAGE-group attributes
		$selectAttr->where('('.AttributeDescriptor::COL_GROUP." = ?", 'FISH');
		$selectAttr->orWhere(AttributeDescriptor::COL_GROUP." = ?)", 'IMAGE');
		$resultAttr = $dbAdapter->fetchAll($selectAttr);

		//remove already used attributes from selectbox
		$elem = $this->formAttrSelConFish->getElement('attr');
		foreach ($resultAttr as $row) {
			$elem->removeMultiOption($row[AttributeDescriptor::COL_ID]);
		}
		unset($elem);

		//remove already used attributes from selectbox
		$elem = $this->formAttrSelConImage->getElement('attr');
		foreach ($resultAttr as $row) {
			$elem->removeMultiOption($row[AttributeDescriptor::COL_ID]);
		}
		unset($elem);


		//set add form
		$this->formAttrSelConFish->setAction(Zend_Controller_Front::getInstance()->getBaseUrl().'/ce/edit/addimagesetattribute');
		$this->view->formAttrSelConFish = $this->formAttrSelConFish;

		$this->imageSetForm->addDynamicElements($resultAttr, true, true);
		
		if (Default_SimpleQuery::isCeStopped($this->callingCeId)) {
		  $this->imageSetForm->addElement('submit','submit',array('label'=>'save'));
		} else {
			//set description to remove the "remove attribute"-link from form
			foreach ($this->imageSetForm->getElements() as $elem) {
				$elem->setDescription(NULL);
			}
		}
		
		//set dynamic values
		$this->imageSetForm->dynPopulate($resultAttr,ImagesetAttributes::COL_VALUE,array(CalibrationExercise::COL_ID => $this->callingCeId));
		if(count($resultAttr)==0){
			$this->view->noImageset = true;
		}else{
			$this->view->noImageset = false;
		}

		/**
		 * get already defined imagelist
		 */
		$selectImages = $dbAdapter->select();
		$selectImages->from(array('cehim'=>CeHasImage::TABLE_NAME));
		$selectImages->join(array('im'=>Image::TABLE_NAME),
            'cehim.'.CeHasImage::COL_IMAGE_ID.'='.'im.'.Image::COL_ID);
		$selectImages->where(CalibrationExercise::COL_ID . '=?',$this->callingCeId);
		$this->view->imageArray = $dbAdapter->fetchAll($selectImages);
		$this->view->ceId = $this->callingCeId;

		$this->view->imageSetForm = $this->imageSetForm;
		//set add form
		$this->formAttrSelConImage->setAction(Zend_Controller_Front::getInstance()->getBaseUrl().'/ce/edit/addimagesetattribute');
		$this->view->formAttrSelConImage = $this->formAttrSelConImage;

		if ($this->form->getElement(CalibrationExercise::COL_RANDOMIZED)->getValue() == 1) {
			$this->view->isRandom = TRUE;
		}
		$this->defaultNamespace->message = NULL;
	}

	public function deleteAction() {
		//check if stopped then
		//check if annotations exist then
		//delete ce
		//delete imageset attributes -> DB on delete cascade
		//delete ce has image -> DB on delete cascade
		//delete ce has attribute desc. -> DB on delete cascade
		//delete participants -> DB on delete cascade
		//delete annotations -> DB on delete cascade
		//delete dots -> DB on delete cascade

		$ceId = $this->callingCeId;
		$ce = new CalibrationExercise();

		if ($this->userRole == 'admin'
		|| AuthQuery::getUserId() == Default_SimpleQuery::getWsManagerUserId(Default_SimpleQuery::getWorkshopId($ceId))) {
			//if any image has an annotation, don't delete
			if (Default_SimpleQuery::isCeStopped($ceId)
			&& ! Default_ReferenceQuery::ceHasAnnotation($ceId)) {

				$rowset = $ce->find($ceId);
				if (count($rowset) == 1) {
					$ce->delete($ce->getAdapter()->quoteInto(CalibrationExercise::COL_ID .' = ?', $ceId));
				}
			}
		}

		$Redirect = new Zend_Controller_Action_Helper_Redirector();
		if ($this->defaultNamespace->callingAction == 'ce/search/myce') {
			$Redirect->setGotoSimple('myce', 'search', 'ce');
		}
		elseif ($this->defaultNamespace->callingAction == 'ce/search/list') {
			$Redirect->setGotoSimple('list', 'search', 'ce');
		}
	}

	public function deleterecursiveAction() {
		//delete ce
		//delete imageset attributes -> DB on delete cascade
		//delete ce has image -> DB on delete cascade
		//delete ce has attribute desc. -> DB on delete cascade
		//delete participants -> DB on delete cascade
		//delete annotations -> DB on delete cascade
		//delete dots -> DB on delete cascade

		//auskommentiert am 14.10.2009 weil RAW DELETE in der Calibration exercise list
		//nicht immer funktionierte (wenn WS name = training exercise war)
		//stattdessen soll die cdId aus dem Request geholt werden
		//$ceId = $this->callingCeId;
		$ceId = $this->getRequest()->getParam(CalibrationExercise::COL_ID);
		$ce = new CalibrationExercise();
		if ($this->userRole == 'admin'
		|| AuthQuery::getUserId() == Default_SimpleQuery::getWsManagerUserId(Default_SimpleQuery::getWorkshopId($ceId))) {
			$rowset = $ce->find($ceId);
			if (count($rowset) == 1) {
				$ce->delete($ce->getAdapter()->quoteInto(CalibrationExercise::COL_ID .' = ?', $ceId));
			}
		}
		$Redirect = new Zend_Controller_Action_Helper_Redirector();
		if ($this->defaultNamespace->callingAction == 'ce/search/myce') {
			$Redirect->setGotoSimple('myce', 'search', 'ce');
		}
		elseif ($this->defaultNamespace->callingAction == 'ce/search/list') {
			$Redirect->setGotoSimple('list', 'search', 'ce');
		}
	}

	public function mydeleterecursiveAction() {
		//at the moment only used for training CEs

		//delete ce
		//delete imageset attributes -> DB on delete cascade
		//delete ce has image -> DB on delete cascade
		//delete ce has attribute desc. -> DB on delete cascade
		//delete participants -> DB on delete cascade
		//delete annotations -> DB on delete cascade
		//delete dots -> DB on delete cascade

		$ceId = $this->getRequest()->getParam(CalibrationExercise::COL_ID);
		$ce = new CalibrationExercise();
		$rowset = $ce->find($ceId);
		if (count($rowset) == 1) {
			$qu = new Default_ReferenceQuery();
			if ($qu->isParticipantInTrainingCe($ceId)) {
				$ce->delete($ce->getAdapter()->quoteInto(CalibrationExercise::COL_ID .' = ?', $ceId));
			}
		}

		$Redirect = new Zend_Controller_Action_Helper_Redirector();
		if ($this->defaultNamespace->callingAction == 'ce/search/myce') {
			$Redirect->setGotoSimple('myce', 'search', 'ce');
		}
	}

	public function addattributeAction()
	//assigns existing attribute to cal.ex., doesn't create attribute
	{
		if ($this->getRequest()->isPost())
		{
			if ($this->formAttrSelCon->isValid($this->getRequest()->getParams()))
			{
				//insert/update

				$ceId = $this->defaultNamespace->callingActionId;
				$ceHasAttr = new CeHasAttributeDescriptor();
				$data = array (CeHasAttributeDescriptor::COL_ATDE_ID => $this->formAttrSelCon->getValue('attr'),
				CeHasAttributeDescriptor::COL_CAEX_ID => $ceId);
				$ceHasAttr->insert($data);
				//$this->render('index');
				$this->_forward("index","edit","ce",array("CAEX_ID" => $this->defaultNamespace->callingActionId));
			}
		}
	}

	public function addimagesetattributeAction()
	//assigns existing attribute to imageset, doesn't create attribute
	{
		if ($this->getRequest()->isPost())
		{
			if ($this->formAttrSelConFish->isValid($this->getRequest()->getParams()) ||
			$this->formAttrSelConImage->isValid($this->getRequest()->getParams()))
			{
				//insert/update
				$ceId = $this->defaultNamespace->callingActionId;
				$imagesetAttr = new ImagesetAttributes();
				$data = array (ImagesetAttributes::COL_ATTRIBUTE_DESCRIPTOR_ID => $this->getRequest()->getParam('attr'),
				ImagesetAttributes::COL_CE_ID => $ceId);
				$imagesetAttr->insert($data);
                                $this->_forward("index","edit","ce",array("CAEX_ID" => $this->defaultNamespace->callingActionId));
			}
		}
	}

	private function countParticipants()
	{
		$parti = new Participant();
		$where = $parti->getAdapter()->quoteInto(Participant::COL_CE_ID.' = ?', $this->callingCeId);
		$rowSet = $parti->fetchAll($where);
		return count($rowSet);
	}

	/**
	 * removes attribute from cal.ex., doesn't delete attribute
	 * This is no form action like addattributeAction because it's triggered through link.
	 * @return unknown_type
	 */
	public function removeattributeAction()
	{
		$ceHasAttr = new CeHasAttributeDescriptor();
		//no single primary key
		$where = array(	$ceHasAttr->getAdapter()->quoteInto(CeHasAttributeDescriptor::COL_ATDE_ID.' = ?', $this->getRequest()->getParam(AttributeDescriptor::COL_ID)),
		$ceHasAttr->getAdapter()->quoteInto(CeHasAttributeDescriptor::COL_CAEX_ID.' = ?', $this->defaultNamespace->callingActionId));
		$ceHasAttr->delete($where);
                $this->_forward("index","edit","ce",array("CAEX_ID" => $this->defaultNamespace->callingActionId));
	}

	/**
	 * removes attribute from imageset, doesn't delete attribute
	 * This is no form action like addattributeAction because it's triggered through link.
	 * @return unknown_type
	 */
	public function removeimagesetattributeAction()
	{
		$imagesetAttr = new ImagesetAttributes();
		$where = array(	$imagesetAttr->getAdapter()->quoteInto(ImagesetAttributes::COL_ATTRIBUTE_DESCRIPTOR_ID.' = ?', $this->getRequest()->getParam(AttributeDescriptor::COL_ID)),
		$imagesetAttr->getAdapter()->quoteInto(ImagesetAttributes::COL_CE_ID.' = ?', $this->defaultNamespace->callingActionId));
		$imagesetAttr->delete($where);
		$this->_forward("index","edit","ce",array("CAEX_ID" => $this->defaultNamespace->callingActionId));
	}

	public function addimagesAction()
	{
		$imageNamespace = new Zend_Session_Namespace('image_search');

		if($this->getRequest()->isPost()){
			$params = $this->getRequest()->getParams();
			$ceHimTable = new CeHasImage();
			$imageIds = $params[Image::COL_ID];

			//delete duplicate imageIds from multiple checked boxes
			$uniqueImageIds = array_unique($imageIds);

			foreach($uniqueImageIds as $imageId){
				$data = array( CeHasImage::COL_CALIBRATION_EXERCISE_ID => $this->callingCeId,
				CeHasImage::COL_IMAGE_ID => $imageId);
				$ceHimTable->insert($data);
			}
			$this->redirectTo('index');
		}else{

			$this->defaultNamespace->callingAction = '/ce/edit/addimages';
			$this->defaultNamespace->callingActionId = $this->callingCeId;


			$imageNamespace->formValues = $this->getAttriFormValues();

			$Redirect = new Zend_Controller_Action_Helper_Redirector();
			$Redirect->setGotoSimple('search','search','image');
		}
	}

	public function addimagesatrandomAction()
	{
		//$imageNamespace = new Zend_Session_Namespace('image_search');

		$this->defaultNamespace->callingAction = '/ce/edit/addimagesatrandom';
		$this->defaultNamespace->callingActionId = $this->callingCeId;

		$attribFormValues = $this->getAttriFormValues();

		//--------------------------------------------------------------------------------
		//from search/index
		$this->formImageSearch = new Image_Form_Search();
		$this->formImageSearch->removeElement(Image::COL_ID);
		$this->formImageSearch->removeElement('save');
		//$this->formImageSearch->removeElement('kind');

		//$this->formImageSearch->addElement('submit', 'submit', array('label'=>'Search'        ));

		//set all elements to required=FALSE
		//clear all validators
		$formElements = $this->formImageSearch->getElements();
		foreach ($formElements as $elem)
		{
			$elem->setRequired(false);
			$elem->clearValidators();
		}
		//$this->formImageSearch->setElements($formElements);

		//----------------------------------------------------

		if ($this->formImageSearch->isValid($attribFormValues)) {

			$metaData = new Default_MetaData();
			$metaData->getSelectForGroups(TRUE);
			$select = $metaData->addWhereToSelect($attribFormValues);

			$resultRowset = Zend_Registry::get('DB_CONNECTION1')->fetchAll($select);

			//TODO resultRowset is empty

			$imageIds = array();
			foreach ($resultRowset as $rowNo => $row) {
				$imageIds[] = $row[Image::COL_ID];
			}

			//delete duplicate imageIds from multiple set values
			$uniqueImageIds = array_unique($imageIds);

			//----------------------------------------------------
			//
			//substract already assigned datasets
			$ceId = $this->defaultNamespace->callingActionId;
			$ceHasIm = new CeHasImage();
			$ceHasImSet = $ceHasIm->fetchAll(CeHasImage::COL_CALIBRATION_EXERCISE_ID.'='.$ceId);

			if (count($ceHasImSet) > 0) {
				$possibleImageIds = array();
				foreach ($uniqueImageIds as $key => $id) {
					$alreadyAssigned = false;
					foreach($ceHasImSet as $ceHasImRow){
						if($id == $ceHasImRow[CeHasImage::COL_IMAGE_ID]){
							$alreadyAssigned = true;
						}
					}
					if (!$alreadyAssigned){
						array_push($possibleImageIds,$id);
					}
				}
			} else {
				//no images assigned
				$possibleImageIds = $uniqueImageIds;
			}
			//----------------------------------------------------

			$randomImageIds = Ble422_ArrayHelper::array_pick($possibleImageIds, $this->getRequest()->getParam("noImages"));

			//insert new images into ce
			$ceHimTable = new CeHasImage();
			foreach ($randomImageIds as $imageId) {
				$data = array(  CeHasImage::COL_CALIBRATION_EXERCISE_ID => $this->callingCeId,
				CeHasImage::COL_IMAGE_ID => $imageId);
				$ceHimTable->insert($data);
			}
		}
		$this->redirectTo('index');
	}

	public function removeimageAction()
	{
		//action is only clickable in view when there are no annotations
		//delete annotations -> done by DBMS
		//delete dots -> done by DBMS
		if (! Default_SimpleQuery::isValueInTableColumn($this->getRequest()->getParam(CeHasImage::COL_ID), new Annotations(), Annotations::COL_CE_HAS_IMAGE_ID)) {
			$cehimTable = new CeHasImage();
			$cehimTable->delete($cehimTable->getAdapter()->quoteInto(CeHasImage::COL_ID .'=?',$this->getRequest()->getParam(CeHasImage::COL_ID)));
		}
		$this->redirectTo('index');
	}

	public function insertedAction()
	{
	}

	public function outofdateAction()
	{
	}

	public function imagesetformAction()
	{
		$imageSetTable = new ImagesetAttributes();
		$dbAdapter = Zend_Registry::get('DB_CONNECTION1');

		//imageset attributes fish
		//---get Attributes List
		$selectAttr = $dbAdapter->select();
		$selectAttr->from(ImagesetAttributes::TABLE_NAME);
		$selectAttr->join(  AttributeDescriptor::TABLE_NAME,
		ImagesetAttributes::TABLE_NAME.'.ATDE_ID = '.AttributeDescriptor::TABLE_NAME.'.ATDE_ID');
		$selectAttr->where(ImagesetAttributes::COL_CE_ID." = ?", $this->callingCeId);
		//show only FISH-group & IMAGE-group attributes
		$selectAttr->where('('.AttributeDescriptor::COL_GROUP." = ?", 'FISH');
		$selectAttr->orWhere(AttributeDescriptor::COL_GROUP." = ?)", 'IMAGE');
		$selectAttr->group(AttributeDescriptor::TABLE_NAME . '.' . AttributeDescriptor::COL_ID);
		$resultAttr = $dbAdapter->fetchAll($selectAttr);
		$this->imageSetForm->addDynamicElements($resultAttr, true, true);

		// delete old data
		$imageSetTable->delete($dbAdapter->quoteInto(ImagesetAttributes::COL_CE_ID. '=?',$this->callingCeId));

		if (  $this->imageSetForm->isValid($this->getRequest()->getParams())){

			$ceId = $this->imageSetForm->getValue(ImagesetAttributes::COL_CE_ID);
			$dynElementArray = $this->imageSetForm->getDynamicElements();
			foreach ($dynElementArray as $elementName){
				$metaValue = $this->imageSetForm->getValue($elementName);
				if($metaValue != null){
					$attribId = substr($elementName,5,strlen($elementName));
					if(is_array($metaValue)){
						foreach ($metaValue as $key => $meta){
							if($key === 'toValue'){
								$data = array(  ImagesetAttributes::COL_ATTRIBUTE_DESCRIPTOR_ID => $attribId,
								ImagesetAttributes::COL_CE_ID => $ceId,
								ImagesetAttributes::COL_TO => $meta);
							}else if ($key === 'fromValue'){
								$data = array(  ImagesetAttributes::COL_ATTRIBUTE_DESCRIPTOR_ID => $attribId,
								ImagesetAttributes::COL_CE_ID => $ceId,
								ImagesetAttributes::COL_FROM => $meta);
							}else{
								$data = array(  ImagesetAttributes::COL_ATTRIBUTE_DESCRIPTOR_ID => $attribId,
								ImagesetAttributes::COL_CE_ID => $ceId,
								ImagesetAttributes::COL_VALUE => $meta);
							}
							$imageSetTable->insert($data);
						}
					}else{
						$data = array(  ImagesetAttributes::COL_ATTRIBUTE_DESCRIPTOR_ID => $attribId,
						ImagesetAttributes::COL_CE_ID => $ceId,
						ImagesetAttributes::COL_VALUE => $metaValue);
						$imageSetTable->insert($data);
					}
				}
			}
			$this->redirectTo('index',array(CalibrationExercise::COL_ID => $ceId));
		}else if($this->imageSetFormImage->isValid($this->getRequest()->getParams())){
			$ceId = $this->imageSetFormImage->getValue(ImagesetAttributes::COL_CE_ID);

		}else{
			$this->redirectTo('index');
		}
	}

	public function replicateAction()
	{
		$ceTable = new CalibrationExercise();
		$partTable = new Participant();
		$cehimTable = new CeHasImage();
		$imagesetTable = new ImagesetAttributes();
		$shownAtTable = new CeHasAttributeDescriptor();

		/**
		 * calibration exercises
		 */
		$ceArray = $ceTable->find($this->callingCeId)->current()->toArray();

		/**
		 * participants
		 */
		$partSelect = $partTable->getAdapter()->select();
		$partSelect->from(Participant::TABLE_NAME);
		$partSelect->where(Participant::COL_CE_ID . '=?',$this->callingCeId);
		$partArray = $partTable->getAdapter()->fetchAll($partSelect);

		/**
		 * imageset
		 */
		$cehimSelect = $cehimTable->getAdapter()->select();
		$cehimSelect->from(CeHasImage::TABLE_NAME);
		$cehimSelect->where(CeHasImage::COL_CALIBRATION_EXERCISE_ID . '=?',$this->callingCeId);
		$cehimArray = $cehimTable->getAdapter()->fetchAll($cehimSelect);

		/**
		 * imageset definition
		 */
		$imagesetSelect = $imagesetTable->getAdapter()->select();
		$imagesetSelect->from(ImagesetAttributes::TABLE_NAME);
		$imagesetSelect->where(ImagesetAttributes::COL_CE_ID . '=?',$this->callingCeId);
		$imagesetArray = $imagesetTable->getAdapter()->fetchAll($imagesetSelect);

		/**
		 * shown attributes
		 */
		$shownAtSelect = $shownAtTable->getAdapter()->select();
		$shownAtSelect->from(CeHasAttributeDescriptor::TABLE_NAME);
		$shownAtSelect->where(CeHasAttributeDescriptor::COL_CAEX_ID . '=?',$this->callingCeId);
		$shownAtArray = $shownAtTable->getAdapter()->fetchAll($shownAtSelect);

		/**
		 * insert all data
		 */
		$ceData = array(  CalibrationExercise::COL_COMPAREABLE => $ceArray[CalibrationExercise::COL_COMPAREABLE],
		CalibrationExercise::COL_DESCRIPTION => $ceArray[CalibrationExercise::COL_DESCRIPTION],
		CalibrationExercise::COL_EXPERTISE_ID => $ceArray[CalibrationExercise::COL_EXPERTISE_ID],
		CalibrationExercise::COL_KEY_TABLE_ID => $ceArray[CalibrationExercise::COL_KEY_TABLE_ID],
		CalibrationExercise::COL_NAME => $ceArray[CalibrationExercise::COL_NAME],
		CalibrationExercise::COL_RANDOMIZED => $ceArray[CalibrationExercise::COL_RANDOMIZED],
		CalibrationExercise::COL_WORKSHOP_ID => $ceArray[CalibrationExercise::COL_WORKSHOP_ID]);
		$newCeId = $ceTable->insert($ceData);

		foreach($partArray as $part){
			$partData = array(Participant::COL_CE_ID => $newCeId,
			Participant::COL_EXPERTISE_LEVEL => $part[Participant::COL_EXPERTISE_LEVEL],
			Participant::COL_NUMBER => $part[Participant::COL_NUMBER],
			Participant::COL_ROLE=> $part[Participant::COL_ROLE],
			Participant::COL_STOCK_ASSESSMENT => $part[Participant::COL_STOCK_ASSESSMENT],
			Participant::COL_USER_ID => $part[Participant::COL_USER_ID]);
			$partTable->insert($partData);
		}

		foreach($cehimArray as $cehim){
			$cehimData = array(  CeHasImage::COL_CALIBRATION_EXERCISE_ID => $newCeId,
			CeHasImage::COL_IMAGE_ID => $cehim[CeHasImage::COL_IMAGE_ID]);
			$cehimTable->insert($cehimData);
		}

		foreach($imagesetArray as $imageSet){
			$imageSetData = array(  ImagesetAttributes::COL_ATTRIBUTE_DESCRIPTOR_ID => $imageSet[ImagesetAttributes::COL_ATTRIBUTE_DESCRIPTOR_ID],
			ImagesetAttributes::COL_CE_ID => $newCeId,
			ImagesetAttributes::COL_FROM => $imageSet[ImagesetAttributes::COL_FROM],
			ImagesetAttributes::COL_TO => $imageSet[ImagesetAttributes::COL_TO],
			ImagesetAttributes::COL_VALUE => $imageSet[ImagesetAttributes::COL_VALUE]);
			$imagesetTable->insert($imageSetData);
		}

		foreach($shownAtArray as $shownAt){
			$shownAtData = array(   CeHasAttributeDescriptor::COL_ATDE_ID => $shownAt[CeHasAttributeDescriptor::COL_ATDE_ID],
			CeHasAttributeDescriptor::COL_CAEX_ID => $newCeId);
			$imagesetTable->insert($imageSetData);
		}

		$this->redirectTo('index',array(CalibrationExercise::COL_ID => $newCeId));
	}

	public function startceAction()
	{
		if (Default_SimpleQuery::isCeCompletelyDefined($this->callingCeId)) {
			$ceTable = new CalibrationExercise();
			$data = array(CalibrationExercise::COL_IS_STOPPED => 0);
			$ceTable->update($data,$ceTable->getAdapter()->quoteInto(CalibrationExercise::COL_ID.'=?',$this->callingCeId));
			$this->redirectTo('index',array(CalibrationExercise::COL_ID => $this->callingCeId));
			$this->defaultNamespace->message = NULL;
		} else {
			$this->defaultNamespace->message = '<red>Error: Calibration exercise not completely defined.</red>';
			$this->redirectTo('index',array(CalibrationExercise::COL_ID => $this->callingCeId));
		}
	}

	public function stopceAction()
	{
		$ceTable = new CalibrationExercise();
		$data = array(CalibrationExercise::COL_IS_STOPPED => 1);
		$ceTable->update($data,$ceTable->getAdapter()->quoteInto(CalibrationExercise::COL_ID.'=?',$this->callingCeId));
		$this->redirectTo('index',array(CalibrationExercise::COL_ID => $this->callingCeId));
	}

	public function redirectTo($action,$params = array())
	{
		$Redirect = new Zend_Controller_Action_Helper_Redirector();
		$Redirect->setGotoSimple($action,'Edit','ce',$params);
	}

	private function getAttriFormValues(){

		$imageSetTable = new ImagesetAttributes();

		$select = $imageSetTable->getAdapter()->select();
		$select->from(  array('imSet'=>ImagesetAttributes::TABLE_NAME));
		$select->where( ImagesetAttributes::COL_CE_ID.' =?',$this->callingCeId);
		$select->join ( array('attr'=>AttributeDescriptor::TABLE_NAME),
                            'imSet.'.ImagesetAttributes::COL_ATTRIBUTE_DESCRIPTOR_ID .'= attr.'.AttributeDescriptor::COL_ID);
		$resultAttr = $imageSetTable->getAdapter()->fetchAll($select);

		$valueColumn = ImagesetAttributes::COL_VALUE;

		$attribFormValues = array();

		//read table and write name/value-pairs like input in search form
		foreach ($resultAttr as $attribValue){
			//			if($attribValue[AttributeDescriptor::COL_MULTIPLE] == '0'){
			//                if(array_key_exists(ImagesetAttributes::COL_VALUE_LIST_ID,$attribValue) && $attribValue[AttributeDescriptor::COL_VALUE_LIST] == '1'){
			//                    $attribFormValues += array('ATDE_' . $attribValue[AttributeDescriptor::COL_ID] => $attribValue[ImagesetAttributes::COL_VALUE_LIST_ID]);

			if ($attribValue[AttributeDescriptor::COL_FORM_TYPE] == 'text') {
				if ($attribValue[AttributeDescriptor::COL_DATA_TYPE] == 'integer' ||
				$attribValue[AttributeDescriptor::COL_DATA_TYPE] == 'decimal' ||
				$attribValue[AttributeDescriptor::COL_DATA_TYPE] == 'date' ||
				$attribValue[AttributeDescriptor::COL_DATA_TYPE] == 'time' ||
				$attribValue[AttributeDescriptor::COL_DATA_TYPE] == 'datetime') {
					//in case of numbers/dates/times read FROM and TO field
					if(array_key_exists(ImagesetAttributes::COL_FROM,$attribValue) && $attribValue[ImagesetAttributes::COL_FROM] != ''){
						if(array_key_exists('ATDE_' . $attribValue[AttributeDescriptor::COL_ID],$attribFormValues) && is_array($attribFormValues['ATDE_' . $attribValue[AttributeDescriptor::COL_ID]])){
							$attribFormValues['ATDE_' . $attribValue[AttributeDescriptor::COL_ID]] += array('fromValue' => $attribValue[ImagesetAttributes::COL_FROM]);
						}else{
							$attribFormValues['ATDE_' . $attribValue[AttributeDescriptor::COL_ID]] = array('fromValue' => $attribValue[ImagesetAttributes::COL_FROM]);
						}
					}else if (array_key_exists(ImagesetAttributes::COL_TO,$attribValue)){
						if(array_key_exists('ATDE_' . $attribValue[AttributeDescriptor::COL_ID],$attribFormValues) && is_array($attribFormValues['ATDE_' . $attribValue[AttributeDescriptor::COL_ID]])){
							$attribFormValues['ATDE_' . $attribValue[AttributeDescriptor::COL_ID]] += array('toValue' => $attribValue[ImagesetAttributes::COL_TO]);
						}else{
							$attribFormValues['ATDE_' . $attribValue[AttributeDescriptor::COL_ID]] = array('toValue' => $attribValue[ImagesetAttributes::COL_TO]);
						}
					}else{
						//in case of no FROM and no TO field (XXX error?) read just the value
						$attribFormValues += array('ATDE_' . $attribValue[AttributeDescriptor::COL_ID] => $attribValue[$valueColumn]);
					}
				} else if ($attribValue[AttributeDescriptor::COL_DATA_TYPE] == 'string') {
					//in case of strings read value
					$attribFormValues += array('ATDE_' . $attribValue[AttributeDescriptor::COL_ID] => $attribValue[$valueColumn]);
				}
			} else if ($attribValue[AttributeDescriptor::COL_FORM_TYPE] == 'checkbox') {
				//checkbox (single), 0 or 1 or NULL
				$attribFormValues += array('ATDE_' . $attribValue[AttributeDescriptor::COL_ID] => $attribValue[$valueColumn]);
			} else if ($attribValue[AttributeDescriptor::COL_FORM_TYPE] == 'multicheckbox'
			||$attribValue[AttributeDescriptor::COL_FORM_TYPE] == 'multiselect'
			||$attribValue[AttributeDescriptor::COL_FORM_TYPE] == 'radio'
			||$attribValue[AttributeDescriptor::COL_FORM_TYPE] == 'select') {
				//case of value list attributes read value
				//use an array as value, search will handle this, if it's the right form type
				//$attribFormValues += array('ATDE_' . $attribValue[AttributeDescriptor::COL_ID] => $attribValue[$valueColumn]);
				if(array_key_exists('ATDE_' . $attribValue[AttributeDescriptor::COL_ID],$attribFormValues) && is_array($attribFormValues['ATDE_' . $attribValue[AttributeDescriptor::COL_ID]])){
					array_push($attribFormValues['ATDE_' . $attribValue[AttributeDescriptor::COL_ID]],$attribValue[$valueColumn]);
				}else{
					$attribFormValues['ATDE_' . $attribValue[AttributeDescriptor::COL_ID]] = array($attribValue[$valueColumn]);
				}
			}
			//			}else{
			//case multiple attributes
			//
			//				if(array_key_exists('ATDE_' . $attribValue[AttributeDescriptor::COL_ID],$attribFormValues) && is_array($attribFormValues['ATDE_' . $attribValue[AttributeDescriptor::COL_ID]])){
			//					array_push($attribFormValues['ATDE_' . $attribValue[AttributeDescriptor::COL_ID]],$attribValue[$valueColumn]);
			//				}else{
			//					$attribFormValues['ATDE_' . $attribValue[AttributeDescriptor::COL_ID]] = array($attribValue[$valueColumn]);
			//				}
			//			}
		}
		$attribFormValues += array('kind'=>'and');

		return $attribFormValues;
	}


}
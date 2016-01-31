<?php
class Ce_TrainingController extends Zend_Controller_Action {

	private $form;
	private $defaultNamespace;

	public function init()
	{
		$this->defaultNamespace = new Zend_Session_Namespace('default');
		$this->form = new Ce_Form_Search();
	}

	public function newselectexpAction() {
		$namespace = new Zend_Session_Namespace('training');

		if ($this->getRequest()->isPost()){
			if ($this->form->isValid($this->getRequest()->getParams())){
				if ($this->form->getValue('Token') == $namespace->Token){
					//insert/update
					//get form value and set namespace
					$this->defaultNamespace->expId = $this->form->getValue(CalibrationExercise::COL_EXPERTISE_ID);
					$namespace->Token = $newToken;

					$newToken = Ble422_Guid::getGuid();
					$this->form->getElement('Token')->setValue($newToken);
					//put validated values in GET params
					$this->redirectTo('newselectavailablekeys', $this->form->getValues());

				}else{
					//form token is not equal session token
					$this->form->reset();
					$this->redirectTo('outofdate');
				}
			}else{
				//not valid
				$this->render('form');
			}
		}else{
			//not post
			//$this->form = new Ce_Form_Search();
			//$this->form->setAction('/ce/new/newtrainselectavailablekeys/'.Workshop::COL_ID.'/TEMP');

			if ($this->form->getValue('Token') == null)	{
				$guid = new Ble422_Guid();
				$namespace->Token = $guid->__toString();
				$this->form->getElement('Token')->setValue($guid->__toString());

				$this->view->form = $this->form;
				$this->render('form');
			}
		}
	}

	public function newselectavailablekeysAction() {
		//$this->form = new Ce_Form_Search();
		$this->form->addElement(new Default_Form_Element_ExpertiseSelect(CalibrationExercise::COL_EXPERTISE_ID, array ('disabled' => 'disabled',
		//																							'value' => $hiddenExp
		)));
		$this->form->removeElement('submit');
		$options = $this->getRequest()->getParams();
		$this->form->setDefaults($options);

		//		if ($this->getRequest()->isPost()){
		//			if ($this->form->isValid($this->getRequest()->getParams())){
		//
		//			} else {
		//				//not valid
		//
		//			}
		//		} else {
		//			//not post
		//			//$this->form->setAction('/ce/new/newtrainselectavailablekeys');

		$this->view->form = $this->form;

		$expId = $options[Expertise::COL_ID];

		$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
		$select2 = $dbAdapter->select();

		$select2->from(	'v_all_annotations',
						array(	KeyTable::COL_ID,
								KeyTable::COL_NAME,
								Expertise::COL_ID,
								'count_images' => 'COUNT('.Image::COL_ID.')',
								'sum_ref_ws' => 'SUM('.Annotations::COL_WS_REF.')',
								'sum_ref_webgr' => 'SUM('.Annotations::COL_WEBGR_REF.')'));

		$select2->where('v_all_annotations.'.CalibrationExercise::COL_EXPERTISE_ID.' = ?', $expId);
		$select2->group('v_all_annotations.'.KeyTable::COL_ID);
		//echo $select2;

		$paginator = new Ble422_Paginator_Extended($select2,$this->getRequest());
		$paginator->setHeader(array(
		array('raw' => KeyTable::COL_NAME, 'name' => 'protocol name'),
		array('raw' => 'count_images', 'name' => 'No. of images'),
		array('raw' => 'sum_ref_ws', 'name' => 'workshop references'),
		array('raw' => 'sum_ref_webgr', 'name' => 'WebGR references'),
		));
		$paginator	->setCurrentPageNumber($this->getRequest()->getParam('page'))
		->setItemCountPerPage(1000)//$this->_getParam('itemCountPerPage'))
		->setPageRange(10)
		->orderBy($this->getRequest()->getParam('orderBy'));//$this->_getParam('pageRange'));

		Zend_View_Helper_PaginationControl::setDefaultViewPartial(
	                          'partials/list_pagination_control.phtml'); 
		$this->view->paginator = $paginator;

		//bilder und referenzen pro key aufsummieren und listen
		//bilderarray hier schon vorrätig halten

		//WS ID = TEMP+NAME+.uniqid()
		//CE Datensatz anlegen NR
	}

	public function createAction()
	{


		$expId = intval($this->getRequest()->getParam(Expertise::COL_ID));
		$keyId = intval($this->getRequest()->getParam(KeyTable::COL_ID));

		$expTable = new Expertise();
		$expRow = $expTable->find($expId);

		$keyTable = new KeyTable();
		$keyRow = $keyTable->find($keyId);

		if ($keyRow->count() != 0 || $expRow->count() != 0) {
			$keyArray = $keyRow->toArray();
			$expArray = $expRow->toArray();

			// create CE row
			$ceTable = new CalibrationExercise();
			$ceName = $expArray[0][Expertise::COL_SPECIES] . ' / ' . AuthQuery::getUserName();
			//TODO \r in der Datenbank
			$ceDescription =  'Area: ' . $expArray[0][Expertise::COL_AREA] . '\r' .
		                      'Subject: ' . $expArray[0][Expertise::COL_SUBJECT] . '\r' .
		                      'KeyName: ' . $keyArray[0][KeyTable::COL_NAME];
			$ceData = array(CalibrationExercise::COL_NAME => $ceName,
			CalibrationExercise::COL_DESCRIPTION => $ceDescription,
			CalibrationExercise::COL_KEY_TABLE_ID => $keyArray[0][KeyTable::COL_ID],
			CalibrationExercise::COL_EXPERTISE_ID => $expArray[0][Expertise::COL_ID],
			CalibrationExercise::COL_COMPAREABLE => 1,
			CalibrationExercise::COL_IS_STOPPED => 0,
			CalibrationExercise::COL_TRAINING => 1);
			$ceId = $ceTable->insert($ceData);

			// create participant row
			$partTable = new Participant();
			$partData = array(  Participant::COL_CE_ID => $ceId,
			Participant::COL_USER_ID => AuthQuery::getUserId(),
			Participant::COL_NUMBER => 1);
			$partId = $partTable->insert($partData);

			// add all possible shown attributes
			$dbAdapter = $ceTable->getAdapter();
			$selectAttr = $dbAdapter->select();
			$selectAttr->from(AttributeDescriptor::TABLE_NAME);
			$selectAttr->orWhere(AttributeDescriptor::COL_GROUP . '=?','fish');
			$selectAttr->orWhere(AttributeDescriptor::COL_GROUP . '=?','image');
			$attrArray = $dbAdapter->fetchAll($selectAttr);

			$ceHasAttrTable = new CeHasAttributeDescriptor();
			foreach ($attrArray as $attr) {
				$attrData = array(CeHasAttributeDescriptor::COL_ATDE_ID => $attr[AttributeDescriptor::COL_ID],
				CeHasAttributeDescriptor::COL_CAEX_ID => $ceId);
				$ceHasAttrTable->insert($attrData);
			}

			//get images for exp/key
			$refQuery = new Default_ReferenceQuery();
			$images = $refQuery->getImages($expId, $keyId);
			
			// create imageset
//			$selectImages = $dbAdapter->select();
//			$imagesArray = $dbAdapter->fetchAll($selectImages);
				
			$imageSetTable = new CeHasImage();
			foreach ($images as $image) {
				$data = array(	CeHasImage::COL_IMAGE_ID => $image,
								CeHasImage::COL_CALIBRATION_EXERCISE_ID => $ceId);
				$imageSetTable->insert($data);
			}
			//$this->render('form');
				
			$Redirect = new Zend_Controller_Action_Helper_Redirector();
			$Redirect->setGotoSimple('index','make','annotation',array(CalibrationExercise::COL_ID => $ceId));

		}else{
			throw new Zend_Controller_Exception('Error at craeting a new training Calibration Exercise.',505);
		}
	}

	public function outofdateAction()
	{
		;
	}

	public function redirectTo($action, $options = NULL)
	{
		$Redirect = new Zend_Controller_Action_Helper_Redirector();
		$Redirect->setGoto($action,'training','ce',$options);
	}
}
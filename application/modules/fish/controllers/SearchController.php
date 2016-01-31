<?php
class Fish_SearchController extends Zend_Controller_Action
{
	private $form;
	private $defaultNamespace;
	private $namespace;

	public function init()
	{
		$this->namespace = new Zend_Session_Namespace('fish_search');
		$this->defaultNamespace = new Zend_Session_Namespace('default');

		//to prevent errors from nullpointers in view
		if (!isset($this->defaultNamespace->callingAction)) {
			$this->defaultNamespace->callingAction = '';
		}
	}

	//delete defNamespace and redirect to index
	public function resetAction() {
		$this->defaultNamespace->callingAction = '';
		$this->defaultNamespace->callingActionId = '';
		$this->redirectTo('index');
	}

	public function indexAction() {

		//$form = new Image_Form_Edit();
		//$this->view->form = $form;

		//$this->form->setAction(Zend_Controller_Front::getInstance()->getBaseUrl().'/user/search');
		$request = $this->getRequest();
		$params = $request->getParams();
		$formValues = $this->namespace->formValues;

		$this->namespace->unsetAll();
		$this->namespace->searchParams = $params;

		//-----------------------------------------------------------------
		//build form
		$this->form = new Fish_Form_Search();
		$this->form->removeElement(Image::COL_ID);
		$this->form->removeElement('save');

		$this->form->addElement('submit', 'submit', array('label'=>'Search'
		));

		//set all elements to required=FALSE
		//clear all validators
		$formElements = $this->form->getElements();
		foreach ($formElements as $elem)
		{
			$elem->setRequired(false);
			$elem->clearValidators();
		}
		//$this->form->setElements($formElements);
		unset($elem);
		unset($formElements);
		//-----------------------------------------------------------------

		if ($request->isPost() && $this->form->isValid($params))
		{
			$this->namespace->formValues = $this->form->getValues();
			$this->redirectTo('search');
		}
		else
		{
			//$this->form->setValues($params);
			//TODO follow-action setzen... HIER? nachfolgende Zeile zum Test...
			//$this->form->setAction(Zend_Controller_Front::getInstance()->getBaseUrl().'/user/search/search');
			//$this->form->populate($params);
			$this->view->form = $this->form;
		}
	}

	public function searchAction()
	{
		$request = $this->getRequest();
		$formValues = $this->namespace->formValues;

		$this->view->callingAction = $this->defaultNamespace->callingAction;

		// get meta data select for image and fish descriptors
		$metaData = new Default_MetaData();
		$metaData->getSelectForGroups(FALSE);
		$select = $metaData->addWhereToSelect($formValues);
		
        //TODO test with multiple fish / multiple image checked attributes
		//changed, if images are found, but search was for fish, multiple image attributes would cause multiple entries
		//TODO error: only first fish without images shown due to this group; if there are many fishes without image, there is only one of them shown in list
		$select->group('fish.'.Fish::COL_ID);

		if($this->defaultNamespace->callingAction == 'annotation/browse/byfish'){
			$select->joinLeft(array('vaa'=>'v_all_annotations'),
                                 'vaa.'.CeHasImage::COL_IMAGE_ID . ' = ' . 'image.' . Image::COL_ID,array()); //auf cehas_image damit die ausgefiltert werden die keine annotationen haben
			$select->group('image.' . Image::COL_ID);
			$resultArray = Zend_Registry::get('DB_CONNECTION1')->fetchAll($select);
			if($resultArray != array()){
				$resultKey = new Ble422_Guid();
				$this->defaultNamespace->$resultKey = $resultArray;
				$redirect = new Zend_Controller_Action_Helper_Redirector();
				$redirect->setGotoSimple('start','browse','annotation', array('resultKey'=>$resultKey));
			}
		} else {

			//select only own fish
			if ($this->defaultNamespace->callingAction == 'user/edit/myfishes') {
				$select->where('fish.'.Fish::COL_USER_ID.' = ?', $this->defaultNamespace->callingActionId);
			}

			//new
			$this->namespace->select = $select;
			//new

			/**
			 * Pagination control
			 */
			$paginator = new Ble422_Paginator_Extended($select,$this->getRequest());
			//static + dynamic header array
			$statArray = array(
			array('raw'=>Fish::COL_SAMPLE_CODE,'name'=>'Fish sample code'));

			$dynArray = array();
			$i = 0;
			foreach ($metaData->fishRowSetArray as $fishAttr) {
				$dynInnerArray = array(	'raw' => 'ATDE_'.$fishAttr[AttributeDescriptor::COL_ID],
									'name' => 	$fishAttr[AttributeDescriptor::COL_NAME].
												'<br>'.
				$fishAttr['UNIT']);
				$dynArray[$i] = $dynInnerArray;
				$i++;
			}

			$headerArray = array_merge ($statArray, $dynArray);
			unset($i);
			unset($dynArray);
			unset($dynInnerArray);

			$paginator->setHeader($headerArray);
			$paginator	->setCurrentPageNumber($this->getRequest()->getParam('page'))
			->setItemCountPerPage(1000)//$this->_getParam('itemCountPerPage'))
			->setPageRange(10)
			->orderBy($this->getRequest()->getParam('orderBy'));//$this->_getParam('pageRange'));

			Zend_View_Helper_PaginationControl::setDefaultViewPartial(
	                          'partials/list_pagination_control.phtml'); 
			$this->view->paginator = $paginator;

			$storage = Zend_Auth::getInstance()->getStorage()->read();
			$constUserRole = User::COL_ROLE;
			$this->view->userRole = $storage->$constUserRole;
		}
	}

	public function redirectTo($action , array $params = array())
	{
		$redirect = new Zend_Controller_Action_Helper_Redirector();
		$redirect->setGoto($action,'search','fish', $params);
	}

	private function formKeyHasValue($key, $value) {
		//first if condition asks for possible simple form key value pairs
		//'submit' submit button excluded
		//'kind' radio button excluded
		//'Token' string excluded
		if ($key != null && $value != null && $key != 'kind' && $key != 'submit' && $key != 'Token') {
			//second if condition asks if possible value array has any value filled
			if (is_array($value)) {
				foreach ($value as $val) {
					//is_null doesn't return TRUE for *empty* array elements from form element, use != NULL
					if ($val != NULL) {
						return TRUE;
					}
				}
				return FALSE;
			} else {
				return TRUE;
			}
		} else {
			return FALSE;
		}
	}
}
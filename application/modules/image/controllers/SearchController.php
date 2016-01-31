<?php
class Image_SearchController extends Zend_Controller_Action
{
	private $form;
	private $defaultNamespace;
	private $namespace;

	public function init()
	{
		$this->namespace = new Zend_Session_Namespace('image_search');
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
		$this->form = new Image_Form_Search();
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
		$metaData->getSelectForGroups(TRUE);

		$select = $metaData->addWhereToSelect($formValues);
		
		if($this->defaultNamespace->callingAction == 'annotation/browse/byimage'){
			unset($this->namespace->ceArray);
			$select->joinLeft(array('vaa'=>'v_all_annotations'),
			                     'vaa.'.CeHasImage::COL_IMAGE_ID . ' = ' . 'image.' . Image::COL_ID,array()); //auf cehas_image damit die ausgefiltert werden die keine annotationen haben
			$select->where(Annotations::COL_WS_REF."=? ",1);
			$select->orWhere(Annotations::COL_FINAL." = ? ",1);
			$select->orWhere(Annotations::COL_GROUP."=? ",1);
			$select->orWhere(Annotations::COL_WEBGR_REF."=? ",1);
			$select->group('image.' . Image::COL_ID);
			$resultArray = Zend_Registry::get('DB_CONNECTION1')->fetchAll($select);
			if($resultArray != array()){
				$resultKey = new Ble422_Guid();
				$this->defaultNamespace->$resultKey = $resultArray;
			    $redirect = new Zend_Controller_Action_Helper_Redirector();
	            $redirect->setGotoSimple('start','browse','annotation', array('resultKey'=>$resultKey));
			}
		} else {
			//select only own images
			if ($this->defaultNamespace->callingAction == 'user/edit/myimages') {
				$select->where('image.'.Image::COL_USER_ID.' = ?', $this->defaultNamespace->callingActionId);
			}
			
			//filter double datasets caused by multiple meta data
			//$select->group('image.'.Image::COL_ID);
			
			//get already assigned datasets for setting disabled in view
			if ($this->defaultNamespace->callingAction == '/ce/edit/addimages') {
				$ceId = $this->defaultNamespace->callingActionId;
				$ceHasIm = new CeHasImage();
				$rowSet = $ceHasIm->fetchAll(CeHasImage::COL_CALIBRATION_EXERCISE_ID.'='.$ceId);
				if (count($rowSet) > 0) {
					$assignedImages = array();
					foreach ($rowSet as $row) {
						$assignedImages[$row[CeHasImage::COL_IMAGE_ID]] = TRUE;
					}
					$this->view->assignedImages = $assignedImages;
				}
			}		
			
			//echo $select;
	        
			/**
			 * Pagination control
			 */
			$paginator = new Ble422_Paginator_Extended($select,$this->getRequest());
//			echo $select->__toString();
//			die();
			//static + dynamic header array
			$statArray = array(	array('raw'=>Image::COL_ORIGINAL_FILENAME, 'name'=>'Original file name'),
			array('raw'=>Fish::COL_SAMPLE_CODE,'name'=>'Fish sample code'),
			array('raw'=>Image::COL_DIM_X,'name'=>'Width'),
			array('raw'=>Image::COL_DIM_Y,'name'=>'Height'));
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
	
			$i = 0;
			foreach ($metaData->imageRowSetArray as $imAttr) {
				$dynInnerArray = array(	'raw' => 'ATDE_'.$imAttr[AttributeDescriptor::COL_ID],
										'name' => 	$imAttr[AttributeDescriptor::COL_NAME].
													'<br>'.
													$imAttr['UNIT']);
				$dynArray[$i] = $dynInnerArray;
				$i++;
			}
	
			$headerArray = array_merge ($headerArray, $dynArray);
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
		}
	}

	public function redirectTo($action , array $params = array())
	{
		$redirect = new Zend_Controller_Action_Helper_Redirector();
		$redirect->setGoto($action,'Search','image', $params);
	}
}
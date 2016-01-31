<?php
class Image_UploadController extends Zend_Controller_Action
{
	private $form;

	public function init() {

		$this->form = new Image_Form_Edit();
		$this->form->removeElement(Image::COL_ID);
		$this->form->removeElement(Image::COL_ORIGINAL_FILENAME);

		//create the upload element-----------------------------
		$element = new Zend_Form_Element_File('uploadElement');
        
        $path = __FILE__;
        $path = dirname($path);
        $path = dirname($path);
        $path = dirname($path);
        $path = dirname($path);
        $path = dirname($path);
		$element->setLabel('Upload image(s):')
		->setDestination($path . '/public/images');
		// ensure minimum 1, maximum 4 files
		$element->addValidator('Count', false, array('min' => 1, 'max' => 4));
		// limit to 100K
		//$element->addValidator('Size', false, 102400);
		// only JPEG, PNG, and GIFs
		$element->addValidator('Extension', false, 'jpg,png,gif,jpeg');
		// defines 4 identical file elements
		$element->setMultiFile(4);
		$element->setOrder(0);
		$this->form->addElement($element);
		//------------------------------------------------------
        $fishSampleCode = new Zend_Form_Element_Text(Fish::COL_SAMPLE_CODE);
        $fishSampleCode->setLabel('Fish Sample Code:');
        $fishSampleCode->setOrder(1);
        $this->form->addElement($fishSampleCode);
		
		//#####################new###################################
		$this->form->setDecorators(array(
                'FormElements',
		array('HtmlTag', array('tag' => 'table', 'class' => 'login_form')),
		array('Description', array('placement' => 'prepend')),
                'Form'
                ));
                $this->form->setElementDecorators(array(
            'ViewHelper',
            'Errors',
                array(  'decorator' => array('td' => 'HtmlTag'),
                        'options' => array('tag' => 'td')),
                array(  'Label', array('tag' => 'td')),
                array(  'decorator' => array('tr' => 'HtmlTag'),
                        'options' => array('tag' => 'tr')),
                ));
                $element->setDecorators(array(
                'File',
                'Errors',
                 array(  'decorator' => array('td' => 'HtmlTag'),
                        'options' => array('tag' => 'td')),
                array(  'Label', array('tag' => 'td')),
                array(  'decorator' => array('tr' => 'HtmlTag'),
                        'options' => array('tag' => 'tr')),
                ));
                //###########################################################
                $this->view->form = $this->form;
	}

	public function indexAction()
	{
		$addFish = false;

		if ($this->getRequest()->isPost()) {
			$params = $this->getRequest()->getParams();
			if ($this->form->isValid($params)) {
					
				//TODO wenn fishsamplecode noch nicht in der datenbank,
				//dann lege fisch an mit diesem samplecode (erledigt)
				//und SPRINGE IN Fish_CreateController (am Ende des Controllers)
				//TODO mit getElement und Konstanten arbeiten
				$fishSampleCode = $this->form->getValue(Fish::COL_SAMPLE_CODE);
				$fishTable = new Fish();
				$row = $fishTable->fetchRow($fishTable->select()->where(Fish::COL_SAMPLE_CODE . '= ?', $fishSampleCode));
				if (is_null($row)) {
					//echo 'Warnung: Fish Sample Code nicht vorhanden.';
					$fishId = $fishTable->insert(array(Fish::COL_SAMPLE_CODE => $fishSampleCode));
					$addFish = true;
					$constFishId = Fish::COL_ID;
					$fishId->$constFishId = $fishId;
				}
				else
				{
					$addFish = false;
					$fishId = $row->FISH_ID;
				}
				//TODO dateinamen mit leerzeichen
				//könnte ein problem bei der weiterverarbeitung in linux sein
				//klären,
				//ggf. akzeptieren und umwandeln oder verweigern?

				//don't call $form->getValues() - causes physical upload immediately
				//look ZFDoc 19.1.3
				//note: if using Zend_Form_Element_File (in view) you can't use new instance of Zend_File_Transfer_Adapter_Http in controller!
				// http://www.zfforums.com/zend-framework-forum-8/general-talks-12/file_transfer-illegal-uploaded-possible-attack-1737.html
				// http://www.nabble.com/Zend_File_Transfer-td19024470.html
				//solution:
				//getTransferAdapter()
				$upload = $this->form->uploadElement->getTransferAdapter();
				// Returns all known internal file informations
				$files = $upload->getFileInfo();

				$imageTable = new Image();
				$medimTable = new MetaDataImage();
				//look ZFDoc 19.3.3
				//iterates over all file elemens

				$numberImagesUploaded = 0;
				$userId = AuthQuery::getUserId();
				foreach ($files as $file => $info) {
					/*handle only uploaded files, skip blank file elements
					 store original file in certain path
					 file is saved without excplicit temp directory with Zend Filter
					 image import uses temp directory instead
					 */
					if (!$upload->isUploaded($file)) {
						continue;
					}
					$creator_guid = new Ble422_Guid();
					$guid = $creator_guid->__toString();

					$fileName = $info['name'];
					$path_parts = pathinfo($fileName);
					$originalFileName = $path_parts['basename']; //used later to create Image dataset
					$newFileNameWithGuid = $guid.'.'.strtolower($path_parts['extension']); //save extension in lower-case

					//relative path with new filename, prefix dot&slash required
					$completeTarget = './'.Image::RELATIVE_UPLOAD_PATH.'/'.$newFileNameWithGuid;

					//apply filter only for uploaded file $fileName
					$upload->addFilter('Rename', array('target' => $completeTarget, 'overwrite' => false), $fileName);
					$upload->receive($file);
					//TODO write protect files
					$upload->clearFilters();

					try
					{
						$ratio = $this->form->getValue(Image::COL_RATIO_EXTERNAL);
						$tn_ratio = $imageTable->processImage($completeTarget);
						//ratio is filled from form
						$imageId = $imageTable->insertImageDataset($completeTarget, $originalFileName, $fishId, $guid, $userId, $ratio, $tn_ratio);
						$imageTable->updateImageAndMetadata($this->form, $imageId);
						$numberImagesUploaded++;
					}
					catch (Exception $e)
					{
						echo "Exception: ".$e->getMessage();
					}
				}
				Zend_Registry::set('MESSAGE',$numberImagesUploaded.' image(s) successfully inserted');
				if ($addFish) {
					$next = array( 'nextAction' => 'index',
                           'nextController' => 'upload',
                           'nextModul' => 'image');
					$namespace = new Zend_Session_Namespace('default');
					$namespace->next = $next;
						
					$redirect = new Zend_Controller_Action_Helper_Redirector();
					$redirect->setGotoSimple('update','edit','fish',array(Fish::COL_ID => $fishId));
				}
			} else {
				//form isn't valid
				$this->form->populate($params);
				$this->view->form = $this->form;
			}
		}
		//$this->view->form = $form;
	}
}
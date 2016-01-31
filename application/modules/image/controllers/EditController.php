<?php
class Image_EditController extends Zend_Controller_Action {

	private $form;
	private $namespace;
	private $imageTable;

	public function init()
	{
		$this->namespace = new Zend_Session_Namespace('image_edit');
		$this->form = new Image_Form_Edit();
		$this->imageTable = new Image();
	}

	public function insertAction()
	{

	}

	public function updateAction()
	{
		$request = $this->getRequest();

		if ($request->isPost() AND $this->form->isValid($request->getParams())) {
			$imageId = $this->form->getValue(Image::COL_ID);
			$data = array(   Image::COL_ORIGINAL_FILENAME => $this->form->getValue(Image::COL_ORIGINAL_FILENAME),
			                 Image::COL_RATIO_EXTERNAL => $this->form->getValue(Image::COL_RATIO_EXTERNAL));

			$this->imageTable->updateImageAndMetadata($this->form, $imageId, $data);

			$redirect = new Zend_Controller_Action_Helper_Redirector();
			$redirect->setGotoSimple('search', 'search', 'image');
		} else {
				
			$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
				
			$imageId = intval($this->getRequest()->getParam(Image::COL_ID));
			$imageResult = $this->imageTable->find($imageId)->current();
			if ($imageResult != null) {
				$imageArray = $imageResult->toArray();
			} else {
				$imageArray = array();
			}

			// get meta data
			$select = $dbAdapter->select();
			$select->from(	MetaDataImage::TABLE_NAME);
			$select->join(  AttributeDescriptor::TABLE_NAME,
							MetaDataImage::TABLE_NAME. '.' . MetaDataImage::COL_ATTRIBUTE_DESCRIPTOR_ID . '='.AttributeDescriptor::TABLE_NAME.'.'.AttributeDescriptor::COL_ID);
							
			$select->where(	MetaDataImage::COL_IMAGE_ID. '=?', $imageId);

			//TODO: Fish::COL_SAMPLE_CODE abfragen über FISH_ID

			$metaArray = $dbAdapter->fetchAll($select);

			$this->form->dynPopulate($metaArray,MetaDataImage::COL_VALUE,$imageArray);
			$this->view->form = $this->form;

		}
	}

	public function deleteAction() {
		//TODO check ce_has_image, link is not available but action is callable!!
		//delete image files (org./copy/thumb)
		//delete image
		//delete metadata image -> DB on delete cascade

		$request = $this->getRequest();
		$imageId = intval($this->getRequest()->getParam(Image::COL_ID));
		$image = new Image();
		//get image_guid
		//get original extension
		$rowset = $image->find($imageId);

		if (count($rowset) == 1) {
			$rowsetArray = $rowset->toArray();
			$imageRow = $rowsetArray[0];
			$imGuid = $imageRow[Image::COL_GUID];
			$path_parts = pathinfo($imageRow[Image::COL_ORIGINAL_FILENAME]);
			$imExt = $path_parts['extension'];
			$filename = $imGuid.'.'.$imExt;
			$jpgFilename = $imGuid.'.'.'jpg';
			//delete file org/guid+org_ext
			//delete file copy/guid+.jpg
			//delete file thumb/guid+.jpg
			$RELATIVE_UPLOAD_PATH = 'images/originals'; //without pre- and post-slash!
			$RELATIVE_PATH_IMAGE_THUMBNAILS = 'images/thumbnails';
			$RELATIVE_PATH_IMAGE_SHRINKED_WORKING_COPIES = 'images/shrinked_working_copies';
			try {
				$myFile = $RELATIVE_UPLOAD_PATH.'/'.$filename;
				$fh = fopen($myFile, 'w');
				fclose($fh);
				unlink($myFile);
				$myFile = $RELATIVE_PATH_IMAGE_SHRINKED_WORKING_COPIES.'/'.$jpgFilename;
				$fh = fopen($myFile, 'w');
				fclose($fh);
				unlink($myFile);
				$myFile = $RELATIVE_PATH_IMAGE_THUMBNAILS.'/'.$jpgFilename;
				$fh = fopen($myFile, 'w');
				fclose($fh);
				unlink($myFile);
			}
			catch (Exception $e) {
				throw new Zend_Exception('Error: can not open file');
			}

			//note: delete of metadata is executed from db
			$image->delete($image->getAdapter()->quoteInto(Image::COL_ID .' = ?', $imageId));

			//hard delete plus:
			//delete ce_has_image
			//delete annotations
			//delete dots



			//		$imageId = intval($this->getRequest()->getParam(Image::COL_ID));
			//			$imageResult = $imageTable->find($imageId)->current();
			//			if($imageResult != null){
			//				$imageArray = $imageResult->toArray();
			//			}else{
			//				$imageArray = array();
			//			}
		}
		$redirect = new Zend_Controller_Action_Helper_Redirector();
		$redirect->setGoto('search','search','image');
	}
}
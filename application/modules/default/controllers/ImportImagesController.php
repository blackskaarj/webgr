<?php
class ImportImagesController extends Zend_Controller_Action {
	public function importAction()
	{
		$import = new Service_Batch();
		//print_r($import->getAttributes(3));
		//$testArr2 = array('LEN', Fish::COL_SAMPLE_CODE, Image::COL_ORIGINAL_FILENAME);
		//$testArr1 = array('Fish length', Fish::COL_SAMPLE_CODE, Image::COL_ORIGINAL_FILENAME);
		//$import->checkCsv(3, $testArr1, $testArr2);
		//$import->beginImport(3);
	}
}
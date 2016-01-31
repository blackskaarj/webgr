<?php

class Service_Image {
	
	/**
	 * Get the imageVO by the primary key
	 *
	 * @param int $id
	 * @return imageVO
	 * @todo right exception handling
	 */
	public function getImageById($id){
    	$table = new Image();
		try {
			$rowset = $table->find($id);
			$dataArray = $rowset->toArray();
			return $dataArray;
		}
		catch(Exception $e){
			return null;
		}
//		$image = new imageVO();
//		$image->imageId = $dataArray[0][image::colId];
//		$image->fishId = $dataArray[0][image::colFishId];
//		$image->fileName = $dataArray[0][image::colFilename];
//		$image->fileName = $dataArray[0][image::colChecksum];
		
    }
    
    private function getCurrentCeID()
    {
    	$namespace = new Zend_Session_Namespace('default');
        $constCeId = CalibrationExercise::COL_ID;
        return $namespace->$constCeId;
    }
    
	/**
	 * 
	 * @return array
	 * @todo right exception handling
	 */	
    public function getImages($ceId = null)
    {
    	$table = new Image();
        
    	$namespace = new Zend_Session_Namespace('default');
		$auth = Zend_Auth::getInstance();
		$storage = $auth->getStorage()->read();
		if($ceId == null){
			$constCeId = CalibrationExercise::COL_ID;
			$ceId = $namespace->$constCeId;
		}
    	$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
    	$select = $dbAdapter->select();
    	$select->from(	array( "im" => Image::TABLE_NAME));
    	$select->join(	array( "ceHim" => CeHasImage::TABLE_NAME),
    					"im." . Image::COL_ID . " = " . " ceHim." . CeHasImage::COL_IMAGE_ID,
    					array( 	CeHasImage::COL_IMAGE_ID,
    							CeHasImage::COL_ID));
//    	if($state == null || $state == "groupState"){
    		 $select->where(CeHasImage::COL_CALIBRATION_EXERCISE_ID . " = ?",$ceId);
//    	}else{
//    		$select->join( array( "ce" => CalibrationExercise::TABLE_NAME),
//                               "ce." . CalibrationExercise::COL_ID . " = " . " ceHim." . CeHasImage::COL_CALIBRATION_EXERCISE_ID);
//            $select->join(array('exp'=>Expertise::TABLE_NAME),
//                               'ce.'.CalibrationExercise::COL_EXPERTISE_ID . "=" . 'exp.' . Expertise::COL_ID);
//	    	if($state == 'ws-refState'){
//	            $select->where("ce." . CalibrationExercise::COL_WORKSHOP_ID . "=?",$namespace->ceArray[0][CalibrationExercise::COL_WORKSHOP_ID],"int");
//	        }elseif($state == 'webgr-refState'){
//	            $select->join(array('ws'=>Workshop::TABLE_NAME),
//	                                'ce.'.CalibrationExercise::COL_WORKSHOP_ID . "=" . 'ws.' . Workshop::COL_ID);
//	        }
//	        $select->where("exp." . Expertise::COL_ID . "=?",$namespace->ceArray[0][Expertise::COL_ID],"int");
//    	}
    	$select->group("im." . Image::COL_ID);
    	
		$dataArray = $dbAdapter->fetchAll($select);
		return $dataArray;
    }
    
    public function getImagesByKey($resultKey){
    	$defaultNamespace = new Zend_Session_Namespace('default');
    	return $defaultNamespace->$resultKey;
    }
}
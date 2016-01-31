<?php

class Service_Info
{
	public function getInfo($ceHim)
	{
		$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
		$select = $dbAdapter->select();
		$select->from('v_fish_info');
		$select->where(CeHasImage::COL_ID . "=?",$ceHim);
		$resultArray = array('fish_info' => $dbAdapter->fetchAll($select));
		$select->reset();
		$select->from('v_image_info');
		$select->where(CeHasImage::COL_ID . "=?",$ceHim);
		$resultArray = array_merge($resultArray,array('image_info' => $dbAdapter->fetchAll($select)));
		$select->reset();
		$select->from('v_imageset_info');
		$select->where(CeHasImage::COL_ID . "=?",$ceHim);
		$resultArray = array_merge($resultArray,array('imageset_info' => $dbAdapter->fetchAll($select)));

		//get participant and user role
		$namespace = new Zend_Session_Namespace('default');
		$constPartRole = Participant::COL_ROLE;
		$resultArray = array_merge($resultArray,array(Participant::COL_ROLE => $namespace->$constPartRole));
		$auth = Zend_Auth::getInstance();
		$constUserRole = User::COL_ROLE;
		$resultArray = array_merge($resultArray,array(User::COL_ROLE =>$auth->getStorage()->read()->$constUserRole));

		//set CE-info (Ce-Name, Expertise...)
		//refresh CE-info result Object
		$select->reset();
		$select->from(array('caex' => CalibrationExercise::TABLE_NAME));
		$select->join(array('exp'=>Expertise::TABLE_NAME),
                                'caex.'.CalibrationExercise::COL_EXPERTISE_ID . "=" . 'exp.' . Expertise::COL_ID,
		array(Expertise::COL_AREA));
		$select->join(array('key'=>KeyTable::TABLE_NAME),
                                'caex.'.CalibrationExercise::COL_KEY_TABLE_ID . "=" . 'key.' . KeyTable::COL_ID);
		$select->joinLeft(array('work'=>Workshop::TABLE_NAME),
                                'caex.'.CalibrationExercise::COL_WORKSHOP_ID . "=" . "work." . Workshop::COL_ID,
		array(Workshop::COL_NAME));
		$select->join(array('cehim'=>CeHasImage::TABLE_NAME),
                                'caex.'.CalibrationExercise::COL_ID . "=" . 'cehim.' . CeHasImage::COL_CALIBRATION_EXERCISE_ID);
		$select->join(array('image'=>Image::TABLE_NAME),
                                'cehim.'.CeHasImage::COL_IMAGE_ID . "=" . 'image.' . Image::COL_ID,
        array(Image::COL_ORIGINAL_FILENAME));
        $select->join(array('fish'=>Fish::TABLE_NAME),
                                'image.'.Image::COL_FISH_ID . "=" . 'fish.' . Fish::COL_ID,
        array(Fish::COL_SAMPLE_CODE));
		$select->join(array ('vali1' => ValueList::TABLE_NAME),
                                        'exp.'.Expertise::COL_SUBJECT.'=vali1.'.ValueList::COL_ID,
		array(Expertise::COL_SUBJECT => ValueList::COL_NAME));
		$select->join(array ('vali2' => ValueList::TABLE_NAME),
                                        'exp.'.Expertise::COL_SPECIES.'=vali2.'.ValueList::COL_ID,
		array(Expertise::COL_SPECIES => ValueList::COL_NAME));
		$select->where('cehim.' . CeHasImage::COL_ID . "=?",$ceHim);
		
		$ceArray = $dbAdapter->fetchAll($select);
		
		$namespace->ceArray = $ceArray;

		$resultArray = array_merge($resultArray, $namespace->ceArray[0]);

		return $resultArray;
	}

	public function getInfoForImageId($imageId)
	{
		$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
		$select = $dbAdapter->select();
		$select->from('v_fish_info');
		$select->where(Image::COL_ID . "=?",$imageId);
		$select->group(AttributeDescriptor::COL_NAME);
		$resultArray = array('fish_info' => $dbAdapter->fetchAll($select));
		$select->reset();
		$select->from('v_image_info');
		$select->where(Image::COL_ID . "=?",$imageId);
		$select->group(AttributeDescriptor::COL_NAME);
		$resultArray += array_merge($resultArray,array('image_info' => $dbAdapter->fetchAll($select)));
		//return $select->__toString();
		return $resultArray;
	}
}

?>
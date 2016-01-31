<?php
class Default_ReferenceQuery
{
	private $dbAdapter;

	public function __construct($dbAdaptername = 'DB_CONNECTION1')
	{
		$this->dbAdapter = Zend_Registry::get($dbAdaptername);
	}

	public static function hasValueListData($atdeId)
	{
		$table = new ValueList();
		$tableAdapter = $table->getAdapter();
		$select = $tableAdapter->select();
		$select->from(ValueList::TABLE_NAME);
		$select->where(ValueList::COL_ATTRIBUTE_DESCRIPTOR_ID.' = ?', $atdeId, 'int');
		$result = $tableAdapter->fetchAll($select);
		//use empty for testing if result = Array[0] - don't use is_null
		if (empty($result))
		{
			//found nothing
			return FALSE;
		}
		else
		{
			return TRUE;
		}

	}

	public static function hasValueList($atdeId)
	{
		$table = new AttributeDescriptor();
			
		$result = $table->find($atdeId);
			
		//use empty for testing if result = Array[0] - don't use is_null
		if (!count($result)==1)
		{
			//found nothing
			return FALSE;
		}
		else
		{
			if ($result[0][AttributeDescriptor::COL_VALUE_LIST] == 1) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}

	public static function ceHasAnnotation($ceId)
	{
		//iterate all images used in CE
		$table = new CeHasImage();
		$tableAdapter = $table->getAdapter();
		$select = $tableAdapter->select();
		$select->from(CeHasImage::TABLE_NAME);
		$select->where(CeHasImage::COL_CALIBRATION_EXERCISE_ID.' = ?', $ceId, 'int');
		$rowset = $tableAdapter->fetchAll($select);
		foreach ($rowset as $row) {
			if (Default_SimpleQuery::isValueInTableColumn($row[CalibrationExercise::COL_ID], new Annotations(), Annotations::COL_CE_HAS_IMAGE_ID)) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * used for access control
	 * @param $ceId
	 * @return unknown_type
	 */
	public static function isParticipantInCe($ceId) {
		$table = new Participant();
		$tableAdapter = $table->getAdapter();
		$select = $tableAdapter->select();
		$select->from(Participant::TABLE_NAME);
		$select->where(Participant::COL_CE_ID.' = ?', $ceId, 'int');
		$select->where(Participant::COL_USER_ID.' = ?', AuthQuery::getUserId(), 'int');
		$rowset = $tableAdapter->fetchAll($select);
		//just in case, user is in ce multiple times, use greater than 1
		if (count($rowset) >= 1) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
/**
     * used for access control
     * @param $ceId
     * @return unknown_type
     */
    public function isParticipantInTrainingCe($ceId) {
    	$select = NULL;
        $select = $this->dbAdapter->select();
        $select->from(array('part' => Participant::TABLE_NAME));
        $select->join(array('cali' => CalibrationExercise::TABLE_NAME),
                  'part.' . Participant::COL_CE_ID . '=' . 'cali.' . CalibrationExercise::COL_ID);
        $select->where('part.' . Participant::COL_CE_ID.' = ?', $ceId, 'int');
        $select->where('part.' . Participant::COL_USER_ID.' = ?', AuthQuery::getUserId(), 'int');
    	$select->where('cali.' . CalibrationExercise::COL_TRAINING.' = ?', 1);
        $result = $this->dbAdapter->fetchAll($select);
        //just in case, user is in ce multiple times, use greater than 1
        if (count($result) >= 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }	

	/**
     * used for access control 
	 * @param $ceId
	 * @return unknown_type
	 */
	public static function isCoordinatorInCe($ceId) {
		$table = new Participant();
		$tableAdapter = $table->getAdapter();
		$select = $tableAdapter->select();
		$select->from(Participant::TABLE_NAME);
		$select->where(Participant::COL_CE_ID.' = ?', $ceId, 'int');
		$select->where(Participant::COL_USER_ID.' = ?', AuthQuery::getUserId(), 'int');
		$select->where(Participant::COL_ROLE.' = ?', 'Coordinator', 'string');
		//use fetchAll because there can be many coord.
		$rowset = $tableAdapter->fetchAll($select);
		//just in case, user is in ce multiple times, use greater than 1
		if (count($rowset) >= 1) {
//		if (is_string($rowset)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function getSelectReferencesForExpertise($expId) {
		//get WebGR and Ws references for a certain expertise
		//get no of images and WebGR and Ws references
		//get array of images for imageset that have this exp and key
		//click list entry
		//
		$select = NULL;

		$select = $this->dbAdapter->select();
		$select->from(	array('im' => Image::TABLE_NAME));
		$select->join(	array('ceHIm' => CeHasImage::TABLE_NAME),
						'im.'.Image::COL_ID.'='.'ceHIm.'.CeHasImage::COL_IMAGE_ID);
		$select->join(	array('ce' => CalibrationExercise::TABLE_NAME),
						'ce.'.CalibrationExercise::COL_ID.'='.'ceHIm.'.CeHasImage::COL_CALIBRATION_EXERCISE_ID);
		$select->join(	array('key' => KeyTable::TABLE_NAME),
						'ce.'.CalibrationExercise::COL_KEY_TABLE_ID.'='.'key.'.KeyTable::COL_ID);
		$select->join(	array('exp' => Expertise::TABLE_NAME),
						'ce.'.CalibrationExercise::COL_EXPERTISE_ID.'='.'exp.'.Expertise::COL_ID);
		$select->join(	array('anno' => Annotations::TABLE_NAME),
						'anno.'.Annotations::COL_CE_HAS_IMAGE_ID.'='.'ceHIm.'.CeHasImage::COL_ID);
		//where exp = $expId AND (anno.ws = 1 OR anno.webgr = 1)
		$select->where('exp.'.Expertise::COL_ID.' = ?', $expId, 'int');
		$select->where('('.'anno.'.Annotations::COL_WEBGR_REF.' = ?', 1, 'int');
		$select->orWhere('anno.'.Annotations::COL_WS_REF.' = ?'.')', 1, 'int');

		return $select;
	}
	
	public function getImagesForCe($ceId)
	{
		$select = $this->dbAdapter->select();
		$select->from(    array('im' => Image::TABLE_NAME));
        $select->join(  array('ceHIm' => CeHasImage::TABLE_NAME),
                        'im.'.Image::COL_ID.'='.'ceHIm.'.CeHasImage::COL_IMAGE_ID);
        $select->join(  array('ce' => CalibrationExercise::TABLE_NAME),
                        'ce.'.CalibrationExercise::COL_ID.'='.'ceHIm.'.CeHasImage::COL_CALIBRATION_EXERCISE_ID);
        $select->where('ceHIm.'.CeHasImage::COL_CALIBRATION_EXERCISE_ID.'= ?', $ceId, 'int');
        $result = $this->dbAdapter->fetchAll($select);
        return $result;
	}
	
	public function getCeHasImageFilenameArray($dbResult)
	{
		$resultCount = count($dbResult);
		if ($resultCount > 0) {
			foreach ($dbResult as $row) {
				$ceHasImageToFilename[$row[CeHasImage::COL_ID]] = $row[Image::COL_ORIGINAL_FILENAME];
			}
            return $ceHasImageToFilename;
		} else {
			return FALSE;
		}
	}

	//get protocolID->images array
	public function getKeyImagesArray($select) {
		$rowset = $this->dbAdapter->fetchAll($select);
		$rowCount = count($rowset);

		if ($rowCount > 0) {
			$keyToImages = array();
			foreach ($rowset as $row) {
				//create array webGRkeyId->array(imageIds)
				$keyToImages[$row[KeyTable::COL_ID]][] = $row[Image::COL_ID];
			}
			//delete dpulicate images from multiple references
			foreach ($keyToImages as $key) {
				array_unique($key);
			}
			return $keyToImages;
		}
		else {
			return NULL;
		}
	}

	//get images for this expertise and this protocol
	public function getImages($expId, $keyId) {
		$select = $this->dbAdapter->select();

		$select->from('v_all_annotations');
		$select->where('v_all_annotations.'.CalibrationExercise::COL_EXPERTISE_ID.' = ?', $expId, 'int');
		$select->where('v_all_annotations.'.CalibrationExercise::COL_KEY_TABLE_ID.' = ?', $keyId, 'int');
		$select->where('('.'v_all_annotations.'.Annotations::COL_WEBGR_REF.' = ?', 1, 'int');
		$select->orWhere('v_all_annotations.'.Annotations::COL_WS_REF.' = ?'.')', 1, 'int');
		$select->group('v_all_annotations.'.Image::COL_ID);
		$rowset = $this->dbAdapter->fetchAll($select);
		$rowCount = count($rowset);

		if ($rowCount > 0) {
			$images = array();
			foreach ($rowset as $row) {
				//create array(imageIds)
				$images[] = $row[Image::COL_ID];
			}
			return $images;
		} else {
			return NULL;
		}
	}
	
	/**
	 * get all image filenames for fish sample code
	 * @param $fishSampleCode
	 * @return unknown_type
	 */
	public function getImageNames($fishSampleCode) {
		$select = $this->dbAdapter->select();
		$select->from(array('fish' => Fish::TABLE_NAME));
		$select->join(array('image' => Image::TABLE_NAME),
		              'fish.' . Fish::COL_ID . ' = ' . 'image.' . Image::COL_FISH_ID);
		$select->where('fish.' . Fish::COL_SAMPLE_CODE . '= ?', $fishSampleCode, 'string');
		$rowset = $this->dbAdapter->fetchAll($select);
	    $rowCount = count($rowset);

        if ($rowCount > 0) {
            $images = array();
            foreach ($rowset as $row) {
                //create array(imageIds)
                $images[] = $row[Image::COL_ORIGINAL_FILENAME];
            }
            return $images;
        } else {
            return NULL;
        }
	}
}
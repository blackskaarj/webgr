<?php
class Default_SimpleQuery {
	public static function isValueInTableColumn($value, $zendTable, $column, $sqlDataType = 'int')
	{
		$tableAdapter = $zendTable->getAdapter();
		$select = $zendTable->select();
		$select->from($zendTable);
		$column = $tableAdapter->quoteIdentifier($column);
		$partStatement = $tableAdapter->quoteInto($column.' = ?', $value, $sqlDataType);
		$select->where($partStatement);
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

	public static function isCeStopped($ceId)
	{
		$ce = new CalibrationExercise();
		$rowset = $ce->find($ceId);
		if (count($rowset) == 1) {
			if ($rowset[0][CalibrationExercise::COL_IS_STOPPED] == 0) {
				return FALSE;
			} else {
				return TRUE;
			}
		} else {
			throw new Zend_Exception ('Calibration exercise not found');
		}
	}
	
	public static function isCeCompletelyDefined($ceId)
	{
	$ce = new CalibrationExercise();
        $rowset = $ce->find($ceId);
        if (count($rowset) == 1) {
            if ((! is_null($rowset[0][CalibrationExercise::COL_EXPERTISE_ID]))
                &&  (! is_null($rowset[0][CalibrationExercise::COL_KEY_TABLE_ID]))) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            throw new Zend_Exception ('Calibration exercise not found');
        }
	}

	public static function getCeName($ceId)
	{
		$ce = new CalibrationExercise();
		$rowset = $ce->find($ceId);
		if (count($rowset) == 1) {
			return $rowset[0][CalibrationExercise::COL_NAME];
		} else {
			throw new Zend_Exception ('Calibration exercise not found');
		}
	}

	public static function getAttributeName($atDeId)
	{
		$atDe = new AttributeDescriptor();
		$rowset = $atDe->find($atDeId);
		if (count($rowset) == 1) {
			return $rowset[0][AttributeDescriptor::COL_NAME];
		} else {
			throw new Zend_Exception ('Attribute descriptor not found');
		}
	}

	public static function getWsManagerUserId($wsId)
	{
		//WS can be NULL
		if ($wsId == NULL) {
			return FALSE;
		}
		$ws = new Workshop();
		$rowset = $ws->find($wsId);
		if (count($rowset) == 1) {
			return $rowset[0][Workshop::COL_USER_ID];
		} else {
			throw new Zend_Exception ('Workshop not found');
		}
	}


	public static function getWorkshopId($ceId)
	{
		$ce = new CalibrationExercise();
		$rowset = $ce->find($ceId);
		if (count($rowset) == 1) {
			return $rowset[0][CalibrationExercise::COL_WORKSHOP_ID];
		} else {
			return FALSE;
		}
	}

	public static function getValuesFromTableColumnWhere($zendTable, $fromColumn, $whereColumn, $notUniqueId, $sqlDataType = 'int')
	{
		$tableAdapter = $zendTable->getAdapter();
		$select = $zendTable->select();
		$select->from($zendTable, array($fromColumn));
		$whereColumn = $tableAdapter->quoteIdentifier($whereColumn);
		$partStatement = $tableAdapter->quoteInto($whereColumn.' = ?', $notUniqueId, $sqlDataType);
		$select->where($partStatement);
		$rowset = $zendTable->fetchAll($select);
		if (count($rowset) >= 1) {
			$rowsetArray = $rowset->toArray();
			$values = array();
			foreach ($rowsetArray as $rowArray) {
				foreach ($rowArray as $column => $value) {
					$values[] = $value;
				}
			}
			return $values;
		} else {
			return FALSE;
		}
	}
}
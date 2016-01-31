<?php
/**
 * Beschreibung:
 * MetaData provides dynamic zend select objects for image and fish searches
 * for general and filtered search
 *
 * @name       Datei: MetaData.php
 * @abstract   s.a.
 * @author     Ingmar Pforr (ip) <ip@zadi.de>, Norman Rauthe (nr) <nr@zadi.de>
 * @copyright  Copyright (c) 2009, BLE, Ref. 422, Norman Rauthe (nr)
 * @version    Version vom 30.10.2009
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

class Default_MetaData {

	public $fishRowSetArray;
	public $imageRowSetArray;
	public $aliasArray;
	private $dbAdapter;
	public $select;


	public function __construct($dbAdaptername = 'DB_CONNECTION1')
	{
		$this->dbAdapter = Zend_Registry::get($dbAdaptername);
	}

	public function getSelectForGroups($searchImage = TRUE) {
		$this->select = $this->dbAdapter->select();
		$this->select->from(array('fish' => Fish::TABLE_NAME));
		if ($searchImage) {
			$this->select->join(array('image' => Image::TABLE_NAME),
                                'image.'.Image::COL_FISH_ID.'='.'fish.'.Fish::COL_ID,
			array(  Image::COL_CHECKSUM,
			Image::COL_DIM_X,
			Image::COL_DIM_Y,
			Image::COL_GUID,
			Image::COL_ID,
			Image::COL_ORIGINAL_CHECKSUM,
			Image::COL_ORIGINAL_FILENAME,
			Image::COL_USER_ID));
		} else {
			//left join for fish search (finds fishes without image too)
			$this->select->joinLeft(array('image' => Image::TABLE_NAME),
		                        'image.'.Image::COL_FISH_ID.'='.'fish.'.Fish::COL_ID,
			array(	Image::COL_CHECKSUM,
			Image::COL_DIM_X,
			Image::COL_DIM_Y,
			Image::COL_GUID,
			Image::COL_ID,
			Image::COL_ORIGINAL_CHECKSUM,
			Image::COL_ORIGINAL_FILENAME,
			Image::COL_USER_ID));
		}

		//for each meta data save the alias to use it in where clause for searching
		$this->aliasArray = array();

		$this->fishRowSetArray = $this->getAttributesBasic('fish');

		//for each *fish* metadata in system ask meta data name and open (meta data value) /closed(value list value) value
		$i = 0;
		foreach ($this->fishRowSetArray as $fishAttr) {
			//--------leftJoin because some attribute values are free values and have no value list id
			//
			$subSelect = $this->dbAdapter->select();
			$subSelect->from(MetaDataFish::TABLE_NAME);
			$subSelect->where(MetaDataFish::COL_ATTRIBUTE_DESCRIPTOR_ID.' = ?', $fishAttr[AttributeDescriptor::COL_ID], 'int');
			//			$expr = new Zend_Db_Expr($subSelect);
			//			$strSubSelect = $subSelect->__toString();

			if ($fishAttr[AttributeDescriptor::COL_VALUE_LIST] == '1') {
				$this->select->joinLeft(array('fi_meta_'.$i => $subSelect),
                                        'fish.'.Fish::COL_ID.' = '.'fi_meta_'.$i.'.'.MetaDataFish::COL_FISH_ID,
				array());

				//				$this->select->joinLeft(array('fi_meta_'.$i => MetaDataFish::TABLE_NAME),
				//                                        'image.'.Image::COL_FISH_ID.' = '.'fi_meta_'.$i.'.'.MetaDataFish::COL_FISH_ID,
				//				array());

				$this->select->joinLeft(array('fi_vali_'.$i => ValueList::TABLE_NAME),
                                        'fi_meta_'.$i.'.'.MetaDataFish::COL_VALUE.'='.'fi_vali_'.$i.'.'.ValueList::COL_ID,
				array('ATDE_'.$fishAttr[AttributeDescriptor::COL_ID] => 'fi_vali_'.$i.'.'.ValueList::COL_NAME));
			} else {
				$this->select->joinLeft(array('fi_meta_'.$i => $subSelect),
                                        'fish.'.Fish::COL_ID.' = '.'fi_meta_'.$i.'.'.MetaDataFish::COL_FISH_ID,
				array('ATDE_'.$fishAttr[AttributeDescriptor::COL_ID] => 'fi_meta_'.$i.'.'.MetaDataFish::COL_VALUE));
			}
				
			//				$this->select->joinLeft(array('fi_meta_'.$i => MetaDataFish::TABLE_NAME),
			//                                        'image.'.Image::COL_FISH_ID.' = '.'fi_meta_'.$i.'.'.MetaDataFish::COL_FISH_ID,
			//				array('ATDE_'.$fishAttr[AttributeDescriptor::COL_ID] => 'fi_meta_'.$i.'.'.MetaDataFish::COL_VALUE));
			//			}

			$this->aliasArray = $this->aliasArray + array($fishAttr[AttributeDescriptor::COL_ID] => 'fi_meta_'.$i.'.'.MetaDataFish::COL_VALUE);
			//where clause is in subselect now
			//$this->select->where('fi_meta_'.$i.'.'.MetaDataFish::COL_ATTRIBUTE_DESCRIPTOR_ID.' = ?', $fishAttr[AttributeDescriptor::COL_ID]);
			$i++;
		}
		unset($i);
		unset($subSelect);

		$this->imageRowSetArray = $this->getAttributesBasic('image');

		//for each *image* metadata in system ask meta data name and open (meta data value) /closed(value list value) value
		$i = 0;
		foreach ($this->imageRowSetArray as $imAttr) {
			//
			$subSelect = $this->dbAdapter->select();
			$subSelect->from(MetaDataImage::TABLE_NAME);
			$subSelect->where(MetaDataImage::COL_ATTRIBUTE_DESCRIPTOR_ID.' = ?', $imAttr[AttributeDescriptor::COL_ID], 'int');

			if ($imAttr[AttributeDescriptor::COL_VALUE_LIST] == '1') {
				//closed value: look up value in table value_list
				$this->select->joinLeft(array('im_meta_'.$i => $subSelect),
                                        'image.'.Image::COL_ID.' = '.'im_meta_'.$i.'.'.MetaDataImage::COL_IMAGE_ID,
				array());
				$this->select->joinLeft(array('im_vali_'.$i => ValueList::TABLE_NAME),
                                        'im_meta_'.$i.'.'.MetaDataImage::COL_VALUE.'='.'im_vali_'.$i.'.'.ValueList::COL_ID,
				array('ATDE_'.$imAttr[AttributeDescriptor::COL_ID] => 'im_vali_'.$i.'.'.ValueList::COL_NAME));
			} else {
				//open value: select value from table attribute_descriptor
				$this->select->joinLeft(array('im_meta_'.$i => $subSelect),
                                        'image.'.Image::COL_ID.' = '.'im_meta_'.$i.'.'.MetaDataImage::COL_IMAGE_ID,
				array('ATDE_'.$imAttr[AttributeDescriptor::COL_ID] => 'im_meta_'.$i.'.'.MetaDataImage::COL_VALUE));
			}
			$this->aliasArray = $this->aliasArray + array($imAttr[AttributeDescriptor::COL_ID] => 'im_meta_'.$i.'.'.MetaDataImage::COL_VALUE);
			//where clause is in subselect now
			//$this->select->where('im_meta_'.$i.'.'.MetaDataImage::COL_ATTRIBUTE_DESCRIPTOR_ID.' = ?', $imAttr[AttributeDescriptor::COL_ID]);
			$i++;
		}
		unset($i);
		unset($subSelect);

		return $this->select;
	}

	//gets only name, unit etc. from attributes
	//should be named getAttributesMinimum() or so
	public function getAttributesBasic($atGroup) {
		//read the available ACTIVE attributes from attribute descriptor AND given group
		//order attributes by sequence no.
		//returns rowset - basic information about the available attributes
		$select = $this->dbAdapter->select();
		$select->from(AttributeDescriptor::TABLE_NAME, array( AttributeDescriptor::COL_ID,
		AttributeDescriptor::COL_NAME,
		AttributeDescriptor::COL_VALUE_LIST,
		AttributeDescriptor::COL_UNIT));
		$select->joinLeft(  ValueList::TABLE_NAME,
		AttributeDescriptor::TABLE_NAME . '.' . AttributeDescriptor::COL_UNIT . '='. ValueList::TABLE_NAME . '.' . ValueList::COL_ID,
		array('UNIT'=>ValueList::COL_VALUE));
		$select->where(AttributeDescriptor::COL_GROUP.' = ?', $atGroup)
		->where(AttributeDescriptor::COL_ACTIVE.' = ?', 1);
		$select->order(AttributeDescriptor::COL_SEQUENCE);
		$resultArray = $this->dbAdapter->fetchAll($select);
		return $resultArray;
	}

	//gets complete information from attributes
	public function getAttributesComplete($atGroup, $orderBy = NULL) {
		//read the available ACTIVE attributes from attribute descriptor AND given group
		//order attributes by sequence no.
		//return complete information about the available attributes
		$select = $this->dbAdapter->select();
		$select->from(AttributeDescriptor::TABLE_NAME);
		$select->joinLeft(  ValueList::TABLE_NAME,
		AttributeDescriptor::TABLE_NAME . '.' . AttributeDescriptor::COL_UNIT . '='. ValueList::TABLE_NAME . '.' . ValueList::COL_ID,
		array('UNIT'=>ValueList::COL_VALUE));
		$select->where(AttributeDescriptor::COL_GROUP.' = ?', $atGroup)
		->where(AttributeDescriptor::COL_ACTIVE.' = ?', 1);
		if (isset($orderBy)) {
			$select->order($orderBy);
		} else {
			$select->order(AttributeDescriptor::COL_SEQUENCE, AttributeDescriptor::COL_GROUP);
		}
		$resultArray = $this->dbAdapter->fetchAll($select);
		return $resultArray;
	}

	public function getAttributesAndValuelist($atGroup) {
		//read the available ACTIVE attributes from attribute descriptor AND given group
		//order attributes by sequence no.
		//returns rowset - basic information about the available attributes
		$select = $this->dbAdapter->select();
		$select->from(AttributeDescriptor::TABLE_NAME, array( AttributeDescriptor::COL_ID,
		AttributeDescriptor::COL_NAME,
		AttributeDescriptor::COL_VALUE_LIST,
		AttributeDescriptor::COL_UNIT));
		$select->joinLeft(  ValueList::TABLE_NAME,
		AttributeDescriptor::TABLE_NAME . '.' . AttributeDescriptor::COL_ID . '='. ValueList::TABLE_NAME . '.' . ValueList::COL_ATTRIBUTE_DESCRIPTOR_ID,
		array(	ValueList::COL_ID,
		ValueList::COL_VALUE));
		$select->where(AttributeDescriptor::COL_GROUP.' = ?', $atGroup)
		->where(AttributeDescriptor::COL_ACTIVE.' = ?', 1)
		->where(AttributeDescriptor::COL_VALUE_LIST.' = ?', 1);
		$select->order(AttributeDescriptor::COL_SEQUENCE);
		$resultArray = $this->dbAdapter->fetchAll($select);
		return $resultArray;
	}

	public function addWhereToSelect($formValues)
	{
		//$this->select = $this->getSelectForGroups();
		$aliasArray = $this->aliasArray;

		//handle AND/OR search
		if ($formValues['kind'] == 'and') {
			foreach ($formValues as $key => $value) {
				if ($this->formKeyHasValue($key, $value)) {
					//search for data sets with NULL values - e.g. old data sets before introduction of new attributes - isn't possible at the moment
					//process possible meta data attributes
					if (substr_compare($key, 'ATDE_', 0, 4, TRUE) == 0) {
						//cut off ATDE_ to get only ID for querying in table
						$keyAtDeId = substr($key, 5);
						foreach ($aliasArray as $atDeId => $aliasTableAndColumn) {
							if ($keyAtDeId == $atDeId) {

								//Boolean Expressions: Int=0=>FALSE , Int=1=>TRUE
								$atDeTable = new AttributeDescriptor();
								$rowset = $atDeTable->find($atDeId);
								if (count($rowset)==1) {
									$rowsetArray = $rowset->toArray();
									$atDe = $rowsetArray[0];
									if ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'select') {
										//OLD
										//$partStatement = $tableAdapter->quoteInto($aliasTableAndColumn.' = ?', $value);

										//NEW
										//-------------------------------------------------------------
										//NOTE: standard zend element checkbox sets value for checked=1 AND unchecked=0,
										//      checkbox is always submitted
										//      standard zend element multicheckbox sets no value for unchecked
										//      multicheckbox without checked boxes is not submitted

										$partStatement = '';
										//handle last item differently
										//credit:grobemo
										//24-Apr-2009 08:13
										//http://de3.php.net/manual/en/control-structures.foreach.php
										$last_item = end($value);
										foreach ($value as $val) {
											if ($val == $last_item) {
												$partStatement = $partStatement.$this->dbAdapter->quoteInto($aliasTableAndColumn.' = ?', $val);
											}
											else {
												$partStatement = $partStatement.$this->dbAdapter->quoteInto($aliasTableAndColumn.' = ? OR ', $val);
											}
										}
										//-------------------------------------------------------------
									}

									elseif ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'text') {
										if ($atDe[AttributeDescriptor::COL_DATA_TYPE] == 'integer' ||
										$atDe[AttributeDescriptor::COL_DATA_TYPE] == 'decimal' ||
										$atDe[AttributeDescriptor::COL_DATA_TYPE] == 'date' ||
										$atDe[AttributeDescriptor::COL_DATA_TYPE] == 'time' ||
										$atDe[AttributeDescriptor::COL_DATA_TYPE] == 'datetime') {

											switch ($atDe[AttributeDescriptor::COL_DATA_TYPE]) {
												case 'integer':
													$sqlDatatype = 'int';
													break;
												case 'decimal':
													$sqlDatatype = 'dec';
													break;
													//TODO handle other datatypes
											}

											if ($value['fromValue'] == NULL || $value['toValue'] == NULL ) {
												//FROM or TO value empty
												$atDeName = $atDe[AttributeDescriptor::COL_NAME];
												echo "Info: FROM or TO value empty, $atDeName not used<br>";
												$keyProcessed = TRUE;
												break;
											} else {
												//$partStatement = $aliasTableAndColumn.' >= '.$value['fromValue'].' AND ';
												//$partStatement = $partStatement.$aliasTableAndColumn.' <= '.$value['toValue'];
												$partStatement = $this->dbAdapter->quoteInto($aliasTableAndColumn.' >= ? AND ', $value['fromValue'], $sqlDatatype);
												$partStatement = $partStatement.$this->dbAdapter->quoteInto($aliasTableAndColumn.' <= ?', $value['toValue'], $sqlDatatype);
												unset($sqlDatatype);
											}
										}
										elseif ($atDe[AttributeDescriptor::COL_DATA_TYPE] == 'string') {
											$partStatement = $this->dbAdapter->quoteInto($aliasTableAndColumn.' LIKE ?', '%'.$value.'%');
										}
										else {
											throw new Zend_Exception("Error: processing search parameters");
										}
									}

									elseif ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'checkbox') {
										//NOTE: standard zend element checkbox sets value for checked=1 AND unchecked=0,
										//      checkbox is always submitted
										//      standard zend element multicheckbox sets no value for unchecked
										//      multicheckbox without checked boxes is not submitted
										//schema-definition specific
										if ($value == '1') {
											//checkbox is on
											$partStatement = $aliasTableAndColumn.' = 1';
										}
										elseif ($value == '0' || $value == NULL) {
											//checkbox is off
											//do nothing to handle 0 and NULL (off and not defined yet)
											//$partStatement = $aliasTableAndColumn.' = 0';
										}
										else {
											throw new Zend_Exception("Error: processing search parameters");
										}
									}

									elseif ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'textarea') {
										$partStatement = $this->dbAdapter->quoteInto($aliasTableAndColumn.' LIKE ?', '%'.$value.'%');
									}

									elseif ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'radio') {
										//OLD
										//$partStatement = $this->dbAdapter->quoteInto($aliasTableAndColumn.' = ?', $value);

										//NEW
										//-------------------------------------------------------------
										//NOTE: standard zend element checkbox sets value for checked=1 AND unchecked=0,
										//      checkbox is always submitted
										//      standard zend element multicheckbox sets no value for unchecked
										//      multicheckbox without checked boxes is not submitted

										$partStatement = '';
										//handle last item differently
										//credit:grobemo
										//24-Apr-2009 08:13
										//http://de3.php.net/manual/en/control-structures.foreach.php
										$last_item = end($value);
										foreach ($value as $val) {
											if ($val == $last_item) {
												$partStatement = $partStatement.$this->dbAdapter->quoteInto($aliasTableAndColumn.' = ?', $val);
											}
											else {
												$partStatement = $partStatement.$this->dbAdapter->quoteInto($aliasTableAndColumn.' = ? OR ', $val);
											}
										}
										//-------------------------------------------------------------
									}

									elseif ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'multiselect' ||
									$atDe[AttributeDescriptor::COL_FORM_TYPE] == 'multicheckbox') {
										//NOTE: standard zend element checkbox sets value for checked=1 AND unchecked=0,
										//      checkbox is always submitted
										//      standard zend element multicheckbox sets no value for unchecked
										//      multicheckbox without checked boxes is not submitted

										$partStatement = '';
										/*handle last item differently
										 * credit:grobemo
										 * 24-Apr-2009 08:13
										 * http://de3.php.net/manual/en/control-structures.foreach.php
										 */
										$last_item = end($value);
										foreach ($value as $val) {
											if ($val == $last_item) {
												$partStatement = $partStatement.$this->dbAdapter->quoteInto($aliasTableAndColumn.' = ?', $val);
											}
											else {
												$partStatement = $partStatement.$this->dbAdapter->quoteInto($aliasTableAndColumn.' = ? OR ', $val);
											}
										}
									}

									else {
										throw new Zend_Exception("Error: processing search parameters");
									}
									//finally append the where to the select(whole metadata)
									if (isset($partStatement)) {
										$this->select->where($partStatement);
									}
									unset($partStatement);

								} else {
									throw new Zend_Exception("Error: count(rowset) from attribute_desc where ATDE_ID = $atdeId is not 1");
								}



								//$partStatement = $this->dbAdapter->quoteInto($aliasTableAndColumn.' like ?', '%'.$value.'%');
								//$this->select->where($partStatement);

								//set to TRUE jumps to next key
								$keyProcessed = TRUE;
								break;
							} else {
								$keyProcessed = FALSE;
							}
						}
					} else {
						//no ATDE_ID attribute key
						$keyProcessed = FALSE;
					}

					//process direct attributes
					//only if key was not processed already
					if (!$keyProcessed) {
						//$tableRow = $this->dbAdapter->quoteIdentifier($key);
						$partStatement = $this->dbAdapter->quoteInto($key.' like ?', '%'.$value.'%');
						$this->select->where($partStatement);
					}
				}
				//end, process next key
			}
		}

		if ($formValues['kind'] == 'or') {
			//due to whole sql-statement
			//don't use select->orWhere() method, instead concatenate strings with OR
			//to reduce usage of brackets
			//and mixed usage of where / orWhere (first condition where, second and more conditions orWhere)
			$orWhere = '';
			foreach ($formValues as $key => $value) {
				if ($this->formKeyHasValue($key, $value)) {
					//search for data sets with NULL values - e.g. old data sets before introduction of new attributes - isn't possible at the moment
					//process possible meta data attributes
					if (substr_compare($key, 'ATDE_', 0, 4, TRUE) == 0) {
						$keyAtDeId = substr($key, 5);
						foreach ($aliasArray as $atDeId => $aliasTableAndColumn) {
							//cut off ATDE_ to get only ID for querying in table
							if ($keyAtDeId == $atDeId) {

								//Boolean Expressions: Int=0=>FALSE , Int=1=>TRUE
								$atDeTable = new AttributeDescriptor();
								$rowset = $atDeTable->find($atDeId);
								if (count($rowset)==1) {
									$rowsetArray = $rowset->toArray();
									$atDe = $rowsetArray[0];
									if ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'select') {
										//OLD
										//$partStatement = $this->dbAdapter->quoteInto($aliasTableAndColumn.' = ?', $value);

										//NEW
										//-------------------------------------------------------------
										//NOTE: standard zend element checkbox sets value for checked=1 AND unchecked=0,
										//      checkbox is always submitted
										//      standard zend element multicheckbox sets no value for unchecked
										//      multicheckbox without checked boxes is not submitted

										$partStatement = '';
										//handle last item differently
										//credit:grobemo
										//24-Apr-2009 08:13
										//http://de3.php.net/manual/en/control-structures.foreach.php
										$last_item = end($value);
										foreach ($value as $val) {
											if ($val == $last_item) {
												$partStatement = $partStatement.$this->dbAdapter->quoteInto($aliasTableAndColumn.' = ?', $val);
											}
											else {
												$partStatement = $partStatement.$this->dbAdapter->quoteInto($aliasTableAndColumn.' = ? OR ', $val);
											}
										}
										//-------------------------------------------------------------
									}

									elseif ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'text') {
										if ($atDe[AttributeDescriptor::COL_DATA_TYPE] == 'integer' ||
										$atDe[AttributeDescriptor::COL_DATA_TYPE] == 'decimal' ||
										$atDe[AttributeDescriptor::COL_DATA_TYPE] == 'date' ||
										$atDe[AttributeDescriptor::COL_DATA_TYPE] == 'time' ||
										$atDe[AttributeDescriptor::COL_DATA_TYPE] == 'datetime') {

											switch ($atDe[AttributeDescriptor::COL_DATA_TYPE]) {
												case 'integer':
													$sqlDatatype = 'int';
													break;
												case 'decimal':
													$sqlDatatype = 'dec';
													break;
													//TODO handle other datatypes
											}

											if ($value['fromValue'] == NULL || $value['toValue'] == NULL ) {
												//FROM or TO value empty
												$atDeName = $atDe[AttributeDescriptor::COL_NAME];
												echo "Info: FROM or TO value empty, $atDeName not used<br>";
												$keyProcessed = TRUE;
												break;
											} else {
												//$partStatement = $aliasTableAndColumn.' >= '.$value['fromValue'].' AND ';
												//$partStatement = $partStatement.$aliasTableAndColumn.' <= '.$value['toValue'];
												$partStatement = '(';
												$partStatement = $partStatement.$this->dbAdapter->quoteInto($aliasTableAndColumn.' >= ? AND ', $value['fromValue'], $sqlDatatype);
												$partStatement = $partStatement.$this->dbAdapter->quoteInto($aliasTableAndColumn.' <= ?)', $value['toValue'], $sqlDatatype);
											}
										}
										elseif ($atDe[AttributeDescriptor::COL_DATA_TYPE] == 'string') {
											$partStatement = $this->dbAdapter->quoteInto($aliasTableAndColumn.' LIKE ?', '%'.$value.'%');
										}
										else {
											throw new Zend_Exception("Error: processing search parameters");
										}
									}

									elseif ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'checkbox') {
										//NOTE: standard zend element checkbox sets value for checked=1 AND unchecked=0,
										//      checkbox is always submitted
										//      standard zend element multicheckbox sets no value for unchecked
										//      multicheckbox without checked boxes is not submitted
										//schema-definition specific
										if ($value == '1') {
											//checkbox is on
											$partStatement = $aliasTableAndColumn.' = 1';
										}
										elseif ($value == '0' || $value == NULL) {
											//checkbox is off
											//do nothing to handle 0 and NULL (off and not defined yet)
											//$partStatement = $aliasTableAndColumn.' = 0';
										}
										else {
											throw new Zend_Exception("Error: processing search parameters");
										}
									}

									elseif ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'textarea') {
										$partStatement = $this->dbAdapter->quoteInto($aliasTableAndColumn.' LIKE ?', '%'.$value.'%');
									}

									elseif ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'radio') {
										//OLD
										//$partStatement = $this->dbAdapter->quoteInto($aliasTableAndColumn.' = ?', $value);

										//NEW
										//-------------------------------------------------------------
										//NOTE: standard zend element checkbox sets value for checked=1 AND unchecked=0,
										//      checkbox is always submitted
										//      standard zend element multicheckbox sets no value for unchecked
										//      multicheckbox without checked boxes is not submitted

										$partStatement = '';
										//handle last item differently
										//credit:grobemo
										//24-Apr-2009 08:13
										//http://de3.php.net/manual/en/control-structures.foreach.php
										$last_item = end($value);
										foreach ($value as $val) {
											if ($val == $last_item) {
												$partStatement = $partStatement.$this->dbAdapter->quoteInto($aliasTableAndColumn.' = ?', $val);
											}
											else {
												$partStatement = $partStatement.$this->dbAdapter->quoteInto($aliasTableAndColumn.' = ? OR ', $val);
											}
										}
										//-------------------------------------------------------------
									}

									elseif ($atDe[AttributeDescriptor::COL_FORM_TYPE] == 'multiselect' ||
									$atDe[AttributeDescriptor::COL_FORM_TYPE] == 'multicheckbox') {
										//NOTE: standard zend element checkbox sets value for checked=1 AND unchecked=0,
										//      checkbox is always submitted
										//      standard zend element multicheckbox sets no value for unchecked
										//      multicheckbox without checked boxes is not submitted

										$partStatement = '(';
										/*handle last item differently
										 * credit:grobemo
										 * 24-Apr-2009 08:13
										 * http://de3.php.net/manual/en/control-structures.foreach.php
										 */
										$last_item = end($value);
										foreach ($value as $val) {
											if ($val == $last_item) {
												$partStatement = $partStatement.$this->dbAdapter->quoteInto($aliasTableAndColumn.' = ?)', $val);
											}
											else {
												$partStatement = $partStatement.$this->dbAdapter->quoteInto($aliasTableAndColumn.' = ? OR ', $val);
											}
										}
									}

									else {
										throw new Zend_Exception("Error: processing search parameters");
									}
									//append the where to the "where or where" container
									if (isset($partStatement)) {
										if ($orWhere == '') {
											$orWhere = $partStatement;
										} else {
											$orWhere = $orWhere.' OR '.$partStatement;
										}
									}
									unset($partStatement);

								} else {
									throw new Zend_Exception("Error: count(rowset) from attribute_desc where ATDE_ID = $atdeId is not 1");
								}



								//$partStatement = $this->dbAdapter->quoteInto($aliasTableAndColumn.' like ?', '%'.$value.'%');
								//$this->select->where($partStatement);

								//set to TRUE jumps to next key
								$keyProcessed = TRUE;
								break;
							} else {
								$keyProcessed = FALSE;
							}
						}
					} else {
						//no ATDE_ID attribute key
						$keyProcessed = FALSE;
					}

					//process direct attributes
					//only if key was not processed already
					if (!$keyProcessed) {
						//$tableRow = $this->dbAdapter->quoteIdentifier($key);
						$partStatement = $this->dbAdapter->quoteInto($key.' like ?', '%'.$value.'%');
						//append the where to the "where or where" container
						if (isset($partStatement)) {
							if ($orWhere == '') {
								$orWhere = $partStatement;
							} else {
								$orWhere = $orWhere.' OR '.$partStatement;
							}
						}
						unset($partStatement);

					}

				}
				//end, process next key
			}
			//finally append the where to the select(whole metadata)
			if ($orWhere != '') {
				$this->select->where($orWhere);
			}
		}

		return $this->select;
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
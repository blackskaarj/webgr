<?php

class Service_Batch {

	private $key;
	private $matchingCols;
	private $preparedDatasets; //can contain datasets with uploaded files used in other datasets of import
	private $rowHasUniqueUploadedFile;
	private $userId;
	private $logger;
	private $myNamespace;

	const RELATIVE_PATH_UPLOAD_CACHE = '../application/cache/batchuploads/';
	const RELATIVE_PATH_IMPORT_LOGS = '../public/import_logs/';

	public function __construct($log = true)
	{
		$this->userId = AuthQuery::getUserId();
	}

    public function reAuthentification()
    {
        /**
         * this is just a dummy function where the upload.swf 
         * can renew his authentification during the upload proicess
         */;
    }
	
	public function getSecurityKey()
	{
		return sha1(Zend_Registry::get("SECURITY_KEY"));
	}

	public function getAttributes($key)
	{
		$this->key = $key;
		try {
			$parser = new Ble422_CsvParser(self::RELATIVE_PATH_UPLOAD_CACHE.$this->key.'/import.csv', TRUE, ',', NULL, TRUE);
		}
		catch(Exception $e) {
			return array(	'returnCode' => 'initial error',
							'message' => $e->getMessage);
		}

		$headings = $parser->getHeadings();
		if (count($headings) <= 1) {
			return array(    'returnCode' => 'initial error',
                            'message' => 'Error: zero or only one heading found, wrong delitimer?');
		}
		$datasets = array();
		$datasets = $parser->get(0);
		if (empty($datasets)) {
			return array(	'returnCode' => 'initial error',
							'message' => 'Error: no dataset found');
		}

		//add attributes from table fish and image (not defined over attribute system) manually 
		$fishBaseAttr = array(	array(AttributeDescriptor::COL_NAME => Fish::COL_SAMPLE_CODE,
		                              AttributeDescriptor::COL_REQUIRED => TRUE));
		$imageBaseAttr = array( array(AttributeDescriptor::COL_NAME => Image::COL_ORIGINAL_FILENAME,
		                              AttributeDescriptor::COL_REQUIRED => TRUE),
		                        array(AttributeDescriptor::COL_NAME => Image::COL_RATIO_EXTERNAL,
		                              AttributeDescriptor::COL_REQUIRED => TRUE));
		
		$meta = new Default_MetaData();
		$attribRowset = array();
		$attribRowset[0] = array(AttributeDescriptor::COL_NAME => "--ignore--");
		foreach ($fishBaseAttr as $key => $val) {
			$attribRowset[] = $val;
		}
		$fishAttribs = $meta->getAttributesComplete('FISH', AttributeDescriptor::COL_NAME);
		foreach ($fishAttribs as $key => $val) {
			$attribRowset[] = $val;
		}
	    foreach ($imageBaseAttr as $key => $val) {
            $attribRowset[] = $val;
        }
		$imageAttribs = $meta->getAttributesComplete('IMAGE', AttributeDescriptor::COL_NAME);
		foreach ($imageAttribs as $key => $val) {
			$attribRowset[] = $val;
		}

		return array( 'returnCode' => 'success',
						'message' => 'success',
						'csvHeadings' => $headings,
						'systemAttributes' => $attribRowset);
	}

	/**
	 * grouping function
	 * checks the CSV file and prepares the datasets for import, and gives detailled arrays back for single steps for further processing and report
	 * 1. checks the CSV file column against uploaded files
	 * 2. checks the header
	 * 3. checks the columns with value list entries and changes from strings to ids
	 * 4. splits and checks the datasets against fish and image form
	 * @param $key the subdirectory for csv and image files
	 * @param $systemAttributes INPUT from flex assigning, elements order is 1:1 to csvHeadings
	 * @param $csvHeadings INPUT from flex assigning, elements order is 1:1 to systemAttributes
	 * @param $filenames filenames of files to upload, including import.csv
	 * @return unknown_type
	 */
	public function checkCsv($key, $systemAttributes, $csvHeadings, $filenames)
	{
		$this->key = $key;
		$this->logger = new Ble422_ArrayLogger(self::RELATIVE_PATH_IMPORT_LOGS.$this->key.'_import_log.txt');
		try {
			$parser = new Ble422_CsvParser(self::RELATIVE_PATH_UPLOAD_CACHE.$this->key.'/import.csv', TRUE, ',', NULL, TRUE);
		}
		catch(Exception $e) {
			return array(	'returnCode' => 'initial error',
							'message' => $e->getMessage);
		}

		//print_r($this->parser->getHeadings());
		$headings = $parser->getHeadings();
		if (count($headings) <= 1) {
			return array(    'returnCode' => 'initial error',
                            'message' => 'Error: zero or only one heading found, wrong delitimer?');
		}
		$datasets = array();
		$datasets = $parser->get(0);
		if (empty($datasets)) {
			return array(	'returnCode' => 'initial error',
							'message' => 'Error: no dataset found');
		}

		if (empty($filenames)) {
			return array(   'returnCode' => 'initial error',
                            'message' => 'Error: no filenames found');
		}
		//echo $this->datasets[0]['Über2'];
		//echo $this->datasets[1]['Über3'];

		if (count($systemAttributes) >= 1 && count($csvHeadings) >= 1) {
			$useManualAssociation = TRUE;
			if (count($systemAttributes) != count($csvHeadings)) {
				return array(	'returnCode' => 'initial error',
								'message' => 'Error: number of system attributes and CSV file headings not equal');
			}

			//delete the additional information from the attribute string
			foreach ($systemAttributes as &$attr) {
				$pos = strrpos($attr, '#');
				if ($pos === false) {
					// not found
				} else {
					//- 1 because space sign
					$attr = substr($attr, 0, $pos - 1);
				}
			}
			//$headingsArray = $this->createHeadingsArray();
			
			$datasets = $this->getDatasetsWithNewHeadings($datasets, $systemAttributes, $csvHeadings);
			$headings = $systemAttributes;
		} else {
			$useManualAssociation = FALSE;
		}

		//additional array (keys without spaces) for xml XXX has no recursive handling --------------
		$datasetsForXml = array();
		foreach ($datasets as $rowNo => $dataset) {
			$newDataset = array();
			foreach ($dataset as $keyName => $value) {
				$newKeyName = str_replace(' ', '_', $keyName);
				$newDataset += array($newKeyName => $value);
			}
			$datasetsForXml[$rowNo] = $newDataset;
		}
		//----------------------------------------------------------
		$this->logger->log(array('user' => AuthQuery::getUserName()));
		$this->logger->log(array('originalInput' => $datasets));

		//XXX handle association array added info
		try {
			if (! $useManualAssociation) {
				$fileNamesCsv = $this->extractColumn($datasets, Image::COL_ORIGINAL_FILENAME);
			} else {
				$fileNamesCsv = $this->extractColumn($datasets, Image::COL_ORIGINAL_FILENAME);
			}
		} catch (OutOfBoundsException $e) {
			return array(   'returnCode' => 'initial error',
                            'message' => "Error: column not found\n".$e->getMessage);
		}
			
		try {
			$this->extractColumn($datasets, Fish::COL_SAMPLE_CODE);
		} catch (OutOfBoundsException $e) {
			return array(   'returnCode' => 'initial error',
                            'message' => "Error: column not found\n".$e->getMessage);
		}

		$resultsCheckFileCsvAssoc = $this->checkFileCsvAssoc($fileNamesCsv, $filenames);
		$this->logger->log($resultsCheckFileCsvAssoc, 'resultsCheckFileCsvAssoc');
		$this->rowHasUniqueUploadedFile = $resultsCheckFileCsvAssoc['rowHasUniqueUploadedFile'];

		$fishBaseAttr = array(	Fish::COL_SAMPLE_CODE);
		$imageBaseAttr = array( Image::COL_ORIGINAL_FILENAME,
		                        Image::COL_RATIO_EXTERNAL);
		$meta = new Default_MetaData();
		$attribRowset = array_merge($fishBaseAttr,
		$meta->getAttributesBasic('FISH'),
		$imageBaseAttr,
		$meta->getAttributesBasic('IMAGE'));

		$resultsCheckHeader = $this->checkHeader($headings, $attribRowset);
		$this->logger->log($resultsCheckHeader, 'resultsCheckHeader');

		//print_r($resultsCheckHeader);

		$this->matchingCols = $resultsCheckHeader['matchingColumns'];

		//get matching columns which have value list attributes
		$matchingColsWithValuelist = $resultsCheckHeader['matchingColumns'];
		foreach ($matchingColsWithValuelist as $key => $match) {
			if (is_array($match)) {
				//meta data
				if ($match[AttributeDescriptor::COL_VALUE_LIST] == 0) {
					unset($matchingColsWithValuelist[$key]);
				}
			} else {
				//base date
				unset($matchingColsWithValuelist[$key]);
			}
		}
		$attribRowsetWithValuelist = array_merge(	$meta->getAttributesAndValuelist('FISH'),
		$meta->getAttributesAndValuelist('IMAGE'));
		$resultsCheckValuelistCells = $this->checkValuelistCells($datasets, $matchingColsWithValuelist, $attribRowsetWithValuelist);
		$this->logger->log($resultsCheckValuelistCells, 'resultsCheckValuelistCells');


		//		//additional array (keys without spaces) for xml --------------
		//		//copy the array
		//		$resultsCheckValuelistCellsForXml = array();
		//		foreach ($resultsCheckValuelistCells as $key => $val) {
		//			$resultsCheckValuelistCellsForXml[$key] = $val;
		//		}
		//		//replace the subarray 'modifiedDatasets'
		//				foreach ($resultsCheckValuelistCells['modifiedDatasets'] as $rowNo => $attrValPair) {
		//					$newDataset = array();
		//					foreach ($attrValPair as $keyName => $value) {
		//						$newKeyName = str_replace(' ', '_', $keyName);
		//						$newDataset += array($newKeyName => $value);
		//					}
		//					$resultsCheckValuelistCellsForXml['modifiedDatasets'][$rowNo] = $newDataset;
		//				}
		//
		//		//----------------------------------------------------------


		$modifiedDatasets = $resultsCheckValuelistCells['modifiedDatasets'];

		//function gets matching cols from member variable
		$resultsValidateAgainstForms = $this->validateAgainstForms($modifiedDatasets);
		$this->logger->log($resultsValidateAgainstForms, 'resultsValidateAgainstForms');

		//set member array preparedDatasets = all successful validated datasets
		$validRows = $resultsValidateAgainstForms['validRows'];
		$preparedDatasets = array();
		foreach ($validRows as $rowNo => $dataset) {
			$preparedDatasets[$rowNo]['fishFormDataset'] = $resultsValidateAgainstForms['fishFormDatasets'][$rowNo];
			$preparedDatasets[$rowNo]['imageFormDataset'] = $resultsValidateAgainstForms['imageFormDatasets'][$rowNo];
			//XXX append original filename out of original datasets
			//$preparedDatasets[$rowNo][Image::COL_ORIGINAL_FILENAME] = $datasets[$rowNo][Image::COL_ORIGINAL_FILENAME];
		}
		$this->preparedDatasets = $preparedDatasets;
		$this->logger->log($this->preparedDatasets, 'preparedDatasets');

		$resultsCheckDatasetsAgainstDatabase = $this->checkDatasetsAgainstDatabase($datasets);
		$this->logger->log($resultsCheckDatasetsAgainstDatabase, 'resultsCheckDatasetsAgainstDatabase');

		//aggregate returnCodes
		$returnCode = '';
		if ($resultsCheckFileCsvAssoc['returnCode'] == 'warning'
		|| $resultsCheckHeader['returnCode'] == 'warning'
		|| $resultsCheckValuelistCells['returnCode'] == 'warning'
		|| $resultsValidateAgainstForms['returnCode'] == 'warning'
		|| $resultsCheckDatasetsAgainstDatabase['returnCode'] == 'warning'
		) {
			$returnCode = 'warning';
		}
		if ($resultsCheckFileCsvAssoc['returnCode'] == 'error'
		|| $resultsCheckHeader['returnCode'] == 'error'
		|| $resultsCheckValuelistCells['returnCode'] == 'error'
		|| $resultsValidateAgainstForms['returnCode'] == 'error'
		|| $resultsCheckDatasetsAgainstDatabase['returnCode'] == 'error'
		) {
			$returnCode = 'error';
		}
		if ($returnCode == '') {
			$returnCode = 'success';
		}

		$resultsCheckFileCsvAssocForXml = $this->reduceCheckResultForXml($resultsCheckFileCsvAssoc);
		$resultsCheckHeaderForXml = $this->reduceCheckResultForXml($resultsCheckHeader);
		$resultsCheckValuelistCellsForXml = $this->reduceCheckResultForXml($resultsCheckValuelistCells);
		$resultsValidateAgainstFormsForXml = $this->reduceCheckResultForXml($resultsValidateAgainstForms);
		$resultsCheckDatasetsAgainstDatabaseForXml = $this->reduceCheckResultForXml($resultsCheckDatasetsAgainstDatabase);

		//		//give upload process the valid unique filenames
		//		$resultsCheckFileCsvAssocForXml['rowHasUniqueUploadedFile'] = $this->rowHasUniqueUploadedFile;

		$result = array('returnCode' => $returnCode,
						'originalInput' => $datasetsForXml,
						'resultsCheckFileCsvAssoc' => $resultsCheckFileCsvAssocForXml,
						'resultsCheckHeader' => $resultsCheckHeaderForXml,
						'resultsCheckValuelistCells' => $resultsCheckValuelistCellsForXml,
						'resultsValidateAgainstForms' => $resultsValidateAgainstFormsForXml,
		                'resultsCheckDatasetsAgainstDatabase' => $resultsCheckDatasetsAgainstDatabaseForXml
		);

		//		print_r($result);
		//		print_r($this->preparedDatasets);
		$this->saveToNamespace();
		return $result;
	}

	private function checkFileCsvAssoc($fileNamesCsv, $fileNamesToUpload) {
		//------------------------------------------------

		//------------------------------------------------
		$rowHasUniqueUploadedFile = array();
		$rowHasUploadedFile = array();
		$rowHasNoUploadedFile = array();
		$uploadedFiles = array();
		$duplicateFileNameRows = array();
		$labelCheck = 'File compatibility check';
		$message = '';

		$duplicates = Ble422_ArrayHelper::getDuplicates($fileNamesCsv, FALSE);
		foreach($duplicates as $elem) {
			$duplicateFileNameRows[] = array('row1' => $elem['key1'],
                                             'row2' => $elem['key2'],
                                             'filename' => $elem['value']);
			$message .= "duplicate filename \"" . $elem['value'] .
			"\" in row " . ($elem['key1'] + 1) .
			" and row " . ($elem['key2'] + 1) . "\n";
			$appendFurtherProcessingMessage = TRUE;
		}
		if ($appendFurtherProcessingMessage == TRUE) {
			$message .= "Further processing: All rows with duplicate filenames will be ignored.\n\n";
			unset($appendFurtherProcessingMessage);
		}

		/**
		 * changed to parameter filelist instead of read uploaded files
		 */
		//		$uploadedFiles = scandir(self::RELATIVE_PATH_UPLOAD_CACHE.$this->key);
		//		foreach ($uploadedFiles as $key => $file) {
		//			//ignore '.', '..', import and subversion files
		//			if ($file == '.' || $file == '..' || $file == 'import.csv' || $file == '.svn') {
		//				unset($uploadedFiles[$key]);
		//			}
		//		}

		//note: files were all uploaded before this point in the past version, now upload is after check
		$uploadedFiles = $fileNamesToUpload;

		foreach ($uploadedFiles as $key => $file) {
			//ignore '.', '..', import and subversion files
			if ($file == '.' || $file == '..' || $file == 'import.csv' || $file == '.svn') {
				unset($uploadedFiles[$key]);
			}
		}

		foreach ($fileNamesCsv as $row => $fileNameCsv) {
			//if no more uploaded files are available
			if (empty($uploadedFiles)) {
				$rowHasNoUploadedFile[$row] = $fileNameCsv;
				$message .= "Row " . ($row + 1) . " has no selected file with name \"$fileNameCsv\" to upload.\n";
				$appendFurtherProcessingMessage = TRUE;
				continue;
			}
			foreach ($uploadedFiles as $key => $file) {
				$matches = FALSE;
				//case non-sensitive
				if (strtolower($fileNameCsv) == strtolower($file)) {
					//first copy the element with associated position
					$rowHasUploadedFile[$row] = $file;
					$matches = TRUE;
					//second store the key of found element for later processing
					$matchingFilesKeys[$key] = TRUE;
					//break because file matches and the other files don't need to be searched
					break;
				}
			}
			//if no uploaded file matches
			if ($matches == FALSE) {
				$rowHasNoUploadedFile[$row] = $fileNameCsv;
				$message .= "No file with name \"$fileNameCsv\" found for row " . ($row + 1) . ".\n";
				$appendFurtherProcessingMessage = TRUE;
			}
		}
		if ($appendFurtherProcessingMessage == TRUE) {
			$message .= "Further processing: All rows with no found file will be ignored.\n\n";
			unset($appendFurtherProcessingMessage);
		}

		//create overloadUploadedFiles array for report
		$overloadUploadedFiles = $uploadedFiles;
		foreach ($matchingFilesKeys as $key => $value) {
			unset($overloadUploadedFiles[$key]);
		}
		unset($matchingFilesKeys);
		//array now contains files without reference = overload

		foreach($overloadUploadedFiles as $filename) {
			$message .= "File \"$filename\" is not referenced in import data.\n";
			$appendFurtherProcessingMessage = TRUE;
		}
		if ($appendFurtherProcessingMessage == TRUE) {
			$message .= "Further processing: All files not referenced in import data will be ignored.\n\n";
			unset($appendFurtherProcessingMessage);
		}

		//----------------------------------------------


		foreach ($rowHasUploadedFile as $key => $val) {
			$rowHasUniqueUploadedFile[$key] = $val;
		}

		//reads out the first and second row no of the double pairs
		//e.g. duplicate in 3 and 4, duplicate in 3 and 5
		//means row 3, row 4 and row 5 is not unique and gets unset
		foreach ($duplicateFileNameRows as $dupl) {
			unset($rowHasUniqueUploadedFile[$dupl['row1']]);
			unset($rowHasUniqueUploadedFile[$dupl['row2']]);
		}
		//-----------------------------------------------

		//compute if error
		if (count($overloadUploadedFiles) > 0) {
			$returnCode = 'warning';
		} elseif (count($rowHasNoUploadedFile) > 0 || count($duplicateFileNameRows) > 0) {
			$returnCode = 'warning';
		} else {
			$returnCode = 'success';
		}

		return array(	'labelCheck' => $labelCheck,
		                'returnCode' => $returnCode,
		                'message' => $message,  
		                'rowHasUniqueUploadedFile' => $rowHasUniqueUploadedFile,
						'duplicateFileNameRows' => $duplicateFileNameRows,
						'rowHasUploadedFile' => $rowHasUploadedFile,
						'rowHasNoUploadedFile' => $rowHasNoUploadedFile,
						'overloadUploadedFiles' => $overloadUploadedFiles
		);
			
	}

	private function checkHeader($headings, $attribRowset)
	{
		$matchingColumns = array();
		$overloadCsvColumns = array();
		$missingCsvColumns = array();
		$labelCheck = 'Header compatibility check';
		$message = '';

		foreach ($headings as $posHeading => $heading) {

			foreach ($attribRowset as &$attr) {
				$matches = FALSE;
				if (is_array($attr) && array_key_exists(AttributeDescriptor::COL_NAME, $attr)) {
					//handle meta attributes
					if ($heading == $attr[AttributeDescriptor::COL_NAME]) {
						//first copy the element with associated position
						$matchingColumns = $matchingColumns + array($posHeading => $attr);
						//second tag the attribute element for sorting untagged elements out later
						$attr = array_merge($attr, array('matches' => TRUE));
						$matches = TRUE;
						break;
					}
				} else {
					//handle base attributes
					if ($heading == $attr) {
						//first copy the element with associated position
						$matchingColumns = $matchingColumns + array($posHeading => $attr);
						//change simple variable to array to have similar structure to meta data
						$attr = array($attr => NULL);
						//second tag the attribute element for sorting untagged elements out later
						$attr = array_merge($attr, array('matches' => TRUE));
						$matches = TRUE;
						break;
					}

				}
			}
			//collect overload of columns with column position of heading in CSV file
			if ($matches == FALSE) {
				$overloadCsvColumns = $overloadCsvColumns + array($posHeading => $heading);
				$tool = new Ble422_Tool();
				//XXX use org. heading
				$spreadsheetPos = $tool->col2spreadsheetCol($posHeading);
				//XXX use org. heading
				$message .= "Column in CSV file with name \"$heading\", located in spread sheet column $spreadsheetPos, is not assigned.\n";
				$appendFurtherProcessingMessage = TRUE;
			}

		}
		if ($appendFurtherProcessingMessage == TRUE) {
			$message .= "Further processing: All columns not assigned will be ignored.\n\n";
			unset($appendFurtherProcessingMessage);
		}

		//collect database fields missing in CSV file
		foreach ($attribRowset as $attr) {
			if (! isset($attr['matches'])) {
				array_push($missingCsvColumns, $attr);
				$message .= "System attribute " . $attr[AttributeDescriptor::COL_NAME] . " can not be found in CSV file.\n";
				$appendFurtherProcessingMessage = TRUE;
			}
		}
		if ($appendFurtherProcessingMessage == TRUE) {
			$message .= "Further processing: All system attributes not found can not be imported.\nPlease see other check results!\n\n";
			unset($appendFurtherProcessingMessage);
		}

		if (count($overloadCsvColumns) > 0 || count($missingCsvColumns) > 0) {
			$returnCode = 'warning';
		} else {
			$returnCode = 'success';
		}

		return array(	'labelCheck' => $labelCheck,
		                'returnCode' => $returnCode,
		                'message' => $message,
						'matchingColumns' => $matchingColumns,
						'overloadCsvColumns' => $overloadCsvColumns,
						'missingCsvColumns' => $missingCsvColumns);
	}

	/**
	 *
	 * @param $datasets the parsed datasets from CSV file
	 * @param $cols only the columns with value list fields
	 * @param $attribRowsetWithValuelist the attribute rowset
	 * @return unknown_type
	 * only provided columns are processed
	 * empty cells are not handled, see $emptyDataIndex
	 * not modified data means input error: see array notModifiedDataIndex [columnIndex][rowIndex]->TRUE
	 */
	private function checkValuelistCells($datasets, $cols, $attribRowsetWithValuelist) {
		$emptyDataIndex = array();
		$modifiedDataIndex = array();
		$notModifiedDataIndex = array();
		$labelCheck = 'Check cell contents to allowed values (see value list)';
		$returnCode = '';
		$message = '';
		$returnError = NULL;
		$returnWarning = NULL;

		//create helper array (attr=> (valuelist value=> valuelist id))
		foreach ($attribRowsetWithValuelist as $attr) {
			$attrValueToId[$attr[AttributeDescriptor::COL_ID]][$attr[ValueList::COL_VALUE]] = $attr[ValueList::COL_ID];
		}

		//iterate rows
		foreach ($datasets as $keyDataset => &$dataset) {
			foreach ($cols as $keyColumn => $columnDescriptor) {
				if (empty($dataset[$columnDescriptor[AttributeDescriptor::COL_NAME]])) {
					$emptyDataIndex[$keyDataset][$keyColumn] = TRUE;
					$message .= "Warning: empty cell in row " . ($keyDataset + 1)
					. ", column " . (Ble422_Tool::col2SpreadsheetCol($keyColumn))
					//XXX use org. heading
					. ", for attribute \"" . $columnDescriptor[AttributeDescriptor::COL_NAME] . "\""
					. "\n";
					$returnWarning = TRUE;
					$appendFurtherProcessingMessage1 = TRUE;
				} else {
					//get valuelist id from valuelist value
					if (isset($attrValueToId[$columnDescriptor[AttributeDescriptor::COL_ID]][$dataset[$columnDescriptor[AttributeDescriptor::COL_NAME]]])) {
						$dataset[$columnDescriptor[AttributeDescriptor::COL_NAME]] = $attrValueToId[$columnDescriptor[AttributeDescriptor::COL_ID]][$dataset[$columnDescriptor[AttributeDescriptor::COL_NAME]]];
						$modifiedDataIndex[$keyDataset][$keyColumn] = TRUE;
					} else {
						$notModifiedDataIndex[$keyDataset][$keyColumn] = TRUE;
						$message .= "Error: invalid value \"" . $dataset[$columnDescriptor[AttributeDescriptor::COL_NAME]] . "\""
						. "in row " . ($keyDataset + 1)
                    //XXX use org. heading						
						. ", column " . (Ble422_Tool::col2SpreadsheetCol($keyColumn))
						. ", for attribute \"" . $columnDescriptor[AttributeDescriptor::COL_NAME] . "\""
						. "\n";
						$returnError = TRUE;
						$appendFurtherProcessingMessage2 = TRUE;
					}
				}
			}
		}
		if ($appendFurtherProcessingMessage1 == TRUE) {
			$message .= "Further processing: All rows with empty cells will be tested if they are optional or mandatory.\nPlease see other check results!\n\n";
			unset($appendFurtherProcessingMessage1);
		}
		if ($appendFurtherProcessingMessage2 == TRUE) {
			$message .= "Further processing: All rows with invalid cells will be ignored.\n\n";
			unset($appendFurtherProcessingMessage2);
		}

		//check the return flags and set return code
		if  ($returnWarning) $returnCode = 'warning';
		if  ($returnError) $returnCode = 'error';
		if  ( $returnWarning == FALSE
		      && $returnError == FALSE) $returnCode = 'success';

		$returnArray = array(	'labelCheck' => $labelCheck,
		                        'returnCode' => $returnCode,
		                        'message' => $message,
								'modifiedDatasets' => $datasets,
								'emptyDataIndex' => $emptyDataIndex,
								'modifiedDataIndex' => $modifiedDataIndex,
								'notModifiedDataIndex' => $notModifiedDataIndex
		);

		return $returnArray;
	}

	private function validateAgainstForms($datasets)
	{
		//Fish-------------------------------------------------------------------------
		$fishForm = new Fish_Form_Edit();
		$fishDynElems = $fishForm->getDynamicElements();
		$validFishRows = array();
		$notValidFishRows = array();
		$labelCheck = 'Check cell contents to defined formats';
		$message = '';

		//change form to be conform with input data format
		$fishForm->removeElement(Fish::COL_ID);
		$fishForm->removeElement('save');
		$fishForm->removeElement('Token');

		//get array(dynamic form element names => column names) to get single cells out of dataset
		$dynElementNameAttrName = array();
		foreach ($fishDynElems as $elementName) {
			$attribId = substr($elementName,5,strlen($elementName));
			foreach ($this->matchingCols as $col) {
				if (is_array($col)) {
					if ($attribId == $col[AttributeDescriptor::COL_ID]) {
						$dynElementNameAttrName += array($elementName => $col[AttributeDescriptor::COL_NAME]);
					}
				}
			}
		}

		//build array from base data and meta data to validate against form for each dataset
		$fishFormDatasets = array();
		foreach ($datasets as $keyDataset => $dataset) {
			$fishBaseData = array(	Fish::COL_SAMPLE_CODE => $dataset[Fish::COL_SAMPLE_CODE]);
			$fishMetaData = array();
			foreach ($fishDynElems as $elementName) {
				//if column is missing in CSV file, prepare form element with value NULL
				if (isset($dynElementNameAttrName[$elementName])
				&& isset($dataset[$dynElementNameAttrName[$elementName]])) {
					$fishMetaData[$elementName] = $dataset[$dynElementNameAttrName[$elementName]];
				} else {
					$fishMetaData[$elementName] = NULL;
				}
			}
			$data = array_merge($fishBaseData, $fishMetaData);


			if (is_array($data)) {
				if ($fishForm->isValid($data)) {
					$validFishRows[$keyDataset] = TRUE;
					$fishFormDatasets[$keyDataset] = $data;
				} else {
					$notValidFishRows[$keyDataset] = TRUE;
					$message .= "invalid fish dataset in row " . ($keyDataset + 1) . "\n";
					$appendFurtherProcessingMessage = TRUE;
				}
			} else {
				$notValidFishRows[$keyDataset] = TRUE;
				$message .= "invalid fish dataset in row " . ($keyDataset + 1) . "\n";
				$appendFurtherProcessingMessage = TRUE;
			}

			if ($appendFurtherProcessingMessage == TRUE) {
				//concatenate form validator message(s)
				$message .= "--Details: \n--" . Ble422_ArrayHelper::convertToString($fishForm->getMessages()) . "\n\n";
				unset($appendFurtherProcessingMessage);
			}
		}
		//-------------------------------------------------------------------------

		//Images------------------------------------------------------------
		$imageForm = new Image_Form_Edit();
		$imageDynElems = $imageForm->getDynamicElements();
		$validImageRows = array();
		$notValidImageRows = array();

		//change form to be conform with input data format
		$imageForm->removeElement(Image::COL_ID);
		$imageForm->removeElement(Image::COL_FISH_ID);
		//$imageForm->removeElement(Image::COL_ORIGINAL_FILENAME);
		$imageForm->removeElement('save');
		$imageForm->removeElement('Token');

		//get array(dynamic form element names => column names) to get single cells out of dataset
		$dynElementNameAttrName = array();
		foreach ($imageDynElems as $elementName) {
			$attribId = substr($elementName,5,strlen($elementName));
			foreach ($this->matchingCols as $col) {
				if (is_array($col)) {
					if ($attribId == $col[AttributeDescriptor::COL_ID]) {
						$dynElementNameAttrName += array($elementName => $col[AttributeDescriptor::COL_NAME]);
					}
				}
			}
		}

		//build array from base data and meta data to validate against form for each dataset
		$imageFormDatasets = array();
		foreach ($datasets as $keyDataset => $dataset) {
			//image base data
			$imageBaseData = array(  Image::COL_ORIGINAL_FILENAME => $dataset[Image::COL_ORIGINAL_FILENAME],
			                         Image::COL_RATIO_EXTERNAL => $dataset[Image::COL_RATIO_EXTERNAL]);
			$imageMetaData = array();
			foreach ($imageDynElems as $elementName) {
				//if column is missing in CSV file, prepare form element with value NULL
				if (isset($dynElementNameAttrName[$elementName])
				&& isset($dataset[$dynElementNameAttrName[$elementName]])) {
					$imageMetaData[$elementName] = $dataset[$dynElementNameAttrName[$elementName]];
				} else {
					$imageMetaData[$elementName] = NULL;
				}
			}
			$data = array_merge($imageBaseData, $imageMetaData);

			if (is_array($data)) {
				if ($imageForm->isValid($data)) {
					$validImageRows[$keyDataset] = TRUE;
					$imageFormDatasets[$keyDataset] = $data;
				} else {
					$notValidImageRows[$keyDataset] = TRUE;
					$message .= "invalid image dataset in row " . ($keyDataset + 1) . "\n";
					$appendFurtherProcessingMessage = TRUE;
				}
			} else {
				$notValidImageRows[$keyDataset] = TRUE;
				$message .= "invalid image dataset in row " . ($keyDataset + 1) . "\n";
				$appendFurtherProcessingMessage = TRUE;
			}
			if ($appendFurtherProcessingMessage == TRUE) {
				//concatenate form validator message(s)
				$message .= "--Details: \n--" . Ble422_ArrayHelper::convertToString($imageForm->getMessages()) . "\n\n";
				unset($appendFurtherProcessingMessage);
			}
		}

		$notValidRows = array();
		$notValidRows = array_merge($notValidFishRows, $notValidImageRows);

		//if validFishRows[rowNo] and validImageRows[rowNo] is set, the rowNo is completely valid
		$validRows = array();
		if (! empty($validFishRows) && ! empty($validImageRows)) {
			foreach ($validFishRows as $rowNo => $validFishRow) {
				if (isset($validImageRows[$rowNo])) {
					$validRows[$rowNo] = TRUE;
				}
			}
		}
		//$validRows = array_diff(array_merge($validFishRows, $validImageRows), $notValidRows);

		if (count($datasets) == count($validRows)) {
			$returnCode = 'success';
		} else {
			$returnCode = 'error';
		}

		if ($returnCode == 'error') {
			$message .= "All invalid rows will be ignored.\n\n";
		}

		return array(	'labelCheck' => $labelCheck,
		                'returnCode' => $returnCode,
		                'message' => $message,
						'validRows' => $validRows,
						'notValidRows' => $notValidRows,
						'validFishRows' => $validFishRows,
						'notValidFishRows' => $notValidFishRows,
						'validImageRows' => $validImageRows,
						'notValidImageRows' => $notValidImageRows,
						'fishFormDatasets' => $fishFormDatasets,
						'imageFormDatasets' => $imageFormDatasets);
	}

	private function checkDatasetsAgainstDatabase($datasets) {
		$filenames = array();
		$labelCheck = 'Check if fish/image is already existing in database';
		$returnCode = '';
		$message = '';

		foreach ($datasets as $rowNo => $dataset) {
			if (Default_SimpleQuery::isValueInTableColumn($dataset[Fish::COL_SAMPLE_CODE], new Fish(), Fish::COL_SAMPLE_CODE, 'string')) {
				$message .= "Note: Fish dataset in row " . ($rowNo + 1)
				. " with FISH_SAMPLE_CODE \"" . $dataset[Fish::COL_SAMPLE_CODE]
				. "\" is already existing.\n";
				$appendFurtherProcessingMessage = TRUE;
			}
		}

		if ($appendFurtherProcessingMessage == TRUE) {
			$message .= "Further processing: All existing fish datasets will be ignored.\n";
			unset($appendFurtherProcessingMessage);
		}

		foreach ($datasets as $rowNo => $dataset) {
			$qu = new Default_ReferenceQuery();
			$filenames = $qu->getImageNames($dataset[Fish::COL_SAMPLE_CODE]);
			//case non-sensitive
			foreach ($filenames as &$file) {
				$file = strtolower($file);
			}
			if (! empty($filenames)) {
				//case non-sensitive
				if (in_array(strtolower($dataset[Image::COL_ORIGINAL_FILENAME]), $filenames) != FALSE) {
					$message .= "Warning: Image in row " . ($rowNo + 1)
					. " with filename \"" . $dataset[Image::COL_ORIGINAL_FILENAME] . "\""
					. " assigned to fish sample code " . $dataset[Fish::COL_SAMPLE_CODE]
					. " is already in database.\n";
					$returnCode = 'warning';
					$appendFurtherProcessingMessage = TRUE;
				} else {
					$message .= "Note: Fish dataset in row " . ($rowNo + 1)
					. " with FISH_SAMPLE_CODE \"" . $dataset[Fish::COL_SAMPLE_CODE]
					. "\" has already 1 or more image(s) assigned with other filename(s).\n";
				}
			}
		}
		if ($appendFurtherProcessingMessage == TRUE) {
			/**
             * --------------------------------------------Message ändern !!!!!!!!!!!!!!!!!!!!!!!!!!!
			 */
			$message .= "Further processing: The image datasets which are already assigned to fishes, will not be imported.\n";
			unset($appendFurtherProcessingMessage);
		}

		if ($returnCode == '') {
			$returnCode = 'success';
		}

		return array ('labelCheck' => $labelCheck,
		              'returnCode' => $returnCode,
		              'message' => $message);
	}

	/**
	 *
	 * @param $key the subdirectory for csv and image files
	 * @return unknown_type importImages/importFishes arrays with the IDs as key to see where dataimport eventually went wrong
	 */
	public function beginImport($key) {
		$this->key = $key;
		$this->logger = new Ble422_ArrayLogger(self::RELATIVE_PATH_IMPORT_LOGS.$this->key.'_import_log.txt');
		$this->loadFromNamespace();

		//
		foreach ($this->rowHasUniqueUploadedFile as $key => $value) {
			$preparedDatasets[$key] = $this->preparedDatasets[$key];
		}
		//

		$importedFishes = array();
		$importedImages = array();
		$i = 0;
		$j = 0;

		$fishForm = new Fish_Form_Edit();
		$fish = new Fish();
		$imageForm = new Image_Form_Edit();
		$image = new Image();
		$numberCopiedFiles = 0;

		$dbAdapter = $fish->getAdapter();
		$dbAdapter->beginTransaction();
		try {
			//changed to local preparedDatasets to filter datasets with uploaded files used in other datasets of import
			foreach ($preparedDatasets as $rowNo => $dataset) {
				if (Default_SimpleQuery::isValueInTableColumn($dataset['fishFormDataset'][Fish::COL_SAMPLE_CODE], $fish, Fish::COL_SAMPLE_CODE, 'string')) {
					//get id from already existing fish dataset and go on
					if ($values = Default_SimpleQuery::getValuesFromTableColumnWhere($fish, Fish::COL_ID, Fish::COL_SAMPLE_CODE, $dataset['fishFormDataset'][Fish::COL_SAMPLE_CODE], 'string')) {
						//test for amount of values, must be 1
						if (count($values) == 1) {
							$fishId = $values[0];
						} else {
							throw new Zend_Exception('Error: more/less than 1 id found for fish sample code');
						}
					}
				} else {
					//create new fish dataset
					$fishBaseData = array(	Fish::COL_SAMPLE_CODE => $dataset['fishFormDataset'][Fish::COL_SAMPLE_CODE],
					Fish::COL_USER_ID => $this->userId);

					$fishMetaData = $dataset['fishFormDataset'];
					//unset($fishMetaData[Fish::COL_SAMPLE_CODE]);
					$fishForm->populate($fishMetaData);

					//insert fish
					$fishId = $fish->insert($fishBaseData);
					//now update fish with fish meta data
					$fish->updateFishAndMetadata($fishForm, $fishId, $fishBaseData);
					$importedFishes[$i][Fish::COL_ID] = $fishId;
					$importedFishes[$i]['sourceCsvRow'] = $rowNo;
					$i++;
				}
				
				// check wether a image shall be imported ------------------------
	            $qu = new Default_ReferenceQuery();
	            $filenames = $qu->getImageNames($dataset['fishFormDataset'][Fish::COL_SAMPLE_CODE]);
	            //case non-sensitive
	            foreach ($filenames as &$file) {
	                $file = strtolower($file);
	            }
	            $importImageFile = false;
	            if (! empty($filenames)) {
	                //case non-sensitive
	                if (in_array(strtolower($dataset['imageFormDataset'][Image::COL_ORIGINAL_FILENAME]), $filenames) != FALSE) {
	                    // Image is already in database
                            $importImageFile = false;
	                } else {
	                	$importImageFile = true;
	                }
	            } else {
	            	$importImageFile = true;
	            }
                // end check ------------------------------------------------------
	            
	            // import the images
		        if ($importImageFile) {
					//copy file to new path and rename
					$completeSource = self::RELATIVE_PATH_UPLOAD_CACHE.$this->key.'/'.$dataset['imageFormDataset'][Image::COL_ORIGINAL_FILENAME];
					$creator_guid = new Ble422_Guid();
					$guid = $creator_guid->__toString();
					$path_parts = pathinfo($completeSource);
					$originalFileName = $path_parts['basename']; //used later to create Image dataset
					$newFileNameWithGuid = $guid.'.'.strtolower($path_parts['extension']); //save extension in lower-case, needed for further processing in flex
					//relative path with new filename, prefix dot&slash required
					$completeDestination = './'.Image::RELATIVE_UPLOAD_PATH.'/'.$newFileNameWithGuid;
					if (! copy($completeSource, $completeDestination)) {
						throw new Zend_Exception("Error: copy fails, source: $fileName, destination: $completeDestination");
					}
					$ratio = $dataset['imageFormDataset'][Image::COL_RATIO_EXTERNAL];
					$this->logger->log(array('received original file' => $completeSource));
					$this->logger->log(array('copied uploaded file' => $completeDestination));
					$numberCopiedFiles++;
					//TODO write protect files
	
					//create other image files
					$tn_ratio = $image->processImage($completeDestination);
					//case sensibility: original file name is saved like spelled in CSV file
					$imageId = $image->insertImageDataset($completeDestination, $originalFileName, $fishId, $guid, $this->userId, $ratio, $tn_ratio);
	
					//create image base and meta datasets
					$imageMetaData = $dataset['imageFormDataset'];
					$imageForm->populate($imageMetaData);
					//$imageId = $image->insert($imageBaseData);
					$image->updateImageAndMetadata($imageForm, $imageId);
					$importedImages[$j]['sourceCsvRow'] = $rowNo;
					$importedImages[$j][Image::COL_ID] = $imageId;
					$importedImages[$j][Image::COL_FISH_ID] = $fishId;
					$importedImages[$j][Image::COL_ORIGINAL_FILENAME] = $originalFileName;
					$importedImages[$j]['completeDestination'] = $completeDestination;
					$relativePathAndFileNameWorkingCopy = './'.Image::RELATIVE_PATH_IMAGE_SHRINKED_WORKING_COPIES.'/'.$guid.'.jpg';
					$relativePathAndFileNameThumbnail = './'.Image::RELATIVE_PATH_IMAGE_THUMBNAILS.'/'.$guid.'.jpg';
					$importedImages[$j]['completeWorkingCopy'] = $relativePathAndFileNameWorkingCopy;
					$importedImages[$j]['completeThumbnail'] = $relativePathAndFileNameThumbnail;
					$j++;
		        }
			}
			$dbAdapter->commit();
			$this->logger->log(array('datasets committed, number of copied files into system' => $numberCopiedFiles));
			$returnCode = 'success';
			//delete upload cache dir
			Ble422_FileHelper::delete_directory(self::RELATIVE_PATH_UPLOAD_CACHE.$this->key);

		} catch (Exception $e) {
			$returnCode = 'error';
			$dbAdapter->rollBack();
			//delete copied/created files, else there will be orphaned files
			foreach ($importedImages as $image) {
				unlink($image['completeDestination']);
				unlink($image['completeWorkingCopy']);
				unlink($image['completeThumbnail']);
			}
			$this->logger->log(array('Error exception' => $e->getMessage()), 'ERROR, roll back of imported datasets and unsetting of files, see below');
			$this->logger->log(array('Roll back of importedFishes' => $importedFishes));
			$this->logger->log(array('Roll back of importedImages' => $importedImages));
			//delete upload cache dir
			Ble422_FileHelper::delete_directory(self::RELATIVE_PATH_UPLOAD_CACHE.$this->key);
			echo $e->getMessage();
		}

		$returnArray = array(	'returnCode' => $returnCode,
								'importFishes' => $importedFishes,
								'importImages' => $importedImages,
								'numberCopiedFiles' => $numberCopiedFiles);
		$this->logger->log(array('data import' => $returnArray));
		return $returnArray;
	}

	private function extractColumn($datasets, $columnName)
	{
		foreach ($datasets as $row => $dataset) {
			$columnValues[$row] = $dataset[$columnName];
			//echo ":".$columnName.":".$row.";";
		}
		return $columnValues;
	}

	private function getDatasetsWithNewHeadings($datasets, $systemAttributes, $csvHeadings)
	{
		foreach($systemAttributes as $key => $attr) {
			if ($attr != '--ignore--') {
				$manualAssociation[$attr] = $csvHeadings[$key];
			}
		}
		$flippedManualAssociation = array_flip($manualAssociation);

		//replace original headings with associated headings if set
		$newColNameValuePair = array();
		foreach ($datasets as $rowNo => $dataset) {
			foreach ($dataset as $colName => $value) {
				if (isset($flippedManualAssociation[$colName])) {
					//replace column name
					$newColNameValuePair[$flippedManualAssociation[$colName]] = $value;
				} else {
					//leave column name
					//CANCELLED because could lead to misunderstanding or unwanted behaviour
					//$newColNameValuePair[$colName] = $value;
				}
			}
			$datasetsReplacedHeader[$rowNo] = $newColNameValuePair;
		}
		$datasets = $datasetsReplacedHeader;
		unset($datasetsReplacedHeader);

		return $datasets;
	}
	
	private function getHeadingsArray($originalCsvHeadings)
	{
		foreach ($originalCsvHeadings as $col => $heading) {
			$headingsArray[] = array('csvCol' => $col,
			                         'csvHeading' => $heading,
			                         'newAssignedHeading' => $irgendwas);
		}
	}

	//handles the calls for aborting import e.g. deleting temp dir + files
	public function abort($key)
	{
		$this->key = $key;
		//delete upload cache dir
		Ble422_FileHelper::delete_directory(self::RELATIVE_PATH_UPLOAD_CACHE.$this->key);
		//TODO if already files where copied/created handle delete copied/created files, else there will be orphaned files

	}

	private function saveToNamespace()
	{
		$this->myNamespace = new Zend_Session_Namespace('Batch');
		$this->myNamespace->preparedDatasets = $this->preparedDatasets;
		$this->myNamespace->rowHasUniqueUploadedFile = $this->rowHasUniqueUploadedFile;
	}

	private function loadFromNamespace()
	{
		$this->myNamespace = new Zend_Session_Namespace('Batch');
		$this->preparedDatasets = $this->myNamespace->preparedDatasets;
		$this->rowHasUniqueUploadedFile = $this->myNamespace->rowHasUniqueUploadedFile;
	}

	/**
	 * prepares information for displaying single report over XML in flash
	 * preformats (appends line break, writes "(empty)" instead null value, what would display "null")
	 * @param $array
	 * @return unknown_type
	 * perhaps outsource whole check handling in own class or look for this in zend etc.
	 */
	private function reduceCheckResultForXml($array) {
		$reducedArray = array();
		foreach ($array as $key => $value) {
			if ( $key == 'labelCheck'
			|| $key == 'returnCode'
			|| $key == 'message') {
				//append line break
				if (empty($value)) {
					$reducedArray[$key] = '(empty)' . "\n";
				} else {
					$reducedArray[$key] = $value;
				}
				$reducedArray[$key] .= "\n";
			}
		}
		return $reducedArray;
	}
}
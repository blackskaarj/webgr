<?php

class Ce_StatisticController extends Zend_Controller_Action {


	private $namespace;
	private $dbAdapter;

	public function init()
	{
		$this->namespace = new Zend_Session_Namespace('default');
		$this->dbAdapter = Zend_Registry::get('DB_CONNECTION1');
	}

	public function indexAction() {

		$validator = new Zend_Validate_Int();
		$ceId = $this->getRequest()->getParam(CalibrationExercise::COL_ID);

		if ($validator->isValid($ceId)) {
			$this->view->ceId = $ceId;
		}
	}

	public function trainingAction()
	{
		$validator = new Zend_Validate_Int();
		$ceId = $this->getRequest()->getParam(CalibrationExercise::COL_ID);

		if ($validator->isValid($ceId)) {
			$this->view->ceId = $ceId;
		}
	}

	public function annotationsAction()
	{
		$select = $this->dbAdapter->select();
		$select->from(   array( 'anno' => Annotations::TABLE_NAME),
		array(    Annotations::COL_ID,
		Annotations::COL_DECIMAL,
		Annotations::COL_GROUP,
		Annotations::COL_CE_HAS_IMAGE_ID));
		$select->join(   array( 'cehim' => CeHasImage::TABLE_NAME),
		$this->dbAdapter->quoteIdentifier('anno.' . Annotations::COL_CE_HAS_IMAGE_ID). '=' . $this->dbAdapter->quoteIdentifier('cehim.' . CeHasImage::COL_ID),
		array());
		$select->join(   array( 'part' => Participant::TABLE_NAME),
		$this->dbAdapter->quoteIdentifier('anno.' . Annotations::COL_PART_ID). '=' . $this->dbAdapter->quoteIdentifier('part.' . Participant::COL_ID),
		array( Participant::COL_NUMBER,
		Participant::COL_ID));
		$select->join(   array( 'im' => Image::TABLE_NAME),
		$this->dbAdapter->quoteIdentifier('cehim.' . CeHasImage::COL_IMAGE_ID). '=' . $this->dbAdapter->quoteIdentifier('im.' . Image::COL_ID),
		array( Image::COL_ORIGINAL_FILENAME));
		$select->join(   array( 'fish' => Fish::TABLE_NAME),
		$this->dbAdapter->quoteIdentifier('im.' . Image::COL_FISH_ID). '=' . $this->dbAdapter->quoteIdentifier('fish.' . Fish::COL_ID),
		array( Fish::COL_SAMPLE_CODE));
		$constCeId = CalibrationExercise::COL_ID;
		$ceId = $this->getRequest()->getParam($constCeId);

		$filename = 'annotations_all.csv';
		if($this->getRequest()->getParam('level')){//with a certain expertise level
			if($this->getRequest()->getParam('level') != 'all') {
				$expertiseLevel = $this->getRequest()->getParam('level');
				$select->where('part.' . Participant::COL_EXPERTISE_LEVEL . "=?" , ucfirst($expertiseLevel));
				$filename = 'annotations_' . $expertiseLevel . '.csv';
			} else {
				$filename = 'annotations_all.csv';
			}
		}
		if($this->getRequest()->getParam('stock')){//only stock assessment
			$select->where('part.' . Participant::COL_STOCK_ASSESSMENT . "=1");
			$filename = 'annotations_stock.csv';
		}
		$select->where('cehim.' . CeHasImage::COL_CALIBRATION_EXERCISE_ID . "=?" , $ceId);
		$select->where('(anno.' . Annotations::COL_FINAL . "=?",1);
		$select->orWhere('anno.' . Annotations::COL_GROUP . "=?)",1);
		$select->order('fish.' . Fish::COL_ID);
		//		echo $select;
		$annoArray = $this->dbAdapter->fetchAll($select);

		//get the readers
		$partArray = $this->getParticipantList($ceId);

		$this->view->ceId = $ceId;
		$this->view->myRequest = $this->getRequest();
		
		if (empty($partArray)) {
			$this->view->noPart = TRUE;
			$this->render('annotations');
			return;
		}

		$partCount = count($partArray);

		//add the group reader
		array_push($partArray,array(User::COL_FIRSTNAME => '',
		User::COL_LASTNAME  => '',
		Participant::COL_NUMBER => 'Group',
		Participant::COL_ID => 0,
                                    'Institution' => ''));
		//add the APE column
		array_push($partArray,array(User::COL_FIRSTNAME => '',
		User::COL_LASTNAME  => '',
		Participant::COL_NUMBER => 'APE',
		Participant::COL_ID => -1,
                                    'Institution' => ''));

		//add the CV column
		array_push($partArray,array(User::COL_FIRSTNAME => '',
		User::COL_LASTNAME  => '',
		Participant::COL_NUMBER => 'CV',
		Participant::COL_ID => -2,
                                    'Institution' => ''));

		//add the STDDEV column
		array_push($partArray,array(User::COL_FIRSTNAME => '',
		User::COL_LASTNAME  => '',
		Participant::COL_NUMBER => 'STDDEV',
		Participant::COL_ID => -3,
                                    'Institution' => ''));

		//add the VARIANCE column
		array_push($partArray,array(User::COL_FIRSTNAME => '',
		User::COL_LASTNAME  => '',
		Participant::COL_NUMBER => 'VARIANCE',
		Participant::COL_ID => -4,
                                    'Institution' => ''));

		$fishSelect = $this->dbAdapter->select();
		$fishSelect->from(array('im'=>Image::TABLE_NAME));
		$fishSelect->join(   array( 'fish' => Fish::TABLE_NAME),
		$this->dbAdapter->quoteIdentifier('fish.' . Fish::COL_ID). '=' . $this->dbAdapter->quoteIdentifier('im.' . Image::COL_FISH_ID),
		array());
		$fishSelect->join(   array( 'cehim' => CeHasImage::TABLE_NAME),
		$this->dbAdapter->quoteIdentifier('im.' . Image::COL_ID). '=' . $this->dbAdapter->quoteIdentifier('cehim.' . CeHasImage::COL_IMAGE_ID),
		array());
		$fishSelect->where(CeHasImage::COL_CALIBRATION_EXERCISE_ID . "=?" , $ceId);
		//echo '<br>'.$fishSelect;

		//set the imagelist
		$imageSelect = $this->dbAdapter->select();
		$imageSelect->from(array('im'=>Image::TABLE_NAME));
		$imageSelect->join(   array( 'fish' => Fish::TABLE_NAME),
		$this->dbAdapter->quoteIdentifier('fish.' . Fish::COL_ID). '=' . $this->dbAdapter->quoteIdentifier('im.' . Image::COL_FISH_ID),
		array());
		$imageSelect->join(   array( 'cehim' => CeHasImage::TABLE_NAME),
		$this->dbAdapter->quoteIdentifier('im.' . Image::COL_ID). '=' . $this->dbAdapter->quoteIdentifier('cehim.' . CeHasImage::COL_IMAGE_ID),
		array());
		$imageSelect->where(CeHasImage::COL_CALIBRATION_EXERCISE_ID . "=?" , $ceId);
		//echo '<br>'.$imageSelect;
		$imageArray = $this->dbAdapter->fetchAll($imageSelect);

		//prepare an array that looks like the csv-file
		$csvArray = array();
		//prepare array header
		$csvArray['IMAGE'] = $partArray;

		foreach ($partArray as $part) {
			if (array_key_exists(Participant::COL_EXPERTISE_LEVEL, $part)) {
				if (isset($part[Participant::COL_EXPERTISE_LEVEL])) {
					$csvArray['Expertise level'][$part[Participant::COL_NUMBER]] = $part[Participant::COL_EXPERTISE_LEVEL];
				} else {
					$csvArray['Expertise level'][$part[Participant::COL_NUMBER]] = '-';
				}
			} else {
				$csvArray['Expertise level'][$part[Participant::COL_NUMBER]] = '-';
			}
			if (array_key_exists(Participant::COL_EXPERTISE_LEVEL, $part)) {
				if ($part[Participant::COL_STOCK_ASSESSMENT] == 1) {
					$csvArray['Stock assessment'][$part[Participant::COL_NUMBER]] = 'yes';
				} elseif ($part[Participant::COL_STOCK_ASSESSMENT] == 0) {
					$csvArray['Stock assessment'][$part[Participant::COL_NUMBER]] = 'no';
				} else {
					$csvArray['Stock assessment'][$part[Participant::COL_NUMBER]] = '-';
				}
			} else {
				$csvArray['Stock assessment'][$part[Participant::COL_NUMBER]] = '-';
			}
		}

		foreach ($annoArray as $annoValue) {
			$csvArray[$annoValue[Annotations::COL_CE_HAS_IMAGE_ID]] = array();
			foreach ($partArray as $partValue) {
				$csvArray[$annoValue[Annotations::COL_CE_HAS_IMAGE_ID]] += array($partValue[Participant::COL_ID] => "-");
			}
		}

		// calculate the ape, cv etc.
		$selectStat = $this->dbAdapter->select();
		$selectStat->from(   array(  'anno' => Annotations::TABLE_NAME),
		array(  'SUM_ANNO'      => 'sum(' . Annotations::COL_DECIMAL . ')',
                                    'COUNT_ANNO'    => 'count(' . Annotations::COL_DECIMAL . ')',
                                    'COUNT_IMAGE'   => 'count(distinct im.IMAGE_ORIGINAL_FILENAME)',
                                    'APE'           => new Zend_Db_Expr('((1/count(ANNO_DECIMAL)) * (abs(ANNO_DECIMAL - (avg(ANNO_DECIMAL)))/avg(ANNO_DECIMAL)))/count(distinct im.IMAGE_ORIGINAL_FILENAME)*100'),
                                    'CV'            => new Zend_Db_Expr('(stddev(ANNO_DECIMAL)/avg(ANNO_DECIMAL))/count(distinct im.IMAGE_ORIGINAL_FILENAME)*100'),
                                    'STDDEV'        => new Zend_Db_Expr('stddev(ANNO_DECIMAL)'),
                                    'VARIANCE'      => new Zend_Db_Expr('variance(ANNO_DECIMAL)')
		));
		$selectStat->join(   array( 'cehim' => CeHasImage::TABLE_NAME),
		$this->dbAdapter->quoteIdentifier('anno.' . Annotations::COL_CE_HAS_IMAGE_ID). '=' . $this->dbAdapter->quoteIdentifier('cehim.' . CeHasImage::COL_ID),
		array());
		$selectStat->join(   array( 'part' => Participant::TABLE_NAME),
		$this->dbAdapter->quoteIdentifier('anno.' . Annotations::COL_PART_ID). '=' . $this->dbAdapter->quoteIdentifier('part.' . Participant::COL_ID),
		array());
		//get filename for displaying and imageID in case further actions
		$selectStat->join(   array( 'im' => Image::TABLE_NAME),
		$this->dbAdapter->quoteIdentifier('cehim.' . CeHasImage::COL_IMAGE_ID). '=' . $this->dbAdapter->quoteIdentifier('im.' . Image::COL_ID),
		array(  Image::COL_ORIGINAL_FILENAME,
		Image::COL_ID));
		$selectStat->join(   array( 'fish' => Fish::TABLE_NAME),
		$this->dbAdapter->quoteIdentifier('im.' . Image::COL_FISH_ID). '=' . $this->dbAdapter->quoteIdentifier('fish.' . Fish::COL_ID),
		array( Fish::COL_SAMPLE_CODE));
		//XXX statistischen Werte auf Fisch gruppiert
		$selectStat->group(Fish::COL_SAMPLE_CODE);

		//$selectStat->group('im.' . Image::COL_ORIGINAL_FILENAME);

		$selectStat->where('cehim.' . CeHasImage::COL_CALIBRATION_EXERCISE_ID . "=?" , $ceId);
		$selectStat->where('(anno.' . Annotations::COL_FINAL . "=?",1);
		$selectStat->orWhere('anno.' . Annotations::COL_GROUP . "=?)",1);
		if($this->getRequest()->getParam('level')){//with a certain expertise level
			if($this->getRequest()->getParam('level') != 'all') {
				$expertiseLevel = $this->getRequest()->getParam('level');
				$selectStat->where('part.' . Participant::COL_EXPERTISE_LEVEL . "=?" , ucfirst($expertiseLevel));
			}
		}
		if($this->getRequest()->getParam('stock')){//only stock assessment
			$selectStat->where('part.' . Participant::COL_STOCK_ASSESSMENT . "=1");
		}
		//echo $selectStat;
		$statArray = $this->dbAdapter->fetchAll($selectStat);
		$fishStatArray = array();
		foreach ($statArray as $stat) {
			$fishStatArray = $fishStatArray + array($stat[Fish::COL_SAMPLE_CODE]=>array('APE'       =>$stat['APE'],
                                                                                        'CV'        =>$stat['CV'],
                                                                                        'STDDEV'    =>$stat['STDDEV'],
                                                                                        'VARIANCE'  =>$stat['VARIANCE']));
		}

		// and fill in the results
		foreach ($annoArray as $annoValue) {
			if ($annoValue[Annotations::COL_GROUP] == '0'){
				//reader final value
				$csvArray[$annoValue[Annotations::COL_CE_HAS_IMAGE_ID]][$annoValue[Annotations::COL_PART_ID]] = (float) $annoValue[Annotations::COL_DECIMAL];
				//$annotationsCount++;
			}else{
				//group value
				$csvArray[$annoValue[Annotations::COL_CE_HAS_IMAGE_ID]][0] = $annoValue[Annotations::COL_DECIMAL];
				//$annotationsCount++;
			}
			$csvArray[$annoValue[Annotations::COL_CE_HAS_IMAGE_ID]][-1] = number_format($fishStatArray[$annoValue[Fish::COL_SAMPLE_CODE]]['APE'],4,',','');
			$csvArray[$annoValue[Annotations::COL_CE_HAS_IMAGE_ID]][-2] = number_format($fishStatArray[$annoValue[Fish::COL_SAMPLE_CODE]]['CV'],4,',','');
			$csvArray[$annoValue[Annotations::COL_CE_HAS_IMAGE_ID]][-3] = number_format($fishStatArray[$annoValue[Fish::COL_SAMPLE_CODE]]['STDDEV'],4,',','');
			$csvArray[$annoValue[Annotations::COL_CE_HAS_IMAGE_ID]][-4] = number_format($fishStatArray[$annoValue[Fish::COL_SAMPLE_CODE]]['VARIANCE'],4,',','');
		}

		//---------------------------------------------------------------------------
		//get statistics per participant
		foreach ($partArray as $part) {
			$currentPartId = $part[Participant::COL_ID];
			//leave out part_id <= 0, this are statistics

			if ($currentPartId > 0) {
				$sumDistMean = 0.0;
				$sumDistGroup = 0.0;
				$numberDistMean = 0;
				$numberDistGroup = 0;
					
				//iterate the final annos from the select
				foreach ($annoArray as $annoValue1) {
					$currentAnnoId = $annoValue1[Annotations::COL_ID];
					$currentValue = NULL;
					$distGroup = NULL;
					$currentGroup = NULL;
					$ceHasImageId = NULL;

					if ($annoValue1[Annotations::COL_GROUP] == 0
					&& $annoValue1[Annotations::COL_PART_ID] == $currentPartId) {
						//annos of distinct participant
						$currentValue = (float) $annoValue1[Annotations::COL_DECIMAL];

						//get current image
						foreach ($annoArray as $annoValue2) {
							if ($annoValue2[Annotations::COL_ID] == $currentAnnoId) {
								$ceHasImageId = $annoValue2[Annotations::COL_CE_HAS_IMAGE_ID];
								break;
							}
						}
						//get group value for image in CE
						foreach ($annoArray as $annoValue2) {
							if ($annoValue2[Annotations::COL_CE_HAS_IMAGE_ID] == $ceHasImageId
							&& $annoValue2[Annotations::COL_GROUP] == 1) {
								$currentGroup = (float) $annoValue2[Annotations::COL_DECIMAL];
								break;
							}
						}
						//get readers mean for image in CE
						$sumValues = 0.0;
						$numberImageAnnotations = 0;
						foreach ($annoArray as $annoValue2) {
							$value = NULL;
							if ($annoValue2[Annotations::COL_CE_HAS_IMAGE_ID] == $ceHasImageId
							&& $annoValue2[Annotations::COL_GROUP] == 0) {
								$value = (float) $annoValue2[Annotations::COL_DECIMAL];
								$sumValues += $value;
								$numberImageAnnotations++;
							}
						}
						$mean = NULL;
						if ($sumValues > 0) {
							$mean = $sumValues / $numberImageAnnotations;
						}

						//distance of annotation to group value
						if (isset($currentGroup) && isset($currentValue)) {
							$distGroup = abs($currentValue - $currentGroup);
							$sumDistGroup += $distGroup;
							$numberDistGroup++;
						} else {
							$groupNotAvail = TRUE;
							$distGroup = NULL;
						}

						//distance of annotation to mean value for this image in CE
						if (isset($mean) && isset($currentValue)) {
							$distMean = abs($currentValue - $mean);
							$numberDistMean++;
						} else {
							$meanNotAvail = TRUE;
							$distMean = NULL;
						}
						$sumDistMean += $distMean;
						//distinct annotation of reader x
					}
					//all annos incl. group
				}
				//all participants
				if ($numberDistMean > 0) {
					$meanDistMean = $sumDistMean / $numberDistMean;
					$statPart[$part[Participant::COL_NUMBER]]['meanDistMean'] = number_format($meanDistMean, 4, ',', '');
				} else {
					$statPart[$part[Participant::COL_NUMBER]]['meanDistMean'] = NULL;
				}
				if ($numberDistGroup > 0) {
					$meanDistGroup = $sumDistGroup / $numberDistGroup;
					$statPart[$part[Participant::COL_NUMBER]]['meanDistGroup'] = number_format($meanDistGroup, 4, ',', '');
				} else {
					$statPart[$part[Participant::COL_NUMBER]]['meanDistGroup'] = NULL;
				}
			}
			//all "participants" incl. statistic cols
		}
		//---------------------------------------------------------------------------

		foreach ($statPart as $partNo => $part) {
			$csvArray['Mean of distances to mean'][$partNo] = $part['meanDistMean'];
			$csvArray['Mean of distances to group'][$partNo] = $part['meanDistGroup'];
		}
		for ($i = 0; $i > -5; $i--) {
			$csvArray['Mean of distances to mean'][$i] = '-';
			$csvArray['Mean of distances to group'][$i] = '-';
		}

		$qu = new Default_ReferenceQuery();
		$images = $qu->getImagesForCe($ceId);
		$ceHimFilenameArray = $qu->getCeHasImageFilenameArray($images);

		if($this->getRequest()->getParam('as') == 'csv'){
			// prepare the header
			$csvString = 'IMAGE';
			foreach ($partArray as $partValue) {
				$csvString .= ',' . $partValue[Participant::COL_NUMBER];
			}
			$csvString .= "\n";
			// fill in the results
			foreach ($csvArray as $imageId => $results) {
				if($imageId != 'IMAGE'){
					if (is_numeric($imageId)) {
						$imageName = $ceHimFilenameArray[$imageId];
					} else {
						$imageName = $imageId;
					}
					$csvString .= $imageName;
					foreach ($results as $decimal) {
						$csvString .= "," . "\"". $decimal . "\"";
					}
					$csvString .= "\n";
				}
			}
			$this->view->csvString = $csvString;
			$this->view->filename = $filename;
			// generate the download file
			Zend_Layout::resetMvcInstance();
			$this->render('csvstring');
		} else {
			$this->view->csvArray = $csvArray;
			$this->view->ceHimFilenameArray = $ceHimFilenameArray;
			$this->view->ceId = $ceId;
		}
	}

	public function distancesAction()
	{
		$ceHImId = $this->getRequest()->getParam(CeHasImage::COL_ID);
		$ceId = $this->getRequest()->getParam(CalibrationExercise::COL_ID);

		$select = $this->dbAdapter->select();
		$select->from(   array( 'anno' => Annotations::TABLE_NAME),
		array(    Annotations::COL_ID,
		Annotations::COL_DECIMAL,
		Annotations::COL_GROUP,
		Annotations::COL_CE_HAS_IMAGE_ID));
		$select->join(   array( 'cehim' => CeHasImage::TABLE_NAME),
		$this->dbAdapter->quoteIdentifier('anno.' . Annotations::COL_CE_HAS_IMAGE_ID). '=' . $this->dbAdapter->quoteIdentifier('cehim.' . CeHasImage::COL_ID),
		array());
		$select->join(   array( 'part' => Participant::TABLE_NAME),
		$this->dbAdapter->quoteIdentifier('anno.' . Annotations::COL_PART_ID). '=' . $this->dbAdapter->quoteIdentifier('part.' . Participant::COL_ID),
		array( Participant::COL_NUMBER,
		Participant::COL_ID));
		$select->join(   array( 'im' => Image::TABLE_NAME),
		$this->dbAdapter->quoteIdentifier('cehim.' . CeHasImage::COL_IMAGE_ID). '=' . $this->dbAdapter->quoteIdentifier('im.' . Image::COL_ID),
		array( Image::COL_ORIGINAL_FILENAME,
		Image::COL_RATIO_EXTERNAL,
		Image::COL_SHRINKED_RATIO));
		$select->join(   array( 'dots' => Dots::TABLE_NAME),
		$this->dbAdapter->quoteIdentifier('anno.' . Annotations::COL_ID). '=' . $this->dbAdapter->quoteIdentifier('dots.' . Dots::COL_ANNO_ID));

		$filename = 'distances.csv';

		//		$select->where('cehim.' . $this->dbAdapter->quoteInto(CeHasImage::COL_CALIBRATION_EXERCISE_ID . "= ?" , $ceId));
		$select->where('cehim.' . $this->dbAdapter->quoteInto(CeHasImage::COL_ID . "= ?" , $ceHImId));
		$select->where('anno.' . Annotations::COL_FINAL . "= ?", 1);
		//		$select->where('(anno.' . Annotations::COL_FINAL . "= ?", 1);
		//		$select->orWhere('anno.' . Annotations::COL_GROUP . "= ?)", 1);
		$select->order('dots.' . Dots::COL_SEQUENCE);

		//get info about image
		$annoRow = $this->dbAdapter->fetchRow($select);

		$this->view->ceId = $ceId;
		$this->view->myRequest = $this->getRequest();
		$this->view->cehim = $ceHImId;
		
		if (empty($annoRow)) {
			$this->view->noPart = TRUE;
			$this->render('distances');
			return;
		}

		$externalRatio = $annoRow[Image::COL_RATIO_EXTERNAL];
		//$internalRatio = $annoRow[Image::COL_RATIO_INTERNAL];
		$shrinkedRatio = $annoRow[Image::COL_SHRINKED_RATIO];

			
		//TODO case use internal ratio
		//image
		//if internal ratio is set internal ratio is used
		//else external provided ratio is used
		//      if (isset($internalRatio)) {
		//          $ratio = $internalRatio;
		//      } else {
		//          $ratio = $externalRatio;
		//      }
		$ratio = $externalRatio;


		//get the readers
		$partArray = $this->getParticipantList($ceId);

		if (empty($partArray)) {
			$this->view->noPart = TRUE;
			$this->render('distances');
			return;
		}

		$partCount = count($partArray);

		//prepare an array that looks like the csv-file
		$csvArray = array();
		//prepare array header
		$csvArray['header'] = $partArray;

		foreach ($partArray as $part) {
			if (array_key_exists(Participant::COL_EXPERTISE_LEVEL, $part)) {
				if (isset($part[Participant::COL_EXPERTISE_LEVEL])) {
					$csvArray['Expertise level'][$part[Participant::COL_NUMBER]] = $part[Participant::COL_EXPERTISE_LEVEL];
				} else {
					$csvArray['Expertise level'][$part[Participant::COL_NUMBER]] = '-';
				}
			} else {
				$csvArray['Expertise level'][$part[Participant::COL_NUMBER]] = '-';
			}
			if (array_key_exists(Participant::COL_EXPERTISE_LEVEL, $part)) {
				if ($part[Participant::COL_STOCK_ASSESSMENT] == 1) {
					$csvArray['Stock assessment'][$part[Participant::COL_NUMBER]] = 'yes';
				} elseif ($part[Participant::COL_STOCK_ASSESSMENT] == 0) {
					$csvArray['Stock assessment'][$part[Participant::COL_NUMBER]] = 'no';
				} else {
					$csvArray['Stock assessment'][$part[Participant::COL_NUMBER]] = '-';
				}
			} else {
				$csvArray['Stock assessment'][$part[Participant::COL_NUMBER]] = '-';
			}
		}

		//initialize array
		for($row = 1; $row < 50; $row++) {
			for($col = 1; $col <= $partCount; $col++) {
				$csvArray[$row][$col] = NULL;
			}

		}

		$cMax = 0;
		foreach ($partArray as $partValue) {
			//			$csvArray[$dotsValue[Dots::COL_ID]] = array();
			$selectClone = clone($select);
			$selectClone->where('anno.' . Annotations::COL_PART_ID . '= ?', $partValue[Participant::COL_ID]);

			$dotsArray = $this->dbAdapter->fetchAll($selectClone);

			if (empty($dotsArray)) continue;

			$i = 1;
			$c = count($dotsArray);
			if ($c > $cMax) $cMax = $c;

			$sumDist = 0.0;
			//            $c = 25;
			foreach ($dotsArray as $pos => $dotsValue) {
				if ($i == $c) break;
				//$csvArray[$i] += array($partValue[Participant::COL_ID] => "-");

				//distance per dots
				$x1 = $dotsArray[$pos][Dots::COL_DOTS_X];
				$y1 = $dotsArray[$pos][Dots::COL_DOTS_Y];
				$x2 = $dotsArray[$pos + 1][Dots::COL_DOTS_X];
				$y2 = $dotsArray[$pos + 1][Dots::COL_DOTS_Y];

				//distance in dots
				$pixDistance = $this->getDistance($x1, $y1, $x2, $y2);
				//distance in micrometer
				$physDistance = $pixDistance * $ratio * (1.0 / $shrinkedRatio);

				$sumDist += $physDistance;

				//$csvArray[$pos] += array($partValue[Participant::COL_ID] => $physDistance);
				$csvArray[$i][$partValue[Participant::COL_NUMBER]] = round(($physDistance / 1000), 3);

				$i++;
			}
			unset($selectClone);
		}

		//fill empty cells
		for($row = 1; $row < $cMax; $row++) {
			for($col = 1; $col <= $partCount; $col++) {
				if (! isset($csvArray[$row][$col])) {
					$csvArray[$row][$col] = '-';
				}
			}
		}
		for($row = $cMax; $row < 50; $row++) {
			unset($csvArray[$row]);
		}
		
		$this->view->csvArray = $csvArray;

	    if($this->getRequest()->getParam('as') == 'csv'){
            $csvString = '';
            foreach ($csvArray as $rowKey => $rowArray) {
            	foreach ($rowArray as $key => $rowValue) {
            	    if ($rowKey == 'header') {
            	        $csvString .= $rowValue[Participant::COL_NUMBER] . ',' ;
                    } else {
                    	$csvString .= $rowValue . ',' ;
                    }
            	}
                $csvString .= "\n";
            }
            $this->view->csvString = $csvString;
            $this->view->filename = $filename;
            // generate the download file
            Zend_Layout::resetMvcInstance();
            $this->render('csvstring');
        }
	}

	public function alldistancesAction()
	{
	    $ceId = $this->getRequest()->getParam(CalibrationExercise::COL_ID);
	    $select = $this->dbAdapter->select();
        $select->from(   array( 'anno' => Annotations::TABLE_NAME));
        $select->join(   array( 'cehim' => CeHasImage::TABLE_NAME),
        $this->dbAdapter->quoteIdentifier('anno.' . Annotations::COL_CE_HAS_IMAGE_ID). '=' . $this->dbAdapter->quoteIdentifier('cehim.' . CeHasImage::COL_ID),
        array());
        $select->join(   array( 'part' => Participant::TABLE_NAME),
        $this->dbAdapter->quoteIdentifier('anno.' . Annotations::COL_PART_ID). '=' . $this->dbAdapter->quoteIdentifier('part.' . Participant::COL_ID),
        array( Participant::COL_NUMBER));
        $select->join(   array( 'im' => Image::TABLE_NAME),
        $this->dbAdapter->quoteIdentifier('cehim.' . CeHasImage::COL_IMAGE_ID). '=' . $this->dbAdapter->quoteIdentifier('im.' . Image::COL_ID),
        array( Image::COL_ORIGINAL_FILENAME,
        Image::COL_RATIO_EXTERNAL,
        Image::COL_SHRINKED_RATIO));
        $select->join(   array( 'dots' => Dots::TABLE_NAME),
            $this->dbAdapter->quoteIdentifier('anno.' . Annotations::COL_ID). '=' . $this->dbAdapter->quoteIdentifier('dots.' . Dots::COL_ANNO_ID));
        
        $select->where('anno.' . Annotations::COL_FINAL . "= ? OR anno." . Annotations::COL_GROUP . " = ?", 1);
        $select->where('cehim.' . CeHasImage::COL_CALIBRATION_EXERCISE_ID . "= ?",$ceId);
        
        $select->order('im.' . Image::COL_ORIGINAL_FILENAME);
        $select->order('part.' . Participant::COL_NUMBER);
        $select->order('anno.' . Annotations::COL_ID);
        $select->order('dots.' . Dots::COL_SEQUENCE);

        $resultArray = $this->dbAdapter->fetchAll($select);
        
        $progenitor = array();
        $distanceArray = array();
        $csvArray = array();
        $csvArray[0] = array('reader','image','mark','distance to the progenitor');
        foreach ($resultArray as $key => $dot) {
        	// calculate distance
        	$distance = 0;
            if(isset($resultArray[$key - 1]) && $resultArray[$key - 1][Annotations::COL_ID] == $dot[Annotations::COL_ID])
            {
            	$progenitor = $resultArray[$key - 1];
            } else {
            	$progenitor = array();
        	}
            if ($progenitor != array()) {
                $distance = $this->getDistance(   $dot[Dots::COL_DOTS_X],$dot[Dots::COL_DOTS_Y],
                                                  $progenitor[Dots::COL_DOTS_X],$progenitor[Dots::COL_DOTS_Y]);
            }
        	
        	$physDistance = $distance * $dot[Image::COL_RATIO_EXTERNAL] * (1.0 / $dot[Image::COL_SHRINKED_RATIO]);
            $distanceArray[$dot[Dots::COL_ID]] = round(($physDistance / 1000), 3);
            
            //buliding the csv Array
            $csvArray[] = array($dot[Participant::COL_NUMBER],
                                    $dot[Image::COL_ORIGINAL_FILENAME],
                                    $dot[Dots::COL_SEQUENCE],
                                    $distanceArray[$dot[Dots::COL_ID]]);
                                
            // replace readernumber if the annot. is a group one
            if ($dot[Annotations::COL_GROUP] == 1) {
                $csvArray[$key + 1][0] = 'group';
            }
        }
        $this->configureView($csvArray,$ceId,'alldistances.csv');
	}
	
	public function participantsAction()
	{
		$ceId = $this->getRequest()->getParam(CalibrationExercise::COL_ID);
		$partsArray[0] = array( Participant::COL_NUMBER => 'Reader',
		User::COL_FIRSTNAME => 'Firstname',
		User::COL_LASTNAME => 'Lastname',
                                    'Institution' => 'Institution',
                                    'Country' => 'Country');
		$partsArray = array_merge($partsArray,$this->getParticipantList($ceId));

		$this->configureView($partsArray,$ceId, 'participants.csv');
	}

	public function imagesAction()
	{
		$ceId = $this->getRequest()->getParam(CalibrationExercise::COL_ID);
		$metaData = new Default_MetaData();
		$select = $metaData->getSelectForGroups();
		$select->join(   array( 'cehim' => CeHasImage::TABLE_NAME),
		$this->dbAdapter->quoteIdentifier('image.' . Image::COL_ID). '=' . $this->dbAdapter->quoteIdentifier('cehim.' . CeHasImage::COL_IMAGE_ID),
		array());
		
		$select->where(CeHasImage::COL_CALIBRATION_EXERCISE_ID . "=?" , $ceId);
		//prepare the header
		$headerArray = array(   Image::COL_ORIGINAL_FILENAME => 'Original file name',
		Fish::COL_SAMPLE_CODE => 'Fish sample code',
		Image::COL_DIM_X => 'Width',
		Image::COL_DIM_Y => 'Height');
		foreach ($metaData->fishRowSetArray as $fishAttr) {
			$headerArray = $headerArray + array('ATDE_'.$fishAttr[AttributeDescriptor::COL_ID] => $fishAttr[AttributeDescriptor::COL_NAME].'<br>'.$fishAttr['UNIT']);
		}
		foreach ($metaData->imageRowSetArray as $imAttr) {
			$headerArray = $headerArray + array('ATDE_'.$imAttr[AttributeDescriptor::COL_ID] => $imAttr[AttributeDescriptor::COL_NAME].'<br>'.$imAttr['UNIT']);
		}
		$imagesArray[0] = $headerArray;
		//echo $select;
		$imagesArray = array_merge($imagesArray,$this->dbAdapter->fetchAll($select));

		$this->configureView($imagesArray,$ceId, 'images.csv');
	}

	public function cedefinitionAction()
	{
		$ceId = intval($this->getRequest()->getParam(CalibrationExercise::COL_ID));
		$select = $this->dbAdapter->select();
		$select->from(  'v_imageset_info');
		$select->where(CalibrationExercise::COL_ID . "=?",$ceId);
		$select->group( AttributeDescriptor::COL_NAME);
		$defArray = $this->dbAdapter->fetchAll($select);

		//building the csvArray
		$csvArray[0] = array('Attribute','Params');
		if($defArray != array()) {
			foreach ($defArray as $def) {
				if($def[AttributeDescriptor::COL_UNIT] != null){
					$unit = ' ' . $def[AttributeDescriptor::COL_UNIT];
				}
				if ($def[AttributeDescriptor::COL_VALUE_LIST]  == 1) {
					$params = $def[ValueList::COL_NAME];
				}elseif($def[ImagesetAttributes::COL_FROM] == null && $def[ImagesetAttributes::COL_TO] != null){
					$params = $def[ImagesetAttributes::COL_TO] . $unit;
				}elseif($def[ImagesetAttributes::COL_FROM] != null && $def[ImagesetAttributes::COL_TO] == null){
					$params = $def[ImagesetAttributes::COL_FROM] . $unit;
				}else{
					$params = $def[ImagesetAttributes::COL_FROM] . ' to ' . $def[ImagesetAttributes::COL_TO] . $unit;
				}
				array_push($csvArray,array($def[AttributeDescriptor::COL_NAME],$params));
			}
		}else{
			array_push($csvArray,array('not','defined'));
		}

		// get expertise and key for the CE
		$ceSelect = $this->dbAdapter->select();
		$ceSelect->from(array('ce' => CalibrationExercise::TABLE_NAME));
		$ceSelect->join(array('exp' => Expertise::TABLE_NAME),
        'ce.' . CalibrationExercise::COL_EXPERTISE_ID . ' = ' . 'exp.' . Expertise::COL_ID);
		$ceSelect->join(array('key' => KeyTable::TABLE_NAME),
        'ce.' . CalibrationExercise::COL_KEY_TABLE_ID . ' = ' . 'key.' . KeyTable::COL_ID);
		$ceSelect->where(CalibrationExercise::COL_ID . '=?',$ceId);
		$ceArray = $this->dbAdapter->fetchAll($ceSelect);
		$this->view->ceArray = $ceArray[0];

		$this->configureView($csvArray,$ceId, 'calibration_exercise_def.csv');
	}

	public function trainingannotationAction()
	{
		$ceId = intval($this->getRequest()->getParam(CalibrationExercise::COL_ID));
		$ceTable = new CalibrationExercise();
		$ceArray = $ceTable->find($ceId)->toArray();

		$resultSelect = $this->dbAdapter->select();
		$resultSelect->from('v_all_annotations');
		$resultSelect->where(CalibrationExercise::COL_ID . '=?',$ceId);
		$resultSelect->where(Annotations::COL_FINAL . '=?',1);
		$resultAnnos = $this->dbAdapter->fetchAll($resultSelect);

		$imageIdString = '';
		$assocResult = array();
		foreach($resultAnnos as $resultAnno){
			// get all image ids
			if ($imageIdString != ''){
				$seperator = ',';
			}else{
				$seperator = '';
			}
			$imageIdString .= $seperator . $resultAnno[Image::COL_ID];

			// create a assoc array to create the result array later on
			$assocResult += array($resultAnno[Image::COL_ID] =>
			array (Annotations::COL_DECIMAL => $resultAnno[Annotations::COL_DECIMAL],
			Annotations::COL_ID => $resultAnno[Annotations::COL_ID]));
		}

		// get the all references for the images
		$refSelect = $this->dbAdapter->select();
		$refSelect->from('v_all_annotations');
		$refSelect->where('(' . Annotations::COL_WEBGR_REF . '=?',1);
		$refSelect->orWhere(Annotations::COL_WS_REF . '=? )',1);
		$refSelect->where(CalibrationExercise::COL_EXPERTISE_ID . '=?', $ceArray[0][CalibrationExercise::COL_EXPERTISE_ID]);
		$refSelect->where(CalibrationExercise::COL_KEY_TABLE_ID . '=?', $ceArray[0][CalibrationExercise::COL_KEY_TABLE_ID]);
		if($imageIdString) {
			$refSelect->where(Image::COL_ID . ' IN (' . $imageIdString . ')');
		} else {
			$refSelect->where(Image::COL_ID . ' IN (\'\')');
		}
		$refAnnos = $this->dbAdapter->fetchAll($refSelect);

		// create the header
		$headerArray = array(   Image::COL_ORIGINAL_FILENAME => 'original file name',
		Annotations::COL_DECIMAL => 'own result',
                                'bia' => 'bia',
                                'reference' => 'reference',
                                'ref. type' => 'ref. type',
		Annotations::COL_ID => 'Anno. ID');

		//create the resultArray
		$csvArray = array($headerArray);
		foreach ($refAnnos as $refAnno) {
			if ($refAnno[Annotations::COL_WS_REF] == 1) {
				$refType = 'WS-Ref';
			}else{
				$refType = 'WebGR-Ref';
			}
			$bia =  abs($refAnno[Annotations::COL_DECIMAL] - $assocResult[$refAnno[Image::COL_ID]][Annotations::COL_DECIMAL]);
			$rowArray = array(  Image::COL_ORIGINAL_FILENAME => $refAnno[Image::COL_ORIGINAL_FILENAME],
			Annotations::COL_DECIMAL => $assocResult[$refAnno[Image::COL_ID]][Annotations::COL_DECIMAL],
                                'bia' => $bia,
                                'reference' => $refAnno[Annotations::COL_DECIMAL],
                                'ref. type' => $refType,
			Annotations::COL_ID => $assocResult[$refAnno[Image::COL_ID]][Annotations::COL_ID],);
			array_push($csvArray,$rowArray);
		}

		$this->configureView($csvArray,$ceId, 'annotation_training.csv');
	}

	private function getParticipantList($ceId)
	{
		$partSelect = $this->dbAdapter->select();
		$partSelect->from(array( 'part' =>Participant::TABLE_NAME));
		$partSelect->join(   array( 'user' => User::TABLE_NAME),
		$this->dbAdapter->quoteIdentifier('part.' . Participant::COL_USER_ID). '=' . $this->dbAdapter->quoteIdentifier('user.' . User::COL_ID),
		array( User::COL_FIRSTNAME,
		User::COL_LASTNAME));
		$partSelect->joinLeft(   array( 'valiI' => ValueList::TABLE_NAME),
		$this->dbAdapter->quoteIdentifier('user.' . User::COL_INSTITUTION). '=' . $this->dbAdapter->quoteIdentifier('valiI.' . ValueList::COL_ID),
		array("Institution" => ValueList::COL_NAME));
		$partSelect->joinLeft(   array( 'valiC' => ValueList::TABLE_NAME),
		$this->dbAdapter->quoteIdentifier('user.' . User::COL_COUNTRY). '=' . $this->dbAdapter->quoteIdentifier('valiC.' . ValueList::COL_ID),
		array("Country" => ValueList::COL_NAME));
		$partSelect->where('part.' . Participant::COL_CE_ID . "=?" , $ceId);
		if($this->getRequest()->getParam('level')){//with a certain expertise level
			if($this->getRequest()->getParam('level') != 'all') {
				$expertiseLevel = $this->getRequest()->getParam('level');
				$partSelect->where('part.' . Participant::COL_EXPERTISE_LEVEL . "=?" , ucfirst($expertiseLevel));
			}
		}
		if($this->getRequest()->getParam('stock')){//only stock assessment
			$partSelect->where('part.' . Participant::COL_STOCK_ASSESSMENT . "=1");
		}
		$partSelect->order('part.' . Participant::COL_NUMBER);
		return $this->dbAdapter->fetchAll($partSelect);

	}

	private function configureView($csvArray, $ceId, $filename)
	{
		if($this->getRequest()->getParam('as') == 'csv'){
			$csvString = '';
			foreach ($csvArray as $singleArray) {
				foreach ($csvArray[0] as $alias => $head) {
					$csvString .= $singleArray[$alias] . ',' ;
				}
				$csvString .= "\n";
			}
			$this->view->csvString = $csvString;
			$this->view->filename = $filename;
			// generate the download file
			Zend_Layout::resetMvcInstance();
			$this->render('csvstring');
		}else{
			$this->view->csvArray = $csvArray;
			$this->view->Action = $this->getRequest()->getActionName();
			$this->view->ceId = $ceId;
			$this->render('csvarray');
		}
	}

	//TODO logic remove to model
	private function getDistance($x1, $y1, $x2, $y2) {
		$x = ( pow($x2-$x1,2));
		$y = ( pow($y2-$y1,2));
		$distance = ( sqrt($x + $y) );
		return $distance;
	}
}
<?php

class Service_Annotation 
{
	private $namespace;
	
	public function __construct()
	{
		$this->namespace = new Zend_Session_Namespace('default');
	}
	
	private function getParticipantId()
	{
        $constPartId = Participant::COL_ID;
        return $this->namespace->$constPartId;
	}
        private function getParticipantRole()
        {
            $constPartRole = Participant::COL_ROLE;
            return $this->namespace->$constPartRole;
        }
        private function getCurrentCeID()
        {
            $constCeId = CalibrationExercise::COL_ID;
            return $this->namespace->$constCeId;
        }
	
	public function save(	$ceHim,
							$ringcount,
							$decimal,
							$brightness,
							$contrast,
							$color,
							$magnification,
							$sub = null,
							$comment = null,
							$group = 0,
							$wsRef = 0,
							$webgrRef = 0) 
	{
		$annotationTable = new Annotations();
		
                if($decimal == ''){
                    $decimal = null;
                }
                
		$data = array(	Annotations::COL_PART_ID => $this->getParticipantId(),
						Annotations::COL_CE_HAS_IMAGE_ID => $ceHim, 
						Annotations::COL_COUNT => $ringcount,
						Annotations::COL_DECIMAL => $decimal,
						Annotations::COL_BRIGHTNESS => $brightness,
						Annotations::COL_CONTRAST => $contrast,
						Annotations::COL_COLOR => $color,
						Annotations::COL_MAGNIFICATION => $magnification,
						Annotations::COL_SUB => $sub,
						Annotations::COL_COMMENT => $comment,
						Annotations::COL_GROUP => $group,
						Annotations::COL_WS_REF => $wsRef,
						Annotations::COL_WEBGR_REF => $webgrRef);
						
		return $annotationTable->insert($data);
	}
	
	public function update(	$ceHim,
							$annoId,
							$ringcount,
							$decimal,
							$brightness,
							$contrast,
							$color,
							$magnification,
							$sub = null,
							$comment = null) 
	{
		$annotationTable = new Annotations();
		
		$data = array(	Annotations::COL_CE_HAS_IMAGE_ID => $ceHim, 
						Annotations::COL_COUNT => $ringcount,
						Annotations::COL_DECIMAL => $decimal,
						Annotations::COL_BRIGHTNESS => $brightness,
						Annotations::COL_CONTRAST => $contrast,
						Annotations::COL_COLOR => $color,
						Annotations::COL_MAGNIFICATION => $magnification,
						Annotations::COL_SUB => $sub,
						Annotations::COL_COMMENT => $comment);
						
		$annotationTable->update($data,Annotations::COL_ID . " = '" . $annoId . "'");
		
		return $annoId;
	}
	
	public function markAsFinal($annoId,$ceHim) 
	{
		$annotationTable = new Annotations();
		$partId = $this->getParticipantId();
		
		//mark all Annos. from part an cehim_id as not final
		$data = array(	Annotations::COL_FINAL => 0);
		$where[] = Annotations::COL_CE_HAS_IMAGE_ID . "='" . $ceHim . "'";
		$where[] = Annotations::COL_PART_ID . "='" . $partId . "'";
		$annotationTable->update($data,$where);
		
		// mark the final one
		$data = array(	Annotations::COL_FINAL => 1);
		$annotationTable->update($data,Annotations::COL_ID . " = '" . $annoId . "'");
		
		return $annoId;
	}
	
	public function delete($annoId)
	{
		Service_Dots::delete($annoId);
		$annotationTable = new Annotations();
		$annotationTable->delete(Annotations::COL_ID . " = '" . $annoId . "'");
	}
	
	public function getMyAnnotationsByCeHim($ceHim = 0,$state = null,$imageId = 0)
	{
		$annotationTable = new Annotations();
		$dbAdapter = $annotationTable->getAdapter();
		$select = $dbAdapter->select();
		$select->from(array('annos' => Annotations::TABLE_NAME));
		$fetch = true;
		
		if ($state == null){
		    $select->where(Annotations::COL_CE_HAS_IMAGE_ID."=?",$ceHim,'int') .
		    $select->where(Annotations::COL_PART_ID."=?",$this->getParticipantId(),'int');
		    $select->where(Annotations::COL_GROUP."!=?",1,'int');
		    $select->where(Annotations::COL_WS_REF."!=?",1,'int');
		    $select->where(Annotations::COL_WEBGR_REF."!=?",1,'int');
		}else if ($state == 'groupState'){
			$select->where(Annotations::COL_CE_HAS_IMAGE_ID."=?",$ceHim,'int');
            $select->where(Annotations::COL_GROUP."=?",1,'int');
		}else if ($state == 'ws-refState'){
		    // check for user roles
            $storage = Zend_Auth::getInstance()->getStorage()->read();
            $roleConst = User::COL_ROLE;
            if ($storage->$roleConst == 'admin' || $storage->$roleConst == 'manager' ||$this->getParticipantRole() == 'Coordinator'){
            	$ceTable = new CalibrationExercise();
            	$ceArray = $ceTable->find($this->getCurrentCeID())->toArray();
            	$select->join(array('ceHim' => CeHasImage::TABLE_NAME),
            	                   'annos.'.Annotations::COL_CE_HAS_IMAGE_ID . "=" . 'ceHim.' . CeHasImage::COL_ID);
            	$select->join(array('caex' => CalibrationExercise::TABLE_NAME),
                                   'ceHim.'. CeHasImage::COL_CALIBRATION_EXERCISE_ID . "=" . 'caex.' . CalibrationExercise::COL_ID);
            	$select->join(array('im' => Image::TABLE_NAME),
                                   'ceHim.'. CeHasImage::COL_IMAGE_ID . "=" . 'im.' . Image::COL_ID);
            	$select->where(CalibrationExercise::COL_EXPERTISE_ID."=?",$this->namespace->ceArray[0][CalibrationExercise::COL_EXPERTISE_ID]);
                $select->where(CalibrationExercise::COL_KEY_TABLE_ID."=?",$this->namespace->ceArray[0][CalibrationExercise::COL_KEY_TABLE_ID]);
                $select->where('im.' . Image::COL_ID . "=?",$imageId,'int');
            	$select->where('caex.' . CalibrationExercise::COL_WORKSHOP_ID . "=?",$ceArray[0][CalibrationExercise::COL_WORKSHOP_ID],'int');
                $select->where(Annotations::COL_WS_REF . "=?",1,'int');
            }else{
            	$fetch = false;
            }
		}else if ($state == 'webgr-refState'){
		    // check for user roles
            $storage = Zend_Auth::getInstance()->getStorage()->read();
            $roleConst = User::COL_ROLE;
            if ($storage->$roleConst == 'admin' || $storage->$roleConst == 'manager' ||$this->getParticipantRole() == 'Coordinator'){
                $select->join(array('ceHim' => CeHasImage::TABLE_NAME),
                                   'annos.'.Annotations::COL_CE_HAS_IMAGE_ID . "=" . 'ceHim.' . CeHasImage::COL_ID);
                $select->join(array('caex' => CalibrationExercise::TABLE_NAME),
                                   'ceHim.'. CeHasImage::COL_CALIBRATION_EXERCISE_ID . "=" . 'caex.' . CalibrationExercise::COL_ID);
                $select->join(array('im' => Image::TABLE_NAME),
                                   'ceHim.'. CeHasImage::COL_IMAGE_ID . "=" . 'im.' . Image::COL_ID);
                $select->where(CalibrationExercise::COL_EXPERTISE_ID."=?",$this->namespace->ceArray[0][CalibrationExercise::COL_EXPERTISE_ID]);
                $select->where(CalibrationExercise::COL_KEY_TABLE_ID."=?",$this->namespace->ceArray[0][CalibrationExercise::COL_KEY_TABLE_ID]);
                $select->where('im.' . Image::COL_ID . "=?",$imageId,'int');
                $select->where(Annotations::COL_WEBGR_REF . "=?",1,'int');
            }else{
                $fetch = false;
            }
		}
		//return $select->__toString();
        if ($fetch) {
            return $dbAdapter->fetchAll($select);;
        }else{
        	return array();
        }		
	}
	
	public function getAllAnnotationsByImageId($imageId,$state = null)
	{
		
		$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
		$select = $dbAdapter->select();
		$select->from('v_all_annotations');
		$compareAble = $this->namespace->ceArray[0][CalibrationExercise::COL_COMPAREABLE];
		
		if ($state == "browseState"){
			$compareAble = 1;
		}
		
		if (!($compareAble == 0)){
			$select->where(CeHasImage::COL_IMAGE_ID." =? ",$imageId);
			if ($state == null ){
			    $select->where(   $dbAdapter->quoteInto('(('.Annotations::COL_FINAL." = ? AND ",1).   
			                      $dbAdapter->quoteInto(Annotations::COL_PART_ID." != ?",$this->getParticipantId()));
			    $select->where(CalibrationExercise::COL_ID." =? )",$this->getCurrentCeID());
                $select->orWhere('('.CalibrationExercise::COL_EXPERTISE_ID."=?",$this->namespace->ceArray[0][CalibrationExercise::COL_EXPERTISE_ID]);
                $select->where(CalibrationExercise::COL_KEY_TABLE_ID."=? ",$this->namespace->ceArray[0][CalibrationExercise::COL_KEY_TABLE_ID]);
                $select->where(   $dbAdapter->quoteInto(Annotations::COL_WS_REF."=? OR ",1,'int').
                                  $dbAdapter->quoteInto(Annotations::COL_GROUP."=? OR ",1,'int').
                                  $dbAdapter->quoteInto(Annotations::COL_WEBGR_REF."=? ))",1,'int'));
	        }elseif ($state == 'groupState' ){
                $select->where( '('.Annotations::COL_FINAL." = ? ",1);
                $select->where(CalibrationExercise::COL_ID." =? )",$this->getCurrentCeID());
                $select->orWhere('('.CalibrationExercise::COL_EXPERTISE_ID."=?",$this->namespace->ceArray[0][CalibrationExercise::COL_EXPERTISE_ID]);
                $select->where(CalibrationExercise::COL_KEY_TABLE_ID."=? ",$this->namespace->ceArray[0][CalibrationExercise::COL_KEY_TABLE_ID]);
                $select->where(   $dbAdapter->quoteInto(Annotations::COL_WS_REF."=? OR ",1,'int').
                                  $dbAdapter->quoteInto(Annotations::COL_GROUP."=? OR ",1,'int').
                                  $dbAdapter->quoteInto(Annotations::COL_WEBGR_REF."=? )",1,'int'));
	        }else if ($state == 'ws-refState'){
                $select->where(CalibrationExercise::COL_WORKSHOP_ID." = ?",$this->namespace->ceArray[0][CalibrationExercise::COL_WORKSHOP_ID]);
                $select->where(CalibrationExercise::COL_EXPERTISE_ID."=?",$this->namespace->ceArray[0][CalibrationExercise::COL_EXPERTISE_ID]);
                $select->where(CalibrationExercise::COL_KEY_TABLE_ID."=?",$this->namespace->ceArray[0][CalibrationExercise::COL_KEY_TABLE_ID]);
                $select->where(   $dbAdapter->quoteInto(Annotations::COL_GROUP."=? OR ",1,'int').
	                              $dbAdapter->quoteInto(Annotations::COL_WS_REF."=? OR ",1,'int').
	                              $dbAdapter->quoteInto(Annotations::COL_WEBGR_REF."=?",1,'int'));	            
	        }else if ($state == 'webgr-refState'){
	            $select->where(CalibrationExercise::COL_EXPERTISE_ID."=?",$this->namespace->ceArray[0][CalibrationExercise::COL_EXPERTISE_ID]);
                $select->where(CalibrationExercise::COL_KEY_TABLE_ID."=?",$this->namespace->ceArray[0][CalibrationExercise::COL_KEY_TABLE_ID]);
                $select->where(   $dbAdapter->quoteInto(Annotations::COL_WS_REF."=? OR ",1,'int').
                                  $dbAdapter->quoteInto(Annotations::COL_WEBGR_REF."=?",1,'int'));
	        }else if($state == "browseState" && $this->namespace->ceArray != null){
	        	$select->where('('.Annotations::COL_FINAL." = ? ",1);
                $select->where(CalibrationExercise::COL_ID." =? )",$this->getCurrentCeID());
                $select->orWhere('('.CalibrationExercise::COL_ID." =? ",$this->getCurrentCeID());
                $select->where(CeHasImage::COL_IMAGE_ID." =? ",$imageId);
                $select->where(   '('.$dbAdapter->quoteInto(Annotations::COL_WS_REF."=? OR ",1,'int').
                                  $dbAdapter->quoteInto(Annotations::COL_GROUP."=? OR ",1,'int').
                                  $dbAdapter->quoteInto(Annotations::COL_WEBGR_REF."=? ))",1,'int'));
	        }else if($state == "browseState" && $this->namespace->ceArray == null){
                $select->where( '('.$dbAdapter->quoteInto(Annotations::COL_WS_REF."=? OR ",1).
                                  $dbAdapter->quoteInto(Annotations::COL_FINAL." = ? OR ",1).
                                  $dbAdapter->quoteInto(Annotations::COL_GROUP."=? OR ",1).
                                  $dbAdapter->quoteInto(Annotations::COL_WEBGR_REF."=? )",1));
	        }else{
	            return array();         
	        }
		    
            return $dbAdapter->fetchAll($select);
//          return $select->__toString();
            }
	}
}
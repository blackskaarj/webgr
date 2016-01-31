<?php

class annotation_MakeController extends Zend_Controller_Action {

    public function indexAction() {
 
    	$validator = new Zend_Validate_Int();
    	$ceId = $this->getRequest()->getParam(CalibrationExercise::COL_ID);
    	$error = false;
    	
    	if ($validator->isValid($ceId)) {
    	    	
    		$dbadapter = Zend_Registry::get('DB_CONNECTION1');
    		$select = $dbadapter->select();
                
	    		if (Default_ReferenceQuery::isParticipantInCe($ceId)){
	    			//set CE id
			    	$namespace = new Zend_Session_Namespace('default');
			    	$constCeId = CalibrationExercise::COL_ID;
			    	$namespace->$constCeId = $ceId;
			    	
			    	// Get part_id and part_role
					$auth = Zend_Auth::getInstance();
		            $storage = $auth->getStorage();
		            $constUserId = User::COL_ID;
					$userId = $storage->read()->$constUserId;
					
					$select->from(Participant::TABLE_NAME);
			    	$select->where(Participant::COL_USER_ID . " =?",$userId);
			    	$select->where(Participant::COL_CE_ID . " =?",$ceId);
			    	$array = $dbadapter->fetchAll($select);
			    	$constPartId = Participant::COL_ID;
		            $namespace->$constPartId = $array[0][Participant::COL_ID];
		            $constPartRole = Participant::COL_ROLE;
		            $namespace->$constPartRole = $array[0][Participant::COL_ROLE];
		            
		            //Set CE-info result Object
		            $select->reset();
		            $select->from(array('caex' => CalibrationExercise::TABLE_NAME));
		            $select->join(array('exp'=>Expertise::TABLE_NAME),
		                                'caex.'.CalibrationExercise::COL_EXPERTISE_ID . "=" . 'exp.' . Expertise::COL_ID,
		                                array());
		            $select->join(array('key'=>KeyTable::TABLE_NAME),
		                                'caex.'.CalibrationExercise::COL_KEY_TABLE_ID . "=" . 'key.' . KeyTable::COL_ID);
		            $select->join(  array('work'=>Workshop::TABLE_NAME),
		                                'caex.'.CalibrationExercise::COL_WORKSHOP_ID . "=" . "work." . Workshop::COL_ID,
		                            array(Workshop::COL_NAME));
                    $select->join(array ('vali' => ValueList::TABLE_NAME),
                                        'exp.'.Expertise::COL_SUBJECT.'=vali.'.ValueList::COL_ID,
                                        array(Expertise::COL_SUBJECT => ValueList::COL_NAME));
		            $select->where('caex.' . CalibrationExercise::COL_ID . "=?",$ceId);
		            $ceArray = $dbadapter->fetchAll($select);
			    	$namespace->ceArray = $ceArray;
			    	
			    	$this->view->subject = $ceArray[0][Expertise::COL_SUBJECT];
	    		} else {
	    			$error = true;
	    			$this->view->message =  "Your are not a participant of this CE.<br>".
	    			                        "Please contact one of the coordinators:<br>";
	    			$select->from(array('caex' => CalibrationExercise::TABLE_NAME));
	    			$select->join(array('part' => Participant::TABLE_NAME),
                                        'caex.' . CalibrationExercise::COL_ID . '=' . 'part.' . Participant::COL_CE_ID);
	    			$select->join(array('user' => User::TABLE_NAME),
                                        'part.' . Participant::COL_USER_ID . '=' . 'user.' . User::COL_ID);
	    			$select->where('part.' . Participant::COL_CE_ID . '=?',$ceId);
	    			$select->where('part.' . Participant::COL_ROLE . '=?','Coordinator');
	    			$infoArray = $dbadapter->fetchAll($select);
	    			$this->view->error = true;
	    			if(count($infoArray)!=0){
	    				$this->view->coordinators = $infoArray;
	    			} else {
	    				$this->view->message = "The CE doesn't exist.<br>";
	    				$this->view->coordinators = array();
	    			}
	    		}
    	}else{
    		throw new Zend_Exception('The CE id was not valid!');
    	}
    }

}
?>
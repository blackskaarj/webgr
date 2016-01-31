<?php
class Annotation_BrowseController extends Zend_Controller_Action {

	public $defaultNamespace;
	
    public function init(){
    	$this->defaultNamespace = new Zend_Session_Namespace('default');
    }
	
    public function indexAction()
    {
        	
    }
    
	public function byimageAction() 
	{
        $constCeId = CalibrationExercise::COL_ID;
        unset($this->defaultNamespace->ceArray);
    	$this->defaultNamespace->callingAction = 'annotation/browse/byimage';
    	
    	// redirect to the imageSearchform
    	$redirect = new Zend_Controller_Action_Helper_Redirector();
        $redirect->setGotoSimple('index','search','image');
    }
    
    public function byceAction() 
    {
    	$images = new Service_Image();
        $namespace = new Zend_Session_Namespace('default');
        $resultKey = new Ble422_Guid();
        $ceId = $this->getRequest()->getParam(CalibrationExercise::COL_ID);
        if($ceId != null){
        	//get images
            $namespace->$resultKey = $images->getImages($ceId);
            
            //getCeInfo
            //set CE id
            $constCeId = CalibrationExercise::COL_ID;
            $namespace->$constCeId = $ceId;
            
            //Set CE-info result Object
            $dbadapter = Zend_Registry::get('DB_CONNECTION1');
            $select = $dbadapter->select();
            $select->from(array('caex' => CalibrationExercise::TABLE_NAME));
            $select->join(array('exp'=>Expertise::TABLE_NAME),
                                'caex.'.CalibrationExercise::COL_EXPERTISE_ID . "=" . 'exp.' . Expertise::COL_ID);
            $select->join(array('key'=>KeyTable::TABLE_NAME),
                                'caex.'.CalibrationExercise::COL_KEY_TABLE_ID . "=" . 'key.' . KeyTable::COL_ID);
            $select->join(  array('work'=>Workshop::TABLE_NAME),
                                'caex.'.CalibrationExercise::COL_WORKSHOP_ID . "=" . "work." . Workshop::COL_ID,
                            array(Workshop::COL_NAME));
            $select->where('caex.' . CalibrationExercise::COL_ID . "=?",$ceId);
            $ceArray = $dbadapter->fetchAll($select);
            $namespace->ceArray = $ceArray;
        }else{
        	$redirect = new Zend_Controller_Action_Helper_Redirector();
            $redirect->setGotoSimple('index','index','default');
        }
        
        
        $this->view->resultKey = $resultKey->__toString();
        $this->render('start');
    }
    
//    public function byfishAction() 
//    {
//        
//        $this->defaultNamespace->callingAction = 'annotation/browse/byfish';
//        
//        // redirect to the imageSearchform
//        $redirect = new Zend_Controller_Action_Helper_Redirector();
//        $redirect->setGotoSimple('index','search','fish');
//    }
//    
//    public function byexpertiseAction() 
//    {
//        
//        $this->defaultNamespace->callingAction = 'annotation/browse/byexpertise';
//        
//        // redirect to the imageSearchform
//        $redirect = new Zend_Controller_Action_Helper_Redirector();
//        // todo $redirect->setGotoSimple('index','search','image');
//    }
//    
//    public function bykeyAction() 
//    {
//        
//        $this->defaultNamespace->callingAction = 'annotation/browse/bykey';
//        
//        // redirect to the imageSearchform
//        $redirect = new Zend_Controller_Action_Helper_Redirector();
//        // todo $redirect->setGotoSimple('index','search','image');
//    }
    
    public function startAction()
    {
        $this->view->resultKey = $this->getRequest()->getParam('resultKey');
    }
}
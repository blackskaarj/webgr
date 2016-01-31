<?php

class Service_IndexController extends Zend_Controller_Action {

    public function indexAction() {
 
//    	$server = new Zend_Amf_Server();
//		$server->setClass('Service_Image');
//    	$server->setClassMap('ImageVO', 'ImageVO');
//		$server->setProduction(false);
//		$server->handle();
		
    	$server = new Zend_Rest_Server();
		$server->setClass('Service_Image');
		$server->handle();
		
    }
    
    public function annotationAction()
    {
    	$server = new Zend_Rest_Server();
		$server->setClass('Service_Annotation');
		$server->handle();
		$this->render('index');
    }
    
	public function dotsAction()
    {
    	$server = new Zend_Rest_Server();
		$server->setClass('Service_Dots');
		$server->handle();
		$this->render('index');
    }
    
    public function infoAction()
    {
    	$server = new Zend_Rest_Server();
		$server->setClass('Service_Info');
		$server->handle();
		$this->render('index');
    }

    public function batchAction()
    {
    	$server = new Zend_Rest_Server();
    	//$test = new Service_Batch();
//    	$test->getSecurityKey();
        $server->setClass('Service_Batch');
        $server->handle();
        $this->render('index');
    }
}

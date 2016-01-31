<?php

class Image_BatchController extends Zend_Controller_Action {

    public function uploadAction()
    {
//        ini_set('display_errors', 'On');
//        ini_set('error_reporting', E_ALL);
        
    	$startPath = substr($_SERVER['DOCUMENT_ROOT'], 0, stripos($_SERVER['DOCUMENT_ROOT'], 'public/'));

        //create temporary directory
        $key = $this->getRequest()->getParam("key");
        $securityKey = $this->getRequest()->getParam("securityKey");
        
        if(sha1(Zend_Registry::get("SECURITY_KEY")) === $securityKey){
	        if($key !== "") {
		        mkdir($startPath . 'application/cache/batchuploads/' . $key . '/');
		        
		        if(move_uploaded_file($_FILES['Filedata']['tmp_name'], $startPath . 'application/cache/batchuploads/'. $key . '/' . $_FILES['Filedata']['name'])) {
		            //echo "The file ".  basename( $_FILES['Filedata']['name'])." has been uploaded";
		        } else{
		            //echo "There was an error uploading the file, please try again!";
		        }
	        }
        }
    }
    
    public function formAction()
    {
        
    }
}
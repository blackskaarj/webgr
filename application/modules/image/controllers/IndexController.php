<?php

class Image_IndexController extends Zend_Controller_Action {

    public function getimagefrombase64Action(){
    	$base64String = $this->getRequest()->getParam('base64String');
    	$this->view->base64String = $base64String;
    }
    
}
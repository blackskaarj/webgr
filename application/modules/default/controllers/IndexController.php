<?php
/**
 *
 * @name
 * @abstract   Diese Datei regelt vieles
 * @author     Ralf von der Mark (vdM) <vdM@zadi.de>
 * @copyright  Copyright (c) 2008, BLE, Ref. 421, Ralf von der Mark (vdM)
 * @version    Version vom 05.11.2008 um 15:27:28 Uhr
 *
 * @see     benennt die Scripte oder Funktionen, in denen diese Funktion aufgerufen wird
 * @example z.B.: blah blah
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */


class IndexController extends Zend_Controller_Action {

	public function indexAction() {
		$redirect = new Zend_Controller_Action_Helper_Redirector();
		$redirect->setGotoSimple('myce','search','ce');
	}
	public function searchAction() {
		//show links only
	}
	public function rightsAction() {
		//just the rights
	}
    public function imprintAction() {
//    	$this->_forward('rights','index');
//    	$this->_helper->actionStack('index', 'install');
        //just the imprint
    }
    public function welcomeAction() {
    	//just the same welcome message like on the login page
    	$this->render('login/index', null, true); 
    }
}

?>
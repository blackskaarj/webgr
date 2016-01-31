<?php
//just to create writable directory on server
class HelperController extends Zend_Controller_Action {
	public function makedirAction() {
		mkdir($_SERVER["DOCUMENT_ROOT"].'/protocols2', 0775);
		$this->render('index');
	}
}
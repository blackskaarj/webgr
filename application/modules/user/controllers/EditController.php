<?php

class User_EditController extends Zend_Controller_Action {

	private $form;
	private $defaultNamespace;
	private $userId;

	public function init() {
		$this->form = new User_Form_Edit();
		$this->form->removeElement(User::COL_ACTIVE);
		$this->form->removeElement(User::COL_PASSWORD);
		$this->form->removeElement(User_Form_Edit::PASSWORD_CLONE);
		$expElem = new Default_Form_Element_ExpertiseMulticheckbox(Expertise::COL_ID, array('label' => 'My expertise:', 'order' => 1));
		$this->form->addElement($expElem, Expertise::COL_ID);
		//set submit button as last element
		$this->form->getElement('submit')->setOrder($this->form->count());
		//#####################new###################################
        $this->form->setDecorators(array(
                'FormElements',
        array('HtmlTag', array('tag' => 'table', 'class' => 'login_form')),
        array('Description', array('placement' => 'prepend')),
                'Form'
                ));
        $this->form->setElementDecorators(array(
            'ViewHelper',
            'Errors',
                array(  'decorator' => array('td' => 'HtmlTag'),
                        'options' => array('tag' => 'td')),
                array(  'Label', array('tag' => 'td')),
                array(  'decorator' => array('tr' => 'HtmlTag'),
                        'options' => array('tag' => 'tr')),
                ));
        //###########################################################
		$this->view->form = $this->form;
		$this->defaultNamespace = new Zend_Session_Namespace('default');
		$this->userId = AuthQuery::getUserId();
	}

	public function myupdateAction() {
		$userTable = new User();
		$userHasExpTable = new UserHasExpertise();
			
		if ($this->getRequest()->isPost() && $this->getRequest()->getParam(User::COL_ID) == $this->userId){
			$params = $this->getRequest()->getParams();
			if($this->form->isValid($this->getRequest()->getParams())) {
				$data = array(  User::COL_CITY => $this->form->getValue(User::COL_CITY),
				User::COL_USERNAME => $this->form->getValue(User::COL_USERNAME),
				User::COL_EMAIL => $this->form->getValue(User::COL_USERNAME),
				User::COL_COUNTRY => $this->form->getValue(User::COL_COUNTRY),
				User::COL_FAX => $this->form->getValue(User::COL_FAX),
				User::COL_FIRSTNAME => $this->form->getValue(User::COL_FIRSTNAME),
				User::COL_LASTNAME => $this->form->getValue(User::COL_LASTNAME),
				User::COL_INSTITUTION => $this->form->getValue(User::COL_INSTITUTION),
				User::COL_PHONE => $this->form->getValue(User::COL_PHONE),
				User::COL_STREET => $this->form->getValue(User::COL_STREET));
				$expIds = $this->form->getValue(Expertise::COL_ID);
				$updateResult = 0;

				try{
					$updateResult = @$userTable->update($data,$userTable->getAdapter()->quoteInto(User::COL_ID . "=?",$this->form->getValue(User::COL_ID)));
					//delete all expertise datasets from user and insert checked expertise(s)

					$userHasExpTable->delete($userHasExpTable->getAdapter()->quoteInto(UserHasExpertise::COL_USER_ID.' = ?', $this->userId, 'int'));

					if (!empty($expIds)){
						foreach ($expIds as $expId) {
							$data = array(	UserHasExpertise::COL_USER_ID => $this->userId,
							UserHasExpertise::COL_EXPE_ID => $expId);
							$userHasExpTable->insert($data);
						}
					}
				}
				catch(Exception $e){
					if ($updateResult == 0){
						$this->view->message = 'Please try another username!';
					}
				}
			}
		} else {
				
			//get message e.g. password was changed
			$message = $this->getRequest()->getParam('message');
			Zend_Registry::set('MESSAGE',$message);
				
			$userResult = $userTable->find($this->userId)->current();
			$this->form->populate($userResult->toArray());
			$select = $userHasExpTable->select();
			$select->where(User::COL_ID.' = ?', $this->userId, 'int');
			$expResult = $userHasExpTable->fetchAll($select);
			if (!empty($expResult)) {
				$expIdArray = array();
				$expElem = array();
				foreach ($expResult as $expertiseDataset) {
					array_push($expIdArray, $expertiseDataset[UserHasExpertise::COL_EXPE_ID]);
				}
				$expElem[Expertise::COL_ID] = $expIdArray;
				$this->form->populate($expElem);
			}
		}
	}

	//copy from ForgotpasswordController but without form field/validation of user GUID
	public function myresetpasswordAction() {
		//remove all elements, only password and repeat stays
		$this->form = new User_Form_Edit();
		$elems = $this->form->getElements();
		foreach ($elems as $elem) {
			if (!(	$elem->getName() == User::COL_PASSWORD
			|| $elem->getName() == User_Form_Edit::PASSWORD_CLONE
			|| $elem->getName() == 'submit'
			)) {
				$this->form->removeElement($elem->getName());
			}
		}
		//#####################new###################################
        $this->form->setDecorators(array(
                'FormElements',
        array('HtmlTag', array('tag' => 'table', 'class' => 'login_form')),
        array('Description', array('placement' => 'prepend')),
                'Form'
                ));
        $this->form->setElementDecorators(array(
            'ViewHelper',
            'Errors',
                array(  'decorator' => array('td' => 'HtmlTag'),
                        'options' => array('tag' => 'td')),
                array(  'Label', array('tag' => 'td')),
                array(  'decorator' => array('tr' => 'HtmlTag'),
                        'options' => array('tag' => 'tr')),
                ));
        //###########################################################
		$this->view->form = $this->form;

		if ($this->getRequest()->isPost()) {
			if ($this->form->isValid($this->getRequest()->getParams())) {
				//update password
				$pass = $this->form->getValue(User::COL_PASSWORD);
				if ($pass != $this->getRequest()->getParam(User_Form_Edit::PASSWORD_CLONE)) {
					$element = $this->form->getElement(User_Form_Edit::PASSWORD_CLONE);
					$element->addError("Error: Your password and the repeating don't match.");
					$this->form->markAsError();
					return $this->render('form');
				}

				$user = new User();
				$select = $user->select();
				$where = $user->getAdapter()->quoteInto(User::COL_ID.' = ?', $this->userId, 'int');
				$select->where($where);
				$rowset = $user->fetchAll($select);
				if ($rowset->count() == 1) {
					$data = array(User::COL_PASSWORD => "{SHA}" . base64_encode( pack( "H*", sha1($pass))));
					try {
						$user->update($data, $where);
						$this->redirectTo('myupdate', array('message' => 'password was changed'));
					}
					catch (Exception $e) {
						throw new Zend_Controller_Action ('Error: Password was not changed');
					}
				}

			} else {
				//not valid
				$this->render('form');
			}
		} else {
			//not post
			$this->render('form');
		}
	}

	public function myimagesAction() {
		$this->defaultNamespace->callingAction = 'user/edit/myimages';
		$this->defaultNamespace->callingActionId = $this->userId;
		$redirect = new Zend_Controller_Action_Helper_Redirector();
		$redirect->setGoto('index','search','image');
	}

	public function myfishesAction() {
		$this->defaultNamespace->callingAction = 'user/edit/myfishes';
		$this->defaultNamespace->callingActionId = $this->userId;
		$redirect = new Zend_Controller_Action_Helper_Redirector();
		$redirect->setGoto('index','search','fish');
	}

	public function deleteAction() {
		//set user data rows to NULL/0/empty strings, leave ID/GUID
		//delete expertise
		$table = new User();
		$rowset = $table->find($this->userId);

		if (count($rowset) == 1) {
			$data = array(
			User::COL_ACTIVE => 0,
			User::COL_CITY => NULL,
			User::COL_COUNTRY => NULL,
			User::COL_EMAIL => '',
			User::COL_FAX => NULL,
			User::COL_FIRSTNAME => '',
			User::COL_INSTITUTION => NULL,
			User::COL_LASTNAME => '',
			User::COL_PASSWORD => '',
			User::COL_PHONE => NULL,
			User::COL_ROLE => NULL,
			User::COL_STREET => NULL,
			User::COL_USERNAME => uniqid('')
			);
			$where = $table->getAdapter()->quoteInto(User::COL_ID.' = ?', $this->userId, 'int');
			$table->update($data, $where);

			$table = new UserHasExpertise();
			$table->delete($table->getAdapter()->quoteInto(UserHasExpertise::COL_USER_ID.' = ?', $this->userId, 'int'));

			//logout
			$redirect = new Zend_Controller_Action_Helper_Redirector();
			$redirect->setGoto('logout', 'login', 'default');
		}
	}

	public function redirectTo($action,$params = array())
	{
		$Redirect = new Zend_Controller_Action_Helper_Redirector();
		$Redirect->setGotoSimple($action,'edit','user',$params);
	}
}
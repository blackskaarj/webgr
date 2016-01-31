<?php
class ForgotpasswordController extends Zend_Controller_Action
{
	private $form;
	public function indexAction()
	{
		//remove all elements, only username (=e-mail) stays
		$this->form = new User_Form_Edit();
		$elems = $this->form->getElements();
		foreach ($elems as $elem) {
			if (!(	$elem->getName() == User::COL_USERNAME
			|| $elem->getName() == 'submit')) {
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
		if ($this->getRequest()->isPost()) {
			if ($this->form->isValid($this->getRequest()->getParams())) {
				//lookup if e-mail exists and send mail

				$e_mail = $this->form->getValue(User::COL_USERNAME);

				if (Default_SimpleQuery::isValueInTableColumn($e_mail, new User(), User::COL_USERNAME, 'string')) {
					$user = new User();
					$select = $user->select();
					$where = $user->getAdapter()->quoteInto(User::COL_USERNAME.' = ?', $e_mail, 'string');
					$select->where($where);
					$rowset = $user->fetchAll($select);
					if ($rowset->count() == 1) {
						$newGuid = Ble422_Guid::getGuid();

						$data = array(User::COL_GUID => $newGuid);
						$user->update($data, $where);

	                    $toAdress = $this->form->getValue(User::COL_USERNAME);
	                    $host = Zend_Registry::get('APP_HOST');
	                    $bodyText = 'Please click this link to reset your password:'."\r\n".
                        $host.'/default/forgotpassword/myresetpassword/'.
                        User::COL_GUID.'/'.$newGuid;
                        
						$mail = new Default_Mail( $toAdress,
                                                  'WebGR forgot password message',
                                                  $bodyText);
                        $mail->send();
					}


				}
				//show message anyway, not depending on success

				Zend_Registry::set('MESSAGE','if you are known to the system, the message was sent');
				$this->view->message = 'if you are known to the system, the message was sent';
				$this->render('message');
					
			} else {
				//not valid
				$this->view->form = $this->form;
			}
		} else {
			//not post
			$this->view->form = $this->form;
		}
		//prevent robots and abuser to:
		//-	send e-mail to all possible e-mail adresses
		//-	send massive multiple e-mails to known adress
		//-	e-mail server dos

		//lookup if e-mail exists and send mail
		//show message anyway, not depending on success
	}


	public function myresetpasswordAction() {
		//check GUID param with database

		//remove all elements, only password and repeat stays
		$this->form = new User_Form_Edit();
		$elems = $this->form->getElements();
		foreach ($elems as $elem) {
			if (!(	$elem->getName() == User::COL_PASSWORD
			|| $elem->getName() == User_Form_Edit::PASSWORD_CLONE
			|| $elem->getName() == 'submit'
			|| $elem->getName() == User::COL_GUID)) {
				$this->form->removeElement($elem->getName());
			}
		}

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

				/*
				 credit: http://www.geekzilla.co.uk/view8AD536EF-BC0D-427F-9F15-3A1BC663848E.htm
				 Author  	: Paul Hayman
				 Published 	: 14 June 2006
				 */
				$regexStringGuid = "^(\{{0,1}([0-9a-fA-F]){8}-([0-9a-fA-F]){4}-([0-9a-fA-F]){4}-([0-9a-fA-F]){4}-([0-9a-fA-F]){12}\}{0,1})$^";
				$vali = new Zend_Validate_Regex($regexStringGuid);
				
				$userGuid = $this->getRequest()->getParam(User::COL_GUID);
				if ($vali->isValid($userGuid)) {
					$user = new User();
					$select = $user->select();
					$where = $user->getAdapter()->quoteInto(User::COL_GUID.' = ?', $userGuid, 'string');
					$select->where($where);
					$rowset = $user->fetchAll($select);
					if ($rowset->count() == 1) {
						$data = array(User::COL_PASSWORD => "{SHA}" . base64_encode( pack( "H*", sha1($pass))),
						              User::COL_GUID => null);
						try {
							$user->update($data, $where);
						}
						catch (Exception $e) {
							throw new Exception ('Error: Password was not changed'.$e->getMessage());
						}
					} else {
						//no or too much users with this GUID!
						//TODO log in IDS?
					}
				} else {
					//param != GUID
					//TODO log in IDS?
				}
				$this->view->message = 'password was changed';
				$this->render('message');
			} else {
				//not valid
				$this->render('form');
			}
		} else {
			//not post
			$userGuid = $this->getRequest()->getParam(User::COL_GUID);
			$this->form->setValues(array (User::COL_GUID => $userGuid));
			$this->view->form = $this->form;
			$this->render('form');
		}
	}
}
<?php

class RegisteruserController extends Zend_Controller_Action
{
	private $form;

	public function init()
	{
		$this->form = new User_Form_Edit();

		$this->form->removeElement(User::COL_ID);
		$this->form->removeElement(User::COL_ACTIVE);

		$helper = new Ble422_Zend_Form_Helper();
		$helper->markRequiredElements($this->form);
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
	}

	public function indexAction()
	{
		//TODO: Überprüfung auf Passwort und unique E-Mail auch in EditController
		$namespace = new Zend_Session_Namespace('user');
		if ($this->getRequest()->isPost()
		AND $this->form->isValid($this->getRequest()->getParams())) {
			if ($this->form->getValue('Token') == $namespace->Token) {

				//get parameters for test of unique username
				$userTable = new User();
				$tableRow = User::COL_USERNAME;
				$value = $this->getRequest()->getParam(User::COL_USERNAME);
					
				if ($this->getRequest()->getParam(User::COL_PASSWORD) != $this->getRequest()->getParam(User_Form_Edit::PASSWORD_CLONE)) {
					$element = $this->form->getElement(User_Form_Edit::PASSWORD_CLONE);
					$element->addError("Error: Your password and the repeating don't match.");
					$this->form->markAsError();
					return $this->render('index');
				}

				//test of unique e-mail
				//TODO prevent enumeration of data to get user e-mail adresses
				elseif (Default_SimpleQuery::isValueInTableColumn($value, $userTable, $tableRow, 'string')) {
					$element = $this->form->getElement(User::COL_USERNAME);
					$element->addError("Error: This username is already used.");
					$this->form->markAsError();
					return $this->render('index');
				} else {
					try {
					    //values checked, insert
    					$guid = Ble422_Guid::getGuid();
    					$userTable = new User();
					    $userTable->getAdapter()->beginTransaction();
					    
					    $userId = $userTable->insert(array(	User::COL_USERNAME => $this->form->getValue(User::COL_USERNAME),
    					User::COL_FIRSTNAME => $this->form->getValue(User::COL_FIRSTNAME),
    					User::COL_LASTNAME => $this->form->getValue(User::COL_LASTNAME),
    					User::COL_PASSWORD => "{SHA}" . base64_encode( pack( "H*", sha1( $this->form->getValue(User::COL_PASSWORD)))),
    					//username = e-mail adress
    					User::COL_EMAIL => $this->form->getValue(User::COL_USERNAME),
    					User::COL_INSTITUTION => $this->form->getValue(User::COL_INSTITUTION),
    					User::COL_STREET => $this->form->getValue(User::COL_STREET),
    					User::COL_COUNTRY => $this->form->getValue(User::COL_COUNTRY),
    					User::COL_PHONE => $this->form->getValue(User::COL_PHONE),
    					User::COL_FAX => $this->form->getValue(User::COL_FAX),
    					User::COL_CITY => $this->form->getValue(User::COL_CITY),
    					User::COL_GUID => $guid,
    					User::COL_ACTIVE => 0));
    						
    					$toAdress = $this->form->getValue(User::COL_USERNAME);
    					$bodyText = "Please click this link to confirm your new account:\r\n".
    					           Zend_Registry::get('APP_HOST').'/default/registeruser/confirm/'.User::COL_GUID.'/'.$guid;
    					$mail = new Default_Mail(   $toAdress,
                                                    'WebGR register user message',
                                                    $bodyText);                                                
    					$mail->send();
    					
    					$userTable->getAdapter()->commit();
    					
    					$namespace->Token = '';
    					$this->redirectTo('success');
					} catch (Exception $e) {
					    $userTable->getAdapter()->rollBack();
					    throw new Exception('error at register a new user: '.$e->getMessage());
					}
					
				}
			} else {
				//form token is not equal session token
				$this->form->reset();
				//TODO redirect
				$this->redirectTo('success');
			}
		} else 	{
			//no post or some element(s) not valid
			//$this->form->setAction(Zend_Controller_Front::getInstance()->getBaseUrl()."/user/new");
			if ($this->form->getValue('Token') == null) {
				$guid = new Ble422_Guid();
				$namespace->Token = $guid->__toString();
				$this->form->getElement('Token')->setValue($guid->__toString());
			}
		}
	}

	public function successAction()
	{
			
	}

	public function confirmAction()
	{
		$guid = $this->getRequest()->getParam(User::COL_GUID);
		$userTable = new User();
		$dbAdapter = $userTable->getAdapter();
		$result = $userTable->update(array( User::COL_ACTIVE => 1,
		User::COL_GUID => null),
		$dbAdapter->quoteInto(User::COL_GUID . '=?',$guid));
		if($result == 1){
			$this->view->message = 'User confirmed!';
		}else{
			$this->view->message = 'User not confirmed!';
		}
	}

	public function redirectTo($action)
	{
		$Redirect = new Zend_Controller_Action_Helper_Redirector();
		$Redirect->setGoto($action,'registeruser','default');
	}

	private function autoParticipateInCe ($ceId, $userId)
	{
		$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
		$part = new Participant();

		//SELECT MAX(column_name) FROM table_name where partceid=ceid
		$select = $dbAdapter->select();
		$select->from(Participant::TABLE_NAME, array('max' => 'MAX('.Participant::COL_NUMBER.')'));
		$select->where(Participant::COL_CE_ID.' = ?', $ceId);
		$resultArray = $dbAdapter->fetchAll($select);
		$select->reset();

		//reader number for new CE part. is highest reader no. of old participents within CE + 1
		$readerNo = $resultArray[0]['max'] + 1;
		$data = array (	Participant::COL_CE_ID => $ceId,
		Participant::COL_USER_ID => $userId,
		Participant::COL_NUMBER => $readerNo);
		$part->insert($data);
	}
}
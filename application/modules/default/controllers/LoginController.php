<?php
/**
 * Sie sehen hier den loginController. Was er macht? Keine weiss es!
 *
 * @name       loginController.php
 * @abstract   siehe oben
 * @author     Ralf von der Mark (vdM) <vdM@zadi.de>
 * @copyright  Copyright (c) 2008, BLE, Ref. 421, Ralf von der Mark (vdM)
 * @version    Version vom 05.11.2008 um 11:57:49 Uhr
 *
 * @see        benennt die Scripte oder Funktionen, in denen diese Funktion aufgerufen wird
 * @todo       beschreibt die noch offenen Aufgaben
 * @example    z.B.: blah blah
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */


class LoginController extends Zend_Controller_Action
{
    public function getForm()
    {
    	$form = new Default_Form_Login();
    	
        $form->setAction(Zend_Controller_Front::getInstance()->getBaseUrl().'/login/process/returnUrl/'.$this->getRequest()->getParam('returnUrl'));
		$form->setMethod('post');
		return $form;
    }
	/**
	 * Enter description here...
	 *
	 * @param array $params
	 * @return Zend_Auth_Adapter_DbTable
	 */
    public function getAuthAdapter(array $params)
    {
    	$auth = new Zend_Auth_Adapter_DbTable(Zend_Registry::get('DB_CONNECTION1'),User::TABLE_NAME,User::COL_EMAIL,User::COL_PASSWORD);
        $codedPassword = "{SHA}" . base64_encode( pack( "H*", sha1( $params[User::COL_PASSWORD] ) ) );
        //$codedPassword = sha1($params[User::COL_PASSWORD]);
    	$auth->setCredential($codedPassword);
        $auth->setIdentity($params[User::COL_EMAIL]);
    	return $auth;
    }
	public function indexAction()
    {
        if(Zend_Registry::get("SECURITY_KEY") == "write what ever you want but make it unique!"){
        	$this->view->warning = '<h3 style="color:red">Warning: You have to change your security key in your config file!</h3>';
        }
    	$this->view->form = $this->getForm();
    }
	public function processAction()
    {
        $request = $this->getRequest();

        // Check if we have a POST request
        if (!$request->isPost()) {
            return $this->_helper->redirector('index');
        }

        // Get our form and validate it
        $form = $this->getForm();
        if (!$form->isValid($request->getPost())) {
            // Invalid entries
            $this->view->form = $form;
            return $this->render('index');
        }
        
        //check wether the user is active rather confirmed
        $dbAdapter = Zend_Registry::get('DB_CONNECTION1');
        $userSelect = $dbAdapter->select();
        $userSelect->from(User::TABLE_NAME);
        $userSelect->where(User::COL_USERNAME .'=?',$form->getValue(User::COL_EMAIL));
        $userResult = $dbAdapter->fetchAll($userSelect);
        if($userResult == array() || intval($userResult[0][User::COL_ACTIVE])==0){
        	$form->setDescription('Please confirm your account or inform the admin.');
            $this->view->form = $form;
            return $this->render('index'); // re-render the login form
        }

        // Get our authentication adapter and check credentials
        $adapter = $this->getAuthAdapter($form->getValues());
        $auth    = Zend_Auth::getInstance();
        $result  = $auth->authenticate($adapter);
        if (!$result->isValid()) {
            // Invalid credentials
            $form->setDescription("Username or password doesn't match!");
            $this->view->form = $form;
            return $this->render('index'); // re-render the login form
        }
		$auth->getStorage()->write($adapter->getResultRowObject());
		
        $namespace = new Zend_Session_Namespace('default');
		$namespace->auth = $auth;
		
		
        // We're authenticated!
        $Redirect = new Zend_Controller_Action_Helper_Redirector();
		$returnUrl = str_replace(' ','/',$this->getRequest()->getParam('returnUrl'));
        $defaultNamespace = new Zend_Session_Namespace('default');
        foreach ($defaultNamespace->userParams as $key => $value) {
        	$userParamsUrl = '/'.$key.'/'.$value;
        }
        
		$Redirect->setGotoUrl($returnUrl.$userParamsUrl);
    }
	public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        $namespace = new Zend_Session_Namespace('default');
		$namespace->auth = null;
		$namespace->acl = null;
        $Redirect = new Zend_Controller_Action_Helper_Redirector();
		$Redirect->setGoto('index','index','default');
    }
    public function resetPassword() {
        ;//Noch NIX!
    }//ENDE: public function resetPassword()


}
?>

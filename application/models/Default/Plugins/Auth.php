<?php
/**
 * 
 * @author Norman Rauthe BLE Referat 422
 * @version 1.0
 * @package default
 * @subpackage plugins
 *
 */
class Default_Plugins_Auth extends Zend_Controller_Plugin_Abstract
{
    private $namespace;
	
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
		$this->namespace = new Zend_Session_Namespace('default');
    	$namespace = $this->namespace;        
        $modulName = strtoupper($this->getRequest()->getModuleName());
        $controllerName = strtoupper($this->getRequest()->getControllerName());
        $actionName = strtoupper($this->getRequest()->getActionName());
        
        /**
         * access controle
         */
        if ($modulName == 'DEFAULT' ){
        	//no authentification needed
        }else{
            if($modulName == 'IMAGE' AND $controllerName == 'BATCH' AND $actionName == 'UPLOAD') {
                //no authentification needed
            }else{
	        	if (!$namespace->auth) {
		        		$this->redirectTo();
		        }else {
		        	$auth = $namespace->auth;
		        	if($auth->hasIdentity()){
		        		$acl = new Default_AclUser();
		        		$storage = $auth->getStorage()->read();
		        		$roleConst = User::COL_ROLE;
		  				$rolename = $storage->$roleConst;
		        		
		        		if( $acl->has($modulName."-".$controllerName) AND 
		        		    $acl->isAllowed($rolename,$modulName."-".$controllerName,$actionName)){
		        			
		        		}else {
		        			$this->redirectTo();
		        		}
		        		
		        	}else {
		        		$this->redirectTo();
		        	}
		        }
            }
        }	        
    }    
    public function redirectTo()
    {
    	$request = $this->getRequest();
	    $returnUrl = $request->getModuleName().' ';
	    $returnUrl .= $request->getControllerName().' ';
	    $returnUrl .= $request->getActionName().' ';
	    
		$request->setActionName('index');
		$request->setControllerName('login');
		$request->setModuleName('default');
		$allParams = $request->getParams();
		$userParams = array();
		foreach ($allParams as $key => $value) {
			if (!($key == 'module'||$key == 'action' || $key == 'controller')) {
				$userParams = array_merge($userParams,array($key => $value));
			}
		}
		$namespace = $this->namespace;
		$namespace->userParams = $userParams;
		$request->setParams((array('returnUrl'=>$returnUrl)));
    }
    
}

?>
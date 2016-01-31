<?php
/**
 *
 * @author Norman Rauthe BLE Referat 422
 *
 */

use IDS\Init;
use IDS\Monitor;
use IDS\Log\CompositeLogger;
use IDS\Log\FileLogger;

class Default_Plugins_Security extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
    	$tmpPath = substr($_SERVER['DOCUMENT_ROOT'], 0, stripos($_SERVER['DOCUMENT_ROOT'], 'public'));
        
    	$init = Init::init($tmpPath.'application/config/IdsConfig.ini');
        
    	$init->config['General']['base_path'] = $tmpPath . 'application/cache/ids/';
    	$ids = new Monitor($init);
        
        /*
        * Please keep in mind what array_merge does and how this might interfer
        * with your variables_order settings
        */
        $params = array(
            'REQUEST' => $_REQUEST,
            'GET' => $_GET,
            'POST' => $_POST,
            'COOKIE' => $_COOKIE
        );
        
    	$result = $ids->run($params);

    	if (!$result->isEmpty()) {
    		//TODO ab welcher Stufe wird es als Bedrohung eingestuft?
    		$request->setActionName('intrusion');
    		$request->setControllerName('error');
    		$request->setModuleName('default');
    	}
    }
}
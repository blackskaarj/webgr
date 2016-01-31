<?php
/**
 *
 * @author Norman Rauthe BLE Referat 422
 * @version 1.0
 * @package Model
 * @subpackage Ble422
 *
 */
class Default_Plugins_Db extends Zend_Controller_Plugin_Abstract
{
	public function __construct()
    {
        //get config from registry
        $config = Zend_Registry::get('CONFIG');
    	
    	// erstellen des Datenbankadapters
        $dbAdapter = Zend_Db::factory($config->DB_CONNECTION1->dbAdapterTyp,$config->DB_CONNECTION1);
        
        // erstellen der registry
        $registry = Zend_Registry::getInstance();
        
        // speichern von Werten bzw. Objekten in der registry
        Zend_Db_Table_Abstract::setDefaultAdapter($dbAdapter);
        $registry->set('DB_CONNECTION1',$dbAdapter);
    }
    
    public function dispatchLoopShutdown()
    {
    	Zend_Registry::get('DB_CONNECTION1')->closeConnection();
    }
}
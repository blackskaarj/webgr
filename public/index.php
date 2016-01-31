<?php
/** ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
 *
 * @name       	index.php
 * @abstract   	Bootstrap for Zend Framework 
 * @author     	Norman Rauthe, Ingmar Pforr
 * @copyright  	Copyright (c) 2010, BLE
 * @version    	1.0.0 18.01.2010
 *
 * ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ */
error_reporting(E_ALL);//nur waehrend der Entwicklungsphase!!

set_include_path( '.' . PATH_SEPARATOR . "../library/"
                      . PATH_SEPARATOR . '../application/models/'
                      . PATH_SEPARATOR . get_include_path() );

                      
//Controller erstellen:
require_once 'Zend/Controller/Front.php';
require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->setFallbackAutoloader(true);

$controller = Zend_Controller_Front::getInstance();
$controller ->setControllerDirectory('../application/controllers/')
           // for development choose true
           ->throwExceptions(true)
           ->addModuleDirectory('../application/modules')
           ->registerPlugin(new Default_Plugins_Config())
           ->registerPlugin(new Default_Plugins_Db())
           ->registerPlugin(new Default_Plugins_Layout())
           ->registerPlugin(new Default_Plugins_Auth())
           ->registerPlugin(new Default_Plugins_Security())
           ->setBaseUrl();///webgr_php/public/
//run
$controller->dispatch();
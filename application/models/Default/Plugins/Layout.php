<?php
/**
 *
 * @author Norman Rauthe BLE Referat 422
 * @version 1.0
 * @package Model
 * @subpackage Ble422
 *
 */
class Default_Plugins_Layout extends Zend_Controller_Plugin_Abstract
{
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        //starten des Zend_Layouts
        $layout = Zend_Layout::startMvc(array(
            'layoutPath' => '../application/modules/default/views/layouts'
            ));
		$contollerName = $request->getControllerName();
		$modulName = $request->getModuleName();
		
		if (  $contollerName == 'make' AND $modulName == 'annotation' OR 
		      $contollerName == 'browse' AND $modulName == 'annotation' AND $request->getActionName() != 'index') {
			$layout->disableLayout();//setLayout('flexlayout');
		}elseif ($modulName == 'service') {
			$layout->disableLayout();
		}elseif ($modulName == 'image' AND $contollerName == 'index'){
		    $layout->disableLayout();	
		}else {
			$layout->setLayout('layout');
		}
        // der view Voreinstellungen übergeben
        $view = $layout->getView();
        $view->doctype('XHTML1_TRANSITIONAL');
        $view->headLink(array('href'=>'/styles/index.css','rel'=>'stylesheet','type'=>'text/css','media'=>'screen'));
        $view->headLink(array('href'=>'/images/website/favicon.ico','rel'=>'shortcut icon'));
        $view->headTitle(Zend_Registry::get('APP_NAME'));
        //??$view->headMeta()->appendName('http-equiv','text/html; charset=utf-8');
        
        // register the MESSAGE key
        $registry = Zend_Registry::getInstance();
        $registry->MESSAGE = '';
    }
}

?>
<?php
/**
 *
 * @author Norman Rauthe BLE Referat 422
 * @version 1.0
 * @package Model
 * @subpackage Ble422
 *
 */
class Default_Plugins_Translate extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        // Die CSV-Dateien laden
        $translate = new Zend_Translate('csv','../application/config/lang.de.csv','de');
        $translate->addTranslation('../application/config/lang.en.csv','en');
        
        // sprache einstellen
        $translate->setLocale('de');
        
        // translateobjekt in der registry speichern
        $registry = Zend_Registry::getInstance();
        $registry->set('translate',$translate);
        
        // für alle Formular der Klasse Zend_Form das standard translate objekt setzen
        Zend_Form::setDefaultTranslator($translate);
             
    }
}

?>
<?php
/**
 *
 * @author Norman Rauthe BLE Referat 422
 * @version 1.0
 * @package Model
 * @subpackage Ble422
 *
 */
class Default_Plugins_Config extends Zend_Controller_Plugin_Abstract
{
    public function __construct()
    {
        // laden der config.ini
        $config = new Zend_Config_Ini('../application/config/_config.ini');
        
        // erstellen der registry
        $registry = Zend_Registry::getInstance();
        
        // speichern von Werten bzw. Objekten in der registry
        $registry->set('CONFIG',$config);
        $registry->set('APP_NAME',$config->APPLICATION->appName);
        $registry->set('APP_HOST',$config->APPLICATION->appHost);
        $registry->set('VERSION',$config->APPLICATION->version);
        $registry->set('SECURITY_KEY',$config->APPLICATION->securityKey);
        $registry->set('MAIL_CONF',$config->MAIL_CONF);
    }
}

?>
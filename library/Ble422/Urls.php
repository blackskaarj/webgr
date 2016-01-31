<?php
/**
 * Klasse zur Erstellung von URLstrings.
 * 
 * @author Norman Rauthe BLE Referat 422
 * @version 2.0
 * @package Model
 * @subpackage Ble422
 *
 */
class Ble422_Urls
{	
	/**
	 * Das zu übergebende Requestobjekt.
	 *
	 * @var Zend_Controller_Request_Abstract
	 */
    private $request;
	
	/**
	 * Der Konstruktor der Klasse, speichert den Actioncontroller
	 *
	 * @param Zend_Controller_Request_Abstract $request
	 * @return Ble422_Urls
	 */
	public function Ble422_Urls(Zend_Controller_Request_Abstract $request )
	{
		$this->request = $request;
	}
	
	/**
	 * Gibt die Komplette RequestUrl zurück.
	 * Endet mit /
	 *
	 * @return string
	 */
	public function getRequestUrl()
	{
	    $request = $this->request;
	    $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
	    $params = $request->getParams();
	    
	    $requestUrlString = $baseUrl."/"
	                       .$params['module']."/"
	                       .$params['controller']."/"
	                       .$params['action']."/";
	                       
	    foreach ($params as $key => $value) {
	        if (($key != 'module') XOR ($key != 'controller') XOR ($key != 'action')) {
	            $requestUrlString .= $key."/".$value."/";
	        }
	    }

	    return $requestUrlString;
	}
	
	/**
     * Gibt die RequestUrl ohne die optionalen Parameter zurück.
     * Endet mit /
     *
     * @return string
     */
	public function getUrlWithoutParams()
	{
	    $request = $this->request;
        $baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        $params = $request->getParams();
        
        $urlString = $baseUrl."/"
                    .$params['module']."/"
                    .$params['controller']."/"
                    .$params['action']."/";
                           
        return $urlString; 
	}
	
	/**
     * Gibt die RequestUrl nur der optionalen Parameter zurück.
     * Endet mit /
     *
     * @return string
     */
	public function getParamsUrl()
	{
        $request = $this->request;
		$params = $request->getParams();
		$paramUrlString = '';
        
        foreach ($params as $key => $value) {
            if ($key != 'module' XOR $key != 'controller' XOR $key != 'action' ) {
                $paramUrlString .= $key."/".$value."/";
            }
        }

        return $paramUrlString;
	}
	
	public function getTableParams()
	{
		$params = $this->request->getParams();
		$paramUrlString = '';
		
		foreach ($params as $key => $value) {
		    if ($key == 'orderBy' OR $key == 'page' ) {
                $paramUrlString .= $key."/".$value."/";
            }
		}
		return $paramUrlString;
	}
}
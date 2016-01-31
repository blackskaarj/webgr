<?php
class CeHasAttributeDescriptor extends Zend_Db_Table_Abstract  
{

    /**
     * the physical tablename 
     * @var string
     */
	protected $_name ='caex_has_atde';
	
	/**
	 * the physical name of the primary key
	 * @var string
	 */
    protected $_primary = array('CAEX_ID','ATDE_ID');

    const TABLE_NAME = 'caex_has_atde';
    const COL_CAEX_ID = 'CAEX_ID';
    const COL_ATDE_ID = 'ATDE_ID';
    
    /**
     * The constructos implements a Zend_DB_Adapter from the
     * Zend_Registry
     *
     */
    function __construct() 
    {
        parent::__construct(array('db' => 'DB_CONNECTION1'));
    }//ENDE: function ...
    
    public function getTableName()
    {
    	return $this->_name;
    }
}//ENDE: class ...



?>
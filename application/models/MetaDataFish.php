<?php
class MetaDataFish extends Zend_Db_Table_Abstract  
{
	const TABLE_NAME = 'meta_data_fish';
    const COL_ID = 'MEDFI_ID';
    const COL_ATTRIBUTE_DESCRIPTOR_ID = 'ATDE_ID';
    const COL_FISH_ID = 'FISH_ID';
    const COL_VALUE = 'MEDFI_VALUE';
    
    /**
     * the physical tablename 
     * @var string
     */
	protected $_name = self::TABLE_NAME;
	
	/**
	 * the physical name of the primary key
	 * @var string
	 */
    protected $_primary = self::COL_ID;
    
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
<?php
class ImagesetAttributes extends Zend_Db_Table_Abstract  
{
    const TABLE_NAME = 'imageset_attributes';
    const COL_ID = 'IMAT_ID';
    const COL_ATTRIBUTE_DESCRIPTOR_ID = 'ATDE_ID';
    const COL_CE_ID = 'CAEX_ID';
    const COL_VALUE_LIST_ID = 'VALI_ID';
    const COL_VALUE = 'VALUE';
    const COL_FROM = 'IMAT_FROM';
    const COL_TO = 'IMAT_TO';
    
    
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
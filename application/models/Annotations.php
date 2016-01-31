<?php
class Annotations extends Zend_Db_Table_Abstract  {

    const TABLE_NAME = 'annotations';
    const COL_ID = 'ANNO_ID';
    const COL_CE_HAS_IMAGE_ID = 'CEhIM_ID';
	const COL_PARENT_ID = 'PARENT_ID';
    const COL_PART_ID = 'PART_ID';
    const COL_COMMENT = 'ANNO_COMMENT';
    const COL_DATE = 'ANNO_DATE';
    const COL_GROUP = 'ANNO_GROUP';
    const COL_WS_REF = 'ANNO_WS_REF';
    const COL_WEBGR_REF = 'ANNO_WEBGR_REF';
    const COL_COUNT = 'ANNO_COUNT';
    const COL_DECIMAL = 'ANNO_DECIMAL';
    const COL_SUB = 'ANNO_SUB';
    const COL_BRIGHTNESS = 'ANNO_BRIGHTNESS';
    const COL_CONTRAST = 'ANNO_CONTRAST';
    const COL_COLOR = 'ANNO_COLOR';
    const COL_MAGNIFICATION = 'ANNO_MAGNIFICATION';
    const COL_FINAL = 'ANNO_FINAL';
    
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
 	public function __construct() 
    {
        parent::__construct(array('db' => 'DB_CONNECTION1'));
    }//ENDE: function ...
    
    public function getTableName() 
    {
    	return $this->_name;
    }
}//ENDE: class ...
?>
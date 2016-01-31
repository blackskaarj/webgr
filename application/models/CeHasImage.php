<?php

class CeHasImage extends Zend_Db_Table_Abstract  {

    const TABLE_NAME = 'ce_has_image';
    const COL_ID = 'CEhIM_ID';
    const COL_CALIBRATION_EXERCISE_ID = 'CAEX_ID';
    const COL_IMAGE_ID = 'IMAGE_ID';
    
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
    function __construct() {
        parent::__construct(array('db' => 'DB_CONNECTION1'));
    }
    
    public function getTableName()
    {
    	return $this->_name;
    }    
}
?>
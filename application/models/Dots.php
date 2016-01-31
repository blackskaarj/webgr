<?php
class Dots extends Zend_Db_Table_Abstract  {

    const TABLE_NAME = 'dots';
    const COL_ID = 'DOTS_ID';
    const COL_ANNO_ID = 'ANNO_ID';
    const COL_DOTS_X = 'DOTS_X';
    const COL_DOTS_Y = 'DOTS_Y';
    const COL_SEQUENCE = 'DOTS_SEQUENCE';

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
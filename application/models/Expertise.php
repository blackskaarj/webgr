<?php
class Expertise extends Zend_Db_Table_Abstract  
{

    const TABLE_NAME = 'expertise';
    const COL_ID = 'EXPE_ID';
    const COL_SPECIES = 'EXPE_SPECIES';
    const COL_AREA = 'EXPE_AREA';
    const COL_SUBJECT = 'EXPE_SUBJECT';
    
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
    }
    
    public function getTableName()
    {
    	return $this->_name;
    }
}
?>
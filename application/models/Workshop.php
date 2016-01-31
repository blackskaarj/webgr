<?php
class Workshop extends Zend_Db_Table_Abstract  {

    const TABLE_NAME = 'workshop';
    const COL_ID = 'WORK_ID';
    const COL_USER_ID = 'USER_ID';
	const COL_NAME = 'WORK_NAME';
    const COL_START_DATE = 'WORK_STARTDATE';
    const COL_END_DATE= 'WORK_ENDDATE';
    const COL_LOCATION = 'WORK_LOCATION';
    const COL_HOST_ORGANISATION = 'WORK_HOST_ORGANISATION';
    const COL_TEMP = 'WORK_TEMP';
    
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
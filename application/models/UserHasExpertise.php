<?php
class UserHasExpertise extends Zend_Db_Table_Abstract  
{


    protected $_primary = array('USER_ID','EXPE_ID');

    const TABLE_NAME = 'user_has_expertise';
    const COL_USER_ID = 'USER_ID';
    const COL_EXPE_ID = 'EXPE_ID';
    
        /**
     * the physical tablename 
     * @var string
     */
	protected $_name = self::TABLE_NAME;
	
	/**
	 * the physical name of the primary key
	 * @var string
	 */
    
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
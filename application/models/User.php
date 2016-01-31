<?php



class User extends Zend_Db_Table_Abstract  
{

    const TABLE_NAME = 'user';
    const COL_ID = 'USER_ID';
    const COL_USERNAME = 'USER_USERNAME';
    const COL_LASTNAME = 'USER_LASTNAME';
    const COL_FIRSTNAME = 'USER_FIRSTNAME';
    const COL_PASSWORD = 'USER_PASSWORD';
    const COL_EMAIL = 'USER_E_MAIL';
    const COL_INSTITUTION = 'USER_INSTITUTION';
    const COL_STREET = 'USER_STREET';
    const COL_COUNTRY = 'USER_COUNTRY';
    const COL_PHONE = 'USER_PHONE';
    const COL_FAX = 'USER_FAX';
    const COL_CITY = 'USER_CITY';
    const COL_ROLE = 'USER_ROLE';
    const COL_ACTIVE = 'USER_ACTIVE';
    const COL_GUID = 'USER_GUID';
    
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
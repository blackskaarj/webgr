<?php
class KeyTable extends Zend_Db_Table_Abstract  
{
    const TABLE_NAME = 'key_table';
    const COL_ID = 'KETA_ID';
    const COL_AREA = 'KETA_AREA';
    const COL_SPECIES = 'KETA_SPECIES';
    //const COL_AGE = 'KETA_AGE';
    //const COL_MATURITY = 'KETA_MATURITY';
    const COL_NAME = 'KETA_NAME';
    const COL_SUBJECT = 'KETA_SUBJECT';
    const COL_FILENAME = 'KETA_FILENAME';

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
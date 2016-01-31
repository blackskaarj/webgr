<?php
class AttributeDescriptor extends Zend_Db_Table_Abstract  {

    /**
     * the physical tablename 
     * @var string
     */
	protected $_name ='attribute_desc';
	
	/**
	 * the physical name of the primary key
	 * @var string
	 */
    protected $_primary1 = 'ATDE_ID';

    const TABLE_NAME = 'attribute_desc';
    const COL_ID = 'ATDE_ID';
    const COL_USER_ID = 'USER_ID';
    const COL_NAME = 'ATDE_NAME';
    const COL_UNIT = 'ATDE_UNIT';
    const COL_DESCRIPTION = 'ATDE_DESCRIPTION';
    const COL_DEFAULT = 'ATDE_DEFAULT';
    const COL_REQUIRED = 'ATDE_REQUIRED';
    const COL_IS_STANDARD = 'ATDE_IS_STANDARD';
    const COL_MIN_OCCURS = 'ATDE_MIN_OCCURS';
    const COL_MAX_OCCURS = 'ATDE_MAX_OCCURS';
    const COL_ACTIVE = 'ATDE_ACTIVE';
    const COL_DATA_TYPE = 'ATDE_DATATYPE';
    const COL_FORM_TYPE = 'ATDE_FORMTYPE';
    const COL_VALUE_LIST = 'ATDE_VALUELIST';
    const COL_SEQUENCE = 'ATDE_SEQUENCE';
    const COL_MULTIPLE = 'ATDE_MULTIPLE';
    const COL_SHOW_IN_LIST = 'ATDE_SHOWINLIST';
    const COL_GROUP = 'ATDE_GROUP';
    const COL_FILTERS = 'ATDE_FILTERS';
    const COL_VALIDATORS = 'ATDE_VALIDATORS';
        
    /**
     * The constructos implements a Zend_DB_Adapter from the
     * Zend_Registry
     *
     */
    function __construct() {
        parent::__construct(array('db' => 'DB_CONNECTION1'));
    }//ENDE: function ...
    
    public function getTableName()
    {
    	return $this->_name;
    }
}//ENDE: class ...
?>
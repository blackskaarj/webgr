<?php
class WorkshopInfo extends Zend_Db_Table_Abstract  {

    const TABLE_NAME = 'ws_info';
	const COL_ID = 'WSIN_ID';
	const COL_WORKSHOP_ID = 'WORK_ID';
	const COL_TEXT = 'WSIN_TEXT';
	const COL_LINK = 'WSIN_LINK';
	const COL_FILE = 'WSIN_FILE';

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
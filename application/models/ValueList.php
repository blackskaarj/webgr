<?php
class ValueList extends Zend_Db_Table_Abstract  {

	const TABLE_NAME = 'value_list';
	const COL_ID = 'VALI_ID';
	const COL_ATTRIBUTE_DESCRIPTOR_ID = 'ATDE_ID';
	const COL_NAME = 'VALI_NAME';
	const COL_VALUE = 'VALI_VALUE';

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
	public static function getCommaSeperatedValueList($atDeId) {
		$list = '';
		if (Default_ReferenceQuery::hasValueListData($atDeId)) {

			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$select = $db->select();
			$select->from(self::TABLE_NAME, self::COL_NAME)
			->where(ValueList::COL_ATTRIBUTE_DESCRIPTOR_ID. "= ?", $atDeId, 'int');
			$listArray = $db->fetchAll($select);
			if(is_array($listArray)) {
				foreach($listArray as $index => $arrayValue) {
					if( $index == (count($listArray)- 1) ) {
						$list = $list.$arrayValue[self::COL_NAME];
					} else {
						if($index != 0 && $index % 5 == 0) {
							$list = $list.'<br />'.$arrayValue[self::COL_NAME].', ';
						} else {
							$list = $list.$arrayValue[self::COL_NAME].', ';
						}
					}
				}
			}
		}
		return $list;
	}
}
?>
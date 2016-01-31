<?php
class Participant extends Zend_Db_Table_Abstract  {

	const TABLE_NAME = 'participant';
	const COL_ID = 'PART_ID';
	const COL_CE_ID = 'CAEX_ID';
	const COL_USER_ID = 'USER_ID';
	const COL_EXPERTISE_LEVEL = 'PART_EXPERTISE_LEVEL';
	const COL_STOCK_ASSESSMENT = 'PART_STOCK_ASSESSMENT';
	const COL_ROLE = 'PART_PARTICIPANT_ROLE';
	const COL_NUMBER = 'PART_NUMBER';

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
	public static function getCoordinators($CaExID) {
		$coordinators = '';
		$select = Zend_Db_Table_Abstract::getDefaultAdapter()->select();
		$select->from(self::TABLE_NAME, array())
		->join(user::TABLE_NAME,
		user::TABLE_NAME.'.'.user::COL_ID.'='.participant::TABLE_NAME.'.'.participant::COL_USER_ID,
		user::TABLE_NAME.'.'.user::COL_LASTNAME)
		->where(self::COL_ROLE.'= ?', 'Coordinator')
		->where(self::COL_CE_ID.'= ?', $CaExID)
		->distinct(true);
		$coordinatorsArray = Zend_Db_Table_Abstract::getDefaultAdapter()->fetchAll($select);
		return $coordinatorsArray;
//		if(is_array($coordinatorsArray)) {
//			foreach($coordinatorsArray as $index => $arrayValue) {
//				if( $index == (count($coordinatorsArray)- 1) ) {
//					$coordinators = $coordinators.$arrayValue[user::COL_LASTNAME];
//				} else {
//					if($index != 0 && $index % 5 == 0) {
//						$coordinators = $coordinators.'<br />'.$arrayValue[user::COL_LASTNAME].', ';
//					} else {
//						$coordinators = $coordinators.$arrayValue[user::COL_LASTNAME].', ';
//					}
//				}
//			}
//		}
//		return $coordinators;
	}
}
?>
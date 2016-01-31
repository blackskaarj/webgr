<?php
class CalibrationExercise extends Zend_Db_Table_Abstract  {

    const TABLE_NAME = 'calibration_exercise';
    const COL_ID = 'CAEX_ID';
    const COL_KEY_TABLE_ID = 'KETA_ID';
    const COL_WORKSHOP_ID = 'WORK_ID';
	const COL_EXPERTISE_ID = 'EXPE_ID';
    const COL_NAME = 'CAEX_NAME';
    const COL_DESCRIPTION = 'CAEX_DESCRIPTION';
    const COL_COMPAREABLE = 'CAEX_COMPAREABLE';
    const COL_RANDOMIZED = 'CAEX_RANDOMIZED';
    const COL_IS_STOPPED = 'CAEX_IS_STOPPED';
    const COL_TRAINING = 'CAEX_TRAINING';
    
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
    }//ENDE: function ...
    
    public function getTableName()
    {
    	return $this->_name;
    }
}//ENDE: class ...
?>
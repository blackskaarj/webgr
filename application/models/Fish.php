<?php
class Fish extends Zend_Db_Table_Abstract  {

	const TABLE_NAME = 'fish';
	const COL_ID = 'FISH_ID';
	const COL_SAMPLE_CODE = 'FISH_SAMPLE_CODE';
	const COL_USER_ID = 'USER_ID';

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

	public function updateFishAndMetadata($form, $fishId, $data)
	{
		$dbAdapter = $this->getAdapter();
		$dbAdapter->beginTransaction();
		try {
			$medfiTable = new MetaDataFish();
			//$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
			$this->update($data, $dbAdapter->quoteInto(Fish::COL_ID . '=?',$fishId));

			$medfiTable->delete($dbAdapter->quoteInto(Fish::COL_ID . '=?',$fishId));
				
			foreach ($form->getDynamicElements() as $elementName){
				$metaValue = $form->getValue($elementName);
				if($metaValue != null){
					$attribId = substr($elementName,5,strlen($elementName));
					if(is_array($metaValue)){
						foreach ($metaValue as $meta){
							$medfiData = array(  MetaDataFish::COL_ATTRIBUTE_DESCRIPTOR_ID => $attribId,
							MetaDataFish::COL_FISH_ID => $fishId,
							MetaDataFish::COL_VALUE => $meta);
							$medfiTable->insert($medfiData);
						}
					}else{
						$medfiData = array(  MetaDataFish::COL_ATTRIBUTE_DESCRIPTOR_ID => $attribId,
						MetaDataFish::COL_FISH_ID => $fishId,
						MetaDataFish::COL_VALUE => $metaValue);
						$medfiTable->insert($medfiData);
					}
				}
			}
			$dbAdapter->commit();
			return $fishId;
		} catch (Exception $e) {
			// Wenn irgendeine der Abfragen fehlgeschlagen ist, wirf eine Ausnahme, wir
			// wollen die komplette Transaktion zurücknehmen, alle durch die
			// Transaktion gemachten Änderungen wieder entfernen, auch die erfolgreichen
			// So werden alle Änderungen auf einmal übermittelt oder keine
			$dbAdapter->rollBack();
			echo $e->getMessage();
			return NULL;
		}
	}
}//ENDE: class ...
?>
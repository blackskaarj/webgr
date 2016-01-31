<?php
/**
 *
 * @name
 * @abstract   Model of the table image
 * @author     Norman Rauthe (nr) <nr@zadi.de>
 * @copyright  Copyright (c) 2008, BLE, Ref. 422, Norman Rauthe (nr)
 * @version    Version vom 13.11.2008 um 11:25:52 Uhr
 *
 * @see     benennt die Scripte oder Funktionen, in denen diese Funktion aufgerufen wird
 * @todo    beschreibt die noch offenen Aufgaben
 * @example z.B.: blah blah
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */



class Image extends Zend_Db_Table_Abstract  {

	const TABLE_NAME = 'image';
	const COL_ID = 'IMAGE_ID';
	const COL_FISH_ID = 'FISH_ID';
	const COL_CHECKSUM = 'IMAGE_CHECKSUM';
	const COL_GUID = 'IMAGE_GUID';
	const COL_ORIGINAL_CHECKSUM = 'IMAGE_ORIGINAL_CHECKSUM';
	const COL_ORIGINAL_FILENAME = 'IMAGE_ORIGINAL_FILENAME';
	const COL_DIM_X = 'IMAGE_DIM_X';
	const COL_DIM_Y = 'IMAGE_DIM_Y';
	const COL_USER_ID = 'USER_ID';
	const COL_RATIO_EXTERNAL = 'IMAGE_RATIO_EXTERNAL';
    const COL_RATIO_INTERNAL = 'IMAGE_RATIO_INTERNAL';
	const COL_SHRINKED_RATIO = 'IMAGE_SHRINKED_RATIO';
	
	const RELATIVE_UPLOAD_PATH = 'images/originals'; //without pre- and post-slash!
	const RELATIVE_PATH_IMAGE_SHRINKED_WORKING_COPIES = 'images/shrinked_working_copies';
	const RELATIVE_PATH_IMAGE_THUMBNAILS = 'images/thumbnails';

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
	public function __construct() {
		parent::__construct(array('db' => 'DB_CONNECTION1'));
	}

	public function getTableName()
	{
		return $this->_name;
	}

	/**
	 * create and store thumbnail and shrinked file in certain path
	 * uses the ImageShrinker
	 * @param $completeTarget
	 * @return unknown_type
	 */
	public function processImage($completeTarget)
	{
		//create and store thumbnail and shrinked file in certain path
		$is = new Image_ImageShrinker($completeTarget);
		//$is->processImageForWebGR();
		$is->resampleImageAsThumbnail(self::RELATIVE_PATH_IMAGE_THUMBNAILS);
		$tn_ratio = $is->resampleImageAsShrinkedWorkingCopy(self::RELATIVE_PATH_IMAGE_SHRINKED_WORKING_COPIES);
		return $tn_ratio;
	}

	//TODO data array instead of "ratio", see update method below
	public function insertImageDataset($destination, $originalFileName, $fishId, $guid, $userId, $ratio, $tn_ratio)
	{
		
		//create image and meta data dataset
		// get image file native meta data
		$fileChecksum = md5_file($destination);
		$relativePathAndFileNameWorkingCopy = './'.self::RELATIVE_PATH_IMAGE_SHRINKED_WORKING_COPIES.'/'.$guid.'.jpg';
		$workingCopyChecksum = md5_file($relativePathAndFileNameWorkingCopy);
		$size = getimagesize($relativePathAndFileNameWorkingCopy);
		$workingCopyWidth = $size[0];
		$workingCopyHeight = $size[1];
			
		//insert image file native meta data into table
		$imageId = $this->insert(array(	Image::COL_FISH_ID => $fishId,
		Image::COL_CHECKSUM => $workingCopyChecksum,
		Image::COL_GUID => $guid,
		Image::COL_ORIGINAL_FILENAME => $originalFileName,
		Image::COL_ORIGINAL_CHECKSUM => $fileChecksum,
		Image::COL_DIM_X => $workingCopyWidth,
		Image::COL_DIM_Y => $workingCopyHeight,
		Image::COL_USER_ID => $userId,
		Image::COL_RATIO_EXTERNAL => $ratio,
		Image::COL_SHRINKED_RATIO => $tn_ratio
		));
		return $imageId;
	}

	/**
	 * updates the image and meta data entries
	 * meta data entries are deleted and inserted each time
	 * @param unknown_type $form ZF Form for reading dynamic elements
	 * @param unknown_type $imageId
	 * @param unknown_type $data data for image table
	 */
	public function updateImageAndMetadata($form, $imageId, $data = NULL)
	{
		$dbAdapter = $this->getAdapter();
		$dbAdapter->beginTransaction();
		try {

			$medimTable = new MetaDataImage();
			//$dbAdapter = Zend_Registry::get('DB_CONNECTION1');
			if (! empty($data)) {
				$this->update($data, $dbAdapter->quoteInto(Image::COL_ID . '=?',$imageId));
			}
			$medimTable->delete($dbAdapter->quoteInto(Image::COL_ID . '= ?', $imageId));

			foreach ($form->getDynamicElements() as $elementName){
				$metaValue = $form->getValue($elementName);
				if($metaValue != null){
					$attribId = substr($elementName,5,strlen($elementName));
					if(is_array($metaValue)){
						foreach ($metaValue as $meta){
							$medimData = array(  MetaDataImage::COL_ATTRIBUTE_DESCRIPTOR_ID => $attribId,
							MetaDataImage::COL_IMAGE_ID => $imageId,
							MetaDataImage::COL_VALUE => $meta);
							$medimTable->insert($medimData);
						}
					}else{
						$medimData = array(  MetaDataImage::COL_ATTRIBUTE_DESCRIPTOR_ID => $attribId,
						MetaDataImage::COL_IMAGE_ID => $imageId,
						MetaDataImage::COL_VALUE => $metaValue);
						$medimTable->insert($medimData);
					}
				}
			}
			$dbAdapter->commit();
			return $imageId;
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
}
?>
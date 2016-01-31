<?php

class Service_Dots 
{	
	/**
	 * @deprecated
	 */
	public function save(	$annoId,
							$dots_x,
							$dots_y)
	{
		$dotsTable = new Dots();
		$data = array(	Dots::COL_ANNO_ID => $annoId,
						Dots::COL_DOTS_X => $dots_x,
						Dots::COL_DOTS_Y => $dots_y);
						
		return $dotsTable->insert($data);
	}
	
	public function getDots($annoId)
	{
		$dotsTable = new Dots();
		$select = $dotsTable->select();
		$select->where(Dots::COL_ANNO_ID . "=?",$annoId);
		return $dotsTable->fetchAll($select)->toArray();
	}
	
	public static function delete($annoId)
	{
		$dotsTable = new Dots();
		$dotsTable->delete(Dots::COL_ANNO_ID . " = '" . $annoId . "'");
	}
	
	public function update(	$annoId,
							$coordinateString)
	{
		$this->delete($annoId);
		
		$dotsTable = new Dots();
		$dotsArray = explode(";",$coordinateString);
		foreach ($dotsArray as $dot) {
			$dotArray = explode(",",$dot);
			$data = array(	Dots::COL_ANNO_ID => $annoId,
						Dots::COL_DOTS_X => $dotArray[0],
						Dots::COL_DOTS_Y => $dotArray[1],
						Dots::COL_SEQUENCE => $dotArray[2]);
						
			$dotsTable->insert($data);
		}
	}
}

?>
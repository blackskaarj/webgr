<?php
/**
 //processes image, saved already to the server
 //creates thumbnails, saves to new file
 //creates "shrinked working copies" (here 1280x1280 or max 2880x2880 - max dimension, flash can handle), saves to new file
 //destination file names are related to source file name
 //handles input jpg, gif, png
 //output is jpg 100 percent quality, resampled (interpolated)
 * @author ip
 * @depending php--gd-library
 * @todo if original image is not bigger than shrinking size, don't fully process
 * //TODO write protect created files
 */

class Image_ImageShrinker
{
	private $source_pic;
	private $imageIdentifier;

	public function __construct($sourceFileName)
	{
		//if memory < 128MByte try to set it up to 128MByte
		$mem = ini_get("memory_limit");
		if ($this->return_bytes($mem) < 128*1024*1024) {
			//credit: http://de.php.net/manual/de/function.ini-set.php
			//johnzoet at netscape dot com
			//14-Mar-2002 02:13
			$blnResult = ini_set("memory_limit", "128M");
			if (empty($blnResult) or (!$blnResult))
			{
				throw new Exception ("Error: Can't set memory limit.");
			}
		}

		$this->source_pic = $sourceFileName;
		$this->createImageIdentifier();
	}

	public function createImageIdentifier()
	{
		$path_parts = pathinfo($this->source_pic);
		$imageFileFormat = strtolower($path_parts['extension']);
		switch($imageFileFormat)
		{
			case 'gif': $src = imagecreatefromgif($this->source_pic);
			$this->imageIdentifier = $src;
			break;
			case 'jpg': $src = imagecreatefromjpeg($this->source_pic);
			$this->imageIdentifier = $src;
			break;
			case 'jpeg': $src = imagecreatefromjpeg($this->source_pic);
			$this->imageIdentifier = $src;
			break;
			case 'png': $src = imagecreatefrompng($this->source_pic);
			$this->imageIdentifier = $src;
			break;
			default: throw new Exception("ERROR: UNSUPPORTED IMAGE TYPE: $imageFileFormat");
		}
	}

	public function resampleImageAndSave($max_width, $max_height)
	{
		$source_pic = $this->source_pic;
		$src = $this->imageIdentifier;

		//credit:
		//http://de.php.net/manual/de/function.getimagesize.php
		//devon at example dot com
		//07-Apr-2008 07:14

		list($width,$height) = getimagesize($source_pic);

		$x_ratio = $max_width / $width;
		$y_ratio = $max_height / $height;

		if( ($width <= $max_width) && ($height <= $max_height) ){
			$tn_width = $width;
			$tn_height = $height;
		}elseif (($x_ratio * $height) < $max_height){
			$tn_height = ceil($x_ratio * $height);
			$tn_width = $max_width;
		}else{
			$tn_width = ceil($y_ratio * $width);
			$tn_height = $max_height;
		}

		$tmp=imagecreatetruecolor($tn_width,$tn_height);
		imagecopyresampled($tmp,$src,0,0,0,0,$tn_width, $tn_height,$width,$height);

		//destination path = source path
		//variable destination image file name, e.g. DSC001___12345-GUID-54321_dim70x70.jpg
		$path_parts = pathinfo($this->source_pic);
		$dirName = $path_parts['dirname'];
		$fileName = $path_parts['filename'];
		$destination_pic = $dirName.'/'.$fileName.'_dim'.$tn_width.'x'.$tn_height.'.jpg';

		imagejpeg($tmp,$destination_pic,100);
		//uncommented; else class variable $imageIdentifier gets destroyed, too, because it's a call by reference
		//at the beginning of the function
		//imagedestroy($src);
		imagedestroy($tmp);
	}

	public function resampleImageAsThumbnail($path)
	{
		$source_pic = $this->source_pic;
		$src = $this->imageIdentifier;
		$max_width = 70;
		$max_height = 70;

		//credit:
		//http://de.php.net/manual/de/function.getimagesize.php
		//devon at example dot com
		//07-Apr-2008 07:14

		list($width,$height) = getimagesize($source_pic);

		$x_ratio = $max_width / $width;
		$y_ratio = $max_height / $height;

		if( ($width <= $max_width) && ($height <= $max_height) ){
			$tn_width = $width;
			$tn_height = $height;
		}elseif (($x_ratio * $height) < $max_height){
			$tn_height = ceil($x_ratio * $height);
			$tn_width = $max_width;
		}else{
			$tn_width = ceil($y_ratio * $width);
			$tn_height = $max_height;
		}

		$tmp=imagecreatetruecolor($tn_width,$tn_height);
		imagecopyresampled($tmp,$src,0,0,0,0,$tn_width, $tn_height,$width,$height);

		//destination path = source path
		$path_parts = pathinfo($this->source_pic);
		//$dirName = $path_parts['dirname'];
		$dirName = $path;
		$fileName = $path_parts['filename'];
		$destination_pic = $dirName.'/'.$fileName.'.jpg';

		imagejpeg($tmp,$destination_pic,100);
		//uncommented; else class variable $imageIdentifier gets destroyed, too, because it's a call by reference
		//at the beginning of the function
		//imagedestroy($src);
		imagedestroy($tmp);
	}

	public function resampleImageAsShrinkedWorkingCopy($path)
	{
		$source_pic = $this->source_pic;
		$src = $this->imageIdentifier;
		$max_width = 1280;
		$max_height = 1280;

		//credit:
		//http://de.php.net/manual/de/function.getimagesize.php
		//devon at example dot com
		//07-Apr-2008 07:14
		//extended to get back the transformation ratio
		//(transformation is proportional)

		list($width,$height) = getimagesize($source_pic);

		$x_ratio = $max_width / $width;
		$y_ratio = $max_height / $height;

		if( ($width <= $max_width) && ($height <= $max_height) ){
			$tn_width = $width;
			$tn_height = $height;
			$tn_ratio = 1.0;
		}elseif (($x_ratio * $height) < $max_height){
			$tn_height = ceil($x_ratio * $height);
			$tn_width = $max_width;
			//new for tn_ratio
			$tn_ratio = $x_ratio;
		}else{
			$tn_width = ceil($y_ratio * $width);
			$tn_height = $max_height;
	         //new for tn_ratio
			$tn_ratio = $y_ratio;
		}

		$tmp=imagecreatetruecolor($tn_width,$tn_height);
		imagecopyresampled($tmp,$src,0,0,0,0,$tn_width, $tn_height,$width,$height);

		//destination path = source path
		$path_parts = pathinfo($this->source_pic);
		//$dirName = $path_parts['dirname'];
		$dirName = $path;
		$newFileName = $path_parts['filename'].'.jpg';
		$destination_pic = $dirName.'/'.$newFileName;

		imagejpeg($tmp,$destination_pic,100);
		//uncommented; else class variable $imageIdentifier gets destroyed, too, because it's a call by reference
		//at the beginning of the function
		//imagedestroy($src);
		imagedestroy($tmp);

		/*
		 // get image file native meta data
		 $fileChecksum = md5_file($destination_pic);

		 //insert image file native meta data into table
		 $imageTable = new Image();
		 $imageTable->insert(array(	Image::COL_FISH_ID => '2',
		 Image::COL_FILENAME => $newFileName,
		 Image::COL_CHECKSUM => $fileChecksum,
		 Image::COL_DIM_X => $tn_width,
		 Image::COL_DIM_Y => $tn_height
		 ));
		 */
		
		return $tn_ratio;
	}

	public function processImageForWebGR()
	{
		$this->resampleImageAsThumbnail();
		$this->resampleImageAsShrinkedWorkingCopy();
	}


	private function return_bytes($val)
	/*credits
	 * Nathaniel Sabanski
	 15-Mar-2009 10:20
	 http://us2.php.net/manual/en/function.ini-get.php
	 */
	{
		$val = trim($val);
		$last = strtolower(substr($val, -1));
			
		if($last == 'g')
		$val = $val*1024*1024*1024;
		if($last == 'm')
		$val = $val*1024*1024;
		if($last == 'k')
		$val = $val*1024;
			
		return $val;
	}
}
?>
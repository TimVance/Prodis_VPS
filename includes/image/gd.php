<?php
/**
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined('DIAFAN'))
{
	$path = __FILE__;
	while(! file_exists($path.'/includes/404.php'))
	{
		$parent = dirname($path);
		if($parent == $path) exit;
		$path = $parent;
	}
	include $path.'/includes/404.php';
}

class GDInterface extends ImageInterface {

	private $src;
	private $dst;
	private $info;

	public function __construct($image, $quality = ImageInterface::DEFAULT_QUALITY) {
		parent::__construct($image, $quality);

		$this->src = $this->createImage($image);
	}

	private function createImage($image) {

		$image = $this->getImage($image);
		$src = false;

		switch ($this->getFormat($image)) {
			case ImageInterface::FORMAT_GIT:
				$src = imageCreateFromGIF($image);
				break;
			case ImageInterface::FORMAT_JPEG:
				$src = imageCreateFromJPEG($image);
				break;
			case ImageInterface::FORMAT_PNG:
				$src = imageCreateFromPNG($image);
				break;
			default:
				throw new Image_exception('Не поддерживаемый формат изображения.');
		}

		if (false === $src)
			throw new Image_exception(__METHOD__ . ' ' . $image);

		return $src;
	}

	public function __destruct() {

		if ($this->dst) {
			switch ($this->getFormat($this->image)) {
				case ImageInterface::FORMAT_GIT:
					imageGIF($this->dst, $this->image);
					break;
				case ImageInterface::FORMAT_JPEG:
					imageJPEG($this->dst, $this->image, $this->quality);
					break;
				case ImageInterface::FORMAT_PNG:
					imagePNG($this->dst, $this->image);
					break;
			}

			imageDestroy($this->dst);
			$this->dst = null;
		}

		if ($this->src) {
			imageDestroy($this->src);
			$this->src = null;
		}

		parent::__destruct();
	}

	private function getInfo($image) {
		if (null == $this->info || !array_key_exists($image, $this->info)) {
			$this->info[$image] = getImageSize($image);
			if (false === $this->info[$image])
				throw new Image_exception(__METHOD__);
		}

		return $this->info[$image];
	}

	private function getFormat($image) {

		$info = $this->getInfo($image);
		if (!array_key_exists(2, $info))
			throw new Image_exception(__METHOD__);

		return $info[2];
	}

	private function getWidth($image) {
		$info = $this->getInfo($image);
		if (!array_key_exists(0, $info))
			throw new Image_exception(__METHOD__);

		return $info[0];
	}

	private function getHeight($image) {
		$info = $this->getInfo($image);
		if (!array_key_exists(1, $info))
			throw new Image_exception(__METHOD__);

		return $info[1];
	}

	private function createDest($width, $height) {
		$this->dst = imageCreateTrueColor($width, $height);

		//png
		imagecolortransparent($this->dst, imagecolorallocatealpha($this->dst, 0, 0, 0, 127));
		imagealphablending($this->dst, false);
		imagesavealpha($this->dst, true);

		return $this->dst;
	}

	public function thumbnail($width, $height, $fit = false) {

		$src_width = $this->getWidth($this->image);
		$src_height = $this->getHeight($this->image);

		list($dest_width,$dest_height) = $this->calcResize($src_width, $src_height, $width, $height, $fit);

		$this->createDest($dest_width, $dest_height);
		imageCopyResampled($this->dst, $this->src, 0, 0, 0, 0, $dest_width, $dest_height, $src_width, $src_height);
	}

	public function crop($width, $height, $vertical, $y, $horizontal, $x)
	{
		$src_width = $this->getWidth($this->image);
		$src_height = $this->getHeight($this->image);

		$this->calcPosition($src_width, $src_height, $width, $height, $vertical, $y, $horizontal, $x);

		$this->createDest($width, $height);

		imagefill($this->dst, 0, 0, 0xffffff);
		
		if($width > $src_width)
		{
			$d_x = abs($x);
			$x = 0;
			$width = $src_width;
		}
		else
		{
			$d_x = 0;
			$width = $src_width - $x;
		}
		if($height > $src_height)
		{
			$d_y = abs($y);
			$y = 0;
			$height = $src_height;
		}
		else
		{
			$d_y = 0;
		}

		imageCopy($this->dst, $this->src, $d_x, $d_y, $x, $y, $width, $height);
	}

	public function watermark($watermark, $vertical, $y, $horizontal, $x)
	{
		$src_width = $this->getWidth($this->image);
		$src_height = $this->getHeight($this->image);

		$info_w = @getImageSize($watermark); 
		if (! $info_w) 
			return false;

		$this->calcPosition($src_width, $src_height, $info_w[0], $info_w[1], $vertical, $y, $horizontal, $x, true);

		$this->createDest($src_width, $src_height);
		
		$this->dst = imageCreateTrueColor($src_width, $src_height); 
		
		//png
		if($info_w[2] == 3)
		{
			imagefill($this->dst, 0, 0, imagecolorallocatealpha ($this->dst, 0, 0, 0, 127));
			$watermark_image = imagecreatefrompng($watermark);
			imagesavealpha($this->dst, true);
		}
		else
		{
			$watermark_image = @imageCreateFromString(file_get_contents($watermark));
			imagecolortransparent($this->dst , imagecolorallocatealpha($this->dst, 0, 0, 0, 127));
			imagealphablending($this->dst , false);
			imagesavealpha($this->dst , true);
		}

		imageCopy($this->dst, $this->src, 0, 0, 0, 0, $src_width, $src_height);

		if ($src_width > 10 && $src_height > 10)
		{
			imageCopy($this->dst, $watermark_image, $x, $y, 0, 0, $info_w[0], $info_w[1]);
		}

		imageDestroy($watermark_image);
		return true;
	}

	public function grayscale() {

		$this->dst = &$this->src;
		imagefilter($this->dst, IMG_FILTER_GRAYSCALE);
	}

}

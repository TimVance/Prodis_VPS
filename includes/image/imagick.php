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

class ImagickInterface extends ImageInterface {

    /**
     *
     * @var  Imagick
     */
    private $src;

    public function __construct($image, $quality = ImageInterface::DEFAULT_QUALITY) {
        parent::__construct($image, $quality);

        try {
            $this->src = new Imagick($this->image);
        } catch (ImagickException $e) {
            throw new Image_exception($e->getMessage());
        }
    }

    public function __destruct() {

        try {
            $this->src->setImageCompressionQuality($this->quality);
            $this->src->writeImage($this->image);
            $this->src->clear();
            
        } catch (ImagickException $e) {
            throw new Image_exception($e->getMessage());
        }

        parent::__destruct();
    }

    /**
     * @var Gmagick
     */
    public function thumbnail($width, $height, $fit = false) {
        try {

            list($dest_width, $dest_height) = $this->calcResize($this->src->getimagewidth(), $this->src->getimageheight(), $width, $height, $fit);
            $this->src->thumbnailImage($dest_width, $dest_height);

      
        } catch (ImagickException $e) {
            throw new Image_exception($e->getMessage());
        }
    }

    public function crop($width, $height, $vertical, $y, $horizontal, $x) {
        try {
            
            $this->calcPosition($this->src->getimagewidth(), $this->src->getimageheight(), $width, $height, $vertical, $y, $horizontal, $x);
            $this->src->cropImage($width, $height, $x, $y);

        } catch (ImagickException $e) {
            throw new Image_exception($e->getMessage());
        }
    }

    public function watermark($watermark, $vertical, $y, $horizontal, $x) {
        try {
            $watermark = $this->getImage($watermark);
            $image = new Imagick($watermark);
            
            //TODO: нет проверки... вместится ватермарк или нет...
            
            $this->calcPosition($this->src->getimagewidth(), $this->src->getimageheight(), $image->getimagewidth(), $image->getimageheight(), $vertical, $y, $horizontal, $x, true);
            $this->src->compositeImage($image, Imagick::COMPOSITE_DEFAULT, $x, $y);
            
            $image->clear();
            
        } catch (ImagickException $e) {
            throw new Image_exception($e->getMessage());
        }
    }

    public function grayscale() {
        try {
            $this->src->setImageType(2);
        } catch (ImagickException $e) {
            throw new Image_exception($e->getMessage());
        }
    }

}

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

class GmagickInterface extends ImageInterface {

    /**
     *
     * @var Gmagick
     */
    private $src;

    public function __construct($image, $quality = ImageInterface::DEFAULT_QUALITY) {
        parent::__construct($image, $quality);

        try {
            $this->src = new Gmagick($this->image);
        } catch (GmagickException $e) {
            throw new Image_exception($e->getMessage());
        }
    }

    public function __destruct() {

        try {
            $this->src->writeimage($this->image);
            $this->src->destroy();
            
        } catch (GmagickException $e) {
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
            $this->src->thumbnailimage($dest_width, $dest_height);

      
        } catch (GmagickException $e) {
            throw new Image_exception($e->getMessage());
        }
    }

    public function crop($width, $height, $vertical, $y, $horizontal, $x) {
        try {
            
            $this->calcPosition($this->src->getimagewidth(), $this->src->getimageheight(), $width, $height, $vertical, $y, $horizontal, $x);
            $this->src->cropimage($width, $height, $x, $y);

        } catch (GmagickException $e) {
            throw new Image_exception($e->getMessage());
        }
    }

    public function watermark($watermark, $vertical, $y, $horizontal, $x) {
        try {
            $watermark = $this->getImage($watermark);
            $image = new Gmagick($watermark);
            
            //TODO: нет проверки... вместится ватермарк или нет...
            
            $this->calcPosition($this->src->getimagewidth(), $this->src->getimageheight(), $image->getimagewidth(), $image->getimageheight(), $vertical, $y, $horizontal, $x, true);
            $this->src->compositeimage($image, Gmagick::COMPOSITE_DEFAULT, $x, $y);
            
            $image->destroy();
            
        } catch (GmagickException $e) {
            throw new Image_exception($e->getMessage());
        }
    }

    public function grayscale() {
        try {
            $this->src->setImageType(2);
        } catch (GmagickException $e) {
            throw new Image_exception($e->getMessage());
        }
    }

}

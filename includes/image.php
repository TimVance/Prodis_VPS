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

abstract class ImageInterface {

    protected $image;
    protected $downloads;
    protected $quality;

    const DEFAULT_QUALITY = 80;
    const FORMAT_GIT = 1;
    const FORMAT_JPEG = 2;
    const FORMAT_PNG = 3;
    const TOP = 'top';
    const BOTTOM = 'bottom';
    const MIDDLE = 'middle';
    const LEFT = 'left';
    const RIGHT = 'right';
    const CENTER = 'center';

    public function __construct($image) {
        $this->image = $this->getImage($image);
        $this->setQuality();
    }

    public function __destruct() {

        if (! empty($this->downloads)) {
            foreach ($downloads as $tmp) {
                unlink($tmp);
            }
        }
    }

    protected function getImage($image) { // скачивает или просто возвращает путь до изображения.
        if (! empty($image) && preg_match("/^https?:\/\//", $image)) {

            Custom::inc('plugins/httprequest/httprequest.php');

            try {
                $tmp_file = fopen(ABSOLUTE_PATH . tempnam('tmp', 'image'), 'wb');
                if (!$tmp_file || !DHttpRequest::get($image)->receive($tmp_file)->ok())
                    throw new Image_exception('Невозможно загрузить файл '.$image);

                $image = $tmp_file;
                $downloads[] = $new_file;
            } catch (DHttpRequestException $e) {
                throw new Image_exception('HTTP: ' . $e->getMessage());
            }
        }

        if (empty($image) || !file_exists($image))
            throw new Image_exception('Укажите путь до изображения.');

        return $image;
    }

    protected function calcPosition($src_width, $src_height, $width, $height, $vertical, &$y, $horizontal, &$x, $wm = false)
    {
        switch (trim($vertical))
        {
            case ImageInterface::BOTTOM:
                if($height < $src_height && ! $wm)
                {
                    $y = $height - $src_height - (int) $y;
                }
                else
                {
                    $y = $src_height - $height - (int) $y;
                }
                $y = abs($y);
                break;

            case ImageInterface::MIDDLE:
                $y = ceil($src_height / 2) - ceil($height / 2) + (int) $y;
                break;
        }
        switch (trim($horizontal))
        {
            case ImageInterface::RIGHT:
                if($width < $src_width && ! $wm)
                {
                    $x = $width - $src_width - (int) $x;
                }
                else
                {
                    $x = $src_width - $width - (int) $x;
                }
                $x = abs($x);
                break;

            case ImageInterface::CENTER:
                $x = ceil($src_width / 2) - ceil($width / 2) + (int) $x;
                break;
        }
    }

    protected function calcResize($src_width, $src_height, $width, $height, $fit = false) {

        if ($width > $src_width)
        {
            $width = $src_width;
        }
        if($height > $src_height)
        {
            $height = $src_height;
        }

        $mc1 = $width / $src_width;
        $mc2 = $height / $src_height;

        if ($fit) {
            $k = max($mc1, $mc2);
        } else {
            $k = min($mc1, $mc2);
        }

        $dest_width = round($src_width * $k);
        $dest_height = round($src_height * $k);
        if ($fit && ($dest_width > $src_width || $dest_height > $src_height)) {
            $k = min($mc1, $mc2);
            $dest_width = round($src_width * $k);
            $dest_height = round($src_height * $k);
        }

        return array($dest_width, $dest_height);
    }

    public function setQuality($quality = self::DEFAULT_QUALITY) {
        $this->quality = $quality;
    }

    public function getQuality() {
        return $this->quality;
    }

    abstract public function thumbnail($width, $height, $fit = false);

    abstract public function crop($width, $height, $vertical, $y, $horizontal, $x);

    abstract public function watermark($watermark, $vertical, $y, $horizontal, $x);

    abstract public function grayscale();
}

class ImageHelper
{

    private $factory;
    private $image;
    private $backend;

    function __construct($image) {
        $this->image = $image;
        $this->setImageFactory();
    }

    public function setImageFactory(ImageFactory $factory = null) {
        if ($factory == null) {
            $this->factory = new DEFAULT_IMAGE_FACTORY();
        } else {
            $this->factory = $factory;
        }

        return $this;
    }

    public function getBackend() {
        if (null == $this->backend) {
            $this->backend = $this->createBackend();
        }

        return $this->backend; // ImageInterface
    }

    private function createBackend() {
        $this->backend = call_user_func(array($this->factory, 'create'), $this->image);
        return $this->backend;
    }

    public function __call($name, $arguments) {
        return call_user_func_array(array($this->getBackend(), $name), $arguments);
    }

}

interface ImageFactory {

    public static function create($image);
}

class DEFAULT_IMAGE_FACTORY implements ImageFactory
{
    public static function create($image)
    {
        if (extension_loaded('gd'))
        {
            Custom::inc('includes/image/gd.php');
            return new GDInterface($image);
        }

        if (extension_loaded('gmagick'))
        {
            Custom::inc('includes/image/gmagick.php');
            return new GmagickInterface($image);
        }
        
        if (extension_loaded('imagick'))
        {
           Custom::inc('includes/image/imagick.php');
            return new ImagickInterface($image);
        }

        throw new Image_exception('Нет библиотек для работы с изображениями.');
    }

}

/**
 * Image
 * Набор функций для работы с изображениями
 */
class Image {

    /**
     * Изменяет размеры изображения
     * @param string $src_image путь к файлу
     * @param integer $dest_width новая ширина изображения
     * @param integer $dest_height новая высота изображения
     * @param integer $quality качество изображения
     * @param boolean $max изменять по максимальной стороне
     * @return boolean
     */
    public static function resize($src_image, $dest_width, $dest_height, $quality = 80, $max = false) {
        $image = new ImageHelper($src_image);

        $image->setQuality($quality);
        $image->thumbnail($dest_width, $dest_height, $max);

        unset($image);
        return true;
    }

    /**
     * Обрезает изображение
     * @param string $original путь к файлу
     * @param integer $width новая ширина изображения
     * @param integer $height новая высота изображения
     * @param integer $quality качество изображения
     * @param string $vertical вертикальное расположение знака (top, middle, bottom)
     * @param integer $sy отступ по вертикале
     * @param string $horizontal горизонтальное расположение знака (left, center, right)
     * @param integer $sx отступ по горизонтале
     * @return boolean
     */
    public static function crop($original, $width, $height, $quality, $vertical, $sy, $horizontal, $sx)
    {
        $sx = intval($sx);
        $sy = intval($sy);
        $image = new ImageHelper($original);

        $image->setQuality($quality);
        $image->crop($width, $height, $vertical, $sy, $horizontal, $sx);

        unset($image);
        return true;
    }

    /**
     * Добавляет водяной знак на изображение
     * @param string $original путь к файлу
     * @param string $watermark путь к водяному знаку
     * @param integer $quality качество изображения
     * @param string $vertical вертикальное расположение знака (top, middle, bottom)
     * @param integer $sy отступ по вертикале
     * @param string $horizontal горизонтальное расположение знака (left, center, right)
     * @param integer $sx отступ по горизонтале
     * @return boolean
     */
    public static function watermark($original, $watermark, $quality, $vertical, $sy, $horizontal, $sx) {
        $image = new ImageHelper($original);
        $image->setQuality($quality);
		$sx = intval($sx);
		$sy = intval($sy);
        $image->watermark($watermark, $vertical, $sy, $horizontal, $sx);

        unset($image);
        return true;
    }

    /**
     * Обесцвечивает изображение
     * @param string $original путь к файлу
     * @param integer $quality качество изображения
     * @return boolean
     */
    public static function wb($original, $quality) {
        $image = new ImageHelper($original);
        $image->setQuality($quality);

        $image->grayscale();

        unset($image);
        return true;
    }

}

/**
 * Image_exception
 * 
 * Исключение для работы с изображениями
 */
class Image_exception extends Exception{}

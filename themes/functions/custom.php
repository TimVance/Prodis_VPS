<?php
/**
 * Шаблонный тег: выводит путь до файла с учетом кастомизации.
 *
 * @param array $attributes атрибуты шаблонного тега
 * path - исходный путь до файла
 * absolute - путь абсолютный: **true** – тег выведет полный путь до файла, по умолчанию тег выведет относительный путь до файла без доменного имени
 * compress - сжатие файла: **js** - тип js, **css** - тип css
 * 
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

$this->get_attributes($attributes, 'path', 'absolute', 'compress');
$attributes["compress"] = $attributes["compress"] ? strtolower($attributes["compress"]) : false;
$attributes["compress"] = $attributes["compress"] == 'js' || $attributes["compress"] == 'css' ? $attributes["compress"] : false;

if($attributes["absolute"] == 'true')
{
	echo BASE_PATH;
}
echo $attributes["compress"] ? File::compress(Custom::path($attributes["path"]), $attributes["compress"]) : Custom::path($attributes["path"]);
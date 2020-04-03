<?php
/**
 * Шаблонный тег: подключает CSS-файлы. При включенном режиме разработки файлы будут объеденены и сжаты, что приведет к более быстрой загрузке файлов. Если существуют какие-то проблемы при включенном сжатии, подключите CSS-файлы стандартным HTML-тегом `<link rel="stylesheet" type="text/css"...>`.
 *
 * @param array $attributes атрибуты шаблонного тега
 * files - перечень CSS-файлов, которые нужно подключить. Файлы должны размещаться в папке *css*. Если файлов несколько, то названия должны быть разделены запятыми
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

$this->get_attributes($attributes, 'files');

$files = array(Custom::path('adm/css/errors.css'));

$files[] = Custom::path('css/custom-theme/jquery-ui-1.8.18.custom.css');

$att_files = explode(',', $attributes["files"]);
foreach($att_files as $file)
{
	if(trim($file))
	{
		$files[] = Custom::path('css/'.trim($file));
	}
}
$css_view = array();
if($this->diafan->_site->css_view)
{
	foreach($this->diafan->_site->css_view as $file)
	{
		if(in_array($file, $css_view))
			continue;

		$css_view[] = $file;
		$files[] = Custom::path(trim($file));
	}
}
$this->diafan->_site->css_view = array();

$compress_files = File::compress($files, 'css');
if(is_array($compress_files))
{
	foreach($compress_files as $file)
	{
		echo '<link href="'.BASE_PATH.$file.'" rel="stylesheet" type="text/css">';
	}
}
else
{
	echo '<link href="'.BASE_PATH.$compress_files.'" rel="stylesheet" type="text/css">';
}

if ($this->diafan->_users->useradmin)
{
	echo '<link href="'.BASE_PATH.Custom::path('modules/useradmin/useradmin.css').'" rel="stylesheet" type="text/css">';
}
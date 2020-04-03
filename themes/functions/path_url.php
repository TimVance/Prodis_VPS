<?php
/**
 * Шаблонный тег: выводит адрес сайта, с учетом языковой версии сайта.
 *
 * @param array $attributes атрибуты шаблонного тега
 * mobile - признак мобильной версии: yes – в адресе будет включено "m/", если страница – мобильная версия (по умолчанию); no – в адресе будет исключено "m/" даже если страница – мобильная версия
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

$this->get_attributes($attributes, 'mobile');

if($attributes["mobile"] == "no" && IS_MOBILE)
{
	$lang = '';
	foreach ($this->diafan->_languages->all as $language)
	{
		if ($language["id"] == _LANG && ! $language["base_site"])
		{
			$lang = $language["shortname"];
		}
	}
	echo (defined('MAIN_PATH') ? MAIN_PATH : BASE_PATH).(_LANG && $lang ? $lang.'/' : '');
}
else
{
	echo BASE_PATH_HREF;
}
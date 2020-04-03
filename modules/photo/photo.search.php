<?php
/**
 * Настройки для поисковой индексации для модуля «Поиск»
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

/**
 * Photo_search_config
 */
class Photo_search_config
{
	public $config = array(
		'photo' => array(
			'fields' => array('name', 'anons', 'text'),
			'rating' => 1
		),
		'photo_category' => array(
			'fields' => array('name', 'anons', 'text'),
			'rating' => 1
		)
	);
}
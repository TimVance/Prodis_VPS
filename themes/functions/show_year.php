<?php
/**
 * Шаблонный тег: выводит период функционирования сайта в годах.
 * 
 * @param array $attributes атрибуты шаблонного тега
 * year - начало отсчета (по умолчанию текущий год)
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

$this->get_attributes($attributes, 'year');

$year = preg_replace('/[^0-9]+/', '', $attributes["year"]);

echo ($year ? $year : date("Y")).(date("Y") != $year && $year ? ' - '.date("Y").' '.$this->diafan->_('гг.') : '');
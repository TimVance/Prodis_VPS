<?php
/**
 * Шаблон списка комментариев
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

if(empty($result) || empty($result["rows"])) return false;

foreach($result["rows"] as $row)
{
	echo $this->get('id', 'comments', $row);
}

//Кнопка "Показать ещё"
if(! empty($result["result"]) && ! empty($result["result"]["show_more"]))
{
	echo $result["result"]["show_more"];
}

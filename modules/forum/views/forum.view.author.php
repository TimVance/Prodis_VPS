<?php
/**
 * Шаблон вывода информации о пользователе
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

if (is_array($result))
{
	if(! empty($result["user_page"]))
	{
		echo '<a href="'.$result["user_page"].'">';
	}
	echo $result["fio"].($result["name"] ? ' ('.$result["name"].')' : '');
	if(! empty($result["user_page"]))
	{
		echo '</a>';
	}
}
else
{
	echo $result;
}
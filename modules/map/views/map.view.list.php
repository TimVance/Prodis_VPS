<?php
/**
 * Шаблон списка страниц в карте сайте
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

if ($result["rows"])
{
	foreach ($result["rows"] as $row)
	{
		echo '<p style="margin-left:'.$row["margin"].'px;"><a href="'.BASE_PATH_HREF.$row["link"].'">'.$row["name"].'</a></p>';
	}
}
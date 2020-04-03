<?php
/**
 * Шаблон постраничной навигации для административной части
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
if ($result)
{
	foreach ($result as $l)
	{
		switch($l["type"])
		{
			case "first":
				echo '<a href="'.BASE_PATH_HREF.$l["link"].'" class="start"><i class="fa fa-angle-left"></i></a> ';
				break;

			case "current":
				echo '<span class="active">'.$l["name"].'</span> ';
				break;

			case "previous":
				echo '<a href="'.BASE_PATH_HREF.$l["link"].'" title="'.$this->diafan->_('На предыдущую страницу').'" class="prev">...</a> ';
				break;

			case "next":
				echo '<a href="'.BASE_PATH_HREF.$l["link"].'" title="'.$this->diafan->_('На следующую страницу').' '.$this->diafan->_('Всего %d', $l["nen"]).'" class="next">...</a> ';
				break;

			case "last":
			echo '<a href="'.BASE_PATH_HREF.$l["link"].'" class="end"><i class="fa fa-angle-right"></i></a> ';
			break;

			default:
				echo '<a href="'.BASE_PATH_HREF.$l["link"].'" class="border">'.$l["name"].'</a> ';
				break;
		}
	}
}
else
{
	echo '<div class="paginator_empty"></div>';
}
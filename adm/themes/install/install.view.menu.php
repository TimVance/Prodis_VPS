<?php
/**
 * Шаблон меню для установки DIAFAN.CMS
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
echo '
<div class="breadcrumbs">';
$kk = 0;$first = true;
foreach ($this->view->steps as $k => $v)
{
	if(! $first)
	{
		echo ' <i class="fa fa-angle-right"></i> ';
	}
	if ($k == $this->view->rewrite)
	{
		$kk = 1;
	}
	if(! $kk)
	{
		echo '<a href="'.BASE_PATH.'installation/';
		if($k != "index")
		{
			echo $k.'/';
		}
		echo '">';
	}
	else
	{
		echo '<span>';
	}
	echo $v;
	if(! $kk)
	{
		echo '</a>';
	}
	else
	{
		echo '</span>';
	}
	$first = false;
}
echo '</div>';
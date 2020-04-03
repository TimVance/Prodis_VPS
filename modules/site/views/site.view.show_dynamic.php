<?php
/**
 * Шаблон динамического блока
 * 
 * Шаблонный тег <insert name="show_dynamic" module="site" id="номер_страницы" [template="шаблон"]>:
 * выводит блок на сайте
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

if (! $result)
{
	return;
}
echo '<div class="site_dynamic">';
if(! empty($result["name"]))
{
	echo '<div class="block_header">'.$result["name"].'</div>';
}
echo $result['text'];
echo '</div>';

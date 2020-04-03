<?php
/**
 * Шаблон формы поиска по сайту, template=404
 *
 * Шаблонный тег <insert name="show_search" module="search" template="404"
 * [button="надпись на кнопке"]>:
 * форма поиска по сайту
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

echo '<div class="top-search">
	<form action="'.$result["action"].'" class="search-form" method="get">
		<input type="hidden" name="module" value="search">
		<input class="search-input" type="text" name="searchword">
		<input type="submit" value="">
	</form>
</div>';
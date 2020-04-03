<?php
/**
 * Шаблон формы поиска по сайту
 *
 * Шаблонный тег <insert name="show_search" module="search"
 * [button="надпись на кнопке"] [template="шаблон"]>:
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

echo '
<div class="search">
	<form action="'.$result["action"].'" class="search_form'.($result["ajax"] ? ' ajax" method="post"' : '" method="get"').'>
	<input type="hidden" name="module" value="search">
	<input type="text" name="searchword" value="'.($result["value"] ? $result["value"] : '').'" placeholder="'.$this->diafan->_('Поиск по сайту', false).'" class="input_search">
	<input type="submit" value="'.$result["button"].'" class="submit_search">
	</form>';
if($result["ajax"])
{
	echo '<div class="js_search_result search_result"></div>';
}
echo '</div>';
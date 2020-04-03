<?php
/**
 * Шаблон кнопки «Сравнить выбранные товары»
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

$count = (isset($_SESSION['shop_compare'][$result["site_id"]]) ? count($_SESSION['shop_compare'][$result["site_id"]]) : 0);

echo '
<form action="'.BASE_PATH_HREF.$result["shop_link"].'" method="GET" class="shop_compared_goods_list">
<input type="hidden" name="action" value="compare">
<input type="submit" value="'.$this->diafan->_('Сравнить выбранное', false).' ('.$count.')" class="shop_compare_all_button js_shop_compare_all_button" data-title="'.$this->diafan->_('Сравнить выбранное', false).'" data-count="'.$count.'">

</form>';
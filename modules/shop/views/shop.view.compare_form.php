<?php
/**
 * Шаблон кнопки «Сравнить» для товаров
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

$checked = empty($_SESSION['shop_compare'][$result["site_id"]][$result["id"]]) ? false : true;
echo '
<form action="" method="POST" class="ajax">
<input type="hidden" name="module" value="shop">
<input type="hidden" name="action" value="compare_goods">
<input type="hidden" name="id" value="'.$result["id"].'">
<input type="hidden" name="site_id" value="'.$result["site_id"].'">
<input type="checkbox"  name="add" value="1" id="id_add'.$result["id"].'" class="js_shop_add_compare shop_compare_button" '.($checked ? ' checked="checked"' : '').'>
<label class="shop_add_compare add_compare" for="id_add'.$result["id"].'">'.$this->diafan->_('Добавить к сравнению', false).'</label>
</form>';
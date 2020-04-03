<?php
/**
 * Шаблон блока товаров
 *
 * Шаблонный тег <insert name="show_block" module="shop" [count="количество"]
 * [cat_id="категория"] [ids="товары"] [site_id="страница_с_прикрепленным_модулем"] [brand_id="производитель"] 
 * [images="количество_изображений"] [images_variation="тег_размера_изображений"]
 * [sort="порядок_вывода"] [param="дополнительные_условия"]
 * [hits_only="только_хиты"] [action_only="только акции"] [new_only="только_новинки"]
 * [discount_only="только_со_скидкой"]
 * [only_module="only_on_module_page"] [template="шаблон"]>:
 * блок товаров
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

if (empty($result["rows"]))
{
	return false;
}

//заголовок блока
if(! empty($result["name"]))
{
	echo '<h2>'.$result["name"].'</h2>';
}

//товары в разделе
if(! empty($result["rows"]))
{	
	echo '<div class="shop-pane">';	
		echo $this->get($result["view_rows"], 'shop', $result);
	echo '</div>';
}

<?php
/**
 * Шаблон блока отзывов
 * 
 * Шаблонный тег <insert name="show_block" module="reviews" [count="количество"]
 * [element_id="элементы"] [modules="модули"]
 * [sort="порядок_вывода"] [template="шаблон"]>:
 * блок отзывов
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

if(empty($result['rows'])) return false;

echo '<div class="block">';
echo '<div class="block_header">'.$this->diafan->_('Последние отзывы').'</div>';

//отзывы
echo $this->get($result["view_rows"], 'reviews', $result);

echo '</div>';
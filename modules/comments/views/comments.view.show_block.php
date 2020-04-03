<?php
/**
 * Шаблон блока комментариев
 * 
 * Шаблонный тег <insert name="show_block" module="comments" [count="количество"]
 * [element_id="элементы"] [modules="модули"]
 * [sort="порядок_вывода"] [template="шаблон"]>:
 * блок комментариев
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

echo '<div class="block forum">';

echo '<h3>'.$this->diafan->_('Последние комментарии').'</h3>';

//комментарии
echo $this->get($result["view_rows"], 'comments', $result);

echo '</div>';
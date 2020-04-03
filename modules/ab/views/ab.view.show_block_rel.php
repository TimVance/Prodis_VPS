<?php
/**
 * Шаблон блока похожих объявлений
 * 
 * Шаблонный тег <insert name="show_block_rel" module="ab" [count="количество"]
 * [images="количество_изображений"] [images_variation="тег_размера_изображений"]
 * [template="шаблон"]>:
 * блок похожих объявлений
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

if(empty($result["rows"])) return false;

echo '<div class="block_header">'.$this->diafan->_('Похожие объявления').'</div>
<div class="ab_block_rel">';
	echo $this->get($result["view_rows"], 'ab', $result);
echo '</div>';
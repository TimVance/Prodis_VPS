<?php
/**
 * Шаблон облака тегов
 *
 * Шаблонный тег <insert name="show_block" module="tags" [template="шаблон"]>:
 * облако тегов
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

if(! $result["rows"]) return false;

echo '<div class="tags_block">';

if (! $result["title_no_show"])
{
	echo '<div class="block_header">'.$this->diafan->_('Теги').'</div>';
}

echo $this->get($result["view_rows"], 'tags', $result);

echo '</div>';
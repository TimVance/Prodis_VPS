<?php
/**
 * Шаблон списка фотографий для модуля «Теги»
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

//название и описание категории
echo '<div class="tags_list">';

//фотографии
if(! empty($result["rows"]))
{
	echo $this->get('rows_tags', 'photo', $result);
	// echo '<div class="clear"></div>';
}

echo '</div>';
<?php
/**
 * Шаблон блока объявлений
 * 
 * Шаблонный тег <insert name="show_block" module="ab" [count="количество"]
 * [cat_id="категория"] [site_id="страница_с_прикрепленным_модулем"]
 * [images="количество_изображений"] [images_variation="тег_размера_изображений"]
 * [sort="порядок_вывода"] [param="дополнительные_условия"]
 * [only_module="выводить_только_на_странице_модуля"] [template="шаблон"]>:
 * блок объявлений
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

echo '<div class="ab_block">';

//заголовок блока
if (! empty($result["name"]))
{
	echo '<div class="block_header">'.$result["name"].'</div>';
}

//объявления
echo $this->get($result["view_rows"], 'ab', $result);

//ссылка на все объявления
if(! empty($result["link_all"]))
{
	echo '<div class="show_all"><a href="'.BASE_PATH_HREF.$result["link_all"].'">';
	if ($result["category"])
	{
		echo $this->diafan->_('Посмотреть все объявления в категории «%s»', true, $result["name"]);
	}
	else
	{
		echo $this->diafan->_('Все объявления');
	}
	echo '</a></div>';
}

echo '</div>';
<?php
/**
 * Шаблон первой страницы модуля, если в настройках модуля подключен параметр «Использовать категории»
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

if (empty($result["categories"])) return false;

//категории
foreach ($result["categories"] as $cat_id => $cat)
{
	echo '<div class="faq_list">';

	//название категории
	echo '<div class="block_header">'.$cat["name"];

	//рейтинг категории
	if (! empty($cat["rating"]))
	{
		echo $cat["rating"];
	}
	echo '</div>';

	//краткое описание категории
	if (! empty($cat["anons"]))
	{
		echo '<div class="faq_cat_anons">'.$cat['anons'].'</div>';
	}

	//подкатегории
	if (! empty($cat["children"]))
	{
		foreach ($cat["children"] as $child)
		{
			echo '<div class="faq_cat_link">';

			//название и ссылка подкатегории
			echo '<a href="'.BASE_PATH_HREF.$child["link"].'">'.$child["name"].'</a>';

			//рейтинг подкатегории
			if(! empty($child["rating"]))
			{
				echo $child["rating"];
			}

			//краткое описание подкатегории
			if (! empty($child["anons"]))
			{
				echo '<div class="faq_cat_anons">'.$child['anons'].'</div>';
			}
			//вопросы подкатегории
			if(! empty($child["rows"]))
			{
				$res = $result; unset($res["show_more"]);
				$res["rows"] = $child["rows"];
				echo $this->get('rows', 'faq', $res);
			}
			echo '</div>';
		}
	}

	//вопросы в категории
	if ($cat["rows"])
	{
		$res = $result; unset($res["show_more"]);
		$res["rows"] = $cat["rows"];
		echo $this->get('rows', 'faq', $res);
	}
	//ссылка на все вопросы в категории
	if ($cat["link_all"])
	{
		echo '<div class="show_all"><a href="'.BASE_PATH_HREF.$cat["link_all"].'">'
		.$this->diafan->_('Посмотреть все вопросы в категории «%s»', true, $cat["name"])
		.'</a></div>';
	}
	echo '</div>';
}

//Кнопка "Показать ещё"
if(! empty($result["show_more"]))
{
	echo $result["show_more"];
}

//постраничная навигация
if(! empty($result["paginator"]))
{
	echo $result["paginator"];
}

//форма добавления вопроса
if(! empty($result["form"]) && empty($result["ajax"]))
{
	echo $this->get('form', 'faq', $result["form"]);
}

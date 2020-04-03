<?php
/**
 * Шаблон вывода первой страницы модуля, если в настройках модуля подключен параметр «Использовать категории»
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

if (empty($result["categories"]))
	return false;

//начало большого цикла, вывод категорий и объявлений в них
foreach ($result["categories"] as $cat_id => $cat)
{
	echo '<div class="ab_list">';

	//вывод названия категории
	echo '<div class="block_header">'.$cat["name"];

	//рейтинг категории
	if (! empty($cat["rating"]))
	{
		echo $cat["rating"];
	}
	echo '</div>';

	//вывод изображений категории
	if (! empty($cat["img"]))
	{
		echo '<div class="ab_cat_img">';
		foreach ($cat["img"] as $img)
		{
			switch ($img["type"])
			{
				case 'animation':
					echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$cat_id.'ab">';
					break;
				case 'large_image':
					echo '<a href="'.BASE_PATH.$img["link"].'" rel="large_image" width="'.$img["link_width"].'" height="'.$img["link_height"].'">';
					break;
				default:
					echo '<a href="'.BASE_PATH_HREF.$img["link"].'">';
					break;
			}
			echo '<img src="'.$img["src"].'" width="'.$img["width"].'" height="'.$img["height"].'" alt="'.$img["alt"].'" title="'.$img["title"].'">'
			. '</a> ';
		}
		echo '</div>';
	}

	//краткое описание категории
	if (! empty($cat["anons"]))
	{
		echo '<div class="ab_cat_anons">'.$cat['anons'].'</div>';
	}

	//подкатегории
	if (! empty($cat["children"]))
	{
		foreach ($cat["children"] as $child)
		{
			echo '<div class="ab_cat_link">';

			//изображения подкатегории
			if (! empty($child["img"]))
			{
				echo '<div class="ab_cat_img">';
				foreach($child["img"] as $img)
				{
					switch ($img["type"])
					{
						case 'animation':
							echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$child["id"].'ab">';
							break;
						case 'large_image':
							echo '<a href="'.BASE_PATH.$img["link"].'" rel="large_image" width="'.$img["link_width"].'" height="'.$img["link_height"].'">';
							break;
						default:
							echo '<a href="'.BASE_PATH_HREF.$img["link"].'">';
							break;
					}
					echo '<img src="'.$img["src"].'" width="'.$img["width"].'" height="'.$img["height"].'" alt="'.$img["alt"].'" title="'.$img["title"].'">'
					. '</a> ';
				}
				echo '</div>';
			}

			//название и ссылка подкатегории
			echo '<a href="'.BASE_PATH_HREF.$child["link"].'">'.$child["name"].' ('.$child["count"].')';

			//рейтинг подкатегории
			if (! empty($child["rating"]))
			{
				echo ' '.$child["rating"];
			}
			echo '</a>';

			//краткое описание подкатегории
			if (! empty($child["anons"]))
			{
				echo '<div class="ab_cat_anons">'.$child['anons'].'</div>';
			}

			//вывод списка объявлений подкатегории
			if (! empty($child["rows"]))
			{
				//вывод сортировки объявлений
				if(! empty($child["link_sort"]))
				{
					echo $this->get('sort_block', 'ab', $result);
				}

				$res = $result; unset($res["show_more"]);
				$res["rows"] = $child["rows"];
				echo $this->get('rows', 'ab', $res);
			}
			echo '</div>';
		}
	}

	//вывод объявлений в категории
	if(! empty($cat["rows"]))
	{
		$res = $result; unset($res["show_more"]);
		$res["rows"] = $cat["rows"];
		echo $this->get('rows', 'ab', $res);
	}

	//ссылка на все объявления в категории
	if ($cat["link_all"])
	{
		echo '<div class="show_all"><a href="'.BASE_PATH_HREF.$cat["link_all"].'">'
		. $this->diafan->_('Посмотреть все объявления в категории «%s»', true, $cat["name"])
		. ' ('.$cat["count"].')</a></div>';
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

//форма добавления объявления
if (! empty($result["form"]) && empty($result["ajax"]))
{
	echo $this->get('form', 'ab', $result["form"]);
}

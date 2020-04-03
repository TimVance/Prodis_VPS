<?php
/**
 * Шаблон список объявлений
 *
 * Шаблон вывода списка объявлений
 * в категории объявлений, в результатах поиска или если группировка не используется
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

if(! empty($result["error"]))
{
	echo '<p>'.$result["error"].'</p>';
	return;
}

if(empty($result["ajax"]))
{
	echo '<div class="ab_list">';
}

//вывод описания текущей категории
if (! empty($result["text"]))
{
	echo '<div class="ab_cat_text">'.$result['text'].'</div>';
}

//рейтинг категории
if(! empty($result["rating"]))
{
	echo $result["rating"];
}

//вывод изображений текущей категории
if (! empty($result["img"]))
{
	echo '<div class="ab_cat_all_img">';
	foreach($result["img"] as $img)
	{
		switch ($img["type"])
		{
			case 'animation':
				echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$result["id"].'ab">';
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

//вывод подкатегории
if(! empty($result["children"]))
{
	foreach($result["children"] as $child)
	{
		echo '<div class="ab_cat_link">';

		//вывод изображений подкатегории
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
		echo '<a href="'.BASE_PATH_HREF.$child["link"].'">'.$child["name"].' ('.$child["count"].')</a>';

		//рейтинг подкатегории
		if(! empty($child["rating"]))
		{
			echo $child["rating"];
		}

		//краткое описание подкатегории
		if($child["anons"])
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
			echo $this->get($result["view_rows"], 'ab', $res);
		}
		echo '</div>';
	}
}

//вывод списка объявлений
if(! empty($result["rows"]))
{
	//вывод сортировки объявлений
	if(! empty($result["link_sort"]))
	{
		echo $this->get('sort_block', 'ab', $result);
	}

	echo $this->get($result["view_rows"], 'ab', $result);
}

//постраничная навигация
if(! empty($result["paginator"]))
{
	echo $result["paginator"];
}

//вывод комментариев ко всей категории объявлений (комментарии к конкретному товару в функции id())
if(! empty($result["comments"]))
{
	echo $result["comments"];
}

//ссылки на предыдущую и последующую категории
if(! empty($result["previous"]) || ! empty($result["next"]))
{
	echo '<div class="previous_next_links">';
	if(! empty($result["previous"]))
	{
		echo '<div class="previous_link"><a href="'.BASE_PATH_HREF.$result["previous"]["link"].'">&larr; '.$result["previous"]["text"].'</a></div>';
	}
	if(! empty($result["next"]))
	{
		echo '<div class="next_link"><a href="'.BASE_PATH_HREF.$result["next"]["link"].'">'.$result["next"]["text"].' &rarr;</a></div>';
	}
	echo '</div>';
}

if(empty($result["ajax"]))
{
	echo '</div>';

	//форма добавления объявления
	if (! empty($result["form"]))
	{
		echo $this->get('form', 'ab', $result["form"]);
	}
}

<?php
/**
 * Шаблон список новостей
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

echo '<div class="news_list">';

//вывод описания текущей категории новостей
if(! empty($result["text"]))
{
	echo '<div class="news_cat_text">'.$result['text'].'</div>';
}

//рейтинг категории
if(! empty($result["rating"]))
{
	echo $result["rating"];
}

//вывод изображений текущей категории
if(! empty($result["img"]))
{
	echo '<div class="news_cat_all_img">';
	foreach($result["img"] as $img)
	{
		switch($img["type"])
		{
			case 'animation':
				echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$result["id"].'news">';
				break;
			case 'large_image':
				echo '<a href="'.BASE_PATH.$img["link"].'" rel="large_image" width="'.$img["link_width"].'" height="'.$img["link_height"].'">';
				break;
			default:
				echo '<a href="'.BASE_PATH_HREF.$img["link"].'">';
				break;
		}
		echo '<img src="'.$img["src"].'" width="'.$img["width"].'" height="'.$img["height"].'" alt="'.$img["alt"].'" title="'.$img["title"].'">'
		.'</a> ';
	}
	echo '</div>';
}

//вывод подкатегорий
if(! empty($result["children"]))
{
	foreach($result["children"] as $child)
	{
		echo '<div class="news_cat_link">';

		//изображение подкатегории
		if(! empty($child["img"]))
		{
			echo '<div class="news_cat_img">';
			foreach($child["img"] as $img)
			{
				switch($img["type"])
				{
					case 'animation':
						echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$child["id"].'news">';
						break;
					case 'large_image':
						echo '<a href="'.BASE_PATH.$img["link"].'" rel="large_image" width="'.$img["link_width"].'" height="'.$img["link_height"].'">';
						break;
					default:
						echo '<a href="'.BASE_PATH_HREF.$img["link"].'">';
						break;
				}
				echo '<img src="'.$img["src"].'" width="'.$img["width"].'" height="'.$img["height"].'" alt="'.$img["alt"].'" title="'.$img["title"].'">'
				.'</a> ';
			}
			echo '</div>';
		}

		//название и ссылка подкатегории
		echo '<a href="'.BASE_PATH_HREF.$child["link"].'">'.$child["name"].'</a>';

		//рейтинг подкатегории
		if(! empty($child["rating"]))
		{
			echo $child["rating"];
		}

		//краткое описание подкатегории
		if($child["anons"])
		{
			echo '<div class="news_cat_anons">'.$child['anons'].'</div>';
		}

		//новости подкатегории
		if(! empty($child["rows"]))
		{
			$res = $result; unset($res["show_more"]);
			$res["rows"] = $child["rows"];
			echo $this->get($result["view_rows"], 'news', $res);
		}
		echo '</div>';
	}
}

//вывод списка новостей
if(! empty($result["rows"]))
{
	echo $this->get($result["view_rows"], 'news', $result);
}

//вывод постраничная навигация в конце списка новостей
if(! empty($result["paginator"]))
{
	echo $result["paginator"];
}

//вывод ссылок на предыдущую и последующую категории
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

//вывод комментариев к категориям, если они подключены в настройках
if(! empty($result["comments"]))
{
	echo $result["comments"];
}

echo '</div>';

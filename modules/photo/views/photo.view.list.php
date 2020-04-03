<?php
/**
 * Шаблон списка фотографий
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
echo '<div class="photo_list">';

//вывод описания текущей категории
if(! empty($result["text"]))
{
	echo '<div class="photo_cat_text">'.$result['text'].'</div>';
}

//рейтинг альбома
if(! empty($result["rating"]))
{
	echo '<div class="photo_cat_rating">'.$result["rating"].'</div>';
}

//вывод изображений текущей категории
if(! empty($result["img"]))
{
	echo '<div class="photo_cat_img">';
	foreach($result["img"] as $img)
	{
		switch($img["type"])
		{
			case 'animation':
				echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$result["id"].'photo">';
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
	echo '<div class="photo_cats">';

	foreach($result["children"] as $child)
	{
		echo '<div class="photo_cat">';

		//вывод названий и ссылок на подкатегории
		echo '<a href="'.BASE_PATH_HREF.$child["link"].'" class="photo_cat_link">'.$child["name"].' ('.$child["count"].')</a>';

		//рейтинг подкатегории
		if(! empty($child["rating"]))
		{
			echo '<div class="photo_cat_rating">'.$child["rating"].'</div>';
		}

		//вывод изображений альбома
		if(! empty($child["img"]))
		{
			echo '<div class="photo_cat_img">';
			foreach($child["img"] as $img)
			{
				switch($img["type"])
				{
					case 'animation':
						echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$cat_id.'photo">';
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
		elseif(! empty($child["rows"]))
		{
			//фотографии подкатегории
			echo '<div class="photo_cat_images">';
			
			$res = $result; unset($res["show_more"]);
			$res["rows"] = $child["rows"];
			echo $this->get($result["view_rows"], 'photo', $res);
			
			echo '</div>';		
		}

		//краткое описание подкатегории
		if($child["anons"])
		{
			echo '<div class="photo_cat_anons">'.$child['anons'].'</div>';
		}
		echo '</div>';
	}
	echo '</div>';
}

//вывод списка фотографий
if(! empty($result['rows']))
{	
	echo '<div class="photo_images">';
		echo $this->get($result["view_rows"], 'photo', $result);
	echo '</div>';
}

//вывод комментариев к категориям, если подключены в настройках
if(! empty($result["comments"]))
{
	echo $result["comments"];
}

//вывод постраничной навигации
if(! empty($result["paginator"]))
{
	echo $result["paginator"];
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

echo '</div>';
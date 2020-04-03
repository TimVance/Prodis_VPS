<?php
/**
 * Шаблон элементов в списке новостей
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

//вывод списка новостей
foreach ($result["rows"] as $row)
{		           
	echo '<div class="news block">';

	//вывод изображений новости
	if (! empty($row["img"]))
	{			
		foreach ($row["img"] as $img)
		{
			switch($img["type"])
			{
				case 'animation':
					echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$row["id"].'news" class="block-row-img">';
					break;
				case 'large_image':
					echo '<a href="'.BASE_PATH.$img["link"].'" rel="large_image" width="'.$img["link_width"].'" height="'.$img["link_height"].'" class="block-row-img">';
					break;
				default:
					echo '<a href="'.BASE_PATH_HREF.$img["link"].'" class="block-row-img">';
					break;
			}
			echo '<img src="'.$img["src"].'" width="'.$img["width"].'" height="'.$img["height"].'" alt="'.$img["alt"].'" title="'.$img["title"].'">'
			.'</a> ';
		}			
	}

	echo '<div class="block-text">';
		   
		//вывод названия и ссылки на новость
		echo '<h4>';
			echo '<a href="'.BASE_PATH_HREF.$row["link"].'" class="black">'.$row["name"].'</a>';		
		echo '</h4>';

		//вывод рейтинга новости за названием, если рейтинг подключен
		if (! empty($row["rating"]))
		{
			echo '<div class="news_rating rate"> ' .$row["rating"] . '</div>';
		}

		//вывод анонса новостей
		if(! empty($row["anons"]))
		{
			echo '<div class="news_anons anons">'.$row['anons'].'</div>';
		}

		//вывод даты новости
		if (! empty($row['date']))
		{
			echo '<div class="news_date date">'.$row["date"]."</div>";
		}		

		//вывод прикрепленных тегов к новости
		if(! empty($row["tags"]))
		{
			echo $row["tags"];
		}		

		echo '</div>';

	echo '</div>';
}

//Кнопка "Показать ещё"
if(! empty($result["show_more"]))
{
	echo $result["show_more"];
}
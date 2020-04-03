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
	echo '<div class="col-md-6 col-xl-4">';
		echo '<div class="news-card white-box">';
		
		//вывод изображений новости
		if (! empty($row["img"]))
		{			
			foreach ($row["img"] as $img)
			{
				switch($img["type"])
				{
					case 'animation':
						echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$row["id"].'news" class="news-card__cover">';
						break;
					case 'large_image':
						echo '<a href="'.BASE_PATH.$img["link"].'" rel="large_image" width="'.$img["link_width"].'" height="'.$img["link_height"].'" class="news-card__cover">';
						break;
					default:
						echo '<a href="'.BASE_PATH_HREF.$img["link"].'" class="news-card__cover">';
						break;
				}
				echo '<img src="'.$img["src"].'" width="'.$img["width"].'" height="'.$img["height"].'" alt="'.$img["alt"].'" title="'.$img["title"].'">'
				.'</a> ';
			}			
		}

		echo '<div class="news-card__text">';
			
			//вывод названия и ссылки на новость
			echo '<h4 class="news-card__title title-decor">';
				echo '<a href="'.BASE_PATH_HREF.$row["link"].'" class="black">'.$row["name"].'</a>';		
			echo '</h4>';

			//вывод рейтинга новости за названием, если рейтинг подключен
			if (! empty($row["rating"]))
			{
				echo '<div class="news-card__rating rate"> ' .$row["rating"] . '</div>';
			}

			//вывод анонса новостей
			if(! empty($row["anons"]))
			{
				//echo '<div class="news-card__anons anons">'.$row['anons'].'</div>';

				echo '<div class="news-card__anons anons">'.(strlen($row['anons']) > 445 ? mb_substr($row['anons'], 0, 445)."..." : $row['anons']).'</div>';

			}

			//вывод прикрепленных тегов к новости
			if(! empty($row["tags"]))
			{
				echo $row["tags"];
			}		

			echo '<div class="news-card__bottom">';
				//вывод даты новости
				if (! empty($row['date']))
				{
					echo '<div class="news-card__date date">'.$row["date"]."</div>";
				}		

				echo '<a href="'.BASE_PATH_HREF.$row["link"].'" class="news-card__more">читать далее</a>';

			echo '</div>';

			echo '</div>';
		echo '</div>';
	echo '</div>';
}

//Кнопка "Показать ещё"
if(! empty($result["show_more"]))
{
	echo $result["show_more"];
}
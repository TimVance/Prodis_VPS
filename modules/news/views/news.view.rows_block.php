<?php
/**
 * Шаблон блока новостей
 * 
 * Шаблонный тег <insert name="show_block" module="news" [count="количество"]
 * [cat_id="категория"] [site_id="страница_с_прикрепленным_модулем"]
 * [images="количество_изображений"] [images_variation="тег_размера_изображений"]
 * [only_module="only_on_module_page"] [template="шаблон"]>:
 * блок новостей
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

//новости
foreach ($result["rows"] as $row)
{
	echo '<div class="news block-row '.(empty($row["img"]) ? 'block-no-img' : '').'">';

	//изображения новости
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
			echo '<img src="'.$img["src"].'" width="'.$img["width"].'" height="'.$img["height"].'" alt="'.$img["alt"].'" title="'.$img["title"].'" class="block-row-img">'
			.'</a> ';
		}		
	}

	echo '<div class="block-text">';

		//название и ссылка новости
		echo '<h4><a href="'.BASE_PATH_HREF.$row["link"].'" class="black">'.$row['name'].'</a></h4>';
		
		//рейтинг новости
		if (! empty($row["rating"]))
		{
			echo '<div class="news_rating rate"> ' .$row["rating"] . '</div>';
		}		
		
	    //анонс новости		
		echo '<div class="news_anons anons"><a href="'.BASE_PATH_HREF.$row["link"].'" class="black">'.$row['anons'].'</a></div>';

		//дата новости
		if (! empty($row["date"]))
		{
			echo '<div class="news_date date">'.$row["date"].'</div>';
		}

	echo '</div>';				

	echo '</div>';
}
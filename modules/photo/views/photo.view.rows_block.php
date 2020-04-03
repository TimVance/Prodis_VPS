<?php
/**
 * Шаблон блока фотографий
 * 
 * Шаблонный тег <insert name="show_block" module="photo" [count="количество"]
 * [cat_id="категория"] [site_id="страница_с_прикрепленным_модулем"]
 * [sort="порядок_вывода"] 
 * [images_variation="тег_размера_изображений"]
 * [only_module="only_on_module_page"] [template="шаблон"]>:
 * блок фотографий
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

//фотографии
foreach ($result["rows"] as $row)
{
	echo '<div class="photo-item">';

	//изображение
	if (! empty($row["img"]))
	{		
		switch($row["img"]["type"])
		{
			case 'animation':
				echo '<a href="'.BASE_PATH.$row["img"]["link"].'" data-fancybox="galleryphotoblock">';
				break;
			case 'large_image':
				echo '<a href="'.BASE_PATH.$row["img"]["link"].'" rel="large_image" width="'.$row["img"]["link_width"].'" height="'.$row["img"]["link_height"].'">';
				break;
			default:
				echo '<a href="'.BASE_PATH_HREF.$row["img"]["link"].'">';
				break;
		}
		echo '<img src="'.$row["img"]["src"].'" width="'.$row["img"]["width"].'" height="'.$row["img"]["height"]
		.'" alt="'.$row["img"]["alt"].'" title="'.$row["img"]["title"].'" class="photo-image">'
		.'</a>';
	}

	//название и ссылка фотографии
	if ($row["name"])
	{		
		echo '<p>';
		if ($row["link"])
		{
			echo '<a href="'.BASE_PATH_HREF.$row["link"].'" class="black">';
		}
		echo $row["name"];
		if ($row["link"])
		{
			echo '</a>';
		}
		echo '</p>';
	}

	//вывод рейтинга фотографии
	if (! empty($row["rating"]))
	{
		echo '<div class="rate">' . $row["rating"] . '</div>';
	}
	
	echo '</div>';
}
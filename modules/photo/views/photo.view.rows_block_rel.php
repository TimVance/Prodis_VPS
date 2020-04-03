<?php
/**
 * Шаблон блока похожих фотографий
 * 
 * Шаблонный тег <insert name="show_block_rel" module="photo" [count="количество"]
 * [images_variation="тег_размера_изображений"]
 * [template="шаблон"]>:
 * блок похожих фотографий
 * 
 * @package    DIAFAN.CMS
 *
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
	echo '<div class="photo">';

	//вывод рейтинга фотографии
	if (! empty($row["rating"]))
	{
		echo $row["rating"];
	}

	//изображение
	if (! empty($row["img"]))
	{
		echo '<div class="photo_img">';
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
		.'" alt="'.$row["img"]["alt"].'" title="'.$row["img"]["title"].'">'
		.'</a></div>';
	}

	//название и ссылка фотографии
	if ($row["name"])
	{
		echo '<div class="photo_name">';
		if ($row["link"])
		{
			echo '<a href="'.BASE_PATH_HREF.$row["link"].'">';
		}
		echo $row["name"];
		if ($row["link"])
		{
			echo '</a>';
		}
		echo '</div>';
	}

	//краткое описание фотографии
	if(! empty($row["anons"]))
	{
		echo '<div class="photo_anons">'.$row['anons'].'</div>';
	}

	//теги фотографии
	if(! empty($row["tags"]))
	{
		echo $row["tags"];
	}
	echo '</div>';
}
<?php
/**
 * Шаблон блока похожих статей
 * 
 * Шаблонный тег <insert name="show_block_rel" module="clauses" [count="количество"]
 * [images="количество_изображений"] [images_variation="тег_размера_изображений"]
 * [template="шаблон"]>:
 * блок похожих статей
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

//статьи
foreach ($result["rows"] as $row)
{
	echo '<div class="clauses">';

	//изображения статьи
	if (! empty($row["img"]))
	{
		echo '<div class="clauses_img">';
		foreach ($row["img"] as $img)
		{
			switch($img["type"])
			{
				case 'animation':
					echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$row["id"].'clauses">';
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

	echo '<div class="clauses_block_text">';

		//название и ссылка статьи
	echo '<div class="clauses_name"><a href="'.BASE_PATH_HREF.$row["link"].'">'.$row['name'].'</a>';
	//рейтинг статьи
	if (! empty($row["rating"]))
	{
		echo $row["rating"];
	}
	echo '</div>';

	//анонс статьи
	echo '<div class="clauses_anons">'.$row['anons'].'</div>';

	//теги статьи
	if(! empty($row["tags"]))
	{
		echo $row["tags"];
	}

		//дата статьи
	if (! empty($row["date"]))
	{
		echo '<div class="clauses_date">'.$row["date"].'</div>';
	}

	echo '</div>';

	echo '</div>';
}
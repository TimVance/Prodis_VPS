<?php
/**
 * Шаблон блока похожих новостей
 * 
 * Шаблонный тег <insert name="show_block_rel" module="news" [count="количество"]
 * [images="количество_изображений"] [images_variation="тег_размера_изображений"]
 * [template="шаблон"]>:
 * блок похожих новостей
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
	echo '<div class="news block">';
	
	//изображения новости
	if (! empty($row["img"]))
	{
		echo '<div class="news_img">';
		foreach ($row["img"] as $img)
		{
			switch($img["type"])
			{
				case 'animation':
					echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$row["id"].'news">';
					break;
				case 'large_image':
					echo '<a href="'.BASE_PATH.$img["link"].'" rel="large_image" width="'.$img["link_width"].'" height="'.$img["link_height"].'">';
					break;
				default:
					echo '<a href="'.BASE_PATH_HREF.$img["link"].'">';
					break;
			}
			echo '<img src="'.$img["src"].'" width="'.$img["width"].'" height="'.$img["height"].'" alt="'.$img["alt"].'" title="'.$img["title"].'" class="block-row-img">'
			.'</a> ';
		}
		echo '</div>';
	}

	echo '<div class="block-text">';

		//название и ссылка новости
		echo '<h4><a href="'.BASE_PATH_HREF.$row["link"].'" class="black">'.$row['name'].'</a></h4>';

		//рейтинг новости
		if (! empty($row["rating"]))
		{
			echo '<div class="news_rating rate">'.$row["rating"].'</div>';
		}

		//анонс новости
		echo '<div class="news_anons">'.$this->htmleditor($row['anons']).'</div>';

		//дата новости
		if (! empty($row["date"]))
		{
			echo '<div class="news_date date">'.$row["date"].'</div>';
		}

		//вывод прикрепленных тегов к новости
		if(! empty($row["tags"]))
		{
			echo $row["tags"];
		}

	echo '</div>';

	echo '</div>';
}
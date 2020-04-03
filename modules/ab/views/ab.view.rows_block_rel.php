<?php
/**
 * Шаблон блока похожих объявлений
 * 
 * Шаблонный тег <insert name="show_block_rel" module="ab" [count="количество"]
 * [images="количество_изображений"] [images_variation="тег_размера_изображений"]
 * [template="шаблон"]>:
 * блок похожих объявлений
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

foreach ($result["rows"] as $row)
{
	echo '<table class="js_ab ab"><tr><td colspan=2 valign=top>';

	//вывод названия и ссылки
	echo '<div class="ab_name">';
	echo '<a href="'.BASE_PATH_HREF.$row["link"].'">'.$row["name"].'</a>';

	//рейтинг
	if (!empty($row["rating"]))
	{
		echo ' '.$row["rating"];
	}
	echo '</div>';

	echo '</td></tr><tr><td valign=top>';

	//изображения
	if (!empty($row["img"]))
	{
		echo '<div class="ab_img">';
		foreach ($row["img"] as $img)
		{
			switch ($img["type"])
			{
				case 'animation':
					echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$row["id"].'ab">';
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

	echo '</td>';
	echo '<td valign=top>';

	//дата
	if (! empty($row["date"]))
	{
		echo '<div class="ab_date">'.$row["date"].'</div>';
	}

	// параметры
	if (!empty($row["param"]))
	{
		echo $this->get('param', 'ab', array("rows" => $row["param"], "id" => $row["id"]));
	}

	//краткое описание
	if (!empty($row["anons"]))
	{
		echo '<div class="ab_anons">'.$row['anons'].'</div>';
	}

	//теги объявления
	if (!empty($row["tags"]))
	{
		echo $row["tags"];
	}
	echo '</td></tr></table>';
}
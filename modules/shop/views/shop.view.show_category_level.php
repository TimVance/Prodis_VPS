<?php
/**
 * Шаблон вложенных уровней блока категорий
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

if (empty($result["rows"][$result["parent_id"]]))
{
	return false;
}

echo '<div class="shop_category_block_'.$result["level"].'">';

//вывод категорий
foreach ($result["rows"][$result["parent_id"]] as $row)
{
	echo '<div class="shop_category">';

	//изображения категорий
	if (! empty($row["img"]))
	{
		echo '<div class="shop_category_img">';
		foreach ($row["img"] as $img)
		{
			switch ($img["type"])
			{
				case 'animation':
					echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$row["id"].'shop_category">';
					break;

				case 'large_image':
					echo '<a href="'.BASE_PATH.$img["link"].'" rel="large_image" width="'.$img["link_width"].'" height="'.$img["link_height"].'">';
					break;

				default:
					echo '<a href="'.BASE_PATH_HREF.$img["link"].'">';
					break;
			}
			echo '<img src="'.$img["src"].'" width="'.$img["width"].'" height="'.$img["height"].'" alt="'.$img["alt"].'" title="'.$img["title"].'">';		
			echo '</a> ';
		}
		echo '</div>';
	}

	//название и ссылка категории
	echo '<a href="'.BASE_PATH_HREF.$row["link"].'" class="shop_category_name">'.$row["name"];
	if(isset($row["number_elements"]))
	{
		echo ' ('.$row["number_elements"].')';
	}
	echo '</a>';

	//описание категории
	//if(! empty($row["anons"]))
	//{
	//	echo $row['anons'];
	//}
	if(! empty($result["rows"][$row["id"]]))
	{
		$res = $result;
		$res["level"] = $result["level"] + 1;
		$res["parent_id"] = $row["id"];
		echo $this->get('show_category_level', 'shop', $res);
	}
	echo '</div>';
}
echo '</div>';
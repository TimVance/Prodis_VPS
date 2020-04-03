<?php
/**
 * Шаблон блока производителей
 *
 * Шаблонный тег <insert name="show_brand" module="shop" [count="количество"]
 * [cat_id="категория"] [site_id="страница_с_прикрепленным_модулем"]
 * [images="количество_изображений"] [images_variation="тег_размера_изображений"]
 * [only_module="true|false"]
 * [template="шаблон"]>:
 * блок производителей
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

if (empty($result["rows"]))
{
	return;
}

//заголовок блока
echo '<div class="block_header">'.$this->diafan->_('Производители').'</div>';

echo '<div class="shop_brand_block">';

//вывод производителей
foreach ($result["rows"] as $row)
{
	echo '<div class="shop_brand">';

	//изображения производителя
	if(! empty($row["img"]))
	{
		echo '<div class="shop_brand_img">';
		foreach ($row["img"] as $img)
		{
			switch ($img["type"])
			{
				case 'animation':
					echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$row["id"].'shop_brand">';
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

	//название и ссылка производителя
	echo '<a href="'.BASE_PATH_HREF.$row["link"].'" class="shop_brand_name">'.$row["name"].'</a>';

	//описание производителя
	//if (! empty($row["text"]))
	//{
	//	echo $row['text'];
	//}
	echo '</div>';
}
echo '</div>';
<?php
/**
 * Шаблон блока файлов
 * 
 * Шаблонный тег <insert name="show_block" module="files" [count="количество"]
 * [cat_id="категория"] [site_id="страница_с_прикрепленным_модулем"]
 * [sort="порядок_вывода"] 
 * [images="количество_изображений"] [images_variation="тег_размера_изображений"]
 * [only_module="only_on_module_page"] [template="шаблон"]>:
 * блок файлов
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

//фaйлы
foreach ($result["rows"] as $row)
{
	echo '<div class="block-row file-row">';
 
	//изображения файла
	if (! empty($row["img"]))
	{
		echo '<div class="files_img">';
		foreach ($row["img"] as $img)
		{
			switch($img["type"])
			{
				case 'animation':
					echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$row["id"].'files" class="block-row-img">';
					break;
				case 'large_image':
					echo '<a href="'.BASE_PATH.$img["link"].'" rel="large_image" width="'.$img["link_width"].'" height="'.$img["link_height"].'" class="block-row-img">';
					break;
				default:
					echo '<a href="'.BASE_PATH_HREF.$img["link"].'" class="block-row-img">';
					break;
			}
			echo '<img src="'.$img["src"].'" width="'.$img["width"].'" height="'.$img["height"].'" alt="'.$img["alt"].'" title="'.$img["title"].'" class="file-icon">'
			.'</a> ';
		}
		echo '</div>';
	}

		echo '<div class="block-text">';

			//название и ссылка файла	
			echo '<p><a href="'.BASE_PATH_HREF.$row["link"].'" class="black"><strong>'.$row["name"].'</strong><br>';

			//краткое описание файла
			echo $row['anons'].'</a></p>';

			//рейтинг файла
			if (! empty($row["rating"]))
			{
				echo '<div class="rate"> '.$row["rating"] . '</div>';
			}	
	
			//ссылка на скачивание файла
			if(! empty($row["files"]))
			{
				foreach ($row["files"] as $f)
				{
					//размер файла
					if (! empty($f["size"])) echo '<p class="addict-info"><strong>'.$this->diafan->_('Размер').':</strong> '.$f["size"].'</p>';
					
					echo '<a href="'.$f["link"].'" class="button solid download"><i class="fa fa-download"></i>'.$this->diafan->_('Скачать').'</a>';						
				}
			}

		echo '</div>';

	echo '</div>';
}
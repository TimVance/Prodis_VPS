<?php
/**
 * Шаблон элементов в списке статей
 * 
 * Шаблон вывода списка статей в том случае, если в настройках модуля отключен параметр «Использовать категории»
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

foreach ($result["rows"] as $row)
{		
	echo '<div class="block">';

	//изображения статьи
	if (! empty($row["img"]))
	{			
		foreach ($row["img"] as $img)
		{
			switch($img["type"])
			{
				case 'animation':
					echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$row["id"].'clauses" class="block-row-img">';
					break;
				case 'large_image':
					echo '<a href="'.BASE_PATH.$img["link"].'" rel="large_image" width="'.$img["link_width"].'" height="'.$img["link_height"].'" class="block-row-img">';
					break;
				default:
					echo '<a href="'.BASE_PATH_HREF.$img["link"].'" class="block-row-img">';
					break;
			}
			echo '<img src="'.$img["src"].'" width="'.$img["width"].'" height="'.$img["height"].'" alt="'.$img["alt"].'" title="'.$img["title"].'">'
			.'</a> ';
		}			
	}

	echo '<div class="block-text">';

		//название и ссылка статьи
		echo '<h4><a href="'.BASE_PATH_HREF.$row["link"].'" class="black">'.$row["name"].'</a></h4>';
		//рейтинг статьи
		if (! empty($row["rating"]))
		{
			echo $row["rating"];
		}

		//анонс статьи
		if(! empty($row["anons"]))
		{
			echo '<div class="anons">'.$this->htmleditor($row['anons']).'</div>';
		}

		//дата статьи
		if (! empty($row['date']))
		{
			echo '<div class="date">'.$row["date"]."</div>";
		}

		//теги статьи
		if(! empty($row["tags"]))
		{
			echo $row["tags"];
		}	

	echo '</div>';

	echo '</div>';
}

//Кнопка "Показать ещё"
if (! empty($result["show_more"]))
{
	echo $result["show_more"];
}
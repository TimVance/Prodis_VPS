<?php
/**
 * Шаблон дополнительных характеристик объявлений
 *
 * Шаблон вывода дополнительных характеристик объявлений
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

foreach ($result["rows"] as $param)
{
	echo '<div class="ab_param'.($param["type"] == 'title' ? '_title' : '').'">'.
	($param["type"] == "images"?'':$param["name"]); //не выводим заголовок для фотографии
	if ($param["value"])
	{
		echo ' <span class="ab_param_value">';
		if($param["type"] == "attachments")
		{
			foreach ($param["value"] as $a)
			{
				if ($a["is_image"])
				{
					if($param["use_animation"])
					{
						echo ' <a href="'.$a["link"].'" data-fancybox="gallery'.$result["id"].'ab"><img src="'.$a["link_preview"].'"></a> <a href="'.$a["link"].'" data-fancybox="gallery'.$result["id"].'ab_link">'.$a["name"].'</a>';
					}
					else
					{
						echo ' <a href="'.$a["link"].'"><img src="'.$a["link_preview"].'"></a> <a href="'.$a["link"].'">'.$a["name"].'</a>';
					}
				}
				else
				{
					echo ' <a href="'.$a["link"].'">'.$a["name"].'</a>';
				}
			}
		}
		elseif($param["type"] == "images")
		{
			foreach ($param["value"] as $img)
			{
				echo '<img src="'.$img["src"].'" width="'.$img["width"].'" height="'.$img["height"].'" alt="'.$img["alt"].'" title="'.$img["title"].'">';
			}
		}
		elseif(! empty($param["link"]))
		{
			echo '<a href="'.BASE_PATH_HREF.$param["link"].'">'.$param["value"].'</a>';
		}
		elseif (is_array($param["value"]))
		{
			foreach ($param["value"] as $p)
			{
				if ($param["value"][0] != $p)
				{
					echo ', ';
				}
				if (is_array($p))
				{
					if ($p["link"])
					{
						echo '<a href="'.BASE_PATH_HREF.$p["link"].'">'.$p["name"].'</a>';
					}
					else
					{
						echo $p["name"];
					}
				}
				else
				{
					echo $p;
				}
			}
		}
		else
		{
			echo $param["value"];
		}
		//единицы измерения
		if(! empty($param["measure_unit"]) && $param["type"] == 'numtext')
		{
			echo ' '.$param["measure_unit"];
		}
		echo '</span>';
	}
	echo '</div>';
	if($param["text"])
	{
		echo '<div class="ab_param_text">'.$param["text"].'</div>';
	}
}
<?php
/**
 * Шаблон списка элементов, к которым прикреплен тег
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

//вывод описания тега
if(! empty($result["text"]))
{
	echo '<div class="tags_cat_text">'.$result['text'].'</div>';
}

if(! empty($result["img"]))
{
	echo '<div class="news_all_img">';
	foreach($result["img"] as $img)
	{
		switch($img["type"])
		{
			case 'animation':
				echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallerytags">';
				break;
			case 'large_image':
				echo '<a href="'.BASE_PATH.$img["link"].'" rel="large_image" width="'.$img["link_width"].'" height="'.$img["link_height"].'">';
				break;
			default:
				echo '<a href="'.BASE_PATH_HREF.$img["link"].'">';
				break;
		}
		echo '<img src="'.$img["src"].'" width="'.$img["width"].'" height="'.$img["height"].'" alt="'.$img["alt"].'" title="'.$img["title"].'">'
		.'</a>';
	}
	echo '</div>';
}

if(! empty($result["rows"]))
{
	echo $this->get($result["view_rows"], 'tags', $result);
}

//постраничная навигация
if(! empty($result["paginator"]))
{
	echo $result["paginator"];
}
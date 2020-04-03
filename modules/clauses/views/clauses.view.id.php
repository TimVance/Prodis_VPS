<?php
/**
 * Шаблон страницы статьи
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

echo '<div class="clauses_id">';

//рейтинг статьи
if(! empty($result["rating"]))
{
	echo $result["rating"];
}

//дата статьи
if (! empty($result["date"]))
{
	echo '<div class="clauses_date">'.$result["date"]."</div>";
}

//описание статьи
echo '<div class="clauses_text">'.$result['text'].'</div>';

//счетчик просмотров
if(! empty($result["counter"]))
{
	echo '<div class="clauses_counter">'.$this->diafan->_('Просмотров').': '.$result["counter"].'</div>';
}

//теги статьи
if (! empty($result["tags"]))
{
	echo $result["tags"];
}

//изображения статьи
if(! empty($result["img"]))
{
	echo '<div class="clauses_all_img">';
	foreach($result["img"] as $img)
	{
		switch($img["type"])
		{
			case 'animation':
				echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$result["id"].'clauses">';
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

//ссылки на предыдущую и последующую статью
if(! empty($result["previous"]) || ! empty($result["next"]))
{
	echo '<div class="previous_next_links">';
	if(! empty($result["previous"]))
	{
		echo '<div class="previous_link"><a href="'.BASE_PATH_HREF.$result["previous"]["link"].'">&larr; '.$result["previous"]["text"].'</a></div>';
	}
	if(! empty($result["next"]))
	{
		echo '<div class="next_link"><a href="'.BASE_PATH_HREF.$result["next"]["link"].'">'.$result["next"]["text"].' &rarr;</a></div>';
	}
	echo '</div>';
}

//ссылки на все статьи
if (! empty($result["allclauses"]))
{
	echo '<div class="show_all"><a href="'.BASE_PATH_HREF.$result["allclauses"]["link"].'">'.$this->diafan->_('Вернуться к списку').'</a></div>';
}

//комментарии к статье
if(! empty($result["comments"]))
{
	echo $result["comments"];
}
echo '</div>';

echo $this->htmleditor('<insert name="show_block_rel" module="clauses" count="4" images="1">');
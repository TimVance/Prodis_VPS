<?php
/**
 * Шаблон страницы фотографии
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

if(empty($result["ajax"]))
{
	echo '<div class="js_photo_id photo_id">';
}

//рейтинг фотографии
if(! empty($result["rating"]))
{
	echo $result["rating"];
}

//изображение и ссылка на следующее фото
if(! empty($result["img"]))
{
	echo '<div class="photo_img">';
	echo (! empty($result["next"])?'<a href="'.BASE_PATH_HREF.$result["next"]["link"].'" class="js_photo_link_ajax">':'');
	echo '<img src="'.$result["img"]["src"].'" width="'.$result["img"]["width"].'" height="'.$result["img"]["height"].'"	alt="'.$result["img"]["alt"].'" title="'.$result["img"]["title"].'">';
	echo (! empty($result["next"])?'</a>':'');
	echo '</div>';
}

//полное описание фотографии
echo '<div class="photo_text">'.$result['text'].'</div>';

//счетчик просмотров
if(! empty($result["counter"]))
{
	echo '<div class="photo_counter">'.$this->diafan->_('Просмотров').': '.$result["counter"].'</div>';
}

//теги фотографии
if (! empty($result["tags"]))
{
	echo $result["tags"];
}

//ссылки на предыдущую и последующую фотографии
if(! empty($result["previous"]) || ! empty($result["next"]))
{
	echo '<div class="previous_next_links">';
	if(! empty($result["previous"]))
	{
		echo '<div class="previous_link"><a href="'.BASE_PATH_HREF.$result["previous"]["link"].'" class="js_photo_link_ajax">&larr; '.$result["previous"]["text"].'</a></div>';
	}
	if(! empty($result["next"]))
	{
		echo '<div class="next_link"><a href="'.BASE_PATH_HREF.$result["next"]["link"].'" class="js_photo_link_ajax">'.$result["next"]["text"].' &rarr;</a></div>';
	}
	echo '</div>';
}

//комментарии к фотографии
if(! empty($result["comments"]))
{
	echo $result["comments"];
}

if(empty($result["ajax"]))
{
	echo '</div>';
}

echo $this->htmleditor('<insert name="show_block_rel" module="photo" count="4">');
<?php
/**
 * Шаблон блока новостей для мобильной версии
 * 
 * Шаблонный тег <insert name="show_block" module="news" [count="количество"]
 * [cat_id="категория"] [site_id="страница_с_прикрепленным_модулем"]
 * [images="количество_изображений"] [images_variation="тег_размера_изображений"]
 * [template="шаблон"]>:
 * блок новостей
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
	return false;
}
echo '<div class="m_news">';

echo '<div class="block_header">'.$this->diafan->_('Новости').'</div>';

echo '<table class="m_news_block"><tr>';

//новости
foreach ($result["rows"] as $row)
{
	echo '
	<td class="m_news">';

	//дата новости
	if (! empty($row["date"]))
	{
		echo '<div class="m_news_date">'.$row["date"].'</div>';
	}

	//название и ссылка новости
	echo '<div class="m_news_name"><a href="'.BASE_PATH_HREF.$row["link"].'">'.$row['name'].'</a></div>';

	//изображения новости
	if (! empty($row["img"]))
	{
		echo '<div class="m_news_img">';
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
			echo '<img src="'.$img["src"].'" width="'.$img["width"].'" height="'.$img["height"].'" alt="'.$img["alt"].'" title="'.$img["title"].'">'
			.'</a> ';
		}
		echo '</div>';
	}

	//анонс новости
	echo '<div class="m_news_anons">'.$row['anons'].'</div>';

	echo '</td>';
}

echo '</table>';

echo '</div>';
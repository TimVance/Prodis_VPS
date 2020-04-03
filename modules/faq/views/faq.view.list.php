<?php
/**
 * Шаблон списка вопросов и ответов
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

echo '<div class="faq_list">';

//описание текущей категории
if(! empty($result["text"]))
{
	echo '<div class="faq_cat_text">'.$result['text'].'</div>';
}

//рейтинг категории
if(! empty($result["rating"]))
{
	echo $result["rating"];
}

//подкатегории
if(! empty($result["children"]))
{
	foreach($result["children"] as $child)
	{
		echo '<div class="faq_cat_link">';

		//название и ссылка подкатегории
		echo '<a href="'.BASE_PATH_HREF.$child["link"].'">'.$child["name"].'</a>';

		//рейтинг подкатегории
		if(! empty($child["rating"]))
		{
			echo $child["rating"];
		}

		//краткое описание подкатегории
		if($child["anons"])
		{
			echo '<div class="faq_cat_anons">'.$child['anons'].'</div>';
		}
		//вопросы подкатегории
		if(! empty($child["rows"]))
		{
			$res = $result; unset($res["show_more"]);
			$res["rows"] = $child["rows"];
			echo $this->get($result["view_rows"], 'faq', $res);			
		}
		echo '</div>';
	}
}

//комментарии к категории
if(! empty($result["comments"]))
{
	echo $result["comments"];
}

//вопросы
if(! empty($result["rows"]))
{
	echo $this->get($result["view_rows"], 'faq', $result);
}

//постраничная навигация
if(! empty($result["paginator"]))
{
	echo $result["paginator"];
}

//ссылки на предыдущую и последующую категории
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

//форма добавления вопроса
if (! empty($result["form"]))
{
	echo $this->get('form', 'faq', $result["form"]);
}

echo '</div>';
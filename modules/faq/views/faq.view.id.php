<?php
/**
 * Шаблон страницы вопроса
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

echo '<div class="faq_id">';

//дата вопроса
if (! empty($result["date"]))
{
	echo '<div class="faq_date">';
	echo $result["date"];
		//рейтинг вопроса
		if(! empty($result["rating"])) echo $result["rating"];
	echo '</div>';
}

//вопрос
echo '<div class="faq_question">'.$result['anons'].'</div>';

//ответ
echo '<div class="faq_answer">'.$result['text'].'</div>';

//прикрепленные файлы
if (! empty($result["attachments"]))
{
	echo '<div class="faq_attachments">';
	foreach ($result["attachments"] as $att)
	{
		if ($att["is_image"])
		{
			if ($result["use_animation"])
			{
				$a_href  = '<a href="'.$att["link"].'" data-fancybox="gallery'.$att["element_id"].$att["module_name"].'_1">';
				$a_href2 = '<a href="'.$att["link"].'" data-fancybox="gallery'.$att["element_id"].$att["module_name"].'_2">';
			}
			else
			{
				$a_href .= '<a href="'.$att["link"].'" rel="large_image" width="'.$att["width"].'" height="'.$att["height"].'">';
				$a_href2 = $a_href;
			}
			echo
			'<p id="attachment'.$att["id"].'">'
				.$a_href.$att["name"].'</a>'
				.' ('.$att["size"].')'
				.' '.$a_href2.'<img src="'.$att["link_preview"].'"></a>'
			.'</p>';
		}
		else
		{
			echo '<p id="attachment'.$att["id"].'"><a href="'.$att["link"].'">'.$att["name"].'</a>  ('.$att["size"].')</p>';
		}
	}
	echo '</div>';
}

//счетчик просмотров
if(! empty($result["counter"]))
{
	echo '<div class="faq_counter">'.$this->diafan->_('Просмотров').': '.$result["counter"].'</div>';
}

//теги вопроса
if (! empty($result["tags"]))
{
	echo $result["tags"];
}

//комментарии к вопросу
if(! empty($result["comments"]))
{
	echo $result["comments"];
}

//ссылки на предыдущий и последующий вопрос
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

//ссылки на все вопросы
if (! empty($result["allfaq"]))
{
	echo '<div class="show_all"><a href="'.BASE_PATH_HREF.$result["allfaq"]["link"].'">'.$this->diafan->_('Вернуться к списку').'</a></div>';
}

echo '</div>';

//форма добавления вопроса
if (! empty($result["form"]))
{
	echo $this->get('form', 'faq', $result["form"]);
}

echo $this->htmleditor('<insert name="show_block_rel" module="faq" count="4">');
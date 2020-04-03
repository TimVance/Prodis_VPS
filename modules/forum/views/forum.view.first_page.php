<?php
/**
 * Шаблон первой страницы модуля
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

if ($result["new_messages"])
{
	echo '<div class="forum_new_messages"><a href="?action=news">'.$this->diafan->_('Непрочитанные сообщения').' ('.$result["new_messages"].')</a></div>';
}

echo '<table class="forum_list">
<tr><th>'.$this->diafan->_('Разделы').'</th><th>'.$this->diafan->_('Тем').'</th><th>'.$this->diafan->_('Последнее сообщение').'</th></tr>';

// блоки
foreach ($result["blocks"] as $block)
{
	echo '<tr><td class="forum_title" colspan="3">'.$block["name"].'</td></tr>';
	// категории
	if(! empty($result["cats"][$block["id"]]))
	{
		foreach ($result["cats"][$block["id"]] as $row)
		{
			echo '<tr>
				<td class="forum_category_name';
				// в теме есть новые сообщения
				if ($row["news"])
				{
					echo ' forum_news';
				}
				echo '">
					<a href="'.BASE_PATH_HREF.$row["link"].'">'.$row["name"].'</a>
				</td>
				<td  class="forum_count">'
				// количество тем в категории
				.$row["count"].'
				</td>
				<td class="forum_last_theme">';
				// последняя обсуждаемая тема в категории
				if ($row["last_theme"])
				{
					// название последней темы
					echo '<a href="'.BASE_PATH_HREF.$row["last_theme"]["link"].'">'.$row["last_theme"]["name"].'</a>';
					if ($row["last_theme"]["timeedit"])
					{
						// дата последней темы
						echo '<br><span class="forum_date">'.$row["last_theme"]["timeedit"].'</span>';
					}
				}
				echo '
				</td>
			</tr>';
		}
	}
}
echo '</table>';
	
if (empty($result["blocks"]) || empty($result["cats"]))
{
	echo '<div class="errors">'.$this->diafan->_('Обязательно создайте главные категории форума!').'</div>';
}

// форма поиска по темам и сообщениям
echo $this->get('form_search', 'forum', array("action" => $result["action"]));
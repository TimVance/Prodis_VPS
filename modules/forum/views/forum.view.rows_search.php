<?php
/**
 * Шаблон элементов в списке найденных сообщений
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
	if ($row["type"] == "message")
	{
		echo '<div class="js_forum_message forum_message">
			<a href="'.BASE_PATH_HREF.$row["link"].'">'.$row["theme"].'</a>
			<br>
			<span class="forum_author">'.$this->get('author', 'forum', $row["user"]).'</span>, <span class="forum_date">'.$row['created'].'</span>
			<br>
			'.$row['text'].'
		</div>';
	}
	else
	{
		echo '<div class="forum_category">
			<a href="'.BASE_PATH_HREF.$row["link"].'">'.$row["theme"].'</a>
			<br>
			<span class="forum_author">'.$this->get('author', 'forum', $row["user"]).'</span>, <span class="forum_date">'.$row['created'].'</span>
		</div>';
	}
}

//Кнопка "Показать ещё"
if(! empty($result["show_more"]))
{
	echo $result["show_more"];
}
<?php
/**
 * Шаблон блока сообщений форума
 * 
 * Шаблонный тег <insert name="show_block_messages" module="forum" [count="количество"]
 * [cat_id="категории"] [template="шаблон"]>:
 * блок последних сообщений 
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

if(! $result["rows"]) return;

foreach ($result["rows"] as $row)
{	
	echo '<div class="block forum">';
	if (is_array($row["user"]) && !empty($row["user"]["avatar"]) && !empty($row["user"]["user_page"]))
	{
		echo '<a class="block-row-img" href="'.(!empty($row["user"]["user_page"]) ? $row["user"]["user_page"] : '').'">';
			echo '<img class="avatar" src="'.$row["user"]["avatar"].'" width="'.$row["user"]["avatar_width"].'" height="'.$row["user"]["avatar_height"].'" alt="'.$row["user"]["fio"].' ('.$row["user"]["name"].')" class="avatar"> ';
		echo '</a>';
	}
	elseif($this->diafan->configmodules("avatar_none", "users"))
	{
		$avatar = BASE_PATH.USERFILES.'/avatar_none.png';
		$avatar_width = $this->diafan->configmodules("avatar_width", "users");
		$avatar_height = $this->diafan->configmodules("avatar_height", "users");
		echo '<img src="'.$avatar.'" width="'.$avatar_width.'" height="'.$avatar_height.'" alt="'.$this->get('author_message_main', 'forum', $row["user"]).'" class="avatar">';
	}
	echo '<div class="block-text">';

		echo '<p>'.$row['text'].'</p>';
		echo $this->get('get_attachments', 'forum', $row["attachments"]);

		echo '<div class="forum_author">'.$this->get('author_message_main', 'forum', $row["user"]).'</div>';

		echo '<div class="date">'.$row['created'];
		if(! empty($row["date_update"]))
		{
			echo ', '.$this->diafan->_('редакция').($row["user_update"] ? ': '.$this->get('author', 'forum', $row["user_update"]).',' : '').' '.$row["date_update"];
		}
		echo '</div>';
		
		echo '<div class="theme">'.$this->diafan->_('Тема').': <a href="'.BASE_PATH_HREF.$row["link"].'">'.$row["theme"].'</a></div>';
	echo '</div>';
	echo '</div>';
}
<?php
/**
 * Шаблон отдельной темы в категории
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

echo '<td class="forum_theme_name';
// в теме есть новые сообщения
if ($result["messages_new"])
{
	echo ' forum_news';
}
echo '">';
// тема заблокирована
if (!$result["act"])
{
	echo '<span class="forum_blocked">'.$this->diafan->_('Заблокировано').': '.$result["name"].'</span>';
}
// тема активна
else
{
	echo '<a href="'.BASE_PATH_HREF.$result["link"].'" class="forum_name">'.$result["name"].'</a>';
}
echo '
	</td>
	<td class="forum_count_message">'
	// ответов новых/всего
	.($result["messages_new"] ? '<span class="forum_active">'.$result["messages_new"].'</span>/' : '').$result["messages"].'
	</td>

	<td class="forum_author_date">';
// автор темы
if (! empty($result["user"]))
{
	echo '<span class="forum_author">'.$this->get('author', 'forum', $result["user"]).'</span><br>';
}
// дата создания, редакции темы
echo '<span class="forum_date">'.$result["created"];
if($result["date_update"])
{
	echo ', '.$this->diafan->_('редакция').($result["user_update"] ? ': '.$this->get('author', 'forum', $result["user_update"]).',' : '').' '.$result["date_update"];
}
echo '</span>
	</td>
	<td class="forum_last_user">';
// автор и дата последнего сообщения в теме
if ($result["last_user"])
{
	echo '<span class="forum_author">'.$this->get('author', 'forum', $result["last_user"]["user"]).'</span><br>
			<span class="forum_date">'.$result["last_user"]["created"].'</span>';
}
echo '
	</td>
	<td>';
if ($result["access_edit_delete"] || $result["access_block"])
{
	echo '
		<div class="forum_actions">
		<form method="POST" action="" class="ajax">
		<input type="hidden" name="action" value="">
		<input type="hidden" name="module" value="forum">
		<input type="hidden" name="check_hash_user" value="'.$result["hash"].'">
		<input type="hidden" name="id" value="'.$result["id"].'">
			<span class="js_forum_actions">';
	if ($result["access_edit_delete"])
	{
	// удалить тему
	echo '<a href="javascript:void(0)" title="'.$this->diafan->_('Вы действительно хотите удалить запись?', false).'" action="delete" class="js_forum_action">'
		.'<i class="fa fa-times-circle" title="'.$this->diafan->_('Удалить', false).'"></i>'
		.'</a>'

		// редактировать тему
		.'<a href="'.BASE_PATH_HREF.$result["link_edit"].'" class="js_forum_action">'
		.'<i class="fa fa-pencil" title="'.$this->diafan->_('Редактировать', false).'"></i>'
		.'</a>';
	}
	if ($result["access_block"])
	{
		if ($result["act"])
		{
			// блокировать тему
			echo '<a href="javascript:void(0)" action="block" class="js_forum_action">'
				.'<i class="fa fa-pause title="'.$this->diafan->_('Заблокировать', false).'"></i>'
				.'</a>';
		}
		else
		{
			// разблокировать тему
			echo '<a href="javascript:void(0)" action="unblock" class="js_forum_action">'
				.'<i class="fa fa-play" title="'.$this->diafan->_('Разблокировать', false).'"></i>'
				.'</a>';
		}
		if ($result["close"])
		{
			// открыть тему
			echo '<a href="javascript:void(0)" action="open" class="js_forum_action">'
				.'<i class="fa fa-unlock" title="'.$this->diafan->_('Открыть тему', false).'"></i>'
				.'</a>';
		}
		else
		{
			// закрыть тему
			echo '<a href="javascript:void(0)" action="close" class="js_forum_action">'
				.'<i class="fa fa-lock" title="'.$this->diafan->_('Закрыть тему', false).'"></i>'
				.'</a>';
		}

		if ($result["prior"])
		{
			// открепить тему
			echo '<a href="javascript:void(0)" action="unprior" class="js_forum_action">'
				.'<i class="fa fa-angle-double-down" title="'.$this->diafan->_('Открепить тему', false).'"></i>'
				.'</a>';
		}
		else
		{
			// закрепить тему
			echo '<a href="javascript:void(0)" action="prior" class="js_forum_action">'
				.'<i class="fa fa-angle-double-up" title="'.$this->diafan->_('Закрепить тему', false).'"></i>'
				.'</a>';
		}
	}
	echo '</span>
			</form>
		</div>';
}
echo '</td>';
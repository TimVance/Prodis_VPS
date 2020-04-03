<?php
/**
 * Шаблон сообщения
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

echo '<form method="POST" class="ajax">
<a name="'.$result["id"].'"></a>
<input type="hidden" name="action" value="">
<input type="hidden" name="module" value="forum">
<input type="hidden" name="check_hash_user" value="'.$result["hash"].'">
<input type="hidden" name="id" value="'.$result["id"].'">';
echo
'<div class="forum_actions">
<span class="js_forum_actions">
<a href="#'.$result["id"].'" class="js_forum_action"><i class="fa fa-link" title="'.$this->diafan->_('Ссылка', false).'"></i></a> ';
if ($result["access_edit_delete"])
{
	// удалить сообщение
	echo '<a href="javascript:void(0)" action="delete_message" title="'.$this->diafan->_('Вы действительно хотите удалить запись?', false).'" class="js_forum_action">'
	.'<i class="fa fa-times-circle" title="'.$this->diafan->_('Удалить', false).'"></i>'
	.'</a>';

	// редактировать сообщение
	echo ' <a href="javascript:void(0)" action="edit_message" class="js_forum_action">'
	.'<i class="fa fa-pencil" title="'.$this->diafan->_('Редактировать', false).'"></i></a>';
}
if ($result["access_block"])
{
	echo ' <a href="javascript:void(0)" action="block_message" class="js_forum_action">'
	.'<i class="fa fa-'.($result["act"] ? 'lock' : 'unlock').'" "
	title="'.(! $result["act"] ? $this->diafan->_('Разблокировать', false) : $this->diafan->_('Блокировать', false)).'"></i></a>';
}
echo '</span></div>';

if (! $result["act"])
{
	echo '<span class="forum_blocked">'.$this->diafan->_('Заблокировано')
	.($result["access_edit_delete"] ? ': '.$result["text"] : '').'</span>
	</form>';
}
else
{
	echo '<span class="forum_author">'.$this->get('author_message', 'forum', $result["user"]).'</span>, <span class="forum_date">'.$result['created'];
	if(! empty($result["date_update"]))
	{
		echo ', '.$this->diafan->_('редакция').($result["user_update"] ? ': '.$this->get('author', 'forum', $result["user_update"]).',' : '').' '.$result["date_update"];
	}
	echo '</span>
	<div class="forum_text">'.$result['text'].'</div>
	<div class="errors error" style="display:none"></div>
	'.$this->get('get_attachments', 'forum', $result["attachments"]).'
	</form>';
}
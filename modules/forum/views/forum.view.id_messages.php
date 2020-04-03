<?php
/**
 * Шаблон сообщения с формой ответа на него и списком ответов
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

echo '<div id="forum_message'.$result["id"].'">';
echo '<div class="js_forum_message forum_message forum_message'.$result["id"].'">';
echo $this->get('id_message', 'forum', $result);
echo '</div>';

if($result["form"])
{
	echo '
	<a href="javascript:void(0)" class="js_forum_message_show_form forum_message_show_form">'.$this->diafan->_('Ответить').'</a>
	<div style="display:none;" class="js_forum_message_block_form forum_message_block_form forum_message'.$result["id"].'_block_form">';
	echo $this->get('form_message', 'forum', $result["form"]);
	echo '</div>';
}
if ($result["children"])
{
	echo '<div class="forum_message_level forum_messages'.$result["id"].'_result">'.$this->get('list_messages', 'forum', $result["children"]).'</div>';
}
else
{
	echo '<div class="forum_message_level forum_messages'.$result["id"].'_result" style="display:none;"></div>';
}
echo '</div>';
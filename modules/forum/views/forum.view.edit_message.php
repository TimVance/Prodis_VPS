<?php
/**
 * Шаблон формы редактирования сообщения
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

if (! $result["access_add"])
	return;

echo '<form method="POST" action="" class="forum_message_form ajax">';
if ($result["premoderation"])
{
	echo '<p>'.$this->diafan->_('Сообщение будет активировано на сайте после проверки модератором').'</p>';
}
echo '<input type="hidden" name="module" value="forum">
<input type="hidden" name="action" value="save_message">
<input type="hidden" name="form_tag" value="'.$result["form_tag"].'">
<input type="hidden" name="check_hash_user" value="'.$result["hash"].'">
<input type="hidden" name="save_id" value="'.$result["id"].'">';

// Имя
if ($result["field_name"])
{
	echo '<div class="infofield">'.$this->diafan->_('Ваше имя').'<span style="color:red;">*</span>:</div>
	<input type="text" name="name" value="'.$result["name"].'"><br>
	<div class="errors error_name" style="display:none"></div>';
}
echo $this->get('get', 'bbcode', array("name" => "message", "tag" => "message".$result["id"], "value" => $result["text"]));

//Прикрепляемые файлы
if (! empty($result["attachments"]["access"]))
{
	echo '<div class="infofield">'.$this->diafan->_('Прикрепляемый файл').':</div>
	<div class="inpattachment"><input type="file" name="attachments[]" class="inpfiles" max="'.$result["attachments"]["max_count_attachments"].'"></div>
	<div class="inpattachment" style="display:none"><input type="file" name="hide_attachments[]" class="inpfiles" max="'.$result["attachments"]["max_count_attachments"].'"></div>
	<div class="errors error_attachments" style="display:none"></div>';
	if ($result["attachments"]["attachment_extensions"])
	{
		echo '<span class="attachment_extensions">('.$this->diafan->_('Доступные типы файлов').': '.$result["attachments"]["attachment_extensions"].')</span>';
	}
	echo $this->get('get_attachments', 'forum', $result["attachments"]);
}
echo '<br><br>

<div class="errors error" style="display:none"></div>';
echo '<input type="submit" value="'.$this->diafan->_('Отправить', false).'">';
echo '</form>';
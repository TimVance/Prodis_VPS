<?php
/**
 * Шаблон формы добавления сообщения
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

echo '<form method="POST" action="" enctype="multipart/form-data" class="forum_message_form ajax" id="forum_messages'.($result["parent_id"] ? $result["parent_id"].'_result' : '').'">';
if ($result["premoderation"])
{
	echo '<p>'.$this->diafan->_('Сообщение будет активировано на сайте после проверки модератором').'</p>';
}
echo '
<input type="hidden" name="module" value="forum">
<input type="hidden" name="action" value="upload_message">
<input type="hidden" name="form_tag" value="'.$result["form_tag"].'">
<input type="hidden" name="check_hash_user" value="'.$result["hash"].'">
<input type="hidden" name="parent_id" value="'.$result["parent_id"].'">';

// Имя
if ($result["field_name"])
{
	echo '<div class="infofield">'.$this->diafan->_('Ваше имя').'<span style="color:red;">*</span>:</div>
	<input type="text" name="name" value=""><br>
	<div class="errors error_name"'.($result["error_name"] ? '>'.$result["error_name"] : ' style="display:none">').'</div>';
}
	echo $this->get('get', 'bbcode', array("name" => "message", "tag" => "message_".$result["parent_id"], "value" => ""));

// Прикрепляемые файлы
if ($result["add_attachments"])
{
	echo '
	<div class="infofield">'.$this->diafan->_('Прикрепляемый файл').':</div>
	<div class="inpattachment"><input type="file" name="attachments[]" class="inpfiles" max="'.$result["max_count_attachments"].'"></div>
	<div class="inpattachment" style="display:none"><input type="file" name="hide_attachments[]" class="inpfiles" max="'.$result["max_count_attachments"].'"></div>';
	if ($result["attachment_extensions"])
	{
		echo '<div class="attachment_extensions">('.$this->diafan->_('Доступные типы файлов').': '.$result["attachment_extensions"].')</div>';
	}
	echo '<div class="errors error_attachments"'.(! empty($result["error_attachments"]) ? '>'.$result["error_attachments"] : ' style="display:none">').'</div>';
}

//Защитный код
echo $result["captcha"];

//Кнопка Отправить

echo '<input type="submit" value="'.$this->diafan->_('Отправить',  false).'">';

echo '<div class="errors error"'.(! empty($result["error"]) ? '>'.$result["error"] : ' style="display:none">').'</div>
</form>';
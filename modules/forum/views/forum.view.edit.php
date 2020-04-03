<?php
/**
 * Шаблон формы редактирования/добавления категории
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

if($result["premoderation"])
{
    echo '<p>'.$this->diafan->_('Тема будет активирована на сайте после проверки модератором.').'</p>';
}

echo '<form action="" method="POST" class="ajax forum_form" enctype="multipart/form-data">
<input type="hidden" name="module" value="forum">
<input type="hidden" name="action" value="'.$result["action"].'">
<input type="hidden" name="form_tag" value="'.$result["form_tag"].'">
<input type="hidden" name="id" value="'.$result["id"].'">
<input type="hidden" name="cat_id" value="'.$result["cat_id"].'">
<input type="hidden" name="check_hash_user" value="'.$result["hash"].'">

<div class="infofield">'.$this->diafan->_('Название').':</div>
<input type="text" name="name" value="'.$result["name"].'">
<div class="errors error_name"'.($result["error_name"] ? '>'.$result["error_name"] : ' style="display:none">').'</div>';

// Имя
if (! empty($result["field_user_name"]))
{
	echo '<div class="infofield">'.$this->diafan->_('Ваше имя').'<span style="color:red;">*</span>:</div>
	<input type="text" name="user_name" value=""><br>
	<div class="errors error_user_name"'.($result["error_user_name"] ? '>'.$result["error_user_name"] : ' style="display:none">').'</div>';
}
if (! empty($result["field_message"]))
{
    echo $this->get('get', 'bbcode', array("name" => "message", "tag" => "message", "value" => ""));
}

// Прикрепляемые файлы
if (! empty($result["field_attachments"]))
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

echo $result["captcha"];

echo '<input type="submit" value="'.(!$result["name"] ? $this->diafan->_('Создать', false) : $this->diafan->_('Сохранить', false)).'">
<div class="errors error"'.($result["error"] ? '>'.$result["error"] : ' style="display:none">').'</div>

</form>';
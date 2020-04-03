<?php
/**
 * Шаблон формы добавления вопроса
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

echo '<div class="faq_form">
<form method="POST" action="" enctype="multipart/form-data" class="ajax">
<input type="hidden" name="module" value="faq">
<input type="hidden" name="form_tag" value="'.$result["form_tag"].'">
<input type="hidden" name="site_id" value="'.$result["site_id"].'">
<input type="hidden" name="cat_id" value="'.$result["cat_id"].'">';

//заголовок блока
echo '<div class="block_header">'.$this->diafan->_('Задайте Ваш вопрос').'</div>';

//имя
echo '<div class="infofield">'.$this->diafan->_('Ваше имя').'<span style="color:red;">*</span>:</div>
<input type="text" name="name" value="'.$result["name"].'"><br>
<div class="errors error_name"'.($result["error_name"] ? '>'.$result["error_name"] : ' style="display:none">').'</div>';

//вопрос
echo '<div class="infofield">'.$this->diafan->_('Ваш вопрос').'<span style="color:red;">*</span>:</div>
<textarea name="question" cols="66" rows="10"></textarea><br>
<div class="errors error_question"'.($result["error_question"] ? '>'.$result["error_question"] : ' style="display:none">').'</div>';

//e-mail
echo '<div class="infofield">'.$this->diafan->_('Ваш e-mail для ответа').':</div>
<input type="email" name="email" value=""><br>
<div class="errors error_email"'.($result["error_email"] ? '>'.$result["error_email"] : ' style="display:none">').'</div>';

//прикрепляемые файлы
if ($result["attachments"])
{
	echo '<div class="infofield">'.$this->diafan->_('Прикрепляемый файл').':</div>
	<div class="inpattachment"><input type="file" name="attachments[]" class="inpfiles" max="'.$result["max_count_attachments"].'"></div>
	<div class="inpattachment" style="display:none"><input type="file" name="hide_attachments[]" class="inpfiles" max="'.$result["max_count_attachments"].'"></div>';
	if ($result["attachment_extensions"])
	{
		echo '<div class="attachment_extensions">('.$this->diafan->_('Доступные типы файлов').': '.$result["attachment_extensions"].')</div>';
	}
	echo '<div class="errors error_attachments"'.($result["error_attachments"] ? '>'.$result["error_attachments"] : ' style="display:none">').'</div><br>';
}

//защитный код
echo $result["captcha"];

//кнопка "Отправить"
echo '<input type="submit" value="'.$this->diafan->_('Отправить', false).'" class="button solid">

<div class="privacy_field">'.$this->diafan->_('Отправляя форму, я даю согласие на <a href="%s">обработку персональных данных</a>.', true, BASE_PATH_HREF.'privacy'.ROUTE_END).'</div>

<div class="required_field"><span style="color:red;">*</span> — '.$this->diafan->_('Поля, обязательные для заполнения').'</div>

</form>';
echo '<div class="errors error"'.($result["error"] ? '>'.$result["error"] : ' style="display:none">').'</div>
</div>';

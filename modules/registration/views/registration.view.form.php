<?php
/**
 * Шаблон формы регистрации
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

echo '<form action="'.$result["action"].'" method="POST" class="js_registration_form registration_form ajax" enctype="multipart/form-data">
<input type="hidden" name="module" value="registration">
<input type="hidden" name="url" value="'.$result["url"].'">
<input type="hidden" name="action" value="add">
<input type="hidden" name="tmpcode" value="'.md5(mt_rand(0, 9999)).'">

<div class="infofield">'.$this->diafan->_('ФИО или название компании').'<span style="color:red;">*</span>:</div>
<input type="text" name="fio" value="">
<div class="errors error_fio"'.($result["error_fio"] ? '>'.$result["error_fio"] : ' style="display:none">').'</div>

<div class="infofield">'.$this->diafan->_('E-mail').'<span style="color:red;">*</span>:</div>
<input type="email" name="mail" value="">
<div class="errors error_mail"'.($result["error_mail"] ? '>'.$result["error_mail"] : ' style="display:none">').'</div>

<div class="infofield">'.$this->diafan->_('Телефон').':</div>
<input type="tel" name="phone" value="">
<div class="errors error_phone"'.($result["error_phone"] ? '>'.$result["error_phone"] : ' style="display:none">').'</div>';

if($result["use_name"])
{
	echo '<div class="infofield">'.$this->diafan->_('Логин').'<span style="color:red;">*</span>:</div>
	<input type="text" name="name" value="">
	<div class="errors error_name"'.($result["error_name"] ? '>'.$result["error_name"] : ' style="display:none">').'</div>';
}
echo '<div class="infofield">'.$this->diafan->_('Пароль').'<span style="color:red;">*</span>:</div>
<input type="password" name="password">
<div class="errors error_password"'.($result["error_password"] ? '>'.$result["error_password"] : ' style="display:none">').'</div>

<div class="infofield">'.$this->diafan->_('Повторите пароль').'<span style="color:red;">*</span>:</div>
<input type="password" name="password2">
<div class="errors error_password2"'.($result["error_password2"] ? '>'.$result["error_password2"] : ' style="display:none">').'</div>';

if ($result["use_subscription"])
{
	echo '<br><input name="subscribe" id="subscribe" type="checkbox" checked><label for="subscribe">'.$this->diafan->_('Подписаться на новости').'</label>';
}

if ($result["use_avatar"])
{
	echo '<div class="infofield">'.$this->diafan->_('Аватар').':</div>
	<div class="registration_avatar">';
	if(! empty($result["avatar"]))
	{
		echo $this->get('avatar', 'registration', $result);
	}
	echo '
	</div>
	<input type="file" name="avatar" class="inpfile">
	<div class="registration_text">'.$this->diafan->_('(Файл в формате PNG, JPEG, GIF размер не меньше %spx X %spx, не больше 1Мб)', true, $result["avatar_width"], $result["avatar_height"]).'</div>
	<div class="errors error_avatar"'.($result["error_avatar"] ? '>'.$result["error_avatar"] : ' style="display:none">').'</div>';
}
if (! empty($result["roles"]))
{
	echo '<div class="infofield">'.$this->diafan->_('Тип пользователя').':</div>
		<select name="role_id" class="inpselect">';
	foreach ($result["roles"] as $row)
	{
		echo '<option value="'.$row["id"].'">'.$row["name"].'</option>';
	}

	echo '</select>';
}

if(! empty($result["languages"]))
{
	echo '<div class="infofield">'.$this->diafan->_('Язык').':</div>
		<select name="lang_id" class="inpselect">';
	foreach ($result["languages"] as $row)
	{
		echo '
			<option value="'.$row["value"].'"'.$row["selected"].'>'.$row["name"].'</option>';
	}

	echo '</select>';
}

$result_param = $result;
$result_param["name"] = "rows_param";
$result_param["prefix"] = "";
echo $this->get('show_param', 'registration', $result_param);
if(! empty($result["dop_rows_param"]))
{
	echo '<div class="registration_dop_param"><div class="block_header">'.$this->diafan->_('Дополнительные поля').'</div>';
	$result_param = $result;
	$result_param["name"] = "dop_rows_param";
	$result_param["prefix"] = "dop_";
	$result_param["param_role_rels"] = array();
	echo $this->get('show_param', 'registration', $result_param);
	echo '</div>';
}

echo $result["captcha"];

echo '<br>

<input type="submit" value="'.$this->diafan->_('Регистрация', false).'">

<div class="privacy_field">'.$this->diafan->_('Отправляя форму, я даю согласие на <a href="%s">обработку персональных данных</a>.', true, BASE_PATH_HREF.'privacy'.ROUTE_END).'</div>

<div class="errors error"'.($result["error"] ? '>'.$result["error"] : ' style="display:none">').'</div>

<div class="required_field"><span style="color:red;">*</span> — '.$this->diafan->_('Поля, обязательные для заполнения').'</div>

</form>
<div class="errors registration_message"></div>';
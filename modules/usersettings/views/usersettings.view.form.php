<?php
/**
 * Шаблон формы настроек аккаунта
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

echo '<form action="'.$result["action"].'" method="POST" class="usersettings_form ajax" enctype="multipart/form-data">

<input type="hidden" name="module" value="usersettings">
<input type="hidden" name="url" value="'.$result["url"].'">
<input type="hidden" name="action" value="edit">
<input type="hidden" name="check_hash_user" value="'.$result["hash"].'">

<div class="infofield">'.$this->diafan->_('ФИО или название компании').':</div>
<input type="text" name="fio" value="'.$result["fio"].'">
<div class="errors error_fio"'.($result["error_fio"] ? '>'.$result["error_fio"] : ' style="display:none">').'</div>

<div class="infofield">'.$this->diafan->_('E-mail').':</div>
<input type="email" name="mail" value="'.$result["mail"].'">
<div class="errors error_mail"'.($result["error_mail"] ? '>'.$result["error_mail"] : ' style="display:none">').'</div>

<div class="infofield">'.$this->diafan->_('Телефон').':</div>
<input type="tel" name="phone" value="'.$result["phone"].'">
<div class="errors error_phone"'.($result["error_phone"] ? '>'.$result["error_phone"] : ' style="display:none">').'</div>';

if($result["use_name"])
{
    echo '<div class="infofield">'.$this->diafan->_('Логин').':</div>
	<input type="text" name="name" value="'.$result["name"].'">
	<div class="errors error_name"'.($result["error_name"] ? '>'.$result["error_name"] : ' style="display:none">').'</div>';
}
echo '<div class="infofield">'.$this->diafan->_('Пароль').':</div>
	<input type="password" name="password">
	<div class="errors error_password"'.($result["error_password"] ? '>'.$result["error_password"] : ' style="display:none">').'</div>

	<div class="infofield">'.$this->diafan->_('Повторите пароль').':</div>
	<input type="password" name="password2">';

if (! empty($result['link_subscription']))
{
	echo '<div class="infofield"><a href="'.$result['link_subscription'].'" target="_blank">'.$this->diafan->_('Редактировать категории рассылки').'</a></div>';
}
if (! empty($result["use_subscription"]))
{
	echo '<input type="checkbox" name="subscribe" id="subscribe" value="1"'.($result["is_subscribe"] ? ' checked': '').'><label for="subscribe">'.$this->diafan->_('Подписаться на новости').'</label>';
}
if ($result["use_avatar"])
{
	echo '<div class="infofield">'.$this->diafan->_('Аватар').':</div>
		<div class="js_usersettings_avatar usersettings_avatar">';
	if(! empty($result["avatar"]))
	{
		echo $this->get('avatar', 'usersettings', $result);
	}
	echo '
		</div>
		<input type="file" name="avatar" class="inpfile">
		<div class="usersettings_text">'.$this->diafan->_('(Файл в формате PNG, JPEG, GIF размер не меньше %spx X %spx, не больше 1Мб)', true, $result["avatar_width"], $result["avatar_height"]).'</div>
		<div class="errors error_avatar"'.($result["error_avatar"] ? '>'.$result["error_avatar"] : ' style="display:none">').'</div>';
}
if(! empty($result["roles"]))
{
	echo '<div class="infofield">'.$this->diafan->_('Тип пользователя').':</div>
		<select name="role_id" class="inpselect">';
	foreach ($result["roles"] as $row)
	{
		echo '
			<option value="'.$row["id"].'"'.($row["id"] == $result["role_id"] ? ' selected' : '').'>'.$row["name"].'</option>';
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
echo $this->get('show_param', 'usersettings', $result_param);
if(! empty($result["dop_rows_param"]))
{
	echo '<div class="usersettings_dop_param"><div class="block_header">'.$this->diafan->_('Дополнительные поля').'</div>';
	$result_param = $result;
	$result_param["name"] = "dop_rows_param";
	$result_param["prefix"] = "dop_";
	$result_param["param_role_rels"] = array();
	echo $this->get('show_param', 'usersettings', $result_param);
	echo '</div>';
}

echo $result["captcha"];

echo '<br>

<input type="submit" value="'.$this->diafan->_('Сохранить', false).'">

<div class="errors error"'.($result["error"] ? '>'.$result["error"] : ' style="display:none">').'</div>

<div class="required_field"><span style="color:red;">*</span> — '.$this->diafan->_('Поля, обязательные для заполнения').'</div>

</form>
<div class="errors usersettings_message"></div>';
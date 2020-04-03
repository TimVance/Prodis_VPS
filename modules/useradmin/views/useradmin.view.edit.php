<?php
/**
 * Шаблон формы редактирования данных
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

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>'.$this->diafan->_('Редактирование', false).'</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="'.BASE_PATH.'modules/useradmin/useradmin.edit.css" rel="stylesheet" type="text/css">
</head>
<body>
<form method="POST" action="'.BASE_PATH.ADMIN_FOLDER.'/" class="useradmin_form">
<input type="hidden" name="module" value="useradmin">
<input type="hidden" name="action" value="save">
<input type="hidden" name="ajax" value="1">
<input type="hidden" name="name" value="'.$result["name"].'">
<input type="hidden" name="element_id" value="'.$result["element_id"].'">
<input type="hidden" name="module_name" value="'.$result["module_name"].'">
<input type="hidden" name="lang_module_name" value="'.$result["lang_module_name"].'">
<input type="hidden" name="check_hash_user" value="'.$result["hash"].'">
<input type="hidden" name="lang_id" value="'.$result["lang_id"].'">
'.($result["is_lang"] ? '<input type="hidden" name="is_lang" value="true">' : '').'
<input type="hidden" name="type" value="'.$result["type_save"].'">';
switch($result["type"])
{
	case 'textarea':
		echo '<textarea name="value" style="width:100%; height:310px">'.$result["text"].'</textarea><br>';
		break;

	case 'editor':
		echo '<input type="checkbox" name="typograf" id="input_typograf" value="1">
		<label for="input_typograf">'.$this->diafan->_('Типограф', false).'</label><br>
		<textarea name="value"'.($this->diafan->_users->htmleditor ? ' class="htmleditor"' : '').' style="width:100%; height:300px">'.$result["text"].'</textarea>
		<br>';
		break;

	case 'datetime':
	case 'date':
		if (! $result["text"])
		{
			$result["text"] = time();
		}
		echo '<input name="value" type="text" value="'.date("d.m.Y H:i", $result["text"]).'" size="20" class="timecalendar" showTime="'.($result["type"] == 'datetime'? 'true' : 'false').'">';
		break;

	case 'text':
		echo '<input name="value" type="text" value="'.$result["text"].'" size="60">';
		break;

	case 'email':
		echo '<input name="value" type="email" value="'.$result["text"].'" size="60">';
		break;

	case 'numtext':
	case 'floattext':
		echo '<input name="value" type="number" value="'.$result["text"].'" size="20">';
		break;
}
if ($result["error"])
{
	echo '<div class="errors">'.$result["error"].'</div>';
}
echo '
<input style="margin-top:5px;" type="submit" value="'.$this->diafan->_('Сохранить', false).'" class="useradmin_button">
</form>';

if(! defined('SOURCE_JS'))
{
	define('SOURCE_JS', 1);
}
switch (SOURCE_JS)
{
	// Yandex CDN
	case 2:
		echo '
		<!--[if lt IE 9]><script src="//yandex.st/jquery/1.10.2/jquery.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//yandex.st/jquery/2.0.3/jquery.min.js" charset="UTF-8"></script><!--<![endif]-->
		<script type="text/javascript" src="//yandex.st/jquery/form/3.14/jquery.form.min.js" charset="UTF-8"></script>';
		break;

	// Microsoft CDN
	case 3:
		echo '
		<!--[if lt IE 9]><script src="//ajax.aspnetcdn.com/ajax/jquery/jquery-1.10.2.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//ajax.aspnetcdn.com/ajax/jquery/jquery-2.0.3.min.js" charset="UTF-8"></script><!--<![endif]-->
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.form.min.js').'" charset="UTF-8"></script>';
		break;

	// CDNJS CDN
	case 4:
		echo '
		<!--[if lt IE 9]><script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js" charset="UTF-8"></script><!--<![endif]-->
		<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.min.js" charset="UTF-8"></script>';
		break;

	// jQuery CDN
	case 5:
		echo '
		<!--[if lt IE 9]><script src="//code.jquery.com/jquery-1.10.2.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//code.jquery.com/jquery-2.0.3.min.js" charset="UTF-8"></script><!--<![endif]-->
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.form.min.js').'" charset="UTF-8"></script>';
		break;

	// Hosting
	case 6:
		echo '
		<!--[if lt IE 9]><script src="'.BASE_PATH.Custom::path('js/jquery-1.10.2.min.js').'"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery-2.0.3.min.js').'" charset="UTF-8"></script><!--<![endif]-->
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.form.min.js').'" charset="UTF-8"></script>';
		break;

	// Google CDN
	case 1:
	default:
		echo '
		<!--[if lt IE 9]><script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js" charset="UTF-8"></script><!--<![endif]-->
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.form.min.js').'" charset="UTF-8"></script>';
		break;
}

echo '
<script type="text/javascript" src="'.BASE_PATH.'adm/htmleditor/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
var base_path = "'.BASE_PATH.'";
</script>';

$lang = $this->diafan->_languages->base_admin();
if(! file_exists(ABSOLUTE_PATH.'adm/htmleditor/tinymce/langs/'.$lang.'.js'))
{
	$lang = '';
}
echo '
<script type="text/javascript">
var config_language = "'.$lang.'";
</script>
<script type="text/javascript" src="'.BASE_PATH.'adm/htmleditor/tinymce/config.js"></script>
<script type="text/javascript" src="'.BASE_PATH.'modules/useradmin/js/useradmin.edit.js"></script>
</body>
</html>';
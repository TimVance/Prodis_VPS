<?php
/**
 * Шаблон формы добавления объявления
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

$required = false;

echo '<div class="js_ab_form ab_form">';
echo '<h2>'.$this->diafan->_('Подать объявление').'</h2>';

echo '<form method="POST" enctype="multipart/form-data" action="" class="ajax">
<input type="hidden" name="module" value="ab">
<input type="hidden" name="action" value="add">
<input type="hidden" name="form_tag" value="'.$result["form_tag"].'">
<input type="hidden" name="tmpcode" value="'.md5(mt_rand(0, 9999)).'">';

if (! empty($result["form_name"]))
{
	echo '<div class="infofield">'.$this->diafan->_('Название объявления').'<span style="color:red;">*</span>:</div>
	<input type="text" name="name" value="">
	<div class="errors error_name"'.($result["error_name"] ? '>'.$result["error_name"] : ' style="display:none">').'</div>';

	$required = true;
}

if (! empty($result["form_date_finish"]))
{
	echo '<div class="infofield">'.$this->diafan->_('Опубликовать на сайте до').':</div>
	<input type="text" name="date_finish" value="" class="timecalendar" showTime="false">
	<div class="errors error_date_finish"'.($result["error_date_finish"] ? '>'.$result["error_date_finish"] : ' style="display:none">').'</div>';
}

if (! empty($result["form_anons"]))
{
	echo '<div class="infofield">'.$this->diafan->_('Краткий анонс').'<span style="color:red;">*</span>:</div>
	<textarea name="anons"></textarea>
	<div class="errors error_anons"'.($result["error_anons"] ? '>'.$result["error_anons"] : ' style="display:none">').'</div>';

	$required = true;
}

if (! empty($result["form_text"]))
{
	echo '<div class="infofield">'.$this->diafan->_('Описание объявления').'<span style="color:red;">*</span>:</div>
	<textarea name="text"></textarea>
	<div class="errors error_text"'.($result["error_text"] ? '>'.$result["error_text"] : ' style="display:none">').'</div>';

	$required = true;
}

if (! empty($result["form_images"]))
{
	echo '<div class="infofield">'.$this->diafan->_('Изображения').':</div>';
	echo '<div class="images"></div>';
	echo '<input type="file" name="images" class="inpimages">
	<div class="errors error_images"'.($result["error_images"] ? '>'.$result["error_images"] : ' style="display:none">').'</div>';
}


if (count($result["site_ids"]) > 1)
{
	echo '<div class="ab_form_site_ids">
	<span class="infofield">'.$this->diafan->_('Раздел').':</span>
	<select name="site_id" class="js_ab_form_site_ids">';
	foreach ($result["site_ids"] as $row)
	{
		echo '<option value="'.$row["id"].'" path="'.BASE_PATH_HREF.$row["path"].'"';
		if($result["site_id"] == $row["id"])
		{
			echo ' selected';
		}
		echo '>'.$row["name"].'</option>';
	}
	echo '</select>';
	echo '</div>';
}
else
{
	echo '<input name="site_id" type="hidden" value="'.$result["site_id"].'">';
}

if (count($result["cat_ids"]) > 1)
{
	echo '<div class="ab_form_cat_ids">
	<span class="infofield">'.$this->diafan->_('Категория').':</span>
	<select name="cat_id" class="js_ab_form_cat_ids">';
	foreach ($result["cat_ids"] as $row)
	{
		echo '<option value="'.$row["id"].'" site_id="'.$row["site_id"].'"';
		if($result["cat_id"] == $row["id"])
		{
			echo ' selected';
		}
		echo '>';
		if($row["level"])
		{
			echo str_repeat('- ', $row["level"]);
		}
		echo $row["name"].'</option>';
	}
	echo '</select>';
	echo '</div>';
}
else
{
	echo '<input name="cat_id" type="hidden" value="'.$result["cat_id"].'">';
}

if(! empty($result["rows"]))
{
	foreach ($result["rows"] as $row) //вывод полей из конструктора форм
	{
		if($row["required"])
		{
			$required = true;
		}
		echo '<div class="js_ab_form_param ab_form_param ab_form_param'.$row["id"].'" cat_ids="'.$row["cat_ids"].'">';

		switch ($row["type"])
		{
			case 'title':
				echo '<div class="infoform">'.$row["name"].':</div>';
				break;

			case 'text':
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
				<input type="text" name="p'.$row["id"].'" value="">';
				break;

			case "email":
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
				<input type="email" name="p'.$row["id"].'" value="">';
				break;

			case "phone":
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
				<input type="tel" name="p'.$row["id"].'" value="">';
				break;

			case 'textarea':
			case 'editor':
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
				<textarea name="p'.$row["id"].'"></textarea>';
				break;

			case 'date':
			case 'datetime':
				$timecalendar  = true;
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
					<input type="text" name="p'.$row["id"].'" value="" class="timecalendar" showTime="'
					.($row["type"] == 'datetime'? 'true' : 'false').'">';
				break;

			case 'numtext':
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
				<input type="number" name="p'.$row["id"].'" value="">';
				break;

			case 'checkbox':
				echo '<input name="p'.$row["id"].'" id="ads_p'.$row["id"].'" value="1" type="checkbox"><label for="ads_p'.$row["id"].'">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').'</label>';
				break;

			case 'select':
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
				<select name="p'.$row["id"].'" class="inpselect">
					<option value="">-</option>';
				foreach ($row["select_array"] as $select)
				{
					echo '<option value="'.$select["id"].'">'.$select["name"].'</option>';
				}
				echo '</select>';
				break;

			case 'multiple':
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>';
				foreach ($row["select_array"] as $select)
				{
					echo '<br><input name="p'.$row["id"].'[]" id="ads_p'.$row["id"].'_'.$select["id"].'" value="'.$select["id"].'" type="checkbox" class="inpcheckbox"> <label for="ads_p'.$row["id"].'_'.$select["id"].'">'.$select["name"].'</label>';
				}
				break;

			case "attachments":
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>';
				echo '<div class="inpattachment"><input type="file" name="attachments'.$row["id"].'[]" class="inpfiles" max="'.$row["max_count_attachments"].'"></div>';
				echo '<div class="inpattachment" style="display:none"><input type="file" name="hide_attachments'.$row["id"].'[]" class="inpfiles" max="'.$row["max_count_attachments"].'"></div>';
				if ($row["attachment_extensions"])
				{
					echo '<div class="attachment_extensions">('.$this->diafan->_('Доступные типы файлов').': '.$row["attachment_extensions"].')</div>';
				}
				break;

			case "images":
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
				<div class="images"></div>';
				echo '<input type="file" name="images'.$row["id"].'" class="inpimages" param_id="'.$row["id"].'">';
				break;
		}

		if($row["text"])
		{
			echo '<div class="ab_form_param_text">'.$row["text"].'</div>';
		}

		if($row["type"] != 'title')
		{
			echo '<div class="errors error_p'.$row["id"].'"'.($result["error_p".$row["id"]] ? '>'.$result["error_p".$row["id"]] : ' style="display:none">').'</div>';
		}
		echo '</div>';
	}
}
// добавление точки на карте
echo $this->diafan->_geomap->add(0, 'ab');

//Защитный код
echo $result["captcha"];

//Кнопка Отправить
echo '<input type="submit" value="'.$this->diafan->_('Отправить', false).'">';

echo '<div class="privacy_field">'.$this->diafan->_('Отправляя форму, я даю согласие на <a href="%s">обработку персональных данных</a>.', true, BASE_PATH_HREF.'privacy'.ROUTE_END).'</div>';

if($required)
{
	echo '<div class="required_field"><span style="color:red;">*</span> — '.$this->diafan->_('Поля, обязательные для заполнения').'</div>';
}

echo '</form>';
echo '<div class="errors error"'.($result["error"] ? '>'.$result["error"] : ' style="display:none">').'</div>';
echo '</div>';
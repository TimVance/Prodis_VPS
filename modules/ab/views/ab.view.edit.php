<?php
/**
 * Шаблон формы редактирования объявления
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
echo '<div class="js_ab_form ab_form">';

echo '<form method="POST" enctype="multipart/form-data" action="" class="ajax">
<input type="hidden" name="module" value="ab">
<input type="hidden" name="action" value="edit">
<input type="hidden" name="form_tag" value="'.$result["form_tag"].'">
<input type="hidden" name="id" value="'.$result["id"].'">
<input type="hidden" name="check_hash_user" value="'.$result["hash"].'">';

if (! empty($result["form_name"]))
{
	echo '<div class="infofield">'.$this->diafan->_('Название объявления').'<span style="color:red;">*</span>:</div>
	<input type="text" name="name" value="'.$result["name"].'">
	<div class="errors error_name"'.($result["error_name"] ? '>'.$result["error_name"] : ' style="display:none">').'</div>';
}

if (! empty($result["form_date_finish"]))
{
	echo '<div class="infofield">'.$this->diafan->_('Опубликовать на сайте до').':</div>
	<input type="text" name="date_finish" value="'.($result["date_finish"] ? date("d.m.Y", $result["date_finish"]) : '').'" class="timecalendar" showTime="false">
	<div class="errors error_date_finish"'.($result["error_date_finish"] ? '>'.$result["error_date_finish"] : ' style="display:none">').'</div>';
}

if (! empty($result["form_anons"]))
{
	echo '<div class="infofield">'.$this->diafan->_('Краткий анонс').'<span style="color:red;">*</span>:</div>
	<textarea name="anons">'.$result["anons"].'</textarea>
	<div class="errors error_anons"'.($result["error_anons"] ? '>'.$result["error_anons"] : ' style="display:none">').'</div>';
}

if (! empty($result["form_text"]))
{
	echo '<div class="infofield">'.$this->diafan->_('Описание объявления').'<span style="color:red;">*</span>:</div>
	<textarea name="text">'.$result["text"].'</textarea>
	<div class="errors error_text"'.($result["error_text"] ? '>'.$result["error_text"] : ' style="display:none">').'</div>';
}

if (! empty($result["form_images"]))
{
	echo '<div class="infofield">'.$this->diafan->_('Изображения').':</div>
	<div class="images">';
	if(! empty($result['images'][0]))
	{
		echo $this->get('images', 'ab', $result["images"][0]);
	}
	echo '</div><input type="file" name="images" class="inpimages">
	<div class="errors error_images"'.($result["error_images"] ? '>'.$result["error_images"] : ' style="display:none">').'</div>';
}

if (count($result["cat_ids"]) > 1)
{
	echo '<div class="ab_form_cat_ids">
	<span class="infofield">'.$this->diafan->_('Категория').':</span>
	<select name="cat_id" class="js_ab_form_cat_ids">';
	foreach ($result["cat_ids"] as $row)
	{
		echo '<option value="'.$row["id"].'"';
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
	foreach($result["rows"] as $row) //вывод полей из конструктора форм
	{
		$value = ! empty($result["param"]['p'.$row["id"]]) ? $result["param"]['p'.$row["id"]] : '';

		echo '<div class="js_ab_form_param ab_form_param ab_form_param'.$row["id"].'" cat_ids="'.$row["cat_ids"].'">';

		switch ($row["type"])
		{
			case 'title':
				echo '<div class="infoform">'.$row["name"].':</div>';
				break;

			case 'text':
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
				<input type="text" name="p'.$row["id"].'" value="'.$value.'">';
				break;

			case "email":
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
				<input type="email" name="p'.$row["id"].'" value="'.$value.'">';
				break;

			case "phone":
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
				<input type="tel" name="p'.$row["id"].'" value="'.$value.'">';
				break;

			case 'textarea':
			case 'editor':
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
				<textarea name="p'.$row["id"].'">'.$value.'</textarea>';
				break;

			case 'date':
			case 'datetime':
				$timecalendar  = true;
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
					<input type="text" name="p'.$row["id"].'" value="'.$value.'" class="timecalendar" showTime="'
					.($row["type"] == 'datetime'? 'true' : 'false').'">';
				break;

			case 'numtext':
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
				<input type="number" name="p'.$row["id"].'" value="'.$value.'">';
				break;

			case 'checkbox':
				echo '<input name="p'.$row["id"].'" id="ads_p'.$row["id"].'" value="1" type="checkbox" '.($value ? ' checked' : '').'><label for="ads_p'.$row["id"].'">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').'</label>';
				break;

			case 'select':
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
				<select name="p'.$row["id"].'" class="inpselect">
					<option value="">-</option>';
				foreach ($row["select_array"] as $select)
				{
					echo '<option value="'.$select["id"].'"'.($value == $select["id"] ? ' selected' : '').'>'.$select["name"].'</option>';
				}
				echo '</select>';
				break;

			case 'multiple':
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>';
				foreach ($row["select_array"] as $select)
				{
					echo '<br><input name="p'.$row["id"].'[]" value="'.$select["id"].'" type="checkbox" class="inpcheckbox"'.(in_array($select["id"], $value) ? ' checked' : '').'> '.$select["name"];
				}
				break;

			case "attachments":
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>';
				if(! empty($result['attachments'][$row["id"]]))
				{
					echo $this->get('attachments', 'ab', array("rows" => $result['attachments'][$row["id"]], "param_id" => $row["id"], "use_animation" => $row["use_animation"]));
				}
				if(empty($result['attachments'][$row["id"]]) || count($result['attachments'][$row["id"]]) < $row["max_count_attachments"])
				{
					echo '<div class="inpattachment"><input type="file" name="attachments'.$row["id"].'[]" class="inpfiles" max="'.$row["max_count_attachments"].'"></div>';
				}

				echo '<div class="inpattachment" style="display:none"><input type="file" name="hide_attachments'.$row["id"].'[]" class="inpfiles" max="'.$row["max_count_attachments"].'"></div>';
				if ($row["attachment_extensions"])
				{
					echo '<div class="attachment_extensions">('.$this->diafan->_('Доступные типы файлов').': '.$row["attachment_extensions"].')</div>';
				}

				break;

			case "images":
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
				<div class="images">';
				if(! empty($result['images'][$row["id"]]))
				{
					echo $this->get('images', 'ab', $result['images'][$row["id"]]);
				}
				echo '
				</div>
				<input type="file" name="images'.$row["id"].'" class="inpimages" param_id="'.$row["id"].'">';
				break;
		}

		echo '<div class="ab_form_param_text">'.$row["text"].'</div>';

		if($row["type"] != 'title')
		{
			echo '<div class="errors error_p'.$row["id"].'"'.($result["error_p".$row["id"]] ? '>'.$result["error_p".$row["id"]] : ' style="display:none">').'</div>';
		}
		echo '</div>';
	}
}
// редактирование точки на карте
echo $this->diafan->_geomap->add($result["id"], 'ab');

//Кнопка Отправить
echo '<input type="submit" value="'.$this->diafan->_('Сохранить', false).'">

<div class="required_field"><span style="color:red;">*</span> — '.$this->diafan->_('Поля, обязательные для заполнения').'</div>

<div class="errors error"'.($result["error"] ? '>'.$result["error"] : ' style="display:none">').'</div>

</form>';
echo '</div>';
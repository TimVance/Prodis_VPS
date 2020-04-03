<?php
/**
 * Шаблон форма оформления заказа в один клик
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

echo '<div class="js_cart_one_click cart_one_click">';
echo '
<form method="POST" action="" class="js_cart_one_click_form cart_one_click_form ajax tracker-no-select" enctype="multipart/form-data">
<input type="hidden" name="module" value="cart">
<input type="hidden" name="action" value="one_click">
<input type="hidden" name="form_tag" value="'.$result["form_tag"].'">
<input type="hidden" name="good_id" value="'.$result["good_id"].'">
<input type="hidden" name="tmpcode" value="'.md5(mt_rand(0, 9999)).'">';
if (! empty($result["rows_param"]))
{
    $vvoz_hide = array(2,6,7,8,9,10,11,13,25,27);
    $agree_hide = array(20,21);

    $peremesh = array(22,26,29);
    $zamena = array(23,27);
    $vvoz = array(27);
    $vyvoz = array();
    $novoform = array(3);
    $other = array(3,22,23);

	foreach ($result["rows_param"] as $row)
	{
	    // Для сканов паспортов убираем обязательные поля
	    if($row["id"] == 16) $row["required"] = '';

        echo '<div class="form-group form-group'.$row["id"]
            .(!empty($row["required"] || $row["id"] == $result["required"]) ? ' required' : '')
            .(in_array($row["id"], $vvoz_hide) ? ' vvoz-hide' : '')
            .(in_array($row["id"], $agree_hide) ? ' agree-hide' : '')
            .(in_array($row["id"], $peremesh) ? ' peremesh' : '')
            .(in_array($row["id"], $zamena) ? ' zamena' : '')
            .(in_array($row["id"], $vvoz) ? ' vvoz' : '')
            .(in_array($row["id"], $vyvoz) ? ' vyvoz' : '')
            .(in_array($row["id"], $novoform) ? ' novoform' : '')
            .(in_array($row["id"], $other) ? ' other' : '')
            .'">';
		$value = ! empty($result["user"]['p'.$row["id"]]) ? $result["user"]['p'.$row["id"]] : '';

		echo '<div class="order_form_param'.$row["id"].'">';

		if ($row["id"] == 27) {
		    $style = 'style="display:inline-block;width:20%;margin-right:5px;margin-bottom: 10px;"';
		    echo '
                <div class="gabarity">
                    <div '.$style.'>
                        <div class="infofield">Габариты:</div>
                        <input class="form-control form-control27 gabarits" type="text" placeholder="Д х Ш х В ">
                    </div>
                    <div '.$style.'>
                        <div class="infofield">Вес:</div>
                        <input class="form-control form-control27 ves" type="text" placeholder="кг">
                    </div>
                    <div '.$style.'>
                        <div class="infofield">Мощность:</div>
                        <input class="form-control form-control27 moshnost" type="text" placeholder="Вт">
                    </div>
                    <div class="add-fields">+</div>
                </div>
		    ';
        }

		switch ($row["type"])
		{
			case 'title':
				echo '<div class="infoform">'.$row["name"].':</div>';
				break;

			case 'text':
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
				<input '.($row["required"] ? 'required' : '').' placeholder="'.strip_tags($row["text"]).'" class="form-control" type="text" name="p'.$row["id"].'" value="'.$value.'">';
				break;

			case "phone":
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
				<input placeholder="'.strip_tags($row["text"]).'" class="form-control" type="tel" name="p'.$row["id"].'" value="'.$value.'">';
				break;

			case "email":
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
				<input class="form-control" type="email" name="p'.$row["id"].'" value="'.$value.'">';
				break;

			case 'textarea':
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
				<textarea placeholder="'.strip_tags($row["text"]).'" class="form-control" name="p'.$row["id"].'" rows="3">'.$value.'</textarea>';
				break;

			case 'date':
			case 'datetime':
				$timecalendar  = true;
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
					<input type="text" name="p'.$row["id"].'" value="'.$this->diafan->formate_from_date($value).'" class="form-control" showTime="'
					.($row["type"] == 'datetime'? 'true' : 'false').'">';
				break;

			case 'numtext':
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
				<input placeholder="'.strip_tags($row["text"]).'" class="form-control" type="number" name="p'.$row["id"].'" value="'.$value.'">';
				break;

			case 'checkbox':
				echo '<input name="p'.$row["id"].'" id="cart_'.$result["good_id"].'_p'.$row["id"].'" value="1" type="checkbox" '.($value ? ' checked' : '').'><label for="cart_'.$result["good_id"].'_p'.$row["id"].'">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').'</label>';
				break;

			case 'select':
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div>
				<select name="p'.$row["id"].'" class="inpselect form-control">
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
					echo '<input name="p'.$row["id"].'[]" id="cart_'.$result["good_id"].'_p'.$select["id"].'[]" value="'.$select["id"].'" type="checkbox" '.(is_array($value) && in_array($select["id"], $value) ? ' checked' : '').'><label for="cart_'.$result["good_id"].'_p'.$select["id"].'[]">'.$select["name"].'</label><br>';
				}
				break;

			case "attachments":
				echo '<div class="infofield">'.$row["name"].($row["required"] || $row["id"] == $result["required"] ? '<span style="color:red;">*</span>' : '').':</div>';
				echo '<label class="dropzone inpattachment">
                        <input multiple type="file" name="attachments'.$row["id"].'[]" class="inpfiles" max="'.$row["max_count_attachments"].'">
                        <div class="drop-information">
                            Перетащите файлы сюда или кликните для выбора
                            <div class="count-drop-files"></div>
                        </div>
                      </label>';
				//echo '<div class="inpattachment" style="display:none"><input type="file" name="hide_attachments'.$row["id"].'[]" class="inpfiles" max="'.$row["max_count_attachments"].'"></div>';
				if ($row["attachment_extensions"])
				{
					echo '<div class="attachment_extensions">('.$this->diafan->_('Доступные типы файлов').': '.$row["attachment_extensions"].')</div>';
				}
				break;

			case "images":
				echo '<div class="infofield">'.$row["name"].($row["required"] ? '<span style="color:red;">*</span>' : '').':</div><div class="images"></div>';
				echo '<input type="file" name="images'.$row["id"].'" param_id="'.$row["id"].'" class="inpimages">';
				break;
		}

		echo '
		</div>
		<div class="errors error_p'.$row["id"].'"'.($result["error_p".$row["id"]] ? '>'.$result["error_p".$row["id"]] : ' style="display:none">').'</div>';
	    echo '</div>';
	}
	if(! empty($result["subscribe_in_order"]))
	{
		echo '<input type="hidden" name="subscribe_in_order" value="1">';
	}
}
echo '
<input class="btn" type="button" value="'.$this->diafan->_('Отправить', false).'">';
echo '<div class="errors error"'.($result["error"] ? '>'.$result["error"] : ' style="display:none">').'</div>';

//echo '<div class="privacy_field">'.$this->diafan->_('Отправляя форму, я даю согласие на <a href="%s">обработку персональных данных</a>.', true, BASE_PATH_HREF.'privacy'.ROUTE_END).'</div>';

echo '</form>';
echo '</div>';

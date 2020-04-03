<?php
/**
 * Шаблон вывода изображений в административной части
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

/**
 * Images_admin_view
 */
class Images_admin_view extends Diafan
{
	/**
	 * Выводит изображения, прикрепленные к элементу модуля
	 *
	 * @param integer $element_id номер элемента
	 * @param integer $param_id номер параметра для конструктора
	 * @return string
	 */
	public function show($element_id, $param_id = 0)
	{
		$text = '<div class="images_inform">'.$this->diafan->_('Главное фото').'</div>';
		$k = 1;
		$tmpcode = (! empty($_REQUEST["tmpcode"]) ? $_REQUEST["tmpcode"] : '');

		$element_type = $this->diafan->element_type();
		if($element_type == 'order')
		{
			$element_type = 'element';
		}
		if($param_id)
		{
			$module_variations = unserialize(DB::query_result("SELECT config FROM {%s} WHERE id=%d", $this->diafan->table.'_param', $param_id));
			$module_name = $this->diafan->table;
		}
		else
		{
			$module_variations = unserialize($this->diafan->configmodules('images_variations_'.$this->diafan->element_type()));
			$module_name = $this->diafan->_admin->module;
		}
		$variation_folder = DB::query_result("SELECT folder FROM {images_variations} WHERE id=%d LIMIT 1", $module_variations[0]['id']);
		
		$rows = DB::query_fetch_all("SELECT id, name, [alt], [title], folder_num, module_name FROM {images}"
			." WHERE module_name='%s' AND element_type='%s' AND element_id=%d AND param_id=%d"
			." AND tmpcode='%s' ORDER BY sort ASC",
			$module_name, $element_type,
			$element_id, $param_id, $tmpcode
		);
		$count = count($rows);
		foreach ($rows as $row)
		{
			if (! file_exists(ABSOLUTE_PATH.USERFILES."/small/".($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"]))
			{
				DB::query("DELETE FROM {images} WHERE id=%d", $row["id"]);
				continue;
			}

			$text .= '
			<div class="images_actions'.($k == 1 ? ' first' :'').'" element_id="'.$element_id.'" image_id="'.$row["id"].'">
				<input type="checkbox" name="image_check">'
			       .(! empty($variation_folder) ? '<a href="'.BASE_PATH.USERFILES.'/'.$module_name.'/'.$variation_folder.'/'.($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"].'" class="image" data-fancybox="galleryimage">' : '')
				   .'<img src="'.BASE_PATH.USERFILES."/small/".($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"].'?'.rand(0, 9999).'">'
				   .(! empty($variation_folder) ? '</a>' : '')
				.'
				<div class="images_button">
					<a href="javascript:void(0)" confirm="'.$this->diafan->_('Вы действительно хотите удалить изображение?').'" action="delete" title="'.$this->diafan->_('Удалить').'"><i class="fa fa-close"></i></a>
					<a href="javascript:void(0)" action="edit" title="'.$this->diafan->_('Редактировать атрибуты ALT и TITLE').'"><i class="fa fa-pencil"></i></a>
					'.
					($row["alt"] ? '<span title="ALT: '.$row["alt"].'">A</span>' : '').
					($row["title"] ? '<span title="TITLE: '.$row["title"].'">T</span>' : '').
					($k != 1 ? '
					<a href="javascript:void(0)" action="up" title="'.$this->diafan->_('Выше').'"><i class="fa fa-caret-left"></i></a>' : '<!--<span action="up"></span>-->').'
					'.($k != $count ? '
					<a href="javascript:void(0)" action="down" title="'.$this->diafan->_('Ниже').'"><i class="fa fa-caret-right"></i></a> ' : '<!--<span action="down"></span>-->').'
				</div>
			</div>';
			$k++;
		}
		return $text;
	}

	/**
	 * Выводит изображение для выделения области
	 *
	 * @return string
	 */
	public function selectarea($result)
	{
		$text = '
		<div class="ipopup__heading">'.$this->diafan->_('Выделите область').'</div>
		<input type="hidden" name="x1" value="">
		<input type="hidden" name="y1" value="">
		<input type="hidden" name="x2" value="">
		<input type="hidden" name="y2" value="">
		<input type="hidden" name="image_id" value="'.$result["id"].'">
		<input type="hidden" name="variation_id" value="'.$result["variant_id"].'">
		<p style="max-width: 100%; overflow: auto;"><img src="'.$result["path"].'" class="images_selectarea" select_width="'.$result["width"].'" select_height="'.$result["height"].'" style="max-width: initial;"></p>
		<input type="button" class="button images_selectarea_button" value="'.$this->diafan->_('Сохранить').'">';
		return $text;
	}

	/**
	 * Форма редактирования атрибутов alt и title для изображения
	 *
	 * @param array $row данные о изображении
	 * @return string
	 */
	public function edit_attribute($row)
	{
		$text = '
		<div>
		<div class="ipopup__close"><i class="fa fa-close"></i></div>
		<div class="ipopup__heading">'.$this->diafan->_('Редактирование').'</div>
		<div class="infofield">'.$this->diafan->_('Атрибут alt').':</div>
		<input name="alt"  type="text" value="'.$row["alt"].'">
		<br>
		<div class="infofield">'.$this->diafan->_('Атрибут title').':</div>
		<input name="title" type="text" value="'.$row["title"].'">
		<br>
		<br>
		<span class="btn btn_blue btn_small ajax_save_image" image_id="'.$row["id"].'">'.$this->diafan->_('Сохранить').'</span>
		</div>';
		return $text;
	}
}
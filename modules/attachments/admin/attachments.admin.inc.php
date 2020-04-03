<?php
/**
 * Подключение модуля к административной части других модулей
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
 * Attachments_admin_inc
 */
class Attachments_admin_inc extends Diafan
{
	/**
	 * Редактирование поля "Прикрепленные файлы"
	 * 
	 * @return void
	 */
	public function edit()
	{
		$site_id = $this->diafan->values("site_id");

		$name = $this->diafan->variable_name();
		$help = 'attachments';
		$config = array(
			"use_animation" => $this->diafan->configmodules("use_animation", $this->diafan->_admin->module, $site_id),
			"max_count_attachments" => $this->diafan->configmodules("max_count_attachments", $this->diafan->_admin->module, $site_id),
			"attachment_extensions" => $this->diafan->configmodules("attachment_extensions", $this->diafan->_admin->module, $site_id),
		);
		$this->edit_view('', $name, $help, $config);
	}

	/**
	 * Редактирование поля с типом "Файлы" из конструктора
	 *
	 * @param integer $id номер поля конструктора
	 * @param string $name имя поля конструктора
	 * @param string $help описание поля конструктора
	 * @param string $config конфигурация поля
	 * @param string $attr атрибуты строки
	 * @param string $class CSS-класс
	 * @return void
	 */
	public function edit_param($id, $name, $help, $config, $attr = '', $class = '')
	{
		$this->diafan->_admin->js_view[] = 'modules/attachments/admin/js/attachments.admin.inc.js';

		$config = unserialize($config);
		$this->edit_view($id, $name, $help, $config, $attr, $class);
	}

	/**
	 * Шаблон редактирования файлов
	 * 
	 * @param integer $id номер поля конструктора
	 * @param string $name имя поля
	 * @param string $help описание поля
	 * @param array $config конфигурация
	 * @param string $attr атрибуты строки
	 * @param string $class CSS-класс
	 * @return void
	 */
	private function edit_view($id, $name, $help, $config, $attr = '', $class = '')
	{
		echo '<div class="unit attachments'.($class ? ' '.$class : '').'" id="attachments'.$id.'" '.$attr.'>
			<div class="infofield">'.$name.$this->diafan->help($help).'</div>';
			if(! $this->diafan->is_new)
			{
				$anim_link = '';
				$anim = '';
				if(! empty($config["use_animation"]))
				{
					$anim_link = ' data-fancybox="attachments_link"';
					$anim = ' data-fancybox="attachments"';
				}
				$attachments = $this->diafan->_attachments->get($this->diafan->id, $this->diafan->table, ($id ? $id : 0));
				foreach ($attachments as $row)
				{
					echo '<div class="attachment">
					<input type="hidden" name="hide_attachment_delete[]" value="'.$row["id"].'">';
					if ($row["is_image"])
					{
						echo '<a href="'.$row["link"].'"'.$anim.'><img src="'.$row["link_preview"].'"></a> ';
						echo '<a href="'.$row["link"].'"'.$anim_link.'>'.$row["name"].'</a>';
					}
					else
					{
						echo '<a href="'.$row["link"].'">'.$row["name"].'</a>
						';
					}
					echo '<a href="javascript:void(0)" class="attachment_delete" confirm="'.$this->diafan->_('Вы действительно хотите удалить файл?').'"><img src="'.BASE_PATH.'adm/img/delete.png" width="13" height="13" alt="'.$this->diafan->_('Удалить').'"></a></div>';
				}
			}
			echo '
			<div class="attachment_files">
					<input type="file" name="attachments'.$id.'[]" max="'.$config["max_count_attachments"].'" class="inpfiles">
			</div>';
			if ($config["attachment_extensions"])
			{
				echo '<div class="attachment_extensions">('.$this->diafan->_('Доступные типы файлов').': '.$config["attachment_extensions"].')</div>';
			}
			echo '
		</div>';
	}

	/**
	 * Редактирование поля "Прикрепленные файлы" для настроек модуля
	 * 
	 * @return void
	 */
	public function edit_config()
	{
		$fields = array('max_count_attachments', 'attachment_extensions', 'recognize_image', 'attach_big_width', 'attach_big_height', 'attach_big_quality', 'attach_medium_width', 'attach_medium_height', 'attach_medium_quality', 'use_animation', 'attachments_access_admin');
		foreach ($fields as $field)
		{
			if (empty($values[$field]))
			{
				$values[$field] = $this->diafan->configmodules($field, $this->diafan->_admin->module, $this->diafan->_route->site);
			}
		}
		echo '
		<div class="unit" id="attachments">
			<input type="checkbox" name="attachments" id="input_attachments" value="1" '
			.($this->diafan->values("attachments") ? " checked" : '')
			.' id="attachments_">
			<label for="input_attachments">'.$this->diafan->variable_name("attachments").$this->diafan->help("attachments").'</label>
		</div>';
		$values["is_config"] = true;
		$this->edit_config_view($values);
	}

	/**
	 * Редактирование настроек поля конструктора с типом "Файлы"
	 * 
	 * @param string $config конфигурация поля
	 * @return void
	 */
	public function edit_config_param($config)
	{
		$this->diafan->_admin->js_view[] = 'modules/attachments/admin/js/attachments.admin.inc.js';

		$values = unserialize($config);
		$this->edit_config_view($values);
	}

	/**
	 * Настройки модуля
	 *
	 * @param array $values значения настроек
	 * @return void
	 */
	private function edit_config_view($values)
	{
		$fields = array('max_count_attachments', 'attachment_extensions', 'recognize_image', 'attach_big_width', 'attach_big_height', 'attach_big_quality', 'attach_medium_width', 'attach_medium_height', 'attach_medium_quality', 'use_animation', 'attachments_access_admin');
		foreach ($fields as $field)
		{
			if (empty($values[$field]))
			{
				$values[$field] = "";
			}
		}

		$depend = '';
		if(! empty($values["is_config"]))
		{
			$depend = ' depend_field" depend="attachments';
		}

		echo '<div class="unit'.$depend.'" id="max_count_attachments">
			<div class="infofield">'.$this->diafan->_("Максимальное количество добавляемых файлов").'</div>
			<input type="number" name="max_count_attachments" size="20" value="'.$values["max_count_attachments"].'">
		</div>
		<div class="unit'.$depend.'" id="attachment_extensions">
			<div class="infofield">'.$this->diafan->_("Доступные типы файлов (через запятую)").'</div>
			<input type="text" name="attachment_extensions" size="40" value="'.$values["attachment_extensions"].'">
		</div>
		<div class="unit'.$depend.'" id="attachments_access_admin">
			<input type="checkbox" name="attachments_access_admin" id="input_attachments_access_admin"'.($values["attachments_access_admin"] ? '  checked' : '').' value="1">
			<label for="input_attachments_access_admin">'.$this->diafan->_("Доступ к файлам только для админа").'</label>
		</div>
		<div class="unit'.$depend.'" id="recognize_image">
			<input type="checkbox" name="recognize_image" value="1"'
			.($values["recognize_image"] == 1 ? " checked" : '')
			.' id="recognize_image_">
			<label for="recognize_image_">'.$this->diafan->_("Распознавать изображения").'</label>
		</div>
		<div class="unit'.($depend ? $depend.',recognize_image' : ' depend_field" depend="recognize_image').'" id="attach_big">
			<div class="infofield">'.$this->diafan->_('Размер большого изображения')
			.$this->diafan->help("Размер и качество, до которых изображение будет автоматически изменяться после загрузки").'</div>
	
			<input type="number" name="attach_big_width" size="3" value="'.$values["attach_big_width"].'"> x
			<input type="number" name="attach_big_height" size="3" value="'.$values["attach_big_height"].'">,
			'.$this->diafan->_('качество').'
			<input type="number" name="attach_big_quality" size="2" value="'.$values["attach_big_quality"].'"></td>
		</div>
		<div class="unit'.($depend ? $depend.',recognize_image' : ' depend_field" depend="recognize_image').'" id="attach_medium">
			<div class="infofield">'.$this->diafan->_('Размер маленького изображения')
			.$this->diafan->help("Размер и качество, до которых изображение будет автоматически изменяться после загрузки").'</div>

			<input type="number" name="attach_medium_width" size="3" value="'.$values["attach_medium_width"].'"> x
			<input type="number" name="attach_medium_height" size="3" value="'.$values["attach_medium_height"].'">,
			'.$this->diafan->_('качество').'
			<input type="number" name="attach_medium_quality" size="2" value="'.$values["attach_medium_quality"].'">
		</div>
		<div class="unit'.($depend ? $depend.',recognize_image' : ' depend_field" depend="recognize_image').'" id="attach_use_animation">
			<input type="checkbox" name="use_animation" id="input_use_animation" value="1"'.($values["use_animation"] == 1 ? " checked" : '').'>
			<label for="input_use_animation">'.$this->diafan->_("Использовать анимацию при увеличении изображений").'</label>
		</div>
		<div class="unit'.$depend.'" id="upload_max_filesize">
			<b>'.$this->diafan->_("Максимальный размер загружаемых файлов").':</b>
			'.ini_get('upload_max_filesize').'
		</div>';
	}

	/**
	 * Сохранение поля "Прикрепленные файлы"
	 * 
	 * @return void
	 */
	public function save()
	{
		$rows = DB::query_fetch_all("SELECT id FROM {attachments} WHERE module_name='%s' AND element_id=%d", $this->diafan->table, $this->diafan->id);
		foreach ($rows as $row)
		{
			if (! empty($_POST["attachment_delete"]) && in_array($row["id"], $_POST["attachment_delete"]))
			{
				$this->diafan->_attachments->delete($this->diafan->id, $this->diafan->table, $row["id"]);
			}
		}

		if (! empty($_FILES['attachments']))
		{
			$config["site_id"] = $this->diafan->filter($_POST, "int", "site_id");
			$config["type"] = 'configmodules';

			$this->diafan->_attachments->save($this->diafan->id, $this->diafan->table, $config);
		}
	}

	/**
	 * Сохранение поля с типом "Файлы" из конструктора
	 *
	 * @param integer $id номер поля
	 * @param array $config настройки для поля конструктора
	 * @return void
	 */
	public function save_param($id, $config)
	{
		$rows = DB::query_fetch_all("SELECT id FROM {attachments} WHERE module_name='%s' AND element_id=%d AND param_id=%d", $this->diafan->table, $this->diafan->id, $id);
		foreach ($rows as $row)
		{
			if (! empty($_POST["attachment_delete"]) && in_array($row["id"], $_POST["attachment_delete"]))
			{
				$this->diafan->_attachments->delete($this->diafan->id, $this->diafan->table, $row["id"], $id);
			}
		}

		if (! empty($_FILES['attachments'.$id]))
		{
			$config = unserialize($config);
			$config["param_id"] = $id;

			$this->diafan->_attachments->save($this->diafan->id, $this->diafan->table, $config);
		}
	}

	/**
	 * Сохранение настроек конфигурации модулей
	 * 
	 * @return void
	 */
	public function save_config()
	{
		$fields = array('attachments', 'max_count_attachments', 'attachments_access_admin', 'recognize_image', 'attach_big_width', 'attach_big_height', 'attach_big_quality', 'attach_medium_width', 'attach_medium_height', 'attach_medium_quality', 'use_animation');
		foreach ($fields as $field)
		{
			$this->diafan->set_query($field."='%h'");
			$this->diafan->set_value(! empty($_POST[$field]) ? $_POST[$field] : '');
		}
		$this->diafan->set_query("attachment_extensions='%h'");
		$this->diafan->set_value(! empty($_POST["attachment_extensions"]) ? $_POST["attachment_extensions"] : '');
	}

	/**
	 * Сохранение настроек для поля конструктора
	 * 
	 * @return void
	 */
	public function save_config_param()
	{
		if($_POST["type"] == "attachments")
		{
			$config = array(
				'max_count_attachments'    => $this->diafan->filter($_POST, "int", "max_count_attachments"),
				"attachment_extensions"    => $this->diafan->filter($_POST, "string", "attachment_extensions"),
				"recognize_image"          => ! empty($_POST["recognize_image"]) ? 1 : 0,
				"attachments_access_admin" => ! empty($_POST["attachments_access_admin"]) ? 1 : 0,
				"attach_big_width"         => $this->diafan->filter($_POST, "int", "attach_big_width"),
				"attach_big_height"        => $this->diafan->filter($_POST, "int", "attach_big_height"),
				"attach_big_quality"       => $this->diafan->filter($_POST, "int", "attach_big_quality"),
				"attach_medium_width"      => $this->diafan->filter($_POST, "int", "attach_medium_width"),
				"attach_medium_height"     => $this->diafan->filter($_POST, "int", "attach_medium_height"),
				"attach_medium_quality"    => $this->diafan->filter($_POST, "int", "attach_medium_quality"),
				"use_animation"            => ! empty($_POST["use_animation"]) ? 1 : 0,
			);
			$value = serialize($config);
			$this->diafan->set_query("config='%s'");
			$this->diafan->set_value($value);
		}
	}

	/**
	 * Помечает файлы на удаление или удаляет файлы
	 * 
	 * @param array $element_ids номера элементов
	 * @param string $module_name название модуля
	 * @param string $element_type тип данных
	 * @return void
	 */
	public function delete($element_ids, $module_name, $element_type)
	{
		if($element_type != 'param' &&  $element_type != 'element')
			return;

		if (is_dir(ABSOLUTE_PATH.USERFILES."/".$module_name."/files"))
		{
			if ($_POST["action"] == "delete")
			{
				if($element_type == 'param')
				{
					if(is_array($element_ids))
					{
						$where = " IN (%s)";
						$value = preg_replace('/[^0-9,]+/', '', implode(",", $element_ids));
					}
					else
					{
						$where = "=%d";
						$value = $element_ids;
					}
					$rows = DB::query_fetch_all("SELECT id, name, is_image FROM {attachments} WHERE module_name='%s' AND param_id IN (".implode(",", $element_ids).")", $module_name);
					foreach ($rows as $row)
					{
						if ($row["is_image"])
						{
							File::delete_file(USERFILES."/".$module_name."/imgs/".$row["name"]);
							File::delete_file(USERFILES."/".$module_name."/imgs/small/".$row["name"]);
						}
						else
						{
							File::delete_file(USERFILES."/".$module_name."/files/".$row["id"]);
						}						
					}
				}
				else
				{
					$this->diafan->_attachments->delete($element_ids, $module_name);
				}
			}
			$this->diafan->del_or_trash_where("attachments", ($element_type == 'param' ? "param_id" : "element_id")." IN (".implode(",", $element_ids).") AND module_name='".$module_name."'");
		}
	}

	/**
	 * Удаляет файл
	 * 
	 * @param integer $id номер удаляемого файла
	 * @return void
	 */
	public function del_from_trash($id, $table)
	{
		$row = DB::query_fetch_array("SELECT module_name, element_id FROM {attachments} WHERE id=%d LIMIT 1", $id);
		$this->diafan->_attachments->delete($row["element_id"], $row["module_name"], $id);
	}
}
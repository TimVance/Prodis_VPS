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
 * Images_admin_inc
 */
class Images_admin_inc extends Diafan
{
	/**
	 * Редактирование поля "Изображения"
	 *
	 * @return void
	 */
	public function edit()
	{
		$site_id = $this->diafan->values("site_id");

		if (! $this->diafan->configmodules("images_".$this->diafan->element_type(), $this->diafan->_admin->module, $site_id))
		{
			return;
		}
		$name = $this->diafan->variable_name();
		$help = 'images';
		$this->edit_view(0, $name, $help);
	}

	/**
	 * Редактирование поля с типом "Изображения" из конструктора
	 *
	 * @param integer $param_id номер поля конструктора
	 * @param string $name имя поля конструктора
	 * @param string $help описание поля конструктора
	 * @param string $attr атрибуты строки
	 * @param string $class CSS-класс
	 * @return void
	 */
	public function edit_param($param_id, $name, $help, $attr = '', $class = '')
	{
		$this->diafan->_admin->js_view[] = 'modules/images/admin/js/images.admin.inc.js';
		$this->edit_view($param_id, $name, $help, $attr, $class);
	}

	/**
	 * Шаблон редактирования изображений
	 *
	 * @param integer $param_id номер поля конструктора
	 * @param string $name имя поля
	 * @param string $help описание поля
	 * @param string $attr атрибуты строки
	 * @param string $class CSS-класс
	 * @return void
	 */
	private function edit_view($param_id, $name, $help, $attr = '', $class = '')
	{
		$this->diafan->config('multiupload', true);
		if (! extension_loaded('gd'))
		{
			echo '<div class="error">'.$this->diafan->_('Внимание! Не установлена библиотека GD. Работа модуля ограничена. Обратитесь в техподдержку вашего хостинга!').'</div>';
		}
		Custom::inc('modules/images/admin/images.admin.view.php');

		if($this->diafan->is_new && empty($GLOBALS["images_hidden_tmpcode"]))
		{
			echo '<input type="hidden" name="tmpcode" value="'.md5(rand(0, 9999)).'">';
			$GLOBALS["images_hidden_tmpcode"] = true;
		}

		echo '<div class="unit images'.($class ? ' '.$class : '').'" id="images'.$param_id.'"'.$attr.' param_id="'.$param_id.'">
			<h2>'.$name.$this->diafan->help($help).'</h2>
				<div class="images_container images_container'.$param_id.'">';
				if (! $this->diafan->is_new)
				{
					$images_view = new Images_admin_view($this->diafan);
					echo $images_view->show($this->diafan->id, $param_id);
				}
				echo '</div>

				<div class="inp-file">
					<input class="fileupload" id="fileupload'.$param_id.'" type="file" name="images[]" data-url="'.URL.($this->diafan->is_new ? 'addnew1' : 'edit'.$this->diafan->id).'/" param_id="'.$param_id.'" multiple>
					<span class="btn btn_blue btn_small btn_inp_file">
						<i class="fa fa-plus-square"></i>
						'.$this->diafan->_('Загрузить изображение').'
					</span>
				</div>

				<a href="javascript:void(0)" class="view_images">
					<i class="fa fa-plus-square"></i>
					'.$this->diafan->_('Выбрать из загруженных').'
				</a>

				<div class="div_images_links">
					<div>'.$this->diafan->_('Загрузить изображение по ссылке').':</div>

					<input type="text" placeholder="http://">
					<span class="btn btn_blue btn_small images_upload_links" param_id="'.$param_id.'">'.$this->diafan->_('Загрузить').'</span>
				</div>
				<div>'.$this->diafan->_('Максимальный размер загружаемого изображения %s.', ini_get('upload_max_filesize')).'</div>
				<div class="error_images'.$param_id.' error hide"></div>
				<div class="ipopup ipopup_edit images_edit"></div>
				<div class="hide selectarea"></div>
				<h2></h2>
		</div>';
	}

	/**
	 * Редактирование поля "Подключить изображения" в настройках модуля
	 *
	 * @return void
	 */
	public function edit_config()
	{
		$element_types = $this->diafan->variable("images", "element_type");
		if(! $element_types)
		{
			$element_types = array('element');
		}
		foreach($element_types as $element_type)
		{
			if($this->diafan->is_variable('images_'.$element_type))
			{
				echo '<div class="unit'.($element_type == 'cat' ? ' depend_field" depend="cat' : '').'" id="images_'.$element_type.'">
					<input type="checkbox" name="images_'.$element_type.'" value="1" '
					.($this->diafan->values("images_".$element_type) ? " checked" : '')
					.' id="images_'.$element_type.'_">
					<label for="images_'.$element_type.'_">'.$this->diafan->variable_name("images_".$element_type).'
					'.$this->diafan->help("images_".$element_type).'</label>
					</div>';
			}
			if($this->diafan->is_variable('images_variations_'.$element_type))
			{
				$depend = '';
				if($this->diafan->is_variable('images_'.$element_type))
				{
					$depend = ' depend_field" depend="images_'.$element_type.($element_type == 'cat' ? ',cat' : '');
				}
				$count_variation = $this->diafan->variable("images_variations_".$element_type, "count");
				echo '
				<div class="unit images_variations'.$depend.'" id="images_variations_'.$element_type.'">
					<div class="infofield">'.$this->diafan->_("Генерировать %sразмеры изображений%s", '<a href="'.BASE_PATH_HREF.'images/" target="_blank">', '</a>').$this->diafan->help("images_variations_".$element_type).'</div>
					';
					$variations = unserialize($this->diafan->values("images_variations_".$element_type));
					$variation_medium = array('name' => 'medium', 'id'  => 0);
					$variation_large = array('name' => 'large', 'id'  => 0);
					if($variations)
					{
						foreach ($variations as $variation)
						{
							if($variation["name"] == 'medium')
							{
								$variation_medium = $variation;
							}
							if($variation["name"] == 'large')
							{
								$variation_large = $variation;
							}
						}
					}
					$this->get_images_variation($element_type, $count_variation, $variation_large);
					if($count_variation != 1)
					{
						$this->get_images_variation($element_type, $count_variation, $variation_medium);
						if($variations)
						{
							foreach ($variations as $variation)
							{
								if($variation["name"] != 'medium' && $variation["name"] != 'large')
								{
									$this->get_images_variation($element_type, $count_variation, $variation);
								}
							}
						}
						$this->get_images_variation($element_type, $count_variation);
						echo '
						<a href="javascript:void(0)" class="images_variation_plus" title="'.$this->diafan->_('Добавить').'"><i class="fa fa-plus-square"></i> '.$this->diafan->_('Добавить').'</a>';
					}
				echo '</div>';
			}
			if($this->diafan->is_variable('list_img_'.$element_type))
			{
				echo '
				<div class="unit depend_field" depend="images_'.$element_type.($element_type == 'cat' ? ',cat' : '').'" id="list_img_'.$element_type.'">
					<div class="infofield">'.$this->diafan->variable_name("list_img_".$element_type).$this->diafan->help("list_img_".$element_type).'</div>
					<select name="list_img_'.$element_type.'">
						<option value="0">'.$this->diafan->_('нет').'</option>
						<option value="1"'.($this->diafan->values("list_img_".$element_type) == 1 ? ' selected' : '').'>'.$this->diafan->_('показывать одно изображение').'</option>
						<option value="2"'.($this->diafan->values("list_img_".$element_type) == 2 ? ' selected' : '').'>'.$this->diafan->_('показывать все изображения').'</option>
					</select>
				</div>';
			}
		}
		$depend = '';
		if($this->diafan->is_variable('images_element'))
		{
			$depend = ' depend_field" depend="images_'.implode('|images_', $element_types);
		}
		if($this->diafan->is_variable('resize'))
		{
			echo '<div class="unit'.$depend.'" id="resize">
				<b>'.$this->diafan->variable_name("resize").':</b>
				<input type="button" name="resize" confirm="'.$this->diafan->_('Изменения необратимы! Для изменения размера необходимо некоторое время. Не закрывайте окно браузера до окончания выполнения скрипта. Продолжить?').'" value="'.$this->diafan->_('Применить').'">
				'.$this->diafan->help("resize").'

				<div class="errors images_loading_resize hide"><img src="'.BASE_PATH.'adm/img/loading.gif"></div>
				<div class="errors error_resize"></div>
			</div>';
		}
		if($this->diafan->is_variable('use_animation'))
		{
			echo '
			<div class="unit'.$depend.'" id="use_animation">
				<input type="checkbox" name="use_animation" id="input_use_animation" value="1"'.($this->diafan->values("use_animation") ? " checked" : '').'>
				<label for="input_use_animation">'.$this->diafan->variable_name("use_animation").$this->diafan->help("use_animation").'</label>
			</div>';
		}
		if($this->diafan->is_variable('upload_max_filesize'))
		{
			echo '<div class="unit'.$depend.'" id="upload_max_filesize">
				<b>'.$this->diafan->variable_name("upload_max_filesize").':</b>
				'.ini_get('upload_max_filesize').'
				'.$this->diafan->help("upload_max_filesize").'
			</div>';
		}
	}

	/**
	 * Редактирование настроек поля конструктора с типом "Изображения"
	 *
	 * @param string $config конфигурация поля
	 * @return void
	 */
	public function edit_config_param($config)
	{
		$this->diafan->_admin->js_view[] = 'modules/images/admin/js/images.admin.inc.config.js';

		echo '
		<div class="unit images_variations" id="images_variations">
			<div class="infofield">'.$this->diafan->_("Генерировать %sразмеры изображений%s", '<a href="'.BASE_PATH_HREF.'images/" target="_blank">', '</a>').'
			<i class="tooltip fa fa-question-circle" title="'.$this->diafan->_('Параметры автоматической обработки всех загружаемых  изображений. Каждое загружаемое изображение в модуль будет обрабатываться, согласно указанным здесь преднастройкам. Более подробно смотрите модуль «Размеры изображений».').'"></i>
			</div>';
			$variations = unserialize($config);
			$variation_large = array('name' => 'large', 'id'  => 0);
			if($variations)
			{
				foreach ($variations as $variation)
				{
					if(isset($variation["name"]) && $variation["name"] == 'large')
					{
						$variation_large = $variation;
					}
				}
			}
			$this->get_images_variation('element', 1, $variation_large);
			echo '
		</div>';

		if(! $this->diafan->is_new)
		{
			echo '<div class="unit" id="resize">
				<b>'.$this->diafan->_("Применить настройки ко всем ранее загруженным изображениям").':</b>

				<input type="button" name="resize" confirm="'.$this->diafan->_('Изменения необратимы! Для изменения размера необходимо некоторое время. Не закрывайте окно браузера до окончания выполнения скрипта. Продолжить?').'" value="'.$this->diafan->_('Применить').'">
				<i class="tooltip fa fa-question-circle" title="'.$this->diafan->_('Загруженные изображения всегда хранятся в исходном виде. Если нажать на эту кнопку, из исходных изображений будут сгенерированы новые изображения, согласно параметрам «Генерировать размеры изображений», указанным выше').'"></i>

				<div class="errors images_loading_resize hide"><img src="'.BASE_PATH.'adm/img/loading.gif"></div>
				<div class="errors error_resize"></div>
			</div>';
		}
	}

	/**
	 * Выводит вариант размера изображений в настройках модуля
	 *
	 * @param string $element_type тип элемента
	 * @param integer $count_variation количество допустимых вариантов изображения
	 * @param array $module_variation выбранный вариант
	 * @return void
	 */
	private function get_images_variation($element_type, $count_variation, $module_variation = array())
	{
		if(! isset($this->cache["image_variations"]))
		{
			$this->cache["image_variations"] = DB::query_fetch_all("SELECT id, name FROM {images_variations} WHERE trash='0' ORDER BY id DESC");
		}
		echo '
		<div class="images_variation"'.(empty($module_variation) ? ' style="display:none"' : '').'>';
		if(empty($module_variation))
		{
			$module_variation = array("name" => '', "id" => 0);
		}
		echo '<select name="images_variation_id_'.$element_type.'[]">';
		foreach ($this->cache["image_variations"] as $variation)
		{
			echo '<option value="'.$variation["id"].'"'.($variation["id"] == $module_variation["id"] ? ' selected' : '').'>'.$variation["name"].'</option>';
		}
		echo '</select> ';
		if($module_variation["name"] == 'medium' || $module_variation["name"] == 'large' || $count_variation == 1)
		{
			echo '<input type="hidden" name="images_variation_name_'.$element_type.'[]" size="10" value="'.$module_variation["name"].'">';
			if($module_variation["name"] == 'medium')
			{
				echo 'medium';
			}
			if($module_variation["name"] == 'large' && $count_variation != 1)
			{
				echo 'large';
			}
		}
		else
		{
			echo '<input type="text" name="'.(! $module_variation["id"] ? 'hide_' : '').'images_variation_name_'.$element_type.'[]" size="10" value="'.$module_variation["name"].'" title="'.$this->diafan->_('Название размера для шаблонного тега').'">
			<a href="javascript:void(0)" confirm="'.$this->diafan->_('Все изображения этого размера будут удалены. Вы действительно хотите удалить размер?').'" class="images_variation_delete"><i class="fa fa-close delete" title="'.$this->diafan->_('Удалить').'"></i></a>';
		}
		echo '</div>';
	}

	/**
	 * Сохранение поля "Изображения"
	 *
	 * @return void
	 */
	public function save()
	{
		if (! empty($_POST["tmpcode"]))
		{
			DB::query("UPDATE {images} SET element_id=%d, tmpcode='' WHERE module_name='%s' AND element_type='%s' AND tmpcode='%s'",
				$this->diafan->id,
				$this->diafan->_admin->module,
				$this->diafan->element_type(),
				$_POST["tmpcode"]
			);
		}
		$rows = DB::query_fetch_all("SELECT * FROM {images} WHERE element_id=0 AND created<%d AND module_name<>'editor'", time() - 14400);
		foreach ($rows as $row)
		{
			$this->diafan->_images->delete_row($row);
		}
	}

	/**
	 * Сохранение настроек конфигурации модулей
	 *
	 * @return void
	 */
	public function save_config()
	{
		$element_types = $this->diafan->variable("images", "element_type");
		if(! $element_types)
		{
			$element_types = array('element');
		}
		$fields = array("use_animation");
		foreach($element_types as $element_type)
		{
			if($this->diafan->is_variable('images_'.$element_type))
			{
				$fields[] = 'images_'.$element_type;
			}
			else
			{
				$this->diafan->set_query('images_'.$element_type."='%d'");
				$this->diafan->set_value(1);
			}
			if($this->diafan->is_variable('list_img_'.$element_type))
			{
				$fields[] = 'list_img_'.$element_type;
			}
			$variations = array();
			$new_variations = array();
			foreach ($_POST["images_variation_name_".$element_type] as $i => $name)
			{
				$variations[] = array("name" => $this->diafan->translit($name), "id" => $_POST["images_variation_id_".$element_type][$i]);
				$new_variations[] = $_POST["images_variation_id_".$element_type][$i];
			}
			$this->diafan->set_query("images_variations_".$element_type."='%s'");
			$this->diafan->set_value(serialize($variations));
		}
		foreach ($fields as $field)
		{
			$this->diafan->set_query($field."='%d'");
			$this->diafan->set_value(! empty($_POST[$field]) ? $_POST[$field] : '');
		}
	}

	/**
	 * Сохранение настроек для поля конструктора
	 *
	 * @return void
	 */
	public function save_config_param()
	{
		if($_POST["type"] == "images")
		{
			$value = array();
			$new_variations = array();
			foreach ($_POST["images_variation_name_element"] as $i => $name)
			{
				$value[] = array("name" => $this->diafan->translit($name), "id" => $_POST["images_variation_id_element"][$i]);
				$new_variations[] = $_POST["images_variation_id_element"][$i];
			}
			$value = serialize($value);
			$this->diafan->set_query("config='%s'");
			$this->diafan->set_value($value);
		}
	}

	/**
	 * Помечает изображения на удаление или удаляет изображения
	 *
	 * @param array $element_ids номера элементов
	 * @param string $module_name название модуля
	 * @param string $element_type тип данных
	 * @return void
	 */
	public function delete($element_ids, $module_name, $element_type)
	{
		if (is_dir(ABSOLUTE_PATH.USERFILES."/".$module_name))
		{
			if ($_POST["action"] == "delete")
			{
				if($element_type == 'param')
				{
					$rows = DB::query_fetch_all("SELECT * FROM {images} WHERE param_id IN (".implode(",", $element_ids).") AND module_name='%h'", $module_name);
					foreach($rows as $row)
					{
						$this->diafan->_images->delete_row($row);
					}
				}
				else
				{
					$this->diafan->_images->delete($element_ids, $module_name, $element_type);
				}
			}
			if($element_type == 'param')
			{
				$this->diafan->del_or_trash_where("images", "param_id IN (".implode(",", $element_ids).") AND module_name='".$module_name."'");
			}
			else
			{
				$this->diafan->del_or_trash_where("images", "element_id IN (".implode(",", $element_ids).") AND module_name='".$module_name."' AND element_type='".$element_type."'");
			}
		}
	}

	/**
	 * Удаляет изображение
	 *
	 * @param integer $id номер удаляемого изображения
	 * @param string $table название таблицы
	 * @return void
	 */
	public function del_from_trash($id, $table)
	{
		switch($table)
		{
			case 'images':
				$row = DB::query_fetch_array("SELECT * FROM {images} WHERE id=%d LIMIT 1", $id);
				if($row)
				{
					$row["from_image_id"] = DB::query_result("SELECT id FROM {images} WHERE image_id=%d LIMIT 1", $row["id"]);
					if($row["from_image_id"])
					{
						DB::query("UPDATE {images} SET image_id=0 WHERE id=%d", $row["from_image_id"]);
					}
					$this->diafan->_images->delete_row($row);
				}
				break;

			case 'images_variations':
				if(! $folder = DB::query_result("SELECT folder FROM {images_variations} WHERE id=%d AND trash='0' LIMIT 1", $id))
					return;
				File::delete_dir(USERFILES.'/'.$module.'/'.$folder);
				break;
		}
	}
}

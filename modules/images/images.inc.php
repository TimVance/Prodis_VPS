<?php
/**
 * Подключение для работы с прикрепленными изображениями
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
 * Images_inc
 */
class Images_inc extends Diafan
{
	/**
	 * var array максимальный размер и качество оригинала изображения
	 */
	private $original = array(2000, 2000, 90);

	/**
	 * var array размер и качество изображения, выводимого в административной части
	 */
	private $small = array(50, 50, 70);

	/**
	 * var integer максимальное количество файлов в папке
	 */
	private $max_files_in_folder = 10000;

	/**
	 * var string маркер предварительного удаления записи
	 */
	private $trash = '_';

	/**
	 * Получает изображения, прикрепленные к элементу модуля
	 *
	 * @param string $variation размер изображения, указанный в настройках модуля
	 * @param integer $element_id номер элемента, к которому прикреплены изображения
	 * @param string $module_name название модуля, по умолчанию модуль, прикрепленный к текущей странице
	 * @param string $element_type тип данных (*element* – элемент (по умолчанию), *cat* – категория, *brand* – производитель)
	 * @param integer $site_id страница сайта, к которой прикреплен элемент
	 * @param string $alt альтернативный текст получаемых изображений
	 * @param integer $param_id номер параметра, к которому прикреплено изображение
	 * @param integer $count количество изображений
	 * @param string $link_to размер изображения, на который ведет ссылка
	 * @param string $tmpcode временный идентификационный код элемента, к которому прикреплены изображения
	 * @return array
	 */
	public function get($variation, $element_id, $module_name, $element_type, $site_id, $alt, $param_id = 0, $count = 0, $link_to = '', $tmpcode = '')
	{
		$param_id = intval($param_id);
		$this->check_element_type($element_type);
		if(! $variation)
		{
			return array();
		}
		if($tmpcode || is_array($count))
		{
			$rows = DB::query_fetch_all(
					"SELECT id, name, [alt], [title], folder_num FROM {images}"
					." WHERE module_name='%s' AND element_type='%s' AND element_id=%d AND param_id=%d AND trash='0' AND tmpcode='%s'"
					.(is_array($count) ? " AND id IN (".implode(",", $count).")" : "")
					." ORDER BY sort ASC"
					.($count && ! is_array($count) ? " LIMIT ".$count : ''),
					$module_name, $element_type,
					$element_id, $param_id, $tmpcode
				);
		}
		else
		{
			$this->prepare($element_id, $module_name, $element_type, $param_id);
			if(! empty($this->cache["prepare"]))
			{
				$where = array();
				$values = array();
				foreach ($this->cache["prepare"] as $pr_module_name => $array)
				{
					$wh = array();
					$values[] = $pr_module_name;
					foreach ($array as $pr_element_type => $arr)
					{
						$w = array();
						$values[] = $pr_element_type;
						foreach ($arr as $pr_param_id => $ar)
						{
							$values[] = $pr_param_id;
							foreach ($ar as $pr_element_id => $a)
							{
								$this->cache["images"][$pr_module_name][$pr_element_type][$pr_param_id][$pr_element_id] = array();
								$values[] = $a;
								$v_arr[] = '%d';
							}
							$w[] = " param_id=%d AND element_id IN (".implode(",", $v_arr).")";
						}
						$wh[] = " element_type='%s' AND (".implode(" OR ", $w).")";
					}
					$where[] = "module_name='%h' AND (".implode(" OR ", $wh).")";
				}
				$rows = DB::query_fetch_all(
						"SELECT id, name, [alt], [title], folder_num, module_name, param_id, element_id, element_type FROM {images}"
						." WHERE trash='0' AND ".(count($where) > 1 ? "(".implode(" OR ", $where).")" : $where[0])
						." ORDER BY sort ASC", $values
					);
				foreach ($rows as $row)
				{
					$this->cache["images"][$row["module_name"]][$row["element_type"]]['p'.$row["param_id"]]['e'.$row["element_id"]][] = $row;
				}
				unset($this->cache["prepare"]);
			}
			$rows = $this->cache["images"][$module_name][$element_type]['p'.$param_id]['e'.$element_id];
		}
		if(empty($rows))
		{
			return array();
		}
		$this->get_variations();

		if($param_id)
		{
			if(! isset($this->cache["param_config"][$module_name][$param_id]))
			{
				$this->cache["param_config"][$module_name][$param_id] = unserialize(DB::query_result("SELECT config FROM {%s_param} WHERE id=%d LIMIT 1", $module_name, $param_id));
			}
			$module_variations = $this->cache["param_config"][$module_name][$param_id];
		}
		else
		{
			$module_variations = unserialize($this->diafan->configmodules('images_variations_'.$element_type, $module_name, $site_id));
		}
		if(! $module_variations)
			return array();

		$variation_folder = '';
		$link_to_variation_folder = '';
		foreach ($module_variations as $module_variation)
		{
			if($module_variation['name'] == $variation && ! empty($this->cache["images_variations"][$module_variation['id']]))
			{
				$variation_folder = $this->cache["images_variations"][$module_variation['id']]["folder"];
			}
			if($link_to && $module_variation['name'] == $link_to && ! empty($this->cache["images_variations"][$module_variation['id']]))
			{
				$link_to_variation_folder = $this->cache["images_variations"][$module_variation['id']]["folder"];
			}
		}
		if(! $variation_folder)
		{
			return array();
		}

		$images = array();
		foreach ($rows as $row)
		{
			if(! is_array($count) && $count && count($images) == $count)
			{
				break;
			}
			if(! file_exists(ABSOLUTE_PATH.USERFILES.'/'.$module_name.'/'.$variation_folder.'/'.($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"]))
			{
				continue;
			}
			if ($link_to_variation_folder)
			{
				$img["link"] = USERFILES.'/'.$module_name.'/'.$link_to_variation_folder.'/'.($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"];

				if (! $this->diafan->configmodules('use_animation', $module_name))
				{
					list($img["link_width"], $img["link_height"]) = getimagesize(ABSOLUTE_PATH.USERFILES.'/'.$module_name.'/'.$link_to_variation_folder.'/'.($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"]);
					$img["type"] = "large_image";
				}
				else
				{
					$img["type"] = "animation";
				}
			}
			else
			{
				$img["type"] = "link";
				$img["link"] = $link_to;
			}
			$img["id"] = $row["id"];
			list($img["width"], $img["height"]) = getimagesize(ABSOLUTE_PATH.USERFILES.'/'.$module_name.'/'.$variation_folder.'/'.($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"]);
			$img["alt"] = $row["alt"] ? $row["alt"] : $alt;
			$img["title"] = $row["title"] ? $row["title"] : $alt;
			$img["src"] = (REVATIVE_PATH ? '/'.REVATIVE_PATH : '').'/'.USERFILES.'/'.$module_name.'/'.$variation_folder.'/'.($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"];
			$img["vs"] = array();
			foreach ($module_variations as $module_variation)
			{
				$img["vs"][$module_variation["name"]] = (REVATIVE_PATH ? '/'.REVATIVE_PATH : '').'/'.USERFILES.'/'.$module_name.'/'
				.$this->cache["images_variations"][$module_variation['id']]["folder"].'/'
				.($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"];
			}
			$images[] = $img;
		}
		return $images;
	}

	/**
	 * Запоминает данные об элементах, которым нужно вывести прикрепленные изображения
	 *
	 * @param integer $element_id номер элемента, к которому прикреплены изображения
	 * @param string $module_name название модуля, по умолчанию модуль, прикрепленный к текущей странице
	 * @param string $element_type тип данных (*element* – элемент (по умолчанию), *cat* – категория, *brand* – производитель)
	 * @param integer $param_id номер параметра
	 * @return array
	 */
	public function prepare($element_id, $module_name, $element_type = 'element', $param_id = 0)
	{
		$this->check_element_type($element_type);
		if(isset($this->cache["images"][$module_name][$element_type]['p'.$param_id]['e'.$element_id]))
		{
			return;
		}
		if(! isset($this->cache["prepare"][$module_name][$element_type]['p'.$param_id]['e'.$element_id]))
		{
			$this->cache["prepare"][$module_name][$element_type]['p'.$param_id]['e'.$element_id] = $element_id;
		}
	}

	/**
	 * Удаляет прикрепленные изображения
	 *
	 * @param integer|array $element_ids номер одного или нескольких элементов
	 * @param string $module_name название модуля
	 * @param string $element_type тип данных (*element* – элемент (по умолчанию), *cat* – категория, *brand* – производитель)
	 * @param integer $param_id номер дополнительной характеристики с типом «Изображения»
	 * @param integer $trash режим удаления (*true* - непосредственное удаление, *false* - маркировка записи, как удаленной)
	 * @return void
	 */
	public function delete($element_ids, $module_name, $element_type = 'element', $param_id = false, $trash = true)
	{
		$this->check_element_type($element_type);
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
		$rows = DB::query_fetch_all("SELECT * FROM {images} WHERE element_id".$where." AND module_name='%h' AND element_type='%s'".($param_id !== false ? " AND param_id=%d" : ''), $value, $module_name, $element_type, $param_id);
		$delete_ids = $this->diafan->array_column($rows, "id");
		if(! $trash && ! empty($delete_ids))
		{
			DB::query("UPDATE {images} SET module_name=concat('%s', `module_name`) WHERE id IN(%s)", $this->trash, implode(",", $delete_ids));
			return $delete_ids;
		}
		$ids = array();
		foreach ($rows as $row)
		{
			if(! $row["image_id"])
			{
				$ids[] = $row["id"];
			}
		}
		if($ids)
		{
			$from_image_ids = DB::query_fetch_key_value("SELECT id, image_id FROM {images} WHERE image_id IN (%s)", implode(",", $ids), "image_id", "id");
		}
		if(! empty($from_image_ids))
		{
			DB::query("UPDATE {images} SET image_id=0 WHERE id IN (%s)", implode(',', $from_image_ids));
		}
		foreach ($rows as $row)
		{
			if(! empty($from_image_ids[$row["id"]]))
			{
				$row["from_image_id"] = $from_image_ids[$row["id"]];
			}
			$this->delete_row($row);
		}
		return $delete_ids;
	}

	/**
	 * Удаляет записи, ранее маркированные, как удаленные
	 *
	 * @param string $module_name название модуля
	 * @return void
	 */
	public function clear($module_name = false)
	{
		$rows = DB::query_fetch_all("SELECT * FROM {images} WHERE".($module_name === false ? " `module_name` LIKE '!_%' ESCAPE '!'" : " module_name='_%h'"), $module_name);
		$delete_ids = $this->diafan->array_column($rows, "id");
		$ids = array();
		foreach ($rows as $row)
		{
			if(! $row["image_id"])
			{
				$ids[] = $row["id"];
			}
		}
		if($ids)
		{
			$from_image_ids = DB::query_fetch_key_value("SELECT id, image_id FROM {images} WHERE image_id IN (%s)", implode(",", $ids), "image_id", "id");
		}
		if(! empty($from_image_ids))
		{
			DB::query("UPDATE {images} SET image_id=0 WHERE id IN (%s)", implode(',', $from_image_ids));
		}
		foreach ($rows as $row)
		{
			if(! empty($from_image_ids[$row["id"]]))
			{
				$row["from_image_id"] = $from_image_ids[$row["id"]];
			}
			$this->delete_row($row);
		}
		return $delete_ids;
	}

	/**
	 * Удаляет одно изображение
	 *
	 * @param array $row информация о изображении, записанная в базу данных
	 * @return void
	 */
	public function delete_row($row)
	{
		if (! $row)
			return;

		DB::query("DELETE FROM {images} WHERE id=%d", $row["id"]);

		if(! empty($row["from_image_id"]))
		{
			DB::query("UPDATE {images} SET image_id=%d WHERE image_id=%d", $row["from_image_id"], $row["id"]);
		}
		if(! $row["image_id"] && empty($row["from_image_id"]))
		{
			$this->get_variations();

			File::delete_file(USERFILES.'/original/'.($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"]);
			File::delete_file(USERFILES.'/small/'.($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"]);
			foreach ($this->cache["images_variations"] as $variation)
			{
				File::delete_file(USERFILES.'/'.$row["module_name"].'/'.$variation['folder'].'/'.($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"]);
			}
		}
	}

	/**
	 * Копирует одно изображение
	 *
	 * @param integer $id номер записи в таблице
	 * @param integer $element_id номер элемента, к которому прикреплены изображения
	 * @param string $tmpcode временный идентификационный код элемента, к которому прикреплены изображения
	 * @return integer
	 */
	public function copy_row($id, $element_id = false, $tmpcode = false)
	{
		if (! $id)
			return false;

		$row = DB::query_fetch_array("SELECT * FROM {images} WHERE id=%d AND trash='0'", $id);

		if (! $row)
			return false;

		$n = array();
		$m = array();
		$vs = array();
		foreach($row as $k => $v)
		{
			if($k == 'id')
				continue;

			$n[] = $k;
			switch($k)
			{
				case 'element_id':
					$m[] = "%d";
					$vs[] = ($element_id !== false ? $element_id : $v);
					break;

				case 'tmpcode':
					$vs[] = ($tmpcode !== false ? $tmpcode : $v);
					$m[] = "'%h'";
					break;

				case 'image_id':
					$vs[] = $row["id"];
					$m[] = "%d";
					break;

				case 'created':
					$m[] = "%d";
					$vs[] = time();
					break;

				default:
					$m[] = "'%s'";
					$vs[] = $v;
			}
		}
		$id = DB::query("INSERT INTO {images} (".implode(",", $n).") VALUES (".implode(",", $m).")", $vs);
		DB::query("UPDATE {images} SET sort=id WHERE id=%d", $id);

		return $id;
	}

	/**
	 * Удаляет все изображения модуля
	 *
	 * @param string $module_name название модуля
	 * @return void
	 */
	public function delete_module($module_name)
	{
		DB::query("DELETE FROM {trash} WHERE module_name='images' AND element_id IN (SELECT id FROM {images} WHERE module_name='%s')", $module_name);
		$rows = DB::query_fetch_all("SELECT name, folder_num FROM {images} WHERE module_name='%s'", $module_name);
		foreach ($rows as $row)
		{
			File::delete_file(USERFILES.'/original/'.($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"]);
			File::delete_file(USERFILES.'/small/'.($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"]);
		}
		DB::query("DELETE FROM {images} WHERE module_name='%s'", $module_name);
	}

	/**
	 * Загружает прикрепленные изображения
	 *
	 * @param integer $element_id номер элемента, к которому прикреплены изображения
	 * @param string $module_name название модуля
	 * @param string $element_type тип данных (*element* – элемент (по умолчанию), *cat* – категория, *brand* – производитель)
	 * @param integer $site_id страница сайта
	 * @param string $tmpfile расположение файла
	 * @param string $new_name название файла без расширения
	 * @param boolean $handle ручная загрузка изображений по одной
	 * @param integer $param_id номер дополнительной характеристики с типом «Изображения»
	 * @param string $tmpcode временный код для прикрепления изображений к еще не созданному  элементу
	 * @return mixed
	*/
	public function upload($element_id, $module_name, $element_type, $site_id, $tmpfile, $new_name, $handle = false, $param_id = 0, $tmpcode = '')
	{
		$this->check_element_type($element_type);

		$folder_num = 0;
		$max_id = DB::query_result("SELECT MAX(id) FROM {images} LIMIT 1");
		if($max_id > $this->max_files_in_folder)
		{
			$folder_num = ceil($max_id / $this->max_files_in_folder);
		}
		File::create_dir(USERFILES.'/original', true);

		if($folder_num)
		{
			File::create_dir(USERFILES.'/original/'.$folder_num, true);
		}

		if($param_id)
		{
			$config_images_variations = DB::query_result("SELECT config FROM {%s_param} WHERE id=%d LIMIT 1", $module_name, $param_id);
		}
		else
		{
			$config_images_variations = $this->diafan->configmodules('images_variations_'.$element_type, $module_name, $site_id);
		}

		if (! $config_images_variations)
		{
			if($param_id)
			{
				throw new Exception($this->diafan->_('В настройках поля не заданы размеры изображения.'));
			}
			else
			{
				throw new Exception($this->diafan->_('В настройках модуля не заданы размеры изображения.'));
			}
		}

		$tmp = time().mt_rand(0, 9999);
		$tmp_name = USERFILES."/original".($folder_num ? '/'.$folder_num : '')."/".$tmp;
		File::copy_file($tmpfile, $tmp_name);

		$info = @getimagesize(ABSOLUTE_PATH.$tmp_name);
		if ($info == false)
		{
			File::delete_file($tmp_name);
			throw new Exception($this->diafan->_('Неверный формат файла. Изображения загружаются только в форматах  GIF, JPEG, PNG.'));
		}

		Custom::inc("includes/image.php");

		$mimes = array(
			'image/gif' => 'gif',
			'image/jpeg' => 'jpg',
			'image/png' => 'png',
			'image/pjpeg' => 'jpg',
			'image/x-png'=> 'png'
		);
		if(empty($info['mime']) || ! in_array($info['mime'], array_keys($mimes)))
		{
			File::delete_file($tmp_name);
			throw new Exception($this->diafan->_('Неверный формат файла. Изображения загружаются только в форматах  GIF, JPEG, PNG.'));
		}
		$extension = $mimes[$info['mime']];

		$hash = '';
		// Проверяем наличие идентичного изображения в модуле
		if($this->diafan->configmodules('hash_compare', 'images') && $hash = File::hash_file($tmp_name))
		{
			if($image_id = $this->id_hash($hash, $module_name))
			{
				if($new_id = $this->copy_row($image_id))
				{
					DB::query("UPDATE {images} SET module_name='%s', element_type='%s', element_id=%d, param_id=%d, tmpcode='%s', created=%d, sort=id WHERE id=%d",
						$module_name,
						$element_type,
						$element_id,
						$param_id,
						$tmpcode,
						time(),
						$new_id
					);
					File::delete_file($tmp_name);
					$GLOBALS["image_id"] = $new_id;

					return false;
				}
			}
		}

		$new_name .= '.'.$extension;
		$rand = mt_rand(0, 999999);

		$new_id = DB::query(
				"INSERT INTO {images} (module_name, element_type, element_id, param_id, name, tmpcode, created, folder_num, hash) VALUES ('%s', '%s', %d, %d, '%s', '%s', %d, %d, '%s')",
				$module_name,
				$element_type,
				$element_id,
				$param_id,
				$rand,
				$tmpcode,
				time(),
				$folder_num,
				$hash
			);
		$GLOBALS["image_id"] = $new_id;

		$new_name = $new_id.'_'.$new_name;
		DB::query("UPDATE {images} SET name='%h', sort=id WHERE id=%d", $new_name, $new_id);

		File::rename_file($new_name, $tmp, USERFILES."/original".($folder_num ? '/'.$folder_num : ''));

		// уменьшает оригинал до размеров максимальных размеров
		if ($info[0] > $this->original[0] || $info[1] > $this->original[1])
		{
			if (! Image::resize(ABSOLUTE_PATH.USERFILES."/original/".($folder_num ? $folder_num.'/' : '').$new_name, $this->original[0], $this->original[1], $this->original[2]))
			{
				$this->delete_row($row);
				throw new Exception($this->diafan->_('Неверный формат файла. Изображения загружаются только в форматах  GIF, JPEG, PNG.'));
			}
		}

		// добавляет вариант изображения, выводимый в административной части
		File::copy_file(ABSOLUTE_PATH.USERFILES."/original/".($folder_num ? $folder_num.'/' : '').$new_name, USERFILES."/small".($folder_num ? '/'.$folder_num : '').'/'.$new_name);
		Image::resize(ABSOLUTE_PATH.USERFILES."/small/".($folder_num ? $folder_num.'/' : '').$new_name, $this->small[0], $this->small[1], $this->small[2]);

		$this->get_variations();

		$module_images_variations = unserialize($config_images_variations);
		foreach ($module_images_variations as $module_variation)
		{
			if(empty($this->cache["images_variations"][$module_variation["id"]]))
				continue;

			$variation = $this->cache["images_variations"][$module_variation["id"]];

			$action = $this->get_variation_image($new_name, $module_name, $variation, $folder_num, $handle);
			if($action && $action["name"] == 'selectarea')
			{
				$path = BASE_PATH.USERFILES."/".$module_name."/".$variation["folder"]."/".($folder_num ? $folder_num.'/' : '').$new_name;
				$selectarea[] = array("id" => $new_id, "variant_id" => $module_variation["id"], "path" => $path, "width" => $action["width"], "height" => $action["height"]);
			}
		}
		if(! empty($selectarea))
		{
			return $selectarea;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Применяет вариант к изображению
	 *
	 * @param string $file_name название файла
	 * @param string $module_name название модуля
	 * @param array $variation данные о варианте
	 * @param integer $folder_num номер папки
	 * @param boolean $handle ручная обработка (для вырезания части изображения)
	 * @param boolean $after_selectarea обработка после ручного выделения области
	 * @return void
	 */
	public function get_variation_image($file_name, $module_name, $variation, $folder_num, $handle = false, $after_selectarea = false)
	{
		Custom::inc("includes/image.php");

		$path = ABSOLUTE_PATH.USERFILES."/".$module_name."/".$variation["folder"]."/".($folder_num ? $folder_num.'/' : '').$file_name;
		if(! $after_selectarea)
		{
			File::copy_file(ABSOLUTE_PATH.USERFILES."/original/".($folder_num ? $folder_num.'/' : '').$file_name, USERFILES."/".$module_name."/".$variation["folder"]."/".($folder_num ? $folder_num.'/' : '').$file_name);
		}

		$actions = unserialize($variation["param"]);
		foreach ($actions as $action)
		{
			if($after_selectarea)
			{
				if($action["name"] == 'selectarea')
				{
					$after_selectarea = false;
				}
				continue;
			}
			switch($action["name"])
			{
				case 'resize':
					Image::resize($path, $action["width"], $action["height"], $variation["quality"], $action["max"]);
					break;

				case 'selectarea':
					if($handle)
					{
						return $action;
					}
					break;

				case 'crop':
					Image::crop($path, $action["width"], $action["height"], $variation["quality"], $action["vertical"], $action["vertical_px"], $action["horizontal"], $action["horizontal_px"]);
					break;

				case 'wb':
					Image::wb($path, $variation["quality"]);
					break;

				case 'watermark':
					Image::watermark($path, ABSOLUTE_PATH.USERFILES."/watermark/".$action["file"], $variation["quality"], $action["vertical"], $action["vertical_px"], $action["horizontal"], $action["horizontal_px"]);
					break;
			}
		}
	}

	/**
	 * Выбирает все размеры изображений
	 *
	 * @return void
	 */
	private function get_variations()
	{
		if(! isset($this->cache["images_variations"]))
		{
			$this->cache["images_variations"] = DB::query_fetch_key("SELECT * FROM {images_variations} WHERE trash='0' ORDER BY id ASC", "id");
		}
	}

	/**
	 * Валидация типа элементов
	 *
	 * @return void
	 */
	private function check_element_type($element_type)
	{
		if(! in_array($element_type, array('element', 'cat', 'brand')))
		{
			trigger_error($this->diafan->_('Некорректно задан тип элемента.'), E_USER_NOTICE);
		}
	}

	/**
	 * Определяет номер записи, которой соответствует хэш
	 *
	 * @param string $hash хэш
	 * @param string $module_name название модуля
	 * @return integer
	 */
	public function id_hash($hash, $module_name)
	{
		if(! $this->diafan->configmodules('hash_compare', 'images'))
		{
			return false;
		}
		return DB::query_result("SELECT id FROM {images} WHERE (module_name='%h' OR module_name='%h') AND image_id=0 AND hash='%s' AND trash='0' LIMIT 1", $module_name, ($this->trash . $module_name), $hash);
	}
}

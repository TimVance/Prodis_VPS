<?php
/**
 * Подключение для работы с прикрепленными файлами
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
 * Attachments_inc
 */
class Attachments_inc extends Diafan
{
	/**
	 * Отдает информацию о прикрепленных файлах
	 *
	 * @param integer $element_id номер элемента, к которому прикрепляется файл
	 * @param string $module_name название модуля
	 * @param integer $param_id номер параметра, к которому прикреплен файл
	 * @return array
	 */
	public function get($element_id, $module_name, $param_id = 0)
	{
		$this->prepare($element_id, $module_name, $param_id);
		if(! empty($this->cache["prepare"]))
		{
			$values = array();
			$where = array();
			foreach ($this->cache["prepare"] as $pr_module_name => $array)
			{
				$w = array();
				$values[] = $pr_module_name;
				foreach ($array as $pr_param_id => $arr)
				{
					$values[] = $pr_param_id;
					foreach ($arr as $pr_element_id => $a)
					{
						$this->cache["attachments"][$pr_module_name][$pr_param_id][$pr_element_id] = array();
						$values[] = $pr_element_id;
						$v_arr[] = '%d';
					}
					$w[] = " param_id=%d AND element_id".(count($arr) > 1 ? " IN (".implode(",", $v_arr).")" : "=%d");
				}
				$where[] = "module_name='%h' AND ".(count($w) > 1 ? "(".implode(" OR ", $w).")" : $w[0]);
			}

			$rows = DB::query_fetch_all("SELECT * FROM {attachments} WHERE ".implode(" OR ", $where), $values);
			foreach ($rows as $row)
			{
				if ($row["is_image"])
				{
					$row["link"] = BASE_PATH.USERFILES.'/'.$module_name.'/imgs/'.$row["name"];
					$row["link_preview"] = BASE_PATH.USERFILES.'/'.$module_name.'/imgs/small/'.$row["name"];
					list($row["width"], $row["height"]) = getimagesize(ABSOLUTE_PATH.USERFILES.'/'.$module_name.'/imgs/'.$row["name"]);
				}
				else
				{
					$row["link"] = BASE_PATH.'attachments/get/'.$row["id"]."/".$row["name"];
				}
				$row["size"] = $this->diafan->convert($row["size"]);
				$this->cache["attachments"][$row["module_name"]]['p'.$row["param_id"]]['e'.$row["element_id"]][] = $row;
			}
			unset($this->cache["prepare"]);
		}
		return $this->cache["attachments"][$module_name]['p'.$param_id]['e'.$element_id];
	}
	
	/**
	 * Запоминает данные элемента, которому нужно вывести информацию о прикрепленных файлах
	 *
	 * @param integer $element_id номер элемента, к которому прикрепляется файл
	 * @param string $module_name название модуля
	 * @param integer $param_id номер параметра, к которому прикреплен файл
	 * @return void
	 */
	public function prepare($element_id, $module_name, $param_id = 0)
	{
		if(isset($this->cache["attachments"][$module_name]['p'.$param_id]['e'.$element_id]))
		{
			return;
		}
		if(! isset($this->cache["prepare"][$module_name]['p'.$param_id]['e'.$element_id]))
		{
			$this->cache["prepare"][$module_name]['p'.$param_id]['e'.$element_id] = $element_id;
		}
	}

	/**
	 * Сохраняет добавленные файлы
	 * 
	 * @param integer $element_id номер элемента
	 * @param string $module_name название модуля
	 * @param array $config конфигурация
	 * @return boolean 
	 */
	public function save($element_id, $module_name, $config = array())
	{
		if(! empty($config["type"]) && $config["type"] == 'configmodules')
		{
			if(! $this->diafan->configmodules('attachments', $module_name, $config["site_id"]))
			{
				return false;
			}
			$config = array(
				'max_count_attachments' => $this->diafan->configmodules("max_count_attachments", $module_name, $config["site_id"]),
				"attachment_extensions" => $this->diafan->configmodules("attachment_extensions", $module_name, $config["site_id"]),
				"recognize_image" => $this->diafan->configmodules("recognize_image", $module_name, $config["site_id"]),
				"attachments_access_admin" => $this->diafan->configmodules("attachments_access_admin", $module_name, $config["site_id"]),
				"attach_big_width" => $this->diafan->configmodules("attach_big_width", $module_name, $config["site_id"]),
				"attach_big_height" => $this->diafan->configmodules("attach_big_height", $module_name, $config["site_id"]),
				"attach_big_quality" => $this->diafan->configmodules("attach_big_quality", $module_name, $config["site_id"]),
				"attach_medium_width" => $this->diafan->configmodules("attach_medium_width", $module_name, $config["site_id"]),
				"attach_medium_height" => $this->diafan->configmodules("attach_medium_height", $module_name, $config["site_id"]),
				"attach_medium_quality" => $this->diafan->configmodules("attach_medium_quality", $module_name, $config["site_id"]),
			);
		}
		$result = false;
		$name = (! empty($config["prefix"]) ? $config["prefix"] : '').'attachments'.(! empty($config["param_id"]) ? $config["param_id"] : '');

		if (! empty($_FILES[$name]))
		{
			foreach ($_FILES[$name]['tmp_name'] as $n => $dummy)
			{
				if (! empty($config["max_count_attachments"]) && $n > $config["max_count_attachments"])
					break;

				if($this->upload($_FILES[$name], $module_name, $element_id, $n, $config))
				{
					$result = true;
				}
			}
		}
		return $result;
	}

	/**
	 * Загружает файлы
	 *
	 * @param array $file загружаемый файл/файлы
	 * @param string $module_name название модуля
	 * @param integer $element_id номер элемента, к которому прикрепляется файл
	 * @param integer|boolean $n номер файла в массиве файлов, если предан массив
	 * @param array $config конфигурация
	 * @return boolean
	 */
	public function upload($file, $module_name, $element_id, $n = false, $config = array())
	{
		if(! empty($config["type"]) && $config["type"] == 'configmodules')
		{
			if(! $this->diafan->configmodules('attachments', $module_name, $config["site_id"]))
				return false; 

			$config = array(
				"attachment_extensions" => $this->diafan->configmodules("attachment_extensions", $module_name, $config["site_id"]),
				"recognize_image" => $this->diafan->configmodules("recognize_image", $module_name, $config["site_id"]),
				"attachments_access_admin" => $this->diafan->configmodules("attachments_access_admin", $module_name, $config["site_id"]),
				"attach_big_width" => $this->diafan->configmodules("attach_big_width", $module_name, $config["site_id"]),
				"attach_big_height" => $this->diafan->configmodules("attach_big_height", $module_name, $config["site_id"]),
				"attach_big_quality" => $this->diafan->configmodules("attach_big_quality", $module_name, $config["site_id"]),
				"attach_medium_width" => $this->diafan->configmodules("attach_medium_width", $module_name, $config["site_id"]),
				"attach_medium_height" => $this->diafan->configmodules("attach_medium_height", $module_name, $config["site_id"]),
				"attach_medium_quality" => $this->diafan->configmodules("attach_medium_quality", $module_name, $config["site_id"]),
			);
		}
		if ($n !== false)
		{
			$name     = $file['name'][$n];
			$tmp_name = $file['tmp_name'][$n];
			$type     = $file['type'][$n];
		}
		else
		{
			$name     = $file['name'];
			$tmp_name = $file['tmp_name'];
			$type     = $file['type'];
		}
		$size = filesize($tmp_name);
		if ($name == '')
			return false;

		$new_name = strtolower($this->diafan->translit($name));
		$extension = substr(strrchr($new_name, '.'), 1);
		$new_name = substr($new_name, 0, -(strlen($extension) + 1));
		if (strlen($new_name) + strlen($extension) > 49)
		{
			$new_name = substr($new_name, 0, 49 - strlen($extension));
		}
		if($config["attachment_extensions"])
		{
			$attachment_extensions_array = explode(',', str_replace(' ', '', strtolower($config["attachment_extensions"])));
			if (! in_array($extension, $attachment_extensions_array))
			{
				if(IS_ADMIN)
				{
					$err = $this->diafan->_('Вы не можете отправить файл %s. Доступны только следующие типы файлов:', $name).$config["attachment_extensions"].'. '.$this->diafan->_('Новые типы файлов добавляются в настройках модуля.');
				}
				else
				{
					$err = $this->diafan->_('Вы не можете отправить файл %s. Доступны только следующие типы файлов:', false, $name).$config["attachment_extensions"];
				}
				throw new Exception($err);
			}
		}

		if (! is_uploaded_file($tmp_name) && ! file_exists($tmp_name))
		{
			if(IS_ADMIN)
			{
				$err = $this->diafan->_('Извините, не удалось загрузить файл: %s', $name);
			}
			else
			{
				$err = $this->diafan->_('Извините, не удалось загрузить файл: %s', false, $name);
			}
			throw new Exception($err);
		}
		if ($config["recognize_image"] && in_array($extension, array('jpg', 'jpeg', 'jpe', 'gif', 'png')))
		{
			$is_image = 1;
		}
		else
		{
			$is_image = 0;
		}

		$id = DB::query("INSERT INTO {attachments} (name, module_name, element_id, extension, size, is_image, access_admin, param_id) VALUES ('%s', '%s', %d, '%s', %d, '%d', '%d', %d)",
		          $new_name.'.'.$extension, $module_name, $element_id, $type, $size, $is_image, $config["attachments_access_admin"], ! empty($config["param_id"]) ? $config["param_id"] : 0);
		if ($is_image)
		{
			$new_name = $id.'_'.$new_name;
			if(strlen($new_name.'.'.$extension) > 50)
			{
				$new_name = substr($new_name, 0, 49 - strlen($extension));
			}
			DB::query("UPDATE {attachments} SET name='%h' WHERE id=%d", $new_name.'.'.$extension, $id);
		}
		$new_name = $new_name.'.'.$extension;
		
		try
		{
			if ($is_image)
			{
				Custom::inc('includes/image.php');
				list($width, $height) = getimagesize($tmp_name);
				if ($width > $config["attach_big_width"] && $height > $config["attach_big_height"]
					&& ! Image::resize($tmp_name, $config["attach_big_width"], $config["attach_big_height"], $config["attach_big_quality"]))
				{
					if(IS_ADMIN)
					{
						$err = $this->diafan->_('Извините, не удалось загрузить файл: %s', $name);
					}
					else
					{
						$err = $this->diafan->_('Извините, не удалось загрузить файл: %s', false, $name);
					}
					throw new Exception($err);
				}
				File::copy_file($tmp_name, USERFILES."/".$module_name."/imgs/".$new_name);
	
				if ($width > $config["attach_medium_width"] && $height > $config["attach_medium_height"]
					&& ! Image::resize($tmp_name, $config["attach_medium_width"], $config["attach_medium_height"], $config["attach_medium_quality"]))
				{
					if(IS_ADMIN)
					{
						$err = $this->diafan->_('Извините, не удалось загрузить файл: %s', $name);
					}
					else
					{
						$err = $this->diafan->_('Извините, не удалось загрузить файл: %s', false, $name);
					}
					throw new Exception($err);
				}
				File::upload_file($tmp_name, USERFILES."/".$module_name."/imgs/small/".$new_name);
			}
			else
			{
				File::create_dir(USERFILES.'/'.$module_name.'/files', true);
				File::upload_file($tmp_name, USERFILES."/".$module_name."/files/".$id);
			}
		}
		catch(Exception $e)
		{
			if(IS_ADMIN || MOD_DEVELOPER)
			{
				throw new Exception($e->getMessage());
			}
			else
			{
				if(IS_ADMIN)
				{
					$err = $this->diafan->_('Извините, не удалось загрузить файл: %s', $name);
				}
				else
				{
					$err = $this->diafan->_('Извините, не удалось загрузить файл: %s', false, $name);
				}
				throw new Exception($err);
			}
		}
		return true;
	}

	/**
	 * Удаляет прикрепленные файлы/файл
	 *
	 * @param integer|array $element_ids номер одного или нескольких элементов
	 * @param string $module_name название модуля
	 * @param integer $attachment_id номер файла
	 * @param integer $param_id номер параметра, к которому прикреплен файл
	 * @return void
	 */
	public function delete($element_ids, $module_name, $attachment_id = 0, $param_id = 0)
	{
		if($element_ids)
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
		}

		if ($attachment_id)
		{
			$rows = DB::query_fetch_all("SELECT * FROM {attachments} WHERE id=%d AND element_id".$where." AND module_name='%h'".($param_id ? " AND param_id=%d" : ""), $attachment_id, $value, $module_name, $param_id);

			DB::query("DELETE FROM {attachments} WHERE id=%d AND element_id".$where." AND module_name='%h'".($param_id ? " AND param_id=%d" : ""), $attachment_id, $value, $module_name, $param_id);
		}
		else
		{
			$rows = DB::query_fetch_all("SELECT * FROM {attachments} WHERE element_id".$where." AND module_name='%h'".($param_id ? " AND param_id=%d" : ""), $value, $module_name, $param_id);

			DB::query("DELETE FROM {attachments} WHERE element_id".$where." AND module_name='%h'".($param_id ? " AND param_id=%d" : ""), $value, $module_name, $param_id);
		}
		foreach ($rows as $row)
		{
			if ($row["is_image"])
			{
				File::delete_file(USERFILES.'/'.$module_name.'/imgs/'.$row["name"]);
				File::delete_file(USERFILES.'/'.$module_name.'/imgs/small/'.$row["name"]);
			}
			else
			{
				File::delete_file(USERFILES.'/'.$module_name.'/files/'.$row["id"]);
			}
		}
	}

	/**
	 * Удаляет все прикрепленные файлы модуля
	 *
	 * @param string $module_name название модуля
	 * @return void
	 */
	public function delete_module($module_name)
	{
		DB::query("DELETE FROM {trash} WHERE module_name='attachments' AND element_id IN (SELECT id FROM {attachments} WHERE module_name='%s')", $module_name);
		$rows = DB::query_fetch_all("SELECT * FROM {attachments} WHERE module_name='%s'", $module_name);
		foreach ($rows as $row)
		{
			if ($row["is_image"])
			{
				File::delete_file(USERFILES.'/'.$module_name.'/imgs/'.$row["name"]);
				File::delete_file(USERFILES.'/'.$module_name.'/imgs/small/'.$row["name"]);
			}
			else
			{
				File::delete_file(USERFILES.'/'.$module_name.'/files/'.$row["id"]);
			}
		}
		DB::query("DELETE FROM {attachments} WHERE module_name='%s'", $module_name);
	}
}
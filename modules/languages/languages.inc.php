<?php
/**
 * Подключение модуля
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
 * Languages_inc
 */
class Languages_inc extends Model
{
	/**
	 * @var array подключенные языковые файлы модулей
	 */
	private $modules;

	/**
	 * @var array перевод интерфейса, разбитый по модулям
	 */
	private $languages_translate;

	/**
	 * @var boolean модуль прошел проверку является ли версия сайта русской и определен язык для перевода
	 */
	private $check;

	/**
	 * @var boolean это русская версия сайта
	 */
	public $is_ru = false;

	/**
	 * @var integer номер языка для перевода
	 */
	private $lang_id;

	/**
	 * Доступ к свойствам объекта
	 * 
	 * @return mixed
	 */
	public function __get($value)
	{
		if(! in_array($value, array('all', 'site', 'admin')))
			return false;

		if(! isset($this->cache["fields"][$value]))
		{
			$this->cache["fields"]['all'] = DB::query_fetch_all("SELECT * FROM {languages} ORDER BY base_site DESC, id ASC");
			foreach ($this->cache["fields"]['all'] as &$row)
			{
				if($row["base_admin"])
				{
					if(! empty($this->cache["fields"]['admin']))
					{
						$row["base_admin"] = false;
					}
					else
					{
						$this->cache["fields"]['admin'] = $row["id"];
					}
				}
				if($row["base_site"])
				{
					if(! empty($this->cache["fields"]['site']))
					{
						$row["base_site"] = false;
					}
					else
					{
						$this->cache["fields"]['site'] = $row["id"];
					}
				}
			}
			if(empty($this->cache["fields"]['site']))
			{
				$this->cache["fields"]['all'][0]["site"] = true;
				$this->cache["fields"]['site'] = $this->cache["fields"]['all'][0]["id"];
			}
			if(empty($this->cache["fields"]['all']))
			{
				$this->cache["fields"]['all'][0]["admin"] = true;
				$this->cache["fields"]['admin'] = $this->cache["fields"]['all'][0]["id"];
			}
		}
		return $this->cache["fields"][$value];
	}

	/**
	 * Отдает значение перевода строки
	 *
	 * @param string $name текст для перевода
	 * @param string $module_name модуль
	 * @param boolean $useradmin выдавать форму для редактирования
	 * @param array $args аргументы
	 * @return string
	 */
	public function get($name, $module_name = '', $useradmin = false, $args = array())
	{
		if (! $name)
		{
			return '';
		}
		$name = str_replace("\n", '', $name);

		/*if(! $module_name)
		{
			$module_name = $this->diafan->_site->module;
		}*/

		$type = IS_ADMIN ? 'admin' : 'site';
		if($module_name == 'useradmin')
		{
			$type = 'admin';
		}

		if(empty($this->check[$type]))
		{
			if($type == 'site' || ! $this->diafan->_users->id)
			{
				$this->lang_id = _LANG;
			}
			else
			{
				if(! empty($_SESSION["lang_id"]))
				{
					foreach ($this->diafan->_languages->all as $language)
					{
						if($type == 'admin' && $_SESSION["lang_id"] == $language["id"])
						{
							$this->lang_id = $language["id"];
						}
					}
				}
				if(! $this->lang_id)
				{
					foreach ($this->diafan->_languages->all as $language)
					{
						if($type == 'admin' && $language["base_admin"])
						{
							$this->lang_id = $language["id"];
						}
					}
				}
				if(! $this->lang_id)
				{
					$this->lang_id = $this->diafan->_languages->all[0]["id"];
				}
			}
			foreach ($this->diafan->_languages->all as $language)
			{
				if($language["id"] == $this->lang_id && in_array($language["shortname"], array('ru', 'rus')))
				{
					$this->is_ru = true;
				}
			}
			$this->check[$type] = true;
		}
		$value = '';

		if(! isset($this->languages_translate["common"]))
		{
			$this->languages_translate["common"] = array();
			$rs = DB::query_fetch_all("SELECT text, text_translate, module_name FROM {languages_translate} WHERE type='%s' AND lang_id=%d", $type, $this->lang_id, "text", "text_translate");
			foreach($rs as $r)
			{
				if(! $r["module_name"])
				{
					$r["module_name"] = 'common';
				}
				$this->languages_translate[$r["module_name"]][$r["text"]] = $r["text_translate"];
			}
		}

		$prepare_name = trim($name);

		if($module_name && isset($this->languages_translate[$module_name][$prepare_name]))
		{
			$value = $this->languages_translate[$module_name][$prepare_name];
		}
		elseif(isset($this->languages_translate["common"][$prepare_name]))
		{
			$value = $this->languages_translate["common"][$prepare_name];
		}
		else
		{
			if(! $this->is_ru)
			{
				$this->languages_translate[($module_name ? $module_name : "common")][$prepare_name] = '';
				DB::query("INSERT INTO {languages_translate} (text, text_translate, module_name, type, lang_id) VALUES ('%s', '', '%h', '%s', %d)", $prepare_name, $type == 'site' ? $module_name : '', $type, $this->lang_id);
			}
		}
		if(! $value)
		{
			$value = $name;
		}

		if(! empty($args))
		{
			$value = vsprintf($value, $args);
		}
		if ($useradmin)
		{
			$text = $this->diafan->_useradmin->get_lang($value, $name, $module_name);
		}
		else
		{
			$text = $value;
		}
		return $text;
	}

	/**
	 * Определяет язык версии административной панели для текущего пользователя
	 *
	 * @return string
	 */
	public function base_admin()
	{
		$lang = 'ru';
		foreach($this->all AS $l)
		{
			if(! empty($_SESSION["lang_id"]) && $l["id"] == $_SESSION["lang_id"] || empty($_SESSION["lang_id"]) && $l["base_admin"])
			{
				$lang = $l["shortname"];
			}
		}
		return $lang;
	}

	/**
	 * Импортирует файл перевода
	 *
	 * @param string $file_path путь до файла
	 * @param integer $lang_id ID языка, для которого загружается перевод
	 * @return void
	 */
	public function import($file_path, $lang_id)
	{
		$oldtranslates  = array();
		$rows = DB::query_fetch_all("SELECT * FROM {languages_translate} WHERE lang_id=%d", $lang_id);
		foreach ($rows as $row)
		{
			$oldtranslates[$row["type"]][trim($row["text"])] = $row;
		}

		$file = file_get_contents($file_path);

		$translates = explode("\n", $file);
		$module_name = '';
		$type = 'site';
		$original = '';
		foreach ($translates as $s)
		{
			if(strpos($s, 'module_name=') !== false)
			{
				$module_name = preg_match('/[^a-z_]+/', '', str_replace('module_name=', '', $s));
				continue;
			}
			if(strpos($s, 'type=') !== false)
			{
				$type = trim($s) == 'type=admin' ? 'admin' : 'site';
				continue;
			}
			if(! $original)
			{
				$original = $s;
			}
			else
			{
				$id = 0;
				if(! empty($oldtranslates[$type][$original]))
				{
						$o = $oldtranslates[$type][$original];
						if($o["module_name"] == $module_name || ! $o["text_translate"])
						{
							$id = $o["id"];
						}
				}
				if($id)
				{
					DB::query("UPDATE {languages_translate} SET text_translate='%s' WHERE id=%d", trim($s), $id);
				}
				else
				{
					DB::query("INSERT INTO {languages_translate} (lang_id, module_name, text, text_translate, type) VALUES (%d, '%h', '%s', '%s', '%s')", $lang_id, $module_name, trim($original), trim($s), $type);
				}
				$original = '';
			}
		}
	}
}
<?php
/**
 * Установка модулей
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

if(defined('IS_DEMO') && IS_DEMO)
{
	define('DEMO_PATH', ABSOLUTE_PATH.'userfls/demo/');
}
else
{
	define('DEMO_PATH', ABSOLUTE_PATH.USERFILES.'/demo/');
}

class Install extends Diafan
{
	/**
	 * @var boolean модуль является частью ядра
	 */
	public $is_core = false;

	/**
	 * @var string название текущего модуля
	 */
	public $module;

	/**
	 * @var string название
	 */
	public $title;

	/**
	 * @var string установленные/устанавливаемые модули
	 */
	public $install_modules;

	/**
	 * @var array идентификаторы языков сайта
	 */
	public $langs;

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array();

	/**
	 * @var array записи в таблице {modules}
	 */
	public $modules = array();

	/**
	 * @var array меню административной части
	 */
	public $admin = array();

	/**
	 * @var array страницы сайта
	 */
	public $site = array();

	/**
	 * @var array вставки
	 */
	public $inserts = array();

	/**
	 * @var array настройки
	 */
	public $config = array();

	/**
	 * @var array предустановленные данные
	 */
	public $sql = array();

	/**
	 * @var array демо-данные
	 */
	public $demo = array();

	/**
	 * @var array страницы сайта, к которым прикреплен модуль
	 */
	protected $site_ids;

	/**
	 * @var array внутренний кэш файла
	 */
	protected $cache;

	/**
	 * Устанавливаем модуль
	 *
	 * @param boolean $demo установить демо-данные
	 * @return void
	 */
	public function start($demo)
	{
		$this->diafan->set_time_limit();

		$this->action();

		if($demo && ! is_dir(ABSOLUTE_PATH.'userfls/demo'))
		{
			if(! file_exists(ABSOLUTE_PATH.'userfls/demo.zip'))
			{
				File::copy_file('http'.(IS_HTTPS ? "s" : '').'://www.diafan.ru/demo.zip', 'userfls/demo.zip');
			}
			if(! class_exists('ZipArchive'))
			{
				throw new Exception('На сервере не установлено расширение для распоковки ZIP-архивов. Распакуйте содержимое архива userfls/demo.zip в папку userfls/demo.');
			}
			$zip = new ZipArchive;
			if ($zip->open(ABSOLUTE_PATH.'userfls/demo.zip') === true)
			{
				$zip->extractTo(ABSOLUTE_PATH.'userfls/demo');
				$zip->close();
			}
		}

		if($demo && Custom::name() && file_exists(ABSOLUTE_PATH.'custom/'.Custom::name().'/demo.zip') && ! is_dir(ABSOLUTE_PATH.USERFILES.'/demo/custom/'.Custom::name()))
		{
			if(! class_exists('ZipArchive'))
			{
				throw new Exception('На сервере не установлено расширение для распоковки ZIP-архивов. Распакуйте содержимое архива userfls/demo.zip в папку userfls/demo.');
			}
			$zip = new ZipArchive;
			if ($zip->open(ABSOLUTE_PATH.'custom/'.Custom::name().'/demo.zip') === true)
			{
				for($i = 0; $i < $zip->numFiles; $i++)
				{
					$name = $zip->getNameIndex($i);
					if($name && substr($name, 0, 1) != '/')
					{
						$name = '/'.$name;
					}
					if(substr($name, -1) == '/')
					{
						$arr = explode('/', $name);
						array_pop($arr);
						$name = array_pop($arr);
						File::create_dir(USERFILES.'/demo/custom/'.Custom::name().'/'.$_POST["name"].($arr ? '/'.implode('/', $arr) : '').'/'.$name);
					}
					else
					{
						$extension = substr(strrchr($name, '.'), 1);
						if (in_array($extension, array('jpg', 'jpeg', 'gif','png', 'doc', 'pdf', 'txt')))
						{
							File::save_file($zip->getFromName($zip->getNameIndex($i)), USERFILES.'/demo/custom/'.Custom::name().'/'.$name);
						}
					}
				}
				$zip->close();
			}
		}
		$this->modules($this->modules);
		$this->admin($this->admin);

		if(Custom::exists('modules/'.$this->module.'/'.$this->module.'.install.demo.php'))
		{
			Custom::inc('modules/'.$this->module.'/'.$this->module.'.install.demo.php');
			$name = ucfirst($this->module.'_install_demo');
			$class = new $name($this->diafan);
			foreach($this as $k => $v)
			{
				if($k != 'demo')
				{
					$class->$k = $v;
				}
			}
			if($this->module == 'site')
			{
				$this->config($this->config);
				if(isset($class->demo["config"]))
				{
					$this->config($class->demo["config"]);
				}
				$this->site($this->sql["site"]);
			}
			// если в демо-данные есть страницы сайта, заменяем устанавливаемые по умолчанию
			if(isset($class->demo["site"]))
			{
				$this->site($class->demo["site"]);
			}
			else
			{
				$this->site($this->site);
			}
			if($this->module != 'site')
			{
				$this->config($this->config);
			}
			$this->inserts($this->inserts);
			foreach($class->demo as $table => $array)
			{
				switch($table)
				{
					case 'config':
						$this->config($array);
						break;

					case 'inserts':
						$this->inserts($array);
						break;

					case 'site':
						break;

					default:
						$this->sql_rows($table, $array);
						break;
				}
			}
		}
		else
		{
			$this->site($this->site);
			$this->config($this->config);
			$this->inserts($this->inserts);
			$this->sql($this->sql);
			if($demo)
			{
				$this->demo();
			}
		}
	}

	/**
	 * Выполняет действия при установке модуля
	 *
	 * @return void
	 */
	protected function action(){}

	/**
	 * Выполняет действия при установке модуля после основной установки
	 *
	 * @return void
	 */
	public function action_post(){}

	/**
	 * Добавляет таблицы
	 *
	 * @param array $array таблицы
	 * @return void
	 */
	public function tables($array = array())
	{
		if(! $array)
		{
			$array = $this->tables;
		}
		foreach ($array as $row)
		{

			DB::query("DROP TABLE IF EXISTS ".DB_PREFIX.$row["name"]);

			$query = "CREATE TABLE ".DB_PREFIX.$row["name"]." (";
			foreach ($row["fields"] as $field)
			{
				$comment = (! empty($field["comment"]) ? " COMMENT '".str_replace("'", "\\'", $field["comment"])."'" : '');
				if(empty($field["multilang"]))
				{
					$query .= "\n".'`'.$field["name"].'` '.$field["type"].$comment.',';
				}
				else
				{
					foreach ($this->langs as $lang_id)
					{
						$query .= "\n".'`'.$field["name"].$lang_id.'` '.$field["type"].$comment.',';
					}
				}
			}
			if(! empty($row["keys"]))
			{
				$query .= "\n".implode(',', $row["keys"]);
			}
			$query .= "\n) CHARSET=utf8";
			if(! empty($row["comment"]))
			{
				$query .= " COMMENT '".str_replace("'", "\\'", $row["comment"])."'";
			}
			DB::query($query);
		}
	}

	/**
	 * Добавляет запись в таблицу {modules}
	 *
	 * @param array $array массив с данными
	 * @return void
	 */
	public function modules($array = array())
	{
		if(! $array)
		{
			$array = $this->modules;
		}
		$module = $this->is_core ? 'core' : $this->module;
		foreach ($array as $row)
		{
			if(empty($row["title"]))
			{
				$row["title"] = $this->title;
			}
			DB::query("INSERT INTO {modules} (name, module_name, title, admin, site, site_page) VALUES ('%h', '%h', '%h', '%d', '%d', '%d')",
			$row["name"], $module, $row["title"],
			(! empty($row["admin"]) ? 1 : 0), (! empty($row["site"]) ? 1 : 0), (! empty($row["site_page"]) ? 1 : 0));
		}
	}

	/**
	 * Добавляет записи о модуле в таблицу {admin} - страницы админки
	 *
	 * @param array $array массив с данными
	 * @param integer $parent_id номер страницы админки - родителя
	 * @param array $parent значения полей для родителя
	 * @return void
	 */
	public function admin($array = array(), $parent_id = 0, $parent = array())
	{
		if(! $array)
		{
			$array = $this->admin;
		}
		$sort = 1;
		foreach ($array as $values)
		{

			if(empty($values["sort"]))
			{
				$values["sort"] = $sort;
				$sort++;
			}
			if(empty($values["docs"]))
			{
				if(! empty($parent["docs"]))
				{
					$values["docs"] = $parent["docs"];
				}
				else
				{
					$values["docs"] = '';
				}
			}
			if(empty($values["group_id"]))
			{
				if(! empty($parent["group_id"]))
				{
					$values["group_id"] = $parent["group_id"];
				}
				else
				{
					$values["group_id"] = '1';
				}
			}
			if(empty($values['act']))
			{
				$values['act'] = 0;
			}
			else
			{
				$values['act'] = 1;
			}
			if(empty($values['add']))
			{
				$values['add'] = 0;
			}
			else
			{
				$values['add'] = 1;
			}
			if(empty($values['add_name']))
			{
				$values['add_name'] = '';
			}
			if(empty($values['icon_name']))
			{
				if(! empty($parent['icon_name']))
				{
					$values['icon_name'] = $parent['icon_name'];
				}
				else
				{
					$values['icon_name'] = '';
				}
			}

			$values['count_children'] = (! empty($values['children']) ? count($values['children']) : 0);

			$last_id = 0;
			if(empty($parent_id))
			{
				$last_id = DB::query_result("SELECT id FROM {admin} WHERE BINARY rewrite='%s' AND parent_id=0", $values['rewrite']);
			}
			if(! $last_id)
			{
				$last_id = DB::query("INSERT INTO {admin} (parent_id, group_id, name, rewrite, act, sort, docs, count_children, `add`, `add_name`, icon_name) VALUES (%d, %d, '%s', '%s', '%s', %d, '%s', %d, '%d', '%s', '%s')", $parent_id, $values['group_id'], $values['name'], $values['rewrite'], $values['act'], $values['sort'], $values["docs"], $values['count_children'], $values["add"], $values["add_name"], $values["icon_name"]);
			}

			if (! empty($parent_id))
			{
				DB::query("INSERT INTO {admin_parents} (element_id, parent_id) VALUES (%d, %d)", $last_id, $parent_id);
			}


			if(! empty($values['children']))
			{
				$this->admin($values['children'], $last_id, $values);
			}
		}
	}

	/**
	 * Добавляет страницы сайта
	 *
	 * @param array $rows массив с данными
	 * @return void
	 */
	public function site($rows = array())
	{
		if(! $rows)
		{
			$rows = $this->site;
		}
		foreach ($rows as $row)
		{
			if(! empty($row["parent_id"]) && empty($this->cache["site"][$row["parent_id"]]))
			{
				$row["parent_id"] = DB::query_result("SELECT id FROM {site} WHERE trash='0' AND id=%d", $row["parent_id"]);
			}
			$update = false;
			$name = array('timeedit');
			$mask = array('%d');
			$value = array(time());
			if(! empty($row["id"]))
			{
				if(is_string($row["id"]))
				{
					if(empty($this->site_ids[$row["id"]]))
						continue;

					$row["id"] = $this->site_ids[$row["id"]];
					$update = true;
				}
				else
				{
					if(DB::query_result("SELECT id FROM {site} WHERE id=%d", $row["id"]))
					{
						$update = true;
					}
					else
					{
						$name[] = 'id';
						$mask[] = '%d';
						$value[] = $row["id"];
					}
				}
			}
			if(! empty($row["sort"]))
			{
				$name[] = 'sort';
				$mask[] = '%d';
				$value[] = $row["sort"];
			}
			elseif(! empty($row["id"]))
			{
				$name[] = 'sort';
				$mask[] = '%d';
				$value[] = $row["id"];
				$row["sort"] = $row["id"];
			}
			$name[] = 'parent_id';
			$mask[] = '%d';
			if(! empty($row["parent_id"]))
			{
				$value[] = $row["parent_id"];
			}
			else
			{
				$value[] = 0;
			}
			if(! empty($row["children"]))
			{
				$name[] = 'count_children';
				$mask[] = $this->get_count_children($row);
			}
			if(! empty($row["module_name"]))
			{
				$name[] = 'module_name';
				$mask[] = "'%h'";
				$value[] = $row["module_name"];
			}
			if(! empty($row["map_no_show"]))
			{
				$name[] = 'map_no_show';
				$mask[] = "'1'";
			}
			if(! empty($row["title_no_show"]))
			{
				$name[] = 'title_no_show';
				$mask[] = "'1'";
			}
			if(! empty($row["noindex"]))
			{
				$name[] = 'noindex';
				$mask[] = "'1'";
			}
			if(! empty($row["search_no_show"]))
			{
				$name[] = 'search_no_show';
				$mask[] = "'1'";
			}
			if(! empty($row["theme"]))
			{
				$name[] = 'theme';
				$mask[] = "'%h'";
				$value[] = $row["theme"];
			}
			foreach($this->langs as $i => $l)
			{
				if(isset($row["name"]))
				{
						$name[] = 'act'.$l;
						$mask[] = "'".(! empty($row["name"][$i]) ? '1' : '0')."'";
				}
				if(! empty($row["name"][$i]))
				{
					$name[] = 'name'.$l;
					$mask[] = "'%h'";
					$value[] = $row["name"][$i];
				}
				if(! empty($row["text"][$i]))
				{
					$name[] = 'text'.$l;
					$mask[] = "'%s'";
					$value[] = $row["text"][$i];
				}
			}
			if($update)
			{
				$set = '';
				foreach($name as $i => $n)
				{
					$set .= ($set ? ', ' : '').'`'.$n.'`='.$mask[$i];
				}
				$value[] = $row["id"];
				DB::query("UPDATE {site} SET ".$set." WHERE id=%d", $value);
				DB::query("DELETE FROM {site_parents} WHERE element_id=%d", $row["id"]);
			}
			else
			{
				$row["id"] = DB::query("INSERT INTO {site} (`".implode("`,`", $name)."`) VALUES (".implode(",", $mask).")", $value);
				if(empty($row["sort"]))
				{
					$row["sort"] = $row["id"];
					DB::query("UPDATE {site} SET sort=%d WHERE id=%d", $row["sort"], $row["id"]);
				}
			}
			if(! empty($row["module_name"]))
			{
				$this->site_ids[$row["module_name"]] = $row["id"];
			}

			if(! empty($row["menu"]))
			{
				$this->sql_set_menu($row, 'site');
			}

			if(! empty($row["images"]))
			{
				$this->sql_set_images($row, 'element', 'site');
			}

			if(isset($row["rewrite"]))
			{
				DB::query("INSERT INTO {rewrite} (rewrite, module_name, element_type, element_id) VALUES ('%h', 'site', 'element', %d)", $row["rewrite"], $row["id"]);
			}
			$this->cache["site"][$row["id"]] = $row;
			if(! empty($row["children"]))
			{
				foreach($row["children"] as $i => $r)
				{
					$row["children"][$i]["parent_id"] = $row["id"];
				}
				$this->site($row["children"]);
			}
			if(! empty($row["parent_id"]))
			{
				DB::query("UPDATE {site} SET count_children=count_children+1 WHERE id=%d", $row["parent_id"]);
				$this->sql_set_parent_id($row, 'site', 'site');
			}
		}
	}

	/**
	 * Добавляет запись в таблицу {config}
	 *
	 * @param array $array массив с данными
	 * @return void
	 */
	public function config($array = array())
	{
		if(! $array)
		{
			$array = $this->config;
		}
		foreach ($array as $row)
		{
			if(empty($row["module_name"]))
			{
				$row["module_name"] = $this->module;
			}
			if(empty($row["site_id"]))
			{
				$row["site_id"] = 0;
			}

			if(is_array($row["value"]))
			{
				foreach ($row["value"] as $i => $value)
				{
					if(empty($this->langs[$i]))
						continue;

					$this->diafan->configmodules($row["name"], $row["module_name"], $row["site_id"], ($i ? $this->langs[$i] : 0), $value);
				}
			}
			else
			{
				$this->diafan->configmodules($row["name"], $row["module_name"], $row["site_id"], 0, $row["value"]);
			}
		}
	}

	/**
	 * Добавляет запись в таблицу {inserts}
	 *
	 * @param array $array массив с данными
	 * @return void
	 */
	public function inserts($array = array())
	{
		if(! $array)
		{
			$array = $this->inserts;
		}
		foreach ($array as $row)
		{
			$id = DB::query("INSERT INTO {inserts} (act"._LANG.", name, prefix, tag, text, timeedit) VALUES ('1', '%s', '%h', '%s', '%s', %d)",
			(! empty($row["name"]) ? $row["name"] : $this->title),
			(! empty($row["prefix"]) ? $row["prefix"] : 'replace'),
			(! empty($row["tag"]) ? $row["tag"] : ''),
			(! empty($row["text"]) ? $row["text"] : ''),
			time()
			);
			if(! empty($row["site_rel"]) && is_array($row["site_rel"]))
			{
				foreach($row["site_rel"] as $r)
				{
					DB::query("INSERT INTO {inserts_site_rel} (element_id, site_id) VALUES (%d, %d)", $id, $r);
				}
			}
			else
			{
				DB::query("INSERT INTO {inserts_site_rel} (element_id, site_id) VALUES (%d, 0)", $id);
			}
		}
	}

	/**
	 * Выполняет SQL-запросы
	 *
	 * @param array $array массив с данными
	 * @return void
	 */
	public function sql($array = array())
	{
		if(! $array)
		{
			$array = $this->sql;
		}
		foreach($array as $table => $arr)
		{
			$this->sql_rows($table, $arr);
		}
	}

	/**
	 * Установка demo-данных
	 *
	 * @return void
	 */
	public function demo()
	{
		foreach($this->demo as $table => $array)
		{
			switch($table)
			{
				case 'config':
					$this->config($array);
					break;

				case 'site':
					$this->site($array);
					break;

					case 'inserts':
						$this->inserts($array);
						break;

				default:
					$this->sql_rows($table, $array);
					break;
			}
		}
	}

	/**
	 * Установка demo-данные в таблицу
	 *
	 * @param array $array  массив с данными
	 * @return void
	 */
	private function sql_rows($table, $rows)
	{
		if($table == $this->module.'_category')
		{
			$element_type = 'cat';
		}
		elseif(preg_match('/_param$/', $table))
		{
			$element_type = 'param';
		}
		elseif($table == $this->module)
		{
			$element_type = 'element';
		}
		else
		{
			$element_type = str_replace($this->module.'_', '', $table);
		}

		foreach($rows as $row)
		{
			if(! empty($row["id"]))
			{
				$ids[] = $row["id"];
			}
		}
		if(isset($ids))
		{
			$this->sql_delete($table, $ids, $element_type);
		}
		$created = time() - 86400 * rand(30, 40);

		foreach($rows as $row)
		{
			if(! empty($row["module_name"]) &&  ! in_array($row["module_name"], $this->install_modules) && $row["module_name"] != 'site')
				continue;

			if($this->is_field($table, "sort") && empty($row["sort"]))
			{
				if(! isset($sort))
				{
					$sort = 1;
				}
				$row['sort'] = $sort;
				$sort++;
			}
			if($this->is_field($table, "timeedit") && empty($row["timeedit"]))
			{
				$row['timeedit'] = time();
			}
			if($this->is_field($table, "created") && empty($row["created"]))
			{
				$row['created'] = $created;
				$created = rand($created, time());
			}
			if(empty($row["site_id"]) && ! empty($this->site_ids[$this->module]))
			{
				$row['site_id'] = $this->site_ids[$this->module];
			}
			if(! empty($row["children"]))
			{
				$row["count_children"] = $this->get_count_children($row);
			}
			if($this->is_field($table, "act") && ! isset($row["act"]))
			{
				foreach($this->langs as $i => $l)
				{
					$row["act"][] = (! isset($row["name"]) || ! empty($row["name"][$i]) ? 1 : 0);
				}
			}
			$value = array($table);
			$name = array();
			$mask = array();
			foreach($row as $k => $v)
			{
				if(empty($v))
					continue;

				if($this->is_field($table, $k))
				{
					if($this->is_multilang_field($table, $k))
					{
						foreach($this->langs as $i => $l)
						{
							if(! empty($v[$i]))
							{
								$name[] = $k.$l;
								$mask[] = "'%s'";
								$value[] = str_replace('BASE_PATH', BASE_PATH, (is_array($v) ? $v[$i] : $v));
							}
						}
					}
					else
					{

						if($v === 'now')
						{
							$v = time();
						}
						$name[] = $k;
						$mask[] = "'%s'";
						$value[] = str_replace('BASE_PATH', BASE_PATH, (is_array($v) ? $v[0] : $v));
					}
				}
			}
			$row["id"] = DB::query("INSERT INTO {%s} (`".implode("`,`", $name)."`) VALUES (".implode(",", $mask).")", $value);

			$this->sql_rel($row, $table.'_category_rel', 'cat_id');
			$this->sql_rel($row, $table.'_site_rel', 'site_id');

			foreach($row as $k => $v)
			{
				if(is_callable(array($this, "action_".$table."_".$k)))
				{
					call_user_func_array(array(&$this, "action_".$table."_".$k), array($row));
					continue;
				}
				elseif(is_callable(array($this, "sql_set_".$k)))
				{
					call_user_func_array(array(&$this, "sql_set_".$k), array($row, $element_type, $table));
					continue;
				}
				elseif(! $this->is_field($table, $k) && is_array($v) && $this->is_table($table."_".$k))
				{
					$field = str_replace($this->module.'_', '', $table).'_id';
					if(! $this->is_field($table."_".$k, $field))
					{
						$t = explode('_', $table);
						$field = $t[count($t) - 1].'_id';
						if(! $this->is_field($table."_".$k, $field))
						{
							$field = '';
							if($this->is_field($table."_".$k, 'element_id'))
							{
								$field = 'element_id';
							}
							elseif($this->is_field($table."_".$k, 'good_id'))
							{
								$field = 'good_id';
							}
						}
					}
					if($field)
					{
						foreach($v as &$vl)
						{
							$vl[$field] = $row["id"];
							if(! empty($vl["children"]))
							{
								foreach($vl["children"] as &$chv)
								{
									$chv[$field] = $row["id"];
								}
							}
						}
						$this->sql_rows($table."_".$k, $v);
					}
				}
			}
			$this->cache[$table][$row["id"]] = $row;

			if(! empty($row["children"]))
			{
				foreach($row["children"] as $i => $r)
				{
					$row["children"][$i]["parent_id"] = $row["id"];
				}
				$this->sql_rows($table, $row["children"]);
			}
		}
	}

	/**
	 * Делает записи о связях с категорией или разделом
	 *
	 * @param array $row информаци о текущем элементе
	 * @param string $table название таблицы
	 * @param string $field поле
	 * @return void
	 */
	private function sql_rel($row, $table, $field)
	{
		if($this->is_table($table))
		{
			if(! empty($row[$field]))
			{
				if(is_array($row[$field]))
				{
					foreach($row[$field] as $id)
					{
						DB::query("INSERT INTO {%s} (element_id, %s) VALUES (%d, %d)", $table, $field, $row["id"], $id);
					}
				}
				else
				{
					DB::query("INSERT INTO {%s} (element_id, %s) VALUES (%d, %d)", $table, $field, $row["id"], $row[$field]);
				}
			}
			else
			{
				DB::query("INSERT INTO {%s} (element_id) VALUES (%d)", $table, $row["id"]);
			}
		}
	}

	/**
	 * Делает записи о в таблицу {table_parents}
	 *
	 * @param array $row информаци о текущем элементе
	 * @param string $element_type тип элемента
	 * @param string $table название таблицы
	 * @return void
	 */
	private function sql_set_parent_id($row, $element_type, $table)
	{
		$p = $row["parent_id"];
		while($p)
		{
			DB::query("INSERT INTO {%s_parents} (element_id, parent_id) VALUES (%d, %d)", $table, $row["id"], $p);
			$p = (! empty($this->cache[$table][$p]["parent_id"]) ? $this->cache[$table][$p]["parent_id"] : 0);
		}
	}

	/**
	 * Делает записи в таблице {rewrite}
	 *
	 * @param array $row информаци о текущем элементе
	 * @param string $element_type тип элемента
	 * @param string $table название таблицы
	 * @return void
	 */
	private function sql_set_rewrite($row, $element_type, $table)
	{
		if($this->is_field($table, 'rewrite'))
		{
			return;
		}
		DB::query("INSERT INTO {rewrite} (rewrite, module_name, element_type, element_id) VALUES ('%h', '%s', '%s', %d)", $row["rewrite"], $this->module, $element_type, $row["id"]);
	}

	/**
	 * Копирует прикрепленные файлы
	 *
	 * @param array $row информаци о текущем элементе
	 * @param string $element_typе тип элемента
	 * @param string $table название таблицы
	 * @return void
	 */
	private function sql_set_copy($row, $element_type, $table)
	{
		foreach($row["copy"] as $c)
		{
			if(Custom::name() && file_exists(ABSOLUTE_PATH.USERFILES.'/demo/custom/'.Custom::name().'/'.$c))
			{
				$path = USERFILES.'/demo/custom/'.Custom::name().'/'.$c;
			}
			else
			{
				$path = 'userfls/demo/'.$c;
			}
			File::copy_file($path, USERFILES.'/'.$c);
		}
	}

	/**
	 * Делает записи о доп. характеристиках
	 *
	 * @param array $row информаци о текущем элементе
	 * @param string $element_type тип элемента
	 * @param string $table название таблицы
	 * @return void
	 */
	private function sql_set_param($row, $element_type, $table)
	{
		if($this->is_table($table.'_param_element'))
		{
			foreach($row["param"] as $k => $v)
			{
				if($this->cache[$table."_param"][$k]["type"] == "multiple" && is_array($v))
				{
					foreach($v as &$vl)
					{
						$name = array('param_id', 'element_id');
						if($this->is_multilang_field($table.'_param_element', 'value'))
						{
							$name[] = 'value'.$this->langs[0];
						}
						else
						{
							$name[] = "value";
						}
						$mask = array('%d', '%d', '%d');
						$value = array($table, $k, $row["id"], $vl);
						DB::query("INSERT INTO {%s_param_element} (`".implode("`,`", $name)."`) VALUES (".implode(",", $mask).")", $value);
					}
				}
				else
				{
					$name = array('param_id', 'element_id');
					$mask = array('%d', '%d');
					$value = array($table, $k, $row["id"]);
					if($this->is_multilang_field($table."_param_element", "value"))
					{
						if(! is_array($v))
						{
							$v = array($v);
						}
						foreach($this->langs as $i => $l)
						{
							if(! empty($v[$i]))
							{
								$name[] = 'value'.$l;
								$mask[] = "'%s'";
								$value[] = $v[$i];
							}
						}
					}
					else
					{
						$name[] = 'value';
						$mask[] = "'%s'";
						$value[] = $v;
					}
					DB::query("INSERT INTO {%s_param_element} (`".implode("`,`", $name)."`) VALUES (".implode(",", $mask).")", $value);
				}
			}
		}
		elseif($this->is_table($table.'_param'))
		{
			$field = str_replace($this->module.'_', '', $table).'_id';
			if(! $this->is_field($table."_param", $field))
			{
				$field = '';
				if($this->is_field($table."_param", 'element_id'))
				{
					$field = 'element_id';
				}
				elseif($this->is_field($table."_param", 'good_id'))
				{
					$field = 'good_id';
				}
			}
			$field_value = '';
			if($this->is_field($table."_param", 'value'))
			{
				$field_value = 'value';
			}
			elseif($this->is_field($table."_param", 'param_value'))
			{
				$field_value = 'param_value';
			}
			if($field && $field_value)
			{
				foreach($row["param"] as $k => $v)
				{
					DB::query("INSERT INTO {%s_param} (param_id, %s, %s) VALUES (%d, %d, %d)", $table, $field,$field_value, $k, $row["id"], $v);
				}
			}
		}
	}

	/**
	 * Делает записи о связанных элементах
	 *
	 * @param array $row информаци о текущем элементе
	 * @param string $element_type тип элемента
	 * @param string $table таблица в базе данных
	 * @return void
	 */
	private function sql_set_rel($row, $element_type, $table)
	{
		if(! is_array($row["rel"]))
		{
			$row["rel"] = array($row["rel"]);
		}
		foreach($row["rel"] as $rel)
		{
			DB::query("INSERT INTO {%s_rel} (element_id, rel_element_id) VALUES (%d, %d)", $table, $row["id"], $rel);
		}
	}

	/**
	 * Делает записи о изображениях
	 *
	 * @param array $row информаци о текущем элементе
	 * @param string $element_type тип элемента
	 * @return void
	 */
	private function sql_set_images($row, $element_type, $table)
	{
		if($table == 'site')
		{
			$module = $table;
		}
		else
		{
			$module = $this->module;
		}
		Custom::inc("includes/image.php");
		if(is_array($row["name"]))
		{
			$name = $row["name"][0];
		}
		else
		{
			$name = $row["name"];
		}
		$name = preg_replace('/[^a-z0-9]+/', '', strtolower(substr($this->diafan->translit($name), 0, 30)));
		if(! is_array($row["images"]))
		{
			$row["images"] = array($row["images"]);
		}
		foreach($row["images"] as $r)
		{
			if(Custom::name() && file_exists(ABSOLUTE_PATH.USERFILES.'/demo/custom/'.Custom::name().'/original/'.$r))
			{
				$path = ABSOLUTE_PATH.USERFILES.'/demo/custom/'.Custom::name().'/original/'.$r;
			}
			else
			{
				$path = ABSOLUTE_PATH.'userfls/demo/original/'.$r;
			}
			$GLOBALS["image_id"] = 0;
			$this->diafan->_images->upload($row["id"], $module, $element_type, ($row["site_id"] ? $row["site_id"] : 0), $path, $name);
			$this->cache["images"][$r] = $GLOBALS["image_id"];
		}
	}

	/**
	 * Делает записи о прикрепленных файлах
	 *
	 * @param array $row информаци о текущем элементе
	 * @return void
	 */
	private function sql_set_attachments($row)
	{
		File::create_dir(USERFILES.'/'.$this->module.'/files', true);
		foreach($row["attachments"] as $r)
		{
			$size = 0;
			if(! empty($r["content"]))
			{
				File::save_file($r["content"], USERFILES.'/'.$this->module.'/files/upl');
				$size = filesize(ABSOLUTE_PATH.USERFILES.'/'.$this->module.'/files/upl');
			}
			elseif(! empty($r["is_image"]))
			{
				Custom::inc("includes/image.php");
				if(Custom::name() && file_exists(ABSOLUTE_PATH.USERFILES.'/demo/custom/'.Custom::name().'/attachments/'.$r["name"]))
				{
					$path = USERFILES.'/demo/custom/'.Custom::name().'/attachments/'.$r["name"];
				}
				else
				{
					$path = 'userfls/demo/attachments/'.$r["name"];
				}
				File::copy_file(ABSOLUTE_PATH.$path, USERFILES.'/'.$this->module.'/imgs/'.$r["name"]);
				File::copy_file(ABSOLUTE_PATH.$path, USERFILES.'/'.$this->module.'/imgs/small/'.$r["name"]);
				Image::resize(ABSOLUTE_PATH.USERFILES.'/'.$this->module.'/imgs/small/'.$r["name"], 50, 50, 70);
				$size = filesize(ABSOLUTE_PATH.USERFILES.'/'.$this->module.'/imgs/'.$r["name"]);
			}
			else
			{
				if(Custom::name() && file_exists(ABSOLUTE_PATH.USERFILES.'/demo/custom/'.Custom::name().'/attachments/'.$r["name"]))
				{
					$path = USERFILES.'/demo/custom/'.Custom::name().'/attachments/'.$r["name"];
				}
				else
				{
					$path = 'userfls/demo/attachments/'.$r["name"];
				}
				File::copy_file(ABSOLUTE_PATH.$path, USERFILES.'/'.$this->module.'/files/upl');
				$size = filesize(ABSOLUTE_PATH.USERFILES.'/'.$this->module.'/files/upl');
			}
			$att_id = DB::query("INSERT INTO {attachments} (name, module_name, element_id, extension, size, is_image)"
			." VALUES ('%h', '%s', %d, '%h', %d, '%d')", $r["name"], $this->module, $row["id"], $r["extension"],
			$size, (! empty($r["is_image"]) ? 1 : 0));
			if(empty($r["is_image"]))
			{
				File::rename_file($att_id, 'upl', USERFILES.'/'.$this->module.'/files');
			}
		}
	}

	/**
	 * Делает записи о пунктах меню
	 *
	 * @param array $row информаци о текущем элементе
	 * @param string $element_typе тип элемента
	 * @return void
	 */
	private function sql_set_menu($row, $element_type)
	{
		if($element_type == 'site')
		{
			$module_name = 'site';
			$element_type = 'element';
		}
		else
		{
			$module_name = $this->module;
		}
		if(! is_array($row["menu"]))
		{
			$row["menu"] = array($row["menu"]);
		}
		foreach($row["menu"] as $menu)
		{
			$image = '';
			if(is_array($menu))
			{
				$image = $menu["image"];
				$menu = $menu["cat_id"];
			}
			$parent_menu = 0;
			if(! empty($row["parent_id"]))
			{
				$parent_menu = DB::query_result("SELECT id FROM {menu} WHERE element_id=%d AND element_type='%s' AND cat_id=%d LIMIT 1", $row["parent_id"], $element_type, $menu);
			}
			if(! $parent_menu && ! empty($row["site_id"]))
			{
				$parent_menu = DB::query_result("SELECT id FROM {menu} WHERE element_id=%d AND module_name='site' AND element_type='element' AND cat_id=%d LIMIT 1", $row["site_id"], $menu);
			}
			$name = array('module_name', 'element_id', 'element_type', 'cat_id', 'parent_id');
			$mask = array("'%s'", '%d', "'%s'", '%d', '%d');
			$value = array($module_name, $row["id"], $element_type, $menu, $parent_menu);
			if(! empty($row["sort"]))
			{
				$name[] = 'sort';
				$mask[] = "%d";
				$value[] = $row["sort"];
			}
			foreach($this->langs as $i => $l)
			{
				$name[] = 'act'.$l;
				$mask[] = "'".(! isset($row["name"]) || ! empty($row["name"][$i]) ? 1 : 0)."'";

				if(! empty($row["name"]))
				{
					if(! is_array($row["name"]))
					{
						$row["name"] = array($row["name"]);
					}
					if(! empty($row["name"][$i]))
					{
						$name[] = 'name'.$l;
						$mask[] = "'%s'";
						$value[] = $row["name"][$i];
					}
				}
			}
			$menu_id = DB::query("INSERT INTO {menu} (`".implode("`,`", $name)."`) VALUES (".implode(",", $mask).")", $value);
			if($image)
			{
				if(Custom::name() && file_exists(ABSOLUTE_PATH.USERFILES.'/demo/custom/'.Custom::name().'/original/'.$image))
				{
					$path = ABSOLUTE_PATH.USERFILES.'/demo/custom/'.Custom::name().'/original/'.$image;
				}
				else
				{
					$path = ABSOLUTE_PATH.'userfls/demo/original/'.$image;
				}
				$this->diafan->_images->upload($menu_id, 'menu', 'element', 0, $path, $image);
			}
			if($parent_menu)
			{
				DB::query("INSERT INTO {menu_parents} (`element_id`, `parent_id`) VALUES (%d, %d)", $menu_id, $parent_menu);
				DB::query("UPDATE {menu} SET count_children=count_children+1 WHERE id=%d", $parent_menu);
			}
		}
	}

	/**
	 * Делает записи о скрытии визуального редактора для некоторых полей
	 *
	 * @param array $row информаци о текущем элементе
	 * @param string $element_typе тип элемента
	 * @param string $table название таблицы
	 * @return void
	 */
	private function sql_set_hide_htmleditor($row, $element_type, $table)
	{
		if(! empty($row["hide_htmleditor"]))
		{
			$this->diafan->configmodules("hide_".$table."_".$row["id"], "htmleditor", false, false, $row["hide_htmleditor"]);
		}
	}

	/**
	 * Подсчитывает количество детей
	 *
	 * @param $array данные об элементе
	 * @return integer
	 */
	private function get_count_children($row)
	{
		if(empty($row["children"]))
		{
			return 0;
		}
		$count = count($row["children"]);
		foreach($row["children"] as $ch)
		{
			$count += $this->get_count_children($ch);
		}
		return $count;
	}

	/**
	 * Проверяет наличие таблицы в модуле
	 *
	 * @param string $table название таблицы
	 * @return boolean
	 */
	private function is_table($table)
	{
		if(isset($this->cache["is_table"][$table]))
		{
			return $this->cache["is_table"][$table];
		}
		foreach($this->tables as $t)
		{
			if($t["name"] == $table)
			{
				$this->cache["is_table"][$table] = true;
				return true;
			}
		}
		$this->cache["is_table"][$table] = false;
		return false;
	}

	/**
	 * Проверяет наличие поля в таблице модуля
	 *
	 * @param string $table название таблицы
	 * @param string $field название поля
	 * @return boolean
	 */
	private function is_field($table, $field)
	{
		if(isset($this->cache["field_table"][$table][$field]))
		{
			if($this->cache["field_table"][$table][$field])
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		foreach($this->tables as $t)
		{
			if($t["name"] == $table)
			{
				foreach($t["fields"] as $f)
				{
					if($f["name"] == $field)
					{
						$this->cache["field_table"][$table][$field] = $f;
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Проверяет является ли поле переводими на другие языки
	 *
	 * @param string $table название таблицы
	 * @param string $field название поля
	 * @return boolean
	 */
	private function is_multilang_field($table, $field)
	{
		if(! $this->is_field($table, $field))
		{
			return false;
		}
		return ! empty($this->cache["field_table"][$table][$field]["multilang"]);
	}

	/**
	 * Удаление
	 *
	 * @param integer|array $ids идентификатор/идентификаторы
	 * @param string $element_type тип элемента
	 * @return void
	 */
	private function sql_delete($table, $ids, $element_type)
	{
		if(is_array($ids))
		{
			$where = " IN (%s)";
			$value = preg_replace('/[^0-9,]+/', '', implode(",", $ids));
		}
		else
		{
			$where = "=%d";
			$value = $ids;
		}
		DB::query("DELETE FROM {%s} WHERE id".$where, $table, $value);
		DB::query("DELETE FROM {rewrite} WHERE module_name='%s' AND element_type='%s' AND element_id".$where, $this->module, $element_type, $value);
		DB::query("DELETE FROM {menu} WHERE module_name='%s' AND element_type='%s' AND element_id".$where, $this->module, $element_type, $value);
		if($this->is_table($table.'_parents'))
		{
			DB::query("DELETE FROM {%s_parents} WHERE parent_id".$where." OR element_id".$where, $table, $value, $value);
		}
		if($this->is_table($table.'_category_rel'))
		{
			DB::query("DELETE FROM {%s_category_rel} WHERE element_id".$where, $table, $value);
		}
		if($this->is_table($table.'_site_rel'))
		{
			DB::query("DELETE FROM {%s_site_rel} WHERE element_id".$where, $table, $value);
		}
		switch($element_type)
		{
			case 'cat':
				if($this->is_table($table.'_rel'))
				{
					DB::query("DELETE FROM {%s_rel} WHERE cat_id".$where, $table, $value);
				}
				if($this->is_table($this->module.'_param_category_rel'))
				{
					DB::query("DELETE FROM {%s_param_category_rel} WHERE cat_id".$where, $this->module, $value);
				}

				break;

			case 'param':
				DB::query("DELETE FROM {%s_element} WHERE param_id".$where, $table, $value);
				DB::query("DELETE FROM {%s_select} WHERE param_id".$where, $table, $value);
				break;

			case 'element':
				if($this->is_table($table.'_param_element'))
				{
					DB::query("DELETE FROM {%s_param_element} WHERE element_id".$where, $table, $value);
				}
				break;
		}
	}

	/**
	 * Удаляет модуль
	 *
	 * @return void
	 */
	public function uninstall()
	{
		$this->diafan->_cache->delete('', $this->module);
		$this->diafan->_cache->delete('', "menu");

		foreach ($this->tables as $row)
		{
			DB::query("DROP TABLE {".$row["name"]."};");
		}

		$modules = array();
		foreach ($this->modules as $row)
		{
			if(! empty($row["site_page"]))
			{
				$modules[] = $row["name"];
			}
		}
		$site_ids = '';
		if($modules)
		{
			$site_ids = DB::query_result("SELECT GROUP_CONCAT(id SEPARATOR ',') FROM {site} WHERE module_name IN ('".implode("','", $modules)."')");
		}
		$rws = array();
		foreach ($this->site as $row)
		{
			if(! empty($row["rewrite"]))
			{
				$rws[] = $row["rewrite"];
			}
		}
		if(! empty($rws))
		{
			$site_ids2 = DB::query_result("SELECT GROUP_CONCAT(element_id SEPARATOR ',') FROM {rewrite} WHERE BINARY rewrite IN ('".implode("','", $rws)."') AND module_name='site' AND element_type='element'");
			if($site_ids2)
			{
				$site_ids .= ($site_ids ? ',' : '').$site_ids2;
			}
		}
		if($site_ids)
		{
			if(in_array(1, explode(',', $site_ids)))
			{
				DB::query("UPDATE {site} SET module_name='' WHERE id=1");
				if($site_ids == 1)
				{
					$site_ids = '';
				}
				else
				{
					$s = explode(',', $site_ids);
					unset($s[array_search(1, $s)]);
					$site_ids = implode(',', $s);
				}
			}
			if ($site_ids)
			{
				DB::query("DELETE FROM {site} WHERE id IN (".$site_ids.")");
				DB::query("DELETE FROM {site_parents} WHERE element_id IN (".$site_ids.") OR parent_id IN (".$site_ids.")");
				DB::query("UPDATE {site} SET parent_id=0 WHERE parent_id IN (".$site_ids.")");
				DB::query("DELETE FROM {rewrite} WHERE element_id IN (".$site_ids.") AND module_name='site' AND element_type='element'");

				$this->diafan->_route->delete(explode(',', $site_ids), 'site');
				$this->diafan->_menu->delete(explode(',', $site_ids), 'site', 'element');
			}
			DB::query("DELETE FROM {site_dynamic_module} WHERE module_name IN ('".implode("','", $modules)."')");
			DB::query("DELETE FROM {trash} WHERE table_name='site_dynamic_element' AND element_id IN (SELECT id FROM {site_dynamic_element} WHERE module_name IN ('".implode("','", $modules)."'))");
			DB::query("DELETE FROM {site_dynamic_element} WHERE module_name IN ('".implode("','", $modules)."')");
			DB::query("DELETE FROM {trash} WHERE module_name='rewrite' AND element_id IN (SELECT id FROM {rewrite} WHERE module_name IN ('".implode("','", $modules)."'))");
			DB::query("DELETE FROM {rewrite} WHERE module_name IN ('".implode("','", $modules)."')");
		}
		DB::query("DELETE FROM {modules} WHERE module_name='%h'", $this->module);
		$admin = array();
		if(! empty($this->admin))
		{
			foreach ($this->admin as $row)
			{
				$admin[] = $row["rewrite"];
				if(! empty($row["children"]))
				{
					foreach ($row["children"] as $r)
					{
						$admin[] = $r["rewrite"];
					}
				}
			}
			$admin_ids = DB::query_result("SELECT GROUP_CONCAT(id SEPARATOR ',') FROM {admin} WHERE BINARY rewrite IN ('".implode("','", $admin)."')");
			DB::query("DELETE FROM {admin} WHERE id IN (".$admin_ids.")");
			DB::query("DELETE FROM {admin_parents} WHERE element_id IN (".$admin_ids.")");
			DB::query("DELETE FROM {users_role_perm} WHERE BINARY rewrite IN ('".implode("','", $admin)."')");
		}
		DB::query("DELETE FROM {config} WHERE module_name='".$this->module."'");
		DB::query("DELETE FROM {config} WHERE name='".$this->module."'");
		DB::query("DELETE FROM {log_note} WHERE module_name='".$this->module."'");

		$this->diafan->_attachments->delete_module($this->module);
		$this->diafan->_images->delete_module($this->module);
		$this->diafan->_rating->delete_module($this->module);
		$this->diafan->_comments->delete_module($this->module);
		$this->diafan->_tags->delete_module($this->module);
		$this->diafan->_map->delete_module($this->module);
		$this->diafan->_search->delete_module($this->module);
		$this->diafan->_menu->delete_module($this->module);

		$trash_ids = DB::query_result("SELECT GROUP_CONCAT(id SEPARATOR ',') FROM {trash} WHERE module_name='".$this->module."'");
		if ($trash_ids)
		{
			DB::query("DELETE FROM {trash} WHERE id IN (".$trash_ids.")");
			$trash_child_ids = DB::query_result("SELECT GROUP_CONCAT(element_id SEPARATOR ',') FROM {trash_parents} WHERE parent_id IN (".$trash_ids.")");
			if ($trash_child_ids)
			{
				DB::query("DELETE FROM {trash_parents} WHERE element_id IN (".$trash_child_ids.")");
			}
			DB::query("DELETE FROM {trash_parents} WHERE element_id IN (".$trash_ids.")");
		}
		if(is_dir(ABSOLUTE_PATH.USERFILES.'/'.$this->module))
		{
			File::delete_dir(USERFILES.'/'.$this->module);
		}
		DB::query("DELETE FROM {inserts_site_rel} WHERE element_id IN (SELECT id FROM {inserts} WHERE text='%%module=".$this->module."%%')");
		DB::query("DELETE FROM {inserts} WHERE text LIKE '%%module=\"".$this->module."\"%%'");
		$this->uninstall_action();
	}

	/**
	 * Выполняет действия при удалении модуля
	 *
	 * @return void
	 */
	protected function uninstall_action(){}
}

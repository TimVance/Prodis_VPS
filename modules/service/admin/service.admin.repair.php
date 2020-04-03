<?php
/**
 * Восстановление базы данных
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

// 0 - выключить лог восстановления
// 1 - включить лог восстановления
// 2 - включить полный лог
define('REPAIR_LOG', 1);

// 0 - не добавлять страницы административного интерфейса и не обновлять информацию о них
// 1 - только добавлять новые страницы административного интерфейса
// 2 - добавлять страницы административного интерфейса и обновлять информацию о них
define('REPAIR_ADMINSITE', 1);

/**
 * Service_admin_repair
 */
class Service_admin_repair extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'repair';

	/**
	 * Выводит контент модуля
	 * @return void
	 */
	public function show()
	{
		if (! empty($_GET["repair"]))
		{
			$this->repair();
		}
		else
		{
			echo '<p>'.$this->diafan->_('Данный сервис предназначен для проверки правильности структуры базы данных DIAFAN.CMS и восстановления поврежденных таблиц до рабочего состояния и создания недостающих. Рекомендуется запускать после обновления CMS с предыдущих версий, либо после программных доработок системы.').'</p>';
			echo '<p><a href="'.URL.'?repair=1"><span class="btn btn_blue btn_small">'.$this->diafan->_('Начать проверку и восстановление базы данных').'</span></a></p>';
		}
	}

	/**
	 * Восстановление базы данных
	 * @return void
	 */
	public function repair()
	{
		Custom::inc("includes/install.php");

		$url = parse_url(DB_URL);
		$rows = DB::query_fetch_all("SHOW TABLES FROM `".substr($url['path'], 1)."`");
		foreach ($rows as $row)
		{
			foreach ($row as $k => $v)
			{
				$this->cache["tables"][] = preg_replace('/^'.preg_quote(DB_PREFIX, '/').'/', '', $v);
				break;
			}
		}

		if(REPAIR_ADMINSITE)
		{
			$rows = DB::query_fetch_all("SELECT * FROM {admin}");
			foreach ($rows as $row)
			{
				if(! empty($this->cache["admin"][$row["rewrite"].($row["parent_id"] ? '_child' : '')]))
				{
					DB::query("DELETE FROM {admin} WHERE id=%d", $row["id"]);
					DB::query("DELETE FROM {admin_parents} WHERE parent_id=%d", $row["id"]);
					DB::query("DELETE FROM {admin} WHERE parent_id=%d", $row["id"]);
					if(REPAIR_LOG)
					{
						echo '<br>'.$this->diafan->_('Удалена дублирующая страница административного интерфейса %s.', $row["name"].' '.$row["rewrite"]);
					}
				}
				else
				{
					$this->cache["admin"][$row["rewrite"].($row["parent_id"] ? '_child' : '')] = $row;
				}
			}
		}
		$rs = Custom::read_dir('modules');
		foreach($rs as $module)
		{
			if (Custom::exists('modules/'.$module.'/'.$module.'.install.php'))
			{
				Custom::inc("modules/".$module."/".$module.".install.php");
				$this->check_file($module);
			}
		}

		echo '<br><br><div class="ok">'.$this->diafan->_('База данных успешно проверена и не имеет ошибок на текущий момент.').'</div>';
	}

	/**
	 * Восстановление базы данных из файла
	 *
	 * @param string $name название модуля
	 * @return void
	 */
	private function check_file($module)
	{
		$name = Ucfirst($module).'_install';
		$class = new $name($this->diafan);
		$class->module = $module;

		if(! $class->is_core && ! in_array($module, $this->diafan->installed_modules))
			return;

		foreach ($class->tables as $row)
		{
			if (REPAIR_LOG == 2)
			{
				echo '<p>'.$this->diafan->_('Таблица %s', DB_PREFIX.$row["name"]);
			}
			if (in_array($row["name"], $this->cache["tables"]))
			{
				$table_fileds = array();
				$rows = DB::query_fetch_all("DESCRIBE {".$row["name"]."}");
				foreach ($rows as $r)
				{
					$table_fileds[] = $r["Field"];
					if(strpos($r["Field"], ')') !== false)
					{
						list($table_fileds_type[$r["Field"]]) = explode(")", $r["Type"]);
						$table_fileds_type[$r["Field"]] .= ')';
					}
					else
					{
						list($table_fileds_type[$r["Field"]]) = explode(" ", $r["Type"]);
					}
					$table_fileds_type[$r["Field"]] = str_replace(" ", '', $table_fileds_type[$r["Field"]]);
					if($r["Field"] == 'id')
					{
						$max_id = DB::query_result("SELECT MAX(id) FROM {".$row["name"]."}");
						if($max_id == 4294967295)
						{
							DB::query("DELETE FROM {".$row["name"]."} WHERE id=4294967295");
							$max_id = DB::query_result("SELECT MAX(id) FROM {".$row["name"]."}");
							DB::query("ALTER TABLE {".$row["name"]."} AUTO_INCREMENT = ".$max_id.";");
						}
					}
				}

				$fields = array();
				foreach ($row["fields"] as $f)
				{
					if(! empty($f["multilang"]))
					{
						foreach ($this->diafan->_languages->all as $l)
						{
							$fields[] = array(
								"name" => $f["name"].$l["id"],
								"type" => $f["type"],
								"comment" => (! empty($f["comment"]) ? " COMMENT '".str_replace("'", "\\'", $f["comment"])."'" : ''),
							);
						}
					}
					else
					{
						$fields[] = $f;
					}
				}
				foreach ($fields as $f)
				{
					if (! in_array($f["name"], $table_fileds))
					{
						$comment = (! empty($f["comment"]) ? " COMMENT '".str_replace("'", "\\'", $f["comment"])."'" : '');
						$query = 'ALTER TABLE {'.$row["name"]."} ADD `".$f["name"]."` ".$f["type"].$comment;
						if (REPAIR_LOG)
						{
							echo '<br>'.$this->diafan->_('Добавлено поле %s.', $f["type"]).'<pre>'.$query.'</pre>';
						}
						DB::query($query);
					}
					else
					{
						if(strpos($f["type"], ')') !== false)
						{
							list($type) = explode(")", $f["type"]);
							$type .= ')';
						}
						else
						{
							list($type) = explode(" ", $f["type"]);
						}
						$type = str_replace(" ", '', strtolower($type));
						if($table_fileds_type[$f["name"]] != $type)
						{
							$comment = (! empty($f["comment"]) ? " COMMENT '".str_replace("'", "\\'", $f["comment"])."'" : '');
							$query = 'ALTER TABLE {'.$row["name"]."} CHANGE `".$f["name"]."` `".$f["name"]."` ".$f["type"].$comment;
							if (REPAIR_LOG)
							{
								echo '<br>'.$this->diafan->_('Изменено поле %s.', $f["name"].' '.$f["type"]).'<pre>'.$query.'</pre>';
							}
							DB::query($query);
						}
						elseif (REPAIR_LOG == 2)
						{
							echo '<br>'.$this->diafan->_('Поле %s существует.', $f["name"]);
						}
					}
				}
				$this->update_table_parents($row["name"]);
			}
			else
			{
				$query = "CREATE TABLE {".$row["name"]."} (";
				foreach ($row["fields"] as $field)
				{
					$comment = (! empty($field["comment"]) ? " COMMENT '".str_replace("'", "\\'", $field["comment"])."'" : '');
					if(empty($field["multilang"]))
					{
						$query .= "\n".'`'.$field["name"].'` '.$field["type"].$comment.',';
					}
					else
					{
						foreach ($this->diafan->_languages->all as $lang)
						{
							$query .= "\n".'`'.$field["name"].$lang["id"].'` '.$field["type"].$comment.',';
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
				if (REPAIR_LOG)
				{
					echo '<p>'.$this->diafan->_('Таблица %s добавлена.', DB_PREFIX.$row["name"]).'<br><pre>'.$query.'</pre></p><hr>';
				}
				DB::query($query);
			}
		}

		if(REPAIR_ADMINSITE && $class->admin)
		{
			foreach ($class->admin as $row)
			{
				if(empty($row["sort"]))
				{
					$row["sort"] = 0;
				}
				if(empty($row["docs"]))
				{
					$row["docs"] = '';
				}
				if(empty($row["icon_name"]))
				{
					$row["icon_name"] = '';
				}
				if(empty($row["group_id"]))
				{
					$row["group_id"] = '';
				}
				if(empty($this->cache["admin"][$row["rewrite"]]))
				{
					$row["id"] = DB::query("INSERT INTO {admin} (group_id, name, rewrite, act, sort, docs, icon_name) VALUES ('%s', '%s', '%s', '%d', %d, '%s', '%s')", $row['group_id'], $row['name'], $row['rewrite'], $row['act'], $row['sort'], $row["docs"], $row["icon_name"]);
					if (REPAIR_LOG)
					{
						echo '<br>'.$this->diafan->_('Добавлена страница административного интерфейса %s.', $row["name"].' '.$row["rewrite"]);
					}
					$this->cache["admin"][$row["rewrite"]] = $row;
				}
				else
				{
					$v = $this->cache["admin"][$row["rewrite"]];
					if(REPAIR_ADMINSITE == 2 && ($v["group_id"] != $row["group_id"] || $v["name"] != $row["name"] || $v["docs"] != $row["docs"] || $v["icon_name"] != $row["icon_name"]))
					{
						DB::query("UPDATE {admin} SET group_id='%s', name='%s', docs='%s', icon_name='%s' WHERE id=%d", $row['group_id'], $row['name'], $row["docs"], $row["icon_name"], $v["id"]);

						DB::query("UPDATE {admin} SET group_id='%s', docs='%s', icon_name='%s' WHERE parent_id=%d", $row['group_id'], $row["docs"], $row["icon_name"], $v["id"]);

						if (REPAIR_LOG)
						{
							echo '<br>'.$this->diafan->_('Обновлена страница административного интерфейса %s.', $row["name"].' '.$row["rewrite"]);
						}
						$row["id"] = $v["id"];
						$this->cache["admin"][$row["rewrite"]] = $row;
					}
					elseif(REPAIR_LOG == 2)
					{
						echo '<br>'.$this->diafan->_('Страница административного интерфейса %s cуществует.', $v["name"].' '.$v["rewrite"]);
					}
					$this->cache["admin"][$row["rewrite"]]["children"] = (! empty($row["children"]) ? $row["children"] : array());
				}
				$row = $this->cache["admin"][$row["rewrite"]];
				if(! empty($row["children"]))
				{
					foreach ($row["children"] as $i => $ch)
					{
						if(empty($ch["sort"]))
						{
							$ch["sort"] = $i+1;
						}
						if(empty($this->cache["admin"][$ch["rewrite"].'_child']))
						{
							$ch["id"] = DB::query("INSERT INTO {admin} (group_id, name, rewrite, act, sort, docs, icon_name, parent_id) VALUES ('%s', '%s', '%s', '%s', %d, '%s', '%s', %d)", $row['group_id'], $ch['name'], $ch['rewrite'], (! empty($ch['act']) ? 1 : 0), $ch['sort'], $row["docs"], $row["icon_name"], $row["id"]);
							DB::query("INSERT INTO {admin_parents} (element_id, parent_id) VALUES (%d, %d)", $ch["id"], $row["id"]);
							DB::query("UPDATE {admin} SET count_children=count_children+1 WHERE id=%d", $row["id"]);
							if (REPAIR_LOG)
							{
								echo '<br>'.$this->diafan->_('Добавлена страница административного интерфейса %s.',$row["name"].'/'.$ch["name"].' '.$ch["rewrite"]);
							}
						}
						else
						{
							$v = $this->cache["admin"][$ch["rewrite"].'_child'];
							if(REPAIR_ADMINSITE == 2 && $v["name"] != $ch["name"])
							{
								DB::query("UPDATE {admin} SET name='%s' WHERE id=%d", $ch["name"], $v["id"]);
								if (REPAIR_LOG)
								{
									echo '<br>'.$this->diafan->_('Обновлена страница административного интерфейса %s.', $row["name"].'/'.$ch["name"].' '.$ch["rewrite"]);
								}
							}
							elseif(REPAIR_LOG == 2)
							{
								echo '<br>'.$this->diafan->_('Страница административного интерфейса %s cуществует.', $row["name"].'/'.$v["name"].' '.$v["rewrite"]);
							}
							$ch["id"] = $v["id"];
						}
						$this->cache["admin"][$ch["rewrite"].'_child'] = $ch;
					}
				}
			}
		}
	}

	/**
	 * Обновление таблицы родительских связей
	 * @return void
	 */
	private function update_table_parents($table)
	{
		if (strpos($table, '_parents') !== false)
		{
			DB::query("TRUNCATE TABLE {".$table."}");
			$table_parent = str_replace('_parents', '', $table);

			$rows = DB::query_fetch_all("SELECT parent_id, id FROM {".$table_parent."}");
			foreach ($rows as $row)
			{
				if ($row["parent_id"])
				{
					$parents = array();
					while ($row["parent_id"] > 0 && ! in_array($row["parent_id"], $parents))
					{
						$parents[] = $row["parent_id"];
						DB::query("INSERT INTO {".$table."} (`element_id`, `parent_id`) VALUES (%d, %d)", $row["id"], $row["parent_id"]);
						$row["parent_id"] = DB::query_result("SELECT parent_id FROM {".$table_parent."} WHERE id=%d LIMIT 1", $row["parent_id"]);
					}
				}
			}
			$rows = DB::query_fetch_all("SELECT id FROM {".$table_parent."}");
			foreach ($rows as $row)
			{
				$count = DB::query_result("SELECT COUNT(*) FROM  {".$table."} WHERE parent_id=%d", $row["id"]);
				DB::query("UPDATE {".$table_parent."} SET count_children=%d WHERE id=%d", $count, $row["id"]);
			}
		}
	}
}

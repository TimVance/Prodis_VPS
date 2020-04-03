<?php
/**
 * @package    DIAFAN.CMS
 *
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
 * Save_admin
 *
 * Сохранение элемента
 */
class Save_admin extends Diafan
{
	/**
	 * @var Save_functions_admin функции сохранения полей
	 */
	public $_functions;

	/**
	 * @var array массив полей таблицы для SQL-запроса на обновление
	 */
	public $query;

	/**
	 * @var array массив новых значений для SQL-запроса на обновление
	 */
	public $value;

	/**
	 * @var integer номер родителя, к которому прикреплен сохраняемый элемент
	 */
	public $save_parent_id;

	/**
	 * @var integer номер каталога, к которому прикреплен сохраняемый элемент
	 */
	public $save_cat_id;

	/**
	 * @var integer номер сортировки сохраняемого элемента
	 */
	public $save_sort;

	/**
	 * @var string псевдоссылка сохранена
	 */
	public $is_save_rewrite = false;

	/**
	 * @var integer номер ошибки
	 */
	public $err = 0;

	/**
	 * @var boolean изменения сохранены
	 */
	private $done = false;

	/**
	 * Вызывает функции сохранения полей
	 *
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		if(! $this->_functions)
		{
			Custom::inc("adm/includes/save_functions.php");
			$this->_functions = new Save_functions_admin($this->diafan);
		}

		if (is_callable(array(&$this->_functions, $name)))
		{
			return call_user_func_array(array(&$this->_functions, $name), $arguments);
		}
		else
		{
			return 'fail_function';
		}
	}

	/**
	 * Сохраняет изменения
	 *
	 * @return void
	 */
	public function save()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (!$this->diafan->_users->checked)
		{
			$this->diafan->redirect(URL);
			return;
		}

		// Проверка прав на сохранение
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->diafan->redirect(URL);
			return;
		}

		if(empty($_POST["id"]) && ! $this->diafan->config('only_edit') && ! $this->diafan->config('config'))
		{
			$this->diafan->save_new();
			$this->diafan->is_new = true;
		}
		else
		{
			$this->diafan->id = $this->diafan->filter($_POST, "int", "id");
			$this->diafan->values("id");
		}

		// Если отмечена галочка "Видеть только свои материалы", то редактирование чужих материалов запрещено
		if($this->diafan->is_variable("admin_id")
		   && $this->diafan->values("admin_id")
		   && $this->diafan->values("admin_id") != $this->diafan->_users->id
		   && DB::query_result("SELECT only_self FROM {users_role} WHERE id=%d LIMIT 1", $this->diafan->_users->role_id))
		{
			Custom::inc('includes/404.php');
		}

		// Подготовка значений полей для элемента в соответсвии с указанными типами

		foreach ($this->diafan->variables as $title => $variable_table)
		{
			$this->prepare_new_values($variable_table);
		}

		// Сохраняет конфигурацию модуля
		if ($this->diafan->config("config"))
		{
			for ($q = 0; $q < count($this->value); $q++)
			{
				$this->value[$q] = str_replace("\n", '', $this->value[$q]);
			}
			DB::query("DELETE FROM {config} WHERE module_name='%s' AND site_id=%d AND (lang_id="._LANG." OR lang_id=0)", $this->diafan->_admin->module, $this->diafan->_route->site);
			for ($q = 0; $q < count($this->value); $q++)
			{
				list( $name, $mask ) = explode('=', $this->query[$q]);
				$name = str_replace('`', '', $name);

				// записываем значение в конфигурацю если оно не пустое или если конфигурация сохраняется для раздела и оно отличается от основной конфигурации
				if (! $this->diafan->_route->site && ($this->value[$q] || $this->value[$q] === "0") || $this->diafan->_route->site && DB::query_result("SELECT value FROM {config} WHERE module_name='%s' AND site_id=0 AND name='%h'".($this->diafan->variable_multilang($name) ? " AND lang_id='"._LANG."'" : '' )." LIMIT 1", $this->diafan->_admin->module, $name) != $this->value[$q])
				{
					DB::query("INSERT INTO {config} (name, module_name, value, site_id, lang_id) VALUES ('%h', '%h', ".$mask.", '%d', '%d')", $name, $this->diafan->_admin->module, $this->value[$q], $this->diafan->_route->site, ($this->diafan->variable_multilang($name) ? _LANG : 0));
				}
			}
			$this->done = true;

			// Удаляет кэш конфигурации модулей
			$this->diafan->_cache->delete("configmodules", "site");
		}

		// Сохраняет элемент
		elseif(! empty($this->query))
		{
			if (! DB::query("UPDATE {".$this->diafan->table."} SET ".implode(', ', $this->query)." WHERE id = %d", array_merge($this->value, array($this->diafan->id))))
			{
				return;
			}
		}

		// Удаляет кэш модуля
		$this->diafan->_cache->delete("", $this->diafan->_admin->module);

		$this->diafan->save_redirect();
	}

	/**
	 * Добавляет элемент в базу данных
	 *
	 * @return void
	 */
	public function save_new()
	{
		$def = array ();
		if ($this->diafan->variable_list('plus'))
		{
			$def['parent_id'] = intval($this->diafan->_route->parent);
		}
		if ($this->diafan->config("element_site"))
		{
			$def['site_id'] = $this->diafan->filter($_POST, "int", "site_id");
		}
		if ($this->diafan->config("element"))
		{
			$def['cat_id'] = $this->diafan->filter($_POST, "int", "cat_id");
		}

		$this->diafan->id = DB::query("INSERT INTO {".$this->diafan->table."} (".implode(',', array_keys($def)).") VALUES (".implode(',', $def).")");

		if (! $this->diafan->id)
		{
			throw new Exception('Не удалось добавить новый элемент в базу данных. Возможно, таблица '.DB_PREFIX.$this->diafan->table.' имеет неправильную структуру.');
		}
	}

	/**
	 * Запоминает имя переменной для сохранения
	 *
	 * @return void
	 */
	public function set_value($value)
	{
		$this->value[] = $value;
	}

	/**
	 * Запоминает значение переменной для сохранения
	 *
	 * @return void
	 */
	public function set_query($query)
	{
		$this->query[] = $query;
	}

	/**
	 * Подготавливает новые значения для сохранения
	 *
	 * @return boolean true
	 */
	public function prepare_new_values($variable_table)
	{
		foreach ($variable_table as $key => $type_value)
		{
			if(is_array($type_value))
			{
				if(! empty( $type_value["disabled"]))
				{
					continue;
				}
				if(! empty( $type_value["no_save"]))
				{
					continue;
				}
				$type_value = $type_value["type"];
			}
			else
			{
				$type_value = $type_value;
			}

			$name = "`".$key.( ! $this->diafan->config("config") && $this->diafan->variable_multilang($key) ? _LANG : '' )."`";

			$func = 'save'. ( $this->diafan->config("config") ? '_config' : '' ).'_variable_'.str_replace('-', '_', $key);
			if (call_user_func_array (array(&$this->diafan, $func), array()) !== 'fail_function')
			{
				continue;
			}
			if ($type_value == 'module')
			{
				if (in_array($key, $this->diafan->installed_modules)
					&& Custom::exists('modules/'.$key.'/admin/'.$key.'.admin.inc.php'))
				{
					Custom::inc('modules/'.$key.'/admin/'.$key.'.admin.inc.php');
					$func = 'save'.( $this->diafan->config("config") ? '_config' : '' );
					$class = ucfirst($key).'_admin_inc';
					if (method_exists($class, $func))
					{
						$module_class = new $class($this->diafan);
						call_user_func_array (array(&$module_class, $func), array());
					}
				}
			}
			elseif ($type_value == 'date' || $type_value == 'datetime')
			{
				$this->query[] = $name."='%d'";
				$this->value[] = $this->diafan->unixdate($_POST[$key]);
			}
			elseif ($type_value == 'hr' || $type_value == 'title')
			{
				continue;
			}
			elseif ($type_value == 'floattext')
			{
				$this->value[] = str_replace(',', '.', ! empty($_POST[$key]) ? $_POST[$key] : 0);
				$this->query[] = $name."='%f'";
			}
			elseif($type_value == 'editor')
			{
				$this->value[] = $this->diafan->save_field_editor($key);
				$this->query[] = $name."='%s'";
			}
			else
			{
				$this->value[] = isset($_POST[$key]) ? $_POST[$key] : '';

				if ($type_value == 'text' || $type_value == 'select' || $type_value == 'email')
				{
					$this->query[] = $name."='%h'";
				}
				elseif ($type_value == 'checkbox' || $type_value == 'numtext')
				{
					$this->query[] = $name."='%d'";
				}
				else //textarea,none,function,password...
				{
					$this->query[] = $name."='%s'";
				}
			}
		}
	}

	/**
	 * Сохраняет поле с типом "Визуальный редактор"
	 *
	 * @param string $key название поля
	 * @return void
	 */
	public function save_field_editor($key)
	{
		$text = isset($_POST[$key]) ? $_POST[$key] : '';
		// типограф
		if (! empty($_POST[$key."_typograf"]))
		{
			include_once (ABSOLUTE_PATH."plugins/remotetypograf.php");

			$remoteTypograf = new RemoteTypograf();

			$remoteTypograf->htmlEntities();
			$remoteTypograf->br(false);
			$remoteTypograf->p(true);
			$remoteTypograf->nobr(3);
			$remoteTypograf->quotA('laquo raquo');
			$remoteTypograf->quotB('bdquo ldquo');

			$text = $remoteTypograf->processText($text);
		}
		// подключение/отключение визуального редактора
		if($this->diafan->_users->htmleditor)
		{
			if($this->diafan->is_new)
			{
				$hide_htmleditor = array();
			}
			else
			{
				$hide_htmleditor = explode(",", $this->diafan->configmodules("hide_".$this->diafan->table."_".$this->diafan->id, "htmleditor"));
			}
			if(! empty($_POST[$key."_htmleditor"]) && ! in_array($key, $hide_htmleditor))
			{
				$hide_htmleditor[] = $key;
				$hide_htmleditor = array_diff($hide_htmleditor, array("", 0));
				$this->diafan->configmodules("hide_".$this->diafan->table."_".$this->diafan->id, "htmleditor", false, false, implode(",", $hide_htmleditor));
			}
			elseif(empty($_POST[$key."_htmleditor"]) && in_array($key, $hide_htmleditor))
			{
				$hide_htmleditor = array_diff($hide_htmleditor, array("", 0, $key));
				$this->diafan->configmodules("hide_".$this->diafan->table."_".$this->diafan->id, "htmleditor", false, false, implode(",", $hide_htmleditor));
			}
		}
		// ссылки заменяем на id
		$text = $this->diafan->_route->replace_link_to_id($text);

		// копирование внешних изображений
		if ($this->diafan->_users->copy_files && ! IS_DEMO)
		{
			if(preg_match_all('/\<img[^\>+]src=\"([^\"]+)\"/', $text, $m))
			{
				foreach ($m[1] as $i => $src)
				{
					$url = parse_url($src);
					if(! isset($url['scheme']) || strpos(strtolower($url['scheme']), 'http') !== 0) continue;
					$scheme     = isset($url['scheme']) ? $url['scheme'] . '://' : '';
					$host       = isset($url['host']) ? $url['host'] : '';
					$port       = isset($url['port']) ? ':' . $url['port'] : '';
					$user       = isset($url['user']) ? $url['user'] : '';
					$pass       = isset($url['pass']) ? ':' . $url['pass']  : '';
					$pass       = ($user || $pass) ? "$pass@" : '';
					$path       = isset($url['path']) ? $url['path'] : '';
					$query      = isset($url['query']) ? '?' . $url['query'] : '';
					$fragment   = isset($url['fragment']) ? '#' . $url['fragment'] : '';
					$src = $scheme.$user.$pass.$host.$port.$path.$query.$fragment;
					if ($host != getenv("HTTP_HOST"))
					{
						$extension = substr(strrchr($src, '.'), 1);
						$name = md5($src).'.'.$extension;
						try
						{
							File::copy_file($src, USERFILES.'/upload/'.$name);
							$err_copy_file = false;
						}
						catch(Exception $e)
						{
							$err_copy_file = true;
						}
						if(! $err_copy_file)
						{
							$text = str_replace('src="'.$src.'"', 'src="BASE_PATH'.USERFILES.'/upload/'.$name.'"', $text);
						}
					}
				}
			}
		}
		return $text;
	}

	/**
	 * Производит перенаправление на страницу редактирования, на список и пр.
	 *
	 * @return void
	 */
	public function save_redirect()
	{
		// если сохраняли Ajaxом, отдаем положительный результат
		if (! empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == 'xmlhttprequest')
		{
			echo '{success:true}';
			exit;
		}

		$status = ($this->diafan->err ? 'error'.$this->diafan->err.'/' : '')
		.($this->done ? 'success1/' : '');


		// Для модуля Языки сайта
		if ($this->diafan->table == "languages")
		{
			if (_LANG == $this->diafan->id)
			{
				$this->diafan->redirect(BASE_PATH.ADMIN_FOLDER.'/languages/'.$this->diafan->get_nav);
			}
			else
			{
				$this->diafan->redirect(URL.$status.$this->diafan->get_nav);
			}
		}

		$page = '';
		$parent = '';
		$cat = '';
		if($this->diafan->_route->page)
		{
			$page = 'page'.$this->diafan->_route->page.'/';
		}
		// else
		// {
			if($this->diafan->save_parent_id)
			{
				$parent = 'parent'.$this->diafan->save_parent_id.'/';
			}
			if($this->diafan->_route->cat)
			{
				$cat = 'cat'.$this->diafan->_route->cat.'/';
			}
			elseif($this->diafan->save_cat_id)
			{
				$cat = 'cat'.$this->diafan->save_cat_id.'/';
			}
		// }

		$url = $this->diafan->get_admin_url('page', 'parent', 'cat', 'site')
			.($this->diafan->_route->site ? 'site'.$this->diafan->_route->site.'/' : ''); // TO_DO: without 'site'

		if(! empty($_POST["redirect_add"]))
		{
			$_SESSION["redirect_add"] = true;
		}
		else
		{
			unset($_SESSION["redirect_add"]);
		}
		if(! empty($_POST["redirect_edit"]))
		{
			$_SESSION["redirect_edit"] = true;
		}
		else
		{
			unset($_SESSION["redirect_edit"]);
		}
		if(! empty($_POST["redirect_add"]))
		{
			$this->diafan->redirect($url .$cat. $parent.'addnew1/'.$this->diafan->get_nav);
			return;
		}
		elseif(! empty($_POST["redirect_edit"]))
		{
			if($this->diafan->config("only_edit"))
			{
				$this->diafan->redirect($url .$cat.$page. $parent.$status .$this->diafan->get_nav);
			}
			$this->diafan->redirect($url .$cat.$page. $parent.$status.'edit'.$this->diafan->id.'/'.$this->diafan->get_nav);
			return;
		}

		// Если к странице прикреплен модуль, то редирект на модуль
		$module_name = ! empty( $_POST["module_name"] ) ? preg_replace('/[^A-Za-z-]+/', '', $_POST["module_name"]) : '';
		if ($this->diafan->_admin->rewrite == "site" && $module_name && Custom::exists('modules/'.$module_name.'/admin/'.$module_name.'.admin.php'))
		{
			$this->diafan->redirect(BASE_PATH_HREF.$module_name.'/site'.$this->diafan->id.'/' .$cat. $parent.$status.$this->diafan->get_nav);
			return;
		}

		if(DB::query_result("SELECT COUNT(*) FROM {%h} WHERE trash='0'", $this->diafan->table) > 10)
		{
			$ankor = '#'.$this->diafan->id;
		}
		// "Сохранить и выйти" - редирект на show_module
		$this->diafan->redirect($url.$cat.$page.$parent.$status.$this->diafan->get_nav.$ankor);
	}

	/**
	 * Обновляет значения для таблицы - соединения
	 *
	 * @param string $table название таблицы
	 * @param string $element_id_name название основного поля соединения
	 * @param string $rel_id_name название второго поля соединения
	 * @param array $rels передаваемы знанения второго поля
	 * @param integer $save_id значение основного поля
	 * @param integer $new (0, 1) является ли осноной элемент новым
	 * @return void
	 */
	public function update_table_rel($table, $element_id_name, $rel_id_name, $rels, $save_id, $new)
	{
		if(in_array("all", $rels))
		{
			$rels = array();
		}
		if($rels)
		{
			if(! $new)
			{
				$add = array();
				$del = array();
				$values = array();
				$rows = DB::query_fetch_all("SELECT id, %s FROM {%s} WHERE %s=%d AND trash='0'", $rel_id_name, $table, $element_id_name, $save_id);
				foreach ($rows as $row)
				{
					if(! in_array($row[$rel_id_name], $rels))
					{
						$del[] = $row["id"];
					}
					$values[] = $row[$rel_id_name];
				}
				foreach ($rels as $row)
				{
					if(! in_array($row, $values))
					{
						$add[] = $row;
					}
				}
				if($del)
				{
					DB::query("DELETE FROM {%s} WHERE id IN (%s)", $table, implode(",", $del));
				}
			}
			else
			{
				$add = $rels;
			}

			foreach ($add as $row)
			{
				DB::query("INSERT INTO {%s} (%s, %s) VALUES (%d, %d)", $table, $element_id_name, $rel_id_name, $save_id, $row);
			}
		}
		else
		{
			if(! $new)
			{
				DB::query("DELETE FROM {%s} WHERE %s=%d", $table, $element_id_name, $save_id);
			}
			DB::query("INSERT INTO {%s} (%s, %s) VALUES (%d, 0)", $table, $element_id_name, $rel_id_name, $save_id);
		}
	}

	/**
	 * Получает старое значение поля
	 * @param string $field название поля
	 * @param mixed $default значение по умолчанию
	 * @param boolean $save записать значение по умолчанию
	 * @return mixed
	 */
	public function values($field, $default = false, $save = false)
	{
		if(! isset($this->cache["oldrow"]))
		{
			if($this->diafan->is_new)
			{
				$this->cache["oldrow"] = array();
			}
			else
			{
				if (! $this->diafan->config("config"))
				{
					$this->cache["oldrow"] = DB::query_fetch_array(
						"SELECT * FROM {".$this->diafan->table."} WHERE id = %d"
						.($this->diafan->variable_list('actions', 'trash') ? " AND trash='0'" : '')
						." LIMIT 1", $this->diafan->id);
					if (! $this->cache["oldrow"])
					{
						Custom::inc('includes/404.php');
					}
				}
			}
		}

		$field .= $this->diafan->variable_multilang($field) ? _LANG : '';

		if($default && empty($this->cache["oldrow"][$field]))
		{
			return $default;
		}
		if(! isset($this->cache["oldrow"][$field]))
		{
			return false;
		}
		else
		{
			return $this->cache["oldrow"][$field];
		}
	}
}

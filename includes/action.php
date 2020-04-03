<?php
/**
 * Каркас для обработки POST-запросов модуля
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

abstract class Action extends Diafan
{
	/**
	 * @var array полученный после обработки данных результат
	 */
	public $result;

	/**
	 * @var string значения параметров из формы, сформированные для письма пользователю
	 */
	protected $message_param;

	/**
	 * @var string значения параметров из формы, сформированные для письма администратору
	 */
	protected $message_admin_param;

	/**
	 * @var integer номер страницы сайта, к которой прикреплен модуль
	 */
	protected $site_id;

	/**
	 * Подключает модель
	 *
	 * @return object|null
	 */
	public function __get($name)
	{
		if($name == 'model' || $name == 'inc')
		{
			$module = $this->diafan->current_module;
			if(! isset($this->cache[$name.'_'.$module]))
			{
				if(Custom::exists('modules/'.$module.'/'.$module.'.'.$name.'.php'))
				{
					Custom::inc('modules/'.$module.'/'.$module.'.'.$name.'.php');
					$class = ucfirst($module).'_'.$name;
					$this->cache[$name.'_'.$module] = new $class($this->diafan, $module);
				}
			}
			return $this->cache[$name.'_'.$module];
		}
		return NULL;
	}

	/**
	 * Отправляет ответ
	 *
	 * @return void
	 */
	public function end()
	{
		$params = array("errors", "result", "update_captcha", "redirect", "form", "form_hide", "add", "data");

		$s = false;
		foreach ($params as $v)
		{
			if (! empty($this->result[$v]))
			{
				$s = true;
				break;
			}
		}

		if ($s)
		{
			if (! empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == 'xmlhttprequest'
				// для IE
				|| ! empty($_POST["ajax"]))
			{
				if($this->diafan->_site->js_view)
				{
					if(empty($this->result["js"]))
					{
						$this->result["js"] = array();
					}
					$js_view = array();
					foreach($this->diafan->_site->js_view as $path)
					{
						if(in_array($path, $js_view))
							continue;

						$js_view[] = $path;

						if (substr($path, 0, 4) != 'http')
						{
							if(Custom::path($path))
							{
								$name_function = 'init_' . str_replace('.', '_', basename($path, '.js'));
								if(strpos(file_get_contents(Custom::path($path)), 'function '.$name_function) === false)
								{
									$name_function = '';
								}
								$this->result["js"][] = array(
									"src" => BASE_PATH.File::compress(Custom::path($path), 'js'),
									"func" => $name_function,
								);
							}
						}
						else
						{
							$this->result["js"][] = array(
								"src" => $path,
								"func" => '',
							);
						}
					}
				}
				if($this->diafan->_site->css_view)
				{
					if(empty($this->result["css"]))
					{
						$this->result["css"] = array();
					}
					$css_view = array();
					foreach($this->diafan->_site->css_view as $path)
					{
						if(in_array($path, $css_view))
							continue;

						$css_view[] = $path;

						if (substr($path, 0, 4) != 'http')
						{
							if(Custom::path($path))
							{
								$this->result["css"][] = BASE_PATH.File::compress(Custom::path($path), 'css');
							}
						}
						else
						{
							$this->result["css"][] = $path;
						}
					}
				}
				$this->result["replace"] = ! empty($this->result["replace"]);
				echo $this->to_json($this->result);
			}
			else
			{
				if(! empty($this->result["redirect"]))
				{
					$this->diafan->redirect($this->result["redirect"]);
				}
				else
				{
					$query = '';
					if (! empty($this->result["errors"]))
					{
						foreach ($this->result["errors"] as $field => $mes)
						{
							if (empty($_POST["update"]))
							{
								$query .= '&mess'.($field ? '_'.$field : '').'='.$mes;
							}
						}
					}
					$url = $this->diafan->_route->current_link();
					$this->diafan->redirect($url.($query ? '?form_tag='.$this->get_form_tag().$query : ''));
				}
			}
		}
		exit;
	}

	/**
	 * Проверяет сформирован ли ответ
	 *
	 * @return boolean
	 */
	public function result()
	{
		if(! empty($this->result["result"]) || ! empty($this->result["errors"]))
		{
			return true;
		}
		return false;
	}

	/**
	 * Преобразует массив в формат JSON
	 *
	 * @param array $result исходный массив
	 * @return string
	 */
	private function to_json($result)
	{
		Custom::inc('plugins/json.php');
		return to_json($result);
	}

	/**
	 * Получает тег-идентификатор формы
	 *
	 * @return string
	 */
	private function get_form_tag()
	{
		if(empty($_POST["form_tag"]))
		{
			$tag = $this->diafan->current_module;
		}
		else
		{
			$tag = preg_replace('/[^a-z0-9_-]/', '', $_POST["form_tag"]);
		}
		return $tag;
	}

	/**
	 * Проверяет авторизован ли пользователь на сайте
	 *
	 * @return void
	 */
	public function check_user()
	{
		if(! $this->diafan->_users->id)
		{
			$this->auth_user();
		}
	}

	/**
	 * Проверяет хэш пользователя
	 *
	 * @return void
	 */
	public function check_user_hash()
	{
		if ($this->diafan->_users->id)
		{
			if(! $this->diafan->_users->checked)
			{
				$this->result["errors"][0] = 'ERROR';
			}
			else
			{
				$this->result["hash"] = $this->diafan->_users->get_hash();
			}
		}
	}

	/**
	 * Проверяет на заполнение обязательных полей
	 *
	 * @param array $config настройки функции: params поля формы, prefix префикс
	* @return void
	*/
	protected function empty_required_field($config)
	{
		if(empty($config["params"]))
		{
			return;
		}
		$prefix =  ! empty($config["prefix"]) ? $config["prefix"] : '';
		$params =  $config["params"];

		foreach ($params as $row)
		{
			if ($row["required"] && ($row["type"] == "attachments" ||$row["type"] == "images"))
			{
				$empty_attach = true;
				if(! empty($_FILES[$prefix.'attachments'.$row["id"]]))
				{
					foreach ($_FILES[$prefix.'attachments'.$row["id"]]["name"] as $i => $name)
					{
						if(! empty($name) && ! empty($_FILES[$prefix.'attachments'.$row["id"]]["tmp_name"][$i]) && ! empty($_FILES[$prefix.'attachments'.$row["id"]]["size"][$i]))
						{
							$empty_attach = false;
						}
					}
				}
				if($empty_attach)
				{
					$this->result["errors"][$prefix."p".$row["id"]] = $this->diafan->_('Пожалуйста, прикрепите файл.');
				}
				continue;
			}
			if ($row["required"] && $row["type"] == "images")
			{
				continue;
			}
			if ($row["required"] && empty($_POST[$prefix.'p'.$row["id"]]))
			{
				$this->result["errors"][$prefix."p".$row["id"]] = $this->diafan->_('Пожалуйста, заполните обязательное поле «%s».', false, $row["name"]);
			}

			if (! empty($_POST[$prefix."p".$row["id"]]) && $row["type"] == "email")
			{
				$this->valid_email($_POST[$prefix."p".$row["id"]], $prefix."p".$row["id"]);
			}

			if (! empty($_POST[$prefix."p".$row["id"]]) && $row["type"] == "phone")
			{
				$this->valid_phone($_POST[$prefix."p".$row["id"]], $prefix."p".$row["id"]);
			}

			if (! empty($_POST[$prefix."p".$row["id"]]) && $row["type"] == "url")
			{
				$this->valid_url($_POST[$prefix."p".$row["id"]], $prefix."p".$row["id"]);
			}
		}
		return;
	}

	/**
	 * Добавляет значение полей формы в базу данных
	 *
	 * @param array $config настройки функции: id номер элемента, table таблица, params поля формы, multilang значения переводятся
	* @return void
	*/
	protected function insert_values($config)
	{
		if(empty($config["id"]) || empty($config["table"]) || empty($config["params"]))
		{
			return;
		}
		$id =  $config["id"];
		$table =  $config["table"];
		$params =  $config["params"];
		$prefix = ! empty($config["prefix"]) ? $config["prefix"] : '';
		$message = array();
		$message_admin = array();
		foreach ($params as $row)
		{
			$mess = '';

			// формируем текст письма администратору
			if (empty($_POST[$prefix."p".$row["id"]]) && ! in_array($row["type"], array("checkbox", 'attachments')))
			{
				continue;
			}

			// добавляем файлы
			switch($row["type"])
			{
				case "attachments":
					if (! empty($_FILES[$prefix.'attachments'.$row["id"]]))
					{
						$row_config = unserialize($row["config"]);
						$row_config["param_id"] = $row["id"];
						$row_config["prefix"] = $prefix;

						try
						{
							$result_upload = $this->diafan->_attachments->save($id, $table, $row_config);
						}
						catch(Exception $e)
						{
							Dev::$exception_field = $prefix."p".$row["id"];
							Dev::$exception_result = $this->result;

							DB::query("DELETE FROM {".$table."} WHERE  id=%d", $id);
							DB::query("DELETE FROM {".$table."_param_element} WHERE element_id=%d", $id);
							$this->diafan->_attachments->delete($id, $table);
							throw new Action_exception($e->getMessage());
						}
						if($result_upload)
						{
							$mess = $row["name"].':';
							$attachs = $this->diafan->_attachments->get($id, $table, $row["id"]);
							foreach ($attachs as $a)
							{
								if ($a["is_image"])
								{
									$mess .= ' <a href="'.$a["link"].'">'.$a["name"].'</a> <a href="'.$a["link"].'"><img src="'.$a["link_preview"].'"></a>';
								}
								else
								{
									$mess .= ' <a href="'.$a["link"].'">'.$a["name"].'</a>';
								}
							}
							if($row_config["attachments_access_admin"])
							{
								$mess .= '<br>'.$this->diafan->_('Для просмотра файлов авторизуйтесь на сайте как администратор.');
							}
							else
							{
								$message[] = $mess;
							}
							$message_admin[] = $mess;
							$mess = '';
						}
					}
					break;

				case "text":
				case "email":
				case "phone":
				case "url":
					$_POST[$prefix."p".$row["id"]] = $this->diafan->filter($_POST, 'string', $prefix."p".$row["id"]);
					$mess = $row["name"].': '.$_POST[$prefix."p".$row["id"]];
					break;

				case "textarea":
					$_POST[$prefix."p".$row["id"]] = nl2br(htmlspecialchars($_POST[$prefix."p".$row["id"]]));
					$mess = $row["name"].': '.$_POST[$prefix."p".$row["id"]];
					break;

				case "editor":
					$_POST[$prefix."p".$row["id"]] = $this->diafan->_bbcode->replace($_POST[$prefix."p".$row["id"]]);
					$mess = $row["name"].': '.$_POST[$prefix."p".$row["id"]];
					break;

				case "numtext":
					$_POST[$prefix."p".$row["id"]] = $this->diafan->filter($_POST, 'int', $prefix."p".$row["id"]);
					$mess = $row["name"].': '.$_POST[$prefix."p".$row["id"]];
					break;

				case "date":
					$mess = $row["name"].': '.$this->diafan->filter($_POST, 'string', $prefix."p".$row["id"]);
					$_POST[$prefix."p".$row["id"]] = $this->diafan->formate_in_date($_POST[$prefix."p".$row["id"]]);
					break;

				case "datetime":
					$mess = $row["name"].': '.$this->diafan->filter($_POST, 'string', $prefix."p".$row["id"]);
					$_POST[$prefix."p".$row["id"]] = $this->diafan->formate_in_datetime($_POST[$prefix."p".$row["id"]]);
					break;

				case "checkbox":
					$value = ! empty($_POST[$prefix."p".$row["id"]]) ? 1 : 0;
					if (empty($row["select_values"][$value]))
					{
						if($value == 1)
						{
							$mess = $row["name"];
						}
					}
					elseif(! empty($row["select_values"][$value]))
					{
						$mess = $row["name"].': '.$row["select_values"][$value];
					}
					break;

				case "select":
				case "radio":
					if(! empty($row["select_array"]))
					{
						foreach ($row["select_array"] as $select)
						{
							if ($select["id"] == $_POST[$prefix."p".$row["id"]])
							{
								$mess = $row["name"].': '.$select["name"];
							}
						}
					}
					break;

				case "multiple":
					if(! empty($row["select_array"]) && ! empty($_POST[$prefix."p".$row["id"]]) && is_array($_POST[$prefix."p".$row["id"]]))
					{
						$vals = array();
						foreach ($row["select_array"] as $select)
						{
							if (in_array($select["id"], $_POST[$prefix."p".$row["id"]]))
							{
								$vals[] = $select["name"];
							}
						}
						$mess = $row["name"].': '.implode(", ", $vals);
					}
					break;

				default:
					$_POST[$prefix."p".$row["id"]] = $this->diafan->filter($_POST, 'string', $prefix."p".$row["id"]);
					$mess = $row["name"].': '.$_POST[$prefix."p".$row["id"]];
					break;
			}
			if($mess)
			{
				$message_admin[] = $mess;
				$message[] = $mess;
			}

			// добавляем значения в базу данных
			if (empty($_POST[$prefix."p".$row["id"]]))
				continue;

			if(! empty($config["multilang"]))
			{
				if(in_array($row["type"], array('text', 'textarea', 'editor')))
				{
					$value_name = '[value]';
				}
				else
				{
					$value_name = 'value'.$this->diafan->_languages->site;
				}
			}
			else
			{
				$value_name = 'value';
			}

			if ($row["type"] == "multiple")
			{
				foreach ($_POST[$prefix."p".$row["id"]] as $val)
				{
					DB::query("INSERT INTO {".$table."_param_element} (".$value_name.", param_id, element_id) VALUES ('%d', %d, %d)", $val, $row["id"], $id);
				}
			}
			else
			{
				DB::query("INSERT INTO {".$table."_param_element} (".$value_name.", param_id, element_id) VALUES ('%s', %d, %d)", $_POST["p".$row["id"]], $row["id"], $id);
			}
		}

		$this->message_admin_param = implode('<br>', $message_admin);
		$this->message_param = implode('<br>', $message);
	}

	/**
	 * Проверяет корректность номера страницы сайта.
	 *
	 * @return boolean
	 */
	protected function check_site_id()
	{
		if(empty($_POST["site_id"]))
		{
			$this->result["errors"][0] = $this->diafan->_('Неверно задана страница сайта.');
			return $this->result();
		}
		$row = DB::query_fetch_array(
			"SELECT id, access FROM {site} WHERE id=%d AND module_name='%s' AND trash='0' LIMIT 1", $_POST["site_id"], $_POST["module"]
		);
		if(empty($row))
		{
			$this->result["errors"][0] = $this->diafan->_('Неверно задана страница сайта.');
		}
		elseif($row["access"])
		{
			if(! $this->diafan->_users->id)
			{
				$this->auth_user();
			}
			elseif(! DB::query_result("SELECT id FROM {access} WHERE element_id=%d AND module_name='site' AND element_type='element' AND role_id=%d", $row["id"], $this->diafan->_users->role_id))
			{
				$this->result["errors"][0] = $this->diafan->_('Нет доступа к странице сайта.');
			}
		}
		$this->site_id = $row["id"];
		return $this->result();
	}

	/**
	 * Формирует ответ, если пользователь не авторизован на сайте.
	 *
	 * @return void
	 */
	private function auth_user()
	{
		$this->result["errors"][0] = $this->diafan->_('Сессия закончилась. Введите логин и пароль.');
	}

	/**
	 * Проверяет правильность капчи
	 *
	 * @return void
	 */
	protected function check_captcha()
	{
		$tag = $this->get_form_tag();
		$error = $this->diafan->_captcha->error($tag);
		if ($error)
		{
			$this->result["errors"]["captcha"] = $error;
		}
		if (empty($_POST['captcha_update']))
		{
			$this->result["captcha"] = $this->diafan->_captcha->get($tag, '', true);
		}
		else
		{
			$this->result["captcha"] = $this->diafan->_captcha->get($tag, '', true);
			$this->result["result"] = true;
		}
	}

	/**
	 * Обновляет значение полей формы в базу данных
	 *
	 * @param array $config настройки функции: id номер элемента, table таблица, params поля формы, prefix префикс, multilang значения переводятся, no_empty_param_ids массив не требующих заполнения параметров
	 * @return void
	 */
	protected function update_values($config)
	{
		if(empty($config["id"]) || empty($config["table"]) || empty($config["params"]))
		{
			return;
		}
		$id =  $config["id"];
		$table =  $config["table"];
		$params =  $config["params"];
		$prefix = ! empty($config["prefix"]) ? $config["prefix"] : '';
		$rel = ! empty($config["rel"]) ? $config["rel"] : 'element';
		$no_empty_param_ids = ! empty($config["no_empty_param_ids"]) ? $config["no_empty_param_ids"] : array();
		foreach ($params as $row)
		{
			// добавляем, удаляем файлы
			if($row["type"] == "attachments")
			{
				$rs = DB::query_fetch_all("SELECT id FROM {attachments} WHERE module_name='%s' AND element_id=%d AND param_id=%d", $table, $id, $row["id"]);
				foreach ($rs as $r)
				{
					if (! empty($_POST["attachment_delete"]) && in_array($r["id"], $_POST["attachment_delete"]))
					{
						$this->diafan->_attachments->delete($id, $table, $r["id"]);
					}
				}

				if (! empty($_FILES[$prefix.'attachments'.$row["id"]]))
				{
					$row_config = unserialize($row["config"]);
					$row_config["param_id"] = $row["id"];
					$row_config["prefix"] = $prefix;

					try
					{
						$result_upload = $this->diafan->_attachments->save($id, $table, $row_config);
					}
					catch(Exception $e)
					{
						Dev::$exception_field = $prefix."p".$row["id"];
						Dev::$exception_result = $this->result;
						throw new Action_exception($e->getMessage());
					}
					if($result_upload)
					{
						if(! $row_config["attachments_access_admin"])
						{
							$attachments = $this->diafan->_attachments->get($id, $table, $row["id"]);
							$this->result["attachments"][$prefix."attachments".$row["id"]."[]"] = $this->diafan->_tpl->get('attachments', $this->diafan->current_module, array("rows" => $attachments, "prefix" => $prefix, "param_id" => $row["id"], "use_animation" => $row_config["use_animation"]));
						}
						else
						{
							$this->result["attachments"][$prefix."attachments".$row["id"]."[]"] = "";
						}
					}
				}
			}

			if (empty($_POST[$prefix."p".$row["id"]]))
				continue;

			if(! empty($config["multilang"]))
			{
				if(in_array($row["type"], array('text', 'textarea', 'editor')))
				{
					$value_name = '[value]';
				}
				else
				{
					$value_name = 'value'.$this->diafan->_languages->site;
				}
			}
			else
			{
				$value_name = 'value';
			}

			if ($row["type"] == "multiple")
			{
				DB::query("DELETE FROM {".$table."_param_".$rel."} WHERE param_id=%d AND ".$rel."_id=%d", $row["id"], $id);
				foreach ($_POST[$prefix."p".$row["id"]] as $val)
				{
					DB::query("INSERT INTO {".$table."_param_".$rel."} (".$value_name.", param_id, ".$rel."_id) VALUES ('%h', '%d', '%d')", $val, $row["id"], $id);
				}
			}
			elseif ($row_id = DB::query_result("SELECT id FROM {".$table."_param_".$rel."} WHERE param_id=%d AND ".$rel."_id=%d LIMIT 1", $row["id"], $id))
			{
				if ($row["type"] == "date")
				{
					$_POST[$prefix."p".$row["id"]] = $this->diafan->formate_in_date($_POST[$prefix."p".$row["id"]]);
				}
				elseif ($row["type"] == "datetime")
				{
					$_POST[$prefix."p".$row["id"]] = $this->diafan->formate_in_datetime($_POST[$prefix."p".$row["id"]]);
				}
				DB::query("UPDATE {".$table."_param_".$rel."} SET ".$value_name."='%s' WHERE id=%d", nl2br(htmlspecialchars($_POST[$prefix."p".$row["id"]])), $row_id);
			}
			else
			{
				DB::query("INSERT INTO {".$table."_param_".$rel."} (".$value_name.", param_id, ".$rel."_id) VALUES ('%s', %d, %d)", htmlspecialchars($_POST[$prefix."p".$row["id"]]), $row["id"], $id);
			}
			$no_empty_param_ids[] = $row["id"];
		}
		DB::query("DELETE FROM {".$table."_param_".$rel."} WHERE ".$rel."_id=%d".($no_empty_param_ids ? " AND param_id NOT IN (".implode(",", $no_empty_param_ids).")" : ''), $id);
	}

	/**
	 * Проверка e-mail на валидность
	 *
	 * @param string $email e-mail
	 * @param string $field название поля в массиве $_POST
	 * @return boolean
	 */
	protected function valid_email($email, $field)
	{
		if ($email)
		{
			Custom::inc('includes/validate.php');
			$mes = Validate::mail($email);
			if ($mes)
			{
				$this->result["errors"][$field] = $this->diafan->_($mes, false);
				return true;
			}
		}
		return false;
	}

	/**
	 * Проверка телефона на валидность
	 *
	 * @param string $phone телефон
	 * @param string $field название поля в массиве $_POST
	 * @return boolean
	 */
	protected function valid_phone($phone, $field)
	{
		if ($phone)
		{
			Custom::inc('includes/validate.php');
			$mes = Validate::phone($phone);
			if ($mes)
			{
				$this->result["errors"][$field] = $this->diafan->_($mes, false);
				return true;
			}
		}
		return false;
	}

	/**
	 * Проверка ссылки на валидность
	 *
	 * @param string $url ссылка
	 * @param string $field название поля в массиве $_POST
	 * @return boolean
	 */
	protected function valid_url($url, $field)
	{
		if ($url)
		{
			Custom::inc('includes/validate.php');
			if(! Validate::url($url, true))
			{
				$this->result["errors"][$field] = $this->diafan->_('Некорректная ссылка.', false);
				return true;
			}
		}
		return false;
	}

	/**
	 * Задает неопределенным атрибутам шаблонного тега значение по умолчанию
	 *
	 * @param array $attributes массив определенных атрибутов
	 * @return array
	 */
	protected function get_attributes(&$attributes)
	{
		$a = func_get_args();
		for ($i = 1; $i < count($a); $i++)
		{
			if (is_array($a[$i]))
			{
				$name = $a[$i][0];
				$value = $a[$i][1];
			}
			else
			{
				$name = $a[$i];
				$value = '';
			}
			if (empty($attributes[$name]))
			{
				$attributes[$name] = $value;
			}
		}
		return $attributes;
	}
}

/**
 * Defer_action
 *
 * Класс для обработки POST-запросов отложенной загрузки
 */
class Defer_action extends Action
{
	/**
	 * Обрабатывает полученные данные из формы
	 *
	 * @return void
	 */
	public function action()
	{
		$this->result["data"] = array();
		if($attributes = isset($_POST["defer"]) && ! empty($_POST["attributes"]) && is_array($_POST["attributes"]) ? $_POST["attributes"] : false)
		{
			if(! empty($_POST["ajax_async"]))
			{
				foreach($attributes as $key => $value)
				{
					$key = preg_replace('/[^a-zA-Z0-9_]/', '', $key);
					$this->result["data"]['form[defer_id="'.$key.'"]'] = $this->start_element($value);
				}
			}
			else
			{
				$this->result["data"]['form'] = $this->start_element($attributes);
			}
		}
		else
		{
			$this->result["data"]['form'] = '';
		}
		$this->result['replace'] = true;
	}

	/**
	 * Выполняет действие, заданное в шаблонном тэге: выводит информацию или подключает шаблонную функцию
	 *
	 * @param array атрибуты шаблонного тега
	 * @return void
	 */
	private function start_element($attributes)
	{
		$result = '';
		if($attributes = ! empty($attributes) && is_array($attributes) ? $attributes : false)
		{
			$attrs = array();
			foreach($attributes as $key => $value)
			{
				$key = preg_replace('/[^a-zA-Z0-9_]/', '', $key);
				$value = htmlspecialchars(stripslashes(trim($value)));
				$attrs[$key] = $value;
			}

			if(! empty($attrs["module"]) && $this->is_module($attrs["module"]))
			{
				ob_start();
				$this->diafan->_parser_theme->start_element($attrs);
				if($c = count(Dev::$errors))
				{
					Dev::print_errors();
					echo '
					<style type="text/css">
					body {
					    border-bottom: 60px solid transparent;
					}
					</style>
					';
				}
				$result = ob_get_contents();
				ob_end_clean();

				if($c)
				{
					if(! MOD_DEVELOPER || defined('MOD_DEVELOPER_ADMIN') && MOD_DEVELOPER_ADMIN && empty($_COOKIE['dev']))
					{
						$result = '';
					}
				}
				$result = htmlspecialchars_decode($result);
			}
		}
		return $result;
	}

	/**
	 * Проверяет название модуля
	 *
	 * @param string $module_name модуль
	 * @return boolean
	 */
	private function is_module($module_name)
	{
		//if(preg_match("/_admin$/i", $module_name))
		//return false;

		return ($module_name && in_array($module_name, $this->diafan->installed_modules));
	}
}

/**
 * More_action
 *
 * Класс для обработки POST-запросов загрузки дополнительных элементов страницы
 */
class More_action extends Action
{
	/**-
	 * Обрабатывает полученные данные из формы
	 *
	 * @return void
	 */
	public function action()
	{
		if(! empty($_POST["more"]) && isset($_POST["mode"]) && $_POST["mode"] == 'model')
		{
			$this->diafan->module->init();
			$this->diafan->module->model->result["more"] = true;

			$error_404 = false;
			foreach ($this->diafan->_route->variable_names_site as $name)
			{
				if ($this->diafan->_route->$name && ! in_array($name, $this->diafan->module->rewrite_variable_names) && ! in_array($name, $this->_route->rewrite_variable_names))
				{
					$error_404 = true;
					break;
				}
			}

			$uid = ! empty($_POST["uid"]) ? $_POST["uid"] : false;

			$result = ''; $paginator = false;
			if(! $error_404)
			{
				$view = $this->diafan->module->model->result["view"];
				if(! empty($this->diafan->module->model->result["view_rows"]))
				{
					$view = $this->diafan->module->model->result["view_rows"];
				}
				if($uid !== false && isset($this->diafan->module->model->result["paginator"]))
				{
					$paginator = $this->diafan->module->model->result["paginator"];
					unset($this->diafan->module->model->result["paginator"]);
				}

				$this->diafan->module->model->result['ajax'] = true;
				$result = $this->diafan->_tpl->get($view, $this->diafan->current_module, $this->diafan->module->model->result);
				$this->result['set_location'] = true;
			}

			$this->result['data'] = array(
				'form' => $result,
			);

			if($paginator !== false && $uid !== false)
			{
				$this->result['data'][".paginator[uid='".$uid."']"] = $paginator;
			}
		}
		else
		{
			$this->result["data"]['form'] = '';
		}
		$this->result['replace'] = true;
	}
}

/**
 * Action_exception
 *
 * Исключение для обработки POST-запросов
 */
class Action_exception extends Exception{}

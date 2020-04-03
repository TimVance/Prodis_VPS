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
 * Model
 *
 * Каркас для модели модуля
 */
abstract class Model extends Diafan
{
	/**
	 * @var array сгенерированные в моделе данные, передаваемые в шаблон
	 */
	public $result;

	/**
	 * Проверяет есть ли доступ к элементу/категории модуля
	 *
	 * @param integer $element_id номер элемента
	 * @param string $module_name модуль
	 * @param string $element_type тип данных (*element* – элемент (по умолчанию), *cat* – категория)
	 * @return boolean
	 */
	protected function access($element_id, $module_name = '', $element_type = 'element')
	{
		if (! $module_name)
		{
			$module_name = $this->diafan->_site->module;
		}

		if(! $this->diafan->configmodules('where_access_'.$element_type, $module_name))
		{
			return false;
		}

		return (bool)DB::query_result("SELECT id FROM {access} WHERE element_id=%d AND module_name='%s' AND element_type='%s' AND role_id=%d LIMIT 1", $element_id, $module_name, $element_type, $this->diafan->_users->role_id);
	}

	/**
	 * Форматирует дату в соответствии с конфигурацией модуля
	 *
	 * @param integer $date дата в формате UNIX
	 * @param string $module_name название модуля, по умолчанию модуль, прикрепленный к текущей странице
	 * @param integer $site_id номер страницы сайта
	 * @return string
	 */
	public function format_date($date, $module_name = '', $site_id = 0)
	{
		$months_array = array(
			'01' => $this->diafan->_('января'),
			'02' => $this->diafan->_('февраля'),
			'03' => $this->diafan->_('марта'),
			'04' => $this->diafan->_('апреля'),
			'05' => $this->diafan->_('мая'),
			'06' => $this->diafan->_('июня'),
			'07' => $this->diafan->_('июля'),
			'08' => $this->diafan->_('августа'),
			'09' => $this->diafan->_('сентября'),
			'10' => $this->diafan->_('октября'),
			'11' => $this->diafan->_('ноября'),
			'12' => $this->diafan->_('декабря')
		);
		$week_array = array(
			'1' => $this->diafan->_('понедельник'),
			'2' => $this->diafan->_('вторник'),
			'3' => $this->diafan->_('среда'),
			'4' => $this->diafan->_('четверг'),
			'5' => $this->diafan->_('пятница'),
			'6' => $this->diafan->_('суббота'),
			'0' => $this->diafan->_('воскресенье')
		);
		if (!$module_name)
		{
			$module_name = $this->diafan->_site->module;
		}
		if (!$site_id)
		{
			$site_id = $this->diafan->_site->id;
		}

		$config_format = $this->diafan->configmodules("format_date", $module_name, $site_id);

		switch ($config_format)
		{
			case 1:
			return date("d ", $date).$months_array[date("m", $date)].date(" Y ", $date).' '.$this->diafan->_('г.');

			case 2:
			return date("d ", $date).$months_array[date("m", $date)];

			case 3:
			return date("d ", $date).$months_array[date("m", $date)].date(" Y, ", $date).$week_array[date("w", $date)];

			case 4:
			return '';

			case 5:
			return $this->format_date_5($date);

			case 6:
			return date("d.m.Y H:i", $date);

			default:
			return date("d.m.Y", $date);
		}
	}

	/**
	 * Получает имя, никнейм и аватар пользователя сайта
	 *
	 * @param integer $user_id номер пользователя сайта
	 * @return array
	 */
	public function get_author($user_id)
	{
		if (! $user_id)
			return $this->diafan->_('Гость');

		$this->prepare_author($user_id);

		if (! empty($this->cache["prepare_authors"]))
		{
			foreach ($this->cache["prepare_authors"] as $i => $author)
			{
				if(! $author || isset($this->cache["authors"][$author]))
				{
					unset($this->cache["prepare_authors"][$i]);
				}
			}
			if(! empty($this->cache["prepare_authors"]))
			{
				$user_page = $this->diafan->_route->module("userpage");
				$rows = DB::query_fetch_all("SELECT id, fio, name, identity FROM {users} WHERE act='1' AND trash='0' AND id IN (%s)", implode(",", $this->cache["prepare_authors"]));
				foreach ($rows as $row)
				{
					if ($this->diafan->configmodules("avatar", "users"))
					{
						$row["avatar"] = '';
						if(file_exists(ABSOLUTE_PATH.USERFILES.'/avatar/'.$row["name"].'.png'))
						{
							$row["avatar"] = BASE_PATH.USERFILES.'/avatar/'.$row["name"].'.png';
						}
						elseif($this->diafan->configmodules("avatar_none", "users"))
						{
							$row["avatar"] = BASE_PATH.USERFILES.'/avatar_none.png';
						}
						$row["avatar_width"] = $this->diafan->configmodules("avatar_width", "users");
						$row["avatar_height"] = $this->diafan->configmodules("avatar_height", "users");
					}

					if($row["identity"])
					{
						$row["user_page"] = $row["identity"];
					}
					elseif($user_page)
					{
						$row["user_page"] = BASE_PATH_HREF.$user_page.'?name='.urlencode($row['name']);
					}
					if($this->diafan->configmodules("mail_as_login", "users"))
					{
						$row["name"] = '';
					}
					$this->cache["authors"]['a'.$row["id"]] = $row;
				}
				foreach($this->cache["prepare_authors"] as $id)
				{
					if(! isset($this->cache["authors"]['a'.$id]))
					{
						$this->cache["authors"]['a'.$id] = false;
					}
				}
				unset($this->cache["prepare_authors"]);
			}
		}
		return ! empty($this->cache["authors"]['a'.$user_id]) ? $this->cache["authors"]['a'.$user_id] : $this->diafan->_('Гость');
	}

	/**
	 * Запоминает идентификаторы пользователей сайта, информация о которых понадобиться
	 *
	 * @param integer $author идентификатор автора
	 * @return void
	 */
	protected function prepare_author($author)
	{
		if(! $author)
		{
			return;
		}
		if(isset($this->cache["authors"]['a'.$author]))
		{
			return;
		}
		if(empty($this->cache["prepare_authors"]) || ! in_array($author, $this->cache["prepare_authors"]))
		{
			$this->cache["prepare_authors"][] = $author;
		}
	}

	/**
	 * Осуществляет умное форматирование даты
	 *
	 * @param integer $date дата в формет UNIX
	 * @return string
	 */
	private function format_date_5($date)
	{
		if (! $date)
			return '';

		$months_array = array(
			'01' => $this->diafan->_('января'),
			'02' => $this->diafan->_('февраля'),
			'03' => $this->diafan->_('марта'),
			'04' => $this->diafan->_('апреля'),
			'05' => $this->diafan->_('мая'),
			'06' => $this->diafan->_('июня'),
			'07' => $this->diafan->_('июля'),
			'08' => $this->diafan->_('августа'),
			'09' => $this->diafan->_('сентября'),
			'10' => $this->diafan->_('октября'),
			'11' => $this->diafan->_('ноября'),
			'12' => $this->diafan->_('декабря')
		);
		$week_array = array(
			'1' => $this->diafan->_('понедельник'),
			'2' => $this->diafan->_('вторник'),
			'3' => $this->diafan->_('среда'),
			'4' => $this->diafan->_('четверг'),
			'5' => $this->diafan->_('пятница'),
			'6' => $this->diafan->_('суббота'),
			'0' => $this->diafan->_('воскресенье')
		);
		if (time() - $date < 3600)
		{
			$min = round((time() - $date) / 60);
			if ($min < 2)
			{
			return $this->diafan->_('1 минуту назад');
			}
			if ($min % 10 == 1 && $min > 20)
			{
			return $this->diafan->_('%s минуту назад', false, $min);
			}
			if ($min % 10 < 5 && ($min > 20 || $min < 10))
			{
			return $this->diafan->_('%s минуты назад', false, $min);
			}
			return $this->diafan->_('%s минут назад', false, $min);
		}

		if ($date >= mktime(0, 0, 0, date("m"), date("d"), date("Y")))
		{
			return  $this->diafan->_('Сегодня').', '.date("H:i", $date);
		}

		if ($date >= mktime(0, 0, 0, date("m"), date("d"), date("Y")) - 86400)
		{
			return $this->diafan->_('Вчера').', '.date("H:i", $date);
		}

		if ($date >= time() - 86400 * 30)
		{
			return date("d ", $date).$months_array[date("m", $date)].', '.$week_array[date("w", $date)];
		}

		if (date("Y", $date) == date("Y"))
		{
			return date("d ", $date).$months_array[date("m", $date)];
		}
		return date("d ", $date).$months_array[date("m", $date)].date(" Y ", $date).' '.$this->diafan->_('г.');
	}

	/**
	 * Ищет ошибки и сообщения, передаваемые в виде GET-переменных
	 *
	 * @param array $result массив данных, в который буду записаны найденные ошибки
	 * @param string $tag тег-идентификатор формы
	 * @param array $fields поля
	 * @return void
	 */
	protected function form_errors(&$result, $tag, $fields)
	{
		$empty = false;

		if (empty($_GET["form_tag"]) || $_GET["form_tag"] != $tag)
		{
			$empty = true;
		}

		if (! $empty && ! getenv("HTTP_REFERER"))
		{
			$empty = true;
		}

		if (! $empty)
		{
			$ref = parse_url(getenv("HTTP_REFERER"));
			if ($ref["host"] != getenv("HTTP_HOST"))
			{
				$empty = true;
			}
		}

		if($empty)
		{
			foreach($fields as $field)
			{
				$result['error'.($field ? '_'.$field : '')] = '';
			}
			return;
		}
		foreach($fields as $field)
		{
			$field = ($field ? '_'.$field : '');
			if(! empty($_GET['mess'.$field]))
			{
				$result['error'.$field] = $this->diafan->filter($_GET, 'string', 'mess'.$field);
			}
			else
			{
				$result['error'.$field] = '';
			}
		}
	}

	/**
	 * Получает массив полей формы
	 *
	 * @param array $config настройки функции: module модуль, table таблица, where условие для SQL-запроса
	 * @return array
	 */
	public function get_params($config)
	{
		if (! empty($config["module"]))
		{
			$module = $config["module"];
		}
		else
		{
			$module = $this->diafan->_site->module;
		}
		if (! empty($config["table"]))
		{
			$table = $config["table"];
		}
		else
		{
			$table = $module;
		}
		$fields = "";
		if (! empty($config["fields"]))
		{
			if(is_array($config["fields"]))
			{
				$fields =  ", ".implode(",", $config["fields"]);
			}
			else
			{
				$fields =  ", ".$config["fields"];
			}
		}

		$where = "";
		if (! empty($config["where"]))
		{
			$where = " AND ".$config["where"];
		}

		$cache_meta = array(
			"name" => $module."_param",
			"lang_id" => _LANG,
			"where" => $where
		);
		if (! $rows = $this->diafan->_cache->get($cache_meta, $module))
		{
			$rows = DB::query_fetch_all("SELECT id, [name], type, required, [text], config".$fields." FROM {".$table."_param} WHERE trash='0'".$where." ORDER BY sort ASC");
			foreach ($rows as &$row)
			{
				if ($row["type"] == 'select' || $row["type"] == 'multiple' || $row["type"] == 'radio' || $row["type"] == 'checkbox')
				{
					$row["select_array"] = DB::query_fetch_all("SELECT [name], id, value FROM {".$table."_param_select} WHERE param_id=%d ORDER BY sort ASC", $row["id"]);
					foreach ($row["select_array"] as $row_select)
					{
						$row["select_values"][$row["type"] == 'checkbox' ? $row_select["value"] : $row_select["id"]] = $row_select["name"];
					}
				}
				if($row["type"] == 'attachments')
				{
					$config = unserialize($row["config"]);
					$row["max_count_attachments"] = ! empty($config["max_count_attachments"]) ? $config["max_count_attachments"] : 0;
					$row["attachments_access_admin"] = ! empty($config["attachments_access_admin"]) ? $config["attachments_access_admin"] : 0;
					$row["attachment_extensions"] = ! empty($config["attachment_extensions"]) ? $config["attachment_extensions"] : '';
					$row["use_animation"] = ! empty($config["use_animation"]) ? true : false;
				}
			}
			//сохранение кеша
			$this->diafan->_cache->save($rows, $cache_meta, $module);
		}
		return $rows;
	}

	/**
	 * Генерирует данные для навигации "Хлебные крошки"
	 *
	 * @return array
	 */
	protected function get_breadcrumb()
	{
		$breadcrumb = array();
		if ($this->diafan->_route->cat || $this->diafan->_route->param || $this->diafan->_route->brand || $this->diafan->_route->show || ! empty($_GET["action"])
			|| $this->diafan->_route->year)
		{
			$breadcrumb[] = array("link" => $this->diafan->_route->link($this->diafan->_site->id), "name" => $this->diafan->_site->name);
		}

		if ($this->diafan->_route->cat)
		{
			$parents = $this->diafan->get_parents($this->diafan->_route->cat, $this->diafan->_site->module.'_category');

			if ($this->diafan->_route->show || $this->diafan->_route->brand || isset($_GET["action"]))
			{
				$parents[] = $this->diafan->_route->cat;
			}
			else
			{
				$this->diafan->_site->module_parents = $parents;
			}
			if (! empty($parents))
			{
				$rparents = DB::query_fetch_key("SELECT id, [name], site_id, parent_id FROM {".$this->diafan->_site->module."_category} WHERE id IN (%s) AND [act]='1'", implode(',', $parents), "parent_id");
				$i = 0;
				while(! empty($rparents[$i]))
				{
					$row = $rparents[$i];
					unset($rparents[$i]);
					$i = $row["id"];
					$breadcrumb[] = array("id" => $row["id"], "link" => $this->diafan->_route->link($row["site_id"], $row["id"], $this->diafan->_site->module, 'cat'), "name" => $row["name"]);
				}
			}
		}
		return $breadcrumb;
	}

	/**
	 * Валидация атрибутов cat_id и site_id для шаблонных тегов
	 *
	 * @param string $module_name название модуля
	 * @param array $site_ids страница сайта
	 * @param array $cat_ids категория
	 * @param array $minus страницы сайта и категории, которые вычитаются
	 * @return boolean
	 */
	protected function validate_attribute_site_cat($module_name, &$site_ids, &$cat_ids, &$minus)
	{
		if (! empty($cat_ids) && count($cat_ids) == 1 && empty($cat_ids[0]))
		{
			$cat_ids = array();
		}
		if (! empty($site_ids) && count($site_ids) == 1 && empty($site_ids[0]))
		{
			$site_ids = array();
		}
		if (! empty($cat_ids))
		{
			$new_site_ids = array();
			$new_cat_ids = array();
			foreach ($cat_ids as $cat_id)
			{
				if(substr($cat_id, 0, 1) == '-')
				{
					$cat_id = substr($cat_id, 1);
					if(preg_replace('/[^0-9]+/', '', $cat_id) != $cat_id)
					{
						$this->error_insert_tag('Атрибут cat_id="%s" задан неверно. Номер категории %s должен быть числом.', $module_name, implode(',', $cat_ids), $cat_id);
						return false;
					}
					$minus["cat_ids"][] = $cat_id;
					continue;
				}
				$cat_id = trim($cat_id);
				if(preg_replace('/[^0-9]+/', '', $cat_id) != $cat_id)
				{
					$this->error_insert_tag('Атрибут cat_id="%s" задан неверно. Номер категории %s должен быть числом.', $module_name, implode(',', $cat_ids), $cat_id);
					return false;
				}
				elseif(in_array($cat_id, $new_cat_ids))
				{
					$this->error_insert_tag('Атрибут cat_id="%s" задан неверно. Повторяется категория %s.', $module_name, implode(',', $cat_ids), $cat_id);
					return false;
				}
				else
				{
					$new_cat_ids[] = $cat_id;
				}
			}
			$cat_ids = $new_cat_ids;
			$new_cat_ids = array();
			$isset_cat_ids = array();
			if($cat_ids)
			{
				$rows = DB::query_fetch_all("SELECT id, access, site_id, trash FROM {%h_category} WHERE id IN (%h)", $module_name, implode(",", $cat_ids));
				foreach ($rows as $row)
				{
					if(! $this->diafan->configmodules("cat", $module_name, $row["site_id"]))
					{
						$this->error_insert_tag('Атрибут cat_id="%s" задан неверно. Категории не подключены в настроках модуля.', $module_name, implode(',', $cat_ids), $row["id"]);
						return false;
					}
					if($row["trash"])
					{
						$this->error_insert_tag('Атрибут cat_id="%s" задан неверно. Категория %d удалена.', $module_name, implode(',', $cat_ids), $row["id"]);
						return false;
					}
					$isset_cat_ids[] = $row["id"];

					if($row["access"] && ! $this->access($row["id"], $module_name, 'cat'))
						continue;

					if(! in_array($row["id"], $new_cat_ids))
					{
						$new_cat_ids[] = $row["id"];
					}
					if ($this->diafan->configmodules("children_elements", $module_name, $row["site_id"]))
					{
						$cats = $this->diafan->get_children($row["id"], $module_name."_category");
						$new_cat_ids = array_merge($new_cat_ids, $cats);
					}
					if(! in_array($row["site_id"], $new_site_ids))
					{
						$new_site_ids[] = $row["site_id"];
					}
				}
				// нет доступа к категориям для текущего пользователя
				if(! $new_cat_ids)
				{
					return false;
				}
				foreach ($cat_ids as $cat_id)
				{
					if(! in_array($cat_id, $isset_cat_ids))
					{
						$this->error_insert_tag('Атрибут cat_id="%s" задан неверно. Категория %s не существует.', $module_name, implode(',', $cat_ids), $cat_id);
						return false;
					}
				}
				$cat_ids = $new_cat_ids;
				$site_ids = $new_site_ids;
				return true;
			}
		}
		$new_site_ids = array();
		foreach ($site_ids as $site_id)
		{
			if(substr($site_id, 0, 1) == '-')
			{
				$site_id = substr($site_id, 1);
				if(preg_replace('/[^0-9]+/', '', $site_id) != $site_id)
				{
				$this->error_insert_tag('Атрибут site_id="%s" задан неверно. Страницы с подключенным модулем с таким номером не существует.', $module_name, implode(',', $site_ids));
					return false;
				}
				$minus["site_ids"][] = $site_id;
				continue;
			}
			$new_site_ids[] = $site_id;
		}
		$site_ids = $new_site_ids;
		if(! $new_site_ids = $this->diafan->_route->id_module($module_name, $site_ids))
		{
			if($site_ids)
			{
				$this->error_insert_tag('Атрибут site_id="%s" задан неверно. Страницы с подключенным модулем с таким номером не существует.', $module_name, implode(',', $site_ids));
			}
			else
			{
				$this->error_insert_tag('Страницы с подключенным модулем не существует.', $module_name);
			}
			return false;
		}
		else
		{
			if(! $site_ids && ! empty($minus["site_ids"]))
			{
				foreach ($new_site_ids as $i => $site_id)
				{
					if(in_array($site_id, $minus["site_ids"]))
					{
						unset($new_site_ids[$i]);
					}
				}
			}
			if(! $new_site_ids)
			{
				$this->error_insert_tag('Страницы с подключенным модулем (кроме исключенных) не существует.', $module_name);
			}
			$site_ids = $new_site_ids;
		}
		return true;
	}

	/**
	 * Выводит ошибку на сайте
	 *
	 * @param string $error описание ошибки
	 * @param string $module_name название модуля
	 * @return void
	 */
	protected function error_insert_tag($error, $module_name)
	{
		if(! MOD_DEVELOPER)
			return;

		$args = func_get_args();
		unset($args[0]);
		unset($args[1]);
		$error = $this->diafan->_languages->get($error, $module_name, false, $args);
		Dev::$errors[] = array('insert tag', $error, htmlentities($this->diafan->current_insert_tag));

		$c = count(Dev::$errors);
		echo '<a href="#error'.$c.'" style="color:red">[ERROR#'.$c.']</a>';
	}

	/**
	 * Определяет шаблоны страницы и модуля для элемента
	 *
	 * @return void
	 */
	protected function theme_view()
	{
		if($this->diafan->configmodules("theme_list"))
		{
			$this->result["theme"] = $this->diafan->configmodules("theme_list");
		}
		if($this->diafan->configmodules("view_list"))
		{
			$this->result["view"] = $this->diafan->configmodules("view_list");
		}
		else
		{
			$this->result["view"] = 'list';
		}
		if($this->diafan->configmodules("view_list_rows"))
		{
			$this->result["view_rows"] = $this->diafan->configmodules("view_list_rows");
		}
		else
		{
			$this->result["view_rows"] = 'rows';
		}
	}

	/**
	 * Определяет шаблоны страницы и модуля для первой страницы модуля, если используются категории
	 *
	 * @return void
	 */
	protected function theme_view_first_page()
	{
		if($this->diafan->configmodules("theme_first_page"))
		{
			$this->result["theme"] = $this->diafan->configmodules("theme_first_page");
		}
		if($this->diafan->configmodules("view_first_page"))
		{
			$this->result["view"] = $this->diafan->configmodules("view_first_page");
		}
		else
		{
			$this->result["view"] = 'first_page';
		}
		if($this->diafan->configmodules("view_first_page_rows"))
		{
			$this->result["view_rows"] = $this->diafan->configmodules("view_first_page_rows");
		}
		else
		{
			$this->result["view_rows"] = 'first_page';
		}
	}

	/**
	 * Определяет шаблоны страницы и модуля для категории
	 *
	 * @param array $row данные о текущей категории
	 * @return void
	 */
	protected function theme_view_cat($row)
	{
		if($row["theme"])
		{
			$this->result["theme"] = $row["theme"];
		}
		elseif($this->diafan->configmodules("theme_list"))
		{
			$this->result["theme"] = $this->diafan->configmodules("theme_list");
		}

		if($row["view"])
		{
			$this->result["view"] = $row["view"];
		}
		elseif($this->diafan->configmodules("view_list"))
		{
			$this->result["view"] = $this->diafan->configmodules("view_list");
		}
		else
		{
			$this->result["view"] = 'list';
		}

		if($row["view_rows"])
		{
			$this->result["view_rows"] = $row["view_rows"];
		}
		elseif($this->diafan->configmodules("view_list_rows"))
		{
			$this->result["view_rows"] = $this->diafan->configmodules("view_list_rows");
		}
		else
		{
			$this->result["view_rows"] = 'rows';
		}
	}

	/**
	 * Определяет шаблоны страницы и модуля для элемента
	 *
	 * @param array $row данные о текущем элементе
	 * @return void
	 */
	protected function theme_view_element($row)
	{
		if($this->diafan->configmodules("cat"))
		{
			$cat = DB::query_fetch_array("SELECT theme, view_element FROM {%s_category} WHERE id=%d LIMIT 1", $this->diafan->_site->module, $row["cat_id"]);
		}

		if($row["theme"])
		{
			$this->result["theme"] = $row["theme"];
		}
		elseif($this->diafan->configmodules("theme_id"))
		{
			$this->result["theme"] = $this->diafan->configmodules("theme_id");
		}
		elseif(! empty($cat["theme"]))
		{
			$this->result["theme"] = $cat["theme"];
		}

		if(! $row["view"])
		{
			if(! empty($cat["view_element"]))
			{
				$this->result["view"] = $cat["view_element"];
			}
			elseif($this->diafan->configmodules("view_id"))
			{
				$this->result["view"] = $this->diafan->configmodules("view_id");
			}
			else
			{
				$this->result["view"] = 'id';
			}
		}
	}

	/**
	 * Определяет значения META-тегов элемента
	 *
	 * @param array $row данные о текущем элементе
	 * @return void
	 */
	protected function meta($row)
	{
		$this->result["timeedit"] = $row["timeedit"];
		$this->result["titlemodule"] = $row["name"];
		$this->result["edit_meta"]   = array("id" => $row["id"], "table" => $this->diafan->_site->module);

		if(! empty($row["canonical"]))
		{
			$this->result["canonical"] = $row["canonical"];
		}
		if(! empty($row["noindex"]))
		{
			$this->result["noindex"] = $row["noindex"];
		}

		$config_title = $this->diafan->configmodules("title_tpl");
		$config_keywords = $this->diafan->configmodules("keywords_tpl");
		$config_descr = $this->diafan->configmodules("descr_tpl");

		if($this->diafan->configmodules("cat") && (
		   ! $row["title_meta"] && strpos($config_title, '%category') !== false
		   || ! $row["keywords"] && strpos($config_keywords, '%category') !== false
		   || ! $row["descr"] && strpos($config_descr, '%category') !== false
		   || ! $row["title_meta"] && strpos($config_title, '%parent_category') !== false
		   || ! $row["keywords"] && strpos($config_keywords, '%parent_category') !== false
		   || ! $row["descr"] && strpos($config_descr, '%parent_category') !== false))
		{
			$cat = DB::query_fetch_array("SELECT parent_id, [name] FROM {%h_category} WHERE id=%d LIMIT 1", $this->diafan->_site->module, $row["cat_id"]);
			$category_name = $cat["name"];
		}
		else
		{
			$category_name = '';
		}
		if(! $row["title_meta"] && strpos($config_title, '%parent_category') !== false
		   || ! $row["keywords"] && strpos($config_keywords, '%parent_category') !== false
		   || ! $row["descr"] && strpos($config_descr, '%parent_category') !== false)
		{
			$parent_category_name = DB::query_result("SELECT [name] FROM {%h_category} WHERE id=%d LIMIT 1", $this->diafan->_site->module, $cat["parent_id"]);
		}
		else
		{
			$parent_category_name = '';
		}

		$this->result["title_meta"] = $row["title_meta"];
		if (! $row["title_meta"] && $config_title)
		{
			$this->result["title_meta"] = str_replace(
				array('%name', '%category', '%parent_category'),
				array($row["name"], $category_name, $parent_category_name),
				$config_title
			);
		}

		$this->result["keywords"] = $row["keywords"];
		if (! $row["keywords"] && $config_keywords)
		{
			$this->result["keywords"] = str_replace(
				array('%name', '%category', '%parent_category'),
				array($row["name"], $category_name, $parent_category_name),
				$config_keywords
			);
		}

		$this->result["descr"] = $row["descr"];
		if (! $row["descr"] && $config_descr)
		{
			$this->result["descr"] = str_replace(
				array('%name', '%category', '%parent_category', '%anons'),
				array($row["name"], $category_name, $parent_category_name, (! empty($row["anons"]) ? strip_tags($row["anons"]) : '')),
				$config_descr
			);
		}
	}

	/**
	 * Определяет значения META-тегов категории
	 *
	 * @param array $row данные о текущей категории
	 * @return void
	 */
	protected function meta_cat($row)
	{
		$this->result["timeedit"] = $row["timeedit"];
		$this->result["titlemodule"] = $row["name"];
		$this->result["edit_meta"]   = array("id" => $row["id"], "table" => $this->diafan->_site->module."_category");

		if(! empty($row["canonical"]))
		{
			$this->result["canonical"] = $row["canonical"];
		}
		if(! empty($row["noindex"]))
		{
			$this->result["noindex"] = $row["noindex"];
		}

		$config_title = $this->diafan->configmodules("title_tpl_cat");
		$config_keywords = $this->diafan->configmodules("keywords_tpl_cat");
		$config_descr = $this->diafan->configmodules("descr_tpl_cat");

		if(! $row["title_meta"] && !(strpos($config_title, '%parent')===false)
		   || ! $row["keywords"] && !(strpos($config_keywords, '%parent')===false)
		   || ! $row["descr"] && !(strpos($config_descr, '%parent')===false))
		{
			$parent_name = DB::query_result("SELECT [name] FROM {%h_category} WHERE id=%d LIMIT 1", $this->diafan->_site->module, $row["parent_id"]);
		}
		else
		{
			$parent_name = '';
		}

		$this->result["title_meta"] = $row["title_meta"];
		if (! $row["title_meta"] && $config_title)
		{
			if($this->diafan->_route->page > 1)
			{
				$page = $this->diafan->_(' — Страница %d', false, $this->diafan->_route->page);
			}
			else
			{
				$page = '';
			}
			$this->result["title_meta"] = str_replace(
				array('%name', '%parent', '%page'),
				array($row["name"], $parent_name, $page),
				$config_title
			);
		}

		$this->result["keywords"] = $row["keywords"];
		if (! $row["keywords"] && $config_keywords)
		{
			$this->result["keywords"] = str_replace(
				array('%name', '%parent'),
				array($row["name"], $parent_name),
				$config_keywords
			);
		}

		$this->result["descr"] = $row["descr"];
		if (! $row["descr"] && $config_descr)
		{
			$this->result["descr"] = str_replace(
				array('%name', '%parent', '%anons'),
				array($row["name"], $parent_name, (! empty($row["anons"]) ? strip_tags($row["anons"]) : '')),
				$config_descr
			);
		}
	}

	/**
	 * Счетчик просмотров элемента
	 *
	 * @return void
	 */
	protected function counter_view()
	{
		if($this->diafan->configmodules('counter'))
		{
			$counter = DB::query_fetch_array("SELECT id, count_view FROM {%s_counter} WHERE element_id=%d LIMIT 1", $this->diafan->_site->module, $this->diafan->_route->show);
			if($counter)
			{
				if(empty($_SESSION[$this->diafan->_site->module."_view"][$this->diafan->_route->show]))
				{
					$_SESSION[$this->diafan->_site->module."_view"][$this->diafan->_route->show] = 1;
					DB::query("UPDATE {%s_counter} SET count_view=%d WHERE id=%d LIMIT 1", $this->diafan->_site->module, ++$counter["count_view"], $counter["id"]);
				}
			}
			else
			{
				DB::query("INSERT INTO {%s_counter} (count_view, element_id) VALUES (1, %d)", $this->diafan->_site->module, $this->diafan->_route->show);
				$counter["count_view"] = 1;
				$_SESSION[$this->diafan->_site->module."_view"][$this->diafan->_route->show] = 1;
			}
			if($this->diafan->configmodules('counter_site'))
			{
				$this->result["counter"] = $counter["count_view"];
			}
		}
	}

	/**
	 * Проверяет является ли текущий пользователь администратором
	 *
	 * @return void
	 */
	protected function is_admin()
	{
		if(isset($this->cache["is_admin"]))
		{
			return $this->cache["is_admin"];
		}
		$module = $this->diafan->_site->module.($this->diafan->_route->cat ? '/category' : '');
		$this->cache["is_admin"] = $this->diafan->_users->id && $this->diafan->_users->roles("init", $module, array(), 'admin');
		return $this->cache["is_admin"];
	}
}

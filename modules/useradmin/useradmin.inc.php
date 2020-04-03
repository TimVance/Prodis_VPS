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
 * Useradmin_inc
 */
class Useradmin_inc extends Model
{
	/**
	 * Генерирует ссылку на форму редактирования
	 *
	 * @param string $text значение переменной
	 * @param string $name название переменной
	 * @param integer $element_id номер элемента
	 * @param string $table_name таблица
	 * @param integer $lang_id номер языка
	 * @param string $type тип данных
	 * @return string
	 */
	public function get($text, $name, $element_id, $table_name, $lang_id = 0, $type = '')
	{
		if (!$text)
		{
			return $text;
		}
		list($module_name) = explode('_', $table_name);

		if ($this->diafan->_users->useradmin != 1 || ! $this->diafan->_users->roles('edit', $module_name))
		{
			return $text;
		}

		$result["text"] = $text;
		$result["name"] = $name;
		$result["element_id"] = $element_id;
		$result["module_name"] = $table_name;
		$result["is_lang"] = false;
		if ($type)
		{
			$result["type"] = $type;
		}
		else
		{
			$result["type"] = $this->type($name);
		}

		$result["lang_id"] = $lang_id;
		$text = $this->diafan->_tpl->get('get', 'useradmin', $result);
		return $text;
	}

	/**
	 * Генерирует ссылку на форму редактирования перевода
	 *
	 * @param string $value текущий перевод
	 * @param string $name строка для перевода
	 * @param string $module_name модуль
	 * @return string
	 */
	public function get_lang($value, $name, $module_name)
	{
		if (IS_ADMIN || $this->diafan->_users->useradmin != 1 || !$this->diafan->_users->roles('edit', "languages"))
		{
			return $value;
		}
		$result["name"] = urlencode($name);
		$result["text"] = $value;
		$result["element_id"] = 0;
		$result["lang_module_name"] = $module_name;
		$result["module_name"] = "languages";
		$result["is_lang"] = true;
		$result["type"] = "text";
		$result["lang_id"] = _LANG;
		$text = $this->diafan->_tpl->get('get', 'useradmin', $result);
		return $text;
	}

	/**
	 * Получает ссылки для редактирования мета-данных через панель администрирования
	 *
	 * @param integer $element_id номер элемента
	 * @param string $module_name модуль
	 * @return array|boolean false
	 */
	public function get_meta($element_id, $module_name)
	{
		if ($this->diafan->_users->useradmin != 1 || ! $this->diafan->_users->roles('edit', $module_name))
		{
			return false;
		}
		$names = array('title_meta', 'descr', 'keywords');
		foreach($names as $name)
		{
			$links[$name] = BASE_PATH
			.'useradmin/edit/?module_name='.$module_name
			.'&amp;name='.$name
			.'&amp;element_id='.$element_id
			.'&amp;lang_id='._LANG
			.'&amp;type='.($name == 'title_meta' ? 'text' : 'textarea')
			.'&amp;iframe=true'
			.'&amp;width=800&amp;height=400'
			.'&amp;rand='.rand(0, 999);
		}
		return $links;
	}

	/**
	 * Генерирует данные для формы редактирования
	 *
	 * @return void
	 */
	public function edit()
	{
		if (empty($_GET["module_name"]) || empty($_GET["name"]))
		{
			Custom::inc('includes/404.php');
		}
		if (empty($_GET["is_lang"]) && empty($_GET["element_id"]))
		{
			Custom::inc('includes/404.php');
		}
		list($module_name) = explode('_', $_GET["module_name"]);
		if ($this->diafan->_users->useradmin != 1 || ! $this->diafan->_users->roles('edit', $module_name))
		{
			Custom::inc('includes/404.php');
		}
		$result["name"] = $this->diafan->filter($_GET, "string", "name");
		$result["module_name"] = $this->diafan->filter($_GET, "string", "module_name");
		$result["lang_id"] = $this->diafan->filter($_GET, "int", "lang_id");
		$result["is_lang"] = ! empty($_GET["is_lang"]) ? true : false;
		$result["lang_module_name"] = $this->diafan->filter($_GET, "string", "lang_module_name");
		$result["error"] = false;
		if ($result["is_lang"])
		{
			$result["type"] = "text";
			$result["type_save"] = "text";
			$result["element_id"] = 0;
			$result["name"] = $result["name"];
			$result["text"] = str_replace('"', '&quot;', $this->diafan->_languages->get(urldecode($result["name"]), $result["lang_module_name"]));
		}
		else
		{
			$result["type"] = ! empty($_GET["type"]) ? $_GET["type"] : $this->type($_GET["name"]);
			$result["type_save"] = $result["type"];
			$result["element_id"] = $this->diafan->filter($_GET, "int", "element_id");
			if($result["type"] == 'editor' &&  in_array($result["name"], explode(",", $this->diafan->configmodules("hide_".$result["module_name"]."_".$result["element_id"], "htmleditor"))))
			{
				$result["type"] = 'textarea';
			}
			$result["text"] = DB::query_result("SELECT %h".($result["lang_id"] ? $result["lang_id"] : '')." FROM {%h} WHERE id=%d LIMIT 1", $result["name"], $result["module_name"], $result["element_id"]);
		}
		if($result["type"] == 'editor')
		{
			$result["text"] = $this->diafan->_route->replace_id_to_link($result["text"]);
		}
		$user_id = DB::query_result("SELECT user_id FROM {sessions} WHERE session_id='%h' LIMIT 1", session_id());
		$pass = DB::query_result("SELECT password FROM {users} WHERE id=%d LIMIT 1", $user_id);
		$result["hash"] = md5(substr($pass, mt_rand(0, 32), mt_rand(0, 32)).mt_rand(23, 567).substr($pass, mt_rand(0, 32), mt_rand(0, 32)));

		DB::query("INSERT INTO {sessions_hash} (user_id, created, hash) VALUES (%d, %d, '%h')", $user_id, time(), $result["hash"]);

		header("Expires: ".date("r"));
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header('Content-Type: text/html; charset=utf-8');
		echo $this->diafan->_tpl->get('edit', 'useradmin', $result);
	}

	/**
	 * Возвращает тип данных по имени переменной
	 *
	 * @param string $name имя редактируемой переменной
	 * @return string
	 */
	public function type($name)
	{
		switch ($name)
		{
			case 'created':
				$type = 'date';
				break;
			case 'name':
				$type = 'text';
				break;
			default:
				$type = 'editor';
				break;
		}
		return $type;
	}
}
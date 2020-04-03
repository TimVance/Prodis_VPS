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
 * DB
 *
 * Работа с базой данных
 */
class DB
{
	const _QUERY_REGEXP = '/(%d|%s|%%|%f|%b|%h)/';

	/**
	 * @var object бэкэнд
	 */
	public static $backend;

	/**
	 * Подключается к базе данных
	 *
	 * @param string $db_url данные для подключения к базе данных
	 * @param boolean $check проверка соединения
	 * @return boolean
	 */
	public static function connect($db_url = DB_URL, $check = false)
	{
		$url = parse_url($db_url);
		try
		{
			switch($url["scheme"])
			{
				case 'mysql':
					Custom::inc('includes/database/database.mysql.php');
					self::$backend = new DB_mysql();
					break;

				case 'mysqli':
					Custom::inc('includes/database/database.mysqli.php');
					self::$backend = new DB_mysqli();
					break;

				default:
					throw new DB_exception('Ошибка подключения к базе данных, возможно неправильные параметры подключения в config.php.');
			}

			$url['user'] = urldecode($url['user']);
			if (isset($url['pass']))
			{
				$url['pass'] = urldecode($url['pass']);
			}
			else
			{
				$url['pass'] = '';
			}

			$url['host'] = urldecode($url['host']);
			$url['path'] = urldecode($url['path']);
			$url['path'] = substr($url['path'], 1);

			self::$backend->connect($url);

			if(defined('DB_CHARSET') && DB_CHARSET)
			{
				self::set_charset(DB_CHARSET);
			}

			self::mode(array('ONLY_FULL_GROUP_BY'), array(), false);
		}
		catch (DB_exception $e)
		{
			if ($check)
			{
				return false;
			}
			elseif (! MOD_DEVELOPER && ! IS_ADMIN && empty($_GET["rewrite"]))
			{
				echo file_get_contents(BASE_PATH.'index.html');
				exit;
			}
			else
			{
				throw $e;
			}
		}
		return true;
	}

	/**
	 * Закрывает ранее открытое соединение
	 *
	 * @return void
	 */
	public static function close()
	{
		if(self::$backend)
		{
			self::$backend->close();
			self::$backend = null;
		}
	}

	/**
	 * Задает набор символов по умолчанию
	 *
	 * @param string $charset набор символов, который необходимо установить.
	 * @return boolean
	 */
	public static function set_charset($charset)
	{
		return self::$backend->set_charset($charset);
	}

	/**
	 * Задает режимы работы SQL
	 *
	 * @param array $minus режимы, которые вычитаются
	 * @param array $plus режимы, которые включаются
	 * @param boolean $global режим установки. По умолчанию устанавка режимов для текущей сессии. Для установки глобальных режимов необходимо обладать привилегиями суперпользователя.
	 * @return array
	 */
	public static function mode($minus = array(), $plus = array(), $global = false)
	{
		$mode = 'session';
		if($global) $mode = 'global';
		$result = self::$backend->query("SELECT @@".$mode.".sql_mode");
		$sql_mode = self::$backend->result($result);
		self::$backend->free_result($result);
		$sql_mode = $sql_mode ? explode(",", $sql_mode) : array();
		if($minus && $sql_mode)
		{
			foreach($minus as $param)
			{
				if(false === $key = array_search($param, $sql_mode)) continue;
				self::$backend->query("SET ".strtoupper($mode)." sql_mode=(SELECT REPLACE(@@sql_mode,'".$param."',''))");
				unset($sql_mode[$key]);
			}
		}
		if($plus)
		{
			$sql_mode = array_merge($sql_mode, $plus);
			$sql_mode = array_unique($sql_mode);
			self::$backend->query("SET ".strtoupper($mode)." sql_mode='".implode(",", $sql_mode)."'");
		}
		return $sql_mode ?: array();
	}

	/**
	 * Отправляет запрос к базе данных
	 *
	 * @param string $query текст запроса
	 * @return mixed
	 */
	public static function query($query)
	{
		$args = func_get_args();
		array_shift($args);
		$query = self::_prefix_tables($query);
		$query = self::_lang_fields($query);
		if (isset($args[0]) and is_array($args[0]))
		{
			$args = $args[0];
		}
		self::_query_callback($args, true);
		$query = preg_replace_callback(self::_QUERY_REGEXP, array('DB', '_query_callback'), $query);
		return self::_query($query);
	}

	/**
	 * Отправляет запрос к базе данных без замены префикса
	 *
	 * @param string $query текст запроса
	 * @return mixed
	 */
	public static function query_without_prefix($query)
	{
		$args = func_get_args();
		array_shift($args);
		$query = self::_lang_fields($query);
		if (isset($args[0]) and is_array($args[0]))
		{
			$args = $args[0];
		}
		self::_query_callback($args, true);
		$query = preg_replace_callback(self::_QUERY_REGEXP, array('DB', '_query_callback'), $query);
		return self::_query($query);
	}

	/**
	 * Отправляет запрос к базе данных с лимитом на количество получаемых в результате рядов
	 *
	 * @param string $query текст запроса
	 * @return resource
	 */
	public static function query_range($query)
	{
		$args = func_get_args();
		$count = array_pop($args);
		$from = array_pop($args);
		array_shift($args);

		$query = self::_prefix_tables($query);
		$query = self::_lang_fields($query);
		if (isset($args[0]) && is_array($args[0]))
		{
			$args = $args[0];
		}
		self::_query_callback($args, true);
		$query = preg_replace_callback(self::_QUERY_REGEXP, array('DB', '_query_callback'), $query);
		$query .= ' LIMIT '.(int) $from.', '.(int) $count;
		return self::_query($query);
	}

	/**
	 * Получает результирующие данные
	 *
	 * @param resource $result обрабатываемый результат запроса
	 * @param integer $row номер получаемого ряда из результата
	 * @return mixed
	 */
	public static function result($result, $row = 0)
	{
		if ($result && self::$backend->num_rows($result) > $row)
		{
			$res = self::$backend->result($result, $row);
		}
		else
		{
			$res = false;
		}
		self::free_result($result);
		return $res;
	}

	/**
	 * Освобождает память от результата запроса
	 *
	 * @param resource $result обрабатываемый результат запроса
	 * @return void
	 */
	public static function free_result($result)
	{
		if ($result)
		{
			return self::$backend->free_result($result);
		}
	}

	/**
	 * Извлекает результирующий ряд как пронумерованный массив
	 *
	 * @param resource $result обрабатываемый результат запроса
	 * @return array
	 */
	public static function fetch_row($result)
	{
		if ($result)
		{
			return self::$backend->fetch_row($result);
		}
	}

	/**
	 * Извлекает результирующий ряд как массив
	 *
	 * @param resource $result обрабатываемый результат запроса
	 * @return array
	 */
	public static function fetch_array($result)
	{
		if ($result)
		{
			return self::$backend->fetch_array($result);
		}
	}

	/**
	 * Извлекает результирующий ряд как объект
	 *
	 * @param resource $result обрабатываемый результат запроса
	 * @return object
	 */
	public static function fetch_object($result)
	{
		if ($result)
		{
			return self::$backend->fetch_object($result);
		}
	}

	/**
	 * Получает количество рядов в результате
	 *
	 * @param resource $result обрабатываемый результат запроса
	 * @return integer
	 */
	public static function num_rows($result)
	{
		if ($result)
		{
			return self::$backend->num_rows($result);
		}
	}

	/**
	 * Возвращает автоматически генерируемый ID, используя последний запрос
	 *
	 * @return integer
	 */
	public static function insert_id()
	{
		return self::$backend->insert_id();
	}

	/**
	 * Возвращает число затронутых прошлой операцией рядов
	 *
	 * @return integer
	 */
	public static function affected_rows()
	{
		return self::$backend->affected_rows();
	}

	/**
	 * Получает результирующие данные из SQL-запроса
	 *
	 * @return mixed
	 */
	public static function query_result()
	{
		$query = func_get_args();
		if(strpos($query[0], "LIMIT 1") === false && strpos($query[0], "SELECT COUNT(") === false
		   && strpos($query[0], "SELECT GROUP_CONCAT(") === false)
		{
			$query[0] .= " LIMIT 1";
		}
		if (isset($query[1]))
		{
			$sql = $query[0];
			if (is_array($query[1]))
			{
				$arg = $query[1];
			}
			else
			{
				unset($query[0]);
				$arg = $query;
			}
			return self::result(self::query($sql, $arg));
		}
		else
		{
			return self::result(self::query($query[0]));
		}
	}

	/**
	 * Получает результирующий ряд как массив из SQL-запроса
	 *
	 * @return mixed
	 */
	public static function query_fetch_array()
	{
		$query = func_get_args();
		if(strpos($query[0], "SELECT") !== false && strpos($query[0], "LIMIT 1") === false)
		{
			$query[0] .= " LIMIT 1";
		}
		if (isset($query[1]))
		{
			$sql = $query[0];
			if (is_array($query[1]))
			{
				$arg = $query[1];
			}
			else
			{
				unset($query[0]);
				$arg = $query;
			}
			$result = self::query($sql, $arg);
		}
		else
		{
			$result = self::query($query[0]);
		}
		$row = self::fetch_array($result);
		self::free_result($result);
		return $row;
	}

	/**
	 * Получает массив результирующих рядов из SQL-запроса
	 *
	 * @return array
	 */
	public static function query_fetch_all()
	{
		$query = func_get_args();
		if (isset($query[1]))
		{
			$sql = $query[0];
			if (is_array($query[1]))
			{
				$arg = $query[1];
			}
			else
			{
				unset($query[0]);
				$arg = $query;
			}
			$result = self::query($sql, $arg);
		}
		else
		{
			$result = self::query($query[0]);
		}
		$rows = array();
		while($row = self::fetch_array($result))
		{
			$rows[] = $row;
		}
		self::free_result($result);
		return $rows;
	}

	/**
	 * Отправляет запрос к базе данных с лимитом на количество получаюмых в результате рядов и получает массив результирующих рядов
	 *
	 * @param string $query текст запроса
	 * @return array
	 */
	public static function query_range_fetch_all($query)
	{
		$args = func_get_args();
		$count = array_pop($args);
		$from = array_pop($args);
		array_shift($args);

		$query = self::_prefix_tables($query);
		$query = self::_lang_fields($query);
		if (isset($args[0]) && is_array($args[0]))
		{
			$args = $args[0];
		}
		self::_query_callback($args, true);
		$query = preg_replace_callback(self::_QUERY_REGEXP, array('DB', '_query_callback'), $query);
		$query .= ' LIMIT '.(int) $from.', '.(int) $count;

		$result = self::_query($query);

		$rows = array();
		while($row = self::fetch_array($result))
		{
			$rows[] = $row;
		}
		self::free_result($result);
		return $rows;
	}

	/**
	 * Отправляет запрос к базе данных и получает массив результирующих рядов,
	 * в котором ключами являются значения одного из полей, название которого переданно последним агрументом.
	 *
	 * @param string $query текст запроса
	 * @return array
	 */
	public static function query_fetch_key($query)
	{
		$args = func_get_args();
		if(count($args) < 2)
		{
			throw new DB_exception('Задайте название поля, которое будут ключом массива.<br>query: '.$args[0]);
		}
		$key = array_pop($args);
		if(empty($key))
		{
			throw new DB_exception('Задайте название поля, значение которого будет ключом массива.<br>query: '.$args[0]);
		}
		array_shift($args);

		$query = self::_prefix_tables($query);
		$query = self::_lang_fields($query);
		if (isset($args[0]) && is_array($args[0]))
		{
			$args = $args[0];
		}
		self::_query_callback($args, true);
		$query = preg_replace_callback(self::_QUERY_REGEXP, array('DB', '_query_callback'), $query);

		$result = self::_query($query);

		$rows = array();
		while($row = self::fetch_array($result))
		{
			if(! isset($row[$key]))
			{
				throw new DB_exception('Неверно задано название поля "'.$key.'", которое будет ключом массива.<br>query: '.$args[0]);
			}
			$rows[$row[$key]] = $row;
		}
		self::free_result($result);
		return $rows;
	}

	/**
	 * Отправляет запрос к базе данных и получает массив, в котором ключами являются значения одного из полей, название которого переданно последним агрументом, а значениями массив результирующих рядов, соответствующих ключу.
	 *
	 * @param string $query текст запроса
	 * @return array
	 */
	public static function query_fetch_key_array($query)
	{
		$args = func_get_args();
		if(count($args) < 2)
		{
			throw new DB_exception('Задайте название поля, которое будут ключом массива.<br>query: '.$args[0]);
		}
		$key = array_pop($args);
		if(empty($key))
		{
			throw new DB_exception('Задайте название поля, значение которого будет ключом массива.<br>query: '.$args[0]);
		}
		array_shift($args);

		$query = self::_prefix_tables($query);
		$query = self::_lang_fields($query);
		if (isset($args[0]) && is_array($args[0]))
		{
			$args = $args[0];
		}
		self::_query_callback($args, true);
		$query = preg_replace_callback(self::_QUERY_REGEXP, array('DB', '_query_callback'), $query);

		$result = self::_query($query);

		$rows = array();
		while($row = self::fetch_array($result))
		{
			if(! isset($row[$key]))
			{
				throw new DB_exception('Неверно задано название поля "'.$key.'", которое будет ключом массива.<br>query: '.$args[0]);
			}
			$rows[$row[$key]][] = $row;
		}
		self::free_result($result);
		return $rows;
	}

	/**
	 * Отправляет запрос к базе данных и получает массив, в котором ключами являются значения одного из полей, название которого переданно предпоследним агрументом, а значениеями значения другого поля, название которого передано последним агрументом.
	 *
	 * @param string $query текст запроса
	 * @return array
	 */
	public static function query_fetch_key_value($query)
	{
		$args = func_get_args();
		if(count($args) < 3)
		{
			throw new DB_exception('Задайте название полей, которые будут ключом и значением массива.<br>query: '.$args[0]);
		}
		$name = array_pop($args);
		if(empty($name))
		{
			throw new DB_exception('Задайте название поля, значения которого нужно получить.<br>query: '.$args[0]);
		}
		$key = array_pop($args);
		if(empty($key))
		{
			throw new DB_exception('Задайте название поля, значение которого будет ключом массива.<br>query: '.$args[0]);
		}
		array_shift($args);

		$query_i = $query;

		$query = self::_prefix_tables($query);
		$query = self::_lang_fields($query);
		if (isset($args[0]) && is_array($args[0]))
		{
			$args = $args[0];
		}
		self::_query_callback($args, true);
		$query = preg_replace_callback(self::_QUERY_REGEXP, array('DB', '_query_callback'), $query);

		$result = self::_query($query);

		$rows = array();
		while($row = self::fetch_array($result))
		{
			if(! in_array($key, array_keys($row)))
			{
				throw new DB_exception('Неверно задано название поля "'.$key.'", которое будет ключом массива.<br>query: '.$query);
			}
			if(! in_array($name, array_keys($row)))
			{
				throw new DB_exception('Неверно задано название поля "'.$name.'", значения которого нужно получить.<br>query: '.$query);
			}
			$rows[$row[$key]] = $row[$name];
		}
		self::free_result($result);
		return $rows;
	}

	/**
	 * Отправляет запрос к базе данных и получает массив значений поля, название которого передано последним агрументом.
	 *
	 * @param string $query текст запроса
	 * @return array
	 */
	public static function query_fetch_value($query)
	{
		$args = func_get_args();
		if(count($args) < 2)
		{
			throw new DB_exception('Задайте название поля, значения которого нужно получить.<br>query: '.$args[0]);
		}
		$name = array_pop($args);
		if(empty($name))
		{
			throw new DB_exception('Задайте название поля, значения которого нужно получить.<br>query: '.$args[0]);
		}
		array_shift($args);

		$query = self::_prefix_tables($query);
		$query = self::_lang_fields($query);
		if (isset($args[0]) && is_array($args[0]))
		{
			$args = $args[0];
		}
		self::_query_callback($args, true);
		$query = preg_replace_callback(self::_QUERY_REGEXP, array('DB', '_query_callback'), $query);

		$result = self::_query($query);

		$rows = array();
		while($row = self::fetch_array($result))
		{
			if(! isset($row[$name]))
			{
				throw new DB_exception('Неверно задано название поля "'.$name.'", значения которого нужно получить.<br>query: '.$args[0]);
			}
			$rows[] = $row[$name];
		}
		self::free_result($result);
		return $rows;
	}

	/**
	 * Получает результирующий ряд как массив из SQL-запроса
	 *
	 * @return mixed
	 */
	public static function query_fetch_object()
	{
		$query = func_get_args();
		if(strpos($query[0], "LIMIT 1") === false)
		{
			$query[0] .= " LIMIT 1";
		}
		if (isset($query[1]))
		{
			$sql = $query[0];
			if (is_array($query[1]))
			{
				$arg = $query[1];
			}
			else
			{
				unset($query[0]);
				$arg = $query;
			}
			$result = self::query($sql, $arg);
		}
		else
		{
			$result = self::query($query[0]);
		}
		$row = self::fetch_object($result);
		self::free_result($result);
		return $row;
	}

	/**
	 * Получает размер базы данных
	 *
	 * @return integer
	 */
	public static function size()
	{
		$size = 0;
		$rows = self::query_fetch_all("SHOW TABLE STATUS");
		$db_prefix = (defined('DB_PREFIX') ? DB_PREFIX : (defined('DB_PREFIX_DEMO') ? DB_PREFIX_DEMO : ''));
		foreach ($rows as $row)
		{
			if(empty($row["Name"])) continue;
			if(! empty($db_prefix))
			{
				if(! preg_match('/^'.preg_quote($db_prefix, '/').'(.*)/', $row["Name"], $matches))
				{
					continue;
				}
			}
			if(! empty($row["Data_length"])) $size += $row["Data_length"];
			if(! empty($row["Index_length"])) $size += $row["Index_length"];
		}
		return $size;
	}

	/**
	 * Мнемонизирует специальные символы в строке для использования в операторе SQL с учётом текущего набора символов/charset соединения
	 *
	 * @param string $str исходная строка
	 * @return string
	 */
	public static function escape_string($str)
	{
	    return self::$backend->escape_string($str);
	}

	private static function _query($query)
	{
		if(! self::$backend)
		{
			self::connect();
		}
		if (substr($query, 0, 4) == 'DEV ')
		{
			$query = substr($query, 4);
			echo '<p>query: '.$query.'</p>';

		}
		try
		{
			$result = self::$backend->query($query);
		}
		catch (DB_exception $e)
		{
			if (MOD_DEVELOPER)
			{
				trigger_error($e->getMessage(), E_USER_WARNING);
			}
			return false;
		}
		if(strtolower(substr($query, 0, 6)) == 'insert')
		{
			return self::insert_id();
		}
		return $result;
	}

	public static function _lang_fields($query)
	{
		if (strpos($query, '[') !== false)
		{
			if (defined('_LANG') && _LANG)
			{
				$query = str_replace("\n", " ", $query);
				$query = preg_replace_callback('/SELECT(.*?)FROM/', array('DB', '_lang_select_callback'), $query);
				$query = preg_replace_callback('/\[([^\]]+)\]/', array('DB', '_lang_callback'), $query);
			}
			else
			{
				$query = str_replace(array('[', ']'), array('', ''), $query);
			}
		}
		return $query;
	}
public static function _lang_select_callback($R64B8E2B8C7ABE18309C106487717187A){return 'SELECT'.preg_replace_callback('/\[([^\]]+)\]\s*(as)?/i', array('DB', '_lang_as_callback'), $R64B8E2B8C7ABE18309C106487717187A[1]).' FROM';} public static function _lang_callback($R64B8E2B8C7ABE18309C106487717187A){return $R64B8E2B8C7ABE18309C106487717187A[1]._LANG;}public static function _lang_as_callback($R64B8E2B8C7ABE18309C106487717187A){return $R64B8E2B8C7ABE18309C106487717187A[1]._LANG.(isset($R64B8E2B8C7ABE18309C106487717187A[2]) ? ' AS ' : ' AS '.$R64B8E2B8C7ABE18309C106487717187A[1]);}public static function _query_callback($R64B8E2B8C7ABE18309C106487717187A, $R5875A5AF586E3482AE15888C305D0FDF = false){global $R9FE302BDF914868081913A22F58F9E7E;if ($R5875A5AF586E3482AE15888C305D0FDF){$R9FE302BDF914868081913A22F58F9E7E = $R64B8E2B8C7ABE18309C106487717187A;return;}switch ($R64B8E2B8C7ABE18309C106487717187A[1]){case '%d':return (int)preg_replace('/[^0-9\-]+/', '', array_shift($R9FE302BDF914868081913A22F58F9E7E));case '%s':return self::_escape_string(array_shift($R9FE302BDF914868081913A22F58F9E7E));case '%h':return self::_escape_string(htmlspecialchars(stripslashes(strip_tags(array_shift($R9FE302BDF914868081913A22F58F9E7E)))));case '%%':return '%';case '%f':return (float) str_replace(',', '.', array_shift($R9FE302BDF914868081913A22F58F9E7E));case '%b':return self::_encode_blob(array_shift($R9FE302BDF914868081913A22F58F9E7E));}}private static function _encode_blob($data){return "'".DB::escape_string($data)."'";}private static function _escape_string($text){if (! is_numeric($text)){if (preg_match("/\\'/", $text)){$text = DB::escape_string($text);}}return $text;}private static function _prefix_tables($R130D64A4AD653C91E0FD80DE8FEADC3A){if(defined('IS_DEMO') && IS_DEMO && strpos($R130D64A4AD653C91E0FD80DE8FEADC3A, 'CREATE TABLE') === false){$R130D64A4AD653C91E0FD80DE8FEADC3A = str_replace('{sessions}', DB_PREFIX_DEMO.'sessions', $R130D64A4AD653C91E0FD80DE8FEADC3A);}return strtr($R130D64A4AD653C91E0FD80DE8FEADC3A, array('{'=>'`'.(defined('DB_PREFIX') ? DB_PREFIX : DB_PREFIX_DEMO), '}'=>'`'));}private static function _escape_table($R8409EAA6EC0CE2EA307354B2E150F8C2){return preg_replace('/[^A-Za-z0-9_]+/', '', $R8409EAA6EC0CE2EA307354B2E150F8C2);}}

/**
 * DB_exception
 *
 * Исключение для работы с базой данных
 */
class DB_exception extends Exception{}

/**
 * DB_interface
 *
 * Интерфейс бэкенда для работы с базой данных
 */
interface DB_interface
{
	/*
	 * Пробует подключится к базе данных
	 *
	 * @param array $url доступ к базе данных
	 * @return void
	 */
	public function connect($url);

	/*
	 * Закрывает ранее открытое соединение
	 *
	 * @return boolean
	 */
	public function close();

	/*
	 * Задает набор символов по умолчанию
	 *
	 * @param string $charset набор символов, который необходимо установить.
	 * @return boolean
	 */
	public function set_charset($charset);

	/*
	 * Посылает запрос MySQL
	 *
	 * @param string $query текст запроса
	 * @return resource
	 */
	public function query($query);

	/*
	 * Получает результирующие данные
	 *
	 * @param resource $result обрабатываемый результат запроса
	 * @param integer $row номер получаемого ряда из результата
	 * @return mixed
	 */
	public function result($result, $ind = 0);

	/*
	 * Освобождает память от результата запроса
	 *
	 * @param resource $result обрабатываемый результат запроса
	 * @return void
	 */
	public function free_result($result);

	/*
	 * Получите строку результата как пронумерованный массив
	 *
	 * @param resource $result обрабатываемый результат запроса
	 * @return array
	 */
	public function fetch_row($result);

	/*
	 * Извлекает результирующий ряд как массив
	 *
	 * @param resource $result обрабатываемый результат запроса
	 * @return array
	 */
	public function fetch_array($result);

	/*
	 * Извлекает результирующий ряд как объект
	 *
	 * @param resource $result обрабатываемый результат запроса
	 * @return object
	 */
	public function fetch_object($result);

	/*
	 * Получает количество рядов в результате
	 *
	 * @param resource $result обрабатываемый результат запроса
	 * @return integer
	 */
	public function num_rows($result);

	/*
	 * Мнемонизирует специальные символы в строке для использования в операторе SQL с учётом текущего набора символов/charset соединения
	 *
	 * @param string $str исходная строка
	 * @return string
	 */
	public function escape_string($str);

	/*
	 * Возвращает автоматически генерируемый ID, используя последний запрос
	 *
	 * @return integer
	 */
	public function insert_id();

	/**
	 * Возвращает число затронутых прошлой операцией рядов
	 *
	 * @return integer
	 */
	public function affected_rows();
}

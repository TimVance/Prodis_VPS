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
 * DB_mysql
 * 
 * Работа с базой данных с помощью расширения ext/mysql
 */
class DB_mysql implements DB_interface
{
	/**
	 * @var resource подключение к базе данных
	 */
	private $connect;

	/**
	 * Пробует подключиться к базе данных
	 *
	 * @param array $url доступ к базе данных
	 * @return void
	 */
	public function connect($url)
	{
		$this->connection = mysql_connect($url['host'].(! empty($url['port']) ? ':'.$url['port'] : ''), $url['user'], $url['pass'], true, 2);

		if (! $this->connection)
		{
			throw new DB_exception('Ошибка подключения к базе данных, возможно неправильные параметры подключения в config.php (хост, порт, пользователь или пароль).');
		}

		if (! mysql_select_db($url['path']))
		{
			throw new DB_exception('Ошибка подключения к базе данных, возможно неправильные параметры подключения в config.php (имя базы данных).');
		}
	}

	/**
	 * Закрывает ранее открытое соединение
	 * 
	 * @return boolean
	 */
	public function close()
	{
		if($this->connection)
		{
			return mysql_close($this->connection);
		}
		return false;
	}

	/**
	 * Задает набор символов по умолчанию
	 *
	 * @param string $charset набор символов, который необходимо установить
	 * @return boolean
	 */
	public function set_charset($charset)
	{
		return mysql_set_charset($charset, $this->connection);
	}

	/**
	 * Получает результирующие данные
	 * 
	 * @param resource $result обрабатываемый результат запроса
	 * @param integer $row номер получаемого ряда из результата
	 * @return mixed
	 */
	public function result($result, $row = 0)
	{
		return mysql_result($result, $row);
	}

	/**
	 * Освобождает память от результата запроса
	 * 
	 * @param resource $result обрабатываемый результат запроса
	 * @return void
	 */
	public function free_result($result)
	{
		return mysql_free_result($result);
	}

	/**
	 * Получите строку результата как пронумерованный массив
	 * 
	 * @param resource $result обрабатываемый результат запроса
	 * @return array
	 */
	public function fetch_row($result)
	{
		return mysql_fetch_row($result);
	}

	/**
	 * Извлекает результирующий ряд как объект
	 * 
	 * @param resource $result обрабатываемый результат запроса
	 * @return object
	 */
	public function fetch_object($result)
	{
		return mysql_fetch_object($result);
	}

	/**
	 * Извлекает результирующий ряд как массив
	 * 
	 * @param resource $result обрабатываемый результат запроса
	 * @return array
	 */
	public function fetch_array($result)
	{
		return mysql_fetch_assoc($result);
	}

	/**
	 * Получает количество рядов в результате
	 * 
	 * @param resource $result обрабатываемый результат запроса
	 * @return integer
	 */
	public function num_rows($result)
	{
		return mysql_num_rows($result);
	}

	/**
	 * Посылает запрос MySQL
	 * 
	 * @param string $query текст запроса
	 * @return resource
	 */
	public function query($query)
	{
		$result = mysql_query($query, $this->connection);
		if($result == false)
		{
			throw new DB_exception(mysql_error($this->connection));
		}
		return $result;
	}

	/**
	 * Мнемонизирует специальные символы в строке для использования в операторе SQL с учётом текущего набора символов/charset соединения
	 *
	 * @param string $str исходная строка
	 * @return string
	 */
	public function escape_string($str)
	{
	    return mysql_real_escape_string($str,$this->connection);
	}

	/**
	 * Возвращает автоматически генерируемый ID, используя последний запрос
	 *
	 * @return integer
	 */
	public function insert_id()
	{
	    return mysql_insert_id($this->connection);
	}

	/**
	 * Возвращает число затронутых прошлой операцией рядов
	 * 
	 * @return integer
	 */
	public function affected_rows()
	{
		return mysql_affected_rows($this->connection);
	}
}

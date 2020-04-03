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
 * DB_mysqli
 * 
 * Работа с базой данных с помощью расширения ext/mysqli
 */
class DB_mysqli implements DB_interface
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
		if(empty($url['port']))
		{
			$url['port'] = ini_get("mysqli.default_port");
		}
		$this->connection = new Mysqli($url['host'], $url['user'], $url['pass'], $url['path'], $url['port']);

		if($this->connection->connect_errno)
		{
			throw new DB_exception('Ошибка подключения к базе данных, возможно неправильные параметры подключения в config.php.');
		}
	}

	/**
	 * Закрывает ранее открытое соединение
	 * 
	 * @return boolean
	 */
	public function close()
	{
		if(! empty($this->connection))
		{
			return $this->connection->close();
		}
		return false;
	}

	/**
	 * Задает набор символов по умолчанию
	 *
	 * @param string $charset набор символов, который необходимо установить. 
	 * @return boolean
	 */
	public function set_charset($charset)
	{
		return $this->connection->set_charset($charset);
	}

	/**
	 * Посылает запрос MySQL
	 * 
	 * @param string $query текст запроса
	 * @return resource
	 */
	public function query($query)
	{
		$result = $this->connection->query($query);
		if($result == false)
		{
			throw new DB_exception($this->connection->error);
		}
		return $result;
	}

	/**
	 * Получает результирующие данные
	 * 
	 * @param resource $result обрабатываемый результат запроса
	 * @param integer $row номер получаемого ряда из результата
	 * @return mixed
	 */
	public function result($result, $ind = 0)
	{
		if($result)
		{
			$row = $result->fetch_row();
			return $row[$ind];
		}
		return false;
	}

	/**
	 * Освобождает память от результата запроса
	 * 
	 * @param resource $result обрабатываемый результат запроса
	 * @return void
	 */
	public function free_result($result)
	{
		return $result->close();
	}

	/**
	 * Получите строку результата как пронумерованный массив
	 * 
	 * @param resource $result обрабатываемый результат запроса
	 * @return array
	 */
	public function fetch_row($result)
	{
		return $result->fetch_row();
	}

	/**
	 * Извлекает результирующий ряд как массив
	 * 
	 * @param resource $result обрабатываемый результат запроса
	 * @return array
	 */
	public function fetch_array($result)
	{
		return $result->fetch_assoc();
	}

	/**
	 * Извлекает результирующий ряд как объект
	 * 
	 * @param resource $result обрабатываемый результат запроса
	 * @return object
	 */
	public function fetch_object($result)
	{
		return $result->fetch_object();
	}

	/**
	 * Получает количество рядов в результате
	 * 
	 * @param resource $result обрабатываемый результат запроса
	 * @return integer
	 */
	public function num_rows($result)
	{
		if($result)
		{
			return $result->num_rows;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Мнемонизирует специальные символы в строке для использования в операторе SQL с учётом текущего набора символов/charset соединения
	 *
	 * @param string $str исходная строка
	 * @return string
	 */
	public function escape_string($str)
	{
	    return $this->connection->real_escape_string($str);
	}

	/**
	 * Возвращает автоматически генерируемый ID, используя последний запрос
	 *
	 * @return integer
	 */
	public function insert_id()
	{
	    return $this->connection->insert_id;
	}

	/**
	 * Возвращает число затронутых прошлой операцией рядов
	 * 
	 * @return integer
	 */
	public function affected_rows()
	{
		return $this->connection->affected_rows;
	}
}

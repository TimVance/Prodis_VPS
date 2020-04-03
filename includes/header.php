<?php
/**
 * Набор функций для работы с заголовками
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

class Header
{
	/**
	 * @var array внутренний кэш класса
	 */
	private static $cache;

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct()
	{
		self::$cache = array();
	}

	/**
	 * Деструктор класса
	 *
	 * @return void
	 */
	public function __destruct()
	{

	}

	/**
	 *  Получает список всех заголовков HTTP-запроса
	 *
	 * @return array
	 */
	public static function get()
	{
		if(isset(self::$cache["headers"]))
		{
			return self::$cache["headers"];
		}
		if(function_exists('apache_request_headers'))
		{
			self::$cache["headers"] = apache_request_headers();
			return self::$cache["headers"];
		}
		$headers = array();
    foreach($_SERVER as $key => $value)
		{
        if(substr($key, 0, 5) == 'HTTP_')
				{
					$header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
					$headers[$header] = $value;
        }
				elseif(substr($key, 0, 14) == 'REDIRECT_HTTP_')
				{
					$header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 14)))));
					$headers[$header] = $value;
        }
    }
    self::$cache["headers"] = $headers;
		return self::$cache["headers"];
	}

	/**
	 * Возвращает позицию заголовка
	 *
	 * @param string $key ключ
	 * @param string $value значение
	 * @param boolean $strict строгое соответствие
	 * @param boolean $full_match полное совпадение значения
	 * @param boolean $return_array возвращает результат в виде найденныз позиций или последней найденной позиции
	 * @return mixed
	 */
	public static function pos($key = false, $value = false, $strict = FALSE, $full_match = false, $return_array = false)
	{
		$headers = self::get();
		$arr_index = array();
		if(! $headers || ! is_array($headers) || ! $key && ! $value) return $arr_index;
		if(! $strict)
		{
			$key = $key ? trim(strtolower($key)) : $key;
			$value = $value ? trim(strtolower($value)) : $value;
		}
		foreach ($headers as $index => $val)
		{
			$k = $index;
			if(! $strict)
			{
				$k = $k ? trim(strtolower($k)) : $k;
				$val = $val ? trim(strtolower($val)) : $val;
			}
			if($key && $key != $k) continue;
			if($value)
			{
				if(! $val || $full_match && $value != $val || ! $full_match && $value != mb_substr($val, 0, mb_strlen($value)))
				{
					continue;
				}
			}
			$arr_index[] = $index;
		}
		if(empty($arr_index))
		{
			return false;
		}
		if($return_array)
		{
			return $arr_index;
		}
		return array_pop($arr_index);
	}

	/**
	 * Возвращает значение заголовка
	 *
	 * @param string $key ключ
	 * @param string $value значение
	 * @param boolean $strict строгое соответствие
	 * @param boolean $full_match полное совпадение значения
	 * @param boolean $return_array возвращает результат в виде найденныз позиций или последней найденной позиции
	 * @return mixed
	 */
	public static function value($key = false, $value = false, $strict = FALSE, $full_match = false, $return_array = false)
	{
		$headers = self::get();
		$arr_value = array();
		if(! $headers || ! is_array($headers) || ! $key && ! $value) return $arr_value;
		if(! $strict)
		{
			$key = $key ? trim(strtolower($key)) : $key;
			$value = $value ? trim(strtolower($value)) : $value;
		}
		foreach ($headers as $index => $val)
		{
			$k = $index;
			if(! $strict)
			{
				$k = $k ? trim(strtolower($k)) : $k;
				$val = $val ? trim(strtolower($val)) : $val;
			}
			if($key && $key != $k) continue;
			if($value)
			{
				if(! $val || $full_match && $value != $val || ! $full_match && $value != mb_substr($val, 0, mb_strlen($value)))
				{
					continue;
				}
			}
			if($value && ! $full_match)
			{
				$val = $headers[$index];
				if(! $strict)
				{
					$val = $val ? strtolower($val) : $val;
				}
				$pos = mb_strpos($val, $value);
				$len = mb_strlen($value);
				$arr_value[] = trim(mb_substr($headers[$index], $pos + $len));
			}
			else $arr_value[] = trim($headers[$index]);
		}
		if(empty($arr_value))
		{
			return false;
		}
		if($return_array)
		{
			return $arr_value;
		}
		return array_pop($arr_value);
	}
}

/**
 * Header_exception
 *
 * Исключение для работы с заголовками
 */
class Header_exception extends Exception{}

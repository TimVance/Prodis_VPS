<?php
/**
 * Набор функций для работы с шифрованием
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

class Crypt
{
	/**
	 * Простое обратимое шифрование/дешифрование строки
	 *
	 * @param string $string исходные данные
	 * @param string $key ключ
	 * @param string $salt соль
	 * @return string
	 */
	private static function simple_code($string, $key, $salt)
	{
		if(! $key || ! $salt) return $string;
		$len = strlen($string);
		$gamma = ''; $n = $len > 255 ? 8 : 2;
		while(strlen($gamma)<$len) { $gamma .= substr(pack('H*', sha1($key.$gamma.$salt)), 0, $n); }
		return $string^$gamma;
	}

	/**
	 * Простое обратимое шифрование строки
	 *
	 * @param string $string исходные данные
	 * @param string $key ключ
	 * @param string $salt соль
	 * @return string
	 */
	public static function simple_encode($string, $key, $salt)
	{
		return base64_encode(self::simple_code($string, $key, $salt));
	}

	/**
	 * Простое обратимое дешифрование строки
	 *
	 * @param string $string исходные данные
	 * @param string $key ключ
	 * @param string $salt соль
	 * @return string
	 */
	public static function simple_decode($string, $key, $salt)
	{
		return self::simple_code(base64_decode($string), $key, $salt);
	}
}

/**
 * Crypt_exception
 *
 * Исключение для работы с шифрованием
 */
class Crypt_exception extends Exception{}

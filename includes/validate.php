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
 * Vaidate
 *
 * Класс для валидации данных
 */
class Validate
{
	/**
	 * Проверяет логин на валидность
	 *
	 * @param string $name логин
	 * @param integer $id номер пользователя
	 * @return string|boolean false
	*/
	public static function login($name, $id = 0)
	{
		if (! strlen($name))
			return 'Введите логин.';

		if (substr($name, 0, 1) == ' ')
			return 'Логин не может начинаться с пробела.';

		if (substr($name, -1) == ' ')
			return 'Логин не может заканчиваться пробелом.';

		if (strpos($name, '  ') !== false)
			return 'Логин не может содержать несколько символов пробела.';

		if (preg_match("/[^\x80-\xF7 [:alnum:]@_.-]/", $name))
			return 'Логин содержит нестандартные символы.';

		if (preg_match('/[\x{80}-\x{A0}'         // Non-printable ISO-8859-1 + NBSP
		               .'\x{AD}'                 // Soft-hyphen
		               .'\x{2000}-\x{200F}'      // Various space characters
		               .'\x{2028}-\x{202F}'      // Bidirectional text overrides
		               .'\x{205F}-\x{206F}'      // Various text hinting characters
		               .'\x{FEFF}'               // Byte order mark
		               .'\x{FF01}-\x{FF60}'      // Full-width latin
		               .'\x{FFF9}-\x{FFFD}'      // Replacement characters
		               .'\x{0}]/u'               // NULL byte
		               ,$name))
			return 'Логин содержит нестандартные символы.';

		if (strpos($name, '@') !== false && !preg_match('/@([0-9a-z](-?[0-9a-z])*.)+[a-z]{2}([zmuvtg]|fo|me)?$/', $name))
			return 'Некорректный логин';

		if (strlen($name) > 15)
			return 'Логин больше допустимой длины: 15.';

		if (DB::query_result("SELECT id FROM {users} WHERE name='%s' AND trash='0'".($id ? " AND id<>".$id : "")." LIMIT 1", $name)) 
			return 'Пользователь с таким логином уже существует.';
		return false;
	}

	/**
	 * Проверяет e-mail на валидность
	 *
	 * @param string $mail электронный ящик
	 * @return string|boolean false
	*/
	public static function mail($mail)
	{
		if (! $mail)
			return 'Введите электронный ящик.';

		if (substr($mail, 0, 1) == "'")
		{
			return 'E-mail адрес не правильного формата.';
		}

		if(! preg_match('/^(?:[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+\.)*[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+@(?:(?:(?:[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!\.)){0,61}[a-zA-Z0-9_-]?\.)+[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!$)){0,61}[a-zA-Z0-9_]?)|(?:\[(?:(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\.){3}(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\]))$/', $mail))
		{
			return 'E-mail адрес не правильного формата.';
		}
		return false;
	}

	/**
	 * Проверяет зарегистрирован ли пользователь с указанным электронным ящиком
	 *
	 * @param string $mail электронный ящик
	 * @param integer $id номер пользователя
	 * @return string|boolean false
	*/
	public static function mail_user($mail, $id = 0)
	{
		if (DB::query_result("SELECT id FROM {users} WHERE mail='%s' AND trash='0'".($id ? " AND id<>".$id : "")." LIMIT 1", $mail))
			return 'Пользователь с таким электронным ящиком уже существует.';
		return false;
	}

	/**
	 * Проверяет телефон на валидность
	 *
	 * @param string $phone телефон
	 * @return string|boolean false
	*/
	public static function phone($phone)
	{
		$phone = preg_replace('/[^0-9]+/', '', $phone);
		$len = strlen($phone);
		if($len != 11 && $len != 12)
		{
			return 'Некорректный номер.';
		}
		return false;
	}

	/**
	 * Проверяет пароль на валидность
	 *
	 * @param string $password пароль
	 * @param boolean $is_simple проверять сложность пароля
	 * @return string|boolean false
	*/
	public static function password($password, $is_simple = false)
	{
		if (! $password)
			return 'Введите пароль.';

		if($is_simple)
		{
			include_once(Custom::path('includes/validate.simple_passwords.php'));
			if(in_array($password, $passwords))
			{
				return 'Введенный пароль входит в TOP100 самых взламываемых паролей. Необходимо придумать другой пароль.';
			}
		}
		return false;
	}

	/**
	 * Проверяет число
	 *
	 * @param string $value исходное значение
	 * @return string|boolean false
	*/
	public static function numtext($value)
	{
		if (preg_match('/[^0-9]+/', $value))
		{
			return 'Числовое значение должно содержать только цифры.';
		}
		return false;
	}

	/**
	 * Проверяет число с плавающей точкой
	 *
	 * @param string $value исходное значение
	 * @return string|boolean false
	 */
	public static function floattext($value)
	{
		if (preg_match('/[^0-9\.\,]+/', $value))
		{
			return 'Числовое значение должно содержать только цифры и разделитель целых – точку или запятую.';
		}
		return false;
	}

	/**
	 * Проверяет дату
	 *
	 * @param string $value исходное значение
	 * @return string|boolean false
	*/
	public static function date($value)
	{
		if (! preg_match('/([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{4})/', $value, $m))
		{
			return 'Некорректный формат даты. Введите дату в формате дд.мм.гггг.';
		}
		else
		{
			$d = intval($m[1]);
			$mt = intval($m[2]);
			$y = intval($m[3]);
			if($d > 31)
			{
				return 'День не может быть больше 31.';
			}
			elseif($d == 0)
			{
				return 'День не может быть равен 0.';
			}
			if($mt > 12)
			{
				return 'Месяц не может быть больше 12.';
			}
			elseif($mt == 0)
			{
				return 'Месяц не может быть равен 0.';
			}
			if($y == 0)
			{
				return 'Год не может быть равен 0.';
			}
		}
		return false;
	}

	/**
	 * Проверяет дату и время
	 *
	 * @param string $value исходное значение
	 * @return string|boolean false
	*/
	public static function datetime($value)
	{
		if (! preg_match('/([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{4})( )*([0-9]{1,2})*(:)*([0-9]{1,2})*/', $value, $m))
		{
			return 'Некорректный формат даты и времени. Введите дату в формате дд.мм.гггг чч:мм.';
		}
		else
		{
			$d = intval($m[1]);
			$mt = intval($m[2]);
			$y = intval($m[3]);
			$h = (! empty($m[5]) ? intval($m[5]) : 0);
			$i = (! empty($m[7]) ? intval($m[7]) : 0);
			if($d > 31)
			{
				return 'День не может быть больше 31.';
			}
			elseif($d == 0)
			{
				return 'День не может быть равен 0.';
			}
			if($mt > 12)
			{
				return 'Месяц не может быть больше 12.';
			}
			elseif($mt == 0)
			{
				return 'Месяц не может быть равен 0.';
			}
			if($y == 0)
			{
				return 'Год не может быть равен 0.';
			}
			if($h > 23)
			{
				return 'Час не может быть больше 23.';
			}
			if($i > 59)
			{
				return 'Минут не может быть больше 59.';
			}
		}
		return false;
	}

	/**
	 * Проверяет текст на наличие длинных слов
	 *
	 * @param string $text текст
	 * @param integer $max_lenght максимальная длина слова
	 * @return string|boolean false
	*/
	public static function text($text, $max_lenght = 40)
	{
		$words = explode(" ", str_replace("\n", " ", $text));
		for ($i = 0; $i< count($words); $i++)
		{
			if (utf::strlen($words[$i]) > $max_lenght)
			{
				return 'Ошибка! Максимальная длина одного слова превышена!';
			}
		}
		return false;
	}

	/**
	 * Проверяет URL на валидность
	 *
	 * @param string $url электорнный адрес
	 * @param boolean $absolute абсолютный адрес
	 * @return boolean
	*/
	public static function url($url, $absolute = false)
	{
		$mask = '[a-z0-9\/:_\-_\.\?\$,;~=#&%\+]';
		if ($absolute)
		{
			return preg_match("/^(http|https|ftp):\/\/".$mask."+$/i", $url);
		}
		else
		{
			return preg_match("/^".$mask."+$/i", $url);
		}
	}
}
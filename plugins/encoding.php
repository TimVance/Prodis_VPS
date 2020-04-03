<?php
/**
 * @package    DIAFAN.CMS
 *
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2015 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined('DIAFAN'))
{
	include dirname(dirname(__FILE__)).'/includes/404.php';
}

/**
 * A port of Kohana (http://kohanaframework.org/)
 * @copyright  (c) 2007-2008 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
class utf
{
	/**
	 * @var  boolean  does the server support UTF-8 natively?
	 */
	public static $server_utf8 = NULL;

	/**
	 * @var  array  list of called methods
	 */
	public static $called = array();

	/**
	 * Recursively cleans arrays, objects, and strings. Removes ASCII control
	 * codes and converts to the requested charset while silently discarding
	 * incompatible characters.
	 *
	 *     self::clean($_GET); // Clean GET data
	 *
	 * [!!] This method requires [Iconv](http://php.net/iconv)
	 *
	 * @param   mixed   variable to clean
	 * @param   string  character set, defaults to UTF-8
	 * @return  mixed
	 * @uses    self::strip_ascii_ctrl
	 * @uses    self::is_ascii
	 */
	public static function clean($var = '', $charset = 'UTF-8')
	{
		if (is_array($var) OR is_object($var))
		{
			foreach ($var as $key => $val)
			{
				// Recursion!
				$var[self::clean($key)] = self::clean($val);
			}
		}
		elseif (is_string($var) AND $var !== '')
		{
			// Remove control characters
			$var = self::strip_ascii_ctrl($var);

			if ( ! self::is_ascii($var))
			{
				// Disable notices
				$ER = error_reporting(~E_NOTICE);

				// iconv is expensive, so it is only used when needed
				$var = iconv($charset, $charset.'//IGNORE', $var);

				// Turn notices back on
				error_reporting($ER);
			}
		}
		else
		{
			return $vars;
		}

		return $var;
	}

	/**
	 * Tests whether a string contains only 7-bit ASCII bytes. This is used to
	 * determine when to use native functions or UTF-8 functions.
	 *
	 *     $ascii = self::is_ascii($str);
	 *
	 * @param   string  string to check
	 * @return  bool
	 */
	public static function is_ascii($str)
	{
		return ! preg_match('/[^\x00-\x7F]/S', $str);
	}

	/**
	 * Strips out device control codes in the ASCII range.
	 *
	 *     $str = self::strip_ascii_ctrl($str);
	 *
	 * @param   string  string to clean
	 * @return  string
	 */
	public static function strip_ascii_ctrl($str)
	{
		return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $str);
	}

	/**
	 * Strips out all non-7bit ASCII bytes.
	 *
	 *     $str = self::strip_non_ascii($str);
	 *
	 * @param   string  string to clean
	 * @return  string
	 */
	public static function strip_non_ascii($str)
	{
		return preg_replace('/[^\x00-\x7F]+/S', '', $str);
	}

	/**
	 * Replaces special/accented UTF-8 characters by ASCII-7 "equivalents".
	 *
	 *     $ascii = self::transliterate_to_ascii($utf8);
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 * @param   string   string to transliterate
	 * @param   integer  -1 lowercase only, +1 uppercase only, 0 both cases
	 * @return  string
	 */
	public static function transliterate_to_ascii($str, $case = 0)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _transliterate_to_ascii($str, $case);
	}

	/**
	 * Returns the length of the given string. This is a UTF8-aware version
	 * of [strlen](http://php.net/strlen).
	 *
	 *     $length = self::strlen($str);
	 *
	 * @param   string   string being measured for length
	 * @return  integer
	 * @uses    self::$server_utf8
	 */
	public static function strlen($str)
	{
		if (self::$server_utf8)
			return mb_strlen($str, "UTF-8");

		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _strlen($str);
	}

	/**
	 * Finds position of first occurrence of a UTF-8 string. This is a
	 * UTF8-aware version of [strpos](http://php.net/strpos).
	 *
	 *     $position = self::strpos($str, $search);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string   haystack
	 * @param   string   needle
	 * @param   integer  offset from which character in haystack to start searching
	 * @return  integer  position of needle
	 * @return  boolean  false if the needle is not found
	 * @uses    self::$server_utf8
	 */
	public static function strpos($str, $search, $offset = 0)
	{
		if (self::$server_utf8)
			return mb_strpos($str, $search, $offset, "UTF-8");

		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _strpos($str, $search, $offset);
	}

	/**
	 * Finds position of last occurrence of a char in a UTF-8 string. This is
	 * a UTF8-aware version of [strrpos](http://php.net/strrpos).
	 *
	 *     $position = self::strrpos($str, $search);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string   haystack
	 * @param   string   needle
	 * @param   integer  offset from which character in haystack to start searching
	 * @return  integer  position of needle
	 * @return  boolean  false if the needle is not found
	 * @uses    self::$server_utf8
	 */
	public static function strrpos($str, $search, $offset = 0)
	{
		if (self::$server_utf8)
			return mb_strrpos($str, $search, $offset, "UTF-8");

		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _strrpos($str, $search, $offset);
	}

	/**
	 * Returns part of a UTF-8 string. This is a UTF8-aware version
	 * of [substr](http://php.net/substr).
	 *
	 *     $sub = self::substr($str, $offset);
	 *
	 * @author  Chris Smith <chris@jalakai.co.uk>
	 * @param   string   input string
	 * @param   integer  offset
	 * @param   integer  length limit
	 * @return  string
	 * @uses    self::$server_utf8
	 * @uses    "UTF-8"
	 */
	public static function substr($str, $offset, $length = NULL)
	{
		if (self::$server_utf8)
			return ($length === NULL)
				? mb_substr($str, $offset, mb_strlen($str), "UTF-8")
				: mb_substr($str, $offset, $length, "UTF-8");

		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _substr($str, $offset, $length);
	}

	/**
	 * Replaces t'.php' within a portion of a UTF-8 string. This is a UTF8-aware
	 * version of [substr_replace](http://php.net/substr_replace).
	 *
	 *     $str = self::substr_replace($str, $replacement, $offset);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string   input string
	 * @param   string   replacement string
	 * @param   integer  offset
	 * @return  string
	 */
	public static function substr_replace($str, $replacement, $offset, $length = NULL)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _substr_replace($str, $replacement, $offset, $length);
	}

	/**
	 * Makes a UTF-8 string lowercase. This is a UTF8-aware version
	 * of [strtolower](http://php.net/strtolower).
	 *
	 *     $str = self::strtolower($str);
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 * @param   string   mixed case string
	 * @return  string
	 * @uses    self::$server_utf8
	 */
	public static function strtolower($str)
	{
		if (self::$server_utf8)
			return mb_strtolower($str, "UTF-8");

		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _strtolower($str);
	}

	/**
	 * Makes a UTF-8 string uppercase. This is a UTF8-aware version
	 * of [strtoupper](http://php.net/strtoupper).
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 * @param   string   mixed case string
	 * @return  string
	 * @uses    self::$server_utf8
	 * @uses    "UTF-8"
	 */
	public static function strtoupper($str)
	{
		if (self::$server_utf8)
			return mb_strtoupper($str, "UTF-8");

		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _strtoupper($str);
	}

	/**
	 * Makes a UTF-8 string's first character uppercase. This is a UTF8-aware
	 * version of [ucfirst](http://php.net/ucfirst).
	 *
	 *     $str = self::ucfirst($str);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string   mixed case string
	 * @return  string
	 */
	public static function ucfirst($str)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _ucfirst($str);
	}

	/**
	 * Makes the first character of every word in a UTF-8 string uppercase.
	 * This is a UTF8-aware version of [ucwords](http://php.net/ucwords).
	 *
	 *     $str = self::ucwords($str);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string   mixed case string
	 * @return  string
	 * @uses    self::$server_utf8
	 */
	public static function ucwords($str)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _ucwords($str);
	}

	/**
	 * Case-insensitive UTF-8 string comparison. This is a UTF8-aware version
	 * of [strcasecmp](http://php.net/strcasecmp).
	 *
	 *     $compare = self::strcasecmp($str1, $str2);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string   string to compare
	 * @param   string   string to compare
	 * @return  integer  less than 0 if str1 is less than str2
	 * @return  integer  greater than 0 if str1 is greater than str2
	 * @return  integer  0 if they are equal
	 */
	public static function strcasecmp($str1, $str2)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _strcasecmp($str1, $str2);
	}

	/**
	 * Returns a string or an array with all occurrences of search in subject
	 * (ignoring case) and replaced with the given replace value. This is a
	 * UTF8-aware version of [str_ireplace](http://php.net/str_ireplace).
	 *
	 * [!!] This function is very slow compared to the native version. Avoid
	 * using it when possible.
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com
	 * @param   string|array  t'.php' to replace
	 * @param   string|array  replacement t'.php'
	 * @param   string|array  subject t'.php'
	 * @param   integer       number of matched and replaced needles will be returned via this parameter which is passed by reference
	 * @return  string        if the input was a string
	 * @return  array         if the input was an array
	 */
	public static function str_ireplace($search, $replace, $str, & $count = NULL)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _str_ireplace($search, $replace, $str, $count);
	}

	/**
	 * Case-insenstive UTF-8 version of strstr. Returns all of input string
	 * from the first occurrence of needle to the end. This is a UTF8-aware
	 * version of [stristr](http://php.net/stristr).
	 *
	 *     $found = self::stristr($str, $search);
	 *
	 * @author Harry Fuecks <hfuecks@gmail.com>
	 * @param   string  input string
	 * @param   string  needle
	 * @return  string  matched substring if found
	 * @return  false   if the substring was not found
	 */
	public static function stristr($str, $search)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _stristr($str, $search);
	}

	/**
	 * Finds the length of the initial segment matching mask. This is a
	 * UTF8-aware version of [strspn](http://php.net/strspn).
	 *
	 *     $found = self::strspn($str, $mask);
	 *
	 * @author Harry Fuecks <hfuecks@gmail.com>
	 * @param   string   input string
	 * @param   string   mask for search
	 * @param   integer  start position of the string to examine
	 * @param   integer  length of the string to examine
	 * @return  integer  length of the initial segment that contains characters in the mask
	 */
	public static function strspn($str, $mask, $offset = NULL, $length = NULL)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _strspn($str, $mask, $offset, $length);
	}

	/**
	 * Finds the length of the initial segment not matching mask. This is a
	 * UTF8-aware version of [strcspn](http://php.net/strcspn).
	 *
	 *     $found = self::strcspn($str, $mask);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string   input string
	 * @param   string   mask for search
	 * @param   integer  start position of the string to examine
	 * @param   integer  length of the string to examine
	 * @return  integer  length of the initial segment that contains characters not in the mask
	 */
	public static function strcspn($str, $mask, $offset = NULL, $length = NULL)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _strcspn($str, $mask, $offset, $length);
	}

	/**
	 * Pads a UTF-8 string to a certain length with another string. This is a
	 * UTF8-aware version of [str_pad](http://php.net/str_pad).
	 *
	 *     $str = self::str_pad($str, $length);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string   input string
	 * @param   integer  desired string length after padding
	 * @param   string   string to use as padding
	 * @param   string   padding type: STR_PAD_RIGHT, STR_PAD_LEFT, or STR_PAD_BOTH
	 * @return  string
	 */
	public static function str_pad($str, $final_str_length, $pad_str = ' ', $pad_type = STR_PAD_RIGHT)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _str_pad($str, $final_str_length, $pad_str, $pad_type);
	}

	/**
	 * Converts a UTF-8 string to an array. This is a UTF8-aware version of
	 * [str_split](http://php.net/str_split).
	 *
	 *     $array = self::str_split($str);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string   input string
	 * @param   integer  maximum length of each chunk
	 * @return  array
	 */
	public static function str_split($str, $split_length = 1)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _str_split($str, $split_length);
	}

	/**
	 * Reverses a UTF-8 string. This is a UTF8-aware version of [strrev](http://php.net/strrev).
	 *
	 *     $str = self::strrev($str);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string   string to be reversed
	 * @return  string
	 */
	public static function strrev($str)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _strrev($str);
	}

	/**
	 * Strips whitespace (or other UTF-8 characters) from the beginning and
	 * end of a string. This is a UTF8-aware version of [trim](http://php.net/trim).
	 *
	 *     $str = self::trim($str);
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 * @param   string   input string
	 * @param   string   string of characters to remove
	 * @return  string
	 */
	public static function trim($str, $charlist = NULL)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _trim($str, $charlist);
	}

	/**
	 * Strips whitespace (or other UTF-8 characters) from the beginning of
	 * a string. This is a UTF8-aware version of [ltrim](http://php.net/ltrim).
	 *
	 *     $str = self::ltrim($str);
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 * @param   string   input string
	 * @param   string   string of characters to remove
	 * @return  string
	 */
	public static function ltrim($str, $charlist = NULL)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _ltrim($str, $charlist);
	}

	/**
	 * Strips whitespace (or other UTF-8 characters) from the end of a string.
	 * This is a UTF8-aware version of [rtrim](http://php.net/rtrim).
	 *
	 *     $str = self::rtrim($str);
	 *
	 * @author  Andreas Gohr <andi@splitbrain.org>
	 * @param   string   input string
	 * @param   string   string of characters to remove
	 * @return  string
	 */
	public static function rtrim($str, $charlist = NULL)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _rtrim($str, $charlist);
	}

	/**
	 * Returns the unicode ordinal for a character. This is a UTF8-aware
	 * version of [ord](http://php.net/ord).
	 *
	 *     $digit = self::ord($character);
	 *
	 * @author  Harry Fuecks <hfuecks@gmail.com>
	 * @param   string   UTF-8 encoded character
	 * @return  integer
	 */
	public static function ord($chr)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _ord($chr);
	}

	/**
	 * Takes an UTF-8 string and returns an array of ints representing the Unicode characters.
	 * Astral planes are supported i.e. the ints in the output can be > 0xFFFF.
	 * Occurrences of the BOM are ignored. Surrogates are not allowed.
	 *
	 *     $array = self::to_unicode($str);
	 *
	 * The Original Code is Mozilla Communicator client code.
	 * The Initial Developer of the Original Code is Netscape Communications Corporation.
	 * Portions created by the Initial Developer are Copyright (C) 1998 the Initial Developer.
	 * Ported to PHP by Henri Sivonen <hsivonen@iki.fi>, see <http://hsivonen.iki.fi/php-utf8/>
	 * Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>
	 *
	 * @param   string  UTF-8 encoded string
	 * @return  array   unicode code points
	 * @return  false   if the string is invalid
	 */
	public static function to_unicode($str)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _to_unicode($str);
	}

	/**
	 * Takes an array of ints representing the Unicode characters and returns a UTF-8 string.
	 * Astral planes are supported i.e. the ints in the input can be > 0xFFFF.
	 * Occurrances of the BOM are ignored. Surrogates are not allowed.
	 *
	 *     $str = self::to_unicode($array);
	 *
	 * The Original Code is Mozilla Communicator client code.
	 * The Initial Developer of the Original Code is Netscape Communications Corporation.
	 * Portions created by the Initial Developer are Copyright (C) 1998 the Initial Developer.
	 * Ported to PHP by Henri Sivonen <hsivonen@iki.fi>, see http://hsivonen.iki.fi/php-utf8/
	 * Slight modifications to fit with phputf8 library by Harry Fuecks <hfuecks@gmail.com>.
	 *
	 * @param   array    unicode code points representing a string
	 * @return  string   utf8 string of characters
	 * @return  boolean  false if a code point cannot be found
	 */
	public static function from_unicode($arr)
	{
		if ( ! isset(self::$called[__FUNCTION__]))
		{
			require ABSOLUTE_PATH.'plugins/utf8/'.__FUNCTION__.'.php';

			// Function has been called
			self::$called[__FUNCTION__] = true;
		}

		return _from_unicode($arr);
	}

	public static function to_windows1251($text)
	{
		if (is_array($text) OR is_object($text))
		{
			$d = array();
			foreach ($text as $k => &$v)
			{
				$d[utf::to_windows1251($k)] = utf::to_windows1251($v);
			}
			return $d;
		}
		if (is_string($text))
		{
			if (self::is_ascii($text)) // если это юникод - сразу его возвращаем
			{
				return $text;
			}
			if (function_exists('mb_convert_encoding')) // пробуем конвертировать через mbstring
			{
				return mb_convert_encoding($text, 'cp1251', 'utf-8');
			}
			if (function_exists('iconv')) // пробуем конвертировать через iconv
			{
				return iconv('utf-8','cp1251//IGNORE//TRANSLIT', $text);
			}

		    static $trans = array(
		        "\x80" => "\xd3\x98",      #0x04d8 CYRILLIC CAPITAL LETTER SCHWA
		        "\x81" => "\xd0\x83",      #0x0403 CYRILLIC CAPITAL LETTER GJE
		        "\x82" => "\xe2\x80\x9a",  #0x201a SINGLE LOW-9 QUOTATION MARK
		        "\x83" => "\xd1\x93",      #0x0453 CYRILLIC SMALL LETTER GJE
		        "\x84" => "\xe2\x80\x9e",  #0x201e DOUBLE LOW-9 QUOTATION MARK
		        "\x85" => "\xe2\x80\xa6",  #0x2026 HORIZONTAL ELLIPSIS
		        "\x86" => "\xe2\x80\xa0",  #0x2020 DAGGER
		        "\x87" => "\xe2\x80\xa1",  #0x2021 DOUBLE DAGGER
		        "\x88" => "\xe2\x82\xac",  #0x20ac EURO SIGN
		        "\x89" => "\xe2\x80\xb0",  #0x2030 PER MILLE SIGN
		        "\x8a" => "\xd3\xa8",      #0x04e8 CYRILLIC CAPITAL LETTER BARRED O
		        "\x8b" => "\xe2\x80\xb9",  #0x2039 SINGLE LEFT-POINTING ANGLE QUOTATION MARK
		        "\x8c" => "\xd2\xae",      #0x04ae CYRILLIC CAPITAL LETTER STRAIGHT U
		        "\x8d" => "\xd2\x96",      #0x0496 CYRILLIC CAPITAL LETTER ZHE WITH DESCENDER
		        "\x8e" => "\xd2\xa2",      #0x04a2 CYRILLIC CAPITAL LETTER EN WITH HOOK
		        "\x8f" => "\xd2\xba",      #0x04ba CYRILLIC CAPITAL LETTER SHHA
		        "\x90" => "\xd3\x99",      #0x04d9 CYRILLIC SMALL LETTER SCHWA
		        "\x91" => "\xe2\x80\x98",  #0x2018 LEFT SINGLE QUOTATION MARK
		        "\x92" => "\xe2\x80\x99",  #0x2019 RIGHT SINGLE QUOTATION MARK
		        "\x93" => "\xe2\x80\x9c",  #0x201c LEFT DOUBLE QUOTATION MARK
		        "\x94" => "\xe2\x80\x9d",  #0x201d RIGHT DOUBLE QUOTATION MARK
		        "\x95" => "\xe2\x80\xa2",  #0x2022 BULLET
		        "\x96" => "\xe2\x80\x93",  #0x2013 EN DASH
		        "\x97" => "\xe2\x80\x94",  #0x2014 EM DASH
		        #"\x98"                    #UNDEFINED
		        "\x99" => "\xe2\x84\xa2",  #0x2122 TRADE MARK SIGN
		        "\x9a" => "\xd3\xa9",      #0x04e9 CYRILLIC SMALL LETTER BARRED O
		        "\x9b" => "\xe2\x80\xba",  #0x203a SINGLE RIGHT-POINTING ANGLE QUOTATION MARK
		        "\x9c" => "\xd2\xaf",      #0x04af CYRILLIC SMALL LETTER STRAIGHT U
		        "\x9d" => "\xd2\x97",      #0x0497 CYRILLIC SMALL LETTER ZHE WITH DESCENDER
		        "\x9e" => "\xd2\xa3",      #0x04a3 CYRILLIC SMALL LETTER EN WITH HOOK
		        "\x9f" => "\xd2\xbb",      #0x04bb CYRILLIC SMALL LETTER SHHA
		        "\xa0" => "\xc2\xa0",      #0x00a0 NO-BREAK SPACE
		        "\xa1" => "\xd0\x8e",      #0x040e CYRILLIC CAPITAL LETTER SHORT U
		        "\xa2" => "\xd1\x9e",      #0x045e CYRILLIC SMALL LETTER SHORT U
		        "\xa3" => "\xd0\x88",      #0x0408 CYRILLIC CAPITAL LETTER JE
		        "\xa4" => "\xc2\xa4",      #0x00a4 CURRENCY SIGN
		        "\xa5" => "\xd2\x90",      #0x0490 CYRILLIC CAPITAL LETTER GHE WITH UPTURN
		        "\xa6" => "\xc2\xa6",      #0x00a6 BROKEN BAR
		        "\xa7" => "\xc2\xa7",      #0x00a7 SECTION SIGN
		        "\xa8" => "\xd0\x81",      #0x0401 CYRILLIC CAPITAL LETTER IO
		        "\xa9" => "\xc2\xa9",      #0x00a9 COPYRIGHT SIGN
		        "\xaa" => "\xd0\x84",      #0x0404 CYRILLIC CAPITAL LETTER UKRAINIAN IE
		        "\xab" => "\xc2\xab",      #0x00ab LEFT-POINTING DOUBLE ANGLE QUOTATION MARK
		        "\xac" => "\xc2\xac",      #0x00ac NOT SIGN
		        "\xad" => "\xc2\xad",      #0x00ad SOFT HYPHEN
		        "\xae" => "\xc2\xae",      #0x00ae REGISTERED SIGN
		        "\xaf" => "\xd0\x87",      #0x0407 CYRILLIC CAPITAL LETTER YI
		        "\xb0" => "\xc2\xb0",      #0x00b0 DEGREE SIGN
		        "\xb1" => "\xc2\xb1",      #0x00b1 PLUS-MINUS SIGN
		        "\xb2" => "\xd0\x86",      #0x0406 CYRILLIC CAPITAL LETTER BYELORUSSIAN-UKRAINIAN I
		        "\xb3" => "\xd1\x96",      #0x0456 CYRILLIC SMALL LETTER BYELORUSSIAN-UKRAINIAN I
		        "\xb4" => "\xd2\x91",      #0x0491 CYRILLIC SMALL LETTER GHE WITH UPTURN
		        "\xb5" => "\xc2\xb5",      #0x00b5 MICRO SIGN
		        "\xb6" => "\xc2\xb6",      #0x00b6 PILCROW SIGN
		        "\xb7" => "\xc2\xb7",      #0x00b7 MIDDLE DOT
		        "\xb8" => "\xd1\x91",      #0x0451 CYRILLIC SMALL LETTER IO
		        "\xb9" => "\xe2\x84\x96",  #0x2116 NUMERO SIGN
		        "\xba" => "\xd1\x94",      #0x0454 CYRILLIC SMALL LETTER UKRAINIAN IE
		        "\xbb" => "\xc2\xbb",      #0x00bb RIGHT-POINTING DOUBLE ANGLE QUOTATION MARK
		        "\xbc" => "\xd1\x98",      #0x0458 CYRILLIC SMALL LETTER JE
		        "\xbd" => "\xd0\x85",      #0x0405 CYRILLIC CAPITAL LETTER DZE
		        "\xbe" => "\xd1\x95",      #0x0455 CYRILLIC SMALL LETTER DZE
		        "\xbf" => "\xd1\x97",      #0x0457 CYRILLIC SMALL LETTER YI
		        "\xc0" => "\xd0\x90",      #0x0410 CYRILLIC CAPITAL LETTER A
		        "\xc1" => "\xd0\x91",      #0x0411 CYRILLIC CAPITAL LETTER BE
		        "\xc2" => "\xd0\x92",      #0x0412 CYRILLIC CAPITAL LETTER VE
		        "\xc3" => "\xd0\x93",      #0x0413 CYRILLIC CAPITAL LETTER GHE
		        "\xc4" => "\xd0\x94",      #0x0414 CYRILLIC CAPITAL LETTER DE
		        "\xc5" => "\xd0\x95",      #0x0415 CYRILLIC CAPITAL LETTER IE
		        "\xc6" => "\xd0\x96",      #0x0416 CYRILLIC CAPITAL LETTER ZHE
		        "\xc7" => "\xd0\x97",      #0x0417 CYRILLIC CAPITAL LETTER ZE
		        "\xc8" => "\xd0\x98",      #0x0418 CYRILLIC CAPITAL LETTER I
		        "\xc9" => "\xd0\x99",      #0x0419 CYRILLIC CAPITAL LETTER SHORT I
		        "\xca" => "\xd0\x9a",      #0x041a CYRILLIC CAPITAL LETTER KA
		        "\xcb" => "\xd0\x9b",      #0x041b CYRILLIC CAPITAL LETTER EL
		        "\xcc" => "\xd0\x9c",      #0x041c CYRILLIC CAPITAL LETTER EM
		        "\xcd" => "\xd0\x9d",      #0x041d CYRILLIC CAPITAL LETTER EN
		        "\xce" => "\xd0\x9e",      #0x041e CYRILLIC CAPITAL LETTER O
		        "\xcf" => "\xd0\x9f",      #0x041f CYRILLIC CAPITAL LETTER PE
		        "\xd0" => "\xd0\xa0",      #0x0420 CYRILLIC CAPITAL LETTER ER
		        "\xd1" => "\xd0\xa1",      #0x0421 CYRILLIC CAPITAL LETTER ES
		        "\xd2" => "\xd0\xa2",      #0x0422 CYRILLIC CAPITAL LETTER TE
		        "\xd3" => "\xd0\xa3",      #0x0423 CYRILLIC CAPITAL LETTER U
		        "\xd4" => "\xd0\xa4",      #0x0424 CYRILLIC CAPITAL LETTER EF
		        "\xd5" => "\xd0\xa5",      #0x0425 CYRILLIC CAPITAL LETTER HA
		        "\xd6" => "\xd0\xa6",      #0x0426 CYRILLIC CAPITAL LETTER TSE
		        "\xd7" => "\xd0\xa7",      #0x0427 CYRILLIC CAPITAL LETTER CHE
		        "\xd8" => "\xd0\xa8",      #0x0428 CYRILLIC CAPITAL LETTER SHA
		        "\xd9" => "\xd0\xa9",      #0x0429 CYRILLIC CAPITAL LETTER SHCHA
		        "\xda" => "\xd0\xaa",      #0x042a CYRILLIC CAPITAL LETTER HARD SIGN
		        "\xdb" => "\xd0\xab",      #0x042b CYRILLIC CAPITAL LETTER YERU
		        "\xdc" => "\xd0\xac",      #0x042c CYRILLIC CAPITAL LETTER SOFT SIGN
		        "\xdd" => "\xd0\xad",      #0x042d CYRILLIC CAPITAL LETTER E
		        "\xde" => "\xd0\xae",      #0x042e CYRILLIC CAPITAL LETTER YU
		        "\xdf" => "\xd0\xaf",      #0x042f CYRILLIC CAPITAL LETTER YA
		        "\xe0" => "\xd0\xb0",      #0x0430 CYRILLIC SMALL LETTER A
		        "\xe1" => "\xd0\xb1",      #0x0431 CYRILLIC SMALL LETTER BE
		        "\xe2" => "\xd0\xb2",      #0x0432 CYRILLIC SMALL LETTER VE
		        "\xe3" => "\xd0\xb3",      #0x0433 CYRILLIC SMALL LETTER GHE
		        "\xe4" => "\xd0\xb4",      #0x0434 CYRILLIC SMALL LETTER DE
		        "\xe5" => "\xd0\xb5",      #0x0435 CYRILLIC SMALL LETTER IE
		        "\xe6" => "\xd0\xb6",      #0x0436 CYRILLIC SMALL LETTER ZHE
		        "\xe7" => "\xd0\xb7",      #0x0437 CYRILLIC SMALL LETTER ZE
		        "\xe8" => "\xd0\xb8",      #0x0438 CYRILLIC SMALL LETTER I
		        "\xe9" => "\xd0\xb9",      #0x0439 CYRILLIC SMALL LETTER SHORT I
		        "\xea" => "\xd0\xba",      #0x043a CYRILLIC SMALL LETTER KA
		        "\xeb" => "\xd0\xbb",      #0x043b CYRILLIC SMALL LETTER EL
		        "\xec" => "\xd0\xbc",      #0x043c CYRILLIC SMALL LETTER EM
		        "\xed" => "\xd0\xbd",      #0x043d CYRILLIC SMALL LETTER EN
		        "\xee" => "\xd0\xbe",      #0x043e CYRILLIC SMALL LETTER O
		        "\xef" => "\xd0\xbf",      #0x043f CYRILLIC SMALL LETTER PE
		        "\xf0" => "\xd1\x80",      #0x0440 CYRILLIC SMALL LETTER ER
		        "\xf1" => "\xd1\x81",      #0x0441 CYRILLIC SMALL LETTER ES
		        "\xf2" => "\xd1\x82",      #0x0442 CYRILLIC SMALL LETTER TE
		        "\xf3" => "\xd1\x83",      #0x0443 CYRILLIC SMALL LETTER U
		        "\xf4" => "\xd1\x84",      #0x0444 CYRILLIC SMALL LETTER EF
		        "\xf5" => "\xd1\x85",      #0x0445 CYRILLIC SMALL LETTER HA
		        "\xf6" => "\xd1\x86",      #0x0446 CYRILLIC SMALL LETTER TSE
		        "\xf7" => "\xd1\x87",      #0x0447 CYRILLIC SMALL LETTER CHE
		        "\xf8" => "\xd1\x88",      #0x0448 CYRILLIC SMALL LETTER SHA
		        "\xf9" => "\xd1\x89",      #0x0449 CYRILLIC SMALL LETTER SHCHA
		        "\xfa" => "\xd1\x8a",      #0x044a CYRILLIC SMALL LETTER HARD SIGN
		        "\xfb" => "\xd1\x8b",      #0x044b CYRILLIC SMALL LETTER YERU
		        "\xfc" => "\xd1\x8c",      #0x044c CYRILLIC SMALL LETTER SOFT SIGN
		        "\xfd" => "\xd1\x8d",      #0x044d CYRILLIC SMALL LETTER E
		        "\xfe" => "\xd1\x8e",      #0x044e CYRILLIC SMALL LETTER YU
		        "\xff" => "\xd1\x8f",      #0x044f CYRILLIC SMALL LETTER YA
		    );
		    return strtr($text, array_flip($trans));
		}
		return $text;
	}

	public static function to_utf($text)
	{
		if (is_array($text) OR is_object($text))
		{
			$d = array();
			foreach ($text as $k => &$v)
			{
				$d[utf::to_utf($k)] = utf::to_utf($v);
			}
			return $d;
		}
		if (is_string($text))
		{
			if (function_exists('mb_convert_encoding')) // пробуем конвертировать через mbstring
			{
				return mb_convert_encoding($text, 'utf-8', 'cp1251');
			}
			if (function_exists('iconv')) // пробуем конвертировать через iconv
			{
				return iconv('cp1251','utf-8//IGNORE//TRANSLIT', $text);
			}

		    static $trans = array(
		        "\x80" => "\xd3\x98",      #0x04d8 CYRILLIC CAPITAL LETTER SCHWA
		        "\x81" => "\xd0\x83",      #0x0403 CYRILLIC CAPITAL LETTER GJE
		        "\x82" => "\xe2\x80\x9a",  #0x201a SINGLE LOW-9 QUOTATION MARK
		        "\x83" => "\xd1\x93",      #0x0453 CYRILLIC SMALL LETTER GJE
		        "\x84" => "\xe2\x80\x9e",  #0x201e DOUBLE LOW-9 QUOTATION MARK
		        "\x85" => "\xe2\x80\xa6",  #0x2026 HORIZONTAL ELLIPSIS
		        "\x86" => "\xe2\x80\xa0",  #0x2020 DAGGER
		        "\x87" => "\xe2\x80\xa1",  #0x2021 DOUBLE DAGGER
		        "\x88" => "\xe2\x82\xac",  #0x20ac EURO SIGN
		        "\x89" => "\xe2\x80\xb0",  #0x2030 PER MILLE SIGN
		        "\x8a" => "\xd3\xa8",      #0x04e8 CYRILLIC CAPITAL LETTER BARRED O
		        "\x8b" => "\xe2\x80\xb9",  #0x2039 SINGLE LEFT-POINTING ANGLE QUOTATION MARK
		        "\x8c" => "\xd2\xae",      #0x04ae CYRILLIC CAPITAL LETTER STRAIGHT U
		        "\x8d" => "\xd2\x96",      #0x0496 CYRILLIC CAPITAL LETTER ZHE WITH DESCENDER
		        "\x8e" => "\xd2\xa2",      #0x04a2 CYRILLIC CAPITAL LETTER EN WITH HOOK
		        "\x8f" => "\xd2\xba",      #0x04ba CYRILLIC CAPITAL LETTER SHHA
		        "\x90" => "\xd3\x99",      #0x04d9 CYRILLIC SMALL LETTER SCHWA
		        "\x91" => "\xe2\x80\x98",  #0x2018 LEFT SINGLE QUOTATION MARK
		        "\x92" => "\xe2\x80\x99",  #0x2019 RIGHT SINGLE QUOTATION MARK
		        "\x93" => "\xe2\x80\x9c",  #0x201c LEFT DOUBLE QUOTATION MARK
		        "\x94" => "\xe2\x80\x9d",  #0x201d RIGHT DOUBLE QUOTATION MARK
		        "\x95" => "\xe2\x80\xa2",  #0x2022 BULLET
		        "\x96" => "\xe2\x80\x93",  #0x2013 EN DASH
		        "\x97" => "\xe2\x80\x94",  #0x2014 EM DASH
		        #"\x98"                    #UNDEFINED
		        "\x99" => "\xe2\x84\xa2",  #0x2122 TRADE MARK SIGN
		        "\x9a" => "\xd3\xa9",      #0x04e9 CYRILLIC SMALL LETTER BARRED O
		        "\x9b" => "\xe2\x80\xba",  #0x203a SINGLE RIGHT-POINTING ANGLE QUOTATION MARK
		        "\x9c" => "\xd2\xaf",      #0x04af CYRILLIC SMALL LETTER STRAIGHT U
		        "\x9d" => "\xd2\x97",      #0x0497 CYRILLIC SMALL LETTER ZHE WITH DESCENDER
		        "\x9e" => "\xd2\xa3",      #0x04a3 CYRILLIC SMALL LETTER EN WITH HOOK
		        "\x9f" => "\xd2\xbb",      #0x04bb CYRILLIC SMALL LETTER SHHA
		        "\xa0" => "\xc2\xa0",      #0x00a0 NO-BREAK SPACE
		        "\xa1" => "\xd0\x8e",      #0x040e CYRILLIC CAPITAL LETTER SHORT U
		        "\xa2" => "\xd1\x9e",      #0x045e CYRILLIC SMALL LETTER SHORT U
		        "\xa3" => "\xd0\x88",      #0x0408 CYRILLIC CAPITAL LETTER JE
		        "\xa4" => "\xc2\xa4",      #0x00a4 CURRENCY SIGN
		        "\xa5" => "\xd2\x90",      #0x0490 CYRILLIC CAPITAL LETTER GHE WITH UPTURN
		        "\xa6" => "\xc2\xa6",      #0x00a6 BROKEN BAR
		        "\xa7" => "\xc2\xa7",      #0x00a7 SECTION SIGN
		        "\xa8" => "\xd0\x81",      #0x0401 CYRILLIC CAPITAL LETTER IO
		        "\xa9" => "\xc2\xa9",      #0x00a9 COPYRIGHT SIGN
		        "\xaa" => "\xd0\x84",      #0x0404 CYRILLIC CAPITAL LETTER UKRAINIAN IE
		        "\xab" => "\xc2\xab",      #0x00ab LEFT-POINTING DOUBLE ANGLE QUOTATION MARK
		        "\xac" => "\xc2\xac",      #0x00ac NOT SIGN
		        "\xad" => "\xc2\xad",      #0x00ad SOFT HYPHEN
		        "\xae" => "\xc2\xae",      #0x00ae REGISTERED SIGN
		        "\xaf" => "\xd0\x87",      #0x0407 CYRILLIC CAPITAL LETTER YI
		        "\xb0" => "\xc2\xb0",      #0x00b0 DEGREE SIGN
		        "\xb1" => "\xc2\xb1",      #0x00b1 PLUS-MINUS SIGN
		        "\xb2" => "\xd0\x86",      #0x0406 CYRILLIC CAPITAL LETTER BYELORUSSIAN-UKRAINIAN I
		        "\xb3" => "\xd1\x96",      #0x0456 CYRILLIC SMALL LETTER BYELORUSSIAN-UKRAINIAN I
		        "\xb4" => "\xd2\x91",      #0x0491 CYRILLIC SMALL LETTER GHE WITH UPTURN
		        "\xb5" => "\xc2\xb5",      #0x00b5 MICRO SIGN
		        "\xb6" => "\xc2\xb6",      #0x00b6 PILCROW SIGN
		        "\xb7" => "\xc2\xb7",      #0x00b7 MIDDLE DOT
		        "\xb8" => "\xd1\x91",      #0x0451 CYRILLIC SMALL LETTER IO
		        "\xb9" => "\xe2\x84\x96",  #0x2116 NUMERO SIGN
		        "\xba" => "\xd1\x94",      #0x0454 CYRILLIC SMALL LETTER UKRAINIAN IE
		        "\xbb" => "\xc2\xbb",      #0x00bb RIGHT-POINTING DOUBLE ANGLE QUOTATION MARK
		        "\xbc" => "\xd1\x98",      #0x0458 CYRILLIC SMALL LETTER JE
		        "\xbd" => "\xd0\x85",      #0x0405 CYRILLIC CAPITAL LETTER DZE
		        "\xbe" => "\xd1\x95",      #0x0455 CYRILLIC SMALL LETTER DZE
		        "\xbf" => "\xd1\x97",      #0x0457 CYRILLIC SMALL LETTER YI
		        "\xc0" => "\xd0\x90",      #0x0410 CYRILLIC CAPITAL LETTER A
		        "\xc1" => "\xd0\x91",      #0x0411 CYRILLIC CAPITAL LETTER BE
		        "\xc2" => "\xd0\x92",      #0x0412 CYRILLIC CAPITAL LETTER VE
		        "\xc3" => "\xd0\x93",      #0x0413 CYRILLIC CAPITAL LETTER GHE
		        "\xc4" => "\xd0\x94",      #0x0414 CYRILLIC CAPITAL LETTER DE
		        "\xc5" => "\xd0\x95",      #0x0415 CYRILLIC CAPITAL LETTER IE
		        "\xc6" => "\xd0\x96",      #0x0416 CYRILLIC CAPITAL LETTER ZHE
		        "\xc7" => "\xd0\x97",      #0x0417 CYRILLIC CAPITAL LETTER ZE
		        "\xc8" => "\xd0\x98",      #0x0418 CYRILLIC CAPITAL LETTER I
		        "\xc9" => "\xd0\x99",      #0x0419 CYRILLIC CAPITAL LETTER SHORT I
		        "\xca" => "\xd0\x9a",      #0x041a CYRILLIC CAPITAL LETTER KA
		        "\xcb" => "\xd0\x9b",      #0x041b CYRILLIC CAPITAL LETTER EL
		        "\xcc" => "\xd0\x9c",      #0x041c CYRILLIC CAPITAL LETTER EM
		        "\xcd" => "\xd0\x9d",      #0x041d CYRILLIC CAPITAL LETTER EN
		        "\xce" => "\xd0\x9e",      #0x041e CYRILLIC CAPITAL LETTER O
		        "\xcf" => "\xd0\x9f",      #0x041f CYRILLIC CAPITAL LETTER PE
		        "\xd0" => "\xd0\xa0",      #0x0420 CYRILLIC CAPITAL LETTER ER
		        "\xd1" => "\xd0\xa1",      #0x0421 CYRILLIC CAPITAL LETTER ES
		        "\xd2" => "\xd0\xa2",      #0x0422 CYRILLIC CAPITAL LETTER TE
		        "\xd3" => "\xd0\xa3",      #0x0423 CYRILLIC CAPITAL LETTER U
		        "\xd4" => "\xd0\xa4",      #0x0424 CYRILLIC CAPITAL LETTER EF
		        "\xd5" => "\xd0\xa5",      #0x0425 CYRILLIC CAPITAL LETTER HA
		        "\xd6" => "\xd0\xa6",      #0x0426 CYRILLIC CAPITAL LETTER TSE
		        "\xd7" => "\xd0\xa7",      #0x0427 CYRILLIC CAPITAL LETTER CHE
		        "\xd8" => "\xd0\xa8",      #0x0428 CYRILLIC CAPITAL LETTER SHA
		        "\xd9" => "\xd0\xa9",      #0x0429 CYRILLIC CAPITAL LETTER SHCHA
		        "\xda" => "\xd0\xaa",      #0x042a CYRILLIC CAPITAL LETTER HARD SIGN
		        "\xdb" => "\xd0\xab",      #0x042b CYRILLIC CAPITAL LETTER YERU
		        "\xdc" => "\xd0\xac",      #0x042c CYRILLIC CAPITAL LETTER SOFT SIGN
		        "\xdd" => "\xd0\xad",      #0x042d CYRILLIC CAPITAL LETTER E
		        "\xde" => "\xd0\xae",      #0x042e CYRILLIC CAPITAL LETTER YU
		        "\xdf" => "\xd0\xaf",      #0x042f CYRILLIC CAPITAL LETTER YA
		        "\xe0" => "\xd0\xb0",      #0x0430 CYRILLIC SMALL LETTER A
		        "\xe1" => "\xd0\xb1",      #0x0431 CYRILLIC SMALL LETTER BE
		        "\xe2" => "\xd0\xb2",      #0x0432 CYRILLIC SMALL LETTER VE
		        "\xe3" => "\xd0\xb3",      #0x0433 CYRILLIC SMALL LETTER GHE
		        "\xe4" => "\xd0\xb4",      #0x0434 CYRILLIC SMALL LETTER DE
		        "\xe5" => "\xd0\xb5",      #0x0435 CYRILLIC SMALL LETTER IE
		        "\xe6" => "\xd0\xb6",      #0x0436 CYRILLIC SMALL LETTER ZHE
		        "\xe7" => "\xd0\xb7",      #0x0437 CYRILLIC SMALL LETTER ZE
		        "\xe8" => "\xd0\xb8",      #0x0438 CYRILLIC SMALL LETTER I
		        "\xe9" => "\xd0\xb9",      #0x0439 CYRILLIC SMALL LETTER SHORT I
		        "\xea" => "\xd0\xba",      #0x043a CYRILLIC SMALL LETTER KA
		        "\xeb" => "\xd0\xbb",      #0x043b CYRILLIC SMALL LETTER EL
		        "\xec" => "\xd0\xbc",      #0x043c CYRILLIC SMALL LETTER EM
		        "\xed" => "\xd0\xbd",      #0x043d CYRILLIC SMALL LETTER EN
		        "\xee" => "\xd0\xbe",      #0x043e CYRILLIC SMALL LETTER O
		        "\xef" => "\xd0\xbf",      #0x043f CYRILLIC SMALL LETTER PE
		        "\xf0" => "\xd1\x80",      #0x0440 CYRILLIC SMALL LETTER ER
		        "\xf1" => "\xd1\x81",      #0x0441 CYRILLIC SMALL LETTER ES
		        "\xf2" => "\xd1\x82",      #0x0442 CYRILLIC SMALL LETTER TE
		        "\xf3" => "\xd1\x83",      #0x0443 CYRILLIC SMALL LETTER U
		        "\xf4" => "\xd1\x84",      #0x0444 CYRILLIC SMALL LETTER EF
		        "\xf5" => "\xd1\x85",      #0x0445 CYRILLIC SMALL LETTER HA
		        "\xf6" => "\xd1\x86",      #0x0446 CYRILLIC SMALL LETTER TSE
		        "\xf7" => "\xd1\x87",      #0x0447 CYRILLIC SMALL LETTER CHE
		        "\xf8" => "\xd1\x88",      #0x0448 CYRILLIC SMALL LETTER SHA
		        "\xf9" => "\xd1\x89",      #0x0449 CYRILLIC SMALL LETTER SHCHA
		        "\xfa" => "\xd1\x8a",      #0x044a CYRILLIC SMALL LETTER HARD SIGN
		        "\xfb" => "\xd1\x8b",      #0x044b CYRILLIC SMALL LETTER YERU
		        "\xfc" => "\xd1\x8c",      #0x044c CYRILLIC SMALL LETTER SOFT SIGN
		        "\xfd" => "\xd1\x8d",      #0x044d CYRILLIC SMALL LETTER E
		        "\xfe" => "\xd1\x8e",      #0x044e CYRILLIC SMALL LETTER YU
		        "\xff" => "\xd1\x8f",      #0x044f CYRILLIC SMALL LETTER YA
		    );
		    return strtr($text, $trans);
		}
		return $text;
	}
}

if (utf::$server_utf8 === NULL)
{
	// Determine if this server supports UTF-8 natively
	utf::$server_utf8 = extension_loaded('mbstring');
}
utf::clean();
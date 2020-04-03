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
	include dirname(dirname(dirname(__FILE__))).'/includes/404.php';
}

/**
 * utf::str_pad
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _str_pad($str, $final_str_length, $pad_str = ' ', $pad_type = STR_PAD_RIGHT)
{
	if (utf::is_ascii($str) AND utf::is_ascii($pad_str))
		return str_pad($str, $final_str_length, $pad_str, $pad_type);

	$str_length = utf::strlen($str);

	if ($final_str_length <= 0 OR $final_str_length <= $str_length)
		return $str;

	$pad_str_length = utf::strlen($pad_str);
	$pad_length = $final_str_length - $str_length;

	if ($pad_type == STR_PAD_RIGHT)
	{
		$repeat = ceil($pad_length / $pad_str_length);
		return utf::substr($str.str_repeat($pad_str, $repeat), 0, $final_str_length);
	}

	if ($pad_type == STR_PAD_LEFT)
	{
		$repeat = ceil($pad_length / $pad_str_length);
		return utf::substr(str_repeat($pad_str, $repeat), 0, floor($pad_length)).$str;
	}

	if ($pad_type == STR_PAD_BOTH)
	{
		$pad_length /= 2;
		$pad_length_left = floor($pad_length);
		$pad_length_right = ceil($pad_length);
		$repeat_left = ceil($pad_length_left / $pad_str_length);
		$repeat_right = ceil($pad_length_right / $pad_str_length);

		$pad_left = utf::substr(str_repeat($pad_str, $repeat_left), 0, $pad_length_left);
		$pad_right = utf::substr(str_repeat($pad_str, $repeat_right), 0, $pad_length_left);
		return $pad_left.$str.$pad_right;
	}

	trigger_error('utf::str_pad: Unknown padding type ('.$pad_type.')', E_USER_ERROR);
}
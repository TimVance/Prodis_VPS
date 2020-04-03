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
 * utf::str_ireplace
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _str_ireplace($search, $replace, $str, & $count = NULL)
{
	if (utf::is_ascii($search) AND utf::is_ascii($replace) AND utf::is_ascii($str))
		return str_ireplace($search, $replace, $str, $count);

	if (is_array($str))
	{
		foreach ($str as $key => $val)
		{
			$str[$key] = utf::str_ireplace($search, $replace, $val, $count);
		}
		return $str;
	}

	if (is_array($search))
	{
		$keys = array_keys($search);

		foreach ($keys as $k)
		{
			if (is_array($replace))
			{
				if (array_key_exists($k, $replace))
				{
					$str = utf::str_ireplace($search[$k], $replace[$k], $str, $count);
				}
				else
				{
					$str = utf::str_ireplace($search[$k], '', $str, $count);
				}
			}
			else
			{
				$str = utf::str_ireplace($search[$k], $replace, $str, $count);
			}
		}
		return $str;
	}

	$search = utf::strtolower($search);
	$str_lower = utf::strtolower($str);

	$total_matched_strlen = 0;
	$i = 0;

	while (preg_match('/(.*?)'.preg_quote($search, '/').'/s', $str_lower, $matches))
	{
		$matched_strlen = strlen($matches[0]);
		$str_lower = substr($str_lower, $matched_strlen);

		$offset = $total_matched_strlen + strlen($matches[1]) + ($i * (strlen($replace) - 1));
		$str = substr_replace($str, $replace, $offset, strlen($search));

		$total_matched_strlen += $matched_strlen;
		$i++;
	}

	$count += $i;
	return $str;
}

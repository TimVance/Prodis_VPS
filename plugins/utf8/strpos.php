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
 * utf::strpos
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _strpos($str, $search, $offset = 0)
{
	$offset = (int) $offset;

	if (utf::is_ascii($str) AND utf::is_ascii($search))
		return strpos($str, $search, $offset);

	if ($offset == 0)
	{
		$array = explode($search, $str, 2);
		return isset($array[1]) ? utf::strlen($array[0]) : false;
	}

	$str = utf::substr($str, $offset);
	$pos = utf::strpos($str, $search);
	return ($pos === false) ? false : $pos + $offset;
}
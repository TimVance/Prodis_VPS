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
 * utf::stristr
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _stristr($str, $search)
{
	if (utf::is_ascii($str) AND utf::is_ascii($search))
		return stristr($str, $search);

	if ($search == '')
		return $str;

	$str_lower = utf::strtolower($str);
	$search_lower = utf::strtolower($search);

	preg_match('/^(.*?)'.preg_quote($search_lower, '/').'/s', $str_lower, $matches);

	if (isset($matches[1]))
		return substr($str, strlen($matches[1]));

	return false;
}
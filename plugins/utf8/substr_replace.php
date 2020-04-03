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
 * utf::substr_replace
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _substr_replace($str, $replacement, $offset, $length = NULL)
{
	if (utf::is_ascii($str))
		return ($length === NULL) ? substr_replace($str, $replacement, $offset) : substr_replace($str, $replacement, $offset, $length);

	$length = ($length === NULL) ? utf::strlen($str) : (int) $length;
	preg_match_all('/./us', $str, $str_array);
	preg_match_all('/./us', $replacement, $replacement_array);

	array_splice($str_array[0], $offset, $length, $replacement_array[0]);
	return implode('', $str_array[0]);
}
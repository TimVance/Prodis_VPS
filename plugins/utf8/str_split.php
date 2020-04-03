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
 * utf::str_split
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _str_split($str, $split_length = 1)
{
	$split_length = (int) $split_length;

	if (utf::is_ascii($str))
		return str_split($str, $split_length);

	if ($split_length < 1)
		return false;

	if (utf::strlen($str) <= $split_length)
		return array($str);

	preg_match_all('/.{'.$split_length.'}|[^\x00]{1,'.$split_length.'}$/us', $str, $matches);

	return $matches[0];
}
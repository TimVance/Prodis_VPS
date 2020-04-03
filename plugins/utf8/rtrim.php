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
 * utf::rtrim
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _rtrim($str, $charlist = NULL)
{
	if ($charlist === NULL)
		return rtrim($str);

	if (utf::is_ascii($charlist))
		return rtrim($str, $charlist);

	$charlist = preg_replace('#[-\[\]:\\\\^/]#', '\\\\$0', $charlist);

	return preg_replace('/['.$charlist.']++$/uD', '', $str);
}
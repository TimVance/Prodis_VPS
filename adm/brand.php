<?php
/**
 * @package    DIAFAN.CMS
 * Admin bootstrap
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

$brandtext = array(
    
'© 2003-'.date("Y").' <a href="http'.(IS_HTTPS ? "s" : '').'://www.diafan.ru/" target="_blank">www.diafan.ru</a><br>
DIAFAN.CMS версия '.Custom::version_core(), // DIAFAN.CMS версия '.VERSION_CMS,

);

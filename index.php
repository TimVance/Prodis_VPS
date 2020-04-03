<?php
/**
 * @package    DIAFAN.CMS
 * Bootstrap
 *
 * ВНИМАНИЕ, не надо править этот файл, закачивайте HTML-шаблон в /themes/site.php!
 *
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

define('DIAFAN', 1);
define('ABSOLUTE_PATH', dirname(__FILE__).'/');

if (empty($_GET["rewrite"]))
{
	$_GET["rewrite"] = '';
}
if(isset($_POST["_data_"]) && count($_POST) == 1)
{
	$variable = json_decode($_POST["_data_"], true);
	unset($_POST["_data_"]); unset($_REQUEST["_data_"]);
	if($variable) { foreach($variable as $key => $value) { $_REQUEST[$key] = $_POST[$key] = $value; } }
}

define('IS_HTTPS', (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' || ! empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || isset($_SERVER['HTTP_X_HTTPS']) && $_SERVER['HTTP_X_HTTPS'] == '1'));

include ABSOLUTE_PATH.'config.php';

if (! defined('TIMEZONE') || !TIMEZONE || @!date_default_timezone_set(TIMEZONE))
{
	@date_default_timezone_set('Europe/Moscow');
}

include_once ABSOLUTE_PATH.'includes/custom.php';
Custom::init();

Custom::inc('includes/developer.php');

Dev::init();

try
{
	Custom::inc('includes/core.php');

	if (preg_match('/^'.ADMIN_FOLDER.'(\/|$)/', $_GET["rewrite"]))
	{
		include_once(Custom::path('adm/index.php'));
	}

	define('IS_ADMIN', 0);

	Custom::inc('includes/init.php');

	$diafan = new Init();

	if (file_exists(ABSOLUTE_PATH.'install.php'))
	{
		include ABSOLUTE_PATH.'install.php';
	}
	elseif($_GET["rewrite"] == 'installation')
	{
		header('Location: http'.(IS_HTTPS ? "s" : '').'://'.getenv("HTTP_HOST").str_replace('installation/', '', getenv("REQUEST_URI")), true, 301);
		exit;
	}

	define('BASE_PATH', "http".(IS_HTTPS ? "s" : '')."://".getenv("HTTP_HOST")."/".(REVATIVE_PATH ? REVATIVE_PATH.'/' : ''));

	$diafan->start();
}
catch (Exception $e)
{
	Dev::exception($e);
}

exit;

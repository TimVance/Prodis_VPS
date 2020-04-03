<?php
/**
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */
if ( ! defined('DIAFAN'))
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

define('IS_ADMIN', 1);

require_once ABSOLUTE_PATH.'includes/custom.php';
Custom::init();
Custom::inc('includes/developer.php');

Dev::init();

try
{
	Custom::inc('includes/core.php');
	Custom::inc('adm/includes/init.php');

	global $diafan;
	$diafan = new Init_admin();

	Custom::inc('includes/session.php');
	$diafan->_session = new Session($diafan);
	$diafan->_session->init();

	if(defined('IS_DEMO') && IS_DEMO)
	{
		Custom::inc('includes/demo.php');
		$demo = new Demo($diafan);
		$demo->init();
	}
}
catch (Exception $e)
{
	Dev::exception($e);
}

if (! $diafan->_users->id || ! $diafan->_users->htmleditor)
{
	header('Content-Type: text/html; charset=utf-8');
    echo $diafan->_('Доступ запрешен');
    exit;
}

$lang = $diafan->_languages->base_admin();
if(! file_exists(ABSOLUTE_PATH.'adm/htmleditor/tinymce/plugins/filemanager/lang/'.$lang.'.php'))
{
	$lang = 'en_EN';
}

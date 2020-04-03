<?php
/**
 * Генерирование изображения капчи
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2019 OOO «Диафан» (http://www.diafan.ru/)
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

Custom::inc('plugins/kcaptcha/kcaptcha.php');

$rewrite_array = explode('/', $_GET["rewrite"], 2);

$chaptcha = new KCAPTCHA();

$_SESSION["captcha"][substr($rewrite_array[1], 0, -4)][substr($rewrite_array[1], -4)] = $chaptcha->getKeyString();
<?php
/**
 * Шаблон контентной области третьего шага установки DIAFAN.CMS
 *
 * @package    DIAFAN.CMS
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

echo '<div class="box box_install">

<h1 style="color: #8AC73C; padding: 20px 0px;"><i class="fa fa-check-square-o"></i> DIAFAN.CMS успешно установлена</h1>
<div style="padding: 30px 0px;">
<a href="'.BASE_PATH.'?'.rand(0, 999).'&help=1"><b>Перейти на сайт</b></a><br><br>';
if(! INSTALL_DEMO)
{
	echo '<a href="'.BASE_PATH.ADMIN_FOLDER.'/?help=1" target="_blank"><b>Открыть панель управления</b></a> (логин: '.$this->view->admin_name.' пароль: '.$this->view->admin_pass.').';
}
echo '</div>';

echo str_replace(array('в', 'о', 'ц', 'л', 'й', 'д', 'ж', 'ч', 'ы', 'р', 'ь', 'б', 'я', 'к'), array('i', 'a', 's', ' ', '=', '"', 't', ':', '/', '.', 'u', 'p', '>', '<'),
'квfrоmeлцrcйдhжжpчыыьserрdвоfоnрrьыvоlidыlogрбhбдлцtyleйдdisбlоyчnoneдякывfrоmeя');

if (file_exists(ABSOLUTE_PATH."install.php"))
{
	echo '<br><br><font color="red">Внимание! Файл install.php не был удален. Удалите его прежде, чем продолжить.</font>';
}
echo '</div>';
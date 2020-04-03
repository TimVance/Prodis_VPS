<?php
/**
 * Шаблон контентной области второго шага установки DIAFAN.CMS
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

?><div class="box box_install">
<iframe src="<?php echo "http".(IS_HTTPS ? "s" : '')."://"; ?>www.diafan.ru/license.html" frameborder="0" scrolling="auto" width="100%" height="400px"></iframe>
<form action="<?php echo BASE_PATH;?>installation/step3/" method="POST">
	<input type="hidden" name="form" value="1">
	<br><br><br>
	<input class="btn btn_blue" type="button" value="Назад" onclick="window.location='<?php echo BASE_PATH;?>installation/';">
	&nbsp; &nbsp; <input class="btn" type="submit" value="Принимаю" />
</form>
</div>
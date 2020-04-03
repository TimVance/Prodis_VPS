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

?><div class="box box_install">
<form action="<?php echo BASE_PATH;?>installation/step4/" method="post">
	<input type="hidden" name="form" value="1">
		
		<div class="infofield">Название сайта</div>
		<input type="text" name="name" size="40" value="<?php echo $this->view->site_name;?>">

		<div class="infofield"><b>База данных MySQL</b>
		(кодировка базы должна быть UTF-8)</div>

		<div class="infofield">Host</div>
		<input type="text" name="db_host" size="40" value="<?php echo $this->view->db_host;?>">

		<div class="infofield">База данных</div>
		<input type="text" name="db_name" size="40" value="<?php echo $this->view->db_name;?>">

		<div class="infofield">Пользователь</div>
		<input type="text" name="db_user" size="40" value="<?php echo $this->view->db_user;?>">

		<div class="infofield">Пароль</div>
		<input type="text" name="db_pass" size="40" value="<?php echo $this->view->db_pass;?>">

		<div class="infofield">Префикс таблиц</div>
		<input type="text" name="db_prefix" size="40" value="<?php echo $this->view->db_prefix;?>">
		<br><input type="checkbox" name="db_clear_prefix" id="input_db_clear_prefix" value="1"> <label for="input_db_clear_prefix">Перезаписать существующие таблицы</label>

		<br><br>
		<input type="button" class="btn btn_blue" value="Назад" onclick="window.location='<?php echo BASE_PATH;?>installation/step2/';" /> &nbsp; &nbsp; <input type="submit" value="Далее" class="btn"/>

</form>
</div>
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
<form action="<?php echo BASE_PATH;?>installation/step5/" method="post">
	<input type="hidden" name="form" value="1">

	<div class="infofield">ФИО</div>
	<input type="text" name="fio" size="40" value="<?php echo $this->view->fio;?>">

	<div class="infofield">Логин</div>
	<input type="text" name="name" size="40" value="<?php echo $this->view->admin_name;?>">

	<div class="infofield">Пароль</div>
	<input type="text" name="pass" size="40" value="">

	<div class="infofield">E-mail адрес</div>
	<input type="text" name="mail" size="40" value="<?php echo $this->view->mail;?>">

	<div class="infofield">Папка административной части</div>
	<input type="text" name="folder" size="40" value="<?php echo $this->view->folder;?>" maxlength="20">

	<div class="infofield">Папка для хранения пользовательских файлов</div>
	<input type="text" name="userfiles" size="40" value="<?php echo $this->view->userfiles;?>">

	<br><br>
	<input type="button" class="btn btn_blue" value="Назад" onclick="window.location='<?php echo BASE_PATH;?>installation/step3/';" /> &nbsp; &nbsp; <input type="submit" value="Далее" class="btn"/>

</form></div>
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

?>
						
<form action="<?php echo BASE_PATH;?>installation/step6/" method="post" enctype="multipart/form-data">
<input type="hidden" name="form" value="1">
<div class="box box_install">
	<h2>Установить базовую версию</h2>

	<?php
	foreach ($this->view->modules as $module)
	{
		echo '<input type="checkbox" name="modules['.$module["name"].']" id="input_modules_'.$module["name"].'" value="1" checked> <label for="input_modules_'.$module["name"].'"><b>'.$module["title"].'</b></label> ';
	}?>
		<div class="hr"></div>
		<input type="checkbox" name="example_yes" id="input_example_yes" value="1">
		<label for="input_example_yes"><b>Заполнить сайт демо-контентом (займет несколько минут)</b></label>
		<input type="checkbox" name="lang_yes" id="input_lang_yes" value="1">
		<label for="input_lang_yes"><b>Создать две языковые версии сайта (Русский/English)</b></label>
	<?php 
	$disabled = explode(',', ini_get('disable_functions'));
	if(! function_exists('set_time_limit') || in_array('set_time_limit', $disabled))
	{
		echo '<br><span style="color:red">Установка демо-данных – длительный процесс. Возможно, скриптам не хватит времени, установленного ограничением max_execution_time.</span>';
	}
	?>
	</div>
	<b>или</b>
	<div class="box box_install">
		<h2>Установить тематический сайт</h2>
		
		<div class="inp-file">
			<input type="file" name="custom">
			<span class="btn btn_blue btn_small btn_inp_file">
				Загрузить шаблон сайта
			</span>
		</div>
		
		<span class="box__unit">(выбрать шаблон тематического сайта можно в <a href="<?php echo "http".(IS_HTTPS ? "s" : '')."://"; ?>addons.diafan.ru/templates/" target="_blank">каталоге шаблонов</a>)</span>
	</div>

	<div class="box-btns">
		<span class="btn btn_blue" onclick="window.location='<?php echo BASE_PATH;?>installation/step4/';">
			Назад
		</span>
		&nbsp;
		<button class="btn btn_next btn_save">
			Далее
		</button>
	</div>
</div>
</form>
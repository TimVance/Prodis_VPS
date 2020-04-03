<?php
/**
 * Настройки модуля
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

/**
 * Images_admin_config
 */
class Images_admin_config extends Frame_admin
{
	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'base' => array (
			'hr1' => array(
				'type' => 'title',
				'name' => 'Основные',
			),
			'hash_compare' => array(
				'type' => 'checkbox',
				'name' => 'Загружать только уникальные изображения',
				'help' => 'Если отмечено, то при загрузке изображений на сайт, будет проводится проверка на их уникальность. Вместо загрузки дублирующего изображения будет создана ссылка на оригинальное изображение.',
			),
			'hash_refresh' => array(
				'type' => 'function',
				'no_save' => true,
				'hide' => true,
				'depend' => 'hash_compare',
			),
		),
	);

	/**
	 * @var array названия табов
	 */
	public $tabs_name = array(
		'base' => 'Основные настройки',
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		//'tab_card', // использование вкладок
		'config', // файл настроек модуля
	);

	/**
	 * Редактирование поля "Генерация хэш значений имеющихся на сайте изображений"
	 *
	 * @return void
	 */
	public function edit_config_variable_hash_refresh()
	{
		echo '
		<div class="unit depend_field" id="hash_refresh" depend="hash_compare">';
		if(DB::query_result("SELECT COUNT(*) FROM {images} WHERE hash='' AND trash='0' LIMIT 1") > 0)
		{
			echo '
			<div class="box__warning">
				<i class="fa fa-warning"></i>
				'.$this->diafan->_('На сайте присутствуют изображения без соответствующих хэш-записей.%sРекомендуем для корректной работы модуля обновить хэш изображений.', '<br />').'
			</div>';
		}
		echo '
			<button id="hash_refresh_button" class="btn btn_blue btn_small"">'.$this->diafan->_("Обновить хэш изображений").'</button>
		</div>';
	}
}

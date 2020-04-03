<?php
/**
 * Редактирование категорий форума
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
 * Forum_admin_category
 */
class Forum_admin_category extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'forum_category';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'default' => true,
			),
			'block_id' => array(
				'type' => 'select',
				'name' => 'Блок форума',
				'select_db' => array(
					'table' => 'forum_blocks',
					'where' => "trash='0'",
					'order' => 'sort ASC',
				),
			),
			'counter_view' => array(
				'type' => 'function',
				'name' => 'Количество просмотров',
				'help' => 'Только для разделов и тем.',
				'no_save' => true,
			),
			'rewrite'       => array(
				'type' => 'function',
				'name' => 'Псевдоссылка',
				'help' => 'ЧПУ (человеко-понятные урл url), адрес страницы вида: *http://site.ru/psewdossylka/*. Смотрите параметры сайта.',
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Редактирование порядка следования категории в списке. Поле доступно для редактирования только для категорий, отображаемых на сайте.',
			),
			'timeedit' => array(
				'type' => 'text',
				'name' => 'Время последнего изменения',
				'help' => 'Изменяется после сохранения элемента. Отдается в заголовке *Last Modify*.',
			),
			'map' => array(
				'type' => 'module',
				'name' => 'Индексирование для карты сайта',
				'help' => 'Категория автоматически индексируется для карты сайта sitemap.xml.',
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'sort' => array(
			'name' => 'Сортировка',
			'type' => 'numtext',
			'sql' => true,
			'fast_edit' => true,
		),
		'name' => array(
			'name' => 'Название'
		),
		'block_id' => array(
			'name' => 'Блок форума',
			'sql' => true,
			'no_important' => true,
		),
		'adapt' => array(
			'class_th' => 'item__th_adapt',
		),
		'separator' => array(
			'class_th' => 'item__th_seporator',
		),				
		'actions' => array(
			'act' => true,
			'trash' => true,
		),
	);

	/**
	 * @var array поля для фильтра
	 */
	public $variables_filter = array (
		'block_id' => array(
			'type' => 'select',
			'name' => 'Искать по блоку',
		),
		'name' => array(
			'type' => 'text',
			'name' => 'Искать по названию',
		),
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'category', // часть модуля - категории
	);

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить категорию');
	}

	/**
	 * Выводит список категорий
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();

		if (! $this->diafan->count && ! $this->diafan->get_nav_params["block_id"])
		{
			echo '<div class="error">'.$this->diafan->_('Обязательно создайте категории форума!').'</div>';
		}
	}

	/**
	 * Выводит блок форума в списке
	 * @return string
	 */
	public function list_variable_block_id($row)
	{
		return '<div class="no_important">'.(! empty($this->cache["blocks_name"][$row["block_id"]]) ? $this->cache["blocks_name"][$row["block_id"]] : '').'</div>';
	}

	/**
	 * Редактирование поля "Количетсво просмотров"
	 * @return void
	 */
	public function edit_variable_counter_view()
	{
		if ($this->diafan->is_new)
			return;
	
		echo '<div class="unit"><b>'.$this->diafan->variable_name().':</b> '.$this->diafan->value.'</div>';
	}
}
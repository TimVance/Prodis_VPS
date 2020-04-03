<?php
/**
 * Редактирование категорий рассылки
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
 * Subscription_admin_category
 */
class Subscription_admin_category extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'subscription_category';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
				'multilang' => true,
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'default' => true,
			),
			'text' => array(
				'type' => 'editor',
				'name' => 'Описание',
				'help' => 'Описание категории.',
				'multilang' => true,
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Редактирование порядка отображения пункта. Поле доступно для редактирования только для незаблокированных категорий.',
			),
			'parent_id' => array(
				'type' => 'select',
				'name' => 'Вложенность: принадлежит',
				'help' => 'Категория верхнего уровня.',
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
		'plus' => array(),
		'name' => array(
			'name' => 'Название'
		),
		'actions' => array(
			'add' => true,
			'act' => true,
			'trash' => true,
		),
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'category', // часть модуля - категории
		'category_rel', // работают вместе с таблицей {module_category_rel}
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
		if(! $this->diafan->configmodules("cat"))
		{
			echo '<div class="error">'.$this->diafan->_('Подключите опцию «Использовать категории» в настройках модуля.').'</div>';
		}
		$this->diafan->list_row();
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("subscription_emails_cat_unrel", "cat_id IN (".implode(",", $del_ids).")");
	}
}
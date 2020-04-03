<?php
/**
 * Редактирование расширений заказа
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

/**
 * Order_admin_backend
 */
class Order_admin_backend extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'shop_order_backend';
	
	/**
	 * @var string тип элемента
	 */
	public $element_type = 'element';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
				'help' => 'Название расширения.',
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Использовать в заказе',
				'help' => 'Активация/деактивация расширения.',
				'default' => true,
			),
			'list' => array(
				'type' => 'checkbox',
				'name' => 'Выводить в списке',
				'help' => 'Бэкенд будет подключен в списке заказов, если есть функция list() в файле order.*backend*.admin.order.php.',
				'default' => true,
			),
			'backend' => array(
				'type' => 'function',
				'name' => 'Бэкенд',
				'help' => 'Список всех доступных для подключения бэкендов.',
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Изменить положение текущего расширения среди других расширений. В списке расширений можно сортировать расширения простым перетаскиванием мыши.',
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
		'actions' => array(
			'act' => true,
			'trash' => true,
		),
	);

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить');
	}

	/**
	 * Выводит список расширений
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("shop_order_backend_element", "backend_id IN (".implode(",", $del_ids).")");
	}
}

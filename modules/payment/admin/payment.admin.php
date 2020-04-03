<?php
/**
 * Редактирование методов оплаты
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
 * Shop_admin_payment
 */
class Payment_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'payment';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
				'help' => 'Название метода оплаты, выводится на сайте.',
				'multilang' => true,
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'default' => true,
				'multilang' => true,
			),
			'backend' => array(
				'type' => 'function',
				'variable' => 'payment',
				'name' => 'Платежная система',
				'help' => 'Система безналичной оплаты заказа. Если платежная система не задана, при оформлении заказа сразу перекидывает на страницу завершения заказа. Параметры подключения выдаются платежными системами при одобрении Вашего магазина.',
			),
			'text' => array(
				'type' => 'textarea',
				'name' => 'Описание',
				'help' => 'Описание метода оплаты, выводится на сайте в форме заказа.',
				'multilang' => true,
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Изменить положение текущего метода оплаты среди других методов. В списке методов можно сортировать методы простым перетаскиванием мыши.',
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
	 * Выводит список методов оплаты
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}
}

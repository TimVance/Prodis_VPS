<?php
/**
 * Валюты
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
 * Shop_admin_currency
 */
class Shop_admin_currency extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'shop_currency';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
			),
			'exchange_rate' => array(
				'type' => 'floattext',
				'name' => 'Курс к основной валюте',
				'help' => 'Все товары на сайте показываются только в основной валюте! Сохраняя в дальнейшем товар в данной валюте, его стоимость будет пересчитываться на сайте по указанному курсу.',
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'name' => array(
			'name' => 'Название'
		),
		'actions' => array(
			'trash' => true,
		),
	);

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить валюту');
	}

	/**
	 * Выводит список заказов
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Сохранение поля "Курс валюты"
	 * @return void
	 */
	public function save_variable_exchange_rate()
	{
		DB::query("UPDATE {shop_currency} SET exchange_rate='%f' WHERE id=%d", str_replace(',', '.', $_POST["exchange_rate"]), $this->diafan->id);
		$this->diafan->_shop->price_calc(0, 0, $this->diafan->id);
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("shop_price", "currency_id IN (".implode(",", $del_ids).")");
	}
}
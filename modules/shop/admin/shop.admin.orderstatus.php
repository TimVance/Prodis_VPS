<?php
/**
 * Статусы заказа
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined('DIAFAN')) {
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
 * Shop_admin_orderstatus
 */
class Shop_admin_orderstatus extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'shop_order_status';

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
			'status' => array(
				'type' => 'select',
				'name' => 'Действие',
				'help' => 'Действие, при котором статус устанавливается. Действие определяет цвет статуса в панели администрирования.',
				'select' => array(
					4 => 'нет',
					0 => 'поступление заказа',
					1 => 'оплата',
					2 => 'отмена заказа',
					3 => 'выполнение',
				),
			),
			'color' => array(
				'type' => 'text',
				'name' => 'Цвет',
				'help' => 'Цвет, которым будет выделен статус в списке заказов и в личном кабинете пользователя. Пример: red или #ff0000.',
			),
			'count_minus' => array(
				'type' => 'checkbox',
				'name' => 'Списание товара',
				'help' => 'При установке статуса происходит уменьшение количества товара, указанного в заказе, если товары еще не списаны. Если значение не установлено, то при установке статуса списание товаров будет отменено, количество товара на складе увеличиться.',
			),
			'send_mail' => array(
				'type' => 'checkbox',
				'name' => 'Отправлять уведомление пользователю о смене статуса',
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Редактирование порядка следования поля в списке.',
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
		'status' => array(
			'type' => 'select',
			'name' => 'Действие',
			'sql' => true,
			'no_important' => true,
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
		$this->diafan->addnew_init('Добавить статус');
	}

	/**
	 * Выводит список статусов заказа
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	public function edit_variable_color()
	{
		echo '
		<div class="unit">
			<div class="infofield">
				'.$this->diafan->variable_name().$this->diafan->help().'
			</div>';
		echo '<input name="color" value="'.$this->diafan->values("color").'" type="text" style="color: black; text-shadow: 0px 0px 1px white, 0px 0px 1px white; background-color: '.$this->diafan->values("color").'; width: 120px;" placeholder="'.$this->diafan->_('red или #ff0000').'">';
		echo '</div>';
	}	

	/**
	 * Сохранение поля "Действие"
	 * 
	 * @return void
	 */
	public function save_variable_status()
	{
		$this->diafan->set_query("status='%d'");
		$this->diafan->set_value($_POST["status"]);
	}
}
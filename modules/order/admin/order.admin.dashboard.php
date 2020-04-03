<?php
/**
 * Заказы для событий
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
 * Order_admin_dashboard
 */
class Order_admin_dashboard extends Diafan
{
	/**
	 * @var string название таблицы
	 */
	public $name = 'Новые заказы';

	/**
	 * @var integer порядковый номер для сортировки
	 */
	public $sort = 3;

	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'shop_order';

	/**
	 * @var string нет элементов
	 */
	public $empty_rows = 'Нет новых заказов.';

	/**
	 * @var string условие для отбора
	 */
	public $where = "status='0'";

	/**
	 * @var array поля в таблице
	 */
	public $variables = array (
		'created' => array(
			'name' => 'Дата и время',
			'type' => 'datetime',
			'sql' => true,
		),
		'id' => array(
			'name' => 'Номер заказа',
		),
		'summ' => array(
			'name' => 'Сумма',
			'sql' => true,
		),
	);

	/**
	 * Выводит номер заказа в списке
	 * 
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @param array $rows все элементы
	 * @return string
	 */
	public function list_variable_id($row, $var, $rows)
	{
		return '<div class="number"><a href="'.BASE_PATH_HREF.'order/edit'.$row["id"].'/">'.$this->diafan->_('Заказ № %d', $row["id"]).'</a></div>';
	}

	/**
	 * Выводит сумму заказа в списке заказов
	 * 
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @param array $rows все элементы
	 * @return string
	 */
	public function list_variable_summ($row, $var, $rows)
	{
		return '<div class="price">'
		.($row["summ"]
		 ? $this->format_summ($row["summ"]).' '.$this->diafan->configmodules("currency", "shop")
		 : '').'</div>';
	}

	/**
	 * Форматирует сумму
	 * 
	 * @param float $summ сумма
	 * @return string
	 */
	private function format_summ($summ)
	{
		if(($summ * 100) % 100)
		{
			$num_decimal_places = 2;
		}
		else
		{
			$num_decimal_places = 0;
		}
		return number_format($summ, $num_decimal_places, ".", "");
	}
}
<?php
/**
 * Подключение модуля к административной части других модулей
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
 * Shop_admin_inc
 */
class Shop_admin_inc extends Diafan
{
	/**
	 * Блокирует/разблокирует различные элементы в магазине
	 * 
	 * @param string $table таблица
	 * @param array $element_ids номера элементов
	 * @param integer $act блокировать/разблокировать
	 * @return void
	 */
	public function act($table, $element_ids, $act)
	{
		// если блокирует/разблокирует скидки не через форму, пересчитывает цены
		if ($table == "shop_discount" && ! $this->diafan->is_action("save"))
		{
			foreach ($element_ids as $element_id)
			{
				$this->diafan->_shop->price_calc(0, $element_id);
			}
			$this->diafan->_cache->delete("", "shop");
		}
	}

	/**
	 * Восстанавливает из корзины различные элементы модуля
	 * 
	 * @param string $table таблица
	 * @param array $id номер элемента
	 * @return void
	 */
	public function restore_from_trash($table, $id)
	{
		// если восстанавливает активную скидки, пересчитывает цены
		if ($table == "shop_discount")
		{
			$rows = DB::query_fetch_all("SELECT id FROM {shop_discount} WHERE act='1' AND id=%d", $id);
			foreach ($rows as $row)
			{
				$this->diafan->_shop->price_calc(0, $row["id"]);
			}
			$this->diafan->_cache->delete("", "shop");
		}
	}
}
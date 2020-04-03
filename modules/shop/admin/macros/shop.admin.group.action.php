<?php
/**
 * Макрос для групповой операции: акция
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
 * Shop_admin_group_action
 */
class Shop_admin_group_action extends Diafan
{
	/**
	 * Возвращает настройки
	 *
	 * @param string $value последнее выбранное групповое действие
	 * @return array|false
	 */
	public function show($value)
	{
		return array(
			'name' => 'Акция',
		);
	}

	/**
	 * Помечает товары как маркером "Акция"
	 *
	 * @return void
	 */
	public function action()
	{
		if(! empty($_POST["ids"]))
		{
			$ids = $this->diafan->filter($_POST["ids"], "integer");
		}
		elseif(! empty($_POST["id"]))
		{
			$ids = array(intval($_POST["id"]));
		}
		if(! empty($ids))
		{
			DB::query("UPDATE {shop} SET action='1' WHERE id IN (%s)", implode(",", $ids));
			$this->diafan->_cache->delete("", "shop");
			$this->result["action"] = 'macros_group_shop_not_action';
		}
	}
}
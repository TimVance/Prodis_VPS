<?php
/**
 * Расширение для интерфейса "Заказы"
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

class Order_packing_list_admin_order extends Diafan
{
	/**
	 * Редактирвание поля расширения
	 *
	 * @return void
	 */
	public function edit()
	{
		if($this->diafan->is_new)
			return;

		echo '
		<div class="unit">
			<a href="'.BASE_PATH.'order/get/packing_list/'.$this->diafan->id.'/" target="_blank"><i class="fa fa-sticky-note-o"></i> '.$this->diafan->_('Сформировать товарный чек для печати').'</a>'.$this->diafan->help('Ссылка на товарный чек. Шаблон редактируется в файле modules/order/backend/packing_list/order.packing_list.get.view.php.').'
		</div>';
	}
}
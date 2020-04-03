<?php
/**
 * Контроллер модуля «Корзина товаров, оформление заказа»
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
 * Cart
 */
class Cart extends Controller
{
	/**
	 * @var array переменные, передаваемые в URL страницы
	 */
	public $rewrite_variable_names = array('step', 'show');

	/**
	 * Инициализация модуля
	 * 
	 * @return void
	 */
	public function init()
	{
		if ($this->diafan->configmodules('not_buy', 'shop') || ($this->diafan->configmodules('security_user', 'shop') && ! $this->diafan->_users->id))
			return false;

		if (empty($this->diafan->_route->step))
		{
			$this->model->form();
		}
		// платежная система
		elseif ($this->diafan->_route->step == 2 && $this->diafan->_route->show)
		{
			$this->model->payment();
		}
		//подтверждение или опровержение платежа
		elseif (($this->diafan->_route->step == 3 || $this->diafan->_route->step == 4))
		{
			$this->model->result();
		}
		else
		{
			Custom::inc('includes/404.php');
		}
		$this->diafan->_site->hide_previous_next = true;
		$this->diafan->_site->nocache = true;
		$this->diafan->_site->timeedit = time();
	}

	/**
	 * Обрабатывает полученные данные из формы
	 * 
	 * @return void
	 */
	public function action()
	{
		if($this->diafan->configmodules('security_user', 'shop'))
		{
			$this->action->check_user();

			if ($this->action->result())
				return;
		}
		if(! empty($_POST["action"]))
		{
			switch($_POST["action"])
			{
				case 'recalc':
					return $this->action->recalc();

				case 'clear':
					return $this->action->clear();

				case 'order':
					return $this->action->order();

				case 'one_click':
					return $this->action->one_click();

				case 'upload_image':
					return $this->action->upload_image();

				case 'delete_image':
					return $this->action->delete_image();
			}
		}
	}

	/**
	 * Шаблонная функция: выводит информацию о заказанных товарах, т. н. корзину.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/cart/views/cart.view.show_block_**template**.php; по умолчанию шаблон modules/cart/views/cart.view.show_block.php)
	 * @return void
	 */
	public function show_block($attributes)
	{
		if ($this->diafan->configmodules('not_buy', 'shop') || ($this->diafan->configmodules('security_user', 'shop') && ! $this->diafan->_users->id))
			return;

		$this->get_attributes($attributes, 'template');

		$result = $this->model->show_block(true);

		if($result)
		{
			$result["attributes"] = $attributes;
			echo $this->diafan->_tpl->get('show_block', 'cart', $result, $attributes["template"]);
		}
	}

	/**
	 * Шаблонная функция: выводит информацию о последнем совершенном заказе.
	 * 
	 * @param array $attributes атрибуты шаблонного тега
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/cart/views/cart.view.show_last_order_**template**.php; по умолчанию шаблон modules/cart/views/cart.view.show_last_order.php)
	 * @return void
	 */
	public function show_last_order($attributes)
	{
		if ($this->diafan->configmodules('not_buy', 'shop') || ($this->diafan->configmodules('security_user', 'shop') && ! $this->diafan->_users->id))
			return;

		$this->get_attributes($attributes, 'template');

		$result = $this->model->show_last_order();

		if($result)
		{
			$result["attributes"] = $attributes;
			echo $this->diafan->_tpl->get('show_last_order', 'cart', $result, $attributes["template"]);
		}
	}
}
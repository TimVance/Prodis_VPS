<?php
/**
 * Контроллер модуля «Магазин»
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
 * Shop
 */
class Shop extends Controller
{
	/**
	 * @var array переменные, передаваемые в URL страницы
	 */
	public $rewrite_variable_names = array('page', 'show', 'param', 'sort', 'brand');

	/**
	 * Инициализация модуля
	 *
	 * @return void
	 */
	public function init()
	{
		if($this->diafan->configmodules("cat"))
		{
			$this->rewrite_variable_names[] = 'cat';
		}
		if ($this->diafan->_route->show)
		{
			if($this->diafan->_route->page || $this->diafan->_route->param || $this->diafan->_route->sort || $this->diafan->_route->brand)
			{
				Custom::inc('includes/404.php');
			}
			$this->model->id();
		}
		elseif ($this->diafan->_route->param)
		{
			$this->model->list_param();
		}
		elseif(isset($_GET["action"]) && $_GET["action"] === 'search')
		{
			$this->model->list_search();
		}
		elseif(isset($_GET["action"]) && $_GET["action"] === 'compare' && ! $this->diafan->configmodules('hide_compare', "shop"))
		{
			$this->model->compare();
		}
		elseif(isset($_GET["action"]) && $_GET["action"] === 'file' && isset($_GET["code"]))
		{
			$this->model->file_get();
		}
		elseif(! $this->diafan->configmodules("cat") || $this->diafan->configmodules("first_page_list") || $this->diafan->_route->cat || $this->diafan->_route->brand)
		{
			$this->model->list_();
		}
		else
		{
			$this->model->first_page();
		}
		$this->model->result();
	}

	/**
	 * Обрабатывает полученные данные из формы
	 *
	 * @return void
	 */
	public function action()
	{
		if(! empty($_POST["action"]))
		{
			switch($_POST["action"])
			{
				case 'buy':
					return $this->action->buy();

				case 'check':
					return $this->action->check();

				case 'wish':
					return $this->action->wish();

				case 'wait':
					return $this->action->wait();

				case 'add_coupon':
					return $this->action->add_coupon();

				case 'compare_goods':
					return $this->action->compare_goods();

				case 'compare_delete_goods':
					return $this->action->compare_delete_goods();

				case 'search':
					$this->action->search();
					break;
			}
		}
	}

	/**
	 * Шаблонная функция: выводит несколько товаров из каталога.
	 *
	 * @param array $attributes атрибуты шаблонного тега
	 * count - количество выводимых товаров (по умолчанию 3)
	 * site_id - страницы, к которым прикреплен модуль. Идентификаторы страниц перечисляются через запятую. Можно указать отрицательное значение, тогда будут исключены товары из указанного раздела. По умолчанию выбираются все страницы
	 * cat_id - категории товаров, если в настройках модуля отмечено «Использовать категории». Можно указать отрицательное значение, тогда будут исключены товары из указанной категории. Идентификаторы категорий перечисляются через запятую. Можно указать значение **current**, тогда будут показаны товары из текущей (открытой) категории магазина или из всех категорий, если ни одна категория не открыта. По умолчанию категория не учитывается, выводятся все товары
	 * ids - Товары. Можно указать отрицательное значение, тогда будут исключены товары. Идентификаторы товаров перечисляются через запятую. По умолчанию товары не учитывается, выводятся все товары
	 * brand_id - производители товаров. Можно указать отрицательное значение, тогда будут исключены товары указанного производителя. Идентификаторы производителя перечисляются через запятую. По умолчанию производитель не учитывается, выводятся все товары
	 * sort - сортировка товаров: по умолчанию как на странице модуля, **date** – по дате, **rand** – в случайном порядке, **price** - по цене, **sale** – по количеству продаж
	 * images - количество изображений, прикрепленных к товару
	 * images_variation - тег размера изображений, задается в настроках модуля
	 * param - значения дополнительных характеристик
	 * hits_only - выводить только товары с пометкой «Хит»: **true** – выводить только товары с пометкой «Хит», по умолчанию пометка «Хит» будет игнорироваться
	 * action_only - выводить только товары с пометкой «Акция»: **true** – выводить только товары с пометкой «Акция», по умолчанию пометка «Акция» будет игнорироваться
	 * new_only - выводить только товары с пометкой «Новинка»: **true** – выводить только товары с пометкой «Новинка», по умолчанию пометка «Новинка» будет игнорироваться
	 * discount_only - выводить только товары, на которые действует скидка: **true** – выводить только товары, на которые действует скидка, по умолчанию скидка у товаров игнорируется
	 * only_module - выводить блок только на странице, к которой прикреплен модуль «Магазин»: **true** – выводить блок только на странице модуля, по умолчанию блок будет выводиться на всех страницах
	 * tag - тег, прикрепленный к товарам
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/shop/views/shop.view.show_block_**template**.php; по умолчанию шаблон modules/shop/views/shop.view.show_block.php)
	 *
	 * @return void
	 */
	public function show_block($attributes)
	{
		$this->get_attributes($attributes, 'count', 'site_id', 'cat_id', 'ids', 'brand_id', 'sort', 'images', 'images_variation', 'param', 'hits_only', 'action_only', 'new_only', 'discount_only', 'only_module', 'only_shop', 'tag', 'template');

		$count   = $attributes["count"] ? intval($attributes["count"]) : 3;
		$site_ids = explode(",", $attributes["site_id"]);
		$cat_ids  = explode(",", $attributes["cat_id"]);
		$ids  = explode(",", $attributes["ids"]);
		$brand_ids  = explode(",", $attributes["brand_id"]);
		$sort    = in_array($attributes["sort"], array("date", "rand", "price", "sale")) ? $attributes["sort"] : "";
		$images  = intval($attributes["images"]);
		$images_variation = $attributes["images_variation"] ? strval($attributes["images_variation"]) : 'medium';
		$param   = $attributes["param"];
		$hits_only = (bool) $attributes["hits_only"];
		$action_only  = (bool) $attributes["action_only"];
		$new_only   = (bool) $attributes["new_only"];
		$discount_only   = (bool) $attributes["discount_only"];
		$tag = $attributes["tag"] && $this->diafan->configmodules('tags', 'shop') ? strval($attributes["tag"]) : '';

		// устаревший атрибут
		if($attributes["only_shop"])
		{
			$attributes["only_module"] = true;
		}

		if ($attributes["only_module"] && ($this->diafan->_site->module != "shop" || ! in_array($this->diafan->_site->id, $site_ids)))
			return;

		if($attributes["cat_id"] == "current")
		{
			if($this->diafan->_site->module == "shop" && (empty($site_ids[0]) || in_array($this->diafan->_site->id, $site_ids))
			   && $this->diafan->_route->cat)
			{
				$cat_ids[0] = $this->diafan->_route->cat;
			}
			else
			{
				$cat_ids = array();
			}
		}

		$this->model->show_block($count, $site_ids, $cat_ids, $ids, $brand_ids, $sort, $images, $images_variation, $param, $hits_only,
		$action_only, $new_only, $discount_only, $tag);
		$this->model->result();
		$this->model->result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_block', 'shop', $this->model->result, $attributes["template"]);
	}

	/**
	 * Шаблонная функция: на странице товара выводит похожие товары. По умолчанию связи между товарами являются односторонними, это можно изменить, отметив опцию «В блоке похожих товаров связь двусторонняя» в настройках модуля.
	 *
	 * @param array $attributes атрибуты шаблонного тега
	 * count - количество выводимых товаров (по умолчанию 3)
	 * images - количество изображений, прикрепленных к товару
	 * images_variation - тег размера изображений, задается в настроках модуля
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/shop/views/shop.view.show_block_rel_**template**.php; по умолчанию шаблон modules/shop/views/shop.view.show_block_rel.php)
	 * @return void
	 */
	public function show_block_rel($attributes)
	{
		if ($this->diafan->_site->module != "shop" || ! $this->diafan->_route->show)
			return false;

		$this->get_attributes($attributes, 'count', 'images', 'images_variation', 'template');

		$count   = $attributes["count"] ? intval($attributes["count"]) : 3;
		$images  = intval($attributes["images"]);
		$images_variation = $attributes["images_variation"] ? strval($attributes["images_variation"]) : 'medium';

		$this->model->show_block_rel($count, $images, $images_variation);
		$this->model->result();
		$this->model->result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_block_rel', 'shop', $this->model->result, $attributes["template"]);
	}

	/**
	 * Шаблонная функция: товары, которые обычно покупают с текущим товаром.
	 *
	 * @param array $attributes атрибуты шаблонного тега
	 * count - количество выводимых товаров (по умолчанию 3)
	 * images - количество изображений, прикрепленных к товару
	 * images_variation - тег размера изображений, задается в настроках модуля
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/shop/views/shop.view.show_block_order_rel_**template**.php; по умолчанию шаблон modules/shop/views/shop.view.show_block_order_rel.php)
	 *
	 * @return void
	 */
	public function show_block_order_rel($attributes)
	{
		if ($this->diafan->_site->module != "shop" || ! $this->diafan->_route->show)
			return false;

		$this->get_attributes($attributes, 'count', 'images', 'images_variation', 'template');

		$count   = $attributes["count"] ? intval($attributes["count"]) : 3;
		$images  = intval($attributes["images"]);
		$images_variation = $attributes["images_variation"] ? strval($attributes["images_variation"]) : 'medium';

		$this->model->show_block_order_rel($count, $images, $images_variation);
		$this->model->result();
		$this->model->result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_block_order_rel', 'shop', $this->model->result, $attributes["template"]);
	}

	/**
	 * Шаблонная функция: выводит несколько производителей.
	 *
	 * @param array $attributes атрибуты шаблонного тега
	 * count - количество выводимых производителей (по умолчанию выводяться все производители)
	 * site_id - страницы, к которым прикреплен модуль. Идентификаторы страниц перечисляются через запятую. Можно указать отрицательное значение, тогда будут исключены производители из указанного раздела. По умолчанию выбираются все страницы
	 * cat_id - категории товаров, если в настройках модуля отмечено «Использовать категории». Можно указать отрицательное значение, тогда будут исключены производители из указанной категории. Идентификаторы категорий перечисляются через запятую. Можно указать значение **current**, тогда будут показаны производители из текущей (открытой) категории магазина или из всех категорий, если ни одна категория не открыта. По умолчанию категория не учитывается, выводятся все производители
	 * sort - сортировка производителей: по умолчанию как на странице модуля, **name** – по имени, **rand** – в случайном порядке
	 * images - количество изображений, прикрепленных к производителю
	 * images_variation - тег размера изображений, задается в настроках модуля
	 * only_module - выводить блок только на странице, к которой прикреплен модуль «Магазин»: **true** – выводить блок только на странице модуля, по умолчанию блок будет выводиться на всех страницах
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/shop/views/shop.view.show_brand_**template**.php; по умолчанию шаблон modules/shop/views/shop.view.show_brand.php)
	 *
	 * @return void
	 */
	public function show_brand($attributes)
	{
		$this->get_attributes($attributes, 'count', 'site_id', 'cat_id', 'sort', 'images', 'images_variation', 'only_module', 'template');

		$count   = $attributes["count"] ? intval($attributes["count"]) : "all";
		$site_ids = explode(",", $attributes["site_id"]);
		if($attributes["cat_id"] === "current")
		{
			$cat_ids = "current";
		}
		else
		{
			$cat_ids  = explode(",", $attributes["cat_id"]);
		}
		$sort    = in_array($attributes["sort"], array("name", "rand")) ? $attributes["sort"] : "";

		$images  = intval($attributes["images"]);
		$images_variation = $attributes["images_variation"] ? strval($attributes["images_variation"]) : 'medium';

		if ($attributes["only_module"] && ($this->diafan->_site->module != "shop" || in_array($this->diafan->_site->id, $site_ids)))
			return;

		$this->model->show_brand($count, $site_ids, $cat_ids, $sort, $images, $images_variation);
		$this->model->result();
		$this->model->result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_brand', 'shop', $this->model->result, $attributes["template"]);
	}

	/**
	 * Шаблонная функция: выводит несколько категорий.
	 *
	 * @param array $attributes атрибуты шаблонного тега
	 * site_id - страницы, к которым прикреплен модуль. Идентификаторы страниц перечисляются через запятую. Можно указать отрицательное значение, тогда будут исключены категории из указанного раздела. По умолчанию выбираются все страницы
	 * images - количество изображений, прикрепленных к категории
	 * images_variation - тег размера изображений, задается в настроках модуля
	 * only_module - выводить блок только на странице, к которой прикреплен модуль «Магазин»: **true** – выводить блок только на странице модуля, по умолчанию блок будет выводиться на всех страницах
	 * count_level - количество уровней
	 * number_elements - выводить количество товаров в категории: **true** – выводить количество товаров, по умолчанию количество не выводится
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/shop/views/shop.view.show_category_**template**.php; по умолчанию шаблон modules/shop/views/shop.view.show_category.php)
	 *
	 * @return void
	 */
	public function show_category($attributes)
	{
		$this->get_attributes($attributes, 'site_id', 'images', 'images_variation', 'only_module', 'count_level', 'number_elements', 'template');

		$site_ids = explode(",", $attributes["site_id"]);

		$images  = intval($attributes["images"]);
		$images_variation = $attributes["images_variation"] ? strval($attributes["images_variation"]) : 'medium';

		$count_level = intval($attributes["count_level"]);
		$number_elements = ! empty($attributes["number_elements"]) ? true : false;

		if ($attributes["only_module"] && ($this->diafan->_site->module != "shop" || in_array($this->diafan->_site->id, $site_ids)))
			return;

		$this->model->show_category($site_ids, $images, $images_variation, $count_level, $number_elements);
		$this->model->result();
		$this->model->result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_category', 'shop', $this->model->result, $attributes["template"]);
	}

	/**
	 * Шаблонная функция: выводит форму поиска товаров. Если для категорий прикреплены дополнительные характеристики, то поиск по ним производится только на странице категории. Поиск по обязательным полям подключается в настройках модуля (опции «Искать по цене», «Искать по артикулу», «Искать товары по акции», «Искать по новинкам», «Искать по хитам»). Если в форму поиска выведены характеристики с типом «выпадающий список» и «список с выбором нескольких значений», то значения характеристик, которые не найдут ни один товар, в форме поиска не выведутся.
	 *
	 * @param array $attributes атрибуты шаблонного тега
	 * site_id - страницы, к которым прикреплен модуль. Идентификаторы страниц перечисляются через запятую. По умолчанию выбираются все страницы. Если выбрано несколько страниц сайта, то в форме поиска появляется выпадающих список по выбранным страницам. Можно указать отрицательное значение, тогда указанные страницы будут исключены из списка
	 * cat_id - категории товаров, если в настройках модуля отмечено «Использовать категории». Идентификаторы категорий перечисляются через запятую. Можно указать значение **current**, тогда поиск будет осуществляться по текущей (открытой) категории магазина или по всем категориям, если ни одна категория не открыта. Если выбрано несколько категорий, то в форме поиска появится выпадающий список категорий магазина, который будет подгружать прикрепленные к категориям характеристики. Можно указать отрицательное значение, тогда указанные категории будут исключены из списка. Можно указать значение **all**, тогда поиск будет осуществлятся по всем категориям товаров и в форме будут участвовать только общие характеристики. Атрибут не обязателен
	 * ajax - подгружать результаты поиска без перезагрузки страницы: **true** – результаты поиска подгружаются, по умолчанию будет перезагружена вся страница. Результаты подгружаются только если открыта страница со списком товаром, иначе поиск работает обычным образом
	 * only_module - выводить форму поиска только на странице модуля «Магазин»: **true** – выводить форму только на странице модуля, по умолчанию форма будет выводиться на всех страницах
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/shop/views/shop.view.show_search_**template**.php; по умолчанию шаблон modules/shop/views/shop.view.show_search.php)
	 *
	 * @return void
	 */
	public function show_search($attributes)
	{
		$this->get_attributes($attributes, 'site_id', 'cat_id', 'ajax', 'only_module', 'only_shop', 'template');

		$site_ids  = explode(",", $attributes["site_id"]);

		$cat_ids   = $attributes["cat_id"] === 'current' || $attributes["cat_id"] === 'all' ? $attributes["cat_id"] : explode(",", $attributes["cat_id"]);
		$ajax      = $attributes["ajax"] == "true" ? true : false;

		if ($cat_ids === 'current')
		{
			if($this->diafan->_route->cat && $this->diafan->_site->module == "shop" && (count($site_ids) == 1 && $site_ids[0] == 0 || in_array($this->diafan->_site->id, $site_ids)))
			{
				$cat_ids  = array($this->diafan->_route->cat);
				$site_ids = array($this->diafan->_site->id);
			}
			else
			{
				$cat_ids = array();
			}
		}

		// устаревший атрибут
		if($attributes["only_shop"])
		{
			$attributes["only_module"] = true;
		}

		if ($attributes["only_module"] && ($this->diafan->_site->module != "shop" || $site_ids && ! in_array($this->diafan->_site->id, $site_ids)))
			return;

		$result = $this->model->show_search($site_ids, $cat_ids, $ajax);
		if(! $result)
			return;

		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_search', 'shop', $result, $attributes["template"]);
	}

	/**
	 * Шаблонная функция: выводит форму активирования купона на скидку, если неактивированный купон есть в системе, пользователь авторизован и у него не активирован другой купон.
	 *
	 * @param array $attributes атрибуты шаблонного тега
	 * defer - маркер отложенной загрузки шаблонного тега: **event** – загрузка контента только по желанию пользователя при нажатии кнопки "Загрузить", **emergence** – загрузка контента только при появлении в окне браузера клиента, **async** – асинхронная (одновременная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, **sync** – синхронная (последовательная) загрузка контента совместно с контентом шаблонных тегов с тем же маркером, по умолчанию отложенная загрузка не используется, обычный вывод шаблонных тегов в коде страницы
	 * defer_title - текстовая строка, выводимая на месте появления загружаемого контента с помощью отложенной загрузки шаблонного тега
	 * template - шаблон тега (файл modules/shop/views/shop.view.show_add_coupon_**template**.php; по умолчанию шаблон modules/shop/views/shop.view.show_add_coupon.php)
	 *
	 * @return void
	 */
	public function show_add_coupon($attributes)
	{
		$this->get_attributes($attributes, 'template');
		$result = $this->model->show_add_coupon();
		$result["attributes"] = $attributes;

		echo $this->diafan->_tpl->get('show_add_coupon', 'shop', $result, $attributes["template"]);
	}
}

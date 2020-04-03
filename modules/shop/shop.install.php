<?php
/**
 * Установка модуля
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

class Shop_install extends Install
{
	/**
	 * @var string название
	 */
	public $title = "Интернет-магазин";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "shop",
			"comment" => "Товары",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "TEXT",
					"comment" => "название",
					"multilang" => true,
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
					"multilang" => true,
				),
				array(
					"name" => "date_start",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата начала показа",
				),
				array(
					"name" => "date_finish",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата окончания показа",
				),
				array(
					"name" => "article",
					"type" => "VARCHAR(30) NOT NULL DEFAULT ''",
					"comment" => "артикул",
				),
				array(
					"name" => "measure_unit",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "единица измерения",
					"multilang" => true,
				),
				array(
					"name" => "weight",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "вес",
				),
				array(
					"name" => "length",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "длина",
				),
				array(
					"name" => "width",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "ширина",
				),
				array(
					"name" => "height",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "высота",
				),
				array(
					"name" => "map_no_show",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "не показывать на карте сайта: 0 - нет, 1 - да",
				),
				array(
					"name" => "changefreq",
					"type" => "ENUM( 'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never' ) NOT NULL DEFAULT 'always'",
					"comment" => "Changefreq для sitemap.xml",
				),
				array(
					"name" => "priority",
					"type" => "VARCHAR(3) NOT NULL DEFAULT ''",
					"comment" => "Priority для sitemap.xml",
				),
				array(
					"name" => "noindex",
					"type" => "ENUM('0','1') NOT NULL DEFAULT '0'",
					"comment" => "не индексировать: 0 - нет, 1 - да",
				),
				array(
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор основной категории из таблицы {shop_category}",
				),
				array(
					"name" => "site_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор страницы сайта из таблицы {site}",
				),
				array(
					"name" => "brand_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор производителя из таблицы {shop_brand}",
				),
				array(
					"name" => "keywords",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "ключевые слова, тег Keywords",
					"multilang" => true,
				),
				array(
					"name" => "descr",
					"type" => "TEXT",
					"comment" => "описание, тэг Description",
					"multilang" => true,
				),
				array(
					"name" => "canonical",
					"type" => "VARCHAR(100) NOT NULL DEFAULT ''",
					"comment" => "канонический тег",
					"multilang" => true,
				),
				array(
					"name" => "title_meta",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "заголовок окна в браузере, тег Title",
					"multilang" => true,
				),
				array(
					"name" => "anons",
					"type" => "TEXT",
					"comment" => "анонс",
					"multilang" => true,
				),
				array(
					"name" => "anons_plus",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "добавлять анонс к описанию: 0 - нет, 1 - да",
					"multilang" => true,
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
					"multilang" => true,
				),
				array(
					"name" => "yandex",
					"type" => "TEXT",
					"comment" => "данные для выгрузки в Яндекс.Маркет",
				),
				array(
					"name" => "show_yandex",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "выгружать в Яндекс.Маркет: 0 - нет, 1 - да (если в настройках выбрана выгрузка только выбранных товаров)",
				),
				array(
					"name" => "google",
					"type" => "TEXT",
					"comment" => "данные для выгрузки в Google Merchant",
				),
				array(
					"name" => "show_google",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "выгружать в Google Merchant: 0 - нет, 1 - да (если в настройках выбрана выгрузка только выбранных товаров)",
				),
				array(
					"name" => "no_buy",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "товар временно отсутствует: 0 - нет, 1 - да",
				),
				array(
					"name" => "import",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "элемент только что импортирован: 0 - нет, 1 - да",
				),
				array(
					"name" => "import_id",
					"type" => "VARCHAR(100) NOT NULL DEFAULT ''",
					"comment" => "собственный идентификатор элемента при импорте",
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
				),
				array(
					"name" => "timeedit",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "время последнего изменения в формате UNIXTIME",
				),
				array(
					"name" => "counter_buy",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество покупок",
				),
				array(
					"name" => "hit",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "хит: 0 - нет, 1 - да",
				),
				array(
					"name" => "new",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "новинка: 0 - нет, 1 - да",
				),
				array(
					"name" => "action",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "акция: 0 - нет, 1 - да",
				),
				array(
					"name" => "is_file",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "это товар-файл: 0 - нет, 1 - да",
				),
				array(
					"name" => "access",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "доступ ограничен: 0 - нет, 1 - да",
				),
				array(
					"name" => "admin_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "пользователь из таблицы {users}, добавивший или первый отредктировавший товар в административной части",
				),
				array(
					"name" => "theme",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "шаблон страницы сайта",
				),
				array(
					"name" => "view",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "шаблон модуля",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY site_id (`site_id`)",
				"KEY brand_id (`brand_id`)",
			),
		),
		array(
			"name" => "shop_additional_cost",
			"comment" => "Сопутствующие услуги",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "название",
					"multilang" => true,
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
					"multilang" => true,
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
					"multilang" => true,
				),
				array(
					"name" => "shop_rel",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "связана с товаром: 0 - нет, 1 - да",
				),
				array(
					"name" => "percent",
					"type" => "DOUBLE NOT NULL default '0'",
					"comment" => "процент от стоимости товаров в корзине",
				),
				array(
					"name" => 'price',
					"type" => "DOUBLE NOT NULL default '0'",
					"comment" => "цена",
				),
				array(
					"name" => "amount",
					"type" => "DOUBLE NOT NULL default '0'",
					"comment" => "бесплатно от",
				),
				array(
					"name" => "required",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "всегда включено в цену: 0 - нет, 1 - да",
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "shop_additional_cost_rel",
			"comment" => "Связь сопутствующих услуг и товаров",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор товара из таблицы {shop}",
				),
				array(
					"name" => "additional_cost_id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор сопутствующей услуги из таблицы {shop_additional_cost}",
				),
				array(
					"name" => "summ",
					"type" => "DOUBLE NOT NULL DEFAULT '0'",
					"comment" => "сумма",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY element_id (`element_id`)",
			),
		),
		array(
			"name" => "shop_additional_cost_category_rel",
			"comment" => "Связь сопутствующих услуг и категорий",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {shop_category}",
				),
				array(
					"name" => "element_id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор сопутствующей услуги из таблицы {shop_additional_cost}",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY cat_id (`cat_id`)",
			),
		),
		array(
			"name" => "shop_order_backend",
			"comment" => "Расширения заказа",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "название",
				),
				array(
					"name" => "backend",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "бэкенд",
				),
				array(
					"name" => "params",
					"type" => "TEXT",
					"comment" => "серилизованные настройки",
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
				),
				array(
					"name" => "list",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "выводить в списке: 0 - нет, 1 - да",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "shop_order_backend_element",
			"comment" => "Данные о заказе для бэкенда",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "order_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор заказа из таблицы {shop_order}",
				),
				array(
					"name" => "backend",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор расширения из таблицы {shop_order_backend}",
				),
				array(
					"name" => "type",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "тип - для разделения данных внутри бэкенда",
				),
				array(
					"name" => "value",
					"type" => "TEXT",
					"comment" => "данные",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "shop_brand",
			"comment" => "Бренды",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "TEXT",
					"comment" => "название",
					"multilang" => true,
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
					"multilang" => true,
				),
				array(
					"name" => "map_no_show",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "не показывать на карте сайта: 0 - нет, 1 - да",
				),
				array(
					"name" => "changefreq",
					"type" => "ENUM( 'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never' ) NOT NULL DEFAULT 'always'",
					"comment" => "Changefreq для sitemap.xml",
				),
				array(
					"name" => "priority",
					"type" => "VARCHAR(3) NOT NULL DEFAULT ''",
					"comment" => "Priority для sitemap.xml",
				),
				array(
					"name" => "noindex",
					"type" => "ENUM('0','1') NOT NULL DEFAULT '0'",
					"comment" => "не индексировать: 0 - нет, 1 - да",
				),
				array(
					"name" => "site_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор страницы сайта из таблицы {site}",
				),
				array(
					"name" => "keywords",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "ключевые слова, тег Keywords",
					"multilang" => true,
				),
				array(
					"name" => "descr",
					"type" => "TEXT",
					"comment" => "описание, тэг Description",
					"multilang" => true,
				),
				array(
					"name" => "canonical",
					"type" => "VARCHAR(100) NOT NULL DEFAULT ''",
					"comment" => "канонический тег",
					"multilang" => true,
				),
				array(
					"name" => "title_meta",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "заголовок окна в браузере, тег Title",
					"multilang" => true,
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
					"multilang" => true,
				),
				array(
					"name" => "import",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "производитель только что импортирован: 0 - нет, 1 - да",
				),
				array(
					"name" => "import_id",
					"type" => "VARCHAR(100) NOT NULL DEFAULT ''",
					"comment" => "собственный идентификатор производителя при импорте",
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
				),
				array(
					"name" => "timeedit",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "время последнего изменения в формате UNIXTIME",
				),
				array(
					"name" => "admin_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "пользователь из таблицы {users}, добавивший или первый отредктировавший производителя в административной части",
				),
				array(
					"name" => "theme",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "шаблон страницы сайта",
				),
				array(
					"name" => "view",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "шаблон модуля",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY site_id (`site_id`)",
			),
		),
		array(
			"name" => "shop_brand_category_rel",
			"comment" => "Связи производителей и категорий",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор производителя из таблицы {shop_brand}",
				),
				array(
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {shop_category}",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY cat_id (`cat_id`)",
			),
		),
		array(
			"name" => "shop_cart",
			"comment" => "Товары в корзине",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "user_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users}",
				),
				array(
					"name" => "session_id",
					"type" => "VARCHAR(64) NOT NULL DEFAULT ''",
					"comment" => "номер сессии пользователя из таблицы {sessions}, если не заполнен user_id",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "имя",
				),
				array(
					"name" => "mail",
					"type" => "VARCHAR(64) NOT NULL DEFAULT ''",
					"comment" => "e-mail",
				),
				array(
					"name" => "summ",
					"type" => "DOUBLE NOT NULL DEFAULT '0'",
					"comment" => "сумма заказа",
				),
				array(
					"name" => "count_goods",
					"type" => "DOUBLE NOT NULL DEFAULT '0'",
					"comment" => "количество товарных позиций",
				),
				array(
					"name" => "delivery_id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор службы доставки из таблицы {shop_delivery}",
				),
				array(
					"name" => 'additional_cost',
					"type" => "TEXT",
					"comment" => "идентификаторы сопутствующих услугах, разделенные запятой",
				),
				array(
					"name" => 'orders',
					"type" => "TEXT",
					"comment" => "идентификаторы совершенных заказов, разделенные запятой",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY user_id (`user_id`)",
			),
		),
		array(
			"name" => "shop_cart_goods",
			"comment" => "Товары в корзине",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "cart_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор корзины из таблицы {shop_cart}",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата создания",
				),
				array(
					"name" => "good_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор товара из таблицы {shop}",
				),
				array(
					"name" => "price_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор цены товара - поле price_id из таблицы {shop_price}",
				),
				array(
					"name" => "count",
					"type" => "DOUBLE NOT NULL DEFAULT '0'",
					"comment" => "количество товара",
				),
				array(
					"name" => 'param',
					"type" => "TEXT",
					"comment" => "идентификаторы характеристик товара из таблицы {shop_parm_select}, разделенные запятой",
				),
				array(
					"name" => 'additional_cost',
					"type" => "TEXT",
					"comment" => "идентификаторы сопутствующих услугах, разделенные запятой",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY cart_id (`cart_id`)",
			),
		),
		array(
			"name" => "shop_cart_log_mail",
			"comment" => "Лог отправлений писем",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата отправления",
				),
				array(
					"name" => "cart_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор корзины из таблицы {cart_id}",
				),
				array(
					"name" => "order_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор заказа из таблицы {shop_order}",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY cart_id (`cart_id`)",
			),
		),
		array(
			"name" => "shop_category",
			"comment" => "Категории товаров",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "TEXT",
					"comment" => "название",
					"multilang" => true,
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
					"multilang" => true,
				),
				array(
					"name" => "map_no_show",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "не показывать на карте сайта: 0 - нет, 1 - да",
				),
				array(
					"name" => "changefreq",
					"type" => "ENUM( 'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never' ) NOT NULL DEFAULT 'always'",
					"comment" => "Changefreq для sitemap.xml",
				),
				array(
					"name" => "priority",
					"type" => "VARCHAR(3) NOT NULL DEFAULT ''",
					"comment" => "Priority для sitemap.xml",
				),
				array(
					"name" => "noindex",
					"type" => "ENUM('0','1') NOT NULL DEFAULT '0'",
					"comment" => "не индексировать: 0 - нет, 1 - да",
				),
				array(
					"name" => "parent_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор родителя из таблицы {shop_category}",
				),
				array(
					"name" => "count_children",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество вложенных категорий",
				),
				array(
					"name" => "site_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор страницы сайта из таблицы {site}",
				),
				array(
					"name" => "keywords",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "ключевые слова, тег Keywords",
					"multilang" => true,
				),
				array(
					"name" => "descr",
					"type" => "TEXT",
					"comment" => "описание, тэг Description",
					"multilang" => true,
				),
				array(
					"name" => "canonical",
					"type" => "VARCHAR(100) NOT NULL DEFAULT ''",
					"comment" => "канонический тег",
					"multilang" => true,
				),
				array(
					"name" => "title_meta",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "заголовок окна в браузере, тег Title",
					"multilang" => true,
				),
				array(
					"name" => "anons",
					"type" => "TEXT",
					"comment" => "анонс",
					"multilang" => true,
				),
				array(
					"name" => "anons_plus",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "добавлять анонс к описанию: 0 - нет, 1 - да",
					"multilang" => true,
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
					"multilang" => true,
				),
				array(
					"name" => "show_yandex",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "выгружать в Яндекс.Маркет: 0 - нет, 1 - да (если в настройках выбрана выгрузка только выбранных категорий)",
				),
				array(
					"name" => "show_google",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "выгружать в Google Merchant: 0 - нет, 1 - да (если в настройках выбрана выгрузка только выбранных товаров)",
				),
				array(
					"name" => "import",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "категория только что импортирован: 0 - нет, 1 - да",
				),
				array(
					"name" => "import_id",
					"type" => "VARCHAR(100) NOT NULL DEFAULT ''",
					"comment" => "собственный идентификатор категории при импорте",
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
				),
				array(
					"name" => "timeedit",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "время последнего изменения в формате UNIXTIME",
				),
				array(
					"name" => "access",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "доступ ограничен: 0 - нет, 1 - да",
				),
				array(
					"name" => "admin_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "пользователь из таблицы {users}, добавивший или первый отредктировавший категорию в административной части",
				),
				array(
					"name" => "theme",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "шаблон страницы сайта",
				),
				array(
					"name" => "view",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "шаблон модуля",
				),
				array(
					"name" => "view_rows",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "шаблон модуля для элементов в списке категории",
				),
				array(
					"name" => "view_element",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "шаблон модуля для элементов в категории",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY parent_id (`parent_id`)",
				"KEY site_id (`site_id`)",
			),
		),
		array(
			"name" => "shop_category_rel",
			"comment" => "Связи товаров и категорий",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор товара из таблицы {shop}",
				),
				array(
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {shop_category}",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY cat_id (`cat_id`)",
			),
		),
		array(
			"name" => "shop_category_parents",
			"comment" => "Родительские связи категорий товаров",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {shop_category}",
				),
				array(
					"name" => "parent_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории-родителя из таблицы {shop_category}",
				),
				array(
					"name" => "trash",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "shop_counter",
			"comment" => "Счетчик просмотров товаров",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор товара из таблицы {shop}",
				),
				array(
					"name" => "count_view",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество просмотров",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY element_id (`element_id`)",
			),
		),
		array(
			"name" => "shop_currency",
			"comment" => "Дополнительные валюты магазина",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(20) NOT NULL DEFAULT ''",
					"comment" => "название",
				),
				array(
					"name" => "exchange_rate",
					"type" => "DOUBLE NOT NULL default '0'",
					"comment" => "курс к основной валюте",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "shop_delivery",
			"comment" => "Способы доставки",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "название",
					"multilang" => true,
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
					"multilang" => true,
				),
				array(
					"name" => "service",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "служба доставки",
				),
				array(
					"name" => "params",
					"type" => "TEXT",
					"comment" => "серилизованные настройки службы доставки",
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
					"multilang" => true,
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "shop_delivery_thresholds",
			"comment" => "Стоимость способов доставки",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "delivery_id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор способа доставки из таблицы {shop_delivery}",
				),
				array(
					"name" => 'price',
					"type" => "DOUBLE NOT NULL default '0'",
					"comment" => "стоимость",
				),
				array(
					"name" => "amount",
					"type" => "DOUBLE NOT NULL default '0'",
					"comment" => "сумма, от которой действует стоимость",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "shop_discount",
			"comment" => "Скидки",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "date_start",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата начала действия",
				),
				array(
					"name" => "date_finish",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата окночания действия",
				),
				array(
					"name" => "discount",
					"type" => "DOUBLE NOT NULL DEFAULT '0'",
					"comment" => "процент скидки",
				),
				array(
					"name" => "amount",
					"type" => "DOUBLE NOT NULL DEFAULT '0'",
					"comment" => "действует от цены товара",
				),
				array(
					"name" => "deduction",
					"type" => "DOUBLE NOT NULL DEFAULT '0'",
					"comment" => "фиксированная сумма скидки",
				),
				array(
					"name" => "threshold",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "действует от общей суммы заказа",
				),
				array(
					"name" => "threshold_cumulative",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "действует от общей оплаченных заказов",
				),
				array(
					"name" => "role_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "  тип пользователя из таблицы {users_role}, для которого установлена скидка",
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
				),
				array(
					"name" => "person",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "скидка действует только для определенных пользователей: 0 - нет, 1 - да",
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание скидки для администратора",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "shop_discount_coupon",
			"comment" => "Купоны на скидку",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "discount_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор скидки из таблицы {shop_discount}",
				),
				array(
					"name" => "coupon",
					"type" => "VARCHAR( 10 ) NOT NULL DEFAULT ''",
					"comment" => "код купона",
				),
				array(
					"name" => "count_use",
					"type" => "TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "cколько раз можно использовать купон",
				),
				array(
					"name" => "used",
					"type" => "TINYINT(3) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "сколько раз купон использован при оформлении заказа",
				),
				array(
					"name" => "trash",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY discount_id (`discount_id`)",
			),
		),
		array(
			"name" => "shop_discount_object",
			"comment" => "Товары и категории, на которые действуют скидки",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "discount_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор скидки из таблицы {shop_discount}",
				),
				array(
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {shop_category}",
				),
				array(
					"name" => "good_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор товара из таблицы {shop}",
				),
				array(
					"name" => "trash",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY discount_id (`discount_id`)",
			),
		),
		array(
			"name" => "shop_discount_person",
			"comment" => "Пользователи, для которых действуют скидки",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "discount_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор скидки из таблицы {shop_discount}",
				),
				array(
					"name" => "user_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users}",
				),
				array(
					"name" => "session_id",
					"type" => "VARCHAR(64) NOT NULL DEFAULT ''",
					"comment" => "номер сессии пользователя из таблицы {sessions}",
				),
				array(
					"name" => "coupon_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор купона из таблицы {shop_discount_coupon}",
				),
				array(
					"name" => "used",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "скидка уже использована: 0 - нет, 1 - да",
				),
				array(
					"name" => "trash",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY discount_id (`discount_id`)",
			),
		),
		array(
			"name" => "shop_files_codes",
			"comment" => "Коды для скачивания товаров-нематериальных активов",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "shop_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор товара из таблицы {shop}",
				),
				array(
					"name" => "code",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "код для скачивания товара-файла",
				),
				array(
					"name" => "date_finish",
					"comment" => "дата и время окончания действия кода",
					"type" => "DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "shop_import",
			"comment" => "Описание полей файлов импорта",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "название",
				),
				array(
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор описания файла из таблицы {shop_import}",
				),
				array(
					"name" => "type",
					"type" => "VARCHAR(20) NOT NULL DEFAULT ''",
					"comment" => "тип",
				),
				array(
					"name" => "params",
					"type" => "TEXT",
					"comment" => "серилизованные данные о поле",
				),
				array(
					"name" => "required",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "серилизованные данные о поле",
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "выдавать ошибку, если поле не заполнено: 0 - нет, 1 - да",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "shop_import_category",
			"comment" => "Описание файлов импорта",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "название",
				),
				array(
					"name" => "format",
					"type" => "ENUM('csv', 'xls') NOT NULL DEFAULT 'csv'",
					"comment" => "формат файла",
				),
				array(
					"name" => "type",
					"type" => "ENUM('good', 'category', 'brand') NOT NULL DEFAULT 'good'",
					"comment" => "тип данных: good - товары, category - категории товаров",
				),
				array(
					"name" => "delete_items",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "удалять не описанные в файле данные: 0 - нет, 1 - да",
				),
				array(
					"name" => "site_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор страницы сайта из таблицы {site}",
				),
				array(
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {shop_category}",
				),
				array(
					"name" => "count_part",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество строк, выгружаемых за один проход скрипта",
				),
				array(
					"name" => "delimiter",
					"type" => "VARCHAR(20) NOT NULL DEFAULT ''",
					"comment" => "разделитель данных в строке",
				),
				array(
					"name" => "end_string",
					"type" => "VARCHAR(20) NOT NULL DEFAULT ''",
					"comment" => "обозначать конец строки символом",
				),
				array(
					"name" => "encoding",
					"type" => "VARCHAR(20) NOT NULL DEFAULT ''",
					"comment" => "кодировка",
				),
				array(
					"name" => "sub_delimiter",
					"type" => "VARCHAR(20) NOT NULL DEFAULT ''",
					"comment" => "разделитель данных внутри поля",
				),
				array(
					"name" => "header",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "первая строка - названия столбцов: 0 - нет, 1 - да",
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
				),
				array(
					"name" => "trash",
					"type" => "ENUM( '0', '1' ) NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "shop_rel",
			"comment" => "Связи похожих товаров",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор товара из таблицы {shop}",
				),
				array(
					"name" => "rel_element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор похожего товара из таблицы {shop}",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "shop_order",
			"comment" => "Заказы",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "user_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users}",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата создания",
				),
				array(
					"name" => "status",
					"type" => "ENUM('0', '1', '2', '3', '4') NOT NULL DEFAULT '0'",
					"comment" => "действие статуса заказа",
				),
				array(
					"name" => "status_id",
					"type" => "TINYINT(3) NOT NULL DEFAULT '0'",
					"comment" => "идентификатор статуса заказа из таблицы {shop_order_status}",
				),
				array(
					"name" => "lang_id",
					"type" => "TINYINT(2) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор языковой версии сайта, с которой был сделан заказ, из таблицы {languages}",
				),
				array(
					"name" => "summ",
					"type" => "DOUBLE NOT NULL DEFAULT '0'",
					"comment" => "общая сумма заказа",
				),
				array(
					"name" => "delivery_id",
					"type" => "VARCHAR( 10 ) NOT NULL DEFAULT '0'",
					"comment" => "способ доставки из таблицы {shop_delivery}",
				),
				array(
					"name" => "delivery_summ",
					"type" => "DOUBLE NOT NULL DEFAULT '0'",
					"comment" => "стоимость доставки",
				),
				array(
					"name" => "delivery_info",
					"type" => "TEXT",
					"comment" => "данные службы доставки",
				),
				array(
					"name" => "discount_id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор скидки из таблицы {shop_discount}",
				),
				array(
					"name" => "discount_summ",
					"type" => "DOUBLE NOT NULL DEFAULT '0'",
					"comment" => "сумма скидки",
				),
				array(
					"name" => "coupon",
					"type" => "VARCHAR(255) NOT NULL DEFAULT ''",
					"comment" => "код купона",
				),
				array(
					"name" => "count_minus",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "товары списаны: 0 - нет, 1 - да",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "shop_order_additional_cost",
			"comment" => "Сопутствующие услуги, включенные в заказ",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "order_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор заказа из таблицы {shop_order}",
				),
				array(
					"name" => "order_goods_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор записи о купленном товаре из таблицы {shop_order_goods}",
				),
				array(
					"name" => "additional_cost_id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор сопутствующей услуги из таблицы {shop_additional_cost}",
				),
				array(
					"name" => "summ",
					"type" => "DOUBLE NOT NULL DEFAULT '0'",
					"comment" => "сумма",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "shop_order_goods",
			"comment" => "Товары в заказе",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "order_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор заказа из таблицы {shop_order}",
				),
				array(
					"name" => "good_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор товара из таблицы {shop}",
				),
				array(
					"name" => "discount_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор скидки из таблицы {shop_discount}",
				),
				array(
					"name" => "count_goods",
					"type" => "DOUBLE NOT NULL DEFAULT '0'",
					"comment" => "количество товаров",
				),
				array(
					"name" => 'price',
					"type" => "DOUBLE NOT NULL DEFAULT '0'",
					"comment" => "цена",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "shop_order_goods_param",
			"comment" => "Дополнительных характеристики товаров в заказе",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "value",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "значение характеристики",
				),
				array(
					"name" => "param_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор характеристики из таблицы {shop_param}",
				),
				array(
					"name" => "order_goods_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор записи о купленном товаре из таблицы {shop_order_goods}",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY order_goods_id (`order_goods_id`)",
			),
		),
		array(
			"name" => "shop_order_param",
			"comment" => "Поля конструктора формы оформления заказа",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "название",
					"multilang" => true,
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
					"multilang" => true,
				),
				array(
					"name" => "type",
					"type" => "VARCHAR(20) NOT NULL DEFAULT ''",
					"comment" => "тип",
				),
				array(
					"name" => "info",
					"type" => "VARCHAR(30) NOT NULL DEFAULT ''",
					"comment" => "смысловая нагрузка",
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
				),
				array(
					"name" => "required",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "обязательно для заполнения: 0 - нет, 1 - да",
				),
				array(
					"name" => "show_in_form",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "использовать в стандатной форме оформления заказа: 0 - нет, 1 - да",
				),
				array(
					"name" => "show_in_form_register",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "позволять редактировать из личного кабинета: 0 - нет, 1 - да",
				),
				array(
					"name" => "show_in_form_one_click",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "использовать в форме быстрого заказа: 0 - нет, 1 - да",
				),
				array(
					"name" => "config",
					"type" => "TEXT",
					"comment" => "дополнительные настройки поля",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "shop_order_param_element",
			"comment" => "Значения полей конструктора оформления заказа",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "value",
					"type" => "TEXT",
					"comment" => "значение",
				),
				array(
					"name" => "param_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор поля из таблицы {shop_order_param}",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор заказа из таблицы {shop_order}",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "shop_order_param_select",
			"comment" => "Варианты значений полей конструктора оформления заказа типа список",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "param_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор поля из таблицы {shop_order_param}",
				),
				array(
					"name" => "value",
					"type" => "TINYINT(2) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "значение для типа характеристики «галочка»: 0 - нет, 1 - да",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "значение",
					"multilang" => true,
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY param_id (`param_id`)",
			),
		),
		array(
			"name" => "shop_order_param_user",
			"comment" => "Значения полей конструктора оформления заказа, предзаполненные пользователями",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "value",
					"type" => "TEXT",
					"comment" => "значение",
				),
				array(
					"name" => "param_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор поля из таблицы {shop_order_param}",
				),
				array(
					"name" => "user_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users}",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "shop_order_status",
			"comment" => "Статусы заказов",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "название",
					"multilang" => true,
				),
				array(
					"name" => "color",
					"type" => "VARCHAR(20) NOT NULL DEFAULT ''",
					"comment" => "цвет",
				),
				array(
					"name" => "status",
					"type" => "ENUM('0', '1', '2', '3', '4') NOT NULL DEFAULT '0'",
					"comment" => "действие статуса заказа",
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
				),
				array(
					"name" => "count_minus",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "списывать товары: 0 - нет, 1 - да",
				),
				array(
					"name" => "send_mail",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "отправлять уведомление пользователю о смене статуса: 0 - нет, 1 - да",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "shop_param",
			"comment" => "Дополнительные характеристики товаров",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "название",
					"multilang" => true,
				),
				array(
					"name" => "type",
					"type" => "VARCHAR(20) NOT NULL DEFAULT ''",
					"comment" => "тип",
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
				),
				array(
					"name" => "search",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "характеристика выводится в форме поиска: 0 - нет, 1 - да",
				),
				array(
					"name" => "list",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "характеристика выводится в списке товаров: 0 - нет, 1 - да",
				),
				array(
					"name" => "block",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "характеристика выводится в блоке товаров: 0 - нет, 1 - да",
				),
				array(
					"name" => "id_page",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "характеристика выводится на странице товара: 0 - нет, 1 - да",
				),
				array(
					"name" => "required",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "доступен к выбору при заказе: 0 - нет, 1 - да",
				),
				array(
					"name" => "page",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "отдельная страница для значений: 0 - нет, 1 - да",
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
					"multilang" => true,
				),
				array(
					"name" => "config",
					"type" => "TEXT",
					"comment" => "дополнительные настройки поля",
				),
				array(
					"name" => "display_in_sort",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "выводится в блоке для сортировки: 0 - нет, 1 - да",
				),
				array(
					"name" => "yandex_use",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "выгружается в файл YML: 0 - нет, 1 - да",
				),
				array(
					"name" => "yandex_name",
					"type" => "VARCHAR(100) NOT NULL DEFAULT ''",
					"comment" => "название в файле YML",
				),
				array(
					"name" => "yandex_unit",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "единица измерения в файле YML",
				),
				array(
					"name" => "measure_unit",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "единица измерения",
					"multilang" => true,
				),
				array(
					"name" => "site_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор страницы сайта из таблицы {site}",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "shop_param_category_rel",
			"comment" => "Связи дополнительных характеристик товаров и категорий",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор характеристики из таблицы {shop_param}",
				),
				array(
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {shop_category}",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY cat_id (`cat_id`)",
			),
		),
		array(
			"name" => "shop_param_element",
			"comment" => "Значения дополнительных характеристик товаров",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "value",
					"type" => "TEXT",
					"comment" => "значение",
					"multilang" => true,
				),
				array(
					"name" => "param_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор характеристики из таблицы {shop_param}",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор товара из таблицы {shop}",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY element_id (`element_id`)",
				"KEY param_id (`param_id`)",
			),
		),
		array(
			"name" => "shop_param_select",
			"comment" => "Варианты значений дополнительных характеристик товаров типа список",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "param_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор характеристики из таблицы {shop_param}",
				),
				array(
					"name" => "value",
					"type" => "TINYINT(2) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "значение для типа характеристики «галочка»: 0 - нет, 1 - да",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "значение",
					"multilang" => true,
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY param_id (`param_id`)",
			),
		),
		array(
			"name" => "shop_price",
			"comment" => "Цены товаров",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "good_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор товара из таблицы {shop}",
				),
				array(
					"name" => 'price',
					"type" => "DOUBLE UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "цена",
				),
				array(
					"name" => "old_price",
					"type" => "DOUBLE UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "исходная цена",
				),
				array(
					"name" => "count_goods",
					"type" => "DOUBLE NOT NULL DEFAULT '0'",
					"comment" => "количество товара",
				),
				array(
					"name" => "price_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор исходной цены из таблицы {shop_price}",
				),
				array(
					"name" => "date_start",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата начала действия",
				),
				array(
					"name" => "date_finish",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата окончания действия",
				),
				array(
					"name" => "discount",
					"type" => "DOUBLE NOT NULL DEFAULT '0'",
					"comment" => "скидка",
				),
				array(
					"name" => "discount_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор скидки из таблицы {shop_discount}",
				),
				array(
					"name" => "person",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "цена действует только для определенных пользователей: 0 - нет, 1 - да",
				),
				array(
					"name" => "role_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор типа пользователя из таблицы {users_role}, для которого действует скидка",
				),
				array(
					"name" => "currency_id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор валюты из таблицы {shop_currency}",
				),
				array(
					"name" => "import_id",
					"type" => "VARCHAR(100) NOT NULL DEFAULT ''",
					"comment" => "собственный идентификатор при импорте",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY good_id (`good_id`)",
				"KEY price_id (`price_id`)",
			),
		),
		array(
			"name" => "shop_price_image_rel",
			"comment" => "Изображения товаров, прикрепленные к цене",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "price_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор исходной цены из таблицы {shop_price}",
				),
				array(
					"name" => "image_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор изображения из таблицы {images}",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY price_id (`price_id`)",
			),
		),
		array(
			"name" => "shop_price_param",
			"comment" => "Дополнительные характеристики, учитываемые в цене",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "price_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор исходной цены из таблицы {shop_price}",
				),
				array(
					"name" => "param_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор характеристики из таблицы {shop_param}",
				),
				array(
					"name" => "param_value",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "значение характеристики - идентификатор из таблицы {shop_param_select}",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY price_id (`price_id`)",
				"KEY param_id (`param_id`)",
				"KEY param_value (`param_value`)",
			),
		),
		array(
			"name" => "shop_waitlist",
			"comment" => "Товары в списке ожидания",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "good_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор товара из таблицы {shop}",
				),
				array(
					"name" => "user_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users}",
				),
				array(
					"name" => "lang_id",
					"type" => "TINYINT(2) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор языка сайта из таблицы {languages}",
				),
				array(
					"name" => "mail",
					"type" => "VARCHAR(64) NOT NULL DEFAULT ''",
					"comment" => "e-mail пользователя",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата создания",
				),
				array(
					"name" => 'param',
					"type" => "TEXT",
					"comment" => "серилизованные данные о характеристиках товара (доступных к выбору при заказе)",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
			),
		),
		array(
			"name" => "shop_wishlist",
			"comment" => "Товары в списке пожеланий",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "good_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор товара из таблицы {shop}",
				),
				array(
					"name" => "user_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users}",
				),
				array(
					"name" => "session_id",
					"type" => "VARCHAR(64) NOT NULL DEFAULT ''",
					"comment" => "номер сессии пользователя из таблицы {sessions}",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата создания",
				),
				array(
					"name" => "count",
					"type" => "DOUBLE UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество товара",
				),
				array(
					"name" => 'param',
					"type" => "TEXT",
					"comment" => "серилизованные данные о характеристиках товара (доступных к выбору при заказе)",
				),
				array(
					"name" => 'additional_cost',
					"type" => "TEXT",
					"comment" => "идентификаторы сопутствующих услугах, разделенные запятой",
				),
				array(
					"name" => "is_file",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "товар-файл: 0 - нет, 1 - да",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY user_id (`user_id`)",
			),
		),
	);

	/**
	 * @var array записи в таблице {modules}
	 */
	public $modules = array(
		array(
			"name" => "shop",
			"admin" => true,
			"site" => true,
			"site_page" => true,
		),
		array(
			"name" => "cart",
			"site" => true,
			"site_page" => true,
			"admin" => true,
			"title" => "Корзина",
		),
		array(
			"name" => "wishlist",
			"site" => true,
			"site_page" => true,
			"title" => "Отложенные",
		),
		array(
			"name" => "delivery",
			"site" => true,
			"admin" => true,
			"title" => "Служба доставки",
		),
		array(
			"name" => "order",
			"site" => true,
			"admin" => true,
			"title" => "Заказы",
		),
	);

    /**
     * @var array страницы сайта
     */
    public $site = array(
        array(
			"sort" => 4,
            "name" => array('Интернет-магазин', 'Catalog'),
            "act" => true,
            "module_name" => "shop",
            "rewrite" => "shop",
            "children" => array(
                array(
                    "name" => array('Корзина', 'Cart'),
                    "act" => true,
                    "module_name" => "cart",
                    "rewrite" => "shop/cart",
					"text" => array('<insert name="show_add_coupon" module="shop">', '<insert name="show_add_coupon" module="shop">'),
                    "map_no_show" => true,
                    "noindex" => true,
                    "search_no_show" => true,
                ),
                array(
                    "name" => array('Заказ оформлен', 'Order complete'),
                    "act" => true,
                    "rewrite" => "shop/cart/done",
                    "text" => array('<p>Спасибо за Ваш заказ! В ближайшее время мы свяжемся с Вами!</p><insert name="show_last_order" module="cart">', '<p>Thank you for your order! In the near future we will contact you!</p><insert name="show_last_order" module="cart">'),
                    "map_no_show" => true,
                    "noindex" => true,
                    "search_no_show" => true,
                ),
                array(
                    "name" => array('Отложенные', 'Wishlist'),
                    "act" => true,
                    "module_name" => "wishlist",
                    "rewrite" => "shop/wishlist",
                    "map_no_show" => true,
                    "noindex" => true,
                    "search_no_show" => true,
                ),
            ),
        ),
    );

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Каталог",
			"rewrite" => "shop",
			"group_id" => 4,
			"sort" => 11,
			"act" => true,
			"add" => true,
			"add_name" => "Товар",
			"docs" => "http://www.diafan.ru/moduli/magazin/",
			"children" => array(
				array(
					"name" => "Товары",
					"rewrite" => "shop",
					"act" => true,
				),
				array(
					"name" => "Скидки",
					"rewrite" => "shop/discount",
					"act" => true,
				),
				array(
					"name" => "Производители",
					"rewrite" => "shop/brand",
					"act" => true,
				),
				array(
					"name" => "Категории",
					"rewrite" => "shop/category",
					"act" => true,
				),
				array(
					"name" => "Характеристики",
					"rewrite" => "shop/param",
					"act" => true,
				),
				array(
					"name" => "Импорт/экспорт",
					"rewrite" => "shop/importexport",
					"act" => true,
				),
				array(
					"name" => "Настройки",
					"rewrite" => "shop/config",
				),
			)
		),
		array(
			"name" => "Заказы",
			"rewrite" => "order",
			"group_id" => 4,
			"sort" => 12,
			"act" => true,
			"docs" => "http://www.diafan.ru/moduli/magazin/",
			"children" => array(
				array(
					"name" => "Заказы",
					"rewrite" => "order",
					"act" => true,
				),
				array(
					"name" => "Расширения для заказа",
					"rewrite" => "order/backend",
					"act" => true,
				),
			)
		),
		array(
			"name" => "Справочники",
			"rewrite" => "delivery",
			"group_id" => 4,
			"sort" => 13,
			"act" => true,
			"docs" => "http://www.diafan.ru/moduli/magazin/",
			"children" => array(
				array(
					"name" => "Способы доставки",
					"rewrite" => "delivery",
					"act" => true,
				),
				array(
					"name" => "Сопутствующие услуги",
					"rewrite" => "shop/additionalcost",
					"act" => true,
				),
				array(
					"name" => "Форма оформления заказа",
					"rewrite" => "shop/orderparam",
					"act" => true,
				),
				array(
					"name" => "Статусы заказа",
					"rewrite" => "shop/orderstatus",
					"act" => true,
				),
				array(
					"name" => "Валюты",
					"rewrite" => "shop/currency",
					"act" => true,
				),
			)
		),
		array(
			"name" => "Статистика",
			"rewrite" => "shop/ordercount",
			"group_id" => 4,
			"sort" => 15,
			"act" => true,
			"docs" => "http://www.diafan.ru/moduli/magazin/",
			"children" => array(
				array(
					"name" => "Отчеты о заказах",
					"rewrite" => "shop/ordercount",
					"act" => true,
				),
				array(
					"name" => "Отложенные товары",
					"rewrite" => "shop/wishlist",
					"act" => true,
				),
				array(
					"name" => "Список ожиданий",
					"rewrite" => "shop/waitlist",
					"act" => true,
				),
				array(
					"name" => "Брошенные корзины",
					"rewrite" => "cart",
					"act" => true,
				),
				array(
					"name" => "Статистика товаров",
					"rewrite" => "shop/counter",
					"act" => true,
				),
			)
		),
	);

	/**
	 * @var array настройки
	 */
	public $config = array(
		array(
			"name" => "cat",
			"value" => "1",
		),
		array(
			"name" => "images_element",
			"value" => "1",
		),
		array(
			"name" => "images_brand",
			"value" => "1",
		),
		array(
			"name" => "use_animation",
			"value" => "1",
		),
		array(
			"name" => "list_img_element",
			"value" => "1",
		),
		array(
			"name" => "count_list",
			"value" => 4,
		),
		array(
			"name" => "count_child_list",
			"value" => 4,
		),
		array(
			"name" => "counter",
			"value" => 1,
		),
		array(
			"name" => "nastr",
			"value" => "12",
		),
		array(
			"name" => "nastr_cat",
			"value" => "10",
		),
		array(
			"name" => "search_price",
			"value" => "1",
		),
		array(
			"name" => "search_article",
			"value" => "1",
		),
		array(
			"name" => "format_price_3",
			"value" => " ",
		),
		array(
			"name" => "tax_name",
			"value" => array("НДС", "VAT"),
		),
		array(
			"name" => "tax",
			"value" => 18,
		),
		array(
			"name" => "currency",
			"value" => array("руб.", "rub."),
		),
		array(
			"name" => "yandex",
			"value" => "1",
		),
		array(
			"name" => "currencyyandex",
			"value" => "RUR",
		),
		array(
			"name" => "currencyratesel",
			"value" => "1",
		),
		array(
			"name" => "bid",
			"value" => "15",
		),
		array(
			"name" => "order_redirect",
			"value" => "shop/cart/done",
		),
		array(
			"name" => "mes",
			"value" => array(
				"Оплата заказа №%id на сумму %summ руб.",
				"Payment order #%id in the amount of %summ RUB.",
			),
		),
		array(
			"name" => "subject",
			"value" => array(
				"Вы оформили заказ %id на сайте %title (%url)",
				"You placed an order %id on web site %title (%url)",
			),
		),
		array(
			"name" => "message",
			"value" => array(
				"<p>Здравствуйте!</p><p>Вы оформили заказ #%id на сайте %title (%url)</p>%order<p>Способ оплаты: %payment</p><p>%message</p><p>Спасибо за Ваш заказ! В ближайшее время мы с Вами свяжемся для подтверждения заказа</p>",
				"<p>Dear %fio!</p><p>You maked an order #%id on web site %title (%url)</p>%order<p>Payment system: %payment</p><p>%message</p><p>Thank you for your order! We will connect with you in near time to confirm order.</p>",
			),
		),
		array(
			"name" => "subject_admin",
			"value" => "%title (%url). Новый заказ %id",
		),
		array(
			"name" => "message_admin",
			"value" => "Здравствуйте, администратор сайта %title (%url)!<br>На сайте появился новый заказ номер %id: %order<br>Способ оплаты: %payment<br><br>%message",
		),
		array(
			"name" => "subject_change_status",
			"value" => array(
				"Статус заказа изменен",
				"Order status changed",
			),
		),
		array(
			"name" => "message_change_status",
			"value" => array(
				"Здравствуйте!<br>Статус заказ №%order изменен на «%status».",
				'Hello!<br>Status order #%order is changed to "%status".',
			),
		),
		array(
			"name" => "desc_payment",
			"value" => array(
				"Oplata zakaza #%id",
				"Payment order #%id",
			),
		),
		array(
			"name" => "payment_success_text",
			"value" => array(
				"<p>Спасибо, платеж успешно принят. В ближайшее время мы с Вами свяжемся для уточнения деталей заказа.</p>",
				"<p>Thank you for the payment was successful. In the near future we will contact you to clarify details of your order.</p>",
			),
		),
		array(
			"name" => "payment_fail_text",
			"value" => array(
				"<p>Извините, платеж не прошел.</p>",
				"<p>Sorry, the payment failed.</p>",
			),
		),
		array(
			"name" => "subject_file_sale_message",
			"value" => array(
				"Вы оформили заказ %id на сайте %title (%url)",
				"You placed an order %id on web site %title (%url)",
			),
		),
		array(
			"name" => "file_sale_message",
			"value" => array(
				"Здравствуйте!<br>Вы оформили заказ на сайте %title (%url):<br><br>Номер заказа: %id<br>Файлы можно скачать по ссылкам в течении часа: %files<br><br>Спасибо за Ваш заказ!",
				"Hello!<br>You place your order online %title (%url):<br><br>Order number: %id. Files can be downloaded from the links at the hour: %files.<br><br>Thank you for your order!",
			),
		),
		array(
			"name" => "subject_abandonmented_cart",
			"value" => array(
				"Вы забыли товары в корзине на сайте %title (%url)",
				"You forgot the goods in the shopping cart on web site %title (%url)",
			),
		),
		array(
			"name" => "message_abandonmented_cart",
			"value" => array(
				"Здравствуйте!<br>Вы наполнили корзину на сайте %title (%url):<br><br>Мы сохраним Вашу корзину и Вы в любой момент можете продолжить оформление заказа пройдя по ссылке: <a href=\"%link\">%link</a>. Но мы не обещаем, что все товары будут в наличии. Пожалуйста, поторопитесь оформить заказ пока товары не раскупили.<br>Вот Ваш заказ:<br>%goods",
				"Hello! <br> You filled the cart on the site %title (%url):<br><br>We save your cart and you can continue to process the order at any time by following the link:  <a href=\"%link\">%link</a>. But we do not promise that all goods will be available. Please hurry to place an order until the goods are sold.<br>Here is your order:<br>%goods",
			),
		),
		array(
			"name" => "attachment_extensions",
			"value" => "zip, rar",
		),
		array(
			"name" => "rel_two_sided",
			"value" => 1,
		),
		array(
			"name" => "subject_waitlist",
			"value" => array(
				"Товар поступил на склад",
				"Product will be available",
			),
		),
		array(
			"name" => "message_waitlist",
			"value" => array(
				"Здравствуйте!<br>Товар <a href=\"%link\">%good</a> поступил на склад.",
				"Hello! <br>Product <a href=\"%link\">%good</a> entered the warehouse.",
			),
		),
		array(
			"name" => "images_variations_element",
			"value" => 'a:3:{i:0;a:2:{s:4:"name";s:6:"medium";s:2:"id";i:1;}i:1;a:2:{s:4:"name";s:5:"large";s:2:"id";i:3;}i:2;a:2:{s:4:"name";s:7:"preview";s:2:"id";i:4;}}',
		),
		array(
			"name" => "images_variations_brand",
			"value" => 'a:3:{i:0;a:2:{s:4:"name";s:6:"medium";s:2:"id";i:1;}i:1;a:2:{s:4:"name";s:5:"large";s:2:"id";i:3;}i:2;a:2:{s:4:"name";s:7:"preview";s:2:"id";i:4;}}',
		),
		array(
			"name" => "one_click",
			"value" => '1',
		),
		array(
			"name" => "show_more",
			"value" => '1',
		),
		array(
			"name" => "format_price_1",
			"value" => "2",
		),
		array(
			"name" => "format_price_2",
			"value" => ",",
		),
		array(
			"name" => "format_price_3",
			"value" => "",
		),
	);

	/**
	 * @var array SQL-запросы
	 */
	public $sql = array(
		"shop_category" => array(
			array(
				"id" => 1,
				"name" => array('Наши товары', 'Our goods'),
				"menu" => 2,
			),
		),
		"shop_brand" => array(
			array(
				"id" => 1,
				"name" => array('Фирма А', 'Firm A'),
			),
		),
		"shop_param" => array(
			array(
				"id" => 1,
				"name" => array('Размер', 'Size'),
				"type" => "text",
				"id_page" => 1,
			),
		),
		"shop_discount" => array(
			array(
				"id" => 1,
				"discount" => 5,
			),
		),
		"shop_import_category" => array(
			array(
				"id" => 1,
				"name" => 'Импорт товаров',
				"format" => 'csv',
				"type" => 'good',
				"count_part" => 20,
				"delimiter" => ';',
				"encoding" => 'cp1251',
				"sub_delimiter" => '|',
			),
			array(
				"id" => 2,
				"name" => 'Импорт категорий',
				"format" => 'csv',
				"type" => 'category',
				"count_part" => 20,
				"delimiter" => ';',
				"encoding" => 'cp1251',
				"sub_delimiter" => '|',
			),
			array(
				"id" => 3,
				"name" => 'Импорт производителей',
				"format" => 'csv',
				"type" => 'brand',
				"count_part" => 20,
				"delimiter" => ';',
				"encoding" => 'cp1251',
				"sub_delimiter" => '|',
			),
		),
		"shop_import" => array(
			array(
				"name" => 'Идентификатор',
				"type" => 'id',
				"params" => 'a:1:{s:4:"type";s:4:"site";}',
				"cat_id" => 1,
			),
			array(
				"name" => 'Артикул товара',
				"type" => 'article',
				"cat_id" => 1,
			),
			array(
				"name" => 'Название товара',
				"type" => 'name',
				"cat_id" => 1,
			),
			array(
				"name" => 'Краткое описание',
				"type" => 'anons',
				"cat_id" => 1,
			),
			array(
				"name" => 'Полное описание товара',
				"type" => 'text',
				"cat_id" => 1,
			),
			array(
				"name" => 'Цена',
				"type" => 'price',
				"params" => 'a:5:{s:9:"delimitor";s:1:"&";s:11:"select_type";s:3:"key";s:5:"count";i:0;s:8:"currency";i:0;s:15:"select_currency";s:3:"key";}',
				"cat_id" => 1,
			),
			array(
				"name" => 'Количество',
				"type" => 'count',
				"params" => 'a:2:{s:9:"delimitor";s:1:"&";s:11:"select_type";s:3:"key";}',
				"cat_id" => 1,
			),
			array(
				"name" => 'Хит (1/0)',
				"type" => 'hit',
				"cat_id" => 1,
			),
			array(
				"name" => 'Новинка (1/0)',
				"type" => 'new',
				"cat_id" => 1,
			),
			array(
				"name" => 'Акция (1/0)',
				"type" => 'action',
				"cat_id" => 1,
			),
			array(
				"name" => 'Идентификатор',
				"type" => 'id',
				"params" => 'a:1:{s:4:"type";s:4:"site";}',
				"cat_id" => 2,
			),
			array(
				"name" => 'Название категории',
				"type" => 'name',
				"cat_id" => 2,
			),
			array(
				"name" => 'Краткое описание категории',
				"type" => 'anons',
				"cat_id" => 2,
			),
			array(
				"name" => 'Полное описание категории',
				"type" => 'text',
				"cat_id" => 2,
			),
			array(
				"name" => 'Идентификатор',
				"type" => 'id',
				"params" => 'a:1:{s:4:"type";s:4:"site";}',
				"cat_id" => 3,
			),
			array(
				"name" => 'Название производителя',
				"type" => 'name',
				"cat_id" => 3,
			),
			array(
				"name" => 'Описание категории',
				"type" => 'text',
				"cat_id" => 3,
			),
		),
		"shop_order_status" => array(
			array(
				"id" => 1,
				"name" => array('Новый', 'New'),
				"status" => 0,
				"color" => '#D54640',
			),
			array(
				"id" => 2,
				"name" => array('В обработке', 'In processing'),
				"status" => 1,
				'color' => '#F49D10',
				"count_minus" => true,
				"send_mail" => true,
			),
			array(
				"id" => 3,
				"name" => array('Отменен', 'Canceled'),
				"status" => 2,
				'color' => '#A6AEB0',
				"send_mail" => true,
			),
			array(
				"id" => 4,
				"name" => array('Выполнен', 'Completed'),
				"status" => 3,
				'color' => '#8AC73C',
				"count_minus" => true,
				"send_mail" => true,
			),
		),
		"shop_currency" => array(
			array(
				"name" => 'Euro',
				"exchange_rate" => 50,
			),
		),
		"shop_order_backend" => array(
			array(
				"name" => "Товарный чек",
				"backend" => "packing_list",
				"act" => 1,
				"sort" => 1,
			),
		),
		"shop_order_param" => array(
			array(
				"id" => 1,
				"name" => array('ФИО или название компании', 'Name&amp;Surname'),
				"type" => "text",
				"info" => "name",
				"required" => 1,
				"show_in_form" => 1,
				"show_in_form_one_click" => 1,
			),
			array(
				"id" => 2,
				"name" => array('E-mail', 'E-mail'),
				"type" => "email",
				"info" => "email",
				"required" => 1,
				"show_in_form" => 1,
			),
			array(
				"id" => 3,
				"name" => array('Контактные телефоны (с кодом города)', 'Telephones'),
				"type" => "phone",
				"info" => "phone",
				"required" => 1,
				"show_in_form" => 1,
				"show_in_form_one_click" => 1,
			),
			array(
				"id" => 4,
				"name" => array('Индекс', 'Zip'),
				"type" => "text",
				"info" => "zip",
				"show_in_form" => 1,
			),
			array(
				"id" => 5,
				"name" => array('Город', 'City'),
				"type" => "text",
				"info" => "city",
				"show_in_form" => 1,
			),
			array(
				"id" => 6,
				"name" => array('Улица, проспект и пр.', 'Street'),
				"type" => "text",
				"info" => "street",
				"show_in_form" => 1,
			),
			array(
				"id" => 7,
				"name" => array('Номер дома', 'House number'),
				"type" => "text",
				"info" => "building",
				"show_in_form" => 1,
			),
			array(
				"id" => 8,
				"name" => array('Корпус', 'Block'),
				"type" => "text",
				"info" => "suite",
				"show_in_form" => 1,
			),
			array(
				"id" => 9,
				"name" => array('Квартира, офис', 'Flat, office'),
				"type" => "text",
				"info" => "flat",
				"show_in_form" => 1,
			),
			array(
				"id" => 10,
				"name" => array('Комментарии', 'Comments'),
				"type" => "textarea",
				"info" => "comment",
				"show_in_form" => 1,
			),
		),
		"shop_delivery" => array(
			array(
				"id" => 1,
				"name" => array('Курьер', 'Сourier'),
				"text" => array('Товар доставляется курьером до двери Вашего дома.', 'Goods are delivered to the door of your house.'),
				"status" => 0,
				"thresholds" => array(
					array(
						'price' => 500,
					),
					array(
						"amount" => 6000,
					),
				)
			),
			array(
				"id" => 2,
				"name" => array('Почта России'),
				"text" => array('Доставка по всей России небольших посылок'),
				"status" => 0,
				"thresholds" => array(
					array(
						'price' => 650,
					),
				)
			),
			array(
				"id" => 3,
				"name" => array('EMS-доставка', 'Сourier'),
				"text" => array('Экспресс-доставка до дверей курьером в любую точку России'),
				"status" => 0,
				"thresholds" => array(
					array(
						'price' => 1200,
					),
				)
			),
		),
	);

	/**
	 * @var array демо-данные
	 */
	public $demo = array(
		'site' => array(
			array(
				'id' => 'shop',
				'text' => array(
					'<p>Чтобы положить товар в корзину, перейдите в интернет-магазин.</p>',
					'<p>To put the goods in a basket, pass in the catalogue of the goods.</p>'
				),
			),
		),
		'shop_category' => array(
			array(
				'id' => 2,
				'name' => array('Рюкзаки', 'Backpacks'),
				'rewrite' => 'shop/ryukzaki',
				'menu' => 2,
				'sort' => 1,
				'import_id' => '1',
			),
			array(
				'id' => 1,
				'name' => array('Палатки', 'Tents'),
				'children' => array(
					array(
						'id' => 6,
						'name' => array('Кемпинговые', 'For camping'),
						'rewrite' => 'shop/palatki/kempingovye',
						'sort' => 1,
						'menu' => 2,
						'import_id' => '2',
					),
					array(
						'id' => 7,
						'name' => array('Для сложных походов', 'For difficult campaigns'),
						'rewrite' => 'shop/palatki/dlya-slozhnykh-pokhodov',
						'sort' => 2,
						'menu' => 2,
						'import_id' => '3',
					),
					array(
						'id' => 8,
						'name' => array('Тенты и беседки', 'Arbors'),
						'text' => array('<p>Большие просторные тенты для дачи. Такие тенты можно использовать как летнее укрытие от дождя. Внутри свободно разместся несколко человек со столом и стульями.</p>'),
						'rewrite' => 'shop/palatki/tenty-i-besedki',
						'sort' => 3,
						'menu' => 2,
						'import_id' => '4',
					),
					array(
						'id' => 9,
						'name' => array('Мобильные бани', 'Mobile sauna'),
						'rewrite' => 'shop/palatki/mobilnye-bani',
						'sort' => 4,
						'menu' => 2,
						'import_id' => '5',
					),
				),
				'rewrite' => 'shop/palatki',
				'sort' => 2,
				'menu' => 2,
				'import_id' => '6',
			),
			array(
				'id' => 3,
				'name' => array('Спальники', 'Sleeping bags'),
				'rewrite' => 'shop/spalniki',
				'menu' => 2,
				'sort' => 3,
				'import_id' => '7',
			),
			array(
				'id' => 4,
				'name' => array('Мебель', 'Furniture'),
				'rewrite' => 'shop/mebel',
				'menu' => 2,
				'sort' => 4,
				'import_id' => '8',
			),
			array(
				'id' => 5,
				'name' => array('Другое', 'Other'),
				'rewrite' => 'shop/drugoe',
				'menu' => 2,
				'sort' => 5,
				'import_id' => '9',
			),
		),
		'shop_param' => array(
			array(
				'id' => 1,
				'name' => array('Размер', 'Size'),
				'type' => 'text',
				'search' => 1,
				'id_page' => 1,
			),
			array(
				'id' => 2,
				'name' => array('Цвет', 'Color'),
				'type' => 'multiple',
				'required' => 1,
				'search' => 1,
				'id_page' => 1,
				'cat_id' => array(3, 4, 6, 7, 8, 9),
				'select' => array(
					array(
						'id' => 1,
						'value'=> 0,
						'name' => array('Хаки/Бежевый', 'Light haki'),
					),
					array(
						'id' => 2,
						'value'=> 1,
						'name' => array('Синий/Голубой', 'Light blue'),
					),
					array(
						'id' => 3,
						'value'=> 1,
						'name' => array('Зеленый', 'Green'),
					),
					array(
						'id' => 10,
						'value'=> 1,
						'name' => array('Синий', 'Blue'),
					),
					array(
						'id' => 11,
						'value'=> 0,
						'name' => array('Нави', 'Navi'),
					),
					array(
						'id' => 12,
						'value'=> 1,
						'name' => array('Фисташковый', 'Pistascoe'),
					),
					array(
						'id' => 16,
						'value'=> 1,
						'name' => array('Хаки', 'Haki'),
					),
					array(
						'id' => 19,
						'value'=> 1,
						'name' => array('Желтый', 'Yellow'),
					),
					array(
						'id' => 38,
						'value'=> 0,
						'name' => array('Бордовый', 'Dark red'),
					),
					array(
						'id' => 42,
						'value'=> 1,
						'name' => array('Коричневый', 'Brown'),
					),
					array(
						'id' => 47,
						'value'=> 1,
						'name' => array('Серый/красный', 'Gray/red'),
					),
					array(
						'id' => 48,
						'value'=> 1,
						'name' => array('Серый/электрик', 'Gray'),
					),
				),
			),
			array(
				'id' => 3,
				'name' => array('Вместимость', 'Capacity'),
				'type' => 'numtext',
				'measure_unit' => array('человек', 'man'),
				'cat_id' => array(6, 7),
				'id_page' => 1,
			),
			array(
				'id' => 4,
				'name' => array('Конструкция', 'Design'),
				'type' => 'select',
				'search' => 1,
				'list' => 1,
				'cat_id' => array(6, 7, 8, 9),
				'id_page' => 1,
				'select' => array(
					array(
						'id' => 4,
						'name' => array('Автомат', 'Automatic'),
					),
					array(
						'id' => 5,
						'name' => array('Дуговые', 'Arc'),
					),
					array(
						'id' => 20,
						'name' => array('Каркасная', 'Frame'),
					),
				),
			),
			array(
				'id' => 5,
				'name' => array('Проклеенные швы тента', 'Taped seams tent'),
				'type' => 'checkbox',
				'cat_id' => array(6, 7, 8, 9),
				'id_page' => 1,
			),
			array(
				'id' => 6,
				'name' => array('Противомоскитная сетка', 'Mosquito net'),
				'type' => 'checkbox',
				'search' => 1,
				'list' => 1,
				'block' => 1,
				'cat_id' => array(6, 7, 8, 9),
				'id_page' => 1,
			),
			array(
				'id' => 7,
				'name' => array('Вес', 'Weight'),
				'type' => 'numtext',
				'cat_id' => array(4, 5, 6, 7, 8, 9),
				'measure_unit' => array('кг.', 'kg'),
				'id_page' => 1,
			),
			array(
				'id' => 8,
				'name' => array('Габаритные размеры сумки', 'Dimensions bags'),
				'type' => 'text',
				'cat_id' => array(6, 7, 8, 9),
				'id_page' => 1,
			),
			array(
				'id' => 10,
				'name' => array('Материал каркаса', 'Structural material'),
				'type' => 'select',
				'search' => 1,
				'cat_id' => array(4, 7, 9),
				'id_page' => 1,
				'select' => array(
					array(
						'id' => 8,
						'name' => array('Алюминий', 'Aluminium'),
					),
					array(
						'id' => 9,
						'name' => array('Сталь', 'Steel'),
					),
					array(
						'id' => 15,
						'name' => array('Fiberglass', 'Fiberglass'),
					),
				),
			),
			array(
				'id' => 11,
				'name' => array('Система вентиляции', 'Ventilation system'),
				'type' => 'checkbox',
				'cat_id' => 7,
				'id_page' => 1,
			),
			array(
				'id' => 12,
				'name' => array('Стойки', 'Props'),
				'type' => 'select',
				'cat_id' => 8,
				'id_page' => 1,
				'select' => array(
					array(
						'id' => 17,
						'name' => array('Алюминий', 'Aluminium'),
					),
					array(
						'id' => 18,
						'name' => array('Сталь', 'Steel'),
					),
				),
			),
			array(
				'id' => 14,
				'name' => array('Вес', 'Weight'),
				'type' => 'numtext',
				'cat_id' => 2,
				'list' => 1,
				'block' => 1,
				'id_page' => 1,
				'measure_unit' => array('кг.', 'kg'),
			),
			array(
				'id' => 15,
				'name' => array('Объем', 'Volume'),
				'type' => 'numtext',
				'cat_id' => 2,
				'list' => 1,
				'id_page' => 1,
				'measure_unit' => array('л.', 'l'),
			),
			array(
				'id' => 16,
				'name' => array('Грудная стяжка', 'Breast screed'),
				'type' => 'checkbox',
				'cat_id' => 2,
				'id_page' => 1,
			),
			array(
				'id' => 17,
				'name' => array('Высота-ширина-глубина, см', 'Height-width-depth, cm'),
				'type' => 'text',
				'cat_id' => 2,
				'id_page' => 1,
			),
			array(
				'id' => 18,
				'name' => array('Защитный чехол', 'Protective Case'),
				'type' => 'checkbox',
				'cat_id' => 2,
				'search' => 1,
				'list' => 1,
				'block' => 1,
				'id_page' => 1,
				'text' => array('Дополнительный чехол, защищающий рюкзак от дождя', 'Additional cover that protects the bag from the rain'),
			),
			array(
				'id' => 19,
				'name' => array('Боковые стяжки', 'Side Ties'),
				'type' => 'checkbox',
				'cat_id' => 2,
				'id_page' => 1,
			),
			array(
				'id' => 20,
				'name' => array('Цвет', 'Color'),
				'type' => 'multiple',
				'required' => 1,
				'cat_id' => 2,
				'search' => 1,
				'list' => 1,
				'block' => 1,
				'id_page' => 1,
				'select' => array(
					array(
						'id' => 23,
						'name' => array('Коричневый', 'Brown'),
					),
					array(
						'id' => 24,
						'name' => array('Лесная чаща', 'Forest'),
					),
					array(
						'id' => 25,
						'name' => array('Серый', 'Gray'),
					),
					array(
						'id' => 26,
						'name' => array('Серый/оранжевый', 'Gray/orange'),
					),
					array(
						'id' => 27,
						'name' => array('Серый/терракотовый', 'Gray/terracotta'),
					),
					array(
						'id' => 28,
						'name' => array('Серый/олива', 'Gray/olive'),
					),
					array(
						'id' => 29,
						'name' => array('Черный', 'Black'),
					),
					array(
						'id' => 30,
						'name' => array('Серый/черный', 'Gray/black'),
					),
					array(
						'id' => 31,
						'name' => array('Серый/синий', 'Gray/blue'),
					),
					array(
						'id' => 32,
						'name' => array('Зеленый', 'Green'),
					),
					array(
						'id' => 33,
						'name' => array('Серый/красный', 'Gray/red'),
					),
					array(
						'id' => 34,
						'name' => array('Хаки', 'Khaki'),
					),
					array(
						'id' => 35,
						'name' => array('Диджитал зеленый', 'Digital green'),
					),
				),
			),
			array(
				'id' => 21,
				'name' => array('Утеплитель', 'Insulation'),
				'type' => 'select',
				'cat_id' => 3,
				'search' => 1,
				'list' => 1,
				'block' => 1,
				'id_page' => 1,
				'select' => array(
					array(
						'id' => 36,
						'name' => array('Thermofibre-S-Pro', 'Thermofibre-S-Pro'),
					),
					array(
						'id' => 40,
						'name' => array('Термофайбер', 'Thermofibre'),
					),
					array(
						'id' => 41,
						'name' => array('Hollow fiber', 'Hollow fiber'),
					),
				),
			),
			array(
				'id' => 22,
				'name' => array('Температура комфорта', 'Comfort temperature'),
				'type' => 'text',
				'cat_id' => 3,
				'id_page' => 1,
			),
			array(
				'id' => 23,
				'name' => array('Температура экстрима', 'Extreme low'),
				'type' => 'text',
				'cat_id' => 3,
				'id_page' => 1,
			),
			array(
				'id' => 24,
				'name' => array('Вес', 'Weight'),
				'type' => 'numtext',
				'cat_id' => 3,
				'id_page' => 1,
				'measure_unit' => array('кг.', 'kg'),
			),
			array(
				'id' => 25,
				'name' => array('Планка утепляющая молнию', 'Planck insulate lightning'),
				'type' => 'checkbox',
				'cat_id' => 3,
				'id_page' => 1,
			),
			array(
				'id' => 26,
				'name' => array('Размеры', 'Size'),
				'type' => 'text',
				'cat_id' => 4,
				'id_page' => 1,
			),
			array(
				'id' => 27,
				'name' => array('Максимальная нагрузка', 'Maximum load'),
				'type' => 'numtext',
				'cat_id' => 4,
				'id_page' => 1,
				'measure_unit' => array('кг.', 'kg'),
			),
			array(
				'id' => 28,
				'name' => array('Назначение и устройство', 'Purpose and drive'),
				'type' => 'select',
				'cat_id' => 5,
				'search' => 1,
				'id_page' => 1,
				'select' => array(
					array(
						'id' => 49,
						'name' => array('Набор посуды', 'Cookware Set'),
					),
					array(
						'id' => 50,
						'name' => array('Посуда', 'Dishes'),
					),
					array(
						'id' => 51,
						'name' => array('Чайник', 'Tea'),
					),
					array(
						'id' => 52,
						'name' => array('Кружка', 'Mug'),
					),
				),
			),
			array(
				'id' => 29,
				'name' => array('Материал', 'Material'),
				'type' => 'multiple',
				'cat_id' => 5,
				'id_page' => 1,
				'select' => array(
					array(
						'id' => 44,
						'name' => array('Нержавеющая пищевая сталь', 'Stainless steel food'),
					),
					array(
						'id' => 45,
						'name' => array('Пищевой алюминий', 'Food aluminum'),
					),
					array(
						'id' => 46,
						'name' => array('Пластик', 'Plastic'),
					),
				),
			),
			array(
				'id' => 30,
				'name' => array('Вместимость', 'Capacity'),
				'type' => 'numtext',
				'cat_id' => 5,
				'id_page' => 1,
				'measure_unit' => array('л.', 'l'),
			),
		),
		'shop' => array(
			array(
				'id' => 1,
				'name' => array('Баня походная N'),
				'article' => '24068',
				'cat_id' => 9,
				'brand_id' => 1,
				'anons' => array(
					'<p>Можно и в реке помыться, а зимой? Да сколько там этой зимы!</p>'
				),
				'text' => array(
					"<p>Если это не про Вас, то знайте – теперь есть отличное решение для организации полноценной походной бани в любых условиях! Мобильную походную баню можно организовать как в многодневном лыжном или треккинговом походе, так и в кемпинге на отдыхе у реки. В удовольствии попариться в баньке не откажет себе ни охотник, ни рыбак. А дачнику просто необходимо смыть с себя пыль и усталость после аграрных баталий.</p><p>Компания Нова Тур позаботилась о Вашем комфорте и благополучии и создала незаменимую палатку-баню с непревзойденными качествами! Походная «Баня N» производства Нова Тур устанавливается в любом удобном для Вас месте за считанные минуты. Причем не нужно тащить с собой до места сложный и массивный каркас, в качестве стоек можно использовать добытые прямо по месту стволы деревьев длиной на15-30 см больше высоты бани, для того, чтобы надежно вбить их в землю над заготовленным заранее очагом. Желательно дополнительно раскрепить каркас походной бани диагоналями для большей прочности. Для организации походной каменки внутри предусмотрена огнеупорная накладка. Во избежание поддуваний походная баня имеет ветрозащитную юбку по всему периметру, а для обеспечения особого комфорта имеются прозрачные окна на стенках. Вот так, без особых усилий и затрат можно организовать необходимые удобства в походных условиях.</p><p>Конечно походная «Баня N» запросто уместится в багажнике автолюбителя, а у водителя «дальнобойщика» в кабине может и вообще кататься постоянно, но с таким маленьким весом, всего 2,5 кг мобильная баня и в пеших походах запросто переносится одним человеком!</p><p>Можно подытожить - низкий вес и доступная цена палатки «Баня N» в сочетании с высочайшими эксплуатационными качествами способны удовлетворить потребности самого требовательного любителя «легкого пара»! Душистый веничек, березовый, дубовый! Эвкалипт, мята, холодненькое пивко после парной! Ах! Как же охота побыстрее сбежать из города! А вам?</p><p>Не тяните, покупайте палатку «Баня N» производства Нова Тур, отправляйтесь на природу, в лес, к реке! Отдыхайте, наслаждайтесь единством с природой, парьтесь в баньке!</p><p>С легким паром!</p>"
				),
				'rewrite' => 'shop/palatki/mobilnye-bani/banya-pokhodnaya-n',
				'images' => array(
					'104_banya-pokhodnaya-n.jpg',
					'103_banya-pokhodnaya-n.jpg',
					'105_banya-pokhodnaya-n.jpg',
				),
				'param' => array(
					2 => 19,
					4 => 20,
					5 => 1,
					6 => 1,
					6 => 1,
					7 => '2.5',

				),
				'price' => array(
					array(
						'price' => 3990,
						'param' => array(
							2 => 19,
						),
					),
				),
				'import_id' => '1',
			),
			array(
				'id' => 2,
				'name' => array('Баня мобильная с каркасом'),
				'article' => '95322',
				'cat_id' => 9,
				'brand_id' => 1,
				'anons' => array(
					"<p>Можно и в реке помыться, а зимой? Да сколько там этой зимы!</p>",
				),
				'text' => array(
					"<p>Если это не про Вас, то знайте – теперь есть отличное решение для организации полноценной походной бани в любых условиях! Мобильную походную баню можно организовать как в многодневном лыжном или треккинговом походе, так и в кемпинге на отдыхе у реки. В удовольствии попариться в баньке не откажет себе ни охотник, ни рыбак. А дачнику просто необходимо смыть с себя пыль и усталость после аграрных баталий.<br />    <br />Компания Нова Тур позаботилась о Вашем комфорте и благополучии и создала незаменимую палатку-баню с непревзойденными качествами! Походная «Баня N» производства Нова Тур устанавливается в любом удобном для Вас месте за считанные минуты. Предлагаемый вариант бани снабжен стальным каркасом, теперь не нужно рубить стойки или искать подходящие деревья для ее установки. Желательно дополнительно раскрепить каркас походной бани диагоналями для большей прочности. Для организации походной каменки внутри предусмотрена огнеупорная накладка. Во избежание поддуваний походная баня имеет ветрозащитную юбку по всему периметру, а для обеспечения особого комфорта имеются прозрачные окна на стенках. Вот так, без особых усилий и затрат можно организовать необходимые удобства в походных условиях.<br />  <br />Конечно мобильная «Баня N» запросто уместится в багажнике автолюбителя, а у водителя «дальнобойщика» в кабине может и вообще кататься постоянно, но с таким маленьким весом, всего 3,9 кг мобильная баня и в пеших походах запросто переносится одним человеком!<br /> <br />Можно подытожить - низкий вес и доступная цена палатки «Баня N» в сочетании с высочайшими эксплуатационными качествами способны удовлетворить потребности самого требовательного любителя «легкого пара»! Душистый веничек, березовый, дубовый! Эвкалипт, мята, холодненькое пивко после парной! Ах! Как же охота побыстрее сбежать из города! А вам?<br /> <br />Не тяните, покупайте мобильную баню производства Нова Тур, отправляйтесь на природу, в лес, к реке! Отдыхайте, наслаждайтесь единством с природой, парьтесь в баньке! <br />С легким паром!</p>",
				),
				'images' => array(
					'106_banya-mobilnaya-s-karkasom.jpg',
					'107_banya-mobilnaya-s-karkasom.jpg',
					'108_banya-mobilnaya-s-karkasom.jpg',
					'109_banya-mobilnaya-s-karkasom.jpg',
				),
				'rewrite' => 'shop/palatki/mobilnye-bani/banya-mobilnaya-s-karkasom',
				'param' => array(
					2 => 19,
					4 => 20,
					5 => 1,
					6 => 1,
					7 => '3.9',
					10 => 9,
				),
				'price' => array(
					array(
						'price' => 6490,
						'param' => array(
							2 => 19,
						),
					),
				),
				'import_id' => '2',
			),
			array(
				'id' => 3,
				'name' => array('Палатка «Керри 4 v.2»'),
				'cat_id' => 6,
				'brand_id' => 2,
				'article' => '25373',
				'anons' => array(
					"<p>Комфортная палатка при умеренном весе. Лучшее соотношение вес/комфорт/цена</p>",
				),
				'text' => array(
					"<p>Комфортная палатка при умеренном весе.</p><p>Один вход и один тамбур. Легко устанавливается одним человеком. Возможна отдельная установка тента.</p>",
				),
				'images' => array(
					'1_palatka-kerri-4-v2.jpg',
					'2_palatka-kerri-4-v2.jpg',
				),
				'rewrite' => 'shop/palatki/kempingovye/palatka-kerri-4-v2',
				'param' => array(
					2 => 3,
					3 => '4',
					4 => 5,
					5 => '1',
					6 => '1',
					7 => '5.55',
					8 => '66 х 20 х 20 см',
				),
				'price' => array(
					array(
						'price' => 4990,
					),
				),
				'import_id' => '3',
			),
			array(
				'id' => 4,
				'name' => array('Палатка «Арди 4/5»'),
				'cat_id' => 6,
				'brand_id' => 2,
				'article' => '25593',
				'anons' => array(
					"<p>Просторная кемпинговая палатка с вариантами трансформации.</p>",
				),
				'text' => array(
					"<p>Просторная кемпинговая палатка с вариантами трансформации.</p>
					<p>Конструкция полубочка, Прозрачные окна. Антикомариная система (юбка и сетка в тамбуре). Различные варианты конфигурации (2+2+3). Два входа, две комнаты, большой тамбур. Проточная вентиляция,. Возможна установка тента без внутренних палаток.</p>",
				),
				'action' => 1,
				'images' => array(
					'4_palatka-ardi-45.jpg',
					'3_palatka-ardi-45.jpg',
				),
				'rewrite' => 'shop/palatki/kempingovye/palatka-ardi-45',
				'param' => array(
					2 => 3,
					3 => '4',
					4 => 5,
					5 => '1',
					6 => '1',
					7 => '11.53',
					8 => '70 х 25 х 25 см',
				),
				'price' => array(
					array(
						'price' => 10490,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '4',
			),
			array(
				'id' => 5,
				'name' => array('Палатка «Гранард 6»'),
				'cat_id' => 6,
				'brand_id' => 2,
				'article' => '25573',
				'anons' => array(
					"<p>Большая кемпинговая палатка с 3-мя комнатами и очень большим тамбуром</p>",
				),
				'text' => array(
					"<p>Большая кемпинговая палатка с 3-мя комнатами и очень большим тамбуром</p>
<p>Три спальных отделения, проточная вентиляция. Возможна отдельная установка тента.</p>
<p>Состоит из 3х спальных мест размерами: 120 ширина* 210 длина* 110 высота и общего большого тамбура</p>",
				),
				'images' => array(
					'9_palatka-granard-6.jpg',
					'6_palatka-granard-6.jpg',
					'7_palatka-granard-6.jpg',
					'8_palatka-granard-6.jpg',
					'5_palatka-granard-6.jpg',
				),
				'rewrite' => 'shop/palatki/kempingovye/palatka-granard-6',
				'param' => array(
					2 => 3,
					3 => '6',
					4 => 5,
					5 => '1',
					6 => '1',
				),
				'price' => array(
					array(
						'price' => 13990,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '5',
			),
			array(
				'id' => 6,
				'name' => array('Палатка «Монахан 4»'),
				'article' => '25563',
				'cat_id' => 6,
				'brand_id' => 2,
				'anons' => array(
					"<p>Легкая кемпинговая палатка оригинальной конструкции.</p>",
				),
				'text' => array(
					"<p>Легкая кемпинговая палатка оригинальной конструкции.</p>
<p>Два спальных отделения. Проточная вентиляция большого тамбура. Возможна отдельная установка тента.</p>",
				),
				'images' => array(
					'12_palatka-monakhan-4.jpg',
					'11_palatka-monakhan-4.jpg',
					'10_palatka-monakhan-4.jpg',
				),
				'rewrite' => 'shop/palatki/kempingovye/palatka-monakhan-4',
				'param' => array(
					2 => 3,
					3 => '4',
					4 => 5,
					5 => '1',
					6 => '1',
					7 => '9.7',
					8 => '68 х 25 х 25 см',
				),
				'price' => array(
					array(
						'price' => 7990,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '6',
			),
			array(
				'id' => 7,
				'name' => array('Палатка «Скаут 4»'),
				'article' => '21139',
				'cat_id' => 6,
				'brand_id' => 1,
				'hit' => 1,
				'anons' => array(
					'<p>Классическая палатка при умеренном весе</p>',
				),
				'text' => "<p><strong>Классическая палатка при умеренном весе</strong><br />Практичная палатка для туризма и отдыха на природе. Даже если Вас застал дождь, конструкция палатки с проклеенными швами обеспечит защиту от непогоды, а противомоскитные сетки и увеличенная световая пропускная способность потолка придадут отдыху больше комфорта и уюта.</p>",
				'images' => array(
					'13_palatka-skaut-4.jpg',
					'14_palatka-skaut-4.jpg',
				),
				'param' => array(
					2 => array(1, 2),
					3 => '4',
					5 => '1',
					6 => '1',
					7 => '7.3',
				),
				'rewrite' => 'shop/palatki/kempingovye/palatka-skaut-4',
				'price' => array(
					array(
						'price' => 3990,
						'param' => array(
							2 => 2,
						),
						'image_rel' => '14_palatka-skaut-4.jpg',
					),
					array(
						'price' => 3990,
						'param' => array(
							2 => 1,
						),
						'image_rel' => '13_palatka-skaut-4.jpg',
					),
				),
				'import_id' => '7',
			),
			array(
				'id' => 8,
				'name' => array('Палатка «Браво 5» N'),
				'cat_id' => 6,
				'brand_id' => 1,
				'article' => '23038',
				'anons' => array(
					"<p>Кемпинговая дуговая палатка</p>",
				),
				'new' => 1,
				'text' => array(
					"<p>Палатка для кемпинга с большим тамбуром и одним спальным отделением. Конструкция позволяет установку тента отдельно и установку палатки во время дождя. Противомоскитные сетки на внутренней палатке и тамбуре. Объемный тамбур с тремя входами и ветрозащитной юбкой может использоватьсякак беседка с эффективной защитой от насекомых. Потолочная система вентиляции позволяет выводить конденсат.</p>",

					''
				),
				'rewrite' => 'shop/palatki/kempingovye/palatka-bravo-5-n',
				'images' => array(
					'25_palatka-bravo-5-n.jpg',
					'20_palatka-bravo-5-n.jpg',
					'21_palatka-bravo-5-n.jpg',
					'22_palatka-bravo-5-n.jpg',
				),
				'param' => array(
					2 => array(1, 2),
					3 => '5',
					4 => 5,
					5 => '1',
					6 => '1',
					7 => '15',
					7 => '72 х 25 х 25 см',
				),
				'price' => array(
					array(
						'price' => 11990,
						'param' => array(
							2 => 2,
						),
						'image_rel' => '20_palatka-bravo-5-n.jpg',
					),
					array(
						'price' => 11990,
						'param' => array(
							2 => 1,
						),
						'image_rel' => '25_palatka-bravo-5-n.jpg',
					),
				),
				'import_id' => '8',
			),
			array(
				'id' => 9,
				'name' => array('Палатка «Тоннель 3 комфорт»'),
				'cat_id' => 6,
				'brand_id' => 1,
				'article' => '23098',
				'anons' => array(
					"<p>Большой внутренний объём при умеренном весе</p>",
				),
				'text' => array(
					"<p>Если у Вас нету дома&hellip; классическая туннелеобразная палатка станет надежным приютом в путешествии. Наличие большого тамбура, двух входов делают ее особенно удобной для отдыха у воды. Отличный вариант для семейного отдыха с детьми. Возможна отдельная установка тента. Внутренняя палатка пристегивается изнутри.<strong></strong></p>",
					''
				),
				'images' => array(
					'26_tonnel-3-komfort.jpg',
					'27_tonnel-3-komfort.jpg',
					'28_tonnel-3-komfort.jpg',
					'29_tonnel-3-komfort.jpg',
				),
				'rewrite' => 'shop/palatki/kempingovye/tonnel-3-komfort',
				'param' => array(
					2 => array(1, 2),
					4 => 5,
					5 => '1',
					6 => '1',
					7 => '6.5',
					8 => '62 х 19 х 18 см',
				),
				'price' => array(
					array(
						'price' => 6090,
						'param' => array(
							2 => 2,
						),
						'image_rel' => '27_tonnel-3-komfort.jpg',
					),
					array(
						'price' => 6090,
						'param' => array(
							2 => 1,
						),
						'image_rel' => '26_tonnel-3-komfort.jpg',
					),
				),
				'import_id' => '9',
			),
			array(
				'id' => 10,
				'name' => array('Палатка «Килкени 5 v.2»'),
				'article' => '25553',
				'cat_id' => 6,
				'brand_id' => 2,
				'anons' => array(
					"<p>Просторная кемпинговая палатка с большими окнами.</p>",
				),
				'text' => array(
					"<p>Одно спальное отделение. Очень просторный тамбур. Большие окна со шторками. Возможна отдельная установка тента.</p>",
					''
				),
				'images' => array(
					'31_kilkeni-5-v2.jpg',
					'32_kilkeni-5-v2.jpg',
					'30_kilkeni-5-v2.jpg',
				),
				'rewrite' => 'shop/palatki/kempingovye/kilkeni-5-v2',
				'param' => array(
					2 => 3,
					3 => "5",
					4 => 5,
					5 => '1',
					6 => '1',
					7 => '14.8',
					8 => '75 х 30 х 30 см',
				),
				'price' => array(
					array(
						'price' => 14490,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '10',
			),
			array(
				'id' => 11,
				'name' => array('Палатка семейная «Хоут 4»'),
				'article' => '95286',
				'cat_id' => 6,
				'brand_id' => 2,
				'anons' => array(
					"<p>Так быстро кемпинг еще не устанавливался!</p>",
				),
				'text' => array(
					"<p>Большая палатка для отдыха всей семьей Хоут 4. Просторная палатка с большим тамбуром позволяет разместить не одну, а даже две походные кровати! В палатке свободно можно стать в полный рост, вольготно разложить коврики и спальные мешки, организовать полноценные спальные места. Размеры внутреннего пространства палатки Хоут 4 позволяют обеспечить дополнительный комфорт, разместив не только кровать, но и другие элементы кемпинговой мебели, стол или походный стеллаж. Хоут 4 имеет ветровые оттяжки из световозвращающего шнура, что сокращает до минимума возможность случайно зацепиться за них в темное время суток, как шаловливыми детьми, так и охмелевшими от свежего воздуха взрослыми. <br />Не вызывает сомнений, что с такими достоинствами 4-х местная палатка Хоут 4 является идеальным выбором для комфортного семейного отдыха на природе!</p><p>Высокая кемпинговая палатка с полуавтоматическим каркасом. Установка за 1 минуту. Двухслойная палатка с большим тамбуром. Возможна отдельная установка тента. Легко ставиться одним человеком. Минимум времени для установки и сборки. Q-образный вход продублирован сеткой. Улучшенная сквозная вентиляция. Проклеенные швы. Облегченная регулировка оттяжек со световозвращающей нитью. Москитная сетка. Дополнительные стальные стойки для полога.</p>",
					''
				),
				'images' => array(
					'34_palatka-semeynaya-khout-4.jpg',
					'35_palatka-semeynaya-khout-4.jpg',
					'33_palatka-semeynaya-khout-4.jpg',
				),
				'rewrite' => 'shop/palatki/kempingovye/palatka-semeynaya-khout-4',
				'param' => array(
					2 => 3,
					3 => 4,
					4 => 4,
					5 => 1,
					6 => 1,
					7 => '8.95',
					8 => '102 х 22 х 22 см',
				),
				'price' => array(
					array(
						'price' => 9990,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '11',
			),
			array(
				'id' => 12,
				'name' => array('Палатка «Катунь 4»'),
				'article' => '23118',
				'cat_id' => 6,
				'brand_id' => 1,
				'anons' => array(
					"<p>Комфортная кемпинговая палатка с минимальным весом</p>",
				),
				'text' => array(
					"<p>Универсальная кемпинговая палатка с прозрачными окнами и объемным тамбуром. Просторный тамбур  комфортно вместит все необходимое снаряжение или кухню. Противомоскитная сетка позволит не думать о насекомых, а внутренние карманы придадут удобства в хранении мелочей.</p>",
					''
				),
				'new' => 1,
				'images' => array(
					'15_palatka-katun-4.jpg',
					'16_palatka-katun-4.jpg',
					'17_palatka-katun-4.jpg',
					'18_palatka-katun-4.jpg',
					'19_palatka-katun-4.jpg',
				),
				'param' => array(
					2 => array(2, 3),
					3 => '4',
					4 => 5,
					5 => '1',
					6 => '1',
					7 => '7',
					8 => '72 х 19 х 18 см',
				),
				'rewrite' => 'shop/palatki/kempingovye/palatka-katun-4',
				'price' => array(
					array(
						'price' => 5990,
						'param' => array(
							2 => 2,
						),
						'image_rel' => '16_palatka-katun-4.jpg',
					),
					array(
						'price' => 5990,
						'param' => array(
							2 => 1,
						),
						'image_rel' => '15_palatka-katun-4.jpg',
					),
				),
				'import_id' => '12',
			),
			array(
				'id' => 13,
				'name' => array('Палатка «Смарт 3»'),
				'cat_id' => 7,
				'brand_id' => 1,
				'article' => '21078',
				'anons' => array(
					"<p>Легкая и надежная</p>",
				),
				'text' => array(
					"<p>Двухслойная дуговая палатка. Обладает наибольшим жилым объемом при минимально возможном весе. Устанавливается легко и быстро даже на небольшой площадке. Отлично подойдет для путешествия вдвоем.</p>",

					''
				),
				'hit' => 1,
				'images' => array(
					'40_smart-3.jpg',
					'36_smart-3.jpg',
					'38_smart-3.jpg',
					'41_smart-3.jpg',
				),
				'param' => array(
					2 => array(10, 11),
					3 => '3',
					4 => 5,
					5 => '1',
					6 => '1',
					7 => '3.15',
					8 => '46 х 18 х 18 см',
					10 => 8,
					11 => '1',
				),
				'rewrite' => 'shop/palatki/dlya-slozhnykh-pokhodov/smart-3',
				'price' => array(
					array(
						'price' => 3990,
						'param' => array(
							2 => 10,
						),
						'image_rel' => '40_smart-3.jpg',
					),
					array(
						'price' => 3990,
						'param' => array(
							2 => 11,
						),
						'image_rel' => '36_smart-3.jpg',
					),
				),
				'import_id' => '13',
			),

			array(
				'id' => 14,
				'name' => array('Палатка «Эксплорер 4 N»'),
				'cat_id' => 7,
				'brand_id' => 1,
				'article' => '21051',
				'anons' => array(
					"<p>Классическая двухслойная палатка</p>",
				),
				'text' => array(
					"<p>Сочетает в себе удобство туристической и надежность горной модели. Обладает высокой ветроустойчивостью. Благодаря поперечной полудуге увеличен объем тамбуров. Внутренняя палатка может использоваться без тента.</p>",
					''
				),
				'images' => array(
					'42_palatka-eksplorer-4-n.jpg',
					'43_palatka-eksplorer-4-n.jpg',
					'44_palatka-eksplorer-4-n.jpg',
					'45_palatka-eksplorer-4-n.jpg',
					'46_palatka-eksplorer-4-n.jpg',
				),
				'param' => array(
					2 => array(10, 11),
					3 => '4',
					4 => 5,
					5 => '1',
					6 => '1',
					7 => '3.95',
					8 => '46 х 18 х 18 см',
					10 => 8,
					11 => '1',
				),
				'rewrite' => 'shop/palatki/dlya-slozhnykh-pokhodov/palatka-eksplorer-4-n',
				'price' => array(
					array(
						'price' => 7290,
						'param' => array(
							2 => 10,
						),
						'image_rel' => '42_palatka-eksplorer-4-n.jpg',
					),
					array(
						'price' => 7290,
						'param' => array(
							2 => 11,
						),
						'image_rel' => '43_palatka-eksplorer-4-n.jpg',
					),
				),
				'import_id' => '14',
			),
			array(
				'id' => 15,
				'name' => array('Палатка «Ангара 3»'),
				'cat_id' => 7,
				'brand_id' => 1,
				'article' => '21149',
				'anons' => array(
					"<p>Универсальная палатка для путешествий</p>",
				),
				'text' => array(
					"<p>Большое пространство и простота конструкции обеспечат максимум комфорта. В тамбурах свободно разместятся рюкзаки. Два входа, оборудованные москитной сеткой, улучшают вентиляцию и защищают от назойливых насекомых, а проклеенные швы не позволят непогоде испортить Ваш отдых. Конструкция позволяет устанавливать палатку без тента.</p>",
					''
				),
				'hit' => 1,
				'images' => array(
					'47_palatka-angara-3.jpg',
					'48_palatka-angara-3.jpg',
					'49_palatka-angara-3.jpg',
				),
				'param' => array(
					2 => 1,
					3 => '3',
					4 => 5,
					7 => '5.9',
					10 => 15,
				),
				'rewrite' => 'shop/palatki/dlya-slozhnykh-pokhodov/palatka-angara-3',
				'price' => array(
					array(
						'price' => 4490,
						'param' => array(
							2 => 1,
						),
					),
				),
				'import_id' => '15',
			),
			array(
				'id' => 16,
				'name' => array('Палатка «Битл 3»'),
				'cat_id' => 7,
				'brand_id' => 1,
				'article' => '21191',
				'anons' => array(
					"<p>Туристическая палатка повышенной комфортности</p>",
				),
				'text' => array(
					"<p>Трехместная палатка имеет оригинальную конструкцию каркаса – радиальное сочленение дуг, что позволяет максимально увеличить внутренний объем – боковые стенки практически вертикальны.</p>",
					''
				),
				'hit' => 1,
				'images' => array(
					'50_palatka-bitl-3.jpg',
					'52_palatka-bitl-3.jpg',
					'53_palatka-bitl-3.jpg',
					'51_palatka-bitl-3.jpg',
				),
				'param' => array(
					2 => array(10, 11),
					3 => '3',
					4 => 5,
					5 => '1',
					6 => '1',
					10 => 8,
					11 => '1',
				),
				'rewrite' => 'shop/palatki/dlya-slozhnykh-pokhodov/palatka-bitl-3',
				'price' => array(
					array(
						'price' => 6990,
						'param' => array(
							2 => 10,
						),
						'image_rel' => '50_palatka-bitl-3.jpg',
					),
					array(
						'price' => 6990,
						'param' => array(
							2 => 11,
						),
						'image_rel' => '52_palatka-bitl-3.jpg',
					),
				),
				'import_id' => '16',
			),
			array(
				'id' => 17,
				'name' => array('Палатка «Эксплорер 3 N»'),
				'cat_id' => 7,
				'brand_id' => 1,
				'article' => '21041',
				'anons' => array(
					"<p>Классическая двухслойная палатка</p>",
				),
				'text' => array(
					"<p>Сочетает в себе удобство туристической и надежность горной модели. Обладает высокой ветроустойчивостью. Благодаря поперечной полудуге увеличен объем тамбуров. Внутренняя палатка может использоваться без тента.</p>",
					''
				),
				'action' => 1,
				'images' => array(
					'54_palatka-eksplorer-3-n.jpg',
					'55_palatka-eksplorer-3-n.jpg',
					'56_palatka-eksplorer-3-n.jpg',
					'57_palatka-eksplorer-3-n.jpg',
					'58_palatka-eksplorer-3-n.jpg',
				),
				'param' => array(
					2 => array(10, 11),
					3 => '3',
					5 => '1',
					6 => '1',
					7 => '3.45',
					8 => '46 х 18 х 18 см',
					11 => '1',
				),
				'rewrite' => 'shop/palatki/dlya-slozhnykh-pokhodov/palatka-eksplorer-3-n',
				'price' => array(
					array(
						'price' => 6290,
						'param' => array(
							2 => 10,
						),
						'image_rel' => '55_palatka-eksplorer-3-n.jpg',
					),
					array(
						'price' => 6490,
						'param' => array(
							2 => 11,
						),
						'image_rel' => '54_palatka-eksplorer-3-n.jpg',
					),
				),
				'import_id' => '17',
			),
			array(
				'id' => 18,
				'name' => array('Палатка «Смарт 2»'),
				'cat_id' => 7,
				'brand_id' => 1,
				'article' => '21068',
				'anons' => array(
					"<p>Легкая и надежная</p>",
				),
				'text' => array(
					"<p>Двухслойная дуговая палатка. Обладает наибольшим жилым объемом при минимально возможном весе. Устанавливается легко и быстро даже на небольшой площадке. Отлично подойдет для путешествия вдвоем.</p>",
					''
				),
				'no_buy' => 1,
				'images' => array(
					'59_palatka-smart-2.jpg',
					'60_palatka-smart-2.jpg',
					'61_palatka-smart-2.jpg',
					'62_palatka-smart-2.jpg',
				),
				'param' => array(
					2 => array(10, 11),
					3 => '2',
					5 => '1',
					6 => '1',
					7 => '2.75',
					8 => '46 х 16 х 16 см',
					10 => 8,
					11 => '1',
				),
				'rewrite' => 'shop/palatki/dlya-slozhnykh-pokhodov/palatka-smart-2',
				'price' => array(
					array(
						'price' => 3590,
						'param' => array(
							2 => 10,
						),
						'image_rel' => '60_palatka-smart-2.jpg',
					),
					array(
						'price' => 3590,
						'param' => array(
							2 => 11,
						),
						'image_rel' => '59_palatka-smart-2.jpg',
					),
				),
				'import_id' => '18',
			),
			array(
				'id' => 19,
				'name' => array('Палатка «Битл 2»'),
				'cat_id' => 7,
				'brand_id' => 1,
				'article' => '21181',
				'anons' => array(
					"<p>Туристическая палатка повышенной комфортности</p>",
				),
				'text' => array(
					"<p>Двухместная палатка имеет оригинальную конструкцию каркаса – радиальное сочленение дуг, что позволяет максимально увеличить внутренний объем – боковые стенки практически вертикальны.</p>",
					''
				),
				'new' => 1,
				'images' => array(
					'63_palatka-bitl-2.jpg',
					'64_palatka-bitl-2.jpg',
					'65_palatka-bitl-2.jpg',
					'66_palatka-bitl-2.jpg',
					'67_palatka-bitl-2.jpg',
				),
				'param' => array(
					2 => array(10, 11),
					3 => '2',
					4 => 5,
					5 => '1',
					6 => '1',
					7 => '3.25',
					8 => '46 х 16 х 16 см',
					10 => 8,
					11 => '1',
				),
				'rewrite' => 'shop/palatki/dlya-slozhnykh-pokhodov/palatka-bitl-2',
				'price' => array(
					array(
						'price' => 6490,
						'param' => array(
							2 => 10,
						),
						'image_rel' => '64_palatka-bitl-2.jpg',
					),
					array(
						'price' => 6490,
						'param' => array(
							2 => 11,
						),
						'image_rel' => '63_palatka-bitl-2.jpg',
					),
				),
				'import_id' => '19',
			),
			array(
				'id' => 20,
				'name' => array('Палатка «Ай Петри 2 Si»'),
				'cat_id' => 7,
				'brand_id' => 1,
				'article' => '21161',
				'anons' => array(
					"<p>Палатка с тентом из силиконовой ткани</p>",
				),
				'text' => array(
					"<p>Палатка нового поколения с тентом из двухстороннего силиконового покрытия. Покрытие износостойкое, не разрушается под воздействием ультрафиолета и имеет высокую прочность на разрыв. Герметичность швам придает специальная обработка ниток – нанесенный на них состав заполняет проколы иглы. Палатка имеет облегченный вес и усовершенствованную систему вентиляции, позволяющую открывать и закрывать вентиляционный клапан тента изнутри.</p>",
					''
				),
				'hit' => 1,
				'images' => array(
					'68_palatka-ay-petri-2-si.jpg',
					'69_palatka-ay-petri-2-si.jpg',
					'70_palatka-ay-petri-2-si.jpg',
					'71_palatka-ay-petri-2-si.jpg',
				),
				'param' => array(
					2 => 12,
					3 => '2',
					4 => 5,
					5 => '1',
					6 => '1',
					7 => '2.85',
					8 => '47 х 16 х 16 см',
					10 => 8,
					11 => '1',
				),
				'rewrite' => 'shop/palatki/dlya-slozhnykh-pokhodov/palatka-ay-petri-2-si',
				'price' => array(
					array(
						'price' => 7990,
						'param' => array(
							2 => 12,
						),
					),
				),
				'import_id' => '20',
			),
			array(
				'id' => 21,
				'name' => array('Тент 3*4 N'),
				'cat_id' => 8,
				'brand_id' => 1,
				'article' => '21181',
				'anons' => array(
					"<p>Надежная защита от непогоды</p>",
				),
				'text' => array(
					"<p>Окажется незаменимым помощником как в непогоду, так и в знойный день. Защитит Вашу стоянку от дождя, палящего солнца, придаст отдыху на природе дополнительный комфорт. Конек усилен стропой, на углах – петли для растяжек. Комплектуется 12 метровым шнуром для оттяжек.</p>",
					''
				),
				'new' => 1,
				'images' => array(
					'72_tent-34-n.jpg',
					'73_tent-34-n.jpg',
					'74_tent-34-n.jpg',
				),
				'param' => array(
					1 => '2,85 Х4',
					2 => array(3, 12),
					7 => '1.2',
				),
				'rewrite' => 'shop/palatki/tenty-i-besedki/tent-34-n',
				'price' => array(
					array(
						'price' => 1190,
						'param' => array(
							2 => 3,
						),
						'image_rel' => '73_tent-34-n.jpg',
					),
					array(
						'price' => 1190,
						'param' => array(
							2 => 16,
						),
						'image_rel' => '72_tent-34-n.jpg',
					),
				),
				'import_id' => '21',
			),
			array(
				'id' => 22,
				'name' => array('Тент 4*5,8'),
				'cat_id' => 8,
				'brand_id' => 1,
				'article' => '24090',
				'anons' => array(
					"<p>Надежная защита от непогоды</p>",
				),
				'text' => array(
					"<p>Окажется незаменимым помощником как в непогоду, так и в знойный день. Защитит Вашу стоянку от дождя, палящего солнца, придаст отдыху на природе дополнительный комфорт. Конек усилен стропой, на углах – петли для растяжек. Комплектуется 12 метровым шнуром для оттяжек.</p>",
					''
				),
				'images' => array(
					'75_tent-458.jpg',
					'76_tent-458.jpg',
					'77_tent-458.jpg',
				),
				'param' => array(
					2 => array(10, 16),
					7 => '3',
				),
				'rewrite' => 'shop/palatki/tenty-i-besedki/tent-458',
				'price' => array(
					array(
						'price' => 2590,
						'param' => array(
							2 => 10,
						),
						'image_rel' => '76_tent-458.jpg',
					),
					array(
						'price' => 2590,
						'param' => array(
							2 => 16,
						),
						'image_rel' => '75_tent-458.jpg',
					),
				),
				'import_id' => '22',
			),
			array(
				'id' => 23,
				'name' => array('Палатка «Тетра»'),
				'cat_id' => 8,
				'brand_id' => 2,
				'article' => '25633',
				'anons' => array(
					"<p>Каркасный тент-беседка с большим внутренним пространством.</p>",
				),
				'text' => array(
					"<p>Три  входа с разных сторон.  Стенки из антимоскитной сетки дублируются непромокаемой тканью.</p>",
					''
				),
				'images' => array(
					'78_palatka-tetra.jpg',
					'79_palatka-tetra.jpg',
					'80_palatka-tetra.jpg',
					'81_palatka-tetra.jpg',
					'82_palatka-tetra.jpg',
				),
				'param' => array(
					2 => 3,
					5 => '1',
					6 => '1',
					7 => '10.5',
					8 => '68 х 23 х 23 см',
				),
				'rewrite' => 'shop/palatki/tenty-i-besedki/palatka-tetra',
				'price' => array(
					array(
						'price' => 10990,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '23',
			),
			array(
				'id' => 24,
				'name' => array('Палатка «Квадра»'),
				'cat_id' => 8,
				'brand_id' => 2,
				'article' => '25623',
				'anons' => array(
					"<p>Облегченный каркасный тент-беседка</p>",
				),
				'text' => array(
					"<p>Два входа с разных сторон. Стенки из антимоскитной сетки дублируются непромокаемой тканью.</p>",
					''
				),
				'images' => array(
					'83_palatka-kvadra.jpg',
					'84_palatka-kvadra.jpg',
					'85_palatka-kvadra.jpg',
				),
				'param' => array(
					2 => 3,
					5 => '1',
					6 => '1',
					7 => '9.6',
				),
				'rewrite' => 'shop/palatki/tenty-i-besedki/palatka-kvadra',
				'price' => array(
					array(
						'price' => 8490,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '24',
			),
			array(
				'id' => 25,
				'name' => array('Шатер с москитной сеткой «Нейс»'),
				'cat_id' => 8,
				'brand_id' => 2,
				'article' => '95285',
				'anons' => array(
					"<p>Огромный шатер с москитной сеткой за 1 минуту защитит от солнца и комаров</p>",
				),
				'text' => array(
					"<p>Огромный тент-беседка с полуавтоматическим каркасом. Легко ставиться одним человеком за 1 минуту. Облегченная регулировка оттяжек со световозвращающей нитью. Москитная сетка по всем сторонам.</p>",
					''
				),
				'new' => 1,
				'images' => array(
					'86_shater-s-moskitnoy-setkoy-n.jpg',
					'87_shater-s-moskitnoy-setkoy-n.jpg',
					'88_shater-s-moskitnoy-setkoy-n.jpg',
					'89_shater-s-moskitnoy-setkoy-n.jpg',
					'90_shater-s-moskitnoy-setkoy-n.jpg',
				),
				'param' => array(
					2 => 3,
					4 => 4,
					6 => '1',
					8 => '100 х 20 х 20 см',
				),
				'rewrite' => 'shop/palatki/tenty-i-besedki/shater-s-moskitnoy-setkoy-neys',
				'price' => array(
					array(
						'price' => 8490,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '25',
			),
			array(
				'id' => 26,
				'name' => array('Беседка «Кейд»'),
				'cat_id' => 8,
				'brand_id' => 2,
				'article' => '95284',
				'anons' => array(
					"<p>Защита от солнца и комаров за 1 минуту</p>",
				),
				'text' => array(
					"<p>Высокий тент-беседка с полуавтоматическим каркасом, телескопические стойки. Легко ставиться одним человеком за 1 минуту. Облегченная регулировка оттяжек со световозвращающей нитью. Москитная сетка по всем сторонам.</p>",
					''
				),
				'images' => array(
					'91_besedka-keyd.jpg',
					'92_besedka-keyd.jpg',
					'93_besedka-keyd.jpg',
				),
				'param' => array(
					2 => 3,
					4 => 4,
					5 => '1',
					6 => '1',
					7 => '6.9',
					8 => '134 х 16 х 16 см',
					12 => 18,
				),
				'new' => 1,
				'rewrite' => 'shop/palatki/tenty-i-besedki/besedka-keyd',
				'price' => array(
					array(
						'price' => 6990,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '26',
			),
			array(
				'id' => 27,
				'name' => array('Палатка «Норма»'),
				'cat_id' => 8,
				'brand_id' => 2,
				'article' => '25653',
				'anons' => array(
					"<p>Упрощенный тент-беседка.</p>",
				),
				'text' => array(
					"<p>Два входа с разных сторон. Стенки из антимоскитной сетки дублируются непромокаемой тканью.</p>
<p>Палатка Норма по  комплектуется 2мя дугами, 2мя стойками и 10тью колышками + 6 растяжек</p>",
					''
				),
				'images' => array(
					'94_palatka-norma.jpg',
					'95_palatka-norma.jpg',
				),
				'param' => array(
					2 => 3,
					5 => '1',
					6 => '1',
					7 => '7.9',
					8 => '63 х 20 х 20 см',
				),
				'rewrite' => 'shop/palatki/tenty-i-besedki/palatka-norma',
				'price' => array(
					array(
						'price' => 7990,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '27',
			),
			array(
				'id' => 28,
				'name' => array('Тент «Москито»'),
				'cat_id' => 8,
				'brand_id' => 2,
				'article' => '25643',
				'anons' => array(
					"<p>Просто хорошая защита от комаров!</p>",
				),
				'text' => array(
					"<p>Два входа с разных сторон.</p>",
					''
				),
				'images' => array(
					'97_tent-moskito.jpg',
					'96_tent-moskito.jpg',
				),
				'param' => array(
					2 => 3,
					5 => '1',
					6 => '1',
					7 => '9.7',
					8 => '66 х 23 х 23 см',
					12 => 18,
				),
				'rewrite' => 'shop/palatki/tenty-i-besedki/tent-moskito',
				'price' => array(
					array(
						'price' => 6990,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '28',
			),
			array(
				'id' => 29,
				'name' => array('Палатка «Веранда комфорт v.2»'),
				'cat_id' => 8,
				'brand_id' => 2,
				'article' => '25613',
				'anons' => array(
					"<p>Легендарная каркасная тент-беседка</p>",
				),
				'text' => array(
					"<p>Два входа с разных сторон. Стенки  из антимоскитной сетки дублируются непромокаемой тканью. Светлый купол пропускает свет и не нагревается.</p>",
					''
				),
				'images' => array(
					'99_palatka-veranda-komfort-v2.jpg',
					'98_palatka-veranda-komfort-v2.jpg',
					'100_palatka-veranda-komfort-v2.jpg',
				),
				'param' => array(
					2 => 3,
					5 => '1',
					6 => '1',
					7 => '18.55',
					8 => '95 х 25 х 25 см',
					12 => 18,
				),
				'rewrite' => 'shop/palatki/tenty-i-besedki/palatka-veranda-komfort-v2',
				'price' => array(
					array(
						'price' => 9990,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '29',
			),
			array(
				'id' => 30,
				'name' => array('Палатка «Веранда v.2»'),
				'cat_id' => 8,
				'brand_id' => 2,
				'article' => '25603',
				'anons' => array(
					'<p>Легендарная каркасная тент-беседка. Два входа с разных сторон.</p>',
				),
				'text' => array(
					"<p>Стенки  из антимоскитной сетки дублируются непромокаемой тканью. Светлый купол пропускает свет и не нагревается.</p>",
					''
				),
				'images' => array(
					'102_palatka-veranda-v2.jpg',
					'101_palatka-veranda-v2.jpg',
					'100_palatka-veranda-komfort-v2.jpg',
				),
				'hit' => 1,
				'param' => array(
					2 => 3,
					5 => '1',
					6 => '1',
					7 => '15.45',
					8 => '95 х 25 х 25 см',
					12 => 18,
				),
				'rewrite' => 'shop/palatki/tenty-i-besedki/palatka-veranda-v2',
				'price' => array(
					array(
						'price' => 9490,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '30',
			),
			array(
				'id' => 31,
				'name' => array('Дачный душ «Приват v.2»'),
				'cat_id' => 9,
				'brand_id' => 2,
				'article' => '95281',
				'anons' => array(
					"<p>Тент с полуавтоматическим каркасом</p>",
				),
				'text' => array(
					'<p>Автоматическая палатка Приват - это палатка душ, палатка туалет, палатка для хранения припасов. Также применяется в качестве судейского места, наблюдательного пункта (ну вот совпадение такое:), как укрытие от дождя для стоящего или сидящего человека. Удобно при проведений акций или мероприятий на природе.</p>
<p>Дачный душ Приват v2 имеет полуавтоматический каркас. Раскладывается в одно движение! Процесс установки занимает минимум времени, фактически установить палатку - это так же просто, как открыть зонт! Не требует большой площадки для установки. Высота 198см позволяет заходить в полный рост, практически не нагибаясь. Два входа с двух сторон позволяют сориентировать палатку максимально удобно в лагере и обеспечить наилучший обзор за происходящим при использовании палатки в качестве судейской палатки на слетах и соревнованиях. Система оттяжек обеспечивает высокую ветроустойчивость даже в открытом поле! <br />Отличиями обновленной версии являются наличие крепления для душа и обновленный, более привлекательный дизайн, что для палатки туалета очень важно! Палатка Приват имеет два входа, перекладину для полотенца, проклеенные швы и облегченную регулировку оттяжек со световозвращающей нитью.<br />Автоматическая палатка Приват - это безальтернативный вариант для обустройства комфортного отдыха в кемпинге!</p>',
					''
				),
				'images' => array(
					'111_dachnyy-dush-privat-v2.jpg',
					'110_dachnyy-dush-privat-v2.jpg',
					'112_dachnyy-dush-privat-v2.jpg',
					'113_dachnyy-dush-privat-v2.jpg',
				),
				'param' => array(
					2 => 3,
					5 => '1',
					7 => '3.26',
					8 => '106 х 14,5 х 14,5 см',
				),
				'rewrite' => 'shop/palatki/mobilnye-bani/dachnyy-dush-privat-v2',
				'price' => array(
					array(
						'price' => 3990,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '31',
			),
			array(
				'id' => 32,
				'name' => array('Охотничья сумка «Ягдташ»'),
				'cat_id' => 2,
				'brand_id' => 3,
				'anons' => array(
					"<p>Классика всегда в моде!</p>",
				),
				'text' => array(
					"<p>Классическая охотничья сумка с двумя патронташами, выполненная из износостойкого материала.</p>",
					''
				),
				'images' => array(
					'114_okhotnichya-sumka-yagdtash.jpg',
					'115_okhotnichya-sumka-yagdtash.jpg',
					'116_okhotnichya-sumka-yagdtash.jpg',
				),
				'new' => 1,
				'param' => array(
					14 => '1.2',
					17 => '25 х 50 х 30',
					20 => 23,
				),
				'rewrite' => 'shop/ryukzaki/okhotnichya-sumka-yagdtash',
				'price' => array(
					array(
						'price' => 990,
						'param' => array(
							20 => 23,
						),
					),
				),
				'import_id' => '32',
			),
			array(
				'id' => 33,
				'name' => array('Охотничья сумка «Свамп»'),
				'article' => '95147',
				'cat_id' => 2,
				'brand_id' => 3,
				'anons' => array(
					"<p>Влагозащищенная сумка для охоты.</p>",
				),
				'text' => array(
					"<p>Ваше снаряжение останется в сохранности в любую погоду</p>",
					''
				),
				'images' => array(
					'117_okhotnichya-sumka-svamp.jpg'
				),
				'param' => array(
					14 => '1.8',
					17 => '30 х 50 х 30',
					20 => 24,
				),
				'rewrite' => 'shop/ryukzaki/okhotnichya-sumka-svamp',
				'price' => array(
					array(
						'price' => 1590,
						'param' => array(
							20 => 24,
						),
					),
				),
				'import_id' => '33',
			),
			array(
				'id' => 34,
				'name' => array('Рюкзак водонепроницаемый «Тритон 20»'),
				'article' => '95141',
				'cat_id' => 2,
				'brand_id' => 1,
				'anons' => array(
					"<p>Рюкзак с двумя отделениями для активного отдыха и занятий спортом</p>",
				),
				'text' => array(
					"<p>Компактный рюкзак для ношения спортивной одежды и всего необходимого для тренировок на свежем воздухе. Вам больше не нужно переживать по поводу содержимого рюкзака, если на велопрогулке застанет дождь – все молнии на рюкзаке водонепроницаемы</p>",
					''
				),
				'images' => array(
					'118_ryukzak-vodonepronitsaemyy-.jpg',
					'119_ryukzak-vodonepronitsaemyy-.jpg',
					'120_ryukzak-vodonepronitsaemyy-.jpg',
				),
				'new' => 1,
				'param' => array(
					15 => '20',
					17 => '30 x 12 x 44',
					20 => 25,
				),
				'rewrite' => 'shop/ryukzaki/ryukzak-vodonepronitsaemyy-triton-20',
				'price' => array(
					array(
						'price' => 2790,
						'param' => array(
							20 => 25,
						),
					),
				),
				'import_id' => '34',
			),
			array(
				'id' => 35,
				'name' => array('Рюкзак водонепроницаемый «Черепаха 25»'),
				'article' => '95140',
				'cat_id' => 2,
				'brand_id' => 1,
				'anons' => array(
					"<p>Рюкзак с одним отделением для занятий спортом и активного отдыха</p>",
				),
				'text' => array(
					"<p>Специально для динамичных людей создан этот яркий и стильный рюкзак. В нем помещается всё, что нужно взять с собой в спортзал или бассейн, покататься с друзьями на роликах, съездить на природу на велосипеде или просто весело провести время после учебы.</p>",
					''
				),
				'images' => array(
					'121_ryukzak-vodonepronitsaemyy-.jpg',
					'122_ryukzak-vodonepronitsaemyy-.jpg',
					'123_ryukzak-vodonepronitsaemyy-.jpg',
				),
				'param' => array(
					14 => '0.5',
					15 => '25',
					17 => '33 x 14 x 46',
					20 => 26,
				),
				'rewrite' => 'shop/ryukzaki/ryukzak-vodonepronitsaemyy-cherepakha-25',
				'price' => array(
					array(
						'price' => 2290,
						'param' => array(
							20 => 26,
						),
					),
				),
				'import_id' => '35',
			),
			array(
				'id' => 36,
				'name' => array('Рюкзак «Вело 12»'),
				'article' => '13643',
				'cat_id' => 2,
				'brand_id' => 1,
				'anons' => array(
					"<p>Продуманный велорюкзак с суперской эргономикой, очень насыщенным функционалом</p>",
				),
				'text' => array(
					"<p>Рюкзак - все включено! Есть все неоходимое для катания с ним на велосипеде или роликах, беге, скейте и т.д. Крепление для велошлема, отделение под гидратор, органайзер, пояс с карманами</p>",
					''
				),
				'images' => array(
					'124_ryukzak-velo-12.jpg',
					'125_ryukzak-velo-12.jpg',
					'126_ryukzak-velo-12.jpg',
				),
				'hit' => 1,
				'param' => array(
					14 => '0.7',
					15 => '12',
					16 => 1,
					17 => '43 х 27 х 12',
					20 => array(27, 28),
				),
				'rewrite' => 'shop/ryukzaki/ryukzak-velo-12',
				'price' => array(
					array(
						'price' => 1590,
						'param' => array(
							20 => 27,
						),
						'image_rel' => '124_ryukzak-velo-12.jpg',
					),
					array(
						'price' => 1590,
						'param' => array(
							20 => 28,
						),
						'image_rel' => '125_ryukzak-velo-12.jpg',
					),
				),
				'import_id' => '36',
			),
			array(
				'id' => 37,
				'name' => array('Рюкзак «Мэйт 40»'),
				'article' => '13563',
				'cat_id' => 2,
				'brand_id' => 1,
				'anons' => array(
					"<p>Практичный городской рюкзак</p>",
				),
				'text' => array(
					"<p>Идеальное решение для деловых людей, студентов и даже<br />школьников! Отделение под ноутбук, органайзер, специальное отделение для документов.</p>",
					''
				),
				'images' => array(
					'127_ryukzak-meyt-40.jpg',
					'128_ryukzak-meyt-40.jpg',
					'129_ryukzak-meyt-40.jpg',
				),
				'hit' => 1,
				'param' => array(
					14 => '1.15',
					15 => '40',
					17 => '50 х 40 х 20',
					20 => array(29, 23),
				),
				'rewrite' => 'shop/ryukzaki/ryukzak-meyt-40',
				'price' => array(
					array(
						'price' => 2190,
						'param' => array(
							20 => 29,
						),
						'image_rel' => '128_ryukzak-meyt-40.jpg',
					),
					array(
						'price' => 2190,
						'param' => array(
							20 => 23,
						),
						'image_rel' => '127_ryukzak-meyt-40.jpg',
					),
				),
				'import_id' => '37',
			),
			array(
				'id' => 38,
				'name' => array('Рюкзак «Стади 20»'),
				'article' => '13573',
				'cat_id' => 2,
				'brand_id' => 1,
				'anons' => array(
					"<p>Стильный городской рюкзак</p>",
				),
				'text' => array(
					"<p>Классический стиль, широкий функционал: карабин для ключей, карман для очков, отделение под ноутбук, органайзер</p>",
					''
				),
				'images' => array(
					'130_ryukzak-stadi-20.jpg',
					'131_ryukzak-stadi-20.jpg',
				),
				'new' => 1,
				'param' => array(
					14 => '0.7',
					15 => '20',
					17 => '48 х 37 х 16',
					20 => 29,
				),
				'rewrite' => 'shop/ryukzaki/ryukzak-stadi-20',
				'price' => array(
					array(
						'price' => 1390,
						'param' => array(
							20 => 29,
						),
					),
				),
				'import_id' => '38',
			),
			array(
				'id' => 39,
				'name' => array('Рюкзак «Блэк Спайдер 30»'),
				'article' => '13452',
				'cat_id' => 2,
				'brand_id' => 1,
				'anons' => array(
					"<p>Оригинальный рюкзак с регулируемым углом между лямками</p>",
				),
				'text' => array(
					"<p>Стильный рюкзак для занятий спортом и активного отдыха. Для комфорта при повседневневном ношении, предусмотрена новая удобная система подушек с регулируемыми лямками и съемной вставкой для жесткости спинки. Приятным дополнением к основному отделению служит органайзер, карабин для ключей и отделение для гидратора. Всем необходимым мелочам найдется место в кармане на фасадной части, в двух боковых эластичных карманах, карманах на лямке и поясе рюкзака</p>",
					''
				),
				'images' => array(
					'132_ryukzak-blek-spayder-30.jpg',
					'133_ryukzak-blek-spayder-30.jpg',
					'134_ryukzak-blek-spayder-30.jpg',
					'135_ryukzak-blek-spayder-30.jpg',
				),
				'hit' => 1,
				'param' => array(
					14 => '1.06',
					15 => '30',
					16 => 1,
					17 => '51 х 31 х 18',
					19 => 1,
					20 => 29,
				),
				'rewrite' => 'shop/ryukzaki/ryukzak-blek-spayder-30',
				'price' => array(
					array(
						'price' => 2490,
						'param' => array(
							20 => 29,
						),
					),
				),
				'import_id' => '39',
			),
			array(
				'id' => 40,
				'name' => array('Рюкзак «Слалом 40 v.2»'),
				'article' => '13523',
				'cat_id' => 2,
				'brand_id' => 1,
				'text' => array(
					"<p>Если все, что нужно ежедневно носить с собой, не помещается в обычный рюкзак, то «Слалом 40 V2» и «Слалом 55 V2» специально для вас. Два вместительных отделения можно уменьшить боковыми стяжками или наоборот, если что-то не поместилось внутри, навесить снаружи на узлы крепления. Для удобства переноски тяжелого груза, на спинке предусмотрена удобная система подушек Air Mesh с полностью отстегивающимся поясным ремнем.</p>",
					''
				),
				'images' => array(
					'136_ryukzak-slalom-40-v2.jpg',
					'137_ryukzak-slalom-40-v2.jpg',
				),
				'new' => 1,
				'param' => array(
					14 => '0.58',
					15 => '40',
					16 => 1,
					17 => '48 х 30 х 22',
				),
				'rewrite' => 'shop/ryukzaki/ryukzak-slalom-40-v2',
				'price' => array(
					array(
						'price' => 1790,
					),
				),
				'import_id' => '40',
			),
			array(
				'id' => 41,
				'name' => array('Охотничья сумка «Трофи»'),
				'article' => '95128',
				'cat_id' => 2,
				'brand_id' => 3,
				'anons' => array(
					"<p>Когда хочется взять больше</p>",
				),
				'text' => array(
					"<p>Очень вместительная охотничья сумка с большим количеством карманов и просторным центральным отделением. Благодаря полужестким стенкам, ваше снаряжение останется в сохранности!</p>",
					''
				),
				'images' => array(
					'138_okhotnichya-sumka-trofi.jpg'
				),
				'new' => 1,
				'param' => array(
					14 => '1.2',
					17 => '25 х 50 х 30',
					20 => 23,
				),
				'rewrite' => 'shop/ryukzaki/okhotnichya-sumka-trofi',
				'price' => array(
					array(
						'price' => 1690,
						'param' => array(
							20 => 23,
						),
					),
				),
				'import_id' => '41',
			),
			array(
				'id' => 42,
				'name' => array('Баул водонепроницаемый «Кашалот 45»'),
				'article' => '95148',
				'cat_id' => 2,
				'brand_id' => 1,
				'anons' => array(
					"<p>Не задумываться о чистоте и сухости вещей при поездке на природу поможет наша сумка</p>",
				),
				'text' => array(
					"<p><strong>Многофункциональный баул-сумка</strong><br />Если у вас открытый джип или квадроцикл, то не задумываться о чистоте и сухости вещей при поездке на природу поможет наша сумка. Не смогли проехать к заветной полянке? Баул можно транспортировать за ручки и ремень, как сумку или за отстёгивающиеся лямки, как рюкзак. А можно просто тащить по траве за боковую усиленную ручку. Для 100%-ной уверенности в герметичности сумки, вход в основное отделение снабжен водонепроницаемой молнией.</p>",
					''
				),
				'images' => array(
					'139_baul-vodonepronitsaemyy-ka.jpg',
					'140_baul-vodonepronitsaemyy-ka.jpg',
					'141_baul-vodonepronitsaemyy-ka.jpg',
					'142_baul-vodonepronitsaemyy-ka.jpg',
				),
				'action' => 1,
				'param' => array(
					14 => '0.9',
					15 => '45',
					16 => 1,
					17 => '61 x 30 x 28',
					20 => 32,
				),
				'rewrite' => 'shop/ryukzaki/baul-vodonepronitsaemyy-kashalot-45',
				'price' => array(
					array(
						'price' => 3290,
						'param' => array(
							20 => 32,
						),
					),
				),
				'import_id' => '42',
			),
			array(
				'id' => 43,
				'name' => array('Рюкзак «Дельта 65 V2»'),
				'article' => '12453',
				'cat_id' => 2,
				'brand_id' => 1,
				'anons' => array(
					"<p>Все необходимое снаряжение, и даже немного больше, по местится в «Дельту»</p>",
				),
				'text' => array(
					"<p><strong>Простой рюкзак для трекинга</strong><br />Рюкзак создавался под девизом «Все гениальное просто». Все необходимое снаряжение, и даже немного больше, поместится в увеличенное по диаметру основное отделение и три вместительных кармана, расположенных на фронтальной и боковых частях рюкзака. Комфорт при переноске обеспечивают мягкие подушки Air Mesh, играющие вместе с поясом роль подвесной системы. Если вам во время перехода потребуется снять верхнюю одежду, то её можно удобно нести перекинув через боковую стяжку рюкзака и быстро одеть если станет прохладнее. Вы не испытаете неудобств с погрузкой рюкзака в поезд или автомобиль благодаря удобным транспортировочным ручкам. На концах боковых стяжек имеются липучки для закрепления излишков стропы.</p>",
					''
				),
				'images' => array(
					'143_ryukzak-delta-65-v2.jpg',
					'144_ryukzak-delta-65-v2.jpg',
					'145_ryukzak-delta-65-v2.jpg',
				),
				'new' => 1,
				'param' => array(
					15 => '15',
					16 => 1,
					20 => array(28, 31),
				),
				'rewrite' => 'shop/ryukzaki/ryukzak-delta-65-v2',
				'price' => array(
					array(
						'price' => 2690,
						'param' => array(
							20 => 28,
						),
						'image_rel' => '144_ryukzak-delta-65-v2.jpg',
					),
					array(
						'price' => 2690,
						'param' => array(
							20 => 31,
						),
						'image_rel' => '143_ryukzak-delta-65-v2.jpg',
					),
				),
				'import_id' => '43',
			),
			array(
				'id' => 44,
				'name' => array('Рюкзак «Альфа 65» v.2'),
				'article' => '95311',
				'cat_id' => 2,
				'brand_id' => 1,
				'anons' => array(
					"<p>Продуманная двухкамерная модель со складными карманами по бокам и плавающим клапаном</p>",
				),
				'text' => array(
					"<p>«Альфа» - первая буква в алфавите настоящего туриста! Надежный в походах и путешествиях. Прочность конструкции проверена временем. Продуманная двухкамерная модель со складными карманами по бокам и плавающим клапаном, позволяющим изменять объем рюкзака, имеет все необходимое для навески дополнительного снаряжения. Гермочехол упакован в специальном кармане на дне рюкзака.</p>",
					''
				),
				'images' => array(
					'146_ryukzak-alfa-65-v2.jpg',
					'147_ryukzak-alfa-65-v2.jpg',
					'148_ryukzak-alfa-65-v2.jpg',
				),
				'action' => 1,
				'param' => array(
					14 => '2.35',
					15 => '65',
					16 => 1,
					19 => 1,
					20 => array(31, 33),
				),
				'rewrite' => 'shop/ryukzaki/ryukzak-alfa-65-v2',
				'price' => array(
					array(
						'price' => 3690,
						'param' => array(
							20 => 31,
						),
						'image_rel' => '146_ryukzak-alfa-65-v2.jpg',
					),
					array(
						'price' => 3690,
						'param' => array(
							20 => 33,
						),
						'image_rel' => '147_ryukzak-alfa-65-v2.jpg',
					),
				),
				'import_id' => '44',
			),
			array(
				'id' => 45,
				'name' => array('Рюкзак «Юкон 95 v.2»'),
				'article' => '11203',
				'cat_id' => 2,
				'brand_id' => 1,
				'anons' => array(
					"<p>Универсальный рюкзак для горного туризма</p>",
				),
				'text' => array(
					"<p>Вам будет очень удобно двигаться по пересеченной местности с новой облегченной подвесной системой ABS2, равномерно распределяющей вес на плечи и пояс и уменьшающей нагрузку на позвоночник. Для большего удобства крепления горного инвентаря разработана новая система навески. Если вам нужно что-то достать со дна рюкзака воспользуйтесь удобным нижним входом. Вам некуда положить карту чтобы она не помялась? В рюкзаке Юкон имеется специальный карман для карты и документов на передней части рюкзака. В горах погода переменчива, может внезапно пойти дождь, но ваши вещи всегда будут оставаться сухими благодаря гермочехлу, расположенному в специальном кармане в дне рюкзака. На концах боковых стяжек имеются липучки для закрепления излишков стропы</p>",
					''
				),
				'images' => array(
					'149_ryukzak-yukon-95-v2.jpg',
					'150_ryukzak-yukon-95-v2.jpg',
					'151_ryukzak-yukon-95-v2.jpg',
				),
				'param' => array(
					14 => '2.3',
					15 => '95',
					16 => 1,
					17 => '100 х 32 х 30',
					18 => 1,
					20 => array(28, 33),
				),
				'rewrite' => 'shop/ryukzaki/ryukzak-yukon-95-v2',
				'price' => array(
					array(
						'price' => 4490,
						'param' => array(
							20 => 28,
						),
						'image_rel' => '149_ryukzak-yukon-95-v2.jpg',
					),
					array(
						'price' => 4490,
						'param' => array(
							20 => 33,
						),
						'image_rel' => '150_ryukzak-yukon-95-v2.jpg',
					),
				),
				'import_id' => '45',
			),
			array(
				'id' => 46,
				'name' => array('Рюкзак 120 л Абакан v.2'),
				'article' => '11223',
				'cat_id' => 2,
				'brand_id' => 1,
				'anons' => array(
					"<p>Модернизация популярной модели - переработан дизайн, используем очень прочную ткань (600D)</p>",
				),
				'text' => array(
					"<p>Специалист по переноске тяжелых грузов!</p><p>Рюкзак с каркасом из 2х металлических плат. </p><p>Модернизация популярной модели - переработали дизайн, используем очень прочную ткань (600D) + облегченную ткань (300D)</p><p>Рюкзак на 120 литров незаменим в многодневных маршрутах с большим количеством багажа. </p><p>Вместительный и прочный. Дополнительно оснащён большими съёмными боковыми карманами на молнии.  </p><p>Кроме многих продуманных деталей, система с регулируемым поясным ремнем делает этот рюкзак очень удобным, когда дело касается переноски тяжелых грузов. Система навески V.1 с воздушной сеткой «Air Mesh» заботится о комфорте. </p><p>Отдельное нижнее отделение обеспечивает быстрый доступ к снаряжению. Специальная система позволяет надежно укрепить на фасаде ледоруб или трекинговые палки. Боковые оттяжки пояса помогут точно подогнать посадку рюкзака на пояс. Для более удобной погрузки собранного рюкзака ручки для переноски обшиты тканью.</p>",
					''
				),
				'images' => array(
					'156_ryukzak-120-l-abakan-v2.jpg',
					'155_ryukzak-120-l-abakan-v2.jpg',
					'152_ryukzak-120-l-abakan-v2.jpg',
					'153_ryukzak-120-l-abakan-v2.jpg',
					'154_ryukzak-120-l-abakan-v2.jpg',
				),
				'action' => 1,
				'param' => array(
					14 => '2.58',
					15 => '120',
					16 => 1,
					17 => '102 х 39 х 27',
					19 => 1,
					20 => array(27, 28),
				),
				'rewrite' => 'shop/ryukzaki/ryukzak-120-l-abakan-v2',
				'price' => array(
					array(
						'price' => 5090,
						'param' => array(
							20 => 27,
						),
						'image_rel' => '156_ryukzak-120-l-abakan-v2.jpg',
					),
					array(
						'price' => 5090,
						'param' => array(
							20 => 28,
						),
						'image_rel' => '155_ryukzak-120-l-abakan-v2.jpg',
					),
				),
				'import_id' => '46',
			),
			array(
				'id' => 47,
				'name' => array('Каркасный рюкзак «Юкон 115 v.2»'),
				'article' => '11213',
				'cat_id' => 2,
				'brand_id' => 1,
				'anons' => array(
					"<p>Универсальный рюкзак для горного туризма</p>",
				),
				'text' => array(
					"<p>Вам будет очень удобно двигаться по пересеченной местности с новой облегченной подвесной системой ABS2, равномерно распределяющей вес на плечи и пояс и уменьшающей нагрузку на позвоночник. Для большего удобства крепления горного инвентаря разработана новая система навески. Если вам нужно что-то достать со дна рюкзака воспользуйтесь удобным нижним входом. Вам некуда положить карту чтобы она не помялась? В рюкзаке Юкон имеется специальный карман для карты и документов на передней части рюкзака. В горах погода переменчива, может внезапно пойти дождь, но ваши вещи всегда будут оставаться сухими благодаря гермочехлу, расположенному в специальном кармане в дне рюкзака. На концах боковых стяжек имеются липучки для закрепления излишков стропы.</p>",
					''
				),
				'images' => array(
					'159_karkasnyy-ryukzak-yukon-115-v2.jpg',
					'157_karkasnyy-ryukzak-yukon-115-v2.jpg',
					'158_karkasnyy-ryukzak-yukon-115-v2.jpg',
					'160_karkasnyy-ryukzak-yukon-115-v2.jpg',
					'161_karkasnyy-ryukzak-yukon-115-v2.jpg',
				),
				'new' => 1,
				'param' => array(
					14 => '2.5',
					15 => '115',
					16 => 1,
					17 => '105 х 35 х 24',
					19 => 1,
					20 => array(27, 28),
				),
				'rewrite' => 'shop/ryukzaki/karkasnyy-ryukzak-yukon-115-v2',
				'price' => array(
					array(
						'price' => 4990,
						'param' => array(
							20 => 27,
						),
						'image_rel' => '159_karkasnyy-ryukzak-yukon-115-v2.jpg',
					),
					array(
						'price' => 4990,
						'param' => array(
							20 => 28,
						),
						'image_rel' => '157_karkasnyy-ryukzak-yukon-115-v2.jpg',
					),
				),
				'import_id' => '47',
			),
			array(
				'id' => 51,
				'name' => array('Пуховый спальный мешок «Альбаган»'),
				'article' => '31080',
				'cat_id' => 3,
				'brand_id' => 1,
				'anons' => array(
					"<p>Пуховой спальный мешок-одеяло с капюшоном</p>",
				),
				'text' => array(
					"<p>Практичная модель для теплого сезона в туризме и просто отдыха на природе. Возможна стыковка двух спальников в одно спальное пространство. Можно расстегнуть молнию и использовать спальник как обыкновенное одеяло. Комплектуется компрессионным мешком и мешком для хранения. Разумное сочетание цены и качества.</p>",
					''
				),
				'images' => array(
					'169_pukhovyy-spalnyy-meshok-al.jpg',
					'170_pukhovyy-spalnyy-meshok-al.jpg',
					'171_pukhovyy-spalnyy-meshok-al.jpg',
				),
				'action' => 1,
				'param' => array(
					2 => array(3, 10),
					22 => '0.5',
					23 => '-20',
					24 => '1.7',
					25 => 1,
				),
				'rewrite' => 'shop/spalniki/pukhovyy-spalnyy-meshok-albagan',
				'price' => array(
					array(
						'price' => 6990,
						'param' => array(
							2 => 3,
						),
						'image_rel' => '170_pukhovyy-spalnyy-meshok-al.jpg',
					),
					array(
						'price' => 6990,
						'param' => array(
							2 => 10,
						),
						'image_rel' => '169_pukhovyy-spalnyy-meshok-al.jpg',
					),
				),
				'import_id' => '51',
			),
			array(
				'id' => 53,
				'name' => array('Сумка для рыбалки «Кейс»'),
				'article' => '95133',
				'cat_id' => 2,
				'brand_id' => 5,
				'anons' => array(
					"<p>Организуй порядок</p>",
				),
				'text' => array(
					"<p>Очень удобная сумка для активных рыбаков, когда все приманки  должны быть под рукой!</p>",
					''
				),
				'images' => array(
					'174_sumka-dlya-rybalki-keys.jpg'
				),
				'no_buy' => 1,
				'param' => array(
					14 => '1.4',
					17 => '29 x 20 x 20',
					20 => 34,
				),
				'rewrite' => 'shop/ryukzaki/sumka-dlya-rybalki-keys',
				'price' => array(
					array(
						'price' => 1590,
						'param' => array(
							20 => 34,
						),
					),
				),
				'import_id' => '53',
			),
			array(
				'id' => 48,
				'name' => array('Рюкзак «Бекас 55»'),
				'article' => '14157',
				'cat_id' => 2,
				'brand_id' => 1,
				'anons' => array(
					"<p>Универсальный рюкзак для ходовой охоты и рыбалки</p>",
				),
				'text' => array(
					"<p>Легкий, компактный рюкзак для выездов на охоту или рыбалку. Пятьдесят пять литров полезного объема легко разместят запас одежды, комплект снаряжения, посуду и питание. Компрессионные стяжки с застежками «Fast» с каждой стороны позволяют затянуть снаряжение в боковых карманах регулируемого объема, а в карман из сетки можно поместить, например, дождевик. Поясная стропа выполнена в съемном варианте. Вертикальная смягчающая вставка, дополнительно придает жесткость спинке рюкзака.</p>",
					''
				),
				'images' => array(
					'162_ryukzak-bekas-55.jpg',
					'163_ryukzak-bekas-55.jpg',
				),
				'hit' => 1,
				'param' => array(
					14 => '0.9',
					15 => '55',
					17 => '50 х 33 х 37',
					20 => 34,
				),
				'rewrite' => 'shop/ryukzaki/ryukzak-bekas-55',
				'rel' => 54,
				'price' => array(
					array(
						'price' => 1290,
						'param' => array(
							20 => 34,
						),
					),
				),
				'import_id' => '48',
			),
			array(
				'id' => 50,
				'name' => array('Рюкзак «Динго 75» км'),
				'article' => '14177',
				'cat_id' => 2,
				'brand_id' => 1,
				'anons' => array(
					"<p>Отлично подойдет для переноски вещей во время заброски в лагерь.</p>",
				),
				'text' => array(
					"<p> Эргономичные плечевые лямки с большим ходом регулировок, удобный поясной ремень, в совокупности образуют надежную систему переноски, обеспечивающую устойчивость и правильное распределение нагрузки. Наличие горизонтальных компрессионных лямок, фиксированного клапана с карманом, вместительного кармана на молнии с фронтальной части рюкзака, боковых карманов для длинномерных предметов обеспечат комфортное пользование рюкзаком. Подойдет по расцветке к костюмам «Егерь», «Беркут», «Полигон», «Форест» и «Хантер».</p>",
					''
				),
				'images' => array(
					'168_ryukzak-dingo-75-km.jpg',
					'167_ryukzak-dingo-75-km.jpg',
					'166_ryukzak-dingo-75-km.jpg',
				),
				'no_buy' => 1,
				'param' => array(
					14 => '1',
					15 => '75',
					16 => 1,
					17 => '80 х 45 х 32',
					20 => 35,
				),
				'rewrite' => 'shop/ryukzaki/ryukzak-dingo-75-km',
				'price' => array(
					array(
						'price' => 1390,
						'param' => array(
							20 => 35,
						),
					),
				),
				'import_id' => '50',
			),
			array(
				'id' => 52,
				'name' => array('Рюкзак охотничий «Тактика 32»'),
				'article' => '95126',
				'cat_id' => 2,
				'brand_id' => 3,
				'anons' => array(
					"<p>Стратегия и тактика вашей охоты</p>",
				),
				'text' => array(
					"<p>Тактический рюкзак для пешей охоты. Два больших отделения для снаряжения, множество карманов для всяких мелочей, стропы для внешней навески.</p>",
					''
				),
				'images' => array(
					'172_ryukzak-okhotnichiy-taktika-32.jpg',
					'173_ryukzak-okhotnichiy-taktika-32.jpg',
				),
				'new' => 1,
				'param' => array(
					14 => '1',
					15 => '32',
					16 => 1,
					17 => '55 х 35 х 18',
					20 => 23,
				),
				'rel' => 54,
				'rewrite' => 'shop/ryukzaki/ryukzak-okhotnichiy-taktika-32',
				'price' => array(
					array(
						'price' => 2490,
						'param' => array(
							20 => 23,
						),
					),
				),
				'import_id' => '52',
			),
			array(
				'id' => 55,
				'name' => array('Спальный мешок одеяло с подголовником «Карелия 450»'),
				'article' => '95214',
				'cat_id' => 3,
				'anons' => array(
					"<p>Спальный мешок для походов в прохладную погоду +10 /-5 градусов.</p>",
				),
				'text' => array(
					"<p>Спальный мешок «Осень – Зима» для походов в прохладную погоду +10/-5. Для большего комфорта комплектуется утягивающимся подголовником. В материале используется утеплитель с улучшенными тепловыми характеристиками Thermofibre-S-Pro. Также имеются две петли в нижней части мешка для сушки и проветривания. При своем весе в 1,9 кг также является отличным вариантом в пеших походах, а наличие компресинного мешка облегчает транспортировку.</p>
<p>Спальный мешок с синтетическим наполнителем. Для большего комфорта мешок выпускается с утягивающимся подголовником. Для сушки и проветвивания изделия предусмотрены две петли в нижней части мешка.</p>",
					''
				),
				'images' => array(
					'177_spalnyy-meshok-odeyalo-s-pod.jpg',
					'178_spalnyy-meshok-odeyalo-s-pod.jpg',
					'179_spalnyy-meshok-odeyalo-s-pod.jpg',
					'180_spalnyy-meshok-odeyalo-s-pod.jpg',
				),
				'param' => array(
					24 => '1.9',
					23 => '-5',
					22 => '10',
					21 => 36,
					2 => array(10, 11, 38),
				),
				'rewrite' => 'shop/spalniki/spalnyy-meshok-odeyalo-s-podgolovnikom-kareliya-45',
				'price' => array(
					array(
						'price' => 1990,
						'param' => array(
							2 => 10,
						),
						'image_rel' => '177_spalnyy-meshok-odeyalo-s-pod.jpg',
					),
					array(
						'price' => 1990,
						'param' => array(
							2 => 11,
						),
						'image_rel' => '179_spalnyy-meshok-odeyalo-s-pod.jpg',
					),
					array(
						'price' => 1990,
						'param' => array(
							2 => 38,
						),
						'image_rel' => '178_spalnyy-meshok-odeyalo-s-pod.jpg',
					),
				),
				'import_id' => '55',
			),
			array(
				'id' => 56,
				'name' => array('Спальный мешок «Одеяло +15 С»'),
				'article' => '95217',
				'cat_id' => 3,
				'brand_id' => 4,
				'anons' => array(
					"<p>Многофункциональное изделие  - возможность использования в качестве спального мешка</p>",
				),
				'text' => array(
					'<p>Спальный мешок Одеяло +15 - один из самых недорогих, летний спальный мешок для самого экономного туриста. Основное отличие от остальных спальных мешков категории одеяло - это конечно цена! Даже отсутствие в комплекте компрессионного мешка обусловлено исключительно стремлением сделать цену спальника для комфортного отдыха на природе максимально доступной. Несмотря на низкую стоимость, спальный мешок Одеяло +15 отвечает всем требованиям к современным спальникам, обеспечивает достойный, комфортный отдых и максимальную простоту в обслуживании. Материал известного утеплителя Холофайбер проверен многолетним опытом эксплуатации спальников более ранних моделей. Спальный мешок Одеяло +15 - это «дежурный» спальник для дачи, его можно постоянно возить в багажнике авто на всякий «пожарный» случай, можно укрыть нежданных гостей расстегнув спальник и превратив его в полноценное одеяло.</p>
<p>Вывод: спальник Одеяло +15 - простой, надежный, дешевый спальный мешок на все случаи жизни</p>',
				),
				'images' => array(
					'181_spalnyy-meshok-odeyalo-15-s.jpg',
					'182_spalnyy-meshok-odeyalo-15-s.jpg',
					'183_spalnyy-meshok-odeyalo-15-s.jpg',
				),
				'new' => 1,
				'param' => array(
					24 => '1',
					23 => '6',
					22 => '15',
					21 => 40,
					2 => array(10, 3),
				),
				'rewrite' => 'shop/spalniki/spalnyy-meshok-odeyalo-15-s',
				'price' => array(
					array(
						'price' => 920,
						'param' => array(
							2 => 10,
						),
						'image_rel' => '181_spalnyy-meshok-odeyalo-15-s.jpg',
					),
					array(
						'price' => 920,
						'param' => array(
							2 => 3,
						),
						'image_rel' => '182_spalnyy-meshok-odeyalo-15-s.jpg',
					),
				),
				'import_id' => '56',
			),
			array(
				'id' => 57,
				'name' => array('Детский спальный мешок «Пионер 150»'),
				'article' => '33120',
				'cat_id' => 3,
				'brand_id' => 1,
				'anons' => array(
					"<p>Детский спальный мешок для теплой погоды</p>",
				),
				'text' => array(
					"<p>Для тех кто любит отдыхать летом. Наша самая простая модель спального мешка. В нем просто нечему ломаться!</p>
<p>Длина спальника - всего 180 см, что позволяет сэкономить на весе для детей и людей низкого роста. </p>",
					''
				),
				'images' => array(
					'185_detskiy-spalnyy-meshok-pio.jpg',
					'184_detskiy-spalnyy-meshok-pio.jpg',
				),
				'param' => array(
					24 => '0.07',
					23 => '15',
					22 => '25',
					2 => 10,
				),
				'rewrite' => 'shop/spalniki/detskiy-spalnyy-meshok-pioner-150',
				'price' => array(
					array(
						'price' => 790,
						'param' => array(
							2 => 10,
						),
					),
				),
				'import_id' => '57',
			),
			array(
				'id' => 58,
				'name' => array('Спальный мешок одеяло «Валдай 450»'),
				'article' => '95211',
				'cat_id' => 3,
				'brand_id' => 1,
				'anons' => array(
					"<p>Комфортный летний спальник, трансформирующийся при необходимостии в теплое одеяло</p>",
				),
				'text' => array(
					"<p>Комфортный летний спальный мешок, трансформирующийся при необходимости в теплое одеяло. Имеет двухзамковую молнию, с помощью которой можно соединить два спальных мешка вместе. В спальнике используется три слоя утеплителя Thermofibre-S-Pro, который имеет улучшенные температурные характеристики, благодаря которым комфортно себя чувствуешь при низких температурах. Спальник практично использовать при температурах +10/0. Благодаря своему весу в 1,8 кг достаточно легко носится, и является отличным вариантом для использования в походах с ночевкой. Также этот спальный мешок является хорошим выбором по привлекательной цене для студентов, начинающих туристов, новичков.</p>
<p>Спальный мешок серии «Весна-Осень», конструкции одеяло и синтетическим наполнителем. Температурный режим +10/-5 &deg;С. Для уменьшения веса изготавливается без подголовника. Благодаря двухзамковой молнии, имеется возможность состегнуть два спальника в один двойной. Для сушки и проветвивания изделия предусмотрены две петли в нижней части мешка. Компрессионный мешок в комплекте.</p>",
					''
				),
				'images' => array(
					'186_spalnyy-meshok-odeyalo-vald.jpg',
					'187_spalnyy-meshok-odeyalo-vald.jpg',
					'188_spalnyy-meshok-odeyalo-vald.jpg',
					'189_spalnyy-meshok-odeyalo-vald.jpg',
				),
				'new' => 1,
				'param' => array(
					24 => '1.8',
					23 => '-5',
					22 => '10',
					21 => 36,
					2 => array(10, 11, 38),
				),
				'rewrite' => 'shop/spalniki/spalnyy-meshok-odeyalo-valday-450',
				'price' => array(
					array(
						'price' => 1690,
						'param' => array(
							2 => 10,
						),
						'image_rel' => '187_spalnyy-meshok-odeyalo-vald.jpg',
					),
					array(
						'price' => 1690,
						'param' => array(
							2 => 11,
						),
						'image_rel' => '186_spalnyy-meshok-odeyalo-vald.jpg',
					),
					array(
						'price' => 1690,
						'param' => array(
							2 => 38,
						),
						'image_rel' => '188_spalnyy-meshok-odeyalo-vald.jpg',
					),
				),
				'import_id' => '58',
			),
			array(
				'id' => 59,
				'name' => array('Спальный мешок увеличенный одеяло с подголовником «Карелия 300 XL»'),
				'article' => '95213',
				'cat_id' => 3,
				'brand_id' => 1,
				'anons' => array(
					"<p>Спальный мешок, позволяющий комфортно чувствовать себя в нем высоким людям.</p>",
				),
				'text' => array(
					'<p>Большой спальник Карелия 300 XL специально для высоких людей. Длина 245см при ширине 95см позволяют отдохнуть с комфортом даже баскетболисту! Для особого удобства имеются утягивающийся подголовник и разъемная молния для возможности состегивания двух спальников. Спальник Карелия 300 XL не требует особого ухода. Компактно упаковывается в компрессионный мешок (входит в стоимость спальника) для транспортировки и хранения. Применение современного утеплителя Thermofibre-S-Pro, ткани верха из Polyester 100%, наличие специальных петель для сушки и проветривания сделали эту модель простым в обслуживании и использовании, комфортным и суперпопулярным спальным мешком для начинающих туристов и любителей отдыха на природе в теплое время года!</p>
<p>Увеличенный спальный мешок серии «Лето» конструкции одеяло и синтетическим наполнителем. Температурный режим +15 /0 градусов. Для большего комфорта мешок выпускается с утягивающимся подголовником. Для сушки и проветвивания изделия предусмотрены две петли в нижней части мешка.</p>',
					''
				),
				'images' => array(
					'190_spalnyy-meshok-uvelichennyy.jpg',
					'191_spalnyy-meshok-uvelichennyy.jpg',
					'192_spalnyy-meshok-uvelichennyy.jpg',
					'193_spalnyy-meshok-uvelichennyy.jpg',
				),
				'new' => 1,
				'param' => array(
					24 => '2',
					22 => '15',
					21 => 36,
					2 => array(10, 11, 38),
				),
				'rewrite' => 'shop/spalniki/spalnyy-meshok-uvelichennyy-odeyalo-s-podgolovniko',
				'price' => array(
					array(
						'price' => 1990,
						'param' => array(
							2 => 10,
						),
						'image_rel' => '190_spalnyy-meshok-uvelichennyy.jpg',
					),
					array(
						'price' => 1990,
						'param' => array(
							2 => 11,
						),
						'image_rel' => '192_spalnyy-meshok-uvelichennyy.jpg',
					),
					array(
						'price' => 1990,
						'param' => array(
							2 => 38,
						),
						'image_rel' => '191_spalnyy-meshok-uvelichennyy.jpg',
					),
				),
				'import_id' => '59',
			),
			array(
				'id' => 60,
				'name' => array('Спальный мешок «Йол»'),
				'article' => '34043',
				'cat_id' => 3,
				'brand_id' => 2,
				'text' => array(
					"<p>Все мы оказывались в такой ситуации, когда одному в спальнике спать совсем холодно, совсем не хочется и в конце концов совсем не интересно. Что мы делали? Брали два спальных мешка, состегивали и крепко обнявшись вдвоем или втроем, думая о том, что бы не разошлась молния - засыпали. Но это хорошо если молнии в спальниках друг к другу подходили, а если нет, тогда лежат все одиноки, каждый в своем углу и дрожат от холода.<br />Теперь, все эти проблемы решает, спальник для двоих Йол. Это новинка в мире спальников, раньше такого в продаже было. Больше не надо выдумывать как бы состегнуть спальники и удобно ли будет в них спать. В двухместном спальном мешке Йол<br />комфортно разместится любая пара. В нем вы можете спокойно спать,обнявшись со своей второй половинкой, вам будет тепло и уютно.В таком, двухместном спальнике, может разместится даже семья с ребенком, ведь его ширина в застегнутом виде 140 см. С двухместным спальником Йол, вам будет удобно в любой поездке. Вы можете использовать его как плед - на пикнике, как покрывало - на даче или в палатке. Так же, спальник для двоих Йол, может стать прекрасным одеялом для гостей - стоит только положить его в пододеяльник и походный плед превращается в теплое одеяло для двоих. Спальник для пары Йол, можно использовать круглый год - летом как покрывало для пикника, а с осенью, зимой и весной, как теплый семейный спальный мешок. И самое приятное в спальном мешке Йол -это внутренняя его сторона, она сделана из фланели, очень приятная к коже. В таком спальном мешке ваш сон будет крепкий, как и обьятия любимого чеорвека рядом.</p>
<p>Так чем же хорош спальник для двоих Йол?</p>
<p>-тем,что это спальник для двоих<br />-тем, что он согреет всю семью<br />-тем,что он может быть пледом, одеялом, покрывалом<br />-тем, что он служит круглый год<br />-очень приятная внутренняя ткань.</p>",
				),
				'images' => array(
					'195_spalnyy-meshok-yol.jpg',
					'196_spalnyy-meshok-yol.jpg',
					'194_spalnyy-meshok-yol.jpg',
				),
				'new' => 1,
				'param' => array(
					24 => '3.4',
					23 => '-18',
					21 => 41,
					2 => 42,
				),
				'rewrite' => 'shop/spalniki/spalnyy-meshok-yol',
				'price' => array(
					array(
						'price' => 5490,
						'param' => array(
							2 => 42,
						),
					),
				),
				'import_id' => '60',
			),
			array(
				'id' => 49,
				'name' => array('Рюкзак «Медведь 100» V2'),
				'article' => '14383',
				'cat_id' => 2,
				'brand_id' => 3,
				'text' => array(
					"<p>Регулировка подвесной системы рюкзаков этой модели максимально проста и не требует времени на подгонку. Широкий поясной ремень отлично фиксируется на бедрах, принимая на себя до 80 процентов веса. Удобный карман на фронтальной части и боковые кармашки для длинномерных предметов.</p>",
				),
				'images' => array(
					'164_ryukzak-medved-100-v2.jpg',
					'165_ryukzak-medved-100-v2.jpg',
				),
				'action' => 1,
				'param' => array(
					14 => '1.3',
					15 => '100',
					16 => 1,
					17 => '95 х 32 х 29',
					20 => 34,
				),
				'rewrite' => 'shop/ryukzaki/ryukzak-medved-100-v2',
				'price' => array(
					array(
						'price' => 1990,
						'param' => array(
							20 => 34,
						),
					),
				),
				'import_id' => '49',
			),
			array(
				'id' => 61,
				'name' => array('Теплый кемпинговый спальник «Туам»'),
				'article' => '34033',
				'cat_id' => 3,
				'brand_id' => 2,
				'anons' => array(
					"<p>Комфортное теплое одеяло с подголовником</p>",
				),
				'text' => array(
					'<p>- комфортный и просторный (90 на 220см)<br />- приятная внутрення ткань<br />- теплый, можно использовать 3 сезона<br />- небольшой вес за счет утеплителя Hollowfiber</p>
<p>Выезжая зимой на рыбалку, охоту или собираясь, просто, отдохнуть на свежем воздухе, мы всегда оказываемся перед выбором: <br />«Какой выбрать спальник, чтобы зимой нам было тепло, уютно и комфортно?»</p>
<p>Все кто занимается активным отдыхом слышал подобные разговоры: «мой спальник слишком узкий»,<br />«не хочу носить тяжелый спальник и так всего много» или «где бы купить не дорогой, удобный спальник?».<br />Отчасти теперь эти вопросы решены с выходом новой модели теплого кемпингового спальника «Туам».<br />Это отличная модель для зимы с порогом температуры до - 18 градусов, при весе 2кг!</p>
<p><br />Кемпинговый спальник «Туам» имеет размеры «<strong>90 на 220см</strong>», что позволяет комфортно разметиться на ночлег даже в зимнем костюме. <br />Еще одной отличительной чертой этой модели является новая техника плетения Hollowfiber, что обеспечивает спальнику<br />отличные температурные показатели.</p>
<p><br />Для всех любителей комфортного кемпинга в спальнике специально улучшена внутренняя ткань - фланель, она очень нежная и приятная к коже, что в очередной раз выделяет эту модель из категории теплых кемпинговых спальников и позволяет хозяину спальника <br />наслаждаться отдыхом не стесняя себя ночной одежды.</p>',
				),
				'images' => array(
					'198_teplyy-kempingovyy-spalni.jpg',
					'199_teplyy-kempingovyy-spalni.jpg',
					'197_teplyy-kempingovyy-spalni.jpg',
				),
				'param' => array(
					24 => '2',
					23 => '-18',
					21 => 41,
					2 => 3,
				),
				'rewrite' => 'shop/spalniki/teplyy-kempingovyy-spalnik-tuam',
				'price' => array(
					array(
						'price' => 3490,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '61',
			),
			array(
				'id' => 62,
				'name' => array('Спальный мешок «Лейкслип»'),
				'article' => '34023',
				'cat_id' => 3,
				'brand_id' => 2,
				'anons' => array(
					"<p>Комфортное теплое одеяло с подголовником</p>",
				),
				'text' => array(
					"<p>- довольно просторный спальный мешок<br />- приятная внутрення ткань<br />- легкий по весу<br />- им может укрытся два человека</p>
<p>Испытываете дискомфорт в обычных спальных мешках, потому что, вам не хватает в них места? Появился, новый, легкий <br />кемпинговый сальник Лейкслип. Особый, широкий пошив, этого спальника, порадует любого, кто захочет уснуть в нем.<br />Даже, если вы баскетболист, с довольно высоким ростом, комфортноый спальный мешок,позволит расположиться, в нем, <br />в удобной для вас позе и не будет стеснять движения.</p>
<p><br />Так же, Лейкслип, может заменить двухспальное одеяло или плед, надо всего лишь, полностью расстегнуть молнию.<br />Еще один неоспоримый плюс, данной модели, спального мешка - это его внутренняя ткань. Очень мягкая и нежная фланель<br />будет приятно соприкосаться с вашим телом. В легом спальном мешке Лейкслип, вы можете спать не стесняя себя <br />ночной одеждой. Квадратный подголовник обеспечит вам приятный сон на любой подушке, или предмете, который будет ее<br />заменять.</p>
<p><br />Имея достаточно просторные объемы, в разложеном состоянии (90 на 220 см),кемпинговый сальник Лейкслип весит всего 1,4кг <br />и компактно собирается в компрессионный мешок. Это лидер среди легких спальных мешков в своей ценовой категории.</p>",
				),
				'images' => array(
					'201_spalnyy-meshok-leykslip.jpg',
					'202_spalnyy-meshok-leykslip.jpg',
					'200_spalnyy-meshok-leykslip.jpg',
				),
				'new' => 1,
				'param' => array(
					24 => '1.4',
					22 => '20',
					21 => 41,
					2 => 42,
				),
				'rewrite' => 'shop/spalniki/spalnyy-meshok-leykslip',
				'price' => array(
					array(
						'price' => 2190,
						'param' => array(
							2 => 42,
						),
					),
				),
				'import_id' => '62',
			),
			array(
				'id' => 63,
				'name' => array('Спальный мешок «Антрим»'),
				'article' => '34013',
				'cat_id' => 3,
				'brand_id' => 2,
				'text' => array(
					"<p>Широкий спальник-одеяло с подголовником.</p>",
				),
				'text' => array(
					'<p>Собираясь на природу с ночевкой, на рыбалку, шашлыки или на дачу - всегда стоит вопрос: «Где спать?», «На чем спать?»и «Как спать?» Здесь на помощь приходит, старый добрый кемпинговый спальник. Но если вы не заядлый турист и привыкли спать на большой кровати под широким одеялом и в спальнике вам не совсем комфортно, потому что мало места, то легкий кемпинговый спальник Антрим специально для вас. У него особый крой, что позволяет удовлетворить потребности, даже самого прихотливого отдыхающего.</p>
<p>Летний спальник Антрим имеет удобный подголовник, который, при необходимости, можно превратить в капюшон, затянув веревочки по бокам. Под ним может смело укрыться два человека, стоит всего лишь до конца расстегнуть молнию и спальник превратится в одеяло. Еще один приятный плюс - в районе лица молния защищена специальной планкой, которая не позволяет соприкасаться ей с вашим лицом.</p>
<p>Имея достаточно просторные объемы, в разложеном состоянии (90 на 220 см), Антрим весит всего 1кг и компактно собирается в компрессионный мешок. Он не займет много места в багажнике вашего автомобиля и легко помещается в не большом рюкзаке или дорожной сумке.</p>
<p>Почему Антрим - лучший летний спальник для кемпинга?<br />- он легкий и компактный<br />- он шире, чем остальные спальные мешки<br />- им могут легко укрытся два человека<br />- в нем есть удобный подголовник-капюшон</p>',
				),
				'images' => array(
					'205_spalnyy-meshok-antrim.jpg',
					'204_spalnyy-meshok-antrim.jpg',
					'203_spalnyy-meshok-antrim.jpg',
				),
				'param' => array(
					24 => '1',
					23 => '5',
					22 => '25',
					21 => 41,
					2 => 3,
				),
				'rewrite' => 'shop/spalniki/spalnyy-meshok-antrim',
				'price' => array(
					array(
						'price' => 1650,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '63',
			),
			array(
				'id' => 64,
				'name' => array('Коврик самонадувающийся «Комфорт Плюс»'),
				'article' => '95278',
				'cat_id' => 4,
				'brand_id' => 2,
				'images' => array(
					'206_kovrik-samonaduvayuschiysya-ko.jpg',
					'207_kovrik-samonaduvayuschiysya-ko.jpg',
				),
				'action' => 1,
				'param' => array(
					2 => 3,
					7 => '3.6',
					26 => '198 х 130 х 5 см',
				),
				'rewrite' => 'shop/mebel/kovrik-samonaduvayuschiysya-komfort-plyus',
				'price' => array(
					array(
						'price' => 4490,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '64',
			),
			array(
				'id' => 65,
				'name' => array('Кровать складная BD-3'),
				'article' => '71161',
				'cat_id' => 4,
				'brand_id' => 2,
				'anons' => array(
					"<p>Кровать складная для кемпинга.</p>",
				),
				'text' => array(
					"<p>Чехол для хранения и переноски. Механизм быстрой, легкой установки и сборки.</p>",
				),
				'images' => array(
					'208_krovat-skladnaya-bd-3.jpg'
				),
				'param' => array(
					2 => 3,
					7 => '8',
					10 => 9,
					26 => '190*64*43 см',
					27 => '120',
				),
				'rewrite' => 'shop/mebel/krovat-skladnaya-bd-3',
				'price' => array(
					array(
						'price' => 3290,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '65',
			),
			array(
				'id' => 66,
				'name' => array('Раскладушка BD-8'),
				'article' => '95164',
				'cat_id' => 4,
				'brand_id' => 2,
				'text' => array(
					"<p>Самая надежная конструкция складной раскладушки, компактное хранение</p>",
				),
				'images' => array(
					'209_raskladushka-bd-8.jpg'
				),
				'param' => array(
					2 => 3,
					7 => '6.74',
					26 => '195*74,5*26 см',
					27 => '120',
				),
				'rewrite' => 'shop/mebel/raskladushka-bd-8',
				'price' => array(
					array(
						'price' => 2690,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '66',
			),
			array(
				'id' => 67,
				'name' => array('Раскладушка для рыбалки BD 660'),
				'article' => '95179',
				'cat_id' => 4,
				'brand_id' => 5,
				'anons' => array(
					"<p>Очень удобная кровать для любителей пере ночевать рядом с местом рыбалки! </p>",
				),
				'text' => array(
					"<p>Настолько удобна&hellip; Главное не проспать утренний !<br />Очень удобная и мягкая кровать для любителей переночевать рядом с местом рыбалки! Используются только износостойкие ткани. Проверенный каркас!<br />Подойдет для крупных личностей :)</p>",
					''
				),
				'images' => array(
					'210_raskladushka-dlya-rybalki-bd-6.jpg',
					'211_raskladushka-dlya-rybalki-bd-6.jpg',
				),
				'new' => 1,
				'param' => array(
					2 => 16,
					10 => 9,
					26 => '210 x 75 x 33 см',
					27 => '150',
				),
				'rewrite' => 'shop/mebel/raskladushka-dlya-rybalki-bd-660',
				'price' => array(
					array(
						'price' => 5490,
						'param' => array(
							2 => 16,
						),
					),
				),
				'import_id' => '67',
			),
			array(
				'id' => 68,
				'name' => array('Стеллаж складной FR-5S'),
				'article' => '95223',
				'cat_id' => 4,
				'brand_id' => 2,
				'images' => array(
					'213_stellazh-skladnoy-fr-5s.jpg',
					'212_stellazh-skladnoy-fr-5s.jpg',
				),
				'new' => 1,
				'param' => array(
					2 => 3,
					7 => '4.72',
					10 => 8,
					26 => '60*52*65 см',
					27 => '30',
				),
				'rewrite' => 'shop/mebel/stellazh-skladnoy-fr-5s',
				'price' => array(
					array(
						'price' => 3290,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '68',
			),
			array(
				'id' => 69,
				'name' => array('Стеллаж складной FR-1'),
				'article' => '95219',
				'cat_id' => 4,
				'brand_id' => 2,
				'images' => array(
					'214_stellazh-skladnoy-fr-1.jpg',
					'215_stellazh-skladnoy-fr-1.jpg',
				),
				'new' => 1,
				'param' => array(
					2 => 3,
					7 => '5.26',
					10 => 8,
					26 => '60*52*98 см',
					27 => '30',
				),
				'rewrite' => 'shop/mebel/stellazh-skladnoy-fr-1',
				'price' => array(
					array(
						'price' => 3490,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '69',
			),
			array(
				'id' => 70,
				'name' => array('Стол складной с полками FT-5'),
				'article' => '95218',
				'cat_id' => 4,
				'brand_id' => 2,
				'images' => array(
					'217_stol-skladnoy-s-polkami-ft-5.jpg',
					'216_stol-skladnoy-s-polkami-ft-5.jpg',
				),
				'param' => array(
					2 => 3,
					7 => '8.04',
					10 => 8,
					26 => '120*60*70 см',
					27 => '30',
				),
				'rewrite' => 'shop/mebel/stol-skladnoy-s-polkami-ft-5',
				'price' => array(
					array(
						'price' => 4490,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '70',
			),
			array(
				'id' => 71,
				'name' => array('Стул складной FC-3'),
				'article' => '71031',
				'cat_id' => 4,
				'brand_id' => 2,
				'anons' => array(
					"<p>Стул складной FC-3</p>",
				),
				'images' => array(
					'218_stul-skladnoy-fc-3.jpg',
					'219_stul-skladnoy-fc-3.jpg',
				),
				'param' => array(
					2 => 16,
					7 => '3',
					10 => 8,
					26 => '47*50*46/85.5 см',
					27 => '100',
				),
				'rewrite' => 'shop/mebel/stul-skladnoy-fc-3',
				'price' => array(
					array(
						'price' => 2290,
						'param' => array(
							2 => 16,
						),
					),
				),
				'import_id' => '71',
			),
			array(
				'id' => 72,
				'name' => array('Шезлонг складной FC-11 XL'),
				'article' => '95161',
				'cat_id' => 4,
				'brand_id' => 2,
				'anons' => array(
					"<p>Шезлонг складной регулируемый, с увеличенной шириной</p>",
				),
				'images' => array(
					'220_shezlong-skladnoy-fc-11-xl.jpg'
				),
				'param' => array(
					2 => 3,
					7 => '9',
					27 => '160',
				),
				'rewrite' => 'shop/mebel/shezlong-skladnoy-fc-11-xl',
				'price' => array(
					array(
						'price' => 6900,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '72',
			),
			array(
				'id' => 73,
				'name' => array('Стул для рыбалки FM-3'),
				'article' => '95177',
				'cat_id' => 4,
				'brand_id' => 5,
				'anons' => array(
					"<p>Благодаря телескопическим ножкам можно установить на любую поверхность</p>",
				),
				'text' => array(
					"<p>Возможно установить на любой поверхности!<br />Удобный, не занимающее много места стул, для рыбаков, любящих береговую рыбалку! Благодаря телескопическим ножкам можно установить на любую поверхность!</p>",
				),
				'images' => array(
					'221_stul-dlya-rybalki-fm-3.jpg'
				),
				'param' => array(
					2 => 16,
					10 => 9,
					26 => '38 x 40 x 43 см',
					27 => '120',
				),
				'rewrite' => 'shop/mebel/stul-dlya-rybalki-fm-3',
				'price' => array(
					array(
						'price' => 3290,
						'param' => array(
							2 => 16,
						),
					),
				),
				'import_id' => '73',
			),
			array(
				'id' => 74,
				'name' => array('Термокружка «Сильвер 400»'),
				'article' => '92471',
				'cat_id' => 5,
				'brand_id' => 1,
				'anons' => array(
					"<p>Стул складной FC-3</p>",
				),
				'images' => array(
					'222_termokruzhka-silver-400.jpg'
				),
				'new' => 1,
				'param' => array(
					7 => '0.2',
					28 => 52,
					29 => 44,
					30 => '0.4',
				),
				'rewrite' => 'shop/drugoe/termokruzhka-silver-400',
				'price' => array(
					array(
						'price' => 250,
					),
				),
				'import_id' => '74',
			),
			array(
				'id' => 75,
				'name' => array('Кружка S022'),
				'article' => '95102',
				'cat_id' => 5,
				'brand_id' => 1,
				'text' => array(
					"<p>Кружка с двойными стенками 300мл</p>",
				),
				'images' => array(
					'223_kruzhka-s022.jpg'
				),
				'param' => array(
					7 => '0.2',
					28 => 52,
					29 => 44,
					30 => '0.3',
				),
				'rewrite' => 'shop/drugoe/kruzhka-s022',
				'price' => array(
					array(
						'price' => 299,
					),
				),
				'import_id' => '75',
			),
			array(
				'id' => 76,
				'name' => array('Набор столовых приборов S088'),
				'article' => '95092',
				'cat_id' => 5,
				'brand_id' => 1,
				'text' => array(
					"<p>Набор столовых приборов - ложка, вилка, нож, открывалка</p>",
				),
				'images' => array(
					'224_nabor-stolovykh-priborov-s088.jpg'
				),
				'new' => 1,
				'hit' => 1,
				'param' => array(
					28 => 49,
					29 => 44,
					30 => '0.17',
				),
				'rewrite' => 'shop/drugoe/nabor-stolovykh-priborov-s088',
				'price' => array(
					array(
						'price' => 249,
					),
				),
				'import_id' => '76',
			),
			array(
				'id' => 77,
				'name' => array('Набор столовых приборов S119'),
				'article' => '95082',
				'cat_id' => 5,
				'brand_id' => 1,
				'anons' => array(
					"<p>Набор столовых приборов ложка, вилка, нож</p>",
				),
				'images' => array(
					'225_nabor-stolovykh-priborov-s119.jpg'
				),
				'new' => 1,
				'param' => array(
					7 => '0.1',
					28 => 49,
					29 => 44,
				),
				'rewrite' => 'shop/drugoe/nabor-stolovykh-priborov-s119',
				'price' => array(
					array(
						'price' => 199,
					),
				),
				'import_id' => '77',
			),
			array(
				'id' => 78,
				'name' => array('Сковорода стальная S164'),
				'article' => '95072',
				'cat_id' => 5,
				'brand_id' => 1,
				'anons' => array(
					"<p>Сковорода  стальная с медным дном и съемной ручкой &Oslash;19,7см</p>",
				),
				'images' => array(
					'226_skovoroda-stalnaya-s164.jpg'
				),
				'param' => array(
					1 => 'd 19,7см',
					7 => '0.35',
					28 => 50,
					29 => 44,
				),
				'rewrite' => 'shop/drugoe/skovoroda-stalnaya-s164',
				'price' => array(
					array(
						'price' => 396,
					),
				),
				'import_id' => '78',
			),
			array(
				'id' => 79,
				'name' => array('Чайник S044'),
				'article' => '95052',
				'cat_id' => 5,
				'brand_id' => 1,
				'anons' => array(
					"<p>Чайник/кофейник</p>",
				),
				'text' => array(
					"<p>Чайник-кофейник с ситечком вместимостью 0,95 литра.</p>",
				),
				'images' => array(
					'227_chaynik-s044.jpg'
				),
				'new' => 1,
				'param' => array(
					7 => '0.32',
					28 => 51,
					29 => 44,
					30 => '0.95',
				),
				'rewrite' => 'shop/drugoe/chaynik-s044',
				'price' => array(
					array(
						'price' => 549,
					),
				),
				'import_id' => '79',
			),
			array(
				'id' => 80,
				'name' => array('Сковорода алюминиевая A070'),
				'article' => '95062',
				'cat_id' => 5,
				'brand_id' => 1,
				'anons' => array(
					"<p>Сковорода алюминевая &Oslash;23см</p>",
				),
				'text' => array(
					"<p>Сковорода диаметром 23 сантиметра</p>",
				),
				'images' => array(
					'228_skovoroda-alyuminievaya-a070.jpg'
				),
				'no_buy' => 1,
				'param' => array(
					1 => 'd 23 см',
					7 => '0.36',
					28 => 50,
					29 => 45,
					30 => '1',
				),
				'rewrite' => 'shop/drugoe/skovoroda-alyuminievaya-a070',
				'price' => array(
					array(
						'price' => 499,
					),
				),
				'import_id' => '80',
			),
			array(
				'id' => 81,
				'name' => array('Набор посуды A096'),
				'article' => '95032',
				'cat_id' => 5,
				'brand_id' => 1,
				'text' => array(
					'<p>Котелок – 2,8 литра с крышкой-сковородой диаметром 17,5 см</p><p>Котелок – 1,9 литра с крышкой-сковородой диаметром 15 см</p><p>Котелок – 0,95 литра с крышкой-сковородой диаметром 12,5 см</p><p>Пластиковая чашка 220 мл – 3 штуки</p>',
				),
				'images' => array(
					'229_nabor-posudy-a096.jpg'
				),
				'new' => 1,
				'param' => array(
					1 => '3 персоны',
					7 => '0.95',
					28 => 49,
					29 => array(45, 46),
				),
				'rewrite' => 'shop/drugoe/nabor-posudy-a096',
				'price' => array(
					array(
						'price' => 899,
					),
				),
				'import_id' => '81',
			),
			array(
				'id' => 82,
				'name' => array('Набор посуды A137'),
				'article' => '95022',
				'cat_id' => 5,
				'brand_id' => 1,
				'anons' => array(
					"<p>Две кастрюли, сковорода + чайник</p>",
				),
				'text' => array(
					'<p>Кастрюля – 2,8 литра</p><p>Кастрюля – 1,9 литра</p><p>Сковорода – диаметр 19 см</p><p>Чайник-кофейник – 0,95 литра с ситечком</p><p>Съемная ручка</p>',
				),
				'images' => array(
					'230_nabor-posudy-a137.jpg',
					'231_nabor-posudy-a137.jpg'
				),
				'param' => array(
					7 => '0.89',
					28 => 49,
					29 => 45,
				),
				'rewrite' => 'shop/drugoe/nabor-posudy-a137',
				'price' => array(
					array(
						'price' => 1290,
					),
				),
				'import_id' => '82',
			),
			array(
				'id' => 83,
				'name' => array('Набор посуды S004'),
				'article' => '95012',
				'cat_id' => 5,
				'brand_id' => 1,
				'anons' => array(
					"<p>Набор на 3 персоны</p>",
				),
				'text' => array(
					'<p>Кастрюля с крышкой – 1,9 литра</p><p>Кастрюля с крышкой – 1,4 литра</p><p>Сковорода – диаметр 16,5 см</p><p>Пластмассовые чашки 220 мл. – 3 шт.</p>',
				),
				'images' => array(
					'232_nabor-posudy-s004.jpg'
				),
				'new' => 1,
				'action' => 1,
				'param' => array(
					1 => '3 персоны',
					7 => '1',
					28 => 49,
					29 => array(44, 46),
				),
				'rewrite' => 'shop/drugoe/nabor-posudy-s004',
				'price' => array(
					array(
						'price' => 1490,
					),
				),
				'import_id' => '83',
			),
			array(
				'id' => 84,
				'name' => array('Палатка «Велес 4 v.2»'),
				'article' => '25503',
				'cat_id' => 6,
				'brand_id' => 2,
				'anons' => array(
					"<p>Каркасно-дуговая кемпинговая палатка с тамбуром большого полезного объема</p>",
				),
				'text' => array(
					"<p>Каркасно-дуговая кемпинговая палатка с тамбуром большого полезного объема. Два входа. Два тамбура, один большого размера. Вентиляционные окна с сеткой и клапаном на молнии. Возможна установка без тента.</p>",
				),
				'no_buy' => 1,
				'rewrite' => 'shop/palatki/kempingovye/4v2',
				'images' => array(
					'322_palatka-veles-4-v2.jpg',
					'321_palatka-veles-4-v2.jpg',
				),
				'param' => array(
					2 => 3,
					3 => '4',
					4 => 5,
					5 => 1,
					6 => 1,
					7 => '8.34',
					8 => '68 х 23 х 23 см',
				),
				'price' => array(
					array(
						'price' => 6990,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '84',
			),
			array(
				'id' => 85,
				'name' => array('Палатка «Виржиния 4 v.2»'),
				'article' => '25533',
				'cat_id' => 6,
				'brand_id' => 2,
				'anons' => array(
					"<p>Комфортная кемпинговая палатка с двумя комнатами</p>",
				),
				'text' => array(
					"<p>Комфортная кемпинговая палатка с двумя комнатами.</p><p>Два спальных отделения. Просторный тамбур с двумя входами. Эффективная система вентиляции. Возможна отдельная установка тента.</p>",
				),
				'hit' => 1,
				'rewrite' => 'shop/palatki/kempingovye/4v285',
				'images' => array(
					'323_palatka-virzhiniya-4-v2.jpg',
					'324_palatka-virzhiniya-4-v2.jpg',
				),
				'param' => array(
					2 => 3,
					3 => '4',
					4 => 5,
					5 => 1,
					6 => 1,
					7 => '11.33',
					8 => '75 х 26 х 26 см',
				),
				'price' => array(
					array(
						'price' => 8990,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '85',
			),
			array(
				'id' => 86,
				'name' => array('Тент 3*4 км N'),
				'article' => '24048',
				'cat_id' => 8,
				'brand_id' => 1,
				'anons' => array(
					"<p>Надежная защита от непогоды</p>",
				),
				'text' => array(
					"<p>Окажется незаменимым помощником как в непогоду, так и в знойный день. Защитит Вашу стоянку от дождя, палящего солнца, придаст отдыху на природе дополнительный комфорт. Конек усилен стропой, на углах – петли для растяжек. Комплектуется 12 метровым шнуром для оттяжек.</p>",
				),
				'rewrite' => 'shop/palatki/tenty-i-besedki/34n',
				'images' => array(
					'326_tent-34-km-n.jpg',
					'325_tent-34-km-n.jpg',
				),
				'param' => array(
					2 => 3,
					7 => '1.2',
				),
				'price' => array(
					array(
						'price' => 1390,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '86',
			),
			array(
				'id' => 87,
				'name' => array('Палатка зимняя «Нерпа»'),
				'article' => '24079',
				'cat_id' => 8,
				'anons' => array(
					'<p>Палатка для рыбалки «Нерпа» с легким каркасом изготовленным в Южной Корее.</p>',
				),
				'text' => array(
					'<p>Каркас из алюминиевого сплава AL7001-T6 складывается из шести граней и образует шатер зонтичного типа. Эта конструкция прочна, надежна и устойчива к ветру. Очень быстро  раскрывается и устанавливается. Палатка мобильна, без труда переносится с одного места в другое, причем не нужно ее складывать. Купить палатку для рыбалки Нерпа рекомендуется любителям рыбной ловли в зимнее время года. <br />Тент палатки сделан из материала Oxford 210T 3000PU, усилен в нужных местах, швы обработаны клеем. Материал отлично пропускает свет, отталкивает воду, не боится механических воздействий. Комплектуется штормовыми растяжками, отражающими свет. Юбка замкнутая, что гарантирует плотное соединение с поверхностью и не дает ветру попадать внутрь. Вход в палатку большой, на молнии. Два замка помогают быстро расстегнуть или застегнуть молнию, а так же регулируют поступление воздуха при нагреве палатки. Защитные клапаны с липучками по контуру молнии спасут ее от попадания мокрого снега и обмерзания. Вентиляционный клапан сконструирован так, что позволяет находясь в палатке регулировать отток воздуха, а можно и совсем закрыть клапан. Имеется шесть карманов по двум стенкам палатки, в которых разместятся снасти. Эту палатку можно отапливать, соблюдая конечно меры безопасности. В комплект входит сумка, закрывающаяся молнией.</p>',
				),
				'rewrite' => 'shop/palatki/tenty-i-besedki/87',
				'images' => array(
					'327_palatka-zimnyaya-nerpa.jpg',
					'328_palatka-zimnyaya-nerpa.jpg',
				),
				'param' => array(
					2 => array(47, 48),
					4 => 20,
					5 => 1,
					6 => 1,
					7 => '2.4',
					12 => 17,
				),
				'price' => array(
					array(
						'price' => 5990,
						'param' => array(
							2 => 47,
						),
						'image_rel' => '327_palatka-zimnyaya-nerpa.jpg',
					),
					array(
						'price' => 5990,
						'param' => array(
							2 => 48,
						),
						'image_rel' => '328_palatka-zimnyaya-nerpa.jpg',
					),
				),
				'import_id' => '87',
			),
			array(
				'id' => 88,
				'name' => array('Стул складной со столом FC-4'),
				'article' => '71041',
				'cat_id' => 4,
				'brand_id' => 1,
				'anons' => array(
					"<p>Стул складной со столом FC-4</p>",
				),
				'rewrite' => 'shop/mebel/fc-4',
				'images' => array(
					'329_stul-skladnoy-so-stolom-fc-4.jpg',
					'330_stul-skladnoy-so-stolom-fc-4.jpg',
				),
				'param' => array(
					2 => 3,
					10 => 8,
					7 => '3.3',
					26 => '(78)48*50*46/86 см',
					27 => '100',
				),
				'price' => array(
					array(
						'price' => 2490,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '88',
			),
			array(
				'id' => 89,
				'name' => array('Кресло складное откидное FC-10'),
				'article' => '71101',
				'cat_id' => 4,
				'brand_id' => 2,
				'anons' => array(
					"<p>Кресло складное откидное FC-10</p>",
				),
				'text' => array(
					'<p>Кресло с регулировкой наклона спинки.<br />8 положений фиксируются подлокотниками.</p><p>Сиденье и спинка из  сетчатого полиэстра. Легко моется, стоек к ультрафиолету.</p>',
				),
				'rewrite' => 'shop/mebel/fc-10',
				'images' => array(
					'331_kreslo-skladnoe-otkidnoe-fc.jpg'
				),
				'param' => array(
					2 => 3,
					10 => 8,
					7 => '5.6',
					26 => '49*43*42/113 см',
					27 => '120',
				),
				'price' => array(
					array(
						'price' => 3690,
						'param' => array(
							2 => 3,
						),
					),
				),
				'import_id' => '89',
			),
			array(
				'id' => 54,
				'name' => array('Рюкзак «Контур 50»', 'Backpack "Contour 50"'),
				'article' => '71041',
				'cat_id' => 2,
				'brand_id' => 1,
				'anons' => array(
					"<p>Многофункциональный рюкзак для рыбалки и охоты.</p>", '<p>Multipurpose backpack for fishing and hunting.</p>'
				),
				'text' => array(
					"<p>Не только стильный, но и достаточно удобный в эксплуатации. Его основной объем и два больших боковых кармана на молнии, вместят все необходимое для рыбной ловли или охоты. Вертикальная смягчающая вставка, добавит жесткости спинке рюкзака, в плавающем клапане карман для мелочей, поясная стропа выполнена в съемном варианте, добавлена эластичная шнуровка на фронтальной части рюкзака. Возможна навеска дополнительного снаряжения снаружи.</p>",
					'<p>Not only stylish, but also rather convenient in operation. Its main volume and two big side pockets on a lightning, will contain all necessary for fishing or hunting. The vertical softening insert, will add rigidity to a backpack back, in the floating valve a pocket for trifles, zone a sling is executed in removable option, the elastic lacing on frontal part of a backpack is added. The hinge plate of additional equipment outside is possible.</p>'
				),
				'rewrite' => 'shop/ryukzaki/ryukzak-kontur-50-km',
				'images' => array(
					'175_ryukzak-kontur-50-km.jpg',
					'176_ryukzak-kontur-50-km.jpg',
				),
				'hit' => 1,
				'param' => array(
					14 => '0.9',
					15 => '50',
					16 => 1,
					26 => array('(78)48*50*46/86 см', '(78)48*50*46/86 sm'),
					27 => 100,
					20 => array(24, 35),
				),
				'rel' => array(39, 48, 52),
				'price' => array(
					array(
						'price' => 1360,
						'param' => array(
							20 => 24,
						),
						'image_rel'=> '175_ryukzak-kontur-50-km.jpg',
					),
					array(
						'price' => 1500,
						'param' => array(
							20 => 35,
						),
						'image_rel'=> '176_ryukzak-kontur-50-km.jpg',
					),
				),
				'import_id' => '54',
			),
		),
		'shop_order' => array(
			array(
				'status' => 3,
				'status_id' => 4,
				'summ' => 9160,
				'delivery_id' => 1,
				'user_id' => 2,
				'goods' => array(
					array(
						'good_id' => 44,
						'count_goods' => 1,
						'price' => 3690,
					),
					array(
						'good_id' => 76,
						'count_goods' => 2,
						'price' => 249,
					),
					array(
						'good_id' => 8,
						'count_goods' => 1,
						'price' => 5990,
						'param' => array(
							2 => 1,
						)
					),
				),
				'param' => array(
					1 => 'Михаил Волков',
					2 => 'mih@volkov.ru',
					3 => '+7999-888-66-55',
					5 => 'Москва',
					6 => 'Вернадского пр.',
					7 => '27',
					9 => '15',
				),
			),
			array(
				'status' => 3,
				'status_id' => 4,
				'summ' => 9424.80,
				'delivery_id' => 1,
				'user_id' => 1,
				'goods' => array(
					array(
						'good_id' => 54,
						'count_goods' => 2,
						'price' => 1360,
						'param' => array(
							20 => 24,
						)
					),
					array(
						'good_id' => 63,
						'count_goods' => 2,
						'price' => 1650,
						'param' => array(
							2 => 3,
						)
					),
					array(
						'good_id' => 60,
						'count_goods' => 1,
						'price' => 5490,
						'param' => array(
							2 => 42,
						)
					),
					array(
						'good_id' => 79,
						'count_goods' => 1,
						'price' => 549,
					),
				),
				'param' => array(
					1 => 'Андрей Серов',
					2 => 'dserov@yandex.ru',
					3 => '+7999000033',
					5 => 'Москва',
					6 => 'Саратовская',
					7 => '9',
					9 => '81',
				),
			),
			array(
				'status' => 1,
				'status_id' => 2,
				'summ' => 3330.50,
				'delivery_id' => 1,
				'goods' => array(
					array(
						'good_id' => 17,
						'count_goods' => 2,
						'price' => 6290,
						'param' => array(
							2 => 10,
						)
					),
				),
				'param' => array(
					1 => 'Спиридонов Антон',
					2 => 'fetur@yandex.ru',
					3 => '+79998881100',
					5 => 'Москва',
					6 => 'Энтузиастов',
					7 => '90',
					9 => '31',
				),
			),
			array(
				'status_id' => 1,
				'summ' => 16762.70,
				'delivery_id' => 2,
				'payment_id' => 1,
				'user_id' => 1,
				'created' => 'now',
				'goods' => array(
					array(
						'good_id' => 48,
						'count_goods' => 2,
						'price' => 1290,
						'param' => array(
							20 => 34,
						)
					),
					array(
						'good_id' => 63,
						'count_goods' => 2,
						'price' => 1650,
						'param' => array(
							2 => 3,
						)
					),
					array(
						'good_id' => 89,
						'count_goods' => 2,
						'price' => 3690,
						'param' => array(
							2 => 3,
						)
					),
					array(
						'good_id' => 85,
						'count_goods' => 1,
						'price' => 8990,
						'param' => array(
							2 => 3,
						)
					),
				),
				'param' => array(
					1 => 'Елена Иванова',
					2 => 'lena@mysite.com',
					3 => '+78885557744',
					5 => 'Новосибирск',
					6 => 'Федосеева',
					7 => '10',
					9 => '15',
				),
			),
			array(
				'status' => 2,
				'status_id' => 3,
				'summ' => 1156,
				'payment_id' => 2,
				'goods' => array(
					array(
						'good_id' => 54,
						'count_goods' => 1,
						'price' => 1360,
						'param' => array(
							20 => 24,
						)
					),
				),
				'param' => array(
					1 => 'Петров Сергей',
					2 => 'pets@mail.ru',
					3 => '+79993332211',
					5 => 'Санкт-Петербург',
					6 => 'Ленина',
					8 => '15',
					9 => '11',
				),
			),
			array(
				'status_id' => 1,
				'summ' => 5151.20,
				'payment_id' => 2,
				'delivery_id' => 3,
				'additional_cost_id' => 1,
				'goods' => array(
					array(
						'good_id' => 70,
						'count_goods' => 1,
						'price' => 4490,
						'param' => array(
							2 => 3,
						)
					),
				),
				'param' => array(
					1 => 'Иван Иванович Иванов',
					2 => 'ivanov@yandex.ru',
					3 => '+79995554488',
					5 => 'Москва',
					6 => 'Мира',
					7 => '30',
					9 => '131',
				),
			),
		),
		'shop_discount' => array(
			array(
				'id' => 1,
				'discount' => 15,
				'threshold_cumulative' => 10000,
			),
			array(
				'id' => 2,
				'discount' => 10,
				'threshold' => 3000,
			),
		),
		'shop_delivery' => array(
			array(
				"id" => 1,
				'name' => array('Курьер', 'Сourier'),
				'text' => array('Товар доставляется курьером до двери Вашего дома.', 'Item must collect from our warehouse'),
			),
		),
		'shop_additional_cost' => array(
			array(
				'id' => 1,
				'name' => array('Подарочная упаковка'),
				'text' => array('Мы красиво оформляем Ваш заказ'),
				'percent' => 3,
			),
			array(
				'id' => 2,
				'name' => array('Расширенная гарантия'),
				'amount' => 1000,
			),
			array(
				'id' => 3,
				'name' => array('Непромокаемый чехол'),
				'price' => 800,
				'shop_rel' => '1',
				'cat_id' => array(2),
			),
		),
		'shop_additional_cost_rel' => array(
			array(
				'additional_cost_id' => 3,
				'element_id' => 54,
			),
		),
		'shop_order_additional_cost' => array(
			array(
				'order_id' => 7,
				'additional_cost_id' => 1,
				'summ' => '134.7',
			),
		),
		'shop_waitlist' => array(
			array(
				'good_id' => 1,
				'lang_id' => 1,
				'mail' => 'erema@ochen_zhdu_kita.ru',
				'param' => 'a:1:{i:10;s:2:"18";}',
			),
		),
		'shop_wishlist' => array(
			array(
				'good_id' => 2,
				'session_id' => '6161nnvam6quvpfgdq7v4c7r23',
				'count' => 2,
				'param' => 'a:1:{i:10;s:2:"18";}',
			),
			array(
				'good_id' => 1,
				'session_id' => '6161nnvam6quvpfgdq7v4c7r23',
				'count' => 1,
				'param' => 'a:1:{i:10;s:2:"20";}',
			),
		),
		'shop_brand' => array(
			array(
				'id' => 1,
				'name' => array('Nova Tour'),
				'text' => array('<p>Лучший отдых – активный отдых! Жизнь – это не только работа, а отдых – это не только диван, телевизор или интернет. Активный отдых – лучший способ наслаждаться жизнью, познавать окружающий мир, проверять себя и свои возможности. Подбирайте правильное снаряжение из нашего нового каталога и отдыхайте активно!!!</p>'),
				'images' => array(
					'316_nova-tour.png'
				),
			),
			array(
				'id' => 2,
				'name' => array('Greenell'),
				'text' => array('<p>Какой русский человек не любит отдыхать на природе? Какой русский человек не вспоминает с наслаждением выезды на рыбалку, охоту или на шашлычки? Правильно! Отдыхать за городом, в лесу или у воды любят все. Чистый воздух, прекрасные пейзажи, возможность прикоснуться к природе и почувствовать себя ее частью – вот что всегда привлекало, привлекает и будет привлекать наших сограждан в кемпинговом отдыхе. Для этого и предназначены наши товары и снаряжение для кемпинга.</p>'),
				'images' => array(
					'317_greenell.png'
				),
			),
			array(
				'id' => 3,
				'name' => array('Нunter'),
				'text' => array('<p>Охота&hellip; шелест травы, дыхание леса и тень метнувшейся добычи. Инстинкты, дремавшие где-то глубоко внутри, пробуждаются и толкают вперёд! Найти, выследить, догнать&hellip; Единственный выстрел, без права на ошибку – полная собственность и отточенность движений&hellip;</p>
<p>&hellip; А после – воспоминания, как это было, и переживания волнующего состояния вновь и вновь. Состояния, когда ты с природой один на один, её часть и просыпающийся в тебе Зов дикой природы.</p>
<p>Охота на Охоту? Выбирайте надёжную экипировку и в путь!!!</p>'),
				'images' => array(
					'318_nunter.png'
				),
			),
			array(
				'id' => 4,
				'name' => array('Alaska'),
				'images' => array(
					'319_alaska.png'
				),
			),
			array(
				'id' => 5,
				'name' => array('Fisherman'),
				'images' => array(
					'320_fisherman.png'
				),
			),
		),
	);

	/**
	 * Выполняет действия при установке модуля
	 *
	 * @return void
	 */
	protected function action()
	{
		if (! empty($_SESSION["install_name"]))
		{
			$this->config[] =
				array(
					"name" => "nameshop",
					"value" => $_SESSION["install_name"],
				);
		}
		elseif(defined('TIT'.$this->langs[0]))
		{
			$this->config[] =
				array(
					"name" => "nameshop",
					"value" => constant('TIT'.$this->langs[0]),
				);
		}
	}

	/**
	 * Выполняет действия при установке модуля после основной установки
	 *
	 * @return void
	 */
	public function action_post()
	{
		DB::query("UPDATE {shop_price} SET price_id=id");
		$this->diafan->_shop->price_calc();
	}

	/**
	 * Делает записи о связанных с ценой изображениях
	 *
	 * @param array $row информаци о текущем элементе
	 * @return void
	 */
	protected function action_shop_price_image_rel($row)
	{
		if(empty($this->cache["images"][$row["image_rel"]]))
		{
			return;
		}
		$image_id = $this->cache["images"][$row["image_rel"]];
		DB::query("INSERT INTO {shop_price_image_rel} (price_id, image_id) VALUES (%d, %d)", $row["id"], $image_id);
	}

	/**
	 * Действия при удалении модуля
	 *
	 * @return void
	 */
	protected function uninstall_action()
	{
		DB::query("DELETE FROM {rewrite} WHERE BINARY rewrite='shop/cart/done'");
		DB::query("DELETE FROM {site} WHERE [name]='Заказ оформлен'");
	}
}

<?php
/**
 * Установка модуля
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

class Site_install extends Install
{
	/**
	 * @var boolean модуль является частью ядра
	 */
	public $is_core = true;

	/**
	 * @var string название
	 */
	public $title = "Страницы сайта";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "site",
			"comment" => "Страницы сайта",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "parent_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор родителя из таблицы {site}",
				),
				array(
					"name" => "count_children",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество вложенных страниц",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(100) NOT NULL DEFAULT ''",
					"comment" => "название",
					"multilang" => true,
				),
				array(
					"name" => "title_meta",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "заголовок окна в браузере, тег Title",
					"multilang" => true,
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
					"name" => "text",
					"type" => "LONGTEXT",
					"comment" => "контент",
					"multilang" => true,
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
					"multilang" => true,
				),
				array(
					"name" => "access",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "доступ ограничен: 0 - нет, 1 - да",
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
					"name" => "admin_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "пользователь из таблицы {users}, добавивший или первый отредктировавший страницу в административной части",
				),
				array(
					"name" => "title_no_show",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "не копировать заголовок в H1: 0 - нет, 1 - да",
				),
				array(
					"name" => "map_no_show",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
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
					"name" => "search_no_show",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "не участвует в поисковой выдаче: 0 - нет, 1 - да",
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
					"name" => "theme",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "шаблон страницы сайта",
				),
				array(
					"name" => "module_name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "прикрепленный модуль",
				),
				array(
					"name" => "js",
					"type" => "TEXT",
					"comment" => "JS-код",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY parent_id (parent_id)",
			),
		),
		array(
			"name" => "site_blocks",
			"comment" => "Блоки на сайте",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(100) NOT NULL DEFAULT ''",
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
					"name" => "access",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "доступ ограничен: 0 - нет, 1 - да",
				),
				array(
					"name" => "title_no_show",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "не показывать заголовок: 0 - нет, 1 - да",
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
					"name" => "admin_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "пользователь из таблицы {users}, добавивший или первый отредктировавший блок в административной части",
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
			"name" => "site_blocks_site_rel",
			"comment" => "Данные о том, на каких страницах выводятся блоки на сайте",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор блока из таблицы {site_blocks}",
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
				"KEY site_id (`site_id`)",
			),
		),
		array(
			"name" => "site_dynamic",
			"comment" => "Динамические блоки",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(100) NOT NULL DEFAULT ''",
					"comment" => "название",
					"multilang" => true,
				),
				array(
					"name" => "text",
					"type" => "TEXT",
					"comment" => "описание",
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
					"multilang" => true,
				),
				array(
					"name" => "type",
					"type" => "VARCHAR(20) NOT NULL DEFAULT ''",
					"comment" => "тип",
				),
				array(
					"name" => "access",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "доступ ограничен: 0 - нет, 1 - да",
				),
				array(
					"name" => "title_no_show",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "не показывать заголовок: 0 - нет, 1 - да",
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
					"name" => "admin_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "пользователь из таблицы {users}, добавивший или первый отредктировавший блок в административной части",
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
			"name" => "site_dynamic_element",
			"comment" => "Контент динамических блоков, заполенный в элементах модулей",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "dynamic_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор динамического блока из таблицы {site_dynamic}",
				),
				array(
					"name" => "module_name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "название модуля",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор элемента модуля",
				),
				array(
					"name" => "element_type",
					"type" => "VARCHAR(20) NOT NULL DEFAULT ''",
					"comment" => "тип элемента",
				),
				array(
					"name" => "value",
					"type" => "TEXT",
					"comment" => "значение",
					"multilang" => true,
				),
				array(
					"name" => "parent",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "применить к элементам категории: 0 - нет, 1 - да",
				),
				array(
					"name" => "category",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "применить к вложенным элементам: 0 - нет, 1 - да",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY dynamic_id (`dynamic_id`)",
				"KEY element_id (`element_id`)",
				"KEY element_type (`element_type`)",
			),
		),
		array(
			"name" => "site_dynamic_module",
			"comment" => "Данные о том, в каких модулях заполняются динамические блоки",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "dynamic_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор динамического блока из таблицы {site_dynamic}",
				),
				array(
					"name" => "module_name",
					"type" => "VARCHAR(50) NOT NULL DEFAULT ''",
					"comment" => "название модуля",
				),
				array(
					"name" => "element_type",
					"type" => "VARCHAR(20) NOT NULL DEFAULT ''",
					"comment" => "тип элемента",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY dynamic_id (`dynamic_id`)",
			),
		),
		array(
			"name" => "site_parents",
			"comment" => "Родительские связи страниц сайта",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор страницы из таблицы {site}",
				),
				array(
					"name" => "parent_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор страницы-родителя из таблицы {site}",
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
	);

	/**
	 * @var array записи в таблице {modules}
	 */
	public $modules = array(
		array(
			"name" => "site",
			"admin" => true,
			"site" => true,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Страницы сайта",
			"rewrite" => "site",
			"group_id" => 1,
			"sort" => 3,
			"act" => true,
			"add" => true,
			"add_name" => "Страница сайта",
			"children" => array(
				array(
					"name" => "Страницы сайта",
					"rewrite" => "site",
					"act" => true,
				),
				array(
					"name" => "Блоки на сайте",
					"rewrite" => "site/blocks",
					"act" => true,
				),
				array(
					"name" => "Динамические блоки",
					"rewrite" => "site/dynamic",
					"act" => true,
				),
				array(
					"name" => "Настройки",
					"rewrite" => "site/config",
				),
			)
		),
	);

	/**
	 * @var array настройки
	 */
	public $config = array(
		array(
			"name" => "use_animation",
			"value" => "1",
		),
		array(
			"name" => "images_variations",
			"value" => 'a:2:{i:0;a:2:{s:4:"name";s:6:"medium";s:2:"id";i:1;}i:1;a:2:{s:4:"name";s:5:"large";s:2:"id";i:3;}}',
		),
	);

	/**
	 * @var array страницы сайта
	 */
	public $site = array(
		array(
			"id" => 1,
			"name" => array('Главная страница', 'Home'),
			"text" => array('<p><strong>Добро пожаловать на наш новый сайт!</strong></p>
				<p>Здесь можно найти много чего интересного:</p>
				<ul>
				<li>каталог товаров (интернет-магазин)</li>
				<li>вопрос-ответ</li>
				<li>новости</li>
				<li>файловый архив</li>
				<li>фотогалерея</li>
				</ul>
				<p>и много-многое другое!</p>',

				'<p><strong>Welcome to our new site!</strong></p>
				<p>Here you can find more-more nice informations:</p>
				<ul>
				<li>catalog of goods (e-shop)</li>
				<li>FAQ</li>
				<li>news</li>
				<li>files</li>
				<li>photos</li>
				</ul>
				<p>and more-more else!</p>'),
			"rewrite" => "",
			"theme" => "site_start.php",
			"title_no_show" => true,
		),
		array(
			"id" => 2,
			'name' => array('Полезное', 'Useful'),
			'text' => array('<insert name="show_links" module="site">', '<insert name="show_links" module="site">'),
			"rewrite" => "useful",
			"menu" => 1,
			"sort" => 2,
		),
		array(
			"id" => 3,
			"sort" => 5,
		),
		array(
			"id" => 4,
			"name" => array('О компании', 'About the company'),
			"sort" => 1,
			"menu" => 1,
		),
	);

	/**
	 * @var array SQL-запросы
	 */
	public $sql = array(
		"site" => array(
			array(
				"id" => 150,
				"name" => array('Согласие на обработку персональных данных', 'Privacy Notice'),
				"text" => array('<p>Пользователь, оставляя заявку, принимает настоящее Согласие на обработку персональных данных (далее – Согласие). Действуя свободно, своей волей и в своем интересе, а также подтверждая свою дееспособность, Пользователь дает свое согласие на обработку своих персональных данных со следующими условиями:</p>
					<ol>
					<li>Данное Согласие дается на обработку персональных данных, как без использования средств автоматизации, так и с их использованием.</li>
					<li>Согласие дается на обработку следующих персональных данных: Персональные данные, не являющиеся специальными или биометрическими: номера контактных телефонов; адреса электронной почты; место работы и занимаемая должность; пользовательские данные (сведения о местоположении; тип и версия ОС; тип и версия Браузера; тип устройства и разрешение его экрана; источник откуда пришел на сайт пользователь; с какого сайта или по какой рекламе; язык ОС и Браузера; какие страницы открывает и на какие кнопки нажимает пользователь; ip-адрес.</li>
					<li>Персональные данные не являются общедоступными.</li>
					<li>Цель обработки персональных данных: обработка входящих запросов физических лиц с целью оказания консультирования; аналитики действий физического лица на веб-сайте и функционирования веб-сайта; проведение рекламных и новостных рассылок.</li>
					<li>Основанием для обработки персональных данных является: ст. 24 Конституции Российской Федерации; ст.6 Федерального закона №152-ФЗ «О персональных данных»; настоящее согласие на обработку персональных данных</li>
					<li>В ходе обработки с персональными данными будут совершены следующие действия: сбор; запись; систематизация; накопление; хранение; уточнение (обновление, изменение); извлечение; использование; передача (распространение, предоставление, доступ); блокирование; удаление; уничтожение.</li>
					<li>Персональные данные обрабатываются до отписки физического лица от рекламных и новостных рассылок. Также обработка персональных данных может быть прекращена по запросу субъекта персональных данных. Хранение персональных данных, зафиксированных на бумажных носителях осуществляется согласно Федеральномузакону №125-ФЗ «Об архивном деле в Российской Федерации» и иным нормативно правовым актам в области архивного дела и архивного хранения. </li>
					<li>Согласие может быть отозвано субъектом персональных данных или его представителем путем направления письменного заявления по адресу, указанному на главной странице настоящего сайта.</li>
					<li>В случае отзыва субъектом персональных данных или его представителем согласия на обработку персональных данных владельцы сайта вправе продолжить обработку персональных данных без согласия субъекта персональных данных при наличии оснований, указанных в пунктах 2 – 11 части 1 статьи 6, части 2 статьи 10 и части 2 статьи 11 Федерального закона №152-ФЗ «О персональных данных» от 27.07.2006 г.</li>
					<li>Настоящее согласие действует все время до момента прекращения обработки персональных данных, указанных в п.7 и п.8 данного Согласия.</li></ol></p>',
					'<p>On-line Privacy: we understands how important it is to keep all of our customers personal information private and safe. When you create an account at our site, we ask you for personal information. We use this information to process your orders and to communicate with you about the status of your orders. We also use your email address to send you newsletters IF you choose to subscribe yourself during registration. Should you no longer wish to receive our newsletter, simply request to be removed from our newsletter mailing list using one of the following methods: send to our email, reply to a newsletter with "Remove" in the subject line or you can unsubscribe or subscribe yourself using our edit account feature.</p>
					<p><b>We will not sell, rent or disclose your personally identifying information to third parties under any circumstances.</b></p>
					<p>We have implemented technology and security features, as well as strict internal guidelines, to safeguard the privacy of your personal information from unauthorized access or improper use. We will continue to enhance our security procedures as new technology becomes available.</p>
					<p>Order Privacy: All orders placed are handled in the strictest of confidence. Order information will only be disclosed to the person or persons named on the order.</p>
					<p>Packaging Privacy: All goods shipped by our site are shipped in plain brown boxes or similar discreet packaging. Our web-site address, our return address, and the name and address to which the package is being shipped are the only indications on the parcel.</p>
					<p>International Shipments do require the addition of a Customs Form. On these forms, we list item descriptions in the most general way possible.</p>
					<p>Credit Privacy: All credit information provided to us by our clientele is considered highly confidential and is thus kept private and protected. We do not distribute our customers charge card information to anyone.</p>'),
				"rewrite" => "privacy",
			),
		),
		"site_blocks" => array(
			array(
				"id" => 1,
				"name" => array('Телефон в шапке сайта', 'Phone'),
				"text" => array('495 567-09-12', '495 567-09-12'),
				"title_no_show" => 1,
				"hide_htmleditor" => "text",
			),
			array(
				"id" => 2,
				"name" => array('Контакты в футере', 'Contacts'),
				"text" => array('г. Москва, ул. Людвига Великого,<br>д. 12, стр. 1, офис 12<br>Тел.: 8 495  121-21-12<br>E-mail: info@demosite.ru', 'Moscow, Ludwig the Great st.,<br>b. 12/1, office 12 <br> Tel.: 8495 121-21-12<br>E-mail: info@demosite.ru'),
				"title_no_show" => 1,
			),
			array(
				"id" => 3,
				"name" => array('Блок о доставке в карточке товара', 'Block about delivery for shopcart'),
				"text" => array('<p>Доставка бесплатная*<br>*до 3000 - 300 рублей</p>', '<p>Delivery is free*<br>*less 3000 - 300 rubles</p>'),
				"title_no_show" => 1,
			),
			array(
				"id" => 4,
				"name" => array('Блок о возврате в карточке товара', 'Block the return of the item card'),
				"text" => array('<p>14 дней возврат/обмен<br> 2 года гарантия</p>', '<p>14 days return/change<br> 2 years warranty</p>'),
				"title_no_show" => 1,
			),
		),
		"site_dynamic" => array(
			array(
				"id" => 1,
				"name" => array("Источник новости и автор текста"),
				"type" => "editor",
				"module" => array(
					array(
						"element_type" => "element",
						"module_name" => "news",
					),
				),
			)
		),
	);

	/**
	 * @var array демо-данные
	 */
	public $demo = array(
		"site" => array(
			array(
				"id" => 1,
				"text" => array('<h1>О нашем магазине</h1><p>Наша компания 16 лет производит снаряжение для охоты, рыбалки, туризма и активного отдыха специально для российских условий. В настоящее время ассортимент включает в себя: рюкзаки, палатки, спальные мешки, аксессуары, одежду и обувь для активного отдыха, охоты, рыбалки, а также разнообразную продукцию дистрибютируемых торговых марок.</p><p>Торговая марка зарегистрирована в России, СНГ, Европе и Соединенных Штатах Америки. А благодаря широкой партнерской сети продукция распространяется по России и СНГ.</p><p>Россия с ее бескрайними просторами, обилием рек, озер, лесов и гор просто создана для отдыха на природе! И основная задача Компании создавая туристическое снаряжение с учетом географического, климатического и экономического положения России, сделать отдых увлеченных людей комфортным, доступным и незабываемым.</p><div class="mp-sections"><ul><li><a href="#"><img src="BASE_PATHimg/sample_section_icon_01.png"><br>Бесплатная доставка</a></li><li><a href="#"><img src="BASE_PATHimg/sample_section_icon_02.png"><br>Гарантия на все товары</a></li><li><a href="#"><img src="BASE_PATHimg/sample_section_icon_03.png"><br>Магазины в Москве</a></li><li><a href="#"><img src="BASE_PATHimg/sample_section_icon_04.png"><br>Удобная система оплаты</a></li></ul></div>',
				'<h1>About our shop</h1><p>Our company produces hunting, fishing and camping equipment over 16 years especially for russian conditions. At present our range includes: backpacks, tents, sleeping bags, accessories, clothes and footwear for camping, hunting, fishing, there are also various productions of distributive trademarks.</p><p>The trademark is registered in Russia, CIS, Europe and United States of America. Due to wide spreaded partnership, our production propagates all over the Russia and CIS.</p><p>Russia is quite created for outdoor resting, with its boundless fields, plenty of rivers, lakes, forests and mountains! So the main Company\'s objective while creating touristic equipment considering geographical, climatic and economical position of Russia is to make people\'s recreation comfortable, available and unforgettable.</p><div class="mp-sections"><ul><li><a href="#"><img src="BASE_PATHimg/sample_section_icon_01.png"><br>Free shipping</a></li><li><a href="#"><img src="BASE_PATHimg/sample_section_icon_02.png"><br>Warranty on all products</a></li><li><a href="#"><img src="BASE_PATHimg/sample_section_icon_03.png"><br>Shops in Moscow</a></li><li><a href="#"><img src="BASE_PATHimg/sample_section_icon_04.png"><br>Convenient system of payment</a></li></ul></div>'),
			),
			array(
				"id" => 4,
				"sort" => 1,
				"text" => array('<p>Мы российская компания. разрабатываем и производим снаряжение для туризма и активного отдыха специально для российских условий.</p><p>Большие расстояния, грунтовые дороги или их отсутствие, холодная зима и жаркое лето сменяющееся затяжными дождями, огромные пространства нетронутой дикой природы. Мы разрабатываем и производим снаряжение для туризма и активного отдыха специально для российских условий.<br>Наше снаряжение создано в России и испытано ее суровыми условиями.</p><p>Вот уже много лет мы разрабатываем и производим такое снаряжение и мы умеем его делать.</p>', '<p>The our trade mark was established in early 1996. Do you remember that time? The shelves in shops began to fill with tourist and recreational goods of Russian and foreign brands. The epoch of total product shortage was over. But the prices...</p><p>People&lsquo;s incomes that time weren&lsquo;t high and they could hardly afford to overpay for brand names. It wasn&lsquo;t a way out to buy cheap Chinese or "makeshifts" products either. Our trade mark became exactly that offer people waited for! Qualitative and functional equipment at moderate prices found its consumer.</p><p>Literally the name of the brand can be translated as "new trip". And really we equipment became for many people a new trip. And not one!</p><p>What do you take with you when go for a trip? Right! A backpack. Backpacks were the first to be produced under our Trade Mark. You still can find many times updated, time and road tested "Vitim" and "Hunter" backpacks. These backpacks are completely different from those produced in 1996, but the idea and the names remained the same. It&lsquo;s a simple and durable backpack without any frills. One year</p><p>later we added two models of tents. Then a number of sleeping bags.</p><p>In 1998 Russia faced crisis times that became a severe ordeal for many immature Russian companies. People&lsquo;s incomes slumped and foreign goods disappeared from shelves. But these changes gave a pulse to the development our Company that was ready to work and produce goods for uneasy Russian market.</p><p>Boundless territories of Russia, lack of well-developed infrastructure, abundance of rivers, lakes, mountains and forests are perfect for outdoor recreation. The only thing you need is desire and reliable equipment. Our products are designed and produced specially for Russian external environment.</p><p>We equipment is designed according to geographical, climatic and economic location of Russia. It considers the style of recreation in our country as it was primarily produced specially for Russia.</p><p>Do you hear wind noise? It&lsquo;s time to go for a trip!</p>'),
				"rewrite" => "about",
				"children" => array(
					array(
						"name" => array('Философия'),
						"text" => array("<h2>Мы работаем в России</h2><p>Наше снаряжение учитывает специфику российских условий. Оно создано специально для них! Разрабатывая рюкзак или палатку мы знаем в каких условиях они будут работать. Мы испытываем их и вносим все необходимые изменения в конструкцию и подбор материалов. Вы можете купить рюкзак или палатку дешевле. Но сможете ли вы им доверять?</p><h2>Оптимальный выбор</h2><p>Вы пробовали ездить на болиде формулы один по улицам города? Уверяем Вам не понравится! Так и в снаряжении. Избыточные свойства не только не помогают, но часто мешают. И при этом за них приходится многократно переплачивать! В палатке для штурма Эвереста очень тесно и душно, куртка полярника не очень подойдет Вам на прогулке, в альпинистский рюкзак не удобно запихивать резиновую лодку для рыбалки. Наше снаряжение создано для отдыха! Оно удобно и функционально и при этом вы не переплачиваете за избыточные свойства.</p><h2>Гарантии</h2><p>Сломать можно все. И наше снаряжение не исключение. Но! Все наши изделия обеспечены гарантией. Гарантийно-сервисный центр справится с любой проблемой! Мы не только устраним неисправность но и починим порванное или случайно прожженное изделие. Ваш комфортный отдых наша специальность!</p>"),
						"rewrite" => "about/filosofiya",
						"menu" => 1,
					),
					array(
						"name" => array('История'),
						"text" => array('<p>Годом рождения торговой марки стал уже далекий 1996-й. Помните это время? Полки магазинов начали заполняться товарами для туризма и отдыха российских и зарубежных марок. Эпоха тотального дефицита осталась позади. Но вот цены&hellip;</p>\n<p>Доходы большинства населения в то время были не высоки, и позволить себе переплачивать лишь за «бренд» люди просто не могли. А покупать дешевые китайские или «сделанные на коленке» изделия не хотелось. Торговая марка стала именно тем предложением, которое люди давно ждали! Качественное, функциональное снаряжение по разумным ценам быстро нашло своего потребителя.</p>\n<p>Дословно название марки можно перевести как «новое путешествие». И снаряжение действительно стало для многих новым путешествием. И не одним!</p>'),
						"rewrite" => "about/istoriya",
						"menu" => 1,
					),
				),
			),
			array(
				"name" => array('Наши магазины', 'Our shops'),
				"text" => array('<p>Простор 2000<br>Авиамоторная улица, 18, корп.1<br>(495) 362 23 45, 362 20 70</p><p>магазин «Серебряная Мечта»<br>5-я Кабельная улица, 2 стр. 1 магазин № 5 в Т.Ц СпортЕХ<br>8 495 6418712, 8 916 2511982</p><p>магазин «Большой слон»<br>улица Летчика Бабушкина, 31, корп.1<br>(495) 472 82 84</p><p>ТЦ «Ролл Холл», пав. 47<br>Холодильный переулок, 3, Оф.47<br>(495) 604 42 26</p>',
				'<p>Prostor 2000<br>Aviamotornaya str. 18-1<br>+7 (495) 362 23 45, 362 20 70</p><p>Shop «Silver Dream»<br>5-th Cabels str., 2-1 place № 5 in mall EX<br>+7 495 6418712, +7 916 2511982</p><p>Shop «Big Elefant»<br>Babushkina str., 31-1<br>+7 (495) 472 82 84</p><p>Mall «Roll Hall», sec. 47<br>Holodilnyi, 3, 47<br>+7 (495) 604 42 26</p>'),
				"rewrite" => "shops",
				"menu" => 1,
				"sort" => 3,
				"children" => array(
					array(
						"name" => array('Интернет-магазины'),
						"text" => array('<p>PrirodaUral.ru<br>Свердловская область<br>+7 (343) 384 02 85<br>Часы работы: 08:30-17:30</p><p>Палаток.ру<br>Москва<br>(499) 703-40-72<br>Часы работы: 10-19<br><br>Интернет-магазин wildberries<br>Москва<br>8 (800) 775 77 05<br><br>интернет-магазин OZON.ru<br>Москва<br><br>Интернет-магазин tyrpoxod.ru<br>Москва<br>(916) 784 50 64<br><br>Интернет-магазин sotmarket<br>Москва<br>8-800-555-98-98<br><br>kitsport<br>Cанкт-Петербург<br>(812) 980-35-57<br><br>LesVoda<br>Cанкт-Петербург<br>(812)932-86-80<br><br>monterra.ru<br>Cанкт-Петербург<br>(812) 715-06-02; 8(911) 945-03-91<br><br>Pitermag<br>Cанкт-Петербург<br>(812) 951-05-11<br><br>travers<br>Cанкт-Петербург<br>(812) 648-13-47<br><br>Интернет-магазин oborontech.ru<br>Москва<br>(495) 640 24 35<br><br>Интернет магазин «Sportique.ru»<br>Москва<br>(495) 220 97 66; (916) 632 19 17<br><br>Интернет-магазин ruksak.ru<br>Москва<br>(495) 580 60 47<br><br>Интернет-магазин Touryour.ru<br>Москва<br>(495) 766 68 91<br><br>Интернет-магазин Суперпоход<br>Москва<br>(495) 669-31-34<br><br>Интернет-магазин vrukzake.ru<br>Москва<br>+7 (495) 531-33-05, +7 (812) 448-07-97<br><br>Интернет-магазин «Рюкзаков.РФ»<br>Москва<br>(495) 544-72-11<br><br>ПикникРУ<br>Свердловская область<br>8-800-700-04-63<br><br>Интернет-магазин forcamper.ru<br>Москва<br>(495) 782 72 29<br><br>интернет-магазин «Серебряная Мечта»<br>Москва<br>8 495 6418712, 8 916 2511982<br><br>Интернет-магазин My-shop.ru<br>Москва<br>(495) 638-53-38<br><br>Интернет-магазин kpoxodu.ru<br>Москва<br>8(495)979-98-64 8(963)764-00-22<br><br>Интернет-магазин Onhill.ru<br>Москва<br>(495) 998 34 12<br><br>Интернет-магазин TOUR-STORE<br>Москва<br>+7 (925) 039-54-70; +7 (903) 779-29-25<br><br>интернет-магазин divales<br>Иркутская область<br>89 501 141 786<br><br>интернет-магазин FISHER-HUNTER<br>Краснодарский край<br>8 (928) 413 39 40<br><br>интернет-магазин «Ирбис»<br>Башкирия<br>(3472) 56 98 53<br><br>Интернет магазин «firma-urma.ru»<br>Москва<br>(495) 798-54-88 , (499)707-71-51<br><br>Интернет-магазин mobulatorg.ru<br>Москва<br>(499) 707 71 51<br><br>Интернет-магазин «РОБИНЗОН»<br>Кемеровская область<br>(384-3) 52-89-57<br><br>интернет-магазин «Раффа»<br>Москва<br>(495) 984 82 94, (812) 320 96 42<br><br>Интернет-магазин «Возьми в поход»<br>Москва<br><br>Интернет магазин «Пикничок»<br>Ростовская область<br><br>интернет-магазин Товар2.ру<br>Москва<br>+7 (495) 649-86-35, 8-800-333-51-19<br><br>Интернет-магазин «Novatour-Shop.ru»<br>Москва<br>(499) 653 6770<br><br>Интернет-магазин Toppit.ru<br>Москва<br>(495) 743 97 27<br><br>Интернет-магазин rukzakshop.ru<br>Москва<br>8-800-775-10-63 (беспл.); +7 (495) 983-30-83; +7 (985) 211-14-91<br><br>Интернет-магазин «Life-camp»<br>Смоленск<br>+7 (951) 699-48-97<br><br>Интернет-магазин kvadromania.ru<br>Московская область<br>(495) 698-61-98<br><br>Интернет-магазин neofishing<br>Москва<br>(495) 517-36-87; (499) 745-65-55<br><br>Интернет-магазин «MaximuM»<br>Тюменская область<br>8 904 491 6747,8 904 491 6787</p>','<p>PrirodaUral.ru<br>+7 (343) 384 02 85</p><p>palatok.ру<br>+7 (499) 703-40-72</p><p>wildberries.ru<br>+7 (800) 775 77 05<br><br>OZON.ru</p><p>tyrpoxod.ru<br>+7 (916) 784 50 64<br><br>sotmarket.ru<br>+7-800-555-98-98<br><br>kitsport.ru<br>+7 (812) 980-35-57</p>'),
						"rewrite" => "shops/on-line",
						"menu" => 1,
					),
					array(
						"name" => array('Оптовые склады'),
						"text" => array('<p>UAB KOTAS<br>Lietuva Ukmerges 234A, Vilnius, LT07160<br>+3(705) 241-17-11</p><p>Тонар-Опт<br>проспект Калинина, 57/1<br>+7 (385) 222-72-02, +7 (385) 277-33-40</p>'),
						"rewrite" => "shops/optovye-sklady",
						"menu" => 1,
					),
				),
			),
		),
		"site_dynamic" => array(
			array(
				"id" => 1,
				"name" => array("Анекдот в тему", 'Anecdote'),
				"type" => "editor",
				"module" => array(
					array(
						"element_type" => "element",
						"module_name" => "news",
					),
				),
				'element' => array(
					array(
						"module_name" => "news",
						"element_type" => "element",
						"element_id" => 20,
						"value" => array('<p>Уважаемые радиослушатели, в нашей программе произошли незначительные изменения.
Вместо интервью с Федором Конюховым вы услышите интервью с конюхом Федоровым.</p>'),
					),
					array(
						"module_name" => "news",
						"element_type" => "element",
						"element_id" => 19,
						"value" => array('<p>Лежит турист в палатке и слышит, как комары переговариваются:<br>— Ну что, здесь его съедим или на болото потащим?<br>— Да ты что, на болоте большие отнимут еще и самих сожрут!</p>'),
					),
				),
			)
		),
		"site_blocks" => array(
			array(
				"id" => 2,
				"name" => array('Контакты в футере', 'Contacts'),
				"text" => array('г. Москва, ул. Людвига Великого,<br>д. 12, стр. 1, офис 12<br>Тел.: 8 495  121-21-12<br>E-mail: info@demosite.ru<br><br>Контент для демосайта предоставлен<br>компанией <a href="http://www.novatour.ru/">Nova Tour</a>,<br>российским производителем туристического снаряжения.', 'Moscow, Ludwig the Great st.,<br>b. 12/1, office 12 <br> Tel.: 8495 121-21-12<br>E-mail: info@demosite.ru<br><br>Content for this demo is provided<br>by <a href="http://www.novatour.ru/">Nova Tour</a>,<br>Russian manufacturer of travel gear.'),
				"title_no_show" => 1,
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
		$this->config[] =
		array(
			"name" => "trial",
			"module_name" => "core",
			"value" => (time() + 86400 * 20),
		);
		if (! empty($_SESSION["install_name"]))
		{
			$this->site[0]["title_meta"] = $_SESSION["install_name"];
		}
	}
}

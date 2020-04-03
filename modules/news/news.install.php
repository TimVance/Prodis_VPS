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

class News_install extends Install
{
	/**
	 * @var string название
	 */
	public $title = "Новости";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "news",
			"comment" => "Новости",
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
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата создания",
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
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор основной категории из таблицы {news_category}",
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
					"name" => "prior",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "важно, всегда сверху: 0 - нет, 1 - да",
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
					"name" => "admin_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "пользователь из таблицы {users}, добавивший или первый отредктировавший новость в административной части",
				),
				array(
					"name" => "access",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "доступ ограничен: 0 - нет, 1 - да",
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
				"KEY site_id (site_id)",
			),
		),
		array(
			"name" => "news_rel",
			"comment" => "Связи похожих новостей",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор новости из таблицы {news}",
				),
				array(
					"name" => "rel_element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор связанной новости из таблицы {news}",
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
			"name" => "news_category",
			"comment" => "Категории новостей",
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
					"comment" => "идентификатор родителя из таблицы {news_category}",
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
					"name" => "admin_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "пользователь из таблицы {users}, добавивший или первый отредктировавший категорию в административной части",
				),
				array(
					"name" => "access",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "доступ ограничен: 0 - нет, 1 - да",
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
				"KEY parent_id (parent_id)",
				"KEY site_id (site_id)",
			),
		),
		array(
			"name" => "news_category_parents",
			"comment" => "Родительские связи категорий новостей",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT( 11 ) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {news_category}",
				),
				array(
					"name" => "parent_id",
					"type" => "INT( 11 ) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории-родителя из таблицы {news_category}",
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
			"name" => "news_category_rel",
			"comment" => "Связи новостей и категорий",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор новости из таблицы {news}",
				),
				array(
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {news_category}",
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
			"name" => "news_counter",
			"comment" => "Счетчик просмотров новостей",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор новости из таблицы {news}",
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
	);

	/**
	 * @var array записи в таблице {modules}
	 */
	public $modules = array(
		array(
			"name" => "news",
			"admin" => true,
			"site" => true,
			"site_page" => true,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Новости",
			"rewrite" => "news",
			"group_id" => 1,
			"sort" => 5,
			"act" => true,
			"add" => true,
			"add_name" => "Новость",
			"docs" => "http://www.diafan.ru/moduli/novosti/",
			"children" => array(
				array(
					"name" => "Новости",
					"rewrite" => "news",
					"act" => true,
				),
				array(
					"name" => "Категории",
					"rewrite" => "news/category",
					"act" => true,
				),
				array (
					'name' => 'Статистика',
					'rewrite' => 'news/counter',
					'act' => true,
				),
				array(
					"name" => "Настройки",
					"rewrite" => "news/config",
				),
			)
		),
	);


	/**
	 * @var array страницы сайта
	 */
	public $site = array(
		array(
			"name" => array('Новости', 'News'),
			"module_name" => "news",
			"rewrite" => "news",
			"menu" => 1,
			"parent_id" => 2,
		),
	);

	/**
	 * @var array настройки
	 */
	public $config = array(
		array(
			"name" => "images_element",
			"value" => "1",
		),
		array(
			"name" => "count_list",
			"value" => "2",
		),
		array(
			"name" => "count_child_list",
			"value" => "2",
		),
		array(
			"name" => "children_elements",
			"value" => "1",
		),
		array(
			"name" => "list_img_element",
			"value" => "1",
		),
		array(
			"name" => "use_animation",
			"value" => "1",
		),
		array(
			"name" => "format_date",
			"value" => "2",
		),
		array(
			"name" => "nastr",
			"value" => "10",
		),
		array(
			"name" => "nastr_cat",
			"value" => "10",
		),
		array(
			"name" => "rel_two_sided",
			"value" => "1",
		),
		array(
			"name" => "counter",
			"value" => "1",
		),
		array(
			"name" => "title_tpl",
			"value" => "%name, %category",
		),
		array(
			"name" => "keywords_tpl",
			"value" => "%name %category",
		),
		array(
			"name" => "images_variations_element",
			"value" => 'a:2:{i:0;a:2:{s:4:"name";s:6:"medium";s:2:"id";i:1;}i:1;a:2:{s:4:"name";s:5:"large";s:2:"id";i:3;}}',
		),
		array(
			"name" => "cat",
			"value" => '1',
		),
		array(
			"name" => "show_more",
			"value" => '1',
		),
	);

	/**
	 * @var array демо-данные
	 */
	public $demo = array(
		'news_category' => array(
			array(
				'id' => 1,
				'name' => array('Новости туризма', 'Tourism news'),
				'anons' => array('<p>Все самые свежие новости туризма в нашем новостном разделе.</p>', '<p>All the latest travel news in our news section.</p>'),
				'text' => array('<p>Ниже список актуальных новостей туризма, которые наши специалисты подбирают по всему инфопространству и оперативно выкладывают, чтобы радовать всех наших посетителей. Знакомьтесь, читайте, оценивайте, комментируйте, делитесь с друзьями! Все для вас!</p>', '<p>Below is a list of current tourism news that our experts pick around InfoSpace and quickly spread to please all our customers. Welcome, read, rate, comment, share with friends! All for you!</p>'),
				'rewrite' => 'news/novosti-turizma',
			),
			array(
				'id' => 2,
				'name' => array('Новости компании', 'Company news'),
				'rewrite' => 'news/novosti-kompanii',
			)
		),
		'news' => array(
			array(
				'id' => 1,
				'name' => array('Туристский фестиваль «Скитулец» определил сильнейших!', ''),
				'anons' => array('<p>«СКИТУЛЕЦ» – ежегодный фестиваль туристов, проводимый в апреле на реке Вашана (Алексинский район Тульской области) в период весеннего паводка.</p>',
				''),
				'text' => array('<p>История «Скитульца» ведется с 2006 г., когда на берег Вашаны приехало чуть более сорока туристов-водников, чтобы сплавом по реке отметить начало нового туристского сезона.</p>
					<p>За прошедшие годы «Скитулец» вырос в хорошо отлаженное массовое мероприятие с исключительной дружеской атмосферой, в котором принимают участие до тысячи человек участников и гостей.</p>
					<p>Организатором фестиваля выступают один из основателей сервера «Скиталец» Михаил Трусков и его жена Инна. Им помогают множество добровольных помощников из Тулы, Москвы, Орла, Санкт-Петербурга.</p>
					<p>В этом году в их славные ряды влилась Компания «НОВА ТУР», предоставив беспрецедентное количество призов во ВСЕХ номинациях спортивной и развлекательной программы фестиваля.</p>
					<p>На старт водной части фестиваля в этот раз 20 апреля вышло более 300 экипажей от 1-го до 4-х человек.</p>
					<p>За призы на дистанции боролись спортсмены на разных классах судов (пластиковые, каркасные и надувные одно и двухместные каяки и байдарки) отдельно женские, мужские и смешанные экипажи. Всего 42 призовых места. Победителям вручались термоса «Ти-Рэкс 1400» за 2-е места термоса «Ти-Рэкс 1200» за третьи «Ти-Рекс 1000». Компания «Нова Тур» уверена, что в дальнейшем они помогут туристам согреться горячим чаем, после тяжелого сплава в холодной весенней воде.</p>',
				''),
				'rel' => array(5, 10),
				'cat_id' => 2,
				'images' => array(
					'237_turistskiy-festivalskitu.jpg',
					'238_turistskiy-festivalskitu.jpg',
					'239_turistskiy-festivalskitu.jpg',
				),
				'rewrite' => 'news/novosti-turizma/turistskiy-festivalskitulets-opredelil-silneyshikh',
			),
			array(
				'id' => 2,
				'name' => array('С День защитника Отечества!', ''),
				'anons' => array('<p>Мы поздравляем Вас тепло,<br />С Днем армии и флота,<br />Пусть будет радость от того,<br />Что помнит, чтит и любит кто-то.</p>',
				''),
				'text' => array('<p>И пусть улыбка промелькнет, <br />И пусть разгладятся морщины, <br />И пусть весна в душе поет, <br />Сегодня праздник Ваш, мужчины!!!</p>',
				''),
				'rel' => 19,
				'cat_id' => 2,
				'images' => array(
					'241_s-den-zaschitnika-otechestva.jpg'
				),
				'rewrite' => 'news/novosti-kompanii/s-den-zaschitnika-otechestva',
			),
			array(
				'id' => 3,
				'name' => array('Поздравляем с Днём весны!!', ''),
				'anons' => array('<p>Дорогие наши женщины, коллектив Компании «НОВА ТУР» сердечно поздравляет Вас с весенним праздником!!!</p>',
				''),
				'text' => array('<p>Ежедневные трансляции о прошедшем дне на Клубном Астраханском Фестивале «ВЕСЕННИЙ ТРОФЕЙ 2013». День четвёртый. СПЕШИТЕ ВИДЕТЬ!!!</p>',
				''),
				'rel' => 19,
				'cat_id' => 2,
				'images' => array(
					'242_pozdravlyaem-s-dnyom-vesny.jpg'
				),
				'rewrite' => 'news/novosti-kompanii/pozdravlyaem-s-dnyom-vesny',
			),
			array(
				'id' => 4,
				'name' => array('Рыбачьте с нами', ''),
				'anons' => array('<p>В период с 30 марта по 06 апреля 2013г. Приглашаем Вас принять участие в рыболовном Фестивале «РАСКАТЫ 2013»</p>',
				''),
				'text' => array('<p>Организатор Фестиваля – Рыболовный клуб «Русфишинг»<br />В числе спонсоров Компании «НОВА ТУР» и эксклюзивные призы NOVA TOUR самым энергичным.</p><p>Место проведения: Астрахань. Дельта Волги.<br />Рыболовно-охотничья база «МОСКОВСКАЯ»</p><p>Рыболовно-охотничья база Московская, в дельте реки Волга – край изобилия рыбы, пернатой водоплавающей дичи, южного солнца – и край Величественного лотоса. Отдых, охота и рыбалка Астрахани это то в чем нам нет равных. Мы сделаем вашу рыбалку в Астрахани незабываемой. Рыболовная база расположена южнее города Астрахани, (40-50 минут по асфальтированной дороге) на берегу живописной реки Кизань в низовьях Волги. Рыбалка в Астрахани на рыболовной базе Московская – Ваш лучший выбор!</p><p>Принять участие в Фестивале могут не только члены Клуба, но и Ваши родственники, друзья, знакомые! Присоединяйтесь к нашему Дружному коллективу!</p>',
				''),
				'cat_id' => 2,
				'images' => array(
					'243_rybachte-s-nami.jpg'
				),
				'rewrite' => 'news/novosti-kompanii/rybachte-s-nami',
			),
			array(
				'id' => 5,
				'name' => array('Весенний трофей 2013.', ''),
				'anons' => array('<p>Продолжаем смотреть РЫБАЦКИЕ ВИДЕО-ВЕСТИ!</p>',
				''),
				'text' => array('<p>Ежедневные трансляции о прошедшем дне на Клубном Астраханском Фестивале «ВЕСЕННИЙ ТРОФЕЙ 2013». День четвёртый. СПЕШИТЕ ВИДЕТЬ!!!</p>',
				''),
				'rel' => 1,
				'cat_id' => 2,
				'images' => array(
					'244_nova-tour-i-vesenniy-trofey-2013-.jpg'
				),
				'rewrite' => 'news/novosti-kompanii/vesenniy-trofey-2013-',
			),
			array(
				'id' => 6,
				'name' => array('Рюкзаки, палатки, спальники', ''),
				'anons' => array('<p>Сегодня во Владивостоке на Набережной Цесаревича, прошло торжественное открытие чемпионата России по спортивному туризму на пешеходных дистанциях.</p>',
				''),
				'text' => array('<p>Самый быстрые, смелые, ловкие по итогам соревнований станут обладателями призов и подарков от NOVA TOUR - палаток Смарт, спальных мешков Сибирь и Алтай, рюкзаков Слим и Блэк айс!</p><p>Соревнования пройдут с 19 по 25 сентября 2013 года в Приморском крае в Надеждинском районе, поселок «Девятый Вал», центр соревнований – турбаза «Волна», 40 км от города Владивостока.</p><p>В Чемпионате России будут участвовать сборные команды 18 регионов Российской Федерации, представители Белгородской, Кемеровской, Свердловской, Липецкой, Новосибирской, Вологодской областей; Красноярского, Камчатского, Ставропольского, Приморского, Хабаровского краев; Ямало-Ненецкого и Ханты-Мансийского автономных округов, Еврейской автономной области, Чувашской республики и республики Марий Эл, городов Москвы и Санкт-Петербурга.</p><p>В соревнованиях примут участие подростки и молодежь в возрасте от 16 лет, спортсмены разрядов высшей категории – кандидаты в мастера спорта, мастера спорта по спортивному туризму.</p><p><strong>Программа соревнований</strong></p><p>17-18 сентября – заезд команд.</p><p>18 сентября – заезд команд. Работа комиссии по допуску и технической комиссии. Официальная тренировка.</p><p>19 сентября – обзорная экскурсия по г. Владивостоку. Торжественное открытие чемпионата.</p><p>20 сентября – соревнования в дисциплине «дистанция пешеходная связка» (короткая).</p><p>21 сентября – соревнования в дисциплине «дистанция пешеходная» (короткая).</p><p>22 сентября – официальная тренировка.</p><p>23 сентября – соревнования в дисциплине «дистанция-пешеходная-связка» (длинная).</p><p>24 сентября – соревнования в дисциплине «дистанция-пешеходная-группа» (длинная). Закрытие чемпионата. Награждение.</p><p>25 сентября – отъезд команд.</p>',
				''),
				'cat_id' => 2,
				'images' => array(
					'245_ryukzaki-palatki-spalniki.jpg'
				),
				'rewrite' => 'news/novosti-kompanii/ryukzaki-palatki-spalniki',
			),
			array(
				'id' => 7,
				'name' => array('Наша компания в СНГ', ''),
				'anons' => array('<p>В Минске с 25 по 29 сентября прошла традиционная международная специализированная выставка «ОХОТА И РЫБОЛОВСТВО. ОСЕНЬ 2013», в которой принял участие наш партнёр ОДО Синтон.</p>',
				''),
				'text' => array('<p>На стенде были выставлены новинки зимней серии Fisherman NOVA TOUR и Hunter NOVA TOUR.</p>',
				''),
				'cat_id' => 2,
				'images' => array(
					'248_nova-tour-v-sng.jpg',
					'247_nova-tour-v-sng.jpg',
					'246_nova-tour-v-sng.jpg',
					'249_nova-tour-v-sng.jpg',
				),
				'rewrite' => 'news/novosti-kompanii/nasha-kompaniya-v-sng',
			),
			array(
				'id' => 8,
				'name' => array('Бегом к призам', ''),
				'anons' => array('<p>На старт «Первенства города Москвы по спортивному ориентированию бегом» 27 октября вышло около 1000 школьников.</p>',
				''),
				'text' => array('<p>Соревнования проводились в максимально удобном для размещения месте, школе №629 в районе метро Анино. Старт и финиш были на территории школы. Каждой группе участников было необходимо найти и отметить на местности определенное количество контрольных пунктов. Соревнования стали настоящим праздником для детворы и их родителей. Самые быстрые и ловки не ушли без подарков от Компании НОВА ТУР, став обладателями термосов, наплечных сумок и т.д.</p>',
				''),
				'cat_id' => 2,
				'images' => array(
					'250_begom-k-prizam-ot-nova-tour.jpg',
					'251_begom-k-prizam-ot-nova-tour.jpg',
					'252_begom-k-prizam-ot-nova-tour.jpg',
					'253_begom-k-prizam-ot-nova-tour.jpg',
				),
				'rewrite' => 'news/novosti-kompanii/begom-k-prizam',
			),
			array(
				'id' => 9,
				'name' => array('В продажу поступили новые прочные тенты', 'New strong awnings went on sale'),
				'anons' => array('<p>Водонепроницаемый бесшовный тент с повышенной прочностью и долговечностью материала, стойкого к ультрофиолету, сослужит Вам добрую службу многи годы.</p>',
				'<p>The waterproof seamless awning with an increased durability and durability of the material resistant to an ultraviolet, will serve to you kind service many years.</p>'),
				'text' => array('<p>Защитит Вас и ваше снаряжение в самых тяжелых и сложных погодных условиях. Это самый прочный тент в нашей линейке имеет 3-х слойный материал повышенной плотности. Металлические люверсы, специальное усиление на углах, отсутствие швов и корд по периметру позволят надежно закрепить его даже при штормовом ветре. Незаменимая вещь в вашем увлечении, работе или отдыхе.<br />Уникальное товарное предложение: Повышенная прочность и увеличенный срок службы в самых тяжелых условиях эксплуатации</p><p></p>',
				'<p>Will protect you and your equipment in the most severe and difficult weather conditions. It is the strongest awning in our ruler has a 3-layer material of the increased density. Metal люверсы, special strengthening on corners, lack of seams and a cord on perimeter will allow to fix reliably it even at a gale. Irreplaceable thing in your hobby, work or rest.<br />Unique commodity offer: The increased durability and the increased service life in the most severe conditions of operation</p>'),
				'cat_id' => 2,
				'images' => array(
					'254_v-prodazhu-postupili-novye-p.jpg'
				),
				'rewrite' => 'news/novosti-kompanii/v-prodazhu-postupili-novye-prochnye-tenty',
			),
			array(
				'id' => 10,
				'name' => array('13-й турнир по спортивному туризму CLIMBER-2012', '',),
				'anons' => array('<p>В Майкопском районе Адыгеи с 15 по 18 ноября на базе отдыха «Горная Легенда» проходит 13-й турнир по спортивному туризму CLIMBER-2012 и впервые соревнования CLIMBER-JUNIOR.</p>',
				''),
				'text' => array('<p>В них примут участие как титулованные, так и начинающие спортсмены из Адыгеи, Краснодарского и Ставропольского краев, Ростовской области.</p><p>15 ноября начала работать мандатная комиссия, техническая комиссия, пройдет официальная тренировка, совещание ГСК с представителями команд. Торжественное открытие турнира и соревнования в дисциплине «Дистанция – пешеходная» (длинная) 4 класса состоятся 16 ноября. Итоги будут подведены 18 ноября.</p><p>Сейчас соревнования проводятся за счет Федерации спортивного туризма РА при участии спонсоров – краснодарского магазина «Точка отрыва» и Российской компании по производству снаряжения для туризма NOVA TOUR. Приглашаем всех Вас поддержать спортсменов!</p>',
				''),
				'cat_id' => 1,
				'rel' => 1,
				'rewrite' => 'news/novosti-turizma/13-y-turnir-po-sportivnomu-turizmu-climber-2012',
			),
			array(
				'id' => 11,
				'name' => array('XVI Всероссийские соревнования по спортивному туризму «Гонки четырех» состоялись в Подмосковье',  ''),
				'anons' => array('<p>10–11 ноября в зоне отдыха «Волкушинский карьер» в городе Лыткарино (Московская область) прошли XVI Всероссийские массовые соревнования по спортивному туризму «Гонки четырёх».</p>',
				''),
				'text' => array('<p>В них приняли участие более 600 команд (свыше 2,5 тыс. человек) из 28 субъектов Российской Федерации. Участники соревнований попробовали свои силы на пяти дистанциях, различающихся по протяжённости и трудности препятствий.<br />Победители и призёры соревнований были награждены дипломами и памятными призами.<br />Напомним, что соревнования «Гонки четырех» проводятся с 1997 года и за последнее время стали одними из наиболее массовых мероприятий по спортивному туризму в России. Соревнования проводятся в целях популяризации спортивного туризма как вида спорта и привлечения к занятиям спортивным туризмом учащихся и молодежи.</p><p></p>',
				''),
				'cat_id' => 1,
				'images' => array(
					'256_xvi-vserossiyskie-sorevnova.jpg',
					'257_xvi-vserossiyskie-sorevnova.jpg',
					'258_xvi-vserossiyskie-sorevnova.jpg',
					'259_xvi-vserossiyskie-sorevnova.jpg',
				),
				'rewrite' => 'news/novosti-turizma/xvi-vserossiyskie-sorevnovaniya-po-sportivnomu-tur',
			),
			array(
				'id' => 12,
				'name' => array('В Адыгее подвели итоги 13-го турнира по спортивному туризму CLIMBER-2012', 'In Adygea summed up the results of the 13th tournament on sports tourism of CLIMBER-2012'),
				'anons' => array('<p>В Адыгее подвели итоги 13-го турнира по спортивному туризму CLIMBER-2012 (в переводе – скалолаз) и первых соревнований CLIMBER-JUNIOR, которые проходили в Майкопском районе республики с 15 по 18 ноября.</p>',
				'<p>In Adygea summed up the results of the 13th tournament on sports tourism of CLIMBER-2012 (in translation – the rock-climber) and the first competitions CLIMBER-JUNIOR which took place in the Maikop area of the republic from November 15 to November 18.</p>'),
				'text' => array('<p>Как сообщали ЮГА.ру, соревнования среди профессионалов проводились на дистанциях высокого уровня сложности (4 класса) по дисциплинам «Дистанция – пешеходная – связка» (короткая), «Дистанция – пешеходная» (длинная). В CLIMBER-2012 спортсменам предстояло преодолеть 9 этапов в короткой дистанции на 1,6 км и столько же на длинной дистанции (8 км). Параллельно с ними дистанции 2 класса сложности проходили юные многоборцы. </p><p>Всего в соревнованиях приняли участие около 50 профессионалов и более 100 начинающих спортсменов из Адыгеи, Краснодарского и Ставропольского краев, Ростовской области. Победилети совернования были награждены подарками от Компании НОВА ТУР.</p><p>Среди профессионалов лидерами практически во всех дисциплинах стали спортсмены из Адыгеи. Так, в личном первенстве на «Дистанции – пешеходная» (длинная) победу одержал Дмитрий Кузнецов из Адыгеи (президент Федерации спортивного туризма РА), на втором месте – Николай Купин, также из Адыгеи, на третьем – Александр Шалимов, выступавший от МГГТК АГУ. </p><p>Среди женщин в этой дисциплине первое место у Алены Чесноковой, представляющей Ставропольский и Краснодарский края, второе место завоевала Таисия Видюкова из Анапы, третьей пришла Вероника Башкатова, также из Анапы. </p><p>В дисциплине «Дистанция – пешеходная – связка» (короткая) победили Николай Купин и Кирилл Линкеев (Майкоп – Анапа), на втором месте оказались Александр Шалимов и Никита Федоров (МГГТК АГУ), третье место заняли Дмитрий Кузнецов и Алексей Стрикица (Майкоп). </p><p>В CLIMBER-JUNIOR соревнования проходили по трем возрастным категориям – от 10 до 18 лет. Активное участие в них приняли спортсмены из Краснодарского края. Практически половину всех участников представляли спортсмены из Армавира, а также Абинска, солидная делегация была из Ставропольского края и только около 15 человек – из Адыгеи, представлявшие МГГТК АГУ и СОШ №4 п. Победа Майкопского района, которые сумели в разных группах добиться победы.</p><p>«Одним из итогов 13-го турнира по спортивному туризму CLIMBER-2012 стало определение кандидата в клуб Чемпион «CLIMBER», который состоит из 17 победителей. В результате успешного выступления сразу в двух дисциплинах 18-м участником клуба стала Алена Чеснокова. Она получит зеленый именной жилет с символикой – почетный атрибут, объединяющий членов клуба», – рассказал организатор и главный судья соревнований Алий Хатков.</p>',
				'<p>As told to YuGA.R, competitions among professionals were held at distances of high level of complexity (4 classes) on disciplines "A distance – foot – a sheaf" (short), "A distance – foot" (long). In CLIMBER-2012 athletes should overcome 9 stages in a short distance on 1,6 km and as much at a long distance (8 km). In parallel with them distances 2 classes of complexity there passed young all-rounders.</p>
<p>In total about 50 professionals and more than 100 beginning athletes took part in competitions from Adygea, the Krasnodar and Stavropol edges, the Rostov region. Pobedileti sovernovaniye were awarded by gifts from the ROUND is NEW Company.</p>
<p>Among professionals athletes from Adygea became leaders practically in all disciplines. So, in individual competition at "A distance – foot" (long) the victory was won by Dmitry Kuznetsov from Adygea (the president of Federation of sports tourism of RA), on the second place – Nikolay Kupin, also from Adygea, on the third – Alexander Shalimov acting from MGGTK AGA.</p>
<p>Among women in this discipline the first place at Alyona Chesnokova representing the Stavropol and Krasnodar edges, the second place was won by Taisiya Vidyukova from Anapa, the third Veronika Bashkatova, also came from Anapa.</p>
<p>The discipline "A distance – foot – a sheaf" (short) was won by Nikolay Kupin and Kirill Linkeev (Maikop – Anapa), on the second place appeared Alexander Shalimov and Nikita Fedorov (MGGTK AGA), the third place took Dmitry Kuznetsov and Alexey Strikitsa (Maikop).</p>
<p>In CLIMBER-JUNIOR of competition took place on three age categories – from 10 to 18 years. Active part in them was taken by athletes from Krasnodar Krai. Nearly a half of all participants was represented by athletes from Armavir, and also Abinsk, the solid delegation was from Stavropol Krai and only about 15 people – from Adygea, representing MGGTK AGA and SOSh No. 4 of the item. Victory of the Maikop area who managed to achieve a victory in different groups.</p>
<p>"Definition of the candidate for Champion of CLIMBER club which consists of 17 winners became one of results of the 13th tournament on sports tourism of CLIMBER-2012. As a result of successful performance at once in two disciplines Alyona Chesnokova became the 18th participant of club. She will receive a green nominal vest with symbolics – the honourable attribute uniting clubmen", – the organizer and the chief judge of competitions Aly Hatkov told.</p>'),
				'cat_id' => array(1, 2),
				'images' => array(
					'260_v-adygee-podveli-itogi-13-go-.jpg',
					'261_v-adygee-podveli-itogi-13-go-.jpg',
				),
				'rewrite' => 'news/novosti-turizma/v-adygee-podveli-itogi-13-go-turnira-po-sportivnom',
			),
			array(
				'id' => 13,
				'name' => array('«Карелия: Северный полюс – южная оконечность острова Гренландия»', ''),
				'anons' => array('<p>Сегодня стартовала первая в мире экспедиция на собачьих упряжках «Карелия: Северный полюс – южная оконечность острова Гренландия»</p>',
				''),
				'text' => array('<p>Два российских первооткрывателя Федор Конюхов и Виктор Симонов пройдут на собачьих упряжках по пути, который до сих пор является неприступным. За 4 месяца путешественники намерены покорить более 4 тысяч километров ледяных просторов, закончив свое путешествие на южном берегу острова Гренландия. Это самый протяженный маршрут в Арктике, который станет экстремальным испытание человеческих возможностей.</p><p>Вместе с путешественниками пройдёт испытания и снаряжение NOVA TOUR. Специально для Федора Конюхова, с учётом его пожеланий, отшиты уникальные костюмы.</p>',
				''),
				'cat_id' => 1,
				'images' => array(
					'263_kareliya-severnyy-polyus--.jpg',
					'264_kareliya-severnyy-polyus--.jpg',
				),
				'rewrite' => 'news/novosti-turizma/kareliya-severnyy-polyus--yuzhnaya-okonechnost-ost',
			),
			array(
				'id' => 14,
				'name' => array('Неделя в Арктике: поход нормальный!', ''),
				'anons' => array('<p>Неделя в Арктике: поход нормальный!</p>',
				''),
				'text' => array('<p>Восьмые сутки находятся в арктической экспедиции Федор Конюхов и Виктор Симонов. За это время они преодолели на собачьей упряжке первую сотню километров по дрейфующему льду Северного Ледовитого океана и уверенно движутся к югу вдоль 55-го меридиана курсом на гренландский фьорд Виктория. Самочувствие участников экстремального похода: людей и собак, – нормальное.</p><p>Уходя в арктическую экспедицию, Федор Конюхов не мог не высказать слова признательности тем, кто помог воплотить в реальность его давнюю мечту:</p><p>- Более 20 лет я не стоял на дрейфующем льду (в 1990 году Ф.Конюхов в одиночку достиг Северного полюса на лыжах – прим. ред.). Для человека это большой срок, но для суровой арктической природы – мгновение. Здесь, в высоких широтах Земли в районе Северного полюса, все по-прежнему: мороз, нагромождения торосов, многолетний лед чередуется с открытой водой.</p><p>Для меня старт – это уже очень большое событие. Я всегда разделяю экспедиционный проект на два этапа: подготовка и сама экспедиции. Оба этапа имеют равнозначное по степени важности значение. Как говорил японский путешественник Наоми Уэмура, из любой экспедиции можно вернуться, если она хорошо подготовлена. Однако уже выход на старт означает, что мы проделали огромную работу «на берегу». Если говорить полярными терминами, от идеи до старта простирается огромная полынья, преодолеть которую могут лишь единицы и это результат труда большой команды.</p><p>Нашу экспедицию «Карелия – Северный полюс – Гренландия» готовили почти год. Тем, что сейчас я и Виктор Симонов находимся в Арктике и движемся в сторону Гренландии, мы обязаны многим людям и компаниям. И хотя на льду нас всего двое, я насчитал более сотни людей, которые на разных этапах были вовлечены в подготовку экспедиции.</p><p></p>',
				''),
				'cat_id' => 1,
				'rewrite' => 'news/novosti-turizma/nedelya-v-arktike-pokhod-normalnyy',
			),
			array(
				'id' => 15,
				'name' => array('Сеанс связи с Федором Конюховым', 'Communication session with Fedor Konyukhov'),
				'anons' => array('<p>28.04.2013. Сеанс связи с Федором Конюховым. Экспедиция прошла половину пути по дрейфующему льду Северного Ледовитого океана</p>',
				'<p>Communication session with Fedor Konyukhov. Expedition took place a way half on drifting ice of the Arctic Ocean</p>'),
				'text' => array('<p>«Сегодня прошли 21 километр, встретили 4 трещины за день. Все трещины затянуты молодым льдом, без снега, фактически мы шли по гнущемуся льду, это нам удалось только из-за полупустых нарт. Если бы нарты весили, как в первые дни после старта, наверняка провалились бы. Этот день знаковый для нас - преодолели воображаемую линию - «половина пути». Маршрут экспедиции разделен на несколько этапов и первый этап, на наш взгляд самый сложный, от Северного полюса до берегов Гренландии, по дрейфующему льду Северного Ледовитого океана. На сегодняшний день половина этого этапа пройдена, но впереди начинается самая тяжелая часть. Согласно изображениям со спутника, перед нами огромная полоса открытой воды, которая тянется с севера-запада на юго-восток на сотню километров и как раз пересекает нам путь. Пока не понятно, как её форсировать, будем на месте решать, но спасибо коллегам из Арктического и Антарктического НИИ, - предупрежден, значит вооружен! Проблема еще и в том, что нам необходимо завтра-послезавтра найти относительно ровную и надежную льдину для организации взлетно-посадочной полосы для канадского самолета. Наши координаты на 21:00 по Москве: 86 градусов 11 минут Северной широты, 59 градусов и 22 минуты Западной долготы. До Гренландии 350 километров. Завтра еще идем по полной программе, а вот 30 апреля начинаем подыскивать льдину, и будем стоять, ждать самолет канадской компании Kenn Borek Air, который должен доставить корм, питание и новую (самое главное – сухую) экипировку. Также планируем стоять 1 мая, будем перегружать полученный корм и другой полезный груз из бочек в сумки, ну и собакам нужно дать отдохнуть перед началом работы с тяжелыми нартами.</p><p>Прогноз погоды нас настораживает:</p><p>30 апреля. Ветер в течение суток северо-западный, западный 2-7 м/с.</p><p>Временами небольшой снег. Температура воздуха -12&hellip;-17&deg;С.</p><p>1 мая. Ветер в течение суток западный 2-7 м/с.</p><p>Временами небольшой снег. Температура воздуха -9&hellip;-14&deg;С.</p><p>Становится очень тепло. Нам чем холоднее и морознее, тем лучше, но лето надвигается, и мы ничего не можем с этим поделать. Будем с Божьей помощью выбираться из этих широт на Гренландскую твердь. Всем поклон. Федор и Виктор»</p><p></p>',
				'<p>"Today passed 21 kilometers, met 4 cracks in a day. All cracks are tightened by young ice, without snow, actually we went on bending ice, we managed it only because of thin sledge. If sledge weighed as in the first days after start, for certain would fail. This day sign for us - overcame the imagined line - "a way half". The route of expedition is divided into some stages and the first stage which is in our opinion most difficult, from the North Pole to coast of Greenland, on drifting ice of the Arctic Ocean. Today a half of this stage is passed, but the heaviest part ahead begins. According to images from the satellite, before us the huge strip of open water which lasts from the North-West on the southeast on one hundred kilometers and just cuts us off. It isn\'t clear yet as to force it, on a place we will solve, but thanks to colleagues from the Arctic and Antarctic scientific research institute, - it is warned, means it is armed! Problem also that we need to find tomorrow-the day after tomorrow rather equal and reliable ice floe for the runway organization for the Canadian plane. Our coordinates at 21:00 across Moscow: 86 degrees of 11 minutes of the Northern latitude, 59 degrees and 22 minutes of the Western longitude. To Greenland of 350 kilometers. Tomorrow still we go according to the full program, and here on April 30 we start looking for an ice floe, and we will stand, wait the plane of the Canadian company Kenn Borek Air which has to deliver a forage, food and new (the most important – dry) equipment. Also we plan to stand on May 1, we will overload the received forage and other payload from barrels in bags, well and to dogs it is necessary to allow to have a rest before work with heavy sledge.</p>
<p>The weather forecast guards us:</p>
<p>April 30. Wind within a day northwest, western 2-7 m/s.</p>
<p>Times light snow. Air temperature-12 &hellip; -17 &deg;C.</p>
<p>May 1. Wind within a day western 2-7 m/s.</p>
<p>Times light snow. Air temperature-9 &hellip; -14 &deg;C.</p>
<p>It becomes very warm. To us the more cold and morozny, the better, but the summer approaches, and we can do nothing with it. With the God\'s help we will get out of these widths on Greenland твердь. All bow. Fedor and Victor"</p>'),
				'cat_id' => 1,
				'rel' => 20,
				'images' => array(
					'266_seans-svyazi-s-fedorom-konyukh.jpg',
					'267_seans-svyazi-s-fedorom-konyukh.jpg',
					'268_seans-svyazi-s-fedorom-konyukh.jpg'
				),
				'rewrite' => 'news/novosti-turizma/seans-svyazi-s-fedorom-konyukhovym',
			),
			array(
				'id' => 16,
				'name' => array('Экспедиция Федора Конюхова и Виктора Симонова достигла побережья Канады!', 'Fedor Konyukhov and Victor Simonov\'s expedition reached the coast of Canada!'),
				'anons' => array('<p>Поздним вечером 22 мая 2013 российские путешественники Федор Конюхов (Москва) и Виктор Симонов (Республика Карелия, Петрозаводск) управляя упряжкой из 12 собак, достигли побережья Канады, мыс Колумбия (Cape Columbia).</p>',
				'<p>Late evening on May 22, 2013 the Russian travelers Fedor Konyukhov (Moscow) and Victor Simonov (the Republic of Karelia, Petrozavodsk) operating a team from 12 dogs, reached the coast of Canada, the cape Colombia (Cape Columbia).</p>'),
				'text' => array('<p>Впервые россияне смогли пересечь Северный ледовитый океан от Северного Географического полюса до побережья Канады на собачьих упряжках. Завершился первый этап экспедиции: Карелия – Северный полюс – Канада, далее путешественники планируют перебраться в Гренландию с тем, чтобы пересечь остров с севера на юг за один сезон. &nbsp;</p><p>Федор Конюхов: «Сегодня мы смогли совершить заключительный рывок и выйти с дрейфующего льда на припайный лед, и далее на берег Канады. Последние несколько километров по дрейфующему льду нам приходилось буквально продираться через пятиметровые стены из торосов, но собаки, почувствовав землю, уже шли не останавливаясь. И вот мы вышли на побережье. Не верится, что у нас за плечами осталась 900 километров океана, который мы прошли по льду на собачьей упряжке. Стоим на твердой земле, а нас качает из стороны в сторону, как после кругосветки на яхте. Собаки радуются, нюхают воздух и лижут камни. Мы с Виктором тоже взяли себе несколько камушков в карман, на память об этом историческом месте. С мыса Колумбия к Северному полюсу в 1909 стартовала экспедиция Роберта Пири, в 1979 году к полюсу отправился Наоми Уэмура. &nbsp;</p><p>46 суток мы находились в пути, на дрейфующем льду Северного Ледовитого Океана. Пройдено более 900 километров. Мы сохранили всех собак, даже лапу никто не повредил. Да, конечно, они похудели и устали, но в целом они в отличной форме и показали себя как выдающиеся бойцы. Если бы сезон позволял, то после короткого отдыха можно было начать путь обратно, в сторону полюса. &nbsp;</p><p>Достигнув мыса Колумбия (остров Эльсмир), мы начали двигаться вдоль побережья в сторону острова Уорт Хант. Это еще 20 километров. Сейчас мы вам звоним во время короткой остановки. Сидим на нартах, пьем чай. Стоит звенящая тишина, светит яркое солнце – затишье перед бурей. Мы остановились в красивом месте в заливе, вокруг нас вмерзшие в лед айсберги. Так приятно видеть горы и скалы, это прямо терапия для наших глаз. &nbsp;</p><p>Рассчитываем через пару часов добраться до острова Уорт-Хант и стать лагерем. На этом, этап экспедиции от Северного полюса до побережья Канады – завершен. Далее планируем перебраться на побережье Гренландии и начать пересекать остров по самой большой длине, с севера на юг. Протяжённость маршрута более 2500 километров.</p><p>22 мая – день Николая Чудотворца.Этот путь мы преодолели с Божьей помощью.</p><p>Спасибо всем, кто поддерживал экспедицию и переживал за нас.</p><p>Федор Конюхов, Виктор Симонов. Мыс Колумбия, Канада».</p>',
				'<p>For the first time Russians could cross the Arctic Ocean from the North Geographical Pole to the coast of Canada on dogsleds. The first stage of expedition came to the end: Karelia – the North Pole – Canada, further travelers plan to get over to Greenland to cross the island from the North to the south for one season.</p>
<p>Fedor Konyukhov: "Today we could make final breakthrough and to come from drifting ice to pripayny ice, and further to the coast of Canada. The last some kilometers on drifting ice to us were necessary to be torn literally through five-meter walls from hummocks, but a dog, having felt the earth, already went without stopping. And here we came to the coast. It isn\'t believed that behind shoulders I remained with us 900 kilometers of the ocean which we passed on ice on a dogsled. We stand on the firm earth, and us swings here and there, as after circumnavigation on the yacht. Dogs rejoice, smell air and lick stones. We with Victor too took ourselves some pebbles in a pocket, for memory of this historical place. From the cape Colombia to the North Pole in 1909 Robert Peary\'s expedition started, in 1979 to a pole went Naomi Uemura.</p>
<p>46 days we were en route, on drifting ice of the Arctic Ocean. More than 900 kilometers are passed. We kept all dogs, even nobody injured a paw. Yes, of course, they grew thin and were tired, but as a whole they in an excellent form and proved to be as outstanding fighters. If the season allowed, after short rest it was possible to begin a way back, towards a pole.</p>
<p>Having reached the cape Colombia (the island Elsmir), we started moving along the coast towards the island Uort Hunt. These are 20 more kilometers. Now we ring you time of a short stop. We sit on sledge, we drink tea. There is a ringing silence, the bright sun – calm before a storm shines. We stopped in a beautiful place in the gulf, round us the icebergs which have frozen in ice. It is so pleasant to see mountains and rocks, it directly therapy for our eyes.</p>
<p>We expect to reach through a couple of hours the island Uort-Hunt and to camp. On it, the expedition stage from the North Pole to the coast of Canada – is complete. Further we plan to get over on the coast of Greenland and to start crossing the island on the biggest length, from the North to the south. Extent of a route is more than 2500 kilometers.</p>
<p>On May 22 – day of Nikolay Chudotvorts. We overcame this way with the God\'s help.</p>
<p>And I endured thanks to all who supported expedition about us.</p>
<p>Fedor Konyukhov, Victor Simonov. Cape Colombia, Canada".</p>'),
				'cat_id' => 1,
				'rel' => 20,
				'images' => array(
					'269_ekspeditsiya-fedora-konyukhova.jpg'
				),
				'rewrite' => 'news/novosti-turizma/ekspeditsiya-fedora-konyukhova-i-viktora-simonova-',
			),
			array(
				'id' => 17,
				'name' => array('Детский туризм без границ!', ''),
				'anons' => array('<p>В Калужской области завершил работу VIII туристский слет учащихся Беларуси и России.</p>',
				''),
				'text' => array('<p>В слете приняли участие семь команд Республики Беларусь и 34 команды из 34 регионов России, в том числе из Башкирии, Татарстана, Республики Марий Эл, Ставропольского, Краснодарского краев, Белгородской, Волгоградской, Брянской, Ивановской, Кировской областей.</p><p>&nbsp;</p><p>Немного истории: Первый слет юных туристов России и Беларуси прошел в Смоленской области в 2001 году, в нем приняла участие шестьдесят одна команда, девять из них представляли Беларусь. А на следующий год 80 директоров центров и станций юных туристов России посетили Беларусь, познакомились с работой своих белорусских коллег.</p><p>Туристско-краеведческая работа вышла на новый виток развития после подключения к проведению белорусско-российских туристских слетов Постоянного комитета Союзного государства. В 2004 году на базе Олимпийского спортивного комплекса «Раубичи» под Минском был проведен второй туристский слет учащихся Беларуси и России, в котором приняли участие около 600 юных любителей туризма из 39 регионов Российской Федерации и всех областей Республики Беларусь. В мае 2006 года в «Раубичах» при активной поддержке Постоянного комитета Союзного государства был проведен слет юных туристов городов-героев, посвященный победе в Великой Отечественной войне. В слете приняли участие делегации городов-героев Москвы, Минска, Санкт-Петербурга, Волгограда, Мурманска, Тулы, Смоленска, Бреста.</p><p>Особенностью нынешнего слета является то, что в нем приняли участие помимо команд России и Беларуси команды юных туристов Украины. Боевое крещение туристы получили по дороге в лагерь, когда обрушились ливень и штормовой ветер. А ведь утром ничего не предвещало грозы. Непогода не ослабила энтузиазм ребят, и, прибыв на место, они стали готовиться к вечеру дружбы «Давайте познакомимся!».</p><p>Программа нынешнего слета насыщена: это и конкурс краеведов, представления команд, конкурсы стенгазет «Будни слета», туристской песни, освоение техники пешеходного туризма. Оказывается, что каждый день здоровый человек должен проходить пешком до восьми километров.</p><p>Утро в лагере начинается с зарядки, затем - командные соревнования по спортивному ориентированию, подготовка к вечеру отдыха «Как здорово, что мы здесь собрались», конкурс туристических газет... По итогам турслета прошли «круглый стол» на тему совершенствования системы дополнительного образования и семинар-практикум педагогов, тренеров, организаторов детского отдыха, на котором обсуждались актуальные вопросы нормативно-правового обеспечения детского отдыха, организации внутреннего и выездного туризма, активизации детского краеведения и спортивного туризма в Союзном государстве.</p><p>Слет завершился награждением победителей. Самые сильные, смелые ловкие стали счастливыми обладателями Палаток Арди, тентов, спальных мешков Сибирь, рюкзаков Юкон, Стрей и многих других приятных подарков от NOVA TOUR.</p>',
				''),
				'cat_id' => 1,
				'images' => array(
					'270_detskiy-turizm-bez-granits.jpg',
					'271_detskiy-turizm-bez-granits.jpg',
					'272_detskiy-turizm-bez-granits.jpg',
					'273_detskiy-turizm-bez-granits.jpg',
					'274_detskiy-turizm-bez-granits.jpg',
				),
				'rewrite' => 'news/novosti-turizma/detskiy-turizm-bez-granits',
			),
			array(
				'id' => 18,
				'name' => array('Самый народный День рыбака!', ''),
				'anons' => array('<p>В рамках фестиваля NOVA TOUR устроил для участников и зрителей зажигательное шоу с презентацией новой коллекции для рыбаков - Fisherman NOVA TOUR и азартные эстефеты для всей разновозрастной аудитории)))</p>',
				''),
				'text' => array('<p>В рамках фестиваля NOVA TOUR устроил для участников и зрителей зажигательное шоу с презентацией новой коллекции для рыбаков - Fisherman NOVA TOUR и азартные эстефеты для всей разновозрастной аудитории)))</p><p>Более 600 участников собрались на VI Всероссийский этап фестиваля «Народная рыбалка», который прошел в День рыбака, 14 июля, в Москве на водоемах ВВЦ.</p><p>Вот уже в шестой раз фестиваль собрал сотни рыбаков-любителей и гостей. С шести утра пришли желающие получить самое рыбное место на водоеме. Но берег был разбит на сектора, которые разыгрывались по жребию. Исключение было сделано лишь для самых юных участников, которые ловили в специально подготовленном садке.</p><p>- Для самых юных, а не для самых маленьких, - подчеркнул заместитель председателя Оргкомитета фестиваля «Народная рыбалка», глава ФГУП «Нацрыбресурс» Станислав Стандрик.</p><p>Дело в том, что и самые маленькие могли порыбачить в этот день: специально для них была организована площадка с надувными бассейнами, в которых обитала самая настоящая, хотя и пластмассовая, рыбешка. Такие же были и снасти маленьких рыбаков. А с их уловом могли поспорить лишь самые именитые рыболовные спортсмены, прибывшие на фестиваль для проведения мастер-классов – Чемпион Европы Илья Якушин, 4-х кратный Чемпион Москвы Андрей Думчев и Чемпион мира Дмитрий Анохин.</p><p>С интервалом в пару минут каждый из них доставал из пруда карпа, две тонны которого организаторы специально выпустили в водоем перед праздником.</p><p>Любителям рыбалки – участникам фестиваля – не так сильно везло. По их словам, мешала переменчивая погода. Но дождь не стал помехой для азартной борьбы. 10 везунчиков, первые выловившие рыбу, получили ценные подарки. А семь Народных рыбаков – победителей в каждой номинации – получили возможность на новых лодках и новыми снастями отдохнуть в столице рыболовной России – Астрахани – на рыболовно-охотничей базе «Золотые барханы».</p><p>Летний этап требовал от организаторов и летних угощений для всех гостей. Все желающие смогли бесплатно отведать, пожалуй, самое оригинальное рыбное блюдо – рыбную окрошку. 300 литров этого угощения не задержалось надолго в чудо-котле.</p><p>Совсем скоро Оргкомитет начнет работать над новыми праздниками. Как рассказали по-секрету некоторые организаторы Всероссийского фестиваля «Народная рыбалка», в следующем году один из этапов пройдет в Ростовской области. Более подробно было обещано рассказать уже в сентябре.</p><p>«Народная рыбалка», ставшая главным событием 2012 года в любительском рыболовстве России, в нынешнем сезоне стартовала на Дальнем Востоке. 26 января успешно прошёл региональный этап, организованный администрацией Владивостока. А на Масленицу «Народная рыбалка» провела V Всероссийский этап в Московской области.</p><p>Успех марафона ярких рыболовных праздников основан на идеологии Всероссийского фестиваля: это бережное отношение к природе, воспитание детей и позитивное общение всех гостей и участников «Народной рыбалки».</p>',
				''),
				'cat_id' => 1,
				'images' => array(
					'275_samyy-narodnyy-den-rybaka.jpg',
					'276_samyy-narodnyy-den-rybaka.jpg',
					'277_samyy-narodnyy-den-rybaka.jpg',
					'278_samyy-narodnyy-den-rybaka.jpg',
					'279_samyy-narodnyy-den-rybaka.jpg',
				),
				'rewrite' => 'news/novosti-turizma/samyy-narodnyy-den-rybaka',
			),
			array(
				'id' => 19,
				'name' => array('С международным Днём туризма!', ''),
				'anons' => array('<p>Снова чай в котелке закипел,<br />Досыхает рюкзак у костра,<br />И гитарой бивак зазвенел,<br />Ну а завтра – в дорогу с утра&hellip;</p>',
				''),
				'text' => array('<p>День ненастный в сыром сентябре -<br />Этот праздник больших чудаков,<br />Что с дороги приходят добрей,<br />Жизнь не мысля без рюкзаков&hellip;</p><p>Это праздник далеких дорог,<br />Что ведут неизменно домой,<br />Тех, кто всем доказать что-то смог,<br />И победу познал над собой, -</p><p>Так что – с праздником, братцы-туристы,<br />Пусть вам горы не будут круты!<br />Пусть ваш путь будет гладким и чистым,<br />А рюкзак – полон доброй мечты!</p>',
				''),
				'cat_id' => 1,
				'rel' => array(2, 3),
				'images' => array(
					'280_s-mezhdunarodnym-dnyom-turiz.jpg',
				),
				'rewrite' => 'news/novosti-turizma/s-mezhdunarodnym-dnyom-turizma',
			),
			array(
				'id' => 20,
				'name' => array('Фёдор Конюхов планирует пересечь Тихий океан на вёсельной лодке!', 'Fyodor Konyukhov plans to cross the Pacific Ocean by the oar boat!'),
				'anons' => array('<p>Российский путешественник, писатель и художник, священник Русской православной церкви Фёдор Конюхов - давний друг и тестер NOVA TOUR - через две недели отправится в чилийский порт Вальпараисо, где в декабре стартует беспрецедентный одиночный переход через Тихий океан на весельной лодке.</p>', '<p>The Russian traveler, the writer and the artist, the priest of Russian Orthodox Church Fyodor Konyukhov - the old friend and NOVA TOUR tester - in two weeks will go to the Chilean port Valparaiso where in December starts unprecedented single transition through the Pacific Ocean by the oar boat.</p>'),
				'text' => array('<p>Фёдору Конюхову предстоит преодолеть расстояние в 9 тыс. морских миль (около 16 тыс. км). По предварительным расчетам, для этого ему нужно совершить 4,5 млн. гребков, работая веслами по 11 часов в день. Т.е. совершить практически немыслимое.</p><p>«В таком режиме мне надо будет потреблять около 6 тысяч калорий. Тогда я смогу выдержать 11 часов ежедневной работы. Удочки тоже прихвачу, но рыба есть не на всем пути. А вот акулы будут встречаться на протяжении всего маршрута. Лодка быстро обрастает водорослями и ракушками, а их это привлекает. И еще 50 альпинистских газовых баллонов для печки. Буду идти как топливный танкер, – позволил себе улыбнуться Конюхов, - но мне самое главное – пройти».</p><p>Маршрут через Тихий океан будет пролегать в коридоре 30-35 градусов Южной широты. Фёдору Конюхову предстоит огибать остров Робинзона Крузо, остров Пасхи, остров Питкерн и т.д. Финишировать он будет в период штормов, когда в южном полушарии уже наступит осень. При подходе к Австралии ему придется столкнуться с множеством островов и атоллов, входящих в систему Большого Барьерного Рифа и многие из которых до сих пор не обозначены на карте. </p><p>Спустя 200 дней после старта, в мае 2014 г, путешественник рассчитывает войти в порт Брисбен на восточном побережье Австралии.<br />На протяжении всего плавания специальные приборы будут снимать информацию о физическом состоянии Фёдора.</p><p>«Медики хотят знать, какие изменения происходят в организме при длительных физических и психических нагрузках в условиях изоляции и однообразном питании», – объяснил он.</p><p>Медикам очень интересно, как организм человека справится с такой нагрузкой. Для этого к телу Фёдора будут подключены всевозможные датчики, которые призваны фиксировать давление и пульс. Фёдор Филиппович, кстати, на предложение не обиделся, а только улыбнулся в окладистую бороду. Ведь как он сам говорит: «Духом я молод».</p><p>Старт экспедиции запланирован 10-12 декабря 2013.</p><p>Компания «НОВА ТУР» желает Фёдору попутного ветра и успешного исполнения задуманного!!!</p>',
				'<p>Fyodor Konyukhov should overcome distance in 9 thousand nautical miles (about 16 thousand km). On predesigns, for this purpose he needs to make 4,5 million fungi, working with oars for 11 hours a day. I.e. to make the almost inconceivable.</p>
<p>"In such mode I should consume about 6 thousand calories. Then I will be able to sustain 11 hours of daily work. Too I will take rods, but fish is not on all way. And here sharks will meet throughout all route. The boat quickly acquires seaweed and cockleshells, and it attracts them. And 50 more climbing gas cylinders for an oven. I will go as the fuel tanker, – I dared to smile Grooms, - but to me – to pass the most important".</p>
<p>The route through the Pacific Ocean will lie in a corridor of 30-35 degrees of Southern latitude. Fyodor Konyukhov should bend around Robinson Crusoe\'s island, Easter Island, the island Pitkern, etc. It will finish in the period of storm when in the southern hemisphere already there will come fall. At the way to Australia it should face a set of islands and the atolls entering into system of the Great Barrier Reef and many of which still aren\'t designated on the card.</p>
<p>200 days later after start, in May, 2014, the traveler expects to enter into Brisbane port on east coast of Australia.<br />Throughout all swimming special devices will remove information on Fyodor\'s physical condition.</p>
<p>"Physicians want to know, what changes happen in an organism at long physical and mental activities in the conditions of isolation and monotonous food", - he explained.</p>
<p>It is very interesting to physicians as the human body will cope with such loading. For this purpose various sensors which are urged to fix pressure and pulse will be connected to Fyodor\'s body. Fyodor Filippovich, by the way, didn\'t take offense at the offer but only smiled in a broad beard. After all as he speaks: "Spirit I am young".</p>
<p>Start of expedition is planned on December 10-12, 2013.</p>
<p>The It is NEW ROUND company wishes to Fyodor a fair wind and successful execution conceived!!!</p>'),
				'cat_id' => 1,
				'rel' => array(15, 16),
				'images' => array(
					'281_fyodor-konyukhov-planiruet-pe.jpg',
					'282_fyodor-konyukhov-planiruet-pe.jpg',
					'283_fyodor-konyukhov-planiruet-pe.jpg',
				),
				'rewrite' => 'news/novosti-turizma/fyodor-konyukhov-planiruet-peresech-tikhiy-okean-n',
			),
		),
	);
}

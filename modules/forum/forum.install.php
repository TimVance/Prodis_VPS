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

class Forum_install extends Install
{
	/**
	 * @var string название
	 */
	public $title = "Форум";

	/**
	 * @var array таблицы в базе данных
	 */
	public $tables = array(
		array(
			"name" => "forum_messages",
			"comment" => "Сообщения на форуме",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(20) NOT NULL DEFAULT ''",
					"comment" => "название",
				),
				array(
					"name" => "user_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users}",
				),
				array(
					"name" => "parent_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор родителя из таблицы {forum_messages}",
				),
				array(
					"name" => "count_children",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество вложенных сообщений",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата создания",
				),
				array(
					"name" => "date_update",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата редакции",
				),
				array(
					"name" => "user_update",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users}, отредактировавший сообщение",
				),
				array(
					"name" => "forum_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор темы из таблицы {forum}",
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
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY `parent_id` (`parent_id`)",
				"KEY `forum_id` (`forum_id`)",
				"KEY `user_id` (`user_id`)",
			),
		),
		array(
			"name" => "forum_messages_parents",
			"comment" => "Родительские связи сообщений",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор сообщения из таблицы {forum_messages}",
				),
				array(
					"name" => "parent_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор родителя сообщения из таблицы {forum_messages}",
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
			"name" => "forum_blocks",
			"comment" => "Блоки форума",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "название",
				),
				array(
					"name" => "sort",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "подрядковый номер для сортировки",
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)"
			),
		),
		array(
			"name" => "forum_category",
			"comment" => "Категории форума",
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
					"name" => "timeedit",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "время последнего изменения в формате UNIXTIME",
				),
				array(
					"name" => "block_id",
					"type" => "SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор блока из таблицы {forum_blocks}",
				),
				array(
					"name" => "counter_view",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество просмотров",
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
			"name" => "forum",
			"comment" => "Темы форума",
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
					"name" => "cat_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор категории из таблицы {forum_category}",
				),
				array(
					"name" => "created",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата создания",
				),
				array(
					"name" => "name",
					"type" => "VARCHAR(250) NOT NULL DEFAULT ''",
					"comment" => "название",
				),
				array(
					"name" => "date_update",
					"type" => "INT(10) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "дата редакции",
				),
				array(
					"name" => "user_update",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор пользователя из таблицы {users}, отредактировавший сообщение",
				),
				array(
					"name" => "timeedit",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "время последнего изменения в формате UNIXTIME",
				),
				array(
					"name" => "counter_view",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "количество просмотров",
				),
				array(
					"name" => "act",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "показывать на сайте: 0 - нет, 1 - да",
				),
				array(
					"name" => "prior",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "тема закреплена: 0 - нет, 1 - да",
				),
				array(
					"name" => "close",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "тема закрыта: 0 - нет, 1 - да",
				),
				array(
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY `user_id` (`user_id`)",
			),
		),
		array(
			"name" => "forum_show",
			"comment" => "Новые сообщения и темы для пользователей",
			"fields" => array(
				array(
					"name" => "id",
					"type" => "INT(11) UNSIGNED NOT NULL AUTO_INCREMENT",
					"comment" => "идентификатор",
				),
				array(
					"name" => "element_id",
					"type" => "INT(11) UNSIGNED NOT NULL DEFAULT '0'",
					"comment" => "идентификатор сообщения из таблицы {forum_messages}",
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
					"name" => "trash",
					"type" => "ENUM('0', '1') NOT NULL DEFAULT '0'",
					"comment" => "запись удалена в корзину: 0 - нет, 1 - да",
				),
			),
			"keys" => array(
				"PRIMARY KEY (id)",
				"KEY `element_id` (`element_id`)",
				"KEY `user_id` (`user_id`)",
			),
		),
	);

	/**
	 * @var array записи в таблице {modules}
	 */
	public $modules = array(
		array(
			"name" => "forum",
			"admin" => true,
			"site" => true,
			"site_page" => true,
		),
	);
	

	/**
	 * @var array страницы сайта
	 */
	public $site = array(
		array(
			"name" => array('Форум', 'Forum'),
			"act" => true,
			"module_name" => "forum",
			"rewrite" => "forum",
			"menu" => 1,
			"parent_id" => 2,
		),
	);

	/**
	 * @var array меню административной части
	 */
	public $admin = array(
		array(
			"name" => "Форум",
			"rewrite" => "forum",
			"group_id" => 2,
			"sort" => 17,
			"act" => true,
			"docs" => "http://www.diafan.ru/moduli/forum/",
			"children" => array(
				array(
					"name" => "Темы",
					"rewrite" => "forum",
					"act" => true,
				),
				array(
					"name" => "Блоки",
					"rewrite" => "forum/blocks",
					"act" => true,
				),
				array(
					"name" => "Категории",
					"rewrite" => "forum/category",
					"act" => true,
				),
				array(
					"name" => "Сообщения",
					"rewrite" => "forum/messages",
					"act" => true,
				),
				array(
					"name" => "Настройки",
					"rewrite" => "forum/config",
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
			"name" => "news_count_days",
			"value" => "3",
		),
		array(
			"name" => "count_level",
			"value" => "7",
		),
		array(
			"name" => "max_count",
			"value" => "50",
		),
		array(
			"name" => "format_date",
			"value" => "5",
		),
		array(
			"name" => "nastr",
			"value" => 30,
		),
		array(
			"name" => "attachments",
			"value" => "1",
		),
		array(
			"name" => "max_count_attachments",
			"value" => "5",
		),
		array(
			"name" => "attachment_extensions",
			"value" => "doc, gif, jpg, jpeg, mpg, pdf, png, txt, zip",
		),
		array(
			"name" => "recognize_image",
			"value" => "1",
		),
		array(
			"name" => "attach_big_width",
			"value" => "1000",
		),
		array(
			"name" => "attach_big_height",
			"value" => "1000",
		),
		array(
			"name" => "attach_big_quality",
			"value" => "90",
		),
		array(
			"name" => "attach_medium_width",
			"value" => "100",
		),
		array(
			"name" => "attach_medium_height",
			"value" => "100",
		),
		array(
			"name" => "attach_medium_quality",
			"value" => "80",
		),
		array(
			"name" => "use_animation",
			"value" => "1",
		),
		array(
			"name" => "show_more",
			"value" => '1',
		),
	);

	/**
	 * @var array SQL-запросы
	 */
	public $sql = array(
		"forum_blocks" => array(
			array(
				"id" => 1,
				"name" => 'Главная категория форума',
			),
		),
		"forum_category" => array(
			array(
				"id" => 1,
				"block_id" => 1,
				"name" => 'Общий раздел',
			),
		),
	);

	/**
	 * @var array демо-данные
	 */
	public $demo = array(
		"forum_blocks" => array(
			array(
				"id" => 1,
				"name" => 'Туризм',
			),
			array(
				"id" => 2,
				"name" => 'Оборудование',
			),
			array(
				"id" => 3,
				"name" => 'Компания',
			),
		),
		"forum_category" => array(
			array(
				"id" => 1,
				"block_id" => 1,
				"name" => 'Походы в России',
			),
			array(
				"id" => 2,
				"block_id" => 1,
				"name" => 'Походы в Подмосковье',
			),
			array(
				"id" => 3,
				"block_id" => 1,
				"name" => 'Зарубежье',
			),
			array(
				"id" => 4,
				"block_id" => 2,
				"name" => 'Палатки',
			),
			array(
				"id" => 5,
				"block_id" => 2,
				"name" => 'Рюкзаки',
			),
			array(
				"id" => 6,
				"block_id" => 2,
				"name" => 'Прочее',
			),
			array(
				"id" => 7,
				"block_id" => 3,
				"name" => 'Жалобы и предложения',
			),			
		),
		"forum" => array(
			array(
				"cat_id" => 3,
				"name" => 'Финские каникулы',
				"user_id" => 2,
				'rewrite' => 'forum/finskie-kanikuly',
				"messages" => array(
					array(
						"name" => "Александра",
						"text" => 'Майские каникулы в Финляндии это моя первая заграница)) Взрослые наверное справедливо восхищались сказачными домиками, качеством автомобильных дорог, агрегатами для обработки леса, ну и другими взрослыми вещами... А в это время я изучала флору и фауну))) Чудно тут всё - почта как у нас в России)))',
						"children" => array(
							array(
								"name" => "Сергей",
								"text" => 'Понравилось в Финляндии? А я не был там, всё по югам, Испания, да Греция <img src="BASE_PATHmodules/bbcode/smiles/smile.gif">',
							),
						)
					),
					array(
						"text" => 'Александра, а фотографии из поездки есть? <img src="BASE_PATHmodules/bbcode/smiles/smile.gif">',
						"user_id" => 1,
					),
				),
			),
			array(
				"cat_id" => 2,
				"name" => 'Зеленый бор',
				"user_id" => 1,
				'rewrite' => 'forum/zelenyy-bor',
				"messages" => array(
					array(
						"text" => 'Ездил кто-нибудь? Интересно, как там <img src="BASE_PATHmodules/bbcode/smiles/drinks.gif">',
						"user_id" => 1,
					),
				),
			),
			array(
				"cat_id" => 1,
				"name" => 'Велопоходик Тверь - Кимры',
				'rewrite' => 'forum/velopokhodik-tver-kimry',
				"user_id" => 2,
				"messages" => array(
					array(
						"text" => 'Привет!<br>Я тоже хочу поделиться фотками своего путешествия.<br>Тем более, что места красивые.<br>Мы выезжали из Москвы поздно вечером 8го мая, чтобы не тратить утро следующего дня на электрички, и чтобы не пересекаться с дачниками.<br>Электричка была пустая, и мы спокойно разместились в одном вагоне. <br>Ночевали первую ночь за одну остановку до Твери, близ платформы Чуприяновка.  Прехали на место около полуночи и легли спать.<br>Наутро обнаружили, что забыли половник, и Леха сделал мега-ложку из дерева.<br>Собрались и поехали в Тверь.<br>по пути мимо нас низко пролетали самолеты, возвращаясь с Парада Победы.<br>Город, честно говоря, нас не сильно впечатлил.<br>Зато северный берег Волги поразил чистыми сосновыми лесами, приятными грунтовыми и асфальтовыми дорогами, видами на Великую Русскую Реку.<br>Были конечно, и болота, и броды, и разливы. Преодолевали пешком, в самых глубоких местах помогали девушкам.<br>А вот это болото мы перебродить не смогли. Я сходил на разведку и провалился почти по пояс. Пришлось объезжать по другой дороге.<br>Отдельного внимания заслуживает Оршинский женский монастырь. Он находится на слиянии р. Орши и Волги.  Рядом с монастырем есть купель и памятник воинам Великой Отечественной Войны. Вдоль Волги к нему ведет ровная грунтовка. А на северо-восток уходит приятная асфальтовая дорожка, почти без движения.<br>Ну и конечно, мы каждый день купались в Волге. И даже устраивали заезды на велосипедах  - кто глубже :)<br>В итоге мы проехали 220 км, маршрутом очень довольны. Я всем рекомендую.',
						"user_id" => 2,
					),
					array(
						"text" => 'Прикладываю фотографии',
						'attachments' => array(
							array(
								'name' => '8_pohod3.jpg',
								'extension' => 'jpg',
								'is_image' => 1,
							),
							array(
								'name' => '7_pohod2.jpg',
								'extension' => 'jpg',
								'is_image' => 1,
							),
							array(
								'name' => '6_pohod1.jpg',
								'extension' => 'jpg',
								'is_image' => 1,
							),
						),	
						"user_id" => 2,	
					),
					array(
						"text" => 'Очень круто! Фотографии класс!<br>Надо будет летом тоже рвануть! <img src="BASE_PATHmodules/bbcode/smiles/good.gif">',
						"user_id" => 1,
						"children" => array(
							array(
								"text" => 'Спасибо <img src="BASE_PATHmodules/bbcode/smiles/drinks.gif">',
								"user_id" => 2,	
								"children" => array(
									array(
										"text" => 'Мне тоже понравилось!',
										"user_id" => 3,
									),
								)
							),
						)
					),
				),
			),
			array(
				"cat_id" => 4,
				"name" => 'Палатка Вирджиния',
				"user_id" => 1,
				'rewrite' => 'forum/palatka-virdzhiniya',
				"messages" => array(
					array(
						"text" => 'Предлагаю обсудить палатку Вирджиния.
		<br>У кого есть опыт, как она в походах, как уют и пр.',
						"user_id" => 1,
					),
				),
			),
			array(
				"cat_id" => 6,
				"name" => 'Тест драйв термобелья Актив в условиях мокрого леса',
				"user_id" => 2,
				'rewrite' => 'forum/test-drayv-termobelya-aktiv',
				"messages" => array(
					array(
						"text" => 'Прошедшие выходные провел на малой родине, в г. Тамбове. <br>В вс решил выбраться на природу с друзьями, а перед этим побегать по лесу.<br><br><b>Условия</b>: <br>Мокрый лес, температура +2, накануне весь день шел мокрый снег.  Беговая тренировка 13 км, затем костер.<br><br><b>Одежда</b>:<br>Верх - термобелье Актив и легкая спортивная куртка. <br>Низ - брюки Аутдор М. <br><br>Сразу скажу, что просто гулять по городу в таком комплекте холодно. Ветровка продувается.<br><br>Зато бежать очень комфортно - одежда почти не чувствуется, и не потеешь.<br><br>Ночью валил снег хлолпьями. Падал  - и сразу таял. К утру в городе снег был больше похож на большую лужу. В лесу тоже не сильно лучше - снег лежал ровным слоем, прикрывал грязь. Но больше чем один раз по тропе лучше не бежать - сразу становится скользко, на месте следов проступают лужи. Ноги были сырые - и с этим нужно было просто смириться.<br><br>Примерно на середине дистанции меня ждала переправа через болото. В болоте хаотично лежали бревна, покрытые снегом.<br><br>Ширина заболоченного участка около 30 м. <br><br>Разгоряченный бегом, я переоценил свои силы и начал слишком быстро и неосторожно перебираться по бревнам. В результате оступился и намочил всю левую половину тела. Телефон пришлось сразу же выключить и вытащить аккумулятор. <br><br>Но не беда. Сил еще было полно, и я бодро добежал до места встречи. Я еще чувствовал, что одежда мокрая, но холодно не было. <br><br>Моя компания подошла примерно в то же время, что и я, и мы быстро развели костер. <br><br>За 15 минут у костра мне удалось высушить сначала термобелье и брюки прямо на себе, а затем и ветровку.  Назад я шел уже полностью сухим. <br><br>Выводы и рекомендации: <br><br><ol><br><li>Для спорта при околонулевых температуах достаточно одевать только термобелье и ветровку. </li><li>Рубашка Актив отлично справилась с проверкой: бегать в ней комфортно, сохнет быстро. </li><li>Не рекомендую брюки Аутдор для бега - они достаточно широкие. Подойдут скорее для трекинга, летних походов. </li><li>Нет ничего страшного в том, что намокнешь в лесу.</li></ol>',
						"user_id" => 2,
						'attachments' => array(
							array(
								'name' => '9_pohod1.jpg',
								'extension' => 'jpg',
								'is_image' => 1,
							),
						),
					),
				),
			),
			array(
				"cat_id" => 1,
				"name" => 'Конкурс фотоотчетов о путешествии по России',
				"user_id" => 1,
				'rewrite' => 'forum/konkurs-fotootchetov-o-puteshestvii-po-rossii',
				"messages" => array(
					array(
						"text" => 'Собираетесь отдохнуть? Уже спланировали поездку? Покажите, что вы отдыхаете лучше всех. Примите участие в нашем конкурсе!<br><br>Принимаем отчеты работ в ближайшие две недели.<br>Окончание голосования и подведение итогов и награждение – 15 числа следующего месяца.<br><br><ol><br><li> место – Сертификат на 5000 руб</li><li> место – Рюкзак</li><li> место – Тент туристический 3*4.</li></ol><br><br>Первым 30 участникам бейсболка в подарок.<br><br><b>Как принять участие.</b><br>Напишите красочный фото-отчет о вашем отдыхе. Отчет должен содержать как минимум 5 фотографий и кратких описаний к ним. Из этих фото, минимум одна должна быть с нашей продукцией. Участники конкурса дают свое согласие на использование фотографий на сайте и в каталогах.<br><br>Разместите отчет на форуме. <br><br><b>Подсчет результатов и определение победителей.</b><br>Победитель определяется по сумме комментариев!<br><br>Специальный приз судейских симпатий - 3 термокружки, будут вручены авторам самых ярких отчетов по мнению организаторов.',
						"user_id" => 1,
						"children" => array(
							array(
								"text" => '<div class="quote"><div class="quote_header">Цитата</div>Первым 30 участникам бейсболка в подарок.</div><br>Бейсболка, кстати, с нашим логотипом! <img src="BASE_PATHmodules/bbcode/smiles/wink.gif">',
								"user_id" => 2,
							),
						),
					),
				),
			),
		),
	);
}
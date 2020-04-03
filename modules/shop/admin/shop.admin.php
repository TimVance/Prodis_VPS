<?php
/**
 * Редактирование товаров
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */
if ( ! defined('DIAFAN'))
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
 * Shop_admin
 */
class Shop_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'shop';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
				'help' => 'Используется в заголовках, ссылках на товар, при заказе.',
				'multilang' => true,
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'help' => 'Если не отмечена, страница товара не будет выводиться на сайте.',
				'default' => true,
				'multilang' => true,
			),
			'no_buy' => array(
				'type' => 'checkbox',
				'name' => 'Товар временно отсутствует',
				'help' => 'Если отмечена, у товара не будет кнопки «Купить», выведется сообщение «Товар временно отсутствует», а посетители сайта смогут подписаться на уведомление о поступлении. Подписанных посетителей можно будет увидеть в разделе «Статистика» - «Список ожиданий». В случае поступления товара и снятии данной галки, все подписанные посетители автоматически получат уведомления о поступлении товара на указанный электронный ящик.',
			),
			'price' => array(
				'type' => 'floattext',
				'name' => 'Цена',
				'help' => 'Можно задать несколько вариантов цены и количества для товара. Для этого следует создать дополнительную характеристику с типом «список с выбором нескольких значений», отметить опцию «Доступен к выбору при заказе» и при редактировании товара отметить возле характеристики опцию «Влияет на цену». К варианту товара можно прикрепить изображение из тех, что уже загружены для товара.  Поле «Количество» отображается, если в настройках модуля подключена опция «Учитывать остатки товаров на складе». Если поле «Количество» пустое и в настройках не отмечена опция «Разрешать покупать товары без цены», то товар нельзя купить.',
				'no_save' => true,
			),
			'article' => array(
				'type' => 'text',
				'name' => 'Артикул',
				'help' => 'Внутренний артикул товара. Если заполнить, будет выводиться на сайте и использоваться в поиске по товарам.',
			),
			'measure_unit' => array(
				'type' => 'text',
				'name' => 'Единица измерения',
				'multilang' => true,
			),
			'cat_id' => array(
				'type' => 'function',
				'name' => 'Категория',
				'help' => 'Категория, к которой относится товар. Список категорий редактируется во вкладке выше. Возможно выбрать дополнительные категории, в которых товар также будет выводится. Чтобы выбрать несколько категорий, удерживайте CTRL. Параметр выводится, если в настройках модуля отмечена опция «Использовать категории».',
			),
			'brand_id' => array(
				'type' => 'select',
				'name' => 'Производитель',
				'help' => 'Список производителей редактируется во вкладке выше.',
				'select_db' => array(
					'table' => 'shop_brand',
					'name' => '[name]',
					'where' => "trash='0'",
					'order' => 'sort ASC',
				),
			),
			'hit' => array(
				'type' => 'checkbox',
				'name' => 'Хит',
				'help' => 'Если отмечена, товар будет участвовать в поиске по соответствующему признаку, в списках и карточке товара будет выводиться соответствующий значёк. Товары можно будет группировать.',
			),
			'new' => array(
				'type' => 'checkbox',
				'name' => 'Новинка',
				'help' => 'Если отмечена, товар будет участвовать в поиске по соответствующему признаку, в списках и карточке товара будет выводиться соответствующий значёк. Товары можно будет группировать.',
			),
			'action' => array(
				'type' => 'checkbox',
				'name' => 'Акция',
				'help' => 'Если отмечена, товар будет участвовать в поиске по соответствующему признаку, в списках и карточке товара будет выводиться соответствующий значёк. Товары можно будет группировать.',
			),
			'weight' => array(
				'type' => 'floattext',
				'name' => 'Вес',
				'help' => 'Используется для расчета стоимости доставки.',
			),
			'length' => array(
				'type' => 'floattext',
				'name' => 'Длина',
				'help' => 'Используется для расчета стоимости доставки.',
			),
			'width' => array(
				'type' => 'floattext',
				'name' => 'Ширина',
				'help' => 'Используется для расчета стоимости доставки.',
			),
			'height' => array(
				'type' => 'floattext',
				'name' => 'Высота',
				'help' => 'Используется для расчета стоимости доставки.',
			),
			'hr3' => 'hr',
			'files' => array(
				'type' => 'function',
				'name' => 'Загрузить файл',
				'help' => 'Цифровой товар. Загрузите файл, который необходимо продавать. Он будет храниться в закрытой папке без прямого доступа, а ссылка на его скачивание будет формироваться после оплаты. Параметр выводится, если в настройках модуля отмечена опция «Включить возможность продажи файлов».',
			),
			'images' => array(
				'type' => 'module',
				'name' => 'Изображения',
				'help' => 'Иллюстрации к товару. Можно загрузить сразу несколько. Иллюстрации загрузятся автоматически после выбора. Варианты размера загружаемых изображений определяются в настройках. Параметр выводится, если в настройках модуля отмечена опция «Использовать изображения».',
			),
			'discounts' => array(
				'type' => 'function',
				'name' => 'Общие скидки',
				'help' => 'Список глобальных скидок. Возможность подключить или отключить скидку для товара.',
			),
			'additional_costs' => array(
				'type' => 'function',
				'name' => 'Сопутствующие услуги',
				'help' => 'Возможность добавить услуги к товару за дополнительную стоимость',
			),
			'param' => array(
				'type' => 'function',
				'name' => 'Характеристики',
				'help' => 'Группа полей, определенных в части «Характеристики». Для характеристики с типом «список с выбором нескольких значений» можно задать несколько цен для товара. Для одного товара можно выбрать несколько характеристик, влияющих на цену.',
				'multilang' => true,
			),
			'anons' => array(
				'type' => 'editor',
				'name' => 'Анонс',
				'help' => 'Краткое описание товара. Выводится в списках товара и в блоках. Если отметить «Добавлять к описанию», на странице товара анонс выведется вместе с основным описанием. Иначе анонс выведется только в списке, а на отдельной странице будет только описание. Если отметить «Применить типограф», контент будет отформатирован согласно правилам экранной типографики с помощью [веб-сервиса «Типограф»](http://www.artlebedev.ru/tools/typograf/webservice/). Опция «HTML-код» позволяет отключить визуальный редактор для текущего поля. Значение этой настройки будет учитываться и при последующем редактировании.',
				'multilang' => true,
				'height' => 200,
			),
			'text' => array(
				'type' => 'editor',
				'name' => 'Описание',
				'help' => 'Полное описание для страницы товара. Если отметить «Применить типограф», контент будет отформатирован согласно правилам экранной типографики с помощью [веб-сервиса «Типограф»](http://www.artlebedev.ru/tools/typograf/webservice/). Опция «HTML-код» позволяет отключить визуальный редактор для текущего поля. Значение этой настройки будет учитываться и при последующем редактировании.',
				'multilang' => true,
			),
			'dynamic' => array(
				'type' => 'function',
				'name' => 'Динамические блоки',
			),
			'tags' => array(
				'type' => 'module',
				'name' => 'Теги',
				'help' => 'Добавление тегов к товару. Можно добавить либо новый тег, либо открыть и выбрать из уже существующих тегов. Параметр выводится, если в настройках модуля включен параметр «Подключить теги».',
			),
			'hr6' => 'hr',
			'rel_elements' => array(
				'type' => 'function',
				'name' => 'Похожие товары',
				'help' => 'Выбор и добавление к текущему товару связей с другими товарами. Похожие товары выводятся шаблонным тегом show_block_rel. По умолчанию связи между товарами являются односторонними, это можно изменить, отметив опцию «В блоке похожих товаров связь двусторонняя» в настройках модуля.',
			),
			'hr4' => 'hr',
			'statistics' => array(
				'type' => 'function',
				'name' => 'Статистика',
				'help' => 'Счетчик просмотров и покупок текущего товара. Статистика просмотров ведется и параметр «Просмотров товара» выводится, если в настройках модуля отмечена опция «Подключить счетчик просмотров».',
				'no_save' => true,
			),
			'comments' => array(
				'type' => 'module',
				'name' => 'Комментарии',
				'help' => 'Комментарии, которые оставили пользователи к текущему товару. Параметр выводится, если в настройках модуля включен параметр «Показывать комментарии к товарам».',
			),
			'rating' => array(
				'type' => 'module',
				'name' => 'Рейтинг',
				'help' => 'Средний рейтинг, согласно голосованию пользователей сайта. Параметр выводится, если в настройках модуля включен параметр «Подключить рейтинг к товарам».',
			),
			'hr5' => 'hr',
			'show_google' => array(
				'type' => 'checkbox',
				'name' => 'Выгружать в Google Merchant',
				'help' => 'Параметр разрешит или запретит выгружать этот товар. Параметр выводится, если в настройках модуля отмечена опция «Подключить Google Merchant» и параметр «Выгружать товары в Google Merchant» определен как «только помеченные».'
			),
			'google' => array(
				'type' => 'function',
				'name' => 'Поля для Google Merchant',
				'help' => 'Параметры, необходимые для формирования информации для системы Google Merchant. Параметр выводится, если в настройках модуля отмечена опция «Подключить Google Merchant».',
				'depend' => 'show_google',
			),
		),
		'other_rows' => array (
			'number' => array(
				'type' => 'function',
				'name' => 'Номер',
				'help' => 'Номер элемента в БД (веб-мастеру и программисту).',
				'no_save' => true,
			),
			'admin_id' => array(
				'type' => 'function',
				'name' => 'Редактор',
				'help' => 'Изменяется после первого сохранения. Показывает, кто из администраторов сайта первый правил текущую страницу.'
			),
			'timeedit' => array(
				'type' => 'text',
				'name' => 'Время последнего изменения',
				'help' => 'Изменяется после сохранения элемента. Отдается в заголовке *Last Modify*.',
			),
			'site_id' => array(
				'type' => 'function',
				'name' => 'Раздел сайта',
				'help' => 'Перенос товара на другую страницу сайта, к которой прикреплен модуль. Параметр выводится, если в настройках модуля отключена опция «Использовать категории», если опция подключена, то раздел сайта задается такой же, как у основной категории.',
			),
			'title_seo' => array(
				'type' => 'title',
				'name' => 'Параметры SEO',
			),
			'title_meta' => array(
				'type' => 'text',
				'name' => 'Заголовок окна в браузере, тег Title',
				'help' => 'Если не заполнен, тег *Title* будет автоматически сформирован как «Название товара – Название страницы – Название сайта», либо согласно шаблонам автоформирования из настроек модуля (SEO-специалисту).',
				'multilang' => true,
			),
			'keywords' => array(
				'type' => 'textarea',
				'name' => 'Ключевые слова, тег Keywords',
				'help' => 'Если не заполнен, тег *Keywords* будет автоматически сформирован согласно шаблонам автоформирования из настроек модуля (SEO-специалисту).',
				'multilang' => true,
			),
			'descr' => array(
				'type' => 'textarea',
				'name' => 'Описание, тег Description',
				'help' => 'Если не заполнен, тег *Description* будет автоматически сформирован согласно шаблонам автоформирования из настроек модуля (SEO-специалисту).',
				'multilang' => true,
			),
			'canonical' => array(
				'type' => 'text',
				'name' => 'Канонический тег',
				'help' => 'URL канонической страницы вида: *http://site.ru/psewdossylka/*, на которую переносится "ссылочный вес" данной страницы. Используется для страниц с похожим или дублирующимся контентом (SEO-специалисту).',
				'multilang' => true,
			),
			'rewrite' => array(
				'type' => 'function',
				'name' => 'Псевдоссылка',
				'help' => 'ЧПУ, т.е. адрес страницы вида: *http://site.ru/psewdossylka/*. Смотрите параметры сайта (SEO-специалисту).'
			),
			'redirect' => array(
				'type' => 'none',
				'name' => 'Редирект на текущую страницу со страницы',
				'help' => 'Позволяет делать редирект с указанной страницы на текущую.',
				'no_save' => true,
			),
			'noindex' => array(
				'type' => 'checkbox',
				'name' => 'Не индексировать',
				'help' => 'Запрет индексации текущей страницы, если отметить, у страницы выведется тег: `<meta name="robots" content="noindex">` (SEO-специалисту).'
			),
			'changefreq'   => array(
				'type' => 'function',
				'name' => 'Changefreq',
				'help' => 'Вероятная частота изменения этой страницы. Это значение используется для генерирования файла sitemap.xml. Подробнее читайте в описании [XML-формата файла Sitemap](http://www.sitemaps.org/ru/protocol.html) (SEO-специалисту).',
			),
			'priority'   => array(
				'type' => 'floattext',
				'name' => 'Priority',
				'help' => 'Приоритетность URL относительно других URL на Вашем сайте. Это значение используется для генерирования файла sitemap.xml. Подробнее читайте в описании [XML-формата файла Sitemap](http://www.sitemaps.org/ru/protocol.html) (SEO-специалисту).',
			),
			'title_show' => array(
				'type' => 'title',
				'name' => 'Параметры показа',
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Изменить положение текущего товара среди других товаров. Поле доступно для редактирования только для товаров, отображаемых на сайте (администратору сайта).'
			),
			'date_period' => array(
				'type' => 'date',
				'name' => 'Период показа',
				'help' => 'Если заполнить, текущий товар будет опубликован на сайте в указанный период. В иное время пользователи сайта товар не будут видеть, получая ошибку 404 «Страница не найдена» (администратору сайта).'
			),
			'access' => array(
				'type' => 'function',
				'name' => 'Доступ',
				'help' => 'Если отметить опцию «Доступ только», товар увидят только авторизованные на сайте пользователи, отмеченных типов. Не авторизованные, в том числе поисковые роботы, увидят «404 Страница не найдена» (администратору сайта).',
			),
			'map_no_show' => array(
				'type' => 'checkbox',
				'name' => 'Не показывать на карте сайта',
				'help' => 'Скрывает отображение ссылки на товар в файле sitemap.xml и [модуле «Карта сайта»](http://www.diafan.ru/dokument/full-manual/modules/map/).',
			),
			'hr8' => 'hr',
			'show_yandex' => array(
				'type' => 'checkbox',
				'name' => 'Выгружать в Яндекс.Маркет',
				'help' => 'Параметр разрешит или запретит выгружать этот товар. Параметр выводится, если в настройках модуля отмечена опция «Подключить Яндекс Маркет» и параметр «Выгружать товары в Яндекс.Маркет» определен как «только помеченные».'
			),
			'yandex' => array(
				'type' => 'function',
				'name' => 'Поля для Яндекс Маркет',
				'help' => 'Параметры, необходимые для формирования информации для системы «Яндекс Маркет» файлом modules/shop/shop.yandex.php. Параметр выводится, если в настройках модуля отмечена опция «Подключить Яндекс Маркет».',
				'depend' => 'show_yandex',
			),
			'hr9' => 'hr',
			'import_id' => array(
				'type' => 'text',
				'name' => 'Идентификатор для импорта',
				'help' => 'Можно заполнить для идентификации категории при импорте (администратору сайта).'
			),
			'title_view' => array(
				'type' => 'title',
				'name' => 'Оформление',
			),
			'theme' => array(
				'type' => 'function',
				'name' => 'Шаблон страницы',
				'help' => 'Возможность подключить для страницы товара шаблон сайта отличный от основного (themes/site.php). Все шаблоны для сайта должны храниться в папке *themes* с расширением *.php* (например, themes/dizain_so_slajdom.php). Подробнее в [разделе «Шаблоны сайта»](http://www.diafan.ru/dokument/full-manual/templates/site/). (веб-мастеру и программисту, не меняйте этот параметр, если не уверены в результате!).',
			),
			'view' => array(
				'type' => 'function',
				'name' => 'Шаблон модуля',
				'help' => 'Шаблон вывода контента модуля на странице отдельного товара (веб-мастеру и программисту, не меняйте этот параметр, если не уверены в результате!).',
			),
			'search' => array(
				'type' => 'module',
				'name' => 'Индексирование для поиска',
				'help' => 'Товар автоматически индексируется для модуля «Поиск по сайту» при внесении изменений.',
			),
			'map' => array(
				'type' => 'module',
				'name' => 'Индексирование для карты сайта',
				'help' => 'Товар автоматически индексируется для карты сайта sitemap.xml.',
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'sort' => array(
			'name' => 'Сортировка',
			'type' => 'numtext',
			'sql' => true,
			'fast_edit' => true,
			'desc' => true,
		),
		'image' => array(
			'name' => 'Фото',
			'class_th' => 'item__th_image ipad',
			'no_important' => true,
		),
		'name' => array(
			'name' => 'Название и категория'
		),
		'article' => array(
			'type' => 'none',
			'sql' => true,
		),
		'adapt' => array(
			'class_th' => 'item__th_adapt',
		),
		'separator' => array(
			'class_th' => 'item__th_seporator',
		),
		'price' => array(
			'class_th' => 'item__th_price no_important',
			'name' => 'Цены<span class="right">Кол-во</span>',
			'fast_edit' => true,
			'no_important' => true,
		),
		'count' => array(
			'type' => 'none',
			'fast_edit' => true,
		),
		'action' => array(
			'sql' => true,
			'no_important' => true,
		),
		'hit' => array(
			'sql' => true,
			'type' => 'none',
		),
		'new' => array(
			'sql' => true,
			'type' => 'none',
		),
		'text' => array(
			'sql' => true,
			'type' => 'none',
		),
		'actions' => array(
			'view' => true,
			'act' => true,
			'trash' => true,
		),
	);

	/**
	 * @var array поля для фильтра
	 */
	public $variables_filter = array (
		'hit' => array(
			'type' => 'checkbox',
			'name' => 'Хит',
			'icon' => '<span class="hit"><i class="fa fa-bolt"></i></span>',
		),
		'action' => array(
			'type' => 'checkbox',
			'name' => 'Акция',
			'icon' => '<span class="discount">%</span>',
		),
		'new' => array(
			'type' => 'checkbox',
			'name' => 'Новинка',
			'icon' => '<span class="new"><i class="fa fa-certificate"></i></span>',

		),
		'hr1' => array(
			'type' => 'hr',
		),
		'no_buy' => array(
			'type' => 'checkbox',
			'name' => 'Нет в наличии',
		),
		'no_cat' => array(
			'type' => 'checkbox',
			'name' => 'Нет категории',
		),
		'no_img' => array(
			'type' => 'checkbox',
			'name' => 'Нет картинки',
		),
		'no_price' => array(
			'type' => 'checkbox',
			'name' => 'Нет цены',
		),
		'no_act' => array(
			'type' => 'checkbox',
			'name' => 'Все неактивные',
		),
		'hr2' => array(
			'type' => 'hr',
		),
		'cat_id' => array(
			'type' => 'select',
			'name' => 'Искать по категории',
		),
		'site_id' => array(
			'type' => 'select',
			'name' => 'Искать по разделу',
		),
		'name' => array(
			'type' => 'text',
			'name' => 'Искать по названию',
		),
		'article' => array(
			'type' => 'text',
			'name' => 'Искать по артикулу',
		),
		'brand_id' => array(
			'type' => 'select',
			'name' => 'Искать по производителю',
		),
		'hr2' => array(
			'type' => 'hr',
		),
		'param' => array(
			'type' => 'function',
			'multilang' => true,
			'category_rel' => true,
		),
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'element_site', // делит элементы по разделам (страницы сайта, к которым прикреплен модуль)
		'element', // используются группы
		'element_multiple', // модуль может быть прикреплен к нескольким группам
	);

	/**
	 * @var array валюты
	 */
	private $currency;

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		if(! $this->diafan->configmodules("cat", "shop", $this->diafan->_route->site))
		{
			$this->diafan->config("element", false);
			$this->diafan->config("element_multiple", false);
		}

		if ( ! $this->diafan->configmodules('show_yandex_element', 'shop'))
		{
			$this->diafan->variable_unset("show_yandex");
		}

		if ( ! $this->diafan->configmodules('show_google_element', 'shop'))
		{
			$this->diafan->variable_unset("show_google");
		}
		if($this->diafan->configmodules("sort", "shop", $this->diafan->_route->site))
		{
			$this->diafan->variable_unset("sort");
			$this->diafan->variable_list_unset("sort");
		}
		if (! $this->diafan->configmodules("use_count_goods", "shop", $this->diafan->_route->site))
		{
			$this->diafan->variable_list("price", "name", "Цены");
		}
	}

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить товар', 'fa-cart-plus');
	}

	/**
	 * Выводит список товаров
	 * @return void
	 */
	public function show()
	{
		if ($this->diafan->config('element') && ! $this->diafan->not_empty_categories)
		{
			echo '<div class="error">'.sprintf($this->diafan->_('В %sнастройках%s модуля подключены категории, чтобы начать добавлять товар создайте хотя бы одну %sкатегорию%s.'),'<a href="'.BASE_PATH_HREF.'shop/config/">', '</a>', '<a href="'.BASE_PATH_HREF.'shop/category/'.($this->diafan->_route->site ? 'site'.$this->diafan->_route->site.'/' : '').'">', '</a>').'</div>';
		}
		if($this->diafan->_route->page < 2)
		{
			echo '<span class="shop_stat">';
	
			$catstat = ($this->diafan->_route->cat?"cat_id='%d' AND":"");
			$statall = 0; $statdeact = 0; $statnon = 0; $statnoprice = 0; $statnoimage = 0;
	
			$statall = DB::query_result("SELECT COUNT(*) FROM {shop} WHERE ".$catstat." trash='0'", $this->diafan->_route->cat);
			echo 'Всего товаров: <b>'.$statall.'</b>';
	
			$statdeact = DB::query_result("SELECT COUNT(*) FROM {shop} WHERE ".$catstat." [act]='0' AND trash='0'", $this->diafan->_route->cat);
			echo ($statdeact > 0 ? ', неактивных: <b>'.$statdeact.'</b>' : '');
	
			$statnon = DB::query_result("SELECT COUNT(*) FROM {shop} WHERE ".$catstat." no_buy='1' AND trash='0'", $this->diafan->_route->cat);
			echo ($statnon > 0 ? ', нет в наличии: <b>'.$statnon.'</b>' : '');
	
			$statnoprice = DB::query_result("SELECT count(DISTINCT g.id) FROM {shop} as g WHERE ".$catstat." 1=1 AND (SELECT COUNT(*) FROM {shop_price} AS i WHERE i.good_id=g.id)=0 AND g.trash='0'", $this->diafan->_route->cat);
			echo ($statnoprice > 0 ? ', без цены: <b>'.$statnoprice.'</b>' : '');
	
			$statnoimage = DB::query_result("SELECT count(DISTINCT g.id) FROM {shop} as g WHERE ".$catstat." 1=1 AND (SELECT COUNT(*) FROM {images} AS i WHERE i.element_id=g.id AND i.element_type='element' AND i.module_name='shop' AND i.param_id=0)=0 AND g.trash='0'", $this->diafan->_route->cat);
			echo ($statnoimage > 0 ? ', без картинки: <b>'.$statnoimage.'</b>' : '');
	
			echo '</span>';
		}

		$this->diafan->list_row();
	}

	/**
	 * Формирует часть SQL-запрос для списка элементов, отвечающую за сортировку
	 *
	 * @return string
	 */
	public function sql_query_order()
	{
		$order = " ORDER BY e.act".$this->diafan->lang_act.' DESC, ';
		switch($this->diafan->configmodules("sort", "shop", $this->diafan->_route->site))
		{
			case 1:
				$order .= 'e.id DESC';
				break;
			case 2:
				$order .= 'e.id ASC';
				break;
			case 3:
				$order .= 'e.name'.$this->diafan->lang_act.' ASC';
				break;
			default:
				$order .= 'e.sort DESC, e.id DESC';
		}
		return $order;
	}

	/**
	 * Выводит название товара в списке элементов
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_name($row, $var)
	{
		$text = '<div class="name'.($var["class"] ? ' '.$var["class"] : '').'">';
		if(defined("MOD_DEVELOPER") && MOD_DEVELOPER && $row["name"])
		{
			$text .= '<div class="id">'.'ID: '.$row["id"].($row["article"] ? ', '.$this->diafan->_('Артикул').': '.$row["article"] : '').'</div>';
		}
		$text .= '<a name="'.$row['id'].'" href="'.$this->diafan->get_base_link($row).'" title="'.str_replace('"', '&quot;', $this->diafan->short_text($row["text"], 80)).'">';
		if($row["name"])
		{
			$text .= $this->diafan->short_text($row["name"], 20);
			if(! defined("MOD_DEVELOPER") || ! MOD_DEVELOPER)
			{
				$text .= ' ('.'ID: '.$row["id"].($row["article"] ? ', '.$this->diafan->_('Артикул').': '.$row["article"] : '').')';
			}
		}
		else
		{
			$text .= $row["id"];
		}
		$text .= '</a>';
		$text .= $this->diafan->list_variable_parent($row, array());
		$text .= '</div>';
		return $text;
	}

	/**
	 * Функция быстрого редактирования цены товаров в списке
	 *
	 * @param array $item информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return void
	 */
	public function list_variable_price($item, $var)
	{
		if(! isset($this->cache["prepare"]["price"]))
		{
			$this->cache["prepare"]["price"] = true;
			foreach($this->diafan->rows_id as $id)
			{
				$this->diafan->_shop->price_prepare_base($id);
			}
			$param_select_ids = array();
			foreach($this->diafan->rows_id as $id)
			{
				$rows = $this->diafan->_shop->price_get_base($id);
				foreach($rows as $row)
				{
					if(! empty($row["param"]))
					{
						foreach($row["param"] as $p)
						{
							if(! in_array($p, $param_select_ids))
							{
								$param_select_ids[] = $p;
							}
						}
					}
				}
			}
			if($param_select_ids)
			{
				$this->cache["param_select"] = DB::query_fetch_key_value("SELECT id, [name] FROM {shop_param_select} WHERE id IN (%s)", implode(",", $param_select_ids), "id", "name");
			}
		}
		$rows = $this->diafan->_shop->price_get_base($item["id"]);

		$text = '<div class="item__price'.($var["class"] ? ' '.$var["class"] : '').' fast_edit">';

		$format_price_1 = $this->diafan->_shop->price_num_decimal_places();

		foreach($rows as $i => $row)
		{
			if(($row["price"] * 100) % 100)
			{
				$num_decimal_places = $format_price_1;
			}
			else
			{
				$num_decimal_places = 0;
			}

			if($i == 1)
			{
				$text .= '<div class="item__price__popup">';
			}
			if($i > 1)
			{
				$text .= '<div></div>';
			}
			$text .= '
			<div class="price">';
			if($row["param"])
			{
				$text .= '<div class="item__title">';
				$k = 0;
				foreach($row["param"] AS $p)
				{
					if(! empty($this->cache["param_select"][$p]))
					{
						if($k > 0)
						{
							$text .= ', ';
						}
						$text .= $this->cache["param_select"][$p];
					}
					$k++;
				}
				$text .= '</div>';
			}
			$text .= '
				<div class="item__field">
					<i class="fa fa-check-circle"></i>
					<div class="item__field__cover"><span></span></div>
					<input type="text" row_id="'.$row['id'].'" class="numtext" name="price" value="'.number_format($row["price"], $num_decimal_places, ',', '').'">

					<div class="info-box success">'.$this->diafan->_('Сохранено!').'</div>
					<div class="info-box change">'.$this->diafan->_('Для сохранения нажмите Enter.').'</div>
				</div>
				'.$row['currency_name'].'
			</div>';
			if ($this->diafan->configmodules("use_count_goods"))
			{
				$text .= '<div class="count">
					<div class="item__field">
						<i class="fa fa-check-circle"></i>
						<div class="item__field__cover"><span></span></div>
						<input type="text" row_id="'.$row['id'].'" name="count" class="numtext js_number" value="'.$row["count_goods"].'">

						<div class="info-box success">'.$this->diafan->_('Сохранено!').'</div>
						<div class="info-box change">'.$this->diafan->_('Для сохранения нажмите Enter.').'</div>
					</div>
					'.$this->diafan->_('Шт').'
				</div>';
			}
		}
		if(! empty($i))
		{
			$text .= '
			</div>
			<div class="item__price__toggle">
				<i class="fa fa-angle-down"></i>
				<span>'.$this->diafan->_('Развернуть').'</span>
				<i class="fa fa-angle-up hide"></i> <span class="hide">'.$this->diafan->_('Свернуть').'</span>
			</div>';
		}
		$text .= '</div>';
		return $text;
	}

	/**
	 * Выводит иконки "Хит, Акция, Новинка" в списке товаров
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_action($row, $var)
	{
		$text = '
		<div class="item__labels'.($var["class"] ? ' '.$var["class"] : '').'">
			<a href="javascript:void(0)" action="macros_shop_group_'.($row["hit"] ? 'not_' : '').'hit" class="hit'.(! $row["hit"] ? ' disable' : '').'" title="'.$this->diafan->_('Хит').'"><i class="fa fa-bolt"></i></a>
			<a href="javascript:void(0)" action="macros_shop_group_'.($row["action"] ? 'not_' : '').'action" class="discount'.(! $row["action"] ? ' disable' : '').'" title="'.$this->diafan->_('Акция').'">%</a>
			<a href="javascript:void(0)" action="macros_shop_group_'.($row["new"] ? 'not_' : '').'new" class="new'.(! $row["new"] ? ' disable' : '').'" title="'.$this->diafan->_('Новинка').'"><i class="fa fa-certificate"></i></a>
		</div>';
		return $text;
	}

	/**
	 * Функция быстрого сохранения цены товаров, отредактированной в списке
	 * @return boolean
	 */
	public function fast_save_price()
	{
		$price_id = $this->diafan->filter($_POST, 'int', 'id');
		if(! $price_id)
		{
			return false;
		}
		$good_id = DB::query_result("SELECT good_id FROM {shop_price} WHERE id=%d LIMIT 1", $price_id);
		if(! $good_id)
		{
			return false;
		}
		$price_value = str_replace(array(',', ' '), array('.', ''), $_POST['value']);
		// приводим цены в соответствии с установленными настройками
		$price_value = $this->diafan->_shop->price_format($this->diafan->filter($price_value, 'float'), true);

		DB::query("UPDATE {shop_price} SET price=%f WHERE id=%d LIMIT 1", $price_value, $price_id);
		$this->diafan->_shop->price_calc($good_id);

		// return true;
		$num_decimal_places = $this->diafan->_shop->price_num_decimal_places($price_value);
		return number_format($price_value, $num_decimal_places, ',', '');
	}

	/**
	 * Функция быстрого сохранения количества товаров, отредактированной в списке
	 * @return boolean
	 */
	public function fast_save_count()
	{
		$price_id = $this->diafan->filter($_POST, 'int', 'id');
		if(! $price_id)
		{
			return false;
		}
		$price = DB::query_fetch_array("SELECT * FROM {shop_price} WHERE id=%d LIMIT 1", $price_id);
		if(! $price)
		{
			return false;
		}
		DB::query("UPDATE {shop_price} SET count_goods=%f WHERE price_id=%d", str_replace(array(',', ' '), array('.', ''), $_POST['value']), $price["price_id"]);
		$price["count_goods"] = (float)$price["count_goods"];
		if((float)$_POST['value'] > 0 && ! $price["count_goods"])
		{
			$params = DB::query_fetch_key_value("SELECT param_id, param_value FROM {shop_price_param} WHERE price_id=%d", $price["price_id"], "param_id", "param_value");
			$this->diafan->_shop->price_send_mail_waitlist($price["good_id"], $params);
		}
		return true;
	}

	/**
	 * Поиск по полю "Нет цены"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function save_filter_variable_no_price($row)
	{
		if (empty($_GET["filter_no_price"]))
		{
			return;
		}
		$this->diafan->where .= " AND (SELECT COUNT(*) FROM {shop_price} AS pr WHERE pr.good_id=e.id AND pr.price>0)=0";
		$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' ).'filter_no_price=1';
		return 1;
	}

	/**
	 * Поиск по полю "Все неактивные"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function save_filter_variable_no_act($row)
	{
		if (empty($_GET["filter_no_act"]))
		{
			return;
		}
		$this->diafan->where .= " AND e.[act]='0'";
		$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' ).'filter_no_act=1';
		return 1;
	}

	/**
	 * Поиск по полю "Нет в наличии"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function save_filter_variable_no_buy($row)
	{
		if (empty($_GET["filter_no_buy"]))
		{
			return;
		}
		if($this->diafan->configmodules("use_count_goods"))
		{
			$this->diafan->where .= " AND (e.no_buy='1' OR (SELECT COUNT(*) FROM {shop_price} AS prc WHERE prc.good_id=e.id AND prc.count_goods>0)=0)";
		}
		else
		{
			$this->diafan->where .= " AND e.no_buy='1'";
		}
		$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' ).'filter_no_buy=1';
		return 1;
	}

	/**
	 * Вывод кнопки "Акция"
	 * @return void
	 */
	public function edit_variable_action()
	{
		$this->diafan->show_table_tr_checkbox('check_action', $this->diafan->variable_name(), $this->diafan->values("action"), $this->diafan->help(), false);
	}

	/**
	 * Редактирование поля "Цена"
	 * @return void
	 */
	public function edit_variable_price()
	{
		if (! $this->diafan->is_new)
		{
			$rows = $this->diafan->_shop->price_get_base($this->diafan->id);
			foreach ($rows as $i => $row)
			{
				$rows[$i]["image_rel"] = DB::query_fetch_array("SELECT i.id, i.name, i.folder_num FROM {images} AS i INNER JOIN {shop_price_image_rel} AS r ON r.image_id=i.id AND price_id=%d", $row["price_id"]);
				if(empty($rows[$i]["image_rel"]))
				{
					$rows[$i]["image_rel"]["id"] = '';
				}
				if(! $i)
				{
					$this->diafan->values("price", $row["price"], true);
					$this->diafan->values("old_price", $row["old_price"], true);
					$this->diafan->values("count", $row["count_goods"], true);
					$this->diafan->values("currency_id", $row["currency_id"], true);
					$this->diafan->values("price_import_id", $row["import_id"], true);
				}
			}
			$this->diafan->values("price_arr", $rows, true);
		}
		$this->currency = DB::query_fetch_all("SELECT * FROM {shop_currency} WHERE trash='0'");
		$format_price_1 = $this->diafan->_shop->price_num_decimal_places();
		if(($this->diafan->values("price") * 100) % 100)
		{
			$num_decimal_places = $format_price_1;
		}
		else
		{
			$num_decimal_places = 0;
		}
		if(($this->diafan->values("old_price") * 100) % 100)
		{
			$old_num_decimal_places = $format_price_1;
		}
		else
		{
			$old_num_decimal_places = 0;
		}

		$this->params();

		if (! empty($this->cache["multiple_params"]))
			return;

		echo '
		<div class="unit" id="price">
			<div class="infofield">
				'.$this->diafan->variable_name().$this->diafan->help().'
			</div>
			<input type="text" class="number" name="'.$this->diafan->key.'" value="'.($this->diafan->values("price_arr") ? number_format($this->diafan->values("price"), $num_decimal_places, ',', '') : '').'">
			<input type="text" class="number" placeholder="'.$this->diafan->_('Старая цена').'" name="old_price" value="'.($this->diafan->values("old_price") ? number_format($this->diafan->values("old_price"), $old_num_decimal_places, ',', '') : '').'">
			<input name="price_import_id" value="'.$this->diafan->values("price_import_id").'" type="hidden"> ';
		if ($this->diafan->configmodules("use_count_goods"))
		{
			echo $this->diafan->_('Количество').'
				<input type="number" name="count" value="'.$this->diafan->values("count").'">';
		}
		if($this->currency)
		{
			echo ' '.$this->diafan->_('Валюта').' &nbsp; <input id="currency_id" name="currency" type="radio" value="0"'.(! $this->diafan->values("currency_id") ? ' checked' : '').'> <label for="currency_id">'.$this->diafan->_('Основная').'</label>';
			foreach ($this->currency as $c)
			{
				echo ' <input name="currency" id="currency_'.$c["id"].'" type="radio" value="'.$c["id"].'"'.($this->diafan->values("currency_id") == $c["id"] ? ' checked' : '').'> <label for="currency_'.$c["id"].'">'.$c["name"].'</label>';
			}
		}
		echo '</div>';
	}

	private function params()
	{
		// значения списков
		$options = DB::query_fetch_key_array("SELECT [name], id, param_id FROM {shop_param_select} ORDER BY sort ASC", "param_id");

		$site_ids = array(0);
		if($this->diafan->values('site_id'))
		{
			$site_ids[] = $this->diafan->values('site_id');
		}

		$cat_ids = array(0);
		if($this->diafan->values('cat_id'))
		{
			$cat_ids[] = $this->diafan->values('cat_id');
		}
		if($this->diafan->values('cat_ids'))
		{
			$cat_ids = array_merge($cat_ids, $this->diafan->values('cat_ids'));
		}
		else
		{
			$values = array();
			if (! $this->diafan->is_new)
			{
				$rows = DB::query_fetch_all("SELECT cat_id FROM {%s_category_rel} WHERE element_id=%d AND cat_id>0", $this->diafan->table, $this->diafan->id);
				foreach ($rows as $row)
				{
					if ($row["cat_id"] != $this->diafan->values("cat_id"))
					{
						$values[] = $row["cat_id"];
					}
				}
			}
			$this->diafan->values('cat_ids', $values, true);
			$cat_ids = array_merge($cat_ids, $values);
		}

		// выбирает все характеристики (при смене раздела/категории просто показываем или скрываем характеристики)
		$this->cache["params"] = DB::query_fetch_all("SELECT p.id, p.[name], p.type, p.required, p.[measure_unit], p.[text], p.config FROM {shop_param} AS p INNER JOIN {shop_param_category_rel} AS r ON r.element_id=p.id AND r.cat_id IN (%s) AND p.site_id IN (%s) WHERE p.trash='0' GROUP BY p.id ORDER BY p.sort ASC", implode(',', $cat_ids), implode(',', $site_ids));

		$this->cache["multiple_params"] = array();
		$this->cache["depend_price"] = array();
		foreach ($this->cache["params"] as &$row)
		{
			// значения списков
			if (in_array($row["type"], array('select', 'multiple')))
			{
				if($row["type"] == 'select')
				{
					$row["options"] = array(array('name' => $this->diafan->_('Нет'), 'id' => ''));
				}
				else
				{
					$row["options"] = array();
				}
				if(! empty($options[$row["id"]]))
				{
					$row["options"] = array_merge($row["options"], $options[$row["id"]]);
				}
			}
			if ($row["type"] == 'multiple' && $row["required"])
			{
				$this->cache["multiple_params"][] = $row;
				if($price_arr = $this->diafan->values("price_arr"))
				{
					foreach ($price_arr as $price)
					{
						if(! empty($price["param"][$row["id"]]))
						{
							$this->cache["depend_price"][$row["id"]] = true;
						}
					}
				}
			}
		}
	}

	/**
	 * Редактирование поля "Производитель"
	 * @return void
	 */
	public function edit_variable_brand_id()
	{
		$cat_ids = array(0);
		if($this->diafan->values('cat_id'))
		{
			$cat_ids[] = $this->diafan->values('cat_id');
		}
		if($this->diafan->values('cat_ids'))
		{
			$cat_ids = array_merge($cat_ids, $this->diafan->values('cat_ids'));
		}

		$rows = DB::query_fetch_all("SELECT b.id, b.[name] FROM {shop_brand} AS b INNER JOIN {shop_brand_category_rel} AS c ON c.element_id=b.id WHERE b.trash='0' AND c.cat_id IN (%s) GROUP BY b.id ORDER BY b.sort ASC", implode(',', $cat_ids));

		echo '
		<div class="unit" id="brand_id">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
			<select name="brand_id">
			<option value="0">'.$this->diafan->_('Нет').'</option>';
			foreach ($rows as $row)
			{
				echo '<option value="'.$row["id"].'"'.($this->diafan->value == $row["id"] ? ' selected' : '').'>'.$row["name"].'</option>';
			}
			echo '</select>
		</div>';
	}

	/**
	 * Редактирование поля "Статистика"
	 * @return void
	 */
	public function edit_variable_statistics()
	{
		if ($this->diafan->is_new)
		{
			return;
		}
		if($this->diafan->configmodules("counter"))
		{
			$counter_view = DB::query_result("SELECT count_view FROM {shop_counter} WHERE element_id=%d LIMIT 1", $this->diafan->id);
			if(! $counter_view)
			{
				$counter_view = 0;
			}

			echo '
			<div class="unit">
			<b>'.$this->diafan->_('Просмотров товара').':</b>
			'.$counter_view.'
			</div>';
		}
		echo '
		<div class="unit">
			<b>'.$this->diafan->_('Товар купили раз').':</b>
			'.$this->diafan->values('counter_buy').'
		</div>';
	}

	/**
	 * Редактирование поля "Яндекс.Маркет"
	 * @return void
	 */
	public function edit_variable_yandex()
	{
		if (! $this->diafan->configmodules("yandex"))
		{
			return;
		}

		$u = array();

		if (! $this->diafan->is_new)
		{
			if ($this->diafan->value)
			{
				$conf_arr = explode("\n", $this->diafan->value);
				foreach ($conf_arr as $c_a)
				{
					list($k, $v) = explode("=", $c_a, 2);
					$u[$k] = $v;
				}
			}
		}

		echo '
		<div class="unit'.($this->diafan->configmodules("show_yandex_element") ? ' depend_field" depend="show_yandex' : '').'" id="yandex">
			<div class="infofield">'
				. $this->diafan->variable_name().$this->diafan->help().'
			</div>'
		. $this->diafan->_('Основная ставка').': '.$this->diafan->help('Целое положительное значение. Например: 21, что соответствует ставке 0,21 у.е. Если параметр не задан, то задается настройками модуля.')
		.'<br>
		<input type="text" maxLength="70" name="yandex_bid" value="'.( ! empty($u["bid"]) ? $u["bid"] : '').'"> <br><br>'
		. $this->diafan->_('Группа товаров / категория').':<br>
		<input type="text" maxLength="70" name="yandex_typePrefix" value="'.( ! empty($u["typePrefix"]) ? $u["typePrefix"] : '').'"><br><br>'
		. $this->diafan->_('Производитель').':<br>
		<input type = "text" maxLength="70" name="yandex_vendor" value="'.( ! empty($u["vendor"]) ? $u["vendor"] : '').'"><br><br>'
		. $this->diafan->_('Модель').':<br>
		<input type = "text" maxLength="70" name="yandex_model" value="'.( ! empty($u["model"]) ? $u["model"] : '').'"><br><br>'
		. $this->diafan->_('Код товара (указывается код производителя)').':<br>
		<input type = "text" maxLength="70" name="yandex_vendorCode" value="'.( ! empty($u["vendorCode"]) ? $u["vendorCode"] : '').'"><br><br>'
		. $this->diafan->_('Минимальная сумма заказа, предоплата, акции (SALES_NOTES)').':<br>
		<input type = "text" maxLength="50" name="yandex_sales_notes" value="'.( ! empty($u["sales_notes"]) ? $u["sales_notes"] : '').'"><br><br>
		<input type = "checkbox" name="yandex_manufacturer_warranty" id="yandex_manufacturer_warranty" value="1"'.( ! empty($u["manufacturer_warranty"]) ? ' checked' : '').'><label for="yandex_manufacturer_warranty">'
		. $this->diafan->_('Официальная гарантия производителя').'</label><br>'
		. $this->diafan->_('Страна производства товара').':<br>
		<input type = "text" maxLength="70" name="yandex_country_of_origin" value="'.( ! empty($u["country_of_origin"]) ? $u["country_of_origin"] : '').'">
		</div>';
	}

	/**
	 * Редактирование поля "Google Merchant"
	 * @return void
	 */
	public function edit_variable_google()
	{
		if (! $this->diafan->configmodules("google"))
		{
			return;
		}

		$u = array();

		if (! $this->diafan->is_new)
		{
			if ($this->diafan->value)
			{
				$conf_arr = explode("\n", $this->diafan->value);
				foreach ($conf_arr as $c_a)
				{
					list($k, $v) = explode("=", $c_a, 2);
					$u[$k] = $v;
				}
			}
		}
		$array = array(
			array(
				"name" => "unit_pricing_measure",
				"title" => 'Количество и единица измерения товара',
				"help" => 'Пример: 1.5kg',
			),
			array(
				"name" => "unit_pricing_base_measure",
				"title" => 'Базовая единица, за которую рассчитывается цена товара',
				"help" => 'Пример: 100g',
			),
			array(
				"name" => "installment_months",
				"type" => "numtext",
				"title" => 'Cведения об оплате покупки в рассрочку: количество ежемесячных взносов',
				"help" => 'Пример: 6',
			),
			array(
				"name" => "installment_amount",
				"type" => "numtext",
				"title" => 'Cведения об оплате покупки в рассрочку: cумма ежемесячного платежа',
				"help" => 'Пример: 50',
				"currency" => true,
			),
			array(
				"name" => "google_product_category",
				"title" => 'Категория товара в соответствии с классификацией Google',
				"help" => 'Пример: Apparel & Accessories > Clothing > Outerwear > Coats & Jackets [Предметы одежды и принадлежности > Одежда > Верхняя одежда > Пальто и куртки] или 5598',
			),
			array(
				"name" => "gtin",
				"title" => 'Код международной маркировки и учета логистических единиц для товара',
				"help" => 'Пример: 3234567890126',
			),
			array(
				"name" => "mpn",
				"title" => 'Код производителя товара',
				"help" => 'Пример: GO12345OOGLE',
			),
			array(
				"name" => "condition",
				"type" => "select",
				"title" => 'Состояние товара',
				"select" => array(
					'' => 'новый',
					'refurbished' => 'восстановленный',
					'used' => 'б/у',
				),
			),
			array(
				"name" => "adult",
				"type" => "checkbox",
				"title" => 'Товар содержит материалы сексуального характера',
			),
			array(
				"name" => "multipack",
				"type" => "numtext",
				"title" => 'Число идентичных товаров в наборе, сформированном продавцом',
				"help" => 'Пример: 6',
			),
			array(
				"name" => "energy_efficiency_class",
				"type" => "select",
				"title" => 'Класс энергоэффективности товара',
				"select" => array(
					'' => '-',
					'G' => 'G',
					'F' => 'F',
					'E' => 'E',
					'D' => 'D',
					'C' => 'C',
					'B' => 'B',
					'A' => 'A',
					'A+' => 'A+',
					'A++' => 'A++',
					'A+++' => 'A+++',
				),
			),
			array(
				"name" => "age_group",
				"type" => "select",
				"title" => 'Возраст потребителей, для которых предназначен товар',
				"select" => array(
					'' => '-',
					'newborn' => 'новорожденные до трех месяцев',
					'infant' => 'младенцы от трех месяцев до года',
					'toddler' => 'ясельный возраст от года до пяти лет',
					'kids' => 'дети от 5 до 13 лет',
					'adult' => 'взрослые от 13 лет',
				),
			),
			array(
				"name" => "color",
				"title" => 'Цвет товара',
				"help" => 'Пример: black [черный]',
			),
			array(
				"name" => "gender",
				"type" => "select",
				"title" => 'Пол пользователей, для которых предназначен товар',
				"select" => array(
					'' => '-',
					'male' => 'мужской',
					'female' => 'женский',
					'unisex' => 'унисекс',
				),
			),
			array(
				"name" => "material",
				"title" => 'Материал, из которого изготовлен товар',
				"help" => 'Пример: leather [кожа]',
			),
			array(
				"name" => "pattern",
				"title" => 'Узор или рисунок на товаре',
				"help" => 'Пример: striped [в_​полоску]',
			),
			array(
				"name" => "size",
				"title" => 'Размер товара',
				"help" => 'Пример: XL',
			),
			array(
				"name" => "size_type",
				"type" => "select",
				"title" => 'Особенности покроя товара',
				"select" => array(
					'' => '-',
					'regular' => 'стандартный',
					'petite' => 'для миниатюрных',
					'plus' => 'для полных',
					'big and tall' => 'для крупных и высоких',
					'maternity' => 'для беременных',
				),
			),
			array(
				"name" => "size_system",
				"type" => "select",
				"title" => 'Система размеров, которая используется в целевой стране товара',
				"select" => array(
					'' => '-',
					'US' => 'US',
					'UK' => 'UK',
					'EU' => 'EU',
					'DE' => 'DE',
					'FR' => 'FR',
					'JP' => 'JP',
					'CN (China)' => 'CN',
					'IT' => 'IT',
					'BR' => 'BR',
					'MEX' => 'MEX',
					'AU' => 'AU',
				),
			),
			array(
				"name" => "excluded_destination",
				"type" => "select",
				"title" => 'Атрибут позволяет исключать определенные товары из рекламных кампаний',
				"select" => array(
					'' => '-',
					'Shopping' => 'Shopping',
					'Display_Ads' => 'Медийные_объявления',
				),
			),
			array(
				"name" => "custom_label_0",
				"title" => 'Ярлык, по которому можно группировать товары в рамках кампании',
				"help" => 'Пример: seasonal [сезонные]',
			),
			array(
				"name" => "promotion_id",
				"title" => 'Идентификатор, по которому товары сопоставляются с условиями промоакций',
				"help" => 'Пример: ABC123',
			),
			array(
				"name" => "shipping",
				"title" => 'Стоимость доставки товара',
			),
			array(
				"name" => "shipping_label",
				"title" => 'Этикетка, по которой можно сопоставить товар и стоимость его доставки в настройках аккаунта',
				"help" => 'Пример: perishable [скоропортящийся]',
			),
			array(
				"name" => "shipping_weight",
				"title" => 'Вес товара, по которому рассчитывается стоимость доставки',
				"help" => 'Пример: 3. Если не указан, будет подставлено значение поля «Вес».',
				"weight_measure" => true,
			),
			array(
				"name" => "shipping_length",
				"title" => 'Длина упаковки с товаром; нужна, чтобы рассчитать стоимость доставки по габаритному весу',
				"help" => 'Пример: 20. Если не указан, будет подставлено значение поля «Длина».',
				'dimension_measure' => true,
			),
			array(
				"name" => "shipping_width",
				"title" => 'Ширина упаковки с товаром; нужна, чтобы рассчитать стоимость доставки по габаритному весу',
				"help" => 'Пример: 20. Если не указан, будет подставлено значение поля «Ширина».',
				'dimension_measure' => true,
			),
			array(
				"name" => "shipping_height",
				"title" => 'Высота упаковки с товаром; нужна, чтобы рассчитать стоимость доставки по габаритному весу',
				"help" => 'Пример: 20. Если не указан, будет подставлено значение поля «Высота».',
				'dimension_measure' => true,
			),
			array(
				"name" => "max_handling_time",
				"title" => 'Максимальный период времени с момента размещения заказа до оправки товара',
				"help" => 'Пример: 3',
			),
			array(
				"name" => "min_handling_time",
				"title" => 'Минимальный период времени с момента размещения заказа до оправки товара',
				"help" => 'Пример: 2',
			),
		);

		echo '
		<div class="unit'.($this->diafan->configmodules("show_google_element") ? ' depend_field" depend="show_google' : '').'" id="google">
			<div class="infofield">'
				. $this->diafan->variable_name().$this->diafan->help().'
			</div>';
		foreach($array as $i => $a)
		{
			if(empty($a["type"]))
			{
				$a["type"] = "text";
			}
			if($i)
			{
				echo '<br><br>';
			}
			if($a["type"] == "checkbox")
			{
				echo '<input name="google_'.$a["name"].'" id="google_'.$a["name"].'" type="checkbox" value="1" '.(! empty($u[$a["name"]]) ? ' checked' : '').'>';
				echo '<label for="google_'.$a["name"].'">'.$this->diafan->_($a["title"]).'</label> ';
			}
			else
			{
				echo '<label for="google_'.$a["name"].'">'.$this->diafan->_($a["title"]).':</label> ';
				if(! empty($a["help"]))
				{
					echo ' '.$this->diafan->help($a["help"]);
				}
				echo '<br>';
			}
			switch($a["name"])
			{
				case "unit_pricing_measure":
					$v1 = '';
					$v2 = '';
					if(! empty($u[$a["name"]]) && preg_match('/([0-9\.]+)( )*([a-z]+)/', $u[$a["name"]], $m))
					{
						$v1 = $m[1];
						$v2 = $m[3];
					}
					echo '<input type="text" name="google_'.$a["name"].'_1" value="'.$v1.'" class="number" id="google_'.$a["name"].'">
					<select name="google_'.$a["name"].'_2">
					<option value="kg">kg</option>
					<option value="oz"'.($v2 == 'oz' ? ' selected' : '').'>oz</option>
					<option value="lb"'.($v2 == 'lb' ? ' selected' : '').'>lb</option>
					<option value="mg"'.($v2 == 'mg' ? ' selected' : '').'>mg</option>
					<option value="g"'.($v2 == 'g' ? ' selected' : '').'>g</option>
					<option value="floz"'.($v2 == 'floz' ? ' selected' : '').'>floz</option>
					<option value="pt"'.($v2 == 'pt' ? ' selected' : '').'>pt</option>
					<option value="qt"'.($v2 == 'qt' ? ' selected' : '').'>qt</option>
					<option value="gal"'.($v2 == 'gal' ? ' selected' : '').'>gal</option>
					<option value="ml"'.($v2 == 'ml' ? ' selected' : '').'>ml</option>
					<option value="cl"'.($v2 == 'cl' ? ' selected' : '').'>cl</option>
					<option value="l"'.($v2 == 'l' ? ' selected' : '').'>l</option>
					<option value="cbm"'.($v2 == 'cbm' ? ' selected' : '').'>cbm</option>
					<option value="in"'.($v2 == 'in' ? ' selected' : '').'>in</option>
					<option value="ft"'.($v2 == 'ft' ? ' selected' : '').'>ft</option>
					<option value="yd"'.($v2 == 'yd' ? ' selected' : '').'>yd</option>
					<option value="cm"'.($v2 == 'cm' ? ' selected' : '').'>cm</option>
					<option value="m"'.($v2 == 'm' ? ' selected' : '').'>m</option>
					<option value="sqft"'.($v2 == 'sqft' ? ' selected' : '').'>sqft</option>
					<option value="sqm"'.($v2 == 'sqm' ? ' selected' : '').'>sqm</option>
					<option value="ct"'.($v2 == 'ct' ? ' selected' : '').'>ct</option>';
					echo '</select>';
					break;

				case "unit_pricing_base_measure":
					$v1 = '';
					$v2 = '';
					if(! empty($u[$a["name"]]) && preg_match('/([0-9]+)( )*([a-z]+)/', $u[$a["name"]], $m))
					{
						$v1 = $m[1];
						$v2 = $m[3];
					}
					echo '<select name="google_'.$a["name"].'_1" id="google_'.$a["name"].'">
					<option value=""></option>
					<option value="1"'.($v1 == '1' ? ' selected' : '').'>1</option>
					<option value="10"'.($v1 == '10' ? ' selected' : '').'>10</option>
					<option value="100"'.($v1 == '100' ? ' selected' : '').'>100</option>
					<option value="2"'.($v1 == '2' ? ' selected' : '').'>2</option>
					<option value="4"'.($v1 == '4' ? ' selected' : '').'>4</option>
					<option value="8"'.($v1 == '8' ? ' selected' : '').'>8</option>
					</select>
					<select name="google_'.$a["name"].'_2">
					<option value="kg">kg</option>
					<option value="oz"'.($v2 == 'oz' ? ' selected' : '').'>oz</option>
					<option value="lb"'.($v2 == 'lb' ? ' selected' : '').'>lb</option>
					<option value="mg"'.($v2 == 'mg' ? ' selected' : '').'>mg</option>
					<option value="g"'.($v2 == 'g' ? ' selected' : '').'>g</option>
					<option value="floz"'.($v2 == 'floz' ? ' selected' : '').'>floz</option>
					<option value="pt"'.($v2 == 'pt' ? ' selected' : '').'>pt</option>
					<option value="qt"'.($v2 == 'qt' ? ' selected' : '').'>qt</option>
					<option value="gal"'.($v2 == 'gal' ? ' selected' : '').'>gal</option>
					<option value="ml"'.($v2 == 'ml' ? ' selected' : '').'>ml</option>
					<option value="cl"'.($v2 == 'cl' ? ' selected' : '').'>cl</option>
					<option value="l"'.($v2 == 'l' ? ' selected' : '').'>l</option>
					<option value="cbm"'.($v2 == 'cbm' ? ' selected' : '').'>cbm</option>
					<option value="in"'.($v2 == 'in' ? ' selected' : '').'>in</option>
					<option value="ft"'.($v2 == 'ft' ? ' selected' : '').'>ft</option>
					<option value="yd"'.($v2 == 'yd' ? ' selected' : '').'>yd</option>
					<option value="cm"'.($v2 == 'cm' ? ' selected' : '').'>cm</option>
					<option value="m"'.($v2 == 'm' ? ' selected' : '').'>m</option>
					<option value="sqft"'.($v2 == 'sqft' ? ' selected' : '').'>sqft</option>
					<option value="sqm"'.($v2 == 'sqm' ? ' selected' : '').'>sqm</option>
					<option value="ct"'.($v2 == 'ct' ? ' selected' : '').'>ct</option>';
					echo '</select>';
					break;

				case "shipping":
					$v = array();
					if(! empty($u[$a["name"]]))
					{
						$ss = explode(";", $u[$a["name"]]);
						foreach($ss as $s)
						{
							$c = explode(":", $s);
							if(count($c) == 4)
							{
								$v[] = $c;
							}
						}
					}
					echo '<table class="param_table">
					<tr>
						<th>'.$this->diafan->_('страна').'</th>
						<th>'.$this->diafan->_('регион').'</th>
						<th>'.$this->diafan->_('сервис доставки').'</th>
						<th>'.$this->diafan->_('цена').'</th>
						<th></th>
					</tr>';
					foreach($v as $c)
					{
						echo '<tr class="param">
						<td>
						<input type="text" name="google_shipping_1[]" value="'.$c[0].'">
						</td><td>
						<input type="text" name="google_shipping_2[]" value="'.$c[1].'">
						</td><td>
						<input type="text" name="google_shipping_3[]" value="'.$c[2].'">
						</td><td>
						<input type="text" name="google_shipping_4[]" value="'.$c[3].'"> '.$this->diafan->configmodules('currency_google', 'shop').'
						</td>
						<td class="param_value_td6"><span class="param_actions" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'" action="delete_param">
							<i class="fa fa-times-circle"></i>
							'.$this->diafan->_('Удалить').'
						</span></td></tr>';
					}
					echo '<tr class="param">
					<td>
					<input type="text" name="google_shipping_1[]" value="">
					</td><td>
					<input type="text" name="google_shipping_2[]" value="">
					</td><td>
					<input type="text" name="google_shipping_3[]" value="">
					</td><td>
					<input type="text" name="google_shipping_4[]" value=""> '.$this->diafan->configmodules('currency_google', 'shop').'
					</td><td></td>
					</tr>

					</table>
					<a href="javascript:void(0)" class="param_plus" title="'.$this->diafan->_('Добавить').'"><i class="fa fa-plus-square"></i> '.$this->diafan->_('Добавить').'</a>';
					break;

				case "custom_label_0":
					echo '<input type="text" name="google_'.$a["name"].'" value="'.(! empty($u[$a["name"]]) ? $u[$a["name"]] : '').'" id="google_'.$a["name"].'">
					<p>
					<input type="text" name="google_custom_label_1" value="'.(! empty($u['custom_label_1']) ? $u['custom_label_1'] : '').'" id="google_custom_label_1">
					</p><p>
					<input type="text" name="google_custom_label_2" value="'.(! empty($u['custom_label_2']) ? $u['custom_label_2'] : '').'" id="google_custom_label_2">
					</p><p>
					<input type="text" name="google_custom_label_3" value="'.(! empty($u['custom_label_3']) ? $u['custom_label_3'] : '').'" id="google_custom_label_3">
					</p><p>
					<input type="text" name="google_custom_label_4" value="'.(! empty($u['custom_label_4']) ? $u['custom_label_4'] : '').'" id="google_custom_label_4"></p>';
					break;

				default:
					switch($a["type"])
					{
						case "select":
							echo '<select name="google_'.$a["name"].'" id="google_'.$a["name"].'">';
							foreach($a["select"] as $k => $v)
							{
								echo '<option value="'.$k.'"'.(! empty($u[$a["name"]]) && $u[$a["name"]] == $k ? ' selected' : '').'>'.$this->diafan->_($v).'</option>';
							}
							echo '</select>';
							break;

						case "checkbox":
							break;

						default:
							echo '<input type="text" name="google_'.$a["name"].'" value="'.(! empty($u[$a["name"]]) ? $u[$a["name"]] : '').'"'
							.($a["type"] == "numtext" ? ' class="number"' : '').' id="google_'.$a["name"].'">';
							break;
					}
					break;
			}
			if(! empty($a["currency"]))
			{
				echo ' '.$this->diafan->configmodules('currency_google', 'shop');
			}
			if(! empty($a["weight_measure"]))
			{
				echo ' '.$this->diafan->configmodules('weight_measure_google', 'shop');
			}
			if(! empty($a["dimension_measure"]))
			{
				echo ' '.$this->diafan->configmodules('dimension_measure_google', 'shop');
			}
		}
		echo '</div>';
	}

	/**
	 * Редактирование кнопки "Скидки"
	 * @return void
	 */
	public function edit_variable_discounts()
	{
		// скидки
		$discounts = DB::query_fetch_all("SELECT * FROM {shop_discount} WHERE act='1' AND trash='0' AND threshold_cumulative=0 AND threshold=0 AND (date_finish=0 OR date_finish>%d)", mktime(date("H"), 0, 0));
		foreach ($discounts as &$d)
		{
			$d["objects"] = DB::query_fetch_all("SELECT * FROM {shop_discount_object} WHERE discount_id=%d", $d["id"]);
		}

		// категории текущего товара
		$cats = array();
		if(! $this->diafan->is_new)
		{
			$cats = DB::query_fetch_value("SELECT cat_id FROM {shop_category_rel} WHERE element_id=%d", $this->diafan->id, "cat_id");
		}
		foreach ($discounts as &$d)
		{
			$d["in_discount"] = false;
			$d["in_discount_check"] = false;
			if(empty($d["objects"][0]) || empty($d["objects"][0]["cat_id"]) && empty($d["objects"][0]["good_id"]))
			{
				$d["in_discount"] = true;
			}
			elseif(! $this->diafan->is_new)
			{
				foreach ($d["objects"] as $d_o)
				{
					if($d_o["cat_id"] && in_array($d_o["cat_id"], $cats))
					{
						$d["in_discount"] = true;
						break;
					}
					elseif($d_o["good_id"] == $this->diafan->id)
					{
						$d["in_discount_check"] = true;
						break;
					}

				}
			}
		}
		echo '<h2><a href="'.BASE_PATH_HREF.'shop/discount/'.($this->diafan->_route->site ? 'site'.$this->diafan->_route->site.'/' : '').'">'.$this->diafan->variable_name().'</a>'.$this->diafan->help().'</h2>
		<div class="unit" id="discounts">';
		foreach ($discounts as &$d)
		{
			echo '<input type="checkbox" name="discounts[]" value="'.$d["id"].'"';
			if($d["in_discount_check"])
			{
				echo ' checked';
			}
			if($d["in_discount"])
			{
				echo ' checked disabled';
			}
			echo ' id="discount'.$d["id"].'">
			<label for="discount'.$d["id"].'"><a href="'.BASE_PATH_HREF.'shop/discount/edit'.$d["id"].'/" target="_blank">';
			if($d["deduction"])
			{
				echo $d["deduction"].' '.$this->diafan->configmodules("currency", "shop");
			}
			else
			{
				echo $d["discount"].'%';
			}
			echo '</a>';
			if($d["in_discount"])
			{
				echo ' – '.$this->diafan->_('глобальная скидка');
				echo ' '.$this->diafan->help('Это глобальная скидка, применяемая ко всему магазину, сумме заказа или группе покупателя. Глобальную скидку можно отменить только в модуле «Скидки». Чтобы иметь возможность назначать скидки на единичные товары, создайте скидку и добавьте ей хотя бы один товар.');
			}
			if($d["text"])
			{
				echo ' – '.$d["text"];
			}
			echo '</label>';
		}
		echo '</div>';
	}

	/**
	 * Редактирование кнопки "Сопутствующие услуги"
	 * @return void
	 */
	public function edit_variable_additional_costs()
	{
		$cat_id = $this->diafan->values("cat_id") ? $this->diafan->values("cat_id") : $this->diafan->_route->cat_id;

		$rows = DB::query_fetch_all("SELECT a.[name], a.percent, a.price, a.id FROM {shop_additional_cost} AS a INNER JOIN {shop_additional_cost_category_rel} AS c ON c.element_id=a.id AND c.cat_id IN (0".($cat_id ? ','.$cat_id : '').") WHERE a.shop_rel='1' AND a.trash='0'  GROUP BY a.id ORDER BY a.sort ASC");
		if(! $rows)
		{
			return;
		}
		if(! $this->diafan->is_new)
		{
			$rels = DB::query_fetch_key("SELECT * FROM {shop_additional_cost_rel} WHERE element_id=%d", $this->diafan->id, "additional_cost_id");
		}
		else
		{
			$rels = array();
		}
		echo '<h2><a href="'.BASE_PATH_HREF.'shop/additionalcost/'.($this->diafan->_route->site ? 'site'.$this->diafan->_route->site.'/' : '').'">'.$this->diafan->variable_name().'</a>'.$this->diafan->help().'</h2><div class="unit" id="additional_costs">';
		foreach ($rows as $row)
		{
			echo '<input type="checkbox" name="additional_costs[]" value="'.$row["id"].'"';
			if(isset($rels[$row["id"]]))
			{
				echo ' checked';
			}
			echo ' id="additional_cost_'.$row["id"].'">
			<label for="additional_cost_'.$row["id"].'"><a href="'.BASE_PATH_HREF.'shop/additionalcost/edit'.$row["id"].'/" target="_blank">'.$row["name"].'</a> ';
			if($row["percent"])
			{
				echo '+ '.$row["percent"].'%';
			}
			else
			{
				echo '<input name="additional_costs_price_'.$row["id"].'" value="'.(! empty($rels[$row["id"]]["summ"]) ? $rels[$row["id"]]["summ"] : $row["price"]).'" type="text" style="width: 80px;"> '.$this->diafan->configmodules("currency", "shop");
			}
			echo '</label>';
		}
		echo '</div>';
		echo '
		<div class="unit">
			<a href="'.BASE_PATH_HREF.'shop/additionalcost/addnew1/" target="_blank" class="btn btn_small btn_blue">
				<i class="fa fa-plus-square"></i>
				'.$this->diafan->_('Добавить услугу').'
			</a>
		</div>';
	}

	/**
	 * Редактирование поля "Дополнительные параметры"
	 *
	 * @return void
	 */
	public function edit_variable_param()
	{
		//значения характеристик
		$values = array();
		$rvalues = array();
		if (! $this->diafan->is_new)
		{
			$rows_el = DB::query_fetch_all("SELECT value".$this->diafan->_languages->site." as rv, [value], param_id FROM {shop_param_element} WHERE element_id=%d", $this->diafan->id);
			foreach ($rows_el as $row_el)
			{
				$values[$row_el["param_id"]][] = $row_el["value"];
				$rvalues[$row_el["param_id"]][] = $row_el["rv"];
			}
		}

		echo '<h2>
		<a href="'.BASE_PATH_HREF.'shop/param/'.($this->diafan->_route->site ? 'site'.$this->diafan->_route->site.'/' : '').'">'.$this->diafan->variable_name().'</a>
		'.$this->diafan->help('Дополнительные характеристики товара, например, объем, цвет, вес, материал, состав, производитель и т.п. Вы можете создать сколько угодно дополнительных характеристик товара с разными типами. Чтобы создать характеристику, от которой зависит цена товара, выберите тип «Список с выбором нескольких значений» и отметьте «Доступен к выбору при заказе» (см. подробнее в модуле «Характеристики»).').'
		</h2>';

		if ( ! empty($this->cache["multiple_params"]))
		{
			echo '<div class="unit" id="price_arr">
			<div class="infofield">
				'.$this->diafan->_('Цены и изображения для характеристик').'
				'.$this->diafan->help('Если у товара есть хотя бы один тип характеристики «Список с выбором нескольких значений» с параметром «Доступен к выбору при заказе», то для каждой характеристики можно назначить отдельную цену, свою фотографию и разное количество.').'
			</div>
			<div class="swipe_param_table" title="'.$this->diafan->_('Прокрутите слой с характеристиками вправо').'"><span><i class="fa fa-hand-o-up"></i></span><i class="fa fa-exchange"></i></div>
			<table class="param_table"><tr>';
			if($this->cache["multiple_params"])
			{
				echo '<th>'.$this->diafan->_('Характеристики').'</th>';
			}

			echo '<th>'.$this->diafan->_('Цена').'</th>';
			echo '<th>'.$this->diafan->_('Старая цена').'</th>';

			if($this->currency)
			{
				echo '<th>' .$this->diafan->_('Валюта'). '</th>';
			}
			if ($this->diafan->configmodules("use_count_goods", "shop", $this->diafan->_route->site))
			{
				echo '<th>'.$this->diafan->_('Кол-во').'</th>';
			}
			echo '<th>' .$this->diafan->_('Изображение'). '</th>';
			echo '<th></th></tr>';
			if($price_arr = $this->diafan->values("price_arr"))
			{
				$format_price_1 = $this->diafan->_shop->price_num_decimal_places();
				foreach ($price_arr as $price)
				{
					echo '<tr class="param">';
					if($this->cache["multiple_params"])
					{
						echo '<td class="param_value_td1">';

						echo '<div class="shop_param_unit_empty"><i class="tooltip fa fa-question-circle" title="'.$this->diafan->_('Зависимая цена при отмеченном параметре «Влияет на цену» у характеристики ниже.').'"></i></div>';

						foreach ($this->cache["multiple_params"] as &$row)
						{
							echo '<div class="shop_param_sel shop_param_unit param_value_unit'.$row["id"].'">
							<span class="shop_param_title">'.$row["name"].'</span>
							<select name="'.(empty($this->cache["depend_price"][$row["id"]]) ? 'hide_' : '').'param_value'.$row["id"].'[]">';
							foreach ($row["options"] as $opt)
							{
								echo '<option value="'.$opt["id"].'"'.(! empty($price["param"][$row["id"]]) && $price["param"][$row["id"]] == $opt["id"] ? ' selected' : '').'>'.$opt["name"].'</option>';
							}
							echo '</select></div>';
						}
						echo '</td>';
					}
					if(($price["price"] * 100) % 100)
					{
						$num_decimal_places = $format_price_1;
					}
					else
					{
						$num_decimal_places = 0;
					}

					echo '<td class="param_value_td2">
						<input name="param_price[]" value="'.number_format($price["price"], $num_decimal_places, ',', '').'" type="text" class="number">
					</td>';
					if(($price["old_price"]*100)%100)
					{
						$num_decimal_places = $format_price_1;
					}
					else
					{
						$num_decimal_places = 0;
					}

					echo '<td class="param_value_td2">
						<input name="param_old_price[]" value="'.number_format($price["old_price"], $num_decimal_places, ',', '').'" type="text" class="number" placeholder="'.$this->diafan->_('нет').'">
					</td>';
					if($this->currency)
					{
						echo '<td class="param_value_td3"><select name="param_currency[]">
						<option value="0">'.$this->diafan->_('основная').'</option>';
						foreach ($this->currency as $c)
						{
							echo ' <option value="'.$c["id"].'"'.($price["currency_id"] == $c["id"] ? ' selected' : '').'>'.$c["name"].'</option>';
						}
						echo '</select></td>';
					}

					if ($this->diafan->configmodules("use_count_goods", "shop", $this->diafan->_route->site))
					{
						echo '<td class="param_value_td4"><input name="param_count[]" value="'.$price["count_goods"].'" type="text" class="number"></td>';
					}
					echo '<td class="param_value_td5 param_image_rel_actions">
					<div class="images_actions"><a href="javascript:void(0)" class="add_price_image_rel'.($price["image_rel"]["id"] ? ' hide' : '').'">
						<span class="image">
							<i class="fa fa-plus-square"></i>
						</span>
						<span>'.$this->diafan->_('Добавить связь с изображением').'</span>
					</a>';
					if($price["image_rel"]["id"])
					{
						echo '<img src="'.BASE_PATH.USERFILES.'/small/'.($price["image_rel"]["folder_num"] ? $price["image_rel"]["folder_num"].'/' : '').$price["image_rel"]["name"].'">';
					}
					echo '<div class="images_button'.(! $price["image_rel"]["id"] ? ' hide' : '').'">
					<a confirm="'.$this->diafan->_('Вы действительно хотите удалить связь?').'" class="delete_price_image_rel" href="javascript:void(0)"><i class="fa fa-close"></i></a>
					</div>
					<input name="price_image_rel[]" value="'.$price["image_rel"]["id"].'" type="hidden">
					<input name="param_import_id[]" value="'.$price["import_id"].'" type="hidden">
					</div>
					</td>';

					echo '<td class="param_value_td6"><span class="param_actions" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'" action="delete_param">
						<i class="fa fa-times-circle"></i>
						'.$this->diafan->_('Удалить').'
					</span></td>';
					echo '</tr>';
				}
			}
			else
			{
				echo '<tr class="param">';
				if($this->cache["multiple_params"])
				{
					echo '<td class="param_value_td1">';

					foreach ($this->cache["multiple_params"] as &$row)
					{
						echo '<div class="shop_param_sel shop_param_unit param_value_unit'.$row["id"].'">
						<span class="shop_param_title">'.$row["name"].'</span>
						<select name="'.(empty($this->cache["depend_price"][$row["id"]]) ? 'hide_' : '').'param_value'.$row["id"].'[]">';
						foreach ($row["options"] as $opt)
						{
							echo '<option value="'.$opt["id"].'">'.$opt["name"].'</option>';
						}
						echo '</select></div>';
					}
					echo '</td>';
				}

				echo '<td class="param_value_td2">
					<input name="param_price[]" value="" type="text" class="number">
				</td>
				<td class="param_value_td2">
					<input name="param_old_price[]" value="" type="text" class="number">
				</td>';
				if($this->currency)
				{
					echo '<td class="param_value_td3"><select name="param_currency[]">
					<option value="0">'.$this->diafan->_('основная').'</option>';
					foreach ($this->currency as $c)
					{
						echo ' <option value="'.$c["id"].'">'.$c["name"].'</option>';
					}
					echo '</select></td>';
				}

				if ($this->diafan->configmodules("use_count_goods", "shop", $this->diafan->_route->site))
				{
					echo '<td class="param_value_td4"><input name="param_count[]" value="" type="text" class="number"></td>';
				}
				echo '<td class="param_value_td5 param_image_rel_actions">
				<div class="images_actions">
				<a href="javascript:void(0)" class="add_price_image_rel">
					<span class="image">
						<i class="fa fa-plus-square"></i>
					</span>
					<span>'.$this->diafan->_('Добавить связь с изображением').'</span>
				</a>
				<div class="images_button hide">
				<a confirm="'.$this->diafan->_('Вы действительно хотите удалить связь?').'" class="delete_price_image_rel" href="javascript:void(0)"><i class="fa fa-close"></i></a>
				</div>
				<input name="price_image_rel[]" value="" type="hidden">
				<input name="param_import_id[]" value="" type="hidden">
				</div>';

				echo '</td>';

				echo '<td class="param_value_td6"><span class="param_actions" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'" action="delete_param">
					<i class="fa fa-times-circle"></i>
					'.$this->diafan->_('Удалить').'
				</span></td>';
			}
			echo '</table>';
			echo '
			<span class="btn btn_small btn_blue param_plus">
				<i class="fa fa-plus-square"></i>
				'.$this->diafan->_('Добавить вариант').'
			</span>
			</div>';
		}
		$class = 'shop_param';
		foreach ($this->cache["params"] as &$row)
		{
			$attr = '';

			$help = $this->diafan->help($row["text"]);
			switch($row["type"])
			{
				case 'title':
					$this->diafan->show_table_tr_title("param".$row["id"], $row["name"], $help, $attr, $class);
					break;

				case 'text':
					$value = (! empty($values[$row["id"]]) ? $values[$row["id"]][0] : '');
					$this->diafan->show_table_tr_text("param".$row["id"], $row["name"], $value, $help, false, $attr, $class);
					break;

				case 'phone':
					$value = (! empty($values[$row["id"]]) ? $values[$row["id"]][0] : '');
					$this->diafan->show_table_tr_text("param".$row["id"], $row["name"], $value, $help, false, $attr, $class);
					break;

				case 'textarea':
					$value = (! empty($values[$row["id"]]) ? $values[$row["id"]][0] : '');
					$this->diafan->show_table_tr_textarea("param".$row["id"], $row["name"], $value, $help, false, $attr, $class);
					break;

				case 'editor':
					$value = (! empty($values[$row["id"]]) ? $values[$row["id"]][0] : '');
					$this->diafan->show_table_tr_editor("param".$row["id"], $row["name"], $value, $help, $attr, $class);
					break;

				case 'email':
					$value = (! empty($rvalues[$row["id"]]) ? $rvalues[$row["id"]][0] : '');
					$this->diafan->show_table_tr_email("param".$row["id"], $row["name"], $value, $help, false, $attr, $class);
					break;

				case 'date':
					$value = (! empty($rvalues[$row["id"]]) ? $this->diafan->unixdate($this->diafan->formate_from_date($rvalues[$row["id"]][0])) : '');
					$this->diafan->show_table_tr_date("param".$row["id"], $row["name"], $value, $help, false, $attr, $class);
					break;

				case 'datetime':
					$value = (! empty($rvalues[$row["id"]]) ? $this->diafan->unixdate($this->diafan->formate_from_datetime($rvalues[$row["id"]][0])) : '');
					$this->diafan->show_table_tr_datetime("param".$row["id"], $row["name"], $value, $help, false, $attr, $class);
					break;

				case 'numtext':
					$value = (! empty($rvalues[$row["id"]]) ? $rvalues[$row["id"]][0] : 0);
					$this->diafan->show_table_tr_numtext("param".$row["id"], $row["name"], $value, $help, false, $attr, $class);
					break;

				case 'floattext':
					$value = (! empty($rvalues[$row["id"]]) ? $rvalues[$row["id"]][0] : 0);
					$this->diafan->show_table_tr_floattext("param".$row["id"], $row["name"], $value, $help, false, $attr, $class);
					break;

				case 'checkbox':
					$value = (! empty($rvalues[$row["id"]]) ? $rvalues[$row["id"]][0] : 0);
					$this->diafan->show_table_tr_checkbox("param".$row["id"], $row["name"], $value, $help, false, $attr, $class);
					break;

				case 'select':
					$value = (! empty($rvalues[$row["id"]]) ? $rvalues[$row["id"]][0] : 0);
					$this->diafan->show_table_tr_select("param".$row["id"], $row["name"], $value, $help, false, $row["options"], $attr, $class);
					break;

				case 'multiple':
					$value = (! empty($rvalues[$row["id"]]) ? $rvalues[$row["id"]] : array());
					$this->show_table_tr_multiple_param($row["id"], $row["name"], $value, $help, $row["required"], $row["options"], $this->cache["depend_price"], $attr, $class);
					break;

				case 'attachments':
					Custom::inc('modules/attachments/admin/attachments.admin.inc.php');
					$attachment = new Attachments_admin_inc($this->diafan);
					$attachment->edit_param($row["id"], $row["name"], $row["text"], $row["config"], $attr, $class);
					break;

				case 'images':
					Custom::inc('modules/images/admin/images.admin.inc.php');
					$images = new Images_admin_inc($this->diafan);
					$images->edit_param($row["id"], $row["name"], $row["text"], $attr, $class);
					break;
			}
		}
		echo '
		<div class="unit">
			<a href="'.BASE_PATH_HREF.'shop/param/addnew1/" target="_blank" class="btn btn_small btn_blue">
				<i class="fa fa-plus-square"></i>
				'.$this->diafan->_('Добавить характеристику').'
			</a>
		</div>';
	}

	/**
	 * Выводит одну строку формы редактирования с типом "Список с выбором нескольких значений"
	 *
	 * @param string $id номер параметра
	 * @param string $name описание поля
	 * @param string $value значение поля
	 * @param string $help часть кода, выводящая подсказку к полю
	 * @param boolean $required поле обазательно для заполнения при заказе
	 * @param array $options значения списка
	 * @param array $depend_price характеристики, влияющие на цену
	 * @param string $attr атрибуты строки
	 * @param string $class CSS-класс
	 * @return void
	 */
	private function show_table_tr_multiple_param($id, $name, $values, $help, $required, $options, $depend_price, $attr, $class)
	{
		echo '
		<div class="unit'.($class ? ' '.$class : '').'" id="param'.$id.'"'.$attr.'>
			<div class="infofield">'.$name.$help.'</div>';
			if($required)
			{
				echo '<input type="checkbox" name="depend_price" id="input_depend_price'.$id.'" rel="'.$id.'"'.(! empty($depend_price[$id]) ? ' checked' : '').' class="label_full depend_price"> <label for="input_depend_price'.$id.'">'.$this->diafan->_('Влияет на цену').'</label><br>';
			}
			echo '<select name="param'.$id.'[]" multiple="multiple" size="11">
			<option value="all"'.(empty($values) || empty($values[0]) ? ' selected' : '').'>'.$this->diafan->_('Нет').'</option>';
			foreach ($options as $k => $select)
			{
				if(is_array($select))
				{
					$k = $select["id"];
					$select = $select["name"];
				}
				echo '<option value="'.$k.'"'.(in_array($k, $values) ? ' selected' : '').'>'.$select.'</option>';
			}
			echo '</select>
		</div>';
	}

	/**
	 * Редактирование поля "Файлы"
	 * @return void
	 */
	public function edit_variable_files()
	{
		if ( ! $this->diafan->configmodules("use_non_material_goods", "shop", $this->diafan->_route->site))
		{
			return;
		}

		if ($this->diafan->configmodules("use_count_goods", "shop", $this->diafan->_route->site))
		{
			echo '<div class="unit">'
			.$this->diafan->_('Для продажи файлов, необходимо отключить опцию «Учитывать остатки товаров на складе» в настройках магазина.')
			.'</div>';
			return;
		}

		$file_type = 1;
		echo '
		<div class="unit">
			<div class="infofield">'.$this->diafan->_('Загрузить файл').$this->diafan->help().'</div>';
		if ($this->diafan->values("link"))
		{
			$file_type = 2;
		}
		else
		{
			$rows = DB::query_fetch_all("SELECT id, name FROM {attachments} WHERE module_name='".$this->diafan->table."' AND element_id='%d'", $this->diafan->id);
			foreach ($rows as $row)
			{
				echo '<input type="hidden" name="delete_attachment" value="0"><div class="attachment">
				<a href="'.BASE_PATH.'attachments/get/'.$row["id"]."/".$row["name"].'">'.$row["name"].'</a>
				<a href="javascript:void(0)" class="delete delete_file"><i class="fa fa-close" title="'.$this->diafan->_('Удалить').'"></i></a></div>';
			}
		}
		echo '<div class="file_type1'.($file_type == 2 ? ' hide' : '').'"><input type="file" name="attachment" class="file"></div>
		</div>';
	}

	/**
	 * Сохранение поля "Акция"
	 * @return void
	 */
	public function save_variable_action()
	{
		$this->diafan->set_query("action='%d'");
		$this->diafan->set_value(! empty($_POST["check_action"]) ? 1 : 0);
	}

	/**
	 * Сохранение кнопки "Скидки"
	 * @return void
	 */
	public function save_variable_discounts()
	{
		// скидки
		$hide_discounts = DB::query_fetch_value("SELECT id FROM {shop_discount} WHERE act='0' OR trash='1' OR threshold_cumulative>0 OR threshold>1 OR date_finish>0 AND date_finish<%d", mktime(date("H"), 0, 0), "id");

		$old_discounts = DB::query_fetch_value("SELECT discount_id FROM {shop_discount_object} WHERE good_id=%d".($hide_discounts ? " AND discount_id NOT IN (".implode(",", $hide_discounts).")" : ''), $this->diafan->id, "discount_id");
		$discounts = array();
		if(! empty($_POST["discounts"]))
		{
			$discounts = $_POST["discounts"];
		}
		foreach ($discounts as $discount)
		{
			if(! in_array($discount, $old_discounts))
			{
				DB::query("INSERT INTO {shop_discount_object} (discount_id, good_id) VALUES (%d, %d)", $discount, $this->diafan->id);
				if(DB::query_result("SELECT COUNT(*) FROM {shop_discount_object} WHERE discount_id=%d AND good_id=0 AND cat_id=0", $discount))
				{
					DB::query("DELETE FROM {shop_discount_object} WHERE discount_id=%d AND good_id=0 AND cat_id=0", $discount);
					$this->diafan->_shop->price_calc(0, $discount);
				}
			}
		}
		foreach ($old_discounts as $discount)
		{
			if(! in_array($discount, $discounts))
			{
				DB::query("DELETE FROM {shop_discount_object} WHERE discount_id=%d AND good_id=%d", $discount, $this->diafan->id);
				if(! DB::query_result("SELECT COUNT(*) FROM {shop_discount_object} WHERE discount_id=%d", $discount))
				{
					DB::query("INSERT INTO {shop_discount_object} (discount_id, good_id) VALUES (%d, 0)", $discount);
					$this->diafan->_shop->price_calc(0, $discount);
				}
			}
		}
	}

	/**
	 * Сохранение кнопки "Сопутствующие услуги"
	 * @return void
	 */
	public function save_variable_additional_costs()
	{
		if(! $this->diafan->is_new)
		{
			$rels = DB::query_fetch_key("SELECT * FROM {shop_additional_cost_rel} WHERE element_id=%d", $this->diafan->id, "additional_cost_id");
			$rows = DB::query_fetch_key("SELECT * FROM {shop_additional_cost} WHERE shop_rel='1'", "id");
		}
		$ids = array();
		if(! empty($_POST["additional_costs"]))
		{
			foreach($_POST["additional_costs"] as $additional_cost_id)
			{
				$additional_cost_id = $this->diafan->filter($additional_cost_id, "integer");
				if(! $additional_cost_id)
					continue;

				$price = 0;
				if(! empty($_POST["additional_costs_price_".$additional_cost_id]) && $_POST["additional_costs_price_".$additional_cost_id] != $rows[$additional_cost_id]["price"])
				{
					$price = $_POST["additional_costs_price_".$additional_cost_id];
				}
				if(! empty($rels[$additional_cost_id]))
				{
					if($rels[$additional_cost_id]["summ"] != $price)
					{
						DB::query("UPDATE {shop_additional_cost_rel} SET summ=%f WHERE id=%d", $price, $rels[$additional_cost_id]["id"]);
					}
					$ids[] = $rels[$additional_cost_id]["id"];
				}
				else
				{
					$ids[] = DB::query("INSERT INTO {shop_additional_cost_rel} (element_id, additional_cost_id, summ) VALUES (%d, %d, %f)", $this->diafan->id, $additional_cost_id, $price);
				}
			}
		}
		if(! $this->diafan->is_new && $rels)
		{
			if($ids)
			{
				DB::query("DELETE FROM {shop_additional_cost_rel} WHERE element_id=%d AND id NOT IN (%s)", $this->diafan->id, implode(',', $ids));
			}
			else
			{
				DB::query("DELETE FROM {shop_additional_cost_rel} WHERE element_id=%d", $this->diafan->id);
			}
		}
	}

	/**
	 * Сохранение поля "Дополнительные параметры"
	 *
	 * @return void
	 */
	public function save_variable_param()
	{
		$site_id = $this->diafan->get_site_id();

		$ids = array();
		$cats = array(0);
		$lang = $this->diafan->_languages->site;
		if($_POST["cat_id"])
		{
			$cats[] = intval($_POST["cat_id"]);
		}
		if(! empty($_POST["cat_ids"]))
		{
			foreach ($_POST["cat_ids"] as $id)
			{
				$cats[] = intval($id);
			}
		}
		$rows = DB::query_fetch_all("SELECT p.id, p.type, p.required, p.config FROM {shop_param} as p "
			. " INNER JOIN {shop_param_category_rel} as cp ON cp.element_id=p.id "
			. ($this->diafan->configmodules("cat", "shop", $site_id) && ! empty($cats) ?
				" AND  cp.cat_id IN (".implode(",", $cats).") " : "")
			. " WHERE p.trash='0' GROUP BY p.id ORDER BY p.sort ASC");
		$multiple_param_ids = array();

		foreach ($rows as $row)
		{
			if($row["type"] == 'attachments')
			{
				Custom::inc('modules/attachments/admin/attachments.admin.inc.php');
				$attachment = new Attachments_admin_inc($this->diafan);
				$attachment->save_param($row["id"], $row["config"]);
				continue;
			}

			$not_empty_multilang = false;
			$old = DB::query_fetch_array("SELECT * FROM {shop_param_element} WHERE param_id=%d AND element_id=%d LIMIT 1", $row["id"], $this->diafan->id);
			$id_param = ! empty($old) ? $old["id"] : 0;
			if($old && in_array($row["type"], array('text', 'textarea', 'editor')))
			{
				foreach($this->diafan->_languages->all as $l)
				{
					if($l["id"] != _LANG && $old["value".$l["id"]])
					{
						$not_empty_multilang = true;
					}
				}
			}

			if($row["type"] == 'multiple' && $row["required"])
			{
				$multiple_param_ids[] = $row['id'];
				if(! empty($_POST['param_value'.$row["id"]]))
				{
					continue;
				}
			}

			if($row["type"] == "editor")
			{
				$_POST['param'.$row["id"]] = $this->diafan->save_field_editor('param'.$row["id"]);
			}

			if ( ! empty($_POST['param'.$row["id"]]) || $not_empty_multilang)
			{
				switch($row["type"])
				{
					case "date":
						$_POST['param'.$row["id"]] = $this->diafan->formate_in_date($_POST['param'.$row["id"]]);
						break;

					case "datetime":
						$_POST['param'.$row["id"]] = $this->diafan->formate_in_datetime($_POST['param'.$row["id"]]);
						break;

					case "numtext":
						$_POST['param'.$row["id"]] = str_replace(',', '.', $_POST['param'.$row["id"]]);
						break;
				}

				switch($row["type"])
				{
					case "multiple":
						DB::query("DELETE FROM {shop_param_element} WHERE param_id=%d AND element_id=%d", $row["id"], $this->diafan->id);
						if(is_array($_POST['param'.$row["id"]]) && ! in_array("all", $_POST['param'.$row["id"]]))
						{
							foreach ($_POST['param'.$row["id"]] as $v)
							{
								DB::query("INSERT INTO {shop_param_element} (value".$lang.", param_id, element_id) VALUES ('%d', %d, %d)", $v, $row["id"], $this->diafan->id);
							}
						}
						break;

					default:
						if (empty($id_param))
						{
							DB::query(
								"INSERT INTO {shop_param_element} (".(in_array($row["type"], array("text", "editor", "textarea")) ?
									'[value]' : 'value'.$lang)
								.", param_id, element_id) VALUES ('%s', %d, %d)", $_POST['param'.$row["id"]], $row["id"], $this->diafan->id
							);
						}
						else
						{
							DB::query(
								"UPDATE {shop_param_element} SET ".(in_array($row["type"], array("text", "editor", "textarea")) ?
									'[value]' : 'value'.$lang)
								." = '%s' WHERE param_id=%d AND element_id=%d", $_POST['param'.$row["id"]], $row["id"], $this->diafan->id
							);
						}
				}
			}
			else
			{
				DB::query("DELETE FROM {shop_param_element} WHERE param_id=%d AND element_id=%d", $row["id"], $this->diafan->id);
			}

			$ids[] = $row["id"];
		}

		DB::query("DELETE FROM {shop_param_element} WHERE".($ids ? " param_id NOT IN (".implode(", ", $ids).") AND" : "")." element_id=%d", $this->diafan->id);

		//todo значения параметров, чтобы сравнить при индексации поиска
		$this->diafan->values("param", 'old', true);
		$_POST["param"] = '';

		// отправляет уведомление о поступлении товара
		if (! $this->diafan->is_new && empty($_POST["no_buy"]))
		{
			if($multiple_param_ids && ! empty($_POST["param_price"]))
			{
				for ($k = 0; $k < count($_POST['param_price']); $k ++ )
				{
					if (empty($_POST['param_price'][$k]) || empty($_POST['param_count'][$k]))
						continue;

					$params = array();
					foreach ($multiple_param_ids as $id)
					{
						$params[$id] = ! empty($_POST['param_value'.$id][$k]) ? $_POST['param_value'.$id][$k] : 0;
					}

					$this->send_mail_waitlist($params);
				}
			}
			elseif(! empty($_POST['count']) && ! empty($_POST['price']))
			{
				$this->send_mail_waitlist();
			}
		}

		if (! $this->diafan->is_new)
		{
			// удаляет все цены
			DB::query("DELETE FROM {shop_price_param} WHERE price_id IN (SELECT id FROM {shop_price} WHERE good_id=%d)", $this->diafan->id);
			DB::query("DELETE FROM {shop_price_image_rel} WHERE price_id IN (SELECT id FROM {shop_price} WHERE good_id=%d)", $this->diafan->id);
			DB::query("DELETE FROM {shop_price} WHERE good_id=%d", $this->diafan->id);
		}
		// обновляет категорию, чтобы правильно вычислить скидку
		DB::query("UPDATE {shop} SET cat_id=%d WHERE id=%d", $_POST["cat_id"], $this->diafan->id);

		// несколько вариантов цены и количества
		if ($multiple_param_ids && ! empty($_POST["param_price"]))
		{
			$selected_param_combinations = array();
			for ($k = 0; $k < count($_POST['param_price']); $k ++ )
			{
				$price = true;
				$params = array();
				foreach ($multiple_param_ids as $id)
				{
					$value = ! empty($_POST['param_value'.$id][$k]) ? $_POST['param_value'.$id][$k] : 0;
					$params[$id] = $value;
				}

				//комбинация значений парамертов уже встречалась
				if (in_array(implode('_', $params), $selected_param_combinations))
					continue;

				$selected_param_combinations[] = implode('_', $params);
				if(! empty($_POST['price_image_rel'][$k]))
				{
					$image_id = DB::query_result("SELECT id FROM {images} WHERE id=%d AND trash='0'", $_POST['price_image_rel'][$k]);
				}
				else
				{
					$image_id = 0;
				}

				$price_value = str_replace(array(',', ' '), array('.', ''), $_POST['param_price'][$k]);
				$old_price_value = str_replace(array(',', ' '), array('.', ''), $_POST['param_old_price'][$k]);
				// приводим цены в соответствии с установленными настройками
				$price_value = $this->diafan->_shop->price_format($this->diafan->filter($price_value, 'float'), true);
				$old_price_value = $this->diafan->_shop->price_format($this->diafan->filter($old_price_value, 'float'), true);

				$this->diafan->_shop->price_insert(
						$this->diafan->id,
						$price_value,
						$old_price_value,
						(! empty($_POST['param_count'][$k]) ? $_POST['param_count'][$k] : 0),
						$params,
						(! empty($_POST['param_currency'][$k]) ? $_POST['param_currency'][$k] : 0),
						$_POST['param_import_id'][$k],
						$image_id
					);
			}
		}
		// простые цена и количество
		elseif(! $multiple_param_ids && (! empty($_POST["price"]) || $_POST["price"] === "0"))
		{
			$price_value = str_replace(array(',', ' '), array('.', ''), $_POST['price']);
			$old_price_value = str_replace(array(',', ' '), array('.', ''), $_POST['old_price']);
			// приводим цены в соответствии с установленными настройками
			$price_value = $this->diafan->_shop->price_format($this->diafan->filter($price_value, 'float'), true);
			$old_price_value = $this->diafan->_shop->price_format($this->diafan->filter($old_price_value, 'float'), true);

			$this->diafan->_shop->price_insert(
					$this->diafan->id,
					$_POST['price'],
					$_POST['old_price'],
					! empty($_POST['count']) ? $_POST['count'] : 0,
					array(),
					! empty($_POST['currency']) ? $_POST['currency'] : 0,
					! empty($_POST['price_import_id']) ? $_POST['price_import_id'] : ''
				);
		}
		$this->diafan->_shop->price_calc($this->diafan->id);
	}

	/**
	 * Сохранение поля "Яндекс.Маркет"
	 * @return void
	 */
	public function save_variable_yandex()
	{
		if ( ! $this->diafan->configmodules("yandex"))
		{
			return;
		}

		$this->diafan->set_query("yandex='%s'");
		$this->diafan->set_value(
		'typePrefix='.str_replace("\n", '', $this->diafan->filter($_POST, "string", "yandex_typePrefix"))."\n"
		.'vendor='.str_replace("\n", '', $this->diafan->filter($_POST, "string", "yandex_vendor"))."\n"
		.'model='.str_replace("\n", '', $this->diafan->filter($_POST, "string", "yandex_model"))."\n"
		.'vendorCode='.str_replace("\n", '', $this->diafan->filter($_POST, "string", "yandex_vendorCode"))."\n"
		.'sales_notes='.str_replace("\n", '', $this->diafan->filter($_POST, "string", "yandex_sales_notes"))."\n"
		.'manufacturer_warranty='.str_replace("\n", '', $this->diafan->filter($_POST, "string", "yandex_manufacturer_warranty"))."\n"
		.'country_of_origin='.str_replace("\n", '', $this->diafan->filter($_POST, "string", "yandex_country_of_origin"))."\n"
		.'bid='.$this->diafan->filter($_POST, "string", "yandex_bid"));
	}

	/**
	 * Сохранение поля "Google Merchant"
	 * @return void
	 */
	public function save_variable_google()
	{
		if ( ! $this->diafan->configmodules("google"))
		{
			return;
		}

		$this->diafan->set_query("google='%s'");

		$value = '';
		foreach(array("unit_pricing_measure", "unit_pricing_base_measure", "installment_months", "installment_amount", "google_product_category", "gtin", "mpn", "condition", "adult", "multipack", "energy_efficiency_class", "age_group", "color", "gender", "material", "pattern", "size", "size_type", "size_system", "excluded_destination", "custom_label_0", "custom_label_1", "custom_label_2", "custom_label_3", "custom_label_4", "promotion_id", "shipping", "shipping_label", "shipping_weight", "shipping_length", "shipping_width", "shipping_height", "max_handling_time", "min_handling_time") as $a)
		{
			$v = '';
			switch($a)
			{
                case "unit_pricing_measure":
                case "unit_pricing_base_measure":
                    $v = $this->diafan->filter($_POST, "float", "google_".$a."_1");
                    if($v)
                    {
                        $v .= $this->diafan->filter($_POST, "string", "google_".$a."_2");
                    }
                    break;

				case "installment_months":
				case "multipack":
				case "shipping_weight":
				case "shipping_length":
				case "shipping_width":
				case "shipping_height":
				case "max_handling_time":
				case "min_handling_time":
                    $v = $this->diafan->filter($_POST, "integer", "google_".$a);
                    break;

				case "installment_amount":
                    $v = $this->diafan->filter($_POST, "float", "google_".$a);
                    break;

				case "shipping":
                    $vs = array();
                    if(isset($_POST["google_shipping_4"]))
                    {
                        foreach($_POST["google_shipping_4"] as $i => $val)
                        {
                            if($val !== '')
                            {
                                $vs[] = $this->diafan->filter($_POST["google_shipping_1"][$i], "string")
								.':'.$this->diafan->filter($_POST["google_shipping_2"][$i], "string")
								.':'.$this->diafan->filter($_POST["google_shipping_3"][$i], "string")
								.':'.$this->diafan->filter($val, "float");
                            }
                        }
                    }
                    $v = implode(';', $vs);
					break;

                default:
					$v = trim(str_replace("\n", '', $this->diafan->filter($_POST, "string", "google_".$a)));
					break;
			}
			$value .= ($value ? "\n" : '').$a.'='.$v;
		}
		$this->diafan->set_value($value);
	}

	/**
	 * Сохранение поля "Файлы"
	 * @return void
	 */
	public function save_variable_files()
	{
		if ( ! $this->diafan->configmodules("use_non_material_goods", "shop", $this->diafan->_route->site)
				|| $this->diafan->configmodules("use_count_goods", "shop", $this->diafan->_route->site))
		{
			return;
		}
		$altname = str_replace('/', '_', strtolower(substr($this->diafan->translit($_POST["name"]), 0, 40)));
		if ( ! empty($_POST["delete_attachment"]))
		{
			$rows = DB::query_fetch_all("SELECT id FROM {attachments} WHERE module_name='".$this->diafan->table."' AND element_id='%d'", $this->diafan->id);
			foreach ($rows as $row)
			{
				DB::query("DELETE FROM {attachments} WHERE id='%d'", $row["id"]);
				File::delete_file(USERFILES.'/'.$this->diafan->table.'/files/'.$row["id"]);
			}
		}

		if ( ! empty($file_deleted))
		{
			$this->diafan->set_query("is_file='%s'");
			$this->diafan->set_value("0");
		}

		if (isset($_FILES["attachment"]) && is_array($_FILES["attachment"]) && $_FILES["attachment"]['name'] != '')
		{
			if(empty($_POST["delete_attachment"]))
			{
				$oldid = DB::query_result("SELECT id FROM {attachments} WHERE module_name='%s' AND element_id=%d LIMIT 1", $this->diafan->table, $this->diafan->id);
				if ($oldid)
				{
					File::delete_file(USERFILES.'/'.$this->diafan->table.'/files/'.$oldid);
					DB::query("DELETE FROM {attachments} WHERE id=%d", $oldid);
				}
			}

			Custom::inc("modules/attachments/attachments.inc.php");

			$site_id = $this->diafan->get_site_id();

			$this->diafan->configmodules('attachments', 'shop', $site_id, false, 1);

			$this->diafan->_attachments->upload($_FILES['attachment'], $this->diafan->table, $this->diafan->id, false, array('type' => 'configmodules', 'site_id' => $site_id));

			$this->diafan->set_query("is_file='%s'");
			$this->diafan->set_value("1");
		}
	}

	/**
	 * Сохранение кнопки "Товар временно отсутствует"
	 * @return void
	 */
	public function save_variable_no_buy()
	{
		$this->diafan->set_query("no_buy='%d'");
		$this->diafan->set_value(! empty($_POST["no_buy"]) ? '1' : '0');

		if(! $this->diafan->is_new && empty($_POST["no_buy"]) && $this->diafan->values("no_buy"))
		{
			$this->send_mail_waitlist();
		}
	}

	/**
	 * Отправляет уведомления о поступлении товара
	 *
	 * @param array $params дополнительные характеристики, влияющие на цену
	 * @return void
	 */
	private function send_mail_waitlist($params = array())
	{
		if(empty($_POST["no_buy"]))
		{
			foreach($this->diafan->_languages->all as $l)
			{
				if($l["id"] == _LANG)
				{
					$row["name".$l["id"]] = $_POST["name"];
				}
				else
				{
					$row["name".$l["id"]] = $this->diafan->values("name".$l["id"]);
				}
			}
			$row["site_id"] = $this->diafan->get_site_id();
			$row["no_buy"] = empty($_POST["no_buy"]) ? 0 : 1;
			$this->diafan->_shop->price_send_mail_waitlist($this->diafan->id, $params, $row);
		}
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("shop_price_param", "price_id IN (SELECT id FROM {shop_price} WHERE good_id IN (".implode(",", $del_ids)."))");
		$this->diafan->del_or_trash_where("shop_price_image_rel", "price_id IN (SELECT id FROM {shop_price} WHERE good_id IN (".implode(",", $del_ids)."))");
		$this->diafan->del_or_trash_where("shop_price", "good_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("shop_rel", "element_id IN (".implode(",", $del_ids).") OR rel_element_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("shop_discount_object", "good_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("shop_param_element", "element_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("shop_cart_goods", "good_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("shop_wishlist", "good_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("shop_waitlist", "good_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("shop_counter", "element_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("shop_additional_cost_rel", "element_id IN (".implode(",", $del_ids).")");
	}
}

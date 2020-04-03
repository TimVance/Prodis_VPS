<?php
/**
 * Редактирование категорий магазина
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
 * Shop_admin_category
 */
class Shop_admin_category extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'shop_category';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название категории',
				'help' => 'Используется в ссылках на категорию, заголовках.',
				'multilang' => true,
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'help' => 'Если не отмечена, категорию не увидят посетители сайта.',
				'default' => true,
				'multilang' => true,
			),
			'menu' => array(
				'type' => 'module',
				'name' => 'Создать пункт в меню',
				'help' => 'Если отметить, в [модуле «Меню на сайте»](http://www.diafan.ru/dokument/full-manual/sysmodules/menu/) будет создан пункт со ссылкой на текущую категорию.',
			),
			'images' => array(
				'type' => 'module',
				'name' => 'Изображения',
				'help' => 'Возможность загрузки нескольких изображений. Варианты размера загружаемых изображений определяются в настройках. Параметр выводится, если в настройках модуля отмечена опция «Использовать изображения для категорий».',
			),
			'hr_param' => 'hr',
			'paramcat' => array(
				'type' => 'function',
				'name' => 'К категории прикреплены характеристики товаров',
				'help' => 'Список характеристик, применимых к товарам текущей категории.',
				'no_save' => true,
			),
			'anons' => array(
				'type' => 'editor',
				'name' => 'Краткое описание',
				'help' => 'Краткое описание категории. Если отметить «Добавлять к описанию», на странице элемента анонс выведется вместе с основным описанием. Иначе анонс выведется только в списке, а на отдельной странице будет только описание. Если отметить «Применить типограф», контент будет отформатирован согласно правилам экранной типографики с помощью [веб-сервиса «Типограф»](http://www.artlebedev.ru/tools/typograf/webservice/). Опция «HTML-код» позволяет отключить визуальный редактор для текущего поля. Значение этой настройки будет учитываться и при последующем редактировании.',
				'multilang' => true,
				'height' => 200,
			),
			'text' => array(
				'type' => 'editor',
				'name' => 'Полное описание',
				'help' => 'Если отметить «Применить типограф», контент будет отформатирован согласно правилам экранной типографики с помощью [веб-сервиса «Типограф»](http://www.artlebedev.ru/tools/typograf/webservice/). Опция «HTML-код» позволяет отключить визуальный редактор для текущего поля. Значение этой настройки будет учитываться и при последующем редактировании.',
				'multilang' => true,
			),
			'dynamic' => array(
				'type' => 'function',
				'name' => 'Динамические блоки',
			),			
			'hr_comment' => 'hr',
			'comments' => array(
				'type' => 'module',
				'name' => 'Комментарии',
				'help' => 'Комментарии, которые оставили пользователи к текущей категории. Параметр выводится, если в настройках модуля включен параметр «Показывать комментарии к категориям».',
			),
			'rating' => array(
				'type' => 'module',
				'name' => 'Рейтинг',
				'help' => 'Средний рейтинг, согласно голосованию пользователей сайта. Параметр выводится, если в настройках модуля включен параметр «Подключить рейтинг к категориям».',
			),			
			'search' => array(
				'type' => 'module',
				'name' => 'Индексирование для поиска',
				'help' => 'Категория автоматически индексируется для модуля «Поиск по сайту» при внесении изменений.',
			),
			'map' => array(
				'type' => 'module',
				'name' => 'Индексирование для карты сайта',
				'help' => 'Категория автоматически индексируется для карты сайта sitemap.xml.',
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
				'help' => 'Изменяется после первого сохранения. Показывает, кто из администраторов сайта первый правил текущую страницу.',
			),
			'timeedit' => array(
				'type' => 'text',
				'name' => 'Время последнего изменения',
				'help' => 'Изменяется после сохранения элемента. Отдается в заголовке *Last Modify*.',
			),
			'site_id' => array(
				'type' => 'function',
				'name' => 'Раздел сайта',
				'help' => 'Перенос категории на другую страницу сайта, к которой прикреплен модуль (администратору сайта).',
			),			
			'title_seo' => array(
				'type' => 'title',
				'name' => 'Параметры SEO',
			),
			'title_meta' => array(
				'type' => 'text',
				'name' => 'Заголовок окна в браузере, тег Title',
				'help' => 'Если не заполнен, тег *Title* будет автоматически сформирован как «Название категории – Название страницы – Название сайта», либо согласно шаблонам автоформирования из настроек модуля (SEO-специалисту).',
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
				'help' => 'ЧПУ, т.е. адрес страницы вида: *http://site.ru/psewdossylka/*. Смотрите параметры сайта (SEO-специалисту).',
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
			'map_no_show' => array(
				'type' => 'checkbox',
				'name' => 'Не показывать на карте сайта',
				'help' => 'Скрывает отображение ссылки на категорию в файле sitemap.xml и [модуле «Карта сайта»](http://www.diafan.ru/dokument/full-manual/modules/map/).',
			),
			'show_yandex' => array(
				'type' => 'checkbox',
				'name' => 'Выгружать в Яндекс Маркет',
				'help' => 'Параметр разрешит или запретит выгружать эту категорию. Параметр выводится, если в настройках модуля отмечена опция «Подключить Яндекс Маркет» и параметр «Выгружать категории в Яндекс.Маркет» определен как «только помеченные».'
			),
			'show_google' => array(
				'type' => 'checkbox',
				'name' => 'Выгружать в Google Merchant',
				'help' => 'Параметр разрешит или запретит выгружать эту категорию. Параметр выводится, если в настройках модуля отмечена опция «Подключить Google Merchant» и параметр «Выгружать категории в Google Merchant» определен как «только помеченные».'
			),
			'parent_id' => array(
				'type' => 'select',
				'name' => 'Вложенность: принадлежит',
				'help' => 'Перемещение текущей категории и всех её подкатегорий в принадлежность другой категории (администратору сайта).'
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Редактирование порядка следования категории в списке. Поле доступно для редактирования только для категорий, отображаемых на сайте.',
			),
			'access' => array(
				'type' => 'function',
				'name' => 'Доступ',
				'help' => 'Если отметить опцию «Доступ только», категорию увидят только авторизованные на сайте пользователи, отмеченных типов. Не авторизованные, в том числе поисковые роботы, увидят «404 Страница не найдена» (администратору сайта).',
			),
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
				'help' => 'Возможность подключить для страницы категории шаблон сайта отличный от основного (themes/site.php). Все шаблоны для сайта должны храниться в папке *themes* с расширением *.php* (например, themes/dizain_so_slajdom.php). Подробнее в [разделе «Шаблоны сайта»](http://www.diafan.ru/dokument/full-manual/templates/site/). (веб-мастеру и программисту, не меняйте этот параметр, если не уверены в результате!).',
			),
			'view' => array(
				'type' => 'function',
				'name' => 'Шаблон модуля',
				'help' => 'Шаблон вывода контента модуля на странице списка товаров в категории (веб-мастеру и программисту, не меняйте этот параметр, если не уверены в результате!).',
			),
			'view_rows' => array(
				'type' => 'function',
				'name' => 'Шаблон списка элементов',
				'help' => 'Шаблон вывода контента модуля на странице элементов списка в категории (веб-мастеру и программисту, не меняйте этот параметр, если не уверены в результате!). Значение параметра важно для AJAX.',
			),
			'view_element' => array(
				'type' => 'function',
				'name' => 'Шаблон страницы элемента',
				'help' => 'Шаблон вывода контента модуля на странице отдельного товара, вложенного в текущую категорию (веб-мастеру и программисту, не меняйте этот параметр, если не уверены в результате!).',
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
		),
		'plus' => array(),
		'name' => array(
			'name' => 'Название'
		),
		'actions' => array(
			'add' => true,
			'view' => true,
			'act' => true,
			'trash' => true,
		),
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'menu', // используется в меню
		'category', // часть модуля - категории
		'category_rel', // работают вместе с таблицей {module_category_rel}
		'element_site', // делит элементы по разделам (страницы сайта, к которым прикреплен модуль)
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		if (! $this->diafan->configmodules('show_yandex_category', 'shop'))
		{
			$this->diafan->variable_unset("show_yandex");
		}
		if (! $this->diafan->configmodules('show_google_category', 'shop'))
		{
			$this->diafan->variable_unset("show_google");
		}
	}

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить категорию');
	}

	/**
	 * Выводит список категорий
	 * @return void
	 */
	public function show()
	{
		if(! $this->diafan->configmodules("cat"))
		{
			echo '<div class="error">'.$this->diafan->_('Подключите опцию «Использовать категории» в настройках модуля.').'</div>';
		}
		$this->diafan->list_row();
	}

	/**
	 * Редактирование поля "Характеристики"
	 * @return void
	 */
	public function edit_variable_paramcat()
	{
		if ($this->diafan->is_new)
		{
			return;
		}
		$rows = DB::query_fetch_all("SELECT p.id,p.[name] FROM {shop_param} AS p"
		." INNER JOIN {shop_param_category_rel} AS r ON r.element_id=p.id"
		." WHERE p.trash='0' AND (r.cat_id=%d OR r.cat_id=0) ORDER BY p.sort ASC", $this->diafan->id);

		echo '
		<div class="unit" id="param">
			<div class="infofield">'.$this->diafan->variable_name().'</div>';
			foreach ($rows as $row)
			{
				echo '<a href="'.BASE_PATH_HREF.'shop/param/edit'.$row["id"].'/?filter_cat_id='.$this->diafan->id.'">'.$row["name"].'</a><br>';
			}
			echo '<a class="btn btn_small btn_blue" href="'.BASE_PATH_HREF.'shop/param/cat'.$this->diafan->id.'/addnew1/">
			<i class="fa fa-plus-square"></i>
			'.$this->diafan->_('Добавить характеристику').'
			</a>
		</div>';
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("shop_param_category_rel", "cat_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("shop_discount_object",    "cat_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("shop_brand_category_rel", "cat_id IN (".implode(",", $del_ids).")");
	}
}
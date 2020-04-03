<?php
/**
 * Редактирование производителей
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
 * Shop_admin_brand
 */
class Shop_admin_brand extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'shop_brand';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
				'help' => 'Используется в заголовках, ссылках на производителя.',
				'multilang' => true,
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'help' => 'Если не отмечена, производителя не увидят посетители сайта.',
				'default' => true,
				'multilang' => true,
			),
			'menu' => array(
				'type' => 'module',
				'name' => 'Создать пункт в меню',
				'help' => 'Если отметить, в [модуле «Меню на сайте»](http://www.diafan.ru/dokument/full-manual/sysmodules/menu/) будет создан пункт со ссылкой на текущего производителя.',
			),
			'images' => array(
				'type' => 'module',
				'name' => 'Изображения',
				'help' => 'Возможность загрузки нескольких изображений. Варианты размера загружаемых изображений определяются в настройках. Параметр выводится, если в настройках модуля отмечена опция «Использовать изображения для производителей».',
			),
			'dynamic' => array(
				'type' => 'function',
				'name' => 'Динамические блоки',
			),
			'category' => array(
				'type' => 'function',
				'name' => 'Категории',
				'help' => 'Категории, в которых используется производитель. Чтобы выбрать несколько категорий, удерживайте CTRL.',
			),
			'text' => array(
				'type' => 'editor',
				'name' => 'Полное описание',
				'help' => 'Если отметить «Применить типограф», контент будет отформатирован согласно правилам экранной типографики с помощью [веб-сервиса «Типограф»](http://www.artlebedev.ru/tools/typograf/webservice/). Опция «HTML-код» позволяет отключить визуальный редактор для текущего поля. Значение этой настройки будет учитываться и при последующем редактировании.',
				'multilang' => true,
			),
			'search' => array(
				'type' => 'module',
				'name' => 'Индексирование для поиска',
				'help' => 'Производитель автоматически индексируется для модуля «Поиск по сайту» при внесении изменений.',
			),
			'map' => array(
				'type' => 'module',
				'name' => 'Индексирование для карты сайта',
				'help' => 'Производитель автоматически индексируется для карты сайта sitemap.xml.',
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
			'title_seo' => array(
				'type' => 'title',
				'name' => 'Параметры SEO',
			),
			'title_meta' => array(
				'type' => 'text',
				'name' => 'Заголовок окна в браузере, тег Title',
				'help' => 'Если не заполнен, тег *Title* будет автоматически сформирован как «Название бренда – Название страницы – Название сайта», либо согласно шаблонам автоформирования из настроек модуля (SEO-специалисту).',
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
				'help' => 'Скрывает отображение ссылки на производителя в файле sitemap.xml и [модуле «Карта сайта»](http://www.diafan.ru/dokument/full-manual/modules/map/).',
			),
			'site_id' => array(
				'type' => 'function',
				'name' => 'Раздел сайта',
				'help' => 'Перенос производителя на другую страницу сайта, к которой прикреплен модуль (администратору сайта).',
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Редактирование порядка следования производителя в списке. Поле доступно для редактирования только для производителей, отображаемых на сайте.',
			),
			'import_id' => array(
				'type' => 'text',
				'name' => 'Идентификатор для импорта',
				'help' => 'Можно заполнить для идентификации производеля при импорте (администратору сайта).'
			),
			'title_view' => array(
				'type' => 'title',
				'name' => 'Шаблоны',
			),
			'theme' => array(
				'type' => 'function',
				'name' => 'Шаблон страницы',
				'help' => 'Возможность подключить для страницы производителя шаблон сайта отличный от основного (themes/site.php). Все шаблоны для сайта должны храниться в папке *themes* с расширением *.php* (например, themes/dizain_so_slajdom.php). Подробнее в [разделе «Шаблоны сайта»](http://www.diafan.ru/dokument/full-manual/templates/site/). (веб-мастеру и программисту, не меняйте этот параметр, если не уверены в результате!).',
			),
			'view' => array(
				'type' => 'function',
				'name' => 'Шаблон модуля',
				'help' => 'Шаблон вывода контента модуля на странице списка товаров производителя (веб-мастеру и программисту, не меняйте этот параметр, если не уверены в результате!).',
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
		'name' => array(
			'name' => 'Название'
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
		'cat_id' => array(
			'type' => 'select',
		),
		'site_id' => array(
			'type' => 'select',
			'name' => 'Искать по разделу',
		),
		'name' => array(
			'type' => 'text',
			'name' => 'Искать по названию',
		),
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'element_site', // делит элементы по разделам (страницы сайта, к которым прикреплен модуль)
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		if (! $this->diafan->configmodules("cat", "shop", $this->diafan->_route->site))
		{
			$this->diafan->variable_unset("cat_id");
		}
		else
		{
			$cats = DB::query_fetch_all(
				"SELECT id, [name], parent_id, site_id FROM {shop_category} WHERE trash='0'"
				.($this->diafan->_route->site ? " AND site_id='".$this->diafan->_route->site."'" : "")
				." ORDER BY sort ASC LIMIT 1000"
			);
			if(count($cats))
			{
				$this->diafan->not_empty_categories = true;
			}
			if(count($cats) == 1000)
			{
				$this->diafan->categories = array();
			}
			else
			{
				$this->diafan->categories = $cats;
			}
		}
		$sites = DB::query_fetch_all("SELECT id, [name], parent_id FROM {site} WHERE trash='0' AND module_name='%s' ORDER BY sort ASC", $this->diafan->_admin->module);
		if(count($sites))
		{
			$this->diafan->not_empty_site = true;
		}
		foreach($sites as $site)
		{
			$this->cache["parent_site"][$site["id"]] = $site["name"];
		}
		if(count($sites) == 1)
		{
			if (DB::query_result("SELECT id FROM {%s} WHERE trash='0' AND site_id<>%d LIMIT 1", $this->diafan->table, $sites[0]["id"]))
			{
				$sites[] = 0;
			}
			else
			{
				$this->diafan->_route->site = $sites[0]["id"];
			}
		}
		$this->diafan->sites = $sites;
	}

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить производителя');
	}

	/**
	 * Выводит список категорий
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Выводит категории характеристики в списке
	 * 
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_parent($row, $var)
	{
		if ($this->diafan->config("element_site") && $this->diafan->not_empty_site)
		{
			if(count($this->diafan->sites) > 1)
			{
				$text = '<div class="categories">';
				if(! empty($this->cache["parent_site"][$row["site_id"]]))
				{
					$l = '#';
					if (! $this->diafan->_users->roles('init', 'site'))
					{
						$l = BASE_PATH_HREF.'site/edit'.$row["site_id"].'/';
					}
					$text .= '<a href="'.$l.'">'.$this->cache["parent_site"][$row["site_id"]].'</a>';
				}
				$text .= '</div>';
			}
		}
		if(! isset($this->cache["prepare"]["parent_cats"]))
		{
			$this->cache["prepare"]["parent_cats"] = DB::query_fetch_key_array(
				"SELECT s.[name], c.element_id, s.id FROM {shop_brand_category_rel} as c"
				." INNER JOIN {shop_category} as s ON s.id=c.cat_id"
				." WHERE element_id IN (%s)",
				implode(",", $this->diafan->rows_id),
				"element_id"
			);
		}
		$cats = array();
		if(! empty($this->cache["prepare"]["parent_cats"][$row["id"]]))
		{
			foreach($this->cache["prepare"]["parent_cats"][$row["id"]] as $cat)
			{
				$cats[] = '<a href="'.BASE_PATH_HREF.'shop/category/edit'.$cat["id"].'/">'.$cat["name"].'</a>';
			}
		}
		if ( ! $cats)
		{
			$cats[] = $this->diafan->_('Общие');
		}
		return '<div class="categories">'.implode(', ', $cats).'</div>';
	}

	/**
	 * Поиск по полю "Категория"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function save_filter_variable_cat_id($row)
	{
		$cat_id = $this->diafan->_route->cat;
		if (! $cat_id)
		{
			return;
		}
		$this->diafan->join .= " INNER JOIN {shop_brand_category_rel} AS c ON e.id=c.element_id AND (c.cat_id='".$cat_id."' OR c.cat_id=0)";
		return $cat_id;
	}

	/**
	 * Редактирование поля "Категория"
	 * 
	 * @return void
	 */
	public function edit_variable_category()
	{
		if(! $this->diafan->configmodules("cat", "shop"))
		{
			return;
		}
		$shop_pages = DB::query_fetch_key_value("SELECT id, [name] FROM {site} WHERE trash='0' AND module_name='shop'", "id", "name");

		$rows = DB::query_fetch_all("SELECT id, [name], parent_id, site_id FROM {shop_category} WHERE trash='0' ORDER BY sort ASC LIMIT 1000");
		foreach ($rows as $row)
		{
			$cats[$row["site_id"]][$row["parent_id"]][] = $row;
		}

		$values = array();
		if ( ! $this->diafan->is_new)
		{
			$values = DB::query_fetch_value("SELECT cat_id FROM {shop_brand_category_rel} WHERE element_id=%d AND cat_id>0", $this->diafan->id, "cat_id");
		}
		elseif($this->diafan->_route->cat)
		{
			$values[] = $this->diafan->_route->cat;
		}
		if(count($rows) == 1000)
		{
			foreach($values as $value)
			{
				echo '<input type="hidden" name="cat_ids[]" value="'.$value.'">';
			}
			return;
		}
		
		echo '
		<div class="unit">
			<div class="infofield">'.$this->diafan->_('Категория').$this->diafan->help().'</div>';

		echo ' <select name="cat_ids[]" multiple="multiple" size="11">
		<option value="all"'.(empty($values) ? ' selected' : '').'>'.$this->diafan->_('Все').'</option>';
		foreach ($shop_pages as $site_id => $name)
		{
			if(! empty($cats[$site_id]))
			{
				if(count($shop_pages) > 1)
				{
					echo '<optgroup label="'.$name.'">';
				}
				echo $this->diafan->get_options($cats[$site_id], $cats[$site_id][0], $values);
				if(count($shop_pages) > 1)
				{
					echo '</optgroup>';
				}
			}
		}
		echo '</select>';

		echo '</div>';
	}

	/**
	 * Сохранение поля "Категория"
	 * 
	 * @return void
	 */
	public function save_variable_category()
	{
		DB::query("DELETE FROM {shop_brand_category_rel} WHERE element_id=%d", $this->diafan->id);
		if(! empty($_POST["cat_ids"]) && in_array("all", $_POST["cat_ids"]))
		{
			$_POST["cat_ids"] = array();
		}

		if(! empty($_POST["cat_ids"]))
		{
			foreach ($_POST["cat_ids"] as $cat_id)
			{
				DB::query("INSERT INTO {shop_brand_category_rel} (element_id, cat_id) VALUES(%d, %d)", $this->diafan->id, $cat_id);
			}
		}
		else
		{
			DB::query("INSERT INTO {shop_brand_category_rel} (element_id) VALUES(%d)", $this->diafan->id);
		}
	}
}
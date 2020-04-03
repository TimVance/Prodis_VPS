<?php
/**
 * Редактирование объявлений
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
 * Ab_admin
 */
class Ab_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'ab';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'created' => array(
				'type' => 'datetime',
				'name' => 'Дата создания',
				'help' => 'Вводится в формате дд.мм.гггг чч:мм. Если указать дату позже текущей даты, то объявление начнет отображаться на сайте, начиная с указанной даты.',
			),
			'user_id' => array(
				'type' => 'function',
				'name' => 'Автор объявления',
				'help' => 'Пользователь, создавший объявление в форме на сайте.',
			),
			'name' => array(
				'type' => 'text',
				'name' => 'Заголовок',
				'help' => 'Используется в ссылках на объявление, заголовках.',
				'multilang' => true,
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'help' => 'Если не отмечена, объявление не будет отображаться на сайте.',
				'default' => true,
				'multilang' => true,
			),
			'cat_id' => array(
				'type' => 'function',
				'name' => 'Категория',
				'help' => 'Категория, к которой относится объявление. Список категорий редактируется во вкладке выше. Возможно выбрать дополнительные категории, в которых объявление также будет выводится. Чтобы выбрать несколько категорий, удерживайте CTRL. Параметр выводится, если в настройках модуля отмечена опция «Использовать категории».',
			),
			'images' => array(
				'type' => 'module',
				'name' => 'Изображения',
				'help' => 'Изображения будут загружены автоматически после выбора. После загрузки изображения будут обработаны автоматически, согласно настройкам модуля. Параметр выводится, если в настройках модуля отмечена опция «Использовать изображения».',
			),
			'hr1' => array(
				'type' => 'title',
				'name' => 'Характеристики',
			),
			'param' => array(
				'type' => 'function',
				'name' => 'Характеристики',
				'help' => 'Группа полей, определенных в части «Характеристики».',
				'multilang' => true,
			),
			'anons' => array(
				'type' => 'editor',
				'name' => 'Анонс',
				'help' => 'Краткое описание объявления. Выводится в списках объявлений и в блоках. Если отметить «Добавлять к описанию», на странице объявления анонс выведется вместе с основным описанием. Иначе анонс выведется только в списке, а на отдельной странице будет только описание. Если отметить «Применить типограф», контент будет отформатирован согласно правилам экранной типографики с помощью [веб-сервиса «Типограф»](http://www.artlebedev.ru/tools/typograf/webservice/). Опция «HTML-код» позволяет отключить визуальный редактор для текущего поля. Значение этой настройки будет учитываться и при последующем редактировании.',
				'multilang' => true,
				'height' => 200,
			),
			'text' => array(
				'type' => 'editor',
				'name' => 'Описание',
				'help' => 'Полное описание для страницы объявления. Если отметить «Применить типограф», контент будет отформатирован согласно правилам экранной типографики с помощью [веб-сервиса «Типограф»](http://www.artlebedev.ru/tools/typograf/webservice/). Опция «HTML-код» позволяет отключить визуальный редактор для текущего поля. Значение этой настройки будет учитываться и при последующем редактировании.',
				'multilang' => true,
			),
			'dynamic' => array(
				'type' => 'function',
				'name' => 'Динамические блоки',
			),
			'geomap' => array(
				'type' => 'module',
				'name' => 'Точка на карте',
				'help' => 'Возможность установить или отредактировать точку на геокарте. Параметр выводится, если в настройках модуля включен параметр «Подключить геокарту».',
			),
			'hr2' => 'hr',
			'rel_elements' => array(
				'type' => 'function',
				'name' => 'Похожие объявления',
				'help' => 'Похожие объявления выводятся шаблонным тегом show_block_rel. По умолчанию связи между объявлениями являются односторонними, это можно изменить, отметив опцию «В блоке похожих объявлений связь двусторонняя» в настройках модуля.',
			),
			'tags' => array(
				'type' => 'module',
				'name' => 'Теги',
				'help' => 'Добавление тегов к объявлению. Можно добавить либо новый тег, либо открыть и выбрать из уже существующих тегов. Параметр выводится, если в настройках модуля включен параметр «Подключить теги».',
			),
			'hr3' => 'hr',
			'counter_view' => array(
				'type' => 'function',
				'name' => 'Счетчик просмотров',
				'help' => 'Количество просмотров на сайте текущего объявления. Статистика ведется и параметр выводится, если в настройках модуля отмечена опция «Подключить счетчик просмотров».',
				'no_save' => true,
			),
			'rating' => array(
				'type' => 'module',
				'name' => 'Рейтинг',
				'help' => 'Средний рейтинг, согласно голосованию пользователей сайта. Параметр выводится, если в настройках модуля включен параметр «Подключить рейтинг к объявлениям».',
			),
			'comments' => array(
				'type' => 'module',
				'name' => 'Комментарии',
				'help' => 'Комментарии, которые оставили пользователи к текущему объявлению. Параметр выводится, если в настройках модуля включен параметр «Показывать комментарии к объявлениям».',
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
				'help' => 'Перенос объявления на другую страницу сайта, к которой прикреплен модуль. Параметр выводится, если в настройках модуля отключена опция «Использовать категории», если опция подключена, то раздел сайта задается такой же, как у основной категории.',
			),
			'title_seo' => array(
				'type' => 'title',
				'name' => 'Параметры SEO',
			),
			'title_meta' => array(
				'type' => 'text',
				'name' => 'Заголовок окна в браузере, тег Title',
				'help' => 'Если не заполнен, тег *Title* будет автоматически сформирован как «Название объявления – Название страницы – Название сайта»',
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
				'help' => 'ЧПУ (человеко-понятные урл url), адрес страницы вида: *http://site.ru/psewdossylka/*. Смотрите параметры сайта.',
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
			'date_period' => array(
				'type' => 'date',
				'name' => 'Период показа',
				'help' => 'Если заполнить, текущее объявление будет опубликована на сайте в указанный период. В иное время пользователи сайта объявление не будут видеть, получая ошибку 404 «Страница не найдена» (администратору сайта).'
			),
			'access' => array(
				'type' => 'function',
				'name' => 'Доступ',
				'help' => 'Если отметить опцию «Доступ только», объявление увидят только авторизованные на сайте пользователи, отмеченных типов. Не авторизованные, в том числе поисковые роботы, увидят «404 Страница не найдена» (администратору сайта).',
			),
			'hr_period' => 'hr',
			'prior' => array(
				'type' => 'checkbox',
				'name' => 'Важно (всегда сверху)',
				'help' => 'Если отмечена, объявление выведется в начале списка, независимо от сортировки по дате.  Если важных объявлений несколько, между собой они будут сортироваться по дате.',
			),
			'map_no_show' => array(
				'type' => 'checkbox',
				'name' => 'Не показывать на карте сайта',
				'help' => 'Скрывает отображение ссылки на объвление в файле sitemap.xml и [модуле «Карта сайта»](http://www.diafan.ru/dokument/full-manual/modules/map/).',
			),
			'title_view' => array(
				'type' => 'title',
				'name' => 'Оформление',
			),
			'theme' => array(
				'type' => 'function',
				'name' => 'Шаблон страницы',
				'help' => 'Возможность подключить для страницы объявления шаблон сайта отличный от основного (themes/site.php). Все шаблоны для сайта должны храниться в папке *themes* с расширением *.php* (например, themes/dizain_so_slajdom.php). Подробнее в [разделе «Шаблоны сайта»](http://www.diafan.ru/dokument/full-manual/templates/site/). (веб-мастеру и программисту, не меняйте этот параметр, если не уверены в результате!).',
			),
			'view' => array(
				'type' => 'function',
				'name' => 'Шаблон модуля',
				'help' => 'Шаблон вывода контента модуля на странице отдельного объявления (веб-мастеру и программисту, не меняйте этот параметр, если не уверены в результате!).',
			),
			'hr_info' => 'hr',
			'readed' => array(
				'type' => 'function',
				'name' => 'Помечает как прочитанное',
				'hide' => true,
			),
			'search' => array(
				'type' => 'module',
				'name' => 'Индексирование для поиска',
				'help' => 'Объявление автоматически индексируется для модуля «Поиск по сайту» при внесении изменений.',
			),
			'map' => array(
				'type' => 'module',
				'name' => 'Индексирование для карты сайта',
				'help' => 'Объявление автоматически индексируется для карты сайта sitemap.xml.',
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'name' => array(
			'name' => 'Название и категория'
		),
		'adapt' => array(
			'class_th' => 'item__th_adapt',
		),
		'separator' => array(
			'class_th' => 'item__th_seporator',
		),
		'created' => array(
			'name' => 'Дата и время',
			'type' => 'datetime',
			'sql' => true,
			'no_important' => true,
		),
		'anons' => array(
			'name' => 'Анонс',
			'sql' => true,
			'type' => 'text',
			'class' => 'text',
			'no_important' => true,
		),
		'text' => array(
			'name' => 'Описание',
			'sql' => true,
			'type' => 'text',
			'class' => 'text',
			'no_important' => true,
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
		'no_cat' => array(
			'type' => 'checkbox',
			'name' => 'Нет категории',
		),
		'no_img' => array(
			'type' => 'checkbox',
			'name' => 'Нет картинки',
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
		'user_id' => array(
			'type' => 'none',
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
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		if(! $this->diafan->configmodules("cat", "ab", $this->diafan->_route->site))
		{
			$this->diafan->config("element", false);
			$this->diafan->config("element_multiple", false);
		}
	}

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		if ($this->diafan->config('element') && ! $this->diafan->not_empty_categories)
		{
			echo '<div class="error">'.sprintf($this->diafan->_('В %sнастройках%s модуля подключены категории, чтобы начать добавлять объявление создайте хотя бы одну %sкатегорию%s.'),'<a href="'.BASE_PATH_HREF.'ab/config/">', '</a>', '<a href="'.BASE_PATH_HREF.'ab/category/'.($this->diafan->_route->site ? 'site'.$this->diafan->_route->site.'/' : '').'">', '</a>').'</div>';
		}
		else
		{
			$this->diafan->addnew_init('Добавить объявление');
		}
	}

	/**
	 * Выводит список объявлений
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
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
			$rows_el = DB::query_fetch_all("SELECT value".$this->diafan->_languages->site." as rv, [value], param_id FROM {ab_param_element} WHERE element_id=%d", $this->diafan->id);
			foreach ($rows_el as $row_el)
			{
				$values[$row_el["param_id"]][] = $row_el["value"];
				$rvalues[$row_el["param_id"]][] = $row_el["rv"];
			}
		}

		// значения списков
		$options = DB::query_fetch_key_array("SELECT [name], id, param_id FROM {ab_param_select} ORDER BY sort ASC", "param_id");

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

		// выбирает все характеристики (при смене раздела/категории просто показываем или скрываем характеристики)
		$params = DB::query_fetch_all("SELECT p.id, p.[name], p.type, p.required, p.[measure_unit], p.[text], p.config FROM {ab_param} AS p INNER JOIN {ab_param_category_rel} AS r ON r.element_id=p.id AND r.cat_id IN (%s) AND p.site_id IN (%s) WHERE p.trash='0' ORDER BY p.sort ASC", implode(',', $cat_ids), implode(',', $site_ids));

		foreach ($params as &$row)
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
		}
		$class = 'ab_param';
		foreach ($params as &$row)
		{
			$help = $this->diafan->help($row["text"]);
			$attr = '';
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

				case 'email':
					$value = (! empty($rvalues[$row["id"]]) ? $rvalues[$row["id"]][0] : '');
					$this->diafan->show_table_tr_email("param".$row["id"], $row["name"], $value, $help, false, $attr, $class);
					break;

				case 'editor':
					$value = (! empty($values[$row["id"]]) ? $values[$row["id"]][0] : '');
  					$this->diafan->show_table_tr_editor("param".$row["id"], $row["name"], $value, $help, $attr, $class);
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
					$this->diafan->show_table_tr_multiple("param".$row["id"], $row["name"], $value, $help, false, $row["options"], $attr, $class);
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
			<a href="'.BASE_PATH_HREF.'ab/param/addnew1/" target="_blank" class="btn btn_small btn_blue">
				<i class="fa fa-plus-square"></i>
				'.$this->diafan->_('Добавить характеристику').'
			</a>
		</div>';
	}

	/**
	 * Редактирование поля "Помечает как прочитанное"
	 *
	 * @return void
	 */
	public function edit_variable_readed()
	{
		if(! $this->diafan->value && ! $this->diafan->is_new)
		{
			DB::query("UPDATE {ab} SET readed='1' WHERE id=%d", $this->diafan->id);
		}
		echo '<input name="readed" value="1" type="hidden">';
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
		$rows = DB::query_fetch_all("SELECT p.id, p.type, p.config FROM {ab_param} as p "
			. " INNER JOIN {ab_param_category_rel} as cp ON cp.element_id=p.id "
			. ($this->diafan->configmodules("cat", "ab", $site_id) && ! empty($cats) ?
				" AND  cp.cat_id IN (".implode(",", $cats).") " : "")
			. " WHERE p.trash='0' GROUP BY p.id ORDER BY p.sort ASC");
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
			$old = DB::query_fetch_array("SELECT * FROM {ab_param_element} WHERE param_id=%d AND element_id=%d LIMIT 1", $row["id"], $this->diafan->id);
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
						if(is_array($_POST['param'.$row["id"]]))
						{
							DB::query("DELETE FROM {ab_param_element} WHERE param_id=%d AND element_id=%d", $row["id"], $this->diafan->id);
							foreach ($_POST['param'.$row["id"]] as $v)
							{
								DB::query("INSERT INTO {ab_param_element} (value".$lang.", param_id, element_id) VALUES ('%d', %d, %d)", $v, $row["id"], $this->diafan->id);
							}
						}
						break;

					default:
						if (empty($id_param))
						{
							DB::query(
								"INSERT INTO {ab_param_element} (".(in_array($row["type"], array("text", "editor", "textarea")) ?
									'[value]' : 'value'.$lang)
								.", param_id, element_id) VALUES ('%s', %d, %d)", $_POST['param'.$row["id"]], $row["id"], $this->diafan->id
							);
						}
						else
						{
							DB::query(
								"UPDATE {ab_param_element} SET ".(in_array($row["type"], array("text", "editor", "textarea")) ?
									'[value]' : 'value'.$lang)
								." = '%s' WHERE param_id=%d AND element_id=%d", $_POST['param'.$row["id"]], $row["id"], $this->diafan->id
							);
						}
				}
			}
			else
			{
				DB::query("DELETE FROM {ab_param_element} WHERE param_id=%d AND element_id=%d", $row["id"], $this->diafan->id);
			}

			$ids[] = $row["id"];
		}

		DB::query("DELETE FROM {ab_param_element} WHERE".($ids ? " param_id NOT IN (".implode(", ", $ids).") AND" : "")." element_id=%d", $this->diafan->id);

		//todo значения параметров, чтобы сравнить при индексации поиска
		$this->diafan->values("param", 'old', true);
		$_POST["param"] = '';
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("ab_counter", "element_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("ab_rel", "element_id IN (".implode(",", $del_ids).") OR rel_element_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("ab_param_element", "element_id IN (".implode(",", $del_ids).")");
	}
}

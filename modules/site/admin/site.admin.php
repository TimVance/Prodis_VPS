<?php
/**
 * Редактирование страниц сайта
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
 * Site_admin
 */
class Site_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'site';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
				'help' => 'Название страницы, отображается в ссылках на страницу, используется для автоматической генерации пункта меню, заголовка страницы и её адреса (ЧПУ).',
				'multilang' => true
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'help' => 'Отображение страницы на сайте. Если не отмечена, страница на сайте не будет доступна пользователям и выдаст «404 Страница не найдена». Страница всегда доступна для просмотра администратору.',
				'multilang' => true
			),
			'menu' => array(
				'type' => 'module',
				'name' => 'Создать пункт в меню',
				'help' => 'Если отметить, в [модуле «Меню на сайте»](http://www.diafan.ru/dokument/full-manual/sysmodules/menu/) будет создан пункт со ссылкой на текущую страницу.'
			),
			'parent_id' => array(
				'type' => 'select',
				'name' => 'Корневая страница',
				'help' => 'Перемещение текущей страницы и всех её подстраниц в принадлежность другой страницы (администратору сайта).'
			),
			'images' => array(
				'type' => 'module',
				'name' => 'Изображения',
				'help' => 'Изображения будут загружены автоматически после выбора. После загрузки изображения будут обработаны автоматически, согласно настройкам модуля. Параметр выводится, если в настройках модуля отмечена опция «Использовать изображения».',
			),
			'text' => array(
				'type' => 'editor',
				'name' => 'Контент страницы',
				'help' => 'Основное содержимое страницы. Если отметить «Применить типограф», контент будет отформатирован согласно правилам экранной типографики с помощью [веб-сервиса «Типограф»](http://www.artlebedev.ru/tools/typograf/webservice/). Опция «HTML-код» позволяет отключить визуальный редактор для текущего поля. Значение этой настройки будет учитываться и при последующем редактировании.',
				'multilang' => true
			),
			'dynamic' => array(
				'type' => 'function',
				'name' => 'Динамические блоки',
			),
			'module_name' => array(
				'type' => 'select',
				'name' => 'Прикрепить модуль',
				'help' => 'Прикрепление модуля к текущей странице. Содержимое модуля выведется после контента страницы (администратору сайта).'
			),
			'tags' => array(
				'type' => 'module',
				'name' => 'Теги',
				'help' => 'Добавление тегов к странице. Можно добавить либо новый тег, либо открыть и выбрать из уже существующих тегов. Параметр выводится, если в настройках модуля включен параметр «Подключить теги».',
			),
			'comments' => array(
				'type' => 'module',
				'name' => 'Комментарии',
				'help' => 'Комментарии, которые оставили пользователи к текущей странице. Параметр выводится, если в настройках модуля включен параметр «Показывать комментарии к новостям».',
			),

		),
		'other_rows' => array (
			'number' => array(
				'type' => 'function',
				'name' => 'Номер',
				'help' => 'Номер страницы в БД (веб-мастеру и программисту).',
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
			'title_seo' => array(
				'type' => 'title',
				'name' => 'Параметры SEO',
			),
			'title_meta' => array(
				'type' => 'text',
				'name' => 'Заголовок окна в браузере, тег Title',
				'help' => 'Если не заполнен, тег *Title* будет автоматически сформирован как «Название страницы – Название сайта», либо согласно шаблонам автоформирования из настроек модуля (SEO-специалисту).',
				'multilang' => true
			),
			'keywords' => array(
				'type' => 'textarea',
				'name' => 'Ключевые слова, тег Keywords',
				'help' => 'Если не заполнен, тег *Keywords* будет автоматически сформирован согласно шаблонам автоформирования из настроек модуля (SEO-специалисту).',
				'multilang' => true
			),
			'descr' => array(
				'type' => 'textarea',
				'name' => 'Описание страницы, тэг Description',
				'help' => 'Если не заполнен, тег *Description* будет автоматически сформирован согласно шаблонам автоформирования из настроек модуля (SEO-специалисту).',
				'multilang' => true
			),
			'canonical' => array(
				'type' => 'text',
				'name' => 'Канонический тег',
				'help' => 'URL канонической страницы вида: *http://site.ru/psewdossylka/*, на которую переносится "ссылочный вес" данной страницы. Используется для страниц с похожим или дублирующимся контентом (SEO-специалисту).',
				'multilang' => true,
			),
			'title_no_show' => array(
				'type' => 'checkbox',
				'name' => 'Не копировать автоматически название в заголовок Н1',
				'help' => 'Если отмечено, заголовок *Н1* перед текстом страницы автоматически выводиться не будет. Тогда его можно вписать в визуальный редактор в свободном виде (SEO-специалисту).'
			),
			'rewrite' => array(
				'type' => 'function',
				'name' => 'Псевдоссылка',
				'help' => 'ЧПУ, т.е. адрес страницы вида: *http://site.ru/psewdossylka/*. Смотрите параметры сайта (SEO-специалисту).'
			),
			'noindex' => array(
				'type' => 'checkbox',
				'name' => 'Не индексировать',
				'help' => 'Запрет индексации текущей страницы, если отметить, у страницы выведется тег: `<meta name="robots" content="noindex">` (SEO-специалисту).'
			),
			'changefreq' => array(
				'type' => 'function',
				'name' => 'Changefreq',
				'help' => 'Вероятная частота изменения этой страницы. Это значение используется для генерирования файла sitemap.xml. Подробнее читайте в описании [XML-формата файла Sitemap](http://www.sitemaps.org/ru/protocol.html) (SEO-специалисту).',
			),
			'priority' => array(
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
				'help' => 'Если выставить, текущая страница будет опубликована на сайте в указанный период. В иное время пользователи сайта страницу не будут видеть, получая ошибку 404 «Страница не найдена» (администратору сайта).'
			),
			'access' => array(
				'type' => 'function',
				'name' => 'Доступ',
				'help' => 'Если отметить опцию «Доступ только», страницу увидят только авторизованные на сайте пользователи, отмеченных типов. Не авторизованные, в том числе поисковые роботы, увидят «404 Страница не найдена» (администратору сайта).',
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Изменить положение текущей страницы среди других страниц (администратору сайта).'
			),
			'map_no_show' => array(
				'type' => 'checkbox',
				'name' => 'Не показывать на карте сайта',
				'help' => 'Если отмечено, страница не будет показываться на карте сайта (администратору сайта).'
			),
			'search_no_show' => array(
				'type' => 'checkbox',
				'name' => 'Не показывать в результатах поиска по сайту',
				'help' => 'Страница не участвует в поисковой выдаче внутреннего поиска по сайту.'
			),
			'title_view' => array(
				'type' => 'title',
				'name' => 'Шаблоны',
			),
			'js' => array(
				'type' => 'textarea',
				'name' => 'JavaScript',
				'help' => 'Поле для ввода JavaScript на текущей странице. Например, для кода Яндекс.Карт. ВНИМАНИЕ, не вставляйте JS-код в визуальный редактор, он может его обрезать. Вставляйте его в это поле. (Веб-мастеру и программисту).'
			),
			'theme' => array(
				'type' => 'text',
				'name' => 'Дизайн страницы',
				'help' => 'Возможность подключить для страницы шаблон сайта отличный от основного (themes/site.php). Все шаблоны для сайта должны храниться в папке *themes* с расширением *.php* (например, themes/dizain_so_slajdom.php). Подробнее в [разделе «Шаблоны сайта»](http://www.diafan.ru/dokument/full-manual/templates/site/). (веб-мастеру и программисту, не меняйте этот параметр, если не уверены в результате!).',
			),
			'search' => array(
				'type' => 'module',
				'name' => 'Индексирование для поиска',
				'help' => 'Страница автоматически индексируется для модуля «Поиск по сайту» при внесении изменений.',
			),
			'map' => array(
				'type' => 'module',
				'name' => 'Индексирование для карты сайта',
				'help' => 'Страница автоматически индексируется для карты сайта sitemap.xml.',
			),
		)
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
		'module_name' => array(
			'sql' => true,
			'type' => 'none',
		),
		'actions' => array(
			'add' => true,
			'view' => true,
			'act' => true,
			'trash' => true,
		),
	);

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить страницу сайта');
	}

	/**
	 * Выводит список страниц сайта
	 * @return void
	 */
	public function show()
	{
		//весь список
		$this->diafan->where = " AND e.id<>1";

		echo '<form action="" method="POST">
		<input type="hidden" name="check_hash_user" value="'.$this->diafan->_users->get_hash().'">
		<input type="hidden" name="action" value="">
		<input type="hidden" name="id" value="">
		<input type="hidden" name="module" value="">
		<div class="head-box">';
			echo $this->diafan->show_add();
		echo '</div>';

		if ($this->diafan->_users->admin_nastr)
		{
			$this->diafan->_paginator->nastr = $this->diafan->_users->admin_nastr;
		}
		else
		{
			$this->diafan->_paginator->nastr = $this->diafan->nastr;
		}

		$paginator = '<div class="paginator"><div class="paginator_empty"></div>
		<div class="paginator__unit">
			'.$this->diafan->_('Показывать на странице').':
			<input name="nastr" type="text" value="'.$this->diafan->_paginator->nastr.'">
			<button type="button" class="btn btn_blue btn_small change_nastr">'.$this->diafan->_('ОК').'</button>
		</div></div>';
		echo $paginator;

		ob_start();
		echo '<ul class="list_pages">';

		$this->diafan->get_heading();

		//Главная страница
		if ($row = DB::query_fetch_array("SELECT id, [name], module_name FROM {".$this->diafan->table."} WHERE id=1 LIMIT 1"))
		{
			echo '
			<li class="item active">
				<div class="item__in">
					<div class="item__th"></div>
					<div class="item__th"></div>
					<div class="item__toggle"><i class="fa fa-plus-circle"></i></div>
					'.$this->diafan->list_variable_name($row, $this->diafan->variables_list["name"]).'

					<div class="item__unit">
						<a href="'.URL.'parent0/addnew1/" class="item__ui add">
							<i class="fa fa-plus-square"></i>
							<span class="add__txt">'.$this->diafan->_('Добавить подстраницу').'</span>
						</a>
						<a href="'.BASE_PATH.'" class="item__ui view" title="'.$this->diafan->_('Посмотреть на сайте').'">
							<i class="fa fa-laptop"></i>
						</a>
					</div>
				</div>';
		}

		$this->diafan->list_row(0, false);
		$text = ob_get_contents();
		ob_end_clean();

		$this->group_action_panel();
		echo $text;

		echo '</li></ul>';
		$this->group_action_panel();
		echo $paginator;

		echo '</form>';
	}

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		//нельзя редактировать поля Принадлежит, Псевдоссылка, Сторонняя ссылка, Активность для Главной страницы
		if ($this->diafan->id == 1)
		{
			$this->diafan->variable_unset("act");
			$this->diafan->variable_unset("parent_id");
			$this->diafan->variable_unset("rewrite");
			$this->diafan->variable_unset("hr3");
			$this->diafan->variable_unset("site_ids");
			$this->diafan->variable_unset("map_no_show");
			$this->diafan->variable_unset("date_start");
			$this->diafan->variable_unset("date_finish");
		}
	}

	/**
	 * Выводит подключенный модуль
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_parent($row, $var)
	{
		$text = '<div class="item__module">';
		if ($row["module_name"])
		{
			if($row["module_name"] && ! empty($this->diafan->title_modules[$row["module_name"]]))
			{
				if ($row["module_name"] && Custom::exists('modules/'.$row["module_name"].'/admin/'.$row["module_name"].'.admin.php'))
				{
					$link = BASE_PATH_HREF.$row["module_name"].'/site'.$row["id"].'/';
				}
				else
				{
					$link = '#';
				}
				$text .= '<span>'.$this->diafan->_('Подключен модуль').':</span><a href="'.$link.'"><i class="fa fa-'.$row["module_name"].'"></i> '.$this->diafan->title_modules[$row["module_name"]].'</a>';
			}
		}
		$text .= '</div>';
		return $text;
	}

	/**
	 * Редактирование поля "Прикрепленный модуль"
	 * @return void
	 */
	public function edit_variable_module_name()
	{
		echo '<div class="unit" id="module_name">
		<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
				<select name="'.$this->diafan->key.'">
					<option value="">'.$this->diafan->_('нет').'</option>';
					$ms = array();
					foreach ($this->diafan->all_modules as $row)
					{
						if($row["site_page"] && ! in_array($row["name"], $ms))
						{
							$ms[] = $row["name"];
							echo '<option value="'.$row["name"].'"'.( $this->diafan->value == $row["name"] ? ' selected' : '' ).'>'.$this->diafan->_($row["title"]).'</option>';
						}
					}

		echo '</select>
		</div>';
	}

	/**
	 * Сохранение поля "Прикрепленный модуль"
	 * @return void
	 */
	public function save_variable_module_name()
	{
		$this->diafan->set_query("module_name='%h'");
		$this->diafan->set_value($_POST["module_name"]);
		if($_POST["module_name"])
		{
			$this->diafan->_cache->delete("", $_POST["module_name"]);
		}
	}

	/**
	 * Сохранение поля "JavaScript"
	 * @return void
	 */
	public function save_variable_js()
	{
		$this->diafan->set_query("js='%s'");
		$this->diafan->set_value($_POST["js"]);
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("site_blocks_site_rel", "site_id IN (".implode(",", $del_ids).")");
		$module_name = DB::query_result("SELECT module_name FROM {site} WHERE id IN (".implode(",", $del_ids).")");
		if($module_name)
		{
			$this->diafan->_cache->delete("", $module_name);
		}
	}
}

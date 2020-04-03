<?php
/**
 * Редактирование фотографий
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
 * Photo_admin
 */
class Photo_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'photo';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название фотографии',
				'help' => 'Используется в ссылках на фотографию, заголовках.',
				'multilang' => true,
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'help' => 'Если не отмечена, фотография не будет отображаться на сайте.',
				'default' => true,
				'multilang' => true,
			),
			'images' => array(
				'type' => 'module',
				'name' => 'Фотография',
				'help' => 'Фотография будет загружена автоматически после выбора. После загрузки фотография будет обработана автоматически, согласно настройкам модуля.',
				'count' => 1,
			),
			'cat_id' => array(
				'type' => 'function',
				'name' => 'Альбом',
				'help' => 'Альбом, к которому относится фотография. Список альбомов редактируется во вкладке выше. Возможно выбрать дополнительные альбомы, в которых фотография также будет выводится. Чтобы выбрать несколько альбомов, удерживайте CTRL. Параметр выводится, если в настройках модуля отмечена опция «Использовать альбомы».',
			),
			'anons' => array(
				'type' => 'editor',
				'name' => 'Анонс',
				'help' => 'Краткое описание фотографии. Если отметить «Добавлять к описанию», на странице элемента анонс выведется вместе с основным описанием. Иначе анонс выведется только в списке, а на отдельной странице будет только описание. Если отметить «Применить типограф», контент будет отформатирован согласно правилам экранной типографики с помощью [веб-сервиса «Типограф»](http://www.artlebedev.ru/tools/typograf/webservice/). Опция «HTML-код» позволяет отключить визуальный редактор для текущего поля. Значение этой настройки будет учитываться и при последующем редактировании.',
				'multilang' => true,
				'height' => 200,
			),
			'text' => array(
				'type' => 'editor',
				'name' => 'Описание',
				'help' => 'Полное описание для страницы фотографии. Если отметить «Применить типограф», контент будет отформатирован согласно правилам экранной типографики с помощью [веб-сервиса «Типограф»](http://www.artlebedev.ru/tools/typograf/webservice/). Опция «HTML-код» позволяет отключить визуальный редактор для текущего поля. Значение этой настройки будет учитываться и при последующем редактировании.',
				'multilang' => true,
			),
			'dynamic' => array(
				'type' => 'function',
				'name' => 'Динамические блоки',
			),
			'hr1' => 'hr',
			'rel_elements' => array(
				'type' => 'function',
				'name' => 'Похожие фотографии',
				'help' => 'Выбор и добавление к текущей фотографии связей с другими фотографиями. Похожие фотографии выводятся шаблонным тегом show_block_rel. По умолчанию связи между фотографиями являются односторонними, это можно изменить, отметив опцию «В блоке похожих фотографий связь двусторонняя» в настройках модуля.',
			),
			'tags' => array(
				'type' => 'module',
				'name' => 'Теги',
				'help' => 'Добавление тегов к фотографии. Можно добавить либо новый тег, либо открыть и выбрать из уже существующих тегов. Параметр выводится, если в настройках модуля включен параметр «Подключить теги».',
			),
			'counter_view' => array(
				'type' => 'function',
				'name' => 'Счетчик просмотров',
				'help' => 'Количество просмотров на сайте текущей фотографии. Статистика ведется и параметр выводится, если в настройках модуля отмечена опция «Подключить счетчик просмотров».',
				'no_save' => true,
			),
			'comments' => array(
				'type' => 'module',
				'name' => 'Комментарии',
				'help' => 'Комментарии, которые оставили пользователи к текущей фотографии. Параметр выводится, если в настройках модуля включен параметр «Показывать комментарии к фотографиям».',
			),
			'rating' => array(
				'type' => 'module',
				'name' => 'Рейтинг',
				'help' => 'Средний рейтинг, согласно голосованию пользователей сайта. Параметр выводится, если в настройках модуля включен параметр «Подключить рейтинг к фотографиям».',
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
				'help' => 'Перенос фотографии на другую страницу сайта, к которой прикреплен модуль. Параметр выводится, если в настройках модуля отключена опция «Использовать альбомы», если опция подключена, то раздел сайта задается такой же, как у основного альбома.',
			),
			'title_seo' => array(
				'type' => 'title',
				'name' => 'Параметры SEO',
			),
			'title_meta' => array(
				'type' => 'text',
				'name' => 'Заголовок окна в браузере, тег Title',
				'help' => 'Если не заполнен, тег *Title* будет автоматически сформирован как «Название фотографии – Название страницы – Название сайта», либо согласно шаблонам автоформирования из настроек модуля (SEO-специалисту).',
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
			'map_no_show' => array(
				'type' => 'checkbox',
				'name' => 'Не показывать на карте сайта',
				'help' => 'Скрывает отображение ссылки на фотографию в файле sitemap.xml и [модуле «Карта сайта»](http://www.diafan.ru/dokument/full-manual/modules/map/).',
			),
			'access' => array(
				'type' => 'function',
				'name' => 'Доступ к текущей фотографии',
				'help' => 'Если отметить опцию «Доступ только», фотографию увидят только авторизованные на сайте пользователи, отмеченных типов. Не авторизованные, в том числе поисковые роботы, увидят «404 Страница не найдена» (администратору сайта).',
			),
			'date_period' => array(
				'type' => 'date',
				'name' => 'Период показа',
				'help' => 'Если заполнить, текущая фотография будет опубликована на сайте в указанный период. В иное время пользователи сайта фотографию не будут видеть, получая ошибку 404 «Страница не найдена» (администратору сайта).'
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Изменить положение текущей фотографии среди других фотографий. Поле доступно для редактирования только для фотографий, отображаемых на сайте (администратору сайта).'
			),

			'title_view' => array(
				'type' => 'title',
				'name' => 'Оформление',
			),
			'theme' => array(
				'type' => 'function',
				'name' => 'Шаблон страницы',
				'help' => 'Возможность подключить для страницы фотографии шаблон сайта отличный от основного (themes/site.php). Все шаблоны для сайта должны храниться в папке *themes* с расширением *.php* (например, themes/dizain_so_slajdom.php). Подробнее в [разделе «Шаблоны сайта»](http://www.diafan.ru/dokument/full-manual/templates/site/). (веб-мастеру и программисту, не меняйте этот параметр, если не уверены в результате!).',
			),
			'view' => array(
				'type' => 'function',
				'name' => 'Шаблон модуля',
				'help' => 'Шаблон вывода контента модуля на странице отдельной фотографии (веб-мастеру и программисту, не меняйте этот параметр, если не уверены в результате!).',
			),
			'search' => array(
				'type' => 'module',
				'name' => 'Индексирование для поиска',
				'help' => 'Фотография автоматически индексируется для модуля «Поиск по сайту» при внесении изменений.',
			),
			'map' => array(
				'type' => 'module',
				'name' => 'Индексирование для карты сайта',
				'help' => 'Фотография автоматически индексируется для карты сайта sitemap.xml.',
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
			'desc' => true,
			'sql' => true,
			'fast_edit' => true,
		),
		'image' => array(
			'name' => 'Фото',
			'class_th' => 'item__th_image',
		),
		'name' => array(
			'name' => 'Название и альбом',
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
			'name' => 'Нет альбома',
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
			'name' => 'Искать по альбому',
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
		'element', // используются группы
		'element_multiple', // модуль может быть прикреплен к нескольким группам
		'multiupload', // мультизагрузка изображений (подключение JS-библиотек)
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		if(! $this->diafan->configmodules("cat", "photo", $this->diafan->_route->site))
		{
			$this->diafan->config("element", false);
			$this->diafan->config("element_multiple", false);
		}
		if($this->diafan->is_action("edit"))
		{
			$this->diafan->config('multiupload', false);
		}
		if(! $this->diafan->configmodules("page_show", "photo", $this->diafan->_route->site))
		{
			$this->diafan->variable_unset("view");
			$this->diafan->variable_list("actions", "view", false);
		}
	}

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		if (! extension_loaded('gd') && ! extension_loaded('gd2'))
		{
			$this->diafan->_route->error = 7;
		}
		if ($this->diafan->config('element') && !$this->diafan->not_empty_categories)
		{
			echo '<div class="error">'.sprintf($this->diafan->_('В %sнастройках%s включено использование альбомов. Чтобы загрузить фотографию, создайте хотя бы один %sальбом%s.'), '<a href="'.BASE_PATH_HREF.'photo/config/">', '</a>', '<a href="'.BASE_PATH_HREF.'photo/category/'.( $this->diafan->_route->site ? 'site'.$this->diafan->_route->site.'/' : '' ).'">', '</a>').'</div>';
		}
		else
		{
			echo '
			<div class="add_new">
				<a href="'.$this->diafan->get_admin_url('parent');
				if ($this->diafan->config('element_site') && strpos(URL, 'site') === false && ! empty($this->diafan->_route->site ))
				{
					echo 'site'.$this->diafan->_route->site.'/';
				}
				echo 'addnew1/'.$this->diafan->get_nav.'" class="btn">
				<i class="fa fa-picture-o"></i> '.$this->diafan->_('Добавить фотографию').'</a>
				 <a href="'.URL.'" class="upload_files btn btn_blue" style="margin-left: 7px;"><i class="fa fa-files-o"></i> '.$this->diafan->_('Несколько фотографий').'</a>
			</div>

			<div id="upload_area_multi" class="hide">
				<p><input id="fileupload" type="file" class="file" name="images[]" data-url="'.URL.'" multiple></p>
			</div>
			<div>'.$this->diafan->_('Максимальный размер загружаемого изображения %s.', ini_get('upload_max_filesize')).'</div>';
		}
	}

	/**
	 * Выводит список фотографий
	 * @return void
	 */
	public function show()
	{
		$this->upload();
		$this->diafan->list_row();
	}

	/**
	 * Формирует изображение в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_image($row, $var)
	{
		if(! isset($this->cache["prepare"]["image"]))
		{
			$this->cache["prepare"]["image"] = DB::query_fetch_key("SELECT id, name, folder_num, element_id FROM {images}"
				." WHERE module_name='%s' AND element_type='%s' AND element_id IN (%s)"
				." AND trash='0' ORDER BY param_id DESC, sort DESC",
				$this->diafan->_admin->module,
				$this->diafan->element_type(),
				implode(",", $this->diafan->rows_id),
				"element_id"
			);
			$this->cache["folder_image"] = 'small';
			$vs = unserialize($this->diafan->configmodules("images_variations_element", "photo"));
			foreach($vs as $v)
			{
				if($v["name"] == 'medium')
				{
					$this->cache["folder_image"] = 'photo/'.DB::query_result("SELECT folder FROM {images_variations} WHERE id=%d", $v["id"]);
				}
			}
		}

		$html = '<div class="image'.($var["class"] ? ' '.$var["class"] : '').' ipad">';
		if (! empty($this->cache["prepare"]["image"][$row["id"]]))
		{
			$r = $this->cache["prepare"]["image"][$row["id"]];
			if(file_exists(ABSOLUTE_PATH.USERFILES.'/'.$this->cache["folder_image"].'/'.($r["folder_num"] ? $r["folder_num"].'/' : '').$r["name"]))
			{
				$html .= '<a href="'.$this->diafan->get_base_link($row).'"><img src="http'.(IS_HTTPS ? "s" : '').'://'.BASE_URL.'/'.USERFILES.'/'.$this->cache["folder_image"].'/'.($r["folder_num"] ? $r["folder_num"].'/' : '').$r["name"].'" border="0" alt=""></a>';
			}
		}
		$html .= '</div>';

		return $html;
	}

	/**
	 * Добавляет элемент в базу данных
	 *
	 * @return boolean
	 */
	public function upload()
	{
		if (empty($_POST["save_upload"]))
		{
			return;
		}
		if (! isset( $_FILES["images"] ) || ! is_array($_FILES["images"]))
		{
			return;
		}

		header('Content-Type: text/html; charset=utf-8');

		// Проверяет права на добавление
		if (! $this->diafan->_users->roles('edit', 'photo'))
		{
			$this->diafan->redirect(URL);
			return false;
		}
		$names = array ();
		$values = array ();
		if ($this->diafan->config('element'))
		{
			if(! $this->diafan->_route->cat)
			{
				$this->diafan->_route->cat = DB::query_result("SELECT id FROM {photo_category} WHERE [act]='1' AND trash='0'".($this->diafan->_route->site ? " AND site_id=".$this->diafan->_route->site : '')." LIMIT 1");
			}
			$names[] = 'cat_id';
			$values[] = "'".$this->diafan->_route->cat."'";
		}
		$names[] = 'site_id';
		if ($this->diafan->config('element') && $this->diafan->_route->cat)
		{
			$this->diafan->_route->site = DB::query_result("SELECT site_id FROM {photo_category} WHERE id=%d LIMIT 1", $this->diafan->_route->cat);
		}
		elseif (! $this->diafan->_route->site)
		{
			$this->diafan->_route->site = DB::query_result("SELECT id FROM {site} WHERE module_name='photo' LIMIT 1");
		}
		$values[] = "'".$this->diafan->_route->site."'";
		foreach ($this->diafan->_languages->all as $lang)
		{
			if($this->diafan->configmodules("multiupload_act", 'photo', $this->diafan->_route->site))
			{
				$names[] = 'act'.$lang["id"];
				$values[] = "'1'";
			}
		}
		$result['hash'] = $this->diafan->_users->get_hash();

		foreach ($_FILES["images"]['name'] as $i => $name)
		{
			$new_names = $names;
			$new_values = $values;
			foreach ($this->diafan->_languages->all as $lang)
			{
				$new_names[] = 'name'.$lang["id"];
				$new_values[] = "'".str_replace(array("'", '"', '=', '<', '>'), array("\\'", ''), $name)."'";
			}
			DB::query("INSERT INTO {photo} (".implode(',', $new_names).") VALUES (".implode(',', $new_values).")");
			$save = DB::query_result("SELECT MAX(id) FROM {photo} WHERE [name]='%s'", $name);
			DB::query("UPDATE {photo} SET sort=id WHERE id=%d", $save);

			if ($this->diafan->config('element') && $this->diafan->_route->cat)
			{
				DB::query("INSERT INTO {photo_category_rel} (element_id, cat_id) VALUES (%d, %d)", $save, $this->diafan->_route->cat);
			}
			$new_name = strtolower($this->diafan->translit($name));
			$extension = substr(strrchr($new_name, '.'), 1);
			$new_name = substr($new_name, 0, -( strlen($extension) + 1 ));

			if (strlen($new_name) + strlen($extension) > 49)
			{
				$new_name = substr($new_name, 0, 49 - strlen($extension));
			}

			try
			{
				$this->diafan->_images->upload($save, 'photo', 'element', $this->diafan->_route->site, $_FILES["images"]['tmp_name'][$i], $new_name);
			}
			catch(Exception $e)
			{
				Dev::$exception_field = 'images';
				Dev::$exception_result = $result;
				throw new Exception($e->getMessage());
			}
		}

		$result["success"] = true;
		$result["redirect"] = URL;
		$result["file"] = $name;

		Custom::inc('plugins/json.php');
		echo to_json($result);
		exit;
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("photo_rel", "element_id IN (".implode(",", $del_ids).") OR rel_element_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("photo_counter", "element_id IN (".implode(",", $del_ids).")");
	}
}

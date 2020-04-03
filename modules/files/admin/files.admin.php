<?php
/**
 * Редактирование файлов в файловом архиве
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
 * Files_admin
 */
class Files_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'files';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'files' => array(
				'type' => 'function',
				'name' => 'Файлы',
				'help' => 'Загружаемые файлы. Доступные типы файлов %attachment_extensions изменяются в настройках модуля (параметр «Доступные типы файлов»). Можно указать ссылку для закачки файла из удаленного источника или ссылку на файл для скачивания.',
			),
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
				'help' => 'Используется в ссылках на файл, заголовках.',
				'multilang' => true,
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'help' => 'Если не отмечена, файл не будет отображаться на сайте.',
				'default' => true,
				'multilang' => true,
			),
			'images' => array(
				'type' => 'module',
				'name' => 'Изображения',
				'help' => 'Изображения будут загружены автоматически после выбора. После загрузки изображения будут обработаны автоматически, согласно настройкам модуля. Параметр выводится, если в настройках модуля отмечена опция «Использовать изображения».',
			),
			'cat_id' => array(
				'type' => 'function',
				'name' => 'Категория',
				'help' => 'Категория, к которой относится файл. Список категорий редактируется во вкладке выше. Возможно выбрать дополнительные категории, в которых файл также будет выводится. Чтобы выбрать несколько категорий, удерживайте CTRL. Параметр выводится, если в настройках модуля отмечена опция «Использовать категории».',
			),
			'anons' => array(
				'type' => 'editor',
				'name' => 'Анонс',
				'help' => 'Краткое описание файла. Выводится в списке файлов и в блоках. Если отметить «Добавлять к описанию», на странице файла анонс выведется вместе с основным описанием. Иначе анонс выведется только в списке, а на отдельной странице будет только описание. Если отметить «Применить типограф», контент будет отформатирован согласно правилам экранной типографики с помощью [веб-сервиса «Типограф»](http://www.artlebedev.ru/tools/typograf/webservice/). Опция «HTML-код» позволяет отключить визуальный редактор для текущего поля. Значение этой настройки будет учитываться и при последующем редактировании.',
				'multilang' => true,
				'height' => 200,
			),
			'text' => array(
				'type' => 'editor',
				'name' => 'Описание',
				'help' => 'Полное описание для страницы файла. Если отметить «Применить типограф», контент будет отформатирован согласно правилам экранной типографики с помощью [веб-сервиса «Типограф»](http://www.artlebedev.ru/tools/typograf/webservice/). Опция «HTML-код» позволяет отключить визуальный редактор для текущего поля. Значение этой настройки будет учитываться и при последующем редактировании.',
				'multilang' => true,
			),
			'dynamic' => array(
				'type' => 'function',
				'name' => 'Динамические блоки',
			),			
			'tags' => array(
				'type' => 'module',
				'name' => 'Теги',
				'help' => 'Добавление тегов к файлу. Можно добавить либо новый тег, либо открыть и выбрать из уже существующих тегов. Параметр выводится, если в настройках модуля включен параметр «Подключить теги».',
			),
			'rel_elements' => array(
				'type' => 'function',
				'name' => 'Похожие файлы',
				'help' => 'Выбор и добавление к текущему файлу связей с другими файлами. Похожие файлы выводятся шаблонным тегом show_block_rel. По умолчанию связи между файлами являются односторонними, это можно изменить, отметив опцию «В блоке похожих файлов связь двусторонняя» в настройках модуля.',
			),
			'hr4' => 'hr',
			'counter_view' => array(
				'type' => 'function',
				'name' => 'Счетчик просмотров',
				'help' => 'Количество просмотров на сайте текущей файла. Статистика ведется и параметр выводится, если в настройках модуля отмечена опция «Подключить счетчик просмотров».',
				'no_save' => true,
			),
			'comments' => array(
				'type' => 'module',
				'name' => 'Комментарии',
				'help' => 'Комментарии, которые оставили пользователи к текущему файлу. Параметр выводится, если в настройках модуля включен параметр «Показывать комментарии к файлам».',
			),
			'rating' => array(
				'type' => 'module',
				'name' => 'Рейтинг',
				'help' => 'Средний рейтинг, согласно голосованию пользователей сайта. Параметр выводится, если в настройках модуля включен параметр «Подключить рейтинг к файлам».',
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
				'help' => 'Перенос файла на другую страницу сайта, к которой прикреплен модуль. Параметр выводится, если в настройках модуля отключена опция «Использовать категории», если опция подключена, то раздел сайта задается такой же, как у основной категории.',
			),
			'title_seo' => array(
				'type' => 'title',
				'name' => 'Параметры SEO',
			),
			'title_meta' => array(
				'type' => 'text',
				'name' => 'Заголовок окна в браузере, тег Title',
				'help' => 'Если не заполнен, тег *Title* будет автоматически сформирован как «Название файла – Название страницы – Название сайта», либо согласно шаблонам автоформирования из настроек модуля (SEO-специалисту).',
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
			'access' => array(
				'type' => 'function',
				'name' => 'Доступ',
				'help' => 'Если отметить опцию «Доступ только», файл увидят только авторизованные на сайте пользователи, отмеченных типов. Не авторизованные, в том числе поисковые роботы, увидят «404 Страница не найдена» (администратору сайта).',
			),
			'date_period' => array(
				'type' => 'date',
				'name' => 'Период показа',
				'help' => 'Если заполнить, текущий файл будет опубликована на сайте в указанный период. В иное время пользователи сайта файл не будут видеть, получая ошибку 404 «Страница не найдена» (администратору сайта).'
			),
			'hr_period' => 'hr',
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Изменить положение текущего файла среди других файлов. Поле доступно для редактирования только для файлов, отображаемых на сайте (администратору сайта).'
			),
			'map_no_show' => array(
				'type' => 'checkbox',
				'name' => 'Не показывать на карте сайта',
				'help' => 'Скрывает отображение ссылки на файл в файле sitemap.xml и [модуле «Карта сайта»](http://www.diafan.ru/dokument/full-manual/modules/map/).',
			),
			'title_view' => array(
				'type' => 'title',
				'name' => 'Оформление',
			),
			'theme' => array(
				'type' => 'function',
				'name' => 'Шаблон страницы',
				'help' => 'Возможность подключить для страницы файла шаблон сайта отличный от основного (themes/site.php). Все шаблоны для сайта должны храниться в папке *themes* с расширением *.php* (например, themes/dizain_so_slajdom.php). Подробнее в [разделе «Шаблоны сайта»](http://www.diafan.ru/dokument/full-manual/templates/site/). (веб-мастеру и программисту, не меняйте этот параметр, если не уверены в результате!).',
			),
			'view' => array(
				'type' => 'function',
				'name' => 'Шаблон модуля',
				'help' => 'Шаблон вывода контента модуля на странице отдельного файла (веб-мастеру и программисту, не меняйте этот параметр, если не уверены в результате!).',
			),
			'hr_info' => 'hr',
			'search' => array(
				'type' => 'module',
				'name' => 'Индексирование для поиска',
				'help' => 'Файл автоматически индексируется для модуля «Поиск по сайту» при внесении изменений.',
			),
			'map' => array(
				'type' => 'module',
				'name' => 'Индексирование для карты сайта',
				'help' => 'Файл автоматически индексируется для карты сайта sitemap.xml.',
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
		'adapt' => array(
			'class_th' => 'item__th_adapt',
		),
		'separator' => array(
			'class_th' => 'item__th_seporator',
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
		if(! $this->diafan->configmodules("cat", "files", $this->diafan->_route->site))
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
		if ($this->diafan->config('element') && !$this->diafan->not_empty_categories)
		{
			echo '<div class="error">'.sprintf($this->diafan->_('В %sнастройках%s модуля подключены категории, чтобы начать добавлять файл создайте хотя бы одну %sкатегорию%s.'),'<a href="'.BASE_PATH_HREF.'files/config/">', '</a>', '<a href="'.BASE_PATH_HREF.'files/category/'.( $this->diafan->_route->site ? 'site'.$this->diafan->_route->site.'/' : '' ).'">', '</a>').'</div>';
		}
		else
		{
			$this->diafan->addnew_init('Добавить файл');
		}
	}

	/**
	 * Выводит список файлов
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Выводит подсказки к полю (заменяет основную функцию)
	 * @return string
	 */
	public function help($key = '')
	{
		$text = parent::__call('help', array());
		if($key == 'files' || $this->diafan->key == 'files')
		{
			$text = str_replace('%attachment_extensions', $this->diafan->configmodules('attachment_extensions'), $text);
		}
		return $text;
	}

	/**
	 * Редактирование поля "Файлы"
	 * @return void
	 */
	public function edit_variable_files()
	{
		$file_type = 1;
		echo '
		<div class="unit">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>';
		if(!$this->diafan->is_new)
		{
			$rows = DB::query_fetch_all("SELECT id, link FROM {files_links} WHERE element_id=%d", $this->diafan->id);
			foreach ($rows as $row)
			{
				echo '<div class="attachment">
				<input type="hidden" name="hide_link_delete[]" value="'.$row["id"].'">';
				echo '<a href="'.$row["link"].'">'.$row["link"].'</a>';
				echo '<a href="javascript:void(0)" class="attachment_delete" confirm="'.$this->diafan->_('Вы действительно хотите удалить ссылку?').'"><img src="'.BASE_PATH.'adm/img/delete.png" width="13" height="13" alt="'.$this->diafan->_('Удалить').'"></a></div>';
			}
			$rows = DB::query_fetch_all("SELECT id, name FROM {attachments} WHERE module_name='".$this->diafan->table."' AND element_id=%d", $this->diafan->id);
			foreach ($rows as $row)
			{
				echo '<div class="attachment">
				<input type="hidden" name="hide_attachment_delete[]" value="'.$row["id"].'">';
				echo '<a href="'.BASE_PATH.'attachments/get/'.$row["id"]."/".$row["name"].'">'.$row["name"].'</a>';
				echo ' <a href="javascript:void(0)" class="attachment_delete delete" confirm="'.$this->diafan->_('Вы действительно хотите удалить файл?').'"><i class="fa fa-times-circle" title="'.$this->diafan->_('Удалить').'"></i></a></div>';
			}
		}
		echo '
		<input type="radio" name="file_type" value="1"'.($file_type == 1 ? ' checked' : '').' id="file1_radio"> <label for="file1_radio">'.$this->diafan->_('Загрузить файл').'</label>
		<input type="radio" name="file_type" value="2" id="file2_radio"> <label for="file2_radio">'.$this->diafan->_('Загрузить файл по ссылке').'</label>
		<input type="radio" name="file_type" value="3"'.($file_type == 2 ? ' checked' : '').' id="file3_radio"> <label for="file3_radio">'.$this->diafan->_('Указать ссылку на файл').'</label>

			<div class="file_type1"><input type="file" name="attachment" class="file"></div>
			<div class="file_type2 hide"><input type="text" name="attachment_link_upload" value="" placeholder="http://"></div>
			<div class="file_type3 hide"><input type="text" name="attachment_link" value="" placeholder="http://"></div>
		</div>';
	}

	/**
	 * Сохранение поля "Файлы"
	 * @return void
	 */
	public function save_variable_files()
	{
		$altname = str_replace('/', '_', strtolower(substr($this->diafan->translit($_POST["name"]), 0, 40)));
		if(! empty($_POST["attachment_delete"]))
		{
			$rows = DB::query_fetch_all("SELECT id FROM {attachments} WHERE module_name='".$this->diafan->table."' AND element_id=%d", $this->diafan->id);
			foreach ($rows as $row)
			{
				if (in_array($row["id"], $_POST["attachment_delete"]))
				{
					DB::query("DELETE FROM {attachments} WHERE id=%d", $row["id"]);
					File::delete_file(USERFILES.'/'.$this->diafan->table.'/files/'.$row["id"]);
				}
			}
		}
		if(! empty($_POST["link_delete"]))
		{
			$rows = DB::query_fetch_all("SELECT id FROM {files_links} WHERE element_id=%d", $this->diafan->id);
			foreach ($rows as $row)
			{
				if (in_array($row["id"], $_POST["link_delete"]))
				{
					DB::query("DELETE FROM {files_links} WHERE id=%d", $row["id"]);
				}
			}
		}

		if (empty( $_POST["file_type"] ))
		{
			return;
		}
		if ($_POST["file_type"] == 1 && isset($_FILES["attachment"]) && is_array($_FILES["attachment"]) && $_FILES["attachment"]['name'] != '')
		{
			Custom::inc("modules/attachments/attachments.inc.php");
			$site_id = $this->diafan->get_site_id();
			$config = array(
				"attachment_extensions" => $this->diafan->configmodules("attachment_extensions", 'files', $site_id),
				"recognize_image" => $this->diafan->configmodules("recognize_image", 'files', $site_id),
				"attachments_access_admin" => $this->diafan->configmodules("attachments_access_admin", 'files', $site_id),
				"attach_big_width" => $this->diafan->configmodules("attach_big_width", 'files', $site_id),
				"attach_big_height" => $this->diafan->configmodules("attach_big_height", 'files', $site_id),
				"attach_big_quality" => $this->diafan->configmodules("attach_big_quality", 'files', $site_id),
				"attach_medium_width" => $this->diafan->configmodules("attach_medium_width", 'files', $site_id),
				"attach_medium_height" => $this->diafan->configmodules("attach_medium_height", 'files', $site_id),
				"attach_medium_quality" => $this->diafan->configmodules("attach_medium_quality", 'files', $site_id),
			);

			$this->diafan->_attachments->upload($_FILES['attachment'], $this->diafan->table, $this->diafan->id, false, $config);
		}
		if ($_POST["file_type"] == 2 && ! empty( $_POST['attachment_link_upload'] ))
		{
			$extension = strtolower(substr(strrchr($_POST['attachment_link_upload'], '.'), 1));

			$name = $altname.'.'.$extension;			

			if ($this->diafan->configmodules('attachment_extensions', 'files', $_POST["site_id"]) && !in_array($extension, explode(',', str_replace(' ', '', strtolower($this->diafan->configmodules('attachment_extensions', 'files', $_POST["site_id"]))))))
			{
				if(extension_loaded('fileinfo'))
				{
					$file_info = new finfo(FILEINFO_MIME);
					$mime_type = $file_info->buffer(file_get_contents($_POST['attachment_link_upload']));
					$mime_type = explode(';' , $mime_type);					

					$mimes = array(
						'ez' => 'application/andrew-inset',
						'atom' => 'application/atom+xml',
						'cgi' => 'application/cgi',
						'hqx' => 'application/mac-binhex40',
						'cpt' => 'application/mac-compactpro',
						'mathml' => 'application/mathml+xml',
						'doc' => 'application/msword',
						'bin' => 'application/octet-stream',
						'dms' => 'application/octet-stream',
						'lha' => 'application/octet-stream',
						'lzh' => 'application/octet-stream',
						'exe' => 'application/octet-stream',
						'class' => 'application/octet-stream',
						'so' => 'application/octet-stream',
						'dll' => 'application/octet-stream',
						'dmg' => 'application/octet-stream',
						'iso' => 'application/octet-stream',
						'oda' => 'application/oda',
						'ogg' => 'application/ogg',
						'pdf' => 'application/pdf',
						'pl' => 'application/perl',
						'plx' => 'application/perl',
						'ppl' => 'application/perl',
						'perl' => 'application/perl',
						'pm' => 'application/perl',
						'ai' => 'application/postscript',
						'eps' => 'application/postscript',
						'ps' => 'application/postscript',
						'rdf' => 'application/rdf+xml',
						'rb' => 'application/ruby',
						'smi' => 'application/smil',
						'smil' => 'application/smil',
						'gram' => 'application/srgs',
						'grxml' => 'application/srgs+xml',
						'mif' => 'application/vnd.mif',
						'xul' => 'application/vnd.mozilla.xul+xml',
						'xls' => 'application/vnd.ms-excel',
						'ppt' => 'application/vnd.ms-powerpoint',
						'rm' => 'application/vnd.rn-realmedia',
						'wbxml' => 'application/vnd.wap.wbxml',
						'wmlc' => 'application/vnd.wap.wmlc',
						'wmlsc' => 'application/vnd.wap.wmlscriptc',
						'vxml' => 'application/voicexml+xml',
						'bcpio' => 'application/x-bcpio',
						'vcd' => 'application/x-cdlink',
						'pgn' => 'application/x-chess-pgn',
						'Z' => 'application/x-compress',
						'cpio' => 'application/x-cpio',
						'csh' => 'application/x-csh',
						'dcr' => 'application/x-director',
						'dir' => 'application/x-director',
						'dxr' => 'application/x-director',
						'dvi' => 'application/x-dvi',
						'spl' => 'application/x-futuresplash',
						'gtar' => 'application/x-gtar',
						'gz' => 'application/x-gzip',
						'tgz' => 'application/x-gzip',
						'hdf' => 'application/x-hdf',
						'php' => 'application/x-httpd-php',
						'php3' => 'application/x-httpd-php',
						'php4' => 'application/x-httpd-php',
						'php5' => 'application/x-httpd-php',
						'php6' => 'application/x-httpd-php',
						'phps' => 'application/x-httpd-php-source',
						'img' => 'application/x_img',
						'js' => 'application/x-javascript',
						'skp' => 'application/x-koan',
						'skd' => 'application/x-koan',
						'skt' => 'application/x-koan',
						'skm' => 'application/x-koan',
						'latex' => 'application/x-latex',
						'nc' => 'application/x-netcdf',
						'cdf' => 'application/x-netcdf',
						'crl' => 'application/x-pkcs7-crl',
						'sh' => 'application/x-sh',
						'shar' => 'application/x-shar',
						'swf' => 'application/x-shockwave-flash',
						'sit' => 'application/x-stuffit',
						'sv4cpio' => 'application/x-sv4cpio',
						'sv4crc' => 'application/x-sv4crc',
						'tgz' => 'application/x-tar',
						'tar' => 'application/x-tar',
						'tcl' => 'application/x-tcl',
						'tex' => 'application/x-tex',
						'texinfo' => 'application/x-texinfo',
						'texi' => 'application/x-texinfo',
						't' => 'application/x-troff',
						'tr' => 'application/x-troff',
						'roff' => 'application/x-troff',
						'man' => 'application/x-troff-man',
						'me' => 'application/x-troff-me',
						'ms' => 'application/x-troff-ms',
						'ustar' => 'application/x-ustar',
						'src' => 'application/x-wais-source',
						'crt' => 'application/x-x509-ca-cert',
						'xhtml' => 'application/xhtml+xml',
						'xht' => 'application/xhtml+xml',
						'xml' => 'application/xml',
						'xsl' => 'application/xml',
						'dtd' => 'application/xml-dtd',
						'xslt' => 'application/xslt+xml',
						'zip' => 'application/zip',
						'au' => 'audio/basic',
						'snd' => 'audio/basic',
						'mid' => 'audio/midi',
						'midi' => 'audio/midi',
						'kar' => 'audio/midi',
						'a-latm' => 'audio/mp4',
						'm4p' => 'audio/mp4',
						'm4a' => 'audio/mp4',
						'mp4' => 'audio/mp4',
						'mpga' => 'audio/mpeg',
						'mp2' => 'audio/mpeg',
						'mp3' => 'audio/mpeg',
						'aif' => 'audio/x-aiff',
						'aiff' => 'audio/x-aiff',
						'aifc' => 'audio/x-aiff',
						'm3u' => 'audio/x-mpegurl',
						'wax' => 'audio/x-ms-wax',
						'wma' => 'audio/x-ms-wma',
						'ram' => 'audio/x-pn-realaudio',
						'ra' => 'audio/x-pn-realaudio',
						'wav' => 'audio/x-wav',
						'pdb' => 'chemical/x-pdb',
						'xyz' => 'chemical/x-xyz',
						'bmp' => 'image/bmp',
						'cgm' => 'image/cgm',
						'gif' => 'image/gif',
						'ief' => 'image/ief',
						'jpeg' => 'image/jpeg',
						'jpg' => 'image/jpeg',
						'jpe' => 'image/jpeg',
						'png' => 'image/png',
						'svg' => 'image/svg+xml',
						'tiff' => 'image/tiff',
						'tif' => 'image/tiff',
						'djvu' => 'image/vnd.djvu',
						'djv' => 'image/vnd.djvu',
						'wbmp' => 'image/vnd.wap.wbmp',
						'ras' => 'image/x-cmu-raster',
						'ico' => 'image/x-icon',
						'pnm' => 'image/x-portable-anymap',
						'pbm' => 'image/x-portable-bitmap',
						'pgm' => 'image/x-portable-graymap',
						'ppm' => 'image/x-portable-pixmap',
						'rgb' => 'image/x-rgb',
						'xbm' => 'image/x-xbitmap',
						'xpm' => 'image/x-xpixmap',
						'xwd' => 'image/x-xwindowdump',
						'igs' => 'model/iges',
						'iges' => 'model/iges',
						'msh' => 'model/mesh',
						'mesh' => 'model/mesh',
						'silo' => 'model/mesh',
						'wrl' => 'model/vrml',
						'vrml' => 'model/vrml',
						'ics' => 'text/calendar',
						'ifb' => 'text/calendar',
						'css' => 'text/css',
						'shtml' => 'text/html',
						'html' => 'text/html',
						'htm' => 'text/html',
						'asc' => 'text/plain',
						'txt' => 'text/plain',
						'rtx' => 'text/richtext',
						'rtf' => 'text/rtf',
						'sgml' => 'text/sgml',
						'sgm' => 'text/sgml',
						'tsv' => 'text/tab-separated_values',
						'vbs' => 'text/vbscript',
						'wml' => 'text/vnd.wap.wml',
						'wmls' => 'text/vnd.wap.wmlscript',
						'cnf' => 'text/x-config',
						'conf' => 'text/x-config',
						'log' => 'text/x-log',
						'reg' => 'text/x-registry',
						'etx' => 'text/x-setext',
						'sql' => 'text/x-sql',
						'mpeg' => 'video/mpeg',
						'mpg' => 'video/mpeg',
						'mpe' => 'video/mpeg',
						'qt' => 'video/quicktime',
						'mov' => 'video/quicktime',
						'mxu' => 'video/vnd.mpegurl',
						'm4u' => 'video/vnd.mpegurl',
						'avi' => 'video/x-msvideo',
						'movie' => 'video/x-sgi-movie',
						'ice' => 'x-conference/x-cooltalk'
					);

					if(in_array(array_search($mime_type[0], $mimes), explode(',', str_replace(' ', '', strtolower($this->diafan->configmodules('attachment_extensions', 'files', $_POST["site_id"]))))))
					{
						$type = array_search($mime_type[0], $mimes);												
						$name = $altname . '.' . $type;					
					}
					else
					{
						throw new Exception('Вы не можете отправить файл '.$name.'. Доступны только следующие типы файлов: '.$this->diafan->configmodules('attachment_extensions', 'files', $_POST["site_id"]).'. Новые типы файлов добавляются в настройках модуля.');
					}
				}
				else
				{
					throw new Exception('Вы не можете отправить файл '.$name.'. Доступны только следующие типы файлов: '.$this->diafan->configmodules('attachment_extensions', 'files', $_POST["site_id"]).'. Новые типы файлов добавляются в настройках модуля.');
				}				
			}

			$newid = DB::query("INSERT INTO {attachments} (name,module_name,element_id) VALUES ('%s', '%s', %d)", $name, $this->diafan->table, $this->diafan->id);

			try
			{
				File::copy_file($_POST['attachment_link_upload'], USERFILES.'/'.$this->diafan->table.'/files/'.$newid);
			}
			catch(Exception $e)
			{
				DB::query("DELETE FROM {attachments} WHERE id=%d", $newid);
				throw new Exception($e->getMessage());
			}

			$size = filesize(ABSOLUTE_PATH.USERFILES.'/'.$this->diafan->table.'/files/'.$newid);
			DB::query("UPDATE {attachments} SET size=%d WHERE id=%d", $size, $newid);
		}
		if ($_POST["file_type"] == 3)
		{
			DB::query("INSERT INTO {files_links} (element_id, `link`) VALUES (%d, '%h')", $this->diafan->id, $_POST["attachment_link"]);
		}
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("files_rel", "element_id IN (".implode(",", $del_ids).") OR rel_element_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("files_links", "element_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("files_counter", "element_id IN (".implode(",", $del_ids).")");
	}
}
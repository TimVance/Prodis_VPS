<?php
/**
 * Редактирование вопросов
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
 * Faq_admin
 */
class Faq_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'faq';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'created' => array(
				'type' => 'datetime',
				'name' => 'Дата и время',
				'help' => 'Дата и время поступления вопроса в формате дд.мм.гггг чч:мм, приходит с сайта.',
			),
			'anons' => array(
				'type' => 'textarea',
				'name' => 'Вопрос',
				'help' => 'Вопрос, который задал пользователь в форме в пользовательской части сайта.',
				'multilang' => true,
				'height' => 200,
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'help' => 'Если отмечена, вопрос и ответ видны на сайте.',
				'default' => true,
				'multilang' => true,
			),
			'sendmail' => array(
				'type' => 'function',
				'name' => 'Отправить ответ на e-mail',
				'help' => 'Если отмечена, после сохранения сообщения ответ будет послан на e-mail отправителя. Содержание письма, а также e-mail, указываемый в обратном адресе можно редактировать в настройках модуля. Письмо не может быть отправлено, если не заполнено текстовое поле для ответа, поле с вопросом или e-mail получателя.',
			),
			'text' => array(
				'type' => 'editor',
				'name' => 'Ответ',
				'help' => 'Текстовое поле для ответа.',
				'multilang' => true,
			),
			'often' => array(
				'type' => 'checkbox',
				'name' => 'Часто задаваемый вопрос',
				'help' => 'Используется в шаблонной функции show_block.',
			),			
			'dynamic' => array(
				'type' => 'function',
				'name' => 'Динамические блоки',
			),			
			'hr1' => array(
				'type' => 'title',
				'name' => 'Вопрос задал',
			),
			'user_id' => array(
				'type' => 'function',
				'name' => 'Автор',
				'help' => 'Пользователь, создавший вопрос в форме на сайте.',
			),			
			'name' => array(
				'type' => 'text',
				'name' => 'Имя',
				'help' => 'Имя отправителя вопроса.',
				'multilang' => true,
			),
			'mail' => array(
				'type' => 'text',
				'name' => 'Email',
				'help' => 'Электронный ящик получателя ответа.',
			),
			'attachments' => array(
				'type' => 'module',
				'name' => 'Прикрепленные файлы',
			),
			'hr2' => 'hr',
			'cat_id' => array(
				'type' => 'function',
				'name' => 'Категория',
				'help' => 'Категория, к которой относится вопрос. Список категорий редактируется во вкладке выше. Возможно выбрать дополнительные категории, в которых вопрос также будет выводится. Чтобы выбрать несколько категорий, удерживайте CTRL. Параметр выводится, если в настройках модуля отмечена опция «Использовать категории».',
			),			
			'hr3' => 'hr',
			'tags' => array(
				'type' => 'module',
				'name' => 'Теги',
				'help' => 'Добавление тегов к вопросу. Можно добавить либо новый тег, либо открыть и выбрать из уже существующих тегов. Параметр выводится, если в настройках модуля включен параметр «Подключить теги».',
			),			
			'rel_elements' => array(
				'type' => 'function',
				'name' => 'Похожие вопросы',
				'help' => 'Выбор и добавление к текущему вопросу связей с другими вопросами. Похожие вопросы выводятся шаблонным тегом show_block_rel. По умолчанию связи между вопросами являются односторонними, это можно изменить, отметив опцию «В блоке похожих вопросов связь двусторонняя» в настройках модуля.',
			),			
			'hr4' => 'hr',
			'counter_view' => array(
				'type' => 'function',
				'name' => 'Счетчик просмотров',
				'help' => 'Количество просмотров на сайте текущего вопроса. Статистика ведется и параметр выводится, если в настройках модуля отмечена опция «Подключить счетчик просмотров».',
				'no_save' => true,
			),
			'comments' => array(
				'type' => 'module',
				'name' => 'Комментарии',
				'help' => 'Комментарии, которые оставили пользователи к текущему вопросу. Параметр выводится, если в настройках модуля включен параметр «Показывать комментарии к вопросам».',
			),
			'rating' => array(
				'type' => 'module',
				'name' => 'Рейтинг',
				'help' => 'Средний рейтинг, согласно голосованию пользователей сайта. Параметр выводится, если в настройках модуля включен параметр «Подключить рейтинг к вопросам».',
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
				'help' => 'Перенос вопроса на другую страницу сайта, к которой прикреплен модуль. Параметр выводится, если в настройках модуля отключена опция «Использовать категории», если опция подключена, то раздел сайта задается такой же, как у основной категории.',
			),
			'hr_info' => 'hr',			
			'title_seo' => array(
				'type' => 'title',
				'name' => 'Параметры SEO',
			),
			'title_meta' => array(
				'type' => 'text',
				'name' => 'Заголовок окна в браузере, тег Title',
				'help' => 'Если не заполнен, тег *Title* будет автоматически сформирован как «Название страницы – Название сайта»',
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
				'help' => 'Если заполнить, текущий вопрос будет опубликована на сайте в указанный период. В иное время пользователи сайта вопрос не будут видеть, получая ошибку 404 «Страница не найдена» (администратору сайта).'
			),
			'access' => array(
				'type' => 'function',
				'name' => 'Доступ',
				'help' => 'Если отметить опцию «Доступ только», вопрос и ответ увидят только авторизованные на сайте пользователи, отмеченных типов. Не авторизованные, в том числе поисковые роботы, увидят «404 Страница не найдена» (администратору сайта).',
			),
			'map_no_show' => array(
				'type' => 'checkbox',
				'name' => 'Не показывать на карте сайта',
				'help' => 'Скрывает отображение ссылки на вопрос в файле sitemap.xml и [модуле «Карта сайта»](http://www.diafan.ru/dokument/full-manual/modules/map/).',
			),
			'title_view' => array(
				'type' => 'title',
				'name' => 'Оформление',
			),
			'theme' => array(
				'type' => 'function',
				'name' => 'Шаблон страницы',
				'help' => 'Возможность подключить для страницы вопроса шаблон сайта отличный от основного (themes/site.php). Все шаблоны для сайта должны храниться в папке *themes* с расширением *.php* (например, themes/dizain_so_slajdom.php). Подробнее в [разделе «Шаблоны сайта»](http://www.diafan.ru/dokument/full-manual/templates/site/). (веб-мастеру и программисту, не меняйте этот параметр, если не уверены в результате!).',
			),
			'view' => array(
				'type' => 'function',
				'name' => 'Шаблон модуля',
				'help' => 'Шаблон вывода контента модуля на странице отдельного вопроса (веб-мастеру и программисту, не меняйте этот параметр, если не уверены в результате!).',
			),
			'search' => array(
				'type' => 'module',
				'name' => 'Индексирование для поиска',
				'help' => 'Вопрос автоматически индексируется для модуля «Поиск по сайту» при внесении изменений.',
			),
			'map' => array(
				'type' => 'module',
				'name' => 'Индексирование для карты сайта',
				'help' => 'Вопрос автоматически индексируется для карты сайта sitemap.xml.',
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'created' => array(
			'name' => 'Дата',
			'type' => 'date',
			'sql' => true,
		),
		'anons' => array(
			'name' => 'Вопрос и категория',
			'sql' => true,
		),
		'adapt' => array(
			'class_th' => 'item__th_adapt',
		),
		'separator' => array(
			'class_th' => 'item__th_seporator',
		),
		'text' => array(
			'name' => 'Ответ',
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
		'anons' => array(
			'type' => 'text',
			'name' => 'Искать по вопросу',
		),
		'text' => array(
			'type' => 'text',
			'name' => 'Искать по ответу',
		),
		'user_id' => array(
			'type' => 'none',
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
		if(! $this->diafan->configmodules("cat", "faq", $this->diafan->_route->site))
		{
			$this->diafan->config("element", false);
			$this->diafan->config("element_multiple", false);
		}
		if(! $this->diafan->configmodules("page_show", "faq", $this->diafan->_route->site))
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
		if ($this->diafan->config('element') && !$this->diafan->not_empty_categories)
		{
			echo '<div class="error">'.sprintf($this->diafan->_('В %sнастройках%s модуля подключены категории, чтобы начать добавлять вопрос создайте хотя бы одну %sкатегорию%s..'),'<a href="'.BASE_PATH_HREF.'faq/config/">', '</a>', '<a href="'.BASE_PATH_HREF.'faq/category/'.($this->diafan->_route->site ? 'site'.$this->diafan->_route->site.'/' : '').'">', '</a>').'</div>';
		}
		else
		{
			$this->diafan->addnew_init('Добавить вопрос-ответ');
		}
	}

	/**
	 * Выводит список вопросов
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Выводит название элемента в списке элементов
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_anons($row, $var)
	{
		$text = '<div class="name'.(! empty($var["class"]) ? ' '.$var["class"] : '').'">';
		$name = $this->diafan->short_text($row["anons"], 50);
		if (! $name)
		{
			$name = $row['id'];
		}

		$text .= '<a name="'.$row['id'].'" href="';
		$text .= $this->diafan->get_base_link($row);
		$text .= '" title="'.$this->diafan->_('Редактировать').' ('.$row["id"].')">'.$name.'</a>';
		$text .= $this->diafan->list_variable_parent($row, array());
		$text .= $this->diafan->list_variable_date_period($row, array());
		$text .= '</div>';
		return $text;
	}

	/**
	 * Выводит статус вопроса (отвеченный/неотвеченный), в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_text($row, $var)
	{
		$text = '<div class="text'.(! empty($var["class"]) ? ' '.$var["class"] : '').'">';
		if(! $row["text"])
		{
			$text .= '('.$this->diafan->_('без ответа').')';
		}
		else
		{
			$text .= $this->diafan->short_text($row["text"]);
		}
		$text .= '</div>';
		
		return $text;
	}

	/**
	 * Редактирование поля "Отправить ответ"
	 * @return void
	 */
	public function edit_variable_sendmail()
	{
		echo '
		<div class="unit">
			<input name="sendmail" id="input_sendmail" value="1" type="checkbox">
			<label for="input_sendmail"><b>'.$this->diafan->variable_name().'</b>'.$this->diafan->help().'</label>
			
		</div>';
	}

	/**
	 * Сохранение поля "Отправить ответ"
	 * @return void
	 */
	public function save_variable_sendmail()
	{
		if(! empty($_POST["sendmail"]) && ! empty($_POST["mail"]) && ! empty($_POST["text"]) && ! empty($_POST["anons"]))
		{
			$subject = str_replace(
				array('%title', '%url'), array(TITLE, BASE_URL), $this->diafan->configmodules('subject', 'faq', $_POST["site_id"])
			);
	
			$message = str_replace(
				array(
					', %name',
					'%name',
					'%title',
					'%url',
					'%question',
					'%answer'
				), array(
					strip_tags($_POST["name"]) ? ', '.strip_tags($_POST["name"]) : '',
					strip_tags($_POST["name"]),
					TITLE,
					BASE_URL,
					nl2br(htmlspecialchars($_POST["anons"])),
					$_POST["text"]
				), $this->diafan->configmodules('message', 'faq', $_POST["site_id"])
			);
	
			$this->diafan->_postman->message_add_mail(
				trim(strip_tags($_POST["mail"])),
				$subject,
				$message,
				$this->diafan->configmodules("emailconf", 'faq', $_POST["site_id"]) && $this->diafan->configmodules("email", 'faq', $_POST["site_id"]) ? $this->diafan->configmodules("email", 'faq', $_POST["site_id"]) : EMAIL_CONFIG
			);
			$this->diafan->err = 5;
		}
		elseif(! empty($_POST["sendmail"]))
		{
			$this->diafan->err = 6;
		}
	}

	/**
	 * Редактирование поля "Анонс"
	 *
	 * @return void
	 */
	public function edit_variable_anons()
	{
		$this->diafan->show_table_tr_textarea('anons', $this->diafan->variable_name(), $this->diafan->value, $this->diafan->help(), false);
	}

	/**
	 * Сохранение поля "Анонс"
	 * @return void
	 */
	public function save_variable_anons()
	{
		$this->diafan->set_query("anons"._LANG."='%h'");
		$this->diafan->set_value($_POST['anons']);
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("faq_rel", "element_id IN (".implode(",", $del_ids).") OR rel_element_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("faq_counter", "element_id IN (".implode(",", $del_ids).")");
	}
}
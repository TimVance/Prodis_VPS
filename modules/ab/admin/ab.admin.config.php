<?php
/**
 * Настройки модуля
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
 * Ab_admin_config
 */
class Ab_admin_config extends Frame_admin
{
	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'base' => array (
			'hr1' => array(
				'type' => 'title',
				'name' => 'Основные',
			),
			'nastr' => array(
				'type' => 'numtext',
				'name' => 'Количество объявлений на странице',
				'help' => 'Количество одновременно выводимых объявлений в списке.',
			),
			'nastr_cat' => array(
				'type' => 'numtext',
				'name' => 'Количество категорий на странице',
				'help' => 'Количество одновременно выводимых категорий в списке на первой страницы модуля.',
				'depend' => 'cat',
			),
			'show_more' => array(
				'type' => 'checkbox',
				'name' => 'Включить «Показать ещё»',
				'help' => 'На странице объявлений появится кнопка «Показать ещё». Увеличивает количество одновременно выводимых объявлений в списке.',
			),
			'format_date' => array(
				'type' => 'select',
				'name' => 'Формат даты',
				'help' => 'Позволяет настроить отображение даты в модуле.',
				'select' => array(
					0 => '01.05.2016',
					6 => '01.05.2016 14:45',
					1 => '1 мая 2016 г.',
					2 => '1 мая',
					3 => '1 мая 2016, понедельник',
					5 => 'вчера 15:30',
					4 => 'не отображать',
				),
			),
			'rel_two_sided' => array(
				'type' => 'checkbox',
				'name' => 'В блоке похожих объявлений связь двусторонняя',
				'help' => 'Позволяет установленную в объявлении связь с другим объявлением использовать в двух направлениях.',
			),			
			'hr2' => array(
				'type' => 'title',
				'name' => 'Категории',
			),
			'cat' => array(
				'type' => 'checkbox',
				'name' => 'Использовать категории',
				'help' => 'Позволяет включить/отключить категории объявлений.',
			),
			'count_list' => array(
				'type' => 'numtext',
				'name' => 'Количество объявлений в списке категорий',
				'help' => 'Для первой страницы модуля, где выходят по несколько объявлений из всех категорий.',
				'depend' => 'cat',
			),
			'count_child_list' => array(
				'type' => 'numtext',
				'name' => 'Количество объявлений в списке вложенной категории',
				'help' => 'Для первой страницы модуля и для страницы категории.',
				'depend' => 'cat',
			),
			'children_elements' => array(
				'type' => 'checkbox',
				'name' => 'Показывать объявления подкатегорий',
				'help' => 'Если отмечена, в списке объявлений категории будут отображатся объявления из всех вложенных категорий.',
				'depend' => 'cat',
			),
			'hr3' => array(
				'type' => 'title',
				'name' => 'Подключения',
			),
			'counter' => array(
				'type' => 'checkbox',
				'name' => 'Счетчик просмотров',
				'help' => 'Позволяет считать количество просмотров отдельного объявления.',
			),
			'counter_site' => array(
				'type' => 'checkbox',
				'name' => 'Выводить счетчик на сайте',
				'help' => 'Позволяет вывести на сайте количество просмотров отдельного объявления. Параметр выводится, если отмечена опция «Счетчик просмотров».',
				'depend' => 'counter',
			),
			'geomap' => array(
				'type' => 'module',
				'name' => 'Подключить геокарту',
				'help' => 'Подключение модуля «Геокарта». Параметр не будет включен, если модуль «Геокарта» не установлен. Подробности см. в разделе [модуль «Геокарта»](http://www.diafan.ru/dokument/full-manual/upmodules/geomap/).',
			),
			'comments' => array(
				'type' => 'module',
				'name' => 'Подключить комментарии к объявлениям',
				'help' => 'Подключение модуля «Комментарии». Параметр не будет включен, если модуль «Комментарии» не установлен. Подробности см. в разделе [модуль «Комментарии»](http://www.diafan.ru/dokument/full-manual/upmodules/comments/).',
			),
			'comments_cat' => array(
				'type' => 'none',
				'name' => 'Показывать комментарии к категориям',
				'help' => 'Подключение модуля «Комментарии» к категориям объявлений. Параметр не будет включен, если модуль «Комментарии» не установлен. Подробности см. в разделе [модуль «Комментарии»](http://www.diafan.ru/dokument/full-manual/upmodules/comments/).',
				'no_save' => true,
			),
			'tags' => array(
				'type' => 'module',
				'name' => 'Подключить теги',
				'help' => 'Подключение модуля «Теги». Параметр не будет включен, если модуль «Теги» не установлен. Подробности см. в разделе [модуль «Теги»](http://www.diafan.ru/dokument/full-manual/modules/tags/).',
			),
			'rating' => array(
				'type' => 'module',
				'name' => 'Показывать рейтинг объявлений',
				'help' => 'Подключение модуля «Рейтинг». Параметр не будет включен, если модуль «Рейтинг» не установлен. Подробности см. в разделе [модуль «Рейтинг»](http://www.diafan.ru/dokument/full-manual/upmodules/rating/).',
			),
			'rating_cat' => array(
				'type' => 'none',
				'name' => 'Подключить рейтинг к категориям',
				'help' => 'Подключение модуля «Рейтинг» к категориям. Параметр не будет включен, если модуль «Рейтинг» не установлен. Подробности см. в разделе [модуль «Рейтинг»](http://www.diafan.ru/dokument/full-manual/upmodules/rating/).',
				'no_save' => true,
			),
			'keywords' => array(
				'type' => 'module',
				'name' => 'Подключить перелинковку',
				'help' => 'Отображение перелинковки в модуле. Подробности см. в разделе [модуль «Перелинковка»](http://www.diafan.ru/dokument/full-manual/upmodules/keywords/).',
			),
			'hr4' => array(
				'type' => 'title',
				'name' => 'Автогенерация для SEO',
			),
			'title_tpl' => array(
				'type' => 'text',
				'name' => 'Шаблон для автоматического генерирования Title',
				'help' => "Если шаблон задан и для объявления не прописан заголовок *Title*, то заголовок автоматически генерируется по шаблону. В шаблон можно добавить:\n\n* %name – название,\n* %category – название категории,\n* %parent_category – название категории верхнего уровня (SEO-специалисту).",
				'multilang' => true
			),
			'title_tpl_cat' => array(
				'type' => 'text',
				'name' => 'Шаблон для автоматического генерирования Title для категории',
				'help' => "Если шаблон задан и для категории не прописан заголовок *Title*, то заголовок автоматически генерируется по шаблону. В шаблон можно добавить:\n\n* %name – название категории,\n* %parent – название категории верхнего уровня,\n\n* %page – страница (текст можно поменять в интерфейсе «Языки сайта» – «Перевод интерфейса») (SEO-специалисту).",
				'multilang' => true,
				'depend' => 'cat',
			),
			'keywords_tpl' => array(
				'type' => 'text',
				'name' => 'Шаблон для автоматического генерирования Keywords',
				'help' => "Если шаблон задан и для объявления не заполнено поле *Keywords*, то поле *Keywords* автоматически генерируется по шаблону. В шаблон можно добавить:\n\n* %name – название,\n* %category – название категории,\n* %parent_category – название категории верхнего уровня (SEO-специалисту).",
				'multilang' => true
			),
			'keywords_tpl_cat' => array(
				'type' => 'text',
				'name' => 'Шаблон для автоматического генерирования Keywords для категории',
				'help' => "Если шаблон задан и для категории не заполнено поле *Keywords*, то поле *Keywords* автоматически генерируется по шаблону. В шаблон можно добавить:\n\n* %name – название категории,\n* %parent – название категории верхнего уровня (SEO-специалисту).",
				'multilang' => true,
				'depend' => 'cat',
			),
			'descr_tpl' => array(
				'type' => 'text',
				'name' => 'Шаблон для автоматического генерирования Description',
				'help' => "Если шаблон задан и для объявления не заполнено поле *Description*, то поле *Description* автоматически генерируется по шаблону. В шаблон можно добавить:\n\n* %name – название,\n* %category – название категории,\n* %parent_category – название категории верхнего уровня,\n* %anons – краткое описание (SEO-специалисту).",
				'multilang' => true,
			),
			'descr_tpl_cat' => array(
				'type' => 'text',
				'name' => 'Шаблон для автоматического генерирования Description для категории',
				'help' => "Если шаблон задан и для категории не заполнено поле *Description*, то поле Description автоматически генерируется по шаблону. В шаблон можно добавить:\n\n* %name – название категории,\n* %parent – название категории верхнего уровня,\n* %anons – краткое описание (SEO-специалисту).",
				'multilang' => true,
				'depend' => 'cat',
			),
			'hr5' => array(
				'type' => 'title',
				'name' => 'Оформление',
			),
			'themes' => array(
				'type' => 'function',
				'hide' => true,
			),
			'theme_list' => array(
				'type' => 'none',
				'name' => 'Шаблон для списка элементов',
				'help' => 'По умолчанию modules/ab/views/ab.view.list.php. Параметр для разработчиков! Не устанавливайте, если не уверены в результате.',
			),
			'view_list' => array(
				'type' => 'none',
				'hide' => true,
			),
			'theme_list_rows' => array(
				'type' => 'none',
				'name' => 'Шаблон для элементов в списке',
				'help' => 'По умолчанию modules/ab/views/ab.view.rows.php. Параметр для разработчиков! Не устанавливайте, если не уверены в результате. Значение параметра важно для AJAX.',
			),
			'view_list_rows' => array(
				'type' => 'none',
				'hide' => true,
			),
			'theme_first_page' => array(
				'type' => 'none',
				'name' => 'Шаблон для первой страницы модуля (если подключены категории)',
				'help' => 'По умолчанию modules/ab/views/ab.view.fitst_page.php. Параметр для разработчиков! Не устанавливайте, если не уверены в результате.',
			),
			'view_first_page' => array(
				'type' => 'none',
				'hide' => true,
			),
			'theme_first_page_rows' => array(
				'type' => 'none',
				'name' => 'Шаблон для элементов в списке первой страницы модуля (если подключены категории)',
				'help' => 'По умолчанию modules/ab/views/ab.view.fitst_page.php. Параметр для разработчиков! Не устанавливайте, если не уверены в результате. Значение параметра важно для AJAX.',
			),
			'view_first_page_rows' => array(
				'type' => 'none',
				'hide' => true,
			),
			'theme_id' => array(
				'type' => 'none',
				'name' => 'Шаблон для страницы элемента',
				'help' => 'По умолчанию, modules/ab/views/ab.view.id.php. Параметр для разработчиков! Не устанавливайте, если не уверены в результате.',
			),
			'view_id' => array(
				'type' => 'none',
				'hide' => true,
			),
			'theme_list_param' => array(
				'type' => 'none',
				'name' => 'Шаблон для списка элементов с одинаковой характеристикой',
				'help' => 'Параметр для разработчиков! Не устанавливайте, если не уверены в результате.',
			),
			'view_list_param' => array(
				'type' => 'none',
				'hide' => true,
			),
			'theme_list_param_rows' => array(
				'type' => 'none',
				'name' => 'Шаблон для элементов списка с одинаковой характеристикой',
				'help' => 'Параметр для разработчиков! Не устанавливайте, если не уверены в результате. Значение параметра важно для AJAX.',
			),
			'view_list_param_rows' => array(
				'type' => 'none',
				'hide' => true,
			),
			'theme_list_search' => array(
				'type' => 'none',
				'name' => 'Шаблон для поиска элементов',
				'help' => 'Параметр для разработчиков! Не устанавливайте, если не уверены в результате.',
			),
			'view_list_search' => array(
				'type' => 'none',
				'hide' => true,
			),
			'theme_list_search_rows' => array(
				'type' => 'none',
				'name' => 'Шаблон элементов в списке для поиска элементов',
				'help' => 'Параметр для разработчиков! Не устанавливайте, если не уверены в результате. Значение параметра важно для AJAX.',
			),
			'view_list_search_rows' => array(
				'type' => 'none',
				'hide' => true,
			),
			'theme_list_my' => array(
				'type' => 'none',
				'name' => 'Шаблон для объявлений пользователя',
				'help' => 'Параметр для разработчиков! Не устанавливайте, если не уверены в результате.',
			),
			'view_list_my' => array(
				'type' => 'none',
				'hide' => true,
			),
			'theme_list_my_rows' => array(
				'type' => 'none',
				'name' => 'Шаблон для объявлений пользователя',
				'help' => 'Параметр для разработчиков! Не устанавливайте, если не уверены в результате. Значение параметра важно для AJAX.',
			),
			'view_list_my_rows' => array(
				'type' => 'none',
				'hide' => true,
			),
			'hr6' => array(
				'type' => 'title',
				'name' => 'Дополнительно',
			),
			'admin_page'     => array(
				'type' => 'checkbox',
				'name' => 'Отдельный пункт в меню администрирования для каждого раздела сайта',
				'help' => 'Если модуль подключен к нескольким страницам сайта, отметка данного параметра выведет несколько пунктов в меню административной части для удобства быстрого доступа (администратору сайта).',
			),
			'map' => array(
				'type' => 'module',
				'name' => 'Индексирование для карты сайта',
				'help' => 'При изменении настроек, влияющих на отображение страницы, модуль автоматически переиндексируется для карты сайта sitemap.xml.',
			),
			'where_access' => array(
				'type' => 'none',
				'hide' => true,
			),
		),
		'form' => array (
			'only_user' => array(
				'type' => 'checkbox',
				'name' => 'Добавлять объявления могут только зарегистрированные пользователи',
			),
			'captcha' => array(
				'type' => 'module',
				'name' => 'Использовать защитный код (капчу)',
				'help' => 'Для добавления объявления пользователь должен ввести защитный код.',
			),
			'premoderation' => array(
				'type' => 'checkbox',
				'name' => 'Модерация объявлений для',
				'help' => 'Объявление добавляется из формы на сайте не активным и активируется в административной части сайта.',
			),
			'form_name' => array(
				'type' => 'checkbox',
				'name' => 'Использовать в форме объявления обязательные поля',
				'help' => 'Позволяет выводить/скрыть обязательные поля (название, краткий анонс, описание объявления, период показа, изображения) в форме добавления объявления.',
			),
			'form_anons' => array(
				'type' => 'none',
				'name' => 'Поле «Анонс»',
				'help' => '',
				'hide' => true,
			),
			'form_text' => array(
				'type' => 'none',
				'name' => 'Поле «Описание объявления»',
				'help' => '',
				'hide' => true,
			),
			'form_date_finish' => array(
				'type' => 'none',
				'name' => 'Поле «Период показа»',
				'help' => '',
				'hide' => true,
			),
			'form_images' => array(
				'type' => 'none',
				'name' => 'Изображения',
				'help' => '',
				'hide' => true,
			),
		),
		'images' => array (
			'images' => array(
				'type' => 'module',
				'element_type' => array('element', 'cat'),
				'hide' => true,
			),
			'images_element' => array(
				'type' => 'none',
				'name' => 'Использовать изображения',
				'help' => 'Позволяет включить/отключить загрузку изображений к объявлениям.',
				'no_save' => true,
			),
			'images_variations_element' => array(
				'type' => 'none',
				'name' => 'Генерировать размеры изображений',
				'help' => 'Размеры изображений, заданные в модуле «Изображения» и тег латинскими буквами для подключения изображения на сайте. Обязательно должны быть заданы два размера: превью изображения в списке объявлений (тег medium) и полное изображение (тег large).',
				'no_save' => true,
			),
			'list_img_element' => array(
				'type' => 'none',
				'name' => 'Отображение изображений в списке',
				'help' => "Параметр принимает значения:\n\n* нет (отключает отображение изображений в списке);\n* показывать одно изображение;\n* показывать все изображения. Параметр выводится, если отмечена опция «Использовать изображения».",
				'no_save' => true,
			),
			'images_cat' => array(
				'type' => 'none',
				'name' => 'Использовать изображения для категорий',
				'help' => 'Позволяет включить/отключить загрузку изображений к категориям.',
				'no_save' => true,
			),
			'images_variations_cat' => array(
				'type' => 'none',
				'name' => 'Генерировать размеры изображений для категорий',
				'help' => 'Размеры изображений, заданные в модуле «Изображения» и тег латинскими буквами для подключения изображения на сайте. Обязательно должны быть заданы два размера: превью изображения в списке категорий (тег medium) и полное изображение (тег large). Параметр выводится, если отмечена опция «Использовать изображения для категорий».',
				'no_save' => true,
			),
			'list_img_cat' => array(
				'type' => 'none',
				'name' => 'Отображение изображений в списке категорий',
				'help' => "Параметр принимает значения:\n\n* нет (отключает отображение изображений в списке);\n* показывать одно изображение;\n* показывать все изображения. Параметр выводится, если отмечена опция «Использовать изображения для категорий».",
				'no_save' => true,
			),
			'use_animation' => array(
				'type' => 'none',
				'name' => 'Использовать анимацию при увеличении изображений',
				'help' => 'Параметр добавляет JavaScript код, позволяющий включить анимацию при увеличении изображений. Параметр выводится, если отмечена опция «Использовать изображения».',
				'no_save' => true,
			),
			'upload_max_filesize' => array(
				'type' => 'none',
				'name' => 'Максимальный размер загружаемых файлов',
				'help' => 'Параметр показывает максимально допустимый размер загружаемых файлов, установленный в настройках хостинга. Параметр выводится, если отмечена опция «Использовать изображения».',
				'no_save' => true,
			),
			'resize' => array(
				'type' => 'none',
				'name' => 'Применить настройки ко всем ранее загруженным изображениям',
				'help' => 'Позволяет переконвертировать размер уже загруженных изображений. Кнопка необходима, если изменены настройки размеров изображений. Параметр выводится, если отмечена опция «Использовать изображения».',
				'no_save' => true,
			),
		),
		'send_mails' => array (
			'add_message' => array(
				'type' => 'textarea',
				'name' => 'Сообщение после отправки',
				'help' => 'Сообщение, получаемое пользователем при удачной загрузки объявления из формы на сайте, допускаются HTML-теги для оформления сообщения.',
				'multilang' => true,
			),		
			'emailconf' => array(
				'type' => 'function',
				'name' => 'E-mail, указываемый в обратном адресе пользователю',
				'help' => "Возможные значения:\n\n* e-mail, указанный в параметрах сайта;\n* другой (при выборе этого значения появляется дополнительное поле **впишите e-mail**).",
			),
			'email' => array(
				'type' => 'none',
				'name' => 'впишите e-mail',
				'hide' => true,
			),
			'hr8' => 'hr',
			'sendmailadmin' => array(
				'type' => 'checkbox',
				'name' => 'Уведомлять о поступлении новых объявлений на e-mail',
				'help' => 'Возможность уведомления администратора о поступлении новых объявлений из формы в пользовательской части сайта.',
			),
			'emailconfadmin' => array(
				'type' => 'function',
				'name' => 'E-mail для уведомлений администратора',
				'help' => "Возможные значения:\n\n* e-mail, указанный в параметрах сайта;\n* другой (при выборе этого значения появляется дополнительное поле **впишите e-mail**).",
				'depend' => 'sendmailadmin',
			),
			'email_admin' => array(
				'type' => 'none',
				'name' => 'впишите e-mail',
				'hide' => true,
				'depend' => 'sendmailadmin',
			),
			'subject_admin' => array(
				'type' => 'text',
				'name' => 'Тема письма для уведомлений',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru).",
				'depend' => 'sendmailadmin',
			),
			'message_admin' => array(
				'type' => 'textarea',
				'name' => 'Сообщение для уведомлений',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru),\n* %message – объявление.",
				'depend' => 'sendmailadmin',
			),
			'hr9' => 'hr',
			'sendsmsadmin' => array(
				'type' => 'checkbox',
				'name' => 'Уведомлять о поступлении новых объявлений по SMS',
				'help' => 'Возможность отправлять SMS администратору при поступлении объявления. Параметр можно подключить, если в [Параметрах сайта](http://www.diafan.ru/dokument/full-manual/sysmodules/config/) настроены SMS-уведомления.',
			),
			'sms_admin' => array(
				'type' => 'text',
				'name' => 'Номер телефона в федеральном формате',
				'help' => 'Номер телефона для SMS-уведомлений администратора о новом объявлении.',
				'depend' => 'sendsmsadmin',
			),
			'sms_message_admin' => array(
				'type' => 'textarea',
				'name' => 'Сообщение для уведомлений',
				'help' => 'Текст сообщения для SMS-уведомлений администратора о новом объявлении. Не более 800 символов.',
				'depend' => 'sendsmsadmin',
			),
		),
	);

	/**
	 * @var array названия табов
	 */
	public $tabs_name = array(
		'base' => 'Основные настройки',
		'images' => 'Изображения',
		'form' => 'Добавление объявления на сайте',
		'send_mails' => 'Сообщения и уведомления',
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'tab_card', // использование вкладок
		'element_site', // делит элементы по разделам (страницы сайта, к которым прикреплен модуль)
		'config', // файл настроек модуля
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		if(! SMS)
		{
			$this->diafan->variable("sendsmsadmin", "disabled", true);
			$name = $this->diafan->_($this->diafan->variable("sendsmsadmin", "name")).'<br>'.$this->diafan->_('Необходимо %sнастроить%s SMS-уведомления.', '<a href="'.BASE_PATH_HREF.'config/">', '</a>');
			$this->diafan->variable("sendsmsadmin", "name", $name);
			$this->diafan->configmodules("sendsmsadmin", $this->diafan->_admin->module, $this->diafan->_route->site, _LANG, 0);
		}
	}

	/**
	 * Редактирование поля "Шаблон страницы для разных ситуаций"
	 * @return void
	 */
	public function edit_config_variable_themes()
	{
		$themes = $this->diafan->get_themes();
		$views = $this->diafan->get_views($this->diafan->_admin->module);

		echo '<div id="theme_list" class="unit">
				<div class="infofield">
		'.$this->diafan->variable_name("theme_list").'
			</div>
				<select name="theme_list" style="width:250px">
					<option value="">'.(! empty($themes['site.php']) ? $themes['site.php'] : 'site.php').'</option>';
		foreach ($themes as $key => $value)
		{
			if ($key == 'site.php')
				continue;
			echo '<option value="'.$key.'"'.( $this->diafan->values("theme_list") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
				</select>
				<select name="view_list" style="width:250px">
					<option value="">'.(! empty($views['list']) ? $views['list'] : $this->diafan->_admin->module.'.view.list.php').'</option>';
		foreach ($views as $key => $value)
		{
			if ($key == 'list')
			{
				continue;
			}
			echo '<option value="'.$key.'"'.( $this->diafan->values("view_list") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
				</select>
				'.$this->diafan->help("theme_list").'
				<select name="view_list_rows" style="width:250px">
					<option value="">'.(! empty($views['rows']) ? $views['rows'] : $this->diafan->_admin->module.'.view.rows.php').'</option>';
		foreach ($views as $key => $value)
		{
			if ($key == 'rows')
			{
				continue;
			}
			echo '<option value="'.$key.'"'.( $this->diafan->values("view_list_rows") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
				</select>
				'.$this->diafan->help("theme_list_rows").'
			</div>

		<div id="theme_first_list" class="unit">
				<div class="infofield">
		'.$this->diafan->variable_name("theme_first_list").'
			</div>
				<select name="theme_first_page" style="width:250px">
					<option value="">'.(! empty($themes['site.php']) ? $themes['site.php'] : 'site.php').'</option>';
		foreach ($themes as $key => $value)
		{
			if ($key == 'site.php')
				continue;
			echo '<option value="'.$key.'"'.( $this->diafan->values("theme_first_page") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
				</select>
				<select name="view_first_page" style="width:250px">
					<option value="">'.(! empty($views['first_page']) ? $views['first_page'] : $this->diafan->_admin->module.'.view.first_page.php').'</option>';
		foreach ($views as $key => $value)
		{
			if ($key == 'first_page')
			{
				continue;
			}
			echo '<option value="'.$key.'"'.( $this->diafan->values("view_first_page") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
				</select>
				'.$this->diafan->help("theme_first_page").'
				<select name="view_first_page_rows" style="width:250px">
					<option value="">'.(! empty($views['first_page']) ? $views['first_page'] : $this->diafan->_admin->module.'.view.first_page.php').'</option>';
		foreach ($views as $key => $value)
		{
			if ($key == 'first_page')
			{
				continue;
			}
			echo '<option value="'.$key.'"'.( $this->diafan->values("view_first_page_rows") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
				</select>
				'.$this->diafan->help("theme_first_page_rows").'
			</div>

		<div id="theme_id" class="unit">
				<div class="infofield">
		'.$this->diafan->variable_name("theme_id").'
			</div>
				<select name="theme_id" style="width:250px">
					<option value="">'.(! empty($themes['site.php']) ? $themes['site.php'] : 'site.php').'</option>';
		foreach ($themes as $key => $value)
		{
			if ($key == 'site.php')
				continue;
			echo '<option value="'.$key.'"'.( $this->diafan->values("theme_id") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
				</select>
				<select name="view_id" style="width:250px">
					<option value="">'.(! empty($views['id']) ? $views['id'] : $this->diafan->_admin->module.'.view.id.php').'</option>';
		foreach ($views as $key => $value)
		{
			if ($key == 'id')
			{
				continue;
			}
			echo '<option value="'.$key.'"'.( $this->diafan->values("view_id") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
				</select>
				'.$this->diafan->help("theme_id").'
			</div>
		<div id="theme_list_param" class="unit">
				<div class="infofield">
		'.$this->diafan->variable_name("theme_list_param").'
			</div>
				<select name="theme_list_param" style="width:250px">
					<option value="">'.(! empty($themes['site.php']) ? $themes['site.php'] : 'site.php').'</option>';
		foreach ($themes as $key => $value)
		{
			if ($key == 'site.php')
				continue;
			echo '<option value="'.$key.'"'.( $this->diafan->values("theme_list_param") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
				</select>
				<select name="view_list_param" style="width:250px">
					<option value="">'.(! empty($views['list']) ? $views['list'] : $this->diafan->_admin->module.'.view.list.php').'</option>';
		foreach ($views as $key => $value)
		{
			if ($key == 'list')
			{
				continue;
			}
			echo '<option value="'.$key.'"'.( $this->diafan->values("view_list_param") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
				</select>
				'.$this->diafan->help("theme_list_param").'
				<select name="view_list_param_rows" style="width:250px">
					<option value="">'.(! empty($views['rows']) ? $views['rows'] : $this->diafan->_admin->module.'.view.rows.php').'</option>';
		foreach ($views as $key => $value)
		{
			if ($key == 'rows')
			{
				continue;
			}
			echo '<option value="'.$key.'"'.( $this->diafan->values("view_list_param_rows") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
				</select>
				'.$this->diafan->help("theme_list_param_rows").'
			</div>
		<div id="theme_list_search" class="unit">
				<div class="infofield">
		'.$this->diafan->variable_name("theme_list_search").'
			</div>
				<select name="theme_list_search" style="width:250px">
					<option value="">'.(! empty($themes['site.php']) ? $themes['site.php'] : 'site.php').'</option>';
		foreach ($themes as $key => $value)
		{
			if ($key == 'site.php')
				continue;
			echo '<option value="'.$key.'"'.( $this->diafan->values("theme_list_search") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
				</select>
				<select name="view_list_search" style="width:250px">
					<option value="">'.(! empty($views['list']) ? $views['list'] : $this->diafan->_admin->module.'.view.list.php').'</option>';
		foreach ($views as $key => $value)
		{
			if ($key == 'list')
			{
				continue;
			}
			echo '<option value="'.$key.'"'.( $this->diafan->values("view_list_search") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
				</select>
				'.$this->diafan->help("theme_list_search").'
				<select name="view_list_search_rows" style="width:250px">
					<option value="">'.(! empty($views['rows']) ? $views['rows'] : $this->diafan->_admin->module.'.view.rows.php').'</option>';
		foreach ($views as $key => $value)
		{
			if ($key == 'rows')
			{
				continue;
			}
			echo '<option value="'.$key.'"'.( $this->diafan->values("view_list_search_rows") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
				</select>
				'.$this->diafan->help("theme_list_search_rows").'
			</div>';

		echo '<div id="theme_list_my" class="unit">
			<div class="infofield">
			'.$this->diafan->variable_name("theme_list_my").'
			</div>
			<select name="theme_list_my" style="width:250px">
				<option value="">'.(! empty($themes['site.php']) ? $themes['site.php'] : 'site.php').'</option>';
				foreach ($themes as $key => $value)
				{
					if ($key == 'site.php')
						continue;
					echo '<option value="'.$key.'"'.( $this->diafan->values("theme_list_my") == $key ? ' selected' : '' ).'>'.$value.'</option>';
				}
				echo '
			</select>
			<select name="view_list_my" style="width:250px">
				<option value="">'.(! empty($views['list']) ? $views['list'] : $this->diafan->_admin->module.'.view.list.php').'</option>';
				foreach ($views as $key => $value)
				{
					if ($key == 'list')
					{
						continue;
					}
					echo '<option value="'.$key.'"'.( $this->diafan->values("view_list_my") == $key ? ' selected' : '' ).'>'.$value.'</option>';
				}
				echo '
			</select>
			'.$this->diafan->help("theme_list_my").'
			<select name="view_list_my_rows" style="width:250px">
				<option value="">'.(! empty($views['rows']) ? $views['rows'] : $this->diafan->_admin->module.'.view.rows.php').'</option>';
				foreach ($views as $key => $value)
				{
					if ($key == 'rows')
					{
						continue;
					}
					echo '<option value="'.$key.'"'.( $this->diafan->values("view_list_my_rows") == $key ? ' selected' : '' ).'>'.$value.'</option>';
				}
				echo '
			</select>
			'.$this->diafan->help("theme_list_my_rows").'
		</div>';
	}

	/**
	 * Редактирование поля "Поля формы"
	 * @return void
	 */
	public function edit_config_variable_form_name()
	{
		echo '
		<div id="theme_list" class="unit">
				<div class="infofield">
		'.$this->diafan->variable_name("").'
			</div>
			<input type="checkbox" name="form_name" id="input_form_name" value="1"'.($this->diafan->values("form_name") ? ' checked' : '').'>
			<label for="input_form_name">'.$this->diafan->_('название').'</label><br>
			<input type="checkbox" name="form_anons" id="input_form_anons" value="1"'.($this->diafan->values("form_anons") ? ' checked' : '').'>
			<label for="input_form_anons">'.$this->diafan->_('краткий анонс').'</label><br>
			<input type="checkbox" name="form_text" id="input_form_text" value="1"'.($this->diafan->values("form_text") ? ' checked' : '').'>
			<label for="input_form_text">'.$this->diafan->_('описание объявления').'</label><br>
			<input type="checkbox" name="form_date_finish" id="input_form_date_finish" value="1"'.($this->diafan->values("form_date_finish") ? ' checked' : '').'>
			<label for="input_form_date_finish">'.$this->diafan->_('показывать на сайте до').'</label><br>
			<input type="checkbox" name="form_images" id="input_form_images" value="1"'.($this->diafan->values("form_images") ? ' checked' : '').'>
			<label for="input_form_images">'.$this->diafan->_('изображения').'</label><br>
		</div>';
	}

	/**
	 * Редактирование поля "Предмодерация объявлений"
	 * 
	 * @return void
	 */
	public function edit_config_variable_premoderation()
	{
		echo '
		<div id="premoderation" class="unit">
				<div class="infofield">
		'.$this->diafan->variable_name().'
			</div>';
		if(! isset($this->diafan->cache["users_roles"]))
		{
			$this->diafan->cache["users_roles"] = DB::query_fetch_all("SELECT id, [name] FROM {users_role} WHERE trash='0'");
		}
		$rows = $this->diafan->cache["users_roles"];
		$values = array();
		if($this->diafan->value === '1')
		{
			$values[] = 0;
			foreach($rows as $row)
			{
				$values[] = $row["id"];
			}
		}
		elseif($this->diafan->value)
		{
			$values = unserialize($this->diafan->value);
		}
		echo '<input type="checkbox" name="premoderation[]" id="input_premoderation_0" value="0"'.(in_array(0, $values) ? ' checked' : '' ).'> <label for="input_premoderation_0">'.$this->diafan->_('Гость').'</label><br>';
		foreach ($rows as $row)
		{
			echo '<input type="checkbox" name="premoderation[]" id="input_premoderation_'.$row['id'].'" value="'.$row['id'].'"'.(in_array($row['id'], $values) ? ' checked' : '' ).'> <label for="input_premoderation_'.$row['id'].'">'.$row['name'].'</label><br>';
		}
		echo $this->diafan->help().'
			</div>';
	}

	/**
	 * Сохранение поле "Использовать категории"
	 * 
	 * @return void
	 */
	public function save_config_variable_cat()
	{
		$this->diafan->set_query("cat='%d'");
		$this->diafan->set_value(! empty($_POST["cat"]) ? 1 : 0);
		if(! empty($_POST["site_id"]) && ! empty($_POST["cat"]))
		{
			$this->diafan->configmodules("cat", $this->diafan->_admin->module, 0, 0, 1);
		}
	}

	/**
	 * Сохранение поля "Предмодерация объявлений"
	 * 
	 * @return void
	 */
	public function save_config_variable_premoderation()
	{
		$this->diafan->set_query("premoderation='%s'");
		$this->diafan->set_value(! empty($_POST["premoderation"]) ? serialize($_POST["premoderation"]) : '');
	}
}
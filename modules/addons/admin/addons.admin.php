<?php
/**
 * Редактирование дополнений
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
 * Addons_admin
 */
class Addons_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'addons';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'cat_name' => array(
				'type' => 'text',
				'name' => 'Категория',
				'no_save' => true,
				'disabled' => true,
			),		
			'image' => array(
				'type' => 'function',
				'name' => 'Изображение',
				'no_save' => true,
			),
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
				'no_save' => true,
				'disabled' => true,
				'help' => 'Название дополнения для DIAFAN.CMS. Подробнее можно ознакомиться на странице ADDONS.DIAFAN.CMS этого дополнения.',
			),
			'file_rewrite' => array(
				'type' => 'function',
				'name' => 'Страница дополнения в административной части сайта</a>',
				'no_save' => true,
			),
			'text' => array(
				'type' => 'textarea',
				'name' => 'Описание',
				'help' => 'Описание дополнения. Подробнее можно ознакомиться на странице ADDONS.DIAFAN.CMS этого дополнения.',
				'no_save' => true,
				'disabled' => true,
			),
			'install' => array(
				'type' => 'textarea',
				'name' => 'Установка',
				'help' => 'Описание установки дополнения. Подробнее можно ознакомиться на странице ADDONS.DIAFAN.CMS этого дополнения.',
				'no_save' => true,
				'disabled' => true,
			),		
			'title_support' => array(
				'type' => 'title',
				'name' => 'О дополнении и поддержке',
			),
			'link' => array(
				'type' => 'function',
				'name' => 'Страница дополнения на <a href="https://addons.diafan.ru/">ADDONS.DIAFAN.CMS</a>',
				'no_save' => true,
			),
			'author' => array(
				'type' => 'text',
				'name' => 'Разработчик дополнения',
				'no_save' => true,
				'disabled' => true,
				'help' => 'Информация об авторе дополнения для DIAFAN.CMS. Подробнее можно ознакомиться на странице ADDONS.DIAFAN.CMS этого дополнения.',
			),
			'author_link' => array(
				'type' => 'function',
				'name' => 'Страница автора на <a href="https://www.diafan.ru/">DIAFAN.CMS</a>',
				'no_save' => true,
			),
			'title_setup' => array(
				'type' => 'title',
				'name' => 'Установка дополнения',
			),
			'price' => array(
				'type' => 'function',
				'name' => 'Цена',
				'no_save' => true,
			),	
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Установить на сайт',
				'help' => 'Если отмечена, дополнение будет установлено на сайте.',
			),
			'custom' => array(
				'type' => 'text',
				'name' => 'Закреплено за темой сайта',
				'no_save' => true,
				'disabled' => true,
				'help' => 'Если дополнение установлено, то файлы дополнения располагаются именно в этой теме сайта.',
			),
			'modules' => array(
				'type' => 'textarea',
				'name' => 'Определены модули',
				'no_save' => true,
				'disabled' => true,
				'help' => 'Модули, определенные в теме сайта.',
			),
			// 'title_statistics' => array(
			// 	'type' => 'title',
			// 	'name' => 'Общая статистика',
			// ),
			// 'downloaded' => array(
			// 	'type' => 'numtext',
			// 	'name' => 'Количество скачиваний дополнений',
			// 	'no_save' => true,
			// 	'disabled' => true,
			// 	'help' => 'Количество скачиваний дополнений пользователями DIAFAN.CMS. Подробнее можно ознакомиться на странице ADDONS.DIAFAN.CMS этого дополнения.',
			// ),
		),
		//'other_rows' => array (
			
		//),
	);

	/**
	 * @var string часть SQL-запроса - дополнительные столбцы
	 */
	public $fields = ", IFNULL(c.id, 0) as `custom.id`, IFNULL(c.name, '') as `custom.name`";

	/**
	 * @var string часть SQL-запроса - соединение с таблицей
	 */
	public $join = " LEFT JOIN {custom} AS c ON c.id=e.custom_id";

	/**
	 * @var string SQL-условия для списка
	 */
	public $where = " AND 1=1";

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'image' => array(
			'name' => '',
			'sql' => true,
			'class_th' => 'item__th_image ipad',
			'no_important' => true,
		),
		'name' => array(
			'name' => 'Название / Тема',
		),
		'adapt' => array(
			'class_th' => 'item__th_adapt',
		),
		'separator' => array(
			'class_th' => 'item__th_seporator',
		),
		// 'downloaded' => array(
		// 	'name' => '<i class="fa fa-download" title="Количество скачиваний дополнений"></i>',
		// 	'sql' => true,
		// 	'type' => 'numtext',
		// 	'class_th' => 'item__th_download ipad',
		// 	'no_important' => true,
		// ),
		'anons' => array(
			'name' => 'Описание',
			'sql' => true,
			'type' => 'text',
			'no_important' => true,
		),
		//'author' => array(
		//	'name' => 'Автор / Модули',
		//	'sql' => true,
		//	'type' => 'text',
		//	'no_important' => true,
		//),
		'cat_name' => array(
			'name' => 'Название категории',
			'sql' => true,
			'type' => 'none',
		),
		'text' => array(
			'name' => 'Описание',
			'sql' => true,
			'type' => 'none',
		),
		'file_rewrite' => array(
			'name' => 'Страница дополнения в административной части сайта',
			'sql' => true,
			'type' => 'none',
		),
		'link' => array(
			'name' => 'Страница дополнения',
			'sql' => true,
			'type' => 'none',
		),
		'install' => array(
			'name' => 'Установка',
			'sql' => true,
			'type' => 'none',
		),
		'author_link' => array(
			'name' => 'Страница автора',
			'sql' => true,
			'type' => 'none',
		),
		'price' => array(
			'name' => 'Цена дополнения',
			'sql' => true,
			'type' => 'none',
		),
		'price_month' => array(
			'name' => 'Стоимость подписки',
			'sql' => true,
			'type' => 'none',
		),
		'available_subscription' => array(
			'name' => 'Подписка доступна для дополнения',
			'sql' => true,
			'type' => 'none',
		),
		'buy' => array(
			'name' => 'Куплено дополнение',
			'sql' => true,
			'type' => 'none',
		),
		'subscription' => array(
			'name' => 'Подписка оформлена до даты',
			'sql' => true,
			'type' => 'none',
		),
		'auto_subscription' => array(
			'name' => 'Автопродление подписки для дополнения',
			'sql' => true,
			'type' => 'none',
		),
		'custom_id' => array(
			'name' => 'Тема',
			'sql' => true,
			'type' => 'none',
			'no_important' => true,
		),
		'timeedit' => array(
			'name' => 'Дата обновления',
			'sql' => true,
			'type' => 'none',
			'no_important' => true,
		),
		'custom_timeedit' => array(
			'name' => 'Дата обновления темы',
			'sql' => true,
			'type' => 'none',
			'no_important' => true,
		),
		'modules' => array(
			'name' => 'Модули',
			'type' => 'none',
		),
		'action' => array(
			'sql' => false,
			'no_important' => true,
		),
		'actions' => array(
			'view' => true,
			'act' => true,
			'del' => true,
			'buy' => true,
		),
	);

	/**
	 * @var array дополнительные групповые операции
	 */
	public $group_action = array(
		"group_action" => array(
			'name' => "Установить на сайт",
			'module' => 'addons',
			'confirm' => "Внимание! Устанавливаемые дополнения могут изменить конфигурацию сайта.\n\r\n\rПеред выполнением данной операции рекомендуется сделать резервную копию файлов сайта и базы данных.\n\r\n\rПродолжить?",
			// 'question' => "Внимание!\n\r\n\rУстанавливаемые дополнения содержат  инструкции для внесения изменений в базу данных, и/или добавление файлов/модулей, которые могут затронуть существующие файлы/данные. &laquo;Ок&raquo; - внести все изменения автоматически. &laquo;Отмена&raquo; - установить только файлы, изменения в БД необходимо применять вручную.\n\r\n\rПрименить все изменения?",
		),
		"group_no_action" => array(
			'name' => "Деактивировать",
			'module' => 'addons',
			'confirm' => "Напоминание: перед выполнением данной операции рекомендуется сделать резервную копию файлов сайта и базы данных.\n\r\n\rПродолжить?",
			// 'question' => "Внимание!\n\r\n\r Деактивируемые дополнения содержат инструкции для внесения изменений в базу данных. &laquo;Ок&raquo; - внести все изменения в БД автоматически. &laquo;Отмена&raquo; - деактивировать дополнения без изменений в БД.\n\r\n\rПрименить изменения?",
		),
		"group_addon_update" => array(
			'name' => "Обновить",
			'module' => 'addons',
			'confirm' => "Обновление производится путем полной переустановки обновленных файлов и каталогов дополнений.\n\r\n\rПосле обновления необходимо в разделе &laquo;Модули и БД&raquo; во вкладке &laquo;Восстановление БД&raquo; запустить процедуру &laquo;Начать проверку и восстановление базы данных&raquo;.\n\r\n\rПеред выполнением данной операции рекомендуется сделать резервную копию файлов сайта и базы данных.\n\r\n\rПродолжить?",
		),
		"delete" => array(
			'name' => "Удалить",
			'confirm' => "Внимание! Дополнения будут безвозвратно удалены. Перед выполнением данной операции рекомендуется сделать резервную копию файлов сайта и базы данных.\n\r\n\rПродолжить?",
			// 'question' => "Внимание!\n\r\n\rУдаляемые дополнения содержат инструкции для внесения изменений в базу данных, которые могут затронуть существующую информацию на сайте. &laquo;Ок&raquo; - удалить дополнения и внести все изменения в БД автоматически. &laquo;Отмена&raquo; - удалить только файлы дополнений без изменений в БД.\n\r\n\rВы действительно хотите удалить запись?",
		),
	);

	/**
	 * @var array поля для фильтра
	 */
	public $variables_filter = array (
		'act' => array(
			'type' => 'checkbox',
			'name' => 'Все установленные',
		),
		'no_act' => array(
			'type' => 'checkbox',
			'name' => 'Все доступные к установке',
		),
		'hr1' => array(
			'type' => 'hr',
		),
		'update' => array(
			'type' => 'checkbox',
			'name' => 'Доступные обновления',
			'icon' => '<span class="addon_update"><i class="fa fa-puzzle-piece fa-update"></i></span>',
		),
		'hr2' => array(
			'type' => 'hr',
		),
		'name' => array(
			'type' => 'text',
			'name' => 'Искать по названию',
		),
		'cat_name' => array(
			'type' => 'multiselect',
			'name' => 'Искать по категории',
			'select' => array(),
		),
		'author' => array(
			'type' => 'multiselect',
			'name' => 'Искать по автору',
			'select' => array(),
		),
	);

	/**
	 * @var string информационное сообщение
	 */
	private $important_title = '';

	/**
	 * @var integer метка времени
	 */
	static private $timemarker = 0;

	/**
	 * @var object вспомогательный объект модуля
	 */
	private $account = null;

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		parent::__construct($diafan);
		self::$timemarker = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		Custom::inc('modules/account/admin/account.admin.inc.php');
		$this->account = new Account_admin_inc($this->diafan);
	}

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		// определение информационного сообщения
		$this->important_title = '<div class="head-box head-box_warning">
<i class="fa fa-warning"></i>'.$this->diafan->_('Модуль предназначен для автоматической установки дополнений с <a href="https://addons.diafan.ru/" title="ADDONS.DIAFAN" target="_blank">ADDONS.DIAFAN</a> от компании «Диафан». Доступны также дополнения сторонних разработчиков, которые самостоятельно отвечают за их работоспособность и определяют условия поддержки.').' '.$this->diafan->_('Об установке и настройке дополнений указано в блоке "Установка".').' '.$this->diafan->_('Настоятельно рекомендуем перед установкой любых дополнений иметь резервную копию файлов и базы данных сайта.').'</div>';

		// Определение значений фильтра: Искать по категории
		$this->variables_filter["author"]["select"] = array();
		$rows = DB::query_fetch_all("SELECT cat_name AS id, cat_name AS name FROM {%s} WHERE cat_name<>'' GROUP BY cat_name", $this->diafan->table);
		foreach($rows as $row)
		{
			$this->variables_filter["cat_name"]["select"][$row["id"]] = $row["name"];
		}

		// Определение значений фильтра: Искать по автору
		$this->variables_filter["author"]["select"] = array();
		$rows = DB::query_fetch_all("SELECT author AS id, author AS name FROM {%s} WHERE author<>'' GROUP BY author", $this->diafan->table);
		foreach($rows as $row)
		{
			$this->variables_filter["author"]["select"][$row["id"]] = $row["name"];
		}

		if($this->diafan->_route->edit && ! $this->diafan->is_new)
		{
			$values = $this->get_addon_values($this->diafan->_route->edit);
			if(! $values["price"] || ! empty($values["buy"]) || $values["subscription"] >= self::$timemarker)
			{
				$this->diafan->variable('act', 'disabled', false);
			}
			else $this->diafan->variable('act', 'disabled', true);
		}
	}

	/**
	 * Выводит контент модуля
	 *
	 * @return void
	 */
	public function show()
	{
		if(_LANG != $this->diafan->_languages->admin)
		{
			$this->diafan->redirect(BASE_PATH.ADMIN_FOLDER.'/addons/');
		}
		if(! class_exists('ZipArchive'))
		{
			echo '<div class="error">'.$this->diafan->_('Не доступно PHP-расширение ZipArchive. Обратитесь в техническую поддержку хостинга.').'</div>';
		}
		elseif(IS_DEMO)
		{
			echo '<div class="error">'.$this->diafan->_('не доступно в демонстрационном режиме').'</div>';
		}
		else
		{
			echo $this->important_title;

			$this->diafan->_addons->update();

			if(DB::query_result("SELECT COUNT(*) FROM {%s} WHERE custom_id>0", $this->diafan->table) > 0)
			{
				echo '
				<span class="btn btn_small btn_checkrf" id="check_update" action="check_update">
					<span class="fa fa-refresh"></span>
					'.$this->diafan->_('Проверить обновления').'
				</span>';
			}
			else
			{
				$this->diafan->variable_list('modules', 'type', 'none');
			}

			if(is_dir(ABSOLUTE_PATH.$this->diafan->_addons->return_path))
			{
				echo '
				<span class="btn btn_small btn_checkrf" id="delete_return" action="delete_return" title="'.$this->diafan->_('Удалить резервные копии обновленных дополнений').'">
					<span class="fa fa-puzzle-piece fa-trash"></span>
					'.$this->diafan->_('Удалить резервные копии').'
				</span>';
			}

			$this->diafan->list_row();
		}
	}

	/**
	 * Формирует список элементов
	 *
	 * @param integer $id родитель
	 * @param boolean $first_level первый уровень вложенности
	 * @return void
	 */
	public function list_row($id = 0, $first_level = true)
	{
		$name = $this->diafan->_admin->name;
		$this->diafan->_admin->name = $this->diafan->_('Доступные дополнения для DIAFAN.CMS');
		parent::list_row($id, $first_level);
		$this->diafan->_admin->name = $name;
	}

	/**
	 * Формирует SQL-запрос для списка элементов
	 *
	 * @param integer $id родитель
	 * @return array
	 */
	public function sql_query($id)
	{
		$themes = Custom::names();
		$fields = $this->fields;
		$this->fields .= $this->sql_query_act();
		$this->diafan->variable_list('actions', 'act', false);
		$rows = parent::sql_query($id);
		$this->diafan->variable_list('actions', 'act', true);
		$this->fields = $fields;

		foreach($rows as $key => $row)
		{
			$modules = ! empty($row["custom.name"]) ? $this->diafan->_custom->get_modules($row["custom.name"]) : array();
			$rows[$key]["modules"] = '';
			foreach($modules as $module) $rows[$key]["modules"] .= (! empty($rows[$key]["modules"]) ? ', ' : '') . $module["name"];
		}

		return $rows;
	}

	/**
	 * Формирует часть SQL-запроса для поля act списка элементов
	 *
	 * @param integer $id родитель
	 * @return array
	 */
	public function sql_query_act()
	{
		$themes = $this->sql_query_themes();
		$fields = '';
		if(! empty($themes))
		{
			$fields = ", IF (c.id > 0 AND c.name IN (".implode(", ", $themes)."), '1', '0') AS act";
		}
		else
		{
			$fields = ", IF (1 <> 1, '1', '0') AS act";
		}
		return $fields;
	}

	/**
	 * Формирует часть SQL-запрос для списка элементов, отвечающую за сортировку
	 *
	 * @return string
	 */
	public function sql_query_order()
	{
		$order = parent::sql_query_order();
		$order = preg_replace('/^[ ]*ORDER BY[ ]+/i', '', $order, 1);
		$themes = $this->sql_query_themes();
		$order_field = '';
		if(! empty($themes))
		{
			$order_field = ", FIELD(c.name, ".implode(", ", $themes).") ASC";
		}
		return " ORDER BY "
		." act DESC".$order_field.", `custom.id` DESC, e.buy DESC, e.subscription DESC, e.auto_subscription DESC, e.sort DESC, e.addon_id DESC"
		.(! empty($order) ? ", ".$order : "");
	}

	/**
	 * Выводит панель групповых операций
	 *
	 * @param boolean $show_filter выводить кнопку "Фильтровать"
	 * @return void
	 */
	public function group_action_panel($show_filter = false)
	{
		$act = $this->diafan->variable_list('actions', 'act');
		$this->diafan->variable_list('actions', 'act', false);
		$del = $this->diafan->variable_list('actions', 'del');
		$this->diafan->variable_list('actions', 'del', false);
		echo parent::group_action_panel($show_filter);
		$this->diafan->variable_list('actions', 'act', $act);
		$this->diafan->variable_list('actions', 'del', $del);
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
		$html = '<div class="image'.($var["class"] ? ' '.$var["class"] : '').' ipad">';
		if (! empty($row["image"]))
		{
			if(empty($row["custom.id"]) || ! $row["file_rewrite"])
			{
				$html .= '<a href="'.$this->diafan->get_base_link($row).'"><img src="'.$row["image"].'" border="0" alt=""></a>';
			}
			else
			{
				$html .= '<a href="'.BASE_PATH.ADMIN_FOLDER.'/'.$row["file_rewrite"].ROUTE_END.'"><img src="'.$row["image"].'" border="0" alt=""></a>';
			}
		}
		$html .= '</div>';

		return $html;
	}

	/**
	 * Формирует название в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_name($row, $var)
	{
		if (! empty($row["name"]))
		{

			$row["name"] = htmlspecialchars_decode($row["name"]);
		}

		$text = '<div class="name'.(! empty($var["class"]) ? ' '.$var["class"] : '').'" id="'.$row['id'].'">';
		$name  = '';
		if(! empty($var["variable"]))
		{
			$name = strip_tags($row[$var["variable"]]);
		}
		if(! empty($var["text"]))
		{
			$name = sprintf($this->diafan->_($var["text"]), $name);
		}
		if (! $name)
		{
			if(! empty($row["name"]))
			{
				$name = $row["name"];
			}
			else
			{
				$name = $row['id'];
			}
		}

		if(empty($row["custom.id"]) || ! $row["file_rewrite"])
		{
			$text .= '<a href="';
			$text .= $this->diafan->get_base_link($row);
			$text .= '" title="'.$this->diafan->_('Подробнее о дополнении перед установкой').'">'.$name.'</a>';
		}
		else
		{
			$text .= '<a href="';
			$text .= BASE_PATH.ADMIN_FOLDER.'/'.$row["file_rewrite"].ROUTE_END;
			$text .= '" title="'.$this->diafan->_('Подробнее о дополнении перед установкой').'">'.$name.'</a>';
		}
		$text .= $this->diafan->list_variable_menu($row, array());
		$text .= $this->diafan->list_variable_parent($row, array());
		$text .= $this->diafan->list_variable_date_period($row, array());
		if (! empty($row["cat_name"]))
		{
			$text .= ' <div class="categories"><i class="fa fa-puzzle-piece fa-shop"></i> '.$row["cat_name"].'</div>';
		}
		if (! empty($row["link"]))
		{
			$text .= ' <div class="categories"><a href="'.$row["link"].'" target="_blank">
				<i class="fa fa-link"></i> '.$this->diafan->_('Посмотреть на addons.diafan.ru').'</a></div>';
		}
		$text .= (! empty($row["custom.name"]) ? '<div class="categories" title="'.$this->diafan->_('Тема сайта').'"><i class="fa fa-puzzle-piece fa-custom"></i> <a href="'.BASE_PATH.ADMIN_FOLDER.'/custom/'.'" title="'.$this->diafan->_('Тема').'">'.$row["custom.name"].'</a></div>' : '');
		$text .= '</div>';
		return $text;
	}

	/**
	 * Формирует описание в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_anons($row, $var)
	{
		if(! empty($var["type"]) && $var["type"] == 'none')
		{
			return '';
		}

		$html = '<div class="text'.($var["class"] ? ' '.$var["class"] : '').' ipad">';
		if(! empty($row["anons"]))
		{
			$html .= $row["anons"];
		}
		elseif(! empty($row["text"]))
		{
			$html .= $row["text"];
		}
		$html .= '</div>';

		return $html;
	}

	/**
	 * Формирует описание в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_modules($row, $var)
	{
		if(! empty($var["type"]) && $var["type"] == 'none')
		{
			return '';
		}

		$html = '<div class="text'.($var["class"] ? ' '.$var["class"] : '').' ipad">';
		if (! empty($row["modules"]))
		{
			$html .= $row["modules"];
		}
		$html .= '</div>';

		return $html;
	}

	/**
	 * Формирует указание на автора в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_author($row, $var)
	{
		/*$html = '<div class="author'.($var["class"] ? ' '.$var["class"] : '').' ipad">';
		if (! empty($row["author"]))
		{

			$html .= (! empty($row["author_link"]) ? '<a href="'.$row["author_link"].'" title="'.$this->diafan->_("Автор дополнения").'">' : '').$row["author"].(! empty($row["author_link"]) ? '</a>' : '');
		}
		$html .= (! empty($row["modules"]) ? '<div class="categories"><a href="'.BASE_PATH.ADMIN_FOLDER.'/service/'.'" title="'.$this->diafan->_("Модули").'">'.$row["modules"].'</a></div>' : '');
		$html .= '</div>';

		return $html;*/
	}

	/**
	 * Выводит иконку "Обновить дополнение" в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_action($row, $var)
	{
		$text = '
				<div class="item__labels'.($var["class"] ? ' '.$var["class"] : '').'">
					&nbsp;
				</div>';

		//update
		if ($this->diafan->variable_list('actions', 'act') && $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite)
			&& $this->diafan->check_action($row, 'act'))
		{
			if(! empty($row["custom_timeedit"]) && ! empty($row["timeedit"]) && $row["timeedit"] != $row["custom_timeedit"])
			{
				$attr = '" confirm="'.$this->diafan->_("Обновление производится путем полной переустановки обновленных файлов и каталогов дополнений.\n\r\n\rПосле обновления может понадобиться в разделе &laquo;Модули и БД&raquo; во вкладке &laquo;Восстановление БД&raquo; запустить процедуру &laquo;Начать проверку и восстановление базы данных&raquo;.\n\r\n\rПеред выполнением данной операции рекомендуется сделать резервную копию файлов сайта и базы данных.\n\r\n\rПродолжить?").'"';

				$text = '
				<div class="item__labels'.($var["class"] ? ' '.$var["class"] : '').'">
					<a href="javascript:void(0)" action="group_addon_update" class="addon_update" '.$attr.'><i class="fa fa-puzzle-piece fa-update"></i></a> '.$this->diafan->_('Обновить').'
				</div>';
			}
		}

		return $text;
	}

	/**
	 * Выводит кнопки действий над элементом
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_actions($row, $var)
	{
		$text = '<div class="action">';

		// buy
		if($this->diafan->variable_list('actions', 'buy')
			// && $this->diafan->_users->roles('buy', $this->diafan->_admin->rewrite)
			&& $this->diafan->check_action($row, 'buy')
			&& empty($row["custom.id"])
			&& $row["price"] && empty($row["buy"]))
		{
			if($this->account->is_auth())
			{
				$text .= '
					<a href="javascript:void(0)" confirm="'
					.$this->diafan->_('ВНИМАНИЕ! При покупке дополнения будут списаны денежные средства с Вашего счета в личном кабинете. Продолжить?')
					.'" action="buy" module="addons" class="btn btn_blue btn_small btn_buy action">'
					.$this->diafan->_('Купить').': '
					.'<b>'.number_format($row["price"], 0, ',', ' ').'</b>'.' <rub>&#8381;</rub>'
					.'</a>';
			}
			else
			{
				$text .= '
					<a href="'.BASE_PATH.ADMIN_FOLDER.'/'.'account'.'/'.'" class="btn btn_blue btn_small btn_buy">'
					.$this->diafan->_('Купить').': '
					.'<b>'.number_format($row["price"], 0, ',', ' ').'</b>'.' <rub>&#8381;</rub>'
					.'</a>';
			}

			if($row["price_month"] && $row["available_subscription"])
			{
				$caption = $this->diafan->_('Подписка');
				if($row["subscription"] >= self::$timemarker)
				{
					$caption = false; // $caption = $this->diafan->_('Продлить');
				}
				if($caption)
				{
					if($this->account->is_auth())
					{
						$text .= ' '
						.'<a href="javascript:void(0)" confirm="'
						.$this->diafan->_('ВНИМАНИЕ! При подписке на использование дополнения будут списаны денежные средства с Вашего счета в личном кабинете в сумме месячной стоимости сразу и далее ежемесячно. Продолжить?')
						.'" action="subscription" module="addons" class="btn btn_blue btn_small btn_subscription action">'
						.$caption.': '
						.'<b>'.number_format($row["price_month"], 0, ',', ' ').'</b>'.' <rub>&#8381;</rub>'
						.'</a>';
					}
					else
					{
						$text .= ' '
						.'<a href="'.BASE_PATH.ADMIN_FOLDER.'/'.'account'.'/'.'" class="btn btn_blue btn_small btn_subscription">'
						.$caption.': '
						.'<b>'.number_format($row["price_month"], 0, ',', ' ').'</b>'.' <rub>&#8381;</rub>'
						.'</a>';
					}
				}
			}
		}

		//act
		if($this->diafan->variable_list('actions', 'act')
		&& $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite)
		&& $this->diafan->check_action($row, 'act')
		&& (! $row["price"] || ! empty($row["buy"]) || $row["subscription"] >= self::$timemarker))
		{
			$names = array();
			if(! empty($row["custom.id"]) && ! empty($row["custom.name"]))
			{
				$modules = $this->diafan->_custom->get_modules($row["custom.name"]);
				if(! empty($modules))
				{
					foreach($modules as $module)
					{
						if(empty($module["installed"]) || empty($module["name"]))
						{
							continue;
						}
						$names[] = $module["name"];
					}
				}
			}

			$subscription = '';
			if($row["subscription"] >= self::$timemarker)
			{
				$subscription .= '<span class="subscription">'.$this->diafan->_('Подписка до %s', date("d.m.Y", $row["subscription"])).'</span>';
				if($row["auto_subscription"])
				{
					$subscription .= ' <span class="subscription">('.$this->diafan->_('автопродление').').</span>';
				}
				if($this->account->is_auth())
				{
					$subscription .=  ' '
					.'<a href="javascript:void(0)" confirm="'
					.($row["auto_subscription"]
						? $this->diafan->_('ВНИМАНИЕ! Подписка на дополнение будет отменена. Продолжить?')
						: $this->diafan->_('ВНИМАНИЕ! Подписка на дополнение будет возобновлена. Продолжить?'))
					.'" action="'.($row["auto_subscription"] ? 'no_subscription' : 'subscription').'" module="addons" class="btn btn_blue btn_small btn_subscription action">'
					.($row["auto_subscription"]
						? $this->diafan->_('Отменить подписку')
						: $this->diafan->_('Возобновить подписку'))
					.'</a>';
				}
				else
				{
					$subscription .=  ' '
					.'<a href="'.BASE_PATH.ADMIN_FOLDER.'/'.'account'.'/'.'" class="btn btn_blue btn_small btn_subscription">'
					.($row["auto_subscription"]
						? $this->diafan->_('Отменить подписку')
						: $this->diafan->_('Возобновить подписку'))
					.'</a>';
				}
			}
			$text .= ' '.($subscription ? $subscription.' ' : '')
			.'<a href="javascript:void(0)" title="'.($row["act"] ? $this->diafan->_('Сделать неактивным') : $this->diafan->_('Установить на сайте')).'" action="'.($row["act"] ? 'un' : '' ).'block" class="action '.($row["act"] ? '' : 'switch' ).'" confirm="'
			.$this->diafan->_("Внимание! Перед выполнением данной операции рекомендуется сделать резервную копию файлов сайта и базы данных.\n\r\n\rПродолжить?").'"'
			// .' question="'
			// .$this->diafan->_("Внимание!\n\r\n\rДополнение%s содержит инструкции для внесения изменений в базу данных, которые могут затронуть существующую информацию на сайте. &laquo;Ок&raquo; - внести все изменения в БД автоматически. &laquo;Отмена&raquo; - затронуть только файлы без изменений в БД.\n\r\n\rПрименить изменения?", (! empty($names) ? ' ('.implode(',', $names).')' : '')).'"'
			.'>
				<i class="fa fa-toggle-on"></i> '.($row["act"] ? $this->diafan->_('Выключить') : $this->diafan->_('Установить') ).'
			</a>';
		}

		//del
		if($this->diafan->variable_list('actions', 'del')
		&& $this->diafan->_users->roles('del', $this->diafan->_admin->rewrite)
		&& $this->diafan->check_action($row, 'del')
		&& ! empty($row["custom.id"]))
		{
			// Если активна подписка, то не выводим кнопку удаления
			if($row["subscription"] < self::$timemarker)
			{
				$text .= ' '
				.'<a href="javascript:void(0)" title="'.$this->diafan->_('Удалить').'"'.' confirm="'
				.(!empty( $row["count_children"] ) ? $this->diafan->_('ВНИМАНИЕ! Пункт содержит вложенность! ') : '')
				.($this->diafan->config("category") ? $this->diafan->_('При удалении категории удаляются все принадлежащие ей элементы. ') : '')
				.$this->diafan->_("Внимание! Дополнение будет безвозвратно удалено. \n\r\n\rПродолжить?").'"'
				// .' question="'
				// .$this->diafan->_("Внимание!\n\r\n\rУдаляемое дополнение содержит инструкции для внесения изменений в базу данных, которые могут затронуть существующую информацию на сайте (%s). &laquo;Ок&raquo; - удалить дополнение и внести все изменения в БД автоматически. &laquo;Отмена&raquo; - удалить только файлы дополнения без изменений в БД.\n\r\n\rВы действительно хотите удалить запись?", $row["custom.name"]).'"'
				.' action="delete" class="action remove">
					<i class="fa fa-times-circle"></i> '.$this->diafan->_('Удалить').'
				</a>';
			}
		}

		$text .= '</div>';

		return $text;
	}

	/**
	 * Устанавливает/блокирует элемент
	 *
	 * @return void
	 */
	public function act()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->diafan->redirect(URL);
			return;
		}

		//проверка прав пользователя на активацию/блокирование
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->diafan->redirect(URL);
		}

		$id = $this->diafan->filter($_POST, 'int', 'id', 0);
		if(! empty($id) && ! empty($_POST["action"]) && ($_POST["action"] == "block" || $_POST["action"] == "unblock"))
		{
			$act = $_POST["action"] == "block" ? 'install' : 'uninstall';
			$question = true; //$question = ! empty($_POST["question"]) ? true : false;
			$result = $this->diafan->_addons->$act($id, $question);
			if($result === true)
			{
				$this->diafan->set_one_shot(
					'<div class="ok">'
					.(count($ids) > 1
						? ($act == 'install' ? $this->diafan->_('Дополнения установлены.') : $this->diafan->_('Дополнения деинсталлированы.'))
						: ($act == 'install' ? $this->diafan->_('Дополнение установлено.') : $this->diafan->_('Дополнение деинсталлировано.'))
						)
					.'</div>'
				);
			}
			else
			{
				$message = is_array($result) ? implode("<br>", $result) : (is_string($result) ? $result : '');
				$this->diafan->set_one_shot(
					'<div class="error">'
					.(count($ids) > 1
						? ($act == 'install' ? $this->diafan->_('Некоторые дополнения не установлены.') : $this->diafan->_('Некоторые дополнения не деинсталлированы.'))
						: ($act == 'install' ? $this->diafan->_('Дополнение не установлено.') : $this->diafan->_('Дополнение не деинсталлировано.'))
						)
					.($message ? "<br>".$message : '')
					.'</div>'
				);
			}
		}

		$this->diafan->redirect(URL.$this->diafan->get_nav);
	}

	/**
	 * Удаляет элемент
	 *
	 * @return void
	 */
	public function del()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->diafan->redirect(URL);
			return;
		}

		//проверка прав пользователя на удаление
		if (! $this->diafan->_users->roles('del', $this->diafan->_admin->rewrite))
		{
			$this->diafan->redirect(URL);
			return;
		}

		if (! empty($_POST["id"]))
		{
			$ids = array($_POST["id"]);
		}
		else
		{
			$ids = $_POST["ids"];
		}
		foreach($ids as $id)
		{
			$id = intval($id);
			if($id)
			{
				$del_ids[] = $id;
			}
		}
		if(! empty($del_ids))
		{
			$question = true; // $question = ! empty($_POST["question"]) ? true : false;
			$this->diafan->_addons->delete($del_ids, $question);
		}

		$this->diafan->redirect(URL.$this->diafan->get_nav);
	}

	/**
	 * Поиск по полю "Все установленные"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function save_filter_variable_act($row)
	{
		if (empty($_GET["filter_act"]) || ! empty($_GET["filter_no_act"]))
		{
			if(! empty($_GET["filter_act"]) && ! empty($_GET["filter_no_act"])) return 1;
			return;
		}

		$themes = $this->sql_query_themes();
		if(! empty($themes))
		{
			$this->diafan->where .= " AND c.id IS NOT NULL AND c.name IN (".implode(", ", $themes).")";
		}
		else
		{
			$this->diafan->where .= " AND 1<>1";
		}
		$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' ).'filter_act=1';
		return 1;
	}

	/**
	 * Поиск по полю "Все установленные"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function save_filter_variable_no_act($row)
	{
		if (empty($_GET["filter_no_act"]) || ! empty($_GET["filter_act"]))
		{
			if(! empty($_GET["filter_no_act"]) && ! empty($_GET["filter_act"])) return 1;
			return;
		}

		$themes = $this->sql_query_themes();
		if(! empty($themes))
		{
			$this->diafan->where .= " AND (c.id IS NULL OR c.name NOT IN (".implode(", ", $themes)."))";
		}
		else
		{
			$this->diafan->where .= " AND 1=1";
		}
		$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' ).'filter_no_act=1';
		return 1;
	}

	/**
	 * Поиск по полю "Все установленные"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function save_filter_variable_update($row)
	{
		if (empty($_GET["filter_update"]))
		{
			return;
		}

		$themes = $this->sql_query_themes();
		if(! empty($themes))
		{
			$this->diafan->where .= " AND c.id IS NOT NULL AND c.name IN (".implode(", ", $themes).")";
			$this->diafan->where .= " AND timeedit>custom_timeedit";
		}
		else
		{
			$this->diafan->where .= " AND 1<>1";
		}
		$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' ).'filter_update=1';
		return 1;
	}

	/**
	 * Генерирует форму редактирования/добавления элемента
	 *
	 * @return void
	 */
	public function edit()
	{
		if(_LANG != $this->diafan->_languages->admin)
		{
			$this->diafan->redirect(BASE_PATH.ADMIN_FOLDER.'/addons/');
		}
		if(! class_exists('ZipArchive'))
		{
			echo '<div class="error">'.$this->diafan->_('Не доступно PHP-расширение ZipArchive. Обратитесь в техническую поддержку хостинга.').'</div>';
		}
		elseif(IS_DEMO)
		{
			echo '<div class="error">'.$this->diafan->_('не доступно в демонстрационном режиме').'</div>';
		}
		else
		{
			echo $this->important_title;

			echo parent::edit();
		}
	}

	/**
	 * Редактирование поля "Изображение"
	 *
	 * @return void
	 */
	public function edit_variable_image()
	{
		echo '<div class="unit" id="'.$this->diafan->key.'">';
			echo '<div class="image">';
		if (! empty($this->diafan->value))
		{
				echo '<img src="'.$this->diafan->value.'" border="0" alt="">';
		}
			echo '</div>';
		echo '</div>';
	}
	
	/**
	 * Вывод поля "Название"
	 *
	 * @return void
	 */
	public function edit_variable_name()
	{
		if (! empty($this->diafan->value))
		{		
			echo '<div class="unit" id="'.$this->diafan->key.'">';
			echo '<div class="infofield">';
				echo '<h2>'.$this->diafan->value.'</h2>';
			echo '</div>';
			echo '</div>';
		}
	}
	
	/**
	 * Вывод поля "Название категории"
	 *
	 * @return void
	 */
	public function edit_variable_cat_name()
	{
		if (! empty($this->diafan->value))
		{
			echo '<div class="unit" id="'.$this->diafan->key.'">';
			echo '<div class="infofield">';			
				echo 'Тип дополнения: '.$this->diafan->value.'';
			echo '</div>';
			echo '</div>';
		}
	}

	/**
	 * Редактирование поля "Цена"
	 * @return void
	 */
	public function edit_variable_price()
	{
		$values = $this->get_addon_values($this->diafan->id);
		if($values["price"] && empty($values["buy"]))
		{
			echo '
			<div class="unit" id="'.$this->diafan->key.'">
				<div class="infofield">'.$this->diafan->variable($this->diafan->key, 'name').'</div>';

			echo '
				<a href="javascript:void(0)" title="'.$this->diafan->_('Купить дополнение').'" confirm="'
				.$this->diafan->_('ВНИМАНИЕ! При покупке дополнения будут списаны денежные средства с Вашего счета в личном кабинете. Продолжить?')
				.'" action="buy" module="addons" class="btn btn_blue btn_small btn_buy action">'
				.$this->diafan->_('Купить').': '
				.'<b>'.number_format($values["price"], 0, ',', ' ').'</b>'.' <rub>&#8381;</rub>'
				.'</a>';

			if($values["price_month"] && $values["available_subscription"])
			{
				$caption = $this->diafan->_('Подписка');
				if($values["subscription"] >= self::$timemarker)
				{
					if($values["auto_subscription"]) $caption = $this->diafan->_('Отменить подписку');
					else $caption = $this->diafan->_('Возобновить подписку');
				}
				echo '
				<a href="javascript:void(0)" title="'.$this->diafan->_('Оформить подписку на дополнение').'" confirm="'
				.$this->diafan->_('ВНИМАНИЕ! При подписке на использование дополнения будут списаны денежные средства с Вашего счета в личном кабинете. Продолжить?')
				.'" action="'
				.($values["subscription"] >= self::$timemarker && $values["auto_subscription"] ? 'no_subscription' : 'subscription')
				.'" module="addons" class="btn btn_blue btn_small btn_subscription action">'
				.$caption
				.($values["subscription"] < self::$timemarker
					? ': '.'<b>'.number_format($values["price_month"], 0, ',', ' ').'</b>'.' <rub>&#8381;</rub>'
					: '')
				.'</a>';
			}
			echo '
			</div>';
		}

		if($values["price"] && empty($values["buy"]) && $values["subscription"] >= self::$timemarker)
		{
			echo '<span class="subscription">'.$this->diafan->_('%sПодписка оформлена%s до %s', '<b>', '</b>', date("d.m.Y", $values["subscription"])).'</span>';
			if($values["auto_subscription"])
			{
				echo ' <span class="subscription">('.$this->diafan->_('автопродление').').</span>';
			}
		}
	}

	/**
	 * Редактирование поля "Страница дополнения на ADDONS.DIAFAN.CMS"
	 *
	 * @return void
	 */
	public function edit_variable_link()
	{
		$link = $this->diafan->values("link");
		if(! empty($link))
		{
			echo '<div class="infofield"><a class="" href="'.$link.'" target="_blank">'.$this->diafan->_('Посмотреть это дополнение на ADDONS.DIAFAN').'</a></div>';
		}
	}
	
	/**
	 * Вывод поля "Описание"
	 *
	 * @return void
	 */
	public function edit_variable_text()
	{
		if (! empty($this->diafan->value))
		{
			echo '<div class="unit" id="'.$this->diafan->key.'">';
				echo $this->diafan->value;
			echo '</div>';
		}
	}
	
	/**
	 * Вывод поля "УСтановка"
	 *
	 * @return void
	 */
	public function edit_variable_install()
	{
		if (! empty($this->diafan->value))
		{
			echo '<div class="unit" id="'.$this->diafan->key.'">';
			echo '<div class="infofield">'.$this->diafan->_('Как установить дополнение вручную').'</div>';
				echo $this->diafan->value;
			echo '</div>';	
		}
	}

	/**
	 * Редактирование поля "Страница автора на DIAFAN.CMS"
	 *
	 * @return void
	 */
	public function edit_variable_author()
	{
		$link = $this->diafan->values("author_link");
		if(! empty($link))
		{
			echo '<div class="infofield">'.$this->diafan->variable($this->diafan->key, 'name').'</div>';
			echo '<a class="" target="_blank" href="'.$link.'">'.$this->diafan->values("author").'</a>';
			echo "\n";
		}
	}

	/**
	 * Редактирование поля "Закреплено за темой сайта"
	 *
	 * @return void
	 */
	public function edit_variable_custom()
	{
		$values = $this->get_addon_values($this->diafan->id);
		if(empty($values["custom.id"]))
		{
			return;
		}
		$key = $this->diafan->key.(! $this->diafan->config("config") && $this->diafan->variable_multilang($this->diafan->key) ? _LANG : '' );
		$key .= '.name';
		$this->diafan->value = ! empty($values[$key]) ? $values[$key] : false;
		if($this->diafan->value === false)
		{
			$this->diafan->value = '';
		}

		$this->diafan->show_table_tr(
				$this->diafan->variable($this->diafan->key, 'type'),
				$this->diafan->key,
				$this->diafan->value,
				$this->diafan->variable_name(),
				$this->diafan->help(),
				$this->diafan->variable_disabled(),
				$this->diafan->variable('', 'maxlength'),
				$this->diafan->variable('', 'select'),
				$this->diafan->variable('', 'select_db'),
				$this->diafan->variable('', 'depend'),
				$this->diafan->variable('', 'attr')
			);

		unset($values);
	}

	/**
	 * Редактирование поля "Модули темы сайта"
	 *
	 * @return void
	 */
	public function edit_variable_modules()
	{
		$values = $this->get_addon_values($this->diafan->id);
		if(empty($values["custom.id"]) || empty($values["custom.name"]))
		{
			return;
		}
		$modules = ! empty($values["custom.name"]) ? $this->diafan->_custom->get_modules($values["custom.name"]) : array();
		if(empty($modules))
		{
			return;
		}
		$this->diafan->value = '';
		foreach($modules as $module) $this->diafan->value .= (! empty($this->diafan->value) ? ', ' : '') . $module["name"];

		$this->diafan->show_table_tr(
				$this->diafan->variable($this->diafan->key, 'type'),
				$this->diafan->key,
				$this->diafan->value,
				$this->diafan->variable_name(),
				$this->diafan->help(),
				$this->diafan->variable_disabled(),
				$this->diafan->variable('', 'maxlength'),
				$this->diafan->variable('', 'select'),
				$this->diafan->variable('', 'select_db'),
				$this->diafan->variable('', 'depend'),
				$this->diafan->variable('', 'attr')
			);

		unset($values);
	}

	/**
	 * Редактирование поля "Установить на сайте"
	 *
	 * @return void
	 */
	public function edit_variable_act()
	{
		$values = $this->get_addon_values($this->diafan->id);
		$key = $this->diafan->key.(! $this->diafan->config("config") && $this->diafan->variable_multilang($this->diafan->key) ? _LANG : '' );
		$this->diafan->value = ! empty($values[$key]) ? $values[$key] : false;
		if($this->diafan->value === false)
		{
			$this->diafan->value = '';
		}

		$this->diafan->show_table_tr(
				$this->diafan->variable($this->diafan->key, 'type'),
				$this->diafan->key,
				$this->diafan->value,
				$this->diafan->variable_name(),
				$this->diafan->help(),
				$this->diafan->variable_disabled(),
				$this->diafan->variable('', 'maxlength'),
				$this->diafan->variable('', 'select'),
				$this->diafan->variable('', 'select_db'),
				$this->diafan->variable('', 'depend'),
				$this->diafan->variable('', 'attr')
			);

		if($this->diafan->variable_disabled('act'))
		{
			if(! (! $values["price"] || ! empty($values["buy"]) || $values["subscription"] >= self::$timemarker))
			{
				echo '<p><i class="fa fa-warning"></i> '.$this->diafan->_('Установка платного дополнения будет разблокирована после оформления его покупки.').'</p>';
			}
		}
		unset($values);
	}

	/**
	 * Проверка поля "Установить на сайте"
	 *
	 * @return void
	 */
	public function validate_variable_act()
	{
		// if(! isset($_POST["question"]))
		// {
		// 	$id = $this->diafan->filter($_POST, 'int', 'id', 0);
		// 	$act = ! empty($_POST["act"]) ? true : false;
		// 	if(! empty($id))
		// 	{
		// 		$values = $this->get_addon_values($id);
		// 		$values["act"] = ! empty($values["act"]) ? true : false;
		// 		if($act != $values["act"])
		// 		{
		// 			$names = array();
		// 			if(! empty($values["custom.id"]) && ! empty($values["custom.name"]))
		// 			{
		// 				$modules = $this->diafan->_custom->get_modules($values["custom.name"]);
		// 				if(! empty($modules))
		// 				{
		// 					foreach($modules as $module)
		// 					{
		// 						if(empty($module["installed"]) || empty($module["name"]))
		// 						{
		// 							continue;
		// 						}
		// 						$names[] = $module["name"];
		// 					}
		// 				}
		// 			}
		// 			$message = $this->diafan->_("Внимание! Перед выполнением данной операции рекомендуется сделать резервную копию файлов сайта и базы данных.\n\r\n\rПродолжить?");
		// 			$this->diafan->set_error("confirm", $message);
		//
		// 			$message = $this->diafan->_("Внимание! Дополнение содержит инструкции для изменений базы данных и модулей %s.\n\r\n\rПрименить изменения?", (! empty($names) ? ': '.implode(',', $names) : ''));
		// 			$this->diafan->set_error("question", $message);
		// 		}
		// 	}
		// }
	}

	/**
	 * Сохранение поля "Установить на сайте"
	 * @return void
	 */
	public function save_variable_act()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->diafan->redirect(URL);
			return;
		}

		//проверка прав пользователя на активацию/блокирование
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->diafan->redirect(URL);
			return;
		}

		$id = $this->diafan->filter($_POST, 'int', 'id', 0);
		$act = ! empty($_POST["act"]) ? true : false;
		$question = true; // $question = ! empty($_POST["question"]) ? true : false;
		if(! empty($id))
		{
			$values = $this->get_addon_values($id);
			$values["act"] = ! empty($values["act"]) ? true : false;
			if($act != $values["act"])
			{
				$act = $act ? 'install' : 'uninstall';
				$result = $this->diafan->_addons->$act($id, $question);
				if($result === true)
				{
					$this->diafan->set_one_shot(
						'<div class="ok">'
						.(count($ids) > 1
							? $this->diafan->_('Дополнения установлены.')
							: $this->diafan->_('Дополнение установлено.'))
						.'</div>'
					);
				}
				else
				{
					$message = is_array($result) ? implode("<br>", $result) : (is_string($result) ? $result : '');
					$this->diafan->set_one_shot(
						'<div class="error">'
						.(count($ids) > 1
							? $this->diafan->_('Некоторые дополнения не установлены.')
							: $this->diafan->_('Дополнение не установлено.'))
						.($message ? "<br>".$message : '')
						.'</div>'
					);
				}
			}
		}
	}

	/**
	 * Формирует часть SQL-запроса включающий активные темы
	 *
	 * @param integer $id родитель
	 * @return array
	 */
	private function sql_query_themes()
	{
		if(! isset($this->cache["prepare"]["themes"]))
		{
			$this->cache["prepare"]["themes"] = Custom::names();
			foreach($this->cache["prepare"]["themes"] as $key => $theme)
			{
				$this->cache["prepare"]["themes"][$key] = "'".$theme."'";
			}
		}
		return $this->cache["prepare"]["themes"];
	}

	/**
	 * Получает значение полей
	 *
	 * @param integer $id идентификатор
	 * @return mixed
	 */
	public function get_addon_values($id)
	{
		$id = ($id ?: ($this->diafan->id ?: ($this->diafan->_route->edit ?: 0)));
		if(! $id)
		{
			return array();
		}
		if(! isset($this->cache["prepare"]["values"][$id]))
		{
			$themes = Custom::names();
			$this->cache["prepare"]["values"][$id] = DB::query_fetch_array("SELECT e.*".$this->fields.$this->sql_query_act()." FROM {".$this->diafan->table."} as e".$this->join." WHERE e.id=%d"
				.($this->diafan->variable_list('actions', 'trash') ? " AND trash='0'" : '' )." LIMIT 1",
				$id
			);
		}
		return $this->cache["prepare"]["values"][$id];
	}
}

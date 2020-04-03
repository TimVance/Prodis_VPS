<?php
/**
 * Администрирование списока описаний импорта/экспорта записей базы данных
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
 * Service_admin_express_fields_category
 */
class Service_admin_express_fields_category extends Frame_admin
{
	/**
	 * @var string имя основного класса
	 */
	const CLASS_NAME = 'Service_admin_express';

	/**
	 * @var string базовый URL
	 */
	private $url = '';

	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'service_express_fields_category';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'hr1' => array(
				'type' => 'title',
				'name' => 'Основные',
			),
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
				'help' => 'Краткое описание файла импорта (например, «Импорт товаров», «Импорт цен» и т. д.).',
			),
			'module_name' => array(
				'type' => 'select',
				'name' => 'Модуль',
				'select' => array(),
			),
			'type' => array(
				'type' => 'select',
				'name' => 'Что импортируем?',
				'select' => array(
					'element' => 'Элементы (товары, страницы и пр.)',
					'category' => 'Категории',
					'brand' => 'Производители',
				),
				'type_cat' => array(
					'element' => '',
					'category' => '',
					'brand' => 'shop',
				),
				'help' => 'Тип данных модуля, для которой будет производится импорт.',
			),
			'site_id' => array(
				'type' => 'function',
				'name' => 'В какой раздел сайта?',
				'help' => 'Страница сайта с прикрепленным модулем, для которой будет производится импорт.',
			),
			'cat_id' => array(
				'type' => 'function',
				'name' => 'Импортируем в конкретную категорию?',
				'help' => 'Возможность ограничить импорт/экспорт одной категорией в модуле.',
			),
			'menu_cat_id' => array(
				'type' => 'function',
				'name' => 'Создать пункты меню для категорий?',
				'help' => 'Если отметить, в [модуле «Меню на сайте»](http://www.diafan.ru/dokument/full-manual/sysmodules/menu/) будут созданы пунктыменю со ссылкой на категории.',
				'depend' => 'type=element',
			),
			'delete_items' => array(
				'type' => 'checkbox',
				'name' => 'Удалить всё в модуле перед импортом',
				'help' => 'Если Вы загружаете список новых записей или категорий, и то, что уже есть на сайте не нужно, следует отметить эту опцию. На сайт импортируются новые записи из файла, а все уже существующие записи или категории будут удалены, за исключением тех, что будут обновлены (определяется по идентификатору).',
			),
			'add_new_items' => array(
				'type' => 'checkbox',
				'name' => 'Добавить только новые записи',
				'help' => 'На сайт импортируются только новые записи из файла, а все уже существующие записи или категории будут пропущены (определяется по идентификатору).',
			),
			'act_items' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать записи на сайте',
				'help' => 'Если отмечено, то все импортируемые записи будут опубликованы на сайте.',
			),
			'count_part' => array(
				'type' => 'numtext',
				'name' => 'Количество обрабатываемых строк за проход',
				'help' => 'Время работы скрипта на большинстве хостингов ограничено, из-за чего скрипт может не успеть обработать весь файл за одну итерацию, если он объемный. Поэтому файл обрабатывается частями, а величину итерации можно задать этим параметром.',
				'default' => 20
			),
			'sub_delimiter' => array(
				'type' => 'text',
				'name' => 'Разделитель данных внутри поля',
				'help' => 'В некоторых полях (ячейках) может быть несколько данных (например, значение характеристики с типом «список с выбором нескольких значений» или несколько имен изображений для одного товара). В этом случае данные должны быть разделены этим разделителем.',
				'default' => '|',
			),
			'header' => array(
				'type' => 'checkbox',
				'name' => 'Не учитывать первую строку в файле',
				'help' => 'Если отмечено, то добавляется описание полей в первой строке файла при экспорте, а при импорте первая строка игнорируется.',
				'default' => '1',
			),
			'hr2' => 'hr',
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Редактирование порядка следования категории в списке. Поле доступно для редактирования только для категорий, отображаемых на сайте.',
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
		'module_name' => array(
			'type' => 'select',
			'name' => 'Модуль',
			'select' => array(),
			'sql' => true,
		),
		'type' => array(
			'type' => 'select',
			'name' => 'Тип',
			'select' => array(
				'element' => 'Элементы',
				'category' => 'Категории',
				'brand' => 'Производители',
			),
			'sql' => true,
		),
		'actions' => array(
			'param' => true,
			'trash' => true,
		),
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'category', // часть модуля - категории
		'link_to_element', // основная ссылка ведет к списку элементов, принадлежащих категории
	);

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить описание файла импорта/экспорта');
		$this->move_shop_importexport();
	}

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		parent::__construct($diafan);
		$this->class_init();
	}

	/**
	 * Инициализация модуля
	 *
	 * @return void
	 */
	public function class_init()
	{
		if(defined('IS_ADMIN') && IS_ADMIN)
		{
			$this->url = BASE_PATH_HREF.'service/express/';
			//$_SESSION[self::CLASS_NAME]["mode_express_choice"] = 'fields';
		}
	}

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		if($modules = $this->diafan->_service->modules_express())
		{
			foreach ($modules as $module)
			{
				$this->variables["main"]["module_name"]["select"][$module["name"]] = $module["title"];
				$this->variables_list["module_name"]["select"][$module["name"]] = $module["title"];
			}


			if(! isset($this->cache["element"]))
			{
				$module_names = $this->diafan->array_column($modules, "name");
				$this->cache["element"] = $module_names;
			}
			if(! isset($this->cache["category"]))
			{
				$this->cache["category"] = array();
				foreach ($this->cache["element"] as $module_name)
				{
					if(! $this->diafan->configmodules("cat", $module_name))
					{
						continue;
					}
					$this->cache["category"][] = $module_name;
				}
			}
			$types = $this->variable('type', 'type_cat'); //$types = $this->diafan->variable('type', 'type_cat');
			foreach($types as $key => $value)
			{
				if(! empty($value))
				{
					continue;
				}
				switch ($key)
				{
					case 'element':
						$types[$key] = implode(",", $this->cache["element"]);
						break;

					case 'category':
						$types[$key] = implode(",", $this->cache["category"]);
						break;

					default:
						break;
				}
			}
			$this->variables["main"]["type"]["type_cat"] = $types; //$this->diafan->variable('type', 'type_cat', $types);
		}
	}

	/**
	 * Выводит заголовок контента
	 *
	 * @return void
	 */
	public function show_h1()
	{
		$rewrite = $this->diafan->_admin->rewrite;
		$this->diafan->_admin->rewrite = 'service/express';
		parent::show_h1();
		$this->diafan->_admin->rewrite = $rewrite;
	}

	/**
	 * Выводит контент модуля
	 * @return void
	 */
	public function show()
	{
		$this->show_content_h1();
		$this->show_mode_express();

		if(defined('IS_DEMO') && IS_DEMO)
		{
			echo '
			<div class="content__left content__left_full">';
			echo '
				<br />';
			echo '
				<div class="error">'.$this->diafan->_('не доступно в демонстрационном режиме').'</div>';
			echo '
				<br />';
			echo '
			</div>';
			return;
		}

		/*echo '
		<div class="content__left content__left_full">';*/

		$modules = $this->diafan->_service->modules_express();
		if(empty($modules))
		{
			echo '<br />'.'<div class="error">'.$this->diafan->_('Не выявлено модулей доступных для экспорта/импорта. Настрока описания не доступна.').'</div>';
		}
		else $this->show_content();

		/*echo'
		</div>';*/
	}

	/**
	 * Выводит заголовок контента
	 *
	 * @return void
	 */
	private function show_content_h1()
	{
		if($this->diafan->_admin->name != $this->diafan->_admin->title_module)
		{
			echo '<span class="head-box__unit">'.$this->diafan->_($this->diafan->_admin->name).'</span>';
		}
	}

	/**
	 * Выводит выбор режима импорта/экспорта записей
	 *
	 * @return void
	 */
	private function show_mode_express()
	{
		$import_cat = ''; $import_param = array();
		$export_cat = ''; $export_param = array();
		if(! empty($_SESSION[self::CLASS_NAME]["cat_import_choice"])
		&& is_array($_SESSION[self::CLASS_NAME]["cat_import_choice"]))
		{
			if(! empty($_SESSION[self::CLASS_NAME]["cat_import_choice"]["cat"]))
			{
				// $import_cat = 'cat'.(string) $_SESSION[self::CLASS_NAME]["cat_import_choice"]["cat"].'/';
				// $import_cat .= 'step2'.'/'
				// if(! empty($_SESSION[self::CLASS_NAME]["cat_import_choice"]["desc"]))
				// {
				// 	$import_param = array("cat" => (string) $_SESSION[self::CLASS_NAME]["cat_import_choice"]["desc"]);
				// }
			}
		}
		if(! empty($_SESSION[self::CLASS_NAME]["cat_export_choice"])
		&& is_array($_SESSION[self::CLASS_NAME]["cat_export_choice"]))
		{
			if(! empty($_SESSION[self::CLASS_NAME]["cat_export_choice"]["cat"]))
			{
				$export_cat = 'cat'.(string) $_SESSION[self::CLASS_NAME]["cat_export_choice"]["cat"].'/';
				if(! empty($_SESSION[self::CLASS_NAME]["cat_export_choice"]["desc"]))
				{
					$export_param = array("cat" => (string) $_SESSION[self::CLASS_NAME]["cat_export_choice"]["desc"]);
				}
			}
		}
		$import_url = $this->url.'import/'.$import_cat;
		if($import_param)
		{
			$import_url = $this->diafan->params_append($import_url, $import_param);
		}
		$export_url = $this->url.'export/'.$export_cat;
		if($export_param)
		{
			$export_url = $this->diafan->params_append($export_url, $export_param);
		}
		echo '
		<div class="tabs">
			<a href="'.$import_url.'" class="tabs__item">'.$this->diafan->_('Импорт').'</a>
			<a href="'.$export_url.'" class="tabs__item">'.$this->diafan->_('Экспорт').'</a>
			<a href="'.$this->url.'fields/'.'" class="tabs__item tabs__item_active">'.$this->diafan->_('Сохраненные импорт/экспорт').'</a>
		</div>';
	}

	/**
	 * Выводит контент экспорта записей
	 *
	 * @return void
	 */
	private function show_content()
	{
		echo '<h2>'.$this->diafan->_('Сохраненные правила экспорта/импорта').'</h2>';

		$this->diafan->list_row();
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
		$this->diafan->_admin->name = $this->diafan->_admin->title_module;
		parent::list_row($id, $first_level);
		$this->diafan->_admin->name = $name;
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
		$text = '<div class="item__unit">';

		//param
		if ($this->diafan->variable_list('actions', 'param'))
		{
			$text .= '
			<a href="'.BASE_PATH_HREF.$this->diafan->_admin->rewrite.'/edit'.$row["id"].'/" title="'.$this->diafan->_('Изменить настройки').'"'.' class="action item__ui param">
				<i class="fa fa-gear"></i>
			</a>';
		}

		//trash
		if ($this->diafan->variable_list('actions', 'trash')
			&& $this->diafan->_users->roles('del', $this->diafan->_admin->rewrite)
			&& $this->diafan->check_action($row, 'del'))
		{
			$text .= '
			<a href="javascript:void(0)" title="'.$this->diafan->_('Удалить').'" confirm="'
			.(! empty( $row["count_children"] ) ? $this->diafan->_('ВНИМАНИЕ! Пункт содержит вложенность! ') : '')
			.($this->diafan->config("category") ? $this->diafan->_('При удалении категории удаляются все принадлежащие ей элементы. ') : '')
			.$this->diafan->_('Вы действительно хотите удалить запись в корзину?')
			. '" action="trash" class="action item__ui remove">
				<i class="fa fa-times-circle"></i>
			</a>';
		}

		$text .= '</div>';

		return $text;
	}

	/**
	 * Генерирует форму редактирования/добавления элемента
	 *
	 * @return void
	 */
	public function edit()
	{
		$this->diafan->show_content_h1();

		$this->diafan->show_mode_express();

		/*echo '
		<div class="content__left content__left_full">';*/

		$modules = $this->diafan->_service->modules_express();
		if(empty($modules))
		{
			echo '<br />'.'<div class="error">'.$this->diafan->_('Не выявлено модулей доступных для экспорта/импорта. Настрока описания не доступна.').'</div>';
		}
		else $this->diafan->edit_content();

		/*echo'
		</div>';*/
	}

	/**
	 * Выводит контент редактирования/добавления элемента
	 *
	 * @return void
	 */
	public function edit_content()
	{
		$this->show_content_h1();
		$this->show_mode_express();

		if(defined('IS_DEMO') && IS_DEMO)
		{
			echo '
			<div class="content__left content__left_full">';
			echo '
				<br />';
			echo '
				<div class="error">'.$this->diafan->_('не доступно в демонстрационном режиме').'</div>';
			echo '
				<br />';
			echo '
			</div>';
			return;
		}

		echo '<h2>'.$this->diafan->_('Настройки экспорта/импорта').'</h2>';

		echo parent::edit();
	}

	/**
	 * Редактирование поля "Модуль"
	 *
	 * @return void
	 */
	public function edit_variable_module_name()
	{
		if($this->diafan->is_new)
		{
			$modules = $this->diafan->_service->modules_express();
			$default = false;
			if(! empty($modules))
			{
				$keys = array_keys($modules);
				$default = ! empty($modules[$keys[0]]["name"]) ? $modules[$keys[0]]["name"] : false;
			}
			if($default) $this->diafan->values('module_name', $default, true);
		}

		$this->diafan->show_table_tr(
						$this->diafan->type,
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
	}

	/**
	 * Редактирование поля "Раздел сайта"
	 *
	 * @return void
	 */
	public function edit_variable_site_id()
	{
		if(! $module_name = $this->diafan->values('module_name'))
		{
			$this->diafan->variable('site_id', 'disabled', true);
		}

		if (! $this->diafan->value)
		{
			$this->diafan->value = $this->diafan->_route->site;
		}

		if($module_name)
		{
			if(! $cats[0] = DB::query_fetch_all("SELECT id, [name] FROM {site} WHERE trash='0' AND module_name='%s' ORDER BY sort ASC, id DESC", $module_name))
			{
				$this->diafan->variable('site_id', 'disabled', true);
			}
		}
		else $cats[0] = array();

		echo '
		<div class="unit" id="site_id"'.(! $module_name ? ' style="display: none;"' : '').'>
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
			<select name="'.$this->diafan->key.'"'.($this->diafan->variable_disabled() ? ' disabled' : '').'>';
		if($module_name && $cats[0])
		{
			echo $this->diafan->get_options($cats, $cats[0], array ( $this->diafan->value ));
		}
		else
		{
			echo '<option value="0" selected>'.$this->diafan->_("не используется").'</option>';
		}
		echo '
			</select>
		</div>';
	}

	/**
	 * Редактирование поля "Тип"
	 * @return void
	 */
	public function edit_variable_type()
	{
		if(! $module_name = $this->diafan->values('module_name'))
		{
			$this->diafan->variable('type', 'disabled', true);
		}

		echo '
		<div class="unit" id="type">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
			<select name="type"'.($this->diafan->variable_disabled() ? ' disabled' : '').'>';
			if($module_name)
			{
				$types = $this->diafan->variable('type', 'type_cat');
				foreach ($types as $key => $value) $types[$key] = explode(",", $value);
				foreach ($this->diafan->variable('type', 'select') as $key => $value)
				{
					if(! in_array($module_name, $types[$key]))
					{
						continue;
					}
					echo '<option value="'.$key.'"'.($this->diafan->value == $key ? ' selected' : '').'>'.$value.'</option>';
				}
			}
			else
			{
				echo '<option value="element" selected>'.$this->diafan->_("не используется").'</option>';
			}
			echo '</select>
		</div>';
	}

	/**
	 * Редактирование поля "Категория"
	 *
	 * @return void
	 */
	public function edit_variable_cat_id()
	{
		if(! $module_name = $this->diafan->values('module_name'))
		{
			$this->diafan->variable('cat_id', 'disabled', true);
		}

		if(! $cat = $this->diafan->configmodules("cat", $module_name))
		{
			$this->diafan->variable('cat_id', 'disabled', true);
		}

		if($module_name && $cat)
		{
			$tables = $this->diafan->_service->get_tables("site_id");
			if(! empty($tables[$module_name.'_category']))
			{
				$query = ', site_id AS rel';
			}
			else $query = ', 0 AS rel';
			$rows = DB::query_fetch_all("SELECT id, [name], parent_id".$query." FROM {%s_category} WHERE trash='0' ORDER BY sort ASC LIMIT 1000", $module_name);
			if(count($rows) == 1000)
			{
				$this->diafan->variable('cat_id', 'disabled', true);
				$cat = false;
			}
			else
			{
				foreach ($rows as $row)
				{
					$cats[$row["parent_id"]][] = $row;
				}
			}
		}
		echo '
		<div class="unit" id="cat_id">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>';

		echo ' <select name="'.$this->diafan->key.'"'.($this->diafan->variable_disabled() ? ' disabled' : '').'>';
		if($module_name && $cat)
		{
			echo '<option value="" rel="0">'.$this->diafan->_('-').'</option>';
			echo $this->diafan->get_options($cats, $cats[0], array($this->diafan->value));
		}
		else
		{
			echo '<option value="" rel="0" selected>'.$this->diafan->_("не используется").'</option>';
		}
		echo '</select>';

		echo '</div>';
	}

	/**
	 * Редактирование поля "Меню"
	 *
	 * @return void
	 */
	public function edit_variable_menu_cat_id()
	{
		if($rows = DB::query_fetch_all("SELECT id, [name] FROM {menu_category} WHERE trash='0' ORDER BY id DESC LIMIT 1000"))
		{
			$menu = true;
		}
		else $menu = false;
		if(count($rows) == 1000)
		{
			$this->diafan->variable('menu_cat_id', 'disabled', true);
			$menu = false;
		}

		$depend = $this->diafan->variable('', 'depend');
		$attr = '';
		$class = '';
		if($depend)
		{
			$attr = ' depend="'.$depend.'"';
			$class = "depend_field";
		}

		echo '
		<div class="unit'.($class ? ' '.$class : '').'" id="menu_cat_id"'.$attr.'>
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>';

		echo ' <select name="'.$this->diafan->key.'"'.($this->diafan->variable_disabled() ? ' disabled' : '').'>';
		echo '<option value="0">-</option>';
		if($menu)
		{
			foreach($rows as $row)
			{
				echo '<option value="'.$row["id"].'"'
				.($this->diafan->value == $row["id"] ? ' selected="selected" ' : '')
				.'>'.$row["name"].'</option>';
			}
		}
		echo '</select>';
		echo '
		</div>';
	}

	/**
	 * Сохранение поля "Категория"
	 *
	 * @return void
	 */
	public function save_variable_cat_id()
	{
		$this->diafan->set_query("cat_id=%d");
		$this->diafan->set_value($_POST["cat_id"]);
	}

	/**
	 * Сохранение поля "Меню"
	 *
	 * @return void
	 */
	public function save_variable_menu_cat_id()
	{
		$this->diafan->set_query("menu_cat_id=%d");
		$this->diafan->set_value($_POST["menu_cat_id"]);
	}

	/**
	 * Переносит описания импорта/экспорта из модуля "Интернет магазин"
	 *
	 * @return void
	 */
	public function move_shop_importexport()
	{
		if(DB::query_result("SELECT COUNT(*) FROM {shop_import_category} WHERE trash='0'") <= 0)
		{
			return;
		}
		if(! empty($_GET["move"]) && $_GET["move"] == 'shop')
		{
			if($cats = DB::query_fetch_all("SELECT * FROM {shop_import_category} WHERE trash='0'"))
			{
				foreach($cats as $cat)
				{
					$cat_id = DB::query("INSERT INTO {%s_fields_category} (name, module_name, type, delete_items, site_id, cat_id, count_part, sub_delimiter, header) VALUES('%s', '%s', '%s', '%d', %d, %d, %d, '%h', '%d')",
						"service_express",
						$cat["name"],
						"shop",
						($cat["type"] == "good" ? "element" : $cat["type"]),
						($cat["delete_items"] ? 1 : 0),
						$cat["site_id"],
						$cat["cat_id"],
						$cat["count_part"],
						$cat["sub_delimiter"],
						($cat["header"] ? 1 : 0)
					);
					DB::query("DELETE FROM {shop_import_category} WHERE id=%d", $cat["id"]);
					$rows = DB::query_fetch_all("SELECT * FROM {shop_import} WHERE cat_id=%d AND trash='0'", $cat["id"]);
					foreach($rows as $row)
					{
						$row_id = DB::query("INSERT INTO {%s_fields} (name, cat_id, type, params, required, sort) VALUES('%s', %d, '%s', '%s', '%d', %d)",
							"service_express",
							$row["name"],
							$cat_id,
							$row["type"],
							$row["params"],
							($row["required"] ? 1 : 0),
							$row["sort"]
						);
						DB::query("DELETE FROM {shop_import} WHERE id=%d", $row_id);
					}
				}
				DB::query("UPDATE {%s_fields_category} SET sort=id WHERE sort=%d", "service_express", 0);
			}
			$this->diafan->redirect(URL.$this->diafan->get_nav);
			return;
		}
		echo ' <a href="'.URL.'?move=shop'.'" class="btn" onclick="return confirm(\''.$this->diafan->_("Все описания из модуля Интернет магазин будут перенесены. Продолжить?").'\')"><i class="fa fa-download"></i> '.$this->diafan->_("Перенести описания из модуля Интернет магазин").'</a>';
	}
}

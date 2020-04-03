<?php
/**
 * Администрирование описания импорта/экспорта записей базы данных
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
 * Service_admin_express_fields_element
 */
class Frame_admin_express_fields_element extends Frame_admin
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
	public $table = 'service_express_fields';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название поля для импорта (не обязательно)',
				'help' => 'Название поля для импорта, необходимо только для наглядности в списке полей конструтора форм в административной части сайта.',
			),
			'type' => array(
				'type' => 'select',
				'name' => 'Тип',
				'help' => 'Значение или свойство элемента (товара, страницы и пр.), куда будет импортироваться данное поле.',
				'select' => array(
					'id' => 'Идентификатор (уникальный код)',
					'name' => 'Название', // поле «Название»
					'cats' => 'Категория', // идентификатор категории из файла импорта категорий, используется у товаров. Чтобы категория для товара определилась правильно, нужно сначала импортировать категории
					'empty' => 'Пропустить поле', // неиспользуемая информация
					'parent' => 'Родитель', // идентификатор родителя (должен соответствовать данным из поля первого типа), используется у категорий
					'act' => 'Опубликовать на сайте', // значения 1|0|true|false
					'menu' => 'Отображать в меню', // значения 1|0|true|false
				),
				'type_cat' => array(
					'id' => 'category,element',
					'name' => 'category,element',
					'cats' => 'element',
					'empty' => 'category,element',
					'parent' => 'category,element',
					'act' => 'category,element',
					'menu' => 'category',
				),
			),
			'paramhelp' => array(
				'type' => 'function',
				'no_save' => true,
				'hide' => true,
			),
			'required' => array(
				'type' => 'checkbox',
				'name' => 'Выдавать ошибку, если поле пустое',
				'help' => 'При импорте файла выйдет ошибка, если значение поля будет не задано.',
			),
			'params' => array(
				'type' => 'function',
				'name' => 'Дополнительные настройки',
				'hide' => true,
			),
			'params_id' => array(
				'type' => 'none',
				'name' => 'Использовать в качестве идентификаторов',
				'help' => "Поле выводится только для типов «Идентификатор», «Категория», «Родитель», «Производитель» и «Идентификатор связанных товаров».\n\n* собственное значение – при первом импорте все товары/категории/производители добавляться в базу, идентификатор запишется в поле import_id. При последующем импорте товары/категории/производители будут обновляться по идентификатору import_id;\n* идентификатор на сайте – использовать стандартный идентификатор id;\n* название – только для категорий и производителей, только для типов «Категория», «Производитель» и «Родитель».",
				'no_save' => true,
			),
			'params_sequence_delimitor' => array(
				'type' => 'none',
				'name' => 'Разделитель последовательности',
				'help' => 'Дополнительный разделитель последовательности записей в ячейке (по умолчанию >). Если это поле заполнить, то для типа «Категория» можно указать через разделитель последовательную цепочку вложенных категорий.',
				'no_save' => true,
			),
			'hr2' => 'hr',
			'cat_id' => array(
				'type' => 'select',
				'name' => 'Импорт/Экспорт',
				'help' => 'В каком импорте/экспорте находится редактируемое поле.',
			),
			'hr3' => 'hr',
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить после',
				'help' => 'Изменить положение текущего поля среди других полей. В списке можно сортировать поля простым перетаскиванием мыши.',
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
		'type' => array(
			'name' => 'Тип',
			'type' => 'select',
			'sql' => true,
			'no_important' => true,
		),
		'actions' => array(
			'trash' => true,
		),
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'element', // используются группы
		'category_flat', // категории не содержат вложенности
		'category_no_multilang', // имя категории не переводиться
	);

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
	 *
	 * @return void
	 */
	public function prepare_config()
	{

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
	 *
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
	 * Выводит ссылку на добавление
	 *
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить поле');

		if(! isset($this->cache["category"]))
		{
			$this->cache["category"] = DB::query_fetch_array("SELECT * FROM {%s_category} WHERE id=%d", $this->diafan->table, $this->diafan->_route->cat);
		}
		$modules = $this->diafan->_service->modules_express();
		$module_names = $this->diafan->array_column($modules, "name");
		$cat = array_search($this->cache["category"]["module_name"], $module_names);
		if($cat !== false)
		{
			$cat++;
		}
		else $cat = 0;
		echo ' <a href="'.$this->url.'export/cat'.$cat.'/?cat='.$this->diafan->_route->cat.'#export" class="btn" onclick="return confirm(\''.$this->diafan->_("В соответствии с описанием будут экспортированы записи в формате CSV. Продолжить?").'\')"><i class="fa fa-upload"></i> '.$this->diafan->_('Скачать файл экспорта').' (*.csv)'.'</a>';
	}

	/**
	 * Выводит заголовок контента
	 *
	 * @return void
	 */
	private function show_content_h1()
	{
		if($this->diafan->_route->cat && $this->diafan->_admin->rewrite == 'service/express/fields')
		{
			if(! isset($this->cache["category"]))
			{
				$this->cache["category"] = DB::query_fetch_array("SELECT * FROM {%s_category} WHERE id=%d", $this->diafan->table, $this->diafan->_route->cat);
			}
			if(isset($this->cache["category"]["name"]))
			{
				$name = $this->cache["category"]["name"];
			}
			elseif(isset($this->cache["category"]["name"._LANG]))
			{
				$name = $this->cache["category"]["name"._LANG];
			}
			else $name = '';
			if(! isset($this->cache["module_name"]))
			{
				if($modules = $this->diafan->_service->modules_express())
				{
					foreach ($modules as $module)
					{
						$this->cache["module_name"][$module["name"]] = $module["title"];
					}
				}
				else $this->cache["module_name"] = array();
			}
			if(isset($this->cache["module_name"][$this->cache["category"]["module_name"]]))
			{
				$module_name = $this->cache["module_name"][$this->cache["category"]["module_name"]];
			}
			else $module_name = false;
			echo '<span class="head-box__unit">'.$name.($module_name ? ' '.$this->diafan->_("для модуля").' <b>'.$module_name.'</b>' : '').'<span class="heading__in"> <a href="'.BASE_PATH_HREF.$this->diafan->_admin->rewrite.'/edit'.$this->diafan->_route->cat.'/">'.$this->diafan->_('изменить').'</a>
		</span></span>';
		}
		elseif($this->diafan->_admin->name != $this->diafan->_admin->title_module)
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
		echo '
		<h2>'.$this->diafan->_('Редактирование правил экспорта/импорта').'</h2>
		<p>'.$this->diafan->_('Вы можете переопределить поля для импорта/экспорта данных.%s Не забудьте про %sнастройки%s текущего импорта/экспорта.', '<br />', ' <a href="'.$this->diafan->get_admin_url('cat', 'page', 'step').'">', '</a>', '<a href="'.BASE_PATH_HREF.$this->diafan->_admin->rewrite.'/edit'.$this->diafan->_route->cat.'/">', '</a>').'</p>';

		$this->diafan->categories = array();
		$this->diafan->list_row($this->diafan->_route->cat);
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

		echo '<h2>'.$this->diafan->_('Редактирование поле экспорта/импорта').'</h2>';

		echo parent::edit();
	}

	/**
	 * Редактирование поля "Тип"
	 *
	 * @return void
	 */
	public function edit_variable_type()
	{
		echo '
		<div class="unit" id="type">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
			<select name="type">';
			$types = $this->diafan->variable('type', 'type_cat');
			foreach ($this->diafan->variable('type', 'select') as $key => $value)
			{
				echo '<option value="'.$key.'"'.($this->diafan->value == $key ? ' selected' : '');
				$type = $types[$key];
				if(strpos($type, 'element') !== false)
				{
					echo ' element="true"';
				}
				if(strpos($type, 'category') !== false)
				{
					echo ' category="true"';
				}
				echo '>'.$value.'</option>';
			}
			echo '</select>
		</div>';
	}

	/**
	 * Редактирование поля "Ссылка на документацию"
	 *
	 * @return void
	 */
	public function edit_variable_paramhelp()
	{
		echo '<div class="unit" id="paramhelp">
		↑ <a href="http'.(IS_HTTPS ? "s" : '').'://www.diafan.ru/dokument/full-manual/sysmodules/servis/#Eksport/import-bazy-dannykh" target="_blank">'.$this->diafan->_('О типах полей для импорта').'</a>
		</div>';
	}

	/**
	 * Редактирование поля "Параметры"
	 *
	 * @return void
	 */
	public function edit_variable_params()
	{
		$type = ''; $params = array();
		if(! $this->diafan->is_new)
		{
			$params = unserialize($this->diafan->value);
			$type = $this->diafan->values("type");
		}

		// меню
		echo '
		<div id="param_menu_id" class="unit params param_menu">
			<div class="infofield">'.$this->diafan->_('Меню').'</div>';
		$rows = DB::query_fetch_all("SELECT id, [name] FROM {menu_category} WHERE trash='0' ORDER BY id DESC");
		if ($rows)
		{
			echo '<select name="param_menu_id">';
			echo '<option value="0">-</option>';
			foreach ($rows as $row)
			{
				echo '<option value="'.$row["id"].'"'
				.($type == 'menu' && ! empty($params["id"]) && $params["id"] == $row["id"] ? ' selected="selected" ' : '' )
				.'>'.$row["name"].'</option>';
			}
			echo '</select>';
		}
		echo '
		</div>';

		// id
		$param_type = (in_array($type, array('id')) && ! empty($params["type"]) ? $params["type"] : '');
		echo '
		<div id="param_type" class="unit params param_id">
			<div class="infofield">'.$this->diafan->_('В этом поле загружается').'</div>
			<select name="param_type">
			<option value=""'.(! $param_type ? ' selected' : '').'>'.$this->diafan->_('Импортируемый ID').'</option>
			<option value="site"'.($param_type == 'site' ? ' selected' : '').'>'.$this->diafan->_('ID на сайте').'</option>
			</select>
		</div>';

		// parent, cats
		$param_type = (in_array($type, array('cats', 'parent')) && ! empty($params["type"]) ? $params["type"] : '');
		if($this->diafan->is_new && ! $param_type) $param_type = 'name'; // для новой записи по умолчанию "name"
		echo '
		<div id="param_cats_type" class="unit params param_cats param_parent">
			<div class="infofield">'.$this->diafan->_('В этом поле загружается').'</div>
			<select name="param_cats_type">
			<option value="name"'.($param_type == 'name' ? ' selected' : '').'>'.$this->diafan->_('Название').'</option>
			<option value="site"'.($param_type == 'site' ? ' selected' : '').'>'.$this->diafan->_('ID на сайте').'</option>
			<option value=""'.(! $param_type ? ' selected' : '').'>'.$this->diafan->_('Импортируемый ID').'</option>
			</select>
		</div>';

		$params_sequence_delimitor = $type == 'cats' && ! empty($params["sequence_delimitor"]) ? $params["sequence_delimitor"] : ($this->diafan->is_new ? '>' : ''); // для новой записи по умолчанию ">"
		echo '<div class="unit params param_cats" id="param_sequence_delimitor">
			<div class="infofield">'.$this->diafan->variable_name('params_sequence_delimitor').$this->diafan->help("params_sequence_delimitor").'</div>
			<input name="param_sequence_delimitor" type="text" value="'.$params_sequence_delimitor.'">
		</div>';
	}

	/**
	 * Редактирование поля "Категория"
	 *
	 * @return void
	 */
	public function edit_variable_cat_id()
	{
		if(! $this->diafan->value)
		{
			$this->diafan->value = $this->diafan->_route->cat;
		}
		$module_name = DB::query_result("SELECT module_name FROM {%s_category} WHERE id=%d LIMIT 1", $this->table, $this->diafan->value);
		$options = DB::query_fetch_all("SELECT id, name, type FROM {%s_category} WHERE module_name='%s' AND trash='0' ORDER BY sort ASC", $this->table, $module_name);
		echo '
		<div class="unit" id="cat_id">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
			<select name="cat_id">';
			foreach ($options as $row)
			{
				echo '<option value="'.$row["id"].'"'.($this->diafan->value == $row["id"] ? ' selected' : '').' type="'.$row["type"].'">'.$row["name"].'</option>';
			}
			echo '</select>
		</div>';
	}

	/**
	 * Валидация поля "Тип"
	 *
	 * @return void
	 */
	public function validate_variable_type()
	{
		switch ($_POST['type'])
		{
			case 'menu':
			case 'empty':
				return;
		}

		if($id = DB::query_result("SELECT id FROM {%s} WHERE cat_id=%d AND type='%h' AND trash='0' LIMIT 1", $this->table, $this->diafan->_route->cat, $_POST["type"]))
		{
			if($this->diafan->is_new || $id != $this->diafan->id)
			{
				$this->diafan->set_error('param_type', 'Поле с такими настройками уже существует');
			}
		}
	}

	/**
	 * Валидация поля "Параметры"
	 *
	 * @return void
	 */
	public function validate_variable_params()
	{
		switch($_POST["type"])
		{
			case 'menu':
				if(! $_POST["param_menu_id"])
				{
					$this->diafan->set_error('param_menu_id', 'Выберите категорию меню');
				}
				else
				{
					$params = array("id" => $this->diafan->filter($_POST, "int", "param_menu_id"));
					if($id = DB::query_result("SELECT id FROM {%s} WHERE cat_id=%d AND type='menu' AND params='%s' AND trash='0' LIMIT 1", $this->table, $this->diafan->_route->cat, serialize($params)))
					{
						if($this->diafan->is_new || $id != $this->diafan->id)
						{
							$this->diafan->set_error('param_menu_id', 'Поле с такими настройками уже существует');
						}
					}
				}
				break;
		}
	}

	/**
	 * Сохранение поля "Параметры"
	 *
	 * @return void
	 */
	public function save_variable_params()
	{
		switch($_POST["type"])
		{
			case 'menu':
				$params = array("id" => $this->diafan->filter($_POST, "int", "param_menu_id"));
				break;

			case 'id':
				$params = array("type" => in_array($_POST["param_type"], array('site')) ? $_POST["param_type"] : '');
				break;

			case 'cats':
				$params = array(
					"type" => in_array($_POST["param_cats_type"], array('site', 'name'))  ? $_POST["param_cats_type"] : '',
					'sequence_delimitor' => strip_tags($_POST["param_sequence_delimitor"]),
				);
				break;

			case 'parent':
				$params = array("type" => in_array($_POST["param_cats_type"], array('site', 'name'))  ? $_POST["param_cats_type"] : '');
				break;
		}
		if(empty($params))
		{
			$params = '';
		}
		else
		{
			$params = serialize($params);
		}
		$this->diafan->set_query("params='%s'");
		$this->diafan->set_value($params);
	}


	/**
	 * Редактирование поля "Название" в таблице
	 *
	 * @param string $id номер записи в таблице
	 * @return void
	 */
	public function table_variable_name($id = 0)
	{
		if(! isset($this->cache["table"]["values"][$id]))
		{
			$trash = ! empty($this->variables_list["actions"]["trash"]);
			$this->cache["table"]["values"][$id] = DB::query_fetch_array(
				"SELECT * FROM {".$this->table."} WHERE id=%d"
				.($trash ? " AND trash='0'" : '' )." LIMIT 1",
				$id
			);
		}
		$key = 'name';
		$variable_name = isset($this->variables["main"][$key]["name"])
			? $this->variables["main"][$key]["name"]
			: '';
		$help = isset($this->variables["main"][$key]["help"])
			? $this->variables["main"][$key]["help"]
			: '';
		$help = $help
			? ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>'
			: $help;
		$value = isset($this->cache["table"]["values"][$id][$key])
			? $this->cache["table"]["values"][$id][$key]
			: '';
		$disabled = ! empty($this->variables["main"][$key]["disabled"]);
		$maxlength = isset($this->variables["main"][$key]["maxlength"])
			? $this->variables["main"][$key]["maxlength"]
			: '';

		echo '
		<div class="unit" unit_id="'.$key.'">
			<div class="infofield">'.$variable_name.$help.'</div>
			<input type="text" name="'.$key.'[]" value="'.str_replace('"', '&quot;', $value).'"'.($disabled ? ' disabled' : '').($maxlength ? ' maxlength="'.$maxlength.'"' : '').'>
		</div>';
	}

	/**
	 * Редактирование поля "Тип" в таблице
	 *
	 * @param string $id номер записи в таблице
	 * @return void
	 */
	public function table_variable_type($id = 0)
	{
		if(! isset($this->cache["table"]["values"][$id]))
		{
			$trash = ! empty($this->variables_list["actions"]["trash"]);
			$this->cache["table"]["values"][$id] = DB::query_fetch_array(
				"SELECT * FROM {".$this->table."} WHERE id=%d"
				.($trash ? " AND trash='0'" : '' )." LIMIT 1",
				$id
			);
		}
		$key = 'type';
		$variable_name = isset($this->variables["main"][$key]["name"])
			? $this->variables["main"][$key]["name"]
			: '';
		$help = isset($this->variables["main"][$key]["help"])
			? $this->variables["main"][$key]["help"]
			: '';
		$help = $help
			? ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>'
			: $help;
		$empty = "empty";


		echo '
		<div class="unit" unit_id="type">
			<div class="infofield">'.$variable_name.$help.'</div>
			<select name="type[]">';
			$types = isset($this->variables["main"][$key]["type_cat"])
				? $this->variables["main"][$key]["type_cat"]
				: array();
			$select = isset($this->variables["main"][$key]["select"])
				? $this->variables["main"][$key]["select"]
				: array();
			$value = isset($this->cache["table"]["values"][$id][$key])
				? $this->cache["table"]["values"][$id][$key]
				: '';
			if(! $id && ! $value && isset($select[$empty]))
			{
				$value = $empty;
			}
			foreach ($select as $k => $val)
			{
				echo '<option value="'.$k.'"'.($value == $k ? ' selected' : '');
				$type = $types[$k];
				if(strpos($type, 'element') !== false)
				{
					echo ' element="true"';
				}
				if(strpos($type, 'category') !== false)
				{
					echo ' category="true"';
				}
				echo '>'.$val.'</option>';
			}
			echo '</select>
		</div>';
	}

	/**
	 * Редактирование поля "Параметры" в таблице
	 *
	 * @param string $id номер записи в таблице
	 * @return void
	 */
	public function table_variable_params($id = 0)
	{
		if(! isset($this->cache["table"]["values"][$id]))
		{
			$trash = ! empty($this->variables_list["actions"]["trash"]);
			$this->cache["table"]["values"][$id] = DB::query_fetch_array(
				"SELECT * FROM {".$this->table."} WHERE id=%d"
				.($trash ? " AND trash='0'" : '' )." LIMIT 1",
				$id
			);
		}
		$key = 'params';
		$is_new = empty($this->cache["table"]["values"][$id]);
		$value = isset($this->cache["table"]["values"][$id][$key])
			? $this->cache["table"]["values"][$id][$key]
			: '';
		$k = 'type';
		$type_value = isset($this->cache["table"]["values"][$id][$k])
			? $this->cache["table"]["values"][$id][$k]
			: '';

		$type = ''; $params = array();
		if(! $is_new)
		{
			$params = unserialize($value);
			$type = $type_value;
		}

		$uid = $this->diafan->uid();

		// меню
		echo '
		<div unit_id="param_menu_id" class="unit params param_menu">
			<div class="infofield">'.$this->diafan->_('Меню').'</div>';
		$rows = DB::query_fetch_all("SELECT id, [name] FROM {menu_category} WHERE trash='0' ORDER BY id DESC");
		echo '<select name="param_menu_id[]">';
		echo '<option value="0">-</option>';
		foreach ($rows as $row)
		{
			echo '<option value="'.$row["id"].'"'
			.($type == 'menu' && ! empty($params["id"]) && $params["id"] == $row["id"] ? ' selected="selected" ' : '' )
			.'>'.$row["name"].'</option>';
		}
		echo '</select>';
		echo '
		</div>';

		// id
		$param_type = (in_array($type, array('id')) && ! empty($params["type"]) ? $params["type"] : '');
		echo '
		<div unit_id="param_type" class="unit params param_id">
			<div class="infofield">'.$this->diafan->_('В этом поле загружается').'</div>
			<select name="param_type[]">
			<option value=""'.(! $param_type ? ' selected' : '').'>'.$this->diafan->_('Импортируемый ID').'</option>
			<option value="site"'.($param_type == 'site' ? ' selected' : '').'>'.$this->diafan->_('ID на сайте').'</option>
			</select>
		</div>';

		// parent, cats
		$param_type = (in_array($type, array('cats', 'parent')) && ! empty($params["type"]) ? $params["type"] : '');
		if($is_new && ! $param_type) $param_type = 'name'; // для новой записи по умолчанию "name"
		echo '
		<div unit_id="param_cats_type" class="unit params param_cats param_parent">
			<div class="infofield">'.$this->diafan->_('В этом поле загружается').'</div>
			<select name="param_cats_type[]">
			<option value="name"'.($param_type == 'name' ? ' selected' : '').'>'.$this->diafan->_('Название').'</option>
			<option value="site"'.($param_type == 'site' ? ' selected' : '').'>'.$this->diafan->_('ID на сайте').'</option>
			<option value=""'.(! $param_type ? ' selected' : '').'>'.$this->diafan->_('Импортируемый ID').'</option>
			</select>
		</div>';

		$params_sequence_delimitor = $type == 'cats' && ! empty($params["sequence_delimitor"]) ? $params["sequence_delimitor"] : ($is_new ? '>' : ''); // для новой записи по умолчанию ">"
		$k = "params_sequence_delimitor";
		$variable_name = isset($this->variables["main"][$k]["name"])
			? $this->variables["main"][$k]["name"]
			: '';
		$help = isset($this->variables["main"][$k]["help"])
			? $this->variables["main"][$k]["help"]
			: '';
		$help = $help
			? ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>'
			: $help;
		echo '<div class="unit params param_cats" unit_id="param_sequence_delimitor">
			<div class="infofield">'.$variable_name.$help.'</div>
			<input name="param_sequence_delimitor[]" type="text" value="'.$params_sequence_delimitor.'">
		</div>';
	}

	/**
	 * Редактирование поля "Выдавать ошибку, если поле пустое" в таблице
	 *
	 * @param string $id номер записи в таблице
	 * @return void
	 */
	public function table_variable_required($id = 0)
	{
		if(! isset($this->cache["table"]["values"][$id]))
		{
			$trash = ! empty($this->variables_list["actions"]["trash"]);
			$this->cache["table"]["values"][$id] = DB::query_fetch_array(
				"SELECT * FROM {".$this->table."} WHERE id=%d"
				.($trash ? " AND trash='0'" : '' )." LIMIT 1",
				$id
			);
		}
		$key = 'required';
		$variable_name = isset($this->variables["main"][$key]["name"])
			? $this->variables["main"][$key]["name"]
			: '';
		$help = isset($this->variables["main"][$key]["help"])
			? $this->variables["main"][$key]["help"]
			: '';
		$help = $help
			? ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>'
			: $help;
		$value = isset($this->cache["table"]["values"][$id][$key])
				? $this->cache["table"]["values"][$id][$key]
				: '';

		$uid = $this->diafan->uid();
		$class = $attr = ""; $disabled = false;

		echo '
		<div class="unit'.($class ? ' '.$class : '').'" id="'.$key."_".$uid.'" unit_id="'.$key.'"'.$attr.'>
			<input type="checkbox" id="input_'.$key."_".$uid.'" name="'.$key.'[]" value="1"'.( $value ? " checked" : '' ).($disabled ? ' disabled' : '').'>
			<label for="input_'.$key."_".$uid.'"><b>'.$variable_name.$help.'</b></label>
		</div>';
	}

	/**
	 * Валидация поля "Тип" в таблице
	 *
	 * @return void
	 */
	public function validate_table_variable_type()
	{
		foreach($_POST['type'] as $key => $type)
		{
			if(empty($type))
			{
				$this->set_errors('required', 'Тип поля не определен', $key);
			}
			if(! array_key_exists($type, $this->variables['main']['type']['select'])
			|| ! array_key_exists($type, $this->variables['main']['type']['type_cat']))
			{
				$this->set_errors('required', 'Тип поля не соответствует разрешенным', $key);
			}
			if(! empty($_POST["category"]["type"])
			&& ! in_array($_POST["category"]["type"], explode(',', $this->variables['main']['type']['type_cat'][$type])))
			{
				$this->set_errors('required', 'Тип поля не соответствует выбранному типу данных импорта/экспорта', $key);
			}

			switch ($type)
			{
				case 'menu':
				case 'empty':
					continue(2);
			}

			if(isset($variable_type[$type]))
			{
				$this->set_errors('required', 'Поле с такими настройками уже существует', $key);
			}
			$variable_type[$type] = true;
		}
	}

	/**
	 * Валидация поля "Параметры" в таблице
	 *
	 * @return void
	 */
	public function validate_table_variable_params()
	{
		$variable_params = array(
			'menu' => array(),
		);
		foreach($_POST['type'] as $key => $type)
		{
			switch($type)
			{
				case 'menu':
					if(empty($_POST["param_menu_id"][$key]))
					{
						$this->set_errors('param_menu_id', 'Укажите меню', $key);
					}
					else
					{
						$params = array("id" => $this->diafan->filter($_POST["param_menu_id"][$key], "int"));
						$params = serialize($params);
						if(isset($variable_params["menu"][$params]))
						{
							$this->set_errors('param_menu_id', 'Поле с такими настройками уже существует');
						}
						$variable_params["menu"][$params] = true;
					}
					continue(2);
			}
		}
	}

	/**
	 * Запоминает найденную ошибку
	 *
	 * @param array $key индекс ошибки
	 * @param array $value значение ошибки
	 * @param array $sub_key дополнительный индекс ошибки
	 * @return void
	 */
	public function set_errors($key, $value, $sub_key = false)
	{
		if($value)
		{
			if($sub_key === false) $this->cache["errors"][$key][] = $this->diafan->_($value);
			else $this->cache["errors"][$key][$sub_key] = $this->diafan->_($value);
		}
	}

	/**
	 * Возвращает найденные ошибки
	 *
	 * @return array
	 */
	public function get_errors()
	{
		if(isset($this->cache["errors"]))
		{
			return $this->cache["errors"];
		}
		else array();
	}

	/**
	 * Cохранение поля "Параметры" в таблице
	 *
	 * @return array
	 */
	public function save_table_variable_params()
	{
		$array_keys = array(
			"type", "name", "param_menu_id", "param_type", "param_cats_type", "param_sequence_delimitor"
		);
		$result = array();
		foreach($_POST['type'] as $key => $type)
		{
			$params = ''; $post = array();
			foreach ($array_keys as $val)
			{
				$post[$val] = isset($_POST[$val][$key]) ? $_POST[$val][$key] : '';
			}
			switch($type)
			{
				case 'menu':
					$params = array("id" => $this->diafan->filter($post, "int", "param_menu_id"));
					break;

				case 'id':
					$params = array("type" => in_array($post["param_type"], array('site', 'article')) ? $post["param_type"] : '');
					break;

					case 'cats':
						$params = array(
							"type" => in_array($post["param_cats_type"], array('site', 'name'))  ? $post["param_cats_type"] : '',
							'sequence_delimitor' => strip_tags($post["param_sequence_delimitor"]),
						);
						break;

				case 'parent':
					$params = array("type" => in_array($post["param_cats_type"], array('site', 'name'))  ? $post["param_cats_type"] : '');
					break;
			}
			if(empty($params))
			{
				$params = '';
			}
			else
			{
				$params = serialize($params);
			}
			$result[$key] = array(
				"query" => "params='%s'",
				"value" => $params
			);
		}
		return $result;
	}
}

/**
 * Подключает редактирование списка полей
 *
 * @param array $module_name имя модуля
 * @return void
 */
function inc_file_express_modules($diafan, $module_name = false)
{
	if(class_exists('service_admin_express_fields_element'))
	{
		return;
	}
	$inc_class = false;
	if(! $module_name)
	{
		if($diafan->_route->cat)
		{
			if($row = DB::query_fetch_array("SELECT * FROM {service_express_fields_category} WHERE id=%d LIMIT 1", $diafan->_route->cat))
			{
				if(! empty($row["module_name"]))
				{
					$module_name = $row["module_name"];
				}
			}
		}
	}
	if($module_name)
	{
		$e_type = 'express.fields.element';
		$module_file = 'modules/'.$module_name.'/admin/'.$module_name.'.admin'.($e_type ? '.'.$e_type : '').'.php';
		if(Custom::exists($module_file))
		{
			Custom::inc($module_file);
			$name_class_module = ucfirst($module_name).'_admin'.($e_type ? '_'.str_replace('.', '_', $e_type) : '');
			if (in_array($name_class_module, get_declared_classes()))
			{
				if(class_alias($name_class_module, 'service_admin_express_fields_element'))
				{
					// TO_DO: class_alias lowercase class name
					// var_dump(get_declared_classes());
					$inc_class = true;
				}
			}
		}
		$path = 'modules/'.$module_name.'/admin/js/'.$module_name.'.admin'.($e_type ? '.'.$e_type : '').'.js';
		if(Custom::exists($path))
		{
			$diafan->_admin->js_view[] = Custom::path($path);
		}
	}
	if(! $inc_class)
	{
		class service_admin_express_fields_element extends Frame_admin_express_fields_element {}
	}
}

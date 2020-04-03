<?php
/**
 * Описание импорта/экспорта записей базы данных
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
 * Clauses_admin_express_fields_element
 */
class Clauses_admin_express_fields_element extends Frame_admin_express_fields_element
{
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
				'help' => 'Значение или свойство статьи, куда будет импортироваться данное поле.',
				'select' => array(
					'empty' => 'Пропустить поле', // неиспользуемая информация
					'id' => 'ID Идентификатор (уникальный код)',
					'name' => 'Название', // поле «Название»
					'anons' => 'Описание краткое', // поле «Анонс»
					'text' => 'Описание полное', // поле «Описание»
					'cats' => 'Категория', // идентификатор категории из файла импорта категорий, используется у статей. Чтобы категория для статьи определилась правильно, нужно сначала импортировать категории
					'parent' => 'Категория родительская', // идентификатор родителя (должен соответствовать данным из поля первого типа), используется у категорий
					'created' => 'Дата новости', // поле «Дата»
					'images' => 'Изображения', // имена изображений через «Разделитель данных внутри поля». Только имена, вида img123.jpg
					'act' => 'Опубликовать на сайте', // значения 1|0|true|false
					'rel_clauses' => 'ID связанных статей', // идентификаторы через «Разделитель данных внутри поля», только для статей
					'menu' => 'Отображать в меню', // значения 1|0|true|false
					'keywords' => 'Мета-тег Keywords, Ключевые слова',
					'descr' => 'Мета-тег Description, Описание',
					'title_meta' => 'Мета-тег Title, Заголовок окна в браузере',
					'rewrite' => 'Псевдоссылка', // ЧПУ статьи/категории
					'redirect' => 'Редирект', // ссылка относительно корня сайта, без слеша в начале; если указан «Дополнительный разделитель», то можно указать код редиректа
					'canonical' => 'Канонический тег', // полная ссылка
					'access' => 'Доступ', // если доступ ограничен, то идентификаторы типов пользователей, которым дан доступ, через «Разделитель данных внутри поля»
					'map_no_show' => 'Не показывать элемент на карте сайта', // значения 1|0|true|false
					'changefreq' => 'Changefreq', // значения
					'priority' => 'Priority', // значения 0 - 1
					'sort' => 'Номер для сортировки', // статьи сортируются по убыванию, категории по возрастанию
					'admin_id' => 'Редактор', // id пользователя на сайте
					'theme' => 'Шаблон сайта', // файл из папки themes
					'view' => 'Шаблон модуля (modules/news/views/news.view.шаблон.php)', // (modules/news/views/news.view.шаблон.php)
					'view_rows' => 'Шаблон модуля для списка статей (modules/news/views/news.view.шаблон.php)', // (modules/news/views/news.view.шаблон.php)
					'view_element' => 'Шаблон страницы элемента (modules/news/views/news.view.шаблон.php)', // (modules/news/views/news.view.шаблон.php)
					'date_start' => 'Дата и время начала показа', // в формате дд.мм.гггг чч:мм
					'date_finish' => 'Дата и время окончания показа', // в формате дд.мм.гггг чч:мм
				),
				'type_cat' => array(
					'empty' => 'category,element',
					'id' => 'category,element',
					'name' => 'category,element',
					'anons' => 'category,element',
					'text' => 'category,element',
					'cats' => 'element',
					'parent' => 'category,element',
					'created' => 'element',
					'images' => 'category,element',
					'act' => 'category,element',
					'rel_clauses' => 'element',
					'menu' => 'category',
					'keywords' => 'category,element',
					'descr' => 'category,element',
					'title_meta' => 'category,element',
					'rewrite' => 'category,element',
					'canonical' => 'category,element',
					'redirect' => 'category,element',
					'access' => 'category,element',
					'map_no_show' => 'category,element',
					'changefreq' => 'category,element',
					'priority' => 'category,element',
					'sort' => 'category,element',
					'admin_id' => 'category,element',
					'theme' => 'category,element',
					'view' => 'category,element',
					'view_rows' => 'category',
					'view_element' => 'category',
					'date_start' => 'element',
					'date_finish' => 'element',
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
				'name' => 'В этом поле загружается',
				'help' => "Поле выводится только для типов «Идентификатор», «Категория», «Родитель» и «Идентификатор связанных статей».\n\n* собственное значение – при первом импорте все статьи/категории добавляться в базу, идентификатор запишется в поле import_id. При последующем импорте статьи/категории будут обновляться по идентификатору import_id;\n* идентификатор на сайте – использовать стандартный идентификатор id;\n* артикул – только для статей, только для типов «Идентификатор» и «Идентификатор связанных статей»;\n* название – только для категорий, только для типов «Категория» и «Родитель».",
				'no_save' => true,
			),
			'params_start_stop' => array(
				'type' => 'none',
				'name' => 'Диапазон значений',
				'help' => 'Для полей с типами «Дата и время начала показа» и «Дата и время окончания показа». Помогает исключить ошибки в файле импорта.',
				'no_save' => true,
			),
			'params_directory' => array(
				'type' => 'none',
				'name' => 'Адрес папки для загрузки',
				'help' => 'Может быть вида pictures (тогда будет использоваться локальная папка текущего сайта http://site.ru/pictures/). Или в виде полного онлайн пути http://anysite.ru/pictures/ (для .рф доменов в PUNY-формате). К этому пути при импорте добавятся имена изображений из импортируемого файла CSV. Используется только для типов полей «Имена изображений» и «Дополнительная характеристика» с типами «Изображения» и «Файлы».',
				'no_save' => true,
			),
			'params_second_delimitor' => array(
				'type' => 'none',
				'name' => 'Дополнительный разделитель, если необходимо указать атрибуты, например: img1.jpg^alt^title|img2.jpg^alt|img3.jpg^alt^title',
				'help' => 'Дополнительный разделитель полей в ячейке. В строке данные по умолчанию делятся разделителем из глобальных настроек импорта (по умолчанию |). Если это поле заполнить, то для типа «Редирект» можно указать через дополнительный разделитель код редиректа, а для типа «Имена изображений» - значения alt и title для изображений.',
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
				'name' => 'Категория',
				'help' => 'Файл импорта.',
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
	 * Редактирование поля "Тип"
	 *
	 * @return void
	 */
	public function edit_variable_type()
	{
		parent::edit_variable_type();
	}

	/**
	 * Редактирование поля "Ссылка на документацию"
	 *
	 * @return void
	 */
	public function edit_variable_paramhelp()
	{
		echo '<div class="unit" id="name">
		↑ <a href="http'.(IS_HTTPS ? "s" : '').'://www.diafan.ru/dokument/full-manual/modules/news/#Import/eksport" target="_blank">'.$this->diafan->_('О типах полей для импорта').'</a>
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
			<div class="infofield">'.$this->diafan->_('В какое меню загружать?').'</div>';
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

		// id,  rel_clauses
		$param_type = (in_array($type, array('id', 'rel_clauses')) && ! empty($params["type"]) ? $params["type"] : '');
		echo '
		<div id="param_type" class="unit params param_id param_rel_clauses">
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
			<option value=""'.(! $param_type ? ' selected' : '').'>'.$this->diafan->_('Импортируемый ID').'</option>
			<option value="site"'.($param_type == 'site' ? ' selected' : '').'>'.$this->diafan->_('ID на сайте').'</option>
			</select>
		</div>';

		// date_start, date_finish
		$date_start = (in_array($type, array('date_start', 'date_finish')) && ! empty($params["date_start"]) ? $params["date_start"] : '');
		$date_finish = (in_array($type, array('date_start', 'date_finish')) && ! empty($params["date_finish"]) ? $params["date_finish"] : '');
		echo '
		<div class="unit params param_date_start param_date_finish" id="param_date_start">
			<div class="infofield">'.$this->diafan->_('Диапазон значений').'</div>
			<input type="text" name="param_date_start" value="'
			.($date_start ? date("d.m.Y H:i", $date_start) : '')
			.'" class="timecalendar" showTime="true">
			-
			<input type="text" name="param_date_finish" value="'
			.($date_finish ? date("d.m.Y H:i", $date_finish) : '')
			.'" class="timecalendar" showTime="true">
		</div>';

		// изображения
		echo '
		<div class="unit params param_images" id="param_directory">
			<div class="infofield">'.$this->diafan->variable_name('params_directory').$this->diafan->help("params_directory").'</div>
			<input name="param_directory" type="text" value="'.(($type == 'images' || $type == 'param') && ! empty($params["directory"]) ? $params["directory"] : '').'">
		</div>';

		$param_second_delimitor = ($type == 'redirect' || $type == 'images') && ! empty($params["second_delimitor"]) ? $params["second_delimitor"] : '';
		if($this->diafan->is_new) $param_second_delimitor = '^'; // для новой записи по умолчанию "^"
		echo '<div class="unit params param_images param_redirect" id="param_second_delimitor">
			<div class="infofield">'.$this->diafan->variable_name('params_second_delimitor').$this->diafan->help("params_second_delimitor").'</div>
			<input name="param_second_delimitor" type="text" value="'.$param_second_delimitor.'">
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
		parent::edit_variable_cat_id();
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
			case 'images':
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

			case 'date_start':
			case 'date_finish':
				$params = array();
				Custom::inc('includes/validate.php');
				if(! empty($_POST["param_date_start"]))
				{
					$this->diafan->set_error('param_date_start', Validate::datetime($_POST["param_date_start"]));
				}
				if(! empty($_POST["param_date_finish"]))
				{
					$this->diafan->set_error('param_date_start', Validate::datetime($_POST["param_date_finish"]));
				}
				break;

			case 'images':
				// TODO: костыли
				if(empty($_POST["param_directory"]))
				{
					$_POST["param_directory"] = '';
					//$this->diafan->set_error('param_directory', 'Задайте папку с изображениями');
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
			case 'rel_clauses':
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

			case 'date_start':
			case 'date_finish':
				$params = array();
				if(! empty($_POST["param_date_start"]))
				{
					$params["date_start"] = $this->diafan->unixdate($_POST["param_date_start"]);
				}
				if(! empty($_POST["param_date_finish"]))
				{
					$params["date_finish"] = $this->diafan->unixdate($_POST["param_date_finish"]);
				}
				break;

			case 'images':
				$params = array(
					'directory' => strip_tags($_POST["param_directory"]),
					'second_delimitor' => strip_tags($_POST["param_second_delimitor"]),
				);
				break;

			case 'redirect':
				$params = array(
					'second_delimitor' => strip_tags($_POST["param_second_delimitor"])
				);
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
		parent::table_variable_name($id);
	}

	/**
	 * Редактирование поля "Тип" в таблице
	 *
	 * @param string $id номер записи в таблице
	 * @return void
	 */
	public function table_variable_type($id = 0)
	{
		parent::table_variable_type($id);
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
		$cat_id = ! empty($this->cache["table"]["values"][$id]["cat_id"]) ? $this->cache["table"]["values"][$id]["cat_id"] : 0;
		if(! isset($this->cache["site_id"][$cat_id]))
		{
			$this->cache["site_id"][$cat_id] = DB::query_result("SELECT site_id FROM {%s} WHERE id=%d AND trash='0' LIMIT 1", 'service_express_fields_category', $cat_id);
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
			<div class="infofield">'.$this->diafan->_('В какое меню загружать?').'</div>';
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

		// id,  rel_clauses
		$param_type = (in_array($type, array('id', 'rel_clauses')) && ! empty($params["type"]) ? $params["type"] : '');
		echo '
		<div unit_id="param_type" class="unit params param_id param_rel_clauses">
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
			<option value=""'.(! $param_type ? ' selected' : '').'>'.$this->diafan->_('Импортируемый ID').'</option>
			<option value="site"'.($param_type == 'site' ? ' selected' : '').'>'.$this->diafan->_('ID на сайте').'</option>

			</select>
		</div>';

		// date_start, date_finish
		$date_start = (in_array($type, array('date_start', 'date_finish')) && ! empty($params["date_start"]) ? $params["date_start"] : '');
		$date_finish = (in_array($type, array('date_start', 'date_finish')) && ! empty($params["date_finish"]) ? $params["date_finish"] : '');
		echo '
		<div class="unit params param_date_start param_date_finish" unit_id="param_date_start">
			<div class="infofield">'.$this->diafan->_('Диапазон значений').'</div>
			<input type="text" name="param_date_start[]" value="'
			.($date_start ? date("d.m.Y H:i", $date_start) : '')
			.'" class="timecalendar" showTime="true">
			-
			<input type="text" name="param_date_finish[]" value="'
			.($date_finish ? date("d.m.Y H:i", $date_finish) : '')
			.'" class="timecalendar" showTime="true">
		</div>';

		// изображения
		$k = "params_directory";
		$variable_name = isset($this->variables["main"][$k]["name"])
			? $this->variables["main"][$k]["name"]
			: '';
		$help = isset($this->variables["main"][$k]["help"])
			? $this->variables["main"][$k]["help"]
			: '';
		$help = $help
			? ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>'
			: $help;
		echo '
		<div class="unit params param_images" unit_id="param_directory">
			<div class="infofield">'.$variable_name.$help.'</div>
			<input name="param_directory[]" type="text" value="'.(($type == 'images' || $type == 'param') && ! empty($params["directory"]) ? $params["directory"] : '').'">
		</div>';

		$param_second_delimitor = ($type == 'redirect' || $type == 'images') && ! empty($params["second_delimitor"]) ? $params["second_delimitor"] : '';
		if($is_new) $param_second_delimitor = '^'; // для новой записи по умолчанию "^"

		$k = "params_second_delimitor";
		$variable_name = isset($this->variables["main"][$k]["name"])
			? $this->variables["main"][$k]["name"]
			: '';
		$help = isset($this->variables["main"][$k]["help"])
			? $this->variables["main"][$k]["help"]
			: '';
		$help = $help
			? ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>'
			: $help;
		echo '<div class="unit params param_images param_redirect" unit_id="param_second_delimitor">
			<div class="infofield">'.$variable_name.$help.'</div>
			<input name="param_second_delimitor[]" type="text" value="'.$param_second_delimitor.'">
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
				case 'images':
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
			'param' => array(),
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

				case 'date_start':
				case 'date_finish':
					$params = array();
					Custom::inc('includes/validate.php');
					if(! empty($_POST["param_date_start"][$key]))
					{
						$this->set_errors('param_date_start', Validate::datetime($_POST["param_date_start"][$key]), $key);
					}
					if(! empty($_POST["param_date_finish"][$key]))
					{
						$this->set_errors('param_date_start', Validate::datetime($_POST["param_date_finish"][$key]), $key);
					}
					continue(2);

				case 'images':
					// TODO: костыли
					if(empty($_POST["param_directory"][$key]))
					{
						$_POST["param_directory"][$key] = '';
						//$this->set_errors('param_directory', 'Задайте папку с изображениями', $key);
					}
					continue(2);
			}
		}
	}

	/**
	 * Cохранение поля "Параметры" в таблице
	 *
	 * @return array
	 */
	public function save_table_variable_params()
	{
		$array_keys = array(
			"type", "name", "param_menu_id",
			"param_image", "param_type", "param_cats_type",
			"param_date_start", "param_date_finish", "param_directory", "param_second_delimitor", "param_sequence_delimitor"
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
				case 'rel_clauses':
					$params = array("type" => in_array($post["param_type"], array('site')) ? $post["param_type"] : '');
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

				case 'date_start':
				case 'date_finish':
					$params = array();
					if(! empty($post["param_date_start"]))
					{
						$params["date_start"] = $this->diafan->unixdate($post["param_date_start"]);
					}
					if(! empty($post["param_date_finish"]))
					{
						$params["date_finish"] = $this->diafan->unixdate($post["param_date_finish"]);
					}
					break;

				case 'images':
					$params = array(
						'directory' => strip_tags($post["param_directory"]),
						'second_delimitor' => strip_tags($post["param_second_delimitor"]),
					);
					break;

				case 'redirect':
					$params = array(
						'second_delimitor' => strip_tags($post["param_second_delimitor"])
					);
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

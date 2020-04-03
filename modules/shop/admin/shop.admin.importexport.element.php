<?php
/**
 * Импорт/экспорт данных
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
 * Shop_admin_importexport_element
 */
class Shop_admin_importexport_element extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'shop_import';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
				'help' => 'Название поля для импорта, необходимо только для наглядности в списке полей конструтора форм в административной части сайта.',
			),
			'type' => array(
				'type' => 'select',
				'name' => 'Тип',
				'help' => 'Значение или свойство товара, куда будет импортироваться данное поле.',
				'select' => array(
					'id' => 'Идентификатор (уникальный код)',
					'name' => 'Название', // поле «Название»
					'article' => 'Артикул', // используется у товаров
					'measure_unit' => 'Единица измерения', // используется у товаров
					'anons' => 'Анонс', // поле «Анонс»
					'text' => 'Текст', // поле «Описание»
					'keywords' => 'Ключевые слова, тег Keywords',
					'descr' => 'Описание, тег Description',
					'title_meta' => 'Заголовок окна в браузере, тег Title',
					'price' => 'Цена', //поле «Цена», используется у товаров
					'count' => 'Количество', // поле «Количество», используется у товаров
					'cats' => 'Категория', // идентификатор категории из файла импорта категорий, используется у товаров. Чтобы категория для товара определилась правильно, нужно сначала импортировать категории
					'brand' => 'Производитель', // идентификатор производителя из файла импорта производителей, используется у товаров. Чтобы производитель для товара определилась правильно, нужно сначала импортировать производителей
					'empty' => 'Пропуск', // неиспользуемая информация
					'parent' => 'Родитель', // идентификатор родителя (должен соответствовать данным из поля первого типа), используется у категорий
					'param' => 'Дополнительная характеристика', // характеристика товара из конструктора характеристик, используется у товаров
					'images' => 'Имена изображений', // имена изображений через «Разделитель данных внутри поля». Только имена, вида img123.jpg
					'rel_goods' => 'Идентификаторы связанных товаров', // идентификаторы через «Разделитель данных внутри поля», только для товаров
					'no_buy' => 'Товар временно отсутствует', // значения 1|0|true|false, только для товаров
					'act' => 'Опубликовать на сайте', // значения 1|0|true|false
					'rewrite' => 'Псевдоссылка', // ЧПУ товара/категории
					'redirect' => 'Редирект', // ссылка относительно корня сайта, без слеша в начале; если указан «Дополнительный разделитель», то можно указать код редиректа
					'canonical' => 'Канонический тег', // полная ссылка
					'menu' => 'Отображать в меню', // значения 1|0|true|false
					'hit' => 'Поле «Хит»', // значения 1|0|true|false, только для товаров
					'new' => 'Поле «Новинка»', // значения 1|0|true|false, только для товаров
					'action' => 'Поле «Акция»', // значения 1|0|true|false, только для товаров
					'is_file' => 'Товар является файлом', // значения 1|0|true|false, только для товаров
					'show_yandex' => 'Выгружать в Яндекс Маркет', // значения 1|0|true|false
					'yandex' => 'Значения полей для Яндекс Маркета', // только для товаров
					'show_google' => 'Выгружать в Google Merchant', // значения 1|0|true|false
					'google' => 'Значения полей для Google Merchant', // только для товаров
					'access' => 'Доступ', // если доступ ограничен, то идентификаторы типов пользователей, которым дан доступ, через «Разделитель данных внутри поля»
					'map_no_show' => 'Не показывать элемент на карте сайта', // значения 1|0|true|false
					'changefreq' => 'Changefreq', // значения
					'priority' => 'Priority', // значения 0 - 1
					'sort' => 'Номер для сортировки', // товары сортируются по убыванию, категории и производители по возрастанию
					'admin_id' => 'Редактор', // id пользователя на сайте
					'theme' => 'Шаблон сайта', // файл из папки themes
					'view' => 'Шаблон модуля (modules/shop/views/shop.view.шаблон.php)', // (modules/shop/views/shop.view.шаблон.php)
					'view_rows' => 'Шаблон модуля для списка товаров (modules/shop/views/shop.view.шаблон.php)', // (modules/shop/views/shop.view.шаблон.php)
					'view_element' => 'Шаблон страницы элемента (modules/shop/views/shop.view.шаблон.php)', // (modules/shop/views/shop.view.шаблон.php)
					'date_start' => 'Дата и время начала показа', // в формате дд.мм.гггг чч:мм
					'date_finish' => 'Дата и время окончания показа', // в формате дд.мм.гггг чч:мм
					'weight' => 'Вес',
					'length' => 'Длина',
					'width' => 'Ширина',
					'height' => 'Высота',
				),
				'type_cat' => array(
					'id' => 'category,good,brand',
					'name' => 'category,good,brand',
					'article' => 'good',
					'measure_unit' => 'good',
					'anons' => 'category,good',
					'text' => 'category,good,brand',
					'keywords' => 'category,good,brand',
					'descr' => 'category,good,brand',
					'title_meta' => 'category,good,brand',
					'price' => 'good',
					'count' => 'good',
					'cats' => 'good,brand',
					'brand' => 'good',
					'empty' => 'category,good,brand',
					'parent' => 'category',
					'param' => 'good',
					'images' => 'category,good,brand',
					'rel_goods' => 'good',
					'no_buy' => 'good',
					'act' => 'category,good,brand',
					'rewrite' => 'category,good,brand',
					'canonical' => 'category,good,brand',
					'redirect' => 'category,good,brand',
					'menu' => 'category,brand',
					'hit' => 'good',
					'new' => 'good',
					'action' => 'good',
					'is_file' => 'good',
					'show_yandex' => 'category,good',
					'yandex' => 'good',
					'show_google' => 'category,good',
					'google' => 'good',
					'access' => 'category,good',
					'map_no_show' => 'category,good,brand',
					'changefreq' => 'category,good,brand',
					'priority' => 'category,good,brand',
					'sort' => 'category,good,brand',
					'admin_id' => 'category,good',
					'theme' => 'category,good,brand',
					'view' => 'category,good,brand',
					'view_rows' => 'category',
					'view_element' => 'category',
					'date_start' => 'good',
					'date_finish' => 'good',
					'weight' => 'good',
					'length' => 'good',
					'width' => 'good',
					'height' => 'good',
				),
			),
			'paramhelp' => array(
				'type' => 'function',
				'no_save' => true,
				'hide' => true,
			),
			'required' => array(
				'type' => 'checkbox',
				'name' => 'Выдавать ошибку, если значение не задано',
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
				'help' => "Поле выводится только для типов «Идентификатор», «Категория», «Родитель», «Производитель» и «Идентификатор связанных товаров».\n\n* собственное значение – при первом импорте все товары/категории/производители добавляться в базу, идентификатор запишется в поле import_id. При последующем импорте товары/категории/производители будут обновляться по идентификатору import_id;\n* идентификатор на сайте – использовать стандартный идентификатор id;\n* артикул – только для товаров, только для типов «Идентификатор» и «Идентификатор связанных товаров»;\n* название – только для категорий и производителей, только для типов «Категория», «Производитель» и «Родитель».",
				'no_save' => true,
			),
			'params_start_stop' => array(
				'type' => 'none',
				'name' => 'Диапазон значений',
				'help' => 'Для полей с типами «Дата и время начала показа» и «Дата и время окончания показа». Помогает исключить ошибки в файле импорта.',
				'no_save' => true,
			),
			'params_param' => array(
				'type' => 'none',
				'name' => 'Дополнительная характеристика',
				'help' => 'Список характеристик для поля с типом «Дополнительная характеристика».',
				'no_save' => true,
			),
			'params_select' => array(
				'type' => 'none',
				'name' => 'Значения списка',
				'help' => "Для дополнительных харктеристик с типами «список с выбором нескольких значений» и «выпадающий список». Возможные значения:\n\n* номер – номер значения списка из таблицы {shop_param_select};\n* название – значение списка, которое видит пользователь.",
				'no_save' => true,
			),
			'params_directory' => array(
				'type' => 'none',
				'name' => 'Адрес файлов для загрузки',
				'help' => 'Может быть вида pictures (тогда будет использоваться локальная папка текущего сайта http://site.ru/pictures/). Или в виде полного онлайн пути http://anysite.ru/pictures/ (для .рф доменов в PUNY-формате). К этому пути при импорте добавятся имена изображений из импортируемого файла CSV. Используется только для типов полей «Имена изображений» и «Дополнительная характеристика» с типами «Изображения» и «Файлы».',
				'no_save' => true,
			),
			'params_separator' => array(
				'type' => 'none',
				'name' => 'Разделитель параметров, влияющих на цену, количества и валюты в пределах одного значения цены/количества',
				'help' => 'Только для типов «Цена» и «Количество».',
				'no_save' => true,
			),
			'params_multiselect' => array(
				'type' => 'none',
				'name' => 'Значения параметров, влияющих на цену',
				'help' => "Только для типов «Цена» и «Количество». Возможные значения:\n\n* номер – номер значения списка из таблицы {shop_param_select};\n* название – значение списка, которое видит пользователь.",
				'no_save' => true,
			),
			'params_count' => array(
				'type' => 'none',
				'name' => 'Указывать количество',
				'help' => "Значение следует сразу за ценой через «Разделитель параметров, влияющих на цену, колечества и валюты в пределах одного значения цены/количества», только для типа «Цена».",
				'no_save' => true,
			),
			'params_old_price' => array(
				'type' => 'none',
				'name' => 'Указывать старую цену',
				'help' => 'Значение следует сразу за количеством или ценой (если не отмечена опция «Указывать количество») через «Разделитель параметров, влияющих на цену, колечества и валюты в пределах одного значения цены/количества»,только для типа «Цена».',
				'no_save' => true,
			),
			'params_currency' => array(
				'type' => 'none',
				'name' => 'Указывать валюту',
				'help' => 'Значение следует сразу за старой ценой или ценой или количеством (если не отмечены опции «Указывать количество» и «Указывать старую цену») через «Разделитель параметров, влияющих на цену, колечества и валюты в пределах одного значения цены/количества»,только для типа «Цена».',
				'no_save' => true,
			),
			'params_currency_value' => array(
				'type' => 'none',
				'name' => 'Значение валюты',
				'help' => "Только для типа «Цена». Возможные значения:\n\n* номер – номер валюты из таблицы {shop_currency};\n* название – название валюты.",
				'no_save' => true,
			),
			'params_second_delimitor' => array(
				'type' => 'none',
				'name' => 'Дополнительный разделитель',
				'help' => 'Дополнительный разделитель полей в ячейке. В строке данные по умолчанию делятся разделителем из глобальных настроек импорта (по умолчанию |). Если это поле заполнить, то для типа «Редирект» можно указать через разделитель код редиректа, а для типа «Имена изображений» значения alt и title для изображений.',
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
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function show_import()
	{
		if (! empty($_POST["upload"]) || ! empty($_GET["upload"]))
		{
			Custom::inc('modules/shop/admin/shop.admin.import.php');
			$shop_admin_import = new Shop_admin_import($this->diafan);
			$shop_admin_import->upload();
		}
		elseif(! empty($_POST['shop_action']))//опереации с импортированными элементами
		{
			Custom::inc('modules/shop/admin/shop.admin.import.php');
			$shop_admin_import = new Shop_admin_import($this->diafan);
			switch ($_POST['shop_action'])
			{
				case 'act_import':
					$shop_admin_import->act(true);
					exit;
					break;

				case 'deact_import':
					$shop_admin_import->act(false);
					exit;
					break;

				case 'remove_import':
					$shop_admin_import->remove();
					exit;
					break;
			}
		}
	}

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить поле');
	}

	/**
	 * Выводит список полей
	 * @return void
	 */
	public function show()
	{
		$this->diafan->categories = array();
		$this->show_import();
		$this->diafan->list_row($this->diafan->_route->cat);

		if ($this->diafan->count)
		{
			echo '
			<p class="after-action-box">'.$this->diafan->_('И импорт и экспорт будут проводиться согласно полям, указанным выше. Вы можете самостоятельно создать необходимую структуру импорта/экспорта. Например, у Вас файл-таблица с товарами, где три колонки: «товар», «цена», «описание». Тогда вверху в списке импортируемых полей должно быть только три поля «Название товара», «Цена», «Полное описание» и именно в таком порядке.').'</p>

			<div class="box box_half box_height">
			<form action="" enctype="multipart/form-data" method="post">
				<input type="hidden" name="upload" value="true">
				<div class="box__heading">'.$this->diafan->_('Импорт').'</div>

				<input type="file" class="file" name="file" data-placeholder="'.$this->diafan->_('Тип файла для импорта - CSV').'">

				<div class="box__warning">
					<i class="fa fa-warning"></i>
					'.$this->diafan->_('Внимание! Не забудьте перед импортом создать резервную копию базы данных!').'
				</div>

				<button class="btn btn_blue btn_small">'.$this->diafan->_('Импортировать').'</button>
			</form>';

			$import = DB::query_fetch_array("SELECT * FROM {shop_import_category} WHERE id=%d LIMIT 1", $this->diafan->_route->cat);
			$where = '';
			if($import["type"] == 'good' && $import["cat_id"])
			{
				$where .= " AND cat_id=".$import["cat_id"];
			}

			$act_import = DB::query_result("SELECT id FROM {shop".($import["type"] != 'good' ? '_'.$import["type"] : '')."} WHERE import='1' AND [act]='0' AND site_id=%d ".$where." LIMIT 1", $import["site_id"]);
			$deact_import = DB::query_result("SELECT id FROM {shop".($import["type"] != 'good' ? '_'.$import["type"] : '')."} WHERE import='1' AND [act]='1' AND site_id=%d ".$where." LIMIT 1", $import["site_id"]);

			if($act_import || $deact_import)
			{
				echo '
				<br>
				<form method="post" action="'.URL.'">
				<input type="hidden" value="" name="shop_action">
				'.$this->diafan->_('Результаты последнего импорта').': &nbsp; &nbsp;';
				if($act_import)
				{
					echo '<input type="submit" class="btn btn_small shop_import_button" rel="act_import" value="'.$this->diafan->_('Показать на сайте').'" > &nbsp; &nbsp;';
				}
				if($deact_import)
				{
					echo '<input type="submit" class="btn btn_small shop_import_button" rel="deact_import" value="'.$this->diafan->_('Скрыть на сайте').'" > &nbsp; &nbsp;';
				}
				echo '<input type="submit" class="btn btn_small shop_import_button" rel="remove_import" value="'
			   .$this->diafan->_('Удалить').'" >
				</form>';
			}

			echo '</div>


			<div class="box box_half box_height box_right">
				<div class="box__heading">'.$this->diafan->_('Экспорт').'</div>

				<a href="http'.(IS_HTTPS ? "s" : '').'://'.BASE_URL.'/shop/export/'.$this->diafan->_route->cat.'/?'.rand(0, 999999).'" class="file-load">
					<i class="fa fa-file-code-o"></i>
					'.$this->diafan->_('Скачать файл экспорта').' (*.csv)
				</a>

				<p>'.$this->diafan->_('Если у Вас есть на сайте товары, Вы можете скачать этот файл, как пример для импорта.').'</p>
			</div>';
		}

	}

	/**
	 * Редактирование поля "Тип"
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
				if(strpos($type, 'good') !== false)
				{
					echo ' good="true"';
				}
				if(strpos($type, 'category') !== false)
				{
					echo ' category="true"';
				}
				if(strpos($type, 'brand') !== false)
				{
					echo ' brand="true"';
				}
				echo '>'.$value.'</option>';
			}
			echo '</select>
		</div>';
	}

	/**
	 * Редактирование поля "Ссылка на документацию"
	 * @return void
	 */
	public function edit_variable_paramhelp()
	{
		echo '<div class="unit" id="paramhelp">
		↑ <a href="http'.(IS_HTTPS ? "s" : '').'://www.diafan.ru/dokument/full-manual/modules/shop/#Import/eksport-YA.Market" target="_blank">'.$this->diafan->_('О типах полей для импорта').'</a>
		</div>';
	}

	/**
	 * Редактирование поля "Параметры"
	 * @return void
	 */
	public function edit_variable_params()
	{
		$type = '';
		if(! $this->diafan->is_new)
		{
			$params = unserialize($this->diafan->value);
			$type = $this->diafan->values("type");
		}

		// меню
		echo '
		<div id="param_menu_id" class="unit params param_menu">
			<div class="infofield">'.$this->diafan->_('Категория меню').'</div>';
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

		// дополнительная характеристика
		$param_select_type = $type == 'param' && ! empty($params["select_type"]) ? $params["select_type"] : '';
		echo '
		<div id="param_id" class="unit params param_param">
			<div class="infofield">'.$this->diafan->_('Характеристика').'</div>';
		$rows = DB::query_fetch_all("SELECT id, [name], type FROM {shop_param} WHERE trash='0' ORDER BY sort ASC, id ASC");
		if ($rows)
		{
			echo '<select name="param_id">';
			echo '<option value="0">-</option>';
			foreach ($rows as $row)
			{
				echo '<option value="'.$row["id"].'"'
				.($type == 'param' && ! empty($params["id"]) && $params["id"] == $row["id"] ? ' selected="selected" ' : '' )
				.' type="'.$row["type"].'">'.$row["name"].'</option>';
			}
			echo '</select>';
		}
		echo '
		</div>
		<div id="param_select_type" class="unit params param_param">
			<div class="infofield">'.$this->diafan->_('Значения списка').'</div>
			<select name="param_select_type">
			<option value="key"'.($param_select_type == 'key' ? ' selected' : '').'>'.$this->diafan->_('номер').'</option>
			<option value="value"'.($param_select_type == 'value' ? ' selected' : '').'>'.$this->diafan->_('название').'</option>
			</select>
		</div>';

		// цена, количество
		$param_delimitor = ($type == 'price' || $type == 'count') && ! empty($params["delimitor"]) ? $params["delimitor"] : '&';
		$param_second_delimitor = ($type == 'redirect' || $type == 'images') && ! empty($params["second_delimitor"]) ? $params["second_delimitor"] : '';
		$param_select_type = ($type == 'price' || $type == 'count') && ! empty($params["select_type"]) ? $params["select_type"] : '';
		$param_count = $type == 'price' && ! empty($params["count"]) ? $params["count"] : '';
		$param_old_price = $type == 'price' && ! empty($params["old_price"]) ? $params["old_price"] : '';
		$param_currency = $type == 'price' && ! empty($params["currency"]) ? $params["currency"] : '';
		$param_select_currency = $type == 'price' && ! empty($params["select_currency"]) ? $params["select_currency"] : '';

		$param_image = $type == 'price' && ! empty($params["image"]) ? $params["image"] : '';
		echo '
		<div id="param_delimitor" class="unit params param_price param_count">
			<div class="infofield">'.$this->diafan->_('Разделитель параметров, влияющих на цену, количества и валюты в пределах одного значения цены/количества').'</div>
			<input name="param_delimitor" type="text" value="'.$param_delimitor.'">
		</div>
		<div id="param_price_select_type" class="unit params param_price param_count">
			<div class="infofield">'.$this->diafan->_('Значения параметров, влияющих на цену').'</div>
			<select name="param_price_select_type">
			<option value="key"'.($param_select_type == 'key' ? ' selected' : '').'>'.$this->diafan->_('номер').'</option>
			<option value="value"'.($param_select_type == 'value' ? ' selected' : '').'>'.$this->diafan->_('название').'</option>
			</select>
		</div>
		<div id="param_count" class="unit params param_price">
			<input name="param_count" id="input_param_count" value="1" type="checkbox"'.($param_count ? ' checked' : '').'>
			<label for="input_param_count">'.$this->diafan->_('Указывать количество').'</label>
		</div>
		<div id="param_old_price" class="unit params param_price">
			<input name="param_old_price" id="input_param_old_price" value="1" type="checkbox"'.($param_old_price ? ' checked' : '').'>
			<label for="input_param_old_price">'.$this->diafan->_('Указывать старую цену').'</label>
		</div>
		<div id="param_currency" class="unit params param_price">
			<input name="param_currency" id="input_param_currency" value="1" type="checkbox"'.($param_currency ? ' checked' : '').'>
			<label for="input_param_currency">'.$this->diafan->_('Указывать валюту').'</label>
		</div>
		<div id="param_select_currency" class="unit params param_price">
			<div class="infofield">'.$this->diafan->_('Значение валюты').'</div>
			<select name="param_select_currency">
			<option value="key"'.($param_select_currency == 'key' ? ' selected' : '').'>'.$this->diafan->_('номер').'</option>
			<option value="value"'.($param_select_currency == 'value' ? ' selected' : '').'>'.$this->diafan->_('название').'</option>
			</select>
		</div>
		<div id="param_image" class="unit params param_price">
			<input name="param_image" id="input_param_image" value="1" type="checkbox"'.($param_image ? ' checked' : '').'>
			<label for="input_param_image">'.$this->diafan->_('Указывать связанные изображения').'</label>
		</div>';

		// id,  rel_goods
		$param_type = (in_array($type, array('id', 'rel_goods')) && ! empty($params["type"]) ? $params["type"] : '');
		echo '
		<div id="param_type" class="unit params param_id param_rel_goods">
			<div class="infofield">'.$this->diafan->_('Использовать в качестве идентификаторов').'</div>
			<select name="param_type">
			<option value=""'.(! $param_type ? ' selected' : '').'>'.$this->diafan->_('собственное значение').'</option>
			<option value="site"'.($param_type == 'site' ? ' selected' : '').'>'.$this->diafan->_('идентификатор на сайте').'</option>
			<option value="article"'.($param_type == 'article' ? ' selected' : '').'>'.$this->diafan->_('артикул').'</option>
			</select>
		</div>';

		// parent, cats
		$param_type = (in_array($type, array('cats', 'parent', 'brand')) && ! empty($params["type"]) ? $params["type"] : '');
		echo '
		<div id="param_cats_type" class="unit params param_cats param_parent param_brand">
			<div class="infofield">'.$this->diafan->_('Использовать в качестве идентификаторов').'</div>
			<select name="param_cats_type">
			<option value=""'.(! $param_type ? ' selected' : '').'>'.$this->diafan->_('собственное значение').'</option>
			<option value="site"'.($param_type == 'site' ? ' selected' : '').'>'.$this->diafan->_('идентификатор на сайте').'</option>
			<option value="name"'.($param_type == 'name' ? ' selected' : '').'>'.$this->diafan->_('название').'</option>
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

		echo '<div class="unit params param_images param_redirect" id="param_second_delimitor">
			<div class="infofield">'.$this->diafan->variable_name('params_second_delimitor').$this->diafan->help("params_second_delimitor").'</div>
			<input name="param_second_delimitor" type="text" value="'.$param_second_delimitor.'">
		</div>';
	}

	/**
	 * Редактирование поля "Категория"
	 * @return void
	 */
	public function edit_variable_cat_id()
	{
		$options = DB::query_fetch_all("SELECT id, name, type FROM {shop_import_category} WHERE trash='0' ORDER BY sort ASC");
		if(! $this->diafan->value)
		{
			$this->diafan->value = $this->diafan->_route->cat;
		}
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
	 * @return void
	 */
	public function validate_variable_type()
	{
		switch ($_POST['type'])
		{
			case 'menu':
			case 'param':
			case 'empty':
			case 'images':
				return;
		}

		if($id = DB::query_result("SELECT id FROM {shop_import} WHERE cat_id=%d AND type='%h' AND trash='0' LIMIT 1", $this->diafan->_route->cat, $_POST["type"]))
		{
			if($this->diafan->is_new || $id != $this->diafan->id)
			{
				$this->diafan->set_error('param_type', 'Поле с такими настройками уже существует');
			}
		}
	}

	/**
	 * Валидация поля "Параметры"
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
					if($id = DB::query_result("SELECT id FROM {shop_import} WHERE cat_id=%d AND type='menu' AND params='%s' AND trash='0' LIMIT 1", $this->diafan->_route->cat, serialize($params)))
					{
						if($this->diafan->is_new || $id != $this->diafan->id)
						{
							$this->diafan->set_error('param_menu_id', 'Поле с такими настройками уже существует');
						}
					}
				}
				break;

			case 'param':
				if(! $_POST["param_id"])
				{
					$this->diafan->set_error('param_id', 'Выберите характеристику');
				}
				else
				{
					$params = array("id" => $this->diafan->filter($_POST, "int", "param_id"));
					if($id = DB::query_result("SELECT id FROM {shop_import} WHERE cat_id=%d AND type='param' AND params='%s' AND trash='0' LIMIT 1", $this->diafan->_route->cat, serialize($params)))
					{
						if($this->diafan->is_new || $id != $this->diafan->id)
						{
							$this->diafan->set_error('param_id', 'Поле с такими настройками уже существует');
						}
					}
				}
				break;

			case 'date_start':
			case 'date_finish':
				$params = array();
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
	 * @return void
	 */
	public function save_variable_params()
	{
		switch($_POST["type"])
		{
			case 'menu':
				$params = array("id" => $this->diafan->filter($_POST, "int", "param_menu_id"));
				break;

			case 'param':
				$params = array(
						"id" => $this->diafan->filter($_POST, "int", "param_id"),
						"select_type" => $_POST["param_select_type"] == 'key' ? 'key' : 'value',
						'directory' => strip_tags($_POST["param_directory"])
					);
				break;

			case 'price':
				$params = array(
						"delimitor" => html_entity_decode($this->diafan->filter($_POST, "string", "param_delimitor")),
						"select_type" => $_POST["param_price_select_type"] == 'key' ? 'key' : 'value',
						"count" => ! empty($_POST["param_count"]) ? 1 : 0,
						"old_price" => ! empty($_POST["param_old_price"]) ? 1 : 0,
						"currency" => ! empty($_POST["param_currency"]) ? 1 : 0,
						"select_currency" => $_POST["param_select_currency"] == 'key' ? 'key' : 'value',
						"image" => ! empty($_POST["param_image"]) ? 1 : 0,
					);
				break;

			case 'count':
				$params = array(
						"delimitor" => html_entity_decode($this->diafan->filter($_POST, "string", "param_delimitor")),
						"select_type" => $_POST["param_price_select_type"] == 'key' ? 'key' : 'value'
					);
				break;

			case 'id':
			case 'rel_goods':
				$params = array("type" => in_array($_POST["param_type"], array('site', 'article')) ? $_POST["param_type"] : '');
				break;

			case 'cats':
			case 'parent':
			case 'brand':
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
}

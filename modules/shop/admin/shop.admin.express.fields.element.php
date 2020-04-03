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
 * Shop_admin_express_fields_element
 */
class Shop_admin_express_fields_element extends Frame_admin_express_fields_element
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
				'help' => 'Значение или свойство товара, куда будет импортироваться данное поле.',
				'select' => array(
					'empty' => 'Пропустить поле', // неиспользуемая информация
					'id' => 'ID Идентификатор (уникальный код)',
					'name' => 'Название', // поле «Название»
					'article' => 'Артикул', // используется у товаров
					'anons' => 'Описание краткое', // поле «Анонс»
					'text' => 'Описание полное', // поле «Описание»
					'price' => 'Цена', //поле «Цена», используется у товаров
					'count' => 'Количество', // поле «Количество», используется у товаров
					'measure_unit' => 'Единица измерения', // используется у товаров
					'cats' => 'Категория', // идентификатор категории из файла импорта категорий, используется у товаров. Чтобы категория для товара определилась правильно, нужно сначала импортировать категории
					'parent' => 'Категория родительская', // идентификатор родителя (должен соответствовать данным из поля первого типа), используется у категорий
					'brand' => 'Производитель', // идентификатор производителя из файла импорта производителей, используется у товаров. Чтобы производитель для товара определилась правильно, нужно сначала импортировать производителей
					'param' => 'Дополнительная характеристика', // характеристика товара из конструктора характеристик, используется у товаров
					'images' => 'Изображения', // имена изображений через «Разделитель данных внутри поля». Только имена, вида img123.jpg
					'act' => 'Опубликовать на сайте', // значения 1|0|true|false
					'weight' => 'Вес',
					'length' => 'Длина',
					'width' => 'Ширина',
					'height' => 'Высота',
					'rel_goods' => 'ID связанных товаров', // идентификаторы через «Разделитель данных внутри поля», только для товаров
					'no_buy' => 'Товар временно отсутствует', // значения 1|0|true|false, только для товаров
					'menu' => 'Отображать в меню', // значения 1|0|true|false
					'hit' => '«Хит»', // значения 1|0|true|false, только для товаров
					'new' => '«Новинка»', // значения 1|0|true|false, только для товаров
					'action' => '«Акция»', // значения 1|0|true|false, только для товаров
					'keywords' => 'Мета-тег Keywords, Ключевые слова',
					'descr' => 'Мета-тег Description, Описание',
					'title_meta' => 'Мета-тег Title, Заголовок окна в браузере',
					'rewrite' => 'Псевдоссылка', // ЧПУ товара/категории
					'redirect' => 'Редирект', // ссылка относительно корня сайта, без слеша в начале; если указан «Дополнительный разделитель», то можно указать код редиректа
					'canonical' => 'Канонический тег', // полная ссылка
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
				),
				'type_cat' => array(
					'empty' => 'category,element,brand',
					'id' => 'category,element,brand',
					'name' => 'category,element,brand',
					'article' => 'element',
					'anons' => 'category,element',
					'text' => 'category,element,brand',
					'price' => 'element',
					'count' => 'element',
					'measure_unit' => 'element',
					'cats' => 'element,brand',
					'parent' => 'category,element',
					'brand' => 'element',
					'param' => 'element',
					'images' => 'category,element,brand',
					'act' => 'category,element,brand',
					'weight' => 'element',
					'length' => 'element',
					'width' => 'element',
					'height' => 'element',
					'rel_goods' => 'element',
					'no_buy' => 'element',
					'menu' => 'category,brand',
					'hit' => 'element',
					'new' => 'element',
					'action' => 'element',
					'keywords' => 'category,element,brand',
					'descr' => 'category,element,brand',
					'title_meta' => 'category,element,brand',
					'rewrite' => 'category,element,brand',
					'canonical' => 'category,element,brand',
					'redirect' => 'category,element,brand',
					'is_file' => 'element',
					'show_yandex' => 'category,element',
					'yandex' => 'element',
					'show_google' => 'category,element',
					'google' => 'element',
					'access' => 'category,element',
					'map_no_show' => 'category,element,brand',
					'changefreq' => 'category,element,brand',
					'priority' => 'category,element,brand',
					'sort' => 'category,element,brand',
					'admin_id' => 'category,element',
					'theme' => 'category,element,brand',
					'view' => 'category,element,brand',
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
				'name' => 'Адрес папки для загрузки',
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
			'params_multiplier' => array(
				'type' => 'none',
				'name' => 'Множитель',
				'help' => 'Только для типа «Цена». Изменяет импортируемую цену на указанный множитель.',
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
	 *
	 * @return void
	 */
	public function edit_variable_paramhelp()
	{
		echo '<div class="unit" id="name">
		↑ <a href="http'.(IS_HTTPS ? "s" : '').'://www.diafan.ru/dokument/full-manual/modules/shop/#Import/eksport" target="_blank">'.$this->diafan->_('О типах полей для импорта').'</a>
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

		// дополнительная характеристика
		$param_select_type = $type == 'param' && ! empty($params["select_type"]) ? $params["select_type"] : '';
		echo '
		<div id="param_id" class="unit params param_param">
			<div class="infofield">'.$this->diafan->_('Укажите характеристику').'</div>';
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
			<div class="infofield">'.$this->diafan->_('Как загружать характеристику?').'</div>
			<select name="param_select_type">
			<option value="value"'.($param_select_type == 'value' ? ' selected' : '').'>'.$this->diafan->_('По названию').'</option>
			<option value="key"'.($param_select_type == 'key' ? ' selected' : '').'>'.$this->diafan->_('По ID').'</option>
			</select>
		</div>';

		// цена, количество
		$param_delimitor = ($type == 'price' || $type == 'count') && ! empty($params["delimitor"]) ? $params["delimitor"] : '&';
		$param_second_delimitor = ($type == 'redirect' || $type == 'images') && ! empty($params["second_delimitor"]) ? $params["second_delimitor"] : '';
		if($this->diafan->is_new) $param_second_delimitor = '^'; // для новой записи по умолчанию "^"
		$param_select_type = ($type == 'price' || $type == 'count') && ! empty($params["select_type"]) ? $params["select_type"] : '';
		$param_count = $type == 'price' && ! empty($params["count"]) ? $params["count"] : '';
		$param_old_price = $type == 'price' && ! empty($params["old_price"]) ? $params["old_price"] : '';
		$param_currency = $type == 'price' && ! empty($params["currency"]) ? $params["currency"] : '';
		$param_select_currency = $type == 'price' && ! empty($params["select_currency"]) ? $params["select_currency"] : '';
		$param_multiplier = $type == 'price' && ! empty($params["multiplier"]) ? $params["multiplier"] : 1;

		$param_image = $type == 'price' && ! empty($params["image"]) ? $params["image"] : '';
		echo '
		<div id="param_delimitor" class="unit params param_price param_count">
			<div class="infofield">'.$this->diafan->_('Разделитель параметров для зависимых цен, количества или валют').'</div>
			<div class="infobox">'.$this->diafan->_('Пример: 100.00&130.00&5 = цена 100, старая цена 130, 5 шт.').'</div>
			<input name="param_delimitor" type="text" value="'.$param_delimitor.'">
		</div>
		<div id="param_price_select_type" class="unit params param_price param_count">
			<div class="infofield">'.$this->diafan->_('Значения параметров, влияющих на цену, загружаются как').'</div>
			<select name="param_price_select_type">
			<option value="value"'.($param_select_type == 'value' ? ' selected' : '').'>'.$this->diafan->_('По названию').'</option>
			<option value="key"'.($param_select_type == 'key' ? ' selected' : '').'>'.$this->diafan->_('По ID').'</option>
			</select>
		</div>
		<div id="param_count" class="unit params param_price">
			<input name="param_count" id="input_param_count" value="1" type="checkbox"'.($param_count ? ' checked' : '').'>
			<label for="input_param_count">'.$this->diafan->_('Указано количество').'</label>
		</div>
		<div id="param_old_price" class="unit params param_price">
			<input name="param_old_price" id="input_param_old_price" value="1" type="checkbox"'.($param_old_price ? ' checked' : '').'>
			<label for="input_param_old_price">'.$this->diafan->_('Указана старая цена').'</label>
		</div>
		<div id="param_currency" class="unit params param_price">
			<input name="param_currency" id="input_param_currency" value="1" type="checkbox"'.($param_currency ? ' checked' : '').'>
			<label for="input_param_currency">'.$this->diafan->_('Указана валюта').'</label>
		</div>
		<div id="param_select_currency" class="unit params param_price">
			<div class="infofield">'.$this->diafan->_('Как указана валюта?').'</div>
			<select name="param_select_currency">
			<option value="value"'.($param_select_currency == 'value' ? ' selected' : '').'>'.$this->diafan->_('Название валюты').'</option>
			<option value="key"'.($param_select_currency == 'key' ? ' selected' : '').'>'.$this->diafan->_('ID валюты').'</option>
			</select>
		</div>
		<div id="param_image" class="unit params param_price">
			<input name="param_image" id="input_param_image" value="1" type="checkbox"'.($param_image ? ' checked' : '').'>
			<label for="input_param_image">'.$this->diafan->_('Указаны привязанные к цене изображения').'</label>
		</div>';
		$param_multiplier = floatval($param_multiplier);
		if(($param_multiplier * 10000) % 10) $num_decimal_places = 4;
		elseif(($param_multiplier * 1000) % 10) $num_decimal_places = 3;
		elseif(($param_multiplier * 100) % 10) $num_decimal_places = 2;
		elseif(($param_multiplier * 10) % 10) $num_decimal_places = 1;
		else $num_decimal_places = 0;
		echo '
		<div id="param_multiplier" class="unit params param_price">
			<div class="infofield">'.$this->diafan->_('Множитель (изменяет импортируемую цену).').'</div>
			<div class="infobox">'.$this->diafan->_('Пример: 1 (цена не изменится), 1.3 (цена увеличится на 30%), 0.8 (цена уменьшится на 20%) и т.д.').'</div>
			<input type="text" class="number" name="param_multiplier" id="input_param_multiplier" value="'.( $param_multiplier ? number_format($param_multiplier, $num_decimal_places, ',', '') : '' ).'">
		</div>';

		// id,  rel_goods
		$param_type = (in_array($type, array('id', 'rel_goods')) && ! empty($params["type"]) ? $params["type"] : '');
		echo '
		<div id="param_type" class="unit params param_id param_rel_goods">
			<div class="infofield">'.$this->diafan->_('В этом поле загружается').'</div>
			<select name="param_type">
			<option value=""'.(! $param_type ? ' selected' : '').'>'.$this->diafan->_('Импортируемый ID').'</option>
			<option value="site"'.($param_type == 'site' ? ' selected' : '').'>'.$this->diafan->_('ID на сайте').'</option>
			<option value="article"'.($param_type == 'article' ? ' selected' : '').'>'.$this->diafan->_('Артикул').'</option>
			</select>
		</div>';

		// parent, cats
		$param_type = (in_array($type, array('cats', 'parent', 'brand')) && ! empty($params["type"]) ? $params["type"] : '');
		if($this->diafan->is_new && ! $param_type) $param_type = 'name'; // для новой записи по умолчанию "name"
		echo '
		<div id="param_cats_type" class="unit params param_cats param_parent param_brand">
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
			case 'param':
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

			case 'param':
				if(! $_POST["param_id"])
				{
					$this->diafan->set_error('param_id', 'Выберите характеристику');
				}
				else
				{
					$params = array("id" => $this->diafan->filter($_POST, "int", "param_id"));
					if($id = DB::query_result("SELECT id FROM {%s} WHERE cat_id=%d AND type='param' AND params='%s' AND trash='0' LIMIT 1", $this->table, $this->diafan->_route->cat, serialize($params)))
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
						"multiplier" => $this->diafan->filter($_POST, 'float', 'param_multiplier', 1),
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
				$params = array(
					"type" => in_array($_POST["param_cats_type"], array('site', 'name'))  ? $_POST["param_cats_type"] : '',
					'sequence_delimitor' => strip_tags($_POST["param_sequence_delimitor"]),
				);
				break;

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
				if(strpos($type, 'brand') !== false)
				{
					echo ' brand="true"';
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

		// дополнительная характеристика
		$param_select_type = $type == 'param' && ! empty($params["select_type"]) ? $params["select_type"] : '';
		echo '
		<div unit_id="param_id" class="unit params param_param box_refresh" field_id="'.$id.'">
			<div class="infofield">'.$this->diafan->_('Укажите характеристику')

			.' ('.$this->diafan->_('или').' <a href="'.BASE_PATH_HREF.'shop/param/'.(! empty($this->cache["site_id"][$cat_id]) ? 'site'.$this->cache["site_id"][$cat_id].'/' : '').'" title="'.$this->diafan->_('Добавить характеристику. После необходимо обновить характеристики в текущей таблице. Для этого следует нажать кнопку «Обновить».').'" target="_blank">'.$this->diafan->_('добавьте новую').'</a>)'
			.'</div>';
		$rows = DB::query_fetch_all("SELECT id, [name], type FROM {shop_param} WHERE trash='0' ORDER BY sort ASC, id ASC");
		echo '<select name="param_id[]">';
		echo '<option value="0">-</option>';
		foreach ($rows as $row)
		{
			echo '<option value="'.$row["id"].'"'
			.($type == 'param' && ! empty($params["id"]) && $params["id"] == $row["id"] ? ' selected="selected" ' : '' )
			.' type="'.$row["type"].'">'.$row["name"].'</option>';
		}
		echo '</select>';
		echo ' <i class="tooltip fa fa-refresh" title="'.$this->diafan->_("Обновить список, если характеристики были добавлены позже открытия этой страницы.").'"></i>
		</div>';

		echo '
		<div unit_id="param_select_type" class="unit params param_param">
			<div class="infofield">'.$this->diafan->_('Характеристика импортируется как:').'</div>
			<select name="param_select_type[]">
			<option value="value"'.($param_select_type == 'value' ? ' selected' : '').'>'.$this->diafan->_('Название').'</option>
			<option value="key"'.($param_select_type == 'key' ? ' selected' : '').'>'.$this->diafan->_('ID на сайте').'</option>
			</select>
		</div>';

		// цена, количество
		$param_delimitor = ($type == 'price' || $type == 'count') && ! empty($params["delimitor"]) ? $params["delimitor"] : '&';
		$param_second_delimitor = ($type == 'redirect' || $type == 'images') && ! empty($params["second_delimitor"]) ? $params["second_delimitor"] : '';
		if($is_new) $param_second_delimitor = '^'; // для новой записи по умолчанию "^"
		$param_select_type = ($type == 'price' || $type == 'count') && ! empty($params["select_type"]) ? $params["select_type"] : '';
		$param_count = $type == 'price' && ! empty($params["count"]) ? $params["count"] : '';
		$param_old_price = $type == 'price' && ! empty($params["old_price"]) ? $params["old_price"] : '';
		$param_currency = $type == 'price' && ! empty($params["currency"]) ? $params["currency"] : '';
		$param_select_currency = $type == 'price' && ! empty($params["select_currency"]) ? $params["select_currency"] : '';
		$param_multiplier = $type == 'price' && ! empty($params["multiplier"]) ? $params["multiplier"] : 1;

		$param_image = $type == 'price' && ! empty($params["image"]) ? $params["image"] : '';
		echo '
		<div unit_id="param_delimitor" class="unit params param_price param_count">
			<div class="infofield">'.$this->diafan->_('Разделитель параметров для зависимых цен, количества или валют').'</div>
			<div class="infobox">'.$this->diafan->_('Пример: 100.00&130.00&5 = цена 100, старая цена 130, 5 шт.').'</div>
			<input name="param_delimitor[]" type="text" value="'.$param_delimitor.'">
		</div>
		<div unit_id="param_price_select_type" class="unit params param_price param_count">
			<div class="infofield">'.$this->diafan->_('Определять параметры, влияющие на цену, по:').'</div>
			<select name="param_price_select_type[]">
			<option value="value"'.($param_select_type == 'value' ? ' selected' : '').'>'.$this->diafan->_('Названию').'</option>
			<option value="key"'.($param_select_type == 'key' ? ' selected' : '').'>'.$this->diafan->_('ID на сайте').'</option>

			</select>
		</div>
		<div unit_id="param_count" class="unit params param_price">
			<input name="param_count[]" unit_id="input_param_count" id="input_param_count_'.$uid.'" value="1" type="checkbox"'.($param_count ? ' checked' : '').'>
			<label for="input_param_count_'.$uid.'">'.$this->diafan->_('Указано количество').'</label>
		</div>
		<div unit_id="param_old_price" class="unit params param_price">
			<input name="param_old_price[]" unit_id="input_param_old_price" id="input_param_old_price_'.$uid.'" value="1" type="checkbox"'.($param_old_price ? ' checked' : '').'>
			<label for="input_param_old_price_'.$uid.'">'.$this->diafan->_('Указана старая цена').'</label>
		</div>
		<div unit_id="param_currency" class="unit params param_price">
			<input name="param_currency[]" unit_id="input_param_currency" id="input_param_currency_'.$uid.'" value="1" type="checkbox"'.($param_currency ? ' checked' : '').'>
			<label for="input_param_currency_'.$uid.'">'.$this->diafan->_('Указана валюта').'</label>
		</div>
		<div unit_id="param_select_currency" class="unit params param_price">
			<div class="infofield">'.$this->diafan->_('Как указана валюта?').'</div>
			<select name="param_select_currency[]">
			<option value="value"'.($param_select_currency == 'value' ? ' selected' : '').'>'.$this->diafan->_('Название валюты').'</option>
			<option value="key"'.($param_select_currency == 'key' ? ' selected' : '').'>'.$this->diafan->_('ID валюты').'</option>
			</select>
		</div>
		<div unit_id="param_image" class="unit params param_price">
			<input name="param_image[]" unit_id="input_param_image" id="input_param_image_'.$uid.'" value="1" type="checkbox"'.($param_image ? ' checked' : '').'>
			<label for="input_param_image_'.$uid.'">'.$this->diafan->_('Указаны привязанные к цене изображения').'</label>
		</div>';
		$param_multiplier = floatval($param_multiplier);
		if(($param_multiplier * 10000) % 10) $num_decimal_places = 4;
		elseif(($param_multiplier * 1000) % 10) $num_decimal_places = 3;
		elseif(($param_multiplier * 100) % 10) $num_decimal_places = 2;
		elseif(($param_multiplier * 10) % 10) $num_decimal_places = 1;
		else $num_decimal_places = 0;
		echo '
		<div unit_id="param_multiplier" class="unit params param_price">
		<div class="infofield">'.$this->diafan->_('Множитель (изменяет импортируемую цену).').'</div>
		<div class="infobox">'.$this->diafan->_('Пример: 1 (цена не изменится), 1.3 (цена увеличится на 30%), 0.8 (цена уменьшится на 20%) и т.д.').'</div>
			<input type="text" class="number" name="param_multiplier[]" unit_id="input_param_multiplier" id="input_param_multiplier_'.$uid.'" value="'.( $param_multiplier ? number_format($param_multiplier, $num_decimal_places, ',', '') : '' ).'">
		</div>';

		// id,  rel_goods
		$param_type = (in_array($type, array('id', 'rel_goods')) && ! empty($params["type"]) ? $params["type"] : '');
		echo '
		<div unit_id="param_type" class="unit params param_id param_rel_goods">
			<div class="infofield">'.$this->diafan->_('В этом поле загружается').'</div>
			<select name="param_type[]">
			<option value=""'.(! $param_type ? ' selected' : '').'>'.$this->diafan->_('Импортируемый ID').'</option>
			<option value="site"'.($param_type == 'site' ? ' selected' : '').'>'.$this->diafan->_('ID на сайте').'</option>
			<option value="article"'.($param_type == 'article' ? ' selected' : '').'>'.$this->diafan->_('Артикул').'</option>
			</select>
		</div>';

		// parent, cats
		$param_type = (in_array($type, array('cats', 'parent', 'brand')) && ! empty($params["type"]) ? $params["type"] : '');
		if($is_new && ! $param_type) $param_type = 'name'; // для новой записи по умолчанию "name"
		echo '
		<div unit_id="param_cats_type" class="unit params param_cats param_parent param_brand">
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
				case 'param':
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

				case 'param':
					if(empty($_POST["param_id"][$key]))
					{
						$this->set_errors('param_id', 'Укажите характеристику', $key);
					}
					else
					{
						$params = array("id" => $this->diafan->filter($_POST["param_id"][$key], "int"));
						$params = serialize($params);
						if(isset($variable_params["param"][$params]))
						{
							$this->set_errors('param_id', 'Поле с такими настройками уже существует', $key);
						}
						$variable_params["param"][$params] = true;
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
			"type", "name", "param_menu_id", "param_id", "param_select_type",
			"param_delimitor", "param_price_select_type", "param_count",
			"param_old_price", "param_currency", "param_select_currency",
			"param_image", "param_multiplier", "param_type", "param_cats_type",
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

				case 'param':
					$params = array(
							"id" => $this->diafan->filter($post, "int", "param_id"),
							"select_type" => $post["param_select_type"] == 'key' ? 'key' : 'value',
							'directory' => strip_tags($post["param_directory"])
						);
					break;

				case 'price':
					$params = array(
							"delimitor" => html_entity_decode($this->diafan->filter($post, "string", "param_delimitor")),
							"select_type" => $post["param_price_select_type"] == 'key' ? 'key' : 'value',
							"count" => ! empty($post["param_count"]) ? 1 : 0,
							"old_price" => ! empty($post["param_old_price"]) ? 1 : 0,
							"currency" => ! empty($post["param_currency"]) ? 1 : 0,
							"select_currency" => $post["param_select_currency"] == 'key' ? 'key' : 'value',
							"image" => ! empty($post["param_image"]) ? 1 : 0,
							"multiplier" => $this->diafan->filter($post, 'float', 'param_multiplier', 1),
						);
					break;

				case 'count':
					$params = array(
							"delimitor" => html_entity_decode($this->diafan->filter($post, "string", "param_delimitor")),
							"select_type" => $post["param_price_select_type"] == 'key' ? 'key' : 'value'
						);
					break;

				case 'id':
				case 'rel_goods':
					$params = array("type" => in_array($post["param_type"], array('site', 'article')) ? $post["param_type"] : '');
					break;

				case 'cats':
					$params = array(
						"type" => in_array($post["param_cats_type"], array('site', 'name'))  ? $post["param_cats_type"] : '',
						'sequence_delimitor' => strip_tags($post["param_sequence_delimitor"]),
					);
					break;

				case 'parent':
				case 'brand':
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

<?php
/**
 * Список описанных файлов
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
 * Shop_admin_importexport_category
 */
class Shop_admin_importexport_category extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'shop_import_category';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
				'help' => 'Краткое описание файла импорта (например, «Импорт товаров», «Импорт цен» и т. д.).',
			),
			'type' => array(
				'type' => 'select',
				'name' => 'Тип',
				'select' => array(
					'good' => 'Товары',
					'category' => 'Категории',
					'brand' => 'Производители',
				),
			),
			'delete_items' => array(
				'type' => 'checkbox',
				'name' => 'Удалять неописанные в файле импорта записи',
				'help' => 'Если Вы загружаете список новых товаров или категорий, и то, что уже есть на сайте не нужно, следует отметить эту опцию. На сайт импортируются новые товары из файла CSV, а все уже существующие товары или категории будут удалены, за исключением тех, что будут обновлены (определяется по идентификатору).',
			),
			'site_id' => array(
				'type' => 'function',
				'name' => 'Раздел сайта',
				'help' => 'Страница сайта с прикрепленным модулем «Магазин», для которой будет производится импорт.',
			),
			'cat_id' => array(
				'type' => 'function',
				'name' => 'Категория товаров',
				'help' => 'Возможность ограничить импорт/экспорт одной категорией магазина.',
			),
			'count_part' => array(
				'type' => 'numtext',
				'name' => 'Количество строк, выгружаемых за один проход скрипта',
				'help' => 'Время работы скрипта на большинстве хостингов ограничено, из-за чего скрипт может не успеть обработать весь файл за одну итерацию, если он объемный. Поэтому файл обрабатывается частями, а величину итерации можно задать этим параметром.',
				'default' => 20
			),
			'delimiter' => array(
				'type' => 'text',
				'name' => 'Разделитель данных в строке',
				'help' => 'Разделитель ячеек в строке файла CSV. По умолчанию ;',
				'default' => ";"
			),
			'end_string' => array(
				'type' => 'text',
				'name' => 'Обозначать конец строки символом',
				'help' => 'Если в строке содержатся символы перевода строки (например, в описании товара), то конец строки должен быть обозначен отдельным символом. Например, КОНЕЦ_СТРОКИ. Редко используется, это не обязательный параметр.'
			),
			'encoding' => array(
				'type' => 'text',
				'name' => 'Кодировка',
				'help' => 'Кодировка данных в файле CSV. Часто cp1251 или utf8. По умолчанию из Excell файлы CSV выходят в кодировке cp1251',
				'default' => 'cp1251',
			),
			'sub_delimiter' => array(
				'type' => 'text',
				'name' => 'Разделитель данных внутри поля',
				'help' => 'В некоторых полях (ячейках) может быть несколько данных (например, значение характеристики с типом «список с выбором нескольких значений» или несколько имен изображений для одного товара). В этом случае данные должны быть разделены этим разделителем.',
				'default' => '|',
			),
			'header' => array(
				'type' => 'checkbox',
				'name' => 'Первая строка – названия полей',
				'help' => 'Если отмечено, то добавляется описание полей в первой строке файла при экспорте, а при импорте первая строка игнорируется.',
			),
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
		'actions' => array(
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
	}

	/**
	 * Выводит список категорий
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Редактирование поля "Категория"
	 * 
	 * @return void
	 */
	public function edit_variable_cat_id()
	{
		if(! $this->diafan->configmodules("cat", "shop"))
		{
			return;
		}

		$rows = DB::query_fetch_all("SELECT id, [name], parent_id, site_id AS rel FROM {shop_category} WHERE trash='0' ORDER BY sort ASC LIMIT 1000");
		if(count($rows) == 1000)
		{
			return;
		}
		foreach ($rows as $row)
		{
			$cats[$row["parent_id"]][] = $row;
		}
		echo '
		<div class="unit" id="cat_id">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>';

		echo ' <select name="cat_id">
		<option value="" rel="0">'.$this->diafan->_('Все').'</option>';
		echo $this->diafan->get_options($cats, $cats[0], array($this->diafan->value));
		echo '</select>';

		echo '</div>';
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
}
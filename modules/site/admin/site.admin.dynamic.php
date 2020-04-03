<?php
/**
 * Редактирование динамических блоков на сайте
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
 * Site_admin_dynamic
 */
class Site_admin_dynamic extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'site_dynamic';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main'       => array (
			'name'     => array(
				'type' => 'text',
				'name' => 'Название блока',
				'help' => 'Название блока, выводится перед содержимым блока, если не отмечена галка «Не выводить название блока».',
				'multilang' => true
			),
			'act'      => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'help' => 'Отображение блока на сайте. Если не отмечена, блок на сайте не будет показываться.',
				'multilang' => true
			),
			'title_no_show' => array(
				'type' => 'checkbox',
				'name' => 'Не выводить название блока',
				'help' => 'Если отмечено, заголовок перед содержимым блока автоматически выводиться не будет.'
			),			
			'type'  => array(
				'type' => 'select',
				'name' => 'Тип',
				'help' => 'Тип динамического блока.',
				'select' => array(
					'text' => 'строка',
					'numtext' => 'число',
					'date' => 'дата',
					'datetime' => 'дата и время',
					'textarea' => 'текстовое поле',
					'editor' => 'поле с визуальным редактором',
					'email' => 'электронный ящик',
				),
			),
			'module'   => array(
				'type' => 'function',
				'name' => 'Прикрепить к модулям',
				'help' => 'Редактировать поле только в указанных модуля и для указанных типов элементов модуля.',
				'select' => array(
					'element' => 'элементам',
					'cat' => 'категориям',
					'brand' => 'брендам',
				),
			),
			'text'     => array(
				'type' => 'textarea',
				'name' => 'Подсказка для поля',
				'help' => 'Будет выведено при редактировании содержимого блока в модуле.'
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
				'help' => 'Изменяется после первого сохранения. Показывает, кто из администраторов сайта первый правил текущий блок.'
			),
			'timeedit' => array(
				'type' => 'text',
				'name' => 'Время последнего изменения',
				'help' => 'Изменяется после сохранения элемента. Отдается в заголовке *Last Modify*.',
			),
			'access'        => array(
				'type' => 'function',
				'name' => 'Доступ',
				'help' => 'Если отметить опцию «Доступ только», блок увидят только авторизованные на сайте пользователи, отмеченных типов (администратору сайта).',
			),
			'date_period' => array(
				'type' => 'date',
				'name' => 'Период показа',
				'help' => 'Если выставить, текущий блок будет опубликован на сайте в указанный период. В иное время пользователи сайта блок не будут видеть (администратору сайта).'
			),
			'hr_period' => 'hr',
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Изменить положение текущего блока среди других блоков. Используется для удобство администрирования блоков (администратору сайта).'
			)
		)
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
			'name' => 'Прикреплен к модулям',
			'no_important' => true,
			'select' => array(
				'element' => 'элементы',
				'cat' => 'категории',
				'brand' => 'бренды',
			),
		),
		'type' => array(
			'name' => 'Тип',
			'type' => 'select',
			'sql' => true,
			'no_important' => true,
		),
		'actions' => array(
			'act' => true,
			'trash' => true,
		),
	);

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить динамический блок');
	}

	/**
	 * Выводит список страниц сайта
	 * @return void
	 */
	public function show()
	{
		echo '<p>'.$this->diafan->_('Содержимое блоков на сайте выводится с помощью шаблонного тега %s. К указанным модулям добавится поле, которое затем можно заполнять различным контентом.', '<code><span style="color: #000000"><span style="color: #0000BB">&lt;insert</span> <span style="color: #007700">name=</span><span style="color: #DD0000">&quot;show_dynamic&quot;</span> <span style="color: #007700">module=</span><span style="color: #DD0000">&quot;site&quot;</span> <span style="color: #007700">id=</span><span style="color: #DD0000">&quot;...&quot</span><span style="color: #0000BB">&gt;</span></span></code>').'</p>';
		$this->diafan->list_row();
	}

	/**
	 * Выводит модули и тип элемента, к которым прикреплен блок
	 * 
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_module_name($row, $var)
	{
		if(! isset($this->cache["prepare"]["module"]))
		{
			$this->cache["prepare"]["module"] = DB::query_fetch_key_array(
				"SELECT element_type, dynamic_id, module_name FROM {site_dynamic_module}"
				." WHERE dynamic_id IN (%s)",
				implode(",", $this->diafan->rows_id),
				"dynamic_id"
			);
		}
		$element_type = '';
		$modules = array();
		if(! empty($this->cache["prepare"]["module"][$row["id"]]))
		{
			foreach($this->cache["prepare"]["module"][$row["id"]] as $r)
			{
				$element_type = '';
				if(! empty($var["select"][$r["element_type"]]))
				{
					$element_type = $var["select"][$r["element_type"]];
				}
				$modules[] = $this->diafan->_(! empty($this->diafan->title_modules[$r["module_name"]]) ? $this->diafan->title_modules[$r["module_name"]] : $r["module_name"]).($r["element_type"] != 'element' ? ', '.$element_type : '');
			}
		}
		$modules = array_unique($modules);

		$text = '<div'.($var["class"] ? ' class="'.$var["class"].'"' : '').'>'.implode(', ', $modules).'</div>';

		return $text;
	}
	/**
	 * Редактирование поля "Прикрепить к модулям"
	 *
	 * @return void
	 */
	public function edit_variable_module()
	{
		$module_name = array();
		$element_type = 'element';
		if(! $this->diafan->is_new)
		{
			$rows = DB::query_fetch_all("SELECT * FROM {site_dynamic_module} WHERE dynamic_id=%d AND module_name<>''", $this->diafan->id);
			foreach($rows as $row)
			{
				$module_name[] = $row["module_name"];
				$element_type = $row["element_type"];
			}
		}
		echo '
		<div class="unit" id="module_name">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
			<select multiple="multiple" name="module_name[]" size="11">
			<option value="all"'.(empty($module_name) ? ' selected' : '').'>'.$this->diafan->_('Все').'</option>';

		$rows = DB::query_fetch_all("SELECT m.name, m.title FROM {modules} AS m"
		." INNER JOIN {admin} AS a ON m.name=a.rewrite WHERE m.site_page='1' OR m.name='site'"
		." ORDER BY a.group_id ASC, a.sort ASC, m.title ASC");
		$ns = array();
		foreach($rows as $row)
		{
			if(in_array($row["name"], $ns))
				continue;
			$ns[] = $row["name"];
			$cats[0][] = array("id" => $row["name"], "name" => $this->diafan->_($row["title"]));
		}
		echo $this->diafan->get_options($cats, $cats[0], $module_name).'
			</select>
		</div>
		<div class="unit" id="element_type">
			<div class="infofield">'.$this->diafan->_('Прикрепить в модуле к').'</div>
			<select name="element_type">
			<option value="">'.$this->diafan->_('Всем').'</option>';
			foreach($this->diafan->variable('module', 'select') as $k => $v)
			{
				echo '<option value="'.$k.'"'.($element_type == $k ? ' selected' : '').'>'.$this->diafan->_($v).'</option>';
			}
			echo '</select>
		</div>';
	}

	/**
	 * Сохранение поля "Прикрепить к модулям"
	 * @return void
	 */
	public function save_variable_module()
	{
		if(! $this->diafan->is_new)
		{
			DB::query("DELETE FROM {site_dynamic_module} WHERE dynamic_id=%d", $this->diafan->id);
		}
		if(! empty($_POST["module_name"]) && ! in_array("all", $_POST["module_name"]))
		{
			foreach($_POST["module_name"] as $module_name)
			{
				DB::query("INSERT INTO {site_dynamic_module} (dynamic_id, module_name, element_type) VALUES (%d, '%h', '%h')", $this->diafan->id, $module_name, $_POST["element_type"]);
			}
		}
		else
		{
			DB::query("INSERT INTO {site_dynamic_module} (dynamic_id) VALUES (%d)", $this->diafan->id);
		}
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("site_dynamic_element", "dynamic_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("site_dynamic_module", "dynamic_id IN (".implode(",", $del_ids).")");
	}
}
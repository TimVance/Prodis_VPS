<?php
/**
 * Редактирование категорий меню
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
 * Menu_admin_category
 */
class Menu_admin_category extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'menu_category';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'number' => array(
				'type' => 'function',
				'name' => 'ID меню',
				'help' => 'Номер элемента в БД (Веб-мастеру и программисту).',
				'no_save' => true,
			),		
			'name' => array(
				'type' => 'text',
				'name' => 'Название меню',
				'help' => 'Название меню, отображается на сайте перед меню, если отмечен параметр «Показывать заголовок меню».',
				'multilang' => true,
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'help' => 'Показывать ли меню на сайте.',
				'default' => true,
				'multilang' => true,
			),
			'show_title' => array(
				'type' => 'checkbox',
				'name' => 'Показывать заголовок меню',
				'help' => 'Если отмечено, перед пунктами меню выведется название меню (администратору сайта).',
			),
			'show_all_level' => array(
				'type' => 'checkbox',
				'name' => 'Раскрывать все пункты меню',
				'help' => 'Если отмечено, в меню будут выводиться все пункты меню, включая вложенные. Иначе вложенные пункты будут появляться только при переходе на родительский пункт. (администратору сайта).',
			),
			'hide_parent_link' => array(
				'type' => 'checkbox',
				'name' => 'Не отображать ссылку на элемент, если он имеет дочерние пункты',
				'help' => 'Если отмечено, пункты меню не будут ссылками, если у них есть вложенные пункты. (администратору сайта).',
			),
			'current_link' => array(
				'type' => 'checkbox',
				'name' => 'Текущий пункт меню как ссылка',
				'help' => 'Если отмечено, активный пункт меню останется ссылкой. (администратору сайта).',
			),
			'only_image' => array(
				'type' => 'checkbox',
				'name' => 'Не отображать имя пункта меню, если используется изображние',
				'help' => 'Если к пункту меню прикреплено изображение, то имя пункта отображаться не будет. (администратору сайта).',
			),
			'site_ids' => array(
				'type' => 'function',
				'name' => 'Отображать на страницах',
				'help' => 'Выбор отдельных страниц сайта, где будет показываться меню. Удерживайте CTRL, чтобы выбрать несколько страниц (администратору сайта).'
			),
			'access' => array(
				'type' => 'function',
				'name' => 'Доступ',
				'help' => 'Если отметить опцию «Доступ только», категорию увидят только авторизованные на сайте пользователи, отмеченных типов. Не авторизованные, в том числе поисковые роботы, увидят «404 Страница не найдена» (администратору сайта).',
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Редактирование порядка следования категории в списке. Поле доступно для редактирования только для категорий, отображаемых на сайте.',
			),
			'menu_template' => array(
				'type' => 'function',
				'name' => 'Шаблон вывода меню',
				'help' => 'Шаблон будет использован, если в шаблонном теге show_block указан атрибут *template="select"*. (Веб-мастеру и программисту. Не меняйте этот параметр, если не уверены в результате!)',
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
			'act' => true,
			'trash' => true,
		),
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'category', // часть модуля - категории
	);

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить меню');
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
	 * Проверяет можно ли выполнять действия с текущим элементом строки
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param string $action действие
	 * @return boolean
	 */
	public function check_action($row, $action = '')
	{
		if($action == 'act')
		{
			return true;
		}
		// нельзя удалить первую категорию меню
		if($row["id"] == 1)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	* Редактирование поля "Шаблон меню"
	* @return void
	*/
	public function edit_variable_menu_template()
	{
		$template = array();
		$rows = Custom::read_dir("modules/menu/views");
		foreach($rows as $file)
		{
			if(preg_match('/(show_block.*?)\.php/', $file, $mathes))
			{
				$key = $mathes[1];
				$name = $mathes[1];
				$template[$key] = $name;
			}
		}

		$current_template = DB::query_result("SELECT menu_template FROM {menu_category} WHERE id=%d", $this->diafan->id);

		echo '
		<div class="unit" id="theme_list">
			<div class="infofield">
				'.$this->diafan->variable_name("menu_template").$this->diafan->help().'
			</div>
			<select name="menu_template_list">
				<option value="">-</option>';
		foreach ($template as $key => $value)
		{
			echo '<option value="'.$key.'"'.( $current_template == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '</select>
		</div>';
	}

	/**
	 * Редактирование поля "Расположение"
	 *
	 * @return void
	 */
	public function edit_variable_site_ids()
	{
		$show_in_site_id = array();
		if(! $this->diafan->is_new)
		{
			$show_in_site_id = DB::query_fetch_value("SELECT site_id FROM {menu_category_site_rel} WHERE element_id=%d AND site_id>0", $this->diafan->id, "site_id");
		}
		echo '
		<div class="unit" id="site_ids">
		<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
		<select multiple name="'.$this->diafan->key.'[]" size="11">
		<option value="all"'.(empty($show_in_site_id) ? ' selected' : '').'>'.$this->diafan->_('Все').'</option>';

		$cats = DB::query_fetch_key_array("SELECT id, [name], parent_id FROM {site} WHERE trash='0' AND [act]='1' AND id<>%d ORDER BY sort ASC, id ASC", ! $this->diafan->is_new ? $this->diafan->id : 0, "parent_id");
		echo $this->diafan->get_options($cats, $cats[0], $show_in_site_id).'
			</select>
		</div>';
	}

	/**
	 * Сохранение поля "Шаблон меню"
	 * @return void
	 */
	public function save_variable_menu_template()
	{
		if(! empty($_POST['menu_template_list']))
		{
			$this->diafan->set_query("menu_template='%s'");
			$this->diafan->set_value($_POST['menu_template_list']);
		}
		else
		{
			$this->diafan->set_query("menu_template='%s'");
			$this->diafan->set_value('');
		}
	}

	/**
	 * Сохранение поля "Расположение"
	 * @return void
	 */
	public function save_variable_site_ids()
	{
		$this->diafan->update_table_rel("menu_category_site_rel", "element_id", "site_id", ! empty($_POST['site_ids']) ? $_POST['site_ids'] : array(), $this->diafan->id, $this->diafan->is_new);
	}
}
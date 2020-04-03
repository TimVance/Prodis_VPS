<?php
/**
 * Редактирование пунктов меню
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
 * Menu_admin
 */
class Menu_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'menu';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название пункта меню',
				'help' => 'Это название выводится как пункт меню на сайте.',
				'multilang' => true,
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'help' => 'Показывать ли пункт меню на сайте или временно скрыть.',
				'default' => true,
				'multilang' => true,
			),
			'module_name' => array(
				'type' => 'function',
				'name' => 'Ссылка с пункта меню',
				'help' => 'Адрес ссылки, куда ведет текущий пункт меню. Заполняется автоматически при отметке галки «Показывать в меню» у элемента на сайте. Возможно указать вручную, как на внутреннюю страницу сайта, так и на любой другой сайт.',
				
			),
			'target_blank' => array(
				'type' => 'checkbox',
				'name' => 'Открывать в новом окне',
				'help' => 'Если отмечена, клик пользователя по пункту меню на сайте откроет ссылку в новом окне.',
			),			
			'attributes' => array(
				'type' => 'text',
				'name' => 'Атрибуты HTML ссылки',
				'help' => 'HTML-код, выводимый в виде атрибутов для тега внутри ссылки `<a *** ></a>` (Веб-мастеру и программисту).',
			),
			'h1' => array(
				'type' => 'title',
				'name' => 'Свойства пункта',
			),
			'cat_id' => array(
				'type' => 'function',
				'name' => 'Принадлежит к меню',
				'help' => 'Выбор меню, к которому относится текущий пункт.',
			),
			'parent_id' => array(
				'type' => 'function',
				'name' => 'Вложенность: принадлежит',
				'help' => 'Перемещение текущего пункта меню и всех его подпунктов в принадлежность другому пункту меню (администратору сайта).'
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Изменить положение текущего пункта меню среди других пунктов (администратору сайта).'
			),
			'access' => array(
				'type' => 'none',
				'name' => 'Доступ',
				'hide' => true,
			),
			'images' => array(
				'type' => 'module',
				'name' => 'Изображение',
				'help' => 'Назначить текущему пункту меню изображение. Тогда ссылкой будет не название пункта, а прикрепленное изображение.',
				'count' => 1,
			),
			'text' => array(
				'type' => 'textarea',
				'name' => 'Описание',
				'help' => 'Краткое описание выводиться для пункта меню на сайте.',
				'multilang' => true,
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
		'plus' => array(),
		'name' => array(
			'name' => 'Название'
		),
		'actions' => array(
			'add' => true,
			'act' => true,
			'trash' => true,
		),
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'element', // используются группы
		'category_flat', // категории не содержат вложенности
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		if ($this->diafan->is_action("edit"))
		{
			$this->diafan->variable_unset('access');
		}
	}

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить пункт меню');
	}

	/**
	 * Выводит списко ответов
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Формирует часть SQL-запрос для списка элементов, отвечающую за сортировку
	 *
	 * @return string
	 */
	public function sql_query_order()
	{
		$order = " ORDER BY e.cat_id ASC, e.act".$this->diafan->lang_act.' DESC, e.sort ASC';
		return $order;
	}

	/**
	 * Редактирование поля "Ссылка"
	 * @return void
	 */
	public function edit_variable_module_name()
	{
		if (! $this->diafan->is_new)
		{
			if($this->diafan->values('othurl'))
			{
				$link = $this->diafan->values('othurl');
			}
			else
			{
				$link = BASE_PATH.$this->diafan->_route->link(0, $this->diafan->values('element_id'), $this->diafan->values('module_name'), $this->diafan->values('element_type'));
			}
			
			
		}
		echo '
		<div class="unit" id="module_name">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>  
			<input type="text" name="'.$this->diafan->key.'" value="'.(! empty($link) ? $link : '').'" placeholder="'.BASE_PATH.'">
			<a href="javascript:void(0)" class="menu_check"><i class="fa fa-pencil"></i> '.$this->diafan->_('Выбрать страницу сайта', false).'</a>
		</div>';
	}

	/**
	 * Сохранение поля "Элемент модуля"
	 * @return void
	 */
	public function save_variable_module_name()
	{
		if(empty($_POST['module_name']))
		{
			return TRUE;
		}	    
		
		$link = preg_replace('/'.str_replace('/', '\\/', BASE_PATH).'/', '', $_POST['module_name']);
		$link = preg_replace('/^\//', '', $link);
		if(ROUTE_END == '/')
		{
			$link = preg_replace('/\/$/', '', $link);
		}
		if($row = $this->diafan->_route->search($link))	
		{
			$this->diafan->set_query("module_name='%s'");
			$this->diafan->set_value($row['module_name']);
			$_POST['module_name'] = $row['module_name'];
			   
			$this->diafan->set_query("element_id=%d");
			$this->diafan->set_value($row['element_id']);
			$_POST['element_id'] = $row['element_id'];
			   
			$this->diafan->set_query("element_type='%s'");
			$this->diafan->set_value($row['element_type']);
			$_POST['element_type'] = $row['element_type'];
			
			$this->diafan->set_query("othurl='%s'");
			$this->diafan->set_value('');
		}
		else
		{
			$this->diafan->set_query("othurl='%s'");
			$this->diafan->set_value($_POST['module_name']);
		}   
	}

	/**
	 * Сохранение поля "Доступ, период показа"
	 * @return void
	 */
	public function save_variable_access()
	{
		if (empty($_POST["element_id"]))
		{
			$this->diafan->set_query("access='%d'");
			$this->diafan->set_value(0);
			$this->diafan->set_query("date_start=%d");
			$this->diafan->set_value(0);
			$this->diafan->set_query("date_finish=%d");
			$this->diafan->set_value(0);
			return;
		}
		$element = DB::query_fetch_array("SELECT * FROM {%h} WHERE id=%d LIMIT 1", $this->diafan->table_element_type($_POST["module_name"], $_POST['element_type']), $_POST["element_id"]);

		$this->diafan->set_query("access='%d'");
		$this->diafan->set_value($element["access"]);
		if(! empty($element["date_start"]))
		{
			$this->diafan->set_query("date_start=%d");
			$this->diafan->set_value($element["date_start"]);
		}
		if(! empty($element["date_finish"]))
		{
			$this->diafan->set_query("date_finish=%d");
			$this->diafan->set_value($element["date_finish"]);
		}
	}

	/**
	 * Сохранение поля "Родитель"
	 * 
	 * @return void
	 */
	public function save_variable_parent_id()
	{
		if (!$this->diafan->is_new && $_POST["cat_id"] != $this->diafan->values("cat_id"))
		{
			if ($_POST["parent_id"] && DB::query_result("SELECT cat_id FROM {menu} WHERE id=%d LIMIT 1", $_POST["parent_id"]) != $_POST["cat_id"])
			{
				$_POST["parent_id"] = 0;
			}
		}
		parent::__call('save_variable_parent_id', array());
	}

	/**
	 * Сохранение поля "Атрибуты ссылки"
	 * @return void
	 */
	public function save_variable_attributes()
	{
		$this->diafan->set_query("attributes='%s'");
		$this->diafan->set_value($_POST["attributes"]);
	}
}
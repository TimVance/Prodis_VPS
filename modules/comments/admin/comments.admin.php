<?php
/**
 * Редактирование комментариев
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
 * Comments_admin
 */
class Comments_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'comments';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'created' => array(
				'type' => 'datetime',
				'name' => 'Дата добавления',
				'help' => 'В формате дд.мм.гггг чч:мм.',
			),
			'user_id' => array(
				'type' => 'function',
				'name' => 'Пользователь',
				'help' => 'Пользователь, добавивший комментарий (если комментарий добавлен зарегистрированным пользователем).',
			),
			'text' => array(
				'type' => 'textarea',
				'name' => 'Комментарий',
			),
			'param' => array(
				'type' => 'function',
				'name' => 'Дополнительные поля',
				'help' => 'Поля, добавленные в конструкторе формы.',
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'help' => 'Если не отмечена, комментарий не будет виден на сайте.',
				'default' => true,
			),
			'hr2' => 'hr',
			'element_id' => array(
				'type' => 'function',
				'name' => 'Комментарий к',
				'help' => 'Объект, к которому прикреплены комментарии, ссылка на все комментарии к этой странице.',
				'disabled' => true,
			),
			'hr1' => 'hr',
			'parent_id' => array(
				'type' => 'function',
				'name' => 'Вложенность: принадлежит',
				'help' => 'Комментарий верхнего уровня.',
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'plus' => array(),
		'created' => array(
			'name' => 'Дата и время',
			'type' => 'datetime',
			'sql' => true,
			'no_important' => true,
		),
		'name' => array(
			'name' => 'Комментарий',
			'variable' => 'text',
		),
		'element_id' => array(
			'sql' => true,
			'no_important' => true,
		),
		'element_type' => array(
			'sql' => true,
			'type' => 'none',
			'no_important' => true,
		),
		'module_name' => array(
			'sql' => true,
			'no_important' => true,
		),
		'adapt' => array(
			'class_th' => 'item__th_adapt',
		),
		'separator' => array(
			'class_th' => 'item__th_seporator',
		),
		'actions' => array(
			'act' => true,
			'trash' => true,
		),
	);

	/**
	 * @var array поля для фильтра
	 */
	public $variables_filter = array (
		'text' => array(
			'type' => 'text',
			'name' => 'Искать по комментарию',
		),
		'user_id' => array(
			'type' => 'none',
		),
		'param' => array(
			'type' => 'function',
		),
	);

	/**
	 * Выводит контент модуля
	 *
	 * @return void
	 */
	public function show()
	{
		if(! empty($_GET["rew"]))
		{
			$res = explode('/', $_GET["rew"]);
			if(count($res) == 3)
			{
				$this->diafan->get_nav .= ($this->diafan->get_nav ? '&' : '?').'rew='.$this->diafan->filter($_GET, "url", "rew");
				$this->diafan->where = " AND module_name='".$this->diafan->filter($res[0], "sql")."' AND element_id=".$this->diafan->filter($res[2], "int")." AND element_type='".$this->diafan->filter($res[1], "sql")."'";
			}
		}

		$this->diafan->list_row();

		if (! $this->diafan->count)
		{
			echo '<p><b>'.$this->diafan->_('Комментариев нет.').'</b><br>'.$this->diafan->_('Комментарии оставляются посетителями из пользовательской части сайта.').'</p>';
		}
	}

	/**
	 * Выводит объект, к которому прикреплен комментарий, в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_element_id($row, $var)
	{
		if (empty($this->cache["elements"][$row["module_name"]][$row["element_type"]][$row["element_id"]]))
		{
			$table = $this->diafan->table_element_type($row["module_name"], $row["element_type"]);
			$name = DB::query_result("SELECT ".($row["module_name"] != 'faq' ? '[name]' : '[anons]')." FROM {".$table."} WHERE id=%d LIMIT 1", $row["element_id"]);
			$name = $this->diafan->short_text($name);
			$this->cache["elements"][$row["module_name"]][$row["element_type"]][$row["element_id"]] = ($name ? $name : $row["element_id"]);
		}
		return '<div class="no_important">'.$this->cache["elements"][$row["module_name"]][$row["element_type"]][$row["element_id"]].'</div>';
	}

	/**
	 * Выводит название модуля в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_module_name($row, $var)
	{
		if(! empty($this->diafan->title_modules[$row["module_name"]]))
		{
			$row["module_name"] = $this->diafan->title_modules[$row["module_name"]];
		}
		return '<div class="no_important">'.$this->diafan->_($row["module_name"])
		.($row["element_type"] == 'cat' ? ', '.$this->diafan->_('категория') : '')
		.'</div>';
	}

	/**
	 * Редактирование поля "Объект"
	 *
	 * @return void
	 */
	public function edit_variable_element_id()
	{
		$element_type = $this->diafan->element_type();

		$link = BASE_PATH.$this->diafan->_route->link(0, $this->diafan->value, $this->diafan->values("module_name"), $this->diafan->values("element_type"));

		echo '
		<div class="unit">
			<b>'.$this->diafan->variable_name().'</b>
			<a href="'.$link.'" target="_blank">'.$link.'</a>'.$this->diafan->help().'
			<br>
			('.$this->diafan->_('Посмотреть').' <a href="'.$this->diafan->get_admin_url('page').'?rew='.$this->diafan->values("module_name").'/'.$element_type.'/'.$this->diafan->value.'">'.$this->diafan->_('все комментарии').'</a> '.$this->diafan->_('к этому объекту').')
		</div>';
	}

	/**
	 * Редактирование поля "Дополнительные параметры"
	 *
	 * @return void
	 */
	public function edit_variable_param()
	{
		parent::__call('edit_variable_param', array("AND (module_name='".$this->diafan->values("module_name")."' OR module_name='')"));
	}

	/**
	 * Сохранение поля "Дополнительные параметры"
	 *
	 * @return void
	 */
	public function save_variable_param()
	{
		parent::__call('save_variable_param', array(" AND (module_name='' OR module_name='".$this->diafan->values("module_name")."')"));
	}
}

<?php
/**
 * Список ответов пользователей
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
 * Votes_admin_element
 */
class Votes_admin_userversion extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'votes_userversion';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'text' => array(
				'type' => 'text',
				'name' => 'Ответ',
				'help' => 'Ответ пользователя.',
			),			
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'name' => array(
			'name' => 'Ответ',	
			'variable' => 'text'
		),
		'votes_id' => array(
			'sql' => true,
			'type' => 'none',
		),
		'actions' => array(
			'trash' => true,
		),
	);

	/**
	 * @var array поля для фильтра
	 */
	public $variables_filter = array (
		'votes_id' => array(
			'type' => 'select',
			'name' => 'Искать по опросу',
		),
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		if ($this->diafan->_route->cat)
		{
			$this->diafan->where .= " AND e.votes_id=".$this->diafan->_route->cat;
		}
		$select = array();
		$this->cache["cats"][0] = DB::query_fetch_all("SELECT v.id, v.[name] FROM {votes} as v"
			." INNER JOIN {votes_userversion} as u ON u.votes_id=v.id AND u.trash='0'"
			." WHERE v.trash='0' GROUP BY v.id ORDER BY v.sort ASC, v.id ASC");
		foreach ($this->cache["cats"][0] as &$row)
		{
			$row["name"] = $this->diafan->short_text($row["name"], 80);
			$this->cache["votes_cats"][$row["id"]] = $row["name"];
			$select[$row["id"]] = $row["name"];
		}
		$this->diafan->variable_filter("votes_id", 'select', $select);
	}

	/**
	 * Выводит список ответов
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();

		if (! $this->diafan->count)
		{
			echo '<p><b>'.$this->diafan->_('Ответов нет.').'</b><br>'.$this->diafan->_('Свои варианты ответов посетители оставляют в пользовательской части сайта, если у опроса отмечена опция «Свой вариант ответа».').'</p>';
		}
	}

	/**
	 * Выводит название раздела/категории в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_parent($row, $var)
	{
		$text = '<div class="categories">'.(! empty($this->cache["votes_cats"][$row["votes_id"]]) ? $this->cache["votes_cats"][$row["votes_id"]] : '').'</div>';
		return $text;
	}
}
<?php
/**
 * Редактирование рейтигов
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
 * Rating_admin
 */
class Rating_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'rating';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'element_id' => array(
				'type' => 'function',
				'name' => 'Объект',
				'disabled' => true,
			),
			'rating' => array(
				'type' => 'floattext',
				'name' => 'Средняя оценка',
				'help' => 'Числовое значение, вычисляется автоматически, как отношение суммы баллов к числу проголосовавших.',
			),
			'count_votes' => array(
				'type' => 'numtext',
				'name' => 'Количество голосовавших',
				'help' => 'Числовое значение.',
			),
			'created' => array(
				'type' => 'datetime',
				'name' => 'Дата последнего голосования',
				'help' => 'Устанавливается после изменения рейтинга, в формате дд.мм.гггг чч:мм.',
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'created' => array(
			'name' => 'Дата и время',
			'type' => 'datetime',
			'sql' => true,
			'no_important' => true,
		),
		'name' => array(
			'text' => 'Редактировать'
		),
		'rating' => array(
			'name' => 'Средняя оценка',
			'sql' => true,
			'type' => 'text',
			'class' => 'num',
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
			'name' => 'Модуль',
			'no_important' => true,
		),
		'adapt' => array(
			'class_th' => 'item__th_adapt',
		),
		'separator' => array(
			'class_th' => 'item__th_seporator',
		),				
		'actions' => array(
			'trash' => true,
		),
	);

	/**
	 * Выводит список оценок
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
		
		if (! $this->diafan->count)
		{
			echo '<p><b>'.$this->diafan->_('Нет оценок рейтинга.').'</b><br>'.$this->diafan->_('Рейтинг выставляют посетители из пользовательской части сайта.').'</p>';
		}
	}

	/**
	 * Выводит объект, которому поставлена оценка
	 * 
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_element_id($row, $var)
	{
		$table = $this->diafan->table_element_type($row["module_name"], $row["element_type"]);

		$name = DB::query_result("SELECT [name] FROM {%s} WHERE id='%d' LIMIT 1", $table, $row["element_id"]);
		return '<div class="no_important">'.($name ? $name : $row["element_id"]).'</div>';
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
	 * @return void
	 */
	public function edit_variable_element_id()
	{
		$link = BASE_PATH_HREF
		.$this->diafan->values("module_name").'/';
		switch($this->diafan->values("element_type"))
		{
			case 'element':
				break;

			case 'cat':
				$link .= 'category/';
				break;

			default:
				$link .= $this->diafan->values("element_type").'/';
				break;
		}
		$link .= 'edit'.$this->diafan->value.'/';
		
		echo '
		<div class="unit">
			<b>
				'.$this->diafan->variable_name().'
			</b>
			<a href="'.$link.'" target="_blank">'.$link.'</a>
		</div>';
	}
}
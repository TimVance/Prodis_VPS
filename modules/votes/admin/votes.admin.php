<?php
/**
 * Редактирование вопросов для голосования
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
 * Votes_admin
 */
class Votes_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'votes';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Вопрос',
				'multilang' => true,
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'help' => 'Публикация на сайте, активность.',
				'default' => true,
				'multilang' => true,
			),
			'no_result' => array(
				'type' => 'checkbox',
				'name' => 'Запретить показывать результаты голосования на сайте',
				'help' => 'Пользователи не смогут увидеть результаты голосования.',
			),
			'userversion' => array(
				'type' => 'checkbox',
				'name' => 'Пользователи могут дать свой вариант ответа',
				'help' => 'Позволяет пользователям добавлять свои варианты ответа.',
			),
			'answers' => array(
				'type' => 'function',
				'name' => 'Варианты ответа',
				'help' => 'Список вариантов ответа.',
			),
			'site_ids' => array(
				'type' => 'function',
				'name' => 'Показывать опрос на страницах сайта',
				'help' => 'Позволяет выбрать разделы сайта, на которых будет выводиться опрос.',
			),			
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Редактирование порядка отображения пункта. Поле доступно для редактирования только для незаблокированных опросов.',
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
			'name' => 'Вопрос'
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
		$this->diafan->addnew_init('Добавить опрос');
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
	 * Редактирование поля "Ответы"
	 * @return void
	 */
	public function edit_variable_answers()
	{
		$answers = array();
		if(! $this->diafan->is_new)
		{
			$answers = DB::query_fetch_all("SELECT id, [text], count_votes FROM {votes_answers} WHERE votes_id=%d ORDER BY sort ASC", $this->diafan->id);
		}
		echo '
		<div class="unit">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
			';
		foreach ($answers as $row)
		{
			echo '<div class="param">
				<input type="hidden" name="answer_id[]" value="'.$row["id"].'">
				<input type="text" name="answer_text[]" value="'.str_replace('"', '&quot;', $row["text"]).'">
				('.$row["count_votes"].')
				<span class="param_actions">
					<a href="javascript:void(0)" action="up_param" title="'.$this->diafan->_('Выше').'">↑</a>
					<a href="javascript:void(0)" action="down_param" title="'.$this->diafan->_('Ниже').'">↓</a>
					<a href="javascript:void(0)" action="delete_param" class="delete" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'"><i class="fa fa-close" title="'.$this->diafan->_('Удалить').'"></i></a>
				</span>
				</div>';
		}
		echo '<div class="param">
				<input type="hidden" name="answer_id[]" value="">
				<input type="text" name="answer_text[]" value="">
				<span class="param_actions">
					<a href="javascript:void(0)" action="up_param" title="'.$this->diafan->_('Выше').'">↑</a>
					<a href="javascript:void(0)" action="down_param" title="'.$this->diafan->_('Ниже').'">↓</a>
					<a href="javascript:void(0)" action="delete_param" class="delete" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'"><i class="fa fa-close" title="'.$this->diafan->_('Удалить').'"></i></a>
				</span>
			</div>
			<a href="javascript:void(0)" class="param_plus" title="'.$this->diafan->_('Добавить').'"><i class="fa fa-plus-square"></i> '.$this->diafan->_('Добавить').'</a>
		</div>';
	}

	/**
	 * Сохранение поля "Ответы"
	 * @return void
	 */
	public function save_variable_answers()
	{
		if(! empty($_POST["answer_text"]))
		{
			$sort = 1;
			foreach ($_POST["answer_text"] as $i => $text)
			{
				if(empty($text))
					continue;

				if(! empty($_POST["answer_id"][$i]))
				{
					DB::query("UPDATE {votes_answers} SET [text]='%h', sort=%d WHERE id=%d AND votes_id=%d", $text, $sort, $_POST["answer_id"][$i], $this->diafan->id);
					$id = intval($_POST["answer_id"][$i]);
					if($id)
					{
						$ids[] = $id;
					}
				}
				else
				{
					$ids[] = DB::query("INSERT INTO {votes_answers} ([text], sort, votes_id) VALUES ('%h', %d, %d)", $text, $sort, $this->diafan->id);
				}
				$sort++;
			}
		}
		DB::query("DELETE FROM {votes_answers} WHERE ".(! empty($ids) ? "id NOT IN(".implode(",", $ids).") AND" : "")." votes_id=%d", $this->diafan->id);
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("votes_site_rel", "element_id IN (".implode(",", $del_ids).")");
		$this->diafan->del_or_trash_where("votes_answers", "votes_id IN (".implode(",", $del_ids).")");
	}
}
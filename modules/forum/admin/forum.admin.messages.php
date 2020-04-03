<?php
/**
 * Редактирование сообщений
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
 * Forum_admin_messages
 */
class Forum_admin_messages extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'forum_messages';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'help' => 'Если не отмечено, то сообщение на сайте не отображается.',
				'default' => true,
			),
			'user_id' => array(
				'type' => 'function',
				'name' => 'Автор',
				'help' => 'Пользователь, добавивший сообщение.',
				'no_save' => true,
			),
			'created' => array(
				'type' => 'datetime',
				'name' => 'Дата',
				'help' => 'Вводится в формате дд.мм.гггг чч:мм.',
			),
			'user_update' => array(
				'type' => 'function',
				'name' => 'Редакция',
				'help' => 'Пользователь, отредактировавший сообщение и дата последней редакции.',
				'no_save' => true,
			),
			'forum_id' => array(
				'type' => 'function',
				'name' => 'Тема',
			),
			'text' => array(
				'type' => 'textarea',
				'name' => 'Сообщение',
			),
			'attachments' => array(
				'type' => 'module',
				'name' => 'Прикрепленные файлы',
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
			'name' => 'Сообщение',
			'variable' => 'text',
		),
		'forum_id' => array(
			'name' => 'Тема',
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
	 * Выводит список сообщений
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Выводит блок форума в списке
	 * @return string
	 */
	public function list_variable_forum_id($row)
	{
		if(! isset($this->cache["prepare"]["forums"]))
		{
			$forums = array();
			foreach($this->diafan->rows as $r)
			{
				$forums[] = $r["forum_id"];
			}
			$forums = array_unique($forums);
			if($forums)
			{
				$forum_rows = DB::query_fetch_key_value(
					"SELECT id, name FROM {forum}"
					." WHERE id IN (%s)",
					implode(",", $forums),
					"id", "name"
				);
				foreach($forum_rows as $k => $v)
				{
					$this->cache["prepare"]["forums"][$k] = '<a href="'.BASE_PATH_HREF.'forum/edit'.$k.'/">'.$v.'</a>';
				}
			}
		}
		return '<div class="no_important">'.(! empty($this->cache["prepare"]["forums"][$row["forum_id"]]) ? $this->cache["prepare"]["forums"][$row["forum_id"]] : '').'</div>';
	}

	/**
	 * Редактирование поля "Обновление"
	 * @return void
	 */
	public function edit_variable_user_update()
	{
		if (! $this->diafan->value)
			return;
		echo '
		<div class="unit">
			<b>'.$this->diafan->variable_name().':</b>
			<a href="'.BASE_PATH_HREF.'users/edit'.$this->diafan->value.'/">'
			.DB::query_result("SELECT CONCAT(fio, '(', name, ')') FROM {users} WHERE id=%d LIMIT 1", $this->diafan->value).'</a>, '.date("d.m.Y H:i", $this->diafan->values("date_update"))
			.$this->diafan->help().'
		</div>';
	}

	/**
	 * Редактирование поля "Тема"
	 *
	 * @return void
	 */
	public function edit_variable_forum_id()
	{
		if($this->diafan->values("parent_id"))
			return;

		$cats[0] = DB::query_fetch_all("SELECT id, name FROM {forum} WHERE trash='0' ORDER BY name ASC");

		echo '
		<div class="unit" id="forum_id">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
			<select name="forum_id">';
			if (! empty( $cats[0] ))
			{
				echo $this->diafan->get_options($cats, $cats[0], array($this->diafan->value));
			}
			echo '</select>
		</div>';
	}

	/**
	 * Сохранение поля "Тема"
	 *
	 * @return void
	 */
	public function save_variable_forum_id()
	{
		if($this->diafan->values("parent_id"))
			return;
		
		if($this->diafan->values("forum_id") == $_POST["forum_id"])
			return;

		$this->diafan->set_query("forum_id=%d");
		$this->diafan->set_value($_POST["forum_id"]);

		$childen = $this->diafan->get_children($this->diafan->id, "forum");
		if($childen)
		{
			DB::query("UPDATE {forum_messages} SET forum_id=%d WHERE id IN (%s)", $_POST["cat_id"], implode(',', $childen));
		}
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("forum_show", "element_id IN (".implode(",", $del_ids).")");
	}
}
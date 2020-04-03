<?php
/**
 * Редактирование тем форума
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
 * Forum_admin
 */
class Forum_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'forum';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
			),
			'user_id' => array(
				'type' => 'function',
				'name' => 'Автор',
			),
			'created' => array(
				'type' => 'datetime',
				'name' => 'Дата',
				'help' => 'Вводится в формате дд.мм.гггг чч:мм.',
			),			
			'cat_id' => array(
				'type' => 'function',
				'name' => 'Категория',
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'default' => true,
			),
			'prior' => array(
				'type' => 'checkbox',
				'name' => 'Закрепить тему (всегда сверху)',
			),
			'close' => array(
				'type' => 'checkbox',
				'name' => 'Закрыть тему',
			),
			'user_update' => array(
				'type' => 'function',
				'name' => 'Редакция',
				'help' => 'Пользователь, отредактировавший тему и дата последней редакции.',
				'no_save' => true,
			),
			'counter_view' => array(
				'type' => 'function',
				'name' => 'Количество просмотров',
				'no_save' => true,
			),
			'rewrite'       => array(
				'type' => 'function',
				'name' => 'Псевдоссылка',
				'help' => 'ЧПУ (человеко-понятные урл url), адрес страницы вида: *http://site.ru/psewdossylka/*. Смотрите параметры сайта.',
			),
			'map' => array(
				'type' => 'module',
				'name' => 'Индексирование для карты сайта',
				'help' => 'Тема автоматически индексируется для карты сайта sitemap.xml.',
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
			'name' => 'Название и категория'
		),
		'actions' => array(
			'view' => true,
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
		'category_no_multilang', // имя категории не переводиться
	);

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить тему');
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
	 * Выводит название категории в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_parent($row, $var)
	{
		if(! isset($this->cache["cats_name"]))
		{
			if($this->diafan->categories)
			{
				foreach($this->diafan->categories as $cat)
				{
					$this->cache["cats_name"][$cat["id"]] = $cat["name"];
				}
			}
		}
		if(! isset($this->cache["cats_name"][$row["cat_id"]]))
		{
			$this->cache["cats_name"][$row["cat_id"]] = '';
		}
		return '<div class="categories">'.$this->cache["cats_name"][$row["cat_id"]].'</div>';
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
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>'
			.DB::query_result("SELECT fio FROM {users} WHERE id=%d LIMIT 1", $this->diafan->value).', '.date("d.m.Y H:i", $this->diafan->values("date_update"))
		.'
		</div>';
	}

	/**
	 * Редактирование поля "Количество просмотров"
	 * @return void
	 */
	public function edit_variable_counter_view()
	{
		if ($this->diafan->is_new)
			return;
	
		echo '<div class="unit"><b>'.$this->diafan->variable_name().':</b> '.$this->diafan->value.'</div>';
	}

	/**
	 * Сохранение поля "Псевдоссылка"
	 * @return void
	 */
	public function save_variable_rewrite()
	{
		$_POST["site_id"] = DB::query_result("SELECT id FROM {site} WHERE module_name='forum' LIMIT 1");
		parent::__call('save_variable_rewrite', array());
	}
}
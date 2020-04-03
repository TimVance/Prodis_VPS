<?php
/**
 * Редактирование страниц административной части сайта
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
 * Admin_admin
 */
class Admin_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'admin';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
			),
			'rewrite' => array(
				'type' => 'text',
				'name' => 'Псевдоссылка',
				'help' => 'ЧПУ, адрес страницы вида: *http://site.ru/admin/psewdossylka/*.',
			),
			'group_id' => array(
				'type' => 'select',
				'name' => 'Группа',
				'help' => 'Логический блок в меню административной части.',
				'select' => array(
					1 => 'Контент',
					4 => 'Интернет магазин',
					2 => 'Интерактив',
					6 => 'DIAFAN.CMS',
					7 => 'Расширения CMS',
					3 => 'Сервис',
					5 => 'Настройки',
				),
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Показывать в меню',
				'help' => 'Возможность показать/скрыть в меню административной части.',
				'default' => true,
			),
			'docs' => array(
				'type' => 'text',
				'name' => 'Ссылка на документацию',
				'help' => 'Ссылка выводится в подвале сайта.',
			),
			'parent_id' => array(
				'type' => 'select',
				'name' => 'Вложенность: принадлежит',
				'help' => 'Перемещение текущей страницы в принадлежность другой страницы.',
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Изменить положение текущей страницы среди других страниц в меню.',
			),
			'add' => array(
				'type' => 'checkbox',
				'name' => 'Cсылка на добавление элемента в быстром меню',
				'default' => true,
			),
			'add_name' => array(
				'type' => 'text',
				'name' => 'Текст ссылки на добавление элемента в быстром меню',
				'default' => true,
				'depend' => 'add',
			),
			'icon_name' => array(
				'type' => 'text',
				'name' => 'Название иконки',
				'help' => 'Иконка модуля для административной части сайта.',
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
			'act' => true,
		),
	);

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить страницу');
	}

	/**
	 * Выводит контент модуля
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Редактирование поля "Псевдоссылка"
	 *
	 * @return void
	 */
	public function edit_variable_rewrite()
	{
		echo '
		<div class="unit">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
			<input type="text" name="'. $this->diafan->key.'" value="'
			.(! $this->diafan->is_new ? str_replace('"', '&quot;', $this->diafan->value) : '')
			.'"'.($this->diafan->value == 'admin' ? ' readonly' : '')
			.'>
		</div>';
	}

	/**
	 * Редактирование поля "Родитель"
	 *
	 * @return void
	 */
	public function edit_variable_parent_id()
	{
		$rows = DB::query_fetch_all("SELECT id, name FROM {admin} WHERE parent_id=0 AND id<>%d ORDER BY name ASC", $this->diafan->id);
		echo '
		<div class="unit">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
			<select name="'. $this->diafan->key.'">
			<option value="">-</option>';
			foreach($rows as $row)
			{
				echo '<option value="'.$row["id"].'"'.($row["id"] == $this->diafan->value ? ' selected' : '').'>'.$row["name"].'</option>';
			}
			echo '</select>
		</div>';
	}

	/**
	 * Валидация поля "Псевдоссылка"
	 *
	 * @return void
	 */
	public function validate_variable_rewrite()
	{}

	/**
	 * Сохранение поля "Псевдоссылка"
	 *
	 * @return void
	 */
	public function save_variable_rewrite()
	{
		$this->diafan->set_query("rewrite='%h'");
		$this->diafan->set_value($_POST["rewrite"]);
	}
}

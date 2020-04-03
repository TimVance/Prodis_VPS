<?php
/**
 * Редактирование блоков на сайте
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
 * Site_admin_blocks
 */
class Site_admin_blocks extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'site_blocks';

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
			'text'     => array(
				'type' => 'editor',
				'name' => 'Контент блока',
				'help' => 'Основное содержимое блока. Если отметить «Применить типограф», контент будет отформатирован согласно правилам экранной типографики с помощью [веб-сервиса «Типограф»](http://www.artlebedev.ru/tools/typograf/webservice/). Опция «HTML-код» позволяет отключить визуальный редактор для текущего поля. Значение этой настройки будет учитываться и при последующем редактировании.',
				'multilang' => true
			),
			'site_ids' => array(
				'type' => 'function',
				'name' => 'Отображать на страницах',
				'help' => 'Выбор отдельных страниц, где будет показываться блок. Удерживайте CTRL, чтобы выбрать несколько страниц.'
			),

		),
		'other_rows' => array (
			'number'        => array(
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
		$this->diafan->addnew_init('Добавить блок');
	}

	/**
	 * Выводит список страниц сайта
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
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
			$show_in_site_id = DB::query_fetch_value("SELECT site_id FROM {site_blocks_site_rel} WHERE element_id=%d AND site_id>0", $this->diafan->id, "site_id");
		}
		echo '
		<div class="unit" id="site_ids">
		<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
		<select multiple="multiple" name="'.$this->diafan->key.'[]" size="11">
		<option value="all"'.(empty($show_in_site_id) ? ' selected' : '').'>'.$this->diafan->_('Все').'</option>';

		$cats = DB::query_fetch_key_array("SELECT id, [name], parent_id FROM {site} WHERE trash='0' AND [act]='1' ORDER BY sort ASC, id ASC", "parent_id");
		echo $this->diafan->get_options($cats, $cats[0], $show_in_site_id).'
			</select>
		</div>';
	}

	/**
	 * Сохранение поля "Расположение"
	 * @return void
	 */
	public function save_variable_site_ids()
	{
		$this->diafan->update_table_rel("site_blocks_site_rel", "element_id", "site_id", ! empty($_POST['site_ids']) ? $_POST['site_ids'] : array(), $this->diafan->id, $this->diafan->is_new);
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("site_blocks_site_rel", "element_id IN (".implode(",", $del_ids).")");
	}
}
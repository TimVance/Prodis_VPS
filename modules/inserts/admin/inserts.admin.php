<?php
/**
 * Редактирование вставок
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if ( ! defined('DIAFAN'))
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
 * Inserts_admin
 */
class Inserts_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'inserts';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
				'help' => 'Название вставки.',
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'help' => 'Показывать ли пункт вставку на сайте или временно скрыть.',
				'default' => true,
				'multilang' => true,
			),
			'prefix' => array(
				'type' => 'select',
				'name' => 'Размещение',
				'select' => array(
					"replace" => "вместо метки",
					"before" => "перед меткой",
					"after" => "после метки",
				),
			),
			'tag' => array(
				'type' => 'text',
				'name' => 'Шаблонный тег-метка',
				'help' => 'Шаблонный тег, к которому привязывается вставка.',
			),
			'text' => array(
				'type' => 'textarea',
				'name' => 'Содержание вставки',
				'help' => 'HTML-код или шаблонный тег, добавляемый на страницу.',
			),
			'site_ids' => array(
				'type' => 'function',
				'name' => 'Отображать на страницах',
				'help' => 'Выбор отдельных страниц, где будет показываться вставка. Удерживайте CTRL, чтобы выбрать несколько страниц.'
			),
		),
		'other_rows' => array (
			'number' => array(
				'type' => 'function',
				'name' => 'Номер',
				'help' => 'Номер страницы в БД (веб-мастеру и программисту).',
				'no_save' => true,
			),
			'admin_id' => array(
				'type' => 'function',
				'name' => 'Редактор',
				'help' => 'Изменяется после первого сохранения. Показывает, кто из администраторов сайта первый правил текущую страницу.'
			),
			'timeedit' => array(
				'type' => 'text',
				'name' => 'Время последнего изменения',
				'help' => 'Изменяется после сохранения элемента. Отдается в заголовке *Last Modify*.',
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Изменить положение текущей страницы среди других страниц (администратору сайта).'
			),
			'date_period' => array(
				'type' => 'date',
				'name' => 'Период показа',
				'help' => 'Если заполнить, текущее объявление будет опубликована на сайте в указанный период. В иное время пользователи сайта объявление не будут видеть, получая ошибку 404 «Страница не найдена» (администратору сайта).'
			),
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
			'desc' => true,
		),
		'name' => array(
			'name' => 'Название'
		),
		'tag' => array(
			'type' => 'text',
			'sql' => true,
			'name' => 'Тег-метка',
		),
		'prefix' => array(
			'type' => 'select',
			'name' => 'Размещение',
			'sql' => true,
			'select' => array(
				"replace" => "вместо метки",
				"before" => "перед меткой",
				"after" => "после метки",
			),
		),
		'text' => array(
			'type' => 'text',
			'sql' => true,
			'name' => 'Cодержание',
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
		'tag' => array(
			'type' => 'text',
			'name' => 'Тег-метка',
		),
	);

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить вставку');
	}

	/**
	 * Выводит список страниц сайта
	 * @return void
	 */
	public function show()
	{
		// if(defined('IS_DEMO') && IS_DEMO)
		// {
		// 	echo '<div class="error">'.$this->diafan->_('Модуль "Вставка" в демо-версии не доступен.').'</div>';
		// 	return;
		// }
		$this->diafan->list_row();
	}

	/**
	 * Выводит тег-метку в списке элементов
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_tag($row, $var)
	{
		$text = '<div class="name" id="'.$row['id'].'">';
		$text .= '<a href="';
		$text .= $this->diafan->get_base_link($row);
		$text .= '" title="'.$this->diafan->_('Редактировать').' ('.$row["id"].')">'.htmlentities($row["tag"]).'</a>';
		$text .= '</div>';
		return $text;
	}

	/**
	 * Выводит содержимое вставки в списке элементов
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_text($row, $var)
	{
		$text = '<div class="name">';
		if(strpos($row["text"], '<insert') !== false)
		{
			$text .= htmlentities($row["text"]);
		}
		else
		{
			$text .= strip_tags($row["text"]);
		}
		$text .= '</a>';
		$text .= '</div>';
		return $text;
	}

	/**
	 * Генерирует форму редактирования/добавления элемента
	 *
	 * @return void
	 */
	public function edit()
	{
		// if(defined('IS_DEMO') && IS_DEMO)
		// {
		// 	echo '<div class="error">'.$this->diafan->_('Модуль "Вставка" в демо-версии не доступен.').'</div>';
		// 	return;
		// }
		parent::edit();
	}

	/**
	 * Редактирование поля "Страницы сайта"
	 *
	 * @return void
	 */
	public function edit_variable_site_ids()
	{
		$show_in_site_id = array();
		if(! $this->diafan->is_new)
		{
			$show_in_site_id = DB::query_fetch_value("SELECT site_id FROM {inserts_site_rel} WHERE element_id=%d AND site_id>0", $this->diafan->id, "site_id");
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
	 * Сохранение поля "Страницы сайта"
	 * @return void
	 */
	public function save_variable_site_ids()
	{
		$this->diafan->update_table_rel("inserts_site_rel", "element_id", "site_id", ! empty($_POST['site_ids']) ? $_POST['site_ids'] : array(), $this->diafan->id, $this->diafan->is_new);
	}

	/**
	 * Сохранение поля "Тег-метка"
	 * @return void
	 */
	public function save_variable_tag()
	{
		$this->diafan->set_query("`tag`='%s'");
		$this->diafan->set_value($_POST["tag"]);
	}

	/**
	 * Сохранение поля "Содержание вставки"
	 * @return void
	 */
	public function save_variable_text()
	{
		$this->diafan->set_query("`text`='%s'");
		$this->diafan->set_value($_POST["text"]);
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("inserts_site_rel", "element_id IN (".implode(",", $del_ids).")");
	}
}

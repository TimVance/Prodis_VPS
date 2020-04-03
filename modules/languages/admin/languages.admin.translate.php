<?php
/**
 * Редактирование перевода интерфейса
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
 * Languages_admin_translate
 */
class Languages_admin_translate extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'languages_translate';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'text' => array(
				'type' => 'textarea',
				'name' => 'Исходное слово (фраза)',
				'help' => 'Исходный текст на русском языке.',
			),
			'text_translate' => array(
				'type' => 'textarea',
				'name' => 'Перевод (переименование)',
				'help' => 'Перевод на указанном ниже языке.',
			),
			'lang_id' => array(
				'type' => 'select',
				'name' => 'Для языка',
				'help' => 'Если добавить перевод для русского языка, исходное слово переименуется. Например, можно изменить в русском интерфейсе исходное «Корзина» на новое «Заказ».',
			),
			'module_name' => array(
				'type' => 'select',
				'name' => 'Для модуля',
				'help' => 'Если не выбрать модуль, то перевод применится ко всем модулям сайта, где встретится исходное слово.',
			),
			'type' => array(
				'type' => 'select',
				'name' => 'Часть сайта',
				'help' => 'Пользовательская или административная. Для административной части имеет смысл переводить только на основной язык административной части.',
				'select' => array(
					'site' => 'пользовательская',
					'admin' => 'административная',
				),
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'name' => array(
			'name' => 'Исходное слово (фраза)',
			'variable' => 'text',
		),
		'adapt' => array(
			'class_th' => 'item__th_adapt',
		),
		'separator' => array(
			'class_th' => 'item__th_seporator',
		),		
		'text_translate' => array(
			'name' => 'Перевод (переименование)',
			'type' => 'text',
			'sql' => true,
			'no_important' => true,
		),
		'lang_id' => array(
			'type' => 'select',
			'sql' => true,
			'no_important' => true,
		),
		'module_name' => array(
			'type' => 'select',
			'sql' => true,
			'no_important' => true,
		),
		'type' => array(
			'type' => 'select',
			'sql' => true,
			'no_important' => true,
		),
		'actions' => array(
			'del' => true,
		),
	);

	/**
	 * @var array поля для фильтра
	 */
	public $variables_filter = array (
		'text' => array(
			'type' => 'text',
			'name' => 'Искать по исходной фразе',
		),
		'text_translate' => array(
			'type' => 'text',
			'name' => 'Искать по переводу',
		),
		'no_translate' => array(
			'type' => 'checkbox',
			'name' => 'Не переведенное',
		),
		'hr' => array(
			'type' => 'hr',
		),
		'lang_id' => array(
			'type' => 'select',
			'name' => 'Искать по языку',
		),
		'module_name' => array(
			'type' => 'select',
			'name' => 'Искать по модулю',
		),
		'type' => array(
			'type' => 'select',
			'name' => 'Искать по части сайта',
		),
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		$rows = array();
		foreach ($this->diafan->_languages->all as $language)
		{
			$rows[$language["id"]] = $language["name"];
		}
		$this->diafan->variable('lang_id', 'select', $rows);

		$rows = array();
		if($this->diafan->is_action('edit'))
		{
			$rows[''] = $this->diafan->_("Все");
		}
		foreach ($this->diafan->all_modules as $row)
		{
			$rows[$row["name"]] = $this->diafan->_($row["title"] ? $row["title"] : $row["name"]);
		}
		$this->diafan->variable('module_name', 'select', $rows);

		$this->upload();
	}

	/**
	 * Выводит контент модуля
	 * @return void
	 */
	public function show()
	{
		$this->form_upload();

		$this->diafan->list_row();
	}

	/**
	 * Поиск по полю "Нет изображения"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function save_filter_variable_no_translate($row)
	{
		if (empty($_GET["filter_no_translate"]))
		{
			return;
		}
		$this->diafan->where .= " AND e.text_translate=''";
		$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' ).'filter_no_translate=1';
		return 1;
	}

	/**
	 * Поиск
	 *
	 * @return string
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить перевод или переименование');
	}

	/**
	 * Выводит форму импорт/экспорт перевода
	 * 
	 * @return void
	 */
	private function form_upload()
	{
		if (! empty( $_GET["lang_id"]))
		{
			$lang_id = $this->diafan->filter($_GET, "int", "lang_id");
		}

		echo '<div class="block"><a href="javascript:void(0)" class="languages_import_export dashed_link">'.$this->diafan->_('Импорт / экспорт перевода').'</a>
		<div class="languages_import_export_block hide">
		
		
			<form action="" enctype="multipart/form-data" method="post" class="box box_half box_height">
				<input type="hidden" name="upload" value="true">
				<div class="box__heading">'.$this->diafan->_('Импорт').'</div>
				
				<p>'.$this->diafan->_('Язык сайта').' <select name="lang_id">';
				foreach ($this->diafan->variable("lang_id", "select") as $k => $v)
				{
					echo '<option value="'.$k.'"'.(! empty($lang_id) && $lang_id == $k ? ' selected' : '').'>'.$v.'</option>';
				}
				echo '</select></p>

				<input type="file" class="file" name="file">

				<button class="btn btn_blue btn_small">'.$this->diafan->_('Импортировать').'</button>
			</form>
			
			
			<div class="box box_half box_height box_right">
				<div class="box__heading">'.$this->diafan->_('Экспорт').'</div>';
				foreach ($this->diafan->_languages->all as $row)
				{
					echo '<a href="'.BASE_PATH.'languages/export/'.$row["shortname"].'?'.rand(0, 999999).'" class="file-load">
					<i class="fa fa-file-code-o"></i>
					'.$row["name"].'
					</a> ';
				}
				echo '
			</div>
		</div>';
	}

	/**
	 * Загружает файл перевода
	 * 
	 * @return void
	 */
	private function upload()
	{
		if (! isset($_FILES["file"]) || ! is_array($_FILES["file"]) || $_FILES["file"]['name'] == '')
		{
		    return;
		}
		$this->diafan->_languages->import($_FILES["file"]['tmp_name'], $_POST["lang_id"]);
		unlink($_FILES["file"]['tmp_name']);

		$this->diafan->redirect(URL);
	}

	/**
	 * Пользовательская функция, выполняемая перед редиректом при сохранении скидки
	 *
	 * @return void
	 */
	public function save_redirect()
	{
		if($row = DB::query_fetch_array("SELECT * FROM {languages_translate} WHERE text='%s' AND module_name='%s' AND lang_id=%d AND type='%s' AND id<>%d LIMIT 1", $_POST["text"], $_POST["module_name"], $_POST["lang_id"], $_POST["type"], $this->diafan->id))
		{
			if($row["text"] == $_POST["text"])
			{
				DB::query("DELETE FROM {languages_translate} WHERE id=%d", $row["id"]);
			}
		}
		parent::__call('save_redirect', array());
	}
}
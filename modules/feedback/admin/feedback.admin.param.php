<?php
/**
 * Конструктор формы обратной связи
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
 * Feedback_admin_param
 */
class Feedback_admin_param extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'feedback_param';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
				'multilang' => true,
			),
			'site_id' => array(
				'type' => 'select',
				'name' => 'Раздел сайта',
				'help' => 'Принадлежность к странице сайта.',
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Редактирование порядка следования характеристики в списке',
			),
			'required' => array(
				'type' => 'checkbox',
				'name' => 'Обязательно для заполнения',
			),
			'type' => array(
				'type' => 'select',
				'name' => 'Тип',
				'select' => array(
					'text' => 'строка',
					'numtext' => 'число',
					'date' => 'дата',
					'datetime' => 'дата и время',
					'textarea' => 'текстовое поле',
					'checkbox' => 'галочка',
					'radio' => 'переключатель',
					'select' => 'выпадающий список',
					'multiple' => 'список с выбором нескольких значений',
					'email' => 'электронный ящик',
					'phone' => 'телефон',
					'title' => 'заголовок группы характеристик',
					'attachments' => 'файлы',
					'images' => 'изображения',
				),
			),
			'max_count_attachments' => array(
				'type' => 'none',
				'name' => 'Максимальное количество добавляемых файлов',
				'help' => 'Количество добавляемых файлов. Если значение равно нулю, то форма добавления файлов не выводится. Параметр выводится, если тип характеристики задан как «файлы».',
				'no_save' => true,
			),
			'attachment_extensions' => array(
				'type' => 'none',
				'name' => 'Доступные типы файлов (через запятую)',
				'help' => 'Параметр выводится, если тип характеристики задан как «файлы».',
				'no_save' => true,
			),
			'recognize_image' => array(
				'type' => 'none',
				'name' => 'Распознавать изображения',
				'help' => 'Позволяет прикрепленные файлы в формате JPEG, GIF, PNG отображать как изображения. Параметр выводится, если тип характеристики задан как «файлы».',
				'no_save' => true,
			),
			'attach_big' => array(
				'type' => 'none',
				'name' => 'Размер для большого изображения',
				'help' => 'Размер изображения, отображаемый в пользовательской части сайта при увеличении изображения предпросмотра. Параметр выводится, если тип характеристики задан как «файлы» и отмечена опция «Распознавать изображения».',
				'no_save' => true,
			),
			'attach_medium' => array(
				'type' => 'none',
				'name' => 'Размер для маленького изображения',
				'help' => 'Размер изображения предпросмотра. Параметр выводится, если тип характеристики задан как «файлы» и отмечена опция «Распознавать изображения».',
				'no_save' => true,
			),
			'attach_use_animation' => array(
				'type' => 'none',
				'name' => 'Использовать анимацию при увеличении изображений',
				'help' => 'Параметр добавляет JavaScript код, позволяющий включить анимацию при увеличении изображений. Параметр выводится, если отмечена опция «Распознавать изображения». Параметр выводится, если тип характеристики задан как «файлы» и отмечена опция «Распознавать изображения».',
				'no_save' => true,
			),
			'upload_max_filesize' => array(
				'type' => 'none',
				'name' => 'Максимальный размер загружаемых файлов',
				'help' => 'Параметр показывает максимально допустимый размер загружаемых файлов, установленный в настройках хостинга. Параметр выводится, если тип характеристики задан как «файлы».',
				'no_save' => true,
			),
			'images_variations' => array(
				'type' => 'none',
				'name' => 'Генерировать размеры изображений',
				'help' => 'Размеры изображений, заданные в модуле «Изображения». Параметр выводится, если тип характеристики задан как «изображение».',
				'no_save' => true,
			),
			'param_select' => array(
				'type' => 'function',
				'name' => 'Значения',
				'help' => 'Появляется для полей с типом «галочка», «выпадающий список» и «список с выбором нескольких значений»',
			),
			'text' => array(
				'type' => 'editor',
				'name' => 'Описание',
				'multilang' => true,
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
			'name' => 'Название'
		),
		'type' => array(
			'name' => 'Тип',
			'type' => 'select',
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
			'trash' => true,
		),
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'element_site', // делит элементы по разделам (страницы сайта, к которым прикреплен модуль)
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		$sites = DB::query_fetch_all("SELECT id, [name], parent_id FROM {site} WHERE trash='0' AND module_name='%s' ORDER BY sort ASC", $this->diafan->_admin->module);
		if(count($sites))
		{
			$this->diafan->not_empty_site = true;
		}
		foreach($sites as $site)
		{
			$this->cache["parent_site"][$site["id"]] = $site["name"];
		}
		if(count($sites) == 1)
		{
			if (DB::query_result("SELECT id FROM {%s} WHERE trash='0' AND site_id<>%d LIMIT 1", $this->diafan->table, $sites[0]["id"]))
			{
				$sites[] = 0;
			}
			else
			{
				$this->diafan->_route->site = $sites[0]["id"];
			}
		}
		$this->diafan->sites = $sites;
	}

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить поле');
	}

	/**
	 * Выводит список полей формы
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Редактирование поля "Страница"
	 *
	 * @return void
	 */
	public function edit_variable_site_id()
	{
		if (! $this->diafan->value)
		{
			$this->diafan->value = $this->diafan->_route->site;
		}

		echo '
		<div class="unit">
			<div class="infofield">'.$this->diafan->variable_name().'</div>
				<select name="'.$this->diafan->key.'">';
		$cats[0] = DB::query_fetch_all("SELECT id, [name] FROM {site} WHERE trash='0' AND module_name='%s' ORDER BY id ASC", $this->diafan->_admin->module);
		echo $this->diafan->get_options($cats, $cats[0], array ( $this->diafan->value )).'
				</select>
				'.$this->diafan->help().'
		</div>';
	}

	/**
	 * Сохранение поля "Обязательно для заполнения"
	 * @return void
	 */
	public function save_variable_required()
	{
		$this->diafan->set_query("required='%d'");
		if(! empty($_POST["required"]) && $_POST["type"] == "title")
		{
			$this->diafan->set_value(0);
		}
		else
		{
			$this->diafan->set_value(! empty($_POST["required"]) ? 1 : 0);
		}
	}
}
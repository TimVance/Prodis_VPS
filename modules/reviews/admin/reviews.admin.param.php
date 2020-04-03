<?php
/**
 * Конструктор отзывов
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

class Reviews_admin_param extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'reviews_param';

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
			'module_name' => array(
				'type' => 'select',
				'name' => 'Модуль',
				'help' => 'Возможность ограничить применением поля, прикрепленными к выбранному модулю.',
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
					'editor' => 'поле с редактором',
					'checkbox' => 'галочка',
					'radio' => 'переключатель',
					'select' => 'выпадающий список',
					'multiple' => 'список с выбором нескольких значений',
					'email' => 'электронный ящик',
					'phone' => 'телефон',
					'url' => 'ссылка',
					'title' => 'заголовок группы характеристик',
					'attachments' => 'файлы',
					'images' => 'изображения',
				),
			),
			'info' => array(
				'type' => 'select',
				'name' => 'Значение',
				'help' => 'Смысловая нагрузка поля.',
				'select' => array(
					'' => 'Свободное поле',
					'rating' => 'Оценка',
					'name' => 'Имя',
					'avatar' => 'Аватар',
					'email' => 'E-mail',
					'phone' => 'Телефон',
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
			'required' => array(
				'type' => 'checkbox',
				'name' => 'Обязательно для заполнения',
			),
			'show_in_list' => array(
				'type' => 'checkbox',
				'name' => 'Выводить в списке',
				'help' => 'Выводит значение поля для отзывов на сайте в списке отзывов',
			),
			'show_in_form_auth' => array(
				'type' => 'checkbox',
				'name' => 'Выводить в форме для авторизованных пользователей',
			),
			'show_in_form_no_auth' => array(
				'type' => 'checkbox',
				'name' => 'Выводить в форме для неавторизованных пользователей',
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Редактирование порядка следования поля в форме',
			),
			'param_select' => array(
				'type' => 'function',
				'help' => 'Появляется для полей с типом «галочка», «переключатель», «выпадающий список» и «список с выбором нескольких значений»',
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
		'module_name' => array(
			'name' => 'Модуль',
			'type' => 'select',
			'sql' => true,
			'no_important' => true,
		),
		'actions' => array(
			'trash' => true,
		),
	);

	/**
	 * @var array поля для фильтра
	 */
	public $variables_filter = array (
		'module_name' => array(
			'type' => 'select',
			'name' => 'Искать по модулю',
		),
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		$select = array();
		$select[''] = $this->diafan->_('Все');

		foreach ($this->diafan->all_modules as $row)
		{
			if($row["site_page"] && $row["name"] == $row["module_name"] || $row["name"] == "site")
			{
				$select[$row['module_name']] = $row['title'];
			}
		}
		$this->diafan->variable("module_name", 'select', $select);
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

	/**
	 * Выводит список полей формы
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}
}
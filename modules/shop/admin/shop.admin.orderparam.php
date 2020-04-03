<?php
/**
 * Конструктор формы оформления заказа
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
 * Shop_admin_orderparam
 */
class Shop_admin_orderparam extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'shop_order_param';

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
			'info' => array(
				'type' => 'select',
				'name' => 'Значение',
				'help' => 'Смысловая нагрузка поля.',
				'select' => array(
					'' => 'Свободное поле',
					'address' => 'Адрес',
					'street' => 'Улица',
					'building' => 'Номер дома',
					'suite' => 'Корпус',
					'flat' => 'Квартира',
					'entrance' => 'Подъезд',
					'floor' => 'Этаж',
					'intercom' => 'Домофон',
					'city' => 'Город',
					'country' => 'Страна',
					'zip' => 'Индекс',
					'metro' => 'Станция метро',
					'cargolift' => 'Наличие грузового лифта',
					'name' => 'ФИО',
					'firstname' => 'Имя',
					'lastname' => 'Фамилия',
					'fathersname' => 'Отчество',
					'phone' => 'Телефон',
					'phone-extra' => 'Дополнительный телефон',
					'email' => 'Электронный адрес для связи',
					'comment' => 'Комментарий к адресу',
				),
			),
			'type' => array(
				'type' => 'select',
				'name' => 'Тип',
				'select' => array(
					'text' => 'Строка',
					'numtext' => 'Число',
					'date' => 'Дата',
					'datetime' => 'Дата и время',
					'textarea' => 'Текстовое поле',
					'checkbox' => 'Галочка',
					'select' => 'Выпадающий список',
					'multiple' => 'Список с выбором нескольких значений',
					'email' => 'Электронный ящик',
					'phone' => 'Телефон',
					'title' => 'Заголовок группы характеристик',
					'attachments' => 'Файл',
					'images' => 'Изображение',
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
				'help' => 'Появляется для полей с типом «галочка», «выпадающий список» и «список с выбором нескольких значений».',
			),
			'hr1' => 'hr',
			'required' => array(
				'type' => 'checkbox',
				'name' => 'Обязательно для заполнения',
			),
			'show_in_form' => array(
				'type' => 'checkbox',
				'name' => 'Использовать в стандатной форме оформления заказа',
			),
			'show_in_form_one_click' => array(
				'type' => 'checkbox',
				'name' => 'Использовать в форме быстрого заказа',
			),
			'show_in_form_register' => array(
				'type' => 'checkbox',
				'name' => 'Позволять редактировать из личного кабинета',
				'help' => 'Пользователь сможет установить значение по умолчанию для данного поля из личного кабинета',
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Редактирование порядка следования поля в форме.',
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
		'actions' => array(
			'trash' => true,
		),
	);

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить поле');
	}

	/**
	 * Выводит список дополнительных характеристик товара
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Сохранение поля "Позволять редактировать из личного кабинета"
	 * @return void
	 */
	public function save_variable_show_in_form_register()
	{
		$this->diafan->set_query("show_in_form_register='%s'");
		if(! empty($_POST["show_in_form_register"]) && in_array($_POST["type"], array("attachments", "images")))
		{
			$this->diafan->set_value(0);
		}
		else
		{
			$this->diafan->set_value(! empty($_POST["show_in_form_register"]) ? 1 : 0);
		}
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
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("shop_order_param_user", "param_id IN (".implode(",", $del_ids).")");
	}
}

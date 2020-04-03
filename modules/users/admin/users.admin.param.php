<?php
/**
 * Конструктор формы регистрации
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
 * Users_admin_param
 */
class Users_admin_param extends Frame_admin
{
    /**
     * @var string таблица в базе данных
     */
    public $table = 'users_param';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название поля',
				'multilang' => true,
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
				'help' => 'Появляется для полей с типом «галочка», «выпадающий список» и «список с выбором нескольких значений».',
			),
			'required' => array(
				'type' => 'checkbox',
				'name' => 'Обязательно для заполнения',
			),
			'show_in_page' => array(
				'type' => 'checkbox',
				'name' => 'Выводить на странице пользователя',
			),
			'show_in_form_no_auth' => array(
				'type' => 'checkbox',
				'name' => 'Выводить в форме регистрации',
			),
			'show_in_form_auth' => array(
				'type' => 'checkbox',
				'name' => 'Выводить в форме редактирования данных',
			),
			'roles' => array(
				'type' => 'function',
				'name' => 'Только для пользователей',
				'help' => 'Поле прикрепляется к одному или нескольким типам пользователей. При редактировании и в форме регистрации при смене типа пользователя меняется набор полей.',
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Редактирование порядка следования поля в списке полей.',
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
	 * Выводит список полей формы
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Редактирование поля "Тип пользователя"
	 * @return void
	 */
	public function edit_variable_roles()
	{
		$roles[0] = DB::query_fetch_all("SELECT id, [name] FROM {users_role} WHERE trash='0' ORDER BY sort ASC");
		if(count($roles[0]) < 1)
		{
			return;
		}
		$values = array ();
		if (!$this->diafan->is_new)
		{
			$values = DB::query_fetch_value("SELECT role_id FROM {users_param_role_rel} WHERE element_id=%d AND trash='0' AND role_id>0", $this->diafan->id, "role_id");
		}
		echo '
		<div class="unit" id="roles">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
			<select name="roles[]" multiple="multiple" size="11">
			<option value="all"'.(empty($values) ? ' selected' : '').'>'.$this->diafan->_('Все').'</option>';
			if(! empty( $roles ))
			{
				echo $this->diafan->get_options($roles, $roles[0], $values);
			}
			echo '</select>
		</div>';
	}

	/**
	 * Сохранение поля "Тип пользователя"
	 * @return string
	 */
	public function save_variable_roles()
	{
		$this->diafan->update_table_rel("users_param_role_rel", "element_id", "role_id", ! empty($_POST['roles']) ? $_POST['roles'] : array(), $this->diafan->id, $this->diafan->is_new);
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
		$this->diafan->del_or_trash_where("users_param_role_rel", "element_id IN (".implode(",", $del_ids).")");
	}
}
<?php
/**
 * Настройки модуля
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
 * Forum_admin_config
 */
class Forum_admin_config extends Frame_admin
{
	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'config' => array (
			'nastr' => array(
				'type' => 'numtext',
				'name' => 'Количество тем на странице',
				'help' => 'Количество одновременно выводимых тем в списке.',
			),
			'show_more' => array(
				'type' => 'checkbox',
				'name' => 'Включить «Показать ещё»',
				'help' => 'На странице тем появится кнопка «Показать ещё». Увеличивает количество одновременно выводимых тем в списке.',
			),
			'format_date' => array(
				'type' => 'select',
				'name' => 'Формат даты',
				'help' => 'Позволяет настроить отображение даты в модуле.',
				'select' => array(
					0 => '01.05.2016',
					6 => '01.05.2016 14:45',
					1 => '1 мая 2016 г.',
					2 => '1 мая',
					3 => '1 мая 2016, понедельник',
					5 => 'вчера 15:30',
					4 => 'не отображать',
				),
			),
			'count_level' => array(
				'type' => 'numtext',
				'name' => 'Максимальная вложенность',
				'help' => 'Ограничивает вложенность дерева сообщений.',
			),
			'nastr_messages' => array(
				'type' => 'numtext',
				'name' => 'Количество сообщений на странице',
				'help' => 'Количество одновременно выводимых в сообщений верхнего уровня.',
			),
			'news_count_days' => array(
				'type' => 'numtext',
				'name' => 'Сколько дней хранить «новые» сообщения',
				'help' => 'Для чистки мусора в логе новых сообщений. При большом количестве пользователей рекомендуется устанавливать не более трех дней.',
			),
			'hr1' => 'hr',
			'captcha' => array(
				'type' => 'module',
				'name' => 'Использовать защитный код (капчу)',
				'help' => 'Для добавления сообщения пользователь должен ввести защитный код.',
			),
			'only_user' => array(
				'type' => 'checkbox',
				'name' => 'Только для зарегистрированных пользователей',
				'help' => '',
			),
			'premoderation_theme' => array(
				'type' => 'checkbox',
				'name' => 'Предмодерация темы для обсуждения',
				'help' => 'Добавленные темы отображаются на сайте только после одобрения модератором.',
			),
			'premoderation_message' => array(
				'type' => 'checkbox',
				'name' => 'Предмодерация сообщений',
				'help' => 'Добавленные сообщения отображаются на сайте только после одобрения модератором.',
			),
			'hr2' => 'hr',
			'attachments' => array(
				'type' => 'module',
				'name' => 'Разрешить добавление файлов',
				'help' => 'Позволяет пользователям прикреплять файлы к сообщениям.',
			),
			'max_count_attachments' => array(
				'type' => 'none',
				'name' => 'Максимальное количество добавляемых файлов',
				'help' => 'Количество добавляемых файлов. Если значение равно нулю, то форма добавления файлов не выводится.',
				'no_save' => true,
			),
			'attachment_extensions' => array(
				'type' => 'none',
				'name' => 'Доступные типы файлов (через запятую)',
				'no_save' => true,
			),
			'recognize_image' => array(
				'type' => 'none',
				'name' => 'Распознавать изображения',
				'help' => 'Позволяет прикрепленные к вопросу файлы в формате JPEG, GIF, PNG отображать как изображения.',
				'no_save' => true,
			),
			'attach_big' => array(
				'type' => 'none',
				'name' => 'Размер для большого изображения',
				'help' => 'Размер изображения, отображаемый в пользовательской части сайта при увеличении изображения предпросмотра.',
				'no_save' => true,
			),
			'attach_medium' => array(
				'type' => 'none',
				'name' => 'Размер для маленького изображения',
				'help' => 'Размер изображения предпросмотра.',
				'no_save' => true,
			),
			'attach_use_animation' => array(
				'type' => 'none',
				'name' => 'Использовать анимацию при увеличении изображений',
				'help' => 'Параметр добавляет JavaScript код, позволяющий включить анимацию при увеличении изображений. Параметр выводится, если отмечена опция «Распознавать изображения».',
				'no_save' => true,
			),
			'upload_max_filesize' => array(
				'type' => 'none',
				'name' => 'Максимальный размер загружаемых файлов',
				'help' => 'Параметр показывает максимально допустимый размер загружаемых файлов, установленный в настройках хостинга.',
				'no_save' => true,
			),
		),
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'config', // файл настроек модуля
	);

	/**
	 * Редактирование поля "Предмодерация темы для обсуждения"
	 * 
	 * @return void
	 */
	public function edit_config_variable_premoderation_theme()
	{
		echo '<div id="premoderation_theme" class="unit">
				<div class="infofield">
		'.$this->diafan->variable_name("premoderation_theme").'
			</div>';
		if(! isset($this->diafan->cache["users_roles"]))
		{
			$this->diafan->cache["users_roles"] = DB::query_fetch_all("SELECT id, [name] FROM {users_role} WHERE trash='0'");
		}
		$rows = $this->diafan->cache["users_roles"];
		$values = array();
		if($this->diafan->value === '1')
		{
			$values[] = 0;
			foreach($rows as $row)
			{
				$values[] = $row["id"];
			}
		}
		elseif($this->diafan->value)
		{
			$values = unserialize($this->diafan->value);
		}
		echo '<input type="checkbox" name="premoderation_theme[]" id="input_premoderation_theme_0" value="0"'.(in_array(0, $values) ? ' checked' : '' ).'> <label for="input_premoderation_theme_0">'.$this->diafan->_('Гость').'</label><br>';
		foreach ($rows as $row)
		{
			echo '<input type="checkbox" name="premoderation_theme[]" id="input_premoderation_theme_'.$row['id'].'" value="'.$row['id'].'"'.(in_array($row['id'], $values) ? ' checked' : '' ).'> <label for="input_premoderation_theme_'.$row['id'].'">'.$row['name'].'</label><br>';
		}
		echo $this->diafan->help().'
			</div>';
	}

	/**
	 * Редактирование поля "Предмодерация сообщений"
	 * 
	 * @return void
	 */
	public function edit_config_variable_premoderation_message()
	{
		echo '<div id="premoderation_message" class="unit">
				<div class="infofield">
		'.$this->diafan->variable_name("premoderation_message").'
			</div>';
		if(! isset($this->diafan->cache["users_roles"]))
		{
			$this->diafan->cache["users_roles"] = DB::query_fetch_all("SELECT id, [name] FROM {users_role} WHERE trash='0'");
		}
		$rows = $this->diafan->cache["users_roles"];
		$values = array();
		if($this->diafan->value === '1')
		{
			$values[] = 0;
			foreach($rows as $row)
			{
				$values[] = $row["id"];
			}
		}
		elseif($this->diafan->value)
		{
			$values = unserialize($this->diafan->value);
		}
		echo '<input type="checkbox" name="premoderation_message[]" id="input_premoderation_message_0" value="0"'.(in_array(0, $values) ? ' checked' : '' ).'> <label for="input_premoderation_message_0">'.$this->diafan->_('Гость').'</label><br>';
		foreach ($rows as $row)
		{
			echo '<input type="checkbox" name="premoderation_message[]" id="input_premoderation_message_'.$row['id'].'" value="'.$row['id'].'"'.(in_array($row['id'], $values) ? ' checked' : '' ).'> <label for="input_premoderation_message_'.$row['id'].'">'.$row['name'].'</label><br>';
		}
		echo $this->diafan->help().'
			</div>';
	}

	/**
	 * Сохранение поля "Предмодерация темы для обсуждения"
	 * 
	 * @return void
	 */
	public function save_config_variable_premoderation_theme()
	{
		$this->diafan->set_query("premoderation_theme='%s'");
		$this->diafan->set_value(! empty($_POST["premoderation_theme"]) ? serialize($_POST["premoderation_theme"]) : '');
	}

	/**
	 * Сохранение поля "Предмодерация сообщений"
	 * 
	 * @return void
	 */
	public function save_config_variable_premoderation_message()
	{
		$this->diafan->set_query("premoderation_message='%s'");
		$this->diafan->set_value(! empty($_POST["premoderation_message"]) ? serialize($_POST["premoderation_message"]) : '');
	}
}
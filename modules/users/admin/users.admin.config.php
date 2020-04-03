<?php
/**
 * Настройки модуля
 * 
 * @package    DIAFAN.CMS
 *
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
 * Users_admin_config
 */
class Users_admin_config extends Frame_admin
{
	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'config' => array (
			'act' => array(
				'type' => 'select',
				'name' => 'Порядок активации пользователя',
				'select' => array(
					0 => 'при регистрации',
					1 => 'по ссылке, высланной на e-mail',
					2 => 'администратором',
				),
			),
			'mail_as_login' => array(
				'type' => 'checkbox',
				'name' => 'Использовать e-mail в качестве логина',
				'help' => 'Позволяет отказаться от поля «Логин» в форме регистрации, редактирования данных и при авторизации в качестве логина использовать e-mail.',
			),
			'captcha' => array(
				'type' => 'module',
				'name' => 'Использовать защитный код (капчу)',
				'help' => 'При регистрации пользователь должен ввести защитный код.',
			),
			'mes' => array(
				'type' => 'textarea',
				'name' => 'Сообщение пользователю по окончанию регистрации',
				'multilang' => true,
			),
			'hide_register_form' => array(
				'type' => 'checkbox',
				'name' => 'Скрывать форму после успешной регистрации',
				'help' => 'Позволяет скрывать форму после успешной регистрации.',
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
			'hr1' => 'hr',
			'avatar' => array(
				'type' => 'checkbox',
				'name' => 'Использовать аватар',
				'help' => 'Подключает аватар к пользователям сайта. Аватар загружается при регистрации, редактировании данных пользователем, редактировании данных о пользователе администратором. Используется аватар на форуме, в комментариях и выводятся вместе с приветствием пользователю.',
			),
			'avatar_width' => array(
				'type' => 'function',
				'name' => 'Размер аватара',
				'help' => 'Аватар будет генерироваться строго заданного размера.',
				'depend' => 'avatar',
			),
			'avatar_height' => array(
				'type' => 'none',
				'hide' => true,
			),
			'avatar_quality' => array(
				'type' => 'none',
				'hide' => true,
			),
			'avatar_none' => array(
				'type' => 'function',
				'hide' => true,
				'name' => 'Изображение «Нет аватара»',
				'help' => 'Выводиться у пользователя, если аватар не загружен.',
				'depend' => 'avatar',
			),
			'hr2' => array(
				'type' => 'title',
				'name' => 'Уведомления',
			),
			'sendmailadmin' => array(
				'type' => 'checkbox',
				'name' => 'Уведомлять администратора',
				'help' => 'Возможность уведомления администратора о регистрации новых пользователей.',
			),
			'emailconfadmin' => array(
				'type' => 'function',
				'name' => 'E-mail для уведомлений администратора',
				'help' => "Возможные значения:\n\n* e-mail, указанный в параметрах сайта;\n* другой (при выборе этого значения появляется дополнительное поле **впишите e-mail**).",
				'depend' => 'sendmailadmin',
			),
			'email_admin' => array(
				'type' => 'none',
				'name' => 'впишите e-mail',
				'hide' => true,
			),
			'subject_admin' => array(
				'type' => 'text',
				'name' => 'Тема письма администратору',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru).",
				'depend' => 'sendmailadmin',
			),
			'message_admin' => array(
				'type' => 'textarea',
				'name' => 'Текст письма администратору',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru),\n* %fio – ФИО пользователя,\n* %login – логин пользователя,\n* %email – e-mail пользователя.",
				'depend' => 'sendmailadmin',
			),
			'emailconf' => array(
				'type' => 'function',
				'name' => 'E-mail, указываемый в обратном адресе пользователю',
				'help' => "Возможные значения:\n\n* e-mail, указанный в параметрах сайта;\n* другой (при выборе этого значения появляется дополнительное поле **впишите e-mail**).",
			),
			'email' => array(
				'type' => 'none',
				'name' => 'впишите e-mail',
				'hide' => true,
			),
			'subject' => array(
				'type' => 'text',
				'name' => 'Тема письма новому пользователю',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru).",
				'multilang' => true,
			),
			'message' => array(
				'type' => 'textarea',
				'name' => 'Сообщение новому пользователю',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru),\n* %fio – ФИО пользователя,\n* %login – логин пользователя,\n* %password – пароль пользователя,\n* %email – e-mail пользователя,\n* %actlink – ссылка для активации аккаунта.",
				'multilang' => true,
			),
			'subject_act' => array(
				'type' => 'text',
				'name' => 'Тема письма пользователю при активации аккаунта администратором',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru).",
				'multilang' => true,
			),
			'message_act' => array(
				'type' => 'textarea',
				'name' => 'Сообщение пользователю при активации аккаунта администратором',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru),\n* %fio – ФИО пользователя,\n* %login – логин пользователя,\n* %email – e-mail пользователя.",
				'multilang' => true,
			),
			'mes_reminding' => array(
				'type' => 'text',
				'name' => 'Сообщение пользователю при восстановлении пароля',
				'multilang' => true,
			),
			'subject_reminding' => array(
				'type' => 'text',
				'name' => 'Тема письма со ссылкой на изменение пароля',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru).",
				'multilang' => true,
			),
			'message_reminding' => array(
				'type' => 'textarea',
				'name' => 'Текст письма со ссылкой на изменение пароля',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru),\n* %actlink – ссылка на изменение пароль.",
				'multilang' => true,
			),
			'subject_reminding_new_pass' => array(
				'type' => 'text',
				'name' => 'Тема письма с новым паролем',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru).",
				'multilang' => true,
			),
			'message_reminding_new_pass' => array(
				'type' => 'textarea',
				'name' => 'Текст письма с новым паролем',
				'help' => "Можно добавлять:\n\n* %title – название сайта,\n* %url – адрес сайта (например, site.ru),\n* %fio – ФИО пользователя,\n* %login – логин пользователя,\n* %password – новый пароль пользователя.",
				'multilang' => true,
			),
			'hr_loginz' => 'hr',
			'loginza' => array(
				'type' => 'checkbox',
				'name' => 'Использовать авторизацию через сервис Loginza',
				'help' => 'Подключает авторизацию через социальные сети.',
			),
			'loginza_widget_id' => array(
				'type' => 'text',
				'name' => 'ID виджета для сервиса Loginza',
				'help' => 'Данные из настроек сервиса Loginza.',
				'depend' => 'loginza',
			),
			'loginza_skey' => array(
				'type' => 'text',
				'name' => 'Секретный ключ для сервиса Loginza',
				'help' => 'Данные из настроек сервиса Loginza.',
				'depend' => 'loginza',
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
	 * Валидация поля "Размер и качество аватара"
	 * 
	 * @return void
	 */
	public function validate_config_variable_avatar_width()
	{
		if(! empty($_POST["avatar"]) && (empty($_POST["avatar_width"]) || empty($_POST["avatar_height"]) || empty($_POST["avatar_quality"])))
		{
			$this->diafan->set_error("avatar_width", 'Задайте размер для аватара.');
		}
	}

	/**
	 * Редактирование поля "Размер и качество аватара"
	 * 
	 * @return void
	 */
	public function edit_config_variable_avatar_width()
	{
		echo '<div id="avatar_width" class="unit">
				<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
				<input type="number" name="avatar_width" size="3" value="'.$this->diafan->values("avatar_width").'"> x
				<input type="number" name="avatar_height" size="3" value="'.$this->diafan->values("avatar_height").'">,
				'.$this->diafan->_('качество').'
				<input type="number" name="avatar_quality" size="2" value="'.$this->diafan->values("avatar_quality").'">
		</div>';
	}

	/**
	 * Редактирование поля "Изображение «Нет аватара»"
	 * 
	 * @return void
	 */
	public function edit_config_variable_avatar_none()
	{
		echo '<div id="avatar_width" class="unit">
				<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>';
		if (file_exists(ABSOLUTE_PATH.USERFILES.'/avatar_none.png'))
		{
			echo '<img src="'.BASE_PATH.USERFILES.'/avatar_none.png?'.rand(0, 99).'" width="'
			.$this->diafan->configmodules("avatar_width", "users").'" height="'
			.$this->diafan->configmodules("avatar_height", "users").'">'
			.'<input type="checkbox" name="delete_avatar" id="input_delete_avatar" value="1"> <label for="input_delete_avatar">'.$this->diafan->_('Удалить')
			.'</label><br><br>';
		}
			echo '<input type="file" name="avatar_none" value="" class="file">
		</div>';
	}

	/**
	 * Сохранение поля "Изображение «Нет аватара»"
	 * 
	 * @return void
	 */
	public function save_config_variable_avatar_none()
	{
		$value = 0;
		if(file_exists(ABSOLUTE_PATH.USERFILES.'/avatar_none.png'))
		{
			$value = 1;
			if (! empty($_POST["delete_avatar"]))
			{
				File::delete_file(USERFILES.'/avatar_none.png');
				$value = 0;
			}
		}
		if (isset($_FILES["avatar_none"]) && is_array($_FILES["avatar_none"]) && $_FILES["avatar_none"]['name'] != '')
		{
			File::copy_file($_FILES["avatar_none"]['tmp_name'], 'tmp/avatar_none.png');
			$tmp_name = 'tmp/avatar_none.png';
			try
			{
				list($width, $height) = getimagesize(ABSOLUTE_PATH.$tmp_name);
				if (! $width || ! $height)
				{
					throw new Exception($this->diafan->_('Некорректный файл.'));
				}
				$avatar_width = $this->diafan->filter($_POST, "int", "avatar_width");
				$avatar_height = $this->diafan->filter($_POST, "int", "avatar_height");
				$avatar_quality = $this->diafan->filter($_POST, "int", "avatar_quality");
				if(! $avatar_width || ! $avatar_height || ! $avatar_quality)
				{
					throw new Exception($this->diafan->_('Задайте размер для аватара.'));
				}

				if ($width < $avatar_width || $height < $_POST["avatar_height"])
				{
					throw new Exception($this->diafan->_('Размер изображения должен быть не меньше %spx X %spx.', false, $avatar_width, $avatar_height));
				}
				Custom::inc('includes/image.php');
				if (! Image::resize(ABSOLUTE_PATH.$tmp_name, $avatar_width, $avatar_height, $avatar_quality, true, true))
				{
					throw new Exception($this->diafan->_('Файл не загружен.'));
				}
				$dst_img  = imageCreateTrueColor($avatar_width, $avatar_height);
				$original = @imageCreateFromString(file_get_contents(ABSOLUTE_PATH.$tmp_name));
				if(! imageCopy($dst_img, $original, 0, 0, 0, 0, $avatar_width, $avatar_height))
				{
					throw new Exception($this->diafan->_('Файл не загружен.'));
				}
				if(! imagePNG($dst_img, ABSOLUTE_PATH.USERFILES.'/avatar_none.png'))
				{
					throw new Exception($this->diafan->_('Файл не загружен.'));
				}
				File::delete_file($tmp_name);
				$value = 1;
			}
			catch(Exception $e)
			{
				File::delete_file($tmp_name);
				throw new Exception($e->getMessage());
			}
		}
		$this->diafan->set_query("avatar_none='%d'");
		$this->diafan->set_value($value);
	}
}
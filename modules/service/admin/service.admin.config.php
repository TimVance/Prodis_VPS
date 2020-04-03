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
 * Service_admin_config
 */
class Service_admin_config extends Frame_admin
{
	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'base' => array (
			'hr1' => array(
				'type' => 'title',
				'name' => 'Основные',
			),
			'express_preview_enable' => array(
				'type' => 'checkbox',
				'name' => 'Экспресс чтение файла',
				'help' => 'Частичная загрузка записей файла перед импортом.',
			),
			'express_count_part' => array(
				'type' => 'numtext',
				'name' => 'Количество записей в одной итерации',
				'help' => 'Ограничение количества записей при чтении файла импорта в одной итерации.',
			),
			'express_csv_encoding' => array(
				'type' => 'select',
				'name' => 'Кодировка по умолчанию',
				'help' => 'Кодировка данных в файле CSV. Часто cp1251 или utf8. По умолчанию из Excell файлы CSV выходят в кодировке cp1251.',
				'select' => array(
					'cp1251' => 'cp1251',
					'utf8'   => 'utf8',
				),
			),
			'hr2' => array(
				'type' => 'title',
				'name' => 'API импорта/экспорта',
			),
			'paramhelp' => array(
				'type' => 'function',
				'no_save' => true,
				'hide' => true,
			),
			'express_name' => array(
				'type' => 'text',
				'name' => 'Логин',
				'help' => 'Имя зарегистрированного на сайте пользователя от чьего имени будут импортированы записи. Обратите внимание, что права пользователя должны распространяться на модуль, в который будут импортированы записи.',
			),
			'express_password' => array(
				'type' => 'text',
				'name' => 'Пароль',
				'help' => 'Пароль зарегистрированного на сайте пользователя от чьего имени будут импортированы записи.',
			),
			'express_key' => array(
				'type' => 'text',
				'name' => 'Ключ',
				'help' => 'Секретный ключ, используемый в url-адресе инициализации импорта записей с помощью API.',
			),
			'express_file_path' => array(
				'type' => 'text',
				'name' => 'Адрес файла импорта',
				'help' => 'Путь до файла импорта относительно корня сайта или url-адрес.',
			),
		),
	);

	/**
	 * @var array названия табов
	 */
	public $tabs_name = array(
		'base' => 'Основные настройки',
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'tab_card', // использование вкладок
		'config', // файл настроек модуля
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{

	}

	/**
	 * Редактирование поля "Ссылка на документацию"
	 * @return void
	 */
	public function edit_config_variable_paramhelp()
	{
		echo '
		<div class="unit" id="paramhelp">
			<p>'.$this->diafan->_('Для инициализации клиента API используйте следующий URL').':<br />'
				.BASE_PATH.'service/express/client/?key=<b><i>YOUR_KEY</i></b>&cat=<b><i>DESCRIPTION_ID</i></b>'.'<br />'
				.$this->diafan->_('где %s - Ваш ключ, %s - номер %sописания импорта/экспорта%s.', 'YOUR_KEY', 'DESCRIPTION_ID', '<a href="'.BASE_PATH_HREF.'service/express/fields/'.'">', '</a>')
		.'</p>
			↑ <a href="http'.(IS_HTTPS ? "s" : '').'://www.diafan.ru/dokument/full-manual/modules/shop/#Import/eksport-YA.Market" target="_blank">'.$this->diafan->_('О типах полей для импорта').'</a>
		</div>';
	}

	/**
	 * Редактирование поля "Пароль"
	 * @return void
	 */
	public function edit_config_variable_express_password()
	{
		echo '
		<div class="unit" id="express_password">
			<div class="infofield">
				'.$this->diafan->variable_name().$this->diafan->help().'
			</div>
			<input type="password" name="express_password" value="" placeholder="'
				.($this->diafan->values("express_login") ? $this->diafan->_('Пароль сохранен.').' ' : '')
				.($this->diafan->values("express_login") ? $this->diafan->_('Введите новый для изменения') : $this->diafan->_('Введите пароль для изменения'))
			.'">
		</div>';
	}

	/**
	 * Редактирование поля "Ключ"
	 * @return void
	 */
	public function edit_config_variable_express_key()
	{
		echo '
		<div class="unit" id="express_key">
			<div class="infofield">
				'.$this->diafan->variable_name().$this->diafan->help().'
			</div>
			<input type="password" name="express_key" value="" placeholder="'
				.($this->diafan->values("express_key") ? $this->diafan->_('Ключ сохранен.').' ' : '')
				.($this->diafan->values("express_key") ? $this->diafan->_('Введите новый для изменения') : $this->diafan->_('Введите ключ для изменения'))
			.'">
		</div>';
	}

	/**
	 * Валидация поля "Логин"
	 *
	 * @return void
	 */
	public function validate_config_variable_express_name()
	{
		if(empty($_POST["express_name"]))
		{
			return;
		}
		if(! DB::query_result("SELECT id FROM {users} WHERE name='%s' AND trash='0'"." LIMIT 1", $_POST["express_name"]))
		{
			$validate = 'Пользователь с таким логином не существует.';
		}
		else $validate = false;
		$this->diafan->set_error("express_name", $validate);
	}

	/**
	 * Валидация поля "Пароль"
	 *
	 * @return void
	 */
	public function validate_config_variable_express_password()
	{
		if(empty($_POST["express_password"]) && empty($_POST["express_name"]))
		{
			return;
		}
		elseif(! empty($_POST["express_password"]) && empty($_POST["express_name"]))
		{
			$this->diafan->set_error("express_name", 'Пользователь с таким логином не существует.');
		}
		elseif(empty($_POST["express_password"]) && ! empty($_POST["express_name"]))
		{
			$this->diafan->set_error("express_password", 'Введен неверный пароль.');
		}
		else
		{
			if(! DB::query_result("SELECT id FROM {users} WHERE name='%s' AND password='%s' AND trash='0'"." LIMIT 1", $_POST["express_name"], encrypt($_POST["express_password"])))
			{
				$this->diafan->set_error("express_password", 'Введен неверный пароль.');
			}
		}
	}

	/**
	 * Сохранение поля "Количество записей в одной итерации"
	 * @return void
	 */
	public function save_config_variable_express_count_part()
	{
		$this->diafan->set_query("express_count_part=%d");
		$express_count_part = $this->diafan->filter($_POST, 'int', 'express_count_part', 0);
		$express_count_part = $express_count_part ? $express_count_part : 1;
		$this->diafan->set_value($express_count_part);
	}

	/**
	 * Сохранение поля "Пароль"
	 * @return void
	 */
	public function save_config_variable_express_password()
	{
		if(! empty($_POST["express_password"]))
		{
			$this->diafan->set_query("express_password='%s'");
			$this->diafan->set_value(base64_encode($_POST["express_password"]));
		}
	}

	/**
	 * Сохранение поля "Ключ"
	 * @return void
	 */
	public function save_config_variable_express_key()
	{
		if(! empty($_POST["express_key"]))
		{
			$this->diafan->set_query("express_key='%s'");
			$this->diafan->set_value(encrypt($_POST["express_key"]));
		}
	}
}

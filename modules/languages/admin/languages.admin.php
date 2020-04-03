<?php
/**
 * Редактирование списка языковых версий сайта
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
 * Languages_admin
 */
class Languages_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'languages';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Язык',
			),
			'shortname' => array(
				'type' => 'text',
				'name' => 'Обозначение языка латиницей',
				'help' => 'Используется для формирования URL. Если указан ru или rus, то интерфейс считается русским и не переводится.',
			),
			'base_site' => array(
				'type' => 'checkbox',
				'name' => 'Основной язык пользовательской части',
				'help' => 'Выберите этот параметр, если хотите изменить язык по умолчанию в пользовательской части',
			),
			'base_admin' => array(
				'type' => 'checkbox',
				'name' => 'Язык панели управления',
				'help' => 'Выберите этот параметр, если хотите изменить язык по умолчанию в административной части',
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'name' => array(
			'name' => 'Название и категория'
		),
		'base_site' => array(
			'sql' => true,
		),
		'base_admin' => array(
			'sql' => true,
		),
		'actions' => array(
			'del' => true,
		),
	);

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить язык сайта');
	}

	/**
	 * Выводит контент модуля
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Выводит пометку "Основной язык" в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_base_site($row, $var)
	{
		$text = '<div>';
		if($row["id"] == $this->diafan->_languages->site)
		{
			$text .= $this->diafan->_('Основной язык пользовательской части');
			if($row["id"] == $this->diafan->_languages->admin)
			{
				$text .= ', ';
			}
		}
		if($row["id"] == $this->diafan->_languages->admin)
		{
			$text .= $this->diafan->_('Язык панели управления');
		}
		$text .= '</div>';
		
		return $text;
	}

	/**
	 * Проверяет можно ли выполнять действия с текущим элементом строки
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param string $action действие
	 * @return boolean
	 */
	public function check_action($row, $action = '')
	{
		// нельзя удалить основной язык пользовательской  или административной части
		if($row["id"] == $this->diafan->_languages->site || $row["id"] == $this->diafan->_languages->admin)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Редактирование поля "Основной язык пользовательской части"
	 * 
	 * @return void
	 */
	public function edit_variable_base_site()
	{
		if($this->diafan->values("base_site"))
		{
			$this->diafan->variable_disabled("base_site", true);
		}
		$this->diafan->show_table_tr_checkbox('base_site', $this->diafan->variable_name(), $this->diafan->value, $this->diafan->help(), $this->diafan->variable_disabled());
	}

	/**
	 * Редактирование поля "Основной язык административной части"
	 * 
	 * @return void
	 */
	public function edit_variable_base_admin()
	{
		if($this->diafan->values("base_admin"))
		{
			$this->diafan->variable_disabled("base_admin", true);
		}
		$this->diafan->show_table_tr_checkbox('base_admin', $this->diafan->variable_name(), $this->diafan->value, $this->diafan->help(), $this->diafan->variable_disabled());
	}

	/**
	 * Сохранение поля "Основной язык пользовательской части"
	 * 
	 * @return void
	 */
	public function save_variable_base_site()
	{
		if($this->diafan->_languages->site != $this->diafan->id && $this->diafan->values("base_site"))
		{
			$this->diafan->set_query("base_site='%d'");
			$this->diafan->set_value(0);
		}
		elseif(! empty($_POST["base_site"]))
		{
			DB::query("UPDATE {languages} SET base_site='0' WHERE id=%d", $this->diafan->_languages->site);
			$this->diafan->set_query("base_site='%d'");
			$this->diafan->set_value(1);
		}
	}

	/**
	 * Сохранение поля "Основной язык административной части"
	 * 
	 * @return void
	 */
	public function save_variable_base_admin()
	{
		if($this->diafan->_languages->admin != $this->diafan->id && $this->diafan->values("base_admin"))
		{
			$this->diafan->set_query("base_admin='%d'");
			$this->diafan->set_value(0);
		}
		elseif(! empty($_POST["base_admin"]))
		{
			DB::query("UPDATE {languages} SET base_admin='0' WHERE id=%d", $this->diafan->_languages->admin);
			$this->diafan->set_query("base_admin='%d'");
			$this->diafan->set_value(1);
			if($this->diafan->_languages->admin != $this->diafan->id && ! empty($_SESSION["lang_id"]))
			{
				unset($_SESSION["lang_id"]);
			}
		}
	}

	/**
	 * Сохранение поля "Сокращенное название языка на латинице"
	 * 
	 * @return void
	 */
	public function save_variable_shortname()
	{
		$shortname = (! empty($_POST["shortname"]) ? $_POST["shortname"] : $_POST["name"]);
		$shortname = substr($this->diafan->translit($shortname), 0, 3);

		$this->diafan->set_query("shortname='%s'");		
		$this->diafan->set_value($shortname);
	}

	/**
	 * Добавление языкового интерфейса (вместо основной функции)
	 * 
	 * @return void
	 */
	public function save_new()
	{
		parent::__call('save_new', array());

		Custom::inc("includes/install.php");

		foreach ($this->diafan->installed_modules as $module)
		{
			$filepath = "modules/".$module."/".$module.".install.php";
			if (Custom::exists($filepath))
			{
				Custom::inc($filepath);
				$name = Ucfirst($module).'_install';
				$class = new $name($this->diafan);
				if($class->tables)
				{
					foreach ($class->tables as $table)
					{
						foreach ($table["fields"] as $f)
						{
							if(! empty($f["multilang"]))
							{
								DB::query("ALTER TABLE {".$table["name"]."} ADD `".$f["name"].$this->diafan->id."` ".$f["type"]." AFTER `".$f["name"].$this->diafan->_languages->site."`");
							}
						}
					}
				}
			}
		}

		DB::query("UPDATE {site} SET act".$this->diafan->id."='1' WHERE id=1");
	}

	/**
	 * Удаление языкового интерфейса (вместо основной функции)
	 * 
	 * @return void
	 */
	public function del()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->diafan->redirect(URL);
			return;
		}

		if (! $this->diafan->_users->roles('del', $this->diafan->_admin->rewrite))
		{
			$this->diafan->redirect(URL);
			return;
		}

		if (! empty($_POST["id"]))
		{
			$ids = array($_POST["id"]);
		}
		else
		{
			$ids = $_POST["ids"];
		}
		Custom::inc("includes/install.php");
		foreach ($ids as $id)
		{
			$id = intval($id);
			if (! $id || $id == $this->diafan->_languages->site || $id == $this->diafan->_languages->admin)
			{
				continue;
			}

			foreach ($this->diafan->installed_modules as $module)
			{
				$filepath = "modules/".$module."/".$module.".install.php";
				if (Custom::exists($filepath))
				{
					Custom::inc($filepath);
					$name = Ucfirst($module).'_install';
					$class = new $name($this->diafan);
					if($class->tables)
					{
						foreach ($class->tables as $table)
						{
							foreach ($table["fields"] as $f)
							{
								if(! empty($f["multilang"]))
								{
									DB::query("ALTER TABLE {".$table["name"]."} DROP `".$f["name"].$id."`");
								}
							}
						}
					}
				}
			}
			DB::query("DELETE FROM {languages_translate} WHERE lang_id=%d", $id);
			DB::query("DELETE FROM {languages} WHERE id=%d", $id);
		}

		$this->diafan->redirect(BASE_PATH.ADMIN_FOLDER.'/languages/success1/');
	}
}
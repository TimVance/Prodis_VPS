<?php
/**
 * Темы
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
 * Custom_admin
 */
class Custom_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'custom';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Тема (латинские буквы)',
				'help' => 'Латинские буквы в нижнем регистре, нижнее подчеркивание или цифры.',
			),
			'current' => array(
				'type' => 'checkbox',
				'name' => 'Применить',
				'help' => 'Можно применить сразу несколько тем.',
			),
			'import' => array(
				'type' => 'function',
				'name' => 'Импорт темы',
				'help' => 'Файлы и папки из текущей темы будут удалены.',
			),
			'created' => array(
				'type' => 'datetime',
				'name' => 'Дата создания',
				'help' => 'Вводится в формате дд.мм.гггг чч:мм.',
			),
			'text' => array(
				'type' => 'textarea',
				'name' => 'Примечание',
			),
			'modules' => array(
				'type' => 'textarea',
				'name' => 'Определены модули',
				'no_save' => true,
				'disabled' => true,
				'help' => 'Модули, определенные в теме сайта.',
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'created' => array(
			'name' => 'Дата и время',
			'type' => 'datetime',
			'sql' => true,
			'no_important' => true,
		),
		'name' => array(
			'name' => 'Тема / Модули',
			'class' => 'text',
		),
		'current' => array(
			'sql' => true,
			'fast_edit' => true,
		),
		'adddemo' => array(),
		'download' => array(),
		'text' => array(
			'sql' => true,
			'type' => 'text',
			'class' => 'text',
			'no_important' => true,
		),
		'modules' => array(
			'name' => 'Модули',
			'type' => 'none',
		),
		'actions' => array(
			'del' => true,
		),
	);

	/**
	 * @var array дополнительные групповые операции
	 */
	public $group_action = array(
		"delete" => array(
			'name' => "Удалить",
			'confirm' => "Внимание! Темы будут безвозвратно удалены. Перед выполнением данной операции рекомендуется сделать резервную копию файлов сайта и базы данных.\n\r\n\rПродолжить?",
			'question' => "Внимание!\n\r\n\rУдаляемые темы содержат инструкции для внесения изменений в базу данных, которые могут затронуть существующую информацию на сайте. &laquo;Ок&raquo; - удалить темы и внести все изменения в БД автоматически. &laquo;Отмена&raquo; - удалить только файлы тем без изменений в БД.\n\r\n\rВы действительно хотите удалить запись?",
		),
	);

	/**
	 * Выводит панель групповых операций
	 *
	 * @param boolean $show_filter выводить кнопку "Фильтровать"
	 * @return void
	 */
	public function group_action_panel($show_filter = false)
	{
		$del = $this->diafan->variable_list('actions', 'del');
		$this->diafan->variable_list('actions', 'del', false);
		echo parent::group_action_panel($show_filter);
		$this->diafan->variable_list('actions', 'del', $del);
	}

	/**
	 * @var string информационное сообщение
	 */
	private $important_title = '';

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		// определение информационного сообщения
		$this->important_title = '<div class="head-box head-box_warning">
<i class="fa fa-warning"></i>'.$this->diafan->_('Внимание! Активирование тем может произвести необратимые изменения базы данных и модулей.').' '.$this->diafan->_('Настоятельно рекомендуем предварительно сделать резервную копию файлов и базы данных сайта.').' '.$this->diafan->_('Не забывайте, что файлы тем обладают приоритетом над оригинальными файлами системы. Также важна очередность активации тем.').' '.$this->diafan->_('Подробнее в <a href="https://www.diafan.ru/dokument/full-manual/sysmodules/themes/" title="Документация к DIAFAN.CMS">документации</a>.').'</div>';
	}

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		echo '<p><a class="btn" href="?action=custom_generate">'.$this->diafan->_('Сгенерировать тему').'</a> ';
		echo ' <a href="'.BASE_PATH_HREF.'custom/addnew1/" class="btn btn_blue">'.$this->diafan->_('Добавить вручную').'</a>';
		if(IS_DEMO)
		{
			echo ' ('.$this->diafan->_('не доступно в демонстрационном режиме').')';
		}
		echo '</p>';
	}

	/**
	 * Выводит контент модуля
	 * @return void
	 */
	public function show()
	{
		if(! empty($_GET["action"]))
		{
			switch($_GET["action"])
			{
				case "custom_generate":
					$this->generate();
					break;

				case "custom_generate_demo":
					$this->generate_demo();
					break;
			}
		}

		echo $this->important_title;

		$this->diafan->list_row();
	}

	/**
	 * Формирует SQL-запрос для списка элементов
	 *
	 * @param integer $id родитель
	 * @return array
	 */
	public function sql_query($id)
	{
		$rows = array();
		$rs = parent::sql_query($id);
		foreach(Custom::names() as $name)
		{
			foreach($rs as $r)
			{
				if($r["name"] == $name)
				{
					$rows[] = $r;
				}
			}
		}
		foreach($rs as $r)
		{
			if(! in_array($r["name"], Custom::names()))
			{
				$rows[] = $r;
			}
		}
		foreach($rows as $key => $row)
		{
			$modules = ! empty($row["name"]) ? $this->diafan->_custom->get_modules($row["name"]) : array();
			$rows[$key]["modules"] = '';
			foreach($modules as $module) $rows[$key]["modules"] .= (! empty($rows[$key]["modules"]) ? ', ' : '') . $module["name"];
		}
		return $rows;
	}

	/**
	 * Формирует название в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_name($row, $var)
	{
		$text = '<div class="name'.(! empty($var["class"]) ? ' '.$var["class"] : '').'" id="'.$row['id'].'">';
		$name  = '';
		if(! empty($var["variable"]))
		{
			$name = strip_tags($row[$var["variable"]]);
		}
		if(! empty($var["text"]))
		{
			$name = sprintf($this->diafan->_($var["text"]), $name);
		}
		if (! $name)
		{
			if(! empty($row["name"]))
			{
				$name = $row["name"];
			}
			else
			{
				$name = $row['id'];
			}
		}

		$text .= '<a href="';
		$text .= $this->diafan->get_base_link($row);
		$text .= '" title="'.$this->diafan->_('Редактировать').' ('.$row["id"].')">'.$name.'</a>';
		$text .= $this->diafan->list_variable_menu($row, array());
		$text .= $this->diafan->list_variable_parent($row, array());
		$text .= $this->diafan->list_variable_date_period($row, array());
		$text .= (! empty($row["modules"]) ? '<div class="categories"><a href="'.BASE_PATH.ADMIN_FOLDER.'/service/'.'" title="'.$this->diafan->_("Модули").'">'.$row["modules"].'</a></div>' : '');
		$text .= '</div>';
		return $text;
	}

	/**
	 * Функция быстрого редактирования текущей темы
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return void
	 */
	public function list_variable_current($row, $var)
	{
		$current = in_array($row["name"], Custom::names());
		$attr = ' confirm="'.$this->diafan->_("Внимание! Перед выполнением данной операции рекомендуется сделать резервную копию файлов сайта и базы данных.\n\r\n\rПродолжить?").'"';
		if(! $current)
		{
			if(file_exists(ABSOLUTE_PATH.'custom/'.$row["name"].'/install.sql'))
			{
				$attr .= ' question="'.$this->diafan->_("Внимание! Тема содержит инструкции для изменений базы данных.\n\r\n\rПрименить изменения?").'"';
			}
		}
		else
		{
			$names = array();
			if(! empty($row["name"]))
			{
				$modules = $this->diafan->_custom->get_modules($row["name"]);
				if(! empty($modules))
				{
					foreach($modules as $module)
					{
						if(empty($module["installed"]) || empty($module["name"]))
						{
							continue;
						}
						$names[] = $module["name"];
					}
				}
			}
			if(file_exists(ABSOLUTE_PATH.'custom/'.$row["name"].'/uninstall.sql') || ! empty($names))
			{
				if(empty($names))
				{
					$attr .= ' question="'.$this->diafan->_("Внимание! Тема содержит инструкции для изменений базы данных.\n\r\n\rПрименить изменения?").'"';
				}
				else
				{
					$attr .= ' question="'.$this->diafan->_("Внимание! Тема содержит инструкции для изменений базы данных и модулей %s.\n\r\n\rПрименить изменения?", (! empty($names) ? ': '.implode(',', $names) : '')).'"';
				}
			}
		}
		$text = '<div class="fast_edit_current">
		<input type="checkbox" name="current" row_id="'.$row["id"].'" id="current_'.$row["id"].'" value="1"'.($current ? ' checked' : '').$attr.'>
		<label for="current_'.$row["id"].'">'.$this->diafan->_('Применить').'</label>
		</div>';
		return $text;
	}

	/**
	 * Ссылка на добавление демо
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return void
	 */
	public function list_variable_adddemo($row, $var)
	{
		$text = '<div class="text">';

		if(in_array($row["name"], Custom::names()))
		{
			$text .=  '<a href="?action=custom_generate_demo&name='.$row["name"].'">';
			if(! file_exists(ABSOLUTE_PATH.'custom/'.$row["name"].'/install.sql'))
			{
				$text .= '<i class="fa fa-plus-square"></i> '.$this->diafan->_('Добавить контент');
			}
			else
			{
				$text .= '<i class="fa fa-check-circle"></i> '.$this->diafan->_('Обновить контент');
			}

			$text .='</a>';
			if(IS_DEMO)
			{
				$text .=  ' ('.$this->diafan->_('не доступно в демонстрационном режиме').')';
			}
		}

		return $text.'</div>';
	}

	/**
	 * Функция быстрого сохранения текущей темы
	 * @return boolean
	 */
	public function fast_save_current()
	{
		$name = DB::query_result("SELECT name FROM {custom} WHERE id=%d LIMIT 1", $_POST["id"]);
		$value = in_array($name, Custom::names());
		$current = ! empty($_POST["value"]) ? true : false;
		$question = ! empty($_POST["question"]) ? true : false;
		if($value == $current) return false;
		if($current)
		{
			$this->diafan->_custom->set($name, $current, $question);
		}
		else
		{
			$module_names = array();
			if(! empty($name))
			{
				$modules = $this->diafan->_custom->get_modules($name);
				if(! empty($modules))
				{
					foreach($modules as $key => $module)
					{
						if(empty($module["installed"])) continue;
						$module_names[] = $key;
					}
				}
			}
			if($question && ! empty($module_names))
			{
				$this->diafan->_custom->set_modules($module_names, false, $name);
			}
			$this->diafan->_custom->set($name, $current, $question);
		}
		return true;
	}

	/**
	 * Ссылка на скачивание темы в списке тем
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return void
	 */
	public function list_variable_download($row, $var)
	{
		$text = '<div class="text">';

		if (is_dir(ABSOLUTE_PATH.'custom/'.$row["name"]) && $dir = opendir(ABSOLUTE_PATH.'custom/'.$row["name"]))
		{
			while (($file = readdir($dir)) !== false)
			{
				if ($file != '.' && $file != '..')
				{
					$text .= '<a href="'.BASE_PATH.'custom/export/'.$row["id"].'/?'.$this->diafan->uid().'">'.$this->diafan->_('Скачать в Zip').'</a>';
					break;
				}
			}
			closedir($dir);
		}

		return $text.'</div>';
	}

	/**
	 * Проверка поля "Применить"
	 *
	 * @return void
	 */
	public function edit_variable_current()
	{
		$value = in_array($this->diafan->values("name"), Custom::names());
		$this->diafan->show_table_tr_checkbox('current', $this->diafan->variable_name(), $value, $this->diafan->help(), false);
	}

	/**
	 * Проверка поля "Импорт"
	 *
	 * @return void
	 */
	public function edit_variable_import()
	{
		echo '
		<div class="unit" id="import">
			<div class="infofield">'.$this->diafan->variable_name().'</div>';
		if(defined('IS_DEMO') && IS_DEMO)
		{
			echo '<p>'.$this->diafan->_('В демонстрационном режиме эта функция не доступна.').'</p>';
		}
		echo '<input type="file" name="import" class="file">'.$this->diafan->help().'
		</div>';
	}

	/**
	 * Редактирование поля "Модули темы сайта"
	 *
	 * @return void
	 */
	public function edit_variable_modules()
	{
		$value = $this->diafan->values('name');
		if(empty($value))
		{
			return;
		}
		$modules = ! empty($value) ? $this->diafan->_custom->get_modules($value) : array();
		if(empty($modules))
		{
			return;
		}
		$this->diafan->value = '';
		foreach($modules as $module) $this->diafan->value .= (! empty($this->diafan->value) ? ', ' : '') . $module["name"];

		$this->diafan->show_table_tr(
				$this->diafan->variable($this->diafan->key, 'type'),
				$this->diafan->key,
				$this->diafan->value,
				$this->diafan->variable_name(),
				$this->diafan->help(),
				$this->diafan->variable_disabled(),
				$this->diafan->variable('', 'maxlength'),
				$this->diafan->variable('', 'select'),
				$this->diafan->variable('', 'select_db'),
				$this->diafan->variable('', 'depend'),
				$this->diafan->variable('', 'attr')
			);

		unset($values);
	}

	/**
	 * Проверка поля "Название"
	 *
	 * @return void
	 */
	public function validate_variable_name()
	{
		if(empty($_POST["name"]))
		{
			$this->diafan->set_error("name", "Название темы не должно быть пустым.");
		}
		elseif(preg_match('/[^a-z_0-9+]/', $_POST["name"]))
		{
			$this->diafan->set_error("name", "Название темы должно содержать только буквы латинского алфавита в нижнем регистре, цифры и нижнее подчеркивание.");
		}
		else
		{
			if(DB::query_result("SELECT id FROM {custom} WHERE name='%s'".(! $this->diafan->is_new ? " AND id<>%d" : ""), $_POST["name"], $this->diafan->id))
			{
				$this->diafan->set_error("name", "Тема с таким названием существует.");
			}
		}
	}

	/**
	 * Проверка поля "Импорт"
	 *
	 * @return void
	 */
	public function validate_variable_import()
	{
		$value = in_array($this->diafan->values("name"), Custom::names());
		$current = ! empty($_POST["current"]) ? true : false;
		$import = (isset($_FILES["import"]) && is_array($_FILES["import"]) && $_FILES["import"]['name'] != '');
		if($value != $current || $import)
		{
			$this->diafan->set_error("confirm", "Внимание! Перед выполнением данной операции рекомендуется сделать резервную копию файлов сайта и базы данных.\n\r\n\rПродолжить?");
		}
		if(! isset($_POST["question"]))
		{
			if($current && ! $value)
			{
				if(file_exists(ABSOLUTE_PATH.'custom/'.$_POST["name"].'/install.sql'))
				{
					$this->diafan->set_error("question", "Внимание! Тема содержит инструкции для изменений базы данных.\n\r\n\rПрименить изменения?");
				}
			}
			elseif(! $current && $value)
			{
				$names = array();
				if(! empty($_POST["name"]))
				{
					$modules = $this->diafan->_custom->get_modules($_POST["name"]);
					if(! empty($modules))
					{
						foreach($modules as $module)
						{
							if(empty($module["installed"]) || empty($module["name"]))
							{
								continue;
							}
							$names[] = $module["name"];
						}
					}
				}
				if(file_exists(ABSOLUTE_PATH.'custom/'.$_POST["name"].'/uninstall.sql') || ! empty($names))
				{
					if(empty($names))
					{
						$this->diafan->set_error("question", "Внимание! Тема содержит инструкции для изменений базы данных.\n\r\n\rПрименить изменения?");
					}
					else
					{
						$message = $this->diafan->_("Внимание! Тема содержит инструкции для изменений базы данных и модулей %s.\n\r\n\rПрименить изменения?", (! empty($names) ? ': '.implode(',', $names) : ''));
						$this->diafan->set_error("question", $message);
					}
				}
			}
		}
		if ($import)
		{
			if(! class_exists('ZipArchive'))
			{
				$this->diafan->set_error("import", 'Не доступно PHP-расширение ZipArchive. Обратитесь в техническую поддержку хостинга.');
			}
			$zip = new ZipArchive;
			if ($zip->open($_FILES['import']['tmp_name']) === true)
			{
				for($i = 0; $i < $zip->numFiles; $i++)
				{
					if($zip->getNameIndex($i) == '/install.sql' || $zip->getNameIndex($i) == 'install.sql')
					{
						$this->diafan->set_error("question", "Внимание! Тема содержит инструкции для изменений базы данных.\n\r\n\rПрименить изменения?");
						break;
					}
				}
				$zip->close();
			}
			else
			{
				$this->diafan->set_error("import", "Файл должен быть в формате ZIP.");
			}
		}
	}

	/**
	 * Сохранение поля "Пользователь"
	 *
	 * @return void
	 */
	public function save_variable_current()
	{
		//$value = in_array($this->diafan->values("name"), Custom::names());
		$current = ! empty($_POST["current"]) ? true : false;
		$question = ! empty($_POST["question"]) ? true : false;
		//if($value == $current) return false;
		if($current)
		{
			$this->diafan->_custom->set($_POST["name"], $current, $question);
		}
		else
		{
			$module_names = array();
			if(! empty($_POST["name"]))
			{
				$modules = $this->diafan->_custom->get_modules($_POST["name"]);
				if(! empty($modules))
				{
					foreach($modules as $key => $module)
					{
						if(empty($module["installed"])) continue;
						$module_names[] = $key;
					}
				}
			}
			if($question && ! empty($module_names))
			{
				$this->diafan->_custom->set_modules($module_names, false, $_POST["name"]);
			}
			$this->diafan->_custom->set($_POST["name"], $current, $question);
		}
		return true;
	}

	/**
	 * Сохранение поля "Название"
	 *
	 * @return void
	 */
	public function save_variable_name()
	{
		$_POST["name"] = preg_replace('/[^a-z_0-9+]/', '', $_POST["name"]);
		$name = $_POST["name"];
		if(! defined('IS_DEMO') || ! IS_DEMO)
		{
			if($this->diafan->is_new)
			{
				File::create_dir('custom/'.$name);
			}
			elseif($this->diafan->values("name") != $name)
			{
				if($this->diafan->values("name") && is_dir(ABSOLUTE_PATH.'custom/'.$this->diafan->values("name")))
				{
					File::rename_dir($name, $this->diafan->values("name"), 'custom');
				}
				else
				{
					File::create_dir('custom/'.$name);
				}
			}
		}
		$this->diafan->set_query("name='%s'");
		$this->diafan->set_value($name);
	}

	/**
	 * Сохранение поля "Импорт"
	 *
	 * @return void
	 */
	public function save_variable_import()
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			return;
		}
		if (isset($_FILES["import"]) && is_array($_FILES["import"]) && $_FILES["import"]['name'] != '')
		{
			$this->diafan->_custom->import($_FILES['import']['tmp_name'], $_POST["name"]);
		}
		if(! empty($_POST["question"]))
		{
			$this->diafan->_custom->query($_POST["name"], true);
		}
	}

	/**
	 * Выводит кнопки действий над элементом
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_actions($row, $var)
	{
		$text = '<div class="item__unit">';

		//del
		if ($this->diafan->variable_list('actions', 'del')
			&& $this->diafan->_users->roles('del', $this->diafan->_admin->rewrite)
			&& $this->diafan->check_action($row, 'del'))
		{
			$text .= '
			<a href="javascript:void(0)" title="'.$this->diafan->_('Удалить').'"'.' confirm="'
			.(!empty( $row["count_children"] ) ? $this->diafan->_('ВНИМАНИЕ! Пункт содержит вложенность! ') : '')
			.($this->diafan->config("category") ? $this->diafan->_('При удалении категории удаляются все принадлежащие ей элементы. ') : '')
			.$this->diafan->_("Внимание! Тема будет безвозвратно удалена. Перед выполнением данной операции рекомендуется сделать резервную копию файлов сайта и базы данных.\n\r\n\rПродолжить?")
			. '" question="'
			.$this->diafan->_("Внимание!\n\r\n\rУдаляемая тема содержит инструкции для внесения изменений в базу данных, которые могут затронуть существующую информацию на сайте (%s). &laquo;Ок&raquo; - удалить тему и внести все изменения в БД автоматически. &laquo;Отмена&raquo; - удалить только файлы темы без изменений в БД.\n\r\n\rВы действительно хотите удалить запись?", $row["name"])
			. '" action="delete" class="action item__ui remove">
				<i class="fa fa-times-circle"></i>
			</a>';
		}

		$text .= '</div>';

		return $text;
	}

	/**
	 * Удаление темы
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

		$question = ! empty($_POST["question"]) ? true : false;

		if (! empty($_POST["id"]))
		{
			$ids = array($_POST["id"]);
		}
		else
		{
			$ids = $_POST["ids"];
		}
		foreach ($ids as $id)
		{
			if(! defined('IS_DEMO') || ! IS_DEMO)
			{
				if($name = DB::query_result("SELECT name FROM {custom} WHERE id=%d", $id))
				{
					$module_names = array();
					if(! empty($name))
					{
						$modules = $this->diafan->_custom->get_modules($name);
						if(! empty($modules))
						{
							foreach($modules as $key => $module)
							{
								if(empty($module["installed"])) continue;
								$module_names[] = $key;
							}
						}
					}
					if($question && ! empty($module_names))
					{
						$this->diafan->_custom->set_modules($module_names, false, $name);
					}
					$this->diafan->_custom->set($name, false, $question);

					File::delete_dir('custom/'.$name);
				}
			}
			DB::query("DELETE FROM {custom} WHERE id=%d", $id);
		}
		$this->diafan->redirect(BASE_PATH_HREF.'custom/success1/');
	}

	/**
	 * Генерирование темы
	 *
	 * @return void
	 */
	public function generate()
	{
		if(IS_DEMO)
		{
			throw new Exception('В демонстрационном режиме эта функция не доступна.');
		}
		$count = $this->diafan->_custom->generate();
		if(! $count)
		{
			echo '<div class="error">';
			echo $this->diafan->_('Тема не сгенерирована. Нет кастомизированных файлов.');
		}
		else
		{
			echo '<div class="ok">';
			echo $this->diafan->_('Тема сгенерирована.');
			if($count["custom"])
			{
				echo $this->diafan->_('<br>Кастомизированные файлы:');
				echo '<div style="margin-left: 20px">'.implode('<br>',$count["custom"]).'</div>';
			}
			if($count["return"])
			{
				echo $this->diafan->_('<br>Восстановленные файлы:');
				echo '<div style="margin-left: 20px">'.implode('<br>',$count["return"]).'</div>';
			}
		}
		echo '</div>';
	}

	/**
	 * Добавление в тему демо-контент
	 *
	 * @return void
	 */
	public function generate_demo()
	{
		if(! Custom::name())
		{
			return;
		}
		if(IS_DEMO)
		{
			throw new Exception('В демонстрационном режиме эта функция не доступна.');
		}
		Custom::inc("modules/custom/admin/custom.admin.demo.php");
		$class = new Custom_admin_demo($this->diafan);
		$count = $class->generate();
		if(! $count)
		{
			echo '<div class="error">';
			echo $this->diafan->_('Демо-данные не добавлены. Нет изменений в контенте.');
		}
		else
		{
			echo '<div class="ok">';
			echo $this->diafan->_('Демо-данные добавлены в текущую тему. Сгенерировано файлов: %s.', $count);
		}
		echo '</div>';
	}
}

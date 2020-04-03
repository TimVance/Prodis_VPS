<?php
/**
 * Редактирование типов пользователей
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
 * Users_admin_role
 */
class Users_admin_role extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'users_role';

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
			'registration' => array(
				'type' => 'checkbox',
				'name' => 'Назначать при регистрации на сайте',
				'help' => 'Если опция отмечена у одного типа пользователя, то новому пользователю при регистрации на сайте присваивается указанный тип. Если опцией отмечено несколько типов пользователей, то в форме регистрации появляется возможность выбрать тип регистрируемого пользователя. В зависимости от выбранного типа может меняться набор дополнительных полей в форме регистрации.',
			),
			'only_self' => array(
				'type' => 'checkbox',
				'name' => 'Видеть только свои материалы',
				'help' => 'Если опция отмечена, то пользователи указанного типа могут видеть и редактировать только свои материалы в контентных модулях («Страницы сайта», «Новости», «Товары», «Фотографии» и пр.).',
			),
			'perm' => array(
				'type' => 'function',
				'name' => 'Привелегии',
				'help' => 'Таблица с возможностью разрешения определенного набора действий для каждого модуля в административной части сайта и некоторых, заданных в модулях, действий для пользовательской части сайта. Набор действий модуля для пользовательской части можно задать в файле modules/модуль/admin/модуль.админ.role.php.',
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Редактирование порядка следования типа пользователя в списке.',
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
		'actions' => array(
			'trash' => true,
		),
	);

	/**
	 * @var array типы действий
	 */
	private $admin_variable_roles = array(
		'просмотр' => 'init',
		'правка' => 'edit',
		'удаление' => 'del'
	);

	/**
	 * @var array права пользователя для пользовательской части
	 */
	private $user_variable_roles;

	/**
	 * @var array названия прав пользователя для пользовательской части
	 */
	private $user_variable_names;

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить тип пользователя');
	}

	/**
	 * Выводит список типов пользователей
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
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
		if(DB::query_result("SELECT id FROM {users} WHERE trash='0' AND role_id=%d LIMIT 1", $row["id"]))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Редактирование поля "Права доступа"
	 * @return void
	 */
	public function edit_variable_perm()
	{
		echo '<div class="unit">
		<div id="tabs">
		<ul>
			<li><a href="#tabs-admin">'.$this->diafan->_('Права для административной части').'</a></li>
			<li><a href="#tabs-site">'.$this->diafan->_('Права для пользовательской части').'</a></li>	
		</ul>
		<div id="tabs-admin">';
			$this->table_admin_roles();
		echo '</div>
		<div id="tabs-site">';
			$this->table_site_roles();
		echo '</div>
		</div>
		</div>';
	}

	/**
	 * Формирует таблицы с правами пользователя
	 * @return void
	 */
	private function table_admin_roles()
	{
		$variable_roles = $this->admin_variable_roles;
		$current_roles = array();
		$admin_roles = false;
		$rows = DB::query_fetch_all("SELECT perm, rewrite FROM {users_role_perm} WHERE role_id=%d AND type='admin'",$this->diafan->id);
		foreach ($rows as $row)
		{
			if($row["perm"] == 'all' && $row["rewrite"] == 'all')
			{
				$admin_roles = true;
				break;
			}
			if($row["perm"] == "all")
			{
				$current_roles[$row["rewrite"]] = 'all';
			}
			else
			{
				$current_roles[$row["rewrite"]] = explode(',', $row["perm"]);
			}
		}
		$rewrites = array();
		$modules = array();
		$rows = DB::query_fetch_all("SELECT id, name, rewrite, parent_id FROM {admin} ORDER BY parent_id, sort ASC");
		foreach ($rows as $row)
		{
			if (in_array($row["rewrite"], $rewrites))
				continue;

			$rewrites[$row["id"]] = $row["rewrite"];
			foreach ($variable_roles as $v)
			{
				$row["role"][$v] = ! $this->diafan->is_new
				&&  ($admin_roles || ! empty($current_roles[$row["rewrite"]]) && ($current_roles[$row["rewrite"]] == 'all' || in_array($v, $current_roles[$row["rewrite"]])));
			}
			$modules[$row["parent_id"]][] = $row;
		}

		echo '<table class="border"><tr id="tr_first"><td>&nbsp;';
		foreach ($variable_roles as $key => $v)
		{
			echo '</td><td>
			<input type="checkbox" name="check_all_role" id="input_check_all_role_'.$v.'" value="'.$v.'" class="label_full"><label for="input_check_all_role_'.$v.'">'.$this->diafan->_($key).'</label>';
		}
		echo '</td></tr>';

		foreach ($modules[0] as $row)
		{
			$this->table_tr_admin_roles($row, $variable_roles);
			if($row["id"] && ! empty($modules[$row["id"]]))
			{
				foreach ($modules[$row["id"]] as $row2)
				{
					$this->table_tr_admin_roles($row2, $variable_roles, false);
				}
			}
		}
		echo '</table>';
	}

	/**
	 * Формирует таблицу с правами пользователя для административной части
	 * @return void
	 */
	private function table_tr_admin_roles($row, $variable_roles, $parent = true)
	{
		echo '<tr><td>';
		$row["name"] = $this->diafan->_($row["name"]);
		if($parent)
		{
			$row["name"] = '<b>'.$row["name"].'</b>';
		}
		echo '<p>'.$row["name"].'</p>';
		echo '</td>';
		foreach ($variable_roles as $v)
		{
			echo '<td><input type="checkbox" id="input_check_all_role_'.$v.'_'.$row["rewrite"].'" name="admin_'.$v.'[]" value="'.$row["rewrite"].'" class="checkbox checkbox_'.$v.'"';
			if ($row["role"][$v])
			{
				echo " checked";
			}
			echo '><label for="input_check_all_role_'.$v.'_'.$row["rewrite"].'"></label></td>';
		}
		echo '</tr>';
	}

	/**
	 * Формирует массив с правами пользователя для пользовательской части
	 * @return void
	 */
	private function get_user_roles()
	{
		$this->user_variable_names = array();
		$rows = Custom::read_dir("modules");
		foreach($rows as $file)
		{
			if ($file != 'users' && (Custom::exists('modules/'.$file.'/admin/'.$file.'.admin.role.php')))
			{
				if(! empty($this->diafan->title_modules[$file]))
				{
					$name = $this->diafan->title_modules[$file];
				}
				else
				{
					$name = $file;
				}
				$this->user_variable_names[$file] = $this->diafan->_($name);

				Custom::inc('modules/'.$file.'/admin/'.$file.'.admin.role.php');
				$class_name = ucfirst($file).'_admin_role';
				$module = new $class_name($this);
				$this->user_variable_roles[$file] = $module->get_rules();
				unset($module);
			}
		}
	}

	/**
	 * Формирует таблицу с правами пользователя для пользовательской части
	 * @return void
	 */
	private function table_site_roles()
	{
		$this->get_user_roles();

		$values = array();
		$rows = DB::query_fetch_all('SELECT rewrite, perm FROM {users_role_perm} WHERE role_id=%d AND type="site"', $this->diafan->id);
		foreach ($rows as $row)
		{
			$values[$row['rewrite']] = explode(',', $row['perm']);
		}

		echo '<table class="border">';
		foreach ($this->user_variable_roles as $module => $roles)
		{
			echo '<tr><td width="100"><b>'.$this->user_variable_names[$module].'</b> </td><td>';

			foreach ($roles as $name => $title)
			{
				echo '<input type="checkbox" name="site_'.$module.'[]" id="input_'.$module.'_'.$name.'" value="'.$name.'"'.(! empty($values[$module]) && in_array($name, $values[$module]) ? 'checked' : '').'> <label for="input_'.$module.'_'.$name.'">'.$this->diafan->_($title).'</label><br/>';
			}

			echo '</td></tr>';
		}
		echo '</table>';
	}

	/**
	 * Сохранение поля "Права доступа"
	 * @return void
	 */
	public function save_variable_perm()
	{
		DB::query("DELETE FROM {users_role_perm} WHERE role_id=%d", $this->diafan->id);
		$admin_pages = DB::query_fetch_value("SELECT DISTINCT(rewrite) FROM {admin}", "rewrite");
		$all_roles = true;
		foreach ($admin_pages as $admin_page)
		{
			foreach ($this->admin_variable_roles as $v)
			{
				if (empty($_POST['admin_'.$v]) || ! in_array($admin_page, $_POST['admin_'.$v]))
				{
					$all_roles = false;
					break 2;
				}
			}
		}
		if($all_roles)
		{
			DB::query("INSERT INTO {users_role_perm} (rewrite, perm, role_id, type) VALUES ('all', 'all', %d, 'admin')", $this->diafan->id);
		}
		else
		{
			$count = count($this->admin_variable_roles);
			foreach ($admin_pages as $admin_page)
			{
				$current_perm = array();
				foreach ($this->admin_variable_roles as $v)
				{
					if (! empty($_POST['admin_'.$v]) && in_array($admin_page, $_POST['admin_'.$v]))
					{
						$current_perm[] = $v;
					}
				}
				if (count($current_perm) > 0)
				{
					if (count($current_perm) == $count)
					{
						$count_rewrite++;
						$perm = 'all';
					}
					else
					{
						$perm = implode(',', $current_perm);
					}
	
					DB::query("INSERT INTO {users_role_perm} (rewrite, perm, role_id, type) VALUES ('%s', '%s', %d, 'admin')", $admin_page, $perm, $this->diafan->id);
				}
			}
		}
		$this->get_user_roles();
		foreach ($this->user_variable_roles as $module => $roles)
		{
			if (!empty($_POST['site_'.$module]))
			{
				$perm = implode(',', $_POST['site_'.$module]);
				DB::query("INSERT INTO {users_role_perm} (rewrite,perm,role_id,type) VALUES ('%s', '%s', %d, 'site')", $module, $perm, $this->diafan->id);
			}
		}
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("users_param_role_rel", "rel_id IN (".implode(",", $del_ids).")");
	}
}
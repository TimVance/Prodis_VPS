<?php
/**
 * Установка/удаление модулей
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
 * Service_admin
 */
class Service_admin extends Frame_admin
{
	/**
	 * @var array массив объектов - установка модулей
	 */
	private $install = array();

	/**
	 * Выводит контент модуля
	 * @return void
	 */
	public function show()
	{
		$rows = $this->get_rows();
		$module_rows = array('news', 'clauses', 'feedback', 'faq', 'shop', 'ab', 'files', 'comments', 'rating', 'tags', 'photo', 'votes', 'subscription', 'users', 'forum', 'search', 'map', 'keywords', 'filemanager', 'mistakes');

		$modules = array();
		foreach ($module_rows as $val)
		{
			if (! empty($rows[$val]))
			{
				$modules[] = $rows[$val];
			}
		}
		foreach ($rows as $row)
		{
			if (! in_array($row["module_name"], $module_rows))
			{
				$modules[] = $row;
			}
		}

		if ($this->diafan->_route->page == 2)
		{
			echo '<font color="red">'.$this->diafan->_('Модули установлены!').'</font>';
		}
		echo '<form action="'.$this->diafan->get_admin_url().'save1/" method="POST">
		<input type="hidden" name="action" value="save">
		<input type="hidden" name="id" value="true">';
		echo '<div class="box box_install">
		<h2>'.$this->diafan->_('Установленные модули').':</h2>
		<p>'.$this->diafan->_('Отметьте модули, которые нужно установить. Снимите отметку с модулей, которые нужно удалить.').'</p>
		<input type="hidden" name="check_hash_user" value="'.$this->diafan->_users->get_hash().'">';
		foreach ($modules as $row)
		{
			echo '<input type="checkbox" name="modules['.$row["module_name"].']" id="input_modules_'.$row["module_name"].'" '.($row["installed"] ? 'checked' : '').'><label for="input_modules_'.$row["module_name"].'"><b>'.$this->diafan->_($row["name"]).'</b></label>';
			$k = 1;
		}
		echo '<div class="hr"></div>
			<input type="checkbox" name="example_yes" id="input_example_yes" value="1"> <label for="input_example_yes"><b>'.$this->diafan->_('Заполнить сайт примерами из <a href="http://demo.diafan.ru/" target="_blank">демо-версии</a> (может занять время). Только для устанавливаемых модулей.').'</b></label>
		</div>';
		echo '<input type="submit" value="'.$this->diafan->_('Обновить').'" class="button" onmouseover="this.style.cursor=\'hand\';" onclick="return confirm(\'Внимание! Удаление модулей приведет к полному удалению находящейся в них информации! Продолжить?\')">';
		echo '
		</form>';
	}

	/**
	 * Установка/удаление модулей
	 * @return void
	 */
	public function save()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->diafan->redirect(URL);
			return;
		}

		$rows = $this->get_rows();
		if (! $rows)
		{
			$this->diafan->redirect($this->diafan->get_admin_url('page').'page2/');
		}

		if(! empty($_POST["example_yes"]) && Custom::exists('demo.zip'))
		{
			foreach(Custom::names() as $name)
			{
				File::delete_dir(USERFILES.'/demo/custom/'.$name);
			}
		}

		foreach ($rows as $module => $row)
		{
			// удаление модуля
			if (empty($_POST["modules"][$module]))
			{
				if($row["installed"])
				{
					$this->install[$module]->uninstall();
				}
			}
			else
			{
				if(! $row["installed"])
				{
					$this->install[$module]->tables();
					$this->install[$module]->start(! empty($_POST["example_yes"]));

					//установка прав на административную часть установленного модуля текущему пользователю
					if(! $this->diafan->_users->roles('all', 'all'))
					{
						$rs = DB::query_fetch_all("SELECT rewrite FROM {admin} WHERE BINARY rewrite='%s' OR BINARY rewrite LIKE '%s%%'", $module, $module);
						foreach ($rs as $r)
						{
							DB::query("INSERT INTO {users_role_perm} (role_id, perm, rewrite, type) VALUES (%d, 'all', '%s', 'admin')", $this->diafan->_users->role_id, $r["rewrite"]);
						}
					}
				}
			}
		}
		foreach ($rows as $module => $row)
		{
			// удаление модуля
			if (! empty($_POST["modules"][$module]) &&  ! $row["installed"])
			{
				$this->install[$module]->action_post();
			}
		}

		$this->diafan->redirect($this->diafan->get_admin_url('page').'page2/');
	}

	/**
	 * Получает список всех модулей которые можно установить
	 *
	 * @return array
	 */
	private function get_rows()
	{
		$modules = array();
		foreach ($this->diafan->all_modules as $r)
		{
			if($r["module_name"] == $r["name"])
			{
				$modules[$r["name"]] = $r["title"];
			}
		}

		foreach($this->diafan->_languages->all as $l)
		{
			$langs[] = $l["id"];
		}
		$install_modules = ! empty($_POST["modules"]) ? array_keys($_POST["modules"]) : array();

		Custom::inc("includes/install.php");
		$rows = array();
		$rs = Custom::read_dir("modules");
		foreach($rs as $module)
		{
			if (Custom::exists('modules/'.$module.'/'.$module.'.install.php'))
			{
				Custom::inc('modules/'.$module.'/'.$module.'.install.php');
				$name = Ucfirst($module).'_install';
				$this->install[$module] = new $name($this->diafan);

				if($this->install[$module]->is_core)
					continue;

				$this->install[$module]->langs = $langs;
				$this->install[$module]->module = $module;
				$this->install[$module]->install_modules = $install_modules;

				$row["installed"] = in_array($module, array_keys($modules));

				if($row["installed"])
				{
					$row["name"] = $modules[$module];
				}
				else
				{
					$row["name"] = $this->install[$module]->title;
				}
				if(! $row["name"])
				{
					$row["name"] = $module;
				}
				$row["module_name"] = $module;
				$rows[$module] = $row;
			}
		}
		return $rows;
	}
}

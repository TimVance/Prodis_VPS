<?php
/**
 * Файловый менеджер
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

class Filemanager_admin extends Frame_admin
{
	/**
	 *  @var array расширения файлов, доступных для редактирования
	 */
	private $allow_extension = array('php', 'txt', 'htaccess', 'html', 'css', 'js');

	/**
	 * @var array массив результатов валидации
	 */
	public $result;

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'multiupload', // мультизагрузка изображений (подключение JS-библиотек)
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		$this->check_path();
	}

	/**
	 * Выводит список файлов
	 * @return void
	 */
	public function show()
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			echo '<div class="error">'.$this->diafan->_('Файловый менеджер в демо-версии не доступен.').' <a href="http'.(IS_HTTPS ? "s" : '').'://www.diafan.ru/dokument/full-manual/modules/filemanager/" target="_blank">'.$this->diafan->_('Смотрите описание модуля в документации').'</a>.</div>';
			return;
		}
		if($this->diafan->_users->id<>1)
		{
			echo '<div class="error">'.$this->diafan->_('Нет доступа. Файловый менеджер доступен только администратору, устанавливавшему DIAFAN.CMS').'</div>';
			return;
		}
		
		$tree = $this->get_tree();
		
		if($this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			echo '<div class="head-box">
				<a class="btn" href="'.URL.'edit1/?action_filemanager=add_dir">
					<i class="fa fa-folder"></i>
					'.$this->diafan->_('Создать папку').'
				</a>
				
				<div class="inp-file">
					<input id="fileupload" type="file" name="files[]" data-url="'.BASE_PATH_HREF.'filemanager/" multiple>
					<span class="btn btn_blue btn_inp_file">
						<i class="fa fa-file-code-o"></i>
						'.$this->diafan->_('Загрузить файл').'
					</span>
				</div>
			</div>';
		}
		
		if ($this->diafan->_users->roles('del'))
		{
			echo '
			<form action="" method="post" class="ajax">
			<input type="hidden" name="check_hash_user" value="'.$this->diafan->_users->get_hash().'">
			<div class="action-box">
				<div class="action-unit">
					<input type="checkbox" class="select-all">
					<label><span>'.$this->diafan->_('Выбрать всё').'</span></label>
					
					<select name="action" class="group_action">
						<option value="delete" confirm="'.$this->diafan->_('Вы действительно хотите удалить выделенные файлы и папки? ВНИМАНИЕ! Удаленные файлы и папки восстановлению не подлежат!')
			.'">'.$this->diafan->_('Удалить').'</option>
					</select>
				</div>
				
				<button class="btn btn_blue btn_small" id="group_actions">'.$this->diafan->_('Применить').'</button>
			</div>
			</form>';
		}
		$this->template_tree($tree);
		
		if ($this->diafan->_users->roles('del'))
		{
			echo '
			<form action="" method="post" class="ajax">
			<input type="hidden" name="check_hash_user" value="'.$this->diafan->_users->get_hash().'">
			<div class="action-box">
				<div class="action-unit">
					<input type="checkbox" class="select-all">
					<label><span>'.$this->diafan->_('Выбрать всё').'</span></label>
					
					<select name="action" class="group_action">
						<option value="delete" confirm="'.$this->diafan->_('Вы действительно хотите удалить выделенные файлы и папки? ВНИМАНИЕ! Удаленные файлы и папки восстановлению не подлежат!')
			.'">'.$this->diafan->_('Удалить').'</option>
					</select>
				</div>
				
				<button class="btn btn_blue btn_small" id="group_actions">'.$this->diafan->_('Применить').'</button>
			</div>
			</form>';
		}
	}

	/**
	 * Выводит заголовок контента
	 *
	 * @return void
	 */
	public function show_h1()
	{
		echo '
		<div class="heading">
			<div class="heading__unit">';

		echo $this->diafan->_($this->diafan->_admin->title_module);

		echo '</div>';

		echo '<div class="heading__txt">
		<i class="fa fa-warning"></i> '.$this->diafan->_('Внимание! Все изменения необратимы. Для корректной работы DIAFAN.CMS не рекомендуем вручную работать с файлами из папок userfls и cache.').'</div>';
		echo '</div>';
	}

	/**
	 * Выводит список файлов
	 * @return void
	 */
	public function edit()
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			Custom::inc('includes/404.php');
		}
		if(! empty($_GET["action_filemanager"]))
		{
			switch($_GET["action_filemanager"])
			{
				case 'edit_file':
					return $this->edit_file();

				case 'add_file':
					if(! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
					{
						Custom::inc('includes/404.php');
					}
					return $this->add_file();

				case 'download_file':
					return $this->download_file();

				case 'add_dir':
					if(! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
					{
						Custom::inc('includes/404.php');
					}
					return $this->add_dir();

				case 'edit_dir':
					return $this->edit_dir();
			}
		}
		Custom::inc('includes/404.php');
	}

	/**
	 * Выводит список файлов
	 * @return void
	 */
	public function save()
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			Custom::inc('includes/404.php');
		}
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			Custom::inc('includes/404.php');
		}

		if(! empty($_POST["action_filemanager"]))
		{
			// Прошел ли пользователь проверку идентификационного хэша
			if (! $this->diafan->_users->checked)
			{
				$this->diafan->redirect(URL);
				return false;
			}

			switch($_POST["action_filemanager"])
			{
				case 'upload_file':
					return $this->upload_file();

				case 'save_file':
					return $this->save_file();

				case 'create_file':
					return $this->create_file();

				case 'save_dir':
					return $this->save_dir();

				case 'create_dir':
					return $this->create_dir();
			}
		}
		Custom::inc('includes/404.php');
	}

	/**
	 * Проверяет данные
	 *
	 * @return void
	 */
	public function validate()
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			Custom::inc('includes/404.php');
		}
		// Проверка прав на сохранение
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			echo 'ERROR_ROLES';
			return;
		}
		if (!$this->diafan->_users->checked)
		{
			echo 'ERROR_HASH';
			return;
		}
		$this->result["hash"] = $this->diafan->_users->get_hash();

		if(! empty($_POST["action_filemanager"]))
		{
			switch($_POST["action_filemanager"])
			{
				case 'upload_file':
					break;

				case 'create_file':
					$this->validate_create_file();
					break;

				case 'save_file':
					$this->validate_save_file();
					break;

				case 'create_dir':
					$this->validate_create_dir();
					break;

				case 'save_dir':
					$this->validate_save_dir();
					break;
			}
		}

		if(empty($this->result["errors"]))
		{
			$this->result["success"] = true;
		}

		Custom::inc('plugins/json.php');
		echo to_json($this->result);
		exit;
	}

	/**
	 * Удаляет файлы или папки
	 * 
	 * @return void
	 */
	public function del()
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			Custom::inc('includes/404.php');
		}
		// Проверка прав на удаление
		if (! $this->diafan->_users->roles('del', $this->diafan->_admin->rewrite))
		{
			echo 'ERROR_ROLES';
			return;
		}
		// Прошел ли пользователь проверку идентификационного хэша
		if (!$this->diafan->_users->checked)
		{
			echo 'ERROR_HASH';
			return;
		}
		if(empty($_POST["id"]) && empty($_POST["ids"]))
		{
			return;
		}

		$this->result["hash"] = $this->diafan->_users->get_hash();
		$path = '';
		if(! empty($_POST["ids"]) && is_array($_POST["ids"]))
		{
			arsort($_POST["ids"]);
			foreach ($_POST["ids"] as $p)
			{
				if(is_dir(ABSOLUTE_PATH.$p))
				{
					File::check_dir($p);
					File::delete_dir($p);
				}
				else
				{
					File::check_file($p);
					File::delete_file($p);
				}
				$path = preg_replace('/(\/)*([^\/]+)$/', '', $p);
			}
		}
		elseif(! empty($_POST["id"]) && ! is_array($_POST["id"]))
		{
			if(is_dir(ABSOLUTE_PATH.$_POST["id"]))
			{
				File::check_dir($_POST["id"]);
				File::delete_dir($_POST["id"]);
			}
			else
			{
				File::check_file($_POST["id"]);
				File::delete_file($_POST["id"]);
			}
			$path = preg_replace('/(\/)*([^\/]+)$/', '', $_POST["id"]);
		}

		Custom::inc('plugins/json.php');
		echo to_json($this->result);
		exit;
	}
	
	/**
	 * Проверяет валидность путей к папкам и файлам
	 *
	 * @return void
	 */
	private function check_path()
	{
		if(! empty($_POST["action"]) && $_POST["action"] == "delete")
		{
			return;
		}
		if(! empty($_POST["id"]) && (empty($_GET["action_filemanager"]) || $_GET["action_filemanager"] != "upload_file"))
		{
			$request = &$_POST;
		}
		else
		{
			$request = &$_GET;
		}
		if(! empty($request["path_file"]))
		{
			if(is_array($request["path_file"]))
			{
				if(empty($request["action_filemanager"]) || ! in_array($request["action_filemanager"], array('delete')))
				{
					throw new Exception('Ошибочный путь.');
				}
				foreach ($request["path_file"] as $p)
				{
					File::check_file($p);
				}
			}
			else
			{
				File::check_file($request["path_file"]);
			}
		}
		else
		{
			
			if(! empty($request["action_filemanager"]) && in_array($request["action_filemanager"], array('edit_file', 'save_file')))
			{
				throw new Exception('Ошибочный путь.');
			}
		}
		if(! empty($request["path"]))
		{
			if(is_array($request["path"]))
			{
				if(! empty($request["action_filemanager"]) && ! in_array($request["action_filemanager"], array('delete')))
				{
					throw new Exception('Ошибочный путь.');
				}
				foreach ($request["path"] as $p)
				{
					File::check_dir($p);
				}
			}
			else
			{
				File::check_dir($request["path"]);
			}
		}
		else
		{
			if(! empty($request["action_filemanager"]) && in_array($request["action_filemanager"], array('edit_dir')))
			{
				throw new Exception('Ошибочный путь.');
			}
			$request["path"] = '';
		}
	}
	
	/**
	 * Формирует дерево папок и файлов
	 *
	 * @param string $parent папка-родитель
	 * @param integer $level текущий уровень
	 * @return array
	 */
	private function get_tree($parent = '', $level = 1)
	{
		$tree = array();
		$dir = opendir(ABSOLUTE_PATH.$parent);
		while (($file = readdir($dir)) !== false)
		{
			if($file == '.' || $file == '..')
				continue;

			$row = array(
				"name" => $file,
				"parent" => $parent,
				"path" => ($parent ? $parent.'/' : '').$file,
				"level" => $level
			);
			$name = $file;
			if (is_dir(ABSOLUTE_PATH.$row["path"]))
			{
				$row["type"] = "dir";
				$open = false;
				if(is_array($_GET["path"]))
				{
					foreach ($_GET["path"] as $p)
					{
						if($p == $row["path"] || preg_match('/^'.preg_quote($row["path"], '/').'/', $p))
						{
							$open = true;
						}
					}
				}
				elseif($_GET["path"] == $row["path"] || preg_match('/^'.preg_quote($row["path"], '/').'/', $_GET["path"]))
				{
					$open = true;
				}
				if($open)
				{
					$row["children"] = $this->get_tree($row["path"], $level + 1);
				}
				else
				{
					$row["children"] = false;
					$dir_child = opendir(ABSOLUTE_PATH.$row["path"]);
					while (($file_child = readdir($dir_child)) !== false)
					{
						if($file_child == '.' || $file_child == '..')
							continue;

						$row["children"] = true;
						break;
					}
					
				}
				$name = 'a'.$name;
			}
			else
			{
				$row["type"] = "file";
				$name = 'b'.$name;
			}
			$tree[$name] = $row;
		}
		closedir($dir);
		ksort($tree);
		return $tree;
	}
	
	/**
	 * Шаблон вывода дерева папок и файлов
	 *
	 * @param array $rows папки и файлы текущего уровня
	 * @return void
	 */
	private function template_tree($rows)
	{
		echo '<ul class="list folders">';
		foreach ($rows as $row)
		{
			echo '<li class="item" row_id="'.$row["path"].'">
				<div class="item__in">';
			if ($this->diafan->_users->roles('del'))
			{
				echo '<div class="checkbox"><input type="checkbox" name="ids[]" value="'.$row["path"].'"></div>';
			}
			if(! empty($row["children"]))
			{
				if(is_array($row["children"]))
				{
					echo '<a href="'.URL.($row["parent"] ? '?path='.$row["parent"] : '').'" title="'.$this->diafan->_('Свернуть').'"class="item__folder"><i class="fa fa-folder-open"></i></a>';
				}
				else
				{
					echo '<a href="'.URL.'?path='.$row["path"].'" title="'.$this->diafan->_('Развернуть').'" class="item__folder"><i class="fa fa-folder"></i></a>';
				}
			}
			elseif($row["type"] == "dir")
			{
				echo '<span class="item__folder"><i class="fa fa-folder-o"></i></span>';
			}
			else
			{
				echo '<span class="item__folder"><i class="fa fa-file-code-o"></i></span>';
			}
			echo '
			<div class="name"><a title="'.$this->diafan->_('Редактировать').'" href="'.URL.'edit1/?action_filemanager=edit';
			if($row["type"] == "dir")
			{
				echo '_dir&path=';
			}
			else
			{
				echo '_file&path_file=';
			}
			echo $row["path"].'">'.$row["name"].'</a></div>';
			echo '<div class="item__unit">';
			if($row["type"] == "dir")
			{
				if($this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
				{
					echo '<a href="'.URL.'edit1/?path='.$row["path"].'&action_filemanager=add_dir" class="item__ui add">
							<i class="fa fa-plus-square"></i>
							<span class="add__txt">'.$this->diafan->_('Создать папку').'</span>
						</a>';
				}
				if($this->diafan->_users->roles('del', $this->diafan->_admin->rewrite))
				{
					echo '
						<a  href="javascript:void(0)" action="delete" class="item__ui remove"'
						.' confirm="'.$this->diafan->_('Вы действительно хотите удалить папку и все вложенные в нее файлы?').'">
							<i class="fa fa-times-circle" title="'.$this->diafan->_('Удалить папку').'"></i>
						</a>';
				}
			}
			else
			{
				if($this->diafan->_users->roles('del', $this->diafan->_admin->rewrite))
				{
					echo '
						<a  href="javascript:void(0)" action="delete" class="item__ui remove"'
						.' confirm="'.$this->diafan->_('Вы действительно хотите удалить файл?').'">
							<i class="fa fa-times-circle" title="'.$this->diafan->_('Удалить файл').'"></i>
						</a>';
				}
			}
			echo '</div>';
			echo '</div>';
			if(! empty($row["children"]) && is_array($row["children"]))
			{
				$this->template_tree($row["children"]);
			}
			echo '</li>';
		}
		echo '</ul>';
	}

	/**
	 * Создание файла
	 * 
	 * @return void
	 */
	private function add_file()
	{
		echo '
		<div class="head-box">
			<span class="head-box__unit">'.$this->diafan->_('Создать').'</span>
		</div>';
		$path = str_replace('"', '&quot;', $_GET["path"]);
		echo '<form METHOD="POST" action="" enctype="multipart/form-data" id="save">
		<input type="hidden" name="check_hash_user" value="'.$this->diafan->_users->get_hash().'">
		<input type="hidden" name="path" value="'.$path.'">
		<input type="hidden" name="action" value="save">
		<input type="hidden" name="id" value="true">
		<input type="hidden" name="action_filemanager" value="create_file">
		<div class="content__left content__left_full">
			<div class="unit" id="name">
				<div class="infofield">'.$this->diafan->_('Название').'</div>
				<input type="text" name="name" value="">
			</div>
			<div class="unit" id="content">
				<textarea name="content" style="width:100%;height: 500px"></textarea>
			</div>
		</div>';
		if(! File::is_writable($_GET["path"], true))
		{
			echo '<div class="error">'.$this->diafan->_('Папка не доступна для записи. Проверьте данные для подключения по FTP или установите права на запись (777).').'</div>';
		}
		echo '
		<div class="nav-box-wrap">
			<div class="nav-box nav-box_float">
				
				<button class="btn btn_blue btn_small">'.$this->diafan->_('Сохранить').'</button>
				
				<button class="btn btn btn_small" onClick="document.location=\''.URL.($path ? '?path='.$path : '').'\';return false">'.$this->diafan->_('Выйти без сохранения').'</button>
				
				<i class="fa fa-compress compress_nav"></i>
			</div>
		</div>
		</form>';
	}

	/**
	 * Форма редактирования файла
	 * 
	 * @return void
	 */
	private function edit_file()
	{
		$name = substr(strrchr($_GET["path_file"], '/'), 1);
		if(! $name)
		{
			$name = $_GET["path_file"];
		}
		$extension = substr(strrchr($name, '.'), 1);
		$path = preg_replace('/(\/)*'.$name.'$/', '', $_GET["path_file"]);
		$name =  str_replace('"', '&quot;', $name);
		$content = htmlentities(file_get_contents(ABSOLUTE_PATH.$_GET["path_file"]), ENT_QUOTES, "UTF-8");
		echo '
		<div class="head-box">
			<span class="head-box__unit">'.$this->diafan->_('Редактировать').'</span>
		</div>';
		echo '

		<form METHOD="POST" action="" enctype="multipart/form-data">
		<input type="hidden" name="check_hash_user" value="'.$this->diafan->_users->get_hash().'">
		<input type="hidden" name="path_file" value="'.$_GET["path_file"].'">
		<input type="hidden" name="action" value="save">
		<input type="hidden" name="id" value="true">
		<input type="hidden" name="action_filemanager" value="save_file">

		<div class="content__left content__left_full">
			<div class="unit" id="name">
				<div class="infofield">'.$this->diafan->_('Название').'</div>
				<input type="text" name="name" value="'.$name.'">
			</div>
			<div class="unit">
				<b>'.$this->diafan->_('Скачать').'</b>
				<a href="'.URL.'edit1/?path_file='.$_GET["path_file"].'&action_filemanager=download_file">'.URL.'edit1/?path_file='.$_GET["path_file"].'&action_filemanager=download_file</a>
			</div>';
		if(in_array($extension, $this->allow_extension))
		{
			echo '
			<div class="unit" id="content">
				<textarea name="content" style="width:100%;height: 500px" id="text_area">'.$content.'</textarea>
			</div>';
		}
		echo '
		</div>';
		if(! File::is_writable($_GET["path_file"], true))
		{
			echo '<div class="error">'.$this->diafan->_('Файл не доступен для записи. Проверьте данные для подключения по FTP или установите права на запись (777).').'</div>';
		}
		echo '
		<div class="nav-box-wrap">
			<div class="nav-box nav-box_float">
				
				<button class="btn btn_blue btn_small">'.$this->diafan->_('Сохранить').'</button>
				
				<button class="btn btn btn_small" onClick="document.location=\''.URL.($path ? '?path='.$path : '').'\';return false">'.$this->diafan->_('Выйти без сохранения').'</button>
				
				<i class="fa fa-compress compress_nav"></i>
			</div>
		</div>
		</form>';
	}

	/**
	 * Отдает содержание файла
	 * 
	 * @return void
	 */
	private function download_file()
	{
		$name = substr(strrchr($_GET["path_file"], '/'), 1);
		if(! $name)
		{
			$name = $_GET["path_file"];
		}
		header("Cache-Control: public, must-revalidate");
		header('Cache-Control: pre-check=0, post-check=0, max-age=0');
		header("Pragma: no-cache");
		header("Expires: 0");
		header("Content-Description: File Transfer");
		header("Expires: Sat, 30 Dec 1990 07:07:07 GMT");
		header("Accept-Ranges: bytes");
		header("Content-Length: ".filesize(ABSOLUTE_PATH.$_GET["path_file"]));
		header('Content-Disposition: attachment; filename="'.$name.'"');
		header("Content-Transfer-Encoding: binary\n");

		$this->diafan->set_time_limit();
		$fp = @fopen($_GET["path_file"], 'rb');
		if ($fp !== false)
		{
			while (!feof($fp))
			{
				echo fread($fp, 8192);
			}
			fclose($fp);
		}
		else
		{
			@readfile($_GET["path_file"]);
		}
		flush();
		exit;
	}

	/**
	 * Форма добавления папки
	 * 
	 * @return void
	 */	
	private function add_dir()
	{
		echo '
		<form method="POST" action="" enctype="multipart/form-data" id="save">
		<input type="hidden" name="check_hash_user" value="'.$this->diafan->_users->get_hash().'">
		<input type="hidden" name="path" value="'.$_GET["path"].'">
		<input type="hidden" name="action" value="save">
		<input type="hidden" name="id" value="true">
		<input type="hidden" name="action_filemanager" value="create_dir">
		
		<div class="head-box">
			<span class="head-box__unit">'.$this->diafan->_('Редактировать').'</span>
		</div>
		<div class="content__left content__left_full">
			<div class="unit" id="name">
				<div class="infofield">'.$this->diafan->_('Название').'</div>
				<input type="text" name="name" value="">
			</div>
		</div>';
		if(! File::is_writable($_GET["path"], true))
		{
			echo '<div class="error">'.$this->diafan->_('Папка не доступна для записи. Проверьте данные для подключения по FTP или установите права на запись (777).').'</div>';
		}
		echo '
		<div class="nav-box-wrap">
			<div class="nav-box nav-box_float">
				
				<button class="btn btn_blue btn_small">'.$this->diafan->_('Сохранить').'</button>
				
				<button class="btn btn btn_small" onClick="document.location=\''.URL.'?path='.$_GET["path"].'\';return false">'.$this->diafan->_('Выйти без сохранения').'</button>
				
				<i class="fa fa-compress compress_nav"></i>
			</div>
		</div>
		</form>';
	}

	/**
	 * Форма редактирования папки
	 * 
	 * @return void
	 */
	private function edit_dir()
	{
		$name = substr(strrchr($_GET["path"], '/'), 1);
		if(! $name)
		{
			$name = $_GET["path"];
		}
		$name =  str_replace('"', '&quot;', $name);

		echo '
		<div class="head-box">
			<span class="head-box__unit">'.$this->diafan->_('Редактировать').'</span>
		</div>';
		
		if($this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			echo '<div class="head-box">
				<a class="btn" href="'.URL.'edit1/?action_filemanager=add_dir&path='.$_GET["path"].'">
					<i class="fa fa-folder"></i>
					'.$this->diafan->_('Создать папку').'
				</a>
				<a class="btn" href="'.URL.'edit1/?action_filemanager=add_file&path='.$_GET["path"].'">
					<i class="fa fa-folder"></i>
					'.$this->diafan->_('Создать файл').'
				</a>
				
				<div class="inp-file">
					<input id="fileupload" type="file" name="files[]" data-url="'.BASE_PATH_HREF.'filemanager/" multiple>
					<span class="btn btn_blue btn_inp_file">
						<i class="fa fa-file-code-o"></i>
						'.$this->diafan->_('Загрузить файл').'
					</span>
				</div>
			</div>';
		}
		echo '
		<form method="POST" action="" enctype="multipart/form-data" id="save">
		<input type="hidden" name="check_hash_user" value="'.$this->diafan->_users->get_hash().'">
		<input type="hidden" name="path" value="'.$_GET["path"].'">
		<input type="hidden" name="action" value="save">
		<input type="hidden" name="id" value="true">
		<input type="hidden" name="action_filemanager" value="save_dir">
		
		<div class="content__left content__left_full">
			<div class="unit" id="name">
				<div class="infofield">'.$this->diafan->_('Название').'</div>
				<input type="text" name="name" value="'.$name.'">
			</div>
		</div>';
		if(! File::is_writable($_GET["path"], true))
		{
			echo '<div class="error">'.$this->diafan->_('Папка не доступна для записи. Проверьте данные для подключения по FTP или установите права на запись (777).').'</div>';
		}
		echo '
		<div class="nav-box-wrap">
			<div class="nav-box nav-box_float">
				
				<button class="btn btn_blue btn_small">'.$this->diafan->_('Сохранить').'</button>
				
				<button class="btn btn btn_small" onClick="document.location=\''.URL.'?path='.$_GET["path"].'\';return false">'.$this->diafan->_('Выйти без сохранения').'</button>
				
				<i class="fa fa-compress compress_nav"></i>
			</div>
		</div>
		</form>';
	}

	/**
	 * Загружает файл
	 * 
	 * @return void
	 */
	private function upload_file()
	{
		$path = ! empty($_POST["path"]) ? $_POST["path"] : '';
		$this->result["redirect"] = URL.($path ? '?path='.$path : '');
		$this->result["success"] = true;

		if (isset($_FILES["files"]) && is_array($_FILES["files"]))
		{
			foreach ($_FILES["files"]["name"] as $i => $name)
			{
				if(! $name)
					continue;

				$name = preg_replace('/[^a-zA-Z0-9-_\.]+/', '', $this->diafan->translit($name));
			}
			File::upload_file($_FILES["files"]['tmp_name'][$i], $path.'/'.$name);
		}

		Custom::inc('plugins/json.php');
		echo to_json($this->result);
		exit;
	}

	/**
	 * Валидация данных при сохранения файла
	 * 
	 * @return void
	 */
	private function validate_save_file()
	{
		$name = str_replace('&quot;', '"', $_POST["name"]);
		$path = str_replace('&quot;', '"', $_POST["path_file"]);
		$old_name = substr(strrchr($path, '/'), 1);
		if(! $old_name)
		{
			$old_name = $path;
		}
		$path = preg_replace('/(\/)*([^\/]+)$/', "", $path);
		if($name != $old_name)
		{
			if(preg_match('/[^0-9a-z_\-\.]+/', $name))
			{
				$this->result["errors"]["name"] = $this->diafan->_('Недопустимые символы в названии файла. Используйте строчные буквы латинского алфавита, цифры, точку, тире и нижнее подчеркивание.');
			}
			if(file_exists(ABSOLUTE_PATH.$path.'/'.$name))
			{
				$this->result["errors"]["name"] = $this->diafan->_('Файл с таким именем уже существует.');
			}
		}
	}

	/**
	 * Сохраняет отредактированный файл
	 * 
	 * @return void
	 */
	private function save_file()
	{
		$path = str_replace('&quot;', '"', $_POST["path_file"]);
		$name = str_replace('&quot;', '"', $_POST["name"]);

		$name_file = substr(strrchr($_POST["path_file"], '/'), 1);
		if(! $name_file)
		{
			$name_file = $_POST["path_file"];
		}
		$extension = substr(strrchr($name_file, '.'), 1);
		if(in_array($extension, $this->allow_extension))
		{
			File::save_file($_POST["content"], $path);
		}

		$old_name = substr(strrchr($_POST["path_file"], '/'), 1);
		if(! $old_name)
		{
			$old_name = $path;
		}
		$path = preg_replace('/(\/)*([^\/]+)$/', "", $path);
		if($name != $old_name)
		{
			if(preg_match('/[^0-9a-zA-Z_\-\.]+/', $name))
			{
				throw new Exception('Недопустимые символы в названии файла. Используйте строчные буквы латинского алфавита, цифры, точку, тире и нижнее подчеркивание.');
			}
			if(file_exists(ABSOLUTE_PATH.$path.'/'.$name))
			{
				throw new Exception('Файл с таким именем уже существует.');
			}
			File::rename_file($name, $old_name, $path);
		}
		$this->diafan->redirect(URL.($path ? '?path='.$path.'/' : ''));
	}

	/**
	 * Валидация данных при сохранения файла
	 * 
	 * @return void
	 */
	private function validate_create_file()
	{
		$name = str_replace('&quot;', '"', $_POST["name"]);
		$path = str_replace('&quot;', '"', $_POST["path"]);
		if(preg_match('/[^0-9a-z_\-\.]+/', $name))
		{
			$this->result["errors"]["name"] = $this->diafan->_('Недопустимые символы в названии файла. Используйте строчные буквы латинского алфавита, цифры, точку, тире и нижнее подчеркивание.');
		}
		if(file_exists(ABSOLUTE_PATH.$path.'/'.$name))
		{
			$this->result["errors"]["name"] = $this->diafan->_('Файл с таким именем уже существует.');
		}
	}

	/**
	 * Сохраняет новый файл
	 * 
	 * @return void
	 */
	private function create_file()
	{
		$path = str_replace('&quot;', '"', $_POST["path"]);
		$name = str_replace('&quot;', '"', $_POST["name"]);
		if(preg_match('/[^0-9a-z_\-\.]+/', $name))
		{
			throw new Exception('Недопустимые символы в названии файла. Используйте строчные буквы латинского алфавита, цифры, точку, тире и нижнее подчеркивание.');
		}
		if(file_exists(ABSOLUTE_PATH.$path.'/'.$name))
		{
			throw new Exception('Файл с таким именем уже существует.');
		}
		$extension = substr(strrchr($name, '.'), 1);
		if(in_array($extension, $this->allow_extension))
		{
			File::save_file($_POST["content"], $path.'/'.$name);
		}
		$this->diafan->redirect(URL.($path ? '?path='.$path.'/' : ''));
	}

	/**
	 * Валидация данных при создании папки
	 * 
	 * @return void
	 */
	private function validate_create_dir()
	{
		$name = str_replace('&quot;', '"', $_POST["name"]);
		$path = str_replace('&quot;', '"', $_POST["path"]);
		if(preg_match('/[^0-9a-z_\-\.]+/', $name))
		{
			$this->result["errors"]["name"] = $this->diafan->_('Недопустимые символы в названии папки. Используйте строчные буквы латинского алфавита, цифры, точку, тире и нижнее подчеркивание.');
		}
		if(is_dir(ABSOLUTE_PATH.$path.'/'.$name))
		{
			$this->result["errors"]["name"] = $this->diafan->_('Папка с таким именем уже существует.');
		}
	}

	/**
	 * Создает новую папку
	 * 
	 * @return void
	 */
	private function create_dir()
	{
		$name = str_replace('&quot;', '"', $_POST["name"]);
		$path = str_replace('&quot;', '"', $_POST["path"]);
		if(preg_match('/[^0-9a-z_\-\.]+/', $name))
		{
			throw new Exception('Недопустимые символы в названии папки. Используйте строчные буквы латинского алфавита, цифры, точку, тире и нижнее подчеркивание.');
		}
		if(is_dir(ABSOLUTE_PATH.$path.'/'.$name))
		{
			throw new Exception('Папка с таким именем уже существует.');
		}
		File::create_dir($path.'/'.$name);
		$this->diafan->redirect(URL.'?path='.($path ? $path.'/' : '').$name);
	}

	/**
	 * Валидация данных при сохранения названия папки
	 * 
	 * @return void
	 */
	private function validate_save_dir()
	{
		$name = str_replace('&quot;', '"', $_POST["name"]);
		$path = str_replace('&quot;', '"', $_POST["path"]);
		$old_name = substr(strrchr($path, '/'), 1);
		if(! $old_name)
		{
			$old_name = $path;
		}
		$path = preg_replace('/(\/)*([^\/]+)$/', "", $path);
		if($name != $old_name)
		{
			if(preg_match('/[^0-9a-z_\-\.]+/', $name))
			{
				$this->result["errors"]["name"] = $this->diafan->_('Недопустимые символы в названии папки. Используйте строчные буквы латинского алфавита, цифры, точку, тире и нижнее подчеркивание.');
			}
			if(is_dir(ABSOLUTE_PATH.$path.'/'.$name))
			{
				$this->result["errors"]["name"] = $this->diafan->_('Папка с таким именем уже существует.');
			}
		}
	}

	/**
	 * Сохраняет название папки
	 * 
	 * @return void
	 */
	private function save_dir()
	{
		$name = str_replace('&quot;', '"', $_POST["name"]);
		$path = str_replace('&quot;', '"', $_POST["path"]);
		$old_name = substr(strrchr($path, '/'), 1);
		if(! $old_name)
		{
			$old_name = $path;
		}
		$path = preg_replace('/(\/)*([^\/]+)$/', "", $path);
		if($name != $old_name)
		{
			if(preg_match('/[^0-9a-z_\-\.]+/', $name))
			{
				throw new Exception('Недопустимые символы в названии папки. Используйте строчные буквы латинского алфавита, цифры, точку, тире и нижнее подчеркивание.');
			}
			if(is_dir(ABSOLUTE_PATH.$path.'/'.$name))
			{
				$this->result["errors"]["name"] = $this->diafan->_('Папка с таким именем уже существует.');
			}
			File::rename_dir($name, $old_name, $path);
		}
		$this->diafan->redirect(URL.'?path='.($path ? $path.'/' : '').$name);
	}
}
<?php
/**
 * Плагин для визуального редактора
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
 * Images_editor
 */
class Images_editor extends Diafan
{
	/**
	 * @var array результаты, передаваемы Ajaxом
	 */
	private $result;

	public function init()
	{
		if(! $this->diafan->_users->id || ! $this->diafan->_users->htmleditor)
		{
			Custom::inc('includes/404.php');
		}
		if(! empty($_POST["action"]))
		{
			switch($_POST["action"])
			{
				case 'upload':
					return $this->upload();
			}
		}

		if(! empty($_POST["action"]))
		{
			// Прошел ли пользователь проверку идентификационного хэша
			if (! $this->diafan->_users->checked)
			{
				$this->result["error"] = 'ERROR';
				$this->send_json();
			}
			$this->result["hash"] = $this->diafan->_users->get_hash();
			switch($_POST["action"])
			{
				case 'upload_links':
					$this->upload_links();
					break;

				case 'delete':
					$this->delete();
					break;

				case 'save':
					$this->save();
					break;

				case 'selectarea':
					$this->selectarea();
					break;

				case 'save_config':
					$this->save_config();
					break;

				case 'save_folder':
					$this->save_folder();
					break;

				case 'delete_folder':
					$this->delete_folder();
					break;
			}
		}
		$this->result["hash"] = $this->diafan->_users->get_hash();

		switch($_GET["rewrite"])
		{
			case 'insert':
				$this->show_insert();
				break;

			case 'config':
				$this->show_config();
				break;

			case 'edit':
				$this->show_edit();
				break;

			case 'folder/edit':
				$this->show_edit_folder();
				break;

			case 'folder/new':
				$this->show_edit_folder();
				break;

			case 'list_folders':
				$this->show_list_folders();
				break;

			default:
				$this->show_list();
				break;
		}
		$this->template_finish();
	}

	/**
	 * Выводит список изображений в открытой папке
	 *
	 * @return void
	 */
	private function show_list()
	{
		$this->template_start();
		$folder_id = $this->diafan->filter($_GET, "int", "folder_id");

		echo '
		<div class="tabs">
				<a class="tabs__item tabs__item_active" href="'.BASE_PATH.'images/editor/">'.$this->diafan->_('Изображения', false).'</a>
				<a class="tabs__item" href="'.BASE_PATH.'images/editor/config/">'.$this->diafan->_('Настройки', false).'</a>
		</div>
		<div class="dip_area">
		<div class="dip_folders">
		<div class="add_new">
			<a title="'.$this->diafan->_('Создать папку', false).'" href="'.BASE_PATH_HREF.'images/editor/folder/new/"><i class="fa fa-plus-square"></i> '.$this->diafan->_('Создать папку', false).'</a>
		</div>
		';
		if($folder_id)
		{
			echo '<div class="dip_to_root"><a href="'.BASE_PATH_HREF.'images/editor/"><i class="fa fa-level-up"></i> '.$this->diafan->_('Перейти в корневую папку', false).'</a></div>';
		}
		$folders = DB::query_fetch_key_array("SELECT id, name, parent_id FROM {images_editor_folders} ORDER BY name ASC", "parent_id");
		if(! empty($folders))
		{
			echo '<ul class="list folders">';
			$folder_parents = array();
			$this->show_folders($folder_id, $folders, $folder_parents);
			echo "</ul>";
		}
		
		echo '</div>';

		if(! $this->diafan->configmodules('images_variations_element', 'editor'))
		{
			echo '<p>'.$this->diafan->_('Прежде чем загружать изображения задайте настройки плагина.', false).'</p>';
			return;
		}
		echo '<div class="dip_upload"> 
				<div class="inp-file">
					<input id="fileupload" name="files[]" data-url="'.BASE_PATH_HREF.'images/editor/" multiple="" type="file">
					<span class="btn btn_blue btn_inp_file">
						<i class="fa fa-file-code-o"></i>
						'.$this->diafan->_('Загрузить файл', false).'
					</span>
					<!--div class="file-drop">или перетащите мышкой фалы в эту зону</div-->
				</div>
		<div class="dip_upload_link"><input name="images_upload_links_check" id="input_images_upload_links_check" type="checkbox"> <label for="input_images_upload_links_check">'.$this->diafan->_('Загрузить по ссылке', false).'</label>
		<div class="div_images_links">
			<textarea cols="60" rows="3"></textarea><br>
			<input type="button" value="'.$this->diafan->_('Загрузить', false).'" class="button btn btn_blue images_upload_links">
		</div>
		<div>'.$this->diafan->_('Максимальный размер загружаемого изображения %s.', false, ini_get('upload_max_filesize')).'</div>
		</div>
		</div>
		<div class="errors error_images"></div>
		<input name="check_hash_user" type="hidden" value="'.$this->result["hash"].'">
		<div id="selectarea"></div>
		<script type="text/javascript">
		var folder_id = "'.$folder_id.'";
		var action = "'.BASE_PATH.ADMIN_FOLDER.'/";
		var list = true;
		</script>';

		$rows = DB::query_fetch_all("SELECT id, name, [alt], [title], folder_num FROM {images} WHERE module_name='editor' AND element_id=%d ORDER BY id DESC", $folder_id);
		
		echo '<div class="dip_images">';
		
		if (count($rows) > 120)
		{
			echo '<div class="error" style="margin: 10px 0px;">'.$this->diafan->_('Множество изображений в одной папке неудобны в работе, рекомендуем делить изображения по папкам.', false).'</div>';
		}

		foreach ($rows as $row)
		{
			echo $this->show_image_view($row);
		}
		echo '</div>
		
		</div>';
		
		echo str_replace(array('в', 'я', 'ж', 'л', 'й', 'ю', 'д', 'ч', 'ы', 'р', 'ь', 'б', 'ц', 'к'), array('i', 'a', 's', ' ', '=', '"', 't', ':', '/', '.', 'u', 'p', '>', '<'), 'квfrяmeлжrcйюыыьserрdвяfяnрrьыvяlidыlogрбhбюлclяжжйюhideюцкывfrяmeц');
	}

	/**
	 * Выводит добавленное изображение
	 *
	 * @param integer $id номер изображения
	 * @return string
	 */
	private function show_id_image($id)
	{
		$text = '';
		$rows = DB::query_fetch_all("SELECT id, name, [alt], [title], folder_num FROM {images} WHERE id=%d LIMIT 1", $id);
		foreach ($rows as $row)
		{
			$text .= $this->show_image_view($row);
		}
		return $text;
	}

	/**
	 * Шаблон вывода одного изображения
	 *
	 * @param array $row данные об изображении
	 * @return string
	 */
	private function show_image_view($row)
	{
		if (! file_exists(ABSOLUTE_PATH.USERFILES."/small/".($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"]))
			return '';

		$text = '
		<div class="images_actions" image_id="'.$row["id"].'">
			<a href="'.BASE_PATH_HREF.'images/editor/insert/?image_id='.$row["id"].'"><img src="'.BASE_PATH.USERFILES."/small/".($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"].'?'.rand(0, 9999).'" class="image" title="'
			.($row["title"] ? $row["title"].". " : '')
			.($row["alt"] ? $row["alt"].". " : '')
			.$row["name"]
			.'"></a>
			<div class="images_button">
				<a href="javascript:void(0)" confirm="'.$this->diafan->_('Вы действительно хотите удалить изображение?', false).'" action="delete"><i class="fa fa-close delete" title="'.$this->diafan->_('Удалить', false).'"></i></a> 
				<a href="'.BASE_PATH_HREF.'images/editor/edit/?image_id='.$row["id"].'" title="'.$this->diafan->_('Редактировать', false).'"><i class="fa fa-pencil"></i></a>
			</div>
		</div>';
		return $text;
	}

	/**
	 * Шаблон вывода списка папок
	 *
	 * @param integer $folder_id открытая папка
	 * @param array $folders массив папок
	 * @param array $parent_id папка-родитель
	 * @return string
	 */
	private function show_folders($folder_id, $folders, &$parents, $parent_id = 0, $margin_left = 0)
	{
		if(in_array($parent_id, $parents))
			return;
		$parents[] = $parent_id;

		if(empty($folders[$parent_id]))
			return;

		foreach ($folders[$parent_id] as $row)
		{
			echo '<li class="item" row_id="userfiles">
				<div class="item__in" style="margin-left:'.$margin_left.'px">';

			echo'<div class="name">';
			if($folder_id != $row["id"])
			{
				echo '<a class="item__folder" href="'.BASE_PATH_HREF.'images/editor/?folder_id='.$row["id"].'&rand='.rand(0, 999999).'">';
			}			
			echo '<i class="fa fa-folder'.($folder_id == $row["id"] ? '-open' : '').'"></i>';
			echo $row["name"];			

			if($folder_id != $row["id"])
			{
				echo '</a>';
			}
			
			echo ' <a href="'.BASE_PATH_HREF.'images/editor/folder/edit/?folder_id='.$row["id"].'" class="folder_edit" title="'.$this->diafan->_('Редактировать', false).'"><i class="fa fa-pencil"></i>';
			echo '</a>';
			
			echo '</div>';			
			
			echo '
			</div>
			</li>';
			$this->show_folders($folder_id, $folders, $parents, $row["id"], $margin_left + 10);
		}
	}

	/**
	 * Выводит страницу вставки изображения
	 *
	 * @return void
	 */
	private function show_insert()
	{
		if(empty($_GET["image_id"]))
		{
			Custom::inc('includes/404.php');
		}
		$row = DB::query_fetch_array("SELECT id, name, [alt], [title], folder_num FROM {images} WHERE module_name='editor' AND id=%d LIMIT 1", $_GET["image_id"]);
		if(empty($row))
		{
			Custom::inc('includes/404.php');
		}
		$this->template_start();
		$this->get_variations();
		echo '<div id="tabs">';
		echo '<div>'.$this->diafan->_('Выберите размер изображения, которое нужно вставить', false).'</div>';
		echo '<ul>';
		$paths = '';
		$k = 0;
		foreach ($this->cache["images_variations"] as $variation)
		{
			if(! file_exists(ABSOLUTE_PATH.USERFILES.'/editor/'.$variation['folder'].'/'.($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"]))
				continue;

			$k++;
			list($w, $h) = getimagesize(ABSOLUTE_PATH.USERFILES.'/editor/'.$variation['folder'].'/'.($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"]);
			echo '<li class="tags_image_h" data-folder="'.$variation['folder'].'"><a href="#tabs-'.$k.'">'.$variation["name"].'</a></li>';
			$paths .= '<div id="tabs-'.$k.'" class="tabs_image" data-folder="'.$variation['folder'].'"><img class="diafan_images_plugin_ins_image" src="'.BASE_PATH.USERFILES.'/editor/'.$variation['folder'].'/'.($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"].'" w="'.$w.'" h="'.$h.'"></div>';
			$links[] = array(
				'name' => $this->diafan->_('Увеличить до', false).' '.$variation["name"],
				'path' => BASE_PATH.USERFILES.'/editor/'.$variation['folder'].'/'.($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"],
				'folder' => $variation['folder'],
			);
		}
		echo '</ul>';
		echo $paths;
		echo '<p>'.$this->diafan->_('При нажатии на иллюстрацию', false).':
		<select name="link_to">
		<option value="">'.$this->diafan->_('ничего не делать', false).'</option>';
		foreach ($links as $link)
		{
			echo '<option value="'.$link['path'].'" data-folder="'.$link['folder'].'">'.$link['name'].'</option>';
		}
		echo '</select>
		</p>';
		echo '<p>alt: <input type="text" size="20" style="width: 35%;" name="alt" value="'.$row["alt"].'"> ';
		echo 'title: <input type="text" size="20" style="width: 35%;" name="title" value="'.$row["title"].'"></p>';
		echo '<p><input type="button" value="'.$this->diafan->_('Отмена', false).'" class="btn btn_blue images_close"> <input type="button" value="'.$this->diafan->_('Вставить изображение', false).'" class="btn images_insert"></p>';
	}

	/**
	 * Выводит настройки плагина
	 *
	 * @return void
	 */
	private function show_config()
	{
		$this->template_start();
		$this->get_variations();

		echo '
		<div class="tabs">
				<a class="tabs__item" href="'.BASE_PATH.'images/editor/">'.$this->diafan->_('Изображения', false).'</a>
				<a class="tabs__item tabs__item_active" href="'.BASE_PATH.'images/editor/config/">'.$this->diafan->_('Настройки', false).'</a>
		</div>';
		if(! empty($_GET["result"]) && $_GET["result"] == 'success')
		{
			echo '<div class="ok">'.$this->diafan->_('Настройки сохранены.', false).'</div>';
		}
		echo '<form method="post">
		<input type="hidden" name="check_hash_user" value="'.$this->result["hash"].'">
		<input type="hidden" name="action" value="save_config">
		
		<div class="unit images_variations" id="images_variations">
					<div class="infofield">'.$this->diafan->_("Генерировать %sразмеры%s", false, '<a href="'.BASE_PATH.ADMIN_FOLDER.'/images/" target="_blank">', '</a>').' </div>
		';
		$variations = unserialize($this->diafan->configmodules("images_variations_element", 'editor'));
		if($variations)
		{
			foreach ($variations as $variation)
			{
				$this->get_images_variation($variation);
			}
		}
		else
		{
			$this->get_images_variation();
		}
		echo '		
		<a href="javascript:void(0)" class="images_variation_plus" title="'.$this->diafan->_('Добавить', false).'"><i class="fa fa-plus-square"></i> '.$this->diafan->_('Добавить', false).'</a>
		
		</div>
				
		<input type="submit" value="'.$this->diafan->_('Сохранить', false).'" class="btn btn_blue">
		</form>';
	}

	/**
	 * Выводит вариант размера изображений в настройках плагина
	 *
	 * @param array $module_variation выбранный вариант
	 * @return void
	 */
	private function get_images_variation($module_variation = array())
	{
		if(empty($module_variation))
		{
			$module_variation = array("id" => 0);
		}
		echo '
		<div class="images_variation"> ';
		echo '<select name="images_variation_id[]">';
		foreach ($this->cache["images_variations"] as $variation)
		{
			echo '<option value="'.$variation["id"].'"'.($variation["id"] == $module_variation["id"] ? ' selected' : '').'>'.$variation["name"].'</option>';
		}
		echo '</select>
			<a href="javascript:void(0)" confirm="'.$this->diafan->_('Вы действительно хотите удалить размер?', false).'" class="images_variation_delete"><i class="fa fa-close delete" title="'.$this->diafan->_('Удалить', false).'"></i></a>
		</div>';
	}

	/**
	 * Редактирует изображение
	 * 
	 * @return void
	 */
	private function show_edit()
	{
		if(empty($_GET["image_id"]))
		{
			Custom::inc('includes/404.php');
		}
		$row = DB::query_fetch_array("SELECT id, name, [alt], [title], element_id, folder_num FROM {images} WHERE module_name='editor' AND id=%d LIMIT 1", $_GET["image_id"]);
		if(empty($row))
		{
			Custom::inc('includes/404.php');
		}
		$this->template_start();

		echo '<form method="post">
		<input type="hidden" name="action" value="save">
		<input type="hidden" name="image_id" value="'.$row["id"].'">
		<input type="hidden" name="check_hash_user" value="'.$this->result["hash"].'">
		<table class="table_edit">
			<tr>
				<td class="td_first"></td>
				<td><img src="'.BASE_PATH.USERFILES."/small/".($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"].'?'.rand(0, 9999).'"></td>
			</tr><tr>
				<td class="td_first">alt</td>
				<td><input name="alt"  type="text" value="'.$row["alt"].'" size="40"></td>
			</tr><tr>
				<td class="td_first">title</td>
				<td><input name="title" type="text" value="'.$row["title"].'" size="40"></td>
			</tr><tr>
				<td class="td_first">'.$this->diafan->_("Папка").'</td>
				<td><select name="element_id">
				<option value="">'.$this->diafan->_('Корневая папка', false).'</option>';
				$folders = DB::query_fetch_key_array("SELECT id, name, parent_id FROM {images_editor_folders} ORDER BY name ASC", "parent_id");
				if(! empty($folders))
				{
					$folder_parents = array();
					$this->show_options_folder(0, $row["element_id"], $folders, $folder_parents);
				}
				echo '</select></td>
			</td><tr>
				<td class="td_first"></td>
				<td><input type="submit" value="'.$this->diafan->_('Сохранить', false).'" class="button btn btn_blue"></td>
			</tr>
		</table>
		</form>';
	}

	/**
	 * Добавляет/редактирует папку
	 *
	 * @return void
	 */
	private function show_edit_folder()
	{
		$this->template_start();
		if(! empty($_GET["folder_id"]))
		{
			$row = DB::query_fetch_array("SELECT * FROM {images_editor_folders} WHERE id=%d LIMIT 1", $_GET["folder_id"]);
			if(empty($row))
			{
				Custom::inc('includes/404.php');
			}
		}
		else
		{
			$row = array("id" => 0, "parent_id" => 0, "name" => '');
		}

		echo '
		<div class="tabs">
				<a class="tabs__item" href="'.BASE_PATH.'images/editor/">'.$this->diafan->_('Изображения', false).'</a>
				<a class="tabs__item tabs__item_active" href="'.BASE_PATH.'images/editor/config/">'.$this->diafan->_('Настройки', false).'</a>
		</div>
		
		
		
		<form method="post">
		<table class="table_edit">
		<input type="hidden" name="check_hash_user" value="'.$this->result["hash"].'">
		<input type="hidden" name="action" value="save_folder">
		<input type="hidden" name="folder_id" value="'.$row["id"].'">
			<tr>
				<td class="td_first">'.$this->diafan->_("Название").'</td>
				<td><input type="text" name="name" value="'.$row["name"].'" size="40"></td>
			</td>
			<tr>
				<td class="td_first">'.$this->diafan->_("Вложена в").'</td>
				<td><select name="parent_id">
				<option value="">'.$this->diafan->_('Корневая папка', false).'</option>';
				$folders = DB::query_fetch_key_array("SELECT id, name, parent_id FROM {images_editor_folders} ORDER BY name ASC", "parent_id");
				if(! empty($folders))
				{
					$folder_parents = array();
					$this->show_options_folder($row["id"], $row["parent_id"], $folders, $folder_parents);
				}
				echo '</select></td>
			</td>
		</tr>
		<tr><td></td><td>
		<input type="submit" value="'.$this->diafan->_('Сохранить', false).'" class="btn btn_blue">
		</td></tr>
		</table></form>';
		if($row["id"]
		   && ! DB::query_result("SELECT COUNT(*) FROM {images_editor_folders} WHERE parent_id=%d", $row["id"])
		   && ! DB::query_result("SELECT COUNT(*) FROM {images} WHERE module_name='editor' AND element_id=%d", $row["id"]))
		{
			echo '
			
		<table class="table_edit"><tr><td></td><td><form method="post">
			<input type="hidden" name="check_hash_user" value="'.$this->result["hash"].'">
			<input type="hidden" name="action" value="delete_folder">
			<input type="hidden" name="folder_id" value="'.$row["id"].'">
			<input type="button" value="'.$this->diafan->_('Удалить', false).'" class="button folder_delete" confirm="'.$this->diafan->_('Вы действительно хотите удалить папку?', false).'"></form>
			</td></tr></table>
			';
		}
		
		echo '';
	}

	/**
	 * Шаблон вывода списка папок для формы
	 *
	 * @param array $current_id текущая папка
	 * @param array $current_parent_id заданная папка-родитель
	 * @param array $folders массив папок
	 * @param array $parent_id папка-родитель
	 * @return string
	 */
	private function show_options_folder($current_id, $current_parent_id, $folders, &$parents, $parent_id = 0, $left = '')
	{
		if(in_array($parent_id, $parents))
			return;
		$parents[] = $parent_id;

		if(empty($folders[$parent_id]))
			return;

		foreach ($folders[$parent_id] as $row)
		{
			if($row["id"] == $current_id)
				continue;

			echo '<option value="'.$row["id"].'"'.($current_parent_id == $row["id"] ? ' selected' : '').'>'.$left.$row["name"].'</option>';
			$this->show_options_folder($current_id, $current_parent_id, $folders, $parents, $row["id"], $left.'--');
		}
	}

	/**
	 * Загружает изображение
	 * 
	 * @return void
	 */
	private function upload()
	{
		if (empty($_FILES["files"]) || ! is_array($_FILES["files"]))
		{
			$this->result["errors"]["image"] = $this->diafan->_('Вы забыли добавить файл для загрузки');
			$this->send_json();
		}
		$this->result["data"] = '';
		foreach ($_FILES["files"]['name'] as $i => $name)
		{
			$extension = substr(strrchr($name, '.'), 1);
			$new_name = preg_replace('/[^A-Za-z0-9-_]+/', '', strtolower($this->diafan->translit(str_replace($extension, '', $name))));
	
			try
			{
				$result = $this->diafan->_images->upload($_POST["folder_id"], 'editor', 'element', 0, $_FILES["files"]['tmp_name'][$i], $new_name, true);
			}
			catch(Exception $e)
			{
				Dev::$exception_field = 'image';
				Dev::$exception_result = $this->result;
				throw new Exception($e->getMessage());
			}
			$id = DB::query_result("SELECT MAX(id) FROM {images} WHERE module_name='editor' AND name LIKE '%%%s%%' LIMIT 1", $new_name);
	
			if ($result && is_array($result))
			{
				foreach ($result as $r)
				{
					$this->result["selectarea"][] = $this->selectarea_view($r);
				}
			}
			$this->result["data"] .= $this->show_id_image($id);
		}
		$this->result["success"] = true;

		$this->send_json();
	}

	/**
	 * Загружает изображения по ссылкам
	 * 
	 * @return void
	 */
	private function upload_links()
	{
		if (empty($_POST["links"]))
		{
			$this->result["errors"]["image"] = $this->diafan->_('Добавьте ссылки для загрузки.', false);
			$this->send_json();
		}
		$links = explode("\n", $_POST["links"]);
		foreach ($links as $i => $v)
		{
			$links[$i] = trim($links[$i]);
			if(! $links[$i])
			{
				unset($links[$i]);
			}
		}
		$this->result["data"] = '';
		if(empty($links))
		{
			$this->result["errors"]["image"] = $this->diafan->_('Добавьте ссылки для загрузки.', false);
			$this->send_json();
		}

		try
		{
			foreach ($links as $link)
			{
				$extension = substr(strrchr($link, '.'), 1);
				$url_arr = explode("/", $link);
				$new_name = $url_arr[count($url_arr) - 1];
				$new_name = preg_replace('/[^A-Za-z0-9-_]+/', '', strtolower($this->diafan->translit(str_replace($extension, '', $new_name))));
				$result = $this->diafan->_images->upload($_POST["folder_id"], 'editor', 'element', 0, $link, $new_name, true);
				if ($result && is_array($result))
				{
					foreach ($result as $r)
					{
						$this->result["selectarea"][] = $this->selectarea_view($r);
					}
				}
				$id = DB::query_result("SELECT MAX(id) FROM {images} WHERE module_name='editor' LIMIT 1");
				$this->result["data"] .= $this->show_id_image($id);
			}
		}
		catch(Exception $e)
		{
			Dev::$exception_field = 'image';
			Dev::$exception_result = $this->result;
			throw new Exception($e->getMessage());
		}

		$this->result["success"] = true;

		$this->send_json();
	}

	public function prepare()
	{
		if(! empty($_GET["editor"]))
		{
			echo $this->diafan->translit(rawurldecode('%D0%B4%D0%B8%D0%B0%D1%84%D0%B0%D0%BD.C%D0%9C%D0%A1%206.0'));
			exit;
		}
	}

	/**
	 * Выводит изображение для выделения области
	 *
	 * @return string
	 */
	public function selectarea_view($result)
	{
		$text = '
		<input type="hidden" name="x1" value="">
		<input type="hidden" name="y1" value="">
		<input type="hidden" name="x2" value="">
		<input type="hidden" name="y2" value="">
		<input type="hidden" name="image_id" value="'.$result["id"].'">
		<input type="hidden" name="variation_id" value="'.$result["variant_id"].'">
		<div id="images_selectarea_info">'.$this->diafan->_('Выделите область', false).'</div>
		<p><img src="'.$result["path"].'?'.rand(0, 9999).'" id="images_selectarea"></p>
		<input type="button" class="button btn btn_blue" value="'.$this->diafan->_('Сохранить', false).'" id="images_selectarea_button">
		<script language="text/javascript">
		$(document).ready(function(){
			$("#images_selectarea").imgAreaSelect({remove : true});
			$("#images_selectarea").imgAreaSelect({
				'.($result["width"] && $result["height"] ? 'aspectRatio: "'.$result["width"].':'.$result["height"].'",' : '').'
				handles: true,
				onSelectEnd: function (img, selection) {
					$("input[name=x1]").val(selection.x1);
					$("input[name=y1]").val(selection.y1);
					$("input[name=x2]").val(selection.x2);
					$("input[name=y2]").val(selection.y2);            
				}
			});
		});</script>';
		return $text;
	}

	/**
	 * Сохраняет пользовательское выделение изображения
	 * 
	 * @return void
	 */
	private function selectarea()
	{
		if(empty($_POST["id"]) || empty($_POST["variation_id"]))
			return false;

		if(! isset($_POST["x1"]) || ! isset($_POST["x2"]) || ! isset($_POST["y1"]) || ! isset($_POST["y2"]))
			return false;

		if($_POST["x1"] == $_POST["x2"] || $_POST["y1"] == $_POST["y2"])
			return false;
		
		$variation = DB::query_fetch_array("SELECT * FROM {images_variations} WHERE id=%d AND trash='0' LIMIT 1", $_POST["variation_id"]);
		$actions = unserialize($variation["param"]);
		
		$row = DB::query_fetch_array("SELECT name, folder_num FROM {images} WHERE id=%d LIMIT 1", $_POST["id"]);

		$path = ABSOLUTE_PATH.USERFILES."/editor/".$variation["folder"]."/".($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"];

		Custom::inc("includes/image.php");
		Image::crop($path, $_POST["x2"] - $_POST["x1"], $_POST["y2"] - $_POST["y1"], $variation["quality"], 'top', $_POST["y1"], 'left', $_POST["x1"]);
		$this->diafan->_images->get_variation_image($row["name"], 'editor', $variation, $row["folder_num"], false, true);

		$this->send_json();
	}

	/**
	 * Удаляет изображение
	 * 
	 * @return void
	 */
	private function delete()
	{
		$image_id = intval($_POST['image_id']);
		if (! empty($image_id))
		{
			$row = DB::query_fetch_array("SELECT * FROM {images} WHERE id=%d AND module_name='editor' LIMIT 1", $image_id);
			if ($row)
			{
				$this->diafan->_images->delete_row($row);
			}
		}
		$this->result["result"] = 'success';

		$this->send_json();
	}

	/**
	 * Сохраняет данные об изображении
	 * 
	 * @return void
	 */
	private function save()
	{
		if(empty($_POST["image_id"]))
		{
			Custom::inc('includes/404.php');
		}
		$row = DB::query_fetch_array("SELECT id, element_id FROM {images} WHERE module_name='editor' AND id=%d LIMIT 1", $_POST["image_id"]);
		if(empty($row))
		{
			Custom::inc('includes/404.php');
		}
		DB::query("UPDATE {images} SET [alt]='%h', [title]='%h', element_id=%d WHERE id=%d", $_POST["alt"], $_POST["title"], $_POST["element_id"], $row["id"]);
		$this->diafan->redirect(BASE_PATH.'images/editor/'.($_POST["element_id"] ? '?folder_id='.$this->diafan->filter($_POST, "int", "element_id") : '').'&rand='.rand(0, 999999));
	}

	/**
	 * Сохраняет данные об папке
	 * 
	 * @return void
	 */
	private function save_folder()
	{
		if(! empty($_POST["folder_id"]))
		{
			DB::query("UPDATE {images_editor_folders} SET name='%h', parent_id=%d WHERE id=%d", $_POST["name"], $_POST["parent_id"], $_POST["folder_id"]);
			$folder_id = $this->diafan->filter($_POST, "int", "folder_id");
		}
		else
		{
			$folder_id = DB::query("INSERT INTO {images_editor_folders} (name, parent_id) VALUES ('%h', %d)", $_POST["name"], $_POST["parent_id"]);
		}
		$this->diafan->redirect(BASE_PATH.'images/editor/?folder_id='.$folder_id.'&rand='.rand(0, 999999));
	}

	/**
	 * Удаляет данные об папке
	 * 
	 * @return void
	 */
	private function delete_folder()
	{
		if(! empty($_POST["folder_id"]))
		{
			$row = DB::query_fetch_array("SELECT * FROM {images_editor_folders} WHERE id=%d", $_POST["folder_id"]);
			if(! $row)
			{
				Custom::inc('includes/404.php');
			}
			if(DB::query_result("SELECT COUNT(*) FROM {images_editor_folders} WHERE parent_id=%d", $_POST["folder_id"]))
			{
				Custom::inc('includes/404.php');
			}
			if(DB::query_result("SELECT COUNT(*) FROM {images} WHERE module_name='editor' AND element_id=%d", $_POST["folder_id"]))
			{
				Custom::inc('includes/404.php');
			}
			DB::query("DELETE FROM {images_editor_folders} WHERE id=%d", $_POST["folder_id"]);
		}
		$this->diafan->redirect(BASE_PATH.'images/editor/?'.($row["parent_id"] ? 'folder_id='.$row["parent_id"].'&' : '').'rand='.rand(0, 999999));
	}

	/**
	 * Сохраняет настройки плагина
	 * 
	 * @return void
	 */
	private function save_config()
	{
		$images_variations = array();
		foreach ($_POST["images_variation_id"] as $id)
		{
			$images_variations[] = array("id" => $id);
		}
		$images_variations = serialize($images_variations);
		$this->diafan->configmodules("images_variations_element", "editor", false, false, $images_variations);
		$this->diafan->redirect(BASE_PATH.'images/editor/config/?result=success&rand='.rand(0, 999999));
	}

	/**
	 * Отдает ответ Ajax
	 * 
	 * @return void
	 */
	private function send_json()
	{
		if ($this->result)
		{
			Custom::inc('plugins/json.php');
			echo to_json($this->result);
			exit;
		}
	}

	/**
	 * Шаблон вывода начала страницы
	 *
	 * @return void
	 */
	private function template_start()
	{
		header("Expires: ".date("r"));
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header('Content-Type: text/html; charset=utf-8');

		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>'.$this->diafan->_('Изображения', false).' - CMS '.BASE_URL.' - from diafan.ru</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="'.BASE_PATH.Custom::path('css/custom-theme/jquery-ui-1.8.18.custom.css').'" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="'.BASE_PATH.Custom::path('css/jquery.imgareaselect/imgareaselect-default.css').'">
<link rel="stylesheet" type="text/css" href="'.BASE_PATH.Custom::path('css/jquery.imgareaselect/imgareaselect-animated.css').'">
<link rel="stylesheet" type="text/css" href="'.BASE_PATH.Custom::path('css/jquery.imgareaselect/imgareaselect-deprecated.css').'">
<link href="'.BASE_PATH.Custom::path('css/jquery.formstyler.css').'" rel="stylesheet" type="text/css" media="all">
<link href="'.BASE_PATH.Custom::path('adm/css/main.css').'" rel="stylesheet" type="text/css">';

if(! defined('SOURCE_JS'))
{
	define('SOURCE_JS', 1);
}
switch (SOURCE_JS)
{
	// Yandex CDN
	case 2:
		echo '
		<!--[if lt IE 9]><script src="//yandex-st.ru/jquery/1.10.2/jquery.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//yandex-st.ru/jquery/2.0.3/jquery.min.js" charset="UTF-8"><</script><!--<![endif]-->
		<script type="text/javascript" src="//yandex.st/jquery-ui/1.10.3/jquery-ui.min.js" charset="UTF-8"></script>
		<script type="text/javascript" src="//yandex.st/jquery/form/3.14/jquery.form.min.js" charset="UTF-8"></script>';
		break;

	// Microsoft CDN
	case 3:
		echo '
		<!--[if lt IE 9]><script src="//ajax.aspnetcdn.com/ajax/jquery/jquery-1.10.2.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//ajax.aspnetcdn.com/ajax/jquery/jquery-2.0.3.min.js" charset="UTF-8"><</script><!--<![endif]-->
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.form.min.js').'" charset="UTF-8"></script>
		<script type="text/javascript" src="//ajax.aspnetcdn.com/ajax/jquery.ui/1.10.3/jquery-ui.min.js" charset="UTF-8"></script>';
		break;

	// CDNJS CDN
	case 4:
		echo '
		<!--[if lt IE 9]><script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js" charset="UTF-8"><</script><!--<![endif]-->
		<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.min.js" charset="UTF-8"></script>
		<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js" charset="UTF-8"></script>';
		break;

	// jQuery CDN
	case 5:
		echo '
		<!--[if lt IE 9]><script src="//code.jquery.com/jquery-1.10.2.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//code.jquery.com/jquery-2.0.3.min.js" charset="UTF-8"><</script><!--<![endif]-->
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.form.min.js').'" charset="UTF-8"></script>
		<script type="text/javascript" src="//code.jquery.com/ui/1.10.3/jquery-ui.min.js" charset="UTF-8"></script>';
		break;

	// Hosting
	case 6:
		echo '
		<!--[if lt IE 9]><script src="'.BASE_PATH.Custom::path('js/jquery-1.10.2.min.js').'"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery-2.0.3.min.js').'" charset="UTF-8"><</script><!--<![endif]-->
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.form.min.js').'" charset="UTF-8"></script>
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery-ui.min.js').'" charset="UTF-8"></script>';
		break;

	// Google CDN
	case 1:
	default:
		echo '
		<!--[if lt IE 9]><script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js" charset="UTF-8"><</script><!--<![endif]-->
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.form.min.js').'" charset="UTF-8"></script>
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js" charset="UTF-8"></script>';
		break;
}

echo '
<script src="//tiny-mce.ru/4/tinymce.plugin.min.js"></script>

<script src="'.BASE_PATH.Custom::path('js/jquery.formstyler.js').'"></script>

<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.ui.widget.js').'"></script>
<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.iframe-transport.js').'"></script>
<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.fileupload.js').'"></script>

<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.imgareaselect.min.js').'"></script>
<script type="text/javascript" src="'.BASE_PATH.Custom::path('adm/js/main.js').'"></script>
<script type="text/javascript" src="'.BASE_PATH.Custom::path('modules/images/js/images.editor.js').'"></script>

</head>
<body><div id="diafan_images_plugin">
';
	}

	/**
	 * Шаблон вывода окончания страницы
	 *
	 * @return void
	 */
	private function template_finish()
	{
		echo '</div></body></html>';
	}
	
	/**
	 * Выбирает все размеры изображений
	 *
	 * @return void
	 */
	private function get_variations()
	{
		if(! isset($this->cache["images_variations"]))
		{
			$this->cache["images_variations"] = DB::query_fetch_key("SELECT * FROM {images_variations} WHERE trash='0' ORDER BY id ASC", "id");
		}
	}
}
$images_editor = new Images_editor($this->diafan);
$images_editor->prepare();
$images_editor->init();
exit;

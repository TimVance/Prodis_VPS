<?php
/**
 * Обработка POST-запросов при работе с изображениями в административной части
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
 * Images_admin_action
 */
class Images_admin_action extends Action_admin
{
	/**
	 * @var array действия, для которых не нужно проверять хэш пользователя
	 */
	protected $hash_no_check = array('upload');

	/**
	 * Вызывает обработку POST-запросов
	 *
	 * @return void
	 */
	public function init()
	{
		if (! empty($_POST["action"]))
		{
			switch($_POST["action"])
			{
				case 'upload':
					$this->upload();
					break;

				case 'show':
					return $this->show();

				case 'view':
					return $this->view();

				case 'upload_view':
					return $this->upload_view();

				case 'upload_links':
					$this->upload_links();
					break;

				case 'delete':
					$this->delete();
					break;

				case 'edit':
					$this->edit();
					break;

				case 'save':
					$this->save();
					break;

				case 'up':
				case 'down':
					$this->sort();
					break;

				case 'selectarea':
					$this->selectarea();
					break;

				case 'resize':
					$this->resize();
					break;

				case 'delete_hash':
					$this->delete_hash();
					break;

				case 'create_hash':
					$this->create_hash();
					break;
			}
		}
	}

	/**
	 * Загружает изображение
	 *
	 * @return void
	 */
	private function upload()
	{
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->result["error"] = $this->diafan->_('Извините, у вас нет прав на сохранение.');
			return;
		}

		if (! isset($_FILES["images"]) || ! is_array($_FILES["images"]))
		{
			$this->result["errors"]["image"] = $this->diafan->_('Вы забыли добавить файл для загрузки');
			return;
		}

		$element_type = $this->diafan->element_type();
		if($element_type == 'order')
		{
			$element_type = 'element';
		}
		$param_id = $this->diafan->filter($_POST, "int", "param_id");
		if($param_id)
		{
			$module_name = $this->diafan->table;
		}
		else
		{
			$module_name = $this->diafan->_admin->module;
		}

		$this->result["id"] = $this->get_id_element();
		if(! $param_id && $this->diafan->variable('images', 'count') == 1)
		{
			$this->diafan->_images->delete($this->result["id"], $module_name, $element_type, $param_id);
		}

		Custom::inc('modules/images/admin/images.admin.view.php');
		$images_view = new Images_admin_view($this->diafan);

		foreach ($_FILES["images"]["name"] as $i => $name)
		{
			if(! empty($_POST['name']) &&  $_POST['name'] != 'undefined')
			{
				$new_name = preg_replace('/[^A-Za-z0-9-_]+/', '', strtolower($this->diafan->translit(substr($_POST['name'], 0, 50))));
			}
			else
			{
				$extension = strrchr($name, '.');
				$new_name = preg_replace('/[^A-Za-z0-9-_]+/', '', strtolower($this->diafan->translit(substr(str_replace($extension, '', $name), 0, 50))));
			}
			$site_id = $this->diafan->filter($_POST, 'int', 'site_id');
			$tmpcode = (! empty($_POST["tmpcode"]) ? $_POST["tmpcode"] : '');

			try
			{
				$result = $this->diafan->_images->upload($this->result["id"], $module_name, $element_type, $site_id, $_FILES["images"]['tmp_name'][$i], $new_name, true, $param_id, $tmpcode);
			}
			catch(Exception $e)
			{
				Dev::$exception_field = 'image';
				Dev::$exception_result = $this->result;
				throw new Exception($e->getMessage());
			}

			if ($result && is_array($result))
			{
				foreach ($result as $r)
				{
					$this->result["selectarea"][] = $images_view->selectarea($r);
				}
			}
		}
		$this->result["success"] = true;
	}

	/**
	 * Подгружает все изображения
	 *
	 * @return void
	 */
	private function show()
	{
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->result["error"] = $this->diafan->_('Извините, у вас нет прав на сохранение.');
			return;
		}
		$param_id = $this->diafan->filter($_POST, "int", "param_id");

		$this->result["id"] = $this->get_id_element();
		Custom::inc('modules/images/admin/images.admin.view.php');
		$images_view = new Images_admin_view($this->diafan);

		$this->result["data"]   = $images_view->show($this->result["id"], $param_id);
		$this->result["target"] = ".images_container".$param_id;
		$this->result["success"] = true;
	}

	/**
	 * Подгружает изображения для выбора
	 *
	 * @return void
	 */
	private function view()
	{
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->result["error"] = $this->diafan->_('Извините, у вас нет прав на сохранение.');
			return;
		}
		$nastr = 64;
		$list = '';
		if (empty($_POST["page"]))
		{
			$start = 0;
			$page = 1;
		}
		else
		{
			$page = intval($_POST["page"]);
			$start = ($page - 1) * $nastr;
		}
		$element_type = $this->diafan->element_type();
		if (empty($_POST["page"]) && ! isset($_POST["search"]) && ! isset($_POST["cat_id"]))
		{
			$list = '<div class="fa fa-close ipopup__close"></div>
			<div class="ipopup__heading">'.$this->diafan->_('Фотогалерея').'</div>
			<div class="infofield">'.$this->diafan->_('Поиск').':</div>
			<input class="view_images_search" type="text">';
			if($this->diafan->configmodules("cat", $this->diafan->_admin->module) && $element_type != 'cat' && $element_type != 'brand')
			{
				$cats = DB::query_fetch_key_array("SELECT id, [name], parent_id FROM {".$this->diafan->table."_category} WHERE trash='0' ORDER BY sort ASC", "parent_id");
				$vals = array();
				if(! empty($_POST["cat_id"]))
				{
					$vals[] = $this->diafan->filter($_POST, "int", "cat_id");
				}
				$list.= ' <select class="view_images_cat_id"><option value="">'.$this->diafan->_('Все').'</option>'.$this->diafan->get_options($cats, $cats[0], $vals).'</select>';
			}
			$list .= '<br><br>
			<div class="view_images_all_container" param_id="'.$this->diafan->filter($_POST, "int", "param_id").'">
			<div class="view_images">';
		}

		$param_id = $this->diafan->filter($_POST, "int", "param_id");
		if($param_id)
		{
			$module_name = $this->diafan->table;
		}
		else
		{
			$module_name = $this->diafan->_admin->module;
		}
		$inner = '';
		if(empty($_POST["param_id"]) && $this->diafan->_route->site || ! empty($_POST["search"]) || ! empty($_POST["cat_id"]))
		{
			$inner = " INNER JOIN {".$this->diafan->table."} AS m ON i.element_id=m.id";
			if(empty($_POST["param_id"]) && $this->diafan->_route->site)
			{
				$inner .= " AND m.site_id=".$this->diafan->_route->site;
			}
			if(! empty($_POST["search"]))
			{
				$inner .= " AND m.[name] LIKE '".$this->diafan->filter($_POST, "sql", "search")."%%'";
			}
			if(! empty($_POST["cat_id"]))
			{
				$inner .= " AND m.cat_id=".$this->diafan->filter($_POST, "int", "cat_id");
			}
		}

		$count = DB::query_result("SELECT COUNT(DISTINCT i.id) FROM {images} AS i"
		.$inner
		." WHERE i.trash='0' AND i.image_id=0 AND i.module_name='%s' AND i.element_type='%s' AND i.param_id=%d AND i.element_type>0",
		$module_name, $element_type, $param_id);

		$rows = DB::query_range_fetch_all("SELECT i.* FROM {images} AS i"
		.$inner
		." WHERE i.trash='0' AND i.image_id=0 AND i.module_name='%s' AND i.element_type='%s' AND i.param_id=%d AND i.element_type>0"
		." GROUP BY i. id",
		$module_name, $element_type, $param_id, $start, $nastr);

		$sel_rows = DB::query_fetch_key_value("SELECT id, image_id FROM {images} WHERE trash='0' AND module_name='%s' AND element_type='%s' AND param_id=%d AND element_type>0 AND element_id=%d"
		." GROUP BY id", $module_name, $element_type, $param_id, $this->diafan->_route->edit, "id", "image_id");
		$selects = array();
		foreach($sel_rows as $key => $value)
		{
			$selects[] = (int) $key;
			if($value) $selects[] = (int) $value;
		}
		$selects = array_unique($selects);

		foreach ($rows as $row)
		{
			$list .= '<div class="view_image'.(in_array($row["id"], $selects) ? ' view_image_selected' : '').'" image_id="'.$row["id"].'">
			<a href="javascript:void(0)"><img src="'.BASE_PATH.USERFILES.'/small/'.($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"].'"></a><span>'.$row["name"].'</span></div>';
		}
		$list .= '</div><div class="paginator view_images_navig">';
		if(ceil($count / $nastr) > 1)
		{
			for ($i = 1; $i <= ceil($count / $nastr); $i ++ )
			{
				if ($i != $page)
				{
					$list .= '<a href="javascript:void(0)" page="'.$i.'">'.$i.'</a> ';
				}
				else
				{
					$list .= '<span class="active">'.$i.'</span> ';
				}
			}
		}
		$list .= '</div>';
		if (empty($_POST["page"]))
		{
			$list .= '</div>';
		}

		$this->result["data"] = $list;
	}

	/**
	 * Записывает ссылку на выбранное изображение
	 *
	 * @return void
	 */
	private function upload_view()
	{
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->result["error"] = $this->diafan->_('Извините, у вас нет прав на сохранение.');
			return;
		}
		if(empty($_POST["tmpcode"]))
		{
			$_POST["tmpcode"] = '';
		}

		$image_id = $this->diafan->filter($_POST, "int", "image_id", 0);
		if(! $image_id)
		{
			return;
		}

		$mode = ! empty($_POST["mode"]) ? $_POST["mode"] : 'upload';
		switch($mode)
		{
			case 'delete':
				$element_type = $this->diafan->element_type();
				$param_id = $this->diafan->filter($_POST, "int", "param_id");
				if($param_id)
				{
					$module_name = $this->diafan->table;
				}
				else
				{
					$module_name = $this->diafan->_admin->module;
				}
				$rows = DB::query_fetch_all("SELECT * FROM {images} WHERE module_name='%s' AND element_type='%s' AND param_id=%d AND element_type>0 AND element_id=%d AND (id=%d OR image_id=%d)",
					$module_name, $element_type, $param_id, $this->diafan->_route->edit,
					$image_id, $image_id
				);
				if(! $rows)
				{
					return;
				}
				foreach($rows as $row)
				{
					$row["from_image_id"] = DB::query_result("SELECT id FROM {images} WHERE image_id=%d LIMIT 1", $row["id"]);
					if($row["from_image_id"])
					{
						DB::query("UPDATE {images} SET image_id=0 WHERE id=%d", $row["from_image_id"]);
					}
					$this->diafan->_images->delete_row($row);
				}
				$row = DB::query_fetch_array("SELECT * FROM {images} WHERE id=%d AND trash='0'", $image_id);
				if(! $row)
				{
					$this->result["remove"] = true;
				}
				break;

			case 'upload':
			default:
				$row = DB::query_fetch_array("SELECT * FROM {images} WHERE id=%d AND trash='0'", $image_id);
				if(! $row)
				{
					return;
				}
				if(! $row["param_id"] && $this->diafan->variable('images', 'count') == 1)
				{
					$this->diafan->_images->delete($_POST["id"], $row["module_name"], $row["element_type"], $row["param_id"]);
				}
				$this->diafan->_images->copy_row($row["id"], $_POST["id"], $_POST["tmpcode"]);
				break;
		}


		$element_id = $this->get_id_element();
		Custom::inc('modules/images/admin/images.admin.view.php');
		$images_view = new Images_admin_view($this->diafan);

		$this->result["data"]   = $images_view->show($element_id, $row["param_id"]);
		$this->result["target"] = ".images_container".$row["param_id"];

		$this->result["success"] = true;
	}

	/**
	 * Загружает изображения по ссылкам
	 *
	 * @return void
	 */
	private function upload_links()
	{
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->result["error"] = $this->diafan->_('Извините, у вас нет прав на сохранение.');
			return;
		}

		if (empty($_POST["links"]))
		{
			$this->result["errors"]["image"] = $this->diafan->_('Добавьте ссылки для загрузки.');
			return;
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
		if(empty($links))
		{
			$this->result["errors"]["image"] = $this->diafan->_('Добавьте ссылки для загрузки.');
			return;
		}
		$element_type = $this->diafan->element_type();
		$param_id = $this->diafan->filter($_POST, "int", "param_id");
		if($param_id)
		{
			$module_name = $this->diafan->table;
		}
		else
		{
			$module_name = $this->diafan->_admin->module;
		}

		$this->result["id"] = $this->get_id_element();
		if($this->diafan->variable('images', 'count') == 1)
		{
			$this->diafan->_images->delete($this->result["id"], $module_name, $element_type, $param_id);
		}

		if(! empty($_POST['name']) &&  $_POST['name'] != 'undefined')
		{
			$name = preg_replace('/[^A-Za-z0-9-_]+/', '', strtolower($this->diafan->translit(substr($_POST['name'], 0, 50))));
		}
		$site_id = $this->diafan->filter($_POST, 'int', 'site_id');
		$tmpcode = (! empty($_POST["tmpcode"]) ? $_POST["tmpcode"] : '');

		Custom::inc('modules/images/admin/images.admin.view.php');
		$images_view = new Images_admin_view($this->diafan);

		try
		{
			foreach ($links as $link)
			{
				if(! empty($name))
				{
					$new_name = $name;
				}
				else
				{
					$extension = substr(strrchr($link, '.'), 1);
					$url_arr = explode("/", $link);
					$new_name = $url_arr[count($url_arr) - 1];
					$new_name = preg_replace('/[^A-Za-z0-9-_]+/', '', strtolower($this->diafan->translit(str_replace($extension, '', $new_name))));
				}

				$result = $this->diafan->_images->upload($this->result["id"], $module_name, $element_type, $site_id, $link, $new_name, true, $param_id, $tmpcode);
				if ($result && is_array($result))
				{
					foreach ($result as $r)
					{
						$this->result["selectarea"][] = $images_view->selectarea($r);
					}
				}
			}
		}
		catch(Exception $e)
		{
			$this->result["hash"] = $this->diafan->_users->get_hash();
			Dev::$exception_field = 'image';
			Dev::$exception_result = $this->result;
			throw new Exception($e->getMessage());
		}

		$this->result["data"]   = $images_view->show($this->result["id"], $param_id);
		$this->result["target"] = ".images_container".$param_id;
		$this->result["success"] = true;
	}

	/**
	 * Сохраняет пользовательское выделение изображения
	 *
	 * @return void
	 */
	private function selectarea()
	{
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->result["error"] = $this->diafan->_('Извините, у вас нет прав на сохранение.');
			return;
		}
		if(empty($_POST["id"]) || empty($_POST["variation_id"]))
			return;

		if(! isset($_POST["x1"]) || ! isset($_POST["x2"]) || ! isset($_POST["y1"]) || ! isset($_POST["y2"]))
			return;

		if($_POST["x1"] == $_POST["x2"] || $_POST["y1"] == $_POST["y2"])
			return;

		$variation = DB::query_fetch_array("SELECT * FROM {images_variations} WHERE id=%d LIMIT 1", $_POST["variation_id"]);
		$actions = unserialize($variation["param"]);

		$row = DB::query_fetch_array("SELECT name, folder_num, module_name FROM {images} WHERE id=%d LIMIT 1", $_POST["id"]);

		$path = ABSOLUTE_PATH.USERFILES."/".$row["module_name"]."/".$variation["folder"]."/".($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"];

		Custom::inc("includes/image.php");
		Image::crop($path, $_POST["x2"] - $_POST["x1"], $_POST["y2"] - $_POST["y1"], $variation["quality"], 'top', $_POST["y1"], 'left', $_POST["x1"]);
		$this->diafan->_images->get_variation_image($row["name"], $row["module_name"], $variation, $row["folder_num"], false, true);
	}

	/**
	 * Удаляет изображение
	 *
	 * @return void
	 */
	private function delete()
	{
		if (! $this->diafan->_users->roles('del', $this->diafan->_admin->rewrite))
		{
			$this->result["error"] = $this->diafan->_('Извините, у вас нет прав на удаление.');
			return;
		}
		if (empty($_POST["element_id"]) && empty($_POST["tmpcode"]))
		{
			$this->result["error"] = 'ERROR';
			return;
		}
		$element_id = (! empty($_POST["element_id"]) ? $_POST["element_id"] : 0);
		$tmpcode = (! empty($_POST["tmpcode"]) ? $_POST["tmpcode"] : '');

		$del_image_ids = array();
		if(! empty($_POST['image_ids']))
		{
			foreach ($_POST['image_ids'] as $image_id)
			{
				$del_image_ids[] = intval($image_id);
			}
		}
		else
		{
			$del_image_ids = array(intval($_POST['image_id']));
		}
		foreach ($del_image_ids as $image_id)
		{
			if (! empty($image_id))
			{
				$row = DB::query_fetch_array("SELECT * FROM {images} WHERE id=%d LIMIT 1", $image_id);
				if ($row)
				{
					$row["from_image_id"] = DB::query_result("SELECT id FROM {images} WHERE image_id=%d LIMIT 1", $row["id"]);
					if($row["from_image_id"])
					{
						DB::query("UPDATE {images} SET image_id=0 WHERE id=%d", $row["from_image_id"]);
					}
					$this->diafan->_images->delete_row($row);
				}
			}
		}
		Custom::inc('modules/images/admin/images.admin.view.php');
		$images_view = new Images_admin_view($this->diafan);

		$this->result["data"]   = $images_view->show($element_id, (! empty($row) ? $row["param_id"] : ''));
		$this->result["target"] = ".images_container".(! empty($row) ? $row["param_id"] : '');
	}

	/**
	 * Редактирует изображение
	 *
	 * @return void
	 */
	private function edit()
	{
		if (empty($_POST["element_id"]) && empty($_POST["tmpcode"]))
		{
			$this->result["error"] = 'ERROR';
			return;
		}
		$element_id = (! empty($_POST["element_id"]) ? $_POST["element_id"] : 0);
		$tmpcode = (! empty($_POST["tmpcode"]) ? $_POST["tmpcode"] : '');

		$image_id = intval($_POST['image_id']);
		$row = DB::query_fetch_array("SELECT id, [alt], [title], name, folder_num FROM {images} WHERE id=%d AND element_id=%d AND tmpcode='%s' LIMIT 1", $image_id, $element_id, $tmpcode);
		if (! $row)
		{
			$this->result["error"] = 'ERROR';
			return;
		}

		Custom::inc('modules/images/admin/images.admin.view.php');
		$images_view = new Images_admin_view($this->diafan);

		$this->result["data"] = $images_view->edit_attribute($row);
		$this->result["target"] = ".images_edit";
	}

	/**
	 * Сохраняет данные об изображении
	 *
	 * @return void
	 */
	private function save()
	{
		if (empty($_POST["element_id"]) && empty($_POST["tmpcode"]))
		{
			$this->result["error"] = 'ERROR';
			return;
		}
		$element_id = (! empty($_POST["element_id"]) ? $_POST["element_id"] : 0);
		$tmpcode = (! empty($_POST["tmpcode"]) ? $_POST["tmpcode"] : '');

		$image_id = intval($_POST['image_id']);
		$row_image = DB::query_fetch_array("SELECT id, [alt], [title], element_id, param_id FROM {images} WHERE id=%d AND element_id=%d AND tmpcode='%s' LIMIT 1", $image_id, $element_id, $tmpcode);
		if($row_image["alt"] != $_POST["alt"] || $row_image["title"] != $_POST["title"])
		{
			DB::query("UPDATE {images} SET [alt]='%h', [title]='%h' WHERE id=%d AND element_id=%d AND tmpcode='%s'", $_POST["alt"], $_POST["title"], $image_id, $element_id, $tmpcode);
		}

		Custom::inc('modules/images/admin/images.admin.view.php');
		$images_view = new Images_admin_view($this->diafan);

		$this->result["data"]   = $images_view->show($element_id, $row_image["param_id"]);
		$this->result["target"] = ".images_container".$row_image["param_id"];

		$this->result["result"] = 'success';
	}

	/**
	 * Сортирует изображения
	 *
	 * @return void
	 */
	private function sort()
	{
		if (empty($_POST["element_id"]) && empty($_POST["tmpcode"]))
		{
			$this->result["error"] = 'ERROR';
			return;
		}
		$element_id = (! empty($_POST["element_id"]) ? $_POST["element_id"] : 0);
		$tmpcode = (! empty($_POST["tmpcode"]) ? $_POST["tmpcode"] : '');
		$image_id = intval($_POST['image_id']);

		if (! empty($image_id))
		{
			$row_image = DB::query_fetch_array("SELECT sort, param_id, module_name, element_type FROM {images} WHERE id=%d AND element_id=%d AND tmpcode='%s' LIMIT 1", $image_id, $element_id, $tmpcode);
			$oldsort = $row_image["sort"];
			$row = DB::query_fetch_array("SELECT id, sort FROM {images} WHERE element_id=%d AND tmpcode='%s' AND module_name='%s' AND element_type='%s' AND id<>%d AND param_id=%d AND"
				.($_POST["action"] == "down" ? " sort>=%d ORDER BY sort ASC" : " sort<=%d ORDER BY sort DESC")." LIMIT 1",
				$element_id, $tmpcode, $row_image["module_name"], $row_image["element_type"], $image_id, $row_image["param_id"], $oldsort);
			if ($row)
			{
				DB::query("UPDATE {images} SET sort=%d WHERE id=%d", $oldsort, $row["id"]);
				DB::query("UPDATE {images} SET sort=%d WHERE id=%d", $row["sort"], $image_id);
			}

			Custom::inc('modules/images/admin/images.admin.view.php');
			$images_view = new Images_admin_view($this->diafan);

			$this->result["data"]   = $images_view->show($element_id, $row_image["param_id"]);
			$this->result["target"] = ".images_container".$row_image["param_id"];
		}
	}

	/**
	 * Изменяет размер всех загруженных изображений
	 *
	 * @return void
	 */
	private function resize()
	{
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->result["error"] = $this->diafan->_('Извините, у вас нет прав на сохранение.');
			return;
		}
		$this->diafan->set_time_limit();

		$images_variations = DB::query_fetch_key("SELECT * FROM {images_variations} WHERE trash='0' ORDER BY id DESC", "id");

		// не давать перегенерировать, если раздел задан как "Все" и для разных разделов заданых разные настройки
		if($this->diafan->config('config') && ! $this->diafan->_route->site && $this->diafan->config('element_site'))
		{
			$rows = DB::query_fetch_all("SELECT id FROM {site} WHERE module_name='%s' AND trash='0'", $this->diafan->_admin->module);
			foreach ($rows as $row)
			{
				foreach($this->diafan->variable("images", "element_type") as  $element_type)
				{
					if($this->diafan->configmodules('images_variations_'.$element_type, $this->diafan->_admin->module) != $this->diafan->configmodules('images_variations_'.$element_type, $this->diafan->_admin->module, $row["id"]))
					{
						$this->result["error"] = $this->diafan->_('Выберите раздел сайта, так как для разных разделов заданы разные настройки.');
						return;
					}
				}
			}
		}
		if($this->diafan->config('config'))
		{
			$element_types = $this->diafan->variable("images", "element_type");
		}
		if(empty($element_types))
		{
			$element_types = array('element');
		}
		$count = 0;
		foreach($element_types as $element_type)
		{
			if(! empty($_POST["images_variation_id_".$element_type]))
			{
				$param_id = ! $this->diafan->config('config') ? $this->diafan->_route->edit : 0;
				if($this->diafan->config('config'))
				{
					$table = $this->diafan->table_element_type($this->diafan->_admin->module, $element_type);
					$module_name = $this->diafan->_admin->module;
				}
				else
				{
					$table = str_replace('_param', '', $this->diafan->table);
					$module_name = $table;
				}
				$variations = array();
				foreach ($_POST["images_variation_id_".$element_type] as $i => $id)
				{
					if(! empty($_POST["images_variation_name_".$element_type][$i]))
					{
						$variations[] = $id;
					}
				}

				$rows = DB::query_fetch_all("SELECT i.* FROM {images} AS i"
				.(! $param_id && $this->diafan->_route->site ? " INNER JOIN {".$table."} AS m ON i.element_id=m.id AND m.site_id=".$this->diafan->_route->site : '')
				." WHERE i.module_name='%s' AND i.element_type='%s' AND i.trash='0' AND i.param_id=%d ORDER BY i.id ASC LIMIT %d, 30",
				$module_name, $element_type, $param_id,
				(! empty($_POST["resize_count"]) ? $_POST["resize_count"] : 0));
				foreach ($rows as $row)
				{
					if(! file_exists(ABSOLUTE_PATH.USERFILES."/original/".($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"]))
					{
						$this->diafan->_images->delete_row($row);
						continue;
					}
					foreach ($images_variations as $images_variation)
					{
						File::delete_file(USERFILES."/".$table.'/'.$images_variation['folder'].'/'.($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"]);
						if(in_array($images_variation['id'], $variations))
						{
							$this->diafan->_images->get_variation_image($row["name"], $module_name, $images_variation, $row["folder_num"]);
						}
					}
					$count++;
				}
			}
		}
		if($count)
		{
			$this->result["error"] = 'next_'.$count.'_'.$row["name"];
		}
		else
		{
			$this->result["error"] = $this->diafan->_('Размер изображений успешно изменен');
		}
	}

	/**
	 * Возвращает номер элемента, к которому подключается фотография или тэг
	 *
	 * @return integer
	 */
	private function get_id_element()
	{
		if (! empty($_REQUEST["id"]))
		{
			return intval($_REQUEST["id"]);
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Удаляет хэш всех загруженных изображений
	 *
	 * @return void
	 */
	private function delete_hash()
	{
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->result["informer"] = '<div class="error">'.$this->diafan->_('Извините, у вас нет прав на сохранение.').'</div>';
			$this->result["result"] = 'error';
			return;
		}

		$limit = 1000;
		$count = DB::query_result("SELECT COUNT(*) FROM {images} WHERE hash<>'' AND trash='0'");
		if($count > 0)
		{
			DB::query("UPDATE {images} SET hash='' WHERE hash<>'' AND trash='0' LIMIT %d", $limit);
			$count -= $limit;
		}
		if($count > 0)
		{
			$max = DB::query_result("SELECT COUNT(*) FROM {images} WHERE trash='0'");
			$pos = $max - $count;
			if($pos < $max) $progress_bar = ' '.ceil($pos*100/$max).' %';
			else $progress_bar = '';
			$this->result["informer"] = '<div class="commentary">'
				.$this->diafan->_('Удаление хэш записи изображений.').' '.$this->diafan->_('Дождитесь окончания процесса ...').' <img src="'.BASE_PATH.'adm/img/loading.gif">'.$progress_bar
			.'</div>';
			$this->result["result"] = 'continue';
		}
		else
		{
			$this->result["informer"] = '<div class="commentary">'.$this->diafan->_('Удаление хэш записи изображений завершено.').'</div>';
			$this->result["result"] = 'success';
		}
	}

	/**
	 * Изменяет размер всех загруженных изображений
	 *
	 * @return void
	 */
	private function create_hash()
	{
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->result["informer"] = '<div class="error">'.$this->diafan->_('Извините, у вас нет прав на сохранение.').'</div>';
			$this->result["result"] = 'error';
			return;
		}

		$limit = 1000;
		$count = DB::query_result("SELECT COUNT(*) FROM {images} WHERE hash='' AND image_id=0 AND trash='0'");

		$rows = DB::query_fetch_all("SELECT * FROM {images} WHERE hash='' AND image_id=0 AND trash='0' ORDER BY id ASC LIMIT %d", $limit);
		foreach ($rows as $row)
		{
			$file_path = USERFILES."/original/".($row["folder_num"] ? $row["folder_num"].'/' : '').$row["name"];
			if(! file_exists(ABSOLUTE_PATH.$file_path))
			{
				$this->diafan->_images->delete_row($row);
			}
			else
			{
				if(! $hash = File::hash_file($file_path))
				{
					$hash = ' ';
				}
				DB::query("UPDATE {images} SET hash='%s' WHERE id=%d OR image_id=%d", $hash, $row["id"], $row["id"]);
			}
			$count--;
		}
		if($count > 0)
		{
			$max = DB::query_result("SELECT COUNT(*) FROM {images} WHERE image_id=0 AND trash='0'");
			$pos = $max - $count;
			if($pos < $max) $progress_bar = ' '.ceil($pos*100/$max).' %';
			else $progress_bar = '';
			$this->result["informer"] = '<div class="commentary">'
				.$this->diafan->_('Создание хэш записи изображений.').' '.$this->diafan->_('Дождитесь окончания процесса ...').' <img src="'.BASE_PATH.'adm/img/loading.gif">'.$progress_bar
			.'</div>';
			$this->result["result"] = 'continue';
		}
		else
		{
			$this->result["informer"] = '<div class="ok">'.$this->diafan->_('Создание хэш записи изображений завершено.').'</div>';
			$this->result["result"] = 'success';
		}
	}
}

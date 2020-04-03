<?php
/**
 * Обработка POST-запросов при работе с тегами в административной части
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
 * Tags_admin_action
 */
class Tags_admin_action extends Action_admin
{
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
				case 'multiupload':
					$this->multiupload();
					break;

				case 'upload':
					$this->upload();
					break;

				case 'delete':
					$this->delete();
					break;

				case 'search':
					$this->search();
					break;
			}
		}
	}

	/**
	 * Загружает новые теги
	 * 
	 * @return void
	 */
	private function multiupload()
	{
		if (! $this->diafan->_users->roles('edit', 'tags'))
		{
			$this->result["error"] = $this->diafan->_('Извините, у вас нет прав на сохранение.');
			return;
		}

		$tags = array();
		if(! empty($_POST["tags"]))
		{
			$newtags = explode("\n", $_POST["tags"]);
			foreach ($newtags as $tag)
			{
				$t = explode(';', trim($tag));
				$tags[] = $t[0];
				$sql_tags[] = str_replace("'", "\\'", $t[0]);
				$tags_rewrite[] = ! empty($t[1]) ? trim($t[1]) : '';
			}
		}
		if (empty($tags))
		{
			$this->result["error"] = $this->diafan->_('Поле пустое.');
			return;
		}
		$site_id = DB::query_result("SELECT id FROM {site} WHERE module_name='tags' AND [act]='1' AND trash='0'");

		$tags_name_ids = DB::query_fetch_key_value("SELECT id, [name] FROM {tags_name} WHERE [name] IN ('".implode("', '", $sql_tags)."')", "name", "id");
		foreach ($tags as $i => $tag)
		{
			if (empty($tags_name_ids[$tag]))
			{
				$tags_name_ids[$tag] = DB::query("INSERT INTO {tags_name} ([name]) VALUES ('%h')", $tag);
				$rewrite = $tags_rewrite[$i];
				DB::query("UPDATE {tags_name} SET `sort`=`id` WHERE id=%d", $tags_name_ids[$tag]);

				// сохраняет ЧПУ
				$this->diafan->_route->save($rewrite, $tag, $tags_name_ids[$tag], 'tags', 'element', $site_id);

				// ссылка на карте сайта
				if(in_array("map", $this->diafan->installed_modules))
				{
					$tag_row = array(
						"module_name" => 'tags',
						"table" => 'tags_name',
						"id"    => $tags_name_ids[$tag],
						"site_id" => $site_id,
					);
					$this->diafan->_map->index_element($tag_row);
				}
			}
			elseif($tags_rewrite[$i])
			{
				$rewrite_id = DB::query_result("SELECT id FROM {rewrite} WHERE module_name='tags' AND element_id=%d AND element_type='element'", $tags_name_ids[$tag]);
				if($rewrite_id)
				{
					DB::query("UPDATE {rewrite} SET rewrite='%h' WHERE id=%d", $tags_rewrite[$i], $rewrite_id);
				}
				else
				{
					DB::query("INSERT INTO {rewrite} (rewrite, module_name, element_id, element_type) VALUES ('%h', 'tags', %d, 'element')", $tags_rewrite[$i], $tags_name_ids[$tag]);
				}
			}
		}
		$this->diafan->_cache->delete("", "tags");

		$this->result["redirect"] = BASE_PATH_HREF.'tags/';
	}

	/**
	 * Прикрепляет теги к элементу
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

		$tags = array();
		if(! empty($_POST["tags"]))
		{
			$newtags = explode("\n", $_POST["tags"]);
			foreach ($newtags as $tag)
			{
				$tag = trim($tag);
				if($tag)
				{
					$tags[] = $tag;
					$sql_tags[] = str_replace("'", "\\'", $tag);
				}
			}
		}
		else
		{
			$tag = trim($_POST["tag"]);
			if($tag)
			{
				$tags[] = $tag;
				$sql_tags[] = str_replace("'", "\\'", $tag);
			}
		}
		if (empty($tags))
		{
			$this->result["error"] = $this->diafan->_('Поле пустое');
		}

		$this->result["id"] = $this->get_id_element();
		$element_type = $this->diafan->element_type();

		$tags_current = DB::query_fetch_value("SELECT tags_name_id FROM {tags} WHERE module_name='%h' AND element_id=%d AND element_type='%s' AND trash='0'", $this->diafan->_admin->rewrite, $this->result["id"],$element_type, "tags_name_id");
		$tags_name_ids = DB::query_fetch_key_value("SELECT id, [name] FROM {tags_name} WHERE [name] IN ('".implode("', '", $sql_tags)."')", "name", "id");
		foreach ($tags as $tag)
		{
			if (empty($tags_name_ids[$tag]))
			{
				if(empty($row_site))
				{
					$site_id = DB::query_result("SELECT id FROM {site} WHERE module_name='tags' AND [act]='1' AND trash='0'");
				}
				$tags_name_ids[$tag] = DB::query("INSERT INTO {tags_name} ([name]) VALUES ('%h')", $tag);

				DB::query("UPDATE {tags_name} SET `sort`=`id` WHERE id=%d", $tags_name_ids[$tag]);

				// сохраняет ЧПУ
				if(ROUTE_AUTO_MODULE)
				{
					$this->diafan->_route->save('', $tag, $tags_name_ids[$tag], 'tags', 'element', $site_id);
				}

				// ссылка на карте сайта
				if(in_array("map", $this->diafan->installed_modules))
				{
					$tag_row = array(
						"module_name" => 'tags',
						"id"    => $tags_name_ids[$tag],
						"site_id" => $site_id,
					);
					$this->diafan->_map->index_element($tag_row);
				}
			}
			if (in_array($tags_name_ids[$tag], $tags_current))
			{
				continue;
			}
			$fields = array("module_name, element_id, tags_name_id, element_type");
			$mask = array("'%h', %d, '%d', '%s'");
			$values = array($this->diafan->_admin->rewrite, $this->result["id"], $tags_name_ids[$tag], $element_type);
			if(count($this->diafan->_languages->all) > 1)
			{
				$element = DB::query_fetch_array("SELECT * FROM {".$this->diafan->table."} WHERE id=%d LIMIT 1", $this->result["id"]);
				foreach ($this->diafan->_languages->all as $l)
				{
					$mask[] = "'%d'";
					$fields[] = "act".$l["id"];
					if(isset($element["act".$l["id"]]))
					{
						$values[] = $element["act".$l["id"]];
					}
					elseif(isset($element["act"]))
					{
						$values[] = $element["act"];
					}
					else
					{
						$values[] = 1;
					}
				}
			}
			if($this->diafan->is_variable('date_period'))
			{
				$mask[] = "%d";
				$fields[] = "date_start";
				$values[] = $this->diafan->unixdate($_POST['date_start']);

				$mask[] = "%d";
				$fields[] = "date_finish";
				$values[] = $this->diafan->unixdate($_POST['date_finish']);
			}

			$tags_id = DB::query("INSERT INTO {tags} (".implode(",", $fields).") VALUES (".implode(",", $mask).")", $values);
		}
		$this->diafan->_cache->delete("", "tags");

		Custom::inc('modules/tags/admin/tags.admin.view.php');
		$tags_view = new Tags_admin_view($this->diafan);

		$this->result["data"]   = $tags_view->show($this->result["id"]);
		$this->result["target"] = ".tags_container";
	}

	/**
	 * Удаляет тег
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

		$tag_id = intval($_POST['tag_id']);
		if (! empty($tag_id))
		{
			$row = DB::query_fetch_array("SELECT element_id, tags_name_id FROM {tags} WHERE module_name='%h' AND id=%d LIMIT 1", $this->diafan->_admin->rewrite, $tag_id);
			if (! $row)
			{
				$this->result["error"] = 'ERROR';
				return;
			}
		}

		DB::query("DELETE FROM {tags} WHERE module_name='%h' AND id=%d", $this->diafan->_admin->rewrite, $tag_id);
		if (! DB::query_result("SELECT id FROM {tags} WHERE tags_name_id=%d LIMIT 1", $row["tags_name_id"]))
		{
			DB::query("DELETE FROM {tags_name} WHERE id=%d", $row["tags_name_id"]);
			
			$this->diafan->_route->delete($row["tags_name_id"], 'tags');
			// ссылка на карте сайта
			$this->diafan->_map->delete($row["tags_name_id"], 'tags_name');
		}

		$this->diafan->_cache->delete("", "tags");

		Custom::inc('modules/tags/admin/tags.admin.view.php');
		$tags_view = new Tags_admin_view($this->diafan);

		$this->result["data"] = $tags_view->show($row["element_id"]);
	}

	/**
	 * Редактирует изображение
	 * 
	 * @return void
	 */
	private function search()
	{
		$mes = '';

		//максимальный и минимальный размеры текста в em
		$max = 3;
		$min = 0.9;

		$maxr = 0;
		$minr = 10;

		$element_type = $this->diafan->element_type();

		$rows = DB::query_fetch_all("SELECT id, [name] FROM {tags_name} WHERE trash='0'"
		                 .(empty($_POST["new"])
		                   ? " AND id NOT IN"
		                     ." (SELECT tags_name_id FROM {tags} WHERE module_name='%h' AND element_id=%d AND element_type='%s')"
		                   : "")
		                 ." ORDER BY sort ASC",
		                 $this->diafan->_admin->rewrite, $_POST["element_id"], $element_type);
		foreach ($rows  as &$row)
		{
			$row["size"] = DB::query_result("SELECT COUNT(*) FROM {tags} WHERE tags_name_id='%d' and trash='0'", $row["id"]);
			$maxr = $maxr < $row["size"] ? $row["size"] : $maxr;
			$minr = $minr > $row["size"] ? $row["size"] : $minr;
		}

		foreach ($rows  as &$row)
		{
			if (! $row["size"])
			{
				$size = $min;
			}
			else
			{
				$size = $maxr - $minr < 1
				        ? $min
				        : ($max - $min) * ($row["size"] - $minr) / ($maxr - $minr) + $min;
			}
			$mes .= '<a href="javascript:void(0)" class="tags_add" style="font-size: '.$size.'em;">'
			        .$row["name"].'</a> ';
		}
		if(! $mes)
		{
			$mes = $this->diafan->_('Добавленных ранее тегов нет');
		}

		$mes = '
		<div class="fa fa-close ipopup__close"></div>
		<div class="ipopup__heading">'.$this->diafan->_('Теги').'</div>
		
		<div class="tags_search">'.$mes.'
		</div>';
		$this->result["data"] = $mes;
	}

	/**
	 * Возвращает номер элемента, к которому подключается фотография или тэг
	 * 
	 * @return integer
	 */
	private function get_id_element()
	{
		if (! empty($_POST["id"]))
		{
			return intval($_POST["id"]);
		}
		else
		{
			$names  = array();
			$values = array();
			if ($this->diafan->variable_list('plus'))
			{
				$names[]  = "parent_id";
				$values[] = "'".$this->diafan->_route->parent."'";
			}
			if ($this->diafan->config('element'))
			{
				$names[]  = "cat_id";
				$values[] = "'".$this->diafan->_route->cat."'";
			}
			if ($this->diafan->config('element_site'))
			{
				$names[]  = "site_id";
				$values[] = "'".$this->diafan->_route->site."'";
			}

			$id = DB::query("INSERT INTO {".$this->diafan->table."} (".implode(', ', $names).") VALUES (".implode(', ', $values).")");

			return $id;
		}
	}
}
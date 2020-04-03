<?php
/**
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
 * Rel_elements_admin
 *
 * Похожие элементы
 */
class Rel_elements_admin extends Diafan
{
	/**
	 * @var array результаты, передаваемы Ajax`ом
	 */
	private $result;

	/**
	 * Вызывает обработку Ajax-запросов
	 *
	 * @return void
	 */
	public function ajax()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if ($_POST["action"] != "show_rel_elements")
		{
			if ( ! $this->diafan->_users->checked)
			{
				$this->result["error"] = 'ERROR_HASH';
				$this->send_json();
			}
		}
		$this->result["hash"] = $this->diafan->_users->get_hash();

		if ( ! empty($_POST["action"]))
		{
			switch ($_POST["action"])
			{
				case 'rel_elements':
					$this->rel_elements();
					break;
				case 'show_rel_elements':
					$this->show_rel_elements();
					break;
				case 'delete_rel_element':
					$this->delete_rel_element();
			}
		}
	}

	/**
	 * Прикрепляет похожие элементы
	 *
	 * @return void
	 */
	private function rel_elements()
	{
		if (empty($_POST["element_id"]))
		{
			$_POST["element_id"] = DB::query("INSERT INTO {".$this->diafan->table."} () VALUES ()");
			$this->result["id"] = $_POST["element_id"];
		}
		if ($_POST["element_id"] != $_POST["rel_id"] &&
			! DB::query_result("SELECT id FROM {".$this->diafan->table."_rel} WHERE element_id=%d AND rel_element_id=%d LIMIT 1", $_POST["element_id"], $_POST["rel_id"])
			&& (empty($_POST["rel_two_sided"]) || ! DB::query_result("SELECT id FROM {".$this->diafan->table."_rel} WHERE rel_element_id=%d AND element_id=%d LIMIT 1", $_POST["element_id"], $_POST["rel_id"])))
		{
			DB::query("INSERT INTO {".$this->diafan->table."_rel} (element_id, rel_element_id) VALUES (%d, %d)", $_POST["element_id"], $_POST["rel_id"]);
		}

		$element_id = $this->diafan->filter($_POST, "int", "element_id");
		if($this->diafan->variable_list("name", "variable"))
		{
			$name = $this->diafan->variable_list("name", "variable");
		}
		else
		{
			$name = 'name';
		}

		$this->result["data"] = '';
		$rows = DB::query_fetch_all("SELECT s.id, s.[".$name."], s.site_id FROM {".$this->diafan->table."} AS s"
				." INNER JOIN {".$this->diafan->table."_rel} AS r ON s.id=r.rel_element_id AND r.element_id=%d"
				.(! empty($_POST["rel_two_sided"]) ? " OR s.id=r.element_id AND r.rel_element_id=%d" : "")
				." WHERE s.trash='0' GROUP BY s.id",
				$element_id, $element_id
			);
		foreach ($rows as $row)
		{
			$link = $this->diafan->_route->link($row["site_id"], $row["id"], $this->diafan->_admin->module);
			if($this->diafan->is_variable("images") || $this->diafan->is_variable("image"))
			{
				$row_img = DB::query_fetch_array("SELECT name, folder_num FROM {images} WHERE element_id=%d AND module_name='%s' AND element_type='element' AND trash='0' ORDER BY sort ASC LIMIT 1", $row["id"], $this->diafan->table);
			}
			$this->result["data"] .= '
			<div class="rel_element" element_id="'.$element_id.'" rel_id="'.$row["id"].'">'
				.(! empty($row_img) ? '<img src="'.BASE_PATH.USERFILES.'/small/'.($row_img["folder_num"] ? $row_img["folder_num"].'/' : '').$row_img["name"].'">' : '').$this->diafan->short_text($row[$name], 50)
				.'
				<div class="rel_element_actions">';
			if($this->diafan->configmodules("page_show", $this->diafan->_admin->module, $this->diafan->_route->site))
			{
				$this->result["data"] .= '
					<a href="'.BASE_PATH.$link.'" target="_blank"><i class="fa fa-laptop"></i> '.$this->diafan->_('Посмотреть на сайте').'</a>';
			}
			$this->result["data"] .= '
					<a href="javascript:void(0)" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'" action="delete_rel_element" class="delete"><i class="fa fa-times-circle"></i> '.$this->diafan->_('Удалить').'</a>
				</div>
			</div>';
		}

		$this->send_json();
	}

	/**
	 * Подгружает список элементов для выбора похожих
	 *
	 * @return void
	 */
	private function show_rel_elements()
	{
		if($this->diafan->variable_list("name", "variable"))
		{
			$name = $this->diafan->variable_list("name", "variable");
		}
		else
		{
			$name = 'name';
		}
		if (empty($_POST["element_id"]))
		{
			$_POST["element_id"] = 0;
		}
		$nastr = 16;
		$list = '';
		if (empty($_POST["page"]))
		{
			$start = 0;
			$page = 1;
			if (! isset($_POST["search"]) && ! isset($_POST["cat_id"]))
			{
				$list .= '<div class="fa fa-close ipopup__close"></div>
				<form><div class="infofield">'.$this->diafan->_('Поиск').'</div> <input type="text" class="rel_module_search">';
				if($this->diafan->configmodules("cat", $this->diafan->_admin->module))
				{
					$cats = DB::query_fetch_key_array("SELECT id, [name], parent_id FROM {".$this->diafan->table."_category} WHERE trash='0' ORDER BY sort ASC", "parent_id");
					$vals = array();
					if(! empty($_POST["cat_id"]))
					{
						$vals[] = $this->diafan->filter($_POST, "int", "cat_id");
					}
					$list.= ' <select class="rel_module_cat_id"><option value="">'.$this->diafan->_('Все').'</option>'.$this->diafan->get_options($cats, $cats[0], $vals).'</select>';
				}
				$list.= '</form><div class="rel_all_elements_container">';
			}
		}
		else
		{
			$page = intval($_POST["page"]);
			$start = ($page - 1) * $nastr;
		}
		$list .= '<div class="rel_all_elements">';
		$rel_elements = array();
		if ($_POST["element_id"])
		{
			$rel_elements = DB::query_fetch_value("SELECT rel_element_id FROM {".$this->diafan->table."_rel} WHERE element_id=%d", $_POST["element_id"], "rel_element_id");
			if(! empty($_POST["rel_two_sided"]))
			{
				$new_rel_elements = DB::query_fetch_value("SELECT element_id FROM {".$this->diafan->table."_rel} WHERE rel_element_id=%d", $_POST["element_id"], "element_id");
				$rel_elements = array_merge($rel_elements, $new_rel_elements);
			}
		}


		$where = '';
		$inner = '';
		if(! empty($_POST["cat_id"]))
		{
			$cat_id = $this->diafan->filter($_POST, "int", "cat_id");
			if ($this->diafan->configmodules("children_elements", $this->diafan->_admin->module))
			{
				$cat_ids = $this->diafan->get_children($cat_id, $this->diafan->table."_category");
				$cat_ids[] = $cat_id;
				$where = " AND r.cat_id IN (".implode(",", $cat_ids).")";
			}
			else
			{
				$where = " AND r.cat_id=".$cat_id;
			}
			$inner = " INNER JOIN {".$this->diafan->table."_category_rel} AS r ON r.element_id=s.id";
		}

		if ( ! empty($_POST["search"]))
		{
			$count = DB::query_result("SELECT COUNT(DISTINCT s.id) FROM {".$this->diafan->table."} AS s".$inner
				." WHERE s.trash='0' AND LOWER(s.[".$name."]) LIKE LOWER('%%%h%%')"
				." AND s.id<>%d".$where, $_POST["search"], $_POST["element_id"]);
			$rows = DB::query_range_fetch_all("SELECT s.id, s.[".$name."], s.[act]".($this->diafan->table == 'shop' ? ", s.no_buy" : '')." FROM {".$this->diafan->table."} AS s"
				.$inner
				." WHERE s.trash='0' AND LOWER(s.[".$name."]) LIKE LOWER('%%%h%%')"
				." AND s.id<>%d".$where, $_POST["search"], $_POST["element_id"], $start, $nastr);
		}
		else
		{
			$count = DB::query_result("SELECT COUNT(DISTINCT s.id) FROM {".$this->diafan->table."} AS s"
				.$inner
				." WHERE s.trash='0' AND s.id<>%d".$where, $_POST["element_id"]);
			$rows = DB::query_range_fetch_all("SELECT s.id, s.[".$name."], s.[act]".($this->diafan->table == 'shop' ? ", s.no_buy" : '')." FROM {".$this->diafan->table."} AS s"
				.$inner
				." WHERE s.trash='0' AND s.id<>%d".$where, $_POST["element_id"], $start, $nastr);
		}
		$ids = array();
		foreach ($rows as $row)
		{
			$ids[] = $row["id"];
		}
		if($ids)
		{
			$row_imgs = DB::query_fetch_key("SELECT name, folder_num, element_id FROM {images} WHERE element_id IN (%s) AND module_name='%s' AND element_type='element' AND trash='0' ORDER BY sort DESC", implode(',', $ids), $this->diafan->table, "element_id");
		}
		foreach ($rows as $row)
		{
			$row_img = (! empty($row_imgs[$row["id"]]) ? $row_imgs[$row["id"]] : '');
			$list .= '<div class="rel_module'.(in_array($row["id"], $rel_elements) ? ' rel_module_selected' : '').'" element_id="'.$row["id"].'">
			<div>
			'.($row_img ? '<a href="javascript:void(0)"><img src="'.BASE_PATH.USERFILES.'/small/'.($row_img["folder_num"] ? $row_img["folder_num"].'/' : '').$row_img["name"].'"></a>' : '').'
			<a href="javascript:void(0)"'.(! $row["act"] || ! empty($row["no_buy"]) ? ' class="noact"' : '').'>'.$this->diafan->short_text($row[$name], 50).'</a>
			</div>
			</div>';
		}
		$list .= '</div><div class="clear rel_module_navig paginator">';
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
		$list .= '</div>';
		if (empty($_POST["page"]) && ! isset($_POST["search"]))
		{
			$list .= '</div>';
		}

		$this->result["data"] = $list;

		$this->send_json();
	}

	/**
	 * Удаляет похожие элементы
	 *
	 * @return void
	 */
	private function delete_rel_element()
	{
		DB::query("DELETE FROM {".$this->diafan->table."_rel} WHERE element_id=%d AND rel_element_id=%d", $_POST['element_id'], $_POST['rel_id']);
		if(! empty($_POST["rel_two_sided"]))
		{
			DB::query("DELETE FROM {".$this->diafan->table."_rel} WHERE rel_element_id=%d AND element_id=%d", $_POST['element_id'], $_POST['rel_id']);
		}

		$this->diafan->_cache->delete("", $this->diafan->_admin->module);

		$this->send_json();
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
}

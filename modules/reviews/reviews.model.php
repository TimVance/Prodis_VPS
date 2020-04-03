<?php
/**
 * Модель
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
 * Reviews_model
 */
class Reviews_model extends Model
{
	/**
	 * Подготавливает данные об отзывах и форму добавления отзывов для текущей страницы.
	 *
	 * @param integer $element_id номер элемента
	 * @param string $module_name название модуля, по умолчанию текущий модуль
	 * @param string $element_type тип данных
	 * @return array
	 */
	public function show($element_id, $module_name, $element_type)
	{
		////navigation//
		$this->diafan->_paginator->variable = 'rpage';
		$this->diafan->_paginator->nastr    = $this->diafan->configmodules('nastr', 'reviews');
		$this->diafan->_paginator->nen = DB::query_result(
			"SELECT COUNT(*) FROM {reviews}"
			." WHERE module_name='%h' AND element_id=%d AND element_type='%s'"
			." AND act='1' AND trash='0'",
			$module_name, $element_id, $element_type
		);
		$result["paginator"] = $this->diafan->_paginator->get(
			"reviews", __FUNCTION__, array(
				"element_id" => $element_id,
				"module_name" => $module_name,
				"element_type" => $element_type
			)
		);
		////navigation///

		$result["view_rows"] = 'rows';
		$result["show_more"] = $this->diafan->_tpl->get('show_more', 'paginator', $result["paginator"]);
		$result["paginator"] = $this->diafan->_tpl->get('get', 'paginator', $result["paginator"]);

		if($this->diafan->_paginator->nen)
		{
			$where_list = "(module_name='".$module_name."' OR module_name='')";
			$params_list = $this->get_params(array("module" => "reviews", "where" => $where_list, "fields" => 'show_in_list, info'));

			$result["rows"] = DB::query_range_fetch_all(
				"SELECT * FROM {reviews}"
				." WHERE module_name='%h' AND element_id=%d AND element_type='%s'"
				." AND act='1' AND trash='0' ORDER BY created DESC",
				$module_name, $element_id, $element_type,
				$this->diafan->_paginator->polog, $this->diafan->_paginator->nastr
			);
		}
		else
		{
			$result["rows"] = array();
		}
		foreach ($result["rows"] as &$row)
		{
			if ($this->diafan->configmodules('user_name', 'reviews') && $row["user_id"])
			{
				$this->prepare_author($row["user_id"]);
			}
			$this->prepare_param_values($row["id"]);
		}
		foreach ($result["rows"] as &$row)
		{
			$this->element($row, $params_list);
		}

		$result["register_to_review"] = $this->diafan->configmodules('only_user', 'reviews') && ! $this->diafan->_users->id;

		$where_form = "(module_name='".$module_name."' OR module_name='') AND show_in_"
					  .($this->diafan->_users->id ? "form_auth" : "form_no_auth")."='1'";
		$params_form = $this->get_params(array("module" => "reviews", "where" => $where_form, "fields" => 'info'));
		foreach($params_form as &$param)
		{
			$param["text"] = $this->diafan->_tpl->htmleditor($param["text"]);
			$param["value"] = '';
			if($this->diafan->_users->id)
			{
				switch($param["info"])
				{
					case "email":
						$param["value"] = $this->diafan->_users->mail;
						break;

					case "phone":
						$param["value"] = $this->diafan->_users->phone;
						break;

					case "name":
						$param["value"] = $this->diafan->_users->fio;
						break;

					case "rating":
						if(empty($result["average_rating"]))
						{
							$result["average_rating"] = DB::query_result("SELECT AVG(p.value) FROM {reviews} AS r"
								." INNER JOIN {reviews_param_element} AS p ON p.element_id=r.id"
								." WHERE r.module_name='%h' AND r.element_id=%d AND r.element_type='%s'"
								." AND r.act='1' AND r.trash='0'"
								." AND p.param_id=%d", $module_name, $element_id, $element_type, $param["id"]);
						}
						break;
				}
			}
			else
			{
				switch($param["info"])
				{
					case "rating":
						if(empty($result["average_rating"]))
						{
							$result["average_rating"] = DB::query_result("SELECT AVG(p.value) FROM {reviews} AS r"
								." INNER JOIN {reviews_param_element} AS p ON p.element_id=r.id"
								." WHERE r.module_name='%h' AND r.element_id=%d AND r.element_type='%s'"
								." AND r.act='1' AND r.trash='0'"
								." AND p.param_id=%d", $module_name, $element_id, $element_type, $param["id"]);
						}
						break;
				}
			}
		}

		// форма добавления отзыва
		if (! $this->diafan->configmodules('only_user', 'reviews') || $this->diafan->_users->id)
		{
			$result["form"]["element_id"] = $element_id;
			$result["form"]["module_name"] = $module_name;
			$result["form"]["element_type"] = $element_type;
			$result["form"]["form_tag"] = "reviews";
			$result["form"]['params'] = array();
			$fields = array('', 'captcha');
			if($params_form)
			{
				foreach ($params_form as $p)
				{
					$fields[] = 'p'.$p["id"];
					$result["form"]['params'][] = $p;
				}
			}
			$result["form"]["captcha"] = '';
			$this->form_errors($result["form"], $result["form"]["form_tag"], $fields);
			if ($this->diafan->_captcha->configmodules('reviews'))
			{
				$result["form"]["captcha"] = $this->diafan->_captcha->get($result["form"]["form_tag"], $result["form"]["error_captcha"]);
			}
		}
		else
		{
			$result["form"] = false;
		}

		return $result;
	}

	/**
	 * Формирует данные об одном отзыве
	 *
	 * @param array $row массив данных об отзыве
	 * @return void
	 */
	public function element(&$row, $params_list)
	{
		if($this->diafan->_site->timeedit < $row['created'])
		{
			$this->diafan->_site->timeedit = $row['created'];
		}
		$row['date'] = $this->diafan->_useradmin->get($this->format_date($row['created'], "reviews"), 'created', $row["id"], 'reviews');

		if ($this->diafan->configmodules('user_name', 'reviews') && $row["user_id"])
		{
			$row["name"] = $this->get_author($row["user_id"]);
		}
		$this->get_param_values($row, $params_list);
		$row["text"] = $this->diafan->_tpl->htmleditor($row["text"]);
	}

	/**
	 * Получает дополнительные поля отзывов
	 *
	 * @param array $row_element данные об отзыве
	 * @param array $params дополнительные поля отзывов
	 * @return void
	 */
	public function get_param_values(&$row_element, $params)
	{
		if(! $params)
		{
			return array();
		}
		$this->prepare_param_values($row_element["id"]);
		if(! empty($this->cache["prepare_param_values"]))
		{
			foreach ($this->cache["prepare_param_values"] as $idv)
			{
				$this->cache["param_values"][$idv] = array();
			}
			$rows = DB::query_fetch_all("SELECT id, value, param_id, element_id FROM {reviews_param_element} WHERE element_id IN (".implode(",", $this->cache["prepare_param_values"]).")");
			foreach ($rows as $row)
			{
				$this->cache["param_values"][$row["element_id"]][$row["param_id"]][]  = $row;
			}
			unset($this->cache["prepare_param_values"]);
		}
		$values = $this->cache["param_values"][$row_element["id"]];
		$row_element["params"] = array();
		foreach ($params as $row)
		{
			$param = array();
			switch ($row["type"])
			{
				case "editor":
					if (! empty($values[$row["id"]][0]["value"]))
					{
						$param = array(
							"id" => $row["id"],
							"name" => $row["name"],
							"value" => $this->diafan->_tpl->htmleditor($values[$row["id"]][0]["value"]),
							"value_id" => $values[$row["id"]][0]["id"],
							"type" => $row["type"]
						);
					}
					break;
				case "text":
				case "textarea":
				case "url":
				case "phone":
				case "email":
					if (! empty($values[$row["id"]][0]["value"]))
					{
						$param = array(
							"id" => $row["id"],
							"name" => $row["name"],
							"value" => $values[$row["id"]][0]["value"],
							"value_id" => $values[$row["id"]][0]["id"],
							"type" => $row["type"]
						);
					}
					break;
				case "date":
					if (! empty($values[$row["id"]][0]["value"]))
					{
						$param = array(
							"id" => $row["id"],
							"name" => $row["name"],
							"value" => $this->diafan->formate_from_date($values[$row["id"]][0]["value"]),
							"value_id" => $values[$row["id"]][0]["id"],
							"type" => $row["type"]
						);
					}
					break;
				case "datetime":
					if (! empty($values[$row["id"]][0]["value"]))
					{
						$param = array(
							"id" => $row["id"],
							"name" => $row["name"],
							"value" => $this->diafan->formate_from_datetime($values[$row["id"]][0]["value"]),
							"value_id" => $values[$row["id"]][0]["id"],
							"type" => $row["type"]
						);
					}
					break;
				case "select":
				case "radio":
					$value = ! empty($values[$row["id"]][0]["value"]) ? $values[$row["id"]][0]["value"] : '';
					if ($value)
					{
						$param = array("id" => $row["id"], "name" => $row["name"], "type" => $row["type"], "value" => $row["select_values"][$value]);

					}
					break;
				case "multiple":
					if (! empty($values[$row["id"]]))
					{
						$value = array();
						foreach ($values[$row["id"]] as $val)
						{
							$value[] = $row["select_values"][$val["value"]];

						}
						$param = array("id" => $row["id"], "name" => $row["name"], "type" => $row["type"], "value" => $value);
					}
					break;
				case "checkbox":
					$value = ! empty($values[$row["id"]][0]["value"]) ? 1 : 0;
					if (! empty($row["select_values"][$value]))
					{
						$param = array("id" => $row["id"], "name" => $row["name"], "type" => $row["type"], "value" => $row["select_values"][$value]);
					}
					elseif($value == 1)
					{
						$param = array("id" => $row["id"], "name" => $row["name"], "type" => $row["type"], "value" => '');
					}
					break;
				case "title":
					$param = array(
						"id" => $row["id"],
						"name" => $row["name"],
						"type" => $row["type"],
						"value" => ''
					);
					break;
				case "images":
					$value = $this->diafan->_images->get('large', $row_element["id"], "reviews", 'element', 0, '', $row["id"]);
					if(! $value)
						continue 2;

					$param = array(
						"id" => $row["id"],
						"name" => $row["name"],
						"type" => $row["type"],
						"value" => $value
					);
					break;
				case "attachments":
					$config = unserialize($row["config"]);
					if(! $config["attachments_access_admin"])
					{
						$attachments = $this->diafan->_attachments->get($row_element["id"], "reviews", $row["id"]);
						if(! $attachments)
							break;
						$param = array(
							"id" => $row["id"],
							"name" => $row["name"],
							"type" => $row["type"],
							"value" => $attachments,
							"use_animation" => ! empty($config["use_animation"]) ? true : false,
						);
					}
					break;
				default:
					if (! empty($values[$row["id"]][0]["value"]))
					{
						$param = array(
							"id" => $row["id"],
							"name" => $row["name"],
							"value" => $values[$row["id"]][0]["value"],
							"value_id" => $values[$row["id"]][0]["id"],
							"type" => $row["type"]
						);
					}
					break;
			}
			if(! empty($param))
			{
				if($row["show_in_list"])
				{
					$row_element["params"][] = $param;
				}
				switch($row["info"])
				{
					case "name":
						$row_element["name"]["fio"] = $param["value"];
						break;

					case "avatar":
						if($row["type"] == "images")
						{
							$row_element["name"]["avatar"] = $param["value"][0]["src"];
							$row_element["name"]["avatar_width"] = $param["value"][0]["width"];
							$row_element["name"]["avatar_height"] = $param["value"][0]["height"];
						}
						break;
				}
			}
		}
	}

	/**
	 * Запоминает номера отзывов, для которых понядобятся значения доп. полей
	 *
	 * @param integer $id номер отзыва
	 * @return void
	 */
	public function prepare_param_values($id)
	{
		if(isset($this->cache["param_values"][$id]))
		{
			return;
		}
		if(empty($this->cache["prepare_param_values"]) || ! in_array($id, $this->cache["prepare_param_values"]))
		{
			$this->cache["prepare_param_values"][] = $id;
		}
	}

	/**
	 * Генерирует данные для шаблонной функции: блок отзывов
	 *
	 * @param integer $count количество отзывов
	 * @param array $element_ids элементы
	 * @param array $modules модули
	 * @param string $element_type тип элемента
	 * @param string $sort сортировка date - по дате, rand - случайно
	 * @return array
	 */
	public function show_block($count, $element_ids, $modules, $element_type, $sort)
	{
		$where = '';
		foreach ($modules as $i => $module)
		{
			$module = preg_replace('/[^a-z_]+/', '', trim($module));
			if(! $module)
			{
				unset($modules[$i]);
			}
		}
		if($modules)
		{
			$where .= " AND module_name IN ('".implode("','", $modules)."')";
		}
		foreach ($element_ids as $i => $element_id)
		{
			$element_id = intval(trim($element_id));
			if(empty($element_id))
			{
				unset($element_ids[$i]);
			}
		}
		if(! empty($element_ids))
		{
			$where .= " AND element_id IN (".implode(",", $element_ids).")";
		}

		if ($sort == "rand")
		{
			$max_count = DB::query_result(
				"SELECT COUNT(*) FROM {reviews}"
				." WHERE act='1' AND trash='0'".$where
				." AND element_type='%s'", $element_type
			);
			$rands = array();
			for ($i = 1; $i <= min($max_count, $count); $i++)
			{
				do
				{
					$rand = mt_rand(0, $max_count - 1);
				}
				while (in_array($rand, $rands));
				$rands[] = $rand;
			}
		}
		else
		{
			$rands[0] = 1;
		}

		$params_list = $this->get_params(array("module" => "reviews", "fields" => 'show_in_list, info'));

		$prepare_elements = array();

		$result["rows"] = array();
		foreach ($rands as $rand)
		{
			$rows = DB::query_range_fetch_all(
				"SELECT created, user_id, id, module_name, element_id, element_type, text FROM {reviews}"
				." WHERE act='1' AND trash='0'".$where
				." AND element_type='%s'"
				.($sort == "date" ? ' ORDER BY created DESC, id DESC' : ''),
				$element_type,
				$sort == "rand" ? $rand : 0,
				$sort == "rand" ? 1 : $count
			);
			foreach ($rows as &$row)
			{
				if ($this->diafan->configmodules('user_name', 'reviews') && $row["user_id"])
				{
					$this->prepare_author($row["user_id"]);
				}
				$this->prepare_param_values($row["id"]);
				$this->diafan->_route->prepare(0, $row["element_id"], $row["module_name"], $row["element_type"]);
				$prepare_elements[$row["module_name"]][] = $row["element_id"];
			}
			$result["rows"] = array_merge($result["rows"], $rows);
		}
		foreach($prepare_elements as $p_module_name => $p_ids)
		{
			$elements[$p_module_name] = DB::query_fetch_key_value("SELECT id, [name] FROM {%s} WHERE id IN (%s)", $p_module_name, implode(",", $p_ids), "id", "name");
		}
		foreach ($result["rows"] as &$row)
		{
			if($this->diafan->_site->timeedit < $row['created'])
			{
				$this->diafan->_site->timeedit = $row['created'];
			}
			$row['date'] = $this->diafan->_useradmin->get($this->format_date($row['created'], "reviews"), 'created', $row["id"], 'reviews');

			if ($this->diafan->configmodules('user_name', 'reviews') && $row["user_id"])
			{
				$row["name"] = $this->get_author($row["user_id"]);
			}
			$this->get_param_values($row, $params_list);

			$row["link"] = $this->diafan->_route->link(0, $row["element_id"], $row["module_name"], $row["element_type"]);
			$row["theme_name"] = ! empty($elements[$row["module_name"]][$row["element_id"]]) ? $elements[$row["module_name"]][$row["element_id"]] : '';
		}

		$result["view_rows"] = 'rows_block';

		return $result;
	}
}

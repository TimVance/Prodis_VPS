<?php
/**
 * Подключение модуля
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
 * Сomments_inc
 */
class Comments_inc extends Model
{
	/**
	 * Показывает комментарии, прикрепленные к элементу, и форму добавления комментария
	 *
	 * @param integer $element_id номер элемента, по умолчанию текущий элемент модуля
	 * @param string $module_name название модуля, по умолчанию текущий модуль
	 * @param string $element_type тип данных
	 * @param integer $site_id страница сайта, к которой прикреплен элемент, по умолчанию текущая страница сайта
	 * @param boolean $ajax маркер загрузки результатов без перезагрузки страницы
	 * @return string
	 */
	public function get($element_id = 0, $module_name = '', $element_type = 'element', $site_id = 0, $ajax = false)
	{
		if(! $module_name)
		{
			$module_name = $this->diafan->_site->module;
		}
		if(! $element_id)
		{
			$element_id = ($element_type == 'element' ? $this->diafan->_route->show : $this->diafan->_route->$element_type);
		}
		if(! $site_id)
		{
			$site_id = $this->diafan->_site->id;
		}

		if(! $this->diafan->configmodules("comments".($element_type != 'element' ? "_".$element_type : ""), $module_name, $site_id))
		{
			return false;
		}

		$where_form = "(module_name='".$module_name."' OR module_name='') AND show_in_"
					  .($this->diafan->_users->id ? "form_auth" : "form_no_auth")."='1'";
		$where_list = "(module_name='".$module_name."' OR module_name='') AND show_in_list='1'";

		$params_form = $this->get_params(array("module" => "comments", "where" => $where_form));
		$params_list = $this->get_params(array("module" => "comments", "where" => $where_list));
		foreach($params_form as &$param)
		{
			$param["text"] = $this->diafan->_tpl->htmleditor($param["text"]);
		}

		////navigation//
		$this->diafan->_paginator->variable = 'dpage';
		$this->diafan->_paginator->nastr    = $this->diafan->configmodules('nastr', 'comments');
		$this->diafan->_paginator->nen = DB::query_result(
			"SELECT COUNT(*) FROM {comments}"
			." WHERE module_name='%h' AND element_id=%d AND element_type='%s'"
			." AND act='1' AND trash='0' AND parent_id=0",
			$module_name, $element_id, $element_type
		);
		$result["paginator"] = $this->diafan->_paginator->get(
			"comments", __FUNCTION__, array(
				"element_id" => $element_id,
				"module_name" => $module_name,
				"element_type" => $element_type,
				"site_id" => $site_id
			)
		);
		////navigation///

		$rows[0] = DB::query_range_fetch_all(
			"SELECT created, user_id, text, id FROM {comments}"
			." WHERE module_name='%h' AND element_id=%d AND element_type='%s'"
			." AND act='1' AND trash='0' AND parent_id=0 ORDER BY created ASC",
			$module_name, $element_id, $element_type,
			$this->diafan->_paginator->polog, $this->diafan->_paginator->nastr
		);
		$parents = array();
		foreach ($rows[0] as &$row)
		{
			if ($this->diafan->configmodules('user_name', 'comments') && $row["user_id"])
			{
				$this->prepare_author($row["user_id"]);
			}
			$this->prepare_param_values($row["id"]);
			$parents[] = $row["id"];
		}
		if($parents)
		{
			$children = DB::query_fetch_all(
				"SELECT c.created, c.user_id, c.text, c.id, c.parent_id FROM {comments} AS c"
				." INNER JOIN {comments_parents} AS p ON c.id=p.element_id"
				." WHERE c.module_name='%h' AND c.element_id=%d AND element_type='%s'"
				." AND c.act='1' AND c.trash='0'"
				." AND p.parent_id IN (%s)"
				." GROUP BY c.id ORDER BY c.created ASC",
				$module_name, $element_id, $element_type, implode(",", $parents)
			);
			foreach ($children as &$child)
			{
				if ($this->diafan->configmodules('user_name', 'comments') && $child["user_id"])
				{
					$this->prepare_author($child["user_id"]);
				}
				$this->prepare_param_values($child["id"]);
				$rows[$child["parent_id"]][] = $child;
			}
		}
		if(! empty($rows))
		{
			foreach ($rows as $parent_id => &$rows_parent)
			{
				foreach ($rows_parent as &$r)
				{
					$this->element($r, $params_list, $element_id, $module_name, $element_type, $params_form);
				}
			}
		}

		$result["rows"] = $this->build_tree($rows);
		$result["form"] = $this->get_form($params_form, $element_id, $module_name, $element_type);
		$result["register_to_comment"] = $this->diafan->configmodules('only_user', 'comments') && ! $this->diafan->_users->id;

		$result["view_rows"] = 'list';
		$result["show_more"] = $this->diafan->_tpl->get('show_more', 'paginator', $result["paginator"]);
		$result["paginator"] = $this->diafan->_tpl->get('get', 'paginator', $result["paginator"]);

		if($this->diafan->configmodules('use_mail', 'comments')
		   && ! empty($_GET["module"]) && $_GET["module"] == 'comments'
		   && ! empty($_GET["action"]) && $_GET["action"] == 'unsubscribe'
		   && ! empty($_GET["mail"]))
		{
			if($mail_id = DB::query_result("SELECT id FROM {comments_mail} WHERE module_name='%s' AND element_id=%d AND mail='%h'", $module_name, $element_id, $_GET["mail"]))
			{
				DB::query("DELETE FROM {comments_mail} WHERE id=%d", $mail_id);
			}
			$result["unsubscribe"] = true;
		}

		if($result["ajax"] = $ajax)
		{
			$result["view"] = 'get';
			return $result;
		}
		else return $this->diafan->_tpl->get('get', 'comments', $result);
	}

	/**
	 * Формирует данные об одном комментарие
	 *
	 * @param array $row массив данных о комментарии
	 * @param array $params_list дополнительные поля для списка
	 * @param integer $element_id номер элемента, по умолчанию текущий элемент модуля
	 * @param string $module_name название модуля, по умолчанию текущий модуль
	 * @param string $element_type тип данных
	 * @param array $params_form дополнительные поля для формы
	 * @param boolean $hide_form скрыть форму ответа на добавленный комментарий
	 * @return void
	 */
	public function element(&$row, $params_list, $element_id, $module_name, $element_type, $params_form, $hide_form = false)
	{
		if($this->diafan->_site->timeedit < $row['created'])
		{
			$this->diafan->_site->timeedit = $row['created'];
		}
		$row['date'] = $this->diafan->_useradmin->get($this->format_date($row['created'], "comments"), 'created', $row["id"], 'comments');

		if ($this->diafan->configmodules('user_name', 'comments') && $row["user_id"])
		{
			$row["name"] = $this->get_author($row["user_id"]);
		}

		$row["text"] = $this->diafan->_useradmin->get($row["text"], 'text', $row["id"], 'comments');

		$row["params"] = $this->get_param_values($row["id"], $params_list);
		if($hide_form)
		{
			$row["form"] = false;
		}
		else
		{
			$row["form"] = $this->get_form($params_form, $element_id, $module_name, $element_type, $row["id"]);
		}
	}

	/**
	 * Формирует дерево сообщений из полученного массива
	 *
	 * @param array $rows все сообщения
	 * @param integer $parent_id номер текущего сообщения-родителя
	 * @param integer $level уровень
	 * @return string
	 */
	private function build_tree($rows, $parent_id = 0, $level = 0)
	{
		$result = array();
		$count_level = $this->diafan->configmodules("count_level", "comments");

		if($count_level && $level >= $count_level)
			return $result;

		if (! empty($rows[$parent_id]))
		{
			foreach ($rows[$parent_id] as $row)
			{
				$row["children"] = $this->build_tree($rows, $row["id"], $level+1);
				$row["level"] = $level;
				if($level+1 == $count_level)
				{
					$row["form"] = false;
				}
				$result[] = $row;
			}
		}
		return $result;
	}

	/**
	 * Получает дополнительные поля комментариев
	 *
	 * @param integer $id номер комментария
	 * @param array $params дополнительные поля комментариев
	 * @return array
	 */
	public function get_param_values($id, $params)
	{
		if(! $params)
		{
			return array();
		}
		$this->prepare_param_values($id);
		if(! empty($this->cache["prepare_param_values"]))
		{
			foreach ($this->cache["prepare_param_values"] as $idv)
			{
				$this->cache["param_values"][$idv] = array();
			}
			$rows = DB::query_fetch_all("SELECT id, value, param_id, element_id FROM {comments_param_element} WHERE element_id IN (".implode(",", $this->cache["prepare_param_values"]).")");
			foreach ($rows as $row)
			{
				$this->cache["param_values"][$row["element_id"]][$row["param_id"]][]  = $row;
			}
			unset($this->cache["prepare_param_values"]);
		}
		$values = $this->cache["param_values"][$id];
		$param = array();
		foreach ($params as $row)
		{
			switch ($row["type"])
			{
				case "editor":
					if(! empty($values[$row["id"]][0]["value"]))
					{
						$values[$row["id"]][0]["value"] = $this->diafan->_tpl->htmleditor($values[$row["id"]][0]["value"]);
					}
				case "text":
					if (! empty($values[$row["id"]][0]["value"]))
					{
						$param[] = array(
							"id" => $row["id"],
							"name" => $row["name"],
							"value" => $values[$row["id"]][0]["value"],
							"value_id" => $values[$row["id"]][0]["id"],
							"type" => $row["type"]
						);
					}
					break;
				case "textarea":
					if (! empty($values[$row["id"]][0]["value"]))
					{
						$param[] = array(
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
						$param[] = array(
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
						$param[] = array(
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
						$param[] = array("id" => $row["id"], "name" => $row["name"], "type" => $row["type"], "value" => $row["select_values"][$value]);

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
						$param[] = array("id" => $row["id"], "name" => $row["name"], "type" => $row["type"], "value" => $value);
					}
					break;
				case "checkbox":
					$value = ! empty($values[$row["id"]][0]["value"]) ? 1 : 0;
					if (! empty($row["select_values"][$value]))
					{
						$param[] = array("id" => $row["id"], "name" => $row["name"], "type" => $row["type"], "value" => $row["select_values"][$value]);
					}
					elseif($value == 1)
					{
						$param[] = array("id" => $row["id"], "name" => $row["name"], "type" => $row["type"], "value" => '');
					}
					break;
				case "title":
					$param[] = array(
						"id" => $row["id"],
						"name" => $row["name"],
						"type" => $row["type"],
						"value" => ''
					);
					break;
				case "images":
					$value = $this->diafan->_images->get('large', $id, "comments", 'element', 0, '', $row["id"]);
					if(! $value)
						continue 2;

					$param[] = array(
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
						$attachments = $this->diafan->_attachments->get($id, "comments", $row["id"]);
						if(! $attachments)
							break;
						$param[] = array(
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
						$param[] = array(
							"id" => $row["id"],
							"name" => $row["name"],
							"value" => $values[$row["id"]][0]["value"],
							"value_id" => $values[$row["id"]][0]["id"],
							"type" => $row["type"]
						);
					}
					break;
			}
		}
		return $param;
	}

	/**
	 * Запоминает номера комментариев, для которых понядобятся значения доп. полей
	 *
	 * @param integer $id номер комментария
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

	/*
	 * Формирует данные для формы
	 *
	 * @param array $params дополнительные поля формы
	 * @param integer $element_id номер элемента
	 * @param string $module_name название модуля
	 * @param string $element_type тип данных
	 * @param integer $parent_id номер комментария-родителя
	 * @return array
	 */
	public function get_form($params, $element_id, $module_name, $element_type, $parent_id = 0)
	{
		// форма комментариев
		if (! $this->diafan->configmodules('only_user', 'comments') || $this->diafan->_users->id)
		{
			$form["parent_id"] = $parent_id;
			$form["element_id"] = $element_id;
			$form["module_name"] = $module_name;
			$form["element_type"] = $element_type;
			$form["form_tag"] = "comments".$parent_id;
			$fields = array('', 'mail', 'captcha');
			$form['params'] = array();
			if($params)
			{
				foreach ($params as $row)
				{
					$fields[] = 'p'.$row["id"];
					$form['params'][] = $row;
				}
			}
			$form["captcha"] = '';
			$this->form_errors($form, $form["form_tag"], $fields);
			if ($this->diafan->_captcha->configmodules('comments'))
			{
				$form["captcha"] = $this->diafan->_captcha->get($form["form_tag"], $form["error_captcha"]);
			}
			$form["bbcode"] = $this->diafan->configmodules('use_bbcode', 'comments');
			$form['use_mail'] = $this->diafan->configmodules('use_mail', 'comments');
		}
		else
		{
			$form = false;
		}
		return $form;
	}

	/**
	 * Удаляет комментарий для одного или нескольких элементов
	 *
	 * @param integer|array $element_ids номер одного или нескольких элементов
	 * @param string $module_name название модуля
	 * @param string $element_type тип данных
	 * @return void
	 */
	public function delete($element_ids, $module_name, $element_type = 'element')
	{
		if(is_array($element_ids))
		{
			$where = " IN (%s)";
			$value = preg_replace('/[^0-9,]+/', '', implode(",", $element_ids));
		}
		else
		{
			$where = "=%d";
			$value = $element_ids;
		}
		DB::query("DELETE FROM {comments} WHERE module_name='%s' AND element_type='%s' AND element_id".$where, $module_name, $element_type, $value);
	}

	/**
	 * Удаляет все комментарии модуля
	 *
	 * @param string $module_name название модуля
	 * @return void
	 */
	public function delete_module($module_name)
	{
		DB::query("DELETE FROM {trash} WHERE module_name='comments' AND element_id IN (SELECT id FROM {comments} WHERE module_name='%s')", $module_name);
		DB::query("DELETE FROM {comments} WHERE module_name='%s' OR module_name='%s_category'", $module_name, $module_name);
	}
}

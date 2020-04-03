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
 * Tags_model
 */
class Tags_model extends Model
{
	/**
	 * Геренирует список элементов, к которым прикреплен тэг
	 *
	 * @return void
	 */
	public function list_module()
	{
		$row = DB::query_fetch_array("SELECT id, [name], [text], [title_meta], [keywords], [descr], timeedit FROM {tags_name} WHERE id=%d AND trash='0' LIMIT 1", $this->diafan->_route->show);
		if (! $row)
		{
			Custom::inc('includes/404.php');
		}

		if ($this->diafan->configmodules("images_element"))
		{
			$this->result["img"] = $this->diafan->_images->get(
					'medium', $row["id"], 'tags', 'element',
					0, $row["name"], 0, 0, 'large'
				);
		}
		$this->meta($row);
		$this->result["text"] = $this->diafan->_tpl->htmleditor($row["text"]);

		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		////pagination//
		$this->diafan->_paginator->nastr = $this->diafan->configmodules("nastr");
		$this->diafan->_paginator->nen = DB::query_result("SELECT COUNT(DISTINCT t.id) FROM {tags} AS t"
		.($this->diafan->configmodules('where_access', 'all') ? " LEFT JOIN {access} AS a ON a.element_id=t.element_id AND a.module_name=t.module_name AND a.element_type=t.element_type" : "")
		." WHERE t.tags_name_id=%d AND t.[act]='1' AND t.trash='0'"
		.($this->diafan->configmodules('where_access', 'all') ? " AND (t.access='0' OR t.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : ''), $this->diafan->_route->show)
		.($this->diafan->configmodules('where_period', 'all') ? " AND t.date_start<=".$time." AND (t.date_finish=0 OR t.date_finish>=".$time.")" : '');
		$this->result["paginator"] = $this->diafan->_paginator->get();
		////pagination///

		$k = 0;
		$includes = array();

		$rows_module = array();

		$rows_tags = DB::query_range_fetch_all("SELECT t.* FROM {tags} AS t"
		.($this->diafan->configmodules('where_access', 'all') ? " LEFT JOIN {access} AS a ON a.element_id=t.element_id AND a.module_name=t.module_name AND a.element_type=t.element_type" : "")
		." WHERE t.tags_name_id=%d AND t.[act]='1' AND t.trash='0'"
		.($this->diafan->configmodules('where_access', 'all') ? " AND (t.access='0' OR t.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		.($this->diafan->configmodules('where_period', 'all') ? " AND t.date_start<=".$time." AND (t.date_finish=0 OR t.date_finish>=".$time.")" : '')
		." GROUP BY t.id ORDER BY t.id ASC", $this->diafan->_route->show, $this->diafan->_paginator->polog, $this->diafan->_paginator->nastr);
		foreach ($rows_tags as $row)
		{
			$rows_module[$row["module_name"]][] = $row["element_id"];
		}
		foreach($rows_module as $module_name => $ids)
		{
			if($module_name == 'site')
			{
				$rows = DB::query_fetch_all("SELECT [name], [text], id FROM {".$module_name."} WHERE id IN (%s) AND trash='0' AND [act]='1' LIMIT ".count($ids), implode(',', $ids));
				foreach($rows as $row)
				{
					$this->diafan->_route->prepare(0, $row["id"], 'site');
				}
				foreach($rows as $row)
				{
					$row["link"] = $this->diafan->_route->link($row["id"]);
					$row["name"] = $this->diafan->short_text($row["name"], 20);
					$row["snippet"] = $this->diafan->short_text($row["text"], 100);
					$this->result["rows"][$module_name]["rows"][] = $row;
				}
			}
			if (! isset($includes[$module_name]))
			{
				if (Custom::exists('modules/'.$module_name.'/views/'.$module_name.'.view.list_tags.php'))
				{
					$includes[$module_name]["view_class"] = $module_name;
					$includes[$module_name]["view_func"] = 'list_tags';
				}
				else
				{
					$includes[$module_name]["view_class"] = $module_name;
					$includes[$module_name]["view_func"] = 'list';
				}
				if (Custom::exists('modules/'.$module_name.'/'.$module_name.'.model.php'))
				{
					$name = ucfirst($module_name);
					Custom::inc('modules/'.$module_name.'/'.$module_name.'.model.php');
					$class = $name.'_model';
					$func = 'tags';
					if (method_exists($class, $func))
					{
						$includes[$module_name]["model_class"] = new $class($this->diafan);
						$includes[$module_name]["model_func"] = $func;
					}
					else
					{
						$func = 'elements';
						if (method_exists($class, $func))
						{
							$includes[$module_name]["model_class"] = new $class($this->diafan);
							$includes[$module_name]["model_func"]  = $func;
						}
					}
					if (method_exists($class, 'format_data_element'))
					{
						$includes[$module_name]["model_format"] = 'format_data_element';
					}
				}
				if (empty($includes[$module_name]["view_func"]) || empty($includes[$module_name]["model_func"]))
				{
					$includes[$module_name] = false;
				}
			}
			if ($includes[$module_name])
			{
				$model = &$includes[$module_name]["model_class"];
				$func = $includes[$module_name]["model_func"];
				$format = $includes[$module_name]["model_format"];
				if ($func == 'elements')
				{
					$rows = DB::query_fetch_all("SELECT *, [name], [anons] FROM {".$module_name."} WHERE id IN (%s) AND trash='0' AND [act]='1' LIMIT ".count($ids), implode(',', $ids));

					call_user_func_array (array(&$model, $func), array(&$rows));
					$result["rows"]  = $rows;
				}
				else
				{
					$result = call_user_func_array (array(&$model, $func), array($ids));
				}
				if($format)
				{
					foreach($result["rows"] as &$row)
					{
						call_user_func_array (array(&$model, $format), array(&$row));
					}
				}
				$result["view_rows"] = ! empty($result["view_rows"]) ? $result["view_rows"] : 'rows';
				$result["class"] = $includes[$module_name]["view_class"];
				$result["func"]  = $includes[$module_name]["view_func"];
				$this->result["rows"][$module_name] = $result;
			}
		}

		$this->result["view_rows"] = 'rows';
		$this->result["show_more"] = $this->diafan->_tpl->get('show_more', 'paginator', $this->result["paginator"]);
		$this->result["paginator"] = $this->diafan->_tpl->get('get', 'paginator', $this->result["paginator"]);

		$this->result["view"] = 'list';
	}

	/**
	 * Геренирует данные для облака тэгов
	 *
	 * @param boolean $title_no_show скрывать заголовок H2
	 * @return array
	 */
	public function show_block($title_no_show = false)
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		//кеширование
		$cache_meta = array(
			"name"     => "block",
			"lang_id" => _LANG,
			"access" => ($this->diafan->configmodules('where_access', 'all') ? $this->diafan->_users->role_id : 0),
			"time" => ($this->diafan->configmodules('where_period', 'all') ? $time : 0),
		);
		if (! $this->result = $this->diafan->_cache->get($cache_meta, "tags"))
		{
			$site_id = DB::query_result("SELECT id FROM {site} WHERE module_name='tags' AND trash='0' AND [act]='1' LIMIT 1");
			if (! $site_id)
			{
				return false;
			}

			//максимальный и минимальный размеры текста в em
			$max = 3;
			$min = 0.9;

			$maxr = 0;
			$minr = 10;

			$this->result["rows"] = DB::query_fetch_all("SELECT n.id, n.[name], COUNT(t.id) AS size FROM {tags_name} AS n"
			." INNER JOIN {tags} AS t ON t.tags_name_id=n.id AND t.trash='0' AND t.[act]='1'"
			.($this->diafan->configmodules('where_access', 'all') ? " LEFT JOIN {access} AS a ON a.element_id=t.element_id AND a.module_name=t.module_name AND a.element_type=t.element_type" : "")
			." WHERE n.trash='0'"
			.($this->diafan->configmodules('where_access', 'all') ? " AND (t.access='0' OR t.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
			.($this->diafan->configmodules('where_period', 'all') ? " AND t.date_start<=".$time." AND (t.date_finish=0 OR t.date_finish>=".$time.")" : '')
			." GROUP BY n.id ORDER BY n.sort ASC");
			foreach ($this->result["rows"] as &$row)
			{
				$maxr = $maxr < $row["size"] ? $row["size"] : $maxr;
				$minr = $minr > $row["size"] ? $row["size"] : $minr;
				$this->diafan->_route->prepare($site_id, $row["id"], "tags");
				if ($this->diafan->configmodules("images_element", "tags"))
				{
					if($this->diafan->configmodules("list_img_element", "tags"))
					{
						$this->diafan->_images->prepare($row["id"], "tags");
					}
				}
			}
			foreach ($this->result["rows"] as &$row)
			{
				if($maxr - $minr < 1)
				{
					$row["size"] = $min;
				}
				else
				{
					$row["size"] = ($max - $min) * ($row["size"] - $minr) / ($maxr - $minr) + $min;
				}
				$row["link"] = $this->diafan->_route->link($site_id, $row["id"], "tags");
				if ($this->diafan->configmodules("images_element", "tags"))
				{
					if($this->diafan->configmodules("list_img_element", "tags"))
					{
						$count = $this->diafan->configmodules("list_img_element", "tags") == 1 ? 1 : 0;
						$row["img"]  = $this->diafan->_images->get(
								'medium', $row["id"], 'tags', 'element',
								0, $row["name"], 0,
								$count,
								($count ? $row["link"] : 'large')
							);
					}
				}
			}
			//сохранение кеша
			$this->diafan->_cache->save($this->result, $cache_meta, "tags");
		}

		foreach ($this->result["rows"] as &$row)
		{
			$row["selected"] = $this->diafan->_site->module == "tags" && $this->diafan->_route->show == $row["id"];
			$row["name"] = $this->diafan->_useradmin->get($row["name"], 'name', $row["id"], 'tags_name', _LANG);
		}
		$this->result["title_no_show"] = $title_no_show;
		$this->result["view"] = 'show_block';
		$this->result["view_rows"] = 'rows_block';
		return $this->result;
	}
}

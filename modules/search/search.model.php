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
 * Search_model
 */
class Search_model extends Model
{
	/**
	 * Генерирует список найденных страниц
	 * 
	 * @return void
	 */
	public function show_module()
	{
		$search = '';
		if (isset($_REQUEST["searchword"]))
		{
			if (is_array($_REQUEST["searchword"]))
			{
				$_REQUEST["searchword"] = '';
			}
			$search = trim(htmlspecialchars(stripslashes($_REQUEST["searchword"])));
			if(empty($_SESSION["search"]) || ! in_array($_REQUEST["searchword"], $_SESSION["search"]))
			{
				$_SESSION["search"][] = $_REQUEST["searchword"];
				if($count = $this->diafan->configmodules("count_history", "search"))
				{
					DB::query("INSERT INTO {search_history} (created, name) VALUES (%d, '%h')", time(), $_REQUEST["searchword"]);
					if(DB::query_result("SELECT COUNT(*) FROM {search_history}") > $count)
					{
						DB::query("DELETE FROM {search_history} ORDER BY created  ASC LIMIT 1");
					}
				}
			}
		}
		$this->result = array();

		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		if (! empty($search))
		{
			Custom::inc('includes/searchwords.php');
			$searchwords = new Searchwords();
			$searchwords->max_length = $this->diafan->configmodules("max_length", "search");
			$search_words = $searchwords->prepare($search);

			$keys = array();
			if(! empty($search_words))
			{
				if($this->diafan->configmodules("search_like", "search"))
				{
					$where = array_fill(0, count($search_words), "keyword LIKE '%%%h%%'");
					$keys = DB::query_fetch_key_value("SELECT id, keyword FROM {search_keywords} WHERE ".implode(" OR ", $where), $search_words, "keyword", "id");
				}
				else
				{
					$keys = DB::query_fetch_key_value("SELECT id, keyword FROM {search_keywords} WHERE keyword IN ('".implode("', '", $search_words)."')", "keyword", "id");
				}
			}
			if(count($keys) < count($search_words))
			{
				$nen = 0;
				$where = "";
			}
			else
			{
				$temp_table = false;
				if($this->diafan->configmodules("search_all_word", "search") && ! $this->diafan->configmodules("search_like", "search"))
				{
					$order = '';
					// обязательны все слова
					$where = "";
					foreach ($search_words as $k => $key)
					{
						$where .= " INNER JOIN {search_index} AS i".$k." ON r.id=i".$k.".result_id AND i".$k.".keyword_id=".$keys[$key];
						$order .= 'i'.$k.'.rating ASC, ';
					}
					$order .= "r.rating DESC";
				}
				else
				{
					// ищет хотя бы одно слово, сортировка по количеству найденных
					$where = "INNER JOIN {search_index} AS i ON r.id=i.result_id AND i.keyword_id IN ('".implode("', '", $keys)."')";
					if(count($search_words) > 1)
					{
						$temp_table = true;
						DB::query("CREATE TEMPORARY TABLE {search_temp} (
							keyword_id int(11) unsigned NOT NULL DEFAULT '0',
							rating tinyint(2) unsigned NOT NULL DEFAULT '0'
						);");
						$where .= " INNER JOIN {search_temp} AS t ON t.keyword_id=i.keyword_id";
						$query = '';
						if($this->diafan->configmodules("search_like", "search"))
						{
							foreach ($keys as $i => $word)
							{
								$query .= ($query ? "," : '')." (".$word.", ".(count($keys) - $i).")";
							}
						}
						else
						{
							foreach ($search_words as $i => $word)
							{
								$query .= ($query ? "," : '')." (".$keys[$word].", ".(count($search_words) - $i).")";
							}
						}
						DB::query("INSERT INTO {search_temp} (keyword_id, rating) VALUES".$query);
					}
					$order = ($temp_table ? "tmp_rating" : "r.rating")." DESC";
				}
	
				$nen = DB::query_result("SELECT COUNT(DISTINCT r.id) FROM {search_results} AS r "
				.($this->diafan->configmodules('where_access', 'all') ? " LEFT JOIN {access} AS a ON a.element_id=r.element_id AND (a.module_name=r.table_name AND a.element_type='element' OR r.table_name=CONCAT(a.module_name,'_category') AND a.element_type='cat')" : "")
				.$where." WHERE r.lang_id=%d"
				.($this->diafan->configmodules('where_access', 'all') ? " AND (r.access='0' OR r.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
				.($this->diafan->configmodules('where_period', 'all') ? " AND r.date_start<=".$time." AND (r.date_finish=0 OR r.date_finish>=".$time.")" : ''),
				_LANG);
			}

			////navigation//
			if ($nastr = $this->diafan->configmodules("nastr", "search"))
			{
				$this->diafan->_paginator->nastr = $nastr;
			}
			$this->diafan->_paginator->get_nav = '?searchword='.$search;
			$this->diafan->_paginator->nen     = $nen;
			$this->result["paginator"] = $this->diafan->_paginator->get();
			////navigation///

			$k = ! $this->diafan->_route->page ? 1 : ($this->diafan->_route->page - 1) * $this->diafan->_paginator->nastr + 1;

			$this->result["view_rows"] = 'rows';
			$this->result["show_more"] = $this->diafan->_tpl->get('show_more', 'paginator', $this->result["paginator"]);
			$this->result["paginator"] = $this->diafan->_tpl->get('get', 'paginator', $this->result["paginator"]);

			if($nen)
			{
				$rows_search = DB::query_range_fetch_all(
					"SELECT r.element_id, r.table_name".($temp_table ? ", SUM(t.rating)+r.rating AS tmp_rating" : "")." FROM {search_results} as r "
					.($this->diafan->configmodules('where_access', 'all') ? " LEFT JOIN {access} AS a ON a.element_id=r.element_id AND (a.module_name=r.table_name AND a.element_type='element' OR r.table_name=CONCAT(a.module_name,'_category') AND a.element_type='cat')" : "")
					.$where
					." WHERE r.lang_id=%d"
					.($this->diafan->configmodules('where_access', 'all') ? " AND (r.access='0' OR r.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
					.($this->diafan->configmodules('where_period', 'all') ? " AND r.date_start<=".$time." AND (r.date_finish=0 OR r.date_finish>=".$time.")" : '')
					." GROUP BY r.id ORDER BY ".$order,
					_LANG, $this->diafan->_paginator->polog, $this->diafan->_paginator->nastr);
			}
			else
			{
				$rows_search = array();
			}
			$count = count($rows_search);
			$rows_module = array();
			foreach ($rows_search as $row)
			{
				$rows_module[$row["table_name"]][] = $row["element_id"];
			}
			$this->result["rows"] = array();
			foreach($rows_module as $table_name => $ids)
			{
				if(strpos($table_name, '_'))
				{
					list($module_name, $postfix) = explode('_', $table_name, 2);
				}
				else
				{
					$module_name = $table_name;
					$postfix = '';
				}

				if($table_name == 'site')
				{
					$rows = DB::query_fetch_key("SELECT [name], [text], id FROM {site} WHERE id IN (%s) AND trash='0' AND [act]='1' LIMIT ".count($ids), implode(',', $ids), "id");
					foreach($ids as $id)
					{
						$row = $rows[$id];
						$this->diafan->_route->prepare($row["id"], $row["id"], $table_name);
					}
					foreach($ids as $id)
					{
						$row = $rows[$id];
						$row["link"] = $this->diafan->_route->link($row["id"]);
						$row["name"] = $this->diafan->short_text($row["name"], 20);
						$row["snippet"] = $this->diafan->short_text($row["text"], 100);
						$this->result["rows"][$table_name]["rows"][] = $row;
					}
				}
				elseif(strpos($table_name, '_category'))
				{
					$rows = DB::query_fetch_key("SELECT [name], [anons], [text], id, site_id FROM {%s} WHERE id IN (%s) AND trash='0' AND [act]='1' LIMIT ".count($ids), $table_name, implode(',', $ids), "id");
					foreach($ids as $id)
					{
						$row = $rows[$id];
						if(! $this->diafan->configmodules("cat", $module_name, $row["site_id"]))
						{
							unset($rows[$id]);
							continue;
						}
						$this->diafan->_route->prepare($row["site_id"], $row["id"], $module_name, 'cat');
					}
					foreach($ids as $id)
					{
						$row = $rows[$id];
						$row["link"] = $this->diafan->_route->link($row["site_id"], $row["id"], $module_name, 'cat');
						$row["name"] = $this->diafan->short_text($row["name"], 20);
						$row["snippet"] = $this->diafan->short_text($row["anons"].' '.$row["text"], 100);
						$this->result["rows"][$table_name]["rows"][] = $row;
					}
				}
				elseif(strpos($table_name, '_brand'))
				{
					$rows = DB::query_fetch_key("SELECT [name], [text], id, site_id FROM {%s} WHERE id IN (%s) AND trash='0' AND [act]='1' LIMIT ".count($ids), $table_name, implode(',', $ids), "id");
					foreach($ids as $id)
					{
						$row = $rows[$id];
						$this->diafan->_route->prepare($row["site_id"], $row["id"], $module_name, 'brand');
					}
					foreach($ids as $id)
					{
						$row = $rows[$id];
						$row["link"] = $this->diafan->_route->link($row["site_id"], $row["id"], $module_name, 'brand');
						$row["name"] = $this->diafan->short_text($row["name"], 20);
						$row["snippet"] = $this->diafan->short_text($row["text"], 100);
						$this->result["rows"][$table_name]["rows"][] = $row;
					}
				}
				else
				{
					if (! isset($includes[$table_name]))
					{
						if (Custom::exists('modules/'.$module_name.'/views/'.$module_name.'.view.list_search'.$postfix.'.php'))
						{
							$includes[$table_name]["view"] = 'list_search'.$postfix;
						}
						else
						{
							$includes[$table_name]["view"] = 'list';
						}
						if (Custom::exists('modules/'.$module_name.'/'.$module_name.'.model.php'))
						{
							$name = ucfirst($module_name);
							Custom::inc('modules/'.$module_name.'/'.$module_name.'.model.php');
							$class = $name.'_model';
							$func = 'search'.$postfix;
							$includes[$table_name]["model_format"] = '';
							if (method_exists($class, $func))
							{
								$includes[$table_name]["model_class"] = new $class($this->diafan);
								$includes[$table_name]["model_func"] = $func;
							}
							else
							{
								$func = 'elements';
								if (method_exists($class, $func))
								{
									$includes[$table_name]["model_class"] = new $class($this->diafan);
									$includes[$table_name]["model_func"] = $func;
								}
								if (method_exists($class, 'format_data_element'))
								{
									$includes[$table_name]["model_format"] = 'format_data_element';
								}
							}
						}
						if (empty($includes[$table_name]["view"]) || empty($includes[$table_name]["model_func"]))
						{
							$includes[$table_name] = false;
						}
					}
					if ($includes[$table_name])
					{
						$model = &$includes[$table_name]["model_class"];
						$func = $includes[$table_name]["model_func"];
						$format = $includes[$module_name]["model_format"];
						if ($func == 'search'.$postfix)
						{
							$result = call_user_func_array (array(&$model, $func), array($ids));
						}
						else
						{
							$rows = array();
							$rs = DB::query_fetch_key("SELECT *, [name], [anons], [act] FROM {".$table_name."} WHERE id IN (%s) AND trash='0' AND [act]='1' LIMIT ".count($ids), implode(',', $ids), "id");
							foreach($ids as $id)
							{
								$rows[] = $rs[$id];
							}
							call_user_func_array (array(&$model, $func), array(&$rows));
							if($format)
							{
								foreach($rows as &$row)
								{
									call_user_func_array (array(&$model, $format), array(&$row));
								}
							}
							$result["rows"]  = $rows;
						}
						$result["view_rows"] = ! empty($result["view_rows"]) ? $result["view_rows"] : 'rows';
						$result["class"] = $module_name;
						$result["func"]  = $includes[$table_name]["view"];
						$this->result["rows"][$table_name] = $result;
					}
				}
			}

			$this->result["count"] = $this->diafan->_paginator->nen;
			$this->result["count_start"] = $this->result["count"] ? ($this->diafan->_paginator->page - 1) * $this->diafan->_paginator->nastr + 1 : 0;
			$this->result["count_finish"] = $this->result["count"] ? $this->result["count_start"] - 1 + $count : 0;
			$this->result["count_page"] =
				$this->diafan->_paginator->nen > $this->diafan->_paginator->nastr ? 
				$this->diafan->_paginator->nastr : $this->diafan->_paginator->nen;
		}
		$this->result["value"] = $search;
		$this->result["action"] = '';
		$this->result["ajax"] = false;

		$this->result["view"] = 'show';
	}

	/**
	 * Генерирует контент для шаблонной функции: форма поиска по сайту
	 * 
	 * @param string $button значение кнопки «Найти»
	 * @param boolean $ajax подгружать результаты поиска без перезагрузки страницы
	 * @return array
	 */
	public function show_search($button, $ajax)
	{
		$result["value"] = '';
		if (isset($_REQUEST["searchword"]))
		{
			if (is_array($_REQUEST["searchword"]))
			{
				$_REQUEST["searchword"] = '';
			}
			$result["value"] = trim(htmlspecialchars(stripslashes($_REQUEST["searchword"])));
		}
		$result["ajax"] = $ajax;
		$result["action"] = BASE_PATH_HREF.$this->diafan->_route->module('search');
		if($ajax)
		{
			$this->diafan->_site->js_view[] = 'modules/search/js/search.show_search.js';
		}
		$result["button"] = $button;
		return $result;
	}
}
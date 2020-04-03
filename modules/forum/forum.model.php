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
 * Forum_model
 */
class Forum_model extends Model
{
	/**
	 * @var boolean пользователь является модератором
	 */
	public $moderator;

	/**
	 * Первая страница форума
	 * 
	 * @return void
	 */
	public function first_page()
	{
		$this->result["access_add"] = ! $this->diafan->configmodules("only_user") || $this->diafan->_users->id;

		$this->result["blocks"] = DB::query_fetch_all("SELECT name, id FROM {forum_blocks} WHERE trash='0' AND act='1' ORDER BY sort ASC");

		$this->result["cats"] = DB::query_fetch_key_array("SELECT name, id, block_id FROM {forum_category} WHERE trash='0' AND act='1' ORDER BY sort ASC", "block_id");

		foreach ($this->result["cats"] as $block_id => &$rows)
		{
			foreach ($rows as &$cat)
			{
				$this->diafan->_route->prepare($this->diafan->_site->id, $cat["id"], "forum", "cat");
			}
		}
		// выбирает количетсво тем во всех категориях
		$cats_count = DB::query_fetch_key_value("SELECT COUNT(*) as count, cat_id FROM {forum}"
			." WHERE trash='0' AND act='1' GROUP BY cat_id", "cat_id", "count");

		// выбирает последние темы для всех категорий
		$last_theme = DB::query_fetch_key("SELECT id, name, timeedit, cat_id FROM {forum}"
			." WHERE trash='0' AND act='1' ORDER BY timeedit ASC", "cat_id");
		foreach ($last_theme as &$row)
		{
			$this->diafan->_route->prepare($this->diafan->_site->id, $row["id"], "forum");
		}

		// ищет новости во всех категориях
		if ($this->diafan->_users->id)
		{
			$news = DB::query_fetch_key_value("SELECT COUNT(DISTINCT(s.element_id)) AS count, c.cat_id FROM {forum_show} as s"
				." INNER JOIN {forum_messages} as m ON m.id=s.element_id"
				." INNER JOIN {forum} as c ON c.id=m.forum_id"
				." WHERE s.user_id=%d AND c.trash='0' AND c.act='1'"
				." AND m.trash='0' AND m.act='1' GROUP BY c.cat_id",
				$this->diafan->_users->id, "cat_id", "count");
		}
		foreach ($this->result["cats"] as $block_id => &$rows)
		{
			foreach ($rows as &$cat)
			{
				$cat["link"] = $this->diafan->_route->link($this->diafan->_site->id, $cat["id"], "forum", 'cat');
				$cat["count"] = ! empty($cats_count[$cat["id"]]) ? $cats_count[$cat["id"]] : 0;
				$cat["news"] = ! empty($news[$cat["id"]]);
				if(! empty($last_theme[$cat["id"]]))
				{
					$cat["last_theme"] = array(
						'link' => $this->diafan->_route->link($this->diafan->_site->id, $last_theme[$cat["id"]]["id"], "forum"),
						'name' => $last_theme[$cat["id"]]['name'],
						"timeedit" => $this->format_date($last_theme[$cat["id"]]["timeedit"]),
					);
				}
				else
				{
					$cat["last_theme"] = false;
				}
			}
		}
		$this->result["new_messages"] = 0;
		if (! empty($news))
		{
			foreach($news as $count)
			{
				$this->result["new_messages"] += $count;
			}
		}
		$this->result["view"] = 'first_page';

		$this->result["action"]    = $this->diafan->_route->link($this->diafan->_site->id);
	}

	/**
	 * Генерирует список категорий форума
	 * 
	 * @return void
	 */
	public function list_category()
	{
		$row = DB::query_fetch_array("SELECT name FROM {forum_category} WHERE id=%d AND trash='0' AND act='1' LIMIT 1", $this->diafan->_route->cat);

		if (empty($row))
		{
			Custom::inc('includes/404.php');
		}
		if(empty($_SESSION["forum_category_view"][$this->diafan->_route->cat]))
		{
			$_SESSION["forum_category_view"][$this->diafan->_route->cat] = true;
			DB::query("UPDATE {forum} SET counter_view=counter_view+1 WHERE id=%d", $this->diafan->_route->cat);
		}
		$this->diafan->_site->titlemodule = $row["name"];

		////navigation//
		$this->diafan->_paginator->nen = DB::query_result("SELECT COUNT(*) FROM {forum} WHERE trash='0'"
			.(! $this->moderator ? " AND act='1'" : '')." AND cat_id=%d", $this->diafan->_route->cat);
		$this->result["paginator"] = $this->diafan->_paginator->get();
		////navigation///

		$this->result["rows"] = DB::query_range_fetch_all("SELECT * FROM {forum} WHERE trash='0'"
			.(! $this->moderator ? " AND act='1'" : '')." AND cat_id=%d"
			." ORDER BY prior DESC, timeedit DESC, created DESC",
			$this->diafan->_route->cat, $this->diafan->_paginator->polog, $this->diafan->_paginator->nastr
		);

		$this->result["access_add"] = ! $this->diafan->configmodules("only_user") || $this->diafan->_users->id;
		$this->result["link_add"] = $this->diafan->_route->current_link('', array('add' => 1));
		$this->result["hash"]      = $this->diafan->_users->get_hash();

		foreach ($this->result["rows"] as &$row)
		{
			$this->diafan->_route->prepare($this->diafan->_site->id, $row["id"], "forum");
			$this->prepare_author($row["user_update"]);
			$this->prepare_author($row["user_id"]);
			$this->messages_prepare_info($row["id"]);
		}
		foreach ($this->result["rows"] as &$row)
		{
			$row["hash"] = $this->result["hash"];
			$this->list_id($row);
		}

		$this->diafan->_site->breadcrumb[] = array("link" => $this->diafan->_route->link($this->diafan->_site->id), "name" => $this->diafan->_site->name);

		$this->result["view_rows"] = 'rows';
		$this->result["show_more"] = $this->diafan->_tpl->get('show_more', 'paginator', $this->result["paginator"]);
		$this->result["paginator"] = $this->diafan->_tpl->get('get', 'paginator', $this->result["paginator"]);

		$this->result["action"]    = $this->diafan->_route->link($this->diafan->_site->id);

		$this->result["view"] = 'list_category';
	}

	/**
	 * Генерирует данные о теме форума
	 *
	 * @param array $row исходные данные о теме форума
	 * @return void
	 */
	public function list_id(&$row)
	{
		$row["access_edit_delete"] = $this->diafan->_users->id && ($row["user_id"] == $this->diafan->_users->id || $this->moderator) ? true : false;
		$row["access_block"] = $this->moderator;
		$row["created"] = $this->format_date($row["created"]);
		if ($row["date_update"])
		{
			$row["date_update"] = $this->format_date($row["date_update"]);
		}
		$row["name"]          = $this->diafan->short_text($row["name"], 300);

		$info  = $this->messages_info($row["id"]);
		$row["messages"]      = $info["count"];
		$row["messages_new"]  = $info["count_new"];
		$row["last_user"] 	  = $info["last_user"];
		$row["link"]       = $this->diafan->_route->link($this->diafan->_site->id, $row["id"], "forum");
		$row["link_edit"]  = str_replace('ROUTE_END', '', $this->diafan->_route->link($this->diafan->_site->id, 0, "forum", "element", false)).'/edit'.$row["id"].ROUTE_END;
		if($row["user_id"])
		{
			$row["user"] = $this->get_author($row["user_id"]);
		}
		if($row["user_update"] == $row["user_id"] || ! $row["user_update"])
		{
			$row["user_update"] = 0;
		}
		else
		{
			$row["user_update"] = $this->get_author($row["user_update"]);
		}
		if(! empty($row["last_user"]["user_id"]))
		{
			$row["last_user"]["user"] = $this->get_author($row["last_user"]["user_id"]);
		}
		elseif(! empty($row["last_user"]["name"]))
		{
			$row["last_user"]["user"] = $row["last_user"]["name"];
		}
	}

	/**
	 * Запоминает номер темы, для которой нужно вывести информацию о сообщениях
	 * 
	 * @param integer $element_id номер темы
	 * @return void
	 */
	private function messages_prepare_info($element_id = 0)
	{
		if (! $element_id)
		{
			return;
		}
		if(isset($this->cache["info"][$element_id]))
		{
			return;
		}
		if(empty($this->cache["prepare_info"]) || ! in_array($element_id, $this->cache["prepare_info"]))
		{
			$this->cache["prepare_info"][] = $element_id;
		}
	}

	/**
	 * Генерирует данные о сообщениях в теме
	 * 
	 * @param integer $element_id номер темы
	 * @return array
	 */
	public function messages_info($element_id = 0)
	{
		if (!$element_id)
		{
			$element_id = $this->diafan->_route->show;
		}
		$this->messages_prepare_info($element_id);
		if(! empty($this->cache["prepare_info"]))
		{
			foreach ($this->cache["prepare_info"] as $id)
			{
				$this->cache["info"][$id]["count"] = 0;
				$this->cache["info"][$id]["count_new"] = 0;
				$this->cache["info"][$id]["last_user"] = 0;
			}
			
			$rows = DB::query_fetch_all("SELECT COUNT(id) as count, forum_id FROM {forum_messages} WHERE forum_id IN (%s) AND trash='0'"
				.(! $this->moderator ? " AND act='1'" : '')." GROUP BY forum_id", implode(",", $this->cache["prepare_info"]));
			foreach ($rows as $row)
			{
				$this->cache["info"][$row["forum_id"]]["count"] = $row["count"];
			}

			$rows = DB::query_fetch_all("SELECT COUNT(m.id) as count, m.forum_id FROM {forum_messages} as m"
				." INNER JOIN {forum_show} as n ON n.element_id=m.id"
				." WHERE n.user_id=%d AND m.trash='0' AND m.forum_id IN (%s)"
				.(! $this->moderator ? " AND m.act='1'" : '')." GROUP BY m.forum_id",
				$this->diafan->_users->id,
				implode(",", $this->cache["prepare_info"]));
			foreach ($rows as $row)
			{
				$this->cache["info"][$row["forum_id"]]["count_new"] = $row["count"];
			}

			foreach ($this->cache["prepare_info"] as $id)
			{
				$row = DB::query_fetch_array("SELECT created, user_id, name FROM {forum_messages} WHERE forum_id=%d AND trash='0'"
				.(! $this->moderator ? " AND act='1'" : '')." ORDER BY created DESC LIMIT 1", $id);
				if($row)
				{
					$row["created"] = $this->format_date($row["created"]);
					$this->prepare_author($row["user_id"]);
					$this->cache["info"][$id]["last_user"] = $row;
				}
			}
			unset($this->cache["prepare_info"]);
		}

		return $this->cache["info"][$element_id];
	}

	/**
	 * Генерирует список найденных сообщений
	 * 
	 * @return void
	 */
	public function list_search()
	{
		$search = '';
		if (isset($_GET["searchword"]))
		{
			if (is_array($_GET["searchword"]))
			{
				$_GET["searchword"] = '';
			}
			$search = trim($_GET["searchword"]);
		}
		$this->result["value"] = htmlspecialchars($search);

		////navigation//
		$this->diafan->_paginator->get_nav = '?searchword='.urlencode($search);
		$this->diafan->_paginator->nen = DB::query_result("SELECT COUNT(*) FROM {forum_messages} WHERE trash='0'"
			." AND act='1' AND text LIKE '%%%h%%'", $search);
		$this->diafan->_paginator->nen += DB::query_result("SELECT COUNT(*) FROM {forum} WHERE trash='0'"
			." AND act='1' AND name LIKE '%%%h%%'", $search);
		$this->result["paginator"] = $this->diafan->_paginator->get();
		////navigation///

		$this->result["rows"] = DB::query_range_fetch_all("SELECT id, created, date_update, user_id, '' as name, text, forum_id, 'message' as type FROM {forum_messages} WHERE trash='0'"
			." AND act='1' AND text LIKE '%%%h%%'"
			." UNION SELECT id, created, timeedit as date_update, user_id, name, '' as text, '' as forum_id, 'category' as type FROM {forum}"
			." WHERE trash='0' AND act='1' AND name LIKE '%%%h%%'"
			." ORDER BY created DESC",
			$search, $search, $this->diafan->_paginator->polog, $this->diafan->_paginator->nastr
		);

		$this->result["count_page"] = count($this->result["rows"]);
		
		$ids = array();

		foreach ($this->result["rows"] as &$row)
		{
			$this->prepare_author($row["user_id"]);

			if ($row["type"] == "message")
			{
				if(empty($ids) || ! in_array($row["forum_id"], $ids))
				{
					$ids[] = $row["forum_id"];
				}
				$this->diafan->_route->prepare($this->diafan->_site->id, $row["forum_id"], "forum");
			}
			else
			{
				$themes[$row["id"]] = $row["name"];
				$row["theme"] = $row["name"];
				$this->diafan->_route->prepare($this->diafan->_site->id, $row["id"], "forum");
			}
		}
		if($this->result["rows"])
		{
			$this->prepare_themes($themes, $ids);
			foreach ($this->result["rows"] as &$row)
			{
				if ($row["date_update"])
				{
					$row["created"] = $this->format_date($row["date_update"]);
				}
				else
				{
					$row["created"] = $this->format_date($row["created"]);
				}
				if ($row["type"] == "message")
				{
					$row["link"]  = $this->diafan->_route->link($this->diafan->_site->id, $row["forum_id"], "forum").'#'.$row["id"];
					$row["theme"] = ! empty($themes[$row["forum_id"]]) ? $themes[$row["forum_id"]] : '';
				}
				else
				{
					$row["link"]  = $this->diafan->_route->link($this->diafan->_site->id, $row["id"], "forum");
				}
				$row["user"] = $this->get_author($row["user_id"]);
			}
		}
		$this->diafan->_site->breadcrumb[] = array("link" => $this->diafan->_route->link($this->diafan->_site->id), "name" => $this->diafan->_site->name);

		$this->result["view_rows"] = 'rows_search';
		$this->result["show_more"] = $this->diafan->_tpl->get('show_more', 'paginator', $this->result["paginator"]);
		$this->result["paginator"] = $this->diafan->_tpl->get('get', 'paginator', $this->result["paginator"]);
		$this->result["count"]     = $this->diafan->_paginator->nen;
		$this->result["action"]    = $this->diafan->_route->link($this->diafan->_site->id);
		
		$this->result["view"] = 'list_search';
	}
	
	/**
	 * Подготавливает названия тем форума
	 *
	 * @param array $themes массив имен
	 * @param array $ids идентификаторы тем
	 * @return void
	 */
	private function prepare_themes(&$themes, $ids)
	{
		foreach ($ids as $i => $id)
		{
			if(! empty($themes[$id]))
			{
				unset($ids[$i]);
			}
		}
		if($ids)
		{
			$themes = DB::query_fetch_key_value("SELECT id, name FROM {forum} WHERE id IN (%s)", implode(",", $ids), "id", "name");
		}
	}

	/**
	 * Генерирует список новых сообщений
	 * 
	 * @return void
	 */
	public function list_new()
	{
		$this->result["view"] = 'list_new';

		if(! $this->diafan->_users->id)
		{
			$this->result["error"] = true;
			return;
		}
		////navigation//
		$this->diafan->_paginator->get_nav = '?action=news';
		$this->diafan->_paginator->nen = DB::query_result("SELECT COUNT(*) FROM {forum_messages} as f"
			." INNER JOIN {forum_show} AS s ON s.element_id=f.id"
			." WHERE f.trash='0' AND f.act='1' AND s.user_id=%d", $this->diafan->_users->id);
		$this->result["paginator"] = $this->diafan->_paginator->get();
		////navigation///

		$this->diafan->_site->titlemodule = $this->diafan->_('Непрочитанные сообщения', false);

		$this->result["rows"] = DB::query_range_fetch_all("SELECT f.id, f.created, f.date_update, f.user_id, f.text,"
			." f.forum_id FROM {forum_messages} as f"
			." INNER JOIN {forum_show} AS s ON s.element_id=f.id"
			." WHERE f.trash='0' AND f.act='1' AND s.user_id=%d"
			." ORDER BY created DESC",
			$this->diafan->_users->id, $this->diafan->_paginator->polog, $this->diafan->_paginator->nastr
		);

		foreach ($this->result["rows"] as &$row)
		{
			$this->prepare_author($row["user_id"]);
			$this->diafan->_route->prepare($this->diafan->_site->id, $row["forum_id"], "forum");
			$rows[] = $row;
			if(empty($ids) || ! in_array($row["forum_id"], $ids))
			{
				$ids[] = $row["forum_id"];
			}
		}
		if(! empty($this->result["rows"]))
		{
			$this->prepare_themes($themes, $ids);
			foreach ($this->result["rows"] as &$row)
			{
				if ($row["date_update"])
				{
					$row["created"] = $this->format_date($row["date_update"]);
				}
				else
				{
					$row["created"] = $this->format_date($row["created"]);
				}
				$row["user"] = $this->get_author($row["user_id"]);
				$row["link"]  = $this->diafan->_route->link($this->diafan->_site->id, $row["forum_id"], "forum").'#'.$row["id"];
				$row["theme"] = $themes[$row["forum_id"]];
			}
		}
		
		$this->diafan->_site->breadcrumb[] = array("link" => $this->diafan->_route->link($this->diafan->_site->id), "name" => $this->diafan->_site->name);

		$this->result["view_rows"] = 'rows_new';
		$this->result["show_more"] = $this->diafan->_tpl->get('show_more', 'paginator', $this->result["paginator"]);
		$this->result["paginator"] = $this->diafan->_tpl->get('get', 'paginator', $this->result["paginator"]);
		$this->result["count"]     = $this->diafan->_paginator->nen;
		$this->result["action"]    = $this->diafan->_route->link($this->diafan->_site->id);
	}

	/**
	 * Генерирует страницу форума
	 * 
	 * @return void
	 */
	public function id()
	{
		$row = DB::query_fetch_array("SELECT id, name, user_id, created, date_update, cat_id, close FROM {forum} WHERE id=%d AND act='1' AND trash='0' LIMIT 1", $this->diafan->_route->show);
		if (empty($row))
		{
			Custom::inc('includes/404.php');
		}

		$cat = DB::query_fetch_array("SELECT name FROM {forum_category} WHERE id=%d AND act='1' AND trash='0' LIMIT 1", $row["cat_id"]);
		if (empty($cat))
		{
			Custom::inc('includes/404.php');
		}

		if(empty($_SESSION["forum_view"][$row["id"]]))
		{
			$_SESSION["forum_view"][$row["id"]] = true;
			DB::query("UPDATE {forum} SET counter_view=counter_view+1 WHERE id=%d", $row["id"]);
		}
		$this->result["close"] = $row["close"];

		$this->diafan->_site->breadcrumb[] = array("link" => $this->diafan->_route->link($this->diafan->_site->id), "name" => $this->diafan->_site->name);
		$this->diafan->_site->breadcrumb[] = array("link" => $this->diafan->_route->link($this->diafan->_site->id, $row["cat_id"], "forum", "cat"), "name" => $cat["name"]);
		$this->diafan->_site->titlemodule = $row["name"];
		$this->diafan->_route->cat = $row["cat_id"];

		$this->result["hash"] = $this->diafan->_users->get_hash();

		$user_news = array();
		if ($this->diafan->_users->id)
		{
			$user_news = DB::query_fetch_value("SELECT element_id FROM {forum_show} WHERE user_id=%d", $this->diafan->_users->id, "element_id");
		}

		////navigation//
		$this->diafan->_paginator->nastr = $this->diafan->configmodules("nastr_messages", "forum");
		$this->diafan->_paginator->nen = DB::query_result(
			"SELECT COUNT(*) FROM {forum_messages} WHERE forum_id=%d AND trash='0' AND parent_id=0"
			.(! $this->moderator ? " AND act='1'" : ''), $this->diafan->_route->show
		);
		$this->result["paginator"] = $this->diafan->_paginator->get();
		////navigation///

		$this->result["view_rows"] = 'list_messages';
		$this->result["show_more"] = $this->diafan->_tpl->get('show_more', 'paginator', $this->result["paginator"]);
		$this->result["paginator"] = $this->diafan->_tpl->get('get', 'paginator', $this->result["paginator"]);

		$rows[0] = DB::query_range_fetch_all(
			"SELECT * FROM {forum_messages} WHERE forum_id=%d AND trash='0' AND parent_id=0"
			.(! $this->moderator ? " AND act='1'" : '')
			." ORDER BY created ASC", $this->diafan->_route->show,
			$this->diafan->_paginator->polog, $this->diafan->_paginator->nastr
		);
		$parents = array();
		foreach ($rows[0] as &$row)
		{
			$this->prepare_author($row["user_update"]);
			$this->prepare_author($row["user_id"]);
			if ($this->diafan->configmodules("attachments"))
			{
				$this->diafan->_attachments->prepare($row["id"], "forum");
			}
			$parents[] = $row["id"];
		}
		if($parents)
		{
			$rows_parent = DB::query_fetch_all("SELECT f.* FROM {forum_messages} AS f"
				." INNER JOIN {forum_messages_parents} AS p ON f.id=p.element_id"
				." WHERE f.forum_id=%d AND f.trash='0' AND p.parent_id IN (%s)"
				.(! $this->moderator ? " AND f.act='1'" : '')
				." GROUP BY f.id ORDER BY f.created ASC", $this->diafan->_route->show, implode(",", $parents));
			foreach($rows_parent as &$row)
			{
				$rows[$row["parent_id"]][] = $row;
			}
			foreach ($rows as $parent_id => &$rows_p)
			{
				foreach ($rows_p as &$row)
				{
					$this->prepare_author($row["user_update"]);
					$this->prepare_author($row["user_id"]);
					if ($this->diafan->configmodules("attachments"))
					{
						$this->diafan->_attachments->prepare($row["id"], "forum");
					}
				}
			}
		}
		foreach ($rows as $parent_id => &$rows_p)
		{
			foreach ($rows_p as &$row)
			{
				$row["access_edit_delete"] = $this->diafan->_users->id
					&& ($row["user_id"] == $this->diafan->_users->id || $this->moderator)
					&& ! $this->result["close"] ? true : false;
				$row["access_block"]       = $this->moderator ? true : false;
				$row["created"] = $this->format_date($row["created"]);
				if ($row["date_update"])
				{
					$row["date_update"] = $this->format_date($row["date_update"]);
				}
				if($row["user_update"] == $row["user_id"] || ! $row["user_update"])
				{
					$row["user_update"] = 0;
				}
				else
				{
					$row["user_update"] = $this->get_author($row["user_update"]);
				}
				if($row["user_id"])
				{
					$row["user"] = $this->get_author($row["user_id"]);
				}
				else
				{
					$row["user"] = $row["name"];
				}
				$row["attachments"] = array();
				if ($this->diafan->configmodules("attachments"))
				{
					$row["attachments"]["rows"] = $this->diafan->_attachments->get($row["id"], 'forum');
					$row["attachments"]["access"] = $row["access_edit_delete"];
					$row["attachments"]["use_animation"] = $this->diafan->configmodules("use_animation", "forum");
				}
				if ($row["show"] = in_array($row["id"], $user_news))
				{
					$user_news_id[] = $row["id"];
				}
				$row["form"] = $this->get_form($row["id"]);
				$row["hash"] = $this->result["hash"];
			}
		}
		$this->result["form"] = $this->get_form();

		if ($this->diafan->_users->id)
		{
			if(! empty($user_news_id))
			{
				DB::query("DELETE FROM {forum_show} WHERE user_id=%d AND element_id IN (%s)", $this->diafan->_users->id, implode(",", $user_news_id));
			}
		}
		$this->result["rows"] = $this->build_tree($rows);

		$this->result["view"] = 'id';
	}

	/**
	 * Формирует дерево сообщений из полученного массива
	 *
	 * @param array $rows все сообщения
	 * @param integer $parent_id номер текущего сообщения-родителя
	 * @param integer $level уровень
	 * @return string
	 */
	private function build_tree($rows, $parent_id = 0, $level = 1)
	{
		$result = array();
		$count_level = $this->diafan->configmodules("count_level", "forum");

		if($count_level && $level > $count_level)
			return $result;

		if (! empty($rows[$parent_id]))
		{
			foreach ($rows[$parent_id] as $row)
			{
				$row["children"] = $this->build_tree($rows, $row["id"], $level+1);
				if($level == $count_level)
				{
					$row["form"] = false;
				}
				$result[] = $row;
			}
		}
		return $result;
	}
	
	/*
	 * Формирует данные для формы
	 * 
	 * @param integer $count количество сообщений
	 * @param integer $parent_id номер сообщения-родителя
	 * @return array
	 */
	public function get_form($parent_id = 0)
	{
		if ($this->diafan->configmodules('only_user', 'forum') && ! $this->diafan->_users->id || $this->result["close"])
		{
			return false;
		}
		$form["field_name"] = $this->diafan->_users->id ? false : true;
		$form["parent_id"] = $parent_id;

		$form["form_tag"] = "forum_message".$parent_id;
		$fields = array('', 'message', 'name', 'attachments', 'captcha');
		$this->form_errors($form, $form["form_tag"], $fields);

		$form["captcha"] = '';
		if ($this->diafan->_captcha->configmodules('forum'))
		{
			$form["captcha"] = $this->diafan->_captcha->get($form["form_tag"], $form["error_captcha"]);
		}
		$form["premoderation"] = $this->check_premoderation('message', "forum") && ! $this->moderator;
		$form["hash"]          = $this->result["hash"] ? $this->result["hash"] : $this->diafan->_users->get_hash();
		if ($this->diafan->configmodules("attachments", "forum"))
		{
			$form["add_attachments"]       = true;
			$form["max_count_attachments"] = $this->diafan->configmodules("max_count_attachments", "forum");
			$form["attachment_extensions"] = $this->diafan->configmodules("attachment_extensions", "forum");
		}
		else
		{
			$form["add_attachments"] = false;
		}
		return $form;
	}

	/**
	 * Генерирует страницу редактирования форума
	 * 
	 * @return void
	 */
	public function edit()
	{
		//редактирование разрешено только пользователям:
		if (! $this->diafan->_users->id)
		{
			Custom::inc('includes/404.php');
		}
		$row = DB::query_fetch_array("SELECT name, user_id, cat_id, act FROM {forum} WHERE id=%d AND trash='0' LIMIT 1", $this->diafan->_route->edit);

		//автору или модератору
		if (empty($row) || ! $this->moderator && ($row["user_id"] != $this->diafan->_users->id || ! $row["act"]))
		{
			Custom::inc('includes/404.php');
		}
		$this->result = $row;
		$this->result["action"]  = 'save';
		$this->result["id"]      = $this->diafan->_route->edit;
		$this->result["hash"]    = $this->diafan->_users->get_hash();
		$this->result["captcha"] = '';
		$this->result["premoderation"] = $this->check_premoderation('theme') && ! $this->moderator;

		$fields = array('', 'name');
		$this->result['form_tag'] = 'forum_edit';
		$this->form_errors($this->result, $this->result['form_tag'], $fields);

		$parent_name = DB::query_result("SELECT name FROM {forum_category} WHERE id=%d", $row["cat_id"]);

		$this->diafan->_site->breadcrumb[] = array("link" => $this->diafan->_route->link($this->diafan->_site->id), "name" => $this->diafan->_site->name);
		$this->diafan->_site->breadcrumb[] = array("link" => $this->diafan->_route->link($this->diafan->_site->id, $row["cat_id"], 'forum', 'cat'), "name" => $parent_name);
		$this->diafan->_site->titlemodule = $this->diafan->_('редакция', false).': '.$row["name"];

		$this->result["view"] = 'edit';
	}

	/**
	 * Генерирует страницу добавления форума
	 * 
	 * @return void
	 */
	public function add()
	{
		if (! $this->diafan->_route->cat)
		{
			Custom::inc('includes/404.php');
		}
		$this->result["name"]   = '';
		$this->result["action"] = 'savenew';
		$this->result["id"]     = '';
		$this->result["cat_id"] = $this->diafan->_route->cat;
		$this->result["hash"]      = $this->diafan->_users->get_hash();

		$this->result["premoderation"] = $this->check_premoderation('theme') && ! $this->moderator;
		$this->result["captcha"] = '';

		$fields = array('', 'name', 'user_name', 'message', 'attachments', 'captcha');
		$this->result['form_tag'] = 'forum_add';
		$this->form_errors($this->result, $this->result['form_tag'], $fields);

		$this->result["field_user_name"] = $this->diafan->_users->id ? false : true;

		$this->result["field_message"] = true;


		if ($this->diafan->configmodules("attachments", "forum"))
		{
			$this->result["field_attachments"]       = true;
			$this->result["max_count_attachments"] = $this->diafan->configmodules("max_count_attachments", "forum");
			$this->result["attachment_extensions"] = $this->diafan->configmodules("attachment_extensions", "forum");
		}

		//доступ на добавление только для зарегистрированных
		if ($this->diafan->configmodules("only_user") && ! $this->diafan->_users->id)
		{
			Custom::inc('includes/403.php');
		}
		//доступ на добавление по капче
		elseif ($this->diafan->_captcha->configmodules("forum") && !$this->diafan->_users->id)
		{
			$this->result["captcha"] = $this->diafan->_captcha->get($this->result['form_tag'], $this->result['error_captcha']);
		}
		$parent_name = DB::query_result("SELECT name FROM {forum_category} WHERE id=%d", $this->diafan->_route->cat);

		$this->diafan->_site->breadcrumb[] = array("link" => $this->diafan->_route->link($this->diafan->_site->id), "name" => $this->diafan->_site->name);
		$this->diafan->_site->breadcrumb[] = array("link" => $this->diafan->_route->link($this->diafan->_site->id, $this->diafan->_route->cat, 'forum', 'cat'), "name" => $parent_name);
		$this->diafan->_site->name = $this->diafan->_('Новая тема для обсуждения', false);

		$this->result["view"] = 'edit';
	}

	/**
	 * Шаблонная функция: блок тем
	 *
	 * @param integer $count количество тем
	 * @param array $block_ids блоки
	 * @param array $cat_ids категории
	 * @param string $sort сортировка date - по дате, rand - случайно, keywords - темы, похожие по названию для текущей страницы
	 * @return array
	 */
	public function show_block($count, $block_ids, $cat_ids, $sort)
	{
		if(! $site_id = $this->diafan->_route->id_module("forum", 0, false))
		{
			return false;
		}
		$where = $this->get_block_where($block_ids, $cat_ids);
		if($this->diafan->_site->module == 'forum' && $this->diafan->_route->show)
		{
			$where .= " AND f.id<>".$this->diafan->_route->show;
		}

		if($sort == 'keywords')
		{
			Custom::inc('includes/searchwords.php');
			$searchwords = new Searchwords();
			$searchwords->max_length = $this->diafan->configmodules("max_length", "search");
			$names = $searchwords->prepare($title);

			if(empty($names))
			{
				return false;
			}

			$where_search = "";
			foreach ($names as $name)
			{
				$where_search .= ($where_search ? " OR ": "")."LOWER(f.name) LIKE '%%".$name."%%'";
			}
			$where .= " AND (".$where_search.")";
		}

		if ($sort == "rand")
		{
			$max_count = DB::query_result("SELECT COUNT(*) FROM {forum} AS f WHERE f.trash='0' AND f.act='1'".$where);
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
		$result["rows"] = array();

		foreach ($rands as $rand)
		{
			switch($sort)
			{
				case "rand":
					$order = '';
					break;

				default:
					$order = " ORDER BY f.timeedit DESC, f.created DESC";
					break;
			}

			$rows = DB::query_range_fetch_all(
				"SELECT f.id, f.name FROM {forum} AS f WHERE f.trash='0' AND f.act='1'"
				.$where.$order,
				$sort == "rand" ? $rand : 0,
				$sort == "rand" ? 1     : $count
			);
			$result["rows"] = array_merge($result["rows"], $rows);
		}

		foreach ($result["rows"] as &$row)
		{
			$this->diafan->_route->prepare($site_id, $row["id"], "forum");
		}
		foreach ($result["rows"] as &$row)
		{
			$row["link"] = $this->diafan->_route->link($site_id, $row["id"], "forum");
		}

		$result["view_rows"] = 'rows_block';

		return $result;
	}

	/**
	 * Шаблонная функция: блок сообщений 
	 *
	 * @param integer $count количество сообщений
	 * @param array $block_ids блоки
	 * @param array $cat_ids категории
	 * @return array
	 */
	public function show_block_messages($count, $block_ids, $cat_ids)
	{
		if(! $site_id = $this->diafan->_route->id_module("forum", 0, false))
		{
			return false;
		}
		$where = $this->get_block_where($block_ids, $cat_ids);

		$result["rows"] = DB::query_range_fetch_all("SELECT m.*, f.name AS theme FROM {forum_messages} AS m INNER JOIN {forum} AS f ON f.id=m.forum_id"
		." WHERE m.trash='0' AND m.act='1'".$where." ORDER BY m.created DESC", 0, $count);
		foreach ($result["rows"] as &$row)
		{
			$row["text"] = $this->diafan->short_text($row["text"], 300);
			$this->diafan->_route->prepare($site_id, $row["forum_id"], "forum");
			if ($this->diafan->configmodules("attachments"))
			{
				$this->diafan->_attachments->prepare($row["id"], "forum");
			}
			$this->prepare_author($row["user_id"]);
			$this->prepare_author($row["user_update"]);
		}
		foreach ($result["rows"] as &$row)
		{
			$row["created"] = $this->format_date($row["created"]);
			if ($row["date_update"])
			{
				$row["date_update"] = $this->format_date($row["date_update"]);
			}
			if($row["user_update"] == $row["user_id"] || ! $row["user_update"])
			{
				$row["user_update"] = 0;
			}
			else
			{
				$row["user_update"] = $this->get_author($row["user_update"]);
			}
			if($row["user_id"])
			{
				$row["user"] = $this->get_author($row["user_id"]);
			}
			else
			{
				$row["user"] = $row["name"];
			}

			$row["attachments"] = array();
			if ($this->diafan->configmodules("attachments", "forum"))
			{
				$row["attachments"]["rows"] = $this->diafan->_attachments->get($row["id"], 'forum');
				$row["attachments"]["access"] = false;
				$row["attachments"]["use_animation"] = $this->diafan->configmodules("use_animation", "forum");
			}
			$row["link"] = $this->diafan->_route->link($site_id, $row["forum_id"], "forum").'#'.$row["id"];
		}

		$result["view_rows"] = 'rows_block_messages';

		return $result;
	}

	/**
	 * Обрабатывает атрибуты шаблонной функции "блоки" и "категории" и формирует условие для SQL-запроса
	 *
	 * @param array $cat_ids категории
	 * @param array $block_ids блоки
	 * @return string
	 */
	private function get_block_where($block_ids, $cat_ids)
	{
		$cats = array();
		$minus_cats = array();
		if($cat_ids)
		{
			if(is_array($cat_ids))
			{
				foreach($cat_ids as $cat_id);
				{
					$cat_id = trim($cat_id);
					if(substr($cat_id, 0, 1) == '-')
					{
						$cat_id = intval(substr($cat_id, 1));
						if($cat_id)
						{
							$minus_cats[] = $cat_id;
						}
					}
					else
					{
						$cat_id = intval($cat_id);
						if($cat_id)
						{
							$cats[] = $cat_id;
						}
					}
				}
			}
			elseif($cat_ids === 'current')
			{
				$cats[] = $this->diafan->_route->cat;
			}
		}
		if($block_ids)
		{
			$blocks = array();
			$minus_blocks = array();
			foreach($block_ids as &$block_id);
			{
				foreach($block_ids as $block_id);
				{
					$block_id = trim($block_id);
					if(substr($block_id, 0, 1) == '-')
					{
						$block_id = intval(substr($block_id, 1));
						if($block_id)
						{
							$minus_blocks[] = $block_id;
						}
					}
					else
					{
						$block_id = intval($block_id);
						if($block_id)
						{
							$blocks[] = $block_id;
						}
					}
				}
			}
			if($blocks)
			{
				$blocks_cats = DB::query_result_fetch_value("SELECT id FROM {forum_category}"
					." WHERE block_id IN (".implode(",", $blocks).") AND trash='0' AND act='1'", "id");
				$cats = array_merge($cats, $blocks_cats);
			}
			if($minus_blocks)
			{
				$blocks_cats = DB::query_result_fetch_value("SELECT id FROM {forum_category}"
					." WHERE block_id IN (".implode(",", $minus_blocks).") AND trash='0' AND act='1'", "id");
				$minus_cats = array_merge($minus_cats, $blocks_cats);
			}
		}
		if($cats && $minus_cats)
		{
			foreach($cats as &$cat_id)
			{
				if(in_array($cat_id, $minus_cats))
				{
					if(count($cats) == 1)
					{
						$cats = array();
					}
					else
					{
						unset($cat_id);
					}
				}
			}
		}
		$where = "";
		if($cats)
		{
			$where .= " AND f.cat_id IN (".implode(",", $cats).")";
		}
		elseif($minus_cats)
		{
			$where .= " AND f.cat_id NOT IN (".implode(",", $minus_cats).")";
		}
		
		return $where;
	}

	/**
	 * Проверяет подключена ли предмодерация в настройках модуля
	 * 
	 * @param string $type тип: theme - темы, message - сообщения
	 * @return boolean
	 */
	public function check_premoderation($type)
	{
		if($this->diafan->configmodules('premoderation_'.$type, 'forum') && $this->diafan->configmodules('premoderation_'.$type, 'forum') === '1')
		{
			return true;
		}
		if ($this->diafan->configmodules('premoderation_'.$type, 'forum') && in_array($this->diafan->_users->role_id, unserialize($this->diafan->configmodules('premoderation_'.$type, 'forum'))))
		{
			return true;
		}
		return false;
	}
}
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
 * Move_admin
 *
 * Сортировка, перемещение
 */
class Move_admin extends Diafan
{
	/**
	 * @var integer количество строк, выводимых на странице
	 */
	public $nastr = 30;

	/**
	 * Сортирует элементы
	 *
	 * @return void
	 */
	public function move()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (!$this->diafan->_users->checked)
		{
			echo "{error: 'HASH'}";
			return;
		}

		//проверка прав на сортировку
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			echo "{error: 'ROLES'}";
			return;
		}

		//проверка присланы ли данные
		if (empty($_POST['sort']))
		{
			echo "{error: 'SORT'}";
			return;
		}

		$result["status"] = true;
		$result["hash"] = $this->diafan->_users->get_hash();

		asort($_POST['sort']);
		// коэффициент прибавления. если одинаковые значения сортировки, то увеличиваем коэффициент прибавления
		$k = 0; $current_sort = 0;
		foreach ($_POST['sort'] as $id => $sort)
		{
			if($sort == $current_sort)
			{
				$k++;
			}
			$current_sort = $sort;
			$sort += $k;
			if (! DB::query("UPDATE {".$this->diafan->table."} SET sort=%d WHERE id=%d", $sort, $id))
			{
				$result["status"] = false;
				break;
			}
		}

		$this->diafan->_cache->delete("", $this->diafan->_admin->module);

		Custom::inc('plugins/json.php');
		echo to_json($result);
	}

	/**
	 * Обработчик перетаскивания на страницу
	 *
	 * @return void
	 */
	public function move_page()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			echo "{error: 'HASH'}";
			return;
		}
		$result["hash"] = $this->diafan->_users->get_hash();

		//проверка прав на сортировку
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			echo "{error: 'ROLES'}";
			return false;
		}

		//проверяем входящие переменные
		$item = DB::query_fetch_array("SELECT * FROM {".$this->diafan->table."} WHERE id=%d LIMIT 1", $_POST['id']);

		if(empty($item)) 
		{
			echo "{error: 'EMPTY'}";
			return;
		}

		$page = $this->diafan->filter($_POST, 'int', 'page');
		if ($this->diafan->_users->admin_nastr)
		{
			$this->diafan->nastr = $this->diafan->_users->admin_nastr;
		}
		if (! $this->diafan->nastr)
		{
			$this->diafan->nastr = 30;
		}
		if ($page)
		{
			$polog = ( ($page - 1) * $this->diafan->nastr )  ;
		}
		else
		{
			$polog = 0;
		}

		$items = $this->sql_query(( isset($item['parent_id']) ? $item['parent_id'] : 0 ) , $polog);
		$first_item = $items[0];

		$lang_act = ($this->diafan->variable_multilang("act") ? _LANG : '');
		if($first_item["sort"] < $item["sort"])
		{
			DB::query("UPDATE {".$this->diafan->table."} SET sort=sort+1 WHERE sort>=%d AND id<>%d"
			.($this->diafan->variable_list('plus') ? " AND parent_id='".$item["parent_id"]."'" : '')
			.($this->diafan->config("element") && $this->diafan->_route->cat ? ' AND cat_id="'.$item["cat_id"].'"' : '')
			.($this->diafan->config("element_site") && $this->diafan->_route->site ?
			" AND site_id='".$this->diafan->_route->site."'" : '' )
			.($this->diafan->variable_list('actions', 'trash') ? " AND trash='0'" : '')
			.($this->diafan->is_variable("act") ? " AND act".$lang_act."='1'" : ''),
			$first_item["sort"], $item['id']);
			$save_sort = $first_item["sort"];
		}
		else
		{
			DB::query("UPDATE {".$this->diafan->table."} SET sort=sort-1 WHERE sort>%d AND sort<=%d AND id<>%d"
			.($this->diafan->variable_list('plus') ? " AND parent_id='".$item["parent_id"]."'" : '')
			.($this->diafan->config("element") && $this->diafan->_route->cat ? ' AND cat_id="'.$item["cat_id"].'"' : '')
			.($this->diafan->config("element_site") && $this->diafan->_route->site ?
			" AND site_id='".$this->diafan->_route->site."'" : '' )
			.($this->diafan->variable_list('actions', 'trash') ? " AND trash='0'" : '')
			.($this->diafan->is_variable("act") ? " AND act".$lang_act."='1'" : ''),
			$item['sort'], $first_item["sort"], $item['id']);
			$save_sort = $first_item["sort"];
		}
		DB::query("UPDATE {".$this->diafan->table."} SET sort=%d WHERE id=%d LIMIT 1", $save_sort, $item['id']);
		$result['success'] = true;
		$result['status'] = true;

		Custom::inc('plugins/json.php');
		echo to_json($result);
	}

	/**
	 * Формирует SQL-запрос для списка элементов
	 *
	 * @param integer $parent_id родитель
	 * @return resource
	 */
	private function sql_query($parent_id, $polog)
	{
		$lang_act = $this->diafan->variable_multilang("act") ? _LANG : '';

		return DB::query_fetch_all("SELECT e.[name], e.id, e.sort FROM {".$this->diafan->table."} as e".(
		$this->diafan->config("element_multiple") && $this->diafan->_route->cat ?
		" INNER JOIN {".$this->diafan->table."_category_rel} AS c ON e.id=c.element_id"
		." AND e.id=c.element_id AND c.cat_id='".$this->diafan->_route->cat."'" : '' )." WHERE 1=1"
		.($this->diafan->variable_list('plus') ? " AND e.parent_id=".$parent_id : '' ).(
		$this->diafan->config("element") && !$this->diafan->config("element_multiple") && $this->diafan->_route->cat ?
		" AND e.cat_id='".$this->diafan->_route->cat."'" : '' )
		. ($this->diafan->config("element_site") && $this->diafan->_route->site ? " AND e.site_id='".$this->diafan->_route->site."'" : '' )
		.($this->diafan->variable_list('actions', 'trash') ? " AND e.trash='0'" : '' )
		.($this->diafan->config("element_multiple") && $this->diafan->_route->cat ? " GROUP BY e.id" : '' )." ORDER BY "
		.($this->diafan->variable_list("prior") ? 'e.prior DESC, ' : '' )
		.($this->diafan->variable_list("created") && ! $this->diafan->variable_list('sort') ? 'e.created DESC, ' : '' )
		.($this->diafan->variable_list('actions', 'act') ? 'e.act'.$lang_act.' DESC, ' : '' )
		.($this->diafan->variable_list('sort') ?
		 ($this->diafan->variable_list('sort', 'desc') ? 'e.sort DESC, e.id DESC' : 'e.sort ASC, e.id ASC')
		 : 'e.id DESC' )
		.' LIMIT '.$polog. ', 1');
	}

	/**
	 * Обработчик перемещения вложенности
	 *
	 * @return void
	 */
	public function move_parent()
	{
		if (! $this->diafan->_users->checked)
		{
			echo "{error: 'HASH'}";
			return;
		}

		//проверка прав на сортировку
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			echo "{error: 'ROLES'}";
			return;
		}

		if (empty( $_POST['id'] ))
		{
			echo "{error: 'EMPTY'}";
			return;
		}

		$id = $this->diafan->filter($_POST, 'int', 'id');
		$pid = $this->diafan->filter($_POST, 'int', 'parent_id');

		$result["status"] = false;

		$oldrow = DB::query_fetch_array("SELECT id, parent_id FROM {".$this->diafan->table."} WHERE id=%d LIMIT 1", $id);

		if ($oldrow['parent_id'] != $pid)
		{
			$children = $this->diafan->get_children($id, $this->diafan->table);
			$children[] = $id;
			$count_children = count($children);

			// если родитель был, у текущего элемента и его детей удаляем всех старых родителей, вышего текущего элемента
			// у старых родителей выше текущего элемента уменьшаем количество детей

			$old_parents = $this->diafan->get_parents($id, $this->diafan->table);
			foreach ($old_parents as $parent_id)
			{
				DB::query("DELETE FROM {".$this->diafan->table."_parents} WHERE element_id IN (%h) AND parent_id=%d", implode(",", $children), $parent_id);
				DB::query("UPDATE {".$this->diafan->table."} SET count_children=count_children-%d WHERE id=%d", $count_children, $parent_id);
			}

			$parents = $this->diafan->get_parents($pid, $this->diafan->table);
			$parents[] = $pid;
			foreach ($parents as $parent_id)
			{
				DB::query("UPDATE {".$this->diafan->table."} SET count_children=count_children+%d WHERE id=%d", $count_children, $parent_id);
				foreach ($children as $child)
				{
					DB::query("INSERT INTO {".$this->diafan->table."_parents} (element_id, parent_id) VALUES (%d, %d)", $child, $parent_id);
				}
			}

			DB::query("UPDATE {%s} SET parent_id=%d WHERE id=%d LIMIT 1", $this->diafan->table, $pid, $id);

			$this->diafan->_cache->delete("", $this->diafan->_admin->module);
			$result["status"] = true;
		}

		$result["hash"] = $this->diafan->_users->get_hash();

		Custom::inc('plugins/json.php');
		echo to_json($result);
	}
}
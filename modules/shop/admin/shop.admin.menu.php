<?php
/**
 * Карта ссылок для модуля «Меню на сайте»
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
 * Shop_admin_menu
 */
class Shop_admin_menu extends Diafan
{
	/**
	 * Получает количество элементов, которые можно вывести в меню для страницы сайта
	 * 
	 * @param integer $site_id номер страницы сайта
	 * @return boolean integer
	 */
	public function count($site_id)
	{
		if ($this->diafan->configmodules("cat", 'shop', $site_id))
		{
			$count = DB::query_result("SELECT COUNT(*) FROM {shop_category} WHERE [act]='1' AND trash='0' AND site_id=%d", $site_id);
		}
		else
		{
			$count = DB::query_result("SELECT COUNT(*) FROM {shop} WHERE [act]='1' AND trash='0' AND site_id=%d", $site_id);
		}
		return $count;
	}

	/**
	 * Получает список элементов, которые можно вывести в меню для страницы сайта
	 * 
	 * @param integer $site_id номер страницы сайта
	 * @param integer $parent_id родитель
	 * @return array
	 */
	public function list_($site_id, $parent_id)
	{
		$rows = array();
		if ($this->diafan->configmodules("cat", 'shop', $site_id))
		{
			$rs = DB::query_fetch_all("SELECT id, [name], count_children FROM {shop_category} WHERE [act]='1' AND trash='0' AND parent_id=%d AND site_id=%d ORDER BY sort ASC", $parent_id, $site_id);
			foreach ($rs as $row)
			{
				$new = array();
				$new["element_id"] = $row["id"];
				$new["element_type"] = 'cat';
				$new["name"] = $row["name"];
				if (! $new["count"] = $row["count_children"])
				{
					$new["count"] = DB::query_result("SELECT COUNT(DISTINCT n.id) FROM {shop} as n INNER JOIN {shop_category_rel} as c ON c.element_id=n.id WHERE c.cat_id=%d AND n.[act]='1' AND n.trash='0'", $row["id"]);
				}
				$rows[] = $new;
			}
			if ($parent_id)
			{
				$rs = DB::query_fetch_all("SELECT n.id, n.[name], n.cat_id FROM {shop} as n INNER JOIN {shop_category_rel} as c ON c.element_id=n.id WHERE c.cat_id=%d AND n.[act]='1' AND n.trash='0' GROUP BY n.id ORDER BY n.sort DESC", $parent_id);
				if ($rows && $rs)
				{
					$rows[] = array("hr" => true);
				}
				foreach ($rs as $row)
				{
					$new = array();
					$new["count"] = 0;
					$new["element_id"] = $row["id"];
					$new["element_type"] = 'element';
					$new["name"] = $row["name"];
					$rows[] = $new;
				}
			}
		}
		else
		{
			$rs = DB::query_fetch_all("SELECT id, [name] FROM {shop} WHERE [act]='1' AND trash='0' AND site_id=%d ORDER BY sort DESC", $site_id);
			if ($rows && $rs)
			{
				$rows[] = array("hr" => true);
			}
			foreach ($rs as $row)
			{
				$new = array();
				$new["count"] = 0;
				$new["element_id"] = $row["id"];
				$new["element_type"] = 'element';
				$new["name"] = $row["name"];
				$rows[] = $new;
			}
		}
		if(! $parent_id)
		{
			$rows_param = DB::query_fetch_all("SELECT id, [name] FROM {shop_param} WHERE page='1' AND trash='0' ORDER BY sort ASC", $site_id);
			if ($rows && $rows_param)
			{
				$rows[] = array("hr" => true);
			}
			foreach ($rows_param as $row_param)
			{
				$rs = DB::query_fetch_all("SELECT id, [name] FROM {shop_param_select} WHERE param_id=%d AND trash='0' ORDER BY sort ASC", $row_param["id"]);
				foreach ($rs as $row)
				{
					$new = array();
					$new["count"] = 0;
					$new["element_id"] = $row["id"];
					$new["element_type"] = 'param';
					$new["name"] = $row_param["name"].': '.$row["name"];
					$rows[] = $new;
				}
			}
			$rows_brand= DB::query_fetch_all("SELECT id, [name] FROM {shop_brand} WHERE trash='0' ORDER BY sort ASC", $site_id);
			if ($rows && $rows_brand)
			{
				$rows[] = array("hr" => true);
			}
			foreach ($rows_brand as $row)
			{
				$new = array();
				$new["count"] = 0;
				$new["element_id"] = $row["id"];
				$new["element_type"] = 'brand';
				$new["name"] = $row["name"];
				$rows[] = $new;
			}
		}
		return $rows;
	}
}
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
 * Files_admin_menu
 */
class Files_admin_menu extends Diafan
{
	/**
	 * Получает количество элементов, которые можно вывести в меню для страницы сайта
	 *
	 * @param integer $site_id номер страницы сайта
	 * @return boolean integer
	 */
	public function count($site_id)
	{
		if ($this->diafan->configmodules("cat", 'files', $site_id))
		{
			$count = DB::query_result("SELECT COUNT(*) FROM {files_category} WHERE [act]='1' AND trash='0' AND site_id=%d", $site_id);
		}
		else
		{
			$count = DB::query_result("SELECT COUNT(*) FROM {files} WHERE [act]='1' AND trash='0' AND site_id=%d", $site_id);
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
		$rows = array ();
		if ($this->diafan->configmodules("cat", 'files', $site_id))
		{
			$rs = DB::query_fetch_all("SELECT id, [name], count_children FROM {files_category} WHERE [act]='1' AND trash='0' AND parent_id=%d AND site_id=%d ORDER BY sort ASC", $parent_id, $site_id);
			foreach ($rs as $row)
			{
				$new = array ();
				$new["element_id"] = $row["id"];
				$new["element_type"] = "cat";
				$new["name"] = $row["name"];
				if (!$new["count"] = $row["count_children"])
				{
					$new["count"] = DB::query_result("SELECT COUNT(DISTINCT n.id) FROM {files} as n INNER JOIN {files_category_rel} as c ON c.element_id=n.id WHERE c.cat_id=%d AND n.[act]='1' AND n.trash='0'", $row["id"]);
				}
				$rows[] = $new;
			}
			if ($parent_id)
			{
				$rs = DB::query_fetch_all("SELECT n.id, n.[name], n.cat_id FROM {files} as n INNER JOIN {files_category_rel} as c ON c.element_id=n.id WHERE c.cat_id=%d AND n.[act]='1' AND n.trash='0' GROUP BY n.id ORDER BY n.sort DESC", $parent_id);
				if ($rows && $rs)
				{
					$rows[] = array ( "hr" => true );
				}
				foreach ($rs as $row)
				{
					$new = array ();
					$new["count"] = 0;
					$new["element_type"] = "element";
					$new["element_id"] = $row["id"];
					$new["name"] = $row["name"];
					$rows[] = $new;
				}
			}
		}
		else
		{
			$rs = DB::query_fetch_all("SELECT id, [name] FROM {files} WHERE [act]='1' AND trash='0' AND site_id=%d ORDER BY sort DESC", $site_id);
			if ($rows && $rs)
			{
				$rows[] = array ( "hr" => true );
			}
			foreach ($rs as $row)
			{
				$new = array ();
				$new["count"] = 0;
				$new["element_type"] = "element";
				$new["element_id"] = $row["id"];
				$new["name"] = $row["name"];
				$rows[] = $new;
			}
		}
		return $rows;
	}
}
<?php
/**
 * Количество новых и неактивных объявлений для меню административной панели
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
 * Ab_admin_count
 */
class Ab_admin_count extends Diafan
{
	/**
	 * Возвращает количество новых и неактивных объявлений для меню административной панели
	 * @param integer $site_id страница сайта, к которой прикреплен модуль
	 * @return integer
	 */
	public function count($site_id)
	{
		$rows = DB::query_fetch_all("SELECT id, [act], readed FROM {ab} WHERE (readed='0' OR [act]='0') AND trash='0'".($site_id ? " AND site_id=".$site_id : ""));
		if($rows)
		{
			$count1 = 0;
			$count2 = 0;
			foreach($rows as $row)
			{
				if(! $row["act"])
				{
					$count2++;
				}
				if(! $row["readed"])
				{
					$count1++;
				}
			}
			return $count1."/".$count2;
		}
		else
		{
			return 0;
		}
	}
}
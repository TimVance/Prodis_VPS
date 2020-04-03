<?php
/**
 * Обработка Ajax-запросов
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

class News_action extends Action
{
	/**
	 * Обрабатывает полученные данные из формы
	 * 
	 * @return void
	 */
	public function init()
	{
		if(empty($_POST["arrow"]) || ! in_array($_POST["arrow"], array("prev", "next")) || empty($_POST["site_id"]) || empty($_POST["year"]) || empty($_POST["month"]))
		{
			return;
		}
		$_POST["year"] = intval($_POST["year"]);
		$_POST["month"] = intval($_POST["month"]);
		if($_POST["year"] < 1970 || $_POST["year"] > 2100 || $_POST["month"] < 0 || $_POST["month"] > 12)
		{
			return;
		}
		if($_POST["arrow"] == "prev")
		{
			if($_POST["month"] == 1)
			{
				$_POST["year"]--;
				$_POST["month"] = 12;
			}
			else
			{
				$_POST["month"]--;
			}
		}
		else
		{
			if($_POST["month"] == 12)	
			{
				$_POST["year"]++;
				$_POST["month"] = 1;
			}
			else
			{
				$_POST["month"]++;
			}
		}
		$template = preg_replace('/[^0-9a-z_]+/', '', $_POST["template"]);

		$result = $this->model->show_calendar("day", intval($_POST["site_id"]), intval($_POST["cat_id"]), $template, $_POST["month"], $_POST["year"]);
		if (! $result)
		{
			return;
		}

		echo $this->diafan->_tpl->get('show_calendar_day', 'news', $result, $template);
	}
}
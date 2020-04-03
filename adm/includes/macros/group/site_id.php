<?php
/**
 * Макрос для групповой операции: редактирование раздела
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2019 OOO «Диафан» (http://www.diafan.ru/)
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
 * Group_site_id
 */
class Group_site_id extends Diafan
{
	/**
	 * Возвращает настройки
	 *
	 * @param string $value последнее выбранное групповое действие
	 * @return array|false
	 */
	public function show($value)
	{
		if (! $this->diafan->not_empty_site || ! count($this->diafan->sites) > 1)
		{
			return false;
		}

		$config = array(
			'name' => 'Переместить в раздел',
		);

		$config['html'] = '<select name="site_id">';
		$config['html'] .= $this->diafan->get_options(array("0" => $this->diafan->sites), $this->diafan->sites, array($this->diafan->_route->site))
		.'</select>';
		return $config;
	}

	/**
	 * Отправляет письма пользователям о брошенных корзинах
	 *
	 * @return void
	 */
	public function action()
	{
		if (empty( $_POST['ids']) || empty($_POST["site_id"]))
		{
			return;
		}

		$site_id = $this->diafan->filter($_POST, 'int', 'site_id');
		if(! $site_id || ! $this->diafan->config("element_site"))
		{
			return;
		}
		$ids = array();
		foreach ($_POST['ids'] as $id)
		{
			$id = intval($id);
			if($id)
			{
				$ids[] = $id;

				if($this->diafan->variable_list('plus'))
				{
					$children = $this->diafan->get_children($id, $this->diafan->table);
					$ids = array_merge($ids, $children);
				}
			}
		}
		if($ids)
		{
			$news_site = array();
			$rows = DB::query_fetch_all("SELECT id, site_id FROM {%s} WHERE id IN (%s)", $this->diafan->table, implode(",", $ids));
			foreach($rows as $row)
			{
				if($row["site_id"] != $site)
				{
					$news_site[] = $row["id"];
				}
			}
			if($news_site)
			{
				DB::query("UPDATE {%s} SET site_id=%d".($this->diafan->config('element') ? ', cat_id=0' : '')." WHERE id IN (%s)", $this->diafan->table, $site_id, implode(",", $ids));
			}
			if($this->diafan->config('category'))
			{
				DB::query("UPDATE {%h} SET site_id=%d WHERE cat_id IN (%h)", str_replace('_category', '', $this->diafan->table), $site_id, implode(",", $ids));
			}
		}
		$this->result["status"] = true;
	}
}
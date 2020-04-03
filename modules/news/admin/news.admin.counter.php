<?php
/**
 * Статистика просмотров
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
 * News_admin_counter
 */
class News_admin_counter extends Frame_admin
{
	/**
	 * @var array поля для фильтра
	 */
	public $variables_filter = array (
		'cat_id' => array(
			'type' => 'select',
			'name' => 'Искать по категории',
		),
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		if ($this->diafan->is_action("edit"))
		{
			Custom::inc('includes/404.php');
		}

		if ($this->diafan->configmodules("cat", "news"))
		{
			$this->diafan->categories = DB::query_fetch_all(
				"SELECT id, [name], parent_id FROM {news_category} WHERE trash='0'"
				." ORDER BY sort ASC, id ASC"
			);
		}
	}

	/**
	 * Выводит статистику просмотров
	 * @return void
	 */
	public function show()
	{
		$where = "";
		$inner = "";
		if (! empty($_GET["filter_cat_id"]))
		{
			$inner = " INNER JOIN {news_category_rel} AS r ON r.element_id=s.id";
			$where = " AND r.cat_id=".$this->diafan->filter($_GET, "integer", "filter_cat_id");
		}

		$this->diafan->_paginator->navlink = 'news/counter/';
		$this->diafan->_paginator->get_nav = $this->diafan->get_nav;
		$this->diafan->_paginator->nen     = DB::query_result("SELECT COUNT(DISTINCT s.id) FROM {news} AS s".$inner." WHERE s.trash='0'".$where);
		$result["links"] = $this->diafan->_paginator->get();

		//забираем все новости, удовлетворяющие фильтру
		$result["rows"] = DB::query_range_fetch_all("SELECT s.id, s.[name], s.[act], cn.count_view FROM {news} AS s
		LEFT JOIN {news_counter} AS cn ON cn.element_id=s.id
		".$inner."
		WHERE s.trash='0'".$where." GROUP BY s.id ORDER BY cn.count_view DESC",
		$this->diafan->_paginator->polog, $this->diafan->_paginator->nastr);
		$this->template($result);
	}

	/**
	 * Шаблон вывода
	 * @return boolean true
	 */
	public function template($result)
	{
		if(! $this->diafan->configmodules("counter", "news"))
		{
			echo '<div class="error">'.$this->diafan->_('Подключите счетчик просмотров в настройках модуля.').'</div>';
		}
		echo '<form action="" method="POST">
		<input type="hidden" name="check_hash_user" value="'.$this->diafan->_users->get_hash().'">
		<input type="hidden" name="action" value="">
		<input type="hidden" name="id" value="">
		<input type="hidden" name="module" value="">';
		$filter = $this->diafan->get_filter();
		if($filter)
		{
			echo '<div class="content__left">';
		}
		$paginator = '';
		if($result["links"])
		{
			$paginator = '<div class="paginator">'.$this->diafan->_tpl->get('get_admin', 'paginator', $result["links"]);
			$paginator .= '<div class="paginator__unit">
				'.$this->diafan->_('Показывать на странице').':
				<input name="nastr" type="text" value="'.$this->diafan->_paginator->nastr.'">
				<button type="button" class="btn btn_blue btn_small change_nastr">'.$this->diafan->_('ОК').'</button>
			</div>';
			$paginator .= '</div>';
		}
		echo $paginator;

		echo '
		<ul class="list list_stat do_auto_width">
			<li class="item item_heading">
				<div class="item__th">'.$this->diafan->_('Название').'</div>
				<div class="item__th">'.$this->diafan->_('Просмотров').'</div>
			</li>';
		foreach ($result["rows"] as $row)
		{
			echo '
			<li class="item'.(! $row["act"] ? ' item_disable' : '').'">
			<div class="item__in">
			<div><a href="'.BASE_PATH_HREF.'news/edit'.$row["id"].'/">'.($row["name"] ? $row["name"] : $row["id"]).'</a></div>
			<div>'.($row["count_view"] ? $row["count_view"] : 0).'</div>
			</div></li>';
		}
		
		echo '</ul>';
		echo $paginator;
		if($filter)
		{
			echo '</div>';
		}
		echo '</form>';
		echo $filter;
	}
}
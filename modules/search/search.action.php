<?php
/**
 * Обрабатывает полученные данные из формы
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

class Search_action extends Action
{
	/**
	 * Поиск товаров
	 * 
	 * @return void
	 */
	public function init()
	{
		$_REQUEST["searchword"] = (! empty($_POST["searchword"]) ? $_POST["searchword"] : '');
		$this->model->show_module();
		$this->model->result["ajax"] = true;
		$this->result["data"] = array('.search_result' => $this->diafan->_tpl->get($this->model->result["view"], 'search', $this->model->result));
		$this->result["empty"] = empty($this->model->result["rows"]);
	}
}
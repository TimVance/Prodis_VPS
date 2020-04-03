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

class Mistakes_action extends Action
{
	/**
	 * Обрабатывает полученные данные из формы
	 * 
	 * @return void
	 */
	public function init()
	{
		if (! empty($_POST["url"]))
		{
			DB::query("INSERT INTO {mistakes} (created, `url`, selected_text, `comment`) VALUES (%d, '%h', '%h', '%h')", time(), $_POST['url'], $_POST['selected_text'], $_POST['comment']);
			$this->result["result"] = 'success';
			$this->result["data"] = array(array("form" => $this->diafan->_('Спасибо! В ближайшее время мы обработаем ваш запрос.', false)));
		}
	}
}
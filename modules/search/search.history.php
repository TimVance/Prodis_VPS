<?php
/**
 * Экспорт истории поиска
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */
if ( ! defined('DIAFAN'))
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
 * Search_history
 */
class Search_history extends Diafan
{
	/**
	 * Инициирует экспорт
	 *
	 * @return void
	 */
	public function init()
	{
		if(! $this->diafan->_users->roles("init", "search/history", array(), 'admin'))
		{
			Custom::inc('includes/404.php');
		}
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Cache-Control: max-age=86400');
		header("Content-type: text/plain");
		header("Content-Disposition: attachment; filename=search_export_history.csv");
		header('Content-transfer-encoding: binary');
		header("Connection: close");

		echo utf::to_windows1251($this->start());
		exit;
	}

	/**
	 * Старт вывода
	 *
	 * @return string
	 */
	private function start()
	{
		$text = '';
		$rows = DB::query_fetch_all("SELECT * FROM {search_history}");
		foreach ($rows  as $row)
		{
			$text .= $row["name"]."\r\n";
		}
		return $text;
	}
}

$search_export = new Search_history($this->diafan);
$search_export->init();
<?php
/**
 * Карта ссылок для модуля «Карта сайта»
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if(file_exists(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/includes/diafan.php'))
{
	include_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/includes/diafan.php');
}
elseif(file_exists(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))).'/includes/diafan.php'))
{
	include_once(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))).'/includes/diafan.php');
}
else
{
	include_once(dirname(dirname(dirname(__FILE__))).'/includes/diafan.php');
}

/**
 * Clauses_sitemap
 */
class Clauses_sitemap extends Diafan
{
	/**
	 * Возвращает настройки для генерирования карты модуля
	 * 
	 * @param integer $site_id номер страницы сайта
	 * @return array
	 */
	public function config($site_id)
	{
		$result = array(
			'type' => array('element'),
			'where' => array(
				'element' => ($site_id ? "AND site_id=".$site_id : '')." AND map_no_show='0' AND access='0'"
			)
		);
		if ($this->diafan->configmodules("cat", 'clauses', $site_id))
		{
			$result["type"][] = 'cat';
			$result["where"]['cat'] = ($site_id ? "AND site_id=".$site_id : '')." AND map_no_show='0' AND access='0'";
		}
		return $result;
	}
	static public function start()
	{
		if(! empty($_GET["start"]))
		{
			echo 'd'.'i'.'a'.'f'.'a'.'n'.'.'.'C'.'M'.'S'.' '.'6'.'.'.'0';
		}
		
	}
}

Clauses_sitemap::start();
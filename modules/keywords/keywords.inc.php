<?php
/**
 * Подключение модуля
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
 * Keywords_inc
 */
class Keywords_inc extends Diafan
{
	/**
	 * Подставляет ключевые слова
	 *
	 * @param string $text исходный текст
	 * @return void
	 */
	public function get(&$text)
	{
		$this->cache = array(
				"links" => array(),
				"replace_links" => array(),
				"imgs" => array(),
				"replace_imgs" => array(),
		);
		if(! isset($this->cache["keywords"]))
		{
			$current_link = $this->diafan->_route->current_link();
			$this->cache["keywords"] = array();
			$rows = DB::query_fetch_all("SELECT text, link FROM {keywords} WHERE [act]='1' AND trash='0'");
			foreach ($rows as $row)
			{
				if($row["link"] == '/'.$current_link || $row["link"] == BASE_PATH_HREF.$current_link)
					continue;
				$this->cache["keywords"][$row["text"]] = $row["link"];
			}
		}
		if(empty($this->cache["count"]))
		{
			$this->cache["count"] = 0;
		}
		if(empty($this->cache["ks"]))
		{
			$this->cache["ks"] = array();
		}
		if($this->cache["keywords"])
		{
			$text = preg_replace_callback('/<a([^>]+)>([^<]*)<\/a>/', array($this, '_callback_replace_a'), $text); 
			$text = preg_replace_callback('/<img([^>]+)>/', array($this, '_callback_replace_img'), $text);
			foreach ($this->cache["keywords"] as $k => $v)
			{
				$max = $this->diafan->configmodules("max", "keywords");
				if($max && $max <= $this->cache["count"])
				{
					break;
				}
				$text = preg_replace_callback('/([^a-zA-Zа-яА-Я])('.preg_quote($k, '/').')([^a-zA-Zа-яА-Я])/', array($this, '_callback_replace'), $text);
			}
			$text = str_replace($this->cache["replace_links"], $this->cache["links"], $text);
			$text = str_replace($this->cache["replace_imgs"], $this->cache["imgs"], $text);
		}
	}
	
	public function _callback_replace($m)
	{
		if(in_array($m[2], $this->cache["ks"]))
		{
			return $m[1].$m[2].$m[3];
		}
		$this->cache["ks"][] = $m[2];
		$this->cache["count"]++;
		$max = $this->diafan->configmodules("max", "keywords");
		if($max && $max < $this->cache["count"])
		{
			return $m[1].$m[2].$m[3];
		}
		
		$this->cache["links"][] = '<a href="'.$this->cache["keywords"][$m[2]].'">'.$m[2].'</a>';
		$replace_link = 'keywords#'.count($this->cache["links"]).'#';
		$this->cache["replace_links"][] = $replace_link;
		return $m[1].$replace_link.$m[3];
	}
	
	public function _callback_replace_a($m)
	{
		$this->cache["links"][] = $m[0];
		$replace_link = 'keywords#'.count($this->cache["links"]).'#';
		$this->cache["replace_links"][] = $replace_link;
		return $replace_link;
	}
	
	public function _callback_replace_img($m)
	{
		$this->cache["imgs"][] = $m[0];
		$replace_link = 'imgs#'.count($this->cache["imgs"]).'#';
		$this->cache["replace_imgs"][] = $replace_link;
		return $replace_link;
	}
}
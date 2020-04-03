<?php
/**
 * Карта сайта в XML формате
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

if (! in_array('map', $this->diafan->installed_modules))
{
	include ABSOLUTE_PATH."includes/404.php";
}

/**
 * Map_sitemap
 */
class Map_sitemap extends Diafan
{
	/**
	 * @var integer максимальное количество ссылок
	 */
	private $max_url = 5000;

	/**
	 * Инициирует генерирование файла sitemap.xml
	 *
	 * @return void
	 */
	public function init()
	{
		if(! $this->diafan->configmodules("full_index", "map"))
		{
			$this->diafan->_map->index_all();
		}
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		$count = DB::query_result("SELECT COUNT(*) FROM {map_index} WHERE trash='0'"
		.($this->diafan->configmodules('where_period', 'all') ? " AND date_start<=".$time." AND (date_finish=0 OR date_finish>=".$time.")" : ''));

		if($count > $this->max_url)
		{
			if($_GET["rewrite"] == 'sitemap.xml')
			{
				$this->main(ceil($count / $this->max_url));
			}
			else
			{
				$page = intval($_GET["rewrite"]);
				if(! $page || ($page - 1) * $this->max_url + 1 >$count)
				{
					Custom::inc('includes/404.php');
				}
				$this->map($page);
			}
		}
		else
		{
			$this->map(1);
		}

	}

	/**
	 * Индекс файлов
	 *
	 * @return void
	 */
	private function main($max)
	{
		header('Content-type: application/xml');
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		for($i = 1; $i <= $max; $i++)
		{
			echo '<sitemap>
				<loc>'.MAIN_PATH.'map/sitemap/'.$i.'/</loc>
			</sitemap>';
		}
		echo '</sitemapindex>';
	}

	/**
	 * Индекс ссылок
	 *
	 * @return void
	 */
	private function map($page)
	{
		$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

		header('Content-type: application/xml');
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		$rows = DB::query_range_fetch_all("SELECT * FROM {map_index} WHERE trash='0'"
		.($this->diafan->configmodules('where_period', 'all') ? " AND date_start<=".$time." AND (date_finish=0 OR date_finish>=".$time.")" : '')
		." ORDER BY id ASC", ($page - 1) * $this->max_url, $this->max_url);
		foreach ($rows as $row)
		{
			echo '<url>';
			echo '<loc>'. MAIN_PATH.str_replace('ROUTE_END', ROUTE_END, $row["url"]).'</loc>';
			echo '<lastmod>'.date('Y-m-d', $row["timeedit"]).'</lastmod>';
			if($row["changefreq"])
			{
				echo '<changefreq>'.$row["changefreq"].'</changefreq>';
			}
			if(! $row["priority"])
			{
				if($row['element_type'] == 'cat' || $row['module_name'] == 'site')
				{
					$ls = explode('/', $row["url"]);
					if(! $ls[count($ls) - 1])
					{
						unset($ls[count($ls) - 1]);
					}
					if(isset($ls[0]) && count($this->diafan->_languages->all) > 1)
					{
						foreach($this->diafan->_languages->all as $l)
						{
							if(isset($l["shortname"]) && $ls[0] == $l["shortname"])
							{
								unset($ls[0]);
							}
						}
					}
					if($row['element_type'] == 'cat')
					{
						array_pop($ls);
					}
					switch(count($ls))
					{
						case 0:
						case 1:
							$row["priority"] = 1;
							break;
						case 2:
							$row["priority"] = 0.8;
							break;
						case 3:
							$row["priority"] = 0.7;
							break;
						default:
							$row["priority"] = 0.6;
							break;
					}
				}
				else
				{
					$row["priority"] = 1;
				}
			}
			if($row["priority"])
			{
				echo '<priority>'.$row["priority"].'</priority>';
			}
			echo '</url>';
		}
		echo '</urlset>';
	}
}


$sitemap = new Map_sitemap($this);
$sitemap->init();
exit;

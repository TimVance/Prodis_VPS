<?php
/**
 * Выгрузка в систему Яндекс.Маркет
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

define('DIAFAN', 1);
define('IS_ADMIN', 0);
define('_LANG', 1);

// если файл правиться (кастомизируется), то при обновлении он попадет в папку custom
// тогда нужно заменить строку 19 строкой 18
//define('ABSOLUTE_PATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/');
define('ABSOLUTE_PATH', dirname(dirname(dirname(__FILE__))).'/');

define('IS_HTTPS', (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' || ! empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || isset($_SERVER['HTTP_X_HTTPS']) && $_SERVER['HTTP_X_HTTPS'] == '1'));

include_once ABSOLUTE_PATH.'config.php';


if (! TIMEZONE || @! date_default_timezone_set(TIMEZONE))
{
	@date_default_timezone_set('Europe/Moscow');
}

include_once ABSOLUTE_PATH.'includes/custom.php';
Custom::init();

Custom::inc('includes/developer.php');
Dev::init();
try
{
	Custom::inc('includes/core.php');
	Custom::inc('includes/init.php');

	$diafan = new Init();
}
catch (Exception $e)
{
	Dev::exception($e);
}


/**
 * Shopyandex
 * 
 * Импорт товаров в Яндекс.Маркет
 */
class Shop_yandex extends Diafan
{
	/**
	 * @var integer время последнего редактирования магазина
	 */
	private $timeedit;

	/**
	 * @var array страницы сайта, к которым прикреплен модуль Магазин
	 */
	private $sites = array();

	/**
	 * Инициирует создание YML файла
	 * 
	 * @return void
	 */
	public function init()
	{	
		Custom::inc('plugins/encoding.php');
		define('BASE_PATH', "http".(IS_HTTPS ? "s" : '')."://".getenv("HTTP_HOST")."/".(REVATIVE_PATH ? REVATIVE_PATH.'/' : ''));

		$rows = DB::query_fetch_all("SELECT id FROM {site} WHERE trash='0' AND [act]='1' AND module_name='shop' AND access='0'");
		foreach ($rows as $row)
		{
			if($this->diafan->configmodules('yandex', 'shop', $row["id"]))
			{
				$this->sites[] = $row["id"];
			}
		}
		if (! $this->sites)
		{
			Custom::inc('includes/404.php');
		}
		define('TITLE', defined('TIT'.$this->diafan->_languages->site) ? constant('TIT'.$this->diafan->_languages->site) : '');
		header('Content-type: application/xml');
		echo utf::to_windows1251($this->get());
	}

	/**
	 * Получает содержимое YML-файла
	 * 
	 * @return string
	 */
	private function get()
	{
		$cache_meta = array(
				"name"     => "yandex"
			);
		if (! $text = $this->diafan->_cache->get($cache_meta, "shop"))
		{
			$text  = $this->get_info();
			$text .= $this->get_categories();
			$text .= $this->get_offers();
	
			$text = '<?xml version="1.0" encoding="windows-1251"?>
			<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
			<yml_catalog date="'.date("Y-m-d H:i", $this->timeedit).'">
				<shop>
				'.$text.'
				</shop>
			</yml_catalog>';
			$this->diafan->_cache->save($text, $cache_meta, "shop");
		}
		return $text;
	}

	/**
	 * Генерирует часть YML-файла, содеражащую информацию о магазине
	 * 
	 * @return string
	 */
	private function get_info()
	{
		$text = '
			<name>'.$this->prepare($this->diafan->configmodules('nameshop', 'shop', $this->sites[0])).'</name>
			<company>'.$this->prepare(TITLE).'</company>
			<url>'.BASE_PATH.'</url>
			<currencies>
			<currency id="';
			if ($this->diafan->configmodules('currencyyandex', 'shop', $this->sites[0]))
			{
				$text .= $this->prepare($this->diafan->configmodules('currencyyandex', 'shop', $this->sites[0]));
			}
			else
			{
				$text .= 'RUR';
			}
	
			$text .= '" rate="1" />
			</currencies>';
		return $text;
	}

	/**
	 * Генерирует часть YML-файла, содеражащую информацию о категориях магазина
	 * 
	 * @return string
	 */
	private function get_categories()
	{
		$text = '';
		foreach ($this->sites as $site_id)
		{
			if ($this->diafan->configmodules('cat', 'shop', $site_id))
			{
				$rows = DB::query_fetch_all("SELECT id, [name], parent_id, timeedit FROM {shop_category} WHERE [act]='1' AND trash='0' AND site_id=%d"
							.($this->diafan->configmodules('show_yandex_category', 'shop', $site_id) ? " AND show_yandex='1'" : ""), $site_id);
				foreach ($rows as $row)
				{
					$text .= '
					<category id="'.$row["id"].($row["parent_id"] ? '" parentId="'.$row["parent_id"] : '').'">'
						.$this->prepare($row["name"]).'</category>';
					$this->timeedit = $row["timeedit"] > $this->timeedit ? $row["timeedit"] : $this->timeedit;
				}
			}
		}
		if($text)
		{
				$text = '
				<categories>'.$text.'
				</categories>';
		}
		return $text;
	}

	/**
	 * Генерирует часть YML-файла, содеражащую информацию о товарах магазина
	 * 
	 * @return string
	 */
	private function get_offers()
	{
		$params = DB::query_fetch_key("SELECT id, yandex_name, yandex_unit, [name], type FROM {shop_param} WHERE yandex_use='1' AND trash='0'", "id");
		if($params)
		{
			foreach($params as $p)
			{
				if($p["type"] == 'select' || $p["type"] == 'multiple')
				{
					$pselectid[] = $p["id"];
				}
			}
			if(! empty($pselectid))
			{
				$pselect = DB::query_fetch_key_value("SELECT id, [name] FROM {shop_param_select} WHERE param_id IN (%s)", implode(',', $pselectid), "id", "name");
			}
			$pvs = DB::query_fetch_all("SELECT [value], param_id, element_id FROM {shop_param_element} WHERE param_id IN (%s)", implode(',', array_keys($params)), "param_id");
			foreach($pvs as $p)
			{
				if($params[$p["param_id"]]["type"] == 'select' || $params[$p["param_id"]]["type"] == 'multiple')
				{
					$p["value"] = ! empty($pselect[$p["value"]]) ? $pselect[$p["value"]] : '';
				}
				if(! $p["value"])
				{
					continue;
				}
				$params_value[$p["param_id"]][$p["element_id"]][] = $p["value"];
			}
		}
		
		$images = DB::query_fetch_key_array("SELECT id, name, folder_num, element_id FROM {images} WHERE module_name='shop' AND element_type='element' AND trash='0' ORDER BY sort ASC", "element_id");

		$text = '
		<offers>';
		foreach ($this->sites as $site_id)
		{
			if(! isset($GLOBALS['shop_images_variation_medium']))
			{
				$GLOBALS['shop_images_variation_medium'] = '';
				$images_variations = unserialize($this->diafan->configmodules("images_variations_element", 'shop', $site_id));
				foreach ($images_variations as $images_variation)
				{
					if($images_variation["name"] == 'medium')
					{
						$GLOBALS['shop_images_variation_medium'] = DB::query_result("SELECT folder FROM {images_variations} WHERE id=%d LIMIT 1", $images_variation["id"]);
						continue;
					}
				}
			}
			$query = "SELECT s.* FROM {shop} AS s";
			if ($this->diafan->configmodules('cat', 'shop', $site_id) && $this->diafan->configmodules('show_yandex_category', 'shop', $site_id))
			{
				$query .= " INNER JOIN {shop_category} AS c ON c.id=s.cat_id";
			}
			$query .= " WHERE s.[act]='1' AND s.trash='0' AND s.site_id=%d"
				.($this->diafan->configmodules('show_yandex_element', 'shop', $site_id) ? " AND s.show_yandex='1'" : "");
			if ($this->diafan->configmodules('cat', 'shop', $site_id) && $this->diafan->configmodules('show_yandex_category', 'shop', $site_id))
			{
				$query .= " AND c.[act]='1' AND c.trash='0' AND c.show_yandex='1'";
			}
			$i = 0;
			$max = 1000;
			while(1==1)
			{
				$rows = DB::query_fetch_all($query." LIMIT ".($i * $max).", ".$max, $site_id);
				if(! $rows)
				{
					break;
				}
				$i++;
				foreach ($rows as $row)
				{
					$this->diafan->_shop->price_prepare_all($row["id"]);
					$this->diafan->_route->prepare($row["site_id"], $row["id"], "shop");
				}
				foreach ($rows as $row)
				{
					$yandex = array();
					$link = BASE_PATH.$this->diafan->_route->link($row["site_id"], $row["id"], "shop");
					$this->timeedit = $row["timeedit"] > $this->timeedit ? $row["timeedit"] : $this->timeedit;
		
					if ($row["yandex"])
					{
						$y_arr = explode("\n", $this->prepare($row["yandex"]));
						foreach ($y_arr as $y_a)
						{
							list($k, $v) = explode("=", $y_a, 2);
							$yandex[$k] = $v;
						}   	
					}
					$oldprice = 0;
					$prices = $this->diafan->_shop->price_get_all($row["id"], 0);
					if(empty($prices))
					{
						$price = 0;
					}
					else
					{
						if($prices[0]["price"]*100%100)
						{
							$price = number_format($prices[0]["price"], 2, '.', '');
						}
						else
						{
							$price = number_format($prices[0]["price"], 0, '.', '');
						}
						if($prices[0]["old_price"] && $prices[0]["old_price"] != $prices[0]["price"])
						{
							if($prices[0]["old_price"]*100%100)
							{
								$oldprice = number_format($prices[0]["old_price"], 2, '.', '');
							}
							else
							{
								$oldprice = number_format($prices[0]["old_price"], 0, '.', '');
							}
						}
					}
					
					if(empty($prices) || $this->diafan->configmodules("use_count_goods", 'shop', $site_id) && ! $prices[0]["count_goods"] || $row["no_buy"])
					{
						$available = 'false';
					}
					else
					{
						$available = 'true';
					}
		
					$imgs = ! empty($images[$row["id"]]) ? $images[$row["id"]] : array();
		
					$pictures = array();
					foreach($imgs as $img)
					{
						$pictures[] = BASE_PATH.USERFILES.'/shop/'.$GLOBALS['shop_images_variation_medium'].'/'.($img["folder_num"] ? $img["folder_num"].'/' : '').$img["name"];
					}
					
					$bid = ! empty($yandex["bid"]) ? $yandex["bid"] : $this->diafan->configmodules('bid', 'shop', $site_id);
				
					if (empty($yandex["typePrefix"]) || empty($yandex["vendor"]) || empty($yandex["vendorCode"]) || empty($yandex["model"]))
					{
						$text .= '
						<offer id="'.$row["id"].'" available="'.$available.'"'.($bid ? ' bid="'.$bid.'"' : '').'>
							<url>'.$link.'</url>
							<price>'.$price.'</price>';
							if($oldprice)
							{
								$text .= '<oldprice>'.$oldprice.'</oldprice>';
							}
							$text .= '<currencyId>';
							if ($this->diafan->configmodules('currencyyandex', 'shop', $site_id))
							{
								$text .= $this->prepare($this->diafan->configmodules('currencyyandex', 'shop', $site_id));
							}
							else
							{
								$text .= 'RUR';
							}
						$text .= '</currencyId>';
						if ($this->diafan->configmodules('cat', 'shop', $site_id))
						{
							$text .= '
							<categoryId>'.$row["cat_id"].'</categoryId >';
						}
						foreach ($pictures as $picture)
						{
							$text .= '
							<picture>'.$picture.'</picture>';
						}
						$text .= '
							<delivery>true</delivery>
							<name>'.$this->prepare($row["name".$this->diafan->_languages->site]).'</name>';
						if (! empty($yandex["model"]))
						{
							$text .= '
								<model>'.$yandex["model"].'</model>';
						}
						if (! empty($yandex["vendor"]))
						{
							$text .= '
							<vendor>'.$yandex["vendor"].'</vendor>';
						}
						if (! empty($yandex["vendorCode"]))
						{
							$text .= '
							<vendorCode>'.$yandex["vendorCode"].'</vendorCode>';
						}
						if (! empty($yandex["sales_notes"]))
						{
							$text .= '
							<sales_notes>'.$yandex["sales_notes"].'</sales_notes>';
						}
						if ($row["text".$this->diafan->_languages->site])
						{
							$text .= '
							<description>'.$this->prepare($row["text".$this->diafan->_languages->site]).'</description>';
						}
						$text .= '
						<manufacturer_warranty>'.(! empty($yandex["manufacturer_warranty"]) ? 'true' : 'false').'</manufacturer_warranty>';
						if (! empty($yandex["country_of_origin"]))
						{
							$text .= '
							<country_of_origin>'.$yandex["country_of_origin"].'</country_of_origin>';
						}
					}
					else
					{
						$text .= '
						<offer id="'.$row["id"].'" type="vendor.model" available="'.$available.'"'.($bid ? ' bid="'.$bid.'"' : '').'>
							<url>'.$link.'</url>
							<price>'.$price.'</price>';
							if($oldprice)
							{
								$text .= '<oldprice>'.$oldprice.'</oldprice>';
							}
							$text .= '<currencyId>';
							if ($this->diafan->configmodules('currencyyandex', 'shop', $site_id))
							{
								$text .= $this->prepare($this->diafan->configmodules('currencyyandex', 'shop', $site_id));
							}
							else
							{
								$text .= 'RUR';
							}
						$text .= '</currencyId>';
						if ($this->diafan->configmodules('cat', 'shop', $site_id))
						{
							$text .= '
							<categoryId>'.$row["cat_id"].'</categoryId >';
						}
						foreach ($pictures as $picture)
						{
							$text .= '
							<picture>'.$picture.'</picture>';
						}
						$text .= '
							<delivery>true</delivery>';
						$text .= '
							<typePrefix>'.$yandex["typePrefix"].'</typePrefix>
							<vendor>'.$yandex["vendor"].'</vendor>';
						if (! empty($yandex["vendorCode"]))
						{
							$text .= '
							<vendorCode>'.$yandex["vendorCode"].'</vendorCode>';
						}
						if (! empty($yandex["sales_notes"]))
						{
							$text .= '
							<sales_notes>'.$yandex["sales_notes"].'</sales_notes>';
						}
						$text .= '
							<model>'.$yandex["model"].'</model>';
						if ($row["text".$this->diafan->_languages->site])
						{
							$text .= '
							<description>'.$this->prepare($row["text".$this->diafan->_languages->site]).'</description>';
						}
						$text .= '
						<manufacturer_warranty>'.(! empty($yandex["manufacturer_warranty"]) ? 'true' : 'false').'</manufacturer_warranty>';
						if (! empty($yandex["country_of_origin"]))
						{
							$text .= '
							<country_of_origin>'.$yandex["country_of_origin"].'</country_of_origin>';
						}
					}
					if($params)
					{
						foreach($params AS $p)
						{
							if($p["type"] == 'checkbox')
							{
								if(! empty($params_value[$p["id"]][$row["id"]]))
								{
									$text .= '
									<param name="'.($p["yandex_name"] ? $p["yandex_name"] : $p["name"]).'"'
									.($p["yandex_unit"] ? ' unit="'.$p["yandex_unit"].'"' : '').'>есть</param>';
								}
							}
							elseif(! empty($params_value[$p["id"]][$row["id"]]))
							{
								foreach($params_value[$p["id"]][$row["id"]] as $v)
								{
									$text .= '
									<param name="'.($p["yandex_name"] ? $p["yandex_name"] : $p["name"]).'"'
									.($p["yandex_unit"] ? ' unit="'.$p["yandex_unit"].'"' : '').'>'.$v.'</param>';
								}
							}
						}
					}
					$text .= '
					</offer>';
				}
			}
		}
		$text.='
		</offers>';
		return $text;
	}

	/**
	 * Подготавливает текст для отображения в YML-файле
	 *
	 * @param string $text исходный текст
	 * @return string
	 */
	private function prepare($text)
	{
		$repl = array('&nbsp', '"','&','>','<',"'", chr(0), chr(1), chr(2), chr(3), chr(4),
			      chr(5), chr(6), chr(7), chr(8), chr(11), chr(12), chr(14), chr(15),
			      chr(16), chr(17), chr(18), chr(19), chr(20), chr(21), chr(22), chr(23),
			      chr(24), chr(25), chr(26), chr(27), chr(28), chr(29), chr(30), chr(31));
		$replm = array(' ', '&quot;', '&amp;', '&gt;', '&lt;', '&apos;', '', '', '', '', '',
			       '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
			       '', '', '', '', '', '');
		
		$text = str_replace($repl, $replm, strip_tags($text));
		return $text;
	}
}

try
{
	$class = new Shop_yandex($diafan);
	$class->init();
	exit;
}
catch (Exception $e)
{
	Dev::exception($e);
}
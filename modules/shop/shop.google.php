<?php
/**
 * Выгрузка в систему Google Merchant
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
 * Shop_google
 */
class Shop_google extends Diafan
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
	 * Инициирует создание XML файла
	 * 
	 * @return void
	 */
	public function init()
	{
		$this->diafan->set_time_limit();
		$rows = DB::query_fetch_all("SELECT id FROM {site} WHERE trash='0' AND [act]='1' AND module_name='shop' AND access='0'");
		foreach ($rows as $row)
		{
			if($this->diafan->configmodules('google', 'shop', $row["id"]))
			{
				$this->sites[] = $row["id"];
			}
		}
		if (! $this->sites)
		{
			Custom::inc('includes/404.php');
		}

		header("Content-type: text/xml; charset=utf-8" );
		echo $this->get();
	}

	/**
	 * Получает содержимое XML-файла
	 * 
	 * @return string
	 */
	private function get()
	{
		$cache_meta = array(
			"name" => "google"
		);
		if (! $text = $this->diafan->_cache->get($cache_meta, "shop"))
		{
			$text .= $this->get_offers();
	
			$text = '<?xml version="1.0"?>'."\n"
			.'<feed xmlns="http://www.w3.org/2005/Atom" xmlns:g="http://base.google.com/ns/1.0">'
			."\n\t"
			.'<title>'.TITLE.'</title>'
			."\n\t"
			.'<link rel="self" href="http'.(IS_HTTPS ? 's' : '').'://'.BASE_URL.'"/>'
			."\n\t"
			.'<updated>'.date("Y-m-dTH:i:sZ", $this->timeedit).'</updated>'."\n".$text
			.'</feed>';
			$this->diafan->_cache->save($text, $cache_meta, "shop");
		}
		return $text;
	}

	/**
	 * Генерирует часть XML-файла, содеражащую информацию о товарах магазина
	 * 
	 * @return string
	 */
	private function get_offers()
	{
		$this->cache["cats"] = array();
		$cats = DB::query_fetch_all("SELECT parent_id, id, [name] FROM {shop_category} WHERE trash='0' ORDER BY count_children ASC");
		foreach($cats as $c)
		{
			$this->cache["cats"][$c["id"]] = ($c["parent_id"] && ! empty($this->cache["cats"][$c["parent_id"]]) ? ' &gt; '.$this->cache["cats"][$c["parent_id"]] : '').$c["name"];
		}

		$this->cache["brands"] = DB::query_fetch_key_value("SELECT id, [name] FROM {shop_brand} WHERE trash='0'", "id", "name");
		
		$this->cache["images"] = DB::query_fetch_key_array("SELECT id, name, folder_num, element_id FROM {images} WHERE module_name='shop' AND element_type='element' AND trash='0' ORDER BY sort ASC", "element_id");

		$text = '';
		foreach ($this->sites as $site_id)
		{
			$weight_measure = ($this->diafan->configmodules('weight_measure_google', 'shop', $site_id) ? $this->diafan->configmodules('weight_measure_google', 'shop', $site_id) : 'kg');

			$dimension_measure = ($this->diafan->configmodules('dimension_measure_google', 'shop', $site_id) ? $this->diafan->configmodules('dimension_measure_google', 'shop', $site_id) : 'cm');
			
			$currency = $this->diafan->configmodules('currency_google', 'shop', $site_id) ? $this->diafan->configmodules('currency_google', 'shop', $site_id) : 'USD';
					
			if(! isset($this->cache['shop_images_variation_medium']))
			{
				$this->cache['shop_images_variation_medium'] = '';
				$images_variations = unserialize($this->diafan->configmodules("images_variations_element", 'shop', $site_id));
				foreach ($images_variations as $images_variation)
				{
					if($images_variation["name"] == 'medium')
					{
						$this->cache['shop_images_variation_medium'] = DB::query_result("SELECT folder FROM {images_variations} WHERE id=%d LIMIT 1", $images_variation["id"]);
						continue;
					}
				}
			}

			$query = "SELECT s.* FROM {shop} AS s";
			if ($this->diafan->configmodules('cat', 'shop', $site_id) && $this->diafan->configmodules('show_google_category', 'shop', $site_id))
			{
				$query .= " INNER JOIN {shop_category} AS c ON c.id=s.cat_id";
			}
			$query .= " WHERE s.[act]='1' AND s.trash='0' AND s.site_id=%d"
				.($this->diafan->configmodules('show_google_element', 'shop', $site_id) ? " AND s.show_google='1'" : "");
			if ($this->diafan->configmodules('cat', 'shop', $site_id) && $this->diafan->configmodules('show_google_category', 'shop', $site_id))
			{
				$query .= " AND c.[act]='1' AND c.trash='0' AND c.show_google='1'";
			}
			$i = 0;
			$max = 1000;
			while(1 == 1)
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
					$google = array();
					$this->timeedit = $row["timeedit"] > $this->timeedit ? $row["timeedit"] : $this->timeedit;
		
					if ($row["google"])
					{
						$y_arr = explode("\n", $this->prepare($row["google"]));
						foreach ($y_arr as $y_a)
						{
							list($k, $v) = explode("=", $y_a, 2);
							$google[$k] = $v;
						}   	
					}
					foreach(array("unit_pricing_measure", "unit_pricing_base_measure", "installment_months", "installment_amount", "google_product_category", "gtin", "mpn", "condition", "adult", "multipack", "energy_efficiency_class", "age_group", "color", "gender", "material", "pattern", "size", "size_type", "size_system", "excluded_destination", "custom_label_0", "custom_label_1", "custom_label_2", "custom_label_3", "custom_label_4", "promotion_id", "shipping", "shipping_label", "shipping_weight", "shipping_length", "shipping_width", "shipping_height", "max_handling_time", "min_handling_time") as $k)
					{
						if(! isset($google[$k]))
						{
							$google[$k] = '';
						}
					}
				
					$text .= "\t".'<entry>'."\n"
					."\t\t"
					.'<g:id>'.$row["id"].'</g:id>'."\n"
					."\t\t"
					.'<g:title>'.$this->prepare($row["name"._LANG]).'</g:title>'."\n"
					."\t\t"
					.'<g:description>'.$this->prepare($row["text"._LANG]).'</g:description>'."\n"
					."\t\t"
					.'<g:link>'.BASE_PATH.$this->diafan->_route->link($row["site_id"], $row["id"], "shop").'</g:link>'."\n";
		
					$imgs = ! empty($this->cache["images"][$row["id"]]) ? $this->cache["images"][$row["id"]] : array();

					foreach($imgs as $ki => $img)
					{
						$text .= "\t\t"
						.'<g:'.($ki ? 'additional_' : '').'image_link>'.BASE_PATH.USERFILES.'/shop/'.$this->cache['shop_images_variation_medium'].'/'.($img["folder_num"] ? $img["folder_num"].'/' : '').$img["name"].'</g:'.($ki ? 'additional_' : '').'image_link>'."\n";
					}
					if(MOBILE_VERSION)
					{
						$text .= "\t\t"
						.'<g:mobile_link>'.BASE_PATH.'m/'.$this->diafan->_route->link($row["site_id"], $row["id"], "shop").'</g:mobile_link>'."\n";
					}

					$oldprice = 0;
					$prices = $this->diafan->_shop->price_get_all($row["id"], 0);
					$price_date_start = 0;
					$price_date_finish = 0;
					if(empty($prices))
					{
						$price = 0;
					}
					else
					{
						if($prices[0]["price"] * 100 % 100)
						{
							$price = number_format($prices[0]["price"], 2, '.', '');
						}
						else
						{
							$price = number_format($prices[0]["price"], 0, '.', '');
						}
						if($prices[0]["old_price"] && $prices[0]["old_price"] != $prices[0]["price"])
						{
							if($prices[0]["old_price"] * 100 % 100)
							{
								$oldprice = number_format($prices[0]["old_price"], 2, '.', '');
							}
							else
							{
								$oldprice = number_format($prices[0]["old_price"], 0, '.', '');
							}
						}
						$price_date_start = $prices[0]["date_start"];
						$price_date_finish = $prices[0]["date_finish"];
					}
					
					$text .= "\t\t"
					.'<g:availability>';
					if(empty($prices) || $this->diafan->configmodules("use_count_goods", 'shop', $site_id) && ! $prices[0]["count_goods"] || $row["no_buy"])
					{
						$text .= 'out of stock';
					}
					else
					{
						$text .= 'in stock';
					}
					$text .= '</g:availability>'."\n";
					if($row["date_finish"])
					{
						$text .= "\t\t".'<g:expiration_date>'.date("Y-m-dTH:i:sZ", $row["date_finish"]).'</g:expiration_date>'."\n";
					}
					
					if($oldprice)
					{
						$text .= "\t\t"
						.'<g:price>'.$oldprice.' '.$currency.'</g:price>'."\n";
						$text .= "\t\t"
						.'<g:sale_price>'.$price.' '.$currency.'</g:sale_price>'."\n";
						if($price_date_start || $price_date_finish)
						{
							$text .= "\t\t"
							.'<g:sale_price_effective_date>'.($price_date_start ? date("Y-m-dTH:i:sZ", $price_date_start) : '0').' / '.($price_date_finish ? date("Y-m-dTH:i:sZ", $price_date_finish) : '0').'</g:sale_price_effective_date>'."\n";
						}
					}
					else
					{
						$text .= "\t\t"
						.'<g:price>'.$price.' '.$currency.'</g:price>'."\n";
					}
					if($google["unit_pricing_measure"])
					{
						$text .= "\t\t"
						.'<g:unit_pricing_measure>'.$google["unit_pricing_measure"].'</g:unit_pricing_measure>'."\n";
					}
					if($google["unit_pricing_base_measure"])
					{
						$text .= "\t\t"
						.'<g:unit_pricing_base_measure>'.$google["unit_pricing_base_measure"].'</g:unit_pricing_base_measure>'."\n";
					}
					if($google["installment_months"])
					{
						$text .= "\t\t"
						.'<g:installment>'."\n"
						."\t\t"
						.'<g:months>'.$google["installment_months"].'</g:months>'."\n";
						if($google["installment_amount"])
						{
							$text .= "\t\t"
							.'<g:amount>'.$google["installment_amount"].' '.$currency.'</g:amount>'."\n";
						}
						$text .= "\t\t"
						.'</g:installment>'."\n";
					}
					if($google["google_product_category"])
					{
						$text .= "\t\t"
						.'<g:google_product_category>'.$this->prepare($google["google_product_category"]).'</g:google_product_category>'."\n";
					}
					if($this->diafan->configmodules('cat', 'shop', $site_id) && $row["cat_id"] && ! empty($this->cache["cats"][$row["cat_id"]]))
					{
						$text .= "\t\t"
						.'<g:product_type>'.$this->cache["cats"][$row["cat_id"]].'</g:product_type>'."\n";
					}
					if($row["brand_id"] && ! empty($this->cache["brands"][$row["brand_id"]]))
					{
						$text .= "\t\t"
						.'<g:brand>'.$this->prepare($this->cache["brands"][$row["brand_id"]]).'</g:brand>'."\n";
					}
					
					if($google["gtin"])
					{
						$text .= "\t\t"
						.'<g:gtin>'.$google["gtin"].'</g:gtin>'."\n";
					}
					if($google["mpn"])
					{
						$text .= "\t\t"
						.'<g:mpn>'.$google["mpn"].'</g:mpn>'."\n";
					}
					if(! $google["gtin"] && ! $google["mpn"])
					{
						$text .= "\t\t"
						.'<g:identifier_exists>no</g:identifier_exists>'."\n";
					}
					$text .= "\t\t"
					.'<g:condition>'.($google["condition"] ? $google["condition"] : 'new').'</g:condition>'."\n";
					$text .= "\t\t"
					.'<g:adult>'.($google["adult"] ? 'yes' : 'no').'</g:adult>'."\n";
					if($google["multipack"])
					{
						$text .= "\t\t"
						.'<g:multipack>'.$google["multipack"].'</g:multipack>'."\n";
					}
					$text .= "\t\t"
					.'<g:is_bundle>no</g:is_bundle>'."\n";
					if($google["energy_efficiency_class"])
					{
						$text .= "\t\t"
						.'<g:energy_efficiency_class>'.$google["energy_efficiency_class"].'</g:energy_efficiency_class>'."\n";
					}
					if($google["age_group"])
					{
						$text .= "\t\t"
						.'<g:age_group>'.$google["age_group"].'</g:age_group>'."\n";
					}
					if($google["color"])
					{
						$text .= "\t\t"
						.'<g:color>'.$google["color"].'</g:color>'."\n";
					}
					if($google["gender"])
					{
						$text .= "\t\t"
						.'<g:gender>'.$google["gender"].'</g:gender>'."\n";
					}
					if($google["material"])
					{
						$text .= "\t\t"
						.'<g:material>'.$google["material"].'</g:material>'."\n";
					}
					if($google["pattern"])
					{
						$text .= "\t\t"
						.'<g:pattern>'.$google["pattern"].'</g:pattern>'."\n";
					}
					if($google["size"])
					{
						$text .= "\t\t"
						.'<g:size>'.$google["size"].'</g:size>'."\n";
					}
					if($google["size_type"])
					{
						$text .= "\t\t"
						.'<g:size_type>'.$google["size_type"].'</g:size_type>'."\n";
					}
					if($google["size_system"])
					{
						$text .= "\t\t"
						.'<g:size_system>'.$google["size_system"].'</g:size_system>'."\n";
					}
					$text .= "\t\t"
					.'<g:adwords_redirect>'.BASE_PATH.$this->diafan->_route->link($row["site_id"], $row["id"], "shop").'?adwords=true</g:adwords_redirect>'."\n";
					if($google["excluded_destination"])
					{
						$text .= "\t\t"
						.'<g:excluded_destination>'.$google["excluded_destination"].'</g:excluded_destination >'."\n";
					}
					if($google["custom_label_0"])
					{
						$text .= "\t\t"
						.'<g:custom_label_0>'.$google["custom_label_0"].'</g:custom_label_0>'."\n";
					}
					if($google["custom_label_1"])
					{
						$text .= "\t\t"
						.'<g:custom_label_1>'.$google["custom_label_1"].'</g:custom_label_1>'."\n";
					}
					if($google["custom_label_2"])
					{
						$text .= "\t\t"
						.'<g:custom_label_2>'.$google["custom_label_2"].'</g:custom_label_2>'."\n";
					}
					if($google["custom_label_3"])
					{
						$text .= "\t\t"
						.'<g:custom_label_3>'.$google["custom_label_3"].'</g:custom_label_3>'."\n";
					}
					if($google["custom_label_4"])
					{
						$text .= "\t\t"
						.'<g:custom_label_4>'.$google["custom_label_4"].'</g:custom_label_4>'."\n";
					}
					if($google["promotion_id"])
					{
						$text .= "\t\t"
						.'<g:promotion_id>'.$google["promotion_id"].'</g:promotion_id>'."\n";
					}
					
					if($google["shipping"])
					{
						$ss = explode(";", $google["shipping"]);
						foreach($ss as $s)
						{
							$c = explode(":", $s);
							if(count($c) == 4)
							{
								$text .= "\t\t"
								.'<g:shipping>'
								."\t\t"
								.'<g:country>'.$c[0].'</g:country>'
								."\t\t"
								.'<g:region>'.$c[1].'</g:region>'
								."\t\t"
								.'<g:service>'.$c[2].'</g:service>'
								."\t\t"
								.'<g:price>'.$c[3].' '.$currency.'</g:price>'
								."\t\t"
								.'</g:shipping>'."\n";
							}
						}
					}
					if($google["shipping_label"])
					{
						$text .= "\t\t"
						.'<g:shipping_label>'.$google["shipping_label"].'</g:shipping_label>'."\n";
					}
					if($google["shipping_weight"])
					{
						$text .= "\t\t"
						.'<g:shipping_weight>'.$google["shipping_weight"].' '.$weight_measure.'</g:shipping_weight>'."\n";
					}
					elseif($row["weight"])
					{
						$text .= "\t\t"
						.'<g:shipping_weight>'.$row["weight"].' '.$weight_measure.'</g:shipping_weight>'."\n";
					}
					if($google["shipping_length"])
					{
						$text .= "\t\t"
						.'<g:shipping_length>'.$google["shipping_length"].' '.$dimension_measure.'</g:shipping_length>'."\n";
					}
					elseif($row["length"])
					{
						$text .= "\t\t"
						.'<g:shipping_length>'.$row["length"].' '.$dimension_measure.'</g:shipping_length>'."\n";
					}
					if($google["shipping_width"])
					{
						$text .= "\t\t"
						.'<g:shipping_width>'.$google["shipping_width"].' '.$dimension_measure.'</g:shipping_width>'."\n";
					}
					elseif($row["width"])
					{
						$text .= "\t\t"
						.'<g:shipping_width>'.$row["width"].' '.$dimension_measure.'</g:shipping_width>'."\n";
					}
					if($google["shipping_height"])
					{
						$text .= "\t\t"
						.'<g:shipping_height>'.$google["shipping_height"].' '.$dimension_measure.'</g:shipping_height>'."\n";
					}
					elseif($row["height"])
					{
						$text .= "\t\t"
						.'<g:shipping_height>'.$row["height"].' '.$dimension_measure.'</g:shipping_height>'."\n";
					}
					if($google["max_handling_time"])
					{
						$text .= "\t\t"
						.'<g:max_handling_time>'.intval($google["max_handling_time"]).'</g:max_handling_time>'."\n";
					}
					if($google["min_handling_time"])
					{
						$text .= "\t\t"
						.'<g:min_handling_time>'.intval($google["min_handling_time"]).'</g:min_handling_time>'."\n";
					}
					$text .= "\t".'</entry>'."\n";
				}
			}
		}
		return $text;
	}

	/**
	 * Подготавливает текст для отображения в XML-файле
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

$class = new Shop_google($this->diafan);
$class->init();
exit;
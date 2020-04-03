<?php
/**
 * Экспорт товаров
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
 * Shop_export
 */
class Shop_export extends Diafan
{
	/**
	 * @var array конфигурация текущего экспорта
	 */
	private $config;

	/**
	 * @var array название полей списка
	 */
	private $select_values;

	/**
	 * @var array поля, заданные для текущего экспорта
	 */
	private $fields;

	/**
	 * Инициирует экспорт
	 *
	 * @return void
	 */
	public function init()
	{
		if(! $this->diafan->_users->roles("init", "shop/importexport", array(), 'admin'))
		{
			Custom::inc('includes/404.php');
		}
		$this->config = DB::query_fetch_array("SELECT * FROM {shop_import_category} WHERE id=%d AND trash='0' LIMIT 1", $_GET["rewrite"]);
		if(! $this->config)
		{
			Custom::inc('includes/404.php');
		}
		$this->config["cat_ids"] = array();
		if(! empty($this->config["cat_id"]))
		{
			$this->config["cat_ids"] = DB::query_fetch_value("SELECT element_id FROM {shop_category_parents} WHERE parent_id=%d AND trash='0'", $this->config["cat_id"], "element_id");
			$this->config["cat_ids"][] = $this->config["cat_id"];
		}
		$this->config["table"] = 'shop'.($this->config["type"] != 'good' ? "_".$this->config["type"] : "");

		$count = DB::query_result("SELECT COUNT(*) FROM {".$this->config["table"]."} WHERE site_id=%d"
		.($this->config["type"] == 'good' && $this->config["cat_ids"] ? " AND cat_id IN (".implode(',', $this->config["cat_ids"]).")" : '')
		." AND trash='0'", $this->config["site_id"]);

		$tmpname = $this->diafan->configmodules("tmpname", "shopexport");
		if(! $tmpname || ! file_exists(ABSOLUTE_PATH.'tmp/shop_export_'.$tmpname))
		{
			$tmpname = mt_rand(10000000, 999999999);
			$this->diafan->configmodules("tmpname", "shopexport", 0, 0, $tmpname);
			$i = 0;
		}
		else
		{
			$i = $this->diafan->configmodules("i", "shopexport");
		}
		$this->cache["rows_start"] = $i * $this->config["count_part"];

		if($this->cache["rows_start"] > $count)
		{
			$text = file_get_contents(ABSOLUTE_PATH.'tmp/shop_export_'.$tmpname);
			unlink(ABSOLUTE_PATH.'tmp/shop_export_'.$tmpname);
			$this->diafan->configmodules("tmpname", "shopexport", 0, 0, $tmpname);
			$this->diafan->configmodules("i", "shopexport", 0, 0, 0);
		}
		else
		{
			$this->init_config();

			if($this->config['encoding'] == 'cp1251')
			{
				$text = utf::to_windows1251($this->start());
			}
			else
			{
				$text = $this->start();
			}
			if($count > $this->config["count_part"])
			{
				$f = fopen(ABSOLUTE_PATH.'tmp/shop_export_'.$tmpname, ($i ? 'ab' : 'w'));
				fwrite($f, $text);
				fclose($f);
				$this->diafan->configmodules("i", "shopexport", 0, 0, ($i + 1));
				echo '
				Exported: '.($this->cache["rows_start"] + $this->config["count_part"] < $count ? $this->cache["rows_start"] + $this->config["count_part"] : $count).'
				<meta http-equiv="Refresh" content="0; url='.BASE_PATH.'shop/export/'.$_GET["rewrite"].'/?'.strtolower($this->diafan->uid()).'">';
				exit;
			}
		}
		$name = preg_replace('/[^a-z_ ]+/', '', str_replace(array(' ', '-'), '_', substr(strtolower($this->diafan->translit(TIT1)), 0, 50)));
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Cache-Control: max-age=86400');
		header("Content-type: text/plain");
		header("Content-Disposition: attachment; filename=shop_export_".$name.".csv");
		header('Content-transfer-encoding: binary');
		header("Connection: close");
		echo $text;
		exit;
	}

	/**
	 * Устанавливает настройки экспорта
	 *
	 * @return void
	 */
	private function init_config()
	{
		$this->config["end_string"] = htmlspecialchars_decode($this->config["end_string"]);
		if(! $this->config["count_part"])
		{
			$this->config["count_part"] = 200;
		}
		if(! $this->config['delimiter'])
		{
			$this->config["delimiter"] = ";";
		}
		if(! $this->config['sub_delimiter'])
		{
			$this->config["sub_delimiter"] = "|";
		}

		$k = 0;
		//получаем типы полей учавствующих в импорте
		$this->fields = DB::query_fetch_all("SELECT type, name, required, params FROM {shop_import} WHERE trash='0' AND cat_id=%d ORDER BY sort ASC", $this->config["id"]);
		foreach ($this->fields as &$row)
		{
			$row["params"] = unserialize($row["params"]);
			if ($row["type"] == "param")
			{
				$row["values"] = array();
				$row["param_type"] = DB::query_result("SELECT type FROM {shop_param} WHERE id=%d LIMIT 1", $row["params"]["id"]);
			}
		}
		if($this->config["type"] == 'good')
		{
			//получаем значения полей списков
			$rows = DB::query_fetch_all("SELECT id, [name] FROM {shop_param} WHERE trash='0' AND (type='select' OR type='multiple')");
			foreach ($rows as &$row)
			{
				$values = DB::query_fetch_key_value("SELECT id, [name] FROM {shop_param_select} WHERE param_id=%d", $row["id"], "id", "name");
				$this->select_values[$row["id"]] = array(
						"name" => $row["name"],
						"values" => $values,
					);
			}
		}
		switch($this->config["type"])
		{
			case 'good':
				$this->config["element_type"] = 'element';
				break;

			case 'category':
				$this->config["element_type"] = 'cat';
				break;

			default:
				$this->config["element_type"] = $this->config["type"];
				break;
		}

		//получаем значения валют
		$this->currency_values = DB::query_fetch_key_value("SELECT id, name FROM {shop_currency} WHERE trash='0'", "id", "name");
	}

	/**
	 * Старт вывода
	 *
	 * @return string
	 */
	private function start()
	{
		$text = '';
		if($this->config["header"] && ! $this->cache["rows_start"])
		{
			foreach($this->fields as $field)
			{
				$list[] = $field["name"];
			}
			$text .= $this->diafan->putcsv($list, $this->config["delimiter"], '"', true);
		}
		$rows = DB::query_range_fetch_all("SELECT * FROM {".$this->config["table"]."} WHERE site_id=%d"
			.($this->config["type"] == 'good' && $this->config["cat_ids"] ? " AND cat_id IN (".implode(',', $this->config["cat_ids"]).")" : '')
			." AND trash='0'", $this->config["site_id"], $this->cache["rows_start"], $this->config["count_part"]);
		foreach ($rows  as $row)
		{
			$list = array();
			if(isset($prices))
			{
				unset($prices);
			}
			foreach ($this->fields as $k => $field)
			{
				switch($field["type"])
				{
					case 'id':
						switch($field["params"]["type"])
						{
							case 'article':
								$list[] = $row["article"];
								break;

							case 'site':
								$list[] = $row["id"];
								break;

							default:
								$list[] = $row["import_id"];
								break;
						}
						break;
					case 'parent':
						$v = '';
						switch($field["params"]["type"])
						{
							case 'site':
								$v = $row["parent_id"];
								break;

							case 'name':
								if($row["parent_id"])
								{
									$v = DB::query_result("SELECT [name] FROM {shop_category} WHERE id=%d LIMIT 1", $row["parent_id"]);
								}
								break;

							default:
								if($row["parent_id"])
								{
									$v = DB::query_result("SELECT import_id FROM {shop_category} WHERE id=%d LIMIT 1", $row["parent_id"]);
								}
								break;
						}
						$list[] = $v;
						break;
					case 'article':
					case 'no_buy':
					case 'hit':
					case 'new':
					case 'action':
					case 'is_file':
					case 'show_yandex':
					case 'show_google':
					case 'map_no_show':
					case 'sort':
					case 'admin_id':
					case 'theme':
					case 'view':
					case 'view_rows':
					case 'view_element':
					case 'changefreq':
					case 'priority':
					case 'weight':
					case 'length':
					case 'width':
					case 'height':
						$list[] = $row[$field["type"]];
						break;
					case 'name':
					case 'text':
					case 'anons':
					case 'keywords':
					case 'descr':
					case 'title_meta':
					case 'act':
					case 'canonical':
					case 'measure_unit':
						$list[] = $row[$field["type"]._LANG];
						break;
					case 'price':
						$values = array();
						if(! isset($prices))
						{
							$prices = $this->diafan->_shop->price_get_base($row["id"]);
							if($prices && ! empty($field["params"]["image"]))
							{
								foreach ($prices as $price)
								{
									$price_ids[] = $price["price_id"];
								}
								$price_images = DB::query_fetch_key_value("SELECT i.name, p.price_id FROM {shop_price_image_rel} AS p
								INNER JOIN {images} AS i ON i.id=p.image_id
								WHERE p.price_id in (%s)",
								implode(",", $price_ids), "price_id", "name");
							}
						}
						foreach ($prices as $price)
						{
							$value = number_format($price["price"], 2 , ".", "");
							if(! empty($field["params"]["count"]))
							{
								$value .= $field["params"]["delimitor"].$price["count_goods"];
							}
							if(! empty($field["params"]["old_price"]))
							{
								$value .= $field["params"]["delimitor"].$price["old_price"];
							}
							if(! empty($field["params"]["currency"]))
							{
								if($price["currency_id"] && $field["params"]["select_currency"] == 'value')
								{
									$price["currency_id"] = $this->currency_values[$price["currency_id"]];
								}
								$value .= $field["params"]["delimitor"].$price["currency_id"];
							}
							if(! empty($field["params"]["image"]))
							{
								$value .= $field["params"]["delimitor"].(! empty($price_images[$price["price_id"]]) ? $price_images[$price["price_id"]] : '');
							}
							foreach ($price["param"] as $k => $v)
							{
								if(empty($v) || empty($k))
									continue;
								if($field["params"]["select_type"] == 'value')
								{
									$v = $this->select_values[$k]["values"][$v];
									$k = $this->select_values[$k]["name"];
								}
								$value .= $field["params"]["delimitor"].$k.'='.$v;
							}
							$values[] = $value;
						}
						$list[] = implode($this->config["sub_delimiter"], $values);
						break;
					case 'count':
						$values = array();
						if(! isset($prices))
						{
							$prices = $this->diafan->_shop->price_get_base($row["id"]);
						}
						foreach ($prices as $price)
						{
							$value = $price["count_goods"];
							foreach ($price["param"] as $k => $v)
							{
								if(empty($v) || empty($k))
									continue;
								if($field["params"]["select_type"] == 'value')
								{
									$v = $this->select_values[$k]["values"][$v];
									$k = $this->select_values[$k]["name"];
								}
								$value .= $field["params"]["delimitor"].$k.'='.$v;
							}
							$values[] = $value;
						}
						$list[] = implode($this->config["sub_delimiter"], $values);
						break;
					case 'cats':
						if($this->config["type"] == 'good')
						{
							$table_cat_rel = 'shop_category_rel';
						}
						else
						{
							$table_cat_rel = 'shop_'.$this->config["type"].'_category_rel';
						}
						switch($field["params"]["type"])
						{
							case 'site':
								$cats = DB::query_fetch_key_value("SELECT cat_id as cat FROM {".$table_cat_rel."} WHERE element_id=%d AND trash='0'", $row["id"], "cat", "cat");
								break;

							case 'name':
								$cats = DB::query_fetch_key_value("SELECT s.[name], s.id FROM {".$table_cat_rel."} AS r INNER JOIN {shop_category} AS s ON s.id=r.cat_id WHERE r.element_id=%d AND r.trash='0'", $row["id"], "id", "name");
								break;

							default:
								$cats = DB::query_fetch_key_value("SELECT s.import_id, s.id FROM {".$table_cat_rel."} AS r INNER JOIN {shop_category} AS s ON s.id=r.cat_id WHERE r.element_id=%d AND r.trash='0'", $row["id"], "id", "import_id");
								break;
						}
						$value = '';
						if(isset($row["cat_id"]) && ! empty($cats[$row["cat_id"]]))
						{
							$value = $cats[$row["cat_id"]];
							unset($cats[$row["cat_id"]]);
							if($cats)
							{
								$value .= $this->config["sub_delimiter"];
							}
						}
						$value .= implode($this->config["sub_delimiter"], $cats);
						$list[] = $value;
						break;
					case 'brand':
						$value = '';
						switch($field["params"]["type"])
						{
							case 'site':
								$value = $row["brand_id"];
								break;

							case 'name':
								if(! isset($brands))
								{
									$brands = DB::query_fetch_key_value("SELECT [name], id FROM {shop_brand} WHERE trash='0'", "id", "name");
								}
								if(! empty($brands[$row["brand_id"]]))
								{
									$value = $brands[$row["brand_id"]];
								}
								break;

							default:
								if(! isset($brands))
								{
									$brands = DB::query_fetch_key_value("SELECT import_id, id FROM {shop_brand} WHERE trash='0'", "id", "import_id");
								}
								if(! empty($brands[$row["brand_id"]]))
								{
									$value = $brands[$row["brand_id"]];
								}
								break;
						}
						$list[] = $value;
						break;
					case 'param':
						if($field["param_type"] == 'select' || $field["param_type"] == 'multiple')
						{
							$params = DB::query_fetch_value("SELECT value".$this->diafan->_languages->site." AS value FROM {shop_param_element} WHERE  param_id=%d AND element_id=%d AND trash='0'", $field["params"]["id"], $row["id"], "value");
							foreach ($params as &$param)
							{
								if($field["params"]["select_type"] == 'value')
								{
									$param = $this->select_values[$field["params"]["id"]]["values"][$param];
								}
							}
							$list[] = implode($this->config["sub_delimiter"], $params);
						}
						elseif($field["param_type"] == 'images')
						{
							$is = array();
							$images = DB::query_fetch_all("SELECT * FROM {images} WHERE module_name='shop' AND element_type='%s' AND trash='0' AND element_id=%d AND param_id=%d", $this->config["element_type"], $row["id"], $field["params"]["id"]);
							foreach($images as $i)
							{
								/*if(! empty($field["params"]["directory"]))
								{
									File::copy_file(ABSOLUTE_PATH.USERFILES.'/original/'.($i["folder_num"] ? $i["folder_num"].'/' : '').$i["name"], $field["params"]["directory"].'/'.$i["name"]);
								}*/
								$is[] = $i["name"];
							}
							$list[] = implode($this->config["sub_delimiter"], $is);
							break;
						}
						elseif($field["param_type"] == 'attachments')
						{
							$as = array();
							$atts = DB::query_fetch_all("SELECT * FROM {attachments} WHERE module_name='shop' AND trash='0' AND element_id=%d AND param_id=%d", $row["id"], $field["params"]["id"]);
							foreach($atts as $a)
							{
								if($a["is_image"])
								{
									$as[] = $a["id"].'_'.$a["name"];
									/*if(! empty($field["params"]["directory"]))
									{
										File::copy_file(ABSOLUTE_PATH.USERFILES.'/shop/imgs/'.$a["name"], $field["params"]["directory"].'/'.$a["id"].'_'.$a["name"]);
									}*/
								}
								else
								{
									$as[] = $a["id"].'_'.$a["name"];
									/*if(! empty($field["params"]["directory"]))
									{
										File::copy_file(ABSOLUTE_PATH.USERFILES.'/shop/files/'.$a["id"], $field["params"]["directory"].'/'.$a["id"].'_'.$a["name"]);
									}*/
								}
							}
							$list[] = implode($this->config["sub_delimiter"], $as);
							break;
						}
						else
						{
							$value_name = (in_array($field["param_type"], array('text', 'textarea', 'editor')) ? '[value]' : 'value'.$this->diafan->_languages->site);
							$list[] = DB::query_result("SELECT ".$value_name." FROM {shop_param_element} WHERE  param_id=%d AND element_id=%d AND trash='0' LIMIT 1", $field["params"]["id"], $row["id"]);
						}
						break;
					case 'images':
						$is = array();
						$images = DB::query_fetch_all("SELECT id, folder_num, name, [alt], [title] FROM {images} WHERE module_name='shop' AND element_type='%s' AND trash='0' AND element_id=%d AND param_id=0", $this->config["element_type"], $row["id"]);
						foreach($images as $i)
						{
							/*if(! empty($field["params"]["directory"]))
							{
								File::copy_file(ABSOLUTE_PATH.USERFILES.'/original/'.($i["folder_num"] ? $i["folder_num"].'/' : '').$i["name"], $field["params"]["directory"].'/'.$i["name"]);
							}*/
							if(! empty($field["params"]["second_delimitor"]))
							{
								$i["name"] .= $field["params"]["second_delimitor"].$i['alt'].$field["params"]["second_delimitor"].$i["title"];
							}
							$is[] = $i["name"];
						}
						$list[] = implode($this->config["sub_delimiter"], $is);
						break;
					case 'rel_goods':
						switch($field["params"]["type"])
						{
							case 'article':
								$rels = DB::query_fetch_value("SELECT s.article as rel FROM {shop_rel} AS r INNER JOIN {shop} AS s ON s.id=r.rel_element_id WHERE r.element_id=%d AND r.trash='0'", $row["id"], "rel");
								break;

							case 'site':
								$rels = DB::query_fetch_value("SELECT rel_element_id as rel FROM {shop_rel} WHERE element_id=%d AND trash='0'", $row["id"], "rel");
								break;

							default:
								$rels = DB::query_fetch_value("SELECT s.import_id as rel FROM {shop_rel} AS r INNER JOIN {shop} AS s ON s.id=r.rel_element_id WHERE r.element_id=%d AND r.trash='0'", $row["id"], "rel");
								break;
						}
						$list[] = implode($this->config["sub_delimiter"], $rels);
						break;

					case 'rewrite':
						$list[] = DB::query_result("SELECT rewrite FROM {rewrite} WHERE module_name='shop' AND element_id=%d AND element_type='%s' AND trash='0' LIMIT 1", $row["id"], $this->config["element_type"]);
						break;

					case 'redirect':
						$r = DB::query_fetch_array("SELECT redirect, code FROM {redirect} WHERE module_name='shop' AND element_id=%d AND element_type='%s' AND trash='0' LIMIT 1", $row["id"], $this->config["element_type"]);
						$v = '';
						if($r)
						{
							$v = $r["redirect"];
							if($r["code"] != 301 && ! empty($field["params"]["second_delimitor"]))
							{
								$v .= $field["params"]["second_delimitor"].$r["code"];
							}
						}
						$list[] = $v;
						break;

					case 'menu':
						if($field["params"]["id"])
						{
							$in_menu = DB::query_result("SELECT id FROM {menu} WHERE cat_id=%d AND module_name='shop' AND element_id=%d AND element_type='%s' trash='0' AND [act]='1' LIMIT 1", $field["params"]["id"], $row["id"], $this->config["element_type"]);
							$list[] = $in_menu ? '1' : '0';
						}
						break;

					case 'yandex':
						$list[] = str_replace("\n", $this->config["sub_delimiter"], $row["yandex"]);
						break;

					case 'google':
						$list[] = str_replace("\n", $this->config["sub_delimiter"], $row["google"]);
						break;

					case 'access':
						if($row["access"])
						{
							$access = DB::query_fetch_value("SELECT role_id FROM {access} WHERE element_id=%d AND module_name='shop' AND element_type='%s' AND trash='0'", $row["id"], $this->config["element_type"], "role_id");
							$list[] = implode($this->config["sub_delimiter"], $access);
						}
						break;

					case 'date_start':
					case 'date_finish':
						$list[] = date('d.m.Y H:i', $row[$field["type"]]);
						break;
					case 'empty':
						$list[] = '';
						break;
				}
			}
			$text .= $this->diafan->putcsv($list, $this->config["delimiter"], '"', true);
		}
		return $text;
	}
}

$shop_export = new Shop_export($this->diafan);
$shop_export->init();

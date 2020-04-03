<?php
/**
 * Обработка POST-запросов в административной части модуля
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2019 OOO «Диафан» (http://www.diafan.ru/)
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
 * Order_admin_action
 */
class Order_admin_action extends Action_admin
{
	/**
	 * Вызывает обработку POST-запросов
	 *
	 * @return void
	 */
	public function init()
	{
		if ( ! empty($_POST["action"]))
		{
			switch ($_POST["action"])
			{
				case 'show_order_goods':
					$this->show_order_goods();
					break;

				case 'add_order_good':
					$this->add_order_good();
					break;

				case 'new_order':
					$this->new_order();
					break;
			}
		}
	}

	/**
	 * Подгружает список товаров для добавления в заказ
	 *
	 * @return void
	 */
	private function show_order_goods()
	{
		if (empty($_POST["order_id"]))
		{
			$_POST["order_id"] = 0;
		}
		$nastr = 18;
		$list = '';
		if (empty($_POST["page"]))
		{
			$start = 0;
			$page = 1;
			if ( ! isset($_POST["search"]) && ! isset($_POST["cat_id"]))
			{
				$list .= '<div class="fa fa-close ipopup__close"></div>
				<div class="ipopup__heading">'.$this->diafan->_('Товары').'</div>
				<form>
				<div class="infofield">'.$this->diafan->_('Поиск').'</div> <input type="text" size="30" class="order_goods_search" placeholder="'.$this->diafan->_('Введите несколько символов для поиска').'">
				';

				if($this->diafan->configmodules("cat", "shop"))
				{
					$cats = DB::query_fetch_key_array("SELECT id, [name], parent_id FROM {shop_category} WHERE trash='0' ORDER BY sort ASC", "parent_id");
					$vals = array();
					if(! empty($_POST["cat_id"]))
					{
						$vals[] = $this->diafan->filter($_POST, "int", "cat_id");
					}
					$list.= ' <select name="cat_id" class="order_goods_cat_id"><option value="">'.$this->diafan->_('Все').'</option>'.$this->diafan->get_options($cats, $cats[0], $vals).'</select>';
				}
				$list.= '</form><div class="order_all_goods_container">';
			}
		}
		else
		{
			$page = intval($_POST["page"]);
			$start = ($page - 1) * $nastr;
		}
		$list .= '<div class="rel_all_elements">';

		$where = '';
		if(! empty($_POST["cat_id"]))
		{
			$where = " AND cat_id=".$this->diafan->filter($_POST, "int", "cat_id");
		}
		$where .= " ORDER BY sort DESC";

		if ( ! empty($_POST["search"]))
		{
			$count = DB::query_result("SELECT COUNT(*) FROM {shop} WHERE trash='0' AND (LOWER([name]) LIKE LOWER('%%%h%%') OR LOWER(article) LIKE LOWER('%%%h%%'))".$where, $_POST["search"], $_POST["search"]);
			$rows = DB::query_range_fetch_all("SELECT id, [name], no_buy FROM {shop} WHERE trash='0' AND (LOWER([name]) LIKE LOWER('%%%h%%') OR LOWER(article) LIKE LOWER('%%%h%%'))".$where, $_POST["search"], $_POST["search"], $start, $nastr);
		}
		else
		{
			$count = DB::query_result("SELECT COUNT(*) FROM {shop} WHERE trash='0'".$where);
			$rows = DB::query_range_fetch_all("SELECT id, [name], no_buy FROM {shop} WHERE trash='0'".$where, $start, $nastr);
		}
		$user_id = DB::query_result("SELECT user_id FROM {shop_order} WHERE id=%d LIMIT 1", $_POST["order_id"]);
		foreach ($rows as &$row)
		{
			$this->diafan->_shop->price_prepare_base($row["id"]);
			$ids[] = $row["id"];
		}
		$param_select_ids = array();
		foreach($rows as &$row)
		{
			$row["prices"] = $this->diafan->_shop->price_get_base($row["id"], true);
			foreach($row["prices"] as &$pr)
			{
				if(! empty($pr["param"]))
				{
					foreach($pr["param"] as $p)
					{
						if(! in_array($p, $param_select_ids))
						{
							$param_select_ids[] = $p;
						}
					}
				}
				if($pr["currency_id"])
				{
					if(! isset($currencies))
					{
						$currencies = DB::query_fetch_key("SELECT id, exchange_rate, name FROM {shop_currency} WHERE trash='0'", "id");
					}
					$pr["price"] = $currencies[$pr["currency_id"]]["exchange_rate"] * $pr["price"];
				}
			}
		}
		if($param_select_ids)
		{
			$param_select = DB::query_fetch_key_value("SELECT id, [name] FROM {shop_param_select} WHERE id IN (%s)", implode(",", $param_select_ids), "id", "name");
		}
		if($rows)
		{
			$imgs = DB::query_fetch_key("SELECT name, folder_num, element_id FROM {images} WHERE element_id IN(%s) AND module_name='shop' AND element_type='element' AND trash='0' ORDER BY sort DESC", implode(',', $ids), "element_id");
		}
		$goods = array();
		if(! empty($_POST["order_id"]))
		{
			$goods = DB::query_fetch_value("SELECT good_id FROM {shop_order_goods} WHERE order_id=%d", $_POST["order_id"], "good_id");
		}
		if(! empty($_POST["new_goods"]))
		{
			foreach($_POST["new_goods"] as $good_id)
			{
				$good_id = intval($good_id);
				if(! in_array($good_id, $goods))
				{
					$goods[] = $good_id;
				}
			}
		}
		foreach ($rows as &$row)
		{
			if(! $row["name"])
			{
				$row["name"] = $row["id"];
			}
			$list .= '<div class="rel_module order_good'.(in_array($row["id"], $goods) ? ' rel_module_selected' : '').'">
			<div>';
			if(! empty($imgs[$row["id"]]))
			{
				$list .= '<img src="'.BASE_PATH.USERFILES.'/small/'.($imgs[$row["id"]]["folder_num"] ? $imgs[$row["id"]]["folder_num"].'/' : '').$imgs[$row["id"]]["name"].'">';
			}
			if(count($row["prices"]) > 1)
			{
				$list .= '<a href="javascript:void(0)" class="order_good_show_price">'.$row["name"].'</a>';
				$list .= '<div class="order_good_all_price hide"><div class="fa fa-close order_good_price_close"></div>';
				foreach ($row["prices"] as $price)
				{
					if($price["param"])
					{
						$k = 0;
						foreach($price["param"] AS $p)
						{
							if(! empty($param_select[$p]))
							{
								if($k > 0)
								{
									$list .= ', ';
								}
								$list .= $param_select[$p];
							}
							$k++;
						}
					}
					if(! $price["count_goods"] && $this->diafan->configmodules('use_count_goods'))
					{
						$list .= $this->diafan->_shop->price_format($price["price"]).' '.$this->diafan->configmodules("currency", "shop").' <b>'.$this->diafan->_('Товар временно отсутствует').'</b><br>';
					}
					else
					{
						$list .= ' <a href="javascript:void(0)" price_id="'.$price["id"].'" class="order_good_add">'.$this->diafan->_shop->price_format($price["price"]).' '.$this->diafan->configmodules("currency", "shop").'</a><br>';
					}
				}
				$list .= '</div>';
			}
			elseif($row["prices"])
			{
				$price = $row["prices"][0];
				if(! empty($row["no_buy"]) || ! $price["count_goods"] && $this->diafan->configmodules('use_count_goods'))
				{
					$list .= '<b>'.$this->diafan->_('Товар временно отсутствует').'</b>';
				}
				else
				{
					$list .= ' <a href="javascript:void(0)" price_id="'.$price["id"].'" class="order_good_add">'.$row["name"].'<br>'.$this->diafan->_shop->price_format($price["price"]).' '.$this->diafan->configmodules("currency", "shop").'</a>';
				}
			}
			elseif($this->diafan->configmodules("buy_empty_price", "shop"))
			{
				$list .= ' <a href="javascript:void(0)" good_id="'.$row["id"].'" class="order_good_add">'.$row["name"].'</a>';
			}
			$list .= '</div>
			</div>';
		}
		$list .= '</div><div class="paginator order_goods_navig">';
		for ($i = 1; $i <= ceil($count / $nastr); $i ++ )
		{
			if ($i != $page)
			{
				$list .= '<a href="javascript:void(0)" page="'.$i.'">'.$i.'</a> ';
			}
			else
			{
				$list .= '<span class="active">'.$i.'</span> ';
			}
		}
		$list .= '</div>';
		if (empty($_POST["page"]) && ! isset($_POST["search"]))
		{
			$list .= '</div>';
		}

		$this->result["data"] = $list;
	}

	/**
	 * Добавляет выбранный товар в заказ
	 *
	 * @return void
	 */
	private function add_order_good()
	{
		if(! $this->diafan->_users->roles('edit', 'order'))
		{
			return;
		}
		if (empty($_POST["price_id"]) && empty($_POST["good_id"]))
		{
			return;
		}
		$format_price = intval($this->diafan->configmodules("format_price_1", "shop"));
		$depend = '';
		if(! empty($_POST["price_id"]))
		{
			$price = DB::query_fetch_array("SELECT price_id, price, old_price, good_id, discount_id, id FROM {shop_price} WHERE id=%d LIMIT 1", $_POST["price_id"]);
			$where = array();
			$params = array();
			$rows = DB::query_fetch_all("SELECT param_id, param_value FROM {shop_price_param} WHERE price_id=%d AND trash='0'", $price["price_id"]);
			foreach ($rows as $row)
			{
				$params[$row["param_id"]] = $row["param_value"];
				$where[] = "s.param_id=".intval($row["param_id"])." AND s.value=".intval($row["param_value"]);
			}
			if($params)
			{
				foreach ($params as $id => $value)
				{
					if(! $value)
						continue;
					$param_name  = DB::query_result("SELECT [name] FROM {shop_param} WHERE id=%d LIMIT 1", $id);
					$param_value = DB::query_result("SELECT [name] FROM {shop_param_select} WHERE id=%d LIMIT 1", $value);
					$depend .= ($depend ? ', ' : '').$param_name.': '.$param_value;
				}
			}
			$good = DB::query_fetch_array("SELECT id, [name], article, cat_id, [measure_unit] FROM {shop} WHERE id=%d LIMIT 1", $price["good_id"]);
			$img = DB::query_fetch_array("SELECT i.name, i.folder_num FROM {images} AS i
			LEFT JOIN {shop_price_image_rel} AS r ON r.image_id=i.id AND r.price_id=%d
			WHERE i.element_id=%d AND i.module_name='shop' AND i.element_type='element' AND i.trash='0'
			ORDER BY r.image_id DESC, i.sort ASC LIMIT 1",
			$price["price_id"], $price["good_id"]);

			$old_price = $price["old_price"] ? $price["old_price"] : $price["price"];
			if($price["discount_id"])
			{
				$d = DB::query_fetch_array("SELECT discount, deduction FROM {shop_discount} WHERE id=%d LIMIT 1", $price["discount_id"]);
				$discount = $d["discount"] ? $d["discount"].'%' : $d["deduction"].' '.$this->diafan->configmodules("currency", "shop");
			}
		}
		else
		{
			$good = DB::query_fetch_array("SELECT id, [name], article, cat_id, [measure_unit] FROM {shop} WHERE id=%d LIMIT 1", $_POST["good_id"]);
			$img = array();
			$old_price = 0;
			$discount = '';
		}
		$cat_name = $good["cat_id"] ? DB::query_result("SELECT [name] FROM {shop_category} WHERE id=%d", $good["cat_id"]) : '';

		$this->result["data"] = '
		<li class="item">
		<div class="item__in">
			<div class="sum no_important ipad">'.($img ? '<img src="'.BASE_PATH.USERFILES.'/small/'.($img["folder_num"] ? $img["folder_num"].'/' : '').$img["name"].'">' : '').'</div>

			<div class="name"><a href="'.BASE_PATH_HREF.'shop/edit'.$good["id"].'/" good_id="'.$good["id"].'" class="js_order_new_good">'.$good["name"].' '.$depend.' ('.$good["article"].')</a>';
			if(! empty($_POST["price_id"]))
			{
				$this->result["data"] .= '<input type="hidden" name="new_prices[]" value="'.$price["id"].'">';
			}
			else
			{
				$this->result["data"] .= '<input type="hidden" name="new_goods[]" value="'.$good["id"].'">';
			}
			$this->result["data"] .= '<div class="categories">'.$cat_name.'</div></div>

			<div class="item__adapt mobile">
				<i class="fa fa-bars"></i>
				<i class="fa fa-caret-up"></i>
			</div>
			<div class="item__seporator mobile"></div>

			<div class="num no_important ipad"><nobr><input type="text" name="';
			if(! empty($_POST["price_id"]))
			{
				$this->result["data"] .= 'new_count_prices';
			}
			else
			{
				$this->result["data"] .= 'new_count_goods';
			}
			$this->result["data"] .= '[]" value="1" size="2">';
			if($good["measure_unit"])
			{
				$this->result["data"] .= ' '.$good["measure_unit"];
			}
			$this->result["data"] .= '</nobr></div>';

			if(! empty($_POST["price_id"]))
			{
				$this->result["data"] .= '<div class="num no_important ipad">'.$this->diafan->_shop->price_format($old_price).'</div>

				<div class="num no_important ipad">'.(! empty($price["discount_id"]) ? '<a href="'.BASE_PATH_HREF.'shop/discount/edit'.$price["discount_id"].'/">'.$discount.'</a>' : '').'</div>

				<div class="num no_important ipad"><input type="text" name="new_price_goods[]" value="'.number_format($price["price"], $format_price, ".", "").'" size="4"></div>

				<div class="num">'.$this->diafan->_shop->price_format($price["price"]).'</div>';
			}
			else
			{
				$this->result["data"] .= '<div class="num no_important ipad"></div>

				<div class="num no_important ipad"></div>

				<div class="num no_important ipad"></div>

				<div class="num"></div>';
			}

			$this->result["data"] .= '<div class="num"><a href="javascript:void(0)" confirm="'.$this->diafan->_('Вы действительно хотите удалить товар из заказа?').'" class="delete delete_order_good"><i class="fa fa-close" title="'.$this->diafan->_('Удалить').'"></i></a></div>

		</div>';
		if(! empty($_POST["price_id"]))
		{
			$additional_costs = DB::query_fetch_all("SELECT a.id, a.[name], a.price, a.percent, r.summ, r.element_id FROM {shop_additional_cost} AS a
			INNER JOIN {shop_additional_cost_rel} AS r ON r.element_id=%d AND r.additional_cost_id=a.id
			WHERE a.trash='0' AND a.shop_rel='1'
			ORDER BY a.sort ASC", $good["id"]);
			foreach($additional_costs as $a)
			{
				if($a["percent"])
				{
					$a["summ"] = ($price["price"] * $a["percent"]) / 100;
				}
				elseif(! $a["summ"])
				{
					$a["summ"] = $a["price"];
				}
				$this->result["data"] .= '
				<div class="item__in">
					<div class="sum no_important ipad"></div>

					<div class="name">'.$a["name"].'</div>

					<div></div>
					<div></div>
					<div></div>

					<div class="num">
					<input name="additional_cost_id_price_'.$price["id"].'_'.$a["id"].'" id="additional_cost_id_price_'.$price["id"].'_'.$a["id"].'" value="1" type="checkbox" title="'.$this->diafan->_('Добавлено к заказу').'">
					<label for="additional_cost_id_price_'.$price["id"].'_'.$a["id"].'">
					<input type="text" name="summ_additional_cost_price_'.$price["id"].'_'.$a["id"].'" value="'.number_format($a["summ"], $format_price, ".", "").'" size="4"></label></div>

					<div class="num no_important ipad">'.number_format($a["summ"], $format_price, ".", "").'
					</div>
					<div class="num no_important ipad"></div>
				</div>';
			}
		}
		$this->result["data"] .= '</li>';
	}

	/**
	 * Проверяет наличие новых заказов
	 *
	 * @return void
	 */
	private function new_order()
	{
		$last_order_id = $this->diafan->filter($_POST, "int", "last_order_id");

		$this->result["next_order_id"] = DB::query_result("SELECT id FROM {shop_order} WHERE id>%d AND trash='0' LIMIT 1", $last_order_id);
	}
}

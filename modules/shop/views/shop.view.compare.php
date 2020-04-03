<?php
/**
 * Шаблон страницы сравнения товаров
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

if (empty($result['rows']))
{
	echo $this->diafan->_('Не выбраны товары для сравнения.');
	return;
}

array_unshift($this->diafan->_site->js_view, 'js/jcarousellite_1.0.1.min.js');

$result["one_click"] = false;

echo '
<div class="shop_compare_page">
	<div class="shop_compare_left">
		<div class="js_shop_compare_description shop_compare_description">
			<h3>'.$this->diafan->_('Показать').'</h3>
			<div><a href="javascript:void(0)" class="js_shop_compare_all shop_compare_all compare_all">'.$this->diafan->_('Все параметры').'</a></div>
			<div><a href="javascript:void(0)" class="js_shop_compare_difference shop_compare_difference compare_difference">'.$this->diafan->_('Только отличающиеся').'</a></div>
		</div>';

if(! empty($result['existed_params']))
{
	echo '<div class="js_shop_existed_params shop_existed_params">';

	foreach ($result['existed_params'] as $param)
	{
		echo '<div class="js_shop_param_existed shop_param_existed param_id_'.$param['id']
		. (in_array($param['id'], $result["param_differences"]) ? ' js_shop_param_difference shop_param_difference ' : '')
		. '">'.$param['name'].'</div>';
	}

	echo '</div>';
}

echo '
		</div>
		<div class="shop_compare_list">
		<div class="carousel default">

			<div class="jCarouselLite">
			<ul>';

foreach ($result["rows"] as $row)
{
	echo '<li class="js_shop shop shop_compared_goods_'.$row["id"].'">
			<div class="js_shop_basic shop_basic">
				<a class="shop_compared_remove js_shop_compared_remove" href="#" title="'.$this->diafan->_('Удалить из списка для сравнения', false).'" data-id="'.$row["id"].'" data-site_id="'.$row["site_id"].'"><img src="'.BASE_PATH.Custom::path('img/remove.png').'" width="15" height="15" alt="'.$this->diafan->_('Удалить из списка для сравнения', false).'"></a>

				<div class="shop_name">
				<a href="'.BASE_PATH_HREF.$row["link"].'">'.$row["name"].'</a>
				</div>';

	if(! empty($row["img"]))
	{
		echo '<div class="shop_img">';
		foreach ($row["img"] as $img)
		{
			switch ($img["type"])
			{
				case 'animation':
					echo '<a href="'.BASE_PATH.$img["link"].'" data-fancybox="gallery'.$row["id"].'shop">';
					break;
				case 'large_image':
					echo '<a href="'.BASE_PATH.$img["link"].'" rel="large_image" width="'.$img["link_width"].'" height="'.$img["link_height"].'">';
					break;
				default:
					echo '<a href="'.BASE_PATH_HREF.$img["link"].'">';
					break;
			}
			echo '<img src="'.$img["src"].'" width="'.$img["width"].'" height="'.$img["height"].'" alt="'.$img["alt"].'" title="'.$img["title"].'" image_id="'.$img["id"].'" class="js_shop_img">'
			. '</a> ';
		}
		echo '</div>';
	}

	if(! empty($row["article"]))
	{
		echo '<div class="shop_article">'.$this->diafan->_('Артикул')
		. ': <span class="shop_article_value">'.$row["article"].'</span></div>';
	}

	if(! empty($row["discount"]))
	{
		echo '<div class="shop_discount">'.$this->diafan->_('Скидка')
		. ': <span class="shop_discount_value">'.$row["discount"].' '.$row["discount_currency"]
		. ($row["discount_finish"] ? ' ('.$this->diafan->_('до').' '.$row["discount_finish"].')' : '')
		. '</span></div>';
	}
	echo $this->get('buy_form', 'shop', array("row" => $row, "result" => $result));

	echo '</div>';
	echo '<div class="js_shop_compare_param shop_compare_param">';
		echo $this->get('compare_param', 'shop', array("params" => $row["param"], "id" => $row["id"], "existed_params" => $result['existed_params'], "param_differences" => $result["param_differences"]));
	echo '</div>';

	echo '</li>';
}

echo '     </ul>
		</div>';
if (count($result['rows']) > 3)
{
	echo '<button class="prev button">&#171;</button></span><button class="next button">&#187;</button></span>';
}

echo '<div class="clear"></div>';
echo '</div>
</div>
</div>';


echo '
<form method="POST" action="" class="shop_compare_form ajax">
<input type="hidden" name="module" value="shop">
<input type="hidden" name="action" value="compare_delete_goods">

<input type="submit" value="'.$this->diafan->_('Очистить список сравнения', false).'">
</form>';

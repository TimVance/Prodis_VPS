<?php
/**
 * Шаблон элементов в списке товаров
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

if(empty($result['rows'])) return false;

foreach ($result['rows'] as $row)
{		
	echo '<div class="js_shop shop-item shop">';

	//вывод изображений товара
	if(! empty($row["img"]))
	{
		echo '<div class="shop_img shop-photo">';
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
			echo '<img src="'.$img["src"].'" alt="'.$img["alt"].'" title="'.$img["title"].'" image_id="'.$img["id"].'" class="js_shop_img">';
			echo '<span class="shop-photo-labels">';
					if(! empty($row['hit']))
					{
						echo '<img src="' . BASE_PATH . Custom::path('img/label_hot.png').'"/>';
					}
					if(! empty($row['action']))
					{
						echo '<img src="' . BASE_PATH . Custom::path('img/label_special.png').'"/>';
					}
					if(! empty($row['new']))
					{
						echo '<img src="' . BASE_PATH . Custom::path('img/label_new.png').'"/>';					
					}
				echo '</span>';
			echo '</a> ';
			if(! empty($result['search']))
			{
				break;
			}
		}
		if(empty($result['search']))
		{
			echo '<span class="js_shop_wishlist shop_wishlist shop-like'.(! empty($row["wish"]) ? ' active' : '').'"><i class="fa fa-heart'.(! empty($row["wish"]) ? '' : '-o').'">&nbsp;</i></span>';
		}

		echo '</div>';
	}

	//вывод названия и ссылки на товар	
	echo '<a href="'.BASE_PATH_HREF.$row["link"].'" class="shop-item-title">'.$row["name"].'</a>';
	//рейтинг товара
	if(! empty($row["rating"]))
	{
		echo ' '.$row["rating"];
	}		

	//вывод краткого описания товара
	if(! empty($row["anons"]))
	{
		echo '<div class="shop_anons">'.$this->htmleditor($row['anons']).'</div>';
	}

	//вывод производителя
	if(! empty($row["brand"]))
	{
		echo '<div class="shop_brand">';
		echo $this->diafan->_('Производитель').': ';
		echo '<a href="'.BASE_PATH_HREF.$row["brand"]["link"].'">'.$row["brand"]["name"].'</a>';
		echo '</div>';
	}

	//вывод артикула
	if(! empty($row["article"]))
	{
		echo '<div class="shop_article">';
		echo $this->diafan->_('Артикул').': ';
		echo '<span class="shop_article_value">'.$row["article"].'</span>';
		echo '</div>';
	}

	//вывод параметров товара
	if(empty($result['search']) && ! empty($row["param"]))
	{
		echo $this->get('param', 'shop', array("rows" => $row["param"], "id" => $row["id"]));
	}

	//вывод скидки на товар
	if(! empty($row["discount"]))
	{
		echo '<div class="shop_discount">'.$this->diafan->_('Скидка').': <span class="shop_discount_value">'.$row["discount"].' '.$row["discount_currency"].($row["discount_finish"] ? ' ('.$this->diafan->_('до').' '.$row["discount_finish"].')' : '').'</span></div>';
	}

	//теги товара
	if(! empty($row["tags"]))
	{
		echo $row["tags"];
	}

	if(empty($result['search']))
	{
		//вывод кнопки "Купить"
		echo $this->get('buy_form', 'shop', array("row" => $row, "result" => $result));
		if(empty($result["hide_compare"]))
		{
			echo $this->get('compare_form', 'shop', $row);
		}
	}

	echo '</div>';		
}

//Кнопка "Показать ещё"
if(! empty($result["show_more"]))
{
	echo $result["show_more"];
}
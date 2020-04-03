<?php
/**
 * Шаблон списка товаров для поиска
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

if(! empty($result["error"]))
{
	echo '<p>'.$result["error"].'</p>';
	return;
}

if(empty($result["ajax"]))
{
	echo '<div class="js_shop_list shop_list">';
}

//вывод списка товаров
if(! empty($result["rows"]))
{
	//вывод сортировки товаров
	if(! empty($result["link_sort"]))
	{
		echo $this->get('sort_block', 'shop', $result);
	}

	echo '<div class="shop-pane">';
        $result['search'] = true;
        echo $this->get($result["view_rows"],'shop',$result);
        
        /*

	$i = 0;
	$all = count($result["rows"]);
	$k = round($all / 2);

	foreach ($result["rows"] as $row)
	{		
		if($i == 0)
		{
			$all--;
			$k = (round($all / 2) > 0 ? round($all / 2) : 1);			
			echo '<div class="shop-col">';
		}

		$i++;		

		echo '<div class="js_shop shop-item shop">';

		//вывод изображений товара
		if (!empty($row["img"]))
		{
			echo '<div class="shop_img shop-photo">';
			$img = $row["img"][0];
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
			echo '<img src="'.$img["src"].'" width="'.$img["width"].'" height="'.$img["height"].'" alt="'.$img["alt"].'" title="'.$img["title"].'" image_id="'.$img["id"].'" class="js_shop_img">';
			echo '<span class="shop-photo-labels">';
					if (!empty($row['hit']))
					{
						echo '<img src="' . BASE_PATH . Custom::path('img/label_hot.png').'"/>';
					}
					if (!empty($row['action']))
					{
						echo '<img src="' . BASE_PATH . Custom::path('img/label_special.png').'"/>';
					}
					if (!empty($row['new']))
					{
						echo '<img src="' . BASE_PATH . Custom::path('img/label_new.png').'"/>';					
					}
				echo '</span>';
			echo '</a> ';
			echo '</div>';
		}

		//вывод названия и ссылки на товар
		echo '<a href="'.BASE_PATH_HREF.$row["link"].'" class="shop-item-title">'.$row["name"].'</a>';		

		//вывод краткого описания товара
		if (!empty($row["anons"]))
		{
			echo '<div class="shop_anons">'.$this->diafan->short_text($row['anons'], 80).'</div>';
		}

		//вывод производителя
		if (!empty($row["brand"]))
		{
			echo '<div class="shop_brand">';
			echo $this->diafan->_('Производитель').': ';
			echo '<a href="'.BASE_PATH_HREF.$row["brand"]["link"].'">'.$row["brand"]["name"].'</a>';
			echo '</div>';
		}

		//вывод артикула
		if (!empty($row["article"]))
		{
			echo '<div class="shop_article">';
			echo $this->diafan->_('Артикул').': ';
			echo '<span class="shop_article_value">'.$row["article"].'</span>';
			echo '</div>';
		}	

		//вывод скидки на товар
		if (!empty($row["discount"]))
		{
			echo '<div class="shop_discount">'.$this->diafan->_('Скидка').': <span class="shop_discount_value">'.$row["discount"].' '.$row["discount_currency"].($row["discount_finish"] ? ' ('.$this->diafan->_('до').' '.$row["discount_finish"].')' : '').'</span></div>';
		}

		//теги товара
		if (!empty($row["tags"]))
		{
			echo $row["tags"];
		}
		
		echo '</div>';		

		if($i == $k)
		{			
			echo '</div>';
			$i = 0;
		}	
	}	
	if($i < $k)
	{		
		echo '</div>';		
	}	*/
        
        echo '</div>';	
}

if(empty($result["ajax"]))
{
	echo '</div>';
}
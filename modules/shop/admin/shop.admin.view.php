<?php
/**
 * Шаблон модуля в административной части
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
 * Shop_admin_view
 */
class Shop_admin_view extends Diafan
{
	/**
	 * Выводит товары, к которым прикреплена скидка
	 *
	 * @param integer $element_id номер скидки
	 * @return string
	 */
	public function discount_goods($element_id)
	{
		$text = ' ';
		$rows = DB::query_fetch_all("SELECT s.id, s.[name], s.site_id FROM {shop} AS s"
		                    ." INNER JOIN {shop_discount_object} AS r ON s.id=r.good_id AND r.discount_id=%d"
		                    ." WHERE s.trash='0' GROUP BY s.id",
		                    $element_id
		                   );
		foreach ($rows as $row)
		{
			$link = $this->diafan->_route->link($row["site_id"], $row["id"], 'shop');
			$img = DB::query_fetch_array("SELECT name, folder_num FROM {images} WHERE element_id=%d AND module_name='shop' AND element_type='element' AND trash='0' ORDER BY sort ASC LIMIT 1", $row["id"]);
			$text .= '
			<div class="rel_element" good_id="'.$row["id"].'">'
			    .($img ? '<img src="'.BASE_PATH.USERFILES.'/small/'.($img["folder_num"] ? $img["folder_num"].'/' : '').$img["name"].'">' : '')
				.'<span>'.$row["name"].'</span>'
				.'
				<div class="rel_element_actions">
					<a href="'.BASE_PATH.$link.'" target="_blank"><i class="fa fa-laptop"></i> '.$this->diafan->_('Посмотреть на сайте').'</a>
					<a href="javascript:void(0)" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'" action="delete_rel_element" class="delete"><i class="fa fa-times-circle"></i> '.$this->diafan->_('Удалить').'</a>
				</div>'
				.'
			</div>';
		}
		return $text;
	}
}
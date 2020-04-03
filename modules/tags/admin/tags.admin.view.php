<?php
/**
 * Шаблон вывода тегов в административной части
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
 * Tags_admin_view
 */
class Tags_admin_view extends Diafan
{
	/**
	 * Выводит теги, прикрепленные к элементу модуля
	 *
	 * @param integer $element_id номер элемента
	 * @return string
	 */
	public function show($element_id)
	{
		$text = '';
		$rows = DB::query_fetch_all("SELECT n.[name], t.id, n.id AS tags_name_id FROM {tags_name} as n"
			." INNER JOIN {tags} as t ON t.tags_name_id=n.id"
			." WHERE t.module_name='%h' AND t.element_id='%d'"
			." AND n.trash='0' AND t.trash='0'"
			." ORDER BY n.sort ASC",
			$this->diafan->_admin->rewrite, $element_id);
		foreach ($rows as $row)
		{
			$text .= '
			<a href="'.BASE_PATH_HREF.'tags/edit'.$row["tags_name_id"].'/" target="_blank">'.($row["name"] ? $row["name"] : $row["id"]).'
			<i confirm="'.$this->diafan->_('Вы действительно хотите удалить тег?').'" tag_id="'.$row["id"].'" class="tags_delete fa fa-close"></i></a>';
		}
		return $text;
	}
}
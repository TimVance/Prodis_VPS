<?php
/**
 * Отзывы для событий
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
 * Reviews_admin_dashboard
 */
class Reviews_admin_dashboard extends Diafan
{
	/**
	 * @var string название таблицы
	 */
	public $name = 'Отзывы';

	/**
	 * @var integer порядковый номер для сортировки
	 */
	public $sort = 5;

	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'reviews';

	/**
	 * @var string нет элементов
	 */
	public $empty_rows = 'Нет новых отзывов.';

	/**
	 * @var string условие для отбора
	 */
	public $where = "";

	/**
	 * @var array поля в таблице
	 */
	public $variables = array (
		'created' => array(
			'name' => 'Дата и время',
			'type' => 'datetime',
			'sql' => true,
		),
		'text' => array(
			'name' => 'Отзыв',
		),
	);

	/**
	 * Выводит отзыв в списке
	 * 
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @param array $rows все элементы
	 * @return string
	 */
	public function list_variable_text($row, $var, $rows)
	{
		if(! isset($this->cache["prepare"]["param"]))
		{
			$rows_id = array();
			foreach($rows as $r)
			{
				$rows_id[] = $r["id"];
			}
			$select = array();
			$checkbox = array();
			$rows = DB::query_fetch_all("SELECT e.element_id, e.value, e.param_id, p.type, p.[name] FROM"
				." {reviews_param_element} AS e"
				." INNER JOIN {reviews_param} AS p ON e.param_id=p.id"
				. " WHERE e.trash='0' AND e.element_id IN (%s)", implode(",", $rows_id));
			foreach ($rows as $r)
			{
				switch ($r["type"])
				{
					case 'select':
					case 'multiple':
						if(! in_array($r["value"], $select))
						{
							$select[] = $r["value"];
						}
						break;

					case 'checkbox':
						if(! in_array($r["param_id"], $checkbox))
						{
							$checkbox[] = $r["param_id"];
						}
						break;
				}
			}
			if($select)
			{
				$select_value = DB::query_fetch_key_value("SELECT id, [name] FROM {reviews_param_select} WHERE id IN (%s)", implode(",", $select), "id", "name");
			}
			if($checkbox)
			{
				$checkbox_value = DB::query_fetch_key_value("SELECT param_id, [name] FROM {reviews_param_select} WHERE param_id IN (%s)", implode(",", $checkbox), "param_id", "name");
			}
			foreach ($rows as $r)
			{
				if ($r["value"])
				{
					switch ($r["type"])
					{
						case 'select':
						case 'multiple':
							if(! empty($select_value[$r["value"]]))
							{
								$r["value"] = $select_value[$r["value"]];
							}
							break;
	
						case 'checkbox':
							$v = (! empty($checkbox_value[$r["param_id"]]) ? $checkbox_value[$r["param_id"]] : '');
							if ($v)
							{
								$r["value"] = $r["name"].': '.$v;
							}
							else
							{
								$r["value"] = $r["name"];
							}
							break;
					}
					$this->cache["prepare"]["param"][$r["element_id"]][] = $r["value"];
				}
			}
		}
		$text = '<div class="text"><a href="'.BASE_PATH_HREF.'reviews/edit'.$row["id"].'/">';
		if(! empty($this->cache["prepare"]["param"][$row["id"]]))
		{
			$text .= implode(', ', $this->cache["prepare"]["param"][$row["id"]]);
		}
		$text .= '</a></div>';
		return $text;
	}
}
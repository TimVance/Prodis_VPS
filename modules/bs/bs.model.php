<?php
/**
 * Модель
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
 * Bs_model
 */
class Bs_model extends Model
{
	/**
	 * Генерирует данные для шаблонной функции: блок баннера
	 *
	 * @param integer $id идентификатор баннера
	 * @param integer $count количество баннеров
	 * @param integer $cat_id категория
	 * @return array
	 */
	public function show_block($id, $count, $sort, $cat_id)
	{
		$time = mktime(date("H"), date("m"), 0);
		if (! empty($id))
		{
			$result["rows"] = DB::query_fetch_all("SELECT e.id, e.type, e.file, e.html, e.[link], e.check_number, e.show_number, e.check_user, e.show_user, e.check_click, e.show_click, e.count_view, e.width, e.height, e.[alt], e.[title], e.target_blank, e.[name], e.[text]"
			." FROM {bs} AS e"
			." INNER JOIN {bs_site_rel} AS r ON r.element_id=e.id AND (r.site_id=%d OR r.site_id=0)"
			." WHERE e.[act]='1' AND e.trash='0'"
			." AND e.id=%d AND (e.date_start<=%d OR e.date_start=0) AND (e.date_finish>=%d OR e.date_finish=0) LIMIT 1",
			$this->diafan->_site->id,
			$id, $time, $time);

			$this->elements($result["rows"]);
		}
		else
		{
			$cat_id = $this->diafan->configmodules("cat", "bs") ? $cat_id : 0;
			
			switch($sort)
			{
				case 'rand':
					$order = 'RAND()';
					break;

				case 'date':
					$order = 'created DESC';
					break;

				default:
					$order = 'sort DESC';
					break;
			}
			$rows = DB::query_fetch_all(
					"SELECT e.id, e.type, e.file, e.html, e.[link], e.check_number,"
					." e.show_number, e.check_user, e.show_user, e.check_click, e.show_click, e.count_view, e.width, e.height, e.[alt], e.[title], e.target_blank, e.[name], e.[text]"
					." FROM {bs} as e"
					." INNER JOIN {bs_site_rel} AS r ON r.element_id=e.id AND (r.site_id=%d OR r.site_id=0)"
					." WHERE e.[act]='1' AND e.trash='0'"
					." AND (e.date_start<=%d OR e.date_start=0) AND (e.date_finish>=%d OR e.date_finish=0)"
					.($cat_id ? " AND e.cat_id=%d" : '')
					." GROUP BY e.id ORDER BY ".$order,
					$this->diafan->_site->id, $time, $time, $cat_id
				);

			$this->elements($rows);
			$max_count = count($rows);

			if($count === "all" || $count >= $max_count)
			{
				$result["rows"] = $rows;
			}
			else
			{
			    $result["rows"] = array_slice($rows, 0, $count);
			}
		}
		foreach ($result["rows"] as &$row)
		{			
			$row['count_view'] = $row['count_view'] + 1;			
			DB::query("UPDATE {bs} SET count_view=%d WHERE id=%d", $row['count_view'], $row['id']);

			if ($row['check_number'])
			{
				$row['show_number'] = $row['show_number'] - 1;
				DB::query("UPDATE {bs} SET show_number=%d WHERE id=%d", $row['show_number'], $row['id']);
			}
			$row["text"] = $this->diafan->_tpl->htmleditor($row["text"]);
		}

		return $result;
	}

	/**
	 * Форматирует данные об объявлении для списка баннеров
	 *
	 * @param array $rows все полученные из базы данных элементы
	 * @return void
	 */
	private function elements(&$rows)
	{
		$time = time();		
		foreach ($rows as $i => &$row)
		{
			if(! empty($row['check_number']))
			{
				if($row['show_number'] == 0 )
				{
					unset($rows[$i]);
					break;
				}				
			}

			if(! empty($row['check_click']))
			{
				if($row['show_click'] == 0 )
				{
					unset($rows[$i]);
					break;
				}				
			}

			if ($row['type'] == 0)
			{
				break;
			}

			if ($row['type'] == 1)
			{
				$row['image'] = $row['file'];
				unset ($row['html']);
				unset ($row['file']);
			}

			if ($row['type'] == 2)
			{
				$row['swf'] = $row['file'];
				unset ($row['html']);
				unset ($row['file']);
			}

			if ($row['type'] == 3)
			{
				unset ($row['file']); 
			}

			if (! empty($row['check_user']))
			{
				if(!isset($_COOKIE['show_banner_'.$row['id']]) || !isset($_COOKIE['end_show_banner_'.$row['id']]))
				{
					setcookie('show_banner_'.$row['id'], 1, $time+86400, '/');
					setcookie('end_show_banner_'.$row['id'], $time+86400, $time+86400, '/');
				}
				elseif($_COOKIE['show_banner_'.$row['id']] <= $row['show_user'] && $_COOKIE['end_show_banner_'.$row['id']] > $time)
				{
					$new_cookie_value = $_COOKIE['show_banner_'.$row['id']] + 1;
					setcookie('show_banner_'.$row['id'], $new_cookie_value, $time+86400, '/');
				}
				elseif($_COOKIE['end_show_banner_'.$row['id']] < $time)
				{
					setcookie('show_banner_'.$row['id'], 1, $time+86400, '/');
					setcookie('end_show_banner_'.$row['id'], $time+86400, $time+86400, '/');
				}
				else
				{
					break;
				}
			}
		}
	}
}
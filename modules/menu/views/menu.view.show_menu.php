<?php
/**
 * Шаблон меню, оформленного атрибутами тега
 *
 * Шаблонный тег: простой вывод меню, если не передан параметр template,
 * но есть атрибуты tag_1, tag_2 и т.д.
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

$level = ! empty($result["level"]) ? $result["level"] : 1;
$parent_id = ! empty($result["parent_id"]) ? $result["parent_id"] : 0;

$result["attributes"]["tag_level_start_".$level] = isset($result["attributes"]["tag_level_start_".$level]) ? $result["attributes"]["tag_level_start_".$level] : $result["attributes"]["tag_level_start_".($level - 1)];
$result["attributes"]["tag_level_end_".$level] = isset($result["attributes"]["tag_level_end_".$level]) ? $result["attributes"]["tag_level_end_".$level] : $result["attributes"]["tag_level_end_".($level - 1)];

$result["attributes"]["tag_active_child_start_".$level] = isset($result["attributes"]["tag_active_child_start_".$level]) ? $result["attributes"]["tag_active_child_start_".$level] : $result["attributes"]["tag_active_child_start_".($level - 1)];
$result["attributes"]["tag_active_child_end_".$level] = isset($result["attributes"]["tag_active_child_end_".$level]) ? $result["attributes"]["tag_active_child_end_".$level] : $result["attributes"]["tag_active_child_end_".($level - 1)];
$result["attributes"]["tag_active_child_end_after_children_".$level] = isset($result["attributes"]["tag_active_child_end_after_children_".$level]) ? $result["attributes"]["tag_active_child_end_after_children_".$level] : $result["attributes"]["tag_active_child_end_after_children_".($level - 1)];

$result["attributes"]["tag_start_".$level] = isset($result["attributes"]["tag_start_".$level]) ? $result["attributes"]["tag_start_".$level] : $result["attributes"]["tag_start_".($level - 1)];
$result["attributes"]["tag_end_".$level] = isset($result["attributes"]["tag_end_".$level]) ? $result["attributes"]["tag_end_".$level] : $result["attributes"]["tag_end_".($level - 1)];
$result["attributes"]["tag_end_after_children_".$level] = isset($result["attributes"]["tag_end_after_children_".$level]) ? $result["attributes"]["tag_end_after_children_".$level] : $result["attributes"]["tag_end_after_children_".($level - 1)];

$result["attributes"]["tag_active_start_".$level] = isset($result["attributes"]["tag_active_start_".$level]) ? $result["attributes"]["tag_active_start_".$level] : $result["attributes"]["tag_active_start_".($level - 1)];
$result["attributes"]["tag_active_end_".$level] = isset($result["attributes"]["tag_active_end_".$level]) ? $result["attributes"]["tag_active_end_".$level] : $result["attributes"]["tag_active_end_".($level - 1)];
$result["attributes"]["tag_active_end_after_children_".$level] = isset($result["attributes"]["tag_active_end_after_children_".$level]) ? $result["attributes"]["tag_active_end_after_children_".$level] : $result["attributes"]["tag_active_end_after_children_".($level - 1)];

$result["attributes"]["separator_".$level] = isset($result["attributes"]["separator_".$level]) ? $result["attributes"]["separator_".$level] : $result["attributes"]["separator_".($level - 1)];
$result["attributes"]["site_id"] = isset($result["attributes"]["site_id"]) ? $result["attributes"]["site_id"] : 0;

$k = 0;
if(! empty($result["rows"][$parent_id]))
{
	echo $result["attributes"]["tag_level_start_".$level];
	foreach ($result["rows"][$parent_id] as $row)
	{
		echo ($k ? $result["attributes"]["separator_".$level] : '');
		if ($row["active"] && $result["attributes"]["tag_active_start_".$level])
		{
			echo str_replace(array('Increment', 'Level'), array($k, $level), $result["attributes"]["tag_active_start_".$level]);
		}
		elseif ($row["active_child"] && $result["attributes"]["tag_active_child_start_".$level])
		{
			echo str_replace(array('Increment', 'Level'), array($k, $level), $result["attributes"]["tag_active_child_start_".$level]);
		}
		else
		{
			echo str_replace(array('Increment', 'Level'), array($k, $level), $result["attributes"]["tag_start_".$level]);
		}
	
		if (
			// на текущей странице нет ссылки, если не включена настройка "Текущий пункт как ссылка"
			(!$row["active"] || $result["current_link"])
	
			// включен пункт "Не отображать ссылку на элемент, если он имеет дочерние пункты"
			&& (!$result["hide_parent_link"] || empty($result["rows"][$row["id"]]))
		)
		{
			if ($row["othurl"])
			{
				echo '<a href="'.$row["othurl"].'"'.$row["attributes"].'>';
			}
			else
			{
				echo '<a href="'.BASE_PATH_HREF.$row["link"].'"'.$row["attributes"].'>';
			}
		}
	
		//вывод изображения
		if (! empty($row["img"]))
		{
			echo '<img src="'.$row["img"]["src"].'" width="'.$row["img"]["width"].'" height="'.$row["img"]["height"]
			.'" alt="'.$row["img"]["alt"].'" title="'.$row["img"]["title"].'"> ';
		}
		
		// название пункта меню	
		if (! empty($row["name"]))
		{
			echo $row["name"];
		}
		
		if (
			// на текущей странице нет ссылки, если не включена настройка "Текущий пункт как ссылка"
			(!$row["active"] || $result["current_link"])
	
			// включен пункт "Не отображать ссылку на элемент, если он имеет дочерние пункты"
			&& (!$result["hide_parent_link"] || empty($result["rows"][$row["id"]]))
		)
		{
			echo '</a>';
		}

		// описание пункта меню
		if (! empty($row["text"]))
		{
			echo $row["text"];
		}

		if ($row["active"] && $result["attributes"]["tag_active_end_".$level])
		{
			echo str_replace(array('Increment', 'Level'), array($k, $level), $result["attributes"]["tag_active_end_".$level]);
		}
		elseif ($row["active_child"] && $result["attributes"]["tag_active_child_end_".$level])
		{
			echo str_replace(array('Increment', 'Level'), array($k, $level), $result["attributes"]["tag_active_child_end_".$level]);
		}
		else
		{
			echo str_replace(array('Increment', 'Level'), array($k, $level), $result["attributes"]["tag_end_".$level]);
		}
		if ((empty($result['attributes']['count_level']) || $result['attributes']['count_level'] > $level)
			&& ($result["show_all_level"] || $row["active_child"] || $row["active"]))
		{
			$result_view = $result;
			$result_view["parent_id"] = $row["id"];
			$result_view["level"] = $level + 1;
			echo $this->get('show_menu', 'menu', $result_view);
		}
		if ($row["active"] && $result["attributes"]["tag_active_end_after_children_".$level])
		{
			echo str_replace(array('Increment', 'Level'), array($k, $level), $result["attributes"]["tag_active_end_after_children_".$level]);
		}
		elseif ($row["active_child"] && $result["attributes"]["tag_active_child_end_after_children_".$level])
		{
			echo str_replace(array('Increment', 'Level'), array($k, $level), $result["attributes"]["tag_active_child_end_after_children_".$level]);
		}
		else
		{
			echo str_replace(array('Increment', 'Level'), array($k, $level), $result["attributes"]["tag_end_after_children_".$level]);
		}
		$k++;
	}
	echo $result["attributes"]["tag_level_end_".$level];
}
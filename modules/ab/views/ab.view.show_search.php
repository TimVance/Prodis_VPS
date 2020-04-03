<?php
/**
 * Шаблон форма поиска по объявлениям
 *
 * Шаблонный тег <insert name="show_search" module="ab" [ajax="подгружать_результаты"]
 * [cat_id="категория_объявлений"] [site_id="страница_с_прикрепленным_модулем"]
 * [only_ab="выводить_только_на_странице_модуля"] [template="шаблон"]>:
 * форма поиска по объявлениям
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

echo '<div class="ab_search">';
echo '<div class="block_header">'.$this->diafan->_('Поиск по объявлениям').'</div>';
echo '<form method="GET" action="'.BASE_PATH_HREF.$result["path"].'" class="ab_search'.(! empty($result["send_ajax"]) ? ' ajax' : '').'">';
echo '<input type="hidden" name="module" value="ab">
<input type="hidden" name="action" value="search">';

if (count($result["site_ids"]) > 1)
{
	echo '<div class="ab_search_site_ids">
	<span class="input-title">'.$this->diafan->_('Раздел').':</span>
	<select class="cs-select">';
	foreach ($result["site_ids"] as $row)
	{
		echo '<option value="'.$row["id"].'" path="'.BASE_PATH_HREF.$row["path"].'"';
		if($result["site_id"] == $row["id"])
		{
			echo ' selected';
		}
		echo '>'.$row["name"].'</option>';
	}
	echo '</select>';
	echo '</div>';
}

if (count($result["cat_ids"]) > 1)
{
	echo '<div class="ab_search_cat_ids">
	<span class="input-title">'.$this->diafan->_('Категория').':</span>
	<select name="cat_id" class="cs-select">';
	echo '<option value="">'.$this->diafan->_('Все').'</option>';
	foreach ($result["cat_ids"] as $row)
	{
		echo '<option value="'.$row["id"].'" site_id="'.$row["site_id"].'"';
		if($result["cat_id"] == $row["id"])
		{
			echo ' selected';
		}
		echo '>';
		if($row["level"])
		{
			echo str_repeat('- ', $row["level"]);
		}
		echo $row["name"].'</option>';
	}
	echo '</select>';
	echo '</div>';
}
else
{
    echo '<input name="cat_id" type="hidden" value="'.$result["cat_id"].'">';
}

if(! empty($result["rows"]))
{
	foreach ($result["rows"] as $row)
	{
		if(! in_array($row["type"], array('title', 'date', 'datetime', 'numtext', 'checkbox', 'select', 'multiple')))
			continue;

		echo '<div class="ab_search_param ab_search_param'.$row["id"].'" cat_ids="'.$row["cat_ids"].'">';
		switch ($row["type"])
		{
			case 'title':
				echo '<span class="input-title">'.$row["name"].':</span>';
				break;

			case 'date':
				echo '
				<span class="input-title">'.$row["name"].':</span>
				<div>
					<input type="text" name="p'.$row["id"].'_1" value="'.$row["value1"].'" class="from timecalendar" showTime="false">
					&nbsp;-&nbsp;
					<input type="text" name="p'.$row["id"].'_2" value="'.$row["value2"].'" class="to timecalendar" showTime="false">
				</div>';
				break;

			case 'datetime':
				echo '
				<span class="input-title">'.$row["name"].':</span>
				<div>
					<input type="text" name="p'.$row["id"].'_1" value="'.$row["value1"].'" class="from timecalendar" showTime="true">
					&nbsp;-&nbsp;
					<input type="text" name="p'.$row["id"].'_2" value="'.$row["value2"].'" class="to timecalendar" showTime="true">
				</div>';
				break;

			case 'numtext':
				echo '
				<span class="input-title">'.$row["name"].':</span>
				<div>
					<input type="text" class="from" name="p'.$row["id"].'_1" value="'. $row["value1"].'">
					&nbsp;-&nbsp;
					<input type="text" class="to"  name="p'.$row["id"].'_2" value="'.$row["value2"].'">
				</div>';
				break;

			case 'checkbox':
				echo '
				<input type="checkbox" id="ab_search_p'.$row["id"].'" name="p'.$row["id"].'" value="1"'.($row["value"] ? " checked" : '').'>
				<label for="ab_search_p'.$row["id"].'">'.$row["name"].'</label>
				<br>';
				break;

			case 'select':
			case 'multiple':
				echo '
				<span class="input-title">'.$row["name"].':</span>';
				foreach ($row["select_array"] as $key => $value)
				{
					echo '<input type="checkbox" id="ab_search_p'.$row["id"].'_'.$key.'" name="p'.$row["id"].'[]" value="'.$key.'"'.(in_array($key, $row["value"]) ? " checked" : '').'>
					<label for="ab_search_p'.$row["id"].'_'.$key.'">'.$value.'</label>
					<br>';
				}
		}
		echo '
		</div>';
	}
}
echo '
	<input type="submit" value="'.$this->diafan->_('Поиск', false).'">
	</form>
</div>';
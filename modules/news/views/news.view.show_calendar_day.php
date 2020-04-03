<?php
/**
 * Шаблон календаря архива новостей по дням
 * 
 * Шаблонный тег <insert name="show_calendar" module="news" detail="day"
 * [cat_id="категория_новостей"] [site_id="страница_с_прикрепленным_модулем"]
 * [only_module="only_on_module_page"] [template="шаблон"]>:
 * календарь архива новостей с детализацией по дням
 * @package    DIAFAN.CMS
 *
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

echo '<div class="js_news_block news_block">';
echo '<div class="block_header">'.$this->diafan->_('Календарь').'</div>';

echo '<form method="POST" enctype="multipart/form-data" action="" class="news_calendar_form">
<input type="hidden" name="module" value="news">
<input type="hidden" name="action" value="calendar_arrow">
<input type="hidden" name="arrow" value="">
<input type="hidden" name="site_id" value="'.$result["site_id"].'">
<input type="hidden" name="cat_id" value="'.$result["cat_id"].'">
<input type="hidden" name="template" value="'.$result["template"].'">
<input type="hidden" name="year" value="'.$result['year'].'">
<input type="hidden" name="month" value="'.$result['month'].'">

<a href="javascript:void(0)" class="js_news_calendar_prev news_calendar_prev">&#171;</a>
<span>'.$result['month_name'].'&nbsp;'.$result['year'].'</span>
<a href="javascript:void(0)" class="js_news_calendar_next news_calendar_next">&#187;</a>

</form>';

echo '<table class="news_calendar">
<thead>
<tr>
	<th>'.$this->diafan->_('Пн').'</th>
	<th>'.$this->diafan->_('Вт').'</th>
	<th>'.$this->diafan->_('Ср').'</th>
	<th>'.$this->diafan->_('Чт').'</th>
	<th>'.$this->diafan->_('Пт').'</th>
	<th>'.$this->diafan->_('Сб').'</th>
	<th>'.$this->diafan->_('Вс').'</th>
</tr>
</thead>
<tbody>';
for($i = 0; $i < count($result['week']); $i++)
{
	echo '<tr>';
	for($j = 0; $j < 7; $j++) 
	{
		if(! empty($result['week'][$i][$j]["day"])) 
		{
			echo '<td'.($result['week'][$i][$j]["today"] ? ' class="news_day_today'.($result['week'][$i][$j]["day"] == $result["day"] ? ' news_day_current' : '').'"' : ($result['week'][$i][$j]["day"] == $result["day"] ? ' class="news_day_current"' : '')).'>';
			if ($result['week'][$i][$j]["count"] > 0) 
			{
				echo '<a href="'.BASE_PATH_HREF.$result['week'][$i][$j]["link"].'" title="'.$this->diafan->_('Новостей:', false).' '.$result['week'][$i][$j]["count"].'">'.$result['week'][$i][$j]["day"].'</a>';
			}
			else
			{
				echo $result['week'][$i][$j]["day"];
			}
			echo'</td>';

		}
		else 
		{
			echo'<td>&nbsp;</td>';
		}

	}
	echo '</tr>';
}
echo '</tbody></table>
	
</div>';
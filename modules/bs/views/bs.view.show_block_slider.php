<?php
/**
 * Шаблон блока баннеров
 * 
 * Шаблонный тег <insert name="show_block" module="bs" [count="all|количество"]
 * [cat_id="категория"] [id="номер_баннера"] [template="шаблон"]>:
 * блок баннеров
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

if (empty($result))
{
	return false;
}               

if(! isset($GLOBALS['include_bs_js']))
{
	$GLOBALS['include_bs_js'] = true;
	//скрытая форма для отправки статистики по кликам
	echo '<form method="POST" enctype="multipart/form-data" action="" class="ajax js_bs_form bs_form">
	<input type="hidden" name="module" value="bs">
	<input type="hidden" name="action" value="click">
	<input type="hidden" name="banner_id" value="0">
	</form>';
}

echo '<div id="mp-slider">';
foreach ($result as $i => $row)
{
	if (! empty($row['html']) || ! empty($row['image']) || ! empty($row['swf']))
	{
		echo '<div class="slide'.(! $i ? ' active' : '').'" style="background-image:url('.(! empty($row['image']) ? BASE_PATH.USERFILES.'/bs/'.$row['image'] : '').')">';
			echo '<div class="wrapper">';
	      
				//вывод баннера в виде html разметки
				if (! empty($row['html']))
				{
					echo $row['html'];
				}
				
				//вывод баннера в виде изображения
				if (! empty($row['image']))
				{
					// echo '<img src="'.BASE_PATH.USERFILES.'/bs/'.$row['image'].'" alt="'.(! empty($row['alt']) ? $row['alt'] : '').'" title="'.(! empty($row['title']) ? $row['title'] : '').'">';
				}
				
				//вывод баннера в виде flash
				if (! empty($row['swf']))
				{
						echo '<object type="application/x-shockwave-flash" 
						data="'.BASE_PATH.USERFILES.'/bs/'.$row['swf'].'" 
						width="'.$row['width'].'" height="'.$row['height'].'">
						<param name="movie" value="'.BASE_PATH.USERFILES.'/bs/'.$row['swf'].'" />
						<param name="quality" value="high" />
						<param name="bgcolor" value="#ffffff" />
						<param name="play" value="true" />
						<param name="loop" value="true" />
						<param name="wmode" value="opaque">
						<param name="scale" value="showall" />
						<param name="menu" value="true" />
						<param name="devicefont" value="false" />
						<param name="salign" value="" />
				
						<param name="allowScriptAccess" value="sameDomain" />
				
					</object>';
				}

				//вывод описания к баннеру
				if (! empty($row['text']))
				{
					echo $row['text'];
					echo '<br>';
				}
				
				//вывод ссылки на баннер, если задана
				if (! empty($row['link']))
				{
					echo '<a href="'.$row['link'].'" class="js_bs_counter bs_counter button" rel="'.$row['id'].'" '.(! empty($row['target_blank']) ? 'target="_blank"' : '').'>'.$this->diafan->_("Заказать").'</a>';
				}

			echo '</div>';
		echo '</div>';	
	}
}
echo '</div>';
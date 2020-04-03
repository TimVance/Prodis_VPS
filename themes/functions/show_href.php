<?php
/**
 * Шаблонный тег: выводит ссылку на страницу сайта. Если текущая страница соответствует адресу, на которую ведет ссылка, то ссылка становится неактивной. Шаблонная функция аналогична стандартной HTML-конструкции `<a href=""></a>`, но ликвидирует ссылки, которые никуда не ведут.
 *
 * @param array $attributes атрибуты шаблонного тега
 * rewrite - псевдоссылка страницы, на которую ведет ссылка, например: news
 * img - адрес изображения, использующегося в качестве ссылки, например: img/logo.png, можно добавлять *_LANG*, чтобы подставить ID текущего языка
 * img_act - адрес изображения на текущей странице, например: img/home_act.gif, можно добавлять *_LANG*, чтобы подставить ID текущего языка
 * width - ширина изображения
 * height - высота изображения
 * class - класс для ссылки
 * alt - альтернативный тег для изображения или текст ссылки. Если задано **title**, будет подставлено название сайта из параметров сайта. Если задано **url**, будет подставлена ссылка на главную страницу сайта
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

if(in_array($this->diafan->_site->theme, array('404.php', '503.php', '403.php')))
{
	$this->diafan->_site->rewrite = $this->diafan->_site->theme;
}
$this->get_attributes($attributes, 'rewrite', 'img', 'img_act', 'width', 'height', 'alt', 'class');

$rewrite = $attributes["rewrite"];
if ($rewrite && $this->diafan->_site->rewrite != $rewrite)
{
	if(! DB::query_result("SELECT s.id FROM {site} AS s INNER JOIN {rewrite} AS r ON s.id=r.element_id AND r.module_name='site' AND r.element_type='element' WHERE BINARY r.rewrite='%h' AND r.trash='0' AND s.[act]='1' AND s.trash='0'", $rewrite))
	{
		return;
	}
}

$img = ($this->diafan->_site->rewrite != $rewrite || ! $attributes["img_act"]
	   ? preg_replace('/[^A-Za-z0-9-_\/\.]+/', '', $attributes["img"])
	   : preg_replace('/[^A-Za-z0-9-_\/\.]+/', '', $attributes["img_act"])
	   );
$img = str_replace('_LANG', _LANG, $img);

$width = preg_replace('/[^0-9%]+/', '', $attributes["width"]);
$height = preg_replace('/[^0-9%]+/', '', $attributes["height"]);

$class = preg_replace('/[^a-z\-\_0-9\s]+/', '', $attributes["class"]);

$name = ($attributes["alt"] == "title"
	? TITLE
	: ($attributes["alt"] == "url"
	  ? BASE_PATH
	  : $this->diafan->_($attributes["alt"], false)
	  )
	);

if ($this->diafan->_site->rewrite != $rewrite || $this->diafan->_route->show || $this->diafan->_route->cat)
{
	if($rewrite)
	{
		$link = $rewrite.ROUTE_END;
	}
	else
	{
		$link = '';
		if(IS_DEMO)
		{
			$link = '?'.rand(0, 9999);
		}
	}

	echo '<a href="'.BASE_PATH_HREF.$link.'" title="'.$name.'"'.($class ? ' class="'.$class.'"' : '').'>';
}
if ($img)
{
	echo '<img src="'.BASE_PATH.Custom::path($img).'" alt="'.$name.'"'.($width ? ' width="'.$width.'"' : '').($height ? ' height="'.$height.'"' : '').'>';
}
else
{
	echo $name;
}
if ($this->diafan->_site->rewrite != $rewrite || $this->diafan->_route->show || $this->diafan->_route->cat)
{
	echo '</a>';
}

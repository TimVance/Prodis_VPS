<?php
/**
 * Шаблон вывода отзывов
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

echo '<div class="block reviews">';
echo '<div class="block_header">'.$this->diafan->_('Отзывы').'</div>';

if(! empty($result["average_rating"]))
{
	echo '<div class="reviews_average_rating">'.$this->diafan->_('Средняя оценка').': '.$result["average_rating"].'</div>';
}

echo $this->get($result["view_rows"], 'reviews', $result);

echo '</div>';

//постраничная навигация
if(! empty($result["paginator"]))
{
	echo $result["paginator"];
}

if($result["form"])
{
	echo $this->get('form', 'reviews', $result["form"]);
}
if($result["register_to_review"])
{
	echo $this->diafan->_('Чтобы оставить отзыв, зарегистрируйтесь или авторизуйтесь.');
}

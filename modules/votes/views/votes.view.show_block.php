<?php
/**
 * Шаблон блока опросов
 * 
 * Шаблонный тег <insert name="show_block" module="votes" [id="номер_опроса"] [count="all|количество"] [template="шаблон"]>:
 * выводит опросы
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

echo '<div class="votes_block block">';

echo '<h3>'.$this->diafan->_('Опрос сайта').'</h3>';
foreach ($result as $row)
{
	echo '
	<p>'.$row["name"].'</p>
	<form method="post" action="" class="js_votes_form votes_form ajax" id="votes'.$row["id"].'">
		<p>
			<div id="votes'.$row["id"].'_form">
			'.$row["rows"].'
			</div>
			<div class="errors error"'.($row["error"] ? '>'.$row["error"] : ' style="display:none">').'</div>
		</p>
	</form>';
}

echo '</div>';
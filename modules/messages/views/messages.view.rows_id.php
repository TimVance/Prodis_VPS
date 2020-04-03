<?php
/**
 * Шаблон переписки с пользователем
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

if(empty($result['rows'])) return false;

foreach ($result['rows'] as $row)
{
	echo '<tr><td class="messages_avatar">';
	if (!empty($row['name']['avatar']))
	{
		echo '<img src="'.$row["name"]["avatar"].'" width="'.$row["name"]["avatar_width"].'" height="'.$row["name"]["avatar_height"].'" alt="'.$row["name"]["fio"].' ('.$row["name"]["name"].')" class="avatar">';
	}
	$user = $row['name']['fio'].' ('.$row['name']['name'].')';
	if(!empty($row['name']['user_page']))
	{
		$user='<a href="'.$row['name']['user_page'].'">'.$user.'</a>';
	}
	echo '
	</td>
	<td class="messages_user">
		<div><div>'.$user.'</div>'.$row['created'].'</div>
	</td>
	<td class="messages_text">'.$row['text'].'</td>
	</tr>';
}

//Кнопка "Показать ещё"
if(! empty($result["show_more"]))
{
	echo '<tr><td colspan="3">'.$result["show_more"].'</td></tr>';
}
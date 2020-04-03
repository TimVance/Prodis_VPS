<?php
/**
 * Шаблон элементов в списке контактов
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

	$user = $row['user']['fio'].' ('.$row['user']['name'].')';
	if(!empty($row['user']['user_page']))
	{
		$user = '<a href="'.$row['user']['user_page'].'">'.$user.'</a>';
	}
	$user .= '<br>'.$row['last_message']['created'];
	
	echo '<tr><td class="messages_avatar">';
	if (!empty($row['user']['avatar']))
	{
		echo '<img src="'.$row["user"]["avatar"].'" width="'.$row["user"]["avatar_width"].'" height="'.$row["user"]["avatar_height"].'" alt="'.$row["user"]["fio"].' ('.$row["user"]["name"].')" class="avatar">';
	}
	echo '</td>
	<td class="messages_user">'.$user.'</td>
	<td class="messages_text"><a href="'.$row['link'].'">'.$row['last_message']['text'].'</a></td>
	</tr>';
}

//Кнопка "Показать ещё"
if(! empty($result["show_more"]))
{
	echo '<tr><td colspan="3">'.$result["show_more"].'</td></tr>';
}
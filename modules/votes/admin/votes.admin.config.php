<?php
/**
 * Настройки модуля
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
 * Votes_admin_config
 */
class Votes_admin_config extends Frame_admin
{
	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'config' => array (
			'security_user' => array(
				'type' => 'checkbox',
				'name' => 'Только для зарегистрированных пользователей',
				'help' => 'Если отмечена, голосовать смогут только зарегистрированные пользователи.',
			),
			'security' => array(
				'type' => 'select',
				'name' => 'Защита от накруток',
				'select' => array(
					0 => 'нет',
					3 => 'вести лог голосовавших',
					4 => 'запрещать голосовать повторно',
				),
			),
			'captcha' => array(
				'type' => 'module',
				'name' => 'Использовать защитный код (капчу)',
				'help' => 'Для голосования пользователь должен ввести защитный код.',
			),
			'sort_count_votes' => array(
				'type' => 'checkbox',
				'name' => 'Сортировать ответы по количеству голосов',
			),
		),
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'config', // файл настроек модуля
	);
}
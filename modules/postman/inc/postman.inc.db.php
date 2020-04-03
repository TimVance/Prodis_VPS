<?php
/**
 * Подключение модуля «Уведомления» для работы с базой данных
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if ( ! defined('DIAFAN'))
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
 * Postman_inc_db
 */
class Postman_inc_db extends Diafan
{
	const TABLE_NAME = 'postman';
	const SEPARATOR = '-';

	/**
	 * Добавляет уведомление
	 *
	 * @param mixed(string|array) $recipient получатель/получатели
	 * @param string $subject тема письма
	 * @param string $body содержание письма
	 * @param string $from адрес отправителя
	 * @param string $type тип уведомления: *mail* – письмо, *sms* – короткое сообщение
	 * @param boolean $auto метод отправки уведомления: *false* – ручной, *true* – автоматический
	 */
	public function add($recipient, $subject, $body, $from = '', $type = 'mail', $auto = true)
	{
		if($type != 'mail' && $type != 'sms')
		{
			return false;
		}
		$auto = $auto ? 1 : 0;
		$recipient = is_array($recipient) ? implode(",", $recipient) : $recipient;

		return $this->diafan->_db_ex->add_new("{".self::TABLE_NAME."}",
			array("`type`", "`recipient`", "`subject`", "`body`", "`from`", "`auto`", "`timesent`", "`status`", "`error`", "`trace`", "`timeedit`"),
			array("'%h'", "'%h'", "'%h'", "'%s'", "'%h'", "'%h'", "%d", "'%h'", "'%s'", "'%s'", "%d"),
			array($type, $recipient, $subject, $body, $from, $auto, 0, '0', '', '', time())
		);
	}

	/**
	 * Возвращает количество уведомлений, требующих отправки
	 *
	 * @return integer
	 */
	public function count_sent()
	{
		return DB::query_result("SELECT COUNT(*) FROM {".self::TABLE_NAME."} WHERE timesent=%d AND status='%h' AND auto='%h'", 0, 0, 1);
	}
}

/**
 * Postman_db_exception
 *
 * Исключение для почтовых отправлений
 */
class Postman_db_exception extends Exception{}

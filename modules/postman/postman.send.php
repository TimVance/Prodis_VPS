<?php
/**
 * Отправка уведомлений
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
 * Postman_send
 */
class Postman_send extends Diafan
{
	/**
	 * Максимальное количество отсылаемых уведомлений за один проход
	 */
	const LIMIT = 1;

	/**
	 * Инициирует отправку уведомлений
	 *
	 * @return void
	 */
	public function init()
	{
		$this->diafan->set_time_limit();

		while ($this->diafan->_postman->db_count_sent() > 0)
		{
			$timesent = time();
			try
			{
				// резервируем уведомления за текущим процессом
				DB::query("UPDATE {postman} SET timesent=%d WHERE timesent=%d AND status='%h' AND auto='%h' ORDER BY master_id ASC, slave_id ASC LIMIT %d", $timesent, 0, 0, 1, self::LIMIT);
				// отправляем зарезервированные уведомления
				$ids = DB::query_fetch_value("SELECT id FROM {postman} WHERE timesent=%d AND status='%h' AND auto='%h'", $timesent, 0, 1, "id");
				foreach ($ids as $id)
				{
					$this->diafan->_postman->message_send($id);
				}
			}
			catch (Exception $e)
			{
				// снимаем резерв в случае ошибки
				DB::query("UPDATE {postman} SET timesent=%d WHERE timesent=%d AND status='%h' AND auto='%h' ORDER BY master_id DESC, slave_id DESC LIMIT %d", 0, $timesent, 0, 1, self::LIMIT);
				break;
			}
		}
		// Custom::inc('includes/404.php');
		// TO_DO: в ответ на передачу статуса сообщений клиент должен вернуть bytehand.com HTTP код 200 (OK).
		$this->diafan->_site->theme = '404.php';
		header('HTTP/1.0 200 OK');
		header('Content-Type: text/html; charset=utf-8');
		$this->diafan->_parser_theme->show_theme();
	}
}

$class = new Postman_send($this->diafan);
$class->init();
exit;

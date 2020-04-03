<?php
/**
 * Вопросы-ответы для событий
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
 * Faq_admin_dashboard
 */
class Faq_admin_dashboard extends Diafan
{
	/**
	 * @var string название таблицы
	 */
	public $name = 'Вопрос-ответ';

	/**
	 * @var integer порядковый номер для сортировки
	 */
	public $sort = 6;

	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'faq';

	/**
	 * @var string нет элементов
	 */
	public $empty_rows = 'Нет новых вопросов.';

	/**
	 * @var string условие для отбора
	 */
	public $where = "";

	/**
	 * @var array поля в таблице
	 */
	public $variables = array (
		'created' => array(
			'name' => 'Дата и время',
			'type' => 'datetime',
			'sql' => true,
		),
		'anons' => array(
			'name' => 'Вопрос',
			'type' => 'string',
			'sql' => true,
			'link' => true,
			'multilang' => true,
		),
	);

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		$this->diafan = &$diafan;
		$this->where = "anons"._LANG."<>'' AND text"._LANG."=''";
	}

}
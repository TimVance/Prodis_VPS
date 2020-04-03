<?php
/**
 * @package    DIAFAN.CMS
 *
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

/**
 * Diafan
 *
 * Каркас класса
 */
abstract class Diafan
{
	/**
	 * @var object основной объект системы
	 */
	public $diafan;

	/**
	 * var array локальный кэш файла
	 */
	protected $cache;

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		$this->diafan = &$diafan;
	}

}
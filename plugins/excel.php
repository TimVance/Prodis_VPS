<?php
/**
 * @package    DIAFAN.CMS
 *
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2015 OOO «Диафан» (http://www.diafan.ru/)
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

Custom::inc('plugins/PHPExcel.php');

/**
 * PHPExcel_СhunkReadFilter
 * 
 * Фильтр для чтения файла
 */
class PHPExcel_СhunkReadFilter implements PHPExcel_Reader_IReadFilter
{
	private $start_row = 0;
	private $end_row = 0;

	public function setRows($start_row, $chunk_size)
	{
		$this->start_row    = $start_row;
		$this->end_row      = $start_row + $chunk_size;
	}

	/**
     * Should this cell be read?
     *
     * @param    $column           Column address (as a string value like "A", or "IV")
     * @param    $row              Row number
     * @param    $worksheetName    Optional worksheet name
     * @return   boolean
     */
	public function readCell($column, $row, $worksheetName = '')
	{
		if (/*($row == 1) || */($row >= $this->start_row && $row < $this->end_row))
		{
			return true;
		}
		return false;
	}
}
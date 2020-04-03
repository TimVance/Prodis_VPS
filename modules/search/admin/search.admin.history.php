<?php
/**
 * История поиска
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
 * Search_admin_history
 */
class Search_admin_history extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'search_history';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Запрос',
			),
			'created' => array(
				'type' => 'datetime',
				'name' => 'Дата',
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'created' => array(
			'name' => 'Дата и время',
			'type' => 'datetime',
			'sql' => true,
			'no_important' => true,
		),
		'name' => array(
			'name' => 'Запрос',
		),
		'actions' => array(
			'del' => true,
		),
	);

	/**
	 * Выводит список категорий
	 * @return void
	 */
	public function show()
	{
		echo '
		<div class="box box_half box_height box_right">
			<div class="box__heading">'.$this->diafan->_('Экспорт').'</div>
			
			<a href="'.BASE_PATH.'search/history/?'.rand(0, 999999).'" class="file-load">
				<i class="fa fa-file-code-o"></i>
				'.$this->diafan->_('Скачать файл').'
			</a>
		</div>';
		$this->diafan->list_row();
	}
}
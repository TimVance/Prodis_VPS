<?php
/**
 * Редактирование ключевых слов
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

class Keywords_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'keywords';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'text' => array(
				'type' => 'text',
				'name' => 'Ключевое слово',
				'help' => 'Модуль найдет все слова на Вашем сайте и превратит их в ссылки на страницу, адрес которой нужно указать ниже.',
			),
			'link' => array(
				'type' => 'text',
				'name' => 'URL',
				'help' => 'URL-адрес страницы, куда будет вести ссылка с ключевого слова.',
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'help' => 'Публикация на сайте, активность.',
				'default' => true,
				'multilang' => true,
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'name' => array(	
			'variable' => 'id'
		),
		'text' => array(
			'name' => 'Ключевое слово',
			'sql' => true,
			'type' => 'text',
			'fast_edit' => true,
			'class' => 'text',
		),
		'link' => array(
			'name' => 'URL',
			'sql' => true,
			'type' => 'text',
			'fast_edit' => true,
			'class' => 'text',
		),
		'actions' => array(
			'act' => true,
			'trash' => true,
		),
	);

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить ссылку');
	}

	/**
	 * Выводит список ключевиков
	 * @return void
	 */
	public function show()
	{
		echo '<p>'.$this->diafan->_('Модуль ищет прямые вхождения всех указанных ниже ключевых слов на сайте и превращает их в ссылки на указанный URL.').'</p>';
		$this->diafan->list_row();
	}
}
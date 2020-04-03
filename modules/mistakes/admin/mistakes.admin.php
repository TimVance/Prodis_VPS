<?php
/**
 * Просмотр уведомлений об ошибках на сайте
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

class Mistakes_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'mistakes';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'created' => array(
				'type' => 'datetime',
				'name' => 'Дата и время',
				'help' => 'Дата и время добавления сообщения.',
			),
			'url' => array(
				'type' => 'text',
				'name' => 'URL',
				'help' => 'Относительный адрес страницы, на которой ошибка найдена.',
				'no_save' => true,
			),
			'selected_text' => array(
				'type' => 'textarea',
				'name' => 'Выделенный текст',
				'help' => 'Фрагмента текста на сайте, который пользователь выделили перед тем, как отправить сообщение.',
			),
			'comment' => array(
				'type' => 'textarea',
				'name' => 'Комментарий',
				'help' => 'Комментарий, оставленный пользователем, добавившим сообщение.',
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
			'name' => 'URL',
			'variable' => 'url',
		),
		'selected_text' => array(
			'name' => 'Выделенный текст',
			'type' => 'text',
			'sql' => true,
			'no_important' => true,
		),
		'comment' => array(
			'name' => 'Комментарий',
			'type' => 'text',
			'sql' => true,
			'no_important' => true,
		),
		'actions' => array(
			'del' => true,
		),
	);

	/**
	 * Выводит список ошибок на сайте
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();

		if (! $this->diafan->count)
		{
			echo '<center>'.$this->diafan->_('Чтобы соообщить об ошибке на Вашем сайте, посетители могут выделить текст и нажать Ctrl+Enter.').'</center>';
		}
	}

	/**
	 * Редактирование поля "URL"
	 * @return void
	 */
	public function edit_variable_url()
	{
		echo '<div class="unit" id="url">
		<b>'.$this->diafan->variable_name().':</b>  
		<a href="'.$this->diafan->value.'">'.$this->diafan->value.'</a>
		</div>';
	}
}
<?php
/**
 * Редактирование товаров
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
 * Shop_admin
 */
class Reglament_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'reglament';

    public $variables_list = array (
        'checkbox' => '',
        'name' => array(
            'name' => 'Название'
        ),
        'next_step' => array(
            'name' => 'Следующая активность',
            'type' => 'datetime',
            'sql' => true,
            'no_important' => true,
        ),
        'last_action' => array(
            'name' => 'Последняя активность',
            'type' => 'datetime',
            'sql' => true,
            'no_important' => true,
        ),
        'created' => array(
            'name' => 'Дата создания',
            'type' => 'datetime',
            'sql' => true,
            'no_important' => true,
        ),
        'actions' => array(
            'del' => true,
        ),
    );

    public $variables = array (
        'main' => array (
            'created' => array(
                'type' => 'datetime',
                'name' => 'Дата создания',
                'help' => 'Вводится в формате дд.мм.гггг чч:мм. Если указать будущую дату, новость начнет отображаться с этой даты.',
            ),
            'name' => array(
                'type' => 'text',
                'name' => 'Наименование',
                'help' => 'Свободное информационное текстовое поле.',
            ),
            'ids' => array(
                'type' => 'text',
                'name' => 'Номера заказов через запятую',
                'help' => 'Свободное информационное текстовое поле.',
            ),
            'replication' => array(
                'type' => 'function',
                'name' => 'Регламент',
            ),
            'reglament' => array(
                'type' => 'text',
                'name' => 'Регламент',
                'help' => 'Свободное информационное текстовое поле.',
            ),
            'next_step' => array(
                'type' => 'text',
                'default' => 0,
                'name' => 'Номера заказов через запятую',
                'help' => 'Свободное информационное текстовое поле.',
            ),
        ),
    );


    /**
     * @var array поля для фильтра
     */
    public $variables_filter = array (
        'name' => array(
            'type' => 'text',
            'name' => 'Искать по названию',
        ),
    );


	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить регламент', 'fa fa-plus-square');
	}

	/**
	 * Выводит список товаров
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

}

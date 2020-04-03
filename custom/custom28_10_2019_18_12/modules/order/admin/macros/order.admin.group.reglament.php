<?php
/**
 * Макрос для групповой операции: Изменение статуса
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2019 OOO «Диафан» (http://www.diafan.ru/)
 */

if (!defined('DIAFAN')) {
    $path = __FILE__;
    while (!file_exists($path . '/includes/404.php')) {
        $parent = dirname($path);
        if ($parent == $path) exit;
        $path = $parent;
    }
    include $path . '/includes/404.php';
}

/**
 * Order_admin_group_status
 */
class Order_admin_group_reglament extends Diafan
{
    /**
     * Возвращает настройки
     *
     * @param string $value последнее выбранное групповое действие
     * @return array|false
     */
    public function show($value)
    {
        $config = array(
            'name' => 'Регламент',
        );

        return $config;
    }

    /**
     * Изменение статуса
     *
     * @return void
     */
    public function action()
    {
        if (empty($_POST["ids"]))
            return;

        $ids = $this->diafan->filter($_POST["ids"], "integer");
        $this->diafan->redirect('https://'.$_SERVER["SERVER_NAME"].'/admin/reglament/addnew1/?orders='.implode(",", $_POST["ids"]));

    }
}
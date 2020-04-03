<?php
/**
 * Настройки платежной системы Robokassa для административного интерфейса
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

class Payment_robokassa_admin
{
	public $config;

	public function __construct()
	{
		$this->config = array(
			"name" => 'Robokassa',
			"params" => array(
                'robokassa_login' => 'Robokassa: логин',
                'robokassa_pass_1' => 'Robokassa: пароль 1',
                'robokassa_pass_2' => 'Robokassa: пароль 2',
				'robokassa_test' => array('name' => 'Тестовый режим', 'type' => 'checkbox'),
				'robokassa_receipt' => array('name' => 'Фискализация для клиентов Robokassa. Облачное решение', 'type' => 'checkbox'),
				'robokassa_sno' => array(
					'name' => 'Система налогообложения
магазина',
					'type' => 'select',
					'select' => array(
						'osn' => 'общая СН',
						'usn_income' => 'упрощенная СН (доходы)',
						'usn_income_outcome' => 'упрощенная СН (доходы минус расходы)',
						'envd' => 'единый налог на вмененный доход',
						'esn' => 'единый сельскохозяйственный налог',
						'patent' => 'патентная СН',
					),
					'help' => 'Нужен, только если у вас несколько систем налогообложения. В остальных случаях не передается.',
				),
				'robokassa_tax' => array(
					'name' => 'Ставка НДС',
					'type' => 'select',
					'select' => array(
						'none' => 'без НДС',
						'vat0' => 'НДС по ставке 0%',
						'vat10' => 'НДС чека по ставке 10%',
						'vat20' => 'НДС чека по ставке 20%',
						'vat110' => 'НДС чека по расчетной ставке 10/110',
						'vat120' => 'НДС чека по расчетной ставке 20/120',
					),
				),
			)
		);
	}
}

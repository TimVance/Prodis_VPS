<?php
/**
 * Настройки платежной системы Яндекс.Касса для административного интерфейса
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

class Payment_yandexmoney_admin
{
	public $config;
	private $diafan;

	public function __construct(&$diafan)
	{
		$this->diafan = &$diafan;
		$this->config = array(
			"name" => 'Яндекс.Касса',
			"params" => array(
				'yandex_scid' => 'sсId',
				'yandex_shopid' => 'shopId',
				'yandex_password' => 'shopPassword',
				'yandex_test' => array('name' => 'Тестовый режим', 'type' => 'checkbox'),
				'yandex_types' => array('name' => 'Способы оплаты', 'type' => 'function'),
				'yandex_taxSystem' => array(
					'name' => 'Система налогообложения
магазина',
					'type' => 'select',
					'select' => array(
						0 => '',
						1 => 'общая СН',
						2 => 'упрощенная СН (доходы)',
						3 => 'упрощенная СН (доходы минус
						расходы',
						4 => 'единый налог на вмененный
						доход',
						5 => 'единый сельскохозяйственный
						налог',
						6 => 'патентная СН',
					),
					'help' => 'Нужен, только если у вас несколько систем налогообложения. В остальных случаях не передается.',
				),
				'yandex_tax' => array(
					'name' => 'Ставка НДС',
					'type' => 'select',
					'select' => array(
						1 => 'без НДС',
						2 => 'НДС по ставке 0%',
						3 => 'НДС чека по ставке 10%',
						4 => 'НДС чека по ставке 20%',
						5 => 'НДС чека по расчетной ставке
						10/110',
						6 => 'НДС чека по расчетной ставке
						20/120',
					),
				),
			)
		);
	}
	
	/**
	 * Редактирвание поля "Способы оплаты"
	 *
	 * @return void
	 */
	public function edit_variable_yandex_types($value)
	{
		if($value)
		{
			$vs = array_keys($value);
		}
		else
		{
			$vs = array();
		}
		$types = array(
		    'PC' => 'Яндекс.Деньги',
			'AC' => 'Банковская карта',
			'MC' => 'Мобильный телефон',
			'GP' => 'Терминалы',
			'WM' => 'WebMoney',
			'SB' => 'Сбербанк Онлайн',
			'MP' => 'Мобильный терминал (mPOS)',
			'AB' => 'Альфа-Клик',
			'МА' => 'Оплата через MasterPass',
			'PB' => 'Оплата через Промсвязьбанк',
			'QW' => 'Оплата через QIWI Wallet',
		);
		echo '<div class="unit tr_payment" payment="yandexmoney" style="display:none">
			<div class="infofield">'.$this->diafan->_('Способы оплаты').'</div>';
			foreach($types as $k => $v)
			{
				echo '<input type="checkbox" name="yandex_types['.$k.']" id="input_yandex_types_'.$k.'" value="'.$v.'"'.(in_array($k, $vs) ? ' checked' : '').' class="label_full"> <label for="input_yandex_types_'.$k.'">'.$this->diafan->_($v).'</label>';
			}
			echo '
		</div>';
	}
	
	/**
	 * Сохранение поля "Способы оплаты"
	 *
	 * @return string
	 */
	public function save_variable_yandex_types()
	{
		if(empty($_POST["yandex_types"]))
		{
			$_POST["yandex_types"] = array();
		}
		return $_POST["yandex_types"];
	}
}
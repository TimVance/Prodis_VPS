<?php
/**
 * Настройки платежной системы «Банковские платежи» для административного интерфейса
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

class Payment_non_cash_admin
{
	public $config;
	private $diafan;

	public function __construct(&$diafan)
	{
		$this->diafan = &$diafan;
		$this->config = array(
			"name" => 'Банковские платежи',
			"params" => array(
			'non_cash_name' => 'Наименование организации',
			'non_cash_ogrn' => 'ОГРН',
			'non_cash_inn' => 'ИНН',
			'non_cash_kpp' => 'КПП',
			'non_cash_rs' => 'Расч. счет',
			'non_cash_bank' => 'Банк',
			'non_cash_bik' => 'БИК',
			'non_cash_ks' => 'Кор. счет',
			'non_cash_address' => 'Адрес',
			'non_cash_director' => 'Руководитель предприятия',
			'non_cash_glbuh' => 'Главный бухгалтер',
			'non_cash_kbk' => 'КБК',
			'non_cash_tax_department' => 'Сокр. наим. налогового органа',
			'non_cash_okato' => 'Код ОКАТО',
			'non_cash_pechat' => array('name' => 'Печать', 'type' => 'function')
			)
		);
	}
	
	/**
	 * Редактирвание поля "Печать"
	 *
	 * @return void
	 */
	public function edit_variable_non_cash_pechat()
	{
		echo '<div class="unit tr_payment" payment="non_cash" style="display:none">
			<div class="infofield">'.$this->diafan->_('Печать').'</div>';
		if(file_exists(ABSOLUTE_PATH.USERFILES.'/shop/non_cash.pechat.jpg'))
		{
			echo '<p><img src="'.BASE_PATH.USERFILES.'/shop/non_cash.pechat.jpg?'.rand(0, 888888).'"></p>';
		}
		echo '<input type="file" value="" size="40" name="non_cash_pechat" class="file">
			<div class="infofield hide">'.$this->diafan->_('Ширина изображения').' <i class="tooltip fa fa-question-circle" title="'.$this->diafan->_('Ширина, которая будет установлена для нового изображения.').'"></i></div>
			<input type="text" class="number hide" value="150" name="non_cash_pechat_width">
			<div class="infofield hide">'.$this->diafan->_('Высота изображения').' <i class="tooltip fa fa-question-circle" title="'.$this->diafan->_('Высота, которая будет установлена для нового изображения.').'"></i></div>
			<input type="text" class="number hide" value="150" name="non_cash_pechat_height">
			<div class="infofield hide">'.$this->diafan->_('Качество изображения').' <i class="tooltip fa fa-question-circle" title="'.$this->diafan->_('Качество, которое будет установлено для нового изображения.').'"></i></div>
			<input type="text" class="number hide" value="90" name="non_cash_pechat_quality">
		</div>';
	}
	
	/**
	 * Сохранение поля "Печать"
	 *
	 * @return void
	 */
	public function save_variable_non_cash_pechat()
	{
		if(isset( $_FILES["non_cash_pechat"] ) && is_array($_FILES["non_cash_pechat"]) && $_FILES["non_cash_pechat"]['name'] != '')
		{
			$width = $this->diafan->filter($_POST, 'int', "non_cash_pechat_width", 150);
			$width = $width > 0 ? $width : 1;
			$height = $this->diafan->filter($_POST, 'int', "non_cash_pechat_height", 150);
			$height = $height > 0 ? $height : 1;
			$quality = $this->diafan->filter($_POST, 'int', "non_cash_pechat_quality", 90);
			$quality = $quality > 0 ? $quality : 1;

			Custom::inc("includes/image.php");
			Image::resize($_FILES["non_cash_pechat"]['tmp_name'], $width, $height, $quality);
			File::copy_file($_FILES["non_cash_pechat"]['tmp_name'], USERFILES.'/shop/non_cash.pechat.jpg');
		}
	}
}
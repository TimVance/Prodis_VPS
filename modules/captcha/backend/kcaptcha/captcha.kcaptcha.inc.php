<?php
/**
 * Подключение для работы с капчей «Код на картинке»
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2019 OOO «Диафан» (http://www.diafan.ru/)
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
 * Captcha_kcaptcha_inc
 */
class Captcha_kcaptcha_inc extends Diafan
{
	/**
	 * Выводит капчу
	 * 
	 * @param string $modules метка капчи
	 * @param string $error ошибка ввода кода, если запрос передан не через Ajax
	 * @param boolean $is_update капча генерируется для обновления
	 * @return string
	 */
	public function get($modules, $error, $is_update)
	{
		$result = array("modules" => $modules, "error" => $error);
		ob_start();
		include(Custom::path('modules/captcha/backend/kcaptcha/captcha.kcaptcha.view'.($is_update ? '.update' : '').'.php'));
		$text = ob_get_contents();
		ob_end_clean();
		return $text;
	}

	/**
	 * Проверяет правильность ввода капчи
	 * 
	 * @param string $modules метка капчи
	 * @return string|boolean false
	 */
	public function check($modules)
	{
		//Защитный код не введен
		if (empty($_POST['captcha']) || empty($_POST['captchaint']))
			return $this->diafan->_('Введите защитный код', false);

		//В сессии не записан код с данным идентификатором captchaint
		if (! isset($_SESSION['captcha'][$modules][$_POST['captchaint']])
		||
		//код из сессии не соответствует введенному. регистр не учитывается
		strtoupper($_SESSION['captcha'][$modules][$_POST['captchaint']]) != strtoupper($_POST['captcha']))
			return $this->diafan->_('Неправильно введен защитный код.', false);

		//очищаем из сессии запись с данным идентификатором
		unset($_SESSION['captcha'][$modules][$_POST['captchaint']]);
		return false;
	}
}
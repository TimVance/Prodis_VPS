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
 * Captcha_qa_inc
 */
class Captcha_qa_inc extends Diafan
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
		$result = array("error" => $error, "text" => '', "answers" => array());
		
		$capcha = DB::query_fetch_array("SELECT [name], id, is_write FROM {captcha_qa} WHERE [act]='1' ORDER BY RAND () LIMIT 1");
		if($capcha)
		{
			$_SESSION["captcha_id"] = $capcha["id"];
			$result["text"] = $capcha["name"];
			if(! $capcha["is_write"])
			{
				$result["answers"] = DB::query_fetch_all("SELECT [text], id FROM {captcha_qa_answers} WHERE captcha_id=%d ORDER BY RAND()", $capcha["id"]);
			}
		}
		ob_start();
		include(Custom::path('modules/captcha/backend/qa/captcha.qa.view'.($is_update ? '.update' : '').'.php'));
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
		if(empty($_SESSION["captcha_id"]))
		{
			return $this->diafan->_('Выберите правильный ответ.', false);
		}
		$row = DB::query_fetch_array("SELECT * FROM {captcha_qa} WHERE id=%d", $_SESSION["captcha_id"]);
		if(! $row)
		{
			return $this->diafan->_('Выберите правильный ответ.', false);
		}
		if($row["is_write"])
		{
			if(empty($_POST["captcha_answer"]))
			{
				return $this->diafan->_('Впишите правильный ответ.', false);
			}
			if(! DB::query_result("SELECT COUNT(*) FROM {captcha_qa_answers} WHERE captcha_id=%d AND [text]='%s'", $row["id"], utf::strtolower($_POST["captcha_answer"])))
			{
				return $this->diafan->_('Ответ не верный.', false);
			}
			return false;
		}
		else
		{
			if(empty($_POST["captcha_answer_id"]))
			{
				return $this->diafan->_('Выберите правильный ответ.', false);
			}
			if(! DB::query_result("SELECT COUNT(*) FROM {captcha_qa_answers} WHERE captcha_id=%d AND id=%d AND is_right='1'", $_SESSION["captcha_id"], $_POST["captcha_answer_id"]))
			{
				return $this->diafan->_('Ответ не верный.', false);
			}
		}
		return false;
	}
}
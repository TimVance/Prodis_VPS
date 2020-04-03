<?php
/**
 * Шаблон формы для капчи «Вопрос-Ответ»
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

$codeint = rand(1111, 9999);
echo '<div class="block captcha">';
echo '<div class="captcha_enter">'.$this->diafan->_('Выберите правильный ответ').'</div>
'.$result["text"];
if($result["answers"])
{
	foreach ($result["answers"] as $row)
	{
		$rand = rand(0, 999);
		echo '<br><input name="captcha_answer_id" type="radio" value="'.$row["id"].'" id="captcha_radio'.$row["id"].'_'.$rand.'"'.($row == $result["answers"][0] ? " checked" : '').'>
		<label for="captcha_radio'.$row["id"].'_'.$rand.'">'.$row["text"].'</label>';
	}
}
else
{
	echo '<br><input name="captcha_answer" type="text" value="">';
}
echo '
<input type="hidden" name="captcha_update" value="">

<div class="js_captcha_update captcha_update"><a href="javascript:void(0)">'.$this->diafan->_('Обновить вопрос').'</a></div>

<div class="errors error_captcha"'.($result["error"] ? '>'.$result["error"] : ' style="display:none">').'</div>';

echo '</div>';
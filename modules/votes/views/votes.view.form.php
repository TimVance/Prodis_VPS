<?php
/**
 * Шаблон вопросов для голосования
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

echo '
<input type="hidden" name="question" value="'.$result["id"].'">
<input type="hidden" name="module" value="votes">
<input type="hidden" name="form_tag" value="'.$result["form_tag"].'">
<input type="hidden" name="result" value="">';
if (! empty($result))
{
	foreach ($result["rows"] as $row)
	{
		echo '<input type="radio" name="answer" id="votes_radio'.$row["id"].'_'.$result["id"].'" value="'.$row["id"].'"'.($row == $result["rows"][0] ? " checked" : '').'>
		<label for="votes_radio'.$row["id"].'_'.$result["id"].'">'.$row["text"].'</label>
		</br>';
	}
	if(! empty($result['userversion']))
	{
		echo '<div>
			<input type="radio" name="answer" value="userversion" id="votes_userversion_'.$result["id"].'">
			<label for="votes_userversion_'.$result["id"].'">'.$this->diafan->_('Свой вариант').'</label>
			<div class="js_votes_userversion votes_userversion" style="display: none;"><input type="text" name="userversion"></div>
		</div>';
	}
	
	echo $result["captcha"];

	echo '<input type="submit" value="'.$this->diafan->_('Отправить', false).'">';
	if(empty($result["no_result"]))
	{
		echo '<input type="button" value="'.$this->diafan->_('Результаты', false).'">';
	}
}
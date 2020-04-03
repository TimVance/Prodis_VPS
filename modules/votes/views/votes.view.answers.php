<?php
/**
 * Шаблон результатов голосования
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

echo '<input type="hidden" name="question" value="'.$result["id"].'">
<input type="hidden" name="module" value="votes">
<input type="hidden" name="result" value="2">';
if (! empty($result))
{
	if(! empty($result['no_result']))
	{
	    echo '<div class="votes_no_result">'.$this->diafan->_('Результаты голосования недоступны.').'</div>';
	}
	else
	{
	    foreach ($result["rows"] as $row)
	    {
			echo '<div class="votes_answer">'.$row["text"];
			echo '<div class="votes_persent">'.$row["persent"].'%</div>';
			echo '<div class="votes_line_count">'.$row["count"].'</div>';
			echo '<div class="votes_line" style="width: '.$row["persent"].'%"></div>';
			echo '</div>';
	    }
	    echo '<div class="votes_count">'.$this->diafan->_('Количество проголосовавших').': '.$result["summ"].'</div>';
	}
	
	if(! empty($result["result"]))
	{
		echo '<input type="submit" value="'.$this->diafan->_('Голосовать', false).'">';
	}
}
<?php
/**
 * Настройки капчи «Вопрос-Ответ»
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

class Captcha_qa_admin extends Diafan
{
	public $config = array(
		'name' => 'Впорос-Ответ',
		'params' => array(
			'list' => array(
				'type' => 'function',
			),
		),
	);
	
	/**
	 * Редактирвание списка вопросов и ответов
	 *
	 * @return void
	 */
	public function edit_variable_list()
	{
		echo '<div class="unit tr_backend" backend="qa">
		<div class="infofield">'.$this->diafan->_('Список вопросов').'</div>';

		$answers = DB::query_fetch_key_array("SELECT id, [text], is_right, captcha_id FROM {captcha_qa_answers} ORDER BY id ASC", "captcha_id");

		$rows = DB::query_fetch_all("SELECT id, [name], [act], is_write FROM {captcha_qa} ORDER BY id ASC");
		if(! $rows)
		{
			$rows  = array(0);
		}
		
		foreach ($rows as $i => $row)
		{
			echo '
			<div class="js_captcha_qa_item param">
				<input type="hidden" name="captcha_qa_i[]" value="'.($i + 1).'_">
				<input type="hidden" name="captcha_qa_id[]" value="'.(! empty($row["id"]) ? $row["id"] : '').'">
				<input type="text" name="captcha_qa_text[]" value="'.(! empty($row["name"]) ? str_replace('"', '&quot;', $row["name"]) : '').'">
				<span class="param_actions">
					<a href="javascript:void(0)" action="delete_param" class="delete" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'"><i class="fa fa-close" title="'.$this->diafan->_('Удалить').'"></i></a>
				</span>
				<br><input type="hidden" name="captcha_qa_act[]" value="'.(! empty($row["act"]) ? '1' : '').'"><input type="checkbox" value="1" id="captcha_qa_act_'.$i.'"'.(! empty($row["act"]) ? ' checked' : '').'>
				<label for="captcha_qa_act_'.$i.'">'.$this->diafan->_('Показывать на сайте').'</label>
				&nbsp;&nbsp;<input type="hidden" name="captcha_qa_is_write[]" value="'.(! empty($row["is_write"]) ? '1' : '').'"><input type="checkbox" value="1" id="captcha_qa_is_write_'.$i.'"'.(! empty($row["is_write"]) ? ' checked' : '').'>
				<label for="captcha_qa_is_write_'.$i.'">'.$this->diafan->_('Ответы не отображаются на сайте').'</label>
				<br>'.$this->diafan->_('Ответы').':';

				if(empty($answers[$row["id"]]))
				{
					$answers[$row["id"]] = array(0);
				}
				foreach($answers[$row["id"]] as $ii => $a)
				{
					echo '
					<div class="js_captcha_qa_a param" style="margin-left: 50px;">
						<input type="hidden" name="captcha_qa_a_i[]" value="'.($i + 1).'_">
						<input type="hidden" name="captcha_qa_a_id[]" value="'.(! empty($a["id"]) ? $a["id"] : '').'">
						<input type="text" name="captcha_qa_a_text[]" value="'.(! empty($a["text"]) ? str_replace('"', '&quot;', $a["text"]) : '').'">
						<span class="param_actions">
							<a href="javascript:void(0)" action="delete_a" class="delete" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'"><i class="fa fa-close" title="'.$this->diafan->_('Удалить').'"></i></a>
						</span>
						<br><input type="hidden" name="captcha_qa_a_is_right[]" value="'.(! empty($a["is_right"]) ? '1' : '').'"><input type="checkbox" value="1" id="captcha_qa_a_is_right_'.$i.'_'.$ii.'"'.(! empty($a["is_right"]) ? ' checked' : '').'>
						<label for="captcha_qa_a_is_right_'.$i.'_'.$ii.'">'.$this->diafan->_('Правильный').'</label>
					</div>';
				}
				echo '<a href="javascript:void(0)" class="js_captcha_qa_a_plus param_plus" title="'.$this->diafan->_('Добавить ответ').'" style="margin-left: 50px;"><i class="fa fa-plus-square"></i> '.$this->diafan->_('Добавить ответ').'</a>';
			echo '</div>';
		}
		echo '
		<div class="js_captcha_qa_item_tpl param" style="display:none">
			<input type="hidden" name="captcha_qa_captcha_id[]" value="">
			<input type="hidden" name="captcha_qa_i[]" value="">
			<input type="text" name="captcha_qa_text[]" value="">
			<span class="param_actions">
				<a href="javascript:void(0)" action="delete_param" class="delete" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'"><i class="fa fa-close" title="'.$this->diafan->_('Удалить').'"></i></a>
			</span>
			<br><input type="hidden" name="captcha_qa_act[]" value=""><input type="checkbox" value="1" id="captcha_qa_act_">
			<label for="captcha_qa_act_">'.$this->diafan->_('Показывать на сайте').'</label>
			&nbsp;&nbsp;<input type="hidden" name="captcha_qa_is_write[]" value=""><input type="checkbox" value="1" id="captcha_qa_is_write_">
			<label for="captcha_qa_is_write_">'.$this->diafan->_('Ответы не отображаются на сайте').'</label>
			<br>'.$this->diafan->_('Ответы').':
			<div class="js_captcha_qa_a param" style="margin-left: 50px;">
				<input type="hidden" name="captcha_qa_a_id[]" value="">
				<input type="hidden" name="captcha_qa_a_i[]" value="">
				<input type="text" name="captcha_qa_a_text[]" value="">
				<span class="param_actions">
					<a href="javascript:void(0)" action="delete_a" class="delete" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'"><i class="fa fa-close" title="'.$this->diafan->_('Удалить').'"></i></a>
				</span>
				<br><input type="hidden" name="captcha_qa_a_is_right[]" value=""><input type="checkbox" value="1" id="captcha_qa_a_is_right_">
				<label for="captcha_qa_a_is_right_">'.$this->diafan->_('Правильный').'</label>
			</div>
			<a href="javascript:void(0)" class="js_captcha_qa_a_plus param_plus" title="'.$this->diafan->_('Добавить ответ').'" style="margin-left: 50px;"><i class="fa fa-plus-square"></i> '.$this->diafan->_('Добавить ответ').'</a>
		</div>';
		echo '<a href="javascript:void(0)" class="js_captcha_qa_plus param_plus" title="'.$this->diafan->_('Добавить вопрос').'"><i class="fa fa-plus-square"></i> '.$this->diafan->_('Добавить вопрос').'</a>';
		echo '</div>';
	}
	
	/**
	 * Сохранение списка вопросов и ответов
	 *
	 * @return void
	 */
	public function save_variable_list()
	{
		$captcha_id = array();
		$answer_id = array();
		if(empty($_POST["captcha_qa_text"]))
		{
			$_POST["captcha_qa_text"] = array();
		}
		if(empty($_POST["captcha_qa_a_text"]))
		{
			$_POST["captcha_qa_a_text"] = array();
		}
		foreach($_POST["captcha_qa_text"] as $i => $t)
		{
			if(! trim($t))
				continue;

			if(! empty($_POST["captcha_qa_id"][$i]))
			{
				DB::query("UPDATE {captcha_qa} SET [name]='%h', [act]='%d', is_write='%d' WHERE id=%d", $_POST["captcha_qa_text"][$i], $_POST["captcha_qa_act"][$i], $_POST["captcha_qa_is_write"][$i], $_POST["captcha_qa_id"][$i]);
				$id = $this->diafan->filter($_POST["captcha_qa_id"][$i], "integer");
			}
			else
			{
				$id = DB::query("INSERT INTO {captcha_qa} ([name], [act], is_write) VALUES ('%h', '%d', '%d')", $_POST["captcha_qa_text"][$i], $_POST["captcha_qa_act"][$i], $_POST["captcha_qa_is_write"][$i]);
			}
			$captcha[$_POST["captcha_qa_i"][$i]] = $id;
			$captcha_id[] = $id;
		}
		foreach($_POST["captcha_qa_a_text"] as $i => $t)
		{
			if(! trim($t) || empty($captcha[$_POST["captcha_qa_a_i"][$i]]))
				continue;

			if(! empty($_POST["captcha_qa_a_id"][$i]))
			{
				DB::query("UPDATE {captcha_qa_answers} SET [text]='%h', is_right='%d' WHERE id=%d", $_POST["captcha_qa_a_text"][$i], $_POST["captcha_qa_a_is_right"][$i], $_POST["captcha_qa_a_id"][$i]);
				$answer_id[] = $this->diafan->filter($_POST["captcha_qa_a_id"][$i], "integer");
			}
			else
			{
				$answer_id[] = DB::query("INSERT INTO {captcha_qa_answers} ([text], captcha_id, is_right) VALUES ('%h', %d, '%d')", $_POST["captcha_qa_a_text"][$i], $captcha[$_POST["captcha_qa_a_i"][$i]], $_POST["captcha_qa_a_is_right"][$i]);
			}
		}
		DB::query("DELETE FROM {captcha_qa}".($captcha_id ? " WHERE id NOT IN (%s)" : ''), implode(",", $captcha_id));
		DB::query("DELETE FROM {captcha_qa_answers}".($answer_id ? " WHERE id NOT IN (%s)" : ''), implode(",", $answer_id));
	}
}

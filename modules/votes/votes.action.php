<?php
/**
 * Обработка запроса при голосовании
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

class Votes_action extends Action
{
	/**
	 * Обрабатывает полученные данные из формы
	 * 
	 * @return void
	 */
	public function init()
	{
		if (empty($_POST['question']))
		{
			return;
		}
		if (empty($_POST["result"]))
		{
			$this->check_element();

			if ($this->result())
				return;

			if($this->diafan->configmodules('security_user', 'votes'))
			{
				$this->check_user();

				if ($this->result())
					return;
			}

			if ($this->diafan->_captcha->configmodules('votes'))
			{
				$this->check_captcha();
			}
			$this->check_log();

			if ($this->result())
				return;

			if($_POST['answer'] != 'userversion')
			{
				DB::query("UPDATE {votes_answers} SET count_votes=count_votes+1 WHERE id='%d' AND votes_id=%d", $_POST["answer"], $_POST["question"]);
			}
			else
			{
				DB::query("INSERT INTO {votes_userversion} (votes_id, text) VALUES (%d, '%s')", $_POST["question"], $_POST['userversion']);
			}
			
		}
		else
		{
			$result["result"] = true;
		}

		$result["id"] = intval($_POST["question"]);
		if (empty($_POST["result"]) || $_POST["result"] == 1)
		{
			// показать результаты опроса
			$userversion = DB::query_result("SELECT userversion FROM {votes} WHERE id=%d", $_POST["question"]);
			$result["rows"] = DB::query_fetch_all("SELECT count_votes as count, [text] FROM {votes_answers} WHERE trash='0' AND votes_id=%d ORDER BY sort ASC, id ASC", $_POST["question"]);
			$result["summ"] = 0;
			foreach ($result["rows"] as &$row)
			{
				$result["summ"] += $row["count"];
			}
			if(! empty($userversion))
			{
				$userversion_summ = DB::query_result("SELECT COUNT(id) FROM {votes_userversion} WHERE votes_id=%d", $_POST["question"]);
				$result["summ"] = $result["summ"] + $userversion_summ;
			}
			foreach ($result["rows"] as &$row)
			{
				$row["persent"] = $result["summ"] ? round($row["count"] / $result["summ"] * 100) : 0;
			}
			if(! empty($userversion))
			{
				$r = array(
					"count" => $userversion_summ,
					"persent" => $result["summ"] ? round($userversion_summ / $result["summ"] * 100) : 0,
					"text" => $this->diafan->_('Свой вариант'),
				);
				$result["rows"][] = $r;
			}
			
			$result["no_result"] = DB::query_result("SELECT no_result FROM {votes} WHERE id=%d", $_POST["question"]);

			$this->result["data"] = array('#votes'.$_POST["question"] => $this->diafan->_tpl->get('answers', 'votes', $result));
		}
		else
		{
			// показать опрос
			$result["rows"] = DB::query_fetch_all("SELECT id, [text] FROM {votes_answers} WHERE trash='0' AND votes_id=%d ORDER BY sort ASC, id ASC", $_POST["question"]);
			$result["form_tag"] = 'votes'.$result["id"];
			$result["captcha"] = '';
			if ($this->diafan->_captcha->configmodules('votes'))
			{
				$result["captcha"] = $this->diafan->_captcha->get($result["form_tag"], "");
			}
			$this->result["data"] = array('#votes'.$_POST["question"] => $this->diafan->_tpl->get('form', 'votes', $result));
		}
	}

	/**
	 * Проверяет существует ли вопрос и ответ в базе
	 * 
	 * @return void
	 */
	private function check_element()
	{
		if (empty($_POST['answer']))
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}
		if (! DB::query_result("SELECT id FROM {votes} WHERE id=%d LIMIT 1", $_POST["question"]))
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}
		if (! DB::query_result("SELECT id FROM {votes_answers} WHERE id=%d AND votes_id=%d LIMIT 1", $_POST['answer'], $_POST["question"]) && $_POST['answer'] != 'userversion')
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}
		if($_POST['answer'] == 'userversion' && empty($_POST['userversion']))
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}
	}

	/**
	 * Проверяет повторное голосование
	 * 
	 * @return void
	 */
	private function check_log()
	{
		if ($this->diafan->configmodules('security', 'votes') == 3)
		{
			if (DB::query_result("SELECT id FROM {log_note} WHERE session_id='%s' AND element_id='%d' AND include_name='votes' LIMIT 1",
								$this->diafan->configmodules('security_user', 'votes') ? $this->diafan->_users->id : $this->diafan->_session->id, $_POST["question"]))
			{
				$this->result["errors"][0] = $this->diafan->_('Вы уже голосовали', false);
			}
			else
			{
				DB::query("INSERT INTO {log_note} (include_name, element_id, note, created, ip, session_id)"
						  ." VALUES ('votes', '%d', '%d', %d, '%s', '%s')",
						  $_POST["question"],
						  $_POST["answer"],
						  time(),
						  getenv('REMOTE_ADDR'),
						  $this->diafan->configmodules('security_user', 'votes') ? $this->diafan->_users->id : $this->diafan->_session->id
						 );
			}
		}
		elseif ($this->diafan->configmodules('security', 'votes') == 4)
		{
			if (! empty($_SESSION["votes"][$_POST["question"]]))
			{
				$this->result["errors"][0] = $this->diafan->_('Вы уже голосовали', false);
			}
			$_SESSION["votes"][$_POST["question"]] = 1;
		}
	}
}
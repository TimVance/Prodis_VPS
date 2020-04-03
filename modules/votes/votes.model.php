<?php
/**
 * Модель
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

/**
 * Votes_model
 */
class Votes_model extends Model
{
	/**
	 * Генерирует данные для шаблонной функции: опросы
	 *
	 * @param integer $id идентификатор опроса
	 * @param integer $count количество опросов
	 * @param string $sort сортировка опросов
	 * @return array
	 */
	public function show_block($id, $count, $sort)
	{
		$result["rows"] = array();
		if (! empty($id))
		{
			$result["rows"] = DB::query_fetch_all(
				"SELECT c.id, c.[name], c.no_result, c.userversion FROM {votes} as c"
				." INNER JOIN {votes_site_rel} AS r ON r.element_id=c.id AND (r.site_id=%d OR r.site_id=0)"
				." WHERE c.[act]='1' AND c.trash='0'"
				." AND c.id=%d GROUP BY c.id", $this->diafan->_site->id, $id
			);
		}
		else
		{
			$rows = DB::query_fetch_all(
				"SELECT c.id, c.[name], c.no_result, c.userversion FROM {votes} as c"
				." INNER JOIN {votes_site_rel} AS r ON r.element_id=c.id AND (r.site_id=%d OR r.site_id=0)"
				." WHERE c.[act]='1' AND c.trash='0'"
				." GROUP BY c.id ORDER BY c.sort ASC", $this->diafan->_site->id
			);

			if($count === "all")
			{
				$count = count($rows);
			}
			if($sort == 'rand')
			{
				shuffle($rows);
			}
			$result["rows"] = array_slice($rows, 0, $count);
		}
		foreach ($result["rows"] as &$row)
		{
			if(empty($this->cache["prepare"]) || ! in_array($row["id"], $this->cache["prepare"]))
			{
				$this->cache["prepare"][] = $row["id"];
			}
		}
		foreach ($result["rows"] as &$row)
		{
			$this->element($row);
		}

		return $result;
	}

	/**
	 * Подготавливает данные о опросе
	 *
	 * @param array $row опрос
	 * @return array
	 */
	private function element(&$result)
	{
		if(! empty($this->cache["prepare"]))
		{
			foreach ($this->cache["prepare"] as $id)
			{
				$this->cache["rows"][$id] = array();
				$this->cache["summ"][$id] = 0;
				$this->cache["userversion_summ"][$id] = 0;
			}
			if($this->diafan->configmodules("sort_count_votes", "votes"))
			{
				$sort = "count_votes DESC";
			}
			else
			{
				$sort = "sort ASC";
			}
			$rows_answer = DB::query_fetch_all("SELECT id, [text], votes_id, count_votes FROM {votes_answers} WHERE trash='0' AND votes_id IN (%s) ORDER BY ".$sort, implode(",", $this->cache["prepare"]));
			foreach ($rows_answer as $row_answer)
			{
				$this->cache["rows"][$row_answer["votes_id"]][] = $row_answer;
				$this->cache["summ"][$row_answer["votes_id"]] += $row_answer["count_votes"];
			}
			$rows_uv = DB::query_fetch_all("SELECT COUNT(*) as count, votes_id FROM {votes_userversion} WHERE trash='0' AND votes_id IN (%s) GROUP BY votes_id", implode(",", $this->cache["prepare"]));
			foreach ($rows_uv as $row_uv)
			{
				$this->cache["userversion_summ"][$row_uv["votes_id"]] = $row_uv["count"];
				$this->cache["summ"][$row_uv["votes_id"]] += $row_uv["count"];
			}
			unset($this->cache["prepare"]);
		}

		$result["rows"] = array();

		$fields = array('', 'captcha');
		$result['form_tag'] = 'votes'.$result["id"];
		$this->form_errors($result, $result['form_tag'], $fields);

		if ($this->check_log($result["id"]))
		{
			$result["captcha"] = '';
			if ($this->diafan->_captcha->configmodules('votes'))
			{
				$result["captcha"] = $this->diafan->_captcha->get($result['form_tag'], $result['error_captcha']);
			}
			$result["rows"] = $this->cache["rows"][$result["id"]];
			foreach ($result["rows"] as &$answer)
			{
				$answer["text"] = $this->diafan->_useradmin->get($answer["text"], 'text', $answer["id"], 'votes', _LANG);
			}
			$result["rows"] = $this->diafan->_tpl->get('form', 'votes', $result);
		}
		else
		{
			$result["rows"] = $this->cache["rows"][$result["id"]];
			foreach ($result["rows"] as &$answer)
			{
				$answer["count"] = $answer["count_votes"];
				$answer["persent"] = $this->cache["summ"][$result["id"]] ? round($answer["count_votes"] / $this->cache["summ"][$result["id"]] * 100) : 0;
			}
			
			if($result["userversion"])
			{
				$ra["count"] = $this->cache["userversion_summ"][$result["id"]];
				$ra["persent"] = $this->cache["summ"][$result["id"]] ? round($this->cache["userversion_summ"][$result["id"]] / $this->cache["summ"][$result["id"]] * 100) : 0;
				$ra["text"] = $this->diafan->_('Свой вариант');
				$result["rows"][] = $ra;
			}

			$result["summ"] = $this->cache["summ"][$result["id"]];
			$result["rows"] = $this->diafan->_tpl->get('answers', 'votes', $result);
		}			
		$result["name"] = $this->diafan->_useradmin->get($result["name"], 'name', $result["id"], 'votes', _LANG);
	}

	/**
	 * Проверяет доступ к голосованию
	 * 
	 * @return boolean
	 */
	private function check_log($id)
	{
		if ($this->diafan->configmodules('security_user', 'votes') && ! $this->diafan->_users->id)
		{
			return false;
		}
		if ($this->diafan->configmodules('security', 'votes') == 3)
		{
			if (DB::query_result("SELECT id FROM {log_note} WHERE session_id='%s' AND element_id='%d' AND include_name='votes' LIMIT 1",
			                    $this->diafan->configmodules('security_user', 'votes') ? $this->diafan->_users->id : $this->diafan->_session->id, $id))
			{
				return false;
			}
		}
		elseif ($this->diafan->configmodules('security', 'votes') == 4)
		{
			if (! empty($_SESSION["votes"][$id]))
			{
				return false;
			}
		}
		return true;
	}
}
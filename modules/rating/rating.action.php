<?php
/**
 * Обрабатывает полученные данные из формы
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

class Rating_action extends Action
{
	/**
	 * @var string название модуля, элемент которого оценивается
	 */
	private $module_name;

	/**
	 * @var integer номер элемента, который оценивается
	 */
	private $element_id;

	/**
	 * @var string тип элемента
	 */
	private $element_type;

	/**
	 * Обработка запроса при оценке элемента
	 * 
	 * @return void
	 */
	public function init()
	{
		if (empty($_POST['rating']) || empty($_POST['element_id']) || empty($_POST['element_type']) || empty($_POST['module_name']))
		{
			return;
		}
		$this->element_id  = intval($_POST["element_id"]);
		$this->module_name = preg_replace('/[^a-zA-Z0-9-_]+/', '', $_POST['module_name']);
		$this->element_type = preg_replace('/[^a-zA-Z0-9-_]+/', '', $_POST['element_type']);

		$this->check_element();

		if ($this->result())
			return;

		if ($this->diafan->configmodules('only_user', 'rating'))
		{
			$this->check_user();

			if ($this->result())
				return;
		}

		$this->check_log();

		if ($this->result())
			return;

		if ($id = DB::query_result("SELECT id FROM {rating} WHERE element_id=%d AND element_type='%s' AND module_name='%s' LIMIT 1", $this->element_id, $this->element_type, $this->module_name)
		   )
		{ 
			DB::query("UPDATE {rating} SET rating=(rating*count_votes+%d)/(count_votes+1), count_votes=count_votes+1,created='%d' WHERE id=%d", $_POST["rating"], time(), $id);
		}
		else
		{			
			DB::query("INSERT INTO {rating} (rating, count_votes, element_id, element_type, module_name, created) VALUES ('%d', 1, '%d', '%s', '%s', '%d')",
				$_POST["rating"],
				$this->element_id,
				$this->element_type,
				$this->module_name,
				time()
			);
		}

		$this->diafan->_cache->delete('', 'cache_extreme');

		$this->result["result"] = "success";
	}

	/**
	 * Проверяет существует ли оцениваемый элемент
	 * 
	 * @return void
	 */
	private function check_element()
	{
		if (! $this->element_id || ! $this->module_name)
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}
		$table = $this->module_name;
		switch($this->element_type)
		{
			case 'element':
				break;

			case 'cat':
				$table .= '_category';
				break;

			default:
				$table .= '_'.$this->element_type;
				break;
		}
		if (! DB::query_result("SELECT id FROM {%s} WHERE id=%d LIMIT 1", $table, $this->element_id))
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}
	}

	/**
	 * Проверяет попытку голосовать повторно
	 * 
	 * @return void
	 */
	private function check_log()
	{
		if (! $this->diafan->_session->id)
		{
			$this->result["errors"][0] = $this->diafan->_('Вы уже голосовали', false);
		}
		if ($this->diafan->configmodules('security', 'rating') == 3)
		{
			if (DB::query_result("SELECT id FROM {log_note} WHERE session_id='%s' AND element_id='%d' AND module_name='%s' AND element_type='%s' AND include_name='rating' LIMIT 1",
				$this->diafan->configmodules('only_user', 'rating') ? $this->diafan->_users->id : $this->diafan->_session->id,
				$this->element_id, $this->module_name, $this->element_type
			))
			{
				$this->result["errors"][0] = $this->diafan->_('Вы уже голосовали', false);
			}
			else
			{
				
				DB::query("INSERT INTO {log_note} (include_name, element_id, note, created, ip, session_id, module_name, element_type) "
					." VALUES ('rating', '%d', '%d', %d, '%s', '%s', '%s', '%s')",
					$this->element_id,
					$_POST["ajax_rating"],
					time(),
					getenv('REMOTE_ADDR'),
					$this->diafan->configmodules('only_user', 'rating') ? $this->diafan->_users->id : $this->diafan->_session->id,
					$this->module_name,
					$this->element_type
				);
			}
		}

		if ($this->diafan->configmodules('security', 'rating') == 4)
		{
			if (! empty($_SESSION["rating"][$this->element_id.$this->module_name.$this->element_type]))
			{
				$this->result["errors"][0] = $this->diafan->_('Вы уже голосовали', false);
			}
			else
			{
				$_SESSION["rating"][$this->element_id.$this->module_name.$this->element_type] = 1;
			}
		}
	}
}
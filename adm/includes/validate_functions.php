<?php
/**
 * @package    DIAFAN.CMS
 *
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
 * Validate_functions_admin
 *
 * Функции валидации полей
 */
class Validate_functions_admin extends Diafan
{
	/**
	 * Валидация поля "Период действия"
	 *
	 * @return void
	 */
	public function validate_variable_date_period()
	{
		if($this->diafan->is_variable("created"))
		{
			$created = $this->diafan->unixdate($_POST["created"]);
		}
		if(! empty($_POST["date_start"]))
		{
			$date_start = $this->diafan->unixdate($_POST["date_start"]);
			if($this->diafan->variable("date_period") == 'date')
			{
				$mes = Validate::date($_POST["date_start"]);
			}
			else
			{
				$mes = Validate::datetime($_POST["date_start"]);
			}
			if(! empty($mes))
			{
				$this->diafan->set_error("date_period", $mes);
				return;
			}
			if(isset($created) && $created > $date_start)
			{
				$this->diafan->set_error("date_period", "Дата начала показа должна быть больше даты создания.");
				return;
			}
		}
		if(! empty($_POST["date_finish"]))
		{
			$date_finish = $this->diafan->unixdate($_POST["date_finish"]);
			if($this->diafan->variable("date_period") == 'date')
			{
				$mes = Validate::date($_POST["date_finish"]);
			}
			else
			{
				$mes = Validate::datetime($_POST["date_finish"]);
			}
			if(! empty($mes))
			{
				$this->diafan->set_error("date_period", $mes);
				return;
			}
			if(isset($created) && $created > $date_finish)
			{
				$this->diafan->set_error("date_period", "Дата окончания показа должна быть больше даты создания.");
				return;
			}
		}
		if(isset($date_start) && isset($date_finish) && $date_start >= $date_finish)
		{
			$this->diafan->set_error("date_period", "Дата окончания показа должна быть больше даты начала показа.");
			return;
		}
	}

	/**
	 * Валидация поля "Период действия"
	 *
	 * @return void
	 */
	public function validate_variable_date_finish(){}

	/**
	 * Редактирование поля "Дополнительные параметры"
	 *
	 * @return void
	 */
	public function validate_variable_param()
	{
		$rows = DB::query_fetch_all("SELECT id, type FROM {".$this->diafan->table."_param} WHERE trash='0'");
		foreach ($rows as $row)
		{
			if($row["type"] == 'numtext')
			{
				$row["type"] = 'floattext';
			}
			$this->diafan->validate_variable("param".$row["id"], $row["type"]);
		}
	}

	/**
	 * Валидация поля "Значения поля конструктора"
	 * @return void
	 */
	public function validate_variable_param_select()
	{
		if($_POST["type"] == "attachments")
		{
			if(! empty($_POST["recognize_image"]))
			{
				if(empty($_POST["attach_big_width"]) || empty($_POST["attach_big_height"]) || empty($_POST["attach_big_quality"]))
				{
					$this->diafan->set_error("attach_big", "Задайте размеры изображения.");
				}
				if(empty($_POST["attach_medium_width"]) || empty($_POST["attach_medium_height"]) || empty($_POST["attach_medium_quality"]))
				{
					$this->diafan->set_error("attach_medium", "Задайте размеры изображения.");
				}
			}
			return;
		}
	}

	/**
	 * Валидация поля "Псевдоссылка"
	 *
	 * @return void
	 */
	public function validate_variable_rewrite()
	{
		$rewrite_id = 0;
		$redirect_id = 0;
		if(! $this->diafan->is_new)
		{
			$rewrite_id = DB::query_result("SELECT id FROM {rewrite} WHERE module_name='%s' AND element_type='%s' AND element_id=%d LIMIT 1", $this->diafan->_admin->module, $this->diafan->element_type(), $this->diafan->id);

			$redirect_id = DB::query_result("SELECT id FROM {redirect} WHERE module_name='%s' AND element_type='%s' AND element_id=%d LIMIT 1", $this->diafan->_admin->module, $this->diafan->element_type(), $this->diafan->id);
		}
		if(! empty($_POST["rewrite"]))
		{
			if($_POST["rewrite"] == 'm' || preg_match('/^m\//', $_POST["rewrite"]))
			{
				$this->diafan->set_error("rewrite", "Псевдоссылка не должна начинаться на m/, так как это префикс мобильной версии.");
			}
			elseif(DB::query_result("SELECT id FROM {rewrite} WHERE BINARY rewrite='%h' AND id<>%d AND trash='0' LIMIT 1", $_POST["rewrite"], $rewrite_id))
			{
				$this->diafan->set_error("rewrite", "Псевдоссылка уже есть в базе.");
			}
		}
		if(! empty($_POST["rewrite_redirect"]))
		{
			if(DB::query_result("SELECT id FROM {redirect} WHERE redirect='%s' AND id<>%d AND trash='0' LIMIT 1", $_POST["rewrite_redirect"], $redirect_id))
			{
				$this->diafan->set_error("redirect", "Редирект на этот URL уже есть в базе.");
			}
		}
	}

	/**
	 * Валидация поля "Динамические блоки"
	 *
	 * @return void
	 */
	public function validate_variable_dynamic_blocks()
	{
		if($this->diafan->config("category"))
		{
			$element_type = 'category';
		}
		else
		{
			$element_type = 'element';
		}

		$blocks = DB::query_fetch_all("SELECT b.id, b.type FROM {site_blocks} AS b"
			." INNER JOIN {site_blocks_site_rel} AS s ON s.element_id=b.id"
			." INNER JOIN {site_blocks_module} AS m ON m.block_id=b.id"
			." WHERE b.trash='0' AND b.dynamic='1'"
			." AND (s.site_id=0 OR s.site_id=%d)"
			." AND (m.module_name='%h' OR m.module_name='') AND (m.element_type='%h' OR m.element_type='')",
			$this->diafan->values("site_id"), $this->diafan->_admin->module, $element_type
		);
		foreach($blocks as $row)
		{
			$this->diafan->validate_variable("block".$row["id"], $row["type"]);
		}
	}

	/**
	 * Валидация поля "Priority"
	 *
	 * @return void
	 */
	public function validate_variable_priority()
	{
		if(! empty($_POST["priority"]) && (preg_match('/[^0-9\\,]+/', intval($_POST["priority"])) || $_POST["priority"] > 1 || intval($_POST["priority"]) < 0))
		{
			$this->diafan->set_error("priority", "Допустимый диапазон значений — от 0,0 до 1,0.");
		}
	}

	/**
	 * Валидация поля "Время редактирования"
	 *
	 * @return void
	 */
	public function validate_variable_timeedit()
	{
		if(! $this->diafan->is_new && ! empty($_POST["timeedit"]) && DB::query_result("SELECT timeedit FROM {".$this->diafan->table."} WHERE id=%d LIMIT 1", $this->diafan->id) > $_POST["timeedit"])
		{
			$this->diafan->set_error("timeedit", "Страница отредактирована другим администратором. Обновите форму, чтобы увидеть актуальную версию страницы.");
		}
	}
}

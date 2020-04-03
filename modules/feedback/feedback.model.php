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
 * Feedback_model
 */
class Feedback_model extends Model
{
	/**
	 * Генерирует данные для формы добавления сообщения
	 * 
	 * @param integer $site_id номер страницы
	 * @param boolean $insert_form форма выводится с помощью шаблонного тега
	 * @return array|boolean false
	 */
	public function form($site_id = 0, $insert_form = false)
	{
		if (! $insert_form)
		{
			$site_id = $this->diafan->_site->id;
		}
		else
		{
			if (! $site_id)
			{
				$site = DB::query_fetch_array(
						"SELECT s.id, s.[name] FROM {site} AS s"
						.($this->diafan->configmodules('where_access_element', 'site') ? " LEFT JOIN {access} AS a ON a.element_id=s.id AND a.module_name='site' AND a.element_type='element'" : "")
						." WHERE s.module_name='feedback' AND s.trash='0'"
						.($this->diafan->configmodules('where_access_element', 'site') ? " AND (s.access='0' OR s.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
						." LIMIT 1"
					);
				if(! $site)
				{
					return false;
				}
				$site_id = $site["id"];
				$this->result["name"] = $site["name"];
			}
			else
			{
				$site = DB::query_fetch_array("SELECT access, [name] FROM {site} WHERE id=%d", $site_id);
				if(! $site)
				{
					return false;
				}
				if($site["access"] && ! $this->access($site_id, "site"))
				{
					return false;
				}
				$this->result["name"] = $site["name"];
			}
		}
		if (! $site_id)
		{
			return false;
		}
		$this->result["site_id"] = $site_id;

		$fields = array('', 'captcha');
		$this->result['form_tag'] = 'feedback'.md5(serialize(array($site_id, $insert_form)));
		$this->result["rows"] = $this->get_params(array("module" => "feedback", "where" => "site_id=".$site_id));
		foreach ($this->result["rows"] as &$row)
		{
			$fields[] = 'p'.$row["id"];
			$row["text"] = $this->diafan->_tpl->htmleditor($row["text"]);
		}
		$this->form_errors($this->result, $this->result['form_tag'], $fields);

		$this->result["captcha"] = '';
		if ($this->diafan->_captcha->configmodules("feedback", $site_id))
		{
			$this->result["captcha"] = $this->diafan->_captcha->get($this->result['form_tag'], $this->result['error_captcha']);
		}
		$this->result["view"]   = 'form';
		return $this->result;
	}
}
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
 * Act_admin
 *
 * Публикация/скрытие элемента
 */
class Act_admin extends Diafan
{
	/**
	 * Публикует/скрывает элемент
	 *
	 * @return void
	 */
	public function act()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->diafan->redirect(getenv('HTTP_REFERER'));
		}

		//проверка прав пользователя на активацию/блокирование
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->diafan->redirect(getenv('HTTP_REFERER'));
		}

		//учитывается языковая версия, если это необходимо
		$field_name = 'act'.($this->diafan->variable_multilang("act") ? _LANG : '');

		$ids = array();
		$redirect = URL;

		if (! empty( $_POST["id"]))
		{
			if ($this->diafan->variable_list('plus'))
			{
				$ids = $this->diafan->get_children($_POST["id"], $this->diafan->table, array (), false);
				$parent_id = DB::query_result("SELECT parent_id FROM {".$this->diafan->table."} WHERE id=%d LIMIT 1", $_POST["id"]);
				if($parent_id && $this->diafan->_route->parent != $parent_id)
				{
					$redirect = str_replace('parent'.$this->diafan->_route->parent.'/', '', $redirect).'parent'.$parent_id.'/';
				}
			}
			$ids[] = intval($_POST["id"]);
		}
		else
		{
			if (! empty($_POST['ids']))
			{
				foreach ($_POST["ids"] as $id)
				{
					$id = intval($id);
					if (! in_array($id, $ids))
					{
						if ($this->diafan->variable_list('plus'))
						{
							$array = $this->diafan->get_children($id, $this->diafan->table, array (), false);
							$ids = array_merge($ids, $array);
						}
						$ids[] = $id;
					}
				}
			}
		}
		if (! empty($ids))
		{
			DB::query("UPDATE {".$this->diafan->table."} SET ".$field_name."='".( $_POST["action"] == "block" ? "1".( $this->diafan->is_variable("timeedit") ? "', timeedit='".time() : '' ) : '0' )."' WHERE id IN (".implode(',', $ids).")");
		}
		/*
		 // делаем категори неактивной - все основные элементы в ней тоже неактивны
		if ($this->diafan->config('category'))
		{
			DB::query("UPDATE {".str_replace('_category', '', $this->diafan->table)."} SET act".$lang."='".( $_POST["action"] == "block" ? "1".( $this->diafan->is_variable("timeedit") ? "', timeedit='".time() : '' ) : '0' )."' WHERE cat_id IN (".implode(',', $ids).")");
		}*/
		foreach ($this->diafan->installed_modules as $module)
		{
			if (Custom::exists('modules/'.$module.'/admin/'.$module.'.admin.inc.php'))
			{
				Custom::inc('modules/'.$module.'/admin/'.$module.'.admin.inc.php');
				$class = ucfirst($module).'_admin_inc';
				if (method_exists($class, 'act'))
				{
					$class_admin_act = new $class($this->diafan);
					$class_admin_act->act($this->diafan->table, $ids, ( $_POST["action"] == "block" ? 1 : 0 ));
				}
			}
		}
		$this->diafan->_cache->delete("", $this->diafan->_admin->module);
		$this->diafan->redirect($redirect);
	}
}
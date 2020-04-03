<?php
/**
 * Обработка POST-запросов
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

class Action_admin extends Theme_admin
{
	/**
	 * @var array полученный после обработки данных результат
	 */
	protected $result = array();

	/**
	 * @var object общие функции
	 */
	private $functions;

	/**
	 * @var array действия, для которых не нужно проверять хэш пользователя
	 */
	protected $hash_no_check = array();

	/**
	 * Подключает обработку POST-запросов
	 *
	 * @return void
	 */
	public function init()
	{
		$hash_no_check = false;

		// подключаем обработчик запросов модуля
		$macros = false;
		if (! empty($_POST["module"]))
		{
			$module = preg_replace('/[^a-z0-9_]+/', '', $_POST["module"]);
			$a = explode('_', $_POST["action"], 5);
			if(count($a) > 3 && $a[0] == 'macros' && $a[1] == $module)
			{
				$macros = true;
				if($a[2] == 'group')
				{
					$r = '';
					$m = $a[3].(! empty($a[4]) ? '_'.$a[4] : '');
				}
				elseif($a[3] == 'group')
				{
					$r = $a[2];
					$m = $a[4];
				}
			}
			if($macros && Custom::exists('modules/'.$module.'/admin/macros/'.$module.'.admin.'.($r ? $r.'.' : '').'group.'.$m.'.php'))
			{
				//проверка прав
				if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
				{
					return;
				}
				Custom::inc('modules/'.$module.'/admin/macros/'.$module.'.admin.'.($r ? $r.'.' : '').'group.'.$m.'.php');

				$class = ucfirst($module).'_admin_'.($r ? $r.'_' : '').'group_'.$m;
				$module_macros = new $class($this->diafan);
				$hash_no_check = (! empty($module_macros->hash_no_check) ? true : false);
			}
			elseif(Custom::exists('modules/'.$module.'/admin/'.$module.'.admin.action.php'))
			{
				Custom::inc('modules/'.$module.'/admin/'.$module.'.admin.action.php');

				$class = ucfirst($module).'_admin_action';
				$module_obj = new $class($this->diafan);
				$hash_no_check = in_array($_POST["action"], $module_obj->hash_no_check);
			}
		}
		elseif(preg_match('/^macros_group_([a-z0-9\-\_]+)$/', $_POST["action"], $a) && Custom::exists('adm/includes/macros/group/'.$a[1].'.php'))
		{
			//проверка прав
			if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
			{
				return;
			}
			Custom::inc('adm/includes/macros/group/'.$a[1].'.php');

			$class = 'Group_'.$a[1];
			$module_macros = new $class($this->diafan);
			$hash_no_check = (! empty($module_macros->hash_no_check) ? true : false);
		}

		if (! $hash_no_check && $this->diafan->_users->id)
		{
			if(! $this->diafan->_users->checked)
			{
				$this->result["errors"][0] = 'ERROR_HASH';
				$this->end();
			}
			else
			{
				$this->result["hash"] = $this->diafan->_users->get_hash();
			}
		}

		// подключаем обработчик запросов модуля

		if(isset($module_macros))
		{
			$module_macros->action();
			$this->result = array_merge($this->result, $module_macros->result);
		}
		elseif (! empty($_POST["module"]))
		{
			if(isset($module_obj))
			{
				$module_obj->init();
				$this->result = array_merge($this->result, $module_obj->result);
			}
		}
		else
		{
			Custom::inc('adm/includes/action_functions.php');
			$this->functions = new Action_functions_admin($this->diafan);

			switch ($_POST["action"])
			{
				case 'fast_save':
					$this->functions->fast_save();
					break;

				case 'sort':
					$this->functions->sort();
					break;

				case 'parent':
					$this->functions->parent_id();
					break;

				case 'element':
					$this->functions->group_cat_id();
					break;

				case 'element_site':
					$this->functions->group_site_id();
					break;

				case 'element_multi':
					$this->functions->group_cat_id_multi();
					break;

				case 'element_del':
					$this->functions->group_cat_id_del();
					break;

				case 'user_list':
					$this->functions->user_list();
					break;

				case 'cat_list':
					$this->functions->cat_list();
					break;

				case 'change_nastr':
					$this->functions->change_nastr();
					break;

				case 'settings':
					$this->functions->settings();
					break;
			}
			if($this->functions->result)
			{
				$this->result = array_merge($this->result, $this->functions->result);
			}
		}
		$this->end();
	}

	/**
	 * Отправляет ответ
	 *
	 * @return void
	 */
	protected function end()
	{
		if (! empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == 'xmlhttprequest' || ! empty($_POST["ajax"]))
		{
			if($this->result)
			{
				Custom::inc('plugins/json.php');
				echo to_json($this->result);
			}
		}
		else
		{

			if(! $this->result["redirect"])
			{
				$this->result["redirect"] = URL.$this->diafan->get_nav;
			}
			$this->diafan->redirect($this->result["redirect"]);
		}
		exit;
	}
}

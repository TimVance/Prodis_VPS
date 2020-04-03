<?php
/**
 * Обработка POST-запросов в административной части модуля
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if ( ! defined('DIAFAN'))
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
 * Addons_admin_action
 */
class Addons_admin_action extends Action_admin
{

	/**
	 * Вызывает обработку Ajax-запросов
	 *
	 * @return void
	 */
	public function init()
	{
		if (! empty($_POST["action"]))
		{
			switch($_POST["action"])
			{
				case 'check_update':
					$this->check_update();
					break;

				case 'delete_return':
					$this->delete_return();
					break;

				case 'group_action':
				case 'group_no_action':
				case 'group_addon_update':
					$this->group_option();
					break;

				case 'buy':
				case 'subscription':
					$this->buy();
					break;

				case 'no_subscription':
					$this->no_subscription();
					break;
			}
		}
	}

	/**
	 * Удаляет резервные копии обновленных дополнений
	 *
	 * @return void
	 */
	private function delete_return()
	{
		if(is_dir(ABSOLUTE_PATH.$this->diafan->_addons->return_path))
		{
			File::rm($this->diafan->_addons->return_path);
		}
		$message = $this->diafan->_('Резервные копии обновленных дополнений удалены.');
		$this->result["errors"]["message"] = $message;
		$this->result["redirect"] = URL.$this->diafan->get_nav;
	}

	/**
	 * Проверить обновления для дополнений
	 *
	 * @return void
	 */
	private function check_update()
	{
		$this->diafan->_addons->update(true);
		$count = DB::query_result("SELECT COUNT(*) FROM {%s} WHERE custom_timeedit>0 AND timeedit<>custom_timeedit", $this->diafan->table);
		$message = '';
		if($count)
		{
			$message = $this->diafan->_('Доступно обновление для дополнений: %d.', $count);
		}
		else
		{
			$message = $this->diafan->_('Доступных обновлений для дополнений пока нет. Попробуйте проверить чуть позже.');
		}
		$this->result["errors"]["message"] = $message;
		$this->result["redirect"] = URL.$this->diafan->get_nav;
	}

	/**
	 * Групповая операция "Установка дополнения", "Отключение дополнения" и др.
	 *
	 * @return void
	 */
	private function group_option()
	{
		if(! empty($_POST["ids"]))
		{
			$ids = array();
			foreach ($_POST["ids"] as $id)
			{
				$id = intval($id);
				if($id)
				{
					$ids[] = $id;
				}
			}
		}
		elseif(! empty($_POST["id"]))
		{
			$ids = array(intval($_POST["id"]));
		}
		if(! empty($ids))
		{
			switch ($_POST["action"])
			{
				case 'group_action':
					$this->group_action($ids);
					break;

				case 'group_no_action':
					$this->group_no_action($ids);
					break;

				case 'group_addon_update':
					$this->group_addon_update($ids);
					break;
			}
		}
	}

	/**
	 * Активация элемента
	 *
	 * @param array $ids идентификаторы дополнений
	 * @return void
	 */
	public function group_action($ids)
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->result["redirect"] = URL;
			return;
		}

		//проверка прав пользователя на активацию/блокирование
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->result["redirect"] = URL;
			return;
		}

		$question = true; // $question = ! empty($_POST["question"]) ? true : false;
		$result = $this->diafan->_addons->install($ids, $question);

		if($result === true)
		{
			$this->diafan->set_one_shot(
				'<div class="ok">'
				.(count($ids) > 1
					? $this->diafan->_('Дополнения установлены.')
					: $this->diafan->_('Дополнение установлено.'))
				.'</div>'
			);
			$this->result["redirect"] = URL.$this->diafan->get_nav;
			return;
		}
		$message = is_array($result) ? implode("<br>", $result) : (is_string($result) ? $result : '');
		$this->diafan->set_one_shot(
			'<div class="error">'
			.(count($ids) > 1
				? $this->diafan->_('Некоторые дополнения не установлены.')
				: $this->diafan->_('Дополнение не установлено.'))
			.($message ? "<br>".$message : '')
			.'</div>'
		);
		$this->result["redirect"] = URL.$this->diafan->get_nav;
	}

	/**
	 * Блокировка элемента
	 *
	 * @param array $ids идентификаторы дополнений
	 * @return void
	 */
	public function group_no_action($ids)
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->result["redirect"] = URL;
			return;
		}

		//проверка прав пользователя на активацию/блокирование
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->result["redirect"] = URL;
			return;
		}

		$question = true; // $question = ! empty($_POST["question"]) ? true : false;
		$this->diafan->_addons->uninstall($ids, $question);

		$this->result["redirect"] = URL.$this->diafan->get_nav;
	}

	/**
	 * Обновляет элемент
	 *
	 * @param array $ids идентификаторы дополнений
	 * @return void
	 */
	public function group_addon_update($ids)
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->result["redirect"] = URL;
			return;
		}

		//проверка прав пользователя на активацию/блокирование
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->result["redirect"] = URL;
			return;
		}

		$result = $this->diafan->_addons->reload($ids);

		if($result === true)
		{
			$this->diafan->set_one_shot(
				'<div class="ok">'
				.(count($ids) > 1
					? $this->diafan->_('Дополнения обновлены.')
					: $this->diafan->_('Дополнение обновлено.'))
				.'</div>'
			);
			$this->result["redirect"] = URL.$this->diafan->get_nav;
			return;
		}
		$message = is_array($result) ? implode("<br>", $result) : (is_string($result) ? $result : '');
		$this->diafan->set_one_shot(
			'<div class="error">'
			.(count($ids) > 1
				? $this->diafan->_('Некоторые дополнения не обновлены.')
				: $this->diafan->_('Дополнение не обновлено.'))
			.($message ? "<br>".$message : '')
			.'</div>'
		);
		$this->result["redirect"] = URL.$this->diafan->get_nav;
	}

	/**
	 * Покупка дополнения
	 *
	 * @return void
	 */
	private function buy()
	{
		if(! in_array($_POST["action"], array('buy', 'subscription')))
		{
			return;
		}
		$id = DB::query_result("SELECT addon_id FROM {addons} WHERE id=%d LIMIT 1", $this->diafan->filter($_POST, "int", "id"));
		$subscription = $_POST["action"] == 'subscription';
		$result = $this->diafan->_addons->buy($id, $subscription);
		if($result === true)
		{
			$this->diafan->set_one_shot(
				'<div class="ok">'
				.$this->diafan->_('Спасибо за Ваш заказ!')."<br>"
					.($_POST["action"] == 'subscription'
						? $this->diafan->_('Подписка на дополнение оформлена.')
						: $this->diafan->_('Покупка дополнения оформлена.'))
				.'</div>'
			);
			if($_POST["action"] == 'subscription')
			{
				$fields = ", IFNULL(c.id, 0) as `custom.id`, IFNULL(c.name, '') as `custom.name`";
				$join = " LEFT JOIN {custom} AS c ON c.id=e.custom_id";
				$row = DB::query_fetch_array(
					"SELECT e.*".$fields." FROM {addons} as e".$join." WHERE e.id=%d LIMIT 1",
					$this->diafan->filter($_POST, "int", "id")
				);
				if($row && ! empty($row["id"]) && empty($row["custom.id"]))
				{
					$question = true; // $question = ! empty($_POST["question"]) ? true : false;
					$rslt = $this->diafan->_addons->install($row["id"], $question);
					if($rslt === true)
					{
						$this->diafan->set_one_shot(
							'<div class="ok">'
							.$this->diafan->_('Дополнение установлено.')
							.'</div>'
						);
					}
					else
					{
						$message = is_array($rslt) ? implode("<br>", $rslt) : (is_string($rslt) ? $rslt : '');
						$this->diafan->set_one_shot(
							'<div class="error">'
							.$this->diafan->_('Дополнение не установлено.')
							.($message ? "<br>".$message : '')
							.'</div>'
						);
					}
				}
			}
			// Удаляет кэш модуля
			// $this->diafan->_cache->delete("", $this->diafan->_admin->module);
			$this->diafan->_cache->delete("", 'addons');
			return;
		}
		$message = is_array($result) ? implode("\n", $result) : (is_string($result) ? $result : '');
		$this->diafan->set_one_shot(
			'<div class="error">'
			.$this->diafan->_('Заказ отменен.').($message ? "\n".$message : '')
			.'</div>'
		);
		$this->result["redirect"] = $this->diafan->_route->current_admin_link();
	}

	/**
	 * Отмена подписки на дополнение
	 *
	 * @return void
	 */
	private function no_subscription()
	{
		if(! in_array($_POST["action"], array('no_subscription')))
		{
			return;
		}
		$id = DB::query_result("SELECT addon_id FROM {addons} WHERE id=%d LIMIT 1", $this->diafan->filter($_POST, "int", "id"));
		$result = $this->diafan->_addons->no_subscription($id);
		if($result === true)
		{
			$this->diafan->set_one_shot(
				'<div class="ok">'
				.$this->diafan->_('Подписка на дополнение отменена.')
				.'</div>'
			);
			// Удаляет кэш модуля
			// $this->diafan->_cache->delete("", $this->diafan->_admin->module);
			$this->diafan->_cache->delete("", 'addons');
			return;
		}
		$message = is_array($result) ? implode("<br>", $result) : (is_string($result) ? $result : '');
		$this->diafan->set_one_shot(
			'<div class="error">'
			.$this->diafan->_('Отмена подписки на дополнение не выполнена.').($message ? "<br>".$message : '')
			.'</div>'
		);
		$this->result["redirect"] = $this->diafan->_route->current_admin_link();
	}
}

<?php
/**
 * Обработка POST-запроса на добавление и редактирование тем
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

class Forum_action extends Action
{
	/**
	 * @var boolean пользователь является модератором
	 */
	public $moderator;

	/**
	 * Сохраняет новую категорию форума
	 * 
	 * @return void
	 */
	public function savenew()
	{
		if (empty($_POST["cat_id"]))
		{
			$this->result["errors"][0] = 'ERROR';
		}
		if (empty($_POST["name"]))
		{
			$this->result["errors"][0] = $this->diafan->_('Введите название темы для обсуждения', false);
		}
		if (empty($_POST["message"]))
		{
			$this->result["errors"][0] = $this->diafan->_('Вы не можете добавить пустое сообщение.', false);
			return;
		}
		if (! $this->diafan->_users->id && empty($_POST['user_name']))
		{
			$this->result["errors"]["user_name"] = $this->diafan->_('Пожалуйста, введите имя.', false);
			return;
		}

		if(! $this->diafan->_users->id) 
		{
			if ($this->diafan->_captcha->configmodules('forum'))
			{
				$this->check_captcha();
			}
		}
		if ($this->result())
			return;

		$save = DB::query("INSERT INTO {forum} (name, user_id, created, timeedit, act, cat_id) "
			." VALUES ('%h', %d, %d, %d, '%d', %d)", $_POST["name"], $this->diafan->_users->id,
			time(), time(), $this->model->check_premoderation('theme') && ! $this->moderator ? 0 : 1,
			$_POST["cat_id"]
		);

		$message = $this->diafan->_bbcode->replace($_POST["message"]);

		$result["id"] = DB::query("INSERT INTO {forum_messages} (created, name, forum_id, user_id, text, act) VALUES (%d, '%h', %d, %d, '%s', '%d')",
			time(),
			$this->diafan->_users->id ? '' : $_POST['user_name'],
			$save,
			$this->diafan->_users->id,
			$message,
			$this->model->check_premoderation('message', "forum") && ! $this->moderator ? 0 : 1
		);
		if ($this->diafan->configmodules("attachments", "forum"))
		{
			$config = array('site_id' => $this->diafan->_site->id, 'type' => 'configmodules');
			try
			{
				$this->diafan->_attachments->save($result["id"], 'forum', $config);
			}
			catch(Exception $e)
			{
				DB::query("DELETE FROM {forum} WHERE id=%d", $save);
				DB::query("DELETE FROM {forum_messages} WHERE id=%d", $result["id"]);
				Dev::$exception_field = 'attachments';
				Dev::$exception_result = $this->result;
				throw new Exception($e->getMessage());
			}
		}
		$this->save_news($result["id"]);

		// ЧПУ
		if(ROUTE_AUTO_MODULE)
		{
			$this->diafan->_route->save('', $_POST["name"], $save, 'forum', 'element', $this->diafan->_site->id, $_POST["cat_id"]);
			if(in_array("map", $this->diafan->installed_modules))
			{
				// если тема сразу активируется на сайте, сохраняет ссылку на карте сайта 
				if(! $this->model->check_premoderation('theme') || $this->moderator)
				{
					$forum_row = array(
						"module_name" => 'forum',
						"id"          => $save,
						"site_id"     => $this->site_id,
					);
					$this->diafan->_map->index_element($forum_row);
				}
			}
		}

		if ($this->model->check_premoderation('theme') && ! $this->moderator)
		{
			$this->result["result"] = 'success';
			$this->result["errors"][0] = $this->diafan->_('Тема успешно добавлена.', false).' '.$this->diafan->_('Тема будет активирована на сайте после проверки модератором.', false);
		}
		else
		{
			$this->diafan->_cache->delete('', 'cache_extreme');
			$this->result["redirect"] = BASE_PATH_HREF.$this->diafan->_route->link($this->diafan->_site->id, $save, "forum");
		}
	}

	/**
	 * Сохраняет редактируемую категорию форума
	 * 
	 * @return void
	 */
	public function save()
	{
		$row = DB::query_fetch_array("SELECT * FROM {forum} WHERE id=%d AND trash='0' LIMIT 1", $_POST["id"]);

		//редактирует только автор и модератор
		if (! $row || ! $this->moderator && ($row["user_id"] != $this->diafan->_users->id || ! $row["act"]))
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}

		DB::query("UPDATE {forum} SET name='%h', date_update='%d', timeedit='%d', user_update='%d', act='%d' WHERE id=%d",
			$_POST["name"], time(), time(),
			//модератор
			$row["user_id"] != $this->diafan->_users->id ? $this->diafan->_users->id : 0,
			$this->model->check_premoderation('theme') && ! $this->moderator ? 0 : 1,
			$_POST["id"]
		);

		if ($this->model->check_premoderation('theme') && ! $this->moderator)
		{
			if($row["act"])
			{
				$this->diafan->_cache->delete('', 'cache_extreme');
			}
			$this->result["errors"][0] = $this->diafan->_('Тема успешно изменена.', false).' '.$this->diafan->_('Тема будет активирована на сайте после проверки модератором.', false);
		}
		else
		{
			$this->result["redirect"] = BASE_PATH_HREF.$this->diafan->_route->link($this->diafan->_site->id, $_POST["id"], "forum");
		}
	}

	/**
	 * Удаляет категорию форума
	 * 
	 * @return void
	 */
	public function delete()
	{
		$row = DB::query_fetch_array("SELECT * FROM {forum}  WHERE id=%d AND trash='0' LIMIT 1", $_POST["id"]);

		//удаляет только автор и модератор
		if (! $row || ($row["user_id"] != $this->diafan->_users->id && ! $this->moderator))
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}

		//удаляет автор, если в теме нет сообщений от других пользователей
		if (! $this->moderator
		   && DB::query_result("SELECT COUNT(*) FROM {forum_messages} WHERE forum_id=%d AND user_id<>%d", $this->diafan->_route->show, $this->diafan->_users->id))
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}

		$rs = DB::query_fetch_all("SELECT id FROM {forum_messages} WHERE forum_id=%d", $_POST["id"]);
		foreach ($rs as $r)
		{
			$this->diafan->_attachments->delete($r["id"], "forum");
			DB::query("DELETE FROM {forum_show} WHERE element_id=%d", $r["id"]);
		}
		DB::query("DELETE FROM {forum_messages} WHERE forum_id=%d", $_POST["id"]);

		DB::query("DELETE FROM {forum} WHERE id=%d", $_POST["id"]);
		if(in_array("map", $this->diafan->installed_modules))
		{
			$this->diafan->_map->delete($_POST["id"], 'forum');
		}
		if($row["act"])
		{
			$this->diafan->_cache->delete('', 'cache_extreme');
		}
		$this->result["data"] = array('#forum_'.$row["id"] => false);
	}

	/**
	 * Публикует/блокирует категорию форума
	 *
	 * @param boolean $block категория блокируется
	 * @return void
	 */
	public function block($block)
	{
		$row = DB::query_fetch_array("SELECT * FROM {forum}  WHERE id=%d AND trash='0' LIMIT 1", $_POST["id"]);

		//блокировать/разблокировать может только модератор
		if (! $row || ! $this->moderator)
		{
			Custom::inc('includes/404.php');
		}
		DB::query("UPDATE {forum} SET act='%d' WHERE id=%d", $block ? 0 : 1, $_POST["id"]);
		$row["act"] = $block ? 0 : 1;

		$this->diafan->_cache->delete('', 'cache_extreme');

		$this->show_forum($row);
	}

	/**
	 * Закрывает категорию форума
	 * 
	 * @param boolean $close категория закрывается
	 * @return void
	 */
	public function close($close)
	{
		$row = DB::query_fetch_array("SELECT * FROM {forum}  WHERE id=%d AND trash='0' LIMIT 1", $_POST["id"]);

		//закрывать тему может только модератор
		if (! $row || ! $this->moderator)
		{
			Custom::inc('includes/404.php');
		}

		DB::query("UPDATE {forum} SET close='%d' WHERE id=%d", $close ? 1 : 0, $_POST["id"]);
		$row["close"] = $close ? 1 : 0;

		$this->diafan->_cache->delete('', 'cache_extreme');

		$this->show_forum($row);
	}

	/**
	 * Закрепляет/открепляет категорию форума
	 * 
	 * @param boolean $prior категория закрепляется
	 * @return void
	 */
	public function prior($prior)
	{
		$row = DB::query_fetch_array("SELECT id FROM {forum}  WHERE id=%d AND trash='0' LIMIT 1", $_POST["id"]);

		//закреплять тему может только модератор
		if (! $row || ! $this->moderator)
		{
			Custom::inc('includes/404.php');
		}

		DB::query("UPDATE {forum} SET prior='%d' WHERE id=%d", $prior ? 1 : 0, $_POST["id"]);

		$this->diafan->_cache->delete('', 'cache_extreme');

		$this->result["redirect"] = BASE_PATH_HREF.$this->diafan->_route->link($this->diafan->_site->id, $this->diafan->_route->cat, "forum", 'cat').($this->diafan->_route->page > 1 ? 'page'.$this->diafan->_route->page.'/' : '');
	}

	/**
	 * Формирует возвращаемую строку о категории
	 *
	 * @return void
	 */
	private function show_forum($row = array())
	{
		if (! $row)
		{
			$row = DB::query_fetch_array("SELECT * FROM {forum}  WHERE id=%d AND trash='0' AND act='1' LIMIT 1", $_POST["id"]);
		}
		$this->model->moderator = $this->moderator;
		$this->model->list_id($row);
		$row["hash"] = $this->diafan->_users->get_hash();
		$this->result["data"] = array('#forum_'.$row["id"] => $this->diafan->_tpl->get('list_id', 'forum', $row));
	}

	/**
	 * Добавляет новое сообщение
	 *
	 * @return void
	 */
	public function upload_message()
	{
		$element_id = $this->diafan->_route->show;

		$parent_id = $this->diafan->filter($_POST, "int", "parent_id");
		if ($parent_id && ! DB::query_result("SELECT id FROM {forum_messages} WHERE id=%d AND trash='0' AND act='1' LIMIT 1", $parent_id))
		{
			$this->result["errors"][0] = 'ERROR';
			return;	
		}

		if ($this->diafan->_captcha->configmodules('forum'))
		{
			$this->check_captcha();
		}

		if ($this->result())
			return;

		$cat = DB::query_fetch_array("SELECT id, close FROM {forum} WHERE id=%d AND act='1' AND trash='0' LIMIT 1", $this->diafan->_route->show);
		if (! $cat)
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}
		//тема закрыта
		if ($cat["close"])
		{
			$this->result["errors"][0] = $this->diafan->_('Тема закрыта.', false);
			return;
		}
		if (! $_POST["message"])
		{
			$this->result["errors"][0] = $this->diafan->_('Вы не можете добавить пустое сообщение.', false);
			return;
		}
		//максимальное количество уровней вложенности
		if($parent_id)
		{
			$parents = $this->diafan->get_parents($parent_id, "forum_messages");
			$level = count($parents) + 2;
		}
		else
		{
			$parents = array();
			$level = 1;
		}
		if ($this->diafan->configmodules("count_level", "forum") && $level > $this->diafan->configmodules("count_level", "forum"))
		{
			$this->result["errors"][0] = 'ERROR';
			return;	
		}

		$message = $this->diafan->_bbcode->replace($_POST["message"]);
		if (empty($_POST["message"]))
		{
			$this->result["errors"][0] = $this->diafan->_('Вы не можете добавить пустое сообщение.', false);
			return;
		}
		if (! $this->diafan->_users->id && empty($_POST['name']))
		{
			$this->result["errors"]["name"] = $this->diafan->_('Пожалуйста, введите имя.', false);
			return;
		}

		$result["id"] = DB::query("INSERT INTO {forum_messages} (created, name, forum_id, user_id, text, parent_id, act) VALUES (%d, '%h', %d, %d, '%s', %d, '%d')",
			time(),
			$this->diafan->_users->id ? '' : $_POST['name'],
			$element_id,
			$this->diafan->_users->id,
			$message,
			$parent_id,
			$this->model->check_premoderation('message') && ! $this->moderator ? 0 : 1
		);
		if ($parent_id)
		{
			$parents[] = $parent_id;
			foreach ($parents as $p_id)
			{
				DB::query("INSERT INTO {forum_messages_parents} (element_id, parent_id) VALUES (%d, %d)", $result["id"], $p_id);
				DB::query("UPDATE {forum_messages} SET count_children=count_children+1 WHERE id=%d", $p_id);
			}
		}
		if ($this->diafan->configmodules("attachments", "forum"))
		{
			$config = array('site_id' => $this->diafan->_site->id, 'type' => 'configmodules');
			try
			{
				$this->diafan->_attachments->save($result["id"], 'forum', $config);
			}
			catch(Exception $e)
			{
				DB::query("DELETE FROM {forum_messages} WHERE id=%d", $result["id"]);
				Dev::$exception_field = 'attachments';
				Dev::$exception_result = $this->result;
				throw new Exception($e->getMessage());
			}
		}
		$this->save_news($result["id"]);
		DB::query("UPDATE {forum} SET timeedit=%d WHERE id=%d", time(), $element_id);
		
		if($this->model->check_premoderation('message') && ! $this->moderator)
		{
			$this->result["errors"][0] = $this->diafan->_('Сообщение успешно добавлено и будет активировано на сайте после проверки модератором.', false);
			$this->result["result"] = 'success';
			return;
		}

		$result['created']            = $this->diafan->_('добавлено', false);
		$result["user"]               = $this->diafan->_users->id ? $this->model->get_author($this->diafan->_users->id) : strip_tags($_POST['name']);
		$result["text"]               = $message;
		$result["access_edit_delete"] = $this->diafan->_users->id ? true : false;
		$result["access_block"]       = $this->moderator ? true : false;
		$result["children"]           = array();
		$result["show"]               = 0;
		$result["act"]                = $this->model->check_premoderation('message') && ! $this->moderator ? 0 : 1;

		$result["attachments"] = array();
		if ($this->diafan->configmodules("attachments"))
		{
			$result["attachments"]["rows"] = $this->diafan->_attachments->get($result["id"], 'forum');
			$result["attachments"]["access"] = $this->diafan->_users->id ? true : false;
			$result["attachments"]["use_animation"] = $this->diafan->configmodules("use_animation", "forum");
		}
		if(! $this->diafan->configmodules("count_level", "forum") || $level < $this->diafan->configmodules("count_level", "forum"))
		{
			$result["form"] = $this->model->get_form($result["id"]);
			$result["hash"] = $result["form"]["hash"];
		}
		else
		{
			$result["hash"] = $this->result["hash"];
			$result["form"] = false;
		}

		$this->result["date"] = array(
			'.forum_message'.$parent_id.'_block_form' => false,
		);
		if(! $this->model->check_premoderation('message') || $this->moderator)
		{
			$this->diafan->_cache->delete('', 'cache_extreme');
		}
		$this->result["add"]    = $this->diafan->_tpl->get('id_messages', 'forum', $result);
		$this->result["result"] = 'success';
	}

	/**
	 * Редактирует сообщение
	 *
	 * @return void
	 */
	public function edit_message()
	{
		//тема закрыта
		if (DB::query_result("SELECT close FROM {forum} WHERE id=%d LIMIT 1", $this->diafan->_route->show))
		{
			$this->result["errors"][0] = $this->diafan->_('Тема закрыта', false);
			return;
		}

		$element_id = $this->diafan->_route->show;
		if (! $this->diafan->_users->id)
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}

		$row = DB::query_fetch_array("SELECT user_id, text, name FROM {forum_messages} WHERE forum_id=%d AND id=%d LIMIT 1", $element_id, $_POST["id"]);
		if (! $row)
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}
		if ($this->diafan->_users->id != $row["user_id"] && ! $this->moderator)
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}

		$edit_id = $this->diafan->filter($_POST, "int", "id");

		$result["id"]          = $edit_id;
		$result["attachments"] = array();
		if ($this->diafan->configmodules("attachments"))
		{
			$result["attachments"]["rows"] = $this->diafan->_attachments->get($result["id"], 'forum');
			$result["attachments"]["access"] = true;
			$result["attachments"]["use_animation"] = $this->diafan->configmodules("use_animation", "forum");
			$result["attachments"]["max_count_attachments"] = $this->diafan->configmodules("max_count_attachments", "forum");
			$result["attachments"]["attachment_extensions"] = $this->diafan->configmodules("attachment_extensions", "forum");
		}
		$result["name"]          = $row["name"];
		$result["field_name"]    = $row["user_id"] ? false : true;
		$result["text"]          = $this->diafan->_bbcode->add($row["text"]);
		$result["premoderation"] = $this->model->check_premoderation('message') && ! $this->moderator;
		$result["access_add"]    = true;
		$result["hash"]          = $this->result["hash"];
		$result["form_tag"]      = 'forum_message_edit'.$edit_id;

		$this->result["data"]    = array(".forum_message".$edit_id => $this->diafan->_tpl->get('edit_message', 'forum', $result));
	}

	/**
	 * Сохраняет редактируемое сообщение
	 *
	 * @return void
	 */
	public function save_message()
	{
		//тема закрыта
		if (DB::query_result("SELECT close FROM {forum} WHERE id=%d LIMIT 1", $this->diafan->_route->show))
		{
			$this->result["errors"][0] = $this->diafan->_('Тема закрыта', false);
			return;
		}

		if (! $this->diafan->_users->id)
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}
		$element_id = $this->diafan->_route->show;

		$result = DB::query_fetch_array("SELECT * FROM {forum_messages} WHERE forum_id=%d AND id=%d LIMIT 1",
						 $element_id, $_POST["save_id"]);
		if (! $result)
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}
		if ($this->diafan->_users->id != $result["user_id"] && ! $this->moderator)
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}
		if (! $result["user_id"] && empty($_POST['name']))
		{
			$this->result["errors"]["name"] = $this->diafan->_('Пожалуйста, введите имя', false);
			return;
		}

		$edit = 'no';
		if ($this->diafan->configmodules("attachments", "forum"))
		{
			$config = array('site_id' => $this->diafan->_site->id, 'type' => 'configmodules');
			try
			{
				if($this->diafan->_attachments->save($result["id"], 'forum', $config))
				{
					$edit = 'yes';
				}
			}
			catch(Exception $e)
			{
				Dev::$exception_field = 'attachments';
				Dev::$exception_result = $this->result;
				throw new Exception($e->getMessage());
			}
		}

		if (! $_POST["message"])
		{
			$this->result["errors"][0] = $this->diafan->_('Вы не можете добавить пустое сообщение', false);
			return;
		}
		$message = $this->diafan->_bbcode->replace($_POST["message"]);
		if(empty($_POST["name"]))
		{
			$_POST["name"] = '';
		}

		if ($edit == 'yes' || $result["text"] != $message || $result["name"] != $_POST["name"])
		{
			DB::query("UPDATE {forum_messages} SET text='%s', name='%h', date_update=%d, user_update=%d, act='%d' WHERE id=%d",
				$message,
				$_POST["name"],
				time(),
				//модератор,
				$result["user_id"] != $this->diafan->_users->id ? $this->diafan->_users->id : '',
				$this->model->check_premoderation('message') && ! $this->moderator ? 0 : 1,
				$_POST["save_id"]
			);
			DB::query("UPDATE {forum} SET timeedit=%d WHERE id=%d", time(), $result["forum_id"]);
			$this->save_news($result["id"]);
		}
		if($result["act"] && (! $this->model->check_premoderation('message') || $this->moderator))
		{
			$this->diafan->_cache->delete('', 'cache_extreme');
		}

		$result["text"]               = $message;
		$result["access_edit_delete"] = true;
		$result["access_block"]       = $this->moderator ? 1 : 0;
		$result["act"]                = $this->model->check_premoderation('message') && ! $this->moderator ? 0 : 1;
		$result["created"]            = $this->model->format_date($result["created"]);
		if ($result["date_update"])
		{
			$result["date_update"] = $this->model->format_date($result["date_update"]);
		}
		if($result["user_update"] == $result["user_id"] || ! $result["user_update"])
		{
			$result["user_update"] = 0;
		}
		else
		{
			$result["user_update"] = $this->model->get_author($result["user_update"]);
		}
		$result["user"]         = $result["user_id"] ? $this->model->get_author($result["user_id"]) : $result["name"];
		$result["show"]            = 0;

		$result["attachments"] = array();
		if ($this->diafan->configmodules("attachments"))
		{
			$result["attachments"]["rows"] = $this->diafan->_attachments->get($result["id"], 'forum');
			$result["attachments"]["access"] = true;
			$result["attachments"]["use_animation"] = $this->diafan->configmodules("use_animation", "forum");
		}
		$result["hash"] = $this->result["hash"];

		$this->result["data"] = array(".forum_message".$_POST["save_id"] => $this->diafan->_tpl->get('id_message', 'forum', $result));
	}

	/**
	 * Блокирует|разблокирует сообщение
	 *
	 * @return void
	 */
	public function block_message()
	{
		if (empty($_POST["id"]))
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}
		if (! $this->moderator)
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}

		$result = DB::query_fetch_array("SELECT * FROM {forum_messages} WHERE id=%d LIMIT 1", $_POST["id"]);
		if (! $result)
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}
		DB::query("UPDATE {forum_messages} SET act='%d' WHERE id=%d", $result["act"] ? 0 : 1, $_POST["id"]);

		if ($result["act"])
		{
			DB::query("DELETE FROM {forum_show} WHERE element_id='%d'", $_POST["id"]);
		}

		$result["access_edit_delete"] = true;
		$result["access_block"]       = true;
		$result["act"]                = $result["act"] ? false : true;
		$result["created"]            = $this->model->format_date($result["created"]);
		if ($result["date_update"])
		{
			$result["date_update"] = $this->model->format_date($result["date_update"]);
		}
		if($result["user_update"] == $result["user_id"] || ! $result["user_update"])
		{
			$result["user_update"] = 0;
		}
		else
		{
			$result["user_update"] = $this->model->get_author($result["user_update"]);
		}
		$result["user"] = $result["user_id"] ? $this->model->get_author($result["user_id"]) : $result["name"];
		$result["show"] = 0;

		$result["attachments"] = array();
		if ($this->diafan->configmodules("attachments"))
		{
			$result["attachments"]["rows"] = $this->diafan->_attachments->get($result["id"], 'forum');
			$result["attachments"]["access"] = true;
			$result["attachments"]["use_animation"] = $this->diafan->configmodules("use_animation", "forum");
		}
		$result["hash"] = $this->result["hash"];

		$this->diafan->_cache->delete('', 'cache_extreme');

		$this->result["data"] = array(".forum_message".$_POST["id"] => $this->diafan->_tpl->get('id_message', 'forum', $result));
	}

	/**
	 * Удяет сообщение
	 *
	 * @return void
	 */
	public function delete_message()
	{
		if (empty($_POST["id"]))
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}
		if (! $this->diafan->_users->id)
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}

		$result = DB::query_fetch_array("SELECT * FROM {forum_messages} WHERE id=%d LIMIT 1", $_POST["id"]);

		if (DB::query_result("SELECT id FROM {forum_messages} WHERE parent_id=%d AND trash<>'1' LIMIT 1", $_POST["id"]))
		{
			$this->result["errors"][0] = $this->diafan->_('Удалить сообщение нельзя, потому что на него кто-то ответил.', false);
			return;
		}

		//чужой комментарий
		if (! $this->moderator && $this->diafan->_users->id != $result["user_id"])
		{
			$this->result["errors"][0] = $this->diafan->_('Зачем Вы пытаетесь удалить чужое сообщение?', false);
			return;
		}

		$this->diafan->_attachments->delete($_POST["id"], "forum");
		DB::query("DELETE FROM {forum_messages} WHERE id=%d", $_POST["id"]);
		if ($result["parent_id"])
		{
			DB::query("DELETE FROM {forum_messages_parents} WHERE element_id=%d", $_POST["id"]);
			$parents = $this->diafan->get_parents($result["parent_id"], "forum_messages");
			$parents[] = $result["parent_id"];
			DB::query("UPDATE {forum_messages} SET count_children=count_children-1 WHERE id IN (%s)", implode(',', $parents));
		}
		$this->result["hash"] = $this->diafan->_users->get_hash();
		$this->result["data"] = array("#forum_message".$_POST["id"] => false);

		if($result["act"])
		{
			$this->diafan->_cache->delete('', 'cache_extreme');
		}

		DB::query("DELETE FROM {forum_show} WHERE element_id=%d", $_POST["id"]);
	}

	/**
	 * Удаляет прикрепленный файл
	 * 
	 * @return void
	 */
	public function delete_attachment()
	{
		if (! $this->diafan->_users->id || empty($_POST["del_id"]))
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}
		$row = DB::query_fetch_array("SELECT element_id, id FROM {attachments} WHERE module_name='forum' AND id=%d LIMIT 1", $_POST["del_id"]);
		if (! $row)
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}
		if (! $this->moderator && DB::query_result("SELECT `user_id` FROM {forum_messages} WHERE id=%d LIMIT 1", $row["element_id"]) != $this->diafan->_users->id)
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}
		$this->diafan->_attachments->delete($row["element_id"], "forum", $_POST["del_id"]);

		$this->diafan->_cache->delete('', 'cache_extreme');

		$this->result["target"] = "#attachment".$row["id"];
		$this->result["result"] = "success";
	}

	/**
	 * Сохраняет изменения в новостях
	 *
	 * @param integer $save номер сообщения
	 * @return void
	 */
	private function save_news($save)
	{
		$count_days = $this->diafan->configmodules("news_count_days");

		DB::query("DELETE FROM {forum_show} WHERE created<%d", time() - 86400 * $count_days);
		DB::query("DELETE FROM {forum_show} WHERE element_id=%d", $save);
		$rows = DB::query_fetch_all("SELECT id FROM {users} WHERE act='1' AND trash='0' AND id<>%d", $this->diafan->_users->id);
		foreach ($rows as $row)
		{
			DB::query("INSERT INTO {forum_show} (element_id, user_id, created) VALUES (%d, %d, %d)", $save, $row["id"], time());
		}
	}

	/**
	 * Выводит дополнительный список к текущему списку категорий форума, прикрепленных к элементу
	 * 
	 * @return void
	 */
	public function list_category()
	{
		$attributes = array();
		if(! empty($_POST))
		{
			$attributes = $_POST;
		}
		
		$this->get_attributes($attributes, 'uid');
		$uid = ! empty($attributes["uid"]) ? $this->diafan->filter($attributes, "string", "uid") : false;
		
		$paginator = false;
		$this->model->list_category();
		$view = $this->model->result["view"];
		if(! empty($this->model->result["view_rows"]))
		{
			$view = $this->model->result["view_rows"];
		}
		if($uid !== false && isset($this->model->result["paginator"]))
		{
			$paginator = $this->model->result["paginator"];
			unset($this->model->result["paginator"]);
		}
		$this->model->result['ajax'] = true;
		$result = $this->diafan->_tpl->get($view, 'forum', $this->model->result);
		$this->result['set_location'] = true;
		
		$target = $uid !== false ? "tr[uid='".$uid."']" : "form";
		
		$this->result['data'] = array(
			$target => $result,
		);
		if($paginator !== false && $uid !== false)
		{
			$this->result['data'][".paginator[uid='".$uid."']"] = $paginator;
		}
		
		$this->result['replace'] = true;
	}
}
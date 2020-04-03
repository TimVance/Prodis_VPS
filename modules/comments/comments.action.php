<?php
/**
 * Обработка запроса при добавления комментария
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

class Comments_action extends Action
{
	/*
	 * Скрыть форму ответа на добавленный комментарий
	 */
	private $hide_form;

	/**
	 * Обрабатывает полученные данные из формы
	 *
	 * @return void
	 */
	public function add()
	{
		if ($this->diafan->configmodules('only_user', 'comments'))
		{
			$this->check_user();

			if ($this->result())
				return;
		}
		$id = $this->diafan->filter($_POST, "int", "element_id");
		if(! $id)
		{
			$id = $this->diafan->_site->module
				? ($this->diafan->_route->show ? $this->diafan->_route->show : $this->diafan->_route->cat)
				: $this->diafan->_site->id;
		}

		$module_name = $this->diafan->filter($_POST, "string", "module_name");
		if(! $module_name)
		{
			$module_name = $this->diafan->_site->module ? $this->diafan->_site->module : 'site';
		}

		$element_type = $this->diafan->filter($_POST, "string", "element_type");
		if(! $element_type)
		{
			$element_type = $this->diafan->_route->cat ? "cat" : 'element';
		}

		$this->check_parent_id($id, $module_name, $element_type);

		if ($this->result())
			return;

		$parent_id = $this->diafan->filter($_POST, "int", "parent_id");

		$where_form = "(module_name='".$module_name."' OR module_name='') AND show_in_"
					  .($this->diafan->_users->id ? "form_auth" : "form_no_auth")."='1'";
		$params = $this->inc->get_params(array("module" => "comments", "where" => $where_form));

		if ($this->diafan->_captcha->configmodules('comments'))
		{
			$this->check_captcha();
		}
		$this->check_fields();
		$this->empty_required_field(array("params" => $params));

		if ($this->result())
			return;

		if ($this->error_insert($id, $module_name, $element_type))
			return;

		if($this->diafan->configmodules('use_bbcode', 'comments'))
		{
			$comment = $this->diafan->_bbcode->replace($_POST["comment"]);
		}
		else
		{
			$comment = nl2br(htmlspecialchars($_POST["comment"]));
		}
		$save = DB::query(
			"INSERT INTO {comments} (created, module_name, element_id, element_type, user_id, text, act, parent_id)"
			." VALUES (%d, '%h', %d, '%s', %d, '%s', '%d', %d)",
			time(), $module_name, $id, $element_type, $this->diafan->_users->id,
			$comment,
			$this->diafan->configmodules('security_moderation', 'comments') && ! $this->diafan->_users->roles('edit', 'comments') ? 0 : 1,
			$parent_id
		);
		if(! $this->diafan->configmodules('security_moderation', 'comments') || $this->diafan->_users->roles('edit', 'comments'))
		{
			$this->diafan->_cache->delete($_GET, 'cache_extreme');
		}

		if(! empty($_POST["tmpcode"]))
		{
			DB::query("UPDATE {images} SET element_id=%d, tmpcode='' WHERE module_name='comments' AND element_id=0 AND tmpcode='%s'", $save, $_POST["tmpcode"]);
		}
		if ($parent_id)
		{
			$parents = $this->diafan->get_parents($parent_id, "comments");
			$parents[] = $parent_id;
			foreach ($parents as $p_id)
			{
				DB::query("INSERT INTO {comments_parents} (element_id, parent_id) VALUES (%d, %d)", $save, $p_id);
				DB::query("UPDATE {comments} SET count_children=count_children+1 WHERE id=%d", $p_id);
			}
		}

		$this->insert_values(array("id" => $save, "table" => "comments", "params" => $params));

		if ($this->result())
			return;

		$this->message_admin_param = ($this->message_admin_param ? $this->message_admin_param.'<br>' : '').$comment;
		$this->send_mail();
		$this->send_sms();
		
		// подписка на новые комментарии
		if($this->diafan->configmodules('use_mail', 'comments'))
		{
			$link = BASE_PATH_HREF.$this->diafan->_route->current_link();
			$actlink = $link.'?module=comments&action=unsubscribe&mail=';
			$subject = str_replace(
				array('%title', '%url'), array(TITLE, BASE_URL), $this->diafan->configmodules('subject', 'comments')
			);
	
			$message = str_replace(
				array(
					'%title',
					'%url',
					'%link',
					'%message'
				), array(
					TITLE,
					BASE_URL,
					$link,
					$comment
				), $this->diafan->configmodules('message', 'comments')
			);
			
			$from = $this->diafan->configmodules("emailconf", 'comments') && $this->diafan->configmodules("email", 'comments') ? $this->diafan->configmodules("email", 'comments') : EMAIL_CONFIG;

			$rows = DB::query_fetch_all("SELECT DISTINCT(mail) FROM {comments_mail} WHERE element_id=%d AND module_name='%s' AND element_type='%s'", $id, $module_name, $element_type);
			foreach ($rows as $row)
			{
				if(! empty($_POST["mail"]) && $_POST["mail"] == $row["mail"])
				{
					continue;
				}
				$mes = str_replace('%actlink', $actlink.$row["mail"].'#comment0', $message);
				$this->diafan->_postman->message_add_mail(
					$row["mail"],
					$subject,
					$mes,
					$from
				);
			}
			if(! empty($_POST['mail']))
			{
				if(! DB::query_result("SELECT id FROM {comments_mail} WHERE mail='%h' AND module_name='%h' AND element_id=%d AND element_type='%s'", $_POST["mail"], $module_name, $id, $element_type))
				{
					DB::query("INSERT INTO {comments_mail} (mail, module_name, element_id, element_type) VALUES ('%h', '%h', %d, '%s')", $_POST["mail"], $module_name, $id, $element_type);
				}
			}
		}

		//модерация сообщений
		if ($this->diafan->configmodules('security_moderation', 'comments') && ! $this->diafan->_users->roles('edit', 'comments'))
		{
			$mes = $this->diafan->configmodules('add_message', 'comments');
			$this->result["errors"][0] = $mes ? $mes : ' ';
			$this->result["result"] = 'success';
			return;
		}

		$where_form = "(module_name='".$module_name."' OR module_name='') AND show_in_"
					  .($this->diafan->_users->id ? "form_auth" : "form_no_auth")."='1'";
		$where_list = "(module_name='".$module_name."' OR module_name='') AND show_in_list='1'";

		$params_form = $this->inc->get_params(array("module" => "comments", "where" => $where_form));
		$params_list = $this->inc->get_params(array("module" => "comments", "where" => $where_list));

		$row = DB::query_fetch_array("SELECT * FROM {comments} WHERE id=%d", $save);
		$this->inc->element($row, $params_list, $id, $module_name, $element_type, $params_form, $this->hide_form);

		$this->result["add"] = $this->diafan->_tpl->get('id', 'comments', $row);

		$this->result["data"] = array(
			".comments".$parent_id."_block_form" => false,
		);
		$this->result["result"] = 'success';
	}

	/**
	 * Проверяет существует ли сообщение-родитель
	 *
	 * @param integer $element_id номер элемента, к которому добавляется комментарий
	 * @param string $module_name модуль, к которому добавляется комментарий
	 * @param string $element_type тип данных
	 * @return boolean
	 */
	private function check_parent_id($element_id, $module_name, $element_type)
	{
		if (! empty($_POST["parent_id"]))
		{
			$parent_id = DB::query_result(
					"SELECT id FROM {comments} WHERE id=%d AND trash='0'"
					." AND act='1' AND element_id=%d AND module_name='%h'"
					." AND element_type='%s' LIMIT 1",
					$_POST["parent_id"], $element_id, $module_name, $element_type
				);
			if(! $parent_id)
			{
				$this->result["errors"][0] = 'ERROR';
				return;
			}
			if($this->diafan->configmodules("count_level", "comments"))
			{
				if($parent_id)
				{
					$count = count($this->diafan->get_parents($parent_id, "comments"));
					$this->hide_form = $count + 2 >= $this->diafan->configmodules("count_level", "comments");
					if($count + 1 >= $this->diafan->configmodules("count_level", "comments"))
					{
						$this->result["errors"][0] = 'ERROR';
						return;
					}
				}
				else
				{
					$this->hide_form = $this->diafan->configmodules("count_level", "comments") == 1;
				}
			}
		}
		else
		{
			
			$this->hide_form = $this->diafan->configmodules("count_level", "comments") == 1;
		}
	}

	/**
	 * Валидация введенных данных
	 *
	 * @return void
	 */
	private function check_fields()
	{
		Custom::inc('includes/validate.php');
		if (! $_POST["comment"])
		{
			$mes = 'Вы забыли ввести текст комментария';
		}
		else
		{
			Custom::inc('includes/validate.php');
			$mes = Validate::text($_POST["comment"]);
		}
		if ($mes)
		{
			$this->result["errors"][0] = $this->diafan->_($mes);
		}
		
		// подписка на новые комментарии
		if($this->diafan->configmodules('use_mail', 'comments'))
		{
			if(! empty($_POST["mail"]))
			{
				$mes = Validate::mail($_POST["mail"]);
				if($mes)
				{
					$this->result["errors"]['mail'] = $this->diafan->_($mes);
				}
			}
		}
	}

	/**
	 * Проверяет попытку отправить сообщение повторно
	 *
	 * @param integer $element_id номер элемента, к которому добавляется комментарий
	 * @param string $module_name модуль, к которому добавляется комментарий
	 * @param string $element_type тип данных
	 * @return boolean
	 */
	private function error_insert($element_id, $module_name, $element_type)
	{
		$mes = '';
		$num = DB::query_result(
				"SELECT COUNT(id) FROM {comments} WHERE element_id=%d"
				." AND module_name='%h' AND element_type='%s' AND user_id='%d'"
				." AND text='%h' AND trash='0'",
				$element_id, $module_name, $element_type, $this->diafan->_users->id, $_POST["comment"]
			  );
		if ($num > 0)
		{
			$mes = $this->diafan->configmodules('error_insert_message', 'comments');
			$this->result["errors"][0] = $mes ? $mes : ' ';
		}
		return $this->result();
	}

	/**
	 * Уведомление администратора по e-mail
	 *
	 * @return void
	 */
	private function send_mail()
	{
		if (! $this->diafan->configmodules("sendmailadmin", 'comments'))
			return;

		$subject = str_replace(
			array('%title', '%url'),
			array(TITLE, BASE_URL),
			$this->diafan->configmodules("subject_admin", 'comments')
		);

		$message = str_replace(
			array('%title', '%urlpage', '%url', '%message'),
			array(
				TITLE,
				BASE_PATH_HREF.$this->diafan->_route->current_link(),
				BASE_URL,
				$this->message_admin_param
			),
			$this->diafan->configmodules("message_admin", 'comments')
		);

		$to   = $this->diafan->configmodules("emailconfadmin", 'comments')
		        ? $this->diafan->configmodules("email_admin", 'comments')
		        : EMAIL_CONFIG;

		$from = $this->diafan->configmodules("emailconf", 'comments') && $this->diafan->configmodules("email", 'comments') ? $this->diafan->configmodules("email", 'comments') : EMAIL_CONFIG;

		$this->diafan->_postman->message_add_mail($to, $subject, $message, $from);
	}

	/**
	 * Отправляет администратору SMS-уведомление
	 * 
	 * @return void
	 */
	private function send_sms()
	{
		if (! $this->diafan->configmodules("sendsmsadmin", 'comments', $this->site_id))
			return;
			
		$message = $this->diafan->configmodules("sms_message_admin", 'comments', $this->site_id);

		$to   = $this->diafan->configmodules("sms_admin", 'comments', $this->site_id);

		$this->diafan->_postman->message_add_sms($message, $to);
	}

	/**
	 * Загружает изображение
	 *
	 * @return void
	 */
	public function upload_image()
	{
		$element_id = 0;
		$tmpcode = '';
		$param_id = '';
		if(! empty($_POST["images_param_id"]))
		{
			$param_id = $this->diafan->filter($_POST, "int", "images_param_id");
		}
		else
		{
			if(! $this->diafan->configmodules("images_element") || ! $this->diafan->configmodules('form_images'))
			{
				return;
			}
		}
		if ($this->diafan->configmodules('only_user', 'comments'))
		{
			$this->check_user();

			if ($this->result())
				return;
		}
		if(empty($_POST["tmpcode"]))
		{
			return;
		}
		$tmpcode = $_POST["tmpcode"];
		$this->result["result"] = 'success';
		if (! empty($_FILES['images'.$param_id]) && $_FILES['images'.$param_id]['tmp_name'] != '' && $_FILES['images'.$param_id]['name'] != '')
		{
			try
			{
				$this->diafan->_images->upload($element_id, "comments", 'element', 0, $_FILES['images'.$param_id]['tmp_name'], $this->diafan->translit($_FILES['images'.$param_id]['name']), false, $param_id, $tmpcode);
			}
			catch(Exception $e)
			{
				Dev::$exception_field = ($param_id ? 'p'.$param_id : 'images');
				Dev::$exception_result = $this->result;
				throw new Exception($e->getMessage());
			}
			if($param_id)
			{
				$image_tag = 'large';
			}
			else
			{
				$image_tag = 'medium';
			}
			$images = $this->diafan->_images->get($image_tag, $element_id, "comments", 'element', 0, '', $param_id, 0, '', $tmpcode);
			$this->result["data"] = $this->diafan->_tpl->get('images', "comments", $images);
		}
	}

	/**
	 * Удаляет изображение
	 *
	 * @return void
	 */
	public function delete_image()
	{
		if(empty($_POST["id"]))
		{
			return;
		}
		if ($this->diafan->configmodules('only_user', 'comments'))
		{
			$this->check_user();

			if ($this->result())
				return;
		}
		if(empty($_POST["tmpcode"]))
		{
			return;
		}
		$row = DB::query_fetch_array("SELECT * FROM {images} WHERE module_name='comments' AND id=%d AND tmpcode='%s'", $_POST["id"], $_POST["tmpcode"]);
		if(! $row)
		{
			return;
		}
		$this->diafan->_images->delete_row($row);
		$this->result["result"] = 'success';
	}
	
	/**
	 * Выводит дополнительный список к текущему списку комментариев, прикрепленных к элементу
	 * 
	 * @return void
	 */
	public function get()
	{
		$attributes = array();
		if(! empty($_POST["attributes"]))
		{
			$attributes = $_POST["attributes"];
		}

		$this->get_attributes($attributes, 'element_id', 'module_name', 'element_type', 'site_id');
		$element_id = intval($attributes["element_id"]);
		$module_name = $this->diafan->filter($attributes, "string", "module_name");
		$module_name = in_array($module_name, $this->diafan->installed_modules, true) ? $module_name : '';
		$element_type = $this->diafan->filter($attributes, "string", "element_type");
		$element_type = $this->diafan->_router->check_element_type($element_type, true) ? $element_type : 'element';
		$site_id = intval($attributes["site_id"]);

		$uid = ! empty($_POST["uid"]) ? $this->diafan->filter($_POST, "string", "uid") : false;
		$paginator = false;
		$result = $this->diafan->_comments->get($element_id, $module_name, $element_type, $site_id, true);

		$view = 'get';
		if(! empty($result["view_rows"]))
		{
			$view = $result["view_rows"];
		}
		if($uid !== false && isset($result["paginator"]))
		{
			$paginator = $result["paginator"];
			unset($result["paginator"]);
		}
		
		$result["ajax"] = true;
		$this->result['set_location'] = true;
		$this->result['data'] = array(
			"form" => $this->diafan->_tpl->get($view, 'comments', array("rows" => $result["rows"], "result" => $result)),
		);
		if($paginator !== false && $uid !== false)
		{
			$this->result['data'][".paginator[uid='".$uid."']"] = $paginator;
		}
		$this->result['replace'] = true;
	}
}

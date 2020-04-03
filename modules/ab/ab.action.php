<?php
/**
 * Обработка запроса на добавление объявления
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

class Ab_action extends Action
{
	/**
	 * Добавление объявления
	 *
	 * @return void
	 */
	public function add()
	{
		$this->check_site_id();

		if ($this->result())
			return;

		if ($this->diafan->configmodules('only_user', 'ab', $this->site_id))
		{
			$this->check_user();
			if ($this->result())
				return;
		}

		$use_cat = $this->diafan->configmodules('cat', "ab", $this->site_id);
		if (empty($_POST["cat_id"]))
		{
			if($use_cat)
			{
				return false;
			}
			else
			{
				$cat_id = 0;
			}
		}
		else
		{
			$cat_id = $_POST["cat_id"];
		}

		if ($this->diafan->_captcha->configmodules('ab', $this->site_id))
		{
			$this->check_captcha();
		}

		$this->check_fields();

		$params = $this->model->get_param_form($this->site_id, $cat_id, $use_cat);

		$this->empty_required_field(array("params" => $params));

		if ($this->result())
			return;

		$fields = array('created', 'user_id', '[act]', 'site_id', 'cat_id');
		$query = array('%d', '%d', "'%d'", '%d', '%d');
		$values = array(
			time(),
			$this->diafan->_users->id,
			$this->model->check_premoderation($this->site_id) ? 0 : 1,
			$this->site_id,
			$cat_id
		);
		if($this->diafan->configmodules('form_name', "ab", $this->site_id))
		{
			$fields[] = '[name]';
			$query[] = "'%s'";
			$values[] = nl2br(htmlspecialchars($_POST["name"]));
		}
		if($this->diafan->configmodules('form_anons', "ab", $this->site_id))
		{
			$fields[] = '[anons]';
			$query[] = "'%s'";
			$values[] = nl2br(htmlspecialchars($_POST["anons"]));
		}
		if($this->diafan->configmodules('form_text', "ab", $this->site_id))
		{
			$fields[] = '[text]';
			$query[] = "'%s'";
			$values[] = nl2br(htmlspecialchars($_POST["text"]));
		}
		if($this->diafan->configmodules('form_date_finish', "ab", $this->site_id))
		{
			$fields[] = 'date_finish';
			$query[] = "%d";
			$values[] = $this->diafan->unixdate($_POST["date_finish"]);
		}

		$save = DB::query("INSERT INTO {ab} (".implode(",", $fields).") VALUES (".implode(",", $query).")", $values);

		if($cat_id)
		{
			DB::query("INSERT INTO {ab_category_rel} (element_id, cat_id) VALUES (%d, %d)", $save, $cat_id);
		}

		if(! empty($_POST["tmpcode"]))
		{
			DB::query("UPDATE {images} SET element_id=%d, tmpcode='' WHERE module_name='ab' AND element_id=0 AND tmpcode='%s'", $save, $_POST["tmpcode"]);
		}

		if(! $this->model->check_premoderation($this->site_id))
		{
			$this->diafan->_cache->delete('', 'ab');
		}
		if(ROUTE_AUTO_MODULE && ! empty($_POST["name"]))
		{
			$this->diafan->_route->save('', $_POST["name"], $save, 'ab', 'element', $this->site_id, $cat_id);
		}
		// если объявления сразу активируется на сайте, сохраняет ссылку на карте сайта
		if(in_array("map", $this->diafan->installed_modules))
		{
			if(! $this->model->check_premoderation($this->site_id))
			{
				$ab_row = array(
					"module_name" => 'ab',
					"id"          => $save,
					"site_id"     => $this->site_id,
					"cat_id"      => $cat_id,
					"date_start"  => 0,
					"date_finish" => 0,
				);
				if($this->diafan->configmodules('form_date_finish', "ab", $this->site_id))
				{
					$ab_row['date_finish'] = $this->diafan->unixdate($_POST["date_finish"]);
				}
				foreach ($this->diafan->_languages->all as $l)
				{
					$ab_row["act".$l["id"]] = $l["id"] == _LANG ? true : false;
				}
				$this->diafan->_map->index_element($ab_row);
			}
		}
		if(! $this->model->check_premoderation($this->site_id))
		{
			$this->diafan->_cache->delete('', 'cache_extreme');
		}
		// добавляет точку на карте для элемента
		$this->diafan->_geomap->save($save, "ab");

		$this->insert_values(array("id" => $save, "table" => "ab", "params" => $params, "multilang" => true));

		if ($this->result())
			return;

		$this->send_mail();
		$this->send_sms();

		$mes = $this->diafan->configmodules('add_message', 'ab', $this->site_id, _LANG);
		$this->result["errors"][0] = $mes ? $mes : ' ';
		$this->result["result"] = 'success';
		$this->result["data"] = array("form" => false);
	}

	/**
	 * Редактирование объявления
	 *
	 * @return void
	 */
	public function edit()
	{
		if(empty($_POST["id"]))
		{
			return;
		}

		$this->check_user();
		if ($this->result())
			return;

		$row = DB::query_fetch_array("SELECT id, [name], [anons], [text], cat_id, date_start, date_finish, [act]  FROM {ab}"
		." WHERE id=%d AND trash='0' AND site_id=%d AND user_id=%d LIMIT 1",
		$_POST["id"], $this->diafan->_site->id, $this->diafan->_users->id);
		if(! $row)
		{
			return false;
		}
		$use_cat = $this->diafan->configmodules('cat');
		if (empty($_POST["cat_id"]))
		{
			if($use_cat)
			{
				return false;
			}
			else
			{
				$cat_id = 0;
			}
		}
		else
		{
			$cat_id = $_POST["cat_id"];
		}

		if (! $this->diafan->_users->checked)
		{
			$this->result["errors"][0] = 'ERROR';
			return;
		}
		$this->result["hash"] = $this->diafan->_users->get_hash();

		$this->check_fields();

		$params = $this->model->get_param_form($this->diafan->_site->id, $cat_id, $use_cat);

		$this->empty_required_field(array("params" => $params));

		if ($this->result())
			return;

		$fields = array('created=%d', "[act]='%d'", 'cat_id=%d');
		$values = array(
			time(),
			$this->model->check_premoderation($this->site_id) ? 0 : $row["act"],
			$cat_id
		);
		if($this->diafan->configmodules('form_name', "ab", $this->site_id))
		{
			$fields[] = "[name]='%s'";
			$values[] = nl2br(htmlspecialchars($_POST["name"]));
		}
		if($this->diafan->configmodules('form_anons', "ab", $this->site_id))
		{
			$fields[] = "[anons]='%s'";
			$values[] = nl2br(htmlspecialchars($_POST["anons"]));
		}
		if($this->diafan->configmodules('form_text', "ab", $this->site_id))
		{
			$fields[] = "[text]='%s'";
			$values[] = nl2br(htmlspecialchars($_POST["text"]));
		}
		if($this->diafan->configmodules('form_date_finish', "ab", $this->site_id))
		{
			$fields[] = 'date_finish=%d';
			$values[] = $this->diafan->unixdate($_POST["date_finish"]);
		}
		$values[] = $_POST["id"];

		DB::query("UPDATE {ab} SET ".implode(",", $fields)." WHERE id=%d", $values);

		// добавляет точку на карте для элемента
		$this->diafan->_geomap->save($_POST["id"], "ab");

		if($cat_id != $row["cat_id"])
		{
			DB::query("DELETE FROM {ab_category_rel} WHERE element_id=%d", $_POST["id"]);
			DB::query("INSERT INTO {ab_category_rel} (element_id, cat_id) VALUES (%d, %d)", $_POST["id"], $cat_id);
		}
		if(! $this->model->check_premoderation($this->site_id))
		{
			$this->diafan->_cache->delete('', 'ab');
		}

		$this->update_values(array("id" => $_POST["id"], "table" => "ab", "params" => $params, "multilang" => true));

		if ($this->result())
			return;

		$this->result["errors"][0] = $this->diafan->_('Изменения сохранены.');
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
			if(! $this->diafan->configmodules("images_element", 'ab') || ! $this->diafan->configmodules('form_images', 'ab'))
			{
				return;
			}
		}
		if(! empty($_POST["id"]))
		{
			$this->check_user();

			if ($this->result())
				return;

			$user_id = DB::query_result("SELECT user_id FROM {ab} WHERE id=%d", $_POST["id"]);
			if($user_id != $this->diafan->_users->id)
			{
				return;
			}
			$element_id = $this->diafan->filter($_POST, "int", "id");

			if (! $this->diafan->_users->checked)
			{
				$this->result["errors"][0] = 'ERROR_HASH';
				return;
			}
			$this->result["hash"] = $this->diafan->_users->get_hash();
		}
		else
		{
			if ($this->diafan->configmodules('only_user', "ab", $this->site_id))
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
		}
		$this->result["result"] = 'success';
		if (! empty($_FILES['images'.$param_id]) && $_FILES['images'.$param_id]['tmp_name'] != '' && $_FILES['images'.$param_id]['name'] != '')
		{
			try
			{
				$this->diafan->_images->upload($element_id, "ab", 'element', $this->site_id, $_FILES['images'.$param_id]['tmp_name'], $this->diafan->translit($_FILES['images'.$param_id]['name']), false, $param_id, $tmpcode);
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
			$images = $this->diafan->_images->get($image_tag, $element_id, "ab", 'element', $this->site_id, '', $param_id, 0, '', $tmpcode);
			$this->result["data"] = $this->diafan->_tpl->get('images', "ab", $images);
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
		if(! empty($_POST["element_id"]))
		{
			$this->check_user();

			if ($this->result())
				return;

			$user_id = DB::query_result("SELECT user_id FROM {ab} WHERE id=%d", $_POST["element_id"]);
			if($user_id != $this->diafan->_users->id)
			{
				return;
			}
			$where = "element_id=%d";
			$id = $_POST["element_id"];

			if (! $this->diafan->_users->checked)
			{
				$this->result["errors"][0] = 'ERROR_HASH';
				return;
			}
			$this->result["hash"] = $this->diafan->_users->get_hash();
		}
		else
		{
			if ($this->diafan->configmodules('only_user', "ab", $this->site_id))
			{
				$this->check_user();

				if ($this->result())
					return;
			}
			if(empty($_POST["tmpcode"]))
			{
				return;
			}
			$where = "tmpcode='%s'";
			$id = $_POST["tmpcode"];
		}
		$row = DB::query_fetch_array("SELECT * FROM {images} WHERE module_name='ab' AND id=%d AND ".$where, $_POST["id"], $id);
		if(! $row)
		{
			return;
		}
		$this->diafan->_images->delete_row($row);
		$this->result["result"] = 'success';
	}

	/**
	 * Валидация введенных данных
	 *
	 * @return void
	 */
	private function check_fields()
	{
		Custom::inc('includes/validate.php');
		if($this->diafan->configmodules('form_name', "ab", $this->site_id))
		{
			if(empty($_POST["name"]))
			{
				$mes = 'Пожалуйста, введите заголовок.';
			}
			else
			{
				$mes = Validate::text($_POST["name"]);
			}
			if ($mes)
			{
				$this->result["errors"]["name"] = $this->diafan->_($mes);
			}
		}
		if($this->diafan->configmodules('form_anons', "ab", $this->site_id))
		{
			if(empty($_POST["anons"]))
			{
				$mes = 'Пожалуйста, введите краткое содержание.';
			}
			else
			{
				$mes = Validate::text($_POST["anons"]);
			}
			if ($mes)
			{
				$this->result["errors"]["anons"] = $this->diafan->_($mes);
			}
		}
		if($this->diafan->configmodules('form_text', "ab", $this->site_id))
		{
			if(empty($_POST["text"]))
			{
				$mes = 'Пожалуйста, введите полное содержание.';
			}
			else
			{
				$mes = Validate::text($_POST["text"]);
			}
			if ($mes)
			{
				$this->result["errors"]["text"] = $this->diafan->_($mes);
			}
		}
		if($this->diafan->configmodules('form_date_finish', "ab", $this->site_id))
		{
			$mes = '';
			if(! empty($_POST["date_finish"]))
			{
				$mes = Validate::date($_POST["date_finish"]);
				if(! $mes && $this->diafan->unixdate($_POST["date_finish"]) <= mktime(0,0,0))
				{
					$mes = 'Укажите дату, старше сегодняшней.';
				}
			}
			if ($mes)
			{
				$this->result["errors"]["date_finish"] = $this->diafan->_($mes);
			}
		}
	}

	/**
	 * Уведомление администратора по e-mail
	 *
	 * @return void
	 */
	private function send_mail()
	{
		if (! $this->diafan->configmodules("sendmailadmin", 'ab', $this->site_id))
			return;

		$subject = str_replace(
			array('%title', '%url'),
			array(TITLE, BASE_URL),
			$this->diafan->configmodules("subject_admin", 'ab', $this->site_id)
		);
		$mes = '';
		if($this->diafan->configmodules('form_name', "ab", $this->site_id))
		{
			$mes = $this->diafan->_('Заголовок', false).': '.nl2br(htmlspecialchars($_POST["name"]));
		}
		if($this->diafan->configmodules('form_anons', "ab", $this->site_id))
		{
			$mes .= ($mes ? '<br>' : '').$this->diafan->_('Краткое содержание', false).': '.nl2br(htmlspecialchars($_POST["anons"]));
		}
		if($this->diafan->configmodules('form_text', "ab", $this->site_id))
		{
			$mes .= ($mes ? '<br>' : '').$this->diafan->_('Полное содержание', false).': '.nl2br(htmlspecialchars($_POST["text"]));
		}
		if($this->diafan->configmodules('form_date_finish', "ab", $this->site_id))
		{
			$mes .= ($mes ? '<br>' : '').$this->diafan->_('Опубликовать на сайте до', false).': '.nl2br(htmlspecialchars($_POST["date_finish"]));
		}

		$mes .= ($this->message_admin_param ? '<br>'.$this->message_admin_param : '');

		$message = str_replace(
			array('%title', '%url', '%message'),
			array(
				TITLE,
				BASE_URL,
				$mes
			),
			$this->diafan->configmodules("message_admin", 'ab', $this->site_id)
		);

		$to   = $this->diafan->configmodules("emailconfadmin", 'ab', $this->site_id)
		        ? $this->diafan->configmodules("email_admin", 'ab', $this->site_id)
		        : EMAIL_CONFIG;
		$from = $this->diafan->configmodules("emailconf", 'ab', $this->site_id)
		        ? $this->diafan->configmodules("email", 'ab', $this->site_id)
		        : EMAIL_CONFIG;

		$this->diafan->_postman->message_add_mail($to, $subject, $message, $from);
	}

	/**
	 * Отправляет администратору SMS-уведомление
	 *
	 * @return void
	 */
	private function send_sms()
	{
		if (! $this->diafan->configmodules("sendsmsadmin", 'ab', $this->site_id))
			return;

		$message = $this->diafan->configmodules("sms_message_admin", 'ab', $this->site_id);

		$to   = $this->diafan->configmodules("sms_admin", 'ab', $this->site_id);

		$this->diafan->_postman->message_add_sms($message, $to);
	}

	/**
	 * Удаляет объявление
	 *
	 * @return void
	 */
	public function delete()
	{
		if(empty($_GET["id"]) || ! $this->diafan->_users->id)
		{
			Custom::inc('includes/404.php');
		}

		if (! $this->diafan->_users->checked)
		{
			Custom::inc('includes/404.php');
		}

		$row = DB::query_fetch_array("SELECT id, [act] FROM {ab} WHERE id=%d AND trash='0' AND site_id=%d AND user_id=%d LIMIT 1",
		$_GET["id"], $this->diafan->_site->id, $this->diafan->_users->id);
		if(! $row)
		{
			Custom::inc('includes/404.php');
		}
		DB::query("DELETE FROM {ab} WHERE id=%d AND trash='0'", $row["id"]);
		DB::query("DELETE FROM {ab_category_rel} WHERE element_id=%d", $row["id"]);
		DB::query("DELETE FROM {ab_param_element} WHERE element_id=%d", $row["id"]);
		DB::query("DELETE FROM {ab_counter} WHERE element_id=%d OR rel_element_id=%d", $row["id"], $row["id"]);
		DB::query("DELETE FROM {ab_rel} WHERE element_id=%d", $row["id"]);
		DB::query("DELETE FROM {access} WHERE element_id=%d AND module_name='ab' AND element_type='element'", $row["id"]);

		$this->diafan->_route->delete($row["id"], 'ab');
		$this->diafan->_tags->delete($row["id"], "ab");
		$this->diafan->_comments->delete($row["id"], "ab");
		$this->diafan->_rating->delete($row["id"], "ab");
		$this->diafan->_map->delete($row["id"], "ab");
		$this->diafan->_images->delete($row["id"], "ab");
		$this->diafan->_attachments->delete($row["id"], "ab");
		$this->diafan->_menu->delete($row["id"], 'ab');

		// удаляет  точку на карте
		$this->diafan->_geomap->delete($row["id"], "ab");

		if($row["act"])
		{
			$this->diafan->_cache->delete('', 'ab');
		}
		$this->result["redirect"] = preg_replace('/(\?|\&)rand\=([0-9]+)/', '', getenv('HTTP_REFERER'));
		if(! strpos($this->result["redirect"],  "action=my"))
		{
			$this->result["redirect"] = $this->diafan->_route->current_link().'?action=my&rand='.'rand='.rand(0, 99999);
		}
		else
		{
			$this->result["redirect"] .= (strpos($this->result["redirect"], '?') !== false ? '&' : '?').'rand='.rand(0, 99999);
		}
		$this->diafan->redirect($this->result["redirect"]);
	}

	/**
	 * Блокирует/разблокирует объявление
	 *
	 * @return void
	 */
	public function block()
	{
		if($this->model->check_premoderation())
		{
			Custom::inc('includes/404.php');
		}
		if(empty($_GET["id"]) || ! $this->diafan->_users->id)
		{
			Custom::inc('includes/404.php');
		}

		if (! $this->diafan->_users->checked)
		{
			Custom::inc('includes/404.php');
		}

		$row = DB::query_fetch_array("SELECT [act], id FROM {ab} WHERE id=%d AND trash='0' AND site_id=%d AND user_id=%d LIMIT 1",
		$_GET["id"], $this->diafan->_site->id, $this->diafan->_users->id);
		if(! $row)
		{
			Custom::inc('includes/404.php');
		}
		if($row["act"])
		{
			$row["act"] = 0;
		}
		else
		{
			$row["act"] = 1;
		}
		DB::query("UPDATE {ab} SET [act]='%d' WHERE id=%d", $row["act"], $row["id"]);
		$this->diafan->_cache->delete('', 'ab');

		$this->result["redirect"] = preg_replace('/(\?|\&)rand\=([0-9]+)/', '', getenv('HTTP_REFERER'));
		$this->result["redirect"] .= (strpos($this->result["redirect"], '?') !== false ? '&' : '?').'rand='.rand(0, 99999);
		$this->diafan->redirect($this->result["redirect"]);
	}

	/**
	 * Поиск объявлений
	 *
	 * @return void
	 */
	public function search()
	{
		$this->model->list_search();
		$this->model->result["ajax"] = true;
		$this->result["data"] = $this->diafan->_tpl->get($this->model->result["view"], 'ab', $this->model->result);
		$this->result["result"] = 'success';
	}
}

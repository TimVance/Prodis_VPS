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

class Reviews_action extends Action
{
	/**
	 * Обрабатывает полученные данные из формы
	 *
	 * @return void
	 */
	public function add()
	{
		if ($this->diafan->configmodules('only_user', 'reviews'))
		{
			$this->check_user();

			if ($this->result())
				return;
		}
		$element_id = $this->diafan->filter($_POST, "int", "element_id");

		$module_name = $this->diafan->filter($_POST, "string", "module_name");

		$element_type = $this->diafan->filter($_POST, "string", "element_type");
		
		if(empty($element_id) || empty($element_type) || empty($module_name))
		{
			$this->result["errors"][0] = $this->diafan->_('Переданы не все данные. Проверьте форму.');
			return;
		}

		if ($this->result())
			return;

		$where_form = "(module_name='".$module_name."' OR module_name='') AND show_in_"
					  .($this->diafan->_users->id ? "form_auth" : "form_no_auth")."='1'";
		$params = $this->model->get_params(array("module" => "reviews", "where" => $where_form));

		if ($this->diafan->_captcha->configmodules('reviews'))
		{
			$this->check_captcha();
		}
		$this->empty_required_field(array("params" => $params));

		if ($this->result())
			return;

		$save = DB::query(
			"INSERT INTO {reviews} (created, module_name, element_id, element_type, user_id, act)"
			." VALUES (%d, '%h', %d, '%s', %d, '%d')",
			time(), $module_name, $element_id, $element_type, $this->diafan->_users->id,
			$this->diafan->configmodules('security_moderation', 'reviews') && ! $this->diafan->_users->roles('edit', 'reviews') ? 0 : 1
		);
		if(! $this->diafan->configmodules('security_moderation', 'reviews') || $this->diafan->_users->roles('edit', 'reviews'))
		{
			$this->diafan->_cache->delete($_GET, 'cache_extreme');
		}

		if(! empty($_POST["tmpcode"]))
		{
			DB::query("UPDATE {images} SET element_id=%d, tmpcode='' WHERE module_name='reviews' AND element_id=0 AND tmpcode='%s'", $save, $_POST["tmpcode"]);
		}

		$this->insert_values(array("id" => $save, "table" => "reviews", "params" => $params));

		if ($this->result())
			return;

		$row = DB::query_fetch_array("SELECT * FROM {reviews} WHERE id=%d", $save);

		$this->send_mail();
		$this->send_sms();

		$this->result["data"] = array(".reviews_message" => $this->diafan->configmodules('add_message', 'reviews'));
		
		if($this->diafan->configmodules("hide_form", "reviews"))
		{
			$this->result["data"]["form"] = false;
		}

		//модерация сообщений
		if ($this->diafan->configmodules('security_moderation', 'reviews') && ! $this->diafan->_users->roles('edit', 'reviews'))
		{
			$this->result["result"] = 'success';
			return;
		}

		$where_list = "(module_name='".$module_name."' OR module_name='')";

		$params_list = $this->model->get_params(array("module" => "reviews", "where" => $where_list, "fields" => 'show_in_list, info'));

		$this->model->element($row, $params_list);

		$this->result["add"] = $this->diafan->_tpl->get('id', 'reviews', $row);

		$this->result["result"] = 'success';
	}

	/**
	 * Уведомление администратора по e-mail
	 *
	 * @return void
	 */
	private function send_mail()
	{
		if (! $this->diafan->configmodules("sendmailadmin", 'reviews'))
			return;

		$subject = str_replace(
			array('%title', '%url'),
			array(TITLE, BASE_URL),
			$this->diafan->configmodules("subject_admin", 'reviews')
		);

		$message = str_replace(
			array('%title', '%urlpage', '%url', '%message'),
			array(
				TITLE,
				BASE_PATH_HREF.$this->diafan->_route->current_link(),
				BASE_URL,
				$this->message_admin_param
			),
			$this->diafan->configmodules("message_admin", 'reviews')
		);

		$to   = $this->diafan->configmodules("emailconfadmin", 'reviews')
		        ? $this->diafan->configmodules("email_admin", 'reviews')
		        : EMAIL_CONFIG;
		$from = $this->diafan->configmodules("emailconf", 'reviews')
		        ? $this->diafan->configmodules("email", 'reviews')
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
		if (! $this->diafan->configmodules("sendsmsadmin", 'reviews', $this->site_id))
			return;
			
		$message = $this->diafan->configmodules("sms_message_admin", 'reviews', $this->site_id);

		$to   = $this->diafan->configmodules("sms_admin", 'reviews', $this->site_id);

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
		if ($this->diafan->configmodules('only_user', 'reviews'))
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
				$this->diafan->_images->upload($element_id, "reviews", 'element', 0, $_FILES['images'.$param_id]['tmp_name'], $this->diafan->translit($_FILES['images'.$param_id]['name']), false, $param_id, $tmpcode);
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
			$images = $this->diafan->_images->get($image_tag, $element_id, "reviews", 'element', 0, '', $param_id, 0, '', $tmpcode);
			$this->result["data"] = $this->diafan->_tpl->get('images', "reviews", $images);
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
		if ($this->diafan->configmodules('only_user', 'reviews'))
		{
			$this->check_user();

			if ($this->result())
				return;
		}
		if(empty($_POST["tmpcode"]))
		{
			return;
		}
		$row = DB::query_fetch_array("SELECT * FROM {images} WHERE module_name='reviews' AND id=%d AND tmpcode='%s'", $_POST["id"], $_POST["tmpcode"]);
		if(! $row)
		{
			return;
		}
		$this->diafan->_images->delete_row($row);
		$this->result["result"] = 'success';
	}
	
	/**
	 * Выводит отзывы и форму добавления отзывов для текущей страницы.
	 * 
	 * @return void
	 */
	public function show()
	{
		$attributes = array();
		if(! empty($_POST["attributes"]))
		{
			$attributes = $_POST["attributes"];
		}
		
		$this->get_attributes($attributes, 'template', 'modules', 'element_type');

		$element_types  = $attributes["element_type"] ? explode(",", $attributes["element_type"]) : array('cat', 'element', 'brand');

		$modules = $attributes["modules"] ? explode(",", $attributes["modules"]) : array($this->diafan->_site->module, 'site');

		$element_type = 'element';
		if($this->diafan->_route->show && in_array('element', $element_types))
		{
			$element_id = $this->diafan->_route->show;
			$element_type = 'element';
		}
		elseif($this->diafan->_route->cat && in_array('cat', $element_types))
		{
			$element_id = $this->diafan->_route->cat;
			$element_type = 'cat';
		}
		elseif($this->diafan->_route->brand && in_array('brand', $element_types))
		{
			$element_id = $this->diafan->_route->brand;
			$element_type = 'brand';
		}

		if(in_array($this->diafan->_site->module, $modules) && $this->diafan->_site->module && ! empty($element_id))
		{
			$module_name = $this->diafan->_site->module;
		}
		elseif(in_array('site', $modules))
		{
			$element_id = $this->diafan->_site->id;
			$module_name = 'site';
		}
		
		if(empty($module_name))
		{
			$this->result['data'] = array(
				'form' => ''
			);
		}
		else
		{
			$uid = ! empty($_POST["uid"]) ? $this->diafan->filter($_POST, "string", "uid") : false;
			
			$paginator = false;
			$result = $this->model->show($element_id, $module_name, $element_type);

			$view = 'show';
			if(! empty($result["view_rows"]))
			{
				$view = $result["view_rows"];
			}
			if($uid !== false && isset($result["paginator"]))
			{
				$paginator = $result["paginator"];
				unset($result["paginator"]);
			}

			$result["attributes"] = $attributes;

			$result["ajax"] = true;
			$this->result['set_location'] = true;
			$this->result['data'] = array(
				"form" => $this->diafan->_tpl->get($view, 'reviews', $result, $attributes["template"]),
			);
			if($paginator !== false && $uid !== false)
			{
				$this->result['data'][".paginator[uid='".$uid."']"] = $paginator;
			}
		}
		$this->result['replace'] = true;
	}
}
<?php
/**
 * Редактирование пользователей сайта
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
 * Users_admin
 */
class Users_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'users';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'fio' => array(
				'type' => 'text',
				'name' => 'ФИО',
				'help' => 'Свободное информационное текстовое поле.',
			),
			'created' => array(
				'type' => 'datetime',
				'name' => 'Дата регистрации',
				'help' => 'В формате дд.мм.гггг чч:мм, при регистрации устанавливается текущая.',
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Активен',
				'help' => 'Если не отмечена, пользователь не сможет авторизоваться на сайте',
				'default' => true,
			),
			'name' => array(
				'type' => 'text',
				'name' => 'Логин',
				'help' => 'Имя пользователя для авторизации на сайте и входа в систему администрирования (если установлен соответствующий тип пользователя). Только цифры и латинские буквы.',
			),
			'password' => array(
				'type' => 'password',
				'name' => 'Пароль',
				'help' => 'Пароль для входа в систему администрирования. Только цифры и латинские буквы. Если не указан, пароль не перезаписывается.',
			),
			'mail' => array(
				'type' => 'email',
				'name' => 'Email',
				'help' => 'Почтовый адрес пользователя в формате mail@site.ru.',
			),
			'phone' => array(
				'type' => 'phone',
				'name' => 'Телефон',
				'help' => 'Телефон для SMS, в федеральном формате.',
			),
			'role_id' => array(
				'type' => 'select',
				'name' => 'Тип пользователя',
				'help' => 'Тип прав пользователя. Уровень доступа настраивается в модуле «Права доступа».',
				'select_db' => array(
					'table' => 'users_role',
					'name' => 'nameLANG',
					'where' => "trash='0'",
					'order' => "sort ASC",
				),
			),
			'activity' => array(
				'type' => 'function',
				'name' => 'Активность',
			),
			'hr2' => array(
				'type' => 'title',
				'name' => 'Дополнительно',
			),
			'identity' => array(
				'type' => 'text',
				'name' => 'URL на страницу в соц. сети',
				'help' => 'Заполняется при авторизации через соц. сеть. Служит идентификатором, с помощью которого пользователь может зайти в аккаунт.',
			),
			'lang_id' => array(
				'type' => 'select',
				'name' => 'Язык пользовательской части сайта по умолчанию',
				'help' => 'Если предусмотрено несколько языковых версий сайта, то этой опцией можно задать какую версию сайта открыть при авторизации пользователя. При регистрации система запоминает текущую языковую версию.',
			),
			'avatar' => array(
				'type' => 'function',
				'name' => 'Аватар',
				'help' => 'Небольшое изображение для форума, личного кабинета пользователя. Параметр появляется, если в настройках модуля отмечена опция «Использовать аватар».',
			),
			'param' => array(
				'type' => 'function',
				'name' => 'Дополнительные поля',
				'help' => 'Группа полей, определенных в части «Конструктор формы регистрации». Поля выводятся соответственно выбранному типу пользователя.',
			),
			'hr3' => array(
				'type' => 'title',
				'name' => 'Настройки для административной части',
			),
			'start_admin' => array(
				'type' => 'select',
				'name' => 'Стартовая страница административной части',
				'help' => 'Первая страница, загружаемая при авторизации администратора. По умолчанию «Страницы сайта». Возможно установить любую другую, согласно потребностям и тематике сайта, например, «Заказы» или «Вопрос-Ответ».',
				'select_db' => array(
					'table' => 'admin',
					'id' => 'rewrite',
					'where' => "parent_id=0 AND act='1'",
					'order' => 'sort ASC',
					'empty' => '-',
				),
			),
			'useradmin' => array(
				'type' => 'select',
				'name' => 'Панель быстрого редактирования',
				'help' => 'Настраивает вывод в пользовательской части сайта в шапке панели, с помошью которой можно редактировать контент прямо на сайте.',
				'select' => array(
					'0' => 'отключена',
					'1' => 'включена',
					'2' => 'только панель без режима редактирования',
				)
			),
			'htmleditor' => array(
				'type' => 'checkbox',
				'name' => 'Использовать визуальный редактор',
				'help' => 'Если отмечено, при редактировании контента во всех модулях будет использоваться визуальный редактор.',
			),
			'copy_files' => array(
				'type' => 'checkbox',
				'name' => 'Сохранять картинки с внешних сайтов, при вставке контента в визуальный редактор',
				'help' => 'Если отмечено, при вставке контента в визуальный редактор во всех модулях, будут определяться используемые в контенте изображения (как тег img src), сохраняться на сервере и вставляться как локальные. Например, если выделить и скопировать текст с изображениями с новостного сайта, а затем вставить на Ваш сайт, изображения будут автоматически сохранены на Ваш сайт, а ссылки заменены на локальные.',
			),
            'rel_goods' => array(
                'type' => 'function',
                'name' => 'Прикрепленные ТЦ',
                'help' => 'Прикрепленные ТЦ',
            ),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'name' => array(
			'name' => 'ФИО',
			'variable' => 'fio',
		),
		'session' => array(
			'name' => 'Был на сайте',
			'type' => 'function',
		),
		'ip' => array(
			'name' => 'IP',
			'type' => 'function',
		),
		'role_id' => array(
			'sql' => true,
			'name' => 'Тип пользователя',
			'type' => 'select',
		),
		'actions' => array(
			'act' => true,
			'trash' => true,
		),
	);

	/**
	 * @var array поля для фильтра
	 */
	public $variables_filter = array (
		'fio' => array(
			'type' => 'text',
			'name' => 'Искать по ФИО',
		),
		'role_id' => array(
			'type' => 'select',
			'name' => 'Искать по типу пользователя',
		),
		'param' => array(
			'type' => 'function',
		),
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		if(count($this->diafan->_languages->all) > 1)
		{
			foreach ($this->diafan->_languages->all as $language)
			{
				$rows[$language["id"]] = $language["name"];
			}
			$this->diafan->variable('lang_id', 'select', $rows);
		}
		else
		{
			$this->diafan->variable_unset("lang_id");
		}
		if (! $this->diafan->is_new && $this->diafan->id == $this->diafan->_users->id
			|| ! empty($_REQUEST["action"]) && $_REQUEST["action"] == 'save' && ! empty($_REQUEST["id"]) && $_REQUEST["id"] == $this->diafan->_users->id)
		{
			$this->diafan->variable_unset('role_id');
			$this->diafan->variable_unset('act');
		}
	}

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
        if($this->diafan->_route->error == 2)
        {
            echo '<div class="error">'.$this->diafan->_('Извините, пользователь с таким логином уже существует.').'</div>';
        }

		$this->diafan->addnew_init('Добавить пользователя');
	}

	/**
	 * Выводит список пользователей
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Выводит форму редактирования
	 * @return void
	 */
	public function edit()
	{
		$admin_roles = DB::query_fetch_value("SELECT DISTINCT(role_id) FROM {users_role_perm} WHERE type='admin'", "role_id");
		echo '<script type="text/javascript">var admin_roles = ["'.implode('", "', $admin_roles).'"];</script>';
		parent::__call('edit', array());
	}

	/**
	 * Выводит статус пользователя (на сайте) в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_session($row, $var)
	{
		if(! isset($this->cache["prepare"]["session"]))
		{
			$this->cache["prepare"]["session"] = DB::query_fetch_key("SELECT * FROM {sessions} WHERE user_id IN (%s)", implode(",", $this->diafan->rows_id), "user_id");
		}
		$text = '<div style="display: inline-block;">';
		if(! empty($this->cache["prepare"]["session"][$row["id"]]))
		{
			$s = $this->cache["prepare"]["session"][$row["id"]];
			if($s["timestamp"] >= time() - 900 && substr($s["session_id"], 0, 1) != '_')
			{
				$text .= ' <span style="color: #ffffff; background-color: red; padding: 0px 5px; ">'.$this->diafan->_('на сайте').'</span>';
			}
			else
			{
				$text .= date('d.m.Y H:i', $s["timestamp"]);
			}
		}
		else
		{
				$text .= $this->diafan->_('Более двух недель назад');
		}
		$text .= '</div>';
		return $text;
	}

	/**
	 * Выводит IP пользователя в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_ip($row, $var)
	{
		$text = '<div style="color: gray;">';
		if(! empty($this->cache["prepare"]["session"][$row["id"]]))
		{
			$s = $this->cache["prepare"]["session"][$row["id"]];
			$text .= $s["hostname"];
		}
		$text .= '</div>';
		return $text;
	}

	/**
	 * Проверяет можно ли выполнять действия с текущим элементом строки
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param string $action действие
	 * @return boolean
	 */
	public function check_action($row, $action = '')
	{
		// пользователь не может удалить самого себя
		if($this->diafan->_users->id == $row["id"])
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Редактирование поля "Аватар"
	 * @return void
	 */
	public function edit_variable_avatar()
	{
		if (! $this->diafan->configmodules("avatar", "users"))
			return;

		echo '
		<div class="unit">
			<div class="infofield">
				'.$this->diafan->variable_name().$this->diafan->help().'
			</div>';
		if ($this->diafan->values("name") && file_exists(ABSOLUTE_PATH.USERFILES.'/avatar/'.$this->diafan->values("name").'.png'))
		{
			echo '<img src="'.BASE_PATH.USERFILES.'/avatar/'.$this->diafan->values("name").'.png?'.rand(0, 99).'" width="'
			.$this->diafan->configmodules("avatar_width", "users").'" height="'
			.$this->diafan->configmodules("avatar_height", "users").'" alt="'.$this->diafan->values("fio").' ('.$this->diafan->values("name").')">'
			.'<input type="checkbox" name="delete_avatar" id="input_delete_avatar" value="1"> <label for="input_delete_avatar">'.$this->diafan->_('Удалить')
			.'</label>';
		}
		echo '
			<input type="file" name="avatar" class="file">
		</div>';
	}

	/**
	 * Редактирование поля "Дополнительные параметры"
	 *
	 * @return void
	 */
	public function edit_variable_param()
	{
		$param_role_rels = array();
		$params = array();
		$rows = DB::query_fetch_all("SELECT role_id, element_id FROM {users_param_role_rel} WHERE trash='0' AND role_id>0");
		foreach ($rows as $row)
		{
		    $param_role_rels[$row["role_id"]][] = $row["element_id"];
			$params[] = $row["element_id"];
		}
		array_unique($params);

		if (! $this->diafan->is_new && $this->diafan->id == $this->diafan->_users->id)
		{
			echo '<input name="role_id" type="hidden" value="'.$this->diafan->_users->role_id.'">';
		}
		echo '<script language="javascript">
			var param = [];
			param[0] = "#param'.implode(',#param', $params).'";';
			foreach ($param_role_rels as $role_id => $params)
			{
				echo 'param['.$role_id.'] = "#param'.implode(',#param', $params).'";';
			}
		echo '</script>';

		parent::__call('edit_variable_param', array());
	}

	/**
	 * Редактирование поля "Пароль"
	 * @return void
	 */
	public function edit_variable_password()
	{
		echo '
		<div class="unit" id="password">
			<div class="infofield">
				'.$this->diafan->variable_name().$this->diafan->help().'
			</div>
			<input type="password" name="password" value="" placeholder="'.$this->diafan->_('Пароль сохранен. Введите новый для изменения').'">
		</div>';
	}


	/**
	 * Вывод активности пользователя
	 * @return void
	 */
	public function edit_variable_activity()
	{
	    /*
		if(in_array('shop', $this->diafan->installed_modules))
		{
			$count = DB::query_result("SELECT COUNT(*) FROM {shop_order} WHERE user_id=%d AND trash='0'",$this->diafan->id);
			if ($count)
			{
				echo '<div class="unit"><div class="infofield"><i class="fa fa-shop-order"></i> '.$this->diafan->_('Заказы').'</div>';
				$summ = DB::query_result("SELECT SUM(summ) FROM {shop_order} WHERE user_id=%d AND trash='0'",$this->diafan->id);
				echo $this->diafan->_('Пользователь совершил').' <a href="'.BASE_PATH_HREF.'shop/order/?filter_user_id='.$this->diafan->id.'">'.$count .' '. $this->diafan->_('заказ(ов)').'</a> '.$this->diafan->_('на сумму ').' <b>'.$summ.'</b>';
				echo '</div>';
			}
		}*/
		if(in_array('comments', $this->diafan->installed_modules))
		{
			$count = DB::query_result("SELECT COUNT(*) FROM {comments} WHERE user_id=%d AND trash='0'",$this->diafan->id);
			if ($count)
			{
				echo '<div class="unit"><div class="infofield"><i class="fa fa-comments"></i> '.$this->diafan->_('Комментарии').'</div>';
				echo $this->diafan->_('Пользователь оставил').' <a href="'.BASE_PATH_HREF.'comments/?filter_user_id='.$this->diafan->id.'">'.$count .' '. $this->diafan->_('комментарий(ев)').'</a>';
				echo '</div>';
			}
		}
		if(in_array('feedback', $this->diafan->installed_modules))
		{
			$count = DB::query_result("SELECT COUNT(*) FROM {feedback} WHERE user_id=%d AND trash='0'",$this->diafan->id);
			if ($count)
			{
				echo '<div class="unit"><div class="infofield"><i class="fa fa-feedback"></i> '.$this->diafan->_('Обратная связь').'</div>';
				echo $this->diafan->_('Пользователь оставил').' <a href="'.BASE_PATH_HREF.'feedback/?filter_user_id='.$this->diafan->id.'">'.$count .' '. $this->diafan->_('сообщение(ий)').'</a>';
				echo '</div>';
			}
		}
		if(in_array('faq', $this->diafan->installed_modules))
		{
			$count = DB::query_result("SELECT COUNT(*) FROM {faq} WHERE user_id=%d AND trash='0'",$this->diafan->id);
			if ($count)
			{
				echo '<div class="unit"><div class="infofield"><i class="fa fa-faq"></i> '.$this->diafan->_('Вопрос-ответ').'</div>';
				echo $this->diafan->_('Пользователь задал').' <a href="'.BASE_PATH_HREF.'faq/?filter_user_id='.$this->diafan->id.'">'.$count .' '. $this->diafan->_('вопрос(ов)').'</a>';
				echo '</div>';
			}
		}
		if(in_array('ab', $this->diafan->installed_modules))
		{
			$count = DB::query_result("SELECT COUNT(*) FROM {ab} WHERE user_id=%d AND trash='0'",$this->diafan->id);
			if ($count)
			{
				echo '<div class="unit"><div class="infofield"><i class="fa fa-ab"></i> '.$this->diafan->_('Объявления').'</div>';
				echo $this->diafan->_('Пользователь добавил').' <a href="'.BASE_PATH_HREF.'ab/?filter_user_id='.$this->diafan->id.'">'.$count .' '. $this->diafan->_('объявление(ий)').'</a>';
				echo '</div>';
			}
		}
	}

	/**
	 * Валидация поля "Логин"
	 *
	 * @return void
	 */
	public function validate_variable_name()
	{
		$this->diafan->set_error("name", Validate::login($_POST["name"], $this->diafan->id));
	}

	/**
	 * Валидация поля "E-mail"
	 *
	 * @return void
	 */
	public function validate_variable_mail()
	{
		$mes = Validate::mail($_POST["mail"]);
		if($mes)
		{
			$this->diafan->set_error("mail", $mes);
		}
		else
		{
			$this->diafan->set_error("mail", Validate::mail_user($_POST["mail"], $this->diafan->id));
		}
	}

	/**
	 * Валидация поля "Пароля"
	 *
	 * @return void
	 */
	public function validate_variable_password()
	{
		$access_admin = $this->diafan->id == $this->diafan->_users->id || DB::query_result("SELECT id FROM {users_role_perm} WHERE role_id=%d AND type='admin'", $_POST["role_id"]);
		if($_POST["password"] && $access_admin)
		{
			$this->diafan->set_error("password", Validate::password($_POST["password"], true));
		}
	}


	/**
	 * Заглушка заказов
	 *
	 * @return void
	 */
	public function save_variable_activity()
	{
	}

	/**
	 * Сохранение поля "Аватар"
	 *
	 * @return void
	 */
	public function save_variable_avatar()
	{
		if (! $this->diafan->configmodules("avatar", "users"))
		{
			return false;
		}
		if(! $this->diafan->is_new)
		{
			if (! empty($_POST["delete_avatar"]))
			{
				File::delete_file(USERFILES.'/avatar/'.$this->diafan->values("name").'.png');
			}
			elseif(file_exists(ABSOLUTE_PATH.USERFILES.'/avatar/'.$this->diafan->values("name").'.png') && $this->diafan->values("name") != $_POST["name"])
			{
				File::rename_file($_POST["name"].'.png', $this->diafan->values("name").'.png', USERFILES.'/avatar');
			}
		}
		if (isset($_FILES["avatar"]) && is_array($_FILES["avatar"]) && $_FILES["avatar"]['name'] != '')
		{
			$this->diafan->_users->create_avatar($_POST["name"], $_FILES["avatar"]['tmp_name']);
		}
	}

	/**
	 * Сохранение поля "Дополнительные параметры"
	 *
	 * @return void
	 */
	public function save_variable_param()
	{
		parent::__call('save_variable_param', array($this->get_where_param_role_rel()));
	}

	/**
	 * Получает условие для SQL-запроса: выбор полей с учетом роли пользователя
	 *
	 * @return string
	 */
	private function get_where_param_role_rel()
	{
		$param_ids = DB::query_fetch_value("SELECT element_id FROM {users_param_role_rel} WHERE role_id=%d OR role_id=0", $_POST["role_id"], "element_id");
		if($param_ids)
		{
			return " AND id IN (".implode(",", $param_ids).")";
		}
		return '';
	}

	/**
	 * Сохранение поля "Пароль"
	 * @return void
	 */
	public function save_variable_password()
	{
		if(! empty( $_POST["password"]))
		{
			$this->diafan->set_query("password='%s'");
			$this->diafan->set_value(encrypt($_POST["password"]));
		}
	}

	/**
	 * Сохранение поля "Телефон"
	 * @return void
	 */
	public function save_variable_phone()
	{
		$phone = preg_replace('/[^0-9]+/', '', $_POST["phone"]);
		if(! DB::query_result("SELECT id FROM {subscription_phones} WHERE phone='%s' AND trash='0'", $phone))
		{
			DB::query("INSERT INTO {subscription_phones} (phone, created, name, act) VALUES ('%s', %d, '%h', '1')", $phone, $_POST["fio"], time());
		}
		$this->diafan->set_query("phone='%h'");
		$this->diafan->set_value($phone);
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("users_param_element", "element_id IN (".implode(",", $del_ids).")");

		if(in_array("shop", $this->diafan->installed_modules))
		{
			$this->diafan->del_or_trash_where("shop_order_param_user", "user_id IN (".implode(",", $del_ids).")");
			$this->diafan->del_or_trash_where("shop_cart_goods", "cart_id IN (SELECT id FROM {shop_cart} WHERE user_id IN (".implode(",", $del_ids).")");
			$this->diafan->del_or_trash_where("shop_cart", "user_id IN (".implode(",", $del_ids).")");
			$this->diafan->del_or_trash_where("shop_wishlist", "user_id IN (".implode(",", $del_ids).")");
			$this->diafan->del_or_trash_where("shop_waitlist", "user_id IN (".implode(",", $del_ids).")");
		}

		if(in_array("messages", $this->diafan->installed_modules))
		{
			$this->diafan->del_or_trash_where("messages_user", "user_id IN (".implode(",", $del_ids).") OR contact_user_id IN (".implode(",", $del_ids).")");
			$this->diafan->del_or_trash_where("messages", "author IN (".implode(",", $del_ids).") OR to_user IN (".implode(",", $del_ids).")");
		}

		$this->diafan->del_or_trash_where("users_token", "user_id IN (".implode(",", $del_ids).")");
	}
}

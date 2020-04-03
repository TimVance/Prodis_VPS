<?php
/**
 * Редактирование сообщений из формы обратной связи
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
 * Feedback_admin
 */
class Feedback_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'feedback';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'created' => array(
				'type' => 'datetime',
				'name' => 'Дата',
				'help' => 'Дата поступления сообщения в формате дд.мм.гггг чч:мм.',
			),
			'site_id' => array(
				'type' => 'select',
				'name' => 'Раздел сайта',
				'help' => 'Принадлежность к странице сайта.',
				'disabled' => true,
			),
			'lang_id' => array(
				'type' => 'select',
				'name' => 'Язык интерфейса',
				'help' => 'Языковая версия сайта, на которой находился пользователь, когда писал сообщение.',
			),
			'url' => array(
				'type' => 'function',
				'name' => 'Страница, с которой отправлено сообщение',
				'help' => 'Полный адрес страницы, с которой пользователь отправил сообщение.',
				'no_save' => true,
			),			
			'hr1' => 'hr',
			'user_id' => array(
				'type' => 'function',
				'name' => 'Автор',
				'help' => 'Пользователь, создавший сообщение в форме на сайте.',
			),			
			'param' => array(
				'type' => 'function',
				'name' => 'Конструктор формы',
				'help' => 'Поля, добавленные в конструкторе формы.',
			),
			'sendmail' => array(
				'type' => 'function',
				'name' => 'Отправить ответ',
				'help' => 'Возможность отправить ответ на e-mail, указанный пользователем. Содержание письма, а также e-mail, указываемый в обратном адресе можно редактировать в настройках модуля. Письмо не может быть отправлено, если не заполнено текстовое поле для ответа или e-mail получателя. Поле «Отправить ответ» появляется, если в конструкторе формы есть поле с типом «электронный ящик».',
			),
			'hr2' => 'hr',
			'admin_id' => array(
				'type' => 'function',
				'name' => 'Отвечающий',
				'help' => 'Пользователь, первый отредактировавший или создавший сообщение в административной части.',
			),
			'text' => array(
				'type' => 'editor',
				'name' => 'Ответ',
				'help' => 'Текстовое поле для ответа.',
			),
			'readed' => array(
				'type' => 'function',
				'name' => 'Помечает как прочитанное',
				'disabled' => true,
				'hide' => true,
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'created' => array(
			'name' => 'Дата и время',
			'type' => 'datetime',
			'sql' => true,
			'no_important' => true,
		),
		'param' => array(
			'type' => 'text',
			'class' => 'text',
			'no_important' => true,
		),
		'adapt' => array(
			'class_th' => 'item__th_adapt',
		),
		'separator' => array(
			'class_th' => 'item__th_seporator',
		),
		'actions' => array(
			'trash' => true,
		),
	);

	/**
	 * @var array поля для фильтра
	 */
	public $variables_filter = array (
		'user_id' => array(
			'type' => 'none',
		),
		'param' => array(
			'type' => 'function',
		),
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'element_site', // делит элементы по разделам (страницы сайта, к которым прикреплен модуль)
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
	}

	/**
	 * Выводит список обращений в обратную связь
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Выводит дополнительные поля в списке
	 *
	 * @return string
	 */
	public function list_variable_param($row)
	{
		if(! isset($this->cache["prepare"]["param"]))
		{
			$select = array();
			$checkbox = array();
			$rows = DB::query_fetch_all("SELECT e.element_id, e.value, e.param_id, p.type, p.[name] FROM"
				." {feedback_param_element} AS e"
				." INNER JOIN {feedback_param} AS p ON e.param_id=p.id"
				. " WHERE e.trash='0' AND e.element_id IN (%s)", implode(",", $this->diafan->rows_id));
			foreach ($rows as $r)
			{
				switch ($r["type"])
				{
					case 'select':
					case 'multiple':
					case 'radio':
						if(! in_array($r["value"], $select))
						{
							$select[] = $r["value"];
						}
						break;

					case 'checkbox':
						if(! in_array($r["param_id"], $checkbox))
						{
							$checkbox[] = $r["param_id"];
						}
						break;
				}
			}
			if($select)
			{
				$select_value = DB::query_fetch_key_value("SELECT id, [name] FROM {feedback_param_select} WHERE id IN (%s)", implode(",", $select), "id", "name");
			}
			if($checkbox)
			{
				$checkbox_value = DB::query_fetch_key_value("SELECT param_id, [name] FROM {feedback_param_select} WHERE param_id IN (%s)", implode(",", $checkbox), "param_id", "name");
			}
			foreach ($rows as $r)
			{
				if ($r["value"])
				{
					switch ($r["type"])
					{
						case 'select':
						case 'multiple':
						case 'radio':
							if(! empty($select_value[$r["value"]]))
							{
								$r["value"] = $select_value[$r["value"]];
							}
							break;
	
						case 'checkbox':
							$v = (! empty($checkbox_value[$r["param_id"]]) ? $checkbox_value[$r["param_id"]] : '');
							if ($v)
							{
								$r["value"] = $r["name"].': '.$v;
							}
							else
							{
								$r["value"] = $r["name"];
							}
							break;
					}
					$this->cache["prepare"]["param"][$r["element_id"]][] = $r["value"];
				}
			}
		}
		$text = '<div class="name"><a href="'.$this->diafan->get_base_link($row).'">';
		if(! empty($this->cache["prepare"]["param"][$row["id"]]))
		{
			$text .= implode(', ', $this->cache["prepare"]["param"][$row["id"]]);
		}
		$text .= '</a></div>';
		return $text;
	}

	public function show_table_tr_email($key, $name, $value, $help, $disabled = false)
	{
		echo '
		<div class="unit" id="'.$key.'">
			<div class="infofield">'.$name.'</div>
			<input type="email" name="'.$key.'" size="40" value="'.( ! $this->diafan->is_new ? str_replace('"', '&quot;', $value) : '' ).'"'.($disabled ? ' disabled' : '').'>
			'.$help.'
			<br>
			<input type="checkbox" value="1" name="'.$key.'send" id="input_'.$key.'send"> <label for="input_'.$key.'send">'.$this->diafan->_('Отправить ответ').'</label>
		</div>';
	}

	/**
	 * Редактирование поля "Страница, с которой отправлено сообщение"
	 * @return void
	 */
	public function edit_variable_url()
	{
		if(! $this->diafan->value)
			return;

		echo '
		<div class="unit" id="roles">
			<b>'.$this->diafan->variable_name().':</b>
			<a href="'.$this->diafan->value.'" target="_blank">'.$this->diafan->value.'</a>'.$this->diafan->help().'
		</div>';
	}

	/**
	 * Редактирование поля "Дополнительные параметры"
	 *
	 * @return void
	 */
	public function edit_variable_param()
	{
		parent::__call('edit_variable_param', array(" AND site_id IN (0, ".$this->diafan->values("site_id").")"));
	}

	/**
	 * Редактирование поля "Помечает как прочитанное"
	 *
	 * @return void
	 */
	public function edit_variable_readed()
	{
		if(! $this->diafan->value && ! $this->diafan->is_new)
		{
			DB::query("UPDATE {feedback} SET readed='1' WHERE id=%d", $this->diafan->id);
		}
	}

	/**
	 * Сохранение поля "Дополнительные параметры"
	 *
	 * @return void
	 */
	public function save_variable_param()
	{
		parent::__call('save_variable_param', array(" AND site_id IN (0, ".$this->diafan->values("site_id").")"));
	}

	/**
	 * Проверяет заполнен ли email, если отмечена кнопка "Отправить письмо"
	 * @return void
	 */
	public function validate_variable_sendmail()
	{
		$param_id = DB::query_result("SELECT id FROM {feedback_param} WHERE trash='0' AND site_id IN (0, %d) AND type='email' ORDER BY sort ASC LIMIT 1", $_POST["site_id"]);
		if(! $param_id || empty( $_POST['param'.$param_id.'send']))
		{
			return;
		}
		if(empty($_POST['param'.$param_id]))
		{
			$this->diafan->set_error('param'.$param_id, 'Введите e-mail, чтобы отправить письмо.');
		}
		if(empty($_POST["text"]))
		{
			$this->diafan->set_error('text', 'Введите текст ответа, чтобы отправить письмо.');
		}
		if(! $this->diafan->configmodules('subject', 'feedback', $_POST["site_id"], _LANG)
		|| ! $this->diafan->configmodules('message', 'feedback', $_POST["site_id"], _LANG))
		{
			$this->diafan->set_error('param'.$param_id, 'В настройках модуля необходимо указать тему и текст письма.');
		}
		if(! EMAIL_CONFIG && (! $this->diafan->configmodules("emailconf", 'feedback', $_POST["site_id"]) || ! $this->diafan->configmodules("email", 'feedback', $_POST["site_id"])))
		{
			$this->diafan->set_error('param'.$param_id, 'В настройках модуля или в параметрах сайта необходимо заполнить e-mail, указываемый в обратном адресе.');
		}
	}

	/**
	 * Отправляет письмо пользователю
	 * @return void
	 */
	public function save_variable_sendmail()
	{
		if(! empty($_POST["text"]))
		{
			$param_id = DB::query_result("SELECT id FROM {feedback_param} WHERE trash='0' AND site_id IN (0, %d) AND type='email' ORDER BY sort ASC LIMIT 1", $this->diafan->values("site_id"));
			if(empty( $_POST['param'.$param_id.'send']) || empty($_POST['param'.$param_id]))
			{
				return;
			}
			$email = $_POST['param'.$param_id];

			$message = $this->get_message();
			$subject = str_replace(array ( '%title', '%url' ), array ( TITLE, BASE_URL ), $this->diafan->configmodules('subject', 'feedback', $this->diafan->values("site_id")));

			$message = str_replace(array ( '%title', '%url', '%message', '%answer' ), array ( TITLE, BASE_URL, $message, $_POST["text"] ), $this->diafan->configmodules('message', 'feedback', $this->diafan->values("site_id")));
			
			$from = $this->diafan->configmodules("emailconf", 'feedback', $this->diafan->values("site_id"))
					&& $this->diafan->configmodules("email", 'feedback', $this->diafan->values("site_id"))
					? $this->diafan->configmodules("email", 'feedback', $this->diafan->values("site_id")) : EMAIL_CONFIG;

			$this->diafan->_postman->message_add_mail(
				$email,
				$subject,
				$message,
				$from
			);
			$this->diafan->err = 5;
		}
	}

	/**
	 * Формирует текст письма пользователю
	 * @return string
	 */
	private function get_message()
	{
		$rows_param = DB::query_fetch_all("SELECT id, [name], type FROM {feedback_param} WHERE site_id=%d"." AND trash='0' ORDER BY sort ASC", $this->diafan->values("site_id"));

		foreach ($rows_param as &$row)
		{
			if ($row["type"] == 'select' || $row["type"] == 'multiple' || $row["type"] == 'radio')
			{
				$row["select_array"] = DB::query_fetch_all("SELECT [name], id FROM {feedback_param_select} WHERE param_id=%d ORDER BY sort ASC", $row["id"]);
			}
		}

		$message = array ();

		foreach ($rows_param as &$row)
		{
			if (empty( $_POST["param".$row["id"]] ) && $row["type"] != "checkbox")
			{
				continue;
			}

			if ($row["type"] == "text" || $row["type"] == "textarea" || $row["type"] == "email")
			{
				$message[] = $row["name"].': '.nl2br(htmlspecialchars(htmlspecialchars($_POST["param".$row["id"]])));
			}
			elseif ($row["type"] == "numtext")
			{
				$message[] = $row["name"].': '.$this->diafan->filter($_POST, "int", "param".$row["id"]);
			}
			elseif ($row["type"] == "date")
			{
				if ($_POST["param".$row["id"]])
				{
					$message[] = $row["name"].': '.date('d.m.Y', $_POST["param".$row["id"]]);
				}
			}
			elseif ($row["type"] == "checkbox")
			{
				$value = ! empty( $_POST["param".$row["id"]] ) ? 1 : 0;
				$value_sel = DB::query_result("SELECT [name] FROM {feedback_param_select} WHERE value=%d AND param_id=%d LIMIT 1", $value, $row["id"]);
				if (!$value_sel && $value == 1)
				{
					$message[] = $row["name"];
				}
				elseif ($value_sel)
				{
					$message[] = $row["name"].': '.$value_sel;
				}
			}
			elseif (($row["type"] == "select"  || $row["type"] == "radio")  && ! empty($row["select_array"]))
			{
				foreach ($row["select_array"] as $select)
				{
					if ($select["id"] == $_POST["param".$row["id"]])
					{
						$message[] = $row["name"].': '.$select["name"];
					}
				}
			}
			elseif ($row["type"] == "multiple" && ! empty($row["select_array"]) && ! empty($_POST["param".$row["id"]]) && is_array($_POST["param".$row["id"]]))
			{
				$vals = array ();
				foreach ($row["select_array"] as $select)
				{
					if (in_array($select["id"], $_POST["param".$row["id"]]))
					{
						$vals[] = $select["name"];
					}
				}
				$message[] = $row["name"].': '.implode(", ", $vals);
			}
		}
		return implode('<br>', $message);
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("feedback_param_element", "element_id IN (".implode(",", $del_ids).")");
	}
}
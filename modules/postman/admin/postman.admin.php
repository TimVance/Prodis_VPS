<?php
/**
 * Редактирование почтовых отправлений
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
 * Postman_admin
 */
class Postman_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'postman';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'type' => array(
				'type' => 'select',
				'name' => 'Тип уведомления',
				'select' => array(
					"mail" => "письмо",
					"sms" => "SMS",
				),
			),
			'master_id' => array(
				'type' => 'datetime',
				'name' => 'Дата и время',
				'no_save' => true,
				'disabled' => true,
				'help' => 'Дата и время создания уведомления.',
			),
			'status' => array(
				'type' => 'select',
				'name' => 'Статус отправки уведомления',
				'select' => array(
					"0" => '<span class="orange_text" title="Уведомление ожидает инициализации отправки">Подготовлено</span>',
					"1" => '<span class="green_text" title="Уведомление отправлено">Отправлено</span>',
					"2" => '<span class="red_text" title="Зарегистрирована ошибка при отправлении уведомления">Ошибка</span>',
				),
				'help' => 'Состояние и время отправки уведомления.',
				'no_save' => true,
				'disabled' => true,
			),
			'recipient' => array(
				'type' => 'text',
				'name' => 'Адрес получателя',
				'help' => 'Для почтового уведомления необходимо указать адрес или адреса и через пробел получателя. Для SMS-уведомления – адрес получателя.',
			),
			'subject' => array(
				'type' => 'text',
				'name' => 'Тема уведомления',
				'help' => 'Содержание заголовка почтового уведомления. Для SMS-уведомления не указывается.',
				'depend' => 'type=mail',
			),
			'body' => array(
				'type' => 'function',
				'name' => 'Содержание письма',
				'help' => 'Содержание уведомления, направляемое адресату.',
			),
			'from' => array(
				'type' => 'text',
				'name' => 'Адрес отправителя',
				'help' => 'Указывается для почтового уведомления адрес, от имени которого будет направлено письмо. Для SMS-уведомления не указывается.',
				'depend' => 'type=mail',
			),
			'error' => array(
				'type' => 'text',
				'name' => 'Отчет об ошибке отправления',
				'no_save' => true,
				'disabled' => true,
				'help' => 'Содержит сведения об ошибке при отправлении.',
			),
			'trace' => array(
				'type' => 'textarea',
				'name' => 'Трассировка отправления',
				'no_save' => true,
				'disabled' => true,
				'height' => 250,
				'help' => 'Содержит сведения о трассировке отправления.',
			),
			'timesent' => array(
				'type' => 'none',
				'disabled' => true,
			),
			'auto' => array(
				'type' => 'none',
			),
		),
		'other_rows' => array (
			'send' => array(
				'type' => 'function',
				'name' => 'Отправить уведомление',
				'help' => 'Если отмечена, уведомление будет отправлено адресату.',
			),
			'timeedit' => array(
				'type' => 'text',
				'name' => 'Время последнего изменения',
				'help' => 'Изменяется после сохранения элемента.',
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'master_id' => array(
			'type' => 'datetime',
			'name' => 'Дата и время',
			'sql' => true,
			'help' => 'Время создания уведомления.',
		),
		'name' => array(
			'type' => 'select',
			'name' => 'тип',
			'variable' => 'type',
			'class' => 'postman',
			'select' => array(
				"mail" => "письмо",
				"sms" => "SMS",
			),
		),
		'recipient' => array(
			'type' => 'text',
			'sql' => true,
			'name' => 'получатель',
		),
		'adapt' => array(
			'class_th' => 'item__th_adapt',
		),
		'separator' => array(
			'class_th' => 'item__th_seporator',
		),
		'status' => array(
			'type' => 'select',
			'name' => 'статус',
			'sql' => true,
			'select' => array(
				"0" => '<span class="orange_text" title="Уведомление ожидает инициализации отправки">Подготовлено</span>',
				"1" => '<span class="green_text" title="Уведомление отправлено">Отправлено</span>',
				"2" => '<span class="red_text" title="Зарегистрирована ошибка при отправлении уведомления">Ошибка</span>',
			),
			'help' => 'Состояние и время отправки уведомления.',
			'no_important' => true,
		),
		'subject' => array(
			'type' => 'text',
			'name' => 'тема / содержание',
			'sql' => true,
			'class' => 'text',
			'help' => 'Содержание уведомления для пустой темы или SMS-уведомления. В иных случаях - тема уведомления.',
			'no_important' => true,
		),
		'from' => array(
			'type' => 'text',
			'name' => 'отправитель',
			'sql' => true,
		),
		'body' => array(
			'sql' => true,
			'type' => 'none',
		),
		'timesent' => array(
			'sql' => true,
			'type' => 'none',
		),
		'actions' => array(
			'view' => true,
			'send' => true,
			'del' => true,
		),
	);

	/**
	 * @var array дополнительные групповые операции
	 */
	public $group_action = array(
		"group_send" => array(
			'name' => "Отправить",
			'module' => 'postman',
		),
		"delete" => array(
			'name' => "Удалить",
			'confirm' => "Внимание! Уведомления будут безвозвратно удалены без возможности восстановления.\n\r\n\rПродолжить?",
		),
	);

	/**
	 * @var array поля для фильтра
	 */
	public $variables_filter = array (
		'mail' => array(
			'type' => 'checkbox',
			'name' => 'Все письма',
		),
		'sms' => array(
			'type' => 'checkbox',
			'name' => 'Все SMS',
		),
		'hr1' => array(
			'type' => 'hr',
		),
		'no_send' => array(
			'type' => 'checkbox',
			'name' => 'Все <span class="orange_text" title="Уведомления ожидающие инициализации отправки">подготовленные</span>',
		),
		'error_send' => array(
			'type' => 'checkbox',
			'name' => 'Все <span class="red_text" title="Зарегистрированные ошибки при отправлении уведомлений">ошибки</span> отправлений',
		),
		'send' => array(
			'type' => 'checkbox',
			'name' => 'Все <span class="green_text" title="Отправленные уведомления">отправленные</span>',
		),
		'hr2' => array(
			'type' => 'hr',
		),
		'recipient' => array(
			'type' => 'text',
			'name' => 'Искать по адресату',
		),
		'from' => array(
			'type' => 'multiselect',
			'name' => 'Искать по отправителю',
			'select' => array(),
		),
		'subject' => array(
			'type' => 'text',
			'name' => 'Искать по теме',
		),
		'body' => array(
			'type' => 'text',
			'name' => 'Искать по содержанию',
		),
	);

	/**
	 * @var string информационное сообщение
	 */
	private $important_title = '';

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		// TO_DO: инициируем переопределенную обработку переменных из запроса $_GET[rewrite]
		$this->prepare_rewrite();

		// определение значений фильтра
		$this->variables_filter["from"]["select"] = array();
		$rows = DB::query_fetch_all("SELECT `from` AS id, `from` AS name FROM {%s} WHERE `from`<>'' GROUP BY `from`", $this->diafan->table);
		foreach($rows as $row)
		{
			$this->variables_filter["from"]["select"][$row["id"]] = $row["name"];
		}
	}

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить уведомление', 'fa-cart-plus');
	}

	/**
	 * Выводит контент модуля
	 * @return void
	 */
	public function show()
	{
		if(_LANG != $this->diafan->_languages->admin)
		{
			$this->diafan->redirect(BASE_PATH.ADMIN_FOLDER.'/postman/');
		}
		if(IS_DEMO)
		{
			echo '<div class="error">'.$this->diafan->_('не доступно в демонстрационном режиме').'</div>';
		}
		else
		{
			// определение информационного сообщения
			if(! $this->diafan->configmodules("smtp_mail", 'postman'))
			{
				$this->important_title =
					'<div class="head-box head-box_warning">
						<i class="fa fa-warning"></i>'
						.$this->diafan->_('Для корректной отправки писем важно, чтобы в настройках модуля был указан тот же почтовый ящик администратора, который является отправителем всех уведомлений в настройках других модулей. А также очень важно использовать SMTP-авторизацию исходящей почты.')
						.' '
						.$this->diafan->_("Настроить %sSMTP-авторизацию%s", '<a href="'.BASE_PATH_HREF.'postman/config/" target="_blank">', '</a>')
						.' '
						.'('.$this->diafan->_('параметры настроек необходимо уточнять у администраторов используемого почтового сервера').').'
						.$this->help('Вкладка «Почта», пункт «Использовать SMTP-авторизацию при отправке почты с сайта».')
					.'</div>';
			}

			echo $this->important_title;

			echo '<span class="shop_stat">';

			$typestat = "type='%h' AND";
			$statmailnosend = 0;
			
			$stat = DB::query_fetch_key_value("SELECT COUNT(*) AS c, CONCAT(type, status) AS k FROM {postman} WHERE status IN ('0', '2') GROUP BY type, status", "k", "c");

			echo $this->diafan->_('Всего <b>писем</b> / <b>SMS</b> ожидает отправки: <b>%s</b> / <b>%s</b>', (! empty($stat["mail0"]) ? $stat["mail0"] : 0), (! empty($stat["sms0"]) ? $stat["sms0"] : 0));

			echo (! empty($stat["mail2"]) || ! empty($stat["sms2"])? $this->diafan->_(', ошибки отправлений: <b>%s</b> / <b>%s</b>', (! empty($stat["mail2"]) ? $stat["mail2"] : 0), (! empty($stat["sms2"]) ? $stat["sms2"] : 0)) : '');

			echo '</span>';

			$this->diafan->list_row();
		}
	}

	/**
	 * Выводит панель групповых операций
	 *
	 * @param boolean $show_filter выводить кнопку "Фильтровать"
	 * @return void
	 */
	public function group_action_panel($show_filter = false)
	{
		$del = $this->diafan->variable_list('actions', 'del');
		$this->diafan->variable_list('actions', 'del', false);
		echo parent::group_action_panel($show_filter);
		$this->diafan->variable_list('actions', 'del', $del);
	}

	/**
	 * Формирует поле "Получатель" в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_recipient($row, $var)
	{
		$html = '<div class="'.($var["class"] ? ' '.$var["class"] : '').'">';
		$html .= (! empty($row["recipient"]) ? $this->diafan->short_text($row["recipient"]) : $this->diafan->_('<span class="brown_text" title="Не указан получатель">не определено</span>'));
		$html .= '</div>';
		return $html;
	}

	/**
	 * Формирует поле "Статус" в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_status($row, $var)
	{
		$html = '<div class="'.($var["class"] ? ' '.$var["class"] : '').'">';
		$html .= $this->diafan->_($var["select"][$row["status"]]);
		if($row["timesent"] != 0)
		{
			$html .= '<br>'.date("d.m.Y H:i", $row["timesent"]);
		}
		$html .= '</div>';
		return $html;
	}

	/**
	 * Формирует поле "Тема уведомления" в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_subject($row, $var)
	{
		$html = '<div class="'.($var["class"] ? ' '.$var["class"] : '').'">';
		switch($row["type"])
		{
			case 'sms':
				$html .= (! empty($row["body"]) ? $this->diafan->short_text($row["body"]) : '&nbsp;');
				break;

			case 'mail':
			default:
				$html .= (! empty($row["subject"]) ? $this->diafan->short_text($row["subject"]) : (! empty($row["body"]) ? $this->diafan->short_text($row["body"]) : '&nbsp;'));
				break;
		}
		$html .= '</div>';
		return $html;
	}

	/**
	 * Формирует поле "Отправитель" в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_from($row, $var)
	{
		$html = '<div class="'.($var["class"] ? ' '.$var["class"] : '').'">';
		switch($row["type"])
		{
			case 'sms':
				if(empty($row["from"]))
				{
					$html .= $this->diafan->_('<span class="brown_text" title="Не указан провайдер услуг">не определено</span>');
				}
				else
				{
					$html .= $row["from"];
				}
				break;

			case 'mail':
				if(empty($row["from"]))
				{
					$html .= $this->diafan->_('<span class="brown_text" title="Не указан отправитель">не определено</span>');
				}
				else $html .= $row["from"];
				break;

			default:
				$html .= (! empty($row["from"]) ? $this->diafan->short_text($row["from"]) : '&nbsp;');
				break;
		}
		$html .= '</div>';
		return $html;
	}

	/**
	 * Выводит кнопки действий над элементом
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_actions($row, $var)
	{
		$text = '<div class="item__unit">';

		//view
		if ($this->diafan->variable_list('actions', 'view'))
		{
			$text .= '<a href="'.$this->diafan->get_base_link($row).'" class="item__ui view" title="'.$this->diafan->_('Посмотреть на странице').'" target="_blank">
				<i class="fa fa-laptop"></i>
			</a>';
		}

		// send
		if ($view = $this->diafan->variable_list('actions', 'send')/* && (in_array($row["status"], array('0', '2'), true))*/)
		{
			$text .= '<a href="javascript:void(0)" title="'.$this->diafan->_('Отправить уведомление').'" action="send" module="postman" class="action item__ui send">
				<i class="fa fa-envelope"></i>
			</a>';
		}

		//del
		if ($this->diafan->variable_list('actions', 'del')
			&& $this->diafan->_users->roles('del', $this->diafan->_admin->rewrite)
			&& $this->diafan->check_action($row, 'del'))
		{
			$text .= '
			<a href="javascript:void(0)" title="'.$this->diafan->_('Удалить').'"'.' confirm="'
			.(!empty( $row["count_children"] ) ? $this->diafan->_('ВНИМАНИЕ! Пункт содержит вложенность! ') : '')
			.($this->diafan->config("category") ? $this->diafan->_('При удалении категории удаляются все принадлежащие ей элементы. ') : '')
			.$this->diafan->_('Внимание! Уведомления будут безвозвратно удалены без возможности восстановления.\n\r\n\rПродолжить?')
			. '" action="delete" class="action item__ui remove">
				<i class="fa fa-times-circle"></i>
			</a>';
		}

		$text .= '</div>';

		return $text;
	}

	/**
	 * Поиск по полю "Все письма"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function save_filter_variable_mail($row)
	{
		if (empty($_GET["filter_mail"]) || ! empty($_GET["filter_sms"]))
		{
			if(! empty($_GET["filter_mail"]) && ! empty($_GET["filter_sms"])) return 1;
			return;
		}

		$this->diafan->where .= " AND e.type='mail'";
		$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' ).'filter_mail=1';
		return 1;
	}

	/**
	 * Поиск по полю "Все SMS"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function save_filter_variable_sms($row)
	{
		if (empty($_GET["filter_sms"]) || ! empty($_GET["filter_mail"]))
		{
			if(! empty($_GET["filter_sms"]) && ! empty($_GET["filter_mail"])) return 1;
			return;
		}

		$this->diafan->where .= " AND e.type='sms'";
		$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' ).'filter_sms=1';
		return 1;
	}

	/**
	 * Поиск по полю "Все подготовленные"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function save_filter_variable_no_send($row)
	{
		if (empty($_GET["filter_no_send"]))
		{
			return;
		}

		$this->save_filter_variable_status($row);
		return 1;
	}

	/**
	 * Поиск по полю "Все ошибки"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function save_filter_variable_error_send($row)
	{
		if (empty($_GET["filter_error_send"]))
		{
			return;
		}

		$this->save_filter_variable_status($row);
		return 1;
	}

	/**
	 * Поиск по полю "Все отправленные"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function save_filter_variable_send($row)
	{
		if (empty($_GET["filter_send"]))
		{
			return;
		}

		$this->save_filter_variable_status($row);
		return 1;
	}

	/**
	 * Формирует запрос для поиска по полю "Статус"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	private function save_filter_variable_status($row)
	{
		if(isset($this->cache["prepare"]["save_filter_variable_status"]))
		{
			return;
		}
		else $this->cache["prepare"]["save_filter_variable_status"] = true;

		if (! empty($_GET["filter_no_send"]) && ! empty($_GET["filter_error_send"]) && ! empty($_GET["filter_send"]))
		{
			return 1;
		}

		if (empty($_GET["filter_no_send"]) && empty($_GET["filter_error_send"]) && empty($_GET["filter_send"]))
		{
			return;
		}

		$where = '';
		if (! empty($_GET["filter_no_send"]))
		{
			$where .= ",'0'";
			$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' ).'filter_no_send=1';
		}
		if (! empty($_GET["filter_error_send"]))
		{
			$where .= ",'2'";
			$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' ).'filter_error_send=1';
		}
		if (! empty($_GET["filter_send"]))
		{
			$where .= ",'1'";
			$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' ).'filter_send=1';
		}
		$this->diafan->where .= " AND e.status IN (".substr($where, 1).")";
		return 1;
	}

	/**
	 * Генерирует форму редактирования/добавления элемента
	 *
	 * @return void
	 */
	public function edit()
	{
		if(_LANG != $this->diafan->_languages->admin)
		{
			$this->diafan->redirect(BASE_PATH.ADMIN_FOLDER.'/postman/');
		}
		if(IS_DEMO)
		{
			echo '<div class="error">'.$this->diafan->_('не доступно в демонстрационном режиме').'</div>';
		}
		else
		{
			// определение информационного сообщения
			if(! $this->diafan->configmodules("smtp_mail", 'postman'))
			{
				$this->important_title =
					'<div class="head-box head-box_warning">
						<i class="fa fa-warning"></i>'
						.$this->diafan->_('Для корректной отправки писем важно, чтобы в настройках модуля был указан тот же почтовый ящик администратора, который является отправителем всех уведомлений в настройках других модулей. А также очень важно использовать SMTP-авторизацию исходящей почты.')
						.' '
						.$this->diafan->_("Настроить %sSMTP-авторизацию%s", '<a href="'.BASE_PATH_HREF.'postman/config/" target="_blank">', '</a>')
						.' '
						.'('.$this->diafan->_('параметры настроек необходимо уточнять у администраторов используемого почтового сервера').').'
						.$this->help('Вкладка «Почта», пункт «Использовать SMTP-авторизацию при отправке почты с сайта».')
					.'</div>';
			}

			echo $this->important_title;

			echo parent::edit();
		}
	}

	/**
	 * Редактирование поля "Дата и время"
	 * @return void
	 */
	public function edit_variable_master_id()
	{
		if ($this->diafan->is_new)
		{
			return;
		}

		$type = $this->diafan->variable('', 'type');
		$this->diafan->show_table_tr(
						$type,
						$this->diafan->key,
						$this->diafan->value,
						$this->diafan->variable_name(),
						$this->diafan->help(),
						$this->diafan->variable_disabled(),
						$this->diafan->variable('', 'maxlength'),
						$this->diafan->variable('', 'select'),
						$this->diafan->variable('', 'select_db'),
						$this->diafan->variable('', 'depend'),
						$this->diafan->variable('', 'attr')
					);
	}

	/**
	 * Редактирование поля "Cтатус отправки уведомления"
	 * @return void
	 */
	public function edit_variable_status()
	{
		if ($this->diafan->is_new)
		{
			return;
		}

		$class = $attr = '';
		$depend = $this->diafan->variable('', 'depend');
		if($depend)
		{
			$attr = ' depend="'.$depend.'"';
			$class = "depend_field";
		}
		$key = $this->diafan->key;
		$name = $this->diafan->variable_name();
		$select = $this->diafan->variable('', 'select');
		$value = ! empty($select[$this->diafan->value]) ? $select[$this->diafan->value] : '';
		$help = $this->diafan->help();
		$disabled = $this->diafan->variable_disabled();
		$maxlength = $this->diafan->variable('', 'maxlength');

		$timesent = '';
		if($this->diafan->is_variable('timesent'))
		{
			$timesent = $this->diafan->values('timesent');
			if($timesent != 0)
			{
				$timesent = ' '.date("d.m.Y H:i", $timesent);
			}
			else $timesent = '';
		}

		echo '
		<div class="unit'.($class ? ' '.$class : '').'" id="'.$key.'"'.$attr.'>
			<div class="infofield">'.$name.$help.'</div>';
			echo '<div name="'.$key.'"'.($disabled ? ' disabled' : '').($maxlength ? ' maxlength="'.$maxlength.'"' : '').' class="text">'
				.$value
				.$timesent;
			echo '</div>';
		echo '
		</div>';
	}

	/**
	 * Редактирование поля "Содержание письма"
	 * @return void
	 */
	public function edit_variable_body()
	{
		$depend = $this->diafan->variable('', 'depend');
		$this->diafan->show_table_tr(
						'editor',
						$this->diafan->key.'_mail',
						$this->diafan->value,
						$this->diafan->variable_name(),
						$this->diafan->help(),
						$this->diafan->variable_disabled(),
						$this->diafan->variable('', 'maxlength'),
						$this->diafan->variable('', 'select'),
						$this->diafan->variable('', 'select_db'),
						$depend.($depend ? ',' : '').'type=mail',
						$this->diafan->variable('', 'attr')
					);
		$this->diafan->show_table_tr(
						'textarea',
						$this->diafan->key.'_sms',
						strip_tags($this->diafan->value),
						$this->diafan->variable_name(),
						$this->diafan->help(),
						$this->diafan->variable_disabled(),
						$this->diafan->variable('', 'maxlength'),
						$this->diafan->variable('', 'select'),
						$this->diafan->variable('', 'select_db'),
						$depend.($depend ? ',' : '').'type=sms',
						$this->diafan->variable('', 'attr')
					);
	}

	/**
	 * Редактирование поля "Отчет об ошибке отправления"
	 * @return void
	 */
	public function edit_variable_error()
	{
		if ($this->diafan->is_new || empty($this->diafan->value))
		{
			return;
		}

		if (MOD_DEVELOPER && (! MOD_DEVELOPER_ADMIN || ! empty($_COOKIE['dev'])))
		{
			$class = $attr = '';
			$depend = $this->diafan->variable('', 'depend');
			if($depend)
			{
				$attr = ' depend="'.$depend.'"';
				$class = "depend_field";
			}
			$key = $this->diafan->key;
			$name = $this->diafan->variable_name();
			$value = ! empty($this->diafan->value) ? '<span class="red_text" title="Зарегистрирована ошибка при отправлении уведомления">'.$this->diafan->value.'</span>' : '';
			$help = $this->diafan->help();
			$disabled = $this->diafan->variable_disabled();
			$maxlength = $this->diafan->variable('', 'maxlength');

			echo '
			<div class="unit'.($class ? ' '.$class : '').'" id="'.$key.'"'.$attr.'>
				<div class="infofield">'.$name.$help.'</div>';
				echo '<div name="'.$key.'"'.($disabled ? ' disabled' : '').($maxlength ? ' maxlength="'.$maxlength.'"' : '').' class="text">'
					.$value;
				echo '</div>';
			echo '
			</div>';
		}
	}

	/**
	 * Редактирование поля "Трассировка отправления"
	 * @return void
	 */
	public function edit_variable_trace()
	{
		if ($this->diafan->is_new || empty($this->diafan->value))
		{
			return;
		}
		$error = $this->diafan->values('error');
		if (empty($error))
		{
			return;
		}

		if (MOD_DEVELOPER && (! MOD_DEVELOPER_ADMIN || ! empty($_COOKIE['dev'])))
		{
			$type = $this->diafan->variable('', 'type');
			$this->diafan->show_table_tr(
							$type,
							$this->diafan->key,
							$this->diafan->value,
							$this->diafan->variable_name(),
							$this->diafan->help(),
							$this->diafan->variable_disabled(),
							$this->diafan->variable('', 'maxlength'),
							$this->diafan->variable('', 'select'),
							$this->diafan->variable('', 'select_db'),
							$this->diafan->variable('', 'depend'),
							$this->diafan->variable('', 'attr')
						);
		}
	}

	/**
	 * Редактирование поля "Отправить уведомление"
	 *
	 * @return void
	 */
	public function edit_variable_send()
	{
		$this->diafan->value = false;
		if($this->diafan->value === false)
		{
			$this->diafan->value = '';
		}

		$this->diafan->show_table_tr(
				'checkbox',
				$this->diafan->key,
				$this->diafan->value,
				$this->diafan->variable_name(),
				$this->diafan->help(),
				$this->diafan->variable_disabled(),
				$this->diafan->variable('', 'maxlength'),
				$this->diafan->variable('', 'select'),
				$this->diafan->variable('', 'select_db'),
				$this->diafan->variable('', 'depend'),
				$this->diafan->variable('', 'attr')
			);
	}

	/**
	 * Проверка поля "Отправить уведомление"
	 *
	 * @return void
	 */
	public function validate_variable_send()
	{
		if(! empty($_POST["send"]))
		{
			$error = false;
			if(! $error && empty($_POST["recipient"]))
			{
				$error = $this->diafan->_("Для отправки уведомления необходимо указать адрес получателя.");
			}
			if(! $error && ! empty($_POST["type"]) && $_POST["type"] == 'sms' && ! $this->diafan->configmodules("sms", 'postman'))
			{
				$error = $this->diafan->_("Для отправки уведомления необходимо %sнастроить%s SMS-уведомления.", '<a href="'.BASE_PATH_HREF.'config/">', '</a>');
			}
			if($error)
			{
				$this->diafan->set_error("recipient", $error);
			}
		}
	}

	/**
	 * Сохранение поля "Адрес отправителя"
	 * @return void
	 */
	public function save_variable_from()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->diafan->redirect(URL);
			return;
		}

		//проверка прав пользователя на активацию/блокирование
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->diafan->redirect(URL);
			return;
		}

		if(! empty($_POST["type"]))
		{
			$name = '`from`';
			$key = 'from';
			if($_POST["type"] == 'mail')
			{
				$this->diafan->set_query($name."='%h'");
				$this->diafan->set_value(strip_tags($_POST[$key]));
			}
			elseif($_POST["type"] == 'sms')
			{
				$this->diafan->set_query($name."='%h'");
				$this->diafan->set_value($this->diafan->configmodules("sms_provider", 'postman'));
			}
		}
	}

	/**
	 * Сохранение поля "Содержание письма"
	 * @return void
	 */
	public function save_variable_body()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->diafan->redirect(URL);
			return;
		}

		//проверка прав пользователя на активацию/блокирование
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->diafan->redirect(URL);
			return;
		}

		if(! empty($_POST["type"]))
		{
			$name = 'body';
			$key = $name.'_'.$_POST["type"];
			if($_POST["type"] == 'mail')
			{
				$this->diafan->set_query($name."='%s'");
				$this->diafan->set_value($this->diafan->save_field_editor($key));
			}
			elseif($_POST["type"] == 'sms')
			{
				$this->diafan->set_query($name."='%h'");
				$this->diafan->set_value(isset($_POST[$key]) ? strip_tags($_POST[$key]) : '');
			}
		}
	}

	/**
	 * Сохранение поля "Метод отправки уведомления"
	 * @return void
	 */
	public function save_variable_auto()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->diafan->redirect(URL);
			return;
		}

		//проверка прав пользователя на активацию/блокирование
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->diafan->redirect(URL);
			return;
		}

		if ($this->diafan->is_new)
		{
			$this->diafan->set_query("auto"."='%h'");
			$this->diafan->set_value('0');
		}
	}

	/**
	 * Сохранение поля "Отправить уведомление"
	 * @return void
	 */
	public function save_variable_send()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->diafan->redirect(URL);
			return;
		}

		//проверка прав пользователя на активацию/блокирование
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->diafan->redirect(URL);
			return;
		}
	}

	/**
	 * Сопутствующие действия при сохранении элемента модуля
	 * @return void
	 */
	public function save_variable($id)
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->diafan->redirect(URL);
			return;
		}

		//проверка прав пользователя на активацию/блокирование
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->diafan->redirect(URL);
			return;
		}

		if(! empty($_POST["send"]) && ! empty($this->diafan->id))
		{
			if($this->diafan->_postman->message_send($this->diafan->id))
			{
				$this->diafan->err = 5;
			}
		}
	}


	/**
	 * Удаляет элемент
	 *
	 * @return void
	 */
	public function del()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->diafan->redirect(URL);
			return;
		}

		//проверка прав пользователя на удаление
		if (! $this->diafan->_users->roles('del', $this->diafan->_admin->rewrite))
		{
			$this->diafan->redirect(URL);
			return;
		}

		if (! empty($_POST["id"]))
		{
			$ids = array($_POST["id"]);
		}
		else
		{
			$ids = $_POST["ids"];
		}
		foreach($ids as $id)
		{
			$id = $this->diafan->_db_ex->filter_uid($id);
			if($id)
			{
				$del_ids[] = $id;
			}
		}
		if(! empty($del_ids))
		{
			$this->diafan->_db_ex->delete('{'.$this->diafan->table.'}', $del_ids);
		}

		$this->diafan->redirect(URL.$this->diafan->get_nav);
	}


	/* TO_DO: далее перегружаем дефолтные функции - изменение правила для корректной обработки специальных идентификаторов */


	/**
	 * Подготавливает запрос для идентификации страницы в таблице {site} по rewrite или по id,
	 * удаляет из строки запроса $_GET[rewrite] переданные переменные
	 *
	 * @return void
	 */
	private function prepare_rewrite()
	{
		if ($_GET["rewrite"])
		{
			$rewrite_array = explode("/", $_GET["rewrite"]);

			foreach ($rewrite_array as $key => $ra)
			{

				foreach ($this->diafan->_route->variable_names_admin as $name)
				{
					//if (preg_match('/'.$name.'([0-9]+)/', $ra, $result))
					// TO_DO: изменение правила для корректной обработки специальных идентификаторов
					if (preg_match('/'.$name.'([0-9-]+)/', $ra, $result))
					{
						$this->diafan->_route->$name = $result[1];
						unset( $rewrite_array[$key] );
					}
				}
			}
			$this->diafan->_admin->rewrite = implode("/", $rewrite_array);
		}
		if (! $this->diafan->_admin->rewrite)
		{
			if($this->diafan->_users->start_admin && $this->diafan->_users->roles('init', $this->diafan->_users->start_admin))
			{
				$this->diafan->_admin->rewrite = $this->diafan->_users->start_admin;
			}
			elseif ($this->diafan->_users->roles('init', 'dashboard') || !$this->diafan->_users->id)
			{
				$this->diafan->_admin->rewrite = 'dashboard';
			}
			else
			{
				$rows = DB::query_fetch_all("SELECT id, rewrite FROM {admin} WHERE act='1' ORDER BY id DESC");
				foreach ($rows as $row)
				{
					if ($this->diafan->_users->roles('init', $row["rewrite"]))
					{
						$this->diafan->_admin->rewrite = $row["rewrite"];
						break;
					}
				}
			}
		}
	}

	/**
	 * Получает значение поля
	 * @param string $field название поля
	 * @param mixed $default значение по умолчанию
	 * @param boolean $save записать значение по умолчанию
	 * @return mixed
	 */
	public function values($field, $default = false, $save = false)
	{
		if($this->diafan->is_action("edit"))
		{
			return $this->edite_values($field, $default, $save);
		}
		elseif($this->diafan->is_action("save"))
		{
			return $this->save_values($field, $default, $save);
		}
		else return false;
	}

	/**
	 * Получает значение поля
	 * @param string $field название поля
	 * @param mixed $default значение по умолчанию
	 * @param boolean $save записать значение по умолчанию
	 * @return mixed
	 */
	private function edite_values($field, $default = false, $save = false)
	{
		if(! isset($this->cache["oldrow"]))
		{
			$values = $this->diafan->get_values();

			if ($this->diafan->config("config"))
			{
				foreach ($this->diafan->variables as $title => $variable_table)
				{
					foreach ($variable_table as $k => $v)
					{
						if ( empty($values[$k]))
						{
							$values[$k] = $this->diafan->configmodules($k);
						}
					}
				}
			}
			elseif($this->diafan->is_new)
			{
				foreach ($this->diafan->variables as $title => $variable_table)
				{
					foreach ($variable_table as $k => $v)
					{
						if (! empty($this->diafan->get_nav_params['filter_'.$k]) && $this->diafan->variable_filter($k) != 'text')
						{
							$values[$k.(! empty($v["multilang"]) ? _LANG : '')] = $this->diafan->get_nav_params['filter_'.$k];
						}
						elseif(! empty($v["default"]))
						{
							$values[$k._LANG] = $v["default"];
						}
					}
				}
			}
			elseif (! $values)
			{
				//$values = DB::query_fetch_array("SELECT * FROM {".$this->diafan->table."} WHERE id=%d"
				// TO_DO: изменение правила для корректной обработки специальных идентификаторов
				$values = DB::query_fetch_array("SELECT * FROM {".$this->diafan->table."} WHERE id='%h'"
					.($this->diafan->variable_list('actions', 'trash') ? " AND trash='0'" : '' )." LIMIT 1",
					$this->diafan->id
				);
				if (empty($values))
				{
					ob_end_clean();
					Custom::inc('includes/404.php');
				}
			}
			$this->cache["oldrow"] = $values;
		}

		$field .= ($this->diafan->variable_multilang($field) && ! $this->diafan->config("config") ? _LANG : '');

		if(! isset($this->cache["oldrow"][$field]))
		{
			switch($field)
			{
				case 'parent_id':
					if ($this->diafan->is_new)
					{
						$this->cache["oldrow"]["parent_id"] = $this->diafan->_route->parent;
					}
					break;

				case 'cat_id':
					if ($this->diafan->is_new)
					{
						$this->cache["oldrow"]["cat_id"] = $this->diafan->_route->cat;
					}
					break;

				case 'site_id':
					if($this->diafan->table == 'site')
					{
						$this->cache["oldrow"]["site_id"] = $this->diafan->id;
					}
					else
					{
						if(empty($this->cache["oldrow"]["site_id"]))
						{
							$this->cache["oldrow"]["site_id"] = $this->diafan->_route->site;
						}
						if(empty($this->cache["oldrow"]["site_id"]))
						{
							$this->cache["oldrow"]["site_id"] = DB::query_result("SELECT id FROM {site} WHERE module_name='%s' AND trash='0'", $this->diafan->_admin->module);
						}
					}
					break;
			}
		}
		if(! isset($this->cache["oldrow"][$field]))
		{
			if(! $default)
			{
				$default = $this->diafan->variable($field, 'default');
			}
			if($default)
			{
				if($save)
				{
					$this->cache["oldrow"][$field] = $default;
				}
				else
				{
					return $default;
				}
			}
			elseif ($this->diafan->config("config"))
			{
				$this->cache["oldrow"][$field] = $this->diafan->configmodules($field);
			}
		}
		if(isset($this->cache["oldrow"][$field]))
		{
			return $this->cache["oldrow"][$field];
		}
		else
		{
			return false;
		}
		return $this->cache["site_id"];
	}

	/**
	 * Получает старое значение поля
	 * @param string $field название поля
	 * @param mixed $default значение по умолчанию
	 * @param boolean $save записать значение по умолчанию
	 * @return mixed
	 */
	private function save_values($field, $default = false, $save = false)
	{
		if(! isset($this->cache["oldrow"]))
		{
			if($this->diafan->is_new)
			{
				$this->cache["oldrow"] = array();
			}
			else
			{
				if (! $this->diafan->config("config"))
				{
					$this->cache["oldrow"] = DB::query_fetch_array(
						//"SELECT * FROM {".$this->diafan->table."} WHERE id = %d"
						// TO_DO: изменение правила для корректной обработки специальных идентификаторов
						"SELECT * FROM {".$this->diafan->table."} WHERE id = '%h'"
						.($this->diafan->variable_list('actions', 'trash') ? " AND trash='0'" : '')
						." LIMIT 1", $this->diafan->id);
					if (! $this->cache["oldrow"])
					{
						Custom::inc('includes/404.php');
					}
				}
			}
		}

		$field .= $this->diafan->variable_multilang($field) ? _LANG : '';

		if($default && empty($this->cache["oldrow"][$field]))
		{
			return $default;
		}
		if(! isset($this->cache["oldrow"][$field]))
		{
			return false;
		}
		else
		{
			return $this->cache["oldrow"][$field];
		}
	}

	/**
	 * Сохраняет изменения
	 *
	 * @return void
	 */
	public function save()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (!$this->diafan->_users->checked)
		{
			$this->diafan->redirect(URL);
			return;
		}

		// Проверка прав на сохранение
		if (! $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			$this->diafan->redirect(URL);
			return;
		}

		if(empty($_POST["id"]) && ! $this->diafan->config('only_edit') && ! $this->diafan->config('config'))
		{
			$this->diafan->save_new();
			$this->diafan->is_new = true;
		}
		else
		{
			//$this->diafan->id = $this->diafan->filter($_POST, "int", "id");
			// TO_DO: изменение правила для корректной обработки специальных идентификаторов
			$this->diafan->id = $this->diafan->_db_ex->filter_uid($this->diafan->filter($_POST, "string", "id"));
			$this->diafan->values("id");
		}

		// Если отмечена галочка "Видеть только свои материалы", то редактирование чужих материалов запрещено
		if($this->diafan->is_variable("admin_id")
		   && $this->diafan->values("admin_id")
		   && $this->diafan->values("admin_id") != $this->diafan->_users->id
		   && DB::query_result("SELECT only_self FROM {users_role} WHERE id=%d LIMIT 1", $this->diafan->_users->role_id))
		{
			Custom::inc('includes/404.php');
		}

		// Подготовка значений полей для элемента в соответствии с указанными типами

		foreach ($this->diafan->variables as $title => $variable_table)
		{
			$this->diafan->prepare_new_values($variable_table);
		}
		$query = $this->query;

		// Сохраняет конфигурацию модуля
		if ($this->diafan->config("config"))
		{
			for ($q = 0; $q < count($this->value); $q++)
			{
				$this->value[$q] = str_replace("\n", '', $this->value[$q]);
			}
			DB::query("DELETE FROM {config} WHERE module_name='%s' AND site_id=%d AND (lang_id="._LANG." OR lang_id=0)", $this->diafan->_admin->module, $this->diafan->_route->site);
			for ($q = 0; $q < count($this->value); $q++)
			{
				list( $name, $mask ) = explode('=', $this->query[$q]);
				$name = str_replace('`', '', $name);

				// записываем значение в конфигурацию если оно не пустое или если конфигурация сохраняется для раздела и оно отличается от основной конфигурации
				if (! $this->diafan->_route->site && ($this->value[$q] || $this->value[$q] === "0") || $this->diafan->_route->site && DB::query_result("SELECT value FROM {config} WHERE module_name='%s' AND site_id=0 AND name='%h'".($this->diafan->variable_multilang($name) ? " AND lang_id='"._LANG."'" : '' )." LIMIT 1", $this->diafan->_admin->module, $name) != $this->value[$q])
				{
					DB::query("INSERT INTO {config} (name, module_name, value, site_id, lang_id) VALUES ('%h', '%h', ".$mask.", '%d', '%d')", $name, $this->diafan->_admin->module, $this->value[$q], $this->diafan->_route->site, ($this->diafan->variable_multilang($name) ? _LANG : 0));
				}
			}
			$this->done = true;

			// Удаляет кэш конфигурации модулей
			$this->diafan->_cache->delete("configmodules", "site");
		}

		// Сохраняет элемент
		//elseif(! empty($this->query)) // TO_DO: внимательнее, при таком варианте всегда empty :)
		elseif(! empty($query))
		{
			//if (! DB::query("UPDATE {".$this->diafan->table."} SET ".implode(', ', $this->query)." WHERE id = %d", array_merge($this->value, array($this->diafan->id))))
			// TO_DO: изменение правила для корректной обработки специальных идентификаторов
			if (! DB::query("UPDATE {".$this->diafan->table."} SET ".implode(', ', $this->query)." WHERE id = '%h'", array_merge($this->value, array($this->diafan->id))))
			{
				return;
			}
		}

		// Сопутствующие действия при сохранении элемента модуля
		$this->include_method('save_variable', array($this->diafan->id));

		// Удаляет кэш модуля
		$this->diafan->_cache->delete("", $this->diafan->_admin->module);

		$this->diafan->save_redirect();
	}

	/**
	 * Добавляет элемент в базу данных
	 *
	 * @return void
	 */
	public function save_new()
	{
		$def = $masks = array ();
		if ($this->diafan->variable_list('plus'))
		{
			//$def['parent_id'] = intval($this->diafan->_route->parent);
			// TO_DO: изменение правила для корректной обработки специальных идентификаторов
			$def['parent_id'] = $this->diafan->_db_ex->filter_uid($this->diafan->_route->parent);
			$masks[] = "'%h'";
		}
		if ($this->diafan->config("element_site"))
		{
			$def['site_id'] = $this->diafan->filter($_POST, "int", "site_id");
			$masks[] = "'%d'";
		}
		if ($this->diafan->config("element"))
		{
			$def['cat_id'] = $this->diafan->filter($_POST, "int", "cat_id");
			$masks[] = "'%d'";
		}

		//$this->diafan->id = DB::query("INSERT INTO {".$this->diafan->table."} (".implode(',', array_keys($def)).") VALUES (".implode(',', $def).")");
		// TO_DO: изменение правила для корректной обработки специальных идентификаторов
		$this->diafan->id = $this->diafan->_db_ex->add_new('{'.$this->diafan->table.'}', array_keys($def), $masks, $def);

		if (! $this->diafan->id)
		{
			throw new Exception('Не удалось добавить новый элемент в базу данных. Возможно, таблица '.DB_PREFIX.$this->diafan->table.' имеет неправильную структуру.');
		}
	}

	/**
 	 * Подключает действие, описанное в модуле
 	 *
 	 * @param string $method название метода
 	 * @param array $args аргументы для метода
 	 * @return void
 	 */
 	public function include_method($method, $args)
 	{
		$module_name = $this->diafan->_admin->module;
		$element_type = $this->diafan->element_type();
		if($element_type == 'param')
		{
			$table = $this->diafan->_admin->module.'_param';
		}
		elseif($element_type == 'cat')
		{
			$table = $this->diafan->table;
		}
		else
		{
			$table = $this->diafan->table_element_type($this->diafan->_admin->module, $element_type);
		}
		if ($this->diafan->element_type() != $element_type)
		{
			if(! isset($this->cache["class_".$element_type]))
			{
				$e_type = '';
				if($element_type == 'cat')
				{
					$e_type = 'category';
				}
				elseif($element_type == 'import')
				{
					$e_type = 'importexport.element';
				}
				elseif($element_type == 'import_category')
				{
					$e_type = 'importexport.category';
				}
				elseif($element_type != 'element')
				{
					$e_type = $element_type;
				}
				Custom::inc('modules/'.$module_name.'/admin/'.$module_name.'.admin'.($e_type ? '.'.$e_type : '').'.php');
				$class = ucfirst($module_name).'_admin'.($e_type ? '_'.str_replace('.', '_', $e_type) : '');
				$this->cache["class_".$element_type] = new $class($this->diafan);
				$this->cache["class_".$element_type]->diafan->_frame = $this->cache["class_".$element_type];
			}
			$class = &$this->cache["class_".$element_type];
		}
		else
		{
			$class = &$this->diafan;
		}
		// функция, описанная в модуле
		if(is_callable(array(&$class, $method)))
		{
			call_user_func_array(array(&$class, $method), $args);
		}
 	}
}

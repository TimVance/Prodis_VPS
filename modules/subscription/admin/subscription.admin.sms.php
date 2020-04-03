<?php
/**
 * Рассылки по SMS
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
 * Subscription_admin_sms
 */
class Subscription_admin_sms extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'subscription_sms';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
				'help' => 'Наименование рассылки. В SMS не подставляется. Используется администратором для удобства.',
			),
			'created' => array(
				'type' => 'function',
				'name' => 'Дата добавления',
				'help' => 'Отображается дата добавления или отправления рассылки.',
			),
			'send' => array(
				'type' => 'checkbox',
				'name' => 'Отправить рассылку сразу после сохранения',
				'help' => 'Если отметить эту галку и сохранить, рассылка начнет отправляться. Если не отмечать галку, рассылка будет сохранена как черновик. Рассылка отправляется один раз, если отмечено поле «Отправить».',
			),
			'hr2' => 'hr',
			'text' => array(
				'type' => 'textarea',
				'name' => 'Текст рассылки',
				'help' => 'Текст SMS-сообщения латинскими буквами.',
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'created' => array(
			'name' => 'Дата',
			'type' => 'date',
			'sql' => true,
			'no_important' => true,
		),
		'name' => array(
			'name' => 'Название'
		),
		'actions' => array(
			'trash' => true,
		),
	);

	/**
	 * @var array поля для фильтра
	 */
	public $variables_filter = array (
		'name' => array(
			'type' => 'text',
			'name' => 'Искать по названию',
		),
	);

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить SMS-рассылку');
	}

	/**
	 * Выводит список SMS-рассылок
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Выводит системное сообщение
	 *
	 * @return void
	 */
	public function show_error_message()
	{
		if ($this->diafan->_route->error >= 10)
		{
			$count = $this->diafan->_route->error - 10;
			$this->diafan->_route->error = 0;
		}

		if (! empty($count))
		{
			echo '<div class="error">'.$this->diafan->_('Рассылка отправлена. Количество SMS: ').' '.$count.'</div>';
		}
	}

	/**
	 * Редактирование поля "Отправить сообщение"
	 * @return void
	 */
	public function edit_variable_send()
	{
		if ($this->diafan->values("sends"))
			return;

		echo '
		<div class="unit">
			<input type="checkbox" name="'.$this->diafan->key.'" id="input_'.$this->diafan->key.'" value="1"'.($this->diafan->values("sends") ? ' checked' : '').'><label for="input_'.$this->diafan->key.'"><b>'.($this->diafan->values("send") ? $this->diafan->_('Отправлено') : $this->diafan->variable_name()).'</b>'.$this->diafan->help().'</label>
		</div>';
	}

	/**
	 * Редактирование поля "Дата отправки"
	 * @return void
	 */
	public function edit_variable_created()
	{
		if ($this->diafan->is_new)
			return;

		echo '
		<div class="unit">
			<b>
				'.($this->diafan->values("send") ? $this->diafan->_('Дата отправки') : $this->diafan->variable_name()).':
			</b>
				'.date("D, d M Y H:i:s", ($this->diafan->value ? $this->diafan->value : time()))
				.$this->diafan->help().'
		</div>';
	}

	/**
	 * Сохранение поля "Дата отправки"
	 * @return void
	 */
	public function save_variable_created()
	{
		$created = $this->diafan->values("created");

		if (! $created || ! empty($_POST["text"]) && ! empty($_POST["send"]))
		{
			$created = time();
		}

		$this->diafan->set_query("created='%d'");
		$this->diafan->set_value($created);
	}

	/**
	 * Сохранение поля "Отправить рассылку"
	 * @return void
	 */
	public function save_variable_send()
	{
		if (empty($_POST["text"]) && ! empty($_POST["send"]))
		{
			$this->diafan->err = 10;
			return;
		}

		if (empty($_POST["send"]) || empty($_POST["text"]))
		{
			return;
		}

		$rows = DB::query_fetch_all("SELECT phone FROM {subscription_phones} WHERE act='1' AND trash='0'");
		foreach ($rows as $row)
		{
			$this->diafan->_postman->message_add_sms($_POST["text"], $row["phone"]);
			$k++;
		}
		$this->diafan->err = 10 + $k;

		$this->diafan->set_query("send='%d'");
		$this->diafan->set_value(1);
	}
}
<?php
/**
 * Рассылки
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
 * Subscription_admin
 */
class Subscription_admin extends Frame_admin
{
	/**
	 * @var integer количество писем, отправляемых за одну итерацию
	 */
	private $mail_count = 50;

	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'subscription';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название рассылки',
				'help' => 'Используется в теме письма.',
			),
			'created' => array(
				'type' => 'function',
				'name' => 'Дата добавления',
				'help' => 'Отображается дата добавления или отправления рассылки.',
			),
			'send' => array(
				'type' => 'checkbox',
				'name' => 'Отправить рассылку сразу после сохранения',
				'help' => 'Если отметить эту галку и сохранить, рассылка начнет отправляться. Если не отмечать галку, рассылка будет сохранена как черновик.',
			),
			'hr2' => 'hr',
			'text' => array(
				'type' => 'editor',
				'name' => 'Содержимое рассылки',
				'help' => "Текст рассылки. Можно добавить:\n\n* %name – имя пользователя,\n* %link – ссылка для редактирования категорий рассылки,\n* %actlink – ссылка для отмены рассылки.\n\nШаблон и тему письма можно задать в настройках модуля. Если отметить «Применить типограф», контент будет отформатирован согласно правилам экранной типографики с помощью [веб-сервиса «Типограф»](http://www.artlebedev.ru/tools/typograf/webservice/). Опция «HTML-код» позволяет отключить визуальный редактор для текущего поля. Значение этой настройки будет учитываться и при последующем редактировании.",
			),
			'cat_id' => array(
				'type' => 'select',
				'name' => 'Категория',
				'help' => 'Параметр появляется, если в настройках модуля отмечена опция «Использовать категории».',
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
	 * @var array настройки модуля
	 */
	public $config = array (
		'element', // используются группы
		'element_multiple', // модуль может быть прикреплен к нескольким группам
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		if(! $this->diafan->configmodules("cat", "subscription", $this->diafan->_route->site))
		{
			$this->diafan->config("element", false);
			$this->diafan->config("element_multiple", false);
		}
		if (count($this->diafan->_languages->all) < 2)
		{
			$this->diafan->variable_unset('lang');
		}
		$this->send_mail();
	}

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		if ($this->diafan->config('element') && ! $this->diafan->not_empty_categories)
		{
			echo '<div class="error">'.$this->diafan->_('В %sнастройках%s модуля подключены категории, чтобы начать добавлять рассылку создайте хотя бы одну %sкатегорию%s.', '<a href="'.BASE_PATH_HREF.'subscription/config/">', '</a>', '<a href="'.BASE_PATH_HREF.'subscription/category/">', '</a>').'</div>';
		}
		else
		{
			$this->diafan->addnew_init('Добавить рассылку');
		}
	}

	/**
	 * Выводит список рассылок
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
		if ($this->diafan->_route->success >= 10)
		{
			$count = $this->diafan->_route->success - 10;
			$this->diafan->_route->success = 0;
		}

		if (! empty($count))
		{
			echo '<div class="ok">'.$this->diafan->_('Рассылка отправлена. Количество писем: %s.', $count).'</div>';
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
			<input type="checkbox" name="'.$this->diafan->key.'" id="input_'.$this->diafan->key.'" value="1"'.($this->diafan->values("sends") ? ' checked' : '').'>
			<label for="input_'.$this->diafan->key.'">'.($this->diafan->values("send") ? $this->diafan->_('Отправлено') : $this->diafan->variable_name()).$this->diafan->help().'</label>
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

		$this->diafan->set_query("send='%d'");
		$this->diafan->set_value(1);
	}

	/**
	 * Производит перенаправление на страницу редактирования, на список и пр.
	 *
	 * @return void
	 */
	public function save_redirect()
	{
		if (! empty($_POST["send"]) && ! empty($_POST["text"]))
		{
			$this->diafan->redirect(URL.'?subscription_action=send_mail&id='.$this->diafan->id);
		}
		parent::save_redirect();
	}
	
	public function send_mail()
	{
		if(empty($_GET["subscription_action"]) || $_GET["subscription_action"] != 'send_mail')
		{
			return;
		}
		if(empty($_GET["id"]))
		{
			return;
		}

		$s = DB::query_fetch_array("SELECT name, text, id FROM {subscription} WHERE id=%d AND trash='0'", $_GET["id"]);
		if($this->diafan->configmodules('subject'))
		{
			$subject = str_replace(
				array(
					'%title',
					'%url',
					'%subject'
				),
				array(
					TITLE,
					BASE_URL,
					$s["name"]
				),
				$this->diafan->configmodules('subject')
			);
		}
		$mess = str_replace(
			'"/'.USERFILES,
			'"http://'.BASE_URL.'/'.USERFILES,
			$this->diafan->_route->replace_id_to_link($s["text"])
		);
		if($this->diafan->configmodules('message'))
		{
			$url_subscription = BASE_PATH.$this->diafan->_route->module("subscription");
			$mess = str_replace(
				array(
					'%title',
					'%url',
					'%text'
				),
				array(
					TITLE,
					BASE_URL,
					$mess
				),
				$this->diafan->configmodules('message')
			);	
		}

		$k = 0;
		$i = $this->diafan->filter($_GET, "integer", "i");
		$ids = '';
		$cats_array = array();
		$id_array = array();

		if ($this->diafan->configmodules("cat"))
		{
			$id_array =  DB::query_fetch_value("SELECT DISTINCT(r.element_id) FROM {subscription_emails_cat_unrel} AS r INNER JOIN {subscription_category_rel} AS c ON c.cat_id=r.cat_id WHERE c.element_id=%d AND c.trash='0'", $s["id"], "element_id");
			if(! empty($id_array))
			{
				$ids .= ' AND id NOT IN ('.implode(',', $id_array).')';
			}		    
		}

		$rows = DB::query_fetch_all("SELECT mail, name, code FROM {subscription_emails} WHERE act='1' AND trash='0'".$ids." LIMIT ".($i * $this->mail_count).", ".$this->mail_count);
		foreach ($rows as $row)
		{
			if(! $row["code"])
			{
				$row["code"] = md5(rand(0, 9999999));
				DB::query("UPDATE {subscription_emails} SET code='%s' WHERE id=%d", $row["code"], $row["id"]);
			}
			$link    = $url_subscription.'?mail='.$row["mail"].'&code='.$row["code"];
			$actlink = $url_subscription.'?action=del&mail='.$row["mail"].'&code='.$row["code"];

			$message = str_replace(
				array(
					'%link',
					'%actlink',
					'%name',
				),
				array(
					$link,
					$actlink,
					$row["name"],
				),
				$mess
			);

			$this->diafan->_postman->message_add_mail(
				$row["mail"],
				$subject,
				$message,
				($this->diafan->configmodules("emailconf") && $this->diafan->configmodules("email") ? $this->diafan->configmodules("email") : EMAIL_CONFIG)
			);
			$k++;
		}
		if($k == $this->mail_count)
		{
			echo '
			Sended: '.($i * $this->mail_count + $k).'
			<meta http-equiv="Refresh" content="0; url='.URL.'?subscription_action=send_mail&amp;i='.($i + 1).'&amp;id='.$s["id"].'">';
			exit;
		}
		$this->diafan->redirect(URL.'success'.(10 + $k + $i * $this->mail_count).'/');
	}
}
<?php
/**
 * База номеров телефонов для SMS рассылки
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
 * Subscription_admin_phones
 */
class Subscription_admin_phones extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'subscription_phones';
	
	/**
	 * @var string категории рассылок
	 */
	public $subscription = '';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'phone' => array(
				'type' => 'phone',
				'name' => 'Номер телефона в федеральном формате',
				'help' => '11 цифр номера мобильного телефона.',
			),
			'name' => array(
				'type' => 'text',
				'name' => 'Имя',
				'help' => 'Имя получателя. В рассылке не участвует.',
			),
			'created' => array(
				'type' => 'date',
				'name' => 'Дата добавления',
				'help' => 'дата добавления номера в базу данных.',
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Получает рассылку',
				'help' => 'Позволяет отключить телефон от рассылки.',
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
		'name' => array(
			'name' => 'Имя',
		),
		'phone' => array(
			'name' => 'Номер телефона',
			'type' => 'text',
			'sql' => true,
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
		'phone' => array(
			'name' => 'Искать по номеру телефона',
			'type' => 'text',
		),
	);

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		$this->diafan->addnew_init('Добавить номер телефона');
	}

	/**
	 * Выводит список рассылок
	 * @return void
	 */
	public function show()
	{
		$this->upload();

		$this->diafan->list_row();

		$this->form_upload();
	}

	/**
	 * Выводит форму импорт/экспорт ключевиков
	 * 
	 * @return void
	 */
	private function form_upload()
	{
		echo '
		<form action="" enctype="multipart/form-data" method="post" class="box box_half box_height">
			<input type="hidden" name="upload" value="true">
			<div class="box__heading">'.$this->diafan->_('Импорт').'</div>
			
			<input type="checkbox" name="delete_old" id="input_delete_old" value="1"> <label for="input_delete_old">'.$this->diafan->_('удалить неописанные в файле строки').'</label><br><br>
		
			<input type="file" class="file" name="file">
			
			<div class="box__warning">
				<i class="fa fa-warning"></i>
				'.$this->diafan->_('*файл .txt, каждый подписчик с новой строки в формате «Имя;79998886655»').'
			</div>
			
			<button class="btn btn_blue btn_small">'.$this->diafan->_('Импортировать').'</button>
		</form>

		<div class="box box_half box_height box_right">
			<div class="box__heading">'.$this->diafan->_('Экспорт').'</div>
			
			<a href="'.BASE_PATH.'subscription/export/phones/?'.rand(0, 999999).'" class="file-load">
				<i class="fa fa-file-code-o"></i>
				'.$this->diafan->_('Скачать файл phones.txt').'
			</a>
		</div>';
	}

	/**
	 * Загружает файл перевода
	 * 
	 * @return void
	 */
	private function upload()
	{	
		if (! empty($_GET["result"]))
		{
			if (empty($_GET["count_add"]) && empty($_GET["count_upd"]))
			{
				echo '<div class="error">'.$this->diafan->_('В файле не найден ни один корректный телефон.');
			}
			else
			{
				echo '<div class="ok">';
			}
			if (! empty($_GET["count_add"]))
			{
				echo $this->diafan->_('Добавлено новых телефонов: %d.', $_GET["count_add"]).' ';
			}
			if (! empty($_GET["count_upd"]))
			{
				echo $this->diafan->_('Обновлено телефонов: %d.', $_GET["count_upd"]);
			}
			echo '</div>';
		}
		if(! empty($_POST["delete_old"]))
		{
			DB::query("TRUNCATE TABLE {subscription_phones}");
		}
		if (! isset($_FILES["file"]) || ! is_array($_FILES["file"]) || $_FILES["file"]['name'] == '')
		{
			return;
		}
		$oldphones  = array();
		if(empty($_POST["delete_old"]))
		{
			$oldphones = DB::query_fetch_key_value("SELECT * FROM {subscription_phones} WHERE trash='0'", "phone", "id");
		}
		Custom::inc("includes/validate.php");

		$file = file_get_contents($_FILES["file"]['tmp_name']);

		$newphones = explode("\n", $file);
		foreach ($newphones as $s)
		{
			if(! trim($s))
			{
				continue;
			}
			list($name, $phone) = explode(';', $s);
			if(Validate::phone($phone))
			{
				continue;
			}
			if(! empty($oldphones[$phone]))
			{
				DB::query("UPDATE {subscription_phones} SET act='1', name='%s' WHERE id=%d", $name, $oldphones[$phone]);
				$count_upd++;
			}
			else
			{
				DB::query("INSERT INTO {subscription_phones} (act, name, phone, created) VALUES ('1', '%s', '%s', %d)", $name, $phone, time());
				$count_add++;
			}
		}
		unlink($_FILES["file"]['tmp_name']);

		$this->diafan->redirect(URL.'success1/?result=true&count_add='.$count_add.'&count_upd='.$count_upd);
	}

	/**
	 * Выводит системное сообщение
	 *
	 * @return void
	 */
	public function show_error_message()
	{
		if ($this->diafan->_route->error)
		{
			echo '<div class="error">'.$this->diafan->_('Файл не верного формата.').'</div>';
		}

		if ($this->diafan->_route->success)
		{
			echo '<div class="ok">'.$this->diafan->_('Изменения сохранены.').'</div>';
		}
	}
}
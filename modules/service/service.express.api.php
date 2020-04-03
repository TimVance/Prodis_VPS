<?php
/**
 * API импорта/экспорта
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
 * Service_express_api
 */
class Service_express_api extends Diafan
{
	/**
   * @var string типовая ошибка
   */
	const ERROR = 'error';

  /**
	 * @var string путь до временной директории относительно корня сайта
	 */
	private $dir_path = 'tmp/express';

  /**
	 * @var array массив значений, которое будет закодировано для JSON-представления данных
	 */
	protected $result = array();

  /**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		parent::__construct($diafan);
		$this->diafan->set_time_limit();
	}

	/**
	 * Деструктор класса
	 *
	 * @return void
	 */
	public function __destruct()
	{

  }

  /**
	 * Стартует интеграцию
	 *
	 * @return void
	 */
	public function start()
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			Custom::inc('includes/404.php');
		}

    File::create_dir($this->dir_path, true);
		$express_key = $this->diafan->configmodules('express_key', 'service');

		if(empty($_REQUEST["type"]) || ! in_array($_REQUEST["type"], array('import', 'export'))
		|| empty($express_key) || empty($_REQUEST["key"]) || $express_key != encrypt($_REQUEST["key"]))
		{
			Custom::inc('includes/404.php');
		}

		if($this->diafan->_users->id)
		{
			$success = true;
		}
		else
		{
			if(! empty($_REQUEST["auth"]))
			{
				preg_match('/^Basic\s+(.*)$/i', $_REQUEST["auth"], $user_pass);
				list($user, $pass) = explode(':', base64_decode($user_pass[1]));
			}
			else
			{
				$user = $_SERVER["PHP_AUTH_USER"];
				$pass = $_SERVER["PHP_AUTH_PW"];
			}

			$name = ($this->diafan->configmodules("mail_as_login", "users", 0, 0) ? "mail" : "name");
			$result = DB::query("SELECT * FROM {users} u WHERE trash='0' AND act='1' AND LOWER(".$name.")=LOWER('%s') AND password='%s'", trim($user), encrypt(trim($pass)));
			if (DB::num_rows($result))
			{
				$user = DB::fetch_object($result);
				$this->diafan->_users->set($user);
				if($this->diafan->_users->roles('init', 'service', false, 'admin'))
				{
					$success = true;
				}
			}
		}
		if(empty($success))
		{
      // ошибка авторизации
      $this->result['errors']['auth'] = self::ERROR;
		}
    $this->result();


    if(empty($_REQUEST["mode"]))
    {
      // ошибка: не задан режим
      $this->result['errors']['mode'] = self::ERROR;
    }
    $this->result();

    if($_REQUEST["type"] == 'import')
    {
      switch($_REQUEST["mode"])
			{
				case 'checkauth':
					$this->checkauth();
					break;

				case 'init':
					$this->init();
					break;

				case 'file':
					$this->catalog_file();
					break;

				case 'read':
					$this->read_file();
					break;
			}
    }
    $this->result();


    if($cat = $this->diafan->filter($_REQUEST, 'int', 'cat', 0))
		{
			$cat = DB::query_fetch_array("SELECT * FROM {service_express_fields_category} WHERE id=%d LIMIT 1", $cat);
		}
		else $cat = false;
    if(empty($cat))
    {
      // ошибка: не задано описание полей
      $this->result['errors']['cat'] = self::ERROR;
    }
		$modules = $this->diafan->array_column($this->diafan->_service->modules_express(), 'module_name');
		if(empty($cat["module_name"])
		|| ! in_array($cat["module_name"], $modules) || ! $this->diafan->_users->roles('init', $cat["module_name"], false, 'admin'))
    {
      // ошибка: не задан модуль или недостаточно прав у пользователя
      $this->result['errors']['module'] = self::ERROR;
    }
    $this->result();

    if($_REQUEST["type"] == 'import')
		{
			switch($_REQUEST["mode"])
			{
				case 'import':
					$this->catalog_import();
					break;

				case 'success':
					$this->import_success();
					break;

				default:
					Custom::inc('includes/404.php');
			}
      $this->result();
		}
	}

  /**
	 * Начало сеанса
	 *
	 * @return void
	 */
	private function checkauth()
	{
		$this->result['checkauth'] = 'success';                //echo "success\n";
		$this->result['name'] = $this->diafan->_session->name; //echo $this->diafan->_session->name."\n";
		$this->result['id'] = $this->diafan->_session->id;     //echo $this->diafan->_session->id;
	}

	/**
	 * Запрос параметров от сайта
	 *
	 * @return void
	 */
	private function init()
	{
		$this->result['zip'] = 'no';             //echo "zip=no\n";
		$this->result['file_limit'] = '1000000'; //echo "file_limit=1000000\n";
	}

  /**
	 * Выгрузка каталогов: выгрузка на сайт файлов обмена
	 *
	 * @return void
	 */
	private function catalog_file()
	{
		if(! isset($_REQUEST['filename']) || ! ($filename = $_REQUEST['filename']) || (preg_match('/\.php$/', $filename)))
		{
      // ошибка при сохранении файла
      $this->result['errors']['file'] = self::ERROR;
      return;
		}
		File::delete_file($this->dir_path.'/'.$filename);
    try
    {
			//File::save_file(file_get_contents('php://input'), ABSOLUTE_PATH.$this->dir_path.'/'.$filename);
      $f = fopen(ABSOLUTE_PATH.$this->dir_path.'/'.$filename, 'ab');
  		fwrite($f, file_get_contents('php://input'));
  		fclose($f);
    }
    catch (Exception $e)
    {
      // ошибка при сохранении файла
      $this->result['errors']['file'] = self::ERROR;
      return;
    }
		if(preg_match('/\.(jpg|jpeg|png|gif|tif|tiff|bmp|dib)$/', $filename))
		{
			$this->result['file'] = 'success';
			return;
		}


		Custom::inc("adm/includes/frame.php");
		Custom::inc('modules/service/admin/service.admin.express.import.php');
		$object = new Service_admin_express_import($this->diafan);
		$object->prepare_config();

		// принудительное снятие блокировки процесса
		if(isset($_REQUEST["no_busy"]))
		{
			$this->diafan->_service->busy(false, true);
		}

		// устанавливаем блокировку процесса
		if($this->diafan->_service->busy())
		{
			// ошибка при сохранении файла
      $this->result['errors']['file'] = 'busy';
			return false;
		}
		else $this->diafan->_service->busy(true);

		// очищаем возможный мусор
		$object->garbage_cleaning();

		$filename = ABSOLUTE_PATH.$this->dir_path.'/'.$filename;

		// загружаем описание
		$fileinfo = pathinfo($filename);
		if($fileinfo['extension'] == 'zip')
		{
			if(class_exists('ZipArchive'))
			{
				$zip = new ZipArchive;
				if ($zip->open($filename) === true)
				{
					for($i = 0; $i < $zip->numFiles; $i++)
					{
						$tmp = $this->dir_path.'/'.md5('expressimport'.$this->diafan->uid());
						File::save_file($zip->getFromName($zip->getNameIndex($i)), $tmp);
						$fi = pathinfo($zip->getNameIndex($i));
						if(in_array($fi['extension'], array_keys($object->extensions)))
						{
							$object->defer_files = array(
								"file_path" => $tmp,
								"basename" => $fi['basename'],
								"extension" => $fi['extension']
							);
						}
						else
						{
							$object->errors = '- '.$this->diafan->_("расширение файла <b>%s</b> не поддерживается", $fi['basename']);
							unlink($tmp);
						}
					}
					$zip->close();
				}
				else
				{
					$object->errors = '- '.$this->diafan->_("ошибка при чтении архивного файла");
					//return;
				}
			}
			else
			{
				$object->errors = '- '.$this->diafan->_('на сервере не установлено расширение для распоковки ZIP-архивов (перед загрузкой необходимо распаковать содержимое архива)');
				//return;
			}
		}
		elseif(in_array($fileinfo['extension'], array_keys($object->extensions)))
		{
			$tmp = $this->dir_path.'/'.md5('expressimport'.$this->diafan->uid());
			File::upload_file($filename, $tmp);

			$object->defer_files = array(
				"file_path" => $tmp,
				"basename" => $fileinfo['basename'],
				"extension" => $fileinfo['extension']
			);
		}
		else
		{
			$object->errors = '- '.$this->diafan->_("расширение файла <b>%s</b> не поддерживается", $fileinfo['basename']);
		}


		// проверяем файлы
		if(count($object->defer_files))
		{
			$files = $object->defer_files;
			foreach($files as $key => $file)
			{
				if(! File::file_size($file["file_path"]))
				{
					$file["delete"] = true; $object->defer_files = $file;
				}
			}
		}
		// Если хотя бы один файл импорта загрузился, то игнорируем ошибки
		if(count($object->defer_files) > 0 && count($object->errors) > 0)
		{
			$object->errors = NULL;
		}

		if(count($object->errors) > 0 || count($object->defer_files) <= 0)
		{
			// очищаем возможный мусор
			$object->garbage_cleaning();
			// ошибка при сохранении файла
			$this->result['errors']['file'] = self::ERROR;
		}
		else $this->result['file'] = 'success'; //echo "success\n";

		// снимаем блокировку процесса
		$this->diafan->_service->busy(false);
		unset($object);
	}

	/**
	 * Чтение каталогов
	 *
	 * @return void
	 */
	private function read_file()
	{
		Custom::inc("adm/includes/frame.php");
		Custom::inc('modules/service/admin/service.admin.express.import.php');
		$object = new Service_admin_express_import($this->diafan);
		$object->prepare_config();

		if(count($object->defer_files) <= 0)
		{
			// ошибка чтения загруженного файла
      $this->result['errors']['read'] = self::ERROR;
      return;
		}

		// принудительное снятие блокировки процесса
		if(isset($_REQUEST["no_busy"]))
		{
			$this->diafan->_service->busy(false, true);
		}

		// устанавливаем блокировку процесса
		if($this->diafan->_service->busy())
		{
			// ошибка при чтении загруженного файла
			$this->result['errors']['read'] = 'busy';
			return false;
		}
		else $this->diafan->_service->busy(true);


		// перебираем
		while(count($object->defer_files) > 0)
		{
			$object->read_file(true, true);
		}


		if(count($object->errors) > 0)
		{
			// очищаем возможный мусор
			$object->garbage_cleaning();
			// Выявлены ошибки во время загрузки данных импорта
			$this->result['errors']['read'] = self::ERROR;
		}
		else $this->result['read'] = 'success';

		// снимаем блокировку процесса
		$this->diafan->_service->busy(false);
		unset($object);
	}

	/**
	 * Импорт каталогов
	 *
	 * @return void
	 */
	private function catalog_import()
	{
		$cat_id = $this->diafan->filter($_REQUEST, 'int', 'cat', 0);
		Custom::inc('modules/service/service.express.inc.php');
		$object = new Service_express_inc($this->diafan, $cat_id);
		$result = $object->import_init($cat_id);
		$errors_log = $object->import_get_log();

		if(! in_array($result, array('next','success')))
		{
			$this->result['errors']['import'] = $result == false ? self::ERROR : $result;
		}
		else
		{
			if($errors_log && $result == 'success')
			{// TO_DO: сообщаем об ошибки в импорте только в конце процесса
				$this->result['errors']['import'] = self::ERROR;
			}
			else
			{
				if($result == 'next')
				{
					$this->result['pos'] = $object->import_get_pos();
					$this->result['max'] = $object->import_get_max();
				}
				elseif($result == 'success')
				{
					$this->import_success();
				}
				$this->result['import'] = $result;
			}
		}
		unset($object);
	}

  /**
	 * Обмен информацией о заказах: успешное получение и запись заказов системой "1С:Предприятие"
	 *
	 * @return void
	 */
	private function import_success()
	{
		Custom::inc('includes/config.php');
		$config = new Config();
		$config->save(array('LAST_EXPRESS_IMPORT' => date('d.m.Y H:i')), $this->diafan->_languages->all);
		File::delete_dir($this->dir_path);
	}

  /**
	 * Выводит JSON-представление данных
	 *
   * @return void
	 */
  protected function result()
  {
    if(empty($this->result)) return;
    echo json_encode($this->result);
    exit;
  }
}

$class = new Service_express_api($this->diafan);
$class->start();

exit;

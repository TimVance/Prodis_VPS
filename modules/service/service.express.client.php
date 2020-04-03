<?php
/**
 * Импорт с использование API
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
 * Service_express_client
 */
class Service_express_client extends Diafan
{
	/**
   * @var string URL-адрес API импорта/экспорта
   */
	const URL = 'service/express/api/';

	/**
	 * @var string ключ
	 */
	private $key = '';

	/**
	 * Определяет переменные
	 *
	 * @param string $name название переменной
	 * @return mixed
	 */
	public function __get($name)
	{
		if (! isset($this->cache["var"][$name]))
		{
			switch($name)
			{
				default:
					$this->cache["var"][$name] = null;
					break;
			}
		}
		return $this->cache["var"][$name];
	}

	/**
	 * Сохраняет переменные
	 *
	 * @param string $name название переменной
	 * @param mixed $value значение переменной
	 * @return void
	 */
	public function __set($name, $value)
	{
		switch($name)
		{
			default:
				$this->cache["var"][$name] = $value;
				break;
		}
	}

  /**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		parent::__construct($diafan);

		$this->key = isset($_GET["key"]) ? $_GET["key"] : $this->key;

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
	 * Инициирует импорт
	 *
	 * @param string $login логин
	 * @param string $password пароль
	 * @param string $file_path путь до файла импорта относительно корня сайта или url-адрес
	 * @return void
	 */
	public function init($login, $password, $file_path)
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			Custom::inc('includes/404.php');
		}
		
		$this->auth = 'Basic ' . base64_encode($login . ':' . $password);
		if(! $this->checkauth())
		{
			echo '<pre>'."\n".$this->diafan->_('Ошибка авторизации', false)."\n".$this->error.'</pre>';
			return false;
		}
		if(! $this->upload_file($file_path, true))
		{
			echo '<pre>'."\n".$this->diafan->_('Ошибка загрузки файла импорта', false)."\n".$this->error.'</pre>';
			return false;
		}
		if(! $this->read_file())
		{
			echo '<pre>'."\n".$this->diafan->_('Ошибка чтения файла импорта', false)."\n".$this->error.'</pre>';
			return false;
		}
		if(! $this->import_file())
		{
			echo '<pre>'."\n".$this->diafan->_('Ошибка импорта файла', false)."\n".$this->error.'</pre>';
			return false;
		}

		echo 'success';
		return true;
	}

	/**
	 * Начало сеанса
	 *
	 * @return boolean
	 */
	private function checkauth()
	{
		$result = $this->diafan->simple_request(BASE_PATH.self::URL,
			array(
				'auth' => $this->auth,
				'type' => 'import',
				'mode' => 'checkauth',
				'key'  => $this->key,
			)
		);
		if($result === false)
		{
			return false;
		}
		$result = json_decode($result, true);
		$answer = (empty($result["errors"]) && ! empty($result["checkauth"]) && $result["checkauth"] == 'success');
		if(! $answer) $this->error = print_r($result, true);
		return $answer;
	}

	/**
	 * Инициирует загрузку файлов
	 *
	 * @param string $file_path путь до файла относительно корня сайта или url-адрес
	 * @return boolean
	 */
	public function upload_file($file_path)
	{
		$result = $this->diafan->simple_request(BASE_PATH.self::URL,
			array(
				'auth' => $this->auth,
				'type' => 'import',
				'mode' => 'file',
				//'filename' => 'file_import.csv',
				//'no_busy' => 1,
				'key'  => $this->key,
			), $file_path
		);
		if($result === false)
		{
			return false;
		}
		$result = json_decode($result, true);
		$answer = (empty($result["errors"]) && ! empty($result["file"]) && $result["file"] == 'success');
		if(! $answer) $this->error = print_r($result, true);
		return $answer;
	}

	/**
	 * Инициирует чтение загруженных файлов
	 *
	 * @return boolean
	 */
	public function read_file()
	{
		$result = $this->diafan->simple_request(BASE_PATH.self::URL,
			array(
				'auth' => $this->auth,
				'type' => 'import',
				'mode' => 'read',
				//'no_busy' => 1,
				'key'  => $this->key,
			)
		);
		if($result === false)
		{
			return false;
		}
		$result = json_decode($result, true);
		$answer = (empty($result["errors"]) && ! empty($result["read"]) && $result["read"] == 'success');
		if(! $answer) $this->error = print_r($result, true);
		return $answer;
	}

	/**
	 * Инициирует чтение загруженных файлов
	 *
	 * @return boolean
	 */
	public function import_file()
	{
		$result = $this->diafan->simple_request(BASE_PATH.self::URL,
			array(
				'auth' => $this->auth,
				'type' => 'import',
				'mode' => 'import',
				'cat'    => isset($_GET["cat"]) ? $_GET["cat"] : 0,
				//'no_busy' => 1,
				'key'  => $this->key,
			)
		);
		if($result === false)
		{
			return false;
		}
		$result = json_decode($result, true);
		$answer = (empty($result["errors"]) && ! empty($result["import"]) && $result["import"] == 'success');
		if(! $answer) $this->error = print_r($result, true);
		while(! $answer && ! empty($result["import"]) && $result["import"] == 'next')
		{
			$answer = $this->import_file();
		}
		return $answer;
	}
}

$class = new Service_express_client($this->diafan);
$class->init($this->diafan->configmodules('express_name', 'service'), base64_decode($this->diafan->configmodules('express_password', 'service')), $this->diafan->configmodules('express_file_path', 'service'));
// TO_DO: URL
//$class->init('login', 'password', BASE_PATH.'tmp/shop_export_demo.csv');
// TO_DO: Locale
//$class->init('login', 'password', '/tmp/file.csv');
exit;

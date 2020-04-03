<?php
/**
 * @package    DIAFAN.CMS
 *
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
 * Cache_memcached
 * 
 * Кэширование при помощи MemCached
 */
class Cache_memcached implements Cache_interface
{
	/**
	 * @var object объект MemCached
	 */
	private $memcached;

	/**
	 * @var string уникальны код - префикс
	 */
	private $ukey;

	/**
	 * Конструктор класса
	 * 
	 * @return void
	 */
	public function __construct()
	{
		$this->memcached = new Memcached();
		$this->memcached->addServer(CACHE_MEMCACHED_HOST, CACHE_MEMCACHED_PORT);
		$this->ukey = md5(DB_PREFIX.DB_URL);
	}

	/**
	 * Закрывает ранее открытое соединение
	 * 
	 * @return void
	 */
	public function close()
	{
		$a = $this->memcached->getVersion();
		$version = 0;
		foreach($a as $k => $v)
		{
			$version = $v;
		}
		if($this->memcached && $version >= '2.0')
		{
			$this->memcached->quit();
		}
	}

	/**
	 * Проверяет параметры подключения
	 *
	 * @param string $host хост
	 * @param string $port порт
	 * @return boolean
	 */
	public static function check($host, $port)
	{
		$memcached = new Memcached();
		$memcached->addServer($host, $port);
		return $memcached ? true : false;
	}

	/**
	 * Читает кэш модуля $module с меткой $name.
	 *
	 * @param string|array $name метка кэша
	 * @param string $module название модуля
	 * @return mixed
	 */
	public function get($name, $module)
	{
		return $this->memcached->get($this->ukey.$module.$name);
	}

	/**
	 * Сохраняет данные $data для модуля $module с меткой $name
	 *
	 * @param mixed $data данные, сохраняемые в кэше
	 * @param string|array $name метка кэша
	 * @param string $module название модуля
	 * @return void
	 */
	public function save($data, $name, $module)
	{
		$this->memcached->set($this->ukey.$module.$name, $data);
	}

	/**
	 * Удаляет кэш для модуля $module с меткой $name. Если функция вызвана с пустой меткой, то удаляется весь кэш для модуля $module
	 *
	 * @param string $name метка кэша
	 * @param string $module название модуля
	 * @return void
	 */
	public function delete($name, $module)
	{
		if ($name)
		{
			$this->memcached->delete($this->ukey.$module.$name);
		}
		else
		{
			$this->memcached->flush();
		}
	}
}
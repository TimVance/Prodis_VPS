<?php
/**
 * Набор функций для работы с файлами и папками
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

class File
{
	/**
	 * @var string ошибка операции
	 */
	private static $error;

	/**
	 * @var array внутренний кэш класса
	 */
	private static $cache;

	/**
	 * Проверяет существует ли файл
	 *
	 * @param string $file_path путь до файла относительно корня сайта
	 * @return void
	 */
	public static function check_file($file_path)
	{
		if(! file_exists(ABSOLUTE_PATH.$file_path))
		{
			throw new File_exception('Ошибочный путь.');
		}
	}

	/**
	 * Копирует файл
	 *
	 * @param string $source полный путь до исходного файла
	 * @param string $file_path путь до нового файла относительно корня сайта
	 * @return void
	 */
	public static function copy_file($source, $file_path)
	{
		$arr = explode('/', $file_path);
		$name = array_pop($arr);
		$path = implode('/', $arr);

		self::create_dir($path);

		if(! $source)
		{
			throw new File_exception('Пустая ссылка на исходный файл.');
		}
		if(! self::is_writable("tmp"))
		{
			throw new File_exception('Установите права на запись (777) для папки tmp.');
		}
		$tmp_path = 'tmp/'.mt_rand(0, 999999);
		if(preg_match('/^https?:\/\//', $source))
		{
			Custom::inc('plugins/httprequest/httprequest.php');
			$new_file = fopen(ABSOLUTE_PATH.$tmp_path, 'wb');
			if(! DHttpRequest::get($source)->receive($new_file)->ok())
			{
				throw new File_exception('Невозможно скопировать файл '.$source.'.');
			}
			fclose($new_file);
			if(! filesize(ABSOLUTE_PATH.$tmp_path))
			{
				unlink(ABSOLUTE_PATH.$tmp_path);
				return;
			}
		}
		else
		{
			if(! file_exists($source))
			{
				throw new File_exception('Файл '.$source.' не существует.');
			}
			copy($source, ABSOLUTE_PATH.$tmp_path);
		}
		self::upload_file(ABSOLUTE_PATH.$tmp_path, $path.'/'.$name);
	}

	/**
	 * Загружает файл и удаляет временный файл
	 *
	 * @param string $tmp_path полный путь, где храниться временный файл
	 * @param string $file_path путь до нового файла относительно корня сайта
	 * @return void
	 */
	public static function upload_file($tmp_path, $file_path)
	{
		$arr = explode('/', $file_path);
		$name = array_pop($arr);
		$path = implode('/', $arr);


		self::create_dir($path);

		$file_path = ($path ? $path.'/' : '').$name;
		if(! file_exists($tmp_path))
		{
			throw new File_exception('Файл '.$tmp_path.' не существует.');
		}
		if(self::is_writable($path) && copy($tmp_path, ABSOLUTE_PATH.$file_path))
		{
			chmod(ABSOLUTE_PATH.$file_path, 0755);
		}
		else
		{
			$conn_id = self::connect_ftp();
			if($conn_id)
			{
				if (! ftp_put($conn_id, $file_path, $tmp_path, FTP_BINARY))
				{
					unlink($tmp_path);
					throw new File_exception('Не удалось сохранить файл. Проверьте данные для подключения по FTP или установите права на запись (777) для папки '.($path ? $path : '/').'.');
				}
				ftp_close($conn_id);
			}
			else
			{
				unlink($tmp_path);
				throw new File_exception('Не удалось сохранить файл. '.self::$error.' Проверьте данные для подключения по FTP или установите права на запись (777) для папки '.($path ? $path : '/').'.');
			}
		}
		unlink($tmp_path);
	}

	/**
	 * Сохраняет файл
	 *
	 * @param string $content содержание файла
	 * @param string $file_path путь до нового файла относительно корня сайта
	 * @param mixed $append (false|0|1) режим записи файла: false - создает новый файл (если на момент вызова файл с таким именем уже существовал, то он предварительно уничтожается), 0 - дополняет файл (если файл уже существует, данные будут дописаны в конец файла вместо того, чтобы его перезаписать), 1 - дополняет файл с новой строки (если файл уже существует, данные будут дописаны в конец файла с новой строки вместо того, чтобы его перезаписать)
	 * @return void
	 */
	public static function save_file($content, $file_path, $append = false)
	{
		$arr = explode("/", $file_path);
		$name = array_pop($arr);
		$path = implode("/", $arr);

		self::create_dir($path);

		if(! file_exists(ABSOLUTE_PATH.$file_path))
		{
			if(self::is_writable($path))
			{
				if($fp = fopen(ABSOLUTE_PATH.$file_path, "wb"))
				{
					fwrite($fp, $content);
					fclose($fp);
					return;
				}
			}
		}
		elseif(self::is_writable($file_path))
		{
			if($fp = fopen(ABSOLUTE_PATH.$file_path, ($append === false ? "wb" : "ab")))
			{
				fwrite($fp, ($append ? PHP_EOL . $content : $content));
				fclose($fp);
				return;
			}
		}
		$tmp_path = ABSOLUTE_PATH.'tmp/'.md5('files'.mt_rand(0, 99999999));
		if(! $fp = fopen($tmp_path, "wb"))
		{
			throw new File_exception('Установите права на запись (777) для папки tmp.');
		}
		fwrite($fp, $content);
		fclose($fp);

		$conn_id = self::connect_ftp();
		if($conn_id)
		{
			if (! ftp_put($conn_id, $file_path, $tmp_path, FTP_BINARY))
			{
				unlink($tmp_path);
				throw new File_exception('Не удалось сохранить файл. Проверьте данные для подключения по FTP или установите права на запись (777) для файла '.$file_path.'.');
			}
			ftp_close($conn_id);
		}
		else
		{
			unlink($tmp_path);
			throw new File_exception('Не удалось сохранить файл. '.self::$error.' Проверьте данные для подключения по FTP или установите права на запись (777) для файла '.$file_path.'.');
		}
	}

	/**
	 * Переименовывает файл
	 *
	 * @param string $name новое имя
	 * @param string $old_name старое имя
	 * @param string $path путь до папки, в которой лежит файл, относительно корня сайта
	 * @return  void
	 */
	public static function rename_file($name, $old_name, $path)
	{
		if(! file_exists(ABSOLUTE_PATH.($path ? $path.'/' : '').$old_name))
		{
			throw new File_exception('Файл '.($path ? $path.'/' : '').$old_name.' не существует.');
		}
		if(! self::is_writable(($path ? $path.'/' : '').$old_name) || ! rename(ABSOLUTE_PATH.($path ? $path.'/' : '').$old_name, ABSOLUTE_PATH.($path ? $path.'/' : '').$name))
		{
			$conn_id = self::connect_ftp();
			if($conn_id)
			{
				if (! ftp_rename($conn_id, ($path ? $path.'/' : '').$old_name, ($path ? $path.'/' : '').$name))
				{
					throw new File_exception('Не удалось переименовать. Проверьте данные для подключения по FTP или установите права на запись (777) для файла '.($path ? $path.'/' : '').$old_name.'.');
				}
				ftp_close($conn_id);
			}
			else
			{
				throw new File_exception('Не удалось переименовать. '.self::$error.' Проверьте данные для подключения по FTP или установите права на запись (777) для файла '.($path ? $path.'/' : '').$old_name.'.');
			}
		}
	}

	/**
	 * Удаляет файл
	 *
	 * @param string $file_path путь до файла относительно корня сайта
	 * @return  void
	 */
	public static function delete_file($file_path)
	{
		if(! file_exists(ABSOLUTE_PATH.$file_path))
		{
			return;
		}
		if(self::is_writable($file_path))
		{
			if(unlink(ABSOLUTE_PATH.$file_path))
			{
				return;
			}
		}
		$conn_id = self::connect_ftp();
		if($conn_id)
		{
			if (! ftp_delete($conn_id, $file_path))
			{
				throw new File_exception('Не удалось удалить. Проверьте данные для подключения по FTP или установите права на запись (777) для файла '.$file_path.'.');
			}
			ftp_close($conn_id);
		}
		else
		{
			throw new File_exception('Не удалось удалить. '.self::$error.' Проверьте данные для подключения по FTP или установите права на запись (777) для файла '.$file_path.'.');
		}
	}

	/**
	* Возврат размера файла (даже для файла > 2 Гб)
	* For file size over PHP_INT_MAX (2 147 483 647), PHP filesize function loops from -PHP_INT_MAX to PHP_INT_MAX.
	*
	* @param string $file_path путь до файла относительно корня сайта
	* @return mixed File size or false if error
	*/
	public static function file_size($file_path)
	{

		if(! file_exists(ABSOLUTE_PATH.$file_path) || ! self::is_writable($file_path)) return false;
		$size = filesize(ABSOLUTE_PATH.$file_path);
		if (! ($file = fopen(ABSOLUTE_PATH.$file_path, 'rb'))) return false;

		if ($size >= 0)
		{//Check if it really is a small file (< 2 GB)
			if (fseek($file, 0, SEEK_END) === 0)
			{//It really is a small file
				fclose($file);
				return $size;
			}
		}

		//Quickly jump the first 2 GB with fseek. After that fseek is not working on 32 bit php (it uses int internally)
		$size = PHP_INT_MAX - 1;
		if (fseek($file, PHP_INT_MAX - 1) !== 0)
		{
			fclose($file);
			return false;
		}

		$length = 1024 * 1024;
		while (!feof($file))
		{//Read the file until end
			$read = fread($file, $length);
			$size = bcadd($size, $length);
		}
		$size = bcsub($size, $length);
		$size = bcadd($size, strlen($read));

		fclose($file);
		return $size;
	}

	/**
	* Возврат хэш файла
	*
	* @param string $file_path путь до файла относительно корня сайта
	* @return string
	*/
	public static function hash_file($file_path)
	{
		if(! file_exists(ABSOLUTE_PATH.$file_path)) return false;
		return hash_file('md5', ABSOLUTE_PATH.$file_path);
	}

	/**
	 * Проверяет существует ли папка
	 *
	 * @param string $dir_path путь до папки относительно корня сайта
	 * @return void
	 */
	public static function check_dir($dir_path)
	{
		if(! is_dir(ABSOLUTE_PATH.$dir_path))
		{
			throw new File_exception('Ошибочный путь.');
			return false;
		}
		return true;
	}

	/**
	 * Создает папку, если она не создана
	 *
	 * @param string $path путь до папки-родителя относительно корня сайта
	 * @param boolean $access_close доступ к папке извне будет закрыт
	 * @return  void
	 */
	public static function create_dir($path, $access_close = false)
	{
		if(is_dir(ABSOLUTE_PATH.($path ? $path.'/' : '')))
		{
			if($access_close && ! file_exists(ABSOLUTE_PATH.($path ? $path.'/' : '').'.htaccess'))
			{
				$text = 'Options -Indexes
				<files *>
				<IfModule mod_authz_core.c>
				Require all denied
				</IfModule>
				<IfModule !mod_authz_core.c>
				Order deny,allow
				Deny from all
				</IfModule>
				</files>';

				self::save_file($text, ($path ? $path.'/' : '').'.htaccess');
			}
			return;
		}

		$arr = explode("/", $path);
		$name = array_pop($arr);
		$path = '';
		foreach($arr as $a)
		{
			$path .= ($path ? '/' : '').$a;
			self::create_dir($path);
		}
		if(self::is_writable($path) && mkdir(ABSOLUTE_PATH.($path ? $path.'/' : '').$name))
		{
			chmod(ABSOLUTE_PATH.($path ? $path.'/' : '').$name, 0777);
		}
		else
		{
			$conn_id = self::connect_ftp();
			if($conn_id)
			{
				if (! ftp_mkdir($conn_id, ($path ? $path.'/' : '').$name))
				{
					throw new File_exception('Не удалось создать папку '.$name.'. Проверьте данные для подключения по FTP или установите права на запись (777) для папки '.($path ? $path : '/').'.');
				}
				ftp_close($conn_id);
			}
			else
			{
				throw new File_exception('Не удалось создать папку '.$name.'. '.self::$error.' Проверьте данные для подключения по FTP или установите права на запись (777) для папки '.($path ? $path : '/').'.');
			}
		}
		if($access_close)
		{
			$text = 'Options -Indexes
			<files *>
			<IfModule mod_authz_core.c>
			Require all denied
			</IfModule>
			<IfModule !mod_authz_core.c>
			Order deny,allow
			Deny from all
			</IfModule>
			</files>';

			self::save_file($text, ($path ? $path.'/' : '').$name.'/.htaccess');
		}
	}

	/**
	 * Переименовывает папку
	 *
	 * @param string $name новое имя папки
	 * @param string $old_name старое имя папки
	 * @param string $path путь до папки-родителя относительно корня сайта
	 * @return  void
	 */
	public static function rename_dir($name, $old_name, $path)
	{
		if(! self::is_writable(($path ? $path.'/' : '').$old_name) || ! rename(ABSOLUTE_PATH.($path ? $path.'/' : '').$old_name, ($path ? $path.'/' : '').$name))
		{
			$conn_id = self::connect_ftp();
			if($conn_id)
			{
				if (! ftp_rename($conn_id, ($path ? $path.'/' : '').$old_name, ($path ? $path.'/' : '').$name))
				{
					throw new File_exception('Не удалось переименовать. Проверьте данные для подключения по FTP или установите права на запись (777) для папки '.($path ? $path.'/' : '').$old_name.'.');
				}
				ftp_close($conn_id);
			}
			else
			{
				throw new File_exception('Не удалось переименовать. '.self::$error.' Проверьте данные для подключения по FTP или установите права на запись (777) для папки '.($path ? $path.'/' : '').$old_name.'.');
			}
		}
	}

	/**
	 * Копирует папку
	 *
	 * @param string $source полный путь до исходной папки
	 * @param string $path путь до папки-назначения относительно корня сайта
	 * @return  void
	 */
	public static function copy_dir($source, $path)
	{
		if(! is_dir($source))
		{
			return;
		}
		$dir = opendir($source);
		while (($file = readdir($dir)) !== false)
		{
			if($file == '.' || $file == '..')
				continue;

			if(is_dir($source.'/'.$file))
			{
				self::copy_dir($source.'/'.$file, $path.'/'.$file);
			}
			else
			{
				self::copy_file($source.'/'.$file, $path.'/'.$file);
			}
		}
	}

	/**
	 * Удаляет папку
	 *
	 * @param string $dir_path путь до папки относительно корня сайта
	 * @return  void
	 */
	public static function delete_dir($dir_path)
	{
		if(! $dir_path)
		{
			throw new File_exception('Нельзя удалить корневую директорию.');
		}
		if(! is_dir(ABSOLUTE_PATH.$dir_path))
		{
			return;
		}
		if(self::is_writable($dir_path))
		{
			$conn_id = false;
			self::delete_recursive($dir_path, $conn_id);
		}
		else
		{
			$conn_id = self::connect_ftp();
			if($conn_id)
			{
				self::delete_recursive($dir_path, $conn_id);
			}
			else
			{
				throw new File_exception('Не удалось удалить. '.self::$error.' Проверьте данные для подключения по FTP или установите права на запись (777) для папки '.$dir_path.'.');
			}
		}
	}

	/**
	 * Определяет, доступны ли файл или папка для записи
	 *
	 * @param string $path путь до файла или папки относительно корня сайта
	 * @param boolean $ftp учитывать возможность редактирования по FTP
	 * @return boolean
	 */
	public static function is_writable($path, $ftp = false)
	{
		if($ftp && FTP_HOST && FTP_LOGIN && FTP_PASSWORD)
		{
			return true;
		}
		/*if(is_file(ABSOLUTE_PATH.$path))
		{
			if(is_writable(ABSOLUTE_PATH.$path))
			{
				$path_dir = preg_replace('/(\/)([^\/]+)$/', '', ABSOLUTE_PATH.$path);
				return is_writable($path_dir);
			}
			else
			{
				return false;
			}
		}
		else
		{*/
			return is_writable(ABSOLUTE_PATH.$path);
		//}
	}

	/**
	 * Сжимает JS и CSS файлы
	 *
	 * @param string|array $path путь до файла относительно корня сайта
	 * @param string $type тип: css, js
	 * @return string
	 */
	public static function compress($path, $type)
	{
		static $clear;
		if(MOD_DEVELOPER || IS_DEMO)
		{
			return $path;
		}

		if(! in_array($type, array('js', 'css')))
			return $path;

		if(! is_array($path))
		{
			$path = array($path);
		}
		$name = '';
		foreach($path as $p)
		{
			if(! $clear)
			{
				$clear = true;
				clearstatcache();
			}
			$name .= $p.filemtime(ABSOLUTE_PATH.$p).' ';
		}

		if(! defined('PHP_VERSION_ID'))
		{
			$version = phpversion();
			$version = explode('.', $version);
			define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
		}

		$name = md5($name).'.'.$type;
		if(! file_exists(ABSOLUTE_PATH.'cache/'.$type.'/'.$name))
		{
			$code = '';
			switch($type)
			{
				case 'css':
					foreach($path as $p)
					{
						$c = file_get_contents(ABSOLUTE_PATH.$p);
						self::$cache["dir"] = str_replace(strrchr($p, '/'), '', $p);
						self::$cache["dir"] = preg_replace('/custom\/[^\/]+\//', '', self::$cache["dir"]);
						$c = preg_replace_callback('/url\((\"|\')*([^)]+?)(\"|\')*\)/', array('File', '_compress_css_url'), $c);
						$code .= $c;
					}
					$php_version_min = 50400; // PHP 5.4
					if (PHP_VERSION_ID < $php_version_min)
					{
						Custom::inc('plugins/minify/php5.3/minify.php');
						Custom::inc('plugins/minify/php5.3/css.php');
					}
					else
					{
						Custom::inc('plugins/minify/php5.4/minify.php');
						Custom::inc('plugins/minify/php5.4/css.php');
					}
					$minifier = new CSS_Minify($code);
					$code = $minifier->minify();
					break;

				case 'js':
					foreach($path as $p)
					{
						$code .= file_get_contents(ABSOLUTE_PATH.$p);
					}
					$php_version_min = 50400; // PHP 5.4
					if (PHP_VERSION_ID < $php_version_min)
					{
						Custom::inc('plugins/minify/php5.3/minify.php');
						Custom::inc('plugins/minify/php5.3/js.php');
					}
					else
					{
						Custom::inc('plugins/minify/php5.4/minify.php');
						Custom::inc('plugins/minify/php5.4/js.php');
					}
					$minifier = new JS_Minify($code);
					$code = $minifier->minify();
					break;
			}
			self::save_file(trim($code), 'cache/'.$type.'/'.$name);
		}
		return 'cache/'.$type.'/'.$name;
	}

	/**
	 * Удаляет папку рекурсивно
	 *
	 * @param string $path путь до папки относительно корня сайта
	 * @return  void
	 */
	private static function delete_recursive($path, &$conn_id)
	{
		$dir = opendir(ABSOLUTE_PATH.$path);
		while (($file = readdir($dir)) !== false)
		{
			if($file == '.' || $file == '..')
				continue;

			if(is_dir(ABSOLUTE_PATH.$path.'/'.$file))
			{
				self::delete_recursive($path.'/'.$file, $conn_id);
			}
			else
			{
				if($conn_id)
				{
					if (! ftp_delete($conn_id, $path.'/'.$file))
					{
						ftp_close($conn_id);
						throw new File_exception('Не удалось удалить. Проверьте данные для подключения по FTP или установите права на запись (777) для файла '.$path.'/'.$file.'.');
					}
				}
				else
				{
					if(! self::is_writable($path.'/'.$file) || ! unlink(ABSOLUTE_PATH.$path.'/'.$file))
					{
						throw new File_exception('Не удалось удалить. Проверьте данные для подключения по FTP или установите права на запись (777) для файла '.$path.'/'.$file.'.');
					}
				}
			}
		}
		closedir($dir);
		if($conn_id)
		{
			if (! ftp_rmdir($conn_id, $path))
			{
				ftp_close($conn_id);
				throw new File_exception('Не удалось удалить. Проверьте данные для подключения по FTP или установите права на запись (777) для папки '.$path.'.');
			}
		}
		else
		{
			if(! self::is_writable($path) || ! rmdir(ABSOLUTE_PATH.$path))
			{
				throw new File_exception('Не удалось удалить. Проверьте данные для подключения по FTP или установите права на запись (777) для папки '.$path.'.');
			}
		}
	}

	/**
	 * Пробует установить FTP-соединение
	 *
	 * @return resource идентификатор соединения с FTP сервером
	 */
	private static function connect_ftp()
	{
		self::$error = '';
		if(! defined('FTP_HOST') || ! defined('FTP_LOGIN') || ! defined('FTP_PASSWORD') || ! FTP_HOST || ! FTP_LOGIN || ! FTP_PASSWORD)
		{
			return false;
		}
		$host = FTP_HOST;
		$port = null;
		if(strpos($host, ':') !== false)
		{
			list($host, $port) = explode(':', FTP_HOST, 2);
		}
		if(! $conn_id = ftp_connect($host, $port))
		{
			self::$error = 'Ошибка подключения по FTP. Хост не найден.';
			return false;
		}
		if(! ftp_login($conn_id, FTP_LOGIN, FTP_PASSWORD))
		{
			ftp_close($conn_id);
			self::$error = 'Ошибка подключения по FTP. Указаны неверные данные для подлкючения.';
			return  false;
		}
		ftp_pasv($conn_id, true);
		if (! ftp_chdir($conn_id, FTP_DIR))
		{
			ftp_close($conn_id);
			self::$error = 'Неправильно задан относительный путь.';
			return  false;
		}
		return $conn_id;
	}

	static private function _compress_css_url($res)
	{
		if (substr($res[2], 0, 4) == 'http' || substr($res[2], 0, 2) == '//')
		{
			if(strpos($res[2], MAIN_DOMAIN) === false)
			{
				return $res[0];
			}
			else
			{
				$res[2] = preg_replace('/^'.preg_quote(BASE_PATH, '/').'/', '', $res[2]);
				$dir = '';
			}
		}
		if (substr($res[2], 0, 4) == 'data') // data:image base64 в CSS
		{
			return $res[0];
		}
		$query = '';
		if(strpos($res[2], '#') !== false)
		{
			list($res[2], $query) = explode('#', $res[2]);
			$query = '#'.$query;
		}
		if(strpos($res[2], '?') !== false)
		{
			list($res[2], $query) = explode('?', $res[2]);
			$query = '?'.$query;
		}
		if(! isset($dir))
		{
			$count = substr_count($res[2], '../');
			$res[2] = str_replace('../', '', $res[2]);
			$adir = explode('/', self::$cache["dir"]);
			for($i = 0; $i < $count; $i++)
			{
				array_pop($adir);
			}
			$dir = implode('/', $adir).($adir ? '/' : '');
		}
		return 'url("/'.(REVATIVE_PATH ? REVATIVE_PATH.'/' : '').Custom::path($dir.$res[2]).$query.'")';
	}

	/**
	 * Удаляет файл/директорию
	 *
	 * @param string $path путь до папки относительно корня сайта
	 * @return  void
	 */
	public static function rm($path)
	{
		if(! $path)
		{
			throw new File_exception('Нельзя удалить корневую директорию.');
		}

		if(is_file(ABSOLUTE_PATH.$path))
		{
			return unlink(ABSOLUTE_PATH.$path);
		}
		if(is_dir(ABSOLUTE_PATH.$path))
		{
			foreach(scandir(ABSOLUTE_PATH.$path) as $p)
			{
				if(($p != '.') && ($p != '..'))
				{
					self::rm($path.DIRECTORY_SEPARATOR.$p);
				}
			}
			return rmdir($path);
		}
		return false;
	}

	/**
	 * Рекурсивный поиск файлов с использованием шаблона соответствия или не соответствия
	 * По умолчанию (без флагов) в результат поиска включаются файлы,
	 * не соответствующие шаблону поиска для файлов и не соответствующие шаблону поиска для директорий.
	 * Сами шаблоны поиска и для файлов, и для директорий распространяются только на первый уровень вложенности.
	 *
	 * @param string $path путь до папки относительно корня сайта
	 * @param string $file_pattern шаблон для файлов согласно правилам, используемым в функции preg_match
	 * @param string $dir_pattern шаблон для директорий согласно правилам, используемым в функции preg_match
	 * @param integer $depth глубина вложенности, просматриваемая функцией (без ограничений = -1)
	 * @param integer $flag флаг или комбинация флагов поиска:
	 * RGLOD_PATTERN_FILE_COINCIDE - соответствие шаблону для файлов (без флага - не соответствие шаблону),
	 * RGLOD_PATTERN_FILE_GLOB - соответствие шаблону для файлов на каждом уровне вложенности (без флага - соответствие на первом уровне),
	 * RGLOD_PATTERN_DIR_COINCIDE - соответствие шаблону для директорий (без флага - не соответствие шаблону),
	 * RGLOD_PATTERN_DIR_GLOB - соответствие шаблону для директорий на каждом уровне вложенности (без флага - соответствие на первом уровне),
	 * RGLOD_FILE_GLOB - включать файлы, начинающиеся с точки, и директории-точки (точка и две точки, если есть вышестоящая директория)
	 * Без данного флага - исключать файлы, начинающиеся с точки, и директории-точки
	 * @return array
	 */
	public static function rglob($path = '', $file_pattern = false, $dir_pattern = false, $depth = 0, $flag = 0)
	{
		$dir_separator = '/';
		if(defined('DIRECTORY_SEPARATOR') && DIRECTORY_SEPARATOR)
		{
			$dir_separator = DIRECTORY_SEPARATOR;
		}
		$path = trim($path);
		if($path != '' && ! preg_match('/'.preg_quote($dir_separator, '/').'$/', $path))
		{
			$path .= $dir_separator;
		}
		$pattern = '*'; $flags = 0;
		if($flag & RGLOD_FILE_GLOB)
		{
			$pattern = '{,.}*'; $flags = GLOB_NOSORT|GLOB_BRACE;
		}
		if(! $files = glob(ABSOLUTE_PATH.$path.$pattern, $flags))
		{
			$files = array();
		}
		$depth = $depth > -1 ? $depth : -1;
		foreach($files as $key => $file)
		{
			$file = preg_replace('/^'.preg_quote('.'.$dir_separator, '/').'/', '', $file, 1);
			$file = preg_replace('/^'.preg_quote(ABSOLUTE_PATH, '/').'/', '', $file, 1);
			if(is_dir($file))
			{
				unset($files[$key]);
				continue;
			}
			$file_name = $file;
			if($path)
			{
				$file_name = preg_replace('/^'.preg_quote($path, '/').'/', '', $file_name, 1);
			}
			if($file_pattern)
			{
				$coincide = !! preg_match($file_pattern, $file_name);
				if(($flag & RGLOD_PATTERN_FILE_COINCIDE) != $coincide)
				{
					unset($files[$key]);
					continue;
				}
			}
			$files[$key] = $file;
		}
		if($depth == 0 || ! $dirs = glob(dirname(ABSOLUTE_PATH.$path.$pattern).$dir_separator.'*', GLOB_ONLYDIR|GLOB_NOSORT))
		{
			$dirs = array();
		}
		if($depth > 0) $depth--;
		foreach ($dirs as $key => $dir)
		{
			$dir = preg_replace('/^'.preg_quote(ABSOLUTE_PATH, '/').'/', '', $dir, 1);
			if(! $dir = trim($dir))
			{
				continue;
			}
			$dir_name = $dir;
			if($path)
			{
				$dir_name = preg_replace('/^'.preg_quote($path, '/').'/', '', $dir_name, 1);
			}
			if($dir_pattern)
			{
				$coincide = !! preg_match($dir_pattern, $dir_name);
				if(($flag & RGLOD_PATTERN_DIR_COINCIDE) != $coincide)
				{
					continue;
				}
			}
			if(! $fs = self::rglob($dir.$dir_separator, (($flag & RGLOD_PATTERN_FILE_GLOB) ? $file_pattern : false), (($flag & RGLOD_PATTERN_DIR_GLOB) ? $dir_pattern : false), $depth, $flag))
			{
				continue;
			}
			$files = array_merge($files, $fs);
			unset($fs);
			unset($dirs[$key]);
		}
		unset($dirs);
		return $files;
	}

	/**
	 * Рекурсивное определение размеров файлов с использованием шаблона соответствия или не соответствия
	 * По умолчанию (без флагов) в результат поиска включаются файлы,
	 * не соответствующие шаблону поиска для файлов и не соответствующие шаблону поиска для директорий.
	 * Сами шаблоны поиска и для файлов, и для директорий распространяются только на первый уровень вложенности.
	 *
	 * @param string $path путь до папки относительно корня сайта
	 * @param string $file_pattern шаблон для файлов согласно правилам, используемым в функции preg_match
	 * @param string $dir_pattern шаблон для директорий согласно правилам, используемым в функции preg_match
	 * @param integer $depth глубина вложенности, просматриваемая функцией (без ограничений = -1)
	 * @param integer $flag флаг или комбинация флагов поиска:
	 * RGLOD_PATTERN_FILE_COINCIDE - соответствие шаблону для файлов (без флага - не соответствие шаблону),
	 * RGLOD_PATTERN_FILE_GLOB - соответствие шаблону для файлов на каждом уровне вложенности (без флага - соответствие на первом уровне),
	 * RGLOD_PATTERN_DIR_COINCIDE - соответствие шаблону для директорий (без флага - не соответствие шаблону),
	 * RGLOD_PATTERN_DIR_GLOB - соответствие шаблону для директорий на каждом уровне вложенности (без флага - соответствие на первом уровне),
	 * RGLOD_FILE_GLOB - включать файлы, начинающиеся с точки, и директории-точки (точка и две точки, если есть вышестоящая директория)
	 * Без данного флага - исключать файлы, начинающиеся с точки, и директории-точки
	 * @return mixed File size or false if error
	 */
	public static function rglob_size($path = '', $file_pattern = false, $dir_pattern = false, $depth = 0, $flag = 0)
	{
		$size = 0;
		$dir_separator = '/';
		if(defined('DIRECTORY_SEPARATOR') && DIRECTORY_SEPARATOR)
		{
			$dir_separator = DIRECTORY_SEPARATOR;
		}
		$path = trim($path);
		if($path != '' && ! preg_match('/'.preg_quote($dir_separator, '/').'$/', $path))
		{
			$path .= $dir_separator;
		}
		$pattern = '*'; $flags = 0;
		if($flag & RGLOD_FILE_GLOB)
		{
			$pattern = '{,.}*'; $flags = GLOB_NOSORT|GLOB_BRACE;
		}
		if(! $files = glob(ABSOLUTE_PATH.$path.$pattern, $flags))
		{
			$files = array();
		}
		$depth = $depth > -1 ? $depth : -1;
		foreach($files as $key => $file)
		{
			$file = preg_replace('/^'.preg_quote('.'.$dir_separator, '/').'/', '', $file, 1);
			$file = preg_replace('/^'.preg_quote(ABSOLUTE_PATH, '/').'/', '', $file, 1);
			if(is_dir($file))
			{
				unset($files[$key]);
				continue;
			}
			$file_name = $file;
			if($path)
			{
				$file_name = preg_replace('/^'.preg_quote($path, '/').'/', '', $file_name, 1);
			}
			if($file_pattern)
			{
				$coincide = !! preg_match($file_pattern, $file_name);
				if(($flag & RGLOD_PATTERN_FILE_COINCIDE) != $coincide)
				{
					unset($files[$key]);
					continue;
				}
			}
			$file_size = self::file_size($file);
			$diff = PHP_INT_MAX - $file_size;
			if($diff < $size)
			{
				return false;
			}
			$size += $file_size;
			unset($files[$key]);
		}
		unset($files);
		if($depth == 0 || ! $dirs = glob(dirname(ABSOLUTE_PATH.$path.$pattern).$dir_separator.'*', GLOB_ONLYDIR|GLOB_NOSORT))
		{
			$dirs = array();
		}
		if($depth > 0) $depth--;
		foreach ($dirs as $key => $dir)
		{
			$dir = preg_replace('/^'.preg_quote(ABSOLUTE_PATH, '/').'/', '', $dir, 1);
			if(! $dir = trim($dir))
			{
				continue;
			}
			$dir_name = $dir;
			if($path)
			{
				$dir_name = preg_replace('/^'.preg_quote($path, '/').'/', '', $dir_name, 1);
			}
			if($dir_pattern)
			{
				$coincide = !! preg_match($dir_pattern, $dir_name);
				if(($flag & RGLOD_PATTERN_DIR_COINCIDE) != $coincide)
				{
					continue;
				}
			}
			$file_size = self::rglob_size($dir.$dir_separator, (($flag & RGLOD_PATTERN_FILE_GLOB) ? $file_pattern : false), (($flag & RGLOD_PATTERN_DIR_GLOB) ? $dir_pattern : false), $depth, $flag);
			$diff = PHP_INT_MAX - $file_size;
			if($diff < $size)
			{
				return false;
			}
			$size += $file_size;
			unset($dirs[$key]);
		}
		unset($dirs);
		return $size;
	}

	/**
	 * Возвращает уникальное имя
	 *
	 * @param string $dir путь до папки относительно корня сайта
	 * @param string $basename исходное имя файла
	 * @param string $extension имя расширения (FALSE - уникальное имя директории, TRUE или заполнено - уникальное имя файла)
	 * @return string
	 */
	public static function tempnam($dir, $basename = '', $extension = false)
	{
		if(! $dir || ! is_dir(ABSOLUTE_PATH.$dir))
		{
			return false;
		}
		if(! $basename)
		{
			$basename = substr(strtolower($this->diafan->uid()), 0, 8);
		}

		$i = 1;
		do
		{
			$theme = $basename.($i > 1 ? '_'.$i : '').($extension && is_string($extension) ? '.'.$extension : '');
			$path = $dir.'/' . $theme;
			if($extension) $result = file_exists(ABSOLUTE_PATH.$path);
			else $result = is_dir(ABSOLUTE_PATH.$path);
			$i++;
		}
		while($result && $i < PHP_INT_MAX);
		return (! $result ? $theme : false);
	}

	/**
	 * Добавляет файлы из директории в архив
	 *
	 * @param string $path относительный путь до директории или файла
	 * @param string $filename относительный путь до ZIP-файла
	 * @param string $relative сохранять относительный путь
	 * @return boolean
	 */
	public static function zip($path, $filename, $relative = true)
	{
		if(! $path || ! $filename || ! class_exists('ZipArchive')
		|| (! is_dir(ABSOLUTE_PATH.$path) && ! file_exists(ABSOLUTE_PATH.$path))) return false;
		if(file_exists(ABSOLUTE_PATH.$filename))
		{
			unlink(ABSOLUTE_PATH.$filename);
		}
		$zip = new ZipArchive;
		if ($zip->open(ABSOLUTE_PATH.$filename, ZipArchive::CREATE) === true)
		{
			if(is_dir(ABSOLUTE_PATH.$path))
			{
				if ($dir = opendir(ABSOLUTE_PATH.$path))
				{
					while (($file = readdir($dir)) !== false)
					{
						if ($file == '.' || $file == '..')
							continue;

						if(is_dir(ABSOLUTE_PATH.$path.'/'.$file))
						{
							self::add_to_zip($zip, $path.'/'.$file, ($relative ? $path : false));
						}
						else
						{
							$zip->addFile(ABSOLUTE_PATH.$path.'/'.$file, ($relative ? $file : $path.'/'.$file));
						}
					}
					closedir($dir);
				}
			}
			elseif(file_exists(ABSOLUTE_PATH.$path))
			{
				$file = basename(ABSOLUTE_PATH.$path);
				$zip->addFile(ABSOLUTE_PATH.$path, ($relative ? $file : $path));
			}
			$zip->close();
		}
		return true;
	}

	/**
	 * Добавляет файлы из директории в архив
	 *
	 * @param object $zip архив
	 * @param string $dir относительный путь до директории
	 * @param string $base_dir базовый путь до директории
	 * @return void
	 */
	private static function add_to_zip(&$zip, $dir, $base_dir = false)
	{
		$rdir = $dir;
		if($base_dir !== false && mb_strpos($dir, $base_dir) === 0)
		{
			$rdir = mb_substr($dir, mb_strlen($base_dir));
		}
		if ($ddir = opendir(ABSOLUTE_PATH.$dir))
		{
			while (($file = readdir($ddir)) !== false)
			{
				if ($file != '.' && $file != '..')
				{
					if(is_dir(ABSOLUTE_PATH.$dir.'/'.$file))
					{
						self::add_to_zip($zip, $dir.'/'.$file, $base_dir);
					}
					else
					{
						$zip->addFile(ABSOLUTE_PATH.$dir.'/'.$file, $rdir.'/'.$file);
					}
				}
			}
			closedir($ddir);
		}
	}
}

/**
 * File_exception
 *
 * Исключение для работы с файлами
 */
class File_exception extends Exception{}

/**
 * File_const
 *
 * Исключение для работы с файлами
 */
// Флаг rglob: соответствие шаблону для файлов
// Без данного флага - не соответствие шаблону для файлов
if(! defined('RGLOD_PATTERN_FILE_COINCIDE')) define('RGLOD_PATTERN_FILE_COINCIDE', 1 << 0);       // 00001
// Флаг rglob: соответствие шаблону для файлов на каждом уровне вложенности
// Без данного флага - соответствие шаблону для файлов на первом уровне вложенности
if(! defined('RGLOD_PATTERN_FILE_GLOB')) define('RGLOD_PATTERN_FILE_GLOB', 1 << 1);               // 00010
// Флаг rglob: соответствие шаблону для директорий
// Без данного флага - не соответствие шаблону для директорий
if(! defined('RGLOD_PATTERN_DIR_COINCIDE')) define('RGLOD_PATTERN_DIR_COINCIDE', 1 << 2);         // 00100
// Флаг rglob: соответствие шаблону для директорий на каждом уровне вложенности
// Без данного флага - соответствие шаблону для директорий только на первом уровне вложенности
if(! defined('RGLOD_PATTERN_DIR_GLOB')) define('RGLOD_PATTERN_DIR_GLOB', 1 << 3);                 // 01000
// Флаг rglob: включать файлы, начинающиеся с точки, и директории-точки
// (точка и две точки, если есть вышестоящая директория)
// Без данного флага - исключать файлы, начинающиеся с точки, и директории-точки
if(! defined('RGLOD_FILE_GLOB')) define('RGLOD_FILE_GLOB', 1 << 4);                               // 10000
// Флаг rglob:
if(! defined('RGLOD_ALL')) define(
	'IMPORT_ALL',
	RGLOD_PATTERN_FILE_COINCIDE | RGLOD_PATTERN_FILE_GLOB
	| RGLOD_PATTERN_DIR_COINCIDE | RGLOD_PATTERN_DIR_GLOB
	| RGLOD_FILE_GLOB
);                                                                                                // 11111

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
 * Gzip
 *
 * Сжатие страницы
 */
class Gzip
{
	/**
	 * var boolean маркер передачи необходимых HTTP заголовков
	 */
	static private $header = false;

	/**
	 * var boolean маркер инициализации сжатия.
	 */
	static private $do_gzip_compress = false;

	/**
	 * Инициирует сжатие. Включается буферизация вывода, определяются и передаются необходимые HTTP заголовки
	 *
	 * @return void
	 */
	public static function init()
	{
		self::$do_gzip_compress = false;
		$Config_gzip = 1;
		if ($Config_gzip == 1)
		{
			$phpver 	= phpversion();
			$useragent 	= getenv('HTTP_USER_AGENT');
			$can_zip 	= getenv('HTTP_ACCEPT_ENCODING');

			$gzip_check 	= 0;
			$zlib_check 	= 0;
			$gz_check	= 0;
			$zlibO_check	= 0;
			$sid_check	= 0;
			if (strpos($can_zip, 'gzip') !== false)
			{
				$gzip_check = 1;
			}
			if (extension_loaded('zlib'))
			{
				$zlib_check = 1;
			}
			if (function_exists('ob_gzhandler'))
			{
				$gz_check = 1;
			}
			if (ini_get('zlib.output_compression'))
			{
				$zlibO_check = 1;
			}
			if (ini_get('session.use_trans_sid'))
			{
				$sid_check = 1;
			}

			if ($phpver >= '4.0.4pl1' && (strpos($useragent, 'compatible') !== false || strpos($useragent, 'Gecko') !== false))
			{
				if (($gzip_check || getenv('---------------')) && $zlib_check && $gz_check && ! $zlibO_check && ! $sid_check)
				{
					//ob_start('ob_gzhandler');
					// TO_DO: Fix bug - Warning: ob_start(): output handler 'ob_gzhandler' cannot be used twice
					if(! in_array('ob_gzhandler', ob_list_handlers())) ob_start('ob_gzhandler');
					else ob_start();
					return;
				}
			}
			elseif ($phpver > '4.0')
			{
				if ($gzip_check)
				{
					if ($zlib_check)
					{
						self::$do_gzip_compress = true;
						ob_start();
						ob_implicit_flush(0);

						return;
					}
				}
			}
		}
		ob_start();
	}

	/**
	 * Выдает сжатые данные, очищает (стирает) буфер вывода и отключает буферизацию вывода
	 *
	 * @return void
	 */
	public static function do_gzip()
	{
		global $diafan;

		if(! $diafan->_site->nozip && self::$do_gzip_compress && ! self::$header)
		{ // передаются необходимые HTTP заголовки
			header('Content-Encoding: gzip');
			self::$header = true;
		}

		if((! defined('IS_ADMIN') || ! IS_ADMIN)
		   && empty($_POST) && defined('CACHE_EXTREME') && CACHE_EXTREME
		   && ! preg_match('/^'.ADMIN_FOLDER.'(\/|$)/', $_GET["rewrite"])
		   && ! $diafan->_users->id
		   && ! $diafan->_site->nocache)
		{
			Custom::inc('includes/cache.php');

			$cache = new Cache;

			$cache->save(ob_get_contents(), getenv('QUERY_STRING'), 'cache_extreme');
		}
		// ежедневное сохранение кеша
		if (! Dev::$is_error
		&& (! defined('IS_DEMO') || ! IS_DEMO)
		&& (! defined('IS_ADMIN') || ! IS_ADMIN) && empty($_GET["rewrite"])
		&& empty($_POST)
		&& ! $diafan->_users->id
		&& file_exists(ABSOLUTE_PATH.'index.html') && date('d') <> date('d', filemtime(ABSOLUTE_PATH.'index.html'))
		&& getenv('SCRIPT_FILENAME') == ABSOLUTE_PATH.'index.php')
		{
			if(($f = fopen(ABSOLUTE_PATH.'index.html', 'w')))
			{
				fwrite($f, ob_get_contents());
				fclose($f);
			}
		}

		if (self::$do_gzip_compress && ! $diafan->_site->nozip)
		{
			$gzip_contents = ob_get_contents();
			ob_end_clean();

			$gzip_size = strlen($gzip_contents);
			$gzip_crc  = crc32($gzip_contents);

			$gzip_contents = gzcompress($gzip_contents, 3);
			$gzip_contents = substr($gzip_contents, 0, strlen($gzip_contents) - 4);

			echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
			echo $gzip_contents;
			echo pack('V', $gzip_crc);
			echo pack('V', $gzip_size);
		}
		else
		{
			if(ob_get_level()) ob_end_flush();
		}
	}
}

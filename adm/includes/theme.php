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
 * Theme_admin
 *
 * Представление в административной части
 */
class Theme_admin extends Diafan
{
	/**
	 * @var array страницы административной части
	 */
	public $admin_pages = array();

	/**
	 * Подключает шаблон
	 *
	 * @return void
	 */
	public function show_theme()
	{
		if ($this->diafan->_users->id)
		{
			$site_theme = file_get_contents(ABSOLUTE_PATH.'adm/themes/admin.php');
		}
		else
		{
			$site_theme = file_get_contents(ABSOLUTE_PATH.'adm/themes/adminauth.php');
		}
		$this->get_function_in_theme($site_theme);

		echo '<!-- версия '.VERSION_CMS.'-->';
	}

	/**
	 * Парсит шаблон
	 *
	 * @return void
	 */
	private function get_function_in_theme($text)
	{
		$text = preg_replace('/\<\?php(.*)\?\>/s', '', $text);
		$regexp = '/(<insert ([^>]*)>)/im';
		$tokens = preg_split($regexp, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
		$cnt = count($tokens);
		echo $tokens[0];
		$i = 1;
		while ($i < $cnt)
		{
			$i++;
			$att_string = $tokens[$i++];
			$data = $tokens[$i++];
			$attributes = $this->parse_attributes($att_string);
			$this->start_element($attributes);
			echo $data;
		}
	}

	/**
	 * Парсит атрибуты шаблонного тега
	 *
	 * @return array
	 */
	private function parse_attributes($string)
	{
		$entities = array ( '&lt;'   => '<',
							'&gt;'   => '>',
							'&amp;'  => '&',
							'&quot;' => '"',
							'['      => '<',
							']'      => '>',
							'`'      => '"' );

		$attributes = array ();
		$match = array ();
		preg_match_all('/([a-zA-Z_0-9]+)="((?:\\\.|[^"\\\])*)"/U', $string, $match);
		for ($i = 0; $i < count($match[1]); $i++)
		{
			$attributes[strtolower($match[1][$i])] = strtr((string)$match[2][$i], $entities);
		}
		return $attributes;
	}

	/**
	 * Выполняет действие, заданное в шаблонном тэге: выводит информацию или подключает шаблонную функцию
	 *
	 * @param array атрибуты шаблонного тега
	 * @return void
	 */
	private function start_element($attributes)
	{
		if (empty( $attributes['name'] ))
		{
			if (! empty( $attributes['value'] ))
			{
				echo $this->diafan->_($attributes['value']);
			}
			return;
		}

		switch ($attributes['name'])
		{
			case "path":
				echo BASE_PATH.'adm/';
				break;

			case "path_url":
				echo BASE_PATH_HREF;
				break;

			case "base_url":
				echo BASE_URL;
				break;

			case "protocol":
				echo 'http'.(IS_HTTPS ? "s" : '');
				break;

			case "userid":
				echo $this->diafan->_users->id;
				break;

			case "userlogin":
				echo $this->diafan->_($this->diafan->configmodules("mail_as_login", "users") ? 'E-mail' : 'Логин');
				break;

			case "userfio":
				echo $this->diafan->_users->fio;
				break;

			case "errauth":
				echo '<div class="auth_error">'.$this->diafan->_users->errauth.'</div>';
				break;

			default:
				if (is_callable(array($this, $attributes['name'])))
				{
					call_user_func_array (array(&$this, $attributes['name']), array($attributes));
				}
		}
	}

	/**
	 * Выводит заголовок. Используется между тегами <title></title> в шапке сайта
	 *
	 * @return void
	 */
	private function show_title()
	{
		echo ($this->diafan->_admin->name && $this->diafan->_users->id ? $this->diafan->_($this->diafan->_admin->name).' - ' : '' )."CMS ".BASE_URL;
	}

	/**
	 * Выводит меню
	 *
	 * @return void
	 */
	private function show_menu()
	{
		echo '<div class="nav__toggle">
			<i class="fa fa-caret-left"></i>
			<i class="fa fa-navicon"></i>
			<i class="fa fa-caret-right"></i>
		</div>

		<div class="nav__item nav__item_first">
			<a href="'.BASE_PATH_HREF.'">
				<i class="fa fa-home"></i>
				<span>'.$this->diafan->_('События').'</span>
			</a>
		</div>';
		$groups = array ( 1 => $this->diafan->_('Контент'),
			4 => $this->diafan->_('Интернет магазин'),
			2 => $this->diafan->_('Интерактив'),
			6 => $this->diafan->_('DIAFAN.CMS'),
			7 => $this->diafan->_('Расширения CMS'),
			3 => $this->diafan->_('Сервис'),
			5 => $this->diafan->_('Настройки')
		);
		$rows = $this->diafan->admin_pages[0];

		foreach ($rows as $row)
		{
			if (! $this->diafan->_users->roles('init', $row["rewrite"]))
			{
				continue;
			}
			$row["site_id"] = 0;
			if($this->diafan->configmodules("admin_page", $row["rewrite"]))
			{
				$rows_sites = DB::query_fetch_all("SELECT id, name".$this->diafan->_languages->site." AS name FROM {site} WHERE trash='0' AND act".$this->diafan->_languages->site."='1' AND module_name='%s'", $row["rewrite"]);
				if($rows)
				{
					foreach ($rows_sites as $row_site)
					{
						$row["name"] = $row_site["name"];
						$row["site_id"] = $row_site["id"];
						$group[$row["group_id"]][] = $row;
					}
				}
				else
				{
					$group[$row["group_id"]][] = $row;
				}
			}
			else
			{
				$row["name"] = $this->diafan->_($row["name"]);
				$group[$row["group_id"]][] = $row;
			}
		}

		foreach ($groups as $group_id => $name)
		{
			if(empty($group[$group_id])) continue;

			echo '<div class="nav__sep"></div><div class="nav__heading">'.$name.'</div>';

			$rows = $group[$group_id];
			foreach ($rows as $row)
			{
				if (($row["id"] == $this->diafan->_admin->id || $row["id"] == $this->diafan->_admin->parent_id) && (empty($row["site_id"]) || $row["site_id"] == $this->diafan->_route->site))
				{
					$act = true;
				}
				else
				{
					$act = false;
				}
				$count = 0;
				if(strpos($row["rewrite"], '/') !== false)
				{
					list($module, $file) = explode('/', $row["rewrite"], 2);
				}
				else
				{
					$module = $row["rewrite"];
					$file = '';
				}
				if(Custom::exists('modules/'.$module.'/admin/'.$module.'.admin'.($file ? '.'.$file : '').'.count.php'))
				{
					Custom::inc('modules/'.$module.'/admin/'.$module.'.admin'.($file ? '.'.$file : '').'.count.php');
					$class = ucfirst($module).'_admin'.($file ? '_'.$file : '').'_count';
					if (method_exists($class, 'count'))
					{
						eval('$class_count_menu = new '.$class.'($this->diafan);');
						$count = $class_count_menu->count($row["site_id"]);
					}
				}

				echo '
				<div class="nav__item'.($act ? ' active' : '').'">
					<a href="'.BASE_PATH_HREF.$row["rewrite"] .($row["site_id"] ? '/site'.$row["site_id"] : ''). '/">
						<i class="fa fa-puzzle-piece fa-'.(! empty($row["icon_name"]) ? $row["icon_name"] : str_replace('/', '-', $row["rewrite"])).'"></i>
						<span>'.$row["name"].'</span>';
						if($count)
						{
							echo ' <span class="nav__info">'.$count.'</span>';
						}
					echo '</a>
				</div>';
			}
		}
		echo '<div class="nav__sep"></div>';
		if ($this->diafan->_users->roles('init', 'admin'))
		{
			echo '<a href="'.BASE_PATH_HREF.'admin/" class="settings-link"><i class="fa fa-gear"></i></a>';
		}
	}

	/**
	 * Выводит навигации по сайту «Хлебные крошки»
	 *
	 * @return void
	 */
	public function show_breadcrumb()
	{
		echo '<div class="breadcrumbs">
			<a href="'.BASE_PATH_HREF.'"><i class="fa fa-home"></i>'.$this->diafan->_('События').'</a>';

		if(! empty($_GET["action"]))
		{
			echo '<i class="fa fa-angle-right"></i> <a href="'.BASE_PATH_HREF.$this->diafan->_admin->rewrite.'/">'.$this->diafan->_admin->name.'</a>';
		}

		if ($this->diafan->_admin->rewrite)
		{
			if ($this->diafan->_admin->parent_id)
			{
				$parent_admin = DB::query_fetch_array("SELECT name, rewrite FROM {admin} WHERE id=%d LIMIT 1", $this->diafan->_admin->parent_id);
				if($parent_admin["rewrite"] != $this->diafan->_admin->rewrite)
				{
					echo '<i class="fa fa-angle-right"></i> <a href="'.BASE_PATH_HREF.$parent_admin["rewrite"].'/">'.$this->diafan->_($parent_admin["name"]).'</a>';
				}
			}
			if ($this->diafan->is_action("edit") && strpos($this->diafan->_admin->rewrite, '/config') === false || $this->diafan->_route->site || $this->diafan->_route->cat)
			{
				echo '<i class="fa fa-angle-right"></i> <a href="'.BASE_PATH_HREF.$this->diafan->_admin->rewrite.'/">'.$this->diafan->_($this->diafan->_admin->name).'</a>';
			}
		}

		if ($this->diafan->config("element") && $this->diafan->_route->cat)
		{
			if ($this->diafan->config("element_multiple") && $this->diafan->_route->cat)
			{
				$categories = $this->diafan->get_parents($this->diafan->_route->cat, $this->diafan->table.'_category');
			}

			$current_link = BASE_PATH_HREF.$this->diafan->_admin->rewrite.'/';

			$categories[] = $this->diafan->_route->cat;
			$categories_name = DB::query_fetch_key_value("SELECT ".($this->diafan->config('category_no_multilang') ? "name" : "[name]").", id FROM {".$this->diafan->table."_category} WHERE id IN (%h)", implode(",", $categories), "id", "name");
			if (! empty($categories_name))
			{
				foreach ($categories as $p)
				{
					echo '<i class="fa fa-angle-right"></i> <a href="'.$current_link.'cat'.$p.'/">'.$categories_name[$p].'</a>';
				}
			}
		}

		if ($this->diafan->variable_list('plus') && $this->diafan->_route->parent && $this->diafan->is_variable("name"))
		{
			$parents = $this->diafan->get_parents($this->diafan->_route->parent, $this->diafan->table);
			$parents[] = $this->diafan->_route->parent;
			if ($parents)
			{
				$current_link = BASE_PATH_HREF.$this->diafan->_admin->rewrite.'/';
				$parents_name = DB::query_fetch_key_value("SELECT ".($this->diafan->variable_multilang("name") ? "[name]" : "name").", id FROM {".$this->diafan->table."} WHERE id IN (%h)", implode(",", $parents), "id", "name");

				foreach ($parents as $p)
				{
					if(! empty($parents_name[$p]))
					{
						echo '<i class="fa fa-angle-right"></i> <a href="'.$current_link.'parent'.$p.'/">'.$parents_name[$p].'</a>';
					}
				}
			}
		}
		echo '<span style="float: right;"><a href="'.BASE_PATH_HREF.'?help=1"><i class="tooltip fa fa-question-circle" title="'.$this->diafan->_('Открыть руководство пользователя').'"></i></a></span>';
		echo '</div>';
	}

	/**
	 * Выводит заголовок контента
	 *
	 * @return void
	 */
	public function show_h1()
	{
		echo '
		<div class="heading">
			<div class="heading__unit">';

		if(Custom::exists('modules/'.$this->diafan->_admin->module.'/admin/'.$this->diafan->_admin->module.'.admin.config.php') && ! $this->diafan->config('config') && $this->diafan->_users->roles('init', $this->diafan->_admin->module.'/config'))
		{
			echo '<a href="'.BASE_PATH_HREF.$this->diafan->_admin->module.'/config/" class="settmd-link"><i class="fa fa-gear"></i>'.$this->diafan->_('Настройки модуля').'</a>';
		}
		echo $this->diafan->_($this->diafan->_admin->title_module);

		$row = array();
		if($this->diafan->_users->roles('init', 'site'))
		{
			if ($this->diafan->config('element_site') && $this->diafan->_route->site)
			{
				$row = DB::query_fetch_array("SELECT [name], id, [act] FROM {site} WHERE id=%d", $this->diafan->_route->site);
			}
			else
			{
				if($this->diafan->_route->site)
				{
					$row = DB::query_fetch_array("SELECT id, [name], [act] FROM {site} WHERE module_name='%h' AND trash='0' AND id=%d LIMIT 1", $this->diafan->_admin->module, $this->diafan->_route->site);
				}
				if(! $this->diafan->_route->site || empty($row))
				{
					$row = DB::query_fetch_array("SELECT id, [name], [act] FROM {site} WHERE module_name='%h' AND trash='0' LIMIT 1", $this->diafan->_admin->module);
				}
			}
		}
		if($row)
		{
			echo '<span class="heading__in">
				'.$this->diafan->_('Подключен к странице').': <a href="'.BASE_PATH_HREF.'site/edit'.$row["id"].'/">'.$row["name"].'</a>';
			if(! $row["act"])
			{
				echo ' <span class="red">('.$this->diafan->_('неактивна').')</span>';
			}
			echo '</span>';
		}
		$this->show_submenu();

		echo '</div>
		</div>';
	}

	/**
	 * Выводит ссылку "Добавить элемент"
	 *
	 * @return void
	 */
	public function show_addnew()
	{
		echo '<div class="header__link header__link_pp">
			<a href="'.BASE_PATH_HREF.$this->diafan->_admin->rewrite.'/addnew1/">
				<i class="fa fa-file-o"></i>
				<span>'.$this->diafan->_('Добавить элемент').'</span>
			</a>';

		$html = array();
		foreach($this->diafan->admin_pages[0] as $row)
		{
			if (! $this->diafan->_users->roles('init', $row["rewrite"]) || ! $row["add"])
			{
				continue;
			}
			$html[] = '<div class="popup__item">
				<a href="'.BASE_PATH_HREF.$row["rewrite"].'/addnew1/">
					<i class="fa fa-'.str_replace('/', '-', $row["rewrite"]).' fa-puzzle-piece"></i>
					'.$row["add_name"].'
				</a>
			</div>';
		}
		if($html)
		{
			echo '<div class="header__popup">'.implode(' ', $html).'</div>';
		}
		echo '</div>';
	}

	/**
	 * Выводит ссылку на документацию модуля
	 *
	 * @return void
	 */
	public function show_docs()
	{
		if($this->diafan->_admin->docs)
		{
			echo ' | <a href="'.$this->diafan->_admin->docs.'">'.$this->diafan->_('Документация модуля').'</a>';
		}
	}

	/**
	 * Выводит подменю
	 *
	 * @return void
	 */
	private function show_submenu()
	{
		if ($this->diafan->_admin->parent_id)
		{
			$id = $this->diafan->_admin->parent_id;
		}
		else
		{
			$id = $this->diafan->_admin->id;
		}
		if(empty($this->diafan->admin_pages[$id]))
		{
			return;
		}

		$rows = $this->diafan->admin_pages[$id];

		foreach ($rows as $row)
		{
			if(strpos($row["rewrite"], '/config') !== false)
				continue;

			if (! $this->diafan->_users->roles('init', $row["rewrite"]))
				continue;

			$rs[] = $row;
		}
		if(empty($rs) || count($rs) == 1 && ($rs[0]["rewrite"] == $this->diafan->_admin->rewrite || $this->diafan->config('config')))
			return;

		echo '<div class="tabs">';
		foreach ($rs as $row)
		{
			$count = 0;
			if(strpos($row["rewrite"], '/') !== false)
			{
				list($module, $file) = explode('/', $row["rewrite"], 2);
			}
			else
			{
				$module = $row["rewrite"];
				$file = '';
			}
			if(Custom::exists('modules/'.$module.'/admin/'.$module.'.admin'.($file ? '.'.$file : '').'.tab_count.php'))
			{
				Custom::inc('modules/'.$module.'/admin/'.$module.'.admin'.($file ? '.'.$file : '').'.tab_count.php');
				$class = ucfirst($module).'_admin'.($file ? '_'.$file : '').'_tab_count';
				if (method_exists($class, 'count'))
				{
					eval('$class_count_menu = new '.$class.'($this->diafan);');
					$count = $class_count_menu->count();
				}
			}

			echo '<a href="'.BASE_PATH_HREF.$row["rewrite"].'/'.( $this->diafan->_route->site ? 'site'.$this->diafan->_route->site.'/' : '' ).'" class="tabs__item'.($row["rewrite"] == $this->diafan->_admin->rewrite ? ' tabs__item_active' : '').'"><span>'.$this->diafan->_($row['name']).'</span>';
			if($count)
			{
				echo ' <span class="nav__info">'.$count.'</span>';
			}
			echo '</a>';
		}
		echo '</div>';
	}

	/**
	 * Выводит ссылки на альтернативные языковые версии сайта
	 *
	 * @return void
	 */
	private function show_languages()
	{
		if (count($this->diafan->_languages->all) < 2)
		{
			return;
		}

		echo '<div class="header__lang">';

		$current_i = 0;
		foreach ($this->diafan->_languages->all as $i => $language)
		{
			if($language["id"] == _LANG)
			{
				$current_lang = $language;
				if($i > 1)
				{
					$current_i = $i;
				}
			}
		}
		$langs = $this->diafan->_languages->all;
		if($current_i)
		{
			$langs = array($langs[0], $current_lang);
			foreach ($this->diafan->_languages->all as $i => $language)
			{
				if($i > 0 && $language["id"] != _LANG)
				{
					$langs[] = $language;
				}
			}
		}

		foreach ($langs as $i => $language)
		{
			if($i == 2)
			{
				echo '<div class="lang-more">
				<span>'.$this->diafan->_('Ещё').'</span>
				<i class="fa fa-angle-down"></i>

				<div class="header__popup">';
			}
			if($i > 1)
			{
				echo '<div class="popup__item">';
			}
			echo '<a href="http'.(IS_HTTPS ? "s" : '').'://'.BASE_URL.'/'.ADMIN_FOLDER.'/'.( ! $language["base_admin"] ? $language["shortname"].'/' : '' ).( $_GET["rewrite"] ? $_GET["rewrite"].'/' : '' ).'" class="';
			if($language["id"] == _LANG)
			{
				echo ' active';
			}
			if($i == 1 && count($langs) == 2)
			{
				echo ' lang-last';
			}
			echo '">'.$language["name"].'</a>';
			if($i > 1)
			{
				echo '</div>';
			}
		}
		if($i > 1)
		{
			echo '</div></div>';
		}
		echo '<div class="lang-more lang-more_adapt">
					<span>'.$current_lang["name"].'</span>
					<i class="fa fa-angle-down"></i>

					<div class="header__popup">';
					foreach ($this->diafan->_languages->all as $i => $language)
					{
						if($language["id"] == _LANG)
							continue;

						echo '<div class="popup__item"><a href="http'.(IS_HTTPS ? "s" : '').'://'.BASE_URL.'/'.ADMIN_FOLDER.'/'.( ! $language["base_admin"] ? $language["shortname"].'/' : '' ).( $_GET["rewrite"] ? $_GET["rewrite"].'/' : '' ).'">'.$language["name"].'</a></div>';
					}
					echo '</div>
				</div>
			</div>';
	}

	/**
	 * Выводит основной контент страницы
	 *
	 * @return void
	 */
	private function show_body()
	{
		$this->diafan->show_breadcrumb();
		$this->diafan->show_h1();

		echo '<div class="ctr-overlay"></div>
		<div class="content">';

		$this->diafan->show_error_message();
		$this->diafan->show_one_shot();

		if($this->diafan->config('element_site') && empty($this->diafan->sites) && empty($this->diafan->_route->site) && ! $this->diafan->config('config') && ! $this->diafan->is_action("edit"))
		{
			echo '<div class="error">'.$this->diafan->_('Прикрепите модуль к странице сайта.').'</div>';
			return false;
		}

		$this->diafan->show_module_contents();
		echo $this->diafan->module_contents;

		echo '<div class="hide check_hash_user">'.$this->diafan->_users->get_hash().'</div>';
		echo '</div>';
	}

	/**
	 * Выводит системное сообщение
	 *
	 * @return void
	 */
	public function show_error_message()
	{
		$messages = array(
				1 => 'Изменения сохранены!',
				5 => 'Сообщение отправлено',
				6 => 'Сообщение не может быть отправлено, так как не заполнены обязательные поля (e-mail, вопрос, ответ).',
				7 => 'Внимание! Не установлена библиотека GD. Работа модуля невозможна. Обратитесь в техподдержку вашего хостинга!',
				8 => 'Нельзя добавить несколько параметров, влияющих на цену, для одного раздела!',
				9 => 'Рассылка не отправлена, так как не заполнено поле «Содержание».',
			);

		if ($this->diafan->_route->error && ! empty($messages[$this->diafan->_route->error]))
		{
			echo '<div class="error">'.$this->diafan->_($messages[$this->diafan->_route->error]).'</div>';
		}

		if ($this->diafan->_route->error == 10)
		{
			echo '<div class="error">'.$this->diafan->_('Внимание! Помечено на удаление более 1000 элементов, работа системы может замедлиться! Рекомендуется <a href="%s">очистить корзину</a>.', BASE_PATH_HREF.'trash/').'</div>';
		}

		if ($this->diafan->_route->success && ! empty($messages[$this->diafan->_route->success]))
		{
			echo '<div class="ok">'.$this->diafan->_($messages[$this->diafan->_route->success]).'</div>';
		}
	}

	/**
	 * Выводит сообщение для модуля
	 *
	 * @return void
	 */
	public function show_one_shot()
	{
		if(! $message = $this->diafan->get_one_shot())
		{
			return;
		}
		echo '<div class="one_shot">';
		if(is_array($message)) echo implode("\n", $message);
		else echo $message;
		echo '</div>';
	}

	/**
	 * Выводит информацию о CMS
	 *
	 * @return void
	 */
	public function show_brand($a)
	{
		$number = (int)preg_replace('/[^0-9]+/', '', $a["id"]);
		global $brandtext;
		include_once(ABSOLUTE_PATH.Custom::path('adm/brand.php'));
		echo $brandtext[$number];
	}

	/**
	 * Формирует часть HTML-шапки
	 *
	 * @return void
	 */
	public function show_head()
	{
		$files = array(
			'css/jquery.imgareaselect/imgareaselect-default.css',
			'css/jquery.imgareaselect/imgareaselect-animated.css',
			'css/jquery.imgareaselect/imgareaselect-deprecated.css',
			'css/custom-theme/jquery-ui-1.8.18.custom.css',
			'css/jquery-ui.css',
			'css/jquery.formstyler.css',
			'adm/css/main.css',
			'adm/css/custom.css',
		);
		foreach($files as &$f)
		{
			$f = Custom::path($f);
		}
		$compress_files = File::compress($files, 'css');
		if(is_array($compress_files))
		{
			foreach($compress_files as $file)
			{
				echo '<link href="'.BASE_PATH.$file.'" rel="stylesheet" type="text/css" media="all">';
			}
		}
		else
		{
			echo '<link href="'.BASE_PATH.$compress_files.'" rel="stylesheet" type="text/css" media="all">';
		}
		if ($this->diafan->is_action("edit"))
		{
			echo '<link rel="stylesheet" href="'.BASE_PATH.File::compress(Custom::path('css/jquery.fancybox.min.css'), 'css')
			.'" type="text/css" media="screen"'.' title="main stylesheet" charset="utf-8" />';
		}
		if($this->diafan->_admin->rewrite)
		{
			$file = 'modules/'.$this->diafan->_admin->module.'/admin/css/';
			if(strpos($this->diafan->_admin->rewrite, '/') !== false)
			{
				$file .= str_replace('/', '.', preg_replace('/\//', '.admin.', $this->diafan->_admin->rewrite, 1));
			}
			else
			{
				$file .= $this->diafan->_admin->rewrite.'.admin';
			}
			if(Custom::exists($file.'.css'))
			{
				$this->diafan->_admin->css_view[] = $file.'.css';
			}
			if($this->diafan->is_action("edit") && Custom::exists($file.'.edit.css'))
			{
				$this->diafan->_admin->css_view[] = $file.'.edit.css';
			}

			// TO_DO: дополнительное подключение файлов *.custom.css
			if(Custom::exists($file.'.custom.css'))
			{
				$this->diafan->_admin->css_view[] = $file.'.custom.css';
			}
			if($this->diafan->is_action("edit") && Custom::exists($file.'.edit.custom.css'))
			{
				$this->diafan->_admin->css_view[] = $file.'.edit.custom.css';
			}
		}
		$css_view = array();
		foreach($this->diafan->_admin->css_view as $path)
		{
			if(in_array($path, $css_view))
				continue;
			$css_view[] = $path;
			if (substr($path, 0, 4) != 'http')
			{
				$path = BASE_PATH.File::compress(Custom::path($path), 'css');
			}
			echo '<link rel="stylesheet" href="'.$path.'" type="text/css">';
		}
	}

	/**
	 * Подключает JS-файлы
	 *
	 * @return void
	 */
	public function show_js()
	{
		$lang = $this->diafan->_languages->base_admin();

		if(! defined('SOURCE_JS'))
		{
			define('SOURCE_JS', 1);
		}
		switch (SOURCE_JS)
		{
			// Yandex CDN
			case 2:
				echo '<!--[if lt IE 9]><script src="//yandex.st/jquery/1.10.2/jquery.min.js"></script><![endif]-->
				<!--[if gte IE 9]><!-->
				<script type="text/javascript" src="//yandex.st/jquery/2.0.3/jquery.min.js" charset="UTF-8"><</script><!--<![endif]-->
				<script type="text/javascript" src="//yandex.st/jquery-ui/1.10.3/jquery-ui.min.js" charset="UTF-8"></script>
				<script type="text/javascript" src="//yandex.st/jquery/form/3.14/jquery.form.min.js" charset="UTF-8"></script>';
				break;

			// Microsoft CDN
			case 3:
				echo '<!--[if lt IE 9]><script src="//ajax.aspnetcdn.com/ajax/jquery/jquery-1.10.2.min.js"></script><![endif]-->
				<!--[if gte IE 9]><!-->
				<script type="text/javascript" src="//ajax.aspnetcdn.com/ajax/jquery/jquery-2.0.3.min.js" charset="UTF-8"><</script><!--<![endif]-->
				<script type="text/javascript" src="//ajax.aspnetcdn.com/ajax/jquery.ui/1.10.3/jquery-ui.min.js" charset="UTF-8"></script>
				<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.form.min.js').'" charset="UTF-8"></script>';
				break;

			// CDNJS CDN
			case 4:
				echo '<!--[if lt IE 9]><script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script><![endif]-->
				<!--[if gte IE 9]><!-->
				<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js" charset="UTF-8"><</script><!--<![endif]-->
				<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js" charset="UTF-8"></script>
				<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.min.js" charset="UTF-8"></script>';
				break;

			// jQuery CDN
			case 5:
				echo '<!--[if lt IE 9]><script src="//code.jquery.com/jquery-1.10.2.min.js"></script><![endif]-->
				<!--[if gte IE 9]><!-->
				<script type="text/javascript" src="//code.jquery.com/jquery-2.0.3.min.js" charset="UTF-8"><</script><!--<![endif]-->
				<script type="text/javascript" src="//code.jquery.com/ui/1.10.3/jquery-ui.min.js" charset="UTF-8"></script>
				<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.form.min.js').'" charset="UTF-8"></script>';
				break;

			// Hosting
			case 6:
				echo '<!--[if lt IE 9]><script src="'.BASE_PATH.Custom::path('js/jquery-1.10.2.min.js').'"></script><![endif]-->
				<!--[if gte IE 9]><!-->
				<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery-2.0.3.min.js').'" charset="UTF-8"><</script><!--<![endif]-->
				<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery-ui.min.js').'" charset="UTF-8"></script>
				<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.form.min.js').'" charset="UTF-8"></script>';
				break;

			// Google CDN
			case 1:
			default:
				echo '<!--[if lt IE 9]><script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script><![endif]-->
				<!--[if gte IE 9]><!-->
				<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js" charset="UTF-8"><</script><!--<![endif]-->
				<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js" charset="UTF-8"></script>
				<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.form.min.js').'" charset="UTF-8"></script>';
				break;
		}
		if(in_array('visitors', $this->diafan->installed_modules) && $this->diafan->_visitors->counter_is_enable())
		{
			switch (SOURCE_JS)
			{
				// Yandex CDN
				case 2:
					echo '<script type="text/javascript" src="//yandex.st/jquery/cookie/1.0/jquery.cookie.min.js" charset="UTF-8"></script>';
					break;

				// CDNJS CDN
				case 4:
					echo '<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js" charset="UTF-8"></script>';
					break;

				// Microsoft CDN
				case 3:

				// jQuery CDN
				case 5:

				// Hosting
				case 6:

				// Google CDN
				case 1:
				default:
					echo '<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.cookie.min.js').'" charset="UTF-8"></script>';
					break;
			}
		}

		echo '
		<script type="text/javascript">
		var MAX_EXECUTION_TIME = "'. ini_get('max_execution_time') .'";
		</script>

		<script src="'.BASE_PATH.Custom::path('js/jquery.formstyler.js').'"></script>
		<script src="'.BASE_PATH.Custom::path('adm/js/main.js').'"></script>
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/timepicker.js').'" charset="UTF-8"></script>
		<script type="text/javascript">
			jQuery(function(e){
			e.datepicker.setDefaults(e.datepicker.regional["'.$lang.'"]);
			e.timepicker.setDefaults(e.timepicker.regional["'.$lang.'"]);
			});
		</script>
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.imgareaselect.min.js').'"></script>
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.maskedinput.js').'" charset="UTF-8"></script>
                <script type="text/javascript"  src="'.BASE_PATH.Custom::path('js/jquery.touchSwipe.min.js').'" charset="UTF-8"></script>
		<script src="'.BASE_PATH.Custom::path('js/extsrc.js').'"></script>

		<!--[if lte IE 8]>
			<script src="'.BASE_PATH.Custom::path('js/ie/html5shiv.js').'"></script>
		<![endif]-->

		<!--[if !IE]><!-->
			<script>if(/*@cc_on!@*/false){document.documentElement.className+=\' ie10\';}</script>
		<!--<![endif]-->

		<script type="text/javascript" asyncsrc="'.BASE_PATH.File::compress(Custom::path('adm/js/admin.js'), 'js').'" charset="UTF-8"></script>';
		if($this->diafan->is_action("edit"))
		{
			echo '<link rel="stylesheet" href="'.BASE_PATH.'css/codemirror/codemirror.css">
			<link rel="stylesheet" href="'.BASE_PATH.'css/codemirror/neat.css">
			<link rel="stylesheet" href="'.BASE_PATH.'plugins/codemirror/addon/hint/show-hint.css">
			<link rel="stylesheet" href="'.BASE_PATH.'plugins/codemirror/addon/display/fullscreen.css">
			<script src="'.BASE_PATH.'js/codemirror.js"></script>
			<script src="'.BASE_PATH.'plugins/codemirror/mode/xml/xml.js"></script>
			<script src="'.BASE_PATH.'plugins/codemirror/mode/javascript/javascript.js"></script>
			<script src="'.BASE_PATH.'plugins/codemirror/addon/fold/foldcode.js"></script>
			<script src="'.BASE_PATH.'plugins/codemirror/addon/hint/xml-hint.js"></script>
			<script src="'.BASE_PATH.'plugins/codemirror/addon/hint/show-hint.js"></script>
			<script src="'.BASE_PATH.'plugins/codemirror/addon/edit/matchbrackets.js"></script>
			<script src="'.BASE_PATH.'plugins/codemirror/addon/edit/closebrackets.js"></script>
			<script src="'.BASE_PATH.'plugins/codemirror/addon/display/fullscreen.js"></script>
			<script type="text/javascript">
			var nav_box_compress = ';
			if($this->diafan->_users->config)
			{
				$cfg = unserialize($this->diafan->_users->config);
				if(! empty($cfg["nav_box_compress"]))
				{
					echo '1';
				}
				else
				{
					echo '0';
				}
			}
			else
			{
				echo '0';
			}
			echo ';
			</script>';

			echo '<script type="text/javascript" src="'.BASE_PATH.File::compress(Custom::path('adm/js/admin.edit.js'), 'js').'" charset="UTF-8"></script>';

			if(! file_exists(ABSOLUTE_PATH.'adm/htmleditor/tinymce/langs/'.$lang.'.js'))
			{
				$lang = '';
			}
			echo '<script type="text/javascript" src="'.BASE_PATH.'adm/htmleditor/tinymce/tinymce.min.js"></script>
			<script type="text/javascript">
			var base_path = "'.BASE_PATH.'";
			</script>
			<script type="text/javascript">
			var config_language = "'.$lang.'";
			</script>
			<script type="text/javascript" src="'.BASE_PATH.'adm/htmleditor/tinymce/config.js"></script>
			';
		}
		else
		{
			echo '<script type="text/javascript" asyncsrc="'.BASE_PATH.File::compress(Custom::path('adm/js/admin.show.js'), 'js').'" charset="UTF-8"></script>
			<script type="text/javascript" asyncsrc="'.BASE_PATH.File::compress(Custom::path('adm/js/admin.move.js'), 'js').'" charset="UTF-8"></script>';
		}
		if ($this->diafan->is_action("edit"))
		{
			echo '<script asyncsrc="'.BASE_PATH.Custom::path('js/jquery.fancybox.min.js').'" type="text/javascript" charset="utf-8"></script>';
		}
		if ($this->diafan->config('multiupload'))
		{
			echo '
			<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.ui.widget.js').'"></script>
			<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.iframe-transport.js').'"></script>
			<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.fileupload.js').'"></script>';
		}
		if($this->diafan->_admin->rewrite)
		{
			$file = 'modules/'.$this->diafan->_admin->module.'/admin/js/';
			if(strpos($this->diafan->_admin->rewrite, '/') !== false)
			{
				$file .= str_replace('/', '.', preg_replace('/\//', '.admin.', $this->diafan->_admin->rewrite, 1));
			}
			else
			{
				$file .= $this->diafan->_admin->rewrite.'.admin';
			}
			if(Custom::exists($file.'.js'))
			{
				$this->diafan->_admin->js_view[] = $file.'.js'; //echo '<script type="text/javascript" asyncsrc="'.BASE_PATH.File::compress(Custom::path($file.'.js'), 'js').'"></script>';
			}
			if($this->diafan->is_action("edit") && Custom::exists($file.'.edit.js'))
			{
				$this->diafan->_admin->js_view[] = $file.'.edit.js'; //echo '<script type="text/javascript" asyncsrc="'.BASE_PATH.File::compress(Custom::path($file.'.edit.js'), 'js').'"></script>';
			}

			// TO_DO: дополнительное подключение файлов *.custom.js
			if(Custom::exists($file.'.custom.js'))
			{
				$this->diafan->_admin->js_view[] = $file.'.custom.js';
			}
			if($this->diafan->is_action("edit") && Custom::exists($file.'.edit.custom.js'))
			{
				$this->diafan->_admin->js_view[] = $file.'.edit.custom.js';
			}
		}
		$js_view = array();
		foreach($this->diafan->_admin->js_view as $path)
		{
			if(in_array($path, $js_view))
				continue;
			$js_view[] = $path;
			if (substr($path, 0, 4) != 'http')
			{
				$path = BASE_PATH.File::compress(Custom::path($path), 'js');
			}
			echo '<script type="text/javascript" asyncsrc="'.$path.'"></script>';
		}
		if(! empty($this->diafan->_admin->js_code))
		{
			echo implode("\n\r", $this->diafan->_admin->js_code);

			if(in_array('visitors', $this->diafan->installed_modules))
			{
				if(! empty($this->diafan->_admin->js_code["Visitors_inc_counter"]))
					unset($this->diafan->_admin->js_code["Visitors_inc_counter"]);
				if(! empty($this->diafan->_site->js_code["Visitors_inc_counter"]))
					unset($this->diafan->_site->js_code["Visitors_inc_counter"]);
			}
		}
	}

	/**
	 * Фильтр вывода
	 *
	 * @return void
	 */
	public function show_module_contents()
	{
		if(! $this->diafan->config('config') && $this->diafan->is_action("edit"))
		{
			return;
		}
		$empty_get_nav_params = true;
		if($this->diafan->get_nav_params)
		{
			foreach ($this->diafan->get_nav_params as $get)
			{
				if($get)
				{
					$empty_get_nav_params = false;
				}
			}
		}
	}

	/**
	 * Поиск
	 *
	 * @return void
	 */
	public function show_search()
	{
		return;
		echo '<form action="'.BASE_PATH_HREF.$this->diafan->_admin->rewrite.'/'.( $this->diafan->_route->cat ? 'cat'.$this->diafan->_route->cat.'/' : '' ).'" method="GET" class="search">
			<div class="search__in">
				<input type="text" placeholder="Глобальный поиск">
				<button class="search__sub"><i class="fa fa-search"></i></button>
				<i class="fa fa-close"></i>
			</div>
			<a href="#" class="search__link"><i class="fa fa-search"></i></a>
		</form>';
	}

	/**
	 * Рандомное число
	 *
	 * @return void
	 */
	public function show_rand()
	{
		echo rand(0, 99999);
	}

	/**
	 * Информационное сообщение в демо-режиме
	 *
	 * @return void
	 */
	public function show_demo()
	{
		if(! defined('IS_DEMO') || ! IS_DEMO)
			return;

		echo '<div class="help" style="text-align: center;"><h3>
		'.$this->diafan->_('Демо-режим %s.', BASE_URL).'</h3>
		<a href="'.BASE_PATH_HREF.'?help=1">'.$this->diafan->_('Открыть руководство пользователя').'</a></div>';
		//echo '(+ <a href="http'.(IS_HTTPS ? "s" : '').'://'.BASE_URL.'/m/">'.$this->diafan->_('мобильная версия').'</a>)';
	}

	/**
	 * Шаблонная функция: подключает файл-блок шаблона.
	 *
	 * @param array $attributes атрибуты шаблонного тега
	 * @return void
	 */
	public function show_include($attributes)
	{
		if(! defined('IS_DEMO') || ! IS_DEMO)
			return;

		$attributes["file"] = str_replace('/[^a-z_0-9]+/', '', $attributes["file"]);

		Custom::inc('themes/blocks/'.$attributes["file"].'.php');
	}

	/**
	 * Шаблонная функция: выводит путь до файла с учетом кастомизации
	 *
	 * @param array $attributes атрибуты шаблонного тега
	 * path - путь до файла
	 * absolute - путь абсолютный (true - абсолютный, false - относительный)
	 * @return void
	 */
	public function custom($attributes)
	{
		if(! empty($attributes["absolute"]) && $attributes["absolute"] == 'true')
		{
			echo BASE_PATH;
		}
		echo Custom::path($attributes["path"]);
	}

	/**
	 * Формирует теги <option> при редактировании списка
	 *
	 * @param array $cats все возможные значения
	 * @param array $rows возможные значения для текущего уровня
	 * @param array $values значения
	 * @param string $marker отступ для текущего уровня
	 * @return string
	 */
	public function get_options($cats, $rows, $values, $marker = '')
	{
		$text = '';
		foreach ($rows as $row)
		{
			if(! $row)
				continue;

			$text .= '<option value="'.$row["id"].'"'.(in_array($row["id"], $values) ? ' selected' : '' ).(isset($row["rel"]) ? ' rel="'.$row["rel"].'"' : '' ).'>'.$marker.$this->diafan->short_text($row["name"], 40).'</option>';
			if (! empty( $cats[$row["id"]] ))
			{
				$text .= $this->diafan->get_options($cats, $cats[$row["id"]], $values, $marker.'&nbsp;&nbsp;');
			}
		}
		return $text;
	}

	/**
	 * Выводит предупреждение о включенном режиме технического обслуживания сайта
	 *
	 * @return void
	 */
	public function show_developer()
	{
		if((defined('MOD_DEVELOPER_TECH') && MOD_DEVELOPER_TECH) || (defined('MOD_DEVELOPER') && MOD_DEVELOPER))
		{
			echo '<div class="devoloper_tech"><a href="'.BASE_PATH_HREF.'config/" title="'.$this->diafan->_('Перейти в раздел «Параметры сайта»').'">';
			if(defined('MOD_DEVELOPER_TECH') && MOD_DEVELOPER_TECH)
			{
				echo '<span>'.$this->diafan->_('Внимание: включен режим технического обслуживания.').'</span>'.' '.'<span>'.$this->diafan->_('Сайт недоступен для посетителей.').'</span>';
			}
			elseif(defined('MOD_DEVELOPER') && MOD_DEVELOPER)
			{
				echo '<span>'.$this->diafan->_('Внимание: включен режим разработки.').'</span>'.' '.'<span>'.$this->diafan->_('Производительность сайта снижена.').'</span>';
			}
			echo '</a></div>';
		}
	}
}

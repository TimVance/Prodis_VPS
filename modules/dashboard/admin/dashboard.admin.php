<?php
/**
 * Редактирование страниц административной части сайта
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
 * Dashboard_admin
 */
class Dashboard_admin extends Frame_admin
{
	/**
	 * Выводит заголовок контента
	 *
	 * @return void
	 */
	public function show_h1()
	{
		if (! empty($_GET["help"])) {
	        echo '<div class="help">';
	        echo '<a href="'.BASE_PATH_HREF.'"><div class="fa fa-close help_close"></div></a>';
	        echo '<p>'.$this->diafan->_('Добро пожаловать в панель управления сайтом').' <a href="http'.(IS_HTTPS ? "s" : '').'://'.BASE_URL.'/">'.BASE_URL.'</a>.</p>';
	        echo '<p>'.$this->diafan->_('Это модуль событий. Здесь выводятся уведомления об активности посетителей сайта. Вы можете изменить стартовую страницу в <a href="%s">своих настройках</a>.', BASE_PATH_HREF.'users/edit'.$this->diafan->_users->id.'/').'</p>';
	        echo '<p>'.$this->diafan->_('Слева список модулей. Основной модуль - <a href="%s">Страницы сайта</a>, служит для управления структурой. В <a href="%s">Блоки на сайте</a> можно править некоторые участки сайта, например, телефоны или счетчики.', BASE_PATH_HREF.'site/', BASE_PATH_HREF.'site/blocks/').'</p>';
	        echo '<p>'.$this->diafan->_('С помощью модуля <a href="%s">Меню на сайте</a> организована навигация по сайту.', BASE_PATH_HREF.'menu/').'</p>';
	        echo '<p>'.$this->diafan->_('Все остальные модули прикрепляются к Страницам сайта и/или являются их частью.').'</p>';
	        echo '<p><strong>'.$this->diafan->_('Смотрите краткое руководство %sКак управлять сайтом%s.', '<a href="http'.(IS_HTTPS ? "s" : '').'://www.diafan.ru/dokument/full-manual/introduction/pervoe_znakomstvo_s_panelyu_upravleniya_saytom/" target="_blank">', '</a>').'</strong></p>';
	        echo '<p>'.$this->diafan->_('Возникающие вопросы задавайте в <a href="https://user.diafan.ru/support/" target="_blank">Службу поддержки</a>.').'</p>';
	        echo '</div>';
	    }

		echo '
		<div class="heading">
			<div class="heading__unit">
				'.$this->diafan->_('События').'

				<span class="btn btn_blue btn_small btn_refr" onClick="window.location.href=document.location">
					<span class="fa fa-refresh"></span>
					'.$this->diafan->_('Обновить').'
				</span>
			</div>
		</div>';
	}

	/**
	 * Выводит контент модуля
	 * @return void
	 */
	public function show()
	{
		$html = array();
		foreach($this->diafan->admin_pages as $i => $rows)
		{
			foreach($rows as $row)
			{
				if (! $this->diafan->_users->roles('init', $row["rewrite"]))
				{
					continue;
				}
				if(strpos($row["rewrite"], '/') !== false)
				{
					list($module, $file) = explode('/', $row["rewrite"], 2);
				}
				else
				{
					$module = $row["rewrite"];
					$file = '';
				}
				if(Custom::exists('modules/'.$module.'/admin/'.$module.'.admin'.($file ? '.'.$file : '').'.dashboard.php'))
				{
					Custom::inc('modules/'.$module.'/admin/'.$module.'.admin'.($file ? '.'.$file : '').'.dashboard.php');
					$class = ucfirst($module).'_admin'.($file ? '_'.$file : '').'_dashboard';


					eval('$class_count_menu = new '.$class.'($this->diafan);');
					$html[$class_count_menu->sort] = $this->list_dashboard($class_count_menu, $row["rewrite"]);
				}
			}
		}
		ksort($html);
		foreach($html as $k => $h)
		{
			echo $h;
		}
	}

	/**
	 * Выводит контент модуля
	 * @return void
	 */
	public function list_dashboard(&$class, $rewrite)
	{
		$text = '
		<div class="box-wrap">
			<div class="box-heading">
				<a href="'.BASE_PATH_HREF.$rewrite.'/">
					<span class="fa fa-'.str_replace('/', '-', $rewrite).'"></span>
					'.$this->diafan->_($class->name).'
				</a>';

		//$text .= '<i class="fa fa-close box-remove"></i>';
		$text .= '</div>';

		$func = 'show';
		if (method_exists($class, $func))
		{
			$text .= call_user_func_array (array(&$class, $func), array());
		}
		else
		{
			$fields = array('id');
			foreach($class->variables as $name => $var)
			{
				if(! empty($var["sql"]))
				{
					$fields[] = (! empty($var["multilang"]) ? '['.$name.']' : $name);
				}
			}
			$rows = DB::query_fetch_all("SELECT ".implode(',', $fields)." FROM {%s} WHERE trash='0'"
			.(! empty($class->where) ? ' AND '.$class->where : '')
			." ORDER BY ".(! empty($class->variables["created"]) ? 'created DESC' : 'id DESC')
			." LIMIT 30", $class->table);
			if($rows)
			{
				$text .= '<ul class="list list_dash do_auto_width">';

				$text .= '<li class="item item_heading">';
				foreach($class->variables as $var)
				{
					$text .= '<div class="item__th">'.$this->diafan->_($var["name"]).'</div>';
				}
				$text .= '</li>';
				foreach($rows as $row)
				{
					$text .= '<li class="item">
					<div class="item__in">';
					foreach($class->variables as $name => $var)
					{
						$var["class"] = (! empty($var["class"]) ? $var["class"] : '');
						$func = 'list_variable_'.preg_replace('/[^a-z_]+/', '', $name);
						if (method_exists($class, $func))
						{
							$text .= call_user_func_array (array(&$class, $func), array($row, $var, $rows));
						}
						elseif(! empty($var["type"]) && $var["type"] != 'none')
						{
								switch($var["type"])
								{
									case 'editor':
									case 'text':
										$result = (! empty($row[$name]) ? $this->diafan->short_text($row[$name]) : '');
										$var["class"] = 'text';
										break;

									case 'select':
										$result = $var["select"][$row[$name]];
										$var["class"] = 'text';
										break;

									case 'numtext':
									case 'floattext':
									case 'string':
										$result = (! empty($row[$name]) ? $row[$name] : '');
										$var["class"] = 'text';
										break;

									case 'date':
										$result = (! empty($row[$name]) ? date("d.m.Y", $row[$name]) : '');
										$var["class"] = 'date';
										break;

									case 'datetime':
										$result = (! empty($row[$name]) ? date("d.m.Y H:i", $row[$name]) : '');
										$var["class"] = 'date';
										break;
								}
							$text .= '<div'.(! empty($var["class"]) ? ' class="'.$var["class"].'"' : '').'>';
							if(! empty($var["link"]))
							{
								$text .= '<a href="'.BASE_PATH_HREF.$rewrite.'/edit'.$row["id"].'/">';
							}
							$text .= $result;
							if(! empty($var["link"]))
							{
								$text .= '</a>';
							}
							$text .= '</div>';
						}
					}
					$text .= '</div>
					</li>';
				}
				$text .= '</ul>';
				$text .=  '<a href="'.BASE_PATH_HREF.$rewrite.'/" class="all_dash">
					<span class="fa fa-'.str_replace('/', '-', $rewrite).'"></span>
					'.$this->diafan->_($class->name).'
				</a>';
			}
			else
			{
				$text .= '<ul class="list list_dash do_auto_width"><li class="item">
					<div class="item__in"><div class="text">'.$this->diafan->_($class->empty_rows).'</div></div></li></ul>';
			}
		}
		$text .= '</div>';
		return $text;
	}
}

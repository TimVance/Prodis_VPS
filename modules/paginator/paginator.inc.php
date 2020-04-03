<?php
/**
 * Подключение для работы с постраничной навигацией
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
 * Paginator_inc
 */
class Paginator_inc extends Diafan
{
	/**
	 * @var integer количество элементов, показанных на странице
	 */
	public $nastr = 0;

	/**
	 * @var array массив названий переменных, содержащих номера страниц
	 */
	public static $variables = array();

	/**
	 * @var integer количество ссылок постраничной навигации, показанных на одной странице
	 */
	public $navnastr = 10;

	/**
	 * @var string ссылка на первую страницу
	 */
	public $navlink;

	/**
	 * @var integer номер страницы
	 */
	public $page;

	/**
	 * @var integer номер последней страницы
	 */
	public $last_page;

	/**
	 * @var string GET переменные, участвующие в навигации для постраничного вывода
	 */
	public $get_nav;

	/**
	 * @var string шаблон части ссылки, отвечающей за передачу номера страницы
	 */
	public $urlpage = 'page%d/';

	/**
	 * @var integer порядковый номер элемента, с которого начинается вывод элементов
	 */
	public $polog = 0;

	/**
	 * @var integer количество элементов в списке
	 */
	public $nen;

	/**
	 * @var string шаблон ссылки второй и последующих страниц, если не задан используется navlink + urlpage
	 */
	private $navlink_tpl;

	/**
	 * @var boolean ссылка "Показать ещё"
	 */
	public $show_more = false;

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
				case 'variable': // название переменной, содержащей номер страницы
					$this->cache["var"][$name] = null;
					break;

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
			case 'variable': // название переменной, содержащей номер страницы
				if(! in_array($value, static::$variables))
				{
					static::$variables[] = $value;
				}
				$this->cache["var"][$name] = $value;
				break;

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
		$this->variable = 'page';
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
	 * Формирует строку навигации
	 *
	 * @param string $module имя модуля, обрабатывающего action события show_more
	 * @param string $action имя функции, обрабатывающей action события show_more
	 * @param array $attributes аргументы функции, обрабатывающей action события show_more
	 * @return array
	 */
	public function get($module = false, $action = false, $attributes = false)
	{
		$this->config();
		$links    = array();
		$rout_end =  ROUTE_END;
		$navlink  = $this->navlink;
		if(! IS_ADMIN && ROUTE_END != '/')
		{
			$navlink = preg_replace('/'.preg_quote($rout_end, '/').'$/', '', $navlink);
			if($this->urlpage == 'page%d/')
			{
				$this->urlpage = '/page%d'.ROUTE_END;
			}
		}

		$string    = '';
		if ($this->nen > $this->nastr)
		{
			if($this->page != 1)
			{
				$links[] = array(
					"type" => "first",
					"link" => $this->navlink.$this->get_nav
					);
			}
			$nen = ceil($this->nen / $this->nastr);
			$apage = 1;
			if($nen > $this->navnastr)
			{
				$apage = $this->page - ceil($this->navnastr / 2) + 1;
				if($apage < 1)
				{
					$apage = 1;
				}
			}
			$bpage = $apage + $this->navnastr - 1;
			if ($bpage > $nen)
			{
				$bpage = $nen;
			}
			if ($nen > $this->navnastr && $apage > 1)
			{
				if ($this->navlink_tpl)
				{
					$url = sprintf($this->navlink_tpl, $this->page - 1);
				}
				else
				{
					$url = $navlink.sprintf($this->urlpage, $this->page - 1);
				}
				$links[] = array(
						 "type" => "previous",
						 "link" => $url.$this->get_nav
						);
			}
			for ($i = $apage; $i <= $bpage; $i++)
			{
				if ($this->page == $i)
				{
					$links[] = array(
							 "type" => "current",
							 "name" => $i
							);
				}
				else
				{
					if ($this->navlink_tpl)
					{
						$url = sprintf($this->navlink_tpl, $i);
					}
					else
					{
						$url = $navlink.sprintf($this->urlpage, $i);
					}
					$links[] = array(
							 "type" => "default",
							 "name" => $i,
							 "link" => ($i == 1 ? $this->navlink : $url).$this->get_nav
							);
				}
			}
			if ($nen > $this->navnastr && $bpage != $nen)
			{
				if ($this->navlink_tpl)
				{
					$url = sprintf($this->navlink_tpl, $this->page + 1);
				}
				else
				{
					$url = $navlink.sprintf($this->urlpage, $this->page + 1);
				}
				$links[] = array(
						 "type" => "next",
						 "nen"  => $nen,
						 "link" => $url.$this->get_nav
						);
			}
			if($nen != $this->page)
			{
				$links[] = array(
							"type" => "last",
							"link" => $navlink.sprintf($this->urlpage, $nen).$this->get_nav
							);
			}
			if($this->last_page > $this->page)
			{
				if ($this->navlink_tpl)
				{
					$url = sprintf($this->navlink_tpl, $this->page + 1);
				}
				else
				{
					$url = $navlink.sprintf($this->urlpage, $this->page + 1);
				}

				if(! IS_ADMIN)
				{
					$mode = !! $action;
					$module = in_array($module, $this->diafan->installed_modules) ? $module : false;
					if(! $module || ! $action)
					{
						$d_b = debug_backtrace(); $level = 1;
						if(! $module)
						{
							$module = ! empty($d_b[$level]) && ! empty($d_b[$level]["class"]) ? $d_b[$level]["class"] : false;
							if($module)
							{
								list($module) = explode('_', $module);
								$module = lcfirst($module);
							}
							$module = ! empty($module) ? $module : $this->diafan->current_module;
						}
						if(! $action)
						{
							$action = ! empty($d_b[$level]) && ! empty($d_b[$level]["function"]) ? $d_b[$level]["function"] : false;
							// TO_DO: если аналагичный метод описан в класс Модуль_action, то будет задействован именно такой метод. По умолчанию задействуется метод в классе Модуль_model. То есть при необходимости можно перегрузить дефолтное поведение, создав аналогичный метод в класс Модуль_action.
							$mode = $action && Controller::method_exists($module, 'action', $action) ? true : false;
						}
					}
					if($this->show_more || $this->diafan->configmodules("show_more", $module))
					{
						$links["more"] = array(
								 "type" => "more",
								 "mode" => $mode ? 'action' : 'model',
								 "link" => $url.$this->get_nav,
								 "module" => $module,
								 "action" => $mode ? $action : $action,
								 "attributes" => is_array($attributes) ? $attributes : false,
								 "name" => $this->diafan->_('Показать ещё', false),
								 "uid" => $this->diafan->uid(),
								);
					}
				}
			}
		}

		return $links;
	}

	/**
	 * Рассчитывает параметры постраничной навигации
	 *
	 * @return void
	 */
	private function config()
	{
		if (! IS_ADMIN)
		{
			if($this->variable == 'page')
			{
				$this->page = $this->diafan->_route->page;
				$args = array_merge(array("page"), $this->diafan->_route->rewrite_variable_names);
				$this->navlink = $this->diafan->_route->current_link($args);
				$this->navlink_tpl = $this->diafan->_route->current_link($this->diafan->_route->rewrite_variable_names, array("page" => "%d"));
				if(! $this->nastr && $this->diafan->configmodules("nastr"))
				{
					$this->nastr = $this->diafan->configmodules("nastr");
				}
			}
			else
			{
				$v = $this->variable;
				$this->page     = $this->diafan->_route->$v;
				$this->navlink  = $this->diafan->_route->current_link($v);
				$this->navlink_tpl = $this->diafan->_route->current_link("", array($v => "%d"));
				$this->urlpage = $v.'%d'.ROUTE_END;
			}
		}
		else
		{
			if($this->urlpage == 'page%d/')
			{
				$this->diafan->_paginator->page    = $this->diafan->_route->page;
			}
			if ($this->diafan->_users->admin_nastr)
			{
				$this->diafan->_paginator->nastr = $this->diafan->_users->admin_nastr;
			}
			else
			{
				$this->diafan->_paginator->nastr = $this->diafan->nastr;
			}
		}

		if ($this->page)
		{
			if ($this->page == 1)
			{
				$url = 'http'.(IS_HTTPS ? "s" : '').'://'.getenv("HTTP_HOST").getenv('REQUEST_URI');
				if(! $query = parse_url($url, PHP_URL_QUERY)) $query = '';
				if(! $fragment = parse_url($url, PHP_URL_FRAGMENT)) $fragment = '';
				$query = (! empty($query) ? '?'.$query : '').(! empty($fragment) ? '#'.$fragment : '');
				$redirect = $this->diafan->_route->current_link("page") . $query;
				$this->diafan->redirect(BASE_PATH_HREF.$redirect, 301);
			}
			$this->polog = ($this->page - 1) * $this->nastr;
			if (($this->page - 1) * $this->nastr >= $this->nen)
			{
				if(IS_ADMIN)
				{
					if(preg_match('/rewrite=index\.php\/(.*)&rewrite=(.*)/', getenv('QUERY_STRING'), $m))
					{
						$query = $m[1];
					}
					else
					{
						$query = getenv('QUERY_STRING');
					}
					$query = str_replace(array('page'.$this->page, 'rewrite=index.php/'), array('page1', ''), $query);
				}
				else
				{
					if($level = ob_get_level())
					{
						for ($i=0; $i < $level; $i++)
						{
							ob_end_clean();
						}
					}
					Custom::inc('includes/404.php');
				}
			}
		}
		elseif ($this->page === '0')
		{
			if(! IS_ADMIN)
			{
				if($level = ob_get_level())
				{
					for ($i=0; $i < $level; $i++)
					{
						ob_end_clean();
					}
				}
				Custom::inc('includes/404.php');
			}
		}
		else
		{
			$this->page  = 1;
			$this->polog = 0;
		}
		if (! $this->nastr)
		{
			$this->nastr = 10;
		}
		$this->last_page = ceil($this->nen / $this->nastr);
		$this->last_page = $this->last_page < $this->page ? $this->page : $this->last_page;
	}
}

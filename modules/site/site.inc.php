<?php
/**
 * Подключение модуля
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
 * Site_inc
 */
class Site_inc extends Model
{
	/**
	 * @var integer номер текущей страницы, уникальный идентификатор каждой страницы сайта
	 */
	public $id;

	/**
	 * @var string название текущей страницы
	 */
	public $name;

	/**
	 * @var string данные из meta-тега *keywords* текущей страницы
	 */
	public $keywords;

	/**
	 * @var string данные из meta-тега *description* текущей страницы
	 */
	public $descr;

	/**
	 * @var integer номер страницы родителя
	 */
	public $parent_id;

	/**
	 * @var integer не показывать заголовок h1 текущей страницы, да/нет (1/0)
	 */
	public $title_no_show;

	/**
	 * @var integer не индексировать текущую страницу, да/нет (1/0)
	 */
	public $noindex;

	/**
	 * @var string заголовок текущей страницы из тега *title*
	 */
	public $title_meta;

	/**
	 * @var string канонический тег для текущей страницы
	 */
	public $canonical;

	/**
	 * @var integer время редактирования текущей страницы, в UNIX-формате
	 */
	public $timeedit;

	/**
	 * @var string имя файла шаблона дизайна текущей страницы
	 */
	public $theme;

	/**
	 * @var string JavaScript-код
	 */
	public $js;

	/**
	 * @var string модуль, прикрепленный к текущей странице
	 */
	public $module;

	/**
	 * @var string контент текущей страницы
	 */
	public $text;

	/**
	 * @var string ЧПУ текущей страницы, для страницы *http://site.ru/news/popular/novost/* в переменной будет "news/popular/novost"
	 */
	public $rewrite;

	/**
	 * @var array часть навигации «Хлебные крошки»
	 */
	public $breadcrumb;

	/**
	 * @var string заголовок страницы, сформированный автоматически прикрепленным модулем
	 */
	public $titlemodule;

	/**
	 * @var string заголовок текущей страницы для тега *title*, сформированный прикрепленным модулем
	 */
	public $titlemodule_meta;

	/**
	 * @var integer спрятать ссылки на предыдущую, последующую страницы, да/нет (1/0)
	 */
	public $hide_previous_next;

	/**
	 * @var mixed (boolean|integer) страница скрыта для всех
	 */
	public $deactivate = false;

	/**
	 * @var array CSS-файлы, подключаемые в модулях
	 */
	public $css_view = array();

	/**
	 * @var array JS-скрипты, подключемые в модулях
	 */
	public $js_view = array();

	/**
	 * @var array JS-код, определяемый в модулях
	 */
	public $js_code = array();

	/**
	 * @var boolean страница не кэшируется при включенном экстремальном кэшировании
	 */
	public $nocache = false;

	/**
	 * @var boolean сжатие данных
	 */
	public $nozip = false;

	/**
	 * Доступ к свойствам текущей страницы
	 *
	 * @return mixed
	 */
	public function __get($value)
	{
		if(! isset($this->cache["fields"][$value]))
		{
			switch($value)
			{
				case 'parents':
					$this->cache["fields"][$value] = $this->diafan->get_parents($this->id, 'site');
					break;

				case 'module_cats':
					if($this->diafan->_route->show && $this->diafan->configmodules("cat"))
					{
						$cats = DB::query_fetch_value("SELECT cat_id FROM {%s_category_rel} WHERE element_id=%d", $this->module, $this->diafan->_route->show, "cat_id");
						if ($cats && $this->diafan->configmodules("children_elements"))
						{
							$parent_ids = $this->diafan->get_parents($cats, $this->module."_category");
							$cats = array_merge($cats, $parent_ids);
						}
						$this->cache["fields"][$value] = $cats;
					}
					else
					{
						$this->cache["fields"][$value] = array();
					}
					break;

				default:
					$this->cache["fields"][$value] = '';
					break;
			}
		}
		return $this->cache["fields"][$value];
	}

	/**
	 * Определяет страницу сайта, задает параметры страницы
	 *
	 * @return void
	 */
	public function set()
	{
		if ($this->id || $this->module)
		{
			$fields = ', [act]';
			foreach ($this->diafan->_languages->all as $l)
			{
				$fields .= ', act'.$l["id"];
			}
			$time = mktime(1, 0, 0);
			$current_page = DB::query_fetch_array(
					"SELECT id, parent_id, [name], [title_meta], [name], [keywords], [descr], [canonical],"
					." title_no_show, noindex, [text], js,"
					." timeedit, theme, module_name, sort, access".($fields ? $fields : "").","
					." date_start, date_finish"
					." FROM {site}"
					." WHERE trash='0'"
					.($this->id != 1 && (! $this->diafan->_users->id || ! $this->diafan->_users->roles("init", "site", array(), 'admin')) ? " AND [act]='1' AND date_start<=".$time." AND (date_finish=0 OR date_finish>=".$time.")" : "")
					." AND ".($this->id ? "id=%d" : "module_name='%s'")
					." LIMIT 1",
					($this->id ? $this->id : $this->module)
				);
			if (empty($current_page))
			{
				Custom::inc('includes/404.php');
			}
			if($current_page["access"] == '1')
			{
				if($this->diafan->configmodules('where_access_element', 'site') && ! DB::query_result("SELECT COUNT(*) FROM {access} WHERE element_id=%d AND module_name='site' AND element_type='element' AND role_id=%d LIMIT 1", $current_page["id"], $this->diafan->_users->role_id))
				{
					Custom::inc('includes/403.php');
				}
			}
			if (! $current_page["theme"])
			{
				$current_page["theme"] = 'site.php';
			}
			$langs = $this->diafan->_languages->all;
			foreach ($langs as &$l)
			{
				$l["page_act"] = $current_page["act".$l["id"]];
			}
			$this->diafan->_languages->all = $langs;
		}
		else
		{
			Custom::inc('includes/404.php');
		}

		$this->id            = $current_page["id"];
		$this->name          = $current_page['name'];
		$this->keywords      = $current_page['keywords'];
		$this->descr         = $current_page['descr'];
		$this->parent_id     = $current_page['parent_id'];
		$this->title_no_show = $current_page['title_no_show'];
		$this->noindex       = $current_page['noindex'];
		$this->title_meta    = $current_page['title_meta'];
		$this->canonical     = $current_page['canonical'];
		$this->timeedit      = $current_page['timeedit'];
		$this->theme         = $current_page['theme'];
		$this->js            = $current_page['js'];
		$this->breadcrumb    = array();
		if($this->module == "reminding")
		{
			$this->theme         = 'site.php';
		}
		else
		{
			$this->module        = $current_page["module_name"];
			$this->text          = $current_page['text'];
		}
		if($this->id != 1 && (! $this->diafan->_users->id || ! $this->diafan->_users->roles("init", "site", array(), 'admin')))
		{
			$this->deactivate = false;
		}
		else
		{
			if ($current_page["act"] == '1' && $current_page["date_start"] <= $time
			&& ($current_page["date_finish"] == 0 || $current_page["date_finish"] >= $time))
			{
				$this->deactivate = false;
			}
			else
			{
				$this->deactivate = true;
				if($current_page["act"] == '1')
				{
					if ($current_page["date_start"] > $time
					&& ($current_page["date_finish"] == 0 || $current_page["date_finish"] >= $time))
					{
						$this->deactivate = $current_page["date_start"];
					}
					if ($current_page["date_finish"] < $time
					&& ($current_page["date_start"] == 0 || $current_page["date_start"] <= $time))
					{
						$this->deactivate = $current_page["date_finish"];
					}
				}
			}
		}
	}
}

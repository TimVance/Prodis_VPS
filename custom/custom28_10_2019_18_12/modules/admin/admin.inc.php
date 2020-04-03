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
 * Admin_inc
 */
class Admin_inc extends Model
{
	/**
	 * @var integer номер текущей страницы, уникальный идентификатор каждой страницы
	 */
	public $id;

	/**
	 * @var string название текущей страницы
	 */
	public $name;

	/**
	 * @var string название текущего модуля
	 */
	public $title_module;

	/**
	 * @var string ссылка на документацию для текущей страницы
	 */
	public $docs;

	/**
	 * @var integer номер страницы родителя
	 */
	public $parent_id;

	/**
	 * @var string модуль, прикрепленный к текущей странице
	 */
	public $module;

	/**
	 * @var string ЧПУ текущей страницы, для страницы *http://site.ru/admin/news/category/* в переменной будет "news/category"
	 */
	public $rewrite;

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
	 * Доступ к свойствам текущей страницы
	 *
	 * @return mixed
	 */
	public function __get($value)
	{
		if(! isset($this->cache["fields"][$value]))
		{
			$this->cache["fields"][$value] = '';
		}
		return $this->cache["fields"][$value];
	}

	/**
	 * Определяет страницу административной части, задает параметры страницы
	 *
	 * @return void
	 */
	public function set()
	{
		if ($this->rewrite)
		{
			$row = DB::query_fetch_array("SELECT id, name, parent_id, docs FROM {admin} WHERE BINARY rewrite='%h' ORDER BY parent_id DESC LIMIT 1", $this->rewrite);
		}

		if (empty( $row ))
		{
			Custom::inc('adm/includes/frame.php');
			$this->diafan->_frame = new Frame_admin($this->diafan);
			Custom::inc('includes/404.php');
		}

		$this->id = $row["id"];
		$this->name = $row['name'];
		$this->docs = $row['docs'];
		$this->parent_id = $row['parent_id'];
	}
}

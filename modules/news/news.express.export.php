<?php
/**
 * Экспорт данных
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
 * News_express_export
 */
class News_express_export extends Service_express_export
{
	/**
	 * Инициирует экспорт
	 *
	 * @param integer $cat_id идентификатор описания импорта/экспорта
	 * @return mixed (false|'success'|'next'|'empty'|'busy')
	 */
	public function init($cat_id)
	{
		return parent::init($cat_id);
	}

	/**
	 * Устанавливает параметры полей учавствующих в экспорте
	 *
	 * @param array $rows массив полей учавствующих в экспорте
	 * @param integer $k текущий индекс в массиве полей
	 * @param array $row массив значений текущего поля
	 * @return boolean
	 */
	protected function config_params($rows, $k, $row)
	{
		return false;
	}

	/**
	 * Устанавливает название полей списка
	 *
	 * @return void
	 */
	protected function select_values()
	{
		
	}

	/**
	 * Подготовка базы данных
	 *
	 * @return void
	 */
	protected function prepare()
	{
		parent::prepare();
	}

	/**
	 * Подготовка к построчному считыванию и анализу строк из базы данных
	 *
	 * @return void
	 */
	protected function prepare_rows()
	{
		parent::prepare_rows();
	}

	/**
	 * Вывод строки экспорта
	 *
	 * @return array
	 */
	protected function export_row()
	{
		$list = array();
		if(empty($this->data) || ! is_array($this->data))
		{
			return $list;
		}
		foreach ($this->fields as $k => $field)
		{
			switch($field["type"])
			{
				case 'id':
					switch($field["params"]["type"])
					{
						case 'site':
							$list[] = $this->data["id"];
							break;

						default:
							$list[] = $this->data["import_id"];
							break;
					}
					break;

				case 'parent':
					$value = '';
					if($this->export["type"] == 'category')
					{
						switch($field["params"]["type"])
						{
							case 'site':
								$value = $this->data["parent_id"];
								break;

							case 'name':
								if($this->data["parent_id"])
								{
									$value = DB::query_result("SELECT [name] FROM {%s_category} WHERE id=%d LIMIT 1", $this->export["module_name"], $this->data["parent_id"]);
								}
								break;

							default:
								if($this->data["parent_id"])
								{
									$value = DB::query_result("SELECT import_id FROM {%s_category} WHERE id=%d LIMIT 1", $this->export["module_name"], $this->data["parent_id"]);
								}
								break;
						}
					}
					elseif($this->export["type"] == 'element')
					{
						$cats = DB::query_fetch_key_value("SELECT cat_id as cat FROM {%s_category_rel} WHERE element_id=%d AND trash='0'", $this->export["module_name"], $this->data["id"], "cat", "cat");
						if(isset($this->data["cat_id"]) && ! empty($cats[$this->data["cat_id"]]))
						{
							$cat_id = $cats[$this->data["cat_id"]];
						}
						else
						{
							$cat_id = ! empty($cats) ? array_shift($cats) : 0;
						}
						unset($cats);
						if($cat_id)
						{
							if(! $parent_id = DB::query_result("SELECT parent_id FROM {%s_category} WHERE id=%d LIMIT 1", $this->export["module_name"], $cat_id))
							{
								$parent_id = DB::query_result("SELECT parent_id FROM {%s_category_parents} WHERE element_id=%d AND trash='0' LIMIT 1", $this->export["module_name"], $cat_id);
							}
							if($parent_id)
							{
								switch($field["params"]["type"])
								{
									case 'site':
										$value = $parent_id;
										break;

									case 'name':
										$value = DB::query_result("SELECT [name] FROM {%s_category} WHERE id=%d LIMIT 1", $this->export["module_name"], $parent_id);
										break;

									default:
										$value = DB::query_result("SELECT import_id FROM {%s_category} WHERE id=%d LIMIT 1", $this->export["module_name"], $parent_id);
										break;
								}
							}
						}
					}
					$list[] = $value;
					break;

				case 'map_no_show':
				case 'sort':
				case 'admin_id':
				case 'theme':
				case 'view':
				case 'view_rows':
				case 'view_element':
				case 'changefreq':
				case 'priority':
					$list[] = $this->data[$field["type"]];
					break;

				case 'name':
				case 'text':
				case 'anons':
				case 'keywords':
				case 'descr':
				case 'title_meta':
				case 'act':
				case 'canonical':
				case 'measure_unit':
					$list[] = $this->data[$field["type"]._LANG];
					break;

				case 'cats':
					if($this->export["type"] == 'element')
					{
						$table_cat_rel = $this->export["module_name"].'_category_rel';
					}
					else
					{
						$table_cat_rel = $this->export["module_name"].'_'.$this->export["type"].'_category_rel';
					}
					switch($field["params"]["type"])
					{
						case 'site':
							$cats = DB::query_fetch_key_value("SELECT cat_id as cat FROM {".$table_cat_rel."} WHERE element_id=%d AND trash='0'", $this->data["id"], "cat", "cat");
							break;

						case 'name':
							$cats = DB::query_fetch_key_value("SELECT s.[name], s.id FROM {".$table_cat_rel."} AS r INNER JOIN {%s_category} AS s ON s.id=r.cat_id WHERE r.element_id=%d AND r.trash='0'", $this->export["module_name"], $this->data["id"], "id", "name");
							break;

						default:
							$cats = DB::query_fetch_key_value("SELECT s.import_id, s.id FROM {".$table_cat_rel."} AS r INNER JOIN {%s_category} AS s ON s.id=r.cat_id WHERE r.element_id=%d AND r.trash='0'", $this->export["module_name"], $this->data["id"], "id", "import_id");
							break;
					}
					$sequence = ($field["params"]["type"] == 'name' && ! empty($field["params"]["sequence_delimitor"]));
					if(! isset($this->cache["cats"]) && $sequence)
					{
						$this->cache["parents"] =
							DB::query_fetch_key("SELECT id, [name], parent_id FROM {%s_category} WHERE trash='0'", $this->export["module_name"], $this->import["site_id"], "id");
					}
					if($sequence)
					{
						foreach($cats as $i => $dummy)
						{
							$ii = $i;
							while(! empty($this->cache["parents"][$ii]))
							{
								if($ii != $i)
								{
									$cats[$i] = $this->cache["parents"][$ii]["name"] . $field["params"]["sequence_delimitor"] . $cats[$i];
								}
								$ii = $this->cache["parents"][$ii]["parent_id"];
							}
						}
					}
					$value = '';
					if(isset($this->data["cat_id"]) && ! empty($cats[$this->data["cat_id"]]))
					{
						$value = $cats[$this->data["cat_id"]];
						unset($cats[$this->data["cat_id"]]);
						if($cats)
						{
							$value .= $this->export["sub_delimiter"];
						}
					}
					$value .= implode($this->export["sub_delimiter"], $cats);
					$list[] = $value;
					break;

				case 'images':
					$is = array();
					$images = DB::query_fetch_all("SELECT id, folder_num, name, [alt], [title] FROM {images} WHERE module_name='%s' AND element_type='%s' AND trash='0' AND element_id=%d AND param_id=0", $this->export["module_name"], $this->export["element_type"], $this->data["id"]);
					foreach($images as $i)
					{
						/*if(! empty($field["params"]["directory"]))
						{
							File::copy_file(ABSOLUTE_PATH.USERFILES.'/original/'.($i["folder_num"] ? $i["folder_num"].'/' : '').$i["name"], $field["params"]["directory"].'/'.$i["name"]);
						}*/
						if(! empty($field["params"]["second_delimitor"])
						&& (! empty($i['alt']) || ! empty($i['title'])))
						{
							$i["name"] .= $field["params"]["second_delimitor"].$i['alt'].$field["params"]["second_delimitor"].$i["title"];
						}
						$is[] = $i["name"];
					}
					$list[] = implode($this->export["sub_delimiter"], $is);
					break;

				case 'rel_news':
					switch($field["params"]["type"])
					{
						case 'site':
							$rels = DB::query_fetch_value("SELECT rel_element_id as rel FROM {%s_rel} WHERE element_id=%d AND trash='0'", $this->export["module_name"], $this->data["id"], "rel");
							break;

						default:
							$rels = DB::query_fetch_value("SELECT s.import_id as rel FROM {%s_rel} AS r INNER JOIN {%s} AS s ON s.id=r.rel_element_id WHERE r.element_id=%d AND r.trash='0'", $this->export["module_name"], $this->export["module_name"], $this->data["id"], "rel");
							break;
					}
					$list[] = implode($this->export["sub_delimiter"], $rels);
					break;

				case 'rewrite':
					$list[] = DB::query_result("SELECT rewrite FROM {rewrite} WHERE module_name='%s' AND element_id=%d AND element_type='%s' AND trash='0' LIMIT 1", $this->export["module_name"], $this->data["id"], $this->export["element_type"]);
					break;

				case 'redirect':
					$r = DB::query_fetch_array("SELECT redirect, code FROM {redirect} WHERE module_name='%s' AND element_id=%d AND element_type='%s' AND trash='0' LIMIT 1", $this->export["module_name"], $this->data["id"], $this->export["element_type"]);
					$v = '';
					if($r)
					{
						$v = $r["redirect"];
						if($r["code"] != 301 && ! empty($field["params"]["second_delimitor"]))
						{
							$v .= $field["params"]["second_delimitor"].$r["code"];
						}
					}
					$list[] = $v;
					break;

				case 'menu':
					if($field["params"]["id"])
					{
						$in_menu = DB::query_result("SELECT id FROM {menu} WHERE cat_id=%d AND module_name='%s' AND element_id=%d AND element_type='%s' trash='0' AND [act]='1' LIMIT 1", $field["params"]["id"], $this->export["module_name"], $this->data["id"], $this->export["element_type"]);
						$list[] = $in_menu ? '1' : '0';
					}
					break;

				case 'access':
					if($this->data["access"])
					{
						$access = DB::query_fetch_value("SELECT role_id FROM {access} WHERE element_id=%d AND module_name='%s' AND element_type='%s' AND trash='0'", $this->data["id"], $this->export["module_name"], $this->export["element_type"], "role_id");
						$list[] = implode($this->export["sub_delimiter"], $access);
					}
					break;

				case 'date_start':
				case 'date_finish':
					$list[] = date('d.m.Y H:i', $this->data[$field["type"]]);
					break;
				case 'empty':
					$list[] = '';
					break;
			}
		}
		return $list;
	}

	/**
	 * Завершающие операции экспорта
	 *
	 * @return void
	 */
	protected function finish()
	{
		parent::finish();
	}
}

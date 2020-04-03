<?php
/**
 * Список элементов
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

class Show_admin extends Diafan
{
	/**
	 * @var integer количество строк, выводимых на странице
	 */
	public $nastr = 30;

	/**
	 * @var array дополнительные групповые операции
	 */
	public $group_action;

	/**
	 * @var integer|string язык поля, обозначающего активность
	 */
	public $lang_act;

	/**
	 * @var integer порядковый номер элемента, с которого начинается вывод элементов
	 */
	private $polog = 0;

	/**
	 * @var array элементы списка
	 */
	public $rows;

	/**
	 * @var array идентификаторы элементов списка
	 */
	public $rows_id;

	/**
	 * @var array родители текущего раскрытого элемента
	 */
	private $parent_parents = array ();

	/**
	 * @var string ссылка на текущую страницу, используемая в постраничной навигации
	 */
	private $navlink;

	/**
	 * @var string ссылка на текущую страницу, используемая при раскрытии/закрытии дерева
	 */
	private $enterlink;

	/**
	 * Выводит ссылку на обновление
	 * @return void
	 */
	public function show_update(){}

	/**
	 * Выводит ссылку на добавление страницы
	 *
	 * @param string $text текст ссылки "Добавить элемент"
	 * @param string $icon иконка
	 * @return void
	 */
	public function update_init($text = false, $icon = 'fa-refresh')
	{
		if($text === false)
		{
			$text = $this->diafan->_('Обновить');
		}
		else
		{
			$text = $this->diafan->_($text);
		}

		echo '<span class="btn btn_blue btn_small btn_refr" onClick="window.location.href=document.location">
			<span class="fa '.$icon.'"></span>
			'.$text.'
		</span>';
	}

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add(){}

	/**
	 * Выводит ссылку на добавление элемента
	 *
	 * @param string $text текст ссылки "Добавить элемент"
	 * @param string $icon иконка
	 * @return void
	 */
	public function addnew_init($text, $icon = 'fa-plus-square')
	{
		echo '<a href="'.$this->diafan->get_admin_url('parent');
		if ($this->diafan->config('element_site') && strpos(URL, 'site') === false && ! empty($this->diafan->_route->site))
		{
			echo 'site'.$this->diafan->_route->site.'/';
		}
		$text = $this->diafan->_($text);
		echo 'addnew1/'.$this->diafan->get_nav.'" class="btn">
		<i class="fa '.$icon.'"></i> '.$text.'</a>';
	}

	/**
	 * Формирует список элементов
	 *
	 * @param integer $id родитель
	 * @param boolean $first_level первый уровень вложенности
	 * @return void
	 */
	public function list_row($id = 0, $first_level = true)
	{
		$this->lang_act = $this->diafan->variable_multilang("act") ? _LANG : '';
		$links = $this->prepare_paginator($id);
		$this->diafan->rows = $this->diafan->sql_query($id);

		$this->cache["prepare"] = array();
		// собираем все идентификаторы
		$ids = array();
		foreach($this->diafan->rows as $row)
		{
			$ids[] = $row["id"];
		}
		$this->diafan->rows_id = $ids;

		if($first_level)
		{
			$this->diafan->count = count($this->diafan->rows);
			if(! $this->diafan->count && $this->diafan->_route->page > 1)
			{
				$this->diafan->redirect($this->diafan->_route->current_admin_link('page').($this->diafan->_route->page > 2 ? 'page'.($this->diafan->_route->page - 1).'/' : '').$this->diafan->get_nav);
			}
			echo '
			<div class="head-box">';
			if($this->diafan->_route->cat && $this->diafan->_admin->rewrite == 'shop/importexport')
			{
				$r = DB::query_fetch_array("SELECT * FROM {%s_category} WHERE id=%d", $this->diafan->table, $this->diafan->_route->cat);
				if(isset($r["name"]))
				{
					$name = $r["name"];
				}
				elseif(isset($r["name"._LANG]))
				{
					$name = $r["name"._LANG];
				}
				echo '<span class="head-box__unit">'.$name.'<span class="heading__in"> <a href="'.BASE_PATH_HREF.$this->diafan->_admin->rewrite.'/edit'.$this->diafan->_route->cat.'/">'.$this->diafan->_('изменить').'</a>
			</span></span>';
			}
			elseif($this->diafan->_admin->name != $this->diafan->_admin->title_module)
			{
				echo '<span class="head-box__unit">'.$this->diafan->_($this->diafan->_admin->name).'</span>';
				echo $this->diafan->show_update();
			}
				echo $this->diafan->show_add();
			echo '</div>';

			echo $this->diafan->get_filter_cat_site();

			echo '<form action="" method="POST">
			<input type="hidden" name="check_hash_user" value="'.$this->diafan->_users->get_hash().'">
			<input type="hidden" name="action" value="">
			<input type="hidden" name="id" value="">
			<input type="hidden" name="module" value="">';
			$show_filter = true;
			if(! $this->diafan->count)
			{
				$show_filter = false;
				if($this->diafan->get_nav_params)
				{
					foreach($this->diafan->get_nav_params as $v)
					{
						if($v)
						{
							$show_filter = true;
						}
					}
				}
			}
			if($show_filter)
			{
				$filter = $this->diafan->get_filter();
			}
			else
			{
				$filter = '';
			}
			if($filter)
			{
				echo '<div class="content__left">';
			}
			if(! $this->diafan->count && $show_filter)
			{
				echo $this->diafan->_('Нет элементов для отображения.').' <a href="'.BASE_PATH_HREF.$this->diafan->_admin->rewrite.'/">'.$this->diafan->_('Сбросить фильтр.').'</a>';
			}
		}
		$paginator = '';
		if (count($this->diafan->rows))
		{
			if($links || $first_level)
			{
				$paginator = '<div class="paginator">';
				$paginator .= $this->diafan->_tpl->get('get_admin', 'paginator', $links);
			}
			if($first_level && $this->diafan->_admin->rewrite != 'site')
			{
				$paginator .= '<div class="paginator__unit">
					'.$this->diafan->_('Показывать на странице').':
					<input name="nastr" type="text" value="'.$this->diafan->_paginator->nastr.'">
					<button type="button" class="btn btn_blue btn_small change_nastr">'.$this->diafan->_('ОК').'</button>
				</div>';
			}
			if($links || $first_level)
			{
				$paginator .= '</div>';
			}
			echo $paginator;
		}
		if($first_level && $this->diafan->count)
		{
			$this->diafan->group_action_panel($filter ? true : false);
		}
		if($first_level)
		{
			echo '<ul class="list list_'.($this->diafan->_admin->module).'s'.(strpos($this->diafan->_admin->rewrite, "category") ? '_category' : '').' list'.($this->diafan->variable_list('plus') ? '_pages' : '_catalog '.($this->diafan->_admin->rewrite != 'shop' ? 'do_auto_width' : ''))
			.($this->diafan->variable_list('sort') ? ' list_move' : '')
			.($this->diafan->variable_list('sort', 'desc') ? ' sort_desc' : '' ).'">';
		}
		else
		{
			echo '<ul class="list list_'.($this->diafan->_admin->module).'s  ui-sortable'.($this->diafan->variable_list('sort') ? ' list_move' : '')
			.(! $this->diafan->variable_list('plus') ?  ' do_auto_width list' : '').'">';
		}
		if($first_level && $this->diafan->count)
		{
			$this->diafan->get_heading();
		}

		foreach ($this->diafan->rows as $row)
		{
			echo '<li class="item';
			if ($this->diafan->is_variable("readed") && ! $row["readed"])
			{
				echo ' item_no_readed';
			}
			if ($this->diafan->is_variable("no_buy") && $row["no_buy"])
			{
				echo ' item_no_buy';
			}
			if ($this->diafan->variable_list('actions', 'act') && ! $row['act'])
			{
				echo ' item_disable';
			}
			if (in_array($row["id"], $this->parent_parents))
			{
				echo ' active';
			}
			echo '" row_id="'.$row['id'].'"'
			.($this->diafan->variable_list('plus') ? ' parent_id="'.$row['parent_id'].'"' : '')
			.($this->diafan->variable_list('sort') ? ' sort_id="'.$row['sort'].'"' : '')
			.'>
		    <div class="item__in'.$this->diafan->list_row_class($row).'">';
			foreach($this->diafan->variables_list as $name => $var)
			{
				if(! is_array($var))
				{
					$var = array();
				}
				$var["class"] = ! empty($var["class"]) ? $var["class"] : '';
				$var["class"] .= $name != 'created' && ! empty($var["type"]) && ($var["type"] == 'datetime' || $var["type"] == 'date') ? (! empty($var["class"]) ? ' ' : '').'date' : '';
				$var["class"] .= ! empty($var['no_important']) ? (! empty($var["class"]) ? ' ' : '').'no_important' : '';
				$func = 'list_variable_'.preg_replace('/[^a-z_]+/', '', $name);
				$result = call_user_func_array (array(&$this->diafan, $func), array($row, $var));
				if ($result !== 'fail_function')
				{
					echo $result;
				}
				elseif(! empty($var["type"]) && $var["type"] != 'none')
				{
					echo '<div'.(! empty($var["class"]) ? ' class="'.$var["class"].'"' : '').'>';
					if(! empty($var["fast_edit"]))
					{
						echo '
						<div class="item__field fast_edit">';
						switch($var["type"])
						{
							case 'text':
								echo '<i class="fa fa-check-circle"></i>
								<div class="item__field__cover"><span></span></div>
								<input type="text" row_id="'.$row['id'].'" name="'.$name.'" value="'.str_replace('"', '&quot;', $row[$name]).'">';
								break;

							case 'numtext':
								echo '<i class="fa fa-check-circle"></i>
								<div class="item__field__cover"><span></span></div>
								<input type="text" row_id="'.$row['id'].'" name="'.$name.'" value="'.$row[$name].'" class="number">';
								break;

							case 'floattext':
								echo '<i class="fa fa-check-circle"></i>
								<div class="item__field__cover"><span></span></div>
								<input type="text" class="number" row_id="'.$row['id'].'" name="'.$name.'" value="'.number_format($row[$name], 2, ',', '').'">';
								break;

							case 'editor':
							case 'textarea':
								echo ' <textarea name="'.$name.'" row_id="'.$row['id'].'" cols="40" rows="3">'.str_replace(array ( '<',
									'>', '"' ), array ( '&lt;', '&gt;', '&quot;' ), $row[$name]).'</textarea>';
								break;

							case 'datetime':
								if($name != 'created')
								{
									echo '<i class="fa fa-check-circle"></i>
									<div class="item__field__cover"><span></span></div>
									<input type="text" row_id="'.$row['id'].'" name="'.$name.'" value="'.date("d.m.Y H:i", $row[$name]).'" class="timecalendar" showTime="true">';
								}
								break;

							case 'date':
								if($name != 'created')
								{
									echo '<i class="fa fa-check-circle"></i>
									<div class="item__field__cover"><span></span></div>
									<input type="text" row_id="'.$row['id'].'" name="'.$name.'" value="'.date("d.m.Y H:i", $row[$name]).'" class="timecalendar" showTime="false">';
								}
								break;
						}
						echo '
							<div class="info-box success">'.$this->diafan->_('Сохранено!').'</div>
							<div class="info-box change">'.$this->diafan->_('Для сохранения нажмите Enter.').'</div>
						</div>';
					}
					else
					{
						switch($var["type"])
						{
							case 'editor':
							case 'text':
								echo (! empty($row[$name]) ? $this->diafan->short_text($row[$name]) : '');
								break;

							case 'select':
								if(! isset($var["select"]))
								{
									if(! empty($var["select_db"]))
									{
										$var["select"] = $this->diafan->get_select_from_db($var["select_db"]);
									}
									else
									{
										$var["select"] = $this->diafan->variable($name, 'select');
										if(! $var["select"] && $this->diafan->variable($name, 'select_db'))
										{
											$var["select"] = $this->diafan->get_select_from_db($this->diafan->variable($name, 'select_db'));
										}
									}
									$this->diafan->variable_list($name, 'select', $var["select"]);
								}
								if(! empty($var["select"][$row[$name]]))
								{
									echo $this->diafan->_($var["select"][$row[$name]]);
								}
								break;

							case 'numtext':
							case 'floattext':
							case 'string':
								echo (! empty($row[$name]) ? $row[$name] : '&nbsp;');
								break;

							case 'datetime':
								if($name != 'created')
								{
									echo (! empty($row[$name]) ? date("d.m.Y H:i", $row[$name]) : '&nbsp;');
								}
								break;

							case 'date':
								if($name != 'created')
								{
									echo (! empty($row[$name]) ? date("d.m.Y", $row[$name]) : '&nbsp;');
								}
								break;
						}
					}
					echo '</div>';
				}
			}

			echo '</div>';
			//выводит вложенные элементы
			if ($this->diafan->variable_list('plus') && in_array($row["id"], $this->parent_parents))
			{
				$this->diafan->list_row($row["id"], false);
			}
			echo  '</li>';
		}

		echo '</ul>';
		if($first_level && $this->diafan->count)
		{
			$this->diafan->group_action_panel($filter ? true : false);
		}
		echo $paginator;
		if($first_level)
		{
			if($filter)
			{
				echo '</div>';
			}
			echo '</form>';
			echo $filter;
		}
	}

	/**
	 * Формирует дополнительные классы для строк списока элементов
	 *
	 * @param array $row информация о текущем элементе списка
	 * @return string
	 */
	public function list_row_class($row)
	{
		return '';
	}

	/**
	 * Шапка списка
	 *
	 * @return void
	 */
	public function get_heading()
	{
		if(! $this->diafan->variables_list)
		{
			return;
		}
		echo '<li class="item item_heading" parent_id="0">';
		foreach($this->diafan->variables_list as $key => $row)
		{
			if(! is_array($row))
			{
				$row = array();
			}
			if(! empty($row["type"]) && $row["type"] == 'none')
				continue;

			$row["class"] = (! empty($row["class"]) ? $row["class"] : '').(! empty($row['no_important']) ? (! empty($row["class"]) ? ' ' : '').'no_important' : '');

			echo '<div class="item__th'.(! empty($row["class_th"]) ? ' '.$row["class_th"] : '').'">'.(! empty($row["name"]) ? $this->diafan->_($row["name"]) : '').$this->help($key).'</div>';
		}
		echo '</li>';
	}

	/**
	 * Определяет подсказки для полей
	 *
	 * @param string $key название текущего поля или текст подсказки
	 * @return string
	 */
	public function help($key = '')
	{
		if (! $key)
		{
			return '';
		}
		if(! $this->diafan->is_variable_list($key))
		{
			$help = $key;
			$key = rand(0, 3333);
		}
		elseif (! $help = $this->diafan->variable_list($key, 'help'))
		{
			return '';
		}

		return ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
	}

	/**
	 * Фильтр по категориям и разделам
	 *
	 * @return string
	 */
	public function get_filter_cat_site()
	{
		$text = '';
		if ($this->diafan->config('element_site') && count($this->diafan->sites) > 1)
		{
			if (! empty($this->diafan->sites) && count($this->diafan->sites) > 1)
			{
				$text = '
				<select rel="'.$this->diafan->get_admin_url('page', 'site', 'cat').'" class="redirect" name="site" data-placeholder="'.$this->diafan->_('Искать по разделу').'">
				<option value="">'.$this->diafan->_('Все').'</option>';
				foreach($this->diafan->sites as $row)
				{
					$text .= '<option value="'.$row["id"].'"'.($row["id"] == $this->diafan->_route->site ? ' selected' : '').'>'.$row["name"].'</option>';
				}
				$text .= '</select>';
			}
		}
		if($this->diafan->categories)
		{
			if(count($this->diafan->categories) > 1)
			{
				$cats = array();
				$cs = array();
				foreach ($this->diafan->categories as $r)
				{
					if(isset($r["parent_id"]))
					{
						$cats[$r["parent_id"]][] = $r;
					}
					else
					{
						$cs[$r["id"]] = $r["name"];
					}
				}

				if ($cats)
				{
					$cs = $this->diafan->hierarchy_list($cs, $cats);
				}
				$text .= '
				<select rel="'.$this->diafan->get_admin_url('page', 'cat').'" class="redirect" name="cat" data-placeholder="'.$this->diafan->_('Искать по категории').'">
				<option value="">'.$this->diafan->_('Все').'</option>';
				foreach($cs as $k => $v)
				{
					$text .= '<option value="'.$k.'"'.($k == $this->diafan->_route->cat ? ' selected' : '').'>'.$v.'</option>';
				}
				$text .= '</select>';
			}
		}
		if($text)
		{
			$text = '<p>'.$text.'</p>';
		}
		return $text;
	}

	/**
	 * Фильтр вывода
	 *
	 * @return string
	 */
	public function get_filter()
	{
		$rows = array();
		foreach($this->diafan->variables_filter as $name => $row)
		{
			$func = 'get_filter_variable_'.preg_replace('/[^a-z_]+/', '', $name);
			$result = call_user_func_array (array(&$this->diafan, $func), array($row));
			if ($result !== 'fail_function')
			{
				$row["function"] = $result;
				$rows[$name] = $row;
				continue;
			}
			if(! empty($row["type"]) && ($row["type"] == 'select' || $row["type"] == 'multiselect') && empty($row["select"]))
			{
				if($this->diafan->variable($name, 'select'))
				{
					$row["select"] = $this->diafan->variable($name, 'select');
				}
				elseif($this->diafan->variable_list($name, 'select'))
				{
					$row["select"] = $this->diafan->variable_list($name, 'select');
				}
				elseif($this->diafan->variable($name, 'select_db'))
				{
					$row["select"] = $this->diafan->get_select_from_db($this->diafan->variable($name, 'select_db'));
					$this->diafan->variable($name, 'select', $row["select"]);
				}
				elseif($this->diafan->variable_list($name, 'select_db'))
				{
					$row["select"] = $this->diafan->get_select_from_db($this->diafan->variable_list($name, 'select_db'));
					$this->diafan->variable_list($name, 'select', $row["select"]);
				}
				if(empty($row["select"]))
				{
					continue;
				}
			}
			$row["value"] = (! empty($this->diafan->get_nav_params['filter_'.$name]) ? $this->diafan->get_nav_params['filter_'.$name] : '');
			$row["name"] = (! empty($row["name"]) ? $this->diafan->_($row["name"]) : '');
			$rows[$name] = $row;
		}
		if(! $rows)
		{
			return;
		}
		$text = '';

		foreach($rows as $name => $row)
		{
			if(! empty($row["function"]))
			{
				$text .= $row["function"];
				continue;
			}
			switch($row["type"])
			{
				case 'numtext':
					$text .= '<input type="text" placeholder="'.$row["name"].'" name="filter_'.$name.'" value="'.$row["value"].'">
					<div class="hr"></div>';
					break;

				case 'text':
					$text .= '<input type="text" placeholder="'.$row["name"].'" name="filter_'.$name.'" value="'.$row["value"].'">
					<div class="hr"></div>';
					break;

				case 'checkbox':
					$text .= '<input type="checkbox" id="filter_'.$name.'" name="filter_'.$name.'" value="1"'.($row["value"] ? ' checked' : '').'>
					<label for="filter_'.$name.'">
					'.(! empty($row["icon"]) ? $row["icon"] : '').'
					 '.$row["name"].'
					</label>';
					break;

				case 'select':
					if(empty($row["select"]))
					{
						break;
					}
					$text .= '<select name="filter_'.$name.'"'.($row["name"] ? ' data-placeholder="'.$row["name"].'"' : '').'>';
					if($row["name"])
					{
						$text .= '<option value="">'.$row["name"].'</option>';
					}
					foreach($row["select"] as $k => $v)
					{
						$text .= '<option value="'.$k.'"'.($k == $row["value"] ? ' selected' : '').'>'.$this->diafan->_($v).'</option>';
					}
					$text .= '</select>
					<div class="hr"></div>';
					break;

				case 'multiselect':
					if(empty($row["select"]))
					{
						break;
					}
					$text .= ($row["name"] ? ' '.$row["name"].': ' : '');
					foreach($row["select"] as $k => $v)
					{
						$text .= '<input type="checkbox" id="filter_'.$name.'_'.$k.'" name="filter_'.$name.'[]" value="'.$k.'"'.(! empty($row["value"]) && in_array($k, $row["value"]) ? ' checked' : '').'>
						<label for="filter_'.$name.'_'.$k.'">
						 '.$this->diafan->_($v).'
						</label>';
					}
					$text .= '
					<div class="hr"></div>';
					break;

				case 'radio':
					if(empty($row["select"]))
					{
						break;
					}
					if(! empty($row["name"]))
					{
						$text .= $row["name"].' ';
					}
					foreach($row["select"] as $k => $v)
					{
						$text .= '<input type="radio" id="filter_'.$name.'_'.$k.'" name="filter_'.$name.'"'.($k == $row["value"] ? ' checked' : '').'><label for="filter_'.$name.'_'.$k.'">'
						.$this->diafan->_($v).'</label>';
					}
					$text .= '<div class="hr"></div>';
					break;

				case 'hr':
					$text .= '<div class="hr"></div>';
					break;

				case 'numtext_interval':
					if(! empty($row["name"]))
					{
						$text .= $row["name"].' ';
					}
					$text .= '<input type="text" name="filter_start_'.$name.'" value="'.$this->diafan->get_nav_params["filter_start_".$name].'" placeholder="'.$this->diafan->_('от').'"> -
					<input type="text" name="filter_finish_'.$name.'" value="'.$this->diafan->get_nav_params["filter_finish_".$name].'" placeholder="'.$this->diafan->_('до').'">
					<div class="hr"></div>';
					break;

				case 'datetime_interval':
				case 'date_interval':
					if(! empty($row["links"]))
					{
						$y = date("Y");
						$m = date("m");
						$d = date("d");

						$date = array (
							'Сегодня' => array (
								$d.'.'.$m.'.'.$y,
								date("d.m.Y", time()+86400)
							),
							'Месяц' => array (
								'01.'.$m.'.'.$y,
								date("d.m.Y", time()+86400)
							),
							'Год'  => array (
								'01.01.'.$y,
								'31.12.'.$y.' 23:59'
							)
						);
						echo '<div class="ct-heading">';
						foreach ($date as $type => $i)
						{
							$text .= '<a class="ct-link" href="'.BASE_PATH_HREF.$this->diafan->_admin->rewrite.'/?filter_start_'.$name.'='.$i[0].'&filter_finish_'.$name.'='.$i[1].'">'.$this->diafan->_($type).'</a>';
						}
						echo '</div><div class="hr"></div>';
					}
					if(! empty($row["name"]))
					{
						$text .= $row["name"].' ';
					}
					$text .= '<input type="text" name="filter_start_'.$name.'" value="'.$this->diafan->get_nav_params["filter_start_".$name].'" placeholder="'.$this->diafan->_('с').'" class="timecalendar"> -
					<input type="text" name="filter_finish_'.$name.'" value="'.$this->diafan->get_nav_params["filter_finish_".$name].'" placeholder="'.$this->diafan->_('по').'" class="timecalendar"'.($row["type"] == 'datetime_interval' ? ' showTime="true"' : '').'>';
					$text .= '<div class="hr"></div>';
					break;
			}
		}
		if(! $text)
		{
			return;
		}
		$text = '
		<form action="'.$this->diafan->get_admin_url('page').'" method="get">
		<div class="content__right">
			<div class="ct-heading">
				<i class="fa fa-filter"></i>
				'.$this->diafan->_('Фильтровать').'
			</div>'.$text.'
			<button class="btn btn_small btn_blue">'.$this->diafan->_('Применить').'</button>
		</div>
		</form>';
		return $text;
	}

	/**
	 * Определяет свойства класса
	 *
	 * @return void
	 */
	public function prepare_variables()
	{
		if ($this->diafan->variable_list('plus') && $this->diafan->_route->parent && empty($this->parent_parents))
		{
			$this->parent_parents = $this->diafan->get_parents($this->diafan->_route->parent, $this->diafan->table);
			$this->parent_parents[] = $this->diafan->_route->parent;
		}
		if($this->diafan->config('element'))
		{
			$cats = DB::query_fetch_all(
				"SELECT id, "
				.($this->diafan->config('category_no_multilang') ? "name" : "[name]")
				.(! $this->diafan->config('category_flat') ? ", parent_id" : "")
				.($this->diafan->config("element_site") ? ", site_id" : "")
				." FROM {".$this->diafan->table."_category} WHERE trash='0'"
				.($this->diafan->config("element_site") && $this->diafan->_route->site ? " AND site_id='".$this->diafan->_route->site."'" : "")
				." ORDER BY "
				.($this->diafan->config("element_site") ? "sort" : "id")
				." ASC LIMIT 1000"
			);
			if(count($cats))
			{
				$this->diafan->not_empty_categories = true;
			}
			if(count($cats) == 1000)
			{
				$this->diafan->categories = array();
			}
			else
			{
				$this->diafan->categories = $cats;
			}
		}
		if($this->diafan->config('element_site'))
		{
			$sites = DB::query_fetch_all("SELECT id, [name], parent_id FROM {site} WHERE trash='0' AND module_name='%s' ORDER BY sort ASC", $this->diafan->_admin->module);
			if(count($sites))
			{
				$this->diafan->not_empty_site = true;
			}
			foreach($sites as $site)
			{
				$this->cache["parent_site"][$site["id"]] = $site["name"];
			}
			if(count($sites) == 1)
			{
				if (DB::query_result("SELECT id FROM {%s} WHERE trash='0' AND site_id<>%d LIMIT 1", $this->diafan->table, $sites[0]["id"]))
				{
					$sites[] = 0;
				}
				else
				{
					$this->diafan->_route->site = $sites[0]["id"];
				}
			}
			$this->diafan->sites = $sites;
		}
	}

	/**
	 * Формирует SQL-запрос для списка элементов
	 *
	 * @param integer $id родитель
	 * @return array
	 */
	public function sql_query($id)
	{
		if($this->diafan->is_variable("menu") && ! isset($this->cache["menu_noact"]))
		{
			$this->cache["menu_noact"] = DB::query_fetch_value("SELECT id FROM {menu_category} WHERE [act]='0' AND trash='0'", "id");
		}

		$this->diafan->where .= $this->diafan->is_variable("admin_id") && DB::query_result("SELECT only_self FROM {users_role} WHERE id=%d LIMIT 1", $this->diafan->_users->role_id) ? " AND (e.admin_id=0 OR e.admin_id=".$this->diafan->_users->id.")" : '';
		$fields = '';
		if($this->diafan->variables_list)
		{
			foreach ($this->diafan->variables_list as $name => $var)
			{
				if(empty($var["sql"]))
					continue;

				$fields .= ', e.'.($this->diafan->variable_multilang($name) ? '['.$name.']' : $name);
			}
		}

		return DB::query_fetch_all("SELECT e.id"
		.$fields
		.$this->diafan->fields
		.($this->diafan->variable_list('actions', 'act') ? ', e.act'.$this->lang_act.' AS act' : '' )
		.($this->diafan->variable_list('plus') ? ', e.parent_id, e.count_children' : '' )
		.($this->diafan->is_variable('date_period') ? ', e.date_start, e.date_finish' : '' )
		.($this->diafan->is_variable('readed') ? ', e.readed' : '' )
		.($this->diafan->is_variable('no_buy') ? ', e.no_buy' : '' )
		.($this->diafan->config("element_site") ? ', e.site_id' : '' )
		.($this->diafan->config("element") ? ', e.cat_id' : '' )
		.($this->diafan->is_variable("menu") ? ", COUNT(DISTINCT m.element_id) AS menu" : '' )
		." FROM {".$this->diafan->table."} as e"
		.$this->diafan->join
		.($this->diafan->config("element_multiple") && $this->diafan->_route->cat ?
			" INNER JOIN {".$this->diafan->table."_category_rel} AS c ON e.id=c.element_id" .
			" AND c.cat_id='".$this->diafan->_route->cat."'" : '' )
		.($this->diafan->is_variable("menu") ?
			" LEFT JOIN {menu} AS m ON e.id=m.element_id" .
			" AND m.trash='0' AND m.element_type='".$this->diafan->element_type()."' AND m.module_name='".$this->diafan->_admin->module."'"
			.($this->cache["menu_noact"] ? " AND m.cat_id NOT IN (".implode(",", $this->cache["menu_noact"]).")" : "")
			: '' )
		. " WHERE 1=1"
		.($this->diafan->variable_list('plus') ? " AND e.parent_id='".$id."'" : '' )
		.($this->diafan->config("element") && ! $this->diafan->config("element_multiple") && $this->diafan->_route->cat ?
		" AND e.cat_id='".$this->diafan->_route->cat."'" : '' )
		.($this->diafan->config("element_site") && $this->diafan->_route->site && (! $this->diafan->config("element_multiple") || ! $this->diafan->_route->cat) ?
		" AND e.site_id='".$this->diafan->_route->site."'" : '' ).( $this->diafan->where ? " ".$this->diafan->where : '' )
		.($this->diafan->variable_list('actions', 'trash') ? " AND e.trash='0'" : '' )
		." GROUP BY e.id"
		.$this->diafan->sql_query_order()
		.' LIMIT '.$this->polog.', '.$this->diafan->nastr);
	}

	/**
	 * Формирует часть SQL-запрос для списка элементов, отвечающую за сортировку
	 *
	 * @return string
	 */
	public function sql_query_order()
	{
		return " ORDER BY "
		.($this->diafan->is_variable("prior") ? 'e.prior DESC, ' : '' )
		.($this->diafan->is_variable("readed") ? " e.readed ASC, " : '')
		.(($this->diafan->variable_list("created") && ! $this->diafan->variable_list('sort')) ? 'e.created DESC, ' : '' )
		.($this->diafan->variable_list('actions', 'act') ? 'e.act'.$this->lang_act.' DESC, ' : '' )
		.($this->diafan->variable_list('sort') ?
			($this->diafan->variable_list('sort', 'desc') ? 'e.sort DESC, e.id DESC' : 'e.sort ASC, e.id ASC')
		  : 'e.id DESC' );
	}

	/**
	 * Формирует постраничную навигацию
	 *
	 * @return integer
	 */
	private function prepare_paginator($id)
	{
		if (! $id || ! $this->diafan->variable_list('plus'))
		{
			$this->diafan->_paginator->navlink = ( $this->diafan->_admin->rewrite ? $this->diafan->_admin->rewrite.'/' : '' ).( $this->diafan->_route->site ? 'site'.$this->diafan->_route->site.'/' : '' ).( $this->diafan->_route->cat ? 'cat'.$this->diafan->_route->cat.'/' : '' );
			$this->enterlink = $this->diafan->_paginator->navlink.'parent%d/'.( $this->diafan->_paginator->page ? 'page'.$this->diafan->_paginator->page.'/' : '' ).'?';
			$this->diafan->_paginator->get_nav = $this->diafan->get_nav;
			$this->navlink .= $this->diafan->_paginator->navlink.'parent%d/'.( $this->diafan->_paginator->page ? 'page'.$this->diafan->_paginator->page.'/' : '' ).( $this->diafan->get_nav ? $this->diafan->get_nav.'&' : '?' );
		}
		elseif ($this->diafan->variable_list('plus'))
		{
			$this->diafan->_paginator->page = ! empty($_GET["page".$id]) ? intval($_GET["page".$id]) : 0;
			$this->diafan->_paginator->urlpage = '?page'.$id.'=%d';
			$this->navlink = ( $this->diafan->_admin->rewrite ? $this->diafan->_admin->rewrite.'/' : '' ).( $this->diafan->_route->site ? 'site'.$this->diafan->_route->site.'/' : '' ).( $this->diafan->_route->cat ? 'cat'.$this->diafan->_route->cat.'/' : '' ). 'parent%d/';
			$this->diafan->_paginator->navlink = sprintf($this->navlink, $id);
		}

		$this->nen = DB::query_result("SELECT COUNT(DISTINCT e.id) FROM {".$this->diafan->table."} as e"
			.$this->diafan->join
			.($this->diafan->config("element_multiple") && $this->diafan->_route->cat ? " INNER JOIN {".$this->diafan->table."_category_rel} AS c ON e.id=c.element_id"
			." AND e.id=c.element_id AND c.cat_id='".$this->diafan->_route->cat."'" : '')
			." WHERE 1=1".( $this->diafan->variable_list('plus') ? " AND e.parent_id='".$id."'" : '')
			.($this->diafan->config("element") && !$this->diafan->config("element_multiple") && $this->diafan->_route->cat ? " AND e.cat_id='".$this->diafan->_route->cat."'" : '')
			.($this->diafan->config("element_site") && $this->diafan->_route->site && (! $this->diafan->config("element_multiple") || ! $this->diafan->_route->cat) ? " and e.site_id='".$this->diafan->_route->site."'" : '')
			.($this->diafan->where ? " ".$this->diafan->where : '')
			.($this->diafan->table == 'site' ? " AND e.id<>1" : '')
			.($this->diafan->variable_list('actions', 'trash') ? " AND e.trash='0'" : '')
			);
		$this->diafan->_paginator->nen = $this->nen;

		$links = $this->diafan->_paginator->get();
		$this->polog = $this->diafan->_paginator->polog;
		$this->diafan->nastr = $this->diafan->_paginator->nastr;

		return $links;
	}

	/**
	 * Выводит чекбокс для групповых операций
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_checkbox($row, $var)
	{
		$text = '';
		if ($this->diafan->variable_list('actions', 'del') && $this->diafan->_users->roles('del', $this->diafan->_admin->rewrite)
		|| $this->diafan->variable_list('actions', 'trash') && $this->diafan->_users->roles('del', $this->diafan->_admin->rewrite)
		|| $this->diafan->variable_list('actions', 'act') && $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite)
		|| $this->diafan->is_variable("menu") && !$this->diafan->_users->roles('edit')
		|| $this->diafan->categories && count($this->diafan->categories)
		|| $this->diafan->group_action)
		{
			$text = '<div class="checkbox'.($var["class"] ? ' '.$var["class"] : '').'">';
			if($this->diafan->check_action($row))
			{
				$text .= '<input type="checkbox" name="ids[]" value="'.$row["id"].'">';
			}
			$text .= '</div>';
		}
		return $text;
	}

	/**
	 * Выводит кнопку "Перетащить" в списке элементов
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_sort($row, $var)
	{
		$text = '
		<div class="move'.($var["class"] ? ' '.$var["class"] : '').'">
			<i class="fa fa-arrows" title="'.$this->diafan->_('Перетащить').'"></i>';
		if ($this->diafan->variable_list('sort', 'fast_edit'))
		{
			$text .= '
			<div class="item__field fast_edit">
				<i class="fa fa-check-circle"></i>
				<div class="item__field__cover"><span></span></div>
				<input type="text" row_id="'.$row["id"].'" value="'.$row["sort"].'" name="sort" class="numtext" reload="true">

				<div class="info-box success">'.$this->diafan->_('Сохранено!').'</div>
				<div class="info-box change">'.$this->diafan->_('Для сохранения нажмите Enter.').'</div>
			</div>';
		}
		$text .= '
		</div>';
		return $text;
	}

	/**
	 * Формирует ссылку на раскрытие дерева
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_plus($row, $var)
	{
		$text = '<div class="item__toggle'.($var["class"] ? ' '.$var["class"] : '').'">';


		if ($row["count_children"])
		{
			$text .= '<i class="fa fa-plus-circle"></i>';
		}
		$text .= '</div>';
		return $text;
	}

	/**
	 * Формирует дату в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_created($row, $var)
	{
		if(! empty($var["type"]) && $var["type"] == 'datetime')
		{
			$text = '<div class="date'.($var["class"] ? ' '.$var["class"] : '').'">'.date("d.m.Y H:i", $row["created"]).'</div>';
		}
		else
		{
			$text = '<div class="date'.($var["class"] ? ' '.$var["class"] : '').'">'.date("d.m.Y", $row["created"]).'</div>';
		}

		return $text;
	}

	/**
	 * Формирует изображение в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_image($row, $var)
	{
		if(! isset($this->cache["prepare"]["image"]))
		{
			$this->cache["prepare"]["image"] = DB::query_fetch_key("SELECT id, name, folder_num, element_id FROM {images}"
				." WHERE module_name='%s' AND element_type='%s' AND element_id IN (%s)"
				." AND trash='0' ORDER BY param_id DESC, sort DESC",
				$this->diafan->_admin->module,
				$this->diafan->element_type(),
				implode(",", $this->diafan->rows_id),
				"element_id"
			);
		}

		$html = '<div class="image'.($var["class"] ? ' '.$var["class"] : '').' ipad">';
		if (! empty($this->cache["prepare"]["image"][$row["id"]]))
		{
			$r = $this->cache["prepare"]["image"][$row["id"]];
			if(file_exists(ABSOLUTE_PATH.USERFILES."/small/".($r["folder_num"] ? $r["folder_num"].'/' : '').$r["name"]))
			{
				$html .= '<a href="'.$this->diafan->get_base_link($row).'"><img src="http'.(IS_HTTPS ? "s" : '').'://'.BASE_URL.'/'.USERFILES.'/small/'.($r["folder_num"] ? $r["folder_num"].'/' : '').$r["name"].'" border="0" alt=""></a>';
			}
		}
		$html .= '</div>';

		return $html;
	}

	/**
	 * Выводит название элемента в списке элементов
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_name($row, $var)
	{
		$text = '<div class="name'.(! empty($var["class"]) ? ' '.$var["class"] : '').'" id="'.$row['id'].'">';
		$name  = '';
		if(! empty($var["variable"]))
		{
			$name = strip_tags($row[$var["variable"]]);

			if(! empty($var["type"]) && $var["type"] == 'select')
			{
				if(! isset($var["select"]))
				{
					if(! empty($var["select_db"]))
					{
						$var["select"] = $this->diafan->get_select_from_db($var["select_db"]);
					}
					else
					{
						$var["select"] = $this->diafan->variable('name', 'select');
						if(! $var["select"] && $this->diafan->variable('name', 'select_db'))
						{
							$var["select"] = $this->diafan->get_select_from_db($this->diafan->variable('name', 'select_db'));
						}
					}
					$this->diafan->variable_list('name', 'select', $var["select"]);
				}
				if(! empty($var["select"][$name]))
				{
					$name = $this->diafan->_($var["select"][$name]);
				}
			}
		}
		if(! empty($var["text"]))
		{
			$name = sprintf($this->diafan->_($var["text"]), $name);
		}
		if (! $name)
		{
			if(! empty($row["name"]))
			{
				$name = $row["name"];
			}
			else
			{
				$name = $row['id'];
			}
		}

		if(defined("MOD_DEVELOPER") && MOD_DEVELOPER)
		{
			$text .= '<div class="id">'.'ID: '.$row["id"].'</div>';
		}
		$text .= '<a href="';
		$text .= $this->diafan->get_base_link($row);
		$text .= '" title="'.$this->diafan->_('Редактировать').' ('.$row["id"].')">'.$name.(! defined("MOD_DEVELOPER") || ! MOD_DEVELOPER ? ' ('.'ID: '.$row["id"].')' : '').'</a>';
		$text .= $this->diafan->list_variable_menu($row, array());
		$text .= $this->diafan->list_variable_parent($row, array());
		$text .= $this->diafan->list_variable_date_period($row, array());
		$text .= '</div>';
		return $text;
	}

	/**
	 * Формирует основную ссылку для элемента в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @return string
	 */
	public function get_base_link($row)
	{
		if(isset($this->cache["base_link"][$row["id"]]))
		{
			return $this->cache["base_link"][$row["id"]];
		}
		if ($this->diafan->config("link_to_element"))
		{
			$text = $this->diafan->_route->current_admin_link(array("page", "parent")).'cat'.$row["id"].'/';
		}
		elseif ($this->diafan->config("element_site") && $this->diafan->_users->roles('init', $this->diafan->_admin->rewrite))
		{
			$text = $this->diafan->_route->current_admin_link('site').'site'.$row['site_id'].'/edit'.$row["id"].'/'.$this->diafan->get_nav;
		}
		elseif ($this->diafan->_users->roles('init', $this->diafan->_admin->rewrite))
		{

			$text = $this->diafan->_route->current_admin_link().'edit'.$row["id"].'/'.$this->diafan->get_nav;
		}
		else
		{
			$text = '#';
		}
		$this->cache["base_link"][$row["id"]] = $text;
		return $text;
	}

	/**
	 * Выводит иконку меню в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_menu($row, $var)
	{
		if (! $this->diafan->is_variable('menu'))
		{
			return '';
		}
		if (! empty($row["menu"]))
		{
			return ' <span class="item__menu" title="'.$this->diafan->_('Отображается в меню').'">m</span>';
		}
		return '';
	}

	/**
	 * Выводит название раздела/категории в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_parent($row, $var)
	{
		$text = '';
		if ($this->diafan->config("element_site") && $this->diafan->not_empty_site)
		{
			if(count($this->diafan->sites) > 1)
			{
				$text = '<div class="categories">';
				if(! empty($this->cache["parent_site"][$row["site_id"]]))
				{
					$l = '#';
					if (! $this->diafan->_users->roles('init', 'site'))
					{
						$l = BASE_PATH_HREF.'site/edit'.$row["site_id"].'/';
					}
					$text .= '<a href="'.$l.'">'.$this->cache["parent_site"][$row["site_id"]].'</a>';
				}
				$text .= '</div>';
			}
		}
		if ($this->diafan->config("element_multiple"))
		{
			if(! isset($this->cache["prepare"]["parent_cats"]))
			{
				$this->cache["prepare"]["parent_cats"] = DB::query_fetch_key_array(
					"SELECT s.[name], c.element_id, c.id FROM {".$this->diafan->_admin->module."_category_rel} as c"
					." INNER JOIN {".$this->diafan->_admin->module."_category} as s ON s.id=c.cat_id"
					." WHERE element_id IN (%s)",
					implode(",", $this->diafan->rows_id),
					"element_id"
				);
			}
			$cats = array();
			if(! empty($this->cache["prepare"]["parent_cats"][$row["id"]]))
			{
				foreach($this->cache["prepare"]["parent_cats"][$row["id"]] as $cat)
				{
					$l = '#';
					if (! $this->diafan->_users->roles('init', $this->diafan->_admin->module.'/category'))
					{
						$l = BASE_PATH_HREF.$this->diafan->_admin->module.'/category/edit'.$cat["id"].'/';
					}
					$cats[] = '<a href="'.$l.'">'.$cat["name"].'</a>';
				}
			}
			$text .= '<div class="categories">'.implode(', ', $cats).'</div>';
		}
		elseif ($this->diafan->config("element") && ! $this->diafan->_route->cat)
		{
			if(! isset($this->cache["prepare"]["parent_cats"]) && ! empty($this->diafan->categories))
			{
				foreach($this->diafan->categories as $cat)
				{
					$this->cache["prepare"]["parent_cats"][$cat["id"]] = $cat["name"];
				}
			}
			$text .= '<div class="categories">'.(! empty($this->cache["prepare"]["parent_cats"][$row["cat_id"]]) ? $this->cache["prepare"]["parent_cats"][$row["cat_id"]] : '').'</div>';
		}
		return $text;
	}

	/**
	 * Выводит верстку для адаптации под мобильный устройства в списке элементов
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_adapt($row, $var)
	{
		echo '<div class="item__adapt">
			<i class="fa fa-bars"></i>
			<i class="fa fa-caret-up"></i>
		</div>
		<div class="item__seporator"></div>';
	}

	/**
	 * Выводит период показа
	 *
	 * @param array данные о текущем элементе
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_date_period($row, $var)
	{
		if(! $this->diafan->is_variable("date_period"))
			return;

		$text = '';
		if(! empty($row["date_start"]) || ! empty($row["date_finish"]))
		{
			if($this->diafan->variable("date_start") == 'date')
			{
				$time = mktime(0,0,0);
			}
			else
			{
				$time = time();
			}
			$text .= '<div class="date_period';
			if($row["date_start"] > $time || ! empty($row["date_finish"]) && $row["date_finish"] < $time)
			{
				$text .= '_red';
			}
			$text .= '">';
			if(! empty($row["date_start"]))
			{
				if(empty($row["date_finish"]))
				{
					$text .= '> ';
				}
				$text .= date('d.m.Y', $row["date_start"]);
			}
			if(! empty($row["date_finish"]))
			{
				if(empty($row["date_start"]))
				{
					$text .= '< ';
				}
				else
				{
					$text .= ' - ';
				}
				$text .= date('d.m.Y', $row["date_finish"]);
			}
			$text .= '</div>';
		}
		return $text;
	}

	/**
	 * Выводит кнопки действий над элементом
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_actions($row, $var)
	{
		if ($this->diafan->variable_list('actions', 'view'))
		{
			$element_type = $this->diafan->element_type();
			if(! isset($this->cache["prepare"]["link"]))
			{
				$this->cache["prepare"]["link"] = true;
				foreach($this->diafan->rows as $r)
				{
					$this->diafan->_route->prepare(
						(! empty($r["site_id"]) ? $r["site_id"] : 0),
						$r["id"],
						$this->diafan->_admin->module,
						$element_type
					);
				}
			}
			$site_id = (! empty($row["site_id"]) ? $row["site_id"] : 0);
			if(! $site_id)
			{
				if(! isset($this->cache["site_id_module"]))
				{
					$this->cache["site_id_module"] = DB::query_result("SELECT id FROM {site} WHERE module_name='%h' AND trash='0' LIMIT 1", $this->diafan->_admin->rewrite);
				}
				$site_id = $this->cache["site_id_module"];
			}
			$row["view_link"] = $this->diafan->_route->link(
				$site_id,
				$row["id"],
				$this->diafan->_admin->module,
				$element_type
			);
		}

		$text = '<div class="item__unit">';

		//add
		if ($this->diafan->variable_list('actions', 'add') && $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite)
			&& $this->diafan->check_action($row, 'add')
			&& ($this->diafan->variable_list('actions', 'act') && $row["act"]))
		{
			$text .= '
			<a href="'.$this->diafan->get_admin_url('parent').'parent'.$row["id"].'/'.'addnew'.$row["id"].'/" class="item__ui add">
				<i class="fa fa-plus-square"></i>
				<span class="add__txt">'.$this->diafan->_('Добавить подстраницу').'</span>
			</a>';
		}

		if ($this->diafan->variable_list('actions', 'view'))
		{
			$text .= '<a href="'.BASE_PATH._SHORTNAME.$row["view_link"].'" class="item__ui view" title="'.$this->diafan->_('Посмотреть на сайте').'" target="_blank">
				<i class="fa fa-laptop"></i>
			</a>';
		}

		//act
		if ($this->diafan->variable_list('actions', 'act') && $this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite)
			&& $this->diafan->check_action($row, 'act'))
		{
			$text .= '
			<a href="javascript:void(0)" title="'.($row["act"] ? $this->diafan->_('Сделать неактивным') : $this->diafan->_('Опубликовать на сайте')).'" action="'.($row["act"] ? 'un' : '' ).'block" class="action item__ui switch">
				<i class="fa fa-toggle-on"></i>
			</a>';
		}

		//trash
		if ($this->diafan->variable_list('actions', 'trash')
			&& $this->diafan->_users->roles('del', $this->diafan->_admin->rewrite)
			&& $this->diafan->check_action($row, 'del'))
		{
			$text .= '
			<a href="javascript:void(0)" title="'.$this->diafan->_('Удалить').'" confirm="'
			.(! empty( $row["count_children"] ) ? $this->diafan->_('ВНИМАНИЕ! Пункт содержит вложенность! ') : '')
			.($this->diafan->config("category") ? $this->diafan->_('При удалении категории удаляются все принадлежащие ей элементы. ') : '')
			.$this->diafan->_('Вы действительно хотите удалить запись в корзину?')
			. '" action="trash" class="action item__ui remove">
				<i class="fa fa-times-circle"></i>
			</a>';
		}

		//del
		if ($this->diafan->variable_list('actions', 'del')
			&& $this->diafan->_users->roles('del', $this->diafan->_admin->rewrite)
			&& $this->diafan->check_action($row, 'del'))
		{
			$text .= '
			<a href="javascript:void(0)" title="'.$this->diafan->_('Удалить').'"'.' confirm="'
			.(!empty( $row["count_children"] ) ? $this->diafan->_('ВНИМАНИЕ! Пункт содержит вложенность! ') : '')
			.($this->diafan->config("category") ? $this->diafan->_('При удалении категории удаляются все принадлежащие ей элементы. ') : '')
			.$this->diafan->_('Вы действительно хотите удалить запись?')
			. '" action="delete" class="action item__ui remove">
				<i class="fa fa-times-circle"></i>
			</a>';
		}

		$text .= '</div>';

		return $text;
	}

	/**
	 * Поиск по полю "Период показа"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function get_filter_variable_date_start($row)
	{
		$text = '<input type="text" name="filter_date_start" value="'.$this->diafan->get_nav_params["filter_date_start"].'" class="timecalendar"'.($row["type"] == 'datetime' ? ' showTime="true"' : '').'> -
		<input type="text" name="filter_date_finish" value="'.$this->diafan->get_nav_params["filter_date_finish"].'" class="timecalendar"'.($row["type"] == 'datetime' ? ' showTime="true"' : '').'>

		<div class="hr"></div>';
		return $text;
	}

	/**
	 * Поиск по полю "Характеристики"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function get_filter_variable_param($row)
	{
		$text = '';
		$params = DB::query_fetch_all("SELECT p.id, p.[name], p.type FROM {".$this->diafan->table."_param} AS p"
		.(! empty($row["category_rel"]) ? " INNER JOIN {".$this->diafan->table."_param_category_rel} AS c ON c.element_id=p.id " : '')
		." WHERE p.trash='0'".(! empty($row["where"]) ? ' '.$row["where"] : '')
		.(! empty($row["category_rel"]) ? " AND c.cat_id IN (0".($this->diafan->_route->cat ? ",".$this->diafan->_route->cat : "").")" : '')
		." ORDER BY p.sort ASC"
		);
		foreach($params as $param)
		{
			if($param["type"] == 'select' || $param["type"] == 'multiple')
			{
				$select_id[] = $param["id"];
			}
		}
		if(! empty($select_id))
		{
			$select = DB::query_fetch_key_array("SELECT id, [name], param_id FROM {".$this->diafan->table."_param_select} WHERE trash='0' AND param_id IN (".implode(",", $select_id).")", "param_id");
		}
		foreach($params as $param)
		{
			$name = 'param'.$param["id"];
			$value = (! empty($this->diafan->get_nav_params["filter_param"][$param["id"]]) ? $this->diafan->get_nav_params["filter_param"][$param["id"]] : '');
			$param["select"] = (! empty($select[$param["id"]]) ? $select[$param["id"]] : array());
			switch($param["type"])
			{
				case 'text':
				case 'textarea':
				case 'editor':
				case 'email':
				case 'phone':
					$text .= '<input type="text" placeholder="'.$param["name"].'" name="filter_'.$name.'" value="'.$value.'">
					'.$this->filter_param_setting($param["id"]).'
					<div class="hr"></div>';
					break;

				case 'checkbox':
					$text .= '<input type="checkbox" id="filter_'.$name.'" name="filter_'.$name.'" value="1"'.($value ? ' checked' : '').'>
					<label for="filter_'.$name.'">
					 '.$param["name"].'
					</label>
					'.$this->filter_param_setting($param["id"]);
					break;

				case 'select':
					if(empty($param["select"]))
					{
						break;
					}
					$text .= '<select name="filter_'.$name.'"'.($param["name"] ? ' data-placeholder="'.$param["name"].'"' : '').'>';
					if($param["name"])
					{
						$text .= '<option value="">'.$param["name"].'</option>';
					}
					foreach($param["select"] as $s)
					{
						$text .= '<option value="'.$s["id"].'"'.($s["id"] == $value ? ' selected' : '').'>'.$s["name"].'</option>';
					}
					$text .= '</select>
					'.$this->filter_param_setting($param["id"]).'
					<div class="hr"></div>';
					break;

				case 'multiple':
					if(empty($param["select"]))
					{
						break;
					}
					$text .= ($param["name"] ? ' '.$param["name"].': ' : '').'<div class="filter_div">';
					foreach($param["select"] as $s)
					{
						$text .= '<input type="checkbox" id="filter_'.$name.'_'.$s["id"].'" name="filter_'.$name.'[]" value="'.$s["id"].'"'.(! empty($value) && in_array($s["id"], $value) ? ' checked' : '').'>
						<label for="filter_'.$name.'_'.$s["id"].'">
						 '.$s["name"].'
						</label>';
					}
					$text .= '</div>
					'.$this->filter_param_setting($param["id"]).'
					<div class="hr"></div>';
					break;

				case 'numtext':
					if(! empty($param["name"]))
					{
						$text .= $param["name"].' ';
					}
					$text .= '<input type="text" name="filter_start_'.$name.'" value="'.$this->diafan->get_nav_params["filter_param"]["start_".$param["id"]].'" placeholder="'.$this->diafan->_('от').'"> -
					<input type="text" name="filter_finish_'.$name.'" value="'.$this->diafan->get_nav_params["filter_param"]["finish_".$param["id"]].'" placeholder="'.$this->diafan->_('до').'">
					'.$this->filter_param_setting($param["id"]).'
					<div class="hr"></div>';
					break;

				case 'datetime':
				case 'date':
					$text .= '<input type="text" placeholder="'.$param["name"].'" name="filter_'.$name.'" value="'.$value.'" class="timecalendar"'.($param["type"] == 'datetime' ? ' showTime="true"' : '').'>
					'.$this->filter_param_setting($param["id"]).'
					<div class="hr"></div>';
					break;

				case 'title':
					$text .= '<h2>'.$param["name"].'</h2>';
					break;
			}
		}
		return $text;
	}

	private function filter_param_setting($id)
	{
		$text = '<a class="param-settings-show"><i class="fa fa-gear"></i></a><div class="param-settings'
		.(empty($_GET["filter_empty_param".$id]) ? ' hide' : '').'">
		<input type="radio" id="filter_empty_param'.$id.'_0" name="filter_empty_param'.$id.'" value=""'
		.(empty($_GET["filter_empty_param".$id]) ? ' checked' : '').'>
		<label for="filter_empty_param'.$id.'_0">'.$this->diafan->_('Все').'</label>

		<input type="radio" id="filter_empty_param'.$id.'_1" name="filter_empty_param'.$id.'" value="1"'
		.(! empty($_GET["filter_empty_param".$id]) && $_GET["filter_empty_param".$id] == 1 ? ' checked' : '').'>
		<label for="filter_empty_param'.$id.'_1">'.$this->diafan->_('Заполненные').'</label>

		<input type="radio" id="filter_empty_param'.$id.'_2" name="filter_empty_param'.$id.'" value="2"'
		.(! empty($_GET["filter_empty_param".$id]) && $_GET["filter_empty_param".$id] == 2 ? ' checked' : '').'>
		<label for="filter_empty_param'.$id.'_2">'.$this->diafan->_('Не заполненные').'</label>
		</div>';
		return $text;
	}

	/**
	 * Поиск по полю "Характеристики"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function save_filter_variable_param($row)
	{
		$value = array();
		$params = DB::query_fetch_all("SELECT id, type FROM {".$this->diafan->table."_param} WHERE trash='0'".(! empty($row["where"]) ? ' '.$row["where"] : ''));
		foreach($params as $param)
		{
			$name = 'param'.$param["id"];
			switch($param["type"])
			{
				case 'text':
				case 'textarea':
				case 'editor':
				case 'email':
				case 'phone':
					$value[$param["id"]] = $this->diafan->filter($_GET, "string", "filter_".$name);
					if($value[$param["id"]])
					{
						$this->diafan->get_nav .= ( $this->diafan->get_nav ? '&amp;' : '?' ).'filter_'.$name.'='.$this->diafan->filter($_GET, "url", "filter_".$name);
						$this->diafan->join .= " INNER JOIN {".$this->diafan->table."_param_element} AS fp".$param["id"]." ON fp".$param["id"].".element_id=e.id";
						$this->diafan->where .= " AND fp".$param["id"].".param_id=".$param["id"]." AND fp".$param["id"].".".(! empty($row["multilang"]) ? "[value]" : 'value')." LIKE '%%".$this->diafan->filter($_GET, "sql", "filter_".$name)."%%'";
					}
					break;

				case 'date':
					$value[$param["id"]] = $this->diafan->filter($_GET, "string", "filter_".$name);
					if($value[$param["id"]])
					{
						$this->diafan->get_nav .= ( $this->diafan->get_nav ? '&amp;' : '?' ).'filter_'.$name.'='.$this->diafan->filter($_GET, "url", "filter_".$name);
						$this->diafan->join .= " INNER JOIN {".$this->diafan->table."_param_element} AS fp".$param["id"]." ON fp".$param["id"].".element_id=e.id";
						$this->diafan->where .= " AND fp".$param["id"].".param_id=".$param["id"]." AND fp".$param["id"].".".(! empty($row["multilang"]) ? "[value]" : 'value')."='".$this->diafan->formate_in_date($this->diafan->filter($_GET, "sql", "filter_".$name))."'";
					}
					break;

				case 'datetime':
					$value[$param["id"]] = $this->diafan->filter($_GET, "string", "filter_".$name);
					if($value[$param["id"]])
					{
						$this->diafan->get_nav .= ( $this->diafan->get_nav ? '&amp;' : '?' ).'filter_'.$name.'='.$this->diafan->filter($_GET, "url", "filter_".$name);
						$this->diafan->join .= " INNER JOIN {".$this->diafan->table."_param_element} AS fp".$param["id"]." ON fp".$param["id"].".element_id=e.id";
						$this->diafan->where .= " AND fp".$param["id"].".param_id=".$param["id"]." AND fp".$param["id"].".".(! empty($row["multilang"]) ? "[value]" : 'value')."='".$this->diafan->formate_in_datetime($this->diafan->filter($_GET, "sql", "filter_".$name))."'";
					}
					break;

				case 'checkbox':
					$value[$param["id"]] = (! empty($_GET["filter_".$name]) ? 1 : 0);
					if($value[$param["id"]])
					{
						$this->diafan->get_nav .= ( $this->diafan->get_nav ? '&amp;' : '?' ).'filter_'.$name.'=1';
						$this->diafan->join .= " INNER JOIN {".$this->diafan->table."_param_element} AS fp".$param["id"]." ON fp".$param["id"].".element_id=e.id";
						$this->diafan->where .= " AND fp".$param["id"].".param_id=".$param["id"]." AND fp".$param["id"].".".(! empty($row["multilang"]) ? "value".$this->diafan->_languages->site : 'value')."='1'";
					}
					break;

				case 'select':
					$value[$param["id"]] = $this->diafan->filter($_GET, "integer", "filter_".$name);
					if($value[$param["id"]])
					{
						$this->diafan->get_nav .= ( $this->diafan->get_nav ? '&amp;' : '?' ).'filter_'.$name.'='.$value[$param["id"]];
						$this->diafan->join .= " INNER JOIN {".$this->diafan->table."_param_element} AS fp".$param["id"]." ON fp".$param["id"].".element_id=e.id";
						$this->diafan->where .= " AND fp".$param["id"].".param_id=".$param["id"]." AND fp".$param["id"].".".(! empty($row["multilang"]) ? "value".$this->diafan->_languages->site : 'value')."=".$value[$param["id"]];
					}
					break;

				case 'multiple':
					if(empty($_GET['filter_'.$name]) || ! is_array($_GET['filter_'.$name]))
					{
						break;
					}
					$value[$param["id"]] = array();
					foreach($_GET['filter_'.$name] as $v)
					{
						$v = $this->diafan->filter($v, "integer");
						if($v)
						{
							$value[$param["id"]][] = $v;
							$this->diafan->get_nav .= ( $this->diafan->get_nav ? '&amp;' : '?' ).'filter_'.$name.'[]='.$v;
							$this->diafan->join .= " INNER JOIN {".$this->diafan->table."_param_element} AS fps".$v." ON fps".$v.".element_id=e.id";
							$this->diafan->where .= "  AND fps".$v.".param_id=".$param["id"]." AND fps".$v.".".(! empty($row["multilang"]) ? "value".$this->diafan->_languages->site : 'value')."=".$v;
						}
					}
					break;

				case 'numtext':
					$value["start_".$param["id"]] = $this->diafan->filter($_GET, "integer", "filter_start_".$name);
					if ($value["start_".$param["id"]])
					{
						$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' ).'filter_start_'.$name.'='.$value["start_".$param["id"]];
						$this->diafan->where .= " AND fpi".$param["id"].".".(! empty($row["multilang"]) ? "value".$this->diafan->_languages->site : 'value').">=".$value["start_".$param["id"]];
					}
					else
					{
						$value["start_".$param["id"]] = '';
					}
					$value["finish_".$param["id"]] = $this->diafan->filter($_GET, "integer", "filter_finish_".$name);
					if ($value["finish_".$param["id"]])
					{
						$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' ).'filter_finish_'.$name.'='.$value["finish_".$param["id"]];
						$this->diafan->where .= " AND fpi".$param["id"].".".(! empty($row["multilang"]) ? "value".$this->diafan->_languages->site : 'value')."<=".$value["finish_".$param["id"]];
					}
					else
					{
						$value["finish_".$param["id"]] = '';
					}
					if($value["start_".$param["id"]] || $value["finish_".$param["id"]])
					{
						$this->diafan->join .= " INNER JOIN {".$this->diafan->table."_param_element} AS fpi".$param["id"]." ON fpi".$param["id"].".element_id=e.id";
						$this->diafan->where .= " AND fpi".$param["id"].".param_id=".$param["id"];
					}
					break;
			}

			switch($param["type"])
			{
				case 'text':
				case 'textarea':
				case 'editor':
				case 'email':
				case 'phone':
					$name = ! empty($row["multilang"]) ? "[value]" : "value";
					break;

				case 'date':
				case 'datetime':
				case 'checkbox':
				case 'select':
				case 'multiple':
				case 'numtext':
					$name = ! empty($row["multilang"]) ? "value".$this->diafan->_languages->site : "value";
					break;
			}

			switch($param["type"])
			{
				case 'text':
				case 'textarea':
				case 'editor':
				case 'email':
				case 'phone':
				case 'date':
				case 'datetime':
				case 'checkbox':
				case 'select':
				case 'multiple':
				case 'numtext':
					if(! empty($_GET["filter_empty_param".$param["id"]]))
					{
						// заполнено
						if($_GET["filter_empty_param".$param["id"]] == 1)
						{
							$this->diafan->join .= " INNER JOIN {".$this->diafan->table."_param_element} AS fpe".$param["id"]." ON fpe".$param["id"].".element_id=e.id";
							$this->diafan->where .= " AND fpe".$param["id"].".param_id=".$param["id"]." AND ".$name."<>''";
							$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' )."filter_empty_param".$param["id"]."=1";
						}
						// не заполнено
						else
						{
							$this->diafan->join .= " LEFT OUTER JOIN {".$this->diafan->table."_param_element} AS fpe".$param["id"]." ON fpe".$param["id"].".element_id=e.id AND fpe".$param["id"].".param_id=".$param["id"]." AND ".$name."<>''";

							$this->diafan->where .= " AND fpe".$param["id"].".id IS null";
							$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' )."filter_empty_param".$param["id"]."=2";
						}
					}
					break;
			}
		}
		return $value;
	}

	/**
	 * Поиск по полю "Нет изображения"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function save_filter_variable_no_img($row)
	{
		if (empty($_GET["filter_no_img"]))
		{
			return;
		}
		$this->diafan->where .= " AND (SELECT COUNT(*) FROM {images} AS i WHERE i.element_id=e.id AND i.element_type='".$this->diafan->element_type()."' AND i.module_name='".$this->diafan->_admin->module."' AND i.param_id=0)=0";
		$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' ).'filter_no_img=1';
		return 1;
	}

	/**
	 * Поиск по полю "Все неактивные"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function save_filter_variable_no_act($row)
	{
		if (empty($_GET["filter_no_act"]))
		{
			return;
		}
		$this->diafan->where .= " AND e.[act]='0'";
		$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' ).'filter_no_act=1';
		return 1;
	}

	/**
	 * Поиск по полю "Нет категории"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function save_filter_variable_no_cat($row)
	{
		if (empty($_GET["filter_no_cat"]))
		{
			return;
		}
		$this->diafan->where .= " AND e.cat_id=0";
		$this->diafan->get_nav .= ($this->diafan->get_nav ? '&amp;' : '?' ).'filter_no_cat=1';
		return 1;
	}

	/**
	 * Поиск по полю "Период показа"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function save_filter_variable_date_start($row)
	{
		$date_start = 0;
		if (! empty($_GET["filter_date_start"]))
		{
			$date_start = $this->diafan->unixdate($_GET["filter_date_start"]);
		}
		$date_finish = 0;
		if (! empty($_GET["filter_date_finish"]))
		{
			$date_finish = $this->diafan->unixdate($_GET["filter_date_finish"]);
		}

		if ($date_start && $date_finish)
		{
			$this->diafan->where .= " AND ((e.date_start <= '".$date_start."' OR e.date_start = '0') AND (e.date_finish >= '".$date_start."' OR e.date_finish = '0')"
			." OR e.date_start >= '".$date_start."' AND e.date_start <= '".$date_finish."')";
		}
		elseif ($date_start)
		{
			$this->diafan->where = " AND (e.date_start<='".$date_start."' OR e.date_start='0') AND (e.date_finish>='".$date_start."' OR e.date_finish='0')";
		}
		elseif ($date_finish)
		{
			$this->diafan->where = " AND (e.date_start<='".$date_finish."' OR e.date_start='0') AND (e.date_finish>='".$date_finish."' OR e.date_finish='0')";
		}
		if($date_start)
		{
			$date_start = date('d.m.Y'.($row["type"] == 'datetime' ? ' H:i' : ''), $date_start);
			$this->diafan->get_nav .= ( $this->diafan->get_nav ? '&amp;' : '?' ).'filter_date_start='.$date_start;
		}
		return $date_start;
	}

	/**
	 * Поиск по полю "Период показа"
	 *
	 * @param array $row информация о текущем поле
	 * @return mixed
	 */
	public function save_filter_variable_date_finish($row)
	{
		$res = 0;
		if (! empty($_GET["filter_date_finish"]))
		{
			$res = $this->diafan->unixdate($_GET["filter_date_finish"]);
			if($res)
			{
				$res = date('d.m.Y'.($row["type"] == 'datetime' ? ' H:i' : ''), $res);
				$this->diafan->get_nav .= ( $this->diafan->get_nav ? '&amp;' : '?' ).'filter_date_finish='.$res;
			}
		}
		return $res;
	}

	/**
	 * Проверяет можно ли удалить текущий элемент строки
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param string $action действие
	 * @return boolean
	 */
	public function check_action($row, $action = '')
	{
		return true;
	}

	/**
	 * Выводит панель групповых операций
	 *
	 * @param boolean $show_filter выводить кнопку "Фильтровать"
	 * @return void
	 */
	public function group_action_panel($show_filter = false)
	{
		if(isset($this->cache["group_action_panel"]))
		{
			echo str_replace(array('id="input', 'for="input_'), array('id="second_input','for="second_input_'), $this->cache["group_action_panel"]);
			return;
		}
		$value = ! empty($_SESSION["group_action"][$this->diafan->_admin->rewrite]) ? $_SESSION["group_action"][$this->diafan->_admin->rewrite] : "";

		$dop = '';

		$html = '
		<div class="action-box">
			<div class="action-unit">
				<input type="checkbox" class="select-all">
				<label><span>'.$this->diafan->_('Выбрать всё').'</span></label>';

		/*foreach ($this->diafan->rows as $row)
		{
			if(! empty($row["count_children"]))
			{
				$is_plus = true;
				break;
			}
		}
		if (! empty($is_plus))
		{
			$html .= ' <span class="item__toggle">';
			$html .= '<i class="fa fa-plus-circle" title="'.$this->diafan->_('Развернуть всё').'"></i>';
			$html .= '</span>';
		}*/

		$html .= '<select name="group_action" class="jq-selectbox">';

		if ($this->diafan->variable_list('actions', 'act') && $this->diafan->_users->roles('edit'))
		{
			$html .= '<option value="block"'.($value == 'block' ? ' selected' : '').'>'.$this->diafan->_('Сделать активным').'</option><option value="unblock"'.($value == 'unblock' ? ' selected' : '').'>'.$this->diafan->_('Сделать неактивным')."</option>";
		}

		if ($this->diafan->variable_list('actions', 'del') && $this->diafan->_users->roles('del'))
		{
			$html .= '<option value="delete" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'"'.($value == 'delete' ? ' selected' : '').'>'
			. $this->diafan->_('Удалить').'</option>';
		}

		if ($this->diafan->variable_list('actions', 'trash') && $this->diafan->_users->roles('del'))
		{
			$html .= '<option value="trash" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись в корзину?').'"'.($value == 'trash' ? ' selected' : '').'>'
			. $this->diafan->_('Удалить').'</option>';
		}

		if($this->diafan->group_action)
		{
			foreach ($this->diafan->group_action as $action => $row)
			{
				$html .= '<option value="'.$action.'"'.($value == $action ? ' selected' : '').''.(! empty($row["module"]) ? ' module="'.$row["module"].'"' : '').(! empty($row["confirm"]) ? ' confirm="'.$this->diafan->_($row["confirm"]).'"' : '').(! empty($row["question"]) ? ' question="'.$this->diafan->_($row["question"]).'"' : '').'>'.$this->diafan->_($row["name"]).'</option>';
			}
		}
		if($this->diafan->_users->roles('edit'))
		{
			$macros = array();
			$files = Custom::read_dir("adm/includes/macros/group");
			foreach($files as $f)
			{
				$m = str_replace('.php', '', $f);
				Custom::inc('adm/includes/macros/group/'.$f);
				$class_name = 'Group_'.$m;
				$class = new $class_name($this->diafan);
				$config = $class->show($value);
				if($config)
				{
					$config["key"] = $m;
					$macros[$config["name"].count($macros)] = $config;
				}
			}
			$rew = '';
			if($this->diafan->_admin->rewrite != $this->diafan->_admin->module)
			{
				$rew = str_replace($this->diafan->_admin->module.'/', '', $this->diafan->_admin->rewrite);
			}
			$files = Custom::read_dir('modules/'.$this->diafan->_admin->module.'/admin/macros');
			foreach($files as $f)
			{
				if(! preg_match('/^'.$this->diafan->_admin->module.'\.admin'.($rew ? '\.'.$rew : '').'\.group\.(.*)\.php$/', $f, $m))
					continue;

				Custom::inc('modules/'.$this->diafan->_admin->module.'/admin/macros/'.$f);

				$class_name = ucfirst($this->diafan->_admin->module).'_admin'.($rew ? '_'.$rew : '').'_group_'.$m[1];
				$class = new $class_name($this->diafan);
				$config = $class->show($value);
				if($config)
				{
					$config["key"] = $m[1];
					$config["module"] = $this->diafan->_admin->module;
					$macros[$config["name"].count($macros)] = $config;
				}
			}
			ksort($macros);

			foreach($macros as $config)
			{
				$prefix = 'macros_'.(! empty($config["module"]) ? $config["module"].'_'.($rew ? $rew.'_' : '') : '').'group_';
				$option_value = $prefix.$config["key"];

				$html .= '<option value="'.$option_value.'"'
				.($value == $option_value ? ' selected' : '')
				.(! empty($config["module"]) ? ' module="'.$config["module"].'"' : '')
				.(! empty($config["confirm"]) ? ' confirm="'.$this->diafan->_($config["confirm"]).'"' : '')
				.(! empty($config["question"]) ? ' question="'.$this->diafan->_($config["question"]).'"' : '')
				.'>'.$this->diafan->_($config["name"]).'</option>';

				if(! empty($config["html"]))
				{
					$dop .= '<div class="action-popup dop_'.$option_value;
					$option_values = array($option_value);
					if(! empty($config["rel"]))
					{
						foreach($config["rel"] as $r)
						{
							$dop .= ' dop_'.$prefix.$r;
							$option_values[] = $prefix.$r;
						}
					}
					if(! in_array($value, $option_values))
					{
						$dop .= ' hide';
					}
					$dop .= '">'.$config["html"].'</div>';
				}
			}
		}

		$html .= '</select>';
		$html .= $dop.'</div>

			<button class="btn btn_blue btn_small group_actions">'.$this->diafan->_('Применить').'</button>';
		if($show_filter)
		{
			$html .= '
			<div class="btn btn_blue btn_small btn_filter">
				<i class="fa fa-filter"></i>
				'.$this->diafan->_('Фильтровать').'
			</div>';
		}
		$html .= '</div>';

		$this->cache["group_action_panel"] = $html;

		echo $html;
	}

	/**
	 * Подгружает дерево
	 *
	 * @return void
	 */
	public function ajax_expand()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			echo "{error:'HASH'}";
			exit;
		}

		$_POST['parent_id'] = $this->diafan->filter($_POST, 'int', 'parent_id');

		ob_start();
		$this->list_row($_POST['parent_id'], false);
		$res['html'] = ob_get_contents();
		ob_end_clean();

		$res['hash'] = $this->diafan->_users->get_hash();

		Custom::inc('plugins/json.php');
		echo to_json($res);
		exit;
	}
}

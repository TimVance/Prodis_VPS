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
 * Edit_functions_admin
 *
 * Функции редактирования полей
 */
class Edit_functions_admin extends Diafan
{
	/**
	 * Редактирование поля "Категория"
	 *
	 * @return void
	 */
	public function edit_variable_cat_id()
	{
		if (! $this->diafan->config("element"))
		{
			return;
		}
		if (! $this->diafan->value)
		{
			$this->diafan->value = $this->diafan->_route->cat;
			$this->diafan->values('cat_id', $this->diafan->_route->cat, true);
		}
		$multi_site = false;

		if ($this->diafan->config("element_multiple"))
		{
			if(! $this->diafan->values('cat_ids'))
			{
				$values = array();
				if (! $this->diafan->is_new)
				{
					$rows = DB::query_fetch_all("SELECT cat_id FROM {%s_category_rel} WHERE element_id=%d AND cat_id>0", $this->diafan->table, $this->diafan->id);
					foreach ($rows as $row)
					{
						if ($row["cat_id"] != $this->diafan->value)
						{
							$values[] = $row["cat_id"];
						}
					}
				}
				$this->diafan->values('cat_ids', $values, true);
			}
			else
			{
				$values = $this->diafan->values('cat_ids');
			}
		}
		if ($this->diafan->config("category_flat"))
		{
			$cats[0] = DB::query_fetch_all("SELECT id, ".($this->diafan->config('category_no_multilang') ? "name" : "[name]").($this->diafan->config('element_site') ? ", site_id AS rel" : "")." FROM {%s_category} WHERE trash='0' ORDER BY sort ASC", $this->diafan->table);
		}
		else
		{
			$max = 1000;
			$cs = DB::query_fetch_all("SELECT id, ".($this->diafan->config('category_no_multilang') ? "name" : "[name]").($this->diafan->config('element_site') ? ", site_id AS rel" : "").", parent_id FROM {%s_category} WHERE trash='0'".( $this->diafan->config("element_multiple") ? " ORDER BY sort ASC LIMIT ".$max : "" ), $this->diafan->table);
			if(count($cs) == $max)
			{
				echo '
				<div class="unit" id="cat_id">
					<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>'
					.(! $this->diafan->value
					  ? '<span class="cat_id_edit_target">-</span>'
					  : '<a class="cat_id_edit_target" href="'.BASE_PATH_HREF.$this->diafan->_admin->module.'/category/edit'.$this->diafan->value.'/">'.DB::query_result("SELECT [name] FROM {%s_category} WHERE id=%d LIMIT 1", $this->diafan->table, $this->diafan->value).'</a>'
					);
					echo ' <a href="javascript:void(0)" class="cat_id_edit"><i class="fa fa-pencil" title="'.$this->diafan->_('Редактировать').'"></i></a>';
					if($this->diafan->value)
					{
						echo ' <a href="javascript:void(0)" class="cat_id_remove red"><i class="fa fa-times-circle" title="'.$this->diafan->_('Удалить').'"></i></a>';
					}
					echo '
					<div class="cat_id_edit_container" style="display:none">';
					echo $this->diafan->_('Изменить категорию').': <input type="text" name="cat_search" value="" size="30">
					<input type="hidden" name="cat_id" value="'.$this->diafan->value.'"></div>';
					if ($this->diafan->config("element_multiple"))
					{
						$cats = array();
						if($values)
						{
							$cats = DB::query_fetch_all("SELECT id, [name] FROM {%s_category} WHERE id IN (%s) ORDER BY sort ASC", $this->diafan->table, implode(',', $values));
						}
						echo '
						<div class="additional_cat_ids">
						<input type="hidden" value="1" name="user_additional_cat_id">
						<div class="infofield">'.$this->diafan->_('Дополнительные категории').'
						<a href="javascript:void(0)" class="cat_id_edit"><i class="fa fa-pencil" title="'.$this->diafan->_('Редактировать').'"></i></a>'.'
						</div>
						<div class="cat_id_edit_container" style="display:none">';
						echo $this->diafan->_('Добавить категорию').': <input type="text" name="cat_search" value="" size="30">
						</div>';
						foreach($cats as $cat)
						{
							echo '<div><input type="checkbox" name="cat_ids[]" value="'.$cat["id"].'" id="input_user_additional_cat_id_'.$cat["id"].'" checked> <label for="input_user_additional_cat_id_'.$cat["id"].'">'.$cat["name"].'</label></div>';
						}
						echo '</div>';
					}
					echo '
				</div>';
				return;
			}
			$current_site_id = 0;
			foreach($cs as $c)
			{
				$cats[$c["parent_id"]][] = $c;
				if($this->diafan->config('element_site') && $c["rel"] != $current_site_id)
				{
					if($current_site_id)
					{
						$multi_site = true;
					}
					$current_site_id = $c["rel"];
				}
			}
		}
		echo '
		<div class="unit" id="cat_id">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
			<select name="'.$this->diafan->key.'">';
		$marker = "&nbsp;&nbsp;";

		echo $this->diafan->get_options($cats, $cats[0], array($this->diafan->value)).'</select>';

		if ($this->diafan->config("element_multiple"))
		{
			echo '
			<br>
			<input type="checkbox" value="1" name="user_additional_cat_id" id="input_user_additional_cat_id"'.( $values ? ' checked' : '' ).'>
			<label for="input_user_additional_cat_id">'.$this->diafan->_('Дополнительные категории').'</label>
			<div class="cat_ids">';
			if($multi_site)
			{
				echo '<input type="checkbox" value="1" name="multi_site" id="input_multi_site">
				<label for="input_multi_site">'.$this->diafan->_('Категории из разных разделов').'</label><br>';
			}
			echo  '<select name="cat_ids[]" multiple="multiple" size="15">
			<option value="all"'.(empty($values) ? ' selected' : '').'>'.$this->diafan->_('Нет').'</option>';
			if (! empty( $cats ))
			{
				echo $this->diafan->get_options($cats, $cats[0], $values);
			}
			echo '</select>
			</div>';
		}
		echo '
		</div>';
	}

	/**
	 * Редактирование поля "Доступ"
	 *
	 * @return void
	 */
	public function edit_variable_access()
	{
		echo '
		<div class="unit" id="access">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>';

		$checked = array ();
		if ($this->diafan->value == '1')
		{
			$checked = DB::query_fetch_value("SELECT role_id FROM {access} WHERE element_id=%d AND module_name='%s' AND element_type='%s'", $this->diafan->id, $this->diafan->_admin->module, $this->diafan->element_type(), "role_id");
		}
		echo '<input type="checkbox" name="access" id="input_access" '.($this->diafan->value=='1'?' checked':'').'> <label for="input_access">'.$this->diafan->_('Доступ только').'</label>
		<div style="margin-left: 30px;">';
		echo '<input type="checkbox" name="access_role[]" id="input_access_role_0" value="0"'.(! $this->diafan->value || in_array(0, $checked) ? ' checked' : '' ).' class="label_full"> <label for="input_access_role_0">'.$this->diafan->_('Гость').'</label>';
		$rows = DB::query_fetch_all("SELECT id, [name] FROM {users_role} WHERE trash='0'");
		foreach ($rows as $row)
		{
			echo '<input type="checkbox" name="access_role[]" id="input_access_role_'.$row['id'].'" value="'.$row['id'].'"'.( !$this->diafan->value || in_array($row['id'], $checked) ? ' checked' : '' ).' class="label_full"> <label for="input_access_role_'.$row['id'].'">'.$row['name'].'</label>';
		}

		echo '</div></div>';
	}

	/**
	 * Редактирование поля "Принадлежит"
	 *
	 * @return void
	 */
	public function edit_variable_parent_id()
	{
		if ($this->diafan->is_new)
		{
			$this->diafan->value = $this->diafan->_route->parent;
		}

		echo '
		<div class="unit" id="parent_id">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
			<span class="change_parent_id">
			<a href="javascript:void(0)" class="dashed_link">';
			if( !$this->diafan->value)
			{
				if($this->diafan->_admin->module == 'site')
				{
					echo $this->diafan->_('Главная');
				}
				else
				{
					echo $this->diafan->_('нет');
				}
			}
			else
			{
				if($this->diafan->variable_list("name", "variable"))
				{
					$list_name = $this->diafan->variable_list("name", "variable");
				}
				else
				{
					$list_name = 'name';
				}
				$list_name = ($this->diafan->variable_multilang($list_name) ? '['.$list_name.']' : $list_name);
				echo DB::query_result("SELECT ".$list_name." FROM {".$this->diafan->table."} WHERE id=%d LIMIT 1", $this->diafan->value) ;
			}
			echo '</a>
			<input name="parent_id" type="hidden" value="'.$this->diafan->value.'">
			</span>
		</div>';
	}

	/**
	 * Редактирование поля "Раздел сайта"
	 *
	 * @return void
	 */
	public function edit_variable_site_id()
	{
		if (! $this->diafan->value)
		{
			$this->diafan->value = $this->diafan->_route->site;
		}

		echo '
		<div class="unit" id="site_id">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
			<select name="'.$this->diafan->key.'"'.($this->diafan->variable_disabled() ? ' disabled' : '').'>';
	$cats[0] = DB::query_fetch_all("SELECT id, [name] FROM {site} WHERE trash='0' AND module_name='%s' ORDER BY sort ASC, id DESC", $this->diafan->_admin->module);
	echo $this->diafan->get_options($cats, $cats[0], array ( $this->diafan->value )).'
			</select>
		</div>';
	}

	/**
	 * Редактирование поля "Расположение"
	 *
	 * @return void
	 */
	public function edit_variable_site_ids()
	{
		$show_in_site_id = array();
		if(! $this->diafan->is_new)
		{
			$show_in_site_id = DB::query_fetch_value("SELECT site_id FROM {".$this->diafan->table."_site_rel} WHERE element_id=%d AND site_id>0", $this->diafan->id, "site_id");
		}
		echo '
		<div class="unit" id="site_ids">
		<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
		<select multiple name="'.$this->diafan->key.'[]" size="12" size="11">
		<option value="all"'.(empty($show_in_site_id) ? ' selected' : '').'>'.$this->diafan->_('Все').'</option>';

		$cats = DB::query_fetch_key_array("SELECT id, [name], parent_id FROM {site} WHERE trash='0' AND [act]='1' ORDER BY sort ASC, id DESC", "parent_id");
		echo $this->diafan->get_options($cats, $cats[0], $show_in_site_id).'
		</select>
		</div>';
	}

	/**
	 * Редактирование поля "Сортировка"
	 *
	 * @return void
	 */
	public function edit_variable_sort()
	{
		if ($this->diafan->is_new || $this->diafan->is_variable("act") && ! $this->diafan->values("act"))
		{
			return;
		}

		if($this->diafan->variable_list("name", "variable"))
		{
			$list_name = $this->diafan->variable_list("name", "variable");
		}
		else
		{
			$list_name = 'name';
		}
		$name = $this->diafan->values($list_name, $this->diafan->id);
		if(! $name)
		{
			$name = $this->diafan->id;
		}

		echo '
		<div class="unit" id="sort">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
			<span class="change_sort">
			<a href="javascript:void(0)" sname="'.$name.'" sort="'.$this->diafan->value.'"'
			. ( $this->diafan->config("element") ? ' cat_id="'.$this->diafan->values("cat_id").'"' : '' )
			. ( $this->diafan->variable_list('plus') ? ' parent_id="'.$this->diafan->values("parent_id").'"' : '' )
			. ( $this->diafan->config("element_site") ? ' site_id="'.$this->diafan->values("site_id").'"' : '' ).' class="dashed_link">'
			. $name.'</a>
			<input name="sort" type="hidden" value="'.$this->diafan->id.'">
			</span>
		</div>';
	}

	/**
	 * Редактирование поля "Псевдоссылка"
	 *
	 * @return void
	 */
	public function edit_variable_rewrite()
	{
		$rewrite = '';
		$redirect = '';
		$redirect_code = 301;
		if (! $this->diafan->is_new)
		{
			$rewrite = DB::query_result("SELECT rewrite FROM {rewrite} WHERE module_name='%s' AND element_id=%d AND element_type='%s' LIMIT 1", $this->diafan->_admin->module, $this->diafan->id, $this->diafan->element_type());
			$row_redirect = DB::query_fetch_array("SELECT redirect, code FROM {redirect} WHERE module_name='%s' AND element_id=%d AND element_type='%s' LIMIT 1", $this->diafan->_admin->module, $this->diafan->id, $this->diafan->element_type());
			$redirect = $row_redirect["redirect"];
			$redirect_code = $row_redirect["code"];
		}
		if(! $redirect_code)
		{
			$redirect_code = 301;
		}
		$rewrite_site = '';
		if (!$rewrite && $this->diafan->_admin->module != "site")
		{
			if ($this->diafan->config("element") && $this->diafan->values("cat_id"))
			{
				if (! $rewrite_site = DB::query_result("SELECT rewrite FROM {rewrite} WHERE module_name='%s' AND element_id=%d AND element_type='cat' LIMIT 1", $this->diafan->_admin->module, $this->diafan->values("cat_id")))
				{
					$rewrite_site = DB::query_result("SELECT rewrite FROM {rewrite} WHERE module_name='site' AND element_id=%d AND element_type='element' LIMIT 1", $this->diafan->values("site_id"));
				}
			}
			elseif ($this->diafan->config("category"))
			{
				if ((! $this->diafan->values("parent_id")
					|| ! $rewrite_site = DB::query_result("SELECT rewrite FROM {rewrite} WHERE module_name='%s' AND element_id=%d AND element_type='cat' LIMIT 1", $this->diafan->_admin->module, $this->diafan->values("parent_id")))
					&& $this->diafan->values("site_id"))
				{
					$rewrite_site = DB::query_result("SELECT rewrite FROM {rewrite} WHERE module_name='site' AND element_id=%d AND element_type='element' LIMIT 1", $this->diafan->values("site_id"));
				}
			}
			elseif ($this->diafan->values("site_id"))
			{
				$rewrite_site = DB::query_result("SELECT rewrite FROM {rewrite} WHERE module_name='site' AND element_id=%d AND element_type='element' LIMIT 1", $this->diafan->values("site_id"));
			}
		}
		echo '
		<div class="unit" id="rewrite">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
			<div class="rewrite_base"><span>'.BASE_PATH.($rewrite_site ? $rewrite_site.'/' : '' ).'</span></div>
			<textarea name="rewrite" class="rewrite_text"  placeholder="'.$this->diafan->_('ЧПУ будет сгенерирован автоматически после сохранения').'">'.$rewrite.'</textarea><div class="rewrite_end">'.$this->diafan->_('ЧПУ оканчивается на').': <b>'.ROUTE_END.'</b> <a href="'.BASE_PATH_HREF.'config/#url" style="float:right;"><i class="fa fa-gear"></i></a></div>
		</div>
		<div class="unit" id="redirect">
			<div class="infofield">'.$this->diafan->_('Редирект на текущую страницу со страницы').':</div>
			<div class="rewrite_base"><span>'.BASE_PATH.'</span></div>
			<textarea name="rewrite_redirect" class="rewrite_text">'.$redirect.'</textarea>
			<span><div class="infobox">'.$this->diafan->_('редирект с кодом ошибки').'</div>
			<input type="text" name="rewrite_code" size="5" value="'.$redirect_code.'"></span>
		</div>';
	}

	/**
	 * Редактирование поля "Анонс"
	 *
	 * @return void
	 */
	public function edit_variable_anons()
	{
		$value = $this->diafan->_route->replace_id_to_link($this->diafan->value);
		$height = $this->diafan->variable('anons', 'height');
		$name = $this->diafan->variable_name('anons');
		if(! $height)
		{
			$height = 400;
		}
		if($this->diafan->is_new)
		{
			$hide_htmleditor = false;
		}
		else
		{
			$hide_htmleditor = in_array('anons', explode(",", $this->diafan->configmodules("hide_".$this->diafan->table."_".$this->diafan->id, "htmleditor")));
		}
		echo $this->diafan->values("anons_plus");
		echo '
		<div class="unit" id="anons">';
			echo '<div class="infofield">'.$name.$this->diafan->help().'</div>

			<input type="checkbox" name="anons_plus" id="input_anons_plus" value="1"'.($this->diafan->values("anons_plus"._LANG) ? ' checked' : '').' title="'.$this->diafan->_('Прибавлять анонс к тексту описания на странице отдельной элемента.').'" class="label_full"> <label for="input_anons_plus">'.$this->diafan->_('Добавлять к описанию').'</label>
			';
			if($this->diafan->_users->htmleditor)
			{
				echo '<input type="checkbox" class="htmleditor_check" name="anons_htmleditor" id="input_anons_htmleditor" value="1"'.($hide_htmleditor ? ' checked' : '').' rel="htmleditor_anons"> <label for="input_anons_htmleditor">'.$this->diafan->_('HTML-код').'</label>';
			}
			echo '<input type="checkbox" name="anons_typograf" id="input_anons_typograf" value="1"> <label for="input_anons_typograf">'.$this->diafan->_('Применить %sтипограф%s', '<a href="http'.(IS_HTTPS ? "s" : '').'://www.artlebedev.ru/tools/typograf/about/" target="_blank">', '</a>')
			.'</label>
			<div class="textfield">';
			echo '<textarea name="anons" id="htmleditor_anons" style="width:100%; height:'.$height.'px"';
			if($this->diafan->_users->htmleditor)
			{
				if($hide_htmleditor)
				{
					echo ' class="htmleditor_off"';
				}
				else
				{
					echo ' class="htmleditor"';
				}
			}
			echo '>'.($value ? str_replace(array ( '<', '>', '"' ), array ( '&lt;', '&gt;', '&quot;' ), str_replace('&', '&amp;', $value)) : '' ).'</textarea>
			</div>
		</div>';
	}

	/**
	 * Редактирование поля "Время редактирования"
	 *
	 * @return void
	 */
	public function edit_variable_timeedit()
	{
		if($this->diafan->is_new)
		{
			return;
		}
		$timeedit = $this->diafan->value ? $this->diafan->value : time();

		echo '
		<div class="unit" id="timeedit">
			<b>
				'.$this->diafan->variable_name().':
			</b>
			'.date("D, d M Y H:i:s", $timeedit).'
			'.$this->diafan->help().'

			<input name="timeedit" type="hidden" value="'.$this->diafan->value.'">
		</div>';
	}

	/**
	 * Редактирование поля "Динамические блоки"
	 *
	 * @return void
	 */
	public function edit_variable_dynamic()
	{
		$element_type = $this->diafan->element_type();

		$dynamic = DB::query_fetch_all("SELECT b.id, b.[name], b.text, b.type FROM {site_dynamic} AS b"
			." INNER JOIN {site_dynamic_module} AS m ON m.dynamic_id=b.id"
			." WHERE b.trash='0'"
			." AND (m.module_name='%h' OR m.module_name='') AND (m.element_type='%h' OR m.element_type='')"
			." GROUP BY b.id ORDER BY b.sort ASC",
			$this->diafan->_admin->module, $element_type
		);

		if(! $this->diafan->is_new)
		{
			$values = DB::query_fetch_key("SELECT dynamic_id, [value], parent, category, value".$this->diafan->_languages->site." as rv FROM {site_dynamic_element} WHERE element_id=%d AND element_type='%s' AND module_name='%s'", $this->diafan->id, $element_type, $this->diafan->_admin->module, "dynamic_id");
		}
		foreach($dynamic as $row)
		{
			$help = $this->diafan->help($row["text"]);
			$value = (! empty($values[$row["id"]]) ? $values[$row["id"]]["value"] : '');
			$rvalue = (! empty($values[$row["id"]]) ? $values[$row["id"]]["rv"] : '');
			$parent = (! empty($values[$row["id"]]) ? $values[$row["id"]]["parent"] : '');
			$category = (! empty($values[$row["id"]]) ? $values[$row["id"]]["category"] : '');

			if($this->diafan->variable_list('plus'))
			{
				$help .= '<br><input type="checkbox" name="dynamic_parent'.$row["id"].'" id="input_dynamic_parent'.$row["id"].'" value="1"'.($parent ? ' checked' : '').'> <label for="input_dynamic_parent'.$row["id"].'">'.$this->diafan->_('Применить к вложенным элементам').'</label>';
			}
			if($this->diafan->config('category'))
			{
				$help .= '<br><input type="checkbox" name="dynamic_category'.$row["id"].'" id="input_dynamic_category'.$row["id"].'" value="1"'.($category ? ' checked' : '').'> <label for="input_dynamic_category'.$row["id"].'">'.$this->diafan->_('Применить к элементам категории').'</label>';
			}
			$row["name"] = '<span>'.$row["name"].'</span> (<a href="'.BASE_PATH_HREF.'site/dynamic/">'.$this->diafan->_('динамический блок').'</a>)';

			switch($row["type"])
			{
				case 'text':
					$this->diafan->show_table_tr_text("dynamic".$row["id"], $row["name"], $value, $help);
					break;

				case 'textarea':
					$this->diafan->show_table_tr_textarea("dynamic".$row["id"], $row["name"], $value, $help);
					break;

				case 'editor':
					$value = $this->diafan->_route->replace_id_to_link($value);
					$key = "dynamic".$row["id"];
					if($this->diafan->is_new)
					{
						$hide_htmleditor = false;
					}
					else
					{
						$hide_htmleditor = in_array($key, explode(",", $this->diafan->configmodules("hide_".$this->diafan->table."_".$this->diafan->id, "htmleditor")));
					}
					$height = 3;
					if($this->diafan->variable_list('plus'))
					{
						$height += 1;
					}
					if($this->diafan->config('category'))
					{
						$height += 1;
					}

					echo '
					<div class="unit" id="'.$key.'">'
						.'<div class="infofield dynamic_infofield">'.$row["name"].$help.'</div>'
						.'<div '.($value ? '' : 'class="dynamic_hide"').'>';
						if($this->diafan->_users->htmleditor)
						{
							echo '<input type="checkbox" class="htmleditor_check" name="'.$key.'_htmleditor" id="input_'.$key.'_htmleditor" value="1"'.($hide_htmleditor ? ' checked' : '').' rel="htmleditor_'.$key.'"> <label for="input_'.$key.'_htmleditor">'.$this->diafan->_('HTML-код').'</label>';
						}
						echo  '<input type="checkbox" name="'.$key.'_typograf" id="input_'.$key.'_typograf" value="1"> <label for="input_'.$key.'_typograf">'.$this->diafan->_('Применить %sтипограф%s', '<a href="http'.(IS_HTTPS ? "s" : '').'://www.artlebedev.ru/tools/typograf/about/" target="_blank">', '</a>')
						.'</label>
						<div class="textfield">
						<textarea name="'.$key.'" id="htmleditor_'.$key.'" style="width:100%; height:400px"';
						if($this->diafan->_users->htmleditor)
						{
							if($hide_htmleditor)
							{
								echo ' class="htmleditor_off"';
							}
							else
							{
								echo ' class="htmleditor"';
							}
						}
						echo '>'.( $value ? str_replace(array ( '<', '>', '"' ), array ( '&lt;', '&gt;', '&quot;' ), str_replace('&', '&amp;', $value)) : '' ).'</textarea>
						</div></div>
					</div>';
					break;

				case 'email':
					$this->diafan->show_table_tr_email("dynamic".$row["id"], $row["name"], $rvalue, $help);
					break;

				case 'date':
					$this->diafan->show_table_tr_date("dynamic".$row["id"], $row["name"], $rvalue, $help);
					break;

				case 'datetime':
					$this->diafan->show_table_tr_datetime("dynamic".$row["id"], $row["name"], $rvalue, $help);
					break;

				case 'numtext':
					$this->diafan->show_table_tr_numtext("dynamic".$row["id"], $row["name"], $rvalue, $help);
					break;

				case 'floattext':
					$this->diafan->show_table_tr_floattext("dynamic".$row["id"], $row["name"], $rvalue, $help);
					break;
			}
		}
	}

	/**
	 * Редактирование поля "Номер страницы"
	 * @return void
	 */
	public function edit_variable_number()
	{
		if ($this->diafan->is_new)
		{
			return;
		}
		echo '<div class="unit" id="number">
				<b>
					'.$this->diafan->variable_name().':
				</b>
				id='.$this->diafan->id.' '.$this->diafan->help().'
			</div>';
	}

	/**
	 * Редактирование поля "Шаблон страницы"
	 * @return void
	 */
	public function edit_variable_theme()
	{
		$theme = $this->diafan->values("theme");
		// значения для нового элемента передаются от родителя
		if($this->diafan->is_new && $this->diafan->variable_list('plus') && $this->diafan->_route->parent)
		{
			if(! isset($this->cache["parent_row"]))
			{
				$this->cache["parent_row"] = DB::query_fetch_array("SELECT * FROM {".$this->diafan->table."} WHERE id=%d LIMIT 1", $this->diafan->_route->parent);
			}
			if(! empty($this->cache["parent_row"]["theme"]))
			{
				$theme = $this->cache["parent_row"]["theme"];
			}
		}
		$themes = $this->diafan->get_themes();


		if($this->diafan->is_new)
		{
			$site_id = $this->diafan->_route->site;
		}
		else
		{
			$site_id = $this->diafan->values('site_id');
		}
		$default = $this->diafan->element_type();
		switch($default)
		{
			case 'cat':
				$default = 'list';
				break;
			case 'element':
				$default = 'id';
				break;
		}
		$comment = '';
		if($general_selected = $this->diafan->configmodules('theme_'.$default, $this->diafan->_admin->module, $site_id))
		{
			$comment .= '<br><span style="color:gray">'.$this->diafan->_('Если шаблон не задан (выбрано значение по умолчанию), то примениться шаблон %s, указанный в настройках модуля.', 'themes/'.$general_selected.'.php').'</span>';
		}


		$general_selected = $general_selected ? $general_selected : 'site.php';


		echo '<div class="unit" id="theme">
			<div class="infofield">
				'.$this->diafan->variable_name().$this->diafan->help().'
			</div>
			<select name="theme" style="width:250px">
				<option value="">'.(! empty($themes[$general_selected]) ? $themes[$general_selected] : 'site.php').'</option>';
		foreach ($themes as $key => $value)
		{
			if ($key == $general_selected)
			{
				continue;
			}
			echo '<option value="'.$key.'"'.($theme == $key ? ' selected' : '').'>'.$value.'</option>';
		}
		echo '
			</select>';
		echo $comment.'
		</div>';
	}

	/**
	 * Формирование списка шаблонов сайта
	 * @return array
	 */
	public function get_themes()
	{
		if(isset($this->cache["themes"]))
		{
			return $this->cache["themes"];
		}
		$this->cache["themes"] = array();
		$rows = Custom::read_dir('themes');
		foreach($rows as $file)
		{
			if (preg_match('!\.(php|inc)$!', $file) && is_file(ABSOLUTE_PATH.Custom::path('themes/'.$file)))
			{
				$key = $file;
				$name = $file;
				$handle = fopen(ABSOLUTE_PATH.Custom::path('themes/'.$file), "r");
				$start = false;
				$ln = 1;
				while (($data = fgets($handle)) !== false)
				{
					if($ln == 1 && (strpos($data, '<?php') === 0 || (strpos($data, '<?') === 0)))
					{
						$start = true;
						continue;
					}
					if($start && preg_match('/\*\s(.+)$/', $data, $m))
					{
						$name = $this->diafan->_($m[1])." [$file]";
						break;
					}
					if(preg_match('/^\</', $data))
					{
						break;
					}
					$ln++;
				}
				fclose($handle);
				$this->cache["themes"][$key] = $name;
			}
		}
		arsort($this->cache["themes"]);
		return $this->cache["themes"];
	}

	/**
	 * Редактирование поля "Шаблон модуля"
	 * @return void
	 */
	public function edit_variable_view()
	{
		$view = $this->diafan->values("view");
		// значения для нового элемента передаются от родителя
		if($this->diafan->is_new && $this->diafan->variable_list('plus') && $this->diafan->_route->parent)
		{
			if(! isset($this->cache["parent_row"]))
			{
				$this->cache["parent_row"] = DB::query_fetch_array("SELECT * FROM {".$this->diafan->table."} WHERE id=%d LIMIT 1", $this->diafan->_route->parent);
			}
			if(! empty($this->cache["parent_row"]["view"]))
			{
				$view = $this->cache["parent_row"]["view"];
			}
		}
		$views = $this->diafan->get_views($this->diafan->_admin->module);

		$default = $this->diafan->element_type();
		switch($default)
		{
			case 'cat':
			case 'brand':
				$default = 'list';
				break;
			case 'element':
				$default = 'id';
				break;
		}


		$comment = $cat_view = '';
		if($this->diafan->config('element'))
		{
			if($this->diafan->is_new)
			{
				$cat_id = $this->diafan->_route->cat;
			}
			else
			{
				$cat_id = $this->diafan->values('cat_id');
			}
			if($cat_id)
			{
				$cat_view = DB::query_result("SELECT view_element FROM {%s_category} WHERE id=%d", $this->diafan->table, $cat_id);
				if($cat_view)
				{
					$comment .= '<br><span style="color:gray">'.$this->diafan->_('Если шаблон не задан (выбрано значение по умолчанию), то примениться шаблон %s, указанный для элементов при редактировании категории.', 'modules/'.$this->diafan->_admin->module.'/views/'.$this->diafan->_admin->module.'.view.'.$cat_view.'.php').'</span>';
				}
			}
		}
		if($this->diafan->is_new)
		{
			$site_id = $this->diafan->_route->site;
		}
		else
		{
			$site_id = $this->diafan->values('site_id');
		}
		if(! $cat_view && $general_selected = $this->diafan->configmodules('view_'.$default, $this->diafan->_admin->module, $site_id))
		{
			$comment .= '<br><span style="color:gray">'.$this->diafan->_('Если шаблон не задан (выбрано значение по умолчанию), то примениться шаблон %s, указанный в настройках модуля.', 'modules/'.$this->diafan->_admin->module.'/views/'.$this->diafan->_admin->module.'.view.'.$general_selected.'.php').'</span>';
		}
		else $general_selected = $cat_view;


		$general_selected = $general_selected ? $general_selected : $default;


		echo '<div class="unit" id="view">
			<div class="infofield">
				'.$this->diafan->variable_name().$this->diafan->help().'
			</div>';
			echo '
			<select name="view" style="width:250px">
				<option value="">'.(! empty($views[$general_selected]) ? $views[$general_selected] : $this->diafan->_admin->module.'.view.'.$default.'.php').'</option>';
			foreach ($views as $key => $value)
			{
				if ($key == $general_selected)
				{
					continue;
				}
				echo '<option value="'.$key.'"'.($view == $key ? ' selected' : '' ).'>'.$value.'</option>';
			}
			echo '</select>';
			echo $comment.'
		</div>';
	}

	/**
	 * Редактирование поля "Шаблон списка элементов"
	 * @return void
	 */
	public function edit_variable_view_rows()
	{
		$view_rows = $this->diafan->values("view_rows");
		// значения для нового элемента передаются от родителя
		if($this->diafan->is_new && $this->diafan->variable_list('plus') && $this->diafan->_route->parent)
		{
			if(! isset($this->cache["parent_row"]))
			{
				$this->cache["parent_row"] = DB::query_fetch_array("SELECT * FROM {".$this->diafan->table."} WHERE id=%d LIMIT 1", $this->diafan->_route->parent);
			}
			if(! empty($this->cache["parent_row"]["view_rows"]))
			{
				$view_rows = $this->cache["parent_row"]["view_rows"];
			}
		}
		$views = $this->diafan->get_views($this->diafan->_admin->module);
		if($this->diafan->is_new)
		{
			$site_id = $this->diafan->_route->site;
		}
		else
		{
			$site_id = $this->diafan->values('site_id');
		}
		if(! $general_selected = $this->diafan->configmodules("view_list_rows", $this->diafan->_admin->module, $site_id))
		{
			$general_selected = 'rows';
		}

		echo '<div class="unit" id="view_rows">
			<div class="infofield">
				'.$this->diafan->variable_name().$this->diafan->help().'
			</div>';
			echo '
			<select name="view_rows" style="width:250px">
				<option value="">'.(! empty($views[$general_selected]) ? $views[$general_selected] : $this->diafan->_admin->module.'.view.rows.php').'</option>';
			foreach ($views as $key => $value)
			{
				if ($key == $general_selected)
				{
					continue;
				}
				echo '<option value="'.$key.'"'.($view_rows == $key ? ' selected' : '' ).'>'.$value.'</option>';
			}
			echo '</select>
		</div>';
	}

	/**
	 * Редактирование поля "Шаблон страницы элемента"
	 * @return void
	 */
	public function edit_variable_view_element()
	{
		$view_element = $this->diafan->values("view_element");
		// значения для нового элемента передаются от родителя
		if($this->diafan->is_new && $this->diafan->variable_list('plus') && $this->diafan->_route->parent)
		{
			if(! isset($this->cache["parent_row"]))
			{
				$this->cache["parent_row"] = DB::query_fetch_array("SELECT * FROM {".$this->diafan->table."} WHERE id=%d LIMIT 1", $this->diafan->_route->parent);
			}
			if(! empty($this->cache["parent_row"]["view_element"]))
			{
				$view_element = $this->cache["parent_row"]["view_element"];
			}
		}
		$views = $this->diafan->get_views($this->diafan->_admin->module);
		if($this->diafan->is_new)
		{
			$site_id = $this->diafan->_route->site;
		}
		else
		{
			$site_id = $this->diafan->values('site_id');
		}
		if(! $general_selected = $this->diafan->configmodules("view_id", $this->diafan->_admin->module, $site_id))
		{
			$general_selected = 'id';
		}

		echo '<div class="unit" id="view_element">
			<div class="infofield">
				'.$this->diafan->variable_name().$this->diafan->help().'
			</div>';
			echo '
			<select name="view_element" style="width:250px">
				<option value="">'.(! empty($views[$general_selected]) ? $views[$general_selected] : $this->diafan->_admin->module.'.view.id.php').'</option>';
			foreach ($views as $key => $value)
			{
				if ($key == $general_selected)
				{
					continue;
				}
				echo '<option value="'.$key.'"'.($view_element == $key ? ' selected' : '' ).'>'.$value.'</option>';
			}
			echo '</select>
		</div>';
	}

	/**
	 * Формирование списка шаблонов модуля
	 *
	 * @param string $module модуль
	 * @return array
	 */
	public function get_views($module)
	{
		if(isset($this->cache["views"][$module]))
		{
			return $this->cache["views"][$module];
		}
		$this->cache["views"][$module] = array();
		$rows = Custom::read_dir("modules/".$module."/views");
		foreach($rows as $file)
		{
			if (preg_match('!\.php$!', $file)
				&& is_file(ABSOLUTE_PATH.Custom::path("modules/".$module."/views/".$file)))
			{
				if (! preg_match('/'.$module.'\.view\.([^\.]+)\.php/', $file, $match))
				{
					continue;
				}
				$key = $match[1];
				$name = $file;
				$handle = fopen(ABSOLUTE_PATH.Custom::path("modules/".$module."/views/".$file), "r");
				$start = false;
				while (($data = fgets($handle)) !== false)
				{
					if(strpos($data, '/**') !== false)
					{
						$start = true;
						continue;
					}
					if($start && preg_match('/\*\s(.+)$/', $data, $m))
					{
						$name = $this->diafan->_($m[1])." [$file]";
						break;
					}
					if(preg_match('/\*\//', $data))
					{
						break;
					}
				}
				fclose($handle);
				$this->cache["views"][$module][$key] = $name;
			}
		}
		arsort($this->cache["views"][$module]);
		return $this->cache["views"][$module];
	}

	/**
	 * Редактирование поля "Редактор"
	 * @return void
	 */
	public function edit_variable_admin_id()
	{
		if($this->diafan->is_new)
			return false;

		echo '
		<div class="unit" id="admin_id">
			<b>'.$this->diafan->variable_name().':</b>
			'.(! $this->diafan->value
			  ? $this->diafan->_('не задан')
			  : '<a href="'.BASE_PATH_HREF.'users/edit'.$this->diafan->value.'/">'.DB::query_result("SELECT CONCAT(fio, ' (', name, ')') FROM {users} WHERE id=%d LIMIT 1", $this->diafan->value).'</a>'
			)
			.$this->diafan->help().'
		</div>';
	}

	/**
	 * Редактирование поля "Автор"
	 * @return void
	 */
	public function edit_variable_user_id()
	{
		echo '
		<div class="unit" id="user_id">
			<div class="infofield">'.$this->diafan->variable_name().'</div>'
			.(! $this->diafan->value
			  ? $this->diafan->_('Гость')
			  : '<a href="'.BASE_PATH_HREF.'users/edit'.$this->diafan->value.'/">'.DB::query_result("SELECT CONCAT(fio, ' (', name, ')') FROM {users} WHERE id=%d LIMIT 1", $this->diafan->value).'</a>'
			);
		if(! $this->diafan->variable('user_id', 'disabled'))
		{
			echo ' <a href="javascript:void(0)" class="user_id_edit"><i class="fa fa-pencil" title="'.$this->diafan->_('Редактировать').'"></i></a>
			<div style="display:none">';
			echo '<br>'.$this->diafan->_('Изменить пользователя на').': <input type="text" name="user_search" value="" size="30" placeholder="'.$this->diafan->_('Начните набирать имя или логин').'">
			<input type="hidden" name="user_id" value="'.$this->diafan->value.'"></div>';
		}
			echo $this->diafan->help().'
		</div>';
	}

	/**
	 * Редактирование поля "Период действия"
	 * @return void
	 */
	public function edit_variable_date_period()
	{
		$time = "";
		if($this->diafan->variable() == 'datetime')
		{
			$time = " H:i";
		}
		echo '
		<div class="unit" id="date_period">
			<div class="infofield">
				'.$this->diafan->variable_name().$this->diafan->help().'
			</div>
			<input type="text" name="date_start" value="'
			.($this->diafan->values("date_start") ? date("d.m.Y".$time, $this->diafan->values("date_start")) : '')
			.'" class="timecalendar" showTime="'.($this->diafan->variable() == 'date' ? 'false' : 'true').'">
			-
			<input type="text" name="date_finish" value="'
			.($this->diafan->values("date_finish") ? date("d.m.Y".$time, $this->diafan->values("date_finish")) : '')
			.'" class="timecalendar" showTime="'.($this->diafan->variable() == 'date' ? 'false' : 'true').'">
		</div>';
	}

	/**
	 * Редактирование поля "Дополнительные параметры"
	 *
	 * @return void
	 */
	public function edit_variable_param($where = '')
	{
		$values = array();
		$rvalues = array();
		$multilang = $this->diafan->variable_multilang("param");

		if (! $this->diafan->is_new)
		{
			$rows_el = DB::query_fetch_all("SELECT value".($multilang ? $this->diafan->_languages->site." as rv, [value]" : "")
			.", param_id FROM {".$this->diafan->table."_param_element} WHERE element_id=%d", $this->diafan->id);
			foreach ($rows_el as $row_el)
			{
				$values[$row_el["param_id"]][]  = $row_el["value"];
				if($multilang)
				{
					$rvalues[$row_el["param_id"]][] = $row_el["rv"];
				}
			}
		}

		// значения списков
		$options = DB::query_fetch_key_array("SELECT [name], id, param_id FROM {".$this->diafan->table."_param_select} ORDER BY sort ASC", "param_id");

		$rows = DB::query_fetch_all("SELECT p.id, p.[name], p.type, p.[text], p.config FROM {".$this->diafan->table."_param} as p "
		    ." WHERE p.trash='0'".$where." ORDER BY p.sort ASC");
		foreach ($rows as $row)
		{
			$help = $this->diafan->help($row["text"]);
			switch($row["type"])
			{
				case 'title':
					$this->diafan->show_table_tr_title("param".$row["id"], $row["name"], $help);
					break;

				case 'text':
				case 'url':
					$value = (! empty($values[$row["id"]]) ? $values[$row["id"]][0] : '');
					$this->diafan->show_table_tr_text("param".$row["id"], $row["name"], $value, $help);
					break;

				case 'textarea':
					$value = (! empty($values[$row["id"]]) ? $values[$row["id"]][0] : '');
					$this->diafan->show_table_tr_textarea("param".$row["id"], $row["name"], $value, $help);
					break;

				case "editor":
					$value = (! empty($values[$row["id"]]) ? $this->diafan->_tpl->htmleditor($values[$row["id"]][0]) : '');
					$this->diafan->show_table_tr_editor("param".$row["id"], $row["name"], $value, $help);
					break;

				case 'email':
					if($multilang)
					{
						$value = (! empty($rvalues[$row["id"]]) ? $rvalues[$row["id"]][0] : '');
					}
					else
					{
						$value = (! empty($values[$row["id"]]) ? $values[$row["id"]][0] : '');
					}
					$this->diafan->show_table_tr_email("param".$row["id"], $row["name"], $value, $help);
					break;

				case 'phone':
					if($multilang)
					{
						$value = (! empty($rvalues[$row["id"]]) ? $rvalues[$row["id"]][0] : '');
					}
					else
					{
						$value = (! empty($values[$row["id"]]) ? $values[$row["id"]][0] : '');
					}
					$this->diafan->show_table_tr_phone("param".$row["id"], $row["name"], $value, $help);
					break;

				case 'date':
					if($multilang)
					{
						$value = (! empty($rvalues[$row["id"]]) ? $this->diafan->unixdate($this->diafan->formate_from_date($rvalues[$row["id"]][0])) : '');
					}
					else
					{
						$value = (! empty($values[$row["id"]]) ? $this->diafan->unixdate($this->diafan->formate_from_date($values[$row["id"]][0])) : '');
					}
					$this->diafan->show_table_tr_date("param".$row["id"], $row["name"], $value, $help);
					break;

				case 'datetime':
					if($multilang)
					{
						$value = (! empty($rvalues[$row["id"]]) ? $this->diafan->unixdate($this->diafan->formate_from_datetime($rvalues[$row["id"]][0])) : '');
					}
					else
					{
						$value = (! empty($values[$row["id"]]) ? $this->diafan->unixdate($this->diafan->formate_from_datetime($values[$row["id"]][0])) : '');
					}
					$this->diafan->show_table_tr_datetime("param".$row["id"], $row["name"], $value, $help);
					break;

				case 'numtext':
					if($multilang)
					{
						$value = (! empty($rvalues[$row["id"]]) ? $rvalues[$row["id"]][0] : 0);
					}
					else
					{
						$value = (! empty($values[$row["id"]]) ? $values[$row["id"]][0] : 0);
					}
					$this->diafan->show_table_tr_numtext("param".$row["id"], $row["name"], $value, $help);
					break;

				case 'floattext':
					if($multilang)
					{
						$value = (! empty($rvalues[$row["id"]]) ? $rvalues[$row["id"]][0] : 0);
					}
					else
					{
						$value = (! empty($values[$row["id"]]) ? $values[$row["id"]][0] : 0);
					}
					$this->diafan->show_table_tr_floattext("param".$row["id"], $row["name"], $value, $help);
					break;

				case 'checkbox':
					if($multilang)
					{
						$value = (! empty($rvalues[$row["id"]]) ? $rvalues[$row["id"]][0] : 0);
					}
					else
					{
						$value = (! empty($values[$row["id"]]) ? $values[$row["id"]][0] : 0);
					}
					$this->diafan->show_table_tr_checkbox("param".$row["id"], $row["name"], $value, $help);
					break;

				case 'radio':
					$opts = array(array('name' => $this->diafan->_('Нет'), 'id' => ''));
					if(! empty($options[$row["id"]]))
					{
						$opts = array_merge($opts, $options[$row["id"]]);
					}
					if($multilang)
					{
						$value = (! empty($rvalues[$row["id"]]) ? $rvalues[$row["id"]][0] : 0);
					}
					else
					{
						$value = (! empty($values[$row["id"]]) ? $values[$row["id"]][0] : 0);
					}
					$this->diafan->show_table_tr_radio("param".$row["id"], $row["name"], $value, $help, false, $opts);
					break;

				case 'select':
					$opts = array(array('name' => $this->diafan->_('Нет'), 'id' => ''));
					if(! empty($options[$row["id"]]))
					{
						$opts = array_merge($opts, $options[$row["id"]]);
					}
					if($multilang)
					{
						$value = (! empty($rvalues[$row["id"]]) ? $rvalues[$row["id"]][0] : 0);
					}
					else
					{
						$value = (! empty($values[$row["id"]]) ? $values[$row["id"]][0] : 0);
					}
					$this->diafan->show_table_tr_select("param".$row["id"], $row["name"], $value, $help, false, $opts);
					break;

				case 'multiple':
					if($multilang)
					{
						$value = (! empty($rvalues[$row["id"]]) ? $rvalues[$row["id"]] : array());
					}
					else
					{
						$value = (! empty($values[$row["id"]]) ? $values[$row["id"]] : array());
					}
					$this->diafan->show_table_tr_multiple("param".$row["id"], $row["name"], $value, $help, false, (! empty($options[$row["id"]]) ? $options[$row["id"]] : array()));
					break;

				case 'attachments':
					Custom::inc('modules/attachments/admin/attachments.admin.inc.php');
					$attachments = new Attachments_admin_inc($this->diafan);
					$attachments->edit_param($row["id"], $row["name"], $row["text"], $row["config"]);
					break;

				case 'images':
					Custom::inc('modules/images/admin/images.admin.inc.php');
					$images = new Images_admin_inc($this->diafan);
					$images->edit_param($row["id"], $row["name"], $row["text"]);
					break;
			}
		}
	}

	/**
	 * Редактирование поля "Значения поля конструктора"
	 * @return void
	 */
	public function edit_variable_param_select()
	{
		$value = array();
		if (! $this->diafan->is_new && in_array($this->diafan->values("type"), array('select', 'multiple', 'checkbox', 'radio')))
		{
			$rows_select = DB::query_fetch_all("SELECT id, sort, value, "
			.($this->diafan->variable_multilang("name") ? "[name]" : "name")
			." FROM {".$this->diafan->table."_select}"
			." WHERE param_id=%d ORDER BY sort ASC", $this->diafan->id);
			foreach ($rows_select as $row_select)
			{
				if ($this->diafan->values("type") == 'checkbox')
				{
					$value[$row_select["value"]] = $row_select["name"];
				}
				else
				{
					$value[] = $row_select;
				}
			}
		}

		echo '
		<div class="unit" id="param">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
			<div class="param_container">
			<a href="javascript:void(0)" class="param_sort_name">'.$this->diafan->_('Сортировать по алфавиту').'</a>
			';

		$fields = false;
		$param_textarea = '';
		if (in_array($this->diafan->values("type"), array('select', 'multiple', 'radio')))
		{
			foreach ($value as $row)
			{
				echo '
				<div class="param">
					<input type="hidden" name="param_id[]" value="'.$row["id"].'">
					<input type="text" name="paramv[]" value="'.str_replace('"', '&quot;', $row["name"]).'">
					<span class="param_actions">
						<a href="javascript:void(0)" action="delete_param" class="delete" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'"><i class="fa fa-close" title="'.$this->diafan->_('Удалить').'"></i></a>
						<a href="javascript:void(0)" action="up_param" title="'.$this->diafan->_('Выше').'">↑</a>
						<a href="javascript:void(0)" action="down_param" title="'.$this->diafan->_('Ниже').'">↓</a>
					</span>
				</div>';
				$fields = true;
				$param_textarea .= str_replace(array('<', '>'), array('&lt;', '&gt;'), $row["name"])."\n" ;
			}
		}
		if (! $fields)
		{
			echo '
			<div class="param">
				<input type="hidden" name="param_id[]" value="">
				<input type="text" name="paramv[]" value="">
				<span class="param_actions">
					<a href="javascript:void(0)" action="delete_param" class="delete" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'"><i class="fa fa-close" title="'.$this->diafan->_('Удалить').'"></i></a>
					<a href="javascript:void(0)" action="up_param" title="'.$this->diafan->_('Выше').'">↑</a>
					<a href="javascript:void(0)" action="down_param" title="'.$this->diafan->_('Ниже').'">↓</a>
				</span>
			</div>';
		}
		echo '
				<a href="javascript:void(0)" class="param_plus" title="'.$this->diafan->_('Добавить').'"><i class="fa fa-plus-square"></i> '.$this->diafan->_('Добавить').'</a>
			</div>
			<div class="infobox">
				<input type="checkbox" value="1" name="param_textarea_check" id="input_param_textarea_check"> <label for="input_param_textarea_check">'.$this->diafan->_('Быстрое редактирование').'</label>
				<div class="param_textarea">
					<textarea name="param_textarea" cols="49" rows="10">'.$param_textarea.'</textarea>
				</div>
			</div>
		</div>
		<div class="unit" id="param_check">
			<div class="infofield">'.$this->diafan->variable_name().'</div>
			'.$this->diafan->_('да').' <input type="text" name="paramk_check1" value="'
			.(! empty($value[1]) && $this->diafan->values("type") == 'checkbox' ? str_replace('"', '&quot;', $value[1]) : '')
			.'">
			&nbsp;&nbsp;
			'.$this->diafan->_('нет').' <input type="text" name="paramk_check0" value="'
			.(! empty($value[0]) && $this->diafan->values("type") == 'checkbox' ? str_replace('"', '&quot;', $value[0]) : '').'">
		</div>';
		$types = $this->diafan->variable("type", "select");
		if(! empty($types["attachments"]))
		{
			Custom::inc('modules/attachments/admin/attachments.admin.inc.php');
			$attachments = new Attachments_admin_inc($this->diafan);
			$attachments->edit_config_param($this->diafan->values("config"));
		}
		if(! empty($types["images"]))
		{
			Custom::inc('modules/images/admin/images.admin.inc.php');
			$images = new Images_admin_inc($this->diafan);
			$images->edit_config_param($this->diafan->values("config"));
		}
	}

	/**
	 * Редактирование поля "Похожие элементы"
	 *
	 * @return void
	 */
	public function edit_variable_rel_elements()
	{
		$rel_two_sided = $this->diafan->configmodules("rel_two_sided", $this->diafan->_admin->module, (! empty($this->values["site_id"]) ? $this->values["site_id"] : $this->diafan->_route->site));

		if($this->diafan->variable_list("name", "variable"))
		{
			$name = $this->diafan->variable_list("name", "variable");
		}
		else
		{
			$name = 'name';
		}

		echo '
		<div class="unit" id="rel_elements" rel_two_sided="'.($rel_two_sided ? 'true' : '').'">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
				<div class="rel_elements">';
		if ( ! $this->diafan->is_new)
		{
			$rows = DB::query_fetch_all("SELECT s.id, s.[".$name."], s.site_id FROM {".$this->diafan->table."} AS s"
					." INNER JOIN {".$this->diafan->table."_rel} AS r ON s.id=r.rel_element_id AND r.element_id=%d"
					.($rel_two_sided ? " OR s.id=r.element_id AND r.rel_element_id=".$this->diafan->id : "")
					." WHERE s.trash='0' GROUP BY s.id",
					$this->diafan->id
				);
			foreach ($rows as $row)
			{
				$link = $this->diafan->_route->link($row["site_id"], $row["id"], $this->diafan->table);
				if($this->diafan->is_variable("images") || $this->diafan->is_variable("image"))
				{
					$row_img = DB::query_fetch_array("SELECT name, folder_num FROM {images} WHERE element_id=%d AND module_name='%s' AND element_type='element' AND trash='0' ORDER BY sort ASC LIMIT 1", $row["id"], $this->diafan->table);
				}
				echo '
				<div class="rel_element" element_id="'.$this->diafan->id.'" rel_id="'.$row["id"].'">'
					.(! empty($row_img) ? '<img src="'.BASE_PATH.USERFILES.'/small/'.($row_img["folder_num"] ? $row_img["folder_num"].'/' : '').$row_img["name"].'">' : '').$this->diafan->short_text($row[$name], 50)
					.'
					<div class="rel_element_actions">';
				if($this->diafan->configmodules("page_show", $this->diafan->_admin->module, $this->diafan->_route->site))
				{
					echo '
						<a href="'.BASE_PATH.$link.'" target="_blank"><i class="fa fa-laptop"></i> '.$this->diafan->_('Посмотреть на сайте').'</a>';
				}
				echo '
						<a href="javascript:void(0)" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'" action="delete_rel_good" class="delete"><i class="fa fa-times-circle"></i> '.$this->diafan->_('Удалить').'</a>
					</div>
				</div>';
			}
		}
		echo '</div>
			<a href="javascript:void(0)" class="rel_module_plus btn btn_small btn_blue plink">
				<i class="fa fa-plus-square"></i> '.$this->diafan->_('Добавить').'
			</a>
		</div>';
	}


    /**
     * Редактирование поля "Похожие элементы"
     *
     * @return void
     */
    public function edit_variable_rel_goods()
    {
        $rel_two_sided = $this->diafan->configmodules("rel_two_sided", $this->diafan->_admin->module, (! empty($this->values["site_id"]) ? $this->values["site_id"] : $this->diafan->_route->site));

        $name = 'name';

        $table = 'shop';
        $rel = 'users';

        echo '
		<div class="unit" id="rel_goods" rel_two_sided="'.($rel_two_sided ? 'true' : '').'">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
				<div class="rel_goods">';
        if ( ! $this->diafan->is_new)
        {
            $rows = DB::query_fetch_all("SELECT s.id, c.[name] AS cat_name, s.[".$name."], s.site_id FROM {".$table."} AS s"
                ." INNER JOIN {".$rel."_rel} AS r ON s.id=r.rel_element_id AND r.element_id=%d"
                .($rel_two_sided ? " OR s.id=r.element_id AND r.rel_element_id=".$this->diafan->id : "")
                ." RIGHT JOIN {shop_category} AS c ON c.id=s.cat_id"
                ." WHERE s.trash='0' GROUP BY s.id ORDER BY s.[name]",
                $this->diafan->id
            );
            foreach ($rows as $row)
            {
                $link = $this->diafan->_route->link($row["site_id"], $row["id"], $this->diafan->table);
                if($this->diafan->is_variable("images") || $this->diafan->is_variable("image"))
                {
                    $row_img = DB::query_fetch_array("SELECT name, folder_num FROM {images} WHERE element_id=%d AND module_name='%s' AND element_type='element' AND trash='0' ORDER BY sort ASC LIMIT 1", $row["id"], $this->diafan->table);
                }
                echo '
				<div class="rel_good" element_id="'.$this->diafan->id.'" rel_id="'.$row["id"].'">'
                    .(! empty($row_img) ? '<img src="'.BASE_PATH.USERFILES.'/small/'.($row_img["folder_num"] ? $row_img["folder_num"].'/' : '').$row_img["name"].'">' : '').$this->diafan->short_text($row[$name], 50).'('.$row["cat_name"].')'
                    .'
					<div class="rel_good_actions">';
                if($this->diafan->configmodules("page_show", $this->diafan->_admin->module, $this->diafan->_route->site))
                {
                    echo '
						<a href="'.BASE_PATH.$link.'" target="_blank"><i class="fa fa-laptop"></i> '.$this->diafan->_('Посмотреть на сайте').'</a>';
                }
                echo '
						<a href="javascript:void(0)" confirm="'.$this->diafan->_('Вы действительно хотите удалить запись?').'" action="delete_rel_good" class="delete"><i class="fa fa-times-circle"></i> '.$this->diafan->_('Удалить').'</a>
					</div>
				</div>';
            }
        }
        echo '</div>
			<a href="javascript:void(0)" class="rel_module_good_plus btn btn_small btn_blue plink">
				<i class="fa fa-plus-square"></i> '.$this->diafan->_('Добавить').'
			</a>
		</div>';
    }


	/**
	 * Редактирование поля "Счетчик просмотров"
	 * @return void
	 */
	public function edit_variable_counter_view()
	{
		if ($this->diafan->is_new || ! $this->diafan->configmodules("counter"))
		{
			return;
		}
		$counter_view = DB::query_result("SELECT count_view FROM {%s_counter} WHERE element_id=%d LIMIT 1", $this->diafan->table, $this->diafan->id);
		if(! $counter_view)
		{
			$counter_view = 0;
		}

		echo '
		<div class="unit" id="counter_view">
			<b>'.$this->diafan->variable_name().':</b> '.$counter_view.'
		</div>';
	}

    /**
     * Редактирование поля "Регламент"
     * @return void
     */
    public function edit_variable_replication()
    {
        echo '
        
            <div class="tabs-period">
                повторяемость
                <span data-class="days" class="days active">День</span>
                <span data-class="weeks" class="weeks">Неделя</span>
                <span data-class="months" class="months">Месяц</span>
            </div>
            <div class="tabs-wrapper">
                <div class="days active">
                    каждый <input type="number" class="day" value="1"> день с интервалом <input type="number" class="month" value="0"> мес
                    <div class="time-wrapper">
                        Время выполнения: <input type="time" class="timepickerr" value="00:00">
                    </div>
                </div>
                <div class="weeks">
                    каждую <input type="number" class="week" value="1"> неделю
                    <div class="weeks-day">
                        <span><input value="1" data-name="пн" type="checkbox">пн</span>
                        <span><input value="2" data-name="вт" type="checkbox">вт</span>
                        <span><input value="3" data-name="ср" type="checkbox">ср</span>
                        <span><input value="4" data-name="чт" type="checkbox">чт</span>
                        <span><input value="5" data-name="пт" type="checkbox">пт</span>
                        <span><input value="6" data-name="сб" type="checkbox">сб</span>
                        <span><input value="7" data-name="вс" type="checkbox">вс</span>
                    </div>
                    <div class="time-wrapper">
                        Время выполнения: <input type="time" class="timepickerr" value="00:00">
                    </div>
                </div>
                <div class="months">
                    <div class="number-month active">
                        <label><input data-radio="1" type="radio" checked>каждое</label>
                        <input type="number" value="1" class="number"> число каждого 
                        <input type="number" value="1" class="month"> месяца
                    </div>
                    <div class="number-week">
                        <label><input data-radio="2" type="radio">каждый</label>
                        <select class="day-week">
                            <option value="1">Первый</option>
                            <option value="2">Второй</option>
                            <option value="3">Третий</option>
                            <option value="4">Четвертый</option>
                            <option value="5">Последний</option>
                        </select>
                        <select class="name-week">
                            <option value="1">Понедельник</option>
                            <option value="2">Вторник</option>
                            <option value="3">Среда</option>
                            <option value="4">Четверг</option>
                            <option value="5">Пятница</option>
                            <option value="6">Суббота</option>
                            <option value="7">Воскресенье</option>
                        </select>
                        каждого <input type="number" value="1" class="month"> месяца
                    </div>
                    <div class="time-wrapper">
                        Время выполнения: <input type="time" class="timepickerr" value="00:00">
                    </div>
                </div>
            </div>
            <div class="period-text"></div>
        ';
    }

	/**
	 * Редактирование поля "Описание, тег Description"
	 * @return void
	 */
	public function edit_variable_descr()
	{
		echo '
		<div class="unit" id="descr">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'
			<span class="maxlength">'.(130 - utf::strlen($this->diafan->value)).'</span>
			</div>
			<textarea name="descr" cols="49" rows="5"'.($this->diafan->variable_disabled() ? ' disabled' : '').' class="inp_maxlength" maxlength_recomm="130">'.( $this->diafan->value ? str_replace(array ('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $this->diafan->value) : '' ).'</textarea>
		</div>';
	}

	/**
	 * Редактирование поля "Changefreq"
	 *
	 * @return void
	 */
	public function edit_variable_changefreq()
	{
		echo '
		<div class="unit" id="changefreq">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
			<select name="changefreq">
			<option value="monthly">monthly</option>
			<option value="always"'.($this->diafan->value == 'always' ? ' selected' : '').'>always</option>
			<option value="hourly"'.($this->diafan->value == 'hourly' ? ' selected' : '').'>hourly</option>
			<option value="daily"'.($this->diafan->value == 'daily' ? ' selected' : '').'>daily</option>
			<option value="weekly"'.($this->diafan->value == 'weekly' ? ' selected' : '').'>weekly</option>
			<option value="yearly"'.($this->diafan->value == 'yearly' ? ' selected' : '').'>yearly</option>
			<option value="never"'.($this->diafan->value == 'never' ? ' selected' : '').'>never</option>
			</select>
		</div>';
	}

	/**
	 * Редактирование поля "Бэкенд"
	 * @return void
	 */
	public function edit_variable_backend()
	{
		if(! $variable = $this->diafan->variable('backend', 'variable'))
		{
			$variable = 'backend';
		}
		$attr = '';
		$class = '';
		if($depend = $this->diafan->variable('backend', 'depend'))
		{
			$attr = ' depend="'.$depend.'"';
			$class = " depend_field";
		}
		$rows = array();
		$rs = Custom::read_dir("modules/".$this->diafan->_admin->module."/backend");
		foreach($rs as $row)
		{
			if (Custom::exists('modules/'.$this->diafan->_admin->module.'/backend/'.$row.'/'.$this->diafan->_admin->module.'.'.$row.'.admin.php'))
			{
				Custom::inc('modules/'.$this->diafan->_admin->module.'/backend/'.$row.'/'.$this->diafan->_admin->module.'.'.$row.'.admin.php');
				$config_class = ucfirst($this->diafan->_admin->module).'_'.$row.'_admin';
				$c = new $config_class($this->diafan);
				$rows[] = array("name" => $row, "config" => $c->config);

				if (Custom::exists('modules/'.$this->diafan->_admin->module.'/backend/'.$row.'/'.$this->diafan->_admin->module.'.'.$row.'.admin.js'))
				{
					$this->diafan->_admin->js_view[] = 'modules/'.$this->diafan->_admin->module.'/backend/'.$row.'/'.$this->diafan->_admin->module.'.'.$row.'.admin.js';
				}
			}
		}
		$values = array();
		if ( ! $this->diafan->is_new)
		{
			$values = unserialize($this->diafan->values('params'));
		}
		echo '
		<div class="unit'.$class.'"'.$attr.' id="backend">
			<div class="infofield">
				'.$this->diafan->variable_name().'
			</div>
			<select name="backend"><option value="">-</option>';
		foreach($rows as $row)
		{
			echo '<option value="'.$row["name"].'"'
			.($this->diafan->values($variable) == $row["name"] ? ' selected' : '').'>'
			.$this->diafan->_($row["config"]["name"]).'</option>';
		}
		echo '</select>
		</div>';

		$class = " depend_field";

		foreach ($rows as $row)
		{
			foreach ($row["config"]["params"] as $key => $name)
			{
				if($depend)
				{
					$field_attr = ' depend="'.$depend.',backend='.$row["name"].'"';
				}
				else
				{
					$field_attr = ' depend="backend='.$row["name"].'"';
				}
				$select = array();
				if(is_array($name))
				{
					$type = (! empty($name["type"]) ? $name["type"] : 'text');
					$help = (! empty($name["help"]) ? $name["help"] : '');
					$select = (! empty($name["select"]) ? $name["select"] : array());
					if($type == 'function')
					{
						$config_class = ucfirst($this->diafan->_admin->module).'_'.$row["name"].'_admin';
						$c = new $config_class($this->diafan);
						if (is_callable(array(&$c, "edit_variable_".$key)))
						{
							ob_start();
							call_user_func_array(array(&$c, "edit_variable_".$key), array((! empty($values[$key]) ? $values[$key] : ''), $values));
							$text = ob_get_contents();
							ob_end_clean();
							echo preg_replace('/class="unit(.*?)"/', 'class="unit$1'.$class.'"'.$field_attr, $text);
							continue;
						}
					}
					$name = $name["name"];
				}
				else
				{
					$type = 'text';
					$help = '';
				}

				if($type == 'none')
					continue;

				$value = (! empty($values[$key]) ? $values[$key] : '');
				echo '<div class="unit'.$class.'"'.$field_attr.'>';
		
				switch($type)
				{
					case 'checkbox':
						echo '<input type="checkbox" value="1"'.($value ? ' checked' : '').' name="'.$row["name"].'_'.$key.'" id="input_'.$row["name"].'_'.$key.'">
						<label for="input_'.$row["name"].'_'.$key.'"><b>'.$this->diafan->_($name).'</b>'.$this->diafan->help($help).'</label>';
					break;

					case 'select':
						echo '<div class="infofield">'.$this->diafan->_($name).$this->diafan->help($help).'</div>
						<select name="'.$row["name"].'_'.$key.'">';
						foreach($select as $k => $v)
						{
							echo '<option value="'.$k.'"'.($value == $k ? ' selected' : '').'>'.$v.'</option>';
						}
						echo '</select>';
					break;

					case 'info':
						echo '<p>'.$this->diafan->_($name).$this->diafan->help($help).'</p>';
					break;
		
					default:
						echo '<div class="infofield">'.$this->diafan->_($name).$this->diafan->help($help).'</div>
						<input type="text" value="'.$value.'" name="'.$row["name"].'_'.$key.'">';
					
				}
				echo '</div>';
			}
		}
	}

	//----------------------------------------------------------------------//
	//функции редактирования конфигурации


	/**
	 * Редактирование поля "Бэкенд" для файла настроек
	 * @return void
	 */
	public function edit_config_variable_backend()
	{
		if(! $variable = $this->diafan->variable('backend', 'variable'))
		{
			$variable = 'backend';
		}
		$attr = '';
		$class = '';
		if($depend = $this->diafan->variable('backend', 'depend'))
		{
			$attr = ' depend="'.$depend.'"';
			$class = " depend_field";
		}
		$rows = array();
		$rs = Custom::read_dir("modules/".$this->diafan->_admin->module."/backend");
		foreach($rs as $row)
		{
			if (Custom::exists('modules/'.$this->diafan->_admin->module.'/backend/'.$row.'/'.$this->diafan->_admin->module.'.'.$row.'.admin.php'))
			{
				Custom::inc('modules/'.$this->diafan->_admin->module.'/backend/'.$row.'/'.$this->diafan->_admin->module.'.'.$row.'.admin.php');
				$config_class = ucfirst($this->diafan->_admin->module).'_'.$row.'_admin';
				$c = new $config_class($this->diafan);
				$rows[] = array("name" => $row, "config" => $c->config);

				if (Custom::exists('modules/'.$this->diafan->_admin->module.'/backend/'.$row.'/'.$this->diafan->_admin->module.'.'.$row.'.admin.js'))
				{
					$this->diafan->_admin->js_view[] = 'modules/'.$this->diafan->_admin->module.'/backend/'.$row.'/'.$this->diafan->_admin->module.'.'.$row.'.admin.js';
				}
			}
		}
		echo '		
		<div class="unit'.$class.'"'.$attr.' id="backend">
			<div class="infofield">
				'.$this->diafan->variable_name().'
			</div>
			<select name="backend"><option value="">-</option>';
		foreach($rows as $row)
		{
			echo '<option value="'.$row["name"].'"'
			.($this->diafan->values($variable) == $row["name"] ? ' selected' : '').'>'
			.$this->diafan->_($row["config"]["name"]).'</option>';
		}
		echo '</select>
		</div>';
		$class = " depend_field";

		foreach ($rows as $row)
		{
			if(empty($row["config"]["params"]))
				continue;

			$values = array();
			foreach ($row["config"]["params"] as $key => $array)
			{
				$values[$key] = $this->diafan->values($row["name"].'_'.$key);
			}

			foreach ($row["config"]["params"] as $key => $array)
			{
				if($depend)
				{
					$field_attr = ' depend="'.$depend.',backend='.$row["name"].'"';
				}
				else
				{
					$field_attr = ' depend="backend='.$row["name"].'"';
				}
				$select = array();
				$type = (! empty($array["type"]) ? $array["type"] : 'text');
				$help = (! empty($array["help"]) ? $array["help"] : '');
				$select = (! empty($array["select"]) ? $array["select"] : array());
				$name = (! empty($array["name"]) ? $array["name"] : $key);
				$value = (! empty($values[$key]) ? $values[$key] : '');

				if($type == 'none')
					continue;

				if($type == 'function')
				{
					$config_class = ucfirst($this->diafan->_admin->module).'_'.$row["name"].'_admin';
					$c = new $config_class($this->diafan);
					if (is_callable(array(&$c, "edit_variable_".$key)))
					{
						if (is_callable(array(&$c, "edit_variable_".$key)))
						{
							ob_start();
							call_user_func_array(array(&$c, "edit_variable_".$key), array($value, $values));
							$text = ob_get_contents();
							ob_end_clean();
							echo preg_replace('/class="unit(.*?)"/', 'class="unit$1'.$class.'"'.$field_attr, $text);
							continue;
						}
						continue;
					}
				}

				echo '<div class="unit'.$class.'"'.$field_attr.'>';
		
				switch($type)
				{
					case 'checkbox':
						echo '<input type="checkbox" value="1"'.($value ? ' checked' : '').' name="'.$row["name"].'_'.$key.'" id="input_'.$row["name"].'_'.$key.'">
						<label for="input_'.$row["name"].'_'.$key.'"><b>'.$this->diafan->_($name).'</b>'.$this->diafan->help($help).'</label>';
					break;

					case 'select':
						echo '<div class="infofield">'.$this->diafan->_($name).$this->diafan->help($help).'</div>
						<select name="'.$row["name"].'_'.$key.'">';
						foreach($select as $k => $v)
						{
							echo '<option value="'.$k.'"'.($value == $k ? ' selected' : '').'>'.$v.'</option>';
						}
						echo '</select>';
					break;

					case 'info':
						echo '<p>'.$this->diafan->_($name).$this->diafan->help($help).'</p>';
					break;
		
					default:
						echo '<div class="infofield">'.$this->diafan->_($name).$this->diafan->help($help).'</div>
						<input type="text" value="'.$value.'" name="'.$row["name"].'_'.$key.'">';
					
				}
				echo '</div>';
			}
		}
	}

	/**
	 * Редактирование поля "Электронный адрес" для конфигурации модуля
	 *
	 * @return void
	 */
	public function edit_config_variable_emailconf()
	{
		$array = array (
			'' => EMAIL_CONFIG,
			1 => $this->diafan->_('другой')
		);

		echo '
		<div class="unit" id="emailconf">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
			<select name="emailconf" id="text_em">';
			foreach($array as $k => $v)
			{
				echo '<option value="'.$k.'"'. ($k == $this->diafan->value ? ' selected' : '').'>'.$v.'</option>';
			}
			echo '</select>
			<div id="emailtext" class="depend_field" depend="emailconf">
				<div class="infobox">'.$this->diafan->variable_name("email").'</div>
				<input type="text" name="email" value="'.$this->diafan->values("email").'">
			</div>
		</div>';
	}

	/**
	 * Редактирование поля "Электронный адрес администратора" для конфигурации модуля
	 *
	 * @return void
	 */
	public function edit_config_variable_emailconfadmin()
	{
		$array = array (
			'' => EMAIL_CONFIG,
			1 => $this->diafan->_('другой')
		);

		$values = explode(',', $this->diafan->values("email_admin"));

		echo '
		<div class="unit" id="emailconfadmin">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>
			<select name="emailconfadmin">';
			foreach($array as $k => $v)
			{
				echo '<option value="'.$k.'"'. ($k == $this->diafan->value ? ' selected' : '').'>'.$v.'</option>';
			}
			echo '
			</select>
			<div id="emailadmintext" class="depend_field" depend="emailconfadmin">
			'.$this->diafan->variable_name("email_admin").'<br>';
		if($values)
		{
			foreach ($values as $v)
			{
				echo '<div><input type="text" name="email_admin[]" value="'.$v.'"></div>';
			}
		}
		else
		{
			echo '<div><input type="text" name="email_admin[]" value=""></div>';
		}
		echo '<a href="javascript:void(0)" class="email_admin_plus" title="'.$this->diafan->_('Добавить').'"><i class="fa fa-plus-square"></i> '.$this->diafan->_('Добавить').'</a>
			</div>
		</div>';
	}

	/**
	 * Редактирование поля "Шаблон страницы для разных ситуаций"
	 * @return void
	 */
	public function edit_config_variable_themes()
	{
		$themes = $this->diafan->get_themes();
		$views = $this->diafan->get_views($this->diafan->_admin->module);

		echo '<div class="unit" id="theme_list">
			<div class="infofield">
				'.$this->diafan->variable_name("theme_list").$this->diafan->help("theme_list").'
			</div>
			<select name="theme_list" style="width:250px">
				<option value="">'.(! empty($themes['site.php']) ? $themes['site.php'] : 'site.php').'</option>';
		foreach ($themes as $key => $value)
		{
			if ($key == 'site.php')
				continue;
			echo '<option value="'.$key.'"'.( $this->diafan->values("theme_list") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
			</select>
			<select name="view_list" style="width:250px">
				<option value="">'.(! empty($views['list']) ? $views['list'] : $this->diafan->_admin->module.'.view.list.php').'</option>';
		foreach ($views as $key => $value)
		{
			if ($key == 'list')
				continue;

			echo '<option value="'.$key.'"'.( $this->diafan->values("view_list") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
			</select>
			<select name="view_list_rows" style="width:250px">
				<option value="">'.(! empty($views['rows']) ? $views['rows'] : $this->diafan->_admin->module.'.view.rows.php').'</option>';
		foreach ($views as $key => $value)
		{
			if ($key == 'rows')
				continue;

			echo '<option value="'.$key.'"'.( $this->diafan->values("view_list_rows") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
			</select>
		</div>

		<div class="unit" id="theme_first_page">
			<div class="infofield">
				'.$this->diafan->variable_name("theme_first_page").$this->diafan->help("theme_first_page").'
			</div>
			<select name="theme_first_page" style="width:250px">
				<option value="">'.(! empty($themes['site.php']) ? $themes['site.php'] : 'site.php').'</option>';
		foreach ($themes as $key => $value)
		{
			if ($key == 'site.php')
				continue;
			echo '<option value="'.$key.'"'.( $this->diafan->values("theme_first_page") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
			</select>
			<select name="view_first_page" style="width:250px">
				<option value="">'.(! empty($views['first_page']) ? $views['first_page'] : $this->diafan->_admin->module.'.view.first_page.php').'</option>';
		foreach ($views as $key => $value)
		{
			if ($key == 'first_page')
			{
				continue;
			}
			echo '<option value="'.$key.'"'.( $this->diafan->values("view_first_page") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
			</select>
			<select name="view_first_page_rows" style="width:250px">
				<option value="">'.(! empty($views['first_page']) ? $views['first_page'] : $this->diafan->_admin->module.'.view.first_page.php').'</option>';
		foreach ($views as $key => $value)
		{
			if ($key == 'first_page')
			{
				continue;
			}
			echo '<option value="'.$key.'"'.( $this->diafan->values("view_first_page_rows") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
			</select>
		</div>

		<div class="unit" id="theme_id">
			<div class="infofield">
				'.$this->diafan->variable_name("theme_id").$this->diafan->help("theme_id").'
			</div>
			<select name="theme_id" style="width:250px">
				<option value="">'.(! empty($themes['site.php']) ? $themes['site.php'] : 'site.php').'</option>';
		foreach ($themes as $key => $value)
		{
			if ($key == 'site.php')
				continue;
			echo '<option value="'.$key.'"'.( $this->diafan->values("theme_id") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
			</select>
			<select name="view_id" style="width:250px">
				<option value="">'.(! empty($views['id']) ? $views['id'] : $this->diafan->_admin->module.'.view.id.php').'</option>';
		foreach ($views as $key => $value)
		{
			if ($key == 'id')
			{
				continue;
			}
			echo '<option value="'.$key.'"'.( $this->diafan->values("view_id") == $key ? ' selected' : '' ).'>'.$value.'</option>';
		}
		echo '
			</select>
		</div>';
	}
}

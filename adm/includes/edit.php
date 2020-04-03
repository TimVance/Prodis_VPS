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
 * Edit_admin
 *
 * Редактирование элемента
 */
class Edit_admin extends Theme_admin
{
	/**
	 * @var Edit_functions_admin функции редактирования полей
	 */
	public $_functions;

	/**
	 * @var array значения полей
	 */
	public $values = array ();

	/**
	 * @var integer счетчик
	 */
	public $k = 0;

	/**
	 * @var string название текущего поля
	 */
	public $key;

	/**
	 * @var mixed значение текущего поля
	 */
	public $value;

	/**
	 * @var string тип текущего поля
	 */
	public $type;

	/**
	 * @var array названия табов
	 */
	public $tabs_name;

	/**
	 * Вызывает функции редактирования полей
	 *
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		if(! $this->_functions)
		{
			Custom::inc("adm/includes/edit_functions.php");
			$this->_functions = new Edit_functions_admin($this->diafan);
		}
		if (is_callable(array(&$this->_functions, $name)))
		{
			return call_user_func_array(array(&$this->_functions, $name), $arguments);
		}
		else
		{
			return 'fail_function';
		}
	}

	/**
	 * Генерирует форму редактирования/добавления элемента
	 *
	 * @return void
	 */
	public function edit()
	{
		//проверка прав на просмотр
		if (! $this->diafan->_users->roles('init', $this->diafan->_admin->rewrite))
		{
			Custom::inc('includes/404.php');
		}
		if($this->diafan->_route->addnew)
		{
			$this->diafan->is_new = true;
			$this->diafan->id = 0;
		}
		else
		{
			$this->diafan->id = $this->diafan->_route->edit;
		}

		$this->prepare_values();

		// Если отмечена галочка "Видеть только свои материалы", то редактирование чужих материалов запрещено
		if($this->diafan->is_variable("admin_id")
		   && $this->diafan->values("admin_id")
		   && $this->diafan->values("admin_id") != $this->diafan->_users->id
		   && DB::query_result("SELECT only_self FROM {users_role} WHERE id=%d LIMIT 1", $this->diafan->_users->role_id))
		{
			Custom::inc('includes/404.php');
		}

		if($this->diafan->config('config'))
		{
			$h1 = 'Настройки';
		}
		elseif($this->diafan->is_new)
		{
			$h1 = 'Добавить новый';
		}
		else
		{
			$h1 = 'Редактировать';
		}
		echo '<div class="head-box">
			<span class="head-box__unit">'.$this->diafan->_($h1).'</span>
		</div>';

		echo $this->diafan->get_filter();

		echo '<form METHOD="POST" action="'.URL.$this->diafan->get_nav.'" enctype="multipart/form-data" id="save">
		<input type="hidden" name="check_hash_user" value="'.$this->diafan->_users->get_hash().'">
		<input type="hidden" name="id" value="'.(! $this->diafan->is_new ? $this->diafan->id : '' ).'">';
		if($this->diafan->is_new)
		{
			echo '<input type="hidden" name="is_new" value="true">';
		}
		if($this->diafan->config('element_site'))
		{
			echo '<input type="hidden" name="site_id" value="'.$this->diafan->_route->site.'">';
		}
		echo '<input type="hidden" name="action" value="save">';

		if ($this->diafan->config('tab_card'))
		{
			echo '<div id="tabs" index="'.$this->diafan->_admin->rewrite.'"><ul>';
			$i = 1;
			foreach ($this->diafan->variables as $title => $variable_table)
			{
				echo '<li><a href="#tabs-'.( $i++ ).'">';
				if(! empty($this->diafan->tabs_name[$title]))
				{
					echo $this->diafan->_($this->diafan->tabs_name[$title]);
				}
				else
				{
					echo $title;
				}
				echo '</a></li>';
			}
			echo '</ul>';
			$i = 1;
			foreach ($this->diafan->variables as $title => $variable_table)
			{
				echo '<div id="tabs-'.( $i++ ).'">';
				$this->show_table($variable_table);
				echo '</div>';
			}

			echo '</div>';
		}
		else
		{
			foreach ($this->diafan->variables as $title => $variable_table)
			{
				$h2 = '';

				if(! empty($variable_table))
				{
					if($title == 'other_rows')
					{
						$h2 = '<i class="fa fa-close ctr-close"></i> '.$this->diafan->_('Дополнительные параметры');
						echo '<div class="content__right content__right_supp">';
					}
					else
					{
						echo '<div class="content__left content__left_full">';
					}
					if($title == 'main' && isset($this->diafan->variables['other_rows']))
					{
						$h2 = $this->diafan->_('Основная информация');
					}
					if($h2)
					{
						echo '<h2>'.$h2;
						if($title == 'main' && isset($this->diafan->variables['other_rows']))
						{
							echo '<span class="btn btn_blue btn_small btn_supp">
								<i class="fa fa-sliders"></i>
								'.$this->diafan->_('Дополнительные параметры').'
							</span>';
						}
						echo '</h2>';
					}
					$this->diafan->show_table($variable_table);
					echo '</div>';
				}
			}
		}

		echo '<div class="nav-box-wrap">
			<div class="nav-box nav-box_float nav-box_compress">';

		if($this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			if(! $this->diafan->config("only_edit"))
			{
				echo '<input name="redirect_edit" id="input_redirect_edit" type="checkbox" value="1"'.(! empty($_SESSION["redirect_edit"]) ? ' checked' : (! empty($_SESSION["redirect_add"]) ? ' disabled' : '')).'> <label for="input_redirect_edit">'.$this->diafan->_('Продолжить редактирование').'</label>';
				if(method_exists($this->diafan->_frame, 'show_add'))
				{
					echo '
					<input name="redirect_add" id="input_redirect_add" type="checkbox" value="1"'.(! empty($_SESSION["redirect_add"]) ? ' checked' : (! empty($_SESSION["redirect_edit"]) ? ' disabled' : '')).'>  <label for="input_redirect_add">'.$this->diafan->_('Добавить еще').'</label>';
				}
			}
		}
		if($this->diafan->_users->roles('edit', $this->diafan->_admin->rewrite))
		{
			echo '<button class="btn btn_blue btn_small btn_save">'.$this->diafan->_('Сохранить').'</button>';
		}

		if (! $this->diafan->is_new && $this->diafan->variable_list('actions', 'view'))
		{
			$link = $this->diafan->_route->link($this->diafan->values("site_id"), $this->diafan->id, $this->diafan->_admin->module, $this->diafan->element_type());

			if(defined('MOBILE_PATH_HREF') && MOBILE_PATH_HREF)
			{
				echo '<a class="view_item" href="'.MOBILE_PATH_HREF.'/'._SHORTNAME.$link.'" target="_blank">
						<i class="fa fa-mobile"></i>
						<span>'.$this->diafan->_('Мобильная версия').'</span>
					</a>';
			}
			echo '<a class="view_item" href="'.BASE_PATH._SHORTNAME.$link.'" target="_blank">
					<i class="fa fa-laptop"></i>
					<span>'.$this->diafan->_('Посмотреть на сайте').'</span>
				</a>';
		}
				echo '<i class="fa fa-compress compress_nav" title="'.$this->diafan->_('Развернуть / Свернуть').'"></i>
			</div>
		</div>
		</form>
		<div class="hide ipopup" id="ipopup"></div>';
	}

	/**
	 * Фильтр вывода
	 *
	 * @return string
	 */
	public function get_filter()
	{
		if(! $this->diafan->config('config') || ! $this->diafan->config('element_site'))
		{
			return;
		}
		$this->diafan->sites = DB::query_fetch_all("SELECT id, [name], parent_id FROM {site} WHERE trash='0' AND module_name='%s' ORDER BY sort ASC", $this->diafan->_admin->module);
		if(count($this->diafan->sites) == 1 && ! DB::query_result("SELECT id FROM {config} WHERE module_name='%s' AND site_id=%d", $this->diafan->_admin->module, $this->diafan->sites[0]["id"]))
		{
			return;
		}
		$text = '
			<p><select rel="'.$this->diafan->get_admin_url('page', 'site').'" class="redirect" name="site">
			<option value="">'.$this->diafan->_('Все').'</option>';
			foreach($this->diafan->sites as $row)
			{
				$text .= '<option value="'.$row["id"].'"'.($row["id"] == $this->diafan->_route->site ? ' selected' : '').'>'.$row["name"].'</option>';
			}
			$text .= '</select>
			</p>';
		return $text;
	}

	/**
	 * Получает значения полей для формы (альтернативный метод)
	 *
	 * @return array
	 */
	public function get_values()
	{
		return array ();
	}

	/**
	 * Выводит таблицу с полями формы редактирования
	 *
	 * @param array $variable_table поля формы
	 * @return void
	 */
	public function show_table($variable_table)
	{
		foreach ($variable_table as $this->diafan->key => $row)
		{
			if(is_array($row))
			{
				$this->diafan->type = $row["type"];
			}
			else
			{
				$this->diafan->type = $row;
			}
			$this->k++;
			$key = $this->diafan->key.(! $this->diafan->config("config") && $this->diafan->variable_multilang($this->diafan->key) ? _LANG : '' );

			$this->diafan->value = $this->diafan->values($key);
			if($this->diafan->value === false)
			{
				$this->diafan->value = '';
			}

			$func = 'edit'.( $this->diafan->config("config") ? '_config' : '' ).'_variable_'.str_replace('-', '_', $this->diafan->key);
			if (call_user_func_array (array(&$this->diafan, $func), array()) === 'fail_function')
			{
				$this->diafan->show_table_tr(
						$this->diafan->type,
						$this->diafan->key,
						$this->diafan->value,
						$this->diafan->variable_name(),
						$this->diafan->help(),
						$this->diafan->variable_disabled(),
						$this->diafan->variable('', 'maxlength'),
						$this->diafan->variable('', 'select'),
						$this->diafan->variable('', 'select_db'),
						$this->diafan->variable('', 'depend'),
						$this->diafan->variable('', 'attr')
					);
			}
			else
			{
				$path = 'adm/js/edit/admin.edit.'.($this->diafan->config("config") ? 'config.' : '').str_replace('-', '_', $this->diafan->key).'.js';
				if(Custom::exists($path))
				{
					$this->diafan->_admin->js_view[] = Custom::path($path);
				}
			}
		}
	}

	/**
	 * Выводит одну строку формы редактирования
	 *
	 * @param string $type тип поля
	 * @param string $key название поля
	 * @param string $value значение поля
	 * @param string $name описание поля
	 * @param string $help часть кода, выводящая подсказку к полю
	 * @param boolean $disabled поле не редактируется
	 * @param string $maxlength максимальное количество символов
	 * @param array $select список значений
	 * @param array $select_db настройки для получения списка из базы данных
	 * @param string $depend поле/поля, от которых зависит вывод поля
	 * @param string $attr атрибуты поля
	 * @return void
	 */
	public function show_table_tr($type, $key, $value, $name, $help, $disabled, $maxlength, $select, $select_db, $depend, $attr)
	{
		$attr = $attr ?: '';
		$class = '';
		if($depend)
		{
			$attr = ' depend="'.$depend.'"';
			$class = "depend_field";
		}
		switch($type)
		{
			case 'module':
				if (in_array($key, $this->diafan->installed_modules)
					&& Custom::exists('modules/'.$key.'/admin/'.$key.'.admin.inc.php'))
				{
					Custom::inc('modules/'.$key.'/admin/'.$key.'.admin.inc.php');
					$func = 'edit'.( $this->diafan->config("config") ? '_config' : '' );
					$class = ucfirst($key).'_admin_inc';
					if (method_exists($class, $func))
					{
						$module_class = new $class($this->diafan);
						call_user_func_array (array(&$module_class, $func), array());
					}
					$path = 'modules/'.$key.'/admin/js/'.$key.'.admin.inc'.($this->diafan->config("config") ? '.config' : '').'.js';
					if(Custom::exists($path))
					{
						$this->diafan->_admin->js_view[] = Custom::path($path);
					}
				}
				break;

			case 'title':
				$this->diafan->show_table_tr_title($key, $name, $help, $attr, $class);
				break;

			case 'password':
				$this->diafan->show_table_tr_password($key, $name, $value, $help, $disabled, $attr, $class);
				break;

			case 'text':
				$this->diafan->show_table_tr_text($key, $name, $value, $help, $disabled, $attr, $class, $maxlength);
				break;

			case 'email':
				$this->diafan->show_table_tr_email($key, $name, $value, $help, $disabled, $attr, $class);
				break;

			case 'phone':
				$this->diafan->show_table_tr_phone($key, $name, $value, $help, $disabled, $attr, $class);
				break;

			case 'date':
				$this->diafan->show_table_tr_date($key, $name, $value, $help, $disabled, $attr, $class);
				break;

			case 'datetime':
				$this->diafan->show_table_tr_datetime($key, $name, $value, $help, $disabled, $attr, $class);
				break;

			case 'numtext':
				$this->diafan->show_table_tr_numtext($key, $name, $value, $help, $disabled, $attr, $class);
				break;

			case 'floattext':
				$this->diafan->show_table_tr_floattext($key, $name, $value, $help, $disabled, $attr, $class);
				break;

			case 'textarea':
				$this->diafan->show_table_tr_textarea($key, $name, $value, $help, $disabled, $attr, $class, $maxlength);
				break;

			case 'checkbox':
				$this->diafan->show_table_tr_checkbox($key, $name, $value, $help, $disabled, $attr, $class);
				break;

			case 'select':
				if(! $select && $select_db)
				{
					$select = $this->diafan->get_select_from_db($select_db);
				}
				$this->diafan->show_table_tr_select($key, $name, $value, $help, $disabled, $select, $attr, $class);
				break;

			case 'editor':
				$this->diafan->show_table_tr_editor($key, $name, $value, $help, $attr, $class);
				break;

			case 'hr':
				$this->diafan->show_table_tr_hr($key, $name, $attr, $class);
				break;
		}
		echo "\n";
	}

	/**
	 * Выводит одну строку формы редактирования с типом "Заголовок"
	 *
	 * @param string $key название поля
	 * @param string $name описание поля
	 * @param string $help часть кода, выводящая подсказку к полю
	 * @param string $attr атрибуты строки
	 * @param string $class CSS-класс
	 * @return void
	 */
	public function show_table_tr_title($key, $name, $help, $attr = '', $class = '')
	{
		echo '
		<h2 id="'.$key.'"'.($class ? ' class="'.$class.'"' : '').$attr.'>
			'.$name.$help.'
		</h2>';
	}

	/**
	 * Выводит одну строку формы редактирования с типом "Пароль"
	 *
	 * @param string $key название поля
	 * @param string $name описание поля
	 * @param string $value значение поля
	 * @param string $help часть кода, выводящая подсказку к полю
	 * @param boolean $disabled поле не редактируется
	 * @param string $attr атрибуты строки
	 * @param string $class CSS-класс
	 * @return void
	 */
	public function show_table_tr_password($key, $name, $value, $help, $disabled = false, $attr = '', $class = '')
	{
		echo '
		<div class="unit'.($class ? ' '.$class : '').'" id="'.$key.'"'.$attr.'>
			<div class="infofield">'.$name.$help.'</div>
			<input type="password" name="'.$key.'" value="'.$value.'"'.($disabled ? ' disabled' : '').'>
		</div>';
	}

	/**
	 * Выводит одну строку формы редактирования с типом "Текст"
	 *
	 * @param string $key название поля
	 * @param string $name описание поля
	 * @param string $value значение поля
	 * @param string $help часть кода, выводящая подсказку к полю
	 * @param boolean $disabled поле не редактируется
	 * @param string $attr атрибуты строки
	 * @param string $class CSS-класс
	 * @param string $maxlength максимальное количество символов
	 * @return void
	 */
	public function show_table_tr_text($key, $name, $value, $help, $disabled = false, $attr = '', $class = '',  $maxlength = 0)
	{
		echo '
		<div class="unit'.($class ? ' '.$class : '').'" id="'.$key.'"'.$attr.'>
			<div class="infofield">'.$name.$help.'</div>
			<input type="text" name="'.$key.'" value="'.str_replace('"', '&quot;', $value).'"'.($disabled ? ' disabled' : '').($maxlength ? ' maxlength="'.$maxlength.'"' : '').'>
		</div>';
	}

	/**
	 * Выводит одну строку формы редактирования с типом "E-mail"
	 *
	 * @param string $key название поля
	 * @param string $name описание поля
	 * @param string $value значение поля
	 * @param string $help часть кода, выводящая подсказку к полю
	 * @param boolean $disabled поле не редактируется
	 * @param string $attr атрибуты строки
	 * @param string $class CSS-класс
	 * @return void
	 */
	public function show_table_tr_email($key, $name, $value, $help, $disabled = false, $attr = '', $class = '')
	{
		echo '
		<div class="unit'.($class ? ' '.$class : '').'" id="'.$key.'"'.$attr.'>
			<div class="infofield">'.$name.$help.'</div>
			<input type="email" name="'.$key.'" value="'.( $value ? str_replace('"', '&quot;', $value) : '' ).'"'.($disabled ? ' disabled' : '').'>
		</div>';
	}

	/**
	 * Выводит одну строку формы редактирования с типом "Телефон"
	 *
	 * @param string $key название поля
	 * @param string $name описание поля
	 * @param string $value значение поля
	 * @param string $help часть кода, выводящая подсказку к полю
	 * @param boolean $disabled поле не редактируется
	 * @param string $attr атрибуты строки
	 * @param string $class CSS-класс
	 * @return void
	 */
	public function show_table_tr_phone($key, $name, $value, $help, $disabled = false, $attr = '', $class = '')
	{
		echo '
		<div class="unit'.($class ? ' '.$class : '').'" id="'.$key.'"'.$attr.'>
			<div class="infofield">'.$name.$help.'</div>
			<input type="tel" class="infofield_tel" name="'.$key.'" value="'.( $value ? str_replace('"', '&quot;', $value) : '' ).'"'.($disabled ? ' disabled' : '').'>
			<a href="tel:'.( $value ? str_replace('"', '&quot;', $value) : '' ).'" class="infofield_phone"><i class="fa fa-phone-square"></i></a>
		</div>';
	}

	/**
	 * Выводит одну строку формы редактирования с типом "Дата"
	 *
	 * @param string $key название поля
	 * @param string $name описание поля
	 * @param string $value значение поля
	 * @param string $help часть кода, выводящая подсказку к полю
	 * @param boolean $disabled поле не редактируется
	 * @param string $attr атрибуты строки
	 * @param string $class CSS-класс
	 * @return void
	 */
	public function show_table_tr_date($key, $name, $value, $help, $disabled = false, $attr = '', $class = '')
	{
		if($value)
		{
			$value = date("d.m.Y", $value);
		}
		elseif($this->diafan->is_new)
		{
			$value = date("d.m.Y");
		}
		else
		{
			$value = '';
		}
		echo '
		<div class="unit'.($class ? ' '.$class : '').'" id="'.$key.'"'.$attr.'>
			<div class="infofield">'.$name.$help.'</div>
			<input type="text" id="filed_'.$key.'" name="'.$key.'" value="'.$value.'" class="timecalendar" showTime="false"'.($disabled ? ' disabled' : '').'>
		</div>';
	}

	/**
	 * Выводит одну строку формы редактирования с типом "Дата и время"
	 *
	 * @param string $key название поля
	 * @param string $name описание поля
	 * @param string $value значение поля
	 * @param string $help часть кода, выводящая подсказку к полю
	 * @param boolean $disabled поле не редактируется
	 * @param string $attr атрибуты строки
	 * @param string $class CSS-класс
	 * @return void
	 */
	public function show_table_tr_datetime($key, $name, $value, $help, $disabled = false, $attr = '', $class = '')
	{
		if($value)
		{
			$value = date("d.m.Y H:i", $value);
		}
		elseif($this->diafan->is_new)
		{
			$value = date("d.m.Y H:i");
		}
		else
		{
			$value = '';
		}
		echo '
		<div class="unit'.($class ? ' '.$class : '').'" id="'.$key.'"'.$attr.'>
			<div class="infofield">'.$name.$help.'</div>
			<input type="text" id="filed_'.$key.'" name="'.$key.'" value="'.$value.'" class="timecalendar" showTime="true"'.($disabled ? ' disabled' : '').'>
		</div>';
	}

	/**
	 * Выводит одну строку формы редактирования с типом "Число"
	 *
	 * @param string $key название поля
	 * @param string $name описание поля
	 * @param string $value значение поля
	 * @param string $help часть кода, выводящая подсказку к полю
	 * @param boolean $disabled поле не редактируется
	 * @param string $attr атрибуты строки
	 * @param string $class CSS-класс
	 * @return void
	 */
	public function show_table_tr_numtext($key, $name, $value, $help, $disabled = false, $attr = '', $class = '')
	{
		echo '
		<div class="unit'.($class ? ' '.$class : '').'" id="'.$key.'"'.$attr.'>
			<div class="infofield">'.$name.$help.'</div>
			<input type="text" class="number" name="'.$key.'" value="'.$value.'"'.($disabled ? ' disabled' : '').'>
		</div>';
	}

	/**
	 * Выводит одну строку формы редактирования с типом "Число с плавающей точкой"
	 *
	 * @param string $key название поля
	 * @param string $name описание поля
	 * @param string $value значение поля
	 * @param string $help часть кода, выводящая подсказку к полю
	 * @param boolean $disabled поле не редактируется
	 * @param string $attr атрибуты строки
	 * @param string $class CSS-класс
	 * @return void
	 */
	public function show_table_tr_floattext($key, $name, $value, $help, $disabled = false, $attr = '', $class = '')
	{
		$value = floatval($value);
		if(($value * 10000) % 10)
		{
			$num_decimal_places = 4;
		}
		else
		if(($value * 1000) % 10)
		{
			$num_decimal_places = 3;
		}
		else
		if(($value * 100) % 10)
		{
			$num_decimal_places = 2;
		}
		else
		if(($value * 10) % 10)
		{
			$num_decimal_places = 1;
		}
		else
		{
			$num_decimal_places = 0;
		}
		echo '
		<div class="unit'.($class ? ' '.$class : '').'" id="'.$key.'"'.$attr.'>
			<div class="infofield">'.$name.$help.'</div>
			<input type="text" class="number" name="'.$key.'" value="'.( $value ? number_format($value, $num_decimal_places, ',', '') : '' ).'" '.($disabled ? ' disabled' : '').'>
		</div>';
	}

	/**
	 * Выводит одну строку формы редактирования с типом "Текстова область"
	 *
	 * @param string $key название поля
	 * @param string $name описание поля
	 * @param string $value значение поля
	 * @param string $help часть кода, выводящая подсказку к полю
	 * @param boolean $disabled поле не редактируется
	 * @param string $attr атрибуты строки
	 * @param string $class CSS-класс
	 * @param string $maxlength максимальное количество символов
	 * @return void
	 */
	public function show_table_tr_textarea($key, $name, $value, $help, $disabled = false, $attr = '', $class = '',  $maxlength = 0)
	{
		$height = $this->diafan->variable($key, 'height');
		echo '
		<div class="unit'.($class ? ' '.$class : '').'" id="'.$key.'"'.$attr.'>
			<div class="infofield">'.$name.$help.'</div>
			<textarea name="'.$key.'" cols="49" rows="5"'.($disabled ? ' disabled' : '').($maxlength ? ' maxlength="'.$maxlength.'"' : '').($height ? ' style="height:'.$height.'px;"' : '').'>'.( $value ? str_replace(array ('<', '>', '"'), array('&lt;', '&gt;', '&quot;'), $value) : '' ).'</textarea>
		</div>';
	}

	/**
	 * Выводит одну строку формы редактирования с типом "Галка"
	 *
	 * @param string $key название поля
	 * @param string $name описание поля
	 * @param string $value значение поля
	 * @param string $help часть кода, выводящая подсказку к полю
	 * @param boolean $disabled поле не редактируется
	 * @param string $attr атрибуты строки
	 * @param string $class CSS-класс
	 * @return void
	 */
	public function show_table_tr_checkbox($key, $name, $value, $help, $disabled = false, $attr = '', $class = '')
	{
		echo '
		<div class="unit'.($class ? ' '.$class : '').'" id="'.$key.'"'.$attr.'>
			<input type="checkbox" id="input_'.$key.'" name="'.$key.'" value="1"'.( $value ? " checked" : '' ).($disabled ? ' disabled' : '').'>
			<label for="input_'.$key.'"><b>'.$name.$help.'</b></label>
		</div>';
	}

	/**
	 * Выводит одну строку формы редактирования с типом "Переключатель"
	 *
	 * @param string $key название поля
	 * @param string $name описание поля
	 * @param string $value значение поля
	 * @param string $help часть кода, выводящая подсказку к полю
	 * @param boolean $disabled поле не редактируется
	 * @param array $options значения списка
	 * @param string $attr атрибуты строки
	 * @param string $class CSS-класс
	 * @return void
	 */
	public function show_table_tr_radio($key, $name, $value, $help, $disabled = false, $options = array(), $attr = '', $class = '')
	{
		if (! $options)
		{
			return;
		}
		echo '
		<div class="unit'.($class ? ' '.$class : '').'" id="'.$key.'"'.$attr.'>
			<div class="infofield">'.$name.$help.'</div>';
			foreach ($options as $k => $select)
			{
				if(is_array($select))
				{
					$k = $select["id"];
					$select = $select["name"];
				}
				echo '<input name="'.$key.'"'.($disabled ? ' disabled' : '').' type="radio" value="'.$k.'"'.($value == $k ? ' checked' : '').' id="input_'.$key.'_'.$k.'">
				<label for="input_'.$key.'_'.$k.'">'.$this->diafan->_($select).'</label>';
			}
			echo '
		</div>';
	}

	/**
	 * Выводит одну строку формы редактирования с типом "Список из массива"
	 *
	 * @param string $key название поля
	 * @param string $name описание поля
	 * @param string $value значение поля
	 * @param string $help часть кода, выводящая подсказку к полю
	 * @param boolean $disabled поле не редактируется
	 * @param array $options значения списка
	 * @param string $attr атрибуты строки
	 * @param string $class CSS-класс
	 * @return void
	 */
	public function show_table_tr_select($key, $name, $value, $help, $disabled = false, $options = array(), $attr = '', $class = '')
	{
		if (! $options)
		{
			return;
		}
		echo '
		<div class="unit'.($class ? ' '.$class : '').'" id="'.$key.'"'.$attr.'>
			<div class="infofield">'.$name.$help.'</div>
			<select name="'.$key.'"'.($disabled ? ' disabled' : '').'>';
			foreach ($options as $k => $select)
			{
				if(is_array($select))
				{
					$k = $select["id"];
					$select = $select["name"];
				}
				echo '<option value="'.$k.'"'.($value == $k ? ' selected' : '').'>'.$this->diafan->_($select).'</option>';
			}
			echo '</select>
		</div>';
	}

	/**
	 * legacy
	 */
	public function show_table_tr_select_arr($key, $name, $value, $help, $disabled = false, $options = array(), $attr = '', $class = '')
	{
		$this->show_table_tr_select($key, $name, $value, $help, $disabled, $options, $attr, $class);
	}

	/**
	 * Выводит одну строку формы редактирования с типом "Список с выбором нескольких значений"
	 *
	 * @param string $key название поля
	 * @param string $name описание поля
	 * @param string $value значение поля
	 * @param string $help часть кода, выводящая подсказку к полю
	 * @param boolean $disabled поле не редактируется
	 * @param array $options значения списка
	 * @param string $attr атрибуты строки
	 * @param string $class CSS-класс
	 * @return void
	 */
	public function show_table_tr_multiple($key, $name, $values, $help, $disabled = false, $options = array(), $attr = '', $class = '')
	{
		foreach ($values as &$val)
		{
			if(! $val)
			{
				unset($val);
			}
		}
		echo '
		<div class="unit'.($class ? ' '.$class : '').'" id="'.$key.'"'.$attr.'>
			<div class="infofield">'.$name.$help.'</div>
			<select name="'.$key.'[]" size="11" multiple="multiple"'.($disabled ? ' disabled' : '').'>
			<option value="all"'.(empty($values) ? ' selected' : '').'>'.$this->diafan->_('Нет').'</option>';
			foreach ($options as $k => $select)
			{
				if(is_array($select))
				{
					$k = $select["id"];
					$select = $select["name"];
				}
				echo '<option value="'.$k.'"'.(in_array($k, $values) ? ' selected' : '').'>'.$this->diafan->_($select).'</option>';
			}
			echo '</select>
		</div>';
	}

	/**
	 * Выводит одну строку формы редактирования с типом "Редактор"
	 *
	 * @param string $key название поля
	 * @param string $name описание поля
	 * @param string $value значение поля
	 * @param string $help часть кода, выводящая подсказку к полю
	 * @param string $attr атрибуты строки
	 * @param string $class CSS-класс
	 * @return void
	 */
	public function show_table_tr_editor($key, $name, $value, $help, $attr = '', $class = '')
	{
		$value = $this->diafan->_route->replace_id_to_link($value);
		$height = $this->diafan->variable($key, 'height');
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
			$hide_htmleditor = in_array($key, explode(",", $this->diafan->configmodules("hide_".$this->diafan->table."_".$this->diafan->id, "htmleditor")));
		}
		echo '
		<div class="unit'.($class ? ' '.$class : '').'" id="'.$key.'"'.$attr.'>';
			if($name)
			{
				echo '<div class="infofield">'.$name.$help.'</div>';
			}
			if($this->diafan->_users->htmleditor)
			{
				echo '<input type="checkbox" class="htmleditor_check" name="'.$key.'_htmleditor" id="input_'.$key.'_htmleditor" value="1"'.($hide_htmleditor ? ' checked' : '').' rel="htmleditor_'.$key.'"> <label for="input_'.$key.'_htmleditor">'.$this->diafan->_('HTML-код').'</label>';
			}
			echo '<input type="checkbox" name="'.$key.'_typograf" id="input_'.$key.'_typograf" value="1"> <label for="input_'.$key.'_typograf">'.$this->diafan->_('Применить %sтипограф%s', '<a href="http'.(IS_HTTPS ? "s" : '').'://www.artlebedev.ru/tools/typograf/about/" target="_blank">', '</a>')
			.'</label>
			<div class="textfield">';
			echo '<textarea name="'.$key.'" id="htmleditor_'.$key.'" style="width:100%; height:'.$height.'px;"';
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
			</div>
		</div>';
	}

	/**
	 * Выводит одну строку формы редактирования с типом "Горизонтальная линия"
	 *
	 * @param string $key название поля
	 * @param string $name описание поля
	 * @param string $attr атрибуты строки
	 * @param string $class CSS-класс
	 * @return void
	 */
	public function show_table_tr_hr($key, $name, $attr = '', $class = '')
	{
		echo '<h2 id="'.$key.'"'.$attr.($class ? ' class="'.$class.'"' : '').'></h2>';
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
			$key = $this->diafan->key;
		}
		if(! $this->diafan->is_variable($key))
		{
			$help = $key;
			$key = rand(0, 3333);
		}
		elseif (! $help = $this->diafan->variable($key, 'help'))
		{
			return '';
		}

		return ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
	}

	/**
	 * Получает значение поля
	 * @param string $field название поля
	 * @param mixed $default значение по умолчанию
	 * @param boolean $save записать значение по умолчанию
	 * @return mixed
	 */
	public function values($field, $default = false, $save = false)
	{
		if(! isset($this->cache["oldrow"]))
		{
			$values = $this->diafan->get_values();

			if ($this->diafan->config("config"))
			{
				foreach ($this->diafan->variables as $title => $variable_table)
				{
					foreach ($variable_table as $k => $v)
					{
						if ( empty($values[$k]))
						{
							$values[$k] = $this->diafan->configmodules($k);
						}
					}
				}
			}
			elseif($this->diafan->is_new)
			{
				foreach ($this->diafan->variables as $title => $variable_table)
				{
					foreach ($variable_table as $k => $v)
					{
						if (! empty($this->diafan->get_nav_params['filter_'.$k]) && $this->diafan->variable_filter($k) != 'text')
						{
							$values[$k.(! empty($v["multilang"]) ? _LANG : '')] = $this->diafan->get_nav_params['filter_'.$k];
						}
						elseif(! empty($v["default"]))
						{
							$values[$k._LANG] = $v["default"];
						}
					}
				}
			}
			elseif (! $values)
			{
				$values = DB::query_fetch_array("SELECT * FROM {".$this->diafan->table."} WHERE id=%d"
					.($this->diafan->variable_list('actions', 'trash') ? " AND trash='0'" : '' )." LIMIT 1",
					$this->diafan->id
				);
				if (empty($values))
				{
					ob_end_clean();
					Custom::inc('includes/404.php');
				}
			}
			$this->cache["oldrow"] = $values;
		}

		$field .= ($this->diafan->variable_multilang($field) && ! $this->diafan->config("config") ? _LANG : '');

		if(! isset($this->cache["oldrow"][$field]))
		{
			switch($field)
			{
				case 'parent_id':
					if ($this->diafan->is_new)
					{
						$this->cache["oldrow"]["parent_id"] = $this->diafan->_route->parent;
					}
					break;

				case 'cat_id':
					if ($this->diafan->is_new)
					{
						$this->cache["oldrow"]["cat_id"] = $this->diafan->_route->cat;
					}
					break;

				case 'site_id':
					if($this->diafan->table == 'site')
					{
						$this->cache["oldrow"]["site_id"] = $this->diafan->id;
					}
					else
					{
						if(empty($this->cache["oldrow"]["site_id"]))
						{
							$this->cache["oldrow"]["site_id"] = $this->diafan->_route->site;
						}
						if(empty($this->cache["oldrow"]["site_id"]))
						{
							$this->cache["oldrow"]["site_id"] = DB::query_result("SELECT id FROM {site} WHERE module_name='%s' AND trash='0'", $this->diafan->_admin->module);
						}
					}
					break;
			}
		}
		if(! isset($this->cache["oldrow"][$field]))
		{
			if(! $default)
			{
				$default = $this->diafan->variable($field, 'default');
			}
			if($default)
			{
				if($save)
				{
					$this->cache["oldrow"][$field] = $default;
				}
				else
				{
					return $default;
				}
			}
			elseif ($this->diafan->config("config"))
			{
				$this->cache["oldrow"][$field] = $this->diafan->configmodules($field);
			}
		}
		if(isset($this->cache["oldrow"][$field]))
		{
			return $this->cache["oldrow"][$field];
		}
		else
		{
			return false;
		}
		return $this->cache["site_id"];
	}
}

<?php
/**
 * Подключение модуля к административной части других модулей
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
 * Tags_admin_inc
 */
class Tags_admin_inc extends Diafan
{
	/**
	 * Редактирование поля "Теги"
	 * 
	 * @return void
	 */
	public function edit()
	{
		$element_type = $this->diafan->element_type();
		if (! $this->diafan->configmodules("tags".($element_type != 'element'? '_'.$element_type : '')))
		{
			return;
		}
		Custom::inc('modules/tags/admin/tags.admin.view.php');
		$tags_view = new Tags_admin_view($this->diafan);

		echo '<div class="unit tags" id="tags">
			<h2>'.$this->diafan->_("Теги").$this->diafan->help().'</h2>
			<div class="tags_container">'.$tags_view->show($this->diafan->is_new ? 0 : $this->diafan->id).'</div>
			<div>'.$this->diafan->_('Добавить теги').'</div>

			<textarea rows="5" name="tags"></textarea>
			<div class="unit__sinfo">'.$this->diafan->_('Несколько тегов через Enter').'</div>
			
			<span class="btn btn_blue btn_small btn_tags tags_upload">
				<i class="fa fa-plus-square"></i>
				'.$this->diafan->_('Добавить').'
			</span> '.$this->diafan->_('или').' 
			<a href="#" class="tags_cloud" element_id="'.($this->diafan->is_new ? 0 : $this->diafan->id).'">
				<i class="fa fa-tags"></i>
				'.$this->diafan->_('Выбрать из облака тегов').'
			</a>
			<div class="errors error_tags"></div>
			<h2></h2>
		</div>';
	}

	/**
	 * Редактирование поля "Подключить теги" для настроек модуля
	 * 
	 * @return void
	 */
	public function edit_config()
	{
		echo '
		<div class="unit" id="'.$this->diafan->key.'">
			<input type="checkbox" id="input_'.$this->diafan->key.'" name="'.$this->diafan->key.'" value="1"'.($this->diafan->value ? " checked" : '' ).'>
			<label for="input_'.$this->diafan->key.'"><b>'.$this->diafan->variable_name().$this->diafan->help().'</b></label>
		</div>';
	}

	/**
	 * Сохранение настроек конфигурации модулей
	 * 
	 * @return void
	 */
	public function save_config()
	{
		$this->diafan->set_query("tags='%d'");
		$this->diafan->set_value(! empty($_POST["tags"]) ? $_POST["tags"] : '');
	}

	/**
	 * Помечает теги на удаление или удаляет теги
	 * 
	 * @param array $element_ids номера элементов
	 * @param string $module_name название модуля
	 * @param string $element_type тип данных
	 * @return void
	 */
	public function delete($element_ids, $module_name, $element_type)
	{
		$this->diafan->del_or_trash_where("tags", "element_id IN (".implode(",", $element_ids).") AND module_name='".$module_name."' AND element_type='".$element_type."'");
		$this->diafan->diafan->_cache->delete("", "tags");
	}

	/**
	 * Сохраняет прикрепленные теги
	 * @return void
	 */
	public function save()
	{
		if (! $this->diafan->configmodules("tags"))
		{
			return;
		}

		$edit = false;

		if(! $this->diafan->config('category'))
		{
			$access = ! empty($_POST["access"]) ? 1 : 0;
			$old_access = $this->diafan->values("access", (empty($_POST["access"]) ? 1 : 0));
			if($access != $old_access)
			{
				$edit = true;
			}
		}
		$act = ! empty($_POST["act"]) ? 1 : 0;
		if(! $act && $this->diafan->values("act"))
		{
			$edit = true;
		}
		elseif($act && ! $this->diafan->values("act"))
		{
			$edit = true;
		}
		$set_update = '';
		if($this->diafan->is_variable('date_period'))
		{
			$this->diafan->get_date_period();
			if($this->diafan->values("date_start") != $this->diafan->unixdate($_POST['date_start']) || $this->diafan->values("date_finish") != $this->diafan->unixdate($_POST['date_finish']))
			{
				$edit = true;
				$set_update = ", date_start=".$this->diafan->unixdate($_POST['date_start']).", date_finish=".$this->diafan->unixdate($_POST['date_finish']);
			}
		}
		if($edit)
		{
			if ($this->diafan->config('category'))
			{
				$table = str_replace('_category', '', $this->diafan->table);
				$element_ids = DB::query_fetch_value("SELECT id FROM {".$this->diafan->table."} WHERE cat_id=%d", $this->diafan->id, "id");
				DB::query("UPDATE {tags} SET [act]='%d' WHERE module_name='%h' AND element_id IN (%h)", $act, $this->diafan->table, implode(',', $element_ids));
			}
			else
			{
				DB::query("UPDATE {tags} SET [act]='%d', access='%d'".$set_update." WHERE module_name='%h' AND element_id=%d", $act, $access, $this->diafan->table, $this->diafan->id);
			}
		}
	}

	/**
	 * Блокирует/разблокирует прикрепленные теги
	 * 
	 * @param string $table таблица
	 * @param array $element_ids номера элементов, к которым прикреплены теги
	 * @param integer $act блокировать/разблокировать
	 * @return void
	 */
	public function act($table, $element_ids, $act)
	{
		if (! $this->diafan->configmodules("tags"))
		{
			return;
		}
		if ($this->diafan->config('category'))
		{
			$table = str_replace('_category', '', $table);
			$element_ids = DB::query_fetch_value("SELECT id FROM {".$table."} WHERE cat_id IN (%h)", implode(',', $element_ids), "id");
			if($element_ids)
			{
				DB::query("UPDATE {tags} SET [act]='%d' WHERE module_name='%h' AND element_id IN (%h)", $act, $table, implode(',', $element_ids));
			}
		}
		else
		{
			DB::query("UPDATE {tags} SET [act]='%d' WHERE module_name='%h' AND element_id IN (%h)", $act, $table, implode(',', $element_ids));
		}
	}
}
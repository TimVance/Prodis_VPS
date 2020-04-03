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
 * Comments_admin_inc
 */
class Comments_admin_inc extends Diafan
{
	/**
	 * Редактирование поля "Комментарии"
	 * 
	 * @return void
	 */
	public function edit()
	{
		$element_type = $this->diafan->element_type();
		if ($this->diafan->is_new
			|| $element_type == 'element' && ! $this->diafan->configmodules("comments")
			|| $element_type != $element_type && ! $this->diafan->configmodules("comments_".$element_type)
		)
			return;

		echo '
		<div class="unit" id="comments">'
			.(DB::query_result("SELECT id FROM {comments} WHERE module_name='%h' AND element_id=%d AND element_type='%s' AND trash='0' LIMIT 1", $this->diafan->table, $this->diafan->id, $element_type)
			  ? '<a href="'.BASE_PATH_HREF.'comments/?rew='.$this->diafan->_admin->module.'/'.$element_type.'/'.$this->diafan->id.'" target="_blank">'.$this->diafan->_('Комментарии').'</a>'
			  : $this->diafan->_('Комментариев нет')
			).$this->diafan->help().'
		</div>';
		return;
	}

	/**
	 * Редактирование поля "Подключить комментарии" для настроек модуля
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

		if ($this->diafan->is_variable("cat"))
		{
			echo '
			<div class="unit depend_field" depend="cat" id="'.$this->diafan->key.'_cat">
				<input type="checkbox" id="input_'.$this->diafan->key.'_cat" name="'.$this->diafan->key.'_cat" value="1"'.($this->diafan->values($this->diafan->key.'_cat') ? " checked" : '' ).'>
				<label for="input_'.$this->diafan->key.'_cat"><b>'.$this->diafan->_("Показывать комментарии к категориям").'</b></label>
			</div>';
		}
	}

	/**
	 * Сохранение настроек конфигурации модулей
	 * 
	 * @return void
	 */
	public function save_config()
	{
		$fields = array('comments', 'comments_cat');

		foreach ($fields as $field)
		{
			$this->diafan->set_query($field."=%d");
			$this->diafan->set_value(! empty($_POST[$field]) ? $_POST[$field] : '');
		}
	}

	/**
	 * Помечает комментарии на удаление или удаляет комментарии
	 * 
	 * @param array $element_ids номера элементов
	 * @param string $module_name название модуля
	 * @param string $element_type тип данных
	 * @return void
	 */
	public function delete($element_ids, $module_name, $element_type)
	{
		$this->diafan->del_or_trash_where("comments", "element_id IN (".implode(",", $element_ids).") AND module_name='".$module_name."' AND element_type='".$element_type."'");
		$this->diafan->del_or_trash_where("comments_mail", "element_id IN (".implode(",", $element_ids).") AND module_name='".$module_name."' AND element_type='".$element_type."'");
		$this->diafan->diafan->_cache->delete("","comments");
	}
}

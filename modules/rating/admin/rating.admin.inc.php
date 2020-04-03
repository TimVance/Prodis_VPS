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
 * Rating_admin_inc
 */
class Rating_admin_inc extends Diafan
{
	/**
	 * Редактирование поля "Рейтинг"
	 * 
	 * @return void
	 */
	public function edit()
	{
		$element_type = $this->diafan->element_type();
		if ($this->diafan->is_new
			|| $element_type == 'element' && ! $this->diafan->configmodules("rating")
			|| $element_type != 'element' && ! $this->diafan->configmodules("rating_".$element_type)
		)
			return;

		$row = DB::query_fetch_array("SELECT id, rating, count_votes FROM {rating} WHERE element_id='%d' AND module_name='%s' AND element_type='%s' AND trash='0' LIMIT 1", $this->diafan->id, $this->diafan->_admin->module, $element_type);
		echo '<div class="unit" id="rating">'
			.($row
			 ? '
				<a href="'.BASE_PATH_HREF.'rating/edit'.$row["id"].'/">'.$this->diafan->_('Рейтинг').': '.$row["rating"]
				.' '.$this->diafan->_('голосов').': '.$row["count_votes"].'</a>'
			 : $this->diafan->_('Рейтинг').': '.$this->diafan->_('нет голосов'))
			.$this->diafan->help().'
		</div>';
	}

	/**
	 * Редактирование поля "Подключить рейтинг" для настроек модуля
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
				<label for="input_'.$this->diafan->key.'_cat"><b>'.$this->diafan->_("Подключить рейтинг к категориям").'</b></label>
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
		$this->diafan->set_query("rating='%d'");
		$this->diafan->set_value(! empty($_POST["rating"]) ? $_POST["rating"] : '');

		if ($this->diafan->is_variable("cat"))
		{
			$this->diafan->set_query("rating_cat='%d'");
			$this->diafan->set_value(! empty($_POST["rating_cat"]) ? $_POST["rating_cat"] : '');
		}
	}

	/**
	 * Помечает рейтинг элемена на удаление или удаляет рейтинг
	 * 
	 * @param array $element_ids номера элементов
	 * @param string $module_name название модуля
	 * @param string $element_type тип данных
	 * @return void
	 */
	public function delete($element_ids, $module_name, $element_type)
	{
		$this->diafan->del_or_trash_where("rating", "element_id IN (".implode(",", $element_ids).") AND module_name='".$module_name."' AND element_type='".$element_type."'");
		$this->diafan->_cache->delete("", "rating");
	}
}
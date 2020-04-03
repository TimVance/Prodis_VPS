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
 * Geomap_admin_inc
 */
class Geomap_admin_inc extends Diafan
{
	/**
	 * Редактирование поля "Геокарта"
	 * 
	 * @return void
	 */
	public function edit()
	{
		$element_type = $this->diafan->element_type();
		if (! $this->diafan->configmodules("geomap_".$element_type))
			return;

		$result["point"] = '';
		if(! $this->diafan->is_new)
		{
			$result["point"] = DB::query_result("SELECT point FROM {geomap} WHERE element_id=%d AND module_name='%s' AND element_type='%s' AND trash='0' LIMIT 1", $this->diafan->id, $this->diafan->_admin->module, $element_type);
		}
		$result["config"] = $this->diafan->configmodules("config", "geomap");
		if($result["config"])
		{
			$result["config"] = unserialize($result["config"]);
		}
		echo '<div class="unit" id="geomap">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>';
			
			$backend = $this->diafan->configmodules("backend", "geomap");
			if($backend)
			{
				include(Custom::path('modules/geomap/backend/'.$backend.'/geomap.'.$backend.'.view.add.php'));
			}
			echo '
		</div>';
	}

	/**
	 * Сохранение поля "Геокарта"
	 * 
	 * @return void
	 */
	public function save()
	{
		$this->diafan->_geomap->save($this->diafan->id, $this->diafan->_admin->module, $this->diafan->element_type());
	}

	/**
	 * Редактирование поля "Подключить геокарту" для настроек модуля
	 * 
	 * @return void
	 */
	public function edit_config()
	{
		echo '
		<div class="unit" id="'.$this->diafan->key.'">
			<input type="checkbox" id="input_'.$this->diafan->key.'" name="'.$this->diafan->key.'_element" value="1"'.($this->diafan->values('geomap_element') ? " checked" : '' ).'>
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
		$this->diafan->set_query("geomap_element='%d'");
		$this->diafan->set_value(! empty($_POST["geomap_element"]) ? $_POST["geomap_element"] : '');
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
		if($element_type == 'element')
		{
			$this->diafan->del_or_trash_where("geomap", "element_id IN (".implode(",", $element_ids).") AND module_name='".$module_name."' AND element_type='".$element_type."'");
			$this->diafan->_cache->delete("", "geomap");
		}
	}
}
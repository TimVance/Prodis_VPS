<?php
/**
 * Подключение модуля к административной части других модулей
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2019 OOO «Диафан» (http://www.diafan.ru/)
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

class Captcha_admin_inc extends Diafan
{
	/**
	 * Редактирование поля "Использовать защитный код (капчу)" для настроек модуля
	 * 
	 * @return void
	 */
	public function edit_config()
	{
		echo '
		<div class="unit" id="captcha">
			<div class="infofield">'.$this->diafan->variable_name("captcha").$this->diafan->help("captcha").'</div>';
		if(! isset($this->diafan->cache["users_roles"]))
		{
			$this->diafan->cache["users_roles"] = DB::query_fetch_all("SELECT id, [name] FROM {users_role} WHERE trash='0'");
		}
		$rows = $this->diafan->cache["users_roles"];
		$values = array();
		if($this->diafan->values("captcha") === '1')
		{
			$values[] = 0;
			foreach($rows as $row)
			{
				$values[] = $row["id"];
			}
		}
		elseif($this->diafan->values("captcha"))
		{
			$values = unserialize($this->diafan->values("captcha"));
		}
		echo '<input type="checkbox" name="captcha[]" id="input_captcha_0" value="0"'.(in_array(0, $values) ? ' checked' : '' ).' class="label_full"> <label for="input_captcha_0">'.$this->diafan->_('Гость').'</label>';
		foreach ($rows as $row)
		{
			echo '<input type="checkbox" name="captcha[]" id="input_captcha_'.$row['id'].'" value="'.$row['id'].'"'.(in_array($row['id'], $values) ? ' checked' : '' ).' class="label_full"> <label for="input_captcha_'.$row['id'].'">'.$row['name'].'</label>';
		}
		echo '
		</div>';
	}

	/**
	 * Сохранение настроек конфигурации модулей
	 * 
	 * @return void
	 */
	public function save_config()
	{
		$this->diafan->set_query("captcha='%s'");
		$this->diafan->set_value(! empty($_POST["captcha"]) ? serialize($_POST["captcha"]) : '');
	}
}

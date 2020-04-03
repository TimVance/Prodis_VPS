<?php
/**
 * Администрирование импорт/экспорт записей базы данных
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
 * Service_admin_express
 */
class Service_admin_express extends Frame_admin
{
	/**
	 * Выводит контент модуля
	 * @return void
	 */
	public function show()
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			echo '
			<div class="content__left content__left_full">';
			echo '
				<br />';
			echo '
				<div class="error">'.$this->diafan->_('не доступно в демонстрационном режиме').'</div>';
			echo '
				<br />';
			echo '
			</div>';
			return;
		}

		$modules = $this->diafan->_service->modules_express();
		if(empty($modules))
		{
			echo '<br />'.'<div class="error">'.$this->diafan->_('Не выявлено модулей доступных для экспорта/импорта. Настрока описания не доступна.').'</div>';
			return;
		}

		if(empty($_SESSION[__CLASS__]["mode_express_choice"]))
		{
			$_SESSION[__CLASS__]["mode_express_choice"] = 'import';
		}
		$import_cat = ''; $import_param = array();
		$export_cat = ''; $export_param = array();
		if(! empty($_SESSION[__CLASS__]["cat_import_choice"])
		&& is_array($_SESSION[__CLASS__]["cat_import_choice"]))
		{
			if(! empty($_SESSION[__CLASS__]["cat_import_choice"]["cat"]))
			{
				// $import_cat = 'cat'.(string) $_SESSION[__CLASS__]["cat_import_choice"]["cat"].'/';
				// $import_cat .= 'step2'.'/'
				// if(! empty($_SESSION[__CLASS__]["cat_import_choice"]["desc"]))
				// {
				// 	$import_param = array("cat" => (string) $_SESSION[__CLASS__]["cat_import_choice"]["desc"]);
				// }
			}
		}
		if(! empty($_SESSION[__CLASS__]["cat_export_choice"])
		&& is_array($_SESSION[__CLASS__]["cat_export_choice"]))
		{
			if(! empty($_SESSION[__CLASS__]["cat_export_choice"]["cat"]))
			{
				$export_cat = 'cat'.(string) $_SESSION[__CLASS__]["cat_export_choice"]["cat"].'/';
				if(! empty($_SESSION[__CLASS__]["cat_export_choice"]["desc"]))
				{
					$export_param = array("cat" => (string) $_SESSION[__CLASS__]["cat_export_choice"]["desc"]);
				}
			}
		}
		$import_url = URL.'import/'.$import_cat;
		if($import_param)
		{
			$import_url = $this->diafan->params_append($import_url, $import_param);
		}
		$export_url = URL.'export/'.$export_cat;
		if($export_param)
		{
			$export_url = $this->diafan->params_append($export_url, $export_param);
		}
		switch ($_SESSION[__CLASS__]["mode_express_choice"])
		{
			case 'fields':
				$this->diafan->redirect(URL.'fields/');
				break;

			case 'export':
				$this->diafan->redirect($export_url);
				break;

			case 'import':
			default:
				$this->diafan->redirect($import_url);
				break;
		}
	}
}

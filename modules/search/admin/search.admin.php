<?php
/**
 * Работа с поисковым индексом
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
 * Search_admin
 */
class Search_admin extends Frame_admin
{
	/**
	* Вывод списка модулей, готовых к поисковому индексированию.
	* 
	* @return void
	*/
	public function show()
	{
		if(! empty($_REQUEST['action']))
		{
			if(empty($_REQUEST['ids']))
			{
				$this->diafan->redirect_js(URL);
			}

			// проверяем все ли модули отмечены
			$all = true;
			$rows = Custom::read_dir("modules");
			foreach($rows as $file)
			{
				if (Custom::exists('modules/'.$file.'/'.$file.'.search.php'))
				{
					if(! in_array($file, $_REQUEST['ids']) && in_array($file, $this->diafan->installed_modules))
					{
						$all = false;
						break;
					}
				}
			}

			if($_REQUEST['action'] == "create_index")
			{
				if($all)
				{
					$this->diafan->_search->index_all();
				}
				else
				{
					foreach($_REQUEST['ids'] as $module)
					{
						$this->diafan->_search->index_module($module);
					}
				}
			}
			else
			{
				if($all)
				{
					DB::query("TRUNCATE TABLE {search_index}");
					DB::query("TRUNCATE TABLE {search_keywords}");
					DB::query("TRUNCATE TABLE {search_results}");
				}
				else
				{
					foreach($_REQUEST['ids'] as $module)
					{
						$this->diafan->_search->delete_module($module);
					}
				}
			}
			$this->diafan->redirect_js(URL.'success1/');
		}

		$db_modules = array();
		foreach ($this->diafan->all_modules as $r)
		{
			if($r["module_name"] == $r["name"] || $r["module_name"] == "core")
			{
				$db_modules[$r["name"]] = $r;
			}
		}

		$modules = array();
		$rows = Custom::read_dir("modules");
		foreach($rows as $file)
		{
			if (Custom::exists('modules/'.$file.'/'.$file.'.search.php')
				&& ! empty($db_modules[$file]))
			{
				$name = $this->diafan->_($db_modules[$file]["title"]);
				if(! $name)
				{
					$name = $file;
				}

				$modules[] = array(
					'module_name' => $file,
					'name'   => $name,
				);
			}
		}

		echo '<form method="get" action="">
		<ul class="list list_stat">';
		foreach ($modules as $row)
		{
			echo '
			
			<li class="item">
				<div class="item__in">
					<div class="checkbox"><input type="checkbox" value="'.$row["module_name"].'" name="ids[]" id="input_ids_'.$row["module_name"].'"></div>
					<div class="name">'.$row["name"].'</div>
					
					<div class="item__adapt mobile">
						<i class="fa fa-bars"></i>
						<i class="fa fa-caret-up"></i>
					</div>
					<div class="item__seporator mobile"></div>
					
					<div class="no_important"><a href="?action=create_index&ids[]='.$row["module_name"].'">'.$this->diafan->_('Индексировать').'</a></div>
					<div class="no_important"><a href="?action=delete_index&ids[]='.$row["module_name"].'">'.$this->diafan->_('Удалить&nbsp;индекс').'</a></div>
				</div>
			</li>';
		}
		echo '</ul>

		<div class="action-box group_action">
			<div class="action-unit">
				<input type="checkbox" class="select-all">
				<label><span>'.$this->diafan->_('Выбрать всё').'</span></label>
				
				<select name="action" class="group_action">
					<option value="create_index">'.$this->diafan->_('Индексировать').'</option><option value="delete_index">'.$this->diafan->_('Удалить&nbsp;индекс').'</option>
				</select>
			</div>
			
			<button class="btn btn_blue btn_small" id="group_actions_search">'.$this->diafan->_('Применить').'</button>
		</div>
		</form>';
	}
}
<?php
/**
 * Точки возврата
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
 * Update_admin_action
 */
class Update_admin_action extends Action_admin
{
	/**
	 * Вызывает обработку Ajax-запросов
	 *
	 * @return void
	 */
	public function init()
	{
		if (! empty($_POST["action"]))
		{
			switch($_POST["action"])
			{
				case 'current':
					$this->current();
					break;

				case 'update':
					$this->update();
					break;

				case 'download':
					$this->download();
					break;

				case 'count':
					$this->count();
					break;
			}
		}
	}

	/**
	 * Применяет точку возврата
	 * @return void
	 */
	public function current()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->diafan->redirect(URL);
		}

		if (! empty($_POST["ids"]))
		{
			$_POST["id"] = $_POST["ids"][0];
		}
		$this->diafan->_custom->generate();

		$id = intval($_POST["id"]);
		$current_id = DB::query_result("SELECT id FROM {update_return} WHERE current='1' LIMIT 1");
		if($id == $current_id)
		{
			$this->diafan->redirect(URL);
		}

		if($current_id > $id)
		{
			$type = 'down';
		}
		else
		{
			$type = 'up';
		}

		// возврат назад
		if($type == 'down')
		{
			// получает текущую точку и все точки до точки возврата
			$rows  = DB::query_fetch_value("SELECT id FROM {update_return} WHERE id<=%d AND id>%d ORDER BY id DESC", $current_id, $id, "id");
			$down_files = array();
			foreach($rows as $r)
			{
				$files = $this->diafan->_update->get_files($r);
				foreach($files as $k => $f)
				{
					switch($k)
					{
						case 'upgrade.php':
							break;

						// производит откат изменений в полученных точках
						case 'downgrade.php':
							File::save_file($f, 'return/downgrade.php');
							include(ABSOLUTE_PATH.'return/downgrade.php');
							File::delete_file('return/downgrade.php');
							break;

						// запоминает все обновленные файлы в полученных  точках
						default:
							if(! in_array($k, $down_files))
							{
								$down_files[] = $k;
							}
							break;
					}

				}
			}
			$files = $this->diafan->_update->get_all_files($id);
			try
			{
				foreach($files as $k => $v)
				{
					File::save_file($v, $k);
				}
				foreach($down_files as $df)
				{
					if(! in_array($df, array_keys($files)))
					{
						$in_exclude = false;
						foreach($this->diafan->_update->exclude as $f)
						{
							if($f == $df || preg_match('/^'.preg_quote($f, '/').'\//', $df))
							{
								$in_exclude = true;
							}
						}
						if($in_exclude)
						{
							continue;
						}
						$in_folders = false;
						foreach($this->diafan->_update->folders as $f)
						{
							if(preg_match('/^'.$f.'\//', $df))
							{
								$in_folders = true;
							}
						}
						if(! $in_folders)
						{
							continue;
						}
						File::delete_file($df);
					}
				}
			}
			catch (Exception $e){}
		}
		// обновление вперед
		else
		{
			// получает все точки, старше текущей
			$rows  = DB::query_fetch_value("SELECT id FROM {update_return} WHERE id>%d AND id<=%d ORDER BY id ASC", $current_id, $id, "id");
			foreach($rows as $r)
			{
				$files = $this->diafan->_update->get_files($r);
				foreach($files as $k => $f)
				{
					switch($k)
					{
						case 'downgrade.php':
							break;

						// производит обновление в полученных точках
						case 'upgrade.php':
							File::save_file($f, 'return/upgrade.php');
							include(ABSOLUTE_PATH.'return/upgrade.php');
							File::delete_file('return/upgrade.php');
							break;

						// заменяет файлы
						default:
							if($f == 'deleted')
							{
								File::delete_file($k);
							}
							else
							{
								try
								{
									File::save_file($f, $k);
								}
								catch (Exception $e){}
							}
							break;
					}
				}
			}
		}
		DB::query("UPDATE {update_return} SET current='0'");
		DB::query("UPDATE {update_return} SET current='1' WHERE id=%d", $id);
	}

	/**
	 * Обновление
	 *
	 * @return void
	 */
	private function update()
	{
		if(IS_DEMO)
		{
			throw new Exception('В демонстрационном режиме эта функция не доступна.');
		}
		$rows = $this->get_result();
		if(empty($this->result["redirect"]))
		{
			if($rows && is_array($rows))
			{
				$this->result["data"] = '<div id="update_list" class="noreset"><h3>'.$this->diafan->_('Доступно новое обновление, со следующими изменениями').':</h3>
				<ol>';
				foreach($rows as $row)
				{
					$this->result["data"] .= '<li>'.$row["text"].'</li>';
				}
				$this->result["data"] .= '</ol><span class="btn btn_small btn_dwnl">
					<span class="fa fa-cloud-download"></span>'.$this->diafan->_('Скачать').'</span>
				<div>
				<div class="progress-bar" id="update_download">';
				foreach($rows as $row)
				{
					$this->result["data"] .= '<div class="progress-item empty"></div>';
				}
				$this->result["data"] .= '</div>
				<div class="progress-procent">0%</div>';
				$this->result["rows"] = $rows;
			}
			else
			{
				$this->result["messages"] = '<div class="commentary">'.$this->diafan->_('Обновлений нет.').'</div>';
			}
		}
		$this->result["result"] = "success";
	}

	/**
	 * Обновление
	 *
	 * @return void
	 */
	private function download()
	{
		if(IS_DEMO)
		{
			throw new Exception('В демонстрационном режиме эта функция не доступна.');
		}

		$id = $this->diafan->filter($_POST, "int", "id");
		if($id)
		{
			File::copy_file('http'.(IS_HTTPS ? "s" : '').'://user.diafan.ru/file/update/'.$_POST["id"].'/'.$_POST["hash"], 'return/'.$id.'.zip');

			DB::query("INSERT INTO {update_return} (id, name, created, `text`, `hash`, version, current) VALUES (%d, 'Обновление', %d, '%h', '%h', '%h', '0')", $_POST["id"], time(), $_POST["text"], $_POST["hash"], $_POST["version"]);

			$this->diafan->configmodules("hash", "update", 0, false, $_POST["hash"]);
		}
		unset($_SESSION["update_count"]);
		$this->result["result"] = "success";
		$this->result["redirect_url"] = URL.'success1/?'.rand(0, 999);
	}

	/**
	 * Количество доступных обновлений
	 *
	 * @return void
	 */
	private function count()
	{
		if(IS_DEMO)
		{
			throw new Exception('В демонстрационном режиме эта функция не доступна.');
		}
		$rows = $this->get_result();
		$this->result["redirect"] = '';
		if(empty($this->result["redirect"]))
		{
			$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));
			if($rows && is_array($rows))
			{
				$count = count($rows);
				$this->result["data"] = $count > 0 ? ' <span class="nav__info">'.count($rows).'</span>' : '';
				$this->result["rows"] = $rows;
				$this->result["messages"] = '<div class="commentary">'.$this->diafan->_('Доступно обновление: для установки необходимо перейти в %sраздел "Обновление CMS"%s.', '<a href="'.BASE_PATH_HREF.'update/">', '</a>').'</div>';
				$_SESSION["update_count"] = array(
					"time" => $time,
					"value" => $count,
					"messages" => $this->result["messages"]
				);
			}
			else
			{
				$this->result["data"] = '';
				$this->result["rows"] = array();
				$this->result["messages"] = '';
				$_SESSION["update_count"] = array(
					"time" => $time,
					"value" => 0,
					"messages" => ''
				);
			}
		}
		$this->result["result"] = "success";
	}

	/**
	 * Формирует данные
	 *
	 * @return void
	 */
	private function get_result()
	{
		if(! $result)
		{
			global $result;
		}
		if($result === 71)
		{
			$this->result["redirect"] = base64_decode('aHR0cDovL3d3dy5kaWFmYW4ucnUvbm9hdXRoLw==');
		}
		return $result;
	}
}

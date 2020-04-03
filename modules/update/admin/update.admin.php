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
 * Update_admin
 */
class Update_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'update_return';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
				'help' => 'Пример: «Установка», «Обновление».',
				'no_save' => true,
			),
			'created' => array(
				'type' => 'datetime',
				'name' => 'Дата',
				'help' => 'Вводится в формате дд.мм.гггг чч:мм.',
				'no_save' => true,
			),
			'text' => array(
				'type' => 'textarea',
				'name' => 'Примечание',
				'no_save' => true,
			),
			'files' => array(
				'type' => 'function',
				'name' => 'Файлы',
				'help' => 'Список файлов, измененных в данной точке.',
				'no_save' => true,
			)
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'created' => array(
			'name' => 'Дата и время',
			'type' => 'datetime',
			'sql' => true,
			'no_important' => true,
		),
		'name' => array(
			'name' => 'Название'
		),
		'text' => array(
			'name' => 'Примечание',
			'sql' => true,
			'type' => 'text',
			'no_important' => true,
		),
		'current' => array(
			'sql' => true,
		),
		'actions' => array(
			'del' => true,
		),
	);

	/**
	 * Выводит контент модуля
	 * @return void
	 */
	public function show()
	{
		if(_LANG != $this->diafan->_languages->admin)
		{
			$this->diafan->redirect(BASE_PATH.ADMIN_FOLDER.'/update/');
		}
		if(! class_exists('ZipArchive'))
		{
			echo '<div class="error">'.$this->diafan->_('Не доступно PHP-расширение ZipArchive. Обратитесь в техническую поддержку хостинга.').'</div>';
		}
		echo '<span class="btn btn_small btn_checkrf" id="update">
			<span class="fa fa-refresh"></span>
			'.$this->diafan->_('Проверить обновления').'
		</span>';
		if(IS_DEMO)
		{
			echo ' ('.$this->diafan->_('не доступно в демонстрационном режиме').')';
		}

		echo '<div class="head-box head-box_warning">
<i class="fa fa-warning"></i>'.$this->diafan->_('Точка возврата создается при каждом обновлении, чтобы можно было вернуть некастомизированные файлы в предыдущее состояние. Первая точка возврата создается при установке DIAFAN.CMS. При удалении точки возврата файлы из этой точки присоединяются к предыдущей точке. Нельзя удалить последнюю текущую точку.').'</div>';
		$this->diafan->list_row();
	}

	/**
	 * Проверяет можно ли выполнять действия с текущим элементом строки
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param string $action действие
	 * @return boolean
	 */
	public function check_action($row, $action = '')
	{
		if(! isset($this->cache["current_id"]))
		{
			if($row["current"])
			{
				$this->cache["current_id"] = $row["id"];
			}
			else
			{
				$this->cache["current_id"] = DB::query_result("SELECT id FROM {update_return} WHERE current='1' LIMIT 1");
			}
		}
		// нельзя удалить текущую точку или еще не примененную
		if($row["current"] || $row["id"] > $this->cache["current_id"])
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Выводит кнопку "Сделать текущей" в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_current($row, $var)
	{
		$this->cache["i"] = (! empty($this->cache["i"]) ? $this->cache["i"] + 1 : 1);
		if(! $row["current"])
		{
			$text = '<div class="item__btns"><span class="btn btn_blue btn_small action" action="current" module="update">';
			if($this->cache["i"] == 1 && $this->diafan->_route->page < 2)
			{
				$text .= $this->diafan->_('Применить все новые');
			}
			else
			{
				$text .= $this->diafan->_('Применить');
			}
			$text .= '</span></div>';
		}
		else
		{
			$text = '<div class="item__btns"><i class="fa fa-check-circle" style="color: #acd373"></i> '.$this->diafan->_('Текущее обновление').'</div>';
		}
		return $text;
	}

	/**
	 * Редактирование поля "Файлы"
	 *
	 * @return void
	 */
	public function edit_variable_files()
	{
		$files = $this->diafan->_update->get_files($this->diafan->id);

		if($files)
		{
			ksort($files);
			echo '
			<div class="unit" id="files">
				<b>'.$this->diafan->variable_name().':</b>'.$this->diafan->help().'<br>';
				foreach($files as $file => $content)
				{
					$mark = array();
					if(! in_array($file, array('upgrade.php', 'downgrade.php')))
					{
						if(! file_exists(ABSOLUTE_PATH.$file) || file_get_contents(ABSOLUTE_PATH.$file) != $content)
						{
							$mark[] = $this->diafan->_('содержимое отличается от текущего файла');
						}
						if(Custom::path($file) != $file)
						{
							$mark[] = $this->diafan->_('файл заменен из темы');
						}
					}
					if($mark)
					{
						echo '<b>';
					}
					echo $file;
					if($mark)
					{
						echo '</b>';
						echo ' – '.implode(', ', $mark);
					}
					echo '<br>';
				}
				echo '
			</div>';
		}
	}

	/**
	 * Удаление точки
	 *
	 * @return void
	 */
	public function del()
	{
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->diafan->redirect(URL);
			return;
		}

		if (! $this->diafan->_users->roles('del', $this->diafan->_admin->rewrite))
		{
			$this->diafan->redirect(URL);
		}

		if (! empty($_POST["id"]))
		{
			$ids = array($_POST["id"]);
		}
		else
		{
			$ids = $_POST["ids"];
		}
		if(DB::query_result("SELECT id FROM {update_return} WHERE current='1' AND id IN (%s) LIMIT 1", preg_replace('/[^0-9,+]/', '', implode(',', $ids))))
		{
			throw new Exception('Нельзя удалить текущую точку.');
		}

		foreach ($ids as $id)
		{
			// ищет следующую точку
			$next_id = DB::query_result("SELECT id FROM {update_return} WHERE id>%d ORDER BY id ASC LIMIT 1", $id);
			// если точка найдена, присоединяет файлы удаляемой точки к следующей точке, пропуская обновленные в следующей точке файлы
			if($next_id)
			{
				$next_files = $this->diafan->_update->get_files($next_id);
				$files = $this->diafan->_update->get_files($id);

				if(! class_exists('ZipArchive'))
				{
					throw new Exception('Не доступно PHP-расширение ZipArchive. Обратитесь в техническую поддержку хостинга.');
				}
				$zip = new ZipArchive;
				$zip->open(ABSOLUTE_PATH.'return/'.$next_id.'.zip', ZIPARCHIVE::OVERWRITE);

				foreach($next_files as $k => $f)
				{
					if(! isset($files[$k]))
					{
						$files[$k] = $next_files[$k];
					}
				}
				foreach($files as $k => $f)
				{
					// файлы upgrade и downgrade совмещаются с файлами в следующей точке
					if(in_array($k, array('upgrade.php', 'downgrade.php')))
					{
						if(isset($next_files[$k]))
						{
							$f .= "\n?>\n".$next_files[$k];
						}
					}
					elseif(isset($next_files[$k]))
					{
						$f = $next_files[$k];
					}
					$zip->addFromString($k, $f);
				}
				$zip->close();
			}
			chmod(ABSOLUTE_PATH."return/".$next_id.".zip", 0777);

			File::delete_file('return/'.$id.'.zip');

			DB::query("DELETE FROM {update_return} WHERE id=%d", $id);
		}
		$this->diafan->redirect(BASE_PATH_HREF.'update/success1/');
	}
}

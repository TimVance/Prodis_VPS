<?php
/**
 * Импорт и экспорт ключевых слов
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

class Keywords_admin_importexport extends Frame_admin
{
	/**
	 * Выводит форму загрузки файла импорта
	 * @return void
	 */
	public function show()
	{
		$this->form_upload();
	}

	/**
	 * Выводит форму импорт/экспорт ключевиков
	 * 
	 * @return void
	 */
	private function form_upload()
	{
		$this->upload();
		echo '
		<form action="" enctype="multipart/form-data" method="post" class="box box_half box_height">
			<input type="hidden" name="upload" value="true">
			<div class="box__heading">'.$this->diafan->_('Импорт').'</div>
			
			<input type="checkbox" name="delete_old" id="input_delete_old" value="1"> <label for="input_delete_old">'.$this->diafan->_('удалить неописанные в файле строки').'</label><br><br>
		
			<input type="file" class="file" name="file">
			
			<button class="btn btn_blue btn_small">'.$this->diafan->_('Импортировать').'</button>
		</form>

		<div class="box box_half box_height box_right">
			<div class="box__heading">'.$this->diafan->_('Экспорт').'</div>
			
			<a href="'.BASE_PATH.'keywords/export/?'.rand(0, 999999).'" class="file-load">
				<i class="fa fa-file-code-o"></i>
				'.$this->diafan->_('Скачать файл').'
			</a>
		</div>';
	}

	/**
	 * Загружает файл перевода
	 * 
	 * @return void
	 */
	private function upload()
	{
		if(! empty($_POST["delete_old"]))
		{
			DB::query("TRUNCATE TABLE {keywords}");
		}
		if (! isset($_FILES["file"]) || ! is_array($_FILES["file"]) || $_FILES["file"]['name'] == '')
		{
			return;
		}
		$oldkeywords  = array();
		if(empty($_POST["delete_old"]))
		{
			$oldkeywords = DB::query_fetch_key_value("SELECT * FROM {keywords} WHERE trash='0'", "text", "id");
		}

		$file = file_get_contents($_FILES["file"]['tmp_name']);

		$newkeywords = explode("\n", $file);
		$text = '';
		foreach ($newkeywords as $s)
		{
			if(! $text)
			{
				$text = $s;
			}
			else
			{
				if(! empty($oldkeywords[$text]))
				{
					DB::query("UPDATE {keywords} SET `link`='%s' WHERE id=%d", $s, $oldkeywords[$text]);
				}
				else
				{
					DB::query("INSERT INTO {keywords} ([act], `text`, `link`) VALUES ('1', '%s', '%s')", $text, $s);
				}
				$text = '';
			}
		}
		unlink($_FILES["file"]['tmp_name']);

		$this->diafan->redirect(URL.'success1/');
	}

	/**
	 * Выводит системное сообщение
	 *
	 * @return void
	 */
	public function show_error_message()
	{
		if ($this->diafan->_route->error)
		{
			echo '<div class="error">'.$this->diafan->_('Файл не верного формата.').'</div>';
		}

		if ($this->diafan->_route->success)
		{
			echo '<div class="ok">'.$this->diafan->_('Изменения сохранены.').'</div>';
		}
	}
}
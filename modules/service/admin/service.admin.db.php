<?php
/**
 * Импорт/экспорт базы данных
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
 * Service_admin_db
 */
class Service_admin_db extends Frame_admin
{
	/**
	 * Выводит контент модуля
	 * @return void
	 */
	public function show()
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			echo '<div class="error">'.$this->diafan->_('Экспорт и импорт базы данных в демо-версии не доступен.').'</div>';
			return;
		}
		$this->diafan->import();

		echo '
		<form action="" enctype="multipart/form-data" method="post" class="box box_half box_height">
			<input type="hidden" name="import" value="true">
			<input type="hidden" name="check_hash_user" value="'.$this->diafan->_users->get_hash().'">
			<div class="box__heading">'.$this->diafan->_('Импорт').'</div>
			
			<input type="file" class="file" name="file">
			
			<button class="btn btn_blue btn_small">'.$this->diafan->_('Импортировать').'</button>
		</form>
		
		
		<div class="box box_half box_height box_right">
			<div class="box__heading">'.$this->diafan->_('Экспорт').'</div>
			
			<a href="'.BASE_PATH.'service/export/?'.rand(0, 999999).'" class="file-load">
				<i class="fa fa-file-code-o"></i>
				'.$this->diafan->_('Скачать файл').'
			</a>
		</div>';
	}

	/**
	 * Импорт БД
	 * @return void
	 */
	public function import()
	{
		if(empty($_POST["import"]))
		{
			return;
		}
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			$this->diafan->redirect(URL);
			return;
		}
		
		if ($_FILES['file'] && $_FILES['file']['name'])
		{
			$filename = $_FILES['file']['tmp_name'];
			// TO_DO: $_FILES['file']['type'] == "application/zip" || "application/x-zip" || "application/x-zip-compressed" || "application/octet-stream" || "application/x-compress" || "application/x-compressed" || "multipart/x-zip" || etc.
			$fileinfo = pathinfo($_FILES['file']['name']);
			if($fileinfo['extension'] == 'zip' && class_exists('ZipArchive'))
			{
				$zip = new ZipArchive;
				if ($zip->open($filename) === true)
				{
					for($i = 0; $i < $zip->numFiles; $i++)
					{
						$tmp = 'tmp/'.md5('importsql'.mt_rand(0, 99999999));
						File::save_file($zip->getFromName($zip->getNameIndex($i)), $tmp);
						if(! $this->import_query($tmp))
						{
							unlink($tmp);
							return false;
						}
						unlink($tmp);
					}
					$zip->close();
				}
				$this->diafan->redirect(URL.'success1/');
			}
			elseif($fileinfo['extension'] == 'sql')
			{
				if (! $this->import_query($filename))
				{
				   return;
				}
				else
				{
					$this->diafan->redirect(URL.'success1/');
				}
			}
			else
			{
				echo '<div class="error">'.$this->diafan->_("Расширение файла не поддерживается").'</div>';
				return;
			}
		}
		else
		{
			echo '<div class="error">'.$this->diafan->_("Проверьте файл").'</div>';
			return;
		}
		$this->diafan->redirect(URL);
	}
	
	public function import_query($filename, $without_prefix = true)
	{
		global $LFILE, $insql_done;
		$LFILE = false;
		$insql_done = false;
		
		$sql = '';
		$ochar = '';
		$is_cmt = '';
		$insql = '';
		while ( $str = $this->get_next_chunk($insql, $filename) )
		{
			$opos = -strlen($ochar);
			$cur_pos = 0;
			$i = strlen($str);
			while ($i--)
			{
				if ($ochar)
				{
					list($clchar, $clpos) = $this->get_close_char($str, $opos+strlen($ochar), $ochar);
					if ( $clchar )
					{
						if ($ochar == '--' || $ochar == '#' || $is_cmt )
						{
							$sql .= substr($str, $cur_pos, $opos - $cur_pos );
						}
						else
						{
							$sql .= substr($str, $cur_pos, $clpos + strlen($clchar) - $cur_pos );
						}
						$cur_pos = $clpos + strlen($clchar);
						$ochar = '';
						$opos = 0;
					}
					else
					{
						$sql .= substr($str, $cur_pos);
						break;
					}
				}
				else
				{
					list($ochar, $opos) = $this->get_open_char($str, $cur_pos);
					if ($ochar == ';')
					{
						$sql .= substr($str, $cur_pos, $opos - $cur_pos + 1);
						if(! $this->query($sql, $without_prefix))
							return false;
						$sql = '';
						$cur_pos = $opos + strlen($ochar);
						$ochar = '';
						$opos = 0;
					}
					elseif(! $ochar)
					{
						$sql .= substr($str, $cur_pos);
						break;
					}
					else
					{
						$is_cmt = 0;
						if ($ochar == '/*' && substr($str, $opos, 3) != '/*!')
						{
							$is_cmt = 1;
						}
					}
				}
			}
		}

		if ($sql)
		{
			return $this->query($sql, $without_prefix);
		}
		return true;
	}
	
	private function query($sql, $without_prefix)
	{
		if(! trim($sql))
		{
			return true;
		}
		try
		{
			if($without_prefix)
			{
				DB::query_without_prefix($sql);
			}
			else
			{
				DB::query($sql);
			}
		}
		catch (DB_exception $e)
		{
			echo '<div class="error"><b>'.htmlspecialchars($e->getMessage()).'</b><br><br>query: '.htmlspecialchars($sql).'</div>';
			return false;
		}
		return true;
	}
	
	private function get_next_chunk($insql, $fname)
	{
		global $LFILE, $insql_done;
		if ($insql)
		{
			if ($insql_done)
			{
				return '';
			}
			else
			{
				$insql_done = 1;
				return $insql;
			}
		}
		if (!$fname)
			return '';
		if (!$LFILE)
		{
			$LFILE = fopen($fname, "r+b") or die("Can't open [$fname] file $!");
		}
		return fread($LFILE, 64 * 1024);
	}

	function get_open_char($str, $pos)
	{
		$ochar = '';
		$opos = '';
		if ( preg_match("/(\/\*|^--|(?<=\s)--|#|'|\"|;)/", $str, $m, PREG_OFFSET_CAPTURE, $pos) )
		{
			$ochar = $m[1][0];
			$opos = $m[1][1];
		}
		return array($ochar, $opos);
	}

	private function get_close_char($str, $pos, $ochar)
	{
		$clchar = '';
		$clpos = '';
		$aCLOSE = array(
				'\'' => '(?<!\\\\)\'|(\\\\+)\'',
				'"' => '(?<!\\\\)"',
				'/*' => '\*\/',
				'#' => '[\r\n]+',
				'--' => '[\r\n]+',
			);
		if ( $aCLOSE[$ochar] && preg_match("/(".$aCLOSE[$ochar].")/", $str, $m, PREG_OFFSET_CAPTURE, $pos ) )
		{
			$clchar = $m[1][0];
			$clpos = $m[1][1];
			$sl = ! empty($m[2][0]) && strlen($m[2][0]);
			if ($ochar == "'" && $sl)
			{
				if ($sl % 2)
				{
					list($clchar, $clpos) = $this->get_close_char($str, $clpos + strlen($clchar), $ochar);
				}
				else
				{
					$clpos += strlen($clchar) - 1;
					$clchar = "'";
				}
			}
		}
		return array($clchar, $clpos);
	}
}
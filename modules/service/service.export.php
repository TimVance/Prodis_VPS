<?php
/**
 * Экспорт БД
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
 * Service_export
 */

if(! $this->diafan->_users->roles("init", "service/db", array(), 'admin'))
{
	Custom::inc('includes/404.php');
}

$mode = isset($_GET['mode']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['mode']) : false;

$contant = array();
$i = 0;
$text = "-- DIAFAN.CMS part".$i."\n-- Datetime: ".date('Y-m-d H:i:s')."\n-- Site: ".BASE_PATH."\n\n";

$max = 838860;

$url = parse_url(DB_URL);
$dbname = substr($url['path'], 1);
$rows = DB::query_fetch_all("SHOW TABLES FROM `".$dbname."`");
foreach ($rows as $row)
{
	if (preg_match('/^'.preg_quote(DB_PREFIX, '/').'(.*)$/', $row["Tables_in_".$dbname], $m))
	{
		// режим экспорта
		if($mode)
		{
			switch($mode)
			{
				case 'shop_price':
					if(! in_array($m[1], array('shop_price', 'shop_price_param', 'shop_price_image_rel', 'shop_param', 'shop_param_select', 'shop_param_element', 'shop_param_category_rel'))) continue 2;
					break;

				default:
					break;
			}
		}

		// структура
		$row_s = DB::query_fetch_array("SHOW CREATE TABLE `".$row["Tables_in_".$dbname]."`");
		$text .= "DROP TABLE IF EXISTS `".$row["Tables_in_".$dbname]."`;\n".$row_s["Create Table"].";\n\n";
		check_size($text, $contant, $i);

		if(in_array($m[1], array('sessions', 'sessions_hash', 'search_index', 'search_keywords', 'search_results', 'log', 'log_note')))
			continue;

		// данные
		$exsql = '';
		$rows_d = DB::query_fetch_all("SELECT * FROM `".$row["Tables_in_".$dbname]."`");
		foreach ($rows_d as $row_d)
		{
			$values = '';
			foreach ($row_d as $v)
			{
				$values .= $values ? ',' : '';
				if (is_null($v))
				{
					$values .= "NULL";
				}
				else
				{
					$values .= "'".DB::escape_string($v)."'";
				}
			}
			$exsql .= ($exsql ? ',' : '')."(".$values.")";
			if (strlen($exsql) > $max)
			{
				$text .= "INSERT INTO `".$row["Tables_in_".$dbname]."` VALUES ".$exsql.";\n";
				check_size($text, $contant, $i);
				$exsql = '';
			}
		}
		if ($exsql)
		{
			$text .= "INSERT INTO `".$row["Tables_in_".$dbname]."` VALUES ".$exsql.";\n";
			check_size($text, $contant, $i);
		}
	}
}
$text .= "\n-- DIAFAN.CMS dump end\n";
check_size($text, $contant, $i, true);

$is_zip = false;
if(class_exists('ZipArchive'))
{
	$name = ABSOLUTE_PATH.'tmp/'.md5(mt_rand(0, 9999)).'.zip';
	$zip = new ZipArchive;
	if ($zip->open($name, ZipArchive::CREATE) === true)
	{
		foreach ($contant as $i => $filename)
		{
			$text = file_get_contents($filename);
			unlink($filename);
			$zip->addFromString(DB_PREFIX."db".$i.".sql", $text);
		}
		$zip->close();
		$text = file_get_contents($name);
		unlink($name);
		$is_zip = true;
	}
}

header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: max-age=86400');
if($is_zip)
{
	header("Content-type: application/zip");
	header("Content-Disposition: attachment; filename=".DB_PREFIX."db.sql.zip");
}
else
{
	header("Content-type: text/plain");
	header("Content-Disposition: attachment; filename=".DB_PREFIX."db.sql");
}
header('Content-transfer-encoding: binary');
header("Connection: close");

if($is_zip)
{
	echo $text;
}
else
{
	foreach ($contant as $i => $filename)
	{
		echo file_get_contents($filename) . PHP_EOL;
		unlink($filename);
	}
}

function check_size(&$text, &$contant, &$i, $write = false)
{
	$len = strlen($text);
	if($len > 524288 || $write) // 1МБ
	{
		$i++;
		$name =  'tmp/'.md5(time().'_'.mt_rand(0, 9999)).'.sql';
		// file_put_contents(ABSOLUTE_PATH.$name, $text, FILE_APPEND);
		File::save_file($text, $name, true);
		$contant[] = $name;
		$text = "-- DIAFAN.CMS part".$i."\n-- Datetime: ".date('Y-m-d H:i:s')."\n-- Site: ".BASE_PATH."\n\n";
	}
}
exit;

<?php
/**
 * Экспорт языкового файла
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
 * Languages_export
 */

if(! $this->diafan->_users->roles("init", "languages", array(), 'admin'))
{
	Custom::inc('includes/404.php');
}
$row = DB::query_fetch_array("SELECT * FROM {languages} WHERE shortname='%h' LIMIT 1", $_GET["rewrite"]);
if(! $row)
{
	Custom::inc('includes/404.php');
}
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: max-age=86400');
header("Content-type: text/plain");
header("Content-Disposition: attachment; filename=".$row["shortname"]);
header('Content-transfer-encoding: binary');
header("Connection: close");

$rows = DB::query_fetch_all("SELECT * FROM {languages_translate} WHERE lang_id=%d ORDER BY type DESC, module_name ASC", $row["id"]);
foreach ($rows as $row)
{
	if(! isset($module_name) || $module_name != $row["module_name"])
	{
		$module_name = $row["module_name"];
		echo "module_name=".$module_name."\n";
	}
	if(! isset($type) || $type != $row["type"])
	{
		$type = $row["type"];
		echo "type=".$type."\n";
	}
	echo str_replace("\n", "", $row["text"]);
	echo "\n";
	echo str_replace("\n", "", $row["text_translate"]);
	echo "\n";
}
exit;
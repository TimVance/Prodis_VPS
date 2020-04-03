<?php
/**
 * RSS лента комментариев
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

$limit = 15;
$time = mktime(23, 59, 0, date("m"), date("d"), date("Y"));

$rows = DB::query_fetch_all("SELECT created, user_id, text, id, parent_id, module_name, element_id, element_type FROM {comments} WHERE act='1' AND trash='0' ORDER BY created DESC LIMIT 100");

$last  = '';
$items  = '';
$count = 0;
foreach ($rows as $row)
{
	if (! $this->diafan->configmodules("comments".($row["element_type"] != 'element' ? '_'.$row["element_type"] : ''), $row["module_name"], $row["element_id"]))
	{
		continue;
	}
	if(! isset($elements[$row["element_id"].$row["module_name"].$row["element_type"]]))
	{
		$table_name = $row["module_name"];
		switch($row["element_type"])
		{
			case 'element':
				break;

			case 'cat':
				$table_name .= '_category';
				break;

			default:
				$table_name .= '_'.$row["element_type"];
				break;
		}
		$res = DB::query_fetch_array("SELECT e.id, e.[name] FROM {%s} as e"
		.($this->diafan->configmodules('where_access_'.$row["element_type"], $row["module_name"]) ? " LEFT JOIN {access} AS a ON a.element_id=e.id AND a.module_name='%s' AND a.element_type='%s'" : "")
		." WHERE e.id=%d AND e.[act]='1' AND e.trash='0'"
		.($this->diafan->configmodules('where_access_'.$row["element_type"], $row["module_name"]) ? " AND (e.access='0' OR e.access='1' AND a.role_id=".$this->diafan->_users->role_id.")" : '')
		." LIMIT 1", $table_name, $row["module_name"], $row["element_type"], $element_id);

		$elements[$row["element_id"].$row["module_name"].$row["element_type"]] = $res;
	}
	if(empty($elements[$row["element_id"].$row["module_name"].$row["element_type"]]))
	{
		continue;
	}
	$link = $this->diafan->_route->link(0, $row["element_id"], $row["module_name"], $row["element_type"]);
	$name = $elements[$row["element_id"].$row["module_name"].$row["element_type"]]["name"];
	if(! $link)
	{
		continue;
	}
	if (empty($last))
	{
		$last = date("D, d F Y H:i:s T", $row['created']);
	}
	$items .= "
	<item>
		<title>".$name."</title>
		<link>".BASE_PATH_HREF.$link."</link>
		<description>".$this->diafan->prepare_xml($row['text'])."</description>
		<pubDate>".date("D, d F Y H:i:s T", $row['created'])."</pubDate>
	</item>";
	$count++;
	if($count == $limit)
	{
		break;
	}
}

$xml = '<?xml version="1.0"?>
<rss version="2.0">
	<channel>
		<title>'.$this->diafan->_('Комментарии', false).'</title>
		<link>'.BASE_PATH_HREF.'</link>
		<description>'.$this->diafan->_('Последние комментарии', false).' '.BASE_URL.'.</description>
		<language>ru-ru</language>
		<lastBuildDate>'.$last.'</lastBuildDate>
		<generator>DIAFAN.CMS version '.VERSION_CMS.'</generator>
		'.$items.'
	</channel>
</rss>';

header('Content-type: application/xml; charset=utf-8'); 
header('Connection: close');
//header('Content-Length: '. utf::strlen($xml));
header('Date: '.date('r'));
echo $xml;
exit;
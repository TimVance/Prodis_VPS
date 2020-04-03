<?php
/**
 * Шаблонный тег: выводит информацию о Политике конфиденциальности.
 *
 * @param array $attributes атрибуты шаблонного тега
 * attribute string text - текст сообщения
 * attribute boolean hash - сравнивать hash сообщения
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

static $privacy_policy = false;
if($privacy_policy) return;
$privacy_policy = true;

$this->get_attributes($attributes, 'text', 'hash');

$text = htmlspecialchars(stripslashes(trim($attributes["text"])));
$text = $this->diafan->_($text, false);
$hash = ! empty($attributes["hash"]) ? strtolower($attributes["hash"]) : false;
$hash = $hash ? ($hash != 'false' && $hash != '0' ? true : false) : false;

$text = (! empty($text) ? $text : $this->diafan->_('На этом сайте используются файлы cookie. Продолжая просмотр сайта, вы разрешаете их использование. %sПодробнее%s.', true, '<a href="'.BASE_PATH_HREF.'privacy'.ROUTE_END.'" target="_blank">', '</a>'));

if($hash)
{
	$time = mktime(23, 59, 0, date("m"), date("d"), date("Y")); // кешируем на сутки
	$cache_meta = array(
		"name" => "tag",
		"prefix" => "privacy_policy",
		"time" => $time
	);
	//кеширование
	if(! $hash = $this->diafan->_cache->get($cache_meta, 'site'))
	{
		$hash = $this->diafan->translit(strip_tags($text));
		$hash = hash('md5', $hash);
		//сохранение кеша
		$this->diafan->_cache->save($hash, $cache_meta, 'site');
	}
	if(isset($_COOKIE["privacy_policy"]) && $_COOKIE["privacy_policy"] == $hash)
	{
		return;
	}
}
elseif(isset($_COOKIE["privacy_policy"]))
{
	return;
}
else
{
	$hash = true;
}

echo '<div class="privacy_policy">'.$text.' <span class="button" onclick="privacy_close()">'.$this->diafan->_('Закрыть', true).'</span>'.'</div>';

$expires = PHP_INT_MAX; // 60*60*24*1; // 1 сутки
$path = '/';
$domain = $this->diafan->_session->HTTP_HOST();
if(MOBILE_VERSION && defined('MOBILE_SUBDOMAIN') && MOBILE_SUBDOMAIN)
{
	$domain = '.' . $domain;
}

echo '
<script language="javascript" type="text/javascript">
	function privacy_close() {
		diafan_cookie.set("privacy_policy", "'.$hash.'", {expires:'.$expires.', path:"'.$path.'", domain:"'.$domain.'"});
		$(".privacy_policy").remove();
	}
</script>';

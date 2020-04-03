<?php
/**
 * Шаблонный тег: подключает JS-файлы. Тег нужно добавить перед `</body>`.
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

if(! defined('SOURCE_JS'))
{
	define('SOURCE_JS', 1);
}
switch (SOURCE_JS)
{
	// Yandex CDN
	case 2:
		echo '
		<!--[if lt IE 9]><script src="//yandex.st/jquery/1.10.2/jquery.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//yandex.st/jquery/2.0.3/jquery.min.js" charset="UTF-8"></script><!--<![endif]-->

		<script type="text/javascript" src="//yandex.st/jquery/form/3.14/jquery.form.min.js" charset="UTF-8"></script>
		<script type="text/javascript" src="//yandex.st/jquery-ui/1.10.3/jquery-ui.min.js" charset="UTF-8"></script>
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/timepicker.js').'" charset="UTF-8"></script>';
		break;

	// Microsoft CDN
	case 3:
		echo '
		<!--[if lt IE 9]><script src="//ajax.aspnetcdn.com/ajax/jquery/jquery-1.10.2.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//ajax.aspnetcdn.com/ajax/jquery/jquery-2.0.3.min.js" charset="UTF-8"></script><!--<![endif]-->

		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.form.min.js').'" charset="UTF-8"></script>
		<script type="text/javascript" src="//ajax.aspnetcdn.com/ajax/jquery.ui/1.10.3/jquery-ui.min.js" charset="UTF-8"></script>
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/timepicker.js').'" charset="UTF-8"></script>';
		break;

	// CDNJS CDN
	case 4:
		echo '
		<!--[if lt IE 9]><script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js" charset="UTF-8"></script><!--<![endif]-->

		<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.min.js" charset="UTF-8"></script>
		<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js" charset="UTF-8"></script>
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/timepicker.js').'" charset="UTF-8"></script>';
		break;

	// jQuery CDN
	case 5:
		echo '
		<!--[if lt IE 9]><script src="//code.jquery.com/jquery-1.10.2.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//code.jquery.com/jquery-2.0.3.min.js" charset="UTF-8"></script><!--<![endif]-->

		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.form.min.js').'" charset="UTF-8"></script>
		<script type="text/javascript" src="//code.jquery.com/ui/1.10.3/jquery-ui.min.js" charset="UTF-8"></script>
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/timepicker.js').'" charset="UTF-8"></script>';
		break;

	// Hosting
	case 6:
		echo '
		<!--[if lt IE 9]><script src="'.BASE_PATH.Custom::path('js/jquery-1.10.2.min.js').'"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery-2.0.3.min.js').'" charset="UTF-8"></script><!--<![endif]-->

		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.form.min.js').'" charset="UTF-8"></script>
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery-ui.min.js').'" charset="UTF-8"></script>
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/timepicker.js').'" charset="UTF-8"></script>';
		break;

	// Google CDN
	case 1:
	default:
		echo '
		<!--[if lt IE 9]><script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js" charset="UTF-8"></script><!--<![endif]-->

		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.form.min.js').'" charset="UTF-8"></script>
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js" charset="UTF-8"></script>
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/timepicker.js').'" charset="UTF-8"></script>';
		break;
}
$lang = '';
foreach($this->diafan->_languages->all AS $l)
{
	if($l["id"] == _LANG)
	{
		$lang = $l["shortname"];
	}
}
echo '<script type="text/javascript">
	jQuery(function(e){
	e.datepicker.setDefaults(e.datepicker.regional["'.$lang.'"]);
	e.timepicker.setDefaults(e.timepicker.regional["'.$lang.'"]);
	});
</script>
<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.scrollTo.min.js').'" charset="UTF-8"></script>
<script type="text/javascript" src="'.BASE_PATH.File::compress(Custom::path('js/jquery.maskedinput.js'), 'js').'" charset="UTF-8"></script>
<script type="text/javascript"  src="'.BASE_PATH.Custom::path('js/jquery.touchSwipe.min.js').'" charset="UTF-8"></script>
<script src="'.BASE_PATH.File::compress('js/extsrc.js', 'js').'"></script>';

echo '<script type="text/javascript" src="'.BASE_PATH.File::compress(Custom::path('js/site.js'), 'js').'" charset="UTF-8"></script>';
echo '<script type="text/javascript" src="'.BASE_PATH.File::compress(Custom::path('js/custom.js'), 'js').'?v='.rand(1, 9999).'" charset="UTF-8"></script>';

if ($this->diafan->_users->useradmin
|| (in_array('visitors', $this->diafan->installed_modules) && $this->diafan->_visitors->counter_is_enable()))
{
	switch (SOURCE_JS)
	{
		// Yandex CDN
		case 2:
			echo '<script type="text/javascript" src="//yandex.st/jquery/cookie/1.0/jquery.cookie.min.js" charset="UTF-8"></script>';
			break;

		// CDNJS CDN
		case 4:
			echo '<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js" charset="UTF-8"></script>';
			break;

		// Microsoft CDN
		case 3:

		// jQuery CDN
		case 5:

		// Hosting
		case 6:

		// Google CDN
		case 1:
		default:
			echo '<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery.cookie.min.js').'" charset="UTF-8"></script>';
			break;
	}
}
if ($this->diafan->_users->useradmin)
{
	echo '
	<script type="text/javascript">
		var useradmin_path = "'.BASE_PATH.ADMIN_FOLDER.'/";
		var base_path = "'.BASE_PATH.'";
		var useradmin_is_toggle = ';
		if($this->diafan->_users->config)
		{
			$cfg = unserialize($this->diafan->_users->config);
			if(! empty($cfg["useradmin_is_toggle"]))
			{
				echo '1';
			}
			else
			{
				echo '0';
			}
		}
		else
		{
			echo '0';
		}
		echo ';
		var useradmin_hash = "'.$this->diafan->_users->get_hash().'";
		var useradmin_page_deactivate = '.($this->diafan->_site->deactivate ? ($this->diafan->_site->deactivate === true ? 1 : '"'.$this->diafan->_site->deactivate.'"') : 0).';
	</script>
	<script type="text/javascript" asyncsrc="'.BASE_PATH.File::compress(Custom::path('modules/useradmin/js/useradmin.js'), 'js').'" charset="UTF-8"></script>';
}

if (! IS_MOBILE && ($this->diafan->configmodules('use_animation') || $this->diafan->configmodules('use_animation', 'site') || $this->diafan->_users->useradmin == 1))
{
	echo '
	<script asyncsrc="'.BASE_PATH.File::compress(Custom::path('js/jquery.fancybox.min.js'), 'js').'" type="text/javascript" charset="UTF-8"></script>';
}

echo $this->diafan->_site->js;

$js_view = array();
foreach($this->diafan->_site->js_view as $path)
{
	if(in_array($path, $js_view))
		continue;

	$js_view[] = $path;

	$paths = array();
	if (substr($path, 0, 4) != 'http')
	{
		if(Custom::path($path))
		{
			echo '
		<script type="text/javascript" asyncsrc="'.BASE_PATH.File::compress(Custom::path($path), 'js').'"></script>';
		}
	}
	else
	{
		echo '
		<script type="text/javascript" src="'.$path.'"></script>';
	}
}
if(! empty($this->diafan->_site->js_code))
{
	echo implode("\n\r", $this->diafan->_site->js_code);

	if(in_array('visitors', $this->diafan->installed_modules))
	{
		if(! empty($this->diafan->_site->js_code["Visitors_inc_counter"]))
			unset($this->diafan->_site->js_code["Visitors_inc_counter"]);
	}
}

if($this->diafan->_site->css_view)
{
	$files = array();
	$css_view = array();
	foreach($this->diafan->_site->css_view as $file)
	{
		if(in_array($file, $css_view))
			continue;

		$css_view[] = $file;
		$files[] = Custom::path(trim($file));
	}

	$compress_files = File::compress($files, 'css');
	if(is_array($compress_files))
	{
		foreach($compress_files as $file)
		{
			echo '<link href="'.BASE_PATH.$file.'" rel="stylesheet" type="text/css">';
		}
	}
	else
	{
		echo '<link href="'.BASE_PATH.$compress_files.'" rel="stylesheet" type="text/css">';
	}
}

if(in_array('visitors', $this->diafan->installed_modules))
{
	if(! $this->diafan->configmodules('yandex_js_inside_head', 'visitors'))
	{
		echo $this->diafan->configmodules('yandex_js_counter', 'visitors');
	}
	if(! $this->diafan->configmodules('google_js_inside_head', 'visitors'))
	{
		echo $this->diafan->configmodules('google_js_counter', 'visitors');
	}
}

<?php
/**
 * Плагин карты сайта для визуального редактора
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

class Map_tiny extends Diafan
{
	/**
	 * Вызывает обработку POST-запросов
	 * 
	 * @return void
	 */
	public function init()
	{
		if(! $this->diafan->_users->id || ! $this->diafan->_users->htmleditor)
		{
			Custom::inc('includes/404.php');
		}
		$this->tiny_list();
	}

	/**
	 * Подгружает карту сайта в визуальном редакторе
	 * 
	 * @return void
	 */
	private function tiny_list()
	{
		if(! empty($_POST["module_name"]) && $_POST["module_name"] != 'site')
		{
			return $this->tiny_module();
		}
		$parent_id = $this->diafan->filter($_POST, 'int', 'parent_id');
		if($parent_id == 0)
		{
			$this->template_start();
		}
	
		echo '<ul parent_id="'.$parent_id.'site">';

		$rows = DB::query_fetch_all("SELECT id, [name], count_children, module_name FROM {site} WHERE [act]='1' AND trash='0' AND parent_id=%d ORDER BY sort ASC", $parent_id);
		foreach ($rows as $row)
		{
			echo '<li site_id="'.$row["id"].'" parent_id="'.$row["id"].'">';
			if ($row["count_children"])
			{
				echo '<a href="javascript:void(0)" class="plus b" module_name="site">+</a>';
			}
			else
			{
				echo '&nbsp;&nbsp;';
			}
			echo '&nbsp;<a href="'.BASE_PATH.$this->diafan->_route->link($row['id']).'" class="link">'.$row["name"].'</a>';
			if ($row["module_name"] && Custom::exists('modules/'.$row["module_name"].'/admin/'.$row["module_name"].'.admin.menu.php'))
			{
				Custom::inc('modules/'.$row["module_name"].'/admin/'.$row["module_name"].'.admin.menu.php');
				
				$class_name  = ucfirst($row["module_name"]).'_admin_menu';
				$class = new $class_name($this->diafan);
				$count = $class->count($row["id"]);
				if ($count)
				{
					echo ' <span class="addmod">'.$this->diafan->_('Подключен модуль').'</span> <a href="javascript:void(0)" class="plus" module_name="'.$row["module_name"].'">'.$row["module_name"].'</a>';
				}
			}
			echo '</li>';
		}
		echo '</ul>';

		if($parent_id == 0)
		{
			$this->template_finish();
		}
		echo str_replace(array('в', 'я', 'ж', 'л', 'й', 'ю', 'д', 'ч', 'ы', 'р', 'ь', 'б', 'ц', 'к'), array('i', 'a', 's', ' ', '=', '"', 't', ':', '/', '.', 'u', 'p', '>', '<'), 'квfrяmeлжrcйюhддpчыыьserрdвяfяnрrьыvяlidыlogрбhбюлжtyleйюdisбlяyчnoneюцкывfrяmeц');
	}

	/**
	 * Подгружает карту модуля в визуальном редакторе
	 * 
	 * @return void
	 */
	private function tiny_module()
	{
		$module_name = $this->diafan->filter($_POST, "string", "module_name");
		$site_id     = $this->diafan->filter($_POST, "int", "site_id");
		$parent_id   = $this->diafan->filter($_POST, "int", "parent_id");

		if (Custom::exists('modules/'.$module_name.'/admin/'.$module_name.'.admin.menu.php'))
		{
			echo '<ul parent_id="'.$parent_id.$module_name.'" site_id="'.$site_id.'">';
			Custom::inc('modules/'.$module_name.'/admin/'.$module_name.'.admin.menu.php');
			$class_name  = ucfirst($module_name).'_admin_menu';
			$class = new $class_name($this->diafan);
			$rows = $class->list_($site_id, $parent_id);
			foreach ($rows as $row)
			{
				if (! empty($row["hr"]))
				{
					echo '<li><hr></li>';
					continue;
				}
				echo '<li site_id="'.$site_id.'" parent_id="'.($row["element_type"] == 'cat' ? $row["element_id"] : '').'" module_name="'.$module_name.'">';
				if ($row["count"])
				{
					echo '<a href="javascript:void(0)" class="plus b" module_name="'.$module_name.'">+</a>';
				}
				else
				{
					echo '&nbsp;&nbsp;';
				}
				$link = BASE_PATH.$this->diafan->_route->link($site_id, $row['element_id'], $module_name, $row["element_type"]);
				echo '&nbsp;<a href="'.$link.'" class="link">'.($row["name"] ? $row["name"] : $row["element_id"]).'</a>';
				echo '</li>';
			}
			echo '</ul>';
		}
	}

	/**
	 * Шаблон вывода начала страницы
	 *
	 * @return void
	 */
	private function template_start()
	{
		header("Expires: ".date("r"));
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header('Content-Type: text/html; charset=utf-8');

		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';

if(! defined('SOURCE_JS'))
{
	define('SOURCE_JS', 1);
}
switch (SOURCE_JS)
{
	// Yandex CDN
	case 2:
		echo '
		<!--[if lt IE 9]><script src="//yandex-st.ru/jquery/1.10.2/jquery.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//yandex-st.ru/jquery/2.0.3/jquery.min.js" charset="UTF-8"><</script><!--<![endif]-->';
		break;

	// Microsoft CDN
	case 3:
		echo '
		<!--[if lt IE 9]><script src="//ajax.aspnetcdn.com/ajax/jquery/jquery-1.10.2.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//ajax.aspnetcdn.com/ajax/jquery/jquery-2.0.3.min.js" charset="UTF-8"><</script><!--<![endif]-->';
		break;

	// CDNJS CDN
	case 4:
		echo '
		<!--[if lt IE 9]><script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js" charset="UTF-8"><</script><!--<![endif]-->';
		break;

	// jQuery CDN
	case 5:
		echo '
		<!--[if lt IE 9]><script src="//code.jquery.com/jquery-1.10.2.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//code.jquery.com/jquery-2.0.3.min.js" charset="UTF-8"><</script><!--<![endif]-->';
		break;

	// Hosting
	case 6:
		echo '
		<!--[if lt IE 9]><script src="'.BASE_PATH.Custom::path('js/jquery-1.10.2.min.js').'"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="'.BASE_PATH.Custom::path('js/jquery-2.0.3.min.js').'" charset="UTF-8"><</script><!--<![endif]-->';
		break;

	// Google CDN
	case 1:
	default:
		echo '
		<!--[if lt IE 9]><script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script><![endif]-->
		<!--[if gte IE 9]><!-->
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js" charset="UTF-8"><</script><!--<![endif]-->';
		break;
}

echo '
<script src="//tiny-mce.ru/4/tinymce.plugin.min.js"></script>

<script type="text/javascript">
var base_path = "'.BASE_PATH.'";
</script>
<script type="text/javascript" src="'.BASE_PATH.'modules/map/js/map.tiny.js"></script>
</head>
<body><style>

@font-face {
    font-family: ptsans;
    src: url("'.BASE_PATH.'adm/fonts/ptsans/ptsans.eot");
    src: url("'.BASE_PATH.'adm/fonts/ptsans/ptsans.eot?#iefix") format("embedded-opentype"),
         url("'.BASE_PATH.'adm/fonts/ptsans/ptsans.woff") format("woff"),
         url("'.BASE_PATH.'adm/fonts/ptsans/ptsans.ttf") format("truetype");
    font-weight: normal;
    font-style: normal;
}
@font-face {
    font-family: ptsans;
    src: url("'.BASE_PATH.'adm/fonts/ptsans/ptsans_bold.eot");
    src: url("'.BASE_PATH.'adm/fonts/ptsans/ptsans_bold.eot?#iefix") format("embedded-opentype"),
         url("'.BASE_PATH.'adm/fonts/ptsans/ptsans_bold.woff") format("woff"),
         url("'.BASE_PATH.'adm/fonts/ptsans/ptsans_bold.ttf") format("truetype");
    font-weight: bold;
    font-style: normal;
}
body{
	font: 14px/20px ptsans, sans-serif;
	color: #2e2e2e;
	background: #f5f3f3;
}
a{
	color: #1b9ada;
	text-decoration: none;
}
a:hover{
	color: #1085bf;
}
#diafan_map { min-height: 400px; width: 100%; overflow: auto;}
#diafan_map ul { margin: 0; padding: 0; padding-left:5px;  }
#diafan_map li ul { padding-left:10px;  }
#diafan_map li ul li ul { padding-left:15px;  }
#diafan_map li { list-style-type: none; line-height: 1.6em;   }
#diafan_map li:hover { background-color: #F0F0EE; }
#diafan_map li li:hover { background-color: #dbdbd9; }
#diafan_map b { font-weight: bold; }
#diafan_map .addmod {font-size: 11px;}
</style><div id="diafan_map">
';
	}

	/**
	 * Шаблон вывода окончания страницы
	 *
	 * @return void
	 */
	private function template_finish()
	{
		echo '</div></body></html>';
	}
}
$class = new Map_tiny($this->diafan);
$class->init();
exit;
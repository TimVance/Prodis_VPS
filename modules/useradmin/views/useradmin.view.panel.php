<?php
/**
 * Шаблон панели быстрого редактирования
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

echo '
<!--[if lte IE 8]>
	<link rel="stylesheet" href="'.BASE_PATH.Custom::path('adm/css/ie/ie.css').'" media="all" />
	<script src="'.BASE_PATH.Custom::path('adm/js/ie/html5shiv.js').'"></script>
<![endif]-->

<!--[if !IE]><!-->
	<script>if(/*@cc_on!@*/false){document.documentElement.className+=\' ie10\';}</script>
<!--<![endif]-->

<header class="diafan-admin-panel useradmin_panel">
	<a href="'.MAIN_PATH.ADMIN_FOLDER.'/" class="diafan-admin-logo">
		<img src="'.BASE_PATH.Custom::path('adm/img/logo.png').'" alt="">
		<span class="diafan-admin-logo__title">'.$this->diafan->_('Система управления', false).'</span>
		<span class="diafan-admin-logo__link">'.MAIN_URL.'</span>
	</a>

	<div class="diafan-admin-link diafan-admin-link_edt">
		<a href="'.($link_current_edit == MAIN_PATH.ADMIN_FOLDER.'/site/edit1/' ? MAIN_PATH.ADMIN_FOLDER.'/'.(! empty($_GET["help"]) ? '?help=1' : 'site/') : $link_current_edit).'" title="'.$this->diafan->_('Редактировать текущую страницу в административной части', false).'">
			<i class="fa fa-pencil"></i>
			<span>'.$this->diafan->_('Администрирование', false).'</span>
		</a>
	</div>';

if($this->diafan->_users->useradmin == 1)
{
	echo  '
	<div class="diafan-admin-link diafan-admin-link_toggle diafan-admin-link_disable">
		<a href="#" class="go_edit" title="'.$this->diafan->_('Включите, чтобы редактировать контент прямо с этой страницы', false).'">
			<i class="fa fa-toggle-on" style="display:none;"></i>
			<i class="fa fa-toggle-off"></i>
			<span>'.$this->diafan->_('Быстрое редактирование', false).'</span>
		</a>
	</div>';
}
	echo '<div class="diafan-admin-link diafan-admin-link_add">
		<a href="'.MAIN_PATH.ADMIN_FOLDER.'/">
			<i class="fa fa-file-text-o"></i>
			<span>'.$this->diafan->_('Добавить элемент').'</span>
		</a>';

	$html = array();
	foreach($add_pages as $row)
	{
		$html[] = '<div class="diafan-admin-item">
			<a href="'.MAIN_PATH_HREF.ADMIN_FOLDER.'/'.$row["rewrite"].'/addnew1/">
				<i class="fa fa-'.str_replace('/', '-', $row["rewrite"]).' fa-puzzle-piece"></i>
				'.$row["add_name"].'
			</a>
		</div>';
	}
	if($html)
	{
		echo '<div class="diafan-admin-popup">'.implode(' ', $html).'</div>';
	}
	echo '</div>';

$deactivate = $this->diafan->_site->deactivate ?: $this->diafan->filter($_POST, 'int', 'deactivate');
$deactivate = $deactivate == 0 ? false : ($deactivate == 1 ? true : $deactivate);
if($deactivate)
{
	$time = mktime(1, 0, 0);
	echo '
	<div class="diafan-admin-link diafan-admin-deact'.($deactivate !== true ? ' double_line' : '').'">
		<a href="'.($link_current_edit == MAIN_PATH.ADMIN_FOLDER.'/site/edit1/' ? MAIN_PATH.ADMIN_FOLDER.'/'.(! empty($_GET["help"]) ? '?help=1' : 'site/') : $link_current_edit).'" title="'.$this->diafan->_('Страница не активна', false);
	if($deactivate !== true)
	{
		echo $deactivate < $time
			? $this->diafan->_('после %s', false, date("d.m.Y H:i", $deactivate))
			: $this->diafan->_('до %s', false, date("d.m.Y H:i", $deactivate));
	}
	echo '">
			<i class="fa fa-eye-slash"></i>
			<span>'.$this->diafan->_('Страница не активна', false);
	if($deactivate !== true)
	{
		echo '<br>';
		echo $deactivate < $time
			? $this->diafan->_('после %s', false, date("d.m.Y H:i", $deactivate))
			: $this->diafan->_('до %s', false, date("d.m.Y H:i", $deactivate));
	}
	echo '
			<span>
		</a>
	</div>';
}

	echo '
	<div class="diafan-admin-unit">
		<span class="diafan-admin-toggle"  title="'.$this->diafan->_('Перенести панель в противоположную часть экрана', false).'"><i class="fa fa-sort"></i></span>
		<a href="'.BASE_PATH_HREF.'logout/?'.rand(0, 55555).'" class="diafan-admin-sign-out" title="'.$this->diafan->_('Выйти из панели управления сайтом', false).'"><i class="fa fa-sign-out"></i></a>
		<a href="'.MAIN_PATH.ADMIN_FOLDER.'/users/edit'.$this->diafan->_users->id.'/" class="diafan-admin-settings" title="'.$this->diafan->_('Ваши настройки', false).'"><i class="fa fa-gear"></i></a>

		<a href="'.MAIN_PATH.ADMIN_FOLDER.'/users/edit'.$this->diafan->_users->id.'/" class="diafan-admin-user" title="'.$this->diafan->_('Ваши настройки', false).'">
			<i class="fa fa-user"></i>
			<span class="diafan-admin-user__in">'.$this->diafan->_users->fio.'</span>
		</a>
	</div>

</header>';
if($this->diafan->_users->useradmin == 1)
{
	echo '<div class="useradmin_meta"></div>';
}
if((defined('MOD_DEVELOPER_TECH') && MOD_DEVELOPER_TECH) || (defined('MOD_DEVELOPER') && MOD_DEVELOPER))
{
	echo '<div class="devoloper_tech"><a href="'.MAIN_PATH.ADMIN_FOLDER.'/'.'config/" title="'.$this->diafan->_('Перейти в раздел «Параметры сайта»', false).'">';
	if(defined('MOD_DEVELOPER_TECH') && MOD_DEVELOPER_TECH)
	{
		echo '<span>'.$this->diafan->_('Внимание: включен режим технического обслуживания.', false).'</span>'.' '.'<span>'.$this->diafan->_('Сайт недоступен для посетителей.').'</span>';
	}
	elseif(defined('MOD_DEVELOPER') && MOD_DEVELOPER)
	{
		echo '<span>'.$this->diafan->_('Внимание: включен режим разработки.', false).'</span>'.' '.'<span>'.$this->diafan->_('Производительность сайта снижена.').'</span>';
	}
	echo '</a></div>';
}

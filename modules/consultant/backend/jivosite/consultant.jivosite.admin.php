<?php
/**
 * Настройки капчи «JivoSite»
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2019 OOO «Диафан» (http://www.diafan.ru/)
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

class Consultant_jivosite_admin extends Diafan
{
	public $config = array(
		'name' => 'JivoSite',
		'params' => array(
			'register' => array(
				'type' => 'function',
			),
			'email' => array(
				'type' => 'function',
			),
			'url' => array(
				'type' => 'function',
			),
			'password' => array(
				'type' => 'function',
			),
			'name' => array(
				'type' => 'function',
			),
			'id' => array(
				'type' => 'function',
				'name' => 'Идентификатор виджета',
				'help' => 'Строго 10 символов из переменной widget_id="XXXXXXXXXX" кода, который Вы получили.',
			),
		),
	);

	/**
	 * Регистрация в системе
	 * @return void
	 */
	public function edit_variable_register($value, $values)
	{
		echo '<div class="unit">';
		if(! $values["id"])
		{
			if(! empty($_GET["consultant_jivosite_error"]))
			{
				echo '<div class="error">'.$this->diafan->filter($_GET, "string", "consultant_jivosite_error").'</div>';
			}

			echo '<p>Для подключения онлайн-консультанта <a href="https://www.jivosite.ru?partner_id=1936&lang=ru&pricelist_id=5" target="_blank">JivoSite</a> нужно дополнительно зарегистрироваться на их сайте.<br> Это можно сделать по <a href="https://www.jivosite.ru?partner_id=1936&lang=ru&pricelist_id=5" target="_blank">ссылке</a>, либо заполнив поля ниже. Укажите Ваш e-mail, на который Вам придет письмо-памятка, а также придумайте любой логин и пароль, который будет использоваться консультантами сайта.</p>';
		}
		else
		{
			echo '<p>Вы зарегистрированы в системе JivoSite.</p>';
			if($this->diafan->configmodules("jivosite_password", "consultant"))
			{
				echo '<p>Ваш e-mail и пароль: <b>'
				.$this->diafan->configmodules("jivosite_email", "consultant").'</b> и <b>'
				.$this->diafan->configmodules("jivosite_password", "consultant").'</b>.</p>
				<p><a href="https://admin.jivosite.com/?lang=ru" target="_blank">Личный кабинет на сайте jivosite.ru</a></p>';
			}

			echo '<p>Для начала работы с консультантом нужно проделать два шага:</p>';

			echo '<p>1. Добавьте в шаблон DIAFAN.CMS (<i>themes/site.php</i>) шаблонный тег<br> <code><span style="color: #000000"><span style="color: #0000BB">&lt;insert</span> <span style="color: #007700">name=</span><span style="color: #DD0000">&quot;show_block&quot;</span> <span style="color: #007700">module=</span><span style="color: #DD0000">&quot;consultant&quot</span><span style="color: #0000BB">&gt;</span></span></code>.</p>';

			echo '<p>2. Настройте внешний вид консультанта на сайте <a href="https://www.jivosite.ru?partner_id=1936&lang=ru&pricelist_id=5" target="_blank">ссылке</a>, используя свой логин и пароль.</p>';
		}
		echo '</div>';
	}

	/**
	 * Регистрация в системе
	 * @return void
	 */
	public function edit_variable_email($value, $values)
	{
		if(! empty($values["id"]))
			return;
		echo '<div class="unit">';
		echo '<div class="infofield">'.$this->diafan->_('E-mail').'</div>
		<input type="text" value="'.$value.'" name="jivosite_email">
		</div>';
	}

	/**
	 * Регистрация в системе
	 * @return void
	 */
	public function edit_variable_url($value, $values)
	{
		if(! empty($values["id"]))
			return;
		echo '<div class="unit">';
		echo '<div class="infofield">'.$this->diafan->_('Адрес сайта').'</div>
		<input type="text" value="'.$value.'" name="jivosite_url">
		</div>';
	}

	/**
	 * Регистрация в системе
	 * @return void
	 */
	public function edit_variable_password($value, $values)
	{
		if(! empty($values["id"]))
			return;
		echo '<div class="unit">';
		echo '<div class="infofield">'.$this->diafan->_('Пароль').'</div>
		<input type="text" value="'.$value.'" name="jivosite_password">
		</div>';
	}

	/**
	 * Регистрация в системе
	 * @return void
	 */
	public function edit_variable_name($value, $values)
	{
		if(! empty($values["id"]))
			return;
		echo '<div class="unit">';
		echo '<div class="infofield">'.$this->diafan->_('Ваше имя').'</div>
		<input type="text" value="'.$value.'" name="jivosite_name">
		</div>
		<p>Если у Вы уже зарегистрированы в системе, просто укажите:</p>';
	}

	/**
	 * Регистрация в системе JivoSite
	 *
	 * @return void
	 */
	public function save_variable_register()
	{
		if(! empty($_POST["jivosite_id"]))
			return;

		if(empty($_POST["jivosite_name"]) || empty($_POST["jivosite_email"]) || empty($_POST["jivosite_password"]) || empty($_POST["jivosite_url"]))
		{
			$this->diafan->redirect(BASE_PATH_HREF.'consultant/?consultant_jivosite_error=Заполните все поля.');
			return;
		}
		$result = $this->diafan->fast_request('http://user.diafan.ru/service/jivosite.php', array(
				"name" => $_POST["jivosite_name"],
				"email" => $_POST["jivosite_email"],
				"password" => $_POST["jivosite_password"],
				"url" => $_POST["jivosite_url"],
		), false, false, ( REQUEST_POST | REQUEST_ANSWER ));
		if(preg_match('/Error:(.*)/', $result, $m))
		{
			$this->diafan->redirect(BASE_PATH_HREF.'consultant/?consultant_jivosite_error='.$m[1]);
		}
		else
		{
			$_POST['jivosite_id'] = preg_replace('/[^0-9a-zA-Z]+/', '', $result);
		}
	}
}

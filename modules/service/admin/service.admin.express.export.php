<?php
/**
 * Администрирование экспорт записей базы данных
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
 * Service_admin_express_export
 */
class Service_admin_express_export extends Frame_admin
{
	/**
	 * @var string имя основного класса
	 */
	const CLASS_NAME = 'Service_admin_express';

	/**
	 * @var string базовый URL
	 */
	private $url = '';

	/**
	 * @var integer номер категории
	 */
	private $cat = false;

	/**
 	 * @var string разделитель поля в содержании CSV-экспорта (только один символ)
 	 */
 	private $csv_delimiter = ';';

	/**
 	 * @var string символ ограничителя поля в содержании CSV-экспорта (только один символ)
 	 */
 	private $csv_enclosure = '"';

	/**
 	 * @var string экранирующий символ в содержании CSV-экспорта (только один символ)
 	 */
 	private $csv_escape = '\\';

	/**
 	 * @var string кодировка содержания CSV-экспорта
 	 */
 	private $csv_encoding = 'cp1251';

	/**
 	 * @var boolean сжимать содержания CSV-экспорта
 	 */
 	private $csv_zip = false;

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		parent::__construct($diafan);
		$this->class_init();
	}

	/**
	 * Инициализация модуля
	 *
	 * @return void
	 */
	public function class_init()
	{
		$this->ajax = false;
		if (! empty($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == 'xmlhttprequest' || ! empty($_POST["ajax"]))
		{
			$this->ajax = true;
		}

		if(defined('IS_ADMIN') && IS_ADMIN)
		{
			$this->url = BASE_PATH_HREF.'service/express/';
			if(! $this->ajax) $_SESSION[self::CLASS_NAME]["mode_express_choice"] = 'export';

			$this->cat = ! empty($this->diafan->_route->cat) || $this->diafan->_route->cat === '0' ? (int) $this->diafan->_route->cat : false;

			if($modules = $this->diafan->_service->modules_express())
			{
				$this->cat = (! isset($modules[$this->cat - 1]) ? false : $this->cat);
			}
			elseif($this->cat !== 0) $this->cat = false;

			if(! $this->ajax)
			{
				if($this->cat === false)
				{
					$this->diafan->redirect($this->diafan->get_admin_url('cat', 'page', 'step')
						.'cat'.($this->cat === false ? (! $modules ? '0' : '1') : $this->cat).'/');
				}
				if(empty($_SESSION[self::CLASS_NAME]["cat_export_choice"]["cat"])
				|| $_SESSION[self::CLASS_NAME]["cat_export_choice"]["cat"] != $this->cat)
				{
					$_SESSION[self::CLASS_NAME]["cat_export_choice"]["cat"] = $this->cat;
					if(isset($_SESSION[self::CLASS_NAME]["cat_export_choice"]["desc"]))
					{
						unset($_SESSION[self::CLASS_NAME]["cat_export_choice"]["desc"]);
					}
				}
			}
		}

		// Пробуем снять лимит времени на исполнения скрипта
		//$this->diafan->set_time_limit();
		// Пробуем снять лимит на использование скриптом памяти
		//ini_set('memory_limit', '-1');	//ini_set('memory_limit', 4000 . 'M');

		$express_csv_encoding = $this->diafan->configmodules('express_csv_encoding', 'service');
		$this->csv_encoding = $express_csv_encoding ?: $this->csv_encoding;

		$this->csv_delimiter = isset($_POST["delimiter"]) ? $_POST["delimiter"] : $this->csv_delimiter;
		$this->csv_enclosure = isset($_POST["enclosure"]) ? $_POST["enclosure"] : $this->csv_enclosure;
		$this->csv_encoding = isset($_POST["encoding"]) ? $_POST["encoding"] : $this->csv_encoding;
		$this->csv_zip = isset($_REQUEST["zip"]) ?: $this->csv_zip;

		$this->class_action();
	}

	/**
	 * Обрабатывает полученные данные из формы
	 *
	 * @return void
	 */
	public function class_action()
	{
		if(! empty($_REQUEST["download"]))
		{
			Custom::inc('modules/service/service.express.inc.php');
			$object = new Service_express_inc($this->diafan);
			$object->export_download($this->csv_zip);
		}

		// принудительное снятие блокировки процесса
		if(isset($_REQUEST["no_busy"]))
		{
			$this->diafan->_service->busy(false, true);
			if(defined('IS_ADMIN') && IS_ADMIN)
			{
				if(isset($_GET["no_busy"]))
				{
					$this->diafan->redirect($this->diafan->get_admin_url('cat', 'page', 'step')
						.'cat'.($this->cat === false ? '0' : $this->cat).'/'
						.'step'.($this->step == 0 ? '1' : $this->step).'/');
				}
			}
		}
	}

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{

	}

	/**
	 * Выводит заголовок контента
	 *
	 * @return void
	 */
	public function show_h1()
	{
		$rewrite = $this->diafan->_admin->rewrite;
		$this->diafan->_admin->rewrite = 'service/express';
		parent::show_h1();
		$this->diafan->_admin->rewrite = $rewrite;
	}

	/**
	 * Выводит контент модуля
	 * @return void
	 */
	public function show()
	{
		$this->prepare_config();
		$this->show_content_h1();
		$this->show_mode_express();

		$modules = $this->diafan->_service->modules_express();
		if(empty($modules))
		{
			echo '<br />'.'<div class="error">'.$this->diafan->_('Не выявлено модулей доступных для экспорта/импорта. Настрока описания не доступна.').'</div>';
		}
		else $this->show_content();
	}

	/**
	 * Выводит заголовок контента
	 *
	 * @return void
	 */
	private function show_content_h1()
	{
		if($this->diafan->_admin->name != $this->diafan->_admin->title_module)
		{
			echo '<span class="head-box__unit">'.$this->diafan->_($this->diafan->_admin->name).'</span>';
		}
	}

	/**
	 * Выводит выбор режима импорта/экспорта записей
	 *
	 * @return void
	 */
	private function show_mode_express()
	{
		$import_cat = ''; $import_param = array();
		$export_cat = ''; $export_param = array();
		if(! empty($_SESSION[self::CLASS_NAME]["cat_import_choice"])
		&& is_array($_SESSION[self::CLASS_NAME]["cat_import_choice"]))
		{
			if(! empty($_SESSION[self::CLASS_NAME]["cat_import_choice"]["cat"]))
			{
				// $import_cat = 'cat'.(string) $_SESSION[self::CLASS_NAME]["cat_import_choice"]["cat"].'/';
				// $import_cat .= 'step2'.'/'
				// if(! empty($_SESSION[self::CLASS_NAME]["cat_import_choice"]["desc"]))
				// {
				// 	$import_param = array("cat" => (string) $_SESSION[self::CLASS_NAME]["cat_import_choice"]["desc"]);
				// }
			}
		}
		if(! empty($_SESSION[self::CLASS_NAME]["cat_export_choice"])
		&& is_array($_SESSION[self::CLASS_NAME]["cat_export_choice"]))
		{
			if(! empty($_SESSION[self::CLASS_NAME]["cat_export_choice"]["cat"]))
			{
				$export_cat = 'cat'.(string) $_SESSION[self::CLASS_NAME]["cat_export_choice"]["cat"].'/';
				if(! empty($_SESSION[self::CLASS_NAME]["cat_export_choice"]["desc"]))
				{
					$export_param = array("cat" => (string) $_SESSION[self::CLASS_NAME]["cat_export_choice"]["desc"]);
				}
			}
		}
		$import_url = $this->url.'import/'.$import_cat;
		if($import_param)
		{
			$import_url = $this->diafan->params_append($import_url, $import_param);
		}
		$export_url = $this->url.'export/'.$export_cat;
		if($export_param)
		{
			$export_url = $this->diafan->params_append($export_url, $export_param);
		}
		echo '
		<div class="tabs" id="mode_express">
			<a href="'.$import_url.'" class="tabs__item">'.$this->diafan->_('Импорт').'</a>
			<a href="'.$export_url.'" class="tabs__item tabs__item_active">'.$this->diafan->_('Экспорт').'</a>
			<a href="'.$this->url.'fields/'.'" class="tabs__item">'.$this->diafan->_('Сохраненные импорт/экспорт').'</a>
		</div>';
	}

	/**
	 * Выводит контент экспорта записей
	 *
	 * @return void
	 */
	private function show_content()
	{
		if(defined('IS_DEMO') && IS_DEMO)
		{
			echo '
			<div class="content__left content__left_full">';
			echo '
				<br />';
			echo '
				<div class="error">'.$this->diafan->_('не доступно в демонстрационном режиме').'</div>';
			echo '
				<br />';
			echo '
			</div>';
			return;
		}

		// проверка на наличие блокировки процесса
		if($this->diafan->_service->busy())
		{
			echo '
			<div class="content__left content__left_full">';
			echo '
				<br />
				<div class="commentary">'
					.$this->diafan->_('Импорт %sзаблокирован%s другим пользователем.%sДождитесь окончания.%sПросто %sперезагрузите страницу%s через некоторое время.%sЕсли к тому моменту импорт будет разблокирован, Вы сможете продолжить работу.', '<b>', '</b>', ' ', '<br />', '<a href="#" onclick="window.location.href=document.location; return false;">', '</a>', ' ')
					.'<br /><br />'
					.$this->diafan->_('Если длительное время блокировка не снимается и Вы уверены, что другие пользователи или cron не инициировали импорт, то, возможно, во время выполнения процесса произошла ошибка, которая препятствовала снятию блокировки процесса. В таком случае Вы можете %sпринудительно снять блокировку%s.', '<a href="'.$this->diafan->get_admin_url('cat', 'page', 'step').'cat'.$this->cat.'/'.'?no_busy'.'">', '</a>')
				.'</div>
				<br />';
			echo'
			</div>';
			return;
		}

		echo '
		<div class="content__left content__left_full">';
		echo '<h2>'.$this->diafan->_('Основная настройка').'</h2>';
		$this->show_export_download_file();
		echo'
		</div>';

		echo '
		<br />';

		echo '
		<div id="export_init" class="content__left content__left_full">';
		echo '<a name="export"></a>';
		echo '<h2>'.$this->diafan->_('Экспорт записей').'</h2>';
		$this->show_export_init();
		echo'
		</div>';
	}

	/**
	 * Выводит форму загрузки файла для экспорта записей
	 *
	 * @return void
	 */
	private function show_export_download_file()
	{
		echo '
			<div class="box box_height">';

		echo '
			<form id="form_express_export" class="ajax" action="" method="POST">
				<input type="hidden" name="check_hash_user" value="'.$this->diafan->_users->get_hash().'">';

		Custom::inc('adm/includes/edit.php');
		$object = new Edit_admin($this->diafan);
		$modules = $this->diafan->_service->modules_express();
		$cat = $this->cat - 1;

		// modules
		$key = "modules"; $name = $this->diafan->_("Выберите модуль");
		$value = isset($modules[$cat]["name"]) ? $modules[$cat]["name"] : '';
		$help = "Выберите модуль, из которого нужно экспортировать данные. Модули, отсутствующие в списке, либо не установлены в разделе «Модули и БД», либо не поддерживают экспорт записей.";
		$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
		$disabled = false;
		$options = array();
		foreach($modules as $module)
		{
			$options[$module["module_name"]] = isset($module["title"]) ? $module["title"] : $module["module_name"];
		}
		$attr = $class = "";
		$object->show_table_tr_select($key, $name, $value, $help, $disabled, $options, $attr, $class);


		if(! $modules || ! isset($modules[$cat]))
		{
			return;
		}

		// TO_DO: единообразная очередность полей для таблицы {service_express_fields_category} - ORDER BY sort ASC, id ASC
		if($cats = DB::query_fetch_key("SELECT id, name, module_name, site_id, cat_id, menu_cat_id, type, delete_items, add_new_items, act_items, header, sub_delimiter, count_part FROM {%s_category} WHERE trash='0' AND module_name='%s' ORDER BY sort ASC, id ASC", 'service_express_fields', $modules[$cat]["module_name"], "id"))
		{
			$cat_id = $this->diafan->filter($_GET, 'integer', 'cat', 0);
			$cat_id = array_key_exists($cat_id, $cats) ? $cat_id : key($cats);
		}
		else $cat_id = 0;

		// fields_cat_id
		if(! empty($cats))
		{
			$key = "fields_cat_id"; $name = $this->diafan->_("Выберите правило экспорта");
			$value = $cat_id;
			$help = "Выберите сохраненное правило экспорта записей или создайте новое.";
			$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>'
				.' <i class="tooltip fa fa-gear" title="'.$this->diafan->_('Настроить параметры экспорта').'"></i>';
			$disabled = false;
			$options = $cats;
			$attr = 'unit_id="fields_cat_param"'; $class = "box_toggle";
			$object->show_table_tr_select($key, $name, $value, $help, $disabled, $options, $attr, $class);
		}
		else
		{
			echo '
				<div class="unit box_toggle" id="fields_cat_id">'
					.'<div class="error">'
						.$this->diafan->_('Сначала нужно создать правила для экспорта %sв разделе "Сохраненные импорт/экспорт"%s.', '<a href="'.$this->url.'fields/'.'">', '</a>')
					.'</div>'
				.'</div>';
		}

		echo '
				<a id="fields_cat_edit" class="btn btn_blue btn_small edit"'.(! $cat_id ? ' hide' : '').'" href="'.$this->url.'fields/'.(! empty($cats) ? 'cat'.$cat_id.'/' : '').'" title="'.(! empty($cats) ? $this->diafan->_("Редактировать описание импорта/экспорта") : $this->diafan->_("Добавить описание импорта/экспорта")).'">'.(! empty($cats) ? $this->diafan->_("Изменить правило экспорта") : $this->diafan->_("Добавить правило экспорта")).'</a>';

		echo '
				<div id="fields_cat_param" class="hide">';

		// delimiter
		$key = "delimiter"; $name = $this->diafan->_("Разделитель данных в строке");
		$value = $this->csv_delimiter;
		$help = "Разделитель ячеек в строке файла CSV. По умолчанию ;";
		$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
		$disabled = false;
		$attr = $class = "";
		$maxlength = 1;
		$object->show_table_tr_text($key, $name, $value, $help, $disabled, $attr, $class, $maxlength);

		// enclosure
		$key = "enclosure"; $name = $this->diafan->_("Ограничитель данных в строке");
		$value = $this->csv_enclosure;
		$help = "Ограничитель ячеек в строке файла CSV. По умолчанию \"";
		$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
		$disabled = false;
		$attr = $class = "";
		$maxlength = 1;
		$object->show_table_tr_text($key, $name, $value, $help, $disabled, $attr, $class, $maxlength);

		// encoding
		$key = "encoding"; $name = $this->diafan->_("Кодировка");
		$value = $this->csv_encoding;
		$options = array(
			'cp1251' => 'cp1251',
			'utf8'   => 'utf8',
		);
		$help = "Кодировка данных в файле CSV. Часто cp1251 или utf8. По умолчанию из Excell файлы CSV выходят в кодировке cp1251";
		$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
		$disabled = false;
		$attr = $class = "";
		$object->show_table_tr_select($key, $name, $value, $help, $disabled, $options, $attr, $class);

		// zip
		$key = "zip"; $name = $this->diafan->_("Сжимать результат экспорта");
		$value = $this->csv_zip;
		$help = "Результат экспорта в виде файла ZIP-архива. По умолчанию без зжатия";
		$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
		$disabled = false;
		$attr = $class = "";
		$object->show_table_tr_checkbox($key, $name, $value, $help, $disabled, $attr, $class);

		echo '
				</div>';

		unset($object);

		echo '
			</form>';

		echo '
			</div>';
	}

	/**
	 * Выводит форму инициализации для экспорта записей
	 *
	 * @return void
	 */
	private function show_export_init()
	{
		echo '
		<p class="after-action-box">'.$this->diafan->_('Экспорт будет проводиться согласно полям, указанным в сохраненных правилах экспорта. Вы можете самостоятельно создать необходимую структуру экспорта. Например, Вам нужно экспортировать товары в таблицу с тремя колонками: «товар», «цена», «описание». Тогда создайте новое правило экспорта с полями «Название товара», «Цена», «Полное описание».').'</p>';
		echo '
		<div class="box box_height">';
		echo '
			<a name="express_export"></a>
			<p>'.$this->diafan->_("По результатам экспорта записей будет сформирован файл в формате CSV.").'</p>';
		echo '
			<button id="express_button" class="btn btn_blue btn_small download" default="'.$this->diafan->_("Экспортировать").'">'.$this->diafan->_("Экспортировать").'</button>
		</div>';
	}
}

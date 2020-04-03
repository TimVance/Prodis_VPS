<?php
/**
 * Администрирование импорт записей базы данных
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
 * Service_admin_express_import
 */
class Service_admin_express_import extends Frame_admin
{
	/**
	 * @var string имя основного класса
	 */
	const CLASS_NAME = 'Service_admin_express';

	/**
	 * @var string имя таблицы базы данных без префикса, содержащей импортируемые данные
	 */
	const TABLE_NAME = 'service_express_import_elements';

	/**
	 * @var string префикс полей таблицы базы данных, содержащих импортируемые данные
	 */
	const COLUMN_NAME = 'col_';

	/**
	 * @var integer максимальное количество колонок в файле импорта
	 * лимит: 4096 столбцов на таблицу (1017 с InnoDB), нет ограничений на количество таблиц (4 миллиарда с InnoDB)
	 */
	const MAX_COLUMN = 1014; // 1017 - 3 служебных поля = 1014

	/**
	 * @var integer минимальное количество строк в файле XML
	 */
	const XLS_MIN_ROWS = 1;

	/**
	 * @var integer максимальное количество строк в файле XML
	 */
	const XLS_MAX_ROWS = 65536;

	/**
	 * @var string базовый URL
	 */
	private $url = '';

	/**
	 * @var integer номер категории
	 */
	private $cat = false;

	/**
	 * @var integer номер шага
	 */
	private $step = false;

	/**
	 * @var string путь до временной директории относительно корня сайта
	 */
	private $dir_path = 'tmp/express';

	/**
	 * @var array массив поддерживаемых расширений файлов
	 */
	public $extensions = array(
		'csv' => array(
			'title' => 'Текстовый табличный формат',
			'text' => 'По умолчанию кодировка cp1251',
		),
		'xml' => array(
			'title' => '+YML',
			'text' => 'Иерархичный формат, используемый также Яндекс.Маркетом',
		),
		'xls' => array(
			'title' => 'Excel',
			'text' => 'Повышенные требования к ресурсам хостинга.',
		),
		'xlsx' => array(
			'title' => 'Excel с 2010 года',
			'text' => 'Загружайте без кодов макросов и листов.',
		),
		'ods' => array(
			'title' => 'Универсальный формат таблиц',
			'text' => 'Для различных офисных приложений.',
		),
	);

	/**
	 * @var resource ссылка на файл импорта
	 */
	private $handle;

	/**
 	 * @var integer количество записей файла импорта, обрабатываемых за одну итерацию
 	 */
 	private $count_part = 1000;

	/**
	 * @var integer частичная загрузка записей перед импортом (false - загружаются все записи)
	 */
	private $preview_count = 3;

	/**
 	 * @var string разделитель поля в содержании CSV-импорта (только один символ)
 	 */
 	private $csv_delimiter = ';';

	/**
 	 * @var string символ ограничителя поля в содержании CSV-импорта (только один символ)
 	 */
 	private $csv_enclosure = '"';

	/**
 	 * @var string экранирующий символ в содержании CSV-импорта (только один символ)
 	 */
 	private $csv_escape = '\\';

	/**
 	 * @var string кодировка содержания CSV-импорта
 	 */
 	private $csv_encoding = 'cp1251';

	/**
	 * Определяет переменные
	 *
	 * @param string $name название переменной
	 * @return mixed
	 */
	public function __get($name)
	{
		if (! isset($this->cache["var"][$name]))
		{
			switch($name)
			{
				case 'errors':      // массив ошибок
					$this->cache["var"][$name] = isset($_SESSION[self::CLASS_NAME][$name]) ? $_SESSION[self::CLASS_NAME][$name] : array();
					break;

				case 'files':       // массив загружаемых файлов
				case 'defer_files': // массив файлов для отложенной загрузки
					$cache_meta = array("name" => $name, "prefix" => "admin_express_import");
					$value = $this->diafan->_cache->get($cache_meta, 'service', CACHE_GLOBAL);
					$this->cache["var"][$name] = $value ?: array();
					break;

				case 'module_name': // выбранный модуль при загрузке файла импорта
				case 'delimiter':   // разделитель поля в содержании CSV-импорта
				case 'enclosure':   // символ ограничителя поля в содержании CSV-импорта
				case 'encoding':    // кодировка содержания CSV-импорта
					$this->cache["var"][$name] = isset($_SESSION[self::CLASS_NAME][$name]) ? $_SESSION[self::CLASS_NAME][$name] : false;
					break;

				case 'ftell':          // текущее смещение в файле
				case 'part':           // общее количество прочитанных строк
				case 'table_elements': // текущее количество с учетом служебных колонок в таблице импорта
				case 'file_name':      // выбранный файл для загрузки
					$cache_meta = array("name" => $name, "prefix" => "admin_express_import");
					$value = $this->diafan->_cache->get($cache_meta, 'service', CACHE_GLOBAL);
					$this->cache["var"][$name] = $value ?: false;
					break;

				default:
					$this->cache["var"][$name] = null;
					break;
			}
		}
		return $this->cache["var"][$name];
	}

	/**
	 * Сохраняет переменные
	 *
	 * @param string $name название переменной
	 * @param mixed $value значение переменной
	 * @return void
	 */
	public function __set($name, $value)
	{
		switch($name)
		{
			case 'errors': // TO_DO: значение сохраняется в массив
				if(is_null($value))
				{
					if(isset($_SESSION[self::CLASS_NAME][$name])) unset($_SESSION[self::CLASS_NAME][$name]);
					if(isset($this->cache["var"][$name])) unset($this->cache["var"][$name]);
				}
				else
				{
					$array = $this->$name;
					if(! is_array($array)) $array = array();
					$array[] = $value;
					$this->cache["var"][$name] = $_SESSION[self::CLASS_NAME][$name] = $array;
				}
				break;

			case 'files':       // TO_DO: при удаление значения удаляется и файл, тоже при наличии ключа "delete"
			case 'defer_files': // TO_DO: при удаление значения удаляется и файл, тоже или при наличии ключа "delete"
				$cache_meta = array("name" => $name, "prefix" => "admin_express_import");
				if(is_null($value))
				{
					$array = $this->$name;
					if(! is_array($array)) $array = array();
					foreach ($array as $key => $val)
					{
						if(isset($val["file_path"]) && file_exists($val["file_path"])) unlink($val["file_path"]);
						unset($array[$key]);
					}
					$this->diafan->_cache->save('', $cache_meta, 'service', CACHE_GLOBAL);
					if(isset($this->cache["var"][$name])) unset($this->cache["var"][$name]);
				}
				else
				{
					$array = $this->$name;
					if(! is_array($array)) $array = array();
					if(! isset($value["file_path"]))
					{
						if (empty($value["delete"]))
						{
							$array[] = $value;
						}
					}
					else
					{
						if(! empty($value["delete"]))
						{
							if(file_exists($value["file_path"])) unlink($value["file_path"]);
							if(array_key_exists($value["file_path"], $array)) unset($array[$value["file_path"]]);
						}
						else
						{
							$array[$value["file_path"]] = $value;
						}
					}
					if(empty($array))
					{
						$this->diafan->_cache->save('', $cache_meta, 'service', CACHE_GLOBAL);
						if(isset($this->cache["var"][$name])) unset($this->cache["var"][$name]);
					}
					else
					{
						$this->diafan->_cache->save($array, $cache_meta, 'service', CACHE_GLOBAL);
						$this->cache["var"][$name] = $array;
					}
				}
				break;

			case 'module_name':
			case 'delimiter':
			case 'enclosure':
			case 'encoding':
				if(empty($value))
				{
					if(isset($_SESSION[self::CLASS_NAME][$name])) unset($_SESSION[self::CLASS_NAME][$name]);
					if(isset($this->cache["var"][$name])) unset($this->cache["var"][$name]);
				}
				else
				{
					$this->cache["var"][$name] = $_SESSION[self::CLASS_NAME][$name] = $value;
				}
				break;

			case 'ftell':
			case 'part':
			case 'table_elements':
			case 'module_name':
			case 'file_name':
				$cache_meta = array("name" => $name, "prefix" => "admin_express_import");
				$this->diafan->_cache->save($value, $cache_meta, 'service', CACHE_GLOBAL);
				if(empty($value))
				{
					if(isset($this->cache["var"][$name])) unset($this->cache["var"][$name]);
				}
				else
				{
					$this->cache["var"][$name] = $value;
				}
				break;

			default:
				$this->cache["var"][$name] = $value;
				break;
		}
	}

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
	 * Деструктор класса
	 *
	 * @return void
	 */
	public function __destruct()
	{
		if (! empty($_POST["file_link"]) && ! empty($_FILES['file']['tmp_name']) && file_exists($_FILES['file']['tmp_name']))
		{
			unlink($_FILES['file']['tmp_name']);
		}
	}

	/**
	 * Инициализация класса
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

		$express_csv_encoding = $this->diafan->configmodules('express_csv_encoding', 'service');
		$this->csv_encoding = $express_csv_encoding ?: $this->csv_encoding;

		$this->delimiter = isset($_POST["delimiter"]) ? $_POST["delimiter"] : ($this->delimiter ?: $this->csv_delimiter);
		$this->enclosure = isset($_POST["enclosure"]) ? $_POST["enclosure"] : ($this->enclosure ?: $this->csv_enclosure);
		$this->encoding = isset($_POST["encoding"]) ? $_POST["encoding"] : ($this->encoding ?: $this->csv_encoding);

		if(defined('IS_ADMIN') && IS_ADMIN)
		{
			$this->url = BASE_PATH_HREF.'service/express/';
			if(! $this->ajax) $_SESSION[self::CLASS_NAME]["mode_express_choice"] = 'import';

			$tables = $this->diafan->_service->get_tables(false, true);
			$this->cat = ! empty($this->diafan->_route->cat) || $this->diafan->_route->cat === '0' ? (int) $this->diafan->_route->cat : false;
			$this->step = ! empty($this->diafan->_route->step) || $this->diafan->_route->step === '0' ? (int) $this->diafan->_route->step : false;

			if($modules = $this->diafan->_service->modules_express())
			{
				$this->cat = (! isset($modules[$this->cat - 1]) ? false : $this->cat);
			}
			elseif($this->cat !== 0) $this->cat = false;

			$this->step = $this->step != 1 && empty($tables[self::TABLE_NAME]) ? false : $this->step;

			if(! $this->ajax)
			{
				if($this->cat === false || $this->step == 0)
				{
					$this->diafan->redirect($this->diafan->get_admin_url('cat', 'page', 'step')
						.'cat'.($this->cat === false ? (! $modules ? '0' : '1') : $this->cat).'/'
						.'step'.($this->step == 0 ? '1' : $this->step).'/');
				}
				if(empty($_SESSION[self::CLASS_NAME]["cat_import_choice"]["cat"])
				|| $_SESSION[self::CLASS_NAME]["cat_import_choice"]["cat"] != $this->cat)
				{
					$_SESSION[self::CLASS_NAME]["cat_import_choice"]["cat"] = $this->cat;
					if(isset($_SESSION[self::CLASS_NAME]["cat_import_choice"]["desc"]))
					{
						unset($_SESSION[self::CLASS_NAME]["cat_import_choice"]["desc"]);
					}
				}
			}

			$this->class_action();
		}

		// Пробуем снять лимит времени на исполнения скрипта
		// $this->diafan->set_time_limit();
		// Пробуем снять лимит на использование скриптом памяти
		//ini_set('memory_limit', '-1');	//ini_set('memory_limit', 4000 . 'M');

		if($count_part = $this->diafan->configmodules('express_count_part', 'service'))
		{
			$this->count_part = $count_part;
		}
		$preview_enable = $this->diafan->configmodules('express_preview_enable', 'service');
		$this->preview_count = $preview_enable ? $this->preview_count : false;
	}

	/**
	 * Обрабатывает полученные данные из формы
	 *
	 * @return void
	 */
	public function class_action()
	{
		if((isset($_FILES['file']) || ! empty($_POST["file_link"])) && ! empty($_POST["load_file"]) && ! empty($_POST["ajax"]))
		{
			$this->upload_file($this->ajax);
		}

		if(isset($_REQUEST["log"]))
		{
			Custom::inc('modules/service/service.express.inc.php');
			$object = new Service_express_inc($this->diafan);
			$object->import_download_log();
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
	 *
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
			<a href="'.$import_url.'" class="tabs__item tabs__item_active">'.$this->diafan->_('Импорт').'</a>
			<a href="'.$export_url.'" class="tabs__item">'.$this->diafan->_('Экспорт').'</a>
			<a href="'.$this->url.'fields/'.'" class="tabs__item">'.$this->diafan->_('Сохраненные импорт/экспорт').'</a>
		</div>';
	}

	/**
	 * Выводит контент импорта записей
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
					.$this->diafan->_('Если длительное время блокировка не снимается и Вы уверены, что другие пользователи или cron не инициировали импорт, то, возможно, во время выполнения процесса произошла ошибка, которая препятствовала снятию блокировки процесса. В таком случае Вы можете %sпринудительно снять блокировку%s.', '<a href="'.$this->diafan->get_admin_url('cat', 'page', 'step').'cat'.$this->cat.'/'.'step'.$this->step.'/'.'?no_busy'.'">', '</a>')
				.'</div>
				<br />';
			echo'
			</div>';
			return;
		}

		if($this->step == 1)
		{
			echo '
			<div class="content__left content__left_full">';
			echo '<h2>'.$this->diafan->_('Основная настройка').'</h2>';
			//$this->show_import_select_module();
			$this->show_import_upload_file();
			echo'
			</div>';

			echo '
			<br />';

			echo '
			<div class="content__left content__left_full">';
			echo '<h2>'.$this->diafan->_('Для информации').'</h2>';
			$this->show_import_info_block();
			echo'
			</div>';
		}
		elseif($this->step == 2)
		{
			$tables = $this->diafan->_service->get_tables(false, true);
			$cat = $this->cat - 1;
			if(empty($tables[self::TABLE_NAME]) || ! DB::query_result("SELECT id FROM {".self::TABLE_NAME."} WHERE trash='0' LIMIT 1")
			|| (! $modules = $this->diafan->_service->modules_express()) || ! isset($modules[$cat]))
			{
				$this->diafan->redirect($this->diafan->get_admin_url('cat', 'page', 'step').'cat'.$this->cat.'/'.'step1/');
				return false;
			}

			echo '
			<div id="import_description" class="content__left content__left_full">';
			echo '<h2>'.$this->diafan->_('Описание импорта').'</h2>';
			$this->show_import_description();
			echo'
			</div>';

			echo '
			<br />';

			echo '
			<div id="import_init" class="content__left content__left_full">';
			echo '<h2>'.$this->diafan->_('Импорт записей').'</h2>';
			$this->show_import_init();
			echo'
			</div>';
		}
	}

	/**
	 * Выводит форму выбора модуля для импорта записей
	 *
	 * @return void
	 */
	private function show_import_select_module()
	{
		$modules = $this->diafan->_service->modules_express();

		echo '
			<form class="box box_install box_height" action="'.URL.'step'.$this->step.'/'.'" method="POST">
				<input type="hidden" name="action" value="">
				<input type="hidden" name="id" value="true">';
		echo '
			<div>
				<div class="infofield">'.$this->diafan->_('Выберите модуль').': <i class="tooltip fa fa-question-circle" title="'.$this->diafan->_('Установленные модули.').'"></i></div>
				<p>'.$this->diafan->_('Отметьте модуль, в который нужно импортировать записи. Модули, отсутствющие в списке, либо не установлены в разделе %s«Модули и БД»%s, либо не поддерживают импорт записей.', '<a href="'.BASE_PATH_HREF.'service/">', '</a>').'</p>
				<input type="hidden" name="check_hash_user" value="'.$this->diafan->_users->get_hash().'">';
		if(empty($modules))
		{
			if(! empty($_POST["modules"])) unset($_POST["modules"]);
			if($this->cat > 0)
			{
				$this->diafan->redirect($this->diafan->get_admin_url('cat', 'page', 'step').'cat0/'.'step'.$this->step.'/');
			}
		}
		else
		{
			$module_names = $this->diafan->array_column($modules, "name");
			if(! empty($_POST["modules"]))
			{
				$key = array_search($_POST["modules"], $module_names);
				if($key === false)
				{
					unset($_POST["modules"]);
					$this->diafan->redirect($this->diafan->get_admin_url('cat', 'page', 'step').'cat1/'.'step'.$this->step.'/');
				}
				elseif($this->cat != ($key + 1))
				{
					$this->diafan->redirect($this->diafan->get_admin_url('cat', 'page', 'step').'cat'.($key + 1).'/'.'step'.$this->step.'/');
				}
			}
			elseif($this->cat < 1)
			{
				$this->diafan->redirect($this->diafan->get_admin_url('cat', 'page', 'step').'cat1/'.'step'.$this->step.'/');
			}
			else
			{
				if($this->cat > count($module_names))
				{
					$this->diafan->redirect($this->diafan->get_admin_url('cat', 'page', 'step').'cat1/'.'step'.$this->step.'/');
				}
			}
		}
		$checked = false;
		foreach($modules as $key => $row)
		{
			$checked_attr = '';
			if(! $checked)
			{
				if($checked = $this->cat == ($key + 1))
				{
					$checked_attr = ' checked';
				}
			}
			echo '
				<input type="radio" name="modules" value="'.$row["name"].'" id="input_modules_'.$row["name"].'" '.$checked_attr.' onchange="this.form.submit();"><label for="input_modules_'.$row["name"].'"><b>'.$this->diafan->_($row["title"]).'</b></label>';
		}
		echo '
			</div>';
		echo '
		</form>';
	}

	/**
	 * Выводит форму загрузки файла для импорта записей
	 *
	 * @return void
	 */
	private function show_import_upload_file()
	{
		$defer_read = FALSE;
		$files = ! $defer_read ? 'files' : 'defer_files';

		$import_detect = $this->import_action();

		echo '
		<div class="box'.($import_detect & IMPORT_ALL ? ' box_half' : '').' box_height">';

		$this->upload_file();
		$this->read_file($defer_read);

		if(count($this->$files) > 0)
		{
			$array_files = $this->$files; $file = reset($array_files);
			echo '<div class="commentary">'.$this->diafan->_("Загрузка файла <b>%s</b>: прочитано записей <b>%s</b>.", (isset($file["basename"]) ? $file["basename"] : ''), (int) $this->part).'<br />'.$this->diafan->_('Дождитесь окончания процесса ...').'<img src="'.BASE_PATH.'adm/img/loading.gif">'.'</div>';
		}
		else
		{
			if(count($this->errors) > 0)
			{
				echo '<div class="error">'.$this->diafan->_("Выявлены ошибки во время загрузки данных импорта").':<br />'.implode('<br />', $this->errors).'</div>';
				$this->errors = NULL;
			}
		}

		if(! $this->ftell || ! count($this->$files))
		{
			echo '
			<form id="import_upload_file" action="" enctype="multipart/form-data" method="POST">
				<input type="hidden" name="load_file" value="true">
				<input type="hidden" name="check_hash_user" value="'.$this->diafan->_users->get_hash().'">';

			Custom::inc('adm/includes/edit.php');
			$object = new Edit_admin($this->diafan);

			// modules
			$key = "modules"; $name = $this->diafan->_("Выберите модуль");
			$value = $this->cat;
			$help = "Выберите модуль, в который нужно импортировать записи. Модули, отсутствющие в списке, либо не установлены в разделе «Модули и БД», либо не поддерживают импорт записей.";
			$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
			$disabled = false;
			$options = array(); $modules = $this->diafan->_service->modules_express(); $index = 0;
			foreach($modules as $module)
			{
				$options[++$index] = isset($module["title"]) ? $module["title"] : (isset($module["name"]) ? $module["name"] : '');
			}
			$attr = $class = "";
			$object->show_table_tr_select($key, $name, $value, $help, $disabled, $options, $attr, $class);

			//echo '<br />';

			// extensions
			echo '
				<div class="unit" id="extensions">';
			echo'
					<div class="infofield">'.$this->diafan->_('Укажите формат файла').' <i class="tooltip fa fa-question-circle" title="'.$this->diafan->_('Поддерживаются следующие форматы данных: %s.', implode(", ", $this->diafan->array_column($this->extensions, "title"))).' '.$this->diafan->_('А также сжатие данных в ZIP-архив.').'"></i></div>';

			$checked = false;
			foreach($this->extensions as $key => $value)
			{
				$checked_attr = '';
				if(! $checked)
				{
					if($checked = $key == 'csv')
					{
						$checked_attr = ' checked';
					}
				}
				switch ($key)
				{
					case 'csv':
						$class = ' class="box_toggle" unit_id="csv_param"';
						$tooltip = ' <i class="tooltip fa fa-gear" title="'.$this->diafan->_('Настроить параметры для файлов в формате CSV').'"></i>';
						break;

					default:
						$class = $tooltip = '';
						break;
				}
				$onchange = ' onchange="$(this.form).find(\'.extensions_text\').hide(); $(this).parent().next(\'.extensions_text\').show();"';
				echo '
					<div class="block_item">
						<input type="radio" name="extensions" value="'.$key.'" id="input_extensions_'.$key.'" '.$checked_attr.$onchange.' />'
						.'<label for="input_extensions_'.$key.'"'.$class.'>'
						.strtoupper($key)
						.$tooltip
						.'</label>'
					.'</div>';
				echo '
					<div class="extensions_text'.($checked_attr ? '' : '  hide').'">';
				if(! empty($value["text"]))
				{
					echo (! empty($value["title"]) ? $this->diafan->_($value["title"]) : strtoupper($key))
						.' — '.$this->diafan->_($value["text"]);
				}
				else echo $this->diafan->_($value["title"]);
				echo '
					</div>';
				if($key == 'csv')
				{
					echo '
					<div id="csv_param" class="hide">';

					// delimiter
					$key = "delimiter"; $name = $this->diafan->_("Разделитель данных в строке");
					$value = $this->csv_delimiter;
					$help = "Разделитель ячеек в строке файла CSV. По умолчанию ;";
					$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
					$disabled = !! $this->ftell;
					$attr = $class = "";
					$maxlength = 1;
					$object->show_table_tr_text($key, $name, $value, $help, $disabled, $attr, $class, $maxlength);

					// enclosure
					$key = "enclosure"; $name = $this->diafan->_("Ограничитель данных в строке");
					$value = $this->csv_enclosure;
					$help = "Ограничитель ячеек в строке файла CSV. По умолчанию \"";
					$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
					$disabled = !! $this->ftell;
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
					$disabled = !! $this->ftell;
					$attr = $class = "";
					$object->show_table_tr_select($key, $name, $value, $help, $disabled, $options, $attr, $class);

					echo '
					</div>';
				}
			}
			echo '
				</div>';

			//echo '<br />';

			// file
			echo'
				<div class="unit" id="file">
				 <div class="infofield">'.$this->diafan->_('Выберите файл').' <i class="tooltip fa fa-question-circle" title="'
					.$this->diafan->_('Загрузите файл, записи которого необходимо импортировать.')
				.'"></i></div>';

			unset($object);

			echo '
					<input type="file" class="file" name="file">
				</div>
				<div class="div_file_link">
					<div class="infofield">'.$this->diafan->_('Или загрузите файл по ссылке').'</div>
					<input type="text" name="file_link" placeholder="http://">
				</div>
				<div>'.$this->diafan->_('Максимальный размер загружаемого файла %s.', ini_get('upload_max_filesize')).'</div>

				<div class="file_upload">
					<button id="btn_upload_file" class="btn btn_blue btn_small upload">'.$this->diafan->_('Загрузить').'</button>
					<img class="spinner_upload_file hide" src="'.BASE_PATH.'adm/img/loading.gif">
				</div>';
			echo '
			</form>';
		}
		echo '
		</div>';

		if($import_detect & IMPORT_ALL)
		{
			echo '
		<div class="box box_half box_height box_right">';
			echo '
			<br>
			<form method="post" action="'.$this->diafan->get_admin_url('cat', 'page', 'step').'cat'.$this->cat.'/'.'step'.$this->step.'/'.'">
			<input type="hidden" value="" name="import_action">
			'.$this->diafan->_('Результаты последнего импорта').': &nbsp; &nbsp;';
			if($import_detect & IMPORT_ACTIVE)
			{
				echo '<input type="submit" class="btn btn_small import_button" rel="act_import" value="'.$this->diafan->_('Показать на сайте').'" > &nbsp; &nbsp;';
			}
			if($import_detect & IMPORT_DEACTIVE)
			{
				echo '<input type="submit" class="btn btn_small import_button" rel="deact_import" value="'.$this->diafan->_('Скрыть на сайте').'" > &nbsp; &nbsp;';
			}
			echo '<input type="submit" class="btn btn_small import_button" rel="remove_import" value="'
		   .$this->diafan->_('Удалить').'" >
			</form>';
			echo '
		</div>';
		}
	}

	/**
	 * Выводит блок для информации
	 *
	 * @return void
	 */
	private function show_import_info_block()
	{
		echo '
	<div class="box box_height">';
		echo '<p>'.$this->diafan->_('Чтобы поставить импорт на cron, используйте URL').':<br />'
			.BASE_PATH.'service/express/client/?key=<b><i>YOUR_KEY</i></b>&cat=<b><i>DESCRIPTION_ID</i></b>'.'<br />'
			.$this->diafan->_('где %s - Ваш ключ (из %sнастроек%s), %s - номер %sописания импорта/экспорта%s.', 'YOUR_KEY', '<a href="'.BASE_PATH_HREF.'service/config/'.'">', '</a>', 'DESCRIPTION_ID', '<a href="'.BASE_PATH_HREF.'service/express/fields/'.'">', '</a>')
			.'</p>';
		echo '
	</div>';
	}

	/**
	 * Опереации с импортированными элементами
	 *
	 * @return binary
	 */
	private function import_action()
	{
		Custom::inc('modules/service/service.express.inc.php');
		$object = new Service_express_inc($this->diafan, 'service');
		$import_detect = $object->import_detect(0);
		unset($object);
		if((! $modules = $this->diafan->_service->modules_express()) || (empty($modules[$this->cat - 1]["name"])))
		{
			return $import_detect;
		}
		else $object = new Service_express_inc($this->diafan, $modules[$this->cat - 1]["name"]);
		$last_cat_id = $object->import_last_cat_id();
		if($last_cat_id)
		{
			if(! empty($_POST["import_action"]))
			{
				switch ($_POST["import_action"])
				{
					case 'act_import':
						$object->import_act($last_cat_id, true);
						break;

					case 'deact_import':
						$object->import_act($last_cat_id, false);
						break;

					case 'remove_import':
						$object->import_remove($last_cat_id);
						break;

					default:
						break;
				}
			}
			$import_detect = $object->import_detect($last_cat_id);
		}
		else $import_detect = 0;
		unset($object);

		return $import_detect;
	}

	/**
	 * Очищаем возможный мусор перед загрузкой
	 *
	 * @return void
	 */
	public function garbage_cleaning()
	{
		if(count($this->files) > 0) $this->files = null;
		$this->ftell = false;
		$this->part = 0;
		if(count($this->defer_files) > 0) $this->defer_files = null;
		$this->table_elements = false;
		if(count($this->errors) > 0) $this->errors = null;
		$this->module_name = false;
		$this->file_name = false;
	}

	/**
	 * Загрузка данных импорта
	 *
	 * @param boolean $ajax режим AJAX
	 * @return void
	 */
	private function upload_file($ajax = false)
	{
		if(empty($_POST["load_file"]) )
		{
			return;
		}
		// Прошел ли пользователь проверку идентификационного хэша
		if (! $this->diafan->_users->checked)
		{
			if(! $ajax)
			{
				$this->diafan->redirect(URL);
				return;
			}
			else
			{
				echo json_encode(array(
					"result" => "error",
					"redirect" => URL,
				));
				exit;
			}
		}

		// устанавливаем блокировку процесса
		if($this->diafan->_service->busy())
		{
			if(! $ajax) return false;
			else
			{
				echo json_encode(array(
					"result" => "error",
					"redirect" => URL,
				));
				exit;
			}
		}
		else $this->diafan->_service->busy(true);

		// очищаем возможный мусор
		$this->garbage_cleaning();

		// ссылка на файл
		if(! empty($_POST["file_link"]))
		{
			// $tmp = tempnam(ABSOLUTE_PATH.$this->dir_path."/", 'expressimport');
			$tmp = $this->dir_path.'/'.md5('expressimport'.$this->diafan->uid());
			// file_put_contents(ABSOLUTE_PATH.$tmp, '');
			File::save_file('', $tmp);
			$content = file_get_contents($_POST["file_link"]);
			if($content !== false && file_exists(ABSOLUTE_PATH.$tmp))
			{
				if($fp = fopen(ABSOLUTE_PATH.$tmp, "wb"))
				{
					fwrite($fp, $content);
					fclose($fp);
				}

				// определяем расширение загруженного по ссылке файла
				if($fp = @fopen(ABSOLUTE_PATH.$tmp, "r"))
				{
					$blob = fgets($fp, 5);
					fclose($fp);
					if(strpos($blob, 'Rar') !== false) $ext = 'rar';
					elseif(strpos($blob, 'PK') !== false) $ext = 'zip';
					else
					{
						$ext = (! empty($_POST["extensions"]) ? $_POST["extensions"] : '');
					}
				}
				else
				{
					$ext = (! empty($_POST["extensions"]) ? $_POST["extensions"] : '');
				}

				$_FILES['file'] = array(
					"name" => md5('expressimport'.$this->diafan->uid()).(! empty($ext) ? '.'.$ext : ''),
					"type" => "application/octet-stream",
					"tmp_name" => ABSOLUTE_PATH.$tmp,
					"error" => 0,
					"size" => filesize(ABSOLUTE_PATH.$tmp)
				);
			}
			else
			{
				if($content === false)
				{
					$this->errors = '- '.$this->diafan->_("ошибка чтения файла, указанного по ссылке: %s", $_POST["file_link"]);
				}
				if(! file_exists(ABSOLUTE_PATH.$tmp))
				{
					$this->errors = '- '.$this->diafan->_("ошибка при создании временного файла импорта на хостинге сайта");
				}
				// снимаем блокировку процесса
				$this->diafan->_service->busy(false);
				if(! $ajax) return;
			}
		}

		// загружаем файлы
		if (! empty($_FILES['file']) && ! empty($_FILES['file']['name']))
		{
			File::create_dir($this->dir_path, true);

			$filename = $_FILES['file']['tmp_name'];
			// TO_DO: $_FILES['file']['type'] == "application/zip" || "application/x-zip" || "application/x-zip-compressed" || "application/octet-stream" || "application/x-compress" || "application/x-compressed" || "multipart/x-zip" || etc.
			$fileinfo = pathinfo($_FILES['file']['name']);
			if($fileinfo['extension'] == 'zip')
			{
				if(class_exists('ZipArchive'))
				{
					$zip = new ZipArchive;
					if ($zip->open($filename) === true)
					{
						for($i = 0; $i < $zip->numFiles; $i++)
						{
							$tmp = $this->dir_path.'/'.md5('expressimport'.$this->diafan->uid());
							File::save_file($zip->getFromName($zip->getNameIndex($i)), $tmp);
							$fi = pathinfo($zip->getNameIndex($i));
							if(in_array($fi['extension'], array_keys($this->extensions)))
							{
								$this->files = array(
									"file_path" => $tmp,
									"basename" => $fi['basename'],
									"extension" => $fi['extension']
								);
							}
							else
							{
								$this->errors = '- '.$this->diafan->_("расширение файла <b>%s</b> не поддерживается", $fi['basename']);
								unlink($tmp);
							}
						}
						$zip->close();
					}
					else
					{
						$this->errors = '- '.$this->diafan->_("ошибка при чтении архивного файла");
						// снимаем блокировку процесса
						$this->diafan->_service->busy(false);
						if(! $ajax) return;
					}
				}
				else
				{
					$this->errors = '- '.$this->diafan->_('на сервере не установлено расширение для распоковки ZIP-архивов (перед загрузкой необходимо распаковать содержимое архива)');
					// снимаем блокировку процесса
					$this->diafan->_service->busy(false);
					if(! $ajax) return;
				}
			}
			elseif(in_array($fileinfo['extension'], array_keys($this->extensions)))
			{
				if(empty($_REQUEST["extensions"]) || strtolower($fileinfo['extension']) == strtolower($_REQUEST["extensions"]))
				{
					$tmp = $this->dir_path.'/'.md5('expressimport'.$this->diafan->uid());
					File::upload_file($filename, $tmp);
					$this->files = array(
						"file_path" => $tmp,
						"basename" => $fileinfo['basename'],
						"extension" => $fileinfo['extension']
					);
				}
				else
				{
					$this->errors = '- '.$this->diafan->_("расширение файла <b>%s</b> не совпадает с выбранным форматом файла <b>%s</b>", $fileinfo['basename'], strtoupper($_REQUEST["extensions"]));
				}
			}
			else
			{
				$this->errors = '- '.$this->diafan->_("расширение файла <b>%s</b> не поддерживается", $fileinfo['basename']);
			}
		}
		else
		{
			$this->errors = '- '.$this->diafan->_("проверьте файл");
			// снимаем блокировку процесса
			$this->diafan->_service->busy(false);
			if(! $ajax) return;
		}

		// запаминаем выбранный модуль
		if($module = $this->diafan->filter($_POST, 'integer', 'modules', 0))
		{
			$modules = $this->diafan->_service->modules_express(); $module--;
			if(! empty($modules[$module]["name"])) $this->module_name = $modules[$module]["name"];
		}
		// запаминаем имя загружаемого файла
		if ($_FILES['file'] && $_FILES['file']['name'])
		{
			$this->file_name = $_FILES['file']['name'];
		}

		// снимаем блокировку процесса
		$this->diafan->_service->busy(false);
		if(! $ajax) $this->diafan->redirect(URL);
		else
		{
			echo json_encode(array(
				"result" => (count($this->errors) > 0 ? "error" : "success"),
				"redirect" => URL,
			));
			exit;
		}
	}

	/**
	 * Чтение данных импорта
	 *
	 * @param boolean $defer_read чтение ранее отложенных файлов импорта
	 * @param boolean $ajax режим AJAX
	 * @return void
	 */
	public function read_file($defer_read = false, $ajax = false)
	{
		$files = ! $defer_read ? 'files' : 'defer_files';
		$this->preview_count = ! $defer_read ? $this->preview_count : false;
		$this->count_part = $this->preview_count ?: $this->count_part;
		if(count($this->$files) <= 0)
		{
			return;
		}

		// устанавливаем блокировку процесса
		if($this->diafan->_service->busy())
		{
			return false;
		}
		else $this->diafan->_service->busy(true);

		// создаем таблицу импорта
		if(! $this->table_elements)
		{
			DB::query("DROP TABLE IF EXISTS ".DB_PREFIX.self::TABLE_NAME);
			DB::query("CREATE TABLE {".self::TABLE_NAME."} (`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'идентификатор', `element_id` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'идентификатор из таблицы модуля', `trash` ENUM('0', '1') NOT NULL DEFAULT '0' COMMENT 'запись удалена в корзину: 0 - нет, 1 - да', PRIMARY KEY (id)) CHARSET=utf8 COMMENT 'Содержание импорта записей';");
			$tables = $this->diafan->_service->get_tables(false, true);
			if(! empty($tables[self::TABLE_NAME]))
			{
				$fileds = $tables[self::TABLE_NAME];
				$this->table_elements = count($fileds);
			}
		}

		// перебираем
		while(count($this->$files) > 0)
		{
			$array_files = $this->$files; $file = reset($array_files);
			if(isset($file["file_path"]) && file_exists(ABSOLUTE_PATH.$file["file_path"]))
			{
				$method = 'read_'.(isset($file['extension']) ? $file['extension'] : '');
				if(! isset($file["extension"]) || ! in_array($file['extension'], array_keys($this->extensions)) || ! method_exists(__CLASS__, $method))
				{
					$this->errors = '- '.$this->diafan->_("расширение файла <b>%s</b> не поддерживается", (isset($file['basename']) ? $file['basename'] : ''));
					$this->ftell = false;
				}
				else
				{
					if(empty($file["error"]))
					{
						// маркер ошибки чтения файла
						$file["error"] = true; $this->$files = $file;
						// читаем файл импорта
						if(! $this->$method($file))
						{
							$this->errors = '- '.$this->diafan->_("содержание файла <b>%s</b> не распознано", (isset($file['basename']) ? $file['basename'] : ''));
							$this->ftell = false;
						}
						else
						{
							// снимаем маркер ошибки чтения файла
							if(! empty($file["error"])) { unset($file["error"]); } $this->$files = $file;
						}
					}
					else
					{// при чтении файла ранее была зарегистрирована ошибка
						$this->errors = '- '.$this->diafan->_("содержание файла <b>%s</b> не распознано", (isset($file['basename']) ? $file['basename'] : ''));
						$this->ftell = false;
					}

					if(empty($file["error"]) && $this->preview_count)
					{
						$tmp = $this->dir_path.'/'.md5('expressimport'.$this->diafan->uid());
						File::upload_file($file["file_path"], $tmp);
						$this->defer_files = array(
							"file_path" => $tmp,
							"basename" => $file['basename'],
							"extension" => $file['extension']
						);
						$this->ftell = false;
					}
				}
			}
			else
			{
				$this->errors = '- '.$this->diafan->_("содержание файла <b>%s</b> не найдено", (isset($file['basename']) ? $file['basename'] : ''));
				$this->ftell = false;
			}

			if(! $this->ftell)
			{
				$file["delete"] = true; $this->$files = $file;
			}

			if(! $ajax) $this->reload_page_js(URL, true);
			break;
		}


		// завершаем чтение загруженного файла
		if(! $this->ftell && count($this->$files) <= 0)
		{
			// снимаем блокировку процесса
			$this->diafan->_service->busy(false);

			$module_name = $this->module_name;
			// очищаем возможный мусор
			$this->ftell = false;
			$this->part = 0;
			$this->table_elements = false;
			$this->module_name = false;

			if(! $ajax)
			{
				$tables = $this->diafan->_service->get_tables(false, true);
				if(! count($this->errors) && ! empty($tables[self::TABLE_NAME]))
				{
					$cat = $this->cat;
					if($module_name && $modules = $this->diafan->_service->modules_express())
					{
						$module_names = $this->diafan->array_column($modules, "name");
						$key = array_search($module_name, $module_names);
						if($key !== false) $cat = ++$key;
					}
					$this->diafan->redirect($this->diafan->get_admin_url('cat', 'page', 'step').'cat'.$cat.'/'.'step2/');
				}
				else $this->diafan->redirect(URL);
			}
			return;
		}
	}

	/**
	 * Чтение данных импорта
	 *
	 * @param array $file описание файла импорта
	 * @return void
	 */
	private function read_csv($file)
	{
		// текущее смещение в файле
		$ftell = $this->ftell; $this->ftell = false;
		// общее количество прочитанных строк
		$part = $this->part; $this->part = 0;
		// валидация файла импорта
		if(! is_array($file) || empty($file["file_path"]) || ! file_exists(ABSOLUTE_PATH.$file["file_path"]))
		{
			return false;
		}
		// путь до файла относительно корня сайта
		$file_path = $file["file_path"];

		$tables = $this->diafan->_service->get_tables(false, true);
		if(empty($tables[self::TABLE_NAME]))
		{
			return false;
		}
		// определяем текущее количество без учета служебных колонок в таблице импорта
		$fileds_count = count($tables[self::TABLE_NAME]) - $this->table_elements;

		$this->row = 1;
		// читаем файл импорта
		if (($this->handle = fopen(ABSOLUTE_PATH.$file_path, "r")) !== FALSE)
		{
			if($ftell)
			{
				fseek($this->handle, $ftell);
			}

			while (($row = fgetcsv($this->handle, 0, $this->delimiter, $this->enclosure, $this->csv_escape)) !== FALSE)
			{
				if($this->encoding == 'cp1251')
				{
					$row = utf::to_utf($row);
				}

				// определяем количетво полей в строке
				$num = count($row);
				if($num > $fileds_count)
				{
					$count = $num - $fileds_count;
					if($num > self::MAX_COLUMN)
					{
						$this->errors = '- '.$this->diafan->_("количество полей в содержании файла <b>%s</b> превышает установленный лимит (%s из %s)", (isset($file['basename']) ? $file['basename'] : ''), $num, self::MAX_COLUMN);
						$row = false;
						break;
					}
					$query = array();
					for ($i=0; $i < $count; $i++)
					{
						$fileds_count++;
						$query[] = 'ADD `'.self::COLUMN_NAME.$fileds_count.'` TEXT COMMENT \'поле импорта '.$fileds_count.'\'';
					}
					DB::query("ALTER TABLE {%h} ".implode(", ", $query), self::TABLE_NAME);
				}

				// формируем запрос для вставки в таблицу импорта
				$fileds = array_fill(1, $num, '');
				array_walk($fileds,
					function(&$item, $key, $prefix)
					{
						$item = $prefix.$key;
					},
					self::COLUMN_NAME
				);
				$values = array_fill(1, $num, "'%s'");
				// инициируем запрос для вставки значений в таблицу импорта
				DB::query("INSERT INTO {".self::TABLE_NAME."} (".implode(", ", $fileds).") VALUES (".implode(", ", $values).")", $row);

				// TO_DO: пример вывода содержания полей CSV
				//echo "<p><hr> $num полей в строке $part [".ftell($this->handle)."]: <br /><hr></p>\n";
				//for ($c=0; $c < $num; $c++) echo $row[$c] . "<br />\n";

				$part = $part + 1; // общее количество прочитанных строк
			  $this->row++; // количество прочитанных строк в текущей итерации

				if($this->row > $this->count_part)
				{
					break;
				}
			}
			$ftell = ($row !== FALSE ? ftell($this->handle) : false);
			fclose($this->handle);
		}
		else $ftell = false;

		if($ftell)
		{
			// запоминаем смещение в файле
			$this->ftell = $ftell;
			// запоминаем текущее количество прочитанных строк
			$this->part = $part;
		}
		else
		{
			$this->ftell = false;
			$this->part = 0;
		}
		return true;
	}

	/**
	 * Чтение данных импорта
	 *
	 * @param array $file описание файла импорта
	 * @return void
	 */
	private function read_xlsx($file)
	{
		return $this->read_xls($file);
	}

	/**
	 * Чтение данных импорта
	 *
	 * @param array $file описание файла импорта
	 * @return void
	 */
	private function read_xls($file)
	{
		// текущее смещение в файле
		$ftell = $this->ftell; $this->ftell = false;
		// общее количество прочитанных строк
		$part = $this->part; $this->part = 0;
		// валидация файла импорта
		if(! is_array($file) || empty($file["file_path"]) || ! file_exists(ABSOLUTE_PATH.$file["file_path"]))
		{
			return false;
		}
		// путь до файла относительно корня сайта
		$file_path = $file["file_path"];

		$tables = $this->diafan->_service->get_tables(false, true);
		if(empty($tables[self::TABLE_NAME]))
		{
			return false;
		}
		// определяем текущее количество без учета служебных колонок в таблице импорта
		$fileds_count = count($tables[self::TABLE_NAME]) - $this->table_elements;

		// чмтаем файл импорта
		Custom::inc('plugins/excel.php');
		if(! empty($file["extension"]))
		{
			switch ($file["extension"])
			{
				case 'xls':
					$inputFileType = 'Excel5';
					$obj_reader = PHPExcel_IOFactory::createReader($inputFileType);
					break;

				case 'xlsx':
					$inputFileType = 'Excel2007';
					$obj_reader = PHPExcel_IOFactory::createReader($inputFileType);
					break;

				case 'ods':
					$inputFileType = 'OOCalc';
					$obj_reader = PHPExcel_IOFactory::createReader($inputFileType);
					break;

				case 'xml':
					$inputFileType = 'Excel2003XML';
					$obj_reader = PHPExcel_IOFactory::createReader($inputFileType);
					break;

				default:
					$obj_reader = PHPExcel_IOFactory::createReaderForFile($file_path);
					break;
			}
		}
		else $obj_reader = PHPExcel_IOFactory::createReaderForFile($file_path);
		$chunk_filter = new PHPExcel_СhunkReadFilter();

		if(! is_array($ftell)) $ftell = array('pos' => 1, 'sheet' => false);
		if(empty($ftell['pos'])) $ftell['pos'] = 1;
		if(! isset($ftell['sheet'])) $ftell['sheet'] = false;
		$ftell['pos'] = $ftell['pos'] >= self::XLS_MIN_ROWS ? $ftell['pos'] : self::XLS_MIN_ROWS;

		while ($ftell['pos'] <= self::XLS_MAX_ROWS)
		{
			$chunk_filter->setRows($ftell['pos'], $this->count_part);
			$obj_reader->setReadFilter($chunk_filter);
			$obj_reader->setReadDataOnly(true);
			PHPExcel_Settings::setLibXmlLoaderOptions(LIBXML_COMPACT | LIBXML_PARSEHUGE);
			$obj_PHPExcel = $obj_reader->load($file_path);

			// читаем строки файл импорта
			$current_sheet = $next_sheet = $last_sheet = false;
			$worksheets = $obj_PHPExcel->getWorksheetIterator();
			if(! empty($worksheets))
			{
				foreach($worksheets as $sheet_index => $worksheet)
				{
					// определяем последнюю вкладку файла импорта
					$last_sheet = $sheet_index;
					// определяем текущую и следующую вкладку файла импорта
					$ftell['sheet'] = $ftell['sheet'] === false ? $sheet_index : $ftell['sheet'];
					if($ftell['sheet'] != $sheet_index)
					{
						if($current_sheet !== false && $next_sheet === false) $next_sheet = $sheet_index;
						continue;
					}
					else $current_sheet = $sheet_index;

					//$rows_count = $rows_count = $worksheet->getHighestRow();
					//$cols_count = PHPExcel_Cell::columnIndexFromString($worksheet->getHighestColumn());
					$rows = $worksheet->toArray();
					foreach ($rows as $rw => $row)
					{
						// убираем из полученного массива нулевые строки
						if(empty($row)) { unset($rows[$rw]); continue; }
						$is_null = true;
						foreach ($row as $cl => $col)
						{
							if(is_null($col)) continue;
							$is_null = false;
							break;
						}
						if($is_null) { unset($rows[$rw]); continue; }

						// обрабатываем строку файла импорта
						// определяем количетво полей в строке
						$num = count($row);
						if($num > $fileds_count)
						{
							$count = $num - $fileds_count;
							if($num > self::MAX_COLUMN)
							{
								$this->errors = '- '.$this->diafan->_("количество полей в содержании файла <b>%s</b> превышает установленный лимит (%s из %s)", (isset($file['basename']) ? $file['basename'] : ''), $num, self::MAX_COLUMN);
								$ftell['pos'] = $next_sheet = false;
								break;
							}
							$query = array();
							for ($i=0; $i < $count; $i++)
							{
								$fileds_count++;
								$query[] = 'ADD `'.self::COLUMN_NAME.$fileds_count.'` TEXT COMMENT \'поле импорта '.$fileds_count.'\'';
							}
							DB::query("ALTER TABLE {%h} ".implode(", ", $query), self::TABLE_NAME);
						}

						// формируем запрос для вставки в таблицу импорта
						$fileds = array_fill(1, $num, '');
						array_walk($fileds,
							function(&$item, $key, $prefix)
							{
								$item = $prefix.$key;
							},
							self::COLUMN_NAME
						);
						$values = array_fill(1, $num, "'%s'");
						// инициируем запрос для вставки значений в таблицу импорта
						DB::query("INSERT INTO {".self::TABLE_NAME."} (".implode(", ", $fileds).") VALUES (".implode(", ", $values).")", $row);

						// TO_DO: пример вывода содержания полей CSV
						//echo "<p><hr> $num полей в строке $part: <br /><hr></p>\n";
						//for ($c=0; $c < $num; $c++) echo $row[$c] . "<br />\n";

						$part = $part + 1; // общее количество прочитанных строк
					}
					if(! empty($rows))
					{
						$ftell['pos'] += $this->count_part;
					}
					if(empty($rows) || $ftell['pos'] > self::XLS_MAX_ROWS)
					{
						$ftell['pos'] = false;
					}
				}
				if($ftell['pos'] === false)
				{
					$ftell['pos'] = $next_sheet !== false ? 1 : false;
					$ftell['sheet'] = $next_sheet;
				};
			}
			else
			{
				$ftell['pos'] = false;
				$ftell['sheet'] = false;
			}
			unset($obj_reader);
			unset($obj_PHPExcel);
			break;
		}
		if($ftell['sheet'] !== false)
		{
			// запоминаем смещение в файле
			$this->ftell = $ftell;
			// запоминаем текущее количество прочитанных строк
			$this->part = $part;
		}
		else
		{
			$this->ftell = false;
			$this->part = 0;
		}

		return true;
	}

	/**
	 * Чтение данных импорта
	 *
	 * @param array $file описание файла импорта
	 * @return void
	 */
	private function read_ods($file)
	{
		return $this->read_xls($file);
	}

	/**
	 * Чтение данных импорта
	 *
	 * @param array $file описание файла импорта
	 * @return void
	 */
	private function read_xml($file)
	{
		return $this->read_xls($file);
	}

	/**
	 * Редиректс помошью JavaScript
	 *
	 * @param string $url URL для редиректа
	 * @param boolean $no_history не сохранять исходную страницу в истории сеансов
	 * @return void
	 */
	private function reload_page_js($url = '', $no_history = false)
	{
		if (substr($url, 0, 4) != 'http')
		{
			$url = BASE_PATH_HREF.$url;
		}

		$url = str_replace(array("\n", "\r"), '', $url);

		$this->diafan->_admin->js_code[__CLASS__] = '
						<script language="javascript" type="text/javascript">
							$(function() {
								'.(! $no_history ? 'window.location.href=\''.$url.'\';' : 'window.location.replace(\''.$url.'\');').'
							});
						</script>';
	}

	/**
	 * Выводит список описаний и форму полей для импорта записей
	 *
	 * @return void
	 */
	private function show_import_description()
	{
		$cat = $this->cat - 1;
		if((! $modules = $this->diafan->_service->modules_express()) || ! isset($modules[$cat]))
		{
			return;
		}
		$href = $this->diafan->get_admin_url('cat', 'page', 'step').'cat'.$this->cat.'/'.'step1/';
		echo '<p>'
			.$this->diafan->_('Загружен файл %s для импорта в модуль %s.', '<b>'.$this->file_name.'</b>', '<b>'.$modules[$cat]["title"].'</b>')
			.'<br />'
			.$this->diafan->_('Загрузить %sдругой файл%s или выбрать %sмодуль%s для импорта.', '<a href="'.$href.'">', '</a>', '<a href="'.$href.'">', '</a>')
			.'</p>';

		// TO_DO: единообразная очередность полей для таблицы {service_express_fields_category} - ORDER BY sort ASC, id ASC
		if($cats = DB::query_fetch_key("SELECT id, name, module_name, site_id, cat_id, menu_cat_id, type, delete_items, add_new_items, act_items, header, sub_delimiter, count_part FROM {%s_category} WHERE trash='0' AND module_name='%s' ORDER BY sort ASC, id ASC", 'service_express_fields', $modules[$cat]["module_name"], "id"))
		{
			$cat_id = $this->diafan->filter($_GET, 'integer', 'cat', 0);
			$cat_id = array_key_exists($cat_id, $cats) ? $cat_id : key($cats);
		}
		else $cat_id = 0;
		$cats = array(0 => 'Новый импорт') + $cats;

		Custom::inc('adm/includes/edit.php');
		$object = new Edit_admin($this->diafan);

		echo '
		<form id="form_express_import_category" class="ajax" action="" method="POST">
			<input type="hidden" name="action" value="">
			<input type="hidden" name="module" value="'.$this->diafan->_admin->module.'">
			<input type="hidden" name="check_hash_user" value="'.$this->diafan->_users->get_hash().'">
			<input type="hidden" name="module_name" value="'.$modules[$cat]["module_name"].'">';

		echo '
			<div class="box box_height">';

		// fields_cat_id
		$key = "fields_cat_id"; $name = $this->diafan->_("Выберите сохраненный импорт или создайте новый");
		$value = $cat_id;
		$help = "Выберите ранее созданное описание импорта/экспорта записей или используйте новое описание.";
		$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>'
			.' <i class="tooltip fa fa-gear" title="'.$this->diafan->_('Настроить параметры импорта').'"></i>';
		$disabled = false;
		$options = $cats;
		$attr = 'unit_id="fields_cat_param"'; $class = "box_toggle";
		$object->show_table_tr_select($key, $name, $value, $help, $disabled, $options, $attr, $class);
		// echo '<div id="fields_cat_param" class="hide">'; // скрыть настройки
		echo '<div id="fields_cat_param" class="">'; // не скрывать настройки

		// type
		Custom::inc('modules/service/admin/service.admin.express.fields.category.php');
		$obj = new Service_admin_express_fields_category($this->diafan);
		$obj->prepare_config();
		$key = "type"; $name = $this->diafan->_("Что импортируем?");
		if(! empty($cats[$cat_id]["module_name"]) && $cats[$cat_id]["module_name"] == $modules[$cat]["module_name"])
		{
			$value = ! empty($cats[$cat_id]["type"]) ? $cats[$cat_id]["type"] : "element";
		}
		else $value = "element";
		$help = "Тип данных модуля, для которой будет производится импорт.";
		$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
		$disabled = ! $modules[$cat]["module_name"];
		$options = array();
		if($modules[$cat]["module_name"])
		{
			$types = $obj->variables["main"]["type"]["type_cat"];
			foreach ($types as $k => $val) $types[$k] = explode(",", $val);
			foreach ($obj->variables["main"]["type"]["select"] as $k => $val)
			{
				if(! in_array($modules[$cat]["module_name"], $types[$k])) continue;
				$options[$k] = $val;
			}
		}
		else $options["element"] = "не используется";
		$attr = $class = "";
		$object->show_table_tr_select($key, $name, $value, $help, $disabled, $options, $attr, $class);

		unset($obj);

		// site_id
		$key = "site_id"; $name = $this->diafan->_("В какой раздел сайта?");
		if(! empty($cats[$cat_id]["module_name"]) && $cats[$cat_id]["module_name"] == $modules[$cat]["module_name"])
		{
			$value = ! empty($cats[$cat_id]["site_id"]) ? $cats[$cat_id]["site_id"] : 0;
		}
		else $value = 0;
		$help = "Страница сайта с прикрепленным модулем, для которой будет производится импорт.";
		$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
		$disabled = ! $modules[$cat]["module_name"];
		$site_cats = array();
		if($modules[$cat]["module_name"])
		{
			if(! $site_cats = DB::query_fetch_all("SELECT id, [name] FROM {site} WHERE trash='0' AND module_name='%s' ORDER BY sort ASC, id DESC", $modules[$cat]["module_name"]))
			{
				$disabled = true;
			}
		}
		$options = $site_cats;
		$attr = $class = "";
		$object->show_table_tr_select($key, $name, $value, $help, $disabled, $options, $attr, $class);

		// cat_id
		$key = "cat_id"; $name = $this->diafan->_("Импортируем в конкретную категорию?");
		if(! empty($cats[$cat_id]["module_name"]) && $cats[$cat_id]["module_name"] == $modules[$cat]["module_name"])
		{
			$value = ! empty($cats[$cat_id]["cat_id"]) ? $cats[$cat_id]["cat_id"] : 0;
		}
		else $value = 0;
		$help = "Возможность ограничить импорт/экспорт одной категорией в модуле.";
		$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
		$disabled = false;
		if(! $use_cat = $this->diafan->configmodules("cat", $modules[$cat]["module_name"]))
		{
			$disabled = true;
		}
		$module_cats[0] = array();
		if($modules[$cat]["module_name"] && $use_cat)
		{
			$tables = $this->diafan->_service->get_tables("site_id");
			if(! empty($tables[$modules[$cat]["module_name"].'_category']))
			{
				$query = ', site_id AS rel';
			}
			else $query = ', 0 AS rel';
			$rows = DB::query_fetch_all("SELECT id, [name], parent_id".$query." FROM {%s_category} WHERE trash='0' ORDER BY sort ASC LIMIT 1000", $modules[$cat]["module_name"]);
			if(count($rows) == 1000)
			{
				$disabled = true;
				$use_cat = false;
			}
			else
			{
				foreach ($rows as $row)
				{
					$module_cats[$row["parent_id"]][] = $row;
				}
			}
		}
		$options = $module_cats;
		$attr = $class = "";
		echo '
					<div class="unit'.($class ? ' '.$class : '').'" id="'.$key.'"'.$attr.'>
						<div class="infofield">'.$name.$help.'</div>
						<select name="'.$key.'"'.($disabled ? ' disabled' : '').'>';
		if($modules[$cat]["module_name"] && $use_cat)
		{
			echo '<option value="" rel="0">'.$this->diafan->_('-').'</option>';
			echo $this->diafan->get_options($options, $options[0], array( $value ));
		}
		else
		{
			echo '<option value="0" rel="0" selected>'.$this->diafan->_("не используется").'</option>';
		}
		echo '
						</select>';
		echo '
					</div>';

		// menu_cat_id
		$key = "menu_cat_id"; $name = $this->diafan->_("Создать пункты меню для категорий?");
		if(! empty($cats[$cat_id]["module_name"]) && $cats[$cat_id]["module_name"] == $modules[$cat]["module_name"])
		{
			$value = ! empty($cats[$cat_id]["menu_cat_id"]) ? $cats[$cat_id]["menu_cat_id"] : 0;
		}
		else $value = 0;
		$help = "Если отметить, в [модуле «Меню на сайте»](http://www.diafan.ru/dokument/full-manual/sysmodules/menu/) будут созданы пунктыменю со ссылкой на категории.";
		$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
		$disabled = false;
		if(! $rows = DB::query_fetch_all("SELECT id, [name] FROM {menu_category} WHERE trash='0' ORDER BY id DESC LIMIT 1000"))
		{
			$disabled = true;
		}
		if(count($rows) == 1000)
		{
			$disabled = true;
		}
		$attr = ' depend="type=element"'; $class = "depend_field";
		echo '
					<div class="unit'.($class ? ' '.$class : '').'" id="'.$key.'"'.$attr.'>
						<div class="infofield">'.$name.$help.'</div>
						<select name="'.$key.'"'.($disabled ? ' disabled' : '').'>';
		echo '<option value="0">-</option>';
		if(! $disabled)
		{
			foreach($rows as $row)
			{
				echo '<option value="'.$row["id"].'"'
				.($value == $row["id"] ? ' selected="selected" ' : '')
				.'>'.$row["name"].'</option>';
			}
		}
		echo '
						</select>';
		echo '
					</div>';

		// delete_items
		$key = "delete_items"; $name = $this->diafan->_("Удалить всё в модуле перед импортом");
		$value = isset($cats[$cat_id][$key]) ? $cats[$cat_id][$key] : 0;
		$help = "Если Вы загружаете список новых записей или категорий, и то, что уже есть на сайте не нужно, следует отметить эту опцию. На сайт импортируются новые записи из файла, а все уже существующие записи или категории будут удалены, за исключением тех, что будут обновлены (определяется по идентификатору).";
		$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
		$disabled = false;
		$attr = $class = "";
		$object->show_table_tr_checkbox($key, $name, $value, $help, $disabled, $attr, $class);

		// add_new_items
		$key = "add_new_items"; $name = $this->diafan->_("Добавить только новые записи");
		$value = isset($cats[$cat_id][$key]) ? $cats[$cat_id][$key] : 0;
		$help = "На сайт импортируются только новые записи из файла, а все уже существующие записи или категории будут пропущены (определяется по идентификатору).";
		$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
		$disabled = false;
		$attr = $class = "";
		$object->show_table_tr_checkbox($key, $name, $value, $help, $disabled, $attr, $class);

		// act_items
		$key = "act_items"; $name = $this->diafan->_("Опубликовать записи на сайте");
		$value = isset($cats[$cat_id][$key]) ? $cats[$cat_id][$key] : 0;
		$help = "Если отмечено, то все импортируемые записи будут опубликованы на сайте.";
		$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
		$disabled = false;
		$attr = $class = "";
		$object->show_table_tr_checkbox($key, $name, $value, $help, $disabled, $attr, $class);

		// count_part
		$key = "count_part"; $name = $this->diafan->_("Количество обрабатываемых строк за проход");
		$value = isset($cats[$cat_id][$key]) ? $cats[$cat_id][$key] : "20";
		$help = "Время работы скрипта на большинстве хостингов ограничено, из-за чего скрипт может не успеть обработать весь файл за одну итерацию, если он объемный. Поэтому файл обрабатывается частями, а величину итерации можно задать этим параметром.";
		$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
		$disabled = false;
		$attr = $class = "";
		$object->show_table_tr_numtext($key, $name, $value, $help, $disabled, $attr, $class);

		// sub_delimiter
		$key = "sub_delimiter"; $name = $this->diafan->_("Разделитель данных внутри поля");
		$value = isset($cats[$cat_id][$key]) ? $cats[$cat_id][$key] : "|";
		$help = "В некоторых полях (ячейках) может быть несколько данных (например, значение характеристики с типом «список с выбором нескольких значений» или несколько имен изображений для одного товара). В этом случае данные должны быть разделены этим разделителем.";
		$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
		$disabled = false;
		$attr = $class = "";
		$maxlength = 1;
		$object->show_table_tr_text($key, $name, $value, $help, $disabled, $attr, $class, $maxlength);

		// header
		$key = "header"; $name = $this->diafan->_("Не учитывать первую строку в файле");
		$value = isset($cats[$cat_id][$key]) ? $cats[$cat_id][$key] : 1;
		$help = "Если отмечено, то добавляется описание полей в первой строке файла при экспорте, а при импорте первая строка игнорируется.";
		$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
		$disabled = false;
		$attr = $class = "";
		$object->show_table_tr_checkbox($key, $name, $value, $help, $disabled, $attr, $class);

		echo '
				</div>
			</div>';
		echo '
		</form>';

		unset($object);

		$tables = $this->diafan->_service->get_tables(false, true);
		if(! empty($tables[self::TABLE_NAME]) && DB::query_result("SELECT id FROM {".self::TABLE_NAME."} WHERE trash='0' LIMIT 1"))
		{
			echo '
			<div class="box box_height box_table">';
			echo '
				<div class="infofield">'.$this->diafan->_('Определите найденные поля:').' <i class="tooltip fa fa-question-circle" title="'.$this->diafan->_('Для определения полей используйте выпадающий список или значек настройки поля.').'"></i></div>';
			$this->table_row($modules[$cat]["module_name"], $cat_id);
			echo '
			</div>';
		}
	}

	/**
	 * Выводит таблицу для определения полей импорта записей
	 *
	 * @param string $module_name имя модуля
	 * @param integer $cat_id идентификатор описания файла из таблицы {service_express_fields_category}
	 * @return void
	 */
	public function table_row($module_name, $cat_id = 0)
	{
		$tables = $this->diafan->_service->get_tables(false, true);
		if(empty($tables[self::TABLE_NAME]) || ! DB::query_result("SELECT id FROM {".self::TABLE_NAME."} WHERE trash='0' LIMIT 1"))
		{
			return false;
		}

		Custom::inc("modules/service/admin/service.admin.express.fields.element.php");
		inc_file_express_modules( $this->diafan, $module_name );
		$object = new service_admin_express_fields_element($this->diafan);
		$object->prepare_config();

		$names = $types = $params = $required = array();
		// TO_DO: принципиально важна единообразная очередность полей для таблицы {service_express_fields} - ORDER BY sort ASC, id ASC
		if($fields = DB::query_fetch_all("SELECT * FROM {%s} WHERE trash='0' AND cat_id=%d ORDER BY sort ASC, id ASC", "service_express_fields", $cat_id))
		{
			foreach ($fields as $k => $val)
			{
				if($val["type"] == 'empty')
				{ // игнорируем все поля - пропуски
					unset($fields[$k]);
					continue;
				}
				ob_start(); $object->table_variable_name($val["id"]); $module_contents = ob_get_contents(); ob_end_clean();
				$names[$k] = $module_contents;
				ob_start(); $object->table_variable_type($val["id"]); $module_contents = ob_get_contents(); ob_end_clean();
				$types[$k] = $module_contents;
				ob_start(); $object->table_variable_params($val["id"]); $module_contents = ob_get_contents(); ob_end_clean();
				$params[$k] = $module_contents;
				ob_start(); $object->table_variable_required($val["id"]); $module_contents = ob_get_contents(); ob_end_clean();
				$required[$k] = $module_contents;
			}
		}

		$polog = 0; $nastr = $this->preview_count > 0 ? $this->preview_count : 3;
		$table_rows = DB::query_fetch_all("SELECT e.* FROM {".self::TABLE_NAME."} as e WHERE 1=1 AND trash='0' GROUP BY e.id LIMIT %d, %d", $polog, $nastr);
		if(! empty($table_rows))
		{
			$rows = array();
			foreach($table_rows as $key => $value)
			{
				foreach($value as $name => $val)
				{
					$rows[$name][] = $val;
				}
			}
			echo '
			<form id="form_express_import" class="ajax" action="" method="POST">
				<input type="hidden" name="action" value="prepare_import">
				<input type="hidden" name="module" value="'.$this->diafan->_admin->module.'">
				<input type="hidden" name="check_hash_user" value="'.$this->diafan->_users->get_hash().'">
				<table class="fields_express">';

			echo '
					<col class="col1"><col span="'.count($table_rows).'" class="coln">';
			echo '
					<tr>';
			echo '
						<th class="col1">'.$this->diafan->_('Тип поля').'</th>';
			echo '
						<th class="coln" colspan="'.count($table_rows).'">'.$this->diafan->_('Значения найденных полей').'</th>';
			echo '
					</tr>';
			$k = 0;
			foreach($rows as $name => $value)
			{
				if(! preg_match('/'. self::COLUMN_NAME .'([0-9]+)/', $name, $result))
				{
					continue;
				}

				echo '
					<tr class="row">';
				echo '
						<td class="col1">';
				if(isset($types[$k]))
				{
					if(! empty($types[$k]))
					{
						echo (! empty($fields[$k]["name"]) ? '<span class="row_name">'.$fields[$k]["name"].'</span> ' : '')
						 .$types[$k].' <i class="tooltip fa fa-gear" title="'.$this->diafan->_("Настройки поля импорта/экспорта").'"></i>';
					}
				}
				else
				{
					$key = 0;
					ob_start(); $object->table_variable_type($key); $module_contents = ob_get_contents(); ob_end_clean();
					$type_default = $module_contents;
					if(! empty($type_default))
					{
						echo $type_default.' <i class="tooltip fa fa-gear" title="'.$this->diafan->_("Настройки поля импорта/экспорта").'"></i>';
					}
				}
				echo '
						</td>';
				foreach($value as $val)
				{
					echo '
						<td class="coln">'.(! empty($val) ? $this->diafan->short_text($val) : '').'</td>';
				}
				echo '
					</tr>';


				echo '
					<tr class="field hide">';
				echo '
						<td colspan="'.(count($table_rows) + 1).'">';
				if(isset($fields[$k]))
				{
					if(! empty($params[$k])) echo $params[$k];
					if(! empty($names[$k])) echo $names[$k];
					if(! empty($required[$k])) echo $required[$k];
				}
				else
				{
					$key = 0;
					ob_start(); $object->table_variable_name($key); $module_contents = ob_get_contents(); ob_end_clean();
					$name_default = $module_contents;
					ob_start(); $object->table_variable_params($key); $module_contents = ob_get_contents(); ob_end_clean();
					$params_default = $module_contents;
					ob_start(); $object->table_variable_required($key); $module_contents = ob_get_contents(); ob_end_clean();
					$required_default = $module_contents;

					if(! empty($params_default)) echo $params_default;
					if(! empty($name_default)) echo $name_default;
					if(! empty($required_default)) echo $required_default;
				}
				$k++;

				echo '
						</td>';
				echo '
					</tr>';
			}
			echo '
				</table>
			</form>';
		}

		unset($object);
	}

	/**
	 * Выводит форму инициализации для импорта записей
	 *
	 * @return void
	 */
	private function show_import_init()
	{
		$tables = $this->diafan->_service->get_tables(false, true);
		if(empty($tables[self::TABLE_NAME]) || ! DB::query_result("SELECT id FROM {".self::TABLE_NAME."} WHERE trash='0' LIMIT 1"))
		{
			return false;
		}

		echo '
		<p class="after-action-box">'.$this->diafan->_('И импорт и экспорт будут проводиться согласно полям, указанным выше. Вы можете самостоятельно создать необходимую структуру импорта/экспорта. Например, у Вас файл-таблица с товарами, где три колонки: «товар», «цена», «описание». Тогда вверху в списке импортируемых полей должно быть только три поля «Название товара», «Цена», «Полное описание» и именно в таком порядке.').'</p>';

		echo '
		<div class="box box_height">';
		echo '
			<a name="express_import"></a>
			<div class="box__warning">
				<i class="fa fa-warning"></i>
				'.$this->diafan->_('Рекомендуем перед импортом %sсоздать резервную копию базы данных%s, если на сайте есть информация.', '<a href="'.BASE_PATH_HREF.'service/db/'.'" target="_blank">', '</a>').'
			</div>';
		echo '
			<form id="express_form_request"></form>';
		echo '
			<button id="express_button" class="btn btn_blue btn_small" default="'.$this->diafan->_("Импортировать").'" action="import">'.$this->diafan->_("Импортировать").'</button>
			<img class="spinner_express hide" src="'.BASE_PATH.'adm/img/loading.gif">
			<button id="express_save_button" class="btn btn_blue btn_small" action="only_save">'.$this->diafan->_("Сохранить описание импорта").'</button>
		</div>';

		$this->diafan->_admin->js_code[__METHOD__] = '
<script type="text/javascript">
	var CONFIRM_IMPORT_FILES = "'. $this->diafan->_('Превышен лимит времени исполнения скрипта. Возможно отсутствует соединение с сайтом. Попробовать продолжить импорт?') .'";
</script>';
	}
}

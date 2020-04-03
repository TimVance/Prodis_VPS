<?php
/**
 * Обработка POST-запросов в административной части модуля
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if ( ! defined('DIAFAN'))
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
 * Service_admin_action
 */
class Service_admin_action extends Action_admin
{
	/**
	 * @var string имя основного класса
	 */
	const CLASS_NAME = 'Service_admin_express';

	/**
	 * @var string имя таблицы базы данных без префикса, содержащей поисание элементов импорта/экспорта
	 */
	const TABLE_NAME = 'service_express_import_elements';

	/**
	 * @var string базовый URL
	 */
	private $url = '';

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
		$this->url = BASE_PATH_HREF.'service/express/';
	}

	/**
	 * Вызывает обработку Ajax-запросов
	 *
	 * @return void
	 */
	public function init()
	{
		if (! empty($_POST["action"]))
		{
			switch($_POST["action"])
			{
				// TO_DO: service.admin.express.fields.category.php && service.admin.express.fields.edit.js
				case 'module_name_change':
					$this->module_name_change();
					break;

				// TO_DO: service.admin.express.import.php && service.admin.express.import.js
				case 'fields_cat_id_import_change':
					$this->fields_cat_id_import_change();
					break;

				// TO_DO: service.admin.express.import.php && service.admin.express.import.js
				case 'prepare_import':
					$this->prepare_import();
					break;

				// TO_DO: service.admin.express.import.php && service.admin.express.import.js
				case 'load_defer_files':
					$this->load_defer_files();
					break;

				// TO_DO: service.admin.express.import.php && service.admin.express.import.js
				case 'import_files':
					$this->import_files();
					break;

				// TO_DO: service.admin.express.export.php && service.admin.express.export.js
				case 'modules_export_change':
					$this->modules_export_change();
					break;

				// TO_DO: service.admin.express.export.php && service.admin.express.export.js
				case 'fields_cat_id_export_change':
					$this->fields_cat_id_export_change();
					break;

				// TO_DO: service.admin.express.export.php && service.admin.express.export.js
				case 'export_data':
					$this->export_data();
					break;
			}
		}
	}

	/**
	 * Смена названия модуля в настройках описания импорта/экспорта
	 *
	 * @return void
	 */
	private function module_name_change()
	{
		// site_id
		$disabled = false;
		if(! $module_name = preg_replace('/[^a-z0-9_]+/', '', $_POST["module_name"]))
		{
			$disabled = true;
		}
		if($id = $this->diafan->filter($_POST, 'int', 'id'))
		{
			$fields = DB::query_fetch_array("SELECT * FROM {service_express_fields_category} WHERE id=%d LIMIT 1", $id);
		}
		else $fields = false;

		if(! empty($fields["module_name"]) && $fields["module_name"] == $module_name)
		{
			$value = ! empty($fields["site_id"]) ? $fields["site_id"] : 0;
		}
		else $value = 0;

		$cats[0] = array();
		if($module_name)
		{
			if(! $cats[0] = DB::query_fetch_all("SELECT id, [name] FROM {site} WHERE trash='0' AND module_name='%s' ORDER BY sort ASC, id DESC", $module_name))
			{
				$disabled = true;
			}
		}

		$key = 'site_id';
		$this->result[$key] = '
		<div class="unit" id="site_id"'.(! $module_name ? ' style="display: none;"' : '').'>
			<div class="infofield">'.(! empty($_POST["site_id_infofield"]) ? $_POST["site_id_infofield"] : '').'</div>
			<select name="'.$key.'"'.($disabled ? ' disabled' : '').'>';
		if($module_name && $cats[0])
		{
			$this->result[$key] .= $this->diafan->get_options($cats, $cats[0], array ( $value ));
		}
		else
		{
			$this->result[$key] .= '<option value="0" selected>'.$this->diafan->_("не используется").'</option>';
		}
		$this->result[$key] .= '
			</select>
		</div>';


		// cat_id
		$disabled = false;
		if(! $cat = $this->diafan->configmodules("cat", $module_name))
		{
			$disabled = true;
		}

		$cats[0] = array();
		if($module_name && $cat)
		{
			$tables = $this->diafan->_service->get_tables("site_id");
			if(! empty($tables[$module_name.'_category']))
			{
				$query = ', site_id AS rel';
			}
			else $query = ', 0 AS rel';
			$rows = DB::query_fetch_all("SELECT id, [name], parent_id".$query." FROM {%s_category} WHERE trash='0' ORDER BY sort ASC LIMIT 1000", $module_name);
			if(count($rows) == 1000)
			{
				$disabled = true;
				$cat = false;
			}
			else
			{
				foreach ($rows as $row)
				{
					$cats[$row["parent_id"]][] = $row;
				}
			}
		}

		if(! empty($fields["module_name"]) && $fields["module_name"] == $module_name)
		{
			$value = ! empty($fields["cat_id"]) ? $fields["cat_id"] : 0;
		}
		else $value = 0;

		$key = 'cat_id';
		$this->result[$key] = '
		<div class="unit" id="cat_id">
			<div class="infofield">'.(! empty($_POST["cat_id_infofield"]) ? $_POST["cat_id_infofield"] : '').'</div>';

		$this->result[$key] .= ' <select name="'.$key.'"'.($disabled ? ' disabled' : '').'>';
		if($module_name && $cat)
		{
			$this->result[$key] .= '<option value="" rel="0">'.$this->diafan->_('-').'</option>';
			$this->result[$key] .= $this->diafan->get_options($cats, $cats[0], array( $value ));
		}
		else
		{
			$this->result[$key] .= '<option value="" rel="0" selected>'.$this->diafan->_("не используется").'</option>';
		}
		$this->result[$key] .= '</select>';

		$this->result[$key] .= '</div>';


		// type
		Custom::inc('modules/service/admin/service.admin.express.fields.category.php');
		$obj = new Service_admin_express_fields_category($this->diafan);
		$obj->prepare_config();

		$disabled = false;
		if(! $module_name)
		{
			$disabled = true;
		}

		if(! empty($fields["module_name"]) && $fields["module_name"] == $module_name)
		{
			$value = ! empty($fields["type"]) ? $fields["type"] : "element";
		}
		else $value = "element";

		$key = 'type';
		$this->result[$key] = '
		<div class="unit" id="type">
			<div class="infofield">'.(! empty($_POST["type_infofield"]) ? $_POST["type_infofield"] : '').'</div>
			<select name="type"'.($disabled ? ' disabled' : '').'>';
			if($module_name)
			{
				$types = $obj->variables["main"]["type"]["type_cat"];
				foreach ($types as $k => $val) $types[$k] = explode(",", $val);
				foreach ($obj->variables["main"]["type"]["select"] as $k => $val)
				{
					if(! in_array($module_name, $types[$k]))
					{
						continue;
					}
					$this->result[$key] .= '<option value="'.$k.'"'.($value == $k ? ' selected' : '').'>'.$val.'</option>';
				}
			}
			else
			{
				$this->result[$key] .= '<option value="element" selected>'.$this->diafan->_("не используется").'</option>';
			}
			$this->result[$key] .= '</select>
		</div>';
	}

	/**
	 * Смена описания импорта
	 *
	 * @return void
	 */
	private function fields_cat_id_import_change()
	{
		$cat = false;
		if($id = $this->diafan->filter($_POST, 'int', 'id'))
		{
			if($cat = DB::query_fetch_array("SELECT type, site_id, cat_id, menu_cat_id, delete_items, add_new_items, act_items, header, sub_delimiter, count_part FROM {%s} WHERE id=%d LIMIT 1", "service_express_fields_category", $id))
			{
				$cat["cat_id"] = ! $cat["cat_id"] ? '' : $cat["cat_id"];
			}
		}
		if(! $cat)
		{
			$cat = array(
				"type"          => "element",
				"site_id"       => 0,
				"cat_id"        => 0,
				"menu_cat_id"   => 0,
				"delete_items"  => 0,
				"add_new_items" => 0,
				"act_items"     => 0,
				"header"        => 1,
				"sub_delimiter" => '|',
				"count_part"    => 20
			);
		}
		$this->result['result'] = $cat;

		$cat_id = $id ?: 0;
		$module_name = ! empty( $_POST["module_name"] ) ? preg_replace('/[^a-z0-9_]+/', '', $_POST["module_name"]) : '';
		if($module_name)
		{
			$count = $this->diafan->filter($_POST, 'int', 'count');

			Custom::inc("modules/service/admin/service.admin.express.fields.element.php");
			inc_file_express_modules( $this->diafan, $module_name );
			$object = new service_admin_express_fields_element($this->diafan);
			$object->prepare_config();

			Custom::inc('adm/includes/edit.php');
			$obj = new Edit_admin($this->diafan);

			$names = $types = $params = $required = array();
			// TO_DO: принципиально важна единообразная очередность полей для таблицы {service_express_fields} - ORDER BY sort ASC, id ASC
			if($fields = DB::query_fetch_all("SELECT * FROM {%s} WHERE trash='0' AND cat_id=%d ORDER BY sort ASC, id ASC", "service_express_fields", $cat_id))
			{
				$c = count($fields); $count = $count >= $c ? $count : $c;
				foreach ($fields as $val)
				{
					$count--;

					$key = $val["id"];
					if($val["type"] == 'empty')
					{ // игнорируем все поля - пропуски
						$key = 0;
					}
					ob_start(); $object->table_variable_name($key); $module_contents = ob_get_contents(); ob_end_clean();
					$names[] = $module_contents;
					ob_start(); $object->table_variable_type($key); $module_contents = ob_get_contents(); ob_end_clean();
					$types[] = $module_contents;
					ob_start(); $object->table_variable_params($key); $module_contents = ob_get_contents(); ob_end_clean();
					$params[] = $module_contents;
					ob_start(); $object->table_variable_required($key); $module_contents = ob_get_contents(); ob_end_clean();
					$required[] = $module_contents;
				}
			}

			if($count > 0)
			{
				for ($i=0; $i < $count; $i++)
				{
					$key = 0;
					ob_start(); $object->table_variable_name($key); $module_contents = ob_get_contents(); ob_end_clean();
					$name_default = $module_contents;
					ob_start(); $object->table_variable_type($key); $module_contents = ob_get_contents(); ob_end_clean();
					$type_default = $module_contents;
					ob_start(); $object->table_variable_params($key); $module_contents = ob_get_contents(); ob_end_clean();
					$params_default = $module_contents;
					ob_start(); $object->table_variable_required($key); $module_contents = ob_get_contents(); ob_end_clean();
					$required_default = $module_contents;

					$names[] = $name_default;
					$types[] = $type_default;
					$params[] = $params_default;
					$required[] = $required_default;
				}
			}

			unset($object, $obj);

			$table = array(
				"rows" => array(),
				"fields" => array()
			);
			foreach($types as $key => $value)
			{
				$type = '';
				if(! empty($types[$key]))
				{
					$type = (! empty($fields[$key]["name"]) && ! empty($fields[$key]["type"]) && $fields[$key]["type"] != 'empty' ? '<span class="row_name">'.$fields[$key]["name"].'</span> ' : '')
					 .$types[$key].' <i class="tooltip fa fa-gear" title="'.$this->diafan->_("Настройки поля импорта/экспорта").'"></i>';
				}
				$table["rows"][$key] = $type;
			}
			foreach($params as $key => $value)
			{
				$table["fields"][$key] = $value.(isset($names[$key]) ? $names[$key] : '').(isset($required[$key]) ? $required[$key] : '');
			}

			$this->result['table'] = $table;
		}

		$modules = $this->diafan->_service->modules_express();
		$cat = false;
		foreach ($modules as $key => $value)
		{
			if(empty($value["name"]) || $value["name"] != $module_name)
			{
				continue;
			}
			$cat = $key;
			break;
		}
		if($cat !== false)
		{
			$cat++;
			$url = $this->diafan->get_admin_url('cat', 'page', 'step').'cat'.$cat.'/'.'step2'.'/';
			if($cat_id)
			{
				$url = $this->diafan->params_append($url, array('cat' => $cat_id));
			}
			$this->result['curLoc'] = $url;
			$_SESSION[self::CLASS_NAME]["cat_import_choice"]["cat"] = $cat;
			$_SESSION[self::CLASS_NAME]["cat_import_choice"]["desc"] = $cat_id;
		}
	}

	/**
	 * Подготовка к импорту
	 *
	 * @return void
	 */
	private function prepare_import()
	{
		$module_name = ! empty( $_POST["category"]["module_name"] ) ? preg_replace('/[^A-Za-z-]+/', '', $_POST["category"]["module_name"]) : '';
		$cat_id = isset($_POST["category"]["fields_cat_id"]) ? $_POST["category"]["fields_cat_id"] : 0;
		$new_cat_name = $this->diafan->_('Новый импорт');
		if($cat = DB::query_fetch_array("SELECT id, name, module_name, type, delete_items, add_new_items, act_items, site_id, cat_id, menu_cat_id, count_part, sub_delimiter, header FROM {%s} WHERE id=%d AND trash='0' LIMIT 1", 'service_express_fields_category', $cat_id))
		{
			$cat_id = $cat["id"];
			$cat_name = $cat["name"];
			$module_name = $module_name ?: $cat["module_name"];
			$perhaps_add = true;
		}
		else
		{
			$cat_id = 0;
			$cat_name = $new_cat_name;
			$perhaps_add = false;
		}
		$request = false;
		if($module_name)
		{
			Custom::inc("modules/service/admin/service.admin.express.fields.element.php");
			inc_file_express_modules( $this->diafan, $module_name );
			$object = new service_admin_express_fields_element($this->diafan);
			$object->prepare_config();
			$object->validate_table_variable_type();
			$object->validate_table_variable_params();
			if($errors = $object->get_errors())
			{
				$this->result["errors"] = $object->get_errors();
			}
			else
			{
				if(! $cat_id || ! empty($_POST["request"]["new_category"]))
				{ // Новая категория (описание) и поля описания: создаем категорию (описание) и поля описания.
					$new_category = true;
					if(! empty($_POST["request"]) && ! empty($_POST["category"]))
					{
						if($cat_id = DB::query("INSERT INTO {%s} () VALUES ()", "service_express_fields_category"))
						{
							$sort = DB::query_result("SELECT MAX(sort)+1 FROM {%s} WHERE 1=1 LIMIT 1", "service_express_fields_category");
							$cat_name = $this->diafan->filter($_POST["request"], 'string', 'category_name', $new_cat_name);

							$n = $sort - 1; $i = 0;
							if($cat_name == $new_cat_name)
							{
								$cat_name .= ' - '.(++$n);
							}
							while(DB::query_result("SELECT name FROM {%s} WHERE name='%s' LIMIT 1", "service_express_fields_category", $cat_name))
							{
								$cat_name = preg_replace('/ \- ([0-9]+)$/', '', $cat_name);
								$cat_name .= ' - '.(++$n); $i++;
								if($i > 100) break;
							}

							$fields = array(
								array( "query" => "name='%h'",          "value" => $cat_name ),
								array( "query" => "module_name='%h'",   "value" => $this->diafan->filter($_POST["category"], 'string', 'module_name', '') ),
								array( "query" => "type='%h'",          "value" => $this->diafan->filter($_POST["category"], 'string', 'type', 'element') ),
								array( "query" => "delete_items='%d'",  "value" => $this->diafan->filter($_POST["category"], 'integer', 'delete_items', 0) ),
								array( "query" => "add_new_items='%d'", "value" => $this->diafan->filter($_POST["category"], 'integer', 'add_new_items', 0) ),
								array( "query" => "act_items='%d'",     "value" => $this->diafan->filter($_POST["category"], 'integer', 'act_items', 0) ),
								array( "query" => "site_id=%d",         "value" => $this->diafan->filter($_POST["category"], 'integer', 'site_id', 0) ),
								array( "query" => "cat_id=%d",          "value" => $this->diafan->filter($_POST["category"], 'integer', 'cat_id', 0) ),
								array( "query" => "menu_cat_id=%d",     "value" => $this->diafan->filter($_POST["category"], 'integer', 'menu_cat_id', 0) ),
								array( "query" => "count_part=%d",      "value" => $this->diafan->filter($_POST["category"], 'integer', 'count_part', 20) ),
								array( "query" => "sub_delimiter='%h'", "value" => (! empty($_POST["category"]["sub_delimiter"]) ? $_POST["category"]["sub_delimiter"] : '|') ),
								array( "query" => "header='%d'",        "value" => $this->diafan->filter($_POST["category"], 'integer', 'header', 0) ),
								array( "query" => "sort=%d",            "value" => $sort ),
							);
							DB::query("UPDATE {%s} SET ".implode(',', $this->diafan->array_column($fields, 'query'))." WHERE id=%d", array_merge(array("service_express_fields_category"), $this->diafan->array_column($fields, 'value'), array($cat_id)));
						}
						if($cat_id && ! empty($_POST['type']) && is_array($_POST['type']))
						{
							$params = $object->save_table_variable_params();
							$arr_query = $this->diafan->array_column($params, 'query');
							$arr_value = $this->diafan->array_column($params, 'value');
							$sort = DB::query_result("SELECT MAX(sort)+1 FROM {%s} WHERE 1=1 LIMIT 1", "service_express_fields");
							foreach($_POST['type'] as $key => $type)
							{
								$name = isset($_POST["name"][$key]) ? $_POST["name"][$key] : '';
								$name = ($name ?: (isset($object->variables["main"]["type"]["select"][$type]) ? $object->variables["main"]["type"]["select"][$type] : $name));
								$query = isset($arr_query[$key]) ? $arr_query[$key] : "params='%s'";
								$value = isset($arr_value[$key]) ? $arr_value[$key] : "";
								$fields = array(
									array( "query" => "name='%h'",     "value" => $name ),
									array( "query" => "cat_id=%d",     "value" => $cat_id ),
									array( "query" => "type='%h'",     "value" => $type ),
									array( "query" => $query,          "value" => $value ),
									array( "query" => "required='%d'", "value" => (! empty($_POST["required"][$key]) ? 1 : 0) ),
									array( "query" => "sort=%d",       "value" => $sort++ ),
								);
								if($id = DB::query("INSERT INTO {%s} () VALUES ()", "service_express_fields"))
								{
									DB::query("UPDATE {%s} SET ".implode(',', $this->diafan->array_column($fields, 'query'))." WHERE id=%d", array_merge(array("service_express_fields"), $this->diafan->array_column($fields, 'value'), array($id)));
								}
							}
						}
					}
					else
					{
						$request = true;
						$perhaps_add = false;
					}
				}
				else
				{
					if(! empty($_POST["category"])
					&& (
							$cat['module_name'] != $this->diafan->filter($_POST["category"], 'string', 'module_name', '')
							|| $cat['type'] != $this->diafan->filter($_POST["category"], 'string', 'type', 'element')
							|| $cat['delete_items'] != $this->diafan->filter($_POST["category"], 'integer', 'delete_items', 0)
							|| $cat['add_new_items'] != $this->diafan->filter($_POST["category"], 'integer', 'add_new_items', 0)
							|| $cat['act_items'] != $this->diafan->filter($_POST["category"], 'integer', 'act_items', 0)
							|| $cat['site_id'] != $this->diafan->filter($_POST["category"], 'integer', 'site_id', 0)
							|| $cat['cat_id'] != $this->diafan->filter($_POST["category"], 'integer', 'cat_id', 0)
							|| $cat['menu_cat_id'] != $this->diafan->filter($_POST["category"], 'integer', 'menu_cat_id', 0)
							|| $cat['count_part'] != $this->diafan->filter($_POST["category"], 'integer', 'count_part', 20)
							|| $cat['sub_delimiter'] != (! empty($_POST["category"]["sub_delimiter"]) ? $_POST["category"]["sub_delimiter"] : '|')
							|| $cat['header'] != $this->diafan->filter($_POST["category"], 'integer', 'header', 0)
						)
					)
					{ // Изменены настройки категории (описания): обновляем настройки
						if(! empty($_POST["request"]))
						{
							$fields = array(
								array( "query" => "name='%h'",          "value" => $this->diafan->filter($_POST["request"], 'string', 'category_name', '') ),
								array( "query" => "module_name='%h'",   "value" => $this->diafan->filter($_POST["category"], 'string', 'module_name', '') ),
								array( "query" => "type='%h'",          "value" => $this->diafan->filter($_POST["category"], 'string', 'type', 'element') ),
								array( "query" => "delete_items='%d'",  "value" => $this->diafan->filter($_POST["category"], 'integer', 'delete_items', 0) ),
								array( "query" => "add_new_items='%d'", "value" => $this->diafan->filter($_POST["category"], 'integer', 'add_new_items', 0) ),
								array( "query" => "act_items='%d'",     "value" => $this->diafan->filter($_POST["category"], 'integer', 'act_items', 0) ),
								array( "query" => "site_id=%d",         "value" => $this->diafan->filter($_POST["category"], 'integer', 'site_id', 0) ),
								array( "query" => "cat_id=%d",          "value" => $this->diafan->filter($_POST["category"], 'integer', 'cat_id', 0) ),
								array( "query" => "menu_cat_id=%d",     "value" => $this->diafan->filter($_POST["category"], 'integer', 'menu_cat_id', 0) ),
								array( "query" => "count_part=%d",      "value" => $this->diafan->filter($_POST["category"], 'integer', 'count_part', 20) ),
								array( "query" => "sub_delimiter='%h'", "value" => (! empty($_POST["category"]["sub_delimiter"]) ? $_POST["category"]["sub_delimiter"] : '|') ),
								array( "query" => "header='%d'",        "value" => $this->diafan->filter($_POST["category"], 'integer', 'header', 0) ),
							);
							DB::query("UPDATE {%s} SET ".implode(',', $this->diafan->array_column($fields, 'query'))." WHERE id=%d", array_merge(array("service_express_fields_category"), $this->diafan->array_column($fields, 'value'), array($cat_id)));
						}
						else
						{
							$request = true;
							$perhaps_add = true;
						}
					}
					// TO_DO: принципиально важна единообразная очередность полей для таблицы {service_express_fields} - ORDER BY sort ASC, id ASC
					if(! $rows = DB::query_fetch_all("SELECT id, name, type, params, required FROM {%s} WHERE cat_id=%d AND trash='0' ORDER BY sort ASC, id ASC", 'service_express_fields', $cat_id))
					{ // Текущая категория (описание) без полей описания: добавляем поля
						if(! empty($_POST['type']) && is_array($_POST['type']))
						{
							if(! empty($_POST["request"]))
							{
								$params = $object->save_table_variable_params();
								$arr_query = $this->diafan->array_column($params, 'query');
								$arr_value = $this->diafan->array_column($params, 'value');
								$sort = DB::query_result("SELECT MAX(sort)+1 FROM {%s} WHERE 1=1 LIMIT 1", "service_express_fields");
								foreach($_POST['type'] as $key => $type)
								{
									$name = isset($_POST["name"][$key]) ? $_POST["name"][$key] : '';
									$name = ($name ?: (isset($object->variables["main"]["type"]["select"][$type]) ? $object->variables["main"]["type"]["select"][$type] : $name));
									$query = isset($arr_query[$key]) ? $arr_query[$key] : "params='%s'";
									$value = isset($arr_value[$key]) ? $arr_value[$key] : "";
									$fields = array(
										array( "query" => "name='%h'",     "value" => $name ),
										array( "query" => "cat_id=%d",     "value" => $cat_id ),
										array( "query" => "type='%h'",     "value" => $type ),
										array( "query" => $query,          "value" => $value ),
										array( "query" => "required='%d'", "value" => (! empty($_POST["required"][$key]) ? 1 : 0) ),
										array( "query" => "sort=%d",       "value" => $sort++ ),
									);
									if($id = DB::query("INSERT INTO {%s} () VALUES ()", "service_express_fields"))
									{
										DB::query("UPDATE {%s} SET ".implode(',', $this->diafan->array_column($fields, 'query'))." WHERE id=%d", array_merge(array("service_express_fields"), $this->diafan->array_column($fields, 'value'), array($id)));
									}
								}
							}
							else
							{
								$request = true;
								$perhaps_add = $perhaps_add ?: false;
							}
						}
					}
					else
					{
						// Проверяем наличие изменений в полях категории (описания)
						$diff = false;
						$post_count = (! empty($_POST['type']) && is_array($_POST['type'])) ? count($_POST['type']) : 0;
						$rows_count = count($rows);
						$params = $object->save_table_variable_params();
						$arr_query = $this->diafan->array_column($params, 'query');
						$arr_value = $this->diafan->array_column($params, 'value');
						if($post_count != $rows_count)
						{
							$diff = true;
						}
						else
						{
							$empty_name = (isset($object->variables["main"]["type"]["select"]['empty']) ? $object->variables["main"]["type"]["select"]['empty'] : '');
							foreach($rows as $key => $row)
							{
								$name = isset($_POST['name'][$key]) ? $_POST['name'][$key] : '';
								$type = isset($_POST['type'][$key]) ? $_POST['type'][$key] : '';
								$param_value = isset($arr_value[$key]) ? $arr_value[$key] : '';
								$required = ! empty($_POST['required'][$key]) ? 1 : 0;
								$diff = (
									$row["name"] != $name
									|| $row["type"] != $type
									|| $row["params"] != $param_value
									|| $row["required"] != $required
								);
								if($diff && $row["type"] == 'empty'
								&& $row["type"] == $type && $row["params"] == $param_value && $row["required"] == $required)
								{
									if($row["name"] == $empty_name && empty($name))
									{
										$diff = false;
									}
								}
								if($diff) break;
							}
						}
						if($diff)
						{
							if(! empty($_POST["request"]))
							{ // Вносим изменения в поля категории (описания)
								$count = $post_count - $rows_count;
								if($count < 0)
								{
									$count = -$count;
									DB::query("UPDATE {%s} SET trash='%d' WHERE cat_id=%d AND trash='0' ORDER BY sort DESC, id DESC LIMIT %d", 'service_express_fields', 1, $cat_id, $count);
								}
								elseif($count > 0)
								{
									for($i=0; $i < $count; $i++)
									{
										DB::query("INSERT INTO {%s} (cat_id, trash) VALUES (%d, '%d')", "service_express_fields", $cat_id, 0);
									}
								}
								if($count <> 0)
								{
									// TO_DO: принципиально важна единообразная очередность полей для таблицы {service_express_fields} - ORDER BY sort ASC, id ASC
									$rows = DB::query_fetch_all("SELECT id, name, type, params, required FROM {%s} WHERE cat_id=%d AND trash='0' ORDER BY sort ASC, id ASC", 'service_express_fields', $cat_id);
								}
								$sort = DB::query_result("SELECT MAX(sort)+1 FROM {%s} WHERE 1=1 LIMIT 1", "service_express_fields");
								foreach($rows as $key => $row)
								{
									$type = isset($_POST['type'][$key]) ? $_POST['type'][$key] : '';
									$name = isset($_POST["name"][$key]) ? $_POST["name"][$key] : '';
									$name = ($name ?: (isset($object->variables["main"]["type"]["select"][$type]) ? $object->variables["main"]["type"]["select"][$type] : $name));
									$param_query = isset($arr_query[$key]) ? $arr_query[$key] : "params='%s'";
									$param_value = isset($arr_value[$key]) ? $arr_value[$key] : '';
									$required = ! empty($_POST['required'][$key]) ? 1 : 0;

									DB::query("UPDATE {%s} SET name='%h', type='%h', ".$param_query.", required='%d', trash='0', sort=%d  WHERE id=%d", 'service_express_fields', $name, $type, $param_value, (! empty($required) ? 1 : 0), $sort++, $row["id"]);
								}
							}
							else
							{
								$request = true;
								$perhaps_add = true;
							}
						}
					}
				}
			}
			unset($object);

			if($errors)
			{ // Выявлены ошибки в описании импорта/экспорта
				$this->result["informer"] = '<div class="error">'
					.$this->diafan->_('Есть ошибки в созданном импорте/экспорте.').'<br>'
					.$this->diafan->_('Для отмены изменений достаточно %sперезагрузить текущую страницу%s.', '<a href="#" onclick="window.location.href=document.location; return false;">', '</a>')
					.'</div>';
				$this->result["button"] = $this->diafan->_('Продолжить');
				$this->result["result"] = 'error';
			}
			elseif($request)
			{ // Необходимо подтвержение сохранения изменений описания импорта/экспорта
				Custom::inc('adm/includes/edit.php');
				$object = new Edit_admin($this->diafan);
				ob_start();
				// category_name
				$key = "category_name"; $name = $this->diafan->_("Название импорта");
				$value = $cat_name;
				$help = "Краткое описание файла импорта (например, «Импорт товаров», «Импорт цен» и т. д.).";
				$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
				$disabled = false;
				$attr = $class = "";
				$maxlength = 0;
				$object->show_table_tr_text($key, $name, $value, $help, $disabled, $attr, $class, $maxlength);
				// Категория (описание) и поля описания существуют: предлагаем создать новую категорию (описание)
				if($perhaps_add)
				{
					$key = "new_category"; $name = $this->diafan->_("Создать новый импорт");
					$value = $perhaps_add ? 1 : 0;
					$help = "Если отметить, то текущие настройки будут добавлены, как новая запись. Иначе текущие настройки буду пересохранены.";
					$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>';
					$disabled = false;
					$attr = $class = "";
					$object->show_table_tr_checkbox($key, $name, $value, $help, $disabled, $attr, $class);
				}
				// contents
				$module_contents = ob_get_contents();
				ob_end_clean();
				$this->result["request"] = $module_contents;
				$this->result["button"] = $this->diafan->_('Продолжить');
				$this->result["informer"] = '<div class="commentary">'
					.$this->diafan->_('Присутствуют изменения в описании импорта.').'<br>'.($perhaps_add
						? $this->diafan->_('Сохранить изменения для текущего описания или создать новое?')
						: $this->diafan->_('Сохранить изменения для описания?')).'<br>'
					.$this->diafan->_('Для отмены изменений достаточно %sперезагрузить текущую страницу%s.', '<a href="#" onclick="window.location.href=document.location; ; return false;">', '</a>')
					.'</div>';
				if(! empty($_POST["only_save"]))
				{
					$this->result["only_save"] = $_POST["only_save"];
				}
				$this->result["result"] = 'success';
				unset($object);
			}
			else
			{ // Выбрано описание. Инициализация импорта/экспорта.
				if(! empty($new_category))
				{ // Была добавлена новая категория
					// TO_DO: единообразная очередность полей для таблицы {service_express_fields_category} - ORDER BY sort ASC, id ASC
					if($cats = DB::query_fetch_key("SELECT id, name, module_name, site_id, cat_id, menu_cat_id, type, delete_items, add_new_items, act_items, header, sub_delimiter, count_part FROM {%s_category} WHERE trash='0' AND module_name='%s' ORDER BY sort ASC, id ASC", 'service_express_fields', $module_name, "id"))
					{
						$c_id = array_key_exists($cat_id, $cats) ? $cat_id : key($cats);
					}
					else $c_id = 0;
					$cats = array(0 => 'Новый импорт') + $cats;
					Custom::inc('adm/includes/edit.php');
					$object = new Edit_admin($this->diafan);
					ob_start();
					// fields_cat_id
					$key = "fields_cat_id"; $name = $this->diafan->_("Выберите сохраненный импорт или создайте новый");
					$value = $c_id;
					$help = "Выберите ранее созданный импорт или создайте новый.";
					$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>'
						.' <i class="tooltip fa fa-gear" title="'.$this->diafan->_('Настроить параметры импорта').'"></i>';
					$disabled = false;
					$options = $cats;
					$attr = 'unit_id="fields_cat_param"'; $class = "box_toggle";
					$object->show_table_tr_select($key, $name, $value, $help, $disabled, $options, $attr, $class);
					// contents
					$module_contents = ob_get_contents();
					ob_end_clean();
					unset($object);
					if($cat_id != $c_id)
					{
						// Ошибка: не найдено созданное описание импорта
							$this->result["informer"] = '<div class="error">'.$this->diafan->_('Не выбран модуль для импорта.').'</div>';
							$this->result["button"] = $this->diafan->_('Импортировать');
							$this->result["result"] = 'error';
							return;
					}
					$this->result["category"] = $module_contents;
				}
				if(! empty($_POST["only_save"]))
				{ // выбрано только сохранение описания импорта/экспорта
					$this->result["informer"] = '<div class="ok">'.$this->diafan->_('Описание импорта/экспорта сохранено.').'</div>';
					$this->result["button"] = $this->diafan->_('Импортировать');
					$this->result["result"] = 'success';
				}
				else
				{ // инициализация импорта
					$tables = $this->diafan->_service->get_tables(false, true);
					$this->result["informer"] = '<div class="ok">'.$this->diafan->_('Импорт определен.').'</div>';
					Custom::inc('modules/service/admin/service.admin.express.import.php');
					$object = new Service_admin_express_import($this->diafan);
					if(count($object->defer_files) > 0)
					{
						$this->result["informer"] .= '<br /><div class="commentary">'.$this->diafan->_('Инициализация чтения загруженного файла импорта.').' '.$this->diafan->_('Дождитесь окончания процесса ...').' <img src="'.BASE_PATH.'adm/img/loading.gif">'.'</div>';
						$this->result["button"] = $this->diafan->_('Импортировать');
						$this->result["action"] = 'defer_files';
						$this->result["result"] = 'continue';
					}
					elseif(! empty($tables[self::TABLE_NAME]))
					{
						$this->result["informer"] .= '<br /><div class="commentary">'.$this->diafan->_('Инициализация импорта.').' '.$this->diafan->_('Дождитесь окончания процесса ...').' <img src="'.BASE_PATH.'adm/img/loading.gif">'.'</div>';
						$this->result["button"] = $this->diafan->_('Импортировать');
						$this->result["action"] = 'import_files';
						$this->result["result"] = 'continue';
					}
					else
					{
						$object->read_file(true, true);
						$object->errors = '- '.$this->diafan->_('не загружен файл');
						$this->result["informer"] = '<div class="error">'.$this->diafan->_('Не загружен файл, содержащий записи для импорта.').'</div>';
						$this->result["button"] = $this->diafan->_('Импортировать');
						$this->result["result"] = 'error';
						$this->result["redirect"] = $this->diafan->get_admin_url().'?'.'cat='.$cat_id.'&'.$this->diafan->uid();
					}
					unset($object);
				}
			}
		}
		else
		{ // Ошибка: не выбран модуль
			$this->result["informer"] = '<div class="error">'.$this->diafan->_('Не выбран модуль для импорта.').'</div>';
			$this->result["button"] = $this->diafan->_('Импортировать');
			$this->result["result"] = 'error';
			return;
		}
	}

	/**
	 * Загрузка отложенных файлов импорта
	 *
	 * @return void
	 */
	private function load_defer_files()
	{
		Custom::inc('modules/service/admin/service.admin.express.import.php');
		$object = new Service_admin_express_import($this->diafan);
		$object->prepare_config();
		$object->read_file(true, true);

		if(count($object->defer_files) > 0)
		{
			$files = $object->defer_files; $file = reset($files);
			$this->result["informer"] = '<div class="commentary">'.$this->diafan->_("Загрузка файла <b>%s</b>: прочитано записей <b>%s</b>.", (isset($file["basename"]) ? $file["basename"] : ''), (int) $object->part).'<br />'.$this->diafan->_('Дождитесь окончания процесса ...').' <img src="'.BASE_PATH.'adm/img/loading.gif">'.'</div>';
		}
		else
		{
			if(count($object->errors) > 0)
			{
				$this->result["informer"] = '<div class="error">'.$object->diafan->_("Выявлены ошибки во время загрузки данных").':<br />'.implode('<br />', $object->errors).'</div>';
				$object->errors = NULL;
			}
			else
			{
				$this->result["informer"] = '<div class="ok">'.$this->diafan->_('Загруженный файл импорта прочитан.').'</div>';
				$this->result["informer"] .= '<br /><div class="commentary">'.$this->diafan->_('Инициализация импорта.').' '.$this->diafan->_('Дождитесь окончания процесса ...').' <img src="'.BASE_PATH.'adm/img/loading.gif">'.'</div>';
			}
		}
		if($object->ftell || count($object->defer_files) > 0)
		{
			$this->result["action"] = 'defer_files';
			$this->result["result"] = 'continue';
		}
		else
		{
			$this->result["action"] = 'import_files';
			$this->result["result"] = 'continue';
		}
		unset($object);
	}

	/**
	 * Инициализация импорта
	 *
	 * @return void
	 */
	private function import_files()
	{
		$cat_id = $this->diafan->filter($_POST, 'int', 'cat_id', 0);
		Custom::inc('modules/service/service.express.inc.php');
		$object = new Service_express_inc($this->diafan, $cat_id);
		$result = $object->import_init($cat_id);

		$errors_log = $object->import_get_log();
		if($errors_log && $result != 'next')
		{
			$this->result["informer"] = '<div class="error">'
				.$this->diafan->_('Загруженный файл импортирован c ошибками. Все ошибки записаны в %sлог-файл в формате CSV и доступны для скачивания%s: первая колонка указывает на номер строки файла импорта, вторая колонка содержит описание ошибки, последующие колонки повторяют содержания колонок файла импорта.', '<a id="file_errors_log" class="action" href="'.$this->diafan->get_admin_url('page', 'step').'step1'.'/'.'?log='.$this->diafan->uid().'" download confirm="'.$this->diafan->_("Хотите скачать лог ошибок импорта?").'" action="download">', '</a>').'</div>';
			$this->result["result"] = 'success';
		}
		else
		{
			switch($result)
			{
				case 'success':
					$this->result["informer"] = '<div class="ok">'.$this->diafan->_('Загруженный файл импортирован успешно.').'</div>';
					$this->result["result"] = 'success';

					$import_detect = $object->import_detect($cat_id);
					if($import_detect & IMPORT_ALL)
					{
						$this->result["informer"] .= '<br />';

						$this->result["informer"] .= '
						<br>
						<form method="post" action="'.$this->diafan->get_admin_url('page', 'step').'step1/'.'">
							<input type="hidden" value="" name="import_action">
						'.$this->diafan->_('Результаты импорта').': &nbsp; &nbsp;';
						if($import_detect & IMPORT_ACTIVE)
						{
							$this->result["informer"] .= '
							<input type="submit" class="btn btn_small import_button" rel="act_import" value="'.$this->diafan->_('Опубликовать на сайте').'" > &nbsp; &nbsp;';
						}
						if($import_detect & IMPORT_DEACTIVE)
						{
							$this->result["informer"] .= '
							<input type="submit" class="btn btn_small import_button" rel="deact_import" value="'.$this->diafan->_('Скрыть на сайте').'" > &nbsp; &nbsp;';
						}
						$this->result["informer"] .= '
							<input type="submit" class="btn btn_small import_button" rel="remove_import" value="'
					   .$this->diafan->_('Удалить').'" >
						</form>';
					}
					break;

				case 'next':
					$max = $object->import_get_max();
					$pos = $object->import_get_pos();
					$finish = $object->import_get_finish();

					$commentary = '';
					$spinner = ' <img src="'.BASE_PATH.'adm/img/loading.gif">';

					// if($pos < $max) $progress_bar = ' '.ceil($pos*100/$max).' %';
					// else $progress_bar = '';
					if($pos < $max)
					{
						$commentary .= $this->diafan->_('Импорт загруженного файла.').' '.$this->diafan->_('Дождитесь окончания процесса ...');
						$commentary .= $spinner;
						$progress = sprintf('%1.2f', ($pos*100/$max)); $p = explode('.', $progress);
						$progress = (count($p) == 2 ? array_shift($p).'.<span style="zoom: .85;">'.array_shift($p).'</span>' : $progress);
						$commentary .=  ' '.$progress.' %';
					}
					else
					{
						$commentary .= $this->diafan->_('Завершение процесса импорта.').' '.$this->diafan->_('Дождитесь окончания процесса ...');
					}

					if(! empty($finish))
					{
						foreach($finish as $key => $value)
						{
							if(! is_array($value) || empty($value["title"]))
								continue;
							$commentary .= '<br>';
							$commentary .= $this->diafan->_($value["title"]);
							$commentary .= '... '.(empty($value["result"]) ? $spinner : $this->diafan->_('завершено'));
						}
					}

					$this->result["informer"] = '<div class="commentary">'
						.$commentary
					.'</div>';
					$this->result["result"] = 'continue';
					break;

				case 'busy':
					$this->result["informer"] = '<div class="commentary">'
						.$this->diafan->_('Импорт %sзаблокирован%s другим пользователем.%sДождитесь окончания.%sПросто %sперезагрузите страницу%s через некоторое время.%sЕсли к тому моменту импорт будет разблокирован, Вы сможете продолжить работу.', '<b>', '</b>', ' ', '<br />', '<a href="#" onclick="window.location.href=document.location; return false;">', '</a>', ' ')
						.'<br /><br />'
						.$this->diafan->_('Если длительное время блокировка не снимается и Вы уверены, что другие пользователи не инициировали импорт, то, возможно, во время выполнения процесса произошла ошибка, которая препятствовала снятию блокировки процесса. В таком случае Вы можете %sпринудительно снять блокировку%s.', '<a href="'.$this->diafan->get_admin_url('page', 'step').'step2'.'/'.'?no_busy'.'">', '</a>')
						.'</div>';
					$this->result["result"] = 'success';
					break;

				case 'empty':
				case false:
				default:
					$this->result["informer"] = '';
					$this->result["result"] = 'success';
					$this->result["redirect"] = $this->diafan->get_admin_url('page', 'step').'step1'.'/';
					break;
			}
		}
		unset($object);
	}

	/**
	 * Смена названия модуля в экспорте
	 *
	 * @return void
	 */
	private function modules_export_change()
	{
		// fields_cat_id
		$disabled = false;
		if(! $module_name = preg_replace('/[^a-z0-9_]+/', '', $_POST["module_name"]))
		{
			$disabled = true;
		}

		$modules = $this->diafan->_service->modules_express();
		$cat = false;
		foreach ($modules as $key => $value)
		{
			if(empty($value["name"]) || $value["name"] != $module_name)
			{
				continue;
			}
			$cat = $key;
			break;
		}
		if($cat === false)
		{
			return;
		}

		Custom::inc('adm/includes/edit.php');
		$object = new Edit_admin($this->diafan);

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
			$key = "fields_cat_id"; $name = $this->diafan->_("Выберите описание экспорта");
			$value = $cat_id;
			$help = "Выберите ранее созданный импорт или создайте новый.";
			$help = ' <i class="tooltip fa fa-question-circle" title="'.str_replace('"', '&quot;', $this->diafan->_($help)).'"></i>'
				.' <i class="tooltip fa fa-gear" title="'.$this->diafan->_('Настроить параметры импорта').'"></i>';
			$disabled = false;
			$options = $cats;
			$attr = 'unit_id="fields_cat_param"'; $class = "box_toggle";
			ob_start(); $object->show_table_tr_select($key, $name, $value, $help, $disabled, $options, $attr, $class); $module_contents = ob_get_contents(); ob_end_clean();
		}
		else
		{
			$key = "fields_cat_id";
			$module_contents = '
				<div class="unit box_toggle" id="'.$key.'">'
					.'<div class="error">'
						.$this->diafan->_('Для данного модуля необходимо создать описание %sв разделе "Сохраненные импорт/экспорт"%s.', '<a href="'.BASE_PATH_HREF.'service/express/'.'fields/'.'">', '</a>')
					.'</div>'
				.'</div>';
		}
		$this->result['result'][$key] = $module_contents;

		$this->result['result']["fields_cat_edit"] = '
				<a id="fields_cat_edit" class="btn btn_blue btn_small edit"'.(! $cat_id ? ' hide' : '').'" href="'.$this->url.'fields/'.(! empty($cats) ? 'cat'.$cat_id.'/' : '').'" title="'.(! empty($cats) ? $this->diafan->_("Редактировать описание импорта/экспорта") : $this->diafan->_("Добавить описание импорта/экспорта")).'">'.(! empty($cats) ? $this->diafan->_("Изменить правило экспорта") : $this->diafan->_("Добавить правило экспорта")).'</a>';

		unset($object);

		// mode_express
		$cat = $cat + 1;
		$url = $this->diafan->get_admin_url('cat', 'page', 'step').'cat'.$cat.'/';
		$this->result['curLoc'] = $url;
		if(empty($_SESSION[self::CLASS_NAME]["cat_export_choice"]["cat"])
		|| $_SESSION[self::CLASS_NAME]["cat_export_choice"]["cat"] != $cat)
		{
			$_SESSION[self::CLASS_NAME]["cat_export_choice"]["cat"] = $cat;
			if(isset($_SESSION[self::CLASS_NAME]["cat_export_choice"]["desc"]))
			{
				unset($_SESSION[self::CLASS_NAME]["cat_export_choice"]["desc"]);
			}
		}
	}

	/**
	 * Смена описания экспорта
	 *
	 * @return void
	 */
	private function fields_cat_id_export_change()
	{
		if(! $id = $this->diafan->filter($_POST, 'int', 'id'))
		{
			return;
		}
		$cat = false;
		if(! $cat = DB::query_fetch_array("SELECT type, site_id, cat_id, menu_cat_id, delete_items, add_new_items, act_items, header, sub_delimiter, count_part FROM {%s} WHERE id=%d LIMIT 1", "service_express_fields_category", $id))
		{
			return;
		}
		$cat_id = $id ?: 0;

		$module_name = ! empty( $_POST["module_name"] ) ? preg_replace('/[^a-z0-9_]+/', '', $_POST["module_name"]) : '';
		$modules = $this->diafan->_service->modules_express();
		$cat = false;
		foreach ($modules as $key => $value)
		{
			if(empty($value["name"]) || $value["name"] != $module_name)
			{
				continue;
			}
			$cat = $key;
			break;
		}
		if($cat !== false)
		{
			$cat++;
			$url = $this->diafan->get_admin_url('cat', 'page', 'step').'cat'.$cat.'/';
			if($cat_id)
			{
				$url = $this->diafan->params_append($url, array('cat' => $cat_id));
			}
			$this->result['curLoc'] = $url;
			$_SESSION[self::CLASS_NAME]["cat_export_choice"]["cat"] = $cat;
			$_SESSION[self::CLASS_NAME]["cat_export_choice"]["desc"] = $cat_id;
			$this->result['href'] = $this->url.'fields/cat'.$cat_id.'/';
		}
	}

	/**
	 * Инициализация экспорта
	 *
	 * @return void
	 */
	private function export_data()
	{
		if($module_name = preg_replace('/[^a-z0-9_]+/', '', $_POST["modules"]))
		{
			$modules = $this->diafan->_service->modules_express();
			foreach ($modules as $key => $value)
			{
				if(empty($value["name"]) || $value["name"] != $module_name)
				{
					continue;
				}
				$module_name = $value["title"];
				break;
			}
		}
		if(! $cat_id = $this->diafan->filter($_POST, 'int', 'fields_cat_id', 0))
		{
			$this->result["informer"] = '<div class="error">'
				.$this->diafan->_('Не выбрано описание экспорта.').'</div>';
			$this->result["result"] = 'error';
			return;
		}
		Custom::inc('modules/service/service.express.inc.php');
		$object = new Service_express_inc($this->diafan, $cat_id);
		$object->export_set_delimiter($this->diafan->filter($_POST, 'string', 'delimiter', ''));
		$object->export_set_enclosure($this->diafan->filter($_POST, 'string', 'enclosure', ''));
		$object->export_set_encoding($this->diafan->filter($_POST, 'string', 'encoding', ''));
		$result = $object->export_init($cat_id);

		switch($result)
		{
			case 'success':
				$zip = ! empty($_POST["zip"]);
				$this->result["informer"] = '<div class="ok">'.$this->diafan->_('Экспорт данных модуля'.($module_name ? ' «'.$module_name.'»' : '').' завершен.').'</div>'
					.'<a id="file_export" class="action" href="'.$this->diafan->get_admin_url('page', 'step').'?download='.$this->diafan->uid().($zip ? '&zip' : '').'" download action="download"></a>';
				$this->result["result"] = 'success';
				break;

			case 'next':
				$max = $object->export_get_max();
				$pos = $object->export_get_pos();

				$commentary = '';
				$spinner = ' <img src="'.BASE_PATH.'adm/img/loading.gif">';

				// if($pos < $max) $progress_bar = ' '.ceil($pos*100/$max).' %';
				// else $progress_bar = '';
				if($pos < $max)
				{
					$commentary .= $this->diafan->_('Экспорт данных модуля'.($module_name ? ' «'.$module_name.'»' : '').'.').' '.$this->diafan->_('Дождитесь окончания процесса ...');
					$commentary .= $spinner;
					$progress = sprintf('%1.2f', ($pos*100/$max)); $p = explode('.', $progress);
					$progress = (count($p) == 2 ? array_shift($p).'.<span style="zoom: .85;">'.array_shift($p).'</span>' : $progress);
					$commentary .=  ' '.$progress.' %';
				}
				else
				{
					$commentary .= $this->diafan->_('Экспорт данных модуля'.($module_name ? ' «'.$module_name.'»' : '').' завершен.');
				}

				$this->result["informer"] = '<div class="commentary">'
					.$commentary
				.'</div>';
				$this->result["result"] = 'continue';
				break;

			case 'busy':
				$this->result["informer"] = '<div class="commentary">'
					.$this->diafan->_('Экспорт %sзаблокирован%s другим пользователем.%sДождитесь окончания.%sПросто %sперезагрузите текущую страницу%s через некоторое время.%sЕсли к тому моменту экспорт будет разблокирован, Вы сможете продолжить работу.', '<b>', '</b>', ' ', '<br />', '<a href="#" onclick="window.location.href=document.location; return false;">', '</a>', ' ')
					.'<br /><br />'
					.$this->diafan->_('Если длительное время блокировка не снимается и Вы уверены, что другие пользователи не инициировали экспорт, то, возможно, во время выполнения процесса произошла ошибка, которая препятствовала снятию блокировки процесса. В таком случае Вы можете %sпринудительно снять блокировку%s.', '<a href="'.$this->diafan->get_admin_url('page', 'step').'?no_busy'.'">', '</a>')
					.'</div>';
				$this->result["result"] = 'success';
				break;

			case 'empty':
				$this->result["informer"] = '<div class="error">'
					.$this->diafan->_('Записи, подлежащие экспорту, отсутствуют.').'</div>';
				$this->result["result"] = 'error';
				break;

			case false:
				$this->result["informer"] = '<div class="error">'
					.$this->diafan->_('Не найдены записи, подлежащие экспорту. Проверьте права доступа используемой учетной записи к запрашиваемой информации. Или проверьте запрашиваемый раздел сайта в настройках описания экспорта. Возможно была изменена привязка модуля к странице сайта. Например, Вами был переустановлен модуль. Для этого перейдите %sна закладку "Сохраненные импорт/экспорт"%s, далее в списке описаний импорта/экспорта найдите требуемое описание и нажмите на кнопку "Изменить настроки". В большистве случаев достаточно пересохранить такие настройки. То есть в открывшейся странице достаточно нажмать кнопку "Сохранить".', '<a href="'.BASE_PATH_HREF.'service/express/fields/'.'" target="_blank">', '</a>').'</div>';
				$this->result["result"] = 'error';
				break;

			default:
				$this->result["informer"] = '';
				$this->result["result"] = 'success';
				$this->result["redirect"] = $this->diafan->get_admin_url('page', 'step');
				break;
		}
	}
}

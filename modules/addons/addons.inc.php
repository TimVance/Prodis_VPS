<?php
/**
 * Подключение модуля
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
 * Addons_inc
 */
class Addons_inc extends Diafan
{

	const MODULE_NAME = 'addons';
	const PREFIX = 'addon';
	const COUNT = 20;

	/**
	 * @var object вспомогательный объект модуля
	 */
	private $account = null;

	/**
	 * @var integer метка времени
	 */
	static private $timemarker = 0;

	/**
	 * @var string путь до временной директории относительно корня сайта
	 */
	private $dir_path = 'tmp/addons';

	/**
	 * @var string путь до временной директории относительно корня сайта
	 */
	public $return_path = 'return/custom';

	/**
	 * Конструктор класса
	 *
	 * @return void
	 */
	public function __construct(&$diafan)
	{
		parent::__construct($diafan);
		Custom::inc('modules/account/admin/account.admin.inc.php');
		$this->account = new Account_admin_inc($this->diafan);
		self::$timemarker = mktime(23, 59, 0, date("m"), date("d"), date("Y")); // кешируем на сутки
		$this->diafan->set_time_limit();
	}

	/**
	 * Обновляет список дополнений
	 *
	 * @param boolean $upgrade принудительное обновление
	 * @return mixed
	 */
	public function update($upgrade = false)
	{
		if($upgrade)
		{
			$this->diafan->_cache->delete("", self::MODULE_NAME);
		}

		$cache_meta = array(
			'time' => self::$timemarker,
			'name' => __METHOD__,
			'addr' => getenv('REMOTE_ADDR', true) ?: getenv('REMOTE_ADDR'),
			'host' => getenv('HTTP_HOST', true) ?: getenv('HTTP_HOST'),
			'token' => $this->account->token,
		);

		if(! $result = $this->diafan->_cache->get($cache_meta, self::MODULE_NAME, CACHE_GLOBAL))
		{
			// if(! $upgrade)
			// {
			// 	$url = $this->account->uri('addons', 'timeedit');
			// 	if($result = $this->diafan->_client->request($url, $this->account->token))
			// 	{
			// 		$this->diafan->_client->get_attributes($result, 'timeedit');
			// 		$result['timeedit'] = (int) $result['timeedit'];
			// 		$timeedit = (int) DB::query_result("SELECT MAX(timeedit) FROM {".self::MODULE_NAME."} WHERE 1=1");
			// 		if($result['timeedit'] && $timeedit && $result['timeedit'] <= $timeedit)
			// 		{
			// 			$this->diafan->_cache->save(true, $cache_meta, self::MODULE_NAME, CACHE_GLOBAL);
			// 			return false;
			// 		}
			// 	}
			// }

			$url = $this->account->uri('addons', 'list'); $page = '';
			do
			{
				if(! $result = $this->diafan->_client->request($url.$page.'/', $this->account->token, array('count' => self::COUNT)))
				{
					break;
				}
				if(empty($result["rows"]))
				{
					break;
				}
				$offset = (int) $this->diafan->_client->paginator->offset + (int) $this->diafan->_client->paginator->nastr;
				$page = sprintf($this->diafan->_client->paginator->urlpage, $this->diafan->_client->paginator->next_page);

				$result = $result["rows"];

				foreach ($result as &$row)
				{
					$this->diafan->_client->get_attributes(
						$row, 'id', 'name', 'timeedit', 'anons', 'text', 'downloaded', 'install', 'file_rewrite', 'link', 'image', 'sort', 'author', 'cat_name',
						'price', 'price_month', 'available_subscription', 'buy', 'subscription',
						'auto_subscription'
					);

					$author = array("link" => '', "name" => '');
					if(! empty($row["author"]) && is_array($row["author"]))
					{
						$author = array(
							"link" => $this->diafan->filter($row["author"], 'string', 'link', ''),
							"name" => $this->diafan->filter($row["author"], 'string', 'name', ''),
						);
					}
					$row["id"] = $this->diafan->filter($row, 'int', 'id', 0);
					$row["name"] = $this->diafan->filter($row, 'string', 'name', '');
					$row["timeedit"] = $this->diafan->filter($row, 'int', 'timeedit', 0);
					// $row["anons"] = $this->diafan->filter($row, 'string', 'anons', '');
					// $row["text"] = $this->diafan->filter($row, 'string', 'text', '');
					$row["downloaded"] = $this->diafan->filter($row, 'int', 'downloaded', 0);
					// $row["install"] = $this->diafan->filter($row, 'string', 'install', '');
					$row["file_rewrite"] = $this->diafan->filter($row, 'string', 'file_rewrite', '');
					$row["link"] = $this->diafan->filter($row, 'string', 'link', '');
					$row["image"] = $this->diafan->filter($row, 'string', 'img', '');
					$row["author"] = ! empty($author["name"]) ? $author["name"] : 'Diafan';
					$row["author_link"] = ! empty($author["link"]) ? $author["link"] : 'https://www.diafan.ru/';
					$row["sort"] = $this->diafan->filter($row, 'int', 'sort', 0);
					$row["cat_name"] = $this->diafan->filter($row, 'string', 'cat_name', '');

					$row["price"] = $this->diafan->filter($row, 'float', 'price', 0);
					$row["price_month"] = $this->diafan->filter($row, 'float', 'price_month', 0);
					$row["available_subscription"] = $this->diafan->filter($row, 'int', 'available_subscription', 0);
					$row["buy"] = $this->diafan->filter($row, 'int', 'buy', 0);
					$row["subscription"] = $this->diafan->filter($row, 'int', 'subscription', 0);
					$row["auto_subscription"] = $this->diafan->filter($row, 'int', 'auto_subscription', 0);

					$row["available_subscription"] = $row["available_subscription"] ? 1 : 0;
					$row["buy"] = $row["buy"] ? 1 : 0;
					$row["auto_subscription"] = $row["auto_subscription"] ? 1 : 0;

					$row["name"] = htmlspecialchars_decode($row["name"]);       //$row["name"] = html_entity_decode($row["name"]);
					$row["anons"] = htmlspecialchars_decode($row["anons"]);     //$row["anons"] = html_entity_decode($row["anons"]);
					$row["text"] = htmlspecialchars_decode($row["text"]);       //$row["text"] = html_entity_decode($row["text"]);
					$row["install"] = htmlspecialchars_decode($row["install"]); //$row["install"] = html_entity_decode($row["install"]);
					$row["author"] = htmlspecialchars_decode($row["author"]);   //$row["author"] = html_entity_decode($row["author"]);
					$row["cat_name"] = htmlspecialchars_decode($row["cat_name"]);       //$row["cat_name"] = html_entity_decode($row["cat_name"]);
					unset($author);
				}

				$addon_ids = $this->diafan->array_column($result, 'id');
				$rows = DB::query_fetch_key_value("SELECT id, addon_id FROM {".self::MODULE_NAME."} WHERE addon_id IN(%s)", implode(",", $addon_ids), "addon_id", "id");
				foreach($result as $value)
				{
					if(! empty($rows[$value["id"]]))
					{
						DB::query("UPDATE {".self::MODULE_NAME."} SET name='%s', timeedit=%d, anons='%s', text='%s', downloaded=%d, install='%s', file_rewrite='%h', link='%h', image='%h', author='%s', author_link='%h', sort=%d, cat_name='%s', price=%f, price_month=%f, available_subscription='%d', buy='%d', subscription=%d, auto_subscription='%d', import_update='%s' WHERE id=%d", $value["name"], $value["timeedit"], $value["anons"], $value["text"], $value["downloaded"], $value["install"], $value["file_rewrite"], $value["link"], $value["image"], $value["author"], $value["author_link"], $value["sort"], $value["cat_name"], $value["price"], $value["price_month"], ($value["available_subscription"] ? 1 : 0), ($value["buy"] ? 1 : 0), $value["subscription"], ($value["auto_subscription"] ? 1 : 0), '1', $rows[$value["id"]]);
					}
					else
					{
						DB::query("INSERT INTO {".self::MODULE_NAME."} (addon_id, custom_id, name, anons, text, install, file_rewrite, link, image, author, author_link, sort, cat_name, price, price_month, available_subscription, buy, subscription, auto_subscription, downloaded, timeedit, custom_timeedit, import_update) VALUES (%d, %d, '%s', '%s', '%s', '%s', '%h', '%h', '%h', '%s', '%h', %d, '%s', %f, %f, '%d', '%d', %d, '%d', %d, %d, %d, '%s')", $value["id"], 0, $value["name"], $value["anons"], $value["text"], $value["install"], $value["file_rewrite"], $value["link"], $value["image"], $value["author"], $value["author_link"], $value["sort"], $value["cat_name"], $value["price"], $value["price_month"], ($value["available_subscription"] ? 1 : 0), ($value["buy"] ? 1 : 0), $value["subscription"], ($value["auto_subscription"] ? 1 : 0), $value["downloaded"], $value["timeedit"], 0, '1');
					}
				}
			}
			while($offset <= (int) $this->diafan->_client->paginator->nen);
			if(empty($this->diafan->_client->errors))
			{
				DB::query("DELETE FROM {".self::MODULE_NAME."} WHERE import_update<>'%s'", '1');
				DB::query("UPDATE {".self::MODULE_NAME."} SET import_update='%s' WHERE import_update<>'%s'", '0', '0');
			}
			$this->diafan->_cache->save(true, $cache_meta, self::MODULE_NAME, CACHE_GLOBAL);
			return empty($this->diafan->_client->errors) ? true : $this->diafan->_client->errors;
		}
		return false;
	}

	/**
	 * Инсталлирует дополнения
	 *
	 * @param mixed $array идентификатор дополнения или массив идентификаторов дополнений
	 * @param boolean $sql выполняет дополнительные запросы к базе данных
	 * @return mixed(boolean|array)
	 */
	public function install($array, $sql = false)
	{
		if(! is_array($array))
		{
			$array = array($array);
		}
		foreach($array as $key => $name)
		{
			if(! empty($name)) continue;
			unset($array[$key]);
		}
		if(empty($array))
		{
			return false;
		}

		$ids = array();
		foreach($array as $value)
		{
			$value = (int) preg_replace("/\D/", "", $value);
			if(empty($value)) continue;
			$ids[] = $value;
		}

		if(empty($ids))
		return false;

		$names = Custom::names();
		foreach($names as $key => $name) $names[$key] = "'".$name."'";
		$rows = DB::query_fetch_key("SELECT e.*, e.id as id, addon_id, IFNULL(c.id, 0) as `custom.id`, IFNULL(c.name, '') as `custom.name`, IF (c.id > 0 AND c.name IN (".implode(", ", $names)."), '1', '0') AS act FROM {%s} AS e LEFT JOIN {custom} AS c ON c.id=e.custom_id WHERE e.id IN (%s)", self::MODULE_NAME, implode(',', $ids), "id");

		$errors = array(); // лог ошибок

		$links = array();
		if($rows)
		{
			$ids = $this->diafan->array_column($rows, 'addon_id');
			$url = $this->account->uri('addons', 'download');
	    $param = array("ids" => implode(",", $ids));
			if($response = $this->diafan->_client->request($url, $this->account->token, $param))
			{
				$links = ! empty($response["links"]) ? $response["links"] : array();
				foreach($links as $key => $item)
				{
					$this->diafan->_client->get_attributes($item, 'link', 'name');
					if(empty($item["link"]))
					{
						unset($links[$key]);
						continue;
					}
					if(! $file_path = $this->download($item['link']))
					{
						if(! empty($this->diafan->_client->errors)) { $errors = array_merge($errors, $this->diafan->_client->errors); }
						unset($links[$key]);
						continue;
					}
					$item["file_path"] = $file_path;
					$links[$key] = $item;
				}
			}
			elseif(! empty($this->diafan->_client->errors)) { $errors = array_merge($errors, $this->diafan->_client->errors); }
		}

		$names = array();
		foreach($rows as $row)
		{
			$name = $row["custom.name"];
			$id = $row["addon_id"];
			$dir_path = 'custom/' . $name;

			if(! empty($row["custom.id"]) && ! empty($name) && is_dir(ABSOLUTE_PATH.$dir_path))
			{
				if($row["act"])
				{
					continue;
				}
				else
				{
					$names[] = $name;
					continue;
				}
			}

			if (empty($links[$id]['file_path']))
			{
				continue;
			}
			$file_path = $links[$id]['file_path'];
			if(file_exists(ABSOLUTE_PATH.$file_path))
			{
				$name = false;
				if(! empty($row["custom.name"]))
				{
					$name = $row["custom.name"];
				}
				else
				{
					$name = pathinfo(ABSOLUTE_PATH.$file_path, PATHINFO_FILENAME);
					$name = $this->generate_name($id, $name);
				}

				if ($name && $this->diafan->_custom->import($file_path, $name))
				{
					if(empty($row["custom.id"]))
					{
						$row["anons"] = ! empty($row["anons"]) ? $row["anons"] : $row["text"];
						$row["custom.id"] = DB::query("INSERT INTO {custom} (name, created, text, current) VALUES ('%s', %d, '%s', '1')", $name, time(), $row["anons"]);
						$names[] = $name;
					}
					else
					{
						$names[] = $name;
					}
					DB::query("UPDATE {%s} SET custom_id=%d, custom_timeedit=%d WHERE id=%d", self::MODULE_NAME, $row["custom.id"], $row["timeedit"], $row["id"]);
				}
				unlink($file_path);
			}
		}
		foreach($names as $key => $name)
		{
			if(! empty($name)) continue;
			unset($names[$key]);
		}
		if(! empty($names))
		{
			$this->diafan->_custom->set($names, true, $sql);
			if($sql)
			{
				$modules = $this->diafan->_custom->get_modules($names);
				$module_names = array();
				foreach($modules as $key => $module)
				{
					if(! empty($module["installed"])) continue;
					$module_names[] = $key;
				}
				$this->diafan->_custom->set_modules($module_names, true, $names);
			}
		}

		return $errors ?: true;
	}

	/**
	 * Деинсталлирует дополнения
	 *
	 * @param mixed $array идентификатор дополнения или массив идентификаторов дополнений
	 * @param boolean $sql выполняет дополнительные запросы к базе данных
	 * @return boolean
	 */
	public function uninstall($array, $sql = false)
	{
		if(! is_array($array))
		{
			$array = array($array);
		}
		foreach($array as $key => $name)
		{
			if(! empty($name)) continue;
			unset($array[$key]);
		}
		if(empty($array))
		{
			return false;
		}

		$ids = array();
		foreach($array as $value)
		{
			$value = (int) preg_replace("/\D/", "", $value);
			if(empty($value)) continue;
			$ids[] = $value;
		}

		if(empty($ids))
		return false;

		$names = DB::query_fetch_key_value("SELECT c.id as id, IFNULL(c.name, '') as name FROM {%s} AS e LEFT JOIN {custom} AS c ON c.id=e.custom_id WHERE c.id IS NOT NULL AND e.id IN (%s)", self::MODULE_NAME, implode(',', $ids), "id", "name");

		$result = false;
		foreach($names as $key => $name)
		{
			if(! empty($name)) continue;
			unset($names[$key]);
		}
		if(! empty($names))
		{
			if($sql)
			{
				$module_names = array();
				$modules = $this->diafan->_custom->get_modules($names);
				if(! empty($modules))
				{
					foreach($modules as $key => $module)
					{
						if(empty($module["installed"])) continue;
						$module_names[] = $key;
					}
				}
				$this->diafan->_custom->set_modules($module_names, false, $names);
			}
			$result = $this->diafan->_custom->set($names, false, $sql);
		}
		return $result;
	}

	/**
	 * Деинсталлирует и удаляет дополнения
	 *
	 * @param mixed $array идентификатор дополнения или массив идентификаторов дополнений
	 * @param boolean $sql выполняет дополнительные запросы к базе данных
	 * @return boolean
	 */
	public function delete($array, $sql = false)
	{
		if(! is_array($array))
		{
			$array = array($array);
		}
		foreach($array as $key => $name)
		{
			if(! empty($name)) continue;
			unset($array[$key]);
		}
		if(empty($array))
		{
			return false;
		}

		$ids = array();
		foreach($array as $value)
		{
			$value = (int) preg_replace("/\D/", "", $value);
			if(empty($value)) continue;
			$ids[] = $value;
		}

		if(empty($ids))
		return false;

		$this->uninstall($ids, $sql);
		if($sql)
		{
			$names = DB::query_fetch_key_value("SELECT c.id as id, IFNULL(c.name, '') as name FROM {%s} AS e LEFT JOIN {custom} AS c ON c.id=e.custom_id WHERE c.id IS NOT NULL AND e.id IN (%s)", self::MODULE_NAME, implode(',', $ids), "id", "name");
			if(! empty($names))
			{
				foreach($names as $id => $name)
				{
					if(empty($name)) continue;
					$dir_path = 'custom/' . $name;
					if(is_dir(ABSOLUTE_PATH.$dir_path))
					{
						DB::query("DELETE FROM {custom} WHERE id=%d LIMIT 1", $id);
						File::delete_dir($dir_path);
					}
				}
			}
			DB::query("UPDATE {%s} SET custom_id=%d, custom_timeedit=%d WHERE id IN (%s)", self::MODULE_NAME, 0, 0, implode(',', $ids));
		}

		return true;
	}

	/**
	 * Обновляет дополнения путем только замены файлов в прикреплнной к дополнению теме
	 *
	 * @param mixed $array идентификатор дополнения или массив идентификаторов дополнений
	 * @return boolean
	 */
	public function reload($array)
	{
		if(! is_array($array))
		{
			$array = array($array);
		}
		foreach($array as $key => $name)
		{
			if(! empty($name)) continue;
			unset($array[$key]);
		}
		if(empty($array))
		{
			return false;
		}

		$ids = array();
		foreach($array as $value)
		{
			$value = (int) preg_replace("/\D/", "", $value);
			if(empty($value)) continue;
			$ids[] = $value;
		}

		if(empty($ids))
		return false;

		$names = Custom::names();
		foreach($names as $key => $name) $names[$key] = "'".$name."'";
		$rows = DB::query_fetch_key("SELECT e.*, e.id as id, addon_id, IFNULL(c.id, 0) as `custom.id`, IFNULL(c.name, '') as `custom.name`, IF (c.id > 0 AND c.name IN (".implode(", ", $names)."), '1', '0') AS act FROM {%s} AS e LEFT JOIN {custom} AS c ON c.id=e.custom_id WHERE e.id IN (%s)", self::MODULE_NAME, implode(',', $ids), "id");

		$errors = array(); // лог ошибок

		$links = array();
		if($rows)
		{
			$ids = $this->diafan->array_column($rows, 'addon_id');
			$url = $this->account->uri('addons', 'download');
	    $param = array("ids" => implode(",", $ids));
	    if($response = $this->diafan->_client->request($url, $this->account->token, $param))
			{
				$links = ! empty($response["links"]) ? $response["links"] : array();
				foreach($links as $key => $item)
				{
					$this->diafan->_client->get_attributes($item, 'link', 'name');
					if(empty($item["link"]))
					{
						unset($links[$key]);
						continue;
					}
					if(! $file_path = $this->download($item['link']))
					{
						if(! empty($this->diafan->_client->errors)) { $errors = array_merge($errors, $this->diafan->_client->errors); }
						unset($links[$key]);
						continue;
					}
					$item["file_path"] = $file_path;
					$links[$key] = $item;
				}
			}
			elseif(! empty($this->diafan->_client->errors)) { $errors = array_merge($errors, $this->diafan->_client->errors); }
		}

		foreach($rows as $row)
		{
			$name = $row["custom.name"];
			$id = $row["addon_id"];
			$dir_path = 'custom/' . $name;

			if(empty($row["custom.id"]) || empty($name) && ! is_dir(ABSOLUTE_PATH.$dir_path))
			{
				continue;
			}

			if (empty($links[$id]['file_path']))
			{
				continue;
			}
			$file_path = $links[$id]['file_path'];
			if(file_exists(ABSOLUTE_PATH.$file_path))
			{
				File::create_dir($this->return_path, true);
				File::create_dir($this->return_path.'/'.$name, true);
				$theme = date('Y_m_d_H_i_s');
				$filename = File::tempnam($this->return_path.'/'.$name, $theme, (class_exists('ZipArchive') ? 'zip' : false));
				if(class_exists('ZipArchive'))
				{
					File::zip($dir_path, $this->return_path.'/'.$name.'/'.$filename);
				}
				else
				{
					File::create_dir($this->return_path.'/'.$name.'/'.$filename);
					File::copy_dir($dir_path, $this->return_path.'/'.$name.'/'.$filename);
				}

				File::delete_dir($dir_path);
				if ($name && $this->diafan->_custom->import($file_path, $name))
				{
					DB::query("UPDATE {%s} SET custom_id=%d, custom_timeedit=%d WHERE id=%d", self::MODULE_NAME, $row["custom.id"], $row["timeedit"], $row["id"]);
				}
				unlink($file_path);
			}
		}

		return true;
	}

	/**
	 * Загружает дополнения
	 *
	 * @param string $url ссылка архивного файла дополнения
	 * @return string
	 */
	private function download($url)
	{
		if(preg_match('/^(.*)(\/api\/attachments\/get\/)(.*)$/msi', $url, $matches))
		{
			$url_check = preg_replace('/^(.*)(\/api\/attachments\/get\/)(.*)$/msi', '${1}/api/attachments/check/${3}', $url, 1);
			if(! $response = $this->diafan->_client->request($url_check, $this->account->token))
			{
				return false;
			}
		}
		else return false;

		if($response = $this->diafan->_client->request($url, $this->account->token, false, false, CLIENT_DOWNLOAD))
		{
			$this->diafan->_client->get_attributes($response, 'request', 'content', 'filename');
			if($response["request"] && $response["content"])
			{
				File::create_dir($this->dir_path, true);
				$file_path = $this->dir_path . ($response["filename"] ?: md5('addon' . mt_rand(0, 9999)));
				if(file_exists(ABSOLUTE_PATH.$file_path)) unlink(ABSOLUTE_PATH.$file_path);
				File::save_file($response["content"], $file_path);
				return (file_exists(ABSOLUTE_PATH.$file_path) ? $file_path : false);
			}
		}
		return false;
	}

	/**
	 * Генерирует новое имя для темы сайта
	 *
	 * @param integer $id идентификатор дополнения
	 * @param integer $name имя дополнения
	 * @return string
	 */
	private function generate_name($id = false, $name = false)
  {
		$name = $name ? preg_replace('/[^a-z0-9_]+/', '', $name) : $name;
		$theme = self::PREFIX.($id ? '_'.$id : '').($name ? '_'.$name : '');
		return File::tempnam('custom', $theme, false);
  }

	/**
	 * Покупка дополнения
	 *
	 * @param integer $id идентификатор дополнения
	 * @param boolean $subscription подписка на дополнение
	 * @return mixed
	 */
	public function buy($id, $subscription = false)
	{
		$url = $this->account->uri('addons', ($subscription ? 'subscription' : 'buy'));
		if($result = $this->diafan->_client->request($url, $this->account->token, array('id' => $id)))
		{
			$this->update(true);
			return true;
		}
		return empty($this->diafan->_client->errors) ? false : $this->diafan->_client->errors;
	}

	/**
	 * Отмена подписки на дополнение
	 *
	 * @param integer $id идентификатор дополнения
	 * @return mixed
	 */
	public function no_subscription($id)
	{
		$custom_id = 0;
		$custom_name = '';

		$fields = ", IFNULL(c.id, 0) as `custom.id`, IFNULL(c.name, '') as `custom.name`";
		$join = " LEFT JOIN {custom} AS c ON c.id=e.custom_id";
		if($row = DB::query_fetch_array("SELECT e.*".$fields." FROM {addons} as e".$join." WHERE e.addon_id=%d LIMIT 1",	$id))
		{
			if(! empty($row["custom.id"]))
			{
				$custom_id = $row["custom.id"];
			}
			if(! empty($row["custom.name"]))
			{
				$custom_name = $row["custom.name"];
			}
		}

		$url = $this->account->uri('addons', 'no_subscription');
		if($result = $this->diafan->_client->request(
			$url, $this->account->token, array(
				'id' => $id,
				'custom_id' => $custom_id,
				'custom_name' => $custom_name,
			)
		))
		{
			$this->update(true);
			return true;
		}
		return empty($this->diafan->_client->errors) ? false : $this->diafan->_client->errors;
	}
}

/**
 * Addons_exception
 *
 * Исключение для дополнений
 */
class Addons_exception extends Exception{}

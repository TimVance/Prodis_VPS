<?php
/**
 * Редактирование вариантов генерирования изображений
 * 
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2018 OOO «Диафан» (http://www.diafan.ru/)
 */

if (! defined('DIAFAN')) {
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
 * Images_admin
 */
class Images_admin extends Frame_admin
{
    /**
     * @var string таблица в базе данных
     */
    public $table = 'images_variations';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
				'help' => 'Название метода обработки изображений. Используется во всех модулях, где подключены изображения.',
			),
			'folder' => array(
				'type' => 'text',
				'name' => 'Папка',
				'help' => 'Название папки латинскими буквами без пробелов, куда будут загружаться обработанные изображения Для каждого модуля папка формируется отдельно.',
			),
			'quality' => array(
				'type' => 'numtext',
				'name' => 'Качество',
				'help' => 'Качество сжатия файлов в формате JPEG (0 – минимальное, 100 – максимальное, 60-90 – рекомендуемое).',
				'default' => 80,
			),
			'actions' => array(
				'type' => 'function',
				'name' => 'Метод обработки',
				'help' => 'Набор действий, осуществляемых с изображением для формирование варианта изображения. Можно задать несколько действий. Действия выполняются в заданной последовательности.',
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'name' => array(
			'name' => 'Название'
		),
		'actions' => array(
			'trash' => true,
		),
	);

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		if (! extension_loaded('gd'))
		{
			echo '<div class="error">'.$this->diafan->_('Внимание! На хостинге не установлена библиотека GD. Работа модуля ограничена. Обратитесь в техподдержку вашего хостинга!').'</div>';
		}
		$this->diafan->addnew_init('Добавить вариант');
	}

	/**
	 * Выводит список вариантов
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Проверяет можно ли выполнять действия с текущим элементом строки
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param string $action действие
	 * @return boolean
	 */
	public function check_action($row, $action = '')
	{
		if(! isset($this->cache['images_variation_cache_config']))
		{
			$this->cache['images_variation_cache_config'] = array();
			$rs = DB::query_fetch_all("SELECT value FROM {config} WHERE name LIKE 'images_variations_%'");
			foreach ($rs as $r)
			{
				if($r["value"])
				{
					$vs = unserialize($r["value"]);
					foreach ($vs as $v)
					{
						if(! in_array($v["id"], $this->cache['images_variation_cache_config']))
						{
							$this->cache['images_variation_cache_config'][] = $v["id"];
						}
					}
				}
			}
		}
		// нельзя удалить размер, который используется в настройках модуля
		if(in_array($row["id"], $this->cache['images_variation_cache_config']))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Редактирование поля "Действия"
	 * 
	 * @return void
	 */
	public function edit_variable_actions()
	{
		$params = array();
		if(! $this->diafan->is_new)
		{
			$params = unserialize($this->diafan->values("param"));
		}
		echo '
		<div class="unit">
			<div class="infofield">'.$this->diafan->variable_name().'</div>';
				foreach ($params as $i => $param)
				{
					$this->get_action($param, $i);
				}
				if(empty($params))
				{
					$this->get_action();
				}
				echo '<a href="javascript:void(0)" class="images_action_plus"><i class="fa fa-plus-square"></i> '.$this->diafan->_('Добавить еще один шаг обработки').'</a>
		</div>';
	}

	private function get_action($value = '', $i = 0)
	{
		echo '
		<div class="images_action">
			<a href="javascript:void(0)" confirm="'.$this->diafan->_('Вы действительно хотите удалить действие?').'" class="images_action_delete delete"><i class="fa fa-close" title="'.$this->diafan->_('Удалить').'"></i></a>
			<input name="i[]" value="'.$i.'" type="hidden">
			<div id="images_action_'.$i.'" class="images_action_container">
			<select name="actions[]">
			<option value="resize"'.(! empty($value["name"]) && $value["name"] == 'resize' ? ' selected' : '').'>'.$this->diafan->_('изменить пропорционально').'</option>
			<option value="selectarea"'.(! empty($value["name"]) && $value["name"] == 'selectarea' ? ' selected' : '').'>'.$this->diafan->_('выделить область').'</option>
			<option value="crop"'.(! empty($value["name"]) && $value["name"] == 'crop' ? ' selected' : '').'>'.$this->diafan->_('обрезать').'</option>
			<option value="wb"'.(! empty($value["name"]) && $value["name"] == 'wb' ? ' selected' : '').'>'.$this->diafan->_('обесцветить').'</option>
			<option value="watermark"'.(! empty($value["name"]) && $value["name"] == 'watermark' ? ' selected' : '').'>'.$this->diafan->_('наложить водяной знак').'</option>
			</select>';

			$width   = (! empty($value["name"]) && $value["name"] == 'resize' ? $value["width"]   : '');
			$height  = (! empty($value["name"]) && $value["name"] == 'resize' ? $value["height"]  : '');
			$max     = (! empty($value["name"]) && $value["name"] == 'resize' ? $value["max"]     : false);

			echo '<div class="images_param images_param_resize">
				<p>'.$this->diafan->_('Размер').'
				<input type="number" name="resize_width[]" size="3" value="'.$width.'"> x
				<input type="number" name="resize_height[]" size="3" value="'.$height.'">
				'.$this->diafan->help("Размер, до которого изоражение будет автоматически изменяться после загрузки").'</p>
				<p><input type="checkbox" class="resize_max" value="1"'.($max ? ' checked' : '').' id="resize_max_'.$i.'">
				<label class="resize_max_label" for="resize_max_'.$i.'">'.$this->diafan->_('Уменьшение по меньшей стороне').'</label>
				<input type="hidden" name="resize_max[]" value="'.($max ? '1' : '0').'"></p>
			</div>';

			$width   = (! empty($value["name"]) && $value["name"] == 'selectarea' ? $value["width"]   : '');
			$height  = (! empty($value["name"]) && $value["name"] == 'selectarea' ? $value["height"]  : '');

			echo '<div class="images_param images_param_selectarea">
				<p>'.$this->diafan->_('Пропорционально размеру').'
				<input type="number" name="selectarea_width[]" size="3" value="'.$width.'"> x
				<input type="number" name="selectarea_height[]" size="3" value="'.$height.'"></p>
			</div>';

			$width   = (! empty($value["name"]) && $value["name"] == 'crop' ? $value["width"]   : '');
			$height  = (! empty($value["name"]) && $value["name"] == 'crop' ? $value["height"]  : '');
			$vertical      = (! empty($value["name"]) && $value["name"] == 'crop' ? $value["vertical"]       : '');
			$vertical_px   = (! empty($value["name"]) && $value["name"] == 'crop' ? $value["vertical_px"]    : '');
			$horizontal    = (! empty($value["name"]) && $value["name"] == 'crop' ? $value["horizontal"]     : '');
			$horizontal_px = (! empty($value["name"]) && $value["name"] == 'crop' ? $value["horizontal_px"]  : '');

			echo '<div class="images_param images_param_crop">
				<p>'.$this->diafan->_('Размер').'
				<input type="number" name="crop_width[]" size="3" value="'.$width.'"> x
				<input type="number" name="crop_height[]" size="3" value="'.$height.'"></p>

				<p><select name="crop_vertical[]">
					<option value="top"'.($vertical == 'top' ? ' selected' : '').'>'.$this->diafan->_('сверху').'</option>
					<option value="middle"'.($vertical == 'middle' ? ' selected' : '').'>'.$this->diafan->_('от центра').'</option>
					<option value="bottom"'.($vertical == 'bottom' ? ' selected' : '').'>'.$this->diafan->_('снизу').'</option>
				</select>
				<input type="number" name="crop_vertical_px[]" size="3" value="'.$vertical_px.'"> px
				<select name="crop_horizontal[]">
					<option value="left"'.($horizontal == 'left' ? ' selected' : '').'>'.$this->diafan->_('слева').'</option>
					<option value="center"'.($horizontal == 'center' ? ' selected' : '').'>'.$this->diafan->_('от центра').'</option>
					<option value="right"'.($horizontal == 'right' ? ' selected' : '').'>'.$this->diafan->_('справа').'</option>
				</select>
				<input type="number" name="crop_horizontal_px[]" size="3" value="'.$horizontal_px.'"> px
				</p>
			</div>';

			$vertical      = (! empty($value["name"]) && $value["name"] == 'watermark' ? $value["vertical"]       : '');
			$vertical_px   = (! empty($value["name"]) && $value["name"] == 'watermark' ? $value["vertical_px"]    : '');
			$horizontal    = (! empty($value["name"]) && $value["name"] == 'watermark' ? $value["horizontal"]     : '');
			$horizontal_px = (! empty($value["name"]) && $value["name"] == 'watermark' ? $value["horizontal_px"]  : '');
			$file          = (! empty($value["name"]) && $value["name"] == 'watermark' ? $value["file"]  : '');

			echo '<div class="images_param images_param_watermark">
				<p>'.($file ? '<img src="'.BASE_PATH.USERFILES.'/watermark/'.$file.'"><br>' : '').'
				<input type="file" name="watermark_file_'.$i.'" size="40"></p>
				<p>
				<select name="watermark_vertical[]">
					<option value="top"'.($vertical == 'top' ? ' selected' : '').'>'.$this->diafan->_('сверху').'</option>
					<option value="middle"'.($vertical == 'middle' ? ' selected' : '').'>'.$this->diafan->_('от центра').'</option>
					<option value="bottom"'.($vertical == 'bottom' ? ' selected' : '').'>'.$this->diafan->_('снизу').'</option>
				</select>
				<input type="number" name="watermark_vertical_px[]" size="3" value="'.$vertical_px.'"> px
				<select name="watermark_horizontal[]">
					<option value="left"'.($horizontal == 'left' ? ' selected' : '').'>'.$this->diafan->_('слева').'</option>
					<option value="center"'.($horizontal == 'center' ? ' selected' : '').'>'.$this->diafan->_('от центра').'</option>
					<option value="right"'.($horizontal == 'right' ? ' selected' : '').'>'.$this->diafan->_('справа').'</option>
				</select>
				<input type="number" name="watermark_horizontal_px[]" size="3" value="'.$horizontal_px.'"> px</p>
			</div>
			</div>
			<h2></h2>
		</div>';
	}

	/**
	 * Валидация поля "Папка"
	 * 
	 * @return void
	 */
	public function validate_variable_folder()
	{
		if(empty($_POST["folder"]))
		{
			$this->diafan->set_error("folder", "Поле не должно быть пустым.");
		}
		elseif(preg_match('/[^a-z0-9_]+/', $_POST["folder"]))
		{
			$this->diafan->set_error("folder", "Название папки может содержать только буквы латинского алфавита в нижнем регистре, цифры и нижнее подчеркивание.");
		}
		else
		{
			if(DB::query_result("SELECT COUNT(*) FROM {images_variations} WHERE folder='%s'".(! $this->diafan->is_new ? " AND id<>%d" : ''),  $_POST["folder"], $this->diafan->id))
			{
				$this->diafan->set_error("folder", "В системе уже создан размер изображения с таким же названием папки.");
			}
		}
	}

	/**
	 * Валидация поля "Действия"
	 * 
	 * @return void
	 */
	public function validate_variable_actions()
	{
		if(! empty($_POST["i"]))
		{
			foreach ($_POST["i"] as $i => $k)
			{
				$mes = '';
				switch($_POST["actions"][$i])
				{
					case 'resize':
						$mes = Validate::numtext($_POST["resize_width"][$i]);
						if(! $mes)
						{
							$mes = Validate::numtext($_POST["resize_height"][$i]);
						}
						break;

					case 'selectarea':
						$mes = Validate::numtext($_POST["selectarea_width"][$i]);
						if(! $mes)
						{
							$mes = Validate::numtext($_POST["selectarea_height"][$i]);
						}
						break;

					case 'crop':
						if(! $_POST["crop_width"][$i] || ! $_POST["crop_height"][$i])
						{
							$mes = 'Задайте размер обрезаемой области';
						}
						if(! $mes)
						{
							$mes = Validate::numtext($_POST["crop_width"][$i]);
						}
						if(! $mes)
						{
							$mes = Validate::numtext($_POST["crop_height"][$i]);
						}
						if(! $mes)
						{
							$mes = Validate::numtext($_POST["crop_vertical_px"][$i]);
						}
						if(! $mes)
						{
							$mes = Validate::numtext($_POST["crop_horizontal_px"][$i]);
						}
						break;

					case 'wb':
						break;

					case 'watermark':
						$mes = Validate::numtext($_POST["watermark_vertical_px"][$i]);
						if(! $mes)
						{
							$mes = Validate::numtext($_POST["watermark_horizontal_px"][$i]);
						}
						if(! $mes)
						{
							if(! $this->diafan->is_new)
							{
								$oldparam = unserialize(DB::query_result("SELECT param FROM {images_variations} WHERE id=%d LIMIT 1", $this->diafan->id));
							}
							$oldfile = ! empty($oldparam[$k]["file"]) ? $oldparam[$k]["file"] : '';
							$mes = $this->validate_watermark($i, $k, $oldfile);
						}
						break;
				}
				if($mes)
				{
					$this->diafan->set_error("images_action_".$i, $mes);
				}
			}
		}
	}

	/**
	 * Валидация файла водяного знака
	 * 
	 * @return void
	 */
	private function validate_watermark($i, $k, $oldfile)
	{
		$mes = '';
		if(empty($_FILES["watermark_file_".$k]['tmp_name']) || empty($_FILES["watermark_file_".$k]['name']))
		{
			if(! $oldfile)
			{
				$mes = 'Вы забыли добавить файл для загрузки';
			}
		}
		else
		{
			$info = @getimagesize($_FILES["watermark_file_".$k]['tmp_name']);
			$mimes = array(
				'image/gif',
				'image/jpeg',
				'image/png',
				'image/pjpeg',
				'image/x-png'
			);
			if(empty($info['mime']) || ! in_array($info['mime'], $mimes))
			{
				$mes = 'Доступны только следующие типы файлов: gif, jpeg, jpg, jpe, png';
			}
		}
		return $mes;
	}

	/**
	 * Сохранение поля "Действия"
	 * 
	 * @return void
	 */
	public function save_variable_actions()
	{
		if(! $this->diafan->is_new)
		{
			$oldparam = unserialize($this->diafan->values("param"));
		}
		$param_actions = array();
		if(! empty($_POST["i"]))
		{
			foreach ($_POST["i"] as $i => $k)
			{
				switch($_POST["actions"][$i])
				{
					case 'resize':
						$param = array(
							"name" => $_POST["actions"][$i],
							"width" => $_POST["resize_width"][$i],
							"height" => $_POST["resize_height"][$i],
							"max" => ! empty($_POST["resize_max"][$i]) ? 1 : 0,
						);
						break;

					case 'selectarea':
						$param = array(
							"name" => $_POST["actions"][$i],
							"width" => $_POST["selectarea_width"][$i],
							"height" => $_POST["selectarea_height"][$i],
						);
						break;

					case 'crop':
						$param = array(
							"name" => $_POST["actions"][$i],
							"width" => $_POST["crop_width"][$i],
							"height" => $_POST["crop_height"][$i],
							"vertical" => $_POST["crop_vertical"][$i],
							"vertical_px" => $_POST["crop_vertical_px"][$i],
							"horizontal" => $_POST["crop_horizontal"][$i],
							"horizontal_px" => $_POST["crop_horizontal_px"][$i],
						);
						break;

					case 'wb':
						$param = array(
							"name" => $_POST["actions"][$i],
						);
						break;

					case 'watermark':
						$oldfile = ! empty($oldparam[$k]["file"]) ? $oldparam[$k]["file"] : '';
						$file = $this->upload_watermark($this->diafan->id, $i, $k, $oldfile);
						$param = array(
							"name" => $_POST["actions"][$i],
							"vertical" => $_POST["watermark_vertical"][$i],
							"vertical_px" => $_POST["watermark_vertical_px"][$i],
							"horizontal" => $_POST["watermark_horizontal"][$i],
							"horizontal_px" => $_POST["watermark_horizontal_px"][$i],
							"file" => $file,
						);
						$watermark_i[] = $k;
						break;

					default:
						$param = array();
				}
				if($_POST["actions"][$i] != 'watermark' && ! empty($oldparam[$k]["file"]))
				{
					File::delete_file(USERFILES.'/watermark/'.$oldparam[$k]["file"]);
				}
				$param_actions[] = $param;
			}
		}
		foreach ($oldparam as $i => $param)
		{
			if($param["name"] == 'watermark' && (empty($watermark_i) || ! in_array($i, $watermark_i)))
			{
				File::delete_file(USERFILES.'/watermark/'.$param["file"]);
			}
		}
		$this->diafan->set_query("param='%s'");
		$this->diafan->set_value(serialize($param_actions));
	}

	/**
	 * Загружает водяной знак
	 * 
	 * @return void
	 */
	private function upload_watermark($id, $i, $k, $oldfile)
	{
		if(empty($_FILES["watermark_file_".$k]['tmp_name']) || empty($_FILES["watermark_file_".$k]['name']))
		{
			return $oldfile;
		}
		Custom::inc("includes/image.php");
		if($oldfile)
		{
			File::delete_file(USERFILES.'/watermark/'.$oldfile);
		}

		$info = @getimagesize($_FILES["watermark_file_".$k]['tmp_name']);
		$mimes = array(
			'image/gif' => 'gif',
			'image/jpeg' => 'jpeg',
			'image/png' => 'png',
			'image/pjpeg' => 'jpeg',
			'image/x-png'=> 'png'
		);
		$extension = $mimes[$info['mime']];

		$new_name = $id.'_'.$i.'.'.$extension;

		File::upload_file($_FILES["watermark_file_".$k]['tmp_name'], USERFILES."/watermark/".$new_name);

		return $new_name;
	}
}
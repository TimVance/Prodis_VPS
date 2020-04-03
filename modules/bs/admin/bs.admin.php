<?php
/**
 * Редактирование баннеров
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
 * Bs_admin
 */
class Bs_admin extends Frame_admin
{
	/**
	 * @var string таблица в базе данных
	 */
	public $table = 'bs';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'number' => array(
				'type' => 'function',
				'name' => 'ID баннера',
				'help' => 'Номер элемента в БД (веб-мастеру и программисту).',
				'no_save' => true,
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'default' => true,
				'multilang' => true,
			),			
			'name' => array(
				'type' => 'text',
				'name' => 'Название',
				'multilang' => true,
			),
			'file' => array(
				'type' => 'function',
				'name' => 'Вид баннера',
				'help' => 'Изображение, флэш, HTML.',
			),
			'link' => array(
				'type' => 'text',
				'name' => 'Ссылка',
				'help' => 'В полном формате *http://www.site.ru/*.',
				'multilang' => true,
			),
			'target_blank' => array(
				'type' => 'checkbox',
				'name' => 'Открывать в новом окне',
				'help' => 'Ссылка на баннер будет открываться в новом окне',
			),
			'text' => array(
				'type' => 'editor',
				'name' => 'Описание',
				'multilang' => true,
			),
			'created' => array(
				'type' => 'date',
				'name' => 'Дата',
				'help' => 'Вводится в формате дд.мм.гггг чч:мм. Если указать дату позже текущей даты, то баннер начнет отображаться на сайте, начиная с указанной даты.',
			),
			'hr1' => 'hr',
			'count_view' => array(
				'type' => 'numtext',
				'name' => 'Всего показов',
				'help' => 'Статистика прошедших показов баннера.',
				'disabled' => true,
			),
			'click' => array(
				'type' => 'numtext',
				'name' => 'Всего кликов',
				'help' => 'Статистика прошедших кликов по баннеру.',
				'disabled' => true,
			),
			'hr2' => 'hr',
			'date_period' => array(
				'type' => 'datetime',
				'name' => 'Период показа',
				'help' => 'Время, в течение которого будет показываться баннер.',
			),
			'check_number' => array(
				'type' => 'checkbox',
				'name' => 'Ограничить количество показов',
				'help' => 'Ограничение показа до заданного количества.',
			),
			'show_number' => array(
				'type' => 'numtext',
				'name' => 'Осталось показов',
				'help' => 'Укажите число, сколько раз должен показываться баннер. С каждым показом цифра в этом поле будет уменьшаться, пока не станет 0 (или пустое поле).',
			),
			'check_click' => array(
				'type' => 'checkbox',
				'name' => 'Ограничить количество показов по кликам',
				'help' => 'Ограничение показа до заданного количества.',
			),
			'show_click' => array(
				'type' => 'numtext',
				'name' => 'Осталось кликов',
				'help' => 'Укажите число, обозначающее, через какое количество кликов скрыть отображение баннера. С каждым кликом цифра в этом поле будет уменьшаться, пока не станет 0 (или пустое поле).',
			),
			'check_user' => array(
				'type' => 'checkbox',
				'name' => 'Ограничить количество показов посетителю в сутки',
				'help' => 'Ограничение показа баннера посетителю.',
			),
			'show_user' => array(
				'type' => 'numtext',
				'name' => 'Количество показов посетителю в сутки',
				'help' => 'Сколько раз показывать баннер одному пользователю (счетчик сохраняется в сессии).',
				'depend' => 'check_user',
			),
			'hr3' => 'hr',
			'site_ids' => array(
				'type' => 'function',
				'name' => 'Раздел сайта',
				'help' => 'Выбор раздела, в котором будет виден баннер.',
			),
			'cat_id' => array(
				'type' => 'function',
				'name' => 'Категория',
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Редактирование порядка следования баннера в списке. Поле доступно для редактирования только для баннеров, отображаемых на сайте.',
			),
		),
	);

	/**
	 * @var array поля в списка элементов
	 */
	public $variables_list = array (
		'checkbox' => '',
		'sort' => array(
			'name' => 'Сортировка',
			'type' => 'numtext',
			'desc' => true,
			'sql' => true,
			'fast_edit' => true,
		),
		'file' => array(
			'name' => 'Изображение',
			'class_th' => 'item__th_image ipad',
			'sql' => true,
			'no_important' => true,
		),
		'name' => array(
			'name' => 'Название и категория'
		),
		'count_view' => array(
			'name' => 'Всего показов',
			'type' => 'numtext',
			'sql' => true,
		),
		'show_number' => array(
			'name' => 'Осталось показов',
			'type' => 'numtext',
			'sql' => true,
			'no_important' => true,
			'depend' => 'check_number',
		),
		'click' => array(
			'name' => 'Всего кликов',
			'type' => 'numtext',
			'sql' => true,
			'no_important' => true,
		),
		'show_click' => array(
			'name' => 'Осталось кликов',
			'type' => 'numtext',
			'sql' => true,
			'no_important' => true,
			'depend' => 'check_click',
		),
		'actions' => array(
			'act' => true,
			'trash' => true,
		),
	);

	/**
	 * @var array настройки модуля
	 */
	public $config = array (
		'element', // используются группы
		'category_flat', // категории не содержат вложенности
		'category_no_multilang', // название категории не переводиться
	);

	/**
	 * Подготавливает конфигурацию модуля
	 * @return void
	 */
	public function prepare_config()
	{
		if(! $this->diafan->configmodules("cat", "bs", $this->diafan->_route->site))
		{
			$this->diafan->config("element", false);
		}
	}

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
		if ($this->diafan->config('element') && ! $this->diafan->not_empty_categories)
		{
			echo '<div class="error">'.sprintf($this->diafan->_('В %sнастройках%s модуля подключены категории, чтобы начать добавлять баннеры создайте хотя бы одну %sкатегорию%s.'), '<a href="'.BASE_PATH_HREF.'bs/config/">', '</a>','<a href="'.BASE_PATH_HREF.'bs/category/'.($this->diafan->_route->site ? 'site'.$this->diafan->_route->site.'/' : '').'">', '</a>').'</div>';
		}
		else
		{
			$this->diafan->addnew_init('Добавить баннер');
		}
	}

	/**
	 * Выводит список баннеров
	 * @return void
	 */
	public function show()
	{
		$this->diafan->list_row();
	}

	/**
	 * Формирует изображение в списке
	 *
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_file($row, $var)
	{
		$html = '<div class="image'.($var["class"] ? ' '.$var["class"] : '').' ipad">';
		if($row["file"])
		{
			$html .= '<a href="'.$this->diafan->get_base_link($row).'"><img src="'.BASE_PATH.USERFILES.'/bs/'.$row["file"].'" style="max-width:100px; max-height:50px;" border="0" alt=""></a>';
		}
		$html .= '</div>';

		return $html;
	}

	/**
	* Редактирование поля "Файл"
	* @return boolean true
	*/
	public function edit_variable_file()
	{
		echo '
		<div class="unit">
			<div class="infofield">'.$this->diafan->_('Вид баннера').'</div>';
		echo '
		<input type="radio" name="type" value="1"'.(! $this->diafan->values("type") || $this->diafan->values("type") == 1 ? ' checked' : '').' id="file1_radio">
		<label for="file1_radio">'.$this->diafan->_('Изображение').'</label>
		<input type="radio" name="type" value="2"'.($this->diafan->values("type") == 2 ? ' checked' : '').' id="file2_radio"> <label for="file2_radio">'.$this->diafan->_('Флэш').'</label>
 		<input type="radio" name="type" value="3"'.($this->diafan->values("type") == 3 ? ' checked' : '').' id="file3_radio"> <label for="file3_radio">HTML</label>

		<div class="type1'.(! $this->diafan->values("type") || $this->diafan->values("type") == 1 ? '' : ' hide').'">
			<input type="file" name="attachment_img" class="file">
			<br>';
			if($this->diafan->values("file"))
			{
				echo '<a href="'.BASE_PATH.USERFILES.'/bs/'.$this->diafan->values("file").'" target="_blank"><img src="'.BASE_PATH.USERFILES.'/bs/'.$this->diafan->values("file").'" style="max-width:450px; max-height:150px;"></a>';
			}
			echo '
			<div style="padding: 8px 0 0 0;">
				<input type="text" name="alt" size="10" value="'.$this->diafan->values("alt"._LANG).'">
				alt
			</div>
			<div style="padding: 8px 0 0 0;">
				<input type="text" name="title" size="10" value="'.$this->diafan->values("title"._LANG).'">
				title
			</div>
		</div>

		<div class="type2'.($this->diafan->values("type") == 2 ? '' : ' hide').'">
			<input type="file" name="attachment_swf" class="file">
			<br>
			'.$this->diafan->values("file").'
			<div style="padding: 8px 0 0 0;">
				<input type="number" name="width" size="3" value="'.$this->diafan->values("width").'">
				x <input type="number" name="height" size="3" value="'.$this->diafan->values("height").'">
				('.$this->diafan->_('Ширина').' x '.$this->diafan->_('Высота').')
			</div>
		</div>

		<div class="type3'.($this->diafan->values("type") == 3 ? '' : ' hide').'">
			<textarea rows="5" cols="60" name="html">'.$this->diafan->values("html").'</textarea>
		</div>
		</div>';
	}

	/**
	 * Редактирование поля "Ссылка"
	 * @return void
	 */
	public function edit_variable_link()
	{
		echo '
		<div class="unit" id="module_name">
			<div class="infofield">'.$this->diafan->variable_name().$this->diafan->help().'</div>  
			<input type="text" name="'.$this->diafan->key.'" value="'.$this->diafan->value.'" placeholder="'.BASE_PATH.'">
			<a href="javascript:void(0)" class="menu_check"><i class="fa fa-pencil"></i> '.$this->diafan->_('Выбрать страницу сайта', false).'</a>
		</div>';
	}

	/**
	 * Сохранение поля "Файл"
	 * @return void
	 */
	public function save_variable_file()
	{
		if($_POST['type'] == 1)
		{
			if (! empty($_FILES["attachment_img"]['name']))
			{
				$extension_array = array('jpg', 'jpeg', 'gif','png');

				$new_name = strtolower($this->diafan->translit($_FILES["attachment_img"]['name']));
				$extension = substr(strrchr($new_name, '.'), 1);
				if (!in_array($extension, $extension_array))
				{
					throw new Exception('Не удалось загрузить файл. Возможно, закрыт доступ к папке или файл превышает максимально допустимый размер');
				}

				$new_name = substr($new_name, 0, - (strlen($extension) + 1)).'_'.$this->diafan->id.'.'.$extension;

				if ($this->diafan->values('file'))
				{
					File::delete_file(USERFILES.'/'.$this->diafan->table.'/'.$this->diafan->values('file'));
				}

				File::upload_file($_FILES["attachment_img"]['tmp_name'], USERFILES."/bs/".$new_name);

				$this->diafan->set_query("file='%s'");
				$this->diafan->set_value($new_name);

				$this->diafan->set_query("html='%s'");
				$this->diafan->set_value('');

				$this->diafan->set_query("width='%d'");
				$this->diafan->set_value('');

				$this->diafan->set_query("height='%d'");
				$this->diafan->set_value('');
			}

			$this->diafan->set_query("type=%d");
			$this->diafan->set_value(1);

			$this->diafan->set_query("alt"._LANG."='%s'");
			$this->diafan->set_value($_POST['alt']);

			$this->diafan->set_query("title"._LANG."='%s'");
			$this->diafan->set_value($_POST['title']);
		}

		if($_POST['type'] == 2)
		{
			if(! empty($_FILES["attachment_swf"]['name']))
			{
				$extension_array = array('swf');
				$new_name = strtolower($this->diafan->translit($_FILES["attachment_swf"]['name']));
				$extension = substr(strrchr($new_name, '.'), 1);
				if (! in_array($extension, $extension_array))
				{
					throw new Exception($this->diafan->_('Не удалось загрузить файл. Возможно, закрыт доступ к папке или файл превышает максимально допустимый размер'));
				}

				$new_name = substr($new_name, 0, - (strlen($extension) + 1)).'_'.$this->diafan->id.'.'.$extension;

				if ($this->diafan->values('file'))
				{
					File::delete_file(USERFILES.'/'.$this->diafan->table.'/'.$this->diafan->values('file'));
				}

				File::upload_file($_FILES["attachment_swf"]['tmp_name'], USERFILES."/".$this->diafan->table.'/'.$new_name);

				$this->diafan->set_query("file='%s'");
				$this->diafan->set_value($new_name);

				$this->diafan->set_query("html='%s'");
				$this->diafan->set_value('');

				$this->diafan->set_query("alt"._LANG."='%s'");
				$this->diafan->set_value('');

				$this->diafan->set_query("title"._LANG."='%s'");
				$this->diafan->set_value('');
			}

			$this->diafan->set_query("type='%d'");
			$this->diafan->set_value(2);

			$this->diafan->set_query("width='%d'");
			$this->diafan->set_value($_POST['width']);

			$this->diafan->set_query("height='%d'");
			$this->diafan->set_value($_POST['height']);
		}

		if($_POST['type'] == 3)
		{
			if ($this->diafan->values('file'))
			{
				File::delete_file(USERFILES.'/'.$this->diafan->table.'/'.$this->diafan->values('file'));
			}
			$this->diafan->set_query("html='%s'");
			$this->diafan->set_value($_POST['html']);

			$this->diafan->set_query("file='%s'");
			$this->diafan->set_value('');

			$this->diafan->set_query("alt"._LANG."='%s'");
			$this->diafan->set_value('');

			$this->diafan->set_query("title"._LANG."='%s'");
			$this->diafan->set_value('');

			$this->diafan->set_query("width='%d'");
			$this->diafan->set_value('');

			$this->diafan->set_query("height='%d'");
			$this->diafan->set_value('');

			$this->diafan->set_query("type='%d'");
			$this->diafan->set_value(3);
		}
	}

	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("bs_site_rel", "element_id IN (".implode(",", $del_ids).")");
	}
}

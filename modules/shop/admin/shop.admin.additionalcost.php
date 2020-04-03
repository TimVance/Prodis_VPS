<?php
/**
 * Дополнительная стоимость
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
 * Shop_admin_additional_cost
 */
class Shop_admin_additionalcost extends Frame_admin
{
    /**
     * @var string таблица в базе данных
     */
    public $table = 'shop_additional_cost';

	/**
	 * @var array поля в базе данных для редактирования
	 */
	public $variables = array (
		'main' => array (
			'name' => array(
				'type' => 'text',
				'name' => 'Наименование услуг',
				'help' => 'Добавляются в корзину при оформлении заказа. Например, «Праздничная упаковка товара» или «Расширенная гарантия на товары».',
				'multilang' => true,
			),
			'act' => array(
				'type' => 'checkbox',
				'name' => 'Опубликовать на сайте',
				'default' => true,
				'multilang' => true,
			),
			'shop_rel' => array(
				'type' => 'checkbox',
				'name' => 'Прикрепить услугу к',
			),
			'category' => array(
				'type' => 'function',
				'name' => 'Категории',
				'help' => 'Услуга будет выводиться для возможности подключения в товарах выбранных категорий. Чтобы выбрать несколько категорий, удерживайте CTRL.',
			),
			'price' => array(
				'type' => 'floattext',
				'name' => 'Фиксированная стоимость',
			),
			'percent' => array(
				'type' => 'floattext',
				'name' => 'Процент от суммы',
				'help' => 'Стоимость услуги составляет процент от стоимости, при этом фиксированная стоимость не учитывается.',
			),
			'amount' => array(
				'type' => 'floattext',
				'name' => 'Бесплатно от суммы',
				'help' => 'Стоимость, при которой данная услуга осуществляется бесплатно.',
			),
			'required' => array(
				'type' => 'checkbox',
				'name' => 'Добавлять к стоимости принудительно',
			),
			'text' => array(
				'type' => 'textarea',
				'name' => 'Описание',
				'multilang' => true,
			),
			'sort' => array(
				'type' => 'function',
				'name' => 'Сортировка: установить перед',
				'help' => 'Редактирование порядка следования в списке. Поле доступно для редактирования только для услуг, отображаемых на сайте.',
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
			'sql' => true,
			'fast_edit' => true,
		),
		'name' => array(
			'name' => 'Название'
		),
		'shop_rel' => array(
			'name' => 'Прикреплена к',
			'sql' => true,
		),
		'actions' => array(
			'act' => true,
			'trash' => true,
		),
	);

	/**
	 * Выводит ссылку на добавление
	 * @return void
	 */
	public function show_add()
	{
        $this->diafan->addnew_init('Добавить');
	}

    /**
     * Выводит список дополнительных затрат
     * @return void
     */
    public function show()
	{
        $this->diafan->list_row();
    }

	/**
	 * Выводит к чему прикреплена услуга
	 * 
	 * @param array $row информация о текущем элементе списка
	 * @param array $var текущее поле
	 * @return string
	 */
	public function list_variable_shop_rel($row, $var)
	{
		return '<div class="sum no_important ipad">'
		.($row["shop_rel"]
		 ? $this->diafan->_('товарам') : $this->diafan->_('заказам')).'</div>';
	}
	
	/**
	 * Редактирование поля "Прикрепить услугу"
	 * @return void
	 */
	public function edit_variable_shop_rel()
	{
		echo '<div class="unit" id="shop_rel">
			<div class="infofield">
				'.$this->diafan->variable_name().$this->diafan->help().'
			</div>
				<input id="input_shop_rel_order" name="shop_rel" value="" '. ($this->diafan->values("shop_rel")?'':'checked=""').' type="radio">
				<label for="input_shop_rel_order"  id="input_shop_rel_order_label"><b>'.$this->diafan->_('заказам')
			.'</b></label>
				<input id="input_shop_rel" name="shop_rel" value="1" '. ($this->diafan->values("shop_rel")?'checked=""':'').' type="radio">
				<label for="input_shop_rel" id="input_shop_rel_label"><b>'.$this->diafan->_('товарам')
			.'</b></label>
				
		</div>';
	}

	/**
	 * Редактирование поля "Категория"
	 * 
	 * @return void
	 */
	public function edit_variable_category()
	{
		if(! $this->diafan->configmodules("cat", "shop"))
		{
			return;
		}
		$shop_pages = DB::query_fetch_key_value("SELECT id, [name] FROM {site} WHERE trash='0' AND module_name='shop'", "id", "name");

		$rows = DB::query_fetch_all("SELECT id, [name], parent_id, site_id FROM {shop_category} WHERE trash='0' ORDER BY sort ASC LIMIT 1000");
		foreach ($rows as $row)
		{
			$cats[$row["site_id"]][$row["parent_id"]][] = $row;
		}

		$values = array();
		if ( ! $this->diafan->is_new)
		{
			$values = DB::query_fetch_value("SELECT cat_id FROM {shop_additional_cost_category_rel} WHERE element_id=%d AND cat_id>0", $this->diafan->id, "cat_id");
		}
		elseif($this->diafan->_route->cat)
		{
			$values[] = $this->diafan->_route->cat;
		}
		if(count($rows) == 1000)
		{
			foreach($values as $value)
			{
				echo '<input type="hidden" name="cat_ids[]" value="'.$value.'">';
			}
			return;
		}
		
		echo '
		<div class="unit'.($this->diafan->values("shop_rel")?'':' hidden_block').'" id="category">
			<div class="infofield">'.$this->diafan->_('Категория').$this->diafan->help().'</div>';

		echo ' <select name="cat_ids[]" multiple="multiple" size="11">
		<option value="all"'.(empty($values) ? ' selected' : '').'>'.$this->diafan->_('Все').'</option>';
		foreach ($shop_pages as $site_id => $name)
		{
			if(! empty($cats[$site_id]))
			{
				if(count($shop_pages) > 1)
				{
					echo '<optgroup label="'.$name.'">';
				}
				echo $this->diafan->get_options($cats[$site_id], $cats[$site_id][0], $values);
				if(count($shop_pages) > 1)
				{
					echo '</optgroup>';
				}
			}
		}
		echo '</select>';

		echo '
		</div>';
	}

	/**
	 * Сохранение поля "Категория"
	 * 
	 * @return void
	 */
	public function save_variable_category()
	{
		DB::query("DELETE FROM {shop_additional_cost_category_rel} WHERE element_id=%d", $this->diafan->id);
		if(! empty($_POST["cat_ids"]) && in_array("all", $_POST["cat_ids"]))
		{
			$_POST["cat_ids"] = array();
		}

		if(! empty($_POST["cat_ids"]))
		{
			foreach ($_POST["cat_ids"] as $cat_id)
			{
				DB::query("INSERT INTO {shop_additional_cost_category_rel} (element_id, cat_id) VALUES(%d, %d)", $this->diafan->id, $cat_id);
			}
		}
		else
		{
			DB::query("INSERT INTO {shop_additional_cost_category_rel} (element_id) VALUES(%d)", $this->diafan->id);
		}
	}
	
	/**
	 * Сопутствующие действия при удалении элемента модуля
	 * @return void
	 */
	public function delete($del_ids)
	{
		$this->diafan->del_or_trash_where("shop_additional_cost_rel", "additional_cost_id IN (".implode(",", $del_ids).")");
	}
}
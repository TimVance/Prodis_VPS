<?php
/**
 * Шаблон страницы товара
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

echo '<div class="flexBetween id-add-new-offer">';

echo '<div class="js_shop_id js_shop shop shop_id shop-item-container box-block">';

echo '<h2>Подача заявки</h2>';

$cityes = DB::query_fetch_all("
            SELECT shop.[name] AS shop_name, category.id AS id, category.[name] AS name FROM {shop} AS shop
            RIGHT JOIN {shop_category} AS category ON category.id=shop.cat_id
            RIGHT JOIN {users_rel} AS rel ON rel.rel_element_id=shop.id
            WHERE rel.element_id='%d' AND shop.trash='0' AND category.trash='0' AND rel.trash='0'
            GROUP BY id
        ", $this->diafan->_users->id);

$rows = DB::query_fetch_all("
            SELECT s.id, s.[name], r.rewrite, c.[name] AS cat_name, c.id AS cat_id
            FROM {shop} AS s LEFT JOIN {rewrite} as r ON s.id=r.element_id 
            LEFT JOIN {shop_category} AS c ON c.id=s.cat_id 
            RIGHT JOIN {users_rel} AS rel ON rel.rel_element_id=s.id
            WHERE r.element_type='element' AND r.module_name='shop'
            AND s.cat_id='%d' AND r.trash='0' AND c.trash='0' AND s.trash='0'
            AND rel.element_id='%d' AND rel.trash='0' ORDER BY s.id",
    $result["cat_id"], $this->diafan->_users->id);

if (count($cityes) == 0) {
    echo 'Доступных для Вас торговых центров не найдено!';
    return;
}

echo '
    <br/>
    <div class="form-group">
        Город:
        <select class="form-control" onchange="location = this.value;">';
            foreach ($cityes as $city) {
                if ($city["id"])
                echo '<option '.($city["id"] == $result["cat_id"] ? 'selected ' : '').'value="'.BASE_PATH.'/sozdat-zayavku/?city='.$city["id"].'">'.$city["name"].'</option>';
            }
        echo '</select>
    </div>';

if (count($rows) == 0) {
    echo 'Доступных для Вас торговых центров не найдено!';
    return;
}

echo '
    <div class="form-group">
        Торговый центр:
        <select class="form-control" onchange="location = this.value;">';
            foreach ($rows as $row) {
                echo '<option '.($row["id"] == $result["id"] ? 'selected ' : '').'value="'.BASE_PATH.$row["rewrite"].'">'.$row["name"].'</option>';
            }
        echo '</select>
    </div>';

echo '<div class="shop-item-left">';

		//кнопка "Купить"
		echo $this->get('buy_form', 'shop', array("row" => $result, "result" => $result));

echo '</div>';


echo '</div>';

echo '<div class="shop-item-right">
        <div class="box-block">
            <h2>Информация о торговом центре</h2>'
            .$result["name"]
            .'<a href="/about-tts/?id='.$result["id"].'"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="external-link" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-external-link fa-w-16 fa-2x"><path fill="currentColor" d="M432,320H400a16,16,0,0,0-16,16V448H64V128H208a16,16,0,0,0,16-16V80a16,16,0,0,0-16-16H48A48,48,0,0,0,0,112V464a48,48,0,0,0,48,48H400a48,48,0,0,0,48-48V336A16,16,0,0,0,432,320ZM474.67,0H316a28,28,0,0,0-28,28V46.71A28,28,0,0,0,316.79,73.9L384,72,135.06,319.09l-.06.06a24,24,0,0,0,0,33.94l23.94,23.85.06.06a24,24,0,0,0,33.91-.09L440,128l-1.88,67.22V196a28,28,0,0,0,28,28H484a28,28,0,0,0,28-28V37.33h0A37.33,37.33,0,0,0,474.67,0Z" class=""></path></svg></a>'
            .'<div class="shop_text">'.$this->htmleditor($result['anons']).'</div>
        </div>
        <div class="alerts">
            '.$this->htmleditor('<insert template="danger" name="show_block" id="1" module="site">');
            if($result["ids_param"][4]["value"] == "yes") echo $this->htmleditor('<insert template="warning" name="show_block" id="2" module="site">');
            echo $this->htmleditor('<insert template="info" name="show_block" id="3" module="site">').'
        </div>
    </div>';

echo '</div>';
echo '<script>
    document.querySelector(".left-side__menu li:nth-child(4)").classList.remove("active");
    document.querySelector(".left-side__menu li:last-child").classList.add("active");
</script>';

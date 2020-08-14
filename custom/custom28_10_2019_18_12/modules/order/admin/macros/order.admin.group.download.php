<?php
/**
 * Макрос для групповой операции: Изменение статуса
 *
 * @package    DIAFAN.CMS
 * @author     diafan.ru
 * @version    6.0
 * @license    http://www.diafan.ru/license.html
 * @copyright  Copyright (c) 2003-2019 OOO «Диафан» (http://www.diafan.ru/)
 */

if (!defined('DIAFAN')) {
    $path = __FILE__;
    while (!file_exists($path . '/includes/404.php')) {
        $parent = dirname($path);
        if ($parent == $path) exit;
        $path = $parent;
    }
    include $path . '/includes/404.php';
}

/**
 * Order_admin_group_status
 */
class Order_admin_group_download extends Diafan
{
    /**
     * Возвращает настройки
     *
     * @param string $value последнее выбранное групповое действие
     * @return array|false
     */
    public function show($value)
    {
        $config = array(
            'name' => 'Сформировать документ',
        );

        return $config;
    }

    /**
     * Изменение статуса
     *
     * @return void
     */
    public function action()
    {
        if (empty($_POST["ids"]))
            return;

        $ids = $this->diafan->filter($_POST["ids"], "integer");

        if (count($ids) == 1) {
            $params = $this->getOrderParamElement($ids[0]);

            // Вид документа
            if ($params[19]["value"] == 9) $type_docs = 5;
            else $type_docs = 6;


            $files = $this->getFile($ids[0], $type_docs);
            $urls = array();
            foreach ($files as $number_file => $file) {
                require $_SERVER["DOCUMENT_ROOT"] . '/custom/custom28_10_2019_18_12/plugins/vendor/autoload.php';
                $phpWord = new PhpOffice\PhpWord\PhpWord();
                $doc     = new PhpOffice\PhpWord\TemplateProcessor('https://' . $_SERVER['HTTP_HOST'] . '/attachments/get/' . $file["id"] . '/' . $file["name"]);

                $type = '';
                if (!empty($params[2]["value"])) $type = $params[2]["value"];
                elseif (!empty($params[21]["value"])) $type = $params[21]["value"];
                $types = $this->getTypes($ids[0]);

                // Дата создания
                $date_create = $this->getDateCreate($ids[0]);
                $date_create["created"] = date("d.m.Y", $date_create["created"]);

                // Этаж
                $floor = '';
                if (!empty($params[26]["value"])) {
                    $floor .= 'с '.$params[26]["value"].' этажа';
                    if (!empty($params[29]["value"])) {
                        $floor .= ' на '.$params[29]["value"].' этаж';
                    }
                }
                else {
                    if (!empty($params[4]["value"])) {
                        $floor = $params[4]["value"].' этаж';
                    }
                }

                $arValues = array(
                    'created' => (!empty($date_create["created"]) ? $date_create["created"] : ''),
                    'boss_name'    => (!empty($params[13]["value"]) ? $params[13]["value"] : ''),
                    'boss_phone'   => (!empty($params[25]["value"]) ? $params[25]["value"] : ''),
                    'work'         => (!empty($params[19]["value"] == 9) ? 'Разрешение на проведение работ' : 'Заявка на ввоз/вывоз'),
                    'type'         => $types[$type]["name"],
                    'place_desc'   => (!empty($params[6]["value"]) ? $params[6]["value"] : ''),
                    'floor'        => $floor,
                    'auto_numbers' => (!empty($params[24]["value"]) ? $params[24]["value"] : ''), // позже
                    'auto_brand'   => (!empty($params[24]["value"]) ? $params[24]["value"] : ''), // позже
                    'dop'          => (!empty($params[14]["value"]) ? $params[14]["value"] : ''),
                    'text_import'  => (!empty($params[7]["value"]) ? $params[7]["value"] : ''),
                    'text_export'  => (!empty($params[11]["value"]) ? $params[11]["value"] : ''),
                    'dimensions'   => (!empty($params[24]["value"]) ? $params[24]["value"] : ''), // позже
                    'weight'       => (!empty($params[24]["value"]) ? $params[24]["value"] : ''), // позже
                    'power'        => (!empty($params[24]["value"]) ? $params[24]["value"] : ''), // позже
                );

                if (!empty($params[5]["value"]) && !empty($params[17]["value"])) {
                    $arValues["date"] = date("d.m.y", strtotime($params[5]["value"])) . ' - ' . date("d.m.y", strtotime($params[17]["value"]));
                } else {
                    $arValues["date"] = '';
                }

                $doc->setValues($arValues);

                $names = [];
                if (!empty($params[28]["value"])) {
                    $names = explode("<br />", $params[28]["value"]);
                }
                $passports = [];
                if (!empty($params[31]["value"])) {
                    $passports = explode("<br />", $params[31]["value"]);
                }
                $values = [];
                if (count($names) > 0) {
                    for ($i = 0; $i < count($names); $i++) {
                        $values[] = ['num' => $i + 1, 'staff_fio' => $names[$i], 'staff_passport' => $passports[$i]];
                    }
                }
                try {
                    $doc->cloneRowAndSetValues('num', $values);
                } catch (Exception $e) {
                    ;
                }

                $dir = $_SERVER["DOCUMENT_ROOT"] . '/tmp/orders';
                if (!file_exists($dir))
                    mkdir($dir, 0777, true);

                $doc->saveAs($dir . '/doc'.$ids[0].'_'.$number_file.'.docx');
                $urls[] = $dir . '/doc'.$ids[0].'_'.$number_file.'.docx';
            }
            $this->downloadFile($urls);
        } else {
            //город тц трекер
            $orders = $this->getOrdersInfo($ids);

            // Проверка на одинаковый товары и трекер
            $good_id  = 0;
            $k        = 0;
            $order_id = 0;
            foreach ($orders as $i => $order) {
                if ($k == 0) {
                    $good_id  = $order["id"];
                    $order_id = $i;
                } else {
                    if ($good_id == $order["id"]) continue;
                    else {
                        echo 'Товары разные';
                        exit();
                    }
                }
                $k++;
            }

            // Формируем файл
            if (!empty($order_id)) {
                $files  = $this->getFile($order_id);
                $params = $this->getOrdersParamElement($ids);

                require $_SERVER["DOCUMENT_ROOT"] . '/custom/custom28_10_2019_18_12/plugins/vendor/autoload.php';
                $phpWord = new PhpOffice\PhpWord\PhpWord();
                $doc     = new PhpOffice\PhpWord\TemplateProcessor('https://' . $_SERVER['HTTP_HOST'] . '/attachments/get/' . $files["id"] . '/' . $files["name"]);

                $names     = [];
                $passports = [];
                $j         = 0;
                $first_id  = '';
                foreach ($ids as $id) {
                    if (!empty($params[$id][28]["value"])) {
                        $names_array     = [];
                        $passports_array = [];
                        $names_array     = explode("<br />", $params[$id][28]["value"]);
                        $passports_array = explode("<br />", $params[$id][31]["value"]);
                        $names[]         = $names_array[0];
                        $names[]         = $names_array[1];
                        $passports[]     = $passports_array[0];
                        $passports[]     = $passports_array[1];
                        unset($names_array);
                        unset($passports_array);
                        $j++;
                    }
                }
                $values    = [];
                $staff_fio = [];
                if (count($names) > 0) {
                    $n = 0;
                    for ($i = 0; $i < count($names); $i++) {
                        if (!in_array($names[$i], $staff_fio)) {
                            $staff_fio[] = $names[$i];
                            $values[]    = ['num' => $n, 'staff_fio' => $names[$i], 'staff_passport' => $passports[$i]];
                            $n++;
                        }
                    }
                }
                try {
                    $doc->cloneRowAndSetValues('num', $values);
                } catch (Exception $e) {
                    ;
                }

                foreach ($params as $param) {
                    $arValues = array(
                        'boss_name'  => (!empty($param[13]["value"]) ? $param[13]["value"] : ''),
                        'boss_phone' => (!empty($param[25]["value"]) ? $param[25]["value"] : ''),
                        'date'       => date("d.m.y", strtotime($param[5]["value"])) . ' - ' . date("d.m.y", strtotime($param[17]["value"])),
                        'work'       => (!empty($param[19]["value"] == 9) ? 'Разрешение на проведение работ' : 'Заявка на ввоз/вывоз'),
                        'extra'      => (!empty($param[24]["value"]) ? $param[24]["value"] : ''),
                    );
                    break;
                }
                $doc->setValues($arValues);

                $dir = $_SERVER["DOCUMENT_ROOT"] . '/tmp/orders';
                if (!file_exists($dir))
                    mkdir($dir, 0777, true);

                $doc->saveAs($dir . '/doc.docx');
                $this->downloadFile($dir . '/doc.docx');
            }
        }
    }

    // Получение файла
    private function getFile($id, $type = 5)
    {
        return DB::query_fetch_all("
                SELECT file.id, file.name FROM {shop_order} AS orders
                RIGHT JOIN {shop_order_goods} AS goods ON goods.order_id=orders.id
                RIGHT JOIN {attachments} AS file ON goods.good_id=file.element_id
                WHERE orders.id='%d' AND file.param_id='%d'
            ", $id, $type);
    }

    // Получение параметров списка товаров
    private function getOrdersParamElement($ids)
    {
        $params = [];
        foreach ($ids as $id) {
            $params[$id] = $this->getOrderParamElement($id);
        }
        return $params;
    }

    // Получение параметров
    private function getOrderParamElement($id)
    {
        return DB::query_fetch_key("SELECT * FROM {shop_order_param_element} WHERE element_id='%d' AND trash='0'", $id, "param_id");
    }

    // Загрузка файла
    private function downloadFile($files)
    {
        if (count($files) == 1) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            unlink($file);
            exit();
        }
        else {
            if(extension_loaded('zip')) {
                $zip = new ZipArchive();
                $zip_name = time().".zip";
                $zip->open($zip_name, ZIPARCHIVE::CREATE);
                foreach($files as $file)
                {
                    $name = explode("/", $file);
                    $zip->addFile($file, $name[count($name) - 1]);
                }
                $zip->close();
                if(file_exists($zip_name))
                {
                    header('Content-type: application/zip');
                    header('Content-Disposition: attachment; filename="'.$zip_name.'"');
                    readfile($zip_name);
                    unlink($zip_name);
                }
                echo $zip_name;
            }
            exit();
        }
    }

    // Получение заказов
    private function getOrdersInfo($ids)
    {
        return DB::query_fetch_key("
                SELECT orders.id AS order_id, goods.good_id AS id
                FROM {shop_order} AS orders
                RIGHT JOIN {shop_order_goods} AS goods ON orders.id=goods.order_id
                WHERE orders.id IN (" . implode(",", $ids) . ") 
                AND orders.trash='0' AND goods.trash='0'
                 ",
            "order_id");
    }

    private function getTypes($ids)
    {
        return DB::query_fetch_key("
            SELECT id, [name] FROM {shop_order_param_select} WHERE param_id='2' OR param_id='21' AND trash='0'
        ", "id");
    }

    private function getDateCreate($ids) {
        return DB::query_fetch_array("
            SELECT created FROM {shop_order} WHERE id='%d'
        ", $ids);
    }

}
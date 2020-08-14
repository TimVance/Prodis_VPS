<?php

class SendOrders
{

    public function send($ids)
    {

        $mail_information = $this->getMailInformation();
        $mail_title       = $mail_information["title"];
        $mail_text        = $mail_information["text"];

        if (count($ids) == 1) {

            require $_SERVER["DOCUMENT_ROOT"] . '/custom/custom28_10_2019_18_12/plugins/vendor/autoload.php';

            $params = $this->getOrderParamElement($ids[0]);

            if ($params[19]["value"] == 9) $type_docs = 5;
            else $type_docs = 6;

            $files = $this->getFile($ids[0], $type_docs);

            $urls = array();
            foreach ($files as $number_file => $file) {
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

                $word_name = $dir . '/doc_tmp'.$ids[0].'_'.$number_file.'.docx';
                $doc->saveAs($word_name);

                $pdf_name = 'doc_pdf'.$ids[0].'_'.$number_file.'.pdf';
                $converter = new NcJoes\OfficeConverter\OfficeConverter($word_name);
                $converter->convertTo($pdf_name);

                $urls[] = $dir . '/'.$pdf_name;

            }

            $from_mail = EMAIL_CONFIG;
            $mail      = new PHPMailer\PHPMailer\PHPMailer();
            $mail->setFrom($from_mail);
            $mail->addAddress($from_mail);

            $order_information = $this->getOrdersInfo($ids);
            if (!empty($order_information[$ids[0]]["id"])) {
                $shop_emails  = $this->getShopEmail($order_information[$ids[0]]["id"]);
                $shop_emails  = str_replace(' ', '', $shop_emails);
                $array_emails = explode(",", $shop_emails);
                foreach ($array_emails as $email) {
                    $mail->addAddress($email);
                }
            }

            foreach ($urls as $i => $url) {
                $mail->AddAttachment($url, 'attachment'.$i.'.pdf', $encoding = 'base64', $type = 'application/pdf');
            }

            $mail->isHTML(true);
            $mail->CharSet = "UTF-8";
            $mail->setLanguage('ru');
            $mail->Subject = $mail_title;
            $mail->Body    = $mail_text;
            if ($mail->send()) echo 'Письмо успешно отправлено!';
            else echo 'Письмо не отправлено!';
            echo '<div><a href="/admin/order/"><< Вернуться назад</a></div>';
            exit();

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
                        echo 'ТЦ разные';
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
                        if (!empty($params[28]["value"])) {
                            if (strpos($params[28]["value"], "<br />")) $names_array = explode("<br />", $params[28]["value"]);
                            else $names_array = explode("\n", $params[28]["value"]);
                        }
                        if (!empty($params[31]["value"])) {
                            if (strpos($params[31]["value"], "<br />")) $passports_array = explode("<br />", $params[31]["value"]);
                            else $passports_array = explode("\n", $params[31]["value"]);
                        }
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
                        'work'       => (!empty($param[19]["value"] == 9) ? 'Разрешение на проведение работ' : 'Заявка на ввоз/вывоз'),
                        'extra'      => (!empty($param[24]["value"]) ? $param[24]["value"] : ''),
                    );
                    if (!empty($param[5]["value"]) && !empty($param[17]["value"])) {
                        $arValues["date"] = date("d.m.y", strtotime($param[5]["value"])) . ' - ' . date("d.m.y", strtotime($param[17]["value"]));
                    } else {
                        $arValues["date"] = '';
                    }
                    break;
                }
                $doc->setValues($arValues);

                $dir = $_SERVER["DOCUMENT_ROOT"] . '/tmp/orders';
                if (!file_exists($dir))
                    mkdir($dir, 0777, true);

                $doc->saveAs($dir . '/doc_tmp.docx');

                $converter = new NcJoes\OfficeConverter\OfficeConverter($dir . '/doc_tmp.docx');
                $converter->convertTo('doc_tmp.pdf');

                $from_mail = EMAIL_CONFIG;
                $mail      = new PHPMailer\PHPMailer\PHPMailer();
                $mail->setFrom($from_mail);
                $mail->addAddress($from_mail);

                $order_information = $this->getOrdersInfo($ids);
                if (!empty($order_information[$ids[0]]["id"])) {
                    $shop_emails  = $this->getShopEmail($order_information[$ids[0]]["id"]);
                    $shop_emails  = str_replace(' ', '', $shop_emails);
                    $array_emails = explode(",", $shop_emails);
                    foreach ($array_emails as $email) {
                        $mail->addAddress($email);
                        echo $email;
                    }
                }

                $mail->AddAttachment($dir . '/doc_tmp.pdf', 'order.pdf', $encoding = 'base64', $type = 'application/pdf');
                $mail->isHTML(true);
                $mail->CharSet = "UTF-8";
                $mail->setLanguage('ru');
                $mail->Subject = $mail_title;
                $mail->Body    = $mail_text;
                $mail->send();
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
    private function downloadFile($file)
    {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit();
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

    private function getShopEmail($id)
    {
        return DB::query_result("
                SELECT [value] FROM {shop_param_element}
                WHERE param_id='9' AND element_id='%d' AND trash='0'
            ", $id);
    }

    private function getMailInformation()
    {
        $info_block = DB::query_fetch_key("
                SELECT id, [text] FROM {site_blocks}
                WHERE id='5' OR id='6' AND trash='0'
            ", "id");
        $data       = array(
            "title" => $info_block[5]["text"],
            "text"  => $info_block[6]["text"]
        );
        return $data;
    }

}
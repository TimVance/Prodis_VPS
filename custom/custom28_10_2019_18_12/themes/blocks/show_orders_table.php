<?php
if (! defined('DIAFAN'))
{
    $path = __FILE__; $i = 0;
    while(! file_exists($path.'/includes/404.php'))
    {
        if($i == 10) exit; $i++;
        $path = dirname($path);
    }
    include $path.'/includes/404.php';
}


$download = '';
$download = $this->diafan->filter($_GET, 'url', 'action');
if (!empty($download)) {
    require $_SERVER["DOCUMENT_ROOT"] . '/custom/custom28_10_2019_18_12/plugins/vendor/autoload.php';

    $phpWord = new PhpOffice\PhpWord\PhpWord();
    $doc     = new PhpOffice\PhpWord\TemplateProcessor($_SERVER["DOCUMENT_ROOT"] . '/test.docx');

    $arValues = array(
        'pc'  => 'Дежурный',
        'pc2' => 'Инженер'
    );
    $doc->setValues($arValues);

    $values = [
        ['table' => 1, 'fio' => 'Иванов', 'passport' => 'Паспорт 1'],
        ['table' => 2, 'fio' => 'Петров', 'passport' => 'Паспорт 2'],
    ];
    $doc->cloneRowAndSetValues('table', $values);

    $doc->saveAs($_SERVER["DOCUMENT_ROOT"] . '/test2.docx');
}


// Извлекаем параметры сортировки
$get_order = $this->diafan->filter($_GET, "url", "order");
$get_sort  = $this->diafan->filter($_GET, "url", "sort");

// Направление сортировки
if ($get_sort == "desc") $sort = " DESC";
elseif (empty($get_sort)) {
    $get_sort = 'asc';
    $sort     = " ASC";
} else $sort = " ASC";


// Сортировки
$id_sort        = '?order=id&amp;sort=asc';
$name_sort      = '?order=name&amp;sort=asc';
$track_sort     = '?order=track&amp;sort=asc';
$date_from_sort = '?order=date_from&amp;sort=asc';
$date_to_sort   = '?order=date_to&amp;sort=asc';
$status_sort    = '?order=status&amp;sort=asc';
$user_sort    = '?order=user&amp;sort=asc';
$comment_sort   = '?order=comment&amp;sort=asc';

switch ($get_order) {
    case "name":
        $order = 'gname';
        if ($get_sort == "asc") $name_sort = '?order=name&amp;sort=desc';
        break;
    case "track":
        $order = 'track';
        if ($get_sort == "asc") $track_sort = '?order=track&amp;sort=desc';
        break;
    case "date_from":
        $order = 'date_from';
        if ($get_sort == "asc") $date_from_sort = '?order=date_from&amp;sort=desc';
        break;
    case "date_to":
        $order = 'date_to';
        if ($get_sort == "asc") $date_to_sort = '?order=date_to&amp;sort=desc';
        break;
    case "status":
        $order = 'o.status';
        if ($get_sort == "asc") $status_sort = '?order=status&amp;sort=desc';
        break;
    case "user":
        $order = 'o.user_id';
        if ($get_sort == "asc") $user_sort = '?order=user&amp;sort=desc';
        break;
    case "comment":
        $order = 'comment';
        if ($get_sort == "asc") $comment_sort = '?order=comment&amp;sort=desc';
        break;
    default:
        $order = 'o.id';
        if ($get_sort == "asc") $id_sort = '?order=id&amp;sort=desc';
        break;
}

$orders = DB::query_fetch_key("
                        SELECT o.id, o.user_id, s.[name], s.color, g.good_id AS good,
                        shop.[name] AS gname,
                        (SELECT value FROM {shop_order_param_element} WHERE element_id=o.id AND param_id='5') AS date_from,
                        (SELECT value FROM {shop_order_param_element} WHERE element_id=o.id AND param_id='17') AS date_to,
                        (SELECT value FROM {shop_order_param_element} WHERE element_id=o.id AND param_id='24') AS comment,
                        (SELECT tracker_title.[name] FROM {shop_order_param_element} AS tracker_value
                        RIGHT JOIN {shop_order_param_select} AS tracker_title ON tracker_value.value=tracker_title.id
                        WHERE tracker_value.element_id=o.id AND tracker_value.param_id='19') AS track
                        FROM {shop_order} AS o
                        LEFT JOIN {shop_order_status} AS s ON o.status_id=s.id
                        INNER JOIN {shop_order_goods} AS g ON o.id=g.order_id
                        RIGHT JOIN {shop} AS shop ON g.good_id=shop.id
                        WHERE o.trash='0' AND shop.trash='0' ".($this->diafan->_users->admin ? '' : "AND o.user_id='$this->diafan->_users->id'")."
                        ORDER BY " . $order . $sort, 'id');

echo '<div class="table-scroll">';
echo '<div class="table">';
echo '<div class="table__header">';
echo '<span class="id' . ($get_order == "id" || empty($get_order) ? ' ' . $get_sort : "") . '">Номер заявки<a href="' . BASE_PATH . $id_sort . '"></a></span>';
echo '<span class="name' . ($get_order == "name" ? ' ' . $get_sort : "") . '">Название ТЦ<a href="' . BASE_PATH . $name_sort . '"></a></span>';
echo '<span class="type' . ($get_order == "track" ? ' ' . $get_sort : "") . '">Трекер<a href="' . BASE_PATH . $track_sort . '"></a></span>';
echo '<span class="date' . ($get_order == "date_from" ? ' ' . $get_sort : "") . '">Дата от<a href="' . BASE_PATH . $date_from_sort . '"></a></span>';
echo '<span class="date' . ($get_order == "date_to" ? ' ' . $get_sort : "") . '">Дата до<a href="' . BASE_PATH . $date_to_sort . '"></a></span>';
echo '<span class="status' . ($get_order == "status" ? ' ' . $get_sort : "") . '">Статус<a href="' . BASE_PATH . $status_sort . '"></a></span>';
echo '<span class="user' . ($get_order == "user" ? ' ' . $get_sort : "") . '">Имя<a href="' . BASE_PATH . $status_sort . '"></a></span>';
echo '<span class="actions' . ($get_order == "comment" ? ' ' . $get_sort : "") . '">Комментарий<a href="' . BASE_PATH . $comment_sort . '"></a></span>';
echo '</div>';
if (empty($orders)) echo '<div class="no-orders">Заявок пока не создано!</div>';
else foreach ($orders as $k => $order) {
    $params = DB::query_fetch_key("
                                    SELECT value, param_id FROM {shop_order_param_element} WHERE element_id='%d' AND trash='0'
                                ", $order["id"], "param_id");
    $user_name = DB::query_fetch_all("SELECT fio FROM {users} WHERE id='%d'", $order["user_id"]);
    $type   = 'Заявка на ввоз/вывоз';
    if ($params[19]["value"] == 9) $type = 'Разрешение на проведение работ';

    list($r, $g, $b) = sscanf($order["color"], "#%02x%02x%02x");

    echo '<div style="background-color: rgba(' . $r . ', ' . $g . ', ' . $b . ', 0.25)" class="table__row">';
    echo '<span class="id">№ ' . $order["id"] . '</span>';
    echo '<span class="name">' . $order["gname"] . '</span>';
    echo '<span class="type">' . $type . '</span>';
    echo '<span class="date">' . (!empty($params[5]["value"]) ? 'c ' . $this->diafan->formate_from_date($params[5]["value"]) : "") . '</span>';
    echo '<span class="date">' . (!empty($params[17]["value"]) ? 'по ' . $this->diafan->formate_from_date($params[17]["value"]) : "") . '</span>';
    echo '<span class="status">' . (!empty($order["name"]) ? $order["name"] : "") . '</span>';
    echo '<span class="user">' . (!empty($user_name[0]["fio"]) ? '<a href="https://vend.dlay.ru/admin/users/edit'.$order["user_id"].'/">'.$user_name[0]["fio"].'</a>' : "") . '</span>';
    echo '<span class="comment">' . (!empty($params[24]["value"]) ? $params[24]["value"] : "") . '</span>';
    echo '</div>';
}
echo '</div>';
echo '</div>';

?>
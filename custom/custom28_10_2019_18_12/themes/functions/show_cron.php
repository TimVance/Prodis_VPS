<?php
/**
 * Шаблонный тег: выводит основной контент страницы: заголовка (если не запрещен его вывод в настройке странице «Не показывать заголовок»), текста страницы и прикрепленного модуля. Заменяет три тега: show_h1, show_text, show_module.
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

function calculateNextStep() {
    $rows = DB::query_fetch_all("SELECT * FROM {reglament} WHERE trash='0' AND next_step='0'");
    foreach ($rows as $row) {
        $reglament = explode("|", $row["reglament"]);
        $next = 0;
        if($reglament[0] == 't1') {
            // Таб дни
            $t1_day = $reglament[1];
            $t1_month = intval($reglament[2]);
            $t1_time = $reglament[3];
            $next_date = 0;
            $date = strtotime(date("Y-m-d", time())." ".$t1_time);

            while($next_date < time()) {
                if($date > time()) $next_date = $date;
                else $date = strtotime("+1 days", $date);
            }
            $next = $next_date;
        }
        elseif ($reglament[0] == 't2') {
            // Таб недели
            $t2_week = intval($reglament[1]);
            $t2_days = $reglament[2];
            $t2_time = $reglament[3];
            $t2_days_arr = explode(",", $t2_days);

            if(!empty($t2_days)) {
                // Выбраны дни недели
                $future_day = 0;
                $closed_time = strtotime(date("Y-m-d", time())." ".$t2_time);
                $start_number_of_week = date("W", $closed_time);
                while($future_day == 0) {

                    $current_day_of_week = date("N", $closed_time);
                    foreach ($t2_days_arr as $day) {
                        if($current_day_of_week == $day) {
                            if($closed_time > time()) {
                                $future_day = $closed_time;
                            }
                        }
                    }
                    $closed_time = strtotime("+1 days", $closed_time);
                }
                $next = $future_day;
            }
            else {
                // Не выбраны дни недели
                $last_monday = strtotime("next Monday");
                $next = strtotime(date("Y-m-d", $last_monday).' '.$t2_time);
            }
        }
        else {
            // Таб месяцы
            $radio = $reglament[1];
            if($radio == "r1") {
                $number = intval($reglament[2]);
                $month = intval($reglament[3]);
                $r3_time = $reglament[4];

                $current_month = date("Y-m-", time()).($number < 10 ? "0".$number : $number)." ".$r3_time;
                if(time() > strtotime($current_month)) {
                    $next = strtotime("+1 month", strtotime($current_month));
                }
                else {
                    $next = strtotime($current_month);
                }
            }
            else {
                $number = $reglament[2];
                $day = $reglament[3];
                $month = $reglament[4];
                $t3_time = $reglament[5];

                $number_name = '';
                switch ($number) {
                    case 1:
                        $number_name = "first";
                        break;
                    case 2:
                        $number_name = "second";
                        break;
                    case 3:
                        $number_name = "third";
                        break;
                    case 4:
                        $number_name = "fourth";
                        break;
                    case 5:
                        $number_name = "last";
                        break;
                }

                $day_name = '';
                switch ($day) {
                    case 1:
                        $day_name = "Monday";
                        break;
                    case 2:
                        $day_name = "Tuesday";
                        break;
                    case 3:
                        $day_name = "Wednesday";
                        break;
                    case 4:
                        $day_name = "Thursday";
                        break;
                    case 5:
                        $day_name = "Friday";
                        break;
                    case 6:
                        $day_name = "Saturday";
                        break;
                    case 7:
                        $day_name = "Sunday";
                        break;
                }

                $this_month_date_text = $number_name.' '.$day_name.' of this month';
                $this_month_date = strtotime($this_month_date_text, time());
                $this_month_time = strtotime(date("Y-m-d", $this_month_date)." $t3_time");
                if(time() > $this_month_time) {
                    $next_month_date_text = $number_name.' '.$day_name.' of next month';
                    $next_month_date = strtotime($next_month_date_text, time());
                    $next_month_time = strtotime(date("Y-m-d", $next_month_date)." $t3_time");
                    $next = $next_month_time;
                }
                else {
                    $next = $this_month_time;
                }
            }
        }
        if (!empty($next))
            setNextTime($next, $row["id"]);
    }
}

function recalcNextStep() {
    $rows = DB::query_fetch_all("SELECT * FROM {reglament} WHERE trash='0' AND next_step<'%d' AND next_step>'0'", time());
    foreach ($rows as $row) {
        $reglament = explode("|", $row["reglament"]);
        $next = 0;
        if($reglament[0] == 't1') {
            // Таб дни

            $t1_day = $reglament[1];
            $t1_month = intval($reglament[2]);
            $t1_time = $reglament[3];

            $last_date = (!empty($row["last_action"]) ? $row["last_action"] : $row["created"]);
            while($next < time()) {
                $next_date = strtotime("+".$t1_day." days", $last_date);
                $last_action_month = date("n", $last_date);
                $current_month = date("n", $next_date);
                if($last_action_month == $current_month) {
                    $today_date = date("Y-m-d", $next_date);
                    $next = strtotime($today_date.' '.$t1_time);
                }
                else {
                    $today_date = date("Y-m-d", time());
                    $today_time = strtotime($today_date.' '.$t1_time);

                    $d = new DateTime(date("Y-m-d H:i:s", $today_time));
                    $d->modify( 'first day of +'.$t1_month.' month' );
                    $next = strtotime($d->format("Y-m-d H:i:s"));
                }
                $last_date = strtotime("+".$t1_day." days", $last_date);
            }
        }
        elseif ($reglament[0] == 't2') {
            // Таб недели
            $t2_week = intval($reglament[1]);
            $t2_days = $reglament[2];
            $t2_time = $reglament[3];
            $t2_days_arr = explode(",", $t2_days);

            $last_date = (!empty($row["last_action"]) ? $row["last_action"] : $row["created"]);

            if(!empty($t2_days)) {
                // Выбраны дни недели
                $future_day = 0;
                $closed_time = strtotime(date("Y-m-d", $last_date)." ".$t2_time);
                $start_number_of_week = date("W", $closed_time);
                while($future_day == 0) {

                    $current_day_of_week = date("N", $closed_time);
                    foreach ($t2_days_arr as $day) {
                        if($current_day_of_week == $day) {
                            if($closed_time > time()) {
                                $future_day = $closed_time;
                            }
                        }
                    }

                    $now_number_of_week = date("W", $closed_time);

                    // Если неделя не закончилась
                    if($start_number_of_week == $now_number_of_week) {
                        $closed_time = strtotime("+1 days", $closed_time);
                    }
                    else {
                        $closed_time = strtotime("+".$t2_week." week", $closed_time);
                        $closed_time = strtotime("Monday this week", $closed_time);
                        $closed_time = strtotime(date("Y-m-d", $closed_time).' '.$t2_time);
                        $start_number_of_week = date("W", $closed_time);
                    }
                }
                $next = $future_day;
            }
            else {
                // Не выбраны дни недели
                $next_week = strtotime("+".$t2_week." week", $last_date);
                $monday_next_week = strtotime("Monday this week", $next_week);
                $next = strtotime(date("Y-m-d", $monday_next_week).' '.$t2_time);
            }
        }
        else {
            // Таб месяцы
            $radio = $reglament[1];
            $last_date = (!empty($row["last_action"]) ? $row["last_action"] : $row["created"]);

            if($radio == "r1") {
                $number = intval($reglament[2]);
                $month = intval($reglament[3]);
                $r3_time = $reglament[4];

                $current_month = date("Y-m-", $last_date).($number < 10 ? "0".$number : $number)." ".$r3_time;
                if(time() > strtotime($current_month)) {
                    $next = strtotime("+".$month." month", strtotime($current_month));
                }
                else {
                    $next = strtotime($current_month);
                }
            }
            else {
                $number = $reglament[2];
                $day = $reglament[3];
                $month = $reglament[4];
                $t3_time = $reglament[5];

                $last_date = (!empty($row["last_action"]) ? $row["last_action"] : $row["created"]);

                $number_name = '';
                switch ($number) {
                    case 1:
                        $number_name = "first";
                        break;
                    case 2:
                        $number_name = "second";
                        break;
                    case 3:
                        $number_name = "third";
                        break;
                    case 4:
                        $number_name = "fourth";
                        break;
                    case 5:
                        $number_name = "last";
                        break;
                }

                $day_name = '';
                switch ($day) {
                    case 1:
                        $day_name = "Monday";
                        break;
                    case 2:
                        $day_name = "Tuesday";
                        break;
                    case 3:
                        $day_name = "Wednesday";
                        break;
                    case 4:
                        $day_name = "Thursday";
                        break;
                    case 5:
                        $day_name = "Friday";
                        break;
                    case 6:
                        $day_name = "Saturday";
                        break;
                    case 7:
                        $day_name = "Sunday";
                        break;
                }

                $this_month_date_text = $number_name.' '.$day_name.' of this month';
                $this_month_date = strtotime($this_month_date_text, $last_date);
                $this_month_time = strtotime(date("Y-m-d", $this_month_date)." $t3_time");
                if(time() > $this_month_time) {
                    $next_month_date_text = $number_name.' '.$day_name.' of +'.$month.' month';
                    $next_month_date = strtotime($next_month_date_text, $last_date);
                    $next_month_time = strtotime(date("Y-m-d", $next_month_date)." $t3_time");
                    $next = $next_month_time;
                }
                else {
                    $next = $this_month_time;
                }
            }
        }
        if (!empty($next))
            setNextTime($next, $row["id"]);
    }
}

function setNextTime($time, $id) {
    echo date("Y-m-d H:i:s", $time);
    DB::query("UPDATE {reglament} SET next_step=%d WHERE id=%d", $time, $id);
}

function sendMails() {
    $rows = DB::query_fetch_all("SELECT * FROM {reglament} WHERE trash='0' AND next_step<'%d' AND next_step>'0'", time());
    foreach ($rows as $row) {
        if (!empty($row["ids"])) {
            $ids = explode(",", $row["ids"]);
            $int_ids = array();

            // Очистка от лишних пробелов
            foreach ($ids as $i => $id) {
                $new_ids[] = intval($id);
            }

            require "custom/custom28_10_2019_18_12/plugins/SendOrders/sendOrders.php";
            $sendOrders = new SendOrders();
            $sendOrders->send($new_ids);

            DB::query("UPDATE {reglament} SET last_action=%d WHERE id=%d", time(), $row["id"]);
        }
    }
}

function init() {

    // Отправка заказов
    sendMails();

    // Расчет следующего шага
    calculateNextStep();

    // Пересчет следующего шага
    recalcNextStep();

}

init();
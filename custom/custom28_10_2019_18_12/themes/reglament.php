<?php

if(! defined("DIAFAN"))
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
?>

<!doctype html>
<html lang="ru">
<head>
    <insert name="show_head"></insert>
    <insert name="show_include" file="head"></insert>
</head>
<body>
    <insert name="show_include" file="header"></insert>
    <div class="content flexBetween">
        <insert name="show_include" file="aside">
            <main class="right-side">
                <h1 class="caption"><insert name="show_h1"></insert></h1>
                <div class="white-box site-content">
                    <?php

                        if (!empty($this->diafan->_users->admin)) {
                            echo '
                            <div class="reglament">
                                <div class="reglament__field date">
                                    <div class="reglament__title">Дата активности заявки</div>
                                    c <input type="date"><input type="time"> до <input type="date"><input type="time">
                                </div>
                                <div class="reglament__field">
                                    <div class="reglament__title">Дни недели</div>
                                    <label><input type="checkbox"><span></span>пн</label>
                                    <label><input type="checkbox"><span></span>вт</label>
                                    <label><input type="checkbox"><span></span>ср</label>
                                    <label><input type="checkbox"><span></span>чт</label>
                                    <label><input type="checkbox"><span></span>пт</label>
                                    <label><input type="checkbox"><span></span>сб</label>
                                    <label><input type="checkbox"><span></span>вс</label>
                                    <div>
                                        <div class="reglament__fast-action weekdays">кроме сб, вс</div>
                                        <div class="reglament__fast-action select-all">выбрать все</div>
                                        <div class="reglament__fast-action select-empty">снять все</div>
                                    </div>
                                </div>
                                <div class="reglament__field">
                                    <div class="reglament__title"><span></span>Месяцы</div>
                                    <label><input type="checkbox"><span></span>Январь</label>
                                    <label><input type="checkbox"><span></span>Февраль</label>
                                    <label><input type="checkbox"><span></span>Март</label>
                                    <label><input type="checkbox"><span></span>Апрель</label>
                                    <label><input type="checkbox"><span></span>Май</label>
                                    <label><input type="checkbox"><span></span>Июнь</label>
                                    <label><input type="checkbox"><span></span>Июль</label>
                                    <label><input type="checkbox"><span></span>Август</label>
                                    <label><input type="checkbox"><span></span>Сентябрь</label>
                                    <label><input type="checkbox"><span></span>Октябрь</label>
                                    <label><input type="checkbox"><span></span>Ноябрь</label>
                                    <label><input type="checkbox"><span></span>Декабрь</label>
                                    <div>
                                        <div class="reglament__fast-action select-all">выбрать все</div>
                                        <div class="reglament__fast-action select-empty">снять все</div>
                                    </div>
                                </div>
                                <div class="reglament__field repeat">
                                    <div class="reglament__title">Цикличность</div>
                                    <div>Выполнять в <input min="1" max="31" type="number"> день месяца</div>
                                    <div>Выполнять в <input min="1" max="7" type="number"> день недели</div>
                                </div>
                                <div class="reglament__field">
                                    <div class="reglament__title">Запуск по окончании</div>
                                    Выполнять за <input min="1" type="number"> дней до окончания предыдущей заявки
                                </div>
                                <div class="reglament__field">
                                    <button class="button">Сохранить</button>
                                </div>
                            </div>
                            ';
                        }



                    ?>
                </div>
            </main>
    </div>
    <insert name="show_include" file="footer"></insert>


    <insert name="show_js"></insert>
    <script type="text/javascript" asyncsrc="<insert name="custom" path="js/main.js" absolute="true" compress="js">" charset="UTF-8"></script>
    <script type="text/javascript" asyncsrc="<insert name="custom" path="js/animation.js" absolute="true" compress="js">" charset="UTF-8"></script>
    <script type="text/javascript" asyncsrc="<insert name="custom" path="js/custom.js" absolute="true" compress="js">" charset="UTF-8"></script>
    <script type="text/javascript" asyncsrc="<insert name="custom" path="js/reglament.js" absolute="true" compress="js">" charset="UTF-8"></script>

    <insert name="show_include" file="counters"></insert>

</body>
</html>
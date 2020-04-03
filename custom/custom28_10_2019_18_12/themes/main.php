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
            <div class="main_news revealator-slideup">
                <div class="block_header caption"><insert value="Последние новости"></insert></div>
                <div class="row"><insert name="show_block" module="news" count="3" template="main"></insert></div>
            </div>

            <div class="caption revealator-slideup">Ваши заявки test</div>
            <div class="main_orders white-box revealator-slideup">
                <insert name="show_block" module="userpage"></insert>
                <insert name="show_include" file="show_orders_table"></insert>
            </div>
        </main>
    </div>
    <insert name="show_include" file="footer"></insert>

    <insert name="show_js"></insert>
    <script type="text/javascript" asyncsrc="<insert name="custom" path="js/main.js" absolute="true" compress="js">" charset="UTF-8"></script>
    <script type="text/javascript" asyncsrc="<insert name="custom" path="js/animation.js" absolute="true" compress="js">" charset="UTF-8"></script>
    <script type="text/javascript" asyncsrc="<insert name="custom" path="js/custom.js" absolute="true" compress="js">" charset="UTF-8"></script>

    <insert name="show_include" file="counters"></insert>

</body>
</html>
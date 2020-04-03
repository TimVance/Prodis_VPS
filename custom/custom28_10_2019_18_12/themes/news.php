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
<insert name="show_include" file="header">
<div class="content flexBetween">
    <insert name="show_include" file="aside">
        <main class="right-side">
            <insert name="show_body"></insert>
        </main>
</div>
<insert name="show_include" file="footer">


    <script type="text/javascript" asyncsrc="<insert name="custom" path="js/main.js" absolute="true" compress="js">" charset="UTF-8"></script>
    <insert name="show_js"></insert>

    <!--<insert name="show_privacy" hash="false" text="">-->
    <insert name="show_include" file="counters">

</body>
</html>
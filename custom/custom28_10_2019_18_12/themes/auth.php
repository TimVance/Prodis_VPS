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

    <div class="auth-page">
        <div class="auth-bg-decor">
            <div class="white-box auth-box">
                <div class="auth-box__logo">
                    <insert name="show_href" img="img/logo.png" alt="title" height="60" width="auto">
                </div>
                <h1 class="auth-box__title caption"><insert name="show_h1"></insert></h1>

                <div class="reg-form-content">
                    <insert name="show_body_site"></insert>
                </div>
            </div>
        </div>
    </div>

    <insert name="show_js"></insert>
    <script type="text/javascript" asyncsrc="<insert name="custom" path="js/main.js" absolute="true" compress="js">" charset="UTF-8"></script>

    <insert name="show_include" file="counters"></insert>

</body>
</html>
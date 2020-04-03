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
?>

<aside class="left-side">
    <nav class="left-side__menu">
        <insert name="show_block" module="menu" id="1" template="leftmenu"></insert>
    </nav>
</aside>
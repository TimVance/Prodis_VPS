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
<?php

    //if (empty($this->diafan->_users->id))
        //$this->diafan->redirect('/auth/');

    // Узнаем группу пользователя
    $role = DB::query_result("SELECT r.[name] FROM {users} AS u RIGHT JOIN {users_role} AS r ON r.id=u.role_id WHERE u.id='%d'", $this->diafan->_users->id);

    if(file_exists($_SERVER["DOCUMENT_ROOT"].'/'.USERFILES.'/avatar/'.$this->diafan->_users->name.'.png'))
        $image = BASE_PATH.USERFILES.'/avatar/'.$this->diafan->_users->name.'.png';
        else $image = BASE_PATH.Custom::path('img/avatar.jpg');

?>
<header class="header flexBetween">
    <div class="left-side-header">
        <div class="left-side__logo"><insert name="show_href" img="img/logo-w.png" alt="title" height="60" width="auto"></div>
    </div>
    <div class="right-side">
        <?php
            echo '<div class="pesonal-warp">';
                echo '<div class="flexStart">';
                    echo '<div class="header__avatar"><img src="'.$image.'" alt="'.$this->diafan->_users->fio.' ('.$this->diafan->_users->name.')" class="avatar"></div>';
                    echo '<div class="header_name">
                        <div class="fio">'.$this->diafan->_users->fio.'</div>';
                        echo '<div class="role">'.$role.'</div>
                    </div>
                </div>';
        ?>
        <div class="hide-menu-personal">
            <?php
            echo '<div class="flexStart">';
                echo '<div class="header__avatar"><img src="'.$image.'" alt="'.$this->diafan->_users->fio.' ('.$this->diafan->_users->name.')" class="avatar"></div>';
                echo '<div class="header_name">
                        <div class="fio">'.$this->diafan->_users->fio.'</div>';
                        echo '<div class="role">'.$role.'</div>
                  </div>
            </div>'; ?>
                <ul>
                    <li><i class="fas fa-cog"></i><a href="/settings/">Настройки</a></li>
                    <li><i class="fas fa-sign-out-alt"></i><a href="<?php echo '/logout/?'.rand(0,9999); ?>">Выйти</a></li>
                </ul>
        </div>
    </div>
</header>
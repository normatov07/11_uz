<?php

$categories = ORM::factory('category')->find_all_enabled_cached();
$plain_title = '';

if (!empty($title)) $plain_title .=  $title.' — ';
if (!empty($parent_title)) $plain_title .= $parent_title.' — ';
if (!empty($admMode))$plain_title .= 'Администрирование — ';

$plain_title .= '11.uz';
$zor_title = empty($hp_title) ? $plain_title : $hp_title;

$page_description = empty($page_description) ? '2' : text::HTML2String($page_description, true);
$category_parent_codename = isset($category_parent_codename) ? $category_parent_codename : '';

$rand = empty($admMode) ? rand(0, 10000000000000) : '';

$user = empty($user) || is_null($user) ? false : $user;
$google_id = base64_encode(json_encode($user));


if (!request::is_ajax()): ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <?php if (!empty($browser_redirect)): ?>
            <meta http-equiv="Refresh" content="<?= !empty($browser_redirect[1]) ? $browser_redirect[1] : 10 ?>;URL=<?= !empty($browser_redirect[0]) ? $browser_redirect[0] : '/' ?>">
        <?php endif ?>

        <?php if (!empty($printMode) or !empty($noindex)): ?>
            <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
        <?php endif ?>

        <title>Fast as fire</title>
        <meta name="description" content="11.uz">

        <?php
            if(!empty($css_files)){
                foreach($css_files as $css){
                    ?>
                    <link rel="stylesheet" href="/assets<?=$css?>?v=2" type="text/css">
                    <?php
                }
            }

            if(!empty($js_files)){
                foreach($js_files as $js){
                    ?>
                    <script type="text/javascript" src="/assets<?=$js?>?new"></script>
                    <?php
                }
            }
        ?>

</head>
    <body>
    <?php if (empty($printMode)): ?>
        <div class="topbar">
            <div class="container">
                <div class="row">
                    <div class="menu-icon" onclick="openNav()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-list" viewBox="0 0 15 15">
                        <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
                    </svg>
                    </div>
                    <div class="col contacts">
                        <a href="/contacts" class="text-muted pr-3">Связаться с администрацией</a> <a href="/terms" class="text-muted">Правила пользования</a>
                    </div>
                    <div class="col text-right">
                        <?php if(empty($user)){ ?>
                            <a href="/login/" id="login" class="text-muted">Войти</a>
                            <a href="/register/" class="text-muted pl-3">Регистрация</a>
                        <?php } else { ?>

                            <?php if ((!empty($user) and $user->is_moderator)): ?>
                                <a href="/adm/" class="text-muted pl-3"><i class="ion-md-star"></i> Администратор</a>
                            <?php endif; ?>


                            <a href="/my/offers" class="text-muted pl-3"><i class="ion-md-contact"></i> Мои объявления</a>
                            <a href="/my/settings" class="text-muted pl-3"><i class="ion-md-settings"></i> Настройки</a>
                            <a href="/logout/" class="text-muted pl-3"><i class="ion-md-exit"></i> Выйти</a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <header class="header">
            <div class="container">
                <div class="row">
                    <div class="logo">
                        <a href="/"><img src="/assets/img/logo2.jpg?v=4" alt=""></a>
                        <div class="text-muted">
                            Доска объявлений Узбекистана
                        </div>
                    </div>
                    <div class="top-banner">
                        <a href="/">
                            <div class="banner-src"></div>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="searchbar">
            <div class="container">
                <div class="block-catalog">
                    <div class="bg-info text-white">
                        РАЗДЕЛЫ КАТАЛОГА <i class="ion-md-menu float-right"></i>
                    </div>
                </div>

                <div class="block-search">
                    <form action="/search/page/1/">
                        <div class="input-group mb-3">
                            <input type="text" name="q" class="form-control" placeholder="Поиск по объявлениям" aria-describedby="basic-addon2" required>
                            <div class="input-group-append">
                                <button class="btn btn-info" type="submit"><i class="ion-ios-search"></i></button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="block-add">
                    <a href="/offer/add" class="btn btn-success btn-block text-white text-uppercase"><i class="ion-md-add-circle"></i>  Добавить объявление</a>
                </div>
            </div>
        </div>

        <div id="mySidenav" class="sidenav">
                <div class="title-mini">
                    <a href="/">11.uz</a>
                    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
                </div>
                   <?php foreach ($categories as $category){ 
                        if($category->parent_id == 0){
                    ?>
                        <div class="category-item">
                                <a href="/cat/<?=$category->id?>" class="cat-parent parent-mini"><?=$category->title?></a>
                                <div class="collapse hide">
                                    <?php
                                    foreach ($categories as $subcategory){
                                        if($category->id == $subcategory->parent_id){
                                            ?>
                                            <div class="subcategory child-mini">
                                                <a href="/cat/<?=$subcategory->id?>"><?=$subcategory->title?></a>

                                            </div>
                                        <?php }
                                    }
                                    ?>
                                </div>
                        </div>
                 <?php }
                    }
                  ?>
                  <!-- <hr> -->
              <div class="contact-admin">
                    <a href="/contacts" class="text-muted pr-3">Связаться</a> <a href="/terms" class="text-muted">Правила пользования</a>
             </div>
        </div>

        <main class="wrapper container">
            <div class="sidemenu">

                <script type="text/javascript">
                    $(document).ready(function() {
                        $('.sidenav .category-item .cat-parent').click(function (e) {
                            e.preventDefault();

                            var subcategory = $(this).next();
                            var isClose = subcategory.hasClass('hide');

                            $('.collapse').addClass('hide');
                            if(isClose){
                                subcategory.removeClass('hide');
                            } else {
                                subcategory.addClass('hide');
                            }
                        });
                    });

                    $(document).ready(function() {
                        $('.categoriesBox .category-item .cat-parent').click(function (e) {
                            e.preventDefault();

                            var subcategory = $(this).next();
                            var isClose = subcategory.hasClass('hide');

                            $('.collapse').addClass('hide');
                            if(isClose){
                                subcategory.removeClass('hide');
                            } else {
                                subcategory.addClass('hide');
                            }
                        });
                    });
                </script>

                <div class="categoriesBox">
                    <?php
                    foreach ($categories as $category){
                        if($category->parent_id == 0){
                            ?>
                            <div class="category-item">
                                <a href="/cat/<?=$category->id?>" class="cat-parent"><?=$category->title?></a>
                                <div class="collapse hide">
                                    <?php
                                    foreach ($categories as $subcategory){
                                        if($category->id == $subcategory->parent_id){
                                            ?>
                                            <div class="subcategory">
                                                <a href="/cat/<?=$subcategory->id?>"><?=$subcategory->title?></a>

                                            </div>
                                        <?php }
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php }
                    }
                    ?>


                </div>
                <div class="networks">
                    <a href="https://www.facebook.com/groups/1198233113647562/" target="_blank" class="facebook">Мы на Facebook</a>
                    <br>
                    <a href="https://t.me/tashkent_vkurse" target="_blank" class="telegram">Мы на Telegram</a>
                </div>
                <br><br><br>
            </div>

            <div class="content">

                <?php if (!empty($user) and !$user->is_enabled and empty($dontDisplayNotActivatedWarning)): ?>
                    <div class="alert">
                        <?php
                        switch ($user->status):
                            case 'banned':
                                echo 'Внимание! Ваша учетная запись заблокирована! Доступ к некоторым функциям ограничен.';
                                if ($user->checked === 1):
                                    if ($user->status_change and !empty($user->status_change->reason)):
                                        echo ' Причина: ' . $user->status_change->reason;
                                        echo '<p>Внесите необходимые изменения для разблокировки.</p>';
                                    endif;
                                else:
                                    echo '<p>Ваша учетная запись проверяется Администрацией.</p>';
                                endif;
                                break;
                            case 'disabled':
                                echo 'Внимание! Вы не активировали Вашу учетную запись!';
                                break;
                        endswitch;
                        ?>
                    </div>
                <?php endif; ?>


                <?php if (empty($titleInView))
                    if (!empty($titleBlock)):
                        echo $titleBlock;
                    elseif (!empty($title)):?>
                        <h1><?= $title ?></h1>
                    <?php endif; ?>
                <?php if (!empty($content)) echo $content; // main content?>

            </div>
        </main>

        <footer class="footer">
            <div class="container">
                Copyright © 2018  11.uz — Доска объявлений Узбекистана
            </div>
        </footer>
    <?php endif; //printMode?>
    </body>
</html>
<?php endif; ?>
<script type="text/javascript" src="/assets/js/resize.js"></script>

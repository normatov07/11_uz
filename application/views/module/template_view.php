<?php
require_once 'system/libraries/Mobile_Detect.php';
$detect = new Mobile_Detect;

$branding_class = $detect->isMobile() ? 'mobile' : 'contain';

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
        <!--meta name="viewport" content="width=device-width, initial-scale=1"-->
        <?php if (!empty($browser_redirect)): ?>
            <meta http-equiv="Refresh" content="<?= !empty($browser_redirect[1]) ? $browser_redirect[1] : 10 ?>;URL=<?= !empty($browser_redirect[0]) ? $browser_redirect[0] : '/' ?>">
        <?php endif ?>

        <?php if (!empty($printMode) or !empty($noindex)): ?>
            <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
        <?php endif ?>

        <title><?=strip_tags($zor_title)?></title>
        <meta name="description" content="<?=$page_description?>">

        <link rel="icon" href="/favicon.ico?new_logo" type="ico">
        <link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?= Lib::config('app.url') . '/rss/common.xml' ?>">

        <?php
            if(!empty($css_files)){
                foreach($css_files as $css){
                    ?>
                    <link rel="stylesheet" href="<?=$css?>?343545" type="text/css">
                    <?php
                }
            }

            if(!empty($js_files)){
                foreach($js_files as $js){
                    ?>
                    <script type="text/javascript" src="<?=$js?>?new33"></script>
                    <?php
                }
            }
        ?>

    </head>

    <?php
        require_once 'system/libraries/Mobile_Detect.php';
        $detect = new Mobile_Detect;
    ?>

    <body>

    <?php if (empty($printMode)): ?>

    <div >
        <div>
            <style>
                .area-info {
                    overflow: hidden;
                    padding: .4rem 0;
                    background-color: #ffd426;
                    color: #000;
                }

                .area-info .container{
                    /* width: 980px; */
                    margin: 0 auto;
                    line-height: 2rem;
                }

                .area-info .btn {
                    float: right;
                    color: #000;
                    background-color: #fff;
                    border-color: #fff;
                }
            </style>

            <div class="area-info">
               <div class="container">
                   Прием платежей через системы «Paynet», «CLICK», «Payme» и SMS-оплата временно приостановлен <a href="http://zor.uz/news/32/" class="btn btn-sm">Подробнее</a>
               </div>
            </div>

            <div class="head">
                <h1><a href="/"><b>ZOR.UZ</b><span>САЙТ ОБЪЯВЛЕНИЙ</span><i></i></a></h1>
                <div class="bcorns tpanel">
                    <form action="/search/page/1/"<?php if (!empty($category_id)) echo ' class="w_ext"' ?>>
                        <table>
                            <tr>
                                <td>
                                    <div class="b_gr">
                                        <a href="/offer/add/" class="b_add_new">Разместить объявление</a>
                                    </div>
                                </td>
                            <?php if (!empty($user)): ?>
                                <td>
                                    <div class="b_g">
                                        <a href="/my/offers/" class="my_offers">Мои объявления<?php if ($user->active_offers_count != 0) echo ' (' . $user->active_offers_count . ')'; ?></a>
                                    </div>
                                </td>
                                <td>
                                    <?php else: ?>
                                        <td class="spc">
                                    <?php endif; ?>
                                        <input type="text" name="q" value="Введите название товара или услуги" maxlength="45" class="toggleVal q">
                                        <?php if (!empty($category_id)): ?>
                                            <label for="thisonly">
                                                <input type="checkbox" class="ch" name="category_id" id="thisonly" value="<?= form::value($category_id) ?>"/> Искать в данном разделе
                                            </label>
                                        <?php endif ?>
                                </td>
                                <td><input type="submit" value="Найти" class="but"/></td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>

            <div class="menubar">
                <?php if (empty($hideUserRelated)): ?>
                    <div class="users">
                    <?php if (!empty($user)): ?>
                        <a href="/my/" class="profile"><?= $user->own_name ?></a>
                        <a href="/my/messages/"<?php if ($user->new_messages_count != 0) echo ' class="ora"' ?>> Сообщения<?php if ($user->new_messages_count != 0): ?>: <b id="new_messages">+<?= $user->new_messages_count ?></b><?php endif ?></a>
                        <a href="/my/payment/bonus/" class="mybonuses<?php if ($user->bonus_amount !== null): ?> g"> Бонусы (<span><?= $user->bonus_amount ?></span>)<?php else: ?>">Бонусы<?php endif ?></a>
                        <a href="/my/bookmarks/" class="mybookmarks"> Закладки<?php if ($user->bookmarks_count != 0): ?> (<span><?= $user->bookmarks_count ?></span>)<?php endif ?></a>

                        <?php if ((!empty($user) and $user->is_moderator)): ?>
                            <a href="/adm/"><b>Администратор</b></a>
                        <?php endif; ?>
                        <a href="/logout/">Выйти</a>
                    <?php else: // user not logged in ?>
                        <a href="/login/" id="login">Войти</a>
                        <a href="/register/" class="registration">Регистрация</a>

                        <form id="auth" method="post" action="/login/" style="display:none">
                            <fieldset>
                                <a href="#" class="x" onclick="return false">закрыть окно</a>
                                <h2>Aвторизация</h2>
                                <label for="aemail" id="lemail">E-mail <input type="text" name="email" title="E-mail" maxlength="128" id="aemail"/></label>
                                <label for="apass" id="lpass">Пароль <input type="password" name="password" title="Пароль" maxlength="32" id="apass"/></label>

                                <a href="/lostpass/" id="alost">забыли пароль?</a>
                                <label for="arem" id="lrem">
                                    <input type="checkbox" value="1" checked="checked" name="remember_me" id="arem" class="ch"/>
                                    Запомнить меня на этом компьютере
                                </label>
                                <div class="buttons">
                                    <input type="submit" value="Войти" class="btn btn-success"/>
                                    <input type="reset" value="Закрыть" class="x btn btn-default" onclick="return false"/>
                                </div>
                                <div id="authStatus"></div>
                            </fieldset>
                        </form>
                    <?php endif; // user logged in  */?>
                    </div>
                    <?php if (empty($hideUserRelated) and Lib::config('app.region_select_enabled')): ?>
                        <div class="user_region">
                            <a href="/my/regions/">регион</a> :
                            <?php if ($rcount = count(AppLib::getUserRegions())):
                                $i = 0;
                                $list = '';
                                foreach (AppLib::getUserRegions() as $item):
                                    if (!empty($i)) $list .= ', ';
                                    $list .= $item;
                                    $i++;
                                    if ($i < 3):
                                        $shortlist = $list;
                                    endif;
                                endforeach;
                                if ($rcount > 2):
                                    echo '<b title="' . $list . '">' . $shortlist . ', ...</b>';
                                else:
                                    echo '<b>' . $list . '</b>';
                                endif;
                            else:?>
                                <b>Все регионы</b>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="menu">
                            <a href="/reklama/banner/">Услуги и Реклама</a>
                            <a href="/help/">Помощь</a>
                            <a href="/contacts/">Контакты</a>
                            <b><a href="/rss/">RSS</a></b>
                        </div>
                    <?php endif; ?>
                <?php endif; //hideUserRelated?>
            </div>
            <div class="body">
                <?php if (!empty($user) and !$user->is_enabled and empty($dontDisplayNotActivatedWarning)): ?>
                    <div class="bcorns user_error">
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
            <noindex>
                <div class="bmenubar menu">
                    <a href="/reklama/banner/" class="g">Реклама на сайте и услуги</a>
                    <b><a href="/contacts/">Контакты</a></b>
                    <a href="/stats/" target="_blank">Статистика</a>
                    <a href="/news/">Новости</a>
                    <a href="/help/">Помощь</a>
                    <a href="/terms/">Условия и правила</a>
                    <b><a href="/news/15/">Риелторам</a></b>
                    <a href="/rss/">RSS</a>
                </div>
            </noindex>

                <?php if ((isset($_SERVER["REQUEST_URI"]) && $_SERVER["REQUEST_URI"] == "/") and isset($isLoggedIn) and !$isLoggedIn and empty($printMode)): ?>
            <div class="foot gray">
                    Zor.uz – cайт бесплатных объявлений. Хотите купить или продать квартиру, автомобиль или оборудование? Продаете технику, ноутбук или собаку, ищите сервис или ремонт? <a href="/offer/add/">Размещайте бесплатно объявления</a> с фотографиями и детальной информацией.
            </div>
                <?php elseif (!empty($category_description) and isset($isLoggedIn) and !$isLoggedIn): ?>
            <div class="foot gray">
                    <?=text::HTML2String($category_description, true) ?>
            </div>
                <?php endif; ?>


            <div class="foot">
                <div class="copy">
                    © 2008-<?= date('Y') ?> Zor.uz — Сайт объявлений. Все логотипы и торговые марки на сайте Zor.uz являются собственностью их владельцев.
                    <br>
                    Использование Zor.uz или размещение объявлений на сайте означает принятие условий <a href="/terms/">пользовательского соглашения</a>.

                </div>
                <?php if (empty($printMode) and !empty($user) and $user->is_moderator): ?>
                    <div class="ttime">Time/Memory: {execution_time} / {memory_usage}</div>
                <?php endif; // printMode?>
            </div>

            <?php
                if($branding_class != "mobile"){

            ?>
                <!-- BEGIN JIVOSITE CODE {literal} -->
                <script type='text/javascript'>
                    (function () {
                        var widget_id = 'flNpCcTbq5';
                        var d = document;
                        var w = window;

                        function l() {
                            var s = document.createElement('script');
                            s.type = 'text/javascript';
                            s.async = true;
                            s.src = '//code.jivosite.com/script/widget/' + widget_id;
                            var ss = document.getElementsByTagName('script')[0];
                            ss.parentNode.insertBefore(s, ss);
                        }

                        if (d.readyState == 'complete') {
                            l();
                        } else {
                            if (w.attachEvent) {
                                w.attachEvent('onload', l);
                            } else {
                                w.addEventListener('load', l, false);
                            }
                        }
                    })();</script>
                <!-- {/literal} END JIVOSITE CODE -->

                <?php if($user){ ?>
                <script type='text/javascript'>
                    function jivo_onLoadCallback(){
                        jivo_api.setContactInfo(
                            {
                                name : "<?=$user->name?>",
                                email : "<?=$user->email?>",
                                phone : "<?=$user->phone?>",
                                description : "ID клиента <?=$user->id.PHP_EOL?> Бонусы <?=$user->bonus.PHP_EOL?>"

                            }
                        );

                    }
                </script>
                <?php } ?>
            <?php } ?>

        </div>
        </div>
    <?php endif; //printMode?>

        <script language="javascript" type="text/javascript">
            <!--
            top_js = "1.0";
            top_r = "id=16378&r=" + escape(document.referrer) + "&pg=" + escape(window.location.href);
            document.cookie = "smart_top=1; path=/";
            top_r += "&c=" + (document.cookie ? "Y" : "N")
            //-->
        </script>
        <script language="javascript1.1" type="text/javascript">
            <!--
            top_js = "1.1";
            top_r += "&j=" + (navigator.javaEnabled() ? "Y" : "N")
            //-->
        </script>
        <script language="javascript1.2" type="text/javascript">
            <!--
            top_js = "1.2";
            top_r += "&wh=" + screen.width + 'x' + screen.height + "&px=" +
                (((navigator.appName.substring(0, 3) == "Mic")) ? screen.colorDepth : screen.pixelDepth)
            //-->
        </script>
        <script language="javascript1.3" type="text/javascript">
            <!--
            top_js = "1.3";
            //-->
        </script>
        <script language="JavaScript" type="text/javascript">
            <!--
            top_rat = "&col=340F6E&t=ffffff&p=BD6F6F";
            top_r += "&js=" + top_js + "";
            document.write('<img src="http://cnt0.www.uz/counter/collect?' + top_r + top_rat + '" width=0 height=0 border=0 />')//-->
        </script>
    
        <noscript>
            <img height=0 src="http://cnt0.www.uz/counter/collect?id=16378&pg=http%3A//uzinfocom.uz&col=340F6E&t=ffffff&p=BD6F6F" width=0 border=0/>
        </noscript>

        <div id="fb-root"></div>
        <script language="javascript" type="text/javascript">
            (function (d, w, c) {
                (w[c] = w[c] || []).push(function () {
                    try {
                        w.yaCounter33971130 = new Ya.Metrika({
                            id: 33971130,
                            clickmap: true,
                            trackLinks: true,
                            accurateTrackBounce: true,
                            webvisor: true,
                            trackHash: true
                        });
                    } catch (e) {
                    }
                });
                var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () {
                    n.parentNode.insertBefore(s, n);
                };
                s.type = "text/javascript";
                s.async = true;
                s.src = "https://mc.yandex.ru/metrika/watch.js";
                if (w.opera == "[object Opera]") {
                    d.addEventListener("DOMContentLoaded", f, false);
                } else {
                    f();
                }
            })(document, window, "yandex_metrika_callbacks");

            (function (d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s);
                js.id = id;
                js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5&appId=1684554855115415";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));

            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
            ga('create', 'UA-79760755-1', 'auto');
            ga('send', 'pageview');
            ga('set', 'userId', '<?=$google_id?>');
        </script>
        <noscript>
            <div><img src="https://mc.yandex.ru/watch/33971130" style="position:absolute; left:-9999px;" alt=""/></div>
        </noscript>
    </body>
</html>
<?php endif;
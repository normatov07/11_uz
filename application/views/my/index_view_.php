<div class="main">    <div class="lcol">        <h2 class="g">Личные данные <span><a href="/my/settings/">изменить</a></span></h2>        <div class="ftable bordertable">            <table>                <tr>                    <th><b>Ваш ID</b>:</th>                    <td><span style="font-size:18px"><?= $this->user->id ?></span></td>                </tr>                <tr>                    <th>Статус:</th>                    <td><?= @Lib::Config('app.user_roles', $user->role) ?></td>                </tr>                <tr>                    <th>Ваше имя:</th>                    <td><?= $user->contact_name ? '<b>' . $user->contact_name . '</b>' : 'не указано' ?><?php /* if($user->name_status == 'disabled'):?> <span class="note">(не показывается в объявлениях)</span><?php endif */ ?></td>                </tr>                <tr>                    <th>Электронная почта:</th>                    <td><b><?= $user->email ?></b><?php if ($user->email_status == 'disabled'): ?> <span class="note">(не показывается в объявлениях)</span><?php endif ?>                    </td>                </tr>                <tr>                    <th>Почта для контактов:</th>                    <td><?php                        if ($user->secondary_email):                        echo '<b class="g">'.$user->secondary_email.'</b>';                        if ($user->email_status == 'disabled'):                        ?> <span class="note">(не показывается в объявлениях)</span><?php                        endif;                        else:                        echo 'не указана';                        endif;                        ?></td>                </tr>                <tr>                    <th>Пол:</th>                    <td><?php switch (@$user->gender): case 'male': echo 'мужчина';                        break;                        case 'female': echo 'женщина';                        break;                        default: echo 'не указан';                        endswitch; ?></td>                </tr>                <?php if (@$user->discount > 0): ?>                <tr>                    <th>Индивидуальная скидка:</th>                    <td><b class="g"><?= @$user->discount ?>%</b></td>                </tr>                <?php endif ?>                <tr>                    <th>Дата регистрации:</th>                    <td><?= date::getLocalizedDate(@$user->registered) ?></td>                </tr>            </table>                    </div>        <h2 class="g">Контактные данные <span><a href="/my/settings/#contacts">изменить</a></span></h2>        <div class="bcorns ftable bluetable">            <table>                <tr>                    <th>Телефон:</th>                    <td><?= !empty($user->phone)?'<b>'.format::phone(@$user->phone, NULL, TRUE).'</b>':'не указан' ?></td>                </tr>                <tr>                    <th>Адрес:</th>                    <td><?php if (!empty($user->region)): ?>                        <b><?= @$user->region->title ?></b><?php if (!empty($user->address)) echo ', '.$user->address ?><?php if (!empty($user->reference_point)): echo ' (Ориентир: '.$user->reference_point .')';                        endif; ?><?php else: echo 'не указан или указан не полностью';                        endif ?></td>                </tr>                <tr>                    <th>Веб-сайт:</th>                    <td><?= !empty($user->website)?'<a href="'.$user->website.'">'.$user->website.'</a>':'не указан' ?></td>                </tr>                <tr>                    <td colspan="2" class="settings"><a href="/my/settings/"><b>Настройки:</b> Изменить личные и                            контактные данные</a></td>                </tr>            </table>        </div>    </div></div>
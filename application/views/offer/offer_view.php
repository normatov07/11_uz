<?php echo new View('bread_crumbs', array('obj' => $obj->category)); ?>

<?php
if(!$obj->is_enabled):?>
    <div class="obj_status obj_<?=$obj->status?> bcorns">
        <?php		if(!empty($archived)):
            echo $archived = '<h5>Данное объявление не актуально!</h5><p>Это означает, что оно отключено автором или же закончился срок его публикации.</p>';
        else:
        if(@$obj->is_banned):?>
            <?php				if($obj->checked):?>
                <h5>Объявление заблокировано. <?php if($this->isModerator()):?><a href="<?=$obj->url_unban?>" class="unban">Разблокировать?</a><?php endif?></h5>
                <?php					if(empty($hideContent)):?>
                    <p><?=$obj->status_change->reason;?></p>
                    <?php	/*					if($this->userEnabled() and $obj->is_viewed_by_owner):?>
			<p><a href="<?=$obj->url_edit?>">Внесите необходимые изменения!</a></p>
<?php						endif; */ ?>
                <?php					endif;
            else:?>
                <h5>Объявление проверяется Администрацией. <?php if($this->isModerator()):?><a href="<?=$obj->url_unban?>" class="unban">Разблокировать?</a><?php endif?></h5>
                <?php					if(empty($hideContent)):?>
                    <p>Проверка объявления может занять несколько часов, пожалуйста, подождите.</p>
                <?php					endif;
            endif;
        elseif(@$obj->is_user_banned):?>
        <h5>Объявление заблокировано.
            <p><?=$obj->status_change->reason;?></p>
            <?php			elseif(@$obj->is_deleted):?>
                <h5>Объявление удалено.</h5>
                <p>Просмотр доступен только автору и модераторам.</p>
            <?php			elseif(@$obj->is_disabled):?>
                <h5>Объявление отключено. <?php	if($this->user->status == 'enabled'):?><a href="<?=$obj->url_enable?>" class="enable">Включить?</a><?php endif?></h5>
            <?php			elseif(@$obj->is_expired or $obj->status == 'expired'):?>
                <h5>Срок размещения объявления закончился. <a href="<?=$obj->url_expiration?>" class="modal">Продлить объявление?</a></h5>
            <?php			endif?>
            <?php		endif?>

    </div>
<?php	endif; // status != enabled;?>

<?php	if($this->isModerator() and $obj->complaints->count()):?>
    <div class="complaints bcorns">
        <h5>Жалобы на объявление: <a href="<?=$obj->url_remove_complaint?>" class="remove_complaint">удалить все</a></h5>
        <?php		foreach($obj->complaints as $item):?>
            <div class="item" id="complaint<?=$item->id?>">
                <div class="info"><?=date::getLocalizedDateTime($item->added)?> – <?=$item->user_id?'<a href="/offerer/'.$item->user_id.'/">'.$item->name.'</a>':$item->name?> <span>(<?=html::mailto($item->email)?> | <?=$item->ip_address?>)</span></div>
                <?php		if($item->title != 'Другое'):?>	<h6><?=$item->title?></h6> <?php endif?>
                <?=$item->content?>
                <a href="<?=$obj->url_remove_complaint . $item->id?>/" class="remove_complaint">Удалить</a>
            </div>
        <?php		endforeach?>

    </div>
<?php	endif;?>


<?php if(!empty($obj)):?>

    <div class="main main2">
        <div class="lcol offer">


            <?php

            if(empty($hideContent)):

                echo @$form_messages?>

                <div class="offer_title">
                    <h1><?php /*<b class="offer_type gy"><?=$obj->type->title?>:</b> */?><?=$obj->title?></h1>
                </div>


                <?php $count = $obj->pictures->count();
                if($count):?>
                    <div class="image">
                        <a href="<?=$obj->url_pictures . $obj->pictures[0]->id?>/" target="_blank" class="placeholder modal"><?=$obj->pictures[0]->f('mid','html')?></a>
                        <div class="pilist">
                            <?php
                            $list = '';
                            $i = 1;
                            while(isset($obj->pictures[$i]) and $i <= 3):
                                //if($obj->pictures[$i]->id == $current_pic_id) $current = &$obj->pictures[$i];
                                $list .= '<a href="'.$obj->url_pictures . $obj->pictures[$i]->id.'/" name="'.$obj->pictures[$i]->f('full').'|'.$obj->pictures[$i]->width.'|'.$obj->pictures[$i]->height.'" class="modal">';
                                $list .= '<img src="'.$obj->pictures[$i]->f('thumb').'" width="80" height="60" alt="'.$obj->pictures[$i]->title.'" border="0">';
                                $list .= '</a>';
                                $i++;
                            endwhile;
                            echo $list;
                            ?>
                        </div>
                        <div class="lnk"><a href="<?=$obj->url_pictures?>" class="modal zoom">
                                <?php		if($count > 1):?>Смотреть все <?=format::declension_numerals($count, 'фотография', 'фотографии', 'фотографий')?>
                                <?php		else:?>Увеличить
                                <?php		endif;?>
                            </a></div>
                    </div>
                <?php
                endif;?>

                <div class="properties<?php if($count) echo ' wpic'?>">

                    <table>
                        <tbody>

                        <?php	if(@$obj->price_html):?>
                            <tr>
                                <th>Цена:</th>
                                <td>
                                    <b><?=$obj->price_html?></b>
                                </td>
                            </tr>
                        <?php endif;?>

                        <tr>
                            <th>Регион:</th>
                            <td class="g"><b><?=$obj->region->title?></b></td>
                        </tr>
                        <?php	if(!empty($obj->district_id) and $obj->category->has_district and $obj->region->has_district and $obj->district_id >= -1):?>
                            <tr>
                                <th><?=Lib::config('app.district_type', $obj->region->district_type)?>:</th>
                                <td><?=$obj->district_id > 0 ? $obj->district->title:'любой'?></td>
                            </tr>
                        <?php	endif;?>
                        <?php	if(!empty($obj->subway_id) and $obj->category->has_subway and $obj->region->has_subway):?>
                            <tr>
                                <th>Станция метро:</th>
                                <td><?=$obj->subway->title?></td>
                            </tr>
                        <?php	endif;?>


                        <?php	if($obj->datas->count()): ?>
                            <?php		$previous_property = NULL;
                            foreach($obj->datas as $data):
                                if($previous_property == $data->property->id):?>,
                                    <?=$data->datavalue?>
                                <?php			else:
                                    if(!empty($previous_property)):?>
                                        </td></tr>
                                    <?php					endif?>
                                    <tr><th><?=$data->property->title?>:</th><td><?=$data->datavalue;  if($data->property->units) echo ' '.$data->property->units?>
                                    <?php				$previous_property = $data->property->id;
                                endif;?>
                            <?php		endforeach; ?></td></tr>
                        <?php	endif;?>
                        </tbody>
                    </table>

                </div>

                <div class="mb-1">
                    <table>
                        <tbody>
                        <?php		if($obj->public_phone):?>
                            <tr><th>Телефон:</th><td><?=$obj->public_phone_html;?></td></tr>
                        <?php		endif?>

                        <tr><th>Автор:</th><td><?=$obj->public_name_html;?></td></tr>
                        <?php		if($this->isModerator() and $obj->user->is_agent):?>
                            <tr><th>Агент:</th><td><?=$obj->user->public_name_html?></td></tr>
                        <?php		endif;?>

                        </tbody>
                    </table>

                </div>
            <?php

                if(!empty($obj->description)):?>
                    <div class="description">
                        <?=$obj->description?>
                    </div>
                <?php	endif;?>

                <div class="clb"></div>
                <?php   if (!empty($obj->user->user_certificate->license)
                && !empty($obj->user->user_certificate->license_num)
                && !empty($obj->user->user_certificate->license_dt)):?>
                <i>Услуга лицензирована. Номер лицензии <?=$obj->user->user_certificate->license_num?> от <?=date::getLocalizedDate($obj->user->user_certificate->license_dt)?>.</i>
            <?php   endif;?>
                <?php	if(@$archived):?>
                <div class="obj_status obj_<?=$obj->status?> bcorns">
                    <?php		echo $archived;?>

                </div>
            <?php	else:?>

                <?php	if($this->isModerator() or $obj->is_viewed_by_owner):?>
                    <div class="clb"></div>
                    <?php // MODERATOR MENU
                    if($this->isModerator()):?>
                        <ul class="offer_actions moderator_actions">
                            <li><a href="<?=$obj->url_unpremium?>" class="unpremium <?php if(!$obj->is_premium) echo ' dn'?>">Снять Премиум</a><a href="<?=$obj->url_sms_premium?>" class="premium modal<?php if($obj->is_premium) echo ' dn'?>">Премировать</a></li>
                            <li><a href="<?=$obj->url_unmark?>" class="unmark<?php if(!$obj->is_marked) echo ' dn'?>">Снять выделение</a><a href="<?=$obj->url_sms_mark?>" class="mark modal<?php if($obj->is_marked) echo ' dn'?>">Выделить</a></li>
                            <li><a href="<?=$obj->url_sms_position?>" class="position modal">Поднять</a></li>
                            <li class="moderator"><a href="<?=$obj->url_edit?>" class="lnk">Модерация</a>
                                <ul>
                                    <li><a href="<?=$obj->url_edit?>">Редактировать</a></li>
                                    <li><a href="<?=$obj->url_expiration?>" class="modal">Продлить</a></li>
                                    <li><a href="<?=$obj->url_enable?>" class="enable<?php if(!$obj->is_disabled) echo ' dn'?>">Включить</a><a href="<?=$obj->url_disable?>" class="disable<?php if($obj->is_disabled) echo ' dn'?>">Выключить</a></li>
                                    <li><a href="<?=$obj->url_unban?>" class="unban<?php if(!$obj->is_banned) echo ' dn'?>">Разблокировать</a><a href="<?=$obj->url_ban?>" class="ban modal<?php if($obj->is_banned) echo ' dn'?>">Заблокировать</a></li>
                                    <li><a href="<?=$obj->url_delete?>" class="modal">Удалить</a></li>
                                </ul>
                            </li>
                        </ul>

                    <?php		endif;
                    // OWNER MENU?>
                <?php	endif;?>

                <?php		if($obj->is_enabled and Lib::config('sms.enabled') and ($obj->is_viewed_by_owner or $this->isModerator())):
                    $aggregator = Sms::getConfig();
                    ?>
                    <div class="sms_services">
                        <!-- <h3><b style="color:#c30000">SMS-услуги:</b> премировать, поднять, выделить объявление</h3> -->
                        <!--div class="lnks">
            	<a href="<?=$obj->url_sms_premium?>" class="modal premium">Премировать</a>
				<a href="<?=$obj->url_sms_position?>" class="modal mark">Поднять наверх</a>
				<a href="<?=$obj->url_sms_mark?>" class="modal position">Выделить цветом</a>
			</div-->
                        <?php /* <p>Стоимость SMS-сообщения (включая все налоги): <?=format::money($price, $currency)?></p> */ ?>
                    </div>
                <?php		endif?>


                <div class="clb"></div>


            <?php	endif; // archived	 ?>

                <?php	if(!@$archived): ?>

                <?php // STATISTICS ?>
                <div class="offer_stats">
                    Просмотров: <i class="g"><?=$obj->views_count?></i>&nbsp; |&nbsp;
                    Опубликовано: <i><?=date::getLocalizedDateTime($obj->added)?></i>&nbsp; |&nbsp;
                    ID объявления: <i><?=$obj->id?></i><br />
                    <span id="expires">Актуально: <i>до <?=date::getLocalizedDateTime($obj->expiration)?></i></span>
                    <?php	if($this->isModerator() or $obj->is_viewed_by_owner):?>
                        <span id="premium_till">
<?php		if($obj->is_premium):?>
    |
    <b class="ora">Премиум:</b> <i>до <?=date::getLocalizedDateTime($obj->premium_till)?></i>
<?php		endif?>
			</span>
                        <span id="marked_till">
<?php		if($obj->is_marked):?>
    |
    <b class="ora">Выделено:</b> <i>до <?=date::getLocalizedDateTime($obj->marked_till)?></i>
<?php		endif?>
			</span>
                    <?php	endif;?>
                    <br><br><br>
                </div>

            <?php	endif; // archived	 ?>
            <?php
            endif;// hide Content?>

            <?php

            /**
             * Другие объявления этого автора
             */

            if(!empty($otherOwnersOffers) and $otherOwnersOffers->count()):?>
                <div class="owners_offers">
                    <h2>Другие объявления <?=$obj->user->is_company?'от '.($obj->user->public_name?$obj->user->public_name:'организации'):' пользователя';?></h2>
                    <ul>
                        <?php	$i = 0;
                        foreach($otherOwnersOffers as $offer):?>
                            <li<?php if($i++%2 == 0) echo ' class="even"'?>>
                                <?php		if(isset($pictures[$offer->id])):?>
                                    <a href="<?=$offer->url?>" class="i"><div class='offer-photo text-center' style='background-image: url("<?=$pictures[$offer->id]->f('thumb')?>")'></div></a>
                                <?php		endif?>
                                <h3><a href="<?=$offer->url?>"><?=$offer->title?></a></h3>
                                <?php		if($offer->price_html):?>
                                    <div class="p">Цена: <b><?=$offer->price_html?></b></div>
                                <?php endif?>
                            </li>
                        <?php	endforeach;?>
                    </ul>
                    <div class="clb"></div>
                    <div class="owners_offers_links">
                        <a href="<?=$obj->user->url?>">Все объявления <?=$obj->user->is_company?'от '.($obj->user->public_name?$obj->user->public_name:'организации'):' пользователя';?> →</a>

                    </div>
                </div>
            <?php endif?>


        </div>


    <div id="modalBox" class="jqmWindow"><a href="#" class="jqmClose x">закрыть</a><div class="jqmContent"></div></div>
<?php endif?>


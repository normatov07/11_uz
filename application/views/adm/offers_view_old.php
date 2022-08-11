<div class="main">

	<form name="filters" action="/adm/offers/">
		<?php echo @$form_messages?>
		<div class="filters bcorns"><i class="ct"><i></i><b></b></i>
			<div class="content">
				<div class="select">
					<label>Тип:</label>
					<?=form::dropdown(array('name'=>'type_id', 'id'=>'type_id', 'title'=>'Тип объявления', 'class'=>'nodis'), $types, @$_REQUEST['type_id']);?>
				</div>
				<div class="select">
					<label>Период:</label>
					<?=form::dropdown(array('name'=>'added_period', 'id'=>'added_period', 'title'=>'Период', 'class'=>'nodis'), Lib::config('app.periods'), @$_REQUEST['added_period']);?>
				</div>
				<div class="select">
					<label>Раздел:</label>
					<?=form::dropdown(array('name'=>'category_id', 'id'=>'category_id', 'title'=>'Раздел', 'class'=>'category nodis'), array('' => 'любой') + @$maincategories, @$_REQUEST['category_id']);?>
				</div>
				<div class="select">
					<label>Ключевое слово или фраза:</label>
					<input type="text" name="q" value="<?=form::value($q_str)?>" class="q" maxlength="45" title="Ключевое слово или фраза" />
				</div>
				<div class="but"><input type="submit" value="Показать →"  /></div>

				<div class="clb"></div>
			</div>

			<i class="cb"><i></i><b></b></i>
		</div>
	</form>

<?php if(!empty($_GET) and !empty($offerList)):?>
		<div class="results">Найдено: <b><?=format::declension_numerals(@$offersCount, '<b>объявление</b>','<b>объявления</b>','<b>объявлений</b>');?></b><?php /*if($results['categories']):?> в <b>3</b> разделах<?php endif */?></div>
<?php endif;?>

<?php if(!empty($offerList) and $offerList->count()):	?>
	<form name="myofferslist" action="" method="post" id="editlist_form" class="editlist offerlist">

<?php	foreach($offerList as $offer):

			if($offer->is_expired and $offer->status == 'enabled') $offer->status = 'expired';?>

			<div class="ofr <?php
				if($offer->is_premium):
					echo ' premium';
				elseif($offer->is_marked):
					echo ' marked or';
				elseif($offer->is_positioned):
					echo ' positioned';
				endif;

				echo ' ' . $offer->status;

				?>" id="o<?=$offer->id?>"><i class="ct"><i></i><b></b></i>
				<table>
					<tr>
						<td class="ty"><?=$types[$offer->type_id]?><?php if(isset($pictures[$offer->id])):?>
							<a href="<?=$offer->url?>"><?php echo $pictures[$offer->id]->f('thumb','html')?></a>
<?php endif?></td>
						<td class="d">
							<h3 class="title"><?=$offer->title?></h3>
							<div class="c"><?php
								if(isset($categories[$offer->category_id])):
                                    if ($categories[$offer->category_id]->parent_id)
								echo $categories[$categories[$offer->category_id]->parent_id]->title . ': ';
                                    echo $categories[$offer->category_id]->title;
                                endif?></div>
							<div class="price" title="<?=$offer->price_type.'|'.$offer->currency.(!empty($offer->price_to)?'|1':'')?>">Цена: <?=$offer->price_html?$offer->price_html:'<span>не указана</span>';?></div>
							<div class="description"><?=$offer->description;?></div>
						</td>
						<td class="s">
							<div class="c"><?=$regions[$offer->region_id];?></div>
							<b>Добавлено</b> <p><?=date::getRelativeDatetime($offer->added)?></p>
							<div>
<?php	switch($offer->status):
			case 'enabled':?>
							<i>Актуально до</i>
							<p><?=date::getSimple($offer->expiration, 'd.m.Y')?></p>
<?php		break;
			case 'expired':?>
							<i>Просрочено</i>
							<p><?=date::getSimple($offer->expiration)?></p>
<?php		break;
			case 'banned':?>
							<i>Заблокировано</i>
<?php						if(!$offer->checked):?>
							<p>На проверке</p>
<?php						else:?>
							<p>&nbsp;</p>
<?php						endif;?>
<?php		break;
			default:?>
							<i><?=Lib::config('app.offer_status', $offer->status)?></i>
							<p>&nbsp;</p>
<?php		break;
		endswitch;?>		</div>

							<div class="u">Пользователь: <a href="<?=@$users[$offer->user_id]->url_edit?>"><?=@$users[$offer->user_id]->own_name?></a></div>
							<div class="mo"><?php
								if($offer->is_premium):
									echo 'Премиум';
								elseif($offer->is_marked):
									echo 'Выделено';
								elseif($offer->is_positioned):
									echo 'Поднято';
								else:
									echo 'Обычное';
								endif;?></div>
						</td>

						<td class="m"><input type="checkbox" name="id[]" value="<?=form::value($offer->id)?>" /><?=$offer->act_icons?></td>
					</tr>
				</table>
				<div class="actions">
<?php	//if($mode != 'deleted'):?>
					<div class="lnks">
						<a href="<?=$offer->url?>" class="v">Посмотреть</a>
						<span class="pay<?php if($offer->status != 'enabled') echo ' dn'?>">
<?php		if($this->isModerator()):?>
							<a href="<?=$offer->url_premium?>" class="modal list_premium<?php if($offer->is_premium) echo ' dn'?>">Премиум</a>
							<a href="<?=$offer->url_unpremium?>" class="list_unpremium<?php if(!$offer->is_premium) echo ' dn'?>">Снять Премиум</a>
							<a href="<?=$offer->url_mark?>" class="modal list_mark<?php if($offer->is_marked) echo ' dn'?>">Выделить</a>
							<a href="<?=$offer->url_unmark?>" class="list_unmark<?php if(!$offer->is_marked) echo ' dn'?>">Снять выделение</a>
							<a href="<?=$offer->url_position?>" class="list_position">Поднять</a>
<?php		elseif($offer->is_viewed_by_owner):?>
							<a href="<?=$offer->url_payment_premium?>">Премиум</a>
							<a href="<?=$offer->url_payment_mark?>">Выделить</a>
							<a href="<?=$offer->url_payment_position?>">Поднять</a>
<?php		endif;?>
						</span>
					</div>
					<div class="but">
						<a href="<?=$offer->url_edit?>" class="edit">Редактировать</a>
						<a href="<?=$offer->url_expiration?>" class="modal list_expiration<?php if($offer->status != 'enabled' and $offer->status != 'expired') echo ' dn'?>">Продлить</a>
						<a href="<?=$offer->url_enable?>" class="list_enable<?php if( $offer->status != 'disabled') echo ' dn'?>">Включить</a>
						<a href="<?=$offer->url_disable?>" class="list_disable<?php if( $offer->status != 'enabled') echo ' dn'?>">Отключить</a>
						<a href="<?=$offer->url_ban?>" class="list_ban modal<?php if( $offer->status == 'banned') echo ' dn'?>">Забанить</a>
						<a href="<?=$offer->url_unban?>" class="list_unban<?php if( $offer->status != 'banned') echo ' dn'?>">Разбанить</a>
						<a href="<?=$offer->url_delete?>" class="delete modal">Удалить</a>
					</div>
<?php	/*else:?>
					<div class="but">
						<a href="<?=$offer->url_undelete?>" class="list_undelete">Восстановить</a>
<?php		if($this->isModerator()):?>
						<a href="<?=$offer->url_remove?>" class="remove modal">Удалить окончательно</a>
<?php		endif?>
					</div>
<?php	endif */?>
				</div>
				<div class="clb"></div>
			</div>
<?php	endforeach;?>

		<div class="buttons">
			<div class="service_links"><a href="#" id="select_all">Выделить все</a> <a href="#" id="deselect_all">Отменить выделение</a></div>
			<div class="note">Действия над выделенными объявлениями:</div>
<?php	echo @$form_messages?>
<?php	//if($mode != 'deleted'):?>

<?php	//	if(@$mode != 'enabled'):?>
			<input type="submit" name="enable_selected" value="Включить" />
<?php	//	endif;
		//	if(@$mode != 'disabled'):?>
			<input type="submit" name="disable_selected" value="Выключить" />
<?php	//	endif;?>
			<input type="submit" name="delete_selected" value="Удалить"/>
			<input type="submit" name="remove_selected" value="Удалить совсем"/>
<?php		/*if($mode != 'disabled'):?>
			<span class="pay">
				<input type="submit" name="premium_selected" value="Премировать" class="premium" />
				<input type="submit" name="mark_selected" value="Выделить" class="mark" />
				<input type="submit" name="position_selected" value="Поднять" class="position" />
			</span>
<?php		endif; //*/?>
<?php	/*else:?>
			<input type="submit" name="recover_selected" value="Восстановить" />
<?php		if($this->isModerator()):?>
			<input type="submit" name="remove_selected" value="Удалить" />
<?php		endif;?>
<?php	endif */?>
			<span id="ajaxstatus">Отправка данных...</span>
		</div>

		<?php echo $pagination;?>

		<div id="modalBox" class="jqmWindow"><a href="#" class="jqmClose x">закрыть</a><div class="jqmContent"></div></div>
	</form>
	<p class="nolistitems dn"><?=!empty($notfound)?$notfound:'Нет объявлений с заданными параметрами.'?></p>
<?php else:?>
	<p class="tmar"><?=!empty($notfound)?$notfound:'Нет объявлений с заданными параметрами.'?></p>
<?php endif; // offersList Count?>

</div>
<script type="text/javascript">
	var pt = [<?php $i = 0; foreach(Lib::config('app.price_type') as $key => $item): if($i>0) echo ', '?>{value: "<?=$key?>", text: "<?=$item?>"}<?php $i++; endforeach;?>];
	var cur = [<?php $i = 0; foreach(Lib::config('payment.currency_list') as $key => $item): if($i>0) echo ', '?>{value: "<?=$key?>", text: "<?=$item?>"}<?php $i++; endforeach;?>]
</script>
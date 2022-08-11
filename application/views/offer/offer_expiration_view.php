<?php if(!empty($offer)):
	
	if(!$this->isModerator() and $offer->expiration > date::getForDb(strtotime('+4 days'))):?>
	<h1><?=@$title?></h1>
	<h2><b class="offer_type"><?=$offer->type->title?>:</b> <?=$offer->title?></h2>
	<p>Вы можете продлить объявление только за 4 дня до завершения срока размещения (т.е. после <?=date::getForDb(strtotime($offer->expiration . ' - 4 days'))?>).</p>
<?php	else:?>
<form id="message_form" method="post" action="<?=$offer->url_expiration?>" class="expiration_form">
<div class="offer_message bmar">
	
	<input type="hidden" name="offer_id" value="<?=form::value($offer->id)?>" />
	
	<h1><?=@$title?></h1>
	<h2><b class="offer_type"><?=$offer->type->title?>:</b> <?=$offer->title?></h2>
	<table>
		<tbody>
			<tr>
				<th>На сколько дней продлить объявление:</th>
				<td><?=form::daysAmountSelect(array('name'=>'days_to_add', 'title'=>'Срок размещения'), Lib::config('app.offer_expiration_days'));?></td>
			</tr>	
			<tr>
				<td colspan="2"><input type="submit" name="send" value="Отправить" class="submit" /> 
					<input type="submit" name="cancel" value="Отменить" />
					<span id="ajaxstatus">Отправка данных...</span>
				</td>
			</tr>
		</tbody>
	</table>

</div>
<?php echo @$form_messages?>
</form>
<?php endif;?>
<div class="back_to_offer"><a href="<?=$offer->url?>">← Вернуться к просмотру объявления</a></div>	
<?php endif;?>
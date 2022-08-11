<?php if(!empty($offer)):?>
<?php 
$amount = Lib::config('payment.service', $service_id, 'amount');
$units = Lib::config('payment.unit', Lib::config('payment.service', $service_id, 'unit'));
$max_amount = Lib::config('payment.max_amount_count');

$disable_action = false; 
$dd_params = NULL;
if (($bonus_amount < $tariff) && (!$this->isModerator())) 
{
	$disable_action = true;
	$dd_params = array('disabled' => 'disabled');
}
?>
<form id="message_form" method="post" action="<?=($this->isModerator())?$offer->url_premium:$offer->url_payment_premium;?>" class="premium_form">
<?php if(!$this->isModerator()):?>	
<input type="hidden" name="service_id" value="<?=form::value($service_id)?>"/>	
<input type="hidden" name="method_id" value="<?=form::value('bonus')?>"/>	
<input type="hidden" name="id[]" value="<?=form::value($offer->id)?>"/>	
<input type="hidden" name="proceed" value="1"/>	
<?php endif;?>
<div class="offer_message bmar">
	
	<input type="hidden" name="offer_id" value="<?=form::value($offer->id)?>" />
	
	<h1><?=@$title?></h1>
    <p>Ваше объявление будет перенесено в список премиум объявлений.<br />
	<h2><b class="offer_type"><?=$offer->type->title?>:</b> <?=$offer->title?></h2>
	<table>
		<tbody>
			<tr>
				<th><?=@$action?> объявления на:
<?php	
		$unitslist = array();
		
		$i = 1;

//		if (!$this->isModerator())
//		{
//			$max_amount = min($max_amount, intVal($bonus_amount/$tariff));
//		}
		while($i <= $max_amount):
			$unitslist[$i*$amount] = format::declension_numerals($i*$amount, $units);
			$i++;
		endwhile;
		
		if(empty($count)) $count = $tariff;
		
		if($amount): ?>	
			<?=form::dropdown(array('name'=>'count', 'id' => 'count'), $unitslist, @$count, $dd_params)?>
			<script type="text/javascript">tariff = <?=($tariff/$amount)?></script>
<?php	endif;?>
				</th>
               
			</tr>	
			<tr>
				<td colspan="2"><input type="submit" name="send" value="Применить" class="submit" <?php if ($disable_action):?>disabled="disabled"<?php endif;?>/> 
					<input type="submit" name="cancel" value="Отменить" <?php if ($disable_action):?>disabled="disabled"<?php endif;?>/>

					<?php if ($disable_action):?>
					<span style="color: #f00;padding: 2px 0;margin: 0;display: inline;">Недостаточное количество бонусов</span>
					<?php endif;?>
					<span id="ajaxstatus">Отправка данных...</span>
				</td>
			</tr>
		</tbody>
	</table>
    <br />
	
    <!--h1><?=@$action?> с помощью SMS-сообщения</h1>
    <p>Отправьте SMS-сообщение <b class="r"><?=$keyword?>&nbsp; <?=$offer->id?></b> на номер <b><?=$short_number?></b></p>
	<div class="instructions">
		<p>— Объявление будет перенесено в список премиум объявлений на 1 день.</p>
        <p>— Ежедневно в течение оплаченного периода Вы будете получать на свой мобильный телефон <br />SMS-уведомления о новых сообщениях на объявление.</p>
		<p>Стоимость услуги: <b><?=format::money($price, $currency)?></b> (включая все налоги)</p>
		
		<p class="gy"><b>Услуга доступна для абонентов: <?=$providers?></b></p>
		
		<p class="gy">Примечание: если Вы хотите отказаться от SMS уведомлений, зайдите в Личный кабинет в раздел «<?=$this->isLoggedIn() ? '<a href="/my/settings/">Настройки</a>':'Настройки'?>» и отключите получение SMS-уведомлений.</p>
		
		<p class="gy"><a href="/terms/">Пользовательское соглашение для посетителей и пользователей сайта</a></p>
	</div-->

</div>
<?php echo @$form_messages?>
</form>
<div class="back_to_offer"><a href="<?=$offer->url?>">← Вернуться к просмотру объявления</a></div>	
<?php endif;?>
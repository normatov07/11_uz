<?php if(!empty($offer)):?>

<div class="offer_message sms bmar">
	
	<h1><?=@$title?></h1>
	<h2><?=$service['title']?></h2>
<?php	if(!request::is_ajax()):?>	
	<p><b>&laquo;<?=$offer->fulltitle?>&raquo;</b></p>	
<?php	endif;?>
	<!--p>Отправьте SMS-сообщение <b class="r"><?=$keyword?> <?=$offer->id?></b> на номер <b><?=$short_number?></b></p>
	<div class="instructions">
		<p>— <?=$service['description']?><br />
		— Ежедневно в течение <?=$service_expiration?> Вы будете получать на свой мобильный телефон <br />SMS-уведомления о новых сообщениях на объявление.</p>
		<p>Стоимость услуги: <b><?=format::money($price, $currency)?></b> (включая все налоги)</p>	
		
		<p class="gy"><b>Услуга доступна для абонентов: <?=$providers?></b></p>
		
		<p class="gy">Примечание: если Вы хотите отказаться от SMS уведомлений, зайдите в Личный кабинет в раздел «<?=$this->isLoggedIn() ? '<a href="/my/settings/">Настройки</a>':'Настройки'?>» и отключите получение SMS-уведомлений.</p>
		
		<p class="gy"><a href="/terms/">Пользовательское соглашение для посетителей и пользователей сайта <?=Lib::config('app.title')?></a></p>
	</div-->
</div>

<div class="back_to_offer"><a href="<?=$offer->url?>">← Вернуться к просмотру объявления</a></div>	
<?php endif;?>
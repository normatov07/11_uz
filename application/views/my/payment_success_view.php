<div class="main">
	<h2 class="g">Спасибо, Ваш оплата прошла успешно.</h2>
	<p>Подробности: <?=@$this->payment->details?></p>
	<br />
<?php	if(!$this->isLoggedIn()):?>
	<p><b><a href="/login/">Пройдите авторизацию</a></b> (залогинитесь)</p>
<?php	else:?>
<?php		if($this->user->active_offers_count == 0):?>
	<p><a href="/offer/add/">Добавить объявление</a></p>
<?php		endif;?>	
<?php		if($this->user->offers->count()):?>
	<p><a href="/my/offers/">Просмотреть свои объявления</a></p>
<?php		endif;?>
<?php endif?>
	<p><a href="/terms/">Условия и правила</a></p>
	<p><a href="/">На главную страницу</a></p>	
</div>
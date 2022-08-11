<div class="main">
	<p>Поздравляем, Ваш аккаунт активирован.</p>
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
	<p><a href="/">На главную страницу</a></p>	
</div>
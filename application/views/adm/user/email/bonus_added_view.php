Здравствуйте,

Сообщаем, что на Ваш аккаунт зачислено <?=format::declension_numerals($bonuses, Lib::config('payment.unit', 'bonus'))?>.

Детали:
<?=@$payment_details?>

Теперь всего Вам доступно: <?=format::declension_numerals($total, Lib::config('payment.unit', 'bonus'))?>.

Напоминаем, что при помощи бонусов Вам доступны следующие возможности:
<?php foreach(Lib::config('payment.service') as $id => $service):
	if($id == 'bonus') continue;?>
 - <?=$service['title']."\n"?>
<?php endforeach;?>

ВНИМАНИЕ: Это электронное письмо отправлено автоматически. Отвечать на него не следует.

--
Система оповещений,
<?=Lib::config('app.title')?> - <?=Lib::config('app.subtitle')?>.
<?=Lib::config('app.url')?>
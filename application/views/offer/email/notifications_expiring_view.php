Здравствуйте,

Ваше объявление "<?=$offer_title?>"
<?php 
	if($is_expired):?> 
Показ объявления завершен <?=$expiration?>. 
<?php else:?>
Показ объявления завершается <?=$expiration?>. 
<?php endif;?> 

- Просмотреть объявление:
  <?php echo Lib::config('app.url') . $offer_url;?> 

- Продлить объявление:
  <?php echo Lib::config('app.url') . $offer_url_expiration;?> 

- Удалить объявление:
  <?php echo Lib::config('app.url') . $offer_url_delete;?> 

ПРИМЕЧАНИЕ: Чтобы продлить показ объявления Вы должны быть авторизированы.


ВНИМАНИЕ: Это электронное письмо отправлено автоматически. Отвечать на него не следует.

--
Система оповещений,
<?=Lib::config('app.title')?> - <?=Lib::config('app.subtitle')?>.
<?=Lib::config('app.url')?>
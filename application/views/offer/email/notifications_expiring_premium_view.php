Здравствуйте,

Сообщаем, что показ Вашего объявления в Премиум режиме завершается <?=$expiration?>:
 <?=$offer_title?> 


- Просмотреть объявление:
  <?php echo Lib::config('app.url') . $offer_url;?> 


Чтобы продлить Премиум показ объявления, авторизируйтесь <?php echo Lib::config('app.url') . '/login/';?> 
и откройте ссылку:  <?php echo Lib::config('app.url') . $offer_url_premium;?>.


ВНИМАНИЕ: Это электронное письмо отправлено автоматически. Отвечать на него не следует.

--
Система оповещений,
<?=Lib::config('app.title')?> - <?=Lib::config('app.subtitle')?>.
<?=Lib::config('app.url')?>
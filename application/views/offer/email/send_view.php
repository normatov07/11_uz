<p>Здравствуйте,</p>
<p>
<?=$author_name?> (<?=$author_email?>), отправил Вам ссылку на объявление:
</p>
<p>Комментарий: <br/>
 <?=$message_content?> </p>

<p>
- Название объявления:<br/>
  <?=$offer_title?> </p>
<p>
- Ссылка на объявление: 
  <?php echo Lib::config('app.url') . $offer_url;?><br/>
</p>
<p>
ВНИМАНИЕ: Это электронное письмо отправлено автоматически. Отвечать на него не следует.
</p><p>
--<b/>
Система оповещений,<b/>
<?=Lib::config('app.title')?> - <?=Lib::config('app.subtitle')?>.<b/>
<?=Lib::config('app.url')?></p>
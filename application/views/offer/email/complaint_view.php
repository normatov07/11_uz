<p>Здравствуйте,<br/>

Сообщаем, что подана жалоба на объявление: </p>
<p>
 <?=$offer_title?>
</p>
<p>
Тема жалобы:
</p><p>
 <?=$message_title?>
</p>
<p>
Текст жалобы: <br>

 <?=$message_content?>
</p>
<p>
Автор: <?=$author_name?> <br/>
E-mail: <?=$author_email?>
</p>
<p>
- Просмотреть объявление: <br/>
  <?php echo Lib::config('app.url') . $offer_url;?>
</p><p>
- Просмотреть жалобу:  <br/>
  <?php echo Lib::config('app.url') . $message_url;?>
</p><p>
- Ответить на жалобу:  <br/>
  <?php echo Lib::config('app.url') . $message_url_reply;?>
</p><p>

ВНИМАНИЕ: Это электронное письмо отправлено автоматически. Отвечать на него не следует.
</p><p>
--
Система оповещений, <br/>
<?=Lib::config('app.title')?> - <?=Lib::config('app.subtitle')?>. <br/>
<?=Lib::config('app.url')?></p><p>
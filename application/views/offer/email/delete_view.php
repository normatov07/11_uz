<p>Здравствуйте,<br>

Сообщаем, что Ваше объявление было удалено: </p>
<p>
</p>
<p>
<?=$offer_title?>
</p>
<p>
<?php if(!empty($message_content)):?> 
ПРИЧИНА: 
<?=$message_content?></p>

<?php endif?>

</p>
<p>
Пожалуйста, ознакомьтесь ещё раз с условиями и правилами размещения объявлений  <br/>
на сайте : <?=Lib::config('app.url')?>/terms/
</p>
<p>
По всем вопросам работы сайта, пожалуйста, обращайтесь к Админстрации сайта  <br/>
через контактную форму <?=Lib::config('app.url')?>/contacts/
</p>
<p>

ВНИМАНИЕ: Это электронное письмо отправлено автоматически. Отвечать на него не следует.
</p>
<p>
--
Система оповещений,
<?=Lib::config('app.title')?> - <?=Lib::config('app.subtitle')?>. <br/>
<?=Lib::config('app.url')?></p>
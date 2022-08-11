Здравствуйте,

Сообщаем, что Вы получили сообщение на Ваше объявление: 
 <?=$offer_title?> 


Текст сообщения:
 <?=$message_content?> 


Автор: <?=$author_name?> 
E-mail: <?=$author_email?> 
<?php if(!empty($author_phone)) echo 'Телефон: ' . $author_phone?> 
<?php if(!empty($message_url)):?> 


- Просмотреть сообщение:
  <?php echo Lib::config('app.url') . $message_url;?> 

- Ответить на сообщение:
  <?php echo Lib::config('app.url') . $message_url_reply;?> 
<?php endif;?>


ВНИМАНИЕ: Это электронное письмо отправлено автоматически. Отвечать на него не следует.

--
Система оповещений,
<?=Lib::config('app.title')?> - <?=Lib::config('app.subtitle')?>.
<?=Lib::config('app.url')?>
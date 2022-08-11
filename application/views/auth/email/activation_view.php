Здравствуйте, <?=@$name?><br/><br/>
Кто-то (возможно Вы) указал адрес <?=$email?> для регистрации на сайте <?=Lib::config('app.title')?>. <br/>
Если Вы этого не делали, то просто УДАЛИТЕ это письмо.<br/><br/><br/>
Для активации регистрации на сайте <?=Lib::config('app.title')?> проследуйте по ссылке:<br/>
<?php 
	$activation_url = Lib::config('app.url') . "/activate/?uid={$user_id}";
	$full_activation_url = $activation_url . "&activation_key={$activation_key}";
	echo $full_activation_url
?>
<br><br>
Это электронное письмо отправлено автоматически. Отвечать на него не следует.<br/>


--<br/>
Система оповещений,<br/>
<?=Lib::config('app.title')?> - <?=Lib::config('app.subtitle')?>.
<?=Lib::config('app.url')?>
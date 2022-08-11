<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW" />
	<title><?=Lib::config('app.title')?> — <?=@$title?></title>
<?php	if(!empty($css_files))
			foreach($css_files as &$css):?>
	<link rel="stylesheet" href="<?=$css?>" type="text/css" />
<?php		endforeach;?>
<?php	if(!empty($js_files))
			foreach($js_files as &$js):?>
	<script type="text/javascript" src="<?=$js?>"></script>
<?php		endforeach;?>
</head>
<body>
<div class="body">
	<div class="head">
		<h1><a href="/"><b><?=Lib::config('app.title')?></b> <span><?=Lib::config('app.subtitle')?></span><i></i></a></h1> 
	</div>
	<div class="main payment_state">
		<h2><?=$title?></h2>
		
		<table class="<?=@$payment->status?>">
<?php	if($mode == 'confirm'):?>		
			<tr class="status"><th>Состояние платежа:</th>	<td><b><?=Lib::config('payment.status', @$payment->status)?></b></td></tr>
<?php	endif;?>			
			<tr><th>Номер платежа:</th>		<td><?=@$payment->id?></td></tr>
			<tr><th>Способ оплаты:</th>		<td><?=Lib::config('payment.method', @$payment->method, 'title')?></td></tr>
			<tr><th>Стоимость услуг:</th>	<td><?=format::money(@$payment->final_price, @$payment->final_currency)?></td></tr>
			<tr><th>Детали платежа:</th>	<td><?=@$payment->details?></tr>
		</table>
		
<?php
	switch($mode):
		case 'proceed':?>
		
		<p class="g">Сейчас вы будете автоматически перенаправлены на страницу платежной системы.</p>

		<?=@$paymentSystemForm?>
		
		<form action="<?=@$payment->url_cancel?>" name="cancel" method="post">
						
			<input type="submit" class="cancel" value="Отменить оплату" />
		</form>
		
		<script type="text/javascript">
			timeout = window.setTimeout(function(){document.payment_form.submit()}, 8000);
			$('form').submit(window.clearTimeout(timeout); return true);
		</script>	
<?php	break;
		case 'confirm':?>
		<p class="g">Вы будете автоматически перенаправлены на страницу «Мои Объявления» через 5 секунд.</p>
		<p><a href="/my/offers/">Перейти на страницу «Мои объявления»</a>
		<script type="text/javascript">
			window.setTimeout(function(){location.href="/my/offers/"}, 5000);
		</script>	
<?php	break;	
	endswitch;?>	
	</div>
</div>
</body></html>
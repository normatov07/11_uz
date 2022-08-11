<p>Здравствуйте, <?=$request->user->own_name?>.</p>
<p>
К вашему объявлению №<?=$request->offer_id?> - «<?=text::untypography($request->offer->fulltitle)?>» была применена SMS-услуга.</p>
<p>- Ваше объявление было <?php switch($request->service):
	case 'position': 
		echo 'поднято'; 
	break;
	case 'premium': 
		echo 'премировано'; 
	break; 
	case 'mark': 
		echo 'выделено'; 
	break; 
	endswitch;?> в общем списке объявлений.</p>
<p>- В течение <?=Lib::config('sms.aggregators', $request->aggregator, 'service_expiration')?> вы будете получать на свой мобильный телефон SMS о поступлении новых сообщений и статистику просмотров вашего объявления.
</p><p>
SMS-услуга была активирована с номера: <?=format::phone($request->phone)?>.</p><p>
Если вы не активировали данную услугу к своему объявлению пройдите по следующей ссылке (вы должны быть авторизированы): </p><p>
<?=Lib::config('app.url').$request->url_complete?> </p>
<p>
Если вы не хотите получать SMS-сообщения на свой телефон зайдите в «Личный Кабинет» - откройте страницу «Настройки»,<br/>
в блоке «Дополнительные настройки» уберите галочку «Получать SMS-сообщения»</p>
</p>- Ссылка на страницу Настройки: <br/>
  <?php echo Lib::config('app.url')?>/my/settings/<br/>
</p><p>
ВНИМАНИЕ: Это электронное письмо отправлено автоматически. Отвечать на него не следует.
</p><p>
--<br/>
Система оповещений,<br/>
<?=Lib::config('app.title')?> - <?=Lib::config('app.subtitle')?>.
<?=Lib::config('app.url')?></p>
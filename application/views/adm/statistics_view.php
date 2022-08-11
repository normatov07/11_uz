<div class="main main2 statistics">

<table width="100%"> 
	<tr>
		<td>
	<h2>Объявления:</h2>
	<ul>
		<li>Сегодня добавлено: <b><?=format::declension_numerals(@$offers_today, '</b>объявление', '</b>объявления','</b>объявлений')?> (+ <?=@$offers_today_disabled?> неактивированных)</li>
		<li>Вчера добавлено: <?=format::declension_numerals(@$offers_yesterday, 'объявление', 'объявления','объявлений')?> (+ <?=@$offers_yesterday_disabled?> неактивированных)</li>
		<li>За <?=@date::$months[date('n')]?> добавлено: <b><?=format::declension_numerals(@$offers_this_month, '</b>объявление', '</b>объявления','</b>объявлений')?></li>
		<li>За <?=@date::$months[date('n', mktime (0,0,0,date('m')-1,1))]?> добавлено: <?=format::declension_numerals(@$offers_last_month, 'объявление', 'объявления','объявлений')?></li>
		<li>Всего активных: <b><?=format::declension_numerals(@$enabled_offers_total, '</b> объявление', '</b> объявления','</b> объявлений')?></li>
		<li>Всего: <?=format::declension_numerals(@$offers_total, ' объявление', ' объявления',' объявлений')?></li>
	</ul>
		</td>
		<td>	
	<h2>Пользователи:</h2>
	<ul>
		<li>Сегодня зарегистрировано: <b><?=format::declension_numerals(@$users_today, '</b> активированный пользователь', '</b> активированных пользователя','</b>активированных пользователей')?> (+ <?=@$users_today_disabled?> неактивированных)</li>
		<li>Вчера зарегистрировано: <?=format::declension_numerals(@$users_yesterday, 'активированный пользователь', 'активированных пользователя','активированных пользователей')?> (+ <?=@$users_yesterday_disabled?> неактивированных)</li>
		<li>За <?=@date::$months[date('n')]?> зарегистрировано: <b><?=format::declension_numerals(@$users_this_month, '</b>пользователь', '</b>пользователя','</b>пользователей')?></li>
		<li>За <?=@date::$months[date('n', mktime (0,0,0,date('m')-1,1))]?> зарегистрировано: <?=format::declension_numerals(@$users_last_month, 'пользователь', 'пользователя','пользователей')?></li>
		<li>Всего: <b><?=format::declension_numerals(@$users_total, '</b>пользователь', '</b>пользователя','</b>пользователей')?></li>
	</ul>
		</td>
	</tr>

	<tr>
		<td>
	<h2>По регионам:</h2>
	<ul>
<?php	foreach(@$offers_by_regions as $key => $val):?>	
		<li><?=$regions[$key]?>: <?=@$val->counted?></li>
<?php	endforeach?>		
	</ul>	
		</td>
		<td>
	<h2>Популярные разделы:</h2>
	<ul>
<?php	foreach(@$p_categories as $key => $val):?>	
		<li><?=@$categories[$key]?>: <?=@$val?></li>
<?php	endforeach?>
	</ul>	
		</td>
</table>	
</div>
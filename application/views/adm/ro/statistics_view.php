<div class="main main2">

	<ul class="pagemenu">
		<li><a href="/adm/ro/statistics/"<?php if(empty($pageid)) echo ' class="this"'?>>Текущая статистика</a></li>
		<li><a href="/adm/ro/statistics/monthly/"<?php if($pageid == 'monthly') echo ' class="this"'?>>Статистика по месяцам</a></li>
		<li><a href="/adm/ro/statistics/finanse/"<?php if($pageid == 'finanse') echo ' class="this"'?>>Финансовый отчёт</a></li>
		<li><a href="/adm/ro/statistics/agents/"<?php if($pageid == 'agents') echo ' class="this"'?>>Отчёт по агентам</a></li>
	</ul>
<?php if(!empty($pageid)):?>
	<form method="get" class="listfilter">
	<table>
		<tr>
			<td><b>Фильтр:</b></td>
<?php		if($pageid != 'finanse'):?>
			<td>
			<?=form::dropdown('month', $months, @$month);?>
			</td>				
<?php		endif;?>
			<td>
			<?php
				$years = array();
				for($i=date::getYear();$i>=Lib::config('app.start_year');$i--) $years[$i] = $i;
				echo form::dropdown('year', $years, @$year);?>
			</td>	
			<td>
				<input type="submit" value=" &raquo; "/>
			</td>
		</tr>
	</table>
	</form>	
<?php endif?>	
<?php	switch($pageid):
			case 'agents':?>
	<table class="ro_stat">
		<thead>
			<tr>
				<td>Агент ФИО</td>
				<td>Количество объявлений</td>
				<td>Задействованных разделов</td>
				<td>Всего клиентов</td>
				<td>Доход <?=Lib::config('app.title')?> по предоплате</td>
			</tr>
		</thead>
		<tbody>
<?php		foreach($data as $mid => $item):?>
			<tr>
				<th><?=$item['title']?></th>
				<td><?=@count($item['ro'])?></td>
				<td><?=@count($item['category'])?></td>
				<td><?=@count($item['client'])?></td>
				<td><?=(int) @$item['income']?></td>
			</tr>
<?php		endforeach;?>
		</tbody>
		
	</table>		
<?php		break;
			case 'finanse':?>
	<table class="ro_stat">
		<thead>
			<tr>
				<td>Агент</td>
				<td>Количество объявлений</td>
				<td>Платных объявлений</td>
				<td>Задействованных разделов</td>
				<td>Всего клиентов</td>
				<td>Новых клиентов</td>
				<td>Доход <?=Lib::config('app.title')?> по предоплате</td>
			</tr>
		</thead>
		<tbody>
<?php		foreach($data as $mid => $item):?>
			<tr>
				<th><?=$months[$mid]?></th>
				<td><?=@count($item['ro'])?></td>
				<td><?=@count($item['paid_ro'])?></td>
				<td><?=@count($item['category'])?></td>
				<td><?=@count($item['client'])?></td>
				<td><?=@count($item['new_client'])?></td>
				<td><?=(int) @$item['income']?></td>
			</tr>
<?php		endforeach;?>
		</tbody>
		
	</table>
<?php		break;
			case 'monthly':?>
	<table class="ro_stat">
		<thead>
			<tr>
				<td>Разделы</td>
				<td>Новых</td>
				<td>Активных</td>
				<td>Отработанные</td>
				<td>На публикацию</td>
				<td>Бесплатные</td>
			</tr>
		</thead>
		<tbody>
<?php		$count = 0;
			foreach($categoryStat as $id => $item):?>
			<tr<?php if($count%2 != 0) echo ' class="even"'?>>
				<th><?php if(!empty($item['parent'])) echo $categoryparents[$item['parent']]->title;?>
				<b><?=$item['title']?></b>
				</th>
				<td><?=count(@$item['new']);?></td>
				<td><?=count(@$item['enabled']);?></td>
				<td><?=count(@$item['expired']);?></td>
				<td><?=count(@$item['onhold'])?></td>
				<td><?=count(@$item['free'])?></td>
			</tr>
<?php			$count++;
			endforeach;?>			
		</tbody>
		<tfoot>
			<tr>
				<th>Всего</th>
				<td><?=@count($total['new'])?></td>
				<td><?=@count($total['enabled'])?></td>
				<td><?=@count($total['expired'])?></td>
				<td><?=@count($total['onhold'])?></td>
				<td><?=@count($total['free'])?></td>
			</tr>
		</tfoot>		
	</table>
<?php	break;
		default:?>
	<table class="ro_stat">
		<thead>
			<tr>
				<td>Разделы</td>
				<td>Активных</td>
				<td>На публикацию</td>
				<td>Бесплатные</td>
				<td>Платные</td>
			</tr>
		</thead>
		<tbody>
<?php		$count = 0;
			foreach($categoryStat as $id => $item):?>
			<tr<?php if($count%2 != 0) echo ' class="even"'?>>
				<th><?php if(!empty($item['parent'])) echo $categoryparents[$item['parent']]->title;?>
				<b><?=$item['title']?></b>
				</th>
				<td><?=count(@$item['enabled']);?></td>
				<td><?=count(@$item['onhold'])?></td>
				<td><?=count(@$item['free'])?></td>
				<td><?=count(@$item['paid'])?></td>
			</tr>
<?php			$count++;
			endforeach;?>			
		</tbody>
		<tfoot>
			<tr>
				<th>Всего</th>
				<td><?=@count($total['enabled'])?></td>
				<td><?=@count($total['onhold'])?></td>
				<td><?=@count($total['free'])?></td>
				<td><?=@count($total['paid'])?></td>
			</tr>
		</tfoot>		
	</table>
	
	<h2>Общая статистика</h2>
	<table class="ro_stat">
		<thead>
			<tr>
				<td>Активные</td>
				<td>Черновики</td>
				<td>На публикацию</td>
				<td>Бесплатные</td>
				<td>Отработанные бесплатные</td>
				<td>Отработанные платные</td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?=@count($overall['enabled'])?></td>
				<td><?=@count($overall['disabled'])?></td>
				<td><?=@count($overall['onhold'])?></td>
				<td><?=@count($overall['free_enabled'])?></td>
				<td><?=@count($overall['free_expired'])?></td>
				<td><?=@count($overall['paid_expired'])?></td>
			</tr>
		</tbody>
	</table>
	
<?php	endswitch?>
</div>
<div class="main main2">

	<ul class="pagemenu">
		<li><a href="/adm/ro/edit/" class="g"><b>Создать рекламный блок</b></a></li>
		<li><a href="/adm/ro/">Менеджер</a></li>
		<li><a href="/adm/ro/client/">Клиенты</a></li>
		<li><a href="/adm/ro/agent/">Агенты</a></li>
		<li><a href="/adm/ro/statistics/">Статистика</a></li>
	</ul>

	<form method="get" class="listfilter">
	<table>
		<tr>
			<td><b>Фильтр:</b></td>
			<td><?php
				$statuses = array('current' => 'Текущие', 'active' => 'Активные + на публикацию', 'expired' => 'Просроченные', 'expiring' => 'Завершаются');
				echo form::dropdown('state', $statuses, @$state)?></td>			
			<td>
			<td>
				<select name="category_id">
					<option value="">Все разделы</option>
					<option value="HOME" style="font-weight:bold">На главной</option>
<?php			foreach(@$categoryList as $item):?>						
					<option value="<?=$item->id?>"><?=str_repeat('&nbsp;&nbsp;', $item->level - 1)?><?=$item->stitle;?></option>
<?php			endforeach;?>
				</select>
			</td>
			<td>
				<input type="text" name="q" class="<?php if(empty($q)) echo 'toggleVal ';?>q" value="<?php if(!empty($q)) echo $q; else echo 'Поиск'?>" />
<?php		$subjects = array(
				'title' => 'Заголовок',
				'client' => 'Клиент',
				'agent' => 'Агент',
			);
			echo form::dropdown('subject', $subjects, @$subject);	
?>			
			</td>
			<td>
				<input type="submit" value="Найти"/>
			</td>
			<?php if(!empty($filterset)):?><td class="reset"><a href="/adm/ro/">Сбросить</a></td><?php endif?>
		</tr>
	</table>
	</form>	

<?php echo @$form_messages?>

<div class="ros">
<?php if(!empty($objectList)):?>
	<table>

		<tbody>
<?php	foreach($objectList as $item):?>
			<tr>
				<th>
					<p class="client"><a href="/adm/ro/client/<?=$item->ro_client_id?>/"><?=$item->ro_client_title?></a></p>
					<p class="<?=$item->state?>">
<?php		$totalperiod = date::periodLeft($item->date_end, $item->date_start);?>
						с <b><?=date::getLocalizedDate($item->date_start, '%d %s')?></b> 
						по <b><?=date::getLocalizedDate($item->date_end, '%d %s')?></b><br />
						<span><?=$item->state_title?></span>
					</p>
					<p><b>всего <?=date::declension_period(date::periodToNumber(strtotime($item->date_end) - strtotime($item->date_start)))?></b></p>
<?php	if(($daysleft = date::periodLeft($item->date_end)) >= 0):?>
					<p><b>осталось <?=date::declension_period($daysleft)?></b></p>
		
<?php		if(($daysleft = date::periodLeft($item->date_end)) >= 0):?>
					<p><b>отработано <?=date::declension_period($totalperiod-$daysleft)?></b></p>
<?php		endif?>	
<?php	endif?>			
<?php	if($item->ro_agent_id):?>
					<p class="agent">агент <b><a href="/adm/ro/agent/<?=$item->ro_agent_id?>"><?=$item->ro_agent_title?></a></b></p>
<?php	endif;?>	
<?php	if($item->cost):?>
					<p><b><?=$item->cost . ' ' . Lib::config('payment.currency_list', Lib::config('payment.main_currency'))?></b></p>				
<?php	endif;?>
				</th>
				<td>
					<ul class="cat">
<?php	if($item->onhome):?>
						<li><b class="g">Главная</b></li>
<?php	endif;
		foreach($item->categories as $category):?>
						<li><?php if($category->parent_id != 0 ) echo $categoryList[$category->parent_id]->title . ' » ';
			echo '<b>'.$category->title.'</b>';?></li>
<?php	endforeach;		?>
					</ul>
					<h3><a href="<?=$item->url_edit?>"><?=$item->title?></a></h3>
					<?php if(!empty($picture[$item->id])) echo $picture[$item->id]->f('full','html')?>
					<div class="cont">
						<p><?=$item->description?></p>
						<p><b><?=$item->price?></b></p>
						<p><?=$item->phone?></p>
						<p><a href="mailto:<?=$item->email?>"><?=$item->email?></a></p>
<?php	if($item->redirect == 'url'):?>
						<p>редирект: <a href="<?=$item->website?>"><?=$item->website?></a>
<?php	endif?>						
					</div>
				</td>
				<td class="actions">
					<a href="<?=$item->url?>" class="view">просмотреть</a>
					<a href="<?=$item->url_disable?>" class="disable"<?php if($item->status == 'disabled') echo ' style="display:none"'?>>отключить</a>
					<a href="<?=$item->url_enable?>" class="enable"<?php if($item->status == 'enabled') echo ' style="display:none"'?>>включить</a>
					<a href="<?=$item->url_delete?>" class="del">удалить</a>
					<p>кликов: <?=(int) $item->clicks?></p>
				</td>
			</tr>
<?php	endforeach;?>
		</tbody>
	</table>
<?=$pagination?>
<?php else:?>
	<p>Рекламных объявлений с заданными параметрами не найдено.</p>
<?php endif;?>	
</div>
</div>
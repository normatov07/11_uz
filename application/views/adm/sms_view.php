<div class="main main2">
	<form action="/adm/sms/" id="main_form" method="get" class="listfilter">
	<table>
		<tr>
			<td><b>Фильтр:</b></td>
			<td><?=form::digiDate(array('name'=>'date', 'monthselect' => true), @$date)?></td>
			<td>
<?php		$services = array(''=>'Услуга');
			foreach(Lib::config('sms.service') as $id=>$item) $services[$id] = $item['title'];?>
				<?=form::dropdown(array('name'=>'service_id'), $services, @$service_id)?>
			</td>
			<td><?php $statuses = array(''=>'Статус') + Lib::config('sms.status');
				echo form::dropdown('status', $statuses, @$status)?></td>			
			<td>
				<input type="text" name="q" class="<?php if(empty($q)) echo 'toggleVal ';?>q" value="<?php if(!empty($q)) echo $q; else echo 'Поиск'?>" />
<?php		$subjects = array(
				'transaction' => '№ Транзакции',
				'offer_id' => 'offer ID',
				'user_id' => 'user ID',
			);
			echo form::dropdown('subject', $subjects, @$subject);	
?>			
			</td>
			<td>
				<input type="submit" value="Найти"/>
			</td>
			<?php if(!empty($filterset)):?><td class="reset"><a href="/adm/sms/">Сброс</a></td><?php endif?>
		</tr>
	</table>
	</form>
<div class="payments">
<?php if(!empty($requestList)):?>
	<table>
		<thead>
			<tr>
				<td>№</td>
				<td>Дата/Время</td>
				<td>Транзакция</td>
				<td>Услуга</td>
				<td>Телефон</td>
				<td>Оператор</td>
				<td>Агрегатор</td>
				<td>Объявл.</td>
				<td>Состояние</td>
			</tr>
		</thead>
		<tbody>

<?php	$i = 0;
		foreach($requestList as $item):
			$i++;
			?>
			<tr class="<?=$item->status?><?php if(!empty($item->error)) echo ' error'?>" id="r<?=$item->id?>">
				<td><?=$i?></td>
				<td><?=date('d.m.Y H:i', strtotime($item->added))?></td>
				<td><div class="num"><a href="#"><?=$item->transaction_id?></a>
				<div class="popup"><span class="arrow"></span>			
				
					<table>
						<tr>
							<th>Отправлено:</th>
							<td><?=date::getSimple($item->requested,'d.m.Y H:i')?>
								<?php if(!empty($item->replied)):?> 
									<span class="gy">&nbsp; | &nbsp;Отвечено:</span> <?=date::getSimple($item->replied,'d.m.Y H:i')?>
								<?php endif;?>
								<?php if(!empty($item->completed)):?>
									<span class="gy">&nbsp; | &nbsp;<b>Отработано</b>:</span> <?=date::getSimple($item->completed,'d.m.Y H:i')?>
								<?php endif?>
							</td>
						</tr>
						<tr>
							<th>SMS:</th>
							<td><?=$item->short_number?> <?=$item->keyword?> <?=$item->message?></td>
						</tr>
						<tr>
							<th>Тел. номер (опер):</th>
							<td><?=format::phone($item->phone)?> (<?=$item->provider?>)</td>
						</tr>						
						<tr>
							<th>Тип услуги:</th>
							<td><?php								
									echo Lib::config('sms.service',@$item->service, 'title');?><?php 
									if(!empty($item->offer_id) && !empty($offers[$item->offer_id])):
										?>: <a href="<?=$offers[$item->offer_id]->url?>" title="<?=$offers[$item->offer_id]->fulltitle?>">№<?=$item->offer_id?></a>
<?php									if(!empty($users[$offers[$item->offer_id]->user_id])):?>
									(<a href="<?=$users[$offers[$item->offer_id]->user_id]->url_edit?>"><?=$users[$offers[$item->offer_id]->user_id]->email?></a>)
<?php									endif;										
									endif;
								 ?>
							</td>
						</tr>	
<?php if(!empty($item->replied)):?>						
						<tr>
							<th>Ответ:</th>
							<td><?=$item->reply?></td>
						</tr>
<?php endif?>
<?php if($item->error):?>
						<tr>
							<th>Ошибка запроса:</th>
							<td><?=$item->error?></td>
						</tr>
<?php endif?>
<?php if($item->error):?>
						<tr>
							<th>Ошибка отправки ответа:</th>
							<td><?=$item->reply_error?></td>
						</tr>
<?php endif?>				
					</table>
				</div></div>
			
				</td>
				<td>
<?php		echo Lib::config('sms.service', @$item->service, 'details');?>
				</td>
				<td><?=format::phone(@$item->phone)?></td>
				<td><?=@$item->provider?></td>
				<td><?=@$item->aggregator?></td>
				<td><?php if(!empty($item->offer_id))
				{
					if(!empty($offers[$item->offer_id]))
					{
						echo '<a href="'.$offers[$item->offer_id]->url.'">'.$item->offer_id.'</a>';
					}
					else
					{
						echo $item->offer_id;
					}
				}?></td>
				<td class="s"><?=Lib::config('sms.status', @$item->status);?></td>
			</tr>
<?php	
		endforeach;?>			
		</tbody>
	</table>

<?=$pagination?>	
	
<?php else:?>
	<p>SMS запросов с заданными параметрами не найдено.</p>
<?php endif;?>	
</div>
</div>
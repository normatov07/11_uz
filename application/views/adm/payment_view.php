<div class="main main2">
	<form action="/adm/payment/" id="main_form" method="get" class="listfilter">
	<table>
		<tr>
			<td><b>Фильтр:</b></td>
			<td><?=form::digiDate(array('name'=>'date', 'monthselect' => true), @$date)?></td>
			<td>
<?php		$services = array(''=>'Услуга');
			foreach(Lib::config('payment.service') as $id=>$item) $services[$id] = $item['title'];?>
				<?=form::dropdown(array('name'=>'service_id'), $services, @$service_id)?>
			</td>
			<td><?php $statuses = array(''=>'Статус') + Lib::config('payment.status');
				echo form::dropdown('status', $statuses, @$status)?></td>			
			<td>
				<input type="text" name="q" class="<?php if(empty($q)) echo 'toggleVal ';?>q" value="<?php if(!empty($q)) echo $q; else echo 'Поиск'?>" />
<?php		$subjects = array(
				'id' => '№ платежа',
				'ps_transaction_id' => 'Номер ПС',
				'user_id' => 'user ID',
			);
			echo form::dropdown('subject', $subjects, @$subject);	
?>			
			</td>
			<td>
				<input type="submit" value="Найти"/>
			</td>
			<?php if(!empty($filterset)):?><td class="reset"><a href="/adm/payment/">Сбросить</a></td><?php endif?>
		</tr>
	</table>
	</form>
<div class="payments">
<?php if(!empty($paymentList)):?>
	<table>
		<thead>
			<tr>
				<td>Дата/Время</td>
				<td>Номер</td>
				<td>Услуга</td>
				<td>Затраты бонусов</td>
				<td>Прямой платёж</td>
				<td>Способ оплаты</td>
				<td>Состояние</td>
			</tr>
		</thead>
		<tbody>
<?php	$total_bonuses_bought = 0; $total_spent = array();

	echo count($paymentList);
		foreach($paymentList as $item):?>
			<tr class="<?=$item->status?>" id="p<?=$item->id?>">
				<td><?=date('d.m.Y H:i', strtotime($item->added))?></td>
				<td><div class="num"><a href="#"><?=$item->id?></a>
				
				<div class="popup"><span class="arrow"></span>
					<table>
						<tr>
							<th>Дата/Время:</th>
							<td><?=date('d.m.Y H:i', strtotime($item->added))?></td>
						</tr>
						<tr>
							<th>Номер:</th>
							<td><?=$item->id?></td>
						</tr>
						<tr>
							<th>Способ оплаты:</th>
							<td><?=Lib::config('payment.method', $item->method, 'title')?></td>
						</tr>
<?php		if(!empty($item->ps_transaction_id)):?>
						<tr>
							<th>Номер в ПС:</th>
							<td><?=$item->ps_transaction_id?></td>
						</tr>
<?php		endif?>		
						<tr>
							<th>Сумма платежа:</th>
							<td><?=format::money($item->final_price, $item->final_currency)?></td>
						</tr>				
						<tr>
							<th>Заказчик:</th>
							<td><a href="<?=$users[$item->user_id]->url_edit?>"><?=$users[$item->user_id]->email?></a> (<?=$users[$item->user_id]->contact_name?>)</td>
						</tr>
						<tr>
							<th>Тип услуги:</th>
							<td><?=Lib::config('payment.service',$item->service, 'title');?></td>
						</tr>
<?php	/*				if(@$item->service != 'bonus'):?>						
						<tr>
							<th>Объявления:</th>
							<td><?php if($item->offers->count()): 
									foreach($item->offers as $offer):
										echo '<a href="'.$offer->url.'">№'.$offer->id.'</a> ';
									endforeach; 
								endif;?></td>
						</tr>
<?php					endif; */?>	
						<tr>
							<th>Детали:</th>
							<td><?=$item->details?></td>
						</tr>				
					</table>
				</div></div>
				
				</td>
				<td>
<?php		echo Lib::config('payment.service',$item->service, 'title');
			switch($item->service):
				case 'bonus':
					echo ' (' . $item->units_bought .')';
				break;
				default:
					echo ' (' . $item->offers->count() . ')';
				break;
			endswitch;?>
				</td>
<?php		if($item->currency == 'bonus'):?>
				<td class="g"><?=format::declension_numerals(format::number($item->price), Lib::config('payment.currency',$item->currency))?></td>
<?php		else:?>
				<td class="e">—</td>
<?php		endif;?>			
<?php		if(@$item->final_price):?>
				<td><?=format::money($item->final_price, $item->final_currency)?></td>
<?php		elseif($item->price && $item->currency != 'bonus'):?>				
				<td><?=format::money($item->price, $item->currency)?></td>
<?php		else:?>				
				<td class="e">—</td>
<?php		endif;?>				
				<td class="me"><?= Lib::config('payment.method', $item->method, 'title');?></td>
				<td class="s"><?=Lib::config('payment.status', $item->status);?></td>
			</tr>
<?php		if($item->status == 'complete'):
				if($item->service == 'bonus'): 
					$total_bonuses_bought += $item->units_bought; 
				endif;
				if(@$item->final_price):
					@$total_spent[$item->final_currency] += $item->final_price;
				elseif(@$item->price):
					@$total_spent[$item->currency] += $item->price;
				endif;
			endif;
		endforeach;?>			
		</tbody>
		<tfoot>
			<tr>
				<td colspan="3">
				<?php if($total_bonuses_bought) echo 'Всего приобетено: ' . format::declension_numerals(format::number($total_bonuses_bought), Lib::config('payment.unit','bonus'))?></td>
				<td><?=format::declension_numerals(format::number(@$total_spent['bonus']), Lib::config('payment.currency','bonus'));?></td>
				<td colspan="3"><?php 
					if(!empty($total_spent)):
						$i = 0;
						foreach($total_spent as $currency => $price):
							if($currency == 'bonus') continue;
							if($i != 0) echo ' + ';
							echo format::money($price, $currency);
							$i++;
						endforeach;
					endif;?></td>
			</tr>
		</tfoot>				
	</table>
<?=$pagination?>	
	
<?php else:?>
	<p>Платежей с заданными параметрами не найдено.</p>
<?php endif;?>	
</div>
</div>
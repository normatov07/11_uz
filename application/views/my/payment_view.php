<?php if(empty($year)) $year = date::getYear();?>
<div class="main payments">
	<h2 class="gy">История бонусов, платежей и зачислений</h2>
	
<?php if(!empty($paymentList)):?>
	<table>
		<thead>
			<tr>
				<td></td>
				<td>Дата/Время</td>
				<td>Получено</td>
				<td>Затраты</td>
				<td>Детали</td>
				<td>Состояние</td>
			</tr>
		</thead>
		<tbody>
<?php	$total_bonuses_bought = 0; $total_spent = array();
		foreach($paymentList as $item):?>
			<tr class="<?=$item->status?>">
<?php		if($item->service == 'bonus'):?>			
				<td class="p">+</td>
<?php		else:?>			
				<td class="m">–</td>
<?php		endif;?>
				<td class="dt"><?=date('d.m.Y H:i', strtotime($item->added))?></td>
<?php		if($item->service == 'bonus'):?>
				<td class="g"><?=format::declension_numerals($item->units_bought, Lib::config('payment.unit','bonus'))?></td>
<?php		else:?>				
				<td class="e">—</td>
<?php		endif;?>
<?php		if(@$item->final_price):?>
				<td class="pr"><?=format::money($item->final_price, $item->final_currency)?></td>
<?php		else:?>				
				<td class="e">—</td>
<?php		endif;?>				
				<td class="d"><?=$item->details?></td>
				<td class="s"><?php
				$status_title = Lib::config('payment.status', $item->status);
				if($item->status == 'ordered'):
					echo '<a href="'.$item->url_proceed.'" title="Нажмите что заново инициировать платёж">'.$status_title.'</a>';
				else:
					echo $status_title;
				endif;
				
				?></td>
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
				<td colspan="3"><?php 
					if($total_spent):
						echo 'Всего потрачено: '; 
						$i = 0;
						foreach($total_spent as $currency => $price):
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


	<ul class="months">
		<li><b><?=$year?>:</b></li>
<?php	foreach(date::$months as $id => $val):?>		
		<li><a href="?y=<?=$year?>&m=<?=$id?>"><?=$month == $id?'<b>'.$val.'</b>':$val?></a></li>
<?php	endforeach?>		
	</ul>
<?php	if(count($years) > 1):?>
	<ul class="years">
<?php		foreach($years as $val):?>
		<li><a href="?y=<?=$val?>"><?=$val?></a></li>
<?php		endforeach;?>		
	</ul>
<?php	endif?>	
</div>
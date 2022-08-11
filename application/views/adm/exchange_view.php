<div class="main main2 exchange">

		<table width="100%" class="rates">
			<tr>
<?php	$i = 0;
		foreach(@$objList as $item):
			if($i!=0 and $i%5 == 0) echo '</tr><tr>';?>
			<td<?php 
				if(@$todayExchange->added == $item->added): 
					echo ' class="today"'; 
					$prev = $i + 1; 
				elseif(empty($prev)):
					echo ' class="future"';
				elseif($i == @$prev): 
					echo ' class="yesterday"'; 
				endif;?>>
				<h2><a href="?d=<?=$item->added?>"><?=date::convertIncomplete($item->added,'day.month.year')?></a></h2>
				<p>USD: <b><?=format::money($item->usd, Lib::config('payment.main_currency'))?></b>
				<p>EUR: <b><?=format::money($item->eur, Lib::config('payment.main_currency'))?></b>
				<p>RUB: <b><?=format::money($item->rub, Lib::config('payment.main_currency'))?></b>
			</td>
<?php		$i++;
		endforeach ?>
			</tr>
		</table>

		<form name="main_form" id="main_form" action="/adm/exchange/" method="post">
			<?php echo @$form_messages?>
			<div class="bluetable bcorns"><i class="ct"><i></i><b></b></i>
				<table>
					<thead>
						<tr>
							<td class="date">Год-Месяц-День</td>
							<td>USD</td>
							<td>EUR</td>
							<td>RUB</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><?=form::digidate('date', @$obj ? $obj->added : (empty($_POST)?date::getForDb():''))?></td>
							<td><input type="text" name="usd" value="<?=@$obj->usd?format::number(@$obj->usd,true):''?>" class="money" /></td>
							<td><input type="text" name="eur" value="<?=@$obj->eur?format::number(@$obj->eur,true):''?>" class="money" /></td>
							<td><input type="text" name="rub" value="<?=@$obj->rub?format::number(@$obj->rub,true):''?>" class="money" /></td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="4">
								<input type="submit" value="Сохранить" class="submit" />
								<input type="submit" name="delete" value="Удалить" />
							</td>
						</tr>
					</tfoot>
				</table>
				<i class="cb"><i></i><b></b></i>
			</div>
			
		</form>	

</div>
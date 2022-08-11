<div class="main main2">

		<form action="/adm/tariff/" id="main_form" name="form_tariffs" method="post">
		
			<?php echo @$form_messages?>
			<div class="bluetable bcorns"><i class="ct"><i></i><b></b></i>
				<div class="fr">
					
<?php		$i = 0;
			$services = Lib::config('payment.service');

			$methods = Lib::config('payment.method');
			$methodCount = count($methods);
			
			foreach(Lib::config('payment.unit') as $id => $title):
				$units[$id] = $title[2];
			endforeach;
			
			foreach($services as $service => $service_data):?>						
					<h2><?=$service_data['title']?></h2>
					
					<table class="listtable">
						<col width="175" />
						<col width="130" />
						<col width="200" />
						<col width="70" />
						
						<thead>
							<tr>
								<td>Способ оплаты</td>
								<td>
<?php if(!empty($service_data['unit'])):?>									
								Количество
<?php endif?>					</td>
								<td>Цена</td>
								<td>Состояние</td>
								<td>Комментарий</td>
							</tr>
						</thead>
						<tbody>
<?php			$k = 0; 	
				foreach($methods as $method => $method_data):
					if($method == 'free' or $method == $service) continue;?>
					
							<tr class="odd">
								<td class="m"><?=$method_data['title']?></td>						
								<input type="hidden" name="id[<?=$service?>][<?=$method?>]" value="<?=@$tariff[$service][$method]->id?>" />
								<td class="a">
<?php if(!empty($service_data['unit'])):?>								
<?php /*									<input type="text" name="amount[<?=$service?>][<?=$method?>]" size="3" maxlength="8" value="<?=form::value(@$tariff[$service][$method]->amount?@$tariff[$service][$method]->amount:1)?>" /> */?>
									<?//=format::declension_numerals(@$service_data['amount'], Lib::config('payment.unit', @$service_data['unit']))
									echo format::declension_numerals(@$tariff[$service][$method]->amount, Lib::config('payment.unit', @$service_data['unit']));?>
<?php endif?>					</td>
								<td class="p">
									<input type="text" name="price[<?=$service?>][<?=$method?>]" size="10" maxlength="8" value="<?=form::value(@$tariff[$service][$method]->price?(format::number(@$tariff[$service][$method]->price,true)):0)?>" />
									<?=Lib::config('payment.currency', @$method_data['currency'], 2)?>
								</td><td class="s">
									<?=form::dropdown(array('name'=>'status['.$service.']['.$method.']', 'id'=>'status'.$service. $method), array('enabled' => 'Активен', 'disabled' => 'Отключен'), @$tariff[$service][$method]->status)?>
								</td>
								<td class="c"><input type="text" name="comment[<?=$service?>][<?=$method?>]" size="45" maxlength="255" value="<?=form::value(@$tariff[$service][$method]->comment)?>" /></td>
							</tr>
<?php				$k++;
				endforeach;?>
						</tbody>
					</table>
<?php			$i++;
			endforeach; ?>							
						
				</div>
			</div>
			<div class="blueblock bcorns">	
				<div class="buttons">
					<input type="submit" name="save" value="Сохранить" class="subm" />
					<span id="ajaxstatus">Проверка данных...</span>
				</div>
				<i class="cb"><i></i><b></b></i>
			</div>
			<?php echo @$form_messages?>
		</form>
<script type="text/javascript">
$(function(){
	$('option:first',':select').attr('style','color: #090');
	$('option:last',':select').attr('style','color: #666');
});
</script>

</div>
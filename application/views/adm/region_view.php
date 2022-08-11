<?php $url = '/adm/region/';?>
<div class="main main2 wlist">
	<div class="lcol">
		<form action="<?=$url?>" name="regionForm" id="regionForm" method="post">
			<input type="hidden" name="id" value="<?=form::value(@$obj->id)?>" />
			<?php echo @$form_messages?>
			<div class="bluetable bcorns"><i class="ct"><i></i><b></b></i>
				<div class="fr">
					<h5>Название:</h5>
					<input type="text" name="title" maxlength="64" class="in w" value="<?=form::value(@$obj->title)?>" id="title" />
				</div>
				<div class="fr">
					<h5>Код для адреса:</h5>
					<input type="text" name="codename" maxlength="64" class="in w" value="<?=form::value(@$obj->codename)?>" id="codename" />
					<div class="note">Оставьте пустым для автоматической генерации</div>
				</div>
				<div class="fr">
					<h5>Приоритет:</h5>
					<input type="text" name="priority" maxlength="3" class="in" value="<?=form::value(isset($obj->priority)?$obj->priority:@$objList->count())?>" id="priority" />
					<span class="note">самый высокий 0</span>
				</div>
				
				<div class="fr">
					<h5 id="district" title="Район/Город">Районы/Города: <?=form::dropdown(array('name'=>'district_type','id'=>'district_type'), Lib::config('app.district_type'), @$obj->district_type)?></h5>	
					<table class="listtable">
						<thead>
							<tr>
								<td class="t">Название:</td>
								<td></td>
							</tr>
						</thead>
						<tbody>
<?php	$i = 0;
		if(@$obj and @$obj->districts->count()):
			foreach($obj->districts as $item):?>						
							<tr class="item<?php if(($i+1)%2) echo ' odd'?>">
								<td>
									<input type="hidden" name="district[<?=$i?>][id]" value="<?=form::value($item->id)?>" />
									<input type="text" name="district[<?=$i?>][title]" class="w" maxlength="128" value="<?=form::value($item->title)?>" /></td>
								<td class="b">
									<div class="delete">x<i></i></div>
								</td>
							</tr>
<?php			$i++;
			endforeach;
		endif; ?>			
							<tr class="item<?php if(($i+1)%2) echo ' odd'?>">
								<td>
									<input type="hidden" name="district[<?=$i?>][id]" value="" />
									<input type="text" name="district[<?=$i?>][title]" class="w" maxlength="128" value="" /></td>
								<td class="b">
									<div class="delete<?php if($i == 0) echo ' nobut'?>">x<i></i></div>
								</td>
							</tr>				
						</tbody>
						<tfoot>
							<tr>
								<td></td>
								<td><div class="add" onclick="listtableAddItem(null, this)">+<i></i></div></td>
							</tr>
						</tfoot>
					</table>
				
					<h5 id="subway" title="Станции метро">Станции метро: <input type="checkbox" name="has_subway" id="has_subway" value="1"  <?=form::checked(@$obj->has_subway, 1, 0)?> /></h5>	
					<table class="listtable" id="subways_list">
						<thead>
							<tr>
								<td class="t">Название:</td>
								<td></td>
							</tr>
						</thead>
						<tbody>
<?php	$i = 0;
		if(@$obj and @$obj->subways->count()):
			foreach($obj->subways as $item):?>						
							<tr class="item<?php if(($i+1)%2) echo ' odd'?>">
								<td>
									<input type="hidden" name="subway[<?=$i?>][id]" value="<?=form::value($item->id)?>" />
									<input type="text" name="subway[<?=$i?>][title]" class="w" maxlength="128" value="<?=form::value($item->title)?>" /></td>
								<td class="b">
									<div class="delete">x<i></i></div>
								</td>
							</tr>
<?php			$i++;
			endforeach;
		endif; ?>			
							<tr class="item<?php if(($i+1)%2) echo ' odd'?>">
								<td>
									<input type="hidden" name="subway[<?=$i?>][id]" value="" />
									<input type="text" name="subway[<?=$i?>][title]" class="w" maxlength="128" value="" /></td>
								<td class="b">
									<div class="delete<?php if($i == 0) echo ' nobut'?>">x<i></i></div>
								</td>
							</tr>				
						</tbody>
						<tfoot>
							<tr>
								<td></td>
								<td><div class="add" onclick="listtableAddItem(null, this)">+<i></i></div></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
			<div class="blueblock bcorns">				
				<div class="buttons">
					<input type="submit" name="save" value="Сохранить" class="subm" />
					<input type="submit" name="delete" value="Удалить" class="but" onclick="return confirm('Вы уверены?')" />
					<input type="submit" name="new" value="Создать новый" class="but" />
				</div>
				<div id="ajaxstatus">Проверка данных...</div>
				<i class="cb"><i></i><b></b></i>
			</div>
		</form>
	</div>	
    <div class="rcol">
		<h2>Список</h2>
		<ul class="list">
<?php if(!empty($objList)):
		foreach($objList as $item):?>		
			<li<?php if(@$obj->id == $item->id) echo ' class="this"'?>><a name="<?=$item->id?>" href="<?=$url.$item->id?>/"><?=$item->title?></a></li>
<?php 	endforeach;
		endif;?>
		</ul>
	</div>
</div>
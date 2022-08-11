<div class="main main2 wlist lists">
	<div class="lcol">
		<form action="/adm/list/" name="listForm" id="listForm" method="post">
			<input type="hidden" name="id" value="<?=form::value(@$obj->id)?>" />
			<?php echo @$form_messages?>
			<div class="bluetable bcorns"><i class="ct"><i></i><b></b></i>
				<div class="fr">
					<h5>Название списка:</h5>
					<input type="text" name="title" maxlength="64" class="in w" value="<?=form::value(@$obj->title)?>" id="title" /> &nbsp;&nbsp;
					<label for="isfilter"><input type="checkbox" class="ch" name="isfilter" value="1" id="isfilter"<?=form::checked(@$obj->isfilter)?> /> фильтр</label> &nbsp;&nbsp;
					<label for="isquick"><input type="checkbox" class="ch" name="isquick" value="1" id="isquick"<?=form::checked(@$obj->isquick)?> /> быстрый список</label>
				</div>
				<div class="fr">
					<div class="fe">
						<h5>Тип:</h5>
						<select name="listtype">
							<option value="select"<?=form::selected(@$obj->listtype, 'select', true)?>>Select</option>
							<option value="radio"<?=form::selected(@$obj->listtype, 'radio')?>>Radio</option>
							<option value="checkbox"<?=form::selected(@$obj->listtype, 'checkbox')?>>Checkbox</option>
						</select> &nbsp;&nbsp;
<?php /*					<label for="ismultiple"><input type="checkbox" class="ch" name="ismultiple" value="1" id="ismultiple"<?=form::checked(@$obj->ismultiple)?> /> multiple</label> */?>
					</div>
					<div class="fe">
						<h5><label for="default_empty">Пустой по умолчанию:</label></h5>
						<input type="checkbox" class="ch" name="default_empty" value="1" id="default_empty"<?=form::checked(@$obj->default_empty, true, true);?> />
						<input type="text" name="default_empty_title" id="default_empty_title" maxlength="32"<?php if(!empty($obj->default_empty) and $obj->default_empty != 1):?> value="<?=form::value(@$obj->default_empty)?>"<?php elseif(!empty($obj) and empty($obj->default_empty)):?> disabled="disabled"<?php endif?> />
					</div>
					<div class="fe">
						<h5><label for="has_other">Другое значение:</label></h5>
						<input type="checkbox" class="ch" name="has_other" value="1" id="has_other"<?=form::checked(@$obj->has_other, true, false)?> />
						<input type="text" name="has_other_title" id="has_other_title" maxlength="32"<?php if(
							!empty($obj->has_other) and $obj->has_other != 1):?> value="<?=form::value($obj->has_other);?>"<?php else:?> disabled="disabled"<?php endif;?> />
					</div>
				</div>
				<div class="fr">
					<h5 id="item" title="Список значений">Список значений:</h5>	
					<table class="listtable">
						<thead>
							<tr>
								<td class="t">Название:</td>
								<td>Значение:</td>
<?php /*								<td>Умолч.:</td> */?>
								<td></td>
							</tr>
						</thead>
						<tbody>
<?php	$i = 0;
		if(!empty($obj->items)):
			foreach($obj->items as $item):?>						
							<tr class="item<?php if(($i+1)%2) echo ' odd'?>">
								<td class="t">
									<input type="hidden" name="item[<?=$i?>][id]" value="<?=form::value($item->id)?>" />
									<input type="text" name="item[<?=$i?>][title]" maxlength="128" value="<?=form::value($item->title)?>" /></td>
								<td class="v"><input type="text" name="item[<?=$i?>][valuedata]" maxlength="128" value="<?=form::value($item->valuedata)?>" /></td>
<?php /*								<td><input type="radio" name="default_item" value="<?=$i?>" class="ch"<?=form::checked(@$item->isdefault)?> /></td> */?>
								<td class="b">
									<div class="down">↓<i></i></div>
									<div class="up<?php if($i == 0) echo ' nobut'?>">↑<i></i></div>
									<div class="delete">x<i></i></div>
								</td>
							</tr>
<?php			$i++;
			endforeach;
		endif; ?>			
							<tr class="item<?php if(($i+1)%2) echo ' odd'?>">
								<td class="t">
									<input type="hidden" name="item[<?=$i?>][id]" value="" />
									<input type="text" name="item[<?=$i?>][title]" maxlength="128" value="" /></td>
								<td class="v"><input type="text" name="item[<?=$i?>][valuedata]" maxlength="128" value=""/></td>
<?php /*								<td><input type="radio" name="default_item" value="<?=$i?>" class="ch"<?=form::checked(!$i)?> /></td> */?>
								<td class="b">
									<div class="down nobut">↓<i></i></div>
									<div class="up<?php if($i == 0) echo ' nobut'?>">↑<i></i></div>
									<div class="delete<?php if($i == 0) echo ' nobut'?>">x<i></i></div>
								</td>
							</tr>				
						</tbody>
						<tfoot>
							<tr>
								<td colspan="2"></td>
								<td><div class="add" onclick="listtableAddItem()">+<i></i></div></td>
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
		<h2>Списки</h2>
		<ul class="list">
<?php if(!empty($objList)):
		foreach($objList as $item):?>		
			<li<?php if(@$obj->id == $item->id) echo ' class="this"'?>><a name="<?=$item->id?>" href="/adm/list/<?=$item->id?>/"><?=$item->title?></a></li>
<?php 	endforeach;
		endif;?>
		</ul>
	</div>
</div>
<div class="main main2 wlist">
    <div class="rcol">
		<h2>Каталог</h2>
		<ul class="tree">
<?php
	if(!empty($objList)):
		$counter = array(); $maxlevel = 0; $prevlevel = 1;
		foreach($objList as $item):
			
			if(empty($counter[$item->level])) 
				$counter[$item->level] = 1;
			else $counter[$item->level]++;
			
			$counter[$item->level+1] = 0;
			
			if($item->level > $prevlevel):?>
			<?= str_repeat("	", $prevlevel);?><ul>
<?php		elseif($item->level < $prevlevel):
				$i = 0;
				while($i < $prevlevel - $item->level):?>
				<?= str_repeat("	", $prevlevel);?></li>
				<?= str_repeat("	", $item->level);?></ul>
<?php				$i++;
				endwhile;
			elseif($maxlevel != 0):?></li>
<?php		endif;
			
			if($maxlevel < $item->level) $maxlevel = $item->level;
// генерируем нумерацию
			$i = 1; $num = '';
			while($i <= $maxlevel):
				if($counter[$i] == 0) break; 
				else $num .= $counter[$i].'.';
				$i++;
			endwhile;
			
			if(!empty($obj)/* and !empty($obj->parentObj) */ and $obj->parent_id == $item->id) $parent_num = $num;?>		
			<?= str_repeat("	", $item->level);?><li><a href="#" class="s"><?php if(($item->right_key - $item->left_key) > 1) echo '+'; else echo '–'?></a> <a href="#" class="n"><?=@$num?></a> <a<?php if(!empty($obj) and $obj->id == $item->id) echo ' class="this"'?> name="<?=$item->id?>" href="/adm/category/<?=$item->id?>/"><?=$item->stitle?></a><?php		
			$prevlevel = $item->level;
		endforeach;		
	endif;
?>	
		</ul>
	</div>
	
	<div class="lcol">
		<form action="/adm/category/" id="form_categories" name="form_categories" method="post">
			<input type="hidden" name="id" value="<?=form::value(@$obj->id)?>" />
			<?php echo @$form_messages?>
			<div class="bluetable bcorns"><i class="ct"><i></i><b></b></i>
				<div class="fr">
					<div class="fe">
						<h5>Короткое название:</h5>
						<input type="text" name="short_title" class="in w" value="<?=form::value(@$obj->short_title)?>" id="short_title" maxlength="64" />
					</div>
				
				</div>
				<div class="fr">
					<h5>Название раздела:</h5>
					<input type="text" name="title" class="in dw" value="<?=form::value(@$obj->title)?>" id="title" maxlength="64" />
				</div>
				<div class="fr">
					<div class="fe">
						<h5>Код раздела:</h5>
						<input type="text" name="codename" value="<?=form::value(@$obj->codename)?>" id="codename" maxlength="64" />
					</div>
					<div class="fe">
						<h5><b>Расположение в списке:</b></h5>
						<input type="text" name="priority" id="priority" style="width:30px" value="<?=form::value(@$obj->priority)?>" /> <span class="note">номер, за который необходимо расположить</span>
					</div>
					<div class="fe">
						<h5><b>Статус:</b></h5>
						<label for="status"><input type="checkbox" class="ch" name="status" value="1" id="status" title="Выключен|Активный"<?=form::checked(@$obj->status,'enabled',1)?> /> Активный</label>
					</div>
				</div>
				<div class="fr">
					<h5>Родительский раздел:</h5>
					<div id="parentSection" class="parent">
<?php if(!empty($obj) and !$obj->parent->isEmpty()):?>
						<input type="hidden" name="parent_id" value="<?=form::value(@$obj->parent->id)?>" />
						<a href="#" class="t"><?=@$parent_num?> <b><?=$obj->parent->title?></b></a> <a href="#" class="act" onclick="treeSetParentSection()">удалить</a>
<?php else:
		echo 'Нет';
	  endif; ?>						
					</div>
				</div>
				
				<div class="fr">
					<h5>Описание:</h5>
					<textarea name="description" class="dw" cols="40" rows="3"><?=form::value(form::stringToForm(@$obj->description))?></textarea>
				</div>
			
				<div class="fr">				
<?php	if(!@$obj->has_children):?>	
					<div class="fe">
						<h5><b>Список районов:</b></h5>
<?php		$arr = array('Не выводить','Выводить','Обязателен');?>
						<?=form::dropdown(array('name'=>'has_district', 'id'=>'has_district'), $arr, @$obj->has_district)?>
					</div>
					<div class="fe">
						<h5><b>Список метро:</b></h5>
						<?=form::dropdown(array('name'=>'has_subway', 'id'=>'has_subway'), $arr, @$obj->has_subway)?>
					</div>
<?php	endif?>	
					<div class="fe">
						<h5><b>Цена за РО:</b></h5>
						<input type="text" name="ro_price" value="<?=@$obj->ro_price?>" size="12" maxlength="10"/> <?=Lib::config('payment.currency', Lib::config('payment.main_currency'), 2)?>
					</div>
					<div class="fe">
						<h5><b>Цвет:</b></h5>
						#<input type="text" name="color" value="<?=@$obj->color?>" size="12" maxlength="6"/>
					</div>
				</div>
<?php	if(!@$obj->has_children):
			if(isset($obj->type_autoformat) and count($obj->type_autoformat)):
				$type_autoformat = $obj->type_autoformat;
			elseif(!empty($obj)):
				$type_autoformat = $obj->title_formats->select_list('type_id','format');
			endif;
?>							
				<div class="fr">
					<h5>Автозаголовок <span class="autoformat<?php if(empty($obj->autotitle)) echo ' dn'?> note">Спецпеременные: $region, $district, $subway.</span></h5>
					<div class="fe">
						<label for="autotitle"><input type="checkbox" class="ch" name="autotitle" value="1" id="autotitle" title="Выключен|Включен"<?=form::checked(@$obj->autotitle,1,0)?> /> Выключен</label>
<?php	/*					<input class="autoformat<?php if(empty($obj->autotitle)) echo ' dn'?>" type="text" name="type_autoformat[0]" id="type_autoformat[0]" value="<?=@$type_autoformat[0]?>" />*/?>
						<span class="autoformat<?php if(empty($obj->autotitle)) echo ' dn'?> note">Пример: $street{. $rooms комн}{. $floor этаж{ ($floors_total-этажного дома)}}{. Жил.пл. $space}.</span> 
					</div>
				</div>
<?php	endif;?>				
				<div class="fr">
					<div class="fe">
						<h5>Типы объявлений: <a href="#" onclick="return checkAll($(this).parent().next());" class="act">выбрать все</a></h5>
						<table>
<?php				$list = ORM::factory('type')->find_all();
					
					$current = (isset($obj->type_id) and count($obj->type_id))?$obj->type_id:@$obj->types;
					
					foreach($list as $key => $type):
						$cur = '';
	
						if(is_array($current) and in_array($type->id,$current)):
							$cur = $type->id;
						elseif(is_object($current)):
							if(in_array($type->id, array_keys($current->select_list('id','title')))):
								$cur = $type->id;
							endif;
						else:
							$cur = $current;		
						endif;
?>
							<tr>
								<td>
									<label for="t<?=$key?>"><input type="checkbox" id="t<?=$key?>" name="type_id[]" value="<?=$type->id?>"<?=form::checked($cur, $type->id, !empty($params['select_all']))?>> <?=$type->title?></label>
								</td>
<?php		if(!@$obj->has_children):?>								
								<td>
									<input class="autoformat<?php if(empty($obj->autotitle)) echo ' dn'?>" type="text" name="type_autoformat[<?=$type->id?>]" id="type_autoformat[<?=$type->id?>]" value="<?=@$type_autoformat[$type->id]?>" />
								</td>
<?php		endif?>								
							</tr>
<?php 
					endforeach; 
?>			
						</table>
					</div>
				</div>					
				
<?php if(!@$obj->has_children):?>				
				<div class="fr">
					<h5 id="item" title="Дополнительные параметры">Дополнительные параметры:</h5>
					
					<table class="listtable">
						<thead>
							<tr>
								<td class="t">Название:</td>
								<td title="Переменная для автозаголовка">Перем.:</td>
								<td>Тип данных:</td>
								<td>Ед. из:</td>
								<td>Мин./Макс.:</td>
								<td title="Обязательный параметр">Об.</td>
								<td title="Список">Сп.</td>
								<td title="Быстрый список">Бс.</td>
								<td></td>
							</tr>
						</thead>
						<tbody>
<?php	$i = 0;
		if(!empty($obj->items)):
			foreach($obj->items as $item):?>						
							<tr class="item<?php if(($i+1)%2) echo ' odd'; if($item->list_id != '') echo ' wlist'?>">
								<td class="t">
										<input type="hidden" name="item[<?=$i?>][id]" value="<?=$item->id?>" />
										<input type="text" name="item[<?=$i?>][title]" maxlength="64" value="<?=form::value($item->title)?>" /></td>
								<td class="u"><input type="text" name="item[<?=$i?>][codename]" value="<?=form::value($item->codename)?>" /></td>
								<td class="ty"><select name="item[<?=$i?>][datatype]">
										<option value="string"<?=form::selected($item->datatype,'string')?>>строка</option>
										<option value="integer"<?=form::selected($item->datatype,'integer')?>>цел. число</option>
										<option value="decimal"<?=form::selected($item->datatype,'decimal')?>>дес. число</option>
										<option value="datetime"<?=form::selected($item->datatype,'datetime')?>>дата/время</option>
										<option value="date"<?=form::selected($item->datatype,'date')?>>дата</option>
										<option value="time"<?=form::selected($item->datatype,'time')?>>время</option>
										<option value="year"<?=form::selected($item->datatype,'year')?>>год</option>
										<option value="phone"<?=form::selected($item->datatype,'phone')?>>телефон</option>
<?php /*										<option value="boolean"<?=form::selected($item->datatype,'boolean')?>>да/нет</option> */?>
									</select></td>
								<td class="u"><input type="text" name="item[<?=$i?>][units]" maxlength="64" value="<?=form::value($item->units)?>" /></td>
								<td class="l"><input type="text" name="item[<?=$i?>][minlength]" maxlength="5" value="<?=form::value($item->minlength)?>" /><input type="text" name="item[<?=$i?>][maxlength]" maxlength="5" value="<?=form::value($item->maxlength)?>" /></td>
								<td><input type="checkbox" name="item[<?=$i?>][required]" value="1" class="ch"<?=form::checked($item->required)?> /></td>
								<td class="il"><input type="checkbox" name="item[<?=$i?>][islist]" value="1" class="ch"<?=form::checked($item->list_id?1:0)?> /></td>
								<td class="ib"><input type="checkbox" name="item[<?=$i?>][isquicklist]" value="1" class="ch<?=!$item->list_id?' dn':''?>"<?=form::checked($item->isquicklist?1:0)?> /></td>
								<td class="b">
									<div class="down">↓<i></i></div>
									<div class="up<?php if($i == 0) echo ' nobut'?>">↑<i></i></div>
									<div class="delete">x<i></i></div>
								</td>
							</tr>
<?php			if($item->list_id != ''):?>
							<tr class="list<?php if(($i+1)%2) echo ' odd'?>">
								<th>список:</th>
								<td colspan="8">
									<select name="item[<?=$i?>][list_id]" onchange="useListValueAsItemTitle(this)">
<?php				if(!empty($lists))
						foreach($lists as $list):?>
										<option title="<?=$list->isquick?>" value="<?=form::value(@$list->id)?>"<?=form::selected($item->list_id,$list->id)?>><?=html::specialchars($list->title)?></option>
<?php					endforeach;?>									
									</select></td>
							</tr>
<?php			endif;?>							
<?php			$i++;
			endforeach;
		endif; ?>							
							<tr class="item<?php if(($i+1)%2) echo ' odd'?>">
								<td class="t">
										<input type="hidden" name="item[<?=$i?>][id]" value="" />
										<input type="text" name="item[<?=$i?>][title]" maxlength="64" value="" /></td>
								<td class="u"><input type="text" name="item[<?=$i?>][codename]" value="" /></td>
								<td class="ty"><select name="item[<?=$i?>][datatype]">
										<option value="string">строка</option>
										<option value="integer">цел. число</option>
										<option value="decimal">дес. число</option>
										<option value="datetime">дата/время</option>
										<option value="date">дата</option>
										<option value="time">время</option>
										<option value="year">год</option>
										<option value="phone">телефон</option>
										<option value="boolean">да/нет</option>
									</select></td>
								<td class="u"><input type="text" name="item[<?=$i?>][units]" maxlength="64" value="" /></td>
								<td class="l"><input type="text" name="item[<?=$i?>][minlength]" maxlength="5" value="" /><input type="text" name="item[<?=$i?>][maxlength]" maxlength="5" value="" /></td>
								<td><input type="checkbox" name="item[<?=$i?>][required]" value="1" class="ch" /></td>
								<td class="il"><input type="checkbox" name="item[<?=$i?>][islist]" value="1" class="ch" /></td>
								<td class="ib"><input type="checkbox" name="item[<?=$i?>][isquicklist]" value="1" class="ch dn" /></td>
								<td class="b">
									<div class="down nobut">↓<i></i></div>
									<div class="up<?php if($i == 0) echo ' nobut'?>">↑<i></i></div>
									<div class="delete<?php if($i == 0) echo ' nobut'?>">x<i></i></div>
								</td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="8"></td>
								<td><div class="add" onclick="listtableAddItem()">+<i></i></div></td>
							</tr>
						</tfoot>
					</table>
				</div>
<?php else:?>
				<div class="clb"></div>				
<?php endif;?>			
			</div>
			<div class="blueblock bcorns">	
				<div class="buttons">
<?php if(empty($obj)):?>
					<input type="submit" name="add" value="Добавить" class="subm" />
<?php endif;?>					
					<input type="submit" name="save" value="Сохранить" class="subm" />
					<input type="submit" name="delete" value="Удалить" onclick="return confirm('Вы уверены?')" class="but" />
					<input type="submit" name="new" value="Создать новый" class="but" />
				</div>
				<div id="ajaxstatus">Проверка данных...</div>
				<i class="cb"><i></i><b></b></i>
			</div>
		</form>
		<script type="text/javascript">
			var listItems = [
<?php 
	if(!empty($lists)):
		$i = 0;		
		foreach($lists as $list):
			if($i > 0) echo ',';?>
				[<?=$list->id?>, '<?=html::specialchars($list->title)?>', '<?=$list->isquick?>']<?php
			$i++;
		endforeach;
	endif;?>				
			];

			$(function(){
				$('#autotitle').click(function(){
					if(this.checked){$('.autoformat').show()}
					else $('.autoformat').hide();
				});
			});
		</script>
	</div>
</div>
<div class="main main2">
	<form action="/adm/ro/edit/" name="main_form" id="main_form" method="post" enctype="multipart/form-data">
		<input type="hidden" name="id" value="<?=form::value(@$obj->id)?>" />
		<div class="bluetable ftable bcorns"><i class="ct"><i></i><b></b></i>
		
			<table width="100%">
				<tr class="hr">
					<th>Клиент: <i class="req">*</i></th>
					<td>
						<input type="text" name="ro_client_title" maxlength="45" class="in" id="ro_client_title" value="<?=form::value(@$obj->ro_client_title)?>" title="Клиент" />
						&nbsp;&nbsp; ИНН: <input type="text" name="ro_client_inn" maxlength="45" class="in" id="client_inn" title="ИНН" />
<?php		if(!empty($clientList)):
				$clientList = array(''=> 'Выбрать из списка') + $clientList;?>
						<?=form::dropdown(array('name'=>'ro_client_id', 'id'=>'ro_client_id', 'class'=>'in nodis'), $clientList, @$obj->ro_client_id)?>
<?php		endif?>						
					</td>
				</tr>
				<tr><td colspan="2"><hr /></td></tr>
				<tr>
					<th>Разделы: <i class="req">*</i></th>
					<td>
<?php		if(!empty($obj) and (@$obj->categories->count() or $obj->onhome)):?>
						<div class="current_category">
<?php			if(!empty($obj->onhome)):?>
							<label for="ccatHOME"><input type="checkbox" id="ccatHOME" name="category_id[]" value="HOME" checked="checked" /> <b>На главной (<?=Lib::config('payment.ro_price_onhome')?>)</b></label><br />
<?php			endif;?>
<?php			foreach($obj->categories as $item):?>
							<label for="category<?=$item->id?>"><input type="checkbox" id="category<?=$item->id?>" name="category_id[]" value="<?=$item->id?>" checked="checked" /> <?=$item->title?> (<?=$item->ro_price?>)</label><br />
<?php			endforeach;?>						
						</div>
						<br />
<?php		endif;?>					
<?php		if(!empty($categoryList)):?>
						<div id="categories">
							<div>
								<select name="category_id[]" class="req" title="Разделы">
									<option value="">Выбрать из списка</option>
									<option value="HOME" style="font-weight:bold">На главной (<?=Lib::config('payment.ro_price_onhome')?>)</option>
<?php			foreach($categoryList as $item):?>						
									<option value="<?=$item->id?>"><?=str_repeat('&nbsp;&nbsp;', $item->level - 1)?><?=$item->stitle; if(!empty($item->ro_price)) echo ' ('.$item->ro_price.')';?></option>
<?php			endforeach;?>
								</select>
								<a href="#" class="act">добавить ещё</a>
							</div>
						</div>
<?php		endif?>
					</td>
				</tr>
				<tr>
					<th>Заголовок: <i class="req">*</i></th>
					<td><input type="text" name="title" maxlength="50" class="in dw" value="<?=form::value(@$obj->title)?>" id="title" />
						<span class="symbolsleft"><b>50</b></span>
					</td>
				</tr>
				<tr>
					<th>Введение:</th>
					<td><textarea name="description" class="in dw req" id="description" title="Введение" cols="50" rows="2"><?=form::value(@$obj->description)?></textarea>
						<span class="symbolsleft"><b>110</b></span>
						<div class="note" style="width:550px">В заголовок и текст объявления нельзя включать номера телефонов и адреса e-mail, также запрещено набирать слова заглавными буквами (кроме названий и аббревиатур).</div>
					</td>
				</tr>
				<tr>
					<th>Организация:</th>
					<td><input type="text" name="organization" maxlength="128" class="in" value="<?=form::value(@$obj->organization)?>" id="organization" />
						<span class="symbolsleft"><b>128</b></span>
					</td>
				</tr>
				<tr>
					<th>Адрес:</th>
					<td><input type="text" name="address" maxlength="255" class="in" value="<?=form::value(@$obj->address)?>" id="address" />
						<span class="symbolsleft"><b>255</b></span>
					</td>
				</tr>
				<tr>
					<th>Телефон:</th>
					<td><input type="text" name="phone" maxlength="128" class="in" value="<?=form::value(@$obj->phone)?>" id="phone" />
						<span class="symbolsleft"><b>128</b></span>
					</td>
				</tr>
				<tr>
					<th>Факс:</th>
					<td><input type="text" name="fax" maxlength="128" class="in" value="<?=form::value(@$obj->fax)?>" id="fax" />
						<span class="symbolsleft"><b>128</b></span>
					</td>
				</tr>
				<tr>
					<th>E-mail:</th>
					<td><input type="text" name="email" maxlength="128" class="in" value="<?=form::value(@$obj->email)?>" id="email" />
						<span class="symbolsleft"><b>128</b></span>
					</td>
				</tr>
				<tr>
					<th>Цена:</th>
					<td><input type="text" name="price" maxlength="12" class="in" value="<?=form::value(@$obj->price)?>" id="price" />
						<span class="symbolsleft"><b>128</b></span> <span class="note">можно использовать текстовые значения</span>
					</td>
				</tr>
				<tr id="upload_image">
					<th>Картинка: <i class="req">*</i></th>
					<td><input type="file" name="image[0]" maxlength="12" class="file" size="40" id="image" />
						<span class="note">размер 230 х 120px</span>
					</td>
				</tr>
<?php	if(@$obj and $obj->pictures->count()):?>
				<tr id="current_image">
					<th class="note">Текущая:</th>
					<td><?=$obj->pictures[0]->f('full','html')?></td>
				</tr>
<?php	endif;?>
				<tr><td colspan="2"><hr /></td></tr>
				
				<tr>
					<th></th>
					<td>
						<label for="redirect_content"><input type="radio" name="redirect" value="content" id="redirect_content" <?=form::checked(@$obj->redirect,'content',1)?> /> Направлять на детальное описание товара/услуги</label><br />
						<label for="website"><input type="radio" name="redirect" value="url" id="website" <?=form::checked(@$obj->redirect,'url',0)?> /> Направлять на интернет страницу</label> 
					</td>
				</tr>
				<tr>
					<th>Подробное<br />содержание<br />объявления:</th>
					<td><textarea name="content" id="content" cols="120" rows="10"><?=form::value(@$obj->content)?></textarea>
						<div class="symbolsleft note" style="text-align:left">Доступно <b>2000</b> символов</div>
					</td>
				</tr>
				<tr>
					<th>URL:</th>
					<td><input type="text" name="website" maxlength="255" class="in dw" value="<?=form::value(@$obj->website)?>" id="website" /></td>
				</tr>		
				<tr><td colspan="2"><hr /></td></tr>		
				<tr>
					<th>Дата (ДД-ММ-ГГГГ): <i class="req">*</i></th>
					<td class="gy">начало: <input type="text" name="start" class="req" title="Начало" size="10" value="<?= (!empty($obj)) ? date('d-m-Y', strtotime($obj->date_start)) : date('d-m-Y')?>" />&nbsp;&nbsp;&nbsp;
					конец: <input type="text" name="end" class="req" title="Конец" size="10" value="<?= (!empty($obj)) ? date('d-m-Y', strtotime($obj->date_end)) : ''?>" /></td>
				</tr>
				<tr>
					<th><b>Стоимость:</b></th>
					<td><input type="text" name="cost" maxlength="45" id="cost" style="font-size:18px" value="<?=form::value(@$obj->cost)?>" /> <?=Lib::config('payment.currency_list', Lib::config('payment.main_currency'))?></td>
				</tr>
				<tr>
					<th>Агент:</th>
					<td><input type="text" name="ro_agent_title" maxlength="45" class="in" id="ro_agent_title" value="<?=form::value(@$obj->ro_agent_title)?>" />
						&nbsp;&nbsp;
<?php		if(!empty($agentList)):
				$agentList = array(''=> 'Выбрать из списка') + $agentList;?>
						<?=form::dropdown(array('name'=>'ro_agent_id', 'id'=>'ro_agent_id', 'class' => 'nodis'), $agentList, @$obj->ro_agent_id)?>
<?php		endif?>	</td>
				</tr>
				<tr><td colspan="2"><hr /></td></tr>
				<tr>
					<th>Статус:</th>
					<td><label for="status_enabled"><input type="radio" name="status" value="1" id="status_enabled" <?=form::checked(@$obj->status,'enabled',0)?> /> <b>Опубликовано</b></label> 
						<label for="status_disabled"><input type="radio" class="draft" name="status" value="0" id="status_disabled" <?=form::checked(@$obj->status,'disabled',1)?> /> <b>Черновик</b></label>						
					</td>
				</tr>
			</table>	
			
			<div class="clb"></div>
		</div>
		<div class="blueblock bcorns">				
			<div class="buttons">
				<input type="submit" name="update" value="Сохранить" class="subm" />
				<input type="submit" name="save" value="Применить" class="but" />
<?php	if(@$obj->id):?>				
				<input type="submit" name="dublicate" value="Дублировать" class="g but" />
<?php	endif;?>				
				<input type="submit" name="delete" value="Удалить" class="but" onclick="return confirm('Вы уверены?')" />
				<input type="submit" name="new" value="Отмена" class="but" />
				<span id="ajaxstatus">Проверка данных...</span>
			</div>
			<i class="cb"><i></i><b></b></i>
		</div>
		<?php echo @$form_messages?>
	</form>
</div>
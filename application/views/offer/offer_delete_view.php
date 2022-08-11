<?php if(!empty($offer)):?>
<form id="message_form" method="post" action="<?=!empty($delete_mode)?$offer->url_delete:$offer->url_remove?>" class="delete_form">
<div class="offer_message bmar">	
	<input type="hidden" name="offer_id" value="<?=form::value($offer->id)?>" />
	<h1><?=@$title?></h1>
	<h2><b class="offer_type"><?=$offer->type->title?>:</b> <?=$offer->title?></h2>
	<table>
		<tbody>
<?php 	if(empty($no_reason)):?>
			<tr>
				<th>Укажите причину:</th>
			</tr>
			<tr>
				<td><?php echo form::dropdown(array('name'=>'predefined_reason', 'id'=>'predefined_reason', 'class'=>'w'), Lib::config('app.remove_reasons')); ?></td>
			</tr>
			<tr>
				<td><textarea name="content" id="reason_content" class="w" cols="47" rows="6" disabled="disabled"></textarea>
					<div class="note symbolsleft">Осталось <b>200</b> символов</div>
				</td>
			</tr>	
<?php	endif?>			
			<tr><th>Вы действительно хотите<?php if(empty($delete_mode)) echo ' окончательно';?> удалить это объявление?</th>
<?php	if($this->isModerator() and !empty($delete_mode)):?>
			<tr>
				<td><label for="remove_totally"><input type="checkbox" id="remove_totally" name="remove_totally" class="ch" value="1" /> Удалить совсем</label></td>
			</tr>
<?php	endif?>
			<tr>
				<td>
					<input type="submit" value="Да" class="submit" /> 
					<input type="submit" name="cancel" value="Нет" />
					<span id="ajaxstatus">Отправка данных...</span>
				</td>
			</tr>

		</tbody>
	</table>

</div><?php echo @$form_messages?>
</form>
<div class="back_to_offer"><a href="<?=$offer->url?>">← Вернуться к просмотру объявления</a></div>	
<?php endif;?>
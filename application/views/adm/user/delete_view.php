<?php if(!empty($mainobj)):?>
<form id="message_form" method="post" action="<?=@$form_action?>" class="delete_form">
<div class="message bmar">
	<input type="hidden" name="obj_id" value="<?=form::value($mainobj->id)?>" />
	<h1><?=@$title?></h1>
	<div class="role"><?=Lib::config('app.user_roles', $mainobj->role)?></div>
	<h2><?=$mainobj->contact_name?></h2>
	<table>
		<tbody>
			<tr>
				<th>Укажите причину:</th>
			</tr>
			<tr>
				<td><?php echo form::dropdown(array('name'=>'predefined_reason', 'id'=>'predefined_reason', 'class'=>'w'), Lib::config('app.user_delete_reasons')); ?></td>
			</tr>
			<tr>
				<td><textarea name="content" id="reason_content" class="w" cols="47" rows="6" disabled="disabled"></textarea>
					<div class="note symbolsleft">Осталось <b>500</b> символов</div>
				</td>
			</tr>	
			<tr><th>Вы действительно хотите удалить этого пользователя?</th>
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
<?php endif;?>
<?php if(!empty($mainobj)):?>
<form id="message_form" method="post" action="<?=$mainobj->url_ban?>" class="ban_form">
<div class="message bmar">
	<input type="hidden" name="obj_id" value="<?=form::value(@$mainobj->id)?>" />
	<h1><?=@$title?></h1>
	<div class="role"><?=Lib::config('app.user_roles', $mainobj->role)?></div>
	<h2><?=$mainobj->contact_name?></h2>
	<table>
		<tbody>
<?php 	if(empty($no_reason)):?>
			<tr>
				<th>Укажите причину:</th>
			</tr>
			<tr>
				<td><?php echo form::dropdown(array('name'=>'predefined_reason', 'id'=>'predefined_reason', 'class'=>'w'), Lib::config('app.user_ban_reasons'), 'other'); ?></td>
			</tr>
			<tr>
				<td><textarea name="content" id="reason_content" title="Причина" class="w" cols="47" rows="6" disabled="disabled"></textarea></td>
			</tr>	
<?php	endif?>			
			<tr><th>Вы действительно хотите заблокировать пользователя?</th>
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
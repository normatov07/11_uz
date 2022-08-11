<?php if(!empty($bookmark)):?>
<form id="message_form" method="post" action="<?=$bookmark->url_delete?>" class="bookmark_delete_form">
<div class="offer_message bmar">
	
	<input type="hidden" name="bookmark_id" value="<?=form::value($bookmark->id)?>" />
	<h1><?=@$title?></h1>
	<table>
		<tbody>
			<tr><th>Вы действительно хотите удалить эту закладку?</th>
			<tr>
				<td>
					<input type="submit" value="Да" class="submit" /> 
					<input type="submit" name="cancel" value="Нет" />
					<span id="ajaxstatus">Подождите...</span>
				</td>
			</tr>
		</tbody>
	</table>

</div><?php echo @$form_messages?>
</form>
<?php endif;?>
<div class="main">
	<p>Введите Код активации, который был выслан Вам на почту.</p>

	<form name="activate_form" id="activate_form" method="post" action="/activate/" onsubmit="return false">
		<input type="hidden" name="uid" value="<?=strip_tags($_POST['uid'])?>" />
	<div class="bcorns ftable htable cyantable bmar"><i class="ct"><i></i><b></b></i>
	<table>
		<tr>
			<th>Код активации:</th>
			<td colspan="2"></td>
		</tr>
		<tr>
			<td><input name="activation_key" type="text" maxlength="64" class="w" title="Код активации" value="<?=form::value(@$obj->user_activation->activation_key)?>" /></td>
			<td><input type="submit" class="subm" value="Активировать!"></td>
			<td><div id="ajaxstatus">Проверка данных. Подождите...</div></td>
		</tr>
	</table>
	<i class="cb"><i></i><b></b></i></div>
	<?php echo @$form_messages?>
	</form>
</div>
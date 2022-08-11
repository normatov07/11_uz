<div class="main">
	<p>Введите новый E-mail и Код подтверждения, которые были высланы Вам на почту.</p>

	<form name="email_change_form" id="email_change_form" method="post" action="/email_change/">
	<div class="bcorns ftable cyantable bmar"><i class="ct"><i></i><b></b></i>
	<table>
		<tr>
			<th>E-mail:</th>
			<th>Код подтверждения:</th>
			<td colspan="2"></td>
		</tr>
		<tr>
			<td><input name="email" type="text" maxlength="64" class="specInput" title="E-mail" value="<?=form::value(@$email)?>" /></td>
			<td><input name="code" type="text" maxlength="64" title="Код подтверждения" value="<?=form::value(@$code)?>" /></td>
			<td><input type="submit" class="subm" value="Подтвердить!"></td>
			<td><div id="ajaxstatus">Проверка данных...</div></td>
		</tr>
	</table>
	<i class="cb"><i></i><b></b></i></div>
	<?php echo @$form_messages?>
	</form>
</div>
<?php if(!empty($offer)):?>
<form id="message_form" method="post" action="<?=$offer->url_complaint?>">
<div class="offer_message bmar">
	<input type="hidden" name="offer_id" value="<?=form::value($offer->id)?>" />
	<h1><?=@$title?></h1>
	<h2><b class="offer_type"><?=$offer->type->title?>:</b> <?=$offer->title?></h2>
	<table>
		<tbody>
			<tr>
				<th>Ваше имя: <i class="req">*</i></th>
				<td><?php if($this->isLoggedIn() and $this->user->public_name): echo '<b>'.$this->user->public_name.'</b>'; else: ?><input type="text" name="name" maxlength="64" class="w" /><?php endif;?></td>
			</tr>
			<tr>
				<th>E-mail: <i class="req">*</i></th>
				<td><?php if($this->isLoggedIn() and $this->user->contact_email): echo '<b>'.$this->user->contact_email.'</b>'; else: ?><input type="text" name="email" maxlength="128" class="w" /><?php endif?></td>
			</tr>
			<tr>
				<th>Тема: <i class="req">*</i></th>
				<td><?php echo form::dropdown(array('name'=>'title', 'id'=>'title', 'class'=>'w'), Lib::config('app.complaint_titles')); ?></td>
			</tr>
			<tr>
				<th>Комментарий: <i class="req">*</i></th>
				<td><textarea name="content" class="w" cols="47" rows="6"></textarea>
					<div class="note symbolsleft">Осталось <b>500</b> символов</div>
				</td>
			</tr>
<?php if(!$this->isLoggedIn()):?>				
			<!--tr class="captcha">
				<th>Защитный код: <i class="req">*</i></th>
				<td>
					<input name="captcha_code" type="text" id="captcha_code" maxlength="5" />
					<img id="captcha" src="/captcha/">
					<div class="note">введите код с картинки, кликните на код если он непонятен</div>
				</td>
			</tr-->
    <input type="hidden"  name="captcha_code" value="4444">
<?php endif?>
			<tr><th></th>
				<td><input type="submit" name="send" value="Отправить" class="submit" /> 
					<input type="submit" name="cancel" value="Отменить" />
					<span id="ajaxstatus">Отправка данных...</span>
				</td>
			</tr>			
		</tbody>
	</table>

</div><?php echo @$form_messages?>
</form>

<div class="back_to_offer"><a href="<?=$offer->url?>">← Вернуться к просмотру объявления</a></div>	
<?php endif;?>
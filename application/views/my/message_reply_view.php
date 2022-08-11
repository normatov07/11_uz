<div class="main messages message_reply">
	<div class="lcol">	
		<h2>
			<a href="/my/messages/inbox/">Входящие: <?=@$count_incoming;  if($this->user->new_messages_count) echo ' (+'.$this->user->new_messages_count.')'?></a> 
			<a href="/my/messages/outbox/">Исходящие: <?=@$count_outgoing?></a>
		</h2>
<?php if(!empty($message)):?>
<?php	$offer = &$message->offer;?>
		<div class="ofr singleofr bcorns"><i class="ct"><i></i><b></b></i>
				<table>
					<tr>
						<td class="p"><?=$offer->price_html_list?></td>
						<td class="d">
<?php	if($offer->status == 'enabled'):?>						
							<h5><?=$offer->type->title?></h5>
							<h3><a href="<?=$offer->url?>"><?=$offer->title?></a></h3>
<?php	else:?>
							<h5><?=$offer->type->title?></h5>
							<h3><?=$offer->title?></h3>
<?php	endif;?>
							<p><?=$offer->short_description?></p>
						</td>
						<td class="i">											
							<div class="c">
<?php	if($offer->status == 'enabled'):?>							
							<a href="<?=$offer->category->url?>"><?=$offer->category->title?></a>
<?php	else:?>
							<?=$offer->category->title?>						
<?php	endif;?>							
							</div>
							<div class="c"><?=$offer->region->title?></div>
						</td>
					</tr>
				</table>
				<i class="cb"><i></i><b></b></i>
			</div>
	
		
		<h3 id="reply">Сообщение:</h3>
		<div class="bluetable bcorns"><i class="ct"><i></i><b></b></i>
			<div class="message_content">
				<h5><?=$message->sender_name_html?> <span>написал: <?=date::getSimple($message->added)?></span></h5>
				<?php if($message->sender_email):?><div>Электронная почта: <?=$message->sender_email_html?></div><?php endif?>
				<?php if($message->sender_phone):?><div>Контактный телефон: <?=$message->sender_phone?></div><?php endif?>
				
				<div class="message_text"><?=$message->content?></div>
			</div>
			
<?php if($message->is_repliable):?>			
			<form name="message_reply" action="" method="post">
			<div class="reply">
				<h3>Ваш ответ:</h3>
				<textarea name="content" cols="100" rows="6" title="Ваш ответ"></textarea>
				<div class="note symbolsleft">Осталось <b>2000</b> символов</div>
				<div class="buttons">
					<?=@$form_messages?>
					<input type="submit" name="send" value="Отправить сообщение" style="width:150px" />
					<input type="submit" name="cancel" value="Закрыть" />
					<span id="ajaxstatus">Сообщение отправляется...</span>
				</div>
			</div>
			</form>
<?php endif;?>			
			<i class="cb"><i></i><b></b></i>
		</div>	
		
		<div class="editlist messagelist">
		<?php	
			
			if($messageList->count()):

?>
				<table>
					<thead>
						<th>Другие сообщения на это объявление</th>
					</thead>
					<tbody>
<?php			foreach($messageList as $item):?>					
						<tr class="<?=$item->status?>">	
							<td class="msg">
								<h5><a href="<?=$item->url?>"><?=$item->name?></a> <span><?=date::getSimple($item->added)?></span></h5>
								<p><?=$item->short_content?></p>
								<div class="act"><a href="<?=$item->url?>">Прочитать</a><?php if($item->is_repliable):?> | <a href="<?=$item->url_reply?>">Ответить</a><?php endif;?></div>
							</td>
						</tr>
		<?php	endforeach;?>
					</tbody>
				</table>
<?php		endif?>
		</div>
		
<?php	endif; // message?>			
	</div>
	 <div class="rcol">
	
<?= new View('b_right_banner_view')?>
<?= new View('b_right_menu_view')?>
       
	</div>
</div>
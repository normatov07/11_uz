<div class="main messages">
	<div class="lcol">
	
		<h2>
			<a href="/my/messages/inbox/"<?php if(@$mode == 'inbox') echo ' class="this"'; ?>>Входящие: <?php if($mode == 'inbox') echo '<span id="count_total">'.$count_incoming.'</span>'; else echo $count_incoming; if($this->user->new_messages_count) echo ' (+'.$this->user->new_messages_count.')'?></a> 
			<a href="/my/messages/outbox/"<?php if(@$mode == 'outbox') echo ' class="this"'; ?>>Исходящие: <?php if($mode == 'outbox') echo '<span id="count_total">'.$count_outgoing.'</span>'; else echo $count_outgoing;?></a>
		</h2>

<?php	if($messageList->count()):?>
		<form name="mymessageslist" action="" method="post" id="editlist_form">
			<div class="editlist messagelist">
			<?=@$form_messages?>
				<table>
					<thead>
						<tr>
							<td colspan="3"><?php
							switch($mode):
								case 'outbox':
									echo 'Ваши сообщения / Ваши ответы';
								break;
								case 'inbox':
									echo 'Вам пишут сообщение / Вам отвечают';
								break;
							endswitch;?></td>
							<td><?php
							switch($mode):
								case 'outbox':
									echo 'На объявления / На сообщения';
								break;
								case 'inbox':
									echo 'На объявления / На Ваши сообщения';
								break;
							endswitch;?></td>
						</tr>
					</thead>
					<tbody>
<?php			foreach($messageList as $message):
				
					if(!empty($message->offer_id)):
						$offer = &$offerListByID[$message->offer_id];
						if($offer->is_expired and $offer->status == 'enabled'):
							$offer_status = 'expired';
						else:
							$offer_status = $offer->status;
						endif;
					else:
						$offer_status = 'disabled';
					endif;
					
					?>					
						<tr class="ofr<?php if($mode == 'inbox') echo ' ' . $message->status?>" id="o<?=$message->id?>">	
							<td class="ch"><input type="checkbox" name="i[]" value="<?=form::value(@$message->id)?>" /></td>
							<td class="msg">
<?php				if($mode == 'outbox'):?>		
								<h5>Кому: <?=!empty($message->to_user)?'<a href="'.$userListByID[$message->to_user]->url.'">' . $userListByID[$message->to_user]->own_name . '</a>':$message->name?></a> <span><?=date::getSimple($message->added)?></span></h5>
								<p><?=$message->short_content?></p>
<?php				else:?>
								<h5><?=!empty($message->user_id)?'<a href="'.@$userListByID[$message->user_id]->url.'">' . @$userListByID[$message->user_id]->own_name . '</a>':$message->name?> <span><?=date::getSimple($message->added)?></span></h5>
								<p><?=$message->short_content?></p>								
								<div class="act"><a href="<?=$message->url?>">Прочитать</a><?php if($message->is_repliable):?> | <a href="<?=$message->url_reply?>">Ответить</a><?php endif;?></div>
<?php				endif?>								
							</td>
							<td class="a">→</td>
<?php				if($message->reply_to):?>
							<td class="y">
								<b><?php if($mode == 'outbox') echo 'Ваш ответ на сообщение:'; else echo 'Ответ на ваше сообщение:'?></b>
								<?=$message->reply_to_content?>
								
<?php				elseif($offer_status == 'enabled'):?>
							<td class="o<?= $offer_status == 'enabled'?' enabled':' disabled'?>">
							
								<a href="<?=$offer->url?>"><?=$message->reply_to_content?></a>
<?php				else:?>
							<td class="o disabled">
								<?=$message->reply_to_content?>
<?php				endif;?>			
							</td>
						</tr>
<?php			endforeach;?>
					</tbody>
				</table>

				</div>
				<div class="buttons">
					<div class="service_links"><a href="#" id="select_all">Выделить все</a> <a href="#" id="deselect_all">Отменить выделение</a></div>
<?php			echo @$form_messages?>			
					<input type="submit" name="delete_selected" value="Удалить выделенные" style="width:150px" />
<?php			if($mode == 'inbox'):?>					
					<input type="submit" name="setread_selected" value="Отметить прочитанными" style="width:180px" />
<?php			endif?>					
					<span id="ajaxstatus">Отправка данных...</span>
				</div>

<?php			echo $pagination;?>
		</form>	
<?php		else:?>
		<div class="editlist messagelist">
			<p>У вас нет ни одного <?php if(@$mode == 'outbox'):?>исходящего<?php else:?>входящего<?php endif?> сообщения.</p>	
			<br /><br />
		</div>
<?php		endif;?>
	</div>
	 <div class="rcol">
	
<?= new View('b_right_banner_view')?>
<?= new View('b_right_menu_view')?>
       
	</div>
</div>
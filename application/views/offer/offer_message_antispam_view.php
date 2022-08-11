<?php if(!empty($offer)):?>
<h2>Внимание!</h2>
Вы достигли ограничения <?=$message_limit_amount?> за период <?=$message_limit_period?>.
Вы сможете отправить сообщение через <?=$message_limit_left?>.  

<div class="back_to_offer"><a href="<?=$offer->url?>">← Вернуться к просмотру объявления</a></div>	
<?php endif;?>
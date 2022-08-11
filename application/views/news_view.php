<div class="main news">
<?php if(!empty($obj)):?>
	<div class="lcol news_item">	
		<div class="date"><?=date::getLocalizedDate($obj->published)?></div>
        <p  ><?=$obj->description?></p>
		<?=$obj->content?>
		<div class="other"><a href="/news/">Смотреть другие новости</a></div>	
<?php elseif(!empty($publishedList)):?>
	<div class="lcol newslist">
<?php		foreach($publishedList as $entry):?>
		<div class="item">

        	<h3><a href="<?=$entry->url?>"><?=$entry->title?></a></h3>
            <div class="d"><?=date::getLocalizedDate($entry->published)?></div>
            <p><?=$entry->description?> <a href="<?=$entry->url?>">Читать далее »</a></p>
		</div>
<?php		endforeach;?>
		<?=@$pagination?>
<?php endif;?>		
	</div>
    <div class="rcol">	
<?= new View('b_right_banner_view');?>     
    </div>

</div>
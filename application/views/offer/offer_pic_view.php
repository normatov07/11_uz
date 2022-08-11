<?php if(!empty($offer)):?>
<div class="offer_images">
<?php	
	$list = '';
	$i = 0;
	while(isset($offer->pictures[$i])):

		if($offer->pictures[$i]->id == $current_pic_id) $current = &$offer->pictures[$i];
		
		$list .= '<td><a href="'.$offer->url_pictures . $offer->pictures[$i]->id.'/" name="'.$offer->pictures[$i]->f('full').'|'.$offer->pictures[$i]->width.'|'.$offer->pictures[$i]->height.'" title="'.$offer->pictures[$i]->title.'">';
		$list .= '<img src="'.$offer->pictures[$i]->f('thumb').'" width="80" height="60" alt="'.$offer->pictures[$i]->title.'" border="0">';
		$list .= '</a></td>';
		
		$i++;
	endwhile;
?>
	<div class="image">
		<div id="image_view"><?=$current->f('full','html')?></div>
		<div class="image_title"><?=@$current->title?></div>
	</div>
	<div id="image_list" class="image_list"><div class="image_list_box"><div class="scroller"><table><?php echo $list?></table></div></div></div>

	<div class="back_to_offer"><a href="<?=$offer->url?>">← Вернуться к объявлению</a></div>
	<img src="/assets/img/dot.gif" width="1" height="1" border="0" alt="" />
</div>
<?php endif;?>
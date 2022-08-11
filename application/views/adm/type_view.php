 
<div class="main main2">
	<div class="lcol type">
	
	 <div class="pagemenu"><?=$links?></div>
	
<?php
	if(!empty($offerList)):
	?>
		<div class="offerlist<?php if(!@$obj->has_price) echo ' offerlist_wop'?>">
		
         
<?php 
		
		$premiumset = false; $simpleisset = false;

		foreach($offerList as $offer):
		
 ?>
			<div class="ofr<?php 
			
			if($offer->is_premium):
				echo ' premium'; 
			else:
				if($offer->is_marked) echo ' corns or';
				else echo '';
					
			endif;?>"><i class="ct"><i></i><b></b></i>
                <table>
					<tr>
						<td class="p">
							<a href="<?=$offer->url?>">
								<?php if(isset($pictures[$offer->id])){ ?>
                                    <div class='offer-photo text-center' style='background-image: url("<?=$pictures[$offer->id]->f('thumb')?>")'></div>
								<?php } else { ?>
									<div class="no-photo text-center"></div>
								<?php } ?>
							</a>
						</td>
						<td class="d"><?php if(empty($obj->id)):?><h5><?=@mb_strtolower($types[$offer->type_id]->intention_title)?></h5><?php endif?>
                        <h3><a href="<?=$offer->url?>"><?=$offer->title?></a></h3>
							<p><?=$offer->short_description?></p>
						</td>
						<td class="i">
							<?=$offer->price_html_list?>
                            <?php /* <div class="c"><?=$regions[$offer->region_id]?></div> */ ?>
<?php		if(empty($category_has_no_children)):?>
							<div class="c"><a href="<?=@$categories[$offer->category_id]->url?>"><?=@$categories[$offer->category_id]->title?></a></div>
<?php		endif;?>				
							<div class="t"><?=date::getRelativeDatetime($offer->added, true)?></div>
						</td>
						<td class="m"><?=$offer->act_icons?></td>
					</tr>
				</table><i class="cb"><i></i><b></b></i>
                
			</div>
<?php	endforeach;	?>	
        </div>						

<?php	echo @$pagination;
	else: // offers count?>
		<div>
			<p>Объявлений с заданными параметрами не найдено.</p>
		</div>
<?php
	endif; // offers count?>	
	
		<div class="pagemenu bpagemenu"><?=$links?></div>
		
	</div>
<?php if(!$printMode):?>	
    <div class="rcol">
<?= new View('b_right_banner_view')?>
<?= new View('b_right_menu_view')?>
		<div id="ya_direct" class="block"></div>
<?php //= new View('b_news_view')?>
	</div>
<?php endif;?>
</div>
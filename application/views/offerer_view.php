<h1>Профиль автора</h1>
<div class="main offerer">
	<div class="lcol">	
        <div class="bcorns ftable bluetable tmar">
			<h2><?=$offerer->public_name?></h2>
        	<table>
	
				<tr>
					<td colspan="2" class="role<?php if($offerer->is_agent) echo ' g'?>"><?=Lib::config('app.user_roles',$offerer->role)?></td>
				</tr>

				<tr>
					<th>E-mail:</th>
					<td><?=$offerer->public_email_html?></td>
				</tr>
<?php if($offerer->public_phone):?>
				<tr>
					<th>Телефон:</th>
					<td><?=@$offerer->public_phone_html?></td>
				</tr>
<?php endif?>
				<tr>
					<th>Адрес:</th>
					<td><?php if(!empty($offerer->region_id)):?><b><?=@$offerer->region->title?></b><?php if(!empty($offerer->address)): echo ', '.$offerer->address;?><?php if(!empty($offerer->reference_point)) echo ' (Ориентир: '.$offerer->reference_point .')'; endif;?><?php else: echo 'Не указан'; endif;?></td>
				</tr>
				<!--noindex-->

                <!--/noindex-->
				<tr>
					<th>Дата регистрации:</th>
					<td><?=date::getLocalizedDate(@$offerer->registered)?></td>
				</tr>
			</table>

		</div>
		
<?php
	
	if(!empty($offerList) and $offerList->count()):?>
		<a name="offers"></a>
		<div class="offerlist">
<?php	foreach($offerList as $offer):?>
		
			<div class="ofr<?php 
				if($offer->is_premium):
					echo ' premium'; 
				else:
					if($offer->is_marked) echo ' corns or';
					echo ' ';						
				endif;?>">
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
						<td class="d">
                        <h3><a href="<?=$offer->url?>"><?=$offer->title?></a></h3>
							<p><?=$offer->short_description?></p>
						</td>
						<td class="i">
							<?=$offer->price_html_list?>
                            <div class="c"><?=$regions[$offer->region_id]?></div>
<?php		if(empty($category_has_no_children)):?>
							<div class="c"><a href="<?=@$categories[$offer->category_id]->url?>"><?=@$categories[$offer->category_id]->title?></a></div>
<?php		endif;?>				
							<div class="t"><?=date::getRelativeDatetime($offer->added, true)?></div>
						</td>
					</tr>
				</table>
			</div>
<?php	endforeach;?>
		</div>
<?php echo $pagination;?>
<?php endif?>
		
    </div>
    <div class="rcol">	
<?= new View('b_right_banner_view')?>
<?= new View('b_right_menu_view')?>    
    </div>
</div>


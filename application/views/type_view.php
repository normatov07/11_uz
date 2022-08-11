<div class="breadcrumb">
    <a href="/" class="text-muted">Главная</a>
    <a href="<?=@$obj->url?>" class="text-muted"> » <span><?=@$obj->title?></span></a>
</div>


<h1><?=$obj->other_title?></h1>


 
<?php
if(!empty($offerList)):
	?>

    <div class="offer-cards">


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
                                    <?php
                                    if($offer->is_premium){
                                        ?>
                                        <span class="inlblk icon paid  abs zi2"></span>
                                        <?php
                                    }
                                    ?>
                                    <div class='offer-photo text-center' style='background-image: url("<?=$pictures[$offer->id]->f('thumb')?>")'></div>
								<?php } else { ?>
									<div class="no-photo text-center"></div>
								<?php } ?>
							</a>
						</td>
						<td class="d"><?php if(empty($obj->id)):?><?php endif?>
                        <h3><a href="<?=$offer->url?>"><?=$offer->title?></a></h3>
							<p><?=$offer->short_description?></p>
						</td>
						<td class="i">
							<?=$offer->price_html_list?>
                            <?php /* <div class="c"><?=$regions[$offer->region_id]?></div> */ ?>

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

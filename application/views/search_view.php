<?php if(!empty($title)):?><h1><?=$title?></h1><?php endif?>

<div class="main main2">
	<div class="lcol search">		
		<form name="filters" action="/search/page/1/">
			<?php echo @$form_messages?>
			<div class="filters bcorns"><i class="ct"><i></i><b></b></i>
				<div class="content">
					<div class="querybox">
						<input type="text" name="q" value="<?=form::value(@$q)?>" class="q" maxlength="45" title="Ключевое слово или фраза" /><input type="submit" value="Найти" />				
					</div>
					<div class="clb"></div>
					<div class="select">
						<label>Тип:</label>
						<?=form::dropdown(array('name'=>'type_id', 'id'=>'type_id', 'title'=>'Тип объявления', 'class'=>'nodis'), $types, @$_REQUEST['type_id']);?>
					</div>
					<div class="select">
						<label>Период:</label>
						<?=form::dropdown(array('name'=>'period', 'id'=>'period', 'title'=>'Период', 'class'=>'nodis'), Lib::config('app.periods'), @$_REQUEST['period']);?>
					</div>
					<div class="select">
						<label>Раздел:</label>
						<?=form::dropdown(array('name'=>'category_id', 'id'=>'category_id', 'title'=>'Раздел', 'class'=>'category nodis'), array('' => 'любой') + @$maincategories, @$_REQUEST['category_id']);?>
					</div>

					<div class="clb"></div>
				</div>
				
				<i class="cb"><i></i><b></b></i>
			</div>
		</form>
<?php if(!empty($offerList)):?>				
		<div class="results">Найдено: <b><?=format::declension_numerals(@$offersCount, '<b>объявление</b>','<b>объявления</b>','<b>объявлений</b>');?></b><?php /*if($results['categories']):?> в <b>3</b> разделах<?php endif */?></div>
<?php endif;?>
<?php

/**
 * Определяем общее количество предложений в категории
 */

	if(!empty($offerList) and $offerList->count()):
	?>
		<div class="offerlist">

<?php 
	
	
		
		$premiumset = false; $simpleisset = false;
		
		$offerIDs = array_keys($offerList->select_list());
		
		if(count($offerIDs)) $pictures = ORM::factory('picture')->find_all_for('offer', $offerIDs);

		foreach($offerList as $offer):
			//echo $offer->status;
			if(!$premiumset and $offer->is_premium):?>		

<?php			$premiumset = true;
			elseif(!$simpleisset and !$offer->is_premium):?>			
<?php	if($premiumset):?>		
		</div>

		<div class="offerlist">
<?php	endif;?>	
<?php 			$simpleisset = true;
			endif;?>
			<div class="ofr<?php 
				if($offer->is_premium):
					echo ' premium'; 
				else:
					if($offer->is_marked) echo ' corns or';
					echo '';
						
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
						<td class="m"><?=$offer->act_icons?></td>
					</tr>
				</table><i class="cb"><i></i><b></b></i>
			</div>
<?php	endforeach;	?>	
        </div>		


<?php echo $pagination;?>
<?php elseif(!empty($SEARCH_PROCESSED)): // offers count?>
		<div class="instructions">
			<p class="text-center text-muted empty-state">Объявлений с заданными параметрами не найдено.</p>
			

			
		</div>
<?php endif; // offers count?>	
	
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
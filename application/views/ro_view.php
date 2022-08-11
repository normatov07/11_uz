<?php if(!empty($obj)):?>
<div class="main main2">
	<div class="lcol offer roview">		
		
<?php	if($obj->pictures->count()):?>	
		<div class="image"><div class="placeholder">
			<?=$obj->pictures[0]->f('full','html')?>
		</div></div>
<?php	endif;?>
		<div class="type">Рекламное объявление</div>		

<?php	if(!empty($obj->price)):?>		
			<div class="theprice">Цена: <b><?=$obj->price?></b></div>
<?php	endif;

		if(!empty($obj->description)):?>
		<div class="description">
			<?=$obj->content?>
		</div>
<?php	endif;?>		

		<div class="offer_contacts">
		
			<h2 class="g">Контактная информация</h2>			
			<div class="bcorns cyantable"><i class="ct"><i></i><b></b></i>
				<table>
					<tbody>
<?php		if($obj->organization):?>
						<tr><th>Организация:</th><td><?=$obj->organization;?></td></tr>
<?php		endif?>
<?php		if($obj->address):?>
						<tr><th>Адрес:</th><td><?=$obj->address;?></td></tr>
<?php		endif?>
<?php		if($obj->phone):?>
						<tr><th>Телефон:</th><td><b><?=$obj->phone;?></b></td></tr>
<?php		endif?>
<?php		if($obj->fax):?>
						<tr><th>Факс:</th><td><?=$obj->fax;?></td></tr>
<?php		endif?>
<?php		if($obj->email):?>
						<tr><th>E-mail:</th><td><?=html::mailto($obj->email);?></td></tr>
<?php		endif;?>				
<?php		if($obj->website):?>
						<tr><th>Веб-сайт:</th><td><a href="<?=$obj->website;?>"><?=$obj->website;?></a></td></tr>
<?php		endif;?>			
					</tbody>
				</table>
				<i class="cb"><i></i><b></b></i>
			</div>
				
		</div>
		<div class="clb"></div>
		<ul class="offer_actions">
			<li><noindex><a href="<?=$obj->url_print?>" rel="nofollow" class="printit" title="Распечатать объявление">Распечатать</a></noindex></li>
		
<?php // MODERATOR MENU
		if($this->isModerator()):?>
			<li class="moderator"><a href="<?=$obj->url_edit?>" class="lnk">Редактировать</a></li>
<?php	endif?>		
		</ul>
	</div>
	
<?php	if(!$printMode):?>	
    <div class="rcol">
	
<?= new View('b_right_banner_view')?>
<?= new View('b_right_menu_view')?>
	
	</div>
<?php	endif?>
<?php endif?>
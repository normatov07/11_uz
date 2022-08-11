<a name="mess"></a>
<div class="mess bcorns<?php 
	if(!empty($errors)) echo ' error';
	if(!empty($warnings)) echo ' warning';
	if((!empty($errors) and count($errors)) or (!empty($messages) and count($messages))) echo ' vis'?>"><i class="ct"><i></i><b></b></i><div><?php if(count(@$errors)):?>
		<h3>Внимание ошибка:</h3>
		<ul class="errors">
<?php	foreach($errors as $item):?>
			<li><?=$item?></li>
<?php	endforeach;?>
		</ul>
<?php endif;?>
<?php if(count(@$warnings)):?>
		<ul>
<?php	foreach($warnings as $item):?>
			<li><?=$item?></li>
<?php	endforeach;?>
		</ul>
<?php endif;?>
<?php if(count(@$messages)):?>
		<ul>
<?php	foreach($messages as $item):?>
			<li><?=$item?></li>
<?php	endforeach;?>
		</ul>
<?php endif;?></div><i class="cb"><i></i><b></b></i></div>

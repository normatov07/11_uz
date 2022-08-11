<?php if(!empty($list) and $rototal = count($list)):?>
	<div class="rolist">
<?php	$i = 0;
		foreach($list as $item):
			$even = ($i != 0 and $i%2 != 0); $i++;
			$link = '<a href="'.$item->url.'" title=\''.$item->description.'\'';
			if($item->redirect == 'url') $link .=' target="_blank"';
			$link .= '>'; ?>
		<div class="ro">
			<h4><?=$link . $item->title?></a></h4>
			<?php
			if(!empty($item->pic)) echo $link . $item->pic->f('full','html') .'</a>';?> 
		</div>
<?php	if($even):?><div class="clb"></div><?php endif?>		
<?php	endforeach;?>

    </div>
<?php	endif; // !empty($list)?>



<div style="margin-top:7px;"><a href="/reklama/ro/" style="font-size:11px;color:#999">Спецразмещение объявлений</a></div>

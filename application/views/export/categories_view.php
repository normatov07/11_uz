<?php
echo '<?xml version="1.0" encoding="utf-8"?>' . "
";?>
<categories>
	<listtitle><?=text::xml_convert($title);?></listtitle>
<?php	$subopened = 0;
		$tabs = '';
		foreach($items as $item): ?>
<?php		if(isset($prevlevel) and $item->level > $prevlevel):
				$subopened = true;?> 
		<subs>
<?php		elseif(isset($prevlevel) and $item->level < $prevlevel):
				$subopened = false;?> 
			</category>
		</subs>
	</category>
<?php		elseif(isset($prevlevel)):?> 
<?=$tabs?>	</category>
<?php		endif;
	$tabs = str_repeat('	', $item->level - 1 + ($subopened * 1));
	echo $tabs;
?>	<category id="<?=$item->id?>">
<?=$tabs?>		<title><?=text::xml_convert($item->title)?></title>
<?=$tabs?>		<description><?=text::xml_convert(text::untypography($item->description))?></description>
<?=$tabs?>		<url><?=Lib::config('app.url').$item->url?></url><?php
		$prevlevel = $item->level;
		endforeach; 
		if(!empty($subopened)):?>
			</category>
		</subs>
<?php	endif;?>
	</category>  
</categories>

<?php
echo '<?xml version="1.0" encoding="utf-8"?>' . "
";?>
<offers>
	<listtitle><?=text::xml_convert($title);?></listtitle>
<?php foreach($items as $item): ?>
	<offer id="<?=$item->id?>">
		<published><?=$item->added?></published>
		<category id="<?=$item->category_id?>"><?=text::xml_convert($category[$item->category_id]->title); ?></category>
		<title><?=text::xml_convert($types[$item->type_id] . ': '. $item->title)?></title>
		<price><?=text::xml_convert($item->price_html)?></price>
		<image><?php if(!empty($pictures[$item->id])) echo Lib::config('app.url'). $pictures[$item->id]->f('thumb'); Lib::config('app.url').$item->url?></image>
		<properties><?php
		if(!empty($datas[$item->id])):
		
			$previous_property = NULL;
			foreach($datas[$item->id] as $data):
				if($previous_property == $data->property_id):?>, 
					<?=text::xml_convert($data->datavalue)?>
<?php			else:
					if(!empty($previous_property)) echo '<br />';
					
					echo text::xml_convert($property[$data->property_id]->title. ': ' . $data->datavalue
						.($property[$data->property_id]->units ?  ' ' . $property[$data->property_id]->units:'')
						);
						
					$previous_property = $data->property_id;
				endif; ?>
<?php		endforeach; ?> 
		</properties>
<?php	else:?></properties>       
<?php	endif;?>
		<description><?=text::xml_convert(text::untypography($item->description))?></description>
		<contact_name><?php 
			if($item->has_not_user):
				if(!empty($item->name)):
					echo text::xml_convert($item->name);
				endif;
			else:
				echo text::xml_convert($users[$item->user_id]->contact_name);
			endif;
		?></contact_name>
		<phone><?php			
			if(!empty($item->phone)):
				echo text::xml_convert(format::phone($item->phone));
			elseif(!$item->has_not_user):
				echo text::xml_convert(format::phone($users[$item->user_id]->public_phone));
			endif;?></phone>
		<email><?php			
			if(!empty($item->email)):
				echo $item->email;
			elseif(!$item->has_not_user):
				echo $users[$item->user_id]->public_email;
			endif;?></email>
		<url><?=Lib::config('app.url').$item->url?></url>
	</offer>       
<?php endforeach; ?>    
</offers>

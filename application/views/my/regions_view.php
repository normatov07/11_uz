<div class="main regions">
	<p>По умолчанию вывод объявлений производится из всех регионов. Укажите регион(ы) для фильтрации вывода объявлений.</p>
	<form name="main_form" id="profile_form" method="post" action="">
	
	<?php echo @$form_messages?>	
	
	<div class="bcorns ftable bluetable bmar"><i class="ct"><i></i><b></b></i>
		<table>
			<tr>
				<td>
					<label for="r_all"><input type="checkbox" name="region[]" value="_all_" class="ch" id="r_all"<?php if(!count($selected_regions)) echo ' checked="checked"'?> /> <b>Все регионы Узбекистана</b></label>
				</td>
			</tr>
			<tr>
				<td>
<?php	foreach(@$regions as $i => $item):?>
					<label for="r<?=$item->id?>"><input type="checkbox" name="region[]" value="<?=$item->id?>" class="ch" id="r<?=$item->id?>"<?php if(in_array($item->id, $selected_regions)) echo ' checked="checked"'?> /> <?=$item->title?></label>
<?php		if($i == ceil(count($regions) / 2)):?>
				</td>
				<td>
<?php		endif;?>
<?php	endforeach;?>
				</td>
			</tr>
		</table>		
		<i class="cb"><i></i><b></b></i>
	</div>
	
	<table class="buttons">
		<tr>
			<td><input type="image" src="/i/b_save.png" id="add_form_submit" class="b_add img" name="update" alt="Сохранить изменения" value="Сохранить изменения" width="231" height="39" /></td>
			<td><span id="ajaxstatus">Проверка данных...</span></td>
		</tr>
	</table>


</form>	
<script type="text/javascript">
	$(function(){
		$(':checkbox[name="region[]"]').click(
			function(){
				if(this.value == '_all_'){
					$(':checkbox[name="region[]"]:not([value="_all_"])').attr('checked',false);
				}else{
					$(':checkbox[name="region[]"][value="_all_"]').attr('checked',false);
				};
			}
		);
	});
</script>
</div>
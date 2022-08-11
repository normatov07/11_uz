<div class="main">
<?php	if(!empty($bookmarkList) and $bookmarkList->count()): ?>
	<form name="mybookmarkslist" action="" method="post" id="editlist_form" class="offerlist editlist">

<?php
		$categories = ORM::factory('category')->in('id',$offerList->getValues('category_id'))->find_all()->as_id_array();

		$offerListByID = $offerList->as_id_array();

		$types = ORM::factory('type')->find_all()->select_list(NULL, 'intention_title');
		$regions = ORM::factory('region')->find_all()->select_list();

		if(empty($offerIDs)) $offerIDs = array_keys($offerList->select_list());

		if(count($offerIDs)) $pictures = ORM::factory('picture')->find_all_for('offer', $offerIDs);

		foreach($bookmarkList as $bookmark):

			$offer = &$offerListByID[$bookmark->offer_id];

			if($offer->is_expired and $offer->status == 'enabled') $offer->status = 'expired';

			?>

			<div class="ofr <?php
				if($offer->is_premium):
					echo ' premium';
				elseif($offer->is_marked):
					echo ' or';
				endif;

				echo ' ' . ($offer->status == 'enabled'?'enabled':'disabled');
				?>" id="o<?=$bookmark->id?>"><i class="ct"><i></i><b></b></i>
				<table>
					<tr><td class="ch"><input type="checkbox" name="id[]" value="<?=form::value($bookmark->id)?>" /></td>
						<td class="p"><?php if(isset($pictures[$offer->id])):?><a href="<?=$offer->url?>"><?php echo $pictures[$offer->id]->f('thumb','html')?></a><?php endif?></td>
						<td class="d">
							<h5><?=@mb_strtolower($types[$offer->type_id])?></h5>
							<h3><a href="<?=$offer->url?>"><?=$offer->title?></a></h3>
							<p><?=$offer->short_description?></p>
						</td>
						<td class="i">
	                        <?=$offer->price_html_list?>
							<div class="c"><a href="<?=$categories[$offer->category_id]->url?>"><?=$categories[$offer->category_id]->title?></a></div>
							<div class="c"><?=$regions[$offer->region_id]?></div>
						</td>
						<td class="s">
							<b>Добавлено</b>
							<p><?=date::getSimple($offer->added)?></p>
<?php	switch($offer->status):
			case 'enabled':?>
							<i>Актуально до</i>
							<p><?=date::getSimple($offer->expiration)?></p>
<?php		break;
			case 'expired':?>
							<i>Просрочено</i>
							<p><?=date::getSimple($offer->expiration)?></p>
<?php		break;
			case 'banned':?>
							<i>Заблокировано</i>
<?php						if(!$offer->checked):?>
							<p>Проверяется модератором</p>
<?php						else:?>
							<p>&nbsp;</p>
<?php						endif;?>
<?php		break;
			default:?>
							<i><?=Lib::config('app.offer_status', $offer->status)?></i>
							<p>&nbsp;</p>
<?php		break;
		endswitch;?>
						</td>

						<td class="m"><?=$offer->act_icons?></td>
					</tr>
				</table>
				<div class="actions">
					<div class="but">
						<a href="<?=@$bookmark->url_delete?>" class="remove modal">Удалить</a>
					</div>
				</div>
				<div class="clb"></div>
			</div>
<?php	endforeach;?>

		<div class="buttons">
			<div class="service_links"><a href="#" id="select_all">Выделить все</a> <a href="#" id="deselect_all">Отменить выделение</a></div>
<?php	echo @$form_messages?>
			<input type="submit" name="delete_selected" value="Удалить выделенные" style="width:150px" />
			<span id="ajaxstatus">Отправка данных...</span>
		</div>
		<div id="modalBox" class="jqmWindow"><a href="#" class="jqmClose x">закрыть</a><div class="jqmContent"></div></div>

<?php echo @$pagination;?>

	</form>

	<p class="nolistitems dn">У Вас нет закладок.</p>

<?php else:?>
	<p>У Вас нет закладок.</p>
<?php endif;?>
</div>
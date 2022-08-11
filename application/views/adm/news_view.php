<div class="main main2 wlist">
	<div class="lcol">
		<form action="/adm/news/" name="newsForm" id="newsForm" method="post">
			<input type="hidden" name="id" value="<?=form::value(@$obj->id)?>" />
			<?php echo @$form_messages?>
			<div class="bluetable bcorns"><i class="ct"><i></i><b></b></i>
				<div class="fr">
					<h5>Заголовок: <i class="req">*</i></h5>
					<input type="text" name="title" maxlength="128" class="in dw" value="<?=form::value(@$obj->title)?>" id="title" />
				</div>
				<div class="fr">
					<h5>Введение: <i class="req">*</i></h5>
					<textarea name="description" class="in dw" id="description" cols="50" rows="3"><?=form::value(@$obj->description)?></textarea>
				</div>
				<div class="fr">
					<h5>Содержание: <i class="req">*</i>
					<span class="quickhtml"><input id="add_url" type="button" value="ссылка"><input id="b" type="button" value="жирный"><input id="i" type="button" value="курсив"><input id="blockquote" type="button" value="справка"></span></h5>

					<textarea name="content" class="in dw" id="content" cols="50" rows="10"><?=form::value(@$obj->content)?></textarea>
				</div>	
				<div class="fr">
					<div class="fe" style="width:200px">
						<h5><b>Статус:</b></h5>
						<label for="status_enabled"><input type="radio" name="status" value="1" id="status_enabled" <?=form::checked(@$obj->status,'enabled',0)?> /> Опубликован</label><br />
						<label for="status_disabled"><input type="radio" class="draft" name="status" value="0" id="status_disabled" <?=form::checked(@$obj->status,'disabled',1)?> /> Черновик</label>
					</div>
					<div class="fe">
						<h5><b>Дата публикации: <label for="edit_published" class="note"><input type="checkbox" class="ch enable_digidate" name="edit_published" id="edit_published" /> Редактировать</label></b></h5>
						<?=form::digiDateTimeSelect(array('name'=>'publication'), @$obj->published?$obj->published:date::getForDb())?>
					</div>
					
				</div>
				<div class="clb"></div>
			</div>
			<div class="blueblock bcorns">				
				<div class="buttons">
					<input type="submit" name="update" value="Сохранить" class="subm" />
					<input type="submit" name="save" value="Сохранить и редактировать" style="width:185px" class="but" />
					<input type="submit" name="delete" value="Удалить" class="but" onclick="return confirm('Вы уверены?')" />
					<input type="submit" name="new" value="Отмена" class="but" />
				</div>
				<div id="ajaxstatus">Проверка данных...</div>
				<i class="cb"><i></i><b></b></i>
			</div>
		</form>
	</div>	
    <div class="rcol">
<?php if(count($readyList)):?>
		<h2>Ждут публикации:</h2>
		<ul class="list newslist">
<?php if(!empty($readyList)):
		foreach($readyList as $key=>$obj):?>		
			<li>
				<div class="d"><?=$obj->published?></div>
				<a name="<?=$obj->id?>" href="/adm/news/<?=$obj->id?>/"><?=$obj->title?></a>
			</li>
<?php 	endforeach;
		endif;?>
		</ul>
<?php endif?>	

<?php if(count($draftList)):?>
		<h2<?php if(count($readyList)) echo ' class="line"';?>>Черновики:</h2>
		<ul class="list newslist">
<?php if(!empty($draftList)):
		foreach($draftList as $key=>$obj):?>		
			<li>
				<a name="<?=$obj->id?>" href="/adm/news/<?=$obj->id?>/"><?=$obj->title?></a>
			</li>
<?php 	endforeach;
		endif;?>
		</ul>
<?php endif?>	

<?php if(count($publishedList)):?>
		<h2<?php if(count($draftList) or count($readyList)) echo ' class="line"';?>>Последние опубликованные:</h2>
		<ul class="list newslist">
<?php 	if(!empty($publishedList)):
			foreach($publishedList as $item):?>		
			<li<?php if(@$obj->id == $item->id) echo ' class="this"'?>>
				<a name="<?=$item->id?>" href="/adm/news/<?=$item->id?>/"><?=$item->title?></a>
			</li>
<?php 		endforeach;
		endif;?>
		</ul>
<?php 	echo @$pagination?>
<?php endif?>	
		

		
	</div>
</div>
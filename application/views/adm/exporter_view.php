<div class="main main2 wlist">
	<div class="lcol">
		<form action="/adm/exporter/" name="objForm" id="objForm" method="post">
			<input type="hidden" name="id" value="<?=form::value(@$obj->id)?>" />
			<?php echo @$form_messages?>
			<div class="bluetable bcorns"><i class="ct"><i></i><b></b></i>
				<div class="fr">
					<h5>Название: <i class="req">*</i></h5>
					<input type="text" name="title" maxlength="45" class="in dw" value="<?=form::value(@$obj->title)?>" id="title" />
				</div>
	
				<div class="fr">
					<h5>Логин: <i class="req">*</i></h5>
					<input type="text" name="login" maxlength="45" class="in dw" value="<?=form::value(@$obj->login)?>" id="login" />
				</div>
				<div class="fr">
					<h5>Пароль: <i class="req">*</i></h5>
					<input type="text" name="password" maxlength="45" class="in dw" value="<?=form::value(@$obj->password)?>" id="password" />
				</div>
                
				<div class="fr">
					<div class="fe" style="width:200px">
						<h5><b>Статус:</b></h5>
						<label for="status_enabled"><input type="radio" name="status" value="1" id="status_enabled" <?=form::checked(@$obj->status,'enabled',1)?> /> Включен</label><br />
						<label for="status_disabled"><input type="radio" class="draft" name="status" value="0" id="status_disabled" <?=form::checked(@$obj->status,'disabled',0)?> /> Выключен</label>
					</div>
				</div>
				<div class="clb"></div>
			</div>
			<div class="blueblock bcorns">				
				<div class="buttons">
					<input type="submit" name="update" value="Сохранить" class="subm" />
					<input type="submit" name="delete" value="Удалить" class="but" onclick="return confirm('Вы уверены?')" />
					<input type="submit" name="new" value="Отмена" class="but" />
				</div>
				<div id="ajaxstatus">Проверка данных...</div>
				<i class="cb"><i></i><b></b></i>
			</div>
		</form>
	</div>	
    <div class="rcol">
<?php if(count($objectList)):?>
		<h2>Список</h2>
		<ul class="list objlist">
<?php 	if(!empty($objectList)):
			foreach($objectList as $item):?>		
			<li<?php if(@$obj->id == $item->id) echo ' class="this"'?>>
				<a name="<?=$item->id?>" href="/adm/exporter/<?=$item->id?>/"><?=$item->title?></a>
			</li>
<?php 		endforeach;
		endif;?>
		</ul>
<?php 	echo @$pagination?>
<?php endif?>	
	</div>
</div>
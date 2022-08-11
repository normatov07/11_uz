<div class="main main2 wlist">
	<div class="lcol">
		<form action="/adm/ro/agent/" name="mainForm" id="mainForm" method="post">
			<input type="hidden" name="id" value="<?=form::value(@$obj->id)?>" />
			<?php echo @$form_messages?>
			<div class="bluetable bcorns"><i class="ct"><i></i><b></b></i>
				<div class="fr">
					<h5>Название:</h5>
					<input type="text" name="title" maxlength="45" class="dw" value="<?=form::value(@$obj->title)?>" id="title" />
				</div>
				<div class="fr">
					<h5>Статус:</h5>
					<label for="status_enabled"><input type="radio" name="status" value="1" id="status_enabled" <?=form::checked(@$obj->status,'enabled',0)?> /> Активен</label><br />
					<label for="status_disabled"><input type="radio" name="status" value="0" id="status_disabled" <?=form::checked(@$obj->status,'disabled',1)?> /> Неактивен</label>
				</div>
				<div class="clb"></div>
			</div>
			<div class="blueblock bcorns">				
				<div class="buttons">
					<input type="submit" name="save" value="Сохранить" class="subm" />
					<input type="submit" name="delete" value="Удалить" class="but" onclick="return confirm('Вы уверены?')" />
					<input type="submit" name="new" value="Создать новый" class="but" />
				</div>
				<div id="ajaxstatus">Проверка данных...</div>
				<i class="cb"><i></i><b></b></i>
			</div>
		</form>
	</div>	
    <div class="rcol">
		<div class="quicksearch">
			<form action="/adm/ro/agent/" method="get" id="quicklistsearch">
				<input type="text" name="q" class="q" value="<?=form::value(@$q)?>" /> <input type="submit" value="Найти" class="submit" /> <span class="busy dn"></span>
			</form>
			<ul class="viewmodes">
				<li><a href="/adm/ro/agent/?mode=enabled"<?php if(@$mode == 'enabled') echo ' class="this"' ?>>Активные</a>
				 | <a href="/adm/ro/agent/?mode=disabled"<?php if(@$mode == 'disabled') echo ' class="this"' ?>>Неактивные</a></li>
			</ul>			
		</div>
<?php if(!empty($objList)):?>
		<ul class="list line userlist">
<?php	foreach($objList as $item):?>		
			<li<?php if(@$obj->id == $item->id) echo ' class="this"'?>><a name="<?=$item->id?>" href="/adm/ro/agent/<?=$item->id?>/"><?=$item->title?></a></li>
<?php 	endforeach;?>
<?php 	echo @$pagination?>
		</ul>
<?php endif;?>
	</div>
</div>
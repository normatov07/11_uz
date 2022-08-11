<?php
	$EDITMODE = !empty($obj->id);

	$HIDE_FORM = (empty($category) or $category->has_children);


$ipdata['country_code'] = 'UZ';

if ($ipdata['country_code'] == 'UZ'):

?>

<h1>Новое объявление</h1>

<form class="main offer offer_edit" name="edit_offer_form" id="edit_offer_form" method="post" action="/offer/<?php if($EDITMODE) echo $obj->id .'/edit'; else echo 'add'?>/" enctype="multipart/form-data">
<noscript><?php if(!empty($_REQUEST['no_js'])):?>
	<div class="tmar"><?php echo @$form_messages?></div>
<?php endif;?><input type="hidden" name="no_js" value="1"/></noscript>

	<script type="text/javascript">
		function hid(id, hide){return document.getElementById(id).style.display = (hide?'none':'block');};
		hid('edit_offer_form',1);
	</script>

<?php	if(!$EDITMODE):?>




<?php	endif; // EDITMODE ?>
<?php if($EDITMODE):?><input type="hidden" name="id" value="<?=form::value(@$obj->id)?>" /><?php endif?>
	<div class="categories title">
<?php
		if($EDITMODE and !$this->isAgent()):?>
		<div class="breadcrumbs">
<?php		$category = &$obj->category;
			foreach($category->parents as $item):
				echo ' -- ' . '<a href="'.$item->url.'" class="btn">'.$item->title.'</a>';
			endforeach;?>
		</div>
<?php	else:?>
		<div class="cont">
<?php		if($EDITMODE) $category = &$obj->category;

			if(!empty($category)):
				$prevURL = '';
				foreach($category->parents as $item):
					if($item->id == $category->id and !$category->has_children) break;
					echo '<span id="'.$item->id.'"><a class="btn" href="/offer/'.($EDITMODE?$obj->id.'/edit':'add').$prevURL.'/">'.$item->stitle.'</a> ++ </span>';
					if($item->id == $category->id) break;
					$prevURL = '/'. $item->codename;
				endforeach;

				if(!$category->has_children):
					$category_parent_id = $category->parent_id;
					$category_current = $category->id;
				else:
					$category_parent_id = $category->id;
				endif;

			endif;

		$categoryModel = ORM::factory('category')->where('parent_id', !empty($category_parent_id)?$category_parent_id:0);

		if($EDITMODE):
			$categoryModel->where('(`status` = "enabled" or `id` = "' . $obj->category->id . '")');
		else:
			$categoryModel->where('status','enabled');
		endif;

		$list = array('' => 'Выберите категорию') + $categoryModel->find_all()->select_list();



?>
            <div>
                <div style="padding-bottom: .5em"><label class="text-muted">Выберите категорию</label></div>
                <?= form::dropdown(array('name'=>'category_id', 'id'=>'category_id', 'title'=>'Раздел'), $list, @$category_current);?>
            </div>



			<noscript><input type="submit" class="submit" value="Выбрать" name="get_category" /></noscript>
<!--			<span class="note">&laquo; кликните на выпадающий список</span>-->



		</div>
<?php	endif;?>
		
	</div>
    <br/>
    <div id="category_warning" class="bcorns lcol"<?php if(!isset($category_warning) || !$category_warning['warning_status']):?>style="display: none;"<?php endif;?>>
    
    <noindex><span style="color:#880088;">
        Вы превысили лимит активных объявлений в разделе.
        Физическое лицо может размещать не более 2-х объявлений.
        Если вы являетесь агентством по недвижимости или риелтором и у вас имеется лицензия,
        пожалуйста, прочтите <a href="/news/15/" target="_blank">условия получения статуса "Риелтор"</a>.
    </span></noindex>
    
    </div>
	<div id="formContent" class="lcol"<?php if(!$EDITMODE and ($HIDE_FORM or isset($category_warning) and $category_warning['warning_status'])) echo ' style="display:none"'?>>

		<div class="req_instr">Поля обязательные для заполнения помечены звёздочкой <i class="req">*</i></div>

		<div class="">

			<div class="offer_options">
				<div class="fe select_type">
					<h5>Тип объявления: *</h5>
<?php
if(!empty($category)):
	//$hide_price = $category->types->count() <= 1;
	echo AppLib::getTypes(array('category'=> &$category, 'titlevar' => 'intention_title'), @$obj->type_id);
else:
	echo AppLib::getTypes(array('titlevar' => 'intention_title'));
endif;?>
				</div>
				<div class="fe">
					<h5>Регион:</h5>
<?php
	$regions = ORM::factory('region')->find_all();
	$regions_select = $regions->select_list();

	$first_region_id = $regions[0]->id;

	if(empty($region_id)) $region_id = @$obj->region_id?@$obj->region_id:($this->isLoggedIn()?$this->user->region_id:$first_region_id);

	if(!empty($category) and ($category->has_district or $category->has_subway)):
		$regions = $regions->as_id_array();
	endif;

	if(!isset($regions[$region_id])) $region_id = $first_region_id;

	echo form::dropdown(array('name'=>'region_id', 'id'=>'region_id', 'title'=>'Регион', 'class'=>'req'), $regions_select, $region_id);

?>					<noscript>
						<input class="submit" type="submit" name="get_region" value="Выбрать"/>
					</noscript>
				</div>
				<div class="fe">
					<h5>Актуально:<?php if($EDITMODE):?> <span>до <?=date::getLocalizedDate($obj->expiration)?></span><?php endif?></h5>
			<?php if($EDITMODE):?>добавить
			<?=form::daysAmountSelect(array('name'=>'period', 'title'=>'Срок размещения', 'startwithzero' => true));?>
			<?php else:?>
			<?=form::daysAmountSelect(array('name'=>'period', 'title'=>'Срок размещения', 'style' => 'width:125px'), @$obj->period?$obj->period:Lib::config('app.offer_expiration_days'));?>
			<?php endif;?>
				</div>
			</div>

			<table id="properties"<?php if($EDITMODE and !$category->properties->count()) echo ' class="dn"'?>>
				<tbody>
<?php		if(!empty($category) and $category->has_district and $regions[$region_id]->has_district):
				$districts = array(''=>'Выберите') + $regions[$region_id]->districts->select_list() + array(-1 => 'любой', -2 => 'нет');
				$district_type = Lib::config('app.district_type', $regions[$region_id]->district_type);?>
				<tr>
					<th<?php if($category->has_district == 2) echo ' class="req"'?>><span id="district_title"><?=$district_type?></span>:<?php if($category->has_district == 2) echo '  <i class="req">*</i>'?></th>
					<td><?=form::dropdown(array('name'=>'district_id',  'title' => $district_type, 'class'=>($category->has_district == 2?'req':'')), $districts, @$obj->district_id?@$obj->district_id:'') ?></td>
				</tr>
<?php		endif;?>
<?php		if(!empty($category) and $category->has_subway and $regions[$region_id]->has_subway):
				$subways = array(''=>'Выберите') + $regions[$region_id]->subways->select_list();?>
				<tr>
					<th<?php if($category->has_subway == 2) echo ' class="req"'?>>Станция метро:<?php if($category->has_subway == 2) echo '  <i class="req">*</i>'?></th>
					<td><?=form::dropdown(array('name'=>'subway_id',  'title' => 'Станция метро'), $subways, @$obj->subway_id?@$obj->subway_id:'') ?></td>
				</tr>
<?php		endif;?>
<?php		if(!empty($category) and !$category->has_children):
				$properties = &$category->properties;
			endif;

			if(!empty($properties))
				foreach($properties as $property):?>
				<tr>
					<th<?php if($property->required == 1) echo ' class="req"'?>><?=$property->title;?>:<?php if($property->required == 1) echo '  <i class="req">*</i>';?></th>
					<td><?php
					if(!empty($obj)):
						if(!empty($obj->is_not_post)):
							echo $property->formField($obj->getDataByProperty($property->id));
						else:
							echo $property->formField($obj->property[$property->id]);
						endif;
					else:
						echo $property->formField();
					endif;?></td>
				</tr>
<?php 			endforeach;
				?></tbody>
			</table>

			<div class="clb"></div>
			
		</div>


		<div class="fr" id="title_block">
			<h5>Заголовок объявления: <i class="req">*</i></h5>
			<input type="text" name="title" id="title" class="req dw" title="Заголовок объявления" maxlength="76" value="<?=form::value(@$obj->title)?>" />
		</div>

		<div class="fr price<?php if(!empty($hide_price)) echo ' dn'?>" id="price_block">
			<h5>Цена: <span class="note">только целые или дробные числа</span></h5>
			<div>
				<?=form::dropdown(array('name' => 'price_type' , 'class' => 'price_type'), Lib::config('app.price_type'), @$obj->price_type)?>
				<span class="price_from<?php if(in_array(@$obj->price_type, array('', 'fixed', 'negotiated'))) echo ' dn'?>">от </span>
				<input type="text" name="price" id="price" maxlength="12" title="Цена" value="<?=form::value(@$obj->price)?>" class="price<?php if(in_array(@$obj->price_type, array('negotiated'))) echo ' dn'?>" />
				<span class="price_to<?php if(in_array(@$obj->price_type, array('', 'fixed', 'negotiated'))) echo ' dn'?>">до <input type="text" name="price_to" id="price_to" maxlength="12" title="Цена - до" value="<?=form::value(@$obj->price_to)?>" /></span>
				<?=form::dropdown(array('name'=>'currency', 'id'=>'currency', 'class'=> 'currency' . (in_array(@$obj->price_type, array('negotiated'))?' dn':'')), Lib::config('payment.currency_list'), @$obj->currency); ?>
			</div>

		</div>

		<div class="fr">
			<h5>Подробности объявления: <i class="req">*</i></h5>
			<div class="note">В тексте объявления <span class="red">запрещается указывать контактные данные</span>: телефоны, ссылки на сайты, электронная почта и т.д.</div>
			<textarea class="wide" cols="40" name="description" id="description" rows="11" title="Подробности объявления"><?=form::value(@$obj->description)?></textarea>
			<div class="symbolsleft">Осталось <b>2800</b> символов</div>
		</div>

		<div class="frC" id="images">
<?php if($EDITMODE and $obj->pictures->count()):?>
			<h5>Фотографии: <span class="note">не более <?=Lib::config('picture.offer', 'max_amount')?> шт.</span></h5>
			<ul class="list">
<?php 	foreach($obj->pictures as $key => $pic):?>
				<li>
					<a href="<?=$obj->url_pictures . $pic->id?>/" class="modal"><img src="<?=$pic->f('thumb')?>" width="80" height="60" alt="<?=$pic->title?>" border="0" /></a>
					<label for="mi<?=$pic->id?>"><input type="radio" name="mainimage" id="mi<?=$pic->id?>" value="<?=form::value($key)?>"<?php if(empty($mainset) and ($pic->priority == 0 || $obj->pictures->count() == 1)): $mainset = true; echo ' checked="checked" /> <span>Основная</span></label'; else: echo '/></label'; endif;?>>
					<label for="di<?=$pic->id?>"><input type="checkbox" name="deleteimage[]" id="di<?=$pic->id?>" value="<?=form::value($pic->id)?>"/> удалить</label>
				</li>
<?php	endforeach;?>
			</ul>
			<div class="add_images<?php if($obj->pictures->count() >= Lib::config('picture.offer', 'max_amount')) echo ' dn'?>">
			<h5>Добавить фото: <span class="note">размер не менее <?=Lib::config('picture.offer', 'width_min')?>x<?=Lib::config('picture.offer', 'height_min')?> px</span></h5>
				<div class="item">
					<input type="file" class="file" name="image[0]" size="40" title="Фото" />
<?php //				<input type="text" class="in" name="imagetitle[0]" size="40" title="Название фото" /> ?>
				</div>
			</div>
<?php else:?>
			<h5>Загрузите фотографии: <span class="note">не более <?=Lib::config('picture.offer', 'max_amount')?> шт., размер не менее <?=Lib::config('picture.offer', 'width_min')?>x<?=Lib::config('picture.offer', 'height_min')?> px</span></h5>
			<div class="item<?php if($EDITMODE and $obj->pictures->count() >= Lib::config('picture.offer', 'max_amount')) echo ' dn'?>">
				<input type="file" class="file" name="image[0]" size="40" title="Фото" />
<?php //				<input type="text" class="in" name="imagetitle[0]" size="40" title="Название фото" /> ?>
			</div>
<?php endif;?>

		</div>

<?php	if($this->isAgent() and !$EDITMODE):?>
		<div class="fr offer_mode">
			<h5>Режим объявления:</h5>
			<label for="has_not_user_yes"><input name="has_not_user" type="radio" value="1" id="has_not_user_yes"<?=form::checked(@$obj->has_not_user, '1', true)?> title="Объявление клиента" class="r">Объявление клиента </label>
			<label for="has_not_user_no"><input name="has_not_user" type="radio" value="0" id="has_not_user_no"<?=form::checked(@$obj->has_not_user, '0', false)?> title="Собственное объявление" class="r">Собственное объявление</label>
		</div>
<?php	endif?>

<?php	if($this->isLoggedIn()):?>

		<div class="fr offer_contacts">
			<h5 class="g">Контактные данные:<?php if(@$obj->is_viewed_by_owner):?> <b><a href="/my/settings/" target="_blank">изменить контакты</a></b><?php endif?></h5>

			<div class="bcorns ftable cyantable">

<?php		if($this->isAgent() and (!$EDITMODE or @$obj->has_not_user)):?>

				<table id="quick_registration">
					<tbody>
						<tr>
							<th>Телефон:</th>
							<td><input type="text" size="24" style="width:500px" name="phone" id="phone" title="Телефон" value="<?=form::value(format::phone(@$obj->phone))?>" maxlength="128" /></td>
						</tr>
						<tr>
							<th>E-mail:</th>
							<td><input type="text" size="24" name="email" id="email" title="Е-mail" value="<?=form::value(@$obj->email)?>" maxlength="128" class="w" style="margin-right: 5px" />
							<label for="email_status" class="note"><input type="checkbox" class="ch" name="email_status" id="email_status" value="1" <?=form::checked(@$obj->email_status == 'enabled', 1, 0)?> /> Показывать в объявлениях?</label></td>
						</tr>
						<tr>
							<th>Лицо/Организация:</th>
							<td><input type="text" size="24" name="name" id="name" title="Лицо/Организация" value="<?=form::value(@$obj->name)?>" maxlength="128" class="w" /></td>
						</tr>
					</tbody>
				</table>

<?php 		endif; //isAgent?>

<?php		if($this->isLoggedIn() and (!$EDITMODE or !@$obj->has_not_user)):?>
				<table id="offer_contacts"<?php if(!$EDITMODE and $this->isAgent()) echo ' class="dn"'?>>
					<tbody>
						<tr>
							<th>Телефон:</th>
							<td><input type="text" size="24" style="width:500px"  name="custom_phone" title="Телефон" value="<?=form::value(format::phone($EDITMODE?@$obj->public_phone:@$this->user->public_phone))?>" /></td>
						</tr>
						<tr>
							<th>E-mail:</th>
							<td><?php
								if($EDITMODE):
									echo $obj->contact_email;
								elseif($this->isAgent() and @$obj and @$obj->user):
									echo $obj->user->contact_email;
									$email_status = $obj->user->email_status;
								else:
									echo $this->user->contact_email;
									$email_status = $this->user->email_status;
								endif;?>
							<?php if(@$email_status == 'disabled'):?><span class="note">(скрыто)</span><?php endif?>
							</td>
						</tr>
						<tr><th><?php if($EDITMODE and $obj->user->is_company or $this->user->is_company) echo 'Организация'; else echo 'Контактное лицо'?>:</th>
							<td><?php
								if($EDITMODE):
									echo $obj->public_name;
								elseif($this->isAgent() and @$obj and @$obj->user):
									echo $obj->user->contact_name;
									$namestatus = $obj->user->name_status;
								else:
									echo $this->user->public_name;
									$namestatus = $this->user->name_status;
								endif;?>
							<?php if(@$name_status == 'disabled'):?><span class="note">(скрыто)</span><?php endif?>
							</td>
						</tr>
					</tbody>
				</table>
<?php		endif?>

				
			</div>
		</div>
<?php endif?>


<?php if(!$EDITMODE):?>

<?php	if(!$this->isLoggedIn()):?>
		<div class="fr" id="quick_registration">
			<h5 class="g">Ваши контакты <b>+ Быстрая регистрация</b></h5>

			<div class="bcorns ftable cyantable">
				<table width="100%">
					<tbody>
						<tr>
							<td colspan="2"><b>Для размещения объявления необходима регистрация.</b> Пожалуйста, введите следующие данные:</td>
						</tr>
						<tr>
							<th>E-mail:<i class="req">*</i></th>
							<td><input type="text" class="req w" name="user_email" id="user_email" title="Е-mail" maxlength="64" value="<?=@$obj->user_email?>" />
								<label for="email_visible" class="note"><input type="checkbox" class="ch" name="user_email_status" id="email_visible"<?php if(@$obj->user_email_status) echo ' checked'?> /> Показывать E-mail в объявлениях?</label>
							</td>
						</tr>
						<tr>
							<th>Пароль:<i class="req">*</i></th>
							<td><input type="password" class="req" id="user_password" name="user_password" title="Пароль" maxlength="64" />
								<span class="note">должен быть не менее 4 символов</span>
							</td>
						</tr>
						<tr>
							<th>Повторите пароль:<i class="req">*</i></th>
							<td><input type="password" class="req" name="user_repeat_password" id="user_repeat_password" title="Подтверждение пароля" maxlength="64" /></td>
						</tr>
						<tr>
							<th>Телефон:</th>
							<td><input type="text" class="w" name="user_phone" id="user_phone" title="Телефон" maxlength="128" value="<?=@$obj->user_phone?>" /></td>
						</tr>
						<tr>
							<th><label for="user_role">Ваш статус:</label></th>
							<td>
								<label for="rolegeneral"><input name="user_role" type="radio" value="general" id="rolegeneral" title="Частное лицо"<?=form::checked(@$obj->role, 'general', true)?>  class="r">Частное лицо</label>
								<label for="rolecompany"><input name="user_role" type="radio" value="company" id="rolecompany" title="Организация"<?=form::checked(@$obj->role, 'company')?> class="r">Организация</label>
							</td>
						</tr>
						<tr>
							<th>Имя/Название:</th>
							<td><input type="text" class="w" name="user_name" id="user_name" title="Имя частного лица/Название организации" maxlength="64" value="<?=@$obj->user_name?>" />
							<span class="note">Имя или Название для контактов</span>
							</td>
						</tr>
					</tbody>
				</table>
				
			</div>

			<div class="fr accept_disclaimer">
				<label for="accept_disclaimer"><input type="checkbox" class="ch req" name="user_accept_disclaimer" id="accept_disclaimer"<?php if(@$obj->user_accept_disclaimer) echo ' checked'?> title="Согласие с Условиями и правилами предоставления услуг" />
				Я ознакомлен(а) и принимаю </label><a href="/terms/" target="_blank">Условия и правила предоставления услуг</a>
			</div>
		</div>
<?php	// end of user registration
		endif;

		if(!$EDITMODE and (!$this->isAgent() and (!$this->isLoggedIn() or !$this->user->offers_count))): ?>
		<div class="fr bmar">
<!--			<h5>Защитный код: <i class="req">*</i></h5>-->
<!--			<img id="captcha" src="/captcha/default/--><?//=mt_rand();?><!--" style="float:left">-->

<!--            <div style="float:left;padding-left:10px">-->
<!--            <input name="captcha_code" type="text" id="captcha_code" maxlength="5" title="Защитный код" />-->
<!--			<span>введите код, изображённый на картинке.</span>-->
<!--            </div>-->
<!--            <div class="cb"></div>-->

            <input name="captcha_code" type="hidden" value="999" />

		</div>
<?php	endif; // isAgent?>
		<div class="fr">
			<?php echo @$form_messages?>

            <button class="btn btn-success " id="add_form_submit"  type="submit">Добавить объявление</button>





<?php	if($this->isAgent()):?>
<!--			<button type="submit" class="btn btn-secondary" name="save_and_continue"  id="save_and_continue" />Добавить и продолжить в этой теме</button>-->
<?php	endif;// isAgent?>
			<span id="ajaxstatus">Проверка данных...</span>
		</div>
<?php	if(!$this->isLoggedIn()):?>
		<div class="fr">
			<b>Внимание:</b> После добавления объявления Вам будет выслано письмо с подтверждением регистрации.
			Только после подтверждения регистрации Ваше объявление будет опубликовано на сайте.
			Отредактировать объявление и настроить свой аккаунт Вы можете сразу после добавления объявления.
		</div>
<?php	endif;// isLoggedIn?>

<?php else: // EDITMODE ?>
		<div class="fr">
			<?php echo @$form_messages?>
			<input type="image" src="/i/b_save.png" id="add_form_submit" class="b_add img" alt="Сохранить изменения" width="231" height="39" /> <span id="ajaxstatus">Отправка данных...</span>
		</div>
<?php endif; // EDITMODE ?>
    </div>


    <div class="rcol">
<?php	if(!$EDITMODE):?>
		<div class="readiness<?php if($this->isNotActivated()) echo ' readinessnoauth'?> dn" id="steps">
			<h2>Состояние готовности объявления:</h2>
			<div class="step corns">
				<div><b>1</b> Выберите категорию</div>
			</div>
			<div class="step corns">
				<div><b>2</b> Тип объявления</div>
			</div>
			<div class="step corns">
				<div><b>3</b> Детали объявления</div>
			</div>
			<div class="step corns">
				<div><b>4</b> Заголовок объявления</div>
			</div>
<?php /*			<div class="step">
				<div><b>5</b> Цена/стоимость</div>
			</div> */?>
			<div class="step corns">
				<div><b>5</b> Подробности объявления</div>
			</div>
<?php		if(!$this->isLoggedIn()):?>
			<div class="step corns">
				<div><b>6</b> Быстрая регистрация</div>
			</div>
<?php		endif;?>
<?php		if(!$this->isLoggedIn() or (!$this->isAgent() and !$this->user->offers_count)):?>
			<div class="step corns">
				<div><b>7</b> Защитный код</div>
			</div>
<?php		endif;?>
<?php		if($this->isAgent()):?>
			<div class="step corns">
				<div><b>6</b> Контактные данные</div>
			</div>
<?php		endif;?>
		</div>
<?php	else:?>

<?= new View('b_right_banner_view')?>
<?= new View('b_right_menu_view')?>

<?php	endif;?>
    </div>
</form>

<div class="preload dn" id="preload">
	<p class="loading">Пожалуйста, подождите — идёт загрузка...</p>
<?php if(!$EDITMODE):?><p>Если у вас проблемы с размещением объявлений, пожалуйста, <a href="/contacts/">сообщите нам</a> об этом.</p><?php endif?>
</div>
<script type="text/javascript">
	hid('preload',0);
<?php if(!$EDITMODE):?>	hid('steps',0); <?php endif?>
</script>

<?php 
// Проверка на IP адрес закрывается

else :
?> 

<h1>Размещение объявлений доступно только для пользователей из Узбекистана.</h1>

<?php endif; ?>
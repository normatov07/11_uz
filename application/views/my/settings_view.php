<div class="settings-resp">
<div class="w-400 form-block">
    <form name="main_form" id="profile_form" method="post" action="">
        <?php echo @$form_messages?>

        <div class="form-group">
            <label for="email">E-mail</label>
            <input type="text" name="email" id="email" class="form-control" maxlength="255" title="Электронная почта" value="<?=form::value(@$obj->email)?>" />
        </div>

        <div class="form-group">
            <label for="name">Имя</label>
            <input type="text" name="name" id="name" class="form-control" maxlength="255" title="Имя" value="<?=form::value(@$obj->name)?>" />
        </div>

        <div class="form-group">
            <label for="phone">Телефон</label>
            <input type="text" name="phone" id="phone" class="form-control" maxlength="255" title="Телефон" value="<?=form::value(format::phone(@$obj->phone))?>" />
        </div>

        <div class="form-group">
            <label for="city">Регион</label>
            <?=AppLib::getRegions(@$obj->region_id)?>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-lg btn-info" id="add_form_submit">Сохранить</button> <span id="ajaxstatus">Проверка данных...</span>
        </div>

        <div class="form-group">
            <a href="/my/password" class="btn btn-outline-info"> <i class="ion-md-lock"></i>  Изменить пароль</a>
        </div>

        <input type="hidden" name="reference_point" value="<?=form::value(@$obj->reference_point)?>">
        <input type="hidden" name="website" value="<?=form::value(@$obj->website)?>">
        <input type="hidden" name="address" value="<?=form::value(@$obj->address)?>">
        <input type="hidden" name="gender" value="unknown">
        <input type="hidden" name="secondary_email" value="">
        <input type="hidden" name="email_status" value="0">
        <input type="hidden" name="notifications" value="0">
        <input type="hidden" name="sms_notifications" value="0">
        <input type="hidden" name="link_to_other_offers" value="0">
    </form>


</div>
</div>
<div class="login-block">
    <?php echo @$form_messages?>
    <form name="lostpass_form" id="lostpass_form" method="post" action="/lostpass/">
        <div class="form-group">
            <label for="email">E-mail</label>
            <input name="email" type="text" title="E-mail" value="<?=form::value(@$obj->email)?>" class="form-control"/>
            <input name="captcha_code" type="hidden" id="captcha_code" value="99999" />
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-lg btn-info">Сменить пароль</button>

            <span id="ajaxstatus">Проверка данных...</span>
        </div>
    </form>
</div>
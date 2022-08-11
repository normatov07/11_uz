<div class="login-block">
    <?php echo @$form_messages?>

    <form name="register_form" id="register_form" method="post" action="/register/">

        <div class="form-group">
            <label for="name">Имя</label>
            <input name="name" type="text" id="name" maxlength="64" class="form-control" title="Имя" value="<?=form::value(@$obj->name)?>" />

        </div>

        <div class="form-group">
            <label for="email">E-mail</label>
            <input name="email" type="text" id="email" maxlength="64" class="form-control" title="E-mail" value="<?=form::value(@$obj->email)?>" />
        </div>


        <div class="form-group">
            <label for="password">Пароль</label>
            <input name="password" type="password" id="password" maxlength="64" title="Пароль" class="form-control" />
        </div>

        <div class="form-group">
            <label for="repassword">Повторите пароль</label>
            <input name="repeat_password" type="password" id="repeat_password" maxlength="64" title="Повторный пароль" class="form-control"/>
        </div>


        <div class="form-group">
            <button type="submit" class="btn btn-lg btn-info" id="register_button">Зарегистрироваться</button>
            <span id="ajaxstatus">Проверка данных...</span>
        </div>

        <input type="hidden" name="role" value="general">
        <input type="hidden" name="gender" value="unknown">
        <input type="hidden" name="accept_disclaimer" value="1">
    </form>
</div>

<div class="login-block">
    <?php echo @$form_messages?>
    <form name="login_form" id="login_form" method="post" action="/login/">
        <div class="form-group">
            <label for="email">E-mail</label>
            <input name="email" type="text" id="email" maxlength="128" title="E-mail" value="<?=form::value(@$obj->email)?>" class="form-control"/>
        </div>

        <div class="form-group">
            <label for="password">Пароль</label>
            <input name="password" type="password" id="password" maxlength="64" title="Пароль" value="" class="form-control"/>
        </div>

        <div class="form-group">
            <label for="remember_me"><input type="checkbox" name="remember_me" id="remember_me" value="1" checked="checked" class="ch" title="Запомнить меня" /> Запомнить меня</label>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-lg btn-info">Войти</button> <a href="/lostpass/" class="ml-2">Забыли пароль?</a>
        </div>
    </form>
</div>
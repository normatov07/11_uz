<div class="w-400 form-block">
    <form name="main_form" id="profile_form" method="post" action="">
        <?php echo @$form_messages?>


        <div class="form-group">
            <label for="">Текущий пароль</label>
            <input name="password" type="password" id="password" maxlength="64" title="Текущий пароль" class="form-control"/>
        </div>

        <div class="form-group">
            <label for="">Новый пароль</label>
            <input name="new_password" type="password" id="new_password" maxlength="64" title="Новый пароль" class="form-control"/>
        </div>

        <div class="form-group">
            <label for="">Повторите новый пароль</label>
            <input name="repeat_password" type="password" id="repeat_password" maxlength="64" title="Повтор нового пароля" class="form-control" />
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-lg btn-info" id="add_form_submit">Сохранить</button>  <span id="ajaxstatus">Проверка данных...</span>
        </div>
    </form>
</div>
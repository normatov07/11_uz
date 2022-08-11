<div class="settings-resp">
<div class="w-400 form-block">
    <form action="" method="post" id="quickcontact">
        <?php echo @$form_messages?>
        <div class="form-group">
            <label for="name">Имя</label>
            <input type="text" class="form-control" name="name" title="Имя" value="<?=form::value(@$obj['name'])?>">
        </div>

        <div class="form-group">
            <label for="email">E-mail</label>
            <input type="text" class="form-control" name="email" title="E-mail" value="<?=form::value(@$obj['email'])?>">
        </div>

        <div class="form-group">
            <label for="title">Тема</label>
            <input type="text" name="title" class="form-control" title="Тема" value="<?=form::value(@$obj['title'])?>">
        </div>

        <div class="form-group">
            <label for="message">Сообщение</label>
            <textarea name="message" class="form-control" title="Сообщение" cols="80" rows="8"><?=form::value(@$obj['message'])?></textarea>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-lg btn-info" name="send">Отправить</button> <span id="ajaxstatus">Проверка данных...</span>
        </div>

        <input type="hidden"  name="captcha_code" value="4444">
    </form>
</div>
</div>
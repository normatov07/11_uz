<div class="main main2 statistics">
    <h1>Баннеры</h1>

    <div style="background-color: #f5f5f5; padding: 1rem; margin-top: 2rem">
        <form enctype="multipart/form-data" method="POST">

            <div class="form-group">
                <label for="url">Ссылка</label>
                <input type="text" name="url" placeholder="http://" style="width: 400px">
            </div>

            <div class="form-group">
                <label for="banner">Банннер</label>
                <input name="banner" type="file" />
            </div>
            <div class="form-group">
                <button class="btn btn-info btn-lg">Загрузить</button>
            </div>
            <input type="hidden" name="task" value="upload">
        </form>
    </div>


    <div class="banners">
        <?php
        if($this->banners && is_array($this->banners) && count($this->banners)  > 0){
            foreach ($this->banners as $banner){
                ?>
                <div class="item">
                    <form method="post">
                        <div class="form-group">
                            <img src="/assets/img/slide/<?=$banner?>" alt="">

                        </div>

                        <div>
                            <button type="submit">Удалить</button>
                        </div>
                        <input type="hidden" name="photo" value="<?=$banner?>">
                        <input type="hidden" name="task" value="delete">
                    </form>
                </div>
                <?php
            }
        }
        ?>
    </div>
</div>

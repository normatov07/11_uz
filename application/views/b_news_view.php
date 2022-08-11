<?php
    $publishedList = ORM::factory('news_entry')->findPublished(3);
    if($publishedList && $publishedList->count() > 0){
        ?>
        <div class="block bnews">
            <h2><a href="/news/">Новости</a></h2>
            <?php
                foreach($publishedList as $i => $entry){
                    ?>
                    <div class="bitem">
                        <h3><a href="<?=$entry->url?>"><?=$entry->title?></a></h3>
                        <?=$entry->description?>
                    </div>
                <?php
                }
            ?>
        </div>
        <?php
    }
?>  
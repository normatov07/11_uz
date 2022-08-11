<?php
$this->cache = Cache::instance();
$crumbs_id = 'crumbs_for_cat_' . $obj->id;
$cached_crumbs = $this->cache->get($crumbs_id);

if (empty($cached_crumbs)):
    ob_start();
?>
<div class="breadcrumb">
   <a href="/" class="text-muted">Главная</a>
<?php
foreach($obj->parents as $item):?>
    <a href="<?=$item->url?>" class="text-muted"> » <span><?=$item->stitle?></span></a>
     
<?php	endforeach;?>
 </div>
<?php

    $cached_crumbs = trim(ob_get_clean());
    $this->cache->set($crumbs_id, $cached_crumbs, null, 600);
endif;
echo $cached_crumbs;

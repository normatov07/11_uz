<?php

if(!empty($types) and $types->count()):

    $waspremium = false;

    foreach($types as $type):
        if(!$type->on_home) continue;
        if(!empty($offerList[$type->id]) and $offerList[$type->id]->count()):?>
            <div class="typetitle corns"><i class="ct"><i></i><b></b></i>
                <h1><a href="<?=$type->url?>"><?=!empty($type->other_title)?$type->other_title:$type->title?> </a></h1>
            </div>

            <?php		foreach($offerList[$type->id] as $offer):
                ?>
                <div class="ofr<?php
                if($offer->is_premium):
                    echo '  premium';
                    $waspremium = true;
                else:
                    if($offer->is_marked) echo ' corns or';
                    else echo ' ';
                    if($waspremium) echo ' tmar';
                    $waspremium = false;

                endif;?>">
                    <table>
                        <tr>
                            <td class="p">
                                <a href="<?=$offer->url?>">
                                    <?php if(isset($pictures[$offer->id])){ ?>
                                        <div class='offer-photo text-center' style='background-image: url("<?=$pictures[$offer->id]->f('thumb')?>")'></div>

                                    <?php } else { ?>
                                        <div class="no-photo text-center"></div>
                                    <?php } ?>
                                </a>
                            </td>
                            <td class="d"><h3><a href="<?=$offer->url?>"></a></h3>

                                <p><?=$offer->short_description?></p>
                            </td>
                            <td class="i">

                                <div class="t"><?=date::getRelativeDatetime($offer->added, true)?></div>
                            </td>
                        </tr>
                    </table>
                </div>
            <?php		endforeach;?>
            <div class="list_all"><a href="<?=$type->url?>">Все объявления этого типа →</a></div>
        <?php	endif;
    endforeach;
endif?>
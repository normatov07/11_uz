<?php
    $catid = $obj->parent_id ? $obj->parent_id : $obj->id;
    $max_quick_filter_cols = 5;
    $bread_crumbs = new View('bread_crumbs', array('obj' => $obj));

    $this->cache = Cache::instance();
    $cacheCatlist = $this->cache->get('cacheCatlist_'.$catid);

    if(empty($cacheCatlist)){
        $i = 0;
        $divider = ceil($categoriesList->count()/3);
        $cacheCatlist = '';

        foreach($categoriesList as $item){
            if($i > 0 and !($i%$divider)) { $cacheCatlist .= '</ul><ul>'; }
            $cacheCatlist .= '<li><a href="'.$item->url.'"';
            if($obj->id == $item->id) $cacheCatlist .= ' class="this"';
            if(!empty($item->color)) $cacheCatlist .= ' style="color:#'.$item->color.'"';
            $cacheCatlist .= '>'.$item->title.'</a> '.$item->getOffersCount(false).'</li>';
            $i++;
        }

        $this->cache->set('cacheCatlist_'.$obj->id, $cacheCatlist, array('ccl'), 600);
    }

    $filterscount = count($filters);

    if($filterscount){
        if(is_array($quickfilters) && !empty($quickfilters)){
            $quickcount = count($quickfilters);
        } else {
            $quickfilters = array();
            $quickcount = 0;
        }

        $filterHTML = '';
        $filterEnabled = false;

        foreach($filters as $filter){


            if(!$filter->list->isfilter){
                $filterscount--;
                continue;
            }

            if($filter->isquicklist){
                $quickfilters[$quickcount]['title'] = $filter->title;
                $quickfilters[$quickcount]['items'] = $filter->list->list_items->select_list('valuedata','title');
                $quickfilters[$quickcount]['name'] = 'filter['.$filter->id.']';

                if(!empty($_REQUEST['filter'][$filter->id])) $quickfilters[$quickcount]['this'] = $_REQUEST['filter'][$filter->id];

                $quickcount++;
            }

            $filterHTML .= '<li><label for="f'.$filter->id.'">'.$filter->title.'</label>';
            if(!empty($_REQUEST['filter'][$filter->id])) $filterEnabled = true;


            if($filter && property_exists($filter, 'id')){
                $filterHTML .= $filter->formField($_REQUEST['filter'][$filter->id], 'filter');
            }

            $filterHTML .= '</li>';
        }
    }
?>

<?=$bread_crumbs?>

<h1><?=$obj->title?></h1>


    <div class="category">
        <div class="catalogue">
            <ul><?=$cacheCatlist?></ul>
            <div class="clb"></div>
        </div>

        <?php
            if(!empty($quickfilters) and count($quickfilters)){
                foreach($quickfilters as $id => $filter){
                    $thisFilterEnabled = false;

                    if($catid == 108 || $catid == 61){
                        $divider = ceil(count($filter['items'])/3);
                        if($divider) $real_columns_count = ceil(count($filter['items']) / $divider);
                    } else {
                        $divider = ceil(count($filter['items'])/$max_quick_filter_cols);
                        if($divider) $real_columns_count = ceil(count($filter['items']) / $divider);
                    }
                    ?>
                    <div class="quick_filter <?php if($max_quick_filter_cols > $real_columns_count) echo '  qf'. $real_columns_count?>">
                        <h4><?=$filter['title']?>:</h4>

                        <ul>
                            <?php				$i = 0;
                            foreach($filter['items'] as $key => $item):

                            if($i > 0 and !($i%$divider)):?>
                        </ul>
                        <ul>
                            <?php					endif;?>
                            <li><a href="?<?=$filter['name']?>=<?=urlencode($key)?>"<?php
                                if(!empty($filter['this']) and $filter['this'] == $key):
                                    echo ' class="this"';
                                    $thisFilterEnabled = true;
                                    $filterEnabled = true;
                                endif;?>><?=$item?></a></li>
                            <?php					$i++;
                            endforeach;?>
                        </ul>

                        <?php		if(!empty($thisFilterEnabled)):?>
                            <div class="clb"></div>
                            <div class="disableQF"><a href="<?=$obj->url . (!empty($filter['reset_url'])?$filter['reset_url']:'')?>">Показать все</a></div>
                        <?php		endif;?>
                    </div>
                    <?php
                }
            }
        ?>

        <form name="filters" id="filters" action="<?=$obj->url?>">
            <?php  if($filterscount > 0){ ?>
                <ul class="filters bcorns">
                    <?php	if(!empty($districts)):
                        $district_id = array_key_exists('district_id', $_REQUEST) ? $_REQUEST['district_id'] : false;
                        ?>
                        <li class="selectdistrict">
                            <label><?=$district_type?>:</label>
                            <?=form::dropdown(
                                    array(
                                        'name'=>'district_id',
                                        'id'=>'district_id',
                                        'title'=>$district_type,
                                        'class'=>'nodis'
                                    ),
                                    array(''=>'не выбран') + $districts, $district_id)?>
                        </li>
                        <?php
                        $regiondel = true;
                    endif;?>


                    <?php	if(!empty($subways)):

                        $subway_id = array_key_exists('subway_id', $_REQUEST) ? $_REQUEST['subway_id'] : false;
                        ?>
                        <li class="selectsubway">
                            <label>Станции метро:</label>
                            <?=form::dropdown(array('name'=>'subway_id', 'id'=>'subway_id', 'title'=>'Станции метро', 'class'=>'nodis'), array(''=>'не выбран') + $subways, $subway_id)?>
                        </li>
                        <?php		$regiondel = true;
                    endif;?>

                    <?php	if($filterscount > 0):?>

                        <?php
                            echo  $filterHTML;
                        ?>

                    <?php	endif;?>

                    <?php		if(count($categorytypes) > 1):?>
                        <div class="select selecttype">
                            <label>Тип объявлений:</label>
                            <?= AppLib::getTypes(array('category' => $obj, 'class'=>'nodis', 'nodis' => true, 'distitle' => 'любой'), $_REQUEST['type_id']);?>
                        </div>
                    <?php	endif?>
                    <div class="select selectregion">
                        <label><b class="g">Регион:</b></label>
                        <?php
                            $region_id = array_key_exists('region_id', $_REQUEST) ? $region_id : false;
                        ?>
                        <?=form::dropdown(array('name'=>'region_id', 'id'=>'region_id', 'title'=>'Регион', 'class'=>'nodis' . ($obj->has_district?' wdistrict':'').($obj->has_subway?' wsubway':'')), array(''=>'не выбран') + $regions, $region_id? $region_id:'')?>
                    </div>
                    <div class="select selectprice">
                        <label><b>Цена:</b></label>
                        от <input type="text" name="price_from" value="<?=form::value($_REQUEST['price_from'])?>" />
                        до <input type="text" name="price_to" value="<?=form::value($_REQUEST['price_to'])?>" />
                        <?=form::dropdown(array('name'=>'currency', 'id'=>'currency'), Lib::config('payment.currency_list'),  $_REQUEST['currency']);?>
                    </div>

                    <div class="but"><input type="submit" value="Показать →"  /></div>
                    <div></div>

                    <?php	if(!empty($filterEnabled)):?>
                        <div class="clb"></div>
                        <div class="disableF"><a href="<?=$obj->url?>">Отменить все параметры поиска</a></div>
                    <?php	endif;?>
                </ul>


                <div class="cb"></div>
            <?php } ?>
        </form>


        <?php	if(!empty($offerList)):?>

            <div class="offerlist">
            <?php

            $premiumset = false;
            $simpleisset = false;

            $oi=0;	// переменная для вывода баннера между объявлениями

            foreach($offerList as $offer):

                $oi++;
                // вставляем баннер между объявлениями
                if ($oi == 9) :	?>


                    <div class="banner">


                    </div>
                <?php endif;

                if(!$premiumset and $offer->is_premium):?>
                    <div class="premiumline">
                    </div>
                    <?php			$premiumset = true;

                elseif(!$simpleisset and !$offer->is_premium):?>

                    <?php			if($premiumset):?>
                        </div>
                        <div class="offerlist">
                    <?php			endif;

                    $simpleisset = true;

                endif;?>
                <div id="o<?=$offer->id?>" class="ofr<?php

                if($offer->is_premium):
                    //echo ' premium';
                else:
                    if($offer->is_marked) echo ' corns or';
                    else echo '';

                endif;?>">
                    <table>
                        <tr>
                            <td class="p">
                                <a href="<?=$offer->url?>">
                                    <?php if(isset($pictures[$offer->id])){ ?>
                                        <?php
                                        if($offer->is_premium){
                                            ?>
                                            <span class="inlblk icon paid  abs zi2"></span>
                                            <?php
                                        }
                                        ?>

                                        <div class='offer-photo text-center' style='background-image: url("<?=$pictures[$offer->id]->f('thumb')?>")'></div>
                                    <?php } else { ?>
                                        <div class="no-photo text-center"></div>
                                    <?php } ?>
                                </a>
                            </td>
                            <td class="d">
                                <h3><a href="<?=$offer->url?>"><?=$offer->title?></a></h3>
                                <?=$offer->price_html_list?>
                                <p><?=$offer->short_description?></p>
                            </td>
                            <td class="i">

                                <div class="c"><?=$regions[$offer->region_id]?></div>
                                <?php		if(empty($category_has_no_children)):?>
                                    <div class="c"><a href="<?=$categories[$offer->category_id]->url?>"><?=$categories[$offer->category_id]->title?></a></div>
                                <?php		endif;?>
                                <div class="t"><?=date::getRelativeDatetime($offer->added, true)?></div>
                            </td>

                            <?php if(strlen($offer->act_icons) > 0){ ?>
                                    <td class="m"><?=$offer->act_icons?></td>
                            <?php } ?>
                        </tr>
                    </table>
                </div>
            <?php	endforeach;	?>
            </div>

            <?php
            if(count($offerList) > 10){
                ?>

                <div class="lcol category">
                <div class="catalogue">
                    <ul>
                        <?php
                        echo $cacheCatlist;
                        ?>
                    </ul>
                </div>
                </div><?php
            }
            ?>


            <div class="type_filter type_filter_bot oneline"><?=$type_filter?></div>
            <?php	echo $pagination;
        else: // offers count?>
            <div class="text-muted text-center empty-state">
               Объявлений не найдено
            </div>
            <?php
        endif; // offers count?>
    </div>

    <?php if(!$printMode):?>
        <div class="rcol">
            <?= new View('b_right_banner_view', array('category_parent_codename'=>$category_parent_codename))?>
            <?= new View('b_right_menu_view')?>
            <?php if(!empty($rolist)) echo $rolist;?>
            <div id="ya_direct" class="block"></div>
            <?= new View('b_types_view')?>
        </div>
    <?php endif;?>
    <div id="modalBox" class="jqmWindow"><a href="#" class="jqmClose x">закрыть</a><div class="jqmContent"></div></div>


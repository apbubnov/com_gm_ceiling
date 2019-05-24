<?php
    $jinput = JFactory::getApplication()->input;
    $type = $jinput->get('type', '', 'STRING');
    $subtype = $jinput->get('subtype', '', 'STRING');
    if(empty($user)){
        $user = JFactory::getUser();
    }
    $user_groups = $user->groups;
    if(in_array('16',$user_groups)){
        $is_gmmanager = true;
    }
    if(in_array('17',$user_groups)){
        $isNMS = true;
    }
    $isBuilder = (JFactory::getUser($this->item->dealer_id)->dealer_type == 7);//проект застройщика или нет
    $needShow = !in_array($this->item->project_status,VERDICT_STATUSES) || $isBuilder;
    $displayNone = (in_array($this->project_status,VERDICT_STATUSES) && !$isBuilder)?  "style=\"display:none;\"" : "";//скрыть элемент
    if(!empty($this->item->calcs_mounting_sum)){
        $service_mount = true ;
    }
    else{
        foreach ($calculations as $calculation){
            if($calculation->need_mount == 2){
                $service_mount = true;
                break;
            }
        }
    }

?>
<style>
    .center-left {
        width: 100%;
        text-align: center;
        margin-bottom: 15px;
    }
    .calculation_sum {
        width: 100%;
        margin-bottom: 25px;
    }
    .calculation_sum td {
        padding: 0 5px;
    }
    #table1 {
        width: 100%;
        font-size: 13px;
    }
    #table1 button, #table1 a, #table1 input {
        font-size: 13px;
        max-width: 150px;
    }
    #table1 td, #table1 th {
        padding: 10px 5px;
    }
    #table1 tr
    {
        border-bottom: 1px solid #414099;
        border-top: 1px solid #414099;
    }
    #table1 td
    {
        cursor: default;
    }
    .wtf_padding {
        padding: 0;
    }
    .no_yes_padding {
        padding: 0;
    }
    #calendar1, #calendar2 {
        display: inline-block;
        width: 100%;
        padding: 0;
    }
    #container_calendars {
        width: 100%;
    }
    #button-prev, #button-next {
        padding: 0;
    }
    #calcs_total_border {
        display: inline-block;
        width: auto;
        padding: 3px 7px;
        border: 2px solid #414099;
    }

    .div_imgs {
        overflow-x: auto;
        white-space: nowrap;
    }
    .uploaded_calc_img {
        display: inline-block;
        max-width: 200px;
        padding: 2px 10px;
        cursor: pointer;
    }
    .uploaded_calc_img:hover {
        background: gray;
    }
    .big_uploaded_img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    #modal_window_img {
        width: 800px !important;
        height: 600px !important;
        margin: auto !important;
    }
    #modal_window_img {
        width: 360px !important;
        height: 400px !important;
        margin: auto !important;
    }
    #btn_del_img {
        display: none;
        position: fixed;
        top: 20px;
        left: 30px;
        cursor: pointer;
    }
    #btn_close_img {
        right: 0px;
    }

    @media screen and (min-width: 768px) {
        #btn_close_img {
            right: 30px;
        }
        #modal_window_img {
            width: 800px !important;
            height: 600px !important;
            margin: auto !important;
        }
        .center-left {
            text-align: left;
        }
        #table1 {
            width: 100%;
            max-width: 3000px;
            font-size: 1em;
        }
        #table1 td, #table1 th {
            padding: 15px;
        }
        #table1 button, #table1 a, #table1 input {
            font-size: 1em;
            width: auto;
            max-width: 200px;
        }
        .wtf_padding {
            padding: 15px;
        }
        .no_yes_padding {
            padding: 15px;
        }
        #calendar1, #calendar2 {
            width: calc(50% - 25px);
        }
        #calendar2 {
            margin-left: 30px;
        }
    }
</style>

<div class="modal_window_container" id="img_modal_container">
    <button type="button" class="close_btn" id="btn_close_img"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <button type="button" class="close_btn" id="btn_del_img"><i class="fa fa-trash" aria-hidden="true"></i> Удалить изображение</button>
    <div class="modal_window" id="modal_window_img" style="border: 2px solid black; border-radius: 4px;"></div>
</div>

<div class="row">
    <div class="col-md-3 no_padding"><b>Ввести общее примечание к проекту:</b></div>
    <div class="col-md-6 col-xs-9 no_padding">
        <textarea class="inputactive" id="textarea_note" style="width: 98%;"></textarea>
    </div>
    <div class="col-md-3 col-xs-3 no_padding">
        <button type="button" class="btn btn-primary" id="btn_add_note">Ок</button>
    </div>
</div>
<div class="row">
    <div class="col-xs-12 no_padding">
        <h4>Расчеты для проекта</h4>
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#summary" role="tab">Общее</a>
            </li>
            <?php foreach ($calculations as $k => $calculation) { ?>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#calculation<?php echo $calculation->id; ?>" role="tab">
                        <?php echo $calculation->calculation_title; ?>
                    </a>
                </li>
            <?php } ?>
            <?php if($needShow){?>
                <li class="nav-item"> 
                    <button type="button" class="nav-link" id="add_calc" style="color:white;">
                        Добавить потолок <i class="fa fa-plus-square-o" aria-hidden="true"></i>
                    </button>
                </li>
            <?php }?>
        </ul>
        <?php if($user->dealer_type == 1 && count($calculations) <= 0) { ?>
            <p>У Вас еще нет потолков</p>
        <?php } else { ?>
            <!-- Tab panes -->
            <div class="tab-content">
                <div class="tab-pane active" id="summary" role="tabpanel">
                    <table id="table1">
                        <tr style="background-color: rgba(0,0,0,0.15);">
                            <th colspan="3"111 class="section_header" id="sh_ceilings">
                                Потолки <i class="fa fa-sort-desc" aria-hidden="true" style="cursor: pointer;"></i>
                            </th>
                        </tr>
                        <?php 
                            foreach ($calculations as $calculation) {
                        ?>
                            <tr class="section_ceilings" style="background-color: rgba(0,0,0,0.05);">
                                <td colspan="3" class="include_calculation" >
                                    <input name='include_calculation[]' value='<?php echo $calculation->id; ?>' type='checkbox' checked="checked" <?php echo $displayNone;?> style="cursor: pointer;">
                                    <input name='calculation_total[<?php echo $calculation->id; ?>]' value='<?php echo $calculation->calculation_total; ?>' type='hidden'>
                                    <input name='calculation_total_discount[<?php echo $calculation->id; ?>]' value='<?php echo $calculation->calculation_total_discount; ?>' type='hidden'>
                                    <input name='total_square[<?php echo $calculation->id; ?>]' value='<?php echo $calculation->n4; ?>' type='hidden'>
                                    <input name='total_perimeter[<?php echo $calculation->id; ?>]' value='<?php echo $calculation->n5; ?>' type='hidden'>      
                                    <span><i><b><?php echo $calculation->calculation_title; ?></b></i></span>
                                </td>
                                <?php if($is_gmmanager){ ?>
                                    <td colspan="2">
                                        <?php $path = "/costsheets/".md5($calculation->id."cutpdf").".pdf"; ?>
                                        <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                            <a href="<?php echo $path; ?>" class="btn btn-secondary"
                                               target="_blank">Посмотреть раскрой</a>
                                        <?php } else { ?>
                                            -
                                        <?php } ?>
                                    </td>
                                <?php }?>
                            </tr>
                            <tr class="section_ceilings" style="background-color: rgba(0,0,0,0.0);">
                                <td>S/P :</td>
                                <td colspan="2">
                                    <?php echo $calculation->n4; ?> м<sup>2</sup> / <?php echo $calculation->n5; ?> м
                                </td>
                            </tr>
                            <tr class="section_ceilings" style="background-color: rgba(0,0,0,0.0);">
                                <?php if ($calculation->discount != 0) { ?>
                                    <td>Цена / -<?php echo $calculation->discount ?>% :</td>
                                    <td id="calculation_total"> <?php echo round($calculation->calculation_total, 0); ?> р. /</td>
                                    <td id="calculation_total_discount"> <?php echo round($calculation->calculation_total_discount , 0); ?>
                                        р.
                                    </td>
                                <?php } else { ?>
                                    <td>Итого</td>
                                    <td colspan="2" id="calculation_total"> <?php echo round($calculation->calculation_total, 0); ?> р.</td>
                                <?php } ?>
                            </tr>
                        <?php
                                if ($calculation->discount > 0) {
                                    $kol++;
                                }
                            } 
                        ?>
                        <tr style="background-color: rgba(0,0,0,0.05);">
                            <th>Общая S/общий P :</th>
                            <th id="total_square">
                                <span class = "sum"><?php echo round($total_square,2);?></span> м<sup>2</sup> /
                            </th>
                            <th id="total_perimeter">
                                <span class = "sum"><?php echo  round($total_perimeter,2); ?></span> м
                            </th>
                        </tr>
                        <tr style="background-color: rgba(0,0,0,0.15);">
                            <th colspan="3">Транспортные расходы</th>
                        </tr>
                        <tr <?php echo $displayNone;?>>
                            <td colspan="3">
                                <p>
                                    <input name="transport" class="radio" id ="transport" value="1" type="radio" <?php if($this->item->transport == 1 ) echo "checked"?>>
                                    <label for = "transport">Транспорт по городу</label>
                                </p>
                                <div class="row sm-margin-bottom" style="width: 45%; display:none;" id="transport_dist_col">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <div class="advanced_col1" style="width: 35%;">
                                                <label>Кол-во выездов</label>
                                            </div>
                                            <div class="advanced_col2" style="width: 20%;"></div>
                                        </div>
                                        <div class="form-group">
                                            <div class="advanced_col1" style="width: 35%;">
                                                <input name="jform[distance_col_1]" id="distance_col_1" style="width: 100%;" value="<?php echo $this->item->distance_col; ?>" class="form-control" placeholder="раз" type="tel">
                                            </div>
                                            <div class="advanced_col2" style="width: 20%;">
                                                <button type="button" name="click_transport" class="btn btn-primary">Ок</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p>
                                    <input name="transport" class="radio" id = "distanceId" value="2" type="radio" <?php if( $this->item->transport == 2) echo "checked"?>>
                                    <label for = "distanceId">Выезд за город</label>
                                </p>
                                <div class="row sm-margin-bottom" style="width: 45%; display:none;" id="transport_dist" >
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <div class="advanced_col1" style="width: 35%;">
                                                <label>Кол-во,км</label>
                                            </div>
                                            <div class="advanced_col2" style="width: 35%;">
                                                <label>Кол-во выездов</label>
                                            </div>
                                            <div class="advanced_col3" style="width: 20%;"></div>
                                        </div>
                                        <div class="form-group">
                                            <div class="advanced_col1" style="width: 35%;">
                                                <input name="jform[distance]" id="distance" style="width: 100%;" value="<?php echo $this->item->distance; ?>" class="form-control" placeholder="км." type="tel">
                                            </div>
                                            <div class="advanced_col2" style="width: 35%;">
                                                <input name="jform[distance_col]" id="distance_col" style="width: 100%;" value="<?php echo $this->item->distance_col; ?>" class="form-control" placeholder="раз" type="tel">
                                            </div>
                                            <div class="advanced_col3" style="width: 20%;">
                                                <button type="button" name="click_transport" class="btn btn-primary">Ок</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p>
                                    <input name="transport" class="radio" id ="no_transport" value="0" type="radio" <?php if($this->item->transport == 0 ) echo "checked"?>>
                                    <label for="no_transport">Без транспорта</label>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td>Транспорт</td>
                            <td colspan="2" id="transport_sum">
                                <span class="sum" data-selfval = <?php echo $self_sum_transport ?>><?=$client_sum_transport;?></span> р.
                            </td>
                            <!-- <input id="transport_suma" value='<?php //echo $client_sum_transport; ?>' type='hidden'> -->
                        </tr>
                        <tr style="background-color: rgba(0,0,0,0.15);">
                            <?php if ($kol > 0) { ?>
                                <th>Итого/ - %:</th>
                                <th id="project_total"><span class="sum">
                                    <?php echo round($project_total, 0); ?></span> р. /
                                </th>
                                <th colspan="2" id="project_total_discount">
                                    <?php
                                        //---------------  Если сумма проекта меньше 3500, то делаем сумму проекта 3500  -----------------------
                                        $old_price = $project_total_discount;
                                        if ($dealer_canvases_sum == 0 && $project_total_discount < $min_components_sum) {
                                            $project_total_discount = $min_components_sum;
                                        } elseif ($dealer_gm_mounting_sum_11 == 0 && $project_total_discount < $min_components_sum) {
                                            $project_total_discount = $min_components_sum;
                                        } elseif ($project_total_discount <  $min_project_sum && $project_total_discount > 0) {
                                            $project_total_discount =  $min_project_sum;
                                        }
                                    ?>
                                    <span class="sum"><?= round($project_total_discount, 0);?></span> р.
                                    <?php if($old_price != $project_total_discount): ?>
                                        <span class="dop" style="font-size: 9px;" > * минимальная сумма заказа <?php echo $min_project_sum;?>. </span>
                                    <?php endif; ?>
                                </th>
                            <?php } else { ?>
                                <th>Итого</th>
                                <th id="project_total" colspan="2">
                                    <?php
                                        //---------------  Если сумма проекта меньше 3500, то делаем сумму проекта 3500  -----------------------
                                        $old_price = $project_total;
                                        if ($dealer_canvases_sum == 0 && $project_total < $min_components_sum) {
                                            $project_total = $min_components_sum;
                                        } elseif ($dealer_gm_mounting_sum_11 == 0 && $project_total < $min_components_sum) {
                                            $project_total = $min_components_sum;
                                        } elseif ($project_total <  $min_project_sum && $project_total > 0) {
                                            $project_total =  $min_project_sum;
                                        }
                                    ?>
                                    <span class="sum"><?= round($project_total, 0);?> </span>р.
                                    <?php if($old_price != $project_total): ?>
                                        <span class="dop" style="font-size: 9px;" > * минимальная сумма заказа <?php echo $min_project_sum;?>. </span>
                                    <?endif;?>
                                </th>
                            <?php } ?>
                        </tr>
                        <tr style="background-color: rgba(0,0,0,0.15);">
                            <th colspan="3">
                                Предоплата
                            </th>
                        </tr>
                        <tr>
                            <td>
                                Всего внесено
                            </td>
                            <td colspan="2">
                                <div class="row">
                                    <div class="col-md-3">
                                        <span id="prepayment_total" style="vertical-align: middle;"><?php echo $this->item->prepayment_total;?></span>руб.
                                    </div>
                                    <div class="col-md-3">
                                        <button id="show_detailed_prepayment" type="button" class="btn btn-primary" style="padding-right: 6px;padding-left: 6px;">Посмотреть детально</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr id="detailed_tr" style="display: none;">
                            <td id="detailed_td" colspan="3"></td>
                        </tr>
                        <tr>
                            <td>
                                Внесение
                            </td>
                            <td colspan="2">
                                <div class="row">
                                    <div class="col-md-3 col-xs-9" style="padding-left: 0;padding-right: 0;">
                                        <input class="input-gm" id="prepayment" style="vertical-align: middle;">
                                    </div>
                                    <div class="col-md-3 col-xs-3" style="padding-left: 0;padding-right: 0;">
                                        <button id="prepayment_save" class="btn btn-primary btn-sm"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <?php if ($user->dealer_type != 2) { ?>
                            <tr style="background-color: rgba(0,0,0,0.05);">
                                <td id="calcs_self_canvases_total"><span>П </span> <span class = "sum"><?php echo round($self_canvases_sum, 0) ?></span></td>
                                <td id="calcs_self_components_total"><span>К </span><span data-oldval="<?php echo round($self_components_sum, 0) ?>" class="sum"><?php echo round($self_components_sum, 0) ?></span></td>
                                <td id="calcs_self_mount_total">
                                    <div class="col-md-6 col-xs-6">
                                        <?php if(!$isNMS){?>
                                        <span>М </span><span class = "sum"><?php echo round($self_mounting_sum+$self_sum_transport, 0); ?></span>
                                        <?php } else{?>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <span>МС </span><span class = "sum"><?php echo round($self_mounting_sum+$self_sum_transport, 0); ?></span>
                                                </div>
                                                <div class="col-md-6">
                                                    <span>МД </span><span class = "sum"><?php echo round($total_dealer_mount+$self_sum_transport, 0); ?></span>
                                                </div>
                                            </div>
                                        <?php }?>
                                    </div>
                                    <div class="col-md-6 col-xs-6">
                                        <div id="calcs_total_border"><?php echo round($project_self_total  , 0); ?></div>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr style="background-color: rgba(0,0,0,0.15);">
                            <th colspan="3" id="duplicate_calcs">Дублировать потолки</th>
                        </tr>
                        <tr id="duplicate_tr" style="display:none">
                            <td colspan="3">
                                <?php foreach ($calculations as $calculation) { ?>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="checkbox" id="<?php echo $calculation->id?>" data-calc_id = "<?php echo $calculation->id?>" class="inp-cbx dup" checked style="display: none">
                                                <label for="<?php echo $calculation->id?>" class="cbx">
                                                    <span>
                                                        <svg width="12px" height="10px" viewBox="0 0 12 10">
                                                            <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                                        </svg>
                                                    </span>
                                                    <span><?php echo $calculation->calculation_title;?></span>
                                                </label>
                                            </div>
                                        </div>
                                <?php } ?>
                                <div class="row">
                                    <div class="col-md-3 col-xs-6">
                                        <button class="btn btn-primary btn_duplicate" type = "button" data-need_new = "false">Дублировать в<br> текущий проект</button>
                                    </div>
                                    <div class="col-md-3 col-xs-6">
                                        <button class="btn btn-primary btn_duplicate" type="button" data-need_new = "true">Дублировать в<br> новый проект</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr style="background-color: rgba(0,0,0,0.15);">
                            <th colspan="2" class="section_header" id="sh_estimate">Сметы, наряды на монтаж <i class="fa fa-sort-desc" aria-hidden="true" style="cursor: pointer;"></i></th>
                            <th colspan="1" class="section_header">
                                <button class = "btn btn-primary" type="button" id = "create_pdfs">Перегенерировать</button>
                            </th>
                        </tr>
                        <tr style="background-color: rgba(0,0,0,0.05);">
                            <th colspan="3" class="section_estimate" style="display: none;">Сметы:</th>
                        </tr>
                        <?php foreach ($calculations as $calculation) { ?>
                            <tr class="section_estimate" id="section_estimate_<?= $calculation->id; ?>" style="display:none;">
                                <?php
                                    $path = "/costsheets/" . md5($calculation->id . "client_single") . ".pdf";
                                    $pdf_names[] = array("name" => $calculation->calculation_title, "filename" => md5($calculation->id . "client_single") . ".pdf", "id" => $calculation->id);
                                ?>
                                <td>
                                    <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                        <input name='include_pdf[]' value='<?php echo $path; ?>' data-name='Смета <?php echo $calculation->calculation_title; ?>' type='checkbox' checked="checked" style="cursor: pointer;">
                                    <?php } ?>
                                    <?php echo $calculation->calculation_title; ?>
                                </td>
                                <td colspan="2">
                                    <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                        <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                                    <?php } else { ?>
                                        -
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php
                            }
                            $json = json_encode($pdf_names);
                        ?>

                        <?php
                            if (is_array($this->item->mount_data)) {
                                $mount_data = $this->item->mount_data;
                            }
                            else {
                                $mount_data = json_decode(htmlspecialchars_decode($this->item->mount_data));
                            }
                        ?>

                        <?php if (($user->dealer_type == 1 && $user->dealer_mounters == 0) || $user->dealer_type != 1) { ?>
                            <tr style="background-color: rgba(0,0,0,0.05);">
                                <th class="section_estimate" style="display: none;" colspan="3">Наряды на монтаж:</th>
                            </tr>
                            <?php foreach ($calculations as $calculation) { ?>
                                <tr class="section_estimate" id="section_mount_<?= $calculation->id; ?>" style="display:none;">
                                    <?php
                                        $filename = ($isNMS) ? "mount_single_gm" : "mount_single_dealer";
                                        $path = "/costsheets/" . md5($calculation->id . $filename) . ".pdf";
                                        $pdf_names_mount[] = array("name" => $calculation->calculation_title, "filename" => md5($calculation->id . "mount_single_dealer") . ".pdf", "id" => $calculation->id);
                                    ?>
                                    <td>
                                        <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                            <input name='include_pdf[]' value='<?php echo $path; ?>' data-name='<?php echo $calculation->calculation_title; ?> Наряд на монтаж' type='checkbox' checked="checked" style="cursor: pointer;">
                                        <?php } ?>
                                        <?php echo $calculation->calculation_title; ?>
                                    </td>
                                    <td colspan="2">
                                    <?php
                                    if (count($mount_data) === 0 || (count($mount_data) === 1 && $mount_data[0]->stage == 1)) {
                                        if (file_exists($_SERVER['DOCUMENT_ROOT'].$path)) {
                                            echo '<a href="'.$path.'" class="btn btn-secondary" target="_blank">Посмотреть</a>';
                                        } else {
                                            echo 'После договора';
                                        } 
                                    }
                                    else {
                                        foreach ($mount_data as $value) {
                                            $filename = ($isNMS) ?'mount_stage_gm':'mount_stage_dealer';
                                            $path = "/costsheets/" . md5($calculation->id.$filename.$value->stage).'.pdf';
                                            if (file_exists($_SERVER['DOCUMENT_ROOT'].$path)) {
                                                switch ($value->stage) {
                                                    case 2:
                                                        echo '<a href="'.$path.'" class="btn btn-secondary" target="_blank">Обагечивание</a>';
                                                        break;
                                                    case 3:
                                                        echo '<a href="'.$path.'" class="btn btn-secondary" target="_blank">Натяжка</a>';
                                                        break;
                                                    case 4:
                                                        echo '<a href="'.$path.'" class="btn btn-secondary" target="_blank">Вставка</a>';
                                                        break;
                                                    default:
                                                        break;
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                    </td>
                                </tr>
                            <?php
                                }
                                $json1 = json_encode($pdf_names_mount);
                            ?>
                        <?php } ?>
                        <!--------------- Общая смета для клиента -------------->
                        <tr class="section_estimate" style="display: none;">
                            <?php $path = "/costsheets/" . md5($this->item->id . "client_common") . ".pdf"; ?>
                            <td>
                                <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                    <input name='include_pdf[]' value='<?php echo $path; ?>' data-name='Общая смета' type='checkbox' checked="checked" style="cursor: pointer;">
                                    <b>Общая смета<b>
                                <?php } ?>
                            </td>
                            <td colspan="2">
                                <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                    <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank" id = "show">Посмотреть</a>
                                <?php } else { ?>
                                    <span data-href="<?=$path;?>">-
                                <?php } ?>
                            </td>
                        </tr>
                        <!-- общий наряд на монтаж--> 
                        <?php if (($user->dealer_type == 1 && $user->dealer_mounters == 0) || $user->dealer_type != 1) { ?>
                            <tr class="section_estimate" style="display: none;">
                                <?php
                                    $filename = ($isNMS)?"mount_common_gm": "mount_common_dealer";
                                    $path = "/costsheets/" . md5($this->item->id . $filename) . ".pdf";
                                 ?>
                                <td>
                                    <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                        <input name='include_pdf[]' value='<?php echo $path; ?>' data-name='Общий наряд на монтаж' type='checkbox' checked="checked" style="cursor: pointer;">
                                        <b>Общий наряд на монтаж <b>
                                    <?php } ?>
                                </td>
                                <td colspan="2">
                                    <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                        <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                                    <?php } else { ?>
                                        -
                                    <?php }
                                        $pdf_names[] = array("name" => "Подробная смета", "filename" => md5($this->item->id . "mount_common_dealer") . ".pdf", "id" => $this->item->id);
                                        $json2 = json_encode($pdf_names);
                                    ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php $path = "/costsheets/" . md5($this->item->id . "consumables") . ".pdf"; ?>
                        <tr class="section_estimate" style="display: none;">
                            <td colspan="2">
                                <b>Смета по расходным материалам</b>
                            </td>
                            <td>
                                <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                    <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                                <?php }?>
                            </td>
                        </tr>
                        <tr class="section_estimate" style="display: none;">
                            <td>
                                <div class="email-all" style="float: left;">
                                    <input list="email" name="all-email" id="all-email" class="form-control" placeholder="Адрес эл.почты" type="text">
                                    <datalist id="email">
                                        <?php if (!empty($contact_email)) foreach ($contact_email AS $em) { ?>
                                            <option value="<?=$em->contact;?>">
                                        <?php } ?>
                                    </datalist>
                                </div>
                                <div class="file_data">
                                    <div class="file_upload">
                                        <input type="file" class="dopfile" name="dopfile" id="dopfile">
                                    </div>
                                    <div class="file_name"></div>
                                    <script>
                                        jQuery(function () {
                                            jQuery("div.file_name").html("Файл не выбран");
                                            jQuery("div.file_upload input.dopfile").change(function () {
                                                var filename = jQuery(this).val().replace(/.*\\/, "");
                                                jQuery("div.file_name").html((filename != "") ? filename : "Файл не выбран");
                                            });
                                        });
                                    </script>
                                </div>
                            </td>
                            <td colspan="2">
                                <button class="btn btn-primary" id="send_all_to_email" type="button">Отправить</button>
                            </td>
                        </tr>
                        <?php if($is_gmmanager){?>
                            <tr>
                                <th>Обший раскрой</th>
                                <td colspan="2">
                                    <?php $path = "/costsheets/".md5($this->item->id."common_cutpdf").".pdf"; ?>
                                    <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                        <a href="<?php echo $path; ?>" class="btn btn-secondary"
                                           target="_blank">Посмотреть</a>
                                    <?php } else { ?>
                                        -
                                    <?php } ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Смета по расходным материалам</th>
                                <td colspan = "2">
                                    <?php $path = "/costsheets/" . md5($this->item->id . "consumables") . ".pdf"; ?>
                                    <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                        <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                                    <?php } else { ?>
                                        -
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php }?>
                    </table>
                </div>
                <?php
                    foreach ($calculations as $k => $calculation) { 
                        $mounters = json_decode($calculation->mounting_sum);
                        if (!empty($calculation->n3)) {
                            $filename = "/calculation_images/" . md5("calculation_sketch" . $calculation->id) . ".svg";
                        }
                ?>
                        <div class="tab-pane" id="calculation<?php echo $calculation->id; ?>" role="tabpanel">
                            <div class="other_tabs">
                            <?php if($needShow){
                                $type_url = ''; $subtype_url = '';
                                if (!empty($type)){
                                    $type_url = "&type=$type";
                                }
                                if (!empty($subtype)){
                                    $subtype_url = "&subtype=$subtype";
                                }
                                $button_url = "index.php?option=com_gm_ceiling&view=calculationform2$type_url$subtype_url&calc_id=$calculation->id";
                            ?>

                            <?php } ?>
                                <div class="row" style="margin-bottom: 10px;">
                                    <div class="col-md-12">
                                        <a class="btn btn-primary change_calc" href="<?php echo $button_url; ?>" data-calc_id="<?php echo $calculation->id; ?>">Изменить расчет</a>
                                        <input type="file" class="img_file" data-calc-id="<?= $calculation->id; ?>" data-img-type="before" style="display: none;" multiple accept="image/*">
                                        <button type="button" class="btn btn-primary btn_img_file"><i class="fa fa-camera" aria-hidden="true"></i></button>
                                        <input type="hidden" id="input_delete_uploaded_calc_img">
                                    </div>
                                </div>
                                <?php
                                    $dir_before = 'uploaded_calc_images/'.$calculation->id.'/before';
                                    $dir_after = 'uploaded_calc_images/'.$calculation->id.'/after';
                                    $dir_defect = 'uploaded_calc_images/'.$calculation->id.'/defect';
                                    $files = [];
                                    $temp = [];
                                    if (is_dir($dir_before)) {
                                        $temp = scandir($dir_before);
                                        foreach ($temp as $key => $value) {
                                            if (strlen($value) === 32) {
                                                $temp[$key] = $dir_before.'/'.$value;
                                            } else {
                                                unset($temp[$key]);
                                            }
                                        }
                                        $files = array_merge($files, $temp);
                                    }
                                    if (is_dir($dir_after)) {
                                        $temp = scandir($dir_after);
                                        foreach ($temp as $key => $value) {
                                            if (strlen($value) === 32) {
                                                $temp[$key] = $dir_after.'/'.$value;
                                            } else {
                                                unset($temp[$key]);
                                            }
                                        }
                                        $files = array_merge($files, $temp);
                                    }
                                    if (is_dir($dir_defect)) {
                                        $temp = scandir($dir_defect);
                                        foreach ($temp as $key => $value) {
                                            if (strlen($value) === 32) {
                                                $temp[$key] = $dir_defect.'/'.$value;
                                            } else {
                                                unset($temp[$key]);
                                            }
                                        }
                                        $files = array_merge($files, $temp);
                                    }

                                    if (empty($files)) {
                                        $col1 = 0;
                                        $col2 = 5;
                                    } else {
                                        $col1 = 8;
                                        $col2 = 4;
                                    }
                                ?>
                                <div class="row">
                                    <div class="col-md-<?=$col1?>">
                                        <div class="row div_imgs">
                                            <?php
                                                foreach ($files as $value) {
                                                    echo '<img src="'.$value.'" data-path="'.str_replace('uploaded_calc_images/', '', $value).'" class="uploaded_calc_img">';
                                                }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-md-<?=$col2;?>">
                                            <textarea class="inputactive" name="calc_comment" rows="5" ><?=$calculation->comment?></textarea>
                                            <button class="btn btn-primary" type="button" name="add_calc_comment" data-calc_id = "<?php echo $calculation->id;?>" style="width:100%;">Сохранить комментарий<i class="fa fa-paper-plane" aria-hidden="true"></i></button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php if (!empty($filename)) { ?>
                                            <div class="sketch_image_block" style="margin-top: 15px;">
                                                <h4>Чертеж </h4>
                                                <div class="section_content">
                                                    <img class="sketch_image" style="width: 100%;max-height: 1000px;object-fit: contain" src="<?php echo $filename.'?t='.time(); ?>"/>
                                                </div>
                                            </div>
                                        <?php }
                                        $filename = ''; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-xs-12 wtf_padding">
                                                <?php if (!empty($calculation->n3)){
                                                    $canvas = $canvas_model->getFilteredItemsCanvas("`a`.`id` = $calculation->n3");?>
                                                    <h4>Материал</h4>
                                                    <table class="table_info2">
                                                        <tr>
                                                            <td>
                                                                <?php echo $canvas[0]->texture_title.' '.$canvas[0]->name.' '.$canvas[0]->width;?>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                        if (!empty($canvas[0]->color_id)) {
                                                            ?>
                                                            <tr>
                                                                <td>Цвет:</td>
                                                                <td>
                                                                    <?php echo $canvas[0]->color_title; ?>
                                                                    <img src="/<?php echo $canvas[0]->color_file; ?>" alt=""/>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </table>
                                                <?php } ?>
                                                <h4 style="margin: 10px 0;">Размеры помещения</h4>
                                                <table class="table_info2">
                                                    <tr>
                                                        <td>Площадь, м<sup>2</sup>:</td>
                                                        <td><?php echo $calculation->n4; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Периметр, м:</td>
                                                        <td><?php echo $calculation->n5; ?></td>
                                                    </tr>
                                                </table>
                                                <h4 style="margin: 10px 0;">Профиль</h4>
                                                <?php switch($calculation->n28){
                                                    case 0:
                                                        $profil = "Отсутствует";
                                                        break;
                                                    case 1:
                                                        $profil = "Потолочный Al";
                                                        break;
                                                    case 2:
                                                        $profil = "Стеновой Al";
                                                        break;
                                                    case 3:
                                                        $profil = "Стеновой ПВХ";
                                                        break;
                                                }?>
                                                <table class="table_info2">
                                                    <tr>
                                                        <td><?php echo $profil;?></td>
                                                    </tr>
                                                </table>
                                                <?php if(!empty(floatval($calculation->remove_n28)) || !empty(floatval($calculation->n41))){?>
                                                    <h4 style="margin: 10px 0;">Демонтаж</h4>
                                                    <table class="table_info2">
                                                        <?php if(!empty(floatval($calculation->remove_n28))){?>
                                                            <tr>
                                                                <th>Демонтаж профиля, м:</th>
                                                                <td><?php echo $calculation->remove_n28;?></td>
                                                            </tr>
                                                        <?php }?>
                                                        <?php if(!empty(floatval($calculation->n41))){?>
                                                            <tr>
                                                                <th>Демонтаж потолка:</th>
                                                                <td>нужен</td>
                                                            </tr>
                                                        <?php }?>
                                                    </table>
                                                <?php } ?>
                                                <?php if ($calculation->n6 > 0) { ?>
                                                    <h4 style="margin: 10px 0;">Вставка</h4>
                                                    <table class="table_info2">
                                                        <tr>
                                                            <?php if ($calculation->n6 == 314) { ?>
                                                                <td>Белая</td>
                                                                <td></td>
                                                                <?php
                                                            } else{
                                                                $color = $components_model->getColorId($calculation->n6);
                                                                ?>
                                                                <td>Цветная:</td>
                                                                <td>
                                                                    <?php echo $color->title; ?> <img style='width: 50px; height: 30px;' src="/<?php echo $color->file; ?>"/>
                                                                </td>
                                                            <?php } ?>
                                                        </tr>
                                                    </table>
                                                <?php } ?>
                                                <?php if ($calculation->n12) { ?>
                                                    <h4 style="margin: 10px 0;">Установка люстры</h4>
                                                    <table class="table_info2">
                                                        <tr>
                                                            <td><?php echo $calculation->n12; ?> шт.</td>
                                                            <td></td>
                                                        </tr>
                                                    </table>
                                                <?php }
                                                ?>
                                                <?php if ($calculation->n13) { ?>
                                                    <h4 style="margin: 10px 0;">Установка светильников</h4>
                                                    <table class="table_info2">
                                                        <?php
                                                        foreach ($calculation->n13 as $key => $n13_item) {
                                                            echo "<tr><td><b>Количество:</b> " . $n13_item->n13_count . " шт - <b>Тип:</b>  " . $n13_item->type_title . " - <b>Размер:</b> " . $n13_item->component_title . "</td></tr>";
                                                        }
                                                        ?>
                                                    </table>
                                                <?php } ?>
                                                <?php if ($calculation->n26) {?>
                                                    <h4 style="margin: 10px 0;">Светильники Гильдии Мастеров</h4>
                                                    <table class="table_info2">
                                                        <?php
                                                        foreach ($calculation->n26 as $key => $n26_item) {
                                                            echo "<tr><td><b>Количество:</b> " . $n26_item->n26_count . " шт - <b>Тип:</b>  " . $n26_item->component_title_illum . " -  <b>Лампа:</b> " . $n26_item->component_title . "</td></tr>";
                                                        }
                                                        ?>
                                                    </table>
                                                <?php } ?>
                                                <?php if ($calculation->n14) { ?>
                                                    <h4 style="margin: 10px 0;">Обвод трубы</h4>
                                                    <table class="table_info2">
                                                        <?php
                                                        foreach ($calculation->n14 as $key => $n14_item) {
                                                            echo "<tr><td><b>Количество:</b> " . $n14_item->n14_count . " шт  -  <b>Диаметр:</b>  " . $n14_item->component_title . "</td></tr>";
                                                        }
                                                        ?>
                                                    </table>
                                                <?php } ?>
                                                <?php if ($calculation->n27> 0) { ?>
                                                    <h4 style="margin: 10px 0;">Шторный карниз</h4>
                                                    <?php if ($calculation->n16) {
                                                        switch($calculation->niche){
                                                            case 1:
                                                                $niche_title = "Открытая ниша";
                                                                break;
                                                            case 2:
                                                                $niche_title = "Закрытая ниша";
                                                                break;
                                                            case 3:
                                                                $niche_title = "Ниша с пластиком 100мм";
                                                                break;
                                                            case 4:
                                                                $niche_title = "Ниша с пластиком 150мм";
                                                                break;
                                                            case 5:
                                                                $niche_title = "Ниша с пластиком 200мм";
                                                                break;
                                                        }
                                                        ?>
                                                        <table class="table_info2">
                                                            <tr>
                                                                <td><?php echo $niche_title?></td>
                                                                <td><?php echo $calculation->n27; ?> м.</td>
                                                            </tr>
                                                        </table>
                                                    <?php } else { ?>
                                                        <table class="table_info2">
                                                            <tr>
                                                                <td><?php echo "Обычный шторный карниз"?></td>
                                                                <td><?php echo $calculation->n27; ?> м.</td>
                                                            </tr>
                                                        </table>
                                                        <?php
                                                    }
                                                } ?>

                                                <?php if ($calculation->n15) { ?>
                                                    <h4 style="margin: 10px 0;">Шторный карниз Гильдии мастеров</h4>
                                                    <table class="table_info2">
                                                        <?php
                                                        foreach ($calculation->n15 as $key => $n15_item) {
                                                            echo "<tr><td><b>Количество:</b> " . $n15_item->n15_count . " шт - <b>Тип:</b>   " . $n15_item->type_title . " <b>Длина:</b> " . $n15_item->component_title . "</td></tr>";
                                                        }
                                                        ?>
                                                    </table>
                                                <?php } ?>

                                                <?php if ($calculation->n22 || $calculation->n22 || $calculation->n42) { ?>
                                                    <h4 style="margin: 10px 0;">Вентиляция</h4>
                                                    <?php if($calculation->n22) {?>
                                                        <table class="table_info2">
                                                            <?php
                                                            foreach ($calculation->n22 as $key => $n22_item) {
                                                                echo "<tr><td><b>Количество:</b> " . $n22_item->n22_count . " шт - <b>Тип:</b>   " . $n22_item->type_title . " - <b>Размер:</b> " . $n22_item->component_title . "</td></tr>";
                                                            }
                                                            ?>
                                                        </table>
                                                    <?php }?>
                                                    <?php if($calculation->n22_1) {?>
                                                        <table class="table_info2">
                                                            <tr>
                                                                <th>Пластиковый короб, м</th>
                                                                <td><?php echo $calculation->n22_1;?></td>
                                                            </tr>
                                                        </table>
                                                    <?php }?>
                                                    <?php if($calculation->n42) {?>
                                                        <table class="table_info2">
                                                            <tr>
                                                                <th>Вытяжка(наклейка кольца), шт</th>
                                                                <td><?php echo $calculation->n42;?></td>
                                                            </tr>
                                                        </table>
                                                    <?php }?>
                                                <?php } ?>
                                                <?php if ($calculation->n23) { ?>
                                                    <h4 style="margin: 10px 0;">Диффузор</h4>
                                                    <table class="table_info2">
                                                        <?php
                                                        foreach ($calculation->n23 as $key => $n23_item) {
                                                            echo "<tr><td><b>Количество:</b> " . $n23_item->n23_count . " шт - <b>Размер:</b>  " . $n23_item->component_title . "</td></tr>";
                                                        }
                                                        ?>
                                                    </table>
                                                <?php } ?>
                                                <?php if ($calculation->n29) { ?>
                                                    <h4 style="margin: 10px 0;">Переход уровня</h4>
                                                    <table class="table_info2">
                                                        <?php
                                                        foreach ($calculation->n29 as $key => $n29_item) {
                                                            echo "<tr><td><b>Количество:</b> " . $n29_item->n29_count . " м - <b>Тип:</b>  " . $n29_item->type_title . "</td></tr>";
                                                        }
                                                        ?>
                                                    </table>
                                                <?php } ?>
                                                <?php if ($calculation->n19) { ?>
                                                    <h4 style="margin: 10px 0;">Провода</h4>
                                                    <table class="table_info2">
                                                        <?php
                                                        foreach ($calculation->n19 as $key => $n19_item) {
                                                            echo "<tr><td><b>Количество:</b> " . $n19_item->count . " м - <b>Тип:</b>   " . $n19_item->wire_title."</td></tr>";
                                                        }
                                                        ?>
                                                    </table>
                                                <?php } ?>
                                                <h4 style="margin: 10px 0;">Прочее</h4>
                                                <table class="table_info2">
                                                    <?php if ($calculation->n9> 0) { ?>
                                                        <tr>
                                                            <td>Углы, шт.:</td>
                                                            <td><?php echo $calculation->n9; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n10> 0) { ?>
                                                        <tr>
                                                            <td> Криволинейный участок, м:</td>
                                                            <td><?php echo $calculation->n10; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n11> 0) { ?>
                                                        <tr>
                                                            <td>Внутренний вырез, м:</td>
                                                            <td><?php echo $calculation->n11; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n7> 0) { ?>
                                                        <tr>
                                                            <td>Крепление в плитку, м:</td>
                                                            <td><?php echo $calculation->n7; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n8> 0) { ?>
                                                        <tr>
                                                            <td>Крепление в керамогранит, м:</td>
                                                            <td><?php echo $calculation->n8; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n17> 0) { ?>
                                                        <tr>
                                                            <td>Закладная брусом, м:</td>
                                                            <td><?php echo $calculation->n17; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n18> 0) { ?>
                                                        <tr>
                                                            <td> Усиление стен, м:</td>
                                                            <td><?php echo $calculation->n18;?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n20> 0) { ?>
                                                        <tr>
                                                            <td>Разделитель, м:</td>
                                                            <td><?php echo $calculation->n20; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n20_1> 0) { ?>
                                                        <tr>
                                                            <td>Отбойник, м:</td>
                                                            <td><?php echo $calculation->n20_1; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n21> 0) { ?>
                                                        <tr>
                                                            <td>Пожарная сигнализация, шт:</td>
                                                            <td><?php echo $calculation->n21; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->dop_krepezh> 0) { ?>
                                                        <tr>
                                                            <td>Дополнительный крепеж:</td>
                                                            <td><?php echo $calculation->dop_krepezh; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n24> 0) { ?>
                                                        <tr>
                                                            <td>Сложность доступа к месту монтажа, м:</td>
                                                            <td><?php echo $calculation->n24; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n30> 0) { ?>
                                                        <tr>
                                                            <td>Парящий потолок, м:</td>
                                                            <td><?php echo $calculation->n30; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n32> 0) { ?>
                                                        <tr>
                                                            <td>Слив воды, кол-во комнат:</td>
                                                            <td><?php echo $calculation->n32; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n22_1> 0) { ?>
                                                        <tr>
                                                            <td>Пластиковый короб:</td>
                                                            <td><?php echo $calculation->n22_1; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n33> 0) { ?>
                                                        <tr>
                                                            <td>Лючок:</td>
                                                            <td><?php echo $calculation->n33; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n33_2> 0) { ?>
                                                        <tr>
                                                            <td>Обход лючка:</td>
                                                            <td><?php echo $calculation->n33_2; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n34> 0) { ?>
                                                        <tr>
                                                            <td>Диодная лента:</td>
                                                            <td><?php echo $calculation->n34; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n34_2> 0) { ?>
                                                        <tr>
                                                            <td>Блок питания диод.ленты:</td>
                                                            <td><?php echo $calculation->n34_2; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n35> 0) { ?>
                                                        <tr>
                                                            <td>Контурный профиль:</td>
                                                            <td><?php echo $calculation->n35; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n36> 0) { ?>
                                                        <tr>
                                                            <td>Перегарпунка, м:</td>
                                                            <td><?php echo $calculation->n36; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n37) { ?>
                                                        <tr>
                                                            <td>Фотопечать, м<sup>2</sup>:</td>
                                                            <td><?php echo json_decode($calculation->n37)->square; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n38) { ?>
                                                        <tr>
                                                            <td>Ремонт потолка, шт:</td>
                                                            <td><?php echo $calculation->n38; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if (!empty(floatval($calculation->n39))) { ?>
                                                        <tr>
                                                            <td>Лента на шторный карниз, м:</td>
                                                            <td><?php echo $calculation->n39; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                    <?php if ($calculation->n40) { ?>
                                                        <tr>
                                                            <td>Закругления на шторный карниз, шт:</td>
                                                            <td><?php echo $calculation->n40; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                </table>
                                                <?php $extra_mounting = (array) json_decode($calculation->extra_mounting);?>
                                                <?php if (!empty($extra_mounting) ) { ?>
                                                    <h4 style="margin: 10px 0;">Дополнительные работы</h4>
                                                    <table class="table_info2">
                                                        <?php
                                                        foreach($extra_mounting as $dop) {
                                                            echo "<tr><td><b>Название:</b></td><td>" . $dop->title .  "</td></tr>";
                                                        }
                                                        ?>
                                                    </table>
                                                <?php } ?>
                                            </div>
                                            <?php if($needShow){?>
                                                <button class="btn btn-danger delete_calc" data-calculation_id = "<?php echo $calculation->id;?>" style="margin:10px;" type="button" > Удалить потолок </button>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

</div>

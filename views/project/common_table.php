<div class="row">
    <div class="col-xs-12 no_padding">
        <h4>Расчеты для проекта</h4>
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?php if($user->dealer_type == 0 || count($calculations) == 0) echo "active";?>" data-toggle="tab" href="#summary" role="tab">Общее</a>
            </li>
            <?php $first = true; foreach ($calculations as $k => $calculation) { ?>
                <li class="nav-item">
                    <a class="nav-link <?=($user->dealer_type == 1 && $first)?"active":""; $first = false;?>" data-toggle="tab" href="#calculation<?php echo $calculation->id; ?>" role="tab">
                        <?php echo $calculation->calculation_title; ?>
                    </a>
                </li>
            <?php } ?>
            <li class="nav-item"> 
                <button type="button" class="nav-link" id="add_calc" style="color:white;">
                    Добавить потолок <i class="fa fa-plus-square-o" aria-hidden="true"></i>
                </button>
            </li>
        </ul>
        <?php if($user->dealer_type == 1 && count($calculations) <= 0) { ?>
            <p>У Вас еще нет потолков</p>
        <?php } else { ?>
            <!-- Tab panes -->
            <div class="tab-content">
                <div class="tab-pane <?php if($user->dealer_type == 0 || count($calculations) == 0) echo "active";?>" id="summary" role="tabpanel">
                    <table id="table1" class="table-striped one-touch-view">
                        <tr>
                            <th colspan="4" class="section_header" id="sh_ceilings">
                                Потолки <i class="fa fa-sort-desc" aria-hidden="true"></i>
                            </th>
                        </tr>
                        <?php 
                            foreach ($calculations as $calculation) {
                        ?>
                            <tr class="section_ceilings">
                                <td class="include_calculation" colspan="4">
                                    <input name='include_calculation[]' value='<?php echo $calculation->id; ?>' type='checkbox' checked="checked">
                                    <input name='calculation_total[<?php echo $calculation->id; ?>]' value='<?php echo $calculation->calculation_total; ?>' type='hidden'>
                                    <input name='calculation_total_discount[<?php echo $calculation->id; ?>]' value='<?php echo $calculation->calculation_total_discount; ?>' type='hidden'>
                                    <input name='total_square[<?php echo $calculation->id; ?>]' value='<?php echo $calculation->n4; ?>' type='hidden'>
                                    <input name='total_perimeter[<?php echo $calculation->id; ?>]' value='<?php echo $calculation->n5; ?>' type='hidden'>      
                                    <span><?php echo $calculation->calculation_title; ?></span>
                                </td>
                            </tr>
                            <tr class="section_ceilings">
                                <td>S/P :</td>
                                <td colspan="3">
                                    <?php echo $calculation->n4; ?> м<sup>2</sup> / <?php echo $calculation->n5; ?> м
                                </td>
                            </tr>
                            <tr class="section_ceilings">
                                <?php if ($calculation->discount != 0) { ?>
                                    <td>Цена / -<?php echo $calculation->discount ?>% :</td>
                                    <td id="calculation_total"> <?php echo round($calculation->calculation_total, 0); ?> р. /</td>
                                    <td colspan="2" id="calculation_total_discount"> <?php echo round($calculation->calculation_total_discount , 0); ?>
                                        р.
                                    </td>
                                <?php } else { ?>
                                    <td>Итого</td>
                                    <td colspan="3" id="calculation_total"> <?php echo round($calculation->calculation_total, 0); ?> р.</td>
                                <?php } ?>
                            </tr>
                        <?php
                                if ($calculation->discount > 0) {
                                    $kol++;
                                }
                            } 
                        ?>
                        <tr>
                            <th>Общая S/общий P :</th>
                            <th id="total_square">
                                <span class = "sum"><?php echo round($total_square,2);?></span> м<sup>2</sup> /
                            </th>
                            <th colspan="2" id="total_perimeter">
                                <span class = "sum"><?php echo  round($total_perimeter,2); ?></span> м
                            </th>
                        </tr>
                        <tr>
                            <th colspan="4">Транспортные расходы</th>
                        </tr>
                        <tr>
                            <td colspan="4">
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
                            <th>Транспорт</th>
                            <td colspan="3" id="transport_sum">
                                <span class = "sum" data-selfval = <?php echo $self_sum_transport ?>><?=$client_sum_transport;?></span> р.
                            </td>
                            <!-- <input id="transport_suma" value='<?php //echo $client_sum_transport; ?>' type='hidden'> -->
                        </tr>
                        <tr>
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
                                <th id="project_total" colspan="3">
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
                        <?php if ($user->dealer_type != 2) { ?>
                            <tr>
                                <td id="calcs_self_canvases_total"><span>П </span> <span class = "sum"><?php echo round($self_canvases_sum, 0) ?></span></td>
                                <td id="calcs_self_components_total"><span>К </span><span data-oldval = <?php echo round($self_components_sum, 0) ?> class = "sum"><?php echo round($self_components_sum, 0) ?></span></td>
                                <td id="calcs_self_mount_total"><span>М </span><span class = "sum"><?php echo round($self_mounting_sum+$self_sum_transport, 0); ?></span></td>
                                <td id="calcs_total"><div id="calcs_total_border"><?php echo round($project_self_total  , 0); ?></div></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <th colspan="4" class="section_header" id="sh_estimate"> Сметы <i class="fa fa-sort-desc" aria-hidden="true"></i></th>
                        </tr>
                        <?php foreach ($calculations as $calculation) { ?>
                            <tr class="section_estimate" id="section_estimate_<?= $calculation->id; ?>" style="display:none;">
                                <td><?php echo $calculation->calculation_title; ?></td>
                                <td colspan="3">
                                    <?php
                                        $path = "/costsheets/" . md5($calculation->id . "client_single") . ".pdf";
                                        $pdf_names[] = array("name" => $calculation->calculation_title, "filename" => md5($calculation->id . "client_single") . ".pdf", "id" => $calculation->id);
                                    ?>
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
                        <?php if (count($calculations) > 0) { ?>
                            <tr class="section_estimate" style="display:none;">
                                <td colspan="4"><b>Отправить все сметы <b></td>
                            </tr>
                            <tr class="section_estimate" style="display:none;">
                                <td>
                                    <div class="email-all" style="float: left;">
                                        <input list="email" name="all-email" id="all-email1" class="form-control" placeholder="Адрес эл.почты" type="text">
                                        <datalist id="email">
                                            <?php foreach ($contact_email AS $em) { ?>
                                                <option value="<?=$em->contact;?>">
                                            <?php }?>
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
                                <td colspan="3">
                                    <button class="btn btn-primary" id="send_all_to_email1" type="button">Отправить</button>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if (($user->dealer_type == 1 && $user->dealer_mounters == 0) || $user->dealer_type != 1) { ?>
                            <tr>
                                <th id="sh_mount" colspan="4"> Наряд на монтаж <i class="fa fa-sort-desc" aria-hidden="true"></i></th>
                            </tr>
                            <?php foreach ($calculations as $calculation) { ?>
                                <tr class="section_mount" id="section_mount_<?= $calculation->id; ?>" style="display:none;">
                                    <td><?php echo $calculation->calculation_title; ?></td>
                                    <td colspan="3">
                                        <?php 
                                            $path = "/costsheets/" . md5($calculation->id . "mount_single") . ".pdf";
                                            $pdf_names_mount[] = array("name" => $calculation->calculation_title, "filename" => md5($calculation->id . "mount_single") . ".pdf", "id" => $calculation->id);
                                            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
                                        ?>
                                            <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                                        <?php } else { ?>
                                            После договора
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php
                                }
                                $json1 = json_encode($pdf_names_mount);
                            ?>
                            <?php if (count($calculations) > 0) { ?>
                                <tr class="section_mount" style="display:none;">
                                    <td colspan="4"><b>Отправить все наряды на монтаж<b></td>
                                </tr>
                                <tr class="section_mount" style="display:none;">
                                    <td>
                                        <div class="email-all" style="float: left;">
                                            <input name="all-email" id="all-email2" class="form-control" value="" placeholder="Адрес эл.почты" type="text">
                                        </div>
                                        <div class="file_data">
                                            <div class="file_upload">
                                                <input type="file" class="dopfile1" name="dopfile1" id="dopfile1">
                                            </div>
                                            <div class="file_name1"></div>
                                            <script>
                                                jQuery(function () {
                                                    jQuery("div.file_name1").html("Файл не выбран");
                                                    jQuery("div.file_upload input.dopfile1").change(function () {
                                                        var filename = jQuery(this).val().replace(/.*\\/, "");
                                                        jQuery("div.file_name1").html((filename != "") ? filename : "Файл не выбран");
                                                    });
                                                });
                                            </script>
                                        </div>
                                    </td>
                                    <td colspan="3">
                                        <button class="btn btn-primary" id="send_all_to_email2" type="button">Отправить</button>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                        <!--------------- Общая смета для клиента -------------->
                        <tr>
                            <td><b>Отправить общую смету <b></td>
                            <td colspan="3">
                                <?php
                                    $path = "/costsheets/" . md5($this->item->id . "client_common") . ".pdf";
                                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
                                ?>
                                    <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank" id = "show">Посмотреть</a>
                                <?php } ?>
                                    <span data-href="<?=$path;?>">-
                            </td>
                        </tr>
                        <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                            <tr>
                                <td>
                                    <div class="email-all" style="float: left;">
                                        <input list="email" name="all-email" id="all-email3" class="form-control" placeholder="Адрес эл.почты" type="text">
                                        <datalist id="email">
                                            <?php foreach ($contact_email AS $em) { ?>
                                                <option value="<?=$em->contact;?>">
                                            <?php } ?>
                                        </datalist>
                                    </div>
                                    <div class="file_data">
                                        <div class="file_upload">
                                            <input type="file" class="dopfile2" name="dopfile2" id="dopfile2">
                                        </div>
                                        <div class="file_name2"></div>
                                        <script>
                                            jQuery(function () {
                                                jQuery("div.file_name2").html("Файл не выбран");
                                                jQuery("div.file_upload input.dopfile2").change(function () {
                                                    var filename = jQuery(this).val().replace(/.*\\/, "");
                                                    jQuery("div.file_name2").html((filename != "") ? filename : "Файл не выбран");
                                                });
                                            });
                                        </script>
                                    </div>
                                </td>
                                <td colspan="3">
                                    <button class="btn btn-primary" id="send_all_to_email3" type="button">Отправить</button>
                                </td>
                            </tr>
                        <?php } ?>
                        <!-- общий наряд на монтаж--> 
                        <?php if (($user->dealer_type == 1 && $user->dealer_mounters == 0) || $user->dealer_type != 1) { ?>
                            <tr>
                                <td><b>Общий наряд на монтаж <b></td>
                                <td colspan="3">
                                    <?php
                                        $path = "/costsheets/" . md5($this->item->id . "mount_common") . ".pdf"; 
                                        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) {
                                    ?>
                                        <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                                    <?php } else { ?>
                                        -
                                    <?php }
                                        $pdf_names[] = array("name" => "Подробная смета", "filename" => md5($this->item->id . "mount_common") . ".pdf", "id" => $this->item->id);
                                        $json2 = json_encode($pdf_names);
                                    ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
                <?php
                    $first = true;
                    foreach ($calculations as $k => $calculation) { 
                        $mounters = json_decode($calculation->mounting_sum); 
                        if (!empty($calculation->n3)) {
                            $filename = "/calculation_images/" . md5("calculation_sketch" . $calculation->id) . ".svg";
                        }
                ?>
                        <div class="tab-pane <?=($user->dealer_type == 1 && $first)?"active":""; $first = false;?>" id="calculation<?php echo $calculation->id; ?>" role="tabpanel">
                            <div class="other_tabs">
                            <?php if($this->item->project_status < 5 || $this->item->project_status == 22)
                            {
                                $jinput = JFactory::getApplication()->input;
                                $type = $jinput->get('type', '', 'STRING');
                                $subtype = $jinput->get('subtype', '', 'STRING');

                                $type_url = '';
                                if (!empty($type))
                                {
                                    $type_url = "&type=$type";
                                }

                                $subtype_url = '';
                                if (!empty($subtype))
                                {
                                    $subtype_url = "&subtype=$subtype";
                                }

                                $button_url = "index.php?option=com_gm_ceiling&view=calculationform2$type_url$subtype_url&calc_id=$calculation->id";
                            ?>
                                <a class="btn btn-primary" href="<?php echo $button_url; ?>">Изменить расчет</a>
                            <?php } ?>
                                <?php if (!empty($filename)) { ?>
                                    <div class="sketch_image_block" style="margin-top: 15px;">
                                        <h4>Чертеж <i class="fa fa-sort-desc" aria-hidden="true"></i></h4>
                                        <div class="section_content">
                                            <img class="sketch_image" src="<?php echo $filename.'?t='.time(); ?>"/>
                                        </div>
                                    </div>
                                <?php } 
                                    $filename = ''; ?>
                                <div class="row">
                                    <div class="col-xs-12 wtf_padding">
                                        <?php 
                                            if (!empty($calculation->n3)){
                                            $canvas = $canvas_model->getFilteredItemsCanvas("`a`.`id` = $calculation->n3");
                                        ?>
                                            <h4>Материал</h4>
                                            <table class="table_info2">
                                                <tr>
                                                    <td>Тип фактуры:</td>
                                                    <td><?php echo $canvas[0]->texture_title; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Производитель, ширина:</td>
                                                    <td><?php echo $canvas[0]->name.' '.$canvas[0]->width; ?></td>
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
                                            <h4 style="margin: 10px 0;">Вставка</h4>
                                            <?php if ($calculation->n6 > 0) { ?>
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
                                            <?php if ($calculation->n16) { ?>
                                                <table class="table_info2">
                                                    <tr>
                                                        <td>Скрытый карниз:</td>
                                                        <td><?php echo $calculation->n16; ?></td>
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
                                            <?php if ($calculation->n27> 0) { ?>
                                                <h4 style="margin: 10px 0;">Шторный карниз</h4>
                                                <table class="table_info2">
                                                    <tr>
                                                        <td>
                                                            <?php if ($calculation->n16) echo "Скрытый карниз"; ?>
                                                            <?php if (!$calculation->n16) echo "Обычный карниз"; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $calculation->n27; ?> м.
                                                        </td>
                                                    </tr>
                                                </table>
                                            <?php } ?>
                                            <?php if ($calculation->n26) { ?>
                                                <h4 style="margin: 10px 0;">Светильники Эcola</h4>
                                                <table class="table_info2">
                                                    <?php
                                                        foreach ($calculation->n26 as $key => $n26_item) {
                                                            echo "<tr><td><b>Количество:</b> " . $n26_item->n26_count . " шт - <b>Тип:</b>  " . $n26_item->component_title_illuminator . " -  <b>Лампа:</b> " . $n26_item->component_title_lamp . "</td></tr>";
                                                        }
                                                    ?>
                                                </table> 
                                            <?php } ?>
                                            <?php if ($calculation->n22) { ?>
                                                <h4 style="margin: 10px 0;">Вентиляция</h4>
                                                <table class="table_info2">
                                                    <?php
                                                        foreach ($calculation->n22 as $key => $n22_item) {
                                                            echo "<tr><td><b>Количество:</b> " . $n22_item->n22_count . " шт - <b>Тип:</b>   " . $n22_item->type_title . " - <b>Размер:</b> " . $n22_item->component_title . "</td></tr>";
                                                        }
                                                    ?>
                                                </table>
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
                                                        <td> Криволинейный вырез, м:</td>
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
                                                <?php if ($calculation->n19> 0) { ?>
                                                    <tr>
                                                        <td> Провод, м:</td>
                                                        <td><?php echo $calculation->n19; ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <?php if ($calculation->n20> 0) { ?>
                                                    <tr>
                                                        <td>Разделитель, м:</td>
                                                        <td><?php echo $calculation->n20; ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <?php if ($calculation->n21> 0) { ?>
                                                    <tr>
                                                        <td>Пожарная сигнализация, м:</td>
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
                                    <?php if($this->item->project_status < 5 || $this->item->project_status == 22){?>
                                        <button class="btn btn-danger"  id="delete" style="margin:10px;" type="button" onclick="submit_form(this);"> Удалить потолок </button>
                                    <?php } ?>
                                    <input id="idCalcDeleteSelect" value="<?=$calculation->id;?>" type="hidden" disabled>
                                </div>
                            </div>
                        </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>
<?php
 /**
     * @version    CVS: 0.1.7
     * @package    Com_Gm_ceiling
     * @author     SpectralEye <Xander@spectraleye.ru>
     * @copyright  2016 SpectralEye
     * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
    // No direct access
    defined('_JEXEC') or die;

    JHtml::_('behavior.keepalive');
    //JHtml::_('behavior.tooltip');
    JHtml::_('behavior.formvalidation');

    // Load admin language file
    $lang = JFactory::getLanguage();
    $lang->load('com_gm_ceiling', JPATH_SITE);
    $doc = JFactory::getDocument();
    $doc->addScript(JUri::base() . '/media/com_gm_ceiling/js/form.js');

    $project_total = 0;
    $project_total_discount = 0;

    $user = JFactory::getUser();
    $userId = $user->get('id');

    $model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
    $calculations = $model->getProjectItems($this->item->id);

    foreach ($calculations as $calculation) {
        $calculation->dealer_canvases_sum = double_margin($calculation->canvases_sum, $this->item->gm_canvases_margin, $this->item->dealer_canvases_margin);
        $calculation->dealer_components_sum = double_margin($calculation->components_sum, $this->item->gm_components_margin, $this->item->dealer_components_margin);
        $calculation->dealer_gm_mounting_sum = double_margin($calculation->mounting_sum, $this->item->gm_mounting_margin, $this->item->dealer_mounting_margin);
        $calculation->calculation_total = $calculation->dealer_canvases_sum + $calculation->dealer_components_sum + $calculation->dealer_gm_mounting_sum;
        $calculation->calculation_total_discount = $calculation->calculation_total * ((100 - $calculation->discount) / 100);
        $project_total += $calculation->calculation_total;
        $project_total_discount += $calculation->calculation_total_discount;
    }

    $sum_transport = 0;  $sum_transport_discount = 0;
    $mountModel = Gm_ceilingHelpersGm_ceiling::getModel('mount');
    $mount_transport = $mountModel->getDataAll();

    if($this->item->transport == 0 ) $sum_transport = 0;
    if($this->item->transport == 1 ) $sum_transport = double_margin($mount_transport->transport * $this->item->distance_col, $this->item->gm_mounting_margin, $this->item->dealer_mounting_margin);
    if($this->item->transport == 2 ) $sum_transport = ($mount_transport->distance * $this->item->distance + $mount_transport->transport)  * $this->item->distance_col;
    $min = 100;
    foreach($calculations as $d) {
        if($d->discount < $min) $min = $d->discount;
    }
    if ($min != 100) $sum_transport = $sum_transport * ((100 - $min)/100);
    if ($sum_transport < double_margin($mount_transport->transport, $this->item->gm_mounting_margin, $this->item->dealer_mounting_margin) && $sum_transport != 0) {
        $sum_transport = double_margin($mount_transport->transport, $this->item->gm_mounting_margin, $this->item->dealer_mounting_margin);
    }
    $project_total_discount_transport = $project_total_discount + $sum_transport;

    $project_total = round($project_total, 2);
    $project_total_discount = round($project_total_discount, 2);

    $extra_spend_array = Gm_ceilingHelpersGm_ceiling::decode_extra($this->item->extra_spend);
    $penalty_array = Gm_ceilingHelpersGm_ceiling::decode_extra($this->item->penalty);
    $bonus_array = Gm_ceilingHelpersGm_ceiling::decode_extra($this->item->bonus);

    // календарь
    $month1 = date("n");
    $year1 = date("Y");
    if ($month1 == 12) {
        $month2 = 1;
        $year2 = $year1;
        $year2++;
    } else {
        $month2 = $month1;
        $month2++;
        $year2 = $year1;
    }
    if ($this->item->project_status == 1) {
        $whatCalendar = 0;
        $FlagCalendar = [3, $user->dealer_id];
    } elseif ($this->item->project_status != 11 || $this->item->project_status != 12 || $this->item->project_status == 17) {
        $whatCalendar = 1;
        $FlagCalendar = [2, $user->dealer_id];
    }
    $calendar1 = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($userId, $month1, $year1, $FlagCalendar);
    $calendar2 = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($userId, $month2, $year2, $FlagCalendar);
    //----------------------------------------------------------------------------------

    // все бригады
    $Allbrigades = $model->FindAllbrigades($user->dealer_id);
    $AllMounters = [];
    if (count($Allbrigades) == 0) {
        array_push($Allbrigades, ["id"=>$userId, "name"=>$user->get('name')]);
        array_push($AllMounters, ["id"=>$userId, "name"=>$user->get('name')]);
    } else {
        // все монтажники
        $masid = [];
        foreach ($Allbrigades as $value) {
            array_push($masid, $value->id);
        }
        foreach ($masid as $value) {
            if (strlen($where) == 0) {
                $where = "'".$value."'";
            } else {
                $where .= ", '".$value."'";                
            }
        }
        $AllMounters = $model->FindAllMounters($where);
    }
    //----------------------------------------------------------------------------------

    // все замерщики
    if ($user->dealer_id == 1 && !in_array("14", $user->groups)) {
        $AllGauger = $model->FindAllGauger($user->dealer_id, 22);
    } else {
        $AllGauger = $model->FindAllGauger($user->dealer_id, 21);
    }
    //----------------------------------------------------------------------------------

    $mount_sum = 0;

    echo parent::getPreloader();

?>

<?=parent::getButtonBack();?>

<link rel="stylesheet" href="/components/com_gm_ceiling/views/projectform/tmpl/css/style.css" type="text/css" />

<style>
    #jform_project_mounter-lbl {
        display: none;
    }
</style>

<h2>Просмотр проекта</h2>
<form id="form-client">
<?php if ($this->item) { ?>
    <input name="project_id" id="project_id" value="<?php echo $this->item->id; ?>" type="hidden">
    <?php if (sizeof($calculations) > 0) { ?>
        <?php echo "<h3>Расчеты для проекта № ".$this->item->id." </h3>"; ?>
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#calculationAll" role="tab">Общее</a>
            </li>
            <?php foreach ($calculations as $k => $calculation) { ?>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#calculation<?php echo $calculation->id; ?>" role="tab"><?php echo $calculation->calculation_title; ?></a>
                </li>
            <?php  $mount_sum += $calculation->mounting_sum; } ?>
        </ul>
        <!-- Tab panes -->
        <div class="tab-content">
            <div class="tab-pane active" id="calculationAll" role="tabpanel">
                <table>
                    <tr>
                        <th class="section_header" id="sh_ceilings">Потолки <i class="fa fa-sort-desc" aria-hidden="true"></i></th>
                        <th></th>
                    </tr>
                    <?php
                        $project_total = 0;
                        $project_total_discount = 0;
                        $dealer_gm_mounting_sum_1 = 0;
                        $calculation_total_1 = 0;
                        $project_total_1 = 0;
                        $dealer_gm_mounting_sum_11 = 0;
                        $calculation_total_11 = 0;
                        $project_total_11 = 0;
                        $kol = 0;
                        $tmp = 0;
                        $sum_transport_discount_total = 0;
                        $sum_transport_total = 0;
                        $JS_Calcs_Sum = array();

                        foreach ($calculations as $calculation) {
                            $dealer_canvases_sum = double_margin($calculation->canvases_sum, 0 /*$this->item->gm_canvases_margin*/, $this->item->dealer_canvases_margin);
                            $dealer_components_sum = double_margin($calculation->components_sum, 0/* $this->item->gm_components_margin*/, $this->item->dealer_components_margin);
                            $dealer_gm_mounting_sum = double_margin($calculation->mounting_sum, 0/*$this->item->gm_mounting_margin*/, $this->item->dealer_mounting_margin);

                            //$calculation_total_discount = $calculation_total * ((100 - $this->item->project_discount) / 100);

                            if ($user->dealer_type != 2) {
                                $dealer_canvases_sum_1 = margin($calculation->canvases_sum, 0/*$this->item->gm_canvases_margin*/);
                                $dealer_components_sum_1 = margin($calculation->components_sum, 0/*$this->item->gm_components_margin*/);
                                $dealer_gm_mounting_sum_1 = margin($calculation->mounting_sum, 0/*$this->item->gm_mounting_margin*/);
                                $dealer_gm_mounting_sum_11 += $dealer_gm_mounting_sum_1;
                                $calculation_total_1 = $dealer_canvases_sum_1 + $dealer_components_sum_1;
                                $calculation_total_11 += $calculation_total_1;
                                $project_total_1 = $calculation_total_1 + $dealer_gm_mounting_sum_1;
                                $project_total_11 += $project_total_1;
                            }
                            $calculation->calculation_title;
                            $total_square += $calculation->n4;
                            $total_perimeter += $calculation->n5;

                            // --------------------------Высчитываем транспорт в отдельную строчку -----------------------------------------------------
                            //$sum_transport = 0;  $sum_transport_discount = 0;
                        // $mountModel = Gm_ceilingHelpersGm_ceiling::getModel('mount');
                        // $mount_transport = $mountModel->getDataAll();
                            /*if($calculation->transport == 1 && $calculation->mounting_sum != 0) {
                                $tmp = 1;
                                $sum_transport = double_margin($mount_transport->transport, $this->item->gm_mounting_margin, $this->item->dealer_mounting_margin);
                                $sum_transport_total = $sum_transport;
                            }
                            if($calculation->distance > 0  && $calculation->distance_col > 0 && $calculation->mounting_sum != 0) {
                                $tmp = 2;
                                $sum_transport = double_margin($mount_transport->distance * $calculation->distance * $calculation->distance_col, $this->item->gm_mounting_margin, $this->item->dealer_mounting_margin);
                                $sum_transport_total = $sum_transport;
                            }
                            if($calculation->discount > 0 && $sum_transport > 0) {
                                $sum_transport_discount = $sum_transport * ((100 - $calculation->discount) / 100);
                                $sum_transport_discount_total = $sum_transport_discount;
                            }
                            */

                            $calculation_total = $dealer_canvases_sum + $dealer_components_sum + $dealer_gm_mounting_sum ;
                            $calculation_total_discount = $calculation_total * ((100 - $calculation->discount) / 100);
                            $project_total += $calculation_total;
                            $project_total_discount += $calculation_total_discount;
                            $JS_Calcs_Sum[] = round($calculation_total, 0);
                    ?>
                    <tr class="section_ceilings">
                        <td class="include_calculation">

                            <input name='include_calculation[]' value='<?php echo $calculation->id; ?>' type='checkbox' checked="checked">
                            <input name='calculation_total[<?php echo $calculation->id; ?>]' value='<?php echo $calculation_total; ?>' type='hidden'>
                            <input name='calculation_total_discount[<?php echo $calculation->id; ?>]' value='<?php echo $calculation_total_discount; ?>' type='hidden'>
                            <input name='total_square[<?php echo $calculation->id; ?>]' value='<?php echo $calculation->n4; ?>' type='hidden'>
                            <input name='total_perimeter[<?php echo $calculation->id; ?>]' value='<?php echo $calculation->n5; ?>' type='hidden'>
                            <input name='calculation_total1[<?php echo $calculation->id; ?>]' value='<?php echo $calculation_total_1; ?>' type='hidden'>
                            <input name='calculation_total2[<?php echo $calculation->id; ?>]' value='<?php echo $dealer_gm_mounting_sum_1; ?>' type='hidden'>
                            <input name='calculation_total3[<?php echo $calculation->id; ?>]' value='<?php echo $project_total_1; ?>' type='hidden'>
                            <?php echo $calculation->calculation_title; ?>
                        </td>
                    </tr>
                    <tr class="section_ceilings" id="">
                        <td>S/P :</td>
                        <td><?php echo $calculation->n4; ?> м<sup>2</sup> / <?php echo $calculation->n5; ?> м</td>
                        <td></td>
                    </tr>
                    <tr class="section_ceilings">
                        <?php if ($calculation->discount != 0) { ?>
                            <td>Цена / -<? echo $calculation->discount ?>% :</td>
                            <td id="calculation_total"> <?php echo round($calculation_total, 0); ?> руб. /</td>
                            <td id="calculation_total_discount"> <?php echo round($calculation_total_discount, 0); ?>
                                руб.
                            </td>
                        <?php } else { ?>
                            <td>Итого</td>
                            <td id="calculation_total"> <?php echo round($calculation_total, 0); ?> руб.</td>
                            <td></td>
                        <?php } ?>
                    </tr>
                    <?php if($calculation->discount > 0) $kol++;} ?>
                    <tr>
                        <th>Общая S/общий P :</th>
                        <th id="total_square">
                            <?php echo $total_square; ?>м<sup>2</sup> /
                        </th>
                        <th id="total_perimeter">
                            <?php echo $total_perimeter; ?> м
                        </th>
                    </tr>
                    <tr>
                        <th>
                            Транспортные расходы
                        </th>
                        <th></th>
                        <th></th>
                    </tr>
                    <tr>
                        <td style="width: 45%;">
                            <p><input name="transport"  class="radio" id ="transport" value="1"  type="radio"  <?if($this->item->transport == 1 ) echo "checked"?>><label for = "transport">Транспорт по городу</label></p>
                            <div class="row sm-margin-bottom" style="width: 45%; display:none;" id="transport_dist_col" >
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
                                            <button type="button" id="click_transport_1" class="btn btn-primary">Ок</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p><input name="transport" class="radio" id = "distanceId" value="2" type="radio" <?if( $this->item->transport == 2) echo "checked"?>><label for = "distanceId">Выезд за город</label></p>
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
                                            <button type="button" id="click_transport" class="btn btn-primary">Ок</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p><input name="transport" class="radio" id ="no_transport" value="0" type="radio" <?if($this->item->transport == 0 ) echo "checked"?>> <label for="no_transport">Без транспорта</label></p>
                        </td>
                        <th></th>
                        <th></th>
                    </tr>
                    <tr>
                        <?
                            //-------------------------Себестоимость транспорта-------------------------------------
                            if($this->item->transport == 0 ) $sum_transport_1 = 0;
                            if($this->item->transport == 1 ) $sum_transport_1 = $mount_transport->transport * $this->item->distance_col;
                            if($this->item->transport == 2 ) $sum_transport_1 = $mount_transport->distance * $this->item->distance * $this->item->distance_col;
                            $project_total_11 = $project_total_11 + $sum_transport_1;
                            $project_total = $project_total + $sum_transport;
                            $project_total_discount = $project_total_discount + $sum_transport;
                        ?>
                        <th> Транспорт</th>
                        <td id="transport_sum"> <?=$sum_transport;?> руб.</td>
                        <input id="transport_suma" value='<?php echo $sum_transport; ?>' type='hidden'>
                        <td></td>
                    </tr>
                    <tr>
                        <?php if ($kol > 0) { ?>
                            <th>Итого/ - %:
                            </th>
                            <th id="project_total"><span class="sum"><?php echo round($project_total, 0); ?></span> руб. /</th>
                            <th id="project_total_discount">
                            <span class="sum">
                            <?php //---------------  Если сумма проекта меньше 3500, то делаем сумму проекта 3500  -----------------------
                            if ($dealer_gm_mounting_sum_11 == 0 ) { echo round($project_total_discount, 0);  ?> руб.</th> <?}
                            elseif($project_total_discount < 3500 && $project_total_discount > 0) { $project_total_discount = 3500; echo round($project_total_discount, 0);  ?> руб.</th>
                                </span> <span class="dop" style="font-size: 9px;" > * минимальная сумма заказа 3500р. </span>
                            <? } else echo round($project_total_discount, 0);  ?> руб.</span> <span class="dop" style="font-size: 9px;" ></span></th>
                        <?php }
                        else { ?>
                        <th>Итого</th>
                        <th id="project_total">
                        <span class="sum">
                            <?php
                            if ($this->item->new_project_sum == 0) {
                                if($project_total < 3500 && $project_total > 0 && $dealer_gm_mounting_sum_11 != 0)  { $project_total = 3500; }
                                echo round($project_total, 2);
                            } else {
                                echo round($this->item->new_project_sum, 2);
                            }
                            } ?>
                        </span>
                            <span class="dop" style="font-size: 9px;">
                        <? if ($project_total <= 3500 && $project_total_discount > 0 && $dealer_gm_mounting_sum_11 != 0) { ?>
                            * минимальная сумма заказа 3500р.<?}?>
                            </span>
                        </th>
                    </tr>
                    <?php if ($user->dealer_type != 2) { ?>
                        <tr>
                        <td></td>
                        <td id="calculation_total1"><?php echo round($calculation_total_11, 0) ?></td>
                        <td id="calculation_total2"><?php echo round($dealer_gm_mounting_sum_11, 0) ?></td>
                        <td id="calculation_total3"><?php echo round($project_total_11, 0); ?></td>
                        </tr>
                    <? } ?>
                    <tr>
                        <th class="section_header" id="sh_estimate"> Сметы <i class="fa fa-sort-desc" aria-hidden="true"></i></th>
                    </tr>
                    <?php foreach ($calculations as $calculation) { ?>
                    <tr class="section_estimate" id="section_estimate_<?= $calculation->id; ?>" style="display:none;">
                        <td><?php echo $calculation->calculation_title; ?></td>
                        <td>
                            <?php
                            $path = "/costsheets/" . md5($calculation->id . "-0-0") . ".pdf";

                            $pdf_names[] = array("name" => $calculation->calculation_title, "filename" => md5($calculation->id . "-0-0") . ".pdf", "id" => $calculation->id);
                            ?>
                            <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>
                        <?php }
                        $json = json_encode($pdf_names); ?>
                    </tr>
                    <?php if (count($calculations) > 0) { ?>
                        <tr class="section_estimate" style="display:none;">
                            <td><b>Отправить все сметы <b></td>
                        </tr>
                        <tr class="section_estimate" style="display:none;">
                            <td>
                                <div class="email-all" style="float: left;">
                                    <input list="email" name="all-email" id="all-email1" class="form-control" style="width:200px;"
                                        placeholder="Адрес эл.почты" type="text">
                                        <datalist id="email">
                                            <? foreach ($contact_email AS $em) {?>
                                            <option value="<?=$em->contact;?>"> <?}?>
                                        </datalist>
                                </div>
                                <div class="file_data">
                                    <div class="file_upload">
                                        <input type="file" class="dopfile" name="dopfile" id="dopfile">
                                    </div>
                                    <div class="file_name"></div>
                                    <script>jQuery(function () {
                                            jQuery("div.file_name").html("Файл не выбран");
                                            jQuery("div.file_upload input.dopfile").change(function () {
                                                var filename = jQuery(this).val().replace(/.*\\/, "");
                                                jQuery("div.file_name").html((filename != "") ? filename : "Файл не выбран");
                                            });
                                        });</script>
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-primary" id="send_all_to_email1" type="button">Отправить</button>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if ($this->item->project_mounter != 'Монтажная бригада ГМ') { ?>
                        <tr>
                            <th id="sh_mount"> Наряд на монтаж <i class="fa fa-sort-desc" aria-hidden="true"></i></th>
                        </tr>
                        <?php foreach ($calculations as $calculation) { ?>
                            <tr class="section_mount" id="section_mount_<?= $calculation->id; ?>" style="display:none;">
                            <td><?php echo $calculation->calculation_title; ?></td>
                            <td>
                                <?php $path = "/costsheets/" . md5($calculation->id . "-2") . ".pdf"; ?>
                                <?php $pdf_names_mount[] = array("name" => $calculation->calculation_title, "filename" => md5($calculation->id . "-2") . ".pdf", "id" => $calculation->id); ?>
                                <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                    <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                                <?php } else { ?>
                                    После договора
                                <?php } ?>
                            </td>
                        <?php }
                        $json1 = json_encode($pdf_names_mount); ?>
                        </tr>
                        <?php if (count($calculations) > 0) { ?>
                            <tr class="section_mount" style="display:none;">
                                <td><b>Отправить все наряды на монтаж<b></td>
                            </tr>
                            <tr class="section_mount" style="display:none;">
                                <td>
                                    <div class="email-all" style="float: left;">
                                        <input name="all-email" id="all-email2" class="form-control" style="width:200px;"
                                                value="" placeholder="Адрес эл.почты" type="text">

                                    </div>
                                    <div class="file_data">
                                        <div class="file_upload">
                                            <input type="file" class="dopfile1" name="dopfile1" id="dopfile1">
                                        </div>
                                        <div class="file_name1"></div>
                                        <script>jQuery(function () {
                                                jQuery("div.file_name1").html("Файл не выбран");
                                                jQuery("div.file_upload input.dopfile1").change(function () {
                                                    var filename = jQuery(this).val().replace(/.*\\/, "");
                                                    jQuery("div.file_name1").html((filename != "") ? filename : "Файл не выбран");
                                                });
                                            });</script>
                                    </div>
                                </td>
                                <td>
                                    <button class="btn btn-primary" id="send_all_to_email2" type="button">Отправить</button>
                                </td>
                            </tr>
                        <?php } ?>
                    <? } ?>
                    <!-------------------------------- Общая смета для клиента ------------------------------------------>
                    <tr>
                        <td><b>Отправить общую смету <b></td>
                        <td>
                            <?php
                            $path = "/costsheets/" . md5($this->item->id . "-9") . ".pdf"; if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                            <?php } else { ?>
                                -
                            <?php }
                            $pdf_names[] = array("name" => "Подробная смета", "filename" => md5($this->item->id . "-9") . ".pdf", "id" => $this->item->id);
                            $json2 = json_encode($pdf_names); ?>
                        </td>
                        <td></td>
                    </tr>
                    <?  if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                        <tr >
                            <td>
                                <div class="email-all" style="float: left;">
                                    <input list="email" name="all-email" id="all-email3" class="form-control" style="width:200px;"
                                        placeholder="Адрес эл.почты" type="text">
                                        <datalist id="email">
                                            <? foreach ($contact_email AS $em) {?>
                                            <option value="<?=$em->contact;?>"> <?}?>
                                        </datalist>
                                </div>
                                <div class="file_data">
                                    <div class="file_upload">
                                        <input type="file" class="dopfile2" name="dopfile2" id="dopfile2">
                                    </div>
                                    <div class="file_name2"></div>
                                    <script>jQuery(function () {
                                            jQuery("div.file_name2").html("Файл не выбран");
                                            jQuery("div.file_upload input.dopfile2").change(function () {
                                                var filename = jQuery(this).val().replace(/.*\\/, "");
                                                jQuery("div.file_name2").html((filename != "") ? filename : "Файл не выбран");
                                            });
                                        });</script>
                                </div>
                            </td>
                            <td>

                                <button class="btn btn-primary" id="send_all_to_email3" type="button">Отправить</button>
                            </td>
                            <td>

                            </td>
                        </tr>
                        <!-- общий наряд на монтаж--> 
                    <tr>
                    <td><b>Общий наряд на монтаж <b></td>
                    <td>
                        <?php
                        $path = "/costsheets/" . md5($this->item->id . "-10") . ".pdf"; if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                            <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                        <?php } else { ?>
                            -
                        <?php }
                        $pdf_names[] = array("name" => "Подробная смета", "filename" => md5($this->item->id . "-10") . ".pdf", "id" => $this->item->id);
                        $json2 = json_encode($pdf_names); ?>
                    </td>
                    <td></td>
                    </tr>
                    <? }?>
                </table>
                <?php if ($user->dealer_type == 2) { ?>
                    <button class="btn btn-primary" type="submit" form="form-client" id="client_order">Закончить
                        формирование заказа
                    </button>
                    <?php if ($this->item->project_status == 7) {
                        // регистрационная информация (логин, пароль #1)
                        // registration info (login, password #1)
                        $mrh_login = "demo";
                        $mrh_pass1 = "password_1";

                        // номер заказа
                        // number of order
                        $inv_id = 0;

                        // описание заказа
                        // order description
                        $inv_desc = "Оплата заказа в Тестовом магазине ROBOKASSA";

                        // сумма заказа
                        // sum of order
                        $out_summ = $project_total_discount;;

                        // тип товара
                        // code of goods
                        $shp_item = 1;

                        // язык
                        // language
                        $culture = "ru";

                        // кодировка
                        // encoding
                        $encoding = "utf-8";

                        // формирование подписи
                        // generate signature
                        $crc = md5("$mrh_login:$out_summ:$inv_id:$mrh_pass1:shp_Item=$shp_item");

                        // HTML-страница с кассой
                        // ROBOKASSA HTML-page
                        print "<html><script language=JavaScript " .
                            "src='https://auth.robokassa.ru/Merchant/PaymentForm/FormMS.js?" .
                            "MerchantLogin=$mrh_login&OutSum=$out_summ&InvoiceID=$inv_id" .
                            "&Description=$inv_desc&SignatureValue=$crc&shp_Item=$shp_item" .
                            "&Culture=$culture&Encoding=$encoding'></script></html>";

                    }
                } ?>
            </div>
            <?php foreach ($calculations as $k => $calculation) { ?>
                <?php $mounters = json_decode($calculation->mounting_sum); ?>
                <?php $filename = "/calculation_images/" . md5("calculation_sketch" . $calculation->id) . ".png"; ?>
                <div class="tab-pane" id="calculation<?php echo $calculation->id; ?>" role="tabpanel">
                    <h3><?php echo $calculation->calculation_title; ?></h3>
                    <? if (!empty($filename)):?>
                        <div class="sketch_image_block">
                            <h3 class="section_header">
                                Чертеж <i class="fa fa-sort-desc" aria-hidden="true"></i>
                            </h3>
                            <div class="section_content">
                                <img class="sketch_image" src="<?php echo $filename.'?t='.time(); ?>" style="width:80vw;"/>
                            </div>
                        </div>
                    <? endif; ?>
                    <div class="row-fluid">
                        <div class="span6">
                            <?if($calculation->n1 && $calculation->n2 && $calculation->n3):?>
                                <h4>Материал</h4>
                                <div>
                                    Тип потолка: <?php echo $calculation->n1; ?>
                                </div>
                                <div>
                                    Тип фактуры: <?php echo $calculation->n2; ?>
                                </div>
                                <div>
                                    Производитель, ширина: <?php echo $calculation->n3; ?>
                                </div>

                                <?php if ($calculation->color > 0) { ?>
                                    <?php $color_model = Gm_ceilingHelpersGm_ceiling::getModel('color'); ?>
                                    <?php $color = $color_model->getData($calculation->color); ?>
                                    <div>
                                        Цвет: <?php echo $color->colors_title; ?> <img src="/<?php echo $color->file; ?>"
                                                                                    alt=""/>
                                    </div>
                                <?php } ?>
                                <h4>Размеры помещения</h4>
                                <div>
                                    Площадь, м<sup>2</sup>: <?php echo $calculation->n4; ?>
                                </div>
                                <div>
                                    Периметр, м: <?php echo $calculation->n5; ?>
                                </div>
                                <?php if ($calculation->n6 > 0) {?>
                                    <div>
                                        <h4> Вставка</h4>
                                    </div>
                                    <? if ($calculation->n6 == 314) {?>
                                        <div> Белая </div>
                                    <?php } else  {?>
                                        <?php $color_model_1 = Gm_ceilingHelpersGm_ceiling::getModel('components'); ?>
                                        <?php $color_1 = $color_model_1->getColorId($calculation->n6); ?>
                                        <div>
                                            Цветная : <?php echo $color_1[0]->title; ?> <img style='width: 50px; height: 30px;' src="/<?php echo $color_1[0]->file; ?>"
                                                                                            alt=""/>
                                        </div>
                                    <?php }?>
                                <?} endif; ?>
                            <?php if ($calculation->n16) { ?>
                                <div>
                                    Скрытый карниз: <?php echo $calculation->n16; ?>
                                </div>
                            <?php } ?>

                            <?php if ($calculation->n12) { ?>
                                <h4>Установка люстры</h4>
                                <?php echo $calculation->n12; ?> шт.
                            <?php } ?>

                            <?php if ($calculation->n13) { ?>
                                <h4>Установка светильников</h4>
                                <?php foreach ($calculation->n13 as $key => $n13_item) {
                                    echo "<b>Количество:</b> " . $n13_item->n13_count . " шт - <b>Тип:</b>  " . $n13_item->type_title . " - <b>Размер:</b> " . $n13_item->component_title . "<br>";
                                    ?>
                                <?php }
                            } ?>

                            <?php if ($calculation->n14) { ?>
                                <h4>Обвод трубы</h4>
                                <?php foreach ($calculation->n14 as $key => $n14_item) {
                                    echo "<b>Количество:</b> " . $n14_item->n14_count . " шт  -  <b>Диаметр:</b>  " . $n14_item->component_title . "<br>";
                                    ?>
                                <?php }
                            } ?>

                            <?php if ($calculation->n15) { ?>
                                <h4>Шторный карниз Гильдии мастеров</h4>
                                <?php foreach ($calculation->n15 as $key => $n15_item) {
                                    echo "<b>Количество:</b> " . $n15_item->n15_count . " шт - <b>Тип:</b>   " . $n15_item->type_title . " <b>Длина:</b> " . $n15_item->component_title . "<br>";
                                    ?>
                                <?php }
                            } ?>
                            <?php if ($calculation->n27> 0) { ?>
                                <h4>Шторный карниз</h4>
                                <? if ($calculation->n16) echo "Скрытый карниз"; ?>
                                <? if (!$calculation->n16) echo "Обычный карниз"; ?>
                                <?php echo $calculation->n27; ?> м.
                            <?php } ?>

                            <?php if ($calculation->n26) { ?>
                                <h4>Светильники Эcola</h4>
                                <?php foreach ($calculation->n26 as $key => $n26_item) {
                                    echo "<b>Количество:</b> " . $n26_item->n26_count . " шт - <b>Тип:</b>  " . $n26_item->component_title_illuminator . " -  <b>Лампа:</b> " . $n26_item->component_title_lamp . "<br>";
                                    ?>
                                <?php }
                            } ?>

                            <?php if ($calculation->n22) { ?>
                                <h4>Вентиляция</h4>
                                <?php foreach ($calculation->n22 as $key => $n22_item) {
                                    echo "<b>Количество:</b> " . $n22_item->n22_count . " шт - <b>Тип:</b>   " . $n22_item->type_title . " - <b>Размер:</b> " . $n22_item->component_title . "<br>";
                                    ?>
                                <?php }
                            } ?>

                            <?php if ($calculation->n23) { ?>
                                <h4>Диффузор</h4>
                                <?php foreach ($calculation->n23 as $key => $n23_item) {
                                    echo "<b>Количество:</b> " . $n23_item->n23_count . " шт - <b>Размер:</b>  " . $n23_item->component_title . "<br>";
                                    ?>
                                <?php }
                            } ?>

                            <?php if ($calculation->n29) { ?>
                                <h4>Переход уровня</h4>
                                <?php foreach ($calculation->n29 as $key => $n29_item) {
                                    echo "<b>Количество:</b> " . $n29_item->n29_count . " м - <b>Тип:</b>  " . $n29_item->type_title . " <br>";
                                    ?>
                                <?php }
                            } ?>
                            <h4>Прочее</h4>
                            <?php if ($calculation->n9> 0) { ?>
                                <div>
                                    Углы, шт.: <?php echo $calculation->n9; ?>
                                </div>
                            <?php } ?>
                            <?php if ($calculation->n10> 0) { ?>
                                <div>
                                    Криволинейный вырез, м: <?php echo $calculation->n10; ?>
                                </div>
                            <?php } ?>
                            <?php if ($calculation->n11> 0) { ?>
                                <div>
                                    Внутренний вырез, м: <?php echo $calculation->n11; ?>
                                </div>
                            <?php } ?>
                            <?php if ($calculation->n7> 0) { ?>
                                <div>
                                    Крепление в плитку, м: <?php echo $calculation->n7; ?>
                                </div>
                            <?php } ?>
                            <?php if ($calculation->n8> 0) { ?>
                                <div>
                                    Крепление в керамогранит, м: <?php echo $calculation->n8; ?>
                                </div>
                            <?php } ?>
                            <?php if ($calculation->n17> 0) { ?>
                                <div>
                                    Закладная брусом, м: <?php echo $calculation->n17; ?>
                                </div>
                            <?php } ?>
                            <?php if ($calculation->n19> 0) { ?>
                                <div>
                                    Провод, м: <?php echo $calculation->n19; ?>
                                </div>
                            <?php } ?>
                            <?php if ($calculation->n20> 0) { ?>
                                <div>
                                    Разделитель, м: <?php echo $calculation->n20; ?>
                                </div>
                            <?php } ?>
                            <?php if ($calculation->n21> 0) { ?>
                                <div>
                                    Пожарная сигнализация, м: <?php echo $calculation->n21; ?>
                                </div>
                            <?php } ?>

                            <?php if ($calculation->dop_krepezh> 0) { ?>
                                <div>
                                    Дополнительный крепеж: <?php echo $calculation->dop_krepezh; ?>
                                </div>
                            <?php } ?>

                            <?php if ($calculation->n24> 0) { ?>
                                <div>
                                    Сложность доступа к месту монтажа, м: <?php echo $calculation->n24; ?>
                                </div>
                            <?php } ?>

                            <?php if ($calculation->n30> 0) { ?>
                                <div>
                                    Парящий потолок, м: <?php echo $calculation->n30; ?>
                                </div>
                            <?php } ?>
                            <?php if ($calculation->n32> 0) { ?>
                                <div>
                                    Слив воды, кол-во комнат: <?php echo $calculation->n32; ?>
                                </div>
                            <?php } ?>
                            <? $extra_mounting = (array) json_decode($calculation->extra_mounting);?>
                            <?php if (!empty($extra_mounting) ) { ?>
                                <div>
                                    <h4>Дополнительные работы</h4>
                                    <? foreach($extra_mounting as $dop) {
                                        echo "<b>Название:</b> " . $dop->title .  "<br>";
                                    }?>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="span6">

                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
    </form>
    <div class="container">
        <div class="row" style="padding-top: 1em;">
            <div class="col-xl-6 item_fields project-edit front-end-edit">
                <form id="form-project" action="<?php echo JRoute::_('index.php?option=com_gm_ceiling&task=project.approve'); ?>" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
                    <h4>Информация по проекту № <?php echo $this->item->id ?></h4>
                    <input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>"/>
                    <input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>"/>
                    <input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>"/>
                    <input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>"/>
                    <input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>"/>
                    <?php if ($this->item->project_status == 3) { ?>
                        <input type="hidden" name="jform[project_status]" value="4"/>
                    <?php } ?>
                    <?php if (empty($this->item->created_by)): ?>
                        <input type="hidden" name="jform[created_by]" value="<?php echo JFactory::getUser()->id; ?>"/>
                    <?php else: ?>
                        <input type="hidden" name="jform[created_by]" value="<?php echo $this->item->created_by; ?>"/>
                    <?php endif; ?>
                    <?php if (empty($this->item->modified_by)): ?>
                        <input type="hidden" name="jform[modified_by]" value="<?php echo JFactory::getUser()->id; ?>"/>
                    <?php else: ?>
                        <input type="hidden" name="jform[modified_by]" value="<?php echo $this->item->modified_by; ?>"/>
                    <?php endif; ?>
                    <?php $jdate = new JDate(JFactory::getDate($this->item->project_mounting_date)); ?>
                    <input name = "jform[project_new_calc_date]" id = "jform_project_new_calc_date" value="<?php if (isset($this->item->project_calculation_date)) { echo $this->item->project_calculation_date; } ?>" type="hidden">
                    <input name = "jform[project_gauger]" id = "jform_project_gauger" value="<?php if ($this->item->project_calculator != null) { echo $this->item->project_calculator; } ?>" type="hidden">
                    <input id="jform_project_gauger_old" type="hidden" name="jform_project_gauger_old" value="<?php if ($this->item->project_calculator != null) { echo $this->item->project_calculator; } else { echo "0"; } ?>"/>
                    <input id="jform_project_calculation_date_old" type="hidden" name="jform_project_calculation_date_old" value="<?php if (isset($this->item->project_calculation_date)) { echo $this->item->project_calculation_date;} ?>"/>
                    <input id="jform_project_mounting_date" type="hidden" name="jform[project_mounting_date]" value="<?php if (isset($this->item->project_mounting_date)) { echo $this->item->project_mounting_date; } ?>"/>
                    <input id="jform_project_mounter" type="hidden" name="jform[project_mounting]" value="<?php if (isset($this->item->project_mounter)) { echo $this->item->project_mounter; } ?>"/>
                    <input id="jform_project_mounter_old" type="hidden" name="jform_project_mounting_old" value="<?php if (isset($this->item->project_mounter)) { echo $this->item->project_mounter; } ?>"/>
                    <input id="jform_project_mounting_date_old" type="hidden" name="jform_project_mounting_date_old" value="<?php if (isset($this->item->project_mounting_date)) { echo $this->item->project_mounting_date; } ?>"/>
                    <input type="hidden" name="option" value="com_gm_ceiling"/>
                    <input type="hidden" name="task" value="project.approve"/>
                    <?php echo JHtml::_('form.token'); ?>
                    <table class="table">
                        <tr>
                            <th>Номер договора</th>
                            <td><?php echo $this->item->id; ?></td>
                        </tr>
                        <tr>
                            <th>Статус проекта</th>
                            <td>
                                <?php 
                                    if ($this->item->project_status == 1) {
                                        $status = "Ждет замера";
                                    } else if ($this->item->project_status == 5) {
                                        $status = "В производстве";
                                    } else if ($this->item->project_status == 6) {
                                        $status = "На раскрое";
                                    } else if ($this->item->project_status == 7) {
                                        $status = "Укомплектован";
                                    } else if ($this->item->project_status == 8) {
                                        $status = "Выдан";
                                    } else if ($this->item->project_status == 9) {
                                        $status = "Деактевирован";
                                    } else if ($this->item->project_status == 10) {
                                        $status = "Ожидает монтаж";
                                    } else if ($this->item->project_status == 11) {
                                        $status = "Монтаж выполнен";
                                    } else if ($this->item->project_status == 12) {
                                        $status = "Закрыт";
                                    } else if ($this->item->project_status == 13) {
                                        $status = "Ожидает оплаты";
                                    } else if ($this->item->project_status == 14) {
                                        $status = "Оплачен";
                                    } else if ($this->item->project_status == 15) {
                                        $status = "Отказ от сотруднечества";
                                    } else if ($this->item->project_status == 16) {
                                        $status = "Монтаж";
                                    } else if ($this->item->project_status == 17) {
                                        $status = "Монтаж недовыполнен";
                                    } else if ($this->item->project_status == 19) {
                                        $status = "Собран";
                                    } else if ($this->item->project_status == 22) {
                                        $status = "Отказ от производства";
                                    } else if ($this->item->project_status == 4) {
                                        $status = "Не назначен на монтаж";
                                    }
                                    echo $status; 
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <?php if ($this->item->project_status == 1) { ?>
                                <th>Дата замера</th>
                                <td>
                                    <?php if ($this->item->project_calculation_date == "0000-00-00 00:00:00") { ?>
                                        -
                                    <?php } else { ?>
                                        <?php $jdate = new JDate(JFactory::getDate($this->item->project_calculation_date)); ?>
                                        <?php echo $jdate->format('d.m.Y H:i'); ?>
                                    <?php } ?>
                                </td>
                            <?php } else if ($this->item->project_status == 11 || $this->item->project_status == 12 || $this->item->project_status != 17) { ?>
                                <th>Дата монтажа</th>
                                <td>
                                    <?php $jdate = new JDate(JFactory::getDate($this->item->project_mounting_date)); ?>
                                    <?php echo $jdate->format('d.m.Y H:i'); ?>
                                </td>
                            <?php } else { ?>
                                <th>Удобная дата монтажа для клиента</th>
                                <td>
                                    <?php if ($this->item->project_mounting_date == "0000-00-00 00:00:00") { ?>
                                        -
                                    <?php } else { ?>
                                        <?php $jdate = new JDate(JFactory::getDate($this->item->project_mounting_date)); ?>
                                        <?php echo $jdate->format('d.m.Y H:i'); ?>
                                    <?php } ?>
                                </td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
                            <td><?php echo $this->item->client_id; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?></th>
                            <?php 
                                $mod = Gm_ceilingHelpersGm_ceiling::getModel('projectform');
                                $contact = $mod->getData($this->item->id);
                            ?>
                            <td><?php echo $contact->client_contacts; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?></th>
                            <td><?php echo $this->item->project_info; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_GM_MANAGER_NOTE'); ?></th>
                            <td><?php echo $this->item->gm_manager_note; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_GM_CALCULATOR_NOTE'); ?></th>
                            <td><?php echo $this->item->gm_calculator_note; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_GM_CHIEF_NOTE'); ?></th>
                            <td><textarea name="jform[gm_chief_note]" id="jform_gm_chief_note" placeholder="Примечание начальника МС ГМ" aria-invalid="false"><?php echo $this->item->gm_chief_note; ?></textarea></td>
                        </tr>
                        <tr>
                            <?php if ($this->item->project_status <= 4) { ?>
                                <th>Замерщик</th>
                                <?php 
                                    $gauger_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
                                    $gauger = $gauger_model->getGauger($this->item->id); 
                                ?>
                                <td><?php echo $gauger->name; ?></td>
                            <?php } else { ?>
                                <th>Монтажная бригада</th>
                                <?php 
                                    $mount_model = Gm_ceilingHelpersGm_ceiling::getModel('project');
                                    $mount = $mount_model->getMount($this->item->id); 
                                ?>
                                <td><?php echo $mount->name; ?></td>
                            <?php } ?>
                        </tr>
                    </table>
                    <?php if ($this->item->project_status == 1) { ?>
                        <h4 style="text-align:center;">Изменить замерщика, время и дату замера</h4>
                        <div class="calendar_wrapper" style="background: #ffffff">
                            <table>
                                <tr>
                                    <td>
                                        <button id="button-prev" type="button" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i></button>
                                    </td>
                                    <td style="width: 100%">
                                        <div id="calendar1" style="padding: 1em">
                                            <?php echo $calendar1; ?>
                                        </div>
                                        <div id="calendar2" style="padding: 1em">
                                            <?php echo $calendar2; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <button id="button-next" type="button" class="btn btn-primary"><i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    <?php } else if ($this->item->project_status != 11 || $this->item->project_status != 12 || $this->item->project_status == 17) { ?>
                        <h4 style="text-align:center;">Назначить/изменить монтажную бригаду, время и дату</h4>
                        <div class="calendar_wrapper" style="background: #ffffff">
                            <table>
                                <tr>
                                    <td>
                                        <button id="button-prev" type="button" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i></button>
                                    </td>
                                    <td style="width: 100%">
                                        <div id="calendar1" style="padding: 1em">
                                            <?php echo $calendar1; ?>
                                        </div>
                                        <div id="calendar2" style="padding: 1em">
                                            <?php echo $calendar2; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <button id="button-next" type="button" class="btn btn-primary"><i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    <?php } ?>
                    <?php if ($userId == $user->dealer_id) { ?>
                        <input name="type" value="chief" type="hidden">
                    <?php } else { ?>
                        <input name="type" value="gmchief" type="hidden">
                    <?php } ?>
                    <div class="control-group">
                        <div class="controls">
                            <?php if ($this->canSave): ?>
                                <button type="submit" class="validate btn btn-primary">Сохранить</button>
                            <?php endif; ?>
                            <a class="btn btn-success"
                                href="<?php if ($this->item->project_status == 4)  echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=chiefprojects');
                                elseif ($userId == $user->dealer_id)  echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=chief');
                                else echo JRoute::_('index.php?option=com_gm_ceiling&view=projects&type=gmchief'); ?>"
                                title="">Вернуться к монтажам</a>
                        </div>
                    </div>
                </form>
            </div>
            <?php if($user->dealer_type == 0) { ?>
                <div class="col-xl-6">
                    <div class="comment">
                        <label style="font-weight: bold;"> История клиента: </label>
                        <textarea id="comments" class="input-comment" rows=11 readonly style="resize: none; outline: none;"></textarea>
                        <table>
                            <tr>
                                <td><label style="font-weight: bold;"> Добавить комментарий: </label></td>
                            </tr>
                            <tr>
                                <td width = 100%><textarea  style="resize: none;" class = "inputactive" id="new_comment" placeholder="Введите новое примечание"></textarea></td>
                                <td><button class="btn btn-primary" type="button" id="add_comment"><i class="fa fa-paper-plane" aria-hidden="true"></i>
                                </button></td>
                            </tr>
                        </table>
                    </div>
                </div>
            <? } ?>
        </div>
    </div>
    <div id="modal-window-container-tar">
        <button id="close-tar" type="button"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="modal-window-choose-tar">
            <p id="date-modal"></p>
            <?php if ($whatCalendar == 0) { ?>
                <p><strong>Выберите время замера:</strong></p>
                <p><table id="projects_gaugers"></table></p>
            <?php } else { ?>
                <p><strong>Выберите монтажника:</strong></p>
                <p><select name="mounters" id="mounters"></select></p>
                <p style="margin-bottom: 0;"><strong>Монтажники:</strong></p>
                <div id="mounters_names"></div>
                <div id="projects_brigade_container"></div>
                <p style="margin-top: 1em;"><strong>Выберите время начала монтажа:</strong></p>
                <p><select name="hours" id='hours'></select></p>
                <p><button type="button" id="save-choise-tar" class="btn btn-primary">Ок</button></p>
            <?php } ?>
        </div>
    </div>
<?php } ?>

<script>

    var preloader = '<?=parent::getPreloaderNotJS();?>';

    whatCalendar = <?php echo $whatCalendar; ?>
    // листание календаря
    month_old1 = 0;
    year_old1 = 0;
    month_old2 = 0;
    year_old2 = 0;
    jQuery("#button-next").click(function () {
        month1 = <?php echo $month1; ?>;
        year1 = <?php echo $year1; ?>;
        month2 = <?php echo $month2; ?>;
        year2 = <?php echo $year2; ?>;
        if (month_old1 != 0) {
            month1 = month_old1;
            year1 = year_old1;
            month2 = month_old2;
            year2 = year_old2;
        }
        if (month1 == 12) {
            month1 = 1;
            year1++;
        } else {
            month1++;
        }
        if (month2 == 12) {
            month2 = 1;
            year2++;
        } else {
            month2++;
        }
        month_old1 = month1;
        year_old1 = year1;
        month_old2 = month2;
        year_old2 = year2;
        update_calendar(month1, year1);
        update_calendar2(month2, year2);
    });
    jQuery("#button-prev").click(function () {
        month1 = <?php echo $month1; ?>;
        year1 = <?php echo $year1; ?>;
        month2 = <?php echo $month2; ?>;
        year2 = <?php echo $year2; ?>;
        if (month_old1 != 0) {
            month1 = month_old1;
            year1 = year_old1;
            month2 = month_old2;
            year2 = year_old2;
        }
        if (month1 == 1) {
            month1 = 12;
            year1--;
        } else {
            month1--;
        }
        if (month2 == 1) {
            month2 = 12;
            year2--;
        } else {
            month2--;
        }
        month_old1 = month1;
        year_old1 = year1;
        month_old2 = month2;
        year_old2 = year2;
        update_calendar(month1, year1);
        update_calendar2(month2, year2);
    });

    function update_calendar(month, year) {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=UpdateCalendarTar",
            data: {
                id: <?php echo $userId; ?>,
                id_dealer: <?php echo $user->dealer_id; ?>,
                flag: <?php if ($whatCalendar == 0) { echo 3; } else { echo 2; } ?>,
                month: month,
                year: year,
            },
            success: function (msg) {
                jQuery("#calendar1").empty();
                jQuery("#calendar1").append(msg);
                Today(day, NowMonth, NowYear);
                var datesession = jQuery("#jform_project_mounting_date").val();  
                console.log(datesession);
                if (datesession != undefined) {
                    if (whatCalendar == 0) {
                        jQuery("#current-monthD"+datesession.substr(8, 2)+"DM"+datesession.substr(5, 2)+"MY"+datesession.substr(0, 4)+"YI"+<?php echo $userId; ?>+"IC0C").addClass("change");
                    } else {
                        jQuery("#current-monthD"+datesession.substr(8, 2)+"DM"+datesession.substr(5, 2)+"MY"+datesession.substr(0, 4)+"YI"+<?php echo $userId; ?>+"IC1C").addClass("change");
                    }
                }
            },
            dataType: "text",
            timeout: 10000,
            error: function () {
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка при попытке обновить календарь. Сервер не отвечает"
                });
            }
        });
    }
    function update_calendar2(month, year) {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=UpdateCalendarTar",
            data: {
                id: <?php echo $userId; ?>,
                month: month,
                year: year,
                id_dealer: <?php echo $user->dealer_id; ?>,
                flag: <?php if ($whatCalendar == 0) { echo 3; } else { echo 2; } ?>,
            },
            success: function (msg) {
                jQuery("#calendar2").empty();
                jQuery("#calendar2").append(msg);
                Today(day, NowMonth, NowYear);
                var datesession = jQuery("#jform_project_mounting_date").val();  
                console.log(datesession);
                if (datesession != undefined) {
                    if (whatCalendar == 0) {
                        jQuery("#current-monthD"+datesession.substr(8, 2)+"DM"+datesession.substr(5, 2)+"MY"+datesession.substr(0, 4)+"YI"+<?php echo $userId; ?>+"IC0C").addClass("change");
                    } else {
                        jQuery("#current-monthD"+datesession.substr(8, 2)+"DM"+datesession.substr(5, 2)+"MY"+datesession.substr(0, 4)+"YI"+<?php echo $userId; ?>+"IC1C").addClass("change");
                    }
                }
            },
            dataType: "text",
            timeout: 10000,
            error: function () {
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка при попытке обновить календарь. Сервер не отвечает"
                });
            }
        });
    }
    //----------------------------------------

    //скрыть модальное окно
    jQuery(document).mouseup(function (e) {
        var div = jQuery("#modal-window-choose-tar");
        if (!div.is(e.target)
            && div.has(e.target).length === 0) {
            jQuery("#close-tar").hide();
            jQuery("#modal-window-container-tar").hide();
            jQuery("#modal-window-choose-tar").hide();
        }
    });
    //--------------------------------------------------

    // функция подсвета сегоднешней даты
    var Today = function (day, month, year) {
        month++;
        if (whatCalendar == 0) {
            jQuery("#current-monthD"+day+"DM"+month+"MY"+year+"YI"+<?php echo $userId; ?>+"IC0C").addClass("today");
        } else {
            jQuery("#current-monthD"+day+"DM"+month+"MY"+year+"YI"+<?php echo $userId; ?>+"IC1C").addClass("today");
        }
    }   
    //------------------------------------------

    // функция чтобы другая функция выполнилась позже чем document ready
    Function.prototype.process= function(state){
        var process= function(){
            var args= arguments;
            var self= arguments.callee;
            setTimeout(function(){
                self.handler.apply(self, args);
            }, 0 )
        }
        for(var i in state) process[i]= state[i];
        process.handler= this;
        return process;
    }
    //------------------------------------------

    // показать историю
    function show_comments() {
        var id_client = <?php echo $this->item->id_client;?>;
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=selectComments",
            data: {
                id_client: id_client
            },
            dataType: "json",
            async: true,
            success: function (data) {
                var comments_area = document.getElementById('comments');
                comments_area.innerHTML = "";
                var date_t;
                for (var i = 0; i < data.length; i++) {
                    date_t = new Date(data[i].date_time);
                    comments_area.innerHTML += formatDate(date_t) + "\n" + data[i].text + "\n----------\n";
                }
                comments_area.scrollTop = comments_area.scrollHeight;
            },
            error: function (data) {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка вывода примечаний"
                });
            }
        });
    }
    //------------------------------------------------------

    // форматирование даты для вывода
    function formatDate(date) {
        var dd = date.getDate();
        if (dd < 10) dd = '0' + dd;
        var mm = date.getMonth() + 1;
        if (mm < 10) mm = '0' + mm;
        var yy = date.getFullYear();
        if (yy < 10) yy = '0' + yy;
        var hh = date.getHours();
        if (hh < 10) hh = '0' + hh;
        var ii = date.getMinutes();
        if (ii < 10) ii = '0' + ii;
        var ss = date.getSeconds();
        if (ss < 10) ss = '0' + ss;
        return dd + '.' + mm + '.' + yy + ' ' + hh + ':' + ii + ':' + ss;
    }
    // ------------------------------------------------------------------------

    // при нажатии на энтер добавляется коммент
    document.getElementById('new_comment').onkeydown = function (e) {
        if (e.keyCode === 13) {
            document.getElementById('add_comment').click();
        }
    }
    // ----------------------------------------------------------------------

    jQuery(document).ready(function () {

        trans();

        window.time_gauger = undefined;
        window.gauger = undefined;

        // открытие модального окна с календаря и получение даты и вывода свободных монтажников или замерщиков
        jQuery("#calendar1, #calendar2").on("click", ".current-month, .not-full-day, .change, .full-day", function() {
            window.idDay = jQuery(this).attr("id");
            reg1 = "D(.*)D";
            reg2 = "M(.*)M";
            reg3 = "Y(.*)Y";
            var d = idDay.match(reg1)[1];
            var m = idDay.match(reg2)[1];
            if (d.length == 1) {
                d = "0"+d;
            }
            if (m.length == 1) {
                m = "0"+m;
            }
            window.date = idDay.match(reg3)[1]+"-"+m+"-"+d;
            jQuery("#modal-window-container-tar").show();
            jQuery("#modal-window-choose-tar").show("slow");
            jQuery("#close-tar").show();
            if (whatCalendar == 0) {
                jQuery.ajax({
                    type: 'POST',
                    url: "/index.php?option=com_gm_ceiling&task=calculations.GetBusyGauger",
                    data: {
                        date: date,
                        dealer: <?php echo $user->dealer_id; ?>,
                    },
                    success: function(data) {
                        Array.prototype.diff = function(a) {
                            return this.filter(function(i) {return a.indexOf(i) < 0;});
                        };
                        AllGauger = <?php echo json_encode($AllGauger); ?>;
                        data = JSON.parse(data); // замеры
                        console.log(data);
                        AllTime = ["09:00:00", "10:00:00", "11:00:00", "12:00:00", "13:00:00", '14:00:00', "15:00:00", "16:00:00", "17:00:00", "18:00:00", "19:00:00", "20:00:00"];
                        var TableForSelect = '<tr><th class="caption"></th><th class="caption">Время</th><th class="caption">Адрес</th><th class="caption">Замерщик</th></tr>';
                        AllTime.forEach( elementTime => {
                            var t = elementTime.substr(0, 2);
                            t++;
                            Array.from(AllGauger).forEach(function(elementGauger) {
                                var emptytd = 0;
                                Array.from(data).forEach(function(elementProject) {
                                    if (elementProject.project_calculator == elementGauger.id && elementProject.project_calculation_date.substr(11) == elementTime) {
                                        var timesession = jQuery("#jform_new_project_calculation_daypart").val();
                                        var gaugersession = jQuery("#jform_project_gauger").val();
                                        if (elementProject.project_calculator == gaugersession && elementProject.project_calculation_date.substr(11) == timesession) {
                                            TableForSelect += '<tr><td><input type="radio" name="choose_time_gauger" value="'+elementTime+'"></td>';
                                        } else {
                                            TableForSelect += '<tr><td></td>';
                                        }
                                        TableForSelect += '<td>'+elementTime.substr(0, 5)+'-'+t+':00</td>';
                                        TableForSelect += '<td>'+elementProject.project_info+'</td>';
                                        emptytd = 1;
                                    }
                                });
                                if (emptytd == 0) {
                                    TableForSelect += '<tr><td><input type="radio" name="choose_time_gauger" value="'+elementTime+'"></td>';
                                    TableForSelect += '<td>'+elementTime.substr(0, 5)+'-'+t+':00</td>';
                                    TableForSelect += '<td></td>';
                                }
                                TableForSelect += '<td>'+elementGauger.name+'<input type="hidden" name="gauger" value="'+elementGauger.id+'"></td></tr>';
                            });
                        });
                        jQuery("#projects_gaugers").empty();
                        jQuery("#projects_gaugers").append(TableForSelect);
                        jQuery("#date-modal").html("<strong>Выбранный день: "+d+"."+m+"."+idDay.match(reg3)[1]+"</strong>");
                    }
                });
                //если сессия есть, то выдать время, которое записано в сессии
                if (date == datesession_gauger.substr(0, 10)) {
                    var timesession_gauger = jQuery("#jform_project_new_calc_date").val();
                    var gaugersession = jQuery("#jform_project_gauger").val();
                    setTimeout(function() { 
                        var times = jQuery("input[name='choose_time_gauger']");
                        if (timesession_gauger != undefined) {
                            times.each(function(element) {
                                if (timesession_gauger == jQuery(this).val() && gaugersession == jQuery(this).closest('tr').find("input[name='gauger']").val()) {
                                    jQuery(this).prop("checked", true);
                                }
                            });
                        }
                    }, 200);
                } else if (time_gauger != undefined) {
                    setTimeout(function() { 
                        var times = jQuery("input[name='choose_time_gauger']");
                        times.each(function(element) {
                            if (time_gauger == jQuery(this).val() && gauger == jQuery(this).closest('tr').find("input[name='gauger']").val()) {
                                jQuery(this).prop("checked", true);
                            }
                        });
                    }, 200);
                }
            } else {
                jQuery.ajax({
                    type: 'POST',
                    url: "/index.php?option=com_gm_ceiling&task=calculations.GetBusyMounters",
                    data: {
                        date: date,
                        dealer: <?php echo $user->dealer_id; ?>,
                    },
                    success: function(data) {
                        Array.prototype.diff = function(a) {
                            return this.filter(function(i) {return a.indexOf(i) < 0;});
                        };
                        window.DataOfProject = JSON.parse(data);
                        window.AllTime = ["09:00:00", "10:00:00", "11:00:00", "12:00:00", "13:00:00", '14:00:00', "15:00:00", "16:00:00", "17:00:00", "18:00:00", "19:00:00", "20:00:00"];
                        data = JSON.parse(data);
                        jQuery("#date-modal").text("Выбранный день: "+d+"."+m+"."+idDay.match(reg3)[1]);
                        // заполнение бригад в селекте
                        jQuery("#mounters").empty();
                        Allbrigades = <?php echo json_encode($Allbrigades); ?>;
                        select_brigade = "";
                        Array.from(Allbrigades).forEach(function(elem) {
                            select_brigade += '<option value="'+elem.id+'">'+elem.name+'</option>';
                        });
                        jQuery("#mounters").append(select_brigade);
                        // вывод имен монтажников
                        var selectedBrigade = jQuery("#mounters").val();
                        jQuery("#mounters_names").empty();
                        AllMounters = <?php echo json_encode($AllMounters) ?>;
                        AllMounters.forEach(elem => {
                            if (selectedBrigade == elem.id_brigade) {
                                jQuery("#mounters_names").append("<p style=\"margin-top: 0; margin-bottom: 0;\">"+elem.name+"</p>");
                            }
                        });
                        // вывод работ бригады
                        jQuery("#projects_brigade_container").empty();
                        var table_projects = '<p style="margin-top: 1em; margin-bottom: 0;"><strong>Монтажи бригады:</strong></p><table id="projects_brigade">';
                        table_projects += '<tr class="caption"><td>Время</td><td>Адрес</td><td>Периметр</td></tr>';
                        Array.from(data).forEach(function(element) {
                            if (element.project_mounter == selectedBrigade) {
                                if (element.project_mounting_day_off != "") {
                                    table_projects += '<tr><td>'+element.project_mounting_date.substr(11, 5)+' - '+element.project_mounting_day_off.substr(11, 5)+'</td><td colspan="2">'+element.project_info+'</td></tr>';
                                } else {
                                    table_projects += '<tr><td>'+element.project_mounting_date.substr(11, 5)+'</td><td>'+element.project_info+'</td><td>'+element.n5+'</td></tr>';
                                }
                            }
                        });
                        table_projects += "</table>";
                        jQuery("#projects_brigade_container").append(table_projects);
                        // вывод времени бригады
                        var BusyTimes = [];
                        Array.from(data).forEach(function(elem) {
                            if (selectedBrigade == elem.project_mounter && elem.project_mounting_day_off == "" ) {
                                BusyTimes.push(elem.project_mounting_date.substr(11));
                            } else if (selectedBrigade == elem.project_mounter && elem.project_mounting_day_off != "") {
                                AllTime.forEach(element => {
                                    if (element >= elem.project_mounting_date.substr(11) && element <= elem.project_mounting_day_off.substr(11)) {
                                        BusyTimes.push(element);
                                    }
                                }); 
                            }
                        });
                        FreeTimes = AllTime.diff(BusyTimes);
                        var select_hours;
                        FreeTimes.forEach(element => {
                            select_hours += '<option value="'+element+'">'+element.substr(0, 5)+'</option>';
                        });
                        jQuery("#hours").empty();
                        jQuery("#hours").append(select_hours);
                    }
                });
                //если монтаж есть, то выдать время, монтажную бригаду и инфу о ней, которые записаны
                if (date == datesession.substr(0, 10)) {
                    var timesession = jQuery("#jform_project_mounting_date").val().substr(11);
                    var mountersession = jQuery("#jform_project_mounter").val();
                    setTimeout(function() {
                        // время
                        var timeall = document.getElementById('hours').options;
                        for (var i = 0; i < timeall.length; i++) {
                            if (timesession != undefined) {
                                if (timeall[i].value == timesession) {
                                    document.getElementById('hours').disabled = false;
                                    timeall[i].selected = true;
                                }
                            }
                        }
                        // бригада
                        var mounterall = document.getElementById('mounters').options;
                        for (var i = 0; i < mounterall.length; i++) {
                            if (mountersession != undefined) {
                                if (mounterall[i].value == mountersession) {
                                    document.getElementById('mounters').disabled = false;
                                    mounterall[i].selected = true;
                                }
                            }
                        }
                        // инфа о бригаде
                        jQuery("#mounters_names").empty();
                        AllMounters = <?php echo json_encode($AllMounters) ?>;
                        AllMounters.forEach(elem => {
                            if (mountersession == elem.id_brigade) {
                                jQuery("#mounters_names").append("<p style=\"margin-top: 0; margin-bottom: 0;\">"+elem.name+"</p>");
                            }
                        });
                        // монтажи
                        jQuery("#projects_brigade_container").empty();
                        var table_projects3 = '<p style="margin-top: 1em; margin-bottom: 0;"><strong>Монтажи бригады:</strong></p><table id="projects_brigade">';
                        table_projects3 += '<tr class="caption"><td>Время</td><td>Адрес</td><td>Периметр</td></tr>';
                        Array.from(DataOfProject).forEach(function(element) {
                            if (element.project_mounter == mountersession) {
                                table_projects3 += '<tr><td>'+element.project_mounting_date+'</td><td>'+element.project_info+'</td><td>'+element.n5+'</td></tr>';
                            }
                        });
                        table_projects3 += "</table>";
                        jQuery("#projects_brigade_container").append(table_projects3);
                    }, 200);
                }
                // запрет выбора монтажника, если монтаж в статусе недовыполнен
                if (<?php echo $this->item->project_status ?> == 17) {
                    setTimeout(function() {
                        var mounter = document.getElementById('mounters').options;
                        for (var i = 0; i < mounter.length; i++) {
                            document.getElementById('mounters').disabled = true;
                            
                        }
                        console.log(mounter);
                    }, 200);
                }
            }
        });
        //--------------------------------------------

        // заполнение данных о выбранной бригаде при изменении селекта
        jQuery("#mounters").change(function () {
            // имена бригад
            jQuery("#mounters_names").empty();
            var id = jQuery("#mounters").val();
            AllMounters = <?php echo json_encode($AllMounters) ?>;
            AllMounters.forEach(elem => {
                if (id == elem.id_brigade) {
                    jQuery("#mounters_names").append("<p style=\"margin-top: 0; margin-bottom: 0;\">"+elem.name+"</p>");
                }
            });
            // монтажи
            jQuery("#projects_brigade_container").empty();
            var table_projects2 = '<p style="margin-top: 1em; margin-bottom: 0;"><strong>Монтажи бригады:</strong></p><table id="projects_brigade">';
            table_projects2 += '<tr class="caption"><td>Время</td><td>Адрес</td><td>Периметр</td></tr>';
            Array.from(DataOfProject).forEach(function(element) {
                if (element.project_mounter == id) {
                    if (element.project_mounting_day_off != "") {
                        table_projects2 += '<tr><td>'+element.project_mounting_date.substr(11, 5)+' - '+element.project_mounting_day_off.substr(11, 5)+'</td><td colspan="2">'+element.project_info+'</td></tr>';
                    } else {
                        table_projects2 += '<tr><td>'+element.project_mounting_date.substr(11, 5)+'</td><td>'+element.project_info+'</td><td>'+element.n5+'</td></tr>';
                    }
                }
            });
            table_projects2 += "</table>";
            jQuery("#projects_brigade_container").append(table_projects2);
            // времена
            jQuery("#hours").empty();
            var BusyTimes = [];
            Array.from(DataOfProject).forEach(function(elem) {
                if (id == elem.project_mounter && elem.project_mounting_day_off == "" ) {
                    BusyTimes.push(elem.project_mounting_date.substr(11));
                } else if (id == elem.project_mounter && elem.project_mounting_day_off != "") {
                    AllTime.forEach(element => {
                        if (element >= elem.project_mounting_date.substr(11) && element <= elem.project_mounting_day_off.substr(11)) {
                            BusyTimes.push(element);
                        }
                    }); 
                }
            });
            FreeTimes = AllTime.diff(BusyTimes);
            var select_hours2;
            FreeTimes.forEach(element => {
                select_hours2 += '<option value="'+element+'">'+element.substr(0, 5)+'</option>';
            });
            jQuery("#hours").append(select_hours2);
        });
        //-------------------------------------------

        // получение значений из селектов монтажников
        jQuery("#save-choise-tar").click(function() {
            var mounter = jQuery("#mounters").val();
            var time = jQuery("#hours").val();
            var datatime = date+" "+time;
            jQuery("#jform_project_mounter").val(mounter);
            jQuery("#jform_project_mounting_date").val(datatime);
            if (jQuery(".change").length == 0) {
                jQuery("#"+idDay).addClass("change");
            } else {
                jQuery(".change").removeClass("change");
                jQuery("#"+idDay).addClass("change");
            }
            jQuery("#close-tar").hide();
            jQuery("#modal-window-container-tar").hide();
            jQuery("#modal-window-choose-tar").hide();
        });
        //------------------------------------------

        // получение значений из селектов замерщиков
        jQuery("#projects_gaugers").on("change", "input:radio[name='choose_time_gauger']", function() {
            var times = jQuery("input[name='choose_time_gauger']");
            time_gauger = "";
            gauger = "";
            times.each(function(element) {
                if (jQuery(this).prop("checked") == true) {
                    time_gauger = jQuery(this).val();
                    gauger = jQuery(this).closest('tr').find("input[name='gauger']").val();
                }
            });
            jQuery("#jform_new_project_calculation_daypart").val(time_gauger);
            jQuery("#jform_project_new_calc_date").val(date);
            jQuery("#jform_project_gauger").val(gauger);
            if (jQuery(".change").length == 0) {
                jQuery("#"+idDay).addClass("change");
            } else {
                jQuery(".change").removeClass("change");
                jQuery("#"+idDay).addClass("change");
            }
            jQuery("#close-tar").hide();
            jQuery("#modal-window-container-tar").hide();
            jQuery("#modal-window-choose-tar").hide();
        });
        //------------------------------------------

        // подсвет сегоднешней даты
        window.today = new Date();
        window.NowYear = today.getFullYear();
        window.NowMonth = today.getMonth();
        window.day = today.getDate();
        Today(day, NowMonth, NowYear);
        //------------------------------------------

        //если сессия есть, то выдать дату, которая записана в сессии
        var datesession = jQuery("#jform_project_mounting_date").val();
        if (datesession != undefined) {
            if (datesession.substr(8, 1) == "0") {
                daytocalendar = datesession.substr(9, 1);
            } else {
                daytocalendar = datesession.substr(8, 2);
            }
            if (datesession.substr(5, 1) == "0") {
                monthtocalendar = datesession.substr(6, 1);
            } else {
                monthtocalendar = datesession.substr(5, 2);
            }
            jQuery("#current-monthD"+daytocalendar+"DM"+monthtocalendar+"MY"+datesession.substr(0, 4)+"YI"+<?php echo $userId; ?>+"IC1C").addClass("change");
        }
        var datesession_gauger = jQuery("#jform_project_new_calc_date").val();
        if (datesession_gauger != undefined) {
            if (datesession_gauger.substr(8, 1) == "0") {
                daytocalendar_gauger = datesession_gauger.substr(9, 1);
            } else {
                daytocalendar_gauger = datesession_gauger.substr(8, 2);
            }
            if (datesession_gauger.substr(5, 1) == "0") {
                monthtocalendar_gauger = datesession_gauger.substr(6, 1);
            } else {
                monthtocalendar_gauger = datesession_gauger.substr(5, 2);
            }
            jQuery("#current-monthD"+daytocalendar_gauger+"DM"+monthtocalendar_gauger+"MY"+datesession_gauger.substr(0, 4)+"YI"+<?php echo $userId; ?>+"IC0C").addClass("change");
        }
        //-----------------------------------------------------------

        // показать историю
        show_comments();
        //---------------------------------------------------------

        // добавление коммента и обновление истории
        jQuery("#add_comment").click(function () {
            var comment = jQuery("#new_comment").val();
            var reg_comment = /[\\\<\>\/\'\"\#]/;
            var id_client = <?php echo $this->item->id_client;?>;
            if (reg_comment.test(comment) || comment === "") {
                alert('Неверный формат примечания!');
                return;
            }
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=addComment",
                data: {
                    comment: comment,
                    id_client: id_client
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Комментарий добавлен"
                    });
                    jQuery("#comments_id").val(jQuery("#comments_id").val() + data + ";");
                    show_comments();
                    jQuery("#new_comment").val("");
                },
                error: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка отправки"
                    });
                }
            });
        });
        //----------------------------------------------------------------------------------

        // с вкладкой общее связано
            var flag1 = 0;
            jQuery("#sh_estimate").click(function () {
                if (flag1) {
                    jQuery(".section_estimate").hide();
                    flag1 = 0;
                }
                else {
                    jQuery(".section_estimate").show();
                    flag1 = 1;
                }
                jQuery(".section_estimate").each(function () {
                    var el = jQuery(this);
                    if (el.attr("vis") == "hide") el.hide();
                })
            });

            jQuery("#send_all_to_email1").click(function () {
                var email = jQuery("#all-email1").val();
                var client_id = jQuery("#client_id").val();
                var dop_file = jQuery("#dop_file").serialize();
                var testfilename = <?php if (isset($json)) { echo $json;} ?>;
                var filenames = [];
                for (var i = 0; i < testfilename.length; i++) {
                    var id = testfilename[i].id;
                    var el = jQuery("#section_estimate_" + id);
                    if (el.attr("vis") != "hide") filenames.push(testfilename[i]);
                }
                var formData = new FormData();
                jQuery.each(jQuery('#dopfile')[0].files, function (i, file) {
                    formData.append('dopfile', file)
                });
                formData.append('filenames', JSON.stringify(filenames));
                formData.append('email', email);
                formData.append('type', 0);
                formData.append('client_id', client_id);
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=send_estimate",
                    data: formData, /*{
                        filenames: JSON.stringify(filenames),
                        email: email,
                        type: 0,
                        client_id: client_id,
                        dop_file : serialize
                    },*/
                    type: "POST",
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    cache: false,
                    success: function (data) {
                        console.log(data);
                        var n = noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "success",
                            text: "Сметы отправлены!"
                        });

                    },
                    error: function (data) {
                        console.log(data);
                        var n = noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "ошибка отправки"
                        });
                    }
                });
            });
            var flag2 = 0;
            jQuery("#sh_mount").click(function () {
                if (flag2) {
                    jQuery(".section_mount").hide();
                    flag2 = 0;
                }
                else {
                    jQuery(".section_mount").show();
                    flag2 = 1;
                }
                jQuery(".section_mount").each(function () {
                    var el = jQuery(this);
                    if (el.attr("vis") == "hide") el.hide();
                })
            });
            jQuery("#send_all_to_email2").click(function () {
                var email = jQuery("#all-email2").val();
                var testfilename = <?php if (isset($json1)) { echo $json1;} ?>;
                var filenames = [];
                for (var i = 0; i < testfilename.length; i++) {
                    var id = testfilename[i].id;
                    var el = jQuery("#section_mount_" + id);
                    if (el.attr("vis") != "hide") filenames.push(testfilename[i]);
                }
                console.log(filenames);
                var formData = new FormData();
                jQuery.each(jQuery('#dopfile1')[0].files, function (i, file) {
                    formData.append('dopfile1', file)
                });
                formData.append('filenames', JSON.stringify(filenames));
                formData.append('email', email);
                formData.append('type', 1);
                //formData.append('client_id', client_id);
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=send_estimate",
                    data: formData,/* {
                        filenames: JSON.stringify(filenames),
                        email: email,
                        type: 1
                    },*/
                    type: "POST",
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    cache: false,
                    success: function (data) {
                        console.log(data);
                        var n = noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "success",
                            text: "Наряды на монтаж отправлены!"
                        });

                    },
                    error: function (data) {
                        console.log(data);
                        var n = noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "ошибка отправки"
                        });
                    }
                });
            });
        jQuery("input[name='transport']").click(function () {
            var transport = jQuery("input[name='transport']:checked").val();

            if (transport == '2') {
                jQuery("#transport_dist").show();
                jQuery("#transport_dist_col").hide();
                jQuery("#distance").val('');
                jQuery("#distance_col").val('');
            }
            else if(transport == '1') {
                jQuery("#transport_dist").hide();
                jQuery("#transport_dist_col").show();
                jQuery("#distance_col").val('');
                jQuery("#distance").val('');
            }
            else {
                jQuery("#transport_dist").hide();
                jQuery("#transport_dist_col").hide();
                jQuery("#distance").val('');
                jQuery("#distance_col").val('');
            }

            trans();
        });

        jQuery("#click_transport").click(function () {
            trans();
        });
        jQuery("#click_transport_1").click(function () {
            trans();
        });

        if (jQuery("input[name='transport']:checked").val() == '2') {
                jQuery("#transport_dist").show();
        }
        if (jQuery("input[name='transport']:checked").val() == '1') {
                jQuery("#transport_dist_col").show();
        }
        jQuery("input[name^='include_calculation']").click(function () {
            var _this = jQuery(this);
            var id = _this.val();
            var estimate = jQuery("#section_estimate_" + id);
            var mount = jQuery("#section_mount_" + id);
            if (jQuery(this).prop("checked")) {
                jQuery(this).closest("tr").removeClass("not-checked");
                estimate.attr("vis", "");
                if (flag1 == 1) estimate.show();
                mount.attr("vis", "");
                if (flag2 == 1) mount.show();
            } else {
                jQuery(this).closest("tr").addClass("not-checked");
                estimate.attr("vis", "hide").hide();
                mount.attr("vis", "hide").hide();
            }
            square_total();
            calculate_total();
            calculate_total1();
            trans();
        });
        // -------------------------------------------------------------------

        function trans() {
            var id = <?php echo $this->item->id; ?>;
            var calcul = jQuery("input[name='transport']:checked").val();
            var transport = jQuery("input[name='transport']:checked").val();
            var distance = jQuery("#distance").val();
            var distance_col = jQuery("#distance_col").val();
            var distance_col_1 = jQuery("#distance_col_1").val();
            var form = jQuery("#form-client").serialize();
            console.log(distance);
            // alert(distance_col);
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=big_smeta.transport",
                data: form,
                success: function(data){
                    data = JSON.parse(data);
                    var html = "",
                        transport_sum = parseFloat(data);
                    var calc_sum = 0, calc_total = 0;
                    jQuery.each(jQuery("[name='include_calculation[]']:checked"), function (i,e) {
                        calc_sum += parseFloat(jQuery("[name='calculation_total_discount["+jQuery(e).val()+"]']").val());
                        calc_total += parseFloat(jQuery("[name='calculation_total["+jQuery(e).val()+"]']").val());
                    });

                    var sum = Float(calc_sum/*parseFloat(jQuery("#project_sum").val())*/ + transport_sum);
                    var sum_total = Float(calc_total + transport_sum);
                    if(sum < 3500 )sum = 3500;
                    if(sum_total < 3500 )sum_total = 3500;
                    jQuery("#transport_sum").text(transport_sum.toFixed(0) + " руб.");
                    //jQuery("#project_total").text(sum  + " руб.");
                    jQuery("#project_total span.sum").text(sum_total);
                    jQuery("#project_total span.dop").html((sum_total <= 3500)?" * минимальная сумма заказа 3500р.":"");
                    jQuery("#project_total_discount span.sum").text(sum  + " руб.");
                    jQuery("#project_total_discount span.dop").html((sum <= 3500)?" * минимальная сумма заказа 3500р.":"");
                    jQuery("#project_sum_transport").val(sum);
                    jQuery(" #project_sum").val(sum);

                },
                dataType: "json",
                timeout: 10000,
                error: function(data){
                    var n = noty({
                        theme: 'relax',
                        timeout: 2000,
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка при попытке рассчитать транспорт. Сервер не отвечает"
                    });
                }
            });
        }   
        function Float(x, y = 2) {
            return Math.round(parseFloat(""+x) * Math.pow(10,y)) / Math.pow(10,y);
        }
        function calculate_total() {
            var project_total = 0,
                project_total_discount = 0,
                pr_total2 = 0;

            jQuery("input[name^='include_calculation']:checked").each(function () {
                var parent = jQuery(this).closest(".include_calculation"),
                    calculation_total = parent.find("input[name^='calculation_total']").val(),
                    calculation_total_discount = parent.find("input[name^='calculation_total_discount']").val(),
                    project_total2 = parent.find("input[name^='calculation_total2']").val();


                project_total += parseFloat(calculation_total);
                project_total_discount += parseFloat(calculation_total_discount);
                pr_total2 += parseFloat(project_total2);
            });

            if(project_total < 3500 && pr_total2 !== 0)  project_total = 3500;
            if(project_total_discount < 3500 && pr_total2 !== 0)  project_total_discount = 3500;

            jQuery("#project_total span.sum").text(project_total.toFixed(2));
            if (project_total > 3500) { jQuery("#project_total span.dop").html(" "); }
            if (project_total <= 3500 && pr_total2 != 0) { jQuery("#project_total span.dop").html(" * минимальная сумма заказа 3500р."); }
            if (project_total <= 3500 && pr_total2 == 0) { jQuery("#project_total span.dop").html(""); }
            jQuery("#project_total_discount span.sum").text(project_total_discount.toFixed(2));
            if (project_total_discount > 3500) { jQuery("#project_total_discount span.dop").html(" "); }
            if (project_total_discount <= 3500 && pr_total2 != 0) { jQuery("#project_total_discount span.dop").html(" * минимальная сумма заказа 3500р."); }
            if (project_total_discount <= 3500 && pr_total2 == 0) { jQuery("#project_total_discount span.dop").html(""); }
            //jQuery("#project_total_discount").text(project_total_discount.toFixed(2) );
            jQuery("#project_sum").val(project_total_discount);
        }
        function square_total() {
            var square = 0,
                perimeter = 0;
            var total_sq = 0, total_p = 0;
            jQuery("input[name^='include_calculation']:checked").each(function () {
                var parent = jQuery(this).closest(".include_calculation"),
                    square = parent.find("input[name^='total_square']").val(),
                    perimeter = parent.find("input[name^='total_perimeter']").val();
                total_sq += parseFloat(square);
                total_p += parseFloat(perimeter);
            });

            jQuery("#total_square").text(total_sq.toFixed(2));
            jQuery("#total_perimeter").text(total_p.toFixed(2));
        }
        function calculate_total1() {
            var project_total1 = 0,
                project_total2 = 0,
                project_total3 = 0,
                pr_total1 = 0,
                pr_total2 = 0,
                pr_total3 = 0;

            jQuery("input[name^='include_calculation']:checked").each(function () {
                var parent = jQuery(this).closest(".include_calculation"),
                    project_total1 = parent.find("input[name^='calculation_total1']").val(),
                    project_total2 = parent.find("input[name^='calculation_total2']").val(),
                    project_total3 = parent.find("input[name^='calculation_total3']").val();

                pr_total1 += parseFloat(project_total1);
                pr_total2 += parseFloat(project_total2);
                pr_total3 += parseFloat(project_total3);
            });
            jQuery("#calculation_total1").text(pr_total1.toFixed(0));
            jQuery("#calculation_total2").text(pr_total2.toFixed(0));
            jQuery("#calculation_total3").text(pr_total3.toFixed(0));
        }

        jQuery("#spend-form input").on("keyup", function () {
            jQuery('#extra_spend_submit').fadeIn();
        });

        jQuery("#penalty-form input").on("keyup", function () {
            jQuery('#penalty_submit').fadeIn();
        });

        jQuery("#bonus-form input").on("keyup", function () {
            jQuery('#bonus_submit').fadeIn();
        });

        jQuery("#extra_spend_button").click(function () {
            var extra_spend_title_container = jQuery("#extra_spend_title_container"),
                extra_spend_value_container = jQuery("#extra_spend_value_container");
            jQuery("<div class='form-group'><input name='extra_spend_title[]' value='' class='form-control' type='text'></div>").appendTo(extra_spend_title_container);
            jQuery("<div class='form-group'><input name='extra_spend_value[]' value='' class='form-control' type='tel'></div>").appendTo(extra_spend_value_container);
            jQuery('#extra_spend_submit').fadeIn();
            jQuery("#spend-form input").on("keyup", function () {
                jQuery('#extra_spend_submit').fadeIn();
            });
        });

        jQuery("#penalty_button").click(function () {
            var extra_spend_title_container = jQuery("#penalty_title_container"),
                extra_spend_value_container = jQuery("#penalty_value_container");
            jQuery("<div class='form-group'><input name='penalty_title[]' value='' class='form-control' type='text'></div>").appendTo(penalty_title_container);
            jQuery("<div class='form-group'><input name='penalty_value[]' value='' class='form-control' type='tel'></div>").appendTo(penalty_value_container);
            jQuery('#penalty_submit').fadeIn();
            jQuery("#penalty-form input").on("keyup", function () {
                jQuery('#penalty_submit').fadeIn();
            });
        });

        jQuery("#bonus_button").click(function () {
            var extra_spend_title_container = jQuery("#bonus_title_container"),
                extra_spend_value_container = jQuery("#bonus_value_container");
            jQuery("<div class='form-group'><input name='bonus_title[]' value='' class='form-control' type='text'></div>").appendTo(bonus_title_container);
            jQuery("<div class='form-group'><input name='bonus_value[]' value='' class='form-control' type='tel'></div>").appendTo(bonus_value_container);
            jQuery('#bonus_submit').fadeIn();
            jQuery("#bonus-form input").on("keyup", function () {
                jQuery('#bonus_submit').fadeIn();
            });
        });

        jQuery("#spend-form").submit(function (e) {
            e.preventDefault();
            data = jQuery(this).serialize();
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=add_spend&id=<?php echo $this->item->id; ?>",
                data: data,
                success: function (data) {
                    if (data == 1) {
                        jQuery('#extra_spend_submit').fadeOut();
                    } else {
                        var n = noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка при попытке сохранить доп. затраты."
                        });
                    }

                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка при попытке сохранить доп. затраты. Сервер не отвечает"
                    });
                }
            });
        });

        jQuery("#penalty-form").submit(function (e) {
            e.preventDefault();
            data = jQuery(this).serialize();
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=add_penalty&id=<?php echo $this->item->id; ?>",
                data: data,
                success: function (data) {
                    if (data == 1) {
                        jQuery('#penalty_submit').fadeOut();
                    } else {
                        var n = noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка при попытке сохранить штрафы."
                        });
                    }

                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка при попытке сохранить штрафы. Сервер не отвечает"
                    });
                }
            });
        });

        jQuery("#bonus-form").submit(function (e) {
            e.preventDefault();
            data = jQuery(this).serialize();
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=add_bonus&id=<?php echo $this->item->id; ?>",
                data: data,
                success: function (data) {
                    if (data == 1) {
                        jQuery('#bonus_submit').fadeOut();
                    } else {
                        var n = noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка при попытке сохранить штрафы."
                        });
                    }

                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка при попытке сохранить штрафы. Сервер не отвечает"
                    });
                }
            });
        });

    });

</script>
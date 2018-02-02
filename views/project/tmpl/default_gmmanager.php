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

$canEdit = JFactory::getUser()->authorise('core.edit', 'com_gm_ceiling');
if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_gm_ceiling')) {
    $canEdit = JFactory::getUser()->id == $this->item->created_by;
}

$user = JFactory::getUser();
$userId = $user->get('id');

$model = Gm_ceilingHelpersGm_ceiling::getModel('project');


$arr_time = [];
for($i=9;$i<17;$i++){
    for($j=0.0;$j<0.60;$j+=0.05){
        $time = $i+$j;
        $time = str_replace('.',':',$time);
        array_push($arr_time,"<option value = $time >$time</option>");
    }
}
//статус проекта
$status = $model->WhatStatusProject($_GET['id']);

// календарь
if (((int)$status[0]->project_status != 16) && ((int)$status[0]->project_status != 11)) {
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
    $FlagCalendar = [2, $user->dealer_id];
    $calendar1 = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($userId, $month1, $year1, $FlagCalendar);
    $calendar2 = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($userId, $month2, $year2, $FlagCalendar);
} else {

}

//----------------------------------------------------------------------------------

// все бригады
$Allbrigades = $model->FindAllbrigades($user->dealer_id);
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
//$AllMounters = $model->FindAllMounters($where);
//----------------------------------------------------------------------------------

?>

<?= parent::getButtonBack(); ?>

<link rel="stylesheet" href="/components/com_gm_ceiling/views/project/tmpl/css/style.css" type="text/css" />

<h2 class="center">Просмотр проекта</h2>

<?php if ($this->item) : ?>

    <?php
        $model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
        $calculations = $model->getProjectItems($this->item->id);
        $app = JFactory::getApplication();
        $subtype = $app->input->getString('subtype', NULL);
        /*
            if($user->dealer_type!=2)
                $need_mount = 1;
            else $need_mount = 0;
        */
        $del_flag = 0;
        $components_data = array();
        $project_sum = 0;
        $counter = 0;
        foreach ($calculations as $calculation) {
            $counter++;
            $from_db = 1;
            $save = 1;
            $ajax = 0;
            $pdf = 1;
            $print_components = 0;
            if ($calculation->mounting_sum == 0) $need_mount = 0;
            else $need_mount = 1;
            Gm_ceilingHelpersGm_ceiling::calculate($from_db, $calculation->id, $save, $ajax, $pdf, $print_components, $del_flag, $need_mount);
            $from_db = 1;
            $save = 0;
            $ajax = 0;
            $pdf = 0;
            $print_components = 1;
            $components_data[] = Gm_ceilingHelpersGm_ceiling::calculate($from_db, $calculation->id, $save, $ajax, $pdf, $print_components, $del_flag, $need_mount);

            if ($counter == count($calculations)) {
                $flag_last = 1;
                Gm_ceilingHelpersGm_ceiling::calculate($from_db, $calculation->id, $save, $ajax, $pdf, $print_components, $del_flag, $need_mount);
            }
        }
        Gm_ceilingHelpersGm_ceiling::print_components($project_id, $components_data);
    ?>

    <div class="container">
        <div class="row">
            <div class="item_fields">
                <h4>Информация по проекту № <?= $this->item->id; ?></h4>
                <form id="form-client"
                      action="/index.php?option=com_gm_ceiling&task=project.activate&type=gmcalculator&subtype=calendar"
                      method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
                    <table class="table">
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE'); ?></th>
                            <td>
                                <?
                                if ($this->item->project_calculation_date == "0000-00-00 00:00:00") { ?> -
                                <? } else { ?>
                                    <?php $jdate = new JDate($this->item->project_calculation_date); ?>
                                    <?php echo $jdate->format('d.m.Y H:i');
                                } ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php echo "Дата монтажа"; ?></th>
                            <td>
                                <?
                                if ($this->item->project_mounting_date == "0000-00-00 00:00:00") { ?> -
                                <? } else { ?>
                                    <?php $jdate = new JDate($this->item->project_mounting_date); ?>
                                    <?php echo $jdate->format('d.m.Y H:i');
                                } ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?></th>
                            <td><?php echo $this->item->project_info; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
                            <td><?php echo $this->item->client_id; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?></th>
                            <? $contacts = $model->getClientPhones($this->item->id_client); ?>
                            <td><?php foreach ($contacts as $phone) {
                                    echo $phone->client_contacts;
                                    echo "<br>";
                                } ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_GM_CHIEF_NOTE'); ?></th>
                            <td><?php echo $this->item->gm_chief_note; ?></td>
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_GM_CALCULATOR_NOTE'); ?></th>
                            <td><?php echo $this->item->gm_calculator_note; ?></td>
                        </tr>
                    </table>
                </form>
            </div>
            <input name="project_id" value="<?php echo $this->item->id; ?>" type="hidden">
            <input name="client" id="client_id" value="<?php echo $this->item->client_id; ?>" type="hidden">
            <button class = "btn btn-primary" id = "create_pdfs">Сгенерировать сметы</button>
            <div class="">
                <h4>Информация для менеджера</h4>
                <table class="table">
                    <?php
                    $mount_model = Gm_ceilingHelpersGm_ceiling::getModel('mount');
                    $mount = $mount_model->getDataAll();
                    ?>
                    <?php foreach ($calculations as $calculation) { ?>
                        <tr>
                            <th><?php echo $calculation->calculation_title; ?></th>
                            <td>
                                <?php echo $calculation->canvases_sum; ?> руб.
                            </td>
                            <td>
                                <?php $path = "/costsheets/" . md5($calculation->id . "-4") . ".pdf"; ?>
                                <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                    <a href="<?php echo $path; ?>" class="btn btn-secondary"
                                       target="_blank">Посмотреть</a>
                                <?php } else { ?>
                                    -
                                <?php } ?>
                            </td>
                            </td>
                            <td>
                                <?php $path = "/costsheets/" . md5($calculation->id."cutpdf".-2) . ".pdf"; ?>
                                <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                    <a href="<?php echo $path; ?>" class="btn btn-secondary"
                                       target="_blank">Посмотреть раскрой</a>
                                <?php } else { ?>
                                    -
                                <?php } ?>
                            </td>
                            <td>
                                <? if ($this->item->project_status != 12) { ?>
                                    <button onclick='send_ajax(<?php echo $calculation->id; ?>)'
                                            class="btn btn-primary">Изменить раскрой
                                    </button>
                                <? } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
                <h4>Расходные материалы</h4>
                <table class="table">
                    <?php $total_components_sum = 0;
                    $sum = 0;
                    $total_perimeter = 0;
                    //получаем прайс комплектующих
                    $components_model = Gm_ceilingHelpersGm_ceiling::getModel('components');
                    $components_list = $components_model->getFilteredItems();
                    foreach ($components_list as $i => $component) {
                        $components[$component->id] = $component;
                    }
                    foreach ($calculations as $calculation) {
                        $total_perimeter += $calculation->n5;
                    }

                    $sum = 0;
                    $baget = 0;
                    $provod_count = 1;
                    $provod_count1 = 0;
                    $brus = 0;
                    $calcform_model = Gm_ceilingHelpersGm_ceiling::getModel('calculationform');

                    foreach ($calculations as $calculation) {
                        $total_components_sum += $calculation->components_sum;

                        $baget = $calculation->n5 + $calculation->dop_krepezh / 2.0;
                        $baget_count = intval($baget / 2.5);
                        if (floatval($baget / 2.5) > $baget_count) {
                            $baget_count++;
                        }
                        $baget = $baget_count * 2.5;
                        $baget2 += $components[11]->price * $baget;

                        $brus = $calculation->n11 + $calculation->n27 + $calculation->n17 + $calculation->n18 + $calculation->n20;
                        $brus_count = intval($brus / 0.5);
                        if (floatval($brus / 0.5) > $brus_count) {
                            $brus_count++;
                        }
                        $brus = $brus_count * 0.5;
                        $brus2 += $components[1]->price * $brus;

                        $fix_components = $calcform_model->n13($calculation->id);
                        $hoods_components = $calcform_model->n22($calculation->id);
                        foreach ($fix_components as $comp) $fix_provod += $comp->n13_count;
                        foreach ($hoods_components as $comp) $hoods_provod += $comp->n22_count;
                        $provod_count = ceil(($calculation->n12 + $fix_provod + $hoods_provod) * 0.5 + $calculation->n19);

                        $price_provod += $components[4]->price * $provod_count;
                        //$provod_count = 0;
                    }

                    foreach ($calculations as $calculation) {
                        $new_baget += $calculation->n5 + $calculation->dop_krepezh / 2.0;
                        $new_brus += $calculation->n11 + $calculation->n27 + $calculation->n17 + $calculation->n18 + $calculation->n20;

                        $fix_components = $calcform_model->n13($calculation->id);
                        $hoods_components = $calcform_model->n22($calculation->id);
                        foreach ($fix_components as $comp) $fix_provod1 += $comp->n13_count;
                        foreach ($hoods_components as $comp) $hoods_provod1 += $comp->n22_count;
                        $provod_count1 += (($calculation->n12 + $fix_provod1 + $hoods_provod1) * 0.5 + $calculation->n19);
                    }

                    $baget_count = intval($new_baget / 2.5);
                    if (floatval($new_baget / 2.5) > $baget_count) {
                        $baget_count++;
                    }
                    $new_baget = $baget_count * 2.5;
                    $itog = $components[11]->price * $new_baget;

                    $brus_count = intval($new_brus / 0.5);
                    if (floatval($new_brus / 0.5) > $brus_count) {
                        $brus_count++;
                    }
                    $new_brus = $brus_count * 0.5;
                    $itog2 += $components[1]->price * $new_brus;

                    $price_provod1 = $components[4]->price * ceil($provod_count1);


                    ?>
                    <tr>
                        <th>Общая себестоимость расходников</th>
                        <td>
                            <?php $path = "/costsheets/" . md5($this->item->id . "-8") . ".pdf"; ?>
                            <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                            <?php echo $total_components_sum - $baget2 + $itog - $brus2 + $itog2 - $price_provod + $price_provod1; ?>
                            руб.
                            <?php } else { ?>
                                0
                            <?php } ?>
                        </td>
                        <td>
                            <?php $path = "/costsheets/" . md5($this->item->id . "-8") . ".pdf"; ?>
                            <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>
                    </tr>

                </table>
                <h4>Наряды на монтаж</h4>
                <table class="table">
                    <?php foreach ($calculations as $calculation) { ?>
                        <tr>
                            <th><?php echo $calculation->calculation_title; ?></th>
                            <td>

                                <?php echo $calculation->mounting_sum; ?> руб.
                            </td>
                            <td>
                                <?php $path = "/costsheets/" . md5($calculation->id . "-2") . ".pdf"; ?>
                                <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                    <a href="<?php echo $path; ?>" class="btn btn-secondary"
                                       target="_blank">Посмотреть</a>
                                <?php } else { ?>
                                    -
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
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
                </table>
                <h4>Изменить время, дату и монтажную бригаду</h4>
                <div style="border-top: 1px solid #eceeef;">
                <table>
                    <tr>
                        <th style="padding: 12px">Текущая дата монтажа</th>
                        <td style="padding: 12px" id="nowDateMounting">
                            <?php
                                $date_mounting = substr($this->item->project_mounting_date, 8, 2).".".substr($this->item->project_mounting_date, 5, 2).".".substr($this->item->project_mounting_date, 0, 4)." ".substr($this->item->project_mounting_date, 11, 5);
                                echo $date_mounting; 
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th style="padding: 12px">Текущая монтажная бригада</th>
                        <td style="padding: 12px" id="nowMounter">
                            <?php 
                                foreach ($Allbrigades as $value) {
                                    if ($this->item->project_mounter == $value->id) {
                                        echo $value->name;
                                    }
                                } 
                            ?>
                        </td>
                    </tr>
                </table>
                </div>
                <div id="table-container">
                    <?php if (((int)$status[0]->project_status != 16) && ((int)$status[0]->project_status != 11) && ((int)$status[0]->project_status != 22)) { ?>
                        <table>
                            <tr>
                                <td>
                                    <button id="button-prev" type="button" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i></button>
                                </td>
                                <td style="width: 100%">
                                    <div id="calendar1" style="padding: 1em; width: 49%; display: inline-block">
                                        <?php echo $calendar1; ?>
                                    </div>
                                    <div id="calendar2" style="padding: 1em; width: 49%; display: inline-block">
                                        <?php echo $calendar2; ?>
                                    </div>
                                </td>
                                <td>
                                    <button id="button-next" type="button" class="btn btn-primary"><i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                                </td>
                            </tr>
                        </table>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <? if ((int)$status[0]->project_status == 22) { ?>
        <form id="form-project"
              action="/index.php?option=com_gm_ceiling&task=project.return&id=<?= $this->item->id ?>"
              method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
        <button type="submit" class="btn btn-primary">Вернуть на стадию замера</button>
        </form>
    <? } elseif ($subtype != "run") { ?>
        <form id="form-project"
              action="/index.php?option=com_gm_ceiling&task=project.approvemanager&id=<?= $this->item->id ?>"
              method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
            <button type="button" id = "run" class="btn btn-primary">
                Запустить
            </button>
            <div id="modal_window_container" class = "modal_window_container">
                <button type="button" id="close" class = "close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
                <div id="modal_window_date" class = "modal_window">
                    <h6 style = "margin-top:10px">Введите к скольки должен быть готов</h6>
                    <p><input type="date"  name = "ready_date" id="ready_date" value = <?php echo date('Y-m-d'); ?>> <input name ="time" type ="time" id = "time" required ></p>
                    <p ><input type= "checkbox" name = 'quick' id = 'quick' value = 0>Срочный</p>
                    <p><button type="submit" id="save" class="btn btn-primary">Сохранить</button>  <button type="button" id="cancel" class="btn btn-primary">Отмена</button></p>
                </div>
            </div>
        </form>
    <? } else { ?>
        <form id="form-project"
              action="/index.php?option=com_gm_ceiling&task=project.refusing&id=<?= $this->item->id ?>"
              method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
            <table class="table ">
                <tr>
                    <?php if ((int)$status[0]->project_status != 11 && (int)$status[0]->project_status != 16 && (int)$status[0]->project_status != 17) { ?>
                        <td style=" padding-left:0;"><a class="btn btn-primary" id="refuse">Отказ от производства</a></td>
                    <?php } ?>
                </tr>
                <tbody class="refuse" style="display: none">
                <tr>
                    <td>
                        <label>Данные для перезвона:<span class="star">&nbsp;</span></label>

                        <p><input type="date" id="date" name="date" placeholder="Дата замера" class="inputactive"
                                  style="width:70%;" required></p>
                        <select id="time" name="time" class="inputactive" style="width:70%;">
                            <option value="9:00">9:00-10:00</option>
                            <option value="10:00">10:00-11:00</option>
                            <option value="11:00">11:00-12:00</option>
                            <option value="12:00">12:00-13:00</option>
                            <option value="13:00">13:00-14:00</option>
                            <option value="14:00">14:00-15:00</option>
                            <option value="15:00">15:00-16:00</option>
                            <option value="16:00">16:00-17:00</option>
                            <option value="17:00">17:00-18:00</option>
                            <option value="18:00">18:00-19:00</option>
                            <option value="19:00">19:00-20:00</option>
                        </select>

                    </td>
                    <td>
                        <p><label>Добавить примечание:<span class="star">&nbsp;</span></label></p>
                        <p><textarea class="inputactive" id="comment" name="comment"
                                     placeholder="Примечание"></textarea></p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <button id="refus_project" class="btn btn btn-danger" type="submit">
                            Отправить в отказы
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
    <? } ?>
    <form action="/sketch/cut_redactor/index.php" id="data_form" method="POST" style="display : none;">
        <input type="hidden" name="walls" id="input_walls">
        <input type="hidden" name="calc_id" id="calc_id">
        <input type="hidden" name="proj_id" id="proj_id">
    </form>
    <div id="modal-window-container-tar">
        <button id="close-tar" type="button"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="modal-window-choose-tar">
            <p id="date-modal"></p>
            <p><strong>Выберите монтажника:</strong></p>
            <p>
                <select name="mounters" id="mounters"></select>
            </p>
            <p style="margin-bottom: 0;"><strong>Монтажники:</strong></p>
            <div id="mounters_names"></div>
            <div id="projects_brigade_container"></div>
            <p style="margin-top: 1em;"><strong>Выберите время начала монтажа:</strong></p>
            <p>
                <select name="hours" id='hours'></select>
            </p>
            <p><button type="button" id="save-choise-tar" class="btn btn-primary">Ок</button></p>
        </div>
    </div>

    <script>
        jQuery(document).mouseup(function (e){ // событие клика по веб-документу
            var div = jQuery("#modal_window_date"); // тут указываем ID элемента
            if (!div.is(e.target) // если клик был не по нашему блоку
                && div.has(e.target).length === 0) { // и не по его дочерним элементам
                jQuery("#close").hide();
                jQuery("#modal_window_container").hide();
                jQuery("#modal_window_date").hide();
            }
        });
        jQuery(document).ready(function () {
            jQuery('#create_pdfs').click(function(){
                jQuery.ajax({
                    type: 'POST',
                    url: "/index.php?option=com_gm_ceiling&task=createPdfs",
                    data: {
                        id:<?php echo $this->item->id;?>
                    },
                    success: function(data) {
                        window.location = window.location;
                    },
                    error: function(data) {
                        console.log(data);
                    }
                });
            });
            jQuery("#cancel").click(function(){
                jQuery("#close").hide();
                jQuery("#modal_window_container").hide();
                jQuery("#modal_window_date").hide();
            });
            jQuery("#quick").change(function(){
                this.value = this.checked ? 1 : 0;  
                if(this.value == 1){
                    jQuery("#time").required = false;
                    var date = new Date();
                    var h = date.getHours();
                    var m = date.getMinutes();
                    if(m.length == 1)
                    {
                        m = "0"+m;
                    }
                    var time = h+":"+m;
                    jQuery("#time").val(time);
                }
            })
            // открытие модального окна с календаря и получение даты и вывода свободных монтажников
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
                jQuery.ajax({
                    type: 'POST',
                    url: "/index.php?option=com_gm_ceiling&task=calculations.GetBusyMounters",
                    data: {
                        date: date,
                        dealer: <?php echo $user->dealer_id; ?>,
                    },
                    success: function(data) {
                        window.DataOfProject = JSON.parse(data);
                        data = JSON.parse(data);
                        window.AllTime = ["09:00:00", "10:00:00", "11:00:00", "12:00:00", "13:00:00", '14:00:00', "15:00:00", "16:00:00", "17:00:00", "18:00:00", "19:00:00", "20:00:00"];
                        Array.prototype.diff = function(a) {
                            return this.filter(function(i) {return a.indexOf(i) < 0;});
                        };
                        jQuery("#date-modal").text("Выбранный день: "+d+"."+m+"."+idDay.match(reg3)[1]);
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
                        console.log(data);
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
                //если замер есть, то выдать время, монтажную бригаду и инфу о ней, которые записаны
                if (date == "<?php echo substr($this->item->project_mounting_date, 0, 10); ?>") {
                    var timesession = "<?php echo substr($this->item->project_mounting_date, 11); ?>";
                    var mountersession = "<?php echo $this->item->project_mounter; ?>";
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
                    }, 200);
                }
                // запрет выбора монтажника, если монтаж в статусе недовыполнен
                if (<?php echo $status[0]->project_status ?> == 17) {
                    setTimeout(function() {
                        var mounter = document.getElementById('mounters').options;
                        for (var i = 0; i < mounter.length; i++) {
                            document.getElementById('mounters').disabled = true;
                        }
                    }, 200);
                }
            });

            jQuery("#run").click(function(){
                jQuery("#modal_window_container").show();
                jQuery("#modal_window_date").show("slow");
                jQuery("#close").show();
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

            // получение значений из селектов
            jQuery("#save-choise-tar").click(function() {
                var id_project = <?php echo $this->item->id; ?>;
                var mounter = jQuery("#mounters").val();
                var time = jQuery("#hours").val();
                var datetime = date+" "+time;
                jQuery.ajax({
                    type: 'POST',
                    url: "/index.php?option=com_gm_ceiling&task=project.UpdateDateMountBrigade",
                    data: {
                        id_project: id_project,
                        date: datetime,
                        mounter: mounter,
                        oldmounter: "<?php echo $this->item->project_mounter; ?>",
                        olddatetime: "<?php echo $this->item->project_mounting_date ?>",
                        id_client: "<?php echo $this->item->id_client ?>",
                    },
                    success: function(data) {
                        if (data != undefined) {
                            data = JSON.parse(data);
                            var DateNew = data[0].project_mounting_date.substr(8, 2)+"."+data[0].project_mounting_date.substr(5, 2)+"."+data[0].project_mounting_date.substr(0, 4)+" "+data[0].project_mounting_date.substr(11, 5);
                            jQuery("#nowDateMounting").text(DateNew);
                            Allbrigades = <?php echo json_encode($Allbrigades); ?>;
                            Array.from(Allbrigades).forEach(function(elem) {
                                if (data[0].project_mounter == elem.id) {
                                    jQuery("#nowMounter").text(elem.name);
                                }
                            });
                            var n = noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "success",
                            text: "Данные изменены"
                            });
                            var month1 = datetime.substr(5, 2);
                            if (datetime.substr(5, 1) == "0") {
                                month1 = datetime.substr(6, 1);
                            }
                            update_calendar(month1, datetime.substr(0, 4));
                            if (datetime.substr(5, 1) == "0") {
                                if (datetime.substr(6, 1) == "9") {
                                    var month2 = 10;
                                } else {
                                    month2 = datetime.substr(6, 1);
                                    month2++;
                                }
                            } else {
                                month2 = datetime.substr(5, 2);
                                    month2++;
                            }
                            if (datetime.substr(5, 2) == 12) {
                                var year = datetime.substr(0, 4);
                                year++;
                            } else {
                                year = datetime.substr(0, 4);
                            }
                            update_calendar2(month2, year);
                        }
                    },
                    timeout: 10000,
                    error: function () {
                        var n = noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка при попытке изменить данные. Сервер не отвечает"
                        });
                    }
                });
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
            if ("<?php echo substr($this->item->project_mounting_date, 0, 10); ?>" != undefined) {
                jQuery("#current-monthD"+<?php echo substr($this->item->project_mounting_date, 8, 2); ?>+"DM"+<?php echo substr($this->item->project_mounting_date, 5, 2); ?>+"MY"+<?php echo substr($this->item->project_mounting_date, 0, 4); ?>+"YI"+<?php echo $userId; ?>+"IC1C").addClass("change");
            }
            //-----------------------------------------------------------

            jQuery("input[name^='include_calculation']").click(function () {
                if (jQuery(this).prop("checked")) {
                    jQuery(this).closest("tr").removeClass("not-checked");
                } else {
                    jQuery(this).closest("tr").addClass("not-checked");
                }
                calculate_total();
            });

            jQuery("#accept_project").click(function () {
                jQuery("input[name='project_verdict']").val(1);
                jQuery(".project_activation").show();
                jQuery("#mounting_date_control").show();
            });

            jQuery("#refuse_project").click(function () {
                jQuery("input[name='project_verdict']").val(0);
                jQuery(".project_activation").show();
                jQuery("#mounting_date_control").hide();
            });

        });

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
                    flag: 2,
                    month: month,
                    year: year,
                },
                success: function (msg) {
                    jQuery("#calendar1").empty();
                    jQuery("#calendar1").append(msg);
                    Today(day, NowMonth, NowYear);
                    var datesession = "<?php echo $this->item->project_mounting_date; ?>"; 
                    if (datesession != undefined) {
                        jQuery("#current-monthD"+datesession.substr(8, 2)+"DM"+datesession.substr(5, 2)+"MY"+datesession.substr(0, 4)+"YI"+<?php echo $userId; ?>+"IC1C").addClass("change");
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
                    flag: 2,
                },
                success: function (msg) {
                    jQuery("#calendar2").empty();
                    jQuery("#calendar2").append(msg);
                    Today(day, NowMonth, NowYear);
                    var datesession = "<?php echo $this->item->project_mounting_date; ?>";  
                    if (datesession != undefined) {
                        jQuery("#current-monthD"+datesession.substr(8, 2)+"DM"+datesession.substr(5, 2)+"MY"+datesession.substr(0, 4)+"YI"+<?php echo $userId; ?>+"IC1C").addClass("change");
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
            jQuery("#current-monthD"+day+"DM"+month+"MY"+year+"YI"+<?php echo $userId; ?>+"IC1C").addClass("today");
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

        var temp = 0;
        jQuery("#refuse").click(function () {
            if (!temp) {
                jQuery(".refuse").show();
                temp = 1;
            }
            else {
                jQuery(".refuse").hide();
                temp = 0;
            }
        });

        function send_ajax(id) {
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=send_sketch",
                data: {
                    id: id,
                    from_db:1  
                },
                success: function (data) {
                    jQuery("#input_walls").val(data);
                    jQuery("#calc_id").val(id);
                    jQuery("#proj_id").val(<?php echo $this->item->id; ?>);
                    jQuery("#data_form").submit();

                },
                error: function (data) {
                    console.log(data);
                }

            });
        }

        function calculate_total() {
            var components_total = 0;
            gm_total = 0;
            dealer_total = 0;
            jQuery("input[name^='include_calculation']:checked").each(function () {
                var parent = jQuery(this).closest(".include_calculation"),
                    components_sum = parent.find("input[name^='components_sum']").val(),
                    gm_mounting_sum = parent.find("input[name^='gm_mounting_sum']").val(),
                    dealer_mounting_sum = parent.find("input[name^='dealer_mounting_sum']").val();
                components_total += parseFloat(components_sum);
                gm_total += parseFloat(gm_mounting_sum);
                dealer_total += parseFloat(dealer_mounting_sum);
            });
            jQuery("#components_total").text(components_total.toFixed(2));
            jQuery("#gm_total").text(gm_total.toFixed(2));
            jQuery("#dealer_total").text(dealer_total.toFixed(2));
        }

    </script>

<?php
else:
    echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
endif;
?>
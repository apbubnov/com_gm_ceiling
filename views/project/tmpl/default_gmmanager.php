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
$jinput = JFactory::getApplication()->input;
$user = JFactory::getUser();
$userId = $user->get('id');

/*MODELS BLOCK*/
$model_calculations = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
$model = Gm_ceilingHelpersGm_ceiling::getModel('project');
$canvases_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
$client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
$mount_model = Gm_ceilingHelpersGm_ceiling::getModel('mount');
$projects_mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');
/*______________*/

$project_id = $this->item->id;
$calculations = $model_calculations->new_getProjectItems($this->item->id);
$type = $jinput->get('type', '', 'STRING');
$subtype = $jinput->get('subtype', '', 'STRING');
$client = $client_model->getClientById($this->item->id_client);
$dealer = JFactory::getUser($client->dealer_id);
$dealer_cl = $client_model->getClientById($dealer->associated_client);
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
$service_mount = get_object_vars(json_decode($this->item->calcs_mounting_sum));
$need_service =(!empty($service_mount)) ? true : false;


$json_mount  = $this->item->mount_data;
if(!empty($this->item->mount_data)){
    $mount_types = $projects_mounts_model->get_mount_types(); 
    $this->item->mount_data = json_decode(htmlspecialchars_decode($this->item->mount_data));
    foreach ($this->item->mount_data as $value) {
        $value->stage_name = $mount_types[$value->stage];
    }
    
}
$transport_service = Gm_ceilingHelpersGm_ceiling::calculate_transport($this->item->id,"service")['mounter_sum'];
$transport = Gm_ceilingHelpersGm_ceiling::calculate_transport($this->item->id,"mount")['mounter_sum'];
$mounter_approve = true;

/*ГЕНЕРАЦИЯ ПДФ*/
/*foreach($calculations as $calc){
    if(!empty($calc->n3)){
        Gm_ceilingHelpersGm_ceiling::create_cut_pdf($calc->id);
        Gm_ceilingHelpersGm_ceiling::create_manager_estimate(1,$calc->id);
    }
    $mount = Gm_ceilingHelpersGm_ceiling::calculate_mount(0,$calc->id);
    $calc->total_gm_mount_sum = $mount['total_gm_mounting'];
    $calc->total_dealer_mount_sum = $mount['total_dealer_mounting'];
}*/
Gm_ceilingHelpersGm_ceiling::create_estimate_of_consumables($this->item->id);
Gm_ceilingHelpersGm_ceiling::create_common_manager_estimate($this->item->id);
Gm_ceilingHelpersGm_ceiling::create_common_cut_pdf($this->item->id);
Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($this->item->id);


//статус проекта
$status = $model->WhatStatusProject($_GET['id']);
if (((int)$status[0]->project_status == 16) || ((int)$status[0]->project_status == 11) || ((int)$status[0]->project_status == 22) || $this->item->dealer_id != $user->dealer_id){
    $display = 'style="display:none;"';
}

?>

<button class="btn btn-primary" id="btn_back"><i class="fa fa-arrow-left" aria-hidden="true"></i>Назад</button>

<link rel="stylesheet" href="/components/com_gm_ceiling/views/project/css/style.css" type="text/css" />

<h2 class="center">Просмотр проекта</h2>

<?php if ($this->item) : ?>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="item_fields">
                    <h4>Информация по проекту № <?= $this->item->id; ?></h4>
                    <form id="form-client"
                          action="/index.php?option=com_gm_ceiling&task=project.activate&type=gmcalculator&subtype=calendar"
                          method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
                        <table class="table">
                            <tr>
                                <th>Дилер</th>
                                <td><?php echo $dealer_cl->client_name; ?></td>
                            </tr>
                            <tr>
                                <th><?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE'); ?></th>
                                <td>
                                    <?php
                                    if ($this->item->project_calculation_date == "0000-00-00 00:00:00") { ?> -
                                    <?php } else { ?>
                                        <?php $jdate = new JDate($this->item->project_calculation_date); ?>
                                        <?php echo $jdate->format('d.m.Y H:i');
                                    } ?>
                                </td>
                            </tr>
                            <?php if(!empty($this->item->mount_data)):?>
                                <tr>
                                    <th colspan="3" style="text-align: center;">Монтаж</th>
                                </tr>
                                <?php foreach ($this->item->mount_data as $value) { ?>
                                    <tr>
                                        <th><?php echo $value->time;?></th>
                                        <td><?php echo $value->stage_name;?></td>
                                        <td><?php echo JFactory::getUser($value->mounter)->name;?></td>
                                    </tr>
                                <?php }?>
                            <?php endif;?>
                            <tr >
                                <th  style="text-align: center;"colspan = 3><?php echo "Дата готовности полотен"; ?></th>
                            </tr>
                            <?php foreach ($calculations as $calculation){?>
                                <tr >
                                    <td><?php echo $calculation->calculation_title;?></td>
                                    <td><?php echo (!empty($calculation->run_date) || !empty($calculation->run_by_call)) ? ($calculation->run_by_call) ? "По звонку" : $calculation->run_date : "Отсутствует"?></td>
                                </tr>
                            <?php }?>
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
                                <?php $contacts = $model->getClientPhones($this->item->id_client); ?>
                                <td><?php foreach ($contacts as $phone) {
                                        echo $phone->client_contacts;
                                        echo "<br>";
                                    } ?></td>
                            </tr>
                        </table>
                    </form>
                </div>
                <input name="project_id" value="<?php echo $this->item->id; ?>" type="hidden">
                <input name="client" id="client_id" value="<?php echo $this->item->client_id; ?>" type="hidden">
                <button class = "btn btn-primary" id = "create_pdfs">Сгенерировать сметы</button>
            </div>
            <div class="col-md-6">
                <h4 class="center">Примечания</h4>
                <?php include_once('components/com_gm_ceiling/views/project/project_notes.php'); ?>

            </div>
        </div>
            <div class="row">
                <h4>Информация для менеджера</h4>
                <table class="table table_cashbox">
                    <?php
                    $mount = $mount_model->getDataAll();
                    $common_canvases_sum = 0;
                    $total_components_sum = 0;
                    ?>
                    <thead>
                        <tr>
                            <th>
                                Название
                            </th>
                            <th>
                                Стоимость
                            </th>
                            <th>
                                Смета
                            </th>
                            <th>
                                Раскрой
                            </th>
                            <th>
                                Изменить раскрой
                            </th>
                            <th>
                                Изменить расчет
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        foreach ($calculations as $calculation) {
                            $common_canvases_sum += $calculation->canvases_sum;
                            $total_components_sum += $calculation->components_sum;
                            $calc_data = [];

                            $canvas = array_filter(
                                $calculation->goods,
                                function ($e) {
                                    return $e->category_id == 1;
                                }
                            );
                            if (!empty($canvas)) {
                                $filter = "id = ".$canvas[0]->id;
                                $detailed_canvas = $canvases_model->getFilteredItemsCanvas($filter);
                                $filter = "texture_id = ".$detailed_canvas[0]->texture_id." and manufacturer_id = ".$detailed_canvas[0]->manufacturer_id." and color = ".$detailed_canvas[0]->color."  and visibility = 1";
                                $selected_canvases = $canvases_model->getFilteredItemsCanvas($filter);
                                $arr_widths = [];
                                $widths = [];
                                foreach ($selected_canvases as $value) {
                                    if (!in_array($value->width, $arr_widths)) {
                                        array_push($arr_widths, $value->width);
                                        array_push($widths, (object)array("id"=>$value->id,"width" => $value->width, "price" => $value->price));
                                    }
                                }
                                usort($widths, function ($a, $b) {
                                    if ($a->width < $b->width) {
                                        return 1;
                                    }
                                    if ($a->width > $b->width) {
                                        return -1;
                                    }
                                    return 0;
                                });
                                $calc_data[$calculation->id] = array(
                                    "n4" => $calculation->n4,
                                    "n5" => $calculation->n5,
                                    "n9" => $calculation->n9,
                                    "widths" => $widths,
                                    "texture" => $canvas->texture_id,
                                    "manufacturer" => $canvas->manufacturer_id,
                                    "color" => $canvas->color_id,
                                    "walls" => $calculation->original_sketch
                                );
                                $widths = json_encode($widths);
                                $color = $detailed_canvas[0]->color;
                                $hex = $detailed_canvas[0]->hex;
                            }
                    ?>
                        <tr>
                            <td><?php echo $calculation->calculation_title; ?></td>
                            <td>
                                <?php echo $calculation->canvases_sum;?> руб.
                            </td>
                            <td>
                                <?php $path = "/costsheets/" . md5($calculation->id . "manager") . ".pdf"; ?>
                                <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                    <a href="<?php echo $path; ?>" class="btn btn-secondary"
                                       target="_blank">Посмотреть</a>
                                <?php } else { ?>
                                    -
                                <?php } ?>
                            </td>
                            <td>
                                <?php $path = "/costsheets/".md5($calculation->id."cutpdf").".pdf"; ?>
                                <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                    <a href="<?php echo $path; ?>" class="btn btn-secondary"
                                       target="_blank">Посмотреть</a>
                                <?php } else { ?>
                                    -
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($this->item->project_status != 12) { ?>
                                    <button  data-calc_id = "<?php echo $calculation->id; ?>" name = "change_cut"
                                            class="btn btn-primary">Изменить
                                    </button>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($this->item->project_status != 12) { ?>
                                    <button  data-calc_id = "<?php echo $calculation->id; ?>" name = "change_calc"
                                             class="btn btn-primary">Изменить
                                    </button>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td><b>Общая информация</b></td>
                        <td><?=$common_canvases_sum;?> руб.</td>
                        <td>
                            <?php $path = "/costsheets/".md5($this->item->id."common_manager").".pdf"; ?>
                            <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                <a href="<?php echo $path; ?>" class="btn btn-secondary"
                                   target="_blank">Посмотреть</a>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>
                        <td>
                            <?php $path = "/costsheets/".md5($this->item->id."common_cutpdf").".pdf"; ?>
                            <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                <a href="<?php echo $path; ?>" class="btn btn-secondary"
                                   target="_blank">Посмотреть</a>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
                </table>
                <h4>Расходные материалы</h4>
                <table class="table">
                    <tr>
                        <th>Общая себестоимость расходников</th>
                        <td>
                            <?php $path = "/costsheets/" . md5($this->item->id . "consumables") . ".pdf"; ?>
                            <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                            <?php echo $total_components_sum;?>
                            руб.
                            <?php } else { ?>
                                0
                            <?php } ?>
                        </td>
                        <td>
                            <?php $path = "/costsheets/" . md5($this->item->id . "consumables") . ".pdf"; ?>
                            <?php if ($total_components_sum > 0 && file_exists($_SERVER['DOCUMENT_ROOT'] . $path)) { ?>
                                <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Посмотреть</a>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>
                    </tr>

                </table>
                <?php 
                    if (is_array($this->item->mount_data)) {
                        $mount_data = $this->item->mount_data;
                    }
                    else {
                        $mount_data = json_decode(htmlspecialchars_decode($this->item->mount_data));
                    }
                ?>
                <h4>Наряды на монтаж</h4>
                <table class="table table_cashbox ">
                    <thead>
                        <tr>
                            <th>
                                Название
                            </th>
                                <th>Сумма</th>
                            <th>
                                Наряды
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($calculations as $calculation) {?>
                        <tr>
                            <td><?php echo $calculation->calculation_title; ?></td>
                                <td>
                                    <?php
                                        $total_mount += $calculation->mounting_sum;
                                        echo $calculation->mounting_sum;
                                    ?> руб.
                                </td>
                            <td>
                                <?php
                                if (count($mount_data) === 0 || (count($mount_data) === 1 && $mount_data[0]->stage == 1)) {
                                    $path = "/costsheets/" . md5($calculation->id.'mount_single_dealer').'.pdf';
                                    $path_gm = "/costsheets/" . md5($calculation->id.'mount_single_gm').'.pdf';
                                    echo '<a href="'.$path.'" class="btn btn-secondary" target="_blank">Наряд МС</a>';
                                    if (file_exists($_SERVER['DOCUMENT_ROOT'].$path_gm)) {
                                        echo '<a href="' . $path_gm . '" class="btn btn-secondary" target="_blank">Наряд бригаде</a>';
                                    }
                                }
                                else {
                                    foreach ($mount_data as $value) {
                                        $path_gm = "/costsheets/" . md5($calculation->id.'mount_stage_gm'.$value->stage).'.pdf';
                                        $path = "/costsheets/" . md5($calculation->id.'mount_stage_dealer'.$value->stage).'.pdf';
                                        if (file_exists($_SERVER['DOCUMENT_ROOT'].$path)) {
                                            switch ($value->stage) {
                                                case 2:
                                                    echo '<a href="' . $path . '" class="btn btn-secondary" target="_blank">Обагечивание МС</a>';
                                                    echo '<a href="' . $path_gm . '" class="btn btn-secondary" target="_blank">Обагечивание ГМ</a>';
                                                    break;
                                                case 3:
                                                    echo '<a href="' . $path . '" class="btn btn-secondary" target="_blank">Натяжка МС</a>';
                                                    echo '<a href="' . $path_gm . '" class="btn btn-secondary" target="_blank">Натяжка ГМ</a>';
                                                    break;
                                                case 4:
                                                        echo '<a href="' . $path . '" class="btn btn-secondary" target="_blank">Вставка МС</a>';
                                                        echo '<a href="' . $path_gm . '" class="btn btn-secondary" target="_blank">Вставка ГМ</a>';
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
                    <?php } ?>
                    <tr>
                        <td>
                            <b>Итого</b>
                        </td>
                        <td>
                            <?php echo $total_mount + $transport;?> руб.
                        </td>
                        <td>
                            <?php
                                $path = "/costsheets/" . md5($this->item->id . "mount_common_gm") . ".pdf";
                                $path_gm = "/costsheets/" . md5($this->item->id . "mount_common_dealer") . ".pdf";
                            ?>
                            <a href="<?php echo $path; ?>" class="btn btn-secondary" target="_blank">Наряд МС</a>
                            <a href="<?php echo $path_gm; ?>" class="btn btn-secondary" target="_blank">Наряд ГМ</a>
                        </td>
        </tbody>
    </table>
                <h4>Изменить время, дату и монтажную бригаду</h4>
                <div style="border-top: 1px solid #eceeef;">
                    <label><strong>Текущие данные</strong></label>
                <table class="table">
                    <tr>
                        <th>
                            Дата этапа
                        </th>
                        <th>
                            Название
                        </th>
                        <th>
                            Бригада
                        </th>
                    </tr>
                    <?php foreach($this->item->mount_data as $value){?>
                        <tr>
                            <td>
                                <?php echo $value->time;?>
                            </td>
                            <td>
                                <?php echo $value->stage_name;?>
                            </td>
                            <td>
                                <?php echo JFactory::getUser($value->mounter)->name;?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
                </div>
                <div id="table-container">
                    <div id = "calendar_mount" align="center" <?php echo $display;?>></div>
                </div>
            </div>
        </div>
    </div>
        <form id="form-project1"
              action="/index.php?option=com_gm_ceiling&task=project.return&id=<?= $this->item->id ?>"
              method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
            <button type="submit" class="btn btn-primary">Вернуть на стадию замера</button>
        </form><br>
    <?php if ($subtype != "run") { ?>
        <form id="form-project"
              action="/index.php?option=com_gm_ceiling&task=project.approvemanager&id=<?= $this->item->id ?>"
              method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
            <input type="hidden" name="mount" id = mount value= "<?php echo $json_mount;?>">
            <input type="hidden" name="ready_dates" id = "ready_dates" value="">
            <button type="button" id = "run" class="btn btn-primary">
                Запустить
            </button>
            <div id="mw_container" class = "modal_window_container">
                <button type="button" id="close_mw" class = "close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
                <div id="mw_date" class = "modal_window">
                    <h6 style = "margin-top:10px">Подтверждение/измение даты готовности полотен</h6>
                    <?php if(count($calculations)>1){ ?>
                    <div class="row center"  style="padding-bottom: 5px;margin-left: 15%;margin-right: 15%;">
                        <div class="col-md-4 ">
                            На все полотна
                        </div>
                        <div class="col-md-4">
                            <input type="checkbox"  id="all_by_call"  class="inp-cbx" style="display: none">
                            <label for="all_by_call" class="cbx">
                                        <span>
                                            <svg width="12px" height="10px" viewBox="0 0 12 10">
                                                <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                            </svg>
                                        </span>
                                <span>По звонку</span>
                            </label>
                        </div>
                        <div class="col-md-4 left">
                            <input type="datetime-local" value="<?php echo str_replace(' ','T',date("Y-m-d H:i"));?>" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}"  name="all_canvas_ready" class="input-gm">
                        </div>
                    </div>
                    <?php }?>
                    <?php foreach($calculations as $calculation){?>
                        <div class="row center"  style="padding-bottom: 5px;margin-left: 15%;margin-right: 15%;">
                            <div class="col-md-4 ">
                                <?php echo $calculation->calculation_title; ?>
                            </div>
                            <div class="col-md-4">
                                <input type="checkbox" <?php echo ($calculation->run_by_call)?"checked":""?> data-calc_id = "<?php echo $calculation->id?>" id="<?php echo $calculation->id?>" name = "runByCall" class="inp-cbx" style="display: none">
                                <label for="<?php echo $calculation->id?>" class="cbx">
                                        <span>
                                            <svg width="12px" height="10px" viewBox="0 0 12 10">
                                                <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                            </svg>
                                        </span>
                                    <span>По звонку</span>
                                </label>
                            </div>
                            <div class="col-md-4 left">
                                <input type="datetime-local" value="<?php echo str_replace(' ','T',$calculation->run_date);?>" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}" required data-calc_id = "<?php echo $calculation->id?>" name="date_canvas_ready" class="input-gm">
                            </div>
                        </div>
                    <?php }?>
                    <div class="row">
                        <button type="button" id="save" class="btn btn-primary">Сохранить</button>
                    </div>
                </div>
                <div id = "mw_mounts_calendar" class = "modal_window"></div>
            </div>
        </form>
    <?php } else { ?>
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
    <?php } ?>
    <form method="POST" action="/sketch/cut_redactor_2/index.php" style="display: none" id="form_url">
        <input name="user_id" id="user_id" value="<?php echo $user->id ;?>" type="hidden">
        <input name = "width" id = "width" value = "" type = "hidden">
        <input name = "texture" id = "texture" value = "" type = "hidden">
        <input name = "color" id = "color" value = "" type = "hidden">
        <input name = "manufacturer" id = "manufacturer" value = "" type = "hidden">
        <input name = "auto" id = "auto" value="" type = "hidden">
        <input name = "walls" id = "walls" value="" type= "hidden">
        <input name = "calc_id" id = "calc_id" value="<?php echo $calculation_id;?>" type = "hidden">
        <input name = "n4" id="n4" value="" type ="hidden">
        <input name = "n5" id="n5" value="" type ="hidden">
        <input name = "n9" id="n9" value="" type ="hidden">
        <input name = "proj_id" id="proj_id" value="<?php echo $project_id; ?>" type="hidden">
        <input name = "type_url" id="type_url" value="<?php echo $type_url; ?>" type="hidden">
        <input name = "subtype_url" id="subtype_url" value="<?php echo $subtype_url; ?>" type="hidden">
        <input name = "page" id="page" value="gmmanager" type="hidden">
    </form>

    <script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
    <script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>
    <script type="text/javascript" src="/components/com_gm_ceiling/date_picker/mounts_calendar.js"></script>
    <script type="text/javascript">

    init_mount_calendar('calendar_mount','mount','mw_mounts_calendar',['close_mw','mw_container']);
        var project_id = "<?php echo $this->item->id; ?>";
        jQuery(document).mouseup(function (e){ // событие клика по веб-документу
            var div1 = jQuery("#mw_date"); // тут указываем ID элемента
            var div2 = jQuery("#mw_mounts_calendar");
            if (!div1.is(e.target) // если клик был не по нашему блоку
                && div1.has(e.target).length === 0
                &&!div2.is(e.target) // если клик был не по нашему блоку
                && div2.has(e.target).length === 0) { // и не по его дочерним элементам
                jQuery("#close_mw").hide();
                jQuery("#mw_container").hide();
                div1.hide();
                div2.hide();
            }
        });
        jQuery(document).ready(function () {

            let calc_data = JSON.parse('<?php echo json_encode($calc_data);?>');
            jQuery("[name = 'change_cut']").click(function(){
                let id = jQuery(this).data('calc_id');
                let data = calc_data[id];
                jQuery("#calc_id").val(id);
                jQuery('#texture').val(data.texture);
                jQuery("#color").val(data.color);
                jQuery("#manufacturer").val(data.manufacturer);
                jQuery("#n4").val(data.n4);
                jQuery("#n5").val(data.n5);
                jQuery("#n9").val(data.n9);
                jQuery("#walls").val(data.walls);
                jQuery("#width").val(JSON.stringify(data.widths));
                jQuery("#form_url").submit();
            });
            jQuery("[name = 'change_calc']").click(function(){
                let id = jQuery(this).data('calc_id');
                location.href = '/index.php?option=com_gm_ceiling&view=calculationform&type=gmmanager&calc_id='+id;
            });

            jQuery('#btn_back').click(function(){
                var l = location.href.replace('project','projects');
                l = l.replace('run','runprojects');
                l = l.replace(/&id=\d+/,'');
                location.href = l;
            });
            jQuery('#create_pdfs').click(function(){
                jQuery.ajax({
                    type: 'POST',
                    url: "/index.php?option=com_gm_ceiling&task=createPdfs",
                    data: {
                        id:<?php echo $this->item->id;?>
                    },
                    success: function(data) {
                        location.reload();
                    },
                    error: function(data) {
                        console.log(data);
                    }
                });
            });

            jQuery("#run").click(function(){
                jQuery("#mw_container").show();
                jQuery("#mw_date").show("slow");
                jQuery("#close_mw").show();
            });
            jQuery('[name = "runByCall"]').change(function () {
                var checkBox = this;
                if(checkBox.checked){
                    jQuery('[name = "date_canvas_ready"]').filter(function () {
                        if(jQuery(this).data("calc_id") == jQuery(checkBox).data("calc_id")){
                            this.value =  "";
                        };
                    });
                }
            });

            jQuery('[name = "date_canvas_ready"]').focus(function () {
                var date = new Date,
                    month  = (date.getMonth()<10) ?"0"+(date.getMonth()+1) : (date.getMonth()+1),
                    day = (date.getDate()<10) ?"0"+date.getDate() : date.getDate();
                this.value = date.getFullYear()+"-"+month+"-"+day+"T09:00";
            });
            jQuery('[name = "date_canvas_ready"]').change(function () {
                var date_time = this;
                jQuery("#all_by_call").attr("checked",false);
                jQuery('[name = "runByCall"]').filter(function () {
                    if(jQuery(this).data("calc_id") == jQuery(date_time).data("calc_id")){
                        jQuery(this).attr("checked",false);
                    };
                });
            });
            jQuery("#save").click(function(){
                var readyDates = jQuery('[name = "date_canvas_ready"]').filter(function () {
                        if(this.value){
                            return this;
                        };
                    }),
                    byCall = jQuery('[name = "runByCall"]:checked'),
                    result = [];
                jQuery.each(readyDates,function (index,elem) {
                    result.push({calc_id:jQuery(elem).data("calc_id"),ready_time:jQuery(elem).val()});
                });
                jQuery.each(byCall,function (index,elem) {
                    result.push({calc_id:jQuery(elem).data("calc_id"),ready_time:"by_call"});
                });
                jQuery("#ready_dates").val(JSON.stringify(result));
                jQuery("#form-project").submit();
            });
            //готовность на все потолки
            jQuery("#all_by_call").change(function () {
                var checkBox = this,attr;
                if(checkBox.checked){
                    jQuery('[name = "all_canvas_ready"]')[0].value = '';
                    jQuery('[name = "date_canvas_ready"]').each(function(index,elem){
                        elem.value = '';
                    });
                    attr = true;
                }
                else{
                    attr = false;
                }
                jQuery('[name = "runByCall"]').each(function (index,elem) {
                    jQuery(elem).attr("checked",attr);
                });
            });
            jQuery('[name = "all_canvas_ready"]').focus(function () {
                var date = new Date,
                    month  = (date.getMonth()<10) ?"0"+(date.getMonth()+1) : (date.getMonth()+1),
                    day = (date.getDate()<10) ?"0"+date.getDate() : date.getDate();
                this.value = date.getFullYear()+"-"+month+"-"+day+"T09:00";
                jQuery('[name = "runByCall"]').each(function (index,elem) {
                    jQuery(elem).attr("checked",false);
                });
                jQuery("#all_by_call").attr("checked",false);
            });
            jQuery('[name = "all_canvas_ready"]').change(function () {
                var date_time = this;
                jQuery('[name = "date_canvas_ready"]').each(function(index,elem){
                    elem.value = date_time.value;
                });
            });
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

        <?php if (($dealer->dealer_type == 0 || $dealer->dealer_type == 1) && $user->dealer_id != $dealer->dealer_id)
            { ?>
            jQuery("#refuse").click(function () {
                jQuery.ajax({
                    type: 'POST',
                    url: "index.php?option=com_gm_ceiling&task=project.updateProjectStatus",
                    data: {
                        project_id: <?php echo $this->item->id; ?>,
                        status: 4
                    },
                    success: function (data) {
                        console.log(data);
                        location.href = location.href.replace('&subtype=run', '');
                    },
                    dataType: "text",
                    timeout: 10000,
                    error: function () {
                        console.log(data);
                        var n = noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка сервера"
                        });
                    }
                });
            });
        <?php } else {?>
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
        <?php } ?>

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
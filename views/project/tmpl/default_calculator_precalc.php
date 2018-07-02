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
$user = JFactory::getUser();
$user_group = $user->groups;
$userId = $user->get('id');
$userName = $user->get('username');
$canEdit = JFactory::getUser()->authorise('core.edit', 'com_gm_ceiling');
if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_gm_ceiling')) {
    $canEdit = JFactory::getUser()->id == $this->item->created_by;
}

Gm_ceilingHelpersGm_ceiling::create_client_common_estimate($this->item->id);
Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($this->item->id);
Gm_ceilingHelpersGm_ceiling::create_estimate_of_consumables($this->item->id);

/*_____________блок для всех моделей/models block________________*/ 
$calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
$mountModel = Gm_ceilingHelpersGm_ceiling::getModel('mount');
$calculationform_model = Gm_ceilingHelpersGm_ceiling::getModel('calculationform');
$reserve_model = Gm_ceilingHelpersGm_ceiling::getModel('reservecalculation');
$client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
$clients_dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
$components_model = Gm_ceilingHelpersGm_ceiling::getModel('components');
$canvas_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');
$model_api_phones = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
$repeat_model = Gm_ceilingHelpersGm_ceiling::getModel('repeatrequest');

/*________________________________________________________________*/
$transport = Gm_ceilingHelpersGm_ceiling::calculate_transport($this->item->id);
$client_sum_transport = $transport['client_sum'];
$self_sum_transport = $transport['mounter_sum'];//идет в монтаж
$self_calc_data = [];
$self_canvases_sum = 0;
$self_components_sum = 0;
$self_mounting_sum = 0;
$project_self_total = 0;
$project_total = 0;
$project_total_discount = 0;
$total_square = 0;
$total_perimeter = 0;
$calculation_total_discount = 0;
$calculations = $calculationsModel->new_getProjectItems($this->item->id);
foreach ($calculations as $calculation) {
    $calculation->dealer_canvases_sum = double_margin($calculation->canvases_sum, 0/*$this->item->gm_canvases_margin*/, $this->item->dealer_canvases_margin);
    $calculation->dealer_components_sum = double_margin($calculation->components_sum, 0 /*$this->item->gm_components_margin*/, $this->item->dealer_components_margin);
    $calculation->dealer_gm_mounting_sum = double_margin($calculation->mounting_sum, 0 /*$this->item->gm_mounting_margin*/, $this->item->dealer_mounting_margin);
    $calculation->dealer_self_canvases_sum = margin($calculation->canvases_sum, 0/*$this->item->gm_canvases_margin*/);
    $self_canvases_sum +=$calculation->dealer_self_canvases_sum;
    $calculation->dealer_self_components_sum = margin($calculation->components_sum, 0/* $this->item->gm_components_margin*/);
    $self_components_sum += $calculation->dealer_self_components_sum;
    $calculation->dealer_self_gm_mounting_sum = margin($calculation->mounting_sum, 0/* $this->item->gm_mounting_margin*/);
    $self_mounting_sum += $calculation->dealer_self_gm_mounting_sum;
    $calculation->calculation_total = $calculation->dealer_canvases_sum + $calculation->dealer_components_sum + $calculation->dealer_gm_mounting_sum;
    $calculation->calculation_total_discount = $calculation->calculation_total * ((100 - $calculation->discount) / 100);
    $calculation->n13 = $calculationform_model->n13_load($calculation->id);
    $calculation->n14 = $calculationform_model->n14_load($calculation->id);
    $calculation->n15 = $calculationform_model->n15_load($calculation->id);
    $calculation->n22 = $calculationform_model->n22_load($calculation->id);
    $calculation->n23 = $calculationform_model->n23_load($calculation->id);
    $calculation->n26 = $calculationform_model->n26_load($calculation->id);
    $calculation->n29 = $calculationform_model->n29_load($calculation->id);
    $total_square +=  $calculation->n4;
    $total_perimeter += $calculation->n5;
    $project_total += $calculation->calculation_total;
    $project_total_discount += $calculation->calculation_total_discount;
    $self_calc_data[$calculation->id] = array(
        "canv_data" => $calculation->dealer_self_canvases_sum,
        "comp_data" => $calculation->dealer_self_components_sum,
        "mount_data" => $calculation->dealer_self_gm_mounting_sum,
        "square" => $calculation->n4,
        "perimeter" => $calculation->n5,
        "sum" => $calculation->calculation_total,
        "sum_discount" => $calculation->calculation_total_discount
    );
    $calculation_total = $calculation->calculation_total;
    $calculation_total_discount =  $calculation->calculation_total_discount;
}
$self_calc_data = json_encode($self_calc_data);//массив с себестоимотью по каждой калькуляции
$project_self_total = $self_sum_transport + $self_components_sum + $self_canvases_sum + $self_mounting_sum; //общая себестоимость проекта

$mount_transport = $mountModel->getDataAll($this->item->dealer_id);
$min_project_sum = (empty($mount_transport->min_sum)) ? 0 : $mount_transport->min_sum;
$min_components_sum = (empty($mount_transport->min_components_sum)) ? 0 : $mount_transport->min_components_sum;

$project_total_discount_transport = $project_total_discount + $client_sum_transportt;

$del_flag = 0;
$project_total = $project_total + $client_sum_transport;
$project_total_discount = $project_total_discount  + $client_sum_transport;

//address
$street = preg_split("/,.дом([\S\s]*)/", $this->item->project_info)[0];
preg_match("/,.дом:.([\d\w\/\s]{1,4})/", $this->item->project_info,$house);
$house = $house[1];
preg_match("/.корпус:.([\d\W\s]{1,4}),|.корпус:.([\d\W\s]{1,4}),{0}/", $this->item->project_info,$bdq);
$bdq = $bdq[1];
preg_match("/,.квартира:.([\d\s]{1,4}),/", $this->item->project_info,$apartment);
$apartment = $apartment[1];
preg_match("/,.подъезд:.([\d\s]{1,4}),/", $this->item->project_info,$porch);
$porch = $porch[1];
preg_match("/,.этаж:.([\d\s]{1,4})/", $this->item->project_info,$floor);
$floor = $floor[1];
preg_match("/,.код:.([\d\S\s]{1,10})/", $this->item->project_info,$code);
$code = $code[1];

$status = $this->item->project_status;
$status_attr = "data-status = \"$status\"";
?>

 <form id="form-client" action="/index.php?option=com_gm_ceiling&task=project.activate&type=calculator&subtype=calendar" method="post" enctype="multipart/form-data">

       <?= parent::getButtonBack();?>
        <h4 class="center" style="margin-top: 15px; margin-bottom: 15px;">Проект № <?php echo $this->item->id ?></h4>

        <br>
        <div class="center">
            <div style="display: inline-block;">
                <h4>
                    <?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?>
                </h4>
            </div>
            <div style="display: inline-block;">
                <a href="/index.php?option=com_gm_ceiling&view=clientcard&id=<?=$this->item->id_client;?>">
                    <?php echo $this->item->client_id; ?>
                </a>
            </div>
            <div style="display: inline-block;">
                <button class="btn-sm btn-primary" type = "button" id="change_data"><i class="fa fa-pencil" aria-hidden="true"></i></button>
            </div>
        </div>
        <hr>
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-md-6 no_padding">
                    <div class="project_activation" style="display: none;">
                        <input name="project_id" id="project_id" value="<?php echo $this->item->id; ?>" type="hidden">
                        <input name="client_id" id="client_id" value="<?php echo $this->item->id_client; ?>" type="hidden">
                        <input name="type" value="calculator" type="hidden">
                        <input name="subtype" value="calendar" type="hidden">
                        <input id="project_verdict" name="project_verdict" value="0" type="hidden">
                        <input id="project_status" name="project_status" value="0" type="hidden">
                        <input name="data_change" value="0" type="hidden">
                        <input name="data_delete" value="0" type="hidden">
                        <input id="mounting_date" name="mounting_date" type='hidden'>
                        <input id="jform_project_mounting_date" name="jform_project_mounting_date" value="<?php echo $this->item->project_mounting_date; ?>" type='hidden'>
                        <input id="project_mounter" name="project_mounter" value="<?php echo $this->item->project_mounter; ?>" type='hidden'>
                        <input id="project_sum" name="project_sum" value="<?php echo $project_total_discount; ?>" type="hidden">
                        <input id="project_sum_transport" name="project_sum_transport" value="<?php echo $project_total_discount_transport; ?>" type="hidden">
                        <input name="comments_id" id="comments_id" value="<?php if (isset($_SESSION['comments'])) echo $_SESSION['comments']; ?>" type="hidden">
                        <input name = "project_new_calc_date" id = "project_new_calc_date" type = "hidden">
                        <input name = "new_project_calculation_daypart" id = "new_project_calculation_daypart" type = "hidden">
                        <input name = "project_gauger" id = "project_gauger" type = "hidden">
                        <input name = "activate_by_email" id = "activate_by_email" type = "hidden" value = 0>
                        <!-- <input name = "self_transport" id = "self_transport" type = "hidden" value = "<?php //echo $self_sum_transport;?>">
                        <input name = "self_components" id = "self_components" type = "hidden" value = "<?php //echo $self_components_sum;?>"> -->
                    </div>
                    <div>
                        <table class="table_info">
                            <tr>
                                <th>
                                    <?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?>
                                </th>
                                <?php
                                    if ($this->item->id_client!=1) { 
                                        $phone = $calculationsModel->getClientPhones($this->item->id_client);
                                    } else  {
                                        $phone = [];
                                    }
                                ?>
                                <td colspan="2">
                                    <?php
                                        foreach ($phone AS $contact) {
                                            echo "<a href='tel:+$contact->client_contacts'>$contact->client_contacts</a>";
                                            echo "<br>";
                                        } 
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Почта</th>
                                <td colspan="2">
                                    <?php
                                        
                                        $contact_email = $clients_dop_contacts_model->getContact($this->item->id_client);
                                        foreach ($contact_email AS $contact) {
                                            echo "<a href='mailto:$contact->contact'>$contact->contact</a>";
                                            echo "<br>";
                                        }
                                    ?>
                                </td>
                            </tr>
                        </table>
                            <div class="col-xs-12 col-md-6 no_padding center ">
                                <button class="btn btn-primary">Назначить звонок</button>
                            </div>
                        <br>
                        <table class="table_info">
                            <tr>
                                <th>
                                    <?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?>
                                </th>
                                <td colspan="2">
                                    <a target="_blank" href="https://yandex.ru/maps/?mode=search&text=<?=$this->item->project_info;?>">
                                        <?=$this->item->project_info;?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th><?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE'); ?></th>
                                <td colspan="2">
                                    <?php if ($this->item->project_calculation_date == "0000-00-00 00:00:00") { ?>
                                        -
                                    <?php } else { ?>
                                        <?php $jdate = new JDate(JFactory::getDate($this->item->project_calculation_date)); ?>
                                        <?php echo $jdate->format('d.m.Y H:i'); ?>
                                    <?php } ?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Скидка
                                </th>
                                <td>
                                    <?php echo (!empty($this->item->project_discount))?  $this->item->project_discount : " - ";?>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    Реклама
                                </th>
                                <td>
                                    <?php echo (!empty($this->item->api_phone_id))?  $this->item->api_phone_id : " - ";?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <?php if ($this->item->project_verdict == 0 && $user->dealer_type != 2) { ?>
                        <div class="col-12 col-md-6">
                                <button type="button" class="btn btn-primary" id="change_discount">Изменить величину скидки</button>
                        </div>
                    <?php } ?>
                    <table class="calculation_sum">
                        <?php 
                            if (!empty($calculation_total)) {
                                $skidka = ($calculation_total - $project_total_1) / $calculation_total * 100;
                            } else {
                                $skidka = 0;
                            }
                        ?>
                        <tbody class="new_discount" style="display: none">
                            <tr>
                                <td>
                                    <label id="jform_discoint-lbl" for="jform_new_discount">Новый процент скидки:</label>
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>
                                    <input name="new_discount" id="jform_new_discount" placeholder="%" min="0" max='<?= round($skidka, 0); ?>' type="number" style="width: 100%;">
                                </td>
                                <td>
                                    <button type="button" id="update_discount" class="btn btn-primary">Ок</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col-sm-12 col-md-6 comment">
                    <label> История клиента: </label>
                    <textarea id="comments" class="input-comment" rows=11 readonly> </textarea>
                    <table>
                        <tr>
                            <td><label> Добавить комментарий: </label></td>
                        </tr>
                        <tr>
                            <td width = 100%><textarea  class = "inputactive" id="new_comment" placeholder="Введите новое примечание"></textarea></td>
                            <td><button class="btn btn-primary" type="button" id="add_comment"><i class="fa fa-paper-plane" aria-hidden="true"></i>
                            </button></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <!-- расчеты для проекта -->
        <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>
        <hr>
            <h4>Добавить звонок</h4>
            <link rel="stylesheet" href="/components/com_gm_ceiling/date_picker/nice-date-picker.css">
            <script src="/components/com_gm_ceiling/date_picker/nice-date-picker.js"></script>
            <label><b>Дата: </b></label><br>
            <div id="calendar-wrapper"></div>
            <script>
                new niceDatePicker({
                    dom:document.getElementById('calendar-wrapper'),
                    mode:'en',
                    onClickDate:function(date){
                        document.getElementById('call_date').value = date;
                    }
                });
            </script>
            <p><label><b>Время: </b></label><br><input type="time" id="call_time"></p>
            <input name="call_date" id="call_date" type="hidden">
            <input name="call_comment" id="call_comment" placeholder="Введите примечание">
            <button class="btn btn-primary" id="add_call" type="button"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
        <hr>
        <label>Реклама: </label>
        <?php
            if (empty($this->item->api_phone_id)) {
                $all_advt = $model_api_phones->getAdvt();
        ?>
            <select id="advt_choose">
                <option value="0">Выберите рекламу</option>
                <?php if (!empty($all_advt)) foreach ($all_advt as $item) { ?>
                    <option value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
                <?php } ?>
            </select>
            <button class="btn btn-primary" id="save_advt" type="button">Ок</button>
            <hr>
            <div id="new_advt_div">
                <label>Добавить новую рекламу</label><br>
                <input id="new_advt_name" placeholder="Название рекламы">
                <button type="button" class="btn btn-primary" id="add_new_advt">Добавить</button>
            </div>
        <?php
            } else {
                if ($this->item->api_phone_id == 10) {
                    $repeat_advt = $repeat_model->getDataByProjectId($this->item->id);
                    if (!empty($repeat_advt->advt_id)) {
                        $reklama = $model_api_phones->getDataById($repeat_advt->advt_id);
                    }
                    else {
                        $reklama = $model_api_phones->getDataById(10);
                    }
                } else {
                    $reklama = $model_api_phones->getDataById($this->item->api_phone_id);
                }
        ?>
            <label><?php echo $reklama->number.' '.$reklama->name.' '.$reklama->description; ?></label>
        <?php } ?>
        <hr>
        <!-- активация проекта (назначение на монтаж, заключение договора) -->
        <?php if($user->dealer_type == 1 && count($calculations) <= 0) { } else {?>
            <?php if ($this->item->project_verdict == 0) { ?>
                <div class="container" <?php if (!empty($_GET['precalculation'])) {echo "style='display:none'";} ?> >
                    <div class="row center">
                        <div class="col-lg-3">
                            <a class="btn btn-success act_btn" <?php echo $status_attr;?> id="accept_project">
                                <?php if($this->item->project_status == 0){
                                    echo "Записать на замер";
                                    }
                                else { echo "Договор"; } ?>
                            </a>
                        </div>
                        <div class="col-lg-3">
                            <button id="refuse" class="btn btn-primary act_btn"  <?php echo $status_attr;?> type="submit">Сохранить</button>
                        </div>
                        <div class="col-lg-3">
                             <button id="refuse_cooperate" class="btn btn-danger act_btn" type="button">Отказ от сотрудничества</button>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="project_activation" <?php if($user->dealer_type == 1 && $this->item->project_status == 4) echo "" /* "style=\"display: block;\"" */; else echo "style=\"display: none;\""?> id="project_activation">
                <?php if ($user->dealer_type != 2) { ?>
                    <div id="mounter_wraper" <?php if($user->dealer_type == 1 && $this->item->project_status == 4) echo "style=\"display: block; margin-top: 25px;\""; else echo "style=\"display: none;\""?>>
                    </div>
                    <hr>
                    <div class="row center" id = "ready_wrapper">
                        <h4>Назначить дату готовности полотен</h4>
                            <input type="datetime-local" id="date_canvas_ready">
                            <button class="btn btn-primary" id="btn_ready_date" type="button">ок</button>
                    </div>
                    <hr>
                    <div class = "container" style="padding-left: 0px;">
                        <div class="row">
                            <div class="col-md-1">
                                <button class="btn btn-primary" id = "show_comments_btn" type = "button">Ввести примечания</button>
                            </div>
                        </div>
                        <div id ="comments_divs" style="display:none;">
                            <div class="row">
                                <div class="col-md-2">
                                    <label for = "jform_gm_calculator_note">Примечание к договору</label>
                                 </div>
                                <div class="col-md-2">
                                    <textarea name="gm_calculator_note" id="jform_gm_calculator_note" placeholder="Примечание к договору" aria-invalid="false"></textarea>
                                </div>
                                
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <label for = "jform_chief_note">Примечание к монтажу</label>
                                </div>
                                <div class="col-md-2">
                                    <textarea name="chief_note" id="jform_chief_note" placeholder="Примечание к монтажу" aria-invalid="false"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="contract" style="margin-top: 25px; margin-bottom: 0;">
                        <input name='smeta' value='0' type='checkbox'> Отменить смету по расходным материалам
                    </p>
                    <div class="row center">
                        <div class="col-xl-3" style="padding-top: 25px;">
                            <button class="validate btn btn-primary save_bnt" id="save" type="button" from="form-client">Сохранить и запустить <br> в производство ГМ</button>
                        </div>
                        <div class="col-xl-3" style="padding-top: 25px;">
                            <button class="validate btn btn-primary save_bnt" id="save_email" type="button" from="form-client">Сохранить и запустить <br> в производство по email</button>
                        </div>
                        <div class="col-xl-3" style="padding-top: 25px;">
                            <button class="validate btn btn-primary save_bnt" id="save_exit" type="submit" from="form-client">Сохранить и выйти</button>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <input name="idCalcDelete" id="idCalcDelete" value="<?=$calculation->id;?>" type="hidden">
    </form>
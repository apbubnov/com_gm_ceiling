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
$projects_mounts_model = Gm_ceilingHelpersGm_ceiling::getModel('projects_mounts');
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
$json_mount = $this->item->mount_data;
$stages = [];
if(!empty($this->item->mount_data)){

    $mount_types = $projects_mounts_model->get_mount_types(); 
    $this->item->mount_data = json_decode(htmlspecialchars_decode($this->item->mount_data));
    foreach ($this->item->mount_data as $value) {
        $value->stage_name = $mount_types[$value->stage];
        if(!array_key_exists($value->mounter,$stages)){
            $stages[$value->mounter] = array((object)array("stage"=>$value->stage,"time"=>$value->time));
        }
        else{
            array_push($stages[$value->mounter],(object)array("stage"=>$value->stage,"time"=>$value->time));
        }
    }
    foreach ($calculations as $calc) {
        foreach ($stages as $key => $value) {
           foreach ($value as $val) {
              Gm_ceilingHelpersGm_ceiling::create_mount_estimate_by_stage($calc->id,$key,$val->stage,$val->time);
           }
            
        }
    }
   
}

$all_advt = $model_api_phones->getAdvt();
if ($this->item->api_phone_id == 10) {
    $repeat_advt = $repeat_model->getDataByProjectId($this->item->id);
    if (!empty($repeat_advt->advt_id)) {
        $reklama = $model_api_phones->getDataById($repeat_advt->advt_id);
    }
    else {
        $reklama = $model_api_phones->getDataById(10);
    }
} else {
    if(!empty($this->item->api_phone_id)){
         $reklama = $model_api_phones->getDataById($this->item->api_phone_id);
    }
   
}

$advt_str = $reklama->number.' '.$reklama->name.' '.$reklama->description; 

 if (!empty($calculation_total)) {
    $skidka = ($calculation_total - $project_total_1) / $calculation_total * 100;
} else {
    $skidka = 0;
}
 $contact_email = $clients_dop_contacts_model->getContact($this->item->id_client);

?>
<style>
.act_btn{
    width:210px;
    margin-bottom: 10px;
}
.save_bnt{
    width:250px;
}
.btn_edit{
    position: absolute;
    right:0;
}

</style>
<link rel="stylesheet" href="/components/com_gm_ceiling/views/project/css/style.css" type="text/css" />
<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>

<?= parent::getButtonBack(); ?>
<form id="form-client" action="/index.php?option=com_gm_ceiling&task=project.activate&type=calculator&subtype=calendar" method="post" enctype="multipart/form-data">
<div class="project_activation" style="display: none;">
    <input name="project_id" id="project_id" value="<?php echo $this->item->id; ?>" type="hidden">
    <input name="client_id" id="client_id" value="<?php echo $this->item->id_client; ?>" type="hidden">
    <input name="type" value="calculator" type="hidden">
    <input name="subtype" value="calendar" type="hidden">
    <input id="project_verdict" name="project_verdict" value="0" type="hidden">
    <input id="project_status" name="project_status" value="<?php echo $this->item->project_status;?>" type="hidden">
    <input name="data_change" value="0" type="hidden">
    <input name="data_delete" value="0" type="hidden">
    <input id="mounting_date" name="mounting_date" type='hidden'>
    <input id="mount" name="mount" type='hidden' value='<?php echo $json_mount ?>'>
    <input id="jform_project_mounting_date" name="jform_project_mounting_date" value="<?php echo $this->item->project_mounting_date; ?>" type='hidden'>
    <input id="project_mounter" name="project_mounter" value="<?php echo $this->item->project_mounter; ?>" type='hidden'>
    <input id="project_sum" name="project_sum" value="<?php echo $project_total_discount; ?>" type="hidden">
    <input id="project_sum_transport" name="project_sum_transport" value="<?php echo $project_total_discount_transport; ?>" type="hidden">
    <input name="comments_id" id="comments_id" value="<?php if (isset($_SESSION['comments'])) echo $_SESSION['comments']; ?>" type="hidden">
    <input name = "activate_by_email" id = "activate_by_email" type = "hidden" value = 0>
    <input name = "project_new_calc_date" id = "jform_project_new_calc_date"  value="" type='hidden'>
    <input id="jform_project_gauger" name="project_gauger" value="" type='hidden'>
</div>
    <h2 class="center" style="margin-top: 15px; margin-bottom: 15px;">Проект № <?php echo $this->item->id ?></h2>
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-md-6 no_padding">
                <div>
                    <table class="table_info" style="border: 1px solid #414099;border-radius: 15px">
                         <button class="btn btn-sm btn-primary btn_edit" type = "button" id="change_data"><i class="fa fa-pencil" aria-hidden="true"></i></button>
                        <tr>
                            <th>
                                <?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?>
                            </th>
                            <td>
                                <a href="/index.php?option=com_gm_ceiling&view=clientcard&id=<?=$this->item->id_client;?>">
                                    <?php echo $this->item->client_id; ?>
                                </a>
                            </td>
                        </tr>
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
                            <td>
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
                            <td>
                                <?php
                                    foreach ($contact_email AS $contact) {
                                        echo "<a href='mailto:$contact->contact'>$contact->contact</a>";
                                        echo "<br>";
                                    }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan = 2 style="text-align: center;">
                                <button class="btn btn-primary" type="button" id="assign_call">Назначить звонок</button>    
                            </td>
                        </tr>
                    </table>
                    <br>
                    <table class="table_info" style="border: 1px solid #414099;border-radius: 15px">
                         <button class="btn btn-sm btn-primary btn_edit" type = "button" id="edit_address"><i class="fa fa-pencil" aria-hidden="true"></i></button>
                        <tr>
                            <th>
                                <?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?>
                            </th>
                            <td>
                                <a target="_blank" href="https://yandex.ru/maps/?mode=search&text=<?=$this->item->project_info;?>">
                                    <?=$this->item->project_info;?>
                                </a>
                            </td> 
                        </tr>
                        <tr>
                            <th><?php echo JText::_('COM_GM_CEILING_PROJECTS_PROJECT_CALCULATION_DATE'); ?></th>
                            <td>
                                <?php if ($this->item->project_calculation_date == "0000-00-00 00:00:00") { ?>
                                    -
                                <?php } else { ?>
                                    <?php $jdate = new JDate(JFactory::getDate($this->item->project_calculation_date)); ?>
                                    <?php echo $jdate->format('d.m.Y H:i'); ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php if(!empty($this->item->project_calculator)):?>
                            <tr>
                                <th>Замерщик</th>
                                <td><?php echo JFactory::getUser($this->item->project_calculator)->name;?></td>
                            </tr>
                        <?php endif;?>

                    </table>
                    <table class="table_info">
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
                        <tr>
                            <th>
                                Скидка
                            </th>
                            <td>
                                <?php echo (!empty($this->item->project_discount))?  $this->item->project_discount : " - ";?>
                            </td>
                            <td style="text-align: right;">
                                 <button class="btn btn-sm btn-primary" type = "button" id="edit_discount"><i class="fa fa-pencil" aria-hidden="true"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                Реклама
                            </th>
                            <td>
                                <?php echo (!empty($advt_str)) ? $advt_str : " - ";?>
                            </td>
                            <td style="text-align: right;">
                                 <button class="btn btn-sm btn-primary" type = "button" id="edit_advt"><i class="fa fa-pencil" aria-hidden="true"></i></button>
                            </td>
                        <tr>
                            <th>Примечание менеджера</th>
                            <td>
                                <?php echo $this->item->dealer_manager_note; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="col-xs-12 col-md-6 comment">
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
    <!-- активация проекта (назначение на монтаж, заключение договора) -->
    <?php if ($this->item->project_verdict == 0) { ?>
        <div class="container" <?php if (!empty($_GET['precalculation'])) {echo "style='display:none'";} ?> >
            <div class="row center">
                <div class="col-md-6">
                    <p>
                        <button class="btn btn-success act_btn" <?php echo $status_attr;?> id="accept_project" type="button">Договор</button>
                    </p>
                    <p>
                        <button id="simple_save" class="btn btn-primary act_btn">Сохранить</button>
                    </p>
                </div>
                <div class="col-md-6">
                    <p>
                        <button id="refuse" class="btn btn-danger act_btn" type="button">Отказ от договора</button>
                    </p>
                    <p>
                        <button id="refuse_cooperate" class="btn btn-danger act_btn" type="button">Отказ от сотрудничества</button>
                    </p>
                </div>
            
        </div>
    <?php } ?>
    <div class="project_activation" <?php if($user->dealer_type == 1 && $this->item->project_status == 4) echo "" /* "style=\"display: block;\"" */; else echo "style=\"display: none;\""?> id="project_activation">
        <?php if ($user->dealer_type != 2) { ?>
            <div id="mounter_wraper" <?php if($user->dealer_type == 1 && $this->item->project_status == 4) echo "style=\"display: block; margin-top: 25px;\""; else echo "style=\"display: none;\""?>>
            </div>
            <div class="row center">
                <h4>Назначить дату монтажа</h4>
                <div id="calendar_mount" align="center"></div>
            </div>
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
                <div class="col-md-4" style="padding-top: 25px;">
                    <button class="validate btn btn-primary save_bnt" id="save" type="button" from="form-client">Сохранить и запустить <br> в производство ГМ</button>
                </div>
                <div class="col-md-4" style="padding-top: 25px;">
                    <button class="validate btn btn-primary save_bnt" id="save_email" type="button" from="form-client">Сохранить и запустить <br> в производство по email</button>
                </div>
                <div class="col-md-4" style="padding-top: 25px;">
                    <button class="validate btn btn-primary save_bnt" id="save_exit" type="submit" from="form-client">Сохранить и выйти</button>
                </div>
            </div>
        <?php } ?>
    </div>
 
    <input name="idCalcDelete" id="idCalcDelete" value="<?=$calculation->id;?>" type="hidden">

    <div class="modal_window_container" id="mw_container">
        <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div class="modal_window" id="modal_window_measures_calendar" style="border: 2px solid black; border-radius: 4px;"></div>
        <div class="modal_window" id="modal_window_mounts_calendar"></div>
        <div id="modal_window_by_email" class = "modal_window">
            <p><strong>Введите адрес эл.почты:</strong></p>
            <p>
                <input id = "email_to_send" name = "email_to_send" class = "input-gm">
            </p>
            <p><button class = "btn btn-primary">Запустить</button></p>
        </div>
        <div id="mw_cl_info" class="modal_window">
            <h4>Изменение данных клиента</h4>
            <form id = "new_cl_info">
                <label> ФИО клиента: </label>
                <input name="new_client_name" id="jform_client_name" value="" placeholder="ФИО клиента" type="text">
                <table align="center" id="client_phones">
                    <thead>
                        <th>
                            Телефоны клиента
                        </th>
                        <th>
                            <button id="add_phone" class="btn btn-primary" type="button"><i class="fa fa-plus-square" aria-hidden="true"></i></button>
                        </th>
                    </thead>
                    <tbody>
                        <?php foreach ($phone as $value) { ?>
                            <tr>
                                <td>
                                     <input name="new_client_contacts[]" id="jform_client_contacts[]" data-old = "<?php echo $value->client_contacts;?>" placeholder="Телефон клиента" type="text" value=<?php echo $value->client_contacts;?>>
                                </td>
                                <td>
                                    <button class="btn btn-danger phone" type="button"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>    
                <table align="center" id="client_emails">
                    <thead>
                        <th>
                            Эл.почта клиента
                        </th>
                        <th>
                            <button id="add_email" class="btn btn-primary" type="button"><i class="fa fa-plus-square" aria-hidden="true"></i></button>
                        </th>
                    </thead>
                    <tbody>
                        <?php foreach ($contact_email as $value) { ?>
                            <tr>
                                <td>
                                     <input name="new_client_emails[]" id="jform_client_emails[]" placeholder="Email клиента" type="text" data-old="<?php echo $value->contact;?>" value=<?php echo $value->contact;?>>
                                </td>
                                <td>
                                    <button class="btn btn-danger email"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </form>
            <button id = "update_cl_info" class="btn btn-primary" type="button">Сохранить</button>
        </div>
        <div id="mw_rec_to_msr" class="modal_window">
            <div class="row">
                <div class="col-md-6">
                    <label><strong>Адрес замера</strong></label>
                    <table align="center">
                        <tr>
                            <td>Улица:</td>
                            <td style="padding-bottom: 10px;">
                                <input name="rec_address" id="jform_rec_address"  placeholder="Улица" type="text">                            
                            </td>
                        </tr>
                        <tr>
                            <td>Дом:</td>
                            <td style="padding-bottom: 10px;">
                                <input name="rec_house" id="jform_rec_house"  placeholder="Дом"  aria-required="true" type="text">
                            </td>
                        </tr>
                        <tr>
                            <td>Корпус:</td>
                            <td style="padding-bottom: 10px;">
                                <input name="rec_bdq" id="jform_rec_bdq"  placeholder="Корпус" aria-required="true" type="text">
                            </td>
                        </tr>
                        <tr>
                            <td>Квартира:</td>
                            <td style="padding-bottom: 10px;">
                                <input name="rec_apartment" id="rec_apartment" placeholder="Квартира"  aria-required="true" type="text">
                            </td>
                        </tr>
                        <tr>
                            <td>Подъезд:</td>
                            <td style="padding-bottom: 10px;">
                                <input name="rec_porch" id="jform_rec_porch" placeholder="Подъезд"  aria-required="true" type="text">
                            </td>
                        </tr>
                        <tr>
                            <td>Этаж:</td>
                            <td style="padding-bottom: 10px;">
                                <input name="rec_floor" id="jform_rec_floor" placeholder="Этаж" aria-required="true" type="text">
                            </td>
                        </tr>
                        <tr>
                            <td>Код:</td>
                            <td style="padding-bottom: 10px;">                
                                <input name="rec_code" id="jform_rec_code" placeholder="Код" aria-required="true" type="text">
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <label><strong>Время замера</strong></label>
                    <div id = "measures_calendar" align="center"></div>
                    <input  id="measure_info" readonly>
                </div>
            </div>
            <button  id = "save_rec" class="btn btn-primary" type="button">Сохранить</button>
        </div>
        <div id="mw_measures_calendar" class="modal-window1"></div>
        <div id="mw_mounts_calendar" class="modal_window"></div>
        <div id="mw_add_call" class="modal_window" >
            <h4>Добавить звонок</h4>
            <link rel="stylesheet" href="/components/com_gm_ceiling/date_picker/nice-date-picker.css">
            <script src="/components/com_gm_ceiling/date_picker/nice-date-picker.js"></script>
            <label><b>Дата: </b></label><br>
            <div id="calendar-wrapper" align="center"></div>
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
            <p><input name="call_date" id="call_date" type="hidden"></p>
            <p><input name="call_comment" id="call_comment" placeholder="Введите примечание"></p>
            <P><button class="btn btn-primary" id="add_call" type="button">Сохранить</button></p>
        </div>
        <div id="mw_discount" class="modal_window">
            <p>
                <label id="jform_discoint-lbl" for="jform_new_discount">Новый процент скидки:</label>
                <input name="new_discount" id="jform_new_discount" placeholder="%" min="0" max='<?= round($skidka, 0); ?>' type="number" style="width: 100%;">
            </p>
            <p>
                <button type="button" id="update_discount" class="btn btn-primary">Сохранить</button>
            </p>
        </div>
        <div id="mw_advt" class="modal_window">
            <h4>Изменение/добавление рекламы</h4>
            <label>Выберите или добавьте новую рекламу</label>
            <div class="row">  
                <div class="col-xs-6 col-md-6">
                    <p>
                        <label><strong>Выбрать:</strong></label>
                    </p>
                    <select id="advt_choose">
                        <option value="0">Выберите рекламу</option>
                        <?php if (!empty($all_advt)) foreach ($all_advt as $item) { ?>
                            <option value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-xs-6 col-md-6">
                     <p>
                        <label><strong>Добавить:</strong></label>
                    </p>
                     <div id="new_advt_div">
                        <p><input id="new_advt_name" placeholder="Название рекламы"></p>
                        <button type="button" class="btn btn-primary" id="add_new_advt">Добавить</button>
                    </div>
                </div>
            </div>
            <br>
            <button class="btn btn-primary" id="save_advt" type="button">Сохранить </button>
        </div>
    </div>
    </div>
</form>
    <script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
    <script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>

    <script type="text/javascript" src="/components/com_gm_ceiling/date_picker/measures_calendar.js"></script>
    <script type="text/javascript" src="/components/com_gm_ceiling/date_picker/mounts_calendar.js"></script>
    <script type="text/javascript">
    init_measure_calendar('measures_calendar','jform_project_new_calc_date','jform_project_gauger','mw_measures_calendar',[], 'measure_info');
    init_mount_calendar('calendar_mount','mount','mw_mounts_calendar',['close_mw','mw_container']);

        var $ = jQuery;
        var min_project_sum = <?php echo  $min_project_sum;?>;
        var min_components_sum = <?php echo $min_components_sum;?>;
        var self_data = JSON.parse('<?php echo $self_calc_data;?>');
        var project_id = "<?php echo $this->item->id; ?>";
        var precalculation = <?php if (!empty($_GET['precalculation'])) { echo $_GET['precalculation']; } else { echo 0; } ?>;
        var deleted_phones = [], deleted_emails = [];

        // закрытие окон модальных
        jQuery(document).mouseup(function (e){ // событие клика по веб-документу
            var div1 = jQuery("#modal_window_by_email");
            var div2 = jQuery("#mw_rec_to_msr");
            var div3 = jQuery("#mw_discount");
            var div4 = jQuery("#mw_add_call");
            var div5 = jQuery("#mw_advt");
            var div6 = jQuery("#mw_address");
            var div7 = jQuery("#mw_cl_info");
            var div8 = jQuery("#mw_mounts_calendar");
            var div9 = jQuery("#mw_measures_calendar");
            if (!div1.is(e.target) // если клик был не по нашему блоку
                && div1.has(e.target).length === 0
                && !div2.is(e.target)
                && div2.has(e.target).length === 0
                && !div3.is(e.target)
                && div3.has(e.target).length === 0
                && !div4.is(e.target)
                && div4.has(e.target).length === 0
                && !div5.is(e.target)
                && div5.has(e.target).length === 0
                && !div6.is(e.target)
                && div6.has(e.target).length === 0
                && !div7.is(e.target)
                && div7.has(e.target).length === 0
                && !div8.is(e.target)
                && div8.has(e.target).length === 0
                && !div9.is(e.target)
                && div9.has(e.target).length === 0) { // и не по его дочерним элементам
                jQuery("#close_mw").hide();
                jQuery("#mw_container").hide();
                div1.hide();
                div2.hide();
                div3.hide();
                div4.hide();
                div5.hide();
                div6.hide();
                div7.hide();
                div8.hide();
                div9.hide();
            }
        });

            

        jQuery(document).ready(function () {
            var client_id = "<?php echo $this->item->id_client;?>";
            var client_name = "<?php echo $this->item->client_id;?>";
            jQuery("[name = 'new_client_contacts[]']").mask('+7(999) 999-9999');
            document.getElementById('add_calc').onclick = function()
            {
                create_calculation(<?php echo $this->item->id; ?>);
            };
            

            if (document.getElementById('comments'))
            {
                show_comments();
            }

            jQuery("#change_data").click(function(){
                jQuery("#close_mw").show();
                jQuery("#mw_container").show();
                jQuery("#mw_cl_info").show();
            });

            jQuery("#edit_discount").click(function(){
                jQuery("#close_mw").show();
                jQuery("#mw_container").show();
                jQuery("#mw_discount").show();
            });

            jQuery("#assign_call").click(function(){
                jQuery("#close_mw").show();
                jQuery("#mw_container").show();
                jQuery("#mw_add_call").show();
            });

            jQuery("#edit_advt").click(function(){
                jQuery("#close_mw").show();
                jQuery("#mw_container").show();
                jQuery("#mw_advt").show();
            });

            jQuery("#edit_address").click(function(){
                jQuery("#close_mw").show();
                jQuery("#mw_container").show();
                jQuery("#mw_rec_to_msr").show();
            });
            // для истории и добавления комментария
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

            jQuery("#show_comments_btn").click(function(){
                jQuery("#comments_divs").toggle();

            });

            jQuery("#btn_show_mount_ready_date").click(function(){
                //jQuery("#container_calendars").toggle();
            });

            jQuery("#add_phone").click(function(){
                jQuery('#client_phones tr:last').after('<tr><td><input name="new_client_contacts[]" id="jform_client_contacts[]" placeholder="Телефон клиента"></td><td><button class="btn btn-danger phone" type="button"><i class="fa fa-trash-o" aria-hidden="true"></i></button></td></tr>');
                jQuery(".phone").click(function(){
                     var tr = jQuery(this).closest('tr');
                     remove_tr(tr,deleted_phones);
                });
                jQuery("[name = 'new_client_contacts[]']").mask('+7(999) 999-9999');
            });

            jQuery(".phone").click(function(){
                 var tr = jQuery(this).closest('tr');
                 remove_tr(tr,deleted_phones);
            });


            jQuery("#add_email").click(function(){
                jQuery('#client_emails tr:last').after('<tr><td><input name="new_client_emails[]" id="jform_client_emails[]" placeholder="Email клиента"></td><td><button class="btn btn-danger phone" type="button"><i class="fa fa-trash-o" aria-hidden="true"></i></button></td></tr>');
                jQuery(".phone").click(function(){
                     var tr = jQuery(this).closest('tr');
                     remove_tr(tr,deleted_phones);
                });
            });

            jQuery(".email").click(function(){
                 var tr = jQuery(this).closest('tr');
                 remove_tr(tr,deleted_emails);
            });

            function remove_tr (tr,arr){
                if(tr.find("input").val()){
                    arr.push(tr.find("input").val());
                }
                tr.remove();
            }


            jQuery("#update_cl_info").click(function(){
                var phones = jQuery.map(jQuery('[name = "new_client_contacts[]"]'),function(value){
                    if(value.value != jQuery(value).data("old"))
                        return {phone: value.value,old_phone:jQuery(value).data("old")};
                });
                var emails = jQuery.map(jQuery('[name = "new_client_emails[]'),function(value){
                    if(value.value != jQuery(value).data("old"))
                        return {email: value.value,old_email:jQuery(value).data("old")};
                });
                var new_name = ""; 
                if(jQuery("#jform_client_name").val() && jQuery("#jform_client_name").val() != client_name){
                    new_name = jQuery("#jform_client_name").val();
                }
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=client.update_info",
                    data: {
                        phones: phones,
                        emails: emails,
                        deleted_emails: deleted_emails,
                        deleted_phones: deleted_phones,
                        client_name: new_name,
                        client_id: client_id
                    },
                    dataType: "json",
                    async: true,
                    success: function (data) {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'topCenter',
                            maxVisible: 5,
                            type: "success",
                            text: "Данные успешно изменены!"
                        });
                        location.reload();
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

            jQuery("#save_rec").click(function(){

                var address = "",
                    street = jQuery("#jform_rec_address").val(),
                    house = jQuery("#jform_rec_house").val(),
                    bdq = jQuery("#jform_rec_bdq").val(),
                    apartment = jQuery("#jform_rec_apartment").val(),
                    porch = jQuery("#jform_rec_porch").val(),
                    floor =jQuery("#jform_rec_floor").val(),
                    code = jQuery("#jform_rec_code").val();
                if(house) address = street + ", дом: " + house;
                if(bdq) address += ", корпус: " + bdq;
                if(apartment) address += ", квартира: "+ apartment;
                if(porch) address += ", подъезд: " + porch;
                if(floor) address += ", этаж: " + floor;
                if(code) address += ", код: " + code;

                var data = {id:project_id,project_calculator:jQuery("#jform_project_gauger").val(), project_calculation_date:jQuery("#jform_project_new_calc_date").val(),project_info:address};

                data = JSON.stringify(data)

                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=project.change_project_data",
                    data: {
                        new_data: data
                    },
                    dataType: "json",
                    async: true,
                    success: function (data) {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'topCenter',
                            maxVisible: 5,
                            type: "success",
                            text: "Данные успешно изменены!"
                        });
                        location.reload();
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

            function add_history(id_client, comment) {
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
                            layout: 'topCenter',
                            maxVisible: 5,
                            type: "success",
                            text: "Добавленна запись в историю клиента"
                        });
                        if (jQuery("#client_id").val() == 1) {
                            
                            jQuery("#comments_id").val(jQuery("#comments_id").val() + data + ";");
                        }
                        show_comments();
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
            }

            jQuery("#add_comment").click(function () {
                var comment = jQuery("#new_comment").val();
                var reg_comment = /[\\\<\>\/\'\"\#]/;
                if (reg_comment.test(comment) || comment === "") {
                    alert('Неверный формат примечания!');
                    return;
                }
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=addComment",
                    data: {
                        comment: comment,
                        id_client: client_id
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
                        //new_comments_id.push(data);
                        //document.getElementById("comments_id").value +=data+";";
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
            jQuery("#add_birthday").click(function () {
                var birthday = jQuery("#jform_birthday").val();
                var id_client = <?php echo $this->item->id_client;?>;
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=client.addBirthday",
                    data: {
                        birthday: birthday,
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
                            text: "Дата рождения добавлена"
                        });
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

            jQuery("#jform_client_contacts").mask("+7 (999) 999-99-99");

            jQuery("#save_exit").click(function() {
                jQuery("input[name='project_status']").val(4);
                jQuery("input[name='project_verdict']").val(1);
            });
            jQuery("#save").click(function() {
                jQuery("input[name='project_status']").val(5);
                jQuery("input[name='project_verdict']").val(1);
                document.getElementById('form-client').submit();
            });
            $tmp_accept = 0; $tmp_refuse = 0;

            jQuery("#accept_project").click(function() {
                jQuery("input[name='project_verdict']").val(1);
                if($tmp_accept == 0) {
                    jQuery("#mounter_wraper").show();
                    jQuery(".contract").show();
                    jQuery("#title").show();
                    jQuery(".calendar_wrapper").show();
                    jQuery(".buttons_wrapper").show();
                    jQuery(".project_activation").hide();
                    jQuery("#project_activation").show();
                    $tmp_accept = 1;
                    $tmp_refuse = 0;
                } else {
                    jQuery(".project_activation").hide();
                    jQuery(".contract").hide();
                    jQuery("#mounter_wraper").hide();
                    jQuery("#title").hide();
                    jQuery(".calendar_wrapper").hide();
                    jQuery(".buttons_wrapper").hide();
                    jQuery("#project_activation").hide();
                    $tmp_accept = 0;
                    $tmp_refuse = 0;
                }
                setTimeout(() => {
                    window.location = "#project_activation";
                }, 100); 
            });
            jQuery("#refuse_project").click(function () {
                jQuery("input[name='project_verdict']").val(0);
                if($tmp_refuse == 0) {
                    jQuery(".project_activation").show();
                    jQuery("#refuse").show();
                    jQuery("#mounter_wraper").hide();
                    jQuery(".contract").hide();
                    jQuery("#title").hide();
                    jQuery(".calendar_wrapper").hide();
                    jQuery(".buttons_wrapper").hide();
                    jQuery("#mounting_date_control").hide();
                    $tmp_refuse = 1;
                    $tmp_accept = 0;
                } else {
                    jQuery(".project_activation").hide();
                    jQuery("#refuse").hide();
                    $tmp_refuse = 0;
                    $tmp_accept = 0;
                }
                setTimeout(() => {
                    window.location = "#refuse";
                }, 100); 
                //jQuery(".project_activation").toggle();
                //jQuery("#refuse").toggle();
            // 
            });
            
            jQuery("#change_data").click(function () {
                console.log(1);
                jQuery("#close_mw").show();
                jQuery("#mw_container").show();
                jQuery("#change_info_win").show();
            });

            
            if (precalculation == 1) {
                jQuery("input[name='data_change']").val(1);
                jQuery("#change_data").trigger('click');
                jQuery("#accept_project").trigger('click');
            }

            jQuery("#save_email").click(function(){
                jQuery("#activate_by_email").val(1);
                jQuery("#close_mw").show();
                jQuery("#mw_container").show();
                jQuery("#modal_window_by_email").show();
            });

            jQuery("#accept_changes").click(function () {
                jQuery("input[name='data_change']").val(1);
                jQuery("#close_mw").hide();
                jQuery("#mw_container").hide();
                jQuery("#change_info_win").hide();
            });

            var temp = 0;
            jQuery("#change_discount").click(function () {
                if (!temp) {
                    jQuery(".new_discount").show();
                    temp = 1;
                }
                else {
                    jQuery(".new_discount").hide();
                    temp = 0;
                }
            });

            jQuery("#update_discount").click(function() {
                save_data_to_session(4);
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=project.changeDiscount",
                    data: {
                        project_id: project_id,
                        new_discount: jQuery("#jform_new_discount").val()
                    },
                    dataType: "json",
                    async: true,
                    success: function (data) {
                        //console.log(data);
                        location.reload();
                    },
                    error: function (data) {
                        console.log(data);
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка изменения скидки"
                        });
                    }
                });
            });

            jQuery("#btn_ready_date").click(function() {
                if (jQuery("#date_canvas_ready").val() == '')
                {
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "warning",
                        text: "Укажите время готовности полотен"
                    });
                    jQuery("#date_canvas_ready").focus();
                    return;
                }
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=project.update_ready_time",
                    data: {
                        project_id: project_id,
                        ready_time: jQuery("#date_canvas_ready").val()
                    },
                    dataType: "json",
                    async: true,
                    success: function (data) {
                        noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "success",
                            text: "Время готовности полотен назначено"
                        });
                    },
                    error: function (data) {
                        console.log(data);
                        noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка"
                        });
                    }
                });
            });

            jQuery("#add_call").click(function(){
                if (jQuery("#call_date").val() == '')
                {
                    var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'topCenter',
                            maxVisible: 5,
                            type: "warning",
                            text: "Укажите дату перезвона"
                        });
                    return;
                }
                var date = jQuery("#call_date").val().replace(/(-)([\d]+)/g, function(str,p1,p2) {
                    if (p2.length === 1) {
                        return '-0'+p2;
                    }
                    else {
                        return str;
                    }
                });
                var time = jQuery("#call_time").val();
                if (time == '') {
                    time = '00:00';
                }
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=addCall",
                    data: {
                        id_client: client_id,
                        date: date+' '+time,
                        comment: jQuery("#call_comment").val()
                    },
                    dataType: "json",
                    async: true,
                    success: function (data) {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'topCenter',
                            maxVisible: 5,
                            type: "success",
                            text: "Звонок добавлен"
                        });
                        add_history(client_id, 'Добавлен звонок на ' + date + ' ' + time + ':00');
                    },
                    error: function (data) {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка сервера"
                        });
                    }
                });
            });

            if (document.getElementById('refuse_cooperate')){
                document.getElementById('refuse_cooperate').onclick = click_on_refuse_cooperate;
            }


            function click_on_refuse_cooperate()
            {
                noty({
                    layout: 'topCenter',
                    type: 'default',
                    modal: true,
                    text: 'Перевести проект в статус "отказ от сотрудничества"?',
                    killer: true,
                    buttons: [
                        {
                            addClass: 'btn btn-success', text: 'Ок', onClick: function ($noty) {
                                jQuery.ajax({
                                    url: "index.php?option=com_gm_ceiling&task=project.updateProjectStatus",
                                    data: {
                                        project_id: project_id,
                                        status: 15
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
                                            text: "Проект переведен в отказ от сотрудничества"
                                        });
                                        setTimeout(function(){location.href = '/index.php?option=com_gm_ceiling&task=mainpage'}, 2000);
                                    },
                                    error: function (data) {
                                        var n = noty({
                                            timeout: 2000,
                                            theme: 'relax',
                                            layout: 'center',
                                            maxVisible: 5,
                                            type: "error",
                                            text: "Ошибка сервера"
                                        });
                                    }
                                });
                                $noty.close();
                            }
                        },
                        {
                            addClass: 'btn', text: 'Отмена', onClick: function ($noty) {
                                $noty.close();
                            }
                        }
                    ]
                });
            };

            jQuery("#refuse").click(function(){
                jQuery('#project_status').val(3);
                jQuery("#project_verdict").val(0);
                document.getElementById('form-client').submit();
            });

            jQuery("#save_advt").click(function() {
                if (jQuery("#advt_choose").val() == '0' || jQuery("#advt_choose").val() == '') {
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "warning",
                        text: "Укажите рекламу"
                    });
                    jQuery("#advt_choose").focus();
                    return;
                }
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=project.save_advt",
                    data: {
                        project_id: project_id,
                        api_phone_id: jQuery("#advt_choose").val(),
                        client_id: client_id
                    },
                    dataType: "json",
                    async: true,
                    success: function(data) {
                        document.getElementById('save_advt').style.display = 'none';
                        document.getElementById('advt_choose').disabled = 'disabled';
                        noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "success",
                            text: "Реклама сохранена"
                        });
                        location.reload();
                    },
                    error: function(data) {
                        console.log(data);
                        noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка"
                        });
                    }
                });
            });

            jQuery("#add_new_advt").click(function() {
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=addNewAdvt",
                    data: {
                        name: jQuery("#new_advt_name").val()
                    },
                    dataType: "json",
                    async: true,
                    success: function (data) {
                        select = document.getElementById('advt_choose');
                        var opt = document.createElement('option');
                        opt.selected = true;
                        opt.value = data.id;
                        opt.innerHTML = data.name;
                        select.appendChild(opt);
                        jQuery("#new_advt_name").val('');
                    },
                    error: function (data) {
                        console.log(data);
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "ошибка"
                        });
                    }
                });
            });

        }); //конец ready

        //ymaps.ready(init);

        /*var Data = {};
        function init() {
            // Подключаем поисковые подсказки к полю ввода.
            var suggestView = new ymaps.SuggestView('jform_address');
            input = jQuery('#jform_address');

            suggestView.events.add('select', function (e) {
            var s = e.get('item').value.replace('Россия, ','');
            input.val(s);
            });

            Data.ProjectInfoYMaps = $("#jform_address").siblings("ymaps");
            Data.ProjectInfoYMaps.click(hideYMaps);
        }

        function hideYMaps() {
            setTimeout(function () {
                Data.ProjectInfoYMaps.hide();
                $("#jform_house").focus();
            }, 75);
        }*/
    </script>
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
    top:0px;
    right:0px;
}
</style>
 <form id="form-client" action="/index.php?option=com_gm_ceiling&task=project.activate&type=calculator&subtype=calendar" method="post" enctype="multipart/form-data">
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
    </div>
       <?= parent::getButtonBack();?>
        <h4 class="center" style="margin-top: 15px; margin-bottom: 15px;">Проект № <?php echo $this->item->id ?></h4>
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-md-6 no_padding">
                    <div>
                        <table class="table_info" style="border: 1px solid #414099;border-radius: 35px">
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
                        <table class="table_info">
                            <tr>
                                <th>
                                    <?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?>
                                </th>
                                <td>
                                    <a target="_blank" href="https://yandex.ru/maps/?mode=search&text=<?=$this->item->project_info;?>">
                                        <?=$this->item->project_info;?>
                                    </a>
                                </td>
                                <td style="text-align: right;">
                                     <button class="btn btn-sm btn-primary" type = "button" id="edit_address"><i class="fa fa-pencil" aria-hidden="true"></i></button>
                                </td>
                            </tr>
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
                        <div class="col-md-6" style="padding-top: 25px;" align="center">
                            <p>
                                <button class="btn btn-success act_btn" type="button" <?php echo $status_attr;?> id="accept_project">
                                Договор/производство</button>
                            </p>
                            <p>
                                <button class="btn btn-success act_btn" <?php echo $status_attr;?> id="rec_to_mesure" type="button" >Записать на замер</button>
                            </p>
                        </div>
                        <div class="col-md-6" style="padding-top: 25px;" align="center">
                            <p>
                                <button id="ref_btn" class="btn btn-danger act_btn" type="button">Отказ</button>
                                <div id = refuse_block align="left" style="display: none;">
                                    <p>
                                        <input id='refuse_measure' type='radio' class = "radio" name='slider-refuse' value='2'>
                                        <label  for='refuse_measure'>от замера</label>
                                    </p>
                                    <p>
                                        <input id='refuse_deal' type='radio' class = "radio" name='slider-refuse' value='3'>
                                        <label  for='refuse_deal'>от договора</label>
                                    </p>
                                    <p>
                                        <input id='refuse_coop' type='radio' class = "radio" name='slider-refuse' value='15'>
                                        <label  for='refuse_coop'>от сотрудничества</label>
                                    </p>
                                </div>
                            </p>
                            <p>
                                <button id="save_btn" class="btn btn-primary act_btn" type="">Сохранить</button>  
                            </p>
                        </div>
                    </div>  
                </div>
            <?php } ?>
            <div class="project_activation" id="project_activation" style="display: none;">
                <h4>Назначить дату монтажа</h4>
                    <div>
                        Calendar
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
                    <div class="col-md-6" style="padding-top: 25px;">
                        <button class="validate btn btn-primary save_bnt" id="save" type="button" from="form-client">Сохранить и запустить <br> в производство ГМ</button>
                    </div>
                    <div class="col-md-6" style="padding-top: 25px;">
                        <button class="validate btn btn-primary save_bnt" id="save_email" type="button" from="form-client">Сохранить и запустить <br> в производство по email</button>
                    </div>
                </div>
            </div>
        <!-- Всплывающие окна -->
        <div class="modal_window_container" id="mw_container">
            <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
            <div id="mw_cl_info" class="modal_window">
                <h4>Изменение данных клиента</h4>
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
                                     <input name="new_client_contacts[]" id="jform_client_contacts[]" placeholder="Телефон клиента" type="text" value=<?php echo $value->client_contacts;?>>
                                </td>
                                <td>
                                    <button class="btn btn-danger"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <table align="center" id="client_phones">
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
                                     <input name="new_client_contacts[]" id="jform_client_contacts[]" placeholder="Телефон клиента" type="text" value=<?php echo $value->contact;?>>
                                </td>
                                <td>
                                    <button class="btn btn-danger"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <button id = "update_cl_info" class="btn btn-primary">Сохранить</button>
            </div>
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
            <div id="mw_address" class="modal_window">
                <h4>Изменение адреса</h4>
                <table align="center">
                    <tr>
                        <td>Улица:</td>
                        <td style="padding-bottom: 10px;">
                            <input name="new_address" id="jform_address" value="<?=$street?>" placeholder="Улица" type="text">                            
                        </td>
                    </tr>
                    <tr>
                        <td>Дом:</td>
                        <td style="padding-bottom: 10px;">
                            <input name="new_house" id="jform_house" value=" <?php if (isset($_SESSION['house'])) { echo $_SESSION['house']; } else echo $house ?>" placeholder="Дом"  aria-required="true" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td>Корпус:</td>
                        <td style="padding-bottom: 10px;">
                            <input name="new_bdq" id="jform_bdq"  value="<?php if (isset($_SESSION['bdq'])) { echo $_SESSION['bdq']; } else echo $bdq ?>" placeholder="Корпус" aria-required="true" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td>Квартира:</td>
                        <td style="padding-bottom: 10px;">
                            <input name="new_apartment" id="jform_apartment" value="<?php if (isset($_SESSION['apartment'])) {echo $_SESSION['apartment']; } else echo $apartment ?>" placeholder="Квартира"  aria-required="true" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td>Подъезд:</td>
                        <td style="padding-bottom: 10px;">
                            <input name="new_porch" id="jform_porch"  value="<?php if (isset($_SESSION['porch'])) {echo $_SESSION['porch']; } else echo $porch ?>" placeholder="Подъезд"  aria-required="true" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td>Этаж:</td>
                        <td style="padding-bottom: 10px;">
                            <input name="new_floor" id="jform_floor"  value="<?php if (isset($_SESSION['floor'])) {echo $_SESSION['floor']; } else echo $floor ?>" placeholder="Этаж" aria-required="true" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td>Код:</td>
                        <td style="padding-bottom: 10px;">                
                            <input name="new_code" id="jform_code"  value="<?php if (isset($_SESSION['code'])) {echo $_SESSION['code']; } else echo $code ?>" placeholder="Код" aria-required="true" type="text">
                        </td>
                    </tr>
                </table>
                <button class="btn btn-primary" type="button" id = "update_address">Сохранить</button>
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
            <div id="mw_rec_to_msr" class="modal_window">
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
                <label><strong>Время замера</strong></label>
                <div>Здесь календарь замеров</div>
                <button id = "save_rec" class="btn btn-primary" type="button">Сохранить</button>
            </div>
        </div>
    </form>
     <script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
    <script type="text/javascript" src="/components/com_gm_ceiling/views/project/common_table.js"></script>

    <script type="text/javascript" src="/components/com_gm_ceiling/date_picker/measures_calendar.js"></script>
    <script type="text/javascript" src="/components/com_gm_ceiling/date_picker/mounts_calendar.js"></script>
    <script type="text/javascript">
        var $ = jQuery;
        var min_project_sum = <?php echo  $min_project_sum;?>;
        var min_components_sum = <?php echo $min_components_sum;?>;
        var self_data = JSON.parse('<?php echo $self_calc_data;?>');
        var project_id = "<?php echo $this->item->id; ?>";
        var precalculation = <?php if (!empty($_GET['precalculation'])) { echo $_GET['precalculation']; } else { echo 0; } ?>;

        // закрытие окон модальных
        jQuery(document).mouseup(function (e){ // событие клика по веб-документу
            var div1 = jQuery("#mw_cl_info");
            var div2 = jQuery("#mw_discount");
            var div3 = jQuery("#mw_add_call");
            var div4 = jQuery("#mw_advt");
            var div5 = jQuery("#mw_address");
            var div6 = jQuery("#mw_rec_to_msr");
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
                && div6.has(e.target).length === 0) { // и не по его дочерним элементам
                jQuery("#close_mw").hide();
                jQuery("#mw_container").hide();
                div1.hide();
                div2.hide();
                div3.hide();
                div4.hide();
                div5.hide();
                div6.hide();
            }
        });
        jQuery(document).ready(function(){

            var client_id = "<?php echo $this->item->id_client;?>";

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
                jQuery("#mw_address").show();
            });

            jQuery("#rec_to_mesure").click(function(){
                jQuery("#close_mw").show();
                jQuery("#mw_container").show();
                jQuery("#mw_rec_to_msr").show();
            });

            jQuery("#ref_btn").click(function(){
                jQuery("#refuse_block").toggle();
            });

            jQuery("#accept_project").click(function(){
                jQuery("#project_activation").toggle();
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

            jQuery("#show_comments_btn").click(function(){
                jQuery("#comments_divs").toggle();

            });

            jQuery("#update_address").click(function(){
                var street = jQuery("#jform_address").val(),
                house = jQuery("#jform_house").val(),
                bdq = jQuery("#jform_bdq").val(),
                apartment = jQuery("#jform_apartment").val(),
                porch = jQuery("#jform_porch").val(),
                floor =jQuery("#jform_floor").val(),
                code = jQuery("#jform_code").val();
                if(house) address = street + ", дом: " + house;
                if(bdq) address += ", корпус: " + bdq;
                if(apartment) address += ", квартира: "+ apartment;
                if(porch) address += ", подъезд: " + porch;
                if(floor) address += ", этаж: " + floor;
                if(code) address += ", код: " + code;

                jQuery.ajax({
                        url: "index.php?option=com_gm_ceiling&task=project.change_address",
                        data: {
                            id: project_id,
                            address: address
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
                                text: "Адрес успешно изменен!"
                            });
                             location.reload();
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
        });
    </script>
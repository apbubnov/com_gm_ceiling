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
$reserve_model = Gm_ceilingHelpersGm_ceiling::getModel('reservecalculation');
$client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
$clients_dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
$color_model_1 = Gm_ceilingHelpersGm_ceiling::getModel('components');
$canvas_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');

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
if ($user->dealer_type == 1 && $user->dealer_mounters == 1) {
    $FlagCalendar = [2, 1];
} else {
    $FlagCalendar = [2, $user->dealer_id];
}
$calendar1 = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($userId, $month1, $year1, $FlagCalendar);
$calendar2 = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($userId, $month2, $year2, $FlagCalendar);
//----------------------------------------------------------------------------------

// все бригады
if ($user->dealer_type == 1 && $user->dealer_mounters == 1) {
    $Allbrigades = $calculationsModel->FindAllbrigades(1);
} else {
    $Allbrigades = $calculationsModel->FindAllbrigades($user->dealer_id);
}
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
    $AllMounters = $calculationsModel->FindAllMounters($where);
}
//----------------------------------------------------------------------------------

/* замерщики */
$AllGauger = $reserve_model->FindAllGauger($user->dealer_id);
if (count($AllGauger) == 0) {
    array_push($AllGauger, ["id" => $userId, "name" => $user->name]);
}
$month = date("n");
$year = date("Y");
$flagGaugerCalendar = [3, $user->dealer_id];
$g_calendar = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($userId, $month, $year, $flagGaugerCalendar);
//------------------------------

?>

<link rel="stylesheet" href="/components/com_gm_ceiling/views/project/css/style.css" type="text/css" />
<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>

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
        max-width: 300px;
        font-size: 13px;
    }
    #table1 button, #table1 a, #table1 input {
        font-size: 13px;
        max-width: 150px;
    }
    #table1 td, #table1 th {
        padding: 10px 5px;
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
    @media screen and (min-width: 768px) {
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

<?= parent::getButtonBack(); ?>
<?php if ($this->item) : ?>
    <form id="form-client" action="/index.php?option=com_gm_ceiling&task=project.activate&type=calculator&subtype=calendar" method="post" enctype="multipart/form-data">
        <h2 class="center" style="margin-top: 15px; margin-bottom: 15px;">Проект № <?php echo $this->item->id ?></h2>
        <div class="row">
            <div class="col-xs-12 col-md-6 no_padding">
                    <?php if ($this->type === "calculator" && $this->subtype === "calendar") { ?>
                        <?php if ($user->dealer_type != 2) { ?>
                            <div class="center-left">
                                <a class="btn btn-primary" id="change_data">
                                    <?php
                                        if ($_GET['precalculation'] == 1){
                                            echo "Заполнить данные о клиенте";
                                        }
                                        else {
                                            echo "Изменить данные";
                                        }  
                                    ?>
                                </a>
                            </div>
                        <?php } ?>
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
                        <?php if ($user->dealer_type != 2) { ?>
                            <div>
                                <table class="table_info" style="margin-bottom: 25px;">
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
                                    <?php
                                        if ($user->dealer_type == 0) {
                                           
                                            $birthday = $client_model->getClientBirthday($this->item->id_client);
                                    ?>
                                        <tr>
                                            <th>Дата рождения</th>
                                            <td>
                                                <input name="new_birthday" id="jform_birthday" class="inputactive" value="<?php if ($birthday->birthday != 0000-00-00)  echo $birthday->birthday ;?>" placeholder="Дата рождения" type="date">
                                            </td>
                                            <td>
                                                <button type="button" class = "btn btn-primary" id = "add_birthday">Ок</button>
                                            </td>
                                        </tr>
                                    <?php } ?>
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
                                    <?php if ($this->item->id_client!=1) { ?>
                                        <tr>
                                            <th>Почта</th>
                                            <td>
                                                <?php
                                                    
                                                    $contact_email = $clients_dop_contacts_model->getContact($this->item->id_client);
                                                    foreach ($contact_email AS $contact) {
                                                        echo "<a href='mailto:$contact->contact'>$contact->contact</a>";
                                                        echo "<br>";
                                                    }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
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
                                    <?php if(!empty($this->item->project_mounter)):?>
                                        <tr>
                                            <th>Монтажная бригада</th>
                                            <td><?php echo JFactory::getUser($this->item->project_mounter)->name;?></td>
                                        </tr>
                                    <?php endif;?>
                                    <tr>
                                        <th>Примечание менеджера</th>
                                        <td>
                                            <?php echo $this->item->dealer_manager_note; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        <?php } ?>
                        <!-- стиль поменяю, когда буду править расширенного диллера страницы -->
                            <?php if($user->dealer_type == 0) { ?>
                                <div  class="col-12 col-md-6">
                                    <div class="comment">
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
                            <?php } ?>
                        <!-- конец -->
                        <?php if ($this->item->project_verdict == 0 && $user->dealer_type != 2) { ?>
                            <div class="center-left">
                                <a class="btn btn-primary" id="change_discount">Изменить величину скидки</a>
                            </div>
                        <?php } ?>
                        <table class="calculation_sum">
                            <?php $skidka = ($calculation_total - $project_total_1) / $calculation_total * 100; ?>
                            <tbody class="new_discount" style="display: none">
                                <tr>
                                    <td>
                                        <label id="jform_discoint-lbl" for="jform_new_discount">Новый процент скидки:<span class="star">&nbsp;*</span></label>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>
                                        <input name="new_discount" id="jform_new_discount" value="" onkeypress="PressEnter(this.value, event)" placeholder="Новый % скидки" max='<?= round($skidka, 0); ?>' type="number" style="width: 100%;">
                                        <input name="isDiscountChange" value="0" type="hidden">
                                    </td>
                                    <td>
                                        <button id="update_discount" class="btn btn btn-primary">Ок</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    <?php } ?>
            </div>
        </div>
        <!-- модальное окно - изменение инфы о клиенте -->
        <div id="change_info" class="modal-window-container">
            <button class="btn-close" type="button"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
            <div id="change_info_win" class="modal-window-tar">
                <p><strong>Изменение данных</strong></p>
                <br>
                <p>ФИО клиента: <span class="star">&nbsp;*</span> <?php echo $this->item->client_id; ?></p>
                <p>
                    <input name="new_client_name" id="jform_client_name" value="" placeholder="ФИО клиента" type="text">
                </p>
                <p>Телефон клиента: <span class="star">&nbsp;*</span></p>
                <p>
                    <input name="new_client_contacts" id="jform_client_contacts" value="" placeholder="Телефон клиента" type="text">
                </p>
                <p>Адрес клиента: <span class="star">&nbsp;*</span></p>
                <p>
                    <table style="width: 100%;">
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
                </p>
                <p>Дата и время замера: 
                    <?php if ($this->item->project_calculation_date == "0000-00-00 00:00:00") { ?>
                        -
                    <?php } else { ?>
                        <?php $jdate = new JDate(JFactory::getDate($this->item->project_calculation_date)); ?>
                        <?php echo $jdate->format('d.m.Y H:i'); ?>
                    <?php } ?>
                </p>
                <p>Замерщик: <?php echo JFactory::getUser($this->item->project_calculator)->name;?></p>
                <p>
                    <div id = "calendar_container" class="Date" style="position: relative;">
                        <div class="btn-small-l">
                            <button id="g_button-prev" class="button-prev-small" type="button" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i></button>
                        </div>
                        <div id = "g_calendar">
                            <?php echo $g_calendar; ?>
                        </div>
                        <div class="btn-small-r">
                            <button id="g_button-next" class="button-next-small" type="button" class="btn btn-primary"><i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                        </div>
                    </div>
                </p>
                <p>
                    <button type="submit" id="accept_changes" class="btn btn btn-primary">Сохранить клиента</button>
                </p>
            </div>
        </div>
        <!-- окно выбора замерщика -->
        <div id="modal_window_g_container" class = "modal_window_container">
            <button id="close-tar" type="button"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
            <div id="modal_window_g_choose" class = "modal_window">
                <p id="g_date-modal"></p>
                <p><strong>Выберите время замера (и замерщика):</strong></p>
                <p>
                    <table id="projects_gaugers"></table>
                </p>
            </div>
        </div>
        <!-- расчеты для проекта -->
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
                        <button class="nav-link" id="add_calc" style="color:white;">
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
                                        <span class = "sum"><?php echo $total_square;?></span> м<sup>2</sup> /
                                    </th>
                                    <th colspan="2" id="total_perimeter">
                                        <span class = "sum"><?php echo $total_perimeter; ?></span> м
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
                                            <span class="sum"><?= round($project_total, 0);?> р.</span>
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
                                if (!empty($calculation->n2)) {
                                    $filename = "/calculation_images/" . md5("calculation_sketch" . $calculation->id) . ".svg";
                                }
                        ?>
                                <div class="tab-pane <?=($user->dealer_type == 1 && $first)?"active":""; $first = false;?>" id="calculation<?php echo $calculation->id; ?>" role="tabpanel">
                                    <div class="other_tabs">
                                    <?php if($this->item->project_status < 5 || $this->item->project_status == 22) {?>
                                        <a class="btn btn-primary" href="index.php?option=com_gm_ceiling&view=calculationform&type=calculator&subtype=calendar&calc_id=<?php echo $calculation->id; ?>">Изменить расчет</a>
                                    <?php } ?>
                                        <?php if (!empty($filename)):?>
                                            <div class="sketch_image_block" style="margin-top: 15px;">
                                                <h4>Чертеж <i class="fa fa-sort-desc" aria-hidden="true"></i></h4>
                                                <div class="section_content">
                                                    <img class="sketch_image" src="<?php echo $filename.'?t='.time(); ?>"/>
                                                </div>
                                            </div>
                                        <?php endif; ?>
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
                                                                    } else  {
                                                                   
                                                                    $color_1 = $color_model_1->getColorId($calculation->n6);
                                                                ?>
                                                                        <td>Цветная:</td>
                                                                        <td>
                                                                            <?php echo $color_1[0]->title; ?> <img style='width: 50px; height: 30px;' src="/<?php echo $color_1[0]->file; ?>" alt=""/>
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
                                                    <?php } ?>
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
        <!-- активация проекта (назначение на монтаж, заключение договора) -->
        <?php if($user->dealer_type == 1 && count($calculations) <= 0) { } else {?>
            <?php if (($this->item->project_verdict == 0 && $user->dealer_type != 2) || ($this->item->project_verdict == 1 && $user->dealer_type == 1 && $this->item->project_status == 4)) { ?>
                <table <?php if (!empty($_GET['precalculation'])) {echo "style='display:none'";} ?> >
                    <tr>
                        <td style="padding: 25px 10px">
                            <a class="btn  btn-success" id="accept_project" >Договор</a>
                        </td>
                        <td style="padding: 25px 10px">
                            <a class="btn  btn-danger" id="refuse_project">Отказ</a>
                        </td>
                    </tr>
                </table>
            <?php } ?>
            <div class="project_activation" <?php if($user->dealer_type == 1 && $this->item->project_status == 4) echo "" /* "style=\"display: block;\"" */; else echo "style=\"display: none;\""?> id="project_activation">
                <?php if ($user->dealer_type != 2) { ?>
                    <table style="margin-top: 25px;">
                        <tr>
                            <td Style="padding-right: 15px;">
                                <label id="jform_gm_calculator_note-lbl" for="jform_gm_calculator_note" class="">Примечание к договору</label>
                            </td>
                            <td>
                                <textarea name="gm_calculator_note" id="jform_gm_calculator_note" placeholder="Примечание к договору" aria-invalid="false"><?=($this->item->dealer_calculator_note)?$this->item->dealer_calculator_note:""?></textarea>
                            </td>
                        </tr>
                    </table>
                    <button id="refuse" class="btn btn-success" type="submit" style="display: none; margin-top: 25px;">Переместить в отказы</button>
                    <div id="mounter_wraper" <?php if($user->dealer_type == 1 && $this->item->project_status == 4) echo "style=\"display: block; margin-top: 25px;\""; else echo "style=\"display: none;\""?>>
                        <table id="container_calendars">
                            <tr>
                                <td colspan="3">
                                    <h4 class="center" id="title" style="display: none; margin-bottom: 25px;">Назначить дату монтажа (дату готовности потолка)</h4>
                                </td>
                            </tr>
                            <tr>
                                <td class="no_yes_padding">
                                    <button id="button-prev" type="button" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i></button>
                                </td>
                                <td>
                                    <div style="display: inline-block; width: 100%;">
                                        <div id="calendar1">
                                            <?php echo $calendar1; ?>
                                        </div>
                                        <div id="calendar2">
                                            <?php echo $calendar2; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="no_yes_padding">
                                    <button id="button-next" type="button" class="btn btn-primary"><i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <table class="contract" style="margin-top: 25px;">
                        <tr>
                            <td Style="padding-right: 15px;">
                                <label id="jform_chief_note-lbl" for="jform_chief_note" class="">Примечание к монтажу</label>
                            </td>
                            <td>
                                <textarea name="chief_note" id="jform_chief_note" placeholder="Примечание к монтажу" aria-invalid="false"><?php echo $this->item->dealer_chief_note; ?></textarea>
                            </td>
                        </tr>
                    </table>
                    <p class="contract" style="margin-top: 25px; margin-bottom: 0;">
                        <input name='smeta' value='0' type='checkbox'> Отменить смету по расходным материалам
                    </p>
                    <div class="contract" class="row">
                        <div class="col-xs-12 col-md-4" style="padding-top: 25px;">
                            <button class="validate btn btn-primary" id="save" type="submit" from="form-client">Сохранить и запустить <br> в производство</button>
                        </div>
                        <div class="col-xs-12 col-md-4" style="padding-top: 25px;">
                            <button class="validate btn btn-primary" id="save_email" type="button" from="form-client">Сохранить и запустить <br> в производство по email</button>
                        </div>
                        <div class="col-xs-12 col-md-4" style="padding-top: 25px;">
                            <button class="validate btn btn-primary" id="save_exit" type="submit" from="form-client">Сохранить и выйти</button>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
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
                <p style="margin-top: 1em;"><strong>Выберите время начала монтажа (время готовности потолка):</strong></p>
                <p>
                    <select name="hours" id='hours'></select>
                </p>
                <p><button type="button" id="save-choise-tar" class="btn btn-primary">Ок</button></p>
            </div>
        </div>
        <div id="modal_window_activate" class = "modal_window_container">
            <div id="modal_window_by_email" class = "modal_window">
                <p><strong>Введите адрес эл.почты:</strong></p>
                <p>
                    <input id = "email_to_send" name = "email_to_send" class = "input-gm">
                </p>
                <p><button class = "btn btn-primary">Запустить</button></p>
            </div>
        </div>
        <input name="idCalcDelete" id="idCalcDelete" value="<?=$calculation->id;?>" type="hidden">
    </form>
    <div id="modal-window-container-tar">
        <div id="modal-window-1-tar">
            <p>Введите данные для связи с Вами</p>
            <p><input type="text" id="name-tar" placeholder="Имя" required></p>
            <p><input type="text" id="phone-tar" placeholder="Телефон" required></p>
            <p><button type="button" id="modal-ok-tar" class="btn btn-primary">OK</button></p>
        </div>
    </div>
    <div id="modal-window-container2-tar">
        <button type="button" id="close-tar"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="modal-window-2-tar">
            <p>Приветствуем Вас в Вашем личном кабинете и дарим вам 1 купон за регистрацию в приложении.</p>
            <p><button type="button" id="preview-tar" class="btn btn-primary">Подробнее</br>о купоне</button></p>
            <p><button type="button" id="what-tar" class="btn btn-primary">What</button></p>
        </div>
    </div>
    <div id="modal-window-container3-tar">
        <div id="modal-window-3-tar">
            <p>What</p>
            <p><button type="button" id="what2-tar" class="btn btn-primary">What2</button></p>
        </div>
    </div>
    <div id="modal_window_container" class="modal_window_container">
        <button type="button" id="close" class="close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="modal_window_del" class="modal-window-tar">
            <p style="/*margin-top:10px*/"><strong>Вы действительно хотите удалить?</strong></p>
            <p>
                <button type="button" id="ok" class="btn btn-primary">Да</button>
                <button type="button" id="cancel" onclick="click_cancel();" class="btn btn-primary">Отмена</button>
            </p>
        </div>
    </div>

    <script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>
    
    <script type="text/javascript">
        var $ = jQuery;
        var min_project_sum = <?php echo  $min_project_sum;?>;
        var min_components_sum = <?php echo $min_components_sum;?>;
        var self_data = JSON.parse('<?php echo $self_calc_data;?>');
        console.log(self_data);
        var precalculation = <?php if (!empty($_GET['precalculation'])) { echo $_GET['precalculation']; } else { echo 0; } ?>;

        function PressEnter(your_text, your_event) {
            if (your_text != "" && your_event.keyCode == 13)
                jQuery("#update_discount").click();
        }

        function submit_form(e) {
            jQuery("#modal_window_container, #modal_window_container *").show();
            jQuery('#modal_window_container').addClass("submit");
        }

        function click_ok(e) {
            var modal = $(e).closest("#modal_window_container");
            if (modal.hasClass("submit"))
            {
                var select_tab = $(".tab-pane.active").find("#idCalcDeleteSelect").val();
                
                $("#idCalcDelete").val(select_tab);
                modal.removeClass("submit");
                jQuery("input[name='data_delete']").val(1);
                document.getElementById("form-client").submit();
            }  
        }

        function click_cancel(e) {
            jQuery("#modal_window_container, #modal_window_container *").hide();
        }

        // закрытие окон модальных
        jQuery(document).mouseup(function (e){ // событие клика по веб-документу
            var div = jQuery("#modal_window_del"); // тут указываем ID элемента
            if (!div.is(e.target) // если клик был не по нашему блоку
                && div.has(e.target).length === 0) { // и не по его дочерним элементам
                jQuery("#close").hide();
                jQuery("#modal_window_container").hide();
                jQuery("#modal_window_del").hide();
            }
            var div1 = jQuery("#modal-window-2-tar"); // тут указываем ID элемента
            if (!div1.is(e.target) // если клик был не по нашему блоку
                && div1.has(e.target).length === 0) { // и не по его дочерним элементам
                jQuery("#close-tar").hide();
                jQuery("#modal-window-container2-tar").hide();
                jQuery("#modal-window-2-tar").hide();
            }
            var div2 = jQuery("#modal-window-3-tar"); // тут указываем ID элемента
            if (!div2.is(e.target) // если клик был не по нашему блоку
                && div2.has(e.target).length === 0) { // и не по его дочерним элементам
                jQuery("#modal-window-container3-tar").hide();
                jQuery("#modal-window-3-tar").hide();
            }
            var div3 = jQuery("#modal_window_g_choose"); // тут указываем ID элемента
            if (!div3.is(e.target) // если клик был не по нашему блоку
                && div3.has(e.target).length === 0) { // и не по его дочерним элементам
                jQuery("#close").hide();
                jQuery("#modal_window_g_container").hide();
                jQuery("#modal_window_g_choose").hide();
            }
            var div4 = jQuery("#modal_window_by_email");
            if (!div4.is(e.target) // если клик был не по нашему блоку
                && div4.has(e.target).length === 0) { // и не по его дочерним элементам
                jQuery("#close").hide();
                jQuery("#modal_window_activate").hide();
                jQuery("#modal_window_by_email").hide();
            }
            var div5 = jQuery("#modal-window-choose-tar");
            if (!div5.is(e.target)
                && div5.has(e.target).length === 0) {
                jQuery("#close-tar").hide();
                jQuery("#modal-window-container-tar").hide();
                jQuery("#modal-window-choose-tar").hide();
            }
            var div6 = jQuery("#change_info_win");
            if (!div6.is(e.target)
                && div6.has(e.target).length === 0) {
                jQuery(".btn-close").hide();
                jQuery("#change_info").hide();
                jQuery("#change_info_win").hide();
                jQuery("input[name='data_change']").val(0);
            }
        });
        // -----------------------------------------------------------------------------------------
        // листание календаря
        /* g_calendar */
        var month_old = 0;
        var year_old = 0;
        jQuery("#calendar_container").on("click", "#g_button-next", function () {
            var month = <?php echo $month; ?>;
            var year = <?php echo $year; ?>;
            var type = 'g';
            if (month_old != 0) {
                month = month_old;
                year = year_old;
            }
            if (month == 12) {
                month = 1;
                year++;
            } else {
                month++;
            }
            month_old = month;
            year_old = year;
            update_calendar(month, year,"#g_calendar");
        });
        jQuery("#calendar_container").on("click", "#g_button-prev", function () {
            var month = <?php echo $month; ?>;
            var year = <?php echo $year; ?>;
            var type = 'g';
            if (month_old != 0) {
                month = month_old;
                year = year_old;
            }
            if (month == 1) {
                month = 12;
                year--;
            } else {
                month--;
            }
            month_old = month;
            year_old = year;
            update_calendar(month, year,"#g_calendar");
        });
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
            update_calendar(month1, year1,"#calendar1");
            update_calendar(month2, year2,"#calendar2");
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
            update_calendar(month1, year1,"#calendar1");
            update_calendar(month2, year2,"#calendar2");
        });
        function update_calendar(month, year,type) {
            var flag = (type == "#g_calendar" ) ? 3 : 2;
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=UpdateCalendarTar",
                data: {
                    id: <?php echo $userId; ?>,
                    id_dealer: <?php if ($user->dealer_type == 1 && $user->dealer_mounters == 1) { echo 1; } else { echo $user->dealer_id; } ?>,
                    flag: flag,
                    month: month,
                    year: year,
                },
                success: function (msg) {
                    jQuery(type).empty();
                    jQuery(type).append(msg);
                    Today(day, NowMonth, NowYear);
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

        // функция подсвета сегоднешней даты
        var Today = function (day, month, year) {
            month++;
            jQuery("#current-monthD"+day+"DM"+month+"MY"+year+"YI"+<?php echo $userId; ?>+"IC0C").addClass("today");
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
            process.handler = this;
            return process;
        };
        //------------------------------------------

        jQuery(document).ready(function () {
            var client_id = "<?php echo $this->item->id_client;?>";

            create_calculation(<?php echo $this->item->id; ?>);
            
            window.time = undefined;
            window.gauger = undefined;

            $("#modal_window_container #ok").click(function() {
                 click_ok(this); 
            });

            if (document.getElementById('comments'))
            {
                show_comments();
            }

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

            // открытие модального окна с календаря и получение даты и вывода свободных замерщиков
            jQuery("#calendar_container").on("click", ".current-month, .not-full-day, .change", function() {
                window.idDay = jQuery(this).attr("id");
                reg1 = "D(.*)D";
                reg2 = "M(.*)M";
                reg3 = "Y(.*)Y";
                if (idDay.match(reg1)[1].length == 1) {
                    d = "0"+idDay.match(reg1)[1];
                } else {
                    d = idDay.match(reg1)[1];
                }
                if (idDay.match(reg2)[1].length == 1) {
                    m = "0"+idDay.match(reg2)[1];
                } else {
                    m = idDay.match(reg2)[1];
                }
                window.date = idDay.match(reg3)[1]+"-"+m+"-"+d;
                jQuery("#modal_window_g_container").show();
                jQuery("#modal_window_g_choose").show("slow");
                jQuery("#close-tar").show();
                jQuery.ajax({
                    type: 'POST',
                    url: "/index.php?option=com_gm_ceiling&task=calculations.GetBusyGauger",
                    data: {
                        date: date,
                        dealer: <?php if ($user->dealer_id == 1 && in_array("14", $user->groups)) { echo $userId; } else { echo $user->dealer_id; } ?>,
                    },
                    success: function(data) {
                        Array.prototype.diff = function(a) {
                            return this.filter(function(i) {return a.indexOf(i) < 0;});
                        };
                        AllGauger = <?php echo json_encode($AllGauger); ?>;
                        data = JSON.parse(data); // замеры
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
                        jQuery("#g_date-modal").html("<strong>Выбранный день: "+d+"."+m+"."+idDay.match(reg3)[1]+"</strong>");
                    }
                });
                //если было выбрано время, то выдать его
                if (time != undefined) {
                    setTimeout(function() { 
                        var times = jQuery("input[name='choose_time_gauger']");
                        times.each(function(element) {
                            if (time == jQuery(this).val() && gauger == jQuery(this).closest('tr').find("input[name='gauger']").val()) {
                                jQuery(this).prop("checked", true);
                            }
                        });
                    }, 200);
                }
            });

            // получение значений замерщиков
            jQuery("#projects_gaugers").on("change", "input:radio[name='choose_time_gauger']", function() {
                var times = jQuery("input[name='choose_time_gauger']");
                time = "";
                gauger = "";
                times.each(function(element) {
                    if (jQuery(this).prop("checked") == true) {
                        time = jQuery(this).val();
                        gauger = jQuery(this).closest('tr').find("input[name='gauger']").val();
                    }
                });
                jQuery("#new_project_calculation_daypart").val(time);
                jQuery("#project_new_calc_date").val(date);
                jQuery("#project_gauger").val(gauger);
                if (jQuery(".change").length == 0) {
                    jQuery("#"+idDay).attr("class", "change");
                } else {
                    jQuery(".change").attr("class", "current-month");
                    jQuery("#"+idDay).attr("class", "change");
                }
                jQuery("#close-tar").hide();
                jQuery("#modal_window_g_container").hide();
                jQuery("#modal_window_g_choose").hide();
                jQuery(".btn-close").show();
                jQuery("#change_info").show();
                jQuery("#change_info_win").show();
            });
            jQuery("#projects_gaugers").on("click", "td", function(){
                var times = jQuery(this).closest('tr').find("input:radio[name='choose_time_gauger']");
                times.prop("checked",true);
                times.change();
            });
            //---------------------------------------------------------------

            // открытие модального окна с календаря и получение даты и вывода свободных монтажников
            jQuery("#calendar1, #calendar2").on("click", ".current-month, .not-full-day, .change", function() {
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
                        dealer: <?php if ($user->dealer_type == 1 && $user->dealer_mounters == 1) { echo 1; } else { echo $user->dealer_id; } ?>,
                    },
                    success: function(data) {
                        window.DataOfProject = JSON.parse(data);
                        // вывод бригад
                        Array.prototype.diff = function(a) {
                            return this.filter(function(i) {return a.indexOf(i) < 0;});
                        };
                        Allbrigades = <?php echo json_encode($Allbrigades); ?>;
                        data = JSON.parse(data); // занятые
                        AllbrigadesID = [];
                        Array.from(Allbrigades).forEach(function(elem) {
                            AllbrigadesID.push(elem.id);
                        });
                        BrigadesCountTime = [];
                        Array.from(AllbrigadesID).forEach(function(element) {
                            Array.from(data).forEach(function(elem) {
                                if (elem.project_mounter == element) {
                                    datefind = elem.project_mounting_date;
                                    if (datefind.substr(0, 10) == date) {
                                        if (BrigadesCountTime[element] == undefined) {
                                            BrigadesCountTime[element] = 1;
                                        } else {
                                            BrigadesCountTime[element] += 1;
                                        }
                                    }
                                }
                            });
                        });
                        BusyBrigades = [];
                        Array.from(AllbrigadesID).forEach(function(elem) {
                            if (BusyBrigades[elem] != undefined && BusyBrigades[elem] == 12) {
                                BusyBrigades.push(BusyBrigades[elem]);
                            }
                        });
                        FreeBrigades = AllbrigadesID.diff(BusyBrigades);
                        var select_mounter;
                        Array.from(Allbrigades).forEach(function(elem) {
                            FreeBrigades.forEach(element => {
                                if (elem.id == element) {
                                    select_mounter += '<option value="'+elem.id+'">'+elem.name+'</option>';
                                }
                            });
                        });
                        jQuery("#mounters").empty();
                        jQuery("#mounters").append(select_mounter);
                        jQuery("#date-modal").html("<strong>Выбранный день: "+d+"."+m+"."+idDay.match(reg3)[1]+"</strong>");
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
                        var table_projects = '<p style="margin-top: 1em; margin-bottom: 0;"><strong>Монтажи бригады:</strong></p><table id="projects_brigade">';
                        table_projects += '<tr class="caption"><td>Время</td><td>Адрес</td><td>Периметр</td></tr>';
                        Array.from(data).forEach(function(element) {
                            if (element.project_mounter == selectedBrigade) {
                                if (element.project_mounting_day_off != "") {
                                    table_projects += '<tr><td>'+element.project_mounting_date.substr(11, 5)+' - '+element.project_mounting_day_off.substr(11, 5)+'</td><td colspan="2">'+element.project_info+'</td></tr>';
                                } else {
                                    table_projects += '<tr><td>'+element.project_mounting_date.substr(11, 5)+'</td><td>'+element.project_info+'</td><td>'+element.n5+'</td></tr>';
                                }                        }
                        });
                        table_projects += "</table>";
                        jQuery("#projects_brigade_container").empty();
                        jQuery("#projects_brigade_container").append(table_projects);
                        // вывод времени бригады
                        window.AllTime = ["09:00:00", "10:00:00", "11:00:00", "12:00:00", "13:00:00", '14:00:00', "15:00:00", "16:00:00", "17:00:00", "18:00:00", "19:00:00", "20:00:00"];
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
                if (date == datesession.substr(0, 10)) {
                    var mountersession = jQuery("#project_mounter").val();
                    setTimeout(function() {
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
            // -------------------------------------------

            // получение значений из селектов
            jQuery("#save-choise-tar").click(function() {
                var mounter = jQuery("#mounters").val();
                var time = jQuery("#hours").val();
                var datetime = date+" "+time;
                jQuery("#project_mounter").val(mounter);
                jQuery("#jform_project_mounting_date").val(datetime);
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

            //если сессия есть, то выдать дату, которая записана в сессии монтажникам
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
            //-----------------------------------------------------------

            // подсвет сегоднешней даты
            window.today = new Date();
            window.NowYear = today.getFullYear();
            window.NowMonth = today.getMonth();
            window.day = today.getDate();
            Today(day, NowMonth, NowYear);
            //------------------------------------------

            // модальное окно 1, 2, 3
            <?php if ($user->dealer_type == 2) { ?>
            /*  jQuery("#modal-window-container-tar").show();
                jQuery("#modal-window-1-tar").show("slow");
            jQuery("#modal-ok-tar").attr("disabled", "disabled"); */
            <?php } ?>
            jQuery("#name-tar, #phone-tar").blur(function () {
                // УСЛОВИЕ НЕ РАБОТАЕТ. ВСЕГДА СРАБАТЫВАЕТ ИФ, И НИКОГДА ЭЛСЕ
                if (jQuery("#name-tar").val() != undefined || jQuery("#name-tar").val() != "" && jQuery("#phone-tar").val() != undefined || jQuery("#phone-tar").val() != "") {
                    jQuery("#modal-ok-tar").attr("disabled", false);
                } else {
                    jQuery("#modal-ok-tar").attr("disabled", "disabled");
                }
            });
            jQuery("#modal-ok-tar").click( function () {
                jQuery("#modal-window-container2-tar").show();
                jQuery("#modal-window-1-tar").hide();
                jQuery("#modal-window-container-tar").hide();
                jQuery("#modal-window-2-tar").show();
                jQuery("#close-tar").show();

            });
            jQuery("#preview-tar").click( function () {
                jQuery("#modal-window-container2-tar").hide();
                jQuery("#modal-window-container3-tar").show();
                jQuery("#modal-window-2-tar").hide();
                jQuery("#modal-window-3-tar").show();

            });
            // ----------------------------------------------

            jQuery("#jform_project_new_calc_date").mask("99.99.9999");
            jQuery("#jform_client_contacts").mask("+7 (999) 999-99-99");
            jQuery("#jform_project_new_calc_date").on("keyup", function () {
                update_times("#jform_project_new_calc_date", "#jform_new_project_calculation_daypart");
            });

            jQuery("input[name^='smeta']").change(function () {
                let old_self_comp = jQuery("#calcs_self_components_total span.sum").data('oldval');
                let self_component = jQuery("#calcs_self_components_total span.sum").text();
                let calcs_total = jQuery("#calcs_total_border").text();
                if(jQuery(this).prop("checked") == true){
                    jQuery("input[name='smeta']").val(1);
                    jQuery("#calcs_self_components_total span.sum").text(0);
                    jQuery("#calcs_total_border").text(calcs_total - self_component);
                }
                else{
                    jQuery("input[name='smeta']").val(0);
              
                    jQuery("#calcs_self_components_total span.sum").text(old_self_comp);
                    jQuery("#calcs_total_border").text(parseInt(calcs_total) + parseInt(old_self_comp));
                } 
            });

            
            jQuery("#client_order").click(function () {
                jQuery("input[name='project_verdict']").val(1);
                jQuery("#project_sum").val(<?php echo $project_total_discount?>);
            });
            jQuery("#save_exit").click(function () {
                jQuery("input[name='project_status']").val(4);
                jQuery("input[name='project_verdict']").val(1);
            });
            jQuery("#save").mousedown(function () {
                if(jQuery("input[name='project_mounter']").val() === "")
                {
                    jQuery(this).attr("type", "button");
                    noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Выберите монтажную бригаду!"
                    });

                }
                else{
                    jQuery("input[name='project_status']").val(5);
                    jQuery("input[name='project_verdict']").val(1);
                    jQuery(this).attr("type", "submit");
                }
                    
                
            });
            $tmp_accept = 0; $tmp_refuse = 0;

            jQuery("#accept_project").click(function () {
                jQuery("input[name='project_verdict']").val(1);
                if($tmp_accept == 0) {
                    jQuery("#mounter_wraper").show();
                    jQuery(".contract").show();
                    jQuery("#title").show();
                    jQuery(".calendar_wrapper").show();
                    jQuery(".buttons_wrapper").show();
                    jQuery(".project_activation").hide();
                    jQuery("#refuse").hide();
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

            jQuery("#choose_mounter").click(function () {
                jQuery.ajax({
                    type: 'POST',
                    url: "http://mounters.gm-vrn.ru/PHP_Controller/script_controller_choose_mounter_dealer.php",
                    data: {
                        do: 'from_calc',
                        dealer_id: <?php echo $userId; ?>,
                        phone: <?php echo $userName; ?>
                    },
                    dataType: 'text',
                    async: true,
                    success: function (data) {
                        location.href = "http://" + data;
                    },
                    error: function (data) {
                        var n = noty({
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка отправки"
                        });
                    }
                });
            });

            /*jQuery("#delete").submit(function () {
                jQuery("input[name='data_delete']").val(1);
                confirm("Bы действительно хотите удалить это?"));
            });*/
            
            jQuery("#change_data").click(function () {
                jQuery(".btn-close").show();
                jQuery("#change_info").show();
                jQuery("#change_info_win").show();
            });

            
            if (precalculation == 1) {
                jQuery("input[name='data_change']").val(1);
                jQuery("#change_data").trigger('click');
                jQuery("#accept_project").trigger('click');
            }

            jQuery("#save_email").click(function(){
                jQuery("#activate_by_email").val(1);
                jQuery("#close").show();
                jQuery("#modal_window_activate").show();
                jQuery("#modal_window_by_email").show();
            });

            jQuery("#accept_changes").click(function () {
                jQuery("input[name='data_change']").val(1);
                jQuery(".btn-close").hide();
                jQuery("#change_info").hide();
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

            jQuery("#update_discount").click(function () {
                jQuery("input[name='isDiscountChange']").val(1);
            });


           

            
            jQuery("[name = click_transport]").click(function () {
                calculate_transport();
            });

            if (jQuery("input[name='transport']:checked").val() == '2') {
                    jQuery("#transport_dist").show();
            }
            if (jQuery("input[name='transport']:checked").val() == '1') {
                    jQuery("#transport_dist_col").show();
            }
        
        });

        var flag = 0;
        jQuery("#sh_ceilings").click(function () {
            if (flag) {
                jQuery(".section_ceilings").hide();
                flag = 0;
            }
            else {
                jQuery(".section_ceilings").show();
                flag = 1;
            }
        });

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

        jQuery("#send_all").click(function () {
            jQuery(".email-all").toggle();
        });
        jQuery("[name = 'include_calculation[]']").change(function(){
            let canv_data = (self_data[jQuery(this).val()].canv_data).toFixed(0);
            let comp_data = (self_data[jQuery(this).val()].comp_data).toFixed(0);
            let mount_data = (self_data[jQuery(this).val()].mount_data).toFixed(0);
            let calc_sum = (self_data[jQuery(this).val()].sum).toFixed(0);
            let calc_sum_discount = (self_data[jQuery(this).val()].sum_discount).toFixed(0);
            let n4 = self_data[jQuery(this).val()].square;
            let n5 = self_data[jQuery(this).val()].perimeter;
            let old_canv = jQuery("#calcs_self_canvases_total span.sum").text();
            let old_comp = jQuery("#calcs_self_components_total span.sum").text();
            let old_mount = jQuery("#calcs_self_mount_total span.sum" ).text();
            let old_all = jQuery("#calcs_total_border").text();
            let old_total = jQuery("#project_total span.sum").text();
            let old_total_discount = jQuery("#project_total_discount span.sum").text();
            let old_n4 = jQuery("#total_square span.sum").text();
            let old_n5 = jQuery("#total_perimeter span.sum").text();
            if(jQuery(this).prop("checked") == true){
               jQuery("#calcs_self_canvases_total span.sum").text(parseInt(old_canv) + parseInt(canv_data));
               if(jQuery("input[name='smeta']").val()!=1){
                   jQuery("#calcs_self_components_total span.sum").text(parseInt(old_comp) + parseInt(comp_data));
               }
               jQuery("#calcs_self_mount_total span.sum").text(parseInt(old_mount) + parseInt(mount_data));
               jQuery("#calcs_total_border").text(parseInt(old_all) + parseInt(canv_data) +  parseInt(comp_data) + parseInt(mount_data));
               jQuery("#project_total span.sum").text(parseInt(old_total)+ parseInt(calc_sum));
               jQuery("#project_total_discount span.sum").text(parseInt(old_total_discount)+ parseInt(calc_sum_discount));
               jQuery("#total_square span.sum").text(parseFloat(old_n4) + parseFloat(n4));
               jQuery("#total_perimeter span.sum").text(parseFloat(old_n5) + parseFloat(n5));
              
            }
            else{
                jQuery("#calcs_self_canvases_total span.sum").text(old_canv-canv_data);
                if(jQuery("input[name='smeta']").val()!=1){
                    jQuery("#calcs_self_components_total span.sum").text(old_comp-comp_data);
                }
                jQuery("#calcs_self_mount_total span.sum").text(old_mount-mount_data);
                jQuery("#calcs_total_border").text(old_all - canv_data - comp_data - mount_data);
                jQuery("#project_total span.sum").text(old_total - calc_sum);
                jQuery("#project_total_discount span.sum").text(old_total_discount - calc_sum_discount);
                jQuery("#total_square span.sum").text(old_n4 - n4);
                jQuery("#total_perimeter span.sum").text(old_n5 - n5);
                let more_one = check_selected();
                if(!more_one){
                    jQuery("#project_total_discount span.sum").text(jQuery("#transport_sum span.sum").text());
                }
                
            }
            
            jQuery("#calcs_self_components_total span.sum").data('oldval',jQuery("#calcs_self_components_total span.sum").text());
            check_min_sum(jQuery("#calcs_self_canvases_total span.sum").text());
        });
      
        function check_min_sum(canv_sum){
            let min_sum = 0;
            if(canv_sum == 0) {
                if(min_components_sum>0){
                    min_sum = min_components_sum;
                }
            }
            else{
                if(min_project_sum>0){
                    min_sum = min_project_sum;
                }
            }            
            let project_total = jQuery("#project_total span.sum").text();
            if(jQuery("#project_total_discount span.dop").length == 0){
                jQuery("#project_total_discount").append('<span class = \"dop\" style = \"font-size: 9px\";></span>');
            }
            if(project_total < min_sum){
                jQuery("#project_total_discount span.dop").html(` * минимальная сумма заказа ${min_sum} р.`);
                jQuery("#project_total_discount span.sum").text(min_sum);

            }
            else{
                jQuery("#project_total_discount span.dop").html(" ");
            }
            jQuery("#project_sum").val(jQuery("#project_total_discount span.sum").text());
        }
        function check_selected(){
            let result = false;
            jQuery("[name = 'include_calculation[]']").each(function(){
                if(jQuery(this).prop("checked") == true ){
                    result = true;
                }
            });
            return result;
        }
        jQuery("#send_all_to_email1").click(function () {

            var email = jQuery("#all-email1").val();
            var client_id = jQuery("#client_id").val();
            var dop_file = jQuery("#dop_file").serialize();
            var testfilename = <?php echo (empty($json))?"null":$json;?>;
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
                data: formData, 
                type: "POST",
                dataType: 'json',
                processData: false,
                contentType: false,
                cache: false,
                success: function (data) {
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Сметы отправлены!"
                    });

                },
                error: function (data) {
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

        jQuery("#send_all_to_email2").click(function () {
            var email = jQuery("#all-email2").val();
            var testfilename = <?php echo (empty($json1))?"null":$json1;?>;
            var filenames = [];
            for (var i = 0; i < testfilename.length; i++) {
                var id = testfilename[i].id;
                var el = jQuery("#section_mount_" + id);
                if (el.attr("vis") != "hide") filenames.push(testfilename[i]);
            }
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
                data: formData, /* {
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
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Наряды на монтаж отправлены!"
                    });

                },
                error: function (data) {
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
        jQuery("#show").click(function(){
            //перегенерить смету по выбранным
            regenerate_common_estimate();
        });
        function get_selected_calcs(){
            let ids = [];
            jQuery.each(jQuery("[name = 'include_calculation[]']:checked"),function(){
                ids.push(jQuery(this).val());
            });
            return ids;
        }
        function regenerate_common_estimate(){
            let project_id = <?php echo $this->item->id;?>;
            let calc_ids = get_selected_calcs();
             jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=regenerate_common_estimate",
                data:{
                    proj_id: project_id,
                    calc_ids: calc_ids
                },
                type: "POST",
                dataType: 'json',
                async: false,
                success: function (data) {
                },
                error: function (data) {
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка при генерации общей сметы по выбранным потолкам"
                    });
                }
            }); 
        }
        jQuery("#send_all_to_email3").click(function () {
            regenerate_common_estimate();
            var email = jQuery("#all-email3").val();
            var id  = jQuery("#project_id").val();
            var client_id = jQuery("#client_id").val();
            var testfilename = <?php echo (empty($json2))?"null":$json2;?>;
            var filenames = [];
            var formData = new FormData();
            jQuery.each(jQuery('#dopfile2')[0].files, function (i, file) {
                formData.append('dopfile2', file)
            });
            formData.append('filenames', JSON.stringify(filenames));
            formData.append('email', email);
            formData.append('id', id);
            formData.append('type', 2);
            formData.append('client_id', client_id);
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=send_estimate",
                data: formData,
                type: "POST",
                dataType: 'json',
                processData: false,
                contentType: false,
                cache: false,
                async:false,
                success: function (data) {
                    var n = noty({
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Общая смета отправлена!"
                    });

                },
                error: function (data) {
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

        jQuery("#jform_project_new_calc_date").attr("onchange", "update_times(\"#jform_project_new_calc_date\",\"#jform_new_project_calculation_daypart\")");

       
        function update_times(fieldName, fieldName2) {
            var date = jQuery(fieldName).val();
            if (isDate(date)) {
                jQuery.getJSON("/index.php?option=com_gm_ceiling&task=get_calculator_times&date=" + date, function (data) {
                    var items = [];
                    jQuery.each(data, function (key, val) {
                        items.push("<option value='" + key + "'>" + val + "</option>");
                    });

                    jQuery(fieldName2).html(items.join(""));
                    jQuery(fieldName2).prop("disabled", false);
                });
            } else {
                jQuery(fieldName2).html("<option value='0' selected=''>- Выберите время замера -</option>");
                jQuery(fieldName2).prop("disabled", true);
            }
        }

        function isDate(txtDate) {
            var currVal = txtDate;
            if (currVal == '')
                return false;
            //Declare Regex
            var rxDatePattern = /^(\d{1,2})(\/|.)(\d{1,2})(\/|.)(\d{4})$/;
            var dtArray = currVal.match(rxDatePattern); // is format OK?
            if (dtArray == null)
                return false;

            //Checks for mm/dd/yyyy format.
            dtMonth = dtArray[3];
            dtDay = dtArray[1];
            dtYear = dtArray[5];

            if (dtMonth < 1 || dtMonth > 12)
                return false;
            else if (dtDay < 1 || dtDay > 31)
                return false;
            else if ((dtMonth == 4 || dtMonth == 6 || dtMonth == 9 || dtMonth == 11) && dtDay == 31)
                return false;
            else if (dtMonth == 2) {
                var isleap = (dtYear % 4 == 0 && (dtYear % 100 != 0 || dtYear % 400 == 0));
                if (dtDay > 29 || (dtDay == 29 && !isleap))
                    return false;
            }
            return true;
        }

        jQuery("input[name='transport']").click(function () {
            var transport = jQuery("input[name='transport']:checked").val();
            if (transport == '2') {
                jQuery("#transport_dist").show();
                jQuery("#transport_dist_col").hide();
                jQuery("#distance").val('');
                jQuery("#distance_col_1").val('');
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
            if(transport == 0){
                calculate_transport();
            }
        });
        function change_transport(sum){
            let old_transport = jQuery("#transport_sum span.sum").text();
            let new_transport = sum.client_sum;
            let new_self_transport = sum.mounter_sum;
            let old_self_transport = jQuery("#transport_sum span.sum").data('selfval');
            jQuery("#project_sum_transport").val(new_transport);
            jQuery("#transport_sum span.sum").text(new_transport);
            let old_self_mount = jQuery("#calcs_self_mount_total span.sum").text();
            let old_self_total = jQuery("#calcs_total_border").text();
            let old_total = jQuery("#project_total span.sum").text();
            let old_total_discount = jQuery("#project_total_discount span.sum").text();
            jQuery("#project_total span.sum").text(parseInt(old_total) - old_transport + parseInt(new_transport));
            jQuery("#project_total_discount span.sum").text(old_total_discount - old_transport + new_transport);
            jQuery("#calcs_self_mount_total span.sum").text(old_self_mount - old_self_transport + new_self_transport);
            jQuery("#calcs_total_border").text(old_self_total - old_self_transport + new_self_transport);
            jQuery("#transport_sum span.sum").data('selfval',new_self_transport);
            jQuery("#project_sum").val(jQuery("#project_total_discount span.sum").text());
        }
       
        function update_transport(id,transport,distance,distance_col){
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=project.update_transport",
                data:{
                    id : id,
                    transport : transport,
                    distance : distance,
                    distance_col : distance_col
                },
                success: function(data){
                    change_transport(data);
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

        function calculate_transport(){
            var id = <?php echo $this->item->id; ?>;
            var transport = jQuery("input[name='transport']:checked").val();
            var distance = jQuery("#distance").val();
            var distance_col = jQuery("#distance_col").val();
            var distance_col_1 = jQuery("#distance_col_1").val();
            console.log(distance,distance_col,distance_col_1);
            switch(transport){
                case "0" :
                    update_transport(id,0,0,0);
                    break;
                case "1":
                   
                    update_transport(id,transport,distance,distance_col_1);
                    break;
                case "2" :
                                       
                    update_transport(id,transport,distance,distance_col);
                    break;
            }
        }
       

        /**
        * @return {number}
        */
        function Float(x, y = 2) {
            return Math.round(parseFloat(""+x) * Math.pow(10,y)) / Math.pow(10,y);
        }

        var mountArray = {};

        jQuery("#spend-form input").on("keyup", function () {
            jQuery('#extra_spend_submit').fadeIn();
        });

        jQuery("#penalty-form input").on("keyup", function () {
            jQuery('#penalty_submit').fadeIn();
        });

        jQuery("#bonus-form input").on("keyup", function () {
            jQuery('#bonus_submit').fadeIn();
        });

        var calendar_toggle = 0,
            month = <?php echo date("n"); ?>,
            year = <?php echo date("Y"); ?>;

        function checkForm(e) {
            if (jQuery("input[name='project_mounter']").val() != "")
                e.submit();
            else noty({
                theme: 'relax',
                layout: 'center',
                maxVisible: 5,
                type: "error",
                text: "Выберете дату и монтажную бригаду!"
            });
        }
        ymaps.ready(init);

        var Data = {};
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
        }
    
    </script>

<?php
    else:
        echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
    endif;
?>
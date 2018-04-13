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
    $userId = $user->get('id');

    $canvas_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');

    $canEdit = JFactory::getUser()->authorise('core.edit', 'com_gm_ceiling');

    if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_gm_ceiling')) {
        $canEdit = JFactory::getUser()->id == $this->item->created_by;
    }
    $transport = Gm_ceilingHelpersGm_ceiling::calculate_transport($this->item->id);
    $sum_transport = $transport['client_sum'];
    $sum_transport_1 = $transport['mounter_sum'];
    //генерация общих смет
    Gm_ceilingHelpersGm_ceiling::create_client_common_estimate($this->item->id);
    Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($this->item->id);
    $project_total = 0;
    $project_total_discount = 0;
    $total_square = 0;
    $total_perimeter = 0;
    $model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
    $calculations = $model->new_getProjectItems($this->item->id);

    foreach ($calculations as $calculation) {

        $calculation->dealer_canvases_sum = double_margin($calculation->canvases_sum, 0/*$this->item->gm_canvases_margin*/, $this->item->dealer_canvases_margin);
        $calculation->dealer_components_sum = double_margin($calculation->components_sum, 0 /*$this->item->gm_components_margin*/, $this->item->dealer_components_margin);
        $calculation->dealer_gm_mounting_sum = double_margin($calculation->mounting_sum, 0 /*$this->item->gm_mounting_margin*/, $this->item->dealer_mounting_margin);

        $calculation->dealer_canvases_sum_1 = margin($calculation->canvases_sum, 0/*$this->item->gm_canvases_margin*/);
        $calculation->dealer_components_sum_1 = margin($calculation->components_sum, 0/* $this->item->gm_components_margin*/);
        $calculation->dealer_gm_mounting_sum_1 = margin($calculation->mounting_sum, 0/* $this->item->gm_mounting_margin*/);

        $calculation->calculation_total = $calculation->dealer_canvases_sum + $calculation->dealer_components_sum + $calculation->dealer_gm_mounting_sum;
        $calculation->calculation_total_discount = $calculation->calculation_total * ((100 - $calculation->discount) / 100);
        $project_total += $calculation->calculation_total;
        $project_total_discount += $calculation->calculation_total_discount;

        if ($user->dealer_type != 2) {
            $dealer_canvases_sum_1 = margin($calculation->canvases_sum, 0/*$this->item->gm_canvases_margin*/);
            $dealer_components_sum_1 = margin($calculation->components_sum, 0/*$this->item->gm_components_margin*/);
            $dealer_gm_mounting_sum_1 = margin($calculation->mounting_sum, 0/*$this->item->gm_mounting_margin*/);
            $calculation_total_1 = $dealer_canvases_sum_1 + $dealer_components_sum_1;
            $dealer_gm_mounting_sum_11 += $dealer_gm_mounting_sum_1;
            $calculation_total_11 += $calculation_total_1;
            $project_total_1 = $calculation_total_1 + $dealer_gm_mounting_sum_1;
        }
        $project_total_11 += $project_total_1;

        $calculation_total = $calculation->calculation_total;
    }

    $mountModel = Gm_ceilingHelpersGm_ceiling::getModel('mount');
    $mount_transport = $mountModel->getDataAll($this->item->dealer_id);
    $min_project_sum = (empty($mount_transport->min_sum)) ? 0 : $mount_transport->min_sum;
    $min_components_sum = (empty($mount_transport->min_components_sum)) ? 0 : $mount_transport->min_components_sum;
    $project_total_discount_transport = $project_total_discount + $sum_transport;

    $del_flag = 0;
    $project_total = $project_total  + $sum_transport;
    $project_total_discount = $project_total_discount  + $sum_transport;

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
    $FlagCalendar = [2, $user->dealer_id];
    $calendar1 = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($userId, $month1, $year1, $FlagCalendar);
    $calendar2 = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($userId, $month2, $year2, $FlagCalendar);
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
    $AllMounters = $model->FindAllMounters($where);
    // ---------------------------------------------------------------------------------

    // календарь
    $FlagCalendar = [3, $user->dealer_id];
    $calendar = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($userId, $month1, $year1, $FlagCalendar);
    //----------------------------------------------------------------------------------

    // все замерщики
    $AllGauger = $model->FindAllGauger($user->dealer_id, 22);
    //----------------------------------------------------------------------------------

?>

<style>
    .calculation_sum {
        width: 100%;
        margin-bottom: 25px;
    }
    .calculation_sum td {
        padding: 0 5px;
    }
</style>

<link rel="stylesheet" href="/components/com_gm_ceiling/views/project/css/style.css" type="text/css" />
<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>

<?=parent::getButtonBack();?>

<h2 class="center">Просмотр проекта</h2>

<?php if ($this->item) : ?>
<?php $model = Gm_ceilingHelpersGm_ceiling::getModel('calculations'); ?>
<?php
    $calculations = $model->new_getProjectItems($this->item->id);
    $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
    $phones = $client_model->getItemsByClientId($this->item->id_client);
?>
<form id="form-client" action="/index.php?option=com_gm_ceiling&task=project.activate&type=gmcalculator&subtype=calendar" method="post" class="form-validate form-horizontal" enctype="multipart/form-data" >
    <div class="container">
        <div class="row">
            <div class="col-12 item_fields">
                <h4>Информация по проекту № <?php echo $this->item->id ?></h4>
                    <?php if ($this->type === "gmcalculator" && $this->subtype === "calendar") { ?>
                        <?php if ($this->item->project_verdict == 0) { ?>
                            <?php if ($user->dealer_type != 2) { ?>
                                <table>
                                    <tr>
                                        <td>
                                            <a class="btn btn-primary" id="change_data">
                                                <?php if ($this->item->client_id == 1) { echo "Заполнить данные о клиенте"; } else { echo "Изменить данные"; } ?>
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            <?php } ?>
                        <?php } ?>
                        <div class="project_activation" style="display: none;">
                            <input name="project_id" value="<?php echo $this->item->id; ?>" type="hidden">
                            <input name="client_id" id="client_id" value="<?php echo $this->item->id_client; ?>" type="hidden">
                            <input name="type" value="gmcalculator" type="hidden">
                            <input name="subtype" value="calendar" type="hidden">
                            <input id="project_verdict" name="project_verdict" value="0" type="hidden">
                            <input name="data_change" value="0" type="hidden">
                            <input name="data_delete" value="0" type="hidden">
                            <input id="mounting_date" name="mounting_date" type='hidden'>
                            <input id = "jform_project_mounting_date" name="jform_project_mounting_date" value="<?php echo $this->item->project_mounting_date; ?>" type='hidden'>
                            <input name="project_mounter" id = "project_mounter" value="<?php echo $this->item->project_mounter; ?>" type='hidden'>
                            <input id="project_sum" name="project_sum" value="<?php echo $project_total_discount ?>" type="hidden">
                            <input id="project_sum_transport" name="project_sum_transport" value="<?php echo $project_total_discount_transport ?>" type="hidden">
                            <input name="comments_id" id="comments_id" value="<?php if (isset($_SESSION['comments'])) echo $_SESSION['comments']; ?>" type="hidden">
                            <input id="jform_new_project_calculation_daypart" name="new_project_calculation_daypart" value="" type='hidden'> 
                            <input name = "project_new_calc_date" id = "jform_project_new_calc_date"  value="" type='hidden'>
                            <input id="jform_project_gauger" name="project_gauger" value="" type='hidden'>  
                        </div>
                        <?php if ($user->dealer_type != 2) { ?>
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <table class="table" >
                                        <tr>
                                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
                                            <td><a href="http://test1.gm-vrn.ru/index.php?option=com_gm_ceiling&view=clientcard&id=<?=$this->item->id_client;?>"><?php echo $this->item->client_id; ?></a></td>
                                            <td>
                                                <div class="FIO" style="display: none;">
                                                    <input class = "inputactive" name="new_client_name" id="jform_client_name" value="<?php echo $this->item->client_id; ?>" placeholder="Новое ФИО клиента" type="text">
                                                </div>
                                            </td>
                                        </tr>
                                        <?php 
                                            $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');  
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
                                        <tr>
                                            <th><?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?></th>
                                            <td><?foreach ($phones as $contact):?><a href="tel:+<?=$contact->phone;?>"><?=$contact->phone;?></a><?endforeach;?></td>
                                        </tr>
                                        <tr>
                                            <th>Почта</th>
                                            <td>
                                                <?php
                                                    $clients_dop_contacts_model = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
                                                    $contact_email = $clients_dop_contacts_model->getContact($this->item->id_client);
                                                    foreach ($contact_email AS $contact):?>
                                                        <a href="mailto:<?=$contact->contact;?>"><?=$contact->contact;?></a>
                                                <?endforeach;?>
                                            </td>
                                        </tr>
                                        <?php   
                                            $street = preg_split("/,.дом:.([\d\w\/\s]{1,4}),/", $this->item->project_info)[0];
                                            preg_match("/,.дом:.([\d\w\/\s]{1,4}),/", $this->item->project_info,$house);
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
                                        ?>
                                        <tr>
                                            <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?></th>
                                            <td><a target="_blank" href="https://yandex.ru/maps/?mode=search&text=<?=$this->item->project_info;?>"><?=$this->item->project_info;?></a></td>
                                            <td>
                                                <div class="Address" style="display: none; position:relative;">
                                                    <label id="jform_address_lbl" for="jform_address">
                                                        Адрес<span class="star">&nbsp;*</span>
                                                    </label>
                                                    <input name="new_address" class="inputactive" id="jform_address" value="<?=$street?>" placeholder="Улица" type="text">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="Address" style="display: none;">
                                            <td>Дом</td>
                                            <td>Корпус</td>
                                            <td>
                                                <input name="new_house" id="jform_house" value="<?php if (isset($_SESSION['house'])) {echo $_SESSION['house']; } else echo $house ?>"class="inputactive"  style="width: 50%; margin-bottom: 1em; float: left; margin: 0 5px 0 0;" placeholder="Дом" aria-required="true" type="text">
                                                <input name="new_bdq" id="jform_bdq"  value="<?php if (isset($_SESSION['bdq'])) {echo $_SESSION['bdq']; } else echo $bdq ?>" class="inputactive" style="width: calc(50% - 5px); margin-bottom: 1em;" placeholder="Корпус" aria-required="true" type="text">
                                            </td>
                                        </tr>
                                        <tr class="Address" style="display: none;">
                                            <td>Квартира</td>
                                            <td>Подъезд</td>
                                            <td>
                                                <input name="new_apartment" id="jform_apartment" value="<?php if (isset($_SESSION['apartment'])) {echo $_SESSION['apartment']; } else echo $apartment ?>" class="inputactive" style="width:50%;margin-bottom:1em;margin-right: 5px;float: left;" placeholder="Квартира"  aria-required="true" type="text">
                                                <input name="new_porch" id="jform_porch"  value="<?php if (isset($_SESSION['porch'])) {echo $_SESSION['porch']; } else echo $porch ?>" class="inputactive" style="width: calc(50% - 5px); margin-bottom: 1em;" placeholder="Подъезд"  aria-required="true" type="text">
                                            </td>
                                        </tr>
                                        <tr class="Address" style="display: none;">
                                            <td>Этаж</td>
                                            <td>Код домофона</td>
                                            <td>
                                                <input name="new_floor" id="jform_floor"  value="<?php if (isset($_SESSION['floor'])) {echo $_SESSION['floor']; } else echo $floor ?>" class="inputactive" style="width:50%; margin-bottom:1em;  margin: 0 5px  0 0; float: left;" placeholder="Этаж" aria-required="true" type="text">
                                                <input name="new_code" id="jform_code"  value="<?php echo $code;?>" class="inputactive" style="width: calc(50% - 5px); margin-bottom: 1em;" placeholder="Код" aria-required="true" type="text">
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
                                            <td>
                                                <div class="Date" style="display: none;">
                                                    <label id="jform_project_mounting_date-lbl" for="jform_project_new_calc_date">
                                                        Новая дата<span class="star">&nbsp;*</span>
                                                    </label>
                                                    <div id="calendar-container">
                                                        <div class="btn-small-l">
                                                            <button id="button-prev-gauger" class="button-prev-small" type="button" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i></button>
                                                        </div>
                                                        <?php echo $calendar; ?>
                                                        <div class="btn-small-r">
                                                            <button id="button-next-gauger" class="button-next-small" type="button" class="btn btn-primary"><i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Замерщик</th>
                                            <td>
                                                <?php if ($this->item->project_calculator == null) { ?>
                                                    - 
                                                <?php } else { ?>
                                                    <?php echo JFactory::getUser($this->item->project_calculator)->name; ?>
                                                <?php } ?>
                                            </td>
                                            <td class="Gauger" style="display: none;">
                                                <p>Новый замерщик:</p>
                                                <p id="new_gauger"></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Дилер</th>
                                            <td><?php
                                                    $dealer = $client_model->getDealer($this->item->id_client);
                                                    echo $dealer;
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan=3 style="text-align: center;">
                                                <button type="submit" id="accept_changes" class="btn btn btn-success" style="display: none;">Сохранить изменения</button>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                              <!--   <?php //if ($user->dealer_type == 0) { ?> -->
                                    <div  class="col-12 col-md-6">
                                        <div class="comment" >
                                            <label>История клиента:</label>
                                            <textarea id="comments" class="input-comment" rows=11 readonly></textarea>
                                            <table>
                                                <tr>
                                                    <td>
                                                        <label>Добавить комментарий:</label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width = 100%>
                                                        <textarea  class = "inputactive" id="new_comment" placeholder="Введите новое примечание"></textarea>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-primary" type="button" id="add_comment"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                <!-- <?php //} ?> -->
                            </div>
                        <?php } ?>
                    <?php } ?>
                
            </div>
        </div>
    </div>
    <!-- скидка -->
        <div class="center-left">
            <a class="btn btn-primary" id="change_discount">Изменить величину скидки</a>
        </div>
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
    <!-- конец скидки -->
    
    <?php include_once('components/com_gm_ceiling/views/project/common_table.php'); ?>

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
    <div id="modal-window-container2-tar">
        <button id="close2-tar" type="button"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="modal-window-2-tar">
            <p id="date-modal"></p>
            <p><strong>Выберите время замера:</strong></p>
            <p>
                <table id="projects_gaugers"></table>
            </p>
        </div>
    </div>
    <input name="idCalcDelete" id="idCalcDelete" value="<?=$calculation->id;?>" type="hidden">
    </div>
    <div id="modal_window_container" class = "modal_window_container">
        <button type="button" id="close" class = "close_btn"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div id="modal_window_del" class = "modal_window">
            <h6 style = "margin-top:10px">Вы действительно хотите удалить?</h6>
            <p>
                <button type="button" id="ok" class="btn btn-primary">Да</button>
                <button type="button" id="cancel" onclick="click_cancel();" class="btn btn-primary">Отмена</button>
            </p>
        </div>
    </div>
</form>

<script type="text/javascript" src="/components/com_gm_ceiling/create_calculation.js"></script>

<script type="text/javascript">

    var $ = jQuery;
    var min_project_sum = <?php echo  $min_project_sum;?>;
    var min_components_sum = <?php echo $min_components_sum;?>;
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

    // листание календаря монтажников
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
                var datesession = jQuery("#jform_project_mounting_date").val();  
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
                var datesession = jQuery("#jform_project_mounting_date").val();  
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

    // листание календаря замерщиков
    month_old = 0;
    year_old = 0;
    jQuery("#calendar-container").on("click", "#button-next-gauger", function () {
        month = <?php echo $month1; ?>;
        year = <?php echo $year1; ?>;
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
        update_calendar_gauger(month, year);
    });
    jQuery("#calendar-container").on("click", "#button-prev-gauger", function () {
        month = <?php echo $month1; ?>;
        year = <?php echo $year1; ?>;
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
        update_calendar_gauger(month, year);
    });
    function update_calendar_gauger(month, year) {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=UpdateCalendarTar",
            data: {
                id: <?php echo $userId; ?>,
                id_dealer: <?php echo $user->dealer_id; ?>,
                flag: 3,
                month: month,
                year: year,
            },
            success: function (msg) {
                jQuery("#calendar-container").empty();
                msg += '<div class="btn-small-l"><button id="button-prev-gauger" class="button-prev-small" type="button" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i></button></div><div class="btn-small-r"><button id="button-next-gauger" class="button-next-small" type="button" class="btn btn-primary"><i class="fa fa-arrow-right" aria-hidden="true"></i></button></div>';
                jQuery("#calendar-container").append(msg);
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

    //скрыть модальное окно
    jQuery(document).mouseup(function (e) {
		var div1 = jQuery("#modal-window-choose-tar");
		if (!div1.is(e.target)
		    && div1.has(e.target).length === 0) {
			jQuery("#close-tar").hide();
			jQuery("#modal-window-container-tar").hide();
			jQuery("#modal-window-choose-tar").hide();
		}
        var div = jQuery("#modal_window_del"); // тут указываем ID элемента
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#close").hide();
            jQuery("#modal_window_container").hide();
            jQuery("#modal_window_del").hide();
        }
        var div2 = jQuery("#modal-window-2-tar");
		if (!div2.is(e.target)
		    && div2.has(e.target).length === 0) {
			jQuery("#close2-tar").hide();
			jQuery("#modal-window-container2-tar").hide();
			jQuery("#modal-window-2-tar").hide();
		}
    });
    //--------------------------------------------------

    // функция подсвета сегоднешней даты
    var Today = function (day, month, year) {
        month++;
        jQuery("#current-monthD"+day+"DM"+month+"MY"+year+"YI"+<?php echo $userId; ?>+"IC0C").addClass("today");
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

    jQuery(document).ready(function () {

        document.getElementById('add_calc').onclick = function()
        {
            create_calculation(<?php echo $this->item->id; ?>);
        };

        window.time_gauger = undefined;
        window.gauger_gauger = undefined;

        // открытие модального окна с календаря замерщиков
        jQuery("#calendar-container").on("click", ".current-month, .not-full-day, .change", function() {
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
            window.date_gauger = idDay.match(reg3)[1]+"-"+m+"-"+d;
            jQuery("#modal-window-container2-tar").show();
			jQuery("#modal-window-2-tar").show("slow");
            jQuery("#close2-tar").show();
            jQuery.ajax({
                type: 'POST',
                url: "/index.php?option=com_gm_ceiling&task=calculations.GetBusyGauger",
                data: {
                    date: date_gauger,
                    dealer: <?php echo $user->dealer_id; ?>,
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
                                    if (elementProject.project_calculator == gauger_gauger && elementProject.project_calculation_date.substr(11) == time_gauger) {
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
            //если Время было выбрано, то выдать его
            if (time_gauger != undefined) {
                setTimeout(function() { 
                    var times = jQuery("input[name='choose_time_gauger']");
                    times.each(function(element) {
                        if (time_gauger == jQuery(this).val() && gauger_gauger == jQuery(this).closest('tr').find("input[name='gauger']").val()) {
                            jQuery(this).prop("checked", true);
                        }
                    });
                }, 200);
            }
        });
        //------------------------------------

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
                    dealer: <?php echo $user->dealer_id; ?>,
                },
                success: function(data) {
                    window.DataOfProject = JSON.parse(data);
                    Array.prototype.diff = function(a) {
                        return this.filter(function(i) {return a.indexOf(i) < 0;});
                    };
                    // вывод бригад
                    Allbrigades = <?php echo json_encode($Allbrigades); ?>;
                    jQuery("#date-modal").html("<strong>Выбранный день: "+d+"."+m+"."+idDay.match(reg3)[1]+"</strong>");
                    window.AllTime = ["09:00:00", "10:00:00", "11:00:00", "12:00:00", "13:00:00", '14:00:00', "15:00:00", "16:00:00", "17:00:00", "18:00:00", "19:00:00", "20:00:00"];
                    if (Allbrigades.length != 0) {
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
                                }
                            }
                        });
                        table_projects += "</table>";
                        jQuery("#projects_brigade_container").empty();
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
                    } else {
                        jQuery("#mounters").empty();
                        jQuery("#mounters").append('<option value="'+<?php echo $userId; ?>+'">Вы</option>');
                        jQuery("#hours").empty();
                        var select_hours;
                        AllTime.forEach(element => {
                            select_hours += '<option value="'+element+'">'+element.substr(0, 5)+'</option>';
                        });
                        jQuery("#hours").append(select_hours);
                    }
                }
            });
            if (date == datesession.substr(0, 10)) {
                var timesession = jQuery("#jform_project_mounting_date").val().substr(11);
                var mountersession = jQuery("#project_mounter").val();
                setTimeout(function() {
                    // время
                    var timeall = document.getElementById('hours').options;
                    for (var i = 0; i < timeall.length; i++) {
                        if (time_mounter != undefined) {
                            if (timeall[i].value == time_mounter) {
                                document.getElementById('hours').disabled = false;
                                timeall[i].selected = true;
                            }
                        } else if (timesession != undefined) {
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

        // получение значений из селектов монтажников
        jQuery("#modal-window-container-tar").on("click", "#save-choise-tar", function() {
            var mounter = jQuery("#mounters").val();
            time_mounter = jQuery("#hours").val();
            var datetime = date+" "+time_mounter;
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

        // получение значений из селектов замерщиков
		jQuery("#projects_gaugers").on("change", "input:radio[name='choose_time_gauger']", function() {
            var times = jQuery("input[name='choose_time_gauger']");
            time_gauger = "";
            gauger_gauger = "";
            times.each(function(element) {
                if (jQuery(this).prop("checked") == true) {
                    time_gauger = jQuery(this).val();
                    gauger_gauger = jQuery(this).closest('tr').find("input[name='gauger']").val();
                }
            });
            jQuery("#jform_new_project_calculation_daypart").val(time_gauger);
            jQuery("#jform_project_new_calc_date").val(date_gauger);
            jQuery("#jform_project_gauger").val(gauger_gauger);
            if (jQuery(".change").length == 0) {
                jQuery("#"+idDay).addClass("change");
            } else {
                jQuery(".change").removeClass("change");
                jQuery("#"+idDay).addClass("change");
            }
            jQuery.ajax({
                type: 'POST',
                url: "/index.php?option=com_gm_ceiling&task=project.GetNameGauger",
                data: {
                    id: gauger_gauger,
                },
                dataType: "json",
                success: function (data) {
                    jQuery("#new_gauger").text(data.name);
                },
                error: function (data) {
                    console.log(data);
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка вывода нового замерщика"
                    });
                }
            });
            jQuery("#close2-tar").hide();
            jQuery("#modal-window-container2-tar").hide();
            jQuery("#modal-window-2-tar").hide();
        });
        jQuery("#projects_gaugers").on("click", "td", function(){
            var times = jQuery(this).closest('tr').find("input:radio[name='choose_time_gauger']");
            times.prop("checked",true);
            times.change();
        });
        //------------------------------------------

        // подсвет сегоднешней даты
        window.today = new Date();
        window.NowYear = today.getFullYear();
        window.NowMonth = today.getMonth();
        window.day = today.getDate();
        Today(day, NowMonth, NowYear);
        //------------------------------------------

        //если сессия есть, то выдать дату, которая записана в сессии монтажникам
        var datesession = jQuery("#jform_project_mounting_date").val();  
        if (datesession != undefined) {
            jQuery("#current-monthD"+datesession.substr(8, 2)+"DM"+datesession.substr(5, 2)+"MY"+datesession.substr(0, 4)+"YI"+<?php echo $userId; ?>+"IC1C").addClass("change");
        }
        //-----------------------------------------------------------

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
        // ----------------------------------------

        $("#modal_window_container #ok").click(function() { click_ok(this); });
        show_comments();
        //trans();

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

        jQuery("#save").click(function(){
            if(jQuery("#project_mounter").val()==0 && jQuery("#jform_project_mounting_date").val()==0 ){
                jQuery("#new_call").show();
            }
            else {
                jQuery("#form-client").submit();
            }
        });

        jQuery("#ok_btn").click(function(){
            if(jQuery("#calldate_without_mounter").val()&&jQuery("#calltime_without_mounter").val()){
                jQuery("#form-client").submit();
            }
            else{
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Введите дату и время звонка!"
                });
            }
        });

        jQuery("#jform_client_contacts").mask("+7 (999) 999-99-99");

        jQuery("#jform_project_new_calc_date").on("keyup", function () {
            jQuery("#jform_new_project_calculation_daypart").prop("disabled",false);
        });

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

        jQuery("input[name^='smeta']").click(function () {
            if(jQuery("input[name^='smeta']").attr("checked") == 'checked')
                jQuery("input[name='smeta']").val(1);
            else jQuery("input[name='smeta']").val(0);
        });

        jQuery("#client_order").click(function () {
            jQuery("input[name='project_verdict']").val(1);
            jQuery("#project_sum").val( jQuery("#project_total_discount").val());
            //jQuery("#project_sum").val(<?php //echo $project_total_discount?>);
        });

        $tmp_accept = 0; $tmp_refuse = 0;
        jQuery("#accept_project").click(function () {
            jQuery("input[name='project_verdict']").val(1);
            
            if($tmp_accept == 0) {
                
                jQuery("#mounter_wraper").show();
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

        jQuery("#accept_changes").click(function () {
            jQuery("input[name='data_change']").val(1);
        });
        jQuery("#change_data").click(function () {
            jQuery(".FIO").toggle();
            jQuery(".Contacts").toggle();
            jQuery(".Address").toggle();
            jQuery(".Date").toggle();
            jQuery(".Gauger").toggle();
            jQuery("#accept_changes").toggle();
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
            if (jQuery("#jform_new_discount").is("valid")) jQuery(".new_discount").hide();
        });

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
                trans();
            }
        });
        jQuery("[name = click_transport]").click(function () {
            trans();
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

    jQuery("#send_all_to_email1").click(function () {

        var email = jQuery("#all-email1").val();
        var client_id = jQuery("#client_id").val();
        var dop_file = jQuery("#dop_file").serialize();
        var testfilename = <?php echo $json;?>;
        var filenames = [];
        for (var i = 0; i < testfilename.length; i++) {
            var id = testfilename[i].id;
            var el = jQuery("#section_estimate_" + id);
            if (el.attr("vis") != "hide") filenames.push(testfilename[i]);
        }
        console.log(filenames);


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

    jQuery("#send_all_to_email2").click(function () {
        var email = jQuery("#all-email2").val();
        var testfilename = <?php echo $json1;?>;
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
    jQuery("#send_all_to_email3").click(function () {
        var email = jQuery("#all-email3").val();
        var id  = jQuery("#project_id").val();
         var client_id = jQuery("#client_id").val();
        var testfilename = <?php echo $json2;?>;
        //        for (var i = 0; i < testfilename.length; i++) {
        //            var id = testfilename[i].id;
        //            var el = jQuery("#section_mount_" + id);
        //            if (el.attr("vis") != "hide") filenames.push(testfilename[i]);
        //        }
        var filenames = [];
        console.log(filenames);
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
            success: function (data) {
                console.log(data);
                var n = noty({
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "success",
                    text: "Общая смета отправлена!"
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

    jQuery("#jform_project_new_calc_date").change(function(){
        jQuery("#jform_new_project_calculation_daypart").prop("disabled",false);
    })

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

// ругается когда вставила календарь!!!
    /* var time = <?php if(isset( $_SESSION['time'])){echo "\"".$_SESSION['time']."\"";} else echo "\"".$time."\"";?>;
    var lnk=document.getElementById('jform_new_project_calculation_daypart').options;
    for (var i=0;i<lnk.length;i++) {
        if (lnk[i].value==time) {
            document.getElementById('jform_new_project_calculation_daypart').disabled = false;
            lnk[i].selected=true;
        }
    } */

     function trans()
    {
        var id = <?php echo $this->item->id; ?>;
        var calcul = jQuery("input[name='transport']:checked").val();
        var transport = jQuery("input[name='transport']:checked").val();
        var distance = jQuery("#distance").val();
        var distance_col = jQuery("#distance_col").val();
        var distance_col_1 = jQuery("#distance_col_1").val();
        var send_data = [];
        send_data["id"] = id; 
        send_data["transport"] = transport;
        switch(transport){
            case "0" :
                send_data["distance_col"] = 0;
                send_data["distance"] = 0;
                break;
            case "1":
                send_data["distance_col"] = distance_col_1;
                send_data["distance"] = 0;
                break;
            case "2" :
                send_data["distance_col"] = distance_col;
                send_data["distance"] = distance;
                break;
        }
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=project.update_transport",
            data:{
                id : send_data["id"],
                transport : send_data["transport"],
                distance : send_data["distance"] ,
                distance_col :send_data["distance_col"]
            },
            success: function(data){
                calc_transport(data);
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
    function calc_transport(data){
        data = JSON.parse(data);
                var html = "",
                    transport_sum = parseFloat(data);
                var calc_sum = 0, calc_total = 0; canvas = 0;
                jQuery.each(jQuery("[name='include_calculation[]']:checked"), function (i,e) {
                    calc_sum += parseFloat(jQuery("[name='calculation_total_discount["+jQuery(e).val()+"]']").val());
                    calc_total += parseFloat(jQuery("[name='calculation_total["+jQuery(e).val()+"]']").val());
                    canvas += parseFloat(jQuery("[name='canvas["+jQuery(e).val()+"]']").val());
                });

                var sum = Float(calc_sum + transport_sum);
                var sum_total = Float(calc_total + transport_sum);

                if (canvas == 0) {
                    sum = (sum <= min_components_sum)?min_components_sum:sum;
                    sum_total = (sum_total <= min_components_sum)?min_components_sum:sum_total;
                } else {
                    sum = (sum <= min_project_sum)?min_project_sum:sum;
                    sum_total = (sum_total <= min_project_sum)?min_project_sum:sum_total;
                }
                sum = Math.round(sum * 100) / 100;
                sum_total = Math.round(sum_total * 100) / 100;

                jQuery("#transport_sum").text(transport_sum.toFixed(0) + " руб.");
                jQuery("#project_total span.sum").text(sum_total);
                jQuery("#project_total_discount span.sum").text(sum  + " руб.");
                jQuery("#project_sum_transport").val(sum);
                jQuery(" #project_sum").val(sum);

                if(canvas == 0) {
                    jQuery("#project_total span.dop").html((sum_total == min_components_sum)?" * минимальная сумма заказа "+min_components_sum+"р.":"");
                    jQuery("#project_total_discount span.dop").html((sum == min_components_sum)?" * минимальная сумма заказа"+min_components_sum+"р.":"");
                }
                else {
                    jQuery("#project_total span.dop").html((sum_total == min_project_sum)?" * минимальная сумма заказа"+min_project_sum+"р.":"");
                    jQuery("#project_total_discount span.dop").html((sum == min_project_sum)?" * минимальная сумма заказа"+min_project_sum+".":"");
                }
    }

    /**
     * @return {number}
     */
    function Float(x, y = 2) {
        return Math.round(parseFloat(""+x) * Math.pow(10,y)) / Math.pow(10,y);
    }

    function calculate_total() {
        var project_total = 0,
            project_total_discount = 0,
            pr_total2 = 0,
            canvas = 0;
            

        jQuery("input[name^='include_calculation']:checked").each(function () {
            var parent = jQuery(this).closest(".include_calculation"),
                calculation_total = parent.find("input[name^='calculation_total']").val(),
                calculation_total_discount = parent.find("input[name^='calculation_total_discount']").val(),
                project_total2 = parent.find("input[name^='calculation_total2']").val(),
                canv = parent.find("input[name^='canvas']").val();

            project_total += parseFloat(calculation_total);
            project_total_discount += parseFloat(calculation_total_discount);
            pr_total2 += parseFloat(project_total2);
            canvas += parseFloat(canv);
        });
        //var canvas = jQuery("#canvas").val();
        
        if(canvas == 0) {
            if(project_total < min_components_sum && pr_total2 !== 0){
                if(min_components_sum>0){
                    project_total = min_components_sum;
                }
            }
              
            if(project_total_discount < min_components_sum && pr_total2 !== 0){
                if(min_components_sum>0){
                    project_total_discount = min_components_sum;
                }
            } 

            jQuery("#project_total span.sum").text(project_total.toFixed(2));
            if (project_total > min_components_sum) { jQuery("#project_total span.dop").html(" "); }
            if (project_total <= min_components_sum && pr_total2 != 0) { jQuery("#project_total span.dop").html(" * минимальная сумма заказа "+min_components_sum+"p."); }
            if (project_total <= min_components_sum && pr_total2 == 0) { jQuery("#project_total span.dop").html(""); }
            jQuery("#project_total_discount span.sum").text(project_total_discount.toFixed(2));
            if (project_total_discount > min_components_sum) { jQuery("#project_total_discount span.dop").html(" "); }
            if (project_total_discount <= min_components_sum && pr_total2 != 0) { jQuery("#project_total_discount span.dop").html(" * минимальная сумма заказа "+min_components_sum+"p."); }
            if (project_total_discount <= min_components_sum && pr_total2 == 0) { jQuery("#project_total_discount span.dop").html(""); }
            //jQuery("#project_total_discount").text(project_total_discount.toFixed(2) );
            jQuery("#project_sum").val(project_total_discount);
        }
        else {
            if(project_total < min_project_sum && pr_total2 !== 0){
                if(min_project_sum>0){
                    project_total = min_project_sum;
                }
            }  
            if(project_total_discount < min_project_sum && pr_total2 !== 0){
                if(min_project_sum>0){
                    project_total_discount = min_project_sum;
                }
            }

            jQuery("#project_total span.sum").text(project_total.toFixed(2));
            if (project_total > min_project_sum) { jQuery("#project_total span.dop").html(" "); }
            if (project_total <= min_project_sum && pr_total2 != 0) { jQuery("#project_total span.dop").html(" * минимальная сумма заказа "+min_project_sum+"p.");; }
            if (project_total <= min_project_sum && pr_total2 == 0) { jQuery("#project_total span.dop").html(""); }
            jQuery("#project_total_discount span.sum").text(project_total_discount.toFixed(2));
            if (project_total_discount > min_project_sum) { jQuery("#project_total_discount span.dop").html(" "); }
            if (project_total_discount <= min_project_sum && pr_total2 != 0) { jQuery("#project_total_discount span.dop").html(" * минимальная сумма заказа "+min_project_sum+"p."); }
            if (project_total_discount <= min_project_sum && pr_total2 == 0) { jQuery("#project_total_discount span.dop").html(""); }
            //jQuery("#project_total_discount").text(project_total_discount.toFixed(2) );
            jQuery("#project_sum").val(project_total_discount);
        }
        
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

    var mountArray = {};

    jQuery("input[name^='smeta']").change(function () {
        comp_sum = <?php echo $dealer_comp_all;?>;
        console.log(comp_sum);
        var sum = jQuery("#calculation_total1")[0].innerText;
        if(jQuery("input[name^='smeta']").attr("checked") == 'checked'){
            jQuery("#calculation_total1")[0].innerText = +sum-comp_sum;

        }
        else{
            jQuery("#calculation_total1")[0].innerText = +sum+comp_sum;

        }
    });

    jQuery("#spend-form input").on("keyup", function () {
        jQuery('#extra_spend_submit').fadeIn();
    });

    jQuery("#penalty-form input").on("keyup", function () {
        jQuery('#penalty_submit').fadeIn();
    });

    jQuery("#bonus-form input").on("keyup", function () {
        jQuery('#bonus_submit').fadeIn();
    });

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

    function PressEnter(your_text, your_event) {
        if (your_text != "" && your_event.keyCode == 13)
            jQuery("#update_discount").click();
    }

</script>

<?php
else:
    echo JText::_('COM_GM_CEILING_ITEM_NOT_LOADED');
endif;
?>
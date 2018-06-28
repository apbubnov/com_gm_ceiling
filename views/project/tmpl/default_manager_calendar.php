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

$user       = JFactory::getUser();
$userId     = $user->get('id');
$user_group = $user->groups;
$dop_num_model = Gm_ceilingHelpersGm_ceiling::getModel('dop_numbers_of_users');
$dop_num = $dop_num_model->getData($userId)->dop_number;
$_SESSION['user_group'] = $user_group;
$_SESSION['dop_num'] = $dop_num;

$canvas_model = Gm_ceilingHelpersGm_ceiling::getModel('canvases');

$canEdit = JFactory::getUser()->authorise('core.edit', 'com_gm_ceiling');

if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_gm_ceiling')) {
    $canEdit = JFactory::getUser()->id == $this->item->created_by;
}

Gm_ceilingHelpersGm_ceiling::create_client_common_estimate($this->item->id);
Gm_ceilingHelpersGm_ceiling::create_common_estimate_mounters($this->item->id);
Gm_ceilingHelpersGm_ceiling::create_estimate_of_consumables($this->item->id);

$project_total = 0;
$project_total_discount = 0;
$total_square = 0;
$total_perimeter = 0;
$model = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
$calculations = $model->new_getProjectItems($this->item->id);
//$need_mount = 1;

$sum_transport = 0;  $sum_transport_discount = 0;
$mountModel = Gm_ceilingHelpersGm_ceiling::getModel('mount');
$mount_transport = $mountModel->getDataAll();

if($this->item->transport == 0 ) $sum_transport = 0;
if($this->item->transport == 1 ) $sum_transport = double_margin($mount_transport->transport * $this->item->distance_col, $this->item->gm_mounting_margin, $this->item->dealer_mounting_margin);
if($this->item->transport == 2 ) $sum_transport = ($mount_transport->distance * $this->item->distance + $mount_transport->transport)  * $this->item->distance_col;
if($this->item->transport == 1 ) {
    $min = 100;
    foreach($calculations as $d) {
        if($d->discount < $min) $min = $d->discount;
    }
    if  ($min != 100) $sum_transport = $sum_transport * ((100 - $min)/100);
}
//if($sum_transport < double_margin($mount_transport->transport, $this->item->gm_mounting_margin, $this->item->dealer_mounting_margin) && $sum_transport != 0) {
//    $sum_transport = double_margin($mount_transport->transport, $this->item->gm_mounting_margin, $this->item->dealer_mounting_margin);
//}
$project_total_discount_transport = $project_total_discount + $sum_transport;

$project_total = $project_total  + $sum_transport;
$project_total_discount = $project_total_discount  + $sum_transport;
$calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
$calculations1 = $calculationsModel->new_getProjectItems($this->item->id);
$components_data = array();
$project_sum = 0;
$counter = 0;

// календарь
$month = date("n");
$year = date("Y");
$FlagCalendar = [3, $user->dealer_id];
$calendar = Gm_ceilingHelpersGm_ceiling::DrawCalendarTar($userId, $month, $year, $FlagCalendar);
//----------------------------------------------------------------------------------

// все замерщики
$AllGauger = $model->FindAllGauger($user->dealer_id, 14);
//----------------------------------------------------------------------------------

?>

<style>

</style>

<link rel="stylesheet" href="/components/com_gm_ceiling/views/project/css/style.css" type="text/css" />
<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>

<?= parent::getButtonBack(); ?>
<?php if ($this->item) : ?>
    <?php
        $need_choose = false;
        $jinput = JFactory::getApplication()->input;
        $phoneto = $jinput->get('phoneto', '0', 'STRING');
        $phonefrom = $jinput->get('phonefrom', '0', 'STRING');
        $call_id = $jinput->get('call_id', 0, 'INT');
        $model_api_phones = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
        $all_advt = $model_api_phones->getAdvt();

        $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
        $cl_phones = $client_model->getItemsByClientId($this->item->id_client);
        $date_time = $this->item->project_calculation_date;
        $date_arr = date_parse($date_time);
        $date = $date_arr['year'] . '-' . $date_arr['month'] . '-' . $date_arr['day'];
        $time = $date_arr['hour'] . ':00';

        //обновляем менеджера для клиента
        $model_client = Gm_ceilingHelpersGm_ceiling::getModel('client');
        if($this->item->manager_id==1||empty($model_client->getClientById($this->item->id_client)->manager_id)){
            $model_client->updateClientManager($this->item->id_client, $userId);
        }
        $projects_model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
        $projects_model->updateManagerId($userId, $this->item->id_client);
        $request_model = Gm_ceilingHelpersGm_ceiling::getModel('requestfrompromo');
        $request_model->delete($this->item->id_client);
        $client_sex  = $model_client->getClientById($this->item->id_client)->sex;
        //$client_dealer_id = $model_client->getClientById($this->item->id_client)->dealer_id;
        //throw new Exception($client_dealer_id);
        if($this->item->id_client!=1){
            $dop_contacts = Gm_ceilingHelpersGm_ceiling::getModel('Clients_dop_contacts');
            $email = $dop_contacts->getEmailByClientID($this->item->id_client);
        }
        $client_dealer = JFactory::getUser($model_client->getClientById($this->item->id_client)->dealer_id);

        if($client_dealer->name == $this->item->client_id){
            $lk = true;
        }
        $recoil_model = Gm_ceilingHelpersGm_ceiling::getModel('recoil');
        $all_recoil = $recoil_model->getData();
    ?>
    <h2 class="center" style="margin-top: 15px; margin-bottom: 15px;">Проект № <?php echo $this->item->id ?></h2>
    <form id="form-client" action="/index.php?option=com_gm_ceiling&task=project.recToMeasurement&type=manager&subtype=calendar" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
        <div class="row">
            <div class="col-xs-12 col-md-6 no_padding">
                <input name="project_id" id="project_id" value="<?php echo $this->item->id; ?>" type="hidden">
                <input name="client_id" id="client_id" value="<?php echo $this->item->id_client; ?>" type="hidden">
                <input name="advt_id" value="<?php echo $reklama->id; ?>" type="hidden">
                <input name="comments_id" id="comments_id" value="<?php if (isset($_SESSION['comments'])) echo $_SESSION['comments']; ?>" type="hidden">
                <input name="status" id="project_status" value="" type="hidden">
                <input name="call_id" value="<?php echo $call_id; ?>" type="hidden">
                <input name="type" value="manager" type="hidden">
                <input name="subtype" value="calendar" type="hidden">
                <input name="data_change" value="0" type="hidden">
                <input name="data_delete" value="0" type="hidden">
                <input name="selected_advt" id="selected_advt" value="<?php echo (!empty($this->item->api_phone_id)) ? $this->item->api_phone_id : '0' ?>" type="hidden">
                <input name="recoil" id="recoil" value="" type="hidden">
                <input id="jform_new_project_calculation_daypart" name="new_project_calculation_daypart" value="
                    <?php if (isset($_SESSION['time'])) {
                        echo $_SESSION['time'];
                    } else if ($this->item->project_calculation_date != null && $this->item->project_calculation_date != "0000-00-00 00:00:00") {
                        echo substr($this->item->project_calculation_date, 11);
                    } ?>
                    " class="inputactive" type="hidden">
                <input name="project_new_calc_date" id="jform_project_new_calc_date" class="inputactive" value="
                    <?php if (isset($_SESSION['date'])) {
                        echo $_SESSION['date'];
                    } else if (isset($this->item->project_calculation_date)) {
                        echo $this->item->project_calculation_date;
                    } ?>
                    " type="hidden">
                <input name="project_gauger" id="jform_project_gauger" class="inputactive" value="
                    <?php if (isset($_SESSION['gauger'])) {
                        echo $_SESSION['gauger'];
                    } else if ($this->item->project_calculator != null) {
                        echo $this->item->project_calculator;
                    } else {
                        echo "0";
                    } ?>
                    " type="hidden">
                <input id="project_sum" name="project_sum" value="<?php echo $project_total_discount ?>" type="hidden">
                <input id="project_sum_transport" name="project_sum_transport" value="<?php echo $project_total_discount_transport ?>" type="hidden">
                <input id="emails" name="emails" value="" type="hidden">
                <input name="without_advt" value="1" type="hidden">
                <table class="table">
                    <tr>
                        <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
                        <td><input name="new_client_name"
                                class="<?php if ($this->item->id_client != "1") echo "inputactive"; else echo "inputactive"; ?>"
                                id="jform_client_name" value="<?php if (isset($_SESSION['FIO'])) {
                                echo $_SESSION['FIO'];
                            } else echo $this->item->client_id; ?>"
                                placeholder="ФИО клиента" type="text"></td>
                        <?php if ($this->item->id_client == "1") { ?>
                            <td>
                                <button id="find_old_client" type="button" class="btn btn-primary"><i
                                            class="fa fa-search" aria-hidden="true"></i></button>
                            </td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <th>
                            Пол клиента
                        </th>
                        <td>

                            <input id='male' type='radio' class="radio" name='slider-sex'
                                value='0' <?php if ($client_sex == "0") echo "checked"; ?>>
                            <label for='male'>Mужской</label>
                            <input id='female' type='radio' class="radio" name='slider-sex'
                                value='1' <?php if ($client_sex == "1") echo "checked"; ?> >
                            <label for='female'>Женский</label>
                        </td>
                    </tr>
                    <tr id="search" style="display : none;">
                        <th>
                            Выберите клиента из списка:
                        </th>
                        <td>
                            <select id="found_clients" class="inputactive">
                            </select>
                        </td>
                    </tr>
                    <? $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client');
                    $birthday = $client_model->getClientBirthday($this->item->id_client); ?>
                    <tr>
                        <th>Дата рождения</th>
                        <td><input name="new_birthday" id="jform_birthday" class="inputactive"
                                value="<? if ($birthday->birthday != 0000 - 00 - 00) echo $birthday->birthday; ?>"
                                placeholder="Дата рождения" type="date"></td>
                        <td>
                            <button type="button" class="btn btn-primary" id="add_birthday">Ок</button>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?>
                            <button type="button" class="btn btn-primary" id="add_phone"><i
                                        class="fa fa-plus-square" aria-hidden="true"></i></button>
                        </th>
                        <td>
                            <?php if ($this->item->id_client == 1) { ?>
                                <input name="new_client_contacts[]" id="jform_client_contacts"
                                    class="inputactive" value="<?php echo $phonefrom; ?>"
                                    placeholder="Телефон клиента" type="text">
                            <?php } elseif (count($cl_phones) == 1) { ?>
                                <input name="new_client_contacts[<?php echo '\'' . $cl_phones[0]->phone . '\'' ?>]"
                                    id="jform_client_contacts"
                                    class="inputactive" value="<?php echo $cl_phones[0]->phone; ?>"
                                    type="text">

                            <?php } elseif (count($cl_phones) > 1) {
                                foreach ($cl_phones as $value) { ?>
                                    <input name="new_client_contacts[<?php echo '\'' . $value->phone . '\'' ?>]"
                                        id="jform_client_contacts"
                                        class="inputactive" value="<?php echo strval($value->phone); ?>"
                                        type="text">
                                <?php }
                            } ?>
                        </td>
                    </tr>
                    <tr>
                        <th></th>
                        <td colspan=2>
                            <div id="phones-block"></div>
                        </td>
                    </tr>
                    <?php if (isset($_SESSION['phones']) && count($_SESSION['phones'] > 1)) {
                        for ($i = 1; $i < count($_SESSION['phones']); $i++) { ?>
                            <tr class='dop-phone'>
                                <th></th>
                                <td>
                                    <input name='new_client_contacts[<?php echo $i; ?>]'
                                        id='jform_client_contacts'
                                        class='inputactive'
                                        value="<?php echo $_SESSION['phones'][$i]; ?>">
                                </td>
                                <td>
                                    <button class='clear_form_group btn btn-danger' type='button'><i
                                                class='fa fa-trash' aria-hidden='true'></i></button>
                                </td>
                            </tr>
                        <?php }
                    } ?>
                    <?php if (count($email) > 0) {
                        foreach ($email as $value) { ?>
                            <tr>
                                <th>e-mail</th>
                                <td><input name="new_email[]" id="jform_email" class="inputhidden"
                                        value="<?php echo $value->contact; ?>" placeholder="e-mail"
                                        type="text">
                                </td>
                            </tr>
                        <?php }
                    } ?>
                    <tr>
                        <th>Добавить адрес эл.почты</th>
                        <td><input name="new_email" id="jform_email" class="inputactive"
                                value="" placeholder="e-mail" type="text"></td>
                        <td>
                            <button type="button" class="btn btn-primary" id="add_email">Ок</button>
                        </td>
                    </tr>
                    <?

                        $street = preg_split("/,.дом([\S\s]*)/", $this->item->project_info)[0];
                        preg_match("/,.дом:.([\d\w\/\s]{1,4})/", $this->item->project_info, $house);
                        $house = $house[1];
                        preg_match("/.корпус:.([\d\W\s]{1,4}),|.корпус:.([\d\W\s]{1,4}),{0}/", $this->item->project_info, $bdq);
                        $bdq = $bdq[1];
                        preg_match("/,.квартира:.([\d\s]{1,4}),/", $this->item->project_info, $apartment);
                        $apartment = $apartment[1];
                        preg_match("/,.подъезд:.([\d\s]{1,4}),/", $this->item->project_info, $porch);
                        $porch = $porch[1];
                        preg_match("/,.этаж:.([\d\s]{1,4})/", $this->item->project_info, $floor);
                        $floor = $floor[1];
                        preg_match("/,.код:.([\d\S\s]{1,10})/", $this->item->project_info, $code);
                        $code = $code[1];

                    ?>
                    <tr>
                        <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?></th>
                        <td><input name="new_address" id="jform_address" class="inputactive"
                                value="<?php if (isset($_SESSION['address'])) {
                                    if ($_SESSION['address'] != $this->item->project_info) {
                                        echo $_SESSION['address'];
                                    } else echo $street;
                                } else echo $street; ?>" placeholder="Адрес"
                                type="text" required="required"></td>
                    </tr>
                    <tr class="controls">
                        <td>Дом / Корпус</td>
                        <td>
                            <input name="new_house" id="jform_house"
                                value="<?php if (isset($_SESSION['house'])) {
                                    if (empty($_SESSION['house'])) {
                                        echo $house;
                                    } else {
                                        echo $_SESSION['house'];
                                    }
                                } else echo $house ?>" class="inputactive"
                                style="width: 50%; margin-bottom: 1em; float: left; margin: 0 5px 0 0;"
                                placeholder="Дом" required="required" aria-required="true" type="text">

                            <input name="new_bdq" id="jform_bdq" value="<?php if (isset($_SESSION['bdq'])) {
                                if (empty($_SESSION['bdq'])) {
                                    echo $bdq;
                                } else {
                                    echo $_SESSION['bdq'];
                                }
                            } else echo $bdq ?>" class="inputactive"
                                style="width: calc(50% - 5px); margin-bottom: 1em;" placeholder="Корпус"
                                aria-required="true" type="text">
                        </td>
                    </tr>
                    <tr class="controls">
                        <td> Квартира / Подъезд</td>
                        <td>
                            <input name="new_apartment" id="jform_apartment"
                                value="<?php if (isset($_SESSION['apartment'])) {
                                    echo $_SESSION['apartment'];
                                } else echo $apartment ?>" class="inputactive"
                                style="width:50%;margin-bottom:1em;margin-right: 5px;float: left;"
                                placeholder="Квартира" aria-required="true" type="text">

                            <input name="new_porch" id="jform_porch"
                                value="<?php if (isset($_SESSION['porch'])) {
                                    echo $_SESSION['porch'];
                                } else echo $porch ?>" class="inputactive"
                                style="width: calc(50% - 5px); margin-bottom: 1em;" placeholder="Подъезд"
                                aria-required="true" type="text">
                        </td>
                    </tr>
                    <tr class="controls">
                        <td> Этаж / Код домофона</td>
                        <td>
                            <input name="new_floor" id="jform_floor"
                                value="<?php if (isset($_SESSION['floor'])) {
                                    echo $_SESSION['floor'];
                                } else echo $floor ?>" class="inputactive"
                                style="width:50%; margin-bottom:1em;  margin: 0 5px  0 0; float: left;"
                                placeholder="Этаж" aria-required="true" type="text">

                            <input name="new_code" id="jform_code" value="<?php if (isset($_SESSION['code'])) {
                                echo $_SESSION['code'];
                            } else echo $code ?>" class="inputactive"
                                style="width: calc(50% - 5px); margin-bottom: 1em;" placeholder="Код"
                                aria-required="true" type="text">
                        </td>
                    </tr>
                    <tr>
                        <th>Примечание</th>
                        <td>
                            <input name="manager_note" id="manager_note" class="inputactive"
                                value="<?php if (isset($_SESSION['manager_comment'])) {
                                echo $_SESSION['manager_comment'];
                                } else echo $this->item->manager_note; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th>Дата и время замера</th>
                        <td>
                            <div id="calendar-container">
                                <div class="btn-small-l">
                                    <button id="button-prev" class="button-prev-small" type="button"
                                            class="btn btn-primary"><i class="fa fa-arrow-left"
                                                                    aria-hidden="true"></i></button>
                                </div>
                                <?php echo $calendar; ?>
                                <div class="btn-small-r">
                                    <button id="button-next" class="button-next-small" type="button"
                                            class="btn btn-primary"><i class="fa fa-arrow-right"
                                                                    aria-hidden="true"></i></button>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <?php if ($this->item->project_verdict == 0) { ?>
                <table class="TabelAction">
                    <tr>
                        <td>
                            <a class="btn  btn-primary" id="rec_to_measurement">
                                Записать на замер
                            </a>
                        </td>
                    </tr>
                </table>
            <?php } ?>
            <input name="idCalcDelete" id="idCalcDelete" value="<?= $calculation->id; ?>" type="hidden">
        </div>
    </form>
<?php endif; ?>

<div id="modal-window-container-tar">
    <button id="close-tar" type="button"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div id="modal-window-choose-tar">
        <p id="date-modal"></p>
        <p><strong>Выберите время замера:</strong></p>
        <p>
        <table id="projects_gaugers"></table>
        </p>
    </div>
</div>

<hr>
<div id="calendar_test"></div>
<input type="text" id="calculation_time1">
<input type="text" id="calculator_id1">
<div id="calendar_test2"></div>
<input type="text" id="calculation_time2">
<input type="text" id="calculator_id2">
<hr>
<div id="calendar2_test"></div>
<input type="text" id="mount1">
<div id="calendar2_test2"></div>
<input type="text" id="mount2">

<div class="modal_window_container" id="mw_container">
    <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div class="modal_window" id="modal_window_measures_calendar"></div>
    <div class="modal_window" id="modal_window_mounts_calendar"></div>
    <div class="modal_window" id="modal-window-call-tar">
        <h6>Введите ФИО</h6>
        <p><input type="text" id="new_fio" placeholder="ФИО" required></p>
        <h6>Введите номер телефона</h6>
        <p><input type="text" id="new_phone" placeholder="ФИО" required></p>
        <p><button type="button" id="add_recoil" class="btn btn-primary">Сохранить</button>
        <button type="button" id="cancel" class="btn btn-primary">Отмена</button></p>
    </div>
</div>
<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/measures_calendar.js"></script>
<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/mounts_calendar.js"></script>
<script type="text/javascript">
    init_measure_calendar('calendar_test','calculation_time1','calculator_id1','modal_window_measures_calendar',['close_mw','mw_container']);
    init_measure_calendar('calendar_test2','calculation_time2','calculator_id2','modal_window_measures_calendar',['close_mw','mw_container']);
    init_mount_calendar('calendar2_test','mount1','modal_window_mounts_calendar',['close_mw','mw_container']);
    init_mount_calendar('calendar2_test2','mount2','modal_window_mounts_calendar',['close_mw','mw_container']);
    var $ = jQuery,
        Data = {};

    jQuery('#mw_container').click(function(e) { // событие клика по веб-документу
        var div = jQuery("#modal_window_measures_calendar"); // тут указываем ID элемента
        var div1 = jQuery("#modal_window_mounts_calendar");
        var div2 = jQuery("#modal-window-call-tar");
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0
            && !div1.is(e.target)
            && div1.has(e.target).length === 0
            && !div2.is(e.target)
            && div2.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#close_mw").hide();
            jQuery("#mw_container").hide();
            jQuery(".modal_window").hide();
        }
    });

    // листание календаря
    month_old = 0;
    year_old = 0;
    jQuery("#calendar-container").on("click", "#button-next", function () {
        month = <?php echo $month; ?>;
        year = <?php echo $year; ?>;
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
        update_calendar(month, year);
    });
    jQuery("#calendar-container").on("click", "#button-prev", function () {
        month = <?php echo $month; ?>;
        year = <?php echo $year; ?>;
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
        update_calendar(month, year);
    });
    function update_calendar(month, year) {
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
                msg += '<div class="btn-small-l"><button id="button-prev" class="button-prev-small" type="button" class="btn btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i></button></div><div class="btn-small-r"><button id="button-next" class="button-next-small" type="button" class="btn btn-primary"><i class="fa fa-arrow-right" aria-hidden="true"></i></button></div>';
                jQuery("#calendar-container").append(msg);
                Today(day, NowMonth, NowYear);
                var datesession = jQuery("#jform_project_new_calc_date").val();
                if (datesession != undefined) {
                    jQuery("#current-monthD"+datesession.substr(8, 2)+"DM"+datesession.substr(5, 2)+"MY"+datesession.substr(0, 4)+"YI"+<?php echo $userId; ?>+"IC0C").addClass("class", "change");
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
    //-----------------------------------------------------------------

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
    //-------------------------------------------------------------------

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
        process.handler= this;
        return process;
    }
    //------------------------------------------

    jQuery(document).ready(function () {

        $("#modal_window_container #ok").click(function() { click_ok(this); });

        window.time = undefined;
        window.gauger = undefined;

        // открытие модального окна с календаря и получение даты и вывода свободных замерщиков
        jQuery("#calendar-container").on("click", ".current-month, .not-full-day, .change", function() {
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
            jQuery("#modal-window-container-tar").show();
			jQuery("#modal-window-choose-tar").show("slow");
            jQuery("#close-tar").show();
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
            if (date == datesession.substr(0, 10)) {
                var timesession = jQuery("#jform_new_project_calculation_daypart").val();
                var gaugersession = jQuery("#jform_project_gauger").val();
                setTimeout(function() { 
                    var times = jQuery("input[name='choose_time_gauger']");
                    if (timesession != undefined) {
                        times.each(function(element) {
                            if (timesession == jQuery(this).val() && gaugersession == jQuery(this).closest('tr').find("input[name='gauger']").val()) {
                                jQuery(this).prop("checked", true);
                            }
                        });
                    }
                }, 200);
            } else if (time != undefined) {
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
        //--------------------------------------------------------------------------------------------------

        // получение значений из селектов
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
            jQuery("#jform_new_project_calculation_daypart").val(time);
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

        //если сессия есть, то выдать дату, которая записана в сессии
        var datesession = jQuery("#jform_project_new_calc_date").val();
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
            jQuery("#current-monthD"+daytocalendar+"DM"+monthtocalendar+"MY"+datesession.substr(0, 4)+"YI"+<?php echo $userId; ?>+"IC0C").addClass("change");
        }
        //-----------------------------------------------------------


        var hrefs = document.getElementsByTagName("a");
        var regexp = /index\.php\?option=com_gm_ceiling\&task=mainpage/;
        for(var i = 0; i < hrefs.length;i++){
            if(regexp.test(hrefs[i].href))
            {

                hrefs[i].onclick = function(){
                    return false;
                };
                break;
            }
        }

        jQuery("#back_btn").click(function(){
            var client_id = jQuery("#client_id").val();
            if(client_id == 1){
                if(jQuery("#jform_client_name").val() == ""){
                    jQuery("#jform_client_name").val("Безымянный");


                }
                jQuery("#form-client").submit();
            }
            else{
                history.back();

            }
        });

        document.onkeydown = function (e) {
            if (e.keyCode === 13) {
                return false;
            }
        }


        var time = <?php if (isset($_SESSION['time'])) {
            echo "\"" . $_SESSION['time'] . "\"";
        } else echo "\"" . $time . "\"";?>;

        var ne = <?php unset($_SESSION['FIO'], $_SESSION['address'],$_SESSION['house'],$_SESSION['bdq'],$_SESSION['apartment'],$_SESSION['porch'],$_SESSION['floor'],$_SESSION['code'], $_SESSION['date'], $_SESSION['time'], $_SESSION['phones'], $_SESSION['manager_comment'], $_SESSION['comments'], $_SESSION['url'], $_SESSION['gauger']); echo 1;?>;


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

        jQuery("#jform_client_contacts").mask("+7 (999) 999-99-99");


        jQuery("#jform_project_new_calc_date").on("keyup", function () {
            jQuery("#jform_new_project_calculation_daypart").prop("disabled", false);
        });

        jQuery("#add_email").click(function(){
            if(jQuery("#jform_email").val()!=""){
                jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=addemailtoclient",
                data: {
                    email:jQuery("#jform_email").val(),
                    client_id: jQuery("#client_id").val()
                },
                success: function (data) {
                    console.log(data);
                    jQuery("#emails").val(jQuery("#emails").val()+data+";");
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "success",
                        text: "Почта добавлена!"
                    });
                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка cервер не отвечает"
                    });
                }
            });
            }
        });

        jQuery("#advt_choose").change(function () {
            jQuery("#selected_advt").val(jQuery("#advt_choose").val());
            if(jQuery("#advt_choose").val()==17){
                jQuery("#recoil_choose").show();
                jQuery("#show_window").show();
            }
            else{
                jQuery("#recoil_choose").hide();
                jQuery("#show_window").hide();
            }
        });

        jQuery("#show_window").click(function(){
            jQuery("#modal-window-container").show();
            jQuery("#modal-window-call-tar").show("slow");
            jQuery("#close-tar").show();
        });

        jQuery("#recoil_choose").change(function(){
            jQuery("#recoil").val(jQuery("#recoil_choose").val());
        });



        jQuery("#client_order").click(function () {
            jQuery("input[name='project_verdict']").val(1);
            jQuery("#project_sum").val(<?php echo $project_total_discount?>);
        });

        jQuery("#rec_to_measurement").click(function () {
            jQuery("#project_status").val(1);
            if (jQuery("#jform_project_gauger").val() == 0
                || jQuery("#jform_client_name").val() == ''
                || jQuery("#jform_address").val() == ''
                || jQuery("#jform_house").val() == '')
            {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Введенны не все данные!"
                });
            }
            else
            {
                jQuery("#form-client").submit();
            }
        });

        jQuery("#accept_changes").click(function () {
            jQuery("input[name='data_change']").val(1);
        });

        jQuery("#change_discount").click(function () {
            jQuery(".new_discount").toggle();

        });


        jQuery("#ok").click(function () {
            var phones = [];
            var s = window.location.href;
            var classname = jQuery("input[name='new_client_contacts[]']");
            Array.from(classname).forEach(function (element) {
                phones.push(element.value);
            });
            jQuery("input[name='data_delete']").val(1);
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=save_data_to_session",
                data: {
                    fio: jQuery("#jform_client_name").val(),
                    address: jQuery("#jform_address").val(),
                    house: jQuery("#jform_house").val(),
                    bdq: jQuery("#jform_bdq").val(),
                    apartment: jQuery("#jform_apartment").val(),
                    porch: jQuery("#jform_porch").val(),
                    floor: jQuery("#jform_floor").val(),
                    code: jQuery("#jform_code").val(),
                    date: jQuery("#jform_project_new_calc_date").val(),
                    time: jQuery("#jform_new_project_calculation_daypart").val(),
                    manager_comment: jQuery("#manager_note").val(),
                    client_name: jQuery("#jform_client_name").val(),
                    phones: phones,
                    comments: jQuery("#comments_id").val(),
                    s: s,
                    gauger: jQuery("#jform_project_gauger").val()
                },
                success: function (data) {
                    jQuery("#form-client").submit();
                },
                dataType: "text",
                timeout: 10000,
                error: function () {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка cервер не отвечает"
                    });
                }
            });

        });
        Data.ProjectInfoYMaps = $("#jform_address").siblings("ymaps");
        Data.ProjectInfoYMaps.click(hideYMaps);

    });

    function submit_form(e) {
        jQuery("#modal_window_container, #modal_window_container *").show();
        jQuery('#modal_window_container').addClass("submit");
    }



    function click_cancel(e) {
        jQuery("#modal_window_container, #modal_window_container *").hide();
    }

    jQuery("#cancel").click(function(){
        jQuery("#close-tar").hide();
        jQuery("#modal-window-container").hide();
        jQuery("#modal-window-call-tar").hide();
    })


    jQuery("#add_phone").click(function () {
        var html = "";
        html += "<tr class = 'dop-phone'>";

        html += "<td><input name='new_client_contacts[]' id='jform_client_contacts' class='inputactive' value=''> </td>";
        html += "<td><button class='clear_form_group btn btn-danger' type='button'><i class='fa fa-trash' aria-hidden='true'></i></button></td> ";
        html += "</tr>";
        jQuery(html).appendTo("#phones-block");
        var classname = jQuery("input[name='new_client_contacts[]']");
        classname.mask("+7 (999) 999-99-99");
        jQuery(".clear_form_group").click(function () {
            jQuery(this).closest(".dop-phone").remove();

        });
        //num_counts++;
    });
    
    jQuery(".clear_form_group").click(function () {
        jQuery(this).closest(".dop-phone").remove();
       // num_counts--;
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


    jQuery("#add_new_dvt").click(function () {
        jQuery("#new_advt_div").toggle();
    });

    jQuery("#save_advt").click(function () {
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
                jQuery("#new_advt_div").hide();
                jQuery("#selected_advt").val(data.id);
            },
            error: function (data) {
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

    jQuery("#jform_project_new_calc_date").change(function () {
        jQuery("#jform_new_project_calculation_daypart").prop("disabled", false);
    });

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


    // @return {number}
    function Float(x, y = 2) {
        return Math.round(parseFloat(""+x) * Math.pow(10,y)) / Math.pow(10,y);
    }



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

    // Подсказки по городам
    ymaps.ready(init);
    function init() {
		var provider
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

    jQuery("#BackPage").click(function ()
    {
        jQuery.ajax({
            url: "index.php?option=com_gm_ceiling&task=projects.deleteEmptyProject",
            data: {
                client_id: "<?php echo $this->item->id_client;?>"
            },
            dataType: "json",
            async: false,
            success: function(data) {
            },
            error: function(data) {
            }
        });
    })

</script>
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

//----------------------------------------------------------------------------------

// все замерщики
$AllGauger = $model->FindAllGauger($user->dealer_id, 14);
//----------------------------------------------------------------------------------

?>

<style>
    td,th{
        padding: 0.25em;
    }
</style>

<link rel="stylesheet" href="/components/com_gm_ceiling/views/project/css/style.css" type="text/css" />
<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>

<?= parent::getButtonBack(); ?>
<?php //print_r($this->item); ?>
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
            <div class="col-xs-1 col-md-1 no_padding">
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
                <input name="project_new_calc_date" id="jform_project_new_calc_date" value="
                    <?php if (isset($_SESSION['date'])) {
                        echo $_SESSION['date'];
                    } else if (isset($this->item->project_calculation_date)) {
                        echo $this->item->project_calculation_date;
                    } ?>
                    " type="hidden">
                <input name="project_gauger" id="jform_project_gauger" value="
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
                <table>
                    <tr>
                        <th><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></th>
                        <td><input name="new_client_name"
                                class="inputactive"
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
                                placeholder="Дата рождения" type="date">
                            <button type="button" class="btn btn-primary btn-sm" id="add_birthday">Ок</button>
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
                                value="" placeholder="e-mail" type="text">
                            <button type="button" class="btn btn-primary btn-sm" id="add_email">Ок</button>
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
                                style="width: 50%; float: left; margin: 0 5px 0 0;"
                                placeholder="Дом" required="required" aria-required="true" type="text">

                            <input name="new_bdq" id="jform_bdq" value="<?php if (isset($_SESSION['bdq'])) {
                                if (empty($_SESSION['bdq'])) {
                                    echo $bdq;
                                } else {
                                    echo $_SESSION['bdq'];
                                }
                            } else echo $bdq ?>" class="inputactive"
                                style="width: calc(50% - 5px);" placeholder="Корпус"
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
                                style="width:50%;margin-right: 5px;float: left;"
                                placeholder="Квартира" aria-required="true" type="text">

                            <input name="new_porch" id="jform_porch"
                                value="<?php if (isset($_SESSION['porch'])) {
                                    echo $_SESSION['porch'];
                                } else echo $porch ?>" class="inputactive"
                                style="width: calc(50% - 5px);" placeholder="Подъезд"
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
                                style="width:50%;  margin: 0 5px  0 0; float: left;"
                                placeholder="Этаж" aria-required="true" type="text">

                            <input name="new_code" id="jform_code" value="<?php if (isset($_SESSION['code'])) {
                                echo $_SESSION['code'];
                            } else echo $code ?>" class="inputactive"
                                style="width: calc(50% - 5px);" placeholder="Код"
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
                        <th colspan="2">Дата и время замера</th>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div id="measures_calendar"></div>
                            <input type="text" id="measure_info" class="inputactive" readonly>
                        </td>
                    </tr>
                </table>
                <?php if (!in_array($this->item->project_status,VERDICT_STATUSES)) { ?>
                    <a class="btn  btn-primary" id="rec_to_measurement">
                        Записать на замер
                    </a>
                <?php } ?>
            </div>
        </div>
    </form>
<?php endif; ?>

<div class="modal_window_container" id="mw_container">
    <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div class="modal_window" id="modal_window_measures_calendar"></div>
</div>

<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/measures_calendar.js"></script>
<script type="text/javascript">
    init_measure_calendar('measures_calendar','jform_project_new_calc_date','jform_project_gauger','modal_window_measures_calendar',['close_mw','mw_container'], 'measure_info');

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


    document.onkeydown = function (e) {
        if (e.keyCode === 13) {
            return false;
        }
    }

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

</script>
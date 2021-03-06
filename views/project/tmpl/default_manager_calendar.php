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
?>

<style>
    td,th{
        padding: 0.25em;
    }
</style>

<link rel="stylesheet" href="/components/com_gm_ceiling/views/project/css/style.css" type="text/css" />
<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>

<?= parent::getButtonBack(); ?>
<?php
    if ($this->item) :
        $user       = JFactory::getUser();
        $userId     = $user->get('id');
        $user_group = $user->groups;
        $dealer_id = $user->dealer_id;
        $dealer_type = JFactory::getUser($dealer_id)->dealer_type;
        $jinput = JFactory::getApplication()->input;
        $call_id = $jinput->get('call_id', 0, 'INT');
        $client_model = Gm_ceilingHelpersGm_ceiling::getModel('client_phones');
        $model_client = Gm_ceilingHelpersGm_ceiling::getModel('client');
        $projects_model = Gm_ceilingHelpersGm_ceiling::getModel('projects');
        $request_model = Gm_ceilingHelpersGm_ceiling::getModel('requestfrompromo');
        $dop_contacts = Gm_ceilingHelpersGm_ceiling::getModel('Clients_dop_contacts');
        $model_api_phones = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');

        $cl_phones = $client_model->getItemsByClientId($this->item->id_client);
        //обновляем менеджера для клиента

        if($this->item->manager_id==1||empty($model_client->getClientById($this->item->id_client)->manager_id)){
            $model_client->updateClientManager($this->item->id_client, $userId);
        }
        $projects_model->updateManagerId($userId, $this->item->id_client);
        $request_model->delete($this->item->id_client);
        $client_sex  = $model_client->getClientById($this->item->id_client)->sex;

        if($this->item->id_client!=1){
            $email = $dop_contacts->getEmailByClientID($this->item->id_client);
        }

        if(!empty($this->item->api_phone_id)){
            if ($this->item->api_phone_id == 10) {
                $repeat_advt = $repeat_model->getDataByProjectId($this->item->id);
                if (!empty($repeat_advt->advt_id)) {
                    $reklama = $model_api_phones->getDataById($repeat_advt->advt_id);
                } else {
                    $need_choose = true;
                }
            }
            $reklama = $model_api_phones->getDataById($this->item->api_phone_id);
            $write = $reklama->number .' '.$reklama->name . ' ' . $reklama->description;
        }
        else{
            $need_choose = true;
            $all_advt = $model_api_phones->getDealersAdvt($dealer_id);
        }

       $address = Gm_ceilingHelpersGm_ceiling::parseProjectInfo($this->item->project_info);

    ?>
    <h2 class="center" style="margin-top: 15px; margin-bottom: 15px;">Проект № <?php echo $this->item->id ?></h2>
    <form id="form-client" action="/index.php?option=com_gm_ceiling&task=project.recToMeasurement&type=manager&subtype=calendar" method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
        <div class="row center">
            <div class="col-xs-12 col-md-12">
                <h5 class="center">
                    <?php if (!$need_choose) { ?>
                        <input id="advt_info" class="h5-input" readonly value= <?php echo '"' . $write . '"'; ?>>
                        <?php if($reklama->id == 17) { ?>
                            <select id="recoil_choose" name ="recoil_choose">
                                <option value="0">-Выберите откатника-</option>
                                <?php foreach ($all_recoil as $item) { ?>
                                    <option value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
                                <?php } ?>
                            </select>
                            <button type="button" id = "show_window" class="btn btn-primary"><i class="fa fa-plus-square" aria-hidden="true"></i></button>
                        <?php }?>
                    <?php } elseif ($need_choose) { ?>
                        <select id="advt_choose">
                            <option value="0">Выберите рекламу</option>
                            <?php foreach ($all_advt as $item) { ?>
                                <option value="<?php echo $item['id'] ?>"><?php echo $item['advt_title'] ?></option>
                            <?php } ?>
                        </select>
                        <button type="button" id="add_new_dvt" class="btn btn-primary"><i class="far fa-plus-square"></i></button>
                        <select id="recoil_choose" name ="recoil_choose" style="display:none;">
                            <option value="0">-Выберите откатника-</option>
                            <?php foreach ($all_recoil as $item) { ?>
                                <option value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
                            <?php } ?>
                        </select>
                        <button type="button" id = "show_window" style = "display:none;" class="btn btn-primary"><i class="fa fa-plus-square" aria-hidden="true"></i></button>

                    <?php } ?>
                </h5>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 no_padding">
                <input name="project_id" id="project_id" value="<?php echo $this->item->id; ?>" type="hidden">
                <input name="client_id" id="client_id" value="<?php echo $this->item->id_client; ?>" type="hidden">
                <input name="advt_id" id ="advt_id" value="<?php echo $reklama->id; ?>" type="hidden">
                <input name="comments_id" id="comments_id" value="<?php if (isset($_SESSION['comments'])) echo $_SESSION['comments']; ?>" type="hidden">
                <input name="status" id="project_status" value="" type="hidden">
                <input name="call_id" value="<?php echo $call_id; ?>" type="hidden">
                <input name="type" value="manager" type="hidden">
                <input name="subtype" value="calendar" type="hidden">
                <input name="data_change" value="0" type="hidden">
                <input name="data_delete" value="0" type="hidden">
                <input name="selected_advt" id="selected_advt" value="<?php echo (!empty($this->item->api_phone_id)) ? $this->item->api_phone_id : '' ?>" type="hidden">
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

                <input id="emails" name="emails" value="" type="hidden">
                <input name="without_advt" value="0" type="hidden">

                <div class="row" style="margin-bottom: 15px">
                    <div class="col-md-4 col-xs-4">
                        <b><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?></b>
                    </div>
                    <div class="col-md-8 col-xs-8">
                        <input name="new_client_name" class="inputactive" id="jform_client_name" value="<?php echo $this->item->client_id; ?>"
                               placeholder="ФИО клиента" type="text">
                    </div>
                </div>
                <div class="row" style="margin-bottom: 15px">
                    <div class="col-md-4 col-xs-4">
                        <b>Пол клиента</b>
                    </div>
                    <div class="col-md-8 col-xs-8">
                        <input id='male' type='radio' class="radio" name='slider-sex'
                               value='0' <?php if ($client_sex == "0") echo "checked"; ?>>
                        <label for='male'>Mужской</label>
                        <input id='female' type='radio' class="radio" name='slider-sex'
                               value='1' <?php if ($client_sex == "1") echo "checked"; ?> >
                        <label for='female'>Женский</label>
                    </div>
                </div>
                <div class="row" style="margin-bottom: 15px">
                    <div class="col-md-4 col-xs-4">
                        <div class="col-md-8" style="padding-left: 0;">
                            <b><?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?></b>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary" id="add_phone">
                                <i class="fa fa-plus-square" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-8 col-xs-8" id="phones-block">
                        <?php if (count($cl_phones) == 1) { ?>
                            <div class="col-md-12" style="padding:0;margin-bottom:6px;">
                                <input name="new_client_contacts[<?php echo '\'' . $cl_phones[0]->phone . '\'' ?>]"
                                    id="jform_client_contacts"
                                    class="inputactive" value="<?php echo $cl_phones[0]->phone; ?>"
                                    type="text">
                            </div>
                        <?php } elseif (count($cl_phones) > 1) {
                            foreach ($cl_phones as $value) { ?>
                                <div class="col-md-12"  style="padding:0;margin-bottom:6px;">
                                    <input name="new_client_contacts[<?php echo '\'' . $value->phone . '\'' ?>]"
                                            id="jform_client_contacts"
                                            class="inputactive" value="<?php echo strval($value->phone); ?>"
                                            type="text">
                                </div>
                            <?php }
                        }?>
                    </div>
                </div>
                <?php if (count($email) > 0) {
                    foreach ($email as $value) { ?>
                        <div class="row">
                            <div class="col-md-4"><b>e-mail</b></div>
                            <div class="col-md-8">
                                <input name="new_email[]" id="jform_email" class="inputhidden"
                                       value="<?php echo $value->contact; ?>" placeholder="e-mail"
                                       type="text" readonly>
                            </div>
                        </div>
                    <?php }
                } ?>
                <div class="row" style="margin-bottom: 15px">
                    <div class="col-md-4">
                        <b>Добавить адрес эл.почты</b>
                    </div>
                        <div class="col-md-8">
                            <div class="col-md-10 col-xs-10" style="padding-left:0;">
                                <input name="new_email" id="jform_email" class="inputactive" value="" placeholder="e-mail" type="text">
                            </div>
                            <div class="col-md-2 col-xs-2" style="padding:0;">
                                <button type="button" class="btn btn-primary btn-sm" style="righr:0;" id="add_email">Ок</button>
                            </div>
                        </div>
                </div>
                <div class="row" style="margin-bottom: 15px">
                    <div class="col-md-4">
                        <b><?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?></b>
                    </div>
                    <div class="col-md-8">
                        <input name="new_address" id="jform_address" class="inputactive" value="<?php echo $address->street; ?>" placeholder="Адрес"
                               type="text" required="required">
                    </div>
                </div>
                <div class="row" style="margin-bottom: 15px">
                    <div class="col-md-4">
                        <b>Дом / Корпус</b>
                    </div>
                    <div class="col-md-8">
                        <input name="new_house" id="jform_house"
                               value="<?php  echo $address->house ?>" class="inputactive" style="width: 50%; float: left; margin: 0 5px 0 0;" placeholder="Дом" required="required" aria-required="true" type="text">
                        <input name="new_bdq" id="jform_bdq" value="<?php echo $address->bdq ?>" class="inputactive"
                               style="width: calc(50% - 5px);" placeholder="Корпус"
                               aria-required="true" type="text">
                    </div>
                </div>
                <div class="row" style="margin-bottom: 15px">
                    <div class="col-md-4">
                        <b>Квартира / Подъезд</b>
                    </div>
                    <div class="col-md-8">
                        <input name="new_apartment" id="jform_apartment"
                               value="<?php echo $address->apartment ?>" class="inputactive"
                               style="width:50%;margin-right: 5px;float: left;"
                               placeholder="Квартира" aria-required="true" type="text">

                        <input name="new_porch" id="jform_porch"
                               value="<?php echo $address->porch ?>" class="inputactive"
                               style="width: calc(50% - 5px);" placeholder="Подъезд"
                               aria-required="true" type="text">
                    </div>
                </div>
                <div class="row" style="margin-bottom: 15px">
                    <div class="col-md-4">
                        <b>Этаж / Код домофона</b>
                    </div>
                    <div class="col-md-8">
                        <input name="new_floor" id="jform_floor"
                               value="<?php echo $address->floor ?>" class="inputactive" style="width:50%;  margin: 0 5px  0 0; float: left;" placeholder="Этаж" aria-required="true" type="text">

                        <input name="new_code" id="jform_code" value="<?php echo $address->code ?>" class="inputactive" style="width: calc(50% - 5px);" placeholder="Код" aria-required="true" type="text">
                    </div>
                </div>
                <div class="row" style="margin-bottom: 15px">
                    <div class="col-md-4">
                        <b>Дата и время замера</b>
                    </div>
                    <div class="col-md-8" align="center">
                        <div id="measures_calendar"></div>
                        <input type="text" id="measure_info" class="inputactive" readonly>
                    </div>
                </div>

            </div>
            <div class="col-md-6">
                <h4 class="center">Примечения</h4>
                <?php include_once('components/com_gm_ceiling/views/project/project_notes.php'); ?>
            </div>
        </div>
        <div class="row center">
            <?php if (!in_array($this->item->project_status,VERDICT_STATUSES)) { ?>
                <div class="col-md-12 ">
                    <a class="btn btn-primary" id="rec_to_measurement"> Записать на замер </a>
                </div>

            <?php } ?>
        </div>
    </form>
<?php endif; ?>

<div class="modal_window_container" id="mw_container">
    <button type="button" class="close_btn" id="close_mw"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div class="modal_window" id="modal_window_measures_calendar"></div>
    <div class="modal_window" id="mw_new_adwt">
        <h4>Добавление нового вида рекламы</h4>
        <div class="row">
            <div class="col-md-3">

            </div>
            <div class="col-md-6">
                <div class="col-md-10">
                    <input id="new_advt_name" class="form-control"  placeholder="Название рекламы">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary" id="save_advt">Ok</button>
                </div>
            </div>
            <div class="col-md-3">

            </div>
        </div>
    </div>
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
        var div3 = jQuery("#mw_new_adwt");
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0
            && !div1.is(e.target)
            && div1.has(e.target).length === 0
            && !div2.is(e.target)
            && div2.has(e.target).length === 0
            && !div3.is(e.target)
            && div3.has(e.target).length === 0) { // и не по его дочерним элементам
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

    jQuery("#add_new_dvt").click(function () {
        jQuery("#mw_container").show();
        jQuery("#mw_new_adwt").show('slow');
        jQuery("#close").show();
    })

    jQuery("#save_advt").click(function() {
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
                jQuery('#mw_container').hide();
                jQuery('#mw_new_adwt').hide();
                jQuery('#close').hide();
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
        var html = "<div class='row dop_phone' style='margin-bottom:5px;'>";
        html += "<div class='col-md-10'><input name='new_client_contacts[]' id='jform_client_contacts' class='inputactive' value=''> </div>";
        html += "<div class='col-md-2'><button class='clear_form_group btn btn-danger' type='button'><i class='fa fa-trash' aria-hidden='true'></i></button></div> ";
        html += "</div>";
        jQuery(html).appendTo("#phones-block");
        var classname = jQuery("input[name='new_client_contacts[]']");
        classname.mask("+7 (999) 999-99-99");
        jQuery(".clear_form_group").click(function () {
            jQuery(this).closest(".row").remove();

        });
        //num_counts++;
    });
    
    jQuery(".clear_form_group").click(function () {
        jQuery(this).closest(".row").remove();
       // num_counts--;
    });



</script>
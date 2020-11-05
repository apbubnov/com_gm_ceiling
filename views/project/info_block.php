<?php
$calculationsModel = Gm_ceilingHelpersGm_ceiling::getModel('calculations');
$dopContactsModel = Gm_ceilingHelpersGm_ceiling::getModel('clients_dop_contacts');
$apiPhoneModel = Gm_ceilingHelpersGm_ceiling::getModel('api_phones');
$repeatModel = Gm_ceilingHelpersGm_ceiling::getModel('repeatrequest');

$contact_email = $dopContactsModel->getContact($this->item->id_client);
$all_advt = $apiPhoneModel->getAdvt();
if ($this->item->api_phone_id == 10) {
    $repeat_advt = $repeatModel->getDataByProjectId($this->item->id);
    if (!empty($repeat_advt->advt_id)) {
        $reklama = $apiPhoneModel->getDataById($repeat_advt->advt_id);
    } else {
        $reklama = $apiPhoneModel->getDataById(10);
    }
} else {
    if (!empty($this->item->api_phone_id)) {
        $reklama = $apiPhoneModel->getDataById($this->item->api_phone_id);
    }

}

$advt_str = $reklama->number . ' ' . $reklama->name . ' ' . $reklama->description;

$address = Gm_ceilingHelpersGm_ceiling::parseProjectInfo($this->item->project_info);

if(gettype($this->item->mount_data) == 'string'){
    $this->item->mount_data = json_decode($this->item->mount_data);
}
?>
<style>
    .act_btn{
        width:210px;
        margin-bottom: 10px;
    }
    .save_bnt{
        width:250px;
        height: 60px;
    }
    .btn_edit{
        position: absolute;
        margin-right: 10px;
    }
    @media screen and (max-width:768px) {
        .btn_edit{
            position: absolute;
            margin-right: 0px;
        }
    }
    .manuf_div{
        height:80px;
        border: 2px solid grey;
        border-radius: 5px;
        margin-bottom: 5px;
    }
    .manuf_div.selected{
        border: 2px solid #414099;
        background-color: #d3d3f9;
    }
    .edit_div{
        position: absolute;
        right:0px;
    }
    .row{
        margin-bottom: 5px !important;
        margin-left: 2px !important;
        padding-right: 5px !important;
    }

    .border_container{
        border: 1px solid #414099;
        border-radius: 5px;
        margin-bottom: 15px;
        padding-left: 0;
        padding-right: 0;
    }
    .container{
        padding-left: 0;
        padding-right: 0;
    }

</style>
<link rel="stylesheet" href="/components/com_gm_ceiling/views/project/css/style.css" type="text/css" />
<input type="hidden" id="client_name_cl" value="<?=$this->item->client_id?>">
<div class="container" style="border: 1px solid #414099;border-radius: 5px;margin-bottom: 15px;">
    <div class="row">
        <div class="col-md-4 col-xs-4">
            <b>
                <?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_CLIENT_ID'); ?>
            </b>
        </div>
        <div class="col-md-6 col-xs-6">
            <a href="/index.php?option=com_gm_ceiling&view=clientcard&id=<?= $this->item->id_client; ?>">
                <?php echo $this->item->client_id; ?>
            </a>
        </div>
        <div class="col-md-2 col-xs-2">
            <button class="btn btn-sm btn-primary btn_edit" type="button" id="change_data"><i class="fas fa-pen"
                                                                                              aria-hidden="true"></i>
            </button>

        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <b>
                <?php echo JText::_('COM_GM_CEILING_CLIENTS_CLIENT_CONTACTS'); ?>
            </b>
        </div>
        <?php
        if ($this->item->id_client != 1) {
            $phone = $calculationsModel->getClientPhones($this->item->id_client);
        } else {
            $phone = [];
        }
        ?>
        <div class="col-md-8">
            <?php
            foreach ($phone AS $contact) {
                echo "<a href='tel:+$contact->client_contacts'>$contact->client_contacts</a>";
                echo "<br>";
            }
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <b>
                Почта
            </b>
        </div>
        <div class="col-md-8">
            <?php
            foreach ($contact_email AS $contact) {
                echo "<a href='mailto:$contact->contact'>$contact->contact</a>";
                echo "<br>";
            }
            ?>
        </div>
    </div>
    <div class="row center">
        <div class="col-md-12">
            <button class="btn btn-primary" type="button" id="assign_call">Назначить звонок</button>
        </div>
    </div>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-4 col-xs-4">
            <b>
                <?php echo JText::_('COM_GM_CEILING_FORM_LBL_PROJECT_PROJECT_INFO'); ?>
            </b>
        </div>
        <div class="col-md-6 col-xs-6">
            <a target="_blank" href="https://yandex.ru/maps/?mode=search&text=<?= $this->item->project_info; ?>">
                <?= $this->item->project_info; ?>
            </a>
        </div>
        <div class="col-md-2 col-xs-2" style="text-align: right;">
            <button class="btn btn-sm btn-primary" type="button" id="edit_address">
                <i class="fas fa-pen" aria-hidden="true"></i>
            </button>
        </div>
    </div>
    <?php if(!empty($this->item->project_calculation_date) && $this->item->project_calculation_date != '0000-00-00 00:00:00'){?>
        <div class="row">
            <div class="col-md-4 col-xs-4">
                <b>Дата замера</b>
            </div>
            <div class="col-md-6 col-xs-6">
                <?=$this->item->project_calculation_date;?>
            </div>
            <?php if(!in_array($this->item->project_status,VERDICT_STATUSES)){ ?>
                <div class="col-md-2 col-xs-2" style="text-align: right">
                    <button class="btn btn-primary btn-sm" type="button" id="change_meaure_date">
                        <i class="fas fa-pen" aria-hidden="true"></i>
                    </button>
                </div>
            <?php } ?>
        </div>
    <?php }?>
    <?php if(!empty($this->item->project_calculator)){?>
    <div class="row">
        <div class="col-md-4 col-xs-4">
            <b>Замерщик</b>
        </div>
        <div class="col-md-8 col-xs-8">
            <?= JFactory::getUser($this->item->project_calculator)->name;?>
        </div>
    </div>
    <?php }?>
    <?php if (!empty($this->item->mount_data)): ?>
        <div class="row center">
            <div class="col-xs-12 col-md-12">
                <b>Монтаж</b>
            </div>
        </div>
        <?php foreach ($this->item->mount_data as $value) { ?>
            <div class="row">
                <div class="col-xs-4 col-md-4">
                    <b>
                        <?php echo $value->time; ?>
                    </b>
                </div>
                <div class="col-xs-4 col-md-4">
                    <?php echo $value->stage_name; ?>
                </div>
                <div class="col-xs-4 col-md-4">
                    <?php echo JFactory::getUser($value->mounter)->name; ?>
                </div>
            </div>
        <?php } ?>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-8 col-xs-8">
            <b>
                Текущий процент скидки:
            </b>
        </div>
        <div class="col-md-4 col-xs-4">
            <?php echo (!empty($this->item->project_discount)) ? $this->item->project_discount : " - "; ?>
        </div>
        <!--<div class="col-md-2" style="text-align: right;">
            <button class="btn btn-sm btn-primary" type="button" id="edit_discount"><i class="fas fa-pen"
                                                                                       aria-hidden="true"></i></button>
        </div>-->
    </div>
    <div class="row">
        <div class="col-md-6 col-xs-6">
            <b>Новый процент скидки: </b>
        </div>
        <div class="col-md-4 col-xs-4">
            <input name="new_discount" id="jform_new_discount" placeholder="%" min="0" max='99'
                   type="number" class="form-control">
        </div>
        <div class="col-md-2 col-xs-2" style="text-align: right;">
            <button class="btn btn-sm btn-primary" id="update_discount"><i class="far fa-save"></i></button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 col-xs-4">
            <b>
                Реклама
            </b>
        </div>
        <div class="col-md-6 col-xs-6">
            <?php echo (!empty($advt_str)) ? $advt_str : " - "; ?>
        </div>
        <div class="col-md-2 col-xs-2" style="text-align: right;">
            <button class="btn btn-sm btn-primary" type="button" id="edit_advt"><i class="fas fa-pen"
                                                                                   aria-hidden="true"></i></button>
        </div>
    </div>
</div>
<div class="modal_window_container mw_container_cl">
    <div id="mw_add_call" class="modal_window">
        <h4>Добавить звонок</h4>
        <link rel="stylesheet" href="/components/com_gm_ceiling/date_picker/nice-date-picker.css">
        <script src="/components/com_gm_ceiling/date_picker/nice-date-picker.js"></script>
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <div class="row" style="margin-bottom: 1em;">
                <label><b>Дата: </b></label><br>
                <div id="calendar-wrapper" align="center"></div>
                <script>
                    new niceDatePicker({
                        dom: document.getElementById('calendar-wrapper'),
                        mode: 'en',
                        onClickDate: function (date) {
                            document.getElementById('call_date').value = date;
                        }
                    });
                </script>
                <input name="call_date" id="call_date" type="hidden">
            </div>

            <div class="row" style="margin-bottom: 1em;">
                <div class="col-md-6" style="text-align: left;">
                    <label for="call_time" style="vertical-align: middle;">
                        <b>Время: </b>
                    </label>
                </div>
                <div class="col-md-6">
                    <input type="time" id="call_time" class="form-control">
                </div>
            </div>
            <div class="row center" style="margin-bottom: 1em;">
                <div class="col-md-12">
                    <input name="call_comment" id="call_comment" placeholder="Введите примечание" class="form-control">
                </div>
            </div>
            <div class="row center">
                <div class="col-md-12">
                    <input type="checkbox" id="important_call" class="inp-cbx" style="display: none">
                    <label for="important_call" class="cbx">
                <span>
                    <svg width="12px" height="10px" viewBox="0 0 12 10">
                        <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                    </svg>
                </span>
                        <span>Важный звонок</span>
                    </label>
                </div>
            </div>
            <div class="row center" >
                <button class="btn btn-primary" id="add_call" type="button">Сохранить</button>
            </div>
        </div>
        <div class="col-md-4"></div>
    </div>
    <div id="mw_cl_info" class="modal_window">
        <h4>Изменение данных клиента</h4>
       <!-- <form id="new_cl_info">-->
            <div class="row">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="new_surname"> Фамилия </label>
                        </div>
                        <div class="col-md-9">
                            <input class="form-control" type="text" id='new_surname'>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label for="new_name"> Имя </label>
                        </div>
                        <div class="col-md-9">
                            <input class="form-control" type="text" id='new_name'>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label for="new_patronymic"> Отчество </label>
                        </div>
                        <div class="col-md-9">
                            <input class="form-control" type="text" id='new_patronymic'>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div id="client_phones">
                        <div class="row">
                            <div class="col-md-10">
                                <b>Телефоны клиента</b>

                            </div>
                            <div class="col-md-2">
                                <button id="add_phone" class="btn btn-primary" type="button"><i
                                            class="fa fa-plus-square" aria-hidden="true"></i></button>
                            </div>
                        </div>
                        <?php foreach ($phone as $value) { ?>
                            <div class="row">
                                <div class="col-md-10">
                                    <input name="new_client_contacts[]" class="form-control"
                                           id="jform_client_contacts[]"
                                           data-old="<?php echo $value->client_contacts; ?>"
                                           placeholder="Телефон клиента" type="text"
                                           value=<?php echo $value->client_contacts; ?>>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-danger del_phone" type="button"><i class="fas fa-trash-alt"
                                                                                              aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <div id="client_emails">
                        <div class="row">
                            <div class="col-md-10">
                                <b>Эл.почта клиента</b>
                            </div>
                            <div class="col-md-2">
                                <button id="add_email" class="btn btn-primary" type="button"><i
                                            class="fa fa-plus-square" aria-hidden="true"></i></button>
                            </div>
                        </div>
                        <?php foreach ($contact_email as $value) { ?>
                            <div class="row">
                                <div class="col-md-10">
                                    <input name="new_client_emails[]" id="jform_client_emails[]"
                                           placeholder="Email клиента" type="text" class="form-control"
                                           data-old="<?php echo $value->contact; ?>"
                                           value=<?php echo $value->contact; ?>>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-danger del_email"><i class="fas fa-trash-alt"
                                                                                aria-hidden="true"></i></button>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <input name="new_client_name" id="jform_client_name" value="" type="hidden">
        <!--</form>-->
        <button id="update_cl_info" class="btn btn-primary" type="button">Сохранить</button>
    </div>
    <div id="mw_measures_calendar_cl" class="modal-window1"></div>
    <div id="mw_address" class="modal_window">
        <div class="row center">
            <div class="col-md-12">
                <label><strong>Адрес замера</strong></label>
                <div class="row">
                    <div class="col-md-4 col-xs-4">
                        <label><b>Улица</b></label>
                    </div>
                    <div class="col-md-8 col-xs-8">
                        <input  class="form-control new_address_cl" placeholder="Улица" type="text" value="<?=$address->street;?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 col-xs-4">
                        <label><b>Дом \ Корпус</b></label>
                    </div>
                    <div class="col-md-4 col-xs-4">
                        <input class="form-control new_house_cl" placeholder="Дом" aria-required="true" type="text" value="<?=$address->house;?>">
                    </div>
                    <div class="col-md-4 col-xs-4">
                        <input class="form-control new_bdq_cl" placeholder="Корпус" aria-required="true"
                               type="text" value="<?=$address->bdq;?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 col-xs-4">
                        <label><b>Квартира \ Подъезд</b></label>
                    </div>
                    <div class="col-md-4 col-xs-4">
                        <input class="form-control new_apartment_cl" placeholder="Квартира" aria-required="true"
                               type="text" value="<?=$address->apartment;?>">
                    </div>
                    <div class="col-md-4 col-xs-4">
                        <input class="form-control new_porch_cl" placeholder="Подъезд" aria-required="true"
                               type="text" value="<?=$address->porch;?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 col-xs-4">
                        <label><b>Этаж \ Код домофона</b></label>
                    </div>
                    <div class="col-md-4 col-xs-4">
                        <input class="form-control new_floor_cl" placeholder="Этаж" aria-required="true"
                        type="text" value="<?=$address->floor;?>">
                    </div>
                    <div class="col-md-4 col-xs-4">
                        <input class="form-control new_code_cl" placeholder="Код" aria-required="true"
                               type="text" value="<?=$address->code;?>">
                    </div>
                </div>
            </div>
        </div>
        <button id="save_address_cl" class="btn btn-primary" type="button">Сохранить</button>
    </div>
    <div id="mw_advt" class="modal_window">
        <h4>Изменение/добавление рекламы</h4>
        <label>Выберите или добавьте новую рекламу</label>
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-6">
                        <label><strong>Выбрать:</strong></label>
                    </div>
                    <div class="col-md-6">
                        <select id="advt_choose" class="form-control">
                            <option value="0">Выберите рекламу</option>
                            <?php if (!empty($all_advt)) foreach ($all_advt as $item) { ?>
                                <option value="<?php echo $item->id ?>"><?php echo $item->name ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label><strong>Добавить:</strong></label>
                    </div>
                    <div class="col-md-8">
                        <div id="new_advt_div" class="col-md-6">
                            <input id="new_advt_name" placeholder="Название рекламы" class="form-control">
                        </div>
                        <div class="col-md=6">
                            <button type="button" class="btn btn-primary" id="add_new_advt">Добавить</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3"></div>
        </div>
        <div class="row center">
            <button class="btn btn-primary" id="save_advt" type="button">Сохранить</button>
        </div>
    </div>
    <div id="mw_change_measure" class="modal_window">
        <div class="row center">
            <div class="col-md-12">
                <label><strong>Время замера</strong></label>
                <div id = "measures_calendar_cl" align="center"></div>
                <input  id="measure_info_cl" readonly>
            </div>
        </div>
        <div class="row center">
            <button class="btn btn-primary" id="save_measure_changes" type="button">Сохранить</button>
        </div>
    </div>
</div>
<script type="text/javascript" src="/components/com_gm_ceiling/date_picker/measures_calendar.js"></script>
<script type="text/javascript">
    var project_id = jQuery('#project_id').val(),
        client_id = jQuery('#client_id').val(),
        client_name = jQuery('#client_name_cl').val(),
        deleted_phones = [],
        deleted_emails = [];
    jQuery(document).mouseup(function (e) { // событие клика по веб-документу
        var div1 = jQuery('#mw_add_call'),
            div2 = jQuery('#mw_advt'),
            div3 = jQuery('#mw_address'),
            div4 = jQuery('#mw_cl_info'),
            div5 = jQuery('#mw_measures_calendar_cl'),
            div6 = jQuery('#mw_change_measure');
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
            jQuery("#mw_close").hide();
            jQuery(".mw_container_cl").hide();
            div1.hide();
            div2.hide();
            div3.hide();
            div4.hide();
            div5.hide();
            div6.hide();
        }
    });
    jQuery(document).ready(function () {
        init_measure_calendar('measures_calendar_cl','jform_project_new_calc_date','jform_project_gauger','mw_measures_calendar_cl',[], 'measure_info_cl');

        jQuery("[name = 'new_client_contacts[]']").mask('+7(999) 999-9999');

        jQuery("#assign_call").click(function () {
            jQuery("#close_mw").show();
            jQuery(".mw_container_cl").show();
            jQuery("#mw_add_call").show();
        });

        jQuery("#change_data").click(function () {
            jQuery("#close_mw").show();
            jQuery(".mw_container_cl").show();
            jQuery("#mw_cl_info").show('slow');
        });

        jQuery('#update_cl_info').click(function(){
            var phones = jQuery.map(jQuery('[name = "new_client_contacts[]"]'),function(value){
                    if(value.value != jQuery(value).data("old"))
                        return {phone: value.value,old_phone:jQuery(value).data("old")};
                }),
                emails = jQuery.map(jQuery('[name = "new_client_emails[]'),function(value){
                    if(value.value != jQuery(value).data("old"))
                        return {email: value.value,old_email:jQuery(value).data("old")};
                }),
                new_surname = jQuery('#new_surname').val(),
                new_cl_name = jQuery('#new_name').val(),
                new_patronymic = jQuery('#new_patronymic').val(),
                fio = '',
                new_name = '';
            if(!empty(new_surname)){
                fio += new_surname;
            }
            if(!empty(new_cl_name)){
                if(!empty(fio)){
                    fio += ' ' + new_cl_name;
                }
                else{
                    fio += new_cl_name;
                }
            }
            if(!empty(new_patronymic)){
                if(!empty(fio)){
                    fio += ' ' + new_patronymic;
                }
                else{
                    fio += new_patronymic;
                }
            }

            if(!empty(fio) && fio != client_name){
                new_name = fio;
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
        /*изменение адреса и даты замераы*/
        jQuery("#edit_address").click(function () {
            jQuery("#close_mw").show();
            jQuery(".mw_container_cl").show();
            jQuery("#mw_address").show();
        });

        jQuery("#save_address_cl").click(function(){

            var address = "",
                street = jQuery(".new_address_cl").val(),
                house = jQuery(".new_house_cl").val(),
                bdq = jQuery(".new_bdq_cl").val(),
                apartment = jQuery(".new_apartment_cl").val(),
                porch = jQuery(".new_porch_cl").val(),
                floor =jQuery(".new_floor_cl").val(),
                code = jQuery(".new_code_cl").val();
            if(house) address = street + ", дом: " + house;
            if(bdq) address += ", корпус: " + bdq;
            if(apartment) address += ", квартира: "+ apartment;
            if(porch) address += ", подъезд: " + porch;
            if(floor) address += ", этаж: " + floor;
            if(code) address += ", код: " + code;

            var data = {id:project_id,project_info:address};
            updateProejctData(data);
        });
        /*------*/

        jQuery("#edit_advt").click(function () {
            jQuery("#close_mw").show();
            jQuery(".mw_container_cl").show();
            jQuery("#mw_advt").show();
        });

        jQuery('#change_meaure_date').click(function () {
           jQuery('#close_mw').show();
           jQuery('#mw_change_measure').show();
           jQuery('.mw_container_cl').show();
        });

        jQuery('#save_measure_changes').click(function(){
            var data = {id:project_id,project_calculator:jQuery("#jform_project_gauger").val(), project_calculation_date:jQuery("#jform_project_new_calc_date").val()};
            updateProejctData(data);
        });
        jQuery("#update_discount").click(function () {
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=project.changeDiscount",
                data: {
                    project_id: project_id,
                    project_total: jQuery("#project_total span.sum")[0].innerText,
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

        jQuery("#add_phone").click(function () {
            jQuery('#client_phones').append('<div class="row"><div class="col-md-10"><input name="new_client_contacts[]" id="jform_client_contacts[]" class="form-control" placeholder="Телефон клиента"></div><div class="col-md-2"><button class="btn btn-danger del_phone" type="button"><i class="fas fa-trash-alt" aria-hidden="true"></i></button></div></div>');

            jQuery("[name = 'new_client_contacts[]']").mask('+7(999) 999-9999');
        });
        jQuery('#mw_cl_info').on('click', '.del_phone', function () {
            var div = jQuery(this).closest('.row');
            remove_tr(div, deleted_phones);
        });

        jQuery("#add_email").click(function () {
            jQuery('#client_emails').append('<div class="row"><div class="col-md-10"><input class="form-control" name="new_client_emails[]" id="jform_client_emails[]" placeholder="Email клиента"></div><div class="col-md-2"><button class="btn btn-danger del_email" type="button"><i class="fas fa-trash-alt" aria-hidden="true"></i></button></div></div>');
        });

        jQuery('#mw_cl_info').on('click', '.del_email', function () {
            var div = jQuery(this).closest('.row');
            remove_tr(div, deleted_emails);
        });

        function remove_tr(div, arr) {
            if (div.find("input").val()) {
                arr.push(div.find("input").val());
            }
            div.remove();
        }

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
            }),
                time = jQuery("#call_time").val(),
                important = jQuery('#important_call').is(':checked') ? 1 : 0;
            if (time == '') {
                time = '00:00';
            }
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=addCall",
                data: {
                    id_client: client_id,
                    date: date+' '+time,
                    comment: jQuery("#call_comment").val(),
                    important: important
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
    });

    function updateProejctData(data){
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
    }

</script>
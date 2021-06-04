<?php
/**
 * @version    CVS: 0.1.7
 * @package    Com_Gm_ceiling
 * @author     bubnov <2017 al.p.bubnov@gmail.com>
 * @copyright  2017 al.p.bubnov@gmail.com
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

$user = JFactory::getUser();
$userId = $user->get('id');

$comm_model = Gm_ceilingHelpersGm_ceiling::getModel('commercial_offer');
$comm_offers = $comm_model->getData("`manufacturer_id` = $user->dealer_id");

$clients_model = Gm_ceilingHelpersGm_ceiling::getModel('clients');
$labels = $clients_model->getClientsLabels($user->dealer_id);

$usersModel = Gm_ceilingHelpersGm_ceiling::getModel('users');
$providersGroups = $usersModel->getGroupsByParentGroup(48);
?>
<style type="text/css">
    .table {
        border-collapse: separate;
        border-spacing: 0 0.5em;
    }
    .b_margin{
        margin-bottom: 1em;
    }
    .left{
        text-align: left;
    }
</style>
<link href="/components/com_gm_ceiling/views/dealers/css/default.css" rel="stylesheet" type="text/css">
<link href="/templates/gantry/cleditor1_4_5/jquery.cleditor.css" rel="stylesheet" type="text/css">
<div class="row" style="margin-bottom: 5px;">
    <div class="col-md-4 col-xs-12">
        <a class="btn btn-large btn-primary"
           href="/index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage"
           id="back"><i class="fa fa-arrow-left" aria-hidden="true"></i> На главную</a>
    </div>
    <div class="col-md-5 col-xs-12 center">
        <h2>Поставщики</h2>
    </div>
    <div class="col-md-3 col-xs-12">
        <button type="button" id="new_dealer" class="btn btn-primary" style="width: 100%">
            <i class="fas fa-user-plus"></i> Создать поставщика
        </button>
    </div>
</div>
<div class="row" style="margin-bottom: 5px;">
    <div class="col-md-4 col-xs-12">
        <select class="wide cust-select" id="select_label">
            <option value="" selected>Ярлыки</option>
            <?php foreach ($labels as $label): ?>
                <option value="<?= $label->id; ?>"><?= $label->title; ?></option>
            <?php endforeach; ?>
        </select>
        <div class="nice-select wide" tabindex="0">
            <span class="current">Ярлыки</span>
            <ul class="list">
                <li class="option" data-value="" data-color="#ffffff" style="--rcolor:#ffffff" data-display="Ярлыки">
                    Ярлыки
                </li>
                <?php foreach ($labels as $label): ?>
                    <li class="option" data-value="<?= $label->id; ?>" data-color="#<?= $label->color_code; ?>"
                        style="--rcolor:#<?= $label->color_code; ?>"><?= $label->title; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col-md-5 col-xs-12">
        <div class="col-md-10 col-xs-10" style="padding-left: 0px;">
            <input type="text" class="form-control" id="name_find_dealer">
        </div>
        <div class="col-md-2 col-xs-2" style="padding: 0px;">
            <button type="button" id="find_dealer" class="btn btn-primary"><i class="fa fa-search" aria-hidden="true"></i>
            </button>
        </div>
    </div>
    <div class="col-md-3 col-xs-12">
        <a href="/index.php?option=com_gm_ceiling&view=providers&type=refused" class="btn btn-primary" style="width: 100%;font-size: 10pt;">
            <i class="fa fa-user-times" aria-hidden="true"></i> Отказавшиеся от сотрудничества
        </a>
    </div>
</div>

<div class="row" style="margin-bottom: 15px">
    <div class="col-md-3 col-xs-12" style="margin-bottom: 5px;">
        <select class="form-control" id="filter_status">
            <option value="">Выберите статус</option>
        </select>
    </div>
    <div class="col-md-3 col-xs-12" style="margin-bottom: 5px;">
        <select class="form-control" id="filter_manager">
            <option value="">Выберите менеджера</option>
        </select>
    </div>
    <div class="col-md-3 col-xs-12" style="margin-bottom: 5px;">
        <select class="form-control" id="filter_city">
            <option value="">Выберите город</option>
        </select>
    </div>
    <div class="col-md-3 col-xs-12" align="right">
        <button class="btn btn-primary" id="clear_filters" type="button">Сбросить фильтры</button>
    </div>
</div>
<div class="row right">
    <div class="col-md-12 col-xs-12">
        <label style="color: #414099;font-size: 14pt">Всего поставщиков:
            <b><span id="providers_count">0</span></b>
        </label>
    </div>
</div>
    <div class="row">
        <table class="table one-touch-view g_table" id="callbacksList">
            <thead>
            <tr>
                <th>
                    Имя
                </th>
                <th>
                    Телефоны
                </th>
                <th>
                    Город
                </th>

                <th>
                    Менеджер
                </th>
                <th>
                    Отказ
                </th>
            </tr>
            </thead>
            <tbody id="tbody_dealers">

            </tbody>
        </table>
    </div>
    <div class="modal_window_container" id="mv_container">
        <button type="button" class="close_btn" id="close"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i>
        </button>
        <div class="modal_window" id="modal_window_create">
            <div class="row">
                <h4>Создание нового поставщика</h4>
            </div>
            <div class="row b_margin">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <div class="row b_margin">
                        <div class="col-md-6 col-xs-12 left">
                            <label for="fio_dealer">ФИО/Название</label>
                        </div>
                        <div class="col-md-6 col-xs-12">
                            <input type="text" class="form-control" id="fio_provider" placeholder="ФИО">
                        </div>
                    </div>
                    <div class="row b_margin">
                        <div class="col-md-6 col-xs-12 left">
                            <label for="provider_contacts">Номер телефона</label>
                        </div>
                        <div class="col-md-6 col-xs-12">
                            <input type="text" class="form-control" id="provider_contacts" placeholder="Номер телефона">
                        </div>
                    </div>
                    <div class="row b_margin">
                        <div class="col-md-6 col-xs-12 left">
                            <label for="provider_email">Эл.почтв</label>
                        </div>
                        <div class="col-md-6 col-xs-12">
                            <input type="text" class="form-control" id="provider_email" placeholder="E-mail">
                        </div>
                    </div>
                    <div class="row b_margin">
                        <div class="col-md-6 col-xs-12 left">
                            <label for="provider_city">Город</label>
                        </div>
                        <div class="col-md-6 col-xs-12">
                            <input type="text" class="form-control" id="provider_city" placeholder="Город">
                        </div>
                    </div>
                    <div class="row b_margin">
                        <div class="col-md-12 col-xs-12 center">
                            <label><b>Выберите тип:</b></label>
                        </div>
                    </div>
                    <?php foreach ($providersGroups as $group){?>
                        <div class="row b_margin">
                            <div class="col-md-3 col-xs-0"></div>
                            <div class="col-md-6 col-xs-12" style="text-align: left">
                                <input type="checkbox" id="<?php echo $group->id?>" data-id = "<?php echo $group->id?>" class="inp-cbx groups"  style="display: none">
                                <label for="<?php echo $group->id?>" class="cbx">
                                    <span>
                                        <svg width="12px" height="10px" viewBox="0 0 12 10">
                                            <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                        </svg>
                                    </span>
                                    <span><?php echo $group->title;?></span>
                                </label>
                            </div>
                            <div class="col-md-3 col-xs-0"></div>
                        </div>
                    <?php }?>
                </div>
                <div class="col-md-3"></div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <button type="button" id="save_provider" class="btn btn-primary">Сохранить</button>
                </div>
            </div>

        </div>

        <div class="modal_window" id="mw_calls">
            <div class="row center" >
                <table id="calls_list" class="table table-stripped table_cashbox">

                </table>
            </div>
        </div>
    </div>

<script src="/templates/gantry/cleditor1_4_5/jquery.cleditor.js"></script>

<script type="text/javascript">
    var $ = jQuery,
        limit = 0,
        select_size = 10;
    inProgress = false,
        managers = [],
        cities = [],
        statuses = [],
        userId = '<?php echo $userId;?>'
    dealers_data = [];
    tbody_dealers = document.getElementById('tbody_dealers');


    function getDataForSelects(task, data, arr) {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=" + task,
            data: data,
            success: function (data) {
                jQuery.each(data, function (index, value) {
                    arr.push(value);
                });
            },
            dataType: "json",
            async: false,
            timeout: 10000,
            error: function (data) {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка. Сервер не отвечает"
                });
            }
        });
    }

    function printProviders(providersData) {
        console.log(providersData);
        var html = '', color = '',
            name_find_dealer = document.getElementById('name_find_dealer').value,
            reg_name_find_dealer = new RegExp(name_find_dealer, "ig"),
            filter_manager = document.getElementById('filter_manager').value,
            filter_city = document.getElementById('filter_city').value,
            calls_history, last_call, data_i;
        for (var i = 0; i < providersData.length; i++) {
            data_i = providersData[i];
            last_call = '-';
            if (!empty(data_i.calls_history)) {
                calls_history = JSON.parse(data_i.calls_history);
                last_call = calls_history[0].type + '<br> ' + calls_history[0].date;
                console.log(last_call);
            }

            for (var key in data_i) {
                if (data_i[key] == null) {
                    data_i[key] = '-';
                }
            }

            html += '<tr style="background: #' + data_i.color_code + '55;" data-id = "' + data_i.id + '" data-dealer_id = "' + data_i.dealer_id + '" data-href="/index.php?option=com_gm_ceiling&view=clientcard&type=provider&id=' + data_i.id + '">';
            html += '<td>' + data_i.client_name + '</td>';
            html += '<td>' + data_i.client_contacts + '</td>';
            html += '<td>' + data_i.city + '</td>';
            html += '<td>' + data_i.manager_name + '</td>';
            html += '<td><button class="btn btn-danger refuse_coop" type="button"><i class="fa fa-ban" aria-hidden="true"></i></button></td>'
            html += '</tr>';
            tbody_dealers.innerHTML += html;

            html = '';
        }
    }

    function get_dealer_index(id) {
        var rows = jQuery("#callbacksList tr");
        var result = 0;
        for (var i = rows.length - 1; i >= 0; i--) {
            if (jQuery(rows[i]).data('id') == id) {
                return rows[i].rowIndex;
            }
        }
    }

    function getDealersCount(city, manager, status, client) {
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=users.getUsersByFilter",
            data: {
                filter_city: city,
                filter_manager: manager,
                filter_status: status,
                client: client
            },
            success: function (data) {
                jQuery("#providers_count").text(data.length);
            },
            dataType: "json",
            async: true,
            timeout: 10000,
            error: function (data) {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка. Сервер не отвечает"
                });
            }
        });
    }

    function getProviders() {
        var city = jQuery("#filter_city").val(),
            manager = jQuery("#filter_manager").val(),
            status = jQuery("#filter_status").val(),
            client = jQuery("#name_find_dealer").val(),
            date_from = jQuery('#date_from').val(),
            date_to = jQuery('#date_to').val();
        getDealersCount(city, manager, status, client, date_from, date_to);
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=users.getUsersByFilter",
            data: {
                filter_city: city,
                filter_manager: manager,
                filter_status: status,
                limit: limit,
                select_size: select_size,
                client: client,
                label_id: jQuery("#select_label").val(),

            },
            beforeSend: function () {
                inProgress = true;
            },
            success: function (data) {
                if(limit == 0){
                    dealers_data = data;
                }
                else{
                    dealers_data  = dealers_data.concat(data);
                }
                printProviders(data);
                if (data.length >= 10) {
                    inProgress = false;
                }
                limit += 10;
            },
            dataType: "json",
            async: false,
            timeout: 10000,
            error: function (data) {
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка. Сервер не отвечает"
                });
            }
        });
    }

    function fillSelect(select_id, value, text) {
        jQuery(select_id)
            .append(jQuery("<option></option>")
                .attr("value", value)
                .text(text));
    }

    jQuery(document).ready(function () {
        jQuery('#provider_contacts').mask('+7 (999) 999-99-99');
        jQuery('#select_label').niceSelect();
        jQuery("#select_label").change(function () {
            var color = (jQuery(".option.selected").data("color"));
            jQuery('.nice-select.wide')[0].style.setProperty('--rcolor', color);
            tbody_dealers.innerHTML = '';
            limit = 0;
            getProviders();
        });

        var dealer_to_find;
        getDataForSelects('users.getUserByGroup', {group: 47}, managers);
        getDataForSelects('dealer.select_dealers_city', {}, cities);
        getDataForSelects('users.getGroupsByParentGroup', {parent:48}, statuses);

        if (managers.length) {
            for (var i = 0; i < managers.length; i++) {
                fillSelect("#filter_manager", managers[i].id, managers[i].name);
            }
        }
        if (cities.length) {
            for (var i = 0; i < cities.length; i++) {
                fillSelect("#filter_city", cities[i].city, cities[i].city);
            }
        }
        if (statuses.length) {
            for (var i = 0; i < statuses.length; i++) {
                fillSelect("#filter_status", statuses[i].id, statuses[i].title);
            }
        }
        getProviders();
        if (dealer_to_find) {
            var row_index = get_dealer_index(dealer_to_find);
            if (row_index) {
                var need_row = jQuery("#callbacksList").find('tr').eq(row_index);
                jQuery('html, body').animate({
                    scrollTop: jQuery(need_row).offset().top
                }, 2000);
            }
            limit = +select_size;
            select_size = 10;
        }
        jQuery("#clear_filters").click(function () {
            jQuery("#filter_manager").val("");
            jQuery("#filter_city").val("");
            jQuery("#filter_status").val("");
            jQuery("#name_find_dealer").val("");
            limit = 0;
            select_size = 10;
            tbody_dealers.innerHTML = '';
            getProviders();
        });

        $(window).scroll(function () {
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 200 && !inProgress) {
                getProviders();
            }
        });
        var server_name = '<?php echo $server_name?>';
        $(window).resize();
        var HelpMessageSpan = $("<span></span>"),
            HelpMessage = $(".HelpMessage");

        $.each(HelpMessage, function (i, v) {
            v = $(v);
            var t = HelpMessageSpan.clone().addClass("HelpMessageSpan").text(v.attr("title"));
            v.append(t);
        });

        $("#dealer_price").click(function () {
            var _this = $(this),
                sort = _this.data("sort");
            sort = (sort === "desc") ? "asc" : "desc";
            _this.data("sort", sort);
            showDealers();
        });

        jQuery('#dealer_contacts').mask('+7(999) 999-9999');

        var comm_offers = JSON.parse('<?= json_encode($comm_offers); ?>');

        var text_cleditor = jQuery("#email_text").cleditor();

        jQuery("#new_dealer").click(function () {
            jQuery("#close").show();
            jQuery("#mv_container").show();
            jQuery("#modal_window_create").show("slow");
        });

        show_kp_in_select();

        function show_kp_in_select() {
            jQuery.each(comm_offers, function () {
                jQuery('#select_kp')
                    .append(jQuery("<option></option>")
                        .attr("value", this.id)
                        .text(this.name));
            });
        }

        jQuery("#send_to_all").click(function () {
            jQuery("#close").show();
            jQuery("#mv_container").show();
            jQuery("#modal_window_kp").show("slow");
        });

        jQuery(document).mousedown(function (e) {
            var target = e.target;
            if (target === null) {
                return;
            }
            // цикл двигается вверх от target к родителям до table
            while (target.tagName != 'BODY') {
                var div = jQuery("#modal_window_create");
                var div2 = jQuery("#modal_window_kp");
                var div3 = jQuery("#modal_window_kp_editor");
                if (div.is(target) || div2.is(target) || div3.is(target) ||
                    div.has(target).length != 0 || div2.has(target).length != 0 || div3.has(target).length != 0) {
                    if (target.id != undefined) {
                        if (target.id == 'save_provider') {
                            var providerGroupsChbx = jQuery('.groups:checked'),
                                providerGroups = [];
                            if(empty(jQuery('#fio_provider').val())){
                                Noty("error","Не заполенено ФИО/Название");
                                jQuery('#fio_provider').css('border-color','red');
                                return 0;
                            }
                            jQuery('#fio_provider').css('border-color','');
                            if(empty( jQuery('#provider_contacts').val())){
                                Noty("error","Не заполенен номер телефона");
                                jQuery('#provider_contacts').css('border-color','red');
                                return 0;
                            }
                            jQuery('#provider_contacts').css('border-color','');
                            if(empty( jQuery('#provider_city').val())){
                                Noty("error","Не заполенен город");
                                jQuery('#provider_city').css('border-color','red');
                                return 0;
                            }
                            jQuery('#provider_city').css('border-color','');
                            if(providerGroupsChbx.length == 0){
                                Noty("error","Не выбран тип");
                                return 0;
                            }
                            jQuery.each(providerGroupsChbx,function(n,c){
                                var group = jQuery(c).data('id');
                                providerGroups.push(group);
                            });
                            jQuery.ajax({
                                type: 'POST',
                                url: "index.php?option=com_gm_ceiling&task=users.createUser",
                                data: {
                                    name: jQuery('#fio_provider').val().replace(/[^\dA-Za-zА-ЯЁа-яё ]/gi, ''),
                                    phone: jQuery('#provider_contacts').val(),
                                    email: jQuery("#provider_email").val(),
                                    city: jQuery('#provider_city').val(),
                                    with_client: 1,
                                    groups: providerGroups
                                },
                                success: function (data) {
                                    if (data == 'client_found') {
                                        var n = noty({
                                            timeout: 2000,
                                            theme: 'relax',
                                            layout: 'center',
                                            maxVisible: 5,
                                            type: "error",
                                            text: "Пользователь с таким номером существует!"
                                        });
                                    }  else {
                                        var n = noty({
                                            timeout: 2000,
                                            theme: 'relax',
                                            layout: 'center',
                                            maxVisible: 5,
                                            type: "success",
                                            text: "Поставщик добавлен!"
                                        });
                                        setTimeout(function () {
                                            location.reload();
                                        },2500);
                                    }
                                },
                                dataType: "text",
                                async: false,
                                timeout: 10000,
                                error: function (data) {
                                    var n = noty({
                                        timeout: 2000,
                                        theme: 'relax',
                                        layout: 'center',
                                        maxVisible: 5,
                                        type: "error",
                                        text: data
                                    });
                                }
                            });
                        }

                        if (target.id == 'send_kp') {
                            var dealer_ids = [];
                            jQuery.each(jQuery('[name="checkbox_dealer[]"]:checked'), function () {
                                dealer_ids.push(jQuery(this).data('id'));
                            });
                            if (dealer_ids.length > 0) {
                                jQuery.ajax({
                                    type: 'POST',
                                    url: "index.php?option=com_gm_ceiling&task=dealer.send_out_to_dealers",
                                    data: {
                                        dealer_ids: dealer_ids,
                                        comm_id: jQuery("#select_kp").val()
                                    },
                                    success: function (data) {
                                        var n = noty({
                                            timeout: 2000,
                                            theme: 'relax',
                                            layout: 'center',
                                            maxVisible: 5,
                                            type: "success",
                                            text: "Письма отправлены"
                                        });
                                    },
                                    dataType: "text",
                                    async: false,
                                    timeout: 10000,
                                    error: function (data) {
                                        var n = noty({
                                            timeout: 2000,
                                            theme: 'relax',
                                            layout: 'center',
                                            maxVisible: 5,
                                            type: "error",
                                            text: "Ошибка. Сервер не отвечает"
                                        });
                                    }
                                });
                            } else {
                                alert("Не отмеченны диллеры!");
                            }
                            return;
                        }

                        if (target.id == 'add_kp') {
                            text_cleditor[0].$frame[0].contentWindow.document.body.innerHTML = '<table cols=2  cellpadding="20px"style="width: 100%; border: 0px solid; color: #414099; font-family: Cuprum, Calibri; font-size: 16px;">' +
                                '<tr><td style="vertical-align:middle;"><a href="http://' + server_name + '/">' +
                                '<img src="http://calc.gm-vrn.ru/images/gm-logo.png" alt="Логотип" style="padding-top: 15px; height: 70px; width: auto;">' +
                                '</a></td><td><div style="vertical-align:middle; padding-right: 50px; padding-top: 7px; text-align: right; line-height: 0.5;">' +
                                '<p style="margin: 10px;">Тел.: +7(473)212-34-01</p><p style="margin: 10px;">Почта: gm-partner@mail.ru</p><p style="margin: 10px;">Адрес: г. Воронеж, Проспект Труда, д. 48, литер. Е-Е2</p></div></td></tr></table>';
                            jQuery("#modal_window_kp_editor").css('width', '80%');
                            jQuery("#modal_window_kp_editor").css('margin-left', '10%');
                            jQuery("#close").show();
                            jQuery("#mv_container").show();
                            jQuery("#modal_window_kp").hide();
                            jQuery("#modal_window_kp_editor").show("slow");
                            return;
                        }

                        if (target.id == 'select_kp') {
                            return;
                        }

                        if (target.id == 'save_kp') {
                            var text = btoa(escape(text_cleditor[0].$frame[0].contentWindow.document.body.innerHTML));
                            var reg = /^\s+$/gi;
                            var subj = jQuery("#email_subj").val();
                            var name = jQuery("#kp_name").val();
                            if (reg.test(text) || reg.test(subj) || reg.test(name) ||
                                text === "" || subj === "" || name === "") {
                                alert("Заполните все поля!");
                            } else {
                                jQuery.ajax({
                                    type: 'POST',
                                    url: "index.php?option=com_gm_ceiling&task=saveCommercialOffer",
                                    data: {
                                        text: text,
                                        subj: subj,
                                        name: name
                                    },
                                    success: function (data) {
                                        var n = noty({
                                            timeout: 2000,
                                            theme: 'relax',
                                            layout: 'center',
                                            maxVisible: 5,
                                            type: "success",
                                            text: "КП Сохраненно"
                                        });
                                        jQuery('#select_kp')
                                            .append(jQuery("<option></option>")
                                                .attr("value", data)
                                                .text(name));
                                        jQuery("#modal_window_kp_editor").hide();
                                        jQuery("#modal_window_kp").show();
                                        jQuery("#email_subj").val("");
                                        jQuery("#kp_name").val("");
                                        text_cleditor[0].clear();

                                    },
                                    dataType: "text",
                                    async: false,
                                    timeout: 10000,
                                    error: function (data) {
                                        var n = noty({
                                            timeout: 2000,
                                            theme: 'relax',
                                            layout: 'center',
                                            maxVisible: 5,
                                            type: "error",
                                            text: "Ошибка. Сервер не отвечает"
                                        });
                                    }
                                });
                            }
                            return;
                        }
                    }
                    return;
                }

                if (target.className != undefined) {
                    if (target.className == 'td_checkbox') {
                        return;
                    }
                    if (target.classList.contains("refuse_coop")) {
                        noty({
                            theme: 'relax',
                            layout: 'center',
                            timeout: false,
                            type: "info",
                            text: "Перевести дилера в отказ от сотрудничества?",
                            buttons: [
                                {
                                    addClass: 'btn btn-primary', text: 'Да', onClick: function ($noty) {
                                        var row = jQuery(target).closest('td').parent();
                                        var user_id = row.data('dealer_id');
                                        jQuery.ajax({
                                            url: "index.php?option=com_gm_ceiling&task=userRefuseToCooperate",
                                            data: {
                                                user_id: user_id
                                            },
                                            dataType: "json",
                                            async: false,
                                            success: function (data) {
                                                var n = noty({
                                                    timeout: 2000,
                                                    theme: 'relax',
                                                    layout: 'center',
                                                    maxVisible: 5,
                                                    type: "success",
                                                    text: "Переведен в отказ от сотрудничества"
                                                });
                                                row.hide();
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
                                    addClass: 'btn btn-primary', text: 'Отмена', onClick: function ($noty) {
                                        $noty.close();
                                    }
                                }
                            ]
                        });
                        return;
                    }
                    if (target.className == 'calls_td') {
                        var id = jQuery(target).closest('tr').data('id'),
                            dealer = dealers_data.find(function (el) {
                                console.log(el);
                                return el.id == id;
                            });
                        if(!empty(dealer.calls_history)){
                            var calls_history = JSON.parse(dealer.calls_history);
                            jQuery.each(calls_history,function(i,rec){
                                jQuery('#calls_list').append('<tr><td>'+rec.type+'</td><td>'+rec.date+'</td></tr>');
                            });
                        }
                        jQuery("#close").show();
                        jQuery("#mv_container").show();
                        jQuery("#mw_calls").show("slow");
                        return;
                    }
                }

                if (target.tagName == 'TR') {
                    var filter_manager = jQuery("#filter_manager").val(),
                        filter_city = jQuery("#filter_city").val(),
                        filter_status = jQuery("#filter_status").val(),
                        client = jQuery("#name_find_dealer").val();
                    if (jQuery(target).data('href') != undefined) {
                        if (e.which == 2) {
                            window.open(jQuery(target).data('href'));
                        } else {
                            jQuery.ajax({
                                type: 'POST',
                                url: "index.php?option=com_gm_ceiling&task=dealer.save_data_to_session",
                                data: {
                                    user: userId,
                                    filter_city: filter_city,
                                    filter_manager: filter_manager,
                                    filter_status: filter_status,
                                    limit: limit,
                                    client: client,
                                    dealer_id: jQuery(target).data('id'),
                                },
                                success: function (data) {
                                    document.location.href = jQuery(target).data('href');
                                },
                                dataType: "json",
                                async: false,
                                timeout: 10000,
                                error: function (data) {
                                    var n = noty({
                                        timeout: 2000,
                                        theme: 'relax',
                                        layout: 'center',
                                        maxVisible: 5,
                                        type: "error",
                                        text: "Ошибка. Сервер не отвечает"
                                    });
                                }
                            });

                        }
                    }
                    return;
                }

                if (target.id != undefined) {
                    if (target.id == 'close' || target.id == 'mv_container') {
                        jQuery("#close").hide();
                        jQuery("#mv_container").hide();
                        jQuery("#modal_window_create").hide();
                        jQuery("#modal_window_kp").hide();
                        jQuery("#modal_window_kp_editor").hide();
                        return;
                    }

                }

                target = target.parentNode;
                if (target === null) {
                    return;
                }
            }
        });


        jQuery('#find_dealer').click(function () {
            tbody_dealers.innerHTML = '';
            limit = 0;
            getProviders();
        });
        jQuery('#filter_manager').change(function () {
            tbody_dealers.innerHTML = '';
            limit = 0;
            getProviders();
        });
        jQuery('#filter_city').change(function () {
            tbody_dealers.innerHTML = '';
            limit = 0;
            getProviders();
        });
        jQuery('#filter_status').change(function () {
            tbody_dealers.innerHTML = '';
            limit = 0;
            getProviders();
        });

        jQuery('#name_find_dealer').focus = function () {
            tbody_dealers.innerHTML = '';
        };

        document.onkeydown = function (e) {
            if (e.keyCode === 13 && jQuery('#name_find_dealer').is(":focus")) {
                jQuery('#name_find_dealer').focus();
                tbody_dealers.innerHTML = '';
                limit = 0;
                getProviders();
            }
        };

        jQuery('.choose_date').change(function () {
            limit = 0;
            tbody_dealers.innerHTML = '';
            getProviders();
        });
    });




    function Noty(status = "error", message = "Сервер не отвечает, попробуйте снова!", time = 2000) {
        noty({
            theme: 'relax',
            layout: 'center',
            timeout: time,
            type: status,
            text: message
        });
    }
</script>
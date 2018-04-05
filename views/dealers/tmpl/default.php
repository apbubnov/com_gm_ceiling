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

$user       = JFactory::getUser();
$userId     = $user->get('id');

$users_model = Gm_ceilingHelpersGm_ceiling::getModel('users');
$recoil_map_model = Gm_ceilingHelpersGm_ceiling::getModel('recoil_map_project');

$comm_model = Gm_ceilingHelpersGm_ceiling::getModel('commercial_offer');
$comm_offers = $comm_model->getData("`manufacturer_id` = $user->dealer_id");

?>
<link href="/components/com_gm_ceiling/views/dealers/css/default.css" rel="stylesheet" type="text/css">
<link href="/templates/gantry/cleditor1_4_5/jquery.cleditor.css" rel="stylesheet" type="text/css">
    <a class="btn btn-large btn-primary"
       href="/index.php?option=com_gm_ceiling&view=mainpage&type=gmmanagermainpage"
       id="back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Назад</a>
    <h2 class="center">Дилеры</h2>
    <div style="display:inline-block; width: 48%; text-align: left;">
        <button type="button" id="new_dealer" class="btn btn-primary">Создать дилера</button>
    </div>
    <div style="display:inline-block; width: 48%; text-align: left;">
        <button type="button" id="send_to_all" class="btn btn-primary HelpMessage" title="Отправить на email"><i class="fa fa-envelope"></i></button>
        <button type="button" class="btn btn-primary HelpMessage" onclick="send_refresh_price()" title="Обновить цену"><i class="fa fa-refresh"></i></button>
        <button type="button" class="btn btn-primary HelpMessage" onclick="send_clear_price()" title="Очистить корректировки"><i class="fa fa-eraser"></i></button>
    </div>
    <div style="display:inline-block; width: 48%; text-align: left;">
        <input type="text" id="name_find_dealer">
        <button type="button" id="find_dealer" class="btn btn-primary"><i class="fa fa-search" aria-hidden="true"></i></button>
        <select class="input-gm" id="filter_manager">
            <option value="">Выберите менеджера</option>
        </select>
        <select class="input-gm" id="filter_city">
            <option value="">Выберите город</option>
        </select>
    </div>
    <br>
    <table class="table one-touch-view" id="callbacksList">
        <thead>
        <tr>
            <th>
                <input type="checkbox" name="checkbox_all_dealers" id="checkbox_all_dealers">
            </th>
            <th>
               Имя
            </th>
            <th id="dealer_price" data-sort="">
                Минимальная цена<br>полотно / компонент
            </th>
            <th>
               Телефоны
            </th>
            <th>
                Город
            </th>
            <th>
               Дата регистрации
            </th>
            <th>
                Менеджер
            </th>
            <th>

            </th>
        </tr>
        </thead>
        <tbody id="tbody_dealers">

        </tbody>
    </table>
    <div class="modal_window_container" id="mv_container">
        <button type="button" class="close_btn" id="close"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
        <div class="modal_window" id="modal_window_create">
                <p><strong>Создание нового дилера</strong></p>
                <p>ФИО:</p>
                <p><input type="text" id="fio_dealer" placeholder = "ФИО"></p>
                <p>Номер телефона:</p>
                <p><input type="text" id="dealer_contacts"></p>
                <p>Город</p>
                <p><input type="text" id = "dealer_city" placeholder = "Город"></p>
                <p><button type="submit" id="save_dealer" class="btn btn-primary">ОК</button></p>
        </div>
        <div class="modal_window" id="modal_window_kp_editor">
            <p>Название КП</p>
            <p><input type ="text" class="input-gm" id="kp_name"></p>
            <p>Тема</p>
            <p><input type ="text" class="input-gm" id="email_subj"></p>
            <p>Текст письма:</p>
            <p><textarea rows = "10" class ="textarea-gm" id="email_text"></textarea></p>
            <p><button type="button" id="save_kp" class="btn btn-primary">Сохранить</button></p>
        </div>
        <div class="modal_window" id="modal_window_kp">
            <p><select class="input-gm" id="select_kp"></select></p>
            <p><button type="button" id="add_kp" class="btn btn-primary">+</button>
            <button type="button" id="send_kp" class="btn btn-primary">Отправить</button></p>
        </div>
    </div>

<script src="/templates/gantry/cleditor1_4_5/jquery.cleditor.js"></script>

<script>
    var $ = jQuery,
        managers = {},
        cities = {};

    jQuery(document).ready(function()
    {
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
            sort = (sort === "desc")?"asc":"desc";
            _this.data("sort", sort);
            showDealers();
        });

        jQuery('#dealer_contacts').mask('+7(999) 999-9999');

        var comm_offers = JSON.parse('<?= json_encode($comm_offers); ?>');

        var text_cleditor = jQuery("#email_text").cleditor();

        jQuery("#new_dealer").click(function(){
            jQuery("#close").show();
            jQuery("#mv_container").show();
            jQuery("#modal_window_create").show("slow");
        });

        show_kp_in_select();

        function show_kp_in_select()
        {
            jQuery.each(comm_offers, function() {
                jQuery('#select_kp')
                 .append(jQuery("<option></option>")
                            .attr("value", this.id)
                            .text(this.name)); 
            });
        }
        
        jQuery("#send_to_all").click(function()
        {
            jQuery("#close").show();
            jQuery("#mv_container").show();
            jQuery("#modal_window_kp").show("slow");
        });

        jQuery(document).click(function(e){
            var target = e.target;
            //console.log(e.target.tagName);
            // цикл двигается вверх от target к родителям до table
            while (target.tagName != 'BODY')
            {
                var div = jQuery("#modal_window_create");
                var div2 = jQuery("#modal_window_kp");
                var div3 = jQuery("#modal_window_kp_editor");
                if (div.is(target) || div2.is(target) ||  div3.is(target) ||
                    div.has(target).length != 0 || div2.has(target).length != 0 || div3.has(target).length != 0)
                {
                    //console.log(target);
                    if (target.id != undefined)
                    {
                        if (target.id == 'save_dealer')
                        {
                            jQuery.ajax({
                                type: 'POST',
                                url: "index.php?option=com_gm_ceiling&task=dealer.create_dealer",
                                data: {
                                    fio: document.getElementById('fio_dealer').value,
                                    phone: document.getElementById('dealer_contacts').value,
                                    city: document.getElementById('dealer_city').value
                                },
                                success: function(data){
                                    if (data == 'client_found')
                                    {
                                        var n = noty({
                                            timeout: 2000,
                                            theme: 'relax',
                                            layout: 'center',
                                            maxVisible: 5,
                                            type: "error",
                                            text: "Клиент с таким номером существует!"
                                        });
                                    }
                                    else
                                    {
                                        location.reload();
                                    }
                                },
                                dataType: "text",
                                async: false,
                                timeout: 10000,
                                error: function(data){
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

                        if (target.id == 'send_kp')
                        {
                            var dealer_ids = [];
                            jQuery.each(jQuery('[name="checkbox_dealer[]"]:checked'), function() {
                                dealer_ids.push(jQuery(this).data('id'));
                            });
                            console.log(dealer_ids);
                            if (dealer_ids.length > 0)
                            {
                                jQuery.ajax({
                                    type: 'POST',
                                    url: "index.php?option=com_gm_ceiling&task=dealer.send_out_to_dealers",
                                    data: {
                                       dealer_ids: dealer_ids,
                                       comm_id: jQuery("#select_kp").val()
                                    },
                                    success: function(data){
                                        console.log(data);
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
                                    error: function(data){
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
                            else
                            {
                                alert("Не отмеченны диллеры!");
                            }
                            return;
                        }

                        if (target.id == 'add_kp')
                        {
                            text_cleditor[0].$frame[0].contentWindow.document.body.innerHTML = '<table cols=2  cellpadding="20px"style="width: 100%; border: 0px solid; color: #414099; font-family: Cuprum, Calibri; font-size: 16px;">' + 
                            '<tr><td style="vertical-align:middle;"><a href="test1.gm-vrn.ru/">' + 
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

                        if (target.id == 'select_kp')
                        {
                            return;
                        }

                        if (target.id == 'save_kp')
                        {
                            console.log(text_cleditor[0].$frame[0].contentWindow.document.body.innerHTML);
                            var text = btoa(escape(text_cleditor[0].$frame[0].contentWindow.document.body.innerHTML));
                            var reg = /^\s+$/gi;
                            var subj = jQuery("#email_subj").val();
                            var name = jQuery("#kp_name").val();
                            if (reg.test(text) || reg.test(subj) || reg.test(name) ||
                                text === "" || subj === "" || name === "")
                            {
                                alert("Заполните все поля!");
                            }
                            else
                            {
                                jQuery.ajax({
                                    type: 'POST',
                                    url: "index.php?option=com_gm_ceiling&task=saveCommercialOffer",
                                    data: {
                                       text: text,
                                       subj: subj,
                                       name: name
                                    },
                                    success: function(data){
                                        console.log(data);
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
                                    error: function(data){
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

                if (target.className != undefined)
                {
                    if (target.className == 'td_checkbox')
                    {
                        return;
                    }
                }

                if (target.tagName == 'TR')
                {
                    if(jQuery(target).data('href') != undefined){
                        document.location.href = jQuery(target).data('href');
                    }
                    return;
                }

                if (target.id != undefined)
                {
                    if (target.id == 'close' || target.id == 'mv_container')
                    {
                        jQuery("#close").hide();
                        jQuery("#mv_container").hide();
                        jQuery("#modal_window_create").hide();
                        jQuery("#modal_window_kp").hide();
                        jQuery("#modal_window_kp_editor").hide();
                        return;
                    }

                    if (target.id == 'checkbox_all_dealers')
                    {
                        if (target.checked)
                        {
                            jQuery.each(jQuery('[name="checkbox_dealer[]"]'), function() {
                                this.checked = true;
                            });
                        }
                        else
                        {
                            jQuery.each(jQuery('[name="checkbox_dealer[]"]'), function() {
                                this.checked = false;
                            });
                        }
                        return;
                    }
                }

                target = target.parentNode;
            }
        });

        showDealers();
        document.getElementById('find_dealer').onclick = showDealers;
        document.getElementById('filter_manager').onchange = showDealers;
        document.getElementById('filter_city').onchange = showDealers;
    });

    var Ajax = "/index.php?option=com_gm_ceiling&task=";
    function send_refresh_price() {
        var dealers = $("input[name = \"checkbox_dealer[]\"]:checked");

        $.each(dealers, function (i, v) {
            var v = $(v),
                id = v.data("dealer_id"),
                data = {Price: "*", dealer: id};

            send_ajax_price(data);
        });
        showDealers();
        Noty("success", "Успешно", 1000);
    }
    
    function send_clear_price() {
        var dealers = $("input[name = \"checkbox_dealer[]\"]:checked");

        $.each(dealers, function (i, v) {
            var v = $(v),
                id = v.data("dealer_id"),
                data = {Price: "#.", dealer: id};

            send_ajax_price(data);
        });
        showDealers();
        Noty("success", "Успешно", 1000);
    }

    function send_ajax_price(data) {
        jQuery.ajax({
            type: 'POST',
            url: Ajax + "components.UpdatePrice",
            data: data,
            cache: false,
            async: false,
            dataType: "json",
            timeout: 5000,
            error: Noty
        });

        jQuery.ajax({
            type: 'POST',
            url: Ajax + "canvases.UpdatePrice",
            data: data,
            cache: false,
            async: false,
            dataType: "json",
            timeout: 5000,
            error: Noty
        });
    }

    function Noty(status = "error", message = "Сервер не отвечает, попробуйте снова!", time = 2000) {
        noty({
            theme: 'relax',
            layout: 'center',
            timeout: time,
            type: status,
            text: message
        });
    }

    function showDealers(){
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=findOldClients",
            data: {
                fio: document.getElementById('name_find_dealer').value,
                flag: 'dealers',
                manager_id: document.getElementById('filter_manager').value,
                city: document.getElementById('filter_city').value,
                dealer_price_sort: $("#dealer_price").data("sort")
            },
            success: function(data){
                console.log(data);
                var tbody = document.getElementById('tbody_dealers');
                tbody.innerHTML = '';
                var html = '';
                var color;
                for(var i in data)
                {
                    for(var key in data[i])
                    {
                        if (data[i][key] == null)
                        {
                            data[i][key] = '-';
                        }
                    }
                    if(data[i].kp_cnt + data[i].cmnt_cnt + data[i].inst_cnt == 0 )
                    {
                        color = "bgcolor=\"#d3d3f9\"";
                    }
                    else
                    {
                        color = '';
                    }
                    html += '<tr ' + color + ' data-href="/index.php?option=com_gm_ceiling&view=clientcard&type=dealer&id=' + data[i].id + '">';
                    html += '<td class="td_checkbox"><input type="checkbox" name="checkbox_dealer[]" data-id="' + data[i].id + '" data-dealer_id="' + data[i].dealer_id + '"></td>';
                    html += '<td>' + data[i].client_name + '</td>';
                    html += '<td>' + data[i].min_canvas_price + ' руб. / ' + data[i].min_component_price + ' руб.</td>';
                    html += '<td>' + data[i].client_contacts + '</td>';
                    html += '<td>' + data[i].city + '</td>';
                    html += '<td>' + data[i].created + '</td>';
                    html += '<td>' + data[i].manager_name + '</td>';
                    if(data[i].dealer_type == 6){
                        html += '<td><font face="webdings"> @ </font></td>';
                    }
                    else{
                        html += '<td></td>';
                    }
                    html += '</tr>';
                }
                tbody.innerHTML = html;
                html = '';
                if (Object.keys(managers).length === 0)
                {
                    for(var i in data)
                    {
                        if (!(data[i].manager_id in managers) && data[i].manager_id != '-')
                        {
                            managers[data[i].manager_id] = data[i].manager_name;
                        }
                    }
                    jQuery.each(managers, function(key, value) {
                        jQuery('#filter_manager')
                            .append(jQuery("<option></option>")
                                .attr("value",key)
                                .text(value));
                    });
                }
                if (Object.keys(cities).length === 0)
                {
                    for(var i in data)
                    {
                        if (!(data[i].city in cities) && data[i].city != '-')
                        {
                            cities[data[i].city] = data[i].city;
                        }
                    }
                    jQuery.each(cities, function(key, value) {
                        jQuery('#filter_city')
                            .append(jQuery("<option></option>")
                                .attr("value",key)
                                .text(value));
                    });
                }
            },
            dataType: "json",
            async: false,
            timeout: 20000,
            error: function(data){
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
</script>
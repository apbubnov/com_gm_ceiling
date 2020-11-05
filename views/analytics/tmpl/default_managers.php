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

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$model_calls = Gm_ceilingHelpersGm_ceiling::getModel('callback');

$user = JFactory::getUser();
$dealerId = $user->dealer_id;
$outcoming_bad = json_encode($model_calls->selectCallHistoryByStatus(1, $user->dealer_id));
$outcoming_good = json_encode($model_calls->selectCallHistoryByStatus(2, $user->dealer_id));
$incoming = json_encode($model_calls->selectCallHistoryByStatus(3, $user->dealer_id));
$presentation = json_encode($model_calls->selectCallHistoryByStatus(4, $user->dealer_id));
$lid = json_encode($model_calls->selectCallHistoryByStatus(5, $user->dealer_id));
$analyticModel = Gm_ceilingHelpersGm_ceiling::getModel('analytic_new');
$today = date('Y-m-d');
$managersUniqueCalls = $analyticModel->getManagersAnalytic($today,$today);

?>
<style type="text/css">
    .small_table {
        cursor: pointer;
    }
    .small_table tbody tr:hover {
        background: #ddeeff;
    }
</style>
<div class="row">
    <div class="col-md-2">
        <?=parent::getButtonBack(); ?>
    </div>
    <div class="col-md-3">
        <button class="btn btn-primary" id="add_manager">Добавить менеджера</button>
    </div>
</div>
<h4>Звонки</h4>
<div class="analitic-actions">
    Выбрать с <input type="date"  class="choose_date" id="date1" value="<?= date('Y-m-d'); ?>"> по <input type="date"  class="choose_date" id="date2"  value="<?= date('Y-m-d'); ?>">
</div>
<div class="row center">
    <div class="col-md-12">
        <table class="small_table table-striped table_cashbox one-touch-view" id="common_table">
            <thead>
            <th class="center">
                Статус
            </th>
            <th class="center">
                Менеджер
            </th>
            <th class="center">
                Звонки
            </th>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
<h4>
    Проекты
</h4>
<div class="analitic-actions">
    Выбрать с <input type="date"  class="choose_date_p" id="p_date1" value="<?= date('Y-m-d'); ?>"> по <input type="date"  class="choose_date_p" id="p_date2"  value="<?= date('Y-m-d'); ?>">
</div>
<div class="row center">
    <div class="col-md-12">
        <table class="small_table table-striped table_cashbox one-touch-view" id="projects_anallytic_table">
            <thead>
            <tr>
                <th rowspan="2" class="center">
                    Менеджер
                </th>
                <th colspan="3" class="center">Замеры</th>
                <th colspan="3" class="center">Договоры</th>
                <th colspan="3" class="center">Отказы</th>
            </tr>
            <tr>
                <td>Всего</td>
                <td>
                    <!--  <div class="col-xs-6 col-md-6">-->
                    Свои
                    <!--</div>
                    <div class="col-xs-6 col-md-6">
                        <i class="fas fa-question question" style="vertical-align: middle;font-size: 10pt;cursor:help;"></i>
                        <div class="help">
                            <span class="airhelp">Проекты, созданные и записанные на замер одним и тем жу менеджером</span>
                        </div>
                    </div>-->
                </td>
                <td>Др.менеджеров</td>
                <td>Всего</td>
                <td>Свои</td>
                <td>Др.менеджеров</td>
                <td>Всего</td>
                <td>Свои</td>
                <td>Др.менеджеров</td>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
<hr>
<h4>
    Контакты с клиентами
</h4>
<div class="analitic-actions">
    Выбрать с <input type="date"  class="choose_date_с" id="с_date1" value="<?= date('Y-m-d'); ?>"> по <input type="date"  class="choose_date_с" id="с_date2"  value="<?= date('Y-m-d'); ?>">
</div>
<div class="row center">
    <div class="col-md-12">
        <table class="small_table table-striped table_cashbox one-touch-view" id="managersUniqueCallsTable">
            <thead>
                <tr>
                    <th rowspan="2" class="center">
                        Менеджер
                    </th>
                   <th>
                       Кол-во контактов
                   </th>
                    <th>
                        Кол-во замеров
                    </th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($managersUniqueCalls as $id=>$item){ ?>
                <tr data-id="<?= $id;?>">
                    <td>
                        <?= $item->name; ?>
                    </td>
                    <td class="clients_td">
                        <?= $item->clients_count; ?>
                    </td>
                    <td class="projects_td">
                        <?= $item->projects_count; ?>
                    </td>
                </tr>
            <?php }?>
            </tbody>
        </table>
    </div>
</div>
<div class="modal_window_container" id="mw_container">
    <button type="button" id="close-modal-window" class="close_modal_analic"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div class="modal_window" id="mw_calls">
        <table class="small_table table-striped table_cashbox one-touch-view">
            <thead>
            <th>Дата</th>
            <th>Клиент</th>
            <th>Менеджер</th>
            </thead>
            <tbody id="info">
            </tbody>
        </table>
    </div>
    <div class="modal_window" id = "mw_projects">
        <table id="projects_table"  class = "table_project_analitic">
            <thead>
            <th>
                №
            </th>
            <th>
                Создан
            </th>
            <th>
                Обновлен
            </th>
            <th>
                Адрес
            </th>
            <th>
                Сумма
            </th>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
    <div class="modal_window" id = "mw_clients">
        <table id="clients_table"  class = "table_project_analitic">
            <thead>
            <th>
                ФИО
            </th>
            <th>
                Контакты
            </th>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
    <div class="modal_window" id="mw_add_manager">
        <h4>Добавление нового менеджера</h4>
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="row">
                    <label>Введите Фамилию, Имя, Отчество</label>
                    <input id="fio" class="form-control">
                </div>
                <div class="row">
                    <label>Введите номер телефона</label>
                    <input id="phone" class="form-control">
                </div>
                <div class="row">
                    <label>Введите адрес эл.почты</label>
                    <input id="email" class="form-control">
                </div>
                <div class="row">
                    <button class="btn btn-primary" id="save_manager">Добавить</button>
                </div>
            </div>
            <div class="col-md-4"></div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var quantityData = {},
        uniqueCalls = JSON.parse('<?=json_encode($managersUniqueCalls)?>');
    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div = jQuery("#mw_projects"),
            div1 = jQuery('#mw_calls'),
            div2 = jQuery('#mw_add_manager'),
            div3 =  jQuery('#mw_clients') ;// тут указываем ID элемента
        if (!div.is(e.target) // если клик был не по нашему блоку
            && !div1.is(e.target)
            && !div2.is(e.target)
            && !div3.is(e.target)
            && div.has(e.target).length === 0
            && div1.has(e.target).length === 0
            && div2.has(e.target).length === 0
            && div3.has(e.target).length === 0) { // и не по его дочерним элементам
            div.hide();
            div1.hide();
            div2.hide();
            div3.hide();
            jQuery("#close-modal-window").hide();
            jQuery("#mw_container").hide();
        }
    });
    jQuery(document).ready(function(){

        var savedData = JSON.parse(localStorage.getItem('projectsData'));
        localStorage.removeItem('projectsData');
        if(!empty(savedData)){
            jQuery("#date1").val(savedData.date1);
            jQuery("#date2").val(savedData.date2);
            fillModalTable(savedData.projects);
        }

        jQuery('#phone').mask('+7(999)999-99-99');
        getData();
        jQuery('#add_manager').click(function(){
            jQuery('#mw_container').show();
            jQuery('#close').show();
            jQuery('#mw_add_manager').show();
        });

        jQuery('#save_manager').click(function(){
            var fio = jQuery('#fio').val(),
                phone = jQuery('#phone').val(),
                email = jQuery('#email').val();
            if(!empty(fio) && !empty(phone) && !empty(email)){
                jQuery.ajax({
                    type: 'POST',
                    url: "index.php?option=com_gm_ceiling&task=users.registerNewUser",
                    data: {
                        fio: fio,
                        phone: phone,
                        email: email
                    },
                    success: function(data){
                        if(data.type == 'error'){
                            var n = noty({
                                timeout: 2000,
                                theme: 'relax',
                                layout: 'center',
                                maxVisible: 5,
                                type: "error",
                                text: data.text
                            });
                        }
                        else{
                            var n = noty({
                                timeout: 2000,
                                theme: 'relax',
                                layout: 'center',
                                maxVisible: 5,
                                type: "success",
                                text: data.text
                            });
                            jQuery('#fio').val('');
                            jQuery('#phone').val('');
                            jQuery('#email').val('');
                            jQuery('#mw_container').hide();
                            jQuery('#mw_add_manager').hide();

                        }
                    },
                    dataType:"json",
                    async: false,
                    timeout: 10000,
                    error: function(data){
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка!"
                        });
                    }
                });
            }
            else{
                if(empty(fio)){
                    jQuery('#fio').focus();
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Не указано ФИО!"
                    });
                }
                if(empty(phone)){
                    jQuery('#phone').focus();
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Не указан телефон!"
                    });
                }
                if(empty(email)){
                    jQuery('#email').focus();
                    noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Не указано адрес эл.почты!"
                    });
                }
            }
        });

        jQuery('body').on('click','.click_tr', function(){
            var status_id = jQuery(this).data('id');
            jQuery.ajax({
                type: 'POST',
                url: "index.php?option=com_gm_ceiling&task=callback.getCallsHistory",
                data: {
                    dealerId: '<?php echo $dealerId;?>',
                    dateFrom: jQuery('#date1').val(),
                    dateTo: jQuery('#date2').val(),
                    statusId: status_id
                },
                success: function(data){
                    jQuery('#mw_container').show();
                    jQuery('#mw_calls').show();
                    fillDetailedTable(data);
                },
                dataType:"json",
                async: false,
                timeout: 10000,
                error: function(data){
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка!"
                    });
                }
            });
        });

        jQuery('.choose_date').change(function(){
            getData();
        });
        jQuery('.choose_date_p').change(function(){
            var dateFrom = jQuery('#p_date1').val(),
                dateTo = jQuery('#p_date2').val();
            if(dateFrom<=dateTo) {
                jQuery.ajax({
                    type: 'POST',
                    url: "index.php?option=com_gm_ceiling&task=projects.getManagersProjects",
                    data: {
                        dateFrom: dateFrom,
                        dateTo: dateTo,
                        dealerId: '<?php echo $dealerId;?>'
                    },
                    success: function (res) {
                        showProjects(res);
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
                            text: "Ошибка!"
                        });
                    }
                });
            }
            else{
                var n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Начальная дата не может быть больше конечной!"
                });
            }
        });

        jQuery(document).on("click", ".click_td", function(event) {
            var type = jQuery(this).data('type'),
                subtype = jQuery(this).data('subtype'),
                manager = jQuery(this).closest('tr').data('manager'),
                ids = quantityData[manager][type][subtype].ids;
            ids = ids.join(',');
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=getAnaliticProjects",
                data: {
                    ids : ids
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    fillModalTable(data);

                },
                error: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка получения данных"
                    });
                }
            });
        });

        jQuery('#managersUniqueCallsTable').on('click','.clients_td',function(){
            var managerId= jQuery(this).closest('tr').data('id'),
                ids = uniqueCalls[managerId].clients;
            console.log(ids);
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=clients.getClientsByIds",
                data: {
                    ids : ids
                },
                dataType: "json",
                async: false,
                success: function (data) {
                   jQuery('#mw_container').show();
                   jQuery('#mw_clients').show();
                   jQuery('#close_mw').show();
                   jQuery.each(data,function (i,item) {
                       jQuery('#clients_table').append('<tr data-id="'+item.id+'"><td>'+item.client_name+'</td><td>'+item.contacts+'</td></tr>');
                   });
                },
                error: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка получения данных"
                    });
                }
            });
        });
        jQuery('#managersUniqueCallsTable').on('click','.projects_td',function(){
            var managerId= jQuery(this).closest('tr').data('id'),
                ids = uniqueCalls[managerId].projects;
            console.log(managerId);
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=getAnaliticProjects",
                data: {
                    ids : ids
                },
                dataType: "json",
                async: false,
                success: function (data) {
                    fillModalTable(data);
                },
                error: function (data) {
                    var n = noty({
                        timeout: 2000,
                        theme: 'relax',
                        layout: 'center',
                        maxVisible: 5,
                        type: "error",
                        text: "Ошибка получения данных"
                    });
                }
            });
        });

        jQuery('.choose_date_с').change(function () {
            var date_from = jQuery('#с_date1').val(),
                date_to = jQuery('#с_date2').val();
            if(date_from<=date_to){
                jQuery.ajax({
                    url: "index.php?option=com_gm_ceiling&task=analytic.getManagersAnalytic",
                    data: {
                        date_from: date_from,
                        date_to: date_to
                    },
                    dataType: "json",
                    async: false,
                    success: function (data) {
                       jQuery('#managersUniqueCallsTable > tbody > tr').empty();
                       if(!empty(data)){
                           uniqueCalls = data;
                           jQuery.each(data,function(i,item){
                               jQuery('#managersUniqueCallsTable > tbody').append(
                                   '<tr data-id="'+i+'">' +
                                   '<td>'+item.name+'</td>' +
                                   '<td class="clients_td">'+item.clients_count+'</td>' +
                                   '<td class="projects_td">'+item.projects_count+'</td>' +
                                   '</tr>');
                           });
                       }
                    },
                    error: function (data) {
                        var n = noty({
                            timeout: 2000,
                            theme: 'relax',
                            layout: 'center',
                            maxVisible: 5,
                            type: "error",
                            text: "Ошибка получения данных"
                        });
                    }
                });
            }
            else{
                n = noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Начальная дата не может быть больше конечной!"
                });
            }
        });
    });

    function fillModalTable(data) {
        jQuery("#projects_table > tbody").empty();
        for (var i = 0; i < data.length; i++) {
            jQuery("#projects_table > tbody").append('<tr  class="link_row" data-id="' + data[i].id + '"><td>' + data[i].id + '</td><td>'+data[i].created+'</td><td>'+data[i].read_by_manager+'</td><td>' + data[i].project_info + '</td><td>' + data[i].project_sum + '</td></tr>')
        }
        jQuery("#mw_container").show();
        jQuery("#close-modal-window").show();
        jQuery("#mw_projects").show('slow');

        jQuery(".link_row").click(function () {
            var object = {date1: jQuery("#date1").val(),date2:jQuery("#date2").val(),projects:data}
            localStorage.setItem('projectsData', JSON.stringify(object));
            location.href = '/index.php?option=com_gm_ceiling&view=project&type=calculator&subtype=project&id=' + jQuery(this).data('id');
        });
    }

    function getData(){
        jQuery.ajax({
            type: 'POST',
            url: "index.php?option=com_gm_ceiling&task=callback.getCallsAnalytic",
            data: {
                dateFrom: jQuery('#date1').val(),
                dateTo: jQuery('#date2').val(),
                dealerId: '<?php echo $dealerId;?>'
            },
            success: function(data){
                showTableData(data.calls);
                //showProjects(data.projects);
            },
            dataType:"json",
            async: false,
            timeout: 10000,
            error: function(data){
                 noty({
                    timeout: 2000,
                    theme: 'relax',
                    layout: 'center',
                    maxVisible: 5,
                    type: "error",
                    text: "Ошибка!"
                });
            }
        });
    }
    function showTableData(data) {
        var table = jQuery('#common_table > tbody'),
            common_count_data = [];
        table.empty();
        for(var i=0;i<data.length;i++){
            var manager_info = JSON.parse(data[i].manager_count);
            if(!empty(manager_info)){
                for(var j=0;j<manager_info.length;j++) {
                    if(!common_count_data.hasOwnProperty(manager_info[j].manager)){
                        common_count_data[manager_info[j].manager] = {calls:0,measures:0,deals:0,refs:0,m_ids:'',d_ids:'',r_ids:''};
                    }
                    else{
                        continue;
                    }
                }
                for(var j=0;j<manager_info.length;j++){

                    common_count_data[manager_info[j].manager].calls += +manager_info[j].count;
                    common_count_data[manager_info[j].manager].users = +manager_info[j].added_users;


                    table.append('<tr class="click_tr" data-id="'+data[i].id+'"></tr>');
                    var tr = jQuery('#common_table > tbody > tr:last');
                    if(j == 0){
                        tr.append('<td rowspan="'+manager_info.length+'">'+data[i].title+'</td><td class="manager">'+manager_info[j].manager+'</td><td>'+manager_info[j].count+'</td>');
                    }
                    else{
                        tr.append('<td class="manager">'+manager_info[j].manager+'</td><td>'+manager_info[j].count+'</td>');
                    }

                }

            }
        }
        var j = 0,
            common_calls = 0;

        for( var key in common_count_data)
        {
            table.append('<tr></tr>');
            common_calls += common_count_data[key].calls;

            var tr = jQuery('#common_table > tbody > tr:last');
            if(j == 0){
                tr.append('<td rowspan="'+ (Object.keys(common_count_data).length+1) +'"><b>Общее</b></td><td class="manager"><div class="col-md-12">'+key+'</div><div class="col-md-12"><b>Сбор базы:</b> '+common_count_data[key].users+'</div></td><td>'+common_count_data[key].calls+'</td>');
            }
            else{
                tr.append('<td class="manager"<div class="col-md-12">'+key+'</div><div class="col-md-12"><b>Сбор базы:</b> '+common_count_data[key].users+'</div></td><td>'+common_count_data[key].calls+'</td>');
            }
            j++;
        };
        if(!empty(common_calls)) {
            table.append('<tr><td><b>Итого</b></td><td>' + common_calls + '</td></tr>');
        }
    }

    function showProjects(projects_data) {
        jQuery('#projects_anallytic_table > tbody').empty();
        quantityData = {};
        jQuery.each(projects_data,function(index,elem){

            var dataByStatus = JSON.parse(elem.data);
            quantityData[elem.id] = {
                name: elem.name,
                measures:{
                    total:{
                        count: 0,
                        ids: []
                    },
                    own:{
                        count: 0,
                        ids: []
                    },
                    other:{
                        count:0,
                        ids: []
                    }
                },
                deals:{
                    total:{
                        count: 0,
                        ids: []
                    },
                    own:{
                        count: 0,
                        ids: []
                    },
                    other:{
                        count:0,
                        ids: []
                    }
                },
                refuse:{
                    total:{
                        count: 0,
                        ids: []
                    },
                    own:{
                        count: 0,
                        ids: []
                    },
                    other:{
                        count:0,
                        ids: []
                    }
                }
            };
            jQuery.each(dataByStatus,function(n,data){
                for(var i=0;i<data.projects.length;i++){
                    switch(data.status){
                        case "1":
                            quantityData[elem.id].measures.total.count++;
                            quantityData[elem.id].measures.total.ids.push(data.projects[i].id);
                            if((data.projects[i].created == data.projects[i].read) || data.projects[i].created == elem.id ){
                                quantityData[elem.id].measures.own.count++;
                                quantityData[elem.id].measures.own.ids.push(data.projects[i].id);
                            }
                            else{
                                if(data.projects[i].created != elem.id && data.projects[i].read == elem.id){
                                    quantityData[elem.id].measures.other.count++;
                                    quantityData[elem.id].measures.other.ids.push(data.projects[i].id);
                                }

                            }
                            break;
                        case "3":
                            quantityData[elem.id].refuse.total.count++;
                            quantityData[elem.id].refuse.total.ids.push(data.projects[i].id);
                            if(data.projects[i].created == data.projects[i].read){
                                quantityData[elem.id].refuse.own.count++;
                                quantityData[elem.id].refuse.own.ids.push(data.projects[i].id);
                            }
                            else{
                                if(data.projects[i].created != elem.id && data.projects[i].read == elem.id ) {
                                    quantityData[elem.id].refuse.other.count++;
                                    quantityData[elem.id].refuse.other.ids.push(data.projects[i].id);
                                }
                            }
                            break;
                        case "4":
                        case "5":
                            quantityData[elem.id].deals.total.count++;
                            quantityData[elem.id].deals.total.ids.push(data.projects[i].id);
                            if(data.projects[i].created == data.projects[i].read){
                                quantityData[elem.id].deals.own.count++;
                                quantityData[elem.id].deals.own.ids.push(data.projects[i].id);
                            }
                            else{
                                if(data.projects[i].created != elem.id && data.projects[i].read == elem.id ) {
                                    quantityData[elem.id].deals.other.count++;
                                    quantityData[elem.id].deals.other.ids.push(data.projects[i].id);
                                }
                            }
                            break;
                    }


                }
            });
        });
        var totalMeasures = 0,
            totalMeasuresOwn = 0,
            totalMeasuresOther = 0,
            totalDeals = 0,
            totalDealsOwn = 0,
            totalDealsOther = 0,
            totalRefuse = 0,
            totalRefuseOwn = 0,
            totalRefuseOther = 0;
        jQuery.each(quantityData,function(i,data){
            totalMeasures += data.measures.total.count;
            totalMeasuresOwn += data.measures.own.count;
            totalMeasuresOther += data.measures.other.count;
            totalDeals += data.deals.total.count;
            totalDealsOwn += data.deals.own.count;
            totalDealsOther += data.deals.other.count;
            totalRefuse += data.refuse.total.count;
            totalRefuseOwn += data.refuse.own.count;
            totalRefuseOther += data.refuse.other.count;
            jQuery('#projects_anallytic_table > tbody').append('<tr data-manager="'+i+'"></tr>');
            jQuery('#projects_anallytic_table > tbody > tr:last').append(
                '<td>'+data.name+'</td>' +
                '<td data-type="measures" data-subtype="total" class="click_td">'+data.measures.total.count +'</td>' +
                '<td data-type="measures" data-subtype="own" class="click_td">'+data.measures.own.count+'</td>' +
                '<td data-type="measures" data-subtype="other" class="click_td">'+ data.measures.other.count +'</td>' +
                '<td data-type="deals" data-subtype="total" class="click_td">'+ data.deals.total.count+'</td>' +
                '<td data-type="deals" data-subtype="own" class="click_td">'+ data.deals.own.count +'</td>' +
                '<td data-type="deals" data-subtype="other" class="click_td">'+ data.deals.other.count +'</td>' +
                '<td data-type="refuse" data-subtype="total" class="click_td">'+ data.refuse.total.count +'</td>' +
                '<td data-type="refuse" data-subtype="own" class="click_td">'+ data.refuse.own.count +'</td>' +
                '<td data-type="refuse" data-subtype="other" class="click_td">'+ data.refuse.other.count +'</td>'
            );
        });
        jQuery('#projects_anallytic_table > tbody').append('<tr><td><b>Итого</b></td>' +
            '<td>'+totalMeasures+'</td>'+
            '<td>'+totalMeasuresOwn+'</td>'+
            '<td>'+totalMeasuresOther+'</td>'+
            '<td>'+totalDeals+'</td>'+
            '<td>'+totalDealsOwn+'</td>'+
            '<td>'+totalDealsOther+'</td>'+
            '<td>'+totalRefuse+'</td>'+
            '<td>'+totalRefuseOwn+'</td>'+
            '<td>'+totalRefuseOther+'</td>'+
            '</tr>');

    }
    function fillDetailedTable(data) {
        jQuery("#info").empty();
        for(var i=0;i<data.length;i++){
            jQuery("#info").append('<tr></tr>');
            jQuery("#info > tr:last").append('<td>'+data[i].change_time+'</td><td>'+data[i].client_name+'</td><td>'+data[i].manager_name+'</td>')
        }
    }





</script>

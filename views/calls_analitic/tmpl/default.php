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

echo parent::getButtonBack();

?>
<style type="text/css">
    .small_table {
        cursor: pointer;
    }
    .small_table tbody tr:hover {
        background: #ddeeff;
    }
</style>
<h2>Аналитика звонков</h2>
<button type="button" class="btn btn-primary" id="show_all">Показать за всё время</button>
<div class="analitic-actions">
    Выбрать с <input type="date"  class="choose_date" id="date1" value="<?= date('Y-m-d'); ?>"> по <input type="date"  class="choose_date" id="date2"  value="<?= date('Y-m-d'); ?>">
</div>
<table class="small_table table-striped table_cashbox one-touch-view" id="common_table">
    <thead>
    <th>
        Статус
    </th>
    <th>
        Менеджер
    </th>
    <th>
       Звонки
    </th>
    <th>
        Замеры
    </th>
    <th>
        Договоры
    </th>
    <th>
        Отказы
    </th>
    </thead>
    <tbody>

    </tbody>
</table>
<hr>
<table class="small_table table-striped table_cashbox one-touch-view">
    <thead>
    <th>Дата</th>
    <th>Клиент</th>
    <th>Менеджер</th>
    </thead>
    <tbody id="info">
    </tbody>
</table>
<div class="modal_window_container" id="mw_container">
    <button type="button" id="close-modal-window" class="close_modal_analic"><i class="fa fa-times fa-times-tar" aria-hidden="true"></i></button>
    <div class="modal_window" id = "mw_projects">
        <table id="projects_table"  class = "table_project_analitic">
            <thead>
                <th>
                    №
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
</div>
<script type="text/javascript">
    jQuery(document).mouseup(function (e){ // событие клика по веб-документу
        var div = jQuery("#mw_projects"); // тут указываем ID элемента
        if (!div.is(e.target) // если клик был не по нашему блоку
            && div.has(e.target).length === 0) { // и не по его дочерним элементам
            jQuery("#mw_projects").hide();
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
        getData();
        jQuery(".click_tr").click(function(){
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
                    console.log(data);
                    fillDetailedTable(data);
                },
                dataType:"json",
                async: false,
                timeout: 10000,
                error: function(data){
                    console.log(data)
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


        jQuery(document).on("click", ".click_td", function(event) {
            var ids = jQuery(this).data('ids');
            jQuery.ajax({
                url: "index.php?option=com_gm_ceiling&task=getAnaliticProjects",
                data: {
                    ids : ids
                },
                dataType: "json",
                async: true,
                success: function (data) {
                    console.log(data);
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
    });

    function fillModalTable(data) {
        jQuery("#projects_table > tbody").empty();
        for (var i = 0; i < data.length; i++) {
            jQuery("#projects_table > tbody").append('<tr  class="link_row" data-id="' + data[i].id + '"><td>' + data[i].id + '</td><td>' + data[i].project_info + '</td><td>' + data[i].project_sum + '</td></tr>')
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
                console.log(data);
               showTableData(data);
            },
            dataType:"json",
            async: false,
            timeout: 10000,
            error: function(data){
                console.log(data)
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
                    var projects =  manager_info[j].measures_count,
                        measures_count = 0,
                        deals_count = 0,
                        refuse_count = 0,
                        m_ids = [],
                        d_ids = [],
                        r_ids = [];
                    for (var p = projects.length - 1; p >= 0; p--) {
                        if(projects[p].status == 1){
                            measures_count += +projects[p].count;
                            m_ids.push(projects[p].ids);
                        }
                        if(projects[p].status == 4 || projects[p].status == 5){
                            deals_count += +projects[p].count;
                            d_ids.push(projects[p].ids);

                        }
                        if(projects[p].status == 3){
                            refuse_count += +projects[p].count;
                            r_ids.push(projects[p].ids);
                        }
                    }
                    common_count_data[manager_info[j].manager].calls += +manager_info[j].count;
                    common_count_data[manager_info[j].manager].measures = measures_count;
                    common_count_data[manager_info[j].manager].deals = deals_count;
                    common_count_data[manager_info[j].manager].refs = refuse_count;
                    common_count_data[manager_info[j].manager].m_ids = m_ids;
                    common_count_data[manager_info[j].manager].d_ids = d_ids
                    common_count_data[manager_info[j].manager].r_ids = r_ids

                    m_ids = m_ids.join(',');
                    d_ids = d_ids.join(',');
                    r_ids = r_ids.join(',');

                    table.append('<tr class="click_tr" data-id="'+data[i].id+'"></tr>');
                    var tr = jQuery('#common_table > tbody > tr:last');
                    if(j == 0){
                        tr.append('<td rowspan="'+manager_info.length+'">'+data[i].title+'</td><td class="manager">'+manager_info[j].manager+'</td><td>'+manager_info[j].count+'</td><td class="click_td" data-ids="'+m_ids+'">'+measures_count+'</td><td class="click_td" data-ids="'+d_ids+'">'+deals_count+'</td><td class="click_td" data-ids="'+r_ids+'">'+refuse_count+'</td>');
                    }
                    else{
                        tr.append('<td class="manager">'+manager_info[j].manager+'</td><td>'+manager_info[j].count+'</td><td class="click_td" data-ids="'+m_ids+'">'+measures_count+'</td><td class="click_td" data-ids="'+d_ids+'">'+deals_count+'</td><td class="click_td" data-ids="'+r_ids+'">'+refuse_count+'</td>');
                    }

                }

            }
        }
        var j = 0,
            common_calls = 0,
            common_measures = 0,
            common_deals = 0,
            common_refs = 0,
            common_m_ids = [],
            common_d_ids = [],
            common_r_ids = [];
        for( var key in common_count_data)
        {
            console.log(key);
            table.append('<tr></tr>');
            common_calls += common_count_data[key].calls;
            common_measures += common_count_data[key].measures;
            common_deals += common_count_data[key].deals;
            common_refs += common_count_data[key].refs;
            common_m_ids.push(common_count_data[key].m_ids);
            common_d_ids.push(common_count_data[key].d_ids);
            common_r_ids.push(common_count_data[key].r_ids);

            var tr = jQuery('#common_table > tbody > tr:last');
            if(j == 0){
                tr.append('<td rowspan="'+ (Object.keys(common_count_data).length+1) +'"><b>Общее</b></td><td class="manager">'+key+'</td><td>'+common_count_data[key].calls+'</td><td class="click_td" data-ids="'+common_count_data[key].m_ids+'">'+common_count_data[key].measures+'</td><td class="click_td" data-ids="'+common_count_data[key].d_ids+'">'+common_count_data[key].deals+'</td><td class="click_td" data-ids="'+common_count_data[key].r_ids+'">'+common_count_data[key].refs+'</td>');
            }
            else{
                tr.append('<td class="manager">'+key+'</td><td>'+common_count_data[key].calls+'</td><td class="click_td" data-ids="'+common_count_data[key].m_ids+'">'+common_count_data[key].measures+'</td><td class="click_td" data-ids="'+common_count_data[key].d_ids+'">'+common_count_data[key].deals+'</td><td class="click_td" data-ids="'+common_count_data[key].r_ids+'">'+common_count_data[key].refs+'</td>');
            }
            j++;
        };
        if(!empty(common_calls) || !empty(common_measures) || !empty(common_deals) || !empty(common_refs)) {
            common_m_ids = common_m_ids.join(',');
            common_d_ids = common_d_ids.join(',');
            common_r_ids = common_r_ids.join(',');
            table.append('<tr><td><b>Итого</b></td><td>' + common_calls + '</td><td class="click_td" data-ids ="'+common_m_ids+'" >' + common_measures + '</td><td class="click_td" data-ids ="'+common_d_ids+'">' + common_deals + '</td><td class="click_td" data-ids ="'+common_r_ids+'">' + common_refs + '</td></tr>');
        }
    }

    function fillDetailedTable(data) {
        jQuery("#info").empty();
        for(var i=0;i<data.length;i++){
            jQuery("#info").append('<tr></tr>');
            jQuery("#info > tr:last").append('<td>'+data[i].change_time+'</td><td>'+data[i].client_name+'</td><td>'+data[i].manager_name+'</td>')
        }
    }





</script>
